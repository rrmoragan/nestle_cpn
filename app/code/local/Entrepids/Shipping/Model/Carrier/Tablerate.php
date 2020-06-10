<?php

class Entrepids_Shipping_Model_Carrier_Tablerate extends Mage_Shipping_Model_Carrier_Tablerate
{
    public $_prods = null; /* listado de los productos que se quieren adquirir */

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        echo '<!-- collectRates on -->';
        Mage::log("calculando precio envio paso 1", Zend_Log::INFO, 'rmorales.log');
        //Helper to validate A users
        $helper = Mage::helper('entrepids_customer');
        //If it is a "A" user show this rate
        if (!$this->getConfigFlag('active') || $helper->getCustomerProfileAAA()) {
            return false;
        }
        Mage::log("calculando precio envio paso 2", Zend_Log::INFO, 'rmorales.log');

        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        Mage::log("calculando precio envio paso 3", Zend_Log::INFO, 'rmorales.log');
        $freeQty = 0;
        $freePackageValue = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {

                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
                            $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeShipping = is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0;
                    $freeQty += $item->getQty() - $freeShipping;
                    $freePackageValue += $item->getBaseRowTotal();
                }
            }
            $oldValue = $request->getPackageValue();
            $request->setPackageValue($oldValue - $freePackageValue);
        }

        if ($freePackageValue) {
            $request->setPackageValue($request->getPackageValue() - $freePackageValue);
        }
        if (!$request->getConditionName()) {
            $conditionName = $this->getConfigData('condition_name');
            $request->setConditionName($conditionName ? $conditionName : $this->_default_condition_name);
        }

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        $result = $this->_getModel('shipping/rate_result');
        $rate = $this->getRate($request);   // consulta sql ==> regresa precio de envio por numero de productos

        //Mage::log("numero de producto ==> oldQty ==> ".print_r($oldQty,true), Zend_Log::INFO, 'rmorales.log');
        //Mage::log("array precio de envio por numero de productos ==> rate ==> ".print_r($rate,true), Zend_Log::INFO, 'rmorales.log');

        $request->setPackageWeight($oldWeight); // numero de productos por peso volumetrico
        $request->setPackageQty($oldQty);   // numero de productos pedidos

        Mage::log("calculando precio envio paso 4", Zend_Log::INFO, 'rmorales.log');
        
        if (!empty($rate) && $rate['price'] >= 0) {
            Mage::log("calculando precio envio paso 5", Zend_Log::INFO, 'rmorales.log');

            $method = $this->_getModel('shipping/rate_result_method');

            $method->setCarrier('tablerate');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('bestway');
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
                $shippingPrice = 0;
                Mage::log('shippingPrice 1 ==> '.$shippingPrice, Zend_Log::INFO, 'rmorales.log');
            } else {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
                Mage::log('shippingPrice 2 ==> '.$shippingPrice, Zend_Log::INFO, 'rmorales.log');
                Mage::log('rate ==> '.print_r( $rate['price'], true ), Zend_Log::INFO, 'rmorales.log');
                Mage::log('rate ==> '.print_r( $rate, true ), Zend_Log::INFO, 'rmorales.log');
            }

	       /* modif rmorales@mlg.com.mx */

            $this->_listado_productos($request);
            $shippingPrice = $shippingPrice + $this->agregar_comision( $shippingPrice );
            Mage::log( 'total envio ==> '.$shippingPrice, Zend_Log::INFO, 'rmorales.log');

	       /* fin modif */

            $method->setPrice($shippingPrice);
            $method->setCost($rate['cost']);

            $result->append($method);
        } elseif (empty($rate) && $request->getFreeShipping() === true) {
            Mage::log("calculando precio envio paso 6", Zend_Log::INFO, 'rmorales.log');
            /**
             * was applied promotion rule for whole cart
             * other shipping methods could be switched off at all
             * we must show table rate method with 0$ price, if grand_total more, than min table condition_value
             * free setPackageWeight() has already was taken into account
             */
            $request->setPackageValue($freePackageValue);
            $request->setPackageQty($freeQty);
            $rate = $this->getRate($request);
            if (!empty($rate) && $rate['price'] >= 0) {
                $method = $this->_getModel('shipping/rate_result_method');

                $method->setCarrier('tablerate');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod('bestway');
                $method->setMethodTitle($this->getConfigData('name'));

                $method->setPrice(0);
                $method->setCost(0);

                $result->append($method);
            }
        } else {
            Mage::log("calculando precio envio paso 7", Zend_Log::INFO, 'rmorales.log');

            $error = $this->_getModel('shipping/rate_result_error');
            $error->setCarrier('tablerate');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }

        return $result;
    }

    /* modif rmorales@mlg.com.mx */

    public function agregar_comision($shippingPrice=0){
        /* status al 2019 / enero / 20
            la tabla de costos de envios incluye
                costo de envio
                costo de empaque
                cosot de seguro

            solo queda agregar los comisiones del motor de pagos
            en este caso las comisiones son 
         */

        /* porcentaje */
        $comision = Mage::getModel('core/variable')->loadByCode('articulo_comision')->getValue('plain');
        $transaccion = Mage::getModel('core/variable')->loadByCode('comision_transaccion')->getValue('plain');
        $subtotal   = Mage::getModel('checkout/session')->getQuote()->getSubtotal();

        $comision = round( $comision/100, 2 );
        $comision = round( $subtotal * $comision, 2) ;

        $agregar_comision = round( $comision + $transaccion, 2 );

        Mage::log( 'precio envio ==> '.$shippingPrice, Zend_Log::INFO, 'rmorales.log');
        Mage::log( 'costo comision ==> '.$comision, Zend_Log::INFO, 'rmorales.log');
        Mage::log( 'costo transaccion ==> '.$transaccion, Zend_Log::INFO, 'rmorales.log');
        Mage::log( 'mosicion + transaccion ==> '.$agregar_comision, Zend_Log::INFO, 'rmorales.log');

        return $agregar_comision;
    }

    /* obtiene los datos de los productos solicitados */
    public function _listado_productos(Mage_Shipping_Model_Rate_Request $request){

        $p = null;
        $prod = null;

        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                $p[ $item->getProductId() ] = 1;
            }

            if( $p ){
                foreach ($p as $et => $r) {
                    $prod[ $et ] = Mage::getModel('catalog/product')->load( $et );
                }
            }

            $this->_prods = $prod;

            return true;
        }

        return false;
    }

    /* fin modif */
}
