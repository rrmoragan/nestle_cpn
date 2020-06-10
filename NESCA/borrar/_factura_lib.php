<?php

define('LIB_IN1','v0.3');
include('basics.php');
include('querys.php');
include('forceUTF8.php');

define('IN1_ERR_01','error: el order_id no esta pagado');
define('IN1_ERR_02','error: obteniendo los datos de la compra');
define('IN1_ERR_03','error: al estructurar el archivo in1');
define('IN1_ERR_04','error: al obtener el nuevo numero de folio');
define('IN1_ERR_05','error: al guardar el archivo');

class FacturaIn1{

	private $data_order = null;
	private $data_in1 = null;
	public  $serie = '';
	private $folio = 0;
	public  $error = '';
	private $id_order = 0; /* id de la orden de conpra */
	private $lsum = null;
	public  $folio_ini = 0;

	public function new_billing( $id_order='', $dir ){
		// tt('new_billing()');
		tt('facturando '.$id_order);

		if($id_order==''){ return false; }

		/* determina si el order id esta pagado y es facturable */
		if( !$this->is_paid_order($id_order,$dir) ){    return false; }
		/* obtiene los datos de la orden de compra */
		if( !$this->purchase_order( $id_order ) ){ $this->error = IN1_ERR_02; return false; }
		/* estructura los datos de la factura */
		if( !$this->struct_in1() ){                $this->error = IN1_ERR_03; return false; }
		/* obtiene un nuevo folio */
		if( !$this->new_folio() ){				   $this->error = IN1_ERR_04; return false; }
		/* guarda el archivo in1 creado y en base de datos */
		if( !$this->save_in1($dir) ){			   $this->error = IN1_ERR_05; return false; }

		return true;
	}

	private function new_folio(){
		// tt('new_folio()');

		if( $this->folio_ini ){
			$new_folio = $this->new_folio_sql($this->folio_ini);
		}else{
			$new_folio = $this->new_folio_sql();			
		}

		$this->data_in1['Comprobante']['idUnico'] .= $new_folio;
		$this->data_in1['Comprobante']['Folio'] = "$new_folio";
		$this->folio = $new_folio;

		return true;
	}

	private function last_folio(){

		if( $this->serie == '' ){ return 0; }

		$serie = $this->serie;

		$s = "SELECT folio_id from nestle_me_114.facturacion_invoices where serie like '$serie' order by folio_id DESC limit 0,1";
		$a = query($s);
		if( $a==null ){ return 0; }

		return $a[0]['folio_id'];
	}

	private function new_folio_sql( $folio=0 ){
		if( $this->serie=='' ){ 
			tt('error: serie ==> null'); 
			return 0;
		}
		$serie = $this->serie;

		$t = time();
		$d = date('Y-m-d G:i:s',$t);

		$new_folio = 0;
		if($folio){
			$new_folio = $folio;
		}else{
			$new_folio = $this->last_folio() + 1;
			// tt($new_folio);
		}

		$s = "INSERT INTO `facturacion_invoices` (`id`, `serie`, `folio_id`, `consecutivo`, `status`, `creating`, `last_update`, `creating_time`, `last_update_time`) 
		VALUES (NULL, '$serie', '$new_folio', '$serie-$new_folio', 'creating', '$d', '$d', '$t', '$t');"; 
		$id = query($s);
		// tt($s);
		if( $id ){ return $new_folio; }

		return 0;
	}

	private function save_in1($dir=''){
		// tt('save_in1()');

		$this->save_in1_sql();

		// foreach ($this->data_in1 as $et => $r) { // tt($et); }

		$s = '';
		$tit = 'Comprobante'; 	$s = $s.     $this->save_in1_data( $this->data_in1[ $tit ], $tit, 0 );
		$tit = 'Extras'; 		$s = $s."\n".$this->save_in1_data( $this->data_in1[ $tit ], $tit );
		$tit = 'Emisor'; 		$s = $s."\n".$this->save_in1_data( $this->data_in1[ $tit ], $tit );
		$tit = 'Receptor'; 		$s = $s."\n".$this->save_in1_data( $this->data_in1[ $tit ], $tit );

		$i = 1;
		$tit = 'Concepto'; 
		foreach ($this->data_in1[ $tit ] as $et => $r) {
			$s = $s."\n".$this->save_in1_data( $this->data_in1[ $tit ][$i], $tit.$i );
			$i++;
		}

		$tit = 'Impuestos'; 	$s = $s."\n".$this->save_in1_data( $this->data_in1[ $tit ], $tit );

		$s = forceLatin1($s);

		$file = $this->data_in1['Comprobante']['idUnico'].'.in1';
		if( output_file($dir, $file, $s ) ){
			tt( 'archivo '.$file.' guardado en '.$dir );
		}

		return true;
	}

