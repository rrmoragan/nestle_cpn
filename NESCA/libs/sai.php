<?php

define('SAI_ERROR_1','error al iniciar el control con los datos sai');
define('SAI_OK_1','datos sai agregados a base local');
define('SAI_MSQL_ERROR_1','error: insertando datos en sai');
define('SAI_MSQL_ERROR_2','error: el registro no fue insertado');

define('ID_PROG','NESCA');
define('ID_STATUS',3);
define('ERROR_ID_COMPRA','id no identidicado');
define('ERROR_USER','usuario no identificado');
define('ERROR_STATUS_NAME','nombre de estado no identificado');	


define('TB1','ut_cafe_cntl');
define('TB2','ut_cafe_mlg_sai_data');

define('TB3','control_canjes');
define('TB4','canjescasa');

define('TB5','test_control_canjes');
define('TB6','test_canjescasa');

define('TB1_ST1','insert_order');
define('TB1_ST2','insert_detail');

/* LISTADOS */

function list_control($v=0){

	$s="SELECT * from ".TB1.";";
	if($v){ tt($s); }

	$a=query($s,2,$v);

	$s="SELECT count(saidat_id) as n from ".TB2.";";
	$n=query($s,2);
	$n=$n[0]['n'];

	echo BR."numero de ordenes = ".count($a);
	echo BR."numero de atributos de productos = ".$n;

	if(!$a){ tt('sin datos'.BR); return null; }

	echo print_table($a);

	return null;
}

/* MODIF */
function cntrl_modif($registro=0,$campo='',$valor=null,$v=0){
	if($registro==0){ if($v){ tt('cntrl_modif() error data1'); } return false; }
	if($campo==''){   if($v){ tt('cntrl_modif() error data2'); } return false; }

	$s="UPDATE ".TB1." set $campo='$valor' where reg_id=$registro; ";
	query($s,2);
	if($v){ tt($s); }

	return true;
}


function sai_fecha_canje($s=''){
	if($s=='') return '';

	$m=mes_min();

	/* fecha en formato 2018-10-22 16:04:29 */
	/* Del 02 Oct 2018 al 02 Oct 2018 Domiciliada */

	$a=explode(' ', $s); 		/* obteniendo fecha */
	$a=explode('-', $a[0]);		/* separando fracciones */

	$ss='Del '.str_pad($a[2],2,'0',STR_PAD_LEFT).' '.$m[ $a[1]-1 ].' '.$a[0];
	$ss=$ss.' al ';
	$ss=$ss.str_pad($a[2],2,'0',STR_PAD_LEFT).' '.$m[ $a[1]-1 ].' '.$a[0].' Domiciliada';

	return $ss;
}

function sai_sql_insert($a=null,$table='',$v=0){

	if($a==null){	if($v){ tt(); }	return '';	}
	if($table==''){	if($v){ tt(); }	return '';	}

	$b='';
	$c='';
	foreach ($a as $et => $r) {
		if($b!=''){ $b=$b.', '; }
		if($c!=''){ $c=$c.', '; }

		$b=$b.$et;
		$c=$c.( $r[0]=='int' ? $r[1]:"'".$r[1]."'" );
	}

	$s="INSERT INTO $table ($b) VALUES ($c);";

	return $s;
}

function add_sai_control($dat=null,$i=0,$v=0){
	if( $dat==null){ return null; }
	if( $dat['code']=='' ){ return null; }
	if( $dat['nregs']==0 ){ return null; }
	if( $dat['narts']==0 ){ return null; }

	$t=time();

	$a=array(
		//'Consecutivo'=>		array('int','null'),
		'CodigoEnvio'=>		array('string',$dat['code']),
		'Fecha'=>			array('string',date('Y-m-d G:i:s',$t).'.000'),
		'Descripcion'=>		array('string',$dat['code']." ".sai_fecha_canje( date('Y-m-d G:i:s',$t) ) ),
		'NumeroFilas'=>		array('int',$dat['nregs']),
		'NumeroArticulos'=>	array('int',$dat['narts']),
		'TipoEnvio'=>		array('string',''),
		'Procesadas'=>		array('int',0),
	);

	if($i){
		$s=sai_sql_insert($a,'control_canjes');	
		return $s;
	}

	return $a;
}

