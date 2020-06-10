<?php
class Entrepids_Newsletter_Block_Adminhtml_Form_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {   
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_form',array('legend'=>Mage::helper('entrepids_newsletter')->__('Información categoría')));
        $fieldset->addType('required_image', 'Entrepids_Newsletter_Block_Adminhtml_Helper_Image_Required');
        $idCatalog = $this->getRequest()->get('idCatalog');
        $valueName = '';
        $valueImage='';
        $required = true;
        $changeImage = '';
        if($idCatalog != null && $idCatalog > 0 ){
            $model = Mage::getModel('entrepids_newsletter/catalog')->load($idCatalog);
            $valueName=$model->getName();
            $valueImage=$model->getImage();
            $fieldset->addField('oimage', 'required_image', array(
                            'label'     => Mage::helper('core')->__('Imagen actual'),
                            'name'      => 'oimage',
                            'value' =>'newsletter'.$valueImage
                            
                ));
            
            $fieldset->addField('idCatalog', 'hidden', array(
                            'name'      => 'idCatalog',
                            'value'     => $idCatalog
                ));
            
        $required = false;
        $changeImage = 'Cambiar';
            
        }
        
        
        
        
        
        $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('entrepids_newsletter')->__('Nombre'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
          'value'     => $valueName
        ));
        
        $fieldset->addField('image', 'file', array(
                'name' => 'image',
                'label' => Mage::helper('entrepids_newsletter')->__($changeImage.' Imagen'),
                'title' => Mage::helper('entrepids_newsletter')->__($changeImage.' Imagen'),
                'required' => $required
            ));
     
        
       
        
        
          
        return parent::_prepareForm();
    }
}