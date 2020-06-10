<?php
include('libs/basics.php');
include('libs/querys.php');

$pack12 = 282;
$pack18 = 284;

for($i=293;$i<=3531;$i++){
        $s = "insert into catalog_product_link values(null,$pack12,$i,3)";
        $id = query($s);
        if($id==null){ echo "\n hubi un roblema."; return false; }
        echo "\n $pack12 -- $i";

        $s = "insert into catalog_product_link_attribute_decimal values(null,3,$id,1.0000)";
        query($s);

        $s = "insert into catalog_product_link values(null,$pack18,$i,3)";
        $id = query($s);
        echo "\n $pack18 -- $i";

        $s = "insert into catalog_product_link_attribute_decimal values(null,3,$id,1.0000)";
        query($s);
}


?>
