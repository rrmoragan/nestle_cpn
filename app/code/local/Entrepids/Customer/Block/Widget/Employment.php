<?php
/**
* @category   Entrepids
* @package    Entrepids_Customer
* @author     fabian.perez@entrepids.com
* @website    http://www.entrepids.com
*/

require_once 'Dropdown.php';

class Entrepids_Customer_Block_Widget_Employment extends Entrepids_Customer_Block_Widget_Dropdown
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('customer/widget/employment.phtml');
    }
}
