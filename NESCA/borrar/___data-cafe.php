<?php

include('libs/basics.php');
include('libs/querys.php');
include('libs/sai.php');
include('libs/logs.php');

$v=0;

date_default_timezone_set('America/Mexico_City');

function help(){
	$s=	BR.'data-cafe.php <opciones ...>'.
		BR.'    --icontrol          obtiene los datos para SAI y los almacena en la base de datos puente.'.
		BR.'    --purge             purga todos los datos de la base de datos puente.'.
		BR.'    --isai              inserta registro puente en SAI'.
		BR.'    --verbose           muestra detalles del proceso que se este ejecutando'.
		BR.'    --lcontrol          lista los datos en base de datos puente'.
		BR.'    --modif             modifica un registro del control'.
		BR.'    --modif'.
		BR.'       --col=...        campo a modificar'.
		BR.'       --dat=...        dato a ingredar'.
		BR.'       --reg=...        numero de registro a modificar'.
		BR.'    --isainext          presenta id del siguiente registro en SAI '.
		BR.'    --sailist           lista los pedidos registrados en SAI'.
		BR.'       --code=...       lista todos los productos registrados en SAI por el CodigoEnvio'.
		BR.BR;

	return $s;
}

function select_function($a=null,$v=0){
	if($v){ tt('select_function()'); }
	if($a==null){ echo help(); return null; }

	foreach ($a as $et => $r) {
		if( $r=='--verbose' ){
			$v=1;
			unset($a[ $et ]);

			if($v){ tt('verbose activo.'); }
		}
	}

	foreach ($a as $et => $r) {
		switch ($r) {
			case '--icontrol':
				if($v){ tt('iniciando registros para el control con sai'); }
				cntrl_insert('pagado',$v);
				//valid_cntrl_insert($v);
				return null;
				break;
			case '--purge':
				if($v){ tt('purgando registros para el control con sai'); }
				cntrl_purge($v);
				return null;
				break;
			case '--isai':
				if($v){ tt('insertando registros con sai'); }
				npedidos($v);
				return null;
				break;
			case '--lcontrol':
				if($v){ tt('muestra elementos de control'); }
				list_control($v);
				return null;
				break;
			case '--modif':
				if($v){ tt('modificando registros'); }

				$reg=0;
				$campo='';
				$val=null;

				foreach ($a as $et => $r) {
					$b=explode('=', $r);
					if( isset($b[1]) ){
						switch ( $b[0] ) {
							case '--col': $campo = $b[1]; break;
							case '--dat': $val   = $b[1]; break;
							case '--reg': $reg   = $b[1]; break;
						}
					}
				}

				cntrl_modif( $reg, $campo, $val, $v );

				return null;
				break;
			case '--isainext':
				tt( sai_id_next($v).BR );
				return null;
				break;
			case '--sailist':
				$code=0;
				foreach ($a as $et => $r) {
					$b=explode('=', $r);
					if( isset($b[1]) ){
						if($b[0]=='--code'){ $code=$b[1]; }
					}
				}
				sai_list($code,$v);
				return null;
				break;
		}
	}

	if($v){
		tt('opcion no encontrada');
	}

	echo help();
	return null;
}

return select_function($_SERVER['argv'],$v);
?>