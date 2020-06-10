<?php

$v=0;

include('basics.php');
include('querys.php');

tt( 'data conexion host :'.SQLS_HOST );
tt( 'data conexion user :'.SQLS_USER );
tt( 'data conexion dbase :'.SQLS_NAME );

$a=query_sql('show tables',null,1);

if($a!=null){
	print_r($a);
}

?>