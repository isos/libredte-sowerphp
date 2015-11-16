BEGIN;

INSERT INTO dte_tipo VALUES
	(30, 'Factura', false, true, true),
	(32, 'Factura de ventas y servicios no afectos o excentos de IVA', false, true, true),
	(33, 'Factura electrónica', true, true, true),
	(34, 'Factura no afecta o exenta electrónica', true, true, true),
	(35, 'Boleta', false, false, true),
	(38, 'Boleta exenta', false, false, true),
	(39, 'Boleta electrónica', true, false, true),
	(41, 'Boleta exenta electrónica', true, false, true),
	(50, 'Guía de despacho', false, false, false),
	(52, 'Guía de despacho electrónica', true, false, false),
	(55, 'Nota de débito', false, true, true),
	(56, 'Nota de débito electrónica', true, true, true),
	(60, 'Nota de crédito', false, true, true),
	(61, 'Nota de crédito electrónica', true, true, true)
;

INSERT INTO iva_no_recuperable VALUES
	(1, 'Compras destinadas a IVA a generar operaciones no gravadas o exentas'),
	(2, 'Facturas de proveedores registradas fuera de plazo'),
	(3, 'Gastos rechazados'),
	(4, 'Entregas gratuitas (premios, bonificaciones, etc) recibidas'),
	(9, 'Otros')
;

INSERT INTO impuesto_adicional VALUES
	(14, NULL, 'IVA de margen de comercialización', 'Para facturas de venta del contribuyente'),
	(15, NULL, 'IVA retenido total', 'Corresponde al IVA retenido en facturas de compra del contribuyente que genera libro. Suma de retenciones con tasa de IVA'),
	(17, NULL, 'IVA anticipado faenamiento carne', 'Tasa de 5% sobre monto base faenamiento. Se registra el monto de IVA anticipado cobrado al cliente'),
	(18, NULL, 'IVA anticipado carne', 'Tasa de 5%. Se registra el monto de IVA anticipado cobrado al cliente.'),
	(19, NULL, 'IVA anticipado harina', 'Tasa de 12%. Se registra el monto de IVA anticipado cobrado al cliente'),
	(23, NULL, 'Impuesto adicional art 37 letras a, b, c', 'Tasa del 15%\na) Artículos oro, platino, marfil\nb) Joyas, piedras preciosas\nc) Pieles finas'),
	(24, NULL, 'Impuesto art 42 Ley 825/74 letra a', 'Tasa del 27%. Licores, piscos, whisky, aguardiente y vinos licorosos o aromatizados'),
	(25, NULL, 'Impuesto art 42, letra c', 'Tasa del 15%: Vinos'),
	(26, NULL, 'Impuesto art 42, letra c', 'Tasa del 15%: Cervezas y bebidas alcohólicas'),
	(27, NULL, 'Impuesto art 42, letra d y e', 'Tasa del 15%: bebidas analcohólicas y minerales'),
	(28, NULL, 'Impuesto específico diesel', 'Impuesto específico a los combustibles traspasado al comprador por compra de diesel según Ley N° 18.502, decreto supremo N° 311/86.\nImpuesto específico resultante a los combustibles = componente fija + componente variable, según Ley 20.493, su reglamento y res. ex SII N° 51 de 2011'),
	(29, NULL, 'Recuperación impuesto específico resultante al diesel transportistas', 'Para transportistas de carga Art 2° Ley N° 19.764/2001.\nImpuesto específico resultante a los combustibles = componente fija + componente variable, según Ley 20.493, su reglamento y res. ex SII N° 51 de 2011'),
	(30, 301, 'IVA retenido legumbres', 'Normalmente 13% retención.\n - Si se retuvo el 13%, el monto retenido se registra en el IEC en retención parcial.\n- Si se retuvo el total del IVA, por ser NDF se registra en retención total'),
	(31, NULL, 'IVA retenido silvestres', 'Total del IVA retención. El monto retenido se registra en el IEC en retención total'),
	(32, 321, 'IVA retenido ganado', 'Normalmente 8% de retención.\n- Si se retuvo el 8%, el monto se registra en el IEC en retención parcial\n- Si se retuvo el total del IVA, por ser NDF, se registra en el IEC en retención total'),
	(33, 331, 'IVA retenido madera', 'Normalmente 8% de retención.\n- Si se retuvo el 8%, el monto se registra en el IEC en retención parcial\n- Si se retuvo el total del IVA, por ser NDF, se registra en el IEC en retención total'),
	(34, 341, 'IVA retenido trigo', 'Normalmente 11% de retención.\n- Si se retuvo el 11%, el monto se registra en el IEC en retención parcial\n- Si se retuvo el total del IVA, por ser NDF, se registra en el IEC en retención total'),
	(35, NULL, 'Impuesto específico gasolina', 'Impuesto específico a los combustibles traspasado al comprador por compra de gasolina según Ley N° 18.502, decreto supremo N° 311/86.\nNo da derecho a crédito.\nImpuesto específico resultante a los combustibles = componente fija + componente variable, según Ley 20.493, su reglamento y res. ex SII N° 51 de 2011'),
	(36, 361, 'IVA retenido arroz', 'Normalmente 10% de retención.\n- Si se retuvo el 10%, el monto se registra en el IEC en retención parcial\n- Si se retuvo el total del IVA, por ser NDF, se registra en el IEC en retención total'),
	(37, 371, 'IVA retenido hidrobiológicas', 'Normalmente 10% de retención.\n- Si se retuvo el 10%, el monto se registra en el IEC en retención parcial\n- Si se retuvo el total del IVA, por ser NDF, se registra en el IEC en retención total'),
	(38, NULL, 'IVA retenido chatarra', 'Total del IVA retención. El monto retenido se registra en el IEC en retención total'),
	(39, NULL, 'IVA retenido PPA', 'Total del IVA retención. El monto retenido se registra en el IEC en retención total'),
	(41, NULL, 'IVA retenido construcción', 'Se retiene el total del IVA'),
	(44, NULL, 'Impuesto adicional Art 37 letras e, h, i, l', 'Tasa del 15% en 1era venta.\na) Alfombras, tapices\nb) Casa rodantes\nc) Caviar\nd)Armas de aire o gas'),
	(45, NULL, 'Impuesto adicional Art 37 letra j', 'Tasa del 50% en 1era venta\na) Pirotecnia'),
	(46, NULL, 'IVA retenido oro', 'Retención del 100% del IVA'),
	(47, NULL, 'IVA retenido cartones', 'Retención total'),
	(48, 481, 'IVA retenido frambuesas', 'Retención 14%'),
	(49, NULL, 'Factura de compra sin retención', '0% de retención (hoy utilizada sólo por Bolsa de Productos de Chile, lo cual es validado por el sistema)'),
	(50, NULL, 'IVA de margen de comercialización de instrumentos de prepago', 'Para facturas de venta del contribuyente'),
	(51, NULL, 'Impuesto gas natural comprimido; 1,93 UTM/KM3, Art 1° Ley N° 20.052', 'Para facturas de venta del contribuyente'),
	(52, NULL, 'Impuesto gas licuado de petróleo; 1,40 UTM/M3, Art 1° Ley N° 20.052', 'Para facturas de venta del contribuyente'),
	(53, NULL, 'Impuesto retenido suplementeros Art 74 N° 5 Ley de la Renta', 'Para facturas de venta del contribuyente, retención del 0,5% sobre el precio de venta al público')
;

INSERT INTO dte_referencia_tipo VALUES
	(1, 'Anula documento'),
	(2, 'Corrige texto'),
	(3, 'Corrige montos')
;

COMMIT;
