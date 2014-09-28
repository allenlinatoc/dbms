<?php
$mode = '1_USERNAME';

$postUsername = '';
$postSecurityAnswer = '';

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- Filtered
    #
    if (DATA::__HasIntentData('REQUEST')) {
        if (DATA::__GetIntent('REQUEST')=='RESET') {
            DATA::DestroyIntents();
            UI::RefreshPage();
        }
    }
    if (DATA::__HasIntentData('FP_USERNAME')) {
        $mode = '2_SECURITY_CHECK';
    }
    if (DATA::__HasIntentData('FP_PASSWORD_CHANGE_MODE')) {
        $mode = '3_PASSWORD_CHANGE_MODE';
    }
}

if (DATA::__HasPostData()) {
    // [post-receive] Username confirmation
    if (DATA::__HasPostData('postUsername') && $mode=='1_USERNAME') {
        $postUsername = DATA::__GetPOST('postUsername', true, true, true);
        DATA::openPassage(Index::__GetPage());
        $sql = new DB();
        $sql    ->Select('1')
                ->From('user')
                ->Where('username LIKE \'%' . $postUsername . '%\'');
        $result_UsernameCheck = $sql->Query();
        FLASH::checkAndAdd(array(
            'Username should not contain spaces!' => STR::__HasSpaces($postUsername),
            'Username <b>' . $postUsername . '</b> is invalid' => count($result_UsernameCheck) <= 0,
            'Username should not be blank' => strlen($postUsername) <= 0
        ), 'Username validation success. Developer should add redirection here.', Index::__GetPage(), Index::__GetPage(), true);
        
        if (FLASH::__getType()=='PROMPT') {
            FLASH::clearFlashes();
            DATA::SetIntent('FP_USERNAME', $postUsername);
            UI::RefreshPage();
        }
    }
    // [post-receive] Security question authentication
    else if (DATA::__HasPostData('postSecurityAnswer') && $mode=='2_SECURITY_CHECK') {
        $postSecurityAnswer = DATA::__GetPOST('postSecurityAnswer', true, true, true);
        $sql = new DB();
        $sql    ->Select(array('secanswer'))
                ->From('user')
                ->Where('username LIKE \'%' . DATA::__GetIntent('FP_USERNAME') . '%\'');
        $secanswer = $sql->Query()[0]['secanswer'];
        $secanswer = ACCOUNTS::Encryptor($secanswer, 'DECRYPT');
        
        // flash checking
        FLASH::checkAndAdd(array(
            'Your answer is incorrect, try again' => strtolower($secanswer) != $postSecurityAnswer
        ), 'Yeah it\'s right!', Index::__GetPage(), Index::__GetPage(), true);
        
        if (FLASH::__getType()=='PROMPT') {
            FLASH::clearFlashes();
            DATA::openPassage(Index::__GetPage());
            DATA::SetIntent('FP_PASSWORD_CHANGE_MODE', true);
            UI::RefreshPage();
        }
    }
    // [post-receive] Password change/reset
    else if (DATA::__HasPostData(array( 'postNewpassword', 'postNewpassword2' )) && $mode=='3_PASSWORD_CHANGE_MODE') {
        $postNewpassword = DATA::__GetPOST('postNewpassword', false, true);
        $postNewpassword2 = DATA::__GetPOST('postNewpassword2', false, true);
        FLASH::checkAndAdd(array(
            'Passwords didn\'t match, please try again' => $postNewpassword!=$postNewpassword2,
            'Blank passwords are not allowed' => strlen($postNewpassword)<=0 || strlen($postNewpassword2) <=0
        ), 'Password validation success, do something developer!', Index::__GetPage(), Index::__GetPage(), true);
        
        if (FLASH::__getType()=='PROMPT') {
            FLASH::clearFlashes();
            // change now the password
            $sql = new DB();
            $sql    ->Update('user')
                    ->Set(array(
                        'password' => ACCOUNTS::Encryptor($postNewpassword, 'ENCRYPT')
                    ))
                    ->Where('username LIKE \'%' . DATA::__GetIntent('FP_USERNAME') . '%\'');
            FLASH::addFlash('Password has been successfully changed!', Index::$DEFAULT_PAGE, 'PROMPT', true);
            UI::RedirectTo(Index::$DEFAULT_PAGE);
        }
    }
}

?>