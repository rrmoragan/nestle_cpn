<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


class Entrepids_Nestle_Block_Adminhtml_Customer_Prospects extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct()
    {
        $this->_blockGroup = 'nestle';
        $this->_controller = 'adminhtml_customer_prospects';
        $this->_headerText = $this->__('Clientes Prospecto');
 
        parent::__construct();
        $this->_removeButton('add');
    }
}