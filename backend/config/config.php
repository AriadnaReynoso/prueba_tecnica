<?php
// Clase para conectar con la base de datos
class DatabaseConfig {
    private $host = 'localhost'; 
    private $db_name = 'contact_db'; 
    private $username = 'root'; 
    private $password = '';
    private $conn; 

    public function getConnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);     
        
        if ($this->conn->connect_error) {
            die("No se pudo conectar: " . $this->conn->connect_error);
        }
        
        // Usa utf8mb4 para que los caracteres (como acentos) se guarden bien
        $this->conn->set_charset("utf8mb4");
        
        return $this->conn; 
    }
}
?>