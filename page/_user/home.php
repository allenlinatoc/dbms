<?php

$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');

# ---- Open passages for all pages related to INSTRUCTOR-COURSES-HOME
DATA::openPassages([
    'user-home',
    'user-courses-home',
    'user-courses-messageboard',
    'user-myaccount',
    'user-tasks',
    'user-profile',
    'instructor-courses',
    'instructor-courses-home-about',
    'instructor-courses-home-attendance',
    'instructor-courses-home-gperiods',
    'instructor-courses-home-tasks',
    'instructor-courses-home-tasks-form',
    'instructor-courses-home-spending',
    'instructor-courses-home-sbanned',
    'instructor-courses-home-students'
]);

$uc_username = USER::Get(USER::USERNAME);
$uc_type = USER::Get(USER::TYPE);

$sql = new DB();
if ($uc_type=='INSTRUCTOR') {
    $sql
            ->Select()
            ->From('v_notifications')
            ->Where('course_id='.$COURSE_INFOS['id'])
            ->OrderBy('datetime', DB::ORDERBY_DESCENDING);
}
else if ($uc_type=='STUDENT') {
    $sql
            ->Select()
            ->From('v_notifications_student')
            ->Where('course_id='.$COURSE_INFOS['id'])
            ->OrderBy('datetime', DB::ORDERBY_DESCENDING);
}
$result_Notifications = $sql->Query();

?>