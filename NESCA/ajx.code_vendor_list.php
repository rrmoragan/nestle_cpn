<?php
include('libs/codevendorcheckout.php');
include('libs/forceUTF8.php');

date_default_timezone_set('America/Mexico_City');

function max_length($a=null,$campo=''){
	if($a==null){ return 0; }
	if($campo==null){ return 0; }

	//var_dump( $a );

	$n = 0;
	foreach ($a as $et => $r) {
		//$nn = strlen( $r[ $campo ] );
		$nn = strlen( utf8_decode( $r[ $campo ] ) );
		/*var_dump( $r[ $campo ] );
		var_dump( strlen( $r[ $campo ] ) );
		var_dump( strlen( utf8_decode( $r[ $campo ] ) ) );
		var_dump( strlen( utf8_encode( $r[ $campo ] ) ) );
		*/
		if( $nn>$n ){ $n = $nn; }
	}

	return $n;
}
function a_str_pad($a=null,$campo='',$long=0,$relleno=''){
	if($a==null){ return 0; }
	if($campo==null){ return 0; }

	foreach ($a as $et => $r) {
		$a[ $et ][ $campo ] = str_pad( utf8_decode( $r[ $campo ] ), $long, $relleno );
		//$a[ $et ][ $campo ] = str_replace( " ", '&nbsp;', $a[ $et ][ $campo ] );
	}

	return $a;
}

$logf = "log/vendor_code";

/* lista todos los codigos de vendedor disponibles */
	$d = new codeVendorCheckout();
	$d->log_file = $logf;
	$d->cv_list_nestle();

	$lmax_a = max_length( $d->data, "name" );
	//$lmax_b = max_length( $d->data, "code" );

	$d->data = a_str_pad( $d->data, "name", $lmax_a, "." );

	//var_dump( $d->data );

	$n = count( $d->data );
	$status = 'error';
	if( $n > 0 ){ $status = 'ok'; }

	$a = array(
		'n' => "$n",
		'status' => $status,
		'data' => $d->data,
		'us' => $d->cv_current_data($_POST)
	);

	//var_dump( fixUTF8( $a ) );
	echo json_encode( fixUTF8( $a ) );

return null;
?>