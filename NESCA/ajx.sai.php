<?php

$local = '../../';
$local = '';

include($local.'libs/basics.php');
include($local.'libs/querys.php');
include($local.'libs/forceUTF8.php');
include($local.'libs/sai_lib.php');
//include('libs/logs.php');

date_default_timezone_set('America/Mexico_City');

$j = array( 
	'status' => 'error',
	'message' => 'Campo vacio',
	'nivel' => 0
);

//$_GET = array ( "so" => "100000893", "prg" => "NESCA", "envio" => "73", "canje" => "183" ); 
//$_GET = array( "prg" => "NESCA", "envio" => 100, "canje" => 289, "so" => 100000937 );

if( $GLOBALS['argc'] == 1 ){
	echo json_encode( $j );return null;
}

$v = 0;

if( $GLOBALS['argv'][1] == '-v' ){
	print_r( $GLOBALS['argv'] );

	$a = $GLOBALS['argv'];

	$b = explode('&', $GLOBALS['argv'][2]);
	foreach ($b as $et => $r) {
		$c = explode('=', $r);
		$_GET[ $c[0] ] = $c[1];		
	}
	
	print_r($_GET);
	//$v = 1;
}

/* si no lleva argumentos entonces salir */
if( $_GET==null ){echo json_encode( $j );return null;}

/* si faltan variables entonces salir */
$err = 0;
if( !isset( $_GET['so'] ) ){    $err++; }
if( !isset( $_GET['prg'] ) ){   $err++; }
if( !isset( $_GET['envio'] ) ){ $err++; }
if( !isset( $_GET['canje'] ) ){ $err++; }

if( $err>0 ){echo json_encode( $j );return null;}

/*
	cafeparaminegocio.com.mx/NESCA/ajx.code_vendor_list.php?
		prg=NESCA&
		envio=73&
		canje=183&
		so=100000358

	array('-order','procesa una orden de compra especifica al sistema sai'),
	array("   sales_order","orden de venta"),
	array("   program","nombre del programa utilizado para agregar canjes al sistema sai ejemplo: 'NESCX'"),
	array("   envio","número de control de envío"),
	array("   canje","número de control de canje"),
 */

$sai = new Sai_lib();

$sai->set_sales_order( $_GET['so'], $v );
$sai->set_program( $_GET['prg'], $v );
$sai->set_envio( $_GET['envio'], $v );
$sai->set_canje( $_GET['canje'], $v );

$sai->carga($v);

$sai->data_json['insert'] = $sai->data;

echo json_encode( $sai->data_json );
return null;
?>
