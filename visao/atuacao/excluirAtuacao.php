<!DOCTYPE html>
<html>
    <head>     
        <title>Excluir Atuação - Seleção EAD</title>
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

        //verificando passagem por get
        if (!isset($_GET['idAtuacao'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // recuperando atuacao 
        $atuacao = buscarAtuacaoPorIdCT($_GET['idAtuacao'], getIdUsuarioLogado());

        // verificando permissao
        if (!permiteAlteracaoCurriculoCT($atuacao->getCDT_ID_CANDIDATO())) {
            new Mensagem("Você não pode alterar seu currículo enquanto está concorrendo a algum edital.", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: Candidato > <a href="listarAtuacao.php">Atuação</a> > <strong>Excluir Atuação</strong></h1>
                </div>

                <div class="col-full m02">

                    <form id="formExcluir" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTCurriculo.php?acao=excluirAtuacao" ?>'>
                        <input type="hidden" name="valido" value="ctcurriculo">
                        <input type="hidden" name="idAtuacao" value="<?php print $atuacao->getATU_ID_ATUACAO(); ?>">

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span><?php
                                    impressaoTipoAtuacao($atuacao->getATU_TP_ITEM(), true);
                                    ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Grande área:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span><?php
                                    impressaoArea($atuacao->getATU_ID_AREA_CONH(), "idAreaConh", true);
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
                                    <select class="form-control" disabled name="idSubareaConh" id="idSubareaConh"></select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div style="display: <?php !Atuacao::exibeLabelAnos($atuacao->getATU_TP_ITEM()) && !Atuacao::exibeLabelSemestres($atuacao->getATU_TP_ITEM()) ? print "block" : print "none"; ?>" id="divQuantidade">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Quantidade:</label>
                            </div>
                            <div style="display: <?php Atuacao::exibeLabelAnos($atuacao->getATU_TP_ITEM()) ? print "block" : print "none"; ?>" id="divQuantidadeAnos">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Quantidade (Em Anos):</label>
                            </div>
                            <div style="display: <?php Atuacao::exibeLabelSemestres($atuacao->getATU_TP_ITEM()) ? print "block" : print "none"; ?>" id="divQuantidadeAnos">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Quantidade (Em Semestres):</label>
                            </div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" disabled type="text" id="qtde" name="qtde" size="5" maxlength="5" value="<?php print $atuacao->getATU_QT_ITEM(); ?>">
                            </div>
                        </div>

                        <?php
                        require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
                        EXC_fragmentoPergExcEmPag();
                        ?>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-danger" id="submeter" type="button" role="button" data-toggle="modal" data-target="#perguntaExclusao">Excluir</button>
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
        carregaScript("ajax");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {

            // Bloquear campos de ediçao
            $(":input").not(":button,:hidden").attr("disabled", true);

            //validando form
            $("#formExcluir").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                }, messages: {
                }
            }
            );

            // tratando gatilho de ajax para subarea
            function getParamsSubarea()
            {
                return {'cargaSelect': "areaConhecimento", 'idArea': $("#idAreaConh").val()};
            }
            adicionaGatilhoAjaxSelect("idAreaConh", getIdSelectSelecione(), "divEsperaSubarea", "divListaSubarea", "idSubareaConh", <?php print "\"'{$atuacao->getATU_ID_SUBAREA_CONH()}'\""; ?>, getParamsSubarea);
        });
    </script>
</html>