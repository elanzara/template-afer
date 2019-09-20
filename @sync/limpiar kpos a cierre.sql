TRUNCATE TABLE `pos_clientes`;
TRUNCATE TABLE `pos_clientes_cc`;
TRUNCATE TABLE `pos_compras`;
TRUNCATE TABLE `pos_compras_comprobante`;
TRUNCATE TABLE `pos_compras_items`;
TRUNCATE TABLE `pos_compras_items_otros`;
TRUNCATE TABLE `pos_ordenes_pago_detalle`;
TRUNCATE TABLE `pos_ordenes_pago`;
TRUNCATE TABLE `pos_articulos`;
TRUNCATE TABLE `pos_articulos_precios`;
TRUNCATE TABLE `pos_proveedores`;
TRUNCATE TABLE `pos_proveedores_cc`;
TRUNCATE TABLE `abm_beneficiarios`;
TRUNCATE TABLE `pos_ventas_donaciones`;
TRUNCATE TABLE `pos_stock`;
TRUNCATE TABLE `pos_stock_movimientos`;
TRUNCATE TABLE `pos_ventas`;
TRUNCATE TABLE `pos_ventas_comprobante`;
TRUNCATE TABLE `pos_ventas_items`;
TRUNCATE TABLE `pos_ventas_recibos`;
TRUNCATE TABLE `pos_ventas_recibos_detalle`;
TRUNCATE TABLE `pos_cajas_movimientos`;
TRUNCATE TABLE `pos_cajas_movimientos_historico`;
TRUNCATE TABLE `pos_cierre_turnos`;
TRUNCATE TABLE `pos_cierre_turnos_detalle_caja`;
TRUNCATE TABLE `pos_cierre_turnos_detalle_picos`;
TRUNCATE TABLE `pos_cierre_turnos_detalle_tanques`;
TRUNCATE TABLE `pos_cierre_turnos_detalle_otros`;
TRUNCATE TABLE `pos_cheques`;
TRUNCATE TABLE `cloud_fuel_updates`;
TRUNCATE TABLE `pos_cierre_fiscal`;
TRUNCATE TABLE `pos_ventas_fe_requests`;
TRUNCATE TABLE `pos_ventas_mayoristas`;
TRUNCATE TABLE `pos_ventas_mayoristas_detalle`;
TRUNCATE TABLE `pos_alivios`;
TRUNCATE TABLE `pos_alivios_detalle`;
TRUNCATE TABLE `pos_ventas_mayoristas_picos`;
TRUNCATE TABLE `pos_fiscal_printer_errors`;
TRUNCATE TABLE `pos_ventas_tkt_fiscales`;
TRUNCATE TABLE `sys_sync_log`;

#info de prueba

INSERT INTO `pos_clientes` (`id`, `razon_social`, `direccion`, `telefono`, `direccion_cp`, `id_provincia`, `email`, `id_documento_tipo`, `documento_numero`, `id_iva_situacion`, `comentarios`, `enabled`) VALUES
(1, 'CONSUMIDOR FINAL', 'CONSUMIDOR FINAL', '', '', 25, '', 99, '99999999999', 5, '', 1),
(2, 'MOVIMIENTO INTERNO', 'CONSUMIDOR FINAL', '', '', 25, '', 99, '99999999999', 5, '', 1),
(100, 'ARIEL FERNANDEZ', 'CAMPANA 1327', '1153295893', '', 25, 'INFO@AFER.COM.AR', 80, '20352660362', 1, 'TEST', 1);

INSERT INTO `pos_proveedores` (`id`, `razon_social`, `direccion`, `telefono`, `direccion_cp`, `id_provincia`, `email`, `id_documento_tipo`, `documento_numero`, `id_iva_situacion`, `comentarios`, `enabled`) VALUES
(1, 'PROVISIÓN INTERNA ROYAL ENERGY', 'test1', '1', '', 16, '1', 80, '30711536600', 1, '', 1);

INSERT INTO `abm_beneficiarios` (`id`, `valor`, `cuit`, `alicuota`, `enabled`) VALUES ("0", 'SIN COLABORACION', '99999999999', '0', '1');
UPDATE `abm_beneficiarios` SET `id` = '0' WHERE `abm_beneficiarios`.`id` = 1; 

INSERT INTO `pos_articulos` (`id`, `codigo_barras`, `descripcion`, `categoria`, `id_proveedor`, `id_deposito`, `cant_min`, `descuenta_stock`, `enabled`) VALUES
(1, '', 'SUPER 95 ENERGY', 'NAFTAS', 0, 3, '1.0000', 1, 1),
(2, '', 'PREMIUM 98 ENERGY', 'NAFTAS', 0, 4, '1.0000', 1, 1),
(3, '', 'EURO D ENERGY', 'GASOIL', 0, 5, '1.0000', 1, 1),
(4, '', 'SUPER 95 ENERGY', 'NAFTAS', 0, 6, '1.0000', 1, 1),
(5, '', 'DIESEL ENERGY', 'GASOIL', 0, 7, '1.0000', 1, 1),
(6, '', 'DIESEL ENERGY', 'GASOIL', 0, 9, '1.0000', 1, 1),
(7, '', 'DIESEL RTE.', 'GASOIL', 0, 8, '1.0000', 8, 1),
(8, '', 'EURO D RTE.', 'GASOIL', 0, 8, '1.0000', 8, 1),
(9, '', 'PREMIUM RTE.', 'GASOIL', 0, 8, '1.0000', 8, 1),
(10, '', 'SUPER RTE.', 'GASOIL', 0, 8, '1.0000', 8, 1)/*,
(100, '', 'CARAMELOS SUGUS', 'DULCES', 0, 1, '150.0000', 1, 1),
(101, '', 'CHICLES BELDENT', 'DULCES', 0, 1, '150.0000', 1, 1),
(102, '', 'TEST', 'DULCES', 0, 1, '150.0000', 1, 1)*/;

