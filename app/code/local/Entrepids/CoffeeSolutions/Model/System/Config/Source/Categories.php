<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Model_System_Config_Source_Categories {

    protected $_options = array();

    public function getCategoriesTreeView() {
        // Get category collection
        $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSort('path', 'asc')
                //->addFieldToFilter('is_active', array('eq'=>'1'))
                ->load()
                ->toArray();

        // Arrange categories in required array
        $categoryList = array();
        foreach ($categories as $catId => $category) {
            if (isset($category['name'])) {
                $categoryList[] = array(
                    'label' => $category['name'],
                    'level' => $category['level'],
                    'value' => $catId
                );
            }
        }
        return $categoryList;
    }

    public function toOptionArray() {
        if (!$this->_options) {
            $this->_options[] = array(
                'label' => Mage::helper('coffeesolutions')->__('-- Seleccione una categoría --'),
                'value' => ''
            );


            $categoriesTreeView = $this->getCategoriesTreeView();

            foreach ($categoriesTreeView as $value) {
                $catName = $value['label'];
                $catId = $value['value'];
                $catLevel = $value['level'];
                
                if($catLevel > 2){
                    continue;
                }

                $hyphen = " » ";
                for ($i = 1; $i < $catLevel; $i++) {
                    $hyphen .= $hyphen;
                }

                $catName = $hyphen . $catName;

                $this->_options[] = array(
                    'label' => $catName,
                    'value' => $catId
                );
            }
        }
        return $this->_options;
    }

}
