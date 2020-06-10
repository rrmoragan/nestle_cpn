<?php

/*
 * @category    Entrepids
 * @package     Entrepids_Credit
 * @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
 * @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class Entrepids_Credit_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function uploadAction() {

        if ($this->getRequest()->getPost() == null) {
            $this->_redirect("/");
        } else {
            try {
                $data = $this->getRequest()->getPost();
                $folder = Mage::getBaseDir('media') . '/credit/';

                $vars = array("customer_name" => $data['customer_name'], "customer_email" => $data['customer_email'], "customer_id" => $data['customer_id']);

                //Change credit application status
                $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
                $customer->setCreditApplicationInProcess(1);
                $customer->save();


                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                }

                $name = $data['official_id_input'];
                $path_id = $folder . $name;
                move_uploaded_file($_FILES["official_id"]["tmp_name"], $path_id);

                $name = $data['address_proof_input'];
                $path_address = $folder . $name;
                move_uploaded_file($_FILES["address_proof"]["tmp_name"], $path_address);

                $name = $data['rfc_input'];
                $path_rfc = $folder . $name;
                move_uploaded_file($_FILES["rfc"]["tmp_name"], $path_rfc);

                $name = $data['signed_request_input'];
                $path_sign = $folder . $name;
                move_uploaded_file($_FILES["signed_request"]["tmp_name"], $path_sign);

                $attachments = array($path_id, $path_address, $path_rfc, $path_sign);

                //Send notification mail with the files attached
                Mage::getModel('credit/email')->sendEmail(
                        'entrepids_credit_template', 
                        array(
                            'name' => Mage::getStoreConfig('trans_email/ident_general/email'), 
                            'email' => Mage::getStoreConfig('trans_email/ident_general/name')
                        ), 
                        Mage::helper('credit')->getEmails(), 
                        Mage::helper('customer')->__('Nestlé Credit Manager(s)'),
                        Mage::helper('customer')->__('New Nestlé Credit Request'), 
                        $vars, 
                        $attachments
                );

                //Delete files (for privacy policy)
                foreach ($attachments as $attachment) {
                    unlink($attachment);
                }

                Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl());
                $this->_redirect('*/*/success');
            } catch (Exception $e) {
                $this->_redirect('*/*/fail');
            }
        }
    }

    public function successAction() {
        $referer = $_SERVER["HTTP_REFERER"];

        if ($referer != Mage::getBaseUrl() . "credit/") {
            $this->_redirect("/");
        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function failAction() {
        $referer = $_SERVER["HTTP_REFERER"];

        if ($referer != Mage::getBaseUrl() . "credit/") {
            $this->_redirect("/");
        } else {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

}
