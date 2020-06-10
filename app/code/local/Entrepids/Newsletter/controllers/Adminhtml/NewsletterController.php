<?php

 class Entrepids_Newsletter_Adminhtml_NewsletterController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Categories'))->_title($this->__('Entrepids Newsletter'));
        $this->loadLayout();
        //$this->_setActiveMenu('sales/sales');
        $this->_addContent($this->getLayout()->createBlock('entrepids_newsletter/adminhtml_newsletter_order'));
        $this->renderLayout();
    }
 
 
    
    public function newAction(){
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('entrepids_newsletter/adminhtml_form_edit'))
                                        ->_addLeft($this->getLayout()->createBlock('entrepids_newsletter/adminhtml_form_edit_tabs'));
        $this->renderLayout();
    }
    
    public function saveAction(){
        $params = $this->getRequest()->getParams();
        $idCatalog = null;
        if( isset($params['idCatalog'])){
           $idCatalog = $params['idCatalog'];
        }
        if($idCatalog  == null){
            $model = Mage::getModel('entrepids_newsletter/catalog');
            $model->setName($params['name']);
            $imageHelper = Mage::helper('entrepids_newsletter');
            if(isset($_FILES['image'])){
                $image = $imageHelper->uploadSolutionImage('image');
            }
            $model->setImage($image); 
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('entrepids_newsletter')->__('Categoría creada correctamente'));
            $this->_redirect('*/*/');
        }else{
            if($params['name'] != null){
                $model = Mage::getModel('entrepids_newsletter/catalog')->load($idCatalog);
                if($model->getName() != $params['name']){
                    $model->setName($params['name']);
                }
            }
            $imageHelper = Mage::helper('entrepids_newsletter');
            if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){
                $image = $imageHelper->uploadSolutionImage('image');
                $model->setImage($image);
                
            }
            $model->save(); 
        }
        
        $this->_redirect('*/*/');
    }
    public function deleteAction() {
        if( $this->getRequest()->getParam('idCatalog') > 0 ) {
            $idCatalog= $this->getRequest()->getParam('idCatalog');
            try {
                Mage::getModel("entrepids_newsletter/subscriptions")->deleteAllSubscriptionsByCatalogId($idCatalog);
                $model = Mage::getModel('entrepids_newsletter/catalog');
                $model->setId($idCatalog)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Categoría Eliminada'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('idCatalog' => $idCatalog));
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function editAction() {
        $id = $this->getRequest()->getParam('idCatalog');
        $model = Mage::getModel('entrepids_newsletter/catalog')->load($id);
        if ($model->getId() || $id == 0) {
            
            $this->loadLayout();
            $this->_setActiveMenu('entrepids_newsletter/items');
            $this->_addContent($this->getLayout()->createBlock('entrepids_newsletter/adminhtml_form_edit'))
            ->_addLeft($this->getLayout()
            ->createBlock('entrepids_newsletter/adminhtml_form_edit_tabs'));
            $this->renderLayout();
            } else {        
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('entrepids_newsletter')->__('Item does not exist'));
                $this->_redirect('*/*/');
            }
        }
        
    public function subscribersAction(){
        $this->_title($this->__('Suscripciones'))->_title($this->__('Entrepids Newsletter'));
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('entrepids_newsletter/adminhtml_subscribers_order'));
        $this->renderLayout();
    }
    
     public function deleteSubscriberAction() {
        if( $this->getRequest()->getParam('idUser') > 0 && $this->getRequest()->getParam('idCatalog') > 0 ) {
            $idUser= $this->getRequest()->getParam('idUser');
            $idCatalog= $this->getRequest()->getParam('idCatalog');
            try {
                Mage::getModel("entrepids_newsletter/subscriptions")->deleteUserSubscription($idUser, $idCatalog);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Suscripción Eliminada Eliminada'));
                $this->_redirect('*/*/subscribers/id/'.$idCatalog);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/subscribers/id/'.$idCatalog);
            }
        }
        $this->_redirect('*/*/subscribers/id/'.$idCatalog);
    }
    
    public function massDeleteAction() {
        $requestIds = $this->getRequest()->getParam('entity_id');
        $idCatalog= $this->getRequest()->getParam('idCatalog');
        
        if(!is_array($requestIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select reqeust(s)'));
        } else {
            try {
                Mage::getModel("entrepids_newsletter/subscriptions")->massiveDelete($requestIds, $idCatalog);   
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($requestIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/subscribers/id/'.$idCatalog);
    }
}
 