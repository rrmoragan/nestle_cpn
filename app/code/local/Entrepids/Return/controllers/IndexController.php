<?php

/*
 * @category    Entrepids
 * @package     Entrepids_Return
 * @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
 * @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class Entrepids_Return_IndexController extends Mage_Core_Controller_Front_Action {

    public function uploadAction() {

        if ($this->getRequest()->getPost() == null) {
            $this->_redirect("/");
        } else {
            try {
                $data = $this->getRequest()->getPost();
                $folder = Mage::getBaseDir('media') . '/nestle-return-files/';

                $vars = array(
                            "customer_name" => $data['customer_name'], 
                            "customer_email" => $data['customer_email'], 
                            "order_id" => $data['order_id_input'], 
                            "cause" => $data['cause'],
                            "comments" => $data['comments']
                            );

                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                }

                $name = $data['img1_input'];
                $path_img1 = $folder . $name;
                move_uploaded_file($_FILES["img1"]["tmp_name"], $path_img1);

                $name = $data['img2_input'];
                $path_img2 = $folder . $name;
                move_uploaded_file($_FILES["img2"]["tmp_name"], $path_img2);

                $name = $data['img3_input'];
                $path_img3 = $folder . $name;
                move_uploaded_file($_FILES["img3"]["tmp_name"], $path_img3);

                $attachments = array($path_img1, $path_img2, $path_img3);

                //Send notification mail with the files attached
                Mage::getModel('return/email')->sendEmail(
                        'entrepids_return_template', 
                        array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 
                            'email' => Mage::getStoreConfig('trans_email/ident_general/email')), 
                        Mage::getStoreConfig('trans_email/ident_general/email'), 
                        Mage::getStoreConfig('trans_email/ident_general/name'), 
                        Mage::helper('customer')->__('New Order Cancellation or return request'), 
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

    public function successAction() 
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function failAction() 
    {
        $this->loadLayout();
        $this->renderLayout();
    }

}
