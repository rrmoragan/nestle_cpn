<?php
/**
* @category   Entrepids
* @package    Entrepids_Product
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

include(Mage::getBaseDir().'/app/code/core/Mage/Checkout/controllers/CartController.php');

class Entrepids_Product_CartController extends Mage_Checkout_CartController {
    
    public function indexAction()
    {
       
        $cart = $this->_getCart();
        $helper = Mage::helper('entrepids_customer');
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
             if((!$cart->getCustomerSession()->isLoggedIn() || !$helper->getCustomerProfileAAA()) && $cart->getQuote()->getShippingAddress() && empty( $cart->getQuote()->getShippingAddress()->getCountryId())  ){
                       $this->_getQuote()->getShippingAddress()
                    ->setCountryId("MX")
                    ->setPostcode("11850")
                    ->setRegionId("485")
                    ->setCollectShippingRates(true);
                
                $this->_getSession()->setEstimatedShippingAddressData(array(
                    'country_id' => "MX",
                    'postcode'   => "11850",
                    'region_id'  => "485"
                ));
                $this->_getQuote()->getShippingAddress()->setShippingMethod("tablerate_bestway")->save();
            }
            
            if (
                $cart->getQuote()->getShippingAddress()
                && $this->_getSession()->getEstimatedShippingAddressData()
                && $couponCode = $this->_getSession()->getCartCouponCode()
            ) {
                $estimatedSessionAddressData = $this->_getSession()->getEstimatedShippingAddressData();
                $cart->getQuote()->getShippingAddress()
                    ->setCountryId($estimatedSessionAddressData['country_id'])
                    ->setCity($estimatedSessionAddressData['city'])
                    ->setPostcode($estimatedSessionAddressData['postcode'])
                    ->setRegionId($estimatedSessionAddressData['region_id'])
                    ->setRegion($estimatedSessionAddressData['region']);
                $cart->getQuote()->setCouponCode($couponCode);
            }
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }
    
    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            if($product->getIsProfessional()){
                $this->_getSession()->addError($this->__('Para adquirir un producto "Professional", es necesario agendar una cita.')); 
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                //Evit redirect to cart, only show livebox
                $position = isset($params['position']) ? '&position='.$params['position'] : '';
                $add_to_param = 'add_to_cart=true'.$position;
                $url = Mage::helper('core/http')->getHttpReferer();
                if(strpos($url,'/?')){
                    $add_to_param = '&'.$add_to_param; 
                }else{
                    $add_to_param = '?'.$add_to_param; 
                }
                $url = $url.$add_to_param;
                Mage::app()->getResponse()->setRedirect($url);
                //$this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
    
    
    
    
}