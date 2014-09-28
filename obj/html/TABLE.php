<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class for rendering Table elements<br><br>
 * <b>Instructions of use: </b><br>
 * <ol type="1">
 * <li>Initialize this instance</li>
 * <li>setColumnheaders, setHTMLproperties, setCSS</li>
 * <li>addRow</li>
 * </ol>
 *
 * @author Allen
 */
class TABLE {

    public $Columnheaders;
    # [0] => { CAPTION, COLUMN_TYPE, ALIGN, (...) }

    /**
     * Assoc-array of row values/data
     * @var Array(assoc)
     */
    public $Rowdata;

    /**
     * Assoc-array of HTML properties of this {TABLE} element
     * @var Array(assoc)
     */
    public $CellsHTMLtemplate;
    
    /**
     * The message to be displayed when no Row data is defined
     * @var String
     */
    public $EmptyMessage;
    public $HTMLcss;
    public $HTMLproperties;

    /**
     * Create new instance of this object.
     */
    public function __construct() {
        $this->Columnheaders = array();
        $this->EmptyMessage = null;
        $this->Rowdata = array();
        $this->HTMLcss = array();
        $this->HTMLproperties = array();
    }

    /**
     * Adds new row data to the table.
     * @param Array $a_rowdata Array of data within this row
     * @return TABLE New instance
     */
    public function addRow($a_rowdata) {
        if (count($a_rowdata) > count($this->Columnheaders)) {
            ERROR::PromptError('Column count of "row_data" (' . count($a_rowdata) . ') exceeded the count of column headers (' . count($this->Columnheaders) . ').', 'TABLE.php', 48);
        } else {
            array_push($this->Rowdata, $a_rowdata);
        }
        return $this;
    }

    /**
     * Adds a group of cell to endpoint
     * @param type $a_rowdata
     * @param Boolean $is_trailingcell (Optional) Boolean value if these/this cell(s)
     *      should be added to the right trailing part of each row, otherwise, will be placed
     *      at left trailing parts.
     * @return TABLE|boolean
     */
    public function addCommonCells($a_rowdata, $is_trailingcell = true) {
        if (count($this->Rowdata) == 0) {
            ERROR::PromptError('<b>addCommonCells():</b> No existing row data yet!', 'TABLE.php');
            return false;
        }
        // Proper start here
        for ($x = 0; $x < count($this->Rowdata); $x++) {
            if ($is_trailingcell) {
                foreach ($a_rowdata as $rowcell) {
                    array_push($this->Rowdata[$x], $rowcell);
                }
            } else {
                $new_rowdata = $a_rowdata;
                foreach ($this->Rowdata[$x] as $rowdata_cell) {
                    array_push($new_rowdata, $rowdata_cell);
                }
                $this->Rowdata[$x] = $new_rowdata;
            }
        }
        return $this;
    }
    
    /**
     * Sets the message to be displayed when no there is no row data
     * @param String $str_emptymsg
     */
    public function defineEmptyMessage($str_emptymsg) {
        $this->EmptyMessage = $str_emptymsg;
    }

    /**
     * Render this whole table
     * @param Boolean $is_headersvisible (Optional) Boolean value if column headers should be rendered
     * @param Boolean $is_boldheaders (Optional) Boolean value if headers should be BOLDED
     */
    public function Render($is_headersvisible=true, $is_boldheaders = true) {
        # ECHO <table (...properties...) 
        // Rendering <table>-properties
        $str_htmlprops = '';
        if (count($this->HTMLproperties) > 0) {
            do {
                if (strtoupper(key($this->HTMLproperties)) != 'STYLE') {
                    $str_htmlprops .= key($this->HTMLproperties) . '="' . current($this->HTMLproperties) . '" ';
                }
            } while (next($this->HTMLproperties));
        }
        $str_htmlprops = trim($str_htmlprops);
        echo '<table ' . $str_htmlprops;
        // Rendering <table>-styles
        $str_htmlcss = '';
        if (count($this->HTMLcss) > 0) {
            $str_htmlcss .= ' style="';
            do {
                $str_htmlcss .= str_replace(' ', '-', strtolower(key($this->HTMLcss))) . ':' . current($this->HTMLcss) . '; ';
            } while (next($this->HTMLcss));
            $str_htmlcss = strtolower(trim($str_htmlcss));
            $str_htmlcss .= '"';
            echo $str_htmlcss;
        }
        echo '>';

        if ($is_headersvisible) {
            $this->renderHeaders($is_boldheaders);
        }
        $this->renderBody();

        echo '</table>';
    }

