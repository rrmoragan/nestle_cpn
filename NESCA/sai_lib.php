<?php

/* v1.1
	bug  direccion, extencion del numero telefonico a float
*/

class Sai_lib{

	public $sales_order_data = null;

	public $data = array(
		'sales_order' => '',
		'program' => 'NESCX',
		'envio' => 0,
		'canje' => 0,
	);

	public $data_json = null;

	public $program = null;

	/* v0.2 */
	public function carga($v=0){
		$cntrlc = null;
		$casac  = null;
		$sinsert = '';

		if( $this->data['sales_order'] == '' ){ return false; }
		if( $this->data['envio'] == 0 ){ return false; }
		if( $this->data['canje'] == 0 ){ return false; }

		$sinsert .= "\n call ==> ".print_r( $this->data,true );
		if( $v ){ echo "\n carga ==> ".print_r( $this->data,true ); }

		if( $this->select_salles_order( $this->data['sales_order'], $v ) ){

			$codigo_envio = $this->data['program'].sprintf( "%06d", $this->data['envio'] );
			$t = time();
			$date = date('Y-m-d G:i:s',$t).'.000';
			$dia  = date('d',$t);
			$anio = date('Y',$t);

			$mes = array( 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic' );
			$mes = $mes[ date('m',$t)-1 ];

			$str_date = "Del $dia $mes $anio al $dia $mes $anio Domiciliada";

			$ln = count( $this->sales_order_data );
			$lnn = 0;

			foreach ($this->sales_order_data as $et => $r) {
				$lnn = $lnn + ( (int)$r['qty_ordered'] );
			}

			$s = "INSERT INTO MLG_CANJES.dbo.control_canjes ( CodigoEnvio, Fecha, Descripcion, NumeroFilas, NumeroArticulos, TipoEnvio, Procesadas) ".
				"VALUES ( '$codigo_envio', '$date', '$codigo_envio $str_date', $ln, $lnn, '', 0 );\n";
			
			$sinsert .= "\n $s";

			$cntrlc = array(
				"CodigoEnvio" => "$codigo_envio",
				"Fecha" => "$date",
				"Descripcion" => "$codigo_envio $str_date",
				"NumeroFilas" => "$ln",
				"NumeroArticulos" => "$lnn",
				"TipoEnvio" => "",
				"Procesadas" => "0"
			);

			$s = '';
			foreach ($this->sales_order_data as $et => $r) {
				if( $s != '' ){ $s = $s.','; }

				$sku = $r['sku'];

				if( isset( $r['sku_alterno'] ) && $r['sku_alterno']!='' ){
					$sku = $r['sku_alterno'];
				}

				$canje = $this->data['program'].sprintf( "%06d", $this->data['canje'] );
				if( $r['telefono_b'] == '' ){ $r['telefono_b'] = 0; }

				if( isset( $r['pices_tot'] ) ){
					$r['qty_ordered'] = $r['qty_ordered']*$r['pices_tot'];
				}

				$s = $s."\n( ".
					" '$codigo_envio', ".
					" '".$r['customer_id']."', ".
					" '".$r['customer_email']."',  ".
					" '$str_date',  ".

					" '".$r['customer_lastname']."', '', '".$r['customer_firstname']."', '',  ".

					" '".$r['calle']."', '".$r['colonia']."', '".$r['municipio']."', '', '".$r['estado']."', '".$r['cp']."',  ".
					" '".$r['telefono']."', '".$r['telefono_b']."', '', '".$r['authorized_person']."', '$date', '', ".

					" '".$r['product_name']."',  ".
					" '".$sku."', '".$r['increment_id']."', '$canje', '".$this->data['program']."', '3', '', '".$this->data['program']."',  ".$r['qty_ordered_tot']." ".
					" )";

				$this->data['canje'] ++;

				$casac[ $sku ] = array(
					"CodigoEnvio" 			=> "$codigo_envio",
					"IdSocio" 				=> $r['customer_id'],
					"Email" 				=> $r['customer_email'],
					"Canje" 				=> "$str_date",
					"Paterno" 				=> $r['customer_lastname'],
					"Materno" 				=> "",
					"Nombres" 				=> $r['customer_firstname'],
					"DireccionEnvio" 		=> "",
					"Calle" 				=> $r['calle'],
					"Colonia" 				=> $r['colonia'],
					"Municipio" 			=> $r['municipio'],
					"Ciudad" 				=> "",
					"Estado" 				=> $r['estado'],
					"CP" 					=> $r['cp'],
					"Telefono" 				=> $r['telefono'],
					"Extension" 			=> $r['telefono_b'],
					"HorarioPreferido" 		=> "",
					"NombreAlterno" 		=> $r['authorized_person'],
					"FechaRegistro" 		=> "$date",
					"FechaCargaEMG" 		=> "",
					"DescripcionArticulo" 	=> $r['product_name'],
					"CodigoArticulo" 		=> $sku,
					"IdTransaccion" 		=> $r['increment_id'],
					"IdCanje" 				=> "$canje",
					"IdPrograma" 			=> $this->data['program'],
					"IdEstatus" 			=> "3",
					"ComentariosEnvio" 		=> "",
					"IdSistema" 			=> $this->data['program'],
					"Cantidad" 				=> $r['qty_ordered_tot'],
				);
			}

			$s = "INSERT INTO MLG_CANJES.dbo.canjescasa (CodigoEnvio, IdSocio, Email, Canje, Paterno, Materno, Nombres, DireccionEnvio, Calle, Colonia, Municipio, Ciudad, Estado, CP, Telefono, Extension, HorarioPreferido, NombreAlterno, FechaRegistro, FechaCargaEMG, DescripcionArticulo, CodigoArticulo, IdTransaccion, IdCanje, IdPrograma, IdEstatus, ComentariosEnvio, IdSistema, Cantidad) values $s ;\n";
			$sinsert .= "\n $s";

			$this->data = $sinsert;

			$this->data_json = null;
			$this->data_json['control_canjes'] = $cntrlc;
			$this->data_json['canjescasa']     = $casac;

			if( $v ){ echo "\n carga ==> ".print_r( $this->data_json,true ); }
			return true;
		}

		return false;
	}

	public function set_sales_order( $data='', $v=0 ){
		if( $data=='' ){
			//echo "\n set_program() ==> null "; 
			return false;
		}
		$this->data['sales_order'] = $data;
		//if( $v ){ echo "\n set_sales_order() ".print_r( $data,true ); }
		return true;
	}
	public function set_program( $data='',$v=0 ){
		if( $data=='' ){
			//echo "\n set_program() ==> null "; 
			return false;
		}
		$this->data['program'] = $data;
		//if( $v ){ echo "\n set_program() ".print_r( $data,true ); }
		return true;
	}
	public function set_envio( $data=0,$v=0 ){
		if( $data==0 ){
			//echo "\n set_envio() ==> null ";
			return false;
		}
		$this->data['envio'] = $data;
		//if( $v ){ echo "\n set_envio() ".print_r( $data,true ); }
		return true;
	}
	public function set_canje( $data=0,$v=0 ){
		if( $data==0 ){
			//echo "\n set_canje() ==> null ";
			return false;
		}
		$this->data['canje'] = $data;
		//if( $v ){ echo "\n set_canje() ".print_r( $data,true ); }
		return true;
	}

	public function piezas($data=null){
		if($data==null){ return 1; }

		$msg = "\n piezas calculadas ==> ";

		if( isset( $data['envio_sai'] ) ){
			$es = (int)$data['envio_sai'];
			if( $es>0 ){
				return $es;
			}
		}

		$pp = 1;
		$minp = 1;

		if( isset( $data['configurable_max_prods'] ) ){
			$data['configurable_max_prods'] = (int)$data['configurable_max_prods'];
			if( $data['configurable_max_prods']>0 ){
				$pp = $data['configurable_max_prods'];
			}
		}
		if( isset( $data['sku_alterno_cantidad'] ) ){
			$data['sku_alterno_cantidad'] = (int)$data['sku_alterno_cantidad'];
			if( $data['sku_alterno_cantidad']>0 ){
				$minp = $data['sku_alterno_cantidad'];
			}
		}

		$res = $pp*$minp;

		return $res;
	}

	public function select_salles_order( $sales_order='',$v=0 ){
		if( $v ){ echo "\n select_salles_order()"; }

		/* obteniendo datos de la orden de venta */
			if( $v ){ echo "\n select_salles_order() ==> paso 1"; }

			$a = $this->select_salles_order_data( $sales_order, $v );
			if( $a==null ){
				if( $v ){ echo "\n select_salles_order() => null"; }
				return false;
			}

			$b = null;
			$b = fixUTF8($a);
			//print_r($b);

		/* completando datos de producto */
			if( $v ){ echo "\n select_salles_order() ==> paso 2"; }

			foreach ($b as $et => $r) {
				$piezas = 1;
				$a = $this->select_product_data( $r['product_id'] );
				if( $a!=null ){
					$b[ $et ]['product_name'] = trim($a['name']).' '.trim($a['nombre_secundario']);
					$b[ $et ]['pices_tot'] = 1;

					$piezas = $this->piezas( $a );
					
					$tot = $b[ $et ]['qty_ordered']*$piezas;

					$b[ $et ]['qty_ordered_tot'] = "$tot";
					$b[ $et ]['sku_alterno'] = '';
					if( isset( $a['sku_alterno'] ) ){
						$b[ $et ]['sku_alterno'] = $a['sku_alterno'];
					}
				}
			}
			//print_r($b);

		/* obteniendo direccion de envio y completando datos */
			if( $v ){ echo "\n select_salles_order() ==> paso 3"; }

			$s = $this->sql_user_address_data( $this->user_address_id( $b[0]['customer_id'], $v ), $v );
			if( $v ){ echo "\n sql ==> $s"; }
			$a = query( $s );
			if( $a == null ){
				echo "\n error: obtener datos de envio ==> ".$b[0]['customer_id']."\n";
				return false;
			}

			$c = null;
			foreach ($a as $et => $r) {
				$c[ $r['attribute_code'] ] = $r['value'];
			}
			//print_r( $c );

			foreach ($b as $et => $r) {
				if( !isset( $c['calle'] ) ){
					$b[ $et ]['calle'] = '';
					if( isset( $c['street'] ) ){ $b[ $et ]['calle'] = $c['street']; }
				}
				if( !isset( $c['colonia'] ) ){
					$b[ $et ]['colonia'] = '';
					if( isset( $c['neighborhood'] ) ){ $b[ $et ]['colonia'] = $c['neighborhood']; }
				}
				if( !isset( $c['municipio'] ) ){
					$b[ $et ]['municipio'] = '';
					if( isset( $c['city'] ) ){ $b[ $et ]['municipio'] = $c['city']; }
				}
				if( !isset( $c['estado'] ) ){
					$b[ $et ]['estado'] = '';
					if( isset( $c['city'] ) ){ $b[ $et ]['estado'] = $c['city']; }
				}
				if( !isset( $c['cp'] ) ){
					$b[ $et ]['cp'] = '';
					if( isset( $c['postcode'] ) ){ $b[ $et ]['cp'] = $c['postcode']; }
				}

				if( $c['num_ext'] != null ){ $b[ $et ]['calle'] = $b[ $et ]['calle'].' número '.$c['num_ext']; }
				//echo "\n\n num_ext ==> ".print_r( $b[ $et ]['calle'],true )."\n";

				if( isset( $c['num_int'] ) )
				if( $c['num_int'] != null ){ $b[ $et ]['calle'] = $b[ $et ]['calle'].' interior '.$c['num_int']; }
				
				$b[ $et ]['telefono'] = '';
				if( $c['telephone'] != 'NULL' ){ $b[ $et ]['telefono'] = $c['telephone']; }

				$b[ $et ]['telefono_b'] = '';
				if( isset( $c['telephone_extension'] ) )
				if( $c['telephone_extension'] != 'NULL' ){
					$b[ $et ]['telefono_b'] = 0;
					if( is_float( $c['telephone_extension'] ) ){
						$b[ $et ]['telefono_b'] = $c['telephone_extension'];
					}
				}

				$b[ $et ]['authorized_person'] = '';
				if( isset( $c['authorized_person'] ) )
				if( $c['authorized_person'] != 'NULL' ){ $b[ $et ]['authorized_person'] = $c['authorized_person']; }	
			}

		$this->sales_order_data = fixUTF8($b);
		//print_r($b);

		return true;
	}

	public function user_address_id( $customer_id='',$v=0 ){
		if( $customer_id=='' ){
			if($v){ echo "\n user_address_id() ==> null"; }
			return '';
		}

		$s = "SELECT 
			cae.entity_id
			
			from customer_address_entity as cae
			inner join customer_address_entity_varchar as caev on caev.entity_id = cae.entity_id
			inner join eav_attribute as ea on ea.attribute_id = caev.attribute_id
			where 
			cae.parent_id = $customer_id
			and cae.is_billing=0
			and ea.attribute_code IN( 'city' )
			and caev.value <> '.....'

			order by cae.entity_id DESC ";
		if( $v ){ echo "\n sql ==> $s"; }
		$a = query( $s );
		if( $a==null ){
			echo "\n error ==> no se encontro direccion de envio";
			return 0;
		}

		$a = $a[0]['entity_id'];

		if( $v ){ echo "\n user_address_id() ==> $a"; }
		return $a;
	}

	private function select_salles_order_data( $sales_order='', $v=0 ){
		if( $sales_order=='' ){
			if( $v ) echo "\n select_salles_order_data() ==> null";
			return '';
		}

		$b = null;

		$s = "SELECT 
			sfo.entity_id as order_id,
			sfo.increment_id,
			sfo.customer_id,
			sfo.customer_email,
			sfo.customer_firstname,
			sfo.customer_lastname,

			sfoi.product_id,
			sfoi.sku,
			sfoi.name as product_name,
			sfoi.qty_ordered

			from sales_flat_order as sfo
			inner join sales_flat_order_item as sfoi on sfoi.order_id = sfo.entity_id
			where 
			sfo.status like 'pagado' and 
			sfo.increment_id like '$sales_order' ";
		$a = query( $s );
		if( $a==null ){ return null; }

		foreach ($a as $et => $r) {
			$i = (int)$r['qty_ordered'];
			$a[ $et ]['qty_ordered'] = "$i";
		}

		$order_id = $a[0]['order_id'];

		/* obteniendo datos de direccion */
		$s = "SELECT
			sfoa.street as calle,
			sfoa.neighborhood as colonia,
			sfoa.city as municipio,
			sfoa.region_id as estado_id,
			sfoa.region as estado,
			sfoa.postcode as cp
			from sales_flat_order_address as sfoa
			where 
			sfoa.parent_id = $order_id 
			and sfoa.is_billing = 0
			and sfoa.street <> '.....'
			and sfoa.city <> '.....'
			and sfoa.postcode <> '99999' ";
		$b = query( $s );

		if( $b==null ){ echo "\n error: direccion errornea\n"; }
		if( $b==null ){
			$c = array( 'calle','colonia','municipio','estado_id','estado','cp' );
			foreach ($a as $et => $r) {
				foreach ($c as $et => $r) {
					$a[ $et ][ $r ] = null;
				}
			}
		}else{
			foreach ($a as $et => $r) {
				foreach ($b as $etr => $rr) {
					foreach ($rr as $etrr => $rrr) {
						$a[ $et ][	 $etrr ] = $rrr;
					}
				}
			}
		}

		return $a;
	}

	private function select_product_data( $product_id='' ){
		if( $product_id=='' ){ return ''; }

		$s = "SELECT
			cpev.value as name,
			cpevb.value as nombre_secundario

			FROM catalog_product_entity_varchar as cpev
			inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id

			inner join catalog_product_entity_varchar as cpevb
			inner join eav_attribute as eab on eab.attribute_id = cpevb.attribute_id

			where 
			    cpev.entity_id =  $product_id
			and eaa.attribute_code like 'name'
			and cpevb.entity_id = $product_id
			and eab.attribute_code like 'nombre_secundario'
			";

		$s = "SELECT cpev.*, eaa.attribute_code
					FROM catalog_product_entity_varchar as cpev
					inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
					where cpev.entity_id = $product_id
				union
				SELECT cpev.*, eaa.attribute_code
					FROM catalog_product_entity_datetime as cpev
					inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
					where cpev.entity_id = $product_id
				union
				SELECT cpev.*, eaa.attribute_code
					FROM catalog_product_entity_decimal as cpev
					inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
					where cpev.entity_id = $product_id
				union
				SELECT cpev.*, eaa.attribute_code
					FROM catalog_product_entity_int as cpev
					inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
					where cpev.entity_id = $product_id
				union
				SELECT cpev.*, eaa.attribute_code
					FROM catalog_product_entity_text as cpev
					inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
					where cpev.entity_id = $product_id
				union
				SELECT cpev.*, eaa.attribute_code
					FROM catalog_product_entity_url_key as cpev
					inner join eav_attribute as eaa on eaa.attribute_id = cpev.attribute_id
					where cpev.entity_id = $product_id
				";

		$a = query($s);
		if($a==null){ return null; }

		$b = null;
		foreach ($a as $et => $r) {
			$b[$r['attribute_code'] ] = $r['value'];
		}

		return $b;
	}

	private function sql_user_address_data( $id_address=0 ){
		if( $id_address==0 ){ return ''; }

		$s = "SELECT
			cev.value_id,cev.attribute_id,cev.entity_id,cev.value,ea.attribute_code
			from customer_address_entity_int as cev
			inner join eav_attribute as ea on ea.attribute_id = cev.attribute_id
			where cev.entity_id = $id_address

		union
		select
			cev.value_id,cev.attribute_id,cev.entity_id,cev.value,ea.attribute_code
			from customer_address_entity_text as cev
			inner join eav_attribute as ea on ea.attribute_id = cev.attribute_id
			where cev.entity_id = $id_address

		union
		select
			cev.value_id,cev.attribute_id,cev.entity_id,cev.value,ea.attribute_code
			from customer_address_entity_varchar as cev
			inner join eav_attribute as ea on ea.attribute_id = cev.attribute_id
			where cev.entity_id = $id_address ";

		return $s;
	}

	public function program_status(){
		tt('program_status()');

		$this->program_list();
		print_table( $this->program );
		return true;
	}

	/* lista los programas dados de alta en sai y los valores maximos para CodigoEnvio y IdCanje */
	private function program_list(){
		tt('program_list()');

		$this->program=null;

		$s = "SELECT distinct(program) from sai_control";
		echo "\n $s";
		$a = query($s);
		if($a==null){ return false; }

		$c = null;
		foreach ($a as $et => $r) {
			$prog = $r['program'];

			$s = "SELECT 
				  '$prog' as program,
				  ( select num_envio from sai_control where program like '$prog' order by num_envio DESC limit 0,1 ) as CodigoEnvio,
				  ( select num_canje  from sai_control where program like '$prog' order by num_canje  DESC limit 0,1 ) as IdCanje
				from sai_control
				limit 0,1";
			echo "\n $s";
			$b = query($s);
			if( $b != null ){
				$c[ $prog ] = $b[0];
			}
		}

		$this->program = $c;
		return true;
	}
}

/*
CREATE TABLE `nestle_me_114`.`sai_control` (
  `id_sai` INT NOT NULL AUTO_INCREMENT,
  `program` VARCHAR(45) NULL,
  `num_envio` INT NULL,
  `num_canje` INT NULL,
  `sales_order` VARCHAR(45) NULL,
  `sku` VARCHAR(45) NULL,
  `product_name` VARCHAR(45) NULL,
  `qty` INT NULL,
  `email` VARCHAR(255) NULL,
  `fecha` DATETIME NULL,
  `name` VARCHAR(128) NULL,
  `lastname` VARCHAR(128) NULL,
  `status` VARCHAR(45) NULL,
  `comment` VARCHAR(45) NULL,
  PRIMARY KEY (`id_sai`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'listado control de canjes';
*/

?>
