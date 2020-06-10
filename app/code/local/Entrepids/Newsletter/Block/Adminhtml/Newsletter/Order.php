<?php

class Entrepids_Newsletter_Block_Adminhtml_Newsletter_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'entrepids_newsletter';
        $this->_controller = 'adminhtml_newsletter_order';
        $this->_headerText = Mage::helper('entrepids_newsletter')->__('Categories - Newsletter');
 
        parent::__construct();
    }
}

