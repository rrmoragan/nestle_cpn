<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


class Entrepids_ScheduleAppointment_Model_Mysql4_Scheduleappointment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('scheduleappointment/scheduleappointment', 'id');
    }
}