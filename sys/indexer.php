<?php

/**
 * Page rendering engine for INDEX pages. Useful for index page manipulations.
 */
class Index {

    public static $DEFAULT_PAGE = 'home';
    public static $FLASHES = array();
    public static $MYSQLI;

    public function __construct() {
        // [BEGIN :: STANDARD PROCEDURES, do not modify]
        // ---- error reporting - all errors for development (ensure you have display_errors = On in your php.ini file)
        error_reporting(E_ALL | E_STRICT);
        mb_internal_encoding('UTF-8');
        // ---- set_exception_handler(array($this, 'handleException'));
        spl_autoload_register(array(
            $this,
            'LoadClass'
        ));
        // ---- session
        session_start();
        // ---- checking of dialog object
        if (array_key_exists("intent_DIALOG_OBJECT", $_SESSION)) {
            if (!is_object($_SESSION["intent_DIALOG_OBJECT"])) {
                unset($_SESSION["intent_DIALOG_OBJECT"]);
            }
        }
        // [END :: STANDARD PROCEDURES, do not modify]

        # ---- ALL CUSTOM INITIALIZATIONS SHOULD BE PLACED BELOW ---------------
        // Determine if System is properly installed
        if (!SYS::__isProperlyInstalled() && self::__GetPage() != 'install') {
            UI::RedirectTo('install');
        }

        // Determine value of Rootpath
        $WebLocation = parse_ini_file('app.ini')['WEB_LOCATION'];

        // Assign constants DIR( $ROOT, $PAGE )
        DIR::$ROOT = '/';
        DIR::$HEADER = DIR::$PAGE . 'header/';
        DIR::$FOOTER = DIR::$PAGE . 'footer/';
        SYS::$PAGES = parse_ini_file(DIR::$CONFIG . 'pages.ini');
        SYS::$SIDEBARS = parse_ini_file(DIR::$CONFIG . 'sidebars.ini');

        // Validate the current page action call
        $this->ValidatePage();

        // Checks for dedicated flashes, otherwise, clears it
        if (!FLASH::__IsDedicatedHere()) {
            FLASH::clearFlashes(self::__GetPage());
        }
        
        // Checks for "Data Passage Gate" page dedication, otherwise, destroy it
        //      to save data space
        if (!DATA::__IsPassageDedicatedHere()
                && strtolower(self::__GetPage())!='404'
                && strtolower(self::__GetPage())!='dialogbox' ) {
            # Close passage and destroy intents
            DATA::closePassage();
        }
        # ---- END OF CUSTOM INITIALIZATIONS -----------------------------------
    }

    public function LoadClass($classname) {
        $WebLocation = parse_ini_file('app.ini')['WEB_LOCATION'];
        $_assoc_Autoload = parse_ini_file('config/autoload.ini');
        if (!array_key_exists($classname, $_assoc_Autoload)) {
            echo '<br><b>Class <i>"' . $classname . '"</i> not found!</b><br>';
            return;
        }
        include $_assoc_Autoload [$classname];
        return;
    }

    public static function __GetPage() {
        if (!array_key_exists('page', $_GET)) {
            return self::$DEFAULT_PAGE;
        }
        return $_GET ['page'];
    }

    /**
     * Returns the path to the .
     * PHTML file of $page, otherwise, null.
     * 
     * @param String $page        	
     * @return String
     */
    public static function __GetPagefile($page) {
        $result = self::__HasScript($page) ? SYS::$PAGES [$page] . '.phtml' : null;
        return $result;
    }

    /**
     * Returns the path to the .
     * PHP file of $page, otherwise, null.
     * 
     * @param String $page        	
     * @return String
     */
    public static function __GetScriptfile($page) {
        $result = self::__HasScript($page) ? SYS::$PAGES [$page] . '.php' : null;
        return $result;
    }

