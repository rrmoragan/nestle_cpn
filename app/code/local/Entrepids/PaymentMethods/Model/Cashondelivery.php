<?php 
/**
* @category   Entrepids
* @package    Entrepids_PaymentMethods
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_PaymentMethods_Model_Cashondelivery extends Mage_Payment_Model_Method_Cashondelivery{

    public function canUseCheckout()
    {
        if(Mage::helper('entrepids_customer')->getCustomerProfileAAA()) return true;
        
        return false;
    }
}