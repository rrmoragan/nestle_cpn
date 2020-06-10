<?php

include('libs/basics.php');
include('libs/codevendorcheckout.php');

if( system_user() == 'nginx' ){ echo 'acceso denegado'; return null; }

function help(){
	$s = "\n code_vendor_checkout.php <opcions>";

	$a = array(
		array('arg'=>'-list','descripcion'=>'lista todos los registros'),
		array('arg'=>'-list_vendor','descripcion'=>'lista los vendedores dados de alta'),
		array('arg'=>'-vendor_search ...','descripcion'=>'muestra los datos de un codigo de vendedor donde ... es el codigo del vendedor'),
		array('arg'=>'-vendor_new ...','descripcion'=>'agrega un codigo de vendedor nuevo donde ... son nombre codigo programa'),
		array('arg'=>'-sales','descripcion'=>'indexa los codigos d evendedor con las ventas realizadas'),
		array('arg'=>'-update','descripcion'=>'agrega un sales order a un codigo de vendedor'),
		array('arg'=>'','descripcion'=>'-update sales_order  gvo_id (registro id)'),
	);

	$s = $s.print_table($a);
	echo $s;
	return null;
}

date_default_timezone_set('America/Mexico_City');

$d = $GLOBALS['argc'];

if($d==1){ help(); return false; }

$d = $GLOBALS['argv'];

$logf = "log/vendor_code";
$cvc = new codeVendorCheckout();
$cvc->log_file = $logf = "log/vendor_code_check";

switch ( $d[1] ) {
	case '-list':
		$cvc->cv_list();
		if( !$cvc->data ){
			echo "\nsin registros\n";
		}
		echo print_table( $cvc->data );
		return true;
		break;
	case '-sales':
		$cvc->cv_list_no_sales();
		$cvc->cv_add_sales();
		return true;
		break;
	case '-list_vendor':
		$cvc->cv_list_vendor();
		echo print_table( $cvc->data );
		return true;
		break;
	case '-vendor_search':
		$cvc->cv_vendor_search( $d[2] );
		if( $cvc->data != null ){
			echo print_table( $cvc->data );
		}else{
			echo "\n vendedor no encontrado";
		}
		return true;
		break;
	case '-vendor_new':
		if( $cvc->cv_vendor_new( $d[2], $d[3], $d[4] ) ){
			echo "\n vendedor agregado\n";
		}else{
			echo "\n vendedor ya existe\n";
		}
		return true;
		break;
	case '-update':
		$cvc->set_cv_sales_order( $d[2], $d[3] );
		echo print_table( $cvc->data );
		return true;
		break;
}

help();
return false;

?>