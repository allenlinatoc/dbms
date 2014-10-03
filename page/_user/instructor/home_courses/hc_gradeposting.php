<?php

// Prepare form pre-values
$postGperiod = '';

// Get COURSE_INFOS
$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
$sy_id = ACADYEAR::__getDefaultID();

# -------------------------------------
DATA::GenerateIntentsFromGET();
#
# ---- requests filtered
if (DATA::__HasIntentData([ 'MODE', 'DCG_ID' ]))
{
    $MODE = DATA::__GetIntentSecurely('MODE');
    $DCG_ID = DATA::__GetIntentSecurely('DCG_ID');
    
    if (DATA::__HasIntentData('DIALOG_RESULT'))
    {
        if (DATA::__GetIntentSecurely('DIALOG_RESULT')==DIALOG::R_AFFIRMATIVE)
        {
            $is_success = false;
            if ( $MODE == 'REQ_DROPRECORD' )
            {
                $sql = new DB();
                $sql
                        ->DeleteFrom('d_student_grades')
                        ->Where('dcg_id='.$DCG_ID.' '
                                . 'AND course_id='.$COURSE_INFOS['id']);
                $sql
                        ->Execute();
                $is_success = $sql->__IsSuccess();
                $successmsg = 'Records has been successfully deleted';
            }
            if ( $is_success )
            {
                FLASH::addFlash($successmsg, Index::__GetPage(), 'PROMPT', TRUE);
            }
            else {
                FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', Index::__GetPage(), 'ERROR', TRUE);
            }
            DATA::DeleteIntents(array(
                'MODE', 'DCG_ID', 'DIALOG_RESULT', 'DIALOG_OBJECT'
            ), TRUE, TRUE);
        }
    }
    else
    {
        // Prepare dialog object
        $sql = new DB();
        $sql    
                ->Select([ 'gperiod.name' ])
                ->From('d_course_gperiod, gperiod')
                ->Where('d_course_gperiod.gperiod_id=gperiod.id '
                        . 'AND d_course_gperiod.id='.$DCG_ID)
                ->Limit(1);
        $periodName = $sql->Query()[0]['name'];
        if ( $MODE == 'REQ_DROPRECORD' )
        {
            $dialog = new DIALOG('Confirm deletion of grade records');
            $dialog
                    ->SetMessage('Are you sure you want to delete all grade records for <b>'.$periodName.'</b>?')
                    ->SetPageCallback(Index::__GetPage());
            $dialog
                    ->AddButton(DIALOG::B_YES)
                    ->AddButton(DIALOG::B_NO)
                    ->AddButton(DIALOG::B_CANCEL);
            $dialog
                    ->ShowDialog();
        }
    }
}


// Get all `gperiods`
$gperiods = array();
$sql = new DB();
$sql    ->Select([ 'gperiod.name', 'd_course_gperiod.id' ])
        ->From('d_course_gperiod, gperiod')
        ->Where('d_course_gperiod.gperiod_id=gperiod.id '
                . 'AND course_id='.$COURSE_INFOS['id'].' '
                . 'AND sy_id='.$sy_id);
$result = $sql->Query();
foreach($result as $row)
{
    $gperiods[$row['name']] = $row['id'];
}

// POST-data retrieval
if (DATA::__HasPostData('postGperiod'))
{
    $postGperiod = DATA::__GetPOST('postGperiod', true, true);
}
else
{
    // get the first grading period as its default
    $postGperiod = $result[0]['id'];
}

// Prepare grade table
$sql = new DB();
$sql    ->Select([ 'concat(profile.fname,\' \',profile.lname) AS fullname', 'd_student_grades.value' ])
        ->From('profile, d_student_grades')
        ->Where('profile.user_id = d_student_grades.student_id '
                . 'AND d_student_grades.course_id='.$COURSE_INFOS['id'].' '
                . 'AND d_student_grades.dcg_id='.$postGperiod);
$result = $sql->Query();

$report_Gradetable = new MySQLReport();
$report_Gradetable
        ->setReportProperties(array(
            'align' => 'center',
            'width' => '100%'
        ))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'Student',
                'class' => 'rpt-header'
            ], [
                'CAPTION' => 'Grade',
                'class' => 'rpt-header'
            ]
        ))
        ->setReportCellstemplate(array(
            [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-hpad rpt-cell-lined'
            ]
        ))
        ->loadResultdata($result)
        ->defineEmptyMessage(
                'Grades are not yet posted in this period <br><br>'
              . UI::Button('Post grades', 'button', 'btn btn-primary btn-small btn-marginized', 
                        UI::GetPageUrl(Index::__GetPage().'-form'
                                , array(
                                    'DCG_ID' => $postGperiod
                                ))
                        , false)
        );
?>