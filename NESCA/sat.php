<?php

	include('libs/basics.php');
	include('libs/querys.php');

	// 'sku', 'sat_clave', 'sat_descrip', 'sat_unidad', 'sat_clave_unidad'),

	$a=array(
		array('722776001738', '50161510', 'Endulzantes artificiales', 'Caja', 'H87'),
		array('738377554009', '52151600', 'Utensilios de cocina domésticos', 'Pieza', 'H87'),
		array('7501000913477', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7501000913484', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7501058619607', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7501058619690', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7501058628596', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7501058629098', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7501058629135', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7501058629159', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7501058629173', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7501058636973', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('7501058636980', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('7501058637420', '50161511', 'Chocolate o sustituto de chocolate', 'Pieza', 'H87'),
		array('7501059214545', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('7501059219106', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('7501059233287', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('7501059233294', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('7501059256729', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('7501059256736', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('7501431235168', '48101919', 'Vasos o tazas o tazones (mugs) o tapas de contenedores para servicio de comidas', 'Caja', 'H87'),
		array('7613031529681', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613032855888', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613032864507', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613033013553', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613033024306', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613033135453', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613034293398', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613034590572', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613034609267', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7613035376083', '48101919', 'Vasos o tazas o tazones (mugs) o tapas de contenedores para servicio de comidas', 'Caja', 'H87'),
		array('7613035970694', '48101919', 'Vasos o tazas o tazones (mugs) o tapas de contenedores para servicio de comidas', 'Caja', 'H87'),
		array('7613036272049', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('7630030318849', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7630039618636', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7630039619398', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7630039619770', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7630039620158', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7630047613685', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7630047615306', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7630047615313', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7630047615320', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7640148344364', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('7640148344371', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('10661440001377', '50161509', 'Azucares naturales o productos endulzantes', 'Caja', 'H87'),
		array('10722776200640', '50161510', 'Endulzantes artificiales', 'Caja', 'H87'),
		array('17501000912459', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('17501059238159', '48101919', 'Vasos o tazas o tazones (mugs) o tapas de contenedores para servicio de comidas', 'Caja', 'H87'),
		array('17501059273242', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('17501059273259', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('17501059273266', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('17501059273273', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('17501059273297', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('17501059273686', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('17501059273709', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('17501059284620', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('17501059284637', '50201709', 'Café instantáneo', 'Pieza', 'H87'),
		array('17501059285627', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('17501059286228', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('17501059288239', '48101919', 'Vasos o tazas o tazones (mugs) o tapas de contenedores para servicio de comidas', 'Caja', 'H87'),
		array('18801055707802', '50201709', 'Café instantáneo', 'Caja', 'H87'),
		array('NCF-A8130', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('NCF-AL-PP', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('NCF-MLN-EXL', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('NCF-MLN-LNG', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('NCF-MLN-M8120', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('NCF-MLN-MTS130', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('NSP-0000001', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('NSP-0000002', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
		array('NSP-0000003', '52141526', 'Cafeteras para uso doméstico', 'Pieza', 'H87'),
	);

foreach( $a as $r ){
	$s="SELECT entity_id, sku FROM nestle_me_114.catalog_product_entity where sku = '".$r[0]."';";
	$b=query($s);
	if($b==null){
		tt('omitiendo '.$r[0]);
	}else{
		$b=$b[0]['entity_id'];
		$s="SELECT * from catalog_product_entity_varchar where entity_id = $b and attribute_id=241";
		$c=query($s);
		if($c!=null){
			$c=$c[0]['value_id'];
			$s="UPDATE catalog_product_entity_varchar set value='".$r[1]."' where value_id=$c ;";
			query($s);
		}
		$s="SELECT * from catalog_product_entity_varchar where entity_id = $b and attribute_id=242";
		$c=query($s);
		if($c!=null){
			$c=$c[0]['value_id'];
			$s="UPDATE catalog_product_entity_varchar set value='".$r[2]."' where value_id=$c ;";
			query($s);
		}
		$s="SELECT * from catalog_product_entity_varchar where entity_id = $b and attribute_id=243";
		$c=query($s);
		if($c!=null){
			$c=$c[0]['value_id'];
			$s="UPDATE catalog_product_entity_varchar set value='".$r[3]."' where value_id=$c ;";
			query($s);
		}
		$s="SELECT * from catalog_product_entity_varchar where entity_id = $b and attribute_id=244";
		$c=query($s);
		if($c!=null){
			$c=$c[0]['value_id'];
			$s="UPDATE catalog_product_entity_varchar set value='".$r[4]."' where value_id=$c ;";
			query($s);
		}

		tt($r[0].' [ok]');
	}
}

?>
