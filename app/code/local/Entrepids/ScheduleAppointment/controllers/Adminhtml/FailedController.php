<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_ScheduleAppointment_Adminhtml_FailedController extends Mage_Adminhtml_Controller_Action{
    public function indexAction()
   {
       $this->_title($this->__('Customer'))->_title($this->__('Citas no Agendadas'));
       $this->loadLayout();
       $this->_setActiveMenu('customer/schedule');
       $this->_addContent($this->getLayout()->createBlock('scheduleappointment/adminhtml_failed'));
       $this->renderLayout();
   }
}