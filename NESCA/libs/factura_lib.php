<?php

if( !defined( 'LIB_IN1' ) ){

	include('basics.php');
	include('querys.php');
	include('forceUTF8.php');
	include('magento_sales.php');
	include('magento_user_lib.php');
	include('magento_product_lib.php');

	$lef =  'rmorales@mlg.com.mx;'.
			'ymurillo@mlg.com.mx;'.
			'mruiz@mlg.com.mx;'.
			'fgonzalez@mlg.com.mx;'.
			'aromero@mlg.com.mx';

	define('LIB_IN1','v0.4');
	define('IN1_ERR_01','error: el order_id no esta pagado');
	define('IN1_ERR_02','error: obteniendo los datos de la compra');
	define('IN1_ERR_03','error: al estructurar el archivo in1');
	define('IN1_ERR_04','error: al obtener el nuevo numero de folio');
	define('IN1_ERR_05','error: al guardar el archivo');
	define('EMAIL_SEND',$lef);

	class FacturaIn1{

		private $Aux_BU = null;
		private $data_in1 = array(
			'Comprobante' => null,
			'Extras' => null,
			'Emisor' => null,
			'Receptor' => null,
			'Conceptos' => null,
			'Impuestos' => null
		);
		private $folio = 0;
		public  $data_in1_string = '';
		public  $error = null;
		public  $data = null;
		public  $sales = null; // datos generales para in1
		private $force = false;
		private $sat_shipping = array(
			'sat_clave'			=> "81141601",
			'sat_clave_unidad'	=> "SX",
			'sat_unidad'		=> "Envio",
			'sat_descrip'	=>  "Costo de envío"
		);

		private $list_limit = 0;
		private $list_filtro = null;

		/* activa el status de forzado */
			public function force_on(){
				$this->force = true;
				// echo "\n .... inciando forzado";
				return null;
			}
		/* determian si el forzado esta activo */
			public function is_force(){
				if( $this->force == true ){ return true; }
				return false;
			}
		/* imprime en cadena los diferentes errores */
			public function error_print(){
				if( $this->error == null ){ return ''; }

				$s = "\nlog error [".date( 'Y-m-d G:i:s', time() )."] "."\n".$this->sales['sales']."\n";
				foreach ($this->error as $et => $r) {
					$s = $s."\n ==> $r";
				}

				return $s;
			}
		/* lista todos los in1 generados
			return 	boolean
			data 	$this->data
			*/
			public function list_in1(){
			
				$s = "SELECT * from factura_folio order by sales ASC";
				$a=query( $s );
				if( $a == null ){ return 0; }

				$this->data = $a;
				return count( $this->data );

				/*
				$rv = new reportVentas();
				if( $rv->rv_list_sales_in1() ){
					$this->data = $rv->data;
					$rv->data = null;

					$data = null;
					foreach ($this->data as $et => $r) {
						$xml = trim( $r['factura_xml'] );
						if( $xml != '' ){
							$data[ $et ] = $r;
						}
					}

					$this->data = $data;
					unset( $data );

					$this->data = array_filter_cols( $this->data, array(
						'entity_id',
						'status',
						'customer_id',
						'sales_order',
						'subtotal',
						'shipping',
						'tax',
						'total',
						'updated_at',
						'factura',
						'payment_method',
						'factura_rfc',
						'factura_rz',
						'factura_cfdi',
						'factura_email',
						'factura_xml',
						'factura_pdf',
					) );

					return true;
				}

				return false;*/
			}
		/* obtiene el registro in1 de 1 orden de venta */
			public function list_in1_so( $so='' ){
				if( $so=='' ){
					$this->data = null;
					return 0;
				}

				$s = "SELECT * from factura_folio where sales like '$so'";
				$a = query( $s );
				if( $a == null ){
					$this->data = null;
					return 0;
				}

				$this->data = $a;
				return count( $this->data );
			}
		/* lista todas las ordenes de compra con solicitudes de facturacion
			return 	boolean
			data 	$this->data
			*/
			public function list_sales_order_data_factura(){
				// lista las ordenes de venta que requieren ser facturadas
				
				// obtiene todas las ordenes de venta
					$sales = new mSales();

					$n = $sales->list_sales( 'DESC' );
					if( $n==0 ){
						echo "\n sin ordenes de venta";
						return 0;
					}

				// descartando las ordenes de venta diferentes de pagado

					foreach ($sales->data as $et => $r) {
						if( $r['status'] != 'pagado' ){ unset( $sales->data[ $et ] ); }
					}

				// descartando direcciones sin datos de factura

					foreach ($sales->data as $et => $r) {
						$sales->data[ $et ]['dir_billing'] = $sales->sales_address_billing( $et );
						if( $sales->data[ $et ]['dir_billing']['fax'] == '' ){
							unset( $sales->data[ $et ] );
						}
					}

				// limitando el numero de elementos a trabajar
					$b = null;
					$i = 0;
					foreach ($sales->data as $et => $r) {
						if( $this->list_limit>0 ){
							if( $i >= $this->list_limit ){
								break;
							}
						}
						$b[ $et ] = $r;

						$i++;
					}

					$sales->data = $b;

				// llenado de datos

					$i = 0;
					$this->data = null;
					foreach ($sales->data as $et => $r) {
						//if( $i==15 ){ print_r( $r ); }
						$this->data[ $et ]['sales'] 					= $et;
						$this->data[ $et ]['status'] 					= $r['status'];
						$this->data[ $et ]['total'] 					= $r['grand_total'];

						$this->data[ $et ]['subtotal'] 					= $r['subtotal'];
						$this->data[ $et ]['discount'] 					= $r['discount_amount'];
						$this->data[ $et ]['subtotal_tax'] 				= $r['subtotal_incl_tax'];

						$this->data[ $et ]['shipping_amount']			= $r['shipping_amount'];
						$this->data[ $et ]['shipping_discount']			= $r['shipping_discount_amount'];
						$this->data[ $et ]['shipping_incl_tax']			= $r['shipping_incl_tax'];

						$this->data[ $et ]['tax'] 						= $r['tax_amount'];

						$this->data[ $et ]['CFDI'] 						= $r['dir_billing']['fax'];
						$this->data[ $et ]['rfc'] 						= $r['dir_billing']['rfc'];
						$this->data[ $et ]['email'] 					= $r['dir_billing']['email'];
						$this->data[ $et ]['razon_social'] 				= $r['dir_billing']['company'];
						$this->data[ $et ]['customer_id']				= $r['customer_id'];

						$i++;
					}
					$sales->data = null;

				// obteniendo metodos de pago 

					foreach ($this->data as $et => $r) {
						$this->data[ $et ]['payment'] = null;
						$payment = $sales->sales_payment( $r['sales'] );
						$this->data[ $et ]['payment'] = $payment['method'];
					}

				// obteniendo todas las direcciones del usuario

					$dir = new mUser();

					foreach ($this->data as $et => $r) {
						$this->data[ $et ]['dir'] = null;

						if( trim($r['rfc']) 		 != '' && 
							trim($r['razon_social']) != '' &&
							trim($r['CFDI']) 		 != '' &&
							trim($r['email']) 		 != '' ){
							continue;
						}

						if( $dir->user_list_all_address( $this->data[ $et ]['customer_id'] ) > 0 ){
							$this->data[ $et ]['dir'] = $dir->data;
						}
					}

				// validando direcciones con datos utiles

					foreach ($this->data as $et => $r) {
						if( $r['razon_social'] == '' ){
							$this->data[ $et ]['razon_social'] 	= $this->rv_direccions( $r['dir'], 'company' );
						}
						if( $r['rfc'] == '' ){
							$this->data[ $et ]['rfc'] 			= $this->rv_direccions( $r['dir'], 'rfc' );
						}
					}

					foreach ($this->data as $et => $r) {
						unset( $this->data[ $et ]['dir'] );
					}

				// marcando registros facturados

					$pre = null;
					foreach ($this->data as $et => $r) {
						$pre[ $r['sales'] ] = $r;
						$pre[ $r['sales'] ]['f'] = ''; 
					}
					$this->data = null;

					$this->list_in1();

					if( $this->data ){
						foreach ($this->data as $et => $r) {
							if( isset( $pre[ $r['sales'] ] ) ){
								$xml = trim( $r['file_xml'] );
								if( $xml!='' ){
									$pre[ $r['sales'] ]['f'] = 'X';
								}
							}
						}
					}

				if( $this->list_filtro ){
					$pre = array_filter_cols( $pre, $this->list_filtro );
				}
				$this->data = $pre;
				return true;
			}
		/* determina si la orden de compra es valida para ser facturada */
			public function sales_order_data_factura( $so='' ){
				//echo "\n sales_order_data_factura()";

				// echo "\n .... paso 1";
				if( $so=='' ){
					$this->data = null;
					return false;
				}

				// echo "\n .... paso 2";

				$this->list_sales_order_data_factura();
				if( isset( $this->data[ $so ] ) ){
					// echo "\n .... paso 2.1";

					$this->data = $this->data[ $so ];
					if( $this->force==false ){
						if( $this->data['f'] == '*' ){
							$this->data = null;
							return false;
						}
					}
					return true;
				}

				// echo "\n .... paso 3";
				$this->data = null;
				return false;
			}
		/* obtiene el valor especificado de un arreglo de direcciones */
			public function rv_direccions( $a=null,$campo='' ){
				if( $a==null ){ return null; }
				if( $campo=='' ){ return null; }

				foreach ($a as $et => $r) {
					if( isset( $r[ $campo ] ) ){
						if( $r[ $campo ]['value'] != '' ){
							return $r[ $campo ]['value'];
						}
					}
				}

				return null;
			}
		/* agrega un registro de error al generar un archivo in1 */
			public function add_error_factura( $s='' ){
				if( $s=='' ){ return false; }
				$this->error[] = $s;
				return true;
			}
		
		/* valida que el folio customizado no este usado */
			private function exist_folio( $folio = 0 ){
				$this->folio = 0;
				if( $folio == 0 ){ return false; }
				if( $folio<0 ){ return false; }

				$s = "SELECT fid,sales,folio,serie,file_in1,file_xml,email,user_id from factura_folio where folio like $folio";
				$s = "SELECT count(*) as n from factura_folio where folio like '$folio'";
				//echo "\n sql ==> $s";
				$a = query( $s );
				if( $a[0]['n'] == 0 ) return false;
				return true;
			}
		/* genera una nueva factura */
			public function new_billing( $serie='', $so='', $force=false, $folio=0 ){
				echo "\n new_billing()";

				if( $folio > 0 ){
					echo "\n folio customizado";
					if( !$this->exist_folio( $folio ) ){
						$this->folio = $folio;
					}else{
						echo "\n error en el folio o ya esta utilizado";
					}
				}

				if( $serie=='' ){ $this->add_error_factura( 'falta numero de serie' ); return false; }
				if( $so=='' ){ $this->add_error_factura( 'falta numero de orden de compra' ); return false; }

				echo "\n serie:: $serie";
				echo "\n sales:: $so";

				if( $force==true ){ $this->force_on(); }

				/* si force esta activo se salta la validacion de exitencia de in1 y xml */
				if( !$this->is_force() ){
					$xml = $this->is_facturado( $so );
					$in1 = $this->is_in1( $so );

					if( $xml != '' ){
						echo "\n esta ordern ya esta facturada";
						echo "\n ... $xml";
						return false;
					}
					if( $in1 != '' ){
						echo "\n esta ordern ya esta creada";
						echo "\n ... $in1";
						return false;
					}
				}

				if( !$this->sales_order_data_factura( $so ) ){
					$this->add_error_factura( 'faltan datos para poder facturar' );
					return false;
				}

				$this->data['serie'] = $serie;
				$this->sales = $this->data;

				echo "\n PASO 1 .... [recopilando datos de productos]";
				$this->in1_add_data();	// obteniendo datos de productos
				if( $this->error ){ return false; }

				echo "\n PASO 2 .... [estrcturando in1]";
				$this->in1_struct();	// estructurando in1
				if( $this->error ){ return false; }

				echo "\n PASO 3 .... [guardando datos in1]";
				$file = $this->in1_save();
				if( $this->error ){ return false; }

				echo "\n ........... ".$file;

				echo "\n PASO 4 .... [guardando en base de datos]";
				$this->in1_save_data( $file );
				if( $this->error ){ return false; }

				return true;
			}
		/* depura los registros de factura para dejar solo 1 por orden de ventas */
			public function new_billing_depure(){
				$s = "SELECT * from factura_folio order by serie ASC, folio ASC";
				$a = query( $s );
				if( $a==null ){ return 0; }

				$b = null;
				foreach ($a as $et => $r) {
					$b[ $r['sales'] ] = $r;
				}

				foreach ($b as $et => $r) {
					foreach ($a as $etr => $rr) {
						if( $r['fid'] == $rr['fid'] ){
							unset( $a[ $etr ] );
						}
					}
				}

				foreach ($a as $et => $r) {
					$this->remove_billing( $r['fid'] );
				}

				return 0;
			}
		/* elimina la entrada de una factura */
			public function remove( $so='' ){
				if( $so=='' ){ return false; }

				$n = $this->list_in1_so( $so );
				if( $n == 0 ){
					$this->add_error_factura( 'sales order no encontrado' );
					return false;
				}

				foreach ($this->data as $et => $r) {
					$this->remove_billing( $r['fid'] );
				}

				return true;
			}
		/* elimina un registro de la tabla de facturacion */
			private function remove_billing( $bid=0 ){
				if( $bid==0 ){ return false; }

				$s = "DELETE from factura_folio where fid = $bid";
				query( $s );

				return true;
			}
		/* transpone los datos de un arrglo */
			public function data_transponer( $a=null ){
				if( $a==null ){ return null; }

				$b = null;
				foreach ($a as $et => $r) {
					$b[] = array( $et, $r );
				}

				return $b;
			}
		/* estructura primeros datos para in1 */
			public function in1_add_data(){
				//echo "\n in1_add_data()";

				/* obteniendo datos de compra de los productos */
				$n = $this->in1_products_data();
				if( $n==0 ){ return false; }

				if( $this->error != null ){
					return false;
				}

				return true;
			}
		/* obtiene datos de productos para in1 */
			public function in1_products_data( $so='' ){
				//echo "\n in1_products_data()";

				$this->data = null;

				$so = $this->sales['sales'];

				// obteniendo items
					$so = $this->sales['sales'];
					$s = "SELECT * FROM sales_flat_order_item where order_id = (
							select entity_id from sales_flat_order where increment_id like '$so'
						)";
					$a = query( $s );
					if( $a==null ){
						echo "\n ===> no data products";
						return 0;
					}

					//echo "\n ===> ".count($a);

				// obtiene datos de facturacion
					$p = new mProduct();

					$omite = null;

					foreach ($a as $et => $r) {
						$data = $p->product_data( $r['product_id'] );
						if( $data['sku'] == 'KIT-0010' ){
							$omite[] = $et;
							continue;
						}
						
						$a[ $et ]['sat_clave'] 			= '';
						$a[ $et ]['sat_descrip']		= '';
						$a[ $et ]['sat_unidad'] 		= '';
						$a[ $et ]['sat_clave_unidad'] 	= '';

						$err = $data['sku'].' ==> ';

						if( isset( $data['sat_clave'] ) ){
							$a[ $et ]['sat_clave'] = trim( $data['sat_clave'] ); }else{
							$this->add_error_factura($err.'falta clave sat');
						}
						if( isset( $data['sat_descrip'] ) ){
							$a[ $et ]['sat_descrip'] = trim( $data['sat_descrip'] ); }else{
							$this->add_error_factura($err.'falta sat descripcion');
						}
						if( isset( $data['sat_unidad'] ) ){
							$a[ $et ]['sat_unidad'] = trim( $data['sat_unidad'] ); }else{
							$this->add_error_factura($err.'falta sat unidad');
						}
						if( isset( $data['sat_clave_unidad'] ) ){
							$a[ $et ]['sat_clave_unidad'] = trim( $data['sat_clave_unidad'] ); }else{
							$this->add_error_factura($err.'falta sat clave unidad');
						}
					}

				// quitando elementos no facturables
					if( $omite ){
						foreach ($omite as $et => $r) {
							unset( $a[ $r ] );
						}
					}

				$this->data = $a;
				return count( $a );
			}
		/* estructura datos in1 */
			private function in1_struct(){
				//echo "\n in1_struct()";

				if( !$this->struct_extras() ){ 		$this->add_error_factura('extras'); return false; }
				if( !$this->struct_emisor() ){ 		$this->add_error_factura('emisor'); return false; }
				if( !$this->struct_receptor() ){ 	$this->add_error_factura('receptor'); return false; }
				if( !$this->struct_conceptos() ){ 	$this->add_error_factura('conceptos'); return false; }
				if( !$this->struct_impuestos() ){ 	$this->add_error_factura('impuestos'); return false; }
				if( !$this->struct_comprobante() ){ $this->add_error_factura('comprobante'); return false; }

				//print_r( $this->data_in1 );

				$this->data_in1_string = $this->in1_array_to_string();

				//echo "\n ===============================\n".$this->data_in1_string."\n ===============================";

				return true;
			}
		/* convierte los datos in1 de arreglo en in1 string */
			private function in1_array_to_string(){

				$s = '';

				$s .= $this->in1_array_to_string_elem( 'Comprobante' );
				$s .= $this->in1_array_to_string_elem( 'Extras' );
				$s .= $this->in1_array_to_string_elem( 'Emisor' );
				$s .= $this->in1_array_to_string_elem( 'Receptor' );
				$s .= $this->in1_array_to_string_conceptos();
				$s .= $this->in1_array_to_string_elem( 'Impuestos' );

				return $s;
			}

			private function in1_array_to_string_elem( $elem='' ){
				if( $elem=='' ){ return ''; }

				if( $this->data_in1[ $elem ] == null ){
					$this->add_error_factura( 'faltan datos in1' );
					return '';
				}

				$s = '';

				if( $elem != 'Comprobante' ){ $s .= "\n\n"; }
				$s = $s."[$elem]";
				foreach ($this->data_in1[ $elem ] as $et => $r) {
					$s = $s."\n".$et.'='.$r;
				}

				return $s;
			}

			private function in1_array_to_string_conceptos(){
				if( $this->data_in1['Conceptos'] == null ){
					$this->add_error_factura( 'faltan datos in1 conceptos' );
					return '';
				}

				$s = "";
				foreach ($this->data_in1['Conceptos'] as $et => $r) {
					$i = 1;
					foreach ($r as $etr => $rr) {
						if( $i==1 ){
							$s = $s."\n\n[$etr]";
						}else{
							$s = $s."\n".$etr.'='.$rr;
						}
						$i++;
					}
				}

				return $s;
			}
		/* estructura in1 extras */
			private function struct_extras(){
				//echo "\n struct_extras()";

				$this->data_in1['Extras']['AfterCreateStatus']='71';
				$this->data_in1['Extras']['ExtrasTexto01']="Ventas público general";
				$this->data_in1['Extras']['Description']="Ventas café para mi negocio";
				$this->data_in1['Extras']['YourReference']=$this->sales['sales'];
				$this->data_in1['Extras']['ExtrasNotas']="";

				return true;
			}
		/* estructura in1 emisor */
			private function struct_emisor(){
				//echo "\n struct_emisor()";

				$this->data_in1['Emisor']['Rfc']			= "MLG100224TC1";
				$this->data_in1['Emisor']['Nombre']			= "Master Loyalty Group, S.A. de C.V.";
				$this->data_in1['Emisor']['RegimenFiscal']	= "601";
				$this->data_in1['Emisor']['Calle']			= "Bosque de Duraznos 65 1002 A y B";
				$this->data_in1['Emisor']['NoExterior']		= "";
				$this->data_in1['Emisor']['NoInterior']		= "";
				$this->data_in1['Emisor']['Colonia']		= "Bosque de las Lomas";
				$this->data_in1['Emisor']['Localidad']		= "";
				$this->data_in1['Emisor']['Referencia']		= "";
				$this->data_in1['Emisor']['Municipio']		= "Miguel Hidalgo";
				$this->data_in1['Emisor']['Estado']			= "Ciudad de México";
				$this->data_in1['Emisor']['Pais']			= "México";
				$this->data_in1['Emisor']['CodigoPostal']	= "11700";

				return true;
			}
		/* estructura in1 receptor */
			private function struct_receptor(){
				//echo "\n struct_receptor()";

				$this->data_in1['Receptor']['Rfc']				=$this->sales['rfc'];
				$this->data_in1['Receptor']['Nombre']			=utf8_encode( $this->sales['razon_social'] );
				$this->data_in1['Receptor']['ResidenciaFiscal']	="";
				$this->data_in1['Receptor']['NumRegIdTrib']		="";
				$this->data_in1['Receptor']['UsoCFDI']			=$this->sales['CFDI'];

				return true;
			}
		/* estructura in1 conceptos */
			private function struct_conceptos(){
				//echo "\n struct_conceptos()";

				/* procesando productos */

					$this->Aux_BU = array(
						'Aux_BU' => '7i5hg9lv27',
						'Aux_PJ' => '6dktzt7frg',
						'Aux_CT' => '*rfc*',
						'Aux_AC' => 'ACT(40240)',
						'Aux_IT' => '1Mg_40240'
					);

					$i = 1;
					$p = null;
					foreach ($this->data as $et => $r) {
						$p[] = $this->struct_concepto( $r,$i );
						$i++;
					}

					$this->data_in1['Conceptos'] = $p;

					if( $this->sales['shipping_amount'] == 0 ){
						return true;
					}

				/* procesando envio */

					/*
					Aux_BU=7i5hg9lv27
					Aux_PJ=6dktzt7frg
					Aux_CT=*rfc*
					Aux_AC=ACT(40290)
					Aux_IT=1Mg_40290*/

					$this->Aux_BU['Aux_AC'] = "ACT(40290)";
					$this->Aux_BU['Aux_IT'] = "1Mg_40290";

					$envio = array(
						'sat_clave' 		=> $this->sat_shipping['sat_clave'],
						'sat_clave_unidad' 	=> $this->sat_shipping['sat_clave_unidad'],
						'sat_unidad' 		=> $this->sat_shipping['sat_unidad'],
						'sat_descrip' 		=> forceLatin1( $this->sat_shipping['sat_descrip'] ),
						'qty_ordered' 		=> 1,
						'price' 			=> $this->sales['shipping_amount'],
						'row_total' 		=> $this->sales['shipping_amount'],
						'discount_amount' 	=> $this->sales['shipping_discount'],
						'tax_percent' 		=> 16,
					);

					$p[] = $this->struct_concepto( $envio,$i );

					$this->data_in1['Conceptos'] = $p;

				return true;
			}

			private function struct_concepto( $a=null,$index=1 ){
				//echo "\n ... struct_concepto()";

				if( $a==null ){
					$this->add_error_factura('producto no determinado');
					return null;
				}

				/* calculo datos */
					$cantidad 	= (int)$a['qty_ordered'];
					$price 		= sprintf( "%0.2f", $a['price'] );
					$importe 	= sprintf( "%0.2f", $a['row_total'] );
					$descuento 	= sprintf( "%0.2f", $a['discount_amount'] );
					$itb 		= $importe - $descuento;
					$itb 		= sprintf( "%0.2f", $itb );
					$iva 		= $a['tax_percent']/100;
					$iva 		= sprintf( "%0.2f", $iva );
					$impuesto 	= $itb * $iva;
					$iva 		= sprintf( "%0.6f", $iva );
					$impuesto   = sprintf( "%0.2f", $impuesto );

					$sat_clave 			= $a['sat_clave'];
					$sat_claveunidad 	= $a['sat_clave_unidad'];
					$sat_unidad 		= $a['sat_unidad'];
					$sat_descripcion 	= utf8_encode( $a['sat_descrip'] );
					$index 				= "Concepto".$index;

				$p = null;

				$p[$index] 									= true;
				$p['ClaveProdServ'] 						= "$sat_clave";
				$p['NoIdentificacion'] 						= "";
				$p['Cantidad'] 								= "$cantidad";
				$p['ClaveUnidad'] 							= "$sat_claveunidad";
				$p['Unidad'] 								= "$sat_unidad";
				$p['Descripcion'] 							= "$sat_descripcion";
				$p['ValorUnitario'] 						= "$price";
				$p['Importe'] 								= "$importe";
				$p['Descuento'] 							= "$descuento";
				$p['ImpuestosTraslado1Base'] 				= "$itb";
				$p['ImpuestosTraslado1Impuestos'] 			= "002";
				$p['ImpuestosTraslado1TipoFactor'] 			= "Tasa";
				$p['ImpuestosTraslado1TasaOCuota'] 			= "$iva";
				$p['ImpuestosTraslado1Importe'] 			= "$impuesto";
				$p['InformacionAduaneraNumeroPedimento'] 	= "";
				$p['CuentaPredialNumero'] 					= "";
				$p['ConceptoTexto01'] 						= "";
				$p['ConceptoTexto02'] 						= "";
				$p['ConceptoTexto03'] 						= "";
				$p['ConceptoTexto04'] 						= "";
				$p['ConceptoTexto05'] 						= "";
				$p['ConceptoTexto06'] 						= "";
				$p['ConceptoTexto07'] 						= "";
				$p['ConceptoTexto08'] 						= "";
				$p['ConceptoTexto09'] 						= "";
				$p['ConceptoTexto10'] 						= "";
				$p['Aux_BU'] 								= $this->Aux_BU['Aux_BU'];
				$p['Aux_PJ'] 								= $this->Aux_BU['Aux_PJ'];
				$p['Aux_CT'] 								= $this->Aux_BU['Aux_CT'];
				$p['Aux_AC'] 								= $this->Aux_BU['Aux_AC'];
				$p['Aux_IT'] 								= $this->Aux_BU['Aux_IT'];

				return $p;
			}
		/* estructura in1 impuestos - calcula los impuestos */
			public function struct_impuestos(){
				//echo "\n struct_impuestos()";

				/* validaciones de existencia */
					if( !isset( $this->data_in1['Conceptos'] ) ){
						$this->add_error_factura('impuestos: faltan conceptos');
						return false;
					}
					if( $this->data_in1['Conceptos'] == null ){
						$this->add_error_factura('impuestos: faltan conceptos');
						return false;
					}

					$list_iva = null;
					foreach ($this->data_in1['Conceptos'] as $et => $r) {
						$list_iva[ $r['ImpuestosTraslado1TasaOCuota'] ][] = $r;
					}

				/* sumando subtotales por tasa */
					$suma = null;
					foreach ($list_iva as $et => $r) {
						$suma[ $et ]['tot'] = 0;
						$suma[ $et ]['impuesto'] = 0;
						$suma[ $et ]['traslado'] = 0;
						foreach ($r as $etr => $rr) {
							$suma[ $et ]['tot'] += $rr['Importe'];
							$suma[ $et ]['traslado'] += $rr['ImpuestosTraslado1Importe'];
						}
					}

				/* obteniendo subtotales por tasa */
					foreach ($suma as $et => $r) {
						$suma[ $et ]['impuesto'] = round( ($suma[ $et ]['tot'] * $et), 2, PHP_ROUND_HALF_DOWN );
					}

				/* generando etiquetas */

					$d = null;

					$d['TotalImpuestosRetenidos'] = '';
					$d['TotalImpuestosTrasladados'] = "0.00";

					/* impuesto cero */
						$i = 1;
						if( isset( $suma['0.000000'] ) ){
							$d['ImpuestosTraslado1Impuesto'] 	= '002';
							$d['ImpuestosTraslado1TipoFactor'] 	= 'Tasa';
							$d['ImpuestosTraslado1TasaOCuota'] 	= '0.000000';
							$d['ImpuestosTraslado1Importe'] 	= '0.00';
							unset( $suma['0.000000'] );
							$i++;
						}

					/* sumando impuestos con las diferentes tasas */
						$sum = 0;
						if( $suma != null ){
							foreach ($suma as $et => $r) {
								$im   = sprintf( "%0.2f", (float)$r['impuesto'] );
								$tasa = sprintf( "%0.6f", (float)$et );	//  0.160000

								$d['ImpuestosTraslado'.$i.'Impuesto'] 	="002";
								$d['ImpuestosTraslado'.$i.'TipoFactor'] ="Tasa";
								$d['ImpuestosTraslado'.$i.'TasaOCuota'] ="$tasa";
								$d['ImpuestosTraslado'.$i.'Importe'] 	="$im";

								$i++;
							}

							foreach ($suma as $et => $r) {
								$sum += $r['impuesto'];
							}

							$d['TotalImpuestosTrasladados'] = sprintf( "%0.2f", $sum );
						}

					$this->data_in1['Impuestos'] = $d;

					if( $sum != $this->data_in1['Impuestos']['TotalImpuestosTrasladados'] ){
						return false;
					}

				return true;
			}
		/* estructura comprobante */
			private function struct_comprobante(){
				//echo "\n struct_comprobante()";

				//print_r( $this->sales );

				$folio = 0;
				$folio = $this->folio;

				if( $this->folio == 0 ){
					$folio 	= $this->last_folio() + 1;
				}else{
					echo "\n guardando folio customizado";
				}

				$serie 	= "NESCA";
				$id 	= $serie.'-'.$folio;
				$ver 	= "3.3";
				$fecha 	= date( 'Y-m-d', time() ).'T'.date( 'G:i:s' );	// 2019-05-22T10:05:19
				$fp 	= $this->get_metodos_de_pago();

				$this->sales['metodo_pago'] = $fp;

				$subtotal 	= sprintf("%0.2f", ( $this->sales['subtotal'] + $this->sales['shipping_amount'] ) );
				$descuento 	= sprintf("%0.2f",0);
				$total    	= sprintf("%0.2f",$this->sales['total']);
				$email 		= EMAIL_SEND;
				$email 		= EMAIL_SEND.';'.$this->sales['email'];

				$this->sales['folio'] = $folio;

				$comprobante = null;
				$comprobante['idUnico']				= "$id";
				$comprobante['Version']				= "$ver";
				$comprobante['Serie']				= "$serie";
				$comprobante['Folio']				= "$folio";
				$comprobante['Fecha']				= "$fecha";
				$comprobante['FormaPago']			= "$fp";
				$comprobante['CondicionesDePago']	= "";

				$comprobante['Subtotal']			= "$subtotal";
				$comprobante['Descuento']			= "$descuento";
				$comprobante['Moneda']				= "MXN";
				$comprobante['TipoCambio']			= "1.00";
				$comprobante['Total']				= "$total";
				$comprobante['TipoDeComprobante']	= "I";
				$comprobante['MetodoPago']			= "PUE";
				$comprobante['LugarExpedicion']		= "11700";
				$comprobante['Confirmacion']		= "";
				$comprobante['Correo']				= "$email";
				$comprobante['FormatoCfdi']			= "";
				$comprobante['Status']				= "";

				$this->data_in1['Comprobante'] = $comprobante;

				$iva = $this->data_in1['Impuestos']['TotalImpuestosTrasladados'];

				if( !$this->is_force() ){
					$ssub = $this->suma_subtotal();
					$desc = $this->suma_discount();
					$siva = $this->suma_iva();
					$stot = $this->suma_total();
				}else{
					$ssub = $comprobante['Subtotal'];
					$desc = $comprobante['Descuento'];
					$siva = $iva;
					$stot = $comprobante['Total'];
				}

				$error = 0;

				if( $siva == -1 ){
					$this->add_error_factura('error impuestos');
					$error++;
				}

				$a = array(
					'reg1' => array( 'subtotal', 	$comprobante['Subtotal'],	$ssub ),
					'reg2' => array( 'descuento',	$comprobante['Descuento'],	$desc ),
					'reg3' => array( 'iva',			$iva,						$siva ),
					'reg4' => array( 'total',		$comprobante['Total'],		$stot ),
				);

				if( $this->is_force() ){
					return true;
				}

				if( $comprobante['Subtotal'] != $ssub ){
					$this->add_error_factura( 'suma subtotales diferentes' );
					$this->add_error_factura( print_table( $a ) );
					$error++;
				}
				if( $comprobante['Descuento'] != $desc ){
					$this->add_error_factura( 'suma descuentos diferentes' );
					$this->add_error_factura( print_table( $a ) );
					$error++;
				}
				if( $iva != $siva ){
					$this->add_error_factura( 'suma iva diferentes' );
					$this->add_error_factura( print_table( $a ) );
					$error++;
				}
				if( $comprobante['Total'] != $stot ){
					$this->add_error_factura( 'suma totales diferentes' );
					$this->add_error_factura( print_table( $a ) );
					$error++;
				}

				if( $error>0 ){
					return false;
				}

				return true;
			}
		/* obtiene la forma de pago */
			private function sat_metodos_de_pago(){
				$s = "SELECT * from sat_forma_de_pago where status like 'activo'";
				$a = query( $s );
				
				return $a;
			}
		/* obtiene el metodo de pago correspondiente a la compra */
			private function get_metodos_de_pago(){
				if( !isset( $this->sales['payment'] ) ){ return null; }

				$s = "SELECT
					satfp.*
					from sat_forma_de_pago_relation as satr
					inner join sat_forma_de_pago as satfp on satfp.forma_pago = satr.fp
					where satr.relacion like '".$this->sales['payment']."'";

				$a = query( $s );
				if( $a==null ){ return null; }

				return sprintf( "%02d", $a[0]['forma_pago'] );
			}
		/* obtiene el ultimo folio generado */
			private function last_folio(){

				$s = "SELECT * from factura_folio order by serie ASC, folio DESC limit 0,1";
				$a = query( $s );

				if( $a==null ){ return 100; }
				return $a[0]['folio'];
			}
		/* determina si la orden de compra ya esta facturada */
			private function is_facturado( $so='' ){
				//echo "\n is_facturado()";

				if( $so=='' ){ return false; }

				$s = "SELECT * from factura_folio where sales like '$so' and file_xml IS NOT NULL";
				$a = query( $s );
				if( $a == null ){ return ''; }

				return $a[0]['file_xml'];
			}
		/* determina si la orden de compra ya esta facturada */
			private function is_in1( $so='' ){
				//echo "\n is_in1()";

				if( $so=='' ){ echo "\n sin sales order"; return false; }

				$s = "SELECT * from factura_folio where sales like '$so' and file_in1 IS NOT NULL";
				//echo "\n sql ==> $s";
				$a = query( $s );

				if( $a == null ){ return ''; }

				$this->sales['in1'] = $a[0]['file_in1'];

				return $a[0]['file_in1'];
			}
		/* determina si el numero de folio ya fue utilizado */
			private function is_folio( $folio=0 ){
				if( $folio==0 ){ return true; }

				$s = "SELECT * from factura_folio where folio = $folio";
				$a = query( $s );
				if( $a==null ){ return false; }

				return true;
			}
		/* sumatorias */
		/* suma subtotales */
			private function suma_subtotal(){

				$suma = 0;
				foreach ($this->data_in1['Conceptos'] as $et => $r) {
					$suma += (float)$r['Importe'];
				}

				$suma = sprintf("%0.2f",$suma);

				return $suma;
			}
		/* suma ivas */
			private function suma_discount(){
				$suma = 0;
				return sprintf("%0.2f",$suma);
			}
		/* suma descuentos */
			private function suma_iva(){
				//echo "\n suma_iva()";

				$i = 1;
				$suma = 0;

				foreach ($this->data_in1['Impuestos'] as $et => $r) {
					$reg = 'ImpuestosTraslado'.$i.'Importe';
					if( $et == $reg ){
						$suma += $r;
						//echo "\n $reg = ".$r;

						$i++;
					}
				}

				if( $suma != $this->data_in1['Impuestos']['TotalImpuestosTrasladados'] ){
					$this->add_error_factura( 'sumatorias impuestos diferentes' );
					return -1;
				}

				$suma = sprintf("%0.2f",$suma);
				return $suma;
			}
		/* suma total */
			private function suma_total(){
				if( $this->suma_iva() == -1 ){
					$this->add_error_factura( 'error en impuestos' );
					return -1;
				}

				$suma = $this->suma_subtotal()-$this->suma_discount()+$this->suma_iva();
				$suma = sprintf("%0.2f",$suma);
				return $suma;
			}
		/* gusarda los datos in1 en archivo */
			public function in1_save(){
				if( $this->data_in1_string == '' ){ return ''; }

				$file = $this->data_in1['Comprobante']['idUnico'].'-'.$this->data_in1['Extras']['YourReference'].'.in1';
				$dir = 'facturas_data/';

				if( !output_file($dir, $file, $this->data_in1_string, 'w' ) ){
					echo "\n error: falla al guardar el archivo";
					return false;
				}

				return $file;
			}
		/* lista facturas generadas */
			public function factura_list(){
				$s = "SELECT * from factura_folio order by serie ASC, folio DESC";
				$a = query( $s );
				if( $a == null ){ return 0; }

				$b = null;
				foreach ($a as $et => $r) {
					$b[ $r['sales'] ] = $r;
				}

				$this->data = $b;

				unset($a);
				unset($b);

				return count( $this->data );
			}
		/* agrega una factura al listado general */
			public function factura_add( $d=null ){

				$err = 0;

				if( !isset( $d['sales'] ) ){ 		echo "\n ... error: falta sales_order"; $err++; }
				if( !isset( $d['serie'] ) ){ 		echo "\n ... error: falta numero de serie"; $err++; }
				if( !isset( $d['folio'] ) ){ 		echo "\n ... error: falta numero de folio"; $err++; }
				if( !isset( $d['rfc'] ) ){ 			echo "\n ... error: falta rfc"; $err++; }
				if( !isset( $d['email'] ) ){ 		echo "\n ... error: falta email"; $err++; }
				if( !isset( $d['customer_id'] ) ){ 	echo "\n ... error: falta user_id"; $err++; }
				if( !isset( $d['in1'] ) ){ 			echo "\n ... error: falta nombre de archivo in1"; $err++; }
				if( !isset( $d['razon_social'] ) ){ echo "\n ... error: falta razon social"; $err++; }
				if( !isset( $d['metodo_pago'] ) ){ 	echo "\n ... error: falta metodo de pago"; $err++; }
				if( !isset( $d['CFDI'] ) ){ 		echo "\n ... error: falta uso cfdi"; $err++; }

				if( $err > 0 ){ return 0; }

				$sales   = $d['sales'];
				$serie   = $d['serie'];
				$folio   = $d['folio'];
				$rfc     = $d['rfc'];
				$email   = $d['email'];
				$user_id = $d['customer_id'];
				$fecha   = date( 'Y-m-d G:i:s', time() );
				$in1     = $d['in1'];
				$xml     = '';
				$pdf     = '';
				$rz 	 = $d['razon_social'];
				$metodo  = $d['metodo_pago'];
				$cfdi 	 = $d['CFDI'];

				$s = "INSERT into factura_folio values( ".
					" null, ".
					" '$sales', ".
					" '$serie', ".
					" $folio, ".
					" '$rfc', ".
					" '$rz', ".
					" '$cfdi', ".
					" '$metodo', ".
					" '$email',  ".
					" $user_id,  ".
					" '$fecha',  ".
					" '$in1',  ".
				    " '$xml', ".
				    " '$pdf' ".
				" );";

				//$id = null;
				//echo "\n sql ==> $s";
				$id = query( $s );
				if( $id==null ){ return 0; }

				return $id;
			}
		/* guardando datos in1 en base de datos */
			public function in1_save_data( $file='' ){
				if( $this->sales == null ){ return false; }

				if( $this->is_force() ){

				}

				$this->sales['in1'] = $file;

				//print_r( $this->sales );

				$id = $this->factura_add( $this->sales );
				if( $id>0 ){
					return true;
				}

				return false;
			}
		/* forzando el guardado de datos in1 en base de datos */
			public function in1_save_data_force( $d=null ){
				if( $d==null ){
					$this->add_error_factura('add force ==> faltan datos');
					return false;
				}

				if( $this->is_folio( $d['folio'] ) ){
					$this->add_error_factura('add force ==> numero de folio ya utilizado');
					return false;
				}

				/*
				if( !$this->force ){
					if( $this->is_in1( $d['sales'] ) ){
						$this->add_error_factura('add force ==> orden de venta ya facturada');
						return false;
					}
				}*/

				$data = null;
				$data['sales'] = $d['sales'];
				$data['serie'] = $d['serie'];
				$data['folio'] = $d['folio'];
				$data['rfc'] = $d['rfc'];
				$data['email'] = $d['email'];
				$data['customer_id'] = 0;
				$data['in1'] = $d['serie'].'-'.$d['folio'].'-'.$d['sales'].'.in1';

				if( isset( $d['razon_social'] ) ){
					$data['razon_social'] = $d['razon_social'];
				}
				if( isset( $d['metodo_pago'] ) ){
					$data['metodo_pago'] = $d['metodo_pago'];
				}
				if( isset( $d['CFDI'] ) ){
					$data['CFDI'] = $d['CFDI'];
				}

				print_r( $data );

				$id = $this->factura_add( $data );
				if( !$id ){
					$this->add_error_factura('add force ==> error al agregar a la base de datos');
					return false;
				}

				return true;
			}
		/* actualiza el reporte de ventas con los datos de factura existentes */
			public function rsales_update(){
				$this->list_in1();
				//print_r( $this->data );

				$rv = new reportVentas();

				foreach ($this->data as $et => $r) {

					$rv->rv_sales( $r['sales'] );
					if( !$rv->data ){ continue; }

					echo "\n actualizando sales ==> ".$r['sales'];

					//print_r( $rv->data );

					$id = $rv->data['rs_id'];
					$rv->rv_update( $id, 'factura_rfc',   $r['rfc'] );
					$rv->rv_update( $id, 'factura_rz',    $r['rz'] );
					$rv->rv_update( $id, 'factura_cfdi',  $r['cfdi'] );
					$rv->rv_update( $id, 'factura_email', $r['email'] );
					$rv->rv_update( $id, 'factura_pdf',   $r['file_pdf'] );
					$rv->rv_update( $id, 'factura_xml',   $r['file_xml'] );
					$rv->rv_update( $id, 'factura',       $r['file_in1'] );
				}

				return true;
			}
		/* agrega el archivo xml a la orden de ventas */
			public function xml_update( $so='', $xml='' ){
				if( $so=='' ){
					$this->add_error_factura( 'falta numero de orden de ventas' );
					return false;
				}
				if( $xml=='' ){
					$this->add_error_factura( 'falta archivo xml' );
					return false;
				}

				$this->list_in1_so($so);
				if( !$this->data ){
					$this->add_error_factura( 'sin registro en la tabla de facturacion' );
					return false;
				}

				//print_r( $this->data );

				foreach ($this->data as $et => $r) {
					$id = $r['fid'];
					$this->fupdate( $id, 'file_xml', $xml.'.xml' );
					$this->fupdate( $id, 'file_pdf', $xml.'.pdf' );
				}

				return true;
			}
		/* actualiza un registro de la tabla de facturacion */
			public function fupdate( $id=0, $campo='',$val=null ){
				if( $id==0 ){ return false; }
				if( $campo=='' ){ return false; }

				$s = "UPDATE factura_folio set $campo = '$val' where fid = $id";
				//echo "\n sql ==> $s";
				query( $s );

				return true;
			}

		// nconfigura opciones de proceso
			public function process_config( $opcion='', $elem='', $data=null ){

				if( $opcion == '' ){ return false; }
				if( $elem == '' ){ return false; }
				if( !is_array( $data ) ){ $data = array( $data ); }

				switch ($opcion) {
					case '-sols':
						if( $elem[0] != '-' ){ return false; }
						$d = $this->get_opcions( $elem,$data );

						$this->config_sols( $elem, $d );
						break;
				}

				return true;
			}
			public function get_opcions( $elem='' ,$data=null ){

				if( $data == null ){ return null; }

				$e = false;
				$d = null;
				foreach ($data as $et => $r) {
					if( $e ){
						if( $r[0] != '-' ){
							$d[] = $r;
						}
					}
					if( $r == $elem ){
						$e = true;
					}
				}

				return $d;
			}
			public function config_sols( $elem='', $data=null ){
				switch ( $elem ) {
					case '-elems':
						$n = (int)$data[0];
						if( $n>0 ){
							$this->list_limit = $n;
							return true;
						}
						break;
					case '-all':
						$this->list_limit = 0;
						return true;
						break;
					case '-filtro':
						if( $data == null ){
							return false;
						}

						$this->list_filtro = $data;
						return true;
						break;
				}

				return false;
			}
		// lista nombres de archivos TITANO
			public function all_in1_to_titano( $a=null ){
				if( $a==null ){ return null; }

				$b = null;
				foreach ($a as $et => $r) {
					$c = $this->in1_to_titano( $r['file_in1'] );
					if( $c != '' ){
						$b[ $r['sales'] ] = $c;
					}
				}

				return $b;
			}
			public function in1_to_titano( $s='' ){
				if( $s=='' ){ return ''; }

				$a = explode('-', $s);
				$s = $a[0].$a[1].'_';

				return $s;
			}
	}

