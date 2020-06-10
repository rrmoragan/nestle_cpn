<?php

if( isset( $_SERVER['HTTP_HOST'] ) ){
	echo "\n acceso denegado";
	return null;
}

include('libs/basics.php');
include('libs/querys.php');
include('libs/magento_sales.php');

function help(){
	$a = array(
		array('concept'=>'-so-delete','descripcion'=>'borra una odern de venta, esta debe esatr cancelada para ser procesada. ejemplo:'),
		array('concept'=>'','descripcion'=>'-so-delete 1000098'),
		array('concept'=>'-op-list','descripcion'=>'lista todas las ordenes de compra registradas por openpay'),
		array('concept'=>'','descripcion'=>'se puede ordenar por status tipo_de_pago'),
		array('concept'=>'','descripcion'=>'   sales.php -op-list completed card'),
		array('concept'=>'','descripcion'=>'   sales.php -op-list all all'),
		array('concept'=>'','descripcion'=>'el argumento all se utiliza para obtener todos los elementos ya sea en status o tipos de pago'),
		array('concept'=>'-op-list-status','descripcion'=>'lista los status disponibles en las ordenes de venta'),
		array('concept'=>'-op-list-types','descripcion'=>'lista los tipos de pago guardados de openpay'),
		array('concept'=>'-op-detail','descripcion'=>'obtiene los detalles de una orden deventa'),
		array('concept'=>'','descripcion'=>'   sales.php -op-detail 100000901'),
	);

	echo "\n sales.php <concepto> <args ... >";
	echo print_table( $a );
	return null;
}

function process_arg( $arg=null ){
	if( $arg==null ){ return false; }

	//print_r( $arg );

	switch ( $arg[1] ) {
		case '-so-delete':
			if( !isset($arg[2]) ){
				echo "\n ... error: no ha sido posible borrar la orden de compra ...\n";
				return false;
			}
			$soi = $arg[2];
			$so = new mSales();
			if( !$so->sales_delete( $soi ) ){
				echo "\n ... error: no ha sido posible borrar la orden de compra [$soi]\n";
				return true;
			}

			echo "\n orden de venta [$soi] borrada\n";
			return true;
			break;
		case '-op-list':
			$opv = new openpayValidate();

			$st = ''; if( isset($arg[2]) ){ $st = $arg[2]; }
			$ty = ''; if( isset($arg[3]) ){ $ty = $arg[3]; }

			$opv->opv_sales_list( $st,$ty );

			$a = null;
			if( $opv->data !=null ){
				foreach ($opv->data as $et => $r) {
					$m = $r['seguimiento'];

					$a[ $m['order_id'] ]['id'] = $r['id'];
					$a[ $m['order_id'] ]['sales'] = $m['order_id'];
					$a[ $m['order_id'] ]['status'] = $m['status'];
					$a[ $m['order_id'] ]['method'] = $m['method'];
					$a[ $m['order_id'] ]['operation_date'] = $m['operation_date'];
					$a[ $m['order_id'] ]['due_date'] = "";
					if( isset( $m['due_date'] ) ){
						$a[ $m['order_id'] ]['due_date'] = $m['due_date'];
					}
					$a[ $m['order_id'] ]['customer_id'] = $m['customer_id'];
					$a[ $m['order_id'] ]['operation_id'] = $m['id'];
					$a[ $m['order_id'] ]['authorization'] = $m['authorization'];
				}

				echo print_table( $a );
			}else{
				echo "\n consulta vacia";
			}

			return true;
			break;
		case '-op-list-status':
			$opv = new openpayValidate();
			echo print_table( $opv->opv_sales_list_status() );

			return true;
			break;
		case '-op-list-types':
			$opv = new openpayValidate();
			echo print_table( $opv->opv_sales_list_type() );

			return true;
			break;
	}

	return false;
}

if( $GLOBALS['argc']==1 ){
	help();
	return null;
}

//print_r($GLOBALS);

if( !process_arg( $GLOBALS['argv'] ) ){
	help();
	return null;
}

?>