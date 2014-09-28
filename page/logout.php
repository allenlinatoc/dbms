<?php

USER::DestroySession();
DATA::DestroyIntents();
$redirectTo = DATA::__GetGET('landing', true, true, true);
if (is_null($redirectTo)) {
    $redirectTo = Index::$DEFAULT_PAGE;
}
UI::RedirectTo($redirectTo);

?>