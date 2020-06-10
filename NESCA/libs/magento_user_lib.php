<?php
/*
 * libreria utilizada para ver y modificar todo lo relacionado con los productos
 */
if( !defined('MAGENTO_LIB_USER') ){

	define('MAGENTO_LIB_USER','v1.4');
	define('MAGENTO_LIB_USER_UPDATE','2019-09-25');

	class mUser{
		public $data = null;

		/* regresa los datos de un usuario por su id */
		public function user_id( $id=0 ){
			if($id==0){ return null; }

			$s = "SELECT entity_id,is_active from customer_entity where entity_id like '$id'";
			$u = query($s);

			if( $u == null ){ return null; }
			$u = $u[0];

			if( $u['is_active'] == 0 ){ return null; }

			$u = $this->user_data( $id );

			return $u;
		}
		/* regresa los datos de un usuario por su email */
		public function user_email($s=''){
			if($s==''){ return null; }
			$s = "SELECT entity_id,is_active from customer_entity where email like '$s'";
			$u = query($s);

			if( $u == null ){ return null; }
			$u = $u[0];

			if( $u['is_active'] == 0 ){ return null; }

			$u = $this->user_data( $u['entity_id'] );

			return $u;
		}
		/* regresa los datos de un usario */
		public function user_data($uid=0){
			if( $uid==0 ){ return null; }

			$s = "SELECT entity_id,email,created_at,updated_at,is_active from customer_entity where entity_id like '$uid'";
			$u = query($s);
			if( $u==null ){ return null; }
			$u = $u[0];

			foreach ($u as $et => $r) {
				$d[ $et ] = $r;
			}

			$s = "SELECT ced.value_id,ea.attribute_code,ced.value,'varchar' as 'data_table' 
				from customer_entity_varchar as ced
				inner join eav_attribute as ea on ea.attribute_id = ced.attribute_id
				where entity_id = $uid
				union
				SELECT ced.value_id,ea.attribute_code,ced.value,'datetime' as 'data_table' 
				from customer_entity_datetime  as ced
				inner join eav_attribute as ea on ea.attribute_id = ced.attribute_id
				where entity_id = $uid
				union
				SELECT ced.value_id,ea.attribute_code,ced.value,'decimel' as 'data_table' 
				from customer_entity_decimal  as ced
				inner join eav_attribute as ea on ea.attribute_id = ced.attribute_id
				where entity_id = $uid
				union
				SELECT ced.value_id,ea.attribute_code,ced.value,'int' as 'data_table' 
				from customer_entity_int  as ced
				inner join eav_attribute as ea on ea.attribute_id = ced.attribute_id
				where entity_id = $uid
				union
				SELECT ced.value_id,ea.attribute_code,ced.value,'text' as 'data_table' 
				from customer_entity_text  as ced
				inner join eav_attribute as ea on ea.attribute_id = ced.attribute_id
				where entity_id = $uid";
			$a = query($s);
			if( $a==null ){ return null; }

			foreach ($a as $et => $r) {
				if( $r['attribute_code'] == 'password_hash' ) continue;
				$d[ $r['attribute_code'] ] = $r['value'];
			}

			return $d;
		}
		/* obtiene un registro de direccion */
		public function user_address_id( $uaid='', $v=false ){
			if( $uaid=='' ){
				if( $v ){ echo "\n user_address_id ==> null"; }
				return null;
			}

			$s = "SELECT caev.*, ea.attribute_code, \"customer_address_entity_varchar\" as ltable from customer_address_entity_datetime as caev ".
			" inner join eav_attribute as ea on ea.attribute_id=caev.attribute_id ".
			" where entity_id = $uaid ".
			" union ".
			" SELECT caev.*, ea.attribute_code, \"customer_address_entity_decimal\" as ltable from customer_address_entity_decimal as caev ".
			" inner join eav_attribute as ea on ea.attribute_id=caev.attribute_id ".
			" where entity_id = $uaid ".
			" union ".
			" SELECT caev.*, ea.attribute_code, \"customer_address_entity_int\" as ltable from customer_address_entity_int as caev ".
			" inner join eav_attribute as ea on ea.attribute_id=caev.attribute_id ".
			" where entity_id = $uaid ".
			" union ".
			" SELECT caev.*, ea.attribute_code, \"customer_address_entity_text\" as ltable from customer_address_entity_text as caev ".
			" inner join eav_attribute as ea on ea.attribute_id=caev.attribute_id ".
			" where entity_id = $uaid ".
			" union ".
			" SELECT caev.*, ea.attribute_code, \"customer_address_entity_varchar\" as ltable from customer_address_entity_varchar as caev ".
			" inner join eav_attribute as ea on ea.attribute_id=caev.attribute_id ".
			" where entity_id = $uaid";

			if( $v ){ echo "\n sql ==> ".$s; }
			$a = query( $s );
			if( $a==null ){
				if( $v ){ echo "\n no data"; }
				return null;
			}

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['attribute_code'] ]['ltable'] = $r['ltable'];
				$b[ $r['attribute_code'] ]['attribute_code'] = $r['attribute_code'];
				$b[ $r['attribute_code'] ]['value'] = $r['value'];
				$b[ $r['attribute_code'] ]['value_id'] = $r['value_id'];
			}

			if( $v ){ print_r( $b ); }
			return $b;
		}
		/* lista todas las direcciones relacionadas con el usuario usuario */
		public function user_list_all_address( $user_id=0 ){
			//echo "\n user_list_all_address()";

			if( $user_id==0 ){
				$this->data = null;
				return 0;
			}

			$s = "SELECT entity_id from customer_entity where entity_id = $user_id";
			$a = query( $s );
			if( $a==null ){
				$this->data = null;
				return 0;
			}

			$id = $a[0]['entity_id'];
			//echo "\n user id ==> $id";

			$s = "SELECT entity_id from customer_address_entity where parent_id =$id";
			$a = query( $s );
			if( $a==null ){
				$this->data = null;
				return 0;
			}

			$c = $a;
			//echo "\n user_list_all_address ==> ".print_r($c,true);
			$d = null;

			foreach ($c as $et => $r) {
				$id = $r['entity_id'];

				$s = "SELECT data.*, ea.attribute_code, \"datetime\" as ltable from customer_address_entity_datetime as data 
					inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
					where entity_id = $id
					union
					SELECT data.*, ea.attribute_code, \"decimal\" as ltable from customer_address_entity_decimal  as data
					inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
					where entity_id = $id
					union
					SELECT data.*, ea.attribute_code, \"int\" as ltable from customer_address_entity_int  as data
					inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
					where entity_id = $id
					union
					SELECT data.*, ea.attribute_code, \"text\" as ltable from customer_address_entity_text  as data
					inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
					where entity_id = $id
					union
					SELECT data.*, ea.attribute_code, \"varchar\" as ltable from customer_address_entity_varchar as data
					inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
					where data.entity_id = $id
					";
				$a = query( $s );
				if( $a==null ){
					$this->data = null;
					return 0;
				}

				$b = null;
				foreach ($a as $et => $r) {
					$b[ $r['entity_id'] ][ $r['attribute_code'] ] = array(
						'entity_id' 		=> $r['entity_id'],
						'value_id' 			=> $r['value_id'],
						'attribute_code' 	=> $r['attribute_code'],
						'value' 			=> $r['value'],
						'ltable' 			=> $r['ltable']
					);
				}

				foreach ($b as $et => $r) {
					$d[ $et ] = $r;
				}
			}

			$this->data = $d;
			return count( $this->data );
		}

	}
}
/*
show tables;
show tables where Tables_in_magento1 like '%customer%';

select * from customer_entity where entity_id = 639;
select entity_id from customer_entity where entity_id = 639;

select * from customer_address_entity where parent_id = ( select entity_id from customer_entity where entity_id = 639 );
+-----------+----------------+------------------+--------------+-----------+---------------------+---------------------+-----------+------------+
| entity_id | entity_type_id | attribute_set_id | increment_id | parent_id | created_at          | updated_at          | is_active | is_billing |
+-----------+----------------+------------------+--------------+-----------+---------------------+---------------------+-----------+------------+
|      1112 |              2 |                0 | NULL         |       639 | 2019-08-30 12:52:17 | 2019-09-17 23:31:59 |         1 |          0 |
|      1116 |              2 |                0 | NULL         |       639 | 2019-09-04 13:46:49 | 2019-09-17 23:31:59 |         1 |          0 |
+-----------+----------------+------------------+--------------+-----------+---------------------+---------------------+-----------+------------+

select data.*, ea.attribute_code, "datetime" as ltable from customer_address_entity_datetime as data 
inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
where entity_id = 1116
union
select data.*, ea.attribute_code, "decimal" as ltable from customer_address_entity_decimal  as data
inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
where entity_id = 1116
union
select data.*, ea.attribute_code, "int" as ltable from customer_address_entity_int  as data
inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
where entity_id = 1116
union
select data.*, ea.attribute_code, "text" as ltable from customer_address_entity_text  as data
inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
where entity_id = 1116
union
select data.*, ea.attribute_code, "varchar" as ltable from customer_address_entity_varchar as data
inner join eav_attribute as ea on ea.attribute_id = data.attribute_id
where data.entity_id = 1116



*/
?>