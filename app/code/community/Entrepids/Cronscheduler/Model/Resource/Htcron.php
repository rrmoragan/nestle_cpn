<?php
/**
* @category   Entrepids
* @package    Entrepids_Cronscheduler
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Cronscheduler_Model_Resource_Htcron extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {  
        $this->_init('cronscheduler/htcron', 'schedule_id');
    }
}