<?php

/**
 * @category   Entrepids
 * @package    Entrepids_Cronscheduler
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_Cronscheduler_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_EMAIL_RECIPIENT = 'system/cron/error_email';
    const XML_PATH_EMAIL_TEMPLATE = 'system/cron/error_email_template';
    const XML_PATH_EMAIL_IDENTITY = 'system/cron/error_email_identity';
    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_general/email';
    const XML_PATH_EMAIL_SENDER_NAME = 'trans_email/ident_general/name';
    const XML_PATH_EMAIL_NOTIFICATION_TEMPLATE = 'system/cron/notification_email_template';
    const SEND_NOTIFICATION_NEVER = 0;
    const SEND_NOTIFICATION_ALWAYS = 1;
    const SEND_NOTIFICATION_ON_SUCCESS = 2;
    const SEND_NOTIFICATION_ON_ERROR = 3;

    private $_minutes;
    private $_minutesRange;
    private $_hours;
    private $_hoursRange;
    private $_days;
    private $_daysRange;
    private $_daysWeek;
    private $_months;

    public function getMinutesOptions() {
        if (empty($this->_minutes)) {
            $this->_minutes = array();
            for ($i = 0; $i < 60; $i++) {
                $this->_minutes[$i] = $i;
            }
        }
        return $this->_minutes;
    }

    public function getMinutesRangeOptions() {
        if (empty($this->_minutesRange)) {
            $this->_minutesRange = array();
            for ($i = 0; $i < 60; $i++) {
                $this->_minutesRange[] = array('value' => $i, 'label' => $i);
            }
        }
        return $this->_minutesRange;
    }

    public function getHoursOptions() {
        if (empty($this->_hours)) {
            $this->_hours = array();
            for ($i = 0; $i < 24; $i++) {
                $this->_hours[$i] = $i . ' hrs.';
            }
        }
        return $this->_hours;
    }

    public function getHoursRangeOptions() {
        if (empty($this->_hoursRange)) {
            $this->_hoursRange = array();
            for ($i = 0; $i < 24; $i++) {
                $this->_hoursRange[] = array('value' => $i, 'label' => $i . ' hrs');
            }
        }
        return $this->_hoursRange;
    }

    public function getDaysOptions() {
        if (empty($this->_days)) {
            $this->_days = array();
            for ($i = 1; $i <= 31; $i++) {
                $this->_days[$i] = $i;
            }
        }
        return $this->_days;
    }

    public function getDaysRangeOptions() {
        if (empty($this->_daysRange)) {
            $this->_daysRange = array();
            for ($i = 1; $i <= 31; $i++) {
                $this->_daysRange[] = array('value' => $i, 'label' => $i);
            }
        }
        return $this->_daysRange;
    }

    public function getDaysWeekOptions() {
        if (empty($this->_daysWeek)) {
            $this->_daysWeek = array(
                array('value' => 0, 'label' => $this->__('Sunday')),
                array('value' => 1, 'label' => $this->__('Monday')),
                array('value' => 2, 'label' => $this->__('Tuesday')),
                array('value' => 3, 'label' => $this->__('Wednesday')),
                array('value' => 4, 'label' => $this->__('Thursday')),
                array('value' => 5, 'label' => $this->__('Friday')),
                array('value' => 6, 'label' => $this->__('Saturday'))
            );
        }
        return $this->_daysWeek;
    }

    public function getMonthsOptions() {
        if (empty($this->_months)) {
            $this->_months = array(
                array('value' => 1, 'label' => $this->__('January')),
                array('value' => 2, 'label' => $this->__('February')),
                array('value' => 3, 'label' => $this->__('March')),
                array('value' => 4, 'label' => $this->__('April')),
                array('value' => 5, 'label' => $this->__('May')),
                array('value' => 6, 'label' => $this->__('June')),
                array('value' => 7, 'label' => $this->__('July')),
                array('value' => 8, 'label' => $this->__('August')),
                array('value' => 9, 'label' => $this->__('September')),
                array('value' => 10, 'label' => $this->__('October')),
                array('value' => 11, 'label' => $this->__('November')),
                array('value' => 12, 'label' => $this->__('December'))
            );
        }
        return $this->_months;
    }

    /**
     * Send error mail
     *
     * @param Entrepids_Cronscheduler_Model_Schedule $schedule
     * @param $status
     * @return void
     */
    public function sendNotificationMail(Entrepids_Cronscheduler_Model_Schedule $scheduler, $typeNotification, $msg = '') {
        $job = $scheduler->getJob();
        $recipients = Mage::helper('aoe_scheduler')->trimExplode(',', $job->getEmailCronNotification(), true);
        $condition = $job->getEventNotification();
        if (empty($recipients) || !($condition)) {
            return;
        }
        if($typeNotification==self::SEND_NOTIFICATION_ON_SUCCESS && $condition!=self::SEND_NOTIFICATION_ON_SUCCESS && $condition!=self::SEND_NOTIFICATION_ALWAYS ){
            return;
        }
        if($typeNotification==self::SEND_NOTIFICATION_ON_ERROR && $condition!=self::SEND_NOTIFICATION_ON_ERROR && $condition!=self::SEND_NOTIFICATION_ALWAYS){
            return;
        }
        $translate = Mage::getSingleton('core/translate'); /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        $emailTemplateVariables['job_name'] = !empty($job->getName()) ? $job->getName() : $job->getJobCode();
        $emailTemplateVariables['message'] = $msg;
        foreach ($recipients as $recipient) {
            $emailTemplate = Mage::getModel('core/email_template'); /* @var $emailTemplate Mage_Core_Model_Email_Template */
            $emailTemplate->setDesignConfig(array('area' => 'backend'));
            $emailTemplate->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_NOTIFICATION_TEMPLATE), Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY), $recipient, null, $emailTemplateVariables
            );
        }
        $translate->setTranslateInline(true);
        /*$emailTemplateVariables = array();
        $emailTemplate->getProcessedTemplate($emailTemplateVariables);
        $emailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER));
        $emailTemplate->setSenderName(Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER_NAME));
        foreach ($recipients as $recipient) {
            $emailTemplate->send($recipient, $recipient);
        }*/
    }

    public function sendErrorMail(Entrepids_Cronscheduler_Model_Schedule $schedule, $error) {
        if (!Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT)) {
            return;
        }

        $recipients = Mage::helper('aoe_scheduler')->trimExplode(',', Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT), true);

        $translate = Mage::getSingleton('core/translate'); /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        foreach ($recipients as $recipient) {
            $emailTemplate = Mage::getModel('core/email_template'); /* @var $emailTemplate Mage_Core_Model_Email_Template */
            $emailTemplate->setDesignConfig(array('area' => 'backend'));
            $emailTemplate->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE), Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY), $recipient, null, array('error' => $error, 'schedule' => $schedule)
            );
        }

        $translate->setTranslateInline(true);
    }

}
