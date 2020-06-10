<?php

include('libs/basics.php');
include('libs/querys.php');
if( !defined('FORCE_UTF8') ){ include('libs/forceUTF8.php'); define('FORCE_UTF8',true); }
include('libs/r_openpay.php');
include('libs/logs.php');
include('libs/email_lib.php');
include('email/payment_update.php');

define('RUTA_REPORT','');
define('EMAIL_CORP','dudas@cafeparaminegocio.com.mx');
define('EMAIL_CORP_FROM','contacto@cafeparaminegocio.com.mx');
//define('EMAIL_CORP','rmorales@mlg.com.mx');

date_default_timezone_set('America/Mexico_City');

function help(){
	echo "\n openpay_mlg_lib ver ".PG_VER."\n";
	echo "\n openpay.php -<order> <... ... ...>";

	$a = array(
		array( 'order'=>'-<order>','description'=>'' ),
		array( 'order'=>'-list-transactions asc/desc','description'=>'lista todas las transacciones y su relacion con oepnpay, asc (ascendente), desc (descendente) por default' ),
		array( 'order'=>'-list-curl','description'=>'lista todas las transacciones para su uso con curl' ),
		array( 'order'=>'','description'=>'no_canceled no_pagado\t omite las transacciones canceladas y pagadas' ),
		array( 'order'=>'-files','description'=>'procesa alrchivos _sales.order ubicados en '.OPENPAY_DIR ),
		array( 'order'=>'-report','description'=>'genera reporte con todas las ordenes de compra validadas por openpay,' ),
		array( 'order'=>'','description'=>'el link al reporte se encuentra en el archivo '.OPENPAY_REPORT.OPENPAY_REPORT_URL ),
		array( 'order'=>'-notify','description'=>'envia una notificacion de status de la orden de compra dada al email dado'),
		array( 'order'=>'','description'=>'-notify sales_order email'),
	);
	echo print_table($a);

	return null;
}

function export_csv( $name='',$data=null,$cab=null ){

	$file = 'noname.csv';
	if($name!=''){ $file = $name; }

	$ss = '';
	if($cab!=null){
		foreach ($cab as $et => $r) {
			if( $ss!='' ){ $ss=$ss.','; }
			$ss = $ss.'"'.$r.'"';
		}
	}

	$s = '';
	if( $data!=null ){
		foreach ($data as $et => $r) {
			$st = '';
			foreach ($r as $etr => $rr) {
				if($st!=''){ $st = $st.','; }
				if( is_float( $rr ) ){ $st = $st.sprintf( "%0.2f", $rr ); }else{
				if( is_int( $rr ) ){ $st = $st.sprintf( "%d", $rr ); }else{
					$st = $st.'"'.$rr.'"';
				} }
			}
			$s = $s.$st."\n";
		}
	}

	$s = $ss."\n".$s;

	$fp = fopen(OPENPAY_REPORT.'/'.$name, "w");
	if( !$fp ){
		tt( 'error creando archivo.' );
		return '';
	}

	fwrite($fp, forceLatin1($s) );
	fclose($fp);

	log_data('/var/www/magento/NESCA/log/mlg_openpay_report','terminal report_generated_csv');

	return $name;
}

function save_bash($data = null){
	$s = "#!/bin/bash\n\n";
	$name = 'dopenpay.sh';

	foreach ($data as $et => $r) {
		$s = $s.$r."\n";
	}

	$fp = fopen(OPENPAY_DIR.$name, "w");
	if( !$fp ){ tt( 'error creando archivo.' ); return false; }
	fwrite($fp, $s);
	fclose($fp);
	log_data('/var/www/magento/NESCA/log/mlg_openpay_report','terminal file bash');

	echo "\nok\n";
	return true;	
}

function save_link_file_report($s=''){
	if($s==''){ tt( 'error sin datos' ); return false; }

	$fname = OPENPAY_REPORT.OPENPAY_REPORT_URL;
	$fp = fopen( $fname, "w");
	if( !$fp ){ tt( 'error creando archivo.' ); return false; }

	fwrite($fp, $s);
	fclose($fp);

	return true;
}

