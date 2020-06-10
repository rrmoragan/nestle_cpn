<?php

function bills_tables(){
	$a = array(
		'sales_flat_creditmemo_item',
		'sales_flat_creditmemo_comment',
		'sales_flat_creditmemo_grid',
		'sales_flat_creditmemo',
		'sales_flat_invoice',
		'sales_flat_invoice_comment',
		'sales_flat_invoice_grid',
		'sales_flat_invoice_item',
		'sales_flat_order',
		'sales_flat_order_address',
		'sales_flat_order_grid',
		'sales_flat_order_item',
		'sales_flat_order_payment',
		'sales_flat_order_status_history',
		'sales_flat_quote',
		'sales_flat_quote_address',
		'sales_flat_quote_address_item',
		'sales_flat_quote_item',
		'sales_flat_quote_item_option',
		'sales_flat_quote_payment',
		'sales_flat_quote_shipping_rate',
		'sales_flat_shipment',
		'sales_flat_shipment_comment',
		'sales_flat_shipment_grid',
		'sales_flat_shipment_item',
		'sales_flat_shipment_track',
		'log_quote',
		'sales_payment_transaction',
		'sales_order_tax',
		'sales_order_tax_item',
		'sales_bestsellers_aggregated_daily',
		'sales_bestsellers_aggregated_monthly',
		'sales_bestsellers_aggregated_yearly',
		'sales_invoiced_aggregated',
		'sales_invoiced_aggregated_order',
		'sales_order_aggregated_created',
		'sales_order_aggregated_updated',
		'sales_recurring_profile',
		'sales_recurring_profile_order',
		'sales_refunded_aggregated',
		'sales_refunded_aggregated_order',
		'sales_shipping_aggregated',
		'sales_shipping_aggregated_order'
	);

	return $a;
}

?>