<?php
/*
 * funciones para generar el reporte de ventas
 */

class report{

	public $data = null;
	public $marca = null;

	public function report_ventas_update(){
		$this->select_all_sales();
		$this->report_ventas_unique();
		$modif = $this->detect_changes();
	
		//echo print_table($modif);

		$this->table_report_ventas_update( $modif );

		return true;
	}

	/* selecciona todas las ventas hechas en el sistema */
	public function select_all_sales(){

		$s = "SELECT 
			sfo.entity_id,sfo.state,sfo.status,sfo.customer_id,sfo.billing_address_id,sfo.shipping_address_id,
			sfo.increment_id,
			sfo.customer_email,sfo.customer_firstname,sfo.customer_lastname,
			sfo.order_currency_code,sfo.subtotal,sfo.subtotal_incl_tax,sfo.shipping_amount,sfo.shipping_tax_amount,sfo.shipping_method,sfo.discount_amount,sfo.tax_amount,sfo.grand_total,sfo.total_item_count,sfo.total_qty_ordered,
			sfo.created_at,sfo.updated_at,
			sfo.remote_ip,sfo.x_forwarded_for,
			sfo.weight as peso_volumetrico
			from sales_flat_order as sfo
			order by entity_id DESC";
		$a = query( $s );
		if($a==null){ return 0; }

		$b = null;
		foreach ($a as $et => $r) {
			$b[ $r['increment_id'] ] = $r;
		}

		$this->data['sales'] = $b;
		return count( $b );
	}
	/* regresa la venta solicitada */
	public function select_sale($so=''){

		$s = "SELECT 
			sfo.entity_id,sfo.state,sfo.status,sfo.customer_id,sfo.billing_address_id,sfo.shipping_address_id,
			sfo.increment_id,
			sfo.customer_email,sfo.customer_firstname,sfo.customer_lastname,
			sfo.order_currency_code,sfo.subtotal,sfo.subtotal_incl_tax,sfo.shipping_amount,sfo.shipping_tax_amount,sfo.shipping_method,sfo.discount_amount,sfo.tax_amount,sfo.grand_total,sfo.total_item_count,sfo.total_qty_ordered,
			sfo.created_at,sfo.updated_at,
			sfo.remote_ip,sfo.x_forwarded_for,
			sfo.weight as peso_volumetrico
			from sales_flat_order as sfo
			where  increment_id like '$so'
			";
		$a = query( $s );
		if($a==null){ return null; }

		return $a[0];
	}
	/* selecciona los datos del reporte con registros unicos para el sales order */
	public function report_ventas_unique(){
		$this->select_report();
		if( $this->data['report']==null ){ return null; }

		$b = null;
		foreach ($this->data['report'] as $et => $r) {
			$b[ $r['sales_order'] ] = $r;
		}

		$this->data['report'] = $b;

		return null;
	}
	/* selecciona los datos del reporte */
	public function select_report(){
		$this->data['report'] = null;
		$s = "SELECT * from report_ventas";
		$a = query($s);
		if($a==null){ return 0; }

		/*
			ventas_id,
			nombre,
			apellidos,
			sales_order,
			sku,
			cantidad,
			total_piezas,

			pedido_total,

			pedido_subtotal,
			pedido_subtotal_iva,
			pedido_envio,
			op,
			op_trans,
			pedido_envio_iva,
			pedido_envio_total,

			pedido_iva as pedido_iva_total,
			pedido_descuento,

			pedido_envio_margen,
			margen_total,
			margen_restante,

			pedido_status,
			pedido_envio_text,

			pedido_fecha,
			pedido_pago as pedido_fecha_pago,

			article,
			marca,
			peso,
			ancho,
			profundo,
			alto,
			m3,

			precio_sin_imp,
			precio_ieps,
			precio_iva,
			precio_venta,

			costo_unit_sin_imp,
			costo_unit_ieps,
			costo_unit_con_ieps,
			costo_unit_iva,
			costo_unit,

			precio_unit_sin_imp,
			precio_unit_ieps,
			precio_unit_mas_ieps,
			precio_unit_iva,
			precio_acumulado,
			margen_unit,

			descuento as descuento_producto,

			telefono,
			telefono_ext,
			email,
			calle,
			nu,
			nu_int,
			cp,
			colonia,
			delegacion_municipio,
			estado,
			referencia,
			programa

		*/

		$this->data['report'] = $a;
		return count($a);
	}
	/* detecta cambios en el reporte */
	public function detect_changes(){
		//$this->data['sales']
		//$this->data['report']

		/* si no hay ventas regresa null */
		if( $this->data['sales']==null ){ return null; }
		/* si el reporte esta vacio regresa datos de ventas */
		if( $this->data['report']==null ){ return $this->data['sales']; }

		$a = array(
			'entity_id',
			'status',
			'increment_id',
			'customer_email',
			'subtotal',
			'grand_total',
			'total_item_count',
			'total_qty_ordered',
			'updated_at'
		);
		$b = array(
			'ventas_id',
			'pedido_status',
			'sales_order',
			'email',
			'pedido_subtotal',
			'pedido_total',
			'total_piezas',
			'cantidad',
			'pedido_pago',
		);

		$an = count($a);
		$bn = count($b);
		$a = $this->array_select( $this->data['sales'], $a, 'increment_id' );
		$b = $this->array_select( $this->data['report'], $b, 'sales_order' );

		$err = "\n error procesando datos";
		if( $an>0 && $a==null ){ echo $err; return null; }
		if( $bn>0 && $b==null ){ echo $err; return null; }

		/* sin acciones para generar el reporte */
		foreach ($a as $et => $r) { $a[ $et ]['_accion_'] = ''; }
		/* si no existe el sales-order en el reporte, agregarlo */
		foreach ($a as $et => $r) {
			if( !isset( $b[ $et ] ) ){
				$a[ $et ]['_accion_'] = 'agregar';
			}else{
				/* si los datos son diferentes reeemplazar */
					$ch = 0;
					if( $r['status'] != $b[ $et ]['pedido_status'] ){ $ch++; }
					if( $r['updated_at'] != $b[ $et ]['pedido_pago'] ){ $ch++; }

					if($ch){
						$a[ $et ]['_accion_'] = 'modificado';
					}
			}
		}
		/*
		foreach ($a as $et => $r) {
			print_r($r);
			print_r( $b[ $et ] );
			break;
		}*/

		foreach ($a as $et => $r) {
			if( $r['_accion_'] == '' ){ unset( $a[ $et ] ); }
		}

		return $a;
	}

