<?php

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    if (DATA::__HasIntentData('MODE') && DATA::__HasIntentData('TARGET_ID')) 
    {
        $MODE = DATA::__GetIntentSecurely('MODE');
        $TARGET_ID = intval(DATA::__GetIntentSecurely('TARGET_ID'));
        $COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
        
        // Get the info about the student first
        $sql = new DB();
        $sql->Select([ 'concat(profile.fname, \' \', profile.lname) AS fullname' ])
                ->From('user, profile')
                ->Where('user.id = profile.user_id '
                        . 'AND user.id = ' . $TARGET_ID);
        
        $studentName = ucwords( $sql->Query()[0]['fullname']) ;
        $message = '';
        $title = '';
        
        if (DATA::__HasIntentData('DIALOG_RESULT')) {
            if (DATA::__GetIntentSecurely('DIALOG_RESULT')==DIALOG::R_AFFIRMATIVE) 
            {
                $acsql = new DB();
                if ($MODE=='REQ_BAN') {
                    $acsql->Query('CALL banStudentCourseEntry('.$TARGET_ID.', '.$COURSE_INFOS['id'].')');
                }
                else if ($MODE=='REQ_REM') {
                    $acsql->Query('CALL deleteStudentCourseEntry('.$TARGET_ID.', '.$COURSE_INFOS['id'].')');
                }
                $is_success = $acsql->Execute()->__IsSuccess();
                if (!$is_success) {
                    die($acsql->query);
                }
                $flashMessage = 'Something went wrong. Geeks are on their way now to work on it.';
                
                // Checking for SUCCESS Flashes
                if ($is_success) {
                    if ($MODE=='REQ_BAN') {
                        $flashMessage = $studentName.' is now banned from this course.';
                    }
                    else if ($MODE=='REQ_REM') {
                        $flashMessage = $studentName.' has now been removed from this course.';
                    }
                }
                FLASH::addFlash($flashMessage, Index::__GetPage(), ($is_success?'PROMPT':'ERROR'), true);
            }
            DATA::DeleteIntents([ 'MODE', 'TARGET_ID', 'DIALOG_RESULT', 'DIALOG_OBJECT' ]
                    , true, true);
        }
        
        if ($MODE=='REQ_BAN') {
            $title = 'Ban student from ' . $COURSE_INFOS['name'];
            $message = 'Are you sure you want to <i>ban</i> '.$studentName.' from course '.$COURSE_INFOS['name'].'?';
        }
        else if ($MODE=='REQ_REM') {
            $title = 'Remove student from ' . $COURSE_INFOS['name'];
            $message = 'Are you sure you want to <i>remove</i> '.$studentName.' from course '.$COURSE_INFOS['name'].'?';
        }
        $dialogObject = new DIALOG($title);
        $dialogObject
                ->SetMessage($message)
                ->SetPageCallback(Index::__GetPage());
        $dialogObject
                ->AddButton(DIALOG::B_YES)
                ->AddButton(DIALOG::B_NO)
                ->AddButton(DIALOG::B_CANCEL);
        $dialogObject->ShowDialog();
    }
}


$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
$sy_id = ACADYEAR::__getDefaultID();

$sql = new DB();
$sql->Select(array(
    'user.id', 'concat(profile.fname, \' \', profile.lname, \' (\', user.username, \')\')'))
        ->From('user, profile, d_student_course')
        ->Where('user.id = d_student_course.student_id '
                . 'AND profile.id = user.id '
                . 'AND d_student_course.course_id = ' . $COURSE_INFOS['id'] . ' '
                . 'AND d_student_course.status = 0 '
                . 'AND d_student_course.sy_id = ' . $sy_id);

$result_EnrolledStudents = $sql->Query();

$report_EnrolledStudents = new MYSQLREPORT();
$report_EnrolledStudents
        ->setReportProperties(array(
            'align' => 'center',
            'width' => '100%'
        ))
        ->setDBobject($sql)
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Student\'s name',
                'align' => 'left',
                'class' => 'rpt-header',
                'width' => '50%'
            ], [
                'CAPTION' => 'Action',
                'DEFAULT' => 
                    UI::Button('Ban', 'button', 'btn btn-warning btn-xs', 
                            UI::GetPageUrl(Index::__GetPage(), [ 'MODE' => 'REQ_BAN', 'TARGET_ID' => '{1}' ]), false)
                  . UI::Button('Remove', 'button', 'btn btn-danger btn-xs',
                            UI::GetPageUrl(Index::__GetPage(), [ 'MODE' => 'REQ_REM', 'TARGET_ID' => '{1}' ]), false),
                'align' => 'left',
                'class' => 'rpt-header',
                'width' => '50%'
            ]
        ))
        ->setReportCellstemplate(array(
            [],[
                'class' => 'rpt-cell-lined rpt-cell-lpad',
                'align' => 'left'
            ], [
                'class' => 'rpt-cell-lined rpt-cell-lpad',
                'align' => 'left'
            ]
        ))
        ->loadResultdata($result_EnrolledStudents)
        ->defineEmptyMessage('No enrolled student yet.');

DATA::openPassage(Index::__GetPage(), true);

?>