function add_sai_data($a=null,$i=0,$v=0){
	if($a==null) return null;

	$t=time();

	$b=array(
		//'Id_ctrl'=>				array('string',''),
		'CodigoEnvio'=>			array('string',$a['CodigoEnvio']),
		'IdSocio'=>				array('string',$a['IdSocio']),
		'Email'=>				array('string',$a['Email']),
		'Canje'=>				array('string',sai_fecha_canje( date('Y-m-d G:i:s',$t) ) ),

		'Paterno'=>				array('string',utf8_encode( strtoupper($a['Paterno']) ) ),
		'Materno'=>				array('string',utf8_encode( strtoupper($a['Materno']) ) ),
		'Nombres'=>				array('string',utf8_encode( strtoupper($a['Nombres']) ) ),
		'DireccionEnvio'=>		array('string',''),
		'Calle'=>				array('string',utf8_encode( $a['Calle']) ),
		'Colonia'=>				array('string',utf8_encode( $a['Colonia']) ),
		'Municipio'=>			array('string',utf8_encode( $a['Municipio']) ),
		'Ciudad'=>				array('string',''),
		'Estado'=>				array('string',utf8_encode( $a['Estado']) ),
		'CP'=>					array('string',utf8_encode( $a['CP'] ) ),
		'DescripcionArticulo'=>	array('string',utf8_encode( $a['DescripcionArticulo'] ) ),
		
		'CodigoArticulo'=>		array('string',ID_PROG.$a['CodigoArticulo']),
		'IdTransaccion'=>		array('int',$a['IdTransaccion']),
		'IdCanje'=>				array('string',ID_PROG),
		'IdPrograma'=>			array('string',ID_PROG),
		'IdEstatus'=>			array('string',ID_STATUS),
		'ComentariosEnvio'=>	array('string',''),
		'IdSistema'=>			array('string',ID_PROG),
		'Extension'=>			array('string',0.0),
		'Telefono'=>			array('string',$a['Telefono']),
		'HorarioPreferido'=>	array('string',''),

		'NombreAlterno'=>		array('string',utf8_encode( $a['NombreAlterno']) ),

		'FechaRegistro'=>		array('string',date('Y-m-d G:i:s',$t).'.000'),
		'FechaCargaEMG'=>		array('string',''),
		'Cantidad'=>			array('int',(int)$a['Cantidad']),
		//'Envio'=>				array('string',''),
		//'Error_envio'=>			array('string','')
	);

	if($i==1){
		$s=sai_sql_insert($b,'test_canjescasa');	
		return $s;
	}

	return $a;
}

function obten($reg='',$a=null){
	if($a==null) return '';
	if($reg=='') return '';

	foreach ($a as $et => $r) {
		if($r['reg']==$reg) return $r['reg_data'];
	}

	return '';
}

function verify_exist_order($a=null,$v=0){
	if($a==null){ if($v){ tt('verify_exist_order() => null'); } return null; }

	/* quitando elementos del arreglo que ya fueron agregados */
	foreach ($a as $et => $r) {
		$s="select * from ".TB1." where status like '".TB1_ST2."' and pedido_id=".$r['increment_id']." and producto_id=".$r['product_sku'].";";
		$b=query($s,2);
		if($b){
			unset($a[$et]);
			if($v){ tt('omitiendo => '.$r['increment_id'].' => '.$r['product_sku']); }
		}
	}

	if($a==null){ return null; }

	/* quitando elementos de la base de datos que fueron parcialmente agregados */
	foreach ($a as $et => $r) {
		$s="select * from ".TB1." where status like '".TB1_ST1."' and pedido_id=".$r['increment_id']." and producto_id=".$r['product_sku'].";";
		$b=query($s,2);
		if($b){
			$s="select saidat_id from ".TB2." where sai_id=".$b[0]['reg_id'].";";
			$c=query($s,2);
			if($c){
				foreach ($c as $etr => $rr) {
					cntrl_del_prod_attrib($rr['saidat_id'],$v);
				}
				//if($v){ tt('regenerando => '.$r['increment_id'].' => '.$r['product_sku']); }

				cntrl_del_prod($b[0]['reg_id'],$v);
				if($v){ tt('reestructurando => '.$r['increment_id'].' => '.$r['product_sku']); }
			}
		}
	}

	return $a;
}

