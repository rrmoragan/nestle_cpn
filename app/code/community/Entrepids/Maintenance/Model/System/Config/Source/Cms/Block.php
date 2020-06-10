<?php

/**
 * @category   Entrepids
 * @package    Entrepids_Maintenance
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_Maintenance_Model_System_Config_Source_Cms_Block {

    protected $_options;

    public function toOptionArray() {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('cms/page_collection')
                    //->addStoreFilter($this->getCurrentStoreId())
                    ->addFieldToFilter('is_active', 1)
                    ->load()
                    ->toOptionArray();
        }
        return $this->_options;
    }

    protected function getCurrentStoreId() {
        if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) { 
            $store_id = Mage::getModel('core/store')->load($code)->getId();
        } elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) {
            $website_id = Mage::getModel('core/website')->load($code)->getId();
            $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
        } else {
            $store_id = 0;
        }
        return $store_id;
    }

}
