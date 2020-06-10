<?php
/**
* @category   Entrepids
* @package    Entrepids_Cronscheduler
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/
class Entrepids_Cronscheduler_Block_Adminhtml_Hiddenjobs extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Constructor for Job Adminhtml Block
     */
    public function __construct()
    {
        $this->_blockGroup = 'cronscheduler';
        $this->_controller = 'adminhtml_hiddenjobs';
        $this->_headerText = $this->__('All Available Jobs (included System Jobs)');
        parent::__construct();
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->removeButton('add');
        return parent::_prepareLayout();
    }


    /**
     * Returns the CSS class for the header
     *
     * Usually 'icon-head' and a more precise class is returned. We return
     * only an empty string to avoid spacing on the left of the header as we
     * don't have an icon.
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return '';
    }
}
