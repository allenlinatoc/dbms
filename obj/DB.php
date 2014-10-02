<?php

/**
 * Static class for MySQLI implementations
 */
class DB {

    public $query = "";
    public $Lasterror = null;
    public $Lasterrno = null;
    public $Lastprocess = "";
    public $Lastresult = array();
    
    const ORDERBY_ASCENDING = 1;
    const ORDERBY_DESCENDING = 2;

    /**
     *
     * @var mysqli
     */
    public $mysqli;
    public $rows_affected = null;

    /**
     * Connects to MySQL via PDO
     * @param String $supplementalpath (Optional) The parent path where this PHP class was called, include /.
     * @return mysqli
     */
    public function Connect($supplementalpath = '') {
        $config = parse_ini_file($supplementalpath . 'config/database.ini');
        $this->mysqli = new mysqli($config['host'], $config['user'], $config['password'], $config['db'], $config['port']);
        return $this->mysqli;
    }

    /**
     * Returns the PDO driver data in associative array form
     * @return Array(Assoc)
     */
    public static function __GetDriverInfo() {
        return pdo_drivers();
    }

    /**
     * Gets the number of rows processed after the last executed query.
     * @return int
     */
    public function __GetAffectedRows() {
        return $this->mysqli->affected_rows;
    }

    /**
     * Gets the current query of this instance
     * @return String The current query in this instance.
     */
    public function __GetQuery() {
        return $this->query;
    }
    
    public  function __IsSuccess() {
        return is_null($this->Lasterror);
    }

    /**
     * Checks for existence of certain condition in MySQL
     * @param String $table Name of the table
     * @param Array(Assoc) $a_fields_values Assoc-array containing fields and values to be checked
     * @return boolean
     */
    public function __checkPositive($table, $a_fields_values) {
        $built_condition = '';
        if (count($a_fields_values) > 0) {
            $x = 0;
            do {
                $built_condition .= '`' . key($a_fields_values)
                        . '`="' . current($a_fields_values) . '"'
                        . ($x < count($a_fields_values) - 1 ? ' AND ' : '');
                $x++;
            } while (next($a_fields_values));
        }
        $result = $this->Select()
                ->From($table)
                ->Where($built_condition)
                ->Query();
        return count($result) > 0;
    }

    /**
     * Cleans the query from basic injections
     * @return String
     */
    public function __cleanQuery() {
        # Trims whitespaces on start and end portion of query
        $this->query = trim($this->query);

        /** SQL query escaping will soon be fixed
         * 
         * # Escapes the SQL query
         * $this->query = \mysql_real_escape_string($this->query);
         */
        return $this->query;
    }

    /**
     * 
     */
    public function __construct() {
        $this->query = "";
        $this->Lasterrno = null;
        $this->Lasterror = null;
        $this->Lastprocess = null;
        $this->Lastresult = array();
        $this->rows_affected = null;
        $this->Connect();
    }

    /**
     * Cleans current query from pre-spacing and ensures that the ending query has 
     * space at the end for future query concatenation
     */
    public function __trailspaceQuery() {
        $this->query = trim($this->query);
        $this->query .= ' ';
    }

    /**
     * DELETE syntax with specific target table
     * @param String $tablename The target table name
     * @return \DB
     */
    public function DeleteFrom($tablename) {
        $this->query = "DELETE FROM " . $tablename . ' ';
        $this->__trailspaceQuery();
        return $this;
    }

    /**
     * Execute the constructed query
     * @return \DB
     */
    public function Execute($query = null, $is_debugmode=false) {
        if (!is_null($query)) {
            $this->query = $query;
        }
        $this->__cleanQuery();
        if ( $is_debugmode )
        {
            echo 'Executing '.$this->query.'<br>';
        }
        if (!$this->mysqli->query($this->query)) 
        {
            $this->Lasterror = $this->mysqli->error;
            $this->Lasterrno = $this->mysqli->errno;
        } 
        else {
            $this->Lasterror = null;
            $this->Lasterrno = null;
        }
        $this->rows_affected = $this->__GetAffectedRows();
        return $this;
    }