function select_order_to_insert_cntrl_sai($status='pagado'){
	$s="SELECT "

		. " sfo.entity_id, "
		. " sfo.status, "
		. " sfo.customer_id, "
		. " sfo.increment_id, "

		. " sfoi.name as product_name, "
		. " sfoi.sku as product_sku, "
		. " sfoi.qty_ordered as product_cantidad, "

		. " sfo.billing_address_id, "
		. " sfo.shipping_address_id, "

		. " sfo.customer_email, "
		. " sfo.customer_lastname, "
		. " sfo.customer_middlename, "
		. " sfo.customer_firstname, "

		. " sfoa.street as dir_calle, "
		. " sfoa.neighborhood as dir_colonia, "
		. " sfoa.city as dir_delegacion, "
		. " sfoa.region as dir_ciudad, "
		. " sfoa.postcode as dir_cp, "
		. " sfoa.telephone as dir_tel, "
		. " sfoa.lastname as dir_u_ape_1, "
		. " sfoa.middlename  as dir_u_ape_2, "
		. " sfoa.firstname  as dir_u_name, "

		. " caev.value as dir_num, "
		. " caevb.value as dir_num_int "


		. " from sales_flat_order as sfo "

		. " inner join sales_flat_order_item as sfoi on sfoi.order_id=sfo.entity_id "
		. " inner join sales_flat_order_address as sfoa on sfoa.entity_id=sfo.shipping_address_id "
		. " inner join customer_address_entity_varchar as caev on caev.entity_id=sfoa.customer_address_id "
		. " inner join customer_address_entity_varchar as caevb on caevb.entity_id=sfoa.customer_address_id "

		. " where  "
		. " sfo.status='pagado' and  "
		. " caev.attribute_id = 252 and "
		. " caevb.attribute_id = 251 ";

	$a=query($s);
	tt($s);
	if($a==null){ return null; }

	return $a;
}

function struct_cntl($a=null){
	if($a==null) return null;

	$b=array(
		'reg_id'		=>array('int','null'),
		'status'		=>array('string',TB1_ST1),
		'sai_id'		=>array('string',''),
		'pedido_id'		=>array('string',$a['increment_id']),
		'producto_id'	=>array('string',$a['product_sku']),
		'fing'			=>array('int',time()),
		'vi'			=>array('int',1)
	);

	return $b;
}

function cntrl_insert($status='',$v=0){

	//if($v){
		tt('obteniendo datos magento');
	//}
	$a=select_order_to_insert_cntrl_sai($status); /* dbase magento */
	if($a==null){
		if($v){ tt('sin datos'); }
		return null;
	}
	tt("registros ==> ".count($a));
	return null;

	/*
	if($v){ tt('verificando registros'); }
	$a=verify_exist_order($a,$v);
	if($a==null){ if($v){ tt('sin datos'); } return null; }
	*/

	$c=null;
	$id=0;
	foreach ($a as $et => $r) {

		/* agregando registro control sai */

		/* struct_cntl()		estructura el arreglo y el tipo de datos */
		/* sai_sql_insert()		genera la instruccion insert control */
		$s = sai_sql_insert( struct_cntl($r) , TB1);
		$id=query( $s, 2 );
		tt(" insert ut_cafe_cntl ==> $s");

		if( $id==null ){
			if($v){ tt('error insertando valores de control'); }
			return null;
		}

		if( $v ){ tt('insert control => '.$r['increment_id'].' => '.$r['product_sku'].' => '.TB1_ST1); }

		/* agregando detalles al control */
		if($id){

			/* insert into ".TB2." (saidat_id,sai_id,prod,reg,reg_type,reg_data,vi) values (); */
			foreach ($r as $etr => $rr) {

				$s="insert into ".TB2." values ( null, $id, '".$r['product_sku']."','".$etr."', 'string', '$rr', 1 ); ";

				$id2=query( $s, 2 );
				tt($s);
				if($id2==null){ return null; }
			}
			
			/* actualizando registro de status */
			cntrl_modif($id,'status',TB1_ST2);

			if($v){ tt('insert control => '.$r['increment_id'].' => '.$r['product_sku'].' => '.TB1_ST2); }
		}
	}

	if($v){ tt(BR.BR); }
	return null;
}

