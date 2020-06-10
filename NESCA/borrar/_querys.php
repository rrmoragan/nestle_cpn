<?php

if( !defined('LIB_QUERYS') ){

	include('config_db.php');

	define('LIB_QUERYS','LIB_QUERYS mysql v0.5');
	define('ERROR_SQL','error en la consulta');
	define('ERROR_SQL_CONEXION','error en la conexion a base de datos');

	define('CNX_OK','Conexion a base de datos iniciada');
	define('CNX_FAIL','Conexion fallido a base de datos');

	/* funciones para mysql */
	{
		$GLOBALS['msql']['status']=false;
		$GLOBALS['msql']['conexion']=null;
		$GLOBALS['msql']['intentos']=0;

		$GLOBALS['msql']['cnxn_data']['select']=1;

		$GLOBALS['msql']['cnxn_data']['host'][1] =DB_HOST;
		$GLOBALS['msql']['cnxn_data']['user'][1] =DB_USER;
		$GLOBALS['msql']['cnxn_data']['pass'][1] =DB_PASS;
		$GLOBALS['msql']['cnxn_data']['dbase'][1]=DB_NAME;

		$GLOBALS['msql']['cnxn_data']['host'][2] =DB_HOST2;
		$GLOBALS['msql']['cnxn_data']['user'][2] =DB_USER2;
		$GLOBALS['msql']['cnxn_data']['pass'][2] =DB_PASS2;
		$GLOBALS['msql']['cnxn_data']['dbase'][2]=DB_NAME2;

		function mysql_cnx_host(){
			$db=$GLOBALS['msql']['cnxn_data']['select'];
			return $GLOBALS['msql']['cnxn_data']['host'][ $db ];
		}
		function mysql_cnx_user(){
			$db=$GLOBALS['msql']['cnxn_data']['select'];
			return $GLOBALS['msql']['cnxn_data']['user'][ $db ];
		}
		function mysql_cnx_pass(){
			$db=$GLOBALS['msql']['cnxn_data']['select'];
			return $GLOBALS['msql']['cnxn_data']['pass'][ $db ];
		}
		function mysql_cnx_dbase(){
			$db=$GLOBALS['msql']['cnxn_data']['select'];
			return $GLOBALS['msql']['cnxn_data']['dbase'][ $db ];
		}
		function mysql_cnx_select($db=1,$v=0){
			$GLOBALS['msql']['cnxn_data']['select']=$db;
			if($v){ tt('conectando con '.mysql_cnx_host().' => '.mysql_cnx_dbase()); }
			return null;
		}

		/* inicia la conexion a base de datos */
		function my_sql_ini($v=0){
			if($v) tt("my_sql_ini() v0.1");

			$GLOBALS['msql']['intentos'] += 1;
			if( $GLOBALS['msql']['conexion'] ){ if($v){ tt(CNX_OK); } return true; }

			$lnk = mysqli_connect( 
				mysql_cnx_host(), 
				mysql_cnx_user(), 
				mysql_cnx_pass(), 
				mysql_cnx_dbase()
			);

			if($v){ pre($lnk); }

			$error = 0;

			if($lnk==null){
				$error = mysqli_connect_errno();

				switch( $error ){
					case 1226: usleep(5000); if( dbase_ini() ){ return true; } break;
					case 1203: usleep(5000); if( dbase_ini() ){ return true; } break;
				}

				tt('mysql conexion ==> '.$error);
				tt(CNX_FAIL);

				return null;
			}else{
				$GLOBALS['msql']['conexion'] = $lnk;
				$GLOBALS['msql']['status'] = true;
				if($v){ tt(CNX_OK); } 
				return true;
			}

			$GLOBALS['msql']['conexion'] = null;
			$GLOBALS['msql']['status'] = false;

			if($v){ tt(CNX_FAIL); }
			return false;
		}
		/* cierra la conexion a base de datos */
		function my_sql_close($v=0){
			if ($v==1){ tt('my_sql_close() v0.4;'); }

			if( $GLOBALS['msql']['conexion'] ){
				mysqli_close( $GLOBALS['msql']['conexion'] );
			}

			$GLOBALS['msql']['conexion']=null;
			$GLOBALS['msql']['status']=false;
			$GLOBALS['msql']['intentos']=0;

			return true;
		}
		/* proporciona los datos de conexion utilizados en las querys */
		function my_sql_conexion(){
			return $GLOBALS['msql']['conexion'];
		}
		/* proporciona el status en la conexion a base de datos true/false */
		function my_sql_conexions_status(){
			return $GLOBALS['msql']['status'];
		}
		/* numero de intentos en conectarse a la base de datos */
		function my_sql_llamadas(){
			return $GLOBALS['msql']['intentos'];
		}

		function query_ini($s=''){
			if($s=='') return '';
			$a=explode(' ', $s);
			return strtolower($a[0]);
		}
		/* genera la consulta a base de datos */
		function query($s='',$conexion=1,$v=0){
			if($v){ tt('query() v1.1'); }

			if( trim($s)=='' ){ return null; }

			my_sql_close();
			// iniciando conexion a base de datos
			if($v){ tt('conectando con base de datos'); }
			mysql_cnx_select($conexion,$v);
			if( !my_sql_ini($v) ){ return null; }

			if($v){ tt($s); }

			$lnk=my_sql_conexion();

			$rs = mysqli_query( $lnk, $s );

			// en caso de que la consulta fuese un insert
			switch ( query_ini($s) ) {
				case 'insert':
					$d = $lnk->insert_id;
					my_sql_close();

					if($v){ tt('insert ==> '.$d); }
					return $d;
				break;
				case 'truncate':
				case 'update':
				case 'delete':
					my_sql_close();
					return null;
				break;
			}

			if($rs==null){
				my_sql_close();
				if($v){ tt('sin resultados'); }
				return null;
			}

			// procesando resultados
			$d = null;
			while ($r = mysqli_fetch_array($rs, MYSQLI_ASSOC)){
				$d[] = $r;
			}

			// consultas que no regresan resultados
			$nregs=count($d);
			if( $nregs==0 || ( $nregs==1 && $d[0]==null ) ){
				my_sql_close();

				if($v){ tt('sin resultados'); }
				return null;
			}

			my_sql_close();
			if($v){ tt( 'regidtros ==> '.count($d) ); }
			return $d;
		}

	}

	/* funciones para sql-server */

	{
		define('LIB_QUERYS_SERV','LIB_QUERYS sql serrver v0.1');

		
		if( !function_exists('sqlsrv_connect') ){
			function sqlsrv_connect(){ return null; }
			function sqlsrv_errors(){ return 0; }
		}
		
		$GLOBALS['sql']['status']=false;
		$GLOBALS['sql']['conexion']=null;
		$GLOBALS['sql']['intentos']=0;

		function sql_server_ini($v=0){

			if( $GLOBALS['sql']['conexion']!=null ){ return true; }

			$lnk = sqlsrv_connect( 
				SQLS_HOST, array(
					'Database'=>SQLS_NAME,
					'UID'=>SQLS_USER,
					'PWD'=>SQLS_PASS,
				)
			);

			if($v){ pre($lnk); }

			if($lnk!=null){
				$GLOBALS['sql']['conexion']=$lnk;
				$GLOBALS['sql']['intentos']+=1;
				$GLOBALS['sql']['status']=true;

				return true;
			}

			$GLOBALS['sql']['status']=false;
			$GLOBALS['sql']['conexion']=null;
			$GLOBALS['sql']['intentos']+=1;

			return false;
		}

		function sql_server_close(){

			if( $GLOBALS['sql']['conexion']==null ){ return true; }

			sqlsrv_close( $GLOBALS['sql']['conexion'] );

			$GLOBALS['sql']['status']=false;
			$GLOBALS['sql']['conexion']=null;
			$GLOBALS['sql']['intentos']=0;

			return true;
		}

		function sql_server_conexion(){
			return $GLOBALS['sql']['conexion'];
		}

		function sql_server_conexion_status(){
			return $GLOBALS['sql']['status'];
		}

		function sql_server_conexion_llamadas(){
			return $GLOBALS['sql']['intentos'];
		}

		function query_sql($s='',$p=null,$v=0){
			if($v){ tt('query_sql()'); }

			if($s==''){ if($v){ tt('query => null'); } return null; }

			if( !sql_server_conexion_status() ){
				if( !sql_server_ini($v) ){
					print_r( sqlsrv_errors() );
					return null;
				}
			}

			$lnk=sql_server_conexion();

			$d=sqlsrv_query( $lnk, $s, $p);

			if( $d === false ) {
				print_r( sqlsrv_errors() );
				return null;
			}

			// en caso de que la consulta fuese un insert
			if (strtolower(substr($s, 0, 6)) === 'insert') {
				$d = $lnk->insert_id;
				sql_server_close();

				if($v){ tt('insert ==> '.$d); }
				return $d;
			}

			if (strtolower(substr($s, 0, 6)) === 'update') {
				sql_server_close();
				return null;
			}

			$a=null;
			while( $r = sqlsrv_fetch_array( $d, SQLSRV_FETCH_ASSOC) ) {
			      $a[] = $r;
			}

			if( count($d)==0 ){
				sql_server_close();

				if($v){ tt('sin resultados'); }
				return null;
			}

			sql_server_close();
			if($v){ tt( 'regidtros ==> '.count($d) ); }
			return $d;
		}

		//if($v){ tt('include ==> '.LIB_QUERYS_SERV); }
	}

}

?>