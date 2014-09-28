<?php

$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');

// Initialize form[frmTasks] field values
$postTitle = '';
$postMessage = '';
$postDeaddate = '';


if (DATA::__HasPostData(array(
    'postTitle',
    'postMessage')))
{
    $postTitle = DATA::__GetPOST('postTitle');
    $postMessage = DATA::__GetPOST('postMessage');
    $postDeaddate = 'STR_TO_DATE("'.DATA::__GetPOST('postDeaddate').'", "%m/%d/%Y")';
    $fileUpload = new FileUpload('postFile');
    $fileUpload->SetBlockedExtensions(array(
        'exe',
        'bat',
        'dll',
        'cab',
        'vbs',
        'mp4',
        'php',
        'phtml',
        'htm',
        'html',
        'css',
        'msi'
    ));
    FLASH::checkAndAdd(array(
        'File extension you uploaded is not allowed. Please try another file!' => !$fileUpload->__IsAllowed(false),
        'No date of deadline specified!' => (is_null(DATA::__GetPOST('postDeaddate')) || strlen(DATA::__GetPOST('postDeaddate')<=0))
    ), 'VALIDATION SUCCESS, Developers should add redirection here', 'instructor-courses-home-tasks', Index::__GetPage(), true);
    
    if (FLASH::__getType()=='PROMPT') {
        // Create task
        $sql = new DB();
        $sql->Select(['id'])
                ->From('d_course_gperiod')
                ->Where('course_id='.$COURSE_INFOS['id'].' '
                        . 'AND sy_id='.ACADYEAR::__getDefaultID().' '
                        . 'AND is_current=1');
        $periodId = intval($sql->Query()[0]['id']);
        $sql = new DB();
        $sql->InsertInto('task', array('course_id', 'period_id', 'sy_id', 'title', 'message', 'postdate', 'deaddate'))
                ->Values(array(
                    $COURSE_INFOS['id'],
                    $periodId,
                    ACADYEAR::__getDefaultID(),
                    $postTitle,
                    $postMessage,
                    'localtime()',
                    $postDeaddate
                ), [3,4]);
        $is_success = $sql->Execute()->__IsSuccess();
        $sql = new DB();
        $sql->Select(['id'])->From('task')->OrderBy('id', DB::ORDERBY_DESCENDING)->Limit('1');
        $taskId = $sql->Query()[0]['id'];
        if ($is_success) {
            $filenames = $fileUpload->Save(DIR::$UPLOAD, (STR::RemoveSpaces(strtoupper($COURSE_INFOS['name'])).$COURSE_INFOS['id']));
            foreach($filenames as $filename) {
                $sql = new DB();
                $sql->InsertInto('taskattachment', array(
                    'task_id', 'user_id', 'tokenvalue', 'lastdown'))
                        ->Values(array(
                            $taskId, USER::Get(USER::ID), $filename, 'date(localtime())'
                        ), [2]);
                $is_success = $sql->Execute()->__IsSuccess();
                if (!$is_success) {
                    // Revert changes on failure
                    $sql = new DB();
                    $sql->DeleteFrom('taskattachment')
                            ->Where('task_id='.$taskId);
                    $sql->Execute();
                    foreach($filenames as $fname) {
                        $IOsys = new IOSys(DIR::$UPLOAD.'/'.$fname);
                        $IOsys->Delete();
                    }
                    FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', 'instructor-courses-home-tasks', 'ERROR', true);
                    break;
                }
            }
        }
        else {
            FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', 'instructor-courses-home-tasks', 'ERROR', true);
        }
        
        if ($is_success) {
            FLASH::addFlash('Task has been successfully added!', 'instructor-courses-home-tasks', 'PROMPT', true);
        }
        UI::RedirectTo('instructor-courses-home-tasks');
    }
}

?>