<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_ScheduleAppointment_Adminhtml_PostalcodeController extends Mage_Adminhtml_Controller_Action{
    public function indexAction()
   {
       $this->_title($this->__('Customer'))->_title($this->__('Codigos postales validos'));
       $this->loadLayout();
       $this->_setActiveMenu('customer/postalcode');
       $this->_addContent($this->getLayout()->createBlock('scheduleappointment/adminhtml_postalcode'));
       $this->renderLayout();
   }

    public function newAction(){
        $this->_title($this->__('Customer'))->_title($this->__('Cargar codigos postales'));
        $this->loadLayout();
        $this->_setActiveMenu('customer/postalcode');
        $this->_addContent($this->getLayout()->createBlock('scheduleappointment/adminhtml_import'));
        $this->renderLayout();
    }

    public function deleteAction(){
        $requestIds = $this->getRequest()->getParam('deleteAction');
        
        if(!is_array($requestIds)){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select reqeust(s)'));
        }else{
            try{
                foreach($requestIds as $requestId){
                    $requestData = Mage::getModel('scheduleappointment/cpscheduleappointment')->load($requestId);
                    $requestData->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully delete', count($requestIds)
                    )
                );
            }catch(Exception $e){
                Mage::getSingleton('adminhtml')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}