<?php
/*
 * libreria utilizada para ver y modificar todo lo relacionado con los productos
 */
if( !defined('MAGENTO_LIB_PRODUCTS') ){

	define('MAGENTO_LIB_PRODUCTS','v1.3');
	include('basics.php');
	include('querys.php');

	// replace class newProduct{
	class mProduct{
		public $data = null;

		// obtiene los datos de un producto por su id
		public function product_id( $id=0 ){
			if( $id==0 ){ return false; }

			$this->data = null;

			$tables = array(
				array( 'name' => 'datetime', 'table' => 'catalog_product_entity_datetime' ),
				array( 'name' => 'decimal', 'table' => 'catalog_product_entity_decimal' ),
				array( 'name' => 'gallery', 'table' => 'catalog_product_entity_gallery' ),
				array( 'name' => 'int', 'table' => 'catalog_product_entity_int' ),
				array( 'name' => 'media_gallery', 'table' => 'catalog_product_entity_media_gallery' ),
				array( 'name' => 'text', 'table' => 'catalog_product_entity_text' ),
				array( 'name' => 'url_key', 'table' => 'catalog_product_entity_url_key' ),
				array( 'name' => 'varchar', 'table' => 'catalog_product_entity_varchar' )
			);

			$s = '';
			foreach ($tables as $et => $r) {
				if( $s!='' ){ $s = $s."\n union \n"; }

				$table = $r['table'];
				$name  = $r['name'];

				$s = $s."SELECT 
					cped.entity_id,
					cped.value_id,
					ea.attribute_code,
					cped.value,
					'$name' as ltable
					from $table cped
					inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
					where 
					cped.entity_id = $id ";
			}

			//echo "\n sql ==> $s";
			$a = query( $s );
			if( $a==null ){ return 0; }

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['attribute_code'] ] = $r;
			}

			$this->data = $b;
			return count( $this->data );
		}

		/* regresa los atributos basicos de un producto por el sku */
		public function product_sku( $sku='' ){
			if( $sku == '' ){ return null; }

			$s = "SELECT entity_id from catalog_product_entity where sku like '$sku'";
			$a = query($s);
			if( $a==null ){ return null; }

			$d = $this->product_data( $a[0]['entity_id'] );

			// SELECT * from catalog_product_entity where sku NOT like '%MCH-%';
			return $d;
		}
		/* regresa los atributos basicos de un producto por el id del producto */
		public function product_data( $id_p=0 ){
			if($id_p == 0){ return null; }

			$s = "SELECT entity_id,type_id,sku,created_at,updated_at from catalog_product_entity where entity_id = $id_p";
			$a = query($s);
			if($a==null){ return null; }

			$b = null;
			foreach ($a[0] as $et => $r) {
				$b[ $et ] = $r;
			}

			$s = "SELECT cped.value_id,ea.attribute_code,cped.value,'varchar' as data_table
				from catalog_product_entity_varchar as cped
				inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
				where cped.entity_id = $id_p
				union
				SELECT cped.value_id,ea.attribute_code,cped.value,'url_key' as data_table
				from catalog_product_entity_url_key as cped
				inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
				where cped.entity_id = $id_p
				/* pendiente catalog_product_entity_tier_price */
				union
				SELECT cped.value_id,ea.attribute_code,cped.value,'text' as data_table
				from catalog_product_entity_text as cped
				inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
				where cped.entity_id = $id_p
				/* pendiente catalog_product_entity_media_gallery_value */
				union
				SELECT cped.value_id,ea.attribute_code,cped.value,'media_gallery' as data_table
				from catalog_product_entity_media_gallery as cped
				inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
				where cped.entity_id = $id_p
				union
				SELECT cped.value_id,ea.attribute_code,cped.value,'int' as data_table
				from catalog_product_entity_int as cped
				inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
				where cped.entity_id = $id_p
				/* pendiente catalog_product_entity_group_price */
				union
				SELECT cped.value_id,ea.attribute_code,cped.value,'gallery' as data_table
				from catalog_product_entity_gallery as cped
				inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
				where cped.entity_id = $id_p
				union
				SELECT cped.value_id,ea.attribute_code,cped.value,'decimal' as data_table
				from catalog_product_entity_decimal as cped
				inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
				where cped.entity_id = $id_p
				union
				SELECT cped.value_id,ea.attribute_code,cped.value,'datetime' as data_table
				from catalog_product_entity_datetime as cped
				inner join eav_attribute as ea on ea.attribute_id = cped.attribute_id
				where cped.entity_id = $id_p
				";
			$a = query($s);
			if( $a!=null ){
				foreach ($a as $et => $r) {
					$b[ $r['attribute_code'] ] = $r['value'];
				}
			}

			return $b;
		}
		/* list todos los productos */
		public function product_list($mch=1,$type=''){

			if( $mch==0 ){
				$ss = " sku NOT like '%MCH-%' ";
			}
			if( $type!='' ){
				if($ss!=''){ $ss = $ss." and "; }
				$ss = $ss." type_id like '$type' ";
			}

			$s = "SELECT entity_id,type_id,sku,created_at,updated_at from catalog_product_entity".( ($mch==0)?" where ".$ss:"" );
			$a = query($s);
			if($a==null){
				return null;
			}

			$d = null;
			foreach ($a as $et => $r) {
				$d[ $r['sku'] ] = $r;
			}

			return $d;
		}
		/* lista los atributos de los productos */
		public function product_attribs(){
			$el = "attribute_id,attribute_code,backend_type,frontend_input,is_required,is_user_defined,is_unique";
			$s = "SELECT $el  from eav_attribute where entity_type_id=4";
			$a = query($s);
			if($a==null){ return null; }

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['attribute_code'] ] = $r;
			}

			return $b;
		}
		/* regresa el stock de un articulo */
		public function product_qty( $sku='' ){

			$s = "SELECT * from cataloginventory_stock_item where product_id = ( select entity_id from catalog_product_entity where sku like '$sku' )";
			$a = query($s);
			if( $a==null ){ return 0; }

			return $a[0]['qty'];
		}
		/* obtiene el valor de un atributo seleccionable */
		public function attribute_option( $attrib='', $opcion='' ){
			if( $attrib=='' ){ return 0; }
			if( $opcion=='' ){ return 0; }

			$s = "SELECT
				eaov.*
				from eav_attribute_option_value as eaov
				where 
				eaov.option_id IN (

				select
				eao.option_id
				from eav_attribute_option as eao
				where
				eao.attribute_id IN ( select attribute_id  from eav_attribute where attribute_code like 'marca' )

				)
				and eaov.value like 'Nespresso'
				";

			$s = "SELECT
				eaov.*
				from eav_attribute_option_value as eaov
				where 
				eaov.option_id IN (

				select
				eao.option_id
				from eav_attribute_option as eao
				where
				eao.attribute_id IN ( select attribute_id  from eav_attribute where attribute_code like '$attrib' )

				)
				and eaov.value like '$opcion'
				and eaov.store_id = 0;
				";
			$a = query( $s );
			if( $a==null ){ return 0; }
			return $a[0]['value_id'];
		}
	}

	define('MAGENTO_LIB_CATEGS','v1.1');
	class mCateg{
		public function product_all_categ( $sku='' ){
			if( $sku=='' ){ return null; }

			$s = "SELECT
				ccev.*,
				eaa.attribute_code

				from catalog_category_entity as cce
				inner join catalog_category_entity_varchar as ccev on ccev.entity_id = cce.entity_id
				inner join eav_attribute as eaa on eaa.attribute_id = ccev.attribute_id

				where
				cce.entity_id IN (
					select category_id from catalog_category_product where product_id IN (
						select entity_id from catalog_product_entity where sku like '$sku'
					)
				)

				order by ccev.entity_id ASC, eaa.attribute_code ASC
				";
			$a = query( $s );
			if( $a==null ){ return null; }

			$b = null;
			foreach ($a as $et => $r) {
				if( $r['attribute_code'] == 'name' ){
					$b[ $r['entity_id'] ] = $r['value'];
				}
			}

			return $b;
		}
	}
}

//$zzz = new newProduct();

//echo print_table( $zzz->product_list(0,'simple') );
//echo print_table( $zzz->product_list_attrib(0,'simple',array('status','visibility')) );
//echo print_table( $zzz->product_attribs() );

/*
$prod = new mProduct();
$prod->product_id( 141 );
echo print_table( $prod->data, 40 );
*/

?>