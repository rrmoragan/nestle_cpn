<?php
/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('entrepids_coffee_solutions')}
    ADD COLUMN calification int(10) UNSIGNED NOT NULL DEFAULT 0;
    
ALTER TABLE {$this->getTable('entrepids_coffee_solutions')}
    ADD COLUMN description TEXT NULL;

ALTER TABLE {$this->getTable('entrepids_coffee_solutions')}
    ADD COLUMN costbycoup VARCHAR(60) NULL;

ALTER TABLE {$this->getTable('entrepids_coffee_solutions')}
    ADD COLUMN preparation VARCHAR(100) NULL;
    
ALTER TABLE {$this->getTable('entrepids_coffee_solutions')}
    ADD COLUMN beveragetype VARCHAR(200) NULL;

ALTER TABLE {$this->getTable('entrepids_coffee_solutions')}
    ADD COLUMN varieties VARCHAR(60) NULL;
 ");

$installer->endSetup();