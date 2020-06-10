<?php
/* libreria de facturacion */

include('factura.define.php');

function help(){
	$s = "\n php factura.php <arg...>";
	$s = "$s\n   --list\t\tlista todas las ordenes de compra que requieren factura";
	$s = "$s\n   --all\t\tprocesa todas las ordenes de compra en archivos in1 para ser facturados";
	echo $s;
	return null;
}

class FacturaIn1{

	public $pedido = null;			/* numero de pedido y forma de pago */
	public $magento_vars = null;	/* contiene las variables personalizadas de magento */
	public $factura = array(		/* contiene los datos de la factura */
		'status'		=>'',		/* string */
		'folio'			=>0,		/* numero consecutivo de factura */
		'factura'		=>'',

		'comprobante'	=>'',
		'extras'		=>'',
		'emisor'		=>'',
		'receptor'		=>'',
		'concepto'		=>null,
		'impuestos'		=>''
	);
	public $productos = null;	/* productos relacionados con el numero de orden 
									$productos[id_producto][order]
									$productos[id_producto][data]
									*/
	public $user = null;		/* datos del usuario que realizo la compra */
	public $precio_envio = null;

	public $sys_data = null;

	public $pruebas = 0;		/* sistema de pruebas */

	/* obtiene todas las ordenes de compra que han solicitado ser facturadas */
	public function list_orden_billing(){
		$this->log_add( '==================== FACTURA MLG ['.DESCIP.'] ===================================================================' );
		$this->log_add( 'called ==> list_orden_billing() ==> ' );

		$s = $this->sql_list_orden('pagado');
		$this->log_add( $s );		
        $a=query( $s );

        return $a;
	}
	/* obtiene los datos de una orden de compra */
	public function data($orden=0){
		$this->log_add( '==================== orden de compra ['.$orden.'] ===================================================================' );
		$this->log_add( 'called ==> data() ==> ' );

		if($orden==0){ log_add( 'sin orden de compra' ); return false; }

		/* no existen datos de pedido */
		if( !$this->exist_pedido($orden) ){
			$this->log_add( 'sin datos de pedido' );
			return false;
		}

		/* ya existe una factura */
		$folio = 0;
		if( !$this->pruebas ){
			$folio = $this->exist_factura($orden);	
		}
		if( $folio > 0 ){
			$this->log_add( 'ya fue creado el archivo in1' );
			return false;
		}

		if( !$this->pedido_productos() ){
			$this->log_add( 'sin datos de productos del pedido' );
			return false;
		}
		if( !$this->pedido_user() ){
			$this->log_add( 'sin datos de usuario' );
			return false;
		}

		return true;
	}
	/* obtiene variables personalizadas magento */
	public function magento_varibles_personalizadas(){
		$this->log_add( 'called ==> magento_varibles_personalizadas() ==> ' );

		$s = $this->sql_variables_personalizadas();
		$a=query( $s );
		if($a==null){ return false; }

		$b=null;
		foreach ($a as $et => $r) {
			$b[ $r['code'] ] = $r;
		}

		$this->magento_vars = $b;

		return true;
	}
	/* regresa el valor de una variable personalizada de magento */
	public function magento_vars($s=''){
		if($s==''){ return null; }

		if(isset( $this->magento_vars[ $s ] )){
			return $this->magento_vars[ $s ]['plain_value'];
		}

		return null;
	}
	/* regresa el id del numero de orden de compra dado */
	public function exist_pedido($orden=0){
		$this->log_add( 'called ==> exist_pedido() ==> ' );

		if($orden=='') return 0;

		$s = $this->sql_orden( $orden );
		$a=query($s);
		if($a==null){ return 0; }

		$a = $a[0];
		if($a['is_billing']!=1){ return 0; }

		$this->pedido = $a;
		return $this->pedido['entity_id'];
	}
	/*	regresa el numero de folio de la factura para la orden de compra dada, en caso de no existir regresa cero */
	public function exist_factura($orden=''){
		$this->log_add( 'called ==> exist_factura() ==> ' );

		if($orden==''){ return 0; }

		if( $this->factura['folio']>0 ){
			return $this->factura['folio'];
		}

		$s = $this->sql_factura_search( $orden, SERIE );
		$a=query( $s );
		if($a==null){
			$this->factura['status'] = '';
			$this->factura['folio'] = 0;
			return 0;
		}

		$a = $a[0];

		if( $a['status'] == 'ediatndo' ){
			$this->factura['status'] = '';
			$this->factura['folio'] = 0;
			return 0;
		}

		$this->factura['status'] = $a['status'];
		$this->factura['folio']  = $a['folio_id'];

		return $this->factura['folio'];
	}
	/* obtiene los datos de los productos registrados en la orden de compra */
	public function pedido_productos(){
		$this->log_add( 'called ==> pedido_productos() ==> ' );
		
		if( !isset( $this->pedido['entity_id'] ) ){
			return false;
		}

		/* id de la orden de pedido */
		$s = $this->sql_orden_prods( $this->pedido['entity_id'] );
		$a=query( $s );
		if($a==null) return false;

		foreach ($a as $et => $r) {
			$id_prod = $r['product_id'];
			$d = array(
				'order'=>$r,
				'data' =>$this->pedido_productos_data( $id_prod )
			);
			$this->productos[] = $d;
		}

		return true;
	}
	/* obtiene los datos de un producto */
	public function pedido_productos_data($id=0){
		$this->log_add( 'called ==> pedido_productos_data() ==> '.$id );

		if($id==0){ return null; }

		$s = $this->sql_product_data($id);
		$b=query( $s );
		if($b==null){ return false; }

		$c=null;
		foreach ($b as $etr => $rr) {
			$c[ $rr['attribute_code'] ] = $rr['value'];
		}

		return $c;
	}
	/* obtiene los datos necesarios del usuario */
	public function pedido_user(){
		$this->log_add( 'called ==> pedido_user() ==> ' );

		if( !isset( $this->pedido['customer_id'] ) ){
			return false;
		}

		$id_user = $this->pedido['customer_id'];

		$s = $this->sql_order_user( $id_user );
		$a=query( $s );
		if($a==null){
			return false;
		}

		$this->user['customer_id'] = $id_user;
		foreach ($a as $et => $r) {
			$this->user[ $r['attribute_code'] ] = $r['value'];
		}

		if( PRODUCCION == 0 ){
			$this->user['company']	= USER_CORP;
			$this->user['rfc'] 		= USER_RFC;
		}

		$this->log_add( print_r( $this->user, true ) );
		return true;
	}
	/* regresa un concentrado con toda la informacion de las variables utilizadas por la clase */
	public function factura_vars(){
		$this->log_add( 'called ==> factura_vars() ==> ' );

		$s = '';
		$ss = "\n=========================\n";
		$s = $s.$ss." pedido ==> "; 
		$dat = "null"; 
		if( isset($this->pedido) ){
			$dat = print_r( $this->pedido, true );
		}
		$s = $s.$dat;
		$s = $s.$ss." magento_vars ==> "; 	$dat = "null"; if( isset($this->magento_vars) ){ 	$dat = print_r( $this->magento_vars, true ); }	$s = $s.$dat;
		$s = $s.$ss." factura ==> "; 		$dat = "null"; if( isset($this->factura) ){ 		$dat = print_r( $this->factura, true ); }		$s = $s.$dat;
		$s = $s.$ss." productos ==> "; 		$dat = "null"; if( isset($this->productos) ){ 		$dat = print_r( $this->productos, true ); }		$s = $s.$dat;
		$s = $s.$ss." user ==> "; 			$dat = "null"; if( isset($this->user) ){ 			$dat = print_r( $this->user, true ); }			$s = $s.$dat;

		return $s;
	}
	/* estructura los datos para generar in1 */
	public function structure(){
		$this->log_add( 'called ==> structure() ==> ' );

		$this->magento_varibles_personalizadas();

		if( !$this->in1_extras() ){ 	return false; }
		if( !$this->in1_emisor() ){ 	return false; }
		if( !$this->in1_receptor() ){ 	return false; }
		if( !$this->in1_concepto() ){ 	return false; }
		if( !$this->in1_impuestos() ){ 	return false; }
		if( !$this->in1_comprobante() ){return false; }

		return true;
	}
	/* obtiene el folio que será utilizado para la factura */
	public function folio(){
		$this->log_add( 'called ==> folio() ==> ' );

		if( !$this->pruebas ){
			if( $this->factura['folio']>0 ){
				return $this->factura['folio'];
			}

			/* revisando numero de orden */
			if( !isset($this->pedido['increment_id']) ){
				return 0;
			}

			/* busca si el numero de orden ya tiene folio */
			$folio = $this->folio_search_order( $this->pedido['increment_id'] );
			/* ya tiene un folio */
			if( $folio ){
				$this->log_add("ya existe un folio para esta orden de compra\r\n\r\n");

				$this->factura['folio_id']	= $folio['id'];
				$this->factura['folio']		= $folio['folio_id'];
				$this->factura['status']	= $folio['status'];
				$this->factura['serie']		= $folio['serie'];
				$this->factura['factura']	= $folio['serie'].'-'.$folio['folio_id'];

				return $folio['folio_id'];
			}
		}

		/* en caso de que $folio == 0 hay que creaar un folio para el numero de orden */
		$folio = $this->folio_create( $this->folio_last()+1 );
		return $folio;
	}
	/* regresa la fecha y hora actuales segun formato factura */
	public function factura_date(){
		$this->log_add( 'called ==> factura_date() ==> ' );

		return $this->ftime();
	}
	/* crea un nuevo folio */
	public function folio_create($consecutivo=0){
		$this->log_add( 'called ==> folio_create() ==> ' );

		if($consecutivo==0){ return 0; }

		if( PRODUCCION!=1 ){
			$this->user['rfc'] = USER_RFC;
			$this->user['company'] = utf8_decode( USER_CORP );
		}

		$status = 'editando';

		$s="INSERT INTO `facturacion_invoices` 
			(`id`, 
			`serie`, 
			`folio_id`, 
			`order_id`, 
			`items`, 
			`qty`, 
			`subtotal`,
			`envios`,
			`impuestos`, 
			`total`, 
			`rfc`, 
			`business_name`, 
			`billing_method`, 
			`customer_id`, 
			`customer_email`, 
			`status`, 
			`last_update`) 
			VALUES (
			'', 
			'".SERIE."', 
			'".$consecutivo."', 
			'".$this->pedido['increment_id']."', 
			'".sprintf("%0.2f",$this->pedido['total_item_count'])."', 
			'".sprintf("%0.2f",$this->pedido['total_qty_ordered'])."',
			'".sprintf("%0.2f",$this->pedido['subtotal'])."',
			'".sprintf("%0.2f",$this->pedido['envio'])."',
			'".sprintf("%0.2f",$this->pedido['tax_amount'])."',
			'".sprintf("%0.2f",$this->pedido['grand_total'])."', 
			'".$this->user['rfc']."', 
			'".$this->user['company']."', 
			'".$this->pedido['billing_method']."', 
			'".$this->pedido['customer_id']."', 
			'".$this->pedido['customer_email']."', 
			'".$status."', 
			'".$this->factura_date()."');";

		$id = query($s);
		if($id==null){ return 0; }

		$this->factura['folio_id']	= $id;
		$this->factura['folio']		= $consecutivo;
		$this->factura['status']	= $status;
		$this->factura['factura']	= SERIE.'-'.$consecutivo;
		$this->factura['serie']		= SERIE;

		return $consecutivo;
	}
	/* obtiene el ultimo folio generado */
	public function folio_last(){
		$this->log_add( 'called ==> folio_last() ==> ' );

		$s = $this->sql_folio_last( SERIE );
		$this->log_add($s);
		$a=query( $s );
		if($a==null){ return (FOLIO_INI-1); }

		return $a[0]['folio_id'];
	}
	/* regresa el folio del numero de orden de compra */
	public function folio_search_order($order=0){
		$this->log_add( 'called ==> folio_search_order() ==> ' );

		if( $order==0 ){ return -1; }

		$s = $this->sql_folio_search_order( $order, SERIE );
		$this->log_add($s);
		$a=query( $s );
		if( $a==null ){ return null; }

		return $a[0];
	}
	/* obtiene el uso cfdi del usuario $this->pedido['uso_cfdi'] */
	public function uso_cfdi(){
		$this->log_add( 'called ==> uso_cfdi() ==> ' );

		$uso_cfdi='G03';

		if( isset( $this->pedido['uso_cfdi'] ) ){
			$uso_cfdi = $this->pedido['uso_cfdi'];
		}

		return $uso_cfdi;
	}
	/* procesa todos los articulos comprados generando los conceptos */
	public function llena_concepto(){
		$this->log_add( 'called ==> llena_concepto() ==> ' );

		if( $this->productos == null  ){ return false; }

		/* genera los conceptos de los productos */
		foreach ($this->productos as $et => $r) {
			if( $r['order']['parent_item_id']!=null ){ continue; }
			$this->factura['concepto'][] = $this->_llena_concepto($r);
		}

		return true;
	}
	/* llena el arreglo del concepto */
	public function _llena_concepto($b=null){
		$this->log_add( 'called ==> _llena_concepto() ==> ' );

		if($b==null){ return null; }

		$dat = $b['data'];
		$ord = $b['order'];

		$ord['tax_percent'] = $ord['tax_percent']/100;

		$ord['price'] 		= round($ord['price'], 2);
		$ord['row_total'] 	= round($ord['row_total'], 2);
		$ord['tax_percent'] = round($ord['tax_percent'], 2);
		$ord['row_total'] 	= round($ord['row_total'], 2);

		$ip = round( ($ord['row_total'] * $ord['tax_percent']) ,2 );

		$aux_ac = Aux_AC3;
		$aux_it = Aux_IT3;
		if( $ord['tax_percent']>0 ){
			$aux_ac = Aux_AC;
			$aux_it = Aux_IT;
		}

		$a = null;
		$a['ClaveProdServ'] 					= $dat['sat_clave'];
		$a['NoIdentificacion'] 					= '';
		$a['Cantidad'] 							= (int)$ord['qty_ordered'];
		$a['ClaveUnidad'] 						= $dat['sat_clave_unidad'];
		$a['Unidad'] 							= $dat['sat_unidad'];
		$a['Descripcion'] 						= utf8_encode($dat['name'].' '.$dat['nombre_secundario']);
		$a['ValorUnitario'] 					= sprintf("%0.2f",$ord['price'] );
		$a['Importe'] 							= sprintf("%0.2f",$ord['row_total'] );
		$a['ImpuestosTraslado1Base'] 			= sprintf("%0.2f",$ord['row_total'] );
		$a['ImpuestosTraslado1Impuestos'] 		= ITRANSLADO;
		$a['ImpuestosTraslado1TipoFactor'] 		= ITIPOFACTOR;
		$a['ImpuestosTraslado1TasaOCuota'] 		= sprintf("%0.6f",$ord['tax_percent'] );
		$a['ImpuestosTraslado1Importe'] 		= sprintf("%0.2f",$ip );
		$a['InformacionAduaneraNumeroPedimento'] = '';
		$a['CuentaPredialNumero'] 				= '';
		$a['ConceptoTexto01'] 					= '';
		$a['ConceptoTexto02'] 					= '';
		$a['ConceptoTexto03'] 					= '';
		$a['ConceptoTexto04'] 					= '';
		$a['ConceptoTexto05'] 					= '';
		$a['ConceptoTexto06'] 					= '';
		$a['ConceptoTexto07'] 					= '';
		$a['ConceptoTexto08'] 					= '';
		$a['ConceptoTexto09'] 					= '';
		$a['ConceptoTexto10'] 					= '';
		$a['Aux_BU'] 							= Aux_BU;
		$a['Aux_PJ'] 							= Aux_PJ;
		$a['Aux_CT'] 							= Aux_CT;
		$a['Aux_AC'] 							= $aux_ac;
		$a['Aux_IT'] 							= $aux_it;

		$this->sys_concepto($a,$ord['sku']);
		return $a;
	}
	/* genera un concepto envio que engloba los impuestos de los productos que no tienen IVA */
	public function concepto_envio(){
		$this->log_add( 'called ==> concepto_envio() ==> ' );

		if( $this->productos == null ){ return false; }
		$b = $this->productos;

		$precio_envio = round( $this->suma_precio_envio($b) ,2 );

		if( $precio_envio == 0 ){ return null; }

		/* quitamos productos que si tienen impuestos */
		foreach ($b as $et => $r) {
			if( $r['data']['impuesto_iva']>0 ){
				unset($b[ $et ]);
			}
		}

		$ip = $precio_envio * ITASA;
		$ip = round( $ip,2 );

		$a=null;
		$a['ClaveProdServ'] 						= SCLAVE;
		$a['NoIdentificacion'] 						= '';
		$a['Cantidad'] 								= 1;
		$a['ClaveUnidad'] 							= SCLAVEUNIT;
		$a['Unidad'] 								= SUNIT;
		$a['Descripcion'] 							= SDESCIP;
		$a['ValorUnitario'] 						= sprintf("%0.2f",$precio_envio);
		$a['Importe'] 								= sprintf("%0.2f",$a['ValorUnitario']);
		$a['ImpuestosTraslado1Base'] 				= sprintf("%0.2f",$a['Importe']);
		$a['ImpuestosTraslado1Impuestos'] 			= ITRANSLADO;
		$a['ImpuestosTraslado1TipoFactor'] 			= ITIPOFACTOR;
		$a['ImpuestosTraslado1TasaOCuota'] 			= ITASA;
		$a['ImpuestosTraslado1Importe'] 			= sprintf("%0.2f",$ip);
		$a['InformacionAduaneraNumeroPedimento'] 	= '';
		$a['CuentaPredialNumero'] 					= '';
		$a['ConceptoTexto01'] 						= '';
		$a['ConceptoTexto02'] 						= '';
		$a['ConceptoTexto03'] 						= '';
		$a['ConceptoTexto04'] 						= '';
		$a['ConceptoTexto05'] 						= '';
		$a['ConceptoTexto06'] 						= '';
		$a['ConceptoTexto07'] 						= '';
		$a['ConceptoTexto08'] 						= '';
		$a['ConceptoTexto09'] 						= '';
		$a['ConceptoTexto10'] 						= '';
		$a['Aux_BU'] 								= Aux_BU;
		$a['Aux_PJ'] 								= Aux_PJ;
		$a['Aux_CT'] 								= Aux_CT;
		$a['Aux_AC'] 								= Aux_AC2;
		$a['Aux_IT'] 								= Aux_IT2;

		$this->sys_concepto($a);
		$this->factura['concepto'][] = $a;
		return $a;
	}
	/* obtiene la suma de todos los precios de envio ==> precio envio + seguro + precio empaque + comision + transaccion */
	public function suma_precio_envio($a=null){
		$this->log_add( 'called ==> suma_precio_envio() ==> ' );

		if( $this->pedido['shipping_method']!='tablerate_bestway' ){
			$this->log_add( 'metodo de envio '.$this->pedido['shipping_method'] );
			return 0;
		}

		$importe = $this->suma_importe_conceptos();

		$sys['peso_volumetrico'] = array( 'peso volumetrico', round( $this->suma_peso_volumetrico($a), 2 ) );
		$sys['emapque'] 		 = array( 'emapque', 		  round( $this->suma_precio_empaque($a), 2 ) );
		$sys['precio_envio'] 	 = array( 'precio envio', 	  round( $this->precio_peso_volumetrico( $sys['peso_volumetrico'][1] ), 2) );
		$sys['seguro'] 			 = array( 'seguro', 		  round( $this->suma_precio_seguro( $importe ), 2) );
		$sys['comision'] 		 = array( 'comision', 		  round( $this->comision(), 2) );
		$sys['transaccion'] 	 = array( 'transaccion', 	  round( $this->transaccion(), 2) );

		$envio = 0;
		foreach ($sys as $et => $r) {
			if( $et=='peso_volumetrico' ){ continue; }
			$envio += $r[1];
		}

		$sys['total']=array('Total precio envio',$envio);
		$this->precio_envio = $sys;

		$this->log_add("... sumatoria precio envio".print_table($sys) );
		return $envio;
	}
	/* suma los pesos volumetricos */
	public function suma_peso_volumetrico($a=null){
		$this->log_add( 'called ==> suma_peso_volumetrico() ==> ' );

		if($a==null){ return 0; }

		$n = 0;
		$pv = 0;
		$nn = 0;

		$sys = null;
		foreach ($a as $et => $r) {
			$w = 0;
			if( isset( $r['data']['weight'] ) ){
				$w = round($r['data']['weight'],2);
			}

			$pv += $w;
			$spv = $w * (int)$r['order']['qty_ordered'];
			$n += $spv;
			$nn += (int)$r['order']['qty_ordered'];

			$sys[] = array( 
				'producto' => $r['order']['sku'],
				'peso volumetrico' => $w,
				'cantidad' => (int)$r['order']['qty_ordered'],
				'suma peso volumetrico' => $spv
			);
		}

		$sys[] = array( 
			'producto' => 'total',
			'peso volumetrico' => $pv,
			'cantidad' => $nn,
			'suma peso volumetrico' => $n
		);

		$this->log_add( "\n\nprecio volumetrico".print_table($sys) );
		return $n;
	}
	/* suma los precios de empaque */
	public function suma_precio_empaque($a=null){
		$this->log_add( 'called ==> suma_precio_empaque() ==> ' );

		$msg = '... sumar precio empaque ==> ';

		$sempaque = $this->magento_vars( 'sumar_empaque' );
		$sempaque = strtolower( trim($sempaque) );

		if( $sempaque == '' ){ $empaque = 'no'; }
		if( strtolower( $sempaque )=='no' ){
			$this->log_add( $msg.'no' );
			return 0;
		}

		if($a==null) return 0;

		$empaque = 0;
		$se = 0;
		$sc = 0;
		$ss = 0;
		foreach ($a as $et => $r) {
			if( isset( $r['data']['configurable_precio'] ) ){
				if( isset( $r['data']['configurable_precio'] ) >0 ){
					continue;
				}
			}

			$e = ($r['data']['costo_empaque'] * ( (int)$r['order']['qty_ordered'] ) );
			$empaque += $e;

			$se += $r['data']['costo_empaque'];
			$sc += (int)$r['order']['qty_ordered'];

			$sys[] = array(
				'sku' 			=> $r['order']['sku'],
				'precio empaque' => $r['data']['costo_empaque'],
				'cantidad' 		=> (int)$r['order']['qty_ordered'],
				'subtotal'		 => $e,
			);
		}

		$sys[] = array(
			'sku' 			=> 'Total',
			'precio empaque' => $se,
			'cantidad' 		=> $sc,
			'subtotal' 		=> $empaque,
		);

		$this->log_add( "\n\n$msg si\n".print_table( $sys ) );
		return $empaque;
	}
	/* suma el precio seguro */
	public function suma_precio_seguro( $subtotal=0 ){
		$this->log_add( 'called ==> suma_precio_seguro() ==> ' );

		$seguro_aplicable = $this->magento_vars( 'seguro_aplicable' );
		$seguro = $this->magento_vars( 'articulo_seguro' );
		$tot = 0;

		if( $seguro==0 ){ return 0; }

		if( $subtotal >= $seguro_aplicable ){
			$tot = round( ($subtotal * round($seguro/100,2) ),2 );
		}

		$sys = array(
			'subtotal' => $subtotal,
			'seguro inicia' => $seguro_aplicable,
			'costo seguro' => $seguro.' %',
			'seguro total' => $tot,
		);

		$this->log_add( "precio seguro".print_table($sys) );
		return $tot;
	}
	/* suma todos los impuestos de los conceptos */
	public function suma_impuestos(){
		$this->log_add( '... called ==> suma_impuestos() ==> ' );

		$n = $this->suma_importe_conceptos_con_iva() * 0.16;
		$n = round( $n, 2 );

		return $n;
	}
	/* suma todos los importes de los conceptos */
	public function suma_importe_conceptos_con_iva(){
		$this->log_add( '... called ==> suma_importe_conceptos_con_iva() ==> ' );

		if( !isset( $this->factura['concepto'] ) ){ return 0; }

		$n = 0;
		foreach ($this->factura['concepto'] as $et => $r) {
			if( $r['ImpuestosTraslado1TasaOCuota']>0 ){
				$n += $r['Importe'];
			}
		}

		return $n;
	}
	/* suma todos los importes de los conceptos */
	public function suma_importe_conceptos(){
		$this->log_add( '... called ==> suma_importe_conceptos() ==> ' );

		if( !isset( $this->factura['concepto'] ) ){ return 0; }

		$n = 0;
		$a = null;
		foreach ($this->factura['concepto'] as $et => $r) {
			$n += $r['Importe'];
			$a[] = array(
				'ValorUnitario' => $r['ValorUnitario'],
				'Cantidad' 		=> $r['Cantidad'],
				'Importe' 		=> $r['Importe'],
				'IVA' 			=> $r['ImpuestosTraslado1TasaOCuota'],
				'IVA_total' 	=> $r['ImpuestosTraslado1Importe'],
				'Descripcion' 	=> $r['Descripcion'],
			);
		}

		$this->log_add( "suma importe conceptos".print_table($a) );
		return $n;
	}
	/* obtiene la comision por el subtotal de cada producto */
	public function comision(){
		$this->log_add( 'called ==> comision() ==> ' );

		$comision = $this->magento_vars( 'articulo_comision' );
		if($comision=='0'){ return 0; }

		$comision = $comision/100;

		$n = 0.00;
		$sys = null;
		$tp = 0;
		$cn = 0;
		$sb = 0;
		foreach ($this->productos as $et => $r) {
			$tp += $r['order']['price'];
			$cn += $r['order']['qty_ordered'];
			$sb += $r['order']['row_total'];

			$nn = $r['order']['row_total'] * $comision;
			$n += $nn;
			$sys[] = array(
				'producto'	=> $r['order']['sku'],
				'precio'	=> round( $r['order']['price'], 2 ),
				'cantidad'	=> (int)$r['order']['qty_ordered'],
				'subtotal'	=> round( $r['order']['row_total'], 2 ),
				'comision'	=> round( $nn, 2)
			);
		}

		$sys[] = array(
			'producto'	=> 'total',
			'precio'	=> round( $tp,2 ),
			'cantidad'	=> (int)$cn,
			'subtotal'	=> round( $sb,2 ),
			'comision'	=> round( $n,2 )
		);

		$this->log_add( "comision por productos".print_table($sys) );
		return $n;
	}
	/* obtiene la comision por transaccion */
	public function transaccion(){
		$this->log_add( 'called ==> transaccion() ==> ' );

		$transaccion = $this->magento_vars( 'comision_transaccion' );
		if($transaccion==null){ return 0.00; }

		$this->log_add("... comision por transaccion ==> ".$transaccion);
		return $transaccion;
	}
	/* obtiene el precio por peso volumetrico */
	public function precio_peso_volumetrico($peso_volumetrico=0){
		$this->log_add( 'called ==> precio_peso_volumetrico() ==> ' );
		
		$a=query( $this->sql_peso_volumetrico( $peso_volumetrico ) );
		if($a==null) return 0;

		$this->log_add( "precio peso volumetrico".print_table($a) );
		return $a[0]['price'];
	}
	public function sys_concepto($a=null,$sku=0){
		if($a==null){ return false; }
		if( $sku=='0' ){ $sku='envios'; }

		$b=null;
		$b['sku'] = $sku;
		$b['cantidad'] = $a['Cantidad'];
		$b['unitario'] = $a['ValorUnitario'];
		$b['importe'] = $a['Importe'];
		$b['impuesto'] = $a['ImpuestosTraslado1Importe'];

		$this->sys_data['conceptos'][]=$b;

		return true;
	}
	public function valida_totales(){
		$this->log_add( 'called ==> valida_totales() ==> ' );

		/* impuestos se calculan sumando los importes con iva y multiplicandolos por el iva */

		$tot1 = round( $this->pedido['grand_total'], 2 );
		$tot2 = round( $this->factura['impuestos']['ImpuestosTraslado1Importe'] + $this->pedido['subtotal'], 2 );

		$a = array(
			array( 'orden de compra', $this->pedido['increment_id'] ),
			array( 'subtotal', $this->pedido['subtotal'] ),
			array( 'impuestos', $this->factura['impuestos']['ImpuestosTraslado1Importe'] ),
			array( 'total', $tot1 ),
		);

		$this->log_add( print_table($a) );

		$dif = round($tot1 - $tot2, 2 ) * 100;
		if($dif==0){
			return 0;
		}

		$this->log_add("diferencia impuestos ==> ".$dif);

		$this->agrega_todos_conceptos_impuestos( $dif );
		$this->in1_impuestos();

		return $dif;
	}
	/* agrega a los conceptos en la parte de iva 1 centavo para cuadrar totales */
	public function agrega_todos_conceptos_impuestos( $dif=0 ){
		$this->log_add( 'called ==> agrega_todos_conceptos_impuestos() ==> ' );

		if( $dif == 0 ){ return true; }
		$dif = (int)$dif;

		$au = $dif;
		$salir = false;

		if( $dif>0 ){
			for(;;){
				foreach ($this->factura['concepto'] as $et => $r) {
					if( $r['ImpuestosTraslado1TasaOCuota']>0 ){
						$this->factura['concepto'][$et]['ImpuestosTraslado1Importe'] += 0.01;
						$au--;
					}

					if($au==0){
						$salir = true;
						break;
					}
				}
				if($salir){ break; }
			}			
		}

		if( $dif<0 ){
			for(;;){
				foreach ($this->factura['concepto'] as $et => $r) {
					if( $r['ImpuestosTraslado1TasaOCuota']>0 ){
						$this->factura['concepto'][$et]['ImpuestosTraslado1Importe'] -= 0.01;
						$au++;
					}

					if($au==0){
						$salir = true;
						break;
					}
				}
				if($salir){ break; }
			}			
		}

		return $dif;
	}
	public function in1_fecha(){
		$t=time()-1000;
		$s=date('Y-m-d',$t).'T'.date('G:i:s',$t);
		return $s;
	}



