<?php
/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
$installer = $this;

$installer->startSetup();

$arrayAttributes = array('numero_tazas_dia');

foreach($arrayAttributes as $attrCode){
    $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attrCode);
    $attributeModel->setUsedInProductListing(1)->save();
}

$installer->endSetup();