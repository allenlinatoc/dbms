<?php

$SCHEME_ID = null;
$SCHEME_INFOS = array();
$SCHEME_COMPONENTS = array();

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- FILTERED
    
    $SCHEME_ID = DATA::__GetIntent('SCHEME_ID');
    $sql = new DB();
    $sql->Select()->From('gscheme')->Where('id=' . $SCHEME_ID);
    $result = $sql->Query();
    if (count($result) == 0) {
        FLASH::addFlash('Scheme provided does not exist', 'instructor-gschemes', 'ERROR', true);
        UI::RedirectTo('instructor-gschemes');
    }
    // Else, let the Grading scheme's Components be provided/generated
    
    if (DATA::__GetIntent('IS_DELETE', true)) {
        if (DATA::__GetIntent('IS_DELETE')=='true') {
            // Scheme deletion proper
            if (DATA::__GetIntent('IS_PROCEED')=='true') {
                $sql = new DB();
                $sql->DeleteFrom('gscheme')
                    ->Where('id=' . DATA::__GetIntent('SCHEME_ID'));
                $rowsaffected = $sql->Execute()->rows_affected;
                FLASH::clearFlashes();
                if ($rowsaffected > 0) {
                    # On SUCCESS deletion,
                    FLASH::addFlash('Scheme has been successfully deleted!', 
                            'instructor-gschemes', 'PROMPT');
                } else {
                    # On FAILED deletion
                    FLASH::addFlash('Something went wrong, geeks are now on their ways to fix it.', 'instructor-gschemes', 'ERROR');
                }
                UI::RedirectTo('instructor-gschemes');
            } else if (DATA::__GetIntent('IS_PROCEED')=='false') {
                # If CANCEL the Deletion
                DATA::DeleteIntents(array(
                    'IS_PROCEED', 'IS_DELETE'
                ));
            }
        } else {
            DATA::DeleteIntent('IS_DELETE');
        }
    }
} else {
    UI::RedirectTo('instructor-gschemes');
}

# ---- Getting Infos of this [SCHEME_ID]
$sql = new DB();
$sql->Select()
    ->From('gscheme')
    ->Where('id=' . $SCHEME_ID);
$SCHEME_INFOS = $sql->Query()[0];
DATA::CreateIntent('SCHEME_INFOS', $SCHEME_INFOS);

# ---- Getting COMPONENTS of this [SCHEME_ID]
$sql = new DB();
$sql->Select(['id', 'name', 'percentage', 'notes'])
    ->From('gschemecomponent')
    ->Where('gscheme_id=' . $SCHEME_ID);
$SCHEME_COMPONENTS = $sql->Query();


# ---- Reports generation
$rptComponents = new MySQLReport();
$rptComponents->setReportProperties([
    'align' => 'center',
    'width' => '100%'
])->setReportHeaders(array(
    [
        'CAPTION' => 'ID',
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Name',
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Percentage',
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Notes',
        'class' => 'rpt-header'
    ]
))->setReportCellstemplate(array(
    [], [], [], []
));

$rptComponents->loadResultdata($SCHEME_COMPONENTS);
$rptComponents->defineEmptyMessage('No existing components.');
?>