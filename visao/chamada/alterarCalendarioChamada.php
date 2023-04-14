<!DOCTYPE html>
<html>
    <head>     
        <title>Alterar Calendário da Chamada - Seleção EAD</title>
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
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idProcesso']) || !isset($_GET['idChamada'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados para processamento
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);
        $chamada = buscarChamadaPorIdCT($_GET['idChamada'], $processo->getPRC_ID_PROCESSO());

        // verificando se pode alterar o calendário
        if (!ProcessoChamada::permiteEditarCalendario($processo)) {
            new Mensagem("Não é possível alterar o calendário da chamada.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // verificando se é criação da chamada
        $fluxoChamada = isset($_GET['cCham']) && $_GET['cCham'] == "true";

        // verificando se é volta de erro sem alteração
        $semAlteracao = isset($_GET[Mensagem::$TOAST_VAR_GET]) && $_GET[Mensagem::$TOAST_VAR_GET] == "errSemAlteracao";
        if ($semAlteracao) {
            // limpando
            unset($_GET[Mensagem::$TOAST_VAR_GET]);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar Chamada</a> > <strong>Alterar Calendário</strong></h1>
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
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class='barra'></separador>
                                    <b>Chamada:</b> <?php print $chamada->getPCH_DS_CHAMADA(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full m01"> 

                    <?php if ($semAlteracao) { ?>
                        <div role="alert" class="alert alert-danger">
                            Você não modificou o calendário!
                        </div>
                    <?php } ?>

                    <form class="form-horizontal" id="formCalendario" method="post" action="<?php print $CFG->rwww; ?>/controle/CTManutencaoProcesso.php?acao=alterarCalendario">
                        <input type="hidden" name="valido" value="ctmanutencaoprocesso">
                        <input type="hidden" id='idProcesso' name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" id='idChamada' name="idChamada" value="<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                        <input type="hidden" id='dtInicioEdital' value="<?php print $processo->getPRC_DT_INICIO(); ?>">
                        <input type="hidden" id='fluxoChamada' name="fluxoChamada" value="<?php print NGUtil::mapeamentoSimNao($fluxoChamada); ?>">
                        <input type="hidden" id='previaValida' name="previaValida" value="<?php $chamada->necessarioInfAtuEditalObj() ? print "false" : print "true"; ?>">

                        <fieldset id="alterarCalendarioChamada">
                            <legend>Configuração do Calendário</legend>
                            <?php
                            // ATENÇÃO: Layout semelhante a gerenciarResultadosProcesso.php
                            // Ao alterar este arquivo, considere revisar gerenciarResultadosProcesso.php e criarChamada.php
                            // 
                            // 
                            // recuperando itens do calendário
                            $itensCal = $chamada->listaItensCalendario(TRUE);

                            // percorrendo e imprimindo
                            foreach ($itensCal as $item) {
                                $classeItem = $item['status'] == ProcessoChamada::$EVENTO_PASSADO ? "" : ($item['status'] == ProcessoChamada::$EVENTO_PRESENTE ? : "");
                                $dsEditavel = !$item['editavel'] ? "disabled" : "";
                                ?>
                                <div class="calendario form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4 <?php print $classeItem; ?>"><?php print $item['nmItem']; ?></label>
                                    <div class="inputs col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" type="text" <?php print $dsEditavel ?> name="<?php print $item['idInput1']; ?>" id="<?php print $item['idInput1']; ?>" size="10" maxlength="10" value="<?php print $item['vlItem1']; ?>">
                                        <?php if ($item['itemDuplo']) { ?>
                                            <div class="ate">a</div>
                                            <input class="form-control" type="text" <?php print $dsEditavel ?> name="<?php print $item['idInput2']; ?>" id="<?php print $item['idInput2']; ?>" size="10" maxlength="10" value="<?php print $item['vlItem2']; ?>">
                                        <?php } ?>

                                        <label style="display: none" for="<?php print $item['idInput1']; ?>" class="error"></label>
                                        <?php if ($item['itemDuplo']) { ?>
                                            <div class="completo">
                                                <label style="display:none" for="<?php print $item['idInput2']; ?>" class="error"></label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php }
                            ?>
                        </fieldset>

                        <?php if ($chamada->necessarioInfAtuEditalObj()) { ?>
                            <fieldset class="m02">
                                <legend>Dados da Notificação</legend>
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Texto inicial: <i title="Este é o texto exibido no início da notificação. Utilize esse espaço para introduzir a modificação e explicar os motivos." class="fa fa-question-circle"></i></label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <textarea class="form-control" cols="60" rows="6" name="textoInicial" id="textoInicial"><?php print $processo->getTextoInicialPadraoNotAltCalendario($chamada->getPCH_DS_CHAMADA(TRUE)); ?></textarea>
                                        <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>                                    
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Documento: <i title="Este é o documento gerado para informar a alteração." class="fa fa-question-circle"></i> *</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <div id="divGerarPrevia">
                                            <button onclick="javascript: visualizarPreviaCalendario();" class="btn btn-info btn-sm" type="button">Visualizar prévia</button>
                                            <label id="erroPrevia" class="error" style="display: none"></label>
                                        </div>
                                        <div style="display: none" id="divValidarPrevia">
                                            <div class="callout callout-warning" style="margin:0px;">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" id="ciente" name="ciente" value="<?php print FLAG_BD_SIM; ?>">
                                                        <b>Revisei o documento a ser publicado</b> e ele está pronto para publicação. <a target="previaCalendario" onclick="javascript: abrirPrevia();">(Clique aqui para visualizar novamente o arquivo)</a>
                                                    </label>
                                                </div>
                                                <label for="ciente" class="error" style="display: none"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        <?php } ?>

                        <div id="divBotoes">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/processo/"; ?>manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>';">Voltar</button>
                            </div>
                        </div>
                    </form>

                    <form id="formPrevia" target="previaCalendario" method="post" action="<?php print $CFG->rwww; ?>/visao/relatorio/gerarPDFRetificacaoCalendario.php?acao=previaCalendario">
                        <input type="hidden" name="dadosPrevia" id="dadosPrevia">
                        <input type="hidden" name="valido" value="retificacaocalendario">
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
        function abrirPrevia() {
            window.open('', 'previaCalendario');
            $("#dadosPrevia").val($("#formCalendario").serialize());
            $("#formPrevia").submit();
        }

        // solicitando prévia
        function visualizarPreviaCalendario() {
            // form valido
            if (!$("#formCalendario").valid()) {
                $("#erroPrevia").html("É necessário preencher todos os campos corretamente para visualizar a prévia.");
                $("#erroPrevia").show();
                return;
            }

            // validando exibição da prévia
            $.ajax({
                type: "POST",
                url: getURLServidor() + "/visao/relatorio/gerarPDFRetificacaoCalendario.php?acao=validarPrevia",
                data: {"valido": "retificacaocalendario", "dadosPrevia": $("#formCalendario").serialize()},
                dataType: "json",
                async: false,
                success: function (json) {
                    if (json['val'])
                    {
                        abrirPrevia();
                        $("#erroPrevia").html("");
                        $("#erroPrevia").hide();

                        $("#divGerarPrevia").hide();
                        $("#divValidarPrevia").show();
                        $("#previaValida").val("true");

                    } else {
                        $("#erroPrevia").html(json['msg']);
                        $("#erroPrevia").show();
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

        $(document).ready(function () {

<?php if ($chamada->necessarioInfAtuEditalObj()) { ?>
                $("#formCalendario").find("input[type='text'],textarea").change(function () {
                    $("#previaValida").val("false");
                    $("#ciente").attr("checked", false);
                    $("#divValidarPrevia").hide();
                    $("#divGerarPrevia").show();
                });

                adicionaContadorTextArea(1000, "textoInicial", "qtCaracteres");

<?php } ?>

            $("#formCalendario").validate({
                submitHandler: function (form) {

                    if ($("#previaValida").val() != "true") {
                        $("#divValidarPrevia").hide();
                        $("#erroPrevia").html("Por favor, antes de salvar os dados, verifique o documento a ser publicado, consultando sua prévia.");
                        $("#erroPrevia").show();
                        $("#divGerarPrevia").show();
                        return false;
                    }

                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    textoInicial: {
                        required: true
                    },
                    ciente: {
                        required: true
                    }
                }, messages: {
                    ciente: {
                        required: "Por favor, revise o documento a ser publicado e marque ciência."
                    }
                }
            });
            // imprimindo scripts importantes de validação e apresentação
<?php
for ($i = 0; $i < count($itensCal); $i++) {
    $item = $itensCal[$i];
    ?>
                $("#<?php print $item['idInput1']; ?>").mask("99/99/9999");

    <?php if ($item['itemDuplo']) { ?>
                    $("#<?php print $item['idInput2']; ?>").mask("99/99/9999");


                    $('#<?php print $item['idInput2']; ?>').rules('add', {
                        required: <?php $item['obrigatorio'] ? print "true" : print "function (element) {
                            return $('#{$item['idInput1']}').val() != '';
                        }"; ?>,
                        dataBR: true,
                        dataBRMaiorIgual: "#<?php print $item['idInput1']; ?>",
                        messages: {
                            dataBRMaiorIgual: "Data final deve ser maior ou igual a data inicial."
                        }});
    <?php }
    ?>

                $('#<?php print $item['idInput1']; ?>').rules('add', {
                    required: <?php $item['obrigatorio'] ? print "true" : print "function (element) {
                            return " . (isset($itensCal[$i + 1]) && $itensCal[$i + 1]['itemDuplo'] ? "$('#" . $itensCal[$i + 1]['idInput2'] . "').val() != ''" : (isset($itensCal[$i + 1]) ? "$('#" . $itensCal[$i + 1]['idInput1'] . "').val() != ''" : "false")) . ";
                        }";
    ?>,
                    dataBR: true,
                    messages: {
                    }});
    <?php
    if ($i != 0) {
        ?>
                    $('#<?php print $item['idInput1']; ?>').rules('add', {
                        dataBRMaiorIgual: "#<?php $itensCal[$i - 1]['itemDuplo'] ? print $itensCal[$i - 1]['idInput2'] : print $itensCal[$i - 1]['idInput1']; ?>",
                        messages: {
                            dataBRMaiorIgual: "Esta data deve ser maior ou igual a data anterior."
                        }});
        <?php
    } else {
        ?>
                    $('#<?php print $item['idInput1']; ?>').rules('add', {
                        dataBRMaiorIgual: "#dtInicioEdital",
                        messages: {
                            dataBRMaiorIgual: "Esta data deve ser maior ou igual a data de abertura do Edital (" + $("#dtInicioEdital").val() + ")."
                        }});
        <?php
    }
}
?>

            function sucChamada() {
                $().toastmessage('showToast', {
                    text: '<b>Chamada criada com sucesso.</b> Configure o calendário...',
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
</html>