	private function save_in1_sql(){
		$tit = 'Comprobante';	$this->save_in1_sql_data( $this->data_in1[ $tit ], $tit );
		$tit = 'Extras'; 		$this->save_in1_sql_data( $this->data_in1[ $tit ], $tit );
		$tit = 'Emisor'; 		$this->save_in1_sql_data( $this->data_in1[ $tit ], $tit );
		$tit = 'Receptor'; 		$this->save_in1_sql_data( $this->data_in1[ $tit ], $tit );

		$i = 1;
		$tit = 'Concepto'; 
		foreach ($this->data_in1[ $tit ] as $et => $r) {
			$this->save_in1_sql_data( $this->data_in1[ $tit ][$i], $tit.$i );
			$i++;
		}

		$tit = 'Impuestos'; 	$this->save_in1_sql_data( $this->data_in1[ $tit ], $tit );

		$consecutivo = $this->serie.'-'.$this->folio;
		$s = "select * from facturacion_invoices where consecutivo like '$consecutivo'";
		$id = query($s);
		if( $id == null ){ return false; }

		$order_id = $this->data_order['order']['increment_id'];
		$customer = $this->data_order['order']['customer_id'];
		$email    = $this->data_order['order']['customer_email'];
		$t = time();

		$s = "UPDATE facturacion_invoices set 
			order_id = '$order_id',
			total = '".$this->data_in1['Comprobante']['Total']."',
			rfc = '".$this->data_in1['Emisor']['Rfc']."',
			cfdi = '".$this->data_in1['Receptor']['UsoCFDI']."',
			business_name = '".$this->data_in1['Receptor']['Nombre']."',
			customer_id = '$customer',
			customer_email = '$email',
			status = 'edited',
			last_update = '".date('Y-m-d G:i:s',$t)."',
			last_update_time = '$t'
			where id = ".$id[0]['id']."
			";
		query($s);
		// tt($s);

		return true;
	}

	private function save_in1_sql_data( $d=null, $cab='' ){
		if($d==null){ return false; }

		$d = forceLatin1( $d );

		$serie = $this->serie;
		$folio = $this->folio;

		$s = '';
		foreach ($d as $et => $r) {
			if( $s!='' ){ $s = $s.','; }
			$s = $s."(NULL, '$serie-$folio', '$cab', '$et', '$r', '1')";
		}

		$s = "INSERT INTO `facturacion_invoices_data` (`id`, `consecutivo`, `cab`, `reg`, `data`, `status`) VALUES $s";
		query($s);
		// tt("insertando $cab");
		// tt($s);

		return true;
	}

	private function save_in1_data($d='',$tit='', $chg_ln=1){
		if($d==null){ return ''; }

		$s = '';
		foreach ($d as $et => $r) { $s = $s."\n".$et.'='.$r; }

		if($tit){
			if( $chg_ln ){
				$s = "\n[".$tit."]".$s;
			}else{
				$s = "[".$tit."]".$s;
			}
		}else{
			if( $chg_ln ){
				$s = "\n[".$cab."]".$s;
			}else{
				$s = "[".$cab."]".$s;
			}
		}

		return $s;
	}

