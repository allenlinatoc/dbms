<?php

// Initialize parameters
$DCG_ID = null;
$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
$PERIOD_INFOS = null;


// Indicators and Data
$a_students = array(); // contents of $a_students varies



if (DATA::__IsPassageOpen())
{
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    
    if (DATA::__HasIntentData('DCG_ID'))
    {
        // Catch DCG_ID
        $DCG_ID = DATA::__GetIntentSecurely('DCG_ID');
        
        // Get DCG_ID infos and put it to $PERIOD_INFOS
        $sql = new DB();
        $sql    ->Select()
                ->From('gperiod, d_course_gperiod')
                ->Where('d_course_gperiod.gperiod_id=gperiod.id '
                        . 'AND d_course_gperiod.id='.$DCG_ID)
                ->Limit(1);
        $PERIOD_INFOS = $sql->Query()[0];
        $PERIOD_INFOS['id'] = $DCG_ID;
        
        if ( DATA::__HasIntentData('MODE') )
        {
            $MODE = DATA::__GetIntentSecurely('MODE');
            // Switch conditions base on MODE
            if ( $MODE == 'REQ_EDIT' )
            {
                $sql = new DB();
                $sql
                        ->Select([
                            'd_student_grades.student_id AS id',
                            'profile.fname',
                            'profile.lname',
                            'd_student_grades.value'
                        ])
                        ->From('profile,d_student_grades')
                        ->Where('dcg_id='.$DCG_ID.' '
                                . 'AND course_id='.$COURSE_INFOS['id'].' '
                                . 'AND profile.user_id=d_student_grades.student_id');
                $a_students = $sql->Query();
            }
        }
        else {
            // Get all student names and IDs
            $sql = new DB();
            $sql    ->Select([ 'profile.user_id AS id', 'profile.fname', 'profile.lname' ])
                    ->From('profile, d_student_course')
                    ->Where('profile.user_id = d_student_course.student_id '
                            . 'AND course_id = '.$COURSE_INFOS['id'].' '
                            . 'AND sy_id = '.ACADYEAR::__getDefaultID().' '
                            . 'AND d_student_course.status!=2');
            $a_students = $sql->Query();
        }
    }
}


// Catch POST-data retrieval

$is_success = false; // initialized marker
if ( DATA::__HasPostData() )
{
    if (DATA::__GetIntentSecurely('MODE')=='REQ_EDIT')
    {
        foreach ($a_students as $student) 
        {
            $grade = DATA::__GetPOST('postStudent'.$student['id'], TRUE, TRUE);
            if (STR::__IsBlank($grade, TRUE) || is_null($grade))
            {
                $grade = 'null';
            }
            
            $sql = new DB();
            $sql
                    ->Update('d_student_grades')
                    ->Set(array(
                        'value' => $grade
                    ))
                    ->Where('dcg_id='.$DCG_ID.' '
                            . 'AND course_id='.$COURSE_INFOS['id'].' '
                            . 'AND student_id='.$student['id']);
            $sql->Execute();
            $is_success = $sql->__IsSuccess(null, true);
        }
    }
    else
    {
        foreach ($a_students as $student)
        {
            $grade = DATA::__GetPOST('postStudent'.$student['id'], TRUE, TRUE);
            $a_fields = array( 'dcg_id', 'course_id', 'student_id' );
            $a_values = array( $DCG_ID, $COURSE_INFOS['id'], $student['id'] );
            if (!STR::__IsBlank($grade, TRUE) && !is_null($grade))
            {
                array_push($a_fields, 'value');
                array_push($a_values, $grade);
            }
            $sql = new DB();
            $sql    ->InsertInto('d_student_grades', $a_fields)
                    ->Values($a_values);
            $sql->Execute();
            $is_success = $sql->__IsSuccess();
            if ( !$is_success )
            {
                // If error encountered during adding, delete any other existing records
                $sql = new DB();
                $sql    ->DeleteFrom('d_student_grades')
                        ->Where('dcg_id='.$DCG_ID.' '
                                . 'AND course_id='.$COURSE_INFOS['id']);
                $sql->Execute();
                FLASH::addFlash('An error occured during the process. Please try again later'
                        , Index::__GetPage(), 'ERROR', true);
                break;
            }
        }
    }
    
    if ($is_success)
    {
        $successpage = 'instructor-courses-home-gposting';
        FLASH::addFlash('Grades have been successfully posted!', 'instructor-courses-home-gposting', 'PROMPT', true);
        DATA::DeleteIntents(array(
            'MODE', 'DCG_ID'
        ), FALSE, TRUE);
        UI::RedirectTo('instructor-courses-home-gposting');
    }
}

?>