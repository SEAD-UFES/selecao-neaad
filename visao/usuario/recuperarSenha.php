<!DOCTYPE html>
<html>
    <head>     
        <title>Recuperar Senha - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        include_once ($CFG->rpasta . "/util/sessao.php");
        if (estaLogado() != null) {
            //redirecionando para página principal
            header("Location: $CFG->rwww/inicio");
            return;
        }
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>

    </script>
</head>

<body>  
    <?php
    include($CFG->rpasta . "/include/cabecalho.php");
    ?>

    <div id="main">
        <div id="container" class="clearfix">

            <div id="breadcrumb">
                <h1>Recuperar senha</h1>
            </div>

            <div class="completo">
                <div class="callout callout-warning">
                    <strong>ATENÇÃO:</strong> Essa recuperação de senha só funciona para a comunidade externa. Caso utilize o login único da UFES, <a target="_blank" href="https://senha.ufes.br/">clique aqui</a>.
                </div>
                <?php print Util::$MSG_CAMPO_OBRIG; ?>
                <p class="col-full m01">Preencha os campos abaixo e clique em 'Salvar' para iniciar o processo de recuperação de sua senha.</p>
            </div>

            <div class="col-full m02">
                <form class="form-horizontal" id="formRecSenha" method="post" action='<?php print $CFG->rwww . '/controle/CTUsuario.php?acao=recuperarSenha' ?>'>
                    <input type="hidden" name="valido" value="ctusuario">
                    <input type="hidden" id="emailValidado" name="emailValidado" value="false">                    
                    <input type="hidden" id="ultimoEmail" name="ultimoEmail" value="false">                    

                    <div class="form-group">
                        <label for="dsEmail" class="control-label col-xs-12 col-sm-4 col-md-4">Email:</label>
                        <div class="col-xs-12 col-sm-8 col-md-8">
                            <input class="form-control tudo-minusculo" type="text" id="dsEmail" name="dsEmail" size="100" maxlength="100" placeholder="Email">
                        </div>
                    </div>

                    <div id="divCandidato" style="display: none">
                        <div class="form-group">
                            <label for="dtNascimento" class="control-label col-xs-12 col-sm-4 col-md-4">Data de nascimento:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dtNascimento" type="text" id="dtNascimento" size="100" maxlength="100" placeholder="Data de Nascimento">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nrCPF" class="control-label col-xs-12 col-sm-4 col-md-4">CPF:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="nrCPF" type="text" id="nrCPF" size="100" maxlength="100" placeholder="CPF">
                            </div>
                        </div>
                    </div>

                    <div id="divBotoes" class="m02">
                        <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                        <div class="col-xs-12 col-sm-8 col-md-8">
                            <button class="btn btn-success" id="submeter" type="submit">Enviar</button>
                            <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/inicio"; ?>';" >Voltar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="divMensagem" class="col-full" style="display: none;">
                <div class="alert alert-info">
                    Aguarde o processamento...
                </div>
            </div>
        </div>
    </div>
    <?php
    include($CFG->rpasta . "/include/rodape.php");
    carregaScript('metodos-adicionaisBR');
    carregaScript('jquery.maskedinput');
    ?>
</body>

<script type="text/javascript">
    $(document).ready(function () {

        // criando gatilho para alteração de email
        var fcAlteraEmail = function () {
            if ($("#ultimoEmail").val() != $("#dsEmail").val())
            {
                $("#emailValidado").val("false");
                $("#divCandidato").hide();
                $("#ultimoEmail").val($("#dsEmail").val());
            }
        };

        $("#dsEmail").keydown(fcAlteraEmail);
        $("#dsEmail").keyup(fcAlteraEmail);

        $("#formRecSenha").validate({
            submitHandler: function (form) {

                // verificando qual será o caso de submissão
                // caso da validação de email já está concluída: submissão nomal
                if ($("#emailValidado").val() === "true")
                {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                } else {

                    // tentar validar o email
                    $.ajax({
                        type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?val=emailRecuperarSenha",
                        data: {"dsEmail": $("#dsEmail").val()},
                        dataType: "json",
                        success: function (json) {
                            // caso validou
                            if (json['status']) {
                                // informando validação de email
                                $("#emailValidado").val("true");

                                // mostrando outros campos
                                if (json['campos']) {
                                    $("#divCandidato").show();
                                } else {
                                    $("#divCandidato").hide();

                                    // realizando submit
                                    mostrarMensagem();
                                    form.submit();
                                }
                            } else {
                                // disparando mensagem de erro
                                $().toastmessage('showToast', {
                                    text: json['msg'],
                                    sticky: true,
                                    type: 'error',
                                    position: 'top-right'
                                });
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
                }
            },
            rules: {
                dsEmail: {
                    required: true,
                    emailUfes: true
                },
                dtNascimento: {
                    required: true,
                    dataBR: true,
                    dataBRMenor: new Date()
                }, nrCPF: {
                    required: true,
                    CPF: true
                }}, messages: {
                dsEmail: {
                    emailUfes: "Informe um email válido."
                }
                , dtNascimento: {
                    dataBRMenor: "Data de nascimento deve ser menor que a data atual"
                }
            }
        }
        );

        //adicionando máscaras
        $("#dtNascimento").mask("99/99/9999");
        $("#nrCPF").mask("999.999.999-99");

        function errUsu() {
            $().toastmessage('showToast', {
                text: '<b>Desculpe.</b> Não foi possível encontrar um usuário com os dados informados.',
                sticky: true,
                type: 'error',
                position: 'top-right'
            });
        }

        function errUfes() {
            $().toastmessage('showToast', {
                text: "<b>Desculpe.</b> Seu login está associado à UFES. Para recuperar a sua senha <a target='_blank' href='https://senha.ufes.br/'>clique aqui</a>.",
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

