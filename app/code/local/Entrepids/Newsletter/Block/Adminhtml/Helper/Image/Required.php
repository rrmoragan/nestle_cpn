<?php
class Entrepids_Newsletter_Block_Adminhtml_Helper_Image_Required extends Varien_Data_Form_Element_Image{
    protected function _getDeleteCheckbox() 
    {
        return '';
    }
    
    public function getElementHtml()
    {
        $html = '';

        if ((string)$this->getValue()) {
            $url = $this->_getUrl();

            if( !preg_match("/^http\:\/\/|https\:\/\//", $url) ) {
                $url = Mage::getBaseUrl('media') . $url;
            }

            $html = '<a href="' . $url . '"'
                . ' onclick="imagePreview(\'' . $this->getHtmlId() . '_image\'); return false;">'
                . '<img src="' . $url . '" id="' . $this->getHtmlId() . '_image" title="' . $this->getValue() . '"'
                . ' alt="' . $this->getValue() . '" height="150" width="150" class="small-image-preview v-middle" />'
                . '</a> ';
        }
        $this->setClass('input-file');

        return $html;
    }
} 