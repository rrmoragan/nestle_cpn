<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


class Entrepids_Nestle_Model_Mysql4_Prospectcustomers_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('nestle/prospectcustomers');
    }
}