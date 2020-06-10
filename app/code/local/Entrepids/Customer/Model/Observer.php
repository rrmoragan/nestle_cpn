<?php
/**
* @category   Entrepids
* @package    Entrepids_Customer
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Customer_Model_Observer {
    private function error($rfc){
        if (!preg_match("/^([A-Z|a-z]{3,4}([0-9]{2})((02(0[1-9]|1[0-9]|2[0-9]))|((0[1|3|5|7|8]|1[0|2])(0[1-9]|1[0-9]|2[0-9]|3[0-1]))|((0[4|6|9]|11)(0[1-9]|1[0-9]|2[0-9]|3[0])))[(A-Z|a-z)|\d]{3})$/",$rfc)){
            $message = Mage::helper("entrepids_customer")->__('El RFC no tiene un formato valido.');
            Mage::getSingleton('core/session')->addError($message); 
            throw new Exception($message, 1);
        }
    }

    public function validateCustomer($observer){
        $customer = $observer->getEvent()->getCustomer();
        if(!empty($customer->getRfc())){
            $this->error($customer->getRfc());
        }
        
        foreach($observer->getEvent()->getCustomer()->getAddresses() as $address){
            if($address->getIsBilling() == 1){
                if(!empty($address->getRfc())){
                    $this->error($address->getRfc());
                }else{
                    $message = Mage::helper("entrepids_customer")->__('El RFC es requerido.');
                    Mage::getSingleton('core/session')->addError($message); 
                    throw new Exception($message, 1);
                }
            }
        }
        return $this;
    }
}