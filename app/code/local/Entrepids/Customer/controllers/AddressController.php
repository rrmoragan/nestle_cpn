<?php
/**
* @category   Entrepids
* @package    Entrepids_Customer
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

include(Mage::getBaseDir().'/app/code/core/Mage/Customer/controllers/AddressController.php');

class Entrepids_Customer_AddressController extends Mage_Customer_AddressController{
    
    /**
     * Customer addresses list
     */

    public function indexAction()
    {
        $customer3A = Mage::helper('entrepids_customer')->getCustomerProfileAAA();

        if (count($this->_getSession()->getCustomer()->getAddresses()) || $customer3A) {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');

            $block = $this->getLayout()->getBlock('address_book');
            if ($block) {
                $block->setRefererUrl($this->_getRefererUrl());
            }
            $this->renderLayout();
        } else {
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/new'));
        }
    }

    private function onEditAddress(){
        if(Mage::helper('entrepids_customer')->getCustomerProfileAAA()){
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');

            $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
            if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
                $this->_forward('defaultNoRoute');
            }
            return false;
        }
        return true;
    }

    public function newAction(){
        if($this->onEditAddress()){
            parent::newAction();
        }
    }

    public function editAction(){
        if($this->onEditAddress()){
            parent::newAction();
        }
    }
}