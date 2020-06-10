<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


class Entrepids_ScheduleAppointment_Model_Mysql4_Cpscheduleappointment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('scheduleappointment/cpscheduleappointment');
    }
    
    
    public function getSize() {

        if ( is_null( $this->_totalRecords ) ) {
            $sql = $this->getSelectCountSql();
            //Mage::log($sql->__toString(),null,'query.log');
            // fetch all rows since it's a joined table and run a count against it.
            $this->_totalRecords = count( $this->getConnection()->fetchall( $sql, $this->_bindParams ) );
        }

        return intval( $this->_totalRecords );
    }
}