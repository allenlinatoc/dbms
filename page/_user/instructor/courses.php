<?php

# ---- Open passages for all pages related to INSTRUCTOR-COURSES-HOME
DATA::openPassages([
    'user-home',
    'user-courses-home',
    'user-courses-messageboard',
    'user-profile',
    'instructor-courses',
    'instructor-courses-home-about',
    'instructor-courses-home-attendance',
    'instructor-courses-home-gperiods',
    'instructor-courses-home-gposting',
    'instructor-courses-home-gposting-form',
    'instructor-courses-home-gview',
    'instructor-courses-home-tasks',
    'instructor-courses-home-tasks-form',
    'instructor-courses-home-spending',
    'instructor-courses-home-sbanned',
    'instructor-courses-home-students'
]);

// report preparation
$db = new DB();
$sqlResult = $db->Select([
    'id', 'name', 'code', 'description'
])->From('course')->Query();
$rptCourses = new MySQLReport();
$rptCourses->setReportProperties(array(
    'width' => '100%',
    'align' => 'center'
))->setReportHeaders(array(
    [
        'CAPTION' => 'hidden_ID',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'hidden_NAME',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'Code',
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Name',
        'DEFAULT' => UI::Button('{2}', 'button', 'btn btn-warning btn-xs', UI::GetPageUrl('user-courses-home', [
                    'COURSE_ID' => '{1}'
                ]), false),
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Description',
        'LIMIT' => 60,
        'class' => 'rpt-header'
    ]
))->setReportCellstemplate(array([], [],
    [
        'class' => 'rpt-cell-lined'
    ], [
        'class' => 'rpt-cell-lined'
    ], [
        'class' => 'rpt-cell-lined'
]));
$rptCourses->loadResultdata($sqlResult);
$rptCourses->defineEmptyMessage('No existing courses');
?>