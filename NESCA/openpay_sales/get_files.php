<?php

include('../libs/basics.php');

$dir = opendir('.');
$i = 0;

$a = null;
while ($file = readdir($dir)){
	if( is_dir($file) ){ continue; }
	$ff = explode('.sales_', $file);
	if( count( $ff )==1 ){ continue; }
	
	$fp = fopen($file, 'r');
	if( !$fp ){
		echo "\n\t $file ... error";
		continue;
	}

	$string = fgets($fp);
	if( !$string ){
		echo " ... vacio ";
		continue;
	}

	$string = json_decode( $string,true );
	$a[] = array(
		'order_id' => $string['order_id'],
                'creation_date' => $string['creation_date'],
                'status' => $string['status'],
	);
}

echo print_table( $a );

echo "\n";

?>
