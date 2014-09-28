<?php

/**
 * Static class containing CONSTANT VALUES
 */
final class SYS {
    
    /**
     * Associative array of viewable site pages
     * @var Array(assoc)
     */
    static public $PAGES = array();
    /**
     * Associative array of sidebar pages
     * @var Array(assoc)
     */
    static public $SIDEBARS = array();
    
    
    
    # ----------------- CONFIGURATION VALUES -----------------
    /**
     * The default INI file for admin info
     * @var String 
     */
    static public $CONFIG_ADMIN_FILENAME = 'admin.ini';
    /**
     * The default INI file for database connectivity configuration
     * @var String
     */
    static public $CONFIG_DB_FILENAME = 'database.ini';
    /**
     * The default INI file for System configuration
     * @var String
     */
    static public $CONFIG_SYSTEM_FILENAME = 'system.ini';
     
    
    /**
     * Returns an array of viewable site pages
     * @return Array(Assoc)
     */
    public static function __getPages() {
        self::$PAGES = parse_ini_file(DIR::$CONFIG . 'pages.ini');
        return self::$PAGES;
    }
    
    /**
     * Check if the system is properly installed.
     * @return boolean Boolean value if system is properly installed or not
     */
    public static function __isProperlyInstalled() {
        return 
            # Check Database configuration
            file_exists(DIR::$CONFIG . SYS::$CONFIG_DB_FILENAME) &&
                
            # Check Admin configuration
            file_exists(DIR::$CONFIG . SYS::$CONFIG_ADMIN_FILENAME);
    }
    
}

?>