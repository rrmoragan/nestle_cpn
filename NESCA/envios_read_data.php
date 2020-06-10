<?php

$l = null;

$fp = fopen("openpay_report/envios_tot.log", "r");
$pre = '';
$ll = '';
$reg = null;
while (!feof($fp)){
    $linea = fgets($fp);

    $b = explode( '2019-', $linea );
    if( isset($b[1]) ){

    	if( $ll ){
    		$reg[] = trim( $pre ).$ll;
    		$ll = '';
    	}

    	$pre = $linea;
    }else{
    	$ll = $ll.' '.trim( $linea );
    }
}
fclose($fp);

$lreg = null;
$i = 1;
foreach ($reg as $et => $r) {
	$b = null;
	$a = explode('cantidad pedida ==', $r);
	$b['fecha'] = $a[0];
	$b['data'] = 'cantidad pedida =='.$a[1];

	$a = explode(')  )  margen_descuento ==', $b['data']);
	$b['data'] = $a[0].') )';
	$b['calc'] = 'margen_descuento =='.$a[1];
	
	$lreg[] = $b;

	if($i==4){
		print_r($b);
	}
	$i++;
}

?>