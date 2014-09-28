<?php

$FORM_MODE = 'ADD';

$postId = null;
$postName = '';
$postDescription = '';

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- FILTERED
    
    if (!is_null(DATA::__GetIntent('TARGET_ID'))) {
        $postId = DATA::__GetIntent('TARGET_ID');
        $sql = new DB();
        $sql->Select()
            ->From('gscheme')
            ->Where('id=' . $postId);
        $result = $sql->Query();
        if (count($result) > 0) {
            $result = $result[0];
            $postName = $result['name'];
            $postDescription = $result['description'];
        }
        $FORM_MODE = 'EDIT';
    }
}

if (DATA::__HasPostData()) {
    if ($FORM_MODE=='ADD') {
        $postName = $_POST['postName'];
        $postName = DATA::__GetPOST('postName', true, true);
        $postDescription = DATA::__GetPOST('postDescription', true, true);
        
        $sql = new DB();
        $sql->InsertInto('gscheme', [ 'name', 'description' ])
            ->Values([
                $postName, $postDescription
            ], [0, 1]);
        $success = $sql->Execute()->__GetAffectedRows() > 0;
        if ($success) {
            FLASH::addFlash('Grading scheme <i>' . $postName . '</i> has been successfully added.', [
                'home', 'user-home', 'instructor-gschemes'
            ], 'PROMPT', true);
            UI::RedirectTo('instructor-gschemes');
        } else {
            FLASH::addFlash('Failed to add the grading scheme <b>' . $postName . '</b>. If error persists, please contact the admin.', [
                'home', 'user-home', 'instructor-gschemes', 'instructor-gschemes-form'
            ], 'ERROR', true);
            UI::RedirectTo('instructor-gschemes-form');
        }
    }
    else if ($FORM_MODE=='EDIT') {
        $sql = new DB();
        $new_postName = DATA::__GetPOST('postName', true, true);
        $new_postDescription = DATA::__GetPOST('postDescription', true, true);
        $sql->Update('gscheme')
            ->Set(array(
                'name' => '"' . $new_postName . '"',
                'description' => '"' . $new_postDescription . '"'))
            ->Where('id=' . $postId);
        $rowsaffected = $sql->Execute()->rows_affected;
        FLASH::clearFlashes();
        if ($rowsaffected > 0) {
            FLASH::addFlash('Changes has been successfully saved.', 'instructor-gschemes', 'PROMPT');
            UI::RedirectTo('instructor-gschemes');
        } else {
            FLASH::addFlash('Unable to save changes, something went wrong and geeks are on it. Please try again later.',
                    Index::__GetPage(), 'ERROR');
            UI::RedirectTo(Index::__GetPage());
        }
    }
}


?>