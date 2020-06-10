<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Block_Adminhtml_Solutions_Edit_Tab_Solucion extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::getModel('coffeesolutions/solutions');
        if(Mage::registry('coffee_solution')){
            $model = Mage::registry('coffee_solution');
        }
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $fieldset = $form->addFieldset('solution_information', array('legend' => Mage::helper('coffeesolutions')->__('Detalles de la Solución')));
        
        if($model->getId()){
            $fieldset->addField('solution_id', 'hidden', array(
                'required' => true,
                'name' => 'solution_id',
                'value' => $model->getId()
            ));
        }
        
        $fieldset->addField('solution_name', 'text', array(
            'label' => Mage::helper('coffeesolutions')->__('Nombre de la solución'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'solution_name',
            'value' => $model->getSolutionName()
        ));
        
        $currentImage = '';
        if(!empty($model->getSolutionImage())){
            $currentImageUrl = Mage::helper('coffeesolutions')->resizeImage($model->getSolutionImage(),'solutions',150,150);
            $currentImage = '<img src="'.$currentImageUrl.'" alt="imagen-solucion-cafe">';
        }
        
        $requieredImage = TRUE;
        if($model->getId()){
            $requieredImage = FALSE;
        }
        
        $fieldset->addField('solution_image', 'file', array(
            'name' => 'solution_image',
            'label' => Mage::helper('coffeesolutions')->__('Imágen de la solución'),
            'title' => Mage::helper('coffeesolutions')->__('Imágen de la solución'),
            'required' => $requieredImage,
            'after_element_html' => $currentImage,
        ));
        
        $fieldset->addField('calification', 'text', array(
            'label' => Mage::helper('coffeesolutions')->__('Calificación'),
            'class' => 'validate-digits',
            'required' => false,
            'name' => 'calification',
            'value' => $model->getCalification()
        ));
        
        
        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('coffeesolutions')->__('Descripción'),
            'class' => 'validate-length maximum-length-130',
            'required' => true,
            'name' => 'description',
            'value' => $model->getDescription(),
            'after_element_html' => '<small>Máximo 130 carácteres.</small>'
        ));
        
         
        $fieldset->addField('costbycoup', 'text', array(
            'label' => Mage::helper('coffeesolutions')->__('Costo por taza'),
            'class' => '',
            'required' => false,
            'name' => 'costbycoup',
            'value' => $model->getCostbycoup()
        ));
        
        
        $fieldset->addField('preparation', 'text', array(
            'label' => Mage::helper('coffeesolutions')->__('Preparación'),
            'class' => '',
            'required' => false,
            'name' => 'preparation',
            'value' => $model->getPreparation()
        ));
        
        $fieldset->addField('beveragetype', 'text', array(
            'label' => Mage::helper('coffeesolutions')->__('Tipo de Bebida'),
            'class' => '',
            'required' => false,
            'name' => 'beveragetype',
            'value' => $model->getBeveragetype()
        ));
        
        $fieldset->addField('varieties', 'text', array(
            'label' => Mage::helper('coffeesolutions')->__('Variedades'),
            'class' => '',
            'required' => false,
            'name' => 'varieties',
            'value' => $model->getVarieties()
        ));
        
        
        $fieldset = $form->addFieldset('solution_coups_alghoritm', array('legend' => Mage::helper('coffeesolutions')->__('Disponibilidad de la Solución')));
        
        $fieldset->addField('solution_active', 'select', array(
            'name' => 'solution_active',
            'label' => Mage::helper('coffeesolutions')->__('Solución disponible'),
            'title' => Mage::helper('coffeesolutions')->__('Solución disponible'),
            'values' => array( '0' => 'No', '1' => 'Si' ),
            'required' => true,
            'value' => $model->getAvailable()
        ));
        
        $fieldset->addField('min_coups', 'text', array(
            'name' => 'min_coups',
            'label' => Mage::helper('coffeesolutions')->__('Número mínimo de Tazas'),
            'title' => Mage::helper('coffeesolutions')->__('Número mínimo de Tazas'),
            'required' => true,
            'class' => 'validate-digits',
            'value' => $model->getMinCoupsAvailable()
        ));
        
        
        $fieldset->addField('max_coups', 'text', array(
            'name' => 'max_coups',
            'label' => Mage::helper('coffeesolutions')->__('Número máximo de Tazas'),
            'title' => Mage::helper('coffeesolutions')->__('Número máximo de Tazas'),
            'required' => false,
            'class' => 'validate-digits',
            'value' => $model->getMaxCoupsAvailable(),
            'after_element_html' => '<small>Dejar en blanco significa que la solución se propondrá siempre que sea mayor al número mínimo de tazas.</small>'
        ));
        
        
     
        return parent::_prepareForm();
    }

}
