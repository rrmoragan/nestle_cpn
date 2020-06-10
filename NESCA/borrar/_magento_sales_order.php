<?php

include('libs/basics.php');
include('libs/querys.php');
include('libs/magento_lib.php');

function help(){

	$s = "\nmagento_sales_order.php <opcion ...> ";
	echo $s;

	$a = array(
		array('opcion'=>'-order-list','descripcion'=>'lista todas las ordenes de compra hechas en un sistema magento.'),
		array('opcion'=>'-order-sum status','descripcion'=>'suma todas las ordenes de compra con un status especifico.'),
		array('opcion'=>'-order-status-change status1 status2','descripcion'=>'cambia a status2 todas las ordenes de compra con status1.'),
		array('opcion'=>'-order-list-status','descripcion'=>'lista los diferentes status que puede tener una orden de compra.'),
		array('opcion'=>'-order-delete','descripcion'=>'borra todas las ordenes de compra con status canceled.'),
		array('opcion'=>'-order-id-change sales_order atributo valor','descripcion'=>'cambia un atributo de una orden de compra.'),
	);

	echo print_table($a);
	return null;
}

if( $GLOBALS['argc']==1 ){
	help();
	return true;
}

if( $GLOBALS['argc']>1 ){
	
	$so = new Magento_Lib();

	switch ( $GLOBALS['argv'][1] ) {
		case '-order-list':
			$order = '';
			if( isset( $GLOBALS['argv'][2] ) ){ 
				$order = $GLOBALS['argv'][2];
			}
			$so->all_list( $order );
			break;
		case '-order-sum': 
			if( !empty($GLOBALS['argv'][2]) ){
				$so->sum_order( $GLOBALS['argv'][2] );
			}
			break;
		case '-order-status-change':
			$err = 0;
			if( !isset( $GLOBALS['argv'][2] ) ){ $err++; }
			if( !isset( $GLOBALS['argv'][3] ) ){ $err++; }
			if($err){ help(); return false; }

			$so->order_change_status( $GLOBALS['argv'][2], $GLOBALS['argv'][3] );

			break;
		case '-order-list-status': $so->order_list_status(); break;
		case '-order-delete': $so->order_delete(); break;
		case '-order-id-change':
			$err = 0;
			if( !isset( $GLOBALS['argv'][2] ) ){ $err++; }
			if( !isset( $GLOBALS['argv'][3] ) ){ $err++; }
			if( !isset( $GLOBALS['argv'][4] ) ){ $err++; }
			if($err){ help(); return false; }

			$so->order_id_change( $GLOBALS['argv'][2], $GLOBALS['argv'][3], $GLOBALS['argv'][4] );

			break;
		default: help(); return true;
	}

	return true;
}

return false;

?>
