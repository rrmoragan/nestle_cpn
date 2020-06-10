<?php

/*
 * @category    Entrepids
 * @package     Entrepids_StockAlert
 * @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
 * @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class Entrepids_StockAlert_Model_Observer {

    public function catalogInventorySave(Varien_Event_Observer $observer) {
        if (Mage::helper('stockalert')->isEnabled()) {

            $minGlobalStock = Mage::getStoreConfig('cataloginventory/item_options/min_qty');

            $email = Mage::helper('stockalert')->getEmails();

            $event = $observer->getEvent();
            $_item = $event->getItem();

            $id = $_item->getId();
            $product = Mage::getModel('catalog/product')->load($id);

            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $minProductStock = $stock->getMinQty();

            if ((int) $_item->getData('qty') != (int) $_item->getOrigData('qty')) {

                $sku = $product->getSku();
                $currentQty = $_item->getQty();

                if ($currentQty <= $minGlobalStock) {
                    //Mail
                    Mage::getModel('stockalert/email')->sendEmail(
                            'entrepids_stockalert_template', 
                            array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 
                                'email' => Mage::getStoreConfig('trans_email/ident_general/email')), 
                            $email, 
                            "Admin Nestlé", 
                            Mage::helper('adminhtml')->__('Low stock notifications'), 
                            array('sku' => $sku, 'qty' => $currentQty)
                    );
                } elseif ($currentQty <= $minProductStock) {
                    //Mail
                    Mage::getModel('stockalert/email')->sendEmail(
                            'entrepids_stockalert_template', 
                            array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 
                                'email' => Mage::getStoreConfig('trans_email/ident_general/email')), 
                            $email, 
                            "Admin Nestlé", 
                            Mage::helper('adminhtml')->__('Low stock notifications'), 
                            array('sku' => $sku, 'qty' => $currentQty)
                    );
                    
                }
                Mage::getModel('productalert/observer')->process();
                $this->flushCacheAfterStockChange();
            }
        }
    }

    public function subtractQuoteInventory(Varien_Event_Observer $observer) {
        if (Mage::helper('stockalert')->isEnabled()) {

            $minGlobalStock = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
            $email = Mage::helper('stockalert')->getEmails();

            $quote = $observer->getEvent()->getQuote();
            foreach ($quote->getAllItems() as $item) {

                $sku = $item->getSku();
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct());
                $minProductStock = $stock->getMinQty();
                $currentQty = $item->getProduct()->getStockItem()->getQty() - $item->getTotalQty();

                if ($currentQty <= $minGlobalStock) {
                    //Mail
                    Mage::getModel('stockalert/email')->sendEmail(
                            'entrepids_stockalert_template', array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email')), $email, "Admin Nestlé", Mage::helper('adminhtml')->__('Low stock notifications'), array('sku' => $sku, 'qty' => $currentQty)
                    );
                } elseif ($currentQty <= $minProductStock) {
                    //Mail
                    Mage::getModel('stockalert/email')->sendEmail(
                            'entrepids_stockalert_template', array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email')), $email, "Admin Nestlé", Mage::helper('adminhtml')->__('Low stock notifications'), array('sku' => $sku, 'qty' => $currentQty)
                    );
                }
                $this->flushCacheAfterStockChange();
            }
        }
    }

    public function revertQuoteInventory(Varien_Event_Observer $observer) {
        if (Mage::helper('stockalert')->isEnabled()) {

            $minGlobalStock = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
            $email = Mage::helper('stockalert')->getEmails();

            $quote = $observer->getEvent()->getQuote();
            foreach ($quote->getAllItems() as $item) {

                $sku = $item->getSku();
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct());
                $minProductStock = $stock->getMinQty();
                $currentQty = $item->getProduct()->getStockItem()->getQty();

                if ($currentQty <= $minGlobalStock) {
                    //Mail
                    Mage::getModel('stockalert/email')->sendEmail(
                            'entrepids_stockalert_template', array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email')), $email, "Admin Nestlé", Mage::helper('adminhtml')->__('Low stock notifications'), array('sku' => $sku, 'qty' => $currentQty)
                    );
                } elseif ($currentQty <= $minProductStock) {
                    //Mail
                    Mage::getModel('stockalert/email')->sendEmail(
                            'entrepids_stockalert_template', array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email')), $email, "Admin Nestlé", Mage::helper('adminhtml')->__('Low stock notifications'), array('sku' => $sku, 'qty' => $currentQty)
                    );
                }
                $this->flushCacheAfterStockChange();
            }
        }
    }

    public function cancelOrderItem(Varien_Event_Observer $observer) {
        if (Mage::helper('stockalert')->isEnabled()) {

            $minGlobalStock = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
            $email = Mage::helper('stockalert')->getEmails();

            $item = $observer->getEvent()->getItem();

            $sku = $item->getSku();
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct());
            $minProductStock = $stock->getMinQty();
            $currentQty = $item->getProduct()->getStockItem()->getQty();

            if ($currentQty <= $minGlobalStock) {
                //Mail
                Mage::getModel('stockalert/email')->sendEmail(
                        'entrepids_stockalert_template', array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email')), $email, "Admin Nestlé", Mage::helper('adminhtml')->__('Low stock notifications'), array('sku' => $sku, 'qty' => $currentQty)
                );
            } elseif ($currentQty <= $minProductStock) {
                //Mail
                Mage::getModel('stockalert/email')->sendEmail(
                        'entrepids_stockalert_template', array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email')), $email, "Admin Nestlé", Mage::helper('adminhtml')->__('Low stock notifications'), array('sku' => $sku, 'qty' => $currentQty)
                );
            }
            $this->flushCacheAfterStockChange();
        }
    }

    public function refundOrderInventory(Varien_Event_Observer $observer) {
        if (Mage::helper('stockalert')->isEnabled()) {

            $minGlobalStock = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
            $email = Mage::helper('stockalert')->getEmails();

            $creditmemo = $observer->getEvent()->getCreditmemo();
            foreach ($creditmemo->getAllItems() as $item) {

                $sku = $item->getSku();
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProduct());
                $minProductStock = $stock->getMinQty();
                $currentQty = $item->getProduct()->getStockItem()->getQty();

                if ($currentQty <= $minGlobalStock) {
                    //Mail
                    Mage::getModel('stockalert/email')->sendEmail(
                            'entrepids_stockalert_template', array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email')), $email, "Admin Nestlé", Mage::helper('adminhtml')->__('Low stock notifications'), array('sku' => $sku, 'qty' => $currentQty)
                    );
                } elseif ($currentQty <= $minProductStock) {
                    //Mail
                    Mage::getModel('stockalert/email')->sendEmail(
                            'entrepids_stockalert_template', array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email')), $email, "Admin Nestlé", Mage::helper('adminhtml')->__('Low stock notifications'), array('sku' => $sku, 'qty' => $currentQty)
                    );
                }
                $this->flushCacheAfterStockChange();
            }
        }
    }
    
    private function flushCacheAfterStockChange(){
        $turpentineEnabled = Mage::helper('core')->isModuleEnabled('Nexcessnet_Turpentine');
        if($turpentineEnabled && Mage::helper('turpentine/varnish')->getVarnishEnabled()){
            Mage::getModel('turpentine/varnish_admin')->flushAll();
        }
    }
}
