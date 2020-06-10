<?php

class Entrepids_Newsletter_Block_Adminhtml_Subscribers_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'entrepids_newsletter';
        $this->_controller = 'adminhtml_subscribers_order';
        $model = Mage::getModel("entrepids_newsletter/catalog");
        $model->load($this->getRequest()->getParam("id"));
        $this->_headerText = Mage::helper('entrepids_newsletter')->__($model->getName().' - Subscribers');
        parent::__construct();
        $this->_removeButton('add');
    }
}
