<?php
/**
* @category   Entrepids
* @package    Entrepids_Product
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Product_Helper_Data extends Mage_Core_Helper_Abstract{
    # Get main email's
    const PROFESSIONALTEXT = "isprofessionaltext/isprofessional/professionaltext";

    public static function getProfessionalText($store = null)
    {
        return Mage::getStoreConfig(self::PROFESSIONALTEXT, $store);
    }
}