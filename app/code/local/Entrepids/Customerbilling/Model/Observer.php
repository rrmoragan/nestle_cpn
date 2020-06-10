<?php
class Entrepids_Customerbilling_Model_Observer {

    public function updateCustomerBilling($event) {

        $customer = $event->getCustomer();
        if($customer->getId())
        {
            $address = $customer->getPrimaryBillingAddress();
        } else {
            return;
        }
        
        if ($address)
            return;
        
        $data = [
            'street' =>     '.....',
            //'company' => $customer->getCompany(),
            'postcode' =>   '99999',
            'region' =>     '.....',
            'region_id' =>  '485',
            'city' =>       '.....',
            'firstname' =>  $customer->getFirstname(),
            'lastname' =>   $customer->getLastname(),
            'telephone' =>  '000000000',
            'country_id' => 'MX',
            //'rfc' => $customer->getRfc()
        ];

        if($customer->getCompany() != "" || $customer->getCompany() != null)
        {
            $data["company"] = $customer->getCompany();
        }

        else
        {
            $data["company"] = "Por favor, ingrese una empresa o razón social";
        }

        try {
            $address = Mage::getModel('customer/address');
            $address->setCustomer($customer);
            $address->addData($data);
            $address->save();
            $customer->addAddress($address)
                    ->setDefaultBilling($address->getId())
                    ->save();
        } catch (Exception $ex) {
            Mage::logException($ex);
        }
    }

}