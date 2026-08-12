<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}
$producto = $producto ?? null;
$insumos = $insumos ?? [];
$action = $action ?? "/ProQuaris/controllers/ProductoController.php?accion=crear";

$envase_opciones = ['Unidades', 'Frascos', 'Tubos', 'Paquetes', 'Cajas', 'Rollo'];
$unidad_contenido_opciones = ['ml', 'L', 'g', 'kg', 'mg', 'unidades'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $producto ? 'Editar Producto' : 'Registrar Producto'; ?> - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="dashboard-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1><?php echo $producto ? 'Editar Producto e Insumos' : 'Registrar Producto, Insumos y Plusvalía'; ?></h1>
                <p>Control exacto de envases, contenidos y unidades de medida</p>
            </div>
            <a href="/ProQuaris/controllers/ProductoController.php?accion=listar" style="padding:10px 20px; background:#475569; color:white; border-radius:8px; text-decoration:none; font-weight:500;">← Volver al listado</a>
        </div>

        <div class="table-container" style="max-width: 850px; padding: 25px; margin-top: 20px;">
            <form action="<?php echo $action; ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
                
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Nombre del Producto:</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre'] ?? ''); ?>" required placeholder="Ej: Pestañas Acrílicas" style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: white;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Descripción:</label>
                    <textarea name="descripcion" placeholder="Detalles generales..." style="height: 60px; padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: white; outline: none;"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Plusvalía / Margen (COP):</label>
                        <input type="number" step="1" min="0" id="plusvalia" name="plusvalia" value="<?php echo round($producto['plusvalia'] ?? 0); ?>" placeholder="Ej: 40000" style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: white;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Precio Final de Venta (COP):</label>
                        <input type="number" step="1" id="precioVenta" name="precioVenta" value="<?php echo round($producto['precioVenta'] ?? 0); ?>" readonly required placeholder="Automático" style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #1E293B; color: #34D399; font-weight: bold; cursor: not-allowed;">
                    </div>
                </div>

                <div style="border-top: 1px solid #334155; padding-top: 15px; margin-top: 5px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h3 style="font-size: 15px; color: #F8FAFC;">Insumos y Gastos de Materia Prima</h3>
                        <button type="button" id="agregarInsumo" style="padding: 6px 12px; background: #3B82F6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;">+ Agregar Insumo</button>
                    </div>

                    <div id="contenedorInsumos" style="display: flex; flex-direction: column; gap: 10px;">
                        <?php if (!empty($insumos)): ?>
                            <?php foreach ($insumos as $index => $ins): 
                                $uA = strtolower(trim($ins['unidad'] ?? 'unidades'));
                                $uContenidoActual = strtolower(trim($ins['unidad_contenido'] ?? 'unidades'));
                                $cantidadEntera = max(1, (int)round($ins['cantidadRequerida']));
                                $costoInsumo = round($ins['costoInsumo']);
                                $precioUnitario = $cantidadEntera > 0 ? ($costoInsumo / $cantidadEntera) : $costoInsumo;
                            ?>
                                <div class="insumo-row" data-unit-price="<?php echo $precioUnitario; ?>" style="display: flex; flex-direction: column; gap: 8px; background: #0F172A; padding: 12px; border-radius: 8px; border: 1px solid #334155;">
                                    <div style="display: grid; grid-template-columns: 2fr 1fr 1.2fr 1fr auto; gap: 10px; align-items: center;">
                                        <input type="text" name="insumos[<?php echo $index; ?>][nombre]" value="<?php echo htmlspecialchars($ins['insumo_nombre']); ?>" placeholder="Ej: Pegante" required style="padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                                        
                                        <input type="number" step="1" min="1" name="insumos[<?php echo $index; ?>][cantidad]" value="<?php echo $cantidadEntera; ?>" placeholder="Cant." class="input-cantidad" required style="padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                                        
                                        <select name="insumos[<?php echo $index; ?>][unidad]" class="select-unidad" style="padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                                            <?php foreach($envase_opciones as $env): ?>
                                                <option value="<?php echo $env; ?>" <?php echo ($uA === strtolower($env)) ? 'selected' : ''; ?>><?php echo $env; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                        <input type="number" step="1" min="0" name="insumos[<?php echo $index; ?>][costo]" value="<?php echo $costoInsumo; ?>" placeholder="Costo ($)" class="input-costo" required style="padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                                        <button type="button" class="btn-eliminar" style="background: #EF4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;">✕</button>
                                    </div>
                                    <div class="detalle-container" style="display: flex; gap: 10px; align-items: center; padding-left: 2px;">
                                        <span style="font-size: 12px; color: #94A3B8;">Contenido exacto por envase:</span>
                                        <input type="number" step="any" min="0" name="insumos[<?php echo $index; ?>][cantidad_por_empaque]" value="<?php echo $ins['cantidad_por_empaque'] ?? 1; ?>" style="width: 100px; padding: 6px; background: #1E293B; border: 1px solid #475569; color: #38BDF8; border-radius: 6px;">
                                        <select name="insumos[<?php echo $index; ?>][unidad_contenido]" style="padding: 6px; background: #1E293B; border: 1px solid #475569; color: #38BDF8; border-radius: 6px;">
                                            <?php foreach($unidad_contenido_opciones as $uc): ?>
                                                <option value="<?php echo $uc; ?>" <?php echo ($uContenidoActual === strtolower($uc)) ? 'selected' : ''; ?>><?php echo $uc; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="submit" style="width: 100%; padding: 12px; background: #6366F1; border: none; color: white; font-weight: bold; border-radius: 8px; cursor: pointer;">Guardar Producto</button>
            </form>
        </div>
    </main>
</div>

<script>
    function calcularTotales() {
        let costoTotalInsumos = 0;
        document.querySelectorAll('.insumo-row').forEach(row => {
            costoTotalInsumos += parseFloat(row.querySelector('.input-costo').value) || 0;
        });
        const plusvalia = parseFloat(document.getElementById('plusvalia').value) || 0;
        document.getElementById('precioVenta').value = Math.round(costoTotalInsumos + plusvalia);
    }

    // Lógica para multiplicar cantidad por valor unitario automáticamente
    document.addEventListener('input', (e) => {
        const row = e.target.closest('.insumo-row');
        
        if (row && (e.target.classList.contains('input-cantidad') || e.target.classList.contains('input-costo'))) {
            const inputCantidad = row.querySelector('.input-cantidad');
            const inputCosto = row.querySelector('.input-costo');
            
            let cantidad = parseFloat(inputCantidad.value) || 0;
            let costoTotal = parseFloat(inputCosto.value) || 0;

            if (e.target.classList.contains('input-cantidad')) {
                // Si cambia la cantidad, calculamos el costo usando el precio unitario guardado
                let unitPrice = parseFloat(row.dataset.unitPrice) || (cantidad > 0 ? costoTotal / cantidad : 0);
                if (cantidad > 0 && unitPrice > 0) {
                    inputCosto.value = Math.round(cantidad * unitPrice);
                }
            } else if (e.target.classList.contains('input-costo')) {
                // Si el usuario cambia el costo directamente, actualizamos el precio unitario base
                if (cantidad > 0) {
                    row.dataset.unitPrice = costoTotal / cantidad;
                }
            }
            calcularTotales();
        } else if (e.target.id === 'plusvalia') {
            calcularTotales();
        }
    });

    let contador = document.querySelectorAll('.insumo-row').length;
    document.getElementById('agregarInsumo').addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'insumo-row';
        div.dataset.unitPrice = "0";
        div.style = "display: flex; flex-direction: column; gap: 8px; background: #0F172A; padding: 12px; border-radius: 8px; border: 1px solid #334155; margin-top: 10px;";
        div.innerHTML = `
            <div style="display: grid; grid-template-columns: 2fr 1fr 1.2fr 1fr auto; gap: 10px; align-items: center;">
                <input type="text" name="insumos[${contador}][nombre]" placeholder="Nombre" required style="padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                <input type="number" step="1" min="1" name="insumos[${contador}][cantidad]" value="1" placeholder="Cant." required style="padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;" class="input-cantidad">
                <select name="insumos[${contador}][unidad]" class="select-unidad" style="padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                    <option value="Unidades">Unidades</option>
                    <option value="Frascos">Frascos</option>
                    <option value="Tubos">Tubos</option>
                    <option value="Paquetes">Paquetes</option>
                    <option value="Cajas">Cajas</option>
                    <option value="Rollo">Rollo</option>
                </select>
                <input type="number" step="1" min="0" name="insumos[${contador}][costo]" placeholder="Costo ($)" class="input-costo" required style="padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                <button type="button" class="btn-eliminar" style="background: #EF4444; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;">✕</button>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; padding-left: 2px;">
                <span style="font-size: 12px; color: #94A3B8;">Contenido exacto:</span>
                <input type="number" step="any" min="0" name="insumos[${contador}][cantidad_por_empaque]" value="1" style="width: 100px; padding: 6px; background: #1E293B; border: 1px solid #475569; color: #38BDF8; border-radius: 6px;">
                <select name="insumos[${contador}][unidad_contenido]" style="padding: 6px; background: #1E293B; border: 1px solid #475569; color: #38BDF8; border-radius: 6px;">
                    <?php foreach($unidad_contenido_opciones as $uc): ?><option value="<?php echo $uc; ?>"><?php echo $uc; ?></option><?php endforeach; ?>
                </select>
            </div>
        `;
        document.getElementById('contenedorInsumos').appendChild(div);
        contador++;
    });

    document.addEventListener('click', (e) => {
        if(e.target.classList.contains('btn-eliminar')) {
            e.target.closest('.insumo-row').remove();
            calcularTotales();
        }
    });
</script>
</body>
</html>