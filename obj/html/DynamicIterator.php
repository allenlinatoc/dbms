<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DynamicIterator
 *
 * @author Allen
 */
class DynamicIterator {
    
    public $DbResult;
    /**
     * Properties: IF_COLUMN, IF_VALUE, HTML
     * @var Array(assoc)
     */
    public $Templates;
    public $EmptyHtml;
    

    public function __construct()
    {
        $this->DbResult = array();
        $this->Templates = array();
        $this->EmptyHtml = '';
    }
    
    /**
     * 
     * @param String $if_column
     * @param String $if_value
     * @param String $html
     * @return \DynamicIterator
     */
    public function addTemplate($if_column, $if_value, $html)
    {
        $array = array();
        $conditions = array();
        $array['IF_COLUMN'] = $if_column;
        $array['IF_VALUE'] = $if_value;
        $array['HTML'] = $html;
        array_push($this->Templates, $array);
        return $this;
    }
    
    public function defineEmptyHtml($html)
    {
        $this->EmptyHtml = $html;
    }
    
    public function removeTemplate($columnName, $value) {
        $new_Templates = array();
        foreach($this->Templates as $template)
        {
            if ($template['IF_COLUMN']!=$columnName && $template['IF_VALUE']!=$value) {
                array_push($new_Templates, $template);
            }
        }
        $this->setTemplates($new_Templates);
    }
    
    public function render($is_return=false, $separator='')
    {
        $html = '';
        if (count($this->DbResult) == 0) {
            echo $this->EmptyHtml;
        }
        foreach ($this->DbResult as $row)
        {
            $htmlElem = $this->__getTemplate($row);
            
            // iterating through all columns of this row
            for ($x=0; $x<count($row); $x++,next($row))
            {
                // replacing all replaceable string parameters
                $htmlElem = str_replace('{'.key($row).'}', current($row), $htmlElem);
            }
            reset($row);
            $html .= $htmlElem . $separator;
        }
        if ($is_return) return $html;
        echo $html;
    }
    
    public function setDbResult($dbresult) {
        $this->DbResult = $dbresult;
    }
    
    public function setTemplates($template) {
        $this->Templates = $template;
    }
    
    
    private function __getTemplate($dbrow)
    {
        $html = null;
        foreach($this->Templates as $template) {
            if ($dbrow[$template['IF_COLUMN']]==$template['VALUE']) {
                return $template['HTML'];
            }
            else if ($template['IF_COLUMN']=='DEFAULT' && $template['IF_VALUE']=='DEFAULT') {
                $html = $template['HTML'];
            }
        }
        return $html;
    }
    
}