	public function array_select($a=null,$b=null,$indx=''){
		if($a==null){ return null; }
		if($b==null){ return null; }

		$c = null;
		foreach ($a as $et => $r) {
			$d = null;
			foreach ($b as $etr => $rr) {
				if( isset( $r[ $rr ] ) ){
					$d[ $rr ] = $r[ $rr ];
				}
			}

			if( $indx!='' ){
				$c[ $r[ $indx ] ] = $d;
			}else{
				$c[] = $d;
			}
		}
		return $c;
	}

	public function marc_modif( $so ){
		if($so==''){ return false; }

		$s = "SELECT * from report_ventas where sales_order like '$so'";
		$a = query($s);
		if( $a==null ){ return false; }

		foreach ($a as $et => $r) {
			$s = "UPDATE report_ventas set pedido_pago='' where ventas_id = ".$r['ventas_id'];
			query($s);
		}

		return true;
	}

	public function remove_report_data($so=''){
		if($so==''){ return false; }

		$s = "SELECT * from report_ventas where sales_order like '$so'";
		$a = query( $s );
		if($a==null){ return false; }

		echo "\n remove ==> ".$so;

		foreach ($a as $et => $r) {
			$s = "DELETE FROM `report_ventas` WHERE (`ventas_id` = '".$r['ventas_id']."')";
			query($s);
		}

		$s = "SELECT * from report_ventas where sales_order like '$so'";
		$a = query( $s );
		if($a==null){ return true; }

		return false;
	}

	public function table_report_ventas_update($a=null){
		if($a==null){ return 0; }

		/* quitando registros apocrifos */
			foreach ($a as $et => $r) {
				$this->remove_report_data( $r['increment_id'] );
			}

		/* obteniendo nuevos datos */
			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['increment_id'] ] = $this->select_sale( $r['increment_id'] );
			}

			$this->data = null;
			$this->data['sales'] = $b;

			$this->data = $this->struct_report_ventas();
			$this->agrega_codigo_vendedor();
			$this->save_sql();

		return true;
	}

	public function report_ventas(){

		$n = $this->select_all_sales();
		tt('numero de ventas ==> '.$n);

		$this->data = $this->struct_report_ventas();
		$this->agrega_codigo_vendedor();

		$this->save_sql();

		return true;
	}

	public function agrega_codigo_vendedor(){
		if( $this->data == null ){ return null; }

		/* obteniendo datos de codigo vendedor */
			include('codevendorcheckout.php');
			$cdv = new codeVendorCheckout();
			$cdv->cv_list_sales_order();
			$d = $cdv->data;

		/* recorriendo todos los items de ventas */
			if($d==null){ return true; }

			foreach ($d as $et => $r) {
				foreach ($this->data as $etr => $rr) {
					if( $rr['idcanasta'] == $et ){
						$this->data[ $etr ]['program'] = $this->data[ $etr ]['program'].'code_vendor=['.$r['code_vendor'].']';
					}
				}
			}

		return true;
	}

	public function save_sql(){
		if( $this->data == null ){ return false; }

		//$s = "TRUNCATE report_ventas;";
		//query($s);

		$s = "DESCRIBE report_ventas";
		$campos = query($s);

		$err = 0;
		$so = '';
		foreach ($this->data as $et => $r) {
			if( $so !=  $r['idcanasta'] ){
				echo "\n INSERT ==> ".$r['idcanasta'];
				$so =  $r['idcanasta'];
			}

			$s = "INSERT into report_ventas values";			
				$s = $s."(null, '".$r['nombre']."',";
				$s = $s."'".$r['apellidos']."',";
				$s = $s."'".$r['idcanasta']."',";
				$s = $s."'".$r['sku']."',";
				$s = $s."'".$r['article']."',";
				$s = $s.$r['cantidad'].",";
				$s = $s.$r['total_piezas'].",";
				$s = $s."'".$r['marca']."',";
				$s = $s.$r['precio_sin_imp'].",";
				$s = $s.$r['precio_ieps'].",";
				$s = $s.$r['precio_iva'].",";
				$s = $s.$r['precio_venta'].",";
				$s = $s.$r['peso'].",";
				$s = $s.$r['ancho'].",";
				$s = $s.$r['profundo'].",";
				$s = $s.$r['alto'].",";
				$s = $s.$r['m3'].",";
				$s = $s.$r['costo_unit_sin_imp'].",";
				$s = $s.$r['costo_unit_ieps'].",";
				$s = $s.$r['costo_unit_con_ieps'].",";
				$s = $s.$r['costo_unit_iva'].",";
				$s = $s.$r['costo_unit'].",";
				$s = $s.$r['precio_unit_sin_imp'].",";
				$s = $s.$r['precio_unit_ieps'].",";
				$s = $s.$r['precio_unit_mas_ieps'].",";
				$s = $s.$r['precio_unit_iva'].",";
				$s = $s.$r['precio_acumulado'].",";
				$s = $s.$r['margen_unit'].",";
				$s = $s.$r['pedido_subtotal'].",";
				$s = $s.$r['pedido_subtotal_iva'].",";
				$s = $s.$r['pedido_envio'].",";
				$s = $s.$r['margen_total'].",";
				$s = $s.$r['pedido_envio_margen'].",";
				$s = $s.$r['op'].",";
				$s = $s.$r['op_trans'].",";
				$s = $s.$r['pedido_envio_iva'].",";
				$s = $s.$r['pedido_envio_total'].",";
				$s = $s.$r['pedido_descuento'].",";
				$s = $s.$r['pedido_iva'].",";
				$s = $s.$r['descuento'].",";
				$s = $s.$r['pedido_total'].",";
				$s = $s.$r['margen_restante'].",";
				$s = $s."'".$r['pedido_status']."',";
				$s = $s."'".$r['pedido_fecha']."',";
				$s = $s."'".$r['pedido_pago']."',";
				$s = $s."'".$r['pedido_envio_text']."',";
				$s = $s."'".$r['telefono']."',";
				$s = $s."'".$r['telefono_ext']."',";
				$s = $s."'".$r['email']."',";
				$s = $s."'".$r['calle']."',";
				$s = $s."'".$r['nu']."',";
				$s = $s."'".$r['nu_int']."',";
				$s = $s."'".$r['cp']."',";
				$s = $s."'".$r['colonia']."',";
				$s = $s."'".$r['delegacion_municipio']."',";
				$s = $s."'".$r['estado']."',";
				$s = $s."'".$r['referencia']."',";
				$s = $s."'".$r['program']."' );";

			$in = query($s);
			if( !$in ){
				tt("error: registro no insertado");
				tt($s);
				$err++;
			}
		}
		if($err){
			echo print_table($campos);
		}

		return true;
	}

	public function select_all_products_of_sale( $sales_id = 0, $dat = null ){
		if( $sales_id==0 ){ return null; }

		$omited = ' -- omitiendo producto ==> ';

		/* listando todos los productos de la venta */
			$s = "SELECT * from sales_flat_order_item where order_id = $sales_id";
			$a = query( $s );

			if( $a==null ){ echo "\nsin datos de productos\n"; return null; }

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['item_id'] ] = $r;
			}
			$a = $b; $b = null;

		/* determinando KITS */
			foreach ($a as $et => $r) { $a[$et]['kit'] = 0; }
			$kk = null;
			foreach ($a as $et => $r) {
				$fsku = explode('KIT-', $r['sku']);
				if( count($fsku)>1 ){
					$a[$et]['kit'] = 1;
					$kk[] = $r['item_id'];
				}
			}

			if( $kk ){
				foreach ($kk as $et => $r) {
					foreach ($a as $etr => $rr) {
						if( $rr['parent_item_id'] == $r ){
							$a[ $etr ]['kit'] = 1;
						}
					}
				}
			}
		/* verificando productos agrupados */
			$grp = null;
			foreach ($a as $et => $r) {
				if( $r['product_type'] == 'grouped' ){
					$grp[ $r['item_id'] ] = 'no_asociado';
				}
			}

			foreach ($a as $et => $r) {
				if( $r['parent_item_id'] != null ){
					$grp[ $r['parent_item_id'] ] = 'asociado';
				}
			}

			if( $grp ){
				foreach ($grp as $et => $r) {
					if( $r=='asociado' ){ unset( $a[ $et ] ); }
				}
			}
		/* obteniendo datos adicionales de los productos */
			foreach ($a as $et => $r) {
				/* casos especiales */
					if( $r['product_type'] == 'bundle' ){
						$fsku = explode('KIT-', $r['sku']);
						if( count($fsku)>1 ){
							unset( $a[ $et ] );
							continue;
						}
					}

					$fsku = explode('MCH-', $r['sku']);
					if( count($fsku)>1 ){
						unset( $a[ $et ] );
						echo "\n sales_id ==> ".$sales_id.$omited.$r['sku'];
						continue;
					}

				//$a[ $et ]['product_options'] = null;

				$pid = $r['product_id'];
				$s = '';
				$s = $s."SELECT 'url_key' as ltable,cpev.entity_id,cpev.attribute_id,cpev.value,ea.attribute_code
					from catalog_product_entity_url_key as cpev
					inner join eav_attribute as ea on ea.attribute_id = cpev.attribute_id
					where cpev.entity_id IN ( $pid )

					union
					select 'decimal' as ltable,cpev.entity_id,cpev.attribute_id,cpev.value,ea.attribute_code
					from catalog_product_entity_decimal as cpev
					inner join eav_attribute as ea on ea.attribute_id = cpev.attribute_id
					where cpev.entity_id IN ( $pid )

					union
					select 'int' as ltable,cpev.entity_id,cpev.attribute_id,cpev.value,ea.attribute_code
					from catalog_product_entity_int as cpev
					inner join eav_attribute as ea on ea.attribute_id = cpev.attribute_id
					where cpev.entity_id IN ( $pid )

					union
					select 'text' as ltable,cpev.entity_id,cpev.attribute_id,cpev.value,ea.attribute_code
					from catalog_product_entity_text as cpev
					inner join eav_attribute as ea on ea.attribute_id = cpev.attribute_id
					where cpev.entity_id IN ( $pid )

					union
					select 'varchar' as ltable,cpev.entity_id,cpev.attribute_id,cpev.value,ea.attribute_code
					from catalog_product_entity_varchar as cpev
					inner join eav_attribute as ea on ea.attribute_id = cpev.attribute_id
					where cpev.entity_id IN ($pid )
					";
				$b = query( $s );
				if(!$b){ tt('sql no data'); continue; }

				$c = null;
				foreach ($b as $etr => $rr) {
					$c[ $rr['attribute_code'] ] = $rr;
				}

				unset( $c['description'] );
				unset( $c['short_description'] );

				if( $r['kit'] == 1 ){
					$a[ $et ]['price'] = $c['price']['value'];
					//tt( $a[ $et ]['price'] );
				}

				$a[ $et ]['product_data'] = $c;
			}

		return $a;
	}

	public function struct_report_ventas(){
		if( $this->data['sales'] == null ){ return 0; }

		$l = null;
		foreach ($this->data['sales'] as $et => $r) {
			$itms = null;

			$itms = $this->struct_report_ventas_item( $r );

			if( !$itms ){
				echo ' struct_report_ventas_item() ==> null ';
				continue; 
			}

			foreach ($itms as $et => $r) {
				$l[] = $r;
			}
		}

		return $l;
	}

	/* estructura todos los items de la venta */
	public function struct_report_ventas_item( $reg=null ){
		if($reg==null){ return null; }

		//echo print_table( $reg );

		/* obtiene todos los productos de la venta */
		$items = $this->select_all_products_of_sale( $reg['entity_id'], $reg );

		/* buscando datos de envio */
		$dir = $this->select_address_shipping( $reg );

		/* estructurando */
		$b = null;
		$margen_total = 0;
		if( !$items ){
			echo 'struct_report_ventas_item() ==> sin datos';
			return null;
		}

		$margen = 0;
		foreach ($items as $et => $r) {
			$margen += ( $r['product_data']['precio_unitario_margen_monto']['value'] * $r['qty_ordered'] );
		}

		$margen_tot = $margen;

		foreach ($items as $et => $r) {
			if( !isset( $r['product_data'] ) ){
				echo "\n faltan datos adicionales de producto\n"; continue; }
			if( !isset( $r['product_data']['precio_unitario']['value'] ) ){
				echo "\n ".$r['sku']." ==> faltan datos ==> precio_unitario\n"; }
			if( !isset( $r['product_data']['cost'] ) ){
				echo "\n ".$r['sku']." ==> faltan datos ==> cost\n"; continue; }
			if( !isset( $r['product_data']['costo_ieps_monto'] ) ){
				echo "\n ".$r['sku']." ==> faltan datos ==> costo_ieps_monto\n"; continue; }
			if( !isset( $r['product_data']['costo_total'] ) ){
				echo "\n ".$r['sku']." ==> faltan datos ==> costo_total\n"; continue; }

			$margen = $margen_tot;

			/* datos basicos */
				$a = null;
				$a['nombre'] 			= $reg['customer_firstname'];
				$a['apellidos'] 		= $reg['customer_lastname'];
				$a['idcanasta'] 		= $reg['increment_id'];
				$a['sku'] 				= $r['sku'];
				$a['article'] 			= $r['product_data']['name']['value'].' '.$r['product_data']['nombre_secundario']['value'];
				$a['cantidad'] 			= $r['qty_ordered'];
				$a['total_piezas'] 		= $this->piezas_totales($r);
				$a['marca'] 			= $this->select_marca( $r['product_data']['marca']['attribute_id'], $r['product_data']['marca']['value'] );

			/* precio */
				$a['precio_venta'] 		= $r['price'];
				$a['precio_iva'] 		= $r['tax_amount'];
				$a['precio_ieps']		= 0;
				if( $r['product_data']['impuesto_ieps_monto']['value']>0 ){
					$a['precio_ieps'] 	= $r['product_data']['impuesto_ieps_monto']['value'];
				}
				$a['precio_sin_imp'] 	= $r['price']-$r['tax_amount']-$a['precio_ieps'];
				$a['precio_mas_ieps']	= $a['precio_sin_imp']+$a['precio_ieps'];
				//$a['precio_mas_ieps'] 	= $r['product_data']['precio_unitario']['value'];

			/* peso */
				$alp1 = 0;	if( isset( $r['product_data']['peso_empaque']['value'] ) ){ 	$alp1 = (float)$r['product_data']['peso_empaque']['value']; }
				$alp2 = 0;	if( isset( $r['product_data']['ancho']['value'] ) ){ 			$alp2 = (float)$r['product_data']['ancho']['value']; }
				$alp3 = 0;	if( isset( $r['product_data']['largo']['value'] ) ){ 			$alp3 = (float)$r['product_data']['largo']['value']; }
				$alp4 = 0;	if( isset( $r['product_data']['alto']['value'] ) ){ 			$alp4 = (float)$r['product_data']['alto']['value']; }
				$alp5 = 0;	if( isset( $r['product_data']['weight']['value'] ) ){ 			$alp5 = (float)$r['product_data']['weight']['value']; }

				$a['peso']		= "$alp1";
				$a['ancho'] 	= "$alp2";
				$a['profundo'] 	= "$alp3";
				$a['alto'] 		= "$alp4";
				$a['m3'] 		= "$alp5";

			/* costo */
				$costo = $r['product_data']['precio_unitario']['value'];
				$iva = 0;	
				$ieps = 0;
				if( $r['product_data']['impuesto_iva']['value'] > 0 ){ /* % 116 */
					$iva = (100 * $costo) / ( $r['product_data']['impuesto_iva']['value'] + 100 );
				}

				if( $r['product_data']['impuesto_ieps']['value'] > 0 ){ /* % 108 */
					$ieps = (100 * ( $costo - $iva ) ) / ( $r['product_data']['impuesto_iva']['value'] + 100 );
				}

				$a['costo_unit_sin_imp'] 	= $r['product_data']['cost']['value'];
				$a['costo_unit_ieps'] 		= $r['product_data']['costo_ieps_monto']['value'];
				$a['costo_unit_con_ieps'] 	= $r['product_data']['cost']['value']+$a['costo_unit_ieps'];
				$a['costo_unit_iva'] 		= $r['product_data']['costo_iva_monto']['value'];
				$a['costo_unit'] 			= $r['product_data']['costo_total']['value'];

			/* precio unitario */
				$a['precio_unit_sin_imp'] 	= $r['product_data']['precio_unitario']['value'];
				$a['precio_unit_ieps'] 		= $r['product_data']['precio_unitario_ieps_monto']['value'];
				$a['precio_unit_mas_ieps'] 	= ( (float)$a['precio_unit_ieps'] )+( (float)$a['precio_unit_sin_imp'] );
				$a['precio_unit_iva'] 		= $r['product_data']['precio_unitario_iva_monto']['value'];
				$a['precio_unit_mas_iva'] 	= $a['precio_unit_mas_ieps']+$a['precio_unit_iva'];
				//$a['precio_unit'] 			= $r['product_data']['precio_unitario_total']['value'];
				/* precio unitario acumulado */
				$a['precio_acumulado'] 			= $a['precio_unit_mas_ieps']+$a['precio_unit_iva'];
													//$a['precio_venta'] * $a['cantidad'];
				$a['precio_acumulado_con_iva']  = $a['precio_acumulado'];
				if( $a['precio_unit_iva'] > 0 ){
					$a['precio_acumulado_con_iva'] = 	$a['precio_acumulado'] * 1.16;
				}

				//$a['margen_unit'] 			= $r['product_data']['precio_unitario_margen']['value'];
				$a['margen_unit'] 			= $r['product_data']['precio_unitario_margen_monto']['value'];

			/* pedido */
				$a['pedido_subtotal'] 		= $reg['subtotal'];
				$a['pedido_subtotal_iva'] 	= $reg['subtotal_incl_tax'] - $reg['subtotal'];

				$a['pedido_envio'] 			= $reg['shipping_amount'];
				$a['margen_total'] 			= $margen_tot;

				$a['pedido_envio_margen']	= $reg['shipping_amount'] - $margen;
				
				if( $a['pedido_envio_margen']<0 ){ $a['pedido_envio_margen'] = 0; }

				$margen = $margen - $a['pedido_envio_margen'];
				if( $margen<0 ){ $margen=0; }

				$a['op']					= ( $reg['subtotal'] + $a['pedido_envio_margen'] ) * 0.029;
				$a['op_trans']				= $a['op'] + 2.5;

				$margen -= $a['op_trans'];
				if( $margen<0 ){ $margen=0; }

				$a['pedido_envio_iva'] 		= $reg['shipping_tax_amount'];
				$a['pedido_envio_total'] 	= $reg['subtotal_incl_tax'];

				$a['pedido_descuento'] 		= $reg['discount_amount'];

				$a['pedido_iva'] 			= $reg['tax_amount'];
				$a['descuento'] 			= $reg['discount_amount'];
				$a['pedido_total'] 			= $reg['grand_total'];
				$a['margen_restante']		= $margen;

				$a['pedido_status'] 		= $reg['status'];
				$a['pedido_fecha'] 			= $reg['created_at'];
				$a['pedido_pago'] 			= $reg['updated_at'];
				$a['pedido_envio_text'] 	= ( ( $a['pedido_envio_total']>0 )?'PAGO ENVIO':'NO PAGO ENVIO' );

			/* agregando datos de direccion */
				if( $dir ){
					foreach ($dir as $etr => $rr) {
						if( $etr == 'nombre' ){ continue; }
						if( $etr == 'apellidos' ){ continue; }
						$a[ $etr ] = $rr;
					}
				}else{
					echo ' no shipping data ';
				}

			/* campo para el listado de programas asignados*/
				$a['program'] = '';

			$b[] = $a;
		}

		if( $b==null ){ return null; }

		return $b;
	}

	public function select_address_shipping( $dat = null ){

		if( $dat == null ){ return null; }

		$customer_email 		= $dat['customer_email'];
		$customer_id 			= $dat['customer_id'];
		$shipping_address_id 	= $dat['shipping_address_id'];
		$nombre 				= $dat['customer_firstname'];
		$apellidos 				= $dat['customer_lastname'];

		if( $shipping_address_id == null ){ $shipping_address_id = 0; }
		if( $customer_id == null ){ $customer_id = 0; }
		if( $customer_email == null ){ $customer_email = ''; }

		if( $shipping_address_id == 0 && $customer_id == 0 && $customer_email == '' ){
			return null;
		}

		$address = null;

		if( $shipping_address_id > 0 ){
			$address['sales'] = $this->sales_address_id( $shipping_address_id );
		}
		if( $customer_id>0 ){
			$address['customer'] = $this->customer_id_address( $customer_id );
		}else{
			$customer_id = $this->customer_email_address( $customer_email );
			if( $customer_id>0 ){
				$address['customer'] = $this->customer_id_address( $customer_id );	
			}
		}

		$dir = null;
		$dir['nombre'] 			= '';
		$dir['apellidos'] 		= '';
		$dir['telefono'] 		= '';
		$dir['telefono_ext'] 	= '';
		$dir['email'] 			= $customer_email;
		$dir['calle'] 			= '';
		$dir['nu'] 				= '';
		$dir['nu_int'] 			= '';
		$dir['cp'] 				= '';
		$dir['colonia'] 		= '';
		$dir['delegacion_municipio'] = '';
		$dir['estado'] 			= '';
		$dir['referencia'] 		= '';

		if( isset( $address['sales'] ) ){

			if( $dir['email'] == '' ){
				$dir['email'] 	= $address['sales']['email'];
			}
			$dir['nombre'] 		= $nombre;
			$dir['apellidos'] 	= $apellidos;

			$dir['calle'] 		= $address['sales']['street'];
			$dir['telefono'] 	= $address['sales']['telephone'];
			$dir['cp'] 			= $address['sales']['postcode'];
			$dir['colonia'] 	= $address['sales']['neighborhood'];
			$dir['delegacion_municipio'] = $address['sales']['city'];
			$dir['estado'] 		= $address['sales']['region'];
		}

		if( isset( $address['customer'] ) ){
			foreach ($address['customer'] as $et => $r) {
				if( !isset( $r['num_ext'] ) ){ continue; }

				if( $r['error']==1 ){ continue; }

				$dir['nu'] 				= $r['num_ext'];
				if( isset( $r['nu_int'] ) ){
					$dir['nu_int'] = $r['num_int'];
				}
				if( isset( $r['adress_references'] ) ){
					$dir['referencia'] = $r['adress_references'];
				}
				if( isset( $r['telefono_ext'] ) ){
					$dir['telefono_ext'] 	= $r['telephone_extension'];
				}
				if( $r['postcode'] != $dir['cp'] ){ continue; }
				if( $r['street']   != $dir['calle'] ){ continue; }
			}
		}

		return $dir;
	}

	public function sales_address_id( $id = 0 ){
		if($id==0){ return null; }

		$s = "SELECT
			entity_id,parent_id,customer_address_id,customer_id,fax,email,firstname,lastname,region,postcode,street,city,telephone,country_id,neighborhood,address_type,prefix,company,is_billing,rfc
			from sales_flat_order_address where entity_id = $id
			and address_type = 'shipping'";
		$a = query($s);
		if( $a == null ){ return null; }

		return $a[0];
	}

	public function customer_id_address( $cid=0 ){
		if($cid==0){ return null; }

		$s = "SELECT
			ce.entity_id,
			ce.email,
			cae.entity_id as address_id
			from customer_entity as ce
			inner join customer_address_entity as cae on cae.parent_id = ce.entity_id
			where
			cae.is_active = 1
			and cae.is_billing = 0
			and ce.entity_id = $cid
			;";
		$a = query( $s );
		if( $a==null ){ return null; }

		$d = null;
		foreach ($a as $et => $r) {
			$address_id = $r['address_id'];

			$s = "SELECT 'varchar' as ltable,caev.attribute_id,caev.value,ea.attribute_code
				from customer_address_entity_varchar as caev
				inner join eav_attribute as ea on ea.attribute_id = caev.attribute_id
				where entity_id = $address_id

				union
				SELECT 'text' as ltable,caev.attribute_id,caev.value,ea.attribute_code
				from customer_address_entity_text as caev
				inner join eav_attribute as ea on ea.attribute_id = caev.attribute_id
				where entity_id = $address_id

				union
				SELECT 'int' as ltable,caev.attribute_id,caev.value,ea.attribute_code
				from customer_address_entity_int as caev
				inner join eav_attribute as ea on ea.attribute_id = caev.attribute_id
				where entity_id = $address_id

				union
				SELECT 'decimal' as ltable,caev.attribute_id,caev.value,ea.attribute_code
				from customer_address_entity_decimal as caev
				inner join eav_attribute as ea on ea.attribute_id = caev.attribute_id
				where entity_id = $address_id

				union
				SELECT 'datetime' as ltable,caev.attribute_id,caev.value,ea.attribute_code
				from customer_address_entity_datetime as caev
				inner join eav_attribute as ea on ea.attribute_id = caev.attribute_id
				where entity_id = $address_id
				";
			$b = query( $s );

			$c = null;
			if( $b != null ){
				foreach ($b as $etr => $rr) {
					$c[ $rr['attribute_code'] ] = $rr['value'];
				}

				$c['is_company'] = 0;
				if( isset( $c['is_company'] ) ){
					if( $c['is_company'] != '' ){ $b['is_company'] = 1; }
				}

				$c['address_id'] = $address_id;

				$c['error']	= $this->address_error( $c );

				$d[] = $c;
			}
		}

		return $d;
	}

	public function customer_email_address( $email='' ){
		if($email==''){ return 0; }

		$s = "SELECT ce.entity_id
			from customer_entity as ce
			where
			ce.email = '$email'
			limit 0,1
			";

		$a = query( $s );
		if($a==null){ return 0; }

		return $a[0]['entity_id'];
	}

	public function address_error( $dat=null ){
		if($dat==null){ return 1; }

		if( $dat['city']  		== '.....' ){ return 1; }
		if( $dat['postcode'] 	== '99999' ){ return 1; }
		if( $dat['telephone'] 	== '000000000' ){ return 1; }
		if( $dat['street'] 	== '.....' ){ return 1; }

		return 0;
	}

	public function select_marca( $attribute=0, $option=0 ){

		if( $attribute==0 ){ return ''; }

		if( $this->marca ){
			if( isset( $this->marca[ $option ]['value'] ) ){
				return $this->marca[ $option ]['value'];
			}
			return '';
		}

		$s = "SELECT * from eav_attribute_option_value where store_id = 1 and option_id IN (
			select option_id from eav_attribute_option where attribute_id = $attribute order by sort_order ASC
		);";

		$a = query( $s );
		//tt( $s );
		if( $a==null ){ return ''; }

		$b = null;
		foreach ($a as $et => $r) {
			$b[ $r['option_id'] ] = $r;
		}

		$this->marca = $b;

		if( isset( $b[ $option ]['value'] ) ){
			return $b[ $option ]['value'];
		}

		return '';
	}

	public function report_search_sales_order( $sales_order='' ){
		if($sales_order==''){ return null; }

		$s = "SELECT * from report_ventas where sales_order like '$sales_order'";
		$a = query($s);

		if($a==null){ return null; }

		return $a;
	}

	public function report_list_sales_order_status( $status='' ){
		if( $status='' ){ return ''; }
		$s = "SELECT distinct( sales_order ) from report_ventas where pedido_status like 'pagado'";
		$a = query($s);
		if($a == null){ return null; }

		$b = null;
		foreach ($a as $et => $r) {
			$b[] = $r['sales_order'];
		}

		return $b;
	}

	public function list_elems(){
		$this->select_report();
		$this->data = $this->data['report'];

		return true;
	}

	public function piezas_totales($d=null){
		if($d==null){ return 0; }

		if( isset( $d['product_data']['envio_sai']['value'] ) ){
			if( $d['product_data']['envio_sai']['value']>0 ){
				$n   = (int)$d['product_data']['envio_sai']['value'];
				$qty = (int)$d['qty_ordered'];
				return $n*$qty;
			}
		}

		if( isset( $d['product_data']['sku_alterno_cantidad']['value'] ) && 
			isset( $d['product_data']['configurable_max_prods']['value'] ) ){

			$skun = (int)$d['product_data']['sku_alterno_cantidad']['value'];
			$cmax = (int)$d['product_data']['configurable_max_prods']['value'];
			$qty = (int)$d['qty_ordered'];

			if( $skun>0 && $cmax>0 ){
				$cmax = $cmax*$skun;

				return $qty*$cmax;
			}
		}

		if( isset( $d['product_data']['sku_alterno_cantidad']['value'] ) ){
			$min = (int)$d['product_data']['sku_alterno_cantidad']['value'];
			if($min>0){
				return $min * $d['qty_ordered'];
			}
		}

		return $d['qty_ordered'];
	}

	/*
		CREATE TABLE `report_ventas` (
		  `ventas_id` INT NOT NULL AUTO_INCREMENT,
		  `nombre` VARCHAR(128) NULL,
		  `apellidos` VARCHAR(128) NULL,
		  `sales_order` VARCHAR(45) NULL,
		  `sku` VARCHAR(45) NULL,
		  `article` VARCHAR(255) NULL,
		  `cantidad` INT NULL,
		  `total_piezas` INT NULL,
		  `marca` VARCHAR(128) NULL,
		  `precio_sin_imp` FLOAT NULL,
		  `precio_ieps` FLOAT NULL,
		  `precio_iva` FLOAT NULL,
		  `precio_venta` FLOAT NULL,
		  `peso` FLOAT NULL,
		  `ancho` FLOAT NULL,
		  `profundo` FLOAT NULL,
		  `alto` FLOAT NULL,
		  `m3` FLOAT NULL,
		  `costo_unit_sin_imp` FLOAT NULL,
		  `costo_unit_ieps` FLOAT NULL,
		  `costo_unit_con_ieps` FLOAT NULL,
		  `costo_unit_iva` FLOAT NULL,
		  `costo_unit` FLOAT NULL,
		  `precio_unit_sin_imp` FLOAT NULL,
		  `precio_unit_ieps` FLOAT NULL,
		  `precio_unit_mas_ieps` FLOAT NULL,
		  `precio_unit_iva` FLOAT NULL,
		  `precio_acumulado` FLOAT NULL,
		  `margen_unit` FLOAT NULL,
		  `pedido_subtotal` FLOAT NULL,
		  `pedido_subtotal_iva` FLOAT NULL,
		  `pedido_envio` FLOAT NULL,
		  `margen_total` FLOAT NULL,
		  `pedido_envio_margen` FLOAT NULL,
		  `op` FLOAT NULL,
		  `op_trans` FLOAT NULL,
		  `pedido_envio_iva` FLOAT NULL,
		  `pedido_envio_total` FLOAT NULL,
		  `pedido_descuento` FLOAT NULL,
		  `pedido_iva` FLOAT NULL,
		  `descuento` FLOAT NULL,
		  `pedido_total` FLOAT NULL,
		  `margen_restante` FLOAT NULL,
		  `pedido_status` VARCHAR(45) NULL,
		  `pedido_fecha` DATETIME NULL,
		  `pedido_pago` DATETIME NULL,
		  `pedido_envio_text` VARCHAR(45) NULL,
		  `telefono` VARCHAR(45) NULL,
		  `telefono_ext` VARCHAR(45) NULL,
		  `email` VARCHAR(128) NULL,
		  `calle` VARCHAR(128) NULL,
		  `nu` VARCHAR(45) NULL,
		  `nu_int` VARCHAR(45) NULL,
		  `cp` VARCHAR(45) NULL,
		  `colonia` VARCHAR(255) NULL,
		  `delegacion_municipio` VARCHAR(255) NULL,
		  `estado` VARCHAR(255) NULL,
		  `referencia` VARCHAR(255) NULL,
		  `programa` VARCHAR(255) NULL,
		  
		  PRIMARY KEY (`ventas_id`))
		ENGINE = InnoDB
		DEFAULT CHARACTER SET = utf8
		COLLATE = utf8_unicode_ci
		COMMENT = 'reporte de ventas';
	*/
}

?>