	private function is_paid_order( $id_order='',$dir='' ){
		// tt('is_paid_order()');

		/* validando si la factura ya ha sido creada */
			$s = "SELECT * FROM nestle_me_114.facturacion_invoices where order_id like '$id_order' and status <> 'canceled'";
			$a = query($s);
			if( $a!=null ){
				$this->error = 'factura ya creada para '.$id_order.' ==> '.$dir.$a[0]['consecutivo'].'.in1';
				return false;
			}

		/* validando si la orden de compra es valida para facturar */
			if($id_order==''){ return false; }

			$s = "SELECT entity_id FROM sales_flat_order where increment_id like '$id_order' and status like 'pagado'";
			$s = "SELECT
				sfo.entity_id
				FROM sales_flat_order as sfo
				inner join sales_flat_order_address as sfoa on sfoa.parent_id = sfo.entity_id
				where 
				sfo.increment_id like '$id_order'
				and sfo.status like 'pagado'
				and sfoa.is_billing = 1
				and sfoa.fax <> '' ";
			$a = query($s);
			//tt('sql ==> '.$s);
			if( $a==null ){
				$this->error = IN1_ERR_01;
				return false;
			}

			$this->id_order = $a[0]['entity_id'];

		return true;
	}

	private function purchase_order( $id_order='' ){
		// tt('purchase_order()');

		if($id_order==''){ return false; }

		/* datos de compra */
			$order = null;
			$s = "SELECT * FROM sales_flat_order where increment_id like '$id_order' and status like 'pagado'";
			$a = query($s);
			if($a==null){ return false; }

			$order['order'] = $a[0];

		/* datos de direccion de facturacion y envio */
			$s = "SELECT * FROM sales_flat_order_address where parent_id=".$order['order']['entity_id'];
			$a = query($s);
			if($a==null){ return false; }

			$order['addres_billing'] = $a[1];
			$order['addres_shipping'] = $a[0];

			if( $a[0]['is_billing']==1 ){
				$order['addres_billing'] = $a[0];
				$order['addres_shipping'] = $a[1];
			}

			$order['addres_billing'] = $this->valid_address_billing( $order['addres_billing'] );

		/* articulos */
			$s = "SELECT * FROM sales_flat_order_item where order_id=".$order['order']['entity_id'];
			$a = query($s);
			if($a==null){ return false; }

		/* obteniendo datos generales de productos */
			foreach ($a as $et => $r) {
				$prod_id = $r['product_id'];
				if( !isset( $order['product'][ $prod_id ] ) ){
					$b = $this->product_id( $prod_id );
					$b['prod_id']['value'] = $prod_id;
					$b['sku']['value'] = $r['sku'];
					$a[ $et ]['prod_id'] = $prod_id;
					$a[ $et ]['sku'] = $r['sku'];

					$order['product'][ $prod_id ] = $b;
				}
			}

			$order['items'] = $a;

		/* metodo de pago */
			$s = "SELECT * FROM sales_flat_order_payment where parent_id=".$order['order']['entity_id'];
			$a = query($s);
			$order['payment'] = $a;

		$this->data_order = $order;

		return true;
	}

	private function valid_address_billing($d=null){
		// tt('valid_address_billing()');

		if($d==null){
			tt('  address billing ==> null'); 
			return null;
		}

		$vrfc=0;
		$vcompany=0;
		$vcfdi=0;

		if( empty( $d['rfc'] ) ){ $vrfc=1; }
		if( empty( $d['company'] ) ){ $vcompany=1; }
		if( empty( $d['fax'] ) ){ $vcfdi=1; }

		if( $vrfc || $vcompany || $vcfdi ){

			$email = $d['email'];
			// tt('  obteniendo datos adicionales ==> '.$email);

			$s = "SELECT caev.*,
				ea.attribute_code
				FROM customer_address_entity_varchar as caev
				inner join eav_attribute as ea on ea.attribute_id = caev.attribute_id
				where 
				caev.entity_id= (
					SELECT entity_id FROM customer_address_entity
					where parent_id = ( 
						SELECT entity_id FROM customer_entity where email like '$email'
					) and
					is_billing = 1
				) and
				ea.attribute_code IN ( 'fax', 'rfc', 'company' )
				";
			$da = query($s);
			// tt("sql ==> ".$s);
			if($da == null){ return null; }

			foreach ($da as $et => $r) {
				switch ( $r['attribute_code'] ) {
					case 'fax': 	$d['fax'] = $r['value']; break;
					case 'rfc': 	$d['rfc'] = $r['value']; break;
					case 'company': $d['company'] = $r['value']; break;
				}
			}

		}

		//print_r($d);
		return $d;
	}

