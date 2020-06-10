<?php

include('libs/basics.php');
include('libs/forceUTF8.php');
include('libs/logs.php');
include('libs/email_lib.php');

date_default_timezone_set('America/Mexico_City');

$dir = "/var/www/magento/NESCA/";

if( isset( $_SERVER['HTTP_HOST'] ) ){
	echo "\n acceso denegado";
	return null;
}

function help(){
	$b = array(
		array( 'concepto' => '', 	'descripcion' => "-active           obtiene todos los carritos con status activo" ),
		array( 'concepto' => '', 	'descripcion' => "-inactive         obtiene todos los carritos con status inactivo" ),
		array( 'concepto' => '', 	'descripcion' => "-fecha_fin        obtiene todos los carritos hasta la fecha indicada" ),
		array( 'concepto' => '', 	'descripcion' => "-user_null        obtiene todos los carritos que no tienen un usuario asignado" ),
		array( 'concepto' => '', 	'descripcion' => "-user_not_null    obtiene todos los carritos que tienen un usuario asignado" ),
	);
	$a = array(
		array( 'concepto' => '-listar', 'descripcion' => 'muestra reporte' ),
		array( 'concepto' => '-totales', 'descripcion' => 'muestra el total de los carritos abandonados' ),
		//array( 'concepto' => '-vaciar', 'descripcion' => 'limpia el reporte' ),
		array( 'concepto' => '-actualizar', 'descripcion' => 'actualiza el reporte de los carritos abandonados' ),
		array( 'concepto' => '-csv', 'descripcion' => 'genera un archivo csv con el reporte de los carritos abandonados' ),
		array( 'concepto' => '-ublock-list', 'descripcion' => 'lista los usuarios bloqueados para el reporte' ),
		array( 'concepto' => '-ublock email', 'descripcion' => 'bloquea un usuario en el reporte hay que colocar el email del usuario' ),
		array( 'concepto' => '-uactive email', 'descripcion' => 'desbloquea un usuario en el reporte' ),
		array( 'concepto' => '-car_id', 'descripcion' => 'muestra el carrito especificado' ),
		array( 'concepto' => '-car_clean', 'descripcion' => 'limpia carritos abandonados' ),
		array( 'concepto' => '-car_clean_data', 'descripcion' => 'muestra la fecha limite que sera limpiado los carritos' ),
	);

	$a[] = array( 'concepto' => '-car_list', 'descripcion' => 'lista todos los carritos' );
	$a[] = array( 'concepto' => '', 	'descripcion' => "-car_list -active/-inactive -fecha_fin '2019-08-03' -user_null" );
	foreach ($b as $et => $r) { $a[] = $r; }

	$a[] = array( 'concepto' => '-car_disable', 'descripcion' => 'vacia carritos' );
	$a[] = array( 'concepto' => '', 	'descripcion' => "-car_disable -active/-inactive -fecha_fin '2019-08-03' -user_null" );
	foreach ($b as $et => $r) { $a[] = $r; }

	echo "\n generador reporte carritos abandonados.";
	echo print_table( $a );
}

function carritos_listado( $arg=null ){

	$ca = new mCars();

	$i = 0;
	foreach ($arg as $et => $r) {
		if( $i==0 ){ $i++; continue; }
		if( $i==1 ){ $i++; continue; }

		switch ( $r ) {
			case '-active':
				//echo "\n filtro status ==> activo";
				$ca->cars_list_filtro( 'status_active' ); break;
			case '-inactive':
				//echo "\n filtro status ==> inactivo";
				$ca->cars_list_filtro( 'status_inactive' );
				break;
			case '-fecha_fin':
				$ca->cars_list_filtro( 'fecha_fin', $arg[ $et+1 ] );
				break;
			case '-user_null':
				$ca->cars_list_filtro( 'user_null' );
				break;
			case '-user_not_null':
				$ca->cars_list_filtro( 'user_not_null' );
				break;
		}

		$i++;
	}

	$n = $ca->cars_list();
	if( $n==0 ){ return null; }

	$data = $ca->data['data'];
	$ca->data['data'] = null;

	return $data;
}

if( $_SERVER['argc'] <= 1 ){
	help();
	return null;
}

include('libs/magento_cars.php');
include('libs/magento_customer_lib.php');

$arg = $_SERVER['argv'];

