<?php
/**
 * Renders bootstrap-oriented forms
 */

class FORM {    
    /**
     * 
     * @param String $name
     * @param String $method
     * @param String $action
     * @param String $class The class for this form's parent container
     * @param String $align [left] Alignment of the form
     * @param Array(assoc) $options [array()] Associative array of HTML attributes of this form
     */
    public function __construct($name, $method, $action, $class, $align="left", $options=array()) {
        $html = '<div class="container-fluid '.$class.'" align="'.$align.'">';
        $html .= '<form name="'.$name.'" method="'.$method.'" action="' . $action . '" class="form" role="form" ';
        if (count($options) > 0) {
            for(reset($options),$x=0; $x<count($options); $x++,next($options)) {
                $html .= key($options).'="'.current($options).'" ';
            }
            reset($options);
        }
        $html = trim($html) . '>';
        echo $html;
    }
    
    /**
     * Render an input element
     * @param String $label Label field text
     * @param String $name Input name
     * @param String $type Input type
     * @param Array $options Input's additional HTML properties
     * @param String $scheme Input scheme (Default or Required)
     * @param String $tooltip Hint text for this field
     * @param Boolean $is_fullwidth Set if this element's width should be 100%
     * @param String $input_class Input's additional CSS class
     * @param String $initial_value Input's default value
     */
    public function AddInput($label, $name, $type, $options=array(), $scheme="DEFAULT", $tooltip=null, $is_fullwidth=false, $input_class = "", $initial_value = "") {
        # <input> ID, SCHEME, <label> initializations
        $input_id = strtolower(str_replace(" ", "", $name));
        $scheme = strtoupper($scheme);
        $label_class = ($scheme==="REQUIRED" ? "label-primary" : "label-default");
        
        # <label> printing
        if (!is_null($label)) {
            echo '<label class="label ' . $label_class . '" for="'.$input_id.'">' . $label . '</label>';
        }
        if ($tooltip !== null || strlen($tooltip) > 0) {
            echo '<label class="label label-warning">'.$tooltip.'</label>';
        }
        # <input> printing
        echo '<input type="' . $type . '" name="' . $name . '" class="form-control' . (!$is_fullwidth ? '-free ':' ') . $input_class . '" id="' . $input_id . '" ';
        # -- check for existing POST data and set value if exist
            echo 'value="' . 
                    ((Index::__HasPostData($name) /* Check if password field */ 
                        && trim(strtolower($type))!='password') ? 
                            DATA::__GetPOST($name, false, true) . '" '
                          : $initial_value . '" ');
            
        # -- check for optional attributes ($options) and add each if exist
        for(reset($options),$x=0; $x<count($options); next($options),$x++) {
            echo strtolower(str_replace(" ", "", key($options))) . '="' . current($options) . '" ';
        }
        echo '><br>';
    }
    
    /**
     * Adds a dropdown HTML element
     * @param String $label
     * @param String  $name
     * @param Array[][] $choices
     * @param Array[][] $options
     * @param String $scheme
     * @param String $tooltip
     * @param Boolean $is_fullwidth
     * @param String $input_class
     * @param String $selectedvalue
     */
    public function AddDropdown($label, $name, $choices=array(), $options=array(), $scheme="DEFAULT", $tooltip=null, $is_fullwidth=false, $input_class=null, $selectedvalue=null) {
        $input_id = strtolower(str_replace(" ", "", $name));
        $scheme = strtoupper($scheme);
        $label_class = ($scheme==="REQUIRED" ? "label-primary" : "label-default");
        
        echo '<label class="label ' . $label_class . '" for="'.$input_id.'">' . $label . '</label>';
        if ($tooltip !== null || strlen($tooltip) > 0) {
            echo '<label class="label label-warning">'.$tooltip.'</label>';
        }
        echo '<select name="' . $name . '" class="form-control' . (!$is_fullwidth ? '-free ':' ') . $input_class . '" id="' . $input_id . '" ';
        // Check and apply `$options` array parameter
        if (count($options) > 0) {
            do {
                echo strtolower(str_replace(" ", "", key($options))) . '="' . current($options) . '" ';
            } while (next($options));
        }
        echo '>';
        
        // Check and apply `$choices` array parameter
        //  for this dropdown element
        $x=0;
        do {
            echo '<option value="' . current($choices) . '" '
                    . (Index::__HasPostData($name) ? 
                        ( DATA::__GetPOST($name) == current($choices) ? 'selected' : '' )
                      : ($selectedvalue == current($choices) ?
                            'selected' : ''))
                    . '>' . key($choices) . '</option>';
            $x++;
            next($choices);
        } while($x < count($choices));
        echo '</select><br>';
        
    }
    