function system_notify($a=null){

	$lsys = 'rmorales@mlg.com.mx, dudas@cafeparaminegocio.com.mx';
	$msg  = 'notify update sales order ';

	if( $a==null ){
		$msg .= '==> null';
	}else{
		$s = '';
		$i = 0;
		foreach ($a as $et => $r) {
			$s .= "\n sales order: ".$r['sales'].
				"\n status: ".$r['status'].
				"\n method: ".$r['metodo'].
				"\n fecha: ".$r['date'];
			$i++;
		}

		$s = "==> ".$i."\n".$s;
		$msg .= $s;
	}

	$email = new vEmail();
	$email->add_send_to( $lsys );
	$email->_from = 'contacto@cafeparaminegocio.com.mx';
	$email->add_title( 'Payment update' );

	/* enviar el sales_order */
	$email->add_message( $msg );
	$email->enviar_email();

	return true;
}

// envia notificacion via email
// to ==> contacto@cafeparaminegocio.com.mx
function email_notify_send( $dat='', $from='', $to='' ){
	if( $to=='' ){ return false; }
	if( $from=='' ){ return false; }
	if( $dat=='' ){ return false; }

	$log_file = '/var/www/magento/NESCA/log/payment_update_'.time().'_'.rand(101,999);
	log_data( $log_file, $dat );

	//$lemail = 'dudas@cafeparaminegocio.com.mx';
	//$lemail = 'rmorales@mlg.com.mx';

	$email = new vEmail();
	$email->add_send_to( $to );
	$email->_from = $from;
	$email->add_title( 'Payment update' );

	/* enviar el sales_order */
	$email->add_message( utf8_decode($dat), 'html' );
	$email->enviar_email();

	$email->destinatarios = EMAIL_CORP;
	$email->enviar_email();

	return true;
}

// procesa todas las ordenes de compra y envia la notificacion de status
function all_email_notify_send($b=null,$cab=''){
	//print_r($b);
	/* obtenemos solo los registros pagados */

	$log_file = '/var/www/magento/NESCA/log/payment_update';

	//print_r($b);

	$a = null;
	foreach ($b as $et => $r) {
		if( $r['status'] == 'completed' ){ $a[ $et ] = $r; }
	}
	//$a=$b;
	$b = array();

	if( $a==null ){
		log_data( $log_file, 'sin pedidos a notificar' );
		system_notify();
		return true;
	}

	//$lemail = 'ecastaneda@mlg.com.mx; rmorales@mlg.com.mx';
	$lemail = 'dudas@cafeparaminegocio.com.mx';

	$b = null;
	foreach ($a as $et => $r) {
		print_r( $r['sales'] );

		if( $r['email'] == 'n/a' || $r['email'] == '' || $r['email'] == null ){
			continue; 
		}

		$data = data_template_payment_confirm( $r['sales'] );
		email_notify_send( $data, EMAIL_CORP_FROM, $r['email'] );
		//log_data( $log_file.'_'.$r['sales'], $data );

		$b[] = $r;
	}

	log_data( $log_file, json_encode( $a ) );
	//log_data( $log_file, json_encode( $a ) );
	system_notify( $b );

	return true;
}

function process_argv(){
	$argv = $GLOBALS['argv'];

	switch( $argv[1] ){
		case '-list-transactions':
			$op = new openpay_mlg();
			$op->list_sales_order( (isset( $argv[2] ))?( $argv[2] ):null );
			if( $op->data ){
				echo print_table( $op->data );
			}else{
				tt("Sin datos\n");
			}

			return true;
			break;
		case '-list-curl':
			$op = new openpay_mlg();
			$op->list_curl();

			save_bash( $op->data );

			return true;
			break;
		case '-files':
			$op = new openpay_mlg();
			$op->process_files();
			//echo print_table( $op->control );
			all_email_notify_send( $op->control );

			return true;
			break;
		case '-report':
			$op = new openpay_mlg();
			$op->report();
			$ff = export_csv( $op->report_name(), $op->data, $op->report_cab() );
			save_link_file_report( 'NESCA/openpay_report/'.$ff );

			return true;
			break;
		case '-notify':
			if( !isset( $argv[2] ) ){ return false; }
			if( !isset( $argv[3] ) ){ return false; }

			if ( !filter_var($argv[3], FILTER_VALIDATE_EMAIL) ) {
				return false;
			}

			email_notify_send( data_template_payment_confirm( $argv[2] ), EMAIL_CORP_FROM, $argv[3] );
			return true;
			break;
	}
	return false;
}

if( $GLOBALS['argc'] == 1 ){
	help();
	return;
}

if( process_argv() ){
	return null;
}

help();
return false;

?>
