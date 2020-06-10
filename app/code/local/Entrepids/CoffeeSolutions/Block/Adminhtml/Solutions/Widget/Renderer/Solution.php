<?php

class Entrepids_CoffeeSolutions_Block_Adminhtml_Solutions_Widget_Renderer_Solution extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $value = $row->getData($this->getColumn()->getIndex());
        if($value){
            return 'Si';
        }else{
            return 'No';
        }
    }

}
?>