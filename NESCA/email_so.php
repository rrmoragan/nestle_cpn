<?php

class emailSO{
	public $data = null;				// datos de la orden de venta
	public $data_html = '';				// html generado de la orden de venta
	public $sales = '';					// orden de venta de la que se extraen los datos
	public $user_email_send = true;		// true/false enviar o no el email al usuario que realizo la compra
	public $user_email = '';			// correo del usuario
	public $email = '';					// lista de correos adicionales
	public $email_from = '';			// correo desde el que se envia el email
	public $send = true;
	public $view_html = false;			// muestra el html

	public function set_so( $so='' ){
		$this->sales = '';
		$this->sales = trim( htmlentities( $so, ENT_QUOTES, "UTF-8" ) );
		if( $this->sales=='' ){ return false; }
		return true;
	}
	public function user_send_off(){
		$this->user_email_send = false;
		return null;
	}
	public function email_send( $a=null ){
		if( $a==null ){ return 0; }
		$s = '';
		$i = 0;
		foreach ($a as $et => $r) {
			if ( !filter_var($r, FILTER_VALIDATE_EMAIL) ){
				echo "\n email:: $r ==> no valid";
				continue;
			}

			$s = $s.$r."; ";
			$i++;
		}
		$this->email = $s;
		return $i;
	}
	public function email_from( $email='' ){
		if( $email=='' ){ return false; }

		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ){
			return false;
		}

