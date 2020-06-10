<?php
/**
* @category   Entrepids
* @package    Entrepids_Nestle
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Nestle_Helper_Prices extends Mage_Core_Helper_Abstract{
    
    public function formatPriceSmallCents($price,$separator = '.'){
        $priceWithoutTags = strip_tags($price);
        $priceTags = $price;
        
        if(strpos($priceWithoutTags, $separator)!==FALSE){
            $priceArray = explode($separator,$priceWithoutTags);
            $price = str_replace('$', ' ', $priceArray[0]);
            unset($priceArray[0]);
            if(!empty($priceArray)){
                foreach ($priceArray as $cents){
                    $price .= '.<sup>'.$cents.'</sup>';
                }
            }
            $price = str_replace($priceWithoutTags, $price, $priceTags);
        }
        return $price;
    }
    
}