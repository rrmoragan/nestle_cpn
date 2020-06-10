<?php

include('libs/codevendorcheckout.php');

date_default_timezone_set('America/Mexico_City');

$j = array( 
	'status' => 'error',
	'message' => 'Campo vacio',
	'nivel' => 0
);

$logf = "log/vendor_code";

log_data($logf, print_r($_POST,true) );

/* si el post esta vacio ==> salir */
	if( $_POST==null ){
		echo json_encode( $j );
		return false;
	}
	$j['nivel'] = 1;
/* valida datos */
	$d = new codeVendorCheckout();
	$d->log_file = $logf;

	$d->valid_data( $_POST );
	if( $d->data == null ){
		$j['message'] = 'Datos incorrectos';
		echo json_encode( $j );
		return false;
	}
	$j['nivel'] = 2;
/* actualizando datos */
	$d->update_code_vendor();
	$j['message'] = $d->code_vendor_action();
	if( $d->error == '' ){
		$j['status'] = 'ok';
		$j['nivel'] = 3;
		echo json_encode( $j );
		return null;
	}
/* en caso de error */
	$j['nivel'] = 4;
	$j['message'] = 'Datos incorrectos';
	echo json_encode( $j );
return null;
?>