INSERT INTO `pos_articulos_precios` (`id`, `id_articulo`, `datetime`, `neto_gravado`, `no_gravado`, `neto_exento`, `iva`, `iva_alicuota`) VALUES
(1, 1, '2010-01-01 00:00:00', '17.3471', '0.0000', '0.0000', '3.6429', '21.00'),
(2, 2, '2010-01-01 00:00:00', '19.7355371901', '0.0000', '0.0000', '4.1444628099', '21.00'),
(3, 3, '2010-01-01 00:00:00', '17.8429752066', '0.0000', '0.0000', '3.7470247934', '21.00'),
(4, 4, '2010-01-01 00:00:00', '17.3471', '0.0000', '0.0000', '3.6429', '21.00'),
(5, 5, '2010-01-01 00:00:00', '15.1983471074', '0.0000', '0.0000', '3.1916528926', '21.00'),
(6, 6, '2010-01-01 00:00:00', '15.1983471074', '0.0000', '0.0000', '3.1916528926', '21.00'),
(7, 100, '2010-01-01 00:00:00', '10.0000', '0.0000', '0.0000', '2.100', '21.00')/*,
(8, 101, '2010-01-01 00:00:00', '5.0000', '0.0000', '0.0000', '1.050', '21.00'),
(9, 102, '2010-01-01 00:00:00', '20.0000', '0.0000', '0.0000', '4.200', '21.00')*/;

INSERT INTO `pos_stock` (`id`, `id_articulo`, `id_deposito`, `cantidad`) VALUES
(1, 1, 3, '9999.999'),
(2, 2, 4, '9999.999'),
(3, 4, 5, '9999.999'),
(4, 5, 6, '9999.999'),
(5, 3, 7, '9999.999'),
(6, 6, 9, '9999.999')/*,
(7, 100, 1, '0.0000'),
(8, 101, 1, '0.0000'),
(9, 102, 1, '0.0000')*/;

INSERT INTO `pos_stock_movimientos` (`id`, `id_usuario`, `datetime`, `id_movimiento_tipo`, `id_deposito`, `cantidad`, `id_articulo`, `comentario`, `id_segmento`) VALUES
(1, 1, '2017-01-01 00:00:00', 1, 3, '9999.999', 1, 'Compra Inicial',2),
(2, 1, '2017-01-01 00:00:00', 1, 4, '9999.999', 1, 'Compra Inicial',2),
(3, 1, '2017-01-01 00:00:00', 1, 5, '9999.999', 1, 'Compra Inicial',2),
(4, 1, '2017-01-01 00:00:00', 1, 6, '9999.999', 1, 'Compra Inicial',2),
(5, 1, '2017-01-01 00:00:00', 1, 7, '9999.999', 1, 'Compra Inicial',2),
(6, 1, '2017-01-01 00:00:00', 1, 9, '9999.999', 1, 'Compra Inicial',2);

INSERT INTO `pos_cierre_turnos` (`id`, `datetime`, `id_usuario`, `responsables`, `turno_duracion`, `comentario`) VALUES
(1, '2017-01-01 00:00:00', 3, '', '00:00:00', 'CIERRE INICIAL'),
(3, '2017-01-01 00:00:00', 1, '', '00:00:00', 'CIERRE INICIAL'),
(2, '2017-01-01 00:00:00', 2, '', '00:00:00', 'CIERRE INICIAL');

INSERT INTO `pos_cierre_turnos_detalle_tanques` (`id`, `id_cierre_turno`, `id_deposito`, `inicial`, `movimientos_tanque`, `purgue_tanque`, `movimientos_pico`, `varillaje`) VALUES
(1, 1, 3, '9999.999', '0.0000', '0.0000', '0.0000', '0.0000'),
(2, 1, 4, '9999.999', '0.0000', '0.0000', '0.0000', '0.0000'),
(3, 1, 5, '9999.999', '0.0000', '0.0000', '0.0000', '0.0000'),
(4, 1, 6, '9999.999', '0.0000', '0.0000', '0.0000', '0.0000'),
(5, 1, 7, '9999.999', '0.0000', '0.0000', '0.0000', '0.0000'),
(6, 1, 9, '9999.999', '0.0000', '0.0000', '0.0000', '0.0000');

INSERT INTO `pos_cierre_turnos_detalle_picos` (`id`, `id_cierre_turno`, `id_pico`, `inicial`, `final`, `purgue_pico`, `precio_unitario`) VALUES
(1, 1, 1, '0.0000', '0', '0.0000', '0.0000'),
(2, 1, 2, '0.0000', '0', '0.0000', '0.0000'),
(3, 1, 3, '0.0000', '0', '0.0000', '0.0000'),
(4, 1, 4, '0.0000', '0', '0.0000', '0.0000'),
(5, 1, 5, '0.0000', '0', '0.0000', '0.0000'),
(6, 1, 6, '0.0000', '0', '0.0000', '0.0000'),
(7, 1, 7, '0.0000', '0', '0.0000', '0.0000'),
(8, 1, 8, '0.0000', '0', '0.0000', '0.0000'),
(9, 1, 9, '0.0000', '0', '0.0000', '0.0000'),
(10, 1, 10, '0.0000', '0', '0.0000', '0.0000'),
(11, 1, 11, '0.0000', '0', '0.0000', '0.0000'),
(12, 1, 12, '0.0000', '0', '0.0000', '0.0000');