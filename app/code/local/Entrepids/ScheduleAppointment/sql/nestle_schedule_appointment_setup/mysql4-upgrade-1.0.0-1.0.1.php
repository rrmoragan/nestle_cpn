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
ALTER TABLE {$this->getTable('scheduleappointment/scheduleappointment')} 
  ADD `valid_pc` SMALLINT (1) DEFAULT 1;
");
  
$installer->endSetup(); 