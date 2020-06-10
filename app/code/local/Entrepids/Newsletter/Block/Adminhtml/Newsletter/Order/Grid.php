<?php

class Entrepids_Newsletter_Block_Adminhtml_Newsletter_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        
        $collection = Mage::getModel('entrepids_newsletter/catalog')->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $helper = Mage::helper('entrepids_newsletter');
       // $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
 
        $this->addColumn('Id', array(
            'header' => $helper->__('Id #'),
            'index'  => 'id',
            'filter' => false
            
        ));
        
        $this->addColumn('Nombre', array(
            'header' => $helper->__('Nombre'),
            'index'  => 'name',
            'filter' => false
        ));
        
       /* $this->addColumn('Url Imagen', array(
            'header' => $helper->__('Url Imagen'),
            'index'  => 'image' 
        ));*/
        
        $deleteLink= Mage::helper('adminhtml')->getUrl('adminhtml/newsletter/delete/') .'idCatalog/$id';
        $editLink = Mage::helper('adminhtml')->getUrl('adminhtml/newsletter/edit/') .'idCatalog/$id';
        
        $this->addColumn('action', array(
        'header'   => $this->helper('catalog')->__('Action'),
        'width'    => 15,
        'sortable' => false,
        'filter'   => false,
        'type'     => 'action',
        'actions'  => array(
            array(
                'url'     => $editLink,
                'caption' => $this->helper('entrepids_newsletter')->__('Edit'),
            ),
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
        return $this->getUrl('*/*/subscribers', array('id'=>$row->getId()));
    }
    
    protected function _prepareLayout()
    {
        $this->unsetChild('reset_filter_button');
        $this->unsetChild('search_button');
        }
}