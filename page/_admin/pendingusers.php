<?php

# Prepare a passage
DATA::openPassage('admin-action-pendings');

$a_pendinglist = ACCOUNTS::getPendingUsers(array(
            'user.id', 'userpower_id', 'concat(fname, \' \', lname)', 'username'
                ), true);
$rptPendingusers = new MySQLReport();
$rptPendingusers->setReportProperties(array(
            'width' => '100%',
            'align' => 'left'))
        ->setReportHeaders(array(
            [
                'CAPTION' => 'hidden_ID',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hidden_TYPE',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'hidden_FULLNAME',
                'HIDDEN' => true
            ], [
                'CAPTION' => 'Username',
                'class' => 'rpt-header-noborder',
                'width' => '20%'
            ], [
                'CAPTION' => 'Full name',
                'DEFAULT' => '<a href="?page=user-profile&USER_ID={1}">{3}</a>',
                'class' => 'rpt-header-noborder',
                'width' => '30%'
            ], [
                'CAPTION' => 'Action',
                'DEFAULT' => 
                    UI::Button('Approve', 'button', 'btn btn-warning btn-xs', 
                            UI::GetPageUrl('admin-action-pendings', array(
                                'who' => '{1}',
                                'is_approve' => 'true'
                            )), false) . '&nbsp;' .
                    UI::Button('Reject', 'button', 'btn btn-danger btn-xs',
                            UI::GetPageUrl('admin-action-pendings', array(
                                'who' => '{1}',
                                'is_approve' => 'false'
                            )), false),
                'class' => 'rpt-header-noborder',
                'width' => '50%'
            ]
        ))
        ->setReportCellstemplate(array(
            [], [], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ], [
                'class' => 'rpt-cell-lined'
            ]
        ));
$rptPendingusers->loadResultdata($a_pendinglist);

DATA::openPassage('user-profile', true);
?>