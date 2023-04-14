<!DOCTYPE html>
<html>
    <head>     
        <title>Gerenciar Acesso - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        include_once ($CFG->rpasta . "/util/sessao.php");
        if (estaLogado() == null) {
            //redirecionando para página de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        include_once ($CFG->rpasta . "/controle/CTUsuario.php");
        //buscar usuario 
        $usuario = buscarUsuarioPorIdCT(getIdUsuarioLogado());

        // verificando se é edicao
        $edicao = $usuario->isComunidadeExterna() && isset($_GET['e']) && $_GET['e'] == "true";
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>
    </head>

    <body>  
        <?php include ($CFG->rpasta . "/include/cabecalho.php"); ?>
        <div id="main">
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1>Você está em: Sistema > <strong>Gerenciar Acesso</strong></h1>
                </div>

                <?php
                if ($edicao) {
                    print Util::$MSG_CAMPO_OBRIG_TODOS;
                }
                ?>

                <div class="col-full m02">
                    <form id="formAlterarLogin" class="form-horizontal" method="post" action='<?php print $CFG->rwww . "/controle/CTUsuario.php?acao=alterarLogin" ?>'>
                        <input type="hidden" name="valido" value="ctusuario">

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo de Acesso:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input type="text" class="form-control" disabled value="<?php print $usuario->getDsTipoAcesso(); ?>">
                            </div>
                        </div>

                        <?php if (!$usuario->isComunidadeExterna()) { ?>
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Filiação UFES:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input type="text" class="form-control" disabled value="<?php print $usuario->getDsVinculoUFES($usuario->getUSR_TP_VINCULO_UFES(), "Comunidade Externa"); ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4"><?php !$usuario->isComunidadeExterna() ? print "Login único" : print "Email" ?>:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="dsEmail" name="dsEmail" type="text" class="form-control tudo-minusculo" <?php $edicao ? print "" : print "disabled"; ?> value="<?php print $usuario->getUSR_DS_LOGIN() ?>">
                            </div>
                        </div>

                        <div id="divSenha" style="display: <?php !$edicao ? print "none" : print "block" ?>">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Senha Atual:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control tudo-normal" type="password" id="dsSenhaAtual" name="dsSenhaAtual" size="20" maxlength="30">
                                </div>
                            </div>
                        </div>


                        <?php if (!$usuario->isComunidadeExterna()) { ?>
                            <div class="completo">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <div class="callout callout-info completo">
                                        Você está usando o login único da UFES. Caso deseja alterar sua senha <a target="_blank" href="https://senha.ufes.br/">clique aqui</a>.
                                    </div>
                                </div>
                            </div>
                            <input class="btn btn-default" type="button" id="btVoltar" onclick="window.location = '<?php echo "$CFG->rwww/inicio"; ?>'" value="Voltar">
                        <?php } else { ?>  
                            <div id="divVisualizacaoLogin" style="display: <?php $edicao ? print "none" : print "block" ?>">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/usuario/alterarDadosAcesso.php?e=true" ?>';">Alterar Email</button>
                                        <button class="btn btn-default"  type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/usuario/alterarSenha.php?v=conf" ?>';">Alterar senha</button>
                                    </div>
                                </div>
                            </div>

                            <div id="divEdicaoLogin" style="display: <?php !$edicao ? print "none" : print "block" ?>">
                                <div id="divBotoes">
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <button class="btn btn-success" type="submit">Salvar</button>
                                            <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/usuario/alterarDadosAcesso.php" ?>';">Voltar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
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
        carregaScript('metodos-adicionaisBR');
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#formAlterarLogin").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    dsEmail: {
                        required: true,
                        emailUfes: true,
                        remote: {
                            url: "<?php print "$CFG->rwww/controle/CTAjax.php?val=emailCadastro&idUsuario="; ?><?php print getIdUsuarioLogado(); ?>",
                            type: "post"
                        }
                    },
                    dsSenhaAtual: {
                        required: true
                    }

                }, messages: {
                    dsEmail: {
                        emailUfes: "Informe um email válido.",
                        remote: "Email já cadastrado."
                    }
                }
            }
            );

            function errSenhaAtual() {
                $().toastmessage('showToast', {
                    text: '<b>Senha atual incorreta.</b>',
                    sticky: true,
                    type: 'error',
                    position: 'top-right'
                });
            }

<?php
if (isset($_GET[Mensagem::$TOAST_VAR_GET])) {
    print $_GET[Mensagem::$TOAST_VAR_GET] . "();";
}
?>

        });
    </script>
</html>

