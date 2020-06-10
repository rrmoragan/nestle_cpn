<?php
/* listado de archivos de un directorio */

include('libs/basics.php');
include('libs/ssh2.php');

$dir = 'facturas_data/';
$a = list_files_array('facturas_data/','file');

if( $a==null ){ tt('sin archivos'); return null; }

$ver_files = 1; // muestra los archivos que se van subiendo
$titanio = new serverSSH();

$titanio->connexion_data( $ssh_conexion_credentials );
if( $titanio->connexion_start() ){

	$titanio->connexion_sftp_start();

	foreach ($a as $et => $r) {
		$s = "upload [".$dir.$r['name']."] ==> ";
		if( $titanio->ssh_upload_file( $dir, $r['name'], $ssh_dir ) ){
			$s = $s."[ok]";
		}else{
			$s = $s."[error]";
		}

		if( $ver_files ){ tt($s); }
	}

	$titanio->connexion_sftp_close();
    $titanio->connection_close();
}

echo "\n";
?>