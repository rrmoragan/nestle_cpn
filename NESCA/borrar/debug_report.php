<?php
include('libs/basics.php');
include('libs/querys.php');

$a = array(
		'7501058629951' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501058636973' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501058636980' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501058637420' => array( 'title'=>'', 'subtitle'=>'' ),
		'738377554009' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501059214545' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501059238152' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501431209107' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501431235168' => array( 'title'=>'', 'subtitle'=>'' ),
		'7613035888975' => array( 'title'=>'', 'subtitle'=>'' ),
		'10661440001377' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501058628596' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501059284623' => array( 'title'=>'', 'subtitle'=>'' ),
		'17501059273297' => array( 'title'=>'', 'subtitle'=>'' ),
		'17501059288239' => array( 'title'=>'', 'subtitle'=>'' ),
		'17501059284637' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501058629098' => array( 'title'=>'', 'subtitle'=>'' ),
		'722776001738' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501059211728' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501059219106' => array( 'title'=>'', 'subtitle'=>'' ),
		'10722776200640' => array( 'title'=>'', 'subtitle'=>'' ),
		'17501059273273' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501059273245' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501059273252' => array( 'title'=>'', 'subtitle'=>'' ),
		'7501059273689' => array( 'title'=>'', 'subtitle'=>'' )
);

foreach ($a as $et => $r) {
	$s = "SELECT 
		cpev.value_id,
		ea.attribute_code,
		cpev.value
		from catalog_product_entity_varchar as cpev
		inner join eav_attribute as ea on ea.attribute_id = cpev.attribute_id
		where entity_id = (
			select entity_id from catalog_product_entity where sku like '$et'
		)
		and ea.attribute_code IN ( 'name','nombre_secundario' )
		 ";
	$b = query( $s );
	foreach ($b as $etr => $rr) {
		switch ( $rr['attribute_code'] ) {
			case 'name': $a[ $et ]['title'] = $rr['value']; break;
			case 'nombre_secundario': $a[ $et ]['subtitle'] = $rr['value']; break;
		}
	}
}

print_r( $a );

foreach ($a as $et => $r) {
	$s = "SELECT id from rpv_nestle where sku like '$et'";
	$b = query( $s );
	if( $b!= null ){
		foreach ($b as $etr => $rr) {
			$s = "UPDATE rpv_nestle set article = '".trim(trim($r['title']).' '.trim($r['subtitle']))."' where id = ".$rr['id'];
			query($s);
			tt( $s );
		}
	}

}

?>
