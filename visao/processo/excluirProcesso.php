<!DOCTYPE html>
<html>
    <head>  
        <title>Excluir Processo - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idProcesso'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // recuperando processo
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);

        // verificando permissão de exclusão
        if (!$processo->permiteExclusao()) {
            new Mensagem("Não é permitido excluir este processo.", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <strong>Excluir Processo</strong></h1>
                </div>

                <div class="col-full m02">

                    <form class="form-horizontal" id="formExcluir" method="post" action="<?php print $CFG->rwww ?>/controle/CTProcesso.php?acao=excluirProcesso">
                        <input type="hidden" name="valido" value="ctprocesso">
                        <input type="hidden" name="idProcesso" id="idProcesso" value="<?php echo $processo->getPRC_ID_PROCESSO(); ?>">

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Edital:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span type="text" class="input uneditable-input"><?php echo $processo->getNumeracaoEdital(); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Atribuição:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span class="input uneditable-input"><?php echo $processo->TIC_NM_TIPO_CARGO; ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Curso:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span class="input uneditable-input"><?php echo $processo->CUR_NM_CURSO; ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Início:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span class="input uneditable-input"><?php echo $processo->getPRC_DT_INICIO(); ?></span>
                            </div>
                        </div>

                        <?php
                        require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
                        EXC_fragmentoPergExcEmPag();
                        ?>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button type="button" class="btn btn-danger" role="button" data-toggle="modal" data-target="#perguntaExclusao">Excluir</button>
                                <input class="btn btn-default" type="button" onclick="javascript: window.location = 'listarProcessoAdmin.php'" value="Voltar">
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
        });
    </script>
</html>