<?php
class Entrepids_Newsletter_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function uploadSolutionImage($fileVar) {
        $path = Mage::getBaseDir('media') . DS . 'newsletter';
        $image = "";
        if (isset($_FILES[$fileVar]['name']) && $_FILES[$fileVar]['name'] != '') {
            try {
                /* Starting upload */
                $uploader = new Varien_File_Uploader($fileVar);

                // Any extention would work
                $uploader->setAllowedExtensions(array(
                    'jpg', 'jpeg', 'gif', 'png'
                ));
                $uploader->setAllowRenameFiles(true);

                $uploader->setFilesDispersion(true);

                $uploader->save($path, $uploader->getCorrectFileName($_FILES[$fileVar]['name']));
                $image = $uploader->getUploadedFileName();
            } catch (Exception $e) {
                Mage::getSingleton('customer/session')->addError($e->getMessage());
            }
        }
        return $image; //Url de la imagen en Magento
    }
}
