<!DOCTYPE html>
<html>
    <head>     
        <title>Gerenciar Atuação - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTCurriculo.php");
        include_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // verificando permissao
        if (!permiteAlteracaoCurriculoCT(buscarIdCandPorIdUsuCT(getIdUsuarioLogado()))) {
            new Mensagem("Você não pode alterar seu currículo enquanto está concorrendo a algum edital.", Mensagem::$MENSAGEM_ERRO);
        }

        // verificando se e um caso de edicao
        $edicao = isset($_GET['idAtuacao']);
        if ($edicao) {
            $atuacao = buscarAtuacaoPorIdCT($_GET['idAtuacao'], getIdUsuarioLogado());
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
                    <h1>Você está em: Candidato > <a href="listarAtuacao.php">Currículo</a> > <strong><?php if (!$edicao) { ?>Adicionar Atuação<?php } else { ?>Editar Atuação<?php } ?></strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full m02">
                    <div id="divErCriarAtuacao" style="display: none" class="alert alert-danger">
                        Você já cadastrou uma atuação com os parâmetros informados. Por favor, atualize o item existente.
                    </div>

                    <?php if (!$edicao) { ?>
                        <form id="formCadastro" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTCurriculo.php?acao=criarAtuacao" ?>'>

                        <?php } else { ?>
                            <form id="formEdicao" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTCurriculo.php?acao=editarAtuacao" ?>'>
                                <input type="hidden" name="idAtuacao" value="<?php print $atuacao->getATU_ID_ATUACAO(); ?>">
                            <?php } ?>
                            <input type="hidden" name="valido" value="ctcurriculo">

                            <div class="form-group m02">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <span><?php
                                        if (!$edicao) {
                                            impressaoTipoAtuacao();
                                        } else {
                                            impressaoTipoAtuacao($atuacao->getATU_TP_ITEM(), $edicao);
                                        }
                                        ?></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Grande área:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <span><?php
                                        if (!$edicao) {
                                            impressaoArea(NULL, "idAreaConh");
                                        } else {
                                            impressaoArea($atuacao->getATU_ID_AREA_CONH(), "idAreaConh", $edicao);
                                        }
                                        ?></span>
                                    <div id="divEsperaSubarea" style="display: none">
                                        <span>Aguarde, Carregando...</span>
                                    </div>
                                </div>
                            </div>

                            <div id="divListaSubarea" style="display: none">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Área:</label>

                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <select class="form-control" <?php $edicao ? print "disabled" : "" ?> name="idSubareaConh" id="idSubareaConh"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div style="display: block" id="divQuantidade">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Quantidade:</label>
                                </div>
                                <div style="display: none" id="divQuantidadeAnos">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Quantidade (Em Anos):</label>
                                </div>
                                <div style="display: none" id="divQuantidadeSemestres">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Quantidade (Em Semestres):</label>
                                </div>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="qtde" name="qtde" size="5" maxlength="5" value="<?php $edicao ? print $atuacao->getATU_QT_ITEM() : print ""; ?>">
                                </div>
                            </div>

                            <div id="divBotoes" class="m02">
                                <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                    <button class="btn btn-default" type="button" onclick="javascript: window.location = 'listarAtuacao.php';">Voltar</button>
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
        carregaScript("additional-methods");
        carregaScript("jquery.mask");
        carregaScript("ajax");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {

            // tratando gatilho de ajax para subarea
            function getParamsSubarea()
            {
                return {'cargaSelect': "areaConhecimento", 'idArea': $("#idAreaConh").val()};
            }
            adicionaGatilhoAjaxSelect("idAreaConh", getIdSelectSelecione(), "divEsperaSubarea", "divListaSubarea", "idSubareaConh", <?php $edicao ? print "\"'{$atuacao->getATU_ID_SUBAREA_CONH()}'\"" : print "''" ?>, getParamsSubarea);

            // criando gatilho para exibicao de label para quantidade
            function processaExibicaoQtdeAnos(valor)
            {
                var vetAnos = <?php print strArrayJavaScript(array_keys(Atuacao::getListaTipoDsTipo(Atuacao::$LABEL_EM_ANOS))); ?>;
                return vetAnos.indexOf(valor) !== -1;
            }

            function processaExibicaoQtdeSemestres(valor)
            {
                var vetSemestres = <?php print strArrayJavaScript(array_keys(Atuacao::getListaTipoDsTipo(Atuacao::$LABEL_EM_SEMESTRES))); ?>;
                return vetSemestres.indexOf(valor) !== -1;
            }

            var funcaoGatilhoTipo = function ()
            {
                // tratando caso de label
                if (processaExibicaoQtdeAnos($("#tpAtuacao").val()))
                {
                    $("#divQuantidade").hide();
                    $("#divQuantidadeSemestres").hide();
                    $("#divQuantidadeAnos").show();
                } else if (processaExibicaoQtdeSemestres($("#tpAtuacao").val())) {
                    $("#divQuantidadeAnos").hide();
                    $("#divQuantidade").hide();
                    $("#divQuantidadeSemestres").show();
                } else {
                    $("#divQuantidadeAnos").hide();
                    $("#divQuantidadeSemestres").hide();
                    $("#divQuantidade").show();
                }

            };
            $("#tpAtuacao").change(funcaoGatilhoTipo);

            funcaoGatilhoTipo();

            //validando form
            $("#formCadastro").validate({
                submitHandler: function (form) {
                    // tentando validaçao de item e area
                    $("#divErCriarAtuacao").hide();
                    $.ajax({
                        type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?val=atuacao",
                        data: {"tpAtuacao": $("#tpAtuacao").val(), "idAreaConh": $("#idAreaConh").val(), "idSubareaConh": $("#idSubareaConh").val(), "idUsuario": getIdUsuarioLogado()},
                        dataType: "json",
                        success: function (json) {
                            // caso validou
                            if (json) {
                                // processa o submit
                                // 
                                //evitar repetiçao do botao
                                mostrarMensagem();
                                form.submit();
                            } else {
                                // exibe msg de erro e aborta operaçao de submit
                                $("#divErCriarAtuacao").show();
                                return false;
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
                    tpAtuacao: {
                        required: true
                    },
                    idAreaConh: {
                        required: true
                    },
                    idSubareaConh: {
                        required: true
                    },
                    qtde: {
                        required: true,
                        min: 1
                    }}
            }
            );

            //validando form de edicao
            $("#formEdicao").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    qtde: {
                        required: true,
                        min: 1
                    }}
            }
            );

            //criando máscaras
            $("#qtde").mask("M9999", {translation: {'M': {pattern: /[1-9]/}}});
        });
    </script>
</html>