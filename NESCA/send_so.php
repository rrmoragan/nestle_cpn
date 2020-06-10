<?php

if( !isset( $_SERVER['TERM'] ) ){
	echo "Acceso denegado";
	return null;
}

include('libs/email_lib.php' );
include('libs/forceUTF8.php');

function help(){
	echo "\n".'send email personalized v0.2';
	echo "\n send_so.php -file 100000973";
	echo "\n los archivos deben estar en la carpeta magento/sales/*.php";
	echo "\n dentro deben tener:";
	echo "\n\t define EMAIL_TEMPLATE = 1";
	echo "\n\t function cpmnSo() \t\t return string_html ( codigo html del email )";
	echo "\n\t function lemail() \t\t return string ( listado de los emails a los que se les enviara copia )";
	echo "\n\t function email_principal() \t return string ( email principal )";
	echo "\n\t function etitle() \t\t return string ( titulo del email )";
	echo "\n\t function text_so() \t\t return string ( numero de orden de venta )";
	echo "\n\n";
	return null;
}

function process( $arg=null ){
	if( $arg==null ){ return false; }

	switch( $arg[1] ){
		case '-file':
			if( !isset( $arg[2] ) ){ return false; }
			$f = "../sales/".$arg[2].".php";
			include( $f );

			$email = new vEmail();

			$te_title = "cafeparaminegocio.com.mx".etitle();

			$email->add_send_to( utf8_decode(email_principal()) );
			$email->add_title( $te_title );
			$email->_from = 'cafeparaminegocio.com.mx <contacto@cafeparaminegocio.com.mx>';
			$email->_bcc = lemail();
			$email->add_message( utf8_decode( cpmnSo() ), 'html' );

			//echo "\n email principal [ ".email_principal()." ]";
			//echo "\n email secundarios [ ".lemail()." ]";
			//echo "\n titulo [ ".$te_title." ]";
			//echo "\n\n html [ ".cpmnSo()." ]";

			$email->enviar_email();

			return true;
			break;
	}

	return false;
}

if( $_SERVER['argc']==1 ){
	help();
	return null;
}

if( process( $_SERVER['argv'] ) ){ return null; }

help();
return null;

?>
