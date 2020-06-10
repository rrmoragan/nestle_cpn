<?php
// v3.2
if( !defined('LOG_M') ){

	define('LOG_M','LOG library');
	define('LOG_M_VER','3.2');

	class logData{

		public $file = '_noname.log';	/* nombre del archivo */
		public $dir = '.';				/* directorio de trabajo */
		public $valid_directory = true;

		/* agrega datos al archivo log */
		public function log_add($s=''){
			if($s==''){ return false; }

			$t = date( "Y-m-d G:i:s", time() );

			$s = "\n:::: $t :::: $s";

			$this->log_save($s);

			return true;
		}
		/* prepara los datos para guardar */
		private function log_save($s=''){
			if($s==''){ return false; }

			$ls = strlen($this->dir);
			$f  = $this->file;

			if( $this->valid_directory ){
				if( $this->dir[ $ls-1 ]!='/' ){
					$f = $this->dir.'/'.$f;
				}else{
					$f = $this->dir.$f;
				}
			}

			$this->save_file( $f, $s, 'a+' );

			return true;
		}
		/* asigna el nombre de archivo de trabajo */
		public function log_file( $s='' ){
			$s = trim($s);
			if($s==''){ return false; }

			$this->file = "$s".".log";

			return true;
		}
		/* asigna el directorio de trabajo */
		public function log_directory( $s='.' ){
			$this->dir = $s;

			return true;
		}
		/* guarda datos en archivo */
	    private function save_file($file='', $dat='', $modo='a'){
	        if( $f = fopen($file, $modo) ){
	            fwrite($f, $dat);
	            fclose($f);
	            return true;
	        }

	        tt('error: open file ==> ['.$file.']');

	        return false;
	    }
	    /* evita la seccion de codigo para validar el directorio */
	    public function valid_dir_off(){
	    	$this->valid_directory = false;

	    	return true;
	    }
	}

	/* funcion simplificada de la clase logData
		log_data( 'mi_directorio/mi_archivo.txt', 'contenido del archivo como una cadena de texto' );
	*/
	function log_data($file='',$s=''){
		if($file==''){ return false; }
		if($s==''){ return false; }

		$lf = new logData();
		$lf->valid_dir_off();
		$lf->log_file( $file );
		$lf->log_add($s);

		unset( $lf );
	}

}

?>