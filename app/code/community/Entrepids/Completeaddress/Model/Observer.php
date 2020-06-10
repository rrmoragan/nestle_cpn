<?php
/**
* @category   Entrepids
* @package    Entrepids_Completeaddress
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Completeaddress_Model_Observer
{
    public function isThisBilling($observer) {
        $address = $observer->getEvent()->getCustomerAddress();
        $data = Mage::app()->getFrontController()->getRequest()->getParams();
        
        if(!$address->getId() && isset($data['is_billing']) && $data['is_billing'] == "1"){
            $address->setIsBilling("1");
            $address->setRFC($data['rfc']);
        }
        return $this;
    }
}