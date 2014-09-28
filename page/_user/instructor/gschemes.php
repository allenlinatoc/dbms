<?php

// Open a warp gate
DATA::openPassages([
    'home',
    'instructor-gschemes-form',
    'instructor-gschemes-home',
    'instructor-gschemes-home-form',
    'user-home'
]);

$sql = new DB();
$sql->Select(['id', 'name', 'description'])
    ->From('gscheme');
$sqlResult = $sql->Query();

$rptSchemes = new MySQLReport();
$rptSchemes->setReportProperties(array(
    'align' => 'center',
    'width' =>' 100%'
))->setReportHeaders(array(
    [
        'CAPTION' => 'hidden_ID',
        'HIDDEN' => true
    ], [
        'CAPTION' => 'hidden_NAME',
        'HIDDEN' => true
    ] ,[
        'CAPTION' => 'Name',
        'DEFAULT' => UI::Button('{2}', 'button', 'btn btn-warning btn-xs', 
                        UI::GetPageUrl('instructor-gschemes-home', [
                            'SCHEME_ID' => '{1}'
                        ]), 
                        false)
                   . UI::Button('Edit', 'button', 'btn btn-primary btn-xs',
                           UI::GetPageUrl('instructor-gschemes-form', array(
                               'TARGET_ID' => '{1}'
                           )), false),
        'class' => 'rpt-header'
    ], [
        'CAPTION' => 'Description',
        'LIMIT' => '60',
        'class' => 'rpt-header'
    ]
))->setReportCellstemplate(array(
    [], 
    [
        'class' => 'rpt-cell-lined rpt-cell-mpad'
    ],
    [
        'class' => 'rpt-cell-lined rpt-cell-mpad'
    ], []
));

$rptSchemes->loadResultdata($sqlResult);
$rptSchemes->defineEmptyMessage('No existing grading schemes. Start by creating one.');

?>