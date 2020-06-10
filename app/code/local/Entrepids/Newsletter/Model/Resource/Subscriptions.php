<?php

class Entrepids_Newsletter_Model_Resource_Subscriptions extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('entrepids_newsletter/subscriptions', 'id');
    }
}
