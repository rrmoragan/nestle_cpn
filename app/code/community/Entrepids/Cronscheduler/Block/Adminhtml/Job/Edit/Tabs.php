<?php
/**
* @category   Entrepids
* @package    Entrepids_Cronscheduler
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Cronscheduler_Block_Adminhtml_Job_Edit_Tabs extends Aoe_Scheduler_Block_Adminhtml_Job_Edit_Tabs
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('job_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Jobs'));
    }
}
