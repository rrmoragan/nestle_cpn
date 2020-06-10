<?php
/**
* @category   Entrepids
* @package    Entrepids_Completeaddress
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_CoffeeSolutions_Model_Mysql4_Solutions extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {  
        $this->_init('coffeesolutions/solutions', 'entity_id');
    }
    
}