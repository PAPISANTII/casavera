<?php
require 'config.php';
requireAuth();

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $habitacion_id   = (int)($_POST['habitacion_id'] ?? 0);
        $inquilino_id    = (int)($_POST['inquilino_id'] ?? 0);
        $fecha_inicio    = trim($_POST['fecha_inicio'] ?? '');
        $fecha_fin       = trim($_POST['fecha_fin'] ?? '');
        $tipo_contrato   = trim($_POST['tipo_contrato'] ?? 'estandar');
        $precio_mensual  = (float)($_POST['precio_mensual'] ?? 0);
        $fianza          = (float)($_POST['fianza'] ?? 0);

        if ($habitacion_id > 0 && $inquilino_id > 0 && $fecha_inicio !== '' && $fecha_fin !== '') {
            $stmt = $pdo->prepare("
                INSERT INTO contratos (
                    habitacion_id, inquilino_id, fecha_inicio, fecha_fin, tipo_contrato, precio_mensual, fianza
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $habitacion_id,
                $inquilino_id,
                $fecha_inicio,
                $fecha_fin,
                $tipo_contrato,
                $precio_mensual,
                $fianza
            ]);
            // Cambiar estado habitación a 'ocupado' solo si el INSERT fue bien
            $stmtEstado = $pdo->prepare('UPDATE habitaciones SET estado = ? WHERE id = ?');
            $stmtEstado->execute(['ocupado', $habitacion_id]);

            $mensaje = 'Contrato creado correctamente.';
            $tipo_mensaje = 'ok';
        }
    }

    if ($accion === 'editar') {
        $id              = (int)($_POST['id'] ?? 0);
        $habitacion_id   = (int)($_POST['habitacion_id'] ?? 0);
        $inquilino_id    = (int)($_POST['inquilino_id'] ?? 0);
        $fecha_inicio    = trim($_POST['fecha_inicio'] ?? '');
        $fecha_fin       = trim($_POST['fecha_fin'] ?? '');
        $tipo_contrato   = trim($_POST['tipo_contrato'] ?? 'estandar');
        $precio_mensual  = (float)($_POST['precio_mensual'] ?? 0);
        $fianza          = (float)($_POST['fianza'] ?? 0);

        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE contratos
                SET habitacion_id = ?, inquilino_id = ?, fecha_inicio = ?, fecha_fin = ?, tipo_contrato = ?, precio_mensual = ?, fianza = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $habitacion_id,
                $inquilino_id,
                $fecha_inicio,
                $fecha_fin,
                $tipo_contrato,
                $precio_mensual,
                $fianza,
                $id
            ]);

            $stmtEstado = $pdo->prepare('UPDATE habitaciones SET estado = ? WHERE id = ?');
            $stmtEstado->execute(['ocupado', $habitacion_id]);

            $mensaje = 'Contrato actualizado correctamente.';
            $tipo_mensaje = 'ok';
        }
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            // Antes de borrar, recuperar la habitación_id
            $stmtHab = $pdo->prepare('SELECT habitacion_id FROM contratos WHERE id = ?');
            $stmtHab->execute([$id]);
            $hab = $stmtHab->fetch();

            $stmt = $pdo->prepare("DELETE FROM contratos WHERE id = ?");
            $stmt->execute([$id]);

            // Devolver habitación a disponible
            if ($hab) {
                $stmtEstado = $pdo->prepare('UPDATE habitaciones SET estado = ? WHERE id = ?');
                $stmtEstado->execute(['disponible', $hab['habitacion_id']]);
            }

            $mensaje = 'Contrato eliminado correctamente.';
            $tipo_mensaje = 'ok';
        }
    }
}

