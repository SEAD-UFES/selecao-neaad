<!DOCTYPE html>
<html>
    <head>     
        <title>Excluir Categoria de Avaliação - Seleção EAD</title>
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

        if (!$etapa->podeAlterar() || $categoriaAval->isSomenteLeitura()) {
            new Mensagem("Categoria não pode ser alterada.", Mensagem::$MENSAGEM_ERRO);
        }

        // criando filtro e paginaçao para busca de itens
        $filtro = new FiltroItemAvalProc($_GET, 'excluirCategoria', $categoriaAval->getCAP_ID_CATEGORIA_AVAL(), $categoriaAval->getPRC_ID_PROCESSO(), FALSE, "", FALSE);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>&idEtapaAval=<?php print $etapa->getEAP_ID_ETAPA_AVAL_PROC(); ?>">Gerenciar</a> > <strong>Excluir Cat. de Avaliação</strong></h1>
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

                <div class="col-full">                    
                    <fieldset class="completo m02">
                        <legend>Categoria de Avaliação</legend>
                        <div class="completo">
                            <span class="col-half">
                                <b>Tipo:</b> <?php print $categoriaAval->getNomeCategoria(); ?><br/>
                                <b>Avaliação:</b> <?php print CategoriaAvalProc::getDsTipoAval($categoriaAval->getCAP_TP_AVALIACAO()); ?><br/>
                                <b>Exclusiva:</b> <?php print $categoriaAval->getDsCatExclusiva(); ?>
                            </span>

                            <span class="col-half">
                                <b>Código:</b> <?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?><br/>
                                <b>Ordem:</b> <?php print $categoriaAval->getCAP_ORDEM(); ?><br/>
                                <b>Pontuação Máx:</b> <?php print$categoriaAval->getVlNotaMaxFormatada(); ?>
                            </span>
                        </div>
                    </fieldset>

                    <fieldset class="completo m02">
                        <legend>Itens de Avaliação da Categoria</legend>
                        <div class="col-full">
                            <?php $paginacao->imprimir(); ?>
                        </div>
                    </fieldset>

                    <?php
                    require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
                    EXC_fragmentoPergExcEmPag("formExcluir", "Todos os itens de avaliação relacionados a esta categoria serão excluídos e, se houver avaliações relacionadas a esta categoria no relatório de notas, elas serão removidas.
                                        <br/><br/>Adicionalmente, todas as configurações dos <strong>Critérios de Eliminação, Classificação, Desempate e Seleção</strong> da respectiva Etapa serão <strong>excluídos</strong>, incluindo toda a <strong>configuração do Resultado Final.</strong>");
                    ?>

                    <form class="form-horizontal completo m02" id="formExcluir" method="post" action="<?php print $CFG->rwww; ?>/controle/CTNotas.php?acao=excluirCategoriaAval">
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" id="idProcesso" name="idProcesso" value="<?php print $categoriaAval->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" id="idCategoriaAval" name="idCategoriaAval" value="<?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?>">
                        <input type="hidden" id="idEtapaAval" name="idEtapaAval" value="<?php print $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC(); ?>">
                        <div id="divBotoes" class="col-full">
                            <button class="btn btn-danger" id="submeter" type="button" role="button" data-toggle="modal" data-target="#perguntaExclusao">Excluir</button>
                            <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_AVALIACAO; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>&idEtapaAval=<?php print $etapa->getEAP_ID_ETAPA_AVAL_PROC(); ?>';" value="Voltar">
                        </div>
                        <div id="divMensagem" class="col-full" style="display:none">
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
        carregaScript("ajax");
        carregaScript("jquery.price_format");
        ?>
    </body>
</html>

