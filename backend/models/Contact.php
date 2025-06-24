<?php
// Clase para manejar los contactos en la base de datos
class Contact {
    private $conn; 
    private $table_name = "contacts"; 

    public $contact_id; 
    public $first_name; 
    public $last_name; 
    public $email; 
    public $phones; 

    public function __construct($db) {
        $this->conn = $db;
        $this->phones = [];
    }

    // Guardar un nuevo contacto en la base de datos
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (first_name, last_name, email) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query); // Prepara la consulta

        if ($stmt === false) {
            return false;
        }

        // Limpia los datos para evitar código malicioso
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Pone los datos en los ? de la consulta
        $stmt->bind_param('sss', $this->first_name, $this->last_name, $this->email); // 'sss' = tres textos

        // Ejecuta la consulta
        if ($stmt->execute()) {
            $this->contact_id = $this->conn->insert_id; 
            $this->savePhones(); 
            $stmt->close(); 
            return true; 
        }

        $stmt->close(); 
        return false; 
    }

    // obtener todos los contactos con sus números de teléfono
    public function readAll() {
        $query = "SELECT c.*, GROUP_CONCAT(p.number) as phone_numbers 
                 FROM " . $this->table_name . " c 
                 LEFT JOIN phone_numbers p ON c.contact_id = p.contact_id 
                 GROUP BY c.contact_id";
        $stmt = $this->conn->prepare($query); 

        if ($stmt === false) {
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result(); 
        $contacts = $result->fetch_all(MYSQLI_ASSOC); // Convierte los resultados en una lista
        $stmt->close(); 
        
        return $contacts; 
    }

    // Borrar un contacto y sus números de teléfono usando el ID
    public function delete($id) {
        $this->conn->begin_transaction();
        try {
            // Borra los números de teléfono del contacto
            $phone_query = "DELETE FROM phone_numbers WHERE contact_id = ?"; 
            $phone_stmt = $this->conn->prepare($phone_query);
            if ($phone_stmt === false) {
                throw new Exception("No se pudo preparar la consulta para borrar números");
            }
            $phone_stmt->bind_param('i', $id); // Pone el ID en el ? ('i' = número)
            $phone_stmt->execute(); 
            $phone_stmt->close();

            // Borra el contacto
            $query = "DELETE FROM " . $this->table_name . " WHERE contact_id = ?"; 
            $stmt = $this->conn->prepare($query); 
            if ($stmt === false) {
                throw new Exception("No se pudo preparar la consulta para borrar el contacto");
            }
            $stmt->bind_param('i', $id); 
            $stmt->execute(); 
            $stmt->close();

            $this->conn->commit();
            return true; // Todo salió bien
        } catch (Exception $e) {
            $this->conn->rollback();
            return false; 
        }
    }

    // Guardar los números de teléfono del contacto
    private function savePhones() {
        if (!empty($this->phones)) {
            $query = "INSERT INTO phone_numbers (contact_id, number) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query); 
            if ($stmt === false) {
                return; 
            }

            // Guardar cada número
            foreach ($this->phones as $phone) {
                $phone = htmlspecialchars(strip_tags($phone)); 
                $stmt->bind_param('is', $this->contact_id, $phone); // 'is' = número y texto
                $stmt->execute();
            }
            
            $stmt->close();
        }
    }
}