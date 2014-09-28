<?php

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    
    if (DATA::__HasIntentData([ 'MODE', 'TARGET_ID' ])) {
        $MODE = DATA::__GetIntentSecurely('MODE');
        $TARGET_ID = DATA::__GetIntentSecurely('TARGET_ID');
        
        // Getting info about the student
        $sql = new DB();
        $sql->Select([ 'concat(profile.fname,\' \',profile.lname) AS fullname' ])
                ->From('user,profile')
                ->Where('user.id=profile.user_id '
                        . 'AND user.id='.$TARGET_ID);
        $fullname = $sql->Query()[0]['fullname'];
        
        if (DATA::__HasIntentData('DIALOG_RESULT')) {
            if (DATA::__GetIntent('DIALOG_RESULT') == DIALOG::R_AFFIRMATIVE)
            {
                if ($MODE == 'REQ_BAN') {
                    // ban user
                    $sql = new DB();
                    $sql->Update('user')
                            ->Set(array('status' => '1'))
                            ->Where('id='.$TARGET_ID);
                }
                else if ($MODE == 'REQ_DELETE') {
                    // delete user
                    $sql = new DB();
                    $sql->DeleteFrom('user')
                            ->Where('id='.$TARGET_ID);
                }
                $is_success = $sql->Execute()->__IsSuccess();
                if ($is_success) {
                    if ($MODE == 'REQ_BAN') {
                        FLASH::addFlash(ucwords($fullname).' has been successfully banned.', Index::__GetPage(), 'PROMPT', true);
                    }
                    else if ($MODE == 'REQ_DELETE') {
                        FLASH::addFlash(ucwords($fullname).'\'s account has been successfully purged and deleted!', Index::__GetPage(), 'PROMPT', true);
                    }
                }
                else {
                    FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', Index::__GetPage(), 'ERROR', true);
                }
            }
            DATA::DeleteIntents(array(
                'MODE', 'TARGET_ID', 'DIALOG_RESULT', 'DIALOG_OBJECT'
            ), true, true);
        }
        
        // Preparing dialog object
        $title = 'DIALOG_TITLE';
        $message = 'DIALOG_MESSAGE';
        if ( $MODE == 'REQ_BAN' ) {
            $title = 'Confirm user ban';
            $message = 'Are you sure you want to <i>ban</i> '.$fullname.'?' ;
        }
        else if ( $MODE == 'REQ_DELETE' ) {
            $title = 'Confirm user removal';
            $message = 'Are you sure you want to <i>delete</i> '.$fullname.'?<br><br>'.PHP_EOL.
                '<b>Warning: </b><i>This action is <b>permanent</b>. Doing this will completetly delete all user data of this account!</i>';
        }
        $dialogObject = new DIALOG($title);
        $dialogObject->SetPageCallback(Index::__GetPage())
                ->SetMessage($message);
        $dialogObject->AddButton(DIALOG::B_YES)
                ->AddButton(DIALOG::B_NO)
                ->AddButton(DIALOG::B_CANCEL);
        $dialogObject->ShowDialog();
    }
}

$sql = new DB();
$sql->Select(['user.id', 'concat(fname, \' \', lname)', 'user.status', 'username'])->
        From('user,profile')->
        Where('user.id=profile.user_id');
$sqlResult = $sql->Query();

$rptUsers = new MySQLReport(array(
    [
        'CAPTION' => 'hidden_ID',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'hidden_FULLNAME',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'hidden_STATUS',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'Username',
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Full name',
        'DEFAULT' => '<a href="'.UI::GetPageUrl('user-profile', ['USER_ID' => '{1}']).'">{2}</a>',
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Actions',
        'DEFAULT' => UI::Button('Delete', 'button', 'btn btn-danger btn-maginized btn-xs btn-block', 
                        UI::GetPageUrl(Index::__GetPage(), array(
                            'MODE' => 'REQ_DELETE',
                            'TARGET_ID' => '{1}'
                        )),
                    false),
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Ban/Unban',
        'LISTOFVALUES' => array(
            0 => UI::Button('Ban', 'button', 'btn btn-danger btn-marginized btn-xs btn-block',
                    UI::GetPageUrl(Index::__GetPage(), array(
                        'MODE' => 'REQ_BAN',
                        'TARGET_ID' => '{1}'
                    )), false),
            1 => UI::Button('Unban', 'button', 'btn btn-warning btn-marginized btn-xs btn-block',
                    UI::GetPageUrl(Index::__GetPage(), array(
                        'MODE' => 'REQ_UNBAN',
                        'TARGET_ID' => '{1}'
                    )), false),
            2 => '<div align="center"><i>Pending user</i></div>'
        ),
        'DEFAULT' => '{3}',
        'class' => 'rpt-header'
    ]
        ));
$rptUsers->setReportProperties(array(
    'width' => '100%',
    'align' => 'center'
))->setReportCellstemplate(array(
    [], [], [], [
        'class' => 'rpt-cell-lined'
    ], [
        'class' => 'rpt-cell-lined'
    ], [
        'class' => 'rpt-cell-lined'
    ], [
        'class' => 'rpt-cell-lined'
    ]
));
$rptUsers->loadResultdata($sqlResult);
$rptUsers->defineEmptyMessage('No existing users yet');

DATA::openPassages([Index::__GetPage(), 'user-profile'], true);
?>