    /**
     * From what table will the query be executed
     * @param String $tablename The table namme
     * @return \DB
     */
    public function From($tablename) {
        $this->query .= 'FROM ' . $tablename . ' ';
        $this->__trailspaceQuery();
        return $this;
    }
    
    /**
     * 
     * @param String $tablename Name of the target table
     * @param String $str_conditions The string containing the condition
     * @return Array|null DBresult of the queried specifications
     */
    public function GetRow($tablename, $str_conditions) {
        $this->
                Select()->
                From($tablename)->
                Where($str_conditions);
        $result = $this->Query();
        
        return      (count($result) > 0 ? $result[0] : null);
    }
    
    /**
     * INNER JOIN with another table
     * @param string $tablename Name of the target table
     * @return \DB
     */
    public function InnerJoin($tablename) {
        $tablename = strtolower($tablename);
        $this->query .= 'INNER JOIN `' . $tablename . '`';
        $this->__trailspaceQuery();
        return $this;
    }

    /**
     * Insert function
     * @param String $tablename The name of table
     * @param String $a_fields The table fields to be specified
     * @return \DB
     */
    public function InsertInto($tablename, $a_fields = array()) {
        $tablename = strtolower($tablename);
        $this->query = 'INSERT INTO ' . $tablename . (count($a_fields) > 0 ? '(' : ' ');

        for ($x = 0; $x < count($a_fields); $x++) {
            $this->query .= $a_fields[$x] . ($x < count($a_fields) - 1 ? ',' : ') ');
        }
        $this->__trailspaceQuery();
        return $this;
    }
    
    /**
     * Limits the returned rows
     * @param String $limit
     * @return \DB
     */
    public function Limit($limit) {
        $this->query .= 'LIMIT '.$limit;
        $this->__trailspaceQuery();
        return $this;
    }
    
    /**
     * ON certain condition
     * @param String $str_condition String containing conditional comparison
     * @return \DB
     */
    public function On($str_condition) {
        $this->query .= 'ON ' . $str_condition;
        $this->__trailspaceQuery();
        return $this;
    }
    
    /**
     * Sorts the result by 
     * @param type $str_ColumnName
     * @param type $sortmode
     * @return \DB
     */
    public function OrderBy($str_ColumnName, $sortmode) {
        $this->query .= 'ORDER BY '
                . $str_ColumnName . ' '
                . ($sortmode==self::ORDERBY_ASCENDING ? 'ASC' : 'DESC');
        $this->__trailspaceQuery();
        return $this;
    }

