<?php

$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
$SB_COURSE_NAME = !is_null(DATA::__GetIntent('COURSE_INFOS')) ?
        DATA::__GetIntent('COURSE_INFOS')['name'] : null;

// verify if instructor has existing grading period,
//  otherwise, redirect the instructor to grading period dashboard
$sql = new DB();
$sql->Select()
        ->From('d_course_gperiod')
        ->Where('course_id='.$COURSE_INFOS['id'].' '
                . 'AND sy_id='.ACADYEAR::__getDefaultID());
if (count($sql->Query()) <= 0 && Index::__GetPage()!='instructor-courses-home-gperiods') {
    FLASH::addFlash('You don\'t have any grading period. Start by creating one.', 'instructor-courses-home-gperiods', 'PROMPT', true);
    UI::RedirectTo('instructor-courses-home-gperiods');
}

?>