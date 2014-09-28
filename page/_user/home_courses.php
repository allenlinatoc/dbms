<?php

$usertype = strtoupper(USER::Get(USER::TYPE));
$redirect_to = ($usertype=='INSTRUCTOR' ? 'instructor-courses' : 
        ($usertype=='STUDENT' ? 'student-mycourses' : 'home') );

$COURSE_ID = null;
$COURSE_INFOS = array();
if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- FILTERED
    $COURSE_ID = DATA::__GetIntent('COURSE_ID');
    $sql = new DB();
    $sql->Select()
        ->From('course')
        ->Where('id=' . $COURSE_ID);
    $result = $sql->Query();
    if (count($result)==0) {
        FLASH::addFlash('Supplied Course ID does not exist.', [$redirect_to], 'ERROR', true);
        UI::RedirectTo($redirect_to);
    } else {
        $COURSE_INFOS = $result[0];
        DATA::CreateIntent('COURSE_INFOS', $COURSE_INFOS);
    }
    if (DATA::__HasIntentData('REDIRECT_TO')) {
        $redirectTo = DATA::__GetIntentSecurely('REDIRECT_TO');
        DATA::DeleteIntent('REDIRECT_TO', true);
        UI::RedirectTo($redirectTo);
    }
} else {
    UI::RedirectTo($redirect_to);
}

// [process] Fetching data from `notifications`
$sql = new DB();
if (USER::Get(USER::TYPE)=='INSTRUCTOR') {
    $sql->Select()->From('v_notifications')->OrderBy('datetime', DB::ORDERBY_DESCENDING);
} else {
    $sql->Select()->From('v_notifications_student')->OrderBy('datetime', DB::ORDERBY_DESCENDING);;
}
$result_Notifications = $sql->Query();

?>