<?php

/* libreria para obtener las ordenes de venta de magento */

include('basics.php');
include('querys.php');

class mSalesOrder{

	public $data = null;

	public function sales_count(){
		$s = "SELECT count(*) as n from sales_flat_order";
		$a = query($s);

		if($a==null){ return 0; }
		return $a[0]['n'];
	}

	public function _so_campos(){
		$a = array(
			'sfo.entity_id',
			'sfop.method as sales_method',
			'sfop.openpay_payment_id',/**/
			'sfop.openpay_barcode',
			'sfo.increment_id',
			'sfo.status',
			'sfo.created_at',
			'sfo.updated_at',
			'sfo.customer_email',
			'sfo.subtotal',
			'( sfo.subtotal_incl_tax - sfo.subtotal ) as subtotal_tax',
			'sfo.subtotal_incl_tax',
			'sfo.shipping_method',
			'sfo.shipping_amount',
			'sfo.shipping_tax_amount',
			'sfo.shipping_incl_tax',
			'sfo.shipping_description',
			'sfo.discount_amount',
			'sfo.shipping_discount_amount',
			'sfo.coupon_code',
			'sfo.discount_coupon_amount',
			'sfo.tax_amount',
			'sfo.grand_total',
			'sfo.total_due',
			'sfo.total_item_count',
			'sfo.total_qty_ordered',
			'sfo.customer_id',
			'sfo.shipping_address_id',
			'sfo.billing_address_id',
			'sfo.customer_firstname',
			'sfo.customer_lastname',
			'sfo.order_currency_code',
			'sfo.remote_ip',
			'sfo.x_forwarded_for',
		);
			//'cev.value as openpay_user_id',

		return $a;
	}

	public function so_campos(){
		return implode(',', $this->_so_campos() );
	}

	/* obtiene el listado de ordenes de venta
	 *	argumentos
	 *		oc 			nombre de la columna por la que se ordenara el listado
	 * 		or 			modo de ordenado ASC o DESC
	 */
	public function so_list($a=null){

		$order_campos = 'increment_id';
		$order = 'DESC';

		if( $a != null ){
			if( isset( $a['oc'] ) ){ if( $a['oc'] != '' ){ $order_campos = $a['oc']; } }
			if( isset( $a['or'] ) ){ if( $a['or'] != '' ){ $order = $a['or']; } }
		}

		$campos = $this->so_campos();

		$s = "SELECT $campos 
			from sales_flat_order as sfo
			left join sales_flat_order_payment as sfop on sfop.parent_id = sfo.entity_id
			order by $order_campos $order
			";
		//echo "\n $s";
		$a = query( $s );
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

	/* agrupa las ordenes de compra por una columna especifica */
	public function so_totals( $cmp='' ){
		if( $cmp=='' ){
			$this->data = null;
			return 0;
		}

		$this->so_list();
		$a = $this->data;
		$this->so_distinct( 'status' );
		$b = $this->data;

		//print_r($b);

		$c = null;
		foreach ($a as $et => $r) {
			$c[ $r[ $cmp ] ][ $cmp ] = $r[ $cmp ];
			foreach ($b as $etr => $rr) {
				$c[ $r[ $cmp ] ][ 'n_total' ] = 0;
				$c[ $r[ $cmp ] ][ 'n_'.$rr ] = 0;
				$c[ $r[ $cmp ] ][ $rr ] = 0;
			}
		}
		foreach ($a as $et => $r) {
			$c[ $r[ $cmp ] ][ 'n_total' ] += 1;
			$c[ $r[ $cmp ] ][ 'n_'.$r['status'] ] += 1;
			$c[ $r[ $cmp ] ][ $r['status'] ] += $r['grand_total'];
		}

		//print_r($c);
		$this->data = $c;
		//$this->data = $a;

		return count($this->data);
	}

	/* obtiene los diferentes valores que hay en una columna especifica en el listado de ordenes de venta */
	public function so_distinct( $c='' ){
		$s = "SELECT distinct( $c ) as campo from sales_flat_order";
		$a = query( $s );
		if( $a==null ){
			$this->data = null;
			return 0;
		}

		$b = null;
		foreach ($a as $et => $r) {
			$b[] = $r['campo'];
		}

		$this->data = $b;
		return count( $b );
	}
	/* lista los nombred de las columnas */
	public function so_list_columns(){
		$a = $this->_so_campos();
		
		$a = null;
		foreach ($this->_so_campos() as $et => $r) {
			$a[]['tit'] = $r;
		}

		return $a;
	}
	/* obtiene una orden de compra */
	public function sales( $so='' ){
		if( $so=='' ){
			$this->data = null;
			return false;
		}

		$campos = $this->so_campos();

		$s = "SELECT $campos 
			from sales_flat_order as sfo
			left join sales_flat_order_payment as sfop on sfop.parent_id = sfo.entity_id
			where sfo.increment_id like '$so'
			";
		//echo "\n $s";
		$a = query( $s );
		if( $a==null ){
			$this->data = null;
			return false;
		}

		$this->data = $a[0];
		return true;
	}
}

/*
$so = new mSalesOrder();
echo "\n numero de ordenes totales ==> ".$so->sales_count();
//$n = $so->so_list();
$n = $so->so_list( array( 'oc'=>'customer_email', 'or'=>'ASC' ) );
$so->so_totals( 'customer_email' );
$n = $so->so_list( array( 'oc'=>'increment_id', 'or'=>'DESC' ) );
//print_r( $so->data );
echo print_table( $so->data );
echo print_table( $so->so_list_columns() );
echo "\n numero de ordenes listadas ==> ".$n;
*/

?>