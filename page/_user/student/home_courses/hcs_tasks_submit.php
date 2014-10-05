<?php

if (DATA::__IsPassageOpen())
{
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    if (DATA::__HasIntentData('TASK_ID')) {
        $TASK_ID = DATA::__GetIntentSecurely('TASK_ID');
        $sql = new DB();
        $sql->Select()
                ->From('task')
                ->Where('id='.$TASK_ID);
        $TASK_INFOS = $sql->Query()[0];
        
        // get course infos
        $COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
    }
    else {
        UI::RedirectTo('student-mycourses-tasks');
    }
}

if (array_key_exists('postFile', $_FILES)) {
    $upload = new FileUpload('postFile');
    $upload->SetBlockedExtensions(array(
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
    if (!$upload->__IsAllowed(false)) {
        FLASH::addFlash('This type of file is not allowed!', Index::__GetPage(), 'ERROR', true);
    }
    else {
        $filename = $upload->Save(DIR::$UPLOAD, (STR::RemoveSpaces(strtoupper($COURSE_INFOS['name'])).$COURSE_INFOS['id']))[0];
        $sql->InsertInto('taskentry', array(
                'task_id', 'student_id', 'tokenvalue', 'datetime'
            ))->Values(array(
                $TASK_INFOS['id'], USER::Get(USER::ID), $filename, 'localtime()'
            ), [2]);
        $is_success = $sql->Execute()->__IsSuccess();
        if ($is_success) {
            FLASH::addFlash('Entry has been successfully submitted!', 'user-tasks', 'PROMPT', TRUE);
            UI::RedirectTo('user-tasks');
        }
        else {
            FLASH::addFlash('Something went wrong. Please try again.', Index::__GetPage(), 'ERROR', true);
        }
    }
}

?>