<?php
/**
* @category   Entrepids
* @package    Entrepids_CoffeSolutions
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_CoffeeSolutions_Model_Observer{
    
    public function saveCategoryCoffeeSolution($observer){
        $solutions = Mage::app()->getRequest()->getParam('solutions_ids',null);
        $category = $observer->getEvent()->getCategory();
        if($solutions && $category->getId()){
            $solutionsIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($solutions);
            //$debug = print_r($solutionsIds,true);
            //Mage::log('Category: '.$category->getId(),null,'cs_category.log');
            //Mage::log($debug,null,'cs_category.log');
            
            try{
            //Eliminamos Soluciones anteriores
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $tableName = $resource->getTableName('coffeesolutions/solutionPlaces');
                $query = "DELETE FROM {$tableName} WHERE category_id = ". (int)$category->getId();
                $writeConnection->query($query);
                
                $model = Mage::getModel('coffeesolutions/solutionPlaces');
                if(!empty($solutionsIds)){
                    foreach ($solutionsIds as $solution => $dataSolution){
                        $data = array(
                            'category_id' => $category->getId(), 
                            'solution_id' => $solution);
                        if(isset($dataSolution['position'])){
                            $data['prioridad'] = (int)$dataSolution['position'];
                        }
                        
                        $model->setData($data);
                        $model->save();
                    }
                }
            }catch(Exception $e){
                //Mage::log('Error al guardar la categoria '.$category->getId().'. '.$e->getMessage(),null,'cs_category.log');
            }
        }
    }
    
    public function setSolutionQuoteItem($observer) {
        $quoteItem = $observer->getQuoteItem();
        $data = Mage::app()->getFrontController()->getRequest()->getParams();

        if (!$quoteItem->getItemId() && $data['idsolucion'] > 0) {
            $quoteItem->setIdSolution($data['idsolucion']);
        }
    }
}