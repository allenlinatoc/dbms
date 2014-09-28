<?php

$count_pending = 0;
$count_users = 0;
$count_sy = 0;

// Prepare to count all Pending users
$mysql = new DB();
$result_EnrolledCourses = $mysql->Select(['count(*) as "PENDING_COUNT"'])
        ->From('user')
        ->Where('user.status=2')
        ->Query();
$count_pending = intval($result_EnrolledCourses[0]['PENDING_COUNT']);

// Count users
$sql = new DB();
$sql->Select(['1'])->From('user');
$count_users = count($sql->Query());

// Count SY
$sql = new DB();
$sql->Select(['1'])->From('sy');
$count_sy = count($sql->Query());
?>