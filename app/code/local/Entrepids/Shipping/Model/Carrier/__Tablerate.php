<?php

class Entrepids_Shipping_Model_Carrier_Tablerate extends Mage_Shipping_Model_Carrier_Tablerate
{
    public $_prods = null; /* listado de los productos que se quieren adquirir */

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
        $rate = $this->getRate($request);   // consulta sql ==> regresa precio de envio por numero de productos

        //Mage::log("numero de producto ==> oldQty ==> ".print_r($oldQty,true), Zend_Log::INFO, 'rmorales.log');
        //Mage::log("array precio de envio por numero de productos ==> rate ==> ".print_r($rate,true), Zend_Log::INFO, 'rmorales.log');

        $request->setPackageWeight($oldWeight); // numero de productos por peso volumetrico
        $request->setPackageQty($oldQty);   // numero de productos pedidos

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

            /* calculando seguro y comision
                a la comision se le agrega la transaccion
            */

            //$this->_listado_productos($request);

            $precio_seguro   = $this->seguro_precio();
            $precio_comision = $this->comision_precio($oldQty);

            $s = "\nshippingPrice ==> $shippingPrice";
            $s = $s."\n precio_seguro ==> $precio_seguro";
            $s = $s."\n precio_comision ==> $precio_comision";

            $shippingPrice = $shippingPrice + $precio_seguro + $precio_comision;

            $s = $s."\n shippingPrice final ==> $shippingPrice";
            Mage::log($s, Zend_Log::INFO, 'rmorales.log');

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

    /*
        se toma el precio lista de todos los productos y se multiplica por el porcentaje del seguro
        si se encuentra un producto agrupado se salta el precio lista de este
        pero se toma en cuenta el precio lista de cada uno de los productos internos del producto agrupado
    */
    public function seguro_precio(){

        $seguro = Mage::getModel('core/variable')->loadByCode('articulo_seguro')->getValue('plain');
        $seguro = $seguro/100;
        $seguro_min = Mage::getModel('core/variable')->loadByCode('seguro_aplicable')->getValue('plain');

        //$order = Mage::app()->getLayout()->createBlock('checkout/cart_totals')-> renderTotals();
        //$subTotal   = Mage::getModel('checkout/cart')->getQuote()->getSubtotal();
        //$grandTotal = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();

        $subtotal   = Mage::getModel('checkout/session')->getQuote()->getSubtotal();
        $grand_total = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();

        $seguro_total = 0;

        if( $subtotal > $seguro_min ){
            $seguro_total = $subtotal * $seguro;
            $seguro_total = round( $seguro_total,2 );
        }

        $s="\n\n comision seguro\n";
        $s=$s."\n seguro ==> ".$seguro;
        $s=$s."\n seguro aplicable a partir de ==> ".$seguro_min;
        $s=$s."\n subtotal ==> ".$subtotal;
        $s=$s."\n subtotal x seguro ==> ".$seguro_total."\n";

        Mage::log( $s, Zend_Log::INFO, 'rmorales.log');
        
        return $seguro_total;

        /*
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                $pp = null;
                $p = 0;

                if( !isset( $this->_prods[ $item->getProductId() ] ) ){
                    Mage::log( "error al calcular el seguro de los productos", Zend_Log::INFO, 'rmorales.log');
                    return 0;
                }

                $pp = $this->_prods[ $item->getProductId() ]->_data;

                if( $pp['configurable_precio']>0 ){
                    $cc = $item->getQty();
                    $_ag = $item->getQty();
                    continue;
                }

                $qty = $item->getQty();
                if( $_ag ){ $qty = $qty * $cc; }

                $p = ( $pp['price_list'] * $qty ) * $seguro;

                $s=$s."\n producto => ".$item->getProductId();
                $s=$s."\t precio lista => ".$pp['price_list'];
                $s=$s."\t cantidad => ".$qty;
                $s=$s."\t total => ".$p;

                $precio += $p;
                if( $_ag ){ $_ag--; }
            }

            Mage::log( "\n\ncomision por seguro\n$s\ntotal $precio\n", Zend_Log::INFO, 'rmorales.log');

            return $precio;
        }
        */

        //Mage::log( 'sin productos', Zend_Log::INFO, 'rmorales.log');
        //return 0;
    }

    /*
        el total de productos por la comision
        no se cuenta el configurable ya que se suman los internos
    */
    public function comision_precio($nprods=0){
        if( $nprods==0 ){ return 0; }

        $comision = Mage::getModel('core/variable')->loadByCode('articulo_comision')->getValue('plain');
        $comision = round( $comision/100, 4 );
        $transaccion = Mage::getModel('core/variable')->loadByCode('comision_transaccion')->getValue('plain');

        $tot = ($comision * $nprods) + $transaccion;

        $s = "\n\n comision sistema de pago online\n";
        $s = $s."\n comision = $comision";
        $s = $s."\n nprods = $nprods";
        $s = $s."\n transaccion = $transaccion";
        $s = $s."\n comision X nprods = ".($comision * $nprods);
        $s = $s."\n comision X nprods + transaccion = $tot\n";

        Mage::log( $s, Zend_Log::INFO, 'rmorales.log');

        return $tot;
    }
}