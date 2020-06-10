<?php

class Entrepids_Newsletter_Block_Adminhtml_Subscribers_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        
       $collection = Mage::getModel("entrepids_newsletter/subscriptions")
               ->getCollection()
               ->addFieldToFilter("catalog_id",$this->getRequest()->getParam("id"))
               ->load();
       
        $customerIds = [];
        foreach($collection as $register){
            $customerIds[] = $register->getCustomerId();
        }
        $customers = Mage::getModel("customer/customer")
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in'=>$customerIds))
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname')
                ->load();
        $this->setCollection($customers);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $helper = Mage::helper('entrepids_newsletter');
       // $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
 
        $this->addColumn('Id', array(
            'header' => $helper->__('User id #'),
            'index'  => 'entity_id',
            'filter' => false
            
        ));
        
        
        $this->addColumn('Email', array(
            'header' => $helper->__('Email'),
            'index'  => 'email',
            'filter' => false
        ));
        
        
        $this->addColumn('firstname', array(
            'header' => $helper->__('Nombre'),
            'index'  => 'firstname',
            'filter' => false
        ));
        
        $this->addColumn('lastname', array(
            'header' => $helper->__('Last Name'),
            'index'  => 'lastname',
            'filter' => false
        ));
        
      
        
        $deleteLink= Mage::helper('adminhtml')
                ->getUrl('adminhtml/newsletter/deleteSubscriber/')
                .'idUser/$entity_id/idCatalog/'.$this->getRequest()->getParam("id");
      
        $this->addColumn('action', array(
        'header'   => $this->helper('catalog')->__('Action'),
        'width'    => 15,
        'sortable' => false,
        'filter'   => false,
        'type'     => 'action',
        'actions'  => array(
            array(
                'url'     => $deleteLink,
                'caption' => $this->helper('entrepids_newsletter')->__('Delete'),
            )
            
        )
    ));
 
        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        return ""; // $this->getUrl('*/*/grid', array('id'=>$row->getId()));
    }
    

    
    protected function _prepareLayout()
    {
        $this->unsetChild('reset_filter_button');
        $this->unsetChild('search_button');
     }
     
     protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('entrepids_newsletter')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete/idCatalog/'.$this->getRequest()->getParam("id")),
             'confirm'  => Mage::helper('entrepids_newsletter')->__('Are you sure?')
        ));

        return $this;
    }
}