	private function struct_in1(){
		// tt('struct_in1()');

		if( $this->data_order == null ){ return false; }

		if( !$this->struct_extras() ){ 		tt('  error: extras'); return false; }
		if( !$this->struct_emisor() ){ 		tt('  error: emisor'); return false; }
		if( !$this->struct_receptor() ){ 	tt('  error: receptor'); return false; }
		if( !$this->struct_conceptos() ){ 	tt('  error: conceptos'); return false; }
		if( !$this->struct_impuestos() ){ 	tt('  error: impuestos'); return false; }
		if( !$this->struct_comprobante() ){ tt('  error: comprobante'); return false; }

		return true;
	}

	private function struct_comprobante(){
		// tt('struct_comprobante()');

		$serie = $this->serie;
		$t = time();
		$f = date( 'Y-m-d',$t).'T'.date('G:m:s',$t);
		$email = "frodriguez@mlg.com.mx;rmorales@mlg.com.mx;ymurillo@mlg.com.mx;mruiz@mlg.com.mx;lrodriguez@mlg.com.mx;fgonzalez@mlg.com.mx;aromero@mlg.com.mx";
		$email = "frodriguez@mlg.com.mx;rmorales@mlg.com.mx;ymurillo@mlg.com.mx;mruiz@mlg.com.mx;fgonzalez@mlg.com.mx;aromero@mlg.com.mx";

		$this->suma_total();

		$this->data_in1['Comprobante']['idUnico']			= $serie."-";
		$this->data_in1['Comprobante']['Version']			= "3.3";
		$this->data_in1['Comprobante']['Serie']				= $serie;
		$this->data_in1['Comprobante']['Folio']				= 0;
		$this->data_in1['Comprobante']['Fecha']				= $f;
		$this->data_in1['Comprobante']['FormaPago']			= "03";
		$this->data_in1['Comprobante']['CondicionesDePago']	= "";
		$this->data_in1['Comprobante']['Subtotal']			= sprintf( "%0.2f", $this->lsum['subtotal'] );
		$this->data_in1['Comprobante']['Descuento']			= sprintf( "%0.2f", $this->lsum['descuento'] );
		$this->data_in1['Comprobante']['Moneda']			= "MXN";
		$this->data_in1['Comprobante']['TipoCambio']		= "1.00";
		$this->data_in1['Comprobante']['Total']				= sprintf( "%0.2f", $this->lsum['total'] );
		$this->data_in1['Comprobante']['TipoDeComprobante']	= "I";
		$this->data_in1['Comprobante']['MetodoPago']		= "PUE";
		$this->data_in1['Comprobante']['LugarExpedicion']	= "11700";
		$this->data_in1['Comprobante']['Confirmacion']		= "";
		$this->data_in1['Comprobante']['Correo']			= $email;
		$this->data_in1['Comprobante']['FormatoCfdi']		= "";
		$this->data_in1['Comprobante']['Status']			= "";

		return true;
	}

	private function suma_total(){

		$subtotal = 0;
		$descuento = 0;
		$i = 1;

		foreach ($this->data_in1['Concepto'] as $et => $r) {
				$subtotal += $r['Importe'];
				$descuento += $r['Descuento'];
		}

		$this->lsum['subtotal'] = round( $subtotal, 2);
		$this->lsum['descuento'] = round( $descuento, 2);
		$this->lsum['impuesto'] = round( ($this->data_in1['Impuestos']['TotalImpuestosTrasladados']), 2);
		$this->lsum['total'] = round( (($this->lsum['subtotal'] - $this->lsum['descuento']) + $this->lsum['impuesto']), 2);

		return true;
	}

