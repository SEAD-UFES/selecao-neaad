<!DOCTYPE html>
<html>
    <head>     
        <title>Manter publicação - Seleção EAD</title>
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
        $edicao = isset($_GET['idPublicacao']);
        if ($edicao) {
            $publicacao = buscarPublicacaoPorIdCT($_GET['idPublicacao'], getIdUsuarioLogado());
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
                    <h1>Você está em: Candidato > <a href="listarPublicacao.php">Currículo</a> > <strong><?php if (!$edicao) { ?>Adicionar Publicação <?php } else { ?>Editar Publicação<?php } ?></strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full m02">

                    <div id="divErCriarPublicacao" style="display: none" class="alert alert-danger">
                        Você já cadastrou uma publicação com os parâmetros informados. Por favor, atualize o item existente.
                    </div>

                    <?php if (!$edicao) { ?>
                        <form id="formCadastro" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTCurriculo.php?acao=criarPublicacao" ?>'>
                        <?php } else { ?>
                            <form id="formEdicao" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTCurriculo.php?acao=editarPublicacao" ?>'>
                                <input type="hidden" name="idPublicacao" value="<?php print $publicacao->getPUB_ID_PUBLICACAO(); ?>">
                            <?php } ?>
                            <input type="hidden" name="valido" value="ctcurriculo">

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php
                                    if (!$edicao) {
                                        impressaoTipoPublicacao();
                                    } else {
                                        impressaoTipoPublicacao($publicacao->getPUB_TP_ITEM(), $edicao);
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Grande área:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php
                                    if (!$edicao) {
                                        impressaoArea(NULL, "idAreaConh");
                                    } else {
                                        impressaoArea($publicacao->getPUB_ID_AREA_CONH(), "idAreaConh", $edicao);
                                    }
                                    ?>
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
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">N° de Publicações:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="qtde" name="qtde" size="5" maxlength="5" value="<?php $edicao ? print $publicacao->getPUB_QT_ITEM() : print ""; ?>">
                                </div>
                            </div>

                            <div id="divBotoes" class="m02">
                                <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                    <button class="btn btn-default" type="button" onclick="javascript: window.location = 'listarPublicacao.php';">Voltar</button>
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
            //validando form
            $("#formCadastro").validate({
                submitHandler: function (form) {
                    // tentando validaçao de item e area
                    $("#divErCriarPublicacao").hide();
                    $.ajax({
                        type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?val=publicacao",
                        data: {"tpPublicacao": $("#tpPublicacao").val(), "idAreaConh": $("#idAreaConh").val(), "idSubareaConh": $("#idSubareaConh").val(), "idUsuario": getIdUsuarioLogado()},
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
                                $("#divErCriarPublicacao").show();
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
                    tpPublicacao: {
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
                    }}, messages: {
                }
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
                    }}, messages: {
                }
            }
            );

            //criando máscaras
            $("#qtde").mask("M9999", {translation: {'M': {pattern: /[1-9]/}}});

            // tratando gatilho de ajax para subarea
            function getParamsSubarea()
            {
                return {'cargaSelect': "areaConhecimento", 'idArea': $("#idAreaConh").val()};
            }
            adicionaGatilhoAjaxSelect("idAreaConh", getIdSelectSelecione(), "divEsperaSubarea", "divListaSubarea", "idSubareaConh", <?php $edicao ? print "\"'{$publicacao->getPUB_ID_SUBAREA_CONH()}'\"" : print "'NULL'" ?>, getParamsSubarea);

        });
    </script>
</html>