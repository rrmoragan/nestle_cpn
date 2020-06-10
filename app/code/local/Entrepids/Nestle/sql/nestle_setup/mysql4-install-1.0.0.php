<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


$installer = $this;
$installer->startSetup();

/**
 * Creating nestle_prospect_customers table
 */

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('nestle/prospectcustomers')}` (
        `id` INT( 10 ) NOT NULL AUTO_INCREMENT,
        `name` TINYTEXT NOT NULL,
        `lastname` TINYTEXT NULL,
        `email` TEXT NOT NULL,
        `visits` INT( 10 ) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `last_visit` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
        `customer_id` INT( 10 ) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = 'Clientes Prospecto';
");

$installer->endSetup();