		// Café para mi negocio - ventas <dudas@cafeparaminegocio.com.mx>
		$this->email_from = "Café para mi negocio - ventas <".$email.">";
		return true;
	}
	public function get_data(){
		$this->data_html = null;

		// revisando numero de orden de venta
			if( $this->sales == '' ){
				echo "\nerror:: falta numero de orden de venta";
				return false;
			}
		// obteniendo datos de orden de venta
			if( !$this->so_data() ){
				$this->data_html = null;
				echo "\nerror:: orden de venta no encontrada";
				return false;
			}
		// obteniendo listado de productos
			if( $this->so_data_products() == 0 ){
				$this->data_html = null;
				echo "\nerror:: no se encontraron productos relacionados con la orden de venta";
				return false;
			}
		// obteniendo email del usuario
			$this->user_email = $this->data['email'];
		// validando correos de envio
			if( $this->user_email_send == false && $this->email == '' ){
				echo "\n error: faltan correos para poder enviar el email";
				return false;
			}

		if( !$this->template() ){
			$this->data_html = null;
			echo "\n error: al crear el html, faltan datos.";
			return false;
		}

		return true;
	}
	private function so_data(){
		$this->data = null;
		$so = $this->sales;

		$s = "SELECT
			x.increment_id as so_num,
			x.status,
			concat( x.date_day, ' de ', x.date_month, ' de ', x.date_year ) as so_fecha,
			x.customer_email,
			x.grand_total as total,
			x.subtotal,
			x.shipping_amount as envio,
			( select value from core_config_data where path like x.method ) as so_pago

			from(
			select
			sfo.entity_id,
			sfo.status,
			sfo.increment_id,
			sfo.created_at,

			year( sfo.created_at ) as date_year,
			case
			when MONTH( sfo.created_at ) = 1 then 'enero' 
			when MONTH( sfo.created_at ) = 2 then 'febrero' 
			when MONTH( sfo.created_at ) = 3 then 'marzo' 
			when MONTH( sfo.created_at ) = 4 then 'abril' 
			when MONTH( sfo.created_at ) = 5 then 'mayo' 
			when MONTH( sfo.created_at ) = 6 then 'junio' 
			when MONTH( sfo.created_at ) = 7 then 'julio' 
			when MONTH( sfo.created_at ) = 8 then 'agosto' 
			when MONTH( sfo.created_at ) = 9 then 'septiembre' 
			when MONTH( sfo.created_at ) = 10 then 'octubre' 
			when MONTH( sfo.created_at ) = 11 then 'noviembre' 
			when MONTH( sfo.created_at ) = 12 then 'diciembre' 
			end as date_month,
			day( sfo.created_at ) as date_day,

			sfo.customer_email,
			concat( '$', format(sfo.grand_total, 2) ) as grand_total,
			concat( '$', format(sfo.subtotal, 2) ) as subtotal,
			concat( '$', format(sfo.shipping_amount, 2) ) as shipping_amount,
			sfop.parent_id ,
			if( sfop.method <> '', concat( 'payment/', sfop.method, '/title' ), sfop.method ) as method,
			sfop.openpay_creation_date,
			sfop.openpay_payment_id,
			sfop.openpay_barcode,
			sfop.openpay_authorization

			from sales_flat_order as sfo
			inner join sales_flat_order_payment as sfop on sfop.parent_id = sfo.entity_id

			where 
			sfo.increment_id like '$so'
			) as x
			";
		$a = query($s);
		if( $a==null ){ return false; }

		$a = $a[0];

		$d = null;

		$d['portal'] 		= 'cafeparaminegocio.com.mx';
		$d['target'] 		= 'cafeparaminegocio';
		$d['url'] 			= 'https://'.$d['portal'].'/';
		$d['email_contacto']= 'dudas@'.$d['portal'];
		$d['title'] 		= 'CONFIRMACIÓN DE PEDIDO';
		$d['email_title']   = 'Café para mi negocio : pedido nº '.$a['so_num'];

		$d['so_fecha'] = $a['so_fecha'];
		$d['so_num']   = $a['so_num'];
		$d['so_pago']  = $a['so_pago'];
		$d['status']  	= $a['status'];
		$d['email']    = $a['customer_email'];
		$d['total'] = array(
			'subtotal' 	=> $a['subtotal'],
			'envio' 	=> $a['envio'],
			'total' 	=> $a['total'],
		);

		$this->data = $d;
		return true;
	}
	private function so_data_products(){
		$so = $this->sales;
		$this->data['data'] = null;

		$s = "SELECT
			sfoi.item_id,
			sfoi.order_id,
			sfoi.parent_item_id,
			sfoi.product_id,
			sfoi.product_type,
			sfoi.sku,
			sfoi.name as product,
			cast( sfoi.qty_ordered as INT ) as cantidad,
			concat( '$', format(sfoi.row_total_incl_tax, 2) ) as precio/*,
			sum( sfoi.row_total_incl_tax ) as subtotal*/

			from sales_flat_order_item as sfoi
			inner join sales_flat_order as sfo on sfo.entity_id = sfoi.order_id
			where 
			sfo.increment_id like '$so'
			";
		$a = query( $s );
		if( $a==null ){ return 0; }

		$this->data['data'] = fixUTF8($a);
		return count( $this->data['data'] );
	}
	private function template(){
		if( $this->data == null ){ return false; }

		$d = $this->data;

		$d['text1'] = '¡Tu pedido se ha generado exitosamente!';
		$d['text2'] = 'Recuerda que una vez confirmado tu pago, el tiempo de entrega es de máximo 72 hrs hábiles<br> en el Área metropolitana y de 3 a 7 días hábiles en el interior de la República.';
		$d['text3'] = 'Descripción de compra:';
		$d['text4'] = 'Te mostramos un listado de tus artículos seleccionados:';
		$d['text5'] = 'Fecha de pedido:';
		$d['text6'] = 'Número de confirmación:';
		$d['text7'] = 'Forma de pago:';
		$d['text8'] = 'Subtotal';
		$d['text9'] = 'Envío';
		$d['text10'] = 'Gran total';
		$d['text11'] = 'Recuerda que ...';
		$d['text12'] = 'En caso de que tu pedido no sea entregado en tiempo, deberás notificarlo inmediatamente al Centro de Atención Telefónica (CAT) para resolver el caso.';
		$d['text13'] = 'En caso de recibir algún pedido con el empaque abierto, o bien el artículo esté visiblemente dañado, no deberás firmar de recibido, ni recibir el producto, sino enviarlo de regreso con una nota y notificarlo de inmediato al CAT, un día hábil de tolerancia a partir de la fecha de recepción.';
		$d['text14'] = '¿Tienes alguna duda?';
		$d['text15'] = 'Comunícate con nosotros al CAT';
		$d['text16'] = '(01 55) 1250-2317';

		$d['sol_list_1'] = 'ARTÍCULO';
		$d['sol_list_2'] = 'SKU';
		$d['sol_list_3'] = 'CANTIDAD';
		$d['sol_list_4'] = 'PRECIO';

		$s = '';
		foreach ($d['data'] as $et => $r) {
			$s = $s.'<tr>'.
					'<td align="left" style="padding:10px 10px 10px 0;color:#ef2226;">'.$r['product'].' </td>'.
					'<td align="left" style="color:#ef2226;">'.$r['sku'].' </td>'.
					'<td align="center" style="color:#ef2226;">'.$r['cantidad'].' </td>'.
					'<td align="right" style="color:#ef2226;">'.$r['precio'].' </td>'.
				'</tr>';
		}

		$s = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.
			'<html xmlns="http://www.w3.org/1999/xhtml">'.
			'<head>'.
			'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'.
			'<meta name="viewport" content="initial-scale=1.0, width=device-width" />'.
			'<title>'.$d['portal'].'</title>'.
			'</head>'.
			'<body style="font-family:Arial, Helvetica, sans-serif;">'.
			'<!-- Begin wrapper table -->'.
			'<table style="border:2px solid #cccccc;" width="800" border="0" align="center" cellpadding="0" cellspacing="0">'.
			'<tr><td>'.
			'<table width="100%" border="0" cellpadding="0" cellspacing="0">'.
			'<tr>'.
			    '<td align="center" height="100" valign="middle">'.
					'<a href="'.$d['url'].'" style="display: block;text-decoration: none;" target="'.$d['target'].'" title="'.$d['portal'].'" >'.
						'<img src="'.$d['url'].'media/email/logo.jpg" height="80px" width="auto" border="0" />'.
					'</a>'.
				'</td></tr>'.
			'<!-- Begin Content -->'.
			'<tr>'.
				'<td height="40" bgcolor="#ef2226" style="color:#ffffff;font-size:18px;text-align:center;border-top: 1px solid #cccccc;">'.
					$d['title'].'</td></tr>'.
			'<tr>'.
				'<td style="border-top: 1px solid #cccccc;">'.
					'<table width="100%" border="0" cellspacing="0" cellpadding="0">'.
					'<tr>'.
						'<td width="22" style="background-color:#000000;"></td>'.
						'<td width="12"></td>'.
						'<td height="60" align="center" style="font-size:20px;font-weight:bold;color:#58595b;">'.$d['text1'].'</td>'.
					'</tr>'.
					'<tr>'.
						'<td width="22" style="background-color:#ffffff;"></td>'.
						'<td width="10"></td>'.
						'<td height="70" align="center" style="padding-right: 33px;font-size:13px;font-weight:normal;color:#58595b;">'.$d['text2'].'</td>'.
					'</tr>'.
					'</table>'.
				'</td></tr>'.
			'<tr>'.
				'<td data="num_sales_order" style="border-top: 1px solid #cccccc;">'.
					'<table width="100%" bgcolor="#f8f9f9" border="0" cellspacing="0" cellpadding="0" style="">'.
					'<tr>'.
						'<td height="90" align="center" valign="middle">'.
							'<table width="70%" border="0" align="center" cellpadding="2" cellspacing="2">'.
							'<tr>'.
								'<td width="50%" style="color:#787878;font-size:15px;text-align:right;">'.$d['text5'].' </td>'.
								'<td style="color:#ef2226;text-align:left;"><strong>'.$d['so_fecha'].' </strong></td>'.
							'</tr>'.
							'<tr>'.
								'<td align="right" style="color:#787878;font-size:15px;">'.$d['text6'].' </td>'.
								'<td align="left" style="color:#ef2226;"><strong>'.$d['so_num'].' </strong></td>'.
							'</tr>'.
							'<tr>'.
								'<td align="right" style="color:#787878;font-size:15px;">'.$d['text7'].'</td>'.
								'<td align="left" style="color:#ef2226;"><strong>'.$d['so_pago'].' </strong></td>'.
							'</tr>'.
							'</table>'.
						'</td>'.
					'</tr>'.
					'</table>'.
				'</td></tr>'.
			'<tr>'.
				'<td data="descripcion_compra" style="padding-bottom:20px;border-top: 1px solid #cccccc;">'.
					'<table width="100%" cellpadding="0" cellspacing="0">'.
					'<tr>'.
						'<td width="20" style="background-color:#f4f4f4;"> </td>'.
						'<td width="10" height="40">&nbsp;</td>'.
						'<td align="left" style="font-size:15px;color:#58595b;"><strong>'.$d['text3'].'</strong></td>'.
					'</tr>'.
					'<tr>'.
						'<td width="20" style="background-color:#ffffff;"> </td>'.
						'<td width="10" height="40">&nbsp;</td>'.
						'<td align="left" style="font-size:13px;color:#58595b;font-weight: normal;padding-left: 25px;">'.$d['text4'].' </td>'.
					'</tr>'.
					'</table>'.
				'</td></tr>'.
			'<!-- orden de venta listado -- cabecera -->'.
			'<tr>'.
				'<td height="30" bgcolor="#f8f9f9" style="padding: 0 10%;border-top: 1px solid #cccccc;">'.
					'<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">'.
					'<tr>'.
						'<td style="color:#787878;font-family:Arial,sans-serif;text-align:center;font-size:14px;">'.$d['sol_list_1'].' </td>'.
						'<td width="180" style="color:#787878;font-family:Arial,sans-serif;text-align:center;font-size:14px;">'.$d['sol_list_2'].' </td>'.
						'<td width="100" style="color:#787878;font-family:Arial,sans-serif;text-align:center;font-size:14px;">'.$d['sol_list_3'].' </td>'.
						'<td width="130" style="color:#787878;font-family:Arial,sans-serif;text-align:right;font-size:14px;">'.$d['sol_list_4'].' </td>'.
					'</tr>'.
					'</table>'.
				'</td></tr>'.
			'<!-- orden de venta listado -- articulos -->'.
			'<tr>'.
				'<td height="30" style="padding: 20px 10%;border-top: 1px solid #cccccc;">'.
					'<table cellpadding="0" cellspacing="0" border="0" width="100%">'.
					'<tbody>'.
					'<tr>'.
						'<td></td>'.
						'<td width="180"></td>'.
						'<td width="100"></td>'.
						'<td width="130"></td>'.
					'</tr>'.
					$s.
					'</tbody>'.
					'</table>'.
				'</td></tr>'.
			'<!-- orden de venta listado -- totales -->'.
			'<tr>'.
				'<td height="30" bgcolor="#f8f9f9" style="padding: 10px 10%;border-top: 1px solid #cccccc;">'.
					'<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">'.
					'<tr>'.
						'<td width="60%"></td>'.
						'<td>'.
							'<table cellpadding="0" cellspacing="0" border="0" width="100%">'.
							'<tr data="subtotal">'.
								'<td align="right" style="padding:3px 9px;">'.$d['text8'].' </td>'.
								'<td align="right" style="padding:3px 0;">'.$d['total']['subtotal'].' </td>'.
							'</tr>'.
							'<tr data="shipping">'.
								'<td align="right" style="padding:3px 9px">'.$d['text9'].' </td>'.
								'<td align="right" style="padding:3px 0;">'.$d['total']['envio'].' </td>'.
							'</tr>'.
							'<tr data="grand_total">'.
								'<td align="right" style="padding:3px 9px"><strong>'.$d['text10'].' </strong></td>'.
								'<td align="right" style="padding:3px 0;"><strong>'.$d['total']['total'].' </strong></td>'.
							'</tr>'.
							'</table>'.
						'</td>'.
					'</tr>'.
					'</table>'.
				'</td></tr>'.
			'<tr>'.
				'<td style="border-top: 1px solid #cccccc;">'.
					'<table width="750" border="0" align="left" cellpadding="0" cellspacing="0" class="title">'.
					'<tr>'.
						'<td width="22" class="title_space" style="background-color:#efb6b3;">&nbsp;</td>'.
						'<td width="12" height="40">&nbsp;</td>'.
						'<td width="716" style="font-size:20px;font-weight:bold;text-align:left;color:#58595b;">'.$d['text11'].' </td>'.
					'</tr>'.
					'<tr>'.
						'<td class="title_space">&nbsp;</td>'.
						'<td height="40">&nbsp;</td>'.
						'<td style="font-size:13px;text-align:left;color:#58595b;">'.
							'<ul>'.
								'<li>'.$d['text12'].'<br /><br /> </li>'.
								'<li>'.$d['text13'].'<br /><br /> </li>'.
							'</ul>'.
						'</td>'.
					'</tr>'.
					'</table>'.
				'</td></tr>'.
			'<!-- End Content -->'.
			'<tr>'.
				'<td style="border-top: 1px solid #cccccc;">'.
					'<table width="750" border="0" align="left" cellpadding="0" cellspacing="0" class="title">'.
					'<tr>'.
						'<td width="20" style="background-color:#f4f4f4;">&nbsp;</td>'.
						'<td width="10" height="40">&nbsp;</td>'.
						'<td width="636" style="font-size:20px;font-weight:bold;text-align:left;color:#58595b;">'.$d['text14'].' </td>'.
					'</tr>'.
					'<tr>'.
						'<td class="title_space">&nbsp;</td>'.
						'<td height="40">&nbsp;</td>'.
						'<td style="font-size:13px;text-align:left;color:#58595b;">'.$d['text15'].' </td>'.
					'</tr>'.
					'<tr>'.
						'<td >&nbsp;</td>'.
						'<td height="40">&nbsp;</td>'.
						'<td height="100" valign="top" style="font-size:15px;text-align:left;color:#58595b;">'.
							'<table width="500" border="0" align="left" cellpadding="2" cellspacing="2">'.
							'<tr>'.
								'<td width="35" align="center" style="text-align:center;">'.
									'<img src="'.$d['url'].'media/email/tel.jpg" width="27" height="28" />'.
								'</td>'.
								'<td width="451" style="color:#58595b;font-size:18px;text-align:left;">'.$d['text16'].' </td>'.
							'</tr>'.
							'<tr>'.
								'<td align="center" style="text-align:center;">'.
									'<img src="'.$d['url'].'media/email/m.jpg" width="27" height="28" />'.
								'</td>'.
								'<td>'.
									'<a href="mailto:'.$d['email_contacto'].'" target="_blank" style="text-decoration:none;color:#ef2226;">'.$d['email_contacto'].'</a>'.
								'</td>'.
							'</tr>'.
							'</table>'.
						'</td>'.
					'</tr>'.
					'</table>'.
				'</td></tr>'.
			'</table>'.
			'</td></tr></table>'.
			'</body>'.
			'</html>';

		$this->data_html = $s;
		return true;
	}
	public function set_sumary(){
		$this->send = false;
	}
	public function get_sumary(){
		$s = '';
		$s = $s."\n title:       ".$this->data['email_title'];
		$s = $s."\n from:        ".$this->email_from;
		$s = $s."\n to:          ".( ($this->user_email_send==true)?$this->user_email:$this->email);
		$s = $s."\n cco:          ".( ($this->user_email_send==true)?$this->email:'' );
		$s = $s."\n sales order: ".$this->data['so_num'];
		$s = $s."\n date:        ".$this->data['so_fecha'];
		$s = $s."\n status:      ".$this->data['status'];
		$s = $s."\n products:    ".count( $this->data['data'] );
		$s = $s."\n content:     ".( ($this->data_html == null)?'error':'html' );
		$s = $s."\n";
		return $s;
	}
	public function get_email(){
		if( $this->view_html == true ){
			echo $this->data_html;
			return false;
		}
		if( $this->send == false ){
			echo $this->get_sumary();
			$this->data_html = null;
			return false;
		}

		return true;
	}
	public function view_html_on(){
		$this->view_html = true;
		$this->send = false;
	}
	public function send_email(){
		echo "\nenviando email\n";

		$dest = $this->user_email;
		if( $this->user_email_send == false ){ $dest = $this->email; }
		if( $dest=='' ){
			echo "\n error: falta email de envio\n";
			return false;
		}
		if( $this->data_html == null ){
			echo "\n error: no hay mensaje";
			return false;
		}

		$cco = '';
		if( $this->user_email_send == true ){ $cco = $this->email; }

		$this->data['email_title'] = utf8_decode($this->data['email_title']);
		$this->data_html  = utf8_decode($this->data_html);
		$this->email_from = utf8_decode($this->email_from);

		$email = new vEmail();
		$email->add_send_to( $dest );   // destinatario
		$email->add_title( $this->data['email_title'] );   // titulo del email
		$email->add_message( $this->data_html, 'html' );   // agregar el mensaje en formato html
		//$email->_cc = 'email2@email.com';   // con copia a:
		$email->_bcc = $cco;   // con copia oculta a:
		$email->_from = $this->email_from;   // quien envia
		$email->add_from_to = $this->email_from;
		//$email->add_message( 'mensaje del email', 'text' );   // agregar el mensaje en formato texto
		$email->add_message( $this->data_html, 'html' );   // agregar el mensaje en formato html

		$email->enviar_email( true ); // mail($this->destinatarios, $this->titulo, $this->mensaje, $this->cabecera);

		return true;
	}
}

