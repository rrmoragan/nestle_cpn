<?php
/**
* @category   Entrepids
* @package    Entrepids_Completeaddress
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_CoffeeSolutions_Model_Mysql4_SolutionPlaces_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        //parent::__construct();
        $this->_init('coffeesolutions/solutionPlaces');
    }
}