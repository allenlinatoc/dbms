<?php

// [Declarations]
$MODE = null;
    # [ possible modes ]
    # + EDIT
    # +-- CANCEL_EDIT
    # + DELETE
    # +-- CANCEL_DELETE
    #

$TARGET_ID = null;
$postName = null;
$postDescription = null;
//print_r($_SESSION);
if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- FILTERED
    #
    if (DATA::__HasIntentData('MODE') && DATA::__HasIntentData('TARGET_ID')) {
        $MODE = strtoupper(DATA::__GetIntent('MODE'));
        $TARGET_ID = intval(DATA::__GetIntent('TARGET_ID'));
        if ($MODE == 'EDIT') {
            $postName = DATA::__GetIntent('COURSE_INFOS')['name'];
            $postDescription = DATA::__GetIntent('COURSE_INFOS')['description'];
        } else if ($MODE == 'DELETE') {
            $allow_delete = false;
            $creatable = true;
            if (DATA::__HasIntentData('DIALOG_RESULT')) {
                $allow_delete = intval(DATA::__GetIntent('DIALOG_RESULT'))===DIALOG::R_AFFIRMATIVE;
                DATA::DeleteIntents(array(
                    'DIALOG_RESULT', 'DIALOG_OBJECT', 'MODE'
                ));
                $creatable = false;
            }
            if (!DATA::__HasIntentData('DIALOG_OBJECT') && $creatable) {
                $dialogDelete = new DIALOG('Confirm delete course');
                $dialogDelete->AddButton(DIALOG::B_YES)
                             ->AddButton(DIALOG::B_NO)
                             ->AddButton(DIALOG::B_CANCEL)
                             ->SetMessage('Are you sure you want to delete <b>' . DATA::__GetIntent('COURSE_INFOS')['name'] . '</b>?')
                             ->SetPageCallback(Index::__GetPage());
                $dialogDelete->ShowDialog();
            } else if ($allow_delete) {
                $dialogresult = DATA::__HasIntentData('DIALOG_RESULT');
                if ($dialogresult == DIALOG::R_AFFIRMATIVE) {
                    $sql = new DB();
                    $sql->DeleteFrom('course')
                        ->Where('id=' . $TARGET_ID);
                    $sql->Execute();
                    if ($sql->__IsSuccess()) {
                        $courseName = DATA::__GetIntent('COURSE_INFOS')['name'];
                        DATA::DestroyIntents();
                        FLASH::addFlash('Course "' . $courseName . '" has been deleted', 'instructor-courses', 'PROMPT', true);
                        UI::RedirectTo('instructor-courses');
                    } else {
                        FLASH::addFlash('A database error occured.', Index::__GetPage(), 'ERROR', true);
                        UI::RedirectTo(Index::__GetPage());
                    }
                } else {
                    DATA::DeleteIntents(array(
                        'DIALOG_RESULT', 'DIALOG_OBJECT', 'MODE', 'LOCALRESULT'
                    ), TRUE);
                }
            } else {
                DATA::DeleteIntents(array(
                    'DIALOG_RESULT', 'DIALOG_OBJECT', 'MODE', 'LOCALRESULT'
                ), TRUE);
            }
        } else if ($MODE=='CANCEL_EDIT' || $MODE=='CANCEL_DELETE') {
            DATA::DeleteIntents(array(
                'MODE', 'TARGET_ID'
            ), TRUE);
        } else {
            DATA::DeleteIntents(array(
                'DIALOG_RESULT', 'DIALOG_OBJECT', 'MODE', 'LOCALRESULT'
            ));
        }
    }
}

if (DATA::__HasPostData()) {
    $postName = DATA::__GetPOST('postName', TRUE, TRUE, FALSE);
    $postDescription = DATA::__GetPOST('postDescription', TRUE, TRUE);
    $TARGET_ID = DATA::__GetIntent('TARGET_ID');
    FLASH::checkAndAdd(array(
        'Course name should only contain letters and numbers' => STR::__HasPunct($postName)
    ), 'Verification success, developer should add redirection/refresh here.', Index::__GetPage(), Index::__GetPage(), true);
    if (FLASH::__getType()=='PROMPT') {
        $sql = new DB();
        $sql->Update('course')
            ->Set(array(
                'name' => '"' . $postName .'"',
                'description' => '"'. $postDescription . '"'
            ))
            ->Where('id=' . $TARGET_ID);
        $sql->Execute();
        if ($sql->__IsSuccess()) {
            FLASH::addFlash('Changes have been successfully saved!', Index::__GetPage(), 'PROMPT', true);
            DATA::DeleteIntents(array(
                'MODE',
                'TARGET_ID'
            ));
            $sql = new DB();
            $sql->Select()->From('course')->Where('id=' . $TARGET_ID);
            $result = $sql->Query();
            if (count($result) > 0) {
                DATA::SetIntent('COURSE_INFOS', $result[0]);
            }
        } else {
            FLASH::addFlash('A database error occured, please try again', Index::__GetPage(), 'ERROR', true);
        }
        UI::RefreshPage();
    }
}

?>