<?php

/**
 * Class for generating up to 60,466,176 (default) unique identification codes
 */
final class CODES {
    
    private static $CHARSET = 'ACDEFGHIJKLMNOPWRSTUVWXYZ';
    
    /**
     * Generate a character-sequenced identification string code
     * @param int $int_count The identification value or count
     * @param int $length (Optional) The target length of the string code
     * @param Boolean $is_reversed (Optional) Boolean value if resulting string
     *      should be reversed or not
     * @return String The generated identification string code
     */
    public static function CreateSequenced($int_count, $length=4, $is_reversed=false) {
        $result = '';
        $a_int = array();
        
        while($length > 0) {
            array_push($a_int, 0);
            $length--;
        }
        while($int_count > 0) {
            $remainder = false;
            for($x=count($a_int)-1; $x>=0; $x--) {
                if ($x==(count($a_int)-1)) {
                    $a_int[$x] += 1;
                }
                if ($remainder) {
                    $a_int[$x] += 1;
                }
                if ($a_int[$x] >= strlen(self::$CHARSET)) {
                    $remainder = true;
                    $a_int[$x] = 0;
                } else {
                    break;
                }
            }
            $int_count--;
        }
        foreach ($a_int as $intval) {
            $result .= self::$CHARSET[$intval];
        }
        
        return $is_reversed ? strrev($result) : $result;
    }
    
    /**
     * Create a hash-based identification string code
     * @param int $intval The identification value to be hashed
     * @param int $int_length (Optional) The target length of the string code
     * @return boolean
     */
    public static function CreateHashed($intval, $int_length=null) {
        $result = strtoupper(base64_encode(crypt(strval($intval), 'salt')));
        $result = str_replace('=', '', $result);
        if (!is_null($int_length)) {
            if ($int_length > 10) {
                echo('<br><b>Error: </b>(CODES.php)');
                echo('<br>Parameter supplied <b>$int_length</b> in CODES.php::CreateHashed is greater than max (10).<br>');
                return false;
            } else {
                $result = substr($result, strlen($result)-$int_length, $int_length);
            }
        }
        return $result;
    }
    
}

?>