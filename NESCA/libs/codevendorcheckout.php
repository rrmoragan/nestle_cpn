<?php
if( !defined('CODE_VENDOR') ){

	define('CODE_VENDOR','CodeVendorCheckout v0.2');

	include('basics.php');
	include('querys.php');
	include('logs.php');

	class codeVendorCheckout{
		public $data = null;
		public $error = '';
		public $data_opc = 0;
		public $log_file = 'log_code_vendor';

		public function valid_data($d=null){
			log_data($this->log_file, 'valid_data()' );

			if( $d==null ){
				$this->error = 'no data';
				return false;
			}

			$fecha = date('Y-m-d G:i:s', time() );

			/* validando que existan campos obligatorios */
				if( !isset( $d['system'] ) ){	$this->error = 'no data'; log_data('vd-001' ); return false; }
				if( !isset( $d['quote_id'] ) ){	$this->error = 'no data'; log_data('vd-002' ); return false; }
				if( !isset( $d['code_vendor'] ) ){	$this->error = 'no data'; log_data('vd-003' ); return false; }
				if( !isset( $d['remote_ip'] ) ){	$this->error = 'no data'; log_data('vd-004' ); return false; }
				//if( !isset( $d['customer_id'] ) ){	$this->error = 'no data'; 	return false; }
				//if( !isset( $d['customer_email'] ) ){	$this->error = 'no data'; 	return false; }

				if( !isset( $d['updated_at'] ) ){
					$d['updated_at'] = $fecha;
				}

			/* quitando espacios iniciales y finales */
				$d['system'] = trim( $d['system'] );
				$d['updated_at'] = trim( $d['updated_at'] );
				$d['customer_email'] = trim( $d['customer_email'] );
				$d['code_vendor'] = htmlentities( trim( $d['code_vendor'] ), ENT_QUOTES, "UTF-8" );
				$d['remote_ip'] = trim( $d['remote_ip'] );
				if( $d['customer_id']=='' ){ $d['customer_id']=0; }

			/* validando campos vacios */
				if( $d['system']=='' ){	$this->error = 'no data'; log_data('vd-005' ); return false; }
				if( $d['quote_id']=='' ){	$this->error = 'no data'; log_data('vd-006' ); return false; }
				//if( $d['customer_id']=='' ){	$this->error = 'no data'; 	return false; }
				//if( $d['customer_email']=='' ){	$this->error = 'no data'; 	return false; }
				if( $d['code_vendor']=='' ){	$this->error = 'no data'; log_data('vd-007'); return false; }
				if( $d['remote_ip']=='' ){	$this->error = 'no data'; log_data('vd-008'); return false; }

				if( $d['updated_at']=='' ){ 		$d['updated_at'] = $fecha; }

			/* validando quote */
				if( $d['quote_id']<=0 ){	$this->error = 'quote null'; log_data('vd-009'); return false; }
			/* validando email */
				if( $d['customer_email']!='' ){
					if (!filter_var($d['customer_email'], FILTER_VALIDATE_EMAIL)) {	$this->error = 'email error'; log_data('vd-0010'); return false; }
				}

			/* llenado inicial */
				$a = array(
					'system' => $d['system'],
					'quote_id' => $d['quote_id'],
					'sales_order' => '',
					'updated_at' => $d['updated_at'],
					'customer_id' => $d['customer_id'],
					'customer_email' => $d['customer_email'],
					'code_vendor' => $d['code_vendor'],
					'remote_ip' => $d['remote_ip'],
				);

				$a['items_count'] 	= 0;
				$a['items_qty'] 	= 0;
				$a['subtotal'] 		= 0.00;
				$a['grand_total'] 	= 0.00;
				$a['customer_firstname'] = '';
				$a['customer_lastname'] = '';
				$a['customer_openpay'] = '';

				if( isset( $d['sales_order'] ) ){ 	$a['sales_order'] = $d['sales_order']; }

			/* llenado datos adicionales */
				if( isset( $d['items_count'] ) ){ 	$a['items_count'] 	= $d['items_count']; }
				if( isset( $d['items_qty'] ) ){ 	$a['items_qty'] 	= $d['items_qty']; }
				if( isset( $d['subtotal'] ) ){ 		$a['subtotal'] 		= $d['subtotal']; }
				if( isset( $d['grand_total'] ) ){ 	$a['grand_total'] 	= $d['grand_total']; }

				if( isset( $d['customer_firstname'] ) ){ 		$a['customer_firstname'] 	= $d['customer_firstname']; }
				if( isset( $d['customer_lastname'] ) ){ 		$a['customer_lastname'] 	= $d['customer_lastname']; }
				if( isset( $d['customer_openpay_user_id'] ) ){ 	$a['customer_openpay'] 		= $d['customer_openpay_user_id']; }

				$a['mkd'] = null;
				if( isset( $d['mkd'] ) ){
					$a['mkd'] = $d['mkd'];
				}

			$this->data = $a;

			return true;
		}
		public function update_code_vendor(){
			log_data($this->log_file, 'update_code_vendor()' );

			if( $this->data == null ){
				$this->error = 'no data';
				return false;
			}

			/* determina si existe o no el registro */
			$id = $this->exist_code_vendor();
			if( !$id ){
				/* agrega el codigo */
				if( $this->insert_code_vendor() == 0 ){
					$this->error = 'error save new data';
					log_data($this->log_file, 'error save new data' );
					return false;
				}
				return true;
			}

			/* actualiz el codigo */
			log_data($this->log_file, 'actualizando registro' );
			if( !$this->upgrade_code_vendor($id) ){	$this->error = 'error update data'; 	return false; }

			return true;
		}
		/* regresa el id del registro de la venta asociada al codigo del vendedor */
		public function exist_code_vendor(){
			log_data($this->log_file, 'exist_code_vendor()' );
			log_data($this->log_file, 'exist_code_vendor() ==> '.print_r( $this->data,true ) );

			$session = $this->data['mkd'];

			$s = "SELECT * from code_vendor_regs where 
				session_id =  '$session' 
				and status <> 'canceled'
				order by gvo_id DESC limit 0,1";

			$a =  query($s);
			log_data($this->log_file, "SQL ==> $s" );

			if( $a==null ){ return 0; }

			return $a[0]['gvo_id'];
		}
		private function insert_code_vendor(){
			log_data($this->log_file, 'insert_code_vendor()' );

			$this->data_opc = 1;

			$d = $this->data;

			log_data($this->log_file, 'insert_code_vendor() ==> '.print_r( $d,true ) );

			$s = "INSERT INTO code_vendor_regs values(
				null,
				".$d['quote_id'].",
				'".$d['sales_order']."',
				'".$d['updated_at']."',
				".$d['customer_id'].",
				'".$d['customer_email']."',
				'".$d['code_vendor']."',
				'".$d['remote_ip']."',
				".$d['items_count'].",
				".$d['items_qty'].",
				".$d['subtotal'].",
				".$d['grand_total'].",
				'".$d['customer_firstname']."',
				'".$d['customer_lastname']."',
				'".$d['customer_openpay']."',
				'inicial',
				'".$d['mkd']."'
			)";

			$id = query($s);
			log_data($this->log_file, "sql ==> $s" );
			if( $id == null ){ return 0; }
			return $id;
		}
		public function upgrade_code_vendor($id){
			log_data($this->log_file, 'upgrade_code_vendor()' );

			$this->data_opc = 2;
			$fecha = date('Y-m-d G:i:s', time() );

			$s = "UPDATE code_vendor_regs set 
				quote_id = '".			$this->data['quote_id']."',
				items_count = '".		$this->data['items_count']."',
				items_qty = '".			$this->data['items_qty']."',
				subtotal = '".			$this->data['subtotal']."',
				grand_total = '".		$this->data['grand_total']."',
				customer_firstname = '".$this->data['customer_firstname']."',
				customer_lastname = '".	$this->data['customer_lastname']."',
				code_vendor = '".		$this->data['code_vendor']."',
				updated_at = '".		$fecha."',
				status = 'update',
				customer_email = '".	$this->data['customer_email']."',
				customer_id = '".		$this->data['customer_id']."'
				where gvo_id = $id";
			query( $s );
			log_data($this->log_file, "sql ==> $s" );

			return true;
		}
		public function code_vendor_action(){
			log_data($this->log_file, 'code_vendor_action()' );

			if( $this->data_opc == 1 ){ return 'new_data'; }
			if( $this->data_opc == 2 ){ return 'update_data'; }

			return 'no_accion';
		}
		/* 
		 * muestra todos los registros de codigos de vendedores agregados en el checkout
		 * tabla usada ==> code_vendor_regs
		 */
		public function cv_list(){
			$s = "SELECT gvo_id,quote_id,sales_order,updated_at,customer_id,customer_email,code_vendor,grand_total,status,session_id from code_vendor_regs";
			$a = query($s);
			log_data($this->log_file, "sql ==> $s" );

			$this->data = $a;
			return $a;
		}
		public function cv_list_sales_order(){
			$s = "SELECT gvo_id,quote_id,sales_order,updated_at,customer_id,customer_email,code_vendor,grand_total from code_vendor_regs
				where sales_order <> ''
			";
			$a = query($s);
			log_data($this->log_file, "sql ==> $s" );

			if($a==null){ return null; }

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['sales_order'] ] = $r;
			}

			$this->data = $b;
			return $b;
		}

		/* filtro para los codigos de vendedor */
		public function cv_list_nestle(){
			$s = "SELECT * from code_vendor_nestle where program like '..checkout..'";
			$a = query( $s );
			if($a==null){
				$this->data = null;
				return null;
			}

			$b = null;
			foreach ($a as $et => $r) {
				$r['name'] = trim( $r['name'] );
				unset( $r['program'] );

				$b[ $r['code'] ] = $r;
			}

			$this->data = $b;
			return true;
		}

		/* obtiene el registro del usuario actual */
		public function cv_current_data($p){
			if( !isset( $p['mkd'] ) ) return null;

			log_data($this->log_file, "".print_r( $p,true ) );

			$session = $p['mkd'];

			$s = "SELECT * from code_vendor_regs 
				where 
					session_id like '$session' 
					and status != 'canceled'
					order by gvo_id DESC limit 0,1";
			$a = query($s);
			log_data($this->log_file, "$s" );
			if($a == null){ return null; }

			return $a[0]['code_vendor'];
		}

		/* obtiene todos los codigos de vendedor que no tienen un sales order indexado */
		public function cv_list_no_sales(){
			log_data($this->log_file, 'cv_list_no_sales()' );

			$s = "SELECT * from code_vendor_regs where sales_order like '' and status <> 'canceled'";
			$a = query($s);
			log_data($this->log_file, 'sql ==> '.print_r( $s,true ) );

			$this->data = $a;
			return true;
		}

		/* agrga los id de orden de ventas a los codigos de vendedor */
		public function cv_add_sales(){
			log_data($this->log_file, 'cv_add_sales()' );

			if( $this->data == null ){ return false; }

			foreach ($this->data as $et => $r) {
				$cu = $r['customer_id'];
				$s = "SELECT 
					entity_id,status,increment_id,grand_total,customer_id,customer_email,quote_id,updated_at
					from sales_flat_order where customer_id = $cu order by entity_id DESC";
				$a = query($s);
				log_data($this->log_file, 'sql ==> '.print_r( $s,true ) );

				if( $a==null ){ return false; }
				foreach ($a as $et => $soa) {
					if(
						$r['customer_id']    == $soa['customer_id'] &&
						$r['customer_email'] == $soa['customer_email'] &&
						round($r['grand_total'],2) == round($soa['grand_total'],2)
					){
						$so = $soa['increment_id'];
						$cd_id = $r['gvo_id'];
						$s = "UPDATE code_vendor_regs set sales_order = '$so', session_id = '_".$r['session_id']."' where gvo_id = $cd_id";
						query($s);
						log_data($this->log_file, 'sql ==> '.print_r( $s,true ) );

						break;
					}
				}
			}

			return true;
		}

		/* lista todos los vendedores dados de alta */
		public function cv_list_vendor(){
			$s = "SELECT * from code_vendor_nestle where program like '..checkout..'";
			$a = query( $s );
			if($a==null){
				$this->data = null;
				return null;
			}

			$b = null;
			foreach ($a as $et => $r) {
				$r['name'] = trim( $r['name'] );
				$b[ $r['code'] ] = $r;
			}

			$this->data = $b;
			return true;
		}

		/* busca un codigo de vendedor */
		public function cv_vendor_search($code){
			if( $code==null ){
				$this->data = null;
				return false;
			}

			$s = "SELECT * from code_vendor_nestle where code like '$code'";
			$a = query( $s );
			if($a==null){
				$this->data = null;
				return null;
			}

			$b = null;
			$b[ $a[0]['code'] ] = $a[0];

			$this->data = $b;
			return true;
		}

		/* agrega un vendedor */
		public function cv_vendor_new( $name='', $code='', $program='' ){

			$code = htmlentities( trim( $code ), ENT_QUOTES, "UTF-8"  );
			//$name = htmlentities( trim( $name ), ENT_QUOTES, "UTF-8"  );
			$name = trim( $name );
			$program = htmlentities( trim( $program ), ENT_QUOTES, "UTF-8"  );

			if( $code == '' ){ return false; }
			if( $name == '' ){ return false; }
			if( $program == '' ){ return false; }

			$program = "..".$program."..";

			$this->cv_vendor_search( $code );
			if( $this->data != null ){ return false; }

			$s = "INSERT INTO code_vendor_nestle values( null, '$name', '$code', '$program' )";
			query($s);

			return true;
		}

		/* obtiene el codigo de vendedor asignado a una orden de venta */
		public function get_cv_sales_order( $so='' ){
			if( $so=='' ){ return ''; }

			$s = "SELECT code_vendor from code_vendor_regs where sales_order like '$so'";
			$a = query( $s );
			if( $a == null ){ return ''; }

			$cv = $a[0]['code_vendor'];
			return $cv;
		}

		/* actualiza cv con un sales order */
		public function set_cv_sales_order( $so='', $reg=0 ){
			if( $so == '' ){ return false; }
			if( $reg == 0 ){ return false; }

			$cv = $this->get_cv_sales_order( $so );
			if( $cv!='' ){
				echo "\n esta orden de venta ya tiene un codigo de vendedor [$cv]";
				return false;
			}

			$s = "UPDATE code_vendor_regs set sales_order = '$so' where gvo_id = $reg";
			echo "\n sql ==> $s";
			query($s);
			$s = "UPDATE code_vendor_regs set status  = 'update' where gvo_id = $reg";
			echo "\n sql ==> $s";
			query($s);
			$s = "UPDATE code_vendor_regs set updated_at  = '".( date( 'Y-m-d G:i:s' ) )."' where gvo_id = $reg";
			echo "\n sql ==> $s";
			query($s);

			$this->cv_list();
			$b = null;
			foreach ($this->data as $et => $r) {
				if( $r['gvo_id'] == $reg ){
					$b[] = $r;
				}
			}

			$this->data = $r;
			if( $this->data != null ) return true;

			return false;
		}


		/* tables

		    CREATE TABLE `code_vendor_nestle` (
			  `cv_id` INT NOT NULL AUTO_INCREMENT,
			  `name` VARCHAR(255) NULL,
			  `code` VARCHAR(255) NULL,
			  `program` VARCHAR(255) NULL,
			  PRIMARY KEY (`cv_id`))
			ENGINE = InnoDB
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_unicode_ci
			COMMENT = 'listado de codigos de vendedores';

		    CREATE TABLE `code_vendor_regs` (
			  `gvo_id` INT NOT NULL AUTO_INCREMENT,
			  `quote_id` INT NULL,
			  `sales_order` VARCHAR(45) NULL,
			  `updated_at` DATETIME NULL,
			  `customer_id` INT NULL,
			  `customer_email` VARCHAR(45) NULL,
			  `code_vendor` VARCHAR(128) NULL,
			  `remote_ip` VARCHAR(128) NULL,
			  `items_count` INT NULL,
			  `items_qty` INT NULL,
			  `subtotal` FLOAT NULL,
			  `grand_total` FLOAT NULL,
			  `customer_firstname` VARCHAR(128) NULL,
			  `customer_lastname` VARCHAR(128) NULL,
			  `customer_openpay` VARCHAR(128) NULL,
			  `status` VARCHAR(45) NULL,
			  `session_id` VARCHAR(64) NULL,
			  PRIMARY KEY (`gvo_id`))
			ENGINE = InnoDB
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_unicode_ci
			COMMENT = 'otras ventas de los vendedores de codigo grano';

			INSERT INTO `code_vendor_regs` VALUES (1,719,'','2019-05-16 12:46:26',0,'','eeeeeeeeee','127.0.0.1',1,1,130,130,'','','','update'),
				(2,728,'','2019-05-16 17:54:12',0,'','qwedrftgh','127.0.0.1',4,6,1052,1052,'','','','inicial'),
				(3,727,'','2019-05-16 18:24:40',0,'','zasdfghjkl&ntilde;23456789','127.0.0.1',3,6,1060,1060,'','','','inicial'),
				(4,1264,'','2019-05-22 15:58:56',0,'','10850616','127.0.0.1',1,6,1140,1140,'','','','inicial'),
				(5,1488,'','2019-05-24 16:44:22',477,'uvgtuxtlacafeteria@gmail.com','10880042','127.0.0.1',11,13,3492.2,3770.63,'DULCE CLAUDIA','JIMENEZ SARAOZ','','update'),
				(6,56,'','2019-06-07 12:56:23',0,'','manolo 2','127.0.0.1',1,3,60,60,'','','','update'),
				(7,1,'','2019-06-07 13:00:51',474,'rmorales@mlg.com.mx','macaco v2','127.0.0.1',1,2,168,168,'RaÃºl','Morales','avrathmmcwftnv079qup','update'),
				(8,59,'','2019-06-07 17:58:55',0,'','pepe pecas','127.0.0.1',1,2,164,164,'','','','inicial'),
				(9,526,'','2019-06-17 20:48:11',0,'','b','127.0.0.1',1,1,1011.94,1102.81,'','','','inicial'),
				(10,527,'','2019-06-17 22:34:29',474,'rmorales@mlg.com.mx','uuuu','127.0.0.1',1,2,280,280,'RaÃºl','Morales','','inicial');
		 *
		 */
	}
}
?>