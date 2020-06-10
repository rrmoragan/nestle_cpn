// =============================================
// Nestle JS
// =============================================

// Insert here all the new scripts that will apply in Nestle site.


jQuery(document).ready(function () {
    $j('.select').change(function () {
        $j(this).find(':selected').addClass('selected')
                .siblings('option').removeClass('selected');
    });
});


$j(document).ready(function() {
    var maxLength = 55;
    if (screen.width < 599) {
        $j('#billing-address-select > option').text(function(i, text) {
            if (text.length > maxLength) {
                return text.substr(0, maxLength) + '...';
            }
        });
        $j('#region_id > option').text(function(i, text) {
            if (text.length > maxLength) {
                return text.substr(0, maxLength) + '...';
            }
        });
    }
});

// Price replace $ //

/*jQuery(document).ready(function(){
 $j('.product-view .price').text(function( i, txt ) { 
 return txt.replace("$"," "); 
 });
 });
 jQuery(document).ready(function(){
 $j('.category-products .price').text(function( i, txt ) { 
 return txt.replace("$"," "); 
 });
 });*/
jQuery(document).ready(function () {
    $j('.cart-price .price').text(function (i, txt) {
        return txt.replace("$", " ");
    });
});
jQuery(document).ready(function () {
    $j('.cart-forms .price').text(function (i, txt) {
        return txt.replace("$", " ");
    });
});

// Header Account //

jQuery(document).ready(function () {
    if (screen.width > 1300) {
        $j('#header-account').addClass('deskt');
    } else {

    }
});
jQuery(document).ready(function () {
    if (screen.width > 1400) {
        $j('#header-account').addClass('deskt1');
        $j('#header-account').removeClass('deskt');
    } else {

    }
});
jQuery(document).ready(function () {
    if (screen.width > 1500) {
        $j('#header-account').addClass('deskt2');
        $j('#header-account').removeClass('deskt1');
        $j('#header-account').removeClass('deskt');
    } else {

    }
});
jQuery(document).ready(function () {
    if (screen.width > 1600) {
        $j('#header-account').addClass('deskt3');
        $j('#header-account').removeClass('deskt2');
        $j('#header-account').removeClass('deskt1');
        $j('#header-account').removeClass('deskt');
    } else {

    }
});
jQuery(document).ready(function () {
    if (screen.width > 1800) {
        $j('#header-account').addClass('deskt4');
        $j('#header-account').removeClass('deskt3');
        $j('#header-account').removeClass('deskt2');
        $j('#header-account').removeClass('deskt1');
        $j('#header-account').removeClass('deskt');
    } else {

    }
});


// Custom S//

jQuery(document).ready(function () {
    $j('form select').addClass('select_style');
});


// Reviews Messages//

jQuery(document).ready(function () {
    $j('#admin_messages:contains("Su reseña se ha aceptado para ser moderada.")').addClass('advicemsg');
});

// Insert Title //
/*
$j(document).ready(function () {
    $j('.checkout-cart-index #header').append('<div class="cont_titles"></div>');
    $j('.page-title h1').detach().appendTo('.cont_titles');
});*/
$j(document).ready(function () {
    $j('.checkout-onepage-index.loggued #header').append('<div class="cont_titles"><h1>Pagar</h1></div>');
});
$j(document).ready(function () {
    $j('.checkout-onepage-index.nologgued #header').append('<div class="cont_titles"><h1>Ingresa a tu cuenta para pagar</h1></div>');
});
$j(document).ready(function () {
    $j('.checkout-onepage-success #header').append('<div class="cont_titles"><h1>Compra exitosa</h1></div>');
});
$j(document).ready(function () {
    $j('.customer-account #header').append('<div class="cont_titles"><h1>Mi cuenta</h1></div>');
});
$j(document).ready(function () {
    $j('.customer-account-forgotpassword #header').append('<div class="cont_titles"><h1>Recuperar Contraseña</h1></div>');
});
$j(document).ready(function () {
    $j('.cms-index-noroute #header').append('<div class="cont_titles"><h1>Oops!...</h1></div>');
});
$j(document).ready(function () {
    $j('.customer-account-changeforgotten #header').append('<div class="cont_titles"><h1>Restablecer contraseña</h1></div>');
});



