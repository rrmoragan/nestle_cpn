<?php

if( !defined('FORCE_UTF8') ){
	include( 'forceUTF8.php' );
}

class Magento_Lib{

	public $step = '';
	public $step_group = '';

	public $step_one = '';
	public $n_step_one = '';

	public $data = null;

	/* listado de productos */

	/* lista las categorias existentes */
	public function list_products_categs(){}
	/* lista todos los productos
		http://localhost/cafe/NESCA/cafe_productos.php?v=list-products
		http://localhost/cafe/NESCA/cafe_productos.php?
			v=list-products&
			d=name,nombre_secundario,marca,sku,sat_clave,sat_descrip,price,impuesto_iva,updated_at
	*/
	public function list_products_all($filtro=''){

		/* obtiene todos los productos activos */
		$this->list_products_all_reindex();

		/* obteniendo el listado de los id de los productos */
		$this->step_group = $this->list_id_string();

		/* agregando los atributos a los productos */
		$this->data_add_attribs();

		$this->array_filtro( $filtro );

		$this->product_attrib_marca();

		$this->product_strim_tags();

		return false;
	}

	/* ejecuta strim_tags a todos los campos de los productos */
	public function product_strim_tags(){
		if( $this->data==null ){ return false; }

		foreach ($this->data as $et => $r) {
			foreach ($r as $etr => $rr) {
				//$this->data[ $et ][ $etr ]['value'] = htmlentities( $this->data[ $et ][ $etr ]['value'], ENT_QUOTES, "ISO-8859-1" );
				$this->data[ $et ][ $etr ]['value'] = ( trim( strip_tags( ( $this->data[ $et ][ $etr ]['value'] ) ) ) );
			}
		}
		return true;
	}
	public function list_products_all_reindex(){
		$s = $this->sql_list_products_all();
		$a = query($s);
		if( $a==null ){ return false; }

		$b = null;
		foreach ($a as $et => $r) {
			$b[ $r['entity_id'] ] = $r;
		}

		$this->data = $b;

		return true;
	}
	/* obtiene los id de todos los productos listados en $this->data */
	public function list_id_string(){
		if($this->data == null){ return ''; }

		$s = '';
		foreach ($this->data as $et => $r) {
			if($s!=''){ $s="$s,"; }
			$s = $s.$r['entity_id'];
		}

		return $s;
	}
	/* agregando los atributos a los productos */
	public function data_add_attribs(){
		if( $this->step_group == '' ){ return false; }

		/* consultando atributos */
		$s = $this->sql_product_data_any( $this->step_group );
		$a = query($s);

		if($a==null){ return false; }

		/* obtiene atributos */
		foreach ($a as $et => $r) {
			if( isset( $this->data[ $r['entity_id'] ] ) ){
				$this->data[ $r['entity_id'] ]['data'][ $r['attribute_code'] ] = array(
						'value'=>$r['value'],
						'attribute_code'=>$r['attribute_code'],
						'frontend_label'=>$r['frontend_label'],
					);
			}
		}

		/* pasando valores de cabecera a atributos */
		foreach ($this->data as $et => $r) {
			foreach ($r as $etr => $rr) {
				switch( $etr ){
					case 'data':
					case 'status':
						break;
					default: 
						$this->data[ $et ]['data'][ $etr ] = array(
							'value'=>$rr,
							'attribute_code'=>$etr,
							'frontend_label'=>$etr,
						);
						break;
				}
			}
		}

		/* quitando valores de cabecera */
		foreach ($this->data as $et => $r) {
			$this->data[ $et ] = $r['data'];
		}

		/* agregando preice_iva en caso de que no lo haya */
		foreach ($this->data as $et => $r) {
			if( isset( $r['price'] ) ){
				if( isset( $r['impuesto_iva'] ) ){
					if( !isset( $r['price_iva'] ) ){
						$this->data[ $et ]['price_iva'] = $r['price'];
						$this->data[ $et ]['price_iva']['attribute_code'] = 'price_iva';
						$this->data[ $et ]['price_iva']['frontend_label'] = 'Precio con IVA';

						$op = $r['impuesto_iva']['value'];
						if( $r['impuesto_iva']['value']>0 ){
							$op = $op / 100;
							$op = $op * $r['price']['value'];
							$op = $op + $r['price']['value'];
							$this->data[ $et ]['price_iva']['value'] = "$op";
						}
					}
				}
			}
		}

		return false;
	}
	/* lista productos por categoria */
	public function list_products_categ($categ=''){}

