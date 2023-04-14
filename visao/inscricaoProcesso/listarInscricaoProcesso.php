<!DOCTYPE html>
<html>
    <head>     
        <title>Visualizar Inscrições para o Edital - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/controle/CTNotas.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");
        require_once ($CFG->rpasta . "/util/filtro/FiltroInscritoProcesso.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idProcesso'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        //verificando permissão e recuperando processo
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);

        // tela pode ser exibida?
        if (!$processo->permiteExibirAcaoCdt()) {
            new Mensagem("Visualização não permitida. Aguarde o período adequado...", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        //criando filtro
        $filtro = new FiltroInscritoProcesso($_GET, 'listarInscricaoProcesso.php', estaLogado(Usuario::$USUARIO_COORDENADOR), "", FALSE);

        //criando objeto de paginação
        $paginacao = new Paginacao('tabelaInscritosPorProcesso', 'contaInscritosPorProcessoCT', $filtro);
        ?>

        <?php
        // buscando dados para botões
        $etapaVigente = buscarEtapaVigenteCT($filtro->getIdChamada());
        $qtElimLote = $etapaVigente != NULL ? contarEliminacaoLoteCT($etapaVigente->getPRC_ID_PROCESSO(), $etapaVigente->getPCH_ID_CHAMADA(), $etapaVigente->getESP_NR_ETAPA_SEL()) : 0;
        $tagsBtElimLote = $qtElimLote == 0 ? " disabled title='Não é possível executar esta operação para os dados abaixo'" : " title='Eliminar todos os candidatos que não foram avaliados'";
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <strong>Inscrições</strong></h1>
                </div>

                <div class="col-full m02">
                    <div class="panel-group ficha-tecnica" id="accordion">
                        <div class="painel">
                            <div class="panel-heading">
                                <a style="text-decoration:none;" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                    <h4 class="panel-title">Ficha Técnica</h4>
                                </a>
                            </div>

                            <div id="collapseOne" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <p>
                                        <i class='fa fa-book'></i>
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                    <form id='formBuscaInscritos' method='get' action='listarInscricaoProcesso.php'>
                                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO() ?>">
                                        <div class="filtro">
                                            <div class="col-md-6">
                                                <h4>Inscrição</h4>
                                                <input class="form-control" type="text" id="codigo" name="codigo" value="<?php print $filtro->getCodigo() ?>" placeholder="Digite o código">
                                            </div>
                                            <div class="col-md-5">
                                                <h4>Chamada</h4>
                                                <?php impressaoChamadaPorProcesso($processo->getPRC_ID_PROCESSO(), $filtro->getIdChamada(), $processo->PCH_ID_ULT_CHAMADA, TRUE); ?>
                                            </div>

                                            <div class="col-md-1">
                                                <h4>&nbsp;</h4>
                                                <button type="button" title="Mais filtros" id="maisFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "none" : print "block"; ?>"><span class="fa fa-plus"></span></button>
                                                <button type="button" title="Menos filtros" id="menosFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>;"><span class="fa fa-minus"></span></button>
                                            </div>

                                            <div id="filtroInterno" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>">
                                                <div class="completo m01">
                                                    <div class="col-md-6">
                                                        <h4>Nome do candidato</h4>
                                                        <input class="form-control" type="text" id="nmCandidato" name="nmCandidato" size="30" maxlength="100" value="<?php print $filtro->getNmUsuario() ?>" placeholder="Nome do candidato:">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <h4>CPF</h4>
                                                        <input class="form-control" type="text" id="nrcpf" name="nrcpf" size="14" value="<?php print $filtro->getNrCpf() ?>" placeholder="CPF:">
                                                    </div>
                                                </div>

                                                <div class="completo m01">
                                                    <div id="divEsperaPolo" style="display: none">
                                                        <span>Aguarde, Carregando...</span>
                                                    </div>

                                                    <div id="divListaPolo" class="col-md-6" style="display: none">
                                                        <label for="idPolo"><h4>Polo</h4></label>
                                                        <select class="form-control" name="idPolo" id="idPolo"></select>
                                                    </div>

                                                    <div id="divEsperaArea" style="display: none">
                                                        <span>Aguarde, Carregando...</span>
                                                    </div>

                                                    <div id="divListaArea" class="col-md-6" style="display: none">
                                                        <label for="idAreaAtuacao"><h4>Área de Atuação</h4></label>
                                                        <select class="form-control" name="idAreaAtuacao" id="idAreaAtuacao"></select>
                                                    </div>

                                                    <div id="divEsperaReservaVaga" style="display: none">
                                                        <span>Aguarde, Carregando...</span>
                                                    </div>
                                                    <div id="divListaReservaVaga" class="col-md-6" style="display: none">
                                                        <label for="idReservaVaga"><h4>Reserva de Vaga</h4></label>
                                                        <select class="form-control" name="idReservaVaga" id="idReservaVaga"></select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="completo m01">
                                                <div class="col-md-6">
                                                    <label for="tpClassificacao"><h4>Classificar por</h4></label>
                                                    <?php impressaoTpClassificacaoInsc($filtro->getTpClassificacao(), FiltroInscritoProcesso::getPadraoTpClassificacao()); ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="tpOrdenacao"><h4>Ordem</h4></label>
                                                    <?php impressaoTpOrdenacaoInsc($filtro->getTpOrdenacao(), FiltroInscritoProcesso::getPadraoTpOrdenacao()); ?>
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="tpExibSituacao"><h4>Situação</h4></label>
                                                    <?php impressaoTpExibSituacaoInsc($filtro->getTpExibSituacao(), FiltroInscritoProcesso::getPadraoTpExibicaoSit()); ?>
                                                </div>
                                            </div>

                                            <div id="divBotoes" class="campo-botoes">
                                                <button id="submeter" class="btn btn-success" type="submit">Filtrar</button>
                                                <button id="limpar" class="btn btn-default" type="button" value="Limpar">Limpar busca</button>
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

                <?php
                require_once ($CFG->rpasta . "/visao/inscricaoProcesso/fragmentoEliminarLote.php");
                LOT_fragmentoElimLote($filtro->getIdProcesso(), $filtro->getIdChamada(), $etapaVigente != NULL ? $etapaVigente->getESP_NR_ETAPA_SEL() : NULL, $qtElimLote, "perguntaElimLote");
                ?>

                <div class="col-full m01">
                    <span class="pull-left completo-mob">
                        <button title="<?php $processo->isFechado() ? print "Edital finalizado" : print "Classificar candidatos de acordo com a pontuação obtida"; ?>" <?php $processo->isFechado() ? print "disabled onclick=\"javascript: return false;\" " : print "onclick=\"javascript: window.location = '$CFG->rwww/visao/inscricaoProcesso/classificarCandidatos.php?idProcesso={$processo->getPRC_ID_PROCESSO()}'\"" ?> class="btn btn-default col-half-mob" type="button"><i class="fa fa-sort-amount-desc"></i> Classificar</button>
                        <button title="Exportar dados dos candidatos" class="btn btn-default col-half-mob" type="button" onclick="javascript: window.open('<?php print "$CFG->rwww/visao/inscricaoProcesso/exportarDados.php?idProcesso={$processo->getPRC_ID_PROCESSO()}"; ?>', '_blank')"> <i class="fa fa-mail-forward"></i> Exportar dados</button>
                        <button type="button" class="btn btn-default col-full-mob" role="button" <?php echo $tagsBtElimLote; ?> data-toggle="modal" data-target="#perguntaElimLote"> <i class="fa fa-user-times"></i> Eliminar não avaliados</button>
                    </span>

                    <span class="pull-right completo-mob">
                        <button title="Visualizar resumo das inscrições" class="btn btn-default col-full-mob" type="button" onclick="javascript: window.location.href = '<?php print "$CFG->rwww/visao/inscricaoProcesso/resumoInscProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}" ?>'"><i class="fa fa-file-text-o"></i> Resumo das inscrições</button>
                    </span>
                </div>

                <div class="col-full m02">
                    <?php
                    //imprimindo dados
                    $paginacao->imprimir();
                    ?>
                </div>

                <input class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/processo/listarProcessoAdmin.php" ?>'" value="Voltar">
            </div>
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("jquery.maskedinput");
        carregaScript("metodos-adicionaisBR");
        carregaScript("ajax");
        carregaScript("jquery.cookie");
        carregaScript("filtro");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {

            filtro_BtMaisMenos();

            $("#formBuscaInscritos").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagemInline();
                    form.submit();
                }, rules: {
                    codigo: {
                        digits: true
                    }, nrcpf: {
                        CPF: true
                    }
                }, messages: {
                }

            });
            $("#limpar").click(function () {
                // destroi cookie
                $.removeCookie('<?php print $filtro->getNmCookie() ?>');

                // limpar dados
                limparFormulario($("#formBuscaInscritos"));
            });

            //adicionar mascara
            $("#nrcpf").mask("999.999.999-99");


            //tratando gatilho de ajax para polo
            function getParamsPolo()
            {
                return {'cargaSelect': "poloChamada", 'idChamada': $("#idChamada").val()};
            }
            var gatPolo = adicionaGatilhoAjaxSelect("idChamada", getIdSelectSelecione(), "divEsperaPolo", "divListaPolo", "idPolo", "<?php print $filtro->getIdPolo() ?>", getParamsPolo, "block", false);

            //tratando gatilho de ajax para area de atuaçao
            function getParamsAreaAtu()
            {
                return {'cargaSelect': "areaAtuChamada", 'idChamada': $("#idChamada").val()};
            }
            var gatAreaAtu = adicionaGatilhoAjaxSelect("idChamada", getIdSelectSelecione(), "divEsperaArea", "divListaArea", "idAreaAtuacao", "<?php print $filtro->getIdAreaAtuacao() ?>", getParamsAreaAtu, "block", false);


            //tratando gatilho de ajax para reserva de vaga
            function getParamsReservaVaga()
            {
                return {'cargaSelect': "reservaVagaChamada", 'idChamada': $("#idChamada").val()};
            }
            var gatReservaVaga = adicionaGatilhoAjaxSelect("idChamada", getIdSelectSelecione(), "divEsperaReservaVaga", "divListaReservaVaga", "idReservaVaga", "<?php print $filtro->getIdReservaVaga() ?>", getParamsReservaVaga, "block", false);


            // gatilho para chamada
            var gatChamada = function () {
                gatPolo();
                gatAreaAtu();
                gatReservaVaga();
            }
            // adicionando gatilho
            $("#idChamada").change(gatChamada);


            function sucAvalAuto() {
                $().toastmessage('showToast', {
                    text: '<b>Avaliação automática realizada com sucesso.</b>',
                    sticky: false,
                    type: 'success', position: 'top-right'
                });
            }

            function sucClassificacao() {
                $().toastmessage('showToast', {
                    text: '<b>Classificação efetuada com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucNota() {
                $().toastmessage('showToast', {
                    text: '<b>Avaliação de candidato atualizada com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucElimLote() {
                $().toastmessage('showToast', {
                    text: '<b>Eliminação de candidatos executada com sucesso.</b>',
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
