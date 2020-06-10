<?php

include('libs/shipping_reg_lib.php');
include('libs/forceUTF8.php');
include('libs/logs.php');

date_default_timezone_set('America/Mexico_City');
/*
if( isset( $_SERVER['argv'][1] ) ){
	if( $_SERVER['argv'][1] == '-test' ){
		$_POST = array(
			'uid' => '620',
			'add_email' => 'rmorales@mlg.com.mx',
			'add_id' => '944',
			'add_nom' => 'rmorales',
			'add_apell' => 'morales',
			'add_calle' => '16 diciembre',
			'add_num' => '31',
			'add_num2' => '',
			'add_cp' => '05210',
			'add_pais' => 'MX',
			'add_col' => 'La Navidad',
			'add_deleg' => 'Cuajimalpa de Morelos',
			'add_estado' => '485',
			'add_tel' => '5532674848',
			'add_tel2' => '',
			'add_person' => '',
			'add_ref' => ''
		);
	}
}*/

if( isset($_POST) ){
	$p = $_POST;
	if( isset( $p['uid'] ) ){
		log_data( 'log/shipping_reg', "data ==> ".print_r( $p,true ) );

		$ss = new Shipping_Reg();
		if( $ss->shipping_update( forceLatin1($p) ) ){
			echo 'datos actualizados';
			return null;
		}
	}else{
		log_data( 'log/shipping_reg', "no-user_id" );
	}
}else{
	log_data( 'log/shipping_reg', "no-post" );
}

echo 'no process';

?>
