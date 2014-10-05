<?php

$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');

// Preparing report
$sql = new DB();
$sql
        ->Select([ 'gperiod.name', 'd_student_grades.value' ])
        ->From('d_student_grades, d_course_gperiod, gperiod')
        ->Where('d_student_grades.dcg_id = d_course_gperiod.id '
                . 'AND d_course_gperiod.gperiod_id = gperiod.id '
                . 'AND d_student_grades.course_id='.$COURSE_INFOS['id'].' '
                . 'AND d_student_grades.student_id='.USER::Get(USER::ID))
        ->OrderBy('gperiod.id', DB::ORDERBY_ASCENDING);
$result = $sql->Query();

$report_Studentgrade = new MySQLReport();
$report_Studentgrade
        ->setReportProperties(array(
            'width' => '100%',
            'align' => 'center'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'Period',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Grade',
                'class' => 'rpt-header'
            ]
        ))
        ->setReportCellstemplate(array(
            [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ]
        ));
$report_Studentgrade
        ->loadResultdata($result)
        ->defineEmptyMessage('Your instructor is not yet posting any grade record.');

?>