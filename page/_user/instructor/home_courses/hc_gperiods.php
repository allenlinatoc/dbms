<?php
$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
$sy_id = ACADYEAR::__getDefaultID();

$postPeriods = 1;
$postNotes = '';
$MODE = 'NOTHING';

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    if (DATA::__HasIntentData('MODE')) {
        if (DATA::__HasIntentData('DIALOG_RESULT') && DATA::__GetIntentSecurely('MODE')=='REQ_DELETE') 
        {
            $dialogResult = DATA::__GetIntent('DIALOG_RESULT');
            $TARGET_ID = DATA::__GetIntentSecurely('TARGET_ID');
            if ($dialogResult == DIALOG::R_AFFIRMATIVE)
            {
                $sql = new DB();
                $sql->DeleteFrom('d_course_gperiod')
                        ->Where('id='.$TARGET_ID.' '
                                . 'AND course_id='.$COURSE_INFOS['id'].' '
                                . 'AND sy_id='.$sy_id);
                $is_success = $sql->Execute()->__IsSuccess();
                if ($is_success) {
                    FLASH::addFlash('Successfully deleted!', Index::__GetPage(), 'PROMPT', true);
                }
                else {
                    FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', Index::__GetPage(), 'ERROR', true);
                }
            }
            DATA::DeleteIntents([
                'MODE', 'TARGET_ID', 'DIALOG_OBJECT', 'DIALOG_RESULT'
            ], true, true);
        }
        
        $MODE = DATA::__GetIntentSecurely('MODE');
        if ($MODE=='REQ_ADD') {
            FLASH::clearFlashes();
        }
        else if ($MODE=='REQ_DEFAULT') {
            $TARGET_ID = DATA::__GetIntentSecurely('TARGET_ID');
            $sql = new DB();
            $sql->Update('d_course_gperiod')
                    ->Set(array(
                        'is_current' => '1'))
                    ->Where('id='.$TARGET_ID.' '
                            . 'AND course_id='.$COURSE_INFOS['id'].' '
                            . 'AND sy_id='.$sy_id);
            $sql_1 = $sql;
            $sql = new DB();
            $sql->Update('d_course_gperiod')
                    ->Set(array(
                        'is_current' => '0'))
                    ->Where('id!='.$TARGET_ID.' '
                            . 'AND course_id='.$COURSE_INFOS['id'].' '
                            . 'AND sy_id='.$sy_id);
            $sql_2 = $sql;
            if ($sql_1->Execute()->__IsSuccess() && $sql_2->Execute()->__IsSuccess()) 
            {
                FLASH::addFlash('Default grading period has been successfully changed.', Index::__GetPage(), 'PROMPT', true);
                DATA::DeleteIntents(array('MODE', 'TARGET_ID'), true, true);
            }
        }
        else if ($MODE=='REQ_DELETE') {
            $TARGET_ID = DATA::__GetIntentSecurely('TARGET_ID');
            $sql = new DB();
            $sql->Select(['name'])
                    ->From('d_course_gperiod,gperiod')
                    ->Where('course_id='.$COURSE_INFOS['id'].' '
                            . 'AND sy_id='.$sy_id.' '
                            . 'AND d_course_gperiod.id='.$TARGET_ID.' '
                            . 'AND d_course_gperiod.gperiod_id=gperiod.id');
            $periodName = ucfirst($sql->Query()[0]['name']);
            $dialogObject = new DIALOG('Confirm delete of '.$periodName);
            $dialogObject->SetMessage('Are you sure you want to delete grading period "'.$periodName
                    .'" under course '.$COURSE_INFOS['name'].'?<br>'
                    . '<i>This will delete all data related to this grading period</i>')
                    ->SetPageCallback(Index::__GetPage())
                    ->AddButton(DIALOG::B_YES)
                    ->AddButton(DIALOG::B_NO)
                    ->AddButton(DIALOG::B_CANCEL);
            
            $dialogObject->ShowDialog();
        }
    }
}

