<?php
  /**
   * file: magento_root/app/code/local/Mlg/OpenpayReport/controllers/Adminhtml/OpenpayReportController.php
   */

  require_once(Mage::getModuleDir('controllers','Mage_Adminhtml') . DS . 'Report' . DS . 'ProductController.php');

  echo '<tt>Mlg_OpenpayReport_Adminhtml_OpenpayReportController</tt>';

  class Mlg_OpenpayReport_Adminhtml_OpenpayReportController extends Mage_Adminhtml_Report_ProductController
  {
      public function indexAction()
      {
          $this->_title($this->__('MLG Openpay Reportes'));

          $this->_initAction()
              ->_setActiveMenu('report/product/mlg_openpayreport')
              ->_addBreadcrumb(Mage::helper('mlg_openpayreport')->__('MLG Openpay Reportes'), 
                Mage::helper('mlg_openpayreport')->__('MLG Openpay Reportes'))
              ->_addContent($this->getLayout()->createBlock('mlg_openpayreport/report_product_opyreport'))
              ->renderLayout();
      }

      /**
       * Export Sold Simple Products report to CSV format action
       *
       */
      public function exportSoldCsvAction()
      {
          $fileName   = 'CPMN-openpayreport-'.time().'.csv';
          $content    = $this->getLayout()
              ->createBlock('mlg_openpayreport/report_product_opyreport_grid')
              ->getCsv();

          $this->_prepareDownloadResponse($fileName, $content);
      }

      /**
       * Export Sold Simple Products report to XML format action
       *
       */
      public function exportSoldExcelAction()
      {
          $fileName   = 'CPMN-openpayreport-'.time().'.xml';
          $content    = $this->getLayout()
              ->createBlock('mlg_openpayreport/report_product_opyreport_grid')
              ->getExcel($fileName);

          $this->_prepareDownloadResponse($fileName, $content);
      }
  }
  ?>
