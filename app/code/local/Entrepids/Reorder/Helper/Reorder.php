<?php


class Entrepids_Reorder_Helper_Reorder extends Mage_Sales_Helper_Reorder{
    public function canReorderCustom(Mage_Sales_Model_Order $order)
    {
        foreach ($order->getItemsCollection() as $item){
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
            if($stock->getQty() > 0){
                return true;
            }   
        }
        return false;
    }
}