if (DATA::__HasPostData('postPeriods') && DATA::__HasPostData('postNotes'))
{
    $sql = new DB();
    $postPeriods = intval(DATA::__GetPOST('postPeriods', true, true));
    $postNotes = DATA::__GetPOST('postNotes', true, true);
    $periodName = $sql->Select(['name'])->From('gperiod')->Where('id='.$postPeriods)->Query()[0]['name'];
    $periodName = ucfirst($periodName);
    $sy_id = ACADYEAR::__getDefaultID();
    $sql = new DB();
    $sql->Select()
            ->From('d_course_gperiod')
            ->Where('sy_id='.$sy_id.' '
                    . 'AND course_id='.$COURSE_INFOS['id'].' '
                    . 'AND gperiod_id='.$postPeriods);
    $p_exists = count($sql->Query()) > 0;
    FLASH::addFlash($periodName.' has been successfully added!', Index::__GetPage(), 'PROMPT', true);
    if ($p_exists) {
        FLASH::addFlash('You already have ' . $periodName . ' grading period', Index::__GetPage(), 'ERROR', true);
    }
    if (FLASH::__getType()=='PROMPT') {
        $sql = new DB();
        $sql->Select()
                ->From('d_course_gperiod')
                ->Where('sy_id='.$sy_id.' '
                        . 'AND course_id='.$COURSE_INFOS['id']);
        $is_current = 1;
        if (count($sql->Query()) > 0) {
            $is_current = 0;
        }
        
        $sql->InsertInto('d_course_gperiod', array(
                    'course_id', 'gperiod_id', 'sy_id', 'notes', 'is_current'
                ))
                ->Values(array(
                    $COURSE_INFOS['id'], $postPeriods, $sy_id, $postNotes, $is_current
                ), [ 3 ]);
        $is_success = $sql->Execute()->__IsSuccess();
        
        if (!$is_success) {
            die($sql->query);
            FLASH::addFlash('Something went wrong. Geeks are on their way to fix it. Please try again.', Index::__GetPage(), 'ERROR', true);
        }
        UI::RedirectTo(Index::__GetPage(), array(
            'MODE' => 'NOTHING'
        ));
    }
}


// [process] Preparing periods report
$sy_id = ACADYEAR::__getDefaultID();

$sql = new DB();
$sql->Select(array(
    'd_course_gperiod.id', 'd_course_gperiod.is_current', 'gperiod.name', 'd_course_gperiod.notes'
))->From('d_course_gperiod, gperiod')
        ->Where('d_course_gperiod.gperiod_id=gperiod.id '
                . 'AND course_id='.$COURSE_INFOS['id'].' '
                . 'AND sy_id='.$sy_id);

$result_Gradingperiods = $sql->Query();
$report_Gradingperiods = new MYSQLREPORT();
$report_Gradingperiods
        ->setReportProperties(array(
            'width' => '100%',
            'align' => 'left'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [ 
                'CAPTION' => 'hidden_ISCURRENT',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Grading period',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Notes',
                'LIMIT' => 60,
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Actions',
                'DEFAULT' =>
                    UI::Button('Delete', 'button', 'btn btn-danger btn-sm',
                            UI::GetPageUrl(Index::__GetPage(), 
                                    array( 'MODE'=>'REQ_DELETE', 'TARGET_ID'=>'{1}' )), false),
                'class' => 'rpt-header'
            ], [
                'CAPTION' => '',
                'LISTOFVALUES' => array(
                    0 => UI::Button('Set default', 'button', 'btn btn-warning btn-sm',
                            UI::GetPageUrl(Index::__GetPage(), 
                                    array( 'MODE'=>'REQ_DEFAULT', 'TARGET_ID'=>'{1}' )), false),
                    1 => '<b>Default</b>'
                ),
                'DEFAULT' => '{2}',
                'class' => 'rpt-header'
            ]
        ))
        ->setReportCellstemplate(array(
            [], [], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ]
        ))
        ->loadResultdata($result_Gradingperiods)
        ->defineEmptyMessage('No existing grading period!')


?>