	/* filtra opciones */
	public function array_filtro($filtro=''){
		if($filtro==''){ return false; }

		$campos = explode(',', $filtro);

		foreach ($this->data as $et => $r) {
			$b = null;
			foreach ($campos as $etr => $rr) {
				if( isset( $this->data[ $et ][ $rr ] ) ){
					$b[ $rr ] = $this->data[ $et ][ $rr ];
				}
			}
			$this->data[ $et ] = $b;
		}

		return true;
	}
	/* agrega un elemnto al arreglo de los datos de los productos */
	public function product_attrib_add_array($attrib='',$dat=null,$desc=''){

		if($attrib==''){ return null; }
		if($dat==null){ return null; }
		if($desc==''){ $desc = $attrib; }

		$a = array(
			'value'=> $dat,
			'attribute_code'=> $attrib,
			'frontend_label'=> $desc,
		);

		return $a;
	}
	/* obtiene el nombre de la marca de un producto */
	private function product_attrib_marca(){
		if( $this->data==null ){ return false; }

		$marca = null;

		foreach ($this->data as $et => $r) {
			if( isset( $r['marca'] ) ){
				$idm = $r['marca']['value'];
				if( isset( $marca[ $idm ] ) ){
					$this->data[ $et ]['marca']['value'] = $marca[ $idm ];
				}else{
					$s = $this->sql_product_attrib_marca( $idm );
					$a = query($s);
					//tt($idm.' ==> '.$s);
					if( $a!=null ){
						$marca[ $a[0]['option_id'] ] = $a[0]['value'];

						$this->data[ $et ]['marca']['value'] = $marca[ $idm ];	
					}
				}
			}
		}

		return true;
	}



	/* sql consulta todos los productos activos */
	private function sql_list_products_all(){
		$s = "SELECT 
			cpe.entity_id,
			cpe.entity_type_id,
			cpe.attribute_set_id,
			cpe.sku,
			cpe.updated_at,
			cpei.value as status

			from catalog_product_entity as cpe
			inner join catalog_product_entity_int as cpei on cpei.entity_id = cpe.entity_id
			inner join eav_attribute as ea on ea.attribute_id = cpei.attribute_id

			where
			ea.attribute_code like 'status' and
			cpei.value like '1'
			;";

		return $s;
	}
	/* sql obtiene los atributos de un producto */
	private function sql_product_data($id=0){
		if($id==0){ return ''; }

		$s = "SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_datetime as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id = $id

			union

			SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_decimal as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id = $id

			union

			SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_gallery as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id = $id

			union

			SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_int as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id = $id

			union

			SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_text as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id = $id

			union

			SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_url_key as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id = $id

			union

			SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_varchar as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id = $id";

		return $s;
	}
	/* sql obtiene los atributos de varios productos */
	private function sql_product_data_any($ids=''){
		if($ids==''){ return ''; }

		$s = "SELECT t.value_id,t.entity_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_datetime as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id IN( $ids )

			union

			SELECT t.value_id,t.entity_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_decimal as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id IN( $ids )

			union

			SELECT t.value_id,t.entity_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_gallery as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id IN( $ids )

			union

			SELECT t.value_id,t.entity_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_int as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id IN( $ids )

			union

			SELECT t.value_id,t.entity_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_text as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id IN( $ids )

			union

			SELECT t.value_id,t.entity_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_url_key as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id IN( $ids )

			union

			SELECT t.value_id,t.entity_id,t.value,eav.attribute_code,eav.frontend_label 
			from catalog_product_entity_varchar as t 
			inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
			where 
			t.entity_id IN( $ids )
            ";

		return $s;
	}
	private function sql_product_attrib_marca($id_marca=0){
		if($id_marca==0){ return ''; }
		$s = "SELECT * from eav_attribute_option_value where option_id = $id_marca and store_id=0;";

		return $s;
	}


	/* ordenes de venta */

