<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/


class Entrepids_Nestle_Block_Adminhtml_Customer_Prospects_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('nestle/prospectcustomers')
                        ->getCollection()
                        ->addFieldToFilter('customer_id',array('null' => true));
        
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this; 
    }
    
    protected function _prepareColumns()
    {
        $helper = Mage::helper('nestle');

        $this->addColumn('id', array(
            'header' => $helper->__('ID'),
            'index'  => 'id'
        ));
 
        $this->addColumn('name', array(
            'header' => $helper->__('User name'),
            'type'   => 'varchar',
            'index'  => 'name'
        ));

        $this->addColumn('lastname', array(
            'header' => $helper->__('Lastname'),
            'type'   => 'varchar',
            'index'  => 'lastname'
        ));
 
        $this->addColumn('email', array(
            'header'       => $helper->__('Email'),
            'type'         => 'varchar',
            'index'        => 'email'
        ));

        $this->addColumn('visits', array(
            'header'       => $helper->__('Visits'),
            'type'         => 'varchar',
            'index'        => 'visits'
        ));
        
        $this->addColumn('last_visit', array(
            'header'       => $helper->__('Last Visit'),
            'type'         => 'varchar',
            'index'        => 'last_visit'
        ));

        $this->addColumn('created_at', array(
            'header'       => $helper->__('Created At'),
            'type'         => 'varchar',
            'index'        => 'created_at'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return false;
     }
}