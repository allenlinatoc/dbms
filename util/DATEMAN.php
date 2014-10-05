<?php


/**
 * Static utility class for basic Date management
 * @author Allen
 */
class DATEMAN {
    
    const DEFAULT_TZ = 'Asia/Manila';
    const DATE_FORMAT = 'Y-m-d';
    const DATE_FORMAT_JQUERY = 'm-d-Y';
    const TIME_FORMAT = 'H:i:s';
    public static $DATETIME_FORMAT = 'Y-m-d H:i:s';
    
    
    /**
     * Create a DateTime object from current/given time
     * @param string $str_time The string where the DateTime will be derived. If null, it will supply the current date and time.
     * @return DateTime
     */
    public static function DateTime($str_time = null) {
        return DateTime::createFromFormat(self::$DATETIME_FORMAT, 
                is_null($str_time) ? self::getStdDatetime() : $str_time);
    }
    
    /**
     * Return the current date in standard system format.<br>
     * FORMAT: YYYY-MM-DD
     * @return String 
     */
    public static function getDate() {
        return date(self::DATE_FORMAT);
    }
    
    public static function getMonth() {
        return date('m');
    }
    
    /**
     * Return the current standard date and time<br>
     * FORMAT: YYYY-MM-DD 24HH:MM:SS (PHP: 'Y-m-d H:i:s')
     * @return String The string formatted date
     */
    public static function getStdDatetime() {
        return date(self::$DATETIME_FORMAT);
    }
    
    /**
     * Return the current time in standard system format.<br>
     * FORMAT: 24H:mm:ss
     * @return String The string formatted date
     */
    public static function getTime() {
        return date(self::TIME_FORMAT);
    }
    
    /**
     * Return the current year
     * @return int
     */
    public static function getYear() {
        return intval(date('Y'));
    }
    
    public static function makeAgo($timestamp, $from_what=NULL, $forward=false) {
        if (is_null($from_what)) {
            $from_what = time();
        }
        $difference = $forward ? abs($from_what - $timestamp) : $from_what-$timestamp;
        $periods = array("sec", "min", "hr", "day", "week", "month", "year", "decade");
        $lengths = array("60","60","24","7","4.35","12","10");
        for($j = 0; $difference >= $lengths[$j]; $j++) {
                $difference /= $lengths[$j];
        }
                $difference = round($difference);
        if($difference != 1) $periods[$j].= "s";
        $text = "$difference $periods[$j]".(!$forward?'ago':'');
        return $text;
    }
    
    /**
     * Converts a DateTime object into string using standard system datetime format.
     * @param DateTime $DateTime_obj The DateTime object to be converted
     * @return String
     */
    public static function toString($DateTime_obj) {
        return date_format($DateTime_obj, self::$DATETIME_FORMAT);
    }
}
