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

$cantidadPlanificadaLote = intval($lote['cantidadPlanificada'] ?? 0);
if ($cantidadPlanificadaLote <= 0) $cantidadPlanificadaLote = intval($lote['cantidad'] ?? 0);

$defectuosasPrevias = intval($defectuosasPrevias ?? 0);
$unidadesDisponibles = max(0, $cantidadPlanificadaLote - $defectuosasPrevias);
$costoUnitarioProducto = $lote['precioVenta'] ?? $lote['precio_venta'] ?? $lote['precio'] ?? 0;

$esEdicion = isset($inspeccion) && !empty($inspeccion);
$idRI = $esEdicion ? ($inspeccion['idRI'] ?? $inspeccion['id'] ?? 0) : 0;
$resultadoActual = $esEdicion ? ($inspeccion['resultado'] ?? 'Aprobado') : 'Aprobado';
$defectuosasActuales = $esEdicion ? intval($inspeccion['unidades_defectuosas'] ?? 0) : 0;
$correctasActuales = max(0, $cantidadPlanificadaLote - $defectuosasPrevias - $defectuosasActuales);

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
                <p>Lote #<?php echo $idLote; ?> | Producto: <?php echo htmlspecialchars($lote['producto'] ?? 'Producto'); ?> (Planificadas: <?php echo $cantidadPlanificadaLote; ?> uds | Defectuosas previas: <?php echo $defectuosasPrevias; ?> | Disponibles: <?php echo $unidadesDisponibles; ?>)</p>
            </div>
            <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo $idLote; ?>" style="padding:10px 20px; background:#475569; color:white; border-radius:8px; text-decoration:none; font-weight:500;">← Volver al Historial</a>
        </div>

        <div class="table-container" style="max-width: 950px; padding: 25px; margin-top: 20px;">
            <form action="/ProQuaris/controllers/CalidadController.php?accion=<?php echo $esEdicion ? 'actualizar' : 'guardar'; ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
                <input type="hidden" name="idLote" value="<?php echo $idLote; ?>">
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="idRI" value="<?php echo $idRI; ?>">
                <?php endif; ?>
                <input type="hidden" id="cantidadPlanificadaOrden" value="<?php echo $cantidadPlanificadaLote; ?>">
                <input type="hidden" id="defectuosasPrevias" value="<?php echo $defectuosasPrevias; ?>">
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
                            <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Unidades Defectuosas (Pérdidas):</label>
                            <input type="number" id="unidadesDefectuosas" name="unidades_defectuosas" min="0" max="<?php echo $unidadesDisponibles; ?>" value="<?php echo $defectuosasActuales; ?>" required style="padding: 8px 12px; border-radius: 6px; border: 1px solid #475569; background: #0F172A; color: #F87171; font-weight: bold;">
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Unidades Finales Correctas:</label>
                            <!-- CORRECCIÓN AQUÍ: Name asignado, type number y ya no es readonly -->
                            <input type="number" id="unidadesFinalesReales" name="unidades_correctas" min="0" max="<?php echo $unidadesDisponibles; ?>" value="<?php echo $correctasActuales; ?>" required style="padding: 8px 12px; border-radius: 6px; border: 1px solid #475569; background: #0F172A; color: #34D399; font-weight: bold;">
                        </div>
                    </div>
                    <div style="font-size: 12px; color: #94A3B8; border-top: 1px dashed #334155; padding-top: 8px; display: flex; justify-content: space-between;">
                        <span>Costo de producción por unidad: <strong id="lblCostoUnitario" style="color: #38BDF8;">$<?php echo number_format($costoUnitarioProducto, 0, ',', '.'); ?></strong></span>
                        <span>Pérdida estimada por defectos/faltantes: <strong id="costoDefectuosas" style="color: #F87171;">$0</strong></span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Observaciones:</label>
                    <textarea name="observaciones" placeholder="Detalles de pérdidas, sobrantes o novedades..." style="height: 70px; padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: white; outline: none;"><?php echo htmlspecialchars($detalleObservacion); ?></textarea>
                </div>

                <!-- Sección de Auditoría Detallada por Insumo -->
                <div style="border-top: 1px solid #334155; padding-top: 15px; margin-top: 5px;">
                    <h3 style="font-size: 15px; color: #F8FAFC; margin-bottom: 5px;">Detalle de Insumos (Plan vs Real)</h3>
                    <p style="font-size: 12px; color: #94A3B8; margin-bottom: 12px;">El plan es el material necesario para TODO el lote (receta de 1 unidad x unidades planificadas). Escribe cuánto se consumió realmente (nunca un valor negativo).</p>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php if (!empty($insumosReales)): ?>
                            <?php foreach ($insumosReales as $index => $ins): 
                                $cantPlanificadaLoteInsumo = abs(floatval($ins['cantidadRequerida'] ?? 0));
                                $costoUnitarioBase = floatval($ins['costoUnitario'] ?? 0);
                                if ($costoUnitarioBase == 0 && $cantPlanificadaLoteInsumo != 0) {
                                    $costoUnitarioBase = abs(floatval($ins['costoInsumo'] ?? 0)) / $cantPlanificadaLoteInsumo;
                                }
                                $cantPorUnidad = floatval($ins['cantidadPorUnidad'] ?? 0);
                                if ($cantPorUnidad == 0 && $cantidadPlanificadaLote > 0) {
                                    $cantPorUnidad = $cantPlanificadaLoteInsumo / $cantidadPlanificadaLote;
                                }
                                $cantPorEmpaque = $ins['cantidad_por_empaque'] ?? 1;
                                $cantRealActual = ($ins['cantidadConsumida'] !== null && $ins['cantidadConsumida'] !== '')
                                    ? floatval($ins['cantidadConsumida'])
                                    : $cantPlanificadaLoteInsumo;
                            ?>
                                <div class="insumo-row" style="background: #0F172A; padding: 14px; border-radius: 8px; border: 1px solid #334155; display: flex; flex-direction: column; gap: 8px;"
                                     data-empaque="<?php echo $cantPorEmpaque; ?>"
                                     data-planificada="<?php echo $cantPlanificadaLoteInsumo; ?>"
                                     data-por-unidad="<?php echo $cantPorUnidad; ?>"
                                     data-unit-cost="<?php echo $costoUnitarioBase; ?>">
                                    
                                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 10px; align-items: center;">
                                        <div>
                                            <span style="font-size: 10px; color: #94A3B8; display: block;">Insumo:</span>
                                            <input type="hidden" name="insumos[<?php echo $index; ?>][id]" value="<?php echo intval($ins['idLoteInsumo'] ?? 0); ?>">
                                            <input type="text" value="<?php echo htmlspecialchars($ins['insumo_nombre'] ?? $ins['nombre'] ?? ''); ?>" readonly style="width: 100%; padding: 8px; background: #1E293B; border: 1px solid #334155; color: #CBD5E1; border-radius: 6px; font-weight: bold;">
                                        </div>
                                        
                                        <div>
                                            <span style="font-size: 10px; color: #94A3B8; display: block;">Planificado (lote):</span>
                                            <input type="text" value="<?php echo rtrim(rtrim(number_format($cantPlanificadaLoteInsumo, 2, '.', ''), '0'), '.') . ' ' . ($ins['unidad'] ?? ''); ?>" title="<?php echo rtrim(rtrim(number_format($cantPorUnidad, 2, '.', ''), '0'), '.'); ?> por unidad x <?php echo $cantidadPlanificadaLote; ?> unidades" readonly style="width: 100%; padding: 8px; background: #1E293B; border: 1px solid #334155; color: #94A3B8; border-radius: 6px; font-size: 11px;">
                                        </div>

                                        <div>
                                            <span style="font-size: 10px; color: #38BDF8; display: block;">Consumo Real:</span>
                                            <input type="number" step="1" min="0" name="insumos[<?php echo $index; ?>][cantidad]" value="<?php echo rtrim(rtrim(number_format($cantRealActual, 2, '.', ''), '0'), '.'); ?>" placeholder="Cant. real" class="input-cantidad" required style="width: 100%; padding: 8px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 6px;">
                                        </div>

                                        <div>
                                            <span style="font-size: 10px; color: #34D399; display: block;">Costo Real:</span>
                                            <input type="number" step="1" value="<?php echo round($cantRealActual * $costoUnitarioBase); ?>" readonly class="input-costo-total" style="width: 100%; padding: 8px; background: #1E293B; border: 1px solid #334155; color: #34D399; font-weight: bold; border-radius: 6px; cursor: not-allowed;">
                                        </div>
                                    </div>

                                    <div class="estado-insumo" style="font-size: 12px; font-weight: 500; color: #94A3B8; padding-top: 6px; border-top: 1px dashed #334155;">
                                        Estado analítico: <span class="texto-estado" style="color: #34D399;">Consumo exacto al planificado</span>
                                    </div>

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
                        <span>Valor Total Planificado del Lote (<?php echo $cantidadPlanificadaLote; ?> uds):</span>
                        <span id="valorBaseLote" style="font-weight: bold; color: #38BDF8;">$0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94A3B8; border-top: 1px dashed #334155; padding-top: 6px;">
                        <span>Ajuste por Insumos (sobrante = ahorro / exceso = costo):</span>
                        <span id="balanceInsumosTotal" style="font-weight: bold; color: #34D399;">$0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94A3B8; border-top: 1px dashed #334155; padding-top: 6px;">
                        <span>Descuento por Unidades Faltantes o Defectuosas:</span>
                        <span id="resumenDefectuosas" style="font-weight: bold; color: #F87171;">-$0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #334155; padding-top: 8px;">
                        <span style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Impacto Financiero Neto del Lote:</span>
                        <span id="impactoFinancieroNeto" style="font-size: 18px; font-weight: bold; color: #34D399;">$0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #334155; padding-top: 6px;">
                        <span style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Rendimiento del Lote (ganado / perdido):</span>
                        <span id="porcentajeRendimiento" style="font-size: 16px; font-weight: bold; color: #34D399;">0%</span>
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
    const defectuosasPrevias = parseInt(document.getElementById('defectuosasPrevias').value) || 0;
    const costoUnitarioBaseFijo = parseFloat(document.getElementById('costoUnitarioFijo').value) || 0;
    const spanImpactoNeto = document.getElementById('impactoFinancieroNeto');
    const spanPorcentaje = document.getElementById('porcentajeRendimiento');
    const maximoDefectuosas = Math.max(0, planificadaOrden - defectuosasPrevias);

    lblCostoUnitario.textContent = "$" + Math.round(costoUnitarioBaseFijo).toLocaleString('es-CO');
    const valorBaseTotalLote = planificadaOrden * costoUnitarioBaseFijo;
    spanValorBaseLote.textContent = "$" + Math.round(valorBaseTotalLote).toLocaleString('es-CO');

    function actualizarCalculosGlobales() {
        let balanceInsumosTotal = 0; 

        document.querySelectorAll('.insumo-row').forEach(row => {
            const inputCant = row.querySelector('.input-cantidad');
            let cantReal = parseFloat(inputCant.value);
            if (isNaN(cantReal) || cantReal < 0) cantReal = 0;

            const unitCost = parseFloat(row.getAttribute('data-unit-cost')) || 0;
            const cantPlanificada = parseFloat(row.getAttribute('data-planificada')) || 0;
            
            let costoTotalInsumoField = row.querySelector('.input-costo-total');
            costoTotalInsumoField.value = Math.round(cantReal * unitCost);
            
            balanceInsumosTotal += (cantPlanificada - cantReal) * unitCost;
        });

        if (balanceInsumosTotal > 0) {
            spanBalanceInsumos.textContent = "+$" + Math.round(balanceInsumosTotal).toLocaleString('es-CO') + " (Sobró material / Ahorro)";
            spanBalanceInsumos.style.color = '#38BDF8';
        } else if (balanceInsumosTotal < 0) {
            spanBalanceInsumos.textContent = "-$" + Math.abs(Math.round(balanceInsumosTotal)).toLocaleString('es-CO') + " (Se gastó de más)";
            spanBalanceInsumos.style.color = '#F87171';
        } else {
            spanBalanceInsumos.textContent = "$0";
            spanBalanceInsumos.style.color = '#34D399';
        }

        const defectuosasTotales = (parseInt(inputDefectuosas.value) || 0) + defectuosasPrevias;
        const perdidaDefectos = Math.round(defectuosasTotales * costoUnitarioBaseFijo);
        spanCostoDefectuosas.textContent = "$" + perdidaDefectos.toLocaleString('es-CO');
        spanResumenDefectuosas.textContent = "-$" + perdidaDefectos.toLocaleString('es-CO');

        const impactoNeto = valorBaseTotalLote + balanceInsumosTotal - perdidaDefectos;

        if (impactoNeto < 0) {
            spanImpactoNeto.textContent = "-$" + Math.abs(Math.round(impactoNeto)).toLocaleString('es-CO');
            spanImpactoNeto.style.color = '#F87171';
        } else {
            spanImpactoNeto.textContent = "$" + Math.round(impactoNeto).toLocaleString('es-CO');
            spanImpactoNeto.style.color = '#34D399';
        }

        const porcentaje = (valorBaseTotalLote !== 0)
            ? ((impactoNeto - valorBaseTotalLote) / valorBaseTotalLote) * 100
            : 0;
        const diferencia = Math.round(impactoNeto - valorBaseTotalLote);

        if (diferencia < 0) {
            spanPorcentaje.textContent = porcentaje.toFixed(2) + "% (pierde $" + Math.abs(diferencia).toLocaleString('es-CO') + ")";
            spanPorcentaje.style.color = '#F87171';
        } else if (diferencia > 0) {
            spanPorcentaje.textContent = "+" + porcentaje.toFixed(2) + "% (ahorra $" + diferencia.toLocaleString('es-CO') + ")";
            spanPorcentaje.style.color = '#38BDF8';
        } else {
            spanPorcentaje.textContent = "0% (sale igual a lo planificado)";
            spanPorcentaje.style.color = '#34D399';
        }
    }

    // SI EL USUARIO EDITA "DEFECTUOSAS", SE CALCULA AUTOMÁTICAMENTE "CORRECTAS"
    inputDefectuosas.addEventListener('input', () => {
        let defectuosas = parseInt(inputDefectuosas.value) || 0;
        if (defectuosas < 0) defectuosas = 0;
        if (defectuosas > maximoDefectuosas) defectuosas = maximoDefectuosas;
        inputDefectuosas.value = defectuosas;
        
        const totales = defectuosas + defectuosasPrevias;
        const finales = planificadaOrden - totales;
        inputFinalesReales.value = finales;
        
        actualizarCalculosGlobales();
    });

    // SI EL USUARIO EDITA "CORRECTAS", SE CALCULA AUTOMÁTICAMENTE "DEFECTUOSAS"
    inputFinalesReales.addEventListener('input', () => {
        let correctas = parseInt(inputFinalesReales.value) || 0;
        if (correctas < 0) correctas = 0;
        if (correctas > maximoDefectuosas) correctas = maximoDefectuosas; // Máximo las que quedan disponibles
        inputFinalesReales.value = correctas;

        const defectuosas = planificadaOrden - defectuosasPrevias - correctas;
        inputDefectuosas.value = defectuosas;

        actualizarCalculosGlobales();
    });

    function actualizarEstadoInsumo(row) {
        const inputCant = row.querySelector('.input-cantidad');
        let cantReal = parseFloat(inputCant.value);
        if (isNaN(cantReal) || cantReal < 0) cantReal = 0;

        const cantPlanificada = parseFloat(row.getAttribute('data-planificada')) || 0;
        const porUnidad = parseFloat(row.getAttribute('data-por-unidad')) || 0;
        const unitCost = parseFloat(row.getAttribute('data-unit-cost')) || 0;
        const spanEstado = row.querySelector('.texto-estado');

        const diferencia = cantReal - cantPlanificada;
        const valorDiferencia = Math.round(Math.abs(diferencia) * unitCost);

        if (diferencia === 0) {
            spanEstado.style.color = '#34D399';
            spanEstado.textContent = 'Consumo exacto al planificado';
        } else if (diferencia > 0) {
            spanEstado.style.color = '#F87171';
            spanEstado.textContent = `Se gastó de más: ${diferencia} unidades por encima del plan (costo extra: -$${valorDiferencia.toLocaleString('es-CO')})`;
        } else {
            const sobrantes = Math.abs(diferencia);
            const unidadesEquivalentes = (porUnidad > 0) ? Math.floor(sobrantes / porUnidad) : 0;
            const detalleUnidades = (unidadesEquivalentes > 0)
                ? ` — equivale al material de ${unidadesEquivalentes} unidad(es)`
                : '';
            spanEstado.style.color = '#38BDF8';
            spanEstado.textContent = `Sobró material: ${sobrantes} unidades sin usar (ahorro: +$${valorDiferencia.toLocaleString('es-CO')})${detalleUnidades}`;
        }
    }

    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('input-cantidad')) {
            if (parseFloat(e.target.value) < 0) e.target.value = 0;
            actualizarEstadoInsumo(e.target.closest('.insumo-row'));
            actualizarCalculosGlobales();
        }
    });

    document.querySelectorAll('.insumo-row').forEach(actualizarEstadoInsumo);
    actualizarCalculosGlobales();
</script>
</body>
</html>