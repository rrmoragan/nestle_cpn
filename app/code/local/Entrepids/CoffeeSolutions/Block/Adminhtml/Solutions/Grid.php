<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Block_Adminhtml_Solutions_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {

        parent::__construct();
        $this->setId('coffee_solutions_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('coffeesolutions/solutions')->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $helper = Mage::helper('coffeesolutions');

        $this->addColumn('entity_id', array(
            'header' => $helper->__('ID Solución'),
            'index' => 'entity_id'
        ));

        $this->addColumn('solution_name', array(
            'header' => $helper->__('Nombre de la solución'),
            'index' => 'solution_name'
        ));
        
        /*$this->addColumn('qty_products', array(
            'header' => $helper->__('Productos en la solución'),
            'index' => 'total_products',
        ));*/

        $this->addColumn('min_coups_available', array(
            'header' => $helper->__('Número mínimo de Tazas/Día'),
            'index' => 'min_coups_available',
        ));

        $this->addColumn('max_coups_available', array(
            'header' => $helper->__('Número máximo de Tazas/Día'),
            'index' => 'max_coups_available',
        ));

        $this->addColumn('available', array(
            'header' => $helper->__('Disponible'),
            'index' => 'available',
            'renderer' => 'Entrepids_CoffeeSolutions_Block_Adminhtml_Solutions_Widget_Renderer_Solution'
        ));

        $this->addColumn('last_update', array(
            'type' => 'datetime',
            'header' => $helper->__('Última modificación'),
            'index' => 'last_update'
        ));

        //$this->addExportType('*/*/exportCsv', $helper->__('CSV'));
        //$this->addExportType('*/*/exportExcel', $helper->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getEntityId()));
    }

}