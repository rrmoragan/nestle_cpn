<?php
/* v1.0 */

class serverArgs{
	public $arg = null;

	public function __construct(){
		$a = $_SERVER['argv'];
		unset($a[0]);

		$this->arg = $a;

		return null;
	}

	public function list_all(){
		if( $this->arg == null ){ return null; }

		foreach ($this->arg as $et => $r) {
			tt( $r );
		}

		return null;
	}

	public function arg( $campo='' ){
		foreach ($this->arg as $et => $r) {
			$b = explode('=', $r);
			if($b[0]==$campo){ return true; }
		}

		return false;
	}
	public function arg_value( $campo='' ){
		foreach ($this->arg as $et => $r) {
			$b = explode('=', $r);
			if($b[0]==$campo){
				if( isset($b[1]) ){
					return $b[1];
				}
			}
		}

		return null;
	}
}

?>