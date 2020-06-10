<?php

include('basics.php');
include('querys.php');
include('magento_product_lib.php');
include('magento_customer_lib.php');

define('DIA',86400);

class mCars{

	public $data = null;
	public $car_list_filtro = null;
	public $items = null;
	public $stock = true;
	public $list_stock = null;

	public $car_clean_time = 1;
	private $clean_time = 86400;

	// lista todos los carritos
		public function cars_list(){

			$filtro = $this->cars_list_get_filtro();
			if( $filtro != '' ){ $filtro = "where $filtro"; }

			$s = "SELECT * from sales_flat_quote $filtro";
			//echo "\n sql ==> $s";
			$a = query( $s );
			if( $a == null ){
				echo "\n consulta nula";
				$this->data = null;
				return 0;
			}

			$s = "DESCRIBE sales_flat_quote";
			$b = query( $s );
			if( $b==null ){
				echo "\n consulta nula";
				$this->data = null;
				return 0;
			}

			$this->data['struct'] = $b;
			$this->data['data'] = $a;
			return count( $this->data['data'] );
		}
	// agrega un filtro al listado de los carritos
		public function cars_list_filtro( $filtro='', $dato='' ){
			if( $filtro=='' ){ return false; }

			if( $dato == '' ){
				$this->car_list_filtro[] = $filtro;
			}else{
				$this->car_list_filtro[] = "$filtro::::$dato";
			}

			return true;
		}
	// obtiene los filtros agregados para el listado de los carritos
		public function cars_list_get_filtro(){
			if( $this->car_list_filtro == null ){
				return '';
			}

			$s = '';
			foreach ($this->car_list_filtro as $et => $r) {

				$a = explode('::::', $r);
				echo "\n filtro ==> ".$a[0].( isset( $a[1] )?( " ==> data ==> ".$a[1] ):'' );
				switch ($a[0]) {
					case 'status_active':
						if( $s!='' ){ $s = $s." and "; }
						$s = $s.'is_active=1 ';
						break;
					case 'status_inactive':
						if( $s!='' ){ $s = $s." and "; }
						$s = $s.'is_active=0 ';
						break;
					case 'fecha_fin':
						$val = $this->cars_max_regs( 'updated_at', $a[1] );

						if( $s!='' ){ $s = $s." and "; }
						$s = $s."updated_at <= '".$val."'";
						break;
					case 'user_null':
						if( $s!='' ){ $s = $s." and "; }
						$s = $s."customer_id IS NULL";
						break;
					case 'user_not_null':
						if( $s!='' ){ $s = $s." and "; }
						$s = $s."(customer_id IS NOT NULL OR customer_email IS NOT NULL)";
						break;
					case 'user_not_null':
						if( $s!='' ){ $s = $s." and "; }
						$s = $s."(customer_id IS NOT NULL OR customer_email IS NOT NULL)";
						break;
				}
			}

			return $s;
		}
	// obtiene el valor maximo de una columa
		public function cars_max_regs( $campo='', $rango=null ){
			if( $campo=='' ){ return ''; }

			$s = "SELECT $campo as data from sales_flat_quote where $campo like '%$rango%' order by $campo DESC limit 0,1;";

			if( $campo == 'updated_at' || $campo == 'created_at' ){
				$s = "SELECT $campo as data from sales_flat_quote where cast($campo as date) <= '$rango' order by $campo DESC limit 0,1";
			}

			$a = query( $s );
			if( $a==null ){ return ''; }

			return $a[0]['data'];
		}
	// obtiene los items de un carrito
		public function car_items( $id_car=0 ){
			if( $id_car==0 ){ return null; }

			$this->data = null;

			$s = "SELECT * from sales_flat_quote_item where quote_id = $id_car";
			//echo "\n sql ==> $s";
			$a = query( $s );
			if( $a == null ){
				return 0;
			}

			$items = new mProduct();
			foreach ($a as $et => $r) {
				$a[ $et ]['codigo_barras'] = '';

				if( isset( $this->items[ $r['product_id'] ] ) ){
					$a[ $et ]['codigo_barras'] = $this->items[ $r['product_id'] ];
					continue;
				}

				$n = $items->product_id( $r['product_id'] );
				if( $n == 0 ){ continue; }

				$this->items[ $r['product_id'] ] = $items->data['codigo_barras']['value'];
				$a[ $et ]['codigo_barras'] = $this->items[ $r['product_id'] ];
			}

			$this->data = $a;
			return count( $this->data );
		}
	// obtiene los datos basicos de un carrito
		public function car_id( $id_car=0 ){
			if( $id_car==0 ){ return false; }

			$s = "SELECT * from sales_flat_quote where entity_id = $id_car";
			$a = query( $s );
			if( $a==null ){
				$this->data = null;
				return false;
			}

			$this->car_items( $id_car );
			$this->items = $this->data;
			$this->data  = $a;
			return true;
		}
	// desabilita un carrito de ventas
		public function car_disable( $id_car=0 ){
			if( $id_car==0 ){ return false; }

			$date = date( 'Y-m-d G:i:s', time() );
			$s = "UPDATE sales_flat_quote set is_active = '0', updated_at = '$date' where entity_id = $id_car";
			query( $s );

			return true;
		}

