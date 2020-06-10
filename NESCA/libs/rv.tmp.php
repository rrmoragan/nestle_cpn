<?php

include('basics.php');
include('querys.php');
include('csv.lib.php');
include('forceUTF8.php');

function selec_all_sales(){
	$s = "SELECT * from sales_flat_order order by increment_id DESC";

	$a = query($s);

	if( $a==null ){ return null; }

	$b = null;
	foreach( $a as $et=>$r ){ $b[ $r['increment_id'] ] = $r; }

	return $b;
}
function select_all_products( $so_id=0 ){
	$s = "SELECT * from sales_flat_order_item where order_id = ".$so_id;
	$a = query( $s );
	return $a;
}
function select_paymeth_method( $so_id=0 ){
	$s = "SELECT * from sales_flat_order_payment where parent_id = ".$so_id;
	$a = query( $s );
	if( $a==null ){ return ''; }
	return $a[0]['method'];
}
function select_direccion( $so_id=0 ){
	$s = "SELECT * from sales_flat_order_address where address_type like 'shipping' and parent_id = ".$so_id;
	$a = query( $s );
	if( $a==null ){ return ''; }
	return $a[0];
}
function select_product( $pid=0 ){
	$s = "

	SELECT
	'varchar' as ltable,
	cpev.value,
	eaa.attribute_code
	from catalog_product_entity_varchar  as cpev
	inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
	where cpev.entity_id = $pid

	union

	SELECT
	'int' as ltable,
	cpev.value,
	eaa.attribute_code
	from catalog_product_entity_int  as cpev
	inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
	where cpev.entity_id = $pid

	union

	SELECT
	'decimal' as ltable,
	cpev.value,
	eaa.attribute_code
	from catalog_product_entity_decimal  as cpev
	inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
	where cpev.entity_id = $pid

		";

	$a = query( $s );
	if( $a==null ){ return null; }

	$b = null;
	foreach ($a as $et => $r) {
		$b[ $r['attribute_code'] ] = $r['value'];
	}

	return $b;
}

// echo "\n paso 1";
$d = selec_all_sales();

// echo "\n paso 2";
	foreach ($d as $et => $r) {
		$d[ $et ]['products'] = select_all_products( $r['entity_id'] );
		$d[ $et ]['paymeth_method'] = select_paymeth_method( $r['entity_id'] );
		$d[ $et ]['direccion'] = select_direccion( $r['entity_id'] );
	}

// echo "\n paso 3";
	$lp = null;
	foreach ($d as $et => $r) {
		foreach ($r['products'] as $etr => $rr) {
			$lp[ $rr['product_id'] ] = 1;
		}
	}

// echo "\n paso 4";
	foreach ($lp as $et => $r) {
		$lp[ $et ] = select_product( $et );
	}

// echo "\n paso 5";
	foreach ($d as $et => $r) {
		foreach ($r['products'] as $etr => $rr) {
			$d[ $et ]['products'][ $etr ]['name'] = $lp[ $rr['product_id'] ]['name'].' '.$lp[ $rr['product_id'] ]['nombre_secundario'];
			$d[ $et ]['products'][ $etr ]['sku']  = $lp[ $rr['product_id'] ]['codigo_barras'];
		}
	}


$lp = null;

// echo "\n paso 6";

foreach ($d as $et => $r) {
	if( $r['products'] != null ){
		foreach ($r['products'] as $etr => $rr) {

			$tmp = $r['subtotal_incl_tax']-$r['subtotal'];

			$a = null;
			$a['sales_order'] = $r['increment_id'];
			$a['paymeth_method'] = $r['paymeth_method'];
			$a['subtotal'] = $r['subtotal'];
			$a['subtotal_tax'] = "$tmp";
			$a['subtotal_incl_tax'] = $r['subtotal_incl_tax'];
			$a['shipping_description'] = $r['shipping_description'];
			$a['shipping_method'] = $r['shipping_method'];
			$a['shipping_amount'] = $r['shipping_amount'];
			$a['shipping_discount_amount'] = $r['shipping_discount_amount'];
			$a['shipping_tax_amount'] = $r['shipping_tax_amount'];
			$a['shipping_incl_tax'] = $r['shipping_incl_tax'];
			$a['discount_amount'] = $r['discount_amount'];
			$a['discount_description'] = $r['discount_description'];
			$a['discount_coupon_amount'] = $r['discount_coupon_amount'];
			$a['grand_total'] = $r['grand_total'];
			$a['status'] = $r['status'];
			$a['created_at'] = $r['created_at'];
			$a['customer_id'] = $r['customer_id'];
			$a['customer_email'] = $r['customer_email'];
			$a['customer_firstname'] = $r['customer_firstname'];
			$a['customer_lastname'] = $r['customer_lastname'];
			$a['product_id'] = $rr['product_id'];
			$a['parent_item_id'] = $rr['parent_item_id'];
			$a['sku'] = $rr['sku'];
			$a['name'] = $rr['name'];
			$a['qty_ordered'] = $rr['qty_ordered'];
			$a['price'] = $rr['price'];
			$a['discount_amount'] = $rr['discount_amount'];
			$a['tax_amount'] = $rr['tax_amount'];
			$a['price_incl_tax'] = $rr['price_incl_tax'];
			$a['row_total'] = $rr['row_total'];
			$a['row_total_incl_tax'] = $rr['row_total_incl_tax'];
			$a['dir_estado'] = $r['direccion']['region'];
			$a['dir_cp'] = $r['direccion']['postcode'];

			//echo ".";

			$lp[] = $a;
		}
	}else{
			$a = null;
			$a['sales_order'] = $r['increment_id'];
			$a['paymeth_method'] = $r['paymeth_method'];
			$a['subtotal'] = $r['subtotal'];
			$a['subtotal_tax'] = ($r['subtotal_incl_tax']-$r['subtotal']);
			$a['subtotal_incl_tax'] = $r['subtotal_incl_tax'];
			$a['shipping_description'] = $r['shipping_description'];
			$a['shipping_method'] = $r['shipping_method'];
			$a['shipping_amount'] = $r['shipping_amount'];
			$a['shipping_discount_amount'] = $r['shipping_discount_amount'];
			$a['shipping_tax_amount'] = $r['shipping_tax_amount'];
			$a['shipping_incl_tax'] = $r['shipping_incl_tax'];
			$a['discount_amount'] = $r['discount_amount'];
			$a['discount_description'] = $r['discount_description'];
			$a['discount_coupon_amount'] = $r['discount_coupon_amount'];
			$a['grand_total'] = $r['grand_total'];
			$a['status'] = $r['status'];
			$a['created_at'] = $r['created_at'];
			$a['customer_id'] = $r['customer_id'];
			$a['customer_email'] = $r['customer_email'];
			$a['customer_firstname'] = $r['customer_firstname'];
			$a['customer_lastname'] = $r['customer_lastname'];

			$a['product_id'] = 0;
			$a['parent_item_id'] = 0;
			$a['sku'] = '';
			$a['name'] = '';
			$a['qty_ordered'] = 0;
			$a['price'] = 0;
			$a['discount_amount'] = 0;
			$a['tax_amount'] = 0;
			$a['price_incl_tax'] = 0;
			$a['row_total'] = 0;
			$a['row_total_incl_tax'] = 0;
			$a['dir_estado'] = '';
			$a['dir_cp'] = '';

			$lp[] = $a;
	}
}

$csv = new fileCSV();
$lp = $csv->data_to_csv( forceLatin1($lp) );

echo $lp;

?>