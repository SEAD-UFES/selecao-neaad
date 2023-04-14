<!DOCTYPE html>
<html>
    <head>     
        <title>Listar Avaliaçao Cega - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");
        require_once ($CFG->rpasta . "/util/filtro/FiltroInscritoProcesso.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado() == NULL || estaLogado(Usuario::$USUARIO_CANDIDATO) != NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        $loginRestrito = estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL;
        if ($loginRestrito) {

            if (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
                $curso = buscarCursoPorCoordenadorCT(getIdUsuarioLogado());
            } else {
                // recuperando usuario para manipulaçao
                $usu = buscarUsuarioPorIdCT(getIdUsuarioLogado());
                $curso = !Util::vazioNulo($usu->getUSR_ID_CUR_AVALIADOR()) ? buscarCursoPorIdCT($usu->getUSR_ID_CUR_AVALIADOR()) : NULL;
            }

            if ($curso == NULL) {
                new Mensagem("Você ainda não está associado a um curso.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
        }

        // caso do login restrito
        if ($loginRestrito) {
            // alterando id de curso para o permitido 
            $_GET['idCurso'] = $curso->getCUR_ID_CURSO();

            // buscando ultimo processo do curso
            $processo = buscarUltProcAbtPorCursoCT($curso->getCUR_ID_CURSO());

            // recuperando processo e chamada
            if ($processo != NULL) {
                $_GET['idProcesso'] = $processo->getPRC_ID_PROCESSO();
                $_GET['idChamada'] = $processo->PCH_ID_ULT_CHAMADA;
            }
        }

        //criando filtro
        $filtro = new FiltroInscritoProcesso($_GET, 'listarAvaliacaoCegaInsc.php', $loginRestrito, "", TRUE);

        //criando objeto de paginação
        $paginacao = new Paginacao('tabelaAvalCegaPorProcesso', 'contaAvalCegaPorProcessoCT', $filtro);
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>
    </head>

    <body>  
        <?php
        include ($CFG->rpasta . "/include/cabecalho.php");
        ?>
        <div id="main">
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <strong>Avaliação Cega</strong></h1>
                </div>

                <div class="col-full m02">
                    <div class="panel-group" id="accordionFiltro" role="tablist" aria-multiselectable="true">
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab">
                                <a data-toggle="collapse" data-parent="#accordionFiltro" href="#filtroPadrao" aria-expanded="true" aria-controls="filtroPadrao">
                                    <h4 class="panel-title">Filtro</h4>
                                </a>
                            </div>
                            <div id="filtroPadrao" class="panel-collapse collapse <?php $filtro->getAccordionAberto() ? print "in" : ""; ?>" role="tabpanel" aria-labelledby="filtroPadrao">
                                <div class="panel-body">
                                    <form id='formBuscaInscritos' method='get' action='listarAvaliacaoCegaInsc.php'>
                                        <div class="filtro">
                                            <div class="col-md-6">
                                                <h4>Curso</h4>
                                                <?php impressaoCurso($filtro->getIdCursoTela(), NULL, $loginRestrito); ?>
                                            </div>

                                            <div id="divListaProcesso" class="col-md-3" style="display: none;">
                                                <h4>Edital</h4>
                                                <select class="form-control" name="idProcesso" id="idProcesso"></select>
                                            </div>

                                            <div id="divListaChamada" class="col-md-3" style="display: none;">
                                                <h4>Chamada</h4>
                                                <select class="form-control" name="idChamada" id="idChamada"></select>
                                            </div>

                                            <div id="divListaPolo" class="col-md-4 m01" style="display: none;">
                                                <h4>Polo</h4>
                                                <select class="form-control" name="idPolo" id="idPolo"></select>
                                            </div>

                                            <div id="divListaArea" class="col-md-4 m01" style="display: none;">
                                                <h4>Área</h4>
                                                <select class="form-control" name="idAreaAtuacao" id="idAreaAtuacao"></select>
                                            </div>

                                            <div id="divListaReservaVaga" class="col-md-4 m01" style="display: none;">
                                                <h4>Reserva</h4>
                                                <select class="form-control" name="idReservaVaga" id="idReservaVaga"></select>
                                            </div>

                                            <div id="spinner" style="display: none">
                                                <div class="delimitador">
                                                    <div class="desenho"></div>
                                                </div>
                                            </div>

                                            <div id="divBotoes" class="completo campo-botoes m02">
                                                <button class="btn btn-success" id="submeter" type="submit">Filtrar</button>
                                                <button class="btn btn-default" id="limpar" type="button" title="Limpar filtros">Limpar buscar</button>
                                            </div>
                                            <div id="divMensagem" class="col-full campo-carregando" style="display:none">
                                                <div class="alert alert-info">
                                                    Aguarde o processamento...
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-full m02">
                    <?php $paginacao->imprimir(); ?>
                </div>
            </div>
        </div> 
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("jquery.maskedinput");
        carregaScript("ajax");
        carregaScript("jquery.cookie");
        carregaScript("spin");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            // criando spin
            var opts = {
                lines: 7, // The number of lines to draw
                length: 6, // The length of each line
                width: 4, // The line thickness
                radius: 6, // The radius of the inner circle
                corners: 1, // Corner roundness (0..1)
                rotate: 0, // The rotation offset
                direction: 1, // 1: clockwise, -1: counterclockwise
                color: '#000', // #rgb or #rrggbb or array of colors
                speed: 1, // Rounds per second
                trail: 60, // Afterglow percentage
                shadow: false, // Whether to render a shadow
                hwaccel: false, // Whether to use hardware acceleration
                className: 'spinner', // The CSS class to assign to the spinner
                zIndex: 2e9, // The z-index (defaults to 2000000000)
                top: '50%', // Top position relative to parent
                left: '50%' // Left position relative to parent
            };
            var spinner = new Spinner(opts).spin();
            $(".desenho").after(spinner.el);

            $("#formBuscaInscritos").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagemInline();
                    form.submit();
                }
            });

            // tratando gatilho de ajax para polo
            function getParamsPolo()
            {
                return {'cargaSelect': "poloChamada", 'idChamada': $("#idChamada").val()};
            }
            function posCargaPolo() {
                // mudando nome de opçao "selecione..." 
                $("#idPolo option:eq(0)").text("Selecione");
            }
            var gatPolo = adicionaGatilhoAjaxSelectIn("idChamada", getIdSelectSelecione(), "spinner", "divListaPolo", "idPolo", "<?php print $filtro->getIdPolo(); ?>", getParamsPolo, posCargaPolo, false);



            //tratando gatilho de ajax para area de atuaçao
            function getParamsAreaAtu()
            {
                return {'cargaSelect': "areaAtuChamada", 'idChamada': $("#idChamada").val()};
            }
            function posCargaAreaAtu() {
                // mudando nome de opçao "selecione..."
                $("#idAreaAtuChamada option:eq(0)").text("Selecione");
            }
            var gatAreaAtu = adicionaGatilhoAjaxSelectIn("idChamada", getIdSelectSelecione(), "spinner", "divListaArea", "idAreaAtuacao", "<?php print $filtro->getIdAreaAtuacao() ?>", getParamsAreaAtu, posCargaAreaAtu, false);



            //tratando gatilho de ajax para reserva de vaga
            function getParamsReservaVaga()
            {
                return {'cargaSelect': "reservaVagaChamada", 'idChamada': $("#idChamada").val()};
            }
            function posCargaReservaVaga() {
                // mudando nome de opçao "selecione..."
                $("#idReservaVaga option:eq(0)").text("Selecione");
            }
            var gatReservaVaga = adicionaGatilhoAjaxSelectIn("idChamada", getIdSelectSelecione(), "spinner", "divListaReservaVaga", "idReservaVaga", "<?php print $filtro->getIdReservaVaga() ?>", getParamsReservaVaga, posCargaReservaVaga, false);


            // tratando gatilho de ajax para chamada
            function getParamsChamada()
            {
                return {'cargaSelect': "chamadaProcesso", 'idProcesso': $("#idProcesso").val()};
            }
            function posCargaChamada() {
                // mudando nome de opçao "selecione..."
                $("#idChamada option:eq(0)").text("Selecione");

                // recarregando selects filhos
                gatPolo();
                gatAreaAtu();
                gatReservaVaga();
            }
            var gatChamada = adicionaGatilhoAjaxSelectIn("idProcesso", getIdSelectSelecione(), "spinner", "divListaChamada", "idChamada", "<?php print $filtro->getIdChamada(); ?>", getParamsChamada, posCargaChamada);


            // tratando gatilho de ajax para processo
            function getParamsProcesso()
            {
                return {'cargaSelect': "processoAbtCurso", 'idCurso': $("#idCurso").val()};
            }
            function posCargaProcesso() {
                // mudando nome de opçao "selecione..."
                $("#idProcesso option:eq(0)").text("Selecione");

                // recarregando selects filhos    
                gatChamada();
            }
            var gatProcesso = adicionaGatilhoAjaxSelectIn("idCurso", getIdSelectSelecione(), "spinner", "divListaProcesso", "idProcesso", "<?php print $filtro->getIdProcesso(); ?>", getParamsProcesso, posCargaProcesso);

            $("#limpar").click(function () {
                // destroi cookie
                $.removeCookie('<?php print $filtro->getNmCookie() ?>');

                limparFormulario($("#formBuscaInscritos"));

                // regerando selects
                gatProcesso();
            });

            function sucAvaliacao() {
                $().toastmessage('showToast', {
                    text: '<b>Avaliação registrada com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }
<?php
if (isset($_GET[Mensagem::$TOAST_VAR_GET])) {
    print $_GET[Mensagem::$TOAST_VAR_GET] . "();";
}
?>
        });
    </script>	
</html>