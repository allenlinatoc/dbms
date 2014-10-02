<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Utility / model for AcademicYear DB table
 *
 * @author Allen
 */
class ACADYEAR {
    
    /**
     * Gets the ID of the current default schoolyear
     * @return int
     */
    public static function __getDefaultID() 
    {
        $sql = new DB();
        $result = $sql->Query('SELECT id FROM sy WHERE `is_default`=1 LIMIT 1');
        if (count($result) > 0) {
            return intval($result[0]['id']);
        }
        return -1;
    }
    
    public static function __getDefaultGradingPeriod($courseId) {
        $PERIOD_INFOS = array();
        // validate if this course has default grading period
        $sql = new DB();
        $sql->Select()
                ->From('d_course_gperiod')
                ->Where('course_id='.$courseId.' '
                        . 'AND sy_id='.ACADYEAR::__getDefaultID().' '
                        . 'AND is_current=1');
        $result_Gperiod = $sql->Query();
        if (count($result_Gperiod) <= 0) {
            FLASH::addFlash('No default grading period is defined for course '.$SB_COURSE_NAME.'. Please try again later.', 'user-home', 'ERROR', true);
            UI::RedirectTo('user-home');
        }
        else {
            $PERIOD_INFOS = $result_Gperiod[0];
        }
        return $PERIOD_INFOS;
    }
    
    /**
     * Returns a boolean value if the system has existing school year or not
     * @return boolean
     */
    public static function __hasSchoolYear() {
        $sql = new DB();
        return count($sql->Select()->From('sy')->Query()) > 0;
    }
    
}
