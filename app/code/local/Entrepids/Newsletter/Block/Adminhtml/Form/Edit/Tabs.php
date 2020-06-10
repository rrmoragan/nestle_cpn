<?php

class Entrepids_Newsletter_Block_Adminhtml_Form_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
  public function __construct()
  {
      parent::__construct();
      $this->setId('form_tabs');
      $this->setDestElementId('edit_form'); // this should be same as the form id define above
      $this->setTitle(Mage::helper('entrepids_newsletter')->__('Categoría'));
  }
 
  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('entrepids_newsletter')->__('Información'),
          'title'     => Mage::helper('entrepids_newsletter')->__('Información'),
          'content'   => $this->getLayout()->createBlock('entrepids_newsletter/adminhtml_form_edit_tab_form')->toHtml(),
      ));
      
      return parent::_beforeToHtml();
}

  }

