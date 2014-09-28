<?php

/**
 * Class for application interface layer managements<br>
 * This includes:<br>
 * <ul>
 * <li>Page redirection</li>
 * <li>Page URL generator</li>
 * </ul><br>
 * <br>
 * HTML rendering features are:<br>
 * <ul>
 * <li>Break lines</li>
 * <li>Buttons</li>
 * <li>Horizontal lines</li>
 * <li>Lists</li>
 * </ul>
 */
class UI {
    /**
     * Generates URL of a page
     * @param String $page
     * @param Array $addons [array()] GET/JSON parameter data to be passed on
     * @param String $anchor [null] The anchor name without '#' symbol
     * @return String The generated URL of the specified page
     */
    public static function GetPageUrl($page, $addons=array(), $anchor=null) {
        $return_url = "";
        if (Index::__HasPage($page)) {
            $return_url = '?page=' . str_replace(" ", "-", $page);
        } else {
            $return_url = '?page=404';
            $addons = array(
                'target' => $page,
                'ref' => Index::__GetPage()
            );
        }
        if (count($addons)>0) {
            do {
                $return_url .= '&' . trim(key($addons)) . '=' . trim(current($addons));
            } while(next($addons));
        }
        $return_url .= (!is_null($anchor) ? '#'.$anchor : '');
        return $return_url;
    }
    
    public static function makeNavigationLink($text, $url, $str_disable_onpage='', $starttag='<b>', $endtag='</b>') {
        echo PHP_EOL;
        echo strtolower(Index::__GetPage())!=strtolower($str_disable_onpage) ?
            '<a href="' . $url . '">'
          : $starttag;
        echo $text;
        echo strtoupper(Index::__GetPage())!=strtoupper($str_disable_onpage) ?
            '</a>'
          : $endtag;
    }

    /**
     * Redirects the user to certain page in this site
     * @param String $pagename The name of the page where the user will be redirected
     * @param Array(Assoc) Assoc-array containing additional GET values in redirection URL
     */
    public static function RedirectTo($pagename, $addons = array(), $is_die=true) {
        $redir_string = 'location:?page=' . str_replace(' ', '-', trim(strtolower($pagename)));
        if (count($addons) > 0) {
            do {
                $redir_string .= '&' . str_replace(' ', '', (trim(key($addons)))) . '=' . urlencode(current($addons));
            } while (next($addons));
        }
        header($redir_string);
        die();
    }
    
    /**
     * Refreshes the whole page
     */
    public static function RefreshPage() {
        self::RedirectTo(Index::__GetPage());
        die();
    }

    
    
    
    # HTML UI Methods ----------------------------------------------------------

    /**
     * Render an HTML Button
     * @param String $str_caption Button caption
     * @param String $str_type Type of button
     * @param String $class CSS class properties for this button
     * @param String $str_clickhref The target link for this button
     * @param boolean $is_render (Optional) Boolean value if HTML code should be rendered or returned
     * @param String $str_disable_on_page The current URL through UI::GetPageUrl() 
     * @return String The rendered input button as HTML String code
     */
    public static function Button($str_caption, $str_type="button", $class=null, $str_clickhref=null, $is_render=true, $str_disable_on_page=null) {
        $strStream = '<input type="' . $str_type . '" value="' . $str_caption . '"';
        if (is_string($class)) {
            $strStream .= ' class="' . $class . '"';
        }
        if (is_string($str_clickhref)) {
            $strStream .= ' onclick="window.location=\'' . $str_clickhref . '\'"';
        }
        if (is_string($str_disable_on_page) && strtolower($str_disable_on_page)==strtolower(Index::__GetPage())) {
            $strStream .= ' disabled';
        }
        $strStream = trim($strStream) . '>' . PHP_EOL;
        if ($is_render) {
            echo $strStream;
            return $strStream;
        } else {
            return $strStream;
        }
    }
    
    /**
     * Creates LIST element
     * @param Array $a_items Array of list values
     * @param Boolean $is_orderedlist (Optional) Boolean value if list should
     *      be ordered or not
     * @param String $str_type (Optional) If $is_orderedlist, then specify the
     *      type of this list
     */
    public static function CreateList($a_items, $is_orderedlist = false, $str_type = '1') {
        echo ($is_orderedlist ? '<ol>' : '<ul type="' . $str_type . '">') . PHP_EOL;
        foreach ($a_items as $item) {
            echo '<li>' . $item . '</li>' . PHP_EOL;
        }
        echo ($is_orderedlist ? '</ol>' : '</ul>') . PHP_EOL;
    }
    
    /**
     * Create DIV element
     * @param Array (Optional) $options HTML properties
     * @param String (Optional) $innerhtml Inner HTML content of this element
     * @param Boolean (Optional) $return Boolean value if this is returnable and not printable
     * @return string The HTML string
     */
    public static function Divbox($options=array(), $innerhtml=null, $return=false) {
        $html = '<div ';
        do {
            $html .= key($options) . '="' . current($options) . '" ';
        } while(next($options));
        $html = trim($html) . '>';
        if (!is_null($innerhtml)) {
            $html .= $innerhtml;
        }
        $html .= '</div>' . PHP_EOL;
        if ($return) {
            return $html;
        }
        echo $html;
    }

    /**
     * Prints an HTML horizontal line (hr) element
     */
    public static function HorizontalLine() {
        echo '<hr>';
    }
    
    /**
     * Create an HTML element
     * @param string $tagname Tag name of this HTML element
     * @param Array(assoc) $a_properties [array()] Assoc-array containing all properties
     *      of this HTML element
     * @param string $str_innerhtml [null] The inner html element of this HTML element
     * @param string $is_render [true] If this will be rendered, otherwise, will be returned as <b>STRING</b>
     * @return string If <b>!$is_render</b>, this is the string containing the rendered HTML codes
     */
    public static function HTML($tagname, $a_properties=array(), $str_innerhtml=null, $is_render=true) {
        $html = '<' . $tagname;
        for( $x=0; $x<count($a_properties); $x++, next($a_properties) ) {
            $html .= ' ' . key($a_properties) . '="' . current($a_properties) . '"';
        }
        $html .= '>' . PHP_EOL;
        $html .= $str_innerhtml . PHP_EOL;
        $html .= '</' . $tagname . '>' . PHP_EOL;
        if (!$is_render) {
            return $html;
        }
        echo $html;
    }
    
    public static function makeLink($url, $caption, $return=false) {
        $html = '<a href="'.$url.'">'.$caption.'</a>';
        if ($return) {
            return $html;
        }
        echo $html;
    }

    /**
     * HTML breakline element
     * @param int $count (Optional=1) Number of breaklines to be rendered
     * @param boolean $return (Optional=false) Boolean value if HTML code should be returned
     * @return string
     */
    public static function NewLine($count=1, $return=false) {
        $html = '';
        for($x=0; $x<$count; $x++) {
            $html .= '<br>' . PHP_EOL;
        }
        if ($return) {
            return $html;
        }
        echo $html;
    }

}

?>