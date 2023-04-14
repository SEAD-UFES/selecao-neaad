<!DOCTYPE html>
<html>
    <head>     
        <title>Validar Comprovante de Inscrição - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTUsuario.php");
        include_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // recuperando dados get
        $dadosToast = isset($_GET[Mensagem::$TOAST_VAR_GET]) ? $_GET[Mensagem::$TOAST_VAR_GET] : NULL;
        $_GET[Mensagem::$TOAST_VAR_GET] = NULL;
        $dadosValidacao = processaApresValidacaoComp($dadosToast);
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
                    <h1>Você está em: Editais > <strong>Validar Comprovante</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full m02">

                    <?php
                    if (!Util::vazioNulo($dadosToast)) {
                        if ($dadosValidacao[0]) {
                            ?>
                            <div role="alert" class="alert alert-success">
                                <?php echo $dadosValidacao[1]; ?>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div role="alert" class="alert alert-danger">
                                <button type="button" class="close" onclick="javascript: window.location = '<?php echo "$CFG->rwww/visao/inscricaoProcesso/validarCodigoCompInsc.php"; ?>';" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                                <?php echo $dadosValidacao[1]; ?>
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <form id="formValidar" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTProcesso.php?acao=validarCompInscProc" ?>'>
                        <input type="hidden" name="valido" value="ctprocesso">

                        <div class="form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Código de Autenticidade:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control tudo-maiusculo" type="text" id="nrAutenticidade" name="nrAutenticidade" size="35" maxlength="35">
                            </div>
                        </div>

                        <div id="divBotoes">
                            <div class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-success" id="submeter" type="submit">Validar</button>
                            </div>	
                        </div>

                        <div id="divMensagem" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>

                    </form>	
                </div>
            </div>  
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        carregaScript("ajax");
        carregaScript("jquery.maskedinput");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            //validando form
            $("#formValidar").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    nrAutenticidade: {
                        required: true
                    }
                }, messages: {
                }
            }
            );

            //adicionando mascaras
            $("#nrOrdem").mask("9?99999999", {placeholder: " "});
            $.mask.definitions['h'] = "[A-Fa-f0-9]";
            $("#nrAutenticidade").mask("hhhhhhhh.hhhhhhhh.hhhhhhhh.hhhhhhhh", {placeholder: " "});
        });
    </script>
</html>