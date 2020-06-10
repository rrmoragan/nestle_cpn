<?php
  /**
   * file: magento_root/app/code/local/Mlg/OpenpayReport/Model/Resource/Openpay/Sold/Collection.php
   */

echo '<tt>Mlg_OpenpayReport_Model_Resource_Openpay_Sold_Collection</tt>';

  class Mlg_OpenpayReport_Model_Resource_Openpay_Sold_Collection extends Mage_Reports_Model_Resource_Product_Sold_Collection
  {
      /**
       * Add ordered qty's
       * updating condition to show simple products instead of products with NULL parent id
       *
       * @param string $from
       * @param string $to
       * @return updating condition to show simple products instead of products with NULL parent id
       */
      public function addOrderedQty($from = '', $to = '')
      {
          $adapter              = $this->getConnection();
          $compositeTypeIds     = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
          $orderTableAliasName  = $adapter->quoteIdentifier('order');

          $orderJoinCondition   = array(
              $orderTableAliasName . '.entity_id = order_items.order_id',
              $adapter->quoteInto("{$orderTableAliasName}.state <> ?", Mage_Sales_Model_Order::STATE_CANCELED),

          );

          $productJoinCondition = array(
              $adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
              'e.entity_id = order_items.product_id',
              $adapter->quoteInto('e.entity_type_id = ?', $this->getProductEntityTypeId())
          );

          if ($from != '' && $to != '') {
              $fieldName            = $orderTableAliasName . '.created_at';
              $orderJoinCondition[] = $this->_prepareBetweenSql($fieldName, $from, $to);
          }
          
          $this->getSelect()->reset()
              ->from(
                  array('order_items' => $this->getTable('sales/order_item')),
                  array(
                      'ordered_qty' => 'SUM(order_items.qty_ordered)',
                      'order_items_name' => 'order_items.name'
                  ))
              ->joinInner(
                  array('order' => $this->getTable('sales/order')),
                  implode(' AND ', $orderJoinCondition),
                  array())
              ->joinLeft(
                  array('e' => $this->getProductEntityTableName()),
                  implode(' AND ', $productJoinCondition),
                  array(
                      'entity_id' => 'order_items.product_id',
                      'entity_type_id' => 'e.entity_type_id',
                      'attribute_set_id' => 'e.attribute_set_id',
                      'type_id' => 'e.type_id',
                      'sku' => 'e.sku',
                      'has_options' => 'e.has_options',
                      'required_options' => 'e.required_options',
                      'created_at' => 'e.created_at',
                      'updated_at' => 'e.updated_at'
                  ))
              ->where('e.type_id = ?', 'simple')
              ->group('order_items.product_id')
              ->having('SUM(order_items.qty_ordered) > ?', 0);

          return $this;
      }
  }
?>
