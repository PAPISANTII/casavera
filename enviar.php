<?php
// ================================
// CONFIGURACIÓN
// ================================

$email_destino = 'info@casaveraestudiantes.es';

// ================================
// SEGURIDAD — solo POST
// ================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método no permitido.');
}

// ================================
// RECOGER Y SANEAR DATOS
// ================================

$nombre     = isset($_POST['nombre'])     ? htmlspecialchars(trim($_POST['nombre']))     : '';
$email      = isset($_POST['email'])      ? htmlspecialchars(trim($_POST['email']))      : '';
$telefono   = isset($_POST['telefono'])   ? htmlspecialchars(trim($_POST['telefono']))   : 'No indicado';
$habitacion = isset($_POST['habitacion']) ? htmlspecialchars(trim($_POST['habitacion'])) : 'No indicada';
$mensaje    = isset($_POST['mensaje'])    ? htmlspecialchars(trim($_POST['mensaje']))    : '';
$privacidad = isset($_POST['privacidad']) ? true : false;
$idioma = (isset($_POST['idioma']) && $_POST['idioma'] === 'en') ? '🇬🇧 Formulario EN' : '🇪🇸 Formulario ES';

// ================================
// VALIDACIÓN
// ================================

$errores = [];

if (empty($nombre)) {
  $errores[] = 'El nombre es obligatorio.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errores[] = 'El email no es válido.';
}

if (empty($mensaje)) {
  $errores[] = 'El mensaje es obligatorio.';
}

if (!$privacidad) {
  $errores[] = 'Debes aceptar la política de privacidad.';
}

if (!empty($errores)) {
  http_response_code(422);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'errores' => $errores]);
  exit;
}

// ================================
// HONEYPOT ANTISPAM
// ================================

if (!empty($_POST['website'])) {
  http_response_code(200);
  header('Content-Type: application/json');
  echo json_encode(['ok' => true]);
  exit;
}

// ================================
// ASUNTO — usa la habitación elegida
// ================================

if (!empty($habitacion) && $habitacion !== 'No indicada' && $habitacion !== 'Aún no lo sé') {
  $asunto = '[Casa Vera] ' . $idioma . ' · Consulta: ' . $habitacion . ' — ' . $nombre;
} else {
  $asunto = '[Casa Vera] ' . $idioma . ' · Nuevo mensaje de contacto — ' . $nombre;
}

// ================================
// CUERPO DEL EMAIL
// ================================

$cuerpo = "
Nuevo mensaje recibido desde casaveraestudiantes.es
=====================================================

DATOS DEL CONTACTO
-------------------
Idioma:       {$idioma}
Nombre:       {$nombre}
Email:        {$email}
Teléfono:     {$telefono}
Habitación:   {$habitacion}

MENSAJE
--------
{$mensaje}

=====================================================
Mensaje enviado desde casaveraestudiantes.es
";

// ================================
// CABECERAS
// ================================

$cabeceras  = "From: info@casaveraestudiantes.es\r\n";
$cabeceras .= "Reply-To: {$email}\r\n";
$cabeceras .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";

// ================================
// ENVIAR
// ================================

$enviado = mail($email_destino, $asunto, $cuerpo, $cabeceras);

header('Content-Type: application/json');

if ($enviado) {
  http_response_code(200);
  echo json_encode(['ok' => true]);
} else {
  http_response_code(500);
  echo json_encode(['ok' => false, 'errores' => ['Error al enviar el mensaje. Inténtalo de nuevo o escríbenos directamente a info@casaveraestudiantes.es']]);
}
?>