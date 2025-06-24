<?php
// Clase para revisar las solicitudes antes de procesarlas
class Middleware {
    // Revisa que la solicitud sea en formato JSON
    public static function validateJsonInput() {
        header('Content-Type: application/json'); 

        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            http_response_code(400);
            echo json_encode(['error' => 'La solicitud debe ser en formato JSON']);
            exit;
        }

        // Leer los datos del JSON
        $input = file_get_contents('php://input');
        $data = json_decode($input);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'El JSON no es válido']);
            exit;
        }

        return $data; 
    }

    // Revisar que el método HTTP (como POST o GET) sea permitido
    public static function validateRequestMethod($allowedMethods) {
        header('Content-Type: application/json');

        $method = $_SERVER['REQUEST_METHOD'];

        if (!in_array($method, $allowedMethods)) {
            http_response_code(405);
            echo json_encode(['error' => "Método $method no permitido. Usa: " . implode(', ', $allowedMethods)]);
            exit;
        }
    }

    // Limpia los datos para evitar código malicioso
    public static function sanitizeInput($data) {
        if (is_object($data)) {
            // Revisa cada dato
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    // Limpia textos
                    $data->$key = htmlspecialchars(strip_tags($value));
                } elseif (is_array($value)) {
                    // Limpia listas (como teléfonos)
                    $data->$key = array_map(function($item) {
                        return htmlspecialchars(strip_tags($item));
                    }, $value);
                }
            }
        }
        return $data; 
    }
}