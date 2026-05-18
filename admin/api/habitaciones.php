<?php
require '../config.php';
requireAuth();

$accion = $_GET['accion'] ?? '';

if ($accion === 'listar') {
    try {
        $stmt = $pdo->query('SELECT id, numero, planta, tipo, precio, estado FROM habitaciones ORDER BY planta, numero');
        $habitaciones = $stmt->fetchAll();
        jsonResponse(['success' => true, 'habitaciones' => $habitaciones]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

if ($accion === 'detalle') {
    $num = $_GET['num'] ?? 0;
    try {
        $stmt = $pdo->prepare('
            SELECT h.*, i.nombre, i.apellidos, i.nacionalidad, i.documento, i.email, i.telefono,
                   c.fecha_inicio, c.fecha_fin, c.tipo_contrato, c.precio_mensual, c.fianza
            FROM habitaciones h
            LEFT JOIN contratos c ON h.id = c.habitacion_id AND c.fecha_fin >= CURDATE()
            LEFT JOIN inquilinos i ON c.inquilino_id = i.id
            WHERE h.numero = ?
        ');
        $stmt->execute([$num]);
        $h = $stmt->fetch();

        if (!$h) {
            jsonResponse(['success' => false, 'error' => 'No encontrada']);
        }

        $hab = [
            'numero'    => $h['numero'],
            'planta'    => $h['planta'],
            'tipo'      => $h['tipo'],
            'precio'    => $h['precio'],
            'estado'    => $h['estado'],
            'inquilino' => null,
            'contrato'  => null
        ];

        if ($h['estado'] !== 'disponible' && $h['nombre']) {
            $hab['inquilino'] = [
                'nombre'       => $h['nombre'],
                'apellidos'    => $h['apellidos'],
                'nacionalidad' => $h['nacionalidad'],
                'documento'    => $h['documento'],
                'email'        => $h['email'],
                'telefono'     => $h['telefono']
            ];
        }

        if ($h['fecha_inicio']) {
            $hab['contrato'] = [
                'fecha_inicio'   => $h['fecha_inicio'],
                'fecha_fin'      => $h['fecha_fin'],
                'tipo_contrato'  => $h['tipo_contrato'],
                'precio_mensual' => $h['precio_mensual'],
                'fianza'         => $h['fianza']
            ];
        }

        jsonResponse(['success' => true, 'habitacion' => $hab]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

if ($accion === 'precio_por_id') {
    $id = intval($_GET['id'] ?? 0);
    try {
        $stmt = $pdo->prepare('SELECT precio FROM habitaciones WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        jsonResponse(['success' => true, 'precio' => $row ? floatval($row['precio']) : 0]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

if ($accion === 'habitacion_por_inquilino') {
    $inquilino_id = intval($_GET['inquilino_id'] ?? 0);
    try {
        $stmt = $pdo->prepare('SELECT habitacion_id FROM inquilinos WHERE id = ?');
        $stmt->execute([$inquilino_id]);
        $row = $stmt->fetch();
        jsonResponse(['success' => true, 'habitacion_id' => $row ? $row['habitacion_id'] : null]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

http_response_code(400);
jsonResponse(['success' => false, 'error' => 'Accion no valida']);