	// tiempo par limpiar carritos
		public function clean_time(){
			$t = $this->clean_time * $this->car_clean_time;
			$t = time() - $t;

			return $t;
		}
		public function clean_date(){
			return date( 'Y-m-d', $this->clean_time() );
		}

	/* regresa la estructura sql para las functiones
	 * 		data_last();
	 *		data_first()
	 *		list_all()
	 *		list_range()
	 */
	private function sql_data( $order='ASC', $limit=1, $any = '' ){
		//echo "\n sql_data()";

		$slimit = '';
		if( $limit==1 ){ $slimit = 'limit 0,1'; }

		$s = "SELECT
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
			$any
			order by sfq.created_at $order
			$slimit";

		return $s;
	}

	public function data_last(){
		//echo "\n data_last()";

		$s = $this->sql_data( 'DESC' );
		$a = query( $s );
		if( $a==null ){
			echo "\n no data";
			return false;
		}

		$a = $this->valid_sku( $a );
		$a = $this->valid_parent( $a );
		$a = $this->valid_marca( $a );
		$a = $this->valid_categoria( $a );

		$this->data = $a[0];
		return true;
	}
	public function data_first(){
		//echo "\n data_first()";

		$s = $this->sql_data( 'ASC' );
		$a = query( $s );
		if( $a==null ){
			echo "\n no data"; 
			return false;
		}

		$a = $this->valid_sku( $a );
		$a = $this->valid_parent( $a );
		$a = $this->valid_marca( $a );
		$a = $this->valid_categoria( $a );

		$this->data = $a[0];
		return true;
	}
	public function list_range( $f1='',$f2='' ){
		//echo "\n list_range()";
		if( $f1=='' ){ echo "\n sin fecha"; return null; }
		if( $f2=='' ){ echo "\n sin fecha"; return null; }

		$n = strlen( $f1 );
		//echo "\n f1 ==> $n";
		if( $n==4 ){  $f1 = $f1."-01-01 00:00:00"; }
		if( $n==7 ){  $f1 = $f1."-01 00:00:00"; }
		if( $n==10 ){ $f1 = $f1." 00:00:00"; }
		if( $n==13 ){ $f1 = $f1."00:00"; }
		if( $n==16 ){ $f1 = $f1."00"; }

		$n = strlen( $f2 );
		//echo "\n f2 ==> $n";
		if( $n==4 ){  $f2 = $f2."-12-31 23:59:59"; }
		if( $n==7 ){  $f2 = $f2."-31 23:59:59"; }
		if( $n==10 ){ $f2 = $f2." 23:59:59"; }
		if( $n==13 ){ $f2 = $f2."59:59"; }
		if( $n==16 ){ $f2 = $f2."59"; }

		echo "\n select range $f1 ==> $f2";

		$s = $this->sql_data( 'ASC', 0, "and sfq.created_at >= '$f1' and sfq.created_at <= '$f2'" );
		//echo "\n $s";
		$a = query( $s );
		if( $a==null ){
			//echo "\n sin registos";
			$this->data = null;
			return 0;
		}

		$u = new mCustomer();
		$a = $u->filter_user( $a );

		$a = $this->valid_sku( $a );
		$a = $this->valid_parent( $a );
		$a = $this->valid_marca( $a );
		$a = $this->valid_categoria( $a );

		$this->data = $a;
		$this->struct_pack();
		return count( $this->data );
	}
	public function list_all(){
		//echo "\n list_all()";

		$s = $this->sql_data( 'ASC', 0 );
		//echo "\n$s";
		$a = query( $s );
		if( $a==null ){
			echo "\n sin datos en los carritos";
			return null;
		}

		$u = new mCustomer();
		$a = $u->filter_user( $a );

		$a = $this->valid_sku( $a );
		$a = $this->valid_parent( $a );
		$a = $this->valid_marca( $a );
		$a = $this->valid_categoria( $a );

		$this->data = $a;
		$this->struct_pack();
		return count( $this->data );
	}
	public function struct_pack(){
		if( $this->data==null ){
			return false;
		}

		$b = null;
		foreach ($this->data as $et => $r) {
			$b[ $r['entity_id'] ][] = $r;
			unset( $this->data[ $et ] );
		}

		$this->data = $b;

		return true;
	}
	public function valid_sku( $d=null ){
		if( $d==null ){ return null; }

		$lp = null;
		foreach ($d as $et => $r) {
			$lp[ $r['product_id'] ] = 1;
		}

		//print_r( $lp );

		$p = new mProduct();
		foreach ($lp as $et => $r) {
			$pp = $p->product_data( $et );

			foreach ($d as $etr => $rr) {
				if( $rr['product_id'] == $et ){
					$d[ $etr ]['sku'] = $pp['sku'];
					$d[ $etr ]['marca'] = $pp['marca'];
				}
			}
		}

		return $d;
	}
	public function valid_parent( $d=null ){
		if( $d==null ){ return null; }

		$tmp = null;
		foreach ($d as $et => $r) {
			if( $r['parent_item_id'] != '' ){
				$tmp[ $r['parent_item_id'] ][ $et ] = 1;
			}
		}

		if( $tmp==null ){
			return $d;
		}

		foreach ($tmp as $et => $r) {
			foreach ($d as $etr => $rr) {
				if( $et == $rr['item_id'] ){
					foreach ($r as $etrr => $rrr) {
						$tmp[ $et ][ $etrr ] = $rr['sku'];
					}
				}
			}
		}

		$a = null;
		foreach ($tmp as $et => $r) {
			foreach ($r as $etr => $rr) {
				$a[ $etr ] = $rr;
			}
		}
		$tmp = $a;
		foreach ($tmp as $et => $r) {
			$d[ $et ]['parent_item_id'] = $r;
		}

		return $d;
	}
	public function valid_marca( $d=null ){
		if( $d==null ){ return null; }

		$marca = null;
		foreach ($d as $et => $r) {
			$marca[ $r['marca'] ] = 1;
		}

		$p = new mProduct();
		foreach ($marca as $et => $r) {
			$marca[ $et ] = $p->attribute_option( 'marca', $et );
		}

		//print_r( $lsku );

		foreach ($d as $et => $r) {
			$d[ $et ]['marca'] = $marca[ $r['marca'] ];
		}

		return $d;
	}
	public function valid_categoria( $d=null ){
		if( $d==null ){ return null; }
		$lsku = null;
		foreach ($d as $et => $r) {
			if( $r['sku'] != '' )
				$lsku[ $r['sku'] ] = 1;
		}

		$c = new mCateg();
		foreach ($lsku as $et => $r) {
			$dd = $c->product_all_categ( $et );
			$s = '';
			$i = 0;
			foreach ($dd as $etr => $rr) {
				if( $this->is_categ_on( $etr ) ){
					$s .= utf8_encode($rr).",";
					$i++;
				}
			}
			$lsku[ $et ] = $s;
		}

		//print_r( $lsku );

		foreach ($d as $et => $r) {
			if( $r['sku']!='' )
				$d[ $et ]['categ'] = $lsku[ $r['sku'] ];
			else
				$d[ $et ]['categ'] = '';
		}

		return $d;
	}
	public function is_categ_on( $id_categ=0 ){
		if( $id_categ==0 ){ return false; }

		if( $id_categ == 3 ) return false;
		if( $id_categ >= 13 && $id_categ <= 62 ) return false;
		if( $id_categ == 65 ) return false;
		if( $id_categ >= 66 && $id_categ <= 67 ) return false;
		if( $id_categ == 69 ) return false;
		if( $id_categ == 70 ) return false;
		if( $id_categ == 76 ) return false;

		return true;
	}

}

