<?php

namespace RelluDatabase\Database;

/**
 * Description of Database
 *
 * @author rellu
 */
class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $db_link;

    public function Database($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        $this->connect();
    }

    /**
     * Connect to the Database
     */
    private function connect() {
        $this->db_link = mysqli_connect($this->host, $this->username, $this->password);
        if ($this->db_link) {
            mysqli_select_db($this->db_link, $this->database);
        } else {
            die("<p>Error: " . mysqli_errno($this->db_link) . " " . mysqli_error($this->db_link) . "</p>");
        }
    }
    
     /**
     * 
     * @param string $file loads /admin/sqls/$file
     * @param array $params parameter for script
     * @param string $types the types of the params e.g. i (integer) s (String)
     * @return bool success
     */
    private function execute(string $file, array $params, string $types = null) : bool {
        if($types === null){
            $types = $this->getTypes($params);
        }
        if ($this->db_link) {
            $statment = mysqli_prepare($this->db_link, $this->loadSQLFile($file));
            $statment->bind_param($types, ...$params);
            $success = $statment->execute();
            $statment->close();
            return $success;
        }
    }
    
    /**
     * 
     * @param string $file loads /admin/sqls/$file
     * @param array $params parameter for script
     * @param string $types the types of the params e.g. i (integer) s (String)
     * @return array Result of query
     */
    function select(string $file, array $params, string $types = null) : array {
        if($types === null){
            $types = $this->getTypes($params);
        }
        if ($this->db_link) {
            $statment = mysqli_prepare($this->db_link, $this->loadSQLFile($file));
            if(sizeof($params) >= 1){
                $statment->bind_param($types, ...$params);
            }
            $statment->execute();
            $result = $statment->get_result();
            $statment->close();
            
            $select = array();
            
            while( $row = mysqli_fetch_assoc($result)){
                $select[] = $row;
            }
            
            $result->close();
            
            return $select;
        }
    }
    
    /**
     * 
     * @param string $file loads /admin/sqls/$file
     * @param array $params parameter for script
     * @param string $types the type of the params e.g. i (integer) s (String)
     * @return array Result of query
     */
    function update(string $file, array $params, string $types = null) : bool {
        return $this->execute($file, $params, $types);
    }
    
    /**
     * 
     * @param string $file loads /admin/sqls/$file
     * @param array $params parameter for script
     * @param string $types the type of the params e.g. i (integer) s (String)
     * @return array Result of query
     */
    function delete(string $file, array $params, string $types = null) : bool {
        return $this->execute($file, $params, $types);
    }
    
    /**
     * 
     * @param string $file loads /admin/sqls/$file
     * @return string File Contents as a String
     */
    private function loadSQLFile($file) : string {
        return file_get_contents($file);
    }

    /**
     * 
     * @param array $fields to get the type from
     * @return string with types
     */
    private function getTypes(array $fields) : string{
        $types = "";
        
        foreach ($fields as $field) {            
            switch (gettype($field)) {
                case "boolean": $types .= "i"; break;
                case "integer": $types .= "i"; break;
                case "double":  $types .= "d"; break;
                case "string":  $types .= "s"; break;
                default:        $types .= "s"; break;
            }
        }
        return $types;
    }
    
    /**
     * Closes the Database Connection
     */
    public function close() {
        mysqli_close($this->db_link);
    }
}
