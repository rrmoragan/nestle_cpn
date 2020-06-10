<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


class Entrepids_Nestle_ProspectsController extends Mage_Core_Controller_Front_Action{
    private function jsonResponse($status,$message){
        $response = array('done'=>$status, 'message'=>$message);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    public function signinAction(){
        try{
            $data = $this->getRequest()->getPost();
            $prospect = Mage::getModel('nestle/prospectcustomers');
            $customer = $prospect->load($data['email'],'email');

            if($customer->getData()){
                if($customer->getCustomerId()){
                    Mage::getSingleton('core/session')->addNotice("La cuenta de correo electronico ".$data['email']." ya esta registrada. Por favor inicia sesion");
                    $this->_redirect('customer/account/login/');
                    return;
                }else{
                    $customer->setVisits($customer->getVisits()+1);
                    $customer->setLastVisit(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                    $customer->save();
                    $id = $customer->getId();
                }
            }else{
                $prospect->setName($data['firstname']);
                $prospect->setLastname($data['lastname']);
                $prospect->setEmail($data['email']);
                $prospect->save();
                $id = $prospect->getId();
            }

            $this->jsonResponse(true,'Gracias por tu registro.');
        }catch(Exception $e){
            Mage::log($e, null, 'clientes_prospecto.log');
            $this->jsonResponse(false,'Ocurrio un error al guardar la información. Intente más tarde.');
        }
    }
}