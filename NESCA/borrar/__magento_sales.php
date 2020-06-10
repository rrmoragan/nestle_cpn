<?php



if( !defined( 'M_SALES' ) ){

	define('M_SALES','Magento Sales 3.0');
	include('magento_user_lib.php');

	class mSales{

		public $data = null;

		/* obtiene los diferentes status que tienen las ordenes de venta */
		public function sales_status(){
			$s = "SELECT distinct( status ) as status from sales_flat_order";
			echo "\n $s";
			$a = query( $s );
			if($a==null){
				return null;
			}

			$b = null;
			foreach ($a as $et => $r) {
				$b[] = $r['status'];
			}

			return $b;
		}
		/* obtiene los contadores de las ordenes de venta */
		public function totales(){
			$n = array(
				"total" => 0,
			);

			$s = "SELECT count(entity_id) as l from sales_flat_order";
			echo "\n $s";
			$a = query($s);
			if( $a == null ){
				$this->data = null;
				return 0;
			}

			$n['total'] = $a[0]['l'];

			$status = $this->sales_status();
			if( $status == null ){
				echo "\n error en consulta de base de datos";
				return 0;
			}

			foreach ($status as $et => $r) {
				$s = "SELECT count(entity_id) as l from sales_flat_order where status like '$r'";
				echo "\n $s";
				$a = query($s);
				$n[ $r ] = $a[0]['l'];
			}

			return $n;
		}
		/* lista todas las ordenes de venta en el sistema */
		public function list_sales(){
			$s = "SELECT * from sales_flat_order order by increment_id ASC";
			$a = query($s);
			if( $a==null ){
				$this->data = null;
				return 0;
			}

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['increment_id'] ] = $r;
			}

			$this->data = $b;
			return count( $this->data );
		}
		/* obtiene los usuarios relacionados con todas las ordenes de venta */
		public function list_users(){
			$n = $this->list_sales();
			if( $n==0 ){ return null; }

			$a = null;
			foreach ($this->data as $et => $r) {

				if( $r['customer_id']==null ){ $r['customer_id'] = 0; }

				if( !isset( $a[ $r['customer_email'] ]['customer_id'] ) ){
					$a[ $r['customer_email'] ]['customer_id'] = $r['customer_id'];
				}
				$a[ $r['customer_email'] ]['email']    = $r['customer_email'];
				$a[ $r['customer_email'] ]['nombre']   = $r['customer_firstname'];
				$a[ $r['customer_email'] ]['apellidos']= $r['customer_lastname'];
				$a[ $r['customer_email'] ]['sales'][ $r['increment_id'] ] = array(
					'sales' => $r['increment_id'],
					'creado' => $r['created_at'],
					'actualizado' => $r['increment_id'],
					'total' => $r['grand_total'],
					'status' => $r['status'],
					'user_id' => $r['customer_id'],
				);

			}

			$this->data = $a;
			return true;
		}
		/* actualiza la orden de compra */
		public function sales_update( $so='',$campo='', $data=null ){
			if( $so=='' ){ return false; }
			if( $campo=='' ){ return false; }

			switch ( $campo ) {
				case 'rfc':
					$this->sales_update_address_billing( $so, 'rfc', $data );
					return true;
					break;
				case 'cfdi':
					$this->sales_update_address_billing( $so, 'fax', $data );
					return true;
					break;
				case 'rz':
					$this->sales_update_address_billing( $so, 'company', $data );
					return true;
					break;
			}

			return false;
		}
		/* actualiza la direccion de facturacion de la orden de compra especifica */
		public function sales_update_address_billing( $so='',$campo='', $data=null ){

			$data_so = $this->sales_address_billing( $so );
			if( $data_so==null ){ return false; }

			//echo print_table( $data_so );

			$s = "UPDATE sales_flat_order_address set $campo = '$data' where entity_id = ".$data_so['entity_id'];
			//echo "\n $s";
			query( $s );

			$this->sales( $so );
			$s = "UPDATE sales_flat_order set 'updated_at' = '".date( 'Y-m-d G:i:s', time() )."' where entity_id = ".$this->data['entity_id'];
			//echo "\n $s";
			query($s);

			return true;
		}
		/* regresa los datos de la direccion de facturacion de la orden de compra dada */
		public function sales_address_billing( $so='' ){
			if( $so=='' ){ return null; }

			$s = "SELECT * from sales_flat_order_address
				where
				parent_id = (
					SELECT entity_id from sales_flat_order where increment_id like '$so'
				)
				and address_type like 'billing'
				";
			$a = query( $s );
			if( $a==null ){
				return null;
			}

			$a = $a[0];
			//log_data('log/factura_valid',' ... direccion => '.print_r( $a,true ));
			return $a;
		}
		/* regresa los datos de la direccion de facturacion de la orden de compra dada */
		public function sales_address_shipping( $so='', $v=false ){
			if( $so=='' ){
				if( $v ){ echo "\n sales_address_shipping ==> sales order ==> not defined"; }
				return null;
			}

			$s = "SELECT * from sales_flat_order_address ".
				" where ".
				" parent_id = ( ".
					" SELECT entity_id from sales_flat_order where increment_id like '$so' ".
				" ) ".
				" and address_type like 'shipping' ";

				if( $v ){ echo "\n sql ==> $s"; }
			$a = query( $s );
			if( $a==null ){
				if( $v ){ echo "\n no data"; }
				return null;
			}

			$a = $a[0];
			if( $v ){ print_r( $a ); }
			//log_data('log/factura_valid',' ... direccion => '.print_r( $a,true ));
			return $a;
		}
		/* obtiene los datos de pago de una orden de venta */
		public function sales_payment( $so='' ){
			if( $so=='' ){ return null; }

			$this->sales( $so );
			$id_so = $this->data['entity_id'];	$this->data = null;

			$s = "SELECT * from sales_flat_order_payment where parent_id = $id_so";
			$a = query( $s );
			if( $a==null ){ return null; }

			return $a[0];
		}
		/* obtiene una orden de venta */
		public function sales( $so='' ){
			$so = htmlentities( trim( $so), ENT_QUOTES, "UTF-8" );
			if( $so=='' ){ $this->data = null; return false; }

			$s = "SELECT * from sales_flat_order where increment_id like '$so'";
			$a = query($s);
			if($a==null){
				$this->data = null;
				return false;
			}

			$this->data = $a[0];
			return true;
		}
		/* borra una orden de ventas */
		public function sales_delete( $so='' ){
			$so = trim($so);
			if( $so=='' ){ return false; }

			if( !$this->sales( $so ) ){ return false; }

			/* pasos
				toda orden de venta a ser elimada tiene que tener el status de cancelado

				paso 1	==> obtiene id de la orden de comra
							SELECT entity_id from sales_flat_order as sfo where increment_id = '100000931'
				paso 2	==> historial de movimientos de la compra
							SELECT group_concat( entity_id ) as list from sales_flat_order_status_history where parent_id = ( 1465 );
				paso 3	==> metodo de pago
				paso 4	==> mlg type
				paso 5	==> order_grid
				paso 6	==>	productos de la orden de compra
				paso 7	==>	direccion de la orden de compra
				paso 8	==> borrado completo de la orden de compra
			*/
			if( $this->data['status'] != 'canceled' ){
				return false;
			}

			$b = null;

			$id = $this->data['entity_id'];
			/* obteniendo datos de registro */
				$s = "SELECT group_concat( entity_id ) as list from sales_flat_order_status_history where parent_id = $id;";
				//echo "\n $s";
				$a = query( $s ); if( $a ){ $a = $a[0]['list']; $b['sales_flat_order_status_history'] = $a; }

				$s = "SELECT entity_id from sales_flat_order_payment where parent_id = $id;";
				//echo "\n $s";
				$a = query( $s ); if( $a ){ $a = $a[0]['entity_id']; $b['sales_flat_order_payment'] = $a; }

				$s = "SELECT group_concat(id) as list from sales_flat_order_mlg_type where sales_order_id = $id;";
				//echo "\n $s";
				$a = query( $s ); if( $a ){ $a = $a[0]['list']; $b['sales_flat_order_mlg_type'] = $a; }

				$s = "SELECT entity_id from sales_flat_order_grid where entity_id = $id;";
				//echo "\n $s";
				$a = query( $s ); if( $a ){ $a = $a[0]['entity_id']; $b['sales_flat_order_grid'] = $a; }

				$s = "SELECT group_concat(item_id) as list from sales_flat_order_item where order_id = $id;";
				//echo "\n $s";
				$a = query( $s ); if( $a ){ $a = $a[0]['list']; $b['sales_flat_order_item'] = $a; }

				$s = "SELECT group_concat(entity_id) as list from sales_flat_order_address where parent_id = $id;";
				//echo "\n $s";
				$a = query( $s ); if( $a ){ $a = $a[0]['list']; $b['sales_flat_order_address'] = $a; }

				//print_r( $b );

			/* procesando borrado */
				if( $b['sales_flat_order_status_history']!='' ){
					$s = "DELETE from sales_flat_order_status_history where entity_id IN( ".$b['sales_flat_order_status_history']." )";
					//echo "\n $s";
					query($s);
				}
				if( $b['sales_flat_order_payment']!='' ){
					$s = "DELETE from sales_flat_order_payment where entity_id IN( ".$b['sales_flat_order_payment']." )";
					//echo "\n $s";
					query($s);
				}
				if( $b['sales_flat_order_mlg_type']!='' ){
					$s = "DELETE from sales_flat_order_mlg_type where id IN( ".$b['sales_flat_order_mlg_type']." )";
					//echo "\n $s";
					query($s);
				}
				if( $b['sales_flat_order_grid']!='' ){
					$s = "DELETE from sales_flat_order_grid where entity_id = ".$b['sales_flat_order_grid']." ";
					//echo "\n $s";
					query($s);
				}
				if( $b['sales_flat_order_item']!='' ){
					$s = "DELETE from sales_flat_order_item where item_id IN( ".$b['sales_flat_order_item']." )";
					//echo "\n $s";
					query($s);
				}
				if( $b['sales_flat_order_address']!='' ){
					$s = "DELETE from sales_flat_order_address where entity_id IN( ".$b['sales_flat_order_address']."  )";
					//echo "\n $s";
					query($s);
				}

				$s = "DELETE from sales_flat_order where entity_id = $id";
				//echo "\n $s";
				query($s);

			/* verificando */
				if( !$this->sales( $so ) ){ return true; }

			return false;
		}

		public function list_campos_items( $tipo='' ){
			$a = array(
				'decimal' => array(
					'cost', 'envio_sai', 'margen', 'configurable_precio', 'shipping_rate', 'margen_descuento'
				),
				'varchar' => array(
					'name', 'nombre_secundario', 'codigo_sap', 'sat_clave', 'sat_descrip', 'sat_unidad', 'sat_clave_unidad', 'configurable_max_prods', 'am_shipping_type', 'sku_alterno'
				),
				'gral' => array(
					'order_id', 'item_id', 'parent_item_id', 'product_id', 'product_type', 'qty_ordered', 'price', 'tax_percent', 'sku'
				),
			);

			$s = '';
			switch( $tipo ){
				case 'decimal':
					foreach ($a[ $tipo ] as $et => $r) {
						if( $s!='' ){ $s .= ','; }
						$s .= " '$r'";
					}
					return $s;
					break;
				case 'varchar':
					foreach ($a[ $tipo ] as $et => $r) {
						if( $s!='' ){ $s .= ','; }
						$s .= " '$r'";
					}
					return $s;
					break;
				case 'all':
					$b = null;
					foreach ($a as $et => $r) {
						foreach ($r as $etr => $rr) {
							$b[] = $rr;
						}
					}
					return $b;
					break;
			}

			return $s;
		}

		/* obtiene todos los items de una orden de ventas */
		public function sales_order_items( $so=null, $v=false ){

			if( $so == null ){
				if( $v ){ echo "\n sales_order_items( null )"; }
				return null;
			}

			$campos_base = 
				"sfoi.order_id, ".
				"sfoi.item_id, ".
				"sfoi.parent_item_id, ".
				"sfoi.product_id, ".
				"sfoi.product_type, ".
				"sfoi.qty_ordered, ".
				"sfoi.price, ".
				"sfoi.discount_percent, ".
				"sfoi.discount_amount, ".
				"sfoi.tax_percent, ".
				"sfoi.price_incl_tax, ".
				"sfoi.row_total, ".
				"sfoi.row_total_incl_tax, ".

				"cpe.sku, ".
				"cpev.value, ".
				"ea.attribute_code ";
			$campos_varchar = $this->list_campos_items( 'varchar' );
			$campos_decimal = $this->list_campos_items( 'decimal' );

			$s = "SELECT $campos_base ".

				" from sales_flat_order_item as sfoi  ".
				" inner join catalog_product_entity as cpe on cpe.entity_id = sfoi.product_id  ".

				" inner join catalog_product_entity_varchar as cpev on cpev.entity_id = sfoi.product_id ".
				" inner join eav_attribute as ea on ea.attribute_id = cpev.attribute_id ".

				" where  ".
				" sfoi.order_id = ( ".
					" SELECT entity_id from sales_flat_order where increment_id like '$so' ".
				" ) ".

				" and ea.attribute_code IN ( $campos_varchar ) ".

				" union ".

				" SELECT $campos_base ".

				" from sales_flat_order_item as sfoi  ".
				" inner join catalog_product_entity as cpe on cpe.entity_id = sfoi.product_id  ".

				" left join catalog_product_entity_decimal as cpev on cpev.entity_id = sfoi.product_id ".
				" inner join eav_attribute as ea on ea.attribute_id = cpev.attribute_id ".

				" where  ".
				" sfoi.order_id = ( ".
					" SELECT entity_id from sales_flat_order where increment_id like '$so' ".
				" ) ".

				" and ea.attribute_code IN ( $campos_decimal )";

			/* validando datos obtenidos */
				if( $v ){ 
					echo "\n sql ==> $s";
				}
				$a = query($s);

				if( $a == null ){
					if( $v ){ echo "\n sin datos"; }
					return null;
				}else{
					if( $v ){ echo "\n registros ==> ".count($a); }
				}

				//print_r( $a );

				$b = null;
				foreach ($a as $et => $r) {
					$b[ $r['sku'] ] = $r;
				}
				foreach ($a as $et => $r) {
					$b[ $r['sku'] ][ $r['attribute_code'] ] = $r['value'];
					unset( $b[ $r['sku'] ]['attribute_code'] );
					unset( $b[ $r['sku'] ]['value'] );
				}

			/* validando todos los campos */
				$lcampos = $this->list_campos_items('all');
				foreach ($b as $et => $r) {
					foreach ($lcampos as $etr => $rr) {
						if( !isset( $b[ $et ][ $rr ] ) ){ $b[ $et ][ $rr ] = null; }
					}
				}

				foreach ($b as $et => $r) {
					if( !isset( $r['margen'] ) ){ $r['margen'] = 0; }
					if( $r['margen'] == null ){ $b[ $et ]['margen'] = $r['margen_descuento']; }
					if( $r['margen'] == 0 ){ 	$b[ $et ]['margen'] = $r['margen_descuento']; }
					if( $b[ $et ]['margen'] == null ){ $b[ $et ]['margen'] = 0; }
				}

			return $b;
		}

	}

	class reportVentas{

		public $data = null;
		public $sql = '';

		/*
		public function sumary($v=false){
			$s = "SELECT

				sfo.entity_id,
				sfo.status,
				sfo.shipping_description,
				sfo.customer_id,
				sfo.customer_email,
				sfo.customer_firstname,
				sfo.customer_lastname,
				sfo.increment_id,
				sfo.total_item_count,
				sfo.total_qty_ordered,

				sfo.subtotal,
				sfo.discount_amount,
				sfo.subtotal_incl_tax,
				sfo.shipping_amount,
				sfo.shipping_discount_amount,
				sfo.shipping_tax_amount,
				sfo.tax_amount,
				sfo.grand_total,
				sfo.total_due,

				sfo.billing_address_id,
				sfo.shipping_address_id,
				sfo.quote_id,

				sfo.x_forwarded_for,
				sfo.created_at,
				sfo.updated_at,

				sfop.method,
				sfop.openpay_authorization,
				sfop.openpay_creation_date,
				sfop.openpay_payment_id,
				sfop.openpay_3d_secure,
				sfop.openpay_3d_secure_url,
				sfop.openpay_barcode

				from sales_flat_order as sfo
				left join sales_flat_order_payment as sfop on sfop.parent_id = sfo.entity_id
				 
				order by sfo.increment_id DESC
				";
			if( $v ){ echo "\n sql ==> ".$s; }

			$a = query($s);
			$this->data = $a;

			if( $v ){ echo "\n ==> regs ==> ".count( $this->data ); }
			return true;
		}*/

		/* lista todos las ordenes de venta con rfc */
		public function list_rfc(){
			$lcampos = array(
				'entity_id',
				'sales_order',
				'updated_at',
				'status',
				'factura',
				'factura_rfc',
				'factura_rz',
				'factura_cfdi',
				'factura_email',
				'factura_pdf',
				'factura_xml'
			);

			$lsc = '';
			foreach ($lcampos as $et => $r) {
				if( $lsc!='' ){ $lsc .= ', '; }
				$lsc .= $r;
			}

			$s = "SELECT $lsc from report_sales where factura_rfc != ''";
			$a = query($s);
			if( $a==null ){
				$this->data = null;
				echo "\n sin registros";
				return false;
			}


			$b = null;
			foreach ($a as $et => $r) { $b[ $r['sales_order'] ] = $r; }
			$this->data = $b;

			return true;
		}
		/* valida datos de facturacion */
		public function add_data_factura( $d=null ){
			$n = count($d);
			if( $d<7 ){ return false; }

			$so   = htmlentities( $d[2], ENT_QUOTES, "UTF-8" );
			$rfc  = htmlentities( $d[3], ENT_QUOTES, "UTF-8" );
			$cfdi = htmlentities( $d[4], ENT_QUOTES, "UTF-8" );
			$mail = htmlentities( $d[5], ENT_QUOTES, "UTF-8" );
			$rz   = htmlentities( $d[6], ENT_QUOTES, "UTF-8" );

			$process = "\n#. agregando datos de facturacion";

			$this->rv_sales( $so );
			if( $this->data == null ){ return false; }

			if( $this->data['factura_rfc']!='' ){
				$process = "\n#. modificando datos de facturacion";
			}

			$this->rv_modif( $this->data['rs_id'], 'factura_rfc', $rfc );
			$this->rv_modif( $this->data['rs_id'], 'factura_cfdi', $cfdi );
			$this->rv_modif( $this->data['rs_id'], 'factura_email', $mail );
			$this->rv_modif( $this->data['rs_id'], 'factura_rz', $rz );

			echo $process;

			return true;
		}
		/* valida archivos facturados */
		public function add_files_factura( $d=null ){
			$n = count($d);
			if( $d<6 ){ return false; }

			$so  = htmlentities( $d[2], ENT_QUOTES, "UTF-8" );
			$in1 = htmlentities( $d[3], ENT_QUOTES, "UTF-8" );
			$pdf = htmlentities( $d[4], ENT_QUOTES, "UTF-8" );
			$xml = htmlentities( $d[5], ENT_QUOTES, "UTF-8" );

			$process = "\n#. agregando archivos facturados";

			$this->rv_sales( $so );
			if( $this->data == null ){
				return false;
			}

			if( $this->data['factura_rfc']!='' ){
				echo "\n modificando archivos faccturados";
			}

			$this->rv_modif( $this->data['rs_id'], 'factura', $in1 );
			$this->rv_modif( $this->data['rs_id'], 'factura_pdf', $pdf );
			$this->rv_modif( $this->data['rs_id'], 'factura_xml', $xml );

			echo $process;

			return true;
		}

		/* agrega datos openpay al registro del reporte de ventas */
		public function get_openpay( $datm=null ){
			if($datm==null){ return null; }

			$so = $datm['increment_id'];
			if( $so=='' ){ return null; }

			$sales = new mSales();
			$data_payment = $sales->sales_payment( $so );

			$dat = null;
			$dat['payment_method'] = '';
			$dat['openpay_authorization'] = '';
			$dat['openpay_creation_date'] = '';
			$dat['openpay_payment_id'] = '';
			$dat['openpay_barcode'] = '';

			if( $data_payment ){
				$dat['payment_method'] = $data_payment['method'];
				$dat['openpay_authorization'] = $data_payment['openpay_authorization'];
				$dat['openpay_creation_date'] = $data_payment['openpay_creation_date'];
				$dat['openpay_payment_id'] = $data_payment['openpay_payment_id'];
				$dat['openpay_barcode'] = $data_payment['openpay_barcode'];
			}

			if( $dat['openpay_authorization'] == '' ){ $dat['openpay_authorization'] = 0.00; }
			if( $dat['openpay_authorization'] == null ){ $dat['openpay_authorization'] = 0.00; }

			return $dat;
		}
		/* obtiene la direccion de envio de la orden de ventas para agregar al reporte */
		public function get_address( $datm=null ){
			if( $datm==null ){ return null; }

			$so = $datm['increment_id'];
			if( $so=='' ){ return null; }

			$sales = new mSales();
			$user  = new mUser();
			$dir  = $sales->sales_address_shipping( $so );
			$udir = $user->user_address_id( $dir['customer_address_id'] );

			$dat = null;
			$dat['customer_telefono'] 		= $dir['telephone'];
			$dat['customer_telefono_ext'] 	= '';
			$dat['customer_calle'] 			= $dir['street'];
			$dat['customer_numero'] 		= '';
			$dat['customer_numero_int'] 	= '';
			$dat['customer_cp'] 			= $dir['postcode'];
			$dat['customer_colonia'] 		= $dir['neighborhood'];
			$dat['customer_deleg'] 			= $dir['city'];
			$dat['customer_estado'] 		= $dir['region'];

			if( $udir ){
				$dat['customer_telefono_ext'] 	= $udir['telephone_extension']['value'];
				$dat['customer_numero'] 		= $udir['num_ext']['value'];
				$dat['customer_numero_int'] 	= $udir['num_int']['value'];
			}

			return $dat;
		}

		/* agrega datos por default en caso de que no los haya */
		public function rv_add_default( $dat=null ){
			if( $dat==null ){ return null; }

			if( !isset( $dat['customer_id'] ) ){ 		$dat['customer_id']=0; }
			if( !isset( $dat['total_due'] ) ){ 			$dat['total_due']=0; }
			if( !isset( $dat['subtotal_discount'] ) ){ 	$dat['subtotal_discount']=0; }
			if( !isset( $dat['subtotal_tax'] ) ){ 		$dat['subtotal_tax']=0; }
			if( !isset( $dat['shipping_amount'] ) ){ 	$dat['shipping_amount']=0; }
			if( !isset( $dat['shipping_discount'] ) ){ 	$dat['shipping_discount']=0; }
			if( !isset( $dat['shipping_tax'] ) ){ 		$dat['shipping_tax']=0; }
			if( !isset( $dat['shipping_with_tax'] ) ){ 	$dat['shipping_with_tax']=0; }
			if( !isset( $dat['discount'] ) ){ 			$dat['discount']=0; }
			if( !isset( $dat['tax'] ) ){ 				$dat['tax']=0; }
			if( !isset( $dat['total_due'] ) ){ 			$dat['total_due']=0; }

			if( $dat['customer_id'] == null ){ 			$dat['customer_id']=0; }
			if( $dat['total_due'] == null ){ 			$dat['total_due']=0; }
			if( $dat['subtotal_discount'] == null ){ 	$dat['subtotal_discount']=0; }
			if( $dat['subtotal_tax'] == null ){ 		$dat['subtotal_tax']=0; }
			if( $dat['shipping_amount'] == null ){ 		$dat['shipping_amount']=0; }
			if( $dat['shipping_discount'] == null ){ 	$dat['shipping_discount']=0; }
			if( $dat['shipping_tax'] == null ){ 		$dat['shipping_tax']=0; }
			if( $dat['shipping_with_tax'] == null ){ 	$dat['shipping_with_tax']=0; }
			if( $dat['discount'] == null ){ 			$dat['discount']=0; }
			if( $dat['tax'] == null ){ 					$dat['tax']=0; }
			if( $dat['total_due'] == null ){ 			$dat['total_due']=0; }

			$dat['total_qty_ordered'] = (int)$dat['total_qty_ordered'];

			return $dat;
		}

		public function rv_add_items( $id_parent=0, $dat=null, $v=false ){
			if( $v ){ echo "\n rv_add_items()"; }

			$envio = null;
			$margen = 0;

			if( $id_parent==0 ){ return 0; }
			if($dat==null){ return 0; }

			if( $v ){ echo "\n rv_add_items() 1"; }
			$items = new mSales();
			$litems = $items->sales_order_items( $dat['increment_id'], $v );

			$v = true;
			if( $v ){
				foreach ($litems as $et => $r) {
					//print_r($r);
					break;
				}
				//print_r( $litems );
				//echo print_table( $litems );
			}

			if( $v ){ echo "\n rv_add_items() 2"; }

			$campos = 
				" `rsi_id`, ".
				" `rs_id`, ".
				" `parent_item_id`, ".
				" `store_id`, ".
				" `product_id`, ".
				" `product_type`, ".
				" `codigo_sap`, ".
				" `sku`, ".
				" `sku_alterno`, ".
				" `name`, ".

				" `cost`, ".
				" `margen`, ".
				" `price`, ".
				" `tax_percent`, ".
				" `price_incl_tax`, ".
				" `qty_ordered`, ".

				" `row_total`, ".
				" `row_tax`, ".
				" `row_total_incl_tax`, ".
				" `am_shipping_type`, ".
				" `shipping_rate`, ".

				" `discount_percent`, ".
				" `discount_amount`, ".
				" `id_solution`, ".

				" `envio_sai`, ".

				" `sat_clave`, ".
				" `sat_descrip`, ".
				" `sat_unidad`, ".
				" `sat_clave_unidad` ";
			$i = 0;
			foreach ($litems as $et => $r) {
				/* valores por default */
					if( $r['parent_item_id'] == null ){ $r['parent_item_id'] = 0; }
					//if( $r['margen'] == null ){ $r['margen'] = 0; }
					if( $r['am_shipping_type'] == null ){ $r['am_shipping_type'] = 0; }
					if( $r['shipping_rate'] == null ){ $r['shipping_rate'] = 0; }
					if( $r['cost'] == null ){ $r['cost'] = 0; }
					if( $r['envio_sai'] == null ){ $r['envio_sai'] = 0; }
					if( $r['price_incl_tax'] == null ){
						$r['price_incl_tax'] = 0;
						if( $r['tax_percent'] > 0 ){ $r['price_incl_tax'] = ($r['tax_percent']/100) * $r['price']; }
					}
					if( $r['row_total_incl_tax'] == null ){
						$r['row_total_incl_tax'] = $r['row_total'];
						if( $r['tax_percent'] > 0 ){ $r['row_total_incl_tax'] = ($r['tax_percent']/100) * $r['row_total']; }
					}

				/* calculos de envios */
					if( !isset( $envio[ $r['am_shipping_type'] ] ) ){
						$envio[ $r['am_shipping_type'] ] = 1;	
					}else{
						$envio[ $r['am_shipping_type'] ] += 1;
					}

				/* calculo margen */
					$r['margen'] = $r['qty_ordered'] * $r['margen'];
					$margen += $r['margen'];

				echo "\n producto ==> ".$r['product_id']." ==> margen ==> ".$r['margen'];

				$valores = " null, $id_parent, ".
					$r['parent_item_id'].", ".
					'0, '.
					$r['product_id'].", ".
					"'".$r['product_type']."', ".
					"'".$r['codigo_sap']."', ".
					"'".$r['sku']."', ".
					"'".$r['sku_alterno']."', ".
					"'".$r['name']."', ".
					$r['cost'].", ".
					$r['margen'].", ".
					$r['price'].", ".
					$r['tax_percent'].", ".
					$r['price_incl_tax'].", ".
					$r['qty_ordered'].", ".
					$r['row_total'].", ".
					($r['row_total_incl_tax'] - $r['row_total']).", ".
					$r['row_total_incl_tax'].", ".
					$r['am_shipping_type'].", ".
					$r['shipping_rate'].", ".
					$r['discount_percent'].", ".
					$r['discount_amount'].", ".
					'0, '.
					$r['envio_sai'].", ".
					"'".$r['sat_clave']."', ".
					"'".$r['sat_descrip']."', ".
					"'".$r['sat_unidad']."', ".
					"'".$r['sat_clave_unidad']."'";

				$s = "INSERT into report_sales_items( $campos ) values( $valores )";
				
				//echo "\n SQL ==> $s";
				$id = query( $s );
				if( $id==null ){
					$i = 0;
					break;
				}
				$i++;
			}

			if( $i==0 ) return null;
			$this->data = array( 'envio' => $envio, 'margen' => $margen );
			return $i;
		}

		/* agrega 1 registro de orden de venta al reporte */
		public function rv_add( $dat=null, $v=false ){
			if( $v ){ echo "\n rv_add()"; }

			if( $dat==null ){ return 0; }

			/* datos default */
				$dat = $this->rv_add_default( $dat );

			/* datos adicionales */
				$opy = $this->get_openpay( $dat );
				$dir = $this->get_address( $dat );

			$campos = " `rs_id`,
				`entity_id`,
				`status`,
				`customer_email`,
				`customer_firstname`,
				`customer_lastname`,
				`sales_order`,
				`total_item_count`,
				`total_qty_ordered`,

				`subtotal`,
				`subtotal_discount`,
				`subtotal_tax`,
				`subtotal_witch_tax`,

				`shipping_description`,
				`shipping`,
				`shipping_discount`,
				`shipping_tax`,
				`shipping_with_tax`,

				`discount`,
				`tax`,
				`total`,
				`total_due`,

				`customer_id`,
				`billing_address_id`,
				`shipping_address_id`,
				`ip_refer`,
				`created_at`,
				`updated_at` ";

			$valores = " null,
					".$dat['entity_id'].",
					'".$dat['status']."',
					'".$dat['customer_email']."',
					'".$dat['customer_firstname']."',
					'".$dat['customer_lastname']."',
					'".$dat['increment_id']."',
					".$dat['total_item_count'].",
					".$dat['total_qty_ordered'].",

					".$dat['subtotal'].",
					".$dat['subtotal_discount'].",
					".($dat['subtotal_incl_tax'] - $dat['subtotal'] ).",
					".$dat['subtotal_incl_tax'].",

					'".$dat['shipping_description']."',
					".$dat['shipping_amount'].",
					".$dat['shipping_discount_amount'].",
					".$dat['shipping_tax_amount'].",
					".$dat['shipping_incl_tax'].",

					".$dat['discount_amount'].",
					".$dat['tax_amount'].",
					".$dat['grand_total'].",
					".$dat['total_due'].",

					".$dat['customer_id'].",
					".$dat['billing_address_id'].",
					".$dat['shipping_address_id'].",
					'".$dat['remote_ip']."',
					'".$dat['created_at']."',
					'".$dat['updated_at']."' ";

			foreach ($opy as $et => $r) { $campos .= ", `$et`"; $valores .= ", '$r'"; }
			foreach ($dir as $et => $r) { $campos .= ", `$et`"; $valores .= ", '$r'"; }

			/* agregando registro principal */
				$s = "INSERT into report_sales ( $campos ) values( $valores )";
				$this->sql = $s;

				$id = query( $s );
				if( $id==null ){ return 0; }

			/* agregando items */
				$this->rv_add_items( $id, $dat, $v );

				echo "\n\n"; print_r( $this->data );

				$envio = $this->calc_envio( $this->data['envio'], $dat['subtotal'] );
				$comicion = $envio * 0.029;
				$envio_t = ($envio + $comicion + 2.5);
				$margen_sobrante = $this->data['margen'] - ($envio + $comicion + 2.5);
				if( $margen_sobrante<0 ){ $margen_sobrante = 0; }

				$this->rv_update( $id, 'margen_total', $this->data['margen'] );
				$this->rv_update( $id, 'shipping_table_rate', $envio );
				$this->rv_update( $id, 'shipping_op_comision', $comicion );
				$this->rv_update( $id, 'shipping_op_transaccion', 2.5 );
				$this->rv_update( $id, 'shipping_calc_sum', $envio_t );
				$this->rv_update( $id, 'margen_sobrante', $margen_sobrante );

			return $id;
		}
		/* calculo el costo de envio segun tamblas ams_tables_rate */
		public function calc_envio( $dat=null ){
			if($dat==null){ return 0; }

			$val = 0;
			foreach ($dat as $et => $r) {
				$env = $this->tables_rate( $et, $r );
				echo "\n envio ==> ".$env."    ";
				$val += $env;
			}

			echo "\n envio total ==> ".$val."    ";

			return $val;
		}

		/* obtiene un costo de envio segun tablas ams_tables_rate */
		public function tables_rate( $indx=0, $val=0, $method=12 ){

			$s = "SELECT * from am_table_rate where shipping_type = $indx and ( $val >= qty_from  and $val <= qty_to  ) and method_id = $method";
			$a = query( $s );
			if( $a == null ){ return 0; }
			
			$a = $a[0];

			if( $a['cost_base']>0 ){ return $a['cost_base']; }
			if( $a['cost_product']>0 ){ return $a['cost_product']; }
			return 0;
		}

		/* actualiza un campo del registro de ventas */
		public function rv_update( $rs_id=0, $campo='', $val=null ){
			if($rs_id==0){ return false; }
			if($campo==''){ return false; }

			$s = "UPDATE report_sales set $campo = '$val' where rs_id = $rs_id";
			query( $s );

			return true;
		}
		/* agrega varios registros de ordenes de ventas al reporte */
		public function rv_add_array( $dat=null, $v=false ){
			if($v){ echo "\n rv_add_array()"; }

			if( $dat==null ){ return false; }

			$i = 0;
			foreach ($dat as $et => $r) {
				//if( $i==5 ){ break; }
				echo "\n agregando ==> ".$et;
				if( !$this->rv_add( $r, $v ) ){
					echo "[ error ]";
					echo "\n SQL ==> ".$this->sql;
					return $i;
				}
				$i++;
			}

			return $i;
		}

		/* elimina los registros de una orden de ventas */
		public function rv_remove( $so='', $v=false ){
			if( $so=='' ){ return false; }

			$so = htmlentities( $so, ENT_QUOTES, "UTF-8" );

			$s = "SELECT rs_id from report_sales where sales_order like '$so'";
			$a = query($s);
			if( $a==null ){ return true; }

			foreach ($a as $et => $r) {
				$s = "DROP TABLE report_sales where rs_id = ".$r['rs_id'];
				if($v){ echo "\n SQL ==> $s"; }
				query($s);	
			}

			return true;
		}
		/* elimina todos las ordenes de ventas del reporte contenidas en el arreglo */
		public function rv_remove_array( $dat=null, $v=false ){
			if($dat==null){ return 0; }

			$i = 0;
			foreach ($dat as $et => $r) {
				$this->rv_remove( $et );
				$i++;
			}

			return $i;
		}

		public function rv_list(){
			$s = "SELECT * from report_sales";
			$a = query($s);
			if( $a==null ){
				$this->data = null;
				return 0;
			}

			/*foreach ($a as $et => $r) {
				print_r($r);
				break;
			}*/

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['sales_order'] ] = $r;
			}

			$this->data = $b;
			return count( $this->data );
		}
		/* actualiza el reporte de ventas */
		public function update( $v=false ){
			if( $v ){ echo "\n update()"; }

			$sales = new mSales();

			$sales->list_sales();
			$this->rv_list();

			$diff = $this->tables_compare( $sales->data, $this->data );
			if( $diff == null ){
				echo "\n sin registros a actualizar";
				return true;
			}

			$this->rv_remove_array( $diff, $v );
			$this->rv_add_array( $diff, $v );

			return false;
		}
		/* compara dos tablas */
		public function tables_compare( $orig=null, $dest=null ){
			if( $orig == null && $dest == null ){ return null; }
			if( $dest == null ){ return $orig; }
			if( $orig == null ){ return null; }

			echo "\n origen ==> ".count( $orig );
			echo "\n destino ==> ".count( $dest );

			/* validando numero de ordenes de compra */
				$c = null;
				foreach ($orig as $et => $r) {
					if( !isset( $dest[ $r['increment_id'] ] ) ){
						$c[ $et ] = $r;
						unset( $orig[ $et ] );
					}
				}
				//echo "\n agregar ==> ".count($c);

				$borrar = null;
				foreach ($dest as $et => $r) {
					if( !isset( $orig[ $r['sales_order'] ] ) ){
						$borrar[ $et ] = $r;
						unset( $dest[ $et ] );
					}
				}
				//echo "\n quitar ==> ".count($borrar);

			/* validando campos */
				$d = $this->tables_compare_cols( $orig, $dest, 'status', 'status' );


				if( $d!=null ){ foreach ($d as $et => $r) {
					$c[ $et ] = $r;
					unset( $orig[ $et ] );
				} }
				//echo "\n agregar status ==> ".count($d);

				$d = $this->tables_compare_cols( $orig, $dest, 'grand_total', 'total' );
				if( $d!=null ){ foreach ($d as $et => $r) {
					$c[ $et ] = $r;
					unset( $orig[ $et ] );
				} }
				//echo "\n agregar total ==> ".count($d);

				$d = $this->tables_compare_cols( $orig, $dest, 'customer_email', 'customer_email' );
				if( $d!=null ){ foreach ($d as $et => $r) {
					$c[ $et ] = $r;
					unset( $orig[ $et ] );
				} }
				//echo "\n agregar email ==> ".count($d);

				//echo "\n agregar ==> ".count($c);

			/**

				'entity_id',
				'status',
				'shipping_description',
				'customer_id',
				'customer_email',
				'increment_id',
				'total_item_count',
				'total_qty_ordered',
				customer_firstname
				customer_lastname
				subtotal
				discount_amount
				subtotal_incl_tax
				shipping_amount
				shipping_discount_amount
				shipping_tax_amount
				tax_amount
				grand_total
				total_due
				billing_address_id
				shipping_address_id
				quote_id
				x_forwarded_for
				created_at
				updated_at
				method
				openpay_authorization
				openpay_creation_date
				openpay_payment_id
				openpay_3d_secure
				openpay_3d_secure_url
				openpay_barcode

				*/

			return $c;
		}

		/* compara dos arreglos en las columnas especificas y regresa los registros que tienen diferencias */
		public function tables_compare_cols( $orig=null, $dest=null, $cpo1='', $cpo2='' ){
			if( $dest == null ){ return $orig; }
			if( $orig == null ){ return null; }

			$process = false;
			foreach ($orig as $et => $r) {
				if( isset( $r[ $cpo1 ] ) ){ $process = true; }
				break;
			}
			foreach ($dest as $et => $r) {
				if( isset( $r[ $cpo2 ] ) ){ $process = true; }
				break;
			}

			if( !$process ){ return null; }

			$c = null;
			foreach ($orig as $et => $r) {
				if( $orig[ $et ][ $cpo1 ] != $dest[ $et ][ $cpo2 ] ){
					$c[ $et ] = $r;
				}
			}

			return $c;
		}

		/* exporta los datos sumarios */
		public function export_sumary(){
			$s = "SELECT 
				sales_order,
				created_at as fecha_pedido,
				shipping_table_rate as envio,
				shipping_op_comision as op_comicion,
				shipping_op_transaccion as op_transaccion,
				shipping_calc_sum as ENVIO_SUM,
				margen_total as margen,
				shipping as envio_cobrado,
				customer_firstname,
				customer_lastname,
				shipping_description,
				status

				from report_sales order by sales_order DESC";
			$a = query($s);
			if($a==null){ $this->data = null; return 0; }

			$this->data = $a;
			return count( $this->data );
		}
	}

	/* TABLA OPENPAY VALIDATE */
	class openpayValidate{

		public $data = null;

		/* obtiene el registro openpay de la orden de venta */
		public function opv_sales( $so='' ){
			if( $so=='' ){ return false; }

			$this->data = null;

			$s = "SELECT * from openpay_validate where sales_order like '$so'";
			$a = query( $s );
			if( $a==null ){
				return false;
			}

			$b = null;
			foreach ($a as $et => $r) {
				$r['seguimiento'] = json_decode( $r['seguimiento'], true );
				$b[ $r['sales_order'] ] = $r;
			}

			$this->data = $b;
			return true;
		}
		/* lista todos los registros  */
		public function opv_sales_list_type(){
			$a = array( 'card'=>1,'bank_account'=>1,'store'=>1 );
			return $a;
		}
		public function opv_sales_list_status(){
			$s = "SELECT distinct( status ) as st from openpay_validate";
			$a = query($s);
			if( $a==null ){ return null; }

			$b = null;
			foreach ($a as $et => $r) {
				$b[] = $r['st'];
			}

			return $b;
		}
		public function opv_sales_list( $status='',$tipo='' ){

			if( $status=='all' ){ $status=''; }
			if( $tipo=='all' ){ $tipo=''; }

			$this->data = null;

			$s = "SELECT * from openpay_validate";

			$lt = $this->opv_sales_list_type();

			$t = 0;
			if( isset( $lt[ $tipo ] ) ){
				$s = $s." where seguimiento like '%\"method\":\"$tipo\"%'";
				$t++;
			}

			if( $status != '' ){
				$ss = " seguimiento like '%\"status\":\"$status\"%' ";
				if( $t ){
					$s .= " and $ss";
				}else{
					$s .= " where $ss ";
				}
			}

			$a = query( $s );
			if( $a==null ){ return false; }

			$b = null;
			foreach ($a as $et => $r) {
				$r['seguimiento'] = json_decode( $r['seguimiento'], true );
				$b[ $r['sales_order'] ] = $r;
			}

			$this->data = $b;
			return true;
		}
	}
}

/*

	describe report_sales;

	truncate report_sales;
	truncate report_sales_items;

	CREATE TABLE `report_sales` (
	  `rs_id` int(11) NOT NULL,
	  `entity_id` int(11) NOT NULL,
	  `status` varchar(32)  NOT NULL,
	  `customer_email` varchar(255)  NOT NULL,
	  `customer_firstname` varchar(128)  NOT NULL,
	  `customer_lastname` varchar(128)  NOT NULL,
	  `sales_order` varchar(45)  NOT NULL,

	  `total_item_count` int(11) NOT NULL,
	  `total_qty_ordered` int(11) NOT NULL,
	  `total_sai` int(11) NOT NULL,

	  `costo_total` float NOT NULL,
	  `subtotal` float NOT NULL,
	  `subtotal_discount` float NOT NULL,
	  `subtotal_tax` float NOT NULL,
	  `subtotal_witch_tax` float NOT NULL,

	  `shipping_table_rate` float NOT NULL,
	  `shipping_op_comision` float NOT NULL,
	  `shipping_op_transaccion` float NOT NULL,
	  `shipping_calc_sum` float NOT NULL,

	  `shipping_description` varchar(255)  NOT NULL,
	  `shipping` float NOT NULL,
	  `shipping_discount` float NOT NULL,
	  `shipping_tax` float NOT NULL,
	  `shipping_with_tax` float NOT NULL,

	  `discount` float NOT NULL,
	  `tax` float NOT NULL,
	  `total` float NOT NULL,
	  `total_due` float NOT NULL,
	  `margen_total` float NOT NULL,
	  `margen_sobrante` float NOT NULL,
	  `customer_id` int(11) NOT NULL,
	  `billing_address_id` int(11) NOT NULL,
	  `shipping_address_id` int(11) NOT NULL,
	  `quote_id` int(11) NOT NULL,
	  `ip_refer` varchar(256)  NOT NULL,
	  `created_at` datetime NOT NULL,
	  `updated_at` datetime NOT NULL,
	  `payment_method` varchar(32)  NOT NULL,
	  `openpay_authorization` float NOT NULL,
	  `openpay_creation_date` datetime NOT NULL,
	  `openpay_payment_id` varchar(255)  NOT NULL,
	  `openpay_barcode` varchar(255)  NOT NULL,
	  `customer_telefono` varchar(15)  NOT NULL,
	  `customer_telefono_ext` varchar(15)  NOT NULL,
	  `customer_calle` varchar(255)  NOT NULL,
	  `customer_numero` varchar(10)  NOT NULL,
	  `customer_numero_int` varchar(10)  NOT NULL,
	  `customer_cp` varchar(10)  NOT NULL,
	  `customer_colonia` varchar(255)  NOT NULL,
	  `customer_deleg` varchar(255)  NOT NULL,
	  `customer_estado` varchar(255)  NOT NULL,
	  `vendor_code` varchar(255)  NOT NULL,

	  `factura` varchar(255)  NOT NULL,
	  `factura_rfc` varchar(255)  NOT NULL,
	  `factura_rz` varchar(255)  NOT NULL,
	  `factura_cfdi` varchar(255)  NOT NULL,
	  `factura_email` varchar(255)  NOT NULL,
	  `factura_pdf` varchar(255)  NOT NULL,
	  `factura_xml` varchar(255)  NOT NULL

	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='reporte general de ventas';


	ALTER TABLE `report_sales` ADD PRIMARY KEY (`rs_id`);
	ALTER TABLE `report_sales` MODIFY `rs_id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `report_sales_items` (
	`rsi_id` int(11) NOT NULL,
	`rs_id` int(11) NOT NULL,
	`parent_item_id` int(11) NOT NULL,
	`store_id` smallint(5) unsigned NOT NULL,
	`product_id` int(11) NOT NULL,
	`product_type` varchar(255)  NOT NULL,
	`codigo_sap` varchar(255)  NOT NULL,
	`sku` varchar(255)  NOT NULL,
	`sku_alterno` varchar(255)  NOT NULL,
	`name` varchar(255)  NOT NULL,

	`cost` float NOT NULL,
	`margen` float NOT NULL,
	`price` float NOT NULL,
	`tax_percent` float NOT NULL,
	`price_incl_tax` float NOT NULL,
	`qty_ordered` float NOT NULL,

	`row_total` float NOT NULL,
	`row_tax` float NOT NULL,
	`row_total_incl_tax` float NOT NULL,
	`am_shipping_type` int(11) NOT NULL,
	`shipping_rate` float NOT NULL,

	`discount_percent` float NOT NULL,
	`discount_amount` float NOT NULL,
	`id_solution` int(11) NOT NULL,

	`envio_sai` int(11) NOT NULL,

	`sat_clave` varchar(255)  NOT NULL,
	`sat_descrip` varchar(255)  NOT NULL,
	`sat_unidad` varchar(255)  NOT NULL,
	`sat_clave_unidad` varchar(255)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='items del reporte general de ventas';

ALTER TABLE `report_sales_items` ADD PRIMARY KEY (`rsi_id`);
ALTER TABLE `report_sales_items` MODIFY `rsi_id` int(11) NOT NULL AUTO_INCREMENT;

*/

?>