<!DOCTYPE html>
<html>
    <head>     
        <title>Recursos do Edital - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>	

        <?php
        //verificando se está logado como candidato
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        require_once ($CFG->rpasta . "/util/filtro/FiltroRecurso.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/controle/CTNotas.php");

        // verificando passagem por get
        if (!isset($_GET['idInscricao'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // recuperando inscriçao
        $inscricao = buscarInscricaoComPermissaoCT($_GET['idInscricao'], getIdUsuarioLogado());

        // recuperando dados para apresentação
        $processo = buscarProcessoPorIdCT($inscricao->getPRC_ID_PROCESSO());
        $chamada = buscarChamadaPorIdCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getPRC_ID_PROCESSO());

        // recuperando etapa em recurso
        $etapaRecurso = buscarEtapaEmRecursoCT($inscricao->getPCH_ID_CHAMADA());

        // alguem em recurso?
        $semRecurso = $etapaRecurso === NULL;

        //criando filtro
        $filtro = new FiltroRecurso($_GET, 'listarRecursoCandidato.php', $inscricao->getIPR_ID_INSCRICAO());

        //criando objeto de paginação
        $paginacao = new Paginacao('tabelaRecursosPorInscricao', 'contarRecursosPorInscricaoCT', $filtro);
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
                    <h1>Você está em: Editais > <a href="<?php print $CFG->rwww; ?>/visao/inscricaoProcesso/listarInscProcessoUsuario.php">Minhas Inscrições</a> > <strong>Recursos</strong></h1>
                </div>

                <?php
                // recuperando dados para montar relatorio.
                // recuperando etapas
                $etapas = buscarEtapaPorChamadaCT($inscricao->getPCH_ID_CHAMADA());
                ?>

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
                                        <?php print $processo->getHTMLDsEditalCompleta(); ?>
                                    </p>
                                    <p> 
                                        <i class="fa fa-user"></i> 
                                        <b>Inscrição: </b> <?php print $inscricao->getIPR_NR_ORDEM_INSC(); ?> <separador class='barra'></separador> 
                                    <b>Chamada: </b> <?php print $inscricao->PCH_DS_CHAMADA; ?> <separador class='barra'></separador>
                                    <b>Data: </b> <?php print $inscricao->getIPR_DT_INSCRICAO(); ?>
                                    </p>
                                    <?php
                                    // tem opção de inscrição?
                                    if (ProcessoChamada::temOpcaoInscricao($chamada)) {
                                        $barra = FALSE;
                                        ?>
                                        <p>
                                            <i class="fa fa-info-circle"></i>
                                            <?php
                                            // área de atuação
                                            if ($chamada->admiteAreaAtuacaoObj()) {
                                                // recuperando area
                                                $areaAtu = buscarAreaAtuChamadaPorIdCT($inscricao->getAAC_ID_AREA_CHAMADA());
                                                ?>
                                                <b>Área:</b> <?php print $areaAtu->ARC_NM_SUBAREA_CONH; ?>
                                                <?php
                                                $barra = TRUE;
                                            }

                                            // reserva de vaga
                                            if ($chamada->admiteReservaVagaObj()) {
                                                ?>
                                                <?php if ($barra) { ?>
                                                <separador class='barra'></separador>
                                            <?php } ?>
                                            <b>Vaga:</b> <?php print getDsReservaVagaInscricaoCT($inscricao->getRVC_ID_RESERVA_CHAMADA()); ?>
                                            <?php
                                            $barra = TRUE;
                                        }

                                        // polos
                                        if ($chamada->admitePoloObj()) {
                                            $polos = buscarPoloPorInscricaoCT($inscricao->getIPR_ID_INSCRICAO());
                                            $dsPolo = count($polos) == 1 ? "Polo:" : "Polos:";
                                            ?>
                                            <?php if ($barra) { ?>
                                                <separador class='barra'></separador>
                                            <?php } ?>
                                            <b><?php print $dsPolo; ?></b> <?php print arrayParaStr($polos); ?>
                                            <?php
                                            $barra = TRUE;
                                        }
                                        ?>
                                        </p>
                                    <?php }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full m02">
                    <div class="tabbable"> <!-- Only required for left/right tabs -->
                        <ul class="nav nav-tabs">
                            <?php
                            // gerando abas para etapas
                            for ($i = 0; $i < count($etapas); $i++) {
                                ?>
                                <li class = "<?php (($semRecurso && $i == 0) || (!$semRecurso && $etapaRecurso->getESP_ID_ETAPA_SEL() === $etapas[$i]->getESP_ID_ETAPA_SEL())) ? print "active" : "" ?>"><a href = "#tab2<?php print $i + 1; ?>" data-toggle = "tab"><?php print $etapas[$i]->getNomeEtapa(); ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                        <div class="tab-content col-full">
                            <?php
                            // gerando conteudo para abas
                            for ($i = 0; $i < count($etapas); $i++) {
                                ?>
                                <div class="tab-pane <?php (($semRecurso && $i == 0) || (!$semRecurso && $etapaRecurso->getESP_ID_ETAPA_SEL() === $etapas[$i]->getESP_ID_ETAPA_SEL())) ? print "active" : "" ?>" id="tab2<?php print $i + 1; ?>">
                                    <?php
                                    // etapa finalizada ou em recurso
                                    if ($etapas[$i]->isFinalizada() || $etapas[$i]->isEmRecurso()) {

                                        $criarRec = podehabilitarRecursoCT($inscricao->getIPR_ID_INSCRICAO(), $etapas[$i]->getESP_ID_ETAPA_SEL());

                                        $textoRec = $criarRec[0] ? "title='Protocolizar um recurso contra o resultado do edital'" : "disabled title='$criarRec[1]'";
                                        ?>
                                        <div class="m02" style="margin-bottom:2em;">    
                                            <input <?php print $textoRec ?> class = "btn btn-primary" type = "button" onclick = "javascript: window.location = 'criarRecursoUsu.php?idChamada=<?php print $inscricao->getPCH_ID_CHAMADA(); ?>&idInscricao=<?php print $inscricao->getIPR_ID_INSCRICAO(); ?>'" value = "Protocolizar recurso">
                                        </div>
                                        <?php
                                        $filtro->setIdEtapa($etapas[$i]->getESP_ID_ETAPA_SEL());
                                        $paginacao->atualizaObjFiltro($filtro);
                                        $paginacao->imprimir();
                                        ?>
                                    <?php } else {
                                        ?>
                                        <div class="callout callout-info m02"><?php print EtapaSelProc::getMsgHtmlEtapaFechadaRec(); ?></div>
                                    <?php }
                                    ?>
                                </div>
                            <?php }
                            ?>
                        </div>
                        <input type="button" class="btn btn-default m02" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/inscricaoProcesso/consultarInscProcesso.php?idInscricao=<?php print $inscricao->getIPR_ID_INSCRICAO(); ?>';" value="Voltar">
                    </div>      
                </div>
            </div>  
        </div>  
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        carregaScript("jquery.maskedinput");
        carregaScript("jquery.cookie");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {

            function sucInsercao() {
                $().toastmessage('showToast', {
                    text: '<b>Recurso Protocolado com sucesso.</b>',
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