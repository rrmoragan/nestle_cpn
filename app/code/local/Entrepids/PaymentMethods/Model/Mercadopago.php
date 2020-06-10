<?php 
/**
* @category   Entrepids
* @package    Entrepids_PaymentMethods
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_PaymentMethods_Model_Mercadopago extends MercadoPago_Core_Model_Standard_Payment{

    public function canUseCheckout()
    {
        if(!Mage::helper('entrepids_customer')->getCustomerProfileAAA()) return true;
        
        return false;
    }
}