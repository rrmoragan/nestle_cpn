<?php
/**
* @category   Entrepids
* @package    Entrepids_Cronscheduler
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Cronscheduler_Adminhtml_HiddenjobsController extends Aoe_Scheduler_Controller_AbstractController
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->__('Hidden Jobs Configuration'), $this->__('Hidden Jobs Configuration'))
            ->_title($this->__('Hidden Jobs Configuration'))
            ->renderLayout();
    }
    
    /**
     * Mass action: disable
     *
     * @return void
     */
    public function disableAction()
    {
        $codes = $this->getMassActionCodes();
        foreach ($codes as $code) {
            /** @var Entrepids_Cronscheduler_Model_Job $job */
            $job = Mage::getModel('cronscheduler/job')->load($code);
            if ($job->getJobCode() && $job->getIsActive()) {
                $job->setIsActive(false)->save();
                $this->_getSession()->addSuccess($this->__('Disabled "%s"', $code));

                /* @var Aoe_Scheduler_Model_ScheduleManager $scheduleManager */
                $scheduleManager = Mage::getModel('cronscheduler/scheduleManager');
                $scheduleManager->flushSchedules($job->getJobCode());
                $this->_getSession()->addNotice($this->__('Pending schedules for "%s" have been flushed.', $job->getJobCode()));
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Mass action: enable
     *
     * @return void
     */
    public function enableAction()
    {
        $codes = $this->getMassActionCodes();
        foreach ($codes as $code) {
            /** @var Entrepids_Cronscheduler_Model_Job $job */
            $job = Mage::getModel('cronscheduler/job')->load($code);
            if ($job->getJobCode() && !$job->getIsActive()) {
                $job->setIsActive(true)->save();
                $this->_getSession()->addSuccess($this->__('Enabled "%s"', $code));

                /* @var Aoe_Scheduler_Model_ScheduleManager $scheduleManager */
                $scheduleManager = Mage::getModel('cronscheduler/scheduleManager');
                $scheduleManager->generateSchedulesForJob($job);
                $this->_getSession()->addNotice($this->__('Job "%s" has been scheduled.', $job->getJobCode()));
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Mass action: schedule now
     *
     * @return void
     */
    public function scheduleNowAction()
    {
        $codes = $this->getMassActionCodes();
        foreach ($codes as $key) {
            Mage::getModel('cron/schedule')
                ->setJobCode($key)
                ->setScheduledReason(Aoe_Scheduler_Model_Schedule::REASON_SCHEDULENOW_WEB)
                ->schedule()
                ->save();

            $this->_getSession()->addSuccess($this->__('Job "%s" has been scheduled.', $key));
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Mass action: run now
     *
     * @return void
     */
    public function runNowAction()
    {
        if (!Mage::getStoreConfig('system/cron/enableRunNow')) {
            Mage::throwException("'Run now' disabled by configuration (system/cron/enableRunNow)");
        }
        $codes = $this->getMassActionCodes();
        foreach ($codes as $key) {
            $schedule = Mage::getModel('cron/schedule')
                ->setJobCode($key)
                ->setScheduledReason(Aoe_Scheduler_Model_Schedule::REASON_RUNNOW_WEB)
                ->runNow(false)// without trying to lock the job
                ->save();

            $messages = $schedule->getMessages();

            if (in_array($schedule->getStatus(), array(Aoe_Scheduler_Model_Schedule::STATUS_SUCCESS, Aoe_Scheduler_Model_Schedule::STATUS_DIDNTDOANYTHING))) {
                $this->_getSession()->addSuccess($this->__('Ran "%s" (Duration: %s sec)', $key, intval($schedule->getDuration())));
                if ($messages) {
                    $this->_getSession()->addSuccess($this->__('"%s" messages:<pre>%s</pre>', $key, $messages));
                }
            } else {
                $this->_getSession()->addError($this->__('Error while running "%s"', $key));
                if ($messages) {
                    $this->_getSession()->addError($this->__('"%s" messages:<pre>%s</pre>', $key, $messages));
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Init job instance and set it to registry
     *
     * @return Entrepids_Cronscheduler_Model_Job
     */
    protected function _initJob()
    {
        $jobCode = $this->getRequest()->getParam('job_code', null);
        $job = Mage::getModel('cronscheduler/job')->load($jobCode);
        Mage::register('current_job_instance', $job);
        return $job;
    }

    protected function getMassActionCodes($key = 'codes')
    {
        $codes = $this->getRequest()->getParam($key);
        if (!is_array($codes)) {
            return array();
        }
        $allowedCodes = Mage::getSingleton('cronscheduler/job')->getResource()->getJobCodes();
        $codes = array_intersect(array_unique(array_filter(array_map('trim', $codes))), $allowedCodes);
        return $codes;
    }
    
    

    /**
     * ACL checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/aoe_scheduler/aoe_scheduler_cron');
    }
}
