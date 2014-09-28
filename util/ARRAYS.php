<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of ARRAY
 *
 * @author Allen
 */
final class ARRAYS {
    
    /**
     * Deletes all array elements whose values match with specified string value
     * @param String $strval The string to search
     * @param String $a_targetarray (ref) Target array
     * @param Boolean $is_exact [optional=true] If exact string comparison should be implemented, otherwise,
     *      will use substring comparison
     * @param Boolean $is_casesensitive [optional=true] If comparison should be case-Sensitive
     */
    public static function DeleteAllWith($strval, &$a_targetarray, $is_exact=true, $is_casesensitive=true) {
        reset($a_targetarray);
        for ($x=0; $x<count($a_targetarray); $x++,next($a_targetarray)) {
            $value = $is_casesensitive ? current($a_targetarray) : strtolower(current($a_targetarray));
            $strval = $is_casesensitive ? $strval : strtolower($strval);
            $is_found = $is_exact ? (strcmp($value,$strval)===0) : (strpos($value, $strval)!==FALSE);
            if ($is_found) {
                unset($a_targetarray[key($a_targetarray)]);
            }
        }
        reset($a_targetarray);
    }
    
    /**
     * Merges 2 arrays
     * @param Array $array1 (Reference)The first array and the container
     * @param Array $array2 The second array
     */
    public static function Merge(&$array1, $array2) {
        foreach($array2 as $element) {
            array_push($array1, $element);
        }
    }
    
    /**
     * Search for the array value which contains the specified substring value
     * @param String $str_value The substring value to search
     * @param Array|Array(assoc) $a_haystack The target array to be searched
     * @param bool $is_firstoccurence [optional=true] Boolean value if first occurence key will be returned, 
     *      otherwise, last occurence key will be returned
     * @return int|string|boolean The key of the matched substring value, otherwise, FALSE
     */
    public static function SearchSubstring($str_value, &$a_haystack, $is_firstoccurence=true) {
        $occurence = -1;
        reset($a_haystack);
        for($x=0; $x<count($a_haystack); $x++, next($a_haystack)) {
            $string = strval(current($a_haystack));
            if (strpos($string, $str_value)!==FALSE) {
                if ($is_firstoccurence) {
                    return key($a_haystack);
                } else {
                    $occurence = key($a_haystack);
                }
            }
        }
        if (!$is_firstoccurence && $occurence!=-1) {
            return $occurence;
        } else {
            return FALSE;
        }
    }
    
    
}
