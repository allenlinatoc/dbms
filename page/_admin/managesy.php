<?php


if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- FILTERED
    #
    // action processing AFTER dialogbox
    if (DATA::__HasIntentData('DIALOG_RESULT')) {
        $dialogResult = DATA::__GetIntent('DIALOG_RESULT');
        if ($dialogResult == DIALOG::R_AFFIRMATIVE) {
            $MODE = strtoupper(DATA::__GetIntentSecurely('MODE', TRUE, TRUE));
            $TARGET_ID = strtoupper(DATA::__GetIntentSecurely('TARGET_ID', TRUE, TRUE));
            $sql = new DB();
            $year = intval($sql->Select(['year'])->From('sy')->Where('id=' . $TARGET_ID)->Query()[0]['year']);
            if ( $MODE == 'DELETE' ) 
            {
                // [action] CONFIRM_DELETE
                $sql = new DB();
                $sql->
                        DeleteFrom('sy')->
                        Where('id=' . $TARGET_ID);
                $is_success = $sql->Execute()->__IsSuccess();
                FLASH::addFlash(($is_success ? 
                            'Academic year ' . $year . '-' . ($year+1) . ' has been successfully deleted!' 
                          : 'No academic year was deleted'), 
                        Index::__GetPage(), 'PROMPT', true);
            }
            else if ( $MODE == 'SETDEFAULT' )
            {
                // [action] CONFIRM_SETDEFAULT
                $is_success = false;
                $sql = new DB();
                $sql->
                        Update('sy')->
                        Set([ 'is_default' => '1' ])->
                        Where('id = '.$TARGET_ID);
                $hasAffected = $sql->Execute()->__GetAffectedRows() > 0;
                if ($hasAffected) {
                    $sql->
                            Update('sy')->
                            Set([ 'is_default' => '0' ])->
                            Where('id <> '.$TARGET_ID);
                    $is_success = $sql->Execute()->__IsSuccess();
                }
                if ($is_success)
                {
                    FLASH::addFlash('Academic year ' . $year . '-' . ($year+1) . ' has been set to default.',
                            Index::__GetPage(), 'PROMPT', TRUE);
                }
                else
                {
                    FLASH::addFlash('Something went wrong during setting of a default AY. Geeks are on the way to fix it.',
                            Index::__GetPage(), 'ERROR', true);
                }
            }
        }
        DATA::FullDestroyIntents();
        UI::RefreshPage();
    }
    
    // dialogbox creations on MODE request
    if (DATA::__HasIntentData('MODE') && DATA::__HasIntentData('TARGET_ID')) {
        $MODE = strtoupper(DATA::__GetIntentSecurely('MODE', TRUE, TRUE));
        $TARGET_ID = DATA::__GetIntent('TARGET_ID');
        $sql = new DB();
        $AY_INFOS = $sql->Select()->From('sy')->Where('id='.$TARGET_ID)->Query()[0];
        // if MODE = DELETE
        $DialogObject = new DIALOG('Confirm delete of academic year entry');
        if ( $MODE == 'DELETE' )
        {
            $DialogObject->
                    SetTitle('Confirm delete of academic year entry')->
                    SetMessage('Are you sure you want to delete academic year entry ' . $AY_INFOS['year'] . '-' . (intval($AY_INFOS['year'])+1) . '?')->
                    SetPageCallback(Index::__GetPage());
            $DialogObject->
                    AddButton(DIALOG::B_YES)->
                    AddButton(DIALOG::B_NO)->
                    AddButton(DIALOG::B_CANCEL);
                    $DialogObject->ShowDialog();
        }
        else if ( $MODE == 'SETDEFAULT' ) 
        {
            $year = intval($AY_INFOS['year']);
            $DialogObject->
                    SetTitle('Set as default School year')->
                    SetMessage('Are you sure you want to set AY ' . $year . '-' . ($year+1) . ' as Default academic year?<br>'
                            . '<i>This will globally affect the data across all courses.</i>')->
                    SetPageCallback(Index::__GetPage());
            $DialogObject->
                    AddButton(DIALOG::B_YES)->
                    AddButton(DIALOG::B_NO)->
                    AddButton(DIALOG::B_CANCEL);
                    $DialogObject->ShowDialog();
        }
    }
}

DATA::openPassages([
    'admin-manage-schoolyears-form',
    Index::__GetPage()
]); 

$postSortby = -1;
if (DATA::__HasPostData('postSortby'))
{
    $postSortby = intval(DATA::__GetPOST('postSortby', true));
}
# [PROCESS] Preparing "School year entries" report
# -------------------------------------------------
$sql = new DB();
$sql->Select(array(
            'sy.id', 'sy.is_default', 'concat(year, \'-\', year+1)', 'description'))
        ->From('sy')
        ->OrderBy($postSortby, DB::ORDERBY_ASCENDING);
$result = $sql->Query();
//die($sql->query);
$rptSchoolYear = new MySQLReport();
$rptSchoolYear->setReportProperties(array(
            'align' => 'center',
            'width' => '100%'))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true,
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'hidden_IS_DEFAULT',
                'HIDDEN' => true,
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'A.Y.',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Description',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Actions',
                'DEFAULT' =>
                    UI::Button('Edit', 'button', 'btn btn-primary btn-xs', UI::GetPageUrl('admin-manage-schoolyears-form', array(
                        'TARGET_ID' => '{1}'
                    )), false) . ' ' .
                    UI::Button('Delete', 'button', 'btn btn-danger btn-xs', UI::GetPageUrl(Index::__GetPage(), array(
                        'MODE' => 'DELETE',
                        'TARGET_ID' => '{1}'
                    )), false),
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Default',
                'LISTOFVALUES' => array(
                    1 => '<b>DEFAULT</b>',
                    0 => UI::Button('Use as default', 'button', 'btn btn-warning btn-small', UI::GetPageUrl(Index::__GetPage(), array(
                        'MODE' => 'SETDEFAULT',
                        'TARGET_ID' => '{1}'
                    )), false)
                ),
                'DEFAULT' => '{2}',
                'class' => 'rpt-header'
            ]))
        ->setReportCellstemplate(array(
            [
                
            ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ]
        ));
$rptSchoolYear->defineEmptyMessage('No existing Academic year entry.');
$rptSchoolYear->loadResultdata($result);



?>