$j(document).ready(function () {
    if ($j(".cart-empty")[0]) {
        $j('.checkout-cart-index').addClass('empty-cart');
    } else {
        // Do something if class does not exist
    }
});

// Timeline//

$j(document).ready(function () {
    $j('.checkout-onepage-index #header').append('<div class="timeline five"></div>');
    $j('.time_main').detach().appendTo('.timeline');
});



// Tabs//

var acc = document.getElementsByClassName("accordion");
var i;
for (i = 0; i < acc.length; i++) {
    acc[i].onclick = function () {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.display === "none") {
            panel.style.display = "block";
        } else {
            panel.style.display = "none";
        }
    }
}

/* Backend solitions */

$j(document).ready(function () {
    $j('.qty-wrapper .more').on('click', function () {
        var input = $j(this).parent().find('input');
        input.val(parseInt(input.val()) + 1);
    });

    $j('.qty-wrapper .less').on('click', function () {
        var input = $j(this).parent().find('input');
        if (input.val() > 1) {
            input.val(parseInt(input.val()) - 1);
        }
    });

    $j('.catalog-qty-send').on('keypress',function(e){
        if(e.which == 13){
            $j(this).parent().parent().find('.btn-cart').trigger('click');
        }
    });

    sendAdd2Cart = function (ele, url) {
        $j('#advice-required-qty').remove()
        var value = $j(ele).parent().parent().find('input').val();
        if(value > 0 && /^[0-9]+$/.test(value)){
            setLocation(url + '?qty=' + value + '&position='+ $j(window).scrollTop());
        }else{
            $j(ele).parent().parent().append(
                '<div class="validation-advice" id="advice-required-qty" style="display: block; color: #DF280A; font-size:11px;">* Por favor, use sólo números en este campo, evite espacios u otros caracteres como puntos o comas.</div>'
            );
        }
    }

    //This is to slider bar filter price
    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer
    {
        $j('.range-slider-bar').on('mouseup', function (e) {
            setLocation($j(this).data('url') + '?price=' + $j(this).val() + '-');
        });
    } else {
        $j('.range-slider-bar').on('input', function () {
            $j('.range-slider-price').html('$' + $j(this).val());
        });

        $j('.range-slider-bar').on('change', function (e) {
            setLocation($j(this).data('url') + '?price=' + $j(this).val() + '-');
        });
    }
});

function validateChars(e){
	if(!(e.which === 8)){
            if(isNaN( String.fromCharCode(e.which))){
                e.preventDefault();
            }
        }
}
function validateChange(input){
	if(input.value < 0){
            input.value = 0;
	}
}

jQuery(document).ready(function () {
    $j('#form-solution-coffee .cont_title h2').text(function (i, txt) {
        return txt.replace("Maquinas", "Máquinas");
    });
});

jQuery(document).ready(function () {
    $j('.cont_title:contains("Insumos")').addClass('insumos');
});

jQuery(document).ready(function () {
    $j('.review-customer-index .limiter select').addClass('select pag');
    $j('.review-customer-index .pager-no-toolbar').addClass('toolbar');
});

jQuery(document).ready(function () {
    $j('#create_acc .error-msg:contains("Usuario o password inválidos.")').addClass('error-pass');
});
jQuery(document).ready(function () {
    $j('#create_acc .error-msg:contains("Se requiere identificador y contraseña.")').addClass('error-pass');
});

//Validations
Validation.add('validate-latin-names', 'Por favor intruduce un nombre/apellido válido.', function(v) {
    return Validation.get('IsEmpty').test(v) || /^([a-zA-ZñÑáéíóúÁÉÍÓÚ\s])+$/.test(v)
});