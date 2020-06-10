<?php

/**
 * @category   Entrepids
 * @package    Entrepids_Maintenance
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Block_Solutions_Details extends Mage_Catalog_Block_Product{ // Mage_Core_Block_Template {

    private $_helper = null;
    private $_products = null;
    private $_productsGrouped = null;
    private $_recommendedQty = array();
    private $_fullSolutionPrice = 0;
    
    const SYSTEM_XML_PATH_DAYS_CS = 'coffeesolution/solution/solutiondays';
    
    public function getHelper($var = null) {
        if(!$this->_helper){
            $this->_helper = Mage::helper('coffeesolutions');
        }
        return $this->_helper;
    }
    
    public function getBreadcrumb(){
        $breadcrumb = '';
        $place = (int)$this->getRequest()->getParam('lugar',null);
        $sublocation = (int)$this->getRequest()->getParam('ubicacion',null);
        $coups = (int)$this->getRequest()->getParam('tazas',null);
        $categoryLocation = Mage::getModel('catalog/category')->load($place);
        $categorySubocation = Mage::getModel('catalog/category')->load($sublocation);
        $breadcrumb = $categoryLocation->getCsSolutionName().' / '.$categorySubocation->getCsSolutionName().' / '.$coups.' tazas diarias';
        return $breadcrumb;
    }

    public function getProductsSolution(){
        if(!$this->_productsGrouped){
            $productsArray = array();
            $id = $this->getRequest()->getParam('id',null);
            $pModel = Mage::getModel('catalog/product');
            $solucion = Mage::getModel('coffeesolutions/solutions')->load($id);
            if($solucion->getId() && is_string($solucion->getProducts())){
                $productsIds = unserialize($solucion->getProducts());
                if(!empty($productsIds)){
                    $products = $pModel->getCollection()
                            ->addAttributeToSelect(array('name','description','sku','short_description','product_url','price','final_price','is_professional','image','image_url','nombre_secundario','numero_tazas_producto','numero_tazas_dia'))
                            //->addAttributeToSelect('description')
                            ->addAttributeToFilter('entity_id',array('in'=>$productsIds))
                            ->addAttributeToFilter('status',array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
                    $this->_products = $products;
                    $this->_productsGrouped = $this->sortProductsByAttributeSet($products);
                }else{
                    $this->_products = null;
                    $this->_productsGrouped = null;
                }
            }else{
                $this->_products = null;
                $this->_productsGrouped = null;
            }
        }
        return $this->_productsGrouped;
    }
    
    public function getAttributeSetNameById($id){
        $attributeSetName = '';
        if((int)$id){
            $model = Mage::getModel('eav/entity_attribute_set');
            $attributeSet = $model->load($id);
            $attributeSetName = $attributeSet->getAttributeSetName();
        }
        return $attributeSetName;
    }
    
    protected function sortProductsByAttributeSet($products){
        //$arrayPriority = array('Máquinas','Insumos','');
        $productsArray = array();
        if(!empty($products)){
            foreach ($products as $_p){
                if(!empty($_p->getAttributeSetId())){
                    $productsArray[$_p->getAttributeSetId()][] = $_p;
                }
            }
        }
        uasort($productsArray, function($a,$b){
            return (count($a) > count($b));
        });

        ksort($productsArray);
        
        return $productsArray;
    }
    
    public function getDaysToCalc(){
        return (int) Mage::getStoreConfig(self::SYSTEM_XML_PATH_DAYS_CS);
    }

    public function getRecommendedQty(){
        $coups = (int)$this->getRequest()->getParam('tazas',null);
        $daysToCalc = (int) Mage::getStoreConfig(self::SYSTEM_XML_PATH_DAYS_CS);
        if(empty($this->_recommendedQty)){
            if($this->getProductsSolution()){
                foreach ($this->_products as $_p){
                    $debug = print_r($_p->getData(),true);
                    //Mage::log('Producto: '.$debug,null,'recommend.log');
                    $coupsByProduct = (int) $_p->getData('numero_tazas_producto');
                    $coupsPerDay = (int) $_p->getData('numero_tazas_dia');
                    $recommended = ($coupsByProduct) ? round(($coups*$daysToCalc)/$coupsByProduct,0,PHP_ROUND_HALF_UP) : 1;
                    if($coupsPerDay > 0){
                        $recommended = round(($coups/$coupsPerDay),0,PHP_ROUND_HALF_UP);
                    }
                    $this->_recommendedQty[$_p->getId()] = $recommended;
                    $this->_fullSolutionPrice += $_p->getFinalPrice() * $recommended;
                }
            }
        }
        return $this->_recommendedQty;
    }
    
    public function getFullSolutionPrice(){
        if(!$this->_fullSolutionPrice){
            $this->getRecommendedQty();
        }
        $this->_fullSolutionPrice = ($this->_fullSolutionPrice) ? $this->_fullSolutionPrice : 0;
        return $this->_fullSolutionPrice;
    }
    
    public function getFullSolutionPriceHtml(){
        $price = '0.<sup>00</sup>';
        if($this->getFullSolutionPrice()){
            $priceFormat = number_format((float)$this->_fullSolutionPrice, 2, '.', ',');
            $price = explode('.',$priceFormat);
            $price = $price[0].'.<sup>'.$price[1].'</sup>';
            
        }
        return $price;
    }
    
    /*public function getTotalQty(){
        
    }*/
    
    public function getAdd2CartUrl(){
        return Mage::getUrl('coffeesolutions/solutions/add2cart');
    }
    
    
}
