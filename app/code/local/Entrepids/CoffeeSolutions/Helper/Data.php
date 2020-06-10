<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Helper_Data extends Mage_Core_Helper_Abstract {

    const COFFEE_SOLUTIONS_PATH = 'coffeesolution/solution/category';
    
    public function getCoffeeSolutionRootCategorey(){
        return Mage::getStoreConfig(self::COFFEE_SOLUTIONS_PATH, Mage::app()->getStore());
    }

    /**
     * $imageName    name of the image file
     * $width        resize width
     * $height        resize height
     * $imagePath    the path where the image is saved. 
     * return        full url path of the image
     */
    public function resizeImage($imageName, $imagePath, $width = NULL, $height = NULL) {
        $imagePath = str_replace("/", DS, $imagePath);
        $imagePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $imageName;
        if ($width == NULL && $height == NULL) {
            $width = 100;
            $height = 100;
        }
        $resizePath = $width . 'x' . $height;
        $resizePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $resizePath . DS . $imageName;
        if (file_exists($imagePathFull) && !file_exists($resizePathFull)) {
            $imageObj = new Varien_Image($imagePathFull);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepFrame(TRUE);
            $imageObj->keepTransparency(TRUE);
            $imageObj->constrainOnly(TRUE);
            //$imageObj->backgroundColor(array(255, 255, 255));
            $imageObj->quality(80);
            $imageObj->resize($width, $height);
            $imageObj->save($resizePathFull);
        }
        $imagePath = str_replace(DS, "/", $imagePath);
        return Mage::getBaseUrl("media") . $imagePath . "/" . $resizePath . "/" . $imageName;
    }

}
