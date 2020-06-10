<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


class Entrepids_Nestle_Model_Prospectcustomers extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('nestle/prospectcustomers');
    }
}