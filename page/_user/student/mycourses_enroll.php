<?php

// Check for enrollment setting
$config = new CONFIG(DIR::$CONFIG . SYS::$CONFIG_SYSTEM_FILENAME);
if (!$config->IsTrue('enable_enrollment', 'enable'))
{
    UI::RedirectTo('home');
}


/**
 * We don't allow SUCCESS flashes during Startup of this page */
if (FLASH::__getType()=='PROMPT') {
    FLASH::clearFlashes();
}

$postCoursecode = '';

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- Filtered
    if (strtolower(DATA::__GetIntent('IS_ALLOW'))!='true') {
        FLASH::addFlash('An error occured. Geeks are on their way to fix it.', 'student-mycourses', 'ERROR', true);
        UI::RedirectTo('student-mycourses');
    }
    
    if (DATA::__HasPostData('postCoursecode')) {
        # Process the submitted data
        $postCoursecode = DATA::__GetPOST('postCoursecode');
        $sql = new DB();
        $sql->Select()
            ->From('course')
            ->Where('code LIKE \'%' . $postCoursecode . '%\'');
        $result = $sql->Query();
        FLASH::checkAndAdd(array(
            '<b>' . $postCoursecode . '</b> is not a valid course code' => !(STR::IsFormatted($postCoursecode, '*****-*****')),
            'The entered course code does not exist. Please try again.' => count($result) <= 0
        ), 'Verification success, flashes after this action should be cleared (debug).', 'student-mycourses-enroll', 'student-mycourses-enroll', true);
        
        if (FLASH::__getType()=='PROMPT') {
            DATA::SetIntent('COURSE_INFOS', $result[0]);
            UI::RedirectTo(Index::__GetPage());
        }
    }
    if (DATA::__GetIntent('CANCEL_COURSE', true) && DATA::__GetIntent('CANCEL_COURSE')=='true') {
        # On Cancel, delete COURSE_INFOS intent
        DATA::DeleteIntent('COURSE_INFOS');
        DATA::DeleteIntent('CANCEL_COURSE');
    }
    
    if (DATA::__GetIntent('IS_ENROLL', true) && DATA::__GetIntent('COURSE_INFOS', true) && DATA::__GetIntent('IS_ENROLL')=='true') {
        # If enrollment is allowed
        $COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
        // Get course id
        $courseId = $COURSE_INFOS['id'];
        $student_UID = USER::Get(USER::ID);
        
        # [FILTER] Check first if user/student is already enrolled in this course
        $result_existence = DB::__getRecord('d_student_course', array(
            '`student_id` = ' . $student_UID,
            '`course_id` = ' . $courseId ));
        if (count($result_existence) > 0) {
            // Halt here
            FLASH::addFlash('You are already enrolled to <b>' . $COURSE_INFOS['name'] . '</b>. '
                        . '<a href="'.UI::GetPageUrl('student-mycourses').'">Go back to My courses</a>',
                    Index::__GetPage(), 'ERROR', true);
            DATA::DeleteIntent('IS_ENROLL');
            UI::RedirectTo(Index::__GetPage());
            die();
        }
        
        // Otherwise, continue
        $sql = new DB();
        $sql->Execute('CALL `addStudentCourseEntry`('.$student_UID.', '.$courseId.')');
        if (!is_null($sql->Lasterror)) {
            echo $sql->query . '<br>';
            die($sql->Lasterror);
        }
        if ($sql->rows_affected > 0) {
            FLASH::addFlash(
                    'You have successfully filed an enrollment entry to ' . $COURSE_INFOS['name'] . '! <br>'
                        . 'Entry is now subject to instructor\'s approval.', 
                    'student-mycourses', 'PROMPT', true);
            DATA::DestroyIntents();
        } else {
            # On DB FailureS
            FLASH::addFlash('Something went wrong during course enrollment. Please try again or contact admin.',
                    'student-mycourses', 'ERROR', true);
        }
        UI::RedirectTo('student-mycourses');
    }
} else {
    UI::RedirectTo('student-mycourses');
}

?>