<?php
/**
* @category   Entrepids
* @package    Entrepids_Completeaddress
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Completeaddress_Block_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping{

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                /* if($type=='shipping' && $address->getIsBilling() == 1){
                    continue;
                } */
                if( $address->street!='Por favor, ingrese una calle válida' ){
                if( $address->street!='.....' ){
                    $options[] = array(
                        'value' => $address->getId(),
                        'label' => $address->format('oneline')
                    );
                }}
            }

            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                if ($type=='billing') {
                    $address = $this->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $this->getCustomer()->getPrimaryShippingAddress();
                }
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            /*
            if(!Mage::helper('entrepids_customer')->getCustomerProfileAAA()){
                $select->addOption('', Mage::helper('checkout')->__('New Address'));
            }*/
            $select->addOption('', Mage::helper('checkout')->__('New Address'));
            
            return $select->getHtml();
        }
        return '';
    }

    public function customerHasAddresses(){
        $count = 0;
        foreach ($this->getCustomer()->getAddresses() as $address) {
            if( $address->street!='Por favor, ingrese una calle válida' ){
            if( $address->street!='.....' ){
               if($address->getIsBilling() != '1') $count++; 
            }}
        }
        return $count;
    }
}