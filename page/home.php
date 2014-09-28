<?php

/**
 * PROCESS: Redirect logged-in users to their respective landing pages
 */
if (USER::IsLoggedin()) {
    UI::RedirectTo(User::GetLandpage());
}

/**
 * PROCESS: Fetch $_GET data; Turn into INTENTS
 */
if (DATA::__HasGetData('IS_CANCELCOURSE')) {
    DATA::openPassage('home');
    DATA::GenerateIntentsFromGET();
    # ---- Filtered
    if (DATA::__GetIntent('IS_CANCELCOURSE')=='true') {
        DATA::DestroyIntents();
        UI::RedirectTo(Index::__GetPage());
    }
}

// Course code initialization
$postCoursecode = '';
$isCourseCodeSettled = DATA::__GetIntent('IS_COURSE_CODE_SETTLED');

if (DATA::__HasPostData()) {
    /**
     * PROCESS: For course code inquiry
     */
    if (DATA::__HasPostData('postCoursecode')) {
        $postCoursecode = DATA::__GetPOST('postCoursecode', true, true, false);
        $sql = new DB();
        $sql->Select()
            ->From('course')
            ->Where('code LIKE "%' . $postCoursecode . '%"');
        $result_EnrolledCourses = $sql->Query();
        FLASH::clearFlashes();
        FLASH::checkAndAdd(array(
            '<b>' . $postCoursecode . '</b> is an invalid course code.' => !STR::IsFormatted($postCoursecode, '*****-*****'),
            'Course code does not exist' => !(count($result_EnrolledCourses) > 0)
        ), 'Course code verification success! Developer should add redirection to this part', ['student-mycourse'], ['home'], false);
        // If COURSE_CODE Validation is success
        if (FLASH::__getType()=='PROMPT') {
            FLASH::clearFlashes();
            DATA::openPassage('home');
            DATA::CreateIntent('IS_COURSE_CODE_SETTLED', true);
            DATA::CreateIntent('COURSE_CODE', $postCoursecode);
            DATA::CreateIntent('COURSE_NAME', $result_EnrolledCourses[0]['name']);
            UI::RedirectTo('home');
        }
    }
    /**
     * PROCESS: User authentication (with Course enrollment processing)
     */
    if (DATA::__HasPostData(['postUsername', 'postPassword'])) {
        
        $postUsername = DATA::__GetPOST('postUsername', true, true, true);
        $postPassword = DATA::__GetPOST('postPassword', true, true);
        FLASH::checkAndAdd(array(
            'Username should only contain letters and numbers' => ctype_punct($postUsername) || ctype_space($postUsername)),
                'Validation success! Developers should add data redirection in this :D', 
                Index::__GetPage(), Index::__GetPage(), true);

        if (FLASH::__getType() == 'PROMPT') {
            $auth_result = ACCOUNTS::Authenticate($postUsername, $postPassword);
            if ($auth_result['IS_SUCCESS']) {
                if (USER::Get(USER::TYPE) != 'SUPERADMIN') {
                    if (!ACADYEAR::__hasSchoolYear()) {
                        FLASH::addFlash('No existing school year (SY) in system. Please contact system admin for further assistance.'
                                , Index::__GetPage(), 'ERROR', true);
                        USER::DestroySession();
                        UI::RefreshPage();
                    }
                    else if (ACADYEAR::__getDefaultID()==-1) {
                        FLASH::addFlash('No default school year (SY) defined in system. Please contact system admin for further assistance.'
                                , Index::__GetPage(), 'ERROR', true);
                        USER::DestroySession();
                        UI::RefreshPage();
                    }
                }
                # If subject to COURSE ENROLLMENT and user logged in as STUDENT
                if (DATA::__GetIntent('IS_COURSE_CODE_SETTLED')=='true'
                        && $auth_result['USERTYPE'] == 'STUDENT') {
                    FLASH::clearFlashes();
                    $sql = new DB();
                    $sql->Select()
                        ->From('course')
                        ->Where('code="' . DATA::__GetIntent('COURSE_CODE') . '"');
                    $COURSE_ID = $sql->Query()[0]['id'];
                    $sql = new DB();
                    $procedureCall = 'CALL `addStudentCourseEntry`(' . $auth_result['USERID'] . ', ' . $COURSE_ID . ')';
                    $sql->Execute($procedureCall);
                    if ($sql->rows_affected > 0) {
                        FLASH::addFlash(
                                '<p>You have successfully filed an enrollment entry to ' . DATA::__GetIntent('COURSE_NAME') . '!<br>'
                                    . 'Entry is now subject to instructor\'s approval.</p>', 
                                'student-mycourses', 'PROMPT', true);
                        UI::RedirectTo('student-mycourses');
                    } else {
                        # On DB FailureS
                        FLASH::addFlash('Something went wrong during course enrollment. Please try again or contact admin.',
                                ['home', 'logout'], 'ERROR', true);
                        UI::RedirectTo('logout');
                    }
                } 
                # If PLAIN AUTHENTICATION is success
                else {
                    UI::RedirectTo(USER::GetLandpage());
                    // check if there is an existing SY first
                    
                    DATA::DestroyIntents(); // Reset all intents
                }
            }
            else if (is_int($auth_result)) {    
                $err_message = $auth_result == 1 ?
                        'Your account was blocked. Please contact the system admin for further assistance.'
                      : 'Your account is still subject to admin\'s approval.';
                FLASH::addFlash($err_message, ['home'], 'ERROR', true);
            }
            else {
                FLASH::addFlash('Wrong username or password, please try again',
                        Index::__GetPage(), 'ERROR', true);
            }
        }
    }
}

// Checking for default SY entry
$sql = new DB();
$sql->Select()
        ->From('sy')
        ->Where('is_default=1');
$result = $sql->Query();
$hasSchoolyear = count($result) > 0;
if ($hasSchoolyear) {
    $result = $result[0];
    $schoolyear = $result['year'] . '-' . (intval($result['year'])+1);
}
?>