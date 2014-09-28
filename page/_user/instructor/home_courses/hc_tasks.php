<?php

$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');

if (DATA::__IsPassageOpen())
{
    DATA::GenerateIntentsFromGET();
    #
    # ---- filtered
    if (DATA::__HasIntentData([ 'MODE', 'TARGET_ID' ])) {
        $MODE = DATA::__GetIntentSecurely('MODE');
        $TARGET_ID = DATA::__GetIntentSecurely('TARGET_ID');
        # ----
        if (DATA::__HasIntentData('DIALOG_RESULT')) {
            if (DATA::__GetIntent('DIALOG_RESULT')==DIALOG::R_AFFIRMATIVE)
            {
                // collect all filenames of attachments first
                $sql = new DB();
                $sql->Select(['tokenvalue'])
                        ->From('taskattachment')
                        ->Where('task_id='.$TARGET_ID);
                $filenames = $sql->Query();
                foreach($filenames as $filename) {
                    $io = new IOSys($filename);
                    $io->Delete();
                }
                // proceed in deleting the task
                $sql->DeleteFrom('task')
                        ->Where('id='.$TARGET_ID);
                $is_success = $sql->Execute()->__IsSuccess();
                if ($is_success) {
                    FLASH::addFlash('Task has been successfully deleted!', Index::__GetPage(), 'PROMPT', true);
                } else {
                    FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', Index::__GetPage(), 'ERROR', true);
                }
            }
            DATA::DeleteIntents(array('MODE', 'TARGET_ID', 'DIALOG_RESULT', 'DIALOG_OBJECT'), true, true);
        }
        
        if ( $MODE == 'REQ_DELETE' )
        {
            $sql = new DB();
            $TASK_INFOS = $sql->GetRow('task', 'id='.$TARGET_ID);
            
            $dialogObject = new DIALOG('Confirm deletion of task');
            $dialogObject->SetMessage('Are you sure you want to delete the task <i>'.$TASK_INFOS['title'].'</i>')
                    ->SetPageCallback(Index::__GetPage())
                    ->AddButton(DIALOG::B_YES)
                    ->AddButton(DIALOG::B_NO)
                    ->AddButton(DIALOG::B_CANCEL);
            $dialogObject->ShowDialog();
        }
    }
}


$sql = new DB();
$sql->Select([ 'id', 'title', 'deaddate' ])
        ->From('task')
        ->Where('sy_id='.ACADYEAR::__getDefaultID().' '
        . 'AND course_id='.$COURSE_INFOS['id']);
$result = $sql->Query();
$report_Tasks = new MySQLReport();
$report_Tasks
        ->setReportProperties(array(
            'align' => 'center',
            'width' => '100%'))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hidden_TITLE',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Title',
                'DEFAULT' => '<a href="?page=user-tasks&TASK_ID={1}">{2}</a>',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Deadline',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Action',
                'DEFAULT' =>
                            UI::Button('Delete', 'button', 'btn btn-danger btn-sm btn-marginized'
                                , UI::GetPageUrl(Index::__GetPage(), array(
                                    'MODE' => 'REQ_DELETE',
                                    'TARGET_ID' => '{1}'
                                )), false),
                'class' => 'rpt-header'
            ]))
        ->setReportCellstemplate(array(
            [], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ], [
                'class' => 'rpt-cell-lined rpt-cell-hpad'
            ]
        ));
$report_Tasks
        ->loadResultdata($result)
        ->defineEmptyMessage('No tasks<br><br><br><br>');

?>