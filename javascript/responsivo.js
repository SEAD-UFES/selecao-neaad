/* 
 * Scripts responsáveis por realizar alguma adaptação responsiva no sistema
 */
var TAMANHO_RESPONSIVO = 768; // limite da tela para conversão da responsividade

$(document).ready(function () {
    $(window).resize(tabsResponsivo);
    $(window).trigger('resize');
});

function tabsResponsivo() {

    // recuperando resolução da tela
    var resolucao = document.documentElement.clientWidth;

    // caso mobile
    if (resolucao <= TAMANHO_RESPONSIVO) {

        if ($('.select-menu').length === 0) {
            // create select menu
            var select = $('<select></select>');

            // add classes to select menu
            select.addClass('select-menu seletorMobile');

            // each link to option tag
            $('.nav-tabs:not(.campoDesktop) li a').each(function () {
                // create element option
                var option = $('<option></option>');

                // add href value to jump
                option.val($(this).attr('href'));

                // add text
                option.text($(this).text());

                // selecionar quem está ativo
                if ($(this).parent().hasClass("active")) {
                    option.attr("selected", true);
                }

                // append to select menu
                select.append(option);
            });

            // add change event to select
            select.change(function () {
                if (this.value.match("^http://")) {
                    window.location.href = this.value;
                } else {
                    $('.nav-tabs a[href=' + this.value + ']').tab('show');
                }
            });

            // add select element to dom, hide the .nav-tabs
            $('.nav-tabs:not(.campoDesktop)').before(select).hide();
        }
    }

    // extrapolou limite
    if (resolucao > TAMANHO_RESPONSIVO) {
        $('.select-menu').remove();
        $('.nav-tabs').show();
    }

}


