<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

class Entrepids_ScheduleAppointment_Block_Adminhtml_Postalcode extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct()
    {
        $this->_blockGroup = 'scheduleappointment';
        $this->_controller = 'adminhtml_postalcode';
        $this->_headerText = $this->__('Codigos Postales Validos');
        
        parent::__construct();
        $this->_removeButton('add');

        $this->_addButton('import', array(
            'label'   => $this->__('Import'),
            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
            'class'   => 'import'
        ));
    }
}