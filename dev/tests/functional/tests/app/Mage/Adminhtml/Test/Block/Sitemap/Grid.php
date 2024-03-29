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

namespace Mage\Adminhtml\Test\Block\Sitemap;

use Magento\Mtf\Client\Locator;

/**
 * Sitemap grid.
 */
class Grid extends \Mage\Adminhtml\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'sitemap_filename' => [
            'selector' => '#sitemapGrid_filter_sitemap_filename',
        ],
        'sitemap_path' => [
            'selector' => '#sitemapGrid_filter_sitemap_path',
        ],
        'sitemap_id' => [
            'selector' => '#sitemapGrid_filter_sitemap_id',
        ],
    ];

    /**
     * Locator link for Google in grid.
     *
     * @var string
     */
    protected $linkForGoogle = ".//td/a[contains(.,'.xml')]";

    /**
     * Get link for Google.
     *
     * @return string
     */
    public function getLinkForGoogle()
    {
        return $this->_rootElement->find($this->linkForGoogle, Locator::SELECTOR_XPATH)->getText();
    }
}
