<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class for generating reports from MySQL database
 * @author Allen
 */
class MySQLReport {
    # Properties ---------------------------------------------------------------
    /**
     * Array containing common cells' values<br>
     * <b>Array-parameters</b>:<br>
     * CELLS, IS_TRAILING
     * @var Array
     */
    public $CommonCells;
    
    /**
     * The message to be displayed when no Row data is defined
     * @var String
     */
    public $EmptyMessage;
    
    /**
     * The DB object which was used in generating this report data
     * @var DB
     */
    public $DbObject;
    
    /**
     * Data containing query results from DB object
     * @var Array(assoc)
     */
    public $Queryresultdata;

    /**
     * Row data for Table data rendering inputs
     * @var Array(2-dime) 
     */
    public $Rowdata;

    /**
     * {TABLE} CSS properties of this report
     * @var Array(assoc)
     */
    public $ReportCSSproperties;

    /**
     * {TD} HTML template properties for corresponding column fields
     * @var Array(assoc)
     */
    public $ReportCellstemplate;

    /**
     * Array of Column headers of this report
     * @var Array(assoc)
     */
    public $Reportheaders;

    /**
     * {TABLE} HTML properties of this report
     * @var Array(assoc)
     */
    public $Reportproperties;

    /**
     * Child TABLE object for rendering reports
     * @var TABLE
     */
    public $TABLE;

    # Methods ------------------------------------------------------------------

    public function __construct(array $a_headers = array()) {
        $this->CommonCells = array();
        $this->EmptyMessage = null;
        $this->Reportheaders = $a_headers;
        $this->Reportproperties = array();
        $this->DbObject = null;
        $this->Queryresultdata = array();
        $this->Rowdata = array();
        $this->TABLE = null;
    }

    public function __columnCount() {
        // Put a procedure here that will return column count of `Resultdata`
    }
    
    /**
     * 
     * @param Array $a_rowdata Array of common cell values
     * @param Boolean $is_trailing Boolean value if these cell values should be
     *  trailing.
     * @return \MySQLReport
     */
    public function addCommonCells($a_rowdata, $is_trailing) {
        array_push($this->CommonCells, array(
            'CELLS' => $a_rowdata,
            'IS_TRAILING' => $is_trailing
        ));
        return $this;
    }
    
    /**
     * Sets the message to be displayed when no there is no row data
     * @param String $str_emptymsg
     */
    public function defineEmptyMessage($str_emptymsg) {
        $this->EmptyMessage = $str_emptymsg;
    }

    public function loadDb(DB $db) {
        $this->DB = $db;
        return $this;
    }

    public function loadResultdata($query_result_data, $is_autoheader = false) {
        $this->Queryresultdata = $query_result_data;
        if (count($this->Queryresultdata) > 0 && $is_autoheader) {
            $reportKeys = array_keys($this->Queryresultdata[0]);
            $new_columnHeaders = array();
            foreach ($reportKeys as $key) {
                array_push($new_columnHeaders, array(
                    'CAPTION' => $key
                ));
            }
            $this->Reportheaders = $new_columnHeaders;
        }
        // Process Query result into ROW DATA
        if (count($this->Queryresultdata) > 0) {
            $reportKeys = array_keys($this->Queryresultdata[0]);

            # Truncate Rowdata
            $this->Rowdata = array();

            foreach ($this->Queryresultdata as $resultrow) {
                # Initialize a container for processed row data
                $row_to_push = array();
                # Prepare the counter
                $ctr_column = 0;

                foreach ($reportKeys as $key) {
                    # Determine what kind of Column
                    $cell_value = $resultrow[$key];
                    if (key_exists('C_TYPE', $this->Reportheaders[$ctr_column])) {
                        if (strtoupper($this->Reportheaders[$ctr_column]['C_TYPE']) == 'CHECKBOX') {
                            $cell_value = '<input type="checkbox" name="' . $this->Reportheaders[$ctr_column]['C_OBJNAME'] . '"'
                                    . ' value="' . $cell_value . '">';
                        }
                    }
                    # -- Adding cell to 'row_to_push'
                    array_push($row_to_push, $cell_value);
                    $ctr_column++;
                }
                array_push($this->Rowdata, $row_to_push);
            }
        }
        // END of processing Query result into ROW DATA

        return $this;
    }
    
    /**
     * Orders this report by certain column (Ascendin or Descending)<br>
     * <b>NOTES: </b>
     * <ul>
     * <li>This requires that you define the 'DbObject'</li>
     * <li>Should be called before rendering the report</li>
     * </ul>
     * 
     * @param String $sql_columnName
     * @param String $str_sortmode (Optional = 'ASC') Possible values are 'ASC' or 'DESC'
     * @return MySQLReport
     */
    public function OrderBy($sql_columnName, $str_sortmode = 'ASC') {
        $str_sortmode = strtoupper($str_sortmode);
        $this->DbObject->Where('ORDER BY `' . $sql_columnName . '` ' . $str_sortmode);
        $result = $this->DbObject->Query();
        $this->loadResultdata($result);
        return $this;
    }

    /**
     * Render the report
     * @param Boolean $is_headersvisible (Optional) Boolean value if column headers should be rendered
     * @param Boolean $is_boldheader (Optional) Boolean value if column headers should be in BOLD letters
     * @param Array $a_tableoptions (Optional) Array of TABLE HTML options/properties
     */
    public function renderReport($is_headersvisible=true, $is_boldheader = true, $a_tableoptions = array()) {
        $this->TABLE = new TABLE();
        if (!is_null($this->EmptyMessage)) {
            $this->TABLE->defineEmptyMessage($this->EmptyMessage);
        }
        # Initializing TABLE report properties
        $this->TABLE->setColumnHeaders($this->Reportheaders)
                ->setHTMLproperties($this->Reportproperties)
                ->setCellstemplate($this->ReportCellstemplate)
                ->setCSS($this->ReportCSSproperties);

        foreach ($this->Rowdata as $row) {
            $this->TABLE->addRow($row);
        }
        if (count($this->CommonCells) > 0) {
            foreach($this->CommonCells as $commonCell) {
                $this->TABLE->addCommonCells($commonCell['CELLS'], $commonCell['IS_TRAILING']);
            }
        }

        $this->TABLE->Render($is_headersvisible, $is_boldheader);
    }
    
    /**
     * Sets the DB (DB.php) object which was used to generate this report
     * @param DB $DBobject
     */
    public function setDBobject(DB $DBobject) {
        $this->DbObject = $DBobject;
        return $this;
    }

    public function setReportCSS($a_cssproperties) {
        $this->ReportCSSproperties = $a_cssproperties;
        return $this;
    }

    public function setReportCellstemplate($a_cellstemplate) {
        $this->ReportCellstemplate = $a_cellstemplate;
        return $this;
    }

    public function setReportProperties($a_htmlproperties) {
        $this->Reportproperties = $a_htmlproperties;
        return $this;
    }

    public function setReportHeaders($a_headers) {
        $this->Reportheaders = $a_headers;
        return $this;
    }
    
}
