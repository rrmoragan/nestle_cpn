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
 * @category    Varien
 * @package     js
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
var paymentForm = Class.create();
paymentForm.prototype = {
    initialize: function(formId){
        this.formId = formId;
        this.validator = new Validation(this.formId);
        var elements = Form.getElements(formId);

        var method = null;
        for (var i=0; i<elements.length; i++) {
            if (elements[i].name=='payment[method]' || elements[i].name=='form_key') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
            } else {
                if((elements[i].type) && ('submit' != elements[i].type.toLowerCase())) {
                    elements[i].disabled = true;
                }
            }
            elements[i].setAttribute('autocomplete','off');
        }
        if (method) this.switchMethod(method);
    },

    switchMethod: function(method){
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
            var form = $('payment_form_'+this.currentMethod);
            form.style.display = 'none';
            var elements = form.getElementsByTagName('input');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
            var elements = form.getElementsByTagName('select');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;

        }
        if ($('payment_form_'+method)){
            var form = $('payment_form_'+method);
            form.style.display = '';
            var elements = form.getElementsByTagName('input');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            var elements = form.getElementsByTagName('select');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            this.currentMethod = method;
        }
    }
};
