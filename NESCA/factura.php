<?php

$ruta = '';
//$ruta = '../';

include( $ruta.'libs/basics.php' );
include( $ruta.'libs/factura_lib.php' );
define( 'DIRF','log/' );

date_default_timezone_set('America/Mexico_City');

function help(){

	$a = array(
		array( 'process' => 'forma de uso', 	'descrip' => 'factura.php -new [ serie ] [ -folio ] [ sales_order ]' ),
		array( 'process' => '-h', 				'descrip' => 'precenta esta ayuda' ),
		array( 'process' => '--help', 			'descrip' => 'precenta esta ayuda' ),
		array( 'process' => '-new', 			'descrip' => 'crea una nueva factura' ),
		array( 'process' => '  [serie]', 		'descrip' => 'es el numero de serie' ),
		//array( 'process' => '  [folio]', 		'descrip' => 'numero consecutivo e irrepetible que tendra la factura' ),
		array( 'process' => '  [sales_order]', 	'descrip' => 'orden de compra a generar la factura' ),
		array( 'process' => '  -force', 		'descrip' => 'genera la factura aunque no este pagada o aunque ya se haya hecho previamente.' ),
		array( 'process' => '  -folio', 		'descrip' => 'configura el numero de folio a utilizar para la generacion de la factura' ),
		array( 'process' => '-list', 			'descrip' => 'lista las facturas generadas' ),
		array( 'process' => '-sols',			'descrip' => 'lista las 25 ultimas ordenes de venta con solicitudes de facturacion' ),
		array( 'process' => '  -all',			'descrip' => 'genera el listado completo' ),
		array( 'process' => '-add',				'descrip' => 'agrega registro in1 manuelamente' ),
		array( 'process' => '',					'descrip' => '-add' ),
		array( 'process' => '',					'descrip' => "     -sales_order ..." ),
		array( 'process' => '',					'descrip' => "     -serie ..." ),
		array( 'process' => '',					'descrip' => "     -folio ..." ),
		array( 'process' => '',					'descrip' => "     -rfc ..." ),
		array( 'process' => '',					'descrip' => "     -email ..." ),
		array( 'process' => '',					'descrip' => "     -razon_social ..." ),
		array( 'process' => '',					'descrip' => "     -metodo_de_pago ..." ),
		array( 'process' => '',					'descrip' => "     -uso_cfdi ..." ),
		array( 'process' => '',					'descrip' => "" ),
		array( 'process' => '',					'descrip' => "-add -sales_order ... -serie ... -folio ... -rfc ... -email ... -razon_social ... -metodo_de_pago ... -uso_cfdi ..." ),
		array( 'process' => '',					'descrip' => "" ),
		array( 'process' => '-rsales',			'descrip' => 'actualiza el reporte de ventas con las notas facturadas' ),
		array( 'process' => '-remove',			'descrip' => 'quita el registro de una factura y el archivo in1' ),
		array( 'process' => '',					'descrip' => '-remove sales_order' ),
		array( 'process' => '-add_xml',			'descrip' => 'agrega el archivo xml al registro de factura' ),
		array( 'process' => '',					'descrip' => '-add_xml sales_order archivo_xml_sin_extencion' ),
		array( 'process' => '-sales',			'descrip' => 'trae los datos de facturacion de una orden de ventas' ),
		array( 'process' => '',					'descrip' => '-sales sales_order' ),
	);

	echo "\n Programa para generar archivos in1 \n".print_table( $a )."\n";

	return null;
}

function facturas_list( $cfactura, $list ){

	if( !$cfactura->list_in1() ){
		echo "\n sin datos";
		return true;
	}

	foreach ($cfactura->data as $et => $r) {
		$cfactura->data[ $et ]['xml'] = '';
		$cfactura->data[ $et ]['user'] = $cfactura->data[ $et ]['user_id'];
		if( $r['file_xml'] != '' ){
			$cfactura->data[ $et ]['xml'] = 'ok';
		}
	}
	foreach ($list as $et => $r) {
		if( $r=='file_xml' ){ unset( $list[ $et ] ); }
		if( $r=='user_id' ){ unset( $list[ $et ] ); }
	}
	$list[] = 'user';
	$list[] = 'xml';

	$cfactura->data = array_filter_cols( $cfactura->data, $list );

	echo print_table( $cfactura->data );

	return true;
}

