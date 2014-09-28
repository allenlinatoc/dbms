<?php

$TASK_INFOS = array();
$TASK_ATTACHMENTS = array();

DATA::openPassage('user-taskattachment', true, false);

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    if (DATA::__HasIntentData('TASK_ID')) 
    {
        // get information about this TASK
        $sql = new DB();
        $sql->Select()
                ->From('task')
                ->Where('id='.DATA::__GetIntent('TASK_ID'))
                ->Limit(1);
        $TASK_INFOS = $sql->Query()[0];
        
        // get attachments
        $sql = new DB();
        $sql->Select()
                ->From('taskattachment')
                ->Where('task_id='.$TASK_INFOS['id']);
        $TASK_ATTACHMENTS = $sql->Query();
    }
}



// Generating reports
$sql = new DB();
$sql->Select([ 'id', 'concat(profile.fname,\' \',profile.lname) AS fullname', 'datetime' ])
        ->From('taskentry')
        ->Where('task_id='.$TASK_INFOS['id'].' '
                . 'AND is_accepted=0');
$unchecked = $sql->Query();

$report_Unchecked = new MySQLReport();
$report_Unchecked
        ->setReportProperties(array(
            'align' => 'center',
            'width' => '100%'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hidden_STUDENT_FULLNAME',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Student\'s name',
                'DEFAULT' => UI::makeNavigationLink($text, $url)
            ]
        ))
        ->setReportCellstemplate(array(
            [], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ]
        ))


?>