<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Block_Adminhtml_Solutions_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('coffeesolutions')->__('Solución de Café'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section_detalles', array(
            'label' => Mage::helper('coffeesolutions')->__('Detalles de la solución'),
            'title' => Mage::helper('coffeesolutions')->__('Detalles de la solución'),
            'content' => $this->getLayout()->createBlock('coffeesolutions/adminhtml_solutions_edit_tab_solucion')->toHtml(),
        ));
        
        
        
        $this->addTab('form_section_maquinas', array(
            'label' => Mage::helper('coffeesolutions')->__('Productos'),
            'title' => Mage::helper('coffeesolutions')->__('Productos'),
            'url'   => $this->getUrl('*/*/productos', array('_current' => true)),
            'class' => 'ajax',
        ));
        
        
        return parent::_beforeToHtml();
    }

}
