<?php

/**
 * @category   Entrepids
 * @package    Entrepids_Maintenance
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_Maintenance_Helper_Data extends Mage_Core_Helper_Abstract {
    
    const COOKIE_NAME = 'entrepidsbypass';

    public function getConfig($field) {
        $value = Mage::getStoreConfig('maintenance/option/' . $field);
        if (!isset($value) or trim($value) == '') {
            return null;
        } else {
            return $value;
        }
    }

    public function isActive() {
        return $this->getConfig('active');
    }

    public function validateBypass($params = array()) {
        $realCode = $this->getConfig('code');
        $realValue = $this->getConfig('value');
        if(isset($params[$realCode]) && strcmp($params[$realCode],$realValue)==0){
            return true;
        }  else {
            return false;
        }
    }
    
    public function validateCookie(){
        $ckname = self::COOKIE_NAME;//$this->getConfig('code');
        $ckvalue = sha1($this->getConfig('value'));
        $ckCurrentValue = Mage::getModel('core/cookie')->get($ckname);
        if(!empty($ckCurrentValue) && $ckCurrentValue==$ckvalue){
            return true;
        }
        return false;
    }
    
    public function setCookie(){
        $cookieModel    = Mage::getModel( 'core/cookie' );
        $ckname         = self::COOKIE_NAME;//$this->getConfig('code');
        $ckvalue        = sha1($this->getConfig('value'));
        $cookieModel->set($ckname, $ckvalue, 3600);
    }

}
