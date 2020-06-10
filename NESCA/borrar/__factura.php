<?php

include('libs/basics.php');
include('libs/querys.php');
include('libs/server_args.php');
include('libs/factura_lib.php');

date_default_timezone_set('America/Mexico_City');

$server = new serverArgs();

$action = 0;

if( $server->arg( '--list' ) ){
	$action = 1;

	$lf = new FacturaIn1();

	echo print_table( $lf->list_orden_billing() );

	return true;
}

if( $server->arg( '--all' ) ){
	$action = 1;	

	$lf = new FacturaIn1();

	$regs = $lf->list_orden_billing();

	if($regs!=null){
		tt('procesando ordenes para ser facturadas');

		foreach ($regs as $et => $r) {
			tt( 'procesando '.$r['increment_id'] );
			if( $lf->data( $r['increment_id'] ) ){
				if( $lf->structure() ){
					$s = $lf->in1_formato();
					output_file( 'facturas_data/', $lf->factura['factura'].'.in1', $s );
				}else{
					tt('error estructurando archivo in1');
				}
			}else{
				tt('sin datos de orden de compra');
			}
			$lf->buffer_null();
		}
	}else{
		tt('sin ordenes para facturar');
	}
}

if( $server->arg( '--allpruebas' ) ){
	$action = 1;	

	$lf = new FacturaIn1();
	$lf->pruebas = 1;

	$regs = $lf->list_orden_billing();

	if($regs!=null){
		tt('procesando ordenes para ser facturadas');

		foreach ($regs as $et => $r) {
			tt( 'procesando '.$r['increment_id'] );
			if( $lf->data( $r['increment_id'] ) ){
				if( $lf->structure() ){
					$s = $lf->in1_formato();
					output_file( 'facturas_data/', $lf->factura['factura'].'.in1', $s );
				}else{
					tt('error estructurando archivo in1');
				}
			}else{
				tt('sin datos de orden de compra');
			}
			$lf->buffer_null();
		}
	}else{
		tt('sin ordenes para facturar');
	}
}

if( !$action ){ echo help(); }
?>