function help(){
	echo "\n envia un email de una orden de venta a los correos indicados.\n";

	$a = array(
		array('opcion' => '-so', 'description' => 'numero de orden de venta' ),
		array('opcion' => '-email', 'description' => 'listado de emails a los que se enviara la copia de la orden de venta' ),
		array('opcion' => '', 'description' => 'cada email debe ir separado 1 espacio' ),
		array('opcion' => '-no_user', 'description' => 'la copia del email no se enviara al usuario que realizo la compra' ),
		array('opcion' => '-from', 'description' => 'email desde el que se envia la copia' ),
		array('opcion' => '-send', 'description' => 'envia el email' ),
		array('opcion' => '-email_data', 'description' => 'precenta los datos de envio' ),
		array('opcion' => '-html', 'description' => 'muestra el html resultante, esto evita el envio del email' ),

		array('opcion' => '', 'description' => '' ),
		array('opcion' => '', 'description' => 'ejemplo envio email' ),
		array('opcion' => '', 'description' => 'php email_so.php -so 100001040 -from contacto@cafeparadatagocio.com.mx ' ),

		array('opcion' => '', 'description' => '' ),
		array('opcion' => '', 'description' => 'ejemplo envio email a email definidos' ),
		array('opcion' => '', 'description' => 'php email_so.php -so 100001040 -from contacto@cafeparadatagocio.com.mx -no_user -email rmorales@mlg.com.mx' ),

		array('opcion' => '', 'description' => '' ),
		array('opcion' => '', 'description' => 'ejemplo ver datos de envio' ),
		array('opcion' => '', 'description' => 'php email_so.php -so 100001040 -from contacto@cafeparadatagocio.com.mx -email rmorales@mlg.com.mx -email_data' ),

		array('opcion' => '', 'description' => '' ),
		array('opcion' => '', 'description' => 'ejemplo ver html generado' ),
		array('opcion' => '', 'description' => 'php email_so.php -so 100001040 -html' ),
		array('opcion' => '', 'description' => 'php email_so.php -so 100001040 -html > email.html' ),

		array('opcion' => '', 'description' => '' ),
		array('opcion' => '', 'description' => 'ejemplo' ),
		array('opcion' => '', 'description' => 'php email_so.php -so 100001040 -from contacto@cafeparadatagocio.com.mx -email rmorales@mlg.com.mx -no_user -email_data' ),
	);

	echo print_table( $a );
	return null;
}

