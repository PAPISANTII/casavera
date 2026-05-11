<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../config.php';
requireAuth();

$id = intval($_GET['id'] ?? 0);
if ($id === 0) { http_response_code(400); exit('ID no válido'); }

$stmt = $pdo->prepare('
    SELECT 
        c.id, c.fecha_inicio, c.fecha_fin, c.tipo_contrato, c.precio_mensual, c.fianza,
        h.numero AS hab_numero, h.planta AS hab_planta, h.tipo AS hab_tipo,
        i.nombre, i.apellidos, i.nacionalidad, i.documento, i.direccion, i.email, i.telefono
    FROM contratos c
    JOIN habitaciones h ON c.habitacion_id = h.id
    JOIN inquilinos  i ON c.inquilino_id  = i.id
    WHERE c.id = ?
');
$stmt->execute([$id]);
$d = $stmt->fetch();
if (!$d) { http_response_code(404); exit('Contrato no encontrado'); }

// — Helpers —
function fechaES($fecha) {
    if (!$fecha) return '—';
    [$y, $m, $dia] = explode('-', $fecha);
    $meses = ['','enero','febrero','marzo','abril','mayo','junio',
              'julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return intval($dia) . ' de ' . $meses[intval($m)] . ' de ' . $y;
}
function tipoContrato($t) {
    return ['estandar'=>'Estándar','erasmus'=>'Erasmus','curso_completo'=>'Curso Completo'][$t] ?? ucfirst($t);
}
function tipoHabitacion($t) {
    $map = [
        'individual_balcon_banio_interior' => 'Individual con balcón y baño interior',
        'individual_banio_interior'        => 'Individual con baño interior',
        'individual_balcon_banio_exterior' => 'Individual con balcón y baño exterior',
        'individual_banio_exterior'        => 'Individual con baño exterior',
        'individual_planta_baja'           => 'Individual en planta baja',
        'doble_banio_interior'             => 'Doble con baño interior',
        'doble_balcon_banio_exterior'      => 'Doble con balcón y baño exterior',
    ];
    return $map[$t] ?? $t;
}

$inicio   = new DateTime($d['fecha_inicio']);
$fin      = new DateTime($d['fecha_fin']);
$diff     = $inicio->diff($fin);
$meses    = ($diff->y * 12) + $diff->m + ($diff->d > 0 ? 1 : 0);
$total    = $meses * floatval($d['precio_mensual']);
$numDoc   = str_pad($d['id'], 4, '0', STR_PAD_LEFT);

require '../tcpdf/tcpdf.php';

// HTML del contrato — TCPDF acepta HTML con estilos inline
$html = '
<style>
    body { font-family: helvetica; font-size: 10px; color: #1a1a1a; }
    h1   { font-size: 16px; color: #C0614A; text-align: center; margin-bottom: 2px; }
    h2   { font-size: 12px; text-align: center; margin-bottom: 2px; }
    .sub { font-size: 9px; color: #888; text-align: center; margin-bottom: 10px; }
    .seccion { background-color: #F2E8D9; font-size: 9px; font-weight: bold; 
               padding: 5px 8px; margin-top: 12px; margin-bottom: 6px; 
               text-transform: uppercase; letter-spacing: 1px; }
    table { width: 100%; margin-bottom: 4px; }
    td.label { width: 42%; font-size: 9px; color: #6b6b6b; font-weight: bold; padding: 3px 0; }
    td.valor { font-size: 9px; color: #1a1a1a; padding: 3px 0; }
    .clausula { font-size: 9px; color: #333; margin-bottom: 4px; }
    .firma-box { width: 45%; display: inline-block; text-align: center; }
    hr { border: none; border-top: 1px solid #F2E8D9; margin: 6px 0; }
</style>

<h1>CASA VERA ESTUDIANTES</h1>
<p class="sub">Cartagena, Región de Murcia</p>
<hr/>
<h2>CONTRATO DE ARRENDAMIENTO DE HABITACIÓN</h2>
<p class="sub">Contrato n.º ' . $numDoc . ' &nbsp;·&nbsp; Tipo: ' . tipoContrato($d['tipo_contrato']) . '</p>

<div class="seccion">Datos del Inquilino</div>
<table>
    <tr><td class="label">Nombre completo:</td><td class="valor">' . htmlspecialchars($d['nombre'] . ' ' . $d['apellidos']) . '</td></tr>
    <tr><td class="label">Documento (DNI/Pasaporte):</td><td class="valor">' . htmlspecialchars($d['documento'] ?: '—') . '</td></tr>
    <tr><td class="label">Nacionalidad:</td><td class="valor">' . htmlspecialchars($d['nacionalidad'] ?: '—') . '</td></tr>
    <tr><td class="label">Dirección de procedencia:</td><td class="valor">' . htmlspecialchars($d['direccion'] ?: '—') . '</td></tr>
    <tr><td class="label">Email:</td><td class="valor">' . htmlspecialchars($d['email'] ?: '—') . '</td></tr>
    <tr><td class="label">Teléfono:</td><td class="valor">' . htmlspecialchars($d['telefono'] ?: '—') . '</td></tr>
</table>

<div class="seccion">Datos de la Habitación</div>
<table>
    <tr><td class="label">Número de habitación:</td><td class="valor">' . intval($d['hab_numero']) . '</td></tr>
    <tr><td class="label">Planta:</td><td class="valor">' . intval($d['hab_planta']) . '</td></tr>
    <tr><td class="label">Tipo:</td><td class="valor">' . htmlspecialchars(tipoHabitacion($d['hab_tipo'])) . '</td></tr>
</table>

<div class="seccion">Condiciones Económicas</div>
<table>
    <tr><td class="label">Fecha de inicio:</td><td class="valor">' . fechaES($d['fecha_inicio']) . '</td></tr>
    <tr><td class="label">Fecha de fin:</td><td class="valor">' . fechaES($d['fecha_fin']) . '</td></tr>
    <tr><td class="label">Duración:</td><td class="valor">' . $meses . ' ' . ($meses === 1 ? 'mes' : 'meses') . '</td></tr>
    <tr><td class="label">Precio mensual:</td><td class="valor">' . number_format(floatval($d['precio_mensual']), 2, ',', '.') . ' €</td></tr>
    <tr><td class="label">Fianza:</td><td class="valor">' . number_format(floatval($d['fianza']), 2, ',', '.') . ' €</td></tr>
    <tr><td class="label">Importe total estimado:</td><td class="valor">' . number_format($total, 2, ',', '.') . ' €</td></tr>
</table>

<div class="seccion">Cláusulas del Contrato</div>
<p class="clausula">1. El arrendatario se compromete a abonar el precio mensual pactado dentro de los cinco primeros días de cada mes.</p>
<p class="clausula">2. La fianza será devuelta al finalizar el contrato, previa comprobación del estado de la habitación.</p>
<p class="clausula">3. Queda prohibido fumar dentro de las instalaciones del edificio.</p>
<p class="clausula">4. El arrendatario se compromete a mantener la habitación y las zonas comunes en buen estado.</p>
<p class="clausula">5. El horario de silencio es de 22:00 a 09:00 horas.</p>
<p class="clausula">6. No se permite la presencia de mascotas en las instalaciones.</p>
<p class="clausula">7. El arrendatario no podrá subarrendar la habitación ni ceder el contrato a terceros.</p>
<p class="clausula">8. El incumplimiento de cualquiera de estas cláusulas podrá ser causa de resolución del contrato.</p>

<br/><br/>
<div class="seccion">Firmas</div>
<br/>
<table>
    <tr>
        <td style="width:45%; text-align:center; font-size:9px;">
            El Arrendador<br/><br/><br/>
            ___________________________<br/>
            Casa Vera Estudiantes<br/>
            <i>Cartagena, ' . fechaES(date('Y-m-d')) . '</i>
        </td>
        <td style="width:10%;"></td>
        <td style="width:45%; text-align:center; font-size:9px;">
            El Arrendatario<br/><br/><br/>
            ___________________________<br/>
            ' . htmlspecialchars($d['nombre'] . ' ' . $d['apellidos']) . '<br/>
            <i>Cartagena, ' . fechaES(date('Y-m-d')) . '</i>
        </td>
    </tr>
</table>
';

// — Generar y descargar —
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('Casa Vera Panel');
$pdf->SetAuthor('Casa Vera Estudiantes');
$pdf->SetTitle('Contrato n.º ' . $numDoc);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();
$pdf->writeHTML($html, true, false, true, false, '');

$nombreArchivo = 'contrato_' . $numDoc . '_' . strtolower(str_replace(' ', '_', $d['apellidos'])) . '.pdf';
$pdf->Output($nombreArchivo, 'D');