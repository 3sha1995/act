<?php
// File: /cms/Database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'wmsu_services';
    private $username = 'root'; // change if different
    private $password = '';     // change if different
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