function sai_id_next($v=0){
	$s=" select DISTINCT(sai_id) from ut_cafe_cntl ORDER by sai_id DESC LIMIT 0,1;";
	$a=query($s,2);
	if($a==null){ return ID_PROG."000001"; }
	if($a[0]['sai_id']==''){ return ID_PROG."000001"; }

	if($v) pre($a);

	$a=$a[0]['sai_id'];
	if($v){ tt('ultimo registro => '.$a); }

	$a=explode(ID_PROG, $a);
	$a=(int)$a[1];
	$a+=1;

	$a=ID_PROG.str_pad($a,6,'0',STR_PAD_LEFT);

	if($v){ tt('siguiente registro => '.$a); }

	return $a;
}

function valid_cntrl_insert($v=0){
	if($v){ tt('validando registros existentes en SAI'); }

	$s="SELECT * from ut_cafe_cntl where status like 'insert_detail' and sai_id = '';";
	$a=query($s,2);
	if($a==null){ return 0; } /* sin modificaciones */

	if($v){ tt('validando '.count($a).' productos'); }

	$i=0;
	foreach ($a as $et => $r) {
		$code = sai_exist( $r['pedido_id'], $r['producto_id'], $v );
		if( $code ){
			cntrl_modif( $r['reg_id'], 'sai_id', $code, $v );
			if($v){ tt('modificando => '.$r['pedido_id'].' producto => '.$r['producto_id']); }
			$i++;
		}
	}

	return $i;
}

/* BORRADO */

function cntrl_del_prod($id=0,$v=0){
	if($id==0) return false;

	$s="DELETE from ".TB1." where reg_id=$id;";
	query($s,2);

	return true;
}

function cntrl_del_prod_attrib($id=0,$v=0){
	if($id==0) return false;

	$s="DELETE from ".TB2." where saidat_id=$id;";
	query($s,2);

	return true;
}

function cntrl_purge($v=0){

	$s="select saidat_id from ".TB2.";";
	$a=query($s,2);
	if($a!=null){
		foreach ($a as $et => $r) {
			$s="delete from ".TB2." where saidat_id=".$r['saidat_id'].";";
			query($s,2);
		}
	}

	$s="select reg_id from ".TB1.";";
	$a=query($s,2);
	if($a!=null){
		foreach ($a as $et => $r) {
			$s="delete from ".TB1." where reg_id=".$r['reg_id'].";";
			query($s,2);
		}
	}

	if($v){ echo BR; }
	return true;
}

/* SAI */

function sai_npedidos($v=0){
	$s="select  
		utc.pedido_id

		from ut_cafe_cntl as utc

		where
		utc.status like 'insert_detail' and
		utc.sai_id = '' and
		utc.vi=1
		;";
	$a=query($s,2);

	if($a==null){ return null; }

	$b=null;
	foreach ($a as $et => $r) {
		$b[]=$r['pedido_id'];
	}

	return $b;
}

function sai_nprods($v=0){
	$s="select  
		utc.reg_id , 
		utc.pedido_id,
		ucmsd.prod, 
		ucmsd.reg, 
		ucmsd.reg_data  

		from ut_cafe_cntl as utc
		inner join ut_cafe_mlg_sai_data as ucmsd on ucmsd.sai_id=utc.reg_id

		where

		utc.status like 'insert_detail' and
		utc.sai_id = '' and
		utc.vi=1 and
		ucmsd.reg='product_cantidad'
		;";
	$a=query($s,2);
	if($a==null) return null;

	return $a;
}

