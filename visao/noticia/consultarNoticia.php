<!DOCTYPE html>
<html>
    <head>  
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        // incluindo arquivos
        require_once ($CFG->rpasta . "/controle/CTNoticia.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/util/Mensagem.php");

        // recuperando parametros
        if (!isset($_GET['idProcesso']) || !isset($_GET['idNoticia'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        $idProcesso = $_GET['idProcesso'];
        $idNoticia = $_GET['idNoticia'];
        ?>

        <title>Visualizar Notícia - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null && estaLogado(Usuario::$USUARIO_COORDENADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        // recuperando dados
        $processo = buscarProcessoComPermissaoCT($idProcesso);
        $noticia = buscarNoticiaPorIdCT($idNoticia, $idProcesso);
        $chamada = buscarChamadaPorIdCT($noticia->getPCH_ID_CHAMADA(), $noticia->getPRC_ID_PROCESSO());
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Edital</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_NOTICIA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong>Visualizar Notícia</strong></h1>
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

                <div class="contents completo m02 p15">
                    <form id="form" class="form-horizontal" method="post">

                        <div class="form-group">
                            <label for="idNoticiaTela" class="control-label col-xs-12 col-sm-4 col-md-4">Código:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" disabled="true" name="idNoticiaTela" type="text" id="idNoticiaTela" size="30" maxlength="50" value="<?php print $noticia->getNOT_ID_NOTICIA(); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label title="Pública: todos podem ver; Privada: apenas quem está inscrito no edital tem acesso." for="idTipo" class="control-label col-xs-12 col-sm-4 col-md-4">Privacidade:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" disabled="true" name="idTipo" type="text" id="idTipo" size="30" maxlength="50" value="<?php print $noticia->getDsTipoObj(); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dtValidade" class="control-label col-xs-12 col-sm-4 col-md-4">Data de validade:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" disabled="true" name="dtValidade" type="text" id="dtValidade" size="10" maxlength="10" value="<?php print $noticia->getNOT_DT_VALIDADE(TRUE); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Link:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <?php if ($noticia->temLink()) { ?>
                                    <a target="_blank" href="<?php print $noticia->getLinkNoticia(); ?>"><?php print $noticia->getLinkNoticia(); ?></a>
                                <?php } else { ?>
                                    <span><?php print Noticia::msgHtmlSemLink(); ?></span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Visualização:</label>
                            <?php print $noticia->getHtmlNoticia(); ?>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="btn btn-primary" type="button" value="Editar" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/noticia/criarEditarNoticia.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>&idChamada=<?php print $noticia->getPCH_ID_CHAMADA(); ?>&idNoticia=<?php print $noticia->getNOT_ID_NOTICIA(); ?>'">
                                <input class="btn btn-default" id="btVoltar" type="button" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_NOTICIA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>'" value="Voltar">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
</html>

