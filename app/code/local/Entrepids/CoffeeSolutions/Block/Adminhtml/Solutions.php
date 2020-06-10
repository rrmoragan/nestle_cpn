<?php
/**
* @category   Entrepids
* @package    Entrepids_CoffeeSolutions
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_CoffeeSolutions_Block_Adminhtml_Solutions extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'coffeesolutions';
        $this->_controller = 'adminhtml_solutions';
        $this->_headerText = Mage::helper('coffeesolutions')->__('Soluciones de Café');
        parent::__construct();
    }
}