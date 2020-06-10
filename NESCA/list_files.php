<?php

$domain = 'cafeparaminegocio.com.mx';
$dir = '.';
$l = scandir( $dir );

echo '<tt>';
foreach( $l as $et => $r ){
	echo "\n".'<a href="http://'.$domain.'/NESCA/'.$r.'" target = "get_email">'.$r.'</a><br />';
}
echo '</tt>';

?>
