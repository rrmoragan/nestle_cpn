<?php
/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
$installer = $this;

$installer->startSetup();

$arrayAttributes = array('description','product_url','price','final_price','is_professional','image','image_url','nombre_secundario','numero_tazas_producto');

foreach($arrayAttributes as $attrCode){
    $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attrCode);
    $attributeModel->setUsedInProductListing(1)->save();
}

$installer->endSetup();