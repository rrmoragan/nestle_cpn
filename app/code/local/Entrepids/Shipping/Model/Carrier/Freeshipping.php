<?php
class Entrepids_Shipping_Model_Carrier_Freeshipping extends Mage_Shipping_Model_Carrier_Freeshipping
{
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        //Helper to validate AAA users
        $helper = Mage::helper('entrepids_customer');
        //If it is a "AAA" user show this rate
        if (!$this->getConfigFlag('active') || !$helper->getCustomerProfileAAA()) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $this->_updateFreeMethodQuote($request);

        if (($request->getFreeShipping())
            || ($request->getBaseSubtotalInclTax() >=
                $this->getConfigData('free_shipping_subtotal'))
        ) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('freeshipping');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('freeshipping');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice('0.00');
            $method->setCost('0.00');

            $result->append($method);
        }

        return $result;
    }
}