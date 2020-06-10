<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Helper_UploadImages extends Mage_Core_Helper_Abstract {

    public function uploadSolutionImage($fileVar) {
        $path = Mage::getBaseDir('media') . DS . 'solutions';
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
                $image = $uploader->getUploadedFileName();//substr(strrchr($uploader->getUploadedFileName(), "/"), 1);
            } catch (Exception $e) {
                Mage::getSingleton('customer/session')->addError($e->getMessage());
            }
        }
        return $image;
    }

    public function deleteImageFile($image) {
        if (!$image) {
            return;
        }
        try {
            $img_path = Mage::getBaseDir('media') . "/" . $image;
            if (!file_exists($img_path)) {
                return;
            }
            unlink($img_path);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

}
