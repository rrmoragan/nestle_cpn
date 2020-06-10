<?php
/**
* @category   Entrepids
* @package    Entrepids_Cronscheduler
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Cronscheduler_Block_Adminhtml_Hiddenjobs_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('hiddenjobs_grid');
        $this->_filterVisibility = true;
        $this->_pagerVisibility = false;
    }


    /**
     * Preparation of the data that is displayed by the grid.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var Entrepids_Cronscheduler_Model_Resource_Job_Collection $collection */
        $collection = Mage::getSingleton('cronscheduler/job')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('codes');
        $this->getMassactionBlock()->addItem(
            'schedule',
            array(
                'label' => $this->__('Schedule now'),
                'url'   => $this->getUrl('*/*/scheduleNow'),
            )
        );
        if (Mage::getStoreConfig('system/cron/enableRunNow')) {
            $this->getMassactionBlock()->addItem(
                'run',
                array(
                    'label' => $this->__('Run now'),
                    'url'   => $this->getUrl('*/*/runNow'),
                )
            );
        }
        $this->getMassactionBlock()->addItem(
            'disable',
            array(
                'label' => $this->__('Disable'),
                'url'   => $this->getUrl('*/*/disable'),
            )
        );
        $this->getMassactionBlock()->addItem(
            'enable',
            array(
                'label' => $this->__('Enable'),
                'url'   => $this->getUrl('*/*/enable'),
            )
        );
        return $this;
    }
    /*
     * Custom filters
     */
    protected function _customfilterByJob($collection,$column){
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->_filterByJobCode($value);
        return;
    }
    protected function _customfilterByName($collection,$column){
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->_filterByJobName($value);
        return;
    }
    /**
     * Preparation of the requested columns of the grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'job_code',
            array(
                'header'   => $this->__('Job code'),
                'index'    => 'job_code',
                'sortable' => false,
                'filter_condition_callback' => array($this, '_customfilterByJob'),
            )
        );

        $this->addColumn(
            'name',
            array(
                'header'   => $this->__('Name'),
                'index'    => 'name',
                'sortable' => false,
                'filter_condition_callback' => array($this, '_customfilterByName'),
            )
        );

        $this->addColumn(
            'short_description',
            array(
                'header'   => $this->__('Short Description'),
                'index'    => 'short_description',
                'sortable' => false,
                'filter'  => false,
            )
        );

        $this->addColumn(
            'schedule_cron_expr',
            array(
                'header'         => $this->__('Cron expression'),
                'index'          => 'schedule_cron_expr',
                'sortable'       => false,
                'frame_callback' => array($this, 'decorateCronExpression'),
                'filter'  => false,
            )
        );
        $this->addColumn(
            'type',
            array(
                'header'         => $this->__('Type'),
                'sortable'       => false,
                'frame_callback' => array($this, 'decorateType'),
                'filter'  => false,
            )
        );
        $this->addColumn(
            'is_active',
            array(
                'header'         => $this->__('Status'),
                'index'          => 'is_active',
                'sortable'       => false,
                'frame_callback' => array($this, 'decorateStatus'),
                'filter'  => false,
            )
        );
        return parent::_prepareColumns();
    }


    /**
     * Decorate status column values
     *
     * @param $value
     *
     * @return string
     */
    public function decorateStatus($value)
    {
        $cell = sprintf(
            '<span class="grid-severity-%s"><span>%s</span></span>',
            $value ? 'notice' : 'critical',
            $this->__($value ? 'Enabled' : 'Disabled')
        );
        return $cell;
    }


    /**
     * Decorate cron expression
     *
     * @param                         $value
     * @param Entrepids_Cronscheduler_Model_Job $job
     *
     * @return string
     */
    public function decorateCronExpression($value, Entrepids_Cronscheduler_Model_Job $job)
    {
        return $job->getCronExpression();
    }


    /**
     * Decorate cron expression
     *
     * @param $value
     *
     * @return string
     */
    public function decorateTrim($value)
    {
        return sprintf('<span title="%s">%s</span>', $value, mb_strimwidth($value, 0, 40, "..."));
    }


    /**
     * Decorate cron expression
     *
     * @param                         $value
     * @param Entrepids_Cronscheduler_Model_Job $job
     *
     * @return string
     */
    public function decorateType($value, Entrepids_Cronscheduler_Model_Job $job)
    {
        return $job->getType();
    }

    /**
     * Row click url
     *
     * @param object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/job/edit', array('job_code' => $row->getJobCode()));
    }

    /**
     * Helper function to receive grid functionality urls for current grid
     *
     * @return string Requested URL
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/hiddenjobs/index', array('_current' => true));
    }
}
