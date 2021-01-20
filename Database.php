<?php

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
     * Closes the Database Connection
     */
    public function close() {
        mysqli_close($this->db_link);
    }
}
