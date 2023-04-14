<!DOCTYPE html>
<html>
    <head>     
        <title>Gerenciar Resultados - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");

        // apenas coordenador e administrador
        if (estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL && estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        $loginRestrito = estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL;
        if ($loginRestrito) {
            $curso = buscarCursoPorCoordenadorCT(getIdUsuarioLogado());

            if ($curso == NULL) {
                new Mensagem("Você ainda não está associado a um curso.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
        }

        //verificando passagem por get
        if (!isset($_GET['idProcesso']) || !isset($_GET['idChamada'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados para processamento
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);
        $chamada = buscarChamadaPorIdCT($_GET['idChamada'], $processo->getPRC_ID_PROCESSO());

        // tela pode ser exibida?
        if (!$processo->permiteExibirAcaoCdt($chamada)) {
            new Mensagem("Visualização não permitida. Aguarde o período adequado...", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // definindo etapa
        $idEtapaSel = isset($_GET['idEtapaSel']) && !Util::vazioNulo($_GET['idEtapaSel']) ? $_GET['idEtapaSel'] : NULL;
        $etapaVigente = buscarEtapaVigenteCT($chamada->getPCH_ID_CHAMADA(), $idEtapaSel);

        // etapa inválida
        if ($etapaVigente == NULL) {
            new Mensagem("Etapa vigente não encontrada.", Mensagem::$MENSAGEM_ERRO);
        }

        // buscando resultados publicados
        $listaResulPublicados = buscarResultadosPublicadosCT($chamada);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww ?>/visao/processo/fluxoProcesso.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Fluxo</a> > <strong>Resultados</strong></h1>
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
                                        <i class='fa fa-book'></i>
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class="barra"></separador>
                                    <b>Chamada</b>: <?php echo $chamada->getPCH_DS_CHAMADA(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <fieldset class="completo m02">
                    <legend>Resultados publicados</legend>

                    <div class="col-full">
                        <?php
                        if (Util::vazioNulo($listaResulPublicados)) {
                            ?>   
                            <div class="callout callout-info">Não há resultados publicados para esta chamada do edital.</div>
                            <?php
                        } else {
                            ?>
                            <div class='table-responsive'>
                                <table class='table table-hover table-bordered'>
                                    <thead>
                                        <tr>
                                            <th>Documento</th>
                                            <th class="campoDesktop">Publicado em</th>
                                            <th class="campoDesktop">Atualizado em</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($listaResulPublicados as $htmlResul) {
                                            print $htmlResul;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php }
                        ?>

                    </div>
                </fieldset>

                <?php
                // definindo se há resultado pendente
                $semPendencia = !EtapaSelProc::temResultadoPendente($chamada, $etapaVigente);
                //
                // tentando encontrar chamada com pendência
                if ($semPendencia && $idEtapaSel != NULL) {
                    // substituindo etapa vigente e reverificando pendência
                    $etapaVigente = buscarEtapaVigenteCT($chamada->getPCH_ID_CHAMADA());
                    $semPendencia = !EtapaSelProc::temResultadoPendente($chamada, $etapaVigente);
                }

                // botão de voltar
                $btVoltar = "<input type='button' class='btn btn-default' onclick=\"javascript: window.location = '$CFG->rwww/visao/processo/fluxoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idChamada={$chamada->getPCH_ID_CHAMADA()}'\" value='Voltar'>";
                ?>
                <fieldset class="completo m02">
                    <legend>
                        Resultado pendente<?php $semPendencia ? print "" : print ":"; ?>
                        <?php if (!$semPendencia) { ?>
                            <i>
                                <?php
                                $vetPubPendente = $etapaVigente->getResultadoPendente();
                                print $vetPubPendente[1];
                                ?>
                            </i>
                        <?php } ?>
                    </legend>

                    <div class="col-full">
                        <?php
                        // caso de não ter pendência
                        if ($semPendencia) {
                            ?>
                            <div class="callout callout-info">Não há resultado pendente de publicação.</div>
                            <?php echo $btVoltar; ?>
                            <?php
                        } else {
                            ?>

                            <?php
                            // verificando se é permitido publicar resultado
                            $valPublicacao = EtapaSelProc::validarPublicacaoResulPendente($chamada, $etapaVigente);
                            if (!$valPublicacao['val']) {
                                ?>
                                <div class='callout callout-<?php echo $valPublicacao['classe'] ?>'>
                                    <?php print $valPublicacao['html']; ?>
                                </div>
                                <?php echo $btVoltar; ?>
                                <?php
                            } else {

                                // recuperando itens do calendário e removendo itens irelevantes
                                $itensCal = $chamada->listaItensCalendario(TRUE);
                                $etapaVigente->removerItensIrrelevantesCalPubResul($chamada, $itensCal);


                                // campos obrigatórios
                                print Util::$MSG_CAMPO_OBRIG;
                                ?>

                                <div class="row">
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <div class='callout callout-success'>
                                            <p style="font-weight:bold;color:#356635">
                                                <i class="fa fa-check"></i> Tudo pronto para a publicação deste resultado.
                                            </p>
                                            <?php if ($etapaVigente->isSolicitouPublicacao($vetPubPendente[0])) {
                                                ?>
                                                Solicitação de publicação enviada
                                                em <b><?php echo $etapaVigente->getPubDtSolResul($vetPubPendente[0]); ?></b>
                                                por <b><?php echo $etapaVigente->getPubUsuRespSol($vetPubPendente[0]); ?></b>.
                                            <?php } ?>

                                            <div id="divArqInterno">
                                                <p>Revise e confirme o documento antes de prosseguir.</p>

                                                <span id="divGerarPreviaResultado">
                                                    <button title="Este é o documento que será publicado informando o resultado." onclick="javascript: visualizarPreviaResultado();" class="btn btn-info btn-sm" type="button">
                                                        Visualizar prévia do documento  <i class="fa fa-question-circle"></i>
                                                    </button>
                                                    <label id="erroPreviaResultado" class="error" style="display: none"></label>
                                                </span>

                                                <span style="display: none" id="divValidarPreviaResultado">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" id="cienteResultado" name="cienteResultado" value="<?php print FLAG_BD_SIM; ?>" onclick="javascript: $(this).is(':checked') ? $('#lbCienteResultado').hide() : $('#lbCienteResultado').show();">
                                                            <b>Revisei o documento a ser publicado</b> e está tudo certo. <a target="previaResultado" onclick="javascript: abrirPreviaResultado();">(Clique aqui para visualizar novamente o arquivo)</a>
                                                        </label>
                                                    </div>
                                                    <label id="lbCienteResultado" for="cienteResultado" class="error" style="display: none">Por favor, revise o documento do resultado e marque ciência.</label>
                                                </span>
                                            </div>

                                            <hr style="border-color: #b9ceab;"> 

                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="arquivoExterno" name="arquivoExterno" value="<?php print FLAG_BD_SIM; ?>" onclick="javascript: pubComArqExterno();">
                                                    Vou usar um arquivo externo <i title="Ao escolher esta opção, o sistema não irá gerar o arquivo de resultado automaticamente e o link de consulta de resultados será direcionado para o site da SEAD." class="fa fa-question-circle"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <form id="formPreviaResultado" target="previaResultado" method="post" action="<?php print $CFG->rwww; ?>/visao/relatorio/gerarPDFResultado.php?acao=previaResultado">
                                        <input type="hidden" name="valido" value="resultado">
                                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                                        <input type="hidden" name="idChamada" value="<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                                        <input type="hidden" name="idEtapaSel" value="<?php print $etapaVigente->getESP_ID_ETAPA_SEL(); ?>">
                                    </form>
                                </div>

                                <div class="row">
                                    <form id="form" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTManutencaoProcesso.php?acao=publicarResultado" ?>'>
                                        <input type="hidden" name="valido" value="ctmanutencaoprocesso">
                                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                                        <input type="hidden" name="idChamada" value="<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                                        <input type="hidden" name="idEtapaSel" value="<?php print $idEtapaSel; ?>">
                                        <input type="hidden" name="idEtapaVigente" value="<?php print $etapaVigente->getESP_ID_ETAPA_SEL(); ?>">
                                        <input type="hidden" id='previaValida' name="previaValida" value="true">
                                        <input type="hidden" id='previaValidaResultado' name="previaValidaResultado" value="false">
                                        <input type="hidden" id='arqExternoForm' name="arqExterno" value="false">

                                        <?php
                                        // ATENÇÃO: Layout semelhante a alterarCalendarioChamada.php
                                        // Ao alterar este arquivo, considere revisar alterarCalendarioChamada.php e criarChamada.php
                                        // 
                                        // 
                                        // É necessário mostrar calendário?
                                        if ($etapaVigente->mostrarCalendarioPubResultado($chamada, $itensCal)) {
                                            ?>
                                            <fieldset id="alterarCalendarioChamada">
                                                <legend>Configuração do Calendário</legend>

                                                <?php
                                                // percorrendo e imprimindo
                                                foreach ($itensCal as $item) {
                                                    $classeItem = $item['status'] == ProcessoChamada::$EVENTO_PASSADO ? "" : ($item['status'] == ProcessoChamada::$EVENTO_PRESENTE ? : "");
                                                    $dsEditavel = !$item['editavel'] ? "disabled" : "";
                                                    ?>
                                                    <div class="calendario form-group">
                                                        <label class="control-label col-xs-12 col-sm-4 col-md-4 <?php print $classeItem; ?>"><?php print $item['nmItem']; ?> *</label>
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
                                                    <?php
                                                }
                                                ?> 
                                            </fieldset>

                                        <fieldset class="m02" id="fieldsetNotificacaoCal" style="display: none">
                                                <legend>Dados da notificação de mudança do calendário</legend>
                                                <div class="form-group">
                                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Texto inicial: * <i title="Este é o texto exibido no início da notificação. Utilize esse espaço para introduzir a modificação e explicar os motivos." class="fa fa-question-circle"></i></label>
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
                                                                <input type="checkbox" id="ciente" name="ciente" value="<?php print FLAG_BD_SIM; ?>" checked="">
                                                                <b>Revisei o documento a ser publicado</b> e ele está pronto para publicação. <a target="previaCalendario" onclick="javascript: abrirPrevia();">Clique aqui para visualizar novamente o arquivo.</a>
                                                                <label for="ciente" class="error" style="display: none"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <?php
                                        }

                                        // Analisando caso de flags especiais
                                        if ($etapaVigente->mostrarOpcaoFinImedPubResul($chamada)) {
                                            ?>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class='completo callout callout-info'>
                                                    <?php print $etapaVigente->getMsgOpcaoFinImedPubResul($chamada); ?>

                                                    <?php print $etapaVigente->getOpcaoFinImedPubResul($chamada); ?>
                                                </div>
                                            </div>
                                        <?php }
                                        ?>

                                        <div id="divBotoes" class="completo">
                                            <div class="form-group">
                                                <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                                <div class="col-xs-12 col-sm-8 col-md-8">
                                                    <input id="submeter" class="btn btn-success" type="submit" value="Publicar">
                                                    <?php echo $btVoltar; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="divMensagem" class="col-full" style="display:none">
                                            <div class="alert alert-info">
                                                Aguarde o processamento...
                                            </div>
                                        </div>
                                    </form>

                                    <form id="formPrevia" target="previaCalendario" method="post" action="<?php print $CFG->rwww; ?>/visao/relatorio/gerarPDFRetificacaoCalendario.php?acao=previaCalendario">
                                        <input type="hidden" name="dadosPrevia" id="dadosPrevia">
                                        <input type="hidden" name="valido" value="retificacaocalendario">
                                    </form>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </fieldset>
            </div>
        </div>  
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("jquery.price_format");
        carregaScript("jquery.maskedinput");
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        ?>
    </body>

    <script type="text/javascript">
        function abrirPrevia() {
            window.open('', 'previaCalendario');
            $("#dadosPrevia").val($("#form").serialize());
            $("#formPrevia").submit();
        }

        // solicitando prévia
        function visualizarPreviaCalendario() {
            // form valido
            if (!$("#form").valid()) {
                $("#erroPrevia").html("É necessário preencher todos os campos corretamente para visualizar a prévia.");
                $("#erroPrevia").show();
                return;
            }

            // validando exibição da prévia
            $.ajax({
                type: "POST",
                url: getURLServidor() + "/visao/relatorio/gerarPDFRetificacaoCalendario.php?acao=validarPrevia",
                data: {"valido": "retificacaocalendario", "dadosPrevia": $("#form").serialize()},
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
                        // caso de não ter alteração
                        if (json['semModificacao']) {
                            // válido! apenas redefinindo controles
                            $("#erroPrevia").html("");
                            $("#erroPrevia").hide();
                            $("#previaValida").val("true");
                            $("#ciente").attr("checked", true);
                            $("#fieldsetNotificacaoCal").hide();
                        }

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

        function abrirPreviaResultado() {
            window.open('', 'previaResultado');
            $("#formPreviaResultado").submit();
        }

        // solicitando prévia
        function visualizarPreviaResultado() {
            // validando exibição da prévia
            $.ajax({
                type: "POST",
                url: getURLServidor() + "/visao/relatorio/gerarPDFResultado.php?acao=validarPrevia",
                data: {"valido": "resultado", "idProcesso": '<?php echo $processo->getPRC_ID_PROCESSO(); ?>', "idChamada": '<?php echo $chamada->getPCH_ID_CHAMADA(); ?>', "idEtapaSel": '<?php echo$etapaVigente->getESP_ID_ETAPA_SEL(); ?>'},
                dataType: "json",
                async: false,
                success: function (json) {
                    if (json['val'])
                    {
                        abrirPreviaResultado();
                        $("#erroPreviaResultado").html("");
                        $("#erroPreviaResultado").hide();

                        $("#divGerarPreviaResultado").hide();
                        $("#divValidarPreviaResultado").show();
                        $("#previaValidaResultado").val("true");

                    } else {
                        $("#erroPreviaResultado").html(json['msg']);
                        $("#erroPreviaResultado").show();
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

        //  Habilita publicação com arquivo externo
        function pubComArqExterno() {
            if ($("#arquivoExterno").is(':checked')) {
                $("#previaValidaResultado").val("true");
                $("#arqExternoForm").val("true");
                $("#divArqInterno").hide();
            } else {
                $("#previaValidaResultado").val($("#cienteResultado").is(':checked') ? "true" : "false");
                $("#arqExternoForm").val("false");
                $("#divArqInterno").show();
            }
        }


        function solicitarPublicacao(idProcesso, idChamada, idEtapaSel) {
            // preparando tela para processamento...
            $("#divSolPublicacaoResul").hide();
            $("#mensagemSolPublicacaoResul").show();

            // enviando dados 
            $.ajax({
                type: "POST",
                url: getURLServidor() + "/controle/CTAjax.php?atualizacao=solicitarPublicacaoResul",
                data: {"idProcesso": idProcesso, "idChamada": idChamada, "idEtapaSel": idEtapaSel},
                dataType: "json",
                success: function (json) {
                    if (!json['situacao'])
                    {
                        //alert de erro
                        alert("Não foi possível solicitar a publicação do resultado:\n" + json['msg']);

                        // restabelecendo
                        $("#mensagemSolPublicacaoResul").hide();
                        $("#divSolPublicacaoResul").show();
                    } else {

                        alert("Solicitação de publicação executada com sucesso.");
                        location.reload();

                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nTente novamente.\n\n";
                    msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                    // exibindo mensagem
                    alert(msg);

                    // restabelecendo
                    $("#mensagemSolPublicacaoResul").hide();
                    $("#divSolPublicacaoResul").show();

                }
            });
            return false;
        }


        $(document).ready(function () {

<?php if (!$semPendencia && isset($valPublicacao) && $valPublicacao['val'] && $etapaVigente->mostrarCalendarioPubResultado($chamada, $itensCal)) { ?>

                $("#form").find("input[type='text'],textarea").change(function () {
                    $("#previaValida").val("false");
                    $("#ciente").attr("checked", false);
                    $("#divValidarPrevia").hide();
                    $("#divGerarPrevia").show();
                    $("#fieldsetNotificacaoCal").show();
                });

                adicionaContadorTextArea(1000, "textoInicial", "qtCaracteres");

<?php } ?>

            //validando form
            $("#form").validate({
                submitHandler: function (form) {
                    // verificando prévia válida
                    if ($("#previaValida").val() != "true") {
                        $("#divValidarPrevia").hide();
                        $("#erroPrevia").html("Por favor, antes de publicar o resultado, verifique o documento de retificação do calendário, consultando sua prévia.");
                        $("#erroPrevia").show();
                        $("#divGerarPrevia").show();
                        return false;
                    }

                    // verificando prévia de resultado válida
                    if ($("#previaValidaResultado").val() != "true") {
                        $("#divValidarPreviaResultado").hide();
                        $("#erroPreviaResultado").html("Por favor, antes de publicar o resultado, verifique o documento do resultado, consultando sua prévia.");
                        $("#erroPreviaResultado").show();
                        $("#divGerarPreviaResultado").show();
                        return false;
                    }

                    // verificando ciência de visualização do arquivo de resultado
                    if (!$("#arquivoExterno").is(":checked") && !$("#cienteResultado").is(":checked")) {
                        $("#lbCienteResultado").show();
                        return false;
                    }

                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    idEtapaSel: {
                        required: true
                    },
                    textoInicial: {
                        required: true
                    },
                    ciente: {
                        required: true
                    }
                }
                , messages: {
                    ciente: {
                        required: "Por favor, revise o documento de retificação do calendário e marque ciência."
                    }
                }
            }
            );

<?php if (!$semPendencia && isset($valPublicacao) && $valPublicacao['val'] && $etapaVigente->isOpcaoFimImedDtFinalizacao($chamada)) { ?>
                // aplicando scrits para data de finalização
                $("#dtFinalizacao").mask("99/99/9999");
                $('#dtFinalizacao').rules('add', {
                    required: true,
                    dataBR: true,
                    dataBRMaior: new Date(),
                    messages: {
                        dataBRMaior: "Data de finalização deve ser maior que a data atual."
                    }});
<?php } ?>


            // imprimindo scripts importantes de validação e apresentação
<?php
if (!$semPendencia && isset($valPublicacao) && $valPublicacao['val'] && $etapaVigente->mostrarCalendarioPubResultado($chamada, $itensCal)) {
    $chavesItem = array_keys($itensCal);
    for ($j = 0; $j < count($chavesItem); $j++) {
        $item = $itensCal[$chavesItem[$j]];
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
                            return " . (isset($itensCal[$chavesItem[$j + 1]]) && $itensCal[$chavesItem[$j + 1]]['itemDuplo'] ? "$('#" . $itensCal[$chavesItem[$j + 1]]['idInput2'] . "').val() != ''" : (isset($itensCal[$chavesItem[$j + 1]]) ? "$('#" . $itensCal[$chavesItem[$j + 1]]['idInput1'] . "').val() != ''" : "false")) . ";
                        }";
        ?>,
                        dataBR: true,
                        messages: {
                        }});
        <?php
        if ($j != 0) {
            ?>
                        $('#<?php print $item['idInput1']; ?>').rules('add', {
                            dataBRMaiorIgual: "#<?php $itensCal[$chavesItem[$j - 1]]['itemDuplo'] ? print $itensCal[$chavesItem[$j - 1]]['idInput2'] : print $itensCal[$chavesItem[$j - 1]]['idInput1']; ?>",
                            messages: {
                                dataBRMaiorIgual: "Esta data deve ser maior ou igual a data anterior."
                            }});
            <?php
        } else {
            ?>
                        $('#<?php print $item['idInput1']; ?>').rules('add', {
                            dataBRMaiorIgual: new Date(),
                            messages: {
                                dataBRMaiorIgual: "Esta data deve ser maior ou igual a data de hoje."
                            }});
            <?php
        }
    }
}
?>

            function sucPubResultado() {
                $().toastmessage('showToast', {
                    text: '<b>Resultado publicado com sucesso.</b>',
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