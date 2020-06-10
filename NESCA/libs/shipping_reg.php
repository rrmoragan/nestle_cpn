<?php

if( !defined( 'SHIPPING_REG' ) ){
	include( 'basics.php' );
	include( 'querys.php' );
	//include( 'logs.php' );

	define('SHIPPING_REG','1');

	class Shipping_Reg{

		public $err_msg = '';
		public $uid = 0;
		public $data_update = null;

		public function shipping_update($dat=null){
			//log_data( 'log/shipping', 'shipping_update()' );

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
			if( $this->data_update == null ){ return false; }

			$dir = $this->direccions();

			if( $this->direccions_update( $dir[0] ) ){
				return true;
			}

			return false;
		}

		public function direccions(){
			if( $this->uid == 0 ){ return null; }

			$s = "SELECT * FROM customer_address_entity where is_active = 1 and parent_id = ".$this->uid." order by entity_id ASC ;";
			$a = query($s);

			return $a;
		}

		private function direccions_update( $d=null ){
			if($d==null){ return false; }
			if($this->data_update==null){ return false; }

			$id = $d['entity_id'];

			$s ="SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
				FROM customer_address_entity_varchar as cae
		        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
		        where cae.entity_id = $id

		        union

				SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
				FROM customer_address_entity_text  as cae
		        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
		        where entity_id = $id
		        
		        union

				SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
				FROM customer_address_entity_int  as cae
		        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
		        where entity_id = $id
		        
		        union
				
				SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
				FROM customer_address_entity_decimal  as cae
		        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
		        where entity_id = $id
		        
		        union
				
				SELECT cae.*, eav.attribute_code, eav.frontend_label,eav.is_required
				FROM customer_address_entity_datetime  as cae
		        inner join eav_attribute as eav on eav.attribute_id = cae.attribute_id
		        where entity_id = $id;";

	        $a = query( $s );

	        if( $a==null ){ $this->err_msg = 'error en consulta'; return false; }

	        $b = null;
	        foreach ($a as $et => $r) {
	        	$b[ $r['attribute_code'] ] = $r;
	        }
	        $a = $b; unset( $b );

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

		    $s = "SELECT * FROM sales_flat_quote_address where email like '".$this->data_update['add_email']."' order by address_id DESC;";
		    //log_data( 'log/shipping', $s );
		    //echo "\n\n$s";

		    $sfqa = query($s);
		    if( $sfqa!=null ){
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