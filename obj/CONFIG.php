<?php

/**
 * Class for CONFIG file (ini) management and processing engine
 */
final class CONFIG {

    public $path;
    public $configdata = array();
    public $headers = array();

    public function __construct($path = null) {
        $this->path = $path;
        $this->configdata = array();
    }

    /**
     * Creates an INI file (virtually)
     * @param String $path The path of the INI file to be created
     */
    public function Create($path) {
        $this->path = $path;
    }

    public function Exists() {
        return file_exists($this->path);
    }
    
    /**
     * Gets the value of certain config key
     * @param String $configkey The name of the config key where you want to
     *      get the value of certain configuration parameter
     * @return mixed The corresponding value of the config key, otherwise,
     *      returns null
     */
    public function Get($configkey) {
        $this->Read();
        if (key_exists($configkey, $this->configdata)) {
            return $this->configdata[$configkey];
        }
        return null;
    }
    
    /**
     * 
     * @param String $configkey
     * @param String $expectedvalue The expected corresponding value of the
     *      config key.
     * @param boolean $is_casesensitive [false] If case-sensitive or not
     * @return boolean If vaue checking is true
     */
    public function IsTrue($configkey, $expectedvalue, $is_casesensitive=false) {
        $this->Read();
        $corresp_value = '{NO_VALUE_FOR_NOW}';
        if (key_exists($configkey, $this->configdata)) {
            $corresp_value = $this->configdata[$configkey];
            if (!$is_casesensitive) {
                $expectedvalue = strtolower($expectedvalue);
                $corresp_value = strtolower($expectedvalue);
            }
        }
        return $expectedvalue==$corresp_value;
    }

    public function Load($configdata, $is_append=false) {
        if ($is_append) {
            ARRAYS::Merge($this->configdata, $configdata);
        } else {
            $this->configdata = $configdata;
        }
    }

    public function Read() {
        if (!$this->Exists()) {
            return false;
        }
        $this->configdata = parse_ini_file($this->path);
        return $this->configdata;
    }

    public function SetHeader($arr_headers) {
        $this->headers = $arr_headers;
    }
    
    public function Set($key, $value) {
        if (!key_exists($key, $this->configdata)) {
            array_push($this->configdata, array($key => $value));
        } else {
            $this->configdata[$key] = $value;
        }
        return $this;
    }

    /**
     * Writes the content of this instance to the set path
     * @param Array(assoc) $configdata Assoc-array --> [key] = [value]
     */
    public function Write($configdata = null) {
        if ($configdata !== null) {
            $this->configdata = $configdata;
        }
        $handle = fopen($this->path, 'w+');

        foreach ($this->headers as $headerline) {
            fwrite($handle, '; ' . $headerline . PHP_EOL);
        }
        fwrite($handle, PHP_EOL);
        $i = 0;
        while ($i < count($this->configdata)) {
            fwrite($handle, key($this->configdata) . ' = ' . current($this->configdata) . PHP_EOL);
            next($this->configdata);
            $i++;
        }
        reset($this->configdata);
    }

}

?>