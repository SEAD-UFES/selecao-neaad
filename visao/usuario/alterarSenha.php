<!DOCTYPE html>
<html>
    <head>     
        <title>Alterar Senha - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        ?>

        <?php
        //verificando se está logado
        include_once ($CFG->rpasta . "/util/sessao.php");
        if (estaLogado() == null) {
            //redirecionando para página de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // definindo troca forcada
        $trocaForcada = isset($_GET["f"]) and $_GET["f"] == "true";

        // definindo volta
        $volta = isset($_GET['v']) && $_GET['v'] == "conf" ? "$CFG->rwww/visao/usuario/alterarDadosAcesso.php" : "$CFG->rwww/inicio";
        ?>

        <?php
        global $CFG;
        require($CFG->rpasta . "/include/includes.php");
        ?>
    </head>

    <body>  
        <?php include ($CFG->rpasta . "/include/cabecalho.php"); ?>
        <div id="main">
            <div id="container" class="clearfix">
                <?php
                include_once ($CFG->rpasta . "/controle/CTUsuario.php");
                //buscar usuario 
                $usuario = buscarUsuarioPorIdCT(getIdUsuarioLogado());
                ?>
                <div id="breadcrumb">
                    <h1>Você está em: Sistema > <strong>Alterar dados de acesso</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full m02">

                    <?php
                    // exibindo mensagem informativa
                    if ($trocaForcada) {
                        ?>
                        <div class="alert alert-info">Sua senha atual é temporária. Para continuar você deve alterar sua senha.</div>
                    <?php }
                    ?>

                    <form class="form-horizontal">
                        <?php if ($trocaForcada) { ?>
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input type="text" class="form-control" disabled value="<?php print $usuario->getUSR_DS_NOME() ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Login:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input type="text" class="form-control tudo-minusculo" disabled value="<?php print $usuario->getUSR_DS_LOGIN() ?>">
                            </div>
                        </div>
                    </form>

                    <?php if (!$usuario->isComunidadeExterna()) { ?>
                        <span class="textoItem">Você está usando o login único da UFES. <a target="_blank" href="https://senha.ufes.br/">Clique Aqui</a> para alterar sua senha.</span>
                        <br/>
                        <br/>
                        <input class="btn" type="button" id="btVoltar" onclick="window.location = '<?php echo "$CFG->rwww/inicio"; ?>'" value="Voltar">
                    <?php } else { ?>                      
                        <form class="form-horizontal" id="formAlterarSenha" method="post" action='<?php print $CFG->rwww . "/controle/CTUsuario.php?acao=alterarSenha" ?>'>
                            <input type="hidden" name="valido" value="ctusuario">

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Senha Atual:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control tudo-normal" type="password" id="dsSenhaAtual" name="dsSenhaAtual" size="20" maxlength="30">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Nova Senha:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control tudo-normal" type="password" id="dsSenha" name="dsSenha" size="20" maxlength="30">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Repita Nova Senha:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control tudo-normal" type="password" id="dsSenhaRep" name="dsSenhaRep" size="20" maxlength="30">
                                </div>
                            </div>

                            <div id="divBotoes">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                        <?php if (!$trocaForcada) { ?>
                                            <button class="btn btn-default"  type="button" onclick="javascript: window.location = '<?php print $volta; ?>';">Voltar</button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </form>	
                    <?php } ?>
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
            $("#formAlterarSenha").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    dsSenhaAtual: {
                        required: true
                    },
                    dsSenha: {
                        required: true,
                        minlength: 6
                    },
                    dsSenhaRep: {
                        required: true,
                        minlength: 6,
                        equalTo: "#dsSenha"
                    }

                }, messages: {
                }
            }
            );


            //bloqueando copiar colar
            bloquearCopiarColar("dsSenhaRep");
            bloquearCopiarColar("dsSenhaAtual");

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

