<!DOCTYPE html>
<html>
    <head>     
        <title>Manter Critério de Desempate - Seleção EAD</title>
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
        if (!isset($_GET['idMacroConfProc']) && (!isset($_GET['idProcesso']) || !isset($_GET['idEtapaAval']))) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados adicionais, se necessario
        if (isset($_GET['idMacroConfProc'])) {
            // recuperando macro
            $macroConfProc = buscarMacroConfProcPorIdCT($_GET['idMacroConfProc']);

            $idProcesso = $macroConfProc->getPRC_ID_PROCESSO();
            $idEtapaAval = $macroConfProc->getEAP_ID_ETAPA_AVAL_PROC();
            $edicao = TRUE;
        } else {
            $idProcesso = $_GET['idProcesso'];
            $idEtapaAval = $_GET['idEtapaAval'];
            $edicao = FALSE;
        }

        // buscando processo
        $processo = buscarProcessoComPermissaoCT($idProcesso);

        // tratando permissões
        if ($idEtapaAval != NULL && $idEtapaAval != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL) {
            // buscando etapa
            $etapa = buscarEtapaAvalPorIdCT($idEtapaAval, $processo->getPRC_ID_PROCESSO());

            // verificando se pode alterar a etapa
            if (!$etapa->podeAlterar()) {
                new Mensagem("Etapa não pode ser alterada.", Mensagem::$MENSAGEM_ERRO);
            }
            $idEtapaAvalTela = $etapa->getEAP_ID_ETAPA_AVAL_PROC();
        } else {
            // validando edicao de processo
            if (!EtapaAvalProc::podeAlterarUltimaEtapa($idProcesso)) {
                new Mensagem("Dados não podem ser alterados.", Mensagem::$MENSAGEM_ERRO);
            }
            $idEtapaAvalTela = MacroConfProc::$ID_ETAPA_RESULTADO_FINAL;
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong><?php if (!$edicao) { ?> Cadastrar Critério de Desempate <?php } else { ?> Editar Crit. de Desempate <?php } ?></strong></h1>
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
                                        <?php print $processo->getHTMLDsEditalCompleta(); ?>
                                        <?php if ($idEtapaAvalTela != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL) { ?> <separador class='barra'></separador>
                                        <b>Etapa:</b> <?php print $etapa->getNomeEtapa(); ?>
                                    <?php } ?>
                                    <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full m02">
                    <div id="divErCriarCriterio" style="display: none" class="alert alert-danger">
                        Você já cadastrou um critério similar neste edital. Por favor, edite o item existente.
                    </div>

                    <form class="form-horizontal" id="formCadastro" method="post" action="<?php print $CFG->rwww; ?>/controle/CTNotas.php?acao=<?php !$edicao ? print "criarMacroConfProc" : print "editarMacroConfProc" ?>">
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" name="idEtapaAval" value="<?php print $idEtapaAvalTela; ?>">
                        <input type="hidden" name="tipoMacro" value="<?php print MacroConfProc::$TIPO_CRIT_DESEMPATE; ?>">

                        <?php if ($edicao) { ?>
                            <input type="hidden" name="idMacroConfProc" value="<?php print $macroConfProc->getMCP_ID_MACRO_CONF_PROC(); ?>">
                        <?php } ?>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Critério:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoTipoMacro(MacroConfProc::$TIPO_CRIT_DESEMPATE, $edicao ? $macroConfProc->getMCP_DS_MACRO() : NULL, $edicao); ?>
                            </div>
                        </div>

                        <div id='divParametros'>

                        </div>

                        <script id='divScriptParametros' type="text/javascript">
                        </script>

                        <script id='divParamChave' type="text/javascript">
                            function retornaParamChave() {
                                return eval($("#<?php print ParamMacro::$ID_CAMPO_SUMARIZA_PARAM_CHAVES ?>").html());
                            }
                        </script>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>&idEtapaAval=<?php print $idEtapaAvalTela; ?>';" value="Voltar">
                            </div>
                        </div>
                    </form>
                </div>

                <div id="divMensagem" class="col-full" style="display:none">
                    <div class="alert alert-info">
                        Aguarde o processamento...
                    </div>
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

            // definindo funcao de carga de parametros
            var gatTipo = function () {
                if ($("#idTipoMacro").val() == "")
                {
                    // limpa div 
                    $("#divParametros").html("");
                    $("#divScriptParametros").html("");
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: getURLServidor() + "/controle/CTAjax.php?obterHTML=paramMacroConfProc",
                    data: {"idProcesso": '<?php print $processo->getPRC_ID_PROCESSO(); ?>', "idEtapaAval": '<?php print $idEtapaAvalTela; ?>', "tipoMacro": '<?php print MacroConfProc::$TIPO_CRIT_DESEMPATE; ?>', "idTipoMacro": $("#idTipoMacro").val(), "edicao": <?php $edicao ? print "true" : print "false"; ?>, "idMacroConfProc":<?php $edicao ? print $macroConfProc->getMCP_ID_MACRO_CONF_PROC() : print "''"; ?>},
                    dataType: "json",
                    success: function (json) {
                        // caso de html ok
                        if (json['situacao']) {
                            $("#divParametros").html(json['html']);
                            $("#divScriptParametros").html(json['script']);
                            eval(json['script']);
                        } else {
                            // caso de mensagem direto do sistema
                            if (json['msg'] != "")
                            {
                                alert(json['msg']);
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
            };

            $("#idTipoMacro").change(gatTipo);
            gatTipo();

            $("#formCadastro").validate({
                submitHandler: function (form) {
                    // tentando validaçao de item e area
                    $("#divErCriarCriterio").hide();
                    $.ajax({
                        type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?val=macroConfProc",
                        data: {"idProcesso": '<?php print $processo->getPRC_ID_PROCESSO(); ?>', "idEtapaAval": '<?php print $idEtapaAvalTela; ?>', "tipoMacro": '<?php print MacroConfProc::$TIPO_CRIT_DESEMPATE; ?>', "idTipoMacro": $("#idTipoMacro").val(), "<?php print ParamMacro::$NM_PARAM_CHAVES ?>": retornaParamChave(), "edicao": <?php $edicao ? print "true" : print "false"; ?>, "idMacroConfProc":<?php $edicao ? print $macroConfProc->getMCP_ID_MACRO_CONF_PROC() : print "''"; ?>},
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
                                    $("#divErCriarCriterio").show();
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
                    idTipoMacro: {
                        required: true
                    }
                }, messages: {
                }
            });
        });
    </script>
</html>

