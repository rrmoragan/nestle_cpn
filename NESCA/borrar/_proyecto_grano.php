<?php

include('libs/basics.php');
include('libs/querys.php');

define('PG_VER','0.1');

function help(){
	echo "\n proyecto_grano_lib ver ".PG_VER."\n";
	echo "\n proyecto_grano.php -<order> <... ... ...>";
	echo "\n\t -<order>";
	echo "\n\t -new_seller \t\t agrega un nuevo vendedor";
	echo "\n\t\t ... code_vendedor name last_name";
	echo "\n\t -list_seller \t\t lista todos los vendedores";
	echo "\n";

	return null;
}

function process_argv(){
	$argv = $GLOBALS['argv'];

	switch( $argv[1] ){
		case '-new_seller':
			$error = 0;
			if( !isset( $argv[2] ) ){ $error++; }
			if( !isset( $argv[3] ) ){ $error++; }
			if( !isset( $argv[4] ) ){ $error++; }
			if(  $error ){
				help();
				return false;
			}

			if( !is_seller( $argv[2] ) ){
				if( !new_seller( $argv[2], $argv[3], $argv[4] ) ){
					tt('error al crear el vendedor');
				}else{
					print_test( $argv[2] );
				}
			}else{
				tt($argv[2]." ==> este código de vendedor ya existe\n");
				return false;
			}

			break;
		case '-list_seller':
			echo print_table( list_all_seller() );
			break;
		default:
			help();
			return false;
			break;
	}
	return true;
}

function is_seller( $code='' ){
	if( $code == '' ){ return 0; }

	$s = "select * from grano_vendedor where code like '$code'";
	$a = query( $s );
	if($a==null){ return 0; }

	return $a[0]['id'];
}

function new_seller( $code='', $name='', $last_name='' ){
	if( $code == '' ){ return false; }
	if( $name == '' ){ return false; }

	$name = htmlentities( $name );
	$last_name = htmlentities( $last_name );

	$s = "insert into grano_vendedor values( null, '$code', '', '$name', '$last_name', 1 )";
	$id = query( $s );
	//tt($s);
	if($id==null){ return false; }

	$s = "insert into grano_codes values( null, $id, 'PACK-0001', 1 )";
	query( $s );
	$s = "insert into grano_codes values( null, $id, 'PACK-0002', 1 )";
	query( $s );
	$s = "insert into grano_codes values( null, $id, 'PACK-0003', 1 )";
	query( $s );
	$s = "insert into grano_codes values( null, $id, 'PACK-0004', 1 )";
	query( $s );
	$s = "insert into grano_codes values( null, $id, 'PACK-0005', 1 )";
	query( $s );
	return true;
}

function print_test( $code='' ){
	if( $code=='' ){ return false; }

	$s = "SELECT * from grano_machine_change where status=1 limit 0,1";
	$a = query($s);
	if($a==null){
		tt('sin número de maquinas para testear');
		return false;
	}

	echo "\n proyecto grano - test";
	echo "\n vendedor:: ".$code;
	echo "\n machine:: ".$a[0]['uid_machine'];

	return true;
}

function list_all_seller(){
	$s = "SELECT code,name,las_name,status from grano_vendedor;";
	$a = query($s);
	if($a==null){
		tt('sin vendedores registrados');
		return null;
	}

	foreach ($a as $et => $r) {
		if($r['code']=='CPMN_MORA'){ unset($a[ $et ]); }
	}

	return $a;
}


if( $GLOBALS['argc'] == 1 ){
	help();
	return;
}

process_argv();

?>