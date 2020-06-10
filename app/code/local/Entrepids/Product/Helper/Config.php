<?php
/**
* @category   Entrepids
* @package    Entrepids_Product
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Product_Helper_Config extends Mage_Core_Helper_Abstract{
    # Min price required
    const MINPRICE = "catalog/publicationrules/min_price_required";

    public static function getMinPrice($store = null)
    {   
        return intval(Mage::getStoreConfig(self::MINPRICE, $store));
    }
}