    public function renderBody() {
        echo '<tbody>' . PHP_EOL;
        # ECHO <tr><td> { Row data } </td></tr>
        if (count($this->Rowdata) <= 0) {
            echo '<tr>';
            echo '<td align="center" colspan="'. count($this->__getVisibleColumns()) . '">';
            echo '<br>';
            echo '<br>';
            echo $this->EmptyMessage;
            echo '<br>';
            echo '<br>';
            echo '</td>';
            echo '</tr>';
        } else {
            foreach ($this->Rowdata as $row) {
                echo '<tr>';
                $domin_max = 0; 
                if (count($this->Columnheaders) > count($row)) {
                    $domin_max = count($this->Columnheaders);
                } else {
                    $domin_max = count($row);
                }

                if (count($this->CellsHTMLtemplate) >= $domin_max) { // {BEGIN IF}
                    # If HTML cell templates count is greater than or equal to
                    #       the number of cells for this row?

                    # Looping CELLs per ROW, and also with respect to COLUMN PROPERTIES                
                    for ($x_row=0,$x_colheader=0,$x_domin=0; 
                            $x_domin < $domin_max; 
                            $x_row++, $x_colheader++, $x_domin++) {
                        $cellvalue = $x_row < count($row) ? $row[$x_row] : '';

                        // ALL EXEMPTIONS FALL HERE
                        # ---- Hide this cell under a HIDDEN COLUMN
                        if (array_key_exists('HIDDEN', $this->Columnheaders[$x_colheader])) {
                            if ($this->Columnheaders[$x_row]['HIDDEN']) {
                                continue;
                            }
                        }
                        # ---- If `current column` has DEFAULT VALUE, let it be the cell value
                        if (array_key_exists('DEFAULT', $this->Columnheaders[$x_colheader])) {
                            $cellvalue = $this->Columnheaders[$x_colheader]['DEFAULT'];
                            $x_row--; // Don't let the row_counter move
                        }
                        # ---- If LIMIT is defined, take it
                        if (array_key_exists('LIMIT', $this->Columnheaders[$x_colheader])) {
                            $cellvalue = STR::Limit($cellvalue, $this->Columnheaders[$x_colheader]['LIMIT']);
                        }

                        // Preparing <td> HTML options
                        $td_options = '';
                        if (count($this->CellsHTMLtemplate) > 0) {
                            do {
                                $td_options .= strtolower(trim(key($this->CellsHTMLtemplate[$x_row])))
                                        . '="' . trim(current($this->CellsHTMLtemplate[$x_row])) . '" ';
                            } while (next($this->CellsHTMLtemplate[$x_row]));
                            reset($this->CellsHTMLtemplate[$x_row]);
                        }
                        $td_options = trim($td_options);

                        // Rendering current cell
                        echo '<td ' . $td_options . '>';
                        // ---- Process all parameter within the cell value
                        $cellvalue = $this->__processCellvalue($cellvalue, $row, $x_colheader, true);
                        echo $cellvalue; // <<--- Cell value here
                        echo '</td>';
                    }
                } else {
                    # If HTML Cell templates is less than the number of cells for this row
                    ERROR::PromptError('Error at rendering Table body when number of cells for this '
                            . 'row|header(whatever\'s max) is greater than the number of Cell templates!' . count($this->CellsHTMLtemplate)
                            . count($row), 'TABLE.php', 167);
                } # { END IF }
                echo '</tr>';
            }
        }
        echo '</tbody>';
    }

    public function renderHeaders($is_bold = true) {
        # ECHO <tr><td> { Column Headers } </td></tr>
        if (count($this->Columnheaders) > 0) {
            echo '<thead><tr>';
            foreach ($this->Columnheaders as $columnHeader) {
                $td_properties = '';
                echo '<td ';
                # This about generating Header properties
                if (count($columnHeader) > 0) {
                    do {
                        $key = strtoupper(key($columnHeader));
                        if ( $key == 'CAPTION' 
                                || ($key=='HIDDEN' && !current($columnHeader))
                                || ($key=='DEFAULT')
                                || ($key=='LIMIT')
                                || ($key=='LISTOFVALUES')) {
                            continue;
                        }
                        $td_properties .= key($columnHeader) . '="' . current($columnHeader) . '" ';
                    } while (next($columnHeader));
                }
                echo strtolower(trim($td_properties)) . '>';
                echo array_key_exists('CAPTION', $columnHeader) ?
                        ($is_bold ? '<b>' : '') . $columnHeader['CAPTION'] . ($is_bold ? '</b>' : '') : '';
                echo '</td>';
            }
            echo '</tr></thead>';
        }
    }

