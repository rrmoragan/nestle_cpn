<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_ScheduleAppointment_BookingController extends Mage_Core_Controller_Front_Action{
    private $_log = 'schedule_appointment.log';

    private function jsonResponse($status,$message){
        $response = array('done'=>$status, 'message'=>$message);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    private function sendNotification($to,$data){

        try{
            $prod =  Mage::getModel('catalog/product')->load($data['product']); 
            $data['product_sku'] = $prod->getSku();
            $data['product_name'] = $prod->getName();
            
            $modelEmail = Mage::getModel('scheduleappointment/email');
            
            //Send notification mail with the files attached
            $modelEmail->sendEmail(
                    'entrepids_appointment_template', 
                    array(
                        'name' => Mage::getStoreConfig('trans_email/ident_general/email'), 
                        'email' => Mage::getStoreConfig('trans_email/ident_general/name')
                    ), 
                    $to, 
                    Mage::getStoreConfig('trans_email/ident_general/email'),
                    'Me interesa su producto '.$data['product_name'],
                    $data
            );
            
            $modelEmail->sendEmail(
                    'entrepids_appointment_customer_template', 
                    array(
                        'name' => Mage::getStoreConfig('trans_email/ident_general/email'), 
                        'email' => Mage::getStoreConfig('trans_email/ident_general/name')
                    ), 
                    $data['customer_email'], 
                    $data['fristname'].' '.$data['lastname'],
                    'Gracias por el interés en nuestros producto', 
                    $data
            );
            
            
        }catch(Exception $e){
            Mage::log($e, null, $this->_log);
        }
    }

    public function indexAction(){
        $data = $this->getRequest()->getParams();

        if(isset($data['firstname']) && isset($data['lastname']) && isset($data['telephone']) && isset($data['email']) && isset($data['postal_code']) && isset($data['product'])){
            try{
                $data['customer_email'] = $data['email'];
                $verify = Mage::helper('scheduleappointment')->verifyPostalCode($data['postal_code']);

                $schedule = Mage::getModel('scheduleappointment/scheduleappointment');
                $schedule->setFirstname($data['firstname']);
                $schedule->setLastname($data['lastname']);
                $schedule->setTelephone($data['telephone']);
                $schedule->setEmail($data['email']);
                $schedule->setPostalCode($data['postal_code']);
                $schedule->setProductId($data['product']);
                $schedule->setCreatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $schedule->setValidPc($verify['done']);
                $schedule->save(); 
                
                if($verify['done']){
                    $emails = Mage::helper('scheduleappointment')->getEmailsTo();
                    if(count($emails)){
                        foreach($emails as $to){
                            $this->sendNotification($to,$data);
                        }
                    }else{
                        Mage::log('Error send notifications: No se encontraron destinatarios a ser notificados.',null, $this->_log);
                    }
                    $this->jsonResponse(true,'¡Gracias por confiar en nuestras soluciones!<br/>Uno de nuestros expertos se pondrá en contacto contigo en menos de 24 horas para validar la instalación y asesorarte durante todo el proceso.');
                }else{
                    $this->jsonResponse($verify['done'],$verify['message']);
                }
            }catch(Exception $e ){
                $this->jsonResponse(false,'Ocurrio un error al guardar la informacion');
                Mage::log('Error to save '+$e->getMessage(),null, $this->_log);
            }
        }else{
            $this->jsonResponse(false,'La información esta incompleta');
        }
    }
}