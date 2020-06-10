<?php

/* datos prueba *//*
	$_POST["so"] = "100000906";
	$_POST["so"] = "100000901";		// pagado valido en fecha
	//$_POST["so"] = "100000645";	// no pagado
	$_POST["srfc"] = "mogr790714xx8";
	$_POST["srfc"] = "ART970227ATA";
	//$_POST["srfc"] = "";
	//$_POST["semail"] = "emaie@mail.com";
	$_POST["scfdi"] = "G03";			// G03
	//$_POST["scfdi"] = "";
	$_POST["srz"] = "khybyyñy yiyiy iyiy' or 1";
	$_POST["srz"] = "ARTFLEUR S.A DE C.V";
	/* */

/* cargando librerias */

	include('libs/basics.php');
	include('libs/querys.php');
	include('libs/logs.php');
	include('libs/email_lib.php');
	include('libs/factura_lib.php');
	include('libs/magento_factura_lib.php');
	include('libs/magento_sales.php');
	include('libs/magento_user_lib.php');

/* datos por default */
	$a = null;
	$a['status'] = 'error';
	$message_ok = "Se esta procesando tu factura en breve te llegar&aacute; un correo electronico<br /> con el PDF y el XML";

/* si no hay datos salir */
	if( $_POST == null ){
		echo json_encode( $a );
		return null;
	}

/* valida datos obtenidos del formulario */
	log_data('log/factura_valid'," ==> procesar ".print_r( $_POST,true ));

	$fc = new mfactura();

	$st = $fc->validate_so( $_POST );
	if( $st == null ){
		$a['data'] = $fc->data;
		echo json_encode( $a );
		return null;
	}

	$_POST = $st;

/* validando datos */
	if( !$fc->valid_so( $_POST["so"] ) ){
		$a['data'] = $fc->data;
		echo json_encode( $a );
		return null;
	}

/* guardando datos y notificando */

	$a['status'] = 'ok';
	$a['message'] = $message_ok;

	if( $_POST["so"] != '100002162' ){
		$fc->so_add_billing_data( $_POST );
	}

    //$email = new vEmail();

	$cpmn = 'cafeparaminegocio.com.mx';

    $email_message = 
    	"process:: Generar factura".
    	"\n sistema:: $cpmn".
    	"\n fecha:: ".date('Y-m-d G:i:s', time() ).
    	"\n venta:: ".$fc->data['sales'].
    	"\n email:: ".$fc->data['email'].
    	"\n rfc:: ".$fc->data['rfc'].
    	"\n cfdi:: ".$fc->data['cfdi'].
    	"\n razon social:: ".$fc->data['rz']."\n";

    log_data('log/factura_valid',$email_message);

    $email_message = "<table>".
		"<tr><td>process:: </td><td>Generar factura</td></tr>".
		"<tr><td>sistema:: </td>		<td>$cpmn</td></tr>".
		"<tr><td>fecha:: </td>			<td>".date('Y-m-d G:i:s', time() )."</td></tr>".
		"<tr><td>orden de venta:: </td>	<td>".$fc->data['sales']."</td></tr>".
		"<tr><td>email:: </td>			<td>".$fc->data['email']."</td></tr>".
		"<tr><td>rfc:: </td>			<td>".$fc->data['rfc']."</td></tr>".
		"<tr><td>cfdi:: </td>			<td>".$fc->data['cfdi']."</td></tr>".
		"<tr><td>razon social:: </td>	<td>".$fc->data['rz']."</td></tr>".
		"</table>";

    $email = new vEmail();

    $email->add_send_to( 'rmorales@mlg.com.mx' );
    $email->add_title( "$cpmn generar factura" );
    $email->add_message( $email_message, 'html' );

    $email->enviar_email();

	echo json_encode( $a );
	return null;

?>
