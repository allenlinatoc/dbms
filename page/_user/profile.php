<?php

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    if (DATA::__HasIntentData('USER_ID')) {
        $sql = new DB();
        $sql->Select()
                ->From('user,profile')
                ->Where('user.id=profile.user_id '
                        . 'AND user.id='.DATA::__GetIntentSecurely('USER_ID'));
        $USER_INFOS = $sql->Query()[0];
    }
}

if (!DATA::__HasIntentData('USER_ID')) {
    UI::RedirectTo('user-home');
}

?>