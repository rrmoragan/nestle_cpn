<?php

include('libs/basics.php');
include('libs/querys.php');
include('libs/logs.php');
include('libs/forceUTF8.php');
include('libs/sai_lib.php');

date_default_timezone_set('America/Mexico_City');

if($_GET){
	print_r($_GET);
}

function help(){
	echo "\n sai <process> <arg...>";
	$a = array(
		array('-all','procesa todas las ordenes de compra que no han sido enviadas al sistema sai'),
		array('-order','procesa una orden de compra especifica al sistema sai'),
		array("   sales_order","orden de venta"),
		array("   program","nombre del programa utilizado para agregar canjes al sistema sai ejemplo: 'NESCX'"),
		array("   envio","número de control de envío"),
		array("   canje","número de control de canje"),
		array("-program-list","lista el status de los programas dados de alta"),
	);
	echo print_table($a);
	echo "\n";

	return null;
}

function valid_arg(){
	if( $GLOBALS['argc']==1 ){ return false; }

	$d = $GLOBALS['argv'];

	switch ( $d[1] ) {
		case '-all':  break;
		case '-order': 
			$n = count( $d );
			if( 6>$n ){ return false; }

			return array(
				'process' => 'order',
				'sales_order' => $d[2],
				'program' => $d[3],
				'envio' => $d[4],
				'canje' => $d[5],
				'sql' => isset($d[6])?$d[6]:0,
			); break;
		case '-program-list':
			return array( 'process' => 'program-list' );
			break;
	}

	return false;
}

if( !valid_arg() ){
	help();
	return null;
}

$op = valid_arg();

$sai = new Sai_lib();

switch ( $op['process'] ) {
	case 'all': return null; break;
	case 'order':
		$sai->set_sales_order( $op['sales_order'] );
		$sai->set_program( $op['program'] );
		$sai->set_envio( $op['envio'] );
		$sai->set_canje( $op['canje'] );

		$sai->carga();

		//log_data('log/sai-insert', $sai->data);

		print_r( $sai->sales_order_data );

		break;
	case 'program-list':
		$sai->program_status();
		break;
}

?>