<?php

$ruta = '';

include('libs/magento_sales.php');
include('libs/csv.lib.php');
include('libs/forceUTF8.php');

date_default_timezone_set('America/Mexico_City');

define('REPORTS','/var/www/magento/NESCA/mlg_reports/');

function help(){
	echo "\n Generador de reporte de ventas v1.3";
	$a = array(
		array( 'opcion' => '-reset', 'description' => 'resetea el reporte de ventas' ),
		array( 'opcion' => '-update', 'description' => 'actualiza el reporte de ventas' ),
		array( 'opcion' => '-export', 'description' => 'exporta el reporte de ventas a un archivo csv' ),
		array( 'opcion' => '-view', 'description' => 'muestra el reporte de ventas' ),
		array( 'opcion' => '-sales', 'description' => 'muestra una orden de venta' ),
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
	);

	echo print_table( $a );
	echo "\n";
	return null;
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
			$rv->sales( $arg[2] );
			echo print_table( $rv->data );

			echo "\n";
			return true;
			break;
		case '-export':
			$csv = new fileCSV();
			$rp = new reportVentas();

			$rp->export_sumary();

			$name = "CPMN_envios_".date( 'Y-m-d', time() );
			$csv->save_file( REPORTS.$name, forceLatin1($rp->data) );
			echo "\n file [$name] [ok]";

			$rp->export_gral();

			$name = "CPMN_gral_".date( 'Y-m-d', time() );
			$csv->save_file( REPORTS.$name, forceLatin1($rp->data) );
			echo "\n file [$name] [ok]";

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