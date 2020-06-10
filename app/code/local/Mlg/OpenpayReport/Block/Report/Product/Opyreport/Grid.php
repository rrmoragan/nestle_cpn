<?php
  /**
   * file: app/code/local/Mlg/OpenpayReport/Block/Report/Product/Opyreport/Grid.php
   */

  echo '<tt>Mlg_OpenpayReport_Block_Report_Product_Opyreport_Grid</tt>';

  class Mlg_OpenpayReport_Block_Report_Product_Opyreport_Grid extends Mage_Adminhtml_Block_Report_Product_Sold_Grid
  {
      /**
       * Setting up proper product collection name for a report
       *
       * @return Mlg_OpenpayReport_Block_Report_Product_Opyreport_Grid
       */
      protected function _prepareCollection()
      {
        echo '+++++++';
        /*
          $nesca = MAGENTO_ROOT.'\\NESCA\\';
          $nesca_libs = $nesca.'libs\\';
          $file  = $nesca.'openpay_report\\';

          include( $nesca_libs.'basics.php' );
          include( $nesca_libs.'querys.php' );
          include( $nesca_libs.'forceUTF8.php' );
          include( $nesca_libs.'r_openpay.php' );

          $op = new openpay_mlg();
          $op->report();

          $op->data = $this->random_data( $op->report_cab() );

          $html = $this->report_html( $op->data, $op->report_cab() );

          $file = $file.$op->report_name();
          $res = $this->export_csv( $file, $op->data, $op->report_cab() );

          echo $this->botom_csv_download( $op->report_name() );

          echo $html;

          *//*
          Mage_Adminhtml_Block_Report_Grid::_prepareCollection();

          $this->getCollection()->initReport('mlg_openpayreport/openpay_sold_collection');*/
          return $this;
      }

      private function random_data( $cab ){
        /*
          $b = null;
          for($i=0;$i<=15;$i++){
            $a = null;
            foreach ($cab as $et => $r) {
                 $a[ $r ] = 'aaaa-'.rand( 100000, 500000 );
            }
            $b[] = $a;
          }

          return $b;*/
      }

      private function report_html( $data, $acab ){/*
          $table = null;
          $even = 1;
          foreach ($data as $et => $r) {
            if( $even ){ $table = $table.'<tr class="even">'; $even = 0; }else{
              $table = $table.'<tr>'; $even = 1;
            }
            foreach ($r as $etr => $rr) {
               $table = $table.'<td>'.$rr.'</td>';
            }
            $table = $table.'</tr>';
          }
          $table = '<tbody>'.$table.'</tbody>';

          $cab = '';
          foreach ($acab as $et => $r) {
            $cab = $cab.'<th class="no-link">'.$r.'</th>';
          }
          $footer = '<tfoot><tr>'.$cab.'</tr></tfoot>';
          $footer = '';
          $cab = '<thead><tr class="headings">'.$cab.'</tr></thead>';

          $table = '<table class="data" id="mmmmmmm" cellspacing=0>'.$cab.$table.$footer.'</table>';
          $table = '<div class="grid">'.$table.'</div>';

          $table = '<style>.grid{overflow: hidden;border: 1px solid #ccc;padding: 10px;overflow-x: auto;}</style>'.$table;

          return $table;*/
      }

      private function export_csv( $name='',$data=null,$cab=null ){
        /*
        $file = 'noname.csv';
        if($name!=''){ $file = $name; }

        $ss = '';
        if($cab!=null){
          foreach ($cab as $et => $r) {
            if( $ss!='' ){ $ss=$ss.','; }
            $ss = $ss.'"'.$r.'"';
          }
        }

        $s = '';
        if( $data!=null ){
          foreach ($data as $et => $r) {
            $st = '';
            foreach ($r as $etr => $rr) {
              if($st!=''){ $st = $st.','; }
              if( is_float( $rr ) ){ $st = $st.sprintf( "%0.2f", $rr ); }else{
              if( is_int( $rr ) ){ $st = $st.sprintf( "%d", $rr ); }else{
                $st = $st.'"'.$rr.'"';
              } }
            }
            $s = $s.$st."\n";
          }
        }

        $s = $ss."\n".$s;

        $fp = fopen($name, "w");
        if( !$fp ){
          tt( 'error creando archivo.' );
          return '';
        }

        fwrite($fp, forceLatin1($s) );
        fclose($fp);

        return $name;*/
      }

      private function botom_csv_download( $file ){
        /*
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/cafe/NESCA/openpay_report/'.$file;

        $s = '<div class="file_download"><a id="file_download" class="button_download" download="'.$file.'" href="'.$url.'">Exportar datos</a></div>';
        $s = $s.'<style>.file_download{display: block;text-align: right;margin-bottom:10px;}.file_download .button_download{display: inline-block;padding: 5px 20px;border-radius: 5px;background-color: #e1261c;color: white !important;cursor: pointer;text-decoration:none;}</style>';

        return $s;
        */
      }

  }
  ?>
