<?php

$TASK_INFOS = array();
$TASK_ATTACHMENTS = array();

DATA::openPassage('user-taskattachment', true, false);

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    if (DATA::__HasIntentData('TASK_ID')) 
    {
        // get information about this TASK
        $sql = new DB();
        $sql->Select()
                ->From('task')
                ->Where('id='.DATA::__GetIntent('TASK_ID'))
                ->Limit(1);
        $TASK_INFOS = $sql->Query()[0];
        
        // get attachments
        $sql = new DB();
        $sql->Select()
                ->From('taskattachment')
                ->Where('task_id='.$TASK_INFOS['id']);
        $TASK_ATTACHMENTS = $sql->Query();
    }
    
    // Check for Dialog_Result object, then settle it
    if (DATA::__HasIntentData('DIALOG_RESULT'))
    {
        if (DATA::__GetIntentSecurely('DIALOG_RESULT') == DIALOG::R_AFFIRMATIVE)
        {
            // proceed with deletion of entry
            $TE_ID = DATA::__GetIntentSecurely('TE_ID');
            //die(print_r($_SESSION, true));
            $sql = new DB();
            $sql
                    ->Select(['tokenvalue'])
                    ->From('taskentry')
                    ->Where('id='.$TE_ID)
                    ->Limit(1);
            $tokenvalue = $sql->Query()[0]['tokenvalue'];
            $IOsys = new IOSys($tokenvalue);
            
            $sql = new DB();
            $sql
                    ->DeleteFrom('taskentry')
                    ->Where('id='.$TE_ID);
            $is_success = $sql->Execute()->__IsSuccess();
            if ($is_success) 
            {
                $IOsys->Delete();
                FLASH::addFlash('Entry submission has been successfully deleted!', Index::__GetPage(), 'PROMPT', TRUE);
            }
            else {
                FLASH::addFlash('Something went wrong during deletion of your entry.', Index::__GetPage(), 'ERROR', TRUE);
            }
        }
        DATA::DeleteIntents([ 'MODE', 'TE_ID', 'DIALOG_RESULT' ], TRUE, TRUE);
    }
    
    // Proceed with INTENT data checking
    
    $MODE = DATA::__GetIntentSecurely('MODE');
    if (DATA::__HasIntentData([ 'MODE', 'TARGET_ID' ]))
    {
        $TARGET_ID = DATA::__GetIntentSecurely('TARGET_ID');
        if ($MODE == 'REQ_CANCEL_TE') {
            // get path to file attachment
            $sql = new DB();
            $path = $sql->GetRow('taskentry', 'id='.$TARGET_ID)['tokenvalue'];
            $sql = new DB();
            $sql->DeleteFrom('taskentry')
                    ->Where('id='.$TARGET_ID);
            $is_success = $sql->Execute()->__IsSuccess();

            if ($is_success) {
                $IO = new IOSys($path);
                $IO->Delete();
                FLASH::addFlash('Your submission has been successfully cancelled.', Index::__GetPage(), 'PROMPT', true);
            }
            else {
                FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', Index::__GetPage(), 'ERROR', true);
            }
        } 
        else if ($MODE == 'REQ_APPROVE_SUBMISSION')
        {
            $sql = new DB();
            $sql->Update('taskentry')
                    ->Set(['is_accepted'=>1])
                    ->Where('id='.$TARGET_ID);
            $is_success = $sql->Execute()->__IsSuccess();
            if ($is_success) {
                FLASH::addFlash('Submission has been approved!.', Index::__GetPage(), 'PROMPT', true);
            }
            else {
                FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', Index::__GetPage(), 'ERROR', true);
            }
        }
        DATA::DeleteIntents(array('MODE', 'TARGET_ID'), true, true);
    }
    else if ($MODE == 'REQ_DELETE_SUBMISSION' && DATA::__HasIntentData('TE_ID'))
    {
        // Create DIALOG object
        $dialog = new DIALOG('Confirm entry deletion');
        $dialog
                ->SetMessage('Are you sure you want to delete your submission entry?')
                ->SetPageCallback(Index::__GetPage())
                ->AddButton(DIALOG::B_YES)
                ->AddButton(DIALOG::B_NO)
                ->AddButton(DIALOG::B_CANCEL);
        $dialog
                ->ShowDialog();
    }
}



// [PROCESS] Generating reports

// unchecked submissions
$sql = new DB();
$sql->Select([ 'taskentry.id', 'concat(profile.fname,\' \',profile.lname) AS fullname', 'datetime' ])
        ->From('taskentry, profile')
        ->Where('task_id='.$TASK_INFOS['id'].' '
                . 'AND is_accepted=0 '
                . 'AND profile.user_id=taskentry.student_id');

$unchecked = $sql->Query();

$report_Unchecked = new MySQLReport();
$report_Unchecked
        ->setReportProperties(array(
            'align' => 'center',
            'width' => '100%'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hidden_STUDENT_FULLNAME',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Student\'s name',
                'DEFAULT' => UI::makeLink(UI::GetPageUrl('user-profile',array('USER_ID'=>'{1}')), '{2}', true),
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Date of submission',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Action',
                'DEFAULT' =>
                        UI::Button('Approve', 'button', 'btn btn-primary btn-sm btn-marginized'
                                , UI::GetPageUrl(Index::__GetPage(), array(
                                    'MODE' => 'REQ_APPROVE_SUBMISSION',
                                    'TARGET_ID' => '{1}'
                                )), false),
                'class' => 'rpt-header'
            ]
        ))
        ->setReportCellstemplate(array(
            [], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ]
        ))
        ->loadResultdata($unchecked)
        ->defineEmptyMessage('No pending submission<br><br>');


// checked submissions
$sql = new DB();
$sql->Select([ 'taskentry.id', 'concat(profile.fname,\' \',profile.lname) AS fullname', 'datetime' ])
        ->From('taskentry,profile')
        ->Where('task_id='.$TASK_INFOS['id'].' '
                . 'AND is_accepted=1 '
                . 'AND profile.user_id=taskentry.student_id');
$checked = $sql->Query();

$report_Checked = new MySQLReport();
$report_Checked
        ->setReportProperties(array(
            'align' => 'center',
            'width' => '100%'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_TASK_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hidden_STUDENT_FULLNAME',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Student\'s name',
                'DEFAULT' => UI::makeLink(UI::GetPageUrl('user-profile',array('USER_ID'=>'{1}')), '{2}', true),
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Date of submission',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Action',
                'DEFAULT' =>
                        UI::Button('Download entry', 'button', 'btn btn-primary btn-sm btn-marginized'
                                , UI::GetPageUrl('user-taskattachment', array(
                                    'TE_ID' => '{1}'
                                )), false),
                'class' => 'rpt-header'
            ]
        ))
        ->setReportCellstemplate(array(
            [], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ]
        ))
        ->loadResultdata($checked)
        ->defineEmptyMessage('No checked submission<br><br>');

?>