$stmt = $pdo->query("
    SELECT 
        c.id,
        c.habitacion_id,
        c.inquilino_id,
        c.fecha_inicio,
        c.fecha_fin,
        c.tipo_contrato,
        c.precio_mensual,
        c.fianza,
        h.numero AS habitacion_numero,
        h.planta AS habitacion_planta,
        i.nombre,
        i.apellidos
    FROM contratos c
    LEFT JOIN habitaciones h ON c.habitacion_id = h.id
    LEFT JOIN inquilinos i ON c.inquilino_id = i.id
    ORDER BY c.fecha_inicio DESC, c.id DESC
");
$contratos = $stmt->fetchAll();

$stmtHabitaciones = $pdo->query("SELECT id, numero, planta FROM habitaciones ORDER BY planta, numero");
$habitaciones = $stmtHabitaciones->fetchAll();

$stmtInquilinos = $pdo->query("SELECT id, nombre, apellidos FROM inquilinos ORDER BY nombre, apellidos");
$inquilinos = $stmtInquilinos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Contratos — Casa Vera</title>
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
                <a href="contratos.php" class="sidebar__link activo">
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
                <h2>Gestión de Contratos</h2>
                <p>Alta, edición y control de contratos de alquiler</p>
            </header>

            <?php if ($mensaje !== ''): ?>
                <div class="mensaje <?php echo $tipo_mensaje === 'ok' ? 'mensaje--ok' : 'mensaje--error'; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            <br>
            <div class="acciones-superior">
                <button type="button" class="btn-editar" id="btn-nuevo-contrato">+ Nuevo contrato</button>
            </div>
            <br>

            <div class="card">
                <table class="tabla-habitaciones">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Inquilino</th>
                            <th>Habitación</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Fianza</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contratos)): ?>
                            <?php foreach ($contratos as $c): ?>
                                <tr>
                                    <td><?php echo (int)$c['id']; ?></td>
                                    <td><?php echo htmlspecialchars(trim(($c['nombre'] ?? '') . ' ' . ($c['apellidos'] ?? '')) ?: '—'); ?></td>
                                    <td>
                                        <?php
                                        echo $c['habitacion_numero']
                                            ? 'Hab. ' . (int)$c['habitacion_numero'] . ' (Planta ' . (int)$c['habitacion_planta'] . ')'
                                            : '—';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($c['fecha_inicio'] ?: '—'); ?></td>
                                    <td><?php echo htmlspecialchars($c['fecha_fin'] ?: '—'); ?></td>
                                    <td><?php echo htmlspecialchars($c['tipo_contrato'] ?: '—'); ?></td>
                                    <td><?php echo $c['precio_mensual'] > 0 ? number_format((float)$c['precio_mensual'], 2, ',', '.') . ' €' : '—'; ?></td>
                                    <td><?php echo $c['fianza'] > 0 ? number_format((float)$c['fianza'], 2, ',', '.') . ' €' : '—'; ?></td>
                                    <td>
                                        <div class="acciones-tabla">
                                            <button
                                                type="button"
                                                class="btn-editar btn-contrato-editar"
                                                data-id="<?php echo (int)$c['id']; ?>"
                                                data-habitacion_id="<?php echo (int)$c['habitacion_id']; ?>"
                                                data-inquilino_id="<?php echo (int)$c['inquilino_id']; ?>"
                                                data-fecha_inicio="<?php echo htmlspecialchars($c['fecha_inicio']); ?>"
                                                data-fecha_fin="<?php echo htmlspecialchars($c['fecha_fin']); ?>"
                                                data-tipo_contrato="<?php echo htmlspecialchars($c['tipo_contrato']); ?>"
                                                data-precio_mensual="<?php echo (float)$c['precio_mensual']; ?>"
                                                data-fianza="<?php echo (float)$c['fianza']; ?>">
                                                Editar
                                            </button>
                                            <a href="api/generar-contrato.php?id=<?= $c['id'] ?>" class="btn-pdf" target="_blank">Contrato</a>
                                            <a href="api/generar-ficha-residente.php?id=<?= $c['id'] ?>" class="btn-pdf btn-pdf--residente" target="_blank">Residente</a>
                                            <a href="api/generar-deposit-receipt.php?id=<?= $c['id'] ?>" class="btn-pdf btn-pdf--en" target="_blank">Receipt</a>
                                            <a href="api/generar-recibo-fianza.php?id=<?= $c['id'] ?>" class="btn-pdf btn-pdf--fianza" target="_blank">Recibo</a>

                                            <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este contrato?');" style="display:inline;">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                                <button type="submit" class="btn-cancelar">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">No hay contratos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="modal-contrato" class="modal">
        <div class="modal__contenido">
            <button class="modal__cerrar" id="cerrar-modal-contrato">✕</button>
            <h3 id="titulo-modal-contrato">Nuevo contrato</h3>

            <form method="POST">
                <input type="hidden" name="accion" id="contrato-accion" value="crear">
                <input type="hidden" name="id" id="contrato-id">

                <div class="form-row">
                    <div class="form-group">
                        <label for="contrato-inquilino">Inquilino</label>
                        <select name="inquilino_id" id="contrato-inquilino" required>
                            <option value="">Selecciona un inquilino</option>
                            <?php foreach ($inquilinos as $i): ?>
                                <option value="<?php echo (int)$i['id']; ?>">
                                    <?php echo htmlspecialchars($i['nombre'] . ' ' . $i['apellidos']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contrato-habitacion">Habitación</label>
                        <select name="habitacion_id" id="contrato-habitacion" required>
                            <option value="">Selecciona una habitación</option>
                            <?php foreach ($habitaciones as $h): ?>
                                <option value="<?php echo (int)$h['id']; ?>">
                                    <?php echo 'Hab. ' . (int)$h['numero'] . ' — Planta ' . (int)$h['planta']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contrato-fecha-inicio">Fecha de inicio</label>
                        <input type="date" name="fecha_inicio" id="contrato-fecha-inicio" required>
                    </div>

                    <div class="form-group">
                        <label for="contrato-fecha-fin">Fecha de fin</label>
                        <input type="date" name="fecha_fin" id="contrato-fecha-fin" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contrato-tipo">Tipo de contrato</label>
                        <input type="text" name="tipo_contrato" value="Estandar" readonly>
                    </div>

                    <div class="form-group">
                        <label for="contrato-precio">Precio mensual (€)</label>
                        <input type="number" name="precio_mensual" id="contrato-precio" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contrato-fianza">Fianza (€)</label>
                    <input type="number" name="fianza" id="contrato-fianza" step="0.01" min="0" required>
                </div>

                <div class="form-buttons">
                    <button type="button" class="btn-cancelar" id="cancelar-modal-contrato">Cancelar</button>
                    <button type="submit" class="btn-guardar">Guardar contrato</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modalContrato = document.getElementById('modal-contrato');
        const btnNuevoContrato = document.getElementById('btn-nuevo-contrato');
        const cerrarModalContrato = document.getElementById('cerrar-modal-contrato');
        const cancelarModalContrato = document.getElementById('cancelar-modal-contrato');
        const tituloModalContrato = document.getElementById('titulo-modal-contrato');

        const contratoAccion = document.getElementById('contrato-accion');
        const contratoId = document.getElementById('contrato-id');
        const contratoHabitacion = document.getElementById('contrato-habitacion');
        const contratoInquilino = document.getElementById('contrato-inquilino');
        const contratoFechaInicio = document.getElementById('contrato-fecha-inicio');
        const contratoFechaFin = document.getElementById('contrato-fecha-fin');
        const contratoPrecio = document.getElementById('contrato-precio');
        const contratoFianza = document.getElementById('contrato-fianza');

        function abrirModalNuevoContrato() {
            tituloModalContrato.textContent = 'Nuevo contrato';
            contratoAccion.value = 'crear';
            contratoId.value = '';
            contratoHabitacion.value = '';
            contratoInquilino.value = '';
            contratoFechaInicio.value = '';
            contratoFechaFin.value = '';
            contratoPrecio.value = '';
            contratoFianza.value = '';
            modalContrato.classList.add('activo');
        }

        function cerrarModalContratoFn() {
            modalContrato.classList.remove('activo');
        }

        btnNuevoContrato.addEventListener('click', abrirModalNuevoContrato);
        cerrarModalContrato.addEventListener('click', cerrarModalContratoFn);
        cancelarModalContrato.addEventListener('click', cerrarModalContratoFn);

        modalContrato.addEventListener('click', (e) => {
            if (e.target === modalContrato) {
                cerrarModalContratoFn();
            }
        });

        document.querySelectorAll('.btn-contrato-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                tituloModalContrato.textContent = 'Editar contrato';
                contratoAccion.value = 'editar';
                contratoId.value = btn.dataset.id;
                contratoHabitacion.value = btn.dataset.habitacion_id;
                contratoInquilino.value = btn.dataset.inquilino_id;
                contratoFechaInicio.value = btn.dataset.fecha_inicio;
                contratoFechaFin.value = btn.dataset.fecha_fin;
                contratoPrecio.value = btn.dataset.precio_mensual;
                contratoFianza.value = btn.dataset.fianza;
                modalContrato.classList.add('activo');
            });
        });

        // Autocompletar precio y fianza al seleccionar habitación
        contratoHabitacion.addEventListener('change', () => {
            const habId = contratoHabitacion.value;
            if (!habId) return;

            fetch('api/habitaciones.php?accion=precio_por_id&id=' + habId)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.precio > 0) {
                        contratoPrecio.value = data.precio;
                        contratoFianza.value = Math.round(data.precio / 2);
                    }
                });
        });

        // Autocompletar habitación al seleccionar inquilino
        contratoInquilino.addEventListener('change', () => {
            const inquilinoId = contratoInquilino.value;
            if (!inquilinoId) return;
            fetch('api/habitaciones.php?accion=habitacion_por_inquilino&inquilino_id=' + inquilinoId)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.habitacion_id) {
                        contratoHabitacion.value = data.habitacion_id;
                        // Dispara el change para autocompletar precio y fianza también
                        contratoHabitacion.dispatchEvent(new Event('change'));
                    }
                });
        });
    </script>
</body>
</html>