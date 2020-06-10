<?php

//$_POST['mkd'] = "s820b7sjhjsfejkloo9399qqr7";

if( isset( $_SERVER['TERM'] ) ){ echo "acceso denegado"; return null; }
if( $_POST==null ){ echo "acceso denegado"; return null; }
if( !isset( $_POST['mkd'] ) ){ echo "acceso denegado"; return null; }
if( $_POST['mkd'] != 's820b7sjhjsfejkloo9399qqr7' ){ echo "acceso denegado"; return null; }

$dir = opendir('mlg_reports');
$files = null;
$shipping = null;

// obteniendo archivos
while ($current = readdir($dir)){
	if( $current == "." || $current == "..") { continue; }

	if( !is_dir( $current ) ) {
		$a = explode('report_search_', $current);
		if( count($a)>1 ){
			$files[] = $current;
		}
	}
}

sort($files);
$files 		= $files[ count( $files )-1 ];

echo json_encode( array( 'f'=> $files ) );

?>