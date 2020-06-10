<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
$temp = array();

$fileName = 'catalog_postal_codes.csv';
$dir = Mage::getBaseDir('app') . DS . 'code'.DS.'local'.DS.'Entrepids'.DS.'ScheduleAppointment'.DS.'data'.DS.'nestle_schedule_appointment_setup'.DS;
$openStream = fopen($dir.$fileName, 'r');

if ($openStream !== FALSE) {
  while ($content = fgetcsv($openStream, 10, ',')) {
      if(sizeof($content)){
          $cp = $content[0];
          if(strlen($cp) == 5){
              $temp[] = array('postal_code' => $cp);
          }
      }
  }
}

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$table = $resource->getTableName('scheduleappointment/cpscheduleappointment');

//foreach ($temp as $in) {
  $writeConnection->insertMultiple($table,$temp);
//}
$writeConnection->closeConnection();