<?php

/**
 * Inventory pull Task
 *
 * @author Fernando Rodriguez
 * @since 2018-10-18
 */
class Aoe_Scheduler_Model_Task_Inventory
{

    /**
     * Run
     *
     * @param Aoe_Scheduler_Model_Task_Inventory $schedule
     * @return string
     * @throws Exception
     */
    public function run(Aoe_Scheduler_Model_Schedule $schedule){
        $collection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('sku') // select all attributes
		->addAttributeToFilter('status', 1); // add status filter
		//->setPageSize(5000) // limit number of results returned
		//->setCurPage(1); // set the offset (useful for pagination)
		foreach($collection as $prod) {
			$product = Mage::getModel('catalog/product')->load($prod->getId());
			/*echo '<pre>';
			print_r($product);
			echo '</pre>';*/
			//var_dump($product->getSku());
			echo $product->getSku()."<br/>";
		}
		die('run');
    }
}
