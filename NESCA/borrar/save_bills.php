<?php
/* respaldar ventas */

include('libs/bills_tables.php');
include('libs/basics.php');
include('libs/querys.php');

function list_all_tables(){
	$s="SELECT * from information_schema.tables where TABLE_SCHEMA LIKE '".DB_NAME."'";
	$a=query($s);
	if($a==null){ return null; }

	$b=null;
	foreach ($a as $et => $r) {
		$b[ $r['TABLE_NAME'] ] = $r;
	}

	return $b;
}

function filtra_tablas_nulas($list_tables=null){
	if( $list_tables==null ){ return null; }

	$a = list_all_tables();
	$c = null;

	foreach ($list_tables as $et => $r) {
		if( $a[ $r ]['ENGINE']=='MEMORY' ){ continue; }
		if( $a[ $r ]['TABLE_ROWS']>0 ){
			$c[ $r ] = $a[ $r ];
		}
	}

	return $c;
}

function npag($n=0,$lim=100){
	if($n==0){ return 0; }
	if( $lim>=$n ){ return 1; }

	$pag = $n / $lim;
	$pag_int = (int)$pag;
	if( $pag>$pag_int ) $pag_int++;

	return $pag_int;
}

function select_sql_data_page($table='',$page=1,$lim=100){
	if($table==''){ return null; }

	$ini = 0;
	$page -=1;
	$page = $page * $lim;
	$lim -= 1;

	$s = "SELECT *  from $table limit 0,$lim";
	$a = query($s);
	if($a==null){ return null; }

	$s = '';
	foreach ($a as $et => $r) {
		$ss = '';
		foreach ($r as $etr => $r) {
			if( $ss!='' ){ $ss = $ss.", "; }
			$ss = $ss."'$r'";
		}
		if( $s != '' ){ $s = $s.","; }
		$s = $s."\n($ss)";
	}

	$s = $s.";";

	return $s;
}

function select_sql_data($list_tables=null){
	if( $list_tables==null ){ return null; }

	$lim = 100;

	$data = null;
	foreach ($list_tables as $et => $r) {
		tt( $r['TABLE_NAME']." ==> ".$r['TABLE_ROWS'] );

		$pag = npag( $r['TABLE_ROWS'], $lim );
		$s = '';
		$t = $r['TABLE_NAME'];
		for($i=1;$i<=$pag;$i++){
			$s=select_sql_data_page( $t, $i, $lim );
		}
		$s = "\n\nINSERT INTO $t values $s";
		echo $s;
	}

	return null;
}


tt('listado de tablas');
$a = bills_tables();

tt('quitando tablas nulas');
$a = filtra_tablas_nulas($a);

tt('seleccionando registros');
select_sql_data( $a ); 	

?>