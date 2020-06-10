<?php

/**
 * @category   Entrepids
 * @package    Entrepids_Maintenance
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */

class Entrepids_Maintenance_Block_Adminhtml_Code_Renderer extends Mage_Adminhtml_Block_System_Config_Form_Field{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        if (Mage::helper('core')->isModuleEnabled('Nexcessnet_Turpentine')) {
            $element->setDisabled('disabled');
        }
        return parent::_getElementHtml($element);
    }
}