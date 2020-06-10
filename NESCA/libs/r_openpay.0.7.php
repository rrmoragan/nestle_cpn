<?php

if( !defined('PG_VER') ){

	include('basics.php');
	include('querys.php');

	if( !defined('FORCE_UTF8') ){ include('forceUTF8.php'); define('FORCE_UTF8',true); }
	include('logs.php');

	define('PG_VER','0.1');
	define('OPENPAY_ID_COMERCIO','myhbbepw14sfvxyylolr');
	define('OPENPAY_PUBLIC','pk_9110baef11ef4beabf619f7a8279dee8');
	define('OPENPAY_PRIVATE','sk_094e464a249f470b9eba5cdc9a7664c6');
	define('OPENPAY_DIR','/var/www/magento/NESCA/openpay_sales/');
	define('OPENPAY_REPORT','/var/www/magento/NESCA/mlg_reports');
	define('OPENPAY_REPORT_URL','/mlg_openpayreport_link.url');
	define('PROGRAM','CPMN');

	class openpay_mlg{

		public $data = null;
		public $control = null;

		// v0.1
		public function list_sales_order_openpay(){
			$s = "SELECT
				x.entity_id,
				x.increment_id,
				x.status,
				x.customer_email,
				x.customer_id,
				x.openpay_payment_id,
				x.openpay_user_id,
				x.openpay_status,
				x.dinamic,
				x.estado,
				x.cp,
				x.calle, 
				x.delegacion,
				x.telefono,
				x.colonia

				from (
					SELECT
					sfo.entity_id,
					sfo.increment_id,
					sfo.status,
					sfo.customer_email,
					ce.entity_id as customer_id,
					sfop.openpay_payment_id,
					cev.value as openpay_user_id,
					ov.status as openpay_status,
					sfoty.dinamic,

					sfoad.region as estado,
					sfoad.postcode as cp,
					sfoad.street as calle, 
					sfoad.city as delegacion,
					sfoad.telephone as telefono,
					sfoad.neighborhood as colonia

					from sales_flat_order as sfo
					left join sales_flat_order_payment as sfop on sfop.parent_id=sfo.entity_id
					left join customer_entity as ce on ce.email = sfo.customer_email
					left join customer_entity_varchar as cev on cev.entity_id = ce.entity_id
					left join eav_attribute as ea on ea.attribute_id = cev.attribute_id
					left join openpay_validate as ov on ov.sales_order = sfo.increment_id
					left join sales_flat_order_mlg_type as sfoty on sfoty.sales_order_id = sfo.entity_id
					left join sales_flat_order_address as sfoad on sfoad.parent_id = sfo.entity_id
					where 
					ea.attribute_code like 'openpay_user_id'
					and sfoad.address_type like 'shipping'
				) as x
				where x.openpay_user_id <> ''
				and x.openpay_payment_id <> ''
				and NOT (x.openpay_status like 'cancelled' and x.status like 'canceled')
				and NOT (x.openpay_status like 'completed' and ( x.status like 'canceled' OR x.status like 'pagado' ) )
				";
			$a = query($s);
			if($a==null){
				return 0;
			}

			$this->data = $a;
			return count( $this->data );
		}

		// v1.4
		public function list_sales_order( $sort='' ){
			if( $sort=='' ){ 
				$sort='order by sfo.entity_id DESC'; }else{
				$sort='order by sfo.entity_id ASC';
			}
			
			$s = "SELECT
				x.entity_id,
				x.increment_id,
				x.status,
				x.customer_email,
				x.customer_id,
				x.openpay_payment_id,
				x.openpay_user_id,
				x.openpay_status,
				x.dinamic,
				x.estado,
				x.cp,
				x.calle, 
				x.delegacion,
				x.telefono,
				x.colonia

				from (
				SELECT
				sfo.entity_id,
				sfo.increment_id,
				sfo.status,
				sfo.customer_email,
				ce.entity_id as customer_id,
				sfop.openpay_payment_id,
				cev.value as openpay_user_id,
				ov.status as openpay_status,
				sfoty.dinamic,

				sfoad.region as estado,
				sfoad.postcode as cp,
				sfoad.street as calle, 
				sfoad.city as delegacion,
				sfoad.telephone as telefono,
				sfoad.neighborhood as colonia

				from sales_flat_order as sfo
				left join sales_flat_order_payment as sfop on sfop.parent_id=sfo.entity_id
				left join customer_entity as ce on ce.email = sfo.customer_email
				left join customer_entity_varchar as cev on cev.entity_id = ce.entity_id
				left join eav_attribute as ea on ea.attribute_id = cev.attribute_id
				left join openpay_validate as ov on ov.sales_order = sfo.increment_id
				left join sales_flat_order_mlg_type as sfoty on sfoty.sales_order_id = sfo.entity_id
				left join sales_flat_order_address as sfoad on sfoad.parent_id = sfo.entity_id
				where 
				ea.attribute_code like 'openpay_user_id'
				and sfoad.address_type like 'shipping'
				) as x
				$sort;
				";
			$a = query($s);
			if($a==null){
				return 0;
			}

			$this->data = $a;
			return count( $this->data );
		}

		public function list_curl(){
			$this->data==null;

			$n = $this->list_sales_order_openpay();
			if( $n==0 ){ return false; }

			$a = null;
			foreach ($this->data as $et => $r) {
				$s = "\ncurl https://api.openpay.mx/v1/".OPENPAY_ID_COMERCIO."/customers/".( trim($r['openpay_user_id']) )."/charges/".$r['openpay_payment_id']." -u sk_094e464a249f470b9eba5cdc9a7664c6: > ".OPENPAY_DIR.$r['increment_id'].'.sales_order';
				$a[] = $s;
			}

			$this->data = $a;
			return true;
		}

		public function process_files(){
			$_dir = getcwd();
			chdir(OPENPAY_DIR);
			$dir = opendir('.');

			$i = 0;
			while ($file = readdir($dir)){
				if( is_dir($file) ){ continue; }

				if( $this->magento_data_openpay( $this->file_to_sales( $file ) ) ){
					continue;
				}

				$ff = explode('.sales_', $file);
				$nn = count( $ff );
				if( $nn==1 ){ continue; }
				if( $ff[ $nn-1 ] != 'order' ){ continue; }

				echo "\n ... $file";

				$fp = fopen($file, 'r');
				if( !$fp ) { tt('error file open ==> '.$file); continue; }

			    while (false !== ($string = fgets($fp))) {
			        $this->magento_data_openpay_update( $this->file_to_sales($file), fixUTF8($string), false );
			    }
			    $i++;
			}

			closedir( $dir );
			//opendir( $_dir );
			//closedir( $_dir );
			//echo "\narchivos procesados ==> ".$i;
			//echo "\n";

			return true;
		}

		public function file_to_sales($file=''){
			if($file==''){ return ''; }

			$a = explode(".sales", $file);
			if( !isset( $a[1] ) ){ return ''; }
			if( $a[1] == '_order' ){ return $a[0]; }
			return '';
		}
		public function magento_data_openpay( $sales = '' ){
			if($sales==''){ return false; }

			$s = "SELECT * from openpay_validate where sales_order like '$sales' and status IN( 'canceles', 'pagado' )";
			$a = query( $s );
			if($a==null){ return false; }

			return true;
		}
		public function is_exist_data_openpay( $sales='' ){
			if($sales==''){ return 0; }

			$s = "SELECT * from openpay_validate where sales_order like '$sales'";	
			$a = query( $s );
			if($a==null){ return 0; }

			return $a[0];
		}
		public function magento_data_openpay_update( $sales='',$str_json='', $vi= flase ){
			if($sales==''){ return false; }
			if($str_json==''){ return false; }

			$obj = json_decode($str_json);
			//print_r( $obj );

			if( $obj == null ){
				$this->control[ $sales ] = array( 
					'sales'		=> $sales, 
					'status'	=> 'ERROR',
					'metodo'	=> 'n/a',
					'date' 		=> 'n/a',
					'date_create' => 'n/a',
					'email' 	=> 'n/a',
				);
				return false;
			}

			if( isset( $obj->http_code ) ){
				if( $obj->http_code == '404' ){
					$this->control[ $sales ] = array( 
						'sales'		=> $sales, 
						'status'	=> 'sales_order_not_found',
						'metodo'	=> 'n/a',
						'date' 		=> 'n/a',
						'date_create' => 'n/a',
						'email' 	=> 'n/a',
					);
					return false;
				}
			}

			$fupdate = '';
			switch ( $obj->method ) {
				case 'card':			$fupdate = $obj->operation_date; break;
				case 'bank_account':	$fupdate = $obj->due_date; break;
				case 'store': 			$fupdate = $obj->due_date; break;
			}

			$status = 'in_progress';
			if( isset( $obj->status ) ){
				$status = $obj->status;
			}

			$reg = $this->is_exist_data_openpay( $sales );
			$id = 0;
			if( $reg ){ $id = $reg['id']; }

			/* obteniendo sales order */
			$s = "SELECT * from sales_flat_order where increment_id like '$sales'";
			$so = query( $s );
			if( $so!=null ){ $so = $so[0]; }
			//echo "\n sales_order ".$so['increment_id'].' ==> '.$so['customer_email'];

			/* si existe ==> update */
			if( $id ){
				//tt( "actualizando ".$sales );
				$s = "UPDATE openpay_validate set status = '$status', seguimiento = '$str_json' where id = $id";
				query($s);
				//if( $vi ){ echo "\nsql ==> ".$s; }

				$s = "SELECT * from sales_flat_order where increment_id like '$sales'";
				$osales = query( $s );
				//if( $vi ){ echo "\nsql ==> ".$s; }
				if( $osales==null ){ return false; }
				$osales = $osales[0];

				switch( $osales['status'] ){
					case 'canceled':
					case 'pagado': break;
					default:

						$this->control[ $sales ] = array( 
							'sales'		=> $sales, 
							'status'	=> $obj->status,
							'metodo'	=> $obj->method,
							'date' 		=> $fupdate,
							'date_create' => $obj->creation_date,
							'email' 	=> '',
						);

						if( $so!=null ){
							$this->control[ $sales ]['email'] = $so['customer_email'];
						}

						$d = explode('T', $fupdate);
						$d[1] = explode('-', $d[1]);

						$d = $d[0].' '.$d[1][0];
						$this->control[ $sales ]['date'] = $d;
						/*
						$dd = explode('T', $fupdate);
						$ddd = explode('-', $dd[1]);
						$d = $dd[0].' '.$ddd[0];
						*/
						$st = 'pending';
						if( $status == 'cancelled' ){ $st = 'canceled'; }
						if( $status == 'completed' ){ $st = 'pagado'; }

						$s = "UPDATE sales_flat_order set status = '$st', updated_at = '$d' where entity_id = ".$osales['entity_id'];
						query($s);
						//echo "\n$s";

						if( $vi ){ echo "\nsql ==> ".$s; }
						$s = "UPDATE sales_flat_order_grid set status = '$st', updated_at = '$d' where entity_id = ".$osales['entity_id'];
						query($s);

						//echo "\n$s";

						//if( $vi ){ echo "\nsql ==> ".$s; }
						break;
				}

				return true;
			}

			/* si no existe ==> crea */
			tt( "creando ".$sales );

			/* obteniendo email */
			$email = '';
			if( $so!=null ){ $email = $so['customer_email']; }

			$this->control[ $sales ] = array( 
				'sales'		=> $sales, 
				'status'	=> 'new',
				'metodo'	=> $obj->method,
				'date' 		=> $fupdate,
				'date_create' => $obj->creation_date,
				'email' 	=> $email,
			);

			$s = "INSERT into openpay_validate values( null, '$sales', '$email', '$status', '$str_json' )";
			$id = query($s);
			if( $id ){ return true; }

			return false;
		}

		public function report(){
			$this->data = null;
			$n = $this->list_sales_order();
			if( $n == 0 ){ return false; }

			$c = null;
			foreach ($this->data as $et => $r) {

				$s = "SELECT * from openpay_validate where sales_order like '".$r['increment_id']."'";
				$b = query( $s );
				if($b==null){ continue; }
				$b = $b[0]['seguimiento'];
				$obj = json_decode($b);

				/* nombre de usuario */
				$s = "SELECT value as name from customer_entity_varchar where entity_id = ".$r['customer_id']." and attribute_id IN( 5,7 )";
				$user = query($s);
				if( $user == null ){
					$user = '';
					if( isset( $obj->card->holder_name ) ){
						$user = $obj->card->holder_name;
					}
				}else{
					$user = $user[0]['name'].' '.$user[0]['name'];
				}

				$a = null;
				$st = 'Pendiente';
				if( $r['openpay_status'] == 'cancelled' ){ $st = 'Rechazada'; }
				if( $r['openpay_status'] == 'completed' ){ $st = 'Exitosa'; }

				$card = 'otra';		if( isset( $obj->card->brand ) ){ 		$card 		= $obj->card->brand; }
				$card_type = '';	if( isset( $obj->card->type ) ){ 		$card_type 	= $obj->card->type; }
				$monto = 0;			if( isset( $obj->amount ) ){ 			$monto 		= $obj->amount; }

				$error = 'waiting time expired';
				if( $st == 'Exitosa' ){ $error=''; }
				if( $st == 'Pendiente' ){ $error=''; }

									if( isset( $obj->error_message ) ){
										if( $obj->error_message != null ){
											$error = $obj->error_message;
										}
									}
				$date = '';			if( isset( $obj->operation_date ) ){ 	$date 		= $obj->operation_date; }
				$date_create = '';	if( isset( $obj->creation_date ) ){ 	$date_create= $obj->creation_date; }
				$method = '';		if( isset( $obj->method ) ){ 			$method 	= $obj->method; }

				$bank_name = '';
				$number = '';
				switch ( $method ) {
					case 'bank_account':
						if( isset( $obj->payment_method->bank ) ){
							$bank_name 	= $obj->payment_method->bank;
						}
						if( isset( $obj->payment_method->type ) ){
							$number 	= $obj->payment_method->type;
						}
						break;
					case 'store':
						$bank_name = 'store';
						if( isset( $obj->payment_method->type ) ){
							$number 	= $obj->payment_method->type;
						}
						break;
					case 'card':
						if( isset( $obj->card->bank_name ) ){
							$bank_name 	= $obj->card->bank_name;
						}
						if( isset( $obj->card->card_number ) ){
							$number 	= $obj->card->card_number;
						}
						break;
				}

				if( $st=='Pendiente' ){ $number = ''; }

				if( $method == 'bank_account' ){ $method = 'Transferencia Bancaria'; }
				if( $method == 'card' ){ $method = 'Tarjeta de Crédito'; }
				if( $method == 'store' ){ $method = 'Tienda de Conveniencia'; }
				if( $card_type == 'credit' ){ $card_type = 'Crédito'; }
				if( $card_type == 'debit' ){ $card_type = 'Débito'; }

				if( $date ){
					$b = explode('T', $date);
					$bc = explode('-', $b[1]);
					$date = $b[0].' '.$bc[0];
				}

				if( $date_create ){
					$b = explode('T', $date_create);
					$bc = explode('-', $b[1]);
					$date_create = $b[0].' '.$bc[0];
				}

				$a['employeeid'] 	= $r['customer_id'];
				$a['sales_order'] 	= $r['increment_id'];
				$a['description'] 	= PROGRAM.' - Compra Pago con '.$method;
				$a['status'] 		= $st;
				$a['name'] 			= $user;
				$a['name_card'] 	= ( isset( $obj->card->holder_name )?$obj->card->holder_name:'' );
				$a['email'] 		= $r['customer_email'];
				$a['card'] 			= $card;
				$a['monto'] 		= sprintf("%0.2f", $monto);
				$a['rechazo'] 		= $error;
				$a['type_card'] 	= $card_type;
				$a['bank'] 			= $bank_name;
				$a['number_card'] 	= $number;
				$a['date_a'] 		= $date;
				$a['date_b'] 		= $date;
				$a['type'] 			= $r['dinamic'];

				$a['telefono']		= $r['telefono'];
				$a['estado']		= $r['estado'];
				$a['delegacion']	= $r['delegacion'];
				$a['colonia']		= $r['colonia'];
				$a['calle']			= $r['calle'];
				$a['cp']			= $r['cp'];

				$c[] = $a;
			}

			$this->data = $c;

			log_data(OPENPAY_REPORT,'report_generated');

			return true;

			/*
			Employeeid			id user
			Idcanasta			id sales order
			Descripción			CPMN - Compra Pago con Tarjeta de Crédito
								CPMN - Compra Pago Transferencia Bancaria
								CPMN - Compra Pago en Tienda de Conveniencia
			Estatus				Rechazadas,Exitosas
			Nombre				nombre y apellido de usuario
			Email 				email usuario
			Marca 				visa,mastercard,amex,other
			Monto 				$monto
			Razón de rechazo 	
			Tipo de tarjeta 	Débito,Crédito
			Nombre del banco 	
			Número de tarjeta 	
			Fecha 				2018-04-12 11:22:00
			*/
		}

		public function report_cab(){
			$a = array(
				'Employeeid',
				'Idcanasta',
				'Descripción',
				'Estatus',
				'Nombre compra',
				'Nombre tarjeta',
				'Email',
				'Marca',
				'Monto',
				'Razón de rechazo',
				'Tipo de tarjeta',
				'Nombre del banco', 	
				'Número de tarjeta', 	
				'Fecha de creación',
				'Fecha de pago',
				'Promoción',
				'Teléfono',
				'Esatdo',
				'Delegacion/Municipio',
				'Colonia',
				'Calle',
				'Código Postal'
			);

			return $a;
		}

		public function report_name(){
			$s = PROGRAM.'-openpay-'.time().'.csv';

			return $s;
		}

		/*
		CREATE TABLE `nestle_me_114`.`openpay_validate` (
		  `id` INT NOT NULL AUTO_INCREMENT,
		  `sales_order` VARCHAR(45) NULL,
		  `email` VARCHAR(45) NULL,
		  `status` VARCHAR(64) NULL,
		  `seguimiento` TEXT NULL,
		  PRIMARY KEY (`id`))
		COMMENT = 'validaciones openpay';

		*/
	}
}
?>
