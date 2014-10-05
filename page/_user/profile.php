<?php
$USER_ID = null;
if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    if (DATA::__HasIntentData('USER_ID')) {
        $USER_ID = DATA::__GetIntentSecurely('USER_ID');
        $sql = new DB();
        $sql->Select()
                ->From('user,profile,userpower')
                ->Where('user.id=profile.user_id '
                        . 'AND user.userpower_id=userpower.id '
                        . 'AND user.id='.DATA::__GetIntentSecurely('USER_ID'));
        $USER_INFOS = $sql->Query()[0];
    }
}

if (!DATA::__HasIntentData('USER_ID')) {
    UI::RedirectTo('user-home');
}

?>