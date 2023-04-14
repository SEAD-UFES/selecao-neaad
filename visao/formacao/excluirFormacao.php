<!DOCTYPE html>
<html>
    <head>     
        <title>Excluir Formação - Seleção EAD</title>
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
        if (!isset($_GET['idFormacao'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // recuperando formaçao 
        $formacao = buscarFormacaoPorIdFormacaoCT($_GET['idFormacao'], getIdUsuarioLogado());

        // verificando permissao
        if (!permiteAlteracaoCurriculoCT($formacao->getCDT_ID_CANDIDATO())) {
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
                    <h1>Você está em: Candidato > <a href="listarFormacao.php">Formação</a> > <strong>Excluir Formação</strong></h1>
                </div>

                <div class="col-full m02">
                    <form id="formExcluir" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTCurriculo.php?acao=excluirFormacao" ?>'>
                        <input type="hidden" name="valido" value="ctcurriculo">
                        <input type="hidden" name="idFormacao" value="<?php print $formacao->getFRA_ID_FORMACAO() ?>">
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Período:</label>                            
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" type="text" id="dsPeriodo" name="dsPeriodo" size="50" maxlength="100" value="<?php print $formacao->getDsPeriodo() ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Formação:</label>                            
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span><?php impressaoTipoFormacao($formacao->getTPC_ID_TIPO_CURSO()); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome da Instituição:</label>                            
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" type="text" id="nmInstituicao" name="nmInstituicao" size="50" maxlength="100" value="<?php print $formacao->getFRA_NM_INSTITUICAO() ?>">
                            </div>
                        </div>

                        <?php
                        require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
                        EXC_fragmentoPergExcEmPag();
                        ?>

                        <div id="divCurso" style="display: <?php ($formacao->getFRA_ID_PAIS() != Pais::$PAIS_BRASIL) ? print "block" : print "none" ?>">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Curso:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="nmCurso" name="nmCurso" size="50" maxlength="50" value="<?php print $formacao->getFRA_NM_CURSO() ?>">
                                </div>
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-danger" id="submeter" type="button" role="button" data-toggle="modal" data-target="#perguntaExclusao">Excluir</button>
                                <button class="btn btn-default" type="button" onclick="javascript: window.location = 'listarFormacao.php';">Voltar</button>
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
                    tpFormacao: {
                        required: true
                    },
                    nmInstituicao: {
                        required: true

                    },
                    nmCurso: {
                        required: function (element) {
                            return ativaCurso($("#tpFormacao").val());
                        }

                    }}, messages: {
                }
            }
            );

            // call back que ativa exibicao de curso
            function ativaCurso(valor)
            {
                var vals = <?php print TipoCurso::getListaAdmiteCurso(); ?>;
                return vals.indexOf(valor) != -1;
            }

            // criando funçao gatilho: Trata curso, carga horaria e detalhes do curso
            var funcaoGatilhoTpFormacao = function () {
                var valor = $("#tpFormacao").val();
                // trata curso
                if (ativaCurso(valor))
                {
                    $("#divCurso").show();
                } else {
                    $("#divCurso").hide();
                }
            };

            // Incluindo gatilho para tpFormacao: Trata curso
            $("#tpFormacao").change(funcaoGatilhoTpFormacao);
        });
    </script>
</html>