function lfunction_on( $a=null ){
	if( $a==null ){ return false; }

	$i=true;
	foreach ($a as $et => $r) {
		switch ( $et ) {
			case '-so':
			case '-email':
			case '-no_user':
			case '-from':
			case '-email_data':
			case '-html':
				break;
			default:
				$i = false;
				break;
		}
	}

	return $i;
}

function process(){
	$a = args_list();

	if( !lfunction_on( $a['email_so.php'] ) ){ return false; }

	$so = new emailSO();

	foreach ($a['email_so.php'] as $et => $r) {
		switch ( $et ) {
			case '-so':        	$so->set_so( $r[0] );  	break;
			case '-no_user':   	$so->user_send_off();  	break;
			case '-email':	   	$so->email_send( $r ); 	break;
			case '-from':	   	$so->email_from( $r[0] ); break;
			case '-email_data': $so->set_sumary();		break;
			case '-html':		$so->view_html_on();	break;
				break;
		}
	}

	if( !$so->get_data() ){ echo "\n"; return true; }
	// detecta si se enviará el email o solo se precentará el sumary
	if( $so->get_email() ){
		$so->send_email();
	}

	return true;
}

include('libs/basics.php');
include('libs/querys.php');
include('libs/forceUTF8.php');
include('libs/email_lib.php');

if( !is_terminal() ){ echo "\nacceso denegado.\n"; return null; }
if( nargs()==1 ){ help(); return null; }

if( process() ){ return null; }

help();
return null;
?>