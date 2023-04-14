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
        //validando parâmetros
        if (!isset($_GET['id']) || !isset($_GET['ch']) || !isset($_GET['dt']) || !isset($_GET['idUsuario'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        require_once ($CFG->rpasta . "/controle/CTUsuario.php");

        //verificando validade do link
        validarAlterarSenhaCT($_GET['id'], $_GET['ch'], $_GET['dt'], $_GET['idUsuario']);
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>
    </head>
    <body>  
        <?php
        include($CFG->rpasta . "/include/cabecalho.php");
        ?>
        <div id="main">
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1>Inserir nova senha</h1>
                </div>

                <div class="col-full m02">
                    <form class="form-horizontal" id="formTrocaSenha" method="post" action=<?php print $CFG->rwww . "/controle/CTUsuario.php?acao=trocarSenhaRecuperada" ?>>
                        <input type="hidden" name="valido" value="ctusuario">
                        <?php
                        //gerando inputs 
                        $id = $_GET['id'];
                        $ch = $_GET['ch'];
                        $dt = $_GET['dt'];
                        $idUsuario = $_GET['idUsuario'];
                        echo "<input type='hidden' name='id' value='$id'>";
                        echo "<input type='hidden' name='ch' value='$ch'>";
                        echo "<input type='hidden' name='dt' value='$dt'>";
                        echo "<input type='hidden' name='idUsuario' value='$idUsuario'>";
                        ?>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dsEmail">Email:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="input-fixo form-control tudo-minusculo" type="text" id="dsEmail" name="dsEmail" size="30" maxlength="100" placeholder="Email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dsSenha">Senha:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="input-fixo form-control tudo-normal" type="password" id="dsSenha" name="dsSenha" size="20" maxlength="30" placeholder="Nova Senha">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dsSenhaRep">Repita a Senha:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="input-fixo form-control tudo-normal" type="password" id="dsSenhaRep" name="dsSenhaRep" size="20" maxlength="30" placeholder="Repita a Nova Senha">
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="btn btn-success" id="submeter" type="submit" value="Salvar">
                                <input class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print $CFG->rwww ?>/acesso';" value="Voltar">
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
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript('metodos-adicionaisBR');
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#formTrocaSenha").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    dsEmail: {
                        required: true,
                        emailUfes: true
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
                    dsEmail: {
                        emailUfes: "Informe um email válido."
                    },
                    dsSenhaRep: {
                        equalTo: "Por favor, Informe uma senha igual a anterior"
                    }
                }
            }
            );

            //bloqueando copiar colar
            bloquearCopiarColar("dsSenhaRep");
            bloquearCopiarColar("dsEmail");

        });
    </script>
</html>

