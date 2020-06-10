<?php

if( !defined('M_CUSTOMER_LIB') ){

	define('M_CUSTOMER_LIB','v0.2');

	class mCustomer{

		public $data = null;

		/*	lista todos los usuarios bloqueados
		 *		sec ==> obtiene los usuarios bloqueados de una seccion 
		 */
		public function list_user_blocked( $sec='' ){

			if( $sec!='' ){ $sec = " where code like '$sec' "; }

			$s = "SELECT 
				cb.*,
				cbi.code,
				cbi.descrip
				from customer_blocked as cb
				inner join customer_blocked_index as cbi on cbi.cbi = cb.cbindx
				$sec
				";
			$a = query( $s );
			if( $a==null ){
				$this->data = null;
				return 0;
			}

			$b = null;
			foreach ($a as $et => $r) {
				$b[ strtolower( $r['cemail'] ) ] = $r;
			}

			$this->data = $b;
			return count($b);
		}

		/* lista las secciones posibles para bloquear un usuario */
		public function list_secc(){
			$s = "SELECT * from customer_blocked_index";
			$a = query( $s );
			if( $a==null ){
				$this->data = null;
				return 0;
			}

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['code'] ] = $r;
			}

			$this->data = $b;
			return count($b);
		}

		/* filtra los datos con los usuarios bloqueados */
		public function filter_user( $d=null ){
			//echo "\n bloqueando usuarios\n";

			if( $d==null ){ return null; }

			$this->list_user_blocked( 'report' );
			//print_r( $this->data );

			foreach ($d as $et => $r) {
				$u = strtolower( $r['customer_email'] );
				if( $u!='' ){
					if( isset( $this->data[ $u ] ) ){
						unset( $d[ $et ] );
						echo ".";
					}
				}
			}

			return $d;
		}

		/* bloquea un usuario */
		public function user_block( $email='', $modo='' ){
			if( $email=='' ){ return false; }

			//if( $this->is_user_blocked( $email, $modo ) ){ return true; }

			$this->list_secc();

			if( !isset( $this->data[ $modo ] ) ){
				return false;
			}

			$modo_id = $this->data[ $modo ]['cbi'];

			$fecha = date('Y-m-d G:i:s', time() );
			$s = "INSERT into customer_blocked( id, cid, cemail, cbindx, fecha ) values( null, 0, '$email', $modo_id, '$fecha' )";
			//echo "\n $s";
			$id = query( $s );
			if( $id==null ){ return false; }

			return true;
		}
		/* desbloquea un usuario */
		public function user_unblock( $email='', $modo='' ){
			if( $email=='' ){ return false; }

			$id_block = $this->is_user_blocked( $email, $modo );
			//echo "\n user_block ==> [$id_block]";

			if( $id_block==0 ){ return true; }

			$fecha = date('Y-m-d G:i:s', time() );
			$s = "UPDATE customer_blocked set cbindx = 0, fecha = '$fecha' where id = $id_block; ";
			//echo "\n $s\n";
			query( $s );

			return true;
		}

		public function is_user_blocked( $email='', $modo='' ){
			if( $email=='' ){ return 0; }

			if( $modo == '' ){
				$s = "SELECT * from customer_blocked where cemail like '$email'";
				//echo "\n $s\n";
				$a = query( $s );
				if( $a==null ){ return 0; }
				return true;
			}

			$this->list_secc();
			if( !isset( $this->data[ $modo ] ) ){
				return 0;
			}

			$modo_id = $this->data[ $modo ]['cbi'];
			//print_r( $modo_id );

			$s = "SELECT * from customer_blocked where cemail like '$email' and cbindx = $modo_id";
			//echo "\n $s\n";
			$a = query( $s );
			//print_r( $a );
			if( $a==null ){ return 0; }
			return $a[0]['id'];
		}

	}

	/*

		SELECT 
			cb.cemail
			from customer_blocked as cb
			inner join customer_blocked_index as cbi on cbi.cbi = cb.cbindx
			where code like 'report'

		SELECT
			sfq.entity_id,
			sfq.created_at,
			sfq.updated_at,
			sfq.customer_email,
			sfq.customer_firstname,
			sfq.customer_lastname,
			sfq.reserved_order_id,

			sfq.items_count,
			sfq.grand_total,
			sfq.subtotal,
			sfqi.item_id,
			sfqi.product_id,
			sfqi.parent_item_id,
			sfqi.sku,
			sfqi.name,
			sfqi.qty,
			sfqi.price,
			sfqi.tax_amount,
			sfqi.row_total,
			sfqi.row_total_incl_tax

			from sales_flat_quote as sfq
			inner join sales_flat_quote_item as sfqi on sfqi.quote_id = sfq.entity_id

			where

			sfq.entity_id NOT IN( select quote_id from sales_flat_order where status IN ( 'pagado', 'pending' ) )
			and sfq.customer_email NOT IN (
				SELECT 
				cb.cemail
				from customer_blocked as cb
				inner join customer_blocked_index as cbi on cbi.cbi = cb.cbindx
				where code like 'report'
			)

			order by sfq.created_at ASC







		SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
		SET time_zone = "+00:00";

		CREATE TABLE `customer_blocked` (
		  `id` int(11) NOT NULL,
		  `cid` int(11) NOT NULL,
		  `cemail` varchar(255) DEFAULT NULL,
		  `cbindx` int(11) NOT NULL,
		  `fecha` datetime NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='usuarios bloqueados';

		ALTER TABLE `customer_blocked`  ADD PRIMARY KEY (`id`);
		ALTER TABLE `customer_blocked` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

		INSERT INTO `customer_blocked` (`id`, `cid`, `cemail`, `cbindx`, `fecha`) VALUES
		(null, 0, 'Erik.Abarca1@mx.nestle.com', 3, '2019-07-17 13:53:00'),
		(null, 0, 'Diana.Guerra@mx.nestle.com', 3, '2019-07-17 13:53:00'),
		(null, 0, 'Tania.Guerra@n-agencias.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'Karla.Ballanes@mx.nestle.com', 3, '2019-07-17 13:53:00'),
		(null, 0, 'Jesus.Angulo@MX.nestle.com', 3, '2019-07-17 13:53:00'),
		(null, 0, 'Citlalli.Miranda@MX.nestle.com', 3, '2019-07-17 13:53:00'),
		(null, 0, 'avaldez@mlg.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'jgrimaldo@mlg.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'ssotelo@mlg.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'ymurillo@mlg.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'acavazos@mlg.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'rcesar@mlg.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'mreyes@mlg.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'mpineda@mlg.com.mx', 3, '2019-07-17 13:53:00'),
		(null, 0, 'ecastaneda@mlg.com.mx', 3, '2019-07-17 13:53:00');

		(15, 0, 'rmorales@mlg.com.mx', 3, '2019-07-17 13:53:00'),

		CREATE TABLE `customer_blocked_index` (
		  `cbi` int(11) NOT NULL,
		  `code` varchar(128) DEFAULT NULL,
		  `descrip` varchar(255) DEFAULT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		ALTER TABLE `customer_blocked_index` ADD PRIMARY KEY (`cbi`);
		ALTER TABLE `customer_blocked` MODIFY `cbi` int(11) NOT NULL AUTO_INCREMENT;

		INSERT INTO `customer_blocked_index` (`cbi`, `code`, `descrip`) VALUES
		(1, 'frontend', 'bloquear en el front-end'),
		(2, 'backend', 'bloquear en el back-end'),
		(3, 'report', 'excluir de los listados de los reportes');

	*/
}
?>