	public function in1_extras(){
		$this->log_add( 'called ==> in1_extras() ==> ' );

		if( PRODUCCION!=1 ){
			$this->user['company'] = utf8_encode( $this->user['company'] );
		}

		$a['AfterCreateStatus'] = ACSTATUS;
		$a['ExtrasTexto01'] 	= $this->user['company'];
		$a['Description'] 		= DESCIP;
		$a['YourReference'] 	= $this->pedido['increment_id'];
		$a['ExtrasNotas'] 		= '';

		$this->factura['extras'] = $a;

		$this->log_add( print_r( $this->factura['extras'], true ) );
		return true;
	}
	public function in1_emisor(){
		$this->log_add( 'called ==> in1_emisor() ==> ' );

		$a['Rfc'] 			= ERFC;
		$a['Nombre'] 		= ENOM;
		$a['RegimenFiscal'] = ERFI;
		$a['Calle'] 		= ECAL;
		$a['NoExterior'] 	= '';
		$a['NoInterior'] 	= '';
		$a['Colonia'] 		= ECOL;
		$a['Localidad'] 	= '';
		$a['Referencia'] 	= '';
		$a['Municipio'] 	= EMUN;
		$a['Estado'] 		= EEST;
		$a['Pais'] 			= EPAI;
		$a['CodigoPostal'] 	= ECPT;

		$this->factura['emisor'] = $a;

		$this->log_add( print_r( $this->factura['emisor'], true ) );
		return true;
	}
	public function in1_receptor(){
		$this->log_add( 'called ==> in1_receptor() ==> ' );

		$a['Rfc'] 				= $this->user['rfc'];
		$a['Nombre'] 			= $this->user['company'];
		$a['ResidenciaFiscal'] 	= '';
		$a['NumRegIdTrib'] 		= '';
		$a['UsoCFDI'] 			= $this->uso_cfdi();

		$this->factura['receptor'] = $a;

		$this->log_add( print_r( $this->factura['receptor'], true ) );
		return true;
	}
	public function in1_concepto(){
		$this->log_add( 'called ==> in1_concepto() ==> ' );

		/* llena los conceptos */
		$this->llena_concepto();
		/* genera el concepto envio */
		$this->concepto_envio();

		return true;
	}
	public function in1_impuestos(){
		$this->log_add( 'called ==> in1_impuestos() ==> ' );

		$tax = $this->suma_impuestos();

		$a['TotalImpuestosRetenidos'] 		= '';
		$a['TotalImpuestosTrasladados'] 	= sprintf("%0.2f",$tax);
		$a['ImpuestosTraslado1Impuesto'] 	= ITRANSLADO;
		$a['ImpuestosTraslado1TipoFactor'] 	= ITIPOFACTOR;
		$a['ImpuestosTraslado1TasaOCuota'] 	= ITASA;
		$a['ImpuestosTraslado1Importe'] 	= sprintf("%0.2f",$tax);
		
		$this->factura['impuestos'] = $a;

		$this->log_add( print_r($this->factura['impuestos'],true) );
		return true;
	}
	public function in1_comprobante(){
		$this->log_add( 'called ==> in1_comprobante() ==> ' );

		$forma_pago='03';
		if( isset( $this->pedido['forma_pago'] ) ){
			$forma_pago = $this->pedido['forma_pago'];
		}

		$this->pedido['subtotal']    = round( $this->suma_importe_conceptos(), 2 );

		$total = $this->factura['impuestos']['ImpuestosTraslado1Importe'] + $this->pedido['subtotal'];
		$total = round( $total,2 );
		$total = round( $this->pedido['grand_total'], 2 );

		$this->valida_totales();

		$a['idUnico'] 			= 'XXXXX-00000';
		$a['Version'] 			= FVER;
		$a['Serie'] 			= 'XXXXX';
		$a['Folio'] 			= '00000';
		$a['Fecha'] 			= $this->in1_fecha();
		$a['FormaPago'] 		= $forma_pago;
		$a['CondicionesDePago'] = '';
		$a['Subtotal'] 			= sprintf("%0.2f", $this->pedido['subtotal'] );
		$a['Descuento'] 		= '';
		$a['Moneda'] 			= MEX;
		$a['TipoCambio'] 		= sprintf("%0.2f",TCAMBIO);
		$a['Total'] 			= sprintf("%0.2f", $total );
		$a['TipoDeComprobante'] = COMPROBANTE;
		$a['MetodoPago'] 		= MPAGO;
		$a['LugarExpedicion'] 	= LEXPEDITION;
		$a['Confirmacion'] 		= '';
		$a['Correo'] 			= LEMAIL;
		$a['FormatoCfdi'] 		= '';
		$a['Status'] 			= '';

		$this->factura['comprobante'] = $a;

		$this->log_add( '... ... subtotal ==> '.$a['Subtotal'] );
		$this->log_add( '... ... impuestos ==> '.$this->factura['impuestos']['ImpuestosTraslado1Importe'] );
		$this->log_add( '... ... subtotal + impuestos ==> '.($a['Subtotal']+$this->factura['impuestos']['ImpuestosTraslado1Importe']) );
		$this->log_add( '... ... total ==> '.$a['Total'] );
		return true;
	}
	/* genera el formato in1 para solicitar factura */
	public function in1_formato(){
		$this->log_add( 'called ==> in1_formato() ==> ' );

		/* obtiene el siguiente folio */
		$folio = $this->folio();

		$this->factura['comprobante']['idUnico'] = SERIE.'-'.$folio;
		$this->factura['comprobante']['Serie'] = SERIE;
		$this->factura['comprobante']['Folio'] = $folio;

		$this->factura['status'] = 'editando';
		$this->factura['folio'] = $folio;
		$this->factura['factura'] = SERIE.'-'.$folio;

		$this->log_add( $this->factura['comprobante']['idUnico'] );

		$this->save_factura();

		$s = '';
		$s = $s.$this->factura_array_to_string( $this->factura['comprobante'],	'Comprobante', 0);
		$s = $s.$this->factura_array_to_string( $this->factura['extras'],		'Extras');
		$s = $s.$this->factura_array_to_string( $this->factura['emisor'],		'Emisor');
		$s = $s.$this->factura_array_to_string( $this->factura['receptor'],		'Receptor');

		$i = 1;
		foreach ($this->factura['concepto'] as $et => $r) {
			$s = $s.$this->factura_array_to_string( $r,'Concepto'.$i);
			$i++;
		}

		$s = $s.$this->factura_array_to_string( $this->factura['impuestos'],	'Impuestos');

		$s = utf8_decode( $s );

		return $s;
	}
	/* guarda los datos de la factura */
	public function save_factura(){
		$this->log_add( 'called ==> save_factura() ==> ' );

		/* codificando textos */

		$b = $this->factura;

		$b['extras']['ExtrasTexto01'] 	= utf8_decode( $b['extras']['ExtrasTexto01'] );
		$b['extras']['Description'] 	= utf8_decode( $b['extras']['Description'] );
		$b['emisor']['Estado'] 			= utf8_decode( $b['emisor']['Estado'] );
		$b['emisor']['Pais'] 			= utf8_decode( $b['emisor']['Pais'] );
		$b['receptor']['Nombre'] 		= utf8_decode( $b['receptor']['Nombre'] );

		foreach ($b['concepto'] as $et => $r) {
			$b['concepto'][$et]['Descripcion'] = utf8_decode( $b['concepto'][$et]['Descripcion'] );
		}

		/* convirtiendo arreglos en consultas sql */
		$a[] = $this->array_data_to_insert_sql_in1( $b['comprobante'], 'comprobante' );
		$a[] = $this->array_data_to_insert_sql_in1( $b['extras'], 'extras' );
		$a[] = $this->array_data_to_insert_sql_in1( $b['emisor'], 'emisor' );
		$a[] = $this->array_data_to_insert_sql_in1( $b['receptor'], 'receptor' );

		$i=1;
		foreach ($b['concepto'] as $et => $r) {
			$a[] = $this->array_data_to_insert_sql_in1( $r, 'concepto-'.$i );
			$i++;
		}

		$a[] = $this->array_data_to_insert_sql_in1( $b['impuestos'], 'impuestos' );

		/* quita datos antiguos y/o corruptos */
		$this->factura_delete_data( $this->factura['folio_id'] );

		$n = 0;
		foreach ($a as $et => $r) { $n +=count($r); }
		$this->log_add( 'ingresando '.$n.' registros' );

		/* procesando consultas sql */
		foreach ($a as $et => $r) {
			foreach ($r as $etr => $rr) {
				query( $rr );
			}
		}

		query( $this->sql_factura_update( 'status', 'generado', $b['folio_id'] ) );
		query( $this->sql_factura_update( 'last_update', $this->factura_date(), $b['folio_id'] ) );

		$this->log_add('factura guardada');
		return true;
	}
	private function factura_delete_data( $id_factura=0 ){
		if($id_factura==0){ return false; }

		$s = $this->sql_factura_select_data($id_factura);
		$a = query($s);
		if($a==null){ return true; }

		$this->log_add('liberando buffer '.count($a));

		foreach ($a as $et => $r) {
			$ss = $this->sql_delete_data_factura_id( $r['id'] );
			query($ss);
		}

		$a = query($s);
		if($a==null){ return true; }

		return false;
	}
	/* transforma el contenido de un arreglo en consulta sql insert */
	public function array_data_to_insert_sql_in1($a=null,$segment=''){
		$this->log_add( 'called ==> array_data_to_insert_sql_in1() ==> ' );

		if($a==null){ return null; }
		if($segment==''){ return null; }

		$b = null;
		foreach ($a as $et => $r) {
			$b[] = "INSERT INTO `facturacion_attribs` (`id`, `id_factura`, `attrib`, `type`, `data`, `segment`) VALUES 
				('', '".$this->factura['folio_id']."', '$et', 'text', '$r', '$segment');";
		}

		return $b;
	}


