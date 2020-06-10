<?php

/**
 * @category   Entrepids
 * @package    Entrepids_Maintenance
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Block_Solutions extends Mage_Core_Block_Template {

    private $_helper = null;
    
    public function getHelper() {
        if(!$this->_helper){
            $this->_helper = Mage::helper('coffeesolutions');
        }
        return $this->_helper;
    }

    public function getPlaces() {
        $places = array();
        $categoryId = $this->getHelper()->getCoffeeSolutionRootCategorey();
        if (empty($categoryId)) {
            return $places;
        }
        $rootCategory = Mage::getModel('catalog/category')->load($categoryId);
        $childsIds = $rootCategory->getChildren();
        $subcategoriesIds = explode(',', $childsIds);

        $subcategories = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect(array('entity_id', 'name', 'cs_solution_image', 'cs_solution_name','url_key'))
                ->addFieldToFilter('entity_id', array('in' => $subcategoriesIds))
                ->load();

        if (count($subcategories)) {
            foreach ($subcategories as $p) {
                if ($p->getCsSolutionName() && $p->getCsSolutionImage()) {
                    $imageResized = Mage::helper('coffeesolutions')->resizeImage($p->getCsSolutionImage(),'catalog/category',150,150);
                    $place = array('id' => $p->getId(), 'name' => $p->getCsSolutionName(), 'image' => $imageResized,'code' => $p->getUrlKey());
                    $places[] = $place;
                }
            }
        }
        return $places;
    }

}
