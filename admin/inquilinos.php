<?php
require 'config.php';
requireAuth();

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar') {
        $id           = (int) filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $nombre       = trim($_POST['nombre'] ?? '');
        $apellidos    = trim($_POST['apellidos'] ?? '');
        $nacionalidad = trim($_POST['nacionalidad'] ?? '');
        $documento    = trim($_POST['documento'] ?? '');
        $direccion    = trim($_POST['direccion'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $telefono     = trim($_POST['telefono'] ?? '');

        if ($id > 0) {
            $stmt = $pdo->prepare('
                UPDATE inquilinos SET nombre = ?, apellidos = ?, nacionalidad = ?,
                documento = ?, direccion = ?, email = ?, telefono = ? WHERE id = ?
            ');
            $stmt->execute([$nombre, $apellidos, $nacionalidad, $documento, $direccion, $email, $telefono, $id]);
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO inquilinos (nombre, apellidos, nacionalidad, documento, direccion, email, telefono)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$nombre, $apellidos, $nacionalidad, $documento, $direccion, $email, $telefono]);
        }
        $mensaje = 'Inquilino guardado correctamente';
        $tipo_mensaje = 'ok';
    }
    if ($accion === 'eliminar') {
    $id = intval(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT));

    // Comprobar si tiene contratos activos
    $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM contratos WHERE inquilino_id = ?');
    $stmtCheck->execute([$id]);
    $tieneContratos = $stmtCheck->fetchColumn();

    if ($tieneContratos > 0) {
        $mensaje = 'No se puede eliminar: este inquilino tiene contratos asociados.';
        $tipo_mensaje = 'error';
    } else {
        $stmt = $pdo->prepare('DELETE FROM inquilinos WHERE id = ?');
        $stmt->execute([$id]);
        $mensaje = 'Inquilino eliminado correctamente.';
        $tipo_mensaje = 'ok';
    }
}
}

$stmt = $pdo->query('SELECT * FROM inquilinos ORDER BY apellidos, nombre');
$inquilinos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inquilinos — Casa Vera</title>
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
                <a href="inquilinos.php" class="sidebar__link activo">
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
                <h2>Gestión de Inquilinos</h2>
                <p>Dar de alta, editar o eliminar inquilinos</p>
                <br>
            </header>

            <?php if ($mensaje !== ''): ?>
                <div class="mensaje <?php echo $tipo_mensaje === 'ok' ? 'mensaje--ok' : 'mensaje--error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <div class="inquilinos-header">
                <button type="button" class="btn-nuevo" id="btn-nuevo">+ Nuevo inquilino</button>
            </div>
                        <table class="tabla-habitaciones">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Nacionalidad</th>
                        <th>Documento</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquilinos as $i): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($i['nombre'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($i['apellidos'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($i['nacionalidad'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($i['documento'] ?: '—'); ?></td>
                        <td><?php echo $i['email'] ? '<a href="mailto:'.$i['email'].'">'.htmlspecialchars($i['email']).'</a>' : '—'; ?></td>
                        <td><?php echo htmlspecialchars($i['telefono'] ?: '—'); ?></td>
                        <td>
                            <button type="button" class="btn-editar"
                                    data-id="<?php echo (int)$i['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($i['nombre']); ?>"
                                    data-apellidos="<?php echo htmlspecialchars($i['apellidos']); ?>"
                                    data-nacionalidad="<?php echo htmlspecialchars($i['nacionalidad']); ?>"
                                    data-documento="<?php echo htmlspecialchars($i['documento']); ?>"
                                    data-direccion="<?php echo htmlspecialchars($i['direccion']); ?>"
                                    data-email="<?php echo htmlspecialchars($i['email']); ?>"
                                    data-telefono="<?php echo htmlspecialchars($i['telefono']); ?>">
                                Editar
                            </button>
                            <button type="button" class="btn-eliminar" data-id="<?php echo intval($i['id']); ?>" data-nombre="<?php echo htmlspecialchars($i['nombre'] . ' ' . $i['apellidos']); ?>">Eliminar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($inquilinos)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--color-texto-claro);padding:24px;">No hay inquilinos registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal editar inquilino -->
    <div id="modal-editar" class="modal">
        <div class="modal__contenido">
            <button class="modal__cerrar" id="modal-cerrar-btn">✕</button>
            <h3 id="modal-titulo">Nuevo inquilino</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" id="edit-id" name="id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-nombre">Nombre</label>
                        <input type="text" id="edit-nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-apellidos">Apellidos</label>
                        <input type="text" id="edit-apellidos" name="apellidos" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-nacionalidad">Nacionalidad</label>
                        <input type="text" id="edit-nacionalidad" name="nacionalidad">
                    </div>
                    <div class="form-group">
                        <label for="edit-documento">Pasaporte / DNI</label>
                        <input type="text" id="edit-documento" name="documento">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-direccion">Dirección</label>
                    <input type="text" id="edit-direccion" name="direccion">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-email">Email</label>
                        <input type="email" id="edit-email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="edit-telefono">Teléfono</label>
                        <input type="text" id="edit-telefono" name="telefono">
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn-cancelar" id="btn-cancelar">Cancelar</button>
                    <button type="submit" class="btn-guardar">Guardar inquilino</button>
                </div>
            </form>
        </div>
    </div>
        <!-- Formulario oculto para eliminar -->
    <form id="form-eliminar" method="POST" style="display:none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" id="eliminar-id">
    </form>

    <script>
        const modal = document.getElementById('modal-editar');
        const cerrarBtn = document.getElementById('modal-cerrar-btn');
        const cancelarBtn = document.getElementById('btn-cancelar');
        const btnNuevo = document.getElementById('btn-nuevo');
        const tituloModal = document.getElementById('modal-titulo');
        const inputId = document.getElementById('edit-id');
        const inputNombre = document.getElementById('edit-nombre');
        const inputApellidos = document.getElementById('edit-apellidos');
        const inputNac = document.getElementById('edit-nacionalidad');
        const inputDoc = document.getElementById('edit-documento');
        const inputDir = document.getElementById('edit-direccion');
        const inputEmail = document.getElementById('edit-email');
        const inputTel = document.getElementById('edit-telefono');

        btnNuevo.addEventListener('click', () => {
            tituloModal.textContent = 'Nuevo inquilino';
            inputId.value = '';
            inputNombre.value = '';
            inputApellidos.value = '';
            inputNac.value = '';
            inputDoc.value = '';
            inputDir.value = '';
            inputEmail.value = '';
            inputTel.value = '';
            modal.classList.add('activo');
        });

        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                tituloModal.textContent = 'Editar inquilino';
                inputId.value = btn.dataset.id;
                inputNombre.value = btn.dataset.nombre;
                inputApellidos.value = btn.dataset.apellidos;
                inputNac.value = btn.dataset.nacionalidad;
                inputDoc.value = btn.dataset.documento;
                inputDir.value = btn.dataset.direccion;
                inputEmail.value = btn.dataset.email;
                inputTel.value = btn.dataset.telefono;
                modal.classList.add('activo');
            });
        });

        cerrarBtn.addEventListener('click', () => { modal.classList.remove('activo'); });
        cancelarBtn.addEventListener('click', () => { modal.classList.remove('activo'); });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) { modal.classList.remove('activo'); }
        });
        //eliminar
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', () => {
            const nombre = btn.dataset.nombre;
            if (confirm('¿Seguro que quieres eliminar a ' + nombre + '? Esta acción no se puede deshacer.')) {
                document.getElementById('eliminar-id').value = btn.dataset.id;
                document.getElementById('form-eliminar').submit();
            }
        });
    });
    </script>
</body>
</html>