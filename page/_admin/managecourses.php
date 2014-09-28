<?php

$MODE = null;
// retrieve actions
if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- Filtered
    #
    if (DATA::__HasIntentData('DIALOG_RESULT') && DATA::__HasIntentData('MODE')) {
        $dialogResult = DATA::__GetIntent('DIALOG_RESULT');
        $MODE = DATA::__GetIntent('MODE');
        $TARGET_ID = DATA::__GetIntent('TARGET_ID');
        if ($dialogResult == DIALOG::R_AFFIRMATIVE) {
            if ($MODE == 'DELETE') {
                $sql = new DB();
                $sql->
                        DeleteFrom('course')->
                        Where('course.id = ' . $TARGET_ID);
                $is_success = $sql->Execute()->__IsSuccess();
                $successmsg = $is_success ? 
                        'Course has been successfully deleted.'
                      : 'No course has been deleted.';
            }
            else {
                $sql = new DB();
                $coursename = $sql->Select(['name'])->From('course')->Where('course.id=' . $TARGET_ID)->Query()[0]['name'];
                $sql = new DB();
                $sql->
                        Update('course')->
                        Set(array(
                            'is_active' => ($MODE=='LOCK' ? 0 : 1)
                        ))->
                        Where('course.id = ' . $TARGET_ID);
                $is_success = $sql->Execute()->__IsSuccess();
                $successmsg = $is_success ?
                        $coursename . ' has been successfully ' . ($MODE=='LOCK' ? 'locked' : 'unlocked') . '!'
                      : 'No course has been ' . ($MODE=='LOCK' ? 'locked.' : 'unlocked.');
            }
            FLASH::addFlash($successmsg, Index::__GetPage(), 'PROMPT', true);
        }
        DATA::DeleteIntents(array(
            'MODE', 'TARGET_ID', 'DIALOG_RESULT', 'DIALOG_OBJECT'
        ), true);
    }
    if (DATA::__HasIntentData('MODE')) {
        $MODE = DATA::__GetIntent('MODE');
        $TARGET_ID = DATA::__GetIntent('TARGET_ID');
        $sql = new DB();
        $sql->
                Select()->
                From('course')->
                Where('id = ' . $TARGET_ID);
        $result_Course = $sql->Query()[0];
        $dialogObject = new DIALOG('DIALOG_NO_TITLE');
        if ($MODE == 'DELETE') {
            $dialogObject->
                    SetTitle('Delete course')->
                    SetMessage('Are you sure you want to delete the course '
                        . $result_Course['name'] . '?<br>'
                        . '<i>This will delete all data related to this course</i>')->
                    SetPageCallback(Index::__GetPage());
        }
        else if ($MODE == 'LOCK') {
            $dialogObject->
                    SetTitle('Lock course')->
                    SetMessage('Are you sure you want to LOCK the course '
                        . $result_Course['name'] . '?<br>'
                        . '<i>Doing this will prevent the instructor and students from accessing this course.</i>')->
                    SetPageCallback(Index::__GetPage());
        }
        else if ($MODE == 'UNLOCK') {
            $dialogObject->
                    SetTitle('Unlock course')->
                    SetMessage('Are you sure you want to UNLOCK the course '
                        . $result_Course['name'] . '?')->
                    SetPageCallback(Index::__GetPage());
        }
        $dialogObject->
                AddButton(DIALOG::B_YES)->
                AddButton(DIALOG::B_NO)->
                AddButton(DIALOG::B_CANCEL);
        $dialogObject->ShowDialog();
    }
}
// Get/Set sort mode
$postSortby = 'SORT_NOTHING';
if (DATA::__HasPostData('postSortby')) {
    $postSortby = DATA::__GetPOST('postSortby', true, true);
}


// Reports preparation follow here
$sql = new DB();
$sql->
        Select(array('course.id', 'teacher_id', 'username', 'course.is_active', 'code', 'name'))->
        From('course, user')->
        Where('course.teacher_id=user.id');
if ($postSortby != 'SORT_NOTHING') {
    $sql->OrderBy($postSortby, DB::ORDERBY_ASCENDING);
}
$result_Courses = $sql->Query();

$sql->
        Select(array('id', 'username'))->
        From('user')->
        Where('userpower_id=1');
$qresult = $sql->Query();
$result_Instructors = array();
foreach($qresult as $qresultrow) {
    array_push($result_Instructors, $qresultrow['username']);
}

$rptManageCourses = new MySQLReport();
$rptManageCourses->
        setReportProperties(array(
            'align' => 'left',
            'width' => '80%'))->
        setReportHeaders(array(
            [
                'CAPTION' => 'hiddenID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hiddenTEACHER_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hiddenUSERNAME',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hiddenIS_ACTIVE',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Course code',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Course name',
                'class' => 'rpt-header',
                'align' => 'center'
            ], [
                'CAPTION' => 'Instructor',
                'DEFAULT' => '{3}',
                'class' => 'rpt-header',
                'align' => 'center'
            ], [
                'CAPTION' => 'Actions', // Actions
                'DEFAULT' =>
                    UI::Button('Delete', 'button', 'btn btn-danger btn-xs btn-marginized', 
                                UI::GetPageUrl(Index::__GetPage(), array(
                                    'MODE' => 'DELETE',
                                    'TARGET_ID' => '{1}'
                                )), 
                            false),
                'class' => 'rpt-header',
                'align' => 'center'
            ], [
                'CAPTION' => 'Lock/Unlock', // Un/Lock
                'LISTOFVALUES' => array(
                    0 => UI::Button('Unlock', 'button', 'btn btn-warning btn-xs btn-marginized', 
                                UI::GetPageUrl(Index::__GetPage(), array(
                                    'MODE' => 'UNLOCK',
                                    'TARGET_ID' => '{1}'
                                )),
                            false),
                    1 => UI::Button('Lock', 'button', 'btn btn-primary btn-xs btn-marginized', 
                                UI::GetPageUrl(Index::__GetPage(), array(
                                    'MODE' => 'LOCK',
                                    'TARGET_ID' => '{1}'
                                )),
                            false),
                ),
                'DEFAULT' => '{4}',
                'class' => 'rpt-header',
                'align' => 'center'
            ]))->
        setReportCellstemplate(array(
            [ ], [ ], [ ], [ ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined',
                'align' => 'center'
            ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ]
        ));
$rptManageCourses->defineEmptyMessage('No existing course yet.');
$rptManageCourses->loadResultdata($result_Courses);


DATA::openPassage(Index::__GetPage());

?>