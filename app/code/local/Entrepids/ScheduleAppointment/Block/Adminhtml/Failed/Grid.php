<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

class Entrepids_ScheduleAppointment_Block_Adminhtml_Failed_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        $this
            ->setId('id')
            ->setDefaultSort('created_at')
            ->setDefaultDir('DESC')
            ->setSaveParametersInSession(true)
            ->setUseAjax(false);
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn(
            'firstname',
            array(
                'header' => $this->__('Nombre'),
                'index'  => 'firstname'
            )
        );

        $this->addColumn(
            'lastname',
            array(
                'header' => $this->__('Apellido'),
                'index'  => 'lastname'
            )
        );

        $this->addColumn(
            'email',
            array(
                'header' => $this->__('Correo electronico'),
                'index'  => 'email'
            )
        );

        $this->addColumn(
            'telephone',
            array(
                'header' => $this->__('Telefono'),
                'index'  => 'telephone'
            )
        );

        $this->addColumn(
            'postal_code',
            array(
                'header' => $this->__('Codigo postal'),
                'index'  => 'postal_code'
            )
        );

        $this->addColumn(
            'product_id',
            array(
                'header' => $this->__('ID producto'),
                'index'  => 'product_id'
            )
        );

        $this->addColumn(
            'name',
            array(
                'header' => $this->__('Producto de interes'),
                'index'  => 'name'
            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => $this->__('Alta'),
                'index'  => 'created_at'
            )
        );

    }

    protected function _prepareCollection()
    {
        $entityTypeId = Mage::getModel('eav/entity')
            ->setType('catalog_product')
            ->getTypeId();
            
        $prodNameAttrId = Mage::getModel('eav/entity_attribute')
            ->loadByCode($entityTypeId, 'name')
            ->getAttributeId();

        $products = Mage::getModel('scheduleappointment/scheduleappointment')
                    ->getCollection()
                    ->addFieldToFilter('valid_pc', 0);
        
        $products->getSelect()->joinLeft(array('cv'=>'catalog_product_entity_varchar'),'cv.entity_id=main_table.product_id AND cv.attribute_id ='.$prodNameAttrId,array('name'=>'value'));
        //$products->load();

        $this->setCollection($products);
        parent::_prepareCollection();
        return $this;
        
    }
    
    public function getRowUrl($row){
       return false;
    }
}
