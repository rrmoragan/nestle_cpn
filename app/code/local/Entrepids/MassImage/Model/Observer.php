<?php

/*
* @category    Entrepids
* @package     Entrepids_MassImage
* @author      Francisco Espinosa <francisco.espinosa@entrepids.com>
* @copyright   Copyright (c) 2018 Entrepids México S. de R.L de C.V
* @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Entrepids_MassImage_Model_Observer
{

	public function uploadImageBySku(Varien_Event_Observer $observer)
	{

		//Get file uploaded to server
		$zip = new ZipArchive;
		$res = $zip->open(Mage::helper('massimage')->getZipPath());
		$path = "media/admin-config-uploads/imageupload/";


		if(Mage::helper('massimage')->getZipPath()=="")
		{
			if(Mage::helper('massimage')->isDebugModeEnabled())
			{
				Mage::log(Mage::helper('adminhtml')->__("There is no zip file to load or has been successfully removed from the configuration"), null, 'massImages.log');
			}

			$this->cleanDir();

			Mage::getSingleton('core/session')->addSuccess(Mage::helper('adminhtml')->__("There is no zip file to load or has been successfully removed from the configuration"));
		}

		else
		{
			$allowedImagesFormat = array("jpeg", "jpg", "png", "gif");
			$skus = array();

			//Flags
			$imagesNotProcessed = false;
			$isOtherImage 		= false;
			$item 				= 0;

			//If file has zip format
			if ($res === true) 
			{
				if(Mage::helper('massimage')->isDebugModeEnabled())
				{
					Mage::log(Mage::helper('adminhtml')->__("Uncompressing zip file..."), null, 'massImages.log');
				}
				
				//Decompress zip file
			  	$zip->extractTo($path);
			  	$zip->close();
			  	
			  	$images  = scandir($path);

			  	//Scan all files for decompressed folder
			  	foreach ($images as $image) 
			  	{
			  		$isOtherImage = false;
			  		$item++;

			  		if(Mage::helper("massimage")->isDebugModeEnabled())
					{
			  			Mage::log("Archivo: ".$image, null, 'massImages.log');
			  		}

			  		$imageFullName = explode(".", $image);
			  		if(in_array($imageFullName[1], $allowedImagesFormat))
			  		{
			  			$productName = $imageFullName[0];

			  			$secondaryImage = explode("_", $productName);

			  			if(count($secondaryImage)>1)
			  			{
			  				$isOtherImage = true;
			  			}

			  			$productSku = $secondaryImage[0];

			  			if(Mage::helper("massimage")->isDebugModeEnabled())
						{
				  			Mage::log("Archivo válido", null, 'massImages.log');
				  			Mage::log("SKU actual: ".$productSku, null, 'massImages.log');

				  			if($isOtherImage)
				  			{
				  				Mage::log(Mage::helper('adminhtml')->__("Secondary image detected!"), null, 'massImages.log');
				  			}
				  		}
			  			$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$productSku);

						if (file_exists($path.$image)) 
						{
							if($product!=null)
							{

								//SKU Main image
								if(!in_array($productSku, $skus))
								{
									//Delete images
								    $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
								    $items = $mediaApi->items($product->getId());
								    foreach($items as $item)
								    {
								    	$mediaApi->remove($product->getId(), $item['file']);
								    }

								    if(Mage::helper("massimage")->isDebugModeEnabled())
									{
								    	Mage::log(Mage::helper('adminhtml')->__("Previous images from current product deleted successfully"), null, 'massImages.log');
								    }

								    //Delete all images will cause that Magento automatically disable the current product
								    //So, we'll enable again.
								    Mage::getModel('catalog/product_status')->updateProductStatus($product->getId(), 0, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

								    //Add the new image
									$product->addImageToMediaGallery($path.$image, array('image', 'small_image', 'thumbnail'), false, false);
							    	$product->save();

							    	array_push($skus, $productSku);
								}

								//Other images
								else
								{
									//Delete all images will cause that Magento automatically disable the current product
								    //So, we'll enable again.
								    Mage::getModel('catalog/product_status')->updateProductStatus($product->getId(), 0, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

									//Add the new image
									$product->addImageToMediaGallery($path.$image, false, false, false);
							    	$product->save();
								}


							    if(Mage::helper("massimage")->isDebugModeEnabled())
								{
							    	Mage::log(Mage::helper('adminhtml')->__("New image added successfully"), null, 'massImages.log');
							    }
							}

							else
							{
								$imagesNotProcessed = true;

								if(Mage::helper("massimage")->isDebugModeEnabled())
								{
									Mage::log(Mage::helper('adminhtml')->__("SKU don't exists...Skip"), null, 'massImages.log');
								}
							}
						    
						}

						else
						{
							$imagesNotProcessed = true;

							if(Mage::helper("massimage")->isDebugModeEnabled())
							{
								Mage::log(Mage::helper('adminhtml')->__("There was an error finding image path...Skip"), null, 'massImages.log');
							}
						}
					}

					else 
					{
						if($item>2){
							$imagesNotProcessed = true;
							if(Mage::helper("massimage")->isDebugModeEnabled()){
					    			Mage::log(Mage::helper('adminhtml')->__("Invalid image format...Skip"), null, 'massImages.log');
					    		}
						}	
					}
					Mage::log("------------------", null, 'massImages.log');   
			 		  		
			  	}

			} 

			else 
			{
				$imagesNotProcessed = true; 

				if(Mage::helper("massimage")->isDebugModeEnabled())
				{
			  		Mage::log(Mage::helper('adminhtml')->__("The selected file is not a zip file, it's corrupted or doesn't exist"), null, 'massImages.log');
			  		$this->cleanDir();
			  		Mage::throwException(Mage::helper('adminhtml')->__("The selected file is not a zip file, it's corrupted or doesn't exist"));
			  	}
			}

			if($imagesNotProcessed)
		  	{
		  		$this->cleanDir();
		  		Mage::getSingleton('core/session')->addWarning(Mage::helper('adminhtml')->__("The images were associated with the products correctly, however once again they were found, because the format of the image is invalid or the name does not correspond to any existing SKU"));
		  	}

		  	else
		  	{
		  		$this->cleanDir();
		  		Mage::getSingleton('core/session')->addSuccess(Mage::helper('adminhtml')->__('All images were asociated to products successfully'));
		  	}

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
		//Delete zip file
		if(Mage::helper("massimage")->isDebugModeEnabled())
		{
	  		Mage::log(Mage::helper('adminhtml')->__('Start folder cleaning...'), null, 'massImages.log');
	  	}

	  	try
	  	{
	  		$this->rmdir_recursive("media/admin-config-uploads/default/");

	  		if(Mage::helper("massimage")->isDebugModeEnabled())
			{
	  			Mage::log(Mage::helper('adminhtml')->__("Upload folder cleaned successfully"), null, 'massImages.log');
	  		}
	  	}

	  	catch(Exception $e)
	  	{
	  		if(Mage::helper("massimage")->isDebugModeEnabled())
			{
	  			Mage::log(Mage::helper('adminhtml')->__("There was a problem deleting upload folder"), null, 'massImages.log');
	  		}
	  	}


	  	//Delete image dir
	  	try
	  	{
	  		$this->rmdir_recursive("media/admin-config-uploads/imageupload/");

	  		if(Mage::helper("massimage")->isDebugModeEnabled())
			{
	  			Mage::log(Mage::helper('adminhtml')->__("Image folder cleaned successfully"), null, 'massImages.log');
	  		}
	  	}

	  	catch(Exception $e)
	  	{
	  		if(Mage::helper("massimage")->isDebugModeEnabled())
			{
	  			Mage::log(Mage::helper('adminhtml')->__("There was a problem deleting image folder"), null, 'massImages.log');
	  		}
	  	}

	}
}
		

