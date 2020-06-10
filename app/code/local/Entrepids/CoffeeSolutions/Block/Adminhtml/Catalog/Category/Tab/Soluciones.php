<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */

class Entrepids_CoffeeSolutions_Block_Adminhtml_Catalog_Category_Tab_Soluciones extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('soluciones_grid');
        $this->setUseAjax(true); // Se obliga para cargar en Ajax este Layout
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(false);
        if(!empty($this->getSolutions())){
            $this->setDefaultFilter(array('solutions'=>1));
        }
    }

    /*protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }*/

    
    /**
     * Prepara la colección nativa de Magento
     * 
     * **/
    protected function _prepareCollection() {
        $collection = Mage::getModel('coffeesolutions/solutions')
                ->getCollection()
                ->addFieldToSelect('*');
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

        $this->addColumn('solutions', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'solutions',
            'values'            => $this->_getSolutions(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));
        
        
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('solution_name', array(
            'header'    => Mage::helper('catalog')->__('Nombre de la Solución'),
            'index'     => 'solution_name'
        ));

        $this->addColumn('min_coups_available', array(
            'header'    => Mage::helper('catalog')->__('Número mínimo de Tazas'),
            'index'     => 'min_coups_available'
        ));

        $this->addColumn('max_coups_available', array(
            'header'    => Mage::helper('catalog')->__('Número máximo de Tazas'),
            'index'     => 'max_coups_available'
        ));

        $this->addColumn('available', array(
            'header'    => Mage::helper('catalog')->__('Disponible'),
            'width'     => 90,
            'index'     => 'available',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('position', array(
            'header'            => Mage::helper('coffeesolutions')->__('Prioridad'),
            'name'              => 'position',
            'width'             => 60,
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'editable'          => true,
            'edit_only'         => true
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
    public function _getSolutions(){
        return array_keys($this->getSolutions());
    }
    
    /**
     * Métodos getSolutions()
     * 
     * Obtiene la información de las soluciones ya seteados en el Grid
     * Este método debe devolver un array asociativo por ID cuando se tiene información
     * en campos adicionales dentro del Grid de productos
     * @return int[]
     * 
     * by @maph65
     * */
    public function getSolutions(){
        $categoryId = (int)$this->getRequest()->getParam('id');
                
        /**
         * @maph65
         * Aqui va la lógica para obtener el ID de las soluiocnes que ya se encuentran seleccionados
         * Dentro de la solución actual
         * */
        
        $solutionsIds = array();
        if($categoryId){
            $solutions = Mage::getModel('coffeesolutions/solutionPlaces')
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('category_id', array('eq' => $categoryId));
            if(!empty($solutions)){
                foreach ($solutions as $_s){
                    $solutionsIds[$_s->getSolutionId()] = array('position' => $_s->getPrioridad());
                }
            }
        }
        $debug = print_r($solutionsIds,true);
        //Mage::log('Soluciones: '.$debug,null,'cs_category.log');
        return $solutionsIds;
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
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('coffeesolutions/adminhtml_solutions/gridsolutions', array('_current'=>true));
    }
    
        
    protected function _addColumnFilterToCollection($column){
        if ($column->getId() == 'solutions') {
            $solutionsIds = $this->_getSolutions();
            if (empty($solutionsIds)) {
                $solutionsIds = FALSE;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$solutionsIds));
            } else {
                if($solutionsIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$solutionsIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
   
}
