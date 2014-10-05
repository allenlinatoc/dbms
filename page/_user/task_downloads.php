<?php

DATA::GenerateIntentsFromGET();
#
# ---- filtered
$TASK_ATTACHMENT_ID = DATA::__GetIntentSecurely('TA_ID');
$TASK_ENTRY_ID = DATA::__GetIntentSecurely('TE_ID');
$A_INFOS = array();

if (DATA::__HasIntentData('TA_ID')) {
    $sql = new DB();
    $A_INFOS = $sql->GetRow('taskattachment', 'id='.$TASK_ATTACHMENT_ID);
}
else if (DATA::__HasIntentData('TE_ID')) {
    $sql = new DB();
    $A_INFOS = $sql->GetRow('taskentry', 'id='.$TASK_ENTRY_ID);
}
if ($A_INFOS !== NULL && DATA::__HasIntentData('TA_ID')) {
    $downloads = intval($A_INFOS['downcount']);
    $downloads += 1;
    $sql = new DB();
    $sql->Update('taskattachment')
            ->Set(array('downcount' => $downloads))
            ->Where('id='.$TASK_ATTACHMENT_ID);
    $sql->Execute();
    header('Content-type: application/download');
    header('Content-Disposition: attachment; filename="' . basename($A_INFOS['tokenvalue']) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.filesize($A_INFOS['tokenvalue']));
    readfile($A_INFOS['tokenvalue']);
}
else if ($A_INFOS !== NULL && DATA::__HasIntentData('TE_ID')) {
    header('Content-type: application/download');
    header('Content-Disposition: attachment; filename="' . basename($A_INFOS['tokenvalue']) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.filesize($A_INFOS['tokenvalue']));
    readfile($A_INFOS['tokenvalue']);
}
else {
    UI::RedirectTo('user-tasks');
}
DATA::DeleteIntents(['TA_ID','TE_ID'], false, true);

?>