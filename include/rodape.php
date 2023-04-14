<div id="footer" class="m08">
    <p>© 2015 Universidade Federal do Espírito Santo <separador class="barra"></separador> Secretaria de Ensino a Distância (SEAD)</p>
<p class="campoDesktop">Av. Fernando Ferrari, 514, Goiabeiras <separador class="hifen"></separador> Vitória - ES - CEP 29075-910</p>
<p class="campoDesktop">Tel.: (27) 4009-2208</p>
</div>

<div class="scroll-top-wrapper ">
    <span class="scroll-top-inner">
        <i class="fa fa-2x fa-arrow-circle-up" title="Ir para topo"></i>
    </span>
</div>

<!--Barra do governo-->
<script defer="defer" src="//barra.brasil.gov.br/barra.js" type="text/javascript"></script>

<?php
require_once "$CFG->rpasta/include/includesPos.php";
carregaScript("responsivo");
?>

<script type="text/javascript">
    $(document).ready(function () {
        function acertaRodape() {
            var tamTela = $(window).height();
            var tamTopo = parseInt($("#toposite").css("height")) + parseInt($("#barra-brasil").css("height"));
            var tamMain = tamTopo + parseInt($("#main").css("height"));
            var tamMargemRodape = parseInt($("#footer").css("margin-top"));
            var tamRodapeSemMargem = parseInt($("#footer").css("height"));
            var margemSeg = 10;
            if (tamMain + tamRodapeSemMargem + margemSeg < tamTela) {
                $("#footer").css("margin-top", tamTela - tamMain - tamRodapeSemMargem);
                $("#main").css("min-height", $("#main").css("height"));
            } else {
                $("#main").css("min-height", tamTela - tamRodapeSemMargem - tamTopo - tamMargemRodape);
            }
        }

        $(window).resize(acertaRodape);
        $(window).resize();



        $(function () {
            $(document).on('scroll', function () {
                if ($(window).scrollTop() > 100) {
                    $('.scroll-top-wrapper').addClass('show');
                } else {
                    $('.scroll-top-wrapper').removeClass('show');
                }
            });
        });


        $(function () {
            $(document).on('scroll', function () {
                if ($(window).scrollTop() > 100) {
                    $('.scroll-top-wrapper').addClass('show');
                } else {
                    $('.scroll-top-wrapper').removeClass('show');
                }
            });

            $('.scroll-top-wrapper').on('click', scrollToTop);
        });


        function scrollToTop() {
            verticalOffset = typeof (verticalOffset) != 'undefined' ? verticalOffset : 0;
            element = $('body');
            offset = element.offset();
            offsetTop = offset.top;
            $('html, body').animate({scrollTop: offsetTop}, 500, 'linear');
        }
    });

</script>