function process( $arg=null ){
	if( $arg==null ){ return false; }

	$factura = new FacturaIn1();

	$list_elems = array(
		'fid',
		'sales',
		'serie',
		'folio',
		'rfc',
		//'rz',
		'cfdi',
		'metodo',
		'email',
		'user_id',
		'update_at',
		'file_in1',
		'file_xml',
		//'file_pdf'
	);
	
	switch ( $arg[1] ) {
		case '-remove':
			if( !isset( $arg[2] ) ){
				return false;
			}
			$factura->remove( $arg[2] );
			echo $factura->error_print();

			if( $factura->data ){
				foreach ($factura->data as $et => $r) {
					echo "\n .... delete ==> ".$r['file_in1'];
					@unlink( DIRF.$r['file_in1'] );
				}
			}

			echo "\n";
			return true;
			break;
		case '-add_xml':

			// obteniendo registros actuales
				$factura->list_in1();
				$l1 = $factura->all_in1_to_titano( $factura->data );	
			// listando archivos
				$l2 = list_files_array( 'facturas_data' );
			// filtrando todos los xml
				foreach ($l2 as $et => $r) {
					if( $r['type'] == 'dir' ){ unset( $l2[ $et ] ); }
					if( $r['type'] == 'file' ){
						$a = explode('.', $r['name'] );
						$n = count( $a );
						if( $a[ $n-1 ] != 'xml' ){
							unset( $l2[ $et ] );
						}
					}
				}
			// filtrando con los in1 generados
				$l3 = null;
				foreach ($l1 as $et => $r) {
					foreach ($l2 as $etr => $rr) {
						$s = substr( $rr['name'], 0, strlen( $r ) );
						if( $s == $r ){
							$l3[ $et ] = $rr['name'];
							unset( $l2[ $etr ] );
						}
					}
				}
			// formateando archivos
				foreach ($l3 as $et => $r) {
					$l3[ $et ] = substr($r, 0,-4);
				}
			// agregando archivos xml
				foreach ($l3 as $et => $r) {
					$factura->xml_update( $et,$r );
				}

			return facturas_list( $factura, $list_elems );
			break;
		case '-rsales':
			$factura->rsales_update();
			return true;
			break;
		case '-add':
			$error = 0;

			$data = array(
				'sales' => null,
				'serie' => null,
				'folio' => null,
				'rfc' => null,
				'email' => null,
				'razon_social' => null,
				'metodo_pago' => null,
				'CFDI' => null,
			);
			$data_error = array(
				'-sales_order' 		=> 'error falta orden de venta',
				'-serie' 			=> 'error falta numero de serie',
				'-folio' 			=> 'error falta numero de folio',
				'-rfc' 				=> 'error falta rfc',
				'-email' 			=> 'error falta email',
				'-razon_social' 	=> 'error falta razon social',
				'-metodo_de_pago' 	=> 'error falta metodo de pago',
				'-uso_cfdi' 		=> 'error falta uso cfdi',
			);

			$i = 0;
			foreach ($arg as $et => $r) {
				if( $i==0 || $i==1 ){ $i++; continue; }

				switch( $r ){
					case '-sales_order': 	
						$data['sales'] = $arg[ $i+1 ];
						unset( $data_error[ $r ] );
						break;
					case '-serie': 			
						$data['serie'] = $arg[ $i+1 ];
						unset( $data_error[ $r ] );
						break;
					case '-folio': 			
						$data['folio'] = $arg[ $i+1 ];
						unset( $data_error[ $r ] );
						break;
					case '-rfc': 			
						$data['rfc'] = $arg[ $i+1 ];
						unset( $data_error[ $r ] );
						break;
					case '-email': 			
						$data['email'] = $arg[ $i+1 ];
						unset( $data_error[ $r ] );
						break;
					case '-razon_social': 	
						$data['razon_social'] = $arg[ $i+1 ];
						unset( $data_error[ $r ] );
						break;
					case '-metodo_de_pago': 
						$data['metodo_pago'] = $arg[ $i+1 ];
						unset( $data_error[ $r ] );
						break;
					case '-uso_cfdi': 		
						$data['CFDI'] = $arg[ $i+1 ];
						unset( $data_error[ $r ] );
						break;
					case '-force': 			
						$factura->force_on();
						break;
				}
				$i++;
			}

			print_r( $data );

			$err = 0;
			foreach ($data as $et => $r) {
				if( $r==null ){ $err++; }
			}

			if( $err>0 ){
				foreach ($data_error as $et => $r) {
					echo "\n ... $r";
				}
				echo "\n";
				return true;
			}

			$factura->in1_save_data_force( $data );
			$factura->new_billing_depure();

			$file = 'error-'.$arg[2].'.log';

			if( $factura->error ){
				echo "\n error log: ".DIRF.$file;
				echo $factura->error_print();
			}else{
				output_file( DIRF, $file, 'factura ==> '.$arg[2].' ==> procesado correctamente', 'w' );
			}

			return true;
			break;
		case '-new':
			$serie = ''; 	if( isset( $arg[2] ) ){ $serie 	= $arg[2]; }
			$so = '';	 	if( isset( $arg[3] ) ){ $so 	= $arg[3]; }
			$force = false;
			$folio = 0;

			if( isset( $arg[ 4 ] ) ){ if( $arg[ 4 ] == '-force' ){ $force = true; } }
			if( isset( $arg[ 6 ] ) ){ if( $arg[ 6 ] == '-force' ){ $force = true; } }
			if( isset( $arg[ 4 ] ) && isset( $arg[ 5 ] ) ){
				if( $arg[ 4 ] == '-folio' ){
					$folio = $arg[ 5 ];
				}
			}
			$i = 4;

			$factura->new_billing( $serie, $so, $force, $folio );
			$factura->new_billing_depure();

			$file = 'error-'.$so.'.log';

			if( $factura->error ){
				echo "\n error log: ".DIRF.$file;
				output_file( DIRF, $file, $factura->error_print(), 'w' );
				echo $factura->error_print();
			}else{
				@unlink( DIRF.$file );
			}

			echo "\n";
			return true;
			break;
		case '-list':
			return facturas_list( $factura, $list_elems );
			break;
		case '-sols':
			$factura->process_config( $arg[1], '-elems', array( '-elems', 24 ) );
			$factura->process_config( $arg[1], '-filtro', array(
				'-filtro',
				'sales',
				'status',
				'total',
				'CFDI',
				'rfc',
				'email',
				'customer_id',
				'payment',
				'f',
				//'razon_social',
			) );

			foreach ($arg as $et => $r) {
				if( $et == 0 ){ continue; }
				if( $et == 1 ){ continue; }
				$factura->process_config( $arg[1], $r, $arg );
			}

			$factura->list_sales_order_data_factura();
			echo "\n listando ".count( $factura->data )." ";
			echo print_table( $factura->data );
			echo "\n";
			return true;
			break;
		case '-h':
		case '--help':
			return false;
			break;
		case '-sales':
			if( !isset( $arg[2] ) ){ return false; }
			$sales = htmlentities( $arg[2], ENT_QUOTES, "UTF-8" );

			// -sols
				$factura->list_sales_order_data_factura();
				if( isset( $factura->data[ $sales ] ) ){
					$factura->data = $factura->data[ $sales ];
				}else{
					$factura->data = null;
				}

				$reg = $factura->data;
			// -list
				$reg2 = null;
				$factura->list_in1();
				foreach ($factura->data as $et => $r) {
					if( $r['sales'] == $sales ){
						$reg2 = $r;
					}
				}
			// conjuntando  datos
				foreach ($reg2 as $et => $r) {
					$reg[ $et ] = $r;
				}
			// filtrando datos
				$reg = array_filter_cols( array($reg), array(
					'sales',
					'status',
					'update_at',

					'total',
					'subtotal',
					'discount',
					'shipping_amount',
					'tax',

					'subtotal_tax',
					'shipping_incl_tax',
					'shipping_discount',

					'customer_id',

					'cfdi',
					'rfc',
					'email',
					'rz',
					'payment',
					'metodo',

					'serie',
					'folio',
					'file_xml',
					'file_pdf'
				) );

			echo print_table_horizontal( ($reg) );
			echo "\n";
			return true;
			break;
	}

	return false;
}

if( !is_terminal() ){ echo "Acceso denegado"; return null; }
if( nargs() == 1 ){ help(); return null; }
if( process( args() ) ){ return null; }

help();
return null;

/* ====================================================== *//*



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
	  `rz` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
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

 */

?>
