<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

class Entrepids_ScheduleAppointment_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Form_Container{
    public function __construct()
    {
        $this->_blockGroup = 'scheduleappointment';
        $this->_controller = 'adminhtml_import';
        $this->_headerText = $this->__('Import');
 
        parent::__construct();
    }
}