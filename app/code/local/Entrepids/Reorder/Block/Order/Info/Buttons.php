<?php

class Entrepids_Reorder_Block_Order_Info_Buttons extends Mage_Sales_Block_Order_Info_Buttons{
    public function getReorderUrl($order)
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this->getUrl('sales/guest/reorder', array('order_id' => $order->getId()));
        }
        return $this->getUrl('*/*/numberOfReorders/', array('order_id' => $order->getId()));
    }
}