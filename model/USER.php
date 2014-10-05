<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Static class to hold user session informations
 *
 * @author Allen
 */
class USER {
    
    static $ADMIN_LANDPAGE = 'admin-home';
    static $USER_LANDPAGE = 'instructor-courses';
    
    const _USER_CREDENTIALS_MAIN_KEY = 'ass_user_credentials';
    const ID = 'uc_id';
    const USERNAME = 'uc_username';
    const PASSWORD = 'uc_password';
    const TYPE = 'uc_usertype';
    
    const STUDENT_ACTIVE_COURSE_STATUS = 0;
    const STUDENT_INACTIVE_COURSE_STATUS = 1;
    const STUDENT_PENDING_COURSE_STATUS = 2;
    
    /**
     * Initializes the User credential session<br>
     * Does nothing if credential already exists
     */
    public static function InitSession($is_resetvalues=false) {
        if (!key_exists(self::_USER_CREDENTIALS_MAIN_KEY, $_SESSION) || $is_resetvalues) {
            $_SESSION[self::_USER_CREDENTIALS_MAIN_KEY] = array(
                self::ID => null,
                self::USERNAME => null,
                self::PASSWORD => null,
                self::TYPE => null
            );
        }
    }
    
    /**
     * Destroy all the user credential session data
     */
    public static function DestroySession() {
        if (key_exists(self::_USER_CREDENTIALS_MAIN_KEY, $_SESSION)) {
            unset($_SESSION[self::_USER_CREDENTIALS_MAIN_KEY]);
        }
    }
    
    /**
     * Return the value of a 'user credential key'
     * @param String $uc_key The 'user credential key', i.e: <i>USER::USERNAME</i>
     * @return mixed The corresponding value to the user credential key
     */
    public static function Get($uc_key) {
        self::InitSession();
        return key_exists($uc_key, $_SESSION[self::_USER_CREDENTIALS_MAIN_KEY]) ?
                $_SESSION[self::_USER_CREDENTIALS_MAIN_KEY][$uc_key]
              : null;
    }
    
    /**
     * Return the Landing homepage(pagename) of the current user
     * @return String
     */
    public static function GetLandpage() {
        self::InitSession();
        # If current session is null
        # --- make 'HOMEPAGE' the landpage
        if (is_null(self::Get(self::TYPE))) {
            return Index::$DEFAULT_PAGE;
        }
        
        $type = strtoupper(self::Get(self::TYPE));
        return (strstr($type, 'ADMIN')) ?
                self::$ADMIN_LANDPAGE
              : self::$USER_LANDPAGE;
    }
    
    /**
     * 
     * @return type
     */
    public static function IsAdmin() {
        self::InitSession();
        $type = strtoupper(self::Get(self::TYPE));
        return !(!strstr($type, 'ADMIN'));
    }
    
    public static function IsLoggedin() {
        return self::IsAdmin() || self::IsUser();
    }
    
    public static function IsUser() {
        return (!self::IsAdmin() && !is_null(self::Get(self::USERNAME)));
    }
    
    /**
     * Reset the user credential session data
     */
    public static function ResetSession() {
        self::InitSession(true);
    }
    
    /**
     * Set the value of a user credential key
     * @param String $uc_key The user credential key
     * @param String $value The value to be assigned to the 'user credential key'
     * @return Array(assoc) The new user credential session data
     */
    public static function Set($uc_key, $value) {
        self::InitSession();
        $_SESSION[self::_USER_CREDENTIALS_MAIN_KEY][$uc_key] = $value;
        return $_SESSION[self::_USER_CREDENTIALS_MAIN_KEY];
    }
    
    /**
     * Set values of multiple credential keys
     * @param Array(assoc) $a_uc_keys_values Assoc-array of KEYS and VALUES
     * @return Array(assoc) The new user credential session data
     */
    public static function Setmany($a_uc_keys_values, $is_resetvalues=false) {
        self::InitSession($is_resetvalues);
        $_SESSION[self::_USER_CREDENTIALS_MAIN_KEY] = $a_uc_keys_values;
        return $_SESSION[self::_USER_CREDENTIALS_MAIN_KEY];
    }
    
}
