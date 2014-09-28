<?php
$targetpage404 = null;
$malicious404 = 'was not found on this server.';
if (array_key_exists('target', $_GET)) {
    $targetpage404 = DATA::__GetGET('target', FALSE, TRUE);
    // Recheck for target page existence
    if (Index::__HasPage($targetpage404) && Index::__HasScript($targetpage404)) {
        UI::RedirectTo($targetpage404);
    }
}
if (DATA::__HasGetData('malicious')) {
    if (DATA::__GetGET('malicious', FALSE, FALSE, TRUE) == 'yes') {
        $malicious404 = 'is an invalid page name.';
    }
}
$referrer = DATA::__HasGetData('ref') ? DATA::__GetGET('ref', TRUE, TRUE) : null;

# Parsing Admin data
$Adminconfig = new CONFIG(DIR::$CONFIG . 'admin.ini');
$Admindata = array();
if ($Adminconfig->Exists()) {
    $Admindata = $Adminconfig->Read();
}