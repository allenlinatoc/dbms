<?php

$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');

$sql = new DB();
$sql
        ->Select([ 'student_id AS id', 'concat(profile.fname,\' \',profile.lname) AS fullname' ])
        ->From('d_student_course,profile')
        ->Where('course_id='.$COURSE_INFOS['id'].' '
                . 'AND sy_id='.ACADYEAR::__getDefaultID().' '
                . 'AND status != 2 '
                . 'AND profile.user_id=d_student_course.student_id');
$studentInfos = $sql->Query(null);

// PREPARING REPORTS ELEMENTS

// Initialize rowdata
$rowdata = array();

// Get all available grading periods (which have POSTED grades)
$sql = new DB();
$result_DCG = $sql->Query(
        'SELECT DISTINCT d_student_grades.dcg_id AS id, gperiod.name AS name '
        . 'FROM d_student_grades, d_course_gperiod, gperiod '
        . 'WHERE d_student_grades.course_id='.$COURSE_INFOS['id'].' '
            . 'AND d_course_gperiod.id=d_student_grades.dcg_id '
            . 'AND d_course_gperiod.gperiod_id=gperiod.id '
        . 'ORDER BY gperiod.id');

// Fill rowdata with students' column values
foreach ($studentInfos as $student)
{
    $row = array();
    array_push($row, $student['id']);
    array_push($row, $student['fullname']);
    
    // fill grades
    foreach ($result_DCG as $DCG)
    {
        $sql = new DB();
        $sql
                ->Select([ 'value' ])
                ->From('d_student_grades')
                ->Where('dcg_id='.$DCG['id'].' '
                        . 'AND course_id='.$COURSE_INFOS['id'].' '
                        . 'AND student_id='.$student['id']);
        $grade = $sql->Query()[0]['value'];
        array_push($row, $grade);
    }
    array_push($rowdata, $row);
}

// Prepare columns
$rptColumns = array(
    [
        'CAPTION' => 'hidden_ID',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'hidden_STUDENTNAME',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'Student',
        'DEFAULT' => UI::makeLink(
                UI::GetPageUrl('user-profile'
                    , array('USER_ID'=>'{1}'))
                , '{2}', true),
        'class' => 'rpt-header'
    ]
);
// Prepare Cell templates
$rptCellTemplates = array(
    [], [
        'class' => 'rpt-cell-hpad rpt-cell-lined'
    ], [
        'class' => 'rpt-cell-hpad rpt-cell-lined'
    ]);

foreach ($result_DCG as $DCG) 
{
    // push column
    $column = array();
    $column['CAPTION'] = $DCG['name'];
    $column['class'] = 'rpt-header';
    array_push($rptColumns, $column);
    // push cell tempalte
    $celltemplate = array();
    $celltemplate['class'] = 'rpt-cell-hpad rpt-cell-lined';
    array_push($rptCellTemplates, $celltemplate);
}

$report_Gradeview = new MySQLReport();
$report_Gradeview
        ->setReportProperties(array(
            'width' => '100%',
            'align' => 'center'
        ))
        ->setReportHeaders($rptColumns)
        ->setReportCellstemplate($rptCellTemplates);
$report_Gradeview
        ->Rowdata = $rowdata;
$report_Gradeview
        ->defineEmptyMessage('No student to be viewed.');


?>