<?php

include('libs/basics.php');
include('libs/querys.php');
include('libs/magento_lib.php');

$a = array(
	'opcion1'=>array(
		'id'=>'list-categs',
		'title'=>'Listado de categorías'
	),
	'opcion2'=>array(
		'id'=>'list-products',
		'title'=>'Listado de todos los productos con sus atributos'
	),
	'opcion3'=>array(
		'id'=>'list-products&d=...',
		'title'=>"d ==> contine el listado de los atributos que se requieren, filtrando el resto de los atributos."
	),
	'opcion4'=>array(
		'id'=>'list-products-categ',
		'title'=>'Listado de productos por categoría'
	),
);

function help($a=null){
	echo "\n<pre> https://cafeparaminegocio.com.mx/NESCA/cafe_productos.php?v=[opcion id]&d=opcion \n";
	echo print_table($a);
	echo '</pre>';
	return true;
}

if( isset($_GET['v']) ){

	$dat = new Magento_Lib();
	
	$opc = $_GET['v'];

	switch ($opc) {
		case 'list-categs':  		
			$dat->list_products_categs();
			break;
		case 'list-products':
			if( !isset( $_GET['d'] ) ){ return null; }

			$dat->list_products_all( $_GET['d'] ); 
			echo( json_encode ( fixUTF8( $dat->data ) ) );
			break;
		case 'list-products-categ': 
			$dat->list_products_categ( $_GET['categ'] ); 
			break;
		case 'help':
			help($a);
                        return null;
                        break;
		
		default:
			help($a);
			return null;
			break;
	}

	return null;
}

return null;
?>
