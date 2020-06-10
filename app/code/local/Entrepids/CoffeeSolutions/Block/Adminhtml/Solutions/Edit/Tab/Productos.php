<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */

class Entrepids_CoffeeSolutions_Block_Adminhtml_Solutions_Edit_Tab_Productos extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productos_grid');
        $this->setUseAjax(true); // Se obliga para cargar en Ajax este Layout
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(false);
        if(!empty($this->getProducts())){
            $this->setDefaultFilter(array('products'=>1));
        }
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    
    /**
     * Prepara la colección nativa de Magento
     * 
     * **/
    protected function _prepareCollection() {
        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Métodos _prepareColumns()
     * 
     * Prepara las columnas nativas d eMagento como se acostumbra en otros Grids
     * by @maph65
     * @return int[]
     * 
     * */
    
    protected function _prepareColumns() {

        $this->addColumn('products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'products',
            'values'            => $this->_getProducts(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));
        
        
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));
        
        return parent::_prepareColumns();
    }
    
    /**
     * Métodos getProducts()
     * 
     * Obtiene la información de los productos ya seteados en el Grid
     * @return int[]
     * 
     * by @maph65
     * 
     * */
    public function _getProducts(){
        return $this->getProducts();
    }
    
    /**
     * Métodos getProducts()
     * 
     * Obtiene la información de los productos ya seteados en el Grid
     * Este método debe devolver un array asociativo por ID cuando se tiene información
     * en campos adicionales dentro del Grid de productos
     * @return int[]
     * 
     * by @maph65
     * */
    public function getProducts(){
        $solutionId = $this->getRequest()->getParam('id');
                
        /**
         * @maph65
         * Aqui va la lógica para obtener el ID de los productos que ya se encuentran seleccionados
         * Dentro de la solución actual
         * */
        
        $productsIds = array();
        if($solutionId){
            $model = Mage::getModel('coffeesolutions/solutions')->load($solutionId);
            $_pids = $model->getProducts();
            if(!empty($_pids) && is_string($_pids)){
                $ids = unserialize($_pids);
                if(is_array($ids)){
                    $productsIds = $ids;
                }
            }
        }
        return $productsIds;
    }
    
    /**
     * Métodos getGridUrl()
     * 
     * Devuelve la URL del grid de productos sin los campos ocultos del serializado,
     * por ello es importante definir otra URL con este grid distinta a la que carga inicialmente
     * el Tab y que contiene el objeto serializer
     * 
     * by @maph65
     * 
     * */
    
    public function getGridUrl(){
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/gridproductos', array('_current'=>true));
    }
    
        
    protected function _addColumnFilterToCollection($column){
        if ($column->getId() == 'products') {
            $productIds = $this->_getProducts();
            if (empty($productIds)) {
                $productIds = FALSE;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
   
}
