<?php
/*
paso 1 determina si es un vendedor
paso 2 muestra los paquetes relacionados a el vendedor

*/

if( !defined('PROJECT_GRNO') ){

	if( !defined('LIB_BASICS') ){ include('basics.php'); }
	if( !defined('LIB_QUERYS') ){ include('querys.php'); }
	include('logs.php');

	define('PROJECT_GRNO','1.0');
	define('PACK_PG','PACK-000');

	class Proyecto_Grano{

		public $log = 'proyecto_grano';
		public $data = array();
		public $prod = null;
		public $user = null;
		public $pack = '';
		public $machine_change = null;
		public $free_machine = null;
		public $sales = null;
		public $sales_oder = 0;

		public function is_vendedor( $code='' ){
			log_data($this->log,'is_vendedor()');
			log_data($this->log,'code ==> '.$code);

			$code = trim($code);
			if($code==''){ return 0; }

			$code = strtoupper($code);

			$n = strlen( $code );
			$P = $code[ $n-1 ];
			$this->pack = '';
			log_data($this->log,'PACK ==> '.$P);

			switch ($P) {
				case 'A':	$this->pack = 1; break;
				case 'B':	$this->pack = 2; break;
				case 'C':	$this->pack = 3; break;
				case 'D':	$this->pack = 4; break;
				case 'E':	$this->pack = 5; break;
			}
			log_data($this->log,'PACK ==> '.$this->pack);

			$code = substr($code, 0, ($n-1) );
			log_data($this->log,'code ==> '.$code);

			$this->data['process'] = $P;
			$this->data['vendedor_code'] = $code;

			$s = $this->sql_is_vendedor( $code );
			$a = query($s);

			if( $a==null ){ return 0; }
			$a = $a[0]['id'];

			$this->data['vendedor'] = $a;

			$lpack = $this->list_pack_for_vendedor();

			$this->data['pack_select'] = $lpack[ $this->pack-1 ]['code'];
			if($P=='D'){
				$this->data['pack_select_b'] = $lpack[ $this->pack ]['code'];
			}
			$this->pack = $this->data['pack_select'];

			$this->data['pack'] = null;
			$this->data['npack'] = 0;
			log_data($this->log,'product select ==> '.$this->data['pack_select']);

			return $a;
		}

		public function is_user( $email='' ){
			log_data( $this->log, 'is_user()' );

			if($email==''){ return 0; }

			$s = $this->sql_is_user( $email );
			$a = query($s);
			if($a==null){ return 0; }
			$a = $a[0];

			$this->user = $a;

			return $a['id_user'];
		}

		public function is_machine_change( $machine='' ){
			log_data($this->log,'is_machine_change()');

			if($machine==''){ return false; }

			$s = $this->sql_is_machine_change($machine);
			$a = query($s);
			if($a==null){ return false; }

			$a = $a[0];
			$this->machine_change = $a;
			log_data($this->log,'is_machine_change data ==> '.print_r($a,true));

			return $a['id_machine'];
		}

		public function machine_change_attrib_change($machine='',$campo='',$val=''){
			log_data($this->log,'machine_change_attrib_change()');
			if($machine==''){ return false; }
			if($campo==''){ return false; }

			$id_machine = $this->is_machine_change($machine);
			$s = $this->sql_machine_change_attrib_change($id_machine,$campo,$val);
			query($s);

			return true;
		}

		public function user_new_valid($p=null){
			if( $p==null ){ return null; }

			if( !isset( $p['uname'] ) ){ return null; }
			if( !isset( $p['ulastname'] ) ){ return null; }
			if( !isset( $p['uemail'] ) ){ return null; }
			if( !isset( $p['vendor'] ) ){ return null; }
			if( !isset( $p['utel'] ) ){ $p['utel'] = ''; }

			$p['uname'] = trim( $p['uname'] );
			$p['ulastname'] = trim( $p['ulastname'] );
			$p['uemail'] = trim( $p['uemail'] );
			$p['vendor'] = trim( $p['vendor'] );
			$p['utel'] = trim( $p['utel'] );

			if( $p['uname'] == '' ){ return null; }
			if( $p['ulastname'] == '' ){ return null; }
			if( $p['uemail'] == '' ){ return null; }
			if( $p['vendor'] == '' ){ return null; }

			$data = array(
				'uname' => $p['uname'],
				'ulastname' => $p['ulastname'],
				'uemail' => $p['uemail'],
				'vendor' => $p['vendor'],
				'utel' => $p['utel'],
				'fing' => date( 'Y-m-d G:i:s', time() ),
			);

			return $data;
		}

		public function user_new($p=null){
			log_data( $this->log, 'user_new()' );

			if( $this->is_user( $p['uemail'] ) ){
				log_data( $this->log, 'user exist!' );
				return 0;
			}
			if( $this->is_code_machine() ){
				log_data( $this->log, 'esta maquina ya fue dada de alta por otro usuario.' );
				return 0;
			}

			/* validando datos de usuario */
			$s = $this->sql_user_new( $this->user_new_valid($p) );
			$id_user = query( $s );

			if($id_user==null){ return 0; }

			$a = null;
			$s = $this->sql_user_data_add( $id_user, 'telephone', $p['utel'] );
			query($s);
			
			$s = $this->sql_user_data_add( $id_user, 'code_vendedor', $p['vendor'] );
			query($s);
			
			$s = $this->sql_user_data_add( $id_user, 'code_maquina', $p['umaquina'] );
			query($s);

			return $id_user;
		}
		/* lista los codigos de los paquetes que el usuario ha comprado */
		public function user_list_shopping($p=null){
			if($p==null){ log_data( $this->log, 'user_list_shopping(null)' ); return null; }

			log_data( $this->log, 'email ==> '.$_POST['uemail'] );
			log_data( $this->log, 'machine ==> '.$_POST['umaquina'] );

			if( !isset($_POST['uemail']) ){ log_data( $this->log, 'email ==> null' ); return null; }
			if( !isset($_POST['umaquina']) ){ log_data( $this->log, 'machine ==> null' ); return null; }

			$email = trim($_POST['uemail']);
			$machine = trim($_POST['umaquina']);

			if($email==''){ log_data( $this->log, 'email ==> null' ); return null; }
			if($machine==''){ log_data( $this->log, 'machine ==> null' ); return null; }

			$id_user = $this->is_user($email);
			log_data( $this->log, 'user ==> '.$id_user );

			if( !$id_user ){ return null; }

			$s = $this->sql_user_list_shopping_code($email,$machine);
			$a = query($s);

			if($a==null){ log_data( $this->log, 'user data ==> null' ); return null; }

			$b = null;
			foreach ($a as $et => $r) {
				$b[] = $r['sku'];
			}
			log_data( $this->log, 'user data ==> '.print_r($b,true) );

			return $b;
		}
		/* cambia los datos de un usuario */
		public function user_id_change($id_user=0,$campo='',$val=''){
			if($id_user==0){ return false; }
			if($campo==''){ return false; }

			$s = $this->sql_user_id_change($id_user,$campo,$val);
			query($s);

			return true;
		}
		/* obtiene los datos del usuario */
		public function user_id_data($id_user=0){
			if($id_user==0){ return null; }

			$sql = $this->sql_user_id_data($id_user);
			$u = query( $sql[0] );
			if($u==null){ return null; }

			$u = $u[0];
			unset($sql[0]);
			if( $sql!=null ){
				foreach ($sql as $et => $r) {
					$b = query( $r );
					if( $b!=null ){
						foreach ($b as $etr => $rr) {
							$u['data']['data_tag'] = $rr['data_value'];
						}
					}
				}
			}

			return $u;
		}

		public function is_code_machine($machine=''){
			if($machine==''){ return false; }

			$s = $this->sql_search_machine_code($machine);
			$id = query($s);
			if( $id==null ){ return false; }

			return true;
		}
		/* lista todos los paquetes relacionados al vendedor */
		public function list_pack_for_vendedor(){
			log_data( $this->log, 'list_pack_for_vendedor()' );

			if( !isset( $this->data['vendedor'] ) ){ return null; }
			$id = $this->data['vendedor'];

			$s = $this->sql_list_pack_for_vendedor($id);
			$a = query($s);
			foreach ($a as $et => $r) {
				$a[$et]['select'] = 1;
			}

			/* no hay codigos para el vendedor */
			if( $a==null ){ return null; }

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['code'] ] = $this->pack( $r['code'] );
				$b[ $r['code'] ]['select'] = 1;
			}

			$this->data['pack'] = $b;
			$this->data['npack'] = count($b);

			$s = '';
			foreach ($a as $et => $r) {
				$s .= "\n".$r['code'];
			}
			log_data( $this->log, $s );

			return $a;
		}

		public function valid_compra($p=null){
			if($p==null){ return false; }

			$a = array(
				'name' => 'required',
				'lastname' => 'required',
				'email' => 'required',
				'calle' => 'required',
				'numero' => 'required',
				'name_int' => 'no_required',
				'cp' => 'required',
				'pais' => 'required',
				'colonia' => 'required',
				'deleg' => 'required',
				'estado' => 'required',
				'tel' => 'required',
				'extencion' => 'no_required',
			);

			/* determinando si existen las variables */
			foreach ($a as $et => $r) {
				/* si no existe */
				if( !isset( $p['envio'][ $et ] ) ){
					if( $r=='required' ){
						tt( 'envio['.$et.']'.' ==> no existe' );
						return false;
					}else{
						tt( 'envio['.$et.']'.' ==> null' );
						$p[$et]='';
					}
				}else{
					/* si existe */
					$p['envio'][ $et ] = trim( $r );
					if( $p['envio'][ $et ]=='' ){
						if( $r=='required' ){
							tt( 'envio['.$et.']'.' ==> null' );
							return false;
						}
						tt( 'envio['.$et.']'.' ==> '.$p['envio'][ $et ] );
					}
				}
			}

			/* validar email usuario */
			if( !isset( $p['uemail'] ) ){ tt( 'email'.' ==> no existe' ); return false; }else{
				if( !filter_var($p['uemail'], FILTER_VALIDATE_EMAIL) ){ tt( 'email'.' ==> fail' ); return false; }
			}

			/* validando que existan paquetes */
			if( !isset( $p['pack'] ) ){	tt( 'paks'.' ==> no existe' ); return false; }else{
				if( $p['pack']==null ){ tt( 'paks'.' ==> null' ); return false; }
			}

			/* validando codigo vendedor */
			if( !isset( $p['vendor'] ) ){ tt( 'vendor'.' ==> no existe' ); return false; }else{
				if( $p['vendor']=='' ){ tt( 'vendor'.' ==> null' ); return false; }
			}

			/* validando exista codigo maquina */
			if( !isset( $p['umaquina'] ) ){ tt( 'umaquina'.' ==> no existe' ); return false; }else{
				if( $p['umaquina']=='' ){ tt( 'umaquina'.' ==> null' ); return false; }
			}

			$this->data = $p;

			return true;
		}
		/* filtra packs */
		public function filtra_packs($p=null){
			if($p==null){ return null; }

			if( isset($p['pack']) && $p['pack']!=null ){
				foreach ($p['pack'] as $et => $r) {
					if( $r['select']!=1 ){ unset( $p['pack'][$et] ); }
				}
			}

			return $p;
		}
		/* obtiene los datos y productos relacionados a un paquete de productos, sku de producto */
		public function pack($code=''){
			log_data($this->log,'pack()');

			if($code==''){ return null; }

			$s = $this->sql_pack_id( $code );
			$a = query( $s );

			if($a==null){ return null; }

			$a = $a[0];
			$id = $a['entity_id'];
			log_data($this->log,'id product ==> '.$id);
			$a['data'] = $this->product_id( $a['entity_id'] );

			/* obteniendo qty de paquetes */
			$s = $this->sql_catalog_product_group_data( $a['entity_id'] );
			$b = query($s);
			$pqty = null;
			if( $b ){ foreach ($b as $et => $r) { $pqty[ $r['linked_product_id'] ] = $r; } }

			/* listado de productos para un paquete */
			$s = $this->sql_pack( $id );
			$b = query( $s );
			if( $b!=null ){
				foreach ($b as $et => $r) {
					/* obtiene datos de producto */
					if( isset( $this->prod[ $r['entity_id'] ] ) ){
						$b[ $et ]['data'] = $this->prod[ $r['entity_id'] ];
					}else{
						$this->prod[ $r['entity_id'] ] = $this->product_id( $r['entity_id'] );
						/* agregando productos por paquete */
						if( isset( $pqty[ $r['entity_id'] ] ) ){
							$this->prod[ $r['entity_id'] ]['pack_qty'] = $pqty[ $r['entity_id'] ];
						}
						/* fin agregando productos por paquete */
						$b[ $et ]['data'] = $this->prod[ $r['entity_id'] ];
					}
				}
				$a['prods'] = $b;
			}


			return $a;
		}

		public function product_sku( $sku='' ){
			if($sku==''){ return null; }

			$s = $this->sql_product_sku($sku);
			$a = query($s);
			if($a==null){ return null; }
			$id_prod = $a[0]['entity_id'];

			$a = $this->product_id( $id_prod );
			if($a==null){ return null; }

			if( !isset($a['entity_id']) ){ $a['entity_id'] = $id_prod; }

			return $a;
		}

		public function product_id( $id_prod=0 ){
			if($id_prod==0){ return null; }
			$a = null;

			$s = $this->sql_product_id( $id_prod );
			$a = query($s);

			if($a==null){ return null; }

			$b = null;
			foreach ($a as $et => $r) {
				$b[ $r['attribute_code'] ] = $r;
			}
			$a = null;

			return $b;
		}

		public function list_vendedor(){
			tt('list_vendedor()');

			$s = $this->sql_list_vendedor();
			$a = query($s);
			if($a==null){ return null; }

			$this->data = $a;

			return $a;
		}

		public function get_packs(){
			if( isset( $this->data['pack'] ) ){ return $this->data['pack']; }

			return null;
		}
		/* guarda una ventao de un paquete con costo cero */
		public function save_venta( $p=null ){
			log_data($this->log,'save_venta()');

			if($p==null){ return false; }

			/* magento y su inventario */
				$pack[ $p['pack_select'] ] = $this->pack( $p['pack_select'] );

			/* obteniendo id de usuario */
				log_data($this->log,'validando usuario ===============');

				$id_user = $this->is_user( $p['uemail'] );
				/* si no existe el usuario crealo */
				if( $id_user==0 ){
					$id_user = $this->user_new($p);
					log_data($this->log,'new user id ==> '.$id_user);
				}
				if( $id_user==0 ){ return false; }
				log_data($this->log,'user ==> '.$id_user);
			/* listado de productos */
				log_data($this->log,'listando productos ===============');
				if( !isset( $p['pack_select'] ) ){
					log_data( $this->log,'articulo no seleccionado' );
					return false;
				}

				log_data( $this->log,'procesando ==> '.$p['pack_select'] );
				if( !isset( $this->data['pack'][ $p['pack_select'] ] ) ){
					log_data( $this->log,'articulo no encontrado' );
					return false;
				}

				$pack = null;
				$prods = null;
				/* contiene los datos basicos del paquete */
				$pack[ $p['pack_select'] ] = $this->pack( $p['pack_select'] );
				$pack[ $p['pack_select'] ]['machine'] = $p['umaquina'];
			/* validando si estos productos fueron comprados previamente */
				log_data($this->log,'validando producto previamente comprados ===================' );
				foreach ($pack as $et => $r) {
					if( $this->is_venta($et,$p['uemail'],$p['umaquina']) ){
						unset( $pack[ $et ] );
						log_data($this->log,'producto previamente comprado' );
					}
				}

				if( $pack==null ){
					log_data($this->log,'no hay productos en la venta' );
					return false;
				}

				$data = array(
					'id_user' => $id_user,
					'user_email' => $p['uemail'],
					'subtotal' => 0.00,
					'descuento' => 0.00,
					'envio' => 0.00,
					'iva' => 0.00,
					'total' => 0.00,
					'fecha' => date( 'Y-m-d G:i:s', time() ),
				);
				log_data($this->log,'data ==> '.print_r($data,true) );
			/* agregando venta a base de datos */
				log_data($this->log,'guardando datos de venta ===============' );

				$s = $this->sql_save_venta( $data );
				$id_venta = query($s);
				log_data($this->log,'id_venta ==> '.$id_venta );

				if( !$id_venta ){
					log_data($this->log,'no es posible generar la venta' );
					return false;
				}

				foreach ($pack as $et => $r) {
					$a = $this->sql_save_venta_item( $r, $id_venta );
					log_data($this->log,'items venta ==> '.count($a) );
					if( $a==null ){
						log_data($this->log,'no hay items de venta' );
						$this->venta_id_attrib_change($id_venta,2);
						
					}else{
						foreach ($a as $et => $r) { query($r); }
					}
				}
			/* agregando envio a base de datos */
				log_data($this->log,'guardando datos de envio');

				$p['envio']['fecha'] = date( 'Y-m-d G:i:s', time() );
				$a = $this->sql_save_venta_address( $p['envio'], $id_venta );
				if($a!=null){
					foreach ($a as $et => $r) { query($r); }
				}else{
					log_data($this->log,'sin datos de direccion');
				}
			/* guardando datos adicionales */
				log_data($this->log,'guaradndo datos complementarios');

				$a = $this->sql_save_venta_data_more( $p, $id_venta );
				if($a==null){
					log_data($this->log,'sin datos adicionales');
					return false;
				}else{
					foreach ($a as $et => $r) { query($r); }
				}
			/* actualizando registros de maquina */
				$this->machine_change_attrib_change($p['umaquina'],'status',2);
				$this->machine_change_attrib_change($p['umaquina'],'id_vendor',$p['vendor']);
			/* actualizando datos de usuario */
				$this->user_id_change($id_user,'status',2);
			/* actualizando datos de venta */
				$this->venta_true( $id_venta );

			return true;
		}
		/* asigna valor verdadero a una venta */
		public function venta_true($id_venta=0){
			if($id_venta==0){ return false; }

			$s = $this->sql_venta_true($id_venta);
			query($s);
			return true;
		}
		/* determina si ya se ha realizado una venta con los datos dados */
		public function is_venta( $product='',$user_email='',$user_machine='' ){
			log_data($this->log,'is_venta()' );

			if($product==''){ return false; }
			//if($user_email==''){ return false; }
			if($user_machine==''){ return false; }

			log_data($this->log,"$product - $user_email - $user_machine" );

			$s = $this->sql_is_venta($product,$user_email,$user_machine);
			$a = query($s);
			if($a==null){ return false; }

			//$s = $this->sql_venta_status(  );

			return true;
		}
		
		public function venta_id_attrib_change( $id_venta=0,$status=0 ){
			if($id_venta==0){ return false; }

			$s = $this->sql_venta_id_attrib_change($id_venta,'status',$status);
			query($s);

			return true;
		}

		/* selecciona las maquinas a regalar */
		public function select_free_machine(){
			$this-> free_machine_next(12);
			$this-> free_machine_next(18);

			return true;
		}
		/*  */
		public function free_machine_next($tipo=0){
			if($tipo==0){ return null; }

			$s = $this->sql_free_machine_nex($tipo);
			$a = query($s);
			if($a==null){ return null; }
			$a = $a[0];

			$this->free_machine[ $tipo ] = $a;

			return $a;
		}

		public function free_machine_stock_minus($sku='',$minus=0){
			if($sku==''){ return false; }
			if($minus<=0){ return false; }

			$s = $this->sql_free_machine_data($sku);
			$a = query($s);
			if($a==null){ return false; }

			$a = $a[0];

			$n = $a['stock']-$minus;
			if($n<0){ $n=0; }
			$s = $this->sql_free_machine_change( $a['id_machine'], 'stock', $n );
			query($s);

			return true;
		}

		public function save_venta_magento(){
			log_data( $this->log, 'save_venta_magento()' );
			log_data( $this->log, 'POST ==> '.print_r($_POST,true) );
			//log_data( $this->log, 'data ==> '.print_r($this->data,true) );

			$subtotal = 0;
			$envio = 0;

			$pack = $_POST['pack_select'];
			$this->data['pack'][ $pack ] = $this->pack( $pack );
			//log_data( $this->log, 'data ==> '.print_r($this->data['pack'][ $pack ],true) );

			$items = 0;
			$qty = 0;
			foreach ($this->data['pack'][ $pack ]['prods'] as $et => $r) {
				/* piezas comprada */
				$unit = 1;
				if( isset( $r['data']['pack_qty']['value'] ) ){
					$unit = $r['data']['pack_qty']['value'];
				}
				// precio del producto
				$n = (float)$r['data']['price']['value'];
				/* subtotal producto */
				$sub = $n*$unit;
				$subtotal += $sub;

				/* subtotal producto envio */
				$env = 0;
				$envio += $env;
				$items ++;
				$qty += (int)$r['data']['pack_qty']['value'];
			}

			$sales = null;
				$sales['customer']['email']			= $_POST['uemail'];
				$sales['customer']['first_name']	= $_POST['uname'];
				$sales['customer']['last_name']		= $_POST['ulastname'];
				$sales['store']				= "Main Website\n Main Website Store\n Default Store View";
				$sales['ip']				= '127.0.0.1';
				$sales['date_created']		= date('Y/m/d G:i:s');
				$sales['updated_at']		= $sales['date_created'];
				$sales['currency_code']		= 'MXN';
				$sales['email_send']		= 0;	// notificacion via email de la venta
				$sales['notify_note']		= 0;	// notificacion cambio de status
				$sales['sales_order']		= $this->next_sales_id();	// id orden de venta
				$sales['weight']			= 0.01;	// peso volumetrico total

				$sales['subtotal']			= $subtotal;
				$sales['subtotal_iva']		= 0.00;
				$sales['envio']				= $envio;
				$sales['envio_discount']	= 0;
				$sales['envio_iva']			= $sales['envio_discount']*0.16;
				$sales['envio_method']		= 'amtable_amtable12';

				$sales['descuento']			= 0;
				$sales['total']				= $subtotal + $envio + $sales['envio_iva'];
				$sales['total_due']			= 0;	// total adeudado
				$sales['total_qty']			= $qty;	// numero total de articulos
				$sales['total_item']		= $items;	// numero de productos diferentes

				$sales['id_quote']				= 0;	// id
				$sales['id_shipping_address']	= 0;	// id direccion envio
				$sales['id_billing_address']	= 0;	// id direccion factura

			log_data( $this->log, 'data ==> '.print_r($sales,true) );

			$s = $this->sql_magento_sales_add();
			$id_sales = query($s);
			if(!$id_sales){ return false; }

			$s = $this->sql_magento_sales_modif($id_sales,$sales);
			query($s);

			$s = $this->sql_magento_sales_address_billing($id_sales,$sales);
				$id_billing = query($s);
			$s = $this->sql_magento_sales_address_shipping($id_sales,$sales);
				$id_shipping = query($s);
			$s = $this->sql_magento_sales_grid($id_sales,$sales); // cambiar status
				$id_grid = query($s);

			$pack = $this->data['pack'][ $pack ];
			foreach ($pack['prods'] as $et => $r) {
				$item = null;
				$item['quote_item_id'] 	= 0;
				$item['date_created'] 	= $sales['date_created'];
				$item['product_id'] 	= $r['entity_id'];
				$item['product_type'] 	= $r['type_id'];
				$item['weight'] 		= $r['data']['weight']['value'];
				$item['is_virtual'] 	= 0;
				$item['name'] 			= $r['data']['name']['value'].' '.$r['data']['nombre_secundario']['value'];
				$item['qty_order'] 		= $r['data']['pack_qty']['value'];
				$item['sku'] 			= $r['sku'];
				$item['price'] 			= $r['data']['price']['value'];
				$item['tax'] 			= $r['data']['impuesto_iva']['value'];
				$item['tax_amount'] 	= ($r['data']['impuesto_iva']['value']/100) * $r['data']['price']['value'];
				$item['discount'] 		= 0;
				$item['discount_amount'] = 0;
				$s = $this->sql_magento_sales_item($id_sales,$item);
					$i_item = query($s);
			}

			/*
				descuento en negativo
				envio

				customer_id
				customer_note_notify
				billing_address_id
				email_send
				quote_id
				shipping_address_id
				ip

			*/

			return false;
		}

		private function next_sales_id(){
			
			$sales_last = $this->sales_order_last();
			$sales_last['increment_last_id'] = ( (int)$sales_last['increment_last_id'] ) + 1;

			$s = $this->sql_next_sales_id($sales_last['entity_store_id'],$sales_last['increment_last_id']);
			query($s);

			$this->sales_oder = $sales_last['increment_last_id'];

			return $this->sales_oder;
		}

		public function sales_order_last(){
			log_data( $this->log, 'sales_order_last()' );

			$s = $this->sql_sales_order_last();
			$a = query($s);

			$order = null;
			foreach ($a as $et => $r) {
				if( $r['entity_type_code']=='order' ){ $order = $r; }
			}

			if($order==null){
				log_data( $this->log, 'sales_order_last() ==> 0' );
				return null;
			}

			log_data( $this->log, 'sales_order_last() ==> '.$order['increment_last_id'] );
			return $order;
		}

		public function sales_address_format(){
			$a = null;

			$a['address_id'] = 0;
			$a['region_id'] = 0;
			$a['customer_id'] = 0;
			$a['cfdi'] = '';
			$a['region'] = $_POST['envio']['estado'];
			$a['postcode'] = $_POST['envio']['cp'];
			$a['lastname'] = $_POST['envio']['lastname'];
			$a['street'] = $_POST['envio']['calle'].' '.$_POST['envio']['numero'].( $_POST['envio']['name_int']?' '.$_POST['envio']['name_int']:'' );
			$a['city'] = $_POST['envio']['deleg'];
			$a['email'] = $_POST['envio']['email'];
			$a['telephone'] = $_POST['envio']['tel'];
			$a['country_id'] = 0;
			$a['firstname'] = $_POST['envio']['name'];
			$a['company'] = '';
			$a['neighborhood'] = $_POST['envio']['colonia'];
			$a['rfc'] = '';

			return $a;
		}

		public function is_request_12_18( $quote_id=0 ){

			$s = $this->sql_is_request_12_18( $quote_id );
			$a = query($s);

			$this->sql_log( print_r($a,true) );
			if($a==null){ return false; }

			return $a[0]['maquina'];
		}





		private function sql_log($a=null){
			if( is_array($a) ){
				foreach ($a as $et => $r) {
					$this->sql_log( $this->log, $r );
				}
			}else{
				log_data( $this->log, "sql ==> $a" );
			}
			return true;
		}

		private function sql_is_request_12_18( $quote_id=0 ){
			if( $quote_id==0 ){ return ''; }

			$s = "SELECT max(
				CASE 
				WHEN sku in(
				'12252322-CST0',
				'12252394-CST0',
				'12224690-CST0',
				'12252324-CST0',
				'12142611-CST0',
				'12227109-CST0',
				'12143068-CST0',
				'12142612-CST0',
				'12208484-CST0',
				'12229241-CST0',
				'12312649-CST0',
				'12229112-CST0',
				'12276638-CST0') THEN 12
				WHEN sku in (
				'12232100-CST0',
				'12304101-CST0',
				'12333037-CST0',
				'12200655-CST0',
				'12227207-CST0',
				'12232059-CST0',
				'12281619-CST0',
				'12281618-CST0',
				'12136578-CST0',
				'12202605-CST0'
				)  THEN 18 
				END ) maquina 
				FROM nestle_me_114.sales_flat_quote_item 
				WHERE created_at >= (
					SELECT created_at FROM nestle_me_114.sales_flat_quote where entity_id = $quote_id
				)";

				$this->sql_log( $s );
			return $s;
		}

		private function sql_magento_sales_address_billing($id_sales=0,$data=null){
			return $this->sql_magento_sales_address($id_sales,1,$this->sales_address_format() );
		}
		private function sql_magento_sales_address_shipping($id_sales=0,$data=null){
			return $this->sql_magento_sales_address($id_sales,0,$this->sales_address_format() );
		}
		private function sql_magento_sales_address($id_sales=0,$is_billing=0,$data=null){
			if( $id_sales == 0 ){ return ''; }
			if( $data == null ){ return ''; }

			$billing = 'shipping';
			if( $is_billing==1 ){ $billing = 'billing'; }

			$s = "INSERT into sales_flat_order_address (entity_id,parent_id,customer_address_id,region_id,customer_id,fax,region,postcode,lastname,street,city,email,telephone,country_id,firstname,address_type,company,neighborhood,is_billing,rfc) 
				values( null, 
				$id_sales,
				".$data['address_id'].",
				".$data['region_id'].",
				".$data['customer_id'].",
				'".$data['cfdi']."',
				'".$data['region']."',
				'".$data['postcode']."',
				'".$data['lastname']."',
				'".$data['street']."',
				'".$data['city']."',
				'".$data['email']."',
				'".$data['telephone']."',
				'".$data['country_id']."',
				'".$data['firstname']."',
				'".$billing."',
				'".$data['company']."',
				'".$data['neighborhood']."',
				".$is_billing.",
				'".$data['rfc']."'
			 ) ";
			$this->sql_log( $s );
			$s = forceLatin1($s);
			return $s;
		}
		private function sql_magento_sales_grid($id_sales=0,$sales=null){
			if($id_sales==0){ return ''; }
			if($sales==null){ return ''; }

			$s = "INSERT into sales_flat_order_grid values(
				$id_sales,
				'pending',
				1,
				'".$sales['store']."',
				null,
				".$sales['total'].",
				".$sales['total'].",
				".$sales['total'].",
				".$sales['total'].",
				'".$this->sales_oder."',
				'".$sales['currency_code']."',
				'".$sales['currency_code']."',
				'".$sales['customer']['first_name'].' '.$sales['customer']['last_name']."',
				'".$sales['customer']['first_name'].' '.$sales['customer']['last_name']."',
				'".$sales['date_created']."',
				'".$sales['date_created']."'
			)";

			$this->sql_log( $s );
			$s = forceLatin1($s);
			return $s;
		}

		private function sql_magento_sales_item($id_sales=0,$data=null){
			if($id_sales==0){ return ''; }
			if($data==null){ return ''; }

			$s = "INSERT into sales_flat_order_item values(
				null,
				$id_sales,
				null,
				".$data['quote_item_id'].",
				1,
				'".$data['date_created']."',
				'".$data['date_created']."',
				'".$data['product_id']."',
				'".$data['product_type']."',
				'a:0:{}',
				".$data['weight'].",
				".$data['is_virtual'].",
				".$data['sku'].",
				'".$data['name']."',
				null, null, null, 0, 0, 0, null, 0, 0,
				".$data['qty_order'].",
				0, 0, null,
				".$data['price'].",
				".$data['price'].",
				".$data['price'].",
				".$data['price'].",
				".$data['tax'].",
				".$data['tax_amount'].",
				".$data['tax_amount'].",
				0, 0,
				".$data['discount'].",
				".$data['discount_amount'].",
				".$data['discount_amount'].",
				0, 0, 0, 0,
				".$data['price'].",
				".$data['price'].",
				0, 0,
				".$data['weight'].",
				null, null, null, null, null,
				".$data['price'].",
				".$data['price'].",
				".($data['price']*$data['qty_order']).",
				".($data['price']*$data['qty_order']).",
				0, 0, null, null, null, null, 0, null, null, null, null, null, null, null,
				0, 0, 0, 0, 0,
				'a:0:{}',
				0, 0, 0, 0,
				null, null, null, null, null, null, null, null, null, null, null, null, null, null, null,
				0,
				null	
				)";
			$this->sql_log( $s );
			$s = forceLatin1($s);
			return $s;
		}

		private function sql_next_sales_id($id_sotore_entity=0,$sales_order=0){
			if( $sales_order==0 ){ return ''; }

			$s = "UPDATE eav_entity_store set increment_last_id = '$sales_order' where entity_store_id = $id_sotore_entity;";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_sales_order_last(){
			$s = "SELECT ees.*,eet.entity_type_code,eet.entity_model,eet.entity_table,eet.increment_model FROM eav_entity_store as ees inner join eav_entity_type as eet on eet.entity_type_id = ees.entity_type_id ";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_list_vendedor(){
			$s="SELECT * from grano_vendedor as gv where gv.status>0";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_is_vendedor($code=''){
			if($code==''){ return ''; }
			$code = strtoupper( trim($code) );

			$s = "SELECT * from grano_vendedor where code like '$code' and status>0;";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_list_pack_for_vendedor($id_vendedor=0){
			$s = "SELECT * FROM grano_codes where id_vendedor=$id_vendedor and status>0;";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_pack_id( $code_pack='' ){
			if( $code_pack=='' ){ return ''; }

			$s = "SELECT * FROM catalog_product_entity where sku='$code_pack'";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_pack($pack_id=0){
			if( $pack_id==0 ){ return ''; }

			$s = "SELECT * FROM catalog_product_entity where entity_id IN(
					SELECT child_id FROM catalog_product_relation where parent_id = $pack_id
				);";

			$this->sql_log( $s );
			return $s;
		}

		private function sql_product_sku($sku=''){
			if($sku==''){ return ''; }

			$s = "SELECT * from catalog_product_entity where sku = '$sku'";

			$this->sql_log( $s );
			return $s;
		}

		private function sql_product_id( $id_prod=0 ){
			if($id_prod==0){ return ''; }

			$s = "SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label,t.attribute_id
				from catalog_product_entity_datetime as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $id_prod

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_decimal as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $id_prod

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_gallery as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $id_prod

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_int as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $id_prod

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_text as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $id_prod

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_url_key as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $id_prod

				union

				SELECT t.value_id,t.value,eav.attribute_code,eav.frontend_label ,t.attribute_id
				from catalog_product_entity_varchar as t 
				inner join eav_attribute as eav on eav.attribute_id = t.attribute_id
				where 
				t.entity_id = $id_prod
				";

			$this->sql_log( 'sql_product_id()' );
			return $s;
		}

		private function sql_is_user( $email='' ){
			if($email==''){ return ''; }

			$s = "SELECT * from grano_users where email like '$email' and status>0";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_user_new($p=null){
			if($p==null){ return ''; }

			$s = "INSERT into grano_users values( null, '".$p['uname']."', '".$p['ulastname']."', '".$p['uemail']."', '".$p['fing']."', 1 );";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_user_data_add( $id_user=0, $tag='', $val='' ){
			if($id_user==0){ return ''; }
			if($tag==''){ return ''; }

			$s = "INSERT into grano_users_data values( null, $id_user, '$tag', '$val', 1 )";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_save_venta($d=null){
			if( $d==null ){ return ''; }

			$s = "INSERT into grano_venta( ".
				"id_venta, id_user, user_email, subtotal, descuento, envio, iva, total, fecha, status ) ";
			$s = $s."values( null, ".
				$d['id_user'].", '".
				$d['user_email']."', ".
				$d['subtotal'].", ".
				$d['descuento'].", ".
				$d['envio'].", ".
				$d['iva'].", ".
				$d['total'].", '".
				$d['fecha']."', 0 );";

			$this->sql_log( $s );
			return $s;
		}

		private function sql_save_venta_item($d=null,$id_venta=0){
			if( $d==null ){ return ''; }

			$e = array(
				'sku' => $d['sku'],
				'title' => $d['data']['name']['value'],
				'subtitle' => $d['data']['nombre_secundario']['value'],
				'price' => '0.00',
				'qty' => '1',
				'subtotal' => '0.00',
				'tax' => '0.00',
				'subtotal_tax' => '0.00',
				'discount' => '0.00',
				'grand_total' => '0.00',
				'parent' => '0',
				'machine' => $d['machine'],
			);

			foreach ($e as $et => $r) {
				$a[] = $this->sql_save_venta_item_elem( $id_venta,$d['entity_id'],$et,$r );
			}

			foreach ($d['prods'] as $et => $r) {

				$qty = 1;
				$subtotal = $r['data']['price']['value']*$qty;

				$iva = 0;
				if( $r['data']['impuesto_iva']['value']>0 ){
					$iva = $subtotal*0.16;
				}

				$subtotal_iva = $subtotal+$iva;
				$discount = 0.00;
				$grand_total = $subtotal_iva - $discount;

				$e = array(
					'sku' => $r['sku'],
					'title' => $r['data']['name']['value'],
					'subtitle' => $r['data']['nombre_secundario']['value'],
					'price' => $r['data']['price']['value'],
					'qty' => $qty,
					'subtotal' => $subtotal,
					'tax' => $iva,
					'subtotal_tax' => $subtotal_iva,
					'discount' => $discount,
					'grand_total' => $grand_total,
					'parent' => "$id_venta--".$d['sku'],
					'machine' => $d['machine'],
				);

				foreach ($e as $etr => $rr) {
					$a[] = $this->sql_save_venta_item_elem( $id_venta,$r['entity_id'],$etr,$rr );
				}
			}

			return $a;
		}

		private function sql_save_venta_item_elem( $id_venta=0,$id_product=0,$tag='',$val='' ){
			if( $id_venta==0 ){ return ''; }
			if( $id_product==0 ){ return ''; }
			if( $tag=='' ){ return ''; }

			$s = "INSERT into grano_venta_items "; //(id_item,id_venta_item,product_id,product_tag,product_value,product_status) ";
			$s = $s."values( null, $id_venta, $id_product, '$tag', '$val', 1 )";

			$this->sql_log( $s );
			return $s;
		}

		private function sql_save_venta_address( $d=null, $id_venta=0 ){
			if($d==null){ return null; }
			if($id_venta==0){ return null; }

			$a = null;
			foreach ($d as $et => $r) {
				$a[] = $this->sql_save_venta_address_elem( $et,$r,$id_venta );
			}

			return $a;
		}

		private function sql_save_venta_address_elem( $tag='',$val='',$id_venta=0 ){
			if($id_venta==0){ return ''; }
			if($tag==''){ return ''; }

			$s = "INSERT into grano_venta_address values( null, $id_venta, '$tag', '$val', 1 )";

			$this->sql_log( $s );
			return $s;
		}

		private function sql_save_venta_data_more($d=null,$id_venta=0){
			if($d==null){ return null; }
			if($id_venta==0){ return null; }

			$a = null;
			$a[] = "INSERT into grano_venta_data values( null, $id_venta, 'vendor', '".$d['vendor']."', 1 );";
			$a[] = "INSERT into grano_venta_data values( null, $id_venta, 'uname', '".$d['uname']."', 1 );";
			$a[] = "INSERT into grano_venta_data values( null, $id_venta, 'ulastname', '".$d['ulastname']."', 1 );";
			$a[] = "INSERT into grano_venta_data values( null, $id_venta, 'umaquina', '".$d['umaquina']."', 1 );";

			$this->sql_log( $a );
			return $a;
		}

		private function sql_venta_id_attrib_change( $id_venta=0,$attrib='',$val='' ){
			if($id_venta==0){ return ''; }
			if($attrib==''){ return ''; }

			$s = "UPDATE grano_venta set $attrib = '$val' where id_venta = $id_venta;";

			$this->sql_log( $s );
			return $s;
		}

		private function sql_venta_add($p=null){
			if($p==null){ return ''; }

			$s = "INSERT into grano_venta( id_venta, id_user, user_email, subtotal, descuento, envio, iva, total, fecha, status ) 
			values( null, 1, 'rmorales@mlg.com.mx', 300.05, 300.05, 0.00, 0.00, 0.00, '2019-03-07 17:48:12', 1 );";

			$this->sql_log( $s );
			return $s;
		}
		/* busca en los datos de todos los usuarios si ya existe una maquina */
		private function sql_search_machine_code($machine=''){
			if( $machine=='' ){ return ''; }

			$s = "SELECT * from grano_users_data where data_tag like 'code_maquina' and data_value like '$machine' ";

			$this->sql_log( $s );
			return $s;
		}
		/* lista los codigos de los productos comprados por el usuario */
		private function sql_user_list_shopping_code($email='',$machine=''){
			if($email==''){ return ''; }
			if($machine==''){ return ''; }

			$s ="SELECT
				gvi.id_venta_item as venta_id,
				gvi.product_value as sku,
				gvi_b.product_value as parent,
				gvi_c.product_value as machine

				from grano_venta_items as gvi
				inner join grano_venta_items as gvi_b on (gvi_b.product_id = gvi.product_id and gvi_b.id_venta_item = gvi.id_venta_item )
				inner join grano_venta_items as gvi_c on (gvi_c.product_id = gvi.product_id and gvi_c.id_venta_item = gvi.id_venta_item )
				where
				gvi.id_venta_item IN( select id_venta from grano_venta where user_email like '$email' ) and
				gvi.product_tag like 'sku' and
				gvi.product_status = 1 and
				gvi_b.product_tag like 'parent' and
				gvi_b.product_value like '0' and
				gvi_c.product_tag like 'machine' and
				gvi_c.product_value like '$machine'";

			$this->sql_log( $s );
			return $s;
		}

		private function sql_is_venta($product='',$user_email='',$user_machine=''){
			if($product==''){ return ''; }
			if($user_email==''){ return ''; }
			if($user_machine==''){ return ''; }
			/*
			$s = "SELECT
				gvi.id_venta_item as venta_id,
				gvi.product_value as sku,
				gvi_b.product_value as parent,
				gvi_c.product_value as machine

				from grano_venta_items as gvi
				inner join grano_venta_items as gvi_b on (gvi_b.product_id = gvi.product_id and gvi_b.id_venta_item = gvi.id_venta_item )
				inner join grano_venta_items as gvi_c on (gvi_c.product_id = gvi.product_id and gvi_c.id_venta_item = gvi.id_venta_item )
				where
				gvi.id_venta_item IN( select id_venta from grano_venta where user_email like '$user_email' ) and
				gvi.product_tag like 'sku' and
				gvi.product_value like '$product' and
				gvi.product_status = 1 and
				gvi_b.product_tag like 'parent' and
				gvi_b.product_value like '0' and
				gvi_c.product_tag like 'machine' and
				gvi_c.product_value like '$user_machine'
				";*/
			$s = "SELECT
				gvi.id_venta_item as venta_id,
				gvi.product_value as sku,
				gvi_b.product_value as parent,
				gvi_c.product_value as machine

				from grano_venta_items as gvi
				inner join grano_venta_items as gvi_b on (gvi_b.product_id = gvi.product_id and gvi_b.id_venta_item = gvi.id_venta_item )
				inner join grano_venta_items as gvi_c on (gvi_c.product_id = gvi.product_id and gvi_c.id_venta_item = gvi.id_venta_item )
				where
				gvi.product_tag like 'sku' and
				gvi.product_value like '$product' and
				gvi.product_status = 1 and
				gvi_b.product_tag like 'parent' and
				gvi_b.product_value like '0' and
				gvi_c.product_tag like 'machine' and
				gvi_c.product_value like '$user_machine'
				";

			$this->sql_log( $s );
			return $s;
		}

		private function sql_is_machine_change($machine=''){
			if($machine==''){ return ''; }

			$s = "SELECT * from grano_machine_change where uid_machine like '$machine' and status>0;";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_machine_change_attrib_change($id_machine=0,$campo='',$val=''){
			if($id_machine==0){ return false; }
			if($campo==''){ return false; }

			$s = "UPDATE grano_machine_change set $campo = '$val' where id_machine = '$id_machine';";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_user_id_change($id_user=0,$campo='',$val=''){
			if($id_user==0){ return false; }
			if($campo==''){ return false; }

			$s = "UPDATE grano_users set $campo = '$val' where id_user = $id_user";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_user_id_data($id_user=''){
			if($id_user==0){ return null; }

			$a = null;
			$a[] = "SELECT * from grano_users as gu where gu.id_user=1;";
			$a[] = "SELECT * from grano_users_data where id_user=$id_user and status>0 ";

			$this->sql_log( $a );
			return null;
		}

		private function sql_free_machine_nex($tipo=0){
			if($tipo==0){ return ''; }

			$s = "SELECT * from grano_free_machine where stock>0 and status=1 and tipo = $tipo order by prioridad ASC limit 0,1";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_free_machine_data($sku=''){
			if($sku==''){ return ''; }

			$s = "SELECT * from grano_free_machine where sku = '$sku' and status =1";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_free_machine_change($id_free_machine=0,$campo='',$val=''){
			if($id_free_machine==0){ return ''; }
			if($campo==''){ return ''; }

			$s = "UPDATE grano_free_machine set $campo = '$val' where id_machine = $id_free_machine";
			$this->sql_log( $s );
			return $s;
		}

		private function sql_catalog_product_group_data($id_product=0){
			if( $id_product==0 ){ return ''; }

			$s = "SELECT
				pcl.link_id,
				pcl.linked_product_id,
				cplad.value,
				cpla.product_link_attribute_code,
				cpla.data_type
				FROM catalog_product_link as pcl
				inner join catalog_product_link_attribute_decimal as cplad on cplad.link_id = pcl.link_id
				inner join catalog_product_link_attribute as cpla on cpla.product_link_attribute_id = pcl.link_type_id
				where 
				pcl.product_id=233
				";
			return $s;
		}

		private function sql_magento_sales_modif($id_sales=0,$a=null){
			if($a==null){ return ''; }
			if($id_sales==0){ return ''; }

			$s = "state = 'new', 
				status = 'pending', 
				protect_code = '', 
				shipping_description = 'Costo de envío - Envio', 
				is_virtual = '0', 
				store_id = '1', 
				/*customer_id = '', */
				base_discount_amount = '0', 
				base_discount_invoiced = '0', 
				base_grand_total = '".$a['total']."', 
				base_shipping_amount = '".$a['envio']."', 
				base_shipping_invoiced = '".$a['envio']."', 
				base_shipping_tax_amount = '".$a['envio_iva']."', 
				base_subtotal = '".$a['subtotal']."', 
				base_subtotal_invoiced = '".$a['subtotal']."', 
				base_tax_amount = '".$a['envio_iva']."', 
				base_tax_invoiced = '".$a['envio_iva']."', 
				base_to_global_rate = '1', 
				base_to_order_rate = '1', 
				base_total_invoiced = '".$a['total']."', 
				base_total_invoiced_cost = '0', 
				base_total_paid = '".$a['total']."', 
				discount_amount = '".$a['descuento']."', 
				discount_invoiced = '".$a['descuento']."', 
				grand_total = '".$a['total']."', 
				shipping_amount = '".$a['envio']."', 
				shipping_invoiced = '".$a['envio']."', 
				shipping_tax_amount = '".$a['envio_iva']."', 
				store_to_base_rate = '1', 
				store_to_order_rate = '1', 
				subtotal = '".$a['subtotal']."', 
				subtotal_invoiced = '".$a['subtotal']."', 
				tax_amount = '".$a['envio_iva']."', 
				tax_invoiced = '".$a['envio_iva']."', 
				total_invoiced = '".$a['total']."', 
				total_paid = '".$a['total']."', 
				total_qty_ordered = '".$a['total_qty']."', 
				customer_is_guest = '0', 
				customer_note_notify = '".$a['notify_note']."', 
				billing_address_id = '".$a['id_billing_address']."', 
				customer_group_id = '3', 
				email_sent = '".$a['email_send']."', 
				quote_id = '".$a['id_quote']."', 
				shipping_address_id = '".$a['id_shipping_address']."', 
				base_shipping_discount_amount = '".$a['envio_discount']."', 
				base_subtotal_incl_tax = '".($a['subtotal']+$a['subtotal_iva'])."', 
				base_total_due = '".$a['total_due']."', 
				shipping_discount_amount = '".$a['envio_discount']."', 
				subtotal_incl_tax = '".($a['subtotal']+$a['subtotal_iva'])."', 
				total_due = '".$a['total_due']."', 
				weight = '".$a['weight']."', 
				increment_id = '".$a['sales_order']."', 
				base_currency_code = '".$a['currency_code']."', 
				customer_email = '".$a['customer']['email']."', 
				customer_firstname = '".$a['customer']['first_name']."', 
				customer_lastname = '".$a['customer']['last_name']."', 
				global_currency_code = '".$a['currency_code']."', 
				order_currency_code = '".$a['currency_code']."', 
				remote_ip = '".$a['ip']."', 
				shipping_method = '".$a['envio_method']."', 
				store_currency_code = '".$a['currency_code']."', 
				store_name = '".$a['store']."', 
				x_forwarded_for = '".$a['ip']."', 
				created_at = '".$a['date_created']."', 
				updated_at = '".$a['updated_at']."', 
				total_item_count = '".$a['total_item']."', 
				shipping_incl_tax = '".($a['envio']+$a['envio_iva'])."', 
				base_shipping_incl_tax = '".($a['envio']+$a['envio_iva'])."', 
				gift_cards = 'a:0:{}'";

			$s = $s . ", hidden_tax_amount = '0',
				base_hidden_tax_amount = '0',
				shipping_hidden_tax_amount = '0',
				base_shipping_hidden_tax_amnt = '0',
				hidden_tax_invoiced = '0',
				base_hidden_tax_invoiced = '0',
				base_gift_cards_amount = '0',
				gift_cards_amount = '0',
				gw_allow_gift_receipt = '0',
				gw_add_card = '0',
				gw_base_price = '0',
				gw_price = '0',
				gw_items_base_price = '0',
				gw_items_price = '0',
				gw_card_base_price = '0',
				gw_card_price = '0',
				gw_base_tax_amount = '0',
				gw_tax_amount = '0',
				gw_items_base_tax_amount = '0',
				gw_items_tax_amount = '0',
				gw_card_base_tax_amount = '0',
				gw_card_tax_amount = '0',
				discount_coupon_amount = '0',
				base_discount_coupon_amount = '0' ";

			$s = "UPDATE sales_flat_order set $s where entity_id = '$id_sales';";

			$this->sql_log( $s );

			$s = forceLatin1($s);
			return $s;
		}
		private function sql_magento_sales_add(){

			$s = "INSERT into sales_flat_order (entity_id)  values(null)";
			$this->sql_log( $s );
			return $s;
		}

		/* sql regresa el stock de un producto */
		private function sql_product_id_stock($id_product=0){
			if( $id_product==0 ){ return ''; }

			$s = "SELECT * FROM cataloginventory_stock_status_idx where product_id=$id_product;";
			$this->sql_log( $s );
			return $s;
		}
		/* incrementa o decrementa el stock de 1 producto */
		private function sql_product_id_stock_modif($id_product=0,$val=''){
			if( $id_product==0 ){ return ''; }
			if( $val==0 ){ return ''; }

			$a = null;
			$a[] = "UPDATE cataloginventory_stock_item   set qty = '$val' where item_id=$id_product";
			$a[] = "UPDATE cataloginventory_stock_status set qty = '$val' where product_id=$id_product";

			$this->sql_log( $a );
			return $a;
		}

		public function sql_venta_true($id_venta=0){
			if($id_venta==0){ return false; }

			$s = "UPDATE grano_venta set status = '1' where id_venta = $id_venta;";
			$this->sql_log( $s );
			return $s;
		}

		public function product_id_stock($id_product=0){
			if( $id_product==0 ){ return false; }

			$s = $this->sql_product_id_stock( $id_product );
			$a = query( $s );
			if($a==null){ return false; }
		}

		public function product_id_stock_modif($id_product=0,$increment=0){
			if( $id_product==0 ){ return false; }
			if( $increment==0 ){ return false; }

			$s = $this->sql_product_id_stock( $id_product );
			$a = query( $s );
			if($a==null){ return false; }
		}
	}
}

?>