<!DOCTYPE html>
<html>
    <head>      
        <title>Contatos - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        include_once ($CFG->rpasta . "/util/sessao.php");
        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // buscando contato
        include_once ($CFG->rpasta . "/controle/CTCandidato.php");
        $contCand = buscarContatoCandPorIdUsuarioCT(getIdUsuarioLogado());
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

                <?php include ($CFG->rpasta . "/include/tutorial.php"); ?>

                <div id="breadcrumb">
                    <h1>Você está em: Candidato > <strong>Contato</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">
                    <form id="formContato" class="form-horizontal" method="post" action="<?php print "$CFG->rwww/controle/CTCandidato.php?acao=editarContato" ?>" >
                        <input type="hidden" name="valido" value="ctcandidato">
                        <input type="hidden" name="<?php echo Candidato::$PARAM_PREENC_REVISAO; ?>" value="<?php isset($_GET[Candidato::$PARAM_PREENC_REVISAO]) ? print Candidato::$PREENC_CURRICULO : print ""; ?>">

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Email:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span class="form-control-static"> <?php print getEmailUsuarioLogado(); ?> </span>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Telefone residencial:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="nrTelResidencial" type="text" id="nrTelResidencial" size="50" maxlength="100" value="<?php print $contCand->getCTC_NR_TEL_RES(); ?>" placeholder='Telefone residencial'>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Telefone comercial:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="nrTelComercial" type="text" id="nrTelComercial" size="50" maxlength="100" value="<?php print $contCand->getCTC_NR_TEL_COM(); ?>" placeholder='Telefone comercial'>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Celular: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="nrTelCelular" type="text" id="nrTelCelular" size="50" maxlength="100" value="<?php print $contCand->getCTC_NR_CELULAR(); ?>" placeholder='Celular' required>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Fax:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="nrTelFax" type="text" id="nrTelFax" size="50" maxlength="100" value="<?php print $contCand->getCTC_NR_FAX(); ?>" placeholder='Fax'>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label title="Informe, de preferência, um email que você tenha acesso mesmo se for desligado da UFES. Caso não tenha outro email, informe seu email de login." class="control-label col-xs-12 col-sm-4 col-md-4">Email alternativo: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class='form-control tudo-minusculo' name="dsEmailAlternativo" type="text" id="dsEmailAlternativo" size="50" maxlength="100" value="<?php print $contCand->getCTC_EMAIL_CONTATO(); ?>" placeholder='Email alternativo (Externo à UFES)' required>
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php echo "$CFG->rwww/inicio"; ?>';">Voltar</button>
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
        carregaScript('jquery.maskedinput');
        carregaScript('metodos-adicionaisBR');
        ?>
    </body>
    <script type = "text/javascript">
        $(document).ready(function () {
            $("#formContato").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    $("#dsEmailAlternativo").lowercase();
                    form.submit();
                },
                rules: {
                    dsEmailAlternativo: {
                        emailUfes: true,
                        remote: {
                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=emailAlternativo&idUsuario=<?php print getIdUsuarioLogado(); ?>",
                                                    type: "post"
                                                }
                                            }}, messages: {
                                            dsEmailAlternativo: {
                                                emailUfes: "Email inválido.",
                                                remote: "Email já cadastrado."
                                            }
                                        }
                                    }
                                    );

                                    var funcaoTrocaMascara = function () {
                                        var phone, element;
                                        element = $(this);
                                        element.unmask();
                                        phone = element.val().replace(/\D/g, '');
                                        if (phone.length > 10) {
                                            element.mask("(99) 99999-999?9");
                                        } else {
                                            element.mask("(99) 9999-9999?9");
                                        }
                                    }

                                    //adicionando máscara
                                    $("#nrTelResidencial").mask("(99) 9999-9999?9");
                                    $('#nrTelResidencial').focusout(funcaoTrocaMascara).trigger('focusout');
                                    $("#nrTelComercial").mask("(99) 9999-9999?9");
                                    $('#nrTelComercial').focusout(funcaoTrocaMascara).trigger('focusout');
                                    $("#nrTelFax").mask("(99) 9999-9999?9");
                                    $('#nrTelFax').focusout(funcaoTrocaMascara).trigger('focusout');
                                    $('#nrTelCelular').mask("(99) 9999-9999?9");
                                    $('#nrTelCelular').focusout(funcaoTrocaMascara).trigger('focusout');

                                });
    </script>
</html>

