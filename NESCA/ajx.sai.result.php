<?php

include('libs/basics.php');
include('libs/logs.php');

if( $_GET==null ){
	return null;
}

$err = 0;
if( !isset( $_GET['prg'] ) ){ $err++; }
if( !isset( $_GET['ok'] ) ){ $err++; }
if( !isset( $_GET['er'] ) ){ $err++; }
if( !isset( $_GET['dt'] ) ){ $err++; }

if( $err ){ return null; }

$ok = $_GET['ok'];
$okm = "procesados correctamente: ninguno";
if( $ok != '' ){
	$ok = explode('_', $ok);
			$s = '';
			foreach ($ok as $et => $r) {
				$b = explode('.json', $r);
				$s = $s."\n\t".$b[0];
			}
			$okm = "procesados correctamente: $s";
}

$er = $_GET['er'];
$erm = 'procesados erroneamente: ninguno';
if( $ok != '' ){
	$er = explode('_', $er);
			$s = '';
			foreach ($er as $et => $r) {
				$b = explode('.json', $r);
				$s = $s."\n\t".$b[0].$b[1];
			}
			$erm = "procesados erroneamente: $s";
}

$date = ( (int) $_GET['dt'] )-15780;
$date = date( 'Y/m/d G:i:s', $date );

$log_message = "log ==> insert sales order in to SAI system \ndate ==> $date\n$okm\n$erm";

log_data( 'log/sai_result', $log_message );

mail('rmorales@mlg.com.mx', 'log insert SAI', $log_message);

?>