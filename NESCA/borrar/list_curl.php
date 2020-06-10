<?php
/* list curl openpay */

/*
open pay

pagina documentacion api

https://www.openpay.mx/docs/api/#introducci-n

MERCHANT_ID: m9drzx7cfagcdd2xmbdq
llave_privada: sk_99f5cf4e3de24a4b9c44da577b42a0ec

https://sandbox-api.openpay.mx/v1/{MERCHANT_ID}/charges \
   -u {llave_privada}:

ejemplo peticion

curl https://sandbox-api.openpay.mx/v1/m9drzx7cfagcdd2xmbdq/charges?limit=20 -u sk_99f5cf4e3de24a4b9c44da577b42a0ec:

curl https://sandbox-api.openpay.mx/v1/m9drzx7cfagcdd2xmbdq/charges/trb0ufh9orabonzmd8vh -u sk_99f5cf4e3de24a4b9c44da577b42a0ec:

curl https://sandbox-api.openpay.mx/v1/m9drzx7cfagcdd2xmbdq/charges?status=CANCELED&created=2018-12-10 -u sk_99f5cf4e3de24a4b9c44da577b42a0ec:

ejemplo respuesta

[{"id":"trb0ufh9orabonzmd8vh","authorization":null,"operation_type":"in","method":"bank_account","transaction_type":"charge","status":"in_progress","conciliated":false,"creation_date":"2018-12-19T11:34:14-06:00","operation_date":"2018-12-19T11:34:14-06:00","description":"NESCAFÉ Cappuccino ORIGINAL ... and (10) other items","error_message":null,"order_id":"1900000030","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-20T23:59:59-06:00","currency":"MXN","payment_method":{"type":"bank_transfer","bank":"BBVA Bancomer","clabe":"000000000000000001","agreement":"0000000","name":"11271556208518488265"},"amount":10054.95},{"id":"tra4bkwpwvtez9o39qtp","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"in_progress","conciliated":false,"creation_date":"2018-12-18T10:48:11-06:00","operation_date":"2018-12-18T10:48:11-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO ... and (1) other items","error_message":null,"order_id":"100000604","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-19T10:48:10-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010100368185638","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010100368185638?width=1&height=45&text=false"},"amount":3217.55},{"id":"tru7wkxbpm2cxeyl63yh","authorization":"1913448","operation_type":"in","method":"bank_account","transaction_type":"charge","status":"completed","conciliated":true,"creation_date":"2018-12-17T17:40:54-06:00","operation_date":"2018-12-17T17:42:59-06:00","description":"COFFEE MATE stick","error_message":null,"order_id":"100000603","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-18T23:59:59-06:00","currency":"MXN","payment_method":{"type":"bank_transfer","bank":"BBVA Bancomer","clabe":"000000000000000001","agreement":"0000000","name":"11134803127218460284"},"amount":2588.96,"fee":{"amount":8.0000,"tax":1.2800,"currency":"MXN"}},{"id":"trewizuazmgk7ept8qvx","authorization":"950002","operation_type":"in","method":"store","transaction_type":"charge","status":"completed","conciliated":true,"creation_date":"2018-12-17T16:36:00-06:00","operation_date":"2018-12-17T16:53:52-06:00","description":"COFFEE MATE stick","error_message":null,"order_id":"1900000029","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-18T16:35:29-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010100832380072","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010100832380072?width=1&height=45&text=false"},"amount":3432.57,"fee":{"amount":102.0400,"tax":16.3264,"currency":"MXN"}},{"id":"tr0zcri1yfx9jiddimjz","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-17T16:32:46-06:00","operation_date":"2018-12-17T16:32:46-06:00","description":"COFFEE MATE stick","error_message":null,"order_id":"1900000028","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-18T16:32:15-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010100542890334","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010100542890334?width=1&height=45&text=false"},"amount":226.89},{"id":"trvcoty8vppe6qt4jvxo","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-17T16:20:27-06:00","operation_date":"2018-12-17T16:20:27-06:00","description":"ABUELITA  chocolate vending","error_message":null,"order_id":"1900000027","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-18T16:19:56-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010100881596594","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010100881596594?width=1&height=45&text=false"},"amount":3369.62},{"id":"trywr9iskcusz1gzxfyu","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-17T16:12:05-06:00","operation_date":"2018-12-17T16:12:05-06:00","description":"COFFEE MATE stick","error_message":null,"order_id":"1900000026","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-18T16:11:34-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010103690652775","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010103690652775?width=1&height=45&text=false"},"amount":3432.57},{"id":"trjfenekxp1plpfyegps","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-17T16:10:13-06:00","operation_date":"2018-12-17T16:10:13-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO","error_message":null,"order_id":"1900000025","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-18T16:09:42-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010101030001113","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010101030001113?width=1&height=45&text=false"},"amount":302.19},{"id":"trdgpzqndhtklrlcbgg0","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-17T15:54:21-06:00","operation_date":"2018-12-17T15:54:21-06:00","description":"Taster´s Choice Regular","error_message":null,"order_id":"1900000024","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-18T15:53:50-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010104112250793","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010104112250793?width=1&height=45&text=false"},"amount":1070.10},{"id":"tr4kxplawcixgc7sq1v6","authorization":null,"operation_type":"in","method":"bank_account","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-17T15:35:24-06:00","operation_date":"2018-12-17T15:35:24-06:00","description":"NESCAFÉ Clásico ... and (2) other items","error_message":null,"order_id":"1900000023","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-18T23:59:59-06:00","currency":"MXN","payment_method":{"type":"bank_transfer","bank":"BBVA Bancomer","clabe":"000000000000000001","agreement":"0000000","name":"11427597791018466294"},"amount":8725.60},{"id":"tr5lwa4vrwxgcvhktdzu","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-14T11:48:30-06:00","operation_date":"2018-12-14T11:48:30-06:00","description":"NESCAFÉ Cappuccino MOKA ... and (2) other items","error_message":null,"order_id":"1900000022","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-15T11:48:01-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010104107278652","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010104107278652?width=1&height=45&text=false"},"amount":747.28},{"id":"tr75t0zxosx7c3cupxob","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T17:06:58-06:00","operation_date":"2018-12-13T17:06:58-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO","error_message":null,"order_id":"1900000021","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T17:06:29-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010100276194700","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010100276194700?width=1&height=45&text=false"},"amount":185.46},{"id":"trvhrm1f09o4y8avzf7q","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T16:51:55-06:00","operation_date":"2018-12-13T16:51:55-06:00","description":"NESCAFÉ Cappuccino ORIGINAL","error_message":null,"order_id":"12147483648","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T16:51:26-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010102612696715","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010102612696715?width=1&height=45&text=false"},"amount":70.00},{"id":"trrovcf4akp5msdj5fj5","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T16:46:23-06:00","operation_date":"2018-12-13T16:46:23-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO","error_message":null,"order_id":"1900000011","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T16:45:55-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010103132246522","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010103132246522?width=1&height=45&text=false"},"amount":185.46},{"id":"traq9kgiklfvfjkdkp1m","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T15:57:28-06:00","operation_date":"2018-12-13T15:57:28-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO","error_message":null,"order_id":"100000602","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T15:57:28-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010104163298948","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010104163298948?width=1&height=45&text=false"},"amount":185.46},{"id":"tr1ucnnhwezahv1pal0n","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T13:45:29-06:00","operation_date":"2018-12-13T13:45:29-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO","error_message":null,"order_id":"100000027","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T13:45:01-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010103951324547","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010103951324547?width=1&height=45&text=false"},"amount":185.46},{"id":"tro3ualri9foyih611ge","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T13:00:22-06:00","operation_date":"2018-12-13T13:00:22-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO","error_message":null,"order_id":"100000026","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T12:59:53-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010101082507649","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010101082507649?width=1&height=45&text=false"},"amount":370.92},{"id":"trgxaez3atjqznciuqma","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T12:06:11-06:00","operation_date":"2018-12-13T12:06:11-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO","error_message":null,"order_id":"100000025","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T12:05:42-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010100446949370","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010100446949370?width=1&height=45&text=false"},"amount":185.46},{"id":"trraqobb6jrt1eaycy3f","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T11:59:45-06:00","operation_date":"2018-12-13T11:59:45-06:00","description":"NESCAFÉ Cappuccino DESCAFEINADO","error_message":null,"order_id":"100000024","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T11:59:16-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010102030940396","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010102030940396?width=1&height=45&text=false"},"amount":185.46},{"id":"trw6krzlp4rpgqyqbd5g","authorization":null,"operation_type":"in","method":"store","transaction_type":"charge","status":"cancelled","conciliated":false,"creation_date":"2018-12-13T11:06:16-06:00","operation_date":"2018-12-13T11:06:16-06:00","description":"NESCAFÉ Cappuccino ORIGINAL","error_message":null,"order_id":"100000023","customer_id":"awsnnupdgbfvogmwtd8y","due_date":"2018-12-14T11:05:48-06:00","currency":"MXN","payment_method":{"type":"store","reference":"1010102343242516","barcode_url":"https://sandbox-api.openpay.mx/barcode/1010102343242516?width=1&height=45&text=false"},"amount":70.00}]
*/

include('libs/basics.php');
include('libs/forceUTF8.php');
include('libs/querys.php');
include('libs/logs.php');
include('libs/magento_search.php');
include('libs/api-openpay-php/Openpay.php');

define('LIMIT_SLCTDS',5);

date_default_timezone_set('America/Mexico_City');

$produccion = 0;

if($produccion){
	define('MERCHANT_ID','myhbbepw14sfvxyylolr');
	define('PRIVATE_KEY','sk_094e464a249f470b9eba5cdc9a7664c6');
}else{
	define('MERCHANT_ID','m9drzx7cfagcdd2xmbdq');
	define('PRIVATE_KEY','sk_99f5cf4e3de24a4b9c44da577b42a0ec');
}

$procesar = 'cancelled';
$final    = 'Canceled';

/*
$procesar = 'completed';
$final    = 'pagado';
*/

function data_charge($a=null){
	if($a==null){ return null; }

	$b['authorization'] 	= $a->authorization;
	$b['creation_date'] 	= $a->creation_date;
	$b['currency'] 			= $a->currency;
	$b['customer_id'] 		= $a->customer_id;
	$b['operation_type'] 	= $a->operation_type;
	$b['status'] 			= $a->status;
	$b['transaction_type'] 	= $a->transaction_type;
	$b['card'] 				= null;
	$b['id'] 				= $a->id;
	$b['resourceName'] 		= $a->resourceName;
	$b['serializableData'] 	= null;

	$c = $a->card;
	if( $c != null ){
		foreach ($c as $et => $r) {
			$ty = gettype($r);
			if( $ty=='object' ){ continue; }
			if( $ty=='array' ){  continue; }

			$b['card'][ $et ] = "$r";
		}

		$b['card']['serializableData'] = null;
		if( isset( $c->serializableData ) ){
			foreach ($c->serializableData as $et => $r) {
				$ty = gettype($r);
				if( $ty=='object' ){ continue; }
				if( $ty=='array' ){  continue; }

				$b['card']['serializableData'][ $et ] = "$r";
			}
		}		
	}

	$c = $a->serializableData;
	if( $c != null ){
		foreach ($c as $et => $r) {
			$ty = gettype($r);
			if( $ty=='object' ){ continue; }
			if( $ty=='array' ){  continue; }

			$b['serializableData'][ $et ] = "$r";
		}

		$b['serializableData']['payment_method'] = null;
		if( isset( $c['payment_method'] ) ){
			foreach ($c['payment_method'] as $et => $r) {
				$ty = gettype($r);
				if( $ty=='object' ){ continue; }
				if( $ty=='array' ){  continue; }

				$b['serializableData']['payment_method'][ $et ] = "$r";
			}
		}
	}

	return $b;
}

$log = 'log/list_curl';
log_data( $log, "==============================================" );

$lopy = new magentoSearch();
if( $produccion ){
	$lopy->list_order_openpay_limit(' and sfo.entity_id >=633');
}else{
	$lopy->list_order_openpay_limit(' and sfo.entity_id < 633');
}

/* obtiene listado de ordenes de compra */
	$lopy->list_order_openpay('status','pending');
	if( $lopy->data==null ){
		$lopy->list_order_openpay('status','processing');

		if( $lopy->data==null ){
			log_data( $log, 'sin ordenes de compra a procesar' );
			return null;
		}
	}

	foreach ($lopy->data as $et => $r) {
		unset( $lopy->data[ $et ]['store_name'] );
		$lopy->data[ $et ]['openpay_data'] = null;
	}

	if( $lopy->data==null ){
		log_data( $log, 'sin ordenes de compra a procesar' );
		return null;
	}

	log_data( $log, "ordenes de compra => ".count( $lopy->data ) );

/* obteniendo datos desde openpay.com */
	$openpay = Openpay::getInstance(MERCHANT_ID, PRIVATE_KEY);
	if( $produccion ){
		Openpay::setProductionMode(true);
	}
	$i=0;

	foreach ($lopy->data as $et => $r) {
		/* obtenemos los datos de openpay */
		//tt( 'process ==> '.$r['openpay_payment_id'] );

		$a = null;
		$charge = null;
		$charge = @$openpay->charges->get( $r['openpay_payment_id'] );

		/* validaciones */
		if( $charge==null ){ continue; }

		$a = data_charge( $charge );

		tt( 'process ==> '.$r['openpay_payment_id'].' status ==> '.$a['status'] );

		if( $a['status'] == $procesar ){
			$a['status'] = $final;
			$lopy->data[ $et ]['openpay_data'] = $a;
			log_data( $log, print_r($a,true) );

			$i++;
			if($i==LIMIT_SLCTDS){ break; }
		}
	}

/* si el arreglo esta vacio terminar programa */
	if( $lopy->data==null ){
		log_data( $log, 'sin cambios' );
		return null;
	}

/* modifica datos en base de datos */
	foreach ($lopy->data as $et => $r) {
		$lopy->modif_openpay_status( $r );
		log_data( $log, 'save '.$r['increment_id'].'  ==> '.$r['openpay_data']['status'] );
	}

?>