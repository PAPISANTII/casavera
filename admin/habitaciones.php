<?php
require 'config.php';
requireAuth();

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar') {
        $id     = intval(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT));
        $tipo   = trim($_POST['tipo'] ?? '');
        $estado = $_POST['estado'] ?? 'disponible';

        // Lee el precio de la tabla precios_tipo
        $stmtPrecio = $pdo->prepare('SELECT precio FROM precios_tipo WHERE tipo = ?');
        $stmtPrecio->execute([$tipo]);
        $rowPrecio = $stmtPrecio->fetch();
        $precio = $rowPrecio ? floatval($rowPrecio['precio']) : 0;

        $stmt = $pdo->prepare('UPDATE habitaciones SET tipo = ?, precio = ?, estado = ? WHERE id = ?');
        $stmt->execute([$tipo, $precio, $estado, $id]);
        $mensaje = 'Habitación guardada correctamente';
        $tipoMensaje = 'ok';
    }
}

$stmt = $pdo->query('SELECT * FROM habitaciones ORDER BY planta, numero');
$habitaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Habitaciones — Casa Vera</title>
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
                <a href="habitaciones.php" class="sidebar__link activo">
                    <span class="sidebar__icono">🛏</span> Habitaciones
                </a>
                <a href="inquilinos.php" class="sidebar__link">
                    <span class="sidebar__icono">👤</span> Inquilinos
                </a>
                <a href="contratos.php" class="sidebar__link">
                    <span class="sidebar__icono">📋</span> Contratos
                </a>
                <a href="precios.php" class="sidebar__link">
                    <span class="sidebar__icono">💰</span> Precios
                </a>
            </nav>
            <div class="sidebar__user">
                <span>👤 <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="sidebar__logout">Cerrar sesión</a>
            </div>
        </aside>

        <main class="main">
            <header class="main__header">
                <h2>Gestión de Habitaciones</h2>
                <p>Edita el tipo, precio y estado de cada habitación</p>
            </header>

            <?php if ($mensaje !== ''): ?>
                <div class="mensaje <?php echo $tipoMensaje === 'ok' ? 'mensaje--ok' : 'mensaje--error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
                        <table class="tabla-habitaciones">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Planta</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($habitaciones as $h): ?>
                    <tr>
                        <td><strong><?php echo (int)$h['numero']; ?></strong></td>
                        <td><?php echo (int)$h['planta']; ?></td>
                        <td><?php echo htmlspecialchars($h['tipo'] ?: '—'); ?></td>
                        <td><?php echo $h['precio'] > 0 ? $h['precio'] . ' €' : '—'; ?></td>
                        <td>
                            <span class="estado-badge estado--<?php echo $h['estado']; ?>">
                                <?php echo ucfirst($h['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn-editar"
                                    data-id="<?php echo (int)$h['id']; ?>"
                                    data-num="<?php echo (int)$h['numero']; ?>"
                                    data-tipo="<?php echo htmlspecialchars($h['tipo']); ?>"
                                    data-precio="<?php echo (float)$h['precio']; ?>"
                                    data-estado="<?php echo $h['estado']; ?>">
                                Editar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal editar -->
    <div id="modal-editar" class="modal">
        <div class="modal__contenido">
            <button class="modal__cerrar" id="modal-cerrar-btn">✕</button>
            <h3 id="modal-titulo-edit">Editar habitación</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" id="edit-id" name="id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-tipo">Tipo de habitación</label>
                        <select id="edit-tipo" name="tipo">
                            <option value="">— Sin asignar —</option>
                            <option value="individual_balcon_banio_interior">Individual con balcón y baño interior</option>
                            <option value="individual_banio_interior">Individual con baño interior</option>
                            <option value="individual_balcon_banio_exterior">Individual con balcón y baño exterior</option>
                            <option value="individual_banio_exterior">Individual con baño exterior</option>
                            <option value="individual_planta_baja">Individual en planta baja</option>
                            <option value="doble_banio_interior">Doble con baño interior</option>
                            <option value="doble_balcon_banio_exterior">Doble con balcón y baño exterior</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-estado">Estado</label>
                    <select id="edit-estado" name="estado">
                        <option value="disponible">Disponible</option>
                        <option value="reservado">Reservado</option>
                        <option value="ocupado">Ocupado</option>
                    </select>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn-cancelar" id="btn-cancelar">Cancelar</button>
                    <button type="submit" class="btn-guardar">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modal-editar');
        const cerrarBtn = document.getElementById('modal-cerrar-btn');
        const cancelarBtn = document.getElementById('btn-cancelar');
        const tituloModal = document.getElementById('modal-titulo-edit');
        const inputId = document.getElementById('edit-id');
        const inputTipo = document.getElementById('edit-tipo');
        const inputPrecio = document.getElementById('edit-precio');
        const inputEstado = document.getElementById('edit-estado');

        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                tituloModal.textContent = 'Editar habitación ' + btn.dataset.num;
                inputId.value = btn.dataset.id;
                inputTipo.value = btn.dataset.tipo;
                inputEstado.value = btn.dataset.estado;
                modal.classList.add('activo');
            });
        });

        cerrarBtn.addEventListener('click', () => { modal.classList.remove('activo'); });
        cancelarBtn.addEventListener('click', () => { modal.classList.remove('activo'); });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) { modal.classList.remove('activo'); }
        });
    </script>
</body>
</html>