function npedidos($v=0){

	/* validando que los canjes no esten en SAI */
	$a=sai_nprods(); /* servidor puente */
	$a=valid_sai_insert_duplicated($a,$v);

	/* numero de pedidos */
	$a=sai_npedidos($v); /* servidor puente */
	if(!$a){ if($v){ tt('procesando 0 pedidos'); } return null; }
	$n1=count($a);

	if($v){ foreach ($a as $et => $r) { tt('procesando pedido '.$r); } }

	/* numero de productos */
	$a=sai_nprods(); /* servidor puente */
	$n2=0;
	foreach ($a as $et => $r) {
		$n2+=$r['reg_data'];
	}
	$n2=(int)$n2;

	if($v){ tt('procesando '.$n2.' productos'); }
	if(!$n2){ return null; }

	/* codigo de control a insertar */
	$code=sai_id_next($v);

	/* obtiene el INSERT para control de canjes sai */
	$ss=add_sai_control(
		array(
			'code'  => $code,
			'nregs' => $n1,
			'narts' => $n2,
		),
		1
	);
	if($v){ tt($ss); }
	
	/* obteniendo id de sai */
	/* conexion mysql */
	//$id=query($ss);
	/* conexion sqlserver */
	$id=query_sql($ss);
	if($v){ tt($id); }

	/* seleccionando datos por producto */
	foreach ($a as $et => $r) {
		$id=$r['reg_id'];
		$s="select * from ut_cafe_mlg_sai_data where sai_id=$id and vi=1;";

		$b=query($s,2);
		if($b){
			//if($v){ pre($r); pre($b); }

			$c=array(
				'CodigoEnvio'=>			$code,
				'IdSocio'=>				obten('customer_id',$b) ,
				'Email'=>				obten('customer_email',$b) ,
				'Paterno'=>				(obten('customer_lastname',$b)) ,
				'Materno'=>				(obten('customer_middlename',$b)) ,
				'Nombres'=>				(obten('customer_firstname',$b)) ,
				'Calle'=>				obten('dir_calle',$b) ,
				'Colonia'=>				obten('dir_colonia',$b) ,
				'Municipio'=>			obten('dir_delegacion',$b) ,
				'Estado'=>				obten('dir_ciudad',$b) ,
				'CP'=>					obten('dir_cp',$b) ,
				'DescripcionArticulo'=>	obten('product_name',$b) ,
				'CodigoArticulo'=>		obten('product_sku',$b) ,
				'IdTransaccion'=>		$r['pedido_id'] ,
				'Telefono'=>			obten('dir_tel',$b) ,
				'NombreAlterno'=>		
					obten('dir_u_name',$b).' '.
					obten('dir_u_ape_1',$b).' '.
					obten('dir_u_ape_2',$b),
				'Cantidad'=>			obten('product_cantidad',$b) ,
			);

			print_r($c);

			/* insertando pedidos por producto en sai */
			$s=add_sai_data($c,1);
			if($v){ tt( BR.$s ); }

			if($v){ tt($s); }
			/* conexion mysql */
			//$id_sai=query($s);
			/* conexion sqlserver */
			$id_sai=query_sql($s);

			cntrl_modif( $r['reg_id'], 'sai_id', $code );
		}
	}

	if($v){ echo BR; }
	return null;
}

function sai_list($cod='',$v=0){

	if($cod==''){ $cod='%NESCA%'; }
	$s="SELECT * from control_canjes where CodigoEnvio like '$cod';";
	/* mysql */
	//$a=query($s);
	/* conexion sqlserver */
	$a=query_sql($s);

	if($a==null){ tt('SAI sin registros'); }

	echo print_table($a);

	if($cod!='%NESCA%'){
		$s="SELECT * from test_canjescasa where CodigoEnvio = '$cod';";
		/* mysql */
		//$a=query($s);
		/* conexion sqlserver */
		$a=query_sql($s);

		if($a==null){ tt('SAI sin datos de productos'); }

		echo print_table($a);
	}

	return null;
}

function sai_exist($code='',$article='',$v=0){
	if($code=='') return '';
	if($code=='') return '';

	$s="SELECT CodigoEnvio from test_canjescasa
	where 
	CodigoArticulo = '".ID_PROG.$article."' and
	IdTransaccion = '$code'; ";

	/* mysql */
	//$a=query($s);
	/* conexion sqlserver */
	$a=query_sql($s);

	if($a==null){
		if($v){ tt('sin codigo de envio'); }
		return '';
	}
	if($a[0]['CodigoEnvio']==null) return '';

	if($v){ tt('codigo envio => '.$a[0]['CodigoEnvio']); }
	return $a[0]['CodigoEnvio'];
}

function valid_sai_insert_duplicated($a=null,$v=0){
	if($a==null){ return null; }

	foreach ($a as $et => $r) {
		$s="SELECT CodigoEnvio FROM test_canjescasa where CodigoArticulo = '".ID_PROG.$r['prod']."' and IdTransaccion = '".$r['pedido_id']."'";
		/* mysql */
		//$b=query($s);
		/* conexion sqlserver */
		$b=query_sql($s);

		if($b!=null){
			cntrl_modif( $r['reg_id'], 'sai_id', $b[0]['CodigoEnvio'] );
			unset($a[$et]);
		}
	}
	
	return $a;
}

?>