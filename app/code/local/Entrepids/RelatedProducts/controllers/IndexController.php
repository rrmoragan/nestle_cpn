<?php

/*
* @category    Entrepids
* @package     Entrepids_RelatedProducts
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_RelatedProducts_IndexController extends Mage_Core_Controller_Front_Action 
{

	public function indexAction()
	{
		$this->loadLayout();
    	$this->renderLayout();
	}


    public function ajaxAction()
    {
        $productId  = $this->getRequest()->getParam("productId");
        $customerId = $this->getRequest()->getParam("customerId");

        if($productId!=null && $productId!="")
        {
            try
            {
                $model = Mage::getModel('relatedproducts/discarded');
                $model
                    ->setCustomerId($customerId)
                    ->setProductId($productId)
                    ->save();
                echo Zend_Json::encode(array("success" => "true", "message" => "El producto con id ".$productId." no volverá a aparecer"));
            }

            catch(Exception $e)
            {
                echo Zend_Json::encode(array("success" => "false", "message" => "Ocurrió un problema"));
            }
            
        }

        else
        {
            echo Zend_Json::encode(array("success" => "false", "message" => "Ocurrió un problema al recibir el id del producto"));
        }
        
    }


    public function massiveAdd2CartAction()
    {
        $idsString          = $this->getRequest()->getParam('productIds');
        $discardedIdsString = $this->getRequest()->getParam('discardedIds');

        $ids            = explode(',', $idsString);
        $discardedIds   = explode(',', $discardedIdsString);
        
        $products = array();

        foreach ($ids as $id) 
        {
            if($id!=null && $id!="")
            {
                if(in_array($id, $discardedIds))
                {
                    continue;
                }

                else
                {
                    $_product = Mage::getModel('catalog/product')->load($id);
                    array_push($products, $_product);
                }
                
            }
            
        }

        $cart = Mage::getSingleton('checkout/cart');
        $cart->init();

        /*if(count($products)==0)
        {
            Mage::getSingleton('customer/session')->addError('No se pudieron agregar las sugerencias. El carrito está vacío'); 
            echo Zend_Json::encode(array("success" => "true", "message" => "Fail"));
            return;
        }*/

        foreach ($products as $product) 
        {
            $paramater = array('product' => $product->getId(),
                    'qty' => '1',
                    'form_key' => Mage::getSingleton('core/session')->getFormKey()
            );       

            $request = new Varien_Object();
            $request->setData($paramater);

            try
            {
                $cart->addProduct($product, $request);
            }

            catch(Exception $e)
            {
                Mage::getSingleton('customer/session')->addError('No se pudieron agregar algunas sugerencias al carrito de compras.'); 
                echo Zend_Json::encode(array("success" => "true", "message" => "Success"));
            }
            
        }

        try
        {
            $cart->save();
            Mage::getSingleton('customer/session')->addSuccess('Se agregaron las sugerencias al carrito de compras.'); 
            echo Zend_Json::encode(array("success" => "true", "message" => "Success"));
        }

        catch(Exception $e)
        {
            Mage::getSingleton('customer/session')->addError('No se pudo generar el carrito de compras.'); 
            echo Zend_Json::encode(array("success" => "true", "message" => "Fail"));
        }      
    }
}