    /**
     * Set the Column headers of this table
     * @param Array(Indexed-assoc) $a_headers Indexed Assoc-array of column headers of this table
     *  with format:<br>
     * { <br>
     * <b>"CAPTION" => "Caption1"</b><br>
     * <b>"HIDDEN" => true|false</b><br>
     * <b>"DEFAULT" => "Default value here with {1} parameters</b><br>
     * <b>"LIMIT" => 15</b><br>
     * "ALIGN" => "left"<br>
     * }
     * @return TABLE New instance
     */
    public function setColumnHeaders($a_headers) {
        $this->Columnheaders = $a_headers;
        return $this;
    }

    /**
     * Set the CSS styles of this {TABLE}
     * @param Array(assoc) $a_cssprops Array(assoc) of CSS styles/properties to be applied
     * @return TABLE
     */
    public function setCSS($a_cssprops) {
        $this->HTMLcss = $a_cssprops;
        return $this;
    }

    /**
     * 
     * @param type $a_cellstemplate
     * @return \TABLE
     */
    public function setCellstemplate($a_cellstemplate) {
        $this->CellsHTMLtemplate = $a_cellstemplate;
        return $this;
    }

    /**
     * Set the HTML properties of this {table}
     * @param Array $a_htmlprops HTML {table} element properties
     * @return TABLE
     */
    public function setHTMLproperties($a_htmlprops) {
        $this->HTMLproperties = $a_htmlprops;
        return $this;
    }

    # PRIVATE FUNCTIONS --------------------------------------------------------

    /**
     * Gets parameter from CELL VALUES and return it as array<br>
     * <b>Example:</b> Your name is {1} and your age is {2}<br>
     * <b>Return value:</b> array( 1, 2 )
     * @param String $cellvalue The cell value to be inspected
     * @return Array
     */
    private function __getParamsFromValue($cellvalue) {
        $a_result = array();
        if (strstr($cellvalue, '{') && strstr($cellvalue, '}')) {
            $extractmode = false;
            $str_integer = '';
            for ($i = 0; $i < strlen($cellvalue); $i++) {
                $char = $cellvalue[$i];
                if ($char == '{') { // Begin Extraction
                    $extractmode = true;
                    continue;
                } else if ($char == '}') { // End Extraction, validate value
                    $extractmode = false;
                    if (intval($str_integer) != 0) {
                        array_push($a_result, intval($str_integer));
                    }
                    $str_integer = '';
                    continue;
                }
                if ($extractmode) { // Extraction proper
                    $str_integer .= $char;
                }
            }
        }
        return $a_result;
    }
    
    /**
     * Return an array of visible Column header data (no 'HIDDEN' in properties)
     * @return Array(assoc)
     */
    private function __getVisibleColumns() {
        $result = array();
        foreach($this->Columnheaders as $colheader) {
            if (!(array_key_exists('HIDDEN', $colheader) || array_key_exists('hidden', $colheader))) {
                array_push($result, $colheader);
            }
        }
        return $result;
    }
    
    /**
     * 
     * @access private
     * @param String $cellvalue
     * @param type $row
     * @param type $x_colheader
     * @param type $is_reiterate
     * @return type
     */
    private function __processCellvalue($cellvalue, $row, $x_colheader, $is_reiterate=false) {
        $a_params = $this->__getParamsFromValue($cellvalue);
        foreach ($a_params as $parameter) {# ---- If value substitution exists (LISTOFVALUES)
            $param_Substitution = $row[intval($parameter) - 1];
            // if 'LISTOFVALUES' is defined in Columnheaders
            if (array_key_exists('LISTOFVALUES', $this->Columnheaders[$x_colheader]) && $is_reiterate) {
                $LOV = $this->Columnheaders[$x_colheader]['LISTOFVALUES'];
                // if level-1 substituted value exists in LISTOFVALUES, resubstitute
                if (array_key_exists($row[intval($parameter) - 1], $LOV)) {
                    $param_Substitution = $LOV[$row[intval($parameter) - 1]];
                    $cellvalue = str_replace('{' . $parameter . '}', $param_Substitution, $cellvalue);
                    if ($is_reiterate) {
                        $cellvalue = $this->__processCellvalue($cellvalue, $row, $x_colheader);
                    }
                }
            }
            $cellvalue = str_replace('{' . $parameter . '}', $row[intval($parameter) - 1], $cellvalue);
        }
        return $cellvalue;
    }

}
