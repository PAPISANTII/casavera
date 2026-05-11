<?php
require '../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    $stmt = $pdo->query('SELECT tipo, precio FROM precios_tipo');
    $rows = $stmt->fetchAll();

    $mapa = [
        'individual_balcon_banio_interior'  => 'individual_balcon_banio_interior',
        'individual_banio_interior'         => 'individual_sin_balcon_banio_interior',
        'individual_balcon_banio_exterior'  => 'individual_balcon_banio_exterior',
        'individual_banio_exterior'         => 'individual_sin_balcon_banio_exterior',
        'individual_planta_baja'            => 'individual_planta_baja',
        'doble_banio_interior'              => 'doble_grande_banio_interior',
        'doble_balcon_banio_exterior'       => 'doble_balcon_banio_exterior',
    ];

    $precios = [];
    foreach ($rows as $row) {
        $key = $mapa[$row['tipo']] ?? null;
        if ($key) $precios[$key] = (int) $row['precio'];
    }

    echo json_encode(['success' => true, 'precios' => $precios], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno']);
}