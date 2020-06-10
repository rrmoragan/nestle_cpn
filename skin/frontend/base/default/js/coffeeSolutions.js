/**
 * 
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 * 
 * **/


var nestle_cs = {
    init: function () {
    	$$("input[type*='checkbox']:first",
		".content_solution > .tabs:first > .panel > .prod > .cont_actions_prod > .cont_check").each(function (item) {
            item.checked = false;
        });
        this.calcTotals();
    },
    checkbox: function (cb) {
        this.calcTotals();
        if(!cb.checked){
            $j('.mycheck').removeClass('selected');
        }
        if(!this.allIsChecked()){
            $j('#deselect').css('display', 'none');
            $j('#deselectbot').css('display', 'none');
            $j('#selectall').css('display', 'inline-block');
            $j('#selectallbot').css('display', 'inline-block');
        }else{
            $j('#deselect').css('display', 'inline-block');
            $j('#deselectbot').css('display', 'inline-block');
            $j('#selectall').css('display', 'none');
            $j('#selectallbot').css('display', 'none');
        }
    },

    inputValueCheck: function (input) {
        if (!(/^[0-9]+$/.test(input.value))) {
            var defaultQty = input.getAttribute('data-original-value');
            input.value = defaultQty;
        }
        this.calcTotals();
    },

    resetProduct: function (obj) {
        var defaultQty = obj.getAttribute('data-original-value');
        var productId = obj.getAttribute('data-product-id');
        if ($('qty-' + productId)) {
            $('qty-' + productId).value = defaultQty;
            this.calcTotals();
        }
    },

    addQty: function (obj) {
        var containerId = obj.getAttribute('data-qty-id');
        if ($(containerId)) {
            var currentVal = $(containerId).value;
            $(containerId).value = ++currentVal;
            this.inputValueCheck($(containerId));
            this.calcTotals();
        }
    },

    subQty: function (obj) {
        var containerId = obj.getAttribute('data-qty-id');
        if ($(containerId)) {
            var currentVal = $(containerId).value;
            $(containerId).value = --currentVal;
            this.inputValueCheck($(containerId));
            this.calcTotals();
        }
    },

    calcTotals: function () {
        var totalPriceSol = 0;
        var totalProducts = 0;
        var csgroup = new Array();
        $$('#form-solution-coffee input[type=checkbox]').each(function (item) {
            //console.log(item);
            var pid = item.getAttribute('data-product-id');
            var pprice = parseFloat(item.getAttribute('data-product-price'));
            var idgroup = parseInt(item.getAttribute('data-id-group'));
            var pqty = 0;
            if(!csgroup[idgroup]){
                csgroup[idgroup] = { idgroup:idgroup,  totalgroup: 0 };
            }
            if (item.checked && $('qty-' + pid)) {
                pqty = parseInt($('qty-' + pid).value);
                totalPriceSol += (pprice * pqty);
                totalProducts += pqty;
                csgroup[idgroup].totalgroup += (pprice * pqty);
            }
        });
        $('total-qty-products').update(totalProducts);
        $('final-price-1').update(this.formatPrice(totalPriceSol));
        if($('final-price-2')){
            $('final-price-2').update(this.formatPrice(totalPriceSol));
        }
        if(csgroup){
            csgroup.each(function(igroup){
                if(igroup.idgroup){
                    //$('price-group-' + igroup.idgroup).update('Total: $ ' + nestle_cs.formatPrice(igroup.totalgroup));
                }
            });
        }
        
    },
    
    formatPrice: function(price){
        var formattedPrice = '0.<sup>00</sup>';
        var integerPrice = Math.floor(price);
        var decimalPrice = Math.floor((price - integerPrice) * 100);
        if (decimalPrice > 10) {
            formattedPrice = this.separateByCommas(integerPrice) + '.<sup>' + String(decimalPrice) + '</sup>';
        } else if (decimalPrice > 0) {
            formattedPrice = this.separateByCommas(integerPrice) + '.<sup>0' + String(decimalPrice) + '</sup>';
        } else {
            formattedPrice = this.separateByCommas(integerPrice) + '.<sup>00</sup>';
        }
        return formattedPrice;
    },
    
    separateByCommas: function(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
    allIsChecked:function(){
        var allCheckedIs = true;
        $$('#form-solution-coffee input[type=checkbox]').each(function (item) {
            //console.log(item.checked);
            if(!item.checked){
                allCheckedIs = false;
                
            }
        });
        return allCheckedIs;
    },
    checkAll: function () {
        $$('#form-solution-coffee input[type=checkbox]').each(function (item) {
            item.checked = true;
        });
        $j('.mycheck').addClass('selected');
        $j('#deselect').css('display', 'inline-block');
        $j('#deselectbot').css('display', 'inline-block');
        $j('#selectall').css('display', 'none');
        $j('#selectallbot').css('display', 'none');
        this.calcTotals();
    },

    uncheckAll: function () {
        $$('#form-solution-coffee input[type=checkbox]').each(function (item) {
            item.checked = false;
        });
        $j('.mycheck').removeClass('selected');
        $j('#deselect').css('display', 'none');
        $j('#deselectbot').css('display', 'none');
        $j('#selectall').css('display', 'inline-block');
        $j('#selectallbot').css('display', 'inline-block');
        this.calcTotals();
    },
    
    checkAllGroup: function(idgroup){
        $$('#tab-group-'+idgroup+' input[type=checkbox]').each(function (item) {
            item.checked = true;
        });
        if(!this.allIsChecked()){
            $j('#deselect').css('display', 'none');
            $j('#deselectbot').css('display', 'none');
            $j('#selectall').css('display', 'inline-block');
            $j('#selectallbot').css('display', 'inline-block');
        }else{
            $j('#deselect').css('display', 'inline-block');
            $j('#deselectbot').css('display', 'inline-block');
            $j('#selectall').css('display', 'none');
            $j('#selectallbot').css('display', 'none');
        }
        this.calcTotals();
    },
    
    uncheckAllGroup: function(idgroup){
        $$('#tab-group-'+idgroup+' input[type=checkbox]').each(function (item) {
            item.checked = false;
        });
        if(!this.allIsChecked()){
            $j('#deselect').css('display', 'none');
            $j('#deselectbot').css('display', 'none');
            $j('#selectall').css('display', 'inline-block');
            $j('#selectallbot').css('display', 'inline-block');
        }else{
            $j('#deselect').css('display', 'inline-block');
            $j('#deselectbot').css('display', 'inline-block');
            $j('#selectall').css('display', 'none');
            $j('#selectallbot').css('display', 'none');
        }
        this.calcTotals();
    },
    
    resetAllGroup: function(idgroup){
        $$('#tab-group-'+idgroup+' input[type=number]').each(function (item) {
            var defaultQty = item.getAttribute('data-original-value');
            var productId = item.getAttribute('data-product-id');
            if ($('qty-' + productId)) {
                $('qty-' + productId).value = defaultQty;
            }
        });
        this.calcTotals();
    },
    hoverindiv:function(objectId){
        var elm = document.getElementById(objectId);
        if(elm){
            elm.style.display = 'block';
        }
    },
    hoveroutdiv:function(objectId){
        var elm = document.getElementById(objectId);
        if(elm){
            elm.style.display = 'none';
        }
    },
};
/*document.observe('dom:ready',function(){
	$j(
		"input[type*='checkbox']:first",
		".content_solution > .tabs:first > .panel > .prod > .cont_actions_prod > .cont_check"
	).prop("checked",false);
	$j(
		"input[type*='checkbox']:first",
		".content_solution > .tabs:first > .panel > .prod:first > .cont_actions_prod > .cont_check"
	).prop("checked",true);
});*/
document.observe('dom:loaded',function(){
    nestle_cs.init();
});