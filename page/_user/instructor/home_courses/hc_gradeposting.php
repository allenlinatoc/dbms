<?php

// Prepare form pre-values
$postGperiod = '';

// Get COURSE_INFOS
$COURSE_INFOS = DATA::__GetIntent('COURSE_INFOS');
$sy_id = ACADYEAR::__getDefaultID();

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