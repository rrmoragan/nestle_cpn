<?php

/*
* @category    Entrepids
* @package     Entrepids_StockAlert
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_StockAlert_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isEnabled()
	{
		return Mage::getStoreConfig('entrepids_stockalert/settings/enabled');
	}

	public function getEmails()
	{
		return Mage::getStoreConfig('entrepids_stockalert/settings/emails');
	}

}