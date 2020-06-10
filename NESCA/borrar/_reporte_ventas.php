<?php

include('libs/basics.php');
include('libs/querys.php');
include('libs/forceUTF8.php');
include('libs/logs.php');
include('libs/magento_lib.3.php');

/* reporte_ventas.php -terminal ==> muestra el resultado en terminal */

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

$report = new report();
$report->report_ventas();
$report->data = data_to_csv( $report->data );

$d = $GLOBALS['argc'];

if($d==1){
	/*  save file*/
	$file_name = 'CPMN-ventas-'.time().'.csv';

	$ddir = 'NESCA/mlg_reports/';
	$dir = '/var/www/magento/'.$ddir;

	save_file( $dir.$file_name, $report->data );

	$link = '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0; url=https://cafeparaminegocio.com.mx/'.$ddir.$file_name.'"></head></html>';

	// https://cafeparaminegocio.com.mx/NESCA/mlg_reports/mlg_report_ventas.html
	save_file( $dir.'mlg_report_ventas.html', $link );

	return true;
}

$d = $GLOBALS['argv'];
print_r($d);

switch ($d[1]) {
	case '-terminal':
		print_r($report->data);
		break;
}




?>
