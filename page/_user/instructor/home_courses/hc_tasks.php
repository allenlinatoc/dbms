<?php

$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');

$sql = new DB();
$sql->Select([ 'id', 'title', 'deaddate' ])
        ->From('task')
        ->Where('sy_id='.ACADYEAR::__getDefaultID().' '
        . 'AND course_id='.$COURSE_INFOS['id']);
$result = $sql->Query();
$report_Tasks = new MYSQLREPORT();
$report_Tasks
        ->setReportProperties(array(
            'align' => 'center',
            'width' => '100%'))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Title',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Deadline',
                'class' => 'rpt-header'
            ]))
        ->setReportCellstemplate(array(
            [], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ]
        ));
$report_Tasks
        ->loadResultdata($result)
        ->defineEmptyMessage('No tasks<br><br><br><br>');

?>