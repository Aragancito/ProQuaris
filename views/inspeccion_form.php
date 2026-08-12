<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Administrador';
$idLote = $_GET['idLote'] ?? ($inspeccion['FK_loteId'] ?? 0);
$insumosReales = $insumosReales ?? [];
$lote = $lote ?? [];
$cantidadProducidaOrden = $lote['cantidad'] ?? 0;

$costoUnitarioProducto = $lote['precioVenta'] ?? $lote['precio_venta'] ?? $lote['precio'] ?? 0;

$esEdicion = isset($inspeccion) && !empty($inspeccion);
$idRI = $esEdicion ? ($inspeccion['idRI'] ?? $inspeccion['id'] ?? 0) : 0;
$resultadoActual = $esEdicion ? ($inspeccion['resultado'] ?? 'Aprobado') : 'Aprobado';
$defectuosasActuales = $esEdicion ? intval($inspeccion['unidades_defectuosas'] ?? 0) : 0;

$observacionesTexto = $esEdicion ? ($inspeccion['observaciones'] ?? '') : '';
$motivoActual = 'Cumple especificaciones estándar';
$detalleObservacion = $observacionesTexto;

if ($esEdicion && strpos($observacionesTexto, 'Motivo: ') === 0) {
    $partes = explode(' - Detalle: ', $observacionesTexto, 2);
    $motivoActual = str_replace('Motivo: ', '', $partes[0]);
    $detalleObservacion = $partes[1] ?? '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $esEdicion ? 'Editar Inspección' : 'Registrar Inspección'; ?> - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="dashboard-container">
    
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1><?php echo $esEdicion ? 'Editar Auditoría e Inspección' : 'Auditoría de Insumos y Control de Calidad'; ?></h1>
                <p>Lote #<?php echo $idLote; ?> | Producto: <?php echo htmlspecialchars($lote['producto'] ?? 'Producto'); ?> (Disponibles: <?php echo $cantidadProducidaOrden; ?> unidades)</p>
            </div>
            <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo $idLote; ?>" style="padding:10px 20px; background:#475569; color:white; border-radius:8px; text-decoration:none; font-weight:500;">← Volver al Historial</a>
        </div>

        <div class="table-container" style="max-width: 950px; padding: 25px; margin-top: 20px;">
            <form action="/ProQuaris/controllers/CalidadController.php?accion=<?php echo $esEdicion ? 'actualizar' : 'guardar'; ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
                <input type="hidden" name="idLote" value="<?php echo $idLote; ?>">
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="idRI" value="<?php echo $idRI; ?>">
                <?php endif; ?>
                <input type="hidden" id="cantidadPlanificadaOrden" value="<?php echo $cantidadProducidaOrden; ?>">
                <input type="hidden" id="costoUnitarioFijo" value="<?php echo $costoUnitarioProducto; ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Resultado de Calidad:</label>
                        <select name="resultado" required style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: white;">
                            <option value="Aprobado" <?php echo ($resultadoActual === 'Aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                            <option value="Rechazado" <?php echo ($resultadoActual === 'Rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                            <option value="Observación" <?php echo ($resultadoActual === 'Observación') ? 'selected' : ''; ?>>Observación</option>
                        </select>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Motivo / Criterio de Inspección:</label>
                        <select name="motivo" required style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: white;">
                            <option value="Cumple especificaciones estándar" <?php echo ($motivoActual === 'Cumple especificaciones estándar') ? 'selected' : ''; ?>>Cumple especificaciones estándar</option>
                            <option value="Material defectuoso" <?php echo ($motivoActual === 'Material defectuoso') ? 'selected' : ''; ?>>Material defectuoso</option>
                            <option value="Unidad defectuosa" <?php echo ($motivoActual === 'Unidad defectuosa') ? 'selected' : ''; ?>>Unidad defectuosa</option>
                            <option value="Ajuste en consumo de insumos" <?php echo ($motivoActual === 'Ajuste en consumo de insumos') ? 'selected' : ''; ?>>Ajuste en consumo de insumos</option>
                            <option value="Defecto en acabado" <?php echo ($motivoActual === 'Defecto en acabado') ? 'selected' : ''; ?>>Defecto en acabado</option>
                            <option value="Falta de materia prima" <?php echo ($motivoActual === 'Falta de materia prima') ? 'selected' : ''; ?>>Falta de materia prima</option>
                        </select>
                    </div>
                </div>

                <!-- Control de Unidades Producidas vs Defectuosas -->
                <div style="display: flex; flex-direction: column; gap: 10px; background: #1E293B; padding: 15px; border-radius: 8px; border: 1px solid #334155;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Unidades Defectuosas:</label>
                            <input type="number" id="unidadesDefectuosas" name="unidades_defectuosas" min="0" max="<?php echo $cantidadProducidaOrden; ?>" value="<?php echo $defectuosasActuales; ?>" required style="padding: 8px 12px; border-radius: 6px; border: 1px solid #475569; background: #0F172A; color: #F87171; font-weight: bold;">
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Unidades Finales Correctas:</label>
                            <input type="text" id="unidadesFinalesReales" value="<?php echo max(0, $cantidadProducidaOrden - $defectuosasActuales); ?>" readonly style="padding: 8px 12px; border-radius: 6px; border: 1px solid #475569; background: #0F172A; color: #34D399; font-weight: bold; cursor: not-allowed;">
                        </div>
                    </div>
                    <div style="font-size: 12px; color: #94A3B8; border-top: 1px dashed #334155; padding-top: 8px; display: flex; justify-content: space-between;">
                        <span>Costo de producción por unidad: <strong id="lblCostoUnitario" style="color: #38BDF8;">$<?php echo number_format($costoUnitarioProducto, 0, ',', '.'); ?></strong></span>
                        <span>Pérdida estimada por defectos: <strong id="costoDefectuosas" style="color: #F87171;">$0</strong></span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Observaciones:</label>
                    <textarea name="observaciones" placeholder="Detalles de pérdidas, sobrantes o novedades..." style="height: 70px; padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: white; outline: none;"><?php echo htmlspecialchars($detalleObservacion); ?></textarea>
                </div>

                <!-- Sección de Auditoría Detallada por Insumo -->
                <div style="border-top: 1px solid #334155; padding-top: 15px; margin-top: 5px;">
                    <h3 style="font-size: 15px; color: #F8FAFC; margin-bottom: 5px;">Detalle de Insumos (Plan vs Real)</h3>
                    <p style="font-size: 12px; color: #94A3B8; margin-bottom: 12px;">Modifica el consumo real de cualquier insumo de forma fluida (positivos, ceros o negativos).</p>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php if (!empty($insumosReales)): ?>
                            <?php foreach ($insumosReales as $index => $ins): 
                                $cantPlanificadaEntera = round($ins['cantidadRequerida']);
                                $costoUnitarioBase = ($cantPlanificadaEntera != 0) ? (abs($ins['costoInsumo']) / abs($cantPlanificadaEntera)) : abs($ins['costoInsumo']);
                                $cantPorEmpaque = $ins['cantidad_por_empaque'] ?? 1;
                                $cantRealActual = $ins['cantidadReal'] ?? $cantPlanificadaEntera;
                            ?>
                                <div class="insumo-row" style="background: #0F172A; padding: 14px; border-radius: 8px; border: 1px solid #334155; display: flex; flex-direction: column; gap: 8px;"
                                     data-empaque="<?php echo $cantPorEmpaque; ?>"
                                     data-planificada="<?php echo $cantPlanificadaEntera; ?>"
                                     data-unit-cost="<?php echo $costoUnitarioBase; ?>"
                                     data-costo-base="<?php echo round($ins['costoInsumo']); ?>">
                                    
                                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 10px; align-items: center;">
                                        <div>
                                            <span style="font-size: 10px; color: #94A3B8; display: block;">Insumo:</span>
                                            <input type="text" name="insumos[<?php echo $index; ?>][nombre]" value="<?php echo htmlspecialchars($ins['insumo_nombre'] ?? $ins['nombre'] ?? ''); ?>" readonly style="width: 100%; padding: 8px; background: #1E293B; border: 1px solid #334155; color: #CBD5E1; border-radius: 6px; font-weight: bold;">
                                        </div>
                                        
                                        <div>
                                            <span style="font-size: 10px; color: #94A3B8; display: block;">Planificado:</span>
                                            <input type="text" value="<?php echo $cantPlanificadaEntera . ' ' . ($ins['unidad'] ?? ''); ?>" readonly style="width: 100%; padding: 8px; background: #1E293B; border: 1px solid #334155; color: #94A3B8; border-radius: 6px; font-size: 11px;">
                                        </div>

                                        <div>
                                            <span style="font-size: 10px; color: #38BDF8; display: block;">Cantidad Real:</span>
                                            <input type="number" step="1" name="insumos[<?php echo $index; ?>][cantidad]" value="<?php echo $cantRealActual; ?>" placeholder="Cant. real" class="input-cantidad" required style="width: 100%; padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                                        </div>

                                        <div>
                                            <span style="font-size: 10px; color: #34D399; display: block;">Costo Real:</span>
                                            <input type="number" step="1" name="insumos[<?php echo $index; ?>][costo]" value="<?php echo round(abs($ins['costoInsumo'])); ?>" readonly class="input-costo-total" style="width: 100%; padding: 8px; background: #1E293B; border: 1px solid #334155; color: #34D399; font-weight: bold; border-radius: 6px; cursor: not-allowed;">
                                        </div>
                                    </div>

                                    <div class="estado-insumo" style="font-size: 12px; font-weight: 500; color: #94A3B8; padding-top: 6px; border-top: 1px dashed #334155;">
                                        Estado analítico: <span class="texto-estado" style="color: #34D399;">Consumo exacto al planificado</span>
                                    </div>

                                    <input type="hidden" name="insumos[<?php echo $index; ?>][unidad]" value="<?php echo htmlspecialchars($ins['unidad'] ?? ''); ?>">
                                    <input type="hidden" name="insumos[<?php echo $index; ?>][cantidad_por_empaque]" value="<?php echo $cantPorEmpaque; ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #F87171; font-size: 13px;">Este lote no tiene insumos heredados.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen Financiero Total del Lote -->
                <div style="background: #0F172A; padding: 15px; border-radius: 8px; border: 1px solid #334155; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94A3B8;">
                        <span>Valor Total Planificado del Lote (<?php echo $cantidadProducidaOrden; ?> uds):</span>
                        <span id="valorBaseLote" style="font-weight: bold; color: #38BDF8;">$0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94A3B8; border-top: 1px dashed #334155; padding-top: 6px;">
                        <span>Ajuste por Insumos (Sobrantes / Faltantes):</span>
                        <span id="balanceInsumosTotal" style="font-weight: bold; color: #34D399;">$0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94A3B8; border-top: 1px dashed #334155; padding-top: 6px;">
                        <span>Descuento por Unidades Defectuosas:</span>
                        <span id="resumenDefectuosas" style="font-weight: bold; color: #F87171;">-$0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #334155; padding-top: 8px;">
                        <span style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Impacto Financiero Neto del Lote:</span>
                        <span id="impactoFinancieroNeto" style="font-size: 18px; font-weight: bold; color: #34D399;">$0</span>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 5px;">
                    <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo $idLote; ?>" style="width: 50%; padding: 12px; background: #475569; color: white; text-align: center; font-weight: bold; border-radius: 8px; text-decoration: none;">Cancelar</a>
                    <button type="submit" style="width: 50%; padding: 12px; background: #6366F1; border: none; color: white; font-weight: bold; border-radius: 8px; cursor: pointer;"><?php echo $esEdicion ? 'Actualizar Auditoría e Inspección' : 'Guardar Auditoría e Inspección'; ?></button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    const inputDefectuosas = document.getElementById('unidadesDefectuosas');
    const inputFinalesReales = document.getElementById('unidadesFinalesReales');
    const spanCostoDefectuosas = document.getElementById('costoDefectuosas');
    const spanResumenDefectuosas = document.getElementById('resumenDefectuosas');
    const spanBalanceInsumos = document.getElementById('balanceInsumosTotal');
    const spanValorBaseLote = document.getElementById('valorBaseLote');
    const lblCostoUnitario = document.getElementById('lblCostoUnitario');
    const planificadaOrden = parseFloat(document.getElementById('cantidadPlanificadaOrden').value) || 1;
    const costoUnitarioBaseFijo = parseFloat(document.getElementById('costoUnitarioFijo').value) || 0;
    const spanImpactoNeto = document.getElementById('impactoFinancieroNeto');

    lblCostoUnitario.textContent = "$" + Math.round(costoUnitarioBaseFijo).toLocaleString('es-CO');

    const valorBaseTotalLote = planificadaOrden * costoUnitarioBaseFijo;
    spanValorBaseLote.textContent = "$" + Math.round(valorBaseTotalLote).toLocaleString('es-CO');

    function actualizarCalculosGlobales() {
        let balanceInsumosTotal = 0; 

        document.querySelectorAll('.insumo-row').forEach(row => {
            const inputCant = row.querySelector('.input-cantidad');
            let cantReal = parseInt(inputCant.value);
            if (isNaN(cantReal)) cantReal = 0;

            const unitCost = parseFloat(row.getAttribute('data-unit-cost')) || 0;
            const cantPlanificada = parseFloat(row.getAttribute('data-planificada')) || 0;
            
            let costoTotalInsumoField = row.querySelector('.input-costo-total');
            let costoTotalInsumo = Math.round(Math.abs(cantReal) * unitCost);
            costoTotalInsumoField.value = costoTotalInsumo;
            
            let diferenciaUnidades = cantReal - cantPlanificada;
            let diferenciaDinero = diferenciaUnidades * unitCost;
            
            balanceInsumosTotal += diferenciaDinero;
        });

        if (balanceInsumosTotal > 0) {
            spanBalanceInsumos.textContent = "+$" + Math.round(balanceInsumosTotal).toLocaleString('es-CO') + " (Sobrante / Exceso)";
            spanBalanceInsumos.style.color = '#38BDF8';
        } else if (balanceInsumosTotal < 0) {
            spanBalanceInsumos.textContent = "-$" + Math.abs(Math.round(balanceInsumosTotal)).toLocaleString('es-CO') + " (Faltante / Ahorro)";
            spanBalanceInsumos.style.color = '#F87171';
        } else {
            spanBalanceInsumos.textContent = "$0";
            spanBalanceInsumos.style.color = '#34D399';
        }

        let defectuosas = parseInt(inputDefectuosas.value) || 0;
        let perdidaDefectos = Math.round(defectuosas * costoUnitarioBaseFijo);
        spanCostoDefectuosas.textContent = "$" + perdidaDefectos.toLocaleString('es-CO');
        spanResumenDefectuosas.textContent = "-$" + perdidaDefectos.toLocaleString('es-CO');

        // Cálculo correcto: Base - Defectos + Insumos
        let impactoNeto = valorBaseTotalLote + balanceInsumosTotal - perdidaDefectos;

        if (impactoNeto < 0) {
            spanImpactoNeto.textContent = "-$" + Math.abs(Math.round(impactoNeto)).toLocaleString('es-CO');
            spanImpactoNeto.style.color = '#F87171';
        } else {
            spanImpactoNeto.textContent = "$" + Math.round(impactoNeto).toLocaleString('es-CO');
            spanImpactoNeto.style.color = '#34D399';
        }
    }

    inputDefectuosas.addEventListener('input', () => {
        let defectuosas = parseInt(inputDefectuosas.value) || 0;
        if (defectuosas < 0) defectuosas = 0;
        if (defectuosas > planificadaOrden) defectuosas = planificadaOrden;
        inputDefectuosas.value = defectuosas;
        
        const finales = planificadaOrden - defectuosas;
        inputFinalesReales.value = finales + " unidades correctas (" + defectuosas + " defectuosas)";
        
        actualizarCalculosGlobales();
    });

    document.addEventListener('input', (e) => {
        if(e.target.classList.contains('input-cantidad')) {
            const row = e.target.closest('.insumo-row');
            const inputCant = e.target;
            let cantReal = parseInt(inputCant.value);
            if (isNaN(cantReal)) cantReal = 0;

            const cantPlanificada = parseFloat(row.getAttribute('data-planificada')) || 0;
            const unitCost = parseFloat(row.getAttribute('data-unit-cost')) || 0;
            const spanEstado = row.querySelector('.texto-estado');

            const diferenciaUnidades = cantReal - cantPlanificada;
            if (diferenciaUnidades === 0) {
                spanEstado.style.color = '#34D399';
                spanEstado.textContent = 'Consumo exacto al planificado';
            } else if (diferenciaUnidades > 0) {
                const valorSobrante = Math.round(diferenciaUnidades * unitCost);
                spanEstado.style.color = '#38BDF8';
                spanEstado.textContent = `¡Sobra material! Exceso de +${diferenciaUnidades} unidades (Sobrante: +$${valorSobrante.toLocaleString('es-CO')})`;
            } else {
                const unidadesFaltantes = Math.abs(diferenciaUnidades);
                const perdidaFaltante = Math.round(unidadesFaltantes * unitCost);
                spanEstado.style.color = '#F87171';
                spanEstado.textContent = `¡Falta material! Faltaron ${unidadesFaltantes} unidades en total (Pérdida/Diferencia: -$${perdidaFaltante.toLocaleString('es-CO')})`;
            }
            actualizarCalculosGlobales();
        }
    });

    actualizarCalculosGlobales();
</script>
</body>
</html>