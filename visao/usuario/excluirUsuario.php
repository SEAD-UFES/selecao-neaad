<!DOCTYPE html>
<html>
    <head>  
        <title>Excluir Usuário - Seleção EAD</title>
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
        ?>
        <?php
        //verificando passagem por get
        if (!isset($_GET['idUsuario'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        //recuperando usuário
        require_once ($CFG->rpasta . "/controle/CTUsuario.php");
        $idUsuario = $_GET['idUsuario'];
        $objUsuario = buscarUsuarioPorIdCT($idUsuario);

        // verificando se pode excluir
        if (!permiteExclusaoUsuCT($idUsuario, $objUsuario->getUSR_TP_USUARIO())) {
            $msgExcluir = "Este usuário não pode ser excluído porque já realizou alguma operação com necessidade de registro.";
            new Mensagem("$msgExcluir", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: Cadastros > <a href="<?php print $CFG->rwww; ?>/visao/usuario/listarUsuario.php">Usuário</a> > <strong>Excluir Usuário</strong></h1>
                </div>

                <div class="col-full m02">
                    <?php
                    // verificando se e o login atual
                    if ($objUsuario->getUSR_ID_USUARIO() == getIdUsuarioLogado()) {
                        New Mensagem("Você não pode excluir seu usuário", Mensagem::$MENSAGEM_ERRO);
                    }
                    ?>
                    <form class="form-horizontal" id="formExcluir" method="post" action="<?php print $CFG->rwww ?>/controle/CTUsuario.php?acao=excluirUsuario">
                        <input type="hidden" name="valido" value="ctusuario">
                        <input type="hidden" name="idUsuario" id="idUsuario" value="<?php print $objUsuario->getUSR_ID_USUARIO(); ?>">

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Código:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span type="text" class="input uneditable-input"><?php print $objUsuario->getUSR_ID_USUARIO(); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Nome completo:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span class="input uneditable-input"><?php print $objUsuario->getUSR_DS_NOME(); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Tipo:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span class="input uneditable-input"><?php print Usuario::getDsTipo($objUsuario->getUSR_TP_USUARIO()); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Login:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span class="input uneditable-input"><?php print $objUsuario->getUSR_DS_LOGIN(); ?></span>
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
                                <input class="btn btn-default" type="button" onclick="javascript: window.location = 'listarUsuario.php'" value="Voltar">
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