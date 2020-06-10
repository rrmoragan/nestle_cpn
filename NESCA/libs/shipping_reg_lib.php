<?php

if( !defined( 'SHIPPING_REG' ) ){
	include( 'basics.php' );
	include( 'querys.php' );
	include( 'logs.php' );

	define('SHIPPING_REG','1');

	class Shipping_Reg{

		public $err_msg = '';
		public $uid = 0;
		public $data_update = null;

		public function shipping_update($dat=null){
			log_data( 'log/shipping_reg', 'shipping_update()' );

			$this->uid = 0;
			$this->err_msg = 'Sin datos a actualizar';

			if( $dat==null ){ return false; }

			if( !$this->data_update_valid( $dat ) ){
				//log_data( 'log/shipping', 'se requieren datos' );
				return false;
			}

			if( !$this->data_update() ){
				$this->$err_msg = 'Error en base de datos';
				return false;
			}

			return true;
		}

		private function data_update_valid($d=null){
			if($d==null){ return false; }

			$err = 0;

			/* quitando espacios en blanco */
			foreach ($d as $et => $r) {
				$d[ $et ] = trim( $r );
			}

			/* quitamos datos en blanco */
			foreach ($d as $et => $r) {
				if( $r == '' ){ unset( $d[$et] ); }
			}

			/* validando existencia de variables obligatorias */
			if( !isset( $d['uid'] ) ){ $err++; }
			if( !isset( $d['add_id'] ) ){ $err++; }
			if( !isset( $d['add_nom'] ) ){ $err++; }
			if( !isset( $d['add_apell'] ) ){ $err++; }
			if( !isset( $d['add_calle'] ) ){ $err++; }
			if( !isset( $d['add_num'] ) ){ $err++; }
			if( !isset( $d['add_cp'] ) ){ $err++; }	
			if( !isset( $d['add_col'] ) ){ $err++; }
			if( !isset( $d['add_deleg'] ) ){ $err++; }
			if( !isset( $d['add_tel'] ) ){ $err++; }
			if( !isset( $d['add_email'] ) ){ $err++; }

			if( !isset( $d['add_pais'] ) ){ 	$d['add_pais'] = 'MX'; }
			if( !isset( $d['add_estado'] ) ){ 	$d['add_estado'] = '485'; }

			if( $err>0 ){ return false; }

			/* almacenando valores por default */
			if( !isset( $d['add_num2'] ) ){ 	$d['add_num2'] = 0; }
			if( !isset( $d['add_tel2'] ) ){ 	$d['add_tel2'] = ''; }
			if( !isset( $d['add_person'] ) ){ 	$d['add_person'] = ''; }
			if( !isset( $d['add_ref'] ) ){ 		$d['add_ref'] = ''; }

			$this->data_update = $d;
			$this->uid = $d['uid'];

			return true;
		}

		private function data_update(){
			log_data( 'log/shipping_reg', 'data_update()' );

			if( $this->data_update == null ){ return false; }

			// obtiene las direcciones del usuario
			$dir = $this->direccions();
			//log_data( 'log/shipping_reg', print_r( $dir, true ) );

			if( $this->direccions_update( $dir[0] ) ){
				return true;
			}

			return false;
		}

		public function direccions(){
			log_data( 'log/shipping_reg', 'direccions()' );

			if( $this->uid == 0 ){ return null; }

			$uid = $this->uid;
			$s = "SELECT * FROM customer_address_entity where is_active = 1 and parent_id = $uid order by entity_id ASC ;";
			$a = query($s);

			$this->direccions_repair( query($s) );

			return $a;
		}

		// repara las inconsistencias en las direcciones
			private function direccions_repair( $a=null ){
				log_data( 'log/shipping_reg', 'direccions_repair()' );

				if( $a==null ){	return false; }
				
				$data = $this->select_direccions( $a );

				foreach ($data as $et => $r) {
					$data[ $et ]['struct'] = 1;
				}

				foreach ($data as $et => $r) {
					if( isset( $data[ $et ]['city'] ) ){
						if( $data[ $et ]['city'] == '.....' ){ $data[ $et ]['struct'] = 0; }
					}
					if( isset( $data[ $et ]['company'] ) ){
						if( $data[ $et ]['company'] == 'Por favor, ingrese una empresa o raz�n social' ){ $data[ $et ]['struct'] = false; }
					}
					if( isset( $data[ $et ]['postcode'] ) ){
						if( $data[ $et ]['postcode'] == '99999' ){ $data[ $et ]['struct'] = 0; }
					}
					if( isset( $data[ $et ]['telephone'] ) ){
						if( $data[ $et ]['telephone'] == '000000000' ){ $data[ $et ]['struct'] = 0; }
					}
					if( isset( $data[ $et ]['street'] ) ){
						if( $data[ $et ]['street'] == '.....' ){ $data[ $et ]['struct'] = 0; }
					}
				}

				$repair = null;
				$viable = null;
				foreach ($data as $et => $r) {
					if( $r['struct']==1 ){
						$viable[ $et ] = $r;
					}else{
						$repair[ $et ] = $r;
					}
				}

				$this->repair_direccion_customer( $viable, $repair );

				return true;
			}
		// modifica los datos de una direccion por otra
			private function repair_direccion_customer( $dir_orig=null, $dir_dest=null ){
				if( $dir_dest==null ){ return false; }
				if( $dir_orig==null ){ return false; }

				$a = array(
					'company' 	=> array( 'attrib' 	=> 'company', 	'data_error' => 'Por favor, ingrese una empresa o raz�n social' ),
					'city' 		=> array( 'attrib' 	=> 'city', 		'data_error' => '.....' ),
					'postcode' 	=> array( 'attrib' 	=> 'postcode', 	'data_error' => '99999' ),
					'telephone' => array( 'attrib' 	=> 'telephone', 'data_error' => '000000000' ),
					'street' 	=> array( 'attrib' 	=> 'street', 	'data_error' => '.....' ),
				);

				$s = "SELECT attribute_id,entity_type_id,attribute_code,backend_type from eav_attribute where entity_type_id=2";
				$b = query( $s );
				$attrib = null;
				foreach ($b as $et => $r) {
					$attrib[ $r['attribute_code'] ] = $r;
				}
				unset( $b );

				foreach ($a as $et => $r) {
					foreach ($dir_orig as $etr => $rr) {
						if( !isset( $rr[ $r['attrib'] ] ) ){ continue; }
						foreach ($dir_dest as $etrr => $rrr) {
							if( !isset( $rrr[ $r['attrib'] ] ) ){ continue; }

							if( $rrr[ $r['attrib'] ] == $r['data_error'] )
								$this->update_customer_direccion( $etrr, $r['attrib'], $rr[ $r['attrib'] ], $attrib );
						}
					}
				}

				return true;
			}
		// actualiza un registro de una direccion de usuario
			private function update_customer_direccion( $did=0,$attrib='',$val=null, $tables=null ){
				if( $did==0 ){ return false; }
				if( $attrib=='' ){ return false; }
				if( $tables==null ){ return false; }

				$tb = '';
				if( isset( $tables[ $attrib ]['backend_type'] ) ){
					$tb = 'customer_address_entity_'.$tables[ $attrib ]['backend_type'];
				}

				if( $tb=='' ){ return false; }

				log_data( 'log/shipping_reg', "actualizando direccion de usuario ==> $did ==> campo ==> $attrib ==> valor ==> [$val] ==> table ==> $tb" );

				$attribute_id = $tables[ $attrib ]['attribute_id'];

				$s = "SELECT * from $tb where entity_id = $did and attribute_id = $attribute_id";
				$s = "SELECT value_id from $tb where entity_id = $did and attribute_id = $attribute_id";
				$id = query( $s );
				if( $id == null ){ return false; }

				$id = $id[0]['value_id'];

				$s = "UPDATE $tb set value = '$val' where value_id = $id";
				log_data( 'log/shipping_reg', "$s \n" );
				query( $s );

				return true;
			}
		// obtiene las direcciones solicitadas por el customer_address_entity ==> entity_id
			public function select_direccions( $a=null ){
				log_data( 'log/shipping_reg', 'select_direccions()' );
				if( $a==null ){ return false; }

				$b = null;
				foreach ($a as $et => $r) {
					$b[ $r['entity_id'] ] = $this->select_dir( $r['entity_id'] );
				}

				//log_data( 'log/shipping_reg', print_r( $b,true ) );

				return $b;
			}
		// obtiene una direccion por el customer_address_entity ==> entity_id
			public function select_dir( $uaid=0 ){
				log_data( 'log/shipping_reg', "select_dir( $uaid )" );
				if( $uaid==0 ){ return false; }

				$s ="SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_varchar as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $uaid

			        union

					SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_text  as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $uaid
			        
			        union

					SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_int  as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $uaid
			        
			        union
					
					SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_decimal  as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $uaid
			        
			        union
					
					SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_datetime  as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $uaid";

			    $a = query( $s );
			    if( $a==null ){ return null; }

			    //log_data( 'log/shipping_reg', print_r( $a,true ) );

			    $b = null;
			    foreach ($a as $et => $r) {
			    	//log_data( 'log/shipping_reg', print_r( $r,true ) );
			    	//log_data( 'log/shipping_reg', $r['attribute_code']." ==> ".$r['value'] );
			    	$b[ $r['attribute_code'] ] = $r['value'];
			    }

			    //log_data( 'log/shipping_reg', print_r( $b,true ) );

			    return $b;
			}

		private function direccions_update( $d=null ){
			//log_data( 'log/shipping_reg', 'direccions_update()' );

			if($d==null){ return false; }
			if($this->data_update==null){ return false; }

			$id = $d['entity_id'];

			/*
				$s ="SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_varchar as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $id

			        union

					SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_text  as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $id
			        
			        union

					SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_int  as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $id
			        
			        union
					
					SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_decimal  as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $id
			        
			        union
					
					SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
					FROM customer_address_entity_datetime  as cae
			        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
			        where cae.entity_id = $id;";

			    //log_data( 'log/shipping_reg', "sql ==> $s" );
		        $a = query( $s );*/

	        $a = $this->select_dir( $d['entity_id'] );

	        if( $a==null ){
	        	$this->err_msg = 'error en consulta';
	        	return false;
	        }

	        $b = null;
	        foreach ($a as $et => $r) {
	        	$b[ $r['attribute_code'] ] = $r;
	        }
	        $a = $b; unset( $b );

	        //log_data( 'log/shipping_reg', print_r( $a,true ) );

		    $r = $this->relation();

		    foreach ($this->relation() as $et => $r) {
		    	$r['crtl'] = $et;

		    	if( isset( $a[ $r['attrib'] ] ) ){
		    		$this->update_direction_data( $r, $a[ $r['attrib'] ] );
		    	}else{
		    		$this->create_direction_data( $r, $id );
		    	}
		    }

		    /* cambios en sales_flat_quote_address */

		    $email = $this->data_update['add_email'];
		    $s = "SELECT * FROM sales_flat_quote_address where email like '$email' order by address_id DESC;";

		    //log_data( 'log/shipping_reg', "sql ==> $s" );
		    $sfqa = query($s);
		    if( $sfqa!=null ){
		    	//log_data( 'log/shipping_reg', print_r( $sfqa,true ) );

		    	foreach ($sfqa as $et => $r) {
		    		$this->update_sfqa( $r );
		    	}
		    }

		    return true;
		}

		public function relation(){
			/* los atributos se encuentran listados en la tabla eav_attribute where entity_type_id=2 */
			$a = array(
			    'add_nom' 		=> array( 'id'=>20,  'attrib' => 'firstname', 			'table' => 'customer_address_entity_varchar' ),
			    'add_apell' 	=> array( 'id'=>22,  'attrib' => 'lastname', 			'table' => 'customer_address_entity_varchar' ),
			    'add_calle' 	=> array( 'id'=>25,  'attrib' => 'street', 				'table' => 'customer_address_entity_text' ),
				'add_num' 		=> array( 'id'=>251, 'attrib' => 'num_ext', 			'table' => 'customer_address_entity_varchar' ),
				'add_num2' 		=> array( 'id'=>252, 'attrib' => 'num_int', 			'table' => 'customer_address_entity_varchar' ),
			    'add_cp' 		=> array( 'id'=>30,  'attrib' => 'postcode', 			'table' => 'customer_address_entity_varchar' ),
			    'add_pais' 		=> array( 'id'=>27,  'attrib' => 'country_id', 			'table' => 'customer_address_entity_varchar' ),
			    'add_col' 		=> array( 'id'=>173, 'attrib' => 'neighborhood', 		'table' => 'customer_address_entity_varchar' ),
			    'add_deleg' 	=> array( 'id'=>26,  'attrib' => 'city', 				'table' => 'customer_address_entity_varchar' ),
			    'add_estado' 	=> array( 'id'=>29,  'attrib' => 'region_id', 			'table' => 'customer_address_entity_int' ),
			    'add_tel' 		=> array( 'id'=>31,  'attrib' => 'telephone', 			'table' => 'customer_address_entity_varchar' ),
			    'add_tel2' 		=> array( 'id'=>216, 'attrib' => 'telephone_extension', 'table' => 'customer_address_entity_varchar' ),
			    'add_person' 	=> array( 'id'=>217, 'attrib' => 'authorized_person', 	'table' => 'customer_address_entity_varchar' ),
			    'add_ref' 		=> array( 'id'=>178, 'attrib' => 'adress_references', 	'table' => 'customer_address_entity_varchar' ),
			);

			return $a;
		}

		private function create_direction_data($reg='',$uid=0){
			if($reg==''){ return false; }
			if($uid==0){ return false; }

			////log_data( 'log/shipping', 'INSERT => '.print_r($reg,true) );

			$table = $reg['table'];
			$at_id = $reg['id'];
			$val = $this->data_update[ $reg['crtl'] ];

			$s = "INSERT into $table values( null, 2, $at_id, $uid, '$val' ) ";
			//log_data( 'log/shipping', $reg['crtl'].' -- INSERT => '.$s );

			query($s);

			return $s;
		}

		private function update_direction_data($reg='',$d=null){
			if($d==null){ return false; }
			if($reg==''){ return false; }

			////log_data( 'log/shipping', 'UPDATE => '.print_r($reg,true).' -- '.print_r($d,true) );
			////log_data( 'log/shipping', 'UPDATE => '.$reg['crtl'] );
			////log_data( 'log/shipping', 'UPDATE => '.$this->data_update[ $reg['crtl'] ] );

			$table = $reg['table'];
			$val = $this->data_update[ $reg['crtl'] ];
			$id = $d['value_id'];

			$s = "UPDATE $table set value='$val' where value_id = $id";
			//log_data( 'log/shipping', 'UPDATE => '.$s );

			query($s);

			return $s;
		}

		private function update_sfqa($reg=null){
			if($reg==null){ return false; }

			$id = $reg['address_id'];

			$s = "UPDATE sales_flat_quote_address set street =    '".$this->data_update['add_calle']."' where address_id = ".$id;
			//echo "\n\n$s";
			query($s);
			$s = "UPDATE sales_flat_quote_address set city =      '".$this->data_update['add_deleg']."' where address_id = ".$id;
			//echo "\n\n$s";
			query($s);
			$s = "UPDATE sales_flat_quote_address set telephone = '".$this->data_update['add_tel']."' where address_id = ".$id;
			//echo "\n\n$s";
			query($s);
			$s = "UPDATE sales_flat_quote_address set postcode =  '".$this->data_update['add_cp']."' where address_id = ".$id;
			//echo "\n\n$s";
			query($s);

			return true;
		}
	}
}

?>