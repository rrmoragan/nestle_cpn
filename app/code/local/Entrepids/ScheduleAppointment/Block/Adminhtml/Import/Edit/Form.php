<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

class Entrepids_ScheduleAppointment_Block_Adminhtml_Import_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/import/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('scheduleappointment')->__('Importar Codigos Postales')));

        $fieldset->addField('import_postal_codes', 'file', array(
            'name'     => 'import_postal_codes',
            'label'    => Mage::helper('importexport')->__('Selecciona el archivo a importar'),
            'title'    => Mage::helper('importexport')->__('Selecciona el archivo a importar'),
            'required' => true
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
