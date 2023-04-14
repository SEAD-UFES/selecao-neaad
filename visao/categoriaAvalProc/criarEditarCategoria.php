
<!DOCTYPE html>
<html>
    <head>     
        <title>Gerenciar Categoria de Avaliação - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        // coordenador ou administrador
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null && estaLogado(Usuario::$USUARIO_COORDENADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idCategoriaAval']) && (!isset($_GET['idProcesso']) || !isset($_GET['idEtapaAval']))) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados adicionais, se necessario
        if (isset($_GET['idCategoriaAval'])) {
            // recuperando categorias
            $categoriaAval = buscarCatAvalPorIdCT($_GET['idCategoriaAval']);

            $idProcesso = $categoriaAval->getPRC_ID_PROCESSO();
            $idEtapaAval = $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC();
            $edicao = TRUE;
        } else {
            $idProcesso = $_GET['idProcesso'];
            $idEtapaAval = $_GET['idEtapaAval'];
            $edicao = FALSE;
        }

        // buscando processo
        $processo = buscarProcessoComPermissaoCT($idProcesso);

        // buscando etapa
        $etapa = buscarEtapaAvalPorIdCT($idEtapaAval, $processo->getPRC_ID_PROCESSO());

        // verificando se pode alterar a etapa
        if (!$etapa->podeAlterar() || ($edicao && $categoriaAval->isSomenteLeitura())) {
            new Mensagem("Categoria não pode ser alterada.", Mensagem::$MENSAGEM_ERRO);
        }
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong><?php if (!$edicao) { ?> Cadastrar Categoria de Avaliação <?php } else { ?> Editar Categoria de Avaliação <?php } ?></strong></h1>
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
                                        <i class="fa fa-book"></i>
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class='barra'></separador>
                                    <b>Etapa:</b> <?php print $etapa->getNomeEtapa(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>                

                <div class="col-full m02">

                    <div id="divErCriarCategoria" style="display: none" class="alert alert-danger">
                        Você já cadastrou uma categoria com os parâmetros informados. Por favor, atualize o item existente.
                    </div>

                    <form class="form-horizontal m02" id="formCadastro" method="post" action="<?php print $CFG->rwww; ?>/controle/CTNotas.php?acao=<?php !$edicao ? print "criarCategoriaAval" : print "editarCategoriaAval" ?>">
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" name="idEtapaAval" value="<?php print $etapa->getEAP_ID_ETAPA_AVAL_PROC(); ?>">

                        <?php if ($edicao) { ?>
                            <input type="hidden" name="idCategoriaAval" value="<?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?>">
                        <?php } ?>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoTipoCategoriaAval(array(), $edicao ? $categoriaAval->getCAP_TP_CATEGORIA() : NULL, $edicao); ?>
                            </div>
                        </div>
                        <div id="divNaoAutomatizada" style="display: none">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Avaliação:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoTipoAvalCategoriaAval($edicao ? $categoriaAval->getCAP_TP_AVALIACAO() : NULL); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Pontuação Máx:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" class="tudo-normal" type='text' name='pontuacaoMax' id='pontuacaoMax' size="6" maxlength="6" value="<?php $edicao ? print $categoriaAval->getVlNotaMaxFormatada() : print ""; ?>">
                                </div>
                            </div>

                            <div id="divExclusiva" style="display: none" class="form-group">
                                <label title="Se 'Exclusiva' é 'Sim', então o sistema considerará apenas o primeiro item da categoria pontuado pelo candidato." class="control-label col-xs-12 col-sm-4 col-md-4">Exclusiva:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoRadioSimNao("catExclusiva", $edicao ? $categoriaAval->getCAP_CATEGORIA_EXCLUSIVA() : NULL); ?>
                                    <label for="catExclusiva" class="error" style="display: none"></label>
                                </div>
                            </div>
                        </div>
                        <div id="divBotoes" class="m02">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                    <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>&idEtapaAval=<?php print $etapa->getEAP_ID_ETAPA_AVAL_PROC(); ?>';" value="Voltar">
                                </div>
                            </div>
                        </div>
                        <div id="divMensagem" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                        <br/>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("ajax");
        carregaScript("jquery.price_format");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            function habilitaExclusividade() {
                return $("#tpAvalCategoria").val() == '<?php print CategoriaAvalProc::$AVAL_AUTOMATICA; ?>';
            }

            // gerenciando div exclusiva
            var gat = adicionaGatilhoAddDivSelect("tpAvalCategoria", habilitaExclusividade, "divExclusiva");
            gat();

            // travando avaliaçao 
            var funcProcessaTipo = function () {
                // caso de ser automatizado: nada a mostrar
                var tipoAutomatizado = '<?php print CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA; ?>';
                if (vazioOuNulo($("#tpCategoriaAval").val()) || $("#tpCategoriaAval").val() == tipoAutomatizado)
                {
                    //ocultaDiv Nao automatizada
                    $("#divNaoAutomatizada").hide();
                } else {
                    // mostra div
                    $("#divNaoAutomatizada").show();
                }

                var vetAdmiteAuto = <?php print strArrayJavaScript(CategoriaAvalProc::getListaTpAdmiteAvalAuto()); ?>

                if (vazioOuNulo($("#tpCategoriaAval").val()) || vetAdmiteAuto.indexOf($("#tpCategoriaAval").val()) != -1)
                {
                    // ambas as avaliaçoes
                    $("#tpAvalCategoria").attr("disabled", false);
                    $("#tpAvalCategoria").change();
                } else {
                    // apenas avaliaçao manual
                    $("#tpAvalCategoria").val('<?php print CategoriaAvalProc::$AVAL_MANUAL ?>');
                    $("#tpAvalCategoria").attr("disabled", true);
                    $("#tpAvalCategoria").change();
                }
            };
            $("#tpCategoriaAval").change(funcProcessaTipo);
            funcProcessaTipo();


            $("#formCadastro").validate({
                submitHandler: function (form) {
                    // tentando validaçao de item e area
                    $("#divErCriarCategoria").hide();

                    $.ajax({
                        type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?val=categoriaAval",
                        data: {"idProcesso": '<?php print $processo->getPRC_ID_PROCESSO(); ?>', "idEtapaAval": '<?php print $etapa->getEAP_ID_ETAPA_AVAL_PROC(); ?>', "tpCategoriaAval": $("#tpCategoriaAval").val(), "tpAvalCategoria": $("#tpAvalCategoria").val(), "catExclusiva": $("input[name='catExclusiva']:checked").val(), "edicao": <?php $edicao ? print "true" : print "false"; ?>, "idCategoriaAval":<?php $edicao ? print $categoriaAval->getCAP_ID_CATEGORIA_AVAL() : print "''"; ?>},
                        dataType: "json",
                        success: function (json) {
                            // caso validou
                            if (json['situacao']) {
                                // processa o submit
                                // 
                                //evitar repetiçao do botao
                                mostrarMensagem();
                                $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                                form.submit();
                            } else {
                                // caso de mensagem direto do sistema
                                if (json['msg'] != "")
                                {
                                    alert(json['msg']);
                                    return false;

                                } else {
                                    // exibe msg de erro e aborta operaçao de submit
                                    $("#divErCriarCategoria").show();
                                    return false;
                                }
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nA página será recarregada.\n\n";
                            msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                            // exibindo mensagem e reiniciando pagina
                            alert(msg);
                            location.reload();
                            return false;
                        }
                    });
                },
                rules: {
                    tpCategoriaAval: {
                        required: true
                    }, tpAvalCategoria: {
                        required: true
                    }, pontuacaoMax: {
                        required: true,
                        min: 0.01
                    }, catExclusiva: {
                        required: function () {
                            return habilitaExclusividade();
                        }
                    }}, messages: {
                }
            });


            addMascaraDecimal("pontuacaoMax");

        });
    </script>
</html>

