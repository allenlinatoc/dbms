<?php

/**
 * Static utility class for String management and checkings
 */
final class STR {

    /**
     * Checks if the string contains spaces
     * @param String $string The string to be validated
     * @return boolean
     */
    public static function __HasSpaces($string) {
        for ($x=0; $x<strlen($string); $x++) {
            if ($string[$x] == ' ') return true;
        } return false;
    }

    /**
     * Checks if the string contains punctuation marks and/or spaces
     * @param String $string The string to be validated
     * @param Boolean $is_includespaces (Optional) Boolean value if spaces should also be restricted
     * @return boolean
     */
    public static function __HasPunct($string, $is_includespaces = false) {
        return ctype_punct($string) && ($is_includespaces ? self::__HasSpaces($string) : true);
    }
    
    /**
     * Checks if a string is blank
     * @param String $string The string
     * @param boolean $is_trim [false] If true, it will remove trailing spaces
     *      before checking.
     * @return boolean
     */
    public static function __IsBlank($string, $is_trim=false) {
        if ($is_trim) {
            $string = trim($string);
        }
        return strlen($string) <= 0;
    }

    /**
     * Checks if the string is a valid numeric value
     * @param String $string The string to be validated
     * @return boolean
     */
    public static function __IsNumericOnly($string) {
        return ctype_digit($string);
    }

    /**
     * Checks if the string is a valid username value
     * @param String $string The username string to be validated
     * @return boolean
     */
    public static function __IsValidUsername($string) {
        return !self::__HasPunct($string, true);
    }

    /**
     * Format a name 'FirstNAME MiddleINITIAL. LastNAME' into certain formats
     * @param String $name The name to be processed
     * @param String $formatmask Use the following:<br>
     * <ul>
     * <li></li>
     * </ul>
     * @return String The newly masked name
     */
    public static function FormatName($name, $formatmask) {
        $name = self::RemoveExcessiveSpacing($name);

        $fname = '';
        $mname = '';
        $lname = '';

        $mode = 'FIRSTNAME'; # FIRSTNAME|| LASTNAME

        $a_name = explode(' ', $name);
        foreach ($a_name as $name) {
            $has_dot = $name[strlen($name) - 1] == '.';

            # For `fname`
            if ($mode == 'FIRSTNAME' && !$has_dot) {
                $fname .= $name . ' ';
            }
            # For `mname`
            else if ($mode == 'FIRSTNAME' && $has_dot) {
                $mname = str_replace('.', '', $name) . ' ';
                $mode = 'LASTNAME';
            }
            # For `lname`
            else if ($mode == 'LASTNAME' && !$has_dot) {
                $lname .= $name . ' ';
            }
        }
        
        // String cleanup
        $fname = trim($fname);
        $mname = trim($mname);
        $lname = trim($lname);

        // Masking phase
        $result = $formatmask;
        $result = str_replace('%F', $fname, $result);
        $result = str_replace('%M', $mname, $result);
        $result = str_replace('%L', $lname, $result);

        return $result;
    }
    
    /**
     * Inserts a string into an existing string
     * @param String $str_value The existing target string
     * @param String $str_insert The string value to be inserted
     * @param int $pos Position where string will be inserted
     */
    public static function InsertAt(&$str_value, $str_insert, $pos) {
        $result = substr($str_value, 0, $pos) . $str_insert . substr($str_value, $pos);
        $str_value = $result;
    }
    
    /**
     * Checks if a string meets the specified masking format
     * @param string $strval The string to be evaluated
     * @param string $mask The string mask where ANY chars is represented by (*) asterisk<br>
     * <b>Example:</b> <b>?12-JO</b> can be represented by mask <b>?**-**</b><br>
     * @return boolean Boolean value if masking format is successfully met.
     */
    public static function IsFormatted($strval, $mask) {
        if (strlen($strval) != strlen($mask)) {
            return false;
        }
        for($x=0; $x<strlen($mask); $x++) {
            if ($mask[$x] != '*' && $mask[$x]!=$strval[$x]) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns a limited version of the string, where a string ('...' by default) is appended
     * @param string $str_value The string value to be limited
     * @param int $maxlength Maximum string length tolerance for limit rule
     * @param string $appendstr (Optional) The string to be appended at the charlimit portion
     * @param boolean $is_trimspace (Optional) Boolean value if output should have
     *      trailing spaces trimmed
     * @param boolean $is_forceappend (Optional) Boolean value if appending string should
     *      still be appended even if target length is below the max
     * @return type
     */
    public static function Limit($str_value, $maxlength, $appendstr='...', $is_trimspace=true, $is_forceappend=false) {
        $result = strlen($str_value) > $maxlength ?
                ($is_trimspace ? trim(substr($str_value, 0, $maxlength)) : substr($str_value, 0, $maxlength)) . $appendstr
              : ($is_trimspace ? trim($str_value) : $str_value) . ($is_forceappend ? $appendstr : '');
        return $result;
    }

    /**
     * Removes excessive usage of spaces in certain string
     * @param String $str_specimen The specimen to be processed
     * @param String The processed string
     */
    public static function RemoveExcessiveSpacing($str_specimen) {
        # Trim out trailing left-right spaces
        $str_specimen = trim($str_specimen);
        $str_processed = '';

        # Start doing this job
        $a_chars = str_split($str_specimen);

        $has_last_space = false; // If there's a space char that currently exist
        $ctr = 0; // Counter/pointer
        foreach ($a_chars as $char) {
            if ($char == ' ' && $has_last_space) {
                $ctr++;
                continue;
            }

            // Notify the secretary
            if ($char == ' ' && !$has_last_space) {
                $has_last_space = true;
            } else if ($char != ' ' && $has_last_space) {
                $has_last_space = false;
            }

            $str_processed .= $char;
            $ctr++;
        }
        return $str_processed;
    }
    
    /**
     * Removes the spaces in a string
     * @param String $string
     * @return String
     */
    public static function RemoveSpaces($string) {
        $result = '';
        for ($x=0; $x<strlen($string); $x++) {
            if ($string[$x] != ' ') {
                $result .= $string[$x];
            }
        }
        return $result;
    }

}

?>