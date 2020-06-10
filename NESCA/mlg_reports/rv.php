<?php

$dir = opendir('.');
$i = 0;

$max = 0;
while ($file = readdir($dir)){
	if( is_dir($file) ){ continue; }

	//echo "\n $file";
	$a = explode( '_CPMN-ventas-', $file );
	if( count($a)>1	){
		//echo "\n $file";
		$b = explode('_', $a[1]);
		if( count($b)>1 ){ continue; }
		$b = explode( '.', $a[1] );
		if( $b[0]>$max ){
			$max = $b[0];
			echo "\n $file";
		}
	}
}

if( $max>0 ){
	return '{"file":"_CPMN-ventas-'.$max.'.csv"}';
}

return "{'file':''}";

?>
