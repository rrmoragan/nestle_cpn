<?php
/* listado de archivos de un directorio */

include('libs/basics.php');

$a = list_files_array('facturas_data/','file');

echo print_table( $a );

?>