class reportCars{

	public $log = '';

	public function update_report(){
		echo "\n actualizando reporte carritos abandonados";
		echo "\n =========================================";


		/*
		 * busco la ultima fecha en los carritos abandonados
		 * busco la primera fecha en los carritos abandonados
		 * busco la ultima fecha en el reporte
		 *
		 * la ultima fecha de carritos es vacio, no hay carritos en el sistema, SALIR
		 * si la fecha en reporte es vacio, reporte en blanco, AGREGAR TODOS LOS REGISTROS
		 * si fecha reporte es menor a fecha inicial carritos, AGREGAR TODOS LOS REGISTROS
		 * si fecha reporte es mayor a fecha inicial carritos,
		 *		rango_cambios = fecha reporte - fecha inicial carritos 
		 *		rango_agregar = fecha final carritos - fecha reporte
		 */

			$car = new mCars();

			$car->data_last();		$clast = $car->data['created_at'];	//echo "\n ==> last  ==> [$clast]";
			$car->data_first();		$cfist = $car->data['created_at'];	//echo "\n ==> first ==> [$cfist]";
			$rlast = $this->report_data_last();
			$rlast = $rlast['created_at'];
			//echo "\n ==> report last ==> [$rlast]";

		/* las dos tablas estan vacias */
			if( $clast == '' && $rlast == '' ){
				echo "\n sin registros en ambas tablas";
				return 0;
			}

		/* carritos vacios */
			if( $clast == '' ){
				echo "\n carritos vacios";
				return 0;
			}

		/* reporte vacio */
			if( $rlast == '' ){
				echo "\n ==> reporte vacio";
				$n = $car->list_all();

				if( $n==0 ){
					echo "\n error obteniendo carritos";
					return 0;
				}
				echo "\n agregando carritos ==> ".$n;
				$this->add_regs_all( $car->data );

				return true;
			}

		/* agregando registros fuera de rango */
			echo "\n agregando registros nuevos";
			$car->list_range( $this->u_plus_date( $rlast, 1 ), $clast );
			if( $car->data == null ){
				echo "\n sin registros nuevos";
			}else{
				//print_r( $car->data );
				$this->add_regs_all( $car->data );
			}

		/* listando registros dentro de rango */
			$car->list_range( $cfist, $rlast );
			$data_change = null;
			if( $car->data != null ){
				$data_change = $car->data;
			}

		/* localizando registros cambiados */
			echo "\n validando cambios\n";
			$delete = null;
			foreach ($data_change as $et => $r) {
				if( $this->is_cahnge_report_data( $r ) ){
					$delete[] = $this->reg_ident( $r );
					//echo '.';
				}
			}

			//print_r( $delete );
			echo "\n registros con cambios ==> ".count( $delete );
			if( $delete==null ){ return true; }

		/* borrando registros modificados */
			echo "\n borrando registros modificados\n";

			foreach ($delete as $et => $r) {
				$this->delete_regs( $r );
			}

		/* borrando todos los registros a actualizar */
			echo "\n agregando registros modificados\n";

			$car->list_all();
			if( $car->data == null ){ return false; }
			foreach ($delete as $et => $r) {
				//print_r( $r );
				foreach ($car->data[ $r['entity_id'] ] as $et => $r) {
					$this->add_regs( $r );
				}
				echo '.';
			}

		return true;
	}

