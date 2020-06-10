<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */


$installer = $this;

$installer->startSetup();
$installer->getConnection()->addColumn(
    $installer->getTable('amtable/method'),
    'comment_img',
    "VARCHAR(255) DEFAULT NULL"
);

$installer->endSetup();
