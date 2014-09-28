<?php

/**
 * Description of IOSYS
 *
 * @author Allen
 */
final class IOSYS {
    
    public $data;
    public $path;
    
    public function __construct($path) {
        $this->path = $path;
        $this->data = '';
    }
    
    public function Delete() {
        if (!file_exists($this->path)) {
            return true;
        }
        \unlink($this->path);
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