	/* obtiene el ultimo registro creado */
	public function report_data_last(){
		//echo "\n report_data_last()";

		$s = "SELECT * from report_cars order by created_at DESC limit 0,1";
		$a = query( $s );
		if( $a == null ){
			//echo "\n tabla vacia";
			return null;
		}

		return $a[0];
	}
	/* determina si existe 1 registro */
	public function is_reg_report( $reg=null ){
		echo "\n is_reg_report()";

		if( $reg==null ){
			echo "\n sin datos 1"; 
			return false;
		}

		$id = $reg['entity_id'];
		$s = "SELECT * from report_cars where entity_id = $id";
		echo "\n $s";
		$a = query($s);
		if( $a==null ){
			echo "\n registro no encontrado";
			return false;
		}

		echo "\n registro encontrado";
		return true;
	}
	/* obtiene todos los registros del reporte
	 * retturn int 		regresa un numero mayor a cero en caso de tener datos
	 *					regresa cero en caso de error
	 * $this->data 		contiene los datos obtenidos
	 */
	public function report_all_data(){
		echo "\n report_all_data()";

		$s = "SELECT * from report_cars";
		$a = query( $s );
		if( $a==null ){
			echo "\n sin registros";
			$this->data = null;
			return 0;
		}

		//$a = $this->struct_pack( $a );
		$this->data = $a;
		return count( $this->data );
	}
	/* estructura los datos en paquetes */
	public function struct_pack( $data=null ){
		if( $data==null ){
			return false;
		}

		$b = null;
		foreach ($data as $et => $r) {
			$b[ $r['entity_id'] ][] = $r;
			unset( $data[ $et ] );
		}

		return $b;
	}
	/* agrega muchos registros al reporte */
	public function add_regs_all( $data=null, $limit = 0 ){
		//echo "\n add_regs_all()";

		if( $data==null ){
			echo "\n sin datos 2";
			return 0;
		}

		$i = 0;
		$a = null;
		echo "\n";
		foreach ($data as $et => $r) {
			//print_r( $r );

			if( $limit>0 ){ if( $i==$limit ){ break; } }
			//print_r( $this->reg_ident( $r ) );
			$a[ $i ] = $this->reg_ident( $r );
			//echo print_table( $this->reg_ident( $r ) );
			foreach ($r as $etr => $rr) {
				$this->add_regs( $rr );
			}
			//print_r( $a[$i] );
			/*
			$s = "\n";
			foreach ($a[$i] as $et => $r) {
				$s = $s." [ $et ==> $r ]";
			}
			echo $s;*/
			echo '.';
			$i++;
		}

		return $i;
	}
	/* regresa identificadores de la data */
	public function reg_ident( $reg=null ){
		if( $reg==null ){ return null; }

		$a = null;
		//print_r( $reg );
		foreach ($reg as $et => $r) {
			$a['entity_id'] 			= $r['entity_id'];
			$a['updated_at'] 			= $r['updated_at'];
			$a['grand_total'] 			= $r['grand_total'];
			$a['items_count'] 			= $r['items_count'];
			if( !isset( $a['sku'] ) ){
				$a['sku']				= '/'.$r['sku'].'/'.( (int)$r['qty'] );
			}else{
				$a['sku'] 				.= '/'.$r['sku'].'/'.( (int)$r['qty'] );
			}
		}

		return $a;
	}
	/* agrega 1 registro al reporte */
	public function add_regs( $reg=null ){
		//echo "\n add_regs()";

		if( $reg==null ){
			echo "\n sin datos 3"; 
			return 0;
		}

		$cab = '';
		$sd  = '';
		foreach ($reg as $et => $r) {
			if( $et == 'item_id' ){ continue; }

			if( $cab != '' ){ $cab .= ','; }
			if( $sd != '' ){  $sd .= ','; }

			$cab .= "`$et`";

			if( is_float($r) ){
				$sd .= (float)$r;
			}else if( is_int($r) ){
				$sd .= (int)$r;
			}else{
				$sd .= '"'.$r.'"';
			}
		}

		$s = "INSERT into report_cars( $cab ) values( $sd )";
		//echo "\n $s";
		$id = query( $s );
		if( $id==null ){
			echo "\n error al agregar datos al reporte";
			return 0;
		}

		return $id;
	}
	/* calculo con fechas */
	public function u_plus_date( $s='', $increment=0 ){
		if( $s=='' ){ return ''; }
		if( $increment==0 ){ return $s; }

		$date = explode(' ', $s);
		$time = 0;
		if( isset( $date[1] ) ){
			$time = $date[1];
		}
		$date = $date[0];

		$date = explode('-', $date);
		$time = explode(':', $time);

		//echo "\n ********** ";	print_r( $date );
		//echo "\n ********** ";	print_r( $time );

		$t = mktime( $time[0],$time[1],$time[2], $date[1],$date[2],$date[0] );
		$t += $increment;

		$fecha = date( 'Y-m-d G:i:s', $t );
		//echo "\n ********** $fecha";

		return $fecha;
	}
	/* revisando datos cambiados */
	public function is_cahnge_report_data( $reg=null ){
		if( $reg==null ){ return false; }

		//echo "\n is_cahnge_report_data()";

		$data1 = $this->reg_ident( $reg );
		//print_r( $data1 );

		$b = $this->regs_idcar( $data1['entity_id'] );
		if( $b==null ){ return true; }

		$data2 = $this->reg_ident( $b );
		//print_r( $data1 );

		$ch = 0;
		if( $data1['updated_at']  != $data2['updated_at'] ){ $ch++; }	//if( $ch>0 ){ echo "\n entity_id ==> [".$data1['entity_id']."] change ==> [".$data1['updated_at'].']['.$data2['updated_at'].']'; }
		if( $data1['grand_total'] != $data2['grand_total'] ){ $ch++; }	//if( $ch>0 ){ echo "\n entity_id ==> [".$data1['entity_id']."] change ==> [".$data1['grand_total'].']['.$data2['grand_total'].']'; }
		if( $data1['items_count'] != $data2['items_count'] ){ $ch++; }	//if( $ch>0 ){ echo "\n entity_id ==> [".$data1['entity_id']."] change ==> [".$data1['items_count'].']['.$data2['items_count'].']'; }
		if( $data1['sku']         != $data2['sku'] ){ $ch++; }			//if( $ch>0 ){ echo "\n entity_id ==> [".$data1['entity_id']."] change ==> [".$data1['sku'].']['.$data2['sku'].']'; }

		/*
		$n = rand(0,9);
		if( $n==0 ) return 1;
		return 0;*/

		if( $ch>0 ){ return true; }

		return false;
	}
	/* obtiene 1 carrito del reporte */
	public function regs_idcar( $id='' ){
		//echo "\n regs_idcar()";

		if( $id=='' ){
			echo "\n sin datos 4";
			return null;
		}

		$s = "SELECT * from report_cars where entity_id = $id";
		//echo "\n -- $s";
		$a = query($s);
		if( $a==null ){
			//echo "\n sin datos 5";
			return null;
		}

		return $a;
	}
	/* vacia todo el reporte */
	public function delete_regs_all(){
		echo "\n delete_regs_all()";

		$s = "TRUNCATE report_cars;";
		query( $s );

		return true;
	}
	/* borra un registro del reporte */
	public function delete_regs( $reg=null ){
		//echo "\n delete_regs()";

		$s = "SELECT GROUP_CONCAT(rc_id) as rc_id from report_cars where entity_id = ".$reg['entity_id'];
		$a = query( $s );
		if( $a==null ){ return false; }

		$a = $a[0]['rc_id'];

		$s = "DELETE from report_cars where rc_id IN( $a )";
		query( $s );

		echo '.';

		return true;
	}
	/* obtiene el numero total de registros en el reporte */
	public function regs_total(){
		$s = "SELECT count(rc_id) as n from report_cars";
		$a = query( $s );
		if( $a==null ){ return 0; }

		return $a[0]['n'];
	}
	/* filtra las cabecera para poder generar el reporte de ventas */
	public function filtra_cabs_report( $d=null ){
		if( $d==null ){ return null; }

		$a = array(
			"entity_id",
			"updated_at",
			"customer_email",
			"customer_firstname",
			"customer_lastname",
			"reserved_order_id",
			"items_count",
			"grand_total",
			"subtotal",
			"parent_item_id",
			//"product_id",
			"sku",
			"name",
			"qty",
			"price",
			"tax_amount",
			"row_total",
			"row_total_incl_tax",
			"marca",
			"categ"
		);

		$c = null;
		foreach ($d as $et => $r) {
			foreach ($a as $etr => $rr) {
				$c[ $et ][ $rr ] = $r[ $rr ]; 
			}
			unset( $d[ $et ] );
		}

		return $c;
	}
	/* convierte una columna en varias por el arreglo en la data, esto para exportar a csv */
	public function col_to_cols( $d=null, $col='' ){
		if( $d==null ){ return null; }
		if( $col=='' ){ return $d; }

		/* obteniendo el numero de columnas adicionales */
		$s = '';
		$n = 0;
		foreach ($d as $et => $r) {
			$s = $r[ $col ];
			if( $s!='' ){
				$s = explode(',',$s);
				$nn = count($s);
				if( $nn>$n ){ $n = $nn; }
			}
		}

		/* generando columnas adicionales */
		foreach ($d as $et => $r) {
			for( $i=1; $i<=$n; $i++ ){
				$d[ $et ][ $col.'-'.$i ] = '';
			}
		}

		/* ingresando datos a las columnas */
		foreach ($d as $et => $r) {
			if( $r[ $col ]!='' ){
				$a = explode(',', $r[ $col ]);
				//print_r($a);

				$i=1;
				foreach ($a as $etr => $rr) {
					$d[ $et ][ $col.'-'.$i ] = $rr;
					$i++;
				}
			}
		}

		/* quitando columna inicial */
		foreach ($d as $et => $r) {
			unset( $d[ $et ][ $col ] );
		}

		return $d;
	}
}

