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
    SELECT c.fecha_inicio, c.fecha_fin, c.fianza,
           i.nombre, i.apellidos,
           h.numero AS hab_numero, h.tipo AS hab_tipo
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
$pdf->SetTitle('Recibo de Fianza — Casa Vera');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(25, 25, 25);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

$habNum      = 'H' . str_pad($d['hab_numero'], 2, '0', STR_PAD_LEFT);
$nombreCompleto = $d['nombre'] . ' ' . $d['apellidos'];
$periodo     = fechaEspanol($d['fecha_inicio']) . ' — ' . fechaEspanol($d['fecha_fin']);
$importe     = number_format($d['fianza'], 0, ',', '.') . '€';
$fechaDoc    = fechaEspanol(date('Y-m-d'));

// Logo
$logoPath = dirname(__DIR__) . '/img/logo.jpg';
$logoHtml = '';
if (file_exists($logoPath)) {
    $logoHtml = '<img src="' . $logoPath . '" width="72" height="72"><br>';
}

$html = '
<p style="text-align:center;">' . $logoHtml . '</p>
<h2 style="text-align:center;"><b>RECIBO DE FIANZA</b></h2>
<br>
<p>Este documento certifica la recepción de una fianza para la reserva de alojamiento en Casa Vera.</p>
<br>
<p><b>• Nombre del estudiante:</b> ' . htmlspecialchars($nombreCompleto) . '</p>
<p><b>• Alojamiento:</b> Casa Vera — Alojamiento para estudiantes</p>
<p><b>• Dirección:</b> Paseo de las Delicias, 23, Cartagena (España)</p>
<p><b>• Tipo de habitación:</b> ' . htmlspecialchars($d['hab_tipo'] ?: '—') . '</p>
<p><b>• Importe de la fianza:</b> ' . $importe . '</p>
<p><b>• Periodo de alquiler:</b> ' . $periodo . '</p>
<br>
<p><b>Condiciones de la fianza</b></p>
<br>
<p>La fianza será devuelta al final de la estancia, siempre que todo transcurra con normalidad y se cumplan todas las condiciones contractuales.</p>
<br>
<p>La fianza no será devuelta si <b>' . htmlspecialchars($nombreCompleto) . '</b> no se presenta en el alojamiento.</p>
<br>
<p><b>Contrato y normas de convivencia</b></p>
<br>
<p>A la llegada al alojamiento, el estudiante deberá firmar el contrato de arrendamiento y las Normas de Convivencia de Casa Vera, que forman parte integrante del contrato.</p>
<br><br>
<p>Lugar y fecha: Cartagena, ' . $fechaDoc . '</p>
<br><br>
<p>Helena Navarro Ros<br>En representación de Casa Vera<br>Alojamiento para estudiantes</p>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('recibo_fianza_' . $habNum . '_' . strtolower(str_replace(' ', '_', $d['apellidos'])) . '.pdf', 'D');