<?php

/**
 * @category   Entrepids
 * @package    Entrepids_ScheduleAppointment
 * @author     fabian.perez@entrepids.com
 * @website    http://www.entrepids.com
 */

class Entrepids_ScheduleAppointment_Helper_Data extends Mage_Core_Helper_Abstract {

    # Get main email's
    const EMAILS = "adminreseivers/receivers/category";

    public static function getEmailsTo($store = null)
    {   
        $data = explode(',',Mage::getStoreConfig(self::EMAILS, $store));
        $emails = array();

        foreach($data as $email){
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }
        return $emails;
    }

    public function verifyPostalCode($cp){
        $responde = array();
        $error = '';

        if(strlen($cp) != 5) $error = 'La longitud de dato no es la esperada';

        if(!$error){
            $res = Mage::getModel('scheduleappointment/cpscheduleappointment')->load($cp);
            if(count($res->getData())){
                $responde = array('done'=>1,'message'=>'');
            }else{
                $responde = array('done'=>0,'message'=>"No contamos con asistencia para el codigo postal $cp, por favor proporcione otro.");
            }
        }else{
            $responde = array('done'=>0,'message'=>$error);
        }

        return $responde;

    }

    public function getProductNameById($id){
        return Mage::getModel('catalog/product')->load($id)->getName(); 
    }
}
