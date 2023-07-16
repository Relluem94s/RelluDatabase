<?php

namespace Relluem94;

/**
 * Welcome to the Docs of RelluDatabase
 * 
 * @link https://github.com/Relluem94s/RelluDatabase/
 *
 * @author Relluem94
 */
class Database {

    /**
     * IP / URL to Database
     * @var string
     */
    private $host;

    /**
     * Username to auth with Database
     * @var string
     */
    private $username;

    /**
     * Password to auth with Database
     * @var string
     */
    private $password;

    /**
     * Database to connect to
     * @var string
     */
    private $database;
    
    /**
     * Stores Standardcharset
     * @var string 
     */
    private $charset;

    /**
     * Stores the Connection
     * @var object 
     */
    private $db_link;
    
    /**
     *  Constructor
     * 
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string $charset
     */
    public function __construct($host, $username, $password, $database, $charset = "utf8") {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;

        if (!$this->connect()) {
            die("<p>Error: " . mysqli_errno($this->db_link) . " " . mysqli_error($this->db_link) . "</p>");
        }
    }

    /**
     * Connect to the Database
     * @return bool success?
     */
    private function connect(): bool {
        $this->db_link = mysqli_connect($this->host, $this->username, $this->password);
        if ($this->db_link) {
            $this->db_link->set_charset($this->charset);
            return mysqli_select_db($this->db_link, $this->database);
        } else {
            return false;
        }
    }

    /**
     * Executes a Statement
     * 
     * @param string $file loads File in Path
     * @param array $params parameter for script
     * @param string $types the types of the params e.g. i (integer) s (String)
     * @return bool success
     */
    private function execute_with_param(string $file, array $params, string $types = null): bool {
        if ($types === null) {
            $types = $this->getTypes($params);
        }
        if ($this->db_link) {
            $statment = mysqli_prepare($this->db_link, $this->loadSQLFile($file));
            $statment->bind_param($types, ...$params);
            $success = $statment->execute();
            $statment->close();
            return $success;
        }
        else {
            return false;
        }
    }

    /**
     * Executes Select Prepare Statement 
     * 
     * @param string $file loads File in Path
     * @param array $params parameter for script
     * @param string $types the types of the params e.g. i (integer) s (String)
     * @return array Result of query
     */
    function select(string $file, array $params, string $types = null): array {
        if ($types === null) {
            $types = $this->getTypes($params);
        }
        if ($this->db_link) {
            $statment = mysqli_prepare($this->db_link, $this->loadSQLFile($file));
            if (sizeof($params) >= 1) {
                $statment->bind_param($types, ...$params);
            }
            $statment->execute();
            $result = $statment->get_result();
            $statment->close();

            $select = array();

            while ($row = mysqli_fetch_assoc($result)) {
                $select[] = $this->decodeRow($row);
            }

            $result->close();

            return $select;
        }
        else {
            return ["success" => false];
        }
    }

    /**
     * Decodes all fields with html_entity_decode
     * 
     * @param array $row the row to decode
     * @return array decoded row
     */
    private function decodeRow(array $row) {
        $row_data = array();
        foreach ($row as $k => $v) {
            $row_data += array($k => html_entity_decode(($v), ENT_QUOTES | ENT_HTML5));
        }
        return $row_data;
    }

    /**
     * Executes a update Statement
     * 
     * @param string $file loads File in Path
     * @param array $params parameter for script
     * @param string $types the type of the params e.g. i (integer) s (String)
     * @return array Result of query
     */
    public function update(string $file, array $params, string $types = null): bool {
        return $this->execute_with_param($file, $params, $types);
    }

    /**
     * Executes a delete Statement 
     * 
     * @param string $file loads File in Path
     * @param array $params parameter for script
     * @param string $types the type of the params e.g. i (integer) s (String)
     * @return array Result of query
     */
    public function delete(string $file, array $params, string $types = null): bool {
        return $this->execute_with_param($file, $params, $types);
    }

    /**
     * Executes a insert Statement
     * 
     * @param string $file loads File in Path
     * @param array $params parameter for script
     * @param string $types the type of the params e.g. i (integer) s (String)
     * @return array Result of query
     */
    public function insert(string $file, array $params, string $types = null): bool {
        return $this->execute_with_param($file, $params, $types);
    }
    
    /**
     * Executes a Statement
     * 
     * @param string $file loads File in Path
     * @param array $params parameter for script
     * @param string $types the types of the params e.g. i (integer) s (String)
     * @return bool success
     */
    public function execute(string $file): bool {
        if ($this->db_link) {
            $statment = mysqli_prepare($this->db_link, $this->loadSQLFile($file));
            $success = $statment->execute();
            $statment->close();
            return $success;
        }
        else {
            return false;
        }
    }

    /**
     * Loads a File from the specified Path
     * 
     * @param string $file loads File in Path
     * @return string File Contents as a String
     */
    private function loadSQLFile($file): string {
        return file_get_contents($file);
    }

    /**
     * Analyses Type of Field and appends the right type to the PrepareStatement
     * 
     * @param array $fields to get the type from
     * @return string with types
     */
    private function getTypes(array $fields): string {
        $types = "";

        foreach ($fields as $field) {
            switch (gettype($field)) {
                case "boolean": $types .= "i";
                    break;
                case "integer": $types .= "i";
                    break;
                case "double": $types .= "d";
                    break;
                case "string": $types .= "s";
                    break;
                default: $types .= "s";
                    break;
            }
        }
        return $types;
    }

    /**
     * Closes the Database Connection
     */
    public function close(): bool {
        if($this->db_link){
            return mysqli_close($this->db_link);
        }
        else{
            return false;
        }
    }
}

