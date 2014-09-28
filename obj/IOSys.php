<?php

/**
 * Description of IOSYS
 *
 * @author Allen
 */
final class IOSys {
    
    public $data;
    public $path;
    
    public function __construct($path) {
        $this->path = $path;
        $this->data = '';
        if (!file_exists($path)) {
            echo '<br><b>Error IOSys::construct('.$path.')</b> - File not found<br>'.PHP_EOL;
        }
    }
    
    public function Delete() {
        return unlink($this->path);
    }
    
    public function MoveFile($newpath) {
        $result = copy($this->path, $newpath);
        $this->Delete();
        $this->path = $newpath;
        return $result;
    }
    
    /**
     * Reads the contents of file, otherwise, FALSE on error
     * @param type $is_appendtocurrentdata
     * @return String|null The contents of file, otherwise, FALSE
     */
    public function Read($is_appendtocurrentdata = false) {
        if (!file_exists($this->path)) {
            # FALSE if file doesn't exist
            return false;
        }
        if (!$is_appendtocurrentdata) {
            # If no-append, then truncate the current contents
            $this->data = '';
        }
        
        # If it exists, continue reading data
        $handler = fopen($this->path, 'r') or die('Error while opening file "'.$this->path.'"');
        fseek($handler, 0);
        while (!feof($handler)) {
            $this->data .= fgetc($handler);
        }
        fclose($handler);
        return $this->data;
    }
    
    public function Rename($newfilename) {
        $olddir = dirname($this->path);
        if ($olddir != dirname($newfilename)) {
            echo '<b>Error IOSys::Rename('.$newfilename.'): </b>Base directories are different<br>'.PHP_EOL;
            return false;
        }
        if (file_exists($newfilename)) {
            echo '<b>Error IOSys::Rename('.$newfilename.'): </b>Assigned new filename already exists<br>'.PHP_EOL;
            return false;
        }
        return rename($this->path, $newfilename);
    }
    
    public function Write($is_append, $data, $is_createifnotexist=true) {
        if (!$is_createifnotexist) {
            if (!file_exists($this->path)) {
                return false;
            }
        }
        $stream_mode = ($is_append ? 'a':'w');
        $handler = fopen($this->path, $stream_mode);
        fwrite($handler, $data);
        fclose($handler);
    }
    
}


