<?php

/*
* @category    Entrepids
* @package     Entrepids_Credit
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$installer = $this;

$attribute  = array(
    'type' => 'int',
    'input' => 'boolean',
    'label' => 'Credit application in process',
    'global' => 1,
    'visible' => 1,
    'default' => '0',
    'required' => 0,
    'user_defined' => 0,
    'sort_order' => 3,
    'used_in_forms' => array(
        'adminhtml_customer',
    ),
    'comment' => 'Flag to check if user have a credit application in process',
); 

$installer->addAttribute('customer', 'credit_application_in_process', $attribute);

 Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'credit_application_in_process')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();

$installer->endSetup();

