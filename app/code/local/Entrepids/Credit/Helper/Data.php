<?php

/*
* @category    Entrepids
* @package     Entrepids_Credit
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_Credit_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isEnabled()
	{
		return Mage::getStoreConfig('entrepids_credit/settings/enabled');
	}


	public function getMinimumAmount()
	{
		return Mage::getStoreConfig('entrepids_credit/settings/minimum_amount');
	}

	public function getEmails()
	{
		return Mage::getStoreConfig('entrepids_credit/settings/emails');
	}

	public function getCreditDocPath()
	{
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true)."media/nestle-credit-doc/".Mage::getStoreConfig('entrepids_credit/settings/upload_file');
	}

	public function getTotalCustomerPurchase($customerId)
	{
		$orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addAttributeToFilter('customer_id', $customerId);
		$grandTotal = 0;

		foreach($orders as $order)
		{
		    $grandTotal+=$order->getGrandTotal();
		}

		return $grandTotal;
	}
}