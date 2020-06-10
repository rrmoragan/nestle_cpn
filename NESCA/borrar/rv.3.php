<?php

include('libs/basics.php');
include('libs/querys.php');
include('libs/forceUTF8.php');
include('libs/logs.php');
include('libs/magento_sales.php');

date_default_timezone_set('America/Mexico_City');

if( !isset( $_SERVER['TERM'] ) ){
	echo "\n access blcked";
	return null;
}

/* functions */
	function help(){
		$a = array(
			array( 'process' => '-list_rfc', 'descripcion' => 'lista todos las ventas con rfc' ),
			array( 'process' => '-add_rfc', 'descripcion' => 'agrega y/o modifica los datos de facturacion de una orden de ventas' ),
			array( 'process' => '', 'descripcion' => '-add_rfc sales_order rfc usocfdi email razon_social' ),
			array( 'process' => '-add_invoice', 'descripcion' => 'agrega y/o modifica factura de una orden de ventas pdf y xml' ),
			array( 'process' => '', 'descripcion' => '-add_invoice sales_order in1 pdf xml' ),
		);

		echo "\n reporte_ventas.php ... programa necesario para generar reportes de ventas.\n";
		echo print_table( $a );

		return null;
	}

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

	function process( $a ){
		if( !isset( $a[1] ) ){ return false; }

		switch ( $a[1] ) {
			case '-list_rfc':
				$rso = new reportVentas();
				$rso->list_rfc();
				if( $rso->list_rfc() ){
					echo print_table( $rso->data );
					return true;
				}
				break;
			case '-add_rfc': 
				$rso = new reportVentas();
				if( $rso->add_data_factura($a) ){
					return true;
				}

				echo "\n#. error en datos, verifiquelos";
				return true;
				break;
			case '-add_invoice':
				$rso = new reportVentas();
				if( $rso->add_files_factura($a) ){
					return true;
				}

				echo "\n#. error en archivos facturados, verifiquelos";
				return true;
				break;
		}

		return false;
	}

if( $GLOBALS['argc']>=1 ){
	if( process($GLOBALS['argv']) ){
		echo "\n";
		return null;
	}
}

help();
return null;

/*
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
return null;*/

?>