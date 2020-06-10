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
 * @category    design
 * @package     rwd_default
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
/*
 * modif: rmorales@mlg.com.mx
 */

Checkout.prototype.gotoSection = function (section, reloadProgressBlock) {
    // Adds class so that the page can be styled to only show the "Checkout Method" step
    if ((this.currentStep == 'login' || this.currentStep == 'billing') && section == 'billing') {
        $j('body').addClass('opc-has-progressed-from-login');
    }

    if (reloadProgressBlock) {
        this.reloadProgressBlock(this.currentStep);
    }
    this.currentStep = section;
    var sectionElement = $('opc-' + section);
    sectionElement.addClassName('allow');
    this.accordion.openSection('opc-' + section);

    // Scroll viewport to top of checkout steps for smaller viewports
    if (Modernizr.mq('(max-width: ' + bp.xsmall + 'px)')) {
        $j('html,body').animate({scrollTop: $j('#checkoutSteps').offset().top}, 800);
    }

    if (!reloadProgressBlock) {
        this.resetPreviousSteps();
    }

};

function hideBillingStep()
{

    if ($j('#is_bill').is(':checked')) 
    {
        /*$j(".button").click();
        $j(".entrepids-billing").hide();*/

        /*
        if(accordion.currentSection == 'opc-billing') 
        {
            billing.save();
        }
        */

        $j("#checkout-step-billing").hide();
        $j("#billing-progress-opcheckout").css("display", "none");
        
        $j("#opc-shipping").show().addClass('active');
        
        $j("#checkout-step-shipping").show();
        
        
        /*$j("#opc-shipping .step-title").css('border-top', 'none');
        $j("span.number").each(function(){
            var n = parseInt($j(this).text());
            $j(this).text(n-1);
        });*/
    }
    else
    {
        $j("#checkout-step-billing").show();
        jQuery("#billing-progress-opcheckout").css("display", "block");
        $j("#entrepids-billing").click();
    }

}


