<?php

/*
* @category    Entrepids
* @package     Entrepids_RelatedProducts
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

    $installer = $this;
    $installer->startSetup();
    $table  = $installer->getConnection()
            ->newTable($installer->getTable('relatedproducts/discarded'))
            ->addColumn('id_discarded', Varien_Db_Ddl_Table::TYPE_INTEGER, null, 
                array('identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
            ), 'Discarded ID')
            ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'unsigned' => true,
                'nullable' => false,
            ), 'Customer Id')
            ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'unsigned' => true,
                'nullable' => false,
            ), 'Product Id')
            ->setComment('Discarded related products');

    $installer->getConnection()->createTable($table);
    $installer->endSetup();


