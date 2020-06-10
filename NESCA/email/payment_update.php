<?php

if( !defined('EMAIL_PAYMENT_UPDATE') ){

    define('EMAIL_PAYMENT_UPDATE','v0.1');

	$dir = '/var/www/magento/NESCA/';

    include($dir.'libs/magento_so_lib.php');
    include($dir.'libs/email_lib.php');

    function template_payment_confirm( $a=null ){
      if($a==null){ return ''; }

        $s = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"> <HTML xmlns="http://www.w3.org/1999/xhtml"> <HEAD> <TITLE>'.
        $a['portal'].'</TITLE> <META content="text/html; charset=UTF-8" http-equiv="Content-Type"> <BODY style="font-family: Arial, Helvetica, sans-serif"> <TABLE style="border: #ccc 1px solid" cellSpacing="0" cellSpacing="0" cellPadding="0" width="600" align="center" border="0"> <TBODY> <tr> <td height="100" vAlign="middle" align="center"> <a style="display: inline-block;text-shadow: none;padding: 10px 40px; text-decoration:none;" href="'.
        $a['url_portal'].'"> <img src="'.
        $a['url_img_logo'].'" width="150" border="0"></A></td></tr><tr> <td style="font-size: 18px;color: #fff;text-align: center;padding: 15px 0;" bgColor="#ef2226" height="40">'.
        $a['template_title'].'</td></tr><tr> <td> <TABLE cellSpacing="0" cellPadding="0" width="100%" border="0"> <TBODY> <tr> <td height="25"> <TABLE class="title" cellSpacing="0" cellPadding="0" width="100%" align="left" border="0"> <TBODY> <tr> <td class="title_space" style="background-color: #000" width="20" bgcolor="#000000"> </td> <td height="40" width="30"> </td> <td style="color: #58595b; text-align: center; padding-right: 33px" height="70"> <P style="font-size: 20px;font-weight: bold;padding-top: 20px;">'.
        $a['status_text'].' '.
        $a['status'].'</P></td> <td class="title_space" style="background-color: #fff" width="20"> </td></tr></TBODY></TABLE></td></tr> <tr> <td> <TABLE class="title" cellSpacing="0" cellPadding="0" width="100%" align="left" border="0" style="border-top: #ccc 1px solid"> <tr> <td class="title_space" width="20"> </td> <td height="40" width="30"> </td> <td style="color: #58595b; text-align: center; padding-right: 33px" height="160"> <p style="text-align: justify;">'.
        $a['status_textb'].'</p></td> <td class="title_space" style="background-color: #fff" width="20"> </td></tr></TBODY></TABLE></td></tr></TBODY></TABLE></td></tr><tr> <td> <TABLE cellSpacing="0" cellPadding="0" width="100%" bgColor="#f8f9f9" border="0" style="border-top: #ccc 1px solid"> <TBODY> <tr> <td height="100" valign="middle" align="center"> <TABLE cellSpacing="2" cellPadding="2" width="100%" align="center" border="0" style="border:0;border-bottom:1px solid #ccc;margin-bottom: 10px;font-size: 13px;"> <TBODY> <tr> <td style="color:#787878;text-align:right;">'.
        $a['f_compra_text'].'</td> <td style="color:#ef2226;text-align:left;"> <strong>'.
        $a['f_compra'].'</strong></td> <td style="color:#787878;text-align:right;">'.
        $a['conf_compra'].'</td> <td style="color:#ef2226;text-align:left;"><strong>'.
        $a['f_pago'].' </strong> </td> </tr></TBODY></TABLE> <TABLE cellSpacing="2" cellPadding="2" width="100%" align="center" border="0" style="font-size: 15px;"> <TBODY><tr> <td style="width:50%;color: #787878; text-align: right">'.
        $a['so_text'].'</td> <td style="color: #ef2226; text-align: left"><strong>'.
        $a['so_textv'].'</strong></td></tr><tr> <td style="width:50%;color: #787878; text-align: right">'.
        $a['so_fp'].'</td> <td style="color: #ef2226; text-align: left"><strong>'.
        $a['so_fpv'].'</strong> </td></tr></TBODY></TABLE></td></tr></TBODY></TABLE></td></tr><tr> <td style="border-top: #ccc 1px solid"> <TABLE class="title" cellSpacing="0" cellPadding="0" width="100%" align="left" border="0" style="border-bottom: 1px solid #ccc;"> <TBODY> <tr> <td style="background-color: #efb6b3" width="20"> </td> <td height="40" width="12"> </td> <td style="font-size: 20px; font-weight: bold; color: #58595b; text-align: left" >'.
        $a['recuerda_tit'].' </td><td height="40" width="32"> </td></tr><tr><td> </td><td height="40"> </td><td style="font-size: 13px; color: #58595b; text-align: left"><ul>'.
        $a['recuerda_msg'].' </ul></td><td height="40" width="32"> </td></tr></TBODY></TABLE></td></tr><tr><td><TABLE class="title" cellSpacing="0" cellPadding="0" width="100%" align="left" border="0"> <TBODY> <tr> <td style="background-color: #f4f4f4" width="20"> </td> <td height="40" width="10"> </td> <td style="font-size: 20px; font-weight: bold; color: #58595b; text-align: left">'.
        $a['dudas'].'</td></tr><tr> <td> </td> <td height="40"> </td> <td style="font-size: 13px; color: #58595b; text-align: left">'.
        $a['comunicate'].'</td></tr><tr> <td> </td> <td height=40> </td> <td style="font-size: 15px; color: #58595b; text-align: left" valign="top"> <TABLE cellSpacing="2" cellPadding="2" width="100%" align="left" border="0"> <TBODY> <tr> <td style="text-align: center" width="35" align="center"> <IMG src="'.
        $a['url_portal'].'media/email/tel.jpg" width="27" height="28"></td> <td style="font-size: 18px; color: #58595b; text-align: left">'.
        $a['telefono'].'</td></tr><tr> <td style="text-align: center" align="center"> <IMG src="'.
        $a['url_portal'].'media/email/m.jpg" width="27" height="28"></td> <td> <A style="text-decoration: none; color: #ef2226" href="'.
        $a['email_dudas_link'].'" target="_blank">'.
        $a['email_dudas'].'</A></td></tr></TBODY></TABLE></td></tr></TBODY></TABLE></td></tr></TBODY></TABLE></BODY></HTML>';

      return $s;
    }

    function data_template_payment_confirm( $sorder='' ){
        if( $sorder=='' ){
    		echo "\n data_template_payment_confirm(null)";
    		return '';
    	}

        $so = new mSalesOrder();
        $so->sales( $sorder );

    	if( $so->data == null ){
    		echo "\n data_template_payment_confirm( $sorder ) ==> null";
    		return '';
    	}
        //print_r( $so->data );

        $fp = $so->data['sales_method'];
        switch ( $fp ) {
            case 'charges': $fp = 'Tarjeta Débito / Crédito'; break;
            case 'stores': $fp = 'Pago en efectivo en tiendas'; break;
            case 'banks': $fp = 'Transferencia Interbancaria (SPEI)'; break;
        }

        $t = time()-( 0 * 3600 );

        $status = 'Pendiente';
        switch ($so->data['status']) {
            case 'pending': $status = 'Pendiente'; break;
            case 'canceled': $status = 'Cancelado'; break;
            case 'pagado': $status = 'Pagado'; break;
        }

        $a = null;
        $a['status'] = $status;
        $a['f_compra'] = $so->data['created_at'];
        $a['f_pago'] = date( 'Y-m-d G:i:s', $t );
        $a['so_text'] = 'Número de pedido:';
        $a['so_textv'] = $so->data['increment_id'];
        $a['so_fp'] = 'Forma de pago:';
        $a['so_fpv'] = $fp;
        $a['portal'] = 'cafeparaminegocio.com.mx';
        $a['url_portal'] = 'https://'.$a['portal'].'/';
        $a['url_img_logo'] = 'https://'.$a['portal'].'/media/email/logo.jpg';

        $a['template_title'] = 'CONFIRMACIÓN DE PAGO';
        $a['telefono'] = '(01 55) 1250-2317';
        $a['status_text'] = 'El estatus de tu orden de compra es:';
        $a['status_textb'] = '¡Muchas gracias por finalizar tu compra, hemos recibido tu pago! A partir de este momento el tiempo de entrega de tu pedido es de máximo 72 hrs hábiles en Ciudad de México y Área Metropolitana y de 3 a 7 días hábiles en el Interior de la República. Si tienes alguna duda respecto a tu pedido por favor comunícate con nosotros al teléfono (01 55) 1250-2317, con gusto te ayudaremos.';
        $a['recuerda_tit'] = 'Recuerda que...';
        $a['recuerda_msg'] = '';

        $a['f_compra_text'] = 'Fecha de compra:';
        $a['conf_compra'] = 'Confirmación de pago:';
        $a['dudas'] = '¿Tienes alguna duda?';
        $a['comunicate'] = 'Comunícate con nosotros al CAT';

        $a['email_dudas'] = 'dudas@'.$a['portal'];
        $a['email_dudas_link'] = 'mailto:'.$a['email_dudas'];

        $recuerda = array(
            //'txt1' => 'Una vez confirmado tu pago, el tiempo de entrega es de máximo 72 hrs hábiles en el Área metropolitana y de 3 a 7 días hábiles en el interior de la República.',
            'txt2' => 'En caso de que tu pedido no sea entregado en tiempo, deberás notificarlo inmediatamente al Centro de Atención Telefónica (CAT) para resolver el caso.',
            'txt3' => 'En caso de recibir algún pedido con el empaque abierto, o bien el artículo esté visiblemente dañado, no deberás firmar de recibido, ni recibir el producto, sino enviarlo de regreso con una nota y notificarlo de inmediato al CAT, un día hábil de tolerancia a partir de la fecha de recepción.',
        );

        foreach ($recuerda as $et => $r) {
            $a['recuerda_msg'] .= '<li><p>'.$r.'<br />&nbsp;</p></li>';
        }

        return template_payment_confirm( $a );
    }
}

/*
$a = array(
	'100002154',
    '100000906',
    '100000904',
    '100000899',    // bank
);


$email = new vEmail();
$email->add_send_to( 'rmorales@mlg.com.mx' );
$email->_from = 'contacto@cafeparaminegocio.com.mx';
$email->add_title( 'Payment update' );

foreach ($a as $et => $r) {
    $m = data_template_payment_confirm( $r );
	//echo "\n$m\n";
    $email->add_message( $m, 'html' );
    $email->enviar_email();
    break;
}
*/
?>
