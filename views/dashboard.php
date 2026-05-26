<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Principal - ProQuaris</title>
    <link rel="stylesheet" href="../css/estilos-globales.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

    <div class="estructura-dashboard">
        
        <aside class="sidebar">
            <div class="logo-dashboard">ProQuaris</div>
            <div class="usuario-info">
                <p class="nombre-usuario">Juan Tamayo</p>
                <span class="rol-usuario">Administrador</span>
            </div>
            
            <nav class="menu-lateral">
                <a href="#" class="item-menu activo">Inicio (Resumen)</a>
                <a href="#" class="item-menu">Órdenes de Producción</a>
                <a href="#" class="item-menu">Lotes y Calidad</a>
                <a href="#" class="item-menu">Inventario Materia Prima</a>
                <a href="#" class="item-menu">Usuarios y Roles</a>
            </nav>

            <div class="sidebar-footer">
                <a href="login.php" class="btn-cerrar-sesion">Cerrar Sesión</a>
            </div>
        </aside>

        <main class="contenido-principal">
            
            <header class="topbar">
                <h1>Panel de Control Principal</h1>
                <div class="fecha-actual">Mayo, 2026</div>
            </header>

            <section class="tarjetas-resumen">
                <div class="tarjeta">
                    <h3>Órdenes Activas</h3>
                    <p class="numero">12</p>
                </div>
                <div class="tarjeta">
                    <h3>Lotes Producidos hoy</h3>
                    <p class="numero">45</p>
                </div>
                <div class="tarjeta alerta">
                    <h3>Alertas de Calidad</h3>
                    <p class="numero">3</p>
                </div>
            </section>

            <section class="seccion-datos">
                <h2>Últimos Lotes Verificados</h2>
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Código Lote</th>
                                <th>Fecha</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>LOT-2026-001</td>
                                <td>21/05/2026</td>
                                <td>500 uds</td>
                                <td><span class="tag aprobado">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td>LOT-2026-002</td>
                                <td>21/05/2026</td>
                                <td>350 uds</td>
                                <td><span class="tag rechazado">Rechazado</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

</body>
</html>