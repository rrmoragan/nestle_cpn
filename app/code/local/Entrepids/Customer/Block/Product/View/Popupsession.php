<?php
class Entrepids_Customer_Block_Product_View_Popupsession extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/view/popupsession.phtml');
    }
}