    /**
     * Includes a sidebar component for page rendering
     * 
     * @param String $sidebarname
     *        	Name of the sidebar component (see keys of 'config/sidebars.ini')
     * @param Boolean $is_require
     *        	(Optional) Boolean value if files will be required or not
     */
    public static function __IncludeSidebar($sidebarname, $is_require = true) {
        if (!array_key_exists($sidebarname, SYS::$SIDEBARS)) {
            echo '<br>Sidebar "' . $sidebarname . '" does not exist!<br>';
            return;
        }
        if ($is_require) {
            require SYS::$SIDEBARS [$sidebarname] . '.php';
            require SYS::$SIDEBARS [$sidebarname] . '.phtml';
        } else {
            include SYS::$SIDEBARS [$sidebarname] . '.php';
            include SYS::$SIDEBARS [$sidebarname] . '.phtml';
        }
    }

    /**
     * Includes Miscellaneous files on page rendering
     * 
     * @param String $path
     *        	Path (no right-trailing slash) containing the files
     * @param Array $filenames
     *        	Linear-array containing paths to be included
     * @param Boolean $is_require
     *        	(Optional) Boolean value if file should be required or not
     */
    public static function __IncludeFiles($path, $filenames, $is_require = true) {
        // Remove right-trailing slashes
        $path = rtrim($path, '/');

        foreach ($filenames as $filename) {
            if ($is_require) {
                require $path . '/' . $filename;
            } else {
                include $path . '/' . $filename;
            }
        }
    }

    /**
     * Returns if page has page file (.phtml)
     * @param String $page        	
     * @return boolean
     */
    public static function __HasPage($page) {
        $pages = parse_ini_file(DIR::$CONFIG . 'pages.ini');
        if (array_key_exists($page, $pages)) {
            return file_exists($pages [$page] . '.phtml');
        } else {
            return false;
        }
    }

    /**
     * Returns if page has script file (.php)
     * @param String $page        	
     * @return boolean
     */
    public static function __HasScript($page) {
        if (!array_key_exists($page, SYS::$PAGES)) {
            return false;
        }
        return file_exists(SYS::$PAGES [$page] . '.php');
    }

    /**
     * Checks if $_POST has content during the load of this page
     * 
     * @param string $datakey
     *        	(Optional) The key of the post data to be extracted
     * @return boolean
     */
    public static function __HasPostData($datakey = null) {
        if (is_null($datakey)) {
            return count($_POST) > 0;
        } else {
            return array_key_exists($datakey, $_POST);
        }
    }

    /**
     * Renders the page by default parameters
     */
    public function Run() {
        $this->RenderPage(Index::__GetPage());
    }

    /**
     * Renders the page with specifiable parameters
     * 
     * @param type $page
     *        	Specific page to be rendered
     * @param type $header
     *        	Specific header page name to be rendered
     * @param type $footer
     *        	Specific footer page name to be rendered
     */
    public function RenderPage($page = null, $header = 'header', $footer = 'footer') {
        $page = (is_null($page) ? self::$DEFAULT_PAGE : $page);

        // Render HEADER
        include DIR::$HEADER . $header . '.php';
        include DIR::$HEADER . $header . '.phtml';

        // Render BODY
        include self::__GetScriptfile($page);
        // Include flashes
        FLASH::Initialize();
        include self::__GetPagefile($page);

        // Render FOOTER
        include DIR::$FOOTER . $footer . '.php';
        include DIR::$FOOTER . $footer . '.phtml';
    }

    /**
     * Validate the current page, otherwise, the supplied page
     * @param String $page The page to be validated
     * @return boolean
     */
    private function ValidatePage($page = null) {
        if ($page == null) {
            $page = Index::__GetPage() != null ? Index::__GetPage() : self::$DEFAULT_PAGE;
        }
        if (!preg_match('/^[a-z0-9-]+$/i', $page)) {
            // TODO log attempt, redirect attacker, ...
            // throw new Exception('Unsafe page "' . $page . '" requested');
            header('location:/?page=404&target=' . $page . '&malicious=yes');
        }
        if (!self::__HasPage($page) /* && !$this->__HasScript($page) */) {
            // TODO log attempt, redirect attacker, ...
            // throw new Exception('Page "' . $page . '" not found');
            header('location:?page=404&target=' . $page);
        }
        return true;
    }

}
