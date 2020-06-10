<?php
  /**
   * file: app/code/local/Mlg/OpenpayReport/Block/Report/Product/Opyreport.php
   */

  echo '<tt>Mlg_OpenpayReport_Block_Report_Product_Opyreport</tt>';

  class Mlg_OpenpayReport_Block_Report_Product_Opyreport extends Mage_Adminhtml_Block_Widget_Grid_Container
  {
      public function __construct()
      {
          $this->_controller = 'report_product_opyreport';
          $this->_blockGroup = 'mlg_openpayreport';
          $this->_headerText = Mage::helper('mlg_openpayreport')->__('MLG Openpay Reportes');
          parent::__construct();
          $this->_removeButton('add');
      }
  }
  ?>
