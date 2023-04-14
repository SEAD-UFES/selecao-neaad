<!DOCTYPE html>
<html>
    <head>
        <title>Contato - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../config.php';
        global $CFG;
        ?>

        <?php
        require_once ($CFG->rpasta . "/util/selects.php");
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
                    <h1>Você está em: <b>Contato</b></h1>
                </div>

                <div class="completo">
                    <div class="callout callout-info m01">
                        <p><b>Olá, você pode entrar em contato conosco para tirar dúvidas relacionadas ao sistema, enviar sugestões ou solicitar alguma informação.</b></p>
                        <p style="margin-bottom:0px;">Preencha atentamente os campos abaixo e clique em Enviar.</p>
                    </div>
                    <?php print Util::$MSG_CAMPO_OBRIG; ?>

                    <div class="col-full m02">
                        <form id="contato" class="form-horizontal" method="post" action="<?php print "$CFG->rwww/controle/CTUsuario.php?acao=enviarContato" ?>">
                            <input type="hidden" name="valido" value="ctusuario">
                            <div class="form-group m01">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome: *</label>
                                <div  class="col-xs-12 col-sm-8 col-md-8">
                                    <input id="nome" name="nome" class="form-control" type="text" size="30" maxlength="50" required>
                                </div>
                            </div>
                            <div class="form-group m01">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">E-mail: *</label>
                                <div  class="col-xs-12 col-sm-8 col-md-8">
                                    <input id="email" name="email" class="form-control" type="text" size="30" maxlength="50" required>
                                </div>
                            </div>
                            <div class="form-group m01">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Telefone:</label>
                                <div  class="col-xs-12 col-sm-8 col-md-8">
                                    <input id="telefone" name="telefone" class="form-control" type="text" size="30" maxlength="50">
                                </div>
                            </div>
                            <div class="form-group m01">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo de contato: *</label>
                                <div  class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoTipoContato(); ?>
                                </div>
                            </div>
                            <div class="form-group m01">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Mensagem: *</label>
                                <div  class="col-xs-12 col-sm-8 col-md-8">
                                    <textarea id="mensagem" name="mensagem" class="form-control" cols="25" rows="3" required></textarea>
                                    <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                                </div>
                            </div>

                            <div class="form-group m01">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Código de verificação: *</label>
                                <div  class="col-xs-12 col-sm-8 col-md-8">
                                    <input type="text" id="defaultReal" name="defaultReal" style="width:210px;margin-top:0.5em;">
                                </div>
                            </div>


                            <div id="divBotoes" class="m02">
                                <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <button class="btn btn-success" id="submeter" type="submit">Enviar</button>
                                    <button class="btn btn-default" type="button" onclick="window.location = '<?php echo "$CFG->rwww/inicio"; ?>'" >Voltar</button>
                                </div>
                            </div>
                        </form>



                        <div id="divMensagem" class="col-full" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include ($CFG->rpasta . "/include/rodape.php");
    carregaScript('metodos-adicionaisBR');
    carregaScript('jquery.maskedinput');
    carregaScript('jquery.plugin');
    carregaScript('jquery.realperson');
    carregaCSS('jquery.realperson');
    ?>
</body>
<script type = "text/javascript">
    $(document).ready(function () {
        
         //Ativando Captcha
        $('#defaultReal').realperson({chars: $.realperson.alphanumeric});


        $("#contato").validate({
            submitHandler: function (form) {
                //evitar repetiçao do botao
                mostrarMensagem();
                $("#email").lowercase();
                form.submit();
            },
            rules: {
                defaultReal:{
                  required: true  
                },
                nome: {
                    required: true,
                    minlength: 3,
                    nome: true
                },
                email: {
                    required: true,
                    emailUfes: true
                }, tpContato: {
                    required: true
                }, mensagem: {
                    maxlength: 500
                }
            }, messages: {
                email: {
                    emailUfes: "Email inválido.",
                }
            }
        }
        );

       
        //incluindo contador para caracteres restantes
        adicionaContadorTextArea(500, "mensagem", "qtCaracteres");

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
        $("#telefone").mask("(99) 9999-9999?9");
        $('#telefone').focusout(funcaoTrocaMascara).trigger('focusout');

        function sucContato() {
            $().toastmessage('showToast', {
                text: '<b>Sua mensagem foi enviada com sucesso!</b> Em breve entraremos em contato. Obrigado!',
                sticky: false,
                type: 'success',
                position: 'top-right'
            });
        }

        function errContato() {
            $().toastmessage('showToast', {
                text: '<b>Desculpe, ocorreu um erro ao enviar sua mensagem.</b> Por favor, tente novamente.',
                sticky: true,
                type: 'error',
                position: 'top-right'
            });
        }

        function errCaptcha() {
            $().toastmessage('showToast', {
                text: '<b>Captcha incorreto.</b> Por favor, digite o código da imagem corretamente.',
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