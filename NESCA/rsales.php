<?php

//$ruta = '../';
$ruta = '';

include($ruta.'libs/magento_sales.php');
include($ruta.'libs/csv.lib.php');
include($ruta.'libs/forceUTF8.php');

date_default_timezone_set('America/Mexico_City');

define('REPORTS','/var/www/magento/NESCA/mlg_reports/');

function help(){
	echo "\n Generador de reporte de ventas v1.3";
	$a = array(
		//array( 'opcion' => '-reset', 'description' => 'resetea el reporte de ventas' ),
		array( 'opcion' => '-list', 'description' => 'lista las ordenes de venta' ),
		array( 'opcion' => '-update', 'description' => 'actualiza el reporte de ventas' ),
		array( 'opcion' => '-export', 'description' => 'exporta el reporte de ventas a un archivo csv' ),
		//array( 'opcion' => '-view', 'description' => 'muestra el reporte de ventas' ),
		array( 'opcion' => '-sales', 	'description' => 'muestra una orden de venta' ),
		array( 'opcion' => '', 			'description' => '-sales sales_order' ),
		array( 'opcion' => '-rrsales', 	'description' => 'muestra un registro del reporte de ventas' ),
		array( 'opcion' => '', 			'description' => '-rrsales sales_order' ),
		array( 'opcion' => '-recalc', 'description' => 'marca una orden de venta para ser recalculada, posteriormente con un -update se actualizan los datos.' ),
		array( 'opcion' => '-product', 'description' => 'busca un producto por su sku dentro del reporte' ),

		array( 'opcion' => '-lvalid', 'description' => 'lista los sales order que no seran recalculados en una actualizacion' ),
		array( 'opcion' => '-addvalid', 'description' => 'agrega un sales order para que no sea recalculado en la actualizacion' ),
		array( 'opcion' => '', 'description' => '-addvalid 100000933' ),
		array( 'opcion' => '-removevalid', 'description' => 'quita un sales order para que no sea recalculado en la actualizacion' ),
		array( 'opcion' => '', 'description' => '-removevalid 100000933' ),
		array( 'opcion' => '-shipping', 'description' => 'modifica el costo de envio' ),
		array( 'opcion' => '', 'description' => '-shipping sales_order valor' ),
		array( 'opcion' => '-margen', 'description' => 'modifica el margen total para una orden de venta en el reporte de ventas' ),
		array( 'opcion' => '', 'description' => '-margen sales_order valor' ),
		array( 'opcion' => '-view_diff', 'description' => 'muestra las diferencias entre los registros' ),
		array( 'opcion' => '-delete', 	'description' => 'borra un registro de ventas, solo se pueden borrar ventas canceladas' ),
		array( 'opcion' => '', 			'description' => '-delete sales_order' ),

        array( 'opcion' => '-user_sales', 'description' => 'lista todos los usuarios que han realizado al menos 1 compra ordenados en forma descandente' ),
        array( 'opcion' => '', 'description' => '-user_sales -csv ==> exporta a csv el listado' ),
        array( 'opcion' => '-user_sales_none', 'description' => 'lista todos los usuarios que no han realizado compras' ),
        array( 'opcion' => '', 'description' => '-user_sales -csv ==> exporta a csv el listado' ),
        array( 'opcion' => '-sales_product', 'description' => 'lista todas las ordenes de compra que tengan los productos seleccionados' ),
        array( 'opcion' => '', 'description' => '-csv     exporta a csv el listado' ),
        array( 'opcion' => '', 'description' => '-sales_product -csv sku  sku  sku ... ' ),
	);

	echo print_table( $a );
	echo "\n";
	return null;
}

function filename( $s='' ){
	$s = $s.date( 'Y-m-d', time() );
	return $s;
}

