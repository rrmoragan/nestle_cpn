<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Model_System_Config_Source_AttributeSets {

    protected $_options = array();
    
    
    public function getLines() {
        // Get product lines
        return Mage::helper('coffeesolutions/productLines')->getProductLines();
    }

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options[] = array(
                'label' => Mage::helper('coffeesolutions')->__('-- Seleccione una línea --'),
                'value' => ''
            );

            $productLines = $this->getLines();

            foreach ($productLines as $value => $label) {
                $this->_options[] = array(
                    'label' => $label,
                    'value' => $value
                );
            }
        }
        return $this->_options;
    }
    
    public function setAttribute(){
        return $this;
    }

}
