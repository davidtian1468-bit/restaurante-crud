<?php

require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Controllers/ProductController.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {

    $db = Database::connect();   // BD 'restaurante': tablas productos y users

    $products = new ProductController($db);
    $users    = new UserController(new User($db));

    /* ---------- Productos ---------- */

    if ($path === '/api/products' && $method === 'GET')           { $products->index(); exit; }
    if ($path === '/api/products/register' && $method === 'POST') { $products->store(); exit; }

    if (preg_match('#^/api/products/([1-9][0-9]*)$#', $path, $m)) {
        $id = (int) $m[1];
        if ($method === 'GET')    { $products->show($id);    exit; }
        if ($method === 'PUT')    { $products->update($id);  exit; }
        if ($method === 'DELETE') { $products->destroy($id); exit; }
    }

    /* ---------- Usuarios ---------- */

    if ($path === '/api/users' && $method === 'GET')  { $users->index(); exit; }
    if ($path === '/api/users' && $method === 'POST') { $users->store(); exit; }

    if (preg_match('#^/api/users/([1-9][0-9]*)$#', $path, $m)) {
        $id = (int) $m[1];
        if ($method === 'GET')    { $users->show($id);    exit; }
        if ($method === 'PUT')    { $users->update($id);  exit; }
        if ($method === 'DELETE') { $users->destroy($id); exit; }
    }

    http_response_code(404);
    echo json_encode(['error' => 'Endpoint no encontrado']);

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);
    $response = ['error' => 'Error interno del servidor'];
    if (getenv('APP_DEBUG') === '1') {      // solo en desarrollo
        $response['detalle'] = $e->getMessage();
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}