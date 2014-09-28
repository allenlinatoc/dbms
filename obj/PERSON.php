<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PERSON
 *
 * @author Allen
 */
class PERSON {

    // Properties
    public $Age;
    public $Firstname;
    public $Lastname;
    public $Gender;
    public $Visitedplaces;

    public function __construct($Firstname, $Lastname) {
        $this->Firstname = $Firstname;
        $this->Lastname = $Lastname;
        $this->Gender = null;
        $this->Age = null;
        $this->Visitedplaces = array();
    }

    /**
     * Returns a boolean if specified file exist
     * @param String $path The specified path of the file
     * @param Boolean $is_createifnotexist (Optional) Boolean value if file should be created if it doesn't exist
     * @return Boolean If file exists or not
     */
    public function fileExist($path, $is_createifnotexist = false) {
        if (!$is_createifnotexist) {
            return file_exists($path);
        }

        if ($is_createifnotexist && !file_exists($path)) {
            // Opens the file
            $handle = fopen($path, 'w+');
            fwrite($handle, '');
            fclose($handle);
            return file_exists($path);
        }
    }

    public function enumVisitedPlaces() {
        echo '<ul>';
        foreach ($this->Visitedplaces as $place) {
            echo '<li>' . $place . '</li>';
        }
        echo '</ul>';
    }

    /**
     * Returns the age of this person THROUGH his `birthyear`
     * @param int $birthyear Birth year
     * @return int Computed age (whereas: current_year - birthyear)
     */
    public function getAge($birthyear) {
        $current_year = intval(date('Y'));
        return abs($current_year - $birthyear);
    }

    /**
     * Returns the full name of this person WHEN married
     * @param booean $is_makeuppercase (Optional) Boolean value if name should be returned in uppercase
     * @param boolean $is_trimspaces (Optional) Boolean value if name should have left-Sright spaces trimmed
     * @return String The new processed fullname
     */
    public function getFullnameAsMarried($is_makeuppercase = false, $is_trimspaces = false) {
        # $this->marriedTo('Konrad');
        # Assign names
        $new_firstname = $this->Firstname;
        $new_lastname = $this->Lastname;
        
        $new_firstname = $is_makeuppercase ? 
                ($is_trimspaces ? 
                    trim(strtoupper($new_firstname)) 
                  : strtoupper($new_firstname)
                ) 
              : ($is_trimspaces ? 
                    trim($new_firstname)
                  : $new_firstname
                );
        
        $new_lastname = $is_makeuppercase ? 
                ($is_trimspaces ?
                    trim(strtoupper($new_lastname)) 
                  : strtoupper($new_lastname)) 
              : ($is_trimspaces ? 
                    trim($new_lastname) 
                  : $new_lastname
                );

        return $new_firstname . ' ' . $new_lastname;
    }

    public function setVisitedPlaces($a_vplaces) {
        $this->Visitedplaces = $a_vplaces;
    }

    public function marriedTo($lastname_of_husband) {
        $this->Lastname = $lastname_of_husband;
    }

    /**
     * Sets the gender of this person
     * @param String $str_gender Gender to be specified
     */
    public function setGender($str_gender) {
        $this->Gender = $str_gender;
    }

    // Methods
    public function setName($name) {
        
    }

}
