<?php
// deve haver a variavel para manipulacao de dados
if (isset($processo)) {

    //criando filtro e objeto de paginação
    require_once ($CFG->rpasta . "/util/filtro/FiltroNoticia.php");
    $filtroNot = new FiltroNoticia($_GET, "manterProcessoAdmin.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&" . Util::$ABA_PARAM . "=" . Util::$ABA_MPA_NOTICIA, $processo->getPRC_ID_PROCESSO(), "ftNot", FALSE);
    $paginacaoNot = new Paginacao('tabelaNoticiaChamada', 'contarNoticiaPorChamadaCT', $filtroNot);

    if (isset($listaChamadas) && count($listaChamadas) > 0) {
        // percorrendo chamadas
        foreach ($listaChamadas as $chamada) {
            ?>
            <div class="panel-group" id="accordionNoticiaCham<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a class='pull-left' style='width: 100%' data-toggle='collapse' data-parent="#accordionNoticiaCham<?php print $chamada->getPCH_ID_CHAMADA(); ?>" href="#collapseNoticiaCham<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                                <?php print $chamada->getPCH_DS_CHAMADA(TRUE); ?>
                            </a> 
                        </h4>
                    </div>
                    <div id="collapseNoticiaCham<?php print $chamada->getPCH_ID_CHAMADA(); ?>" class="panel-collapse collapse <?php $chamada->isChamadaAtual() ? print "in" : ""; ?>">
                        <div class="panel-body">
                            <?php
                            $permiteCriarNoticia = $chamada->permiteEdicao();
                            $tituloNoticia = $permiteCriarNoticia ? "Criar uma Notícia" : "Não é possível criar uma notícia. Chamada finalizada";
                            ?>
                            <input title="<?php print $tituloNoticia; ?>" <?php !$permiteCriarNoticia ? print "disabled" : print "" ?> class="btn btn-primary" type="button" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/noticia/criarEditarNoticia.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>&idChamada=<?php print $chamada->getPCH_ID_CHAMADA(); ?>'" value="Nova Notícia">

                            <div class="completo">
                                <?php
                                require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
                                EXC_fragmentoPergExcEmLista("$CFG->rwww/controle/CTNoticia.php?acao=excluirNoticia", "excluirNoticia", array("idNoticia"), array("valido" => "ctnoticia", "idProcesso" => $processo->getPRC_ID_PROCESSO(), "idChamada" => $chamada->getPCH_ID_CHAMADA()));
                                ?>

                                <?php
                                $filtroNot->setIdChamada($chamada->getPCH_ID_CHAMADA());
                                $paginacaoNot->atualizaObjFiltro($filtroNot);
                                $paginacaoNot->imprimir();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
        ?>
        <?php
    } else {
        ?>
        <div class='callout callout-warning'>Este edital ainda não possui chamadas. Crie uma chamada para visualizar notícias.</div>
        <?php
    }
}
?>