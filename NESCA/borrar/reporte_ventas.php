<?php

include('libs/basics.php');
include('libs/querys.php');
include('libs/forceUTF8.php');
include('libs/logs.php');
include('libs/magento_lib.3.php');

if( $_GET!=null ){ echo 'access blcked'; return null; }
if( $_POST!=null ){ echo 'access blcked'; return null; }

/* reporte_ventas.php -terminal ==> muestra el resultado en terminal */

function help(){
	$s = "";
	$s = $s."\n reporte_ventas.php ... programa necesario para generar reportes de ventas.";
	$s = $s."\n  -update ...... actualiza el reporte de ventas";
	$s = $s."\n  -csv ......... produce un listado en terminal separado por comas";
	$s = $s."\n  -generate .... genera el reporte y actualiza los links";
	$s = $s."\n  -refresh ..... recalcula los datos de una orden de ventas";
	$s = $s."\n      sales_order ... numero de orden de ventas";
	$s = $s."\n";
	echo $s;

	return null;
}

date_default_timezone_set('America/Mexico_City');

function save_file( $file='', $dat=null ){
	if($file==''){ $file = 'noname.txt'; }

	$fp = fopen( $file, "w");
	if( !$fp ){
		tt( 'error creando archivo.' );
		return '';
	}

	fwrite($fp, utf8_decode( forceUTF8($dat) ) );
	fclose($fp);

	log_data('log/mlg_openpay_report','terminal report_ventas_generated_csv');

	return true;
}

function data_to_csv( $data=null ){
	if($data==null){ return ''; }

	$s = '';
	foreach ($data as $et => $r) {
		$ss = '';
		foreach ($r as $etr => $rr) {
			if( $ss!='' ){ $ss = $ss.','; }
			$ss = $ss.'"'.$rr.'"';
		}
		$s = $s.$ss."\n";
	}

	$cab = '';
	foreach ($data as $et => $r) {
		foreach ($r as $etr => $rr) {
			if( $cab!='' ){ $cab = $cab.','; }
			$cab = $cab.'"'.$etr.'"';
		}
		break;
	}

	$s = $cab."\n".$s;

	return $s;
}

// fuerza a una order de ventas a actualizarse
// $report->marc_modif( $r );

// actualiza la tabla para el reporte de ventas
// $report->report_ventas_update();

//print_r( $GLOBALS );

if( $GLOBALS['argc']==1 ){
	help();
	return null;
}

$a = $GLOBALS['argv'];
unset($a[0]);

switch ( $a[1] ) {
	case '-update':
		$report = new report();
		$report->report_ventas_update();
		return null;
		break;
	case '-refresh':
		unset($a[1]);

		if( $a == null ){ help(); return null; }

		$report = new report();
		foreach ($a as $et => $r) {
			$report->marc_modif( $r );
			echo "\n forzando el recalculo para: ".$r;
		}
		echo "\n\n ejecute reporte_ventas.php -update";
		echo "\n para actualizar datos ....";

		return null;
		break;
	case '-generate':

		$report = new report();
		$report->list_elems();
		$report->data = data_to_csv( $report->data );

		$file_name = 'CPMN-ventas-'.time().'.csv';

		$ddir = 'NESCA/mlg_reports/';
		$dir = '/var/www/magento/'.$ddir;

		save_file( $dir.$file_name, $report->data );

		$link = '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0; url=https://cafeparaminegocio.com.mx/'.$ddir.$file_name.'"></head></html>';

		// https://cafeparaminegocio.com.mx/NESCA/mlg_reports/mlg_report_ventas.html
		save_file( $dir.'mlg_report_ventas.html', $link );

		return null;
		break;
	case '-csv':
		$report = new report();
		$report->list_elems();
		$report->data = data_to_csv( $report->data );
		echo fixUTF8( $report->data );
		return null;
		break;
}

help();
return null;

?>