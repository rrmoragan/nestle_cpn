<?php
require_once(Mage::getModuleDir('controllers','Mage_Newsletter').DS.'ManageController.php');
class Entrepids_Newsletter_ManageController extends  Mage_Newsletter_ManageController{


    const XML_PATH_SUCCESS_EMAIL_TEMPLATE       = 'newsletter/subscription/success_email_template';
    const XML_PATH_SUCCESS_EMAIL_IDENTITY       = 'newsletter/subscription/success_email_identity';
    const XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE   = 'newsletter/subscription/un_email_template';
    const XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY   = 'newsletter/subscription/un_email_identity';

    public function saveAction(){
       if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/');
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
 
            // Load the customer's data
            $customer = Mage::getSingleton('customer/session')->getCustomer();
         
            $customerName   =  $customer->getName(); 
            $customerEmail  =  $customer->getEmail();
        }
        
        $ids = $this->getRequest()->getParam("ids");
        $cats = "Te has suscrito a las siguientes categorías de productos: ";
        $i= 0;

        try {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            Mage::getModel("entrepids_newsletter/subscriptions")->deleteAllSubscriptionsByCustomerId();
            if(count($ids)> 0){
                foreach($ids as $id){
                    $i++;
                    Mage::getModel("entrepids_newsletter/subscriptions")
                            ->setCatalogId($id)
                            ->setCustomerId($customerId)
                            ->save();
                    $currentCat = Mage::getModel("entrepids_newsletter/catalog")->load($id);
                    $name = $currentCat->getName();
                    if($i==count($ids))
                    {
                        $cats.="y ".$name.".";
                    }

                    else
                    {
                        $cats.=$name.", ";
                    }
                    
                }

                $translate = Mage::getSingleton('core/translate');
                /* @var $translate Mage_Core_Model_Translate */
                $translate->setTranslateInline(false);

                $email = Mage::getModel('core/email_template');

                $email->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
                    $customerEmail,
                    $customerName,
                    array('subscriber'=>null, 'categories' =>$cats)
                );

                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
            }

            else
            {
                $translate = Mage::getSingleton('core/translate');
                /* @var $translate Mage_Core_Model_Translate */
                $translate->setTranslateInline(false);

                $email = Mage::getModel('core/email_template');

                $email->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY),
                    $customerEmail,
                    $customerName,
                    array('subscriber'=>null)
                );

                $translate->setTranslateInline(true);
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
        }
        $this->_redirect('newsletter/manage/');
    }
    
   
   
}
 
