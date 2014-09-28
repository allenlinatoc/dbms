<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class for iterating each row of DBRESULT into repeatable HTML contents
 * @author Allen
 */
class ITERATOR {
    
    public 
            $DbResult,
            $Template
    ;
    
    public function __construct($dbresult = array()) {
        $this->DbResult = $dbresult;
    }
    
    public function loadDbresult($dbresult)
    {
        $this->DbResult = $dbresult;
    }
    
    public function setTemplate($str_formattedhtml) 
    {
        $this->Template = $str_formattedhtml;
    }
    
    /**
     * Renders the content of this iteration instance
     * @param boolean $is_return
     * @param String $separator
     * @return string
     */
    public function render($is_return=false, $separator='')
    {
        $html = '';
        for ($i=0; $i<count($this->DbResult); $i++) 
        {
            $row = $this->DbResult[$i];
            $htmlElem = $this->Template;
            // iterating through all columns of this row
            for ($x=0; $x<count($row); $x++,next($row))
            {
                // replacing all replaceable string parameters
                $htmlElem = str_replace('{'.key($row).'}', current($row), $htmlElem);
            }
            reset($row);
            $html .= $htmlElem . $separator;
        }
        if ($is_return) {
            return $html;
        }
        echo $html;
    }
    
}