switch( $arg[1] ){
	case '-listar':
		echo "\n reporte listando carritos abandonados";
		$ca = new reportCars();
		$ca->report_all_data();
		echo print_table( $ca->data );
		return null;
		break;
	case '-totales':
		$ca = new reportCars();
		echo "\n registros totales ==> ".$ca->regs_total();
		return null;
		break;
	case '-vaciar':
		$ca = new reportCars();
		$ca->delete_regs_all();
		return null;
		break;
	case '-actualizar':
		$ca = new reportCars();
		$ca->update_report();
		return null;
		break;
	case '-csv':
		include('libs/csv.lib.php');

		$ca = new reportCars();
		$ca->report_all_data();
		$data = $ca->filtra_cabs_report( $ca->data );
		$data = $ca->col_to_cols( $data, 'categ' );

		$cabs = '';
		$cabs .= "Reporte,generado automaticamento\n";
		$cabs .= "Portal,cafeparaminegocio.com.mx\n";
		$cabs .= "Fecha,".date( 'Y-m-d G:i:s', time() )."\n";

		$f = new fileCSV();
		$file = '/var/www/magento/NESCA/mlg_reports/'.'carritos_abandonados_'.time();
		echo "\n file generate ==> ".$file.".csv\n";
		$f->save_file( $file, forceLatin1( $data ), $cabs );

		return null;
		break;
	case '-ublock-list':
		$u = new mCustomer();
		$u->list_user_blocked();
		echo print_table( $u->data );

		return null;
		break;
	case '-ublock':
		if (!filter_var($_SERVER['argv'][2], FILTER_VALIDATE_EMAIL)) {
			echo "\n email invalido";
			return null;
		}

		$u = new mCustomer();
		$u->user_block( $_SERVER['argv'][2], 'report' );

		return null;
		break;
	case '-uactive':
		if (!filter_var($_SERVER['argv'][2], FILTER_VALIDATE_EMAIL)) {
			echo "\n email invalido";
			return null;
		}

		$u = new mCustomer();
		$u->user_unblock( $_SERVER['argv'][2], 'report' );
		
		return null;
		break;

	case '-car_disable':
		echo "\n vacia carritos";

		// obteniendo los carritos que coinciden con la busqueda
			$data = carritos_listado( $arg );
			if( $data==null ){
				echo "\n sin datos\n";
				return null;
			}
			$data = array_filter_cols( $data, array(
				'entity_id',
				'updated_at',
				'is_active',
				'items_count',
				'items_qty',
				'reserved_order_id',
				'customer_id',
				'customer_email',
				'grand_total'
			) );

			//echo "\n CARRITOS";
			//echo print_table( $data );

			// contiene todos los entity_id de los carritos
			$d = null;
			foreach ($data as $et => $r) {
				$d[ $et ] = $r['entity_id'];
			}

			//print_r($d);
		// obteniendo los items asociados a los carritos seleccionados
			$ca = new mCars();
			$data_items = null;

			foreach ($d as $et => $r) {
				if( $data[ $et ]['items_count'] == 0 ){ continue; }
				if( $data[ $et ]['is_active'] == 0 ){ continue; }

				//echo print_table( $data[$et] );

				$n = $ca->car_items( $r );
				if( $n==0 ){ continue; }

				$ca->data = array_filter_cols( $ca->data, array(
					'quote_id',
					'item_id',
					//'product_id',
					//'parent_item_id',
					'sku',
					'codigo_barras',
					'qty',
					'price',
					'discount_amount',
					'tax_amount',
					'row_total',
					'row_total_incl_tax',
				) );

				//echo print_table( $ca->data,25 );

				foreach ($ca->data as $et => $r) {
					$data_items[] = $r;
				}
			}

			//echo "\n PRODUCTOS";
			//echo print_table( $data_items,25 );
		// sumando items
			$list_items = null;
			if( $data_items ){
				foreach ($data_items as $et => $r) {
					$list_items[ $r['codigo_barras'] ] = 0;
				}
				foreach ($data_items as $et => $r) {
					$list_items[ $r['codigo_barras'] ] += $r['qty'];
				}

				echo "\n CANTIDADES";
				print_r( $list_items );
			}

			unset($data_items);
		// desabilitando carritos
			$ca = new mCars();

			echo "\n desabilitando carritos ==> ";
			foreach ($data as $et => $r) {
				/*echo "\n desabilitando carrito [".$r['entity_id']."] ".
					"productos [".$r['items_count']."] ".
					"customer [".$r['customer_id']."] ".
					"grand_total [".$r['grand_total']."] ".
					"";*/
				echo ".";
				$ca->car_disable( $r['entity_id'] );
			}
		// incrementando stock
			if( $list_items==null ){
				echo "\n";
				return null;
			}

			echo "\n INCREMENTANDO STOCK EN PRODUCTOS";


		echo "\n";
		return null;
		break;
	case '-car_list':
		echo "\n lista todos los carritos";

		$data = carritos_listado( $arg );

		if( $data == null ){
			echo "\n listado vacio";
			return null;
		}

		$data = array_filter_cols( $data, array(
			'entity_id',
			'updated_at',
			'is_active',
			'items_count',
			'items_qty',
			'reserved_order_id',
			'customer_id',
			'customer_email',
			'grand_total'
		) );

		echo print_table( $data );

		return null;
		break;
	case '-car_id':
		echo "\n muestra un carrito especifico";

		if( !isset( $arg[2] ) ){
			echo "\n faltan argumentos";
			echo help();
			return null;
		}

		$ca = new mCars();
		if( !$ca->car_id( $arg[2] ) ){
			echo "\n carrito no encontrado";
			return null;
		}

		$data = array_filter_cols( $ca->data, array(
			'entity_id',
			'updated_at',
			'is_active',
			'items_count',
			'items_qty',
			'reserved_order_id',
			'customer_id',
			'customer_email',
			'grand_total'
		) );
		echo print_table( $data );

		$data = array_filter_cols( $ca->items, array(
			'item_id',
			'quote_id',
			'product_id',
			'parent_item_id',
			'codigo_barras',
			'qty',
			'price',
			'discount_amount',
			'tax_amount',
			'product_type',
			'row_total_incl_tax',
			'tax_amount'
		) );
		echo print_table( $data );
		return null;

		break;
	case '-car_clean_data':
		echo "\n muestra la fecha limite para limpiar los carritos";

		$ca = new mCars();
		echo "\n limpiar carritos hasta: ".( $ca->clean_date() )."\n";
		return null;

		break;
	case '-car_clean':
		// listando carritos
			$ca = new mCars();
			$t = $ca->clean_date();

			$data = carritos_listado( array(
				null,
				null,
				'-car_list',
				'-active',
				'-fecha_fin',
				$t,
			) );

			if( $data == null ){
				echo "\n listado sin resultados";
				$s = "\n carritos vaciados ==> 0";
				log_data( $dir.'log/carritos_clean', $s );
				return null;
			}

			$data = array_filter_cols( $data, array(
				'entity_id',
				'updated_at',
				'is_active',
				'items_count',
				'items_qty',
				'reserved_order_id',
				'customer_id',
				'customer_email',
				'grand_total'
			) );

			echo print_table( $data );

			$d = null;
			foreach ($data as $et => $r) {
				$d[ $et ] = $r['entity_id'];
			}
		// listando items de los carritos
			$data_items = null;

			foreach ($d as $et => $r) {
				if( $data[ $et ]['items_count'] == 0 ){ continue; }
				if( $data[ $et ]['is_active'] == 0 ){ continue; }

				//echo print_table( $data[$et] );

				$n = $ca->car_items( $r );
				if( $n==0 ){ continue; }

				$ca->data = array_filter_cols( $ca->data, array(
					'quote_id',
					'item_id',
					//'product_id',
					//'parent_item_id',
					'sku',
					'codigo_barras',
					'qty',
					'price',
					'discount_amount',
					'tax_amount',
					'row_total',
					'row_total_incl_tax',
				) );

				//echo print_table( $ca->data,25 );

				foreach ($ca->data as $et => $r) {
					$data_items[] = $r;
				}
			}

			echo print_table( $data_items );

		// sumando items
			$list_items = null;
			if( $data_items ){
				foreach ($data_items as $et => $r) {
					$list_items[ $r['codigo_barras'] ] = 0;
				}
				foreach ($data_items as $et => $r) {
					$list_items[ $r['codigo_barras'] ] += $r['qty'];
				}

				$s = "\n CANTIDADES ==> ".print_r( $list_items,true );
				log_data( $dir.'log/carritos_clean', $s );
			}

			unset($data_items);
		// desabilitando carritos
			$ca = new mCars();

			echo "\n desabilitando carritos ==> ";
			foreach ($data as $et => $r) {
				/*echo "\n desabilitando carrito [".$r['entity_id']."] ".
					"productos [".$r['items_count']."] ".
					"customer [".$r['customer_id']."] ".
					"grand_total [".$r['grand_total']."] ".
					"";*/
				echo ".";
				$ca->car_disable( $r['entity_id'] );
			}
		return null;
		break;
}

echo help();

?>