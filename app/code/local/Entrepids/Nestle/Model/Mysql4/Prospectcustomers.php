<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


class Entrepids_Nestle_Model_Mysql4_Prospectcustomers extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('nestle/prospectcustomers', 'id');
    }
}