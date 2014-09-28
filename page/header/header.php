<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

# Process title

$HEADER_appconfig = parse_ini_file(DIR::$ROOT . 'sys/app.ini');
$HEADER_title = $HEADER_appconfig['WEB_TITLE'];
$PAGE_TITLES = parse_ini_file(DIR::$CONFIG . 'page-titles.ini');

if (array_key_exists(Index::__GetPage(), $PAGE_TITLES))
{
    $HEADER_title = $PAGE_TITLES[Index::__GetPage()];
}