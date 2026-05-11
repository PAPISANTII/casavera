<?php
require 'config.php';
requireAuth();

$mensaje = '';
$tipoMensaje = '';

$tipos = [
    'individual_balcon_banio_interior'  => 'Individual con balcón y baño interior',
    'individual_banio_interior'         => 'Individual con baño interior',
    'individual_balcon_banio_exterior'  => 'Individual con balcón y baño exterior',
    'individual_banio_exterior'         => 'Individual con baño exterior',
    'individual_planta_baja'            => 'Individual en planta baja',
    'doble_banio_interior'              => 'Doble con baño interior',
    'doble_balcon_banio_exterior'       => 'Doble con balcón y baño exterior',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo   = $_POST['tipo']   ?? '';
    $precio = floatval($_POST['precio'] ?? 0);

    if (array_key_exists($tipo, $tipos) && $precio > 0) {
        // 1. Guarda en la tabla precios_tipo
        $stmt = $pdo->prepare('
            INSERT INTO precios_tipo (tipo, precio) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE precio = ?
        ');
        $stmt->execute([$tipo, $precio, $precio]);

        // 2. Actualiza también todas las habitaciones de ese tipo que ya existan
        $stmt2 = $pdo->prepare('UPDATE habitaciones SET precio = ? WHERE tipo = ?');
        $stmt2->execute([$precio, $tipo]);
        $afectadas = $stmt2->rowCount();

        $mensaje = 'Precio actualizado correctamente.' . ($afectadas > 0 ? ' Se han actualizado ' . $afectadas . ' habitación(es).' : '');
        $tipoMensaje = 'ok';
    } else {
        $mensaje = 'Datos no válidos.';
        $tipoMensaje = 'error';
    }
}

// Cargar precios desde precios_tipo
$precios = [];
foreach ($tipos as $clave => $nombre) {
    $stmt = $pdo->prepare('SELECT precio FROM precios_tipo WHERE tipo = ?');
    $stmt->execute([$clave]);
    $row = $stmt->fetch();
    $precios[$clave] = $row ? floatval($row['precio']) : 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Precios — Casa Vera</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="sidebar__logo">
            <h1>Casa Vera</h1>
            <p>Panel de Gestión</p>
        </div>
        <nav class="sidebar__nav">
            <a href="dashboard.php" class="sidebar__link">
                <span class="sidebar__icono">🏠</span> Dashboard
            </a>
            <a href="habitaciones.php" class="sidebar__link">
                <span class="sidebar__icono">🛏</span> Habitaciones
            </a>
            <a href="inquilinos.php" class="sidebar__link">
                <span class="sidebar__icono">👤</span> Inquilinos
            </a>
            <a href="contratos.php" class="sidebar__link">
                <span class="sidebar__icono">📋</span> Contratos
            </a>
            <a href="precios.php" class="sidebar__link activo">
                <span class="sidebar__icono">💰</span> Precios
            </a>
        </nav>
        <div class="sidebar__user">
            <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="sidebar__logout">Cerrar sesión</a>
        </div>
    </aside>

    <main class="main">
        <header class="main__header">
            <h2>Gestión de Precios</h2>
            <p>Actualiza el precio de un tipo y se aplica automáticamente a todas sus habitaciones y a la web pública.</p>
        </header>

        <?php if ($mensaje !== ''): ?>
            <div class="mensaje mensaje--<?php echo $tipoMensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="background:#fff;border-radius:12px;padding:8px;box-shadow:0 2px 12px rgba(0,0,0,0.05);">
            <table class="tabla-habitaciones">
                <thead>
                    <tr>
                        <th>Tipo de habitación</th>
                        <th>Habitaciones</th>
                        <th>Precio actual/mes</th>
                        <th>Nuevo precio</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tipos as $clave => $nombre):
                    // Contar cuántas habitaciones hay de este tipo
                    $stmtCount = $pdo->prepare('SELECT COUNT(*) FROM habitaciones WHERE tipo = ?');
                    $stmtCount->execute([$clave]);
                    $cantidad = $stmtCount->fetchColumn();
                ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($nombre); ?></strong></td>
                        <td style="color:var(--color-texto-claro)"><?php echo $cantidad; ?> hab.</td>
                        <td>
                            <span class="estado-badge" style="background:rgba(122,158,126,0.1);color:#437a22;">
                                <?php echo $precios[$clave] > 0 ? number_format($precios[$clave], 0, ',', '.') . ' €' : '—'; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display:flex;gap:8px;align-items:center;">
                                <input type="hidden" name="tipo" value="<?php echo $clave; ?>">
                                <input type="number"
                                       name="precio"
                                       value="<?php echo $precios[$clave] > 0 ? $precios[$clave] : ''; ?>"
                                       min="0" step="1" placeholder="0"
                                       style="width:100px;padding:8px 10px;border:1.5px solid #F2E8D9;border-radius:6px;font-family:Inter,sans-serif;font-size:0.9rem;">
                                <td><button type="submit" class="btn-guardar" style="white-space:nowrap;">Actualizar</button></td>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>