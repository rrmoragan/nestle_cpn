<?php
/**
* @category   Entrepids
* @package    Entrepids_CoffeSolutions
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_CoffeeSolutions_Model_Solutions extends Mage_Core_Model_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('coffeesolutions/solutions');
    }
    
}