/*
	CREATE TABLE `report_cars` (
	  `rc_id` INT NOT NULL AUTO_INCREMENT,
	  `entity_id` INT NULL,
	  `created_at` VARCHAR(45) NULL,
	  `updated_at` VARCHAR(45) NULL,
	  `customer_email` VARCHAR(255) NULL,
	  `customer_firstname` VARCHAR(128) NULL,
	  `customer_lastname` VARCHAR(255) NULL,
	  `reserved_order_id` VARCHAR(128) NULL,
	  `items_count` INT NULL,
	  `grand_total` DECIMAL(12,2) NULL,
	  `subtotal` DECIMAL(12,2) NULL,
	  `product_id` INT NULL,
	  `parent_item_id` VARCHAR(128) NULL,
	  `sku` VARCHAR(256) NULL,
	  `name` VARCHAR(128) NULL,
	  `qty` INT NULL,
	  `price` DECIMAL(12,2) NULL,
	  `tax_amount` DECIMAL(12,2) NULL,
	  `row_total` DECIMAL(12,2) NULL,
	  `row_total_incl_tax` DECIMAL(12,2) NULL,
	  `marca` VARCHAR(128) NULL,
	  `categ` TEXT NULL,
	  PRIMARY KEY (`rc_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_unicode_ci
	COMMENT = 'reporte carritos abandonados';
*/

?>