function process(){
	$arg = $_SERVER['argv'];
	$v = true;

	switch ( $arg[1] ) {
		case '-update':
			$rv = new reportVentas();
			$rv->update();
			echo "\n\n verificando";

			$recalc = $rv->rv_verify();
			if( $recalc>0 ){
				$rv->update();
			}

			return true;
			break;
		case '-sales':
			$rv = new mSales();
			$n = $rv->sales( $arg[2] );

			if( $n==0 ){
				echo "\n sin datos";
				return true;
			}
			foreach ($rv->data as $et => $r) {
				$a = null;
				foreach ($r as $etr => $rr) {
					$a[] = array( 'campo' => $etr, 'valor' => $rr );
				}
				echo print_table( $a );
			}

			echo "\n";
			return true;
			break;
		case '-rrsales':
			$rv = new reportVentas();
			$rv->rv_sales( $arg[2] );
			$rv->rv_sales_items();

			$items = $rv->data['items'];
			unset( $rv->data['items'] );

			$a = null;
			foreach ($rv->data as $et => $r) {
				$a[] = array(
					'campo' => $et,
					'valor' => $r
				);
			}

			echo print_table( $a );
			echo print_table( ($items) );

			echo "\n";
			return true;
			break;
		case '-export':
			$csv = new fileCSV();
			$rp = new reportVentas();

			$rp->export_sumary();

				$file = filename( "CPMN_envios_" );
				if( $rp->data == null ){ echo "\n reporte vacio $file"; return true; }
				@unlink( REPORTS.$file.'.csv' );

				$csv->save_file( REPORTS.$file, forceLatin1( $rp->data ) );
				echo "\n ==> ".str_pad("$file ",30,'.')." [ok]";

			$rp->export_gral();

				$file = filename( "CPMN_gral_" );
				if( $rp->data == null ){ echo "\n reporte vacio $file"; return true; }
				@unlink( REPORTS.$file.'.csv' );

				$csv->save_file( REPORTS.$file, forceLatin1( $rp->data ) );
				echo "\n ==> ".str_pad("$file ",30,'.')." [ok]";

			echo "\n";
			return true;
			break;
		case '-recalc':
			$rp = new reportVentas();
			$rp->disable( $arg[2] );
			return true;
			break;
		case '-product':
			$rp = new reportVentas();
			$rp->search_sku( $arg[2] );
			echo print_table( $rp->data );
			return true;
			break;
		case '-lvalid':
			$rp = new reportVentas();
			$lista = $rp->list_sales_no_valid();
			if( $lista==null ){
				echo "\n listado vacio";
				return true;
			}
			
			echo print_table( $lista );
			return true;
			break;
		case '-addvalid':
			$rp = new reportVentas();
			$id = $rp->add_sales_no_valid( $arg[2] );
			if( $id==0 ){
				echo "\n no se pudo agregar el sales order";
				return true;
			}

			echo "\n sales order bloqueado";
			return true;
			break;
		case '-removevalid':
			$rp = new reportVentas();
			if( !$rp->remove_sales_no_valid( $arg[2] ) ){
				echo "\n no se pudo desbloquear el sales order";
				return true;
			}

			echo "\n sales order desbloqueado";
			return true;
			break;
		case '-shipping':
			$rp = new reportVentas();
			if( isset( $arg[2] ) && isset( $arg[3] ) ){
				$rp->shipping_recalc( $arg[2], $arg[3] );
				echo print_table( $rp->data );

				return true;
			}
			
			return false;
			break;
		case '-margen':
			$rp = new reportVentas();
			if( isset( $arg[2] ) && isset( $arg[3] ) ){
				$rp->margen_update( $arg[2], $arg[3] );
				echo print_table( $rp->data );

				return true;
			}
			
			return false;
			break;
		case '-view_diff':
			$rp = new reportVentas();
			$diff  = $rp->debug_diferences();
			$lista = $rp->list_sales_no_valid();
			if( $diff == null ){
				echo "\n registros vacios";
				return true;
			}

			foreach ($diff as $et => $r) {
				$diff[ $et ]['bloked'] = '';
				if( $r['diff'] == 0 ){
					$diff[ $et ]['diff'] = '';
				}
				if( isset( $lista[ $r['sales_order'] ] ) ){
					$diff[ $et ]['bloked'] = 'X';
				}
			}

			unset( $lista );

			$diff = array_filter_cols( $diff, array(
				'rs_id',
				'sales_order',
				'status',
				'total_item_count',
				'so_qty',
				'diff',
				'bloked',
				'updated_at',
			) );

			echo print_table( $diff );

			return true;
			break;
		case '-list':
			$so = new mSales();
			$n1 = $so->list_sales();

			if( $n1==0 ){ echo "\n sin ordenes de venta"; return true; }

			$so->data = array_filter_cols( $so->data, array(
				'entity_id',
				'status',
				'increment_id',
				//'coupon_code',
				//'protect_code',
				//'shipping_description',
				//'shipping_method',
				//'discount_description',
				//'store_id',
				
				//'shipping_amount',
				//'shipping_discount_amount',
				//'shipping_tax_amount',
				//'shipping_incl_tax',
				//'subtotal',
				//'subtotal_incl_tax',
				//'discount_amount',
				//'discount_coupon_amount',
				//'tax_amount',
				'grand_total',
				//'total_due',
				
				//'customer_id',
				'customer_email',
				//'customer_firstname',
				//'customer_lastname',

				'total_item_count',
				'total_qty_ordered',

				'customer_group_id',
				//'email_sent',
				//'quote_id',
				//'payment_authorization_amount',


				//'remote_ip',

				//'x_forwarded_for',
				'created_at',
				//'updated_at',
				
				'billing_address_id',
				'shipping_address_id'
			) );

			echo "\n ordenes totales ==> ".count($n1);
			echo print_table( $so->data );
			echo "\n ordenes totales ==> ".count($n1);
			return true;
			break;

		case '-user_sales':
			// lista todos los usuarios que han realizado al menos 1 compra ordenados en forma descandente
			return true;
			break;
		case '-user_sales_none':
			// lista todos los usuarios que no han realizado compras
			return true;
			break;
		case '-sales_product':
			// lista todas las ordenes de compra que tengan los productos seleccionados
			return true;
			break;
	}

	return false;
}

/**
 * flujo principal del sistema
 */

	//echo "\n ==> paso 1";
	if( !isset( $_SERVER['TERM'] ) ){
		echo "\n acceso restringido";
		return null;
	}

	//echo "\n ==> paso 3";
	if( $_SERVER['argc'] == 1 ){ help(); return null; }

	//echo "\n ==> paso 4";
	if( process() ){ return null; }

	//echo "\n ==> paso 5";
	help();
	return null;

?>