	/* funciones auxiliares */
	public function ftime(){
		return date('Y-m-d G:i:s', time() );		
	}
	public function log_add($s=''){
		if( $s=='' ){ return false; }

		$s = "\n:::: ".$this->ftime()." :::: $s\n";

		$this->log_save($s);

		return true;
	}
	public function log_save($s=''){
		if($s==''){ return false; }

		$file = "log/factura.log";

		if( $f = fopen($file, 'a+') ){
            fwrite($f, $s);
            fclose($f);
            return true;
        }

		return false;
	}
	/* convierte un arreglo a cadena para archivos in1 */
	public function factura_array_to_string($a=null,$tit='',$line_cab=1){
		if($a==null){ return ''; }
		if($tit==''){ return ''; }

		$s = '';
		foreach ($a as $et => $r) {
			$s = $s."\r\n".$et."=$r";
		}

		$cab = "\r\n\r\n";
		if( $line_cab == 0 ){ $cab = ''; }
		$s = $cab."[".$tit."]".$s;

		return $s;
	}
	public function buffer_null(){

		$this->pedido = null;
		$this->magento_vars = null;
		$this->productos = null;
		$this->user = null;
		$this->precio_envio = null;
		$this->sys_data = null;
		$this->factura['status'] = '';
		$this->factura['folio'] = 0;
		$this->factura['factura'] = '';
		$this->factura['comprobante'] = '';
		$this->factura['extras'] = '';
		$this->factura['emisor'] = '';
		$this->factura['receptor'] = '';
		$this->factura['concepto'] = null;
		$this->factura['impuestos'] = '';

		return true;
	}

