<?php

class Entrepids_Newsletter_Model_Resource_Subscriptions_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    protected function _construct()
    {
            $this->_init('entrepids_newsletter/subscriptions');
    }
    
    public function deleteAllSubscriptions(){
        $db_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $db_write1 = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql2 = 'DELETE FROM `' . $tablePrefix . 'news_subscriptions` WHERE customer_id='.Mage::getSingleton('customer/session')->getCustomer()->getId();
        $db_write1->query($sql2);       
    }
}