	public function sales_order_change_status( $st1='', $st2='' ){
		if( $st1=='' ){ return false; }
		if( $st2=='' ){ return false; }

		/* busca las ordenes de compra con status $st1 */
		$this->sql_select_status_to_status( $st1 );
		$a = query($this->step_group);
		if($a==null){ return true; }
		$this->step_group = $a[0]['list'];

		/* actualizando las ordenes de compra con el status $st2 */
		$s = $this->sql_update_status_to_status($st2);
		query($s);//tt($s);

		return true;
	}
	/* lista todas las ordenes de compra */
	public function all_list( $order='' ){
		$s = $this->sql_all_list( $order );
		$a=query($s);

		if($a==null){
			tt('sin ordenes de compras');
			return true;
		}

		$s = 'items => '.count($a);
		tt($s);
		echo print_table($a);
		tt($s);
		echo "\n";
		return true;
	}
	/* suma las ordenes de compra con un status especifico */
	public function sum_order($st=''){
		if( $st=='' ){ return false; }

		$s = $this->sql_sum_order($st);
		$a = query( $s );
		if($a==null){
			tt('grand_total ==> 0');
			return true;
		}

		tt('grand_total ==> '.$a[0]['grand_total']);
		return true;
	}
	/* cambia los status de compra de todas las ordenes de compra que coincidan con el status dado */
	public function order_change_status( $st1='', $st2='' ){
		if($st1==''){ return false; }
		if($st2==''){ return false; }

		$s = $this->sql_order_select_id_status($st1);
		$a = query($s);
		//tt($s);
		if($a==null){
			tt('sin ordenes de compra con status ['.$st1.']');
			return true;
		}
		$a = $a[0]['list'];

		$s = $this->sql_update_sfo_status( $a, $st2 );
		query($s);
		//tt($s);
		$s = $this->sql_update_sfog_status( $a, $st2 );
		query($s);
		//tt($s);

		return true;
	}
	/* lista los status posibles para una orden de compraq */
	public function order_list_status(){
		$s = $this->sql_order_list_status();
		$a=query($s);
		if($a==null){
			tt('sin status definidos');
			return true;
		}

		echo print_table($a);

		$s = $this->sql_order_list_status_used();
		$a=query($s);
		if($a!=null){
			tt('status utilizado');
			echo print_table($a);
		}

		return true;
	}
	/* solo se puede borrar las ordenes de compra canceladas */
	public function order_delete(){

		$s = $this->sql_order_select_id_status('canceled', false);
		$a = query($s);
		//tt($s);
		if($a==null){
			tt('sin ordenes de compra canceladas');
			return true;
		}

		foreach ($a as $et => $r) {
			tt('borrando '.$r['increment_id'].' ..........');
			$s = $this->sql_delete_order_status_history( $r['entity_id'] );
			query($s);//tt($s);
			$s = $this->sql_delete_order_payment( $r['entity_id'] );
			query($s);//tt($s);
			$s = $this->sql_delete_order_address( $r['entity_id'] );
			query($s);//tt($s);
			$s = $this->sql_delete_order_item( $r['entity_id'] );
			query($s);//tt($s);
			$s = $this->sql_delete_order_grid( $r['entity_id'] );
			query($s);//tt($s);
			$s = $this->sql_delete_order( $r['entity_id'] );
			query($s);//tt($s);
		}

		return true;
	}
	/* cambia un atributo de una orden de compra */
	public function order_id_change( $order=0,$attrib='',$val='' ){
		if( $order==0 ){ return false; }
		if( $attrib=='' ){ return false; }

		$oid = $this->order_id( $order );
		if( !$oid ){
			tt('el numero de orden no existe');
			return false;
		}

		//echo print_table($oid);

		$s = $this->sql_order_grid_id_change( $order,$attrib,$val );
		query($s);//tt($s);
		$s = $this->sql_order_id_change( $order,$attrib,$val );
		query($s);//tt($s);

		//echo print_table($this->order_id( $order ));

		return true;
	}
	public function order_id( $order=0 ){
		if( $order==0 ){ return false; }

		$s = $this->sql_order_id( $order );
		$a = query($s);
		if( $a==null ){ return null; }

		return $a[0];
	}

	/* productos */
	public function product_list(){

		$s = $this->sql_product_list_all();
		$a = query($s);

		return $a;
	}
	public function product_list_count(){

		$s = $this->product_list_all_count();
		$a = query($s);

		return $a;
	}

