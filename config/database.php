<?php
/**
 * Database Configuration for NeverLands Telegram Mini App
 */

class Database {
    private static $instance = null;
    private $connection;

    private $host;
    private $database;
    private $username;
    private $password;
    private $charset;

    private function __construct() {
        // Read from environment variables or use defaults
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->database = getenv('DB_NAME') ?: 'nl';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: 'root';
        $this->charset = getenv('DB_CHARSET') ?: 'cp1251';

        error_log("Database config - Host: {$this->host}, DB: {$this->database}, User: {$this->username}");

        try {
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );

            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

            // Set charset
            $this->connection->set_charset($this->charset);

        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }

    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {}
}
?>
