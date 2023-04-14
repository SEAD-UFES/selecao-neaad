<!DOCTYPE html>
<html>
    <head>  
        <title>Alterar Senha do Usuário - Seleção EAD</title>
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

        // verificando se pode reiniciar senha
        if (!$objUsuario->isComunidadeExterna()) {
            new Mensagem("Usuário pertencente ao Login Único da UFES. Sua senha deve ser alterada em <a target='_blank' href='http://senha.ufes.br'>http://senha.ufes.br</a>.", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: Cadastro > <a href="<?php print $CFG->rwww; ?>/visao/usuario/listarUsuario.php">Usuário</a> > <strong>Alterar senha</strong></h1>
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
                                        <i class="fa fa-user"></i>
                                        <b>Nome:</b> <?php print $objUsuario->getUSR_DS_NOME(); ?> <separador class='barra'></separador> 
                                    <b>Tipo:</b> <?php print Usuario::getDsTipo($objUsuario->getUSR_TP_USUARIO()); ?> <separador class='barra'></separador> 
                                    <b>Login:</b> <?php print $objUsuario->getUSR_DS_LOGIN(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full m02">
                    <?php
                    // verificando se e o login atual
                    if ($objUsuario->getUSR_ID_USUARIO() == getIdUsuarioLogado()) {
                        ?>
                        <div class="alert alert-warning">Atenção: Você está alterando a senha de seu acesso!</div>
                    <?php }
                    ?>
                    <form class="form-horizontal" id="form" method="post" action="<?php print $CFG->rwww ?>/controle/CTUsuario.php?acao=alterarSenhaUsuarioAdmin">
                        <input type="hidden" name="valido" value="ctusuario">
                        <input type="hidden" name="idUsuario" id="idUsuario" value="<?php print $objUsuario->getUSR_ID_USUARIO(); ?>">

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Código:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span type="text" class="form-control-static"><?php print $objUsuario->getUSR_ID_USUARIO(); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nova Senha:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" class="tudo-normal" type="password" id="dsSenha" placeholder="Nova Senha" name="dsSenha" size="20" maxlength="30">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Repita Nova Senha:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" class="tudo-normal" type="password" id="dsSenhaRep" placeholder="Repita Nova Senha" name="dsSenhaRep" size="20" maxlength="30">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Forçar Troca de Senha:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <label class="checkbox-inline">
                                    <input type="checkbox" value="<?php print FLAG_BD_SIM ?>" id="forcarTroca" name="forcarTroca" checked>
                                </label>
                            </div>
                        </div>

                        <div id="divBotoes">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="btn btn-success" id="submeter" type="submit" value="Salvar">
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

            $("#form").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    dsSenha: {
                        required: true,
                        minlength: 6
                    },
                    dsSenhaRep: {
                        required: true,
                        minlength: 6,
                        equalTo: "#dsSenha"
                    }}, messages: {
                    dsSenhaRep: {
                        equalTo: "Por favor, Informe uma senha igual a anterior"
                    }
                }
            }
            );

            // bloquear copiar colar
            bloquearCopiarColar("dsSenhaRep");
        });
    </script>
</html>

