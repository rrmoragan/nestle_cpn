<?php

include('libs/magento_lib.3.php');

define('PROGRAM','CPMN');

class magentoReport{


	public function report_envios(){

		$ld = $this->filtra_ventas();

		echo print_table( $ld );

	}

	public function filtra_ventas(){
		tt('filtra_ventas()');

		$d = new report();
		$dd = $d->report_list_sales_order_status( 'pagado' );
		if($dd==null){ return null; }
		print_r($dd);

		$cab = array(
			''
		);

		$i = 1;
		$dat = null;
		foreach ($dd as $et => $r) {
			$dc = $d->report_search_sales_order( $r );
			if( $dc==null ){
				tt( "$r ==> null" );
				continue;
			}

			$dc = $dc[0];

			if($i==1){
				print_r($dc);
			}
			$i++;
		}

		return $dat;
	}

}

?>