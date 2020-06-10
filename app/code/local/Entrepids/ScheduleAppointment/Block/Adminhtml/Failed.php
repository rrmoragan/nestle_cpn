<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

class Entrepids_ScheduleAppointment_Block_Adminhtml_Failed extends Mage_Adminhtml_Block_Widget_Grid_Container{
    public function __construct()
    {
        $this->_blockGroup = 'scheduleappointment';
        $this->_controller = 'adminhtml_failed';
        $this->_headerText = $this->__('Citas No Agendadas');
 
        parent::__construct();
        $this->_removeButton('add');
    }
}