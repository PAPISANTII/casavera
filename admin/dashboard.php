<?php
require 'config.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gestión — Casa Vera</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <script src="admin.js"></script>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar__logo">
                <h1>Casa Vera</h1>
                <p>Panel de Gestión</p>
            </div>
            <nav class="sidebar__nav">
                <a href="dashboard.php" class="sidebar__link activo">
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
                <h2>Plano de Habitaciones</h2>
                <p>Seleccione una planta para ver el estado de las habitaciones</p>
            </header>

            <div class="planta-selector">
                <button class="planta-btn" data-planta="0">Planta Baja (0)</button>
                <button class="planta-btn activo" data-planta="1">Planta 1</button>
                <button class="planta-btn" data-planta="2">Planta 2</button>
                <button class="planta-btn" data-planta="3">Planta 3</button>
            </div>

            <div class="leyenda">
                <span class="leyenda__item">
                    <span class="leyenda__color leyenda__color--disponible"></span>Disponible
                </span>
                <span class="leyenda__item">
                    <span class="leyenda__color leyenda__color--reservado"></span>Reservado
                </span>
                <span class="leyenda__item">
                    <span class="leyenda__color leyenda__color--ocupado"></span>Ocupado
                </span>
            </div>

            <div class="plano-contenedor">
                <!-- Planta 0: habitaciones 01, 02, 03 -->
                <svg id="plano-0" class="plano-svg" viewBox="0 0 400 200" style="display:none;">
                    <rect x="20" y="40" width="100" height="120" class="habitacion-svg" data-num="1"/>
                    <text x="70" y="105" class="hab-num">1</text>
                    <rect x="150" y="40" width="100" height="120" class="habitacion-svg" data-num="2"/>
                    <text x="200" y="105" class="hab-num">2</text>
                    <rect x="280" y="40" width="100" height="120" class="habitacion-svg" data-num="3"/>
                    <text x="330" y="105" class="hab-num">3</text>
                </svg>

                <!-- Planta 1: habitaciones 11-16 -->
                <svg id="plano-1" class="plano-svg" viewBox="0 0 400 300" style="display:none;">
                    <rect x="20" y="30" width="110" height="100" class="habitacion-svg" data-num="11"/>
                    <text x="75" y="85" class="hab-num">11</text>
                    <rect x="145" y="30" width="110" height="100" class="habitacion-svg" data-num="12"/>
                    <text x="200" y="85" class="hab-num">12</text>
                    <rect x="270" y="30" width="110" height="100" class="habitacion-svg" data-num="13"/>
                    <text x="325" y="85" class="hab-num">13</text>
                    <rect x="20" y="160" width="110" height="100" class="habitacion-svg" data-num="14"/>
                    <text x="75" y="215" class="hab-num">14</text>
                    <rect x="145" y="160" width="110" height="100" class="habitacion-svg" data-num="15"/>
                    <text x="200" y="215" class="hab-num">15</text>
                    <rect x="270" y="160" width="110" height="100" class="habitacion-svg" data-num="16"/>
                    <text x="325" y="215" class="hab-num">16</text>
                </svg>

                <!-- Planta 2: habitaciones 21-26 -->
                <svg id="plano-2" class="plano-svg" viewBox="0 0 400 300" style="display:none;">
                    <rect x="20" y="30" width="110" height="100" class="habitacion-svg" data-num="21"/>
                    <text x="75" y="85" class="hab-num">21</text>
                    <rect x="145" y="30" width="110" height="100" class="habitacion-svg" data-num="22"/>
                    <text x="200" y="85" class="hab-num">22</text>
                    <rect x="270" y="30" width="110" height="100" class="habitacion-svg" data-num="23"/>
                    <text x="325" y="85" class="hab-num">23</text>
                    <rect x="20" y="160" width="110" height="100" class="habitacion-svg" data-num="24"/>
                    <text x="75" y="215" class="hab-num">24</text>
                    <rect x="145" y="160" width="110" height="100" class="habitacion-svg" data-num="25"/>
                    <text x="200" y="215" class="hab-num">25</text>
                    <rect x="270" y="160" width="110" height="100" class="habitacion-svg" data-num="26"/>
                    <text x="325" y="215" class="hab-num">26</text>
                </svg>

                <!-- Planta 3: habitaciones 31-35 -->
                <svg id="plano-3" class="plano-svg" viewBox="0 0 400 300" style="display:none;">
                    <rect x="20" y="30" width="110" height="100" class="habitacion-svg" data-num="31"/>
                    <text x="75" y="85" class="hab-num">31</text>
                    <rect x="145" y="30" width="110" height="100" class="habitacion-svg" data-num="32"/>
                    <text x="200" y="85" class="hab-num">32</text>
                    <rect x="270" y="30" width="110" height="100" class="habitacion-svg" data-num="33"/>
                    <text x="325" y="85" class="hab-num">33</text>
                    <rect x="80" y="160" width="110" height="100" class="habitacion-svg" data-num="34"/>
                    <text x="135" y="215" class="hab-num">34</text>
                    <rect x="210" y="160" width="110" height="100" class="habitacion-svg" data-num="35"/>
                    <text x="265" y="215" class="hab-num">35</text>
                </svg>
            </div>

            <div id="modal" class="modal">
                <div class="modal__contenido">
                    <button class="modal__cerrar" id="modal-cerrar">✕</button>
                    <h3 id="modal-titulo">Habitación</h3>
                    <div class="modal__estado" id="modal-estado"></div>
                    <div class="modal__info" id="modal-info"></div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>