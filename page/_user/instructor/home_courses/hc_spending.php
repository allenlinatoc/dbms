<?php

$MODE = null;
$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- Filtered
    if ( DATA::__HasIntentData('COURSE_INFOS') ) {
        $COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
    }
    if (DATA::__HasIntentData('MODE') && DATA::__HasIntentData('TARGET_ID')) {
        $MODE = strtoupper(DATA::__GetIntent('MODE'));
        $TARGET_ID = intval(DATA::__GetIntent('TARGET_ID'));
        $sql = new DB();
        // fetching of Student information
        $STUDENT_INFOS = $sql->Select()->From('user, profile')->Where('user.id=profile.user_id')->Query();
        if (count($STUDENT_INFOS) > 0) {
            $STUDENT_INFOS = $STUDENT_INFOS[0];
        }
        $studentFname = $STUDENT_INFOS['fname'];
        $studentLname = $STUDENT_INFOS['lname'];
        $is_success = false;
        $sql = new DB();
        if ($MODE === 'REQ_ACCEPT') {
            $sql->
                    Update('d_student_course')->
                    Set(array('status' => USER::STUDENT_ACTIVE_COURSE_STATUS))->
                    Where('student_id = ' . $TARGET_ID . ' '
                            . 'AND course_id = ' . $COURSE_INFOS['id'].' '
                            . 'AND sy_id = ' . ACADYEAR::__getDefaultID() );
            
            $is_success = $sql->Execute()->__IsSuccess();
            if (!is_null($sql->Lasterror)) {
                die($sql->Lasterror);
            }
            if ($is_success) {
                FLASH::addFlash($studentFname . ' ' . $studentLname . '\'s request has been accepted!', Index::__GetPage(), 'PROMPT', true);
            }
        }
        else if ($MODE === 'REQ_REJECT') {
            $sql->Query('CALL deleteStudentCourseEntry('.$TARGET_ID.', '.$COURSE_INFOS['id'].')');
            $is_success = $sql->Execute()->__IsSuccess();
            if ($is_success) {
                FLASH::addFlash($studentFname . ' ' . $studentLname . '\'s request has been rejected!', Index::__GetPage(), 'PROMPT', true);
            }
        }
        else if ($MODE ==='REQ_BAN') {
            $sql->Query('CALL banStudentCourseEntry('.$TARGET_ID.', '.$COURSE_INFOS['id'].')');
            
            $is_success = $sql->Execute()->__IsSuccess();
            if ($is_success) {
                FLASH::addFlash($studentFname . ' ' . $studentLname . ' is now banned from this course!', Index::__GetPage(), 'PROMPT', true);
            }
        }
        
        if (!$is_success) {
            FLASH::addFlash('Something went wrong, geeks are on their way to fix the error.', Index::__GetPage(), 'ERROR', true);
        }
        DATA::DeleteIntents(['MODE', 'TARGET_ID'], true, true);
    }
}


// [PROGRESS] -- generate pending student entries report
$sy_id = ACADYEAR::__getDefaultID();
$sql = new DB();
$sql->Select([
        'user.id',
        'concat(profile.lname, \', \', profile.fname, \' (\', user.username, \')\')'
    ])
    ->From('user,profile,course,d_student_course')
    ->Where('user.id=profile.user_id '
            . 'AND user.id = d_student_course.student_id '
            . 'AND d_student_course.status = ' . USER::STUDENT_PENDING_COURSE_STATUS . ' '
            . 'AND d_student_course.course_id = course.id '
            . 'AND d_student_course.course_id = ' . $COURSE_INFOS['id'] . ' '
            . 'AND d_student_course.sy_id = ' . $sy_id);

$result_Pendingstudents = $sql->Query();

$rptPendingstudents = new MySQLReport();
$rptPendingstudents->setReportProperties(array(
        'align' => 'center',
        'width' => '100%'
    ))->setReportHeaders(array(
        [
            'CAPTION' => 'hidden_ID',
            'HIDDEN' => true
        ], [
            'CAPTION' => 'Student\'s name',
            'class' => 'rpt-header'
        ], [
            'CAPTION' => 'Action',
            'DEFAULT' => 
                UI::Button('Accept', 'button', 'btn btn-warning btn-marginized btn-xs',
                        UI::GetPageUrl(Index::__GetPage(), array(
                            'MODE' => 'REQ_ACCEPT',
                            'TARGET_ID' => '{1}'
                        )), 
                    FALSE) .
                UI::Button('Reject', 'button', 'btn btn-primary btn-marginized btn-xs',
                        UI::GetPageUrl(Index::__GetPage(), array(
                            'MODE' => 'REQ_REJECT',
                            'TARGET_ID' => '{1}'
                        )),
                    FALSE) .
                UI::Button('Ban', 'button', 'btn btn-danger btn-marginized btn-xs', 
                        UI::GetPageUrl(Index::__GetPage(), array(
                            'MODE' => 'REQ_BAN',
                            'TARGET_ID' => '{1}'
                        )), 
                    FALSE),
            'class' => 'rpt-header'
        ]
    ))->setReportCellstemplate(array(
        [], [
            'class' => 'rpt-cell-lined'
        ], [
            'class' => 'rpt-cell-lined'
        ]
    ));
$rptPendingstudents->defineEmptyMessage('No pending course enrollment entry at the moment.');
$rptPendingstudents->loadResultdata($result_Pendingstudents);

?>