	private function struct_impuestos(){
		// tt('struct_impuestos()');

		if( empty( $this->data_in1['Concepto'] ) ){ return false; }

		/* obteniendo todos los "ImpuestosTraslado" */
		$a = null;
		$i = 1;
		if( $this->data_in1['Concepto'] != null ){
			foreach ($this->data_in1['Concepto'] as $et => $r) {
				if( $r != null ){
					foreach ($r as $etr => $rr) {
						$b = explode('ImpuestosTraslado', $etr);
						$n = count($b);
						if($n>1){
							$a[ $i ][ substr( $b[1] , 1) ] = $rr;
						}
					}
					$i++;
				}
			}
		}

		if($a==null){ return false; }

		/* obteniendo las diferentes tasas */
		$tasas = null;
		foreach ($a as $et => $r) {
			$tasas[ $r['TasaOCuota'] ] = 1;
		}

		/* sumando todas las "Base" de cada una de las tasas */
		$sum = null;
		foreach ($tasas as $et => $r) {
			foreach ($a as $etr => $rr) {
				if( $rr['TasaOCuota'] == $et ){
					if( isset( $sum[ $et ]['Base'] ) ){
						$sum[ $et ]['Base'] += $rr['Base'];
					}else{
						$sum[ $et ]['Base'] = $rr['Base'];
					}
					
					$sum[ $et ]['TasaOCuota'] = $rr['TasaOCuota'];

					// tt( "Base => ".$rr['Base']." ==> TasaOCuota ==> ".$rr['TasaOCuota'] );
				}
			}
			// print_r( $sum[ $et ] );
		}
		
		/* calculando el impuesto de cada una de las tasas */
		$tot = 0;
		foreach ($sum as $et => $r) {
			$sum[ $et ]['Importe'] = round( ($r['Base'] * $r['TasaOCuota']), 2 );
			$tot += $sum[ $et ]['Importe'];
		}

		$_tot = round( $tot, 2 );
		$this->data_in1['Impuestos']['TotalImpuestosRetenidos']='';
		$this->data_in1['Impuestos']['TotalImpuestosTrasladados']="$_tot";

		$i = 1;
		foreach ($sum as $et => $r) {
			$this->data_in1['Impuestos']['ImpuestosTraslado'.$i.'Impuesto']  ='002';
			$this->data_in1['Impuestos']['ImpuestosTraslado'.$i.'TipoFactor']='Tasa';
			$this->data_in1['Impuestos']['ImpuestosTraslado'.$i.'TasaOCuota']=sprintf( "%0.6f", $r['TasaOCuota'] );
			$this->data_in1['Impuestos']['ImpuestosTraslado'.$i.'Importe']   =sprintf( "%0.2f", $r['Importe'] );
			$i++;
		}

		// print_r( $this->data_in1['Impuestos'] );

		return true;
	}

	private function struct_extras(){
		// tt('struct_extras()');

		$this->data_in1['Extras']['AfterCreateStatus']='71';
		$this->data_in1['Extras']['ExtrasTexto01']="Ventas público general";
		$this->data_in1['Extras']['Description']="Ventas café para mi negocio";
		$this->data_in1['Extras']['YourReference']=$this->data_order['order']['increment_id'];
		$this->data_in1['Extras']['ExtrasNotas']="";

		return true;
	}

