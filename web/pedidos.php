<?php
("")
declare(strict_types=1);

$host    = '127.0.0.1';
$dbname  = 'restaurante';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

header('Content-Type: application/json; charset=utf-8');

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $stmt = $pdo->query('SELECT * FROM pedidos');
    $registros = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'total'   => count($registros),
        'data'    => $registros,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error al conectar o consultar la base de datos',
        'detalle' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}