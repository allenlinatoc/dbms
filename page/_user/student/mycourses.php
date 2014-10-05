<?php

DATA::openPassages([
    'student-action-cancelpendingcourse',
    'student-gradereports',
    'student-mycourses-enroll',
    'student-mycourses-gradeview',
    'student-mycourses-tasks',
    'student-mycourses-tasks-submit',
    'student-tasks',
    'user-courses-home',
    'user-courses-messageboard',
    'user-myaccount',
    'user-profile',
    'user-tasks',
    'user-taskattachment'
]);

/**
 * PROCESS: Preparation of Reports for PENDING COURSE ENROLLMENTS
 */
$sql = new DB();
$sql->Select([
        'd_student_course.id', 'name', 'description'
    ])
    ->From('course')
    ->InnerJoin('d_student_course')
    ->On('course.id = d_student_course.course_id')
    ->Where('d_student_course.student_id=' . USER::Get(USER::ID)
            . ' AND d_student_course.status=2');
$result_PendingCourses = $sql->Query();

$rptPendingCourses = new MySQLReport();
$rptPendingCourses->setReportProperties(array(
    'align' => 'center',
    'width' => '100%'
))->setReportHeaders(array(
    [
        'CAPTION' => 'hidden_COURSEENTRYID',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'hidden_COURSENAME',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'Course name',
        'DEFAULT' => '<b>{2}</b>',
    ], [
        'CAPTION' => 'Description',
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Action',
        'DEFAULT' => UI::Button('Cancel request', 'button', 'btn btn-danger btn-xs', 
                UI::GetPageUrl('student-action-cancelpendingcourse', array(
                    'TARGET_ENTRY_ID' => '{1}'
                )), false),
        'class' => 'rpt-header'
    ]
))->setReportCellstemplate(array(
    [], [
        'class' => 'rpt-cell-lined rpt-cell-mpad'
    ], [
        'class' => 'rpt-cell-lined rpt-cell-mpad'
    ], [
        'class' => 'rpt-cell-lined rpt-cell-mpad'
    ], [
        'class' => 'rpt-cell-lined rpt-cell-mpad'
    ]
));
$rptPendingCourses->loadResultdata($result_PendingCourses);
$rptPendingCourses->defineEmptyMessage('You have no pending enrollment request at the moment.');

/**
 * PROCESS: Preparation of Reports for ENROLLED COURSES
 */
$sql = new DB();
$sql->Select([
        'course.id', 'name', 'description'
    ])
    ->From('course')
    ->InnerJoin('d_student_course')
    ->On('course.id = d_student_course.course_id')
    ->Where('d_student_course.student_id=' . USER::Get(USER::ID)
            . ' AND d_student_course.status=0');
$result_EnrolledCourses = $sql->Query();

$rptEnrolledCourses = new MySQLReport();
$rptEnrolledCourses->setReportProperties([
    'align' => 'center',
    'width' => '100%'
])->setReportHeaders(array(
    [
        'CAPTION' => 'hidden_COURSEID',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'hidden_COURSENAME',
        'HIDDEN' => true,
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Course name',
        'DEFAULT' => UI::Button('{2}', 'button', 'btn btn-warning btn-xs', UI::GetPageUrl('user-courses-home', ['COURSE_ID'=>'{1}']), false),
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Description',
        'LIMIT' => '60',
        'class' => 'rpt-header',
        'width' => '100%'
    ]
))->setReportCellstemplate(array(
    [], [
        'class' => 'rpt-cell-lined rpt-cell-mpad'
    ], [
        'class' => 'rpt-cell-lined rpt-cell-mpad'
    ], [
        'class' => 'rpt-cell-lined',
        'width' => '70%'
    ]
));

$rptEnrolledCourses->loadResultdata($result_EnrolledCourses);
$rptEnrolledCourses->defineEmptyMessage('You are not yet enrolled in any course.');

?>