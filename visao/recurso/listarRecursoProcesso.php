<!DOCTYPE html>
<html>
    <head>     
        <title>Visualizar Recursos contra o Edital - Seleção EAD</title>
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
        require_once ($CFG->rpasta . "/util/filtro/FiltroRecurso.php");
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

        // etapa em recurso
        $etapaRecurso = buscarEtapaEmRecursoCT($processo->PCH_ID_ULT_CHAMADA);

        //criando filtro
        $filtro = new FiltroRecurso($_GET, 'listarRecursoProcesso.php', NULL, NULL, $processo->getPRC_ID_PROCESSO());

        //criando objeto de paginação
        $paginacao = new Paginacao('tabelaRecursoPorFiltro', 'contarRecursoPorFiltroCT', $filtro);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <strong>Recursos</strong></h1>
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
                                    <form id='formBuscaRecursos' method='get' action='listarRecursoProcesso.php'>
                                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO() ?>">

                                        <div class="filtro">
                                            <div class="col-md-5">
                                                <h4>Chamada</h4>
                                                <?php impressaoChamadaPorProcesso($processo->getPRC_ID_PROCESSO(), $filtro->getIdChamada(), $processo->PCH_ID_ULT_CHAMADA, TRUE); ?>
                                            </div>
                                            <div class="col-md-3">
                                                <h4>Etapa</h4>
                                                <?php impressaoEtapaAvalPorProc($processo->getPRC_ID_PROCESSO(), FALSE, $filtro->getIdEtapaTela(), $etapaRecurso != NULL ? $etapaRecurso->getEAP_ID_ETAPA_AVAL_PROC() : NULL); ?>
                                            </div>
                                            <div class="col-md-3">
                                                <h4>Situação</h4>
                                                <?php impressaoSitRecurso($filtro->getStSituacao()); ?>
                                            </div>

                                            <div class="col-md-1">
                                                <h4>&nbsp;</h4>
                                                <button type="button" title="Mais filtros" id="maisFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "none" : print "block"; ?>"><span class="fa fa-plus"></span></button>
                                                <button type="button" title="Menos filtros" id="menosFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>;"><span class="fa fa-minus"></span></button>
                                            </div>

                                            <div id="filtroInterno" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>">
                                                <div class="completo m01">
                                                    <div class="col-md-5">
                                                        <h4>Recurso</h4>
                                                        <input class="form-control" type="text" class="span2" id="idRecurso" name="idRecurso" size="15" maxlength="10" value="<?php print $filtro->getIdRecurso() ?>" placeholder="Código do recurso">
                                                    </div>
                                                    <div class="col-md-7">
                                                        <h4>Inscrição</h4>
                                                        <input class="form-control" type="text" class="span2" id="ordemInsc" name="ordemInsc" size="15" maxlength="10" value="<?php print $filtro->getOrdemInsc() ?>" placeholder="Digite o código">
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="divBotoes" class="campo-botoes">
                                                <button id="submeter" class="btn btn-success" type="submit">Filtrar</button>
                                                <button id="limpar" class="btn btn-default" type="button" value="Limpar">Limpar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full">
                    <button title="Exportar recursos" class="btn btn-default col-half-mob" type="button" onclick="javascript: window.open('<?php print "$CFG->rwww/visao/inscricaoProcesso/exportarDados.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&rec=true"; ?>', '_blank')"> <i class ="fa fa-mail-forward"></i> Exportar recursos</button>
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
        carregaScript("ajax");
        carregaScript("jquery.cookie");
        carregaScript("filtro");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            filtro_BtMaisMenos();

            $("#formBuscaRecursos").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagemInline();
//                    $('#pergunta').modal('show');
                    form.submit();
                }, rules: {
                    ordemInsc: {
                        digits: true
                    },
                    idRecurso: {
                        digits: true
                    },
                    idChamada: {
                        required: true
                    }
                }, messages: {
                }

            });

            $("#limpar").click(function () {
                // destroi cookie
                $.removeCookie('<?php print $filtro->getNmCookie() ?>');

                limparFormulario($("#formBuscaRecursos"));
            });

            function sucRecurso() {
                $().toastmessage('showToast', {
                    text: '<b>Recurso respondido com sucesso.</b>',
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
