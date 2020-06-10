<?php
/**
* @category   Entrepids
* @package    Entrepids_Customer
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

class Entrepids_Customer_Block_Widget_Dropdown extends Mage_Customer_Block_Widget_Abstract
{
    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     * @return string
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? $this->__($attribute->getStoreLabel()) : '';
    }

    /**
     * Check if gender attribute marked as required
     *
     * @return bool
     */
    public function isRequired($attributeCode)
    {
        return (bool)$this->_getAttribute($attributeCode)->getIsRequired();
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAllOptions($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        $data = array(array('value'=>'','label'=>'Selecciona una opción'));

        if($attribute){
            $options = $attribute->getSource()->getAllOptions();
            foreach($options as $option){
                if(!empty($option['label'])){
                    $data[] = array('value'=>$option['value'],'label'=>$option['label']);
                }
            }
        }

        return $data;
    }
}
