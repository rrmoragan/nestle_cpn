<?php

/*
* @category    Entrepids
* @package     Entrepids_Credit
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_Credit_Model_Observer
{

	function uploadDocument(Varien_Event_Observer $observer)
	{
		//var_dump($observer); die();
		$file = Mage::getStoreConfig('entrepids_credit/settings/upload_file');
		$extension = explode(".", $file);

		//var_dump($extension); die();
		$allowed = array("pdf", "PDF", "DOC", "doc", "docx", "DOCX");

		if(!in_array($extension[1], $allowed))
		{
			$this->cleanDir();
			Mage::throwException("Invalid document format");
			
		}
	}

	function rmdir_recursive($dir) 
	{
	    foreach(scandir($dir) as $file) 
	    {
	        if ('.' === $file || '..' === $file) continue;
	        if (is_dir("$dir/$file")) $this->rmdir_recursive("$dir/$file");
	        else unlink("$dir/$file");
	    }

	    rmdir($dir);
	}


	function cleanDir()
	{
	
	  	try
	  	{
	  		$this->rmdir_recursive("media/nestle-credit-doc/default/");
	  	}

	  	catch(Exception $e)
	  	{
	  		Mage::throwException("Error while cleaning dir");
	  	}

	}
}
		

