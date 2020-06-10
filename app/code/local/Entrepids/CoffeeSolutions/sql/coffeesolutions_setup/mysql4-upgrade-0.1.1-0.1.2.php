<?php
/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
$installer = $this;

$installer->startSetup();

/*$installer->removeAttribute('catalog_category', 'cs_num_tazas_ini');
$installer->removeAttribute('catalog_category', 'cs_num_tazas_fin');
$installer->removeAttribute('catalog_category', 'cs_linea');
$installer->removeAttribute('catalog_category', 'cs_prioridad');*/

$installer->run("
  
DROP TABLE IF EXISTS {$this->getTable('entrepids_coffee_solutions')};
CREATE TABLE {$this->getTable('entrepids_coffee_solutions')} (
  `entity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `solution_name` varchar(250) NOT NULL,
  `available` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `places` TEXT NULL,
  `products` TEXT NULL,
  `last_update` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();