    /**
     * Adds a simple dropdown element
     * @param String $label Label for this element
     * @param String $name FORM Name of this element
     * @param Array(assoc) $choices [array] The choices or values for this dropdown element
     * @param Array(assoc) $options [array] The HTML properties of this element
     * @param String $class [null] The class of this element
     * @param String $selectedvalue [null] Selected value of this element
     * @param boolean $is_render [true] If HTML code should be rendered, otherwise, returned
     * @param boolean $is_labelbold [true] If label text should be in bold or not
     * @return string The rendered/returned HTML code
     */
    public function AddDropdownSimple(
            $label, $name, $choices=array(), $options=array(), $class=null, $selectedvalue=null, $is_render=true, $is_labelbold=true)
    {
        // label
        $html = '<label ' . (!$is_labelbold ? 'style="font-weight: normal;"':'') . '>';
        $html .= $label . '</label>' . PHP_EOL;
        // select
        $html .= '<select name="'.$name.'" ';
        $html .= 'class="form-inline form-icontrol '.(is_null($class)?'':$class).'" ';
        for ($i=0,reset($options); $i<count($options); $i++,next($options))
        {
            $html .= key($options).'="'.current($options).'" ';
        } $html = trim($html) . '>'.PHP_EOL;
        // options
        for ($i=0,reset($choices); $i<count($choices); $i++,next($choices)) 
        {
            $html .= '<option value="'.(current($choices)).'"';
            if (!is_null($selectedvalue) && $selectedvalue==current($choices)) {
                $html = trim($html) .' selected';
            }
            $html .= '>';
            $html .= key($choices).'</option>'.PHP_EOL;
        }
        $html .= '</select>';
        if (!$is_render) {
            return $html;
        }
        echo $html;
    }
    
    /**
     * Adds a textarea element
     * @param string $label Input's label text
     * @param string $name Element's control name
     * @param string $innerhtml Inner HTML value
     * @param Array(assoc) $options HTML properties
     * @param string $scheme Can be DEFAULT or REQUIRED
     * @param string $tooltip Tooltip text
     * @param boolean $is_fullwidth If true, this will occupy the whole space
     * @param string $class
     * @param string $initial_value
     */
    public function AddTextarea($label, $name, $innerhtml=null, $options=array(), $scheme="DEFAULT", $tooltip=null, $is_fullwidth=false, $class = null) {
        $input_id = strtolower(str_replace(" ", "", $name));
        $scheme = strtoupper($scheme);
        $label_class = ($scheme==="REQUIRED" ? "label-primary" : "label-default");
        echo '<label class="label ' . $label_class . '" for="' . $input_id . '">' . $label . '</label>';
        if ($tooltip !== null || strlen($tooltip) > 0) {
            echo '<label class="label label-warning">'.$tooltip.'</label>';
        }
        echo '<br>';
        
        # <textarea> printing
        echo '<textarea name="' . $name . '" class="' . ($is_fullwidth ? 'form-textarea-fullwidth ' : 'form-textarea ') . $class . '" ';
        if (count($options) > 0) {
            do {
                echo strtolower(str_replace(" ", "", key($options))) . '="' . current($options) . '" ';
            } while (next($options));
        }
        echo '>';
        echo $innerhtml;
        echo '</textarea>';
        echo '<br>';
    }
    
    public function AddHidden($name, $value) {
        echo '<input type="hidden" name="'.$name.'" value="' . $value . '">';
    }
    
    public function AddLabel($text, $str_size='mid', $str_alignment='left') {
        echo '<c class="' . $str_size . ' ' . $str_alignment . '">'
                . $text . '</c>';
        echo '<br>';
    }
    
    public function AddText($text, $padding_val='5px 2px') {
        echo '<div style="padding: ' . $padding_val . ';">';
        echo $text;
        echo '</div>';
    }
    
    /**
     * Renders a Form Submit button
     * @param string $caption The caption for this button
     * @param boolean $return If true, rendered HTML will rather be returned
     * @return string (Optional) The rendered HTML codes
     */
    public function RenderSubmitButton($caption, $return=false) {
        $html = ' <input type="submit" value="' . $caption . '" class="btn btn-primary btn-sm"> ';
        if ($return) {
            return $html;
        }
        echo $html;
    }
    /**
     * 
     * @param string $caption The caption for this button
     * @param string $str_actionpage The page where button action will be pointed to
     * @param boolean $return If true, rendered HTML will rather be returned
     * @return string (Optional) The rendered HTML codes
     */
    public function RenderCancelButton($caption, $str_actionpage=null, $return=false) {
        $html = ' <input type="button" '
                . 'value="'  . $caption . '" '
                . 'class="btn btn-warning btn-sm"'
                . 'onclick="' . (is_null($str_actionpage) ? 'window.history.back();' : 'window.location=\''.UI::GetPageUrl(strtolower($str_actionpage)).'\';') . '"> ';
        if ($return) {
            return $html;
        }
        echo $html;
    }
    
    public function EndForm() {
        echo '</form></div>';
    }
    
    # Private functions --------------------------------------------------------
    
    private function __getAvailableValue() {
        
    }
    
}
