<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Helper_ProductLines extends Mage_Core_Helper_Abstract {
    
    const LINE_DOLCE_GUSTO = 'dolce_gusto';
    const LINE_NESCAFE = 'nescafe';
    const LINE_NESCAFE_TASTER_CHOICE = 'nescafe_taster_choice';
    const LINE_NESCAFE_PROFESSIONAL = 'nescafe_professional';
    
    public function getProductLines(){
        return array(
            self::LINE_DOLCE_GUSTO => 'Dolce Gusto',
            self::LINE_NESCAFE => 'Nescafé',
            self::LINE_NESCAFE_TASTER_CHOICE => 'Nescafé Taster Choice',
            self::LINE_NESCAFE_PROFESSIONAL => 'Nescafé Professional'
        );
        
    }
    
}

    
