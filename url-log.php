<?php
function save_file($dir='',$file='', $dat='', $modo='a'){
        if( !file_exists( $dir ) ){
                $dir = '';
        }
	if( $f = fopen($dir.$file, $modo) ){
		fwrite($f, $dat);
		fclose($f);
		return true;
	}

	tt('error: open file ==> ['.$file.']');
	return false;
}

define( 'UL','1.0' );
define( 'UL_COMPLETE', 0 );

$S = $_SERVER;

$t = time();
$d = date('Y/m/d G:i:s',$t);
$ip = $S['REMOTE_ADDR'];
if( isset($S['HTTP_X_REAL_IP']) ){
	$ip = $S['HTTP_X_REAL_IP'];
}

$murl = explode( '/turpentine/esi/getBlock/', $S['REQUEST_URI'] );
if( !empty($murl[1]) ){ $S['REQUEST_URI']='/turpentine/esi/getBlock/'; }

$url = $S['REQUEST_SCHEME'].'//'.$S['HTTP_HOST'].$S['REQUEST_URI'];
$agent = $S['HTTP_USER_AGENT'];

$s = "\n:::: [$t][$d][$ip][$url]";
//$s = "\n:::: :::: [$t][$d][$ip][".$S['REQUEST_URI']."]";

if( UL_COMPLETE == 1 ){
	$s = $s."\n:::: [$agent]";
        $s = $s."\n:::: [GET][".print_r($_GET,true)."]";
        $s = $s."\n:::: [POST][".print_r($_POST,true)."]";        
}

$tt = mktime( 0,0,0, date('m',$t),date('d',$t),date('Y',$t) );
$dir  = 'var/log/';
$file = 'url-'.$tt.'.log';

save_file( $dir,$file,$s );

?>
