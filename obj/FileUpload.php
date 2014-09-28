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

    private $allowed_extensions;
    private $blocked_extensions;
    private $error;
    private $keyname;
    private $metadata;
    
    public function __construct($keyname=null)
    {
        parent::__construct();
        $this->allowed_extensions = array();
        $this->blocked_extensions = array();
        $this->is_filter = false;
        $this->keyname = $keyname;
        $this->metadata = array();
        if (!is_null($keyname)) {
            $this->Load($keyname);
        }
    }
    
    public function CreateUniqueFilenames($destpath, $a_filenames, $separator, $is_addextensions=true)
    {
        // resulting filenames
        $result = array(); 
        
        // convert non-array to array
        if (!is_array($a_filenames)) {
            $a_filenames = array(0 => $a_filenames);
        }
        
        // process
        $ctr = 0;
        foreach ($a_filenames as $filename)
        {
            $new_Filename = '';
            $z = 0;
            do {
                $extension = pathinfo($this->metadata['name'][$ctr], PATHINFO_EXTENSION);
                $new_Filename = $filename . ($z>0 ? ($separator.$z):'') . ($is_addextensions ? ('.'.$extension):'');
                $IOsys = new IOSys($destpath . '/' . $new_Filename);
                $z++;
            }
            while($IOsys->Exists());
            
            // if new_Filename has been finalized
            array_push($result, $new_Filename);
            $ctr++;
        }
        return $result;
    }
    
    /**
     * Loads an uploaded file from $_FILES
     * @param String $postname Name of the $_FILES key
     * @return boolean If specified $_FILES key exists
     */
    public function Load($keyname)
    {
        if (array_key_exists($keyname, $_FILES)) {
            if ($this->__HasUploadError()) {
                $this->error = $_FILES[$keyname]['error'];
                return false;
            }
            $this->keyname = $keyname;
            $this->metadata = $_FILES[$keyname];
            return true;
        }
        return false;
    }
    
    /**
     * Saves the uploaded file into a destination path
     * 
     * @param String $destpath Path where this uploaded file will be saved
     * @param String|Array $filename [null] The target filename/s when saved, if null,
     *      will use the client's original filename/s
     * @param String $str_nameseparator [_] Name separator when only one filename is supplied
     *      and multiple files are to be uploaded
     * @return boolean|int|Array ARRAY of Filenames if saving is success, FALSE if filename/either of filenames already exist in
     *      the destination path or programming reasons,INTEGER(0,1,2) or the index of the filename where
     *      saving process failed
     */
    public function Save($destpath, $filename=null, $str_nameseparator='_')
    {
        $destpath = rtrim($destpath, '/');
        // Will only be returned when saving is success
        $savedFilenames = array();
        
        if (!file_exists($destpath)) {
            echo '<br><b>Error FileUpload::Save('.$destpath.','.$filename.')</b> - Destination path does not exist<br>'.PHP_EOL;
            return false;
        }
        if ($this->__IsMultiple()) {
            // For multiple files
            $filenames = array();
            
            // IF NO FILENAME is supplied, get original filenames
            if ($filename===null) {
                foreach($this->metadata['name'] as $name) {
                    array_push($filenames, $name);
                }
            }
            // else IF filenames were manually supplied
            else if (is_array($filename)) {
                if (is_array($filename) && count($filename) !== count($this->metadata['name'])) {
                    // check if number of filenames supplied is not equal to the number of uploaded files
                    echo '<b>Error FileUpload::Save(..)</b> - Filenames supplied is not equal to the number of uploaded files='
                    . count($this->metadata['name']).'<br>'.PHP_EOL;
                    return false;
                }
                $filenames = $filename;
                // append file extensions respectively
                for (reset($filenames),$x=0; $x<count($filenames); $x++,next($filenames)) {
                    $filenames[key($filenames)] = current($filenames) . pathinfo(current($filenames), PATHINFO_EXTENSION);
                }
            }
            // IF ONLY ONE filename is supplied, consider it a template
            else {
                $filenames = array();
                for ($x=0,$counter=0; $x<$this->__GetFilesCount(); $x++)
                {
                    // Generate unique/non-existing indexed filename first
                    do {
                        $indexedFilename = $filename.$str_nameseparator.($counter+1);
                        $counter++;
                    } while (file_exists($destpath.'/'.$indexedFilename));
                    array_push($filenames, $indexedFilename);
                }
            }
            
            // Generating unique filenames from existing filenames
            $filenames = $this->CreateUniqueFilenames($destpath, $filenames, $str_nameseparator, !is_null($filename));
            
            // Saving files
            for(reset($filenames),$x=0; $x<count($filenames); $x++,next($filenames))
            {
                if (!move_uploaded_file($this->metadata['tmp_name'][$x], $destpath.'/'.$filenames[key($filenames)])) {
                    return $x;
                }
                array_push($savedFilenames, $destpath.'/'.$filenames[key($filenames)]);
            }
            return $filenames;
        }
        else {
            // filter out array filenames since we're only uploading 
            if (is_array($filename)) {
                // always get the first filename if array is supplied
                $filename = $filename[0];
            }
            $s_filename = is_null($filename) ? $this->metadata['name'][0] : $filename;
            $filename = $this->CreateUniqueFilenames($destpath, $s_filename, '_')[0];
            /**
            do {
                $filename = $s_filename . ($i>0 ? $i:'') . '.'
                    . strtolower(pathinfo($this->metadata['name'][0], PATHINFO_EXTENSION));
                parent::__construct($destpath.'/'.$filename);
                $i++;
            } while($this->Exists());
            */
            
            // Saving files
            if (!move_uploaded_file($this->metadata['tmp_name'][0], $destpath.'/'.$filename)) {
                return 0;
            }
            else {
                array_push($savedFilenames, $destpath.'/'.$filename);
            }
            return $savedFilenames;
        }
    }
    
    public function SetAllowedExtensions($a_extensions)
    {
        $this->allowed_extensions = $a_extensions;
    }
    
    public function SetBlockedExtensions($a_extensions)
    {
        $this->blocked_extensions = $a_extensions;
    }
    
    public function __GetError()
    {
        return $this->error;
    }
    
    public function __GetFilesCount()
    {
        return count($this->metadata['name']);
    }
    
    public function __GetExtension() {
        
    }
    
    public function __GetFilename()
    {
        return $_FILES[$this->keyname]['name'];
    }
    
    /**
     * Get the metadata of the uploaded file(s)
     * @return Array
     */
    public function __GetMetadata()
    {
        return $this->metadata;
    }
    
    /**
     * Get the filename of the temporary data on server
     * @return String
     */
    public function __GetTempname()
    {
        return $_FILES[$this->keyname]['tmp_name'];
    }
    
    /**
     * If multipled files were uploaded
     * @return boolean
     */
    public function __IsMultiple()
    {
        return (count($_FILES[$this->keyname]['name']) > 1);
    }
    
    /**
     * If this file or either of these files are allowed
     * @param boolean $is_allowables_only
     * @return boolean
     */
    public function __IsAllowed($is_allowables_only=true)
    {
        for ($x=0; $x<count($this->metadata['name']); $x++) {
            $extension = pathinfo($this->metadata['name'][$x], PATHINFO_EXTENSION);
            if ($is_allowables_only) {
                if (array_search($extension, $this->allowed_extensions)===FALSE) {
                    return FALSE;
                }
            }
            else {
                if (array_search($extension, $this->blocked_extensions)!==FALSE) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }
    
    public function __HasUploadError() {
        foreach($_FILES[$this->keyname]['error'] as $error) {
            if ( $error!==UPLOAD_ERR_OK ) {
                return true;
            }
        }
        return false;
    }
    
    
 // ---- Protected methods
    protected function __HasTargetExistence($destpath, $filenames) {
        if (is_array($filenames)) {
            foreach ($filenames as $filename) {
                if (file_exists($destpath.'/'.$filename)) {
                    return true;
                }
            }
            return false;
        }
        else {
            file_exists($destpath.'/'.$filenames);
        }
    }
 
}
