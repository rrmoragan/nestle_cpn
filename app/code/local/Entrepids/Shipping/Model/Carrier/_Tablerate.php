<?php

class Entrepids_Shipping_Model_Carrier_Tablerate extends Mage_Shipping_Model_Carrier_Tablerate
{
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        //Helper to validate A users
        $helper = Mage::helper('entrepids_customer');
        //If it is a "A" user show this rate
        if (!$this->getConfigFlag('active') || $helper->getCustomerProfileAAA()) {
            return false;
        }

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
        $rate = $this->getRate($request);   // regresa el resultado de la consulta
        //echo '<pre style="display:none;">'; echo 'oldQty '; print_r($rate); echo '</pre>';
        /*
        Array
        (
            [pk] => 26
            [website_id] => 1
            [dest_country_id] => MX
            [dest_region_id] => 0
            [dest_zip] => 
            [condition_name] => package_weight
            [condition_value] => 4.6000
            [price] => 591.0000
            [cost] => 0.0000
        )
        */

        // echo '<pre style="display:none;">'; echo 'oldWeight '; print_r($oldWeight); echo '</pre>';
        //echo '<pre style="display:none;">'; echo 'oldQty '; print_r($oldQty); echo '</pre>';
        $request->setPackageWeight($oldWeight); // 4.8, numero de productos por peso volumetrico
        $request->setPackageQty($oldQty);   // 16, numero de productos pedidos

        if (!empty($rate) && $rate['price'] >= 0) {
            $method = $this->_getModel('shipping/rate_result_method');

            $method->setCarrier('tablerate');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('bestway');
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
                $shippingPrice = 0;
            } else {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
            }

            // $shippingPrice = 591

            // 1.20 % 
            $articulo_seguro = Mage::getModel('core/variable')->loadByCode('articulo_seguro')->getValue('plain') / 100;
            // 3.00 %
            $articulo_comision = Mage::getModel('core/variable')->loadByCode('articulo_comision')->getValue('plain') / 100;

            $price_prod_seguro = 0;
            $price_prod_comision = 0;
            if ( $request->getAllItems() ) {
                $_ag = 0;
                foreach ($request->getAllItems() as $item) {
                    $_product = Mage::getModel('catalog/product')->load( $item->getProductId() );

                    if( $_ag>0 ){
                        $_ag--;
                        continue;
                    }

                    $_pp = 0;
                    $_pp = $_product->_data['price_list'];  // precio lista
                    $_pl = $_pp * $item->getQty();

                    $price_prod_seguro +=   ( $_pl * $articulo_seguro );
                    $price_prod_comision += ( $_pl * $articulo_comision );


                    if( $_product->_data['configurable_precio']>0 ){
                        $_ag = $_product->_data['configurable_max_prods'];
                    }

                    //echo '<pre style="display:none;">data '; print_r($_product->_data); echo '</pre>';
                    /*
                    echo "\n".'<tt style="display:none;">precio precio lista '.$_pp.'</tt>';
                    echo "\n".'<tt style="display:none;">cantidad pedida '.$item->getQty().'</tt>';
                    echo "\n".'<tt style="display:none;">seguro % '.$articulo_seguro.'</tt>';
                    echo "\n".'<tt style="display:none;">comision % '.$articulo_comision.'</tt>';
                    echo "\n".'<tt style="display:none;">seguro '.$price_prod_seguro.'</tt>';
                    echo "\n".'<tt style="display:none;">comision '.$price_prod_comision.'</tt>';
                    */
                }
            }

            /*
            echo "\n\n".'<tt style="display:none;">precio envio inicial '.$shippingPrice.'</tt>';
            echo "\n".'<tt style="display:none;">suma comision '.$price_prod_comision.'</tt>';
            echo "\n".'<tt style="display:none;">suma seguro '.$price_prod_seguro.'</tt>';
            echo "\n".'<tt style="display:none;">precio envio final '.($shippingPrice + $price_prod_seguro + $price_prod_comision).'</tt>'."\n";
            */

            $shippingPrice = $shippingPrice + $price_prod_seguro + $price_prod_comision;

            $method->setPrice($shippingPrice);
            $method->setCost($rate['cost']);

            $result->append($method);
        } elseif (empty($rate) && $request->getFreeShipping() === true) {
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
            $error = $this->_getModel('shipping/rate_result_error');
            $error->setCarrier('tablerate');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }

        return $result;
    }
}