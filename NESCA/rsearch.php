<?php

//$ruta = '../';
$ruta = '';

include( $ruta.'libs/basics.php' );
include( $ruta.'libs/querys.php' );
include( $ruta.'libs/forceUTF8.php' );
include( $ruta.'libs/csv.lib.php' );
include( $ruta.'libs/magento_search.php' );

date_default_timezone_set('America/Mexico_City');

function help(){
	echo "\n magento display list words search";
	$a = array(
		array( 'opcion' => '-list', 'description' => 'lista todas las busquedas hechas en magento' ),
		array( 'opcion' => '-json', 'description' => 'lista todas las busquedas en formato json' ),
		array( 'opcion' => '-list-bloqued', 'description' => 'lista todas las busquedas bloqueadas' ),
		array( 'opcion' => '-blocked', 'description' => 'lista todas las palabras bloqueada en el listado' ),
		array( 'opcion' => '-csv', 'description' => 'exporta el listado de las busquedas en un archivo CSV' ),
		array( 'opcion' => '', 'description' => '', ),
	);

	echo print_table( $a );
	return null;
}

function process( $arg=null ){
	if( $arg==null ){ return false; }

	switch ( $arg[1] ) {
		case '-list':
			$sr = new mSearch();
			$n = $sr->slist_count();
			$sr->slist();

			echo "\n registros = $n";
			echo print_table( $sr->data );
			echo "\n registros = $n";

			return true;
			break;
		case '-list-bloqued':
			$sr = new mSearch();
			$n = $sr->slist_count( false );
			$sr->slist_bloqued();

			echo "\n registros = $n";
			echo print_table( $sr->data );
			echo "\n registros = $n";

			return true;
			break;
		case '-csv':
			$sr = new mSearch();
			$n = $sr->slist_count();
			$sr->slist( 'DESC' );

			if( $sr->data == null ){
				echo "\n registro vacio";
				return true;
			}

			$sr->data = array_filter_cols( $sr->data, array(
				'query_text',
				'popularity',
				'updated_at'
			) );

			$file = 'report_search_'.date( 'Y-m-d', time() );
			$dir = '/var/www/magento/NESCA/mlg_reports/';

			$csv = new fileCSV();
			$csv->save_file( $dir.$file, $sr->data );

			echo "\n data saved ... ".$dir.$file.".csv\n";

			return true;
			break;
		case '-blocked':
			$sr = new mSearch();
			$a = $sr->bloqued_words();
			if( $a ){
				foreach ($a as $et => $r) {
					echo "\n ==> '$r'";
				}
			}

			return true;
			break;
		case '-json':
			$sr = new mSearch();
			$n = $sr->slist();

			$sr->data = array_filter_cols( $sr->data, array(
				'query_text',
				'popularity',
				'updated_at'
			) );

			$sr->data= fixUTF8( $sr->data );

			echo json_encode( array( 'nregs' => $n, 'data' =>$sr->data ) );

			return true;
			break;
	}

	return false;
}

if( !isset( $_SERVER['TERM'] ) ){
	echo "\n acceso restringido";
	return null;
}

if( $_SERVER['argc'] == 1 ){
	help();
	return null;
}

if( process( $_SERVER['argv'] ) ){
	return null;
}

help();
return null;

?>