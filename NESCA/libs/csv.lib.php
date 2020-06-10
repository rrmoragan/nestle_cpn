<?php

if( !defined('CSV_LIB') ){

	define( 'CSV_LIB', 'v0.2' );

	class fileCSV{
		// obtiene el nombre del sistema operativo utilizado
			public function os(){

				$a = $_SERVER;
				/*
				foreach ($a as $et => $r) {
					if( is_array($r) ){ continue; }
					
					$b = explode('Windows', $r);
					if( count($b)>1 ){
						echo "\n $et ==> $r";
					}
				}*/

				if( isset( $_SERVER['OS'] ) ){
					return $_SERVER['OS'];
				}

				return '';
			}
			public function os_ruta( $r='' ){
				if( $r=='' ){ return ''; }

				switch ( $this->os() ) {
					case 'Windows_NT':
						$s = explode('/c/', $r);
						if( count($s)>1 && $s[0]=='' ){
							unset( $s[0] );

							$s = 'c:\\'.implode('/c/',$s );
						}else{
							$s = $s[0];
						}
						$s = explode( '/', $s );
						$s = implode( '\\', $s );
						
						return $s;
						break;
				}

				return $r;
			}
		// guarda un arreglo bidimencional como un archivo CSV
			// la comilla doble la guardara como "`"
			public function save_file( $file='', $data=null, $cab='' ){
				if( $file=='' ){ $file = 'noname'; }
				$file .= '.csv';

				$file = $this->os_ruta( $file );
				//echo "\n ********* $file\n";

				$d = $this->data_to_csv( $data );

				$f = null;
				if( is_writable( $file ) ){
					//echo "\n el archivo existe ==> w";
					$f = fopen( $file, 'w' );
				}else{
					//echo "\n el archivo no existe ==> x";
					$opts = array('http'=>array('method'=>"GET", 'header'=>"Content-Type: text/csv; charset=utf-8") );
					$stream = stream_context_create($opts);

					//print_r( $stream );

					$f = fopen( $file, 'x', false, $stream );
				}

				if( $f==null ){
					echo "\n no se pudo tener acceso al archivo.\n";
					return false;
				}

				$data = "$cab \n".$d;
				$data = trim( $data );
				fwrite( $f, $data );
				fclose( $f );

				return true;
			}
		// convierte un arreglo en cadena divida por comas
			public function data_to_csv( $data=null ){
				if( $data == null ){ return ''; }

				$cab = $this->data_to_csv_cabs( $data );

				/* procesando datos */
					$niv = 1;
					foreach ($data as $et => $r) { if( is_array($r) ){ $niv = 2; } }

					$s = '';
					if( $niv == 2 ){
						foreach ($data as $et => $r) {
							$ss = '';
							foreach ($cab as $etr => $rr) {
								if( $ss!='' ){ $ss .= ','; }

								if( !isset( $r[ $rr ] ) ){
									$ss .= '""';
								}else if( is_float( $r[ $rr ] ) ){
									$ss .= $r[ $rr ];
								}else if( is_int( $r[ $rr ] ) ){
									$ss .= $r[ $rr ];
								}else if( is_string( $r[ $rr ] ) ){
									$r[ $rr ] = str_replace( '"', "`", $r[ $rr ] );

									$ss = $ss.'"'.$r[ $rr ].'"';
								}else{
									$ss = $ss.'"Array"';
								}
							}
							$s .= "$ss\n";
						}
					}else{
						foreach ($data as $et => $r) {
							$ss = '';
							foreach ($cab as $etr => $rr) {
								if( $ss!='' ){ $ss .= ','; }

								if( $rr == $et ){
									if( is_float( $r ) ){
										$ss .= $r;
									}else if( is_int( $r ) ){
										$ss .= $r;
									}else if( is_string( $r ) ){
										$ss .= '"'.$r.'"';
									}else{
										$ss .= '"Array"';
									}
								}else{
									$ss .= '""';
								}
							}
							$s .= "$ss\n";
						}
					}

				/* cabeceras */
					$ss = '';
					foreach ($cab as $et => $r) {
						if( $ss!='' ){ $ss .= ','; }
						$ss .= '"'.$r.'"';
					}
					$ss .= "\n";

				return $ss.$s;
			}
		// obtiene las cabeceras de un arreglo y las separa por comas
			public function data_to_csv_cabs( $data=null ){
				if( $data == null ){ return ''; }

				$b = null;
				foreach ($data as $et => $r) {
					if( is_array( $r ) ){
						foreach ($r as $etr => $rr) {
							$b[ $etr ] = 1;
						}
					}
				}

				if( $b == null ){
					foreach ($data as $et => $r) {
						$b[ $et ] = 1;
					}
				}

				$a = null;
				foreach ($b as $et => $r) {
					$a[] = $et;
				}

				return $a;
			}
	}
}

/*
$csv = new fileCSV();
$csv->save_file( '/c/xampp/htdocs/works/reporte_ventas/data/mi_archivo', $a );
*/
?>