    /**
     * Runs the current query or (optional) a specified query.
     * @param $query (Optional) String of query to be run in replace.
     * @return Array(Assoc) The assoc-array containing the returned rows
     */
    public function Query($query = null, $is_debugmode=false) {
        $result_object = array();
        if (!is_null($query)) {
            $this->query = $query;
        }
        
        if ( $is_debugmode )
        {
            echo 'Querying <i>'.$this->query.'</i>';
        }
        $result = $this->mysqli->query($this->query);
        if ($result) {
            # SUCCESS : Process the result to an array stack
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($result_object, $row);
            }
            $this->Lastresult = $result_object;
            $this->Lasterror = null;
            $this->Lasterrno = null;
        } else {
            # FAILURE : Log the last error
            $this->Lasterror = $this->mysqli->error;
            $this->Lasterrno = $this->mysqli->errno;
        }
        return $result_object;
    }

    /**
     * Select a series (optional) of table fields
     * @param Array $a_fields Array of fields to be selected
     * @return \DB
     */
    public function Select($a_fields = array()) {
        # $tablename = strtolower($tablename);
        $this->query = 'SELECT ' . (count($a_fields) == 0 ? '* ' : '');

        for ($x = 0; $x < count($a_fields); $x++) {
            $this->query .= $a_fields[$x] . ($x < count($a_fields) - 1 ? ',' : ' ');
        }
        $this->__trailspaceQuery();
        return $this;
    }

    /**
     * Sets assignments to column fields, specifically for UPDATE command
     * @param Array(Assoc) $a_assignments Assoc-array of column field values assignment
     * @return \DB
     */
    public function Set($a_assignments) {
        $this->query .= 'SET ';
        $x = 0;
        do {
            $this->query .= key($a_assignments) . ' = ' . current($a_assignments) . ($x < count($a_assignments)-1 ? ',' : ' ' );
            $x++;
        } while (next($a_assignments));
        $this->query .= ' ';
        $this->__trailspaceQuery();
        return $this;
    }

    /**
     * UPDATE table function
     * @param String $tablename The target table name
     * @return \DB
     */
    public function Update($tablename) {
        $this->query('UPDATE ' . $tablename . ' ');
        $this->__trailspaceQuery();
        return $this;
    }

    /**
     * Values function, specifically used for INSERT command
     * @param Array $a_values Array of values to be inserted
     * @return \DB
     */
    public function Values($a_values, $a_quoteindices=array()) {
        $this->query .= 'VALUES(';
        foreach($a_quoteindices as $qindex) {
            if ($qindex < count($a_values)) {
                $a_values[intval($qindex)] = '\'' . mysqli_real_escape_string($this->mysqli, $a_values[intval($qindex)]) . '\'';
            }
        }
        for ($x = 0; $x < count($a_values); $x++) {
            $this->query .= $a_values[$x] . ($x < count($a_values) - 1 ? ', ' : ' );');
        }
        $this->__trailspaceQuery();
        return $this;
    }

    /**
     * Conditional function WHERE
     * @param String $condition A string containing the condition(s) for this query
     */
    public function Where($condition) {
        $has_where = stripos($this->query, ' WHERE ');
        if ($has_where !== FALSE) {
            $this->query = strstr($this->query, 'WHERE ', true);
            $this->__trailspaceQuery();
        }
        $this->query .= 'WHERE ' . $condition . ' ';
        $this->__trailspaceQuery();
        return $this;
    }

    # Static functions ---------------------------------------------------------

    /**
     * Returns an array of Table rows from given conditional specifications.
     * @param string $tablename The name of the target table
     * @param Array $a_specifications Linear array of LOGICAL CONDITIONS string, 
     *      each spec is separated by AND
     * @return Array(assoc)
     */
    public static function __getRecord($tablename, $a_specifications) {
        $mysql = new DB();
        $mysql->Select()->From($tablename);
        // build where string
        $str_where = '';
        for ($x=0; $x < count($a_specifications); $x++) {
            $str_where .= $a_specifications[$x] . ($x < count($a_specifications)-1 ? ' AND ' : '');
        }
        $mysql->Where($str_where);
        return $mysql->Query();
    }
    
    /**
     * Lets you get the corresponding value of certain ID value from another
     *      referenced foreign table. Otherwise, returns NULL
     * @param mixed $id The ID value
     * @param String $tablename The name of referenced foreign table
     * @param String $field The name of the target field containing the
     *      corresponding value
     * @return String|null The returned corresponding value, NULL on failure
     */
    public static function __getSubstitute($id, $tablename, $field) {
        $mysql = new DB();
        $mysql->Select([$field])->
                From($tablename)->
                Where($tablename . '.id=' . $id);
        $result = $mysql->Query();
        if (count($result) > 0) {
            return $result[0][$field];
        } else {
            return null;
        }
    }
    
    /**
     * Gets the next index value from certain PK column in a table
     * @param String $table Name of the target table
     * @param String $column Name of the PK column/field
     * @param int $startindex Starting index of the PK field
     * @return int The next index value
     */
    public static function __getFirstIndexval($table, $column, $startindex) {
        $sql = new DB();
        $sql->Select([$column])->From($table);
        $result = $sql->Query();
        $counter = $startindex;
        foreach($result as $row) {
            if ($counter!=$row[$column]) {
                return $counter;
            }
            $counter++;
        }
        return $counter;
    }

}

?>