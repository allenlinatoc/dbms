<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class for all data abstraction and manipulation layer of the application
 *
 * @author Allen
 */
final class DATA {
    private static $SESS_DATALOCK_TIME = 'emit_kcolatad';
    private static $SESS_DATALOCK_KEY = '_yek_kcolatad';
    private static $SESS_DATALOCK_TARGETPAGES = 'segaptegrat_kcolatad';

    
    
    /**
     * Bulk check (AND) a set of boolean specifications
     * @param array $a_specs The array of boolean specifications
     * @return int Returns -1 if all specs were met, otherwise, returns the index of
     *      first false specification
     */
    public static function __BulkCheck(array $a_specs) {
        for ($x=0; $x < count($a_specs); $x++) {
            if (!$a_specs[$x]) {
                return $x;
            }
        } return -1;
    }
    
    /**
     * Extracts certain value from a specified $_POST data, otherwise, returns null
     * @param Array(Assoc) $POST_DATA Source $_POST data
     * @param String $datakey The data key of the value
     * @param Boolean $is_trimspaces Boolean value whether left-right trailing spaces should be trimmed out
     * @param Boolean $is_striphtml Boolean value whether HTML tags should be stripped out
     * @param Boolean|null $is_tolower True will make LowerCase, otherwise, UpperCase, NULL makes it do nothing
     * @return Mixed|null
     */
    public static function __ExtractPost($POST_DATA, $datakey, $is_trimspaces = false, $is_striphtml = false, $is_tolower = null) {
        if (!array_key_exists($datakey, $POST_DATA)) {
            return null;
        }
        $data = $POST_DATA[$datakey];
        if ($is_striphtml) {
            $data = strip_tags($data);
        }
        if ($is_trimspaces) {
            $data = trim($data);
        }
        if (!is_null($is_tolower)) {
            $data = $is_tolower ? strtolower($data) : strtoupper($data);
        }
        return $data;
    }

    /**
     * Generate a random hash string
     * @param int $length (Optional) The length of the hash, max of 32
     * @param int $timestamp (Optional) If SUPPLIED, random hash will be generated
     *      based on this value
     * @return String
     */
    public static function __GenerateRandomhash($length = 32, $timestamp = null) {
        $timehash = (is_null($timestamp) ? time() : $timestamp);
        return substr(md5($timehash), 0, $length);
    }

    /**
     * Gets data from specified GET data key, otherwise, returns null
     * @param String $datakey The key of data to be fetched from $_GET
     * @param Boolean $is_trimspaces Boolean value whether left-right trailing spaces should be trimmed out
     * @param Boolean $is_striphtml Boolean value whether HTML tags should be stripped out
     * @param Boolean|null $is_tolower True will make LowerCase, otherwise, UpperCase, NULL makes it do nothing
     * @return Mixed|null
     */
    public static function __GetGET($datakey, $is_trimspaces = false, $is_striphtml = false, $is_tolower = null) {
        if (!array_key_exists($datakey, $_GET)) {
            return null;
        }
        $data = $_GET[$datakey];
        if ($is_striphtml) {
            $data = strip_tags($data);
        }
        if ($is_trimspaces) {
            $data = trim($data);
        }
        if (!is_null($is_tolower)) {
            $data = $is_tolower ? strtolower($data) : strtoupper($data);
        }
        return $data;
    }

    /**
     * Returns certain value from existing intent, otherwise, returns NULL
     * @param String $intentname The intent name
     * @param boolean $return_booleanIfNotExist (Optional) Boolean value if
     *      FALSE should be returned if Intent does not exist, otherwise,
     *      NULL.
     * @return Mixed|null
     */
    public static function __GetIntent($intentname, $return_booleanIfNotExist=false) {
        if (!array_key_exists("intent_" . $intentname, $_SESSION)) {
            return $return_booleanIfNotExist ? false : null;
        }
        return $_SESSION["intent_" . $intentname];
    }
    
    /**
     * Get the value of an intent with filter
     * @param String $intentname The target intent's name
     * @param Boolean $is_trimspace [optional=true] If spaces should be trimmed or not
     * @param Boolean $is_striphtml [optional=true] If HTML tags should be trimmed or not
     * @return mixed The value of the intent, otherwise, returns NULL
     */
    public static function __GetIntentSecurely($intentname, $is_trimspace=true, $is_striphtml=true) {
        if (!array_key_exists("intent_" . $intentname, $_SESSION)) {
            return null;
        }
        $value = $is_trimspace ? trim($_SESSION["intent_" . $intentname]) : $_SESSION["intent_" . $intentname];
        $value = $is_striphtml ? strip_tags($value) : $value;
        return $value;
    }

