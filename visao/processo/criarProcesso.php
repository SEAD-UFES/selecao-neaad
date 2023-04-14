<!DOCTYPE html>
<html>
    <head>     
        <title>Cadastrar Novo Edital - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        // coordenador ou administrador
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null && estaLogado(Usuario::$USUARIO_COORDENADOR) == null) {
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <strong>Novo</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full m02">
                    <form enctype="multipart/form-data" class="form-horizontal" id="formCadastro" method="post" action="<?php print $CFG->rwww; ?>/controle/CTProcesso.php?acao=criarProcesso">
                        <input type="hidden" name="valido" value="ctprocesso">
                        <input type="hidden" name="MAX_FILE_SIZE" value="<?php print Processo::$TAM_MAX_ARQ_BYTES; ?>">
                        <fieldset class="m02">
                            <legend>Numeração do Edital</legend>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Número:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="nrEdital" type="text" id="nrEdital" size="3" maxlength="3" placeholder="Número" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Ano:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="anoEdital" type="text" id="anoEdital" size="4" maxlength="4" placeholder="Ano" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Atribuição:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoTipoCargo() ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Curso:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoCurso() ?>
                                </div>
                            </div>

                            <div id="divErroNumeracao" style="display: none" class="alert alert-error">
                                Desculpe. Você já cadastrou uma Edital com esta numeração. Por favor, informe outra numeração e tente novamente.
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Dados Adicionais</legend>
                            <div class="form-group">
                                <label title="Data a partir da qual o Edital poderá ser visualizado pelos candidatos"  class="control-label col-xs-12 col-sm-4 col-md-4">Data de Início:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="dtInicio" type="text" id="dtInicio" size="10" maxlength="10" placeholder="Data de Início" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">PDF do Edital:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                    <input name="arqEdital" type="file" id="arqEdital" placeholder="PDF do Edital">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Descrição do Edital:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8" style="display:block">
                                    <textarea class="form-control" style="width:100%;" cols="60" rows="6" name="dsEdital" id="dsEdital"></textarea>
                                    <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                                </div>
                                <br/>
                            </div>
                        </fieldset>
                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                <input type="button" class="btn btn-default" onclick="javascript: window.location = 'listarProcessoAdmin.php';" value="Voltar">
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
        carregaScript("ajax");
        carregaScript("jquery.mask");
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            //incluindo contador para caracteres restantes
            adicionaContadorTextArea(<?php print Processo::$MAX_CARACTER_DS_EDITAL; ?>, "dsEdital", "qtCaracteres");

            // adicionando mascaras
            $("#nrEdital").mask('0ZZ', {translation: {'Z': {pattern: /[0-9]/, optional: true}}});
            $("#anoEdital").mask('QZZZ', {translation: {'Z': {pattern: /[0-9]/, optional: false}, 'Q': {pattern: /[1-9]/, optional: false}}, clearIfNotMatch: true});
            $("#dtInicio").mask("00/00/0000", {clearIfNotMatch: true});

            $("#formCadastro").validate({
                submitHandler: function (form) {
                    // tentando validaçao de item e area
                    $("#divErroNumeracao").hide();
                    $.ajax({
                        type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?val=numeracaoEdital",
                        data: {"nrEdital": $("#nrEdital").val(), "anoEdital": $("#anoEdital").val(), "idTipoCargo": $("#idTipoCargo").val(), "idCurso": $("#idCurso").val()},
                        dataType: "json",
                        success: function (json) {
                            // caso validou
                            if (json) {
                                // processa o submit
                                // 
                                //evitar repetiçao do botao
                                mostrarMensagem();
                                $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                                form.submit();
                            } else {
                                // exibe msg de erro e aborta operaçao de submit
                                $("#divErroNumeracao").show();
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
                },
                rules: {
                    nrEdital: {
                        required: true,
                        digits: true
                    }, anoEdital: {
                        required: true,
                        digits: true
                    }, idTipoCargo: {
                        required: true
                    }, idCurso: {
                        required: true
                    }, dtInicio: {
                        required: true,
                        dataBR: true,
                        dataBRMaiorIgual: new Date()
                    }, arqEdital: {
                        required: true,
                        extension: "pdf",
                        accept: "application/pdf",
                        tamMaxArq: 2 // tamanho em MB
                    }, dsEdital: {
                        required: true,
                        maxlength: <?php print Processo::$MAX_CARACTER_DS_EDITAL; ?>
                    }}, messages: {
                    dtInicio: {
                        dataBR: "Por favor, Informe uma data válida.",
                        dataBRMaiorIgual: "A data de início deve ser igual ou posterior a data atual."
                    }, arqEdital: {
                        extension: "Só é possível enviar arquivo PDF.",
                        accept: "Só é possível enviar arquivo PDF.",
                        tamMaxArq: "O arquivo deve ter no máximo 2MB."
                    }
                }
            });
        });
    </script>
</html>