/*

truncate factura_folio;
drop table factura_folio;

CREATE TABLE `factura_folio` (
	`fid`       INT NOT NULL AUTO_INCREMENT COMMENT 'id factura',
	`sales`     VARCHAR(16) NULL COMMENT 'sales_order al que pertenece',
	`serie`     VARCHAR(8) NULL COMMENT 'numero de serie',
	`folio`     INT NULL COMMENT 'ultimo folio utilizado para el numero de serie',
	`rfc`       VARCHAR(14) NULL COMMENT 'rfc del cliente',
	`rz`        VARCHAR(255) NULL COMMENT 'razon social',
	`cfdi`      VARCHAR(5) NULL COMMENT 'uso cfdi',
	`metodo`    VARCHAR(64) NULL COMMENT 'metodo de pago',

	`email`     VARCHAR(128) NULL COMMENT 'email del usuario',
	`user_id`   VARCHAR(128) NULL COMMENT 'id del usuario',
	`update_at` DATETIME NULL COMMENT 'fecha de creacion',
	`file_in1`  VARCHAR(128) NULL COMMENT 'archivo in1',
	`file_xml`  VARCHAR(128) NULL COMMENT 'archivo xml',
	`file_pdf`  VARCHAR(128) NULL COMMENT 'archivo pdf',
	PRIMARY KEY (`fid`) )
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_unicode_ci
	COMMENT = 'Control consecutivo de los folios facturados';


CREATE TABLE `sat_forma_de_pago` (
	`fp_id` INT NOT NULL AUTO_INCREMENT COMMENT 'SAT listado de las formas de pago',
	`forma_pago` INT NULL,
	`descripcion` VARCHAR(128) NULL,
	`status` VARCHAR(45) NULL DEFAULT 'activo',
	`update_at` DATETIME NULL,
	PRIMARY KEY (`fp_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_unicode_ci
	COMMENT = 'Listado de las formas de pago para del SAT';

	insert into sat_forma_de_pago values( null, 1, 'Efectivo', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 2, 'Cheque', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 3, 'Transferencia Electrónica de Fondos', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 4, 'Trajeta de Credito', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 5, 'Monedero Electrónico', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 6, 'Dinero Electrónico', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 8, 'Vales de Despensa', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 28, 'Tarjeta de Débito', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 29, 'Tarjeta de Servicio', 'activo', '2019-09-27 19:12:45' );
	insert into sat_forma_de_pago values( null, 99, 'Otros', 'activo', '2019-09-27 19:12:45' );

CREATE TABLE `sat_forma_de_pago_relation` (
	`sat_pr_id` INT NOT NULL AUTO_INCREMENT,
	`fp` INT NULL,
	`relacion` VARCHAR(128) NULL,
	`status` VARCHAR(45) NULL DEFAULT 'activo',
	PRIMARY KEY (`sat_pr_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_unicode_ci
	COMMENT = 'relaciona los metodos de pago con sat forma de pago';

	insert into sat_forma_de_pago_relation values( null, 1, 'stores', 'activo' );
	insert into sat_forma_de_pago_relation values( null, 3, 'banks', 'activo' );
	insert into sat_forma_de_pago_relation values( null, 3, 'charges', 'activo' );

select
*
from sat_forma_de_pago_relation as satr
inner join sat_forma_de_pago as satfp.forma_pago = satr.
where satr.relacion like 'stores'

*/

}
?>