<?php
class Entrepids_Newsletter_Block_Adminhtml_Form_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                  
        $this->_objectId = 'id';
        $this->_blockGroup = 'entrepids_newsletter';
        $this->_controller = 'adminhtml_form';
         
        $this->_updateButton('save', 'label', Mage::helper('entrepids_newsletter')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('entrepids_newsletter')->__('Delete'));
         
       
    }
 
    public function getHeaderText()
    {
        return Mage::helper('entrepids_newsletter')->__('Formulario Categoría');
    }
}