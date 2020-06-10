<?php

if( !defined('MFACTURA') ){

define('MFACTURA','v1.0');

define('FC_ERROR_1','Introduzca un número de orden de venta válido');
define('FC_ERROR_2','Introduzca un rfc válido');
define('FC_ERROR_3','Introduzca la razón social');
define('FC_ERROR_4','Introduzca un email válido');
define('FC_ERROR_5','Seleccione un uso CFDI');
define('FC_ERROR_6','Orden de compra no encontrada');
define('FC_ERROR_7','Orden de compra no pagada');
define('FC_ERROR_8','Orden de compra ya esta facturada');
define('FC_ERROR_9','Orden de compra en proceso de facturación');

define('FACTURA_DIAS',5);

//define('FC_ERROR_10','Límite de tiempo excedido, se tiene un máximo de '.FACTURA_DIAS.' días despues de confirmado el pago para poder facturar.');
define('FC_ERROR_10','Lo sentimos, no es posible realizar la facturación.<br />El tiempo para facturar es de máximo '.FACTURA_DIAS.' días hábiles después de confirmado el pago. Para mayor información favor de contactar al call center (0155) 1250-2317.');

class mfactura{

	public $data = null;

	/* determina si la orden de compra ya fue facturada */
	public function is_factured( $so='' ){
		if( $so=='' ){ return false; }

		/*
		$so = new reportVentas();
		if( !$so->rv_sales( $so ) ){
			return false;
		}

		if( $so->data['factura'] != '' ){
			return true;
		}*/

		return false;
	}

	/* entrega un listado de los usos cfdi validos */
	public function cfdi_list(){
		$a = array(
			"G01" => "Adquisici&oacute;n de mercanc&iacute;as",
			"G02" => "Devoluciones, descuentos o bonificaciones",
			"G03" => "Gastos en general",
			"I01" => "Construcciones",
			"I02" => "Mobiliario y equipo de oficina por inversiones",
			"I03" => "Equipo de transporte",
			"I04" => "Equipo de computo y accesorios",
			"I05" => "Dados, troqueles, moldes, matrices y herramental",
			"I06" => "Comunicaciones telef&oacute;nicas",
			"I07" => "Comunicaciones satelitales",
			"I08" => "Otra maquinaria y equipo",
			"D01" => "Honorarios m&eacute;dicos, dentales y gastos hospitalarios.",
			"D02" => "Gastos m&eacute;dicos por incapacidad o discapacidad",
			"D03" => "Gastos funerales.",
			"D04" => "Donativos.",
			"D05" => "Intereses reales efectivamente pagados por créditos hipotecarios (casa habitaci&oacute;n).",
			"D06" => "Aportaciones voluntarias al SAR.",
			"D07" => "Primas por seguros de gastos m&eacute;dicos.",
			"D08" => "Gastos de transportación escolar obligatoria.",
			"D09" => "Dep&oacute;sitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.",
			"D10" => "Pagos por servicios educativos (colegiaturas)",
			"P01" => "Por definir",
		);

		return $a;
	}

	/* valida si llos datos ingresados son validos para empezar una factura */
	public function validate_so( $d=null ){
		if( $d == null ){ return null; }

		$el = array(
			'so' 		=> FC_ERROR_1,
			'srfc' 		=> FC_ERROR_2,
			'srz' 		=> FC_ERROR_3,
			//'semail' 	=> FC_ERROR_4,
			'scfdi' 	=> FC_ERROR_5,
		);

		$a = null;
		$err = 0;

		/* verificando existencia de variables */
			foreach ($el as $et => $r) {
				if( !isset( $d[ $et ] ) ){
					$err++;
					$a[ $et ]['campo'] = $et;
					$a[ $et ]['status'] = 'error';
					$a[ $et ]['message'] = $r;
				}
			}

			//print_r( $a );
			if( $err>0 ){ $this->data = $a; return null; }

		/* verificando que las varibles no esten vacias */
			foreach ($el as $et => $r) {
				$d[ $et ] = htmlentities( trim( $d[ $et ] ), ENT_QUOTES, "UTF-8" );
			}

			foreach ($el as $et => $r) {
				if( $d[ $et ] == '' ){
					$err++;
					$a[ $et ]['campo'] = $et;
					$a[ $et ]['status'] = 'error';
					$a[ $et ]['message'] = $r;
				}
			}

			if( $err>0 ){ $this->data = $a; return null; }

		/* verificando datos */
			if( !is_numeric( $d["so"] ) ){
				$err++;
				$a['so']['campo'] = 'so';
				$a['so']['status'] = 'error';
				$a['so']['message'] = FC_ERROR_1;
			}
			/*
			if (!filter_var( $d["semail"], FILTER_VALIDATE_EMAIL)) {
				$err++; 
				$a['semail']['campo'] = 'semail';
				$a['semail']['status'] = 'error';
				$a['semail']['message'] = FC_ERROR_4;
			}*/


			$d["srfc"] = strtoupper( $d["srfc"] );
			if( !$this->validate_rfc( $d["srfc"] ) ){
				$err++; 
				$a['srfc']['campo'] = 'srfc';
				$a['srfc']['status'] = 'error';
				$a['srfc']['message'] = FC_ERROR_2;
			}

			if( $this->validate_cfdi( $d["scfdi"] ) == '' ){
				$err++; 
				$a['scfdi']['campo'] = 'scfdi';
				$a['scfdi']['status'] = 'error';
				$a['scfdi']['message'] = FC_ERROR_5;
			}

		$this->data = $a;

		if( $err==0 ){
			return $d;
		}

		return null;
	}

	/* valida el rfc */
	public function validate_rfc( $s='' ){
		if( $s=='' ){ return false; }

		//// // log_data('log/factura_valid', 'validate_rfc' );
		$a = explode( ' ', $s );
		if( count($a)>1 ){ return false; }

		$long = strlen( $s );
		//// // log_data('log/factura_valid', 'validate_rfc ==> 1 ==> '.$long );
		if( $long<12 ){ return false; }

		/* validando persona moral */
		$saa = substr( $s, ( -1*$long ), 4 );
		$sab = substr( $s, ( -1*$long ), 3 );
		$sa = '';
		$i = -1*$long;

		/* persona fisica */
		if( ctype_alpha( $saa ) ){
			$sa = $saa;
			$i += 4;	$sb = substr( $s, $i, 6 );
			$i += 6;	$sc = substr( $s, $i );

		}else if( ctype_alpha( $sab ) ){
			/* persona moral */
			$sa = $sab;
			$i += 3;	$sb = substr( $s, $i, 6 );
			$i += 6;	$sc = substr( $s, $i );
		}

		$err = 0;
		//// // log_data('log/factura_valid', 'validate_rfc ==> 2 ==> ['.$sa.']' );
		if( !ctype_alpha( $sa ) ){ $err++; }

		//// // log_data('log/factura_valid', 'validate_rfc ==> 3 ==> ['.$sb.']' );
		if( !is_numeric( $sb ) ){ $err++; }
		//echo "\n $sb";

		//// // log_data('log/factura_valid', 'validate_rfc ==> 4 ==> ['.$sc.']' );
		if( !ctype_alnum( $sc ) ){ $err++; }
		if( strlen( $sc ) != 3 ){ $err++; }

		if( $err==0 ){ return true; }

		//// // log_data('log/factura_valid', 'validate_rfc ==> 5' );
		return false;
	}
	/* valida el uso cfdi */
	public function validate_cfdi( $s='' ){
		if( $s=='' ){ return ''; }

		$cfdi = $this->cfdi_list();

		if( isset( $cfdi[ $s ] ) ){
			return $cfdi[ $s ];
		}

		return '';
	}

	/* valida la orden de compra
	 * 		valida que exista
	 *		valida que su status este pagado
	 *		valida que no este facturado
	 */
	public function valid_so( $so='' ){

		$a['campo'] = 'so';
		$a['status'] = 'error';
		$a['message'] = FC_ERROR_6;

		//log_data('log/factura_valid',' ... validando datos para facturar');

		// validando facturado
			$rv = new reportVentas();

			$rv->rv_sales( $so );

			//log_data('log/factura_valid',' ... validando ['.print_r($rv->data,true).']');

			$facturado = false;

			if( $rv->data['factura_xml'] != null ){
				$facturado = true;
				log_data('log/factura_valid',' ... facturado['.$rv->data['factura_xml'].']');
			}

			$fact = new FacturaIn1();
			$fact->list_in1_so( $so );
			if( $fact->data ){ $fact->data = $fact->data[0]; }
			if( $fact->data ){
				if( $fact->data['file_xml'] != null ){
					$facturado = true;
					log_data('log/factura_valid',' ... facturado['.print_r( $fact->data['file_xml'],true ).']');
				}
			}

			if( $facturado ){
				$a['message'] = FC_ERROR_8;
				$this->data['so'] = $a;
				return false;
			}

		// validando la existencia de la orden de compra

			$sales = new mSales();
			$sales->sales( $so );
			if( $sales->data == null ){
				$a['message'] = FC_ERROR_6;
				$this->data['so'] = $a;
				return false;
			}
			
			$sales->data = $sales->data[0];

		// validando status de la orden de compra
			//log_data('log/factura_valid',' ... validando ['.print_r( $sales->data,true ).']');

			$status = $sales->data['status'];
			if( !($status == 'pagado' || $status == 'pending') ){
				$a['message'] = FC_ERROR_7;
				$this->data['so'] = $a;
				return false;
			}

		// validando tiempo para poder facturar
			$t = time();
			$t -= (60*60)*7;	// calibrar hora internacional
			$dia = 60*60*24;	// numero de segundos por dia

			$hoy_ini = mktime( 0,0,0, date('m',$t),date('d',$t),date('Y',$t) );
			$hoy_menos = $hoy_ini - ( ( ((int)FACTURA_DIAS) -1 ) * $dia );

			//log_data('log/factura_valid',' ... validando ['.$hoy_ini.']');
			//log_data('log/factura_valid',' ... validando ['.$hoy_menos.']');

		// obteniendo fecha de pago de openpay
			
			$date_completed = '';
			$opv = new openpayValidate();
			$opv->opv_sales( $so );
			//log_data('log/factura_valid',' ... validando openpay ['.print_r( $opv->data,true ).']');

			if( isset( $opv->data[ $so ] ) ){
				$opv->data = $opv->data[ $so ];
			}else{
				$opv->data = null;
			}

			if( $opv->data ){
				$status = $opv->data['status'];
				$seguimiento = $opv->data['seguimiento'];
				switch ( $seguimiento['method'] ) {
					case 'card': 			$date_completed = $seguimiento['operation_date']; break;
					case 'bank_account':	$date_completed = $seguimiento['operation_date']; break;
					case 'store': 			$date_completed = $seguimiento['operation_date']; break;
				}
			}

			if( $date_completed == '' ){
				$date_completed = $sales->data['updated_at'];
			}

			//log_data('log/factura_valid',' ... validando date_completed ['.$date_completed.']');

			// desabilitar esta linea
			//$status = '';
			if( $status == 'cancelled' ){
				$a['message'] = FC_ERROR_7;
				$this->data['so'] = $a;
				return false;				
			}

			// desabilitar esta linea
			//$date_completed = date( 'Y-m-d', time() ).'T01:01:01';
			if( $this->date_to_udate($date_completed) < $hoy_menos ){
				$a['message'] = FC_ERROR_10;
				$this->data['so'] = $a;
				return false;
			}

		// procesando datos
			$a['status'] = 'ok';
			$this->data['so'] = $a;

		return true;
	}
	/* guarda los datos para facturar */
	public function so_add_billing_data( $d=null ){
		//log_data('log/factura_valid', print_r( $d,true ) );

		$sales = $d['so'];
		$so = new mSales();

		if( !$so->sales_update( $sales, 'rfc',  $d['srfc'] ) ){ 	return false; }
		if( !$so->sales_update( $sales, 'cfdi', $d['scfdi'] ) ){ 	return false; }
		if( !$so->sales_update( $sales, 'rz',   $d['srz'] ) ){ 		return false; }

		$dir = $so->sales_address_billing( $sales );

		$error = 0;
		if( $dir['fax'] != $d['scfdi'] ){ $error++; }
		if( $dir['company'] != $d['srz'] ){ $error++; }
		if( $dir['rfc'] != $d['srfc'] ){ $error++; }

		if( $error>0 ){
			log_data('log/factura_valid', 'error en actualizacion' );
			return false;
		}

		$so->sales( $d['so'] );
		if( $so->data != null ){
			$so->data = $so->data[0];
		}

		//log_data('log/factura_valid', 'sales order ['.print_r( $so->data,true ).']' );

		$this->data = array(
			'sales' => $d['so'],
			'email' => $so->data['customer_email'],
			'rfc' => $d['srfc'],
			'cfdi' => $d['scfdi'],
			'rz' => $d['srz'],
		);
		//log_data('log/factura_valid', 'reporte de ventas ['.print_r( $this->data,true ).']' );

		// | 100000664 | pagado | 860.8000    | G03  | IBM111101KQ2  | compras@nabiak.com.mx                    | 234         | charges      |   |
		// desabilitar esta linea
		//$sales = '100000664';

		$rv = new reportVentas();
		$rv->rv_sales( $sales );
		//log_data('log/factura_valid', 'reporte de ventas ['.$sales.']' );
		//log_data('log/factura_valid', 'reporte de ventas ['.print_r( $rv->data,true ).']' );

		$error = 0;
		if( !$rv->rv_update( $rv->data['rs_id'], 'factura_rfc', $d['srfc'] ) ){ $error++; }
		if( !$rv->rv_update( $rv->data['rs_id'], 'factura_rz', $d['srz'] ) ){ $error++; }
		if( !$rv->rv_update( $rv->data['rs_id'], 'factura_cfdi', $d['scfdi'] ) ){ $error++; }

		if( $error==0 ){ return true; }

		return false;
	}

	/* convirtiendo facha texto a fecha unix
	 * 	s => fecvha en formato texto
	 *  form => formato en el que esta la fecha
	 *				por default "yyyymmdd hhiiss"
	 */
	public function date_to_udate( $s='' ){
		
		$fl = strlen( $s );
		if($fl>19){ $s = substr( $s,0,19 ); }

		//echo "\n longitud cadena ==> ".strlen( $s )." ==> ".$s;

		$hr = substr( $s,-8,2 );
		$mn = substr( $s,-5,2 );
		$sg = substr( $s,-2,2 );

		$di = substr( $s,-11,2 );
		$ms = substr( $s,-14,2 );
		$yr = substr( $s,-19,4 );

		$n = mktime( $hr,$mn,$sg, $ms,$di,$yr );
		//echo "\n $yr-$ms-$di $hr:$mn:$sg ==> $n => ".date( "Y-m-d G:i:s",$n );

		return $n;
	}
}

}

/*
$rfc = new mfactura();
$rfc->validate_rfc( 'mogr790714rg8' );
$rfc->validate_rfc( 'ART970227ATA' );
*/
?>