	/* consultas sql */

	private function sql_all_list($order='updated_at'){

		if( $order=='' ){ $order='updated_at'; }
		if( $order!='customer_name' ){ $order = "sfo.$order"; }
		$order = "order by ".$order." ASC";

		$s="SELECT 
		sfo.entity_id as id,
		sfo.subtotal,
		sfo.grand_total,
		sfo.increment_id,
		sfo.state,
		sfo.status,
		/*sfo.created_at,*/
		sfo.updated_at,
		sfo.customer_email,
		concat( sfo.customer_firstname,' ',sfo.customer_lastname ) as customer_name
		from sales_flat_order as sfo
		$order";

		return $s;
	}
	private function sql_sum_order($st=''){
		if($st==''){ return ''; }

		$s = "SELECT sum(grand_total) as grand_total from sales_flat_order where status = '$st'";

		return $s;
	}
	private function sql_order_select_id_status($st='',$group=1){
		if($st==''){ return ''; }

		$s = "SELECT entity_id, increment_id from sales_flat_order where status like '$st'";
		if( $group ){
			$s = "SELECT GROUP_CONCAT(entity_id) as list from sales_flat_order where status like '$st'";
		}

		return $s;
	}
	private function sql_update_sfo_status( $lid='',$st='' ){
		if($lid==''){ return ''; }
		if($st==''){ return ''; }

		$s = "UPDATE sales_flat_order set status = '$st' where entity_id IN( $lid ) ";

		return $s;
	}
	private function sql_update_sfog_status( $lid='',$st='' ){
		if($lid==''){ return ''; }
		if($st==''){ return ''; }

		$s = "UPDATE sales_flat_order_grid set status = '$st' where entity_id IN( $lid ) ";

		return $s;
	}
	private function sql_order_list_status(){
		$s = "SELECT * from sales_order_status";
		return $s;
	}
	private function sql_order_list_status_used(){
		$s = "SELECT distinct(status) from sales_flat_order";
		return $s;
	}
	private function sql_order_id($id=0){
		if($id==0){ return ''; }

		$s = "SELECT * from sales_flat_order where increment_id = '$id'";
		return $s;
	}
	private function sql_order_id_change( $order=0,$attrib='',$val='' ){
		if($order==0){ return '';}
		if($attrib==''){ return '';}

		$s = "UPDATE sales_flat_order set $attrib = '$val' where increment_id = '$order';";
		return $s;
	}
	private function sql_order_grid_id_change( $order=0,$attrib='',$val='' ){
		if($order==0){ return '';}
		if($attrib==''){ return '';}

		$s = "UPDATE sales_flat_order_grid set $attrib = '$val' where increment_id = '$order';";
		return $s;
	}


	private function sql_delete_order_status_history($order_id=0){
		if( $order_id==0 ){ return ''; }

		/* parent_id es el id dado por sales_flat_order */
		$s = "DELETE from sales_flat_order_status_history where parent_id = $order_id;";
		return $s;
	}
	private function sql_delete_order_payment($order_id=0){
		if( $order_id==0 ){ return ''; }

		/* parent_id es el id dado por sales_flat_order */
		$s = "DELETE from sales_flat_order_payment where parent_id = $order_id;";
		return $s;
	}
	private function sql_delete_order_address($order_id=0){
		if( $order_id==0 ){ return ''; }

		/* parent_id es el id dado por sales_flat_order */
		$s = "DELETE from sales_flat_order_address where parent_id = $order_id;";
		return $s;
	}
	private function sql_delete_order_item($order_id=0){
		if( $order_id==0 ){ return ''; }

		/* order_id es el id dado por sales_flat_order */
		$s = "DELETE from sales_flat_order_item where order_id = $order_id;";
		return $s;
	}
	private function sql_delete_order_grid($order_id=0){
		if( $order_id==0 ){ return ''; }

		/* entity_id es el id dado por sales_flat_order */
		$s = "DELETE from sales_flat_order_grid where entity_id = $order_id;";
		return $s;
	}
	private function sql_delete_order($order_id=0){
		if( $order_id==0 ){ return ''; }

		/* entity_id es el id dado por sales_flat_order */
		$s = "DELETE from sales_flat_order where entity_id = $order_id;";
		return $s;
	}


}

?>
