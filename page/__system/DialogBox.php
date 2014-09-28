<?php
if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET(); // check for GET data extraction
    # ---- Filterted
    #
    if ( !DATA::__HasIntentData('DIALOG_OBJECT')
            && (DATA::__HasIntentData('DIALOG_OBJECT') ? !is_object(DATA::__GetIntent('DIALOG_OBJECT')) : true) ) {
        FLASH::addFlash('You tried to enter an unauthorized page.', 
                [Index::$DEFAULT_PAGE],
            'ERROR', TRUE);
        UI::RedirectTo(Index::$DEFAULT_PAGE);
    }
    $dialogObject = DIALOG::ToDialog(DATA::__GetIntent('DIALOG_OBJECT'));
    if (isset($_SESSION["intent_LOCALRESULT"])) {
        // DATA::closePassage(Index::__GetPage());
        $dialogObject->SetResult(intval(DATA::__GetIntent('LOCALRESULT')), true);
    }
}

DATA::openPassage(Index::__GetPage(), true, false);
?>