	/* consultas MySQL */
	private function sql_list_orden( $status='' ){
		if($status==''){ return ''; }

		$ss = "(
			fi.status != 'generado' or 
			fi.status is null 
			) and";
		if( $this->pruebas ){
			$ss = "";
		}

		$s="SELECT

			sfo.entity_id,
		    sfo.status,
		    sfo.customer_id,
		    sfo.increment_id,

		    sfo.grand_total,

            fi.status as fstatus,
            sfoa.is_billing
		    
			from sales_flat_order as sfo
			left join facturacion_invoices as fi on fi.order_id = sfo.increment_id
            inner join sales_flat_order_address as sfoa on sfoa.parent_id = sfo.entity_id

			where

			sfo.status='pagado' and
			$ss
			sfoa.address_type like 'billing' and
			sfoa.is_billing = 1
            ;";

        return $this->sql_no_line($s);
	}
	private function sql_variables_personalizadas(){
		$s="SELECT
			cv.code,
			cv.name,
			cvv.plain_value
			FROM core_variable as cv
			inner join core_variable_value as cvv on cvv.variable_id=cv.variable_id
			;";

		return $this->sql_no_line($s);
	}
	private function sql_orden($orden=''){
		if($orden==''){ return ''; }

		$s="SELECT

			sfo.entity_id,
		    sfo.state,
		    sfo.status,
		    sfo.customer_id,
		    sfo.increment_id,

			sfo.total_item_count,
			sfo.total_qty_ordered,
			sfo.subtotal,
			sfo.shipping_amount as envio,
			sfo.shipping_tax_amount as envio_impuesto,
			sfo.tax_amount,
		    sfo.grand_total,

			sfo.billing_address_id,
		    sfo.customer_group_id,
		    sfo.weight as peso_volumetrico,
		    sfo.order_currency_code,
		    sfo.customer_email,
		    sfo.customer_firstname,
		    sfo.customer_lastname,
		    sfo.customer_middlename,
		    sfo.shipping_method,
		    sfo.created_at,
		    sfo.updated_at,
            
            sfop.method as billing_method,
            sfoa.fax as uso_cfdi,
            sfoa.is_billing
		    
			from sales_flat_order as sfo
            inner join sales_flat_order_payment as sfop on sfop.parent_id=sfo.entity_id
            inner join sales_flat_order_address as sfoa on sfoa.parent_id = sfo.entity_id

			where 
			sfo.status='pagado' and
			sfoa.address_type like 'billing' and
			sfo.increment_id= '$orden';
			";

		return $this->sql_no_line($s);
	}
	private function sql_factura_search($orden=0,$serie=''){
		if($orden==0){ return ''; }
		if($serie==''){ return ''; }

		$s = "SELECT fi.* 
			from facturacion_invoices as fi 
			where 
			fi.order_id like '$orden' and 
			fi.serie like '$serie' and 
			fi.status like 'generado';";

		return $this->sql_no_line($s);
	}
	private function sql_orden_prods( $orden_id=0 ){
		if( $orden_id==0 ){ return ''; }

		/* numero de items del pedido */
		$s="SELECT 

			item_id,
			order_id,
			product_id,
			sku,
			name,
			price,
			qty_ordered,
			row_total,
			tax_percent,
			quote_item_id,
			parent_item_id,
			created_at,
			updated_at

			from sales_flat_order_item where order_id=$orden_id;";

		return $this->sql_no_line($s);
	}
	private function sql_product_data($product_id=0){
		if( $product_id==0 ){ return ''; }

		$a = array(
			'catalog_product_entity_datetime',
			'catalog_product_entity_datetime',
			'catalog_product_entity_decimal',
			'catalog_product_entity_gallery',
			'catalog_product_entity_int',
			'catalog_product_entity_media_gallery',
			'catalog_product_entity_text',
			'catalog_product_entity_url_key',
			'catalog_product_entity_varchar',
		);

		$s = '';
		foreach ($a as $et => $r) {
			if( $s!='' ){ $s = $s."\n union \n"; }
			$s = $s."SELECT
				ea.attribute_code,cpe.value 
				from $r  as cpe
				inner join eav_attribute as ea on ea.attribute_id=cpe.attribute_id
				where cpe.entity_id=$product_id ";
		}

		return $this->sql_no_line($s);
	}
	private function sql_order_user( $user_id=0 ){
		if($user_id==0){ return ''; }
		$s="SELECT

			cae.entity_id,
			cae.parent_id,
			cae.is_active,
			cae.is_billing,

			caev.value,

			ea.attribute_code

			from customer_address_entity as cae
			inner join customer_address_entity_varchar as caev on caev.entity_id=cae.entity_id
			inner join eav_attribute as ea on ea.attribute_id=caev.attribute_id

			where 
			cae.parent_id=$user_id and 
			cae.is_billing=1 and 
			(
			caev.attribute_id=24 or
			caev.attribute_id=199 
			)
			;";

		return $this->sql_no_line($s);
	}
	private function sql_peso_volumetrico( $peso=0 ){
		$s="SELECT * FROM shipping_tablerate 
			where
			condition_value<=$peso
			order by condition_value DESC
			limit 0,1 ;";

		return $this->sql_no_line($s);
	}
	private function sql_folio_search_order( $order=0, $serie='' ){
		if($order==0){ return ''; }
		if($serie==''){ return ''; }

		$s="SELECT * from facturacion_invoices where order_id = $order and serie like '$serie';";

		return $this->sql_no_line($s);
	}
	private function sql_folio_last($serie = ''){
		if( $serie=='' ){ return ''; }
		$s="SELECT * from facturacion_invoices where serie like '$serie' order by folio_id DESC limit 0,1;";

		return $this->sql_no_line($s);
	}
	private function sql_no_line($s=''){
		$s = trim($s);
		if($s==''){ return ''; }

		$s = str_replace("\r", "", $s);
		$s = str_replace("\t", " ", $s);
		$s = str_replace("\n", " ", $s);

		for($i=1;$i<=5;$i++){
			$s = str_replace("  ", " ", $s);
		}

		return $s;
	}
	private function sql_factura_update( $campo='', $valor=null, $factura_id=0 ){
		if($factura_id==0){ return ''; }
		if($campo==''){ return ''; }

		$s = "UPDATE `facturacion_invoices` SET `$campo` = '$valor' WHERE `id` = $factura_id;";

		return $this->sql_no_line($s);
	}
	private function sql_factura_select_data($id_factura=0){
		if($id_factura==0){ return null; }

		$s = "SELECT * from facturacion_attribs where id_factura=$id_factura;";
		return $s;
	}
	private function sql_delete_data_factura_id($id_factura=0){

		$ss = "DELETE from facturacion_attribs where id=$id_factura;";
		return $ss;
	}
}
?>