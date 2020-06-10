<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

class Entrepids_ScheduleAppointment_Block_Adminhtml_Postalcode_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        $this
            ->setId('postal_code')
            ->setDefaultSort('postal_code')
            ->setDefaultDir('DESC')
            ->setSaveParametersInSession(true)
            ->setUseAjax(false);
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn(
            'postal_code',
            array(
                'header' => $this->__('Codigo Postal'),
                'index'  => 'postal_code',
                'filter_index' => 'main_table.postal_code'
            )
        );

        $this->addColumn(
            'municipality',
            array(
                'header' => $this->__('Municipio'),
                'index'  => 'municipality'
            )
        );

        $this->addColumn(
            'default_name',
            array(
                'header' => $this->__('Estado'),
                'index'  => 'default_name'
            )
        );
    }

    protected function _prepareCollection()
    {
        $cps = Mage::getSingleton('core/resource')->getTableName('completeaddress/neighborhood');
        $collection = Mage::getModel('scheduleappointment/cpscheduleappointment')->getCollection()->addFieldToSelect('postal_code');

        $collection->getSelect()->joinLeft(array('nh'=>$cps), 'main_table.postal_code = nh.postal_code', array('nh.municipality'))
                                ->joinLeft( 'directory_country_region', 'directory_country_region.region_id = nh.region_id', array('default_name'))
                                ->group('main_table.postal_code');
        
        /*$query = $collection->getSelect()->__toString();
        Mage::log($query,null,'query.log');
        die();*/
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareMassaction(){
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('deleteAction');
        
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('scheduleappointment')->__('Delete'),
            'url' => $this->getUrl('*/*/delete'),
            'confirm' => Mage::helper('scheduleappointment')->__('Are you sure')
        ));
        return $this;
    }
    
    public function getRowUrl($row){
       return false;
    }
    
}
