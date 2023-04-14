<?php
// deve haver a variavel para manipulacao de dados
if (isset($processo)) {

    require_once ($CFG->rpasta . "/controle/CTManutencaoProcesso.php");

    require_once ($CFG->rpasta . "/util/filtro/FiltroInfCompProc.php");

    //criando filtro
    $filtro = new FiltroInfCompProc($_GET, 'manterProcessoAdmin.php', $processo->getPRC_ID_PROCESSO(), Util::$ABA_MPA_INF_COMP, "", FALSE);

    //criando objeto de paginação
    $paginacao = new Paginacao('tabelaInfCompProcPorFiltro', 'contarInfCompProcCT', $filtro);

    $permiteManterInfComp = permiteManterGrupoAnexoProcCT($processo);
    $tituloCriarInfComp = $permiteManterInfComp ? "title='Cadastrar Nova Infomação Complementar'" : "disabled title='Não é possível cadastrar nova Informação Complementar. Edital finalizado (ou em finalização) ou já existe inscrição para este Edital'";

    // definindo se alterar ordem aparece
    $qtItem = contarInfCompProcCT($filtro);
    $styleOrdem = $qtItem <= 0 ? "style='display: none'" : "";
    $tituloOrdemInfComp = $permiteManterInfComp ? "title='Alterar ordem da Infomação Complementar'" : "disabled title='Não é possível alterar a ordem da Informação Complementar. Edital finalizado (ou em finalização) ou já existe inscrição para este Edital'";
    ?>

    <div id="mensagemInfComp<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>" style="display:none">
        <div class="alert alert-info">
            Aguarde o processamento...
        </div>
    </div>

    <div id="erroOrdemInfComp<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>" class="alert alert-danger" style="display:none">
        A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
    </div>

    <div class="completo">
        <div class="pull-left">
            <input <?php print $tituloCriarInfComp ?> class="btn btn-primary" type="button" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/grupoAnexoProc/criarEditarGrupoAnexoProc.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>'" value="Nova Inf. Complementar">
        </div>

        <div class="pull-right">
            <span id="spanVisualizacaoInfComp<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>" style="display: block">
                <input <?php print $tituloOrdemInfComp ?>  <?php print $styleOrdem; ?> class="btn btn-default" type="button" onclick="javascript: alterarOrdem('InfComp', '<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>')" value="Alterar Ordem">
            </span>
            <span id="spanEdicaoInfComp<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>" style="display: none">
                <span id="botaoInfComp<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>">
                    <input type="submit" class="btn btn-success" onclick="javascript: salvarOrdem('InfComp', '<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>')" value="Salvar">
                    <input type="button" class="btn btn-default" onclick="javascript: cancelarAlteracaoOrdem('InfComp', '<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>')" value="Cancelar">
                </span>
            </span>
        </div>
    </div>

    <?php
    require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
    EXC_fragmentoPergExcEmLista("$CFG->rwww/controle/CTManutencaoProcesso.php?acao=excluirGrupoAnexoProc", "excluirInfComp", array("idGrupoAnexoProc"), array("valido" => "ctmanutencaoprocesso", "idProcesso" => $processo->getPRC_ID_PROCESSO()), "formExcInfComp", "divPergExcInfComp", "Se esta pergunta for avaliativa, todas as configurações dos <strong>Critérios de Eliminação, Classificação, Desempate e Seleção</strong> da Etapa correspondente serão <strong>excluídos</strong>,
                        bem como toda a configuração do <strong>Resultado Final</strong>.<br/><br/>A seguinte pergunta será excluída: ", "nmPergunta");
    ?>

    <div class="completo m01">
        <span id="spanTabelaInfComp<?php print GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP; ?>">
            <?php $paginacao->imprimir(); ?>
        </span>
    </div>

    <?php
}
?>