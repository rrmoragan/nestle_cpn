<?php

date_default_timezone_set('America/Mexico_City');

function help(){
	echo "\nfactura_unic.php serie folio_ini sales_order";
	echo "\n\t ejemplo:";
	echo "\n\t factura_unic.php NESCX 23 1000125";
	echo "\n";

	return null;
}

//	print_r($GLOBALS);

if( $argv == null ){ echo help(); return null; }
if( count($argv)==1 ){ echo help(); return null; }

include('libs/factura_lib.php');

$factura = new FacturaIn1();
$l = null;
/*
$l = $factura->list_purchase_orders_to_invoice();
if( $l==null ){
	tt('sin registros a facturar');
	return null;
}*/

$serie = 'NESCX';
$folio_ini = 1;
$sales_order = null;

$serie = $argv[1];
$folio_ini = $argv[2];
$sales_order = $argv[3];

if( $serie == '' ){ tt('serie no valida'); return null; }
if( $folio_ini == '' ){ tt('folio invalido'); return null; }
if( $sales_order == null ){ tt('orden de venta no identificado'); return null; }


$factura->serie = $serie;
$factura->folio_ini = $folio_ini;

if( !$factura->new_billing( $sales_order, 'facturas_data/' ) ){
	tt( $factura->error );
}

/*
foreach ($l as $et => $r) {
	if( !$factura->new_billing( $r, 'facturas_data/' ) ){
		tt( $factura->error );
	}
}*/

/* tablas

	CREATE TABLE `facturacion_invoices` (
	  `id` int(11) NOT NULL,
	  `serie` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
	  `folio_id` int(11) NOT NULL,
	  `consecutivo` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
	  `order_id` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
	  `total` float NOT NULL,
	  `rfc` varchar(14) COLLATE utf8_unicode_ci NOT NULL,
	  `cfdi` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
	  `business_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `billing_method` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	  `customer_id` int(11) NOT NULL,
	  `customer_email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	  `status` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
	  `creating` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
	  `last_update` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
	  `creating_time` int(11) NOT NULL,
	  `last_update_time` int(11) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='facturacion listado';

	CREATE TABLE `facturacion_invoices_data` (
	  `id` int(11) NOT NULL,
	  `consecutivo` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
	  `cab` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	  `reg` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
	  `data` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `status` int(11) NOT NULL DEFAULT '1'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='facturacion datos';


	ALTER TABLE `facturacion_invoices`
	  ADD PRIMARY KEY (`id`),
	  ADD UNIQUE KEY `factura_folio` (`folio_id`),
	  ADD UNIQUE KEY `folio_consecutivo` (`consecutivo`);

	ALTER TABLE `facturacion_invoices_data`
	  ADD PRIMARY KEY (`id`);

	ALTER TABLE `facturacion_invoices` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
	ALTER TABLE `facturacion_invoices_data` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

 */

?>
