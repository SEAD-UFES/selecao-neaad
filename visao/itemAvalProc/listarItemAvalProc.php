<!DOCTYPE html>
<html>
    <head>     
        <title>Itens de Avaliação da Categoria - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/util/filtro/FiltroItemAvalProc.php");
        require_once ($CFG->rpasta . "/controle/CTNotas.php");


        // coordenador ou administrador
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null && estaLogado(Usuario::$USUARIO_COORDENADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idCategoriaAval'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // recuperando categoria
        $categoriaAval = buscarCatAvalPorIdCT($_GET['idCategoriaAval']);

        $idProcesso = $categoriaAval->getPRC_ID_PROCESSO();
        $idEtapaAval = $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC();

        // buscando processo
        $processo = buscarProcessoComPermissaoCT($idProcesso);

        // buscando etapa
        $etapa = buscarEtapaAvalPorIdCT($idEtapaAval, $processo->getPRC_ID_PROCESSO());

        // criando filtro e paginaçao para busca de itens
        $filtro = new FiltroItemAvalProc($_GET, 'listarItemAvalProc', $categoriaAval->getCAP_ID_CATEGORIA_AVAL(), $categoriaAval->getPRC_ID_PROCESSO(), TRUE, "", FALSE);
        $paginacao = new Paginacao('tabelaItemAvalPorCategoria', 'contarItemAvalPorCategoriaCT', $filtro);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong>Itens de Avaliação</strong></h1>
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
                                    <b>Etapa:</b> <?php print $etapa->getNomeEtapa(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full m02">
                    <fieldset>
                        <legend>Categoria de Avaliação</legend>
                        <div class="completo">
                            <div class="col-half">
                                <p><b>Tipo:</b> <?php print $categoriaAval->getNomeCategoria(); ?></p>
                                <p><b>Avaliação:</b> <?php print CategoriaAvalProc::getDsTipoAval($categoriaAval->getCAP_TP_AVALIACAO()); ?></p>
                                <p><b>Exclusiva:</b> <?php print $categoriaAval->getDsCatExclusiva(); ?></p>
                            </div>
                            <div class="col-half">
                                <p><b>Código:</b> <?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?></p>
                                <p><b>Ordem:</b> <?php print $categoriaAval->getCAP_ORDEM(); ?></p>
                                <p><b>Pontuação Máx:</b> <?php print$categoriaAval->getVlNotaMaxFormatada(); ?></p>
                            </div>
                        </div>
                    </fieldset>

                    <?php
                    require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");

                    // esqueleto de exclusão de etapa
                    EXC_fragmentoPergExcEmLista("$CFG->rwww/controle/CTNotas.php?acao=excluirItemAval", "excluirItemAval", array("idItemAval"), array("valido" => "ctnotas", "idProcesso" => $processo->getPRC_ID_PROCESSO(), "idCategoriaAval" => $categoriaAval->getCAP_ID_CATEGORIA_AVAL()), "formExcItemAval", "divPergExcItemAval", "Se houver avaliações relacionadas a este item, elas serão removidas do relatório de notas."
                            . "<br/><br/>Adicionalmente, todas as configurações dos <strong>Critérios de Eliminação, Classificação, Desempate e Seleção</strong> da respectiva Etapa serão <strong>excluídos</strong>, incluindo toda a <strong>configuração do Resultado Final.</strong><br/><br/>Código do item a ser excluído: ", "nmItemAval");
                    ?>

                    <fieldset class="m02">
                        <legend>Itens de Avaliação da Categoria</legend>
                        <div class="col-full">
                            <?php
                            $inicioTitulo = !$categoriaAval->isSomenteLeitura() ? "Existe uma etapa de seleção finalizada." : "Categoria gerada automaticamente.";
                            ?>
                            <input title="<?php !$etapa->podeAlterar() || $categoriaAval->isSomenteLeitura() ? print "$inicioTitulo Não é possível adicionar item de avaliação." : print "Adicionar item de avaliação." ?>" <?php !$etapa->podeAlterar() || $categoriaAval->isSomenteLeitura() ? print "disabled" : print "" ?> id="botaoCat" class="btn btn-primary" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/itemAvalProc/criarEditarItemAval.php?idProcesso={$etapa->getPRC_ID_PROCESSO()}&idCategoriaAval={$categoriaAval->getCAP_ID_CATEGORIA_AVAL()}"; ?>'" value="Novo Item">
                        </div>

                        <div class="col-full">
                            <?php $paginacao->imprimir(); ?>
                        </div>
                    </fieldset>

                    <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>&idEtapaAval=<?php print $etapa->getEAP_ID_ETAPA_AVAL_PROC(); ?>';" value="Voltar">
                </div>
            </div>
        </div>
    </div>
    <?php
    include ($CFG->rpasta . "/include/rodape.php");
    carregaScript("ajax");
    carregaScript("jquery.price_format");
    ?>
</body>
<script type="text/javascript">
    $(document).ready(function () {
        function sucInsercao() {
            $().toastmessage('showToast', {
                text: '<b>Item adicionado com sucesso.</b>',
                sticky: false,
                type: 'success',
                position: 'top-right'
            });
        }

        function sucAtualizacao() {
            $().toastmessage('showToast', {
                text: '<b>Item atualizado com sucesso.</b>',
                sticky: false,
                type: 'success',
                position: 'top-right'
            });
        }


        function sucExclusao() {
            $().toastmessage('showToast', {
                text: '<b>Item excluído com sucesso.</b>',
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

