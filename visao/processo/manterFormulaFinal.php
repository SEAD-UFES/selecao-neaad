<!DOCTYPE html>
<html>
    <head>     
        <title>Manter Fórmula Final - Seleção EAD</title>
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

        //verificando passagem por get
        if (!isset($_GET['idProcesso'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando processo
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);

        // validando edicao de processo
        if (!permiteComporNotaFinalCT($processo)) {
            New Mensagem("Dados não podem ser alterados.", Mensagem::$MENSAGEM_ERRO);
        }

        // buscando etapas do processo
        $etapas = buscarEtapaAvalPorProcCT($processo->getPRC_ID_PROCESSO());

        // buscando macro de fórmula
        $macroFinal = buscarMacroConfNotaFinalCT($processo->getPRC_ID_PROCESSO());
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Edital</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong>Fórmula Final</strong></h1>
                </div>

                <div class="col-full m02">
                    <div class="panel-group ficha-tecnica" id="accordion">
                        <div class="painel">
                            <div class="panel-heading">
                                <a style="text-decoration:none;" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                    <h4 class="panel-title">Ficha Técnica</h4>
                                </a>
                            </div>

                            <div id="collapseOne" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <p>
                                        <i class="fa fa-book"></i>
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">
                    <form class="form-horizontal" id="form" method="post" action="<?php print $CFG->rwww; ?>/controle/CTNotas.php?acao=manterFormulaFinal">
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" id='idProcesso' name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">

                        <div class="form-group">
                            <label title="Exibe o ID dos elementos disponíveis para a composição da fórmula" class="control-label col-xs-12 col-sm-4 col-md-4">Elementos Disponíveis:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <textarea class="form-control" disabled style="width:100%;" cols="60" rows="6" name="elementosDisponiveis" id="elementosDisponiveis"><?php print EtapaAvalProc::strElementosFormula($etapas); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label title="Caso necessário, escolha uma fórmula rápida para começar." class="control-label col-xs-12 col-sm-4 col-md-4">Fórmula Rápida:</label>
                            <div id='divFormulaRapida' class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoFormulaRapida($etapas); ?>
                                <div id='mensagemFormulaRapida' style="display: none">
                                    Aguarde o processamento...
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label title="Cálculo da Nota Final" class="control-label col-xs-12 col-sm-4 col-md-4">Cálculo: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <textarea class="form-control" style="width:100%;" cols="60" rows="6" name="formula" id="formula"><?php $macroFinal != NULL ? print $macroFinal->getMCP_DS_PARAMETROS() : print ""; ?></textarea>
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                <input type="button" class="btn btn-default" onclick="javascript: window.location = 'manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>#resultadoFinal';" value="Voltar">
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
        carregaScript("jquery.maskedinput");
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            // gatilho para fórmula rápida
            $("#idFormulaRapida").change(function () {
                // selecione: Nada a fazer
                if ($("#idFormulaRapida").val() === "")
                {
                    return;
                }

                // preparando tela
                $("#mensagemFormulaRapida").show();

                // enviando dados 
                $.ajax({
                    type: "POST",
                    url: getURLServidor() + "/controle/CTAjax.php?obterHTML=formulaFinalProc",
                    data: {"idProcesso": '<?php print $processo->getPRC_ID_PROCESSO(); ?>', "idFormulaRapida": $("#idFormulaRapida").val()},
                    dataType: "json",
                    success: function (json) {
                        var idFormulaRapida = $("#idFormulaRapida").val();
                        var pesoGenerico = '<?php print MacroConfProc::$_PESO_GENERICO; ?>';
                        var tipoMediaPonderada = '<?php print MacroConfProc::$FORM_RAP_MEDIA_PONDERADA ?>';
                        //restabelecendo pagina
                        $("#mensagemFormulaRapida").hide();
                        $("#idFormulaRapida").val('');

                        if (!json['situacao'])
                        {
                            alert("Erro tentar obter fórmula rápida: " + json['msg']);
                        } else {

                            // inserindo fórmula no campo
                            $("#formula").val(json['formula']);
                            // emitindo aviso  
                            if (idFormulaRapida == tipoMediaPonderada) {
                                alert("Substitua os pesos " + pesoGenerico + "1, ..., " + pesoGenerico + "n pelo valor correspondente ao peso de cada etapa.");
                            }
                        }
                    }, error: function (xhr, ajaxOptions, thrownError) {
                        var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nTente novamente.\n\n";
                        msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;
                        // exibindo mensagem
                        alert(msg);
                        //restabelecendo
                        $("#mensagemFormulaRapida").hide();
                        $("#idFormulaRapida").val('');
                    }
                });
            });

            $("#form").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    formula: {
                        required: true,
                        remote: {
                            url: "<?php print $CFG->rwww ?>/controle/CTAjax.php?val=formulaFinalProc&idProcesso=" + $("#idProcesso").val(),
                            type: "post"
                        }
                    }
                }, messages: {
                    formula: {
                        remote: "Fórmula inválida."
                    }
                }
            });

        });

    </script>
</html>

