<?php

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
        FLASH::addFlash('Supplied Course ID does not exist.', ['instructor-courses'], 'ERROR', true);
        UI::RedirectTo('instructor-courses');
    } else {
        $COURSE_INFOS = $result[0];
        DATA::CreateIntent('COURSE_INFOS', $COURSE_INFOS);
    }
} else {
    UI::RedirectTo(USER::Get(USER::TYPE).'-courses');
}

if (DATA::__HasPostData('postMessage')) {
    $COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
    $message = DATA::__GetPOST('postMessage', true, true);
    $sql = new DB();
    $sql->InsertInto('thread', array(
        'course_id', 'author_id', 'created', 'message'
    ))->
        Values(array(
            intval($COURSE_INFOS['id']),
            USER::Get(USER::ID),
            'localtime()',
            $message
        ), array(3));
    $is_success = $sql->Execute()->__IsSuccess();
    if (!$is_success)
    {
        FLASH::addFlash('Something went wrong. Please try again later.', Index::__GetPage(), 'ERROR', true);
    } else {
        FLASH::clearFlashes();
    }
    DATA::SetIntent('ALLOW_POST', false);
    UI::RefreshPage();
}

// [process] fetch all threads for this course
$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
$sql = new DB();
$sql->Select()
        ->From('thread')
        ->Where('course_id=' . $COURSE_INFOS['id'])
        ->OrderBy('created', DB::ORDERBY_DESCENDING);
$result_Threads = $sql->Query();



// [prepare] 
$a_threads = array();
// [process] rendering the threads
foreach ($result_Threads as $Thread) {
    // Fetch required values
    $sql = new DB();
    $sql->Select()
            ->From('user, profile')
            ->Where('user.id=' . USER::Get(USER::ID) . ' '
                    . 'AND user.id=profile.user_id');

    // Substitution values
    $author = $sql->Query()[0];
    $author_id = $sql->Select(['id'])->From('user')->Where('id=' . USER::Get(USER::ID))
            ->Query()[0]['id'];
    $thread_Datetime = $sql->Select(array(
        'DATE_FORMAT(created, "%l:%i%p &centerdot; %e %b %Y") AS datetime'))
            ->From('thread')
            ->Where('id=' . $Thread['id'])
            ->Query()[0]['datetime'];

    // Html elements
    $e_threadAuthor = '';
    $e_threadDatetime = '';
    $e_threadHeader = '';
    $e_threadControls = '';

    // render elements
    // [START] threadHeader
    $e_threadAuthor = 
            UI::Divbox(array(
                'class' => 'col-lg-7 col-md-7 col-sm-7 thread-author'
            ), 
                // Elements inside 
                '<a href="?page=user-profile&USER_ID=' . $author_id . '"><img src="web+/site/img/user.png">'
                    . '<b>' . $author['fname'] . ' ' . $author['lname'] . '</b></a>'
            , true);
    $e_threadDatetime =
            UI::Divbox(array(
                'class' => 'col-lg-5 col-md-5 col-sm-5 thread-datetime'
            ), 
                // Elements inside
                $thread_Datetime
            , true);
    $e_threadHeader =
            UI::Divbox(array(
                'class' => 'row'
            ),
                $e_threadAuthor . PHP_EOL . $e_threadDatetime
            , true);
    // [END] threadHeader
    
    // [START] threadControls
    if (intval($Thread['author_id'])===intval(USER::Get(USER::ID))) {
        $e_threadControls =
                UI::Divbox(array(
                    'class' => 'thread-controls row'
                ), UI::Button('Reply', 'button', 'btn btn-primary btn-xs thread-button', '', false) . PHP_EOL .
                   UI::Button('Edit', 'button', 'btn btn-warning btn-xs thread-button', '', false) . PHP_EOL .
                   UI::Button('Delete', 'button', 'btn btn-danger btn-xs thread-button', '', false) . PHP_EOL, true);
    }
    // [END] threadControls


    // Finalize and echo
    array_push($a_threads, 
        UI::Divbox(array(
            'class' => 'thread'
        ), 
            // Elements inside
            $e_threadHeader .
            UI::Divbox(array(
                'class' => 'thread-message container-fluid row'
            ), nl2br($Thread['message']), true)
        , true)
    );
    
}

?>