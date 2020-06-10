<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Nestle_Adminhtml_ProspectsController extends Mage_Adminhtml_Controller_Action{
     public function indexAction()
    {
        $this->_title($this->__('Customer'))->_title($this->__('Clientes Prospecto'));
        $this->loadLayout();
        $this->_setActiveMenu('customer/prospects');
        $this->_addContent($this->getLayout()->createBlock('nestle/adminhtml_customer_prospects'));
        $this->renderLayout();
    }
}
