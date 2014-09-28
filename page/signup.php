<?php

# [FILTER] verify for anti-flood registration
if ( isset($_SESSION['ASS_LAST_SIGNUP']) ) {
    $config_regtimeout = parse_ini_file(DIR::$CONFIG . SYS::$CONFIG_SYSTEM_FILENAME)['registration_delay'];
    $last = DATEMAN::DateTime($_SESSION['ASS_LAST_SIGNUP']);
    $current = DATEMAN::DateTime();
    $diff_minutes = $current->diff($last)->format('%i');
    $diff_seconds = $current->diff($last)->format('%s');
    if ($diff_minutes > $config_regtimeout || ($diff_minutes==$config_regtimeout && $diff_seconds > 0) ) {
        unset($_SESSION['ASS_LAST_SIGNUP']);
        UI::RedirectTo(Index::__GetPage());
    } else {
        $waiting_time = intval($config_regtimeout) - $diff_minutes;
        FLASH::clearFlashes();
        FLASH::addFlash('Sorry but you have to wait for ' . ($waiting_time-1 > 0 ? ($waiting_time-1) . ' minute(s) and ' : '') . (60-$diff_seconds) . ' second(s) to request for another account. '
                . '<a href="' . UI::GetPageUrl('signup') . '">Click here to try again</a>',
                ['home'], 'ERROR', true);
        UI::RedirectTo('home');
    }
}

if (!FLASH::__IsDedicatedHere()) {
    FLASH::clearFlashes();
}

if (Index::__HasPostData()) {
    $postUsername = strtolower(DATA::__GetPOST('postUsername', true, true));
    $postEmail = strtolower(DATA::__GetPOST('postEmail', true, true));
    $postBirthday = DATA::__GetPOST('postBirthday', false, true);
    # Data validation
    FLASH::checkAndAdd(array(
        "Username already exists." => ACCOUNTS::Exists(array('username' => $postUsername)),
        "Passwords verification didn't match, please check again." => DATA::__GetPOST('postPass1') != DATA::__GetPOST('postPass2'),
        "Username should only contain letters and numbers" => ctype_punct($postUsername) || ctype_space($postUsername),
        "The email is already registered to an account." => ACCOUNTS::Exists(array('email' => $postEmail)),
        "The birthdate is invalid, the format should be mm/dd/yyyy." => !ACCOUNTS::ValidateDate($postBirthday, '%%/%%/%%%%')
            ), "You have successfully registered.", 'home', Index::__GetPage(), true);

    if (strtoupper(FLASH::__getType()) == 'PROMPT') {
        if (ACCOUNTS::Create($_POST)) {
            if (!ACCOUNTS::CreateProfile($_POST)) {
                $mysql = new DB();
                $userid = $mysql->Select(['id'])
                        ->From('user')
                        ->Where('`username`="' . DATA::__GetPOST($postUsername) . '"')
                        ->Query();
                if (count($userid) > 0) {
                    ACCOUNTS::Delete($userid[0]['id']);
                    FLASH::addFlash("Database error. Failed to create a profile for your account."
                            , "ERROR", true);
                }
            } else {
                // Secure a session for most recent signup time
                $_SESSION['ASS_LAST_SIGNUP'] = DATEMAN::getStdDatetime();
                UI::RedirectTo('home');
            }
        } else {
            FLASH::addFlash("Registration failed due to a system error. Please contact the admin", Index::__GetPage(), "ERROR", true);
        }
    }
}
?>