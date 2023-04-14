<?php
// deve haver a variavel para manipulacao de dados
if (isset($processo)) {

    require_once ($CFG->rpasta . "/controle/CTManutencaoProcesso.php");

    // recuperando etapas do BD
    $etapasAval = buscarEtapaAvalPorProcCT($processo->getPRC_ID_PROCESSO());

    // verificando qual etapa deve estar aberta
    $idEtapaAberta = isset($_GET['idEtapaAval']) ? $_GET['idEtapaAval'] : NULL;
    $idEtapaResulFinal = MacroConfProc::$ID_ETAPA_RESULTADO_FINAL;
    ?>

    <input title="<?php !permiteCriarEtapaAvalCT($processo) ? print "Não é possível adicionar etapa de avaliação. O edital está finalizado (ou em finalização), ou já existe uma chamada configurada" : print "Adicionar etapa de avaliação" ?>" <?php !permiteCriarEtapaAvalCT($processo) ? print "disabled" : print "" ?> id="botaoAval" class="btn btn-primary" type="button" onclick="javascript: criarEtapa();" value="Nova Etapa">
    <div id="mensagemAval" style="display:none">
        <div class="alert alert-info">
            Aguarde o processamento...
        </div>
    </div>
    <script type="text/javascript">
        // funcao de criaçao de etapa
        function criarEtapa() {
            // escondendo botao para processamento
            $("#botaoAval").hide();
            $("#mensagemAval").show();
            // criando no bd
            criaEtapaBD();
        }

        // executa operacao de criacao de etapa no BD
        function criaEtapaBD() {
            // enviando dados 
            $.ajax({
                type: "POST",
                url: getURLServidor() + "/controle/CTAjax.php?atualizacao=criarEtapaAval",
                data: {"idProcesso": '<?php print $processo->getPRC_ID_PROCESSO(); ?>'},
                dataType: "json",
                success: function (json) {

                    //restabelecendo pagina
                    $("#mensagemAval").hide();
                    $("#botaoAval").show();
                    if (!json['situacao'])
                    {
                        // deu erro na criacao: criando toast de erro
                        $().toastmessage('showToast', {
                            text: '<b>Erro ao criar Etapa de Avaliação:</b> ' + json['msg'],
                            sticky: true,
                            type: 'error',
                            position: 'top-right'
                        });
                    } else {

                        // inserindo nova etapa
                        $("#accordion1").append(json['htmlEtapa']);

                        // criando toast de sucesso
                        $().toastmessage('showToast', {
                            text: '<b>Etapa de Avaliação ' + json['nrEtapa'] + ' criada com sucesso.</b>',
                            sticky: false,
                            type: 'success',
                            position: 'top-right'
                        });
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nTente novamente.\n\n";
                    msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;
                    // exibindo mensagem
                    alert(msg);
                    //restabelecendo
                    $("#mensagemAval").hide();
                    $("#botaoAval").show();
                }
            });
        }
    </script>

    <?php
    require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");

    // esqueleto de exclusão de etapa
    EXC_fragmentoPergExcEmLista("$CFG->rwww/controle/CTNotas.php?acao=excluirEtapaAval", "excluirEtapa", array("idEtapaAval"), array("valido" => "ctnotas", "idProcesso" => $processo->getPRC_ID_PROCESSO()), "formExcEtapa", "divPergExcEtapa", "Se necessário, a numeração das etapas será reajustada.
                        Adicionalmente, toda a configuração do <strong>Resultado Final</strong> será excluída.<br/><br/>A seguinte etapa será excluída: ", "nmEtapa");

    // exclusão de macros de configuração do processo
    EXC_fragmentoPergExcEmLista("$CFG->rwww/controle/CTNotas.php?acao=excluirMacroConfProc", "excluirMacroConfProc", array("idMacroConfProc"), array("valido" => "ctnotas", "idProcesso" => $processo->getPRC_ID_PROCESSO()), "formExcMacroConfProc", "divPergExcMacroConfProc", "O seguinte item será permanentemente excluído: ", "dsTipoMacro");
    ?>

    <div class="panel-group m02" id="accordion1">
        <?php
        // percorrendo etapas e exibindo
        if ($etapasAval != NULL) {
            foreach ($etapasAval as $etapa) {
                echo getHtmlEtapaAval($etapa, $idEtapaAberta != NULL && $idEtapaAberta == $etapa->getEAP_ID_ETAPA_AVAL_PROC());
            }
        }
        ?>
    </div>


    <a id='linkResultadoFinal' href='#resultadoFinal' style='display: none'></a>
    <fieldset class="completo m02" id="resultadoFinal">
        <legend>Resultado Final</legend>
        <?php
        // recuperando macro de nota final
        $macroFinal = buscarMacroConfNotaFinalCT($processo->getPRC_ID_PROCESSO());

        // verificando permissao de composição de fórmula
        if (permiteComporNotaFinalCT($processo)) {
            $titleFormFinal = "title='Alterar fórmula da nota final'";
            $htmlAddBotaoFormFinal = "$titleFormFinal";
            $htmlAddDivFormFinal = "$titleFormFinal onclick=\"javascript: window.location = '$CFG->rwww/visao/processo/manterFormulaFinal.php?idProcesso={$processo->getPRC_ID_PROCESSO()}'\"";
        } else {
            $titleFormFinal = "title='Não é possível alterar a fórmula da nota final'";
            $htmlAddBotaoFormFinal = "$titleFormFinal disabled ";
            $htmlAddDivFormFinal = "$titleFormFinal onclick=\"javascript: return false;\"";
        }
        // defindo visualização de ordem
        $qtItemCritClas = contarMacroPorProcEtapaCT($processo->getPRC_ID_PROCESSO(), NULL, MacroConfProc::$TIPO_CRIT_CLASSIFICACAO);
        $styleOrdemCritClas = $qtItemCritClas <= 0 ? "style='display: none'" : "";

        $qtItemCritDes = contarMacroPorProcEtapaCT($processo->getPRC_ID_PROCESSO(), NULL, MacroConfProc::$TIPO_CRIT_DESEMPATE);
        $styleOrdemCritDes = $qtItemCritDes <= 0 ? "style='display: none'" : "";

        $qtItemCritCadRes = contarMacroPorProcEtapaCT($processo->getPRC_ID_PROCESSO(), NULL, MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA);
        $styleOrdemCritCadRes = $qtItemCritCadRes <= 0 ? "style='display: none'" : "";
        ?>
        <form class="form-horizontal completo">
            <div class="form-group">
                <label class="col-sm-3 control-label">Fórmula da Nota Final:</label>
                <div class="col-sm-9">
                    <?php if ($macroFinal == NULL) { ?>
                        <button type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/processo/manterFormulaFinal.php?idProcesso={$processo->getPRC_ID_PROCESSO()}" ?>'" class="btn btn-primary" <?php print $htmlAddBotaoFormFinal; ?>>Criar Fórmula</button>
                    <?php } else {
                        ?>
                        <div class="input-group">
                            <input disabled class="form-control" type="text" placeholder="" value="<?php print $macroFinal->getMCP_DS_PARAMETROS(); ?>">
                            <div style="cursor: pointer;" class="input-group-addon btn-default" <?php print $htmlAddDivFormFinal; ?>>Alterar</div>
                        </div>
                    <?php }
                    ?>
                </div>
            </div>
        </form>

        <a id="linkCritClassificacao<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" href="#critClassificacaoProc<?php print $processo->getPRC_ID_PROCESSO(); ?>" style="display: none"></a>
        <fieldset class="itemInterno completo m02" id="critClassificacaoProc<?php print $processo->getPRC_ID_PROCESSO() ?>">

            <legend>Critérios de Classificação Final <small><i class="fa fa-question-circle" title="Se não informado, a classificação será decrescente, de acordo com a nota total obtida."></i></small></legend>

            <div id="mensagemCritCla<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display:none">
                <div class="alert alert-info">
                    Aguarde o processamento...
                </div>
            </div>

            <div id="erroOrdemCritCla<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" class="alert alert-danger" style="display:none">
                A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
            </div>

            <div class="col-full">
                <div class="completo">
                    <div class="pull-left">
                        <input title="<?php !$processo->permiteEdicao(TRUE) ? print "Edital Finalizado. Não é possível adicionar critério de classificação final" : print "Adicionar critério de classificação" ?>" <?php !$processo->permiteEdicao(TRUE) ? print "disabled" : print "" ?> id="botaoCritClaProc" class="btn btn-primary" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/macroConfProc/manterCriterioClassificacao.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idEtapaAval=$idEtapaResulFinal"; ?>'" value="Novo Critério">
                    </div>
                    <div class="pull-right">
                        <span id="spanVisualizacaoCritCla<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display: block">
                            <input <?php print $styleOrdemCritClas; ?> title="<?php !$processo->permiteEdicao(TRUE) ? print "Não é possível alterar a ordem do critério de classificação final" : print "Alterar ordem do critério de classificação" ?>" <?php !$processo->permiteEdicao(TRUE) ? print "disabled" : print "" ?> class="btn btn-default" type="button" onclick="javascript: alterarOrdem('CritCla', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Alterar Ordem">
                        </span>
                        <span id="spanEdicaoCritCla<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display: none">
                            <span id="botaoCritCla<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>">
                                <input type="submit" class="btn btn-success" onclick="javascript: salvarOrdem('CritCla', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Salvar">
                                <input type="button" class="btn btn-default" onclick="javascript: cancelarAlteracaoOrdem('CritCla', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Cancelar">
                            </span>
                        </span>
                    </div>
                </div>

                <div class="completo m01">
                    <span id="spanTabelaCritCla<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>">
                        <?php print tabelaMacroConfProcPorProcEtapa(NULL, MacroConfProc::$TIPO_CRIT_CLASSIFICACAO, $processo); ?>
                    </span>
                </div>
            </div>

            <a id="linkCritDesempate<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" href="#critDesempateProc<?php print $processo->getPRC_ID_PROCESSO(); ?>" style="display: none"></a>
            <fieldset class="itemInterno completo m02" id="critDesempateProc<?php print $processo->getPRC_ID_PROCESSO(); ?>">

                <legend>Desempate Final <small><i class="fa fa-question-circle" title="Na persistência de empate, o critério final é a ordem de inscrição."></i></small></legend>

                <div id="mensagemCritDes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display:none">
                    <div class="alert alert-info">
                        Aguarde o processamento...
                    </div>
                </div>

                <div id="erroOrdemCritDes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" class="alert alert-danger" style="display:none">
                    A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
                </div>

                <div class="col-full">
                    <div class="completo">
                        <div class="pull-left">
                            <input title="<?php !$processo->permiteEdicao(TRUE) ? print "Edital Finalizado. Não é possível adicionar critério de desempate final" : print "Adicionar critério de desempate" ?>" <?php !$processo->permiteEdicao(TRUE) ? print "disabled" : print "" ?> id="botaoCritDesProc" class="btn btn-primary" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/macroConfProc/manterCriterioDesempate.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idEtapaAval=$idEtapaResulFinal"; ?>'" value="Novo Critério">
                        </div>
                        <div class="pull-right">
                            <span id="spanVisualizacaoCritDes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display: block">
                                <input <?php print $styleOrdemCritDes; ?> title="<?php !$processo->permiteEdicao(TRUE) ? print "Não é possível alterar a ordem do critério de desempate final" : print "Alterar ordem do critério de desempate" ?>" <?php !$processo->permiteEdicao(TRUE) ? print "disabled" : print "" ?> class="btn btn-default" type="button" onclick="javascript: alterarOrdem('CritDes', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Alterar Ordem">
                            </span>

                            <span id="spanEdicaoCritDes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display: none">
                                <span id="botaoCritDes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>">
                                    <input type="submit" class="btn btn-success" onclick="javascript: salvarOrdem('CritDes', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Salvar">
                                    <input type="button" class="btn btn-default" onclick="javascript: cancelarAlteracaoOrdem('CritDes', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Cancelar">
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="completo m01">
                        <span id="spanTabelaCritDes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>">
                            <?php print tabelaMacroConfProcPorProcEtapa(NULL, MacroConfProc::$TIPO_CRIT_DESEMPATE, $processo); ?>
                        </span>
                    </div>
                </div>
            </fieldset>
        </fieldset>

        <a id="linkCritCadReserva<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" href="#critCadReservaProc<?php print $processo->getPRC_ID_PROCESSO(); ?>" style="display: none"></a>
        <fieldset class="itemInterno completo m02" id="critCadReservaProc<?php print $processo->getPRC_ID_PROCESSO(); ?>">

            <legend>Critérios de Seleção - Cadastro de Reserva <small><i class="fa fa-question-circle" title="Após preencher as vagas previstas no Edital, informe como deve ser o Cadastro de Reserva."></i></small></legend>

            <div id="mensagemCritRes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display:none">
                <div class="alert alert-info">
                    Aguarde o processamento...
                </div>
            </div>

            <div id="erroOrdemCritRes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" class="alert alert-danger" style="display:none">
                A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
            </div>

            <div class="col-full">
                <div class="completo">
                    <div class="pull-left">
                        <input title="<?php !$processo->permiteEdicao(TRUE) || $qtItemCritCadRes > 0 ? print "Não é possível adicionar critério de seleção - Cadastro de Reserva" : print "Adicionar critério de seleção - Cadastro de Reserva" ?>" <?php !$processo->permiteEdicao(TRUE) || $qtItemCritCadRes > 0 ? print "disabled" : print "" ?> id="botaoCritResProc" class="btn btn-primary" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/macroConfProc/manterCriterioCadReserva.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idEtapaAval=$idEtapaResulFinal"; ?>'" value="Novo Critério">
                    </div>
                    <div class="pull-right">
                        <span id="spanVisualizacaoCritRes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display: block">
                            <input <?php print $styleOrdemCritCadRes; ?> title="<?php !$processo->permiteEdicao(TRUE) ? print "Não é possível alterar a ordem do critério de seleção - Cadastro de Reserva" : print "Alterar ordem do critério de seleção - Cadastro de Reserva" ?>" <?php !$processo->permiteEdicao(TRUE) ? print "disabled" : print "" ?> class="btn btn-default" type="button" onclick="javascript: alterarOrdem('CritRes', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Alterar Ordem">
                        </span>

                        <span id="spanEdicaoCritRes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>" style="display: none">
                            <span id="botaoCritRes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>">
                                <input type="submit" class="btn btn-success" onclick="javascript: salvarOrdem('CritRes', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Salvar">
                                <input type="button" class="btn btn-default" onclick="javascript: cancelarAlteracaoOrdem('CritRes', '<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>')" value="Cancelar">
                            </span>
                        </span>
                    </div>
                </div>

                <div class="completo m01">
                    <span id="spanTabelaCritRes<?php print MacroConfProc::$ID_ETAPA_RESULTADO_FINAL; ?>">
                        <?php print tabelaMacroConfProcPorProcEtapa(NULL, MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA, $processo); ?>
                    </span>
                </div>
            </div>
        </fieldset>
    </fieldset>
    <?php
}
?>