    /**
     * Gets data from specified POST data key, otherwise, returns null
     * @param String $datakey The key of data to be fetched from $_POST
     * @param Boolean $is_trimspaces Boolean value whether left-right trailing spaces should be trimmed out
     * @param Boolean $is_striphtml Boolean value whether HTML tags should be stripped out
     * @param Boolean|null $is_tolower True will make LowerCase, otherwise, UpperCase, NULL makes it do nothing
     * @return Mixed|null
     */
    public static function __GetPOST($datakey, $is_trimspaces = false, $is_striphtml = false, $is_tolower = null) {
        if (!array_key_exists($datakey, $_POST)) {
            return null;
        }
        $data = $_POST[$datakey];
        if ($is_striphtml) {
            $data = strip_tags($data);
        }
        if ($is_trimspaces) {
            $data = trim($data);
        }
        if (!is_null($is_tolower)) {
            $data = $is_tolower ? strtolower($data) : strtoupper($data);
        }
        return $data;
    }

    /**
     * Checks if $_POST has content during the load of this page
     * @param string|Array $datakey (Optional) The key of the post data to be extracted
     * @return boolean
     */
    public static function __HasPostData($datakey = null) {
        if (is_null($datakey)) {
            return count($_POST) > 0;
        } else if (is_array($datakey)) {
            // If supplied `datakey` is an array of 'post keys'
            foreach($datakey as $postkey) {
                if (!self::__HasPostData($postkey)) {
                    return false;
                }
            }
            return true;
        } else {
            // Otherwise, if string
            return array_key_exists($datakey, $_POST);
        }
    }
    
    public static function __HasGetData($datakey = null) {
        if (is_null($datakey)) {
            return count($_GET) > 0;
        } else if (is_array($datakey)) {
            foreach($datakey as $postkey) {
                if (!self::__HasGetData($postkey)) {
                    return false;
                }
            }
            return true;
        } else {
            return array_key_exists($datakey, $_GET);
        }
    }
    
    /**
     * Returns a boolean value if a specific intent data exists
     * @param String|Array $datakey (Optional=null) The data key/keys of an intent to be checked
     * @return boolean
     */
    public static function __HasIntentData($datakey = null) {
        if (is_null($datakey)) {
            // checking for existing intent data
            $x = 0;
            while($x < count($_SESSION)) {
                if (stripos(key($_SESSION), "intent_")!==FALSE) {
                    return true;
                }
                next($_SESSION);
                $x++;
            }
            return false;
        } else if (is_array($datakey)) {
            // checking for multiple intent data
            foreach($datakey as $key) {
                if (!self::__HasIntentData($key)) {
                    return false;
                }
            }
            return true;
        } else {
            return array_key_exists("intent_" . $datakey, $_SESSION);
        }
    }

    /**
     * Reformats a date %m/%d/%Y into another format
     * @param type $str_date The date string in format %m/%d/%Y
     * @param type $formatmask New date format: e.g. %d-%m-%Y
     * @return The new date with respect to '$formatmask'
     */
    public static function __ReformatDate($str_date, $formatmask) {
        $month = substr($str_date, 0, 2);
        $day = substr($str_date, 3, 2);
        $year = substr($str_date, 5, 2);
        $date = $formatmask;

        $date = str_replace('%m', $month, $date);
        $date = str_replace('%d', $day, $date);
        $date = str_replace('%Y', $year, $date);
        return $date;
    }
    
    /**
     * Returns a boolean value if Passage Gate is not dedicated in current page
     * @return Boolean Boolean value as test result
     */
    public static function __IsPassageDedicatedHere() {
        if (array_key_exists(self::$SESS_DATALOCK_TARGETPAGES, $_SESSION)) {
            foreach($_SESSION[self::$SESS_DATALOCK_TARGETPAGES] as $page) {
                if (trim(strtolower($page))==Index::__GetPage()) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * Returns a boolean value whether the GET passage gate is open or not <b>and</b>
     *      IF passage is dedicated for the current page
     * @param Boolean $is_clear_ifnotdedicated (Optional) Boolean value if intents
     *      should be cleared if Passage Gate is not dedicated for current page.
     *      Default is <b>false</b>
     * @return boolean Boolean value if the GET passage gate is open or not
     */
    public static function __IsPassageOpen($is_clear_ifnotdedicated=false) {
        if (!self::__IsPassageDedicatedHere() && $is_clear_ifnotdedicated) {
            self::DestroyIntents();
        }
        if (!isset($_SESSION[self::$SESS_DATALOCK_KEY]) || !isset($_SESSION[self::$SESS_DATALOCK_TIME])) {
            return false;
        }
        $sessTime = $_SESSION[self::$SESS_DATALOCK_TIME];
        $sessHash = $_SESSION[self::$SESS_DATALOCK_KEY];
        return self::__GenerateRandomhash(32, $sessTime) == $sessHash;
    }

    /**
     * Close all intent passages<br>
     * This is useful when you want to invalidate all programmer-defined GET values
     */
    public static function closePassage($pagename=null, $is_clearintents = true) {
        if (is_null($pagename)) {
            unset($_SESSION[self::$SESS_DATALOCK_TARGETPAGES]);
            if ($is_clearintents) {
                unset($_SESSION[self::$SESS_DATALOCK_TIME]);
                unset($_SESSION[self::$SESS_DATALOCK_KEY]);
                self::DestroyIntents();
            }
        } else {
            if (array_key_exists($_SESSION[self::$SESS_DATALOCK_TARGETPAGES], $pagename)) {
                unset($_SESSION[self::$SESS_DATALOCK_TARGETPAGES][$pagename]);
            }
        }
    }

    /**
     * Create a value for certain intent
     * @param String $intentname The intent name
     * @param Mixed $value The value of the intent
     */
    public static function CreateIntent($intentname, $value) {
        $_SESSION["intent_" . $intentname] = $value;
    }

    /**
     * Delete an entire intent
     * @param String $intentname Name of the intent to be deleted/disposed
     * @param boolean $is_notoriousmode [false] If true, this will never stop
     *      until specified intent is not deleted.
     * @return boolean Boolean value if an existing intent was deleted,
     *      otherwise, no intent was deleted.
     */
    public static function DeleteIntent($intentname, $is_notoriousmode=false) {
        reset($_SESSION);
        for ( $x=0; $x<count($_SESSION); $x++, next($_SESSION) ) {
            if ( key($_SESSION)===("intent_".$intentname) ) {
                do {
                    echo 'Unsetting ' . key($_SESSION) . '<br>';
                    unset($_SESSION[key($_SESSION)]);
                } while($is_notoriousmode && DATA::__HasIntentData($intentname));
                return true;
            }
        }
        return false;
    }
    
    /**
     * Deletes multiple intents
     * @param Array $intents Array of names of intents to be deleted/destroyed
     * @param Boolean $refresh_onsuccess [optional=false] If page should be refreshed once one or more
     *      intents has/have been successfully deleted.
     * @param boolean $is_notoriousmode [false] If true, this will never stop
     *      until specified intent is not deleted.
     */
    public static function DeleteIntents(array $intents, $refresh_onsuccess=true, $is_notoriousmode=false) {
        $make_refresh = false;
        foreach($intents as $intent) {
            if (self::DeleteIntent($intent, $is_notoriousmode) && !$make_refresh && $refresh_onsuccess) {
                $make_refresh = true;
            }
        }
        if ($make_refresh) {
            UI::RefreshPage();
        }
    }
    
    /**
     * Destroy all current intent data
     * @param boolean $refresh_onsuccess [false] If page should be refreshed once
     *      at least an intent has been deleted.
     * @param boolean $is_notoriousmode [false] If will force to reiteratively delete
     *      all intents until nothing is left.<br>
     *      Use only when intents stick like pussy worms and you need smash jerks :D
     * @return boolean Boolean value if there had been <b>at least 1</b> intent
     *      destroyed. False if none was.
     */
    public static function DestroyIntents($refresh_onsuccess=false, $is_notoriousmode=true) {
        $is_somethingcleared = false;
        while ($is_notoriousmode && DATA::__HasIntentData()) {
            if (count($_SESSION) > 0) {
                $ctr = 0;
                while($ctr < count($_SESSION)) {
                    if (strpos(key($_SESSION), "intent_")!==FALSE) {
                        unset($_SESSION[key($_SESSION)]);
                        $is_somethingcleared = true;
                    }
                    next($_SESSION);
                    $ctr++;
                }
                reset($_SESSION);
            }
        }
        if ($is_somethingcleared && $refresh_onsuccess) {
            UI::RefreshPage();
        }
        return $is_somethingcleared;
    }
    
    /**
     * 100% deletes and shreds all intents in this page
     */
    public static function FullDestroyIntents() {
        while (self::__HasIntentData()) {
            self::DestroyIntents();
        }
    }

    /**
     * Generate Intent data from current $_GET parameters and (optional) eliminate $_GET from URL.<br>
     * <b>Warning:</b> This will redirect you back to <b>Index::DEFAULT_PAGE</b> or <i>$str_redirectpage</i>
     *      once Data Passage Gate is not open.
     * <br>
     * @param Array $a_keyslist (Optional) The $_GET keys to be extracted with as Intents
     * @param Boolean $is_refresh (Optional) Boolean value if the current page should be refreshed.
     *      This approach is usually done when you want to get rid of GET parameters from URL
     *      since values of these Intents have already been secured.
     * @param String $str_redirectpage (Optional) If not null, this will enforce page redirection if
     *      one of the specified $_GET keys on <i>$a_getkeys</i> were not found.
     */
    public static function GenerateIntentsFromGET($a_keyslist = array(), $is_refresh = true, $str_redirectpage=null) {
        // Check first if Passage Gate is open, otherwise, supplied $_GET data should be malicious!
        if (!self::__IsPassageOpen()) {
            $redirectpage = is_null($str_redirectpage) ? Index::$DEFAULT_PAGE : trim($str_redirectpage);
            FLASH::addFlash('Woah, unauthorized data supplied in page <b>' . Index::__GetPage() . '</b>, '
                    . 'and we just fixed the error for you ;)',
                    [
                        'admin-home', 'home', 'user-home'
                    ], 'ERROR', true);
            UI::RedirectTo($redirectpage);
            return false;
        }
        
        if (count($_GET) > 1) {
            do {
                if (strtolower(key($_GET))=='page') {
                    continue;
                } else if (count($a_keyslist) > 0 ? !key_exists(key($_GET), $a_keyslist) : false) {
                    if (!is_null($str_redirectpage)) {
                        $redirectpage = trim($str_redirectpage);
                        FLASH::addFlash('Non qualified data was accidentally supplied in page <b>' . Index::__GetPage() . '</b> '
                                . 'but don\'t worry, geeks are on the way to fix it.',
                                [
                                    'admin-home', 'home', 'user-home'
                                ], 'ERROR', true);
                        UI::RedirectTo($redirectpage);
                    } else {
                        continue;
                    }
                }
                self::CreateIntent(strtoupper(key($_GET)), current($_GET));
            } while (next($_GET));
            reset($_GET);
            if ($is_refresh) {
                UI::RedirectTo(Index::__GetPage());
            }
        }
    }

    /**
     * Opens a <b>single</b> GET method passage<br>
     * This is useful when you want to allow all programmer-defined GET values (or <b>INTENTS</b>)
     * @param String $str_targetpage The target page for this passage opening
     * @param bool $is_append [false] If this passage should be appended or not
     * @param bool $renew_hash [true] If renew the hash identifier
     */
    public static function openPassage($str_targetpage, $is_append=false, $renew_hash=true) {
        if ($renew_hash) {
            $_SESSION[self::$SESS_DATALOCK_TIME] = time();
            $_SESSION[self::$SESS_DATALOCK_KEY] = self::__GenerateRandomhash();
        }
        if (!isset($_SESSION[self::$SESS_DATALOCK_TARGETPAGES])) {
            $_SESSION[self::$SESS_DATALOCK_TARGETPAGES] = array();
        }
        
        if (!$is_append) { // clear if !$is_append
            $_SESSION[self::$SESS_DATALOCK_TARGETPAGES] = array();
        } else { // "search" and "unset" duplicate page entry
            ARRAYS::DeleteAllWith($str_targetpage, $_SESSION[self::$SESS_DATALOCK_TARGETPAGES], true, false);
        }
        array_push($_SESSION[self::$SESS_DATALOCK_TARGETPAGES], strtolower(trim($str_targetpage)));
    }
    
    /**
     * Opens <b>multiple</b> GET method passages to various page targets<br>
     * This is useful when you want to allow all programmer-defined GET values (or <b>INTENTS</b>)
     * @param Array $str_targetpages
     * @param bool $is_append [false] If these passages should be appended to existing or not.
     */
    public static function openPassages($str_targetpages, $is_append=false) {
        $_SESSION[self::$SESS_DATALOCK_TIME] = time();
        $_SESSION[self::$SESS_DATALOCK_KEY] = self::__GenerateRandomhash();
        if (!$is_append) {
            $_SESSION[self::$SESS_DATALOCK_TARGETPAGES] = $str_targetpages;
        } else {
            foreach($str_targetpages as $targetpage) {
                self::openPassage($targetpage, $is_append, false);
            }
        }
    }
    
    /**
     * Sets the value of an intent
     * @param string $intentname The name of the intent
     * @param mixed $value The value of to be assigned
     */
    public static function SetIntent($intentname, $value) {
        $_SESSION['intent_' . $intentname] = $value;
    }

}
