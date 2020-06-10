<?php

class Entrepids_Newsletter_Model_Subscriptions extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('entrepids_newsletter/subscriptions');
    }
    
    public function deleteAllSubscriptionsByCustomerId(){
        if(Mage::getSingleton('customer/session')->getCustomer()->getId() != null){
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $db_read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $db_write1 = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql2 = 'DELETE FROM `' . $tablePrefix . 'news_subscriptions` WHERE customer_id='.$customerId;
            $db_write1->query($sql2);       
        }
    }
    
    public function deleteAllSubscriptionsByCatalogId($idCatalog = null){
        if($idCatalog != null){
            $db_read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $db_write1 = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql2 = 'DELETE FROM `' . $tablePrefix . 'news_subscriptions` WHERE catalog_id='.$idCatalog;
            $db_write1->query($sql2);    
        }
    }
    
    public function deleteUserSubscription($idUser = null, $idCatalog =null){
        if($idUser != null && $idCatalog != null){
            
            $db_read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $db_write1 = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sql2 = 'DELETE FROM `' 
                    . $tablePrefix . 'news_subscriptions` '
                    . 'WHERE customer_id='.$idUser
                    .' AND catalog_id ='.$idCatalog;
            $db_write1->query($sql2);       
        }
    }
    
    public function massiveDelete($idsUser = null , $idCatalog){
        if($idsUser != null && $idCatalog != null 
                && is_array($idsUser)){
            $db_read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $db_write1 = Mage::getSingleton('core/resource')->getConnection('core_write');
            foreach($idsUser as $id){
            $sql2 = 'DELETE FROM `' 
                    . $tablePrefix . 'news_subscriptions` '
                    . 'WHERE customer_id='.$id
                    .' AND catalog_id ='.$idCatalog;
            $db_write1->query($sql2);     
            }
            
        }
    }
}