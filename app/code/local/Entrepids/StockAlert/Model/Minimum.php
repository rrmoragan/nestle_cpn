<?php

/*
* @category    Entrepids
* @package     Entrepids_StockAlert
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_StockAlert_Model_Minimum extends Mage_Core_Model_Config_Data
{
	public function save()
    {
        if(!is_numeric($this->getValue()))
        {
            Mage::throwException(Mage::helper('adminhtml')->__("The minimum stock qty must be a numeric value"));
        }

        return parent::save();
    }
}	