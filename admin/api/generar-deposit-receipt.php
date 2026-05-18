<?php
require '../config.php';
requireAuth();

function fechaIngles($fecha) {
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    $dt  = new DateTime($fecha);
    $dia = $dt->format('d');
    $mes = $months[(int)$dt->format('n')];
    $anio = $dt->format('Y');
    return $mes . ' ' . $dia . ', ' . $anio;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); exit('Invalid ID'); }

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
if (!$d) { http_response_code(404); exit('Not found'); }

require '../tcpdf/tcpdf.php';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('Casa Vera');
$pdf->SetAuthor('Casa Vera');
$pdf->SetTitle('Deposit Receipt — Casa Vera');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(25, 25, 25);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

$habNum         = 'H' . str_pad($d['hab_numero'], 2, '0', STR_PAD_LEFT);
$nombreCompleto = $d['nombre'] . ' ' . $d['apellidos'];
$periodo        = fechaIngles($d['fecha_inicio']) . ' — ' . fechaIngles($d['fecha_fin']);
$importe        = number_format($d['fianza'], 0, ',', '.') . '€';
$fechaDoc       = fechaIngles(date('Y-m-d'));

// Logo
$logoPath = dirname(__DIR__) . '/img/logo.jpg';
$logoHtml = '';
if (file_exists($logoPath)) {
    $logoHtml = '<img src="' . $logoPath . '" width="72" height="72"><br>';
}

$html = '
<p style="text-align:center;">' . $logoHtml . '</p>
<h2 style="text-align:center;"><b>DEPOSIT RECEIPT</b></h2>
<br>
<p>This document certifies the receipt of a security deposit for the reservation of accommodation at Casa Vera.</p>
<br>
<p><b>• Student Name:</b> ' . htmlspecialchars($nombreCompleto) . '</p>
<p><b>• Accommodation:</b> Casa Vera — Student Accommodation</p>
<p><b>• Address:</b> Paseo de las Delicias, 23, Cartagena (Spain)</p>
<p><b>• Room Type:</b> ' . htmlspecialchars($d['hab_tipo'] ?: '—') . '</p>
<p><b>• Deposit Amount:</b> ' . $importe . '</p>
<p><b>• Rental Period:</b> ' . $periodo . '</p>
<br>
<p><b>Deposit conditions</b></p>
<br>
<p>• The deposit will be refunded at the end of the stay, provided that everything proceeds normally and all contractual conditions are fulfilled.</p>
<br>
<p>• The deposit will not be refunded if <b>' . htmlspecialchars($nombreCompleto) . '</b> does not show up at the accommodation.</p>
<br>
<p><b>Contract and house rules</b></p>
<br>
<p>Upon arrival at the accommodation, the student must sign the rental agreement and the Rules of Coexistence of Casa Vera, which form an integral part of the contract.</p>
<br><br>
<p>Place and date: Cartagena, ' . $fechaDoc . '</p>
<br><br>
<p>Helena Navarro Ros<br>On behalf of Casa Vera<br>Student Accommodation</p>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('deposit_receipt_' . $habNum . '_' . strtolower(str_replace(' ', '_', $d['apellidos'])) . '.pdf', 'D');