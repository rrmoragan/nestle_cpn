<?php

//$_POST['mkd'] = "s820b7sjhjsfejkloo9399qqr7";

if( $_POST==null ){ echo "acceso denegado"; return null; }
if( !isset( $_POST['mkd'] ) ){ echo "acceso denegado"; return null; }
if( $_POST['mkd'] != 's820b7sjhjsfejkloo9399qqr7' ){ echo "acceso denegado"; return null; }

$dir = opendir('mlg_reports');
$files = null;
$shipping = null;

 while ($current = readdir($dir)){
 	if( $current == "." || $current == "..") { continue; }

 	if( !is_dir( $current ) ) {
 		$a = explode('CPMN_gral_', $current);
 		if( count($a)>1 ){
 			$files[] = $current;
 		}
 		$a = explode('CPMN_envios_', $current);
 		if( count($a)>1 ){
 			$shipping[] = $current;
 		}
 	}
 }

sort($files);
sort($shipping);
$files 		= $files[ count( $files )-1 ];
$shipping 	= $shipping[ count( $shipping )-1 ];

echo json_encode( array( 'f'=> $files, 'sh'=> $shipping ) );

?>