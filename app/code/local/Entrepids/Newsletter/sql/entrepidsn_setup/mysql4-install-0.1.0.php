<?php
$installer = $this;
$installer->startSetup();

$queueTable = $installer->getTable('newsletter_queue');
$conn = $installer->getConnection();
$conn->addColumn($queueTable, 'catalog_id', "int(10) NOT NULL");

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('entrepids_newsletter/catalog')}` (
      `id` int(11) NOT NULL auto_increment,
      `name` text,
      `image` text,
      `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
CREATE TABLE IF NOT EXISTS `{$installer->getTable('entrepids_newsletter/subscriptions')}` (
      `id` int(11) NOT NULL auto_increment,
      `customer_id` int(10) NOT NULL,
      `catalog_id` int(10) NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


   
");
$installer->endSetup();