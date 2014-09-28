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
final class ACADYEAR {
    
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
    
    /**
     * Returns a boolean value if the system has existing school year or not
     * @return boolean
     */
    public static function __hasSchoolYear() {
        $sql = new DB();
        return count($sql->Select()->From('sy')->Query()) > 0;
    }
    
}
