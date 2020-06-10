<?php
/**
* @category   Entrepids
* @package    Entrepids_Completeaddress
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Completeaddress_Block_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing{

    public function getAddressesBillingHtmlSelect()
    {
        if ($this->isCustomerLoggedIn()) {

            $primaryBillingAddress=$this->getCustomer()->getPrimaryBillingAddress();

            if( count( $this->getCustomer()->getAddresses() )>1 ){
                foreach ($this->getCustomer()->getAddresses() as $address) {
                     if( $address->getIsBilling()==1 ){
                        if( $primaryBillingAddress != $address->getId() ){
                            $address->setIsDefaultBilling( '1' );
                            $address->save();
                            break;
                        }
                     }
                }
            }

            $options = array();

            foreach ($this->getCustomer()->getAddresses() as $address) {
                if( $address->getIsBilling()==1 ){
                    if( $address->getRfc() ){
                        $options[] = array(
                            'value' => $address->getId(),
                            'label' => "rfc ".$address->getRfc().", ".$address->getCompany()
                        );
                    }
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')

                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setValue($addressId)
                ->setOptions($options);

            return $select->getHtml();
        }
        return '';
    }

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                if($type=='billing' && $address->getIsBilling() == 0){
                    continue;
                }else if($type=='shipping' && $address->getIsBilling() == 1){
                    continue;
                }
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $type=='billing' ? $this->getAddress()->getRfc().", ".$this->getCustomer()->getCompany() : $address->format('oneline')
                );
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

            if(!Mage::helper('entrepids_customer')->getCustomerProfileAAA()){
                //$select->addOption('', Mage::helper('checkout')->__('New Address'));
            }

            return $select->getHtml();
        }
        return '';
    }

    public function customerHasAddresses(){
        $count = 0;
        foreach ($this->getCustomer()->getAddresses() as $address) {
            if($address->getIsBilling() == '1') $count++; 
        }
        return $count;
    }
}