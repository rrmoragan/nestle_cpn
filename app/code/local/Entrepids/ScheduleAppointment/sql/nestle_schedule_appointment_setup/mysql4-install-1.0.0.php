<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
$installer = $this;
  
$installer->startSetup();
  
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('scheduleappointment/cpscheduleappointment')};
CREATE TABLE {$this->getTable('scheduleappointment/cpscheduleappointment')} (
  `postal_code` varchar(10) NOT NULL,
  PRIMARY KEY (`postal_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('scheduleappointment/scheduleappointment')};
CREATE TABLE {$this->getTable('scheduleappointment/scheduleappointment')} (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` varchar(70) NOT NULL,
  `lastname` varchar(70) NOT NULL,
  `telephone` varchar(15) NOT NULL,
  `email` varchar(150) NOT NULL,
  `postal_code` int(10) UNSIGNED NOT NULL,
  `product_id` INT( 10 ) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
  
$installer->endSetup(); 