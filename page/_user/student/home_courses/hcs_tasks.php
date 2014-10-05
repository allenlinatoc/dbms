<?php

$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');


// Preparing reports
$sql = new DB();
$sql
        ->Select([ 'id', 'title', 'message', 'deaddate' ])
        ->From('task')
        ->Where('course_id='.$COURSE_INFOS['id'].' '
                . 'AND period_id='.ACADYEAR::__getDefaultGradingPeriod($COURSE_INFOS['id'])['id'].' '
                . 'AND sy_id='.ACADYEAR::__getDefaultID())
        ->OrderBy('deaddate', DB::ORDERBY_ASCENDING);
$result = $sql->Query();

$report_Studenttasks = new MySQLReport();
$report_Studenttasks
        ->setReportProperties(array(
            'width' => '100%',
            'align' => 'center'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Title',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Description',
                'LIMIT' => 60,
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Deadline',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => '',
                'DEFAULT' =>
                    UI::Button('View', 'button', 'btn btn-primary btn-small btn-marginized'
                            , UI::GetPageUrl('user-tasks', array(
                                'TASK_ID' => '{1}'
                            ))
                            , false),
                'class' => 'rpt-header'
            ]
        ))
        ->setReportCellstemplate(array(
            [], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ]
        ))
        ->loadResultdata($result)
        ->defineEmptyMessage('Your instructor is not yet giving any task/assignment.');

?>