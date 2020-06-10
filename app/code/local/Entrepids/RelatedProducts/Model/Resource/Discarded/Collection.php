<?php

/*
* @category    Entrepids
* @package     Entrepids_RelatedProducts
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_RelatedProducts_Model_Resource_Discarded_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct() 
	{
		$this->_init('relatedproducts/discarded');
	}
}