<?php

// Initialize form[frmTasks] field values
$postTitle = '';
$postMessage = '';

if (DATA::__HasPostData(array(
    'postTitle',
    'postMessage')))
{
    $postTitle = DATA::__GetPOST('postTitle');
    $postMessage = DATA::__GetPOST('postMessage');
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
        'File extension you uploaded is not allowed. Please try another file!' => !$fileUpload->__IsAllowed(false)
    ), 'VALIDATION SUCCESS, Developers should add redirection here', 'instructor-courses-home-tasks', Index::__GetPage(), true);
    
    if (FLASH::__getType()=='PROMPT') {
        // Create task
        $sql = new DB();
        $sql->InsertInto('task', array('course_id', 'period_id', 'sy_id', 'title', 'message', 'postdate', 'deaddate'))
                ->Values(array()
                        , [0,1,2]);
        
        // save and get saved filenames
        $filenames = $fileUpload->Save(DIR::$UPLOAD, '');
    }
}

?>