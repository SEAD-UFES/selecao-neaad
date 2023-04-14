<?php
$urlAtual = $_SERVER['REQUEST_URI'];
$loginPopup = strpos($urlAtual, "login.php") === FALSE ? "true" : "false";
?>
<form class="form-horizontal" id="formLogin" action='<?php print $CFG->rwww . "/util/sessao.php?acao=login" ?>' method="post">
    <input type="hidden" name="valido" value="sessao">
    <input type="hidden" name="loginPopup" value="<?php print $loginPopup; ?>">
    <div class="form-group">
        <label for="login">E-mail / Login único</label>
        <input class="input-fixo form-control tudo-minusculo m0p5" type="text" id="login" name="login" size="30" maxlength="100" placeholder="email ou login único">
    </div>
    <div class="form-group m0p5">
        <label for="senha">Senha</label>
        <input class="input-fixo form-control tudo-normal m0p5" type="password" id="senha" name="senha" size="30" maxlength="30" placeholder="senha">
    </div>
    <div id="divBotoes">
        <button class="btn btn-success" id="submeter" type="submit">Entrar</button>
        <button class="btn btn-default" id="limpar" type="reset" >Limpar</button>
        <br>&nbsp;
        <br>
    </div>
    <div id="divMensagem" style="display:none">
        <div class="alert alert-info">
            Aguarde o processamento...
        </div>
    </div>
    <div>
        <a id="recSenha" title="Recuperar Senha" href="<?php print $CFG->rwww ?>/recuperar-senha">Esqueci minha senha</a><br>
        <a id="cadastrarUsuario" title="Realizar Cadastro" href="<?php print $CFG->rwww ?>/cadastre-se">Ainda não tenho cadastro</a>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        //validando form
        $("#formLogin").validate({
            submitHandler: function (form) {
                //evitar repetiçao do botao
                mostrarMensagem();
                form.submit();
            },
            rules: {
                login: {
                    required: true,
                    emailUfes: {
                        depends: function (element) {
                            return !validaLoginUFES(element);
                        }
                    }
                },
                senha: {
                    required: true

                }}, messages: {
                login: {
                    emailUfes: "Informe um email válido ou um Login Único válido."
                }
            }
        }
        );


        //casos botões
        $("#limpar").click(function () {
            $("#formLogin").validate().resetForm();
        });

        function errAutenticacao() {
            $().toastmessage('showToast', {
                text: '<b>Login ou senha inválidos.</b>',
                sticky: true,
                type: 'error',
                position: 'top-right'
            });
        }

        function errBloqueio() {
            $().toastmessage('showToast', {
                text: '<b>Usuário bloqueado.</b>',
                sticky: true,
                type: 'error',
                position: 'top-right'
            });
        }

        function notSessao() {
            $().toastmessage('showToast', {
                text: '<b>Sua sessão expirou.</b> Por favor, faça seu login novamente.',
                sticky: true,
                type: 'warning',
                position: 'top-right'
            });
        }

        function sucNovaSenha() {
            $().toastmessage('showToast', {
                text: '<b>Senha alterada com sucesso.</b> Utilize sua nova senha para acessar o sistema.',
                sticky: false,
                type: 'success',
                position: 'top-right'
            });
        }

        function sucNovoLogin() {
            $().toastmessage('showToast', {
                text: "<b>Login alterado com sucesso.</b> Utilize seu novo login para acessar o sistema.",
                sticky: false,
                type: 'success',
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