<?php
/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
$installer = $this;

$installer->startSetup();

$attributes = array(
    'cs_solution_name' => array(
        'group'            => 'Solución de Café',
        'input'            => 'text',
        'type'             => 'varchar',
        'label'            => 'Ubicación de la Solución',
        'visible'          => true,
        'required'         => false,
        'visible_on_front' => true,
        'user_defined'     => true,
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'sort_order'       => 10
    ), 
    'cs_solution_image' => array(
        'group'            => 'Solución de Café',
        'input'            => 'image',
        'type'             => 'varchar',
        'label'            => 'Icono de la solución',
        'backend'          => 'catalog/category_attribute_backend_image',
        'visible'          => true,
        'required'         => false,
        'visible_on_front' => true,
        'user_defined'     => true,
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'sort_order'       => 20
    ),
    /*'cs_num_tazas_ini' => array(
        'group'            => 'Detalle de la Solución de Café',
        'input'            => 'text',
        'type'             => 'int',
        'label'            => 'Número inicial de Tazas diarias',
        'visible'          => true,
        'required'         => false,
        'visible_on_front' => true,
        'user_defined'     => true,
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'sort_order'       => 30
    ),
    'cs_num_tazas_fin' => array(
        'group'            => 'Detalle de la Solución de Café',
        'input'            => 'text',
        'type'             => 'int',
        'label'            => 'Número final de Tazas diarias',
        'visible'          => true,
        'required'         => false,
        'visible_on_front' => true,
        'user_defined'     => true,
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'sort_order'       => 40
    ),
    
    'cs_linea' => array(
        'group'            => 'Detalle de la Solución de Café',
        'input'            => 'select',
        'type'             => 'varchar',
        'label'            => 'Linea a la que pertenece',
        'visible'          => true,
        'required'         => false,
        'visible_on_front' => true,
        'user_defined'     => true,
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'source'           => 'coffeesolutions/system_config_source_lines',
        'sort_order'       => 50
    ),
    'cs_prioridad' => array(
        'group'            => 'Detalle de la Solución de Café',
        'input'            => 'text',
        'type'             => 'int',
        'label'            => 'Prioridad de la solución',
        'visible'          => true,
        'required'         => false,
        'visible_on_front' => true,
        'user_defined'     => true,
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'sort_order'       => 60
    ),*/
);
foreach ($attributes as $key => $value) {
    $installer->addAttribute('catalog_category', $key, $value);
}

$installer->endSetup();