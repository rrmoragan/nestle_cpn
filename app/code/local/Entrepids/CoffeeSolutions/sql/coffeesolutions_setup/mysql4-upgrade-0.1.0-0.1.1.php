<?php
/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
$installer = $this;

$installer->startSetup();

$staticBlock = array(
                'title' => 'Soluciones de Café',
                'identifier' => 'coffee_solutions',
                'content' => '{{block type="coffeesolutions/solutions" name="coffeesolutions_solutions" template="entrepids/coffeesolutions/solutions.phtml"}}',
                'is_active' => 1,                    
                'stores' => array(0)
                );
                
Mage::getModel('cms/block')->setData($staticBlock)->save();


$installer->getConnection()->insertIgnore(
    $installer->getTable('admin/permission_block'),
    array('block_name' => 'coffeesolutions/solutions', 'is_allowed' => 1)
);

$installer->endSetup();