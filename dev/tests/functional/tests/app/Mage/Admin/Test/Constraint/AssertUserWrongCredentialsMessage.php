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

namespace Mage\Admin\Test\Constraint;

use Mage\Admin\Test\Fixture\User;
use Magento\Mtf\Constraint\AbstractConstraint;
use Mage\Adminhtml\Test\Page\AdminAuthLogin;

/**
 * Verify incorrect credentials message while login to admin.
 */
class AssertUserWrongCredentialsMessage extends AbstractConstraint
{
    /**
     * Credentials error message.
     */
    const INVALID_CREDENTIALS_MESSAGE = 'You did not sign in correctly or your account is temporarily disabled.';

    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Verify incorrect credentials message while login to admin.
     *
     * @param AdminAuthLogin $adminAuth
     * @param User $user
     * @return void
     */
    public function processAssert(AdminAuthLogin $adminAuth, User $user)
    {
        $adminAuth->open();
        $adminAuth->getLoginBlock()->loginToAdminPanel($user->getData());

        \PHPUnit_Framework_Assert::assertEquals(
            static::INVALID_CREDENTIALS_MESSAGE,
            $adminAuth->getMessagesBlock()->getErrorMessages(),
            'Message "' . static::INVALID_CREDENTIALS_MESSAGE . '" is not visible.'
        );
    }

    /**
     * Returns success message if equals to expected message.
     *
     * @return string
     */
    public function toString()
    {
        return 'Invalid credentials message was displayed.';
    }
}
