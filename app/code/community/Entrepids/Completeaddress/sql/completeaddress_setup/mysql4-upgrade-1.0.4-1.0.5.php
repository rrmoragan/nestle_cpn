<?php
/**
* @category   Entrepids
* @package    Entrepids_Completeaddress
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
$installer = $this; 
$installer->startSetup();

$qa = $this->getTable('sales/quote_address');
$installer->run("
    ALTER TABLE  $qa ADD  `rfc` varchar(15) DEFAULT NULL COMMENT 'RFC'
");
 
$oa = $this->getTable('sales/order_address');
$installer->run("
    ALTER TABLE  $oa ADD  `rfc` varchar(15) DEFAULT NULL COMMENT 'RFC'
");

$installer->endSetup();