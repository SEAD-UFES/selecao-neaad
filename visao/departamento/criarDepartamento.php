<!DOCTYPE html>
<html>
    <head>     
        <title>Cadastrar Novo Departamento - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
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
                    <h1>Você está em: Cadastros > <a href="<?php print $CFG->rwww; ?>/visao/departamento/listarDepartamento.php">Departamento</a> > <strong>Cadastrar</strong></h1>
                </div>
                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full m02">
                    <form class="form-horizontal" id="formCadastro" method="post" action="<?php print $CFG->rwww ?>/controle/CTDepartamento.php?acao=criarDepartamento">
                        <input type="hidden" name="valido" value="ctdepartamento">
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dsNome" type="text" id="dsNome" size="30" maxlength="50" placeholder="Nome do Departamento" required>
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="btn btn-success" id="submeter" type="submit" value="Salvar">
                                <input class="btn btn-default" type="button" onclick="javascript: window.location = 'listarDepartamento.php';" value="Voltar">
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
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            $("#formCadastro").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    //$(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                    form.submit();
                },
                rules: {
                    dsNome: {
                        required: true,
                        minlength: 3,
                        remote: {
                            url: "<?php print $CFG->rwww ?>/controle/CTAjax.php?val=nomeDepartamento",
                            type: "post"
                        }

                    }}, messages: {
                    dsNome: {
                        remote: "Departamento já cadastrado."
                    }
                }
            }
            );

        });
    </script>
</html>