	private function struct_emisor(){
		// tt('struct_emisor()');

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

	private function struct_receptor(){
		// tt('struct_receptor()');

		$error_msg = '';

		if( !isset( $this->data_order['addres_billing']['rfc'] ) ){ $error_msg .= "\n".'error: falta rfc'; }
		if( !isset( $this->data_order['addres_billing']['company'] ) ){ $error_msg .= "\n".'error: nombre de la empresa'; }
		if( !isset( $this->data_order['addres_billing']['fax'] ) ){ $error_msg .= "\n".'error: falta uso de cfdi'; }

		if( $error_msg ){ 
			//print_r($this->data_order['addres_billing']);
			tt($error_msg);
			return false;
		}

		$this->data_in1['Receptor']['Rfc']				=$this->data_order['addres_billing']['rfc'];
		$this->data_in1['Receptor']['Nombre']			=$this->data_order['addres_billing']['company'];
		$this->data_in1['Receptor']['ResidenciaFiscal']	="";
		$this->data_in1['Receptor']['NumRegIdTrib']		="";
		$this->data_in1['Receptor']['UsoCFDI']			=$this->data_order['addres_billing']['fax'];

		return true;
	}

	private function struct_conceptos(){
		// tt('struct_conceptos()');

		/* validando datos sat */
		if( !$this->list_sat() ){ return false; }

		$i = 1;
		foreach ($this->data_order['items'] as $et => $r) {
			$d = $this->struct_concepto($r,$i);
			if( $d==null ){ 
				tt("  error: concepto ".$i); 
				return false;
			}
			$this->data_in1['Concepto'][$i] = $d;
			$i++;
		}

		if( $this->data_order['order']['shipping_amount'] == 0 ){
			// tt("numero de conceptos procesados ==> ".count($this->data_in1['Concepto']) );
			return true;
		}

		$d = $this->struct_conceptos_envio($i);

		if( $d==null ){ 
			tt("  error: concepto ".$i); 
			return false;
		}
		$this->data_in1['Concepto'][$i] = $d;

		// tt("numero de conceptos procesados ==> ".count($this->data_in1['Concepto']) );

		return true;
	}

	/* obtiene los datos sat de todos los productos en data_order */
	private function list_sat(){
		if( $this->data_order['items']==null ){ return false; }

		$err_com=0;
		foreach ($this->data_order['items'] as $et => $r) {
			$err=0;
			$sku = $this->data_order['product'][ $r['product_id'] ]['sku']['value'];

			$prdct = $this->data_order['product'][ $r['product_id'] ];

			if( !isset( $prdct['sat_clave'] ) ){ 		$err++; }
			if( !isset( $prdct['sat_clave_unidad'] ) ){ $err++; }

			if( $err ){
				echo " faltan claves SAT";
				$err_com++;
				continue;
			}

			if( trim( $prdct['sat_clave']['value'] ) == '' ){ $err++; }
			if( trim( $prdct['sat_clave_unidad']['value'] ) == '' ){ $err++; }

			if( $err ){
				echo " faltan claves SAT";
				$err_com++;
				continue;
			}

			echo "\n =====> ".print_r( $sku,true ).' - '.$prdct['sat_clave']['value'].' - '.$prdct['sat_clave_unidad']['value'];
		}

		if($err_com){ return false; }
		return true;
	}

	private function struct_conceptos_envio($i=1){
		// tt('struct_conceptos_envio()');

		$a['product_id'] 		= 'envio';
		$a['qty_ordered'] 		= '1';
		$a['name'] 				= 'Costo de envío';
		$a['price'] 			= $this->data_order['order']['shipping_amount'];
		$a['row_total'] 		= $a['price'];
		$a['discount_amount'] 	= '0';
		$a['tax_percent'] 		= '16';
		$a['tax_amount'] 		= $this->data_order['order']['shipping_tax_amount'];
		$a['unidad'] 			= 'Envio';
		$a['sku'] 				= 'envio';

		$this->data_order['product']['envio']['sat_clave']['value'] = '81141601';
		$this->data_order['product']['envio']['sat_clave_unidad']['value'] = 'SX';
		$this->data_order['product']['envio']['nombre_secundario']['value'] = '';

		//print_r( $this->data_order['product'][0] );
		//print_r( $a );

		$aux = null;
		$aux['bu'] = "7i5hg9lv27";
		$aux['pj'] = "6dktzt7frg";
		$aux['ct'] = "*rfc*";
		$aux['ac'] = "ACT(40290)";
		$aux['it'] = "1Mg_40290";

		return $this->struct_concepto($a,$i,$aux,1);
	}

	private function struct_concepto($d=null,$i=1,$aux=null,$envio=0){
		// tt('struct_concepto('.$i.')');

		if($d==null){ return null; }

		$Aux_BU="7i5hg9lv27";
		$Aux_PJ="6dktzt7frg";
		$Aux_CT="*rfc*";
		$Aux_AC="ACT(40240)";
		$Aux_IT="1Mg_40240";

		if($aux!=null){
			$Aux_BU = $aux['bu'];
			$Aux_PJ = $aux['pj'];
			$Aux_CT = $aux['ct'];
			$Aux_AC = $aux['ac'];
			$Aux_IT = $aux['it'];
		}

		$error_msg = '';
		$prdct = $this->data_order['product'][ $d['product_id'] ];

			if( !isset( $prdct['sat_clave'] ) ){ $error_msg .= "\nfalta sat clave"; }
			if( !isset( $prdct['sat_clave_unidad'] ) ){ $error_msg .= "\nfalta sat clave unidad"; }

			if( $error_msg ){
				//foreach ($prdct as $et => $r) { // tt(".... ".$et.' ==> '.$r['value']); }
				echo "\n#. ".$prdct['sku']['value'].' ==> '.$error_msg;
				return null;
			}

			if( $prdct['sat_clave']['value'] == '' ){ $error_msg .= "\nfalta sat clave"; }
			if( $prdct['sat_clave_unidad']['value'] == '' ){ $error_msg .= "\nfalta sat clave unidad"; }

			if( $error_msg ){
				//foreach ($prdct as $et => $r) { // tt(".... ".$et.' ==> '.$r['value']); }
				echo "\n#. ".$prdct['sku']['value'].' ==> '.$error_msg;
				return null;
			}

			if( !isset( $prdct['nombre_secundario'] ) ){
				$prdct['nombre_secundario']['value'] = '';
			}
			
		$unidad = "Caja";
		if( isset( $d['unidad'] ) && $d['unidad']!='' ){ $unidad = $d['unidad']; }

		$itb = $d['row_total'] - $d['discount_amount'];
		$prc = $d['tax_percent']/100;
		$impuesto = $itb * $prc;
		$cantidad = (int)$d['qty_ordered'];

		$a = null;
		$a['ClaveProdServ']						= $prdct['sat_clave']['value'];
		$a['NoIdentificacion']					= "";
		$a['Cantidad']							= "$cantidad";
		$a['ClaveUnidad']						= $prdct['sat_clave_unidad']['value'];
		$a['Unidad']							= $unidad;
		$a['Descripcion']						= $d['name'].' '.$prdct['nombre_secundario']['value'];
		$a['ValorUnitario']						= sprintf( "%0.2f", $d['price'] );
		$a['Importe']							= sprintf( "%0.2f", $d['row_total'] );
		$a['Descuento']							= sprintf( "%0.2f", $d['discount_amount'] );
		$a['ImpuestosTraslado1Base']			= sprintf( "%0.2f", $itb );
		$a['ImpuestosTraslado1Impuestos']		= "002";
		$a['ImpuestosTraslado1TipoFactor']		= "Tasa";
		$a['ImpuestosTraslado1TasaOCuota']		= sprintf( "%0.6f", $prc );
		$a['ImpuestosTraslado1Importe']			= sprintf( "%0.2f", $impuesto );
		$a['InformacionAduaneraNumeroPedimento']= "";
		$a['CuentaPredialNumero']="";
		$a['ConceptoTexto01']="";
		$a['ConceptoTexto02']="";
		$a['ConceptoTexto03']="";
		$a['ConceptoTexto04']="";
		$a['ConceptoTexto05']="";
		$a['ConceptoTexto06']="";
		$a['ConceptoTexto07']="";
		$a['ConceptoTexto08']="";
		$a['ConceptoTexto09']="";
		$a['ConceptoTexto10']="";
		$a['Aux_BU']=$Aux_BU;
		$a['Aux_PJ']=$Aux_PJ;
		$a['Aux_CT']=$Aux_CT;
		$a['Aux_AC']=$Aux_AC;
		$a['Aux_IT']=$Aux_IT;

		//echo "\n ===> ".$d['sku'];
		return $a;
	}

	public function product_id( $prod_id='' ){
		// tt('product_id()');
		
		$s = $this->sql_product_id( $prod_id );
		$a = query($s);
		if($a==null){ return null; }

		$b = null;
		foreach ($a as $et => $r) {
			$b[ $r['attribute_code'] ] = $r;
		}

		return $b;
	}

	private function sql_product_id( $prod_id='' ){
		// // tt('sql_product_id()');

		if( $prod_id=='' ){ return null; }

		$s = "SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label,t.attribute_id
				from catalog_product_entity_datetime as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $prod_id

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_decimal as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $prod_id

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_gallery as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $prod_id

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_int as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $prod_id

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_text as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $prod_id

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_url_key as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $prod_id

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_varchar as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $prod_id
				";

		return $s;
	}

	public function list_purchase_orders_to_invoice(){

		$s = "SELECT increment_id from sales_flat_order 
			inner join sales_flat_order_address on sales_flat_order_address.parent_id = sales_flat_order.entity_id
			where 
			status like 'pagado'
			and sales_flat_order_address.fax <> ''
			and sales_flat_order_address.street <> '.....'";
		$a = query($s);

		if($a==null){ return null; }

		$b=null;
		foreach ($a as $et => $r) {
			$b[] = $r['increment_id'];
		}

		return $b;
	}
}

?>
