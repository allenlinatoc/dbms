<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class for basic manipulation of file uploads
 *
 * @author Allen
 */
class FileUpload extends IOSys {
    
    private $keyname;
    private $error;
    
    public function __construct($postname=null)
    {
        parent::__construct();
        $this->keyname = $postname;
    }
    
    /**
     * Loads an uploaded file from $_FILES
     * @param String $postname Name of the $_FILES key
     * @return boolean If specified $_FILES key exists
     */
    public function Load($keyname)
    {
        if (key_exists($keyname, $_FILES)) {
            if ($_FILES[$keyname]['error'] != UPLOAD_ERR_OK) {
                $this->error = $_FILES[$keyname]['error'];
                return false;
            }
            $this->keyname = $keyname;
            return true;
        }
        return false;
    }
    
    /**
     * Saves the uploaded file into a destination path
     * @param String $destpath Path where this uploaded file will be saved
     * @param String $filename [null] The target filename when saved, if null,
     *      will use the client's original filename
     * @return boolean If saving is success
     */
    public function Save($destpath, $filename=null)
    {
        if (!file_exists($destpath)) {
            echo '<br><b>Error FileUpload::Save('.$destpath.','.$filename.')</b> - Destination path does not exist<br>'.PHP_EOL;
            return false;
        }
        if (is_null($filename)) {
            $filename = $_FILES[$this->keyname]['name'];
        }
        return move_uploaded_file($filename, $destpath);
    }
    
    public function __GetError()
    {
        return $this->error;
    }
    
    public function __GetFilename()
    {
        return $_FILES[$this->keyname]['name'];
    }
    
    public function __GetTempname()
    {
        return $_FILES[$this->keyname]['tmp_name'];
    }
    
    public function __IsMultiple()
    {
        return is_array($_FILES[$this->keyname]['name']);
    }
    
    
}
