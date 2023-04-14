<!DOCTYPE html>
<html>
    <head>     
        <title>Excluir Curso - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        //verificando passagem por get
        if (!isset($_GET['idCurso'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }
        $idCurso = $_GET['idCurso'];

        // recuperando curso
        require_once ($CFG->rpasta . "/controle/CTCurso.php");
        $curso = buscarCursoPorIdCT($idCurso);
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
                    <h1>Você está em: Cadastro > <a href="<?php print $CFG->rwww; ?>/visao/curso/listarCurso.php">Curso</a> > <strong>Excluir Curso</strong></h1>
                </div>

                <div class="col-full m02">
                    <form class="form-horizontal" id="formExcluir" method="post" action="<?php print $CFG->rwww; ?>/controle/CTCurso.php?acao=excluirCurso">
                        <input type="hidden" name="valido" value="ctcurso">
                        <input id='idCurso' type="hidden" name="idCurso" value="<?php print $curso->getCUR_ID_CURSO(); ?>">
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Código:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" disabled="true" name="idCursoTela" type="text" id="idCursoTela" size="30" maxlength="50" placeholder="Código do Curso" value="<?php print $curso->getCUR_ID_CURSO(); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control"  name="nmCurso" type="text" id="nmCurso" size="30" maxlength="30" placeholder="Nome do Curso" value="<?php print $curso->getCUR_NM_CURSO(); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoTipoCurso($curso->getTPC_ID_TIPO_CURSO()) ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Departamento:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoDepartamento($curso->getDEP_ID_DEPARTAMENTO()) ?>
                            </div>
                        </div>

                        <?php
                        require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
                        EXC_fragmentoPergExcEmPag();
                        ?>

                        <div id="divBotoes">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-danger" id="submeter" type="button" role="button" data-toggle="modal" data-target="#perguntaExclusao">Excluir</button>
                                <input class= "btn btn-default" id="btVoltar" type="button" onclick="javascript: window.location = 'listarCurso.php';" value="Voltar">
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
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            $("#formExcluir").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                }
            }
            );
            $(":input").not(":button,:submit,[type='hidden']").attr("disabled", true);

        });
    </script>
</html>

