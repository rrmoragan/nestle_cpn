<?php
/**
* @category   Entrepids
* @package    Entrepids_Product
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Product_Model_Observer {

    private function generalValidation(Mage_Catalog_Model_Product $product){
        $minPrice = Mage::helper('entrepids_product/config')->getMinPrice();
        $error = array();

        if($product->getPrice() === "0" || $product->getPrice()<0){
            $error[] = "-Debe tener un precio mayor a $0";
        }
        if($minPrice && $product->getPrice() <= $minPrice ){
            $error[] = "-Debe tener un precio mayor a \$$minPrice";
        }
        if($product->getImage() == 'no_selection' || $product->getSmallImage() == 'no_selection' || $product->getThumbnail() == 'no_selection'){
            $error[] = "-Debe contener imágen (Base Image, Small Image y Thumbnail)";
        }
        if($product->getSpecialPrice() === "0" || $product->getSpecialPrice()<0){
            $error[] = "-El precio especial debe ser mayor a $0";
        }

        return $error;
    }

    public function addProductValidate($observer){
        $product = $observer->getEvent()->getProduct();
        $invalid = false;

        if($product->getImportCSVFlag()){
            $invalid = true;
        }else{
            $error = $this->generalValidation($product);

            if(count($error)){
                Mage::getSingleton('core/session')->addNotice('El producto no estara disponible por que:');
                foreach($error as $er){
                    Mage::getSingleton('core/session')->addNotice($er);
                }
                $invalid = true;
            }
        }

        if($invalid) $product->setStatus(2);
        
        return $this;
    }

    public function addProductValidateFromImport($observer){
        $adapter = $observer->getEvent()->getAdapter();
        $entityIds = $adapter->getAffectedEntityIds();
        $invalidProducts = array();
        foreach($entityIds as $id){
            $product = Mage::getModel('catalog/product')->load($id);
            $error = $this->generalValidation($product);
        
            if(count($error)){
                $product->setImportCSVFlag(true);
                $invalidProducts[] = $product->getName();
                $product->save();
            }
            $product->clearInstance();
        }

        if($total = count($invalidProducts)){
            $minPrice = Mage::helper('entrepids_product/config')->getMinPrice();
            if($total == 1){
                Mage::getSingleton('core/session')->addNotice($invalidProducts[0]." no estara habilitado debido a las reglas de publicación:");
            }else{
                Mage::getSingleton('core/session')->addNotice("$total productos no estaran habilitados debido a las reglas de publicación:");
            }
            
            Mage::getSingleton('core/session')->addNotice("-Debe tener un precio mayor a $0");
            Mage::getSingleton('core/session')->addNotice("-Debe tener un precio mayor a \$$minPrice");
            Mage::getSingleton('core/session')->addNotice("-Debe contener imágen (Base Image, Small Image y Thumbnail)");
        }

        return $this;
    }
}