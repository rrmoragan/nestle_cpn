<?php
/**
* @category   Entrepids
* @package    Entrepids_Cronscheduler
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Cronscheduler_IndexController extends Mage_Core_Controller_Front_Action{
    
    public function indexAction(){
       echo '<pre>';
       $cronJobs = Mage::getModel('cronscheduler/job')->getCollection()->getOnlyVisibleJobs();
       if(!empty($cronJobs)){
           foreach($cronJobs as $j){
               print_r($j);
           }
       }
       echo '</pre>';
    }
}