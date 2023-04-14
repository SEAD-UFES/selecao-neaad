<!DOCTYPE html>
<html>
    <head>     
        <title>Resumo das Inscrições - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTManutencaoProcesso.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");

        if (estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL && estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
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

        // tela pode ser exibida?
        if (!$processo->permiteExibirAcaoCdt()) {
            new Mensagem("Visualização não permitida. Aguarde o período adequado...", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // recuperando chamadas do processo para montar abas
        $chamada = buscarChamadaPorIdCT($processo->PCH_ID_ULT_CHAMADA, $processo->getPRC_ID_PROCESSO());
        $listaChamadas = buscarChamadaPorProcessoCT($processo->getPRC_ID_PROCESSO(), TRUE);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww ?>/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Inscrições</a> > <strong>Resumo</strong></h1>
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
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full m02">
                    <div class="tabbable">
                        <ul id="tabProcesso" class="nav nav-tabs">
                            <?php
                            // percorrendo chamadas para criar título das abas
                            foreach ($listaChamadas as $chamadaTemp) {
                                ?>
                                <li <?php $chamadaTemp->getPCH_ID_CHAMADA() === $chamada->getPCH_ID_CHAMADA() ? print "class='active'" : print ""; ?>><a href="#chamada<?php echo $chamadaTemp->getPCH_ID_CHAMADA(); ?>" data-toggle="tab"><?php echo $chamadaTemp->getPCH_DS_CHAMADA(TRUE); ?></a></li>
                            <?php } ?>
                        </ul>

                        <div class="tab-content">
                            <?php
                            // percorrendo chamadas para preencher dados
                            foreach ($listaChamadas as $chamadaTemp) {
                                ?>
                                <div class="tab-pane <?php $chamadaTemp->isChamadaAtual() ? print "active" : print ""; ?>" id="chamada<?php echo $chamadaTemp->getPCH_ID_CHAMADA(); ?>">
                                    <fieldset class="col-full m02">          
                                        <legend>Inscrições</legend>
                                        <?php
                                        echo tabelaResumoPorChamada($chamadaTemp, $processo);
                                        ?>
                                    </fieldset>

                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <input class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}" ?>'" value="Voltar">
                </div>
            </div>
        </div>  
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
</html>