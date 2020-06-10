<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Block_Adminhtml_Solutions_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'coffeesolutions';
        $this->_controller = 'adminhtml_solutions';
        $this->_headerText = Mage::helper('coffeesolutions')->__('Soluciones de Café');
        $this->_mode = 'edit';
        
        $this->_updateButton('save', 'label', Mage::helper('coffeesolutions')->__('Guardar'));
        $this->_updateButton('delete', 'label', Mage::helper('coffeesolutions')->__('Eliminar'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);
    }
    
    public function getHeaderText() {
        return Mage::helper('coffeesolutions')->__('Solución de Café');
    }

}
