<?php

if( !defined( 'M_SALES' ) ){

	define('M_SALES','Magento Sales 3.0');

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
		public function sales_address_shipping( $so='' ){
			if( $so=='' ){ return null; }

			$s = "SELECT * from sales_flat_order_address
				where
				parent_id = (
					SELECT entity_id from sales_flat_order where increment_id like '$so'
				)
				and address_type like 'shipping'
				";
			$a = query( $s );
			if( $a==null ){
				return null;
			}

			$a = $a[0];
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
	}

	class reportVentas{

		public $data = null;

		public function sumary(){
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
			$a = query($s);
			$this->data = $a;
			return true;
		}
		/* obtiene los datos basicos de una orden de compra */
		public function rv_sales( $so='' ){
			if( $so=='' ){ return false; }

			$s = "SELECT * from report_sales where sales_order like '$so'";
			$a = query( $s );
			if( $a==null ){
				$this->data = null;
				return false;
			}

			$this->data = $a[0];
			return true;
		}
		/* modifica un campo del reporte de ventas */
		public function rv_modif( $so_id='', $campo='', $data=null ){
			if( $so_id=='' ){ return false; }
			if( $campo=='' ){ return false; }

			$s = "UPDATE report_sales set $campo = '$data' where rs_id = $so_id ";
			query($s);

			return true;
		}
		/* actualiza una orden de compra del reporte de ventas */
		public function sales_report_update( $d=null ){
			//echo "\n sales_report_update()";
			if($d==null){ return false; }
			//print_r($d);

			$so = $d['sales'];
			/* si no existe */
			if( !$this->rv_sales( $so ) ){
				//echo "\n reporte ventas, no existe el registro ....";
				if( $this->rv_add_sales( $so, $d ) ){
					//echo "\n registro agregado";
					return true;
				}
				return false;
			}

			//print_r( $this->data );

			$this->rv_remove_sales( $this->data['rs_id'] );
			$this->rv_add_sales( $so, $d );
			//echo "\n registro agregado";
			return true;
		}
		/* reporte de ventas agrega un registro con los datos de la tabla sales_flat_order */
		public function rv_add_sales( $so='', $df=null ){
			//echo "\n rv_add_sales()";
			if( $so=='' ){ return false; }
			if( $df==null ){ return false; }

			$sales = new mSales();
			$sales->sales( $so );
			//print_r( $sales->data );

			$sd = $sales->data;
			$sa = $sales->sales_address_shipping( $so );
			$sp = $sales->sales_payment( $so );

			$u = new mUser();
			$ua = $u->user_address_id( $sa['customer_address_id'] );

			//print_r( $sales->data );
			//print_r( $sd );

			$campos = 'entity_id,status,shipping_description,customer_email,customer_firstname,customer_lastname,sales_order,
			total_item_count,total_qty_ordered,subtotal,subtotal_discount,subtotal_tax,subtotal_total,shipping,shipping_discount,
			shipping_tax,shipping_total,discount,tax,total,total_due,margen_total,margen_sobrante,customer_id,billing_address_id,
			shipping_address_id,quote_id,ip_refer,created_at,updated_at,payment_method,openpay_authorization,openpay_creation_date,
			openpay_payment_id,openpay_barcode,customer_telefono,customer_telefono_ext,customer_calle,customer_numero,
			customer_numero_int,customer_cp,customer_colonia,customer_deleg,customer_estado,vendor_code,factura,factura_rfc,factura_rz,
			factura_cfdi,factura_email,factura_pdf,factura_xml';

			$s = "INSERT INTO report_sales VALUES( ".
				"null, ".

				((int)$sd['entity_id']).", ".
				"'".$sd['status']."', ".
				"'".$sd['shipping_description']."', ".
				"'".$sd['customer_email']."', ".
				"'".$sd['customer_firstname']."', ".
				"'".$sd['customer_lastname']."', ".
				"'".$sd['increment_id']."', ".
				((int)$sd['total_item_count']).", ".
				((int)$sd['total_qty_ordered']).", ".
				((float)$sd['subtotal']).", ".
				((float)$sd['discount_amount']).", ".
				((float)($sd['subtotal_incl_tax']-( $sd['subtotal']-$sd['discount_amount'] ))).", ".
				((float)$sd['subtotal_incl_tax']).", ".
				((float)$sd['shipping_amount']).", ".
				((float)$sd['shipping_discount_amount']).", ".
				((float)$sd['shipping_tax_amount']).", ".
				((float)$sd['shipping_incl_tax']).", ".
				((float)$sd['discount_amount']).", ".
				((float)$sd['tax_amount']).", ".
				((float)$sd['grand_total']).", ".
				((float)$sd['total_due']).", ".
				((float)0).", ".
				((float)0).", ".
				((int)$sd['customer_id']).", ".
				((int)$sd['billing_address_id']).", ".
				((int)$sd['shipping_address_id']).", ".
				((int)$sd['quote_id']).", ".
				"'".$sd['x_forwarded_for']."', ".
				"'".$sd['created_at']."', ".
				"'".$sd['updated_at']."', ".
				"'', ".
				((float)$sp['openpay_authorization']).", ".
				"'".$sp['openpay_creation_date']."', ".
				"'".$sp['openpay_payment_id']."', ".
				"'".$sp['openpay_barcode']."', ".

				"'".$sa['telephone']."', ".
				"'".$ua['telephone_extension']['value']."', ".
				"'".$ua['street']['value']."', ".
				"'".$ua['num_ext']['value']."', ".
				"'".$ua['num_int']['value']."', ".
				"'".$ua['postcode']['value']."', ".
				"'".$ua['neighborhood']['value']."', ".
				"'".$ua['city']['value']."', ".
				"'".$ua['region']['value']."', ".

				"'', ".
				"'', ".
				"'".$df['rfc']."', ".
				"'".$df['rz']."', ".
				"'".$df['cfdi']."', ".
				"'".$df['email']."', ".
				"'', ".
				"'' ".
				")";

			//echo "\n $s";
			$id = query( $s );
			if( $id==null ){ return false; }

			return true;
		}
		/* quita una orden de venta del reporte de ventas */
		private function rv_remove_sales( $id=0 ){
			if( $id==0 ){ return false; }

			$s = "DELETE from report_sales where rs_id = $id";
			query($s);

			return true;
		}
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

CREATE TABLE `report_sales` (
  `rs_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `status` varchar(32)  NOT NULL,
  `shipping_description` varchar(255)  NOT NULL,
  `customer_email` varchar(255)  NOT NULL,
  `customer_firstname` varchar(128)  NOT NULL,
  `customer_lastname` varchar(128)  NOT NULL,
  `sales_order` varchar(45)  NOT NULL,
  `total_item_count` int(11) NOT NULL,
  `total_qty_ordered` int(11) NOT NULL,
  `subtotal` float NOT NULL,
  `subtotal_discount` float NOT NULL,
  `subtotal_tax` float NOT NULL,
  `subtotal_total` float NOT NULL,
  `shipping` float NOT NULL,
  `shipping_discount` float NOT NULL,
  `shipping_tax` float NOT NULL,
  `shipping_total` float NOT NULL,
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

*/

?>