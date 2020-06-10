<?php

/**
 * TimelineDetail block
 *
 * @author Fabrizio Branca
 */
class Entrepids_Cronscheduler_Block_Adminhtml_TimelineDetail extends Mage_Adminhtml_Block_Template
{

    /**
     * @var string path to default template
     */
    protected $_template = 'aoe_scheduler/timeline_detail.phtml';

    /**
     * @var Entrepids_Cronscheduler_Model_Schedule
     */
    protected $schedule;


    /**
     * Set schedule
     *
     * @param Entrepids_Cronscheduler_Model_Schedule $schedule
     * @return Aoe_Scheduler_Block_Adminhtml_TimelineDetail
     */
    public function setSchedule(Entrepids_Cronscheduler_Model_Schedule $schedule)
    {
        $this->schedule = $schedule;
        return $this;
    }


    /**
     * Get schedule
     *
     * @return Aoe_Scheduler_Block_Adminhtml_TimelineDetail
     */
    public function getSchedule()
    {
        return $this->schedule;
    }
}
