<?php

class Entrepids_Newsletter_Block_Customer_Newsletter extends Mage_Customer_Block_Newsletter{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('entrepids/newsletter/newsletter.phtml');
    }
}