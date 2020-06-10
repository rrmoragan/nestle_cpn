<?php

/*
* @category    Entrepids
* @package     Entrepids_MassImage
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_MassImage_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getZipPath()
	{
		return "media/admin-config-uploads/".Mage::getStoreConfig('entrepids_massimage/general_settings/upload_file');
	}

	public function isDebugModeEnabled()
	{
		return Mage::getStoreConfig('entrepids_massimage/general_settings/debug_mode');
	}
}