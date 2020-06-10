<?php
/*
* @category    Entrepids
* @package     Entrepids_StockAlert
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_StockAlert_Model_Email extends Mage_Core_Model_Email_Template
{

    /**
     * Send email to recipient
     *
     * @param string $templateId template identifier (see config.xml to know it)
     * @param array $sender sender name with email ex. array('name' => 'John D.', 'email' => 'email@ex.com')
     * @param string $email recipient email address
     * @param string $name recipient name
     * @param string $subject email subject
     * @param array $params data array that will be passed into template
     */
    public function sendEmail($templateId, $sender, $email, $name, $subject, $params = array())
    {
        $this->setDesignConfig(array('area' => 'frontend', 'store' => $this->getDesignConfig()->getStore()))
            ->setTemplateSubject($subject)
            ->sendTransactional(
                $templateId,
                $sender,
                $email,
                $name,
                $params
        );
    }
}