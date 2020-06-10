<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Adminhtml_SolutionsController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        if(Mage::registry('coffee_solution')){
            Mage::unregister('coffee_solution');
        }
        $helper = Mage::helper('coffeesolutions');
        if ($helper->getCoffeeSolutionRootCategorey()) {
            $this->_title($this->__('Soluciones de Café'))->_title($this->__('Soluciones de Café'));
            $this->loadLayout();
            $this->_setActiveMenu('catalog/coffeesolutions');
            $this->_addContent($this->getLayout()->createBlock('coffeesolutions/adminhtml_solutions'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addNotice('Debes configurar primero la categoría principal de las Soluciones de Café');
            $url = Mage::getModel('adminhtml/url')->getUrl('adminhtml/system_config/edit', array('_secure' => true, 'section' => 'coffeesolution'));
            $this->_redirectUrl($url);
            return;
        }
    }

    
    public function newAction() {
        if(Mage::registry('coffee_solution')){
            Mage::unregister('coffee_solution');
        }
        $this->_forward('edit');
    }

    public function editAction() {
        $procesar = TRUE;
        $idEdit = (int)$this->getRequest()->getParam('id');
        if($idEdit){
            $this->_title($this->__('Editar Solución de Café'))->_title($this->__('Editar Solución de Café'));
            $solution = Mage::getModel('coffeesolutions/solutions')->load($idEdit);
            if($solution->getId()){
                Mage::register('coffee_solution', $solution);
            }else{
                $procesar = FALSE;
                Mage::getSingleton('adminhtml/session')->addError($this->__('No se encotró la solución de Café especificada.'));
                $this->_redirect('*/*/index');
            }
        }else{
            $this->_title($this->__('Nueva Solución de Café'))->_title($this->__('Nueva Solución de Café'));
        }
        if($procesar){
            $this->loadLayout();
            $this->_setActiveMenu('catalog/coffeesolutions');
            $this->_addContent($this->getLayout()->createBlock('coffeesolutions/adminhtml_solutions_edit'))
                    ->_addLeft($this->getLayout()->createBlock('coffeesolutions/adminhtml_solutions_edit_tabs'));
            $this->renderLayout();
        }else{
            $this->_redirect('*/*/index');
        }
    }
    
    /**
     *Layout para obtener la vista de los productos estableciendo los productos pre-existentes
     */
    public function productosAction(){
        $this->loadLayout();
        $this->getLayout()->getBlock('edit.coffeesolutions.productos')
                ->setProducts($this->getRequest()->getPost('products', null));
        $this->renderLayout();
    }

    /**
     * Este método carga el Layout del Grid sin el objeto Serializer
     * Necesario para cuando se utilizan filtros y es necesario obtener
     * via Ajax nuevamente el contenido del grid ya filtrado
     * **/
    public function gridproductosAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function saveAction(){
        $solutionId = (int)$this->getRequest()->getParam('solution_id');
        $productsIds = $this->getRequest()->getParam('product_ids', null);
        
        if ($productsIds) {
            $productsIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($productsIds);
        }else{
            $productsIds = array();
        }
        $imageHelper = Mage::helper('coffeesolutions/uploadImages');
        $model = Mage::getModel('coffeesolutions/solutions');
        $setProducts = serialize($productsIds);

        if($solutionId){
            $model = $model->load($solutionId);
            if(empty($model->getId())){
                Mage::getSingleton('adminhtml/session')->addWarning($this->__('No se encotró la solución con ID '.$solutionId.'. Se proceso como una nuev solución de café.'));
                $model = Mage::getModel('coffeesolutions/solutions');
            }else{
                if(!$productsIds){
                    $setProducts = $model->getProducts();
                }
            }
        }
        try{
            if(isset($_FILES['solution_image']['name'])){
                $image = $imageHelper->uploadSolutionImage('solution_image');
            }else{
                $image = $model->getSolutionImage();
            }
            if(empty($image)){
                $image = $model->getSolutionImage();
            }
            
            $data = array(
                'solution_name' => addslashes(strip_tags($this->getRequest()->getPost('solution_name'))),
                'solution_image' =>  $image,
                'min_coups_available' => (int)$this->getRequest()->getPost('min_coups'),
                'max_coups_available' => (int)$this->getRequest()->getPost('max_coups'),
                'available' => $this->getRequest()->getPost('solution_active') ? 1 : 0,
                'products' => $setProducts,
                'calification' => (int) $this->getRequest()->getPost('calification'),
                'description' => $this->getRequest()->getPost('description'),
                'costbycoup' => $this->getRequest()->getPost('costbycoup'),
                'preparation' => $this->getRequest()->getPost('preparation'),
                'beveragetype' => $this->getRequest()->getPost('beveragetype'),
                'varieties' => $this->getRequest()->getPost('varieties'),
            );
            if($solutionId){
                $data['entity_id'] = $solutionId;
                
            }
            $model->setData($data);
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Se ha guardado la solución de Café exitosamente.'));
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($this->__('No se ha podido guardar la solución de Café. '.$e->getMessage()));
        }
        $this->_redirect('*/*/index');
        
    }
    
    
    
    /**
     *Layout para obtener la vista de las soluciones estableciendo los productos pre-existentes
     */
    public function solutionsAction(){
        $this->loadLayout();
        $this->getLayout()->getBlock('edit.coffeesolutions.soluciones')
                ->setSolutions($this->getRequest()->getPost('solutions', null));
        $this->renderLayout();
    }

    /**
     * Este método carga el Layout del Grid sin el objeto Serializer
     * Necesario para cuando se utilizan filtros y es necesario obtener
     * via Ajax nuevamente el contenido del grid ya filtrado
     * **/
    public function gridsolutionsAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function deleteAction(){
        $solutionId = (int)$this->getRequest()->getParam('id');
        if($solutionId){
            $solution = Mage::getModel('coffeesolutions/solutions')->load($solutionId);
            if($solution->getId()){
                try{
                    $resource = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
                    $csplaces = $resource->getTableName('entrepids_coffee_solutions_places');
                    $query = 'DELETE FROM '.$csplaces.' WHERE solution_id = '.$solutionId;
                    $writeConnection->query($query);
                    $solution->delete();
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Se ha eliminado la solución de café.'));
                }catch(Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Ocurrió un error inesperado. Intente más tarde. '.$e->getMessage()));
                }
            }else{
                Mage::getSingleton('adminhtml/session')->addError($this->__('No se ha encontrado la solución de café especificada.'));
            }
        }else{
            Mage::getSingleton('adminhtml/session')->addError($this->__('No se ha encontrado la solución de café especificada.'));
        }
        $this->_redirect('*/*/index');
    }
    
    
    
}
