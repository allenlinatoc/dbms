<?php

DATA::GenerateIntentsFromGET();
#
# ---- filtered
$TASK_ATTACHMENT_ID = DATA::__GetIntentSecurely('TA_ID');
$TA_INFOS = array();

$sql = new DB();
$TA_INFOS = $sql->GetRow('taskattachment', 'id='.$TASK_ATTACHMENT_ID);

if ($TA_INFOS !== NULL) {
    $downloads = intval($TA_INFOS['downcount']);
    $downloads += 1;
    $sql = new DB();
    $sql->Update('taskattachment')
            ->Set(array('downcount' => $downloads))
            ->Where('id='.$TASK_ATTACHMENT_ID);
    $sql->Execute();
    header('Content-type: application/download');
    header('Content-Disposition: attachment; filename="' . basename($TA_INFOS['tokenvalue']) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.filesize($TA_INFOS['tokenvalue']));
    readfile($TA_INFOS['tokenvalue']);
}
else {
    UI::RedirectTo('user-tasks');
}

?>