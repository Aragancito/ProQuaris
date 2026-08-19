-- ==========================================================
-- ProQuaris - Migración: separar PLAN vs CONSUMO REAL
-- ==========================================================
-- Ejecutar UNA sola vez en phpMyAdmin (pestaña SQL) sobre proquaris_bd.
--
-- Qué hace:
--   1. El lote guarda su cantidad planificada fija (ya no se "encoge"
--      con cada inspección).
--   2. Los insumos del lote guardan tres cosas separadas:
--        - cantidadPorUnidad : lo que lleva UNA unidad (receta)
--        - cantidadRequerida : lo que lleva TODO el lote (receta x unidades)
--        - cantidadConsumida : lo que realmente se consumió (inspección)
--   3. costoUnitario guarda el costo de una unidad de insumo sin redondear,
--      para que no se pierdan pesos en los cálculos.
--   4. La inspección guarda el % de rendimiento (ganado / perdido).
-- ==========================================================

-- ---------- 1. Lote: cantidad planificada fija ----------
ALTER TABLE `lote`
    ADD COLUMN `cantidadPlanificada` INT NOT NULL DEFAULT 0 AFTER `cantidad`;

UPDATE `lote` l
SET l.`cantidadPlanificada` = l.`cantidad` + COALESCE(
        (SELECT SUM(r.`unidades_defectuosas`)
         FROM `registroinspeccion` r
         WHERE r.`FK_loteId` = l.`idLote`), 0)
WHERE l.`cantidadPlanificada` = 0;

-- ---------- 2. Insumos del lote: plan vs consumo ----------
ALTER TABLE `lote_insumos_reales`
    ADD COLUMN `cantidadPorUnidad` DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER `insumo_nombre`,
    ADD COLUMN `cantidadConsumida` DECIMAL(10,2) DEFAULT NULL AFTER `cantidadRequerida`,
    ADD COLUMN `costoUnitario` DECIMAL(14,4) NOT NULL DEFAULT 0 AFTER `costoInsumo`,
    MODIFY `cantidadRequerida` DECIMAL(14,2) NOT NULL,
    MODIFY `costoInsumo` DECIMAL(14,2) NOT NULL;

-- 2.a Lotes cuyo producto todavía tiene receta: se recalcula desde la receta.
UPDATE `lote_insumos_reales` li
JOIN `lote` l              ON l.`idLote` = li.`FK_loteId`
JOIN `ordenproduccion` o   ON o.`idOrden` = l.`FK_ordenId`
JOIN `recetas` r           ON r.`idProducto` = o.`idProducto`
                          AND r.`insumo_nombre` = li.`insumo_nombre`
SET li.`cantidadPorUnidad` = ABS(r.`cantidadRequerida`),
    li.`costoUnitario`     = CASE WHEN r.`cantidadRequerida` <> 0
                                  THEN ABS(r.`costoInsumo`) / ABS(r.`cantidadRequerida`)
                                  ELSE ABS(r.`costoInsumo`) END,
    li.`cantidadRequerida` = ABS(r.`cantidadRequerida`) * l.`cantidadPlanificada`,
    li.`costoInsumo`       = CASE WHEN r.`cantidadRequerida` <> 0
                                  THEN ABS(r.`costoInsumo`) / ABS(r.`cantidadRequerida`)
                                  ELSE ABS(r.`costoInsumo`) END
                             * ABS(r.`cantidadRequerida`) * l.`cantidadPlanificada`
WHERE li.`cantidadPorUnidad` = 0;

-- 2.b Lotes viejos sin receta: se asume que lo guardado era la receta de 1 unidad.
UPDATE `lote_insumos_reales` li
JOIN `lote` l ON l.`idLote` = li.`FK_loteId`
SET li.`cantidadPorUnidad` = ABS(li.`cantidadRequerida`),
    li.`costoUnitario`     = CASE WHEN li.`cantidadRequerida` <> 0
                                  THEN ABS(li.`costoInsumo`) / ABS(li.`cantidadRequerida`)
                                  ELSE ABS(li.`costoInsumo`) END,
    li.`cantidadRequerida` = ABS(li.`cantidadRequerida`) * l.`cantidadPlanificada`,
    li.`costoInsumo`       = ABS(li.`costoInsumo`) * l.`cantidadPlanificada`
WHERE li.`cantidadPorUnidad` = 0;

-- ---------- 3. Inspección: porcentaje de rendimiento ----------
ALTER TABLE `registroinspeccion`
    ADD COLUMN `porcentaje_rendimiento` DECIMAL(7,2) NOT NULL DEFAULT 0 AFTER `impacto_financiero`;
