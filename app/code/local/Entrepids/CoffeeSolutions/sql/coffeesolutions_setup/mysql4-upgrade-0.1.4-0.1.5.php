<?php
/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     fabian.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('sales/quote_item')}
        ADD COLUMN id_solution int(10) NULL COMMENT '# Solution';

    ALTER TABLE {$this->getTable('sales/order_item')}
        ADD COLUMN id_solution int(10) NULL COMMENT '# Solution';
");

$installer->endSetup();