<?php
/**
* @category   Entrepids
* @package    Entrepids_Cronscheduler
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Cronscheduler_Block_Adminhtml_Job_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Internal constructor
     *
     */    
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('General');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get job
     *
     * @return Aoe_Scheduler_Model_Job
     */
    public function getJob()
    {
        return Mage::registry('current_job_instance');
    }
    
    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        /*
         * @var $helper Entrepids_Cronscheduler_Helper_Data 
         */
        $helper = Mage::helper('cronscheduler/data');
        $job = $this->getJob();
        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post'
            )
        );

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $this->__('General')));
        $this->_addElementTypes($fieldset);

        $fieldset->addField(
            'job_code',
            'text',
            array(
                'name'     => 'job_code',
                'label'    => $this->__('Job code'),
                'title'    => $this->__('Job code'),
                'class'    => '',
                'required' => true,
                'disabled' => $job->getJobCode() ? true : false,
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'name'               => 'name',
                'label'              => $this->__('Name'),
                'title'              => $this->__('Name'),
                'class'              => '',
                'required'           => false,
                'after_element_html' => $this->getOriginalValueSnippet($job, 'name'),
            )
        );

        $fieldset->addField(
            'short_description',
            'textarea',
            array(
                'name'               => 'short_description',
                'label'              => $this->__('Short description'),
                'title'              => $this->__('Short description'),
                'class'              => '',
                'required'           => false,
                'after_element_html' => $this->getOriginalValueSnippet($job, 'short_description'),
            )
        );

        $fieldset->addField(
            'description',
            'textarea',
            array(
                'name'               => 'description',
                'label'              => $this->__('Description'),
                'title'              => $this->__('Description'),
                'class'              => '',
                'required'           => false,
                'after_element_html' => $this->getOriginalValueSnippet($job, 'description'),
            )
        );

        $fieldset->addField(
            'run_model',
            'text',
            array(
                'name'               => 'run_model',
                'label'              => $this->__('Run model'),
                'title'              => $this->__('Run model'),
                'class'              => '',
                'required'           => true,
                'note'               => $this->__('e.g. "aoe_scheduler/task_heartbeat::run"'),
                'after_element_html' => $this->getOriginalValueSnippet($job, 'run/model'),
            )
        );

        $fieldset->addField(
            'is_active',
            'select',
            array(
                'name'               => 'is_active',
                'label'              => $this->__('Status'),
                'title'              => $this->__('Status'),
                'required'           => true,
                'options'            => array(
                    0 => $this->__('Disabled'),
                    1 => $this->__('Enabled')
                ),
                'after_element_html' => $this->getOriginalValueSnippetFlag($job, 'is_active', 'Enabled', 'Disabled'),
            )
        );
        
        $fieldset->addField(
            'visible',
            'select',
            array(
                'name'               => 'visible',
                'label'              => $this->__('Visible'),
                'title'              => $this->__('Visible'),
                'required'           => true,
                'options'            => array(
                    0 => $this->__('No'),
                    1 => $this->__('Yes')
                )
            )
        );

        $fieldset = $form->addFieldset('cron_fieldset', array('legend' => $this->__('Scheduling')));
        $this->_addElementTypes($fieldset);

        $fieldset->addField(
            'schedule_config_path',
            'text',
            array(
                'name'               => 'schedule_config_path',
                'label'              => $this->__('Cron configuration path'),
                'title'              => $this->__('Cron configuration path'),
                'class'              => '',
                'required'           => false,
                'note'               => $this->__(
                    'Path to system configuration containing the cron configuration for this job. (e.g. system/cron/scheduler_cron_expr_heartbeat) This configuration - if set - has a higher priority over the cron expression configured with the job directly.'
                ),
                'after_element_html' => $this->getOriginalValueSnippet($job, 'schedule/config_path'),
            )
        );
        
        $fieldset->addField(
            'cron_frequency',
            'select',
            array(
                'id'                 => 'cron_frequency',
                'name'               => 'cron_frequency',
                'label'              => $this->__('Select a frequency:'),
                'title'              => $this->__('Select a frequency:'),
                'required'           => true,
                'options'            => array(
                    0 => $this->__('Never'),
                    1 => $this->__('Always'),
                    2 => $this->__('Minutes'),
                    3 => $this->__('Hours'),
                    4 => $this->__('Daily'),
                    5 => $this->__('Weekly'),
                    6 => $this->__('Monthly'),
                    7 => $this->__('Yearly'),
                )
            )
        );
        
       //#######################################################################
        
        $fieldset->addField(
            'yearly_months',
            'multiselect',
            array(
                'id'                 => 'yearly_months',
                'name'               => 'yearly_months',
                'label'              => $this->__('What months?'),
                'title'              => $this->__('What months?'),
                'required'           => false,
                'values'            => $helper->getMonthsOptions()
            )
        );
        
        $fieldset->addField(
            'monthly_days',
            'multiselect',
            array(
                'id'                 => 'monthly_days',
                'name'               => 'monthly_days',
                'label'              => $this->__('What days?'),
                'title'              => $this->__('What days?'),
                'required'           => false,
                'values'            => $helper->getDaysRangeOptions(),
                'note'               => $this->__('Doubleclick for generate time intervals')
            )
        );
        
        $fieldset->addField(
            'weekly_days',
            'multiselect',
            array(
                'id'                 => 'weekly_days',
                'name'               => 'weekly_days',
                'label'              => $this->__('What weekdays?'),
                'title'              => $this->__('What weekdays?'),
                'required'           => false,
                'values'            => $helper->getDaysWeekOptions()
            )
        );
        
        $fieldset->addField(
            'hours_range',
            'multiselect',
            array(
                'id'                 => 'hours_range',
                'name'               => 'hours_range',
                'label'              => $this->__('What hours?'),
                'title'              => $this->__('What hours?'),
                'required'           => false,
                'values'            => $helper->getHoursRangeOptions(),
                'note'               => $this->__('Doubleclick for generate time intervals')
            )
        );
        
        $fieldset->addField(
            'a_hour',
            'select',
            array(
                'id'                 => 'a_hour',
                'name'               => 'a_hour',
                'label'              => $this->__('What hour?'),
                'title'              => $this->__('What hour?'),
                'required'           => false,
                'options'            => $helper->getHoursOptions()
            )
        );
        
        $fieldset->addField(
            'minutes_range',
            'multiselect',
            array(
                'id'                 => 'minutes_range',
                'name'               => 'minutes_range',
                'label'              => $this->__('What minutes?'),
                'title'              => $this->__('What minutes?'),
                'required'           => false,
                'values'            => $helper->getMinutesRangeOptions(),
                'note'               => $this->__('Doubleclick for generate time intervals')
            )
        );
        
        $fieldset->addField(
            'a_minute',
            'select',
            array(
                'id'                 => 'a_minute',
                'name'               => 'a_minute',
                'label'              => $this->__('What minute?'),
                'title'              => $this->__('What minute?'),
                'required'           => false,
                'options'            => $helper->getMinutesOptions()
            )
        );
        
        ///#####################################################################

        $fieldset->addField(
            'schedule_cron_expr',
            'text',
            array(
                'id'                 => 'schedule_cron_expr',
                'name'               => 'schedule_cron_expr',
                'label'              => $this->__('Cron expression (autogenerated)'),
                'title'              => $this->__('Cron expression (autogenerated)'),
                'required'           => false,
                'readonly'           => true,
                'note'               => $this->__('e.g "*/5 * * * *" or "always"'),
                'after_element_html' => $this->getOriginalValueSnippet($job, 'schedule/cron_expr'),
            )
        );
        
        $fieldset = $form->addFieldset('notifications_fieldset', array('legend' => $this->__('Notification')));
        $this->_addElementTypes($fieldset);
        
        $fieldset->addField(
            'event_notification',
            'select',
            array(
                'id'                 => 'event_notification',
                'name'               => 'event_notification',
                'label'              => $this->__('Send notification:'),
                'title'              => $this->__('Send notification:'),
                'required'           => true,
                'options'            => array(
                    0 => $this->__('Never'),
                    1 => $this->__('Always'),
                    2 => $this->__('On success'),
                    3 => $this->__('On error'),
                )
            )
        );
        
        $fieldset->addField(
            'email_cron_notification',
            'text',
            array(
                'id'                 => 'email_cron_notification',
                'name'               => 'email_cron_notification',
                'class'              => 'validate-email',
                'label'              => $this->__('Mail for receive notifications:'),
                'title'              => $this->__('Mail for receive notifications:'),
                'required'           => false,
                'note'               => $this->__('e.g. "someone@example.com"'),
                'after_element_html' => $this->getOriginalValueSnippet($job, 'schedule/cron_expr'),
            )
        );
        
        $fieldset = $form->addFieldset('parameter_fieldset', array('legend' => $this->__('Extras')));
        $this->_addElementTypes($fieldset);

        $fieldset->addField(
            'parameters',
            'textarea',
            array(
                'name'               => 'parameters',
                'label'              => $this->__('Parameters'),
                'title'              => $this->__('Parameters'),
                'class'              => 'textarea',
                'required'           => false,
                'note'               => $this->__('These parameters will be passed to the model. It is up to the model to specify the format of these parameters (e.g. json/xml/...'),
                'after_element_html' => $this->getOriginalValueSnippet($job, 'parameters'),
            )
        );

        $fieldset->addField(
            'groups',
            'textarea',
            array(
                'name'               => 'groups',
                'label'              => $this->__('Groups'),
                'title'              => $this->__('Groups'),
                'class'              => 'textarea',
                'required'           => false,
                'note'               => $this->__('Comma-separated list of groups (tags) that can be used with the include/exclude command line options of scheduler.php'),
                'after_element_html' => $this->getOriginalValueSnippet($job, 'groups'),
            )
        );

        $fieldset = $form->addFieldset('dependency_fieldset', array('legend' => $this->__('Dependencies')));
        $this->_addElementTypes($fieldset);

        $fieldset->addField(
            'on_success',
            'textarea',
            array(
                'name'               => 'on_success',
                'label'              => $this->__('Run jobs on success'),
                'title'              => $this->__('Run jobs on success'),
                'class'              => 'textarea',
                'required'           => false,
                'note'               => $this->__('Comma-separated list of job codes that will be scheduled after the current cron job has completed successfully.')
            )
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getOriginalValueSnippet(Entrepids_Cronscheduler_Model_Job $job, $key)
    {
        if ($job->isDbOnly()) {
            return '';
        }

        $xmlJobData = $job->getXmlJobData();
        if (!array_key_exists($key, $xmlJobData)) {
            return '';
        }

        $value = $xmlJobData[$key];
        if ($value === null || $value === '') {
            $value = '<em>empty</em>';
        }

        return '<p class="original" style="background-color: white"><strong>Original:</strong> ' . $value . '</p>';
    }

    protected function getOriginalValueSnippetFlag(Entrepids_Cronscheduler_Model_Job $job, $key, $trueLabel, $falseLabel)
    {
        if ($job->isDbOnly()) {
            return '';
        }

        $xmlJobData = $job->getXmlJobData();
        if (!array_key_exists($key, $xmlJobData)) {
            return '';
        }

        $value = $this->__(!in_array($xmlJobData[$key], array(false, 'false', 0, '0'), true) ? $trueLabel : $falseLabel);

        return '<p class="original" style="background-color: white"><strong>Original:</strong> ' . $value . '</p>';
    }

    /**
     * Initialize form fields values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getJob()->getData());
        return parent::_initFormValues();
    }
}
