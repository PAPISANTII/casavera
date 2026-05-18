<?php
require '../config.php';
requireAuth();

function fechaEspanol($fecha) {
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    $dt   = new DateTime($fecha);
    $dia  = $dt->format('d');
    $mes  = $meses[(int)$dt->format('n')];
    $anio = $dt->format('Y');
    return $dia . ' de ' . $mes . ' de ' . $anio;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); exit('ID no válido'); }

$stmt = $pdo->prepare('
    SELECT c.fecha_inicio, c.fecha_fin, c.precio_mensual,
           i.nombre, i.apellidos, i.nacionalidad, i.documento, i.direccion, i.email, i.telefono,
           h.numero AS hab_numero
    FROM contratos c
    JOIN inquilinos i ON c.inquilino_id = i.id
    JOIN habitaciones h ON c.habitacion_id = h.id
    WHERE c.id = ?
');
$stmt->execute([$id]);
$d = $stmt->fetch();
if (!$d) { http_response_code(404); exit('No encontrado'); }

require '../tcpdf/tcpdf.php';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('Casa Vera');
$pdf->SetAuthor('Casa Vera');
$pdf->SetTitle('Ficha de Residente — Casa Vera');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(25, 25, 25);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

$habNum  = 'H' . str_pad($d['hab_numero'], 2, '0', STR_PAD_LEFT);
$periodo = fechaEspanol($d['fecha_inicio']) . ' — ' . fechaEspanol($d['fecha_fin']);
$precio  = number_format($d['precio_mensual'], 0, ',', '.') . '€';

//logo
$logoPath = dirname(dirname(__DIR__)) . '/img/logo.jpg';
$logoHtml = '';
if (file_exists($logoPath)) {
    $logoHtml = '<img src="' . $logoPath . '" width="72" height="72"><br>';
}

$html = '
<p style="text-align:center;">' . $logoHtml . '</p>
<h1 style="text-align:center; color: blue"><b>FICHA DE RESIDENTE — CASA VERA</b></h1>
<br>
<p><b>Habitación:</b> ' . $habNum . '</p>
<p><b>Nombre completo:</b> ' . htmlspecialchars($d['nombre'] . ' ' . $d['apellidos']) . '</p>
<p><b>Domicilio:</b> ' . htmlspecialchars($d['direccion'] ?: '') . '</p>
<p><b>Número de pasaporte /</b> ' . htmlspecialchars($d['documento']) . '</p>
<p><b>Correo electrónico:</b> ' . htmlspecialchars($d['email'] ?: '') . '</p>
<p><b>Teléfono:</b> ' . htmlspecialchars($d['telefono'] ?: '') . '</p>
<p><b>Teléfono de contacto en caso de emergencia:</b> ________________________</p>
<p><b>Universidad en la que estudiará en Cartagena:</b> ________________________</p>
<p><b>Mail de contacto universidad de procedencia:</b> ________________________</p>
<p><b>Día y hora de llegada:</b> ________________________</p>
<p><b>Estado de habitación:</b> ________________________</p>
<p><b>Balcón:</b> ________________________</p>
<p><b>Baño:</b> ________________________</p>
<p><b>Precio mensual:</b> ' . $precio . '</p>
<p><b>Periodo de estancia:</b> ' . $periodo . '</p>
<p><b>Nacionalidad:</b> ' . htmlspecialchars($d['nacionalidad'] ?: '') . '</p>
<p><b>Otros datos:</b> ________________________</p>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('ficha_residente_' . $habNum . '.pdf', 'D');