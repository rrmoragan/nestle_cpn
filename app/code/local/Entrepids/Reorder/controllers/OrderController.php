<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Sales orders controller
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once(Mage::getModuleDir('controllers','Mage_Sales').DS.'OrderController.php');
class Entrepids_Reorder_OrderController extends Mage_Sales_OrderController 
{

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * Customer order history
     */
    public function historyAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Orders'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    /**
     * Check osCommerce order view availability
     *
     * @deprecate after 1.6.0.0
     * @param   array $order
     * @return  bool
     */
    protected function _canViewOscommerceOrder($order)
    {
        return false;
    }

    /**
     * osCommerce Order view page
     *
     * @deprecate after 1.6.0.0
     *
     */
    public function viewOldAction()
    {
        $this->_forward('noRoute');
        return;
    }
    
    
    //Create this function to iterate trough orders so 
    //we can add all items orders to customer cart
  
    public function numberOfReordersAction()
    {   
       
        if($this->getRequest()->getParam("orderIds")){
            $ids= $this->getRequest()->getParam("orderIds");
        }
        if($this->getRequest()->getParam("order_id")){
            $ids =[];
            $ids[] = $this->getRequest()->getParam("order_id");
            
        }
        
        foreach($ids as $id){
            $cart = Mage::getSingleton('checkout/cart');
            $cartTruncated = false;
            /* @var $cart Mage_Checkout_Model_Cart */
            $order = Mage::getModel('sales/order')->load($id);
            
            if (!$this->_canViewOrder($order)) {
                $this->_redirect('*/*/history');
            }
            
            $cartHelper = Mage::helper('checkout/cart');
            $productsQty = [];
            $itemsCart = $cartHelper->getCart()->getItems();        
            foreach ($itemsCart as $_item) 
            {
               $productsQty[$_item->getproductId()] = $_item->getQty();
               $_item->getProductId();
            } 
            
            $itemsOrder = $order->getItemsCollection();
            foreach ($itemsOrder as $_item) {
                 
                $productId = (int)$_item->getProduct()->getId();
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
               
                $qtyOrdered = (int)$_item->getQtyOrdered();
                $alreadyInCart = 0;
                $inStock = $stock->getQty();
                if($productsQty[$productId]){
                    $alreadyInCart = $productsQty[$productId];    
                }          
                if($inStock > $alreadyInCart){
                    if( $inStock < $_item->getQtyOrdered() + $alreadyInCart ){
                        $_item->setQtyOrdered($inStock);
                        $cart->getCheckoutSession()->addError("No tenemos la cantidad solicitada en stock del producto '{$_item->getProduct()->getName()}' , agregamos ".(int)$inStock." de ".$qtyOrdered." de la   'Orden {$order->getRealOrderId()} '<br>");
                    }
                }else{
                    $cart->getCheckoutSession()->addError("No tenemos la cantidad solicitada en stock del producto '{$_item->getProduct()->getName()}' , agregamos ".(int)$inStock." de ".$qtyOrdered." de la   'Orden {$order->getRealOrderId()} '<br>");
                    continue;
                }
                    
                try {
                    $cart->addOrderItem($_item);
                } catch (Mage_Core_Exception $e){
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                       
                    }
                    else {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    }
                    $this->_redirect('*/*/history');
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addException($e,
                        Mage::helper('checkout')->__('Cannot add the item to shopping cart.')
                    );
                    $this->_redirect('checkout/cart');
                }
                    
            }
            
        }
        $cart->save();
        $this->_redirect('checkout/cart');
        
    }
    
    
}
