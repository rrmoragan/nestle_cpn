<?php
/**
* @category   Entrepids
* @package    Entrepids_ScheduleAppointment
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_ScheduleAppointment_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action{
    private $path = '';
    private $fname = 'catalog_postal_codes.csv';

    public function setPath(){
        $this->path = Mage::getBaseDir().DS.'var'.DS.'importexport'.DS;
    }

    private function uploadFile($file){
        if(isset($file['import_postal_codes']['name']) && $file['import_postal_codes']['name'] != ''){
            $this->setPath();
            try{                      
                $uploader = new Varien_File_Uploader('import_postal_codes');
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowCreateFolders(true);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $uploader->save($this->path,$this->fname);
            }catch (Exception $e){
                throw new Exception($e, 1);
            }
        }
    }
    
    private function loadCatalog(){
        $this->setPath();
        try{
            $openStream = fopen($this->path.$this->fname, 'r');
            $temp = array();
            $count = 0;
            if ($openStream !== FALSE) {
                while ($content = fgetcsv($openStream, 10, ',')) {
                    if(sizeof($content)){
                        $cp = $content[0];
                        if(strlen($cp) == 5){
                            $temp[] = array('postal_code' => $cp);
                        }
                    }
                }
            }

            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $table = $resource->getTableName('scheduleappointment/cpscheduleappointment');
            
            foreach ($temp as $in) {
                try{
                    $writeConnection->insertMultiple($table,$in);
                    $count++;
                }catch(Exception $e){
                    Mage::log("El codigo postal: ".$in['postal_code']." ya existe",null,'import_postal_codes.log' );
                }
            }
            $writeConnection->closeConnection();
            return $count;
        }catch(Exception $e){
            throw new Exception($e, 1);
        }
    }

    public function saveAction(){
        try{
            $this->uploadFile($_FILES);
            $total = $this->loadCatalog();

            if($total == 0){
                Mage::getSingleton('core/session')->addNotice('No se agrego ningun codigo postal');
                Mage::getSingleton('core/session')->addNotice('-Los codigos postales deben tener 5 numeros');
                Mage::getSingleton('core/session')->addNotice('-Comprueba que los codigos postales no existan ya en la lista');
            }else if($total == 1){
                Mage::getSingleton('core/session')->addSuccess('Se agrego un nuevo codigo postal'); 
            }else{
                Mage::getSingleton('core/session')->addSuccess("Se agregaron $total codigos postales nuevos"); 
            }
            
            $this->_redirect('*/postalcode/index');
        }catch(Exception $e){
            Mage::getSingleton('core/session')->addError($e->getMessage()); 
            Mage::log("Error to load postal codes : Solo suba archivos '.csv'. Los registros deben ir separador por comas (,).",null,'import_postal_codes.log' );
            $this->_redirectReferer();
        }
    }
}