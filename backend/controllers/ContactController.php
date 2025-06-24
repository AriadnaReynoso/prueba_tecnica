<?php
require_once 'models/Contact.php';
require_once 'middlewares/Middleware.php';
require_once 'validators/Validator.php';

// Clase para manejar las solicitudes de la API
class ContactController {
    private $db; 
    private $contact; 

    public function __construct($db) {
        $this->db = $db;
        $this->contact = new Contact($db);
    }

    // Crear un contacto nuevo
    public function create() {
        header('Content-Type: application/json');

        // revisar que la solicitud sea POST y JSON
        Middleware::validateRequestMethod(['POST']);
        $data = Middleware::validateJsonInput();

        // Revisar que los datos sean válidos
        $errors = Validator::validateContact($data);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            return;
        }

        // Limpiar los datos
        $data = Middleware::sanitizeInput($data);

        // pasa los datos al objeto Contact
        $this->contact->first_name = $data->first_name;
        $this->contact->last_name = $data->last_name;
        $this->contact->email = $data->email;
        $this->contact->phones = $data->phones ?? [];

        // Guardar el contacto
        if ($this->contact->create()) {
            http_response_code(201);
            echo json_encode(['message' => 'Contacto creado con éxito']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear el contacto']);
        }
    }

    // Listar todos los contactos
    public function read() {
        header('Content-Type: application/json');

        Middleware::validateRequestMethod(['GET']);

        $contacts = $this->contact->readAll();

        // Separa los números en una lista
        $formatted_contacts = array_map(function($contact) {
            $contact['phone_numbers'] = $contact['phone_numbers'] ? explode(',', $contact['phone_numbers']) : [];
            return $contact;
        }, $contacts);

        // enviar los contactos como JSON
        echo json_encode($formatted_contacts);
    }

    // Borrar un contacto por ID
    public function delete($id) {
        header('Content-Type: application/json');

        // Revisa que sea DELETE
        Middleware::validateRequestMethod(['DELETE']);

        // Intenta borrar
        if ($this->contact->delete($id)) {
            echo json_encode(['message' => 'Contacto borrado con éxito']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo borrar el contacto']);
        }
    }
}