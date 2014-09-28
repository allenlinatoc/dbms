<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!USER::IsAdmin()) {
    UI::RedirectTo('home');
    die('Redirected!');
}

# Data processing for pending users --------------------------------------------
$a_pendingusers = ACCOUNTS::getPendingUsers([
    'user.id', 'username', 'fname', 'lname' ], true);
$rptPendingusers = new MYSQLREPORT(array(
    [
        'CAPTION' => 'ID',
        'width' => '10%',
        'align' => 'center'
    ],
    [
        'CAPTION' => 'Username',
        'width' => '20%'
    ],
    [
        'CAPTION' => 'First name',
        'width' => '22%'
    ],
    [
        'CAPTION' => 'Lastname',
        'width' => '23%'
    ]
));

?>