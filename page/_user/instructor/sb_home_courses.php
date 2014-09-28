<?php

$SB_COURSE_NAME = !is_null(DATA::__GetIntent('COURSE_INFOS')) ?
        DATA::__GetIntent('COURSE_INFOS')['name'] : null;

// validate if this student is banned from accessing this course
//      or not
if (USER::Get(USER::TYPE)=='STUDENT') 
{
    $sql = new DB();
    $sql->Select()
            ->From('d_student_course,course')
            ->Where('student_id='.USER::Get(USER::ID).' '
                    . 'AND d_student_course.course_id=course.id '
                    . 'AND course_id='.$COURSE_INFOS['id'].' '
                    . 'AND sy_id='.ACADYEAR::__getDefaultID());
    $courseResult = $sql->Query()[0];
    
    if ( intval($courseResult['status'])==1 )
    {
        FLASH::addFlash('You are banned from course '.$courseResult['name'], array(), 'ERROR', true);
    }
}

?>