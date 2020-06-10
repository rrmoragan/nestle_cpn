<?php

/*
* @category    Entrepids
* @package     Entrepids_Credit
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_Credit_Model_Amount extends Mage_Core_Model_Config_Data
{
	public function save()
    {
        if(!is_numeric($this->getValue()))
        {
            Mage::throwException(Mage::helper('adminhtml')->__("The minimum amount must be a numeric value"));
        }

        return parent::save();
    }
}	