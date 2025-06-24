<?php

require_once 'config/config.php';
require_once 'controllers/ContactController.php';
require_once 'middlewares/Middleware.php';

// Permite solicitudes desde cualquier sitio (para pruebas con Postman)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Muestra que la solicitud llegó
echo json_encode(['debug' => 'Solicitud recibida en index.php']);

$db_config = new DatabaseConfig();
$db = $db_config->getConnection();

$controller = new ContactController($db);

// obtener la ruta limpia
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Elimina '/backend/index.php' de la ruta si está presente
$base_path = '/backend/index.php';
if (strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}
$path = trim($path, '/');
$path_parts = explode('/', $path);

// Muestra la ruta procesada para depuración
echo json_encode(['debug' => 'Ruta procesada', 'path' => $path, 'path_parts' => $path_parts]);

$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'POST':
        echo json_encode(['debug' => 'Entró al caso POST']);
        if (empty($path_parts[0]) || $path_parts[0] === 'contacts') {
            echo json_encode(['debug' => 'Ruta correcta: /contacts']);
            $controller->create(); 
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(['error' => 'Ruta no encontrada', 'path' => $path]);
        }
        break;

    case 'GET':
        if (empty($path_parts[0]) || $path_parts[0] === 'contacts') {
            $controller->read(); 
        }
        break;

    case 'DELETE':
        if ($path_parts[0] === 'contacts' && isset($path_parts[1])) {
            $controller->delete($path_parts[1]); 
        }
        break;

    default:
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>