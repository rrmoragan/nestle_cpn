<?php

echo "\n Validación de integridad de datos en las ordenes de venta de magento 1.9 y 1.14";

include('libs/basics.php');

define('ACCESO_DENEGADO',"\nAcceso denegado\n");

function help(){
	$a = array(
		array( 'process' => '-valid', 'descrip' => 'inicia el proceso de validacion de las ordenes de compra con los productos relacionados a las mismas y muestra sus diferencias' ),
		array( 'process' => '', 'descrip' => '' ),
	);

	echo print_table( $a );
	return true;
}

function process(){
	$prcss = args_list();

	$if = 0;
	foreach ($prcss as $et => $r) {
		foreach ($r as $etr => $rr) {
			switch ( $etr ) {
				case '-valid':
					break;
				default:
					$if++;
					echo "\n funcion $etr no reconocida";
					break;
			}
		}
	}

	if( $if ){ return false; }

	$prcss = $prcss[ $et ];

	foreach ($prcss as $et => $r) {
		switch ( $et ) {
			case '-valid':
				echo "\n funcion ==> $et ==> data ==> ".print_r( $r,true );
				return true;
				break;
		}
	}

	return false;
}

if( !is_terminal() ){ echo ACCESO_DENEGADO; return null; }
if( nargs() == 1 ){ help(); return null; }

if( process() ){ return null; }
help(); 
return null;

?>