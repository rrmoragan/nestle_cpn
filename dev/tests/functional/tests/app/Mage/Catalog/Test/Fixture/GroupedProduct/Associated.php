<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Mage\Catalog\Test\Fixture\GroupedProduct;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Repository\RepositoryFactory;

/**
 * Grouped associated products preset.
 */
class Associated extends DataSource
{

    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Associated products data
     */
    protected $products;

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param ObjectManager $objectManager
     * @param array $data
     * @param array $params [optional]
     ** @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(RepositoryFactory $repositoryFactory, ObjectManager $objectManager, array $data, array $params = [])
    {
        $this->objectManager = $objectManager;
        $this->params = $params;
        $associatedData = isset($data['dataset'])
            ? $repositoryFactory->get($this->params['repository'])->get($data['dataset'])
            : $data;
        if ($associatedData) {
            $this->products = $this->createProducts($associatedData['products'])['products'];
            foreach ($this->products as $key => $product) {
                $this->data[] =
                    [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'qty' => $associatedData['assigned_products'][$key]['qty'],
                        'position' => $key + 1
                    ];
            }
        }
    }

    /**
     * Create products.
     *
     * @param string $products
     * @return array
     */
    protected function createProducts($products)
    {
        return $this->objectManager->create('Mage\Catalog\Test\TestStep\CreateProductsStep', ['products' => $products])
            ->run();
    }

    /**
     * Return products' fixtures.
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
