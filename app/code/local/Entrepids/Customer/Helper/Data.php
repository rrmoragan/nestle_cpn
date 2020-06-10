<?php
/**
* @category   Entrepids
* @package    Entrepids_Customer
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Customer_Helper_Data extends Mage_Core_Helper_Abstract{

    public function getCustomerProfileAAA(){
        if(Mage::getSingleton('customer/session')->getCustomer()->getGroupId() == 2) return true;

        return false;
    }

    public function getAttributeValidationClass($attributeCode)
    {
        /** @var $attribute Mage_Customer_Model_Attribute */
        $attribute = isset($this->_attributes[$attributeCode]) ? $this->_attributes[$attributeCode]
            : Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);
        $class = $attribute ? $attribute->getFrontend()->getClass() : '';

        return $class;
    }

}