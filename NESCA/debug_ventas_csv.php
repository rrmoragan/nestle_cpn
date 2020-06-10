<?php

include('libs/querys.php');
include('libs/forceUTF8.php');

function save_file( $file='', $dat=null ){
	if($file==''){ $file = 'noname.txt'; }

	$fp = fopen( $file, "w");
	if( !$fp ){
		tt( 'error creando archivo.' );
		return '';
	}

	fwrite($fp, utf8_decode( forceUTF8($dat) ) );
	fclose($fp);

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

$s = "SELECT * from rpv_nestle";
$a = query($s);

if( $a==null ){
	tt('sin datos');
	return null;
}

$a = forceLatin1( $a );

$s = data_to_csv($a);

save_file( 'r_ventas.csv', $s );



?>