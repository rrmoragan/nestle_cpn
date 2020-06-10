<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Model_System_Config_Source_Attributesets {

    protected $_options = array();
    
    
    public function getAttributeSets() {
        $entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
        return Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($entityType->getId())
                ->load()
                ->toOptionArray();
    }
    
    public function toOptionArray(){
        if (!$this->_options) {
            $this->_options[] = array(
                'label' => Mage::helper('coffeesolutions')->__('-- Seleccione una línea --'),
                'value' => ''
            );

            $attributeSets = $this->getAttributeSets();

            /*foreach ($attributeSets as $value => $label) {
                $this->_options[] = array(
                    'label' => $label,
                    'value' => $value
                );
            }*/
            $this->_options = array_merge($this->_options, $attributeSets);
        }
        return $this->_options;
    }


    public function setAttribute(){
        return $this;
    }

}
