<?php
/**
* @category   Entrepids
* @package    Entrepids_Completeaddress
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
$installer = $this; 
$installer->startSetup();

$installer->addAttribute('customer_address', 'rfc', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'RFC',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'visible_on_front' => 1,
    'validate_rules'    => array(
        'max_text_length'  => 15
    )
));

$installer->addAttribute('customer_address', 'email', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Email',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'visible_on_front' => 1,
    'validate_rules'    => array(
        'input_validation'  => 'email'
    )
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'rfc')
    ->setData('used_in_forms', array('adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address'))
    ->save();

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'email')
    ->setData('used_in_forms', array('adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address'))
    ->save();

$ca = $this->getTable('customer/address_entity');
$installer->run("
    ALTER TABLE  $ca ADD  `is_billing` smallint(5) DEFAULT 0 COMMENT 'Is Billing'
");

$installer->addAttribute(
    'customer_address',
    'is_billing',
    array(
        'label' => 'Is Billing',
        'type'  => 'static'
    )
);  

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'is_billing')
    ->setData('used_in_forms', array('adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address'))
    ->save();
    
$qa = $this->getTable('sales/quote_address');
$installer->run("
    ALTER TABLE  $qa ADD  `is_billing` smallint(5) DEFAULT 0 COMMENT 'Is Billing'
");
 
$oa = $this->getTable('sales/order_address');
$installer->run("
    ALTER TABLE  $oa ADD  `is_billing` smallint(5) DEFAULT 0 COMMENT 'Is Billing'
");

$installer->endSetup();