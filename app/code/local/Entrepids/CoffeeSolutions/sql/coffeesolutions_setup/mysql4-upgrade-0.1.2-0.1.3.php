<?php
/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('entrepids_coffee_solutions')}; 


CREATE TABLE {$this->getTable('entrepids_coffee_solutions')} (
  `entity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `solution_name` varchar(250) NOT NULL,
  `solution_image` varchar(250) NOT NULL,
  `min_coups_available` int(10) UNSIGNED NULL,
  `max_coups_available` int(10) UNSIGNED NULL,
  `available` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `products` TEXT NULL,
  `last_update` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE {$this->getTable('entrepids_coffee_solutions_places')} (
  `entity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `solution_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `prioridad` int(10) NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();