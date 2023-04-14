<!DOCTYPE html>
<html>
    <head>     
        <title>Visualizar Inscrição - Seleção EAD</title>
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

        //verificando se está logado
        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idInscricao'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados
        $inscricao = buscarInscricaoComPermissaoCT($_GET['idInscricao'], getIdUsuarioLogado());

        //verificando permissão e recuperando dados para processamento
        $processo = buscarProcessoComPermissaoCT($inscricao->getPRC_ID_PROCESSO(), TRUE);
        $chamada = buscarChamadaPorIdCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getPRC_ID_PROCESSO());

        // caso de vir direto de inscrição, porque já estava inscrito
        $veioInscricao = isset($_GET[Mensagem::$TOAST_VAR_GET]) && $_GET[Mensagem::$TOAST_VAR_GET] == "inscricao";

        // se for inscrição, remover dados da sessão
        if ($veioInscricao) {
            sessaoDados_removerDados("idProcessoInscricao");
            sessaoDados_removerDados("dsProcessoInscricao");
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

                <?php
                if ($veioInscricao) {
                    ?>
                    <div class="callout callout-success">
                        <strong>Você já está inscrito</strong> no <strong><?php print $processo->getHTMLDsEditalCompleta(); ?></strong>. Consulte abaixo a sua inscrição.
                    </div>
                <?php }
                ?>

                <div id="breadcrumb">
                    <h1>Você está em: Editais > <a href="listarInscProcessoUsuario.php">Minhas inscrições</a> > <strong>Visualizar</strong></h1>
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

                <div class="col-full">
                    <fieldset>
                        <?php
                        // verificando permissões
                        $tituloImprimir = "Imprimir comprovante de inscrição";

                        // exclusao
                        $podeExcluir = validaPeriodoInscPorChamadaCT($inscricao->getPCH_ID_CHAMADA());
                        $tituloExcluir = $podeExcluir ? "Excluir sua inscrição" : "Não é possível excluir. Período de Inscrição finalizado";

                        // protocolizar recurso
                        $podeProtRecurso = permiteMostrarProtocolizacaoRecursoCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getIPR_ID_INSCRICAO());
                        $tituloProtRecurso = $podeProtRecurso ? "Protocolizar recurso contra o resultado do edital" : "Não é possível protocolizar recurso contra o resultado do edital";

                        // visualizar recurso
                        $podeVisualizarRecurso = permiteMostrarRecursoCT($inscricao->getPCH_ID_CHAMADA());
                        $tituloVisualizarRecurso = $podeVisualizarRecurso ? "Visualizar recurso contra o resultado do edital" : "Não é possível visualizar recursos contra o resultado do edital";
                        ?>
                        <button class="btn btn-default m01 col-half-mob" type="button" onclick="javascript: window.open('<?php print "$CFG->rwww/visao/processo/consultarProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}"; ?>', '_blank')" title="Visualizar o Edital"><i class="fa fa-eye"></i> Visualizar edital</button>
                        <button class="btn btn-default m01 col-half-mob" type="button" onclick="javascript: window.open('<?php print "$CFG->rwww/visao/relatorio/imprimirCompInscricao.php?idInscricao={$inscricao->getIPR_ID_INSCRICAO()}"; ?>', '_blank')" title="<?php print $tituloImprimir; ?>"><i class="fa fa-print"></i> Imprimir <span class='campoDesktop'>comprovante</span><span class='campoMobile'>compr.</span></button>
                        <button <?php $podeExcluir ? print "" : print "disabled" ?> class="btn btn-default m01 col-half-mob" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/inscricaoProcesso/excluirInscProcesso.php?idInscricao={$inscricao->getIPR_ID_INSCRICAO()}"; ?>';" title="<?php print $tituloExcluir; ?>"><i class="fa fa-trash-o"></i> Excluir inscrição</button>
                        <button <?php $podeProtRecurso ? print "" : print "disabled" ?> class="btn btn-default m01 col-half-mob" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/recurso/criarRecursoUsu.php?idChamada={$inscricao->getPCH_ID_CHAMADA()}&idInscricao={$inscricao->getIPR_ID_INSCRICAO()}"; ?>';" title="<?php print $tituloProtRecurso; ?>"><i class="fa fa-bookmark"></i> <span class='campoDesktop'>Protocolizar</span><span class='campoMobile'>Prot.</span> recurso</button>
                        <button <?php $podeVisualizarRecurso ? print "" : print "disabled" ?> class="btn btn-default m01 col-half-mob" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/recurso/listarRecursoCandidato.php?idInscricao={$inscricao->getIPR_ID_INSCRICAO()}"; ?>';" title="<?php print $tituloVisualizarRecurso; ?>"><i class="fa fa-bullhorn"></i> Visualizar recurso</button>
                    </fieldset>
                </div>

                <?php
                include ($CFG->rpasta . "/visao/candidato/fragmentoResultadoCandidato.php");
                RES_USU_MinhasInscricoes($inscricao, $chamada);
                ?>

                <div class="col-full">                     
                    <?php
                    // Recuperando grupos do processo
                    $grupos = buscarGrupoPorProcessoCT($inscricao->getPRC_ID_PROCESSO());
                    if (count($grupos) > 0) {
                        ?>
                        <fieldset class="m02">
                            <h3 class="sublinhado">Informações Complementares</h3>

                            <div id="questoes" class="respondidas">
                                <?php
                                // preenchendo os grupos
                                // ATENÇÃO: AO ALTERAR ESTE BLOCO DE CÓDIGO, É IMPORTANTE REVISAR OS ARQUIVOS ASSOCIADOS:
                                // 1 - consultarGrupoAnexoProc.php
                                // 2 - criarInscProcesso.php
                                // 3 - consultarInscProcesso.php
                                // 4 - imprimirCompInscricao.php
                                // 5 - consultarInscProcessoAdmin.php
                                // 6 - fragmentoAvaliarInfComp.php
                                foreach ($grupos as $grupo) {
                                    // A seguir: Nome da pergunta e descriçao
                                    ?>
                                    <div id="questao" class="form-group m01" style="float:left;width:100%;">
                                        <label class="faixa">
                                            <?php if (!Util::vazioNulo($grupo->getGAP_NM_GRUPO())) { ?>
                                                <h4><?php print $grupo->getGAP_NM_GRUPO() ?></h4>
                                            <?php } ?>
                                        </label>

                                        <div id="resposta">
                                            <?php
                                            if ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
                                                // recuperando resposta do grupo
                                                $resp = buscarRespPorInscricaoGrupoCT($inscricao->getIPR_ID_INSCRICAO(), $grupo->getGAP_ID_GRUPO_PROC());
                                                $strResposta = RespAnexoProc::getDsResposta($resp);
                                                $maxCaracter = RespAnexoProc::getTamanhoResposta($strResposta);
                                                ?>
                                                <p align="justify"><?php print $strResposta; ?></p>

                                                <?php
                                            } elseif ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {

                                                // nesse caso carregar perguntas
                                                $itens = buscarItemPorGrupoCT($grupo->getGAP_ID_GRUPO_PROC());

                                                // gerar id do agrupamento
                                                $idAgrupamento = $grupo->getIdElementoHtml();

                                                // recuperando resposta do grupo, se existir
                                                $resp = buscarRespPorInscricaoGrupoCT($inscricao->getIPR_ID_INSCRICAO(), $grupo->getGAP_ID_GRUPO_PROC());
                                                $arrayResp = $resp != NULL ? $resp->respParaArray() : NULL;
                                                ?>

                                                <?php if ($arrayResp == NULL) { ?>
                                                    <p><?php print htmlspecialchars(RespAnexoProc::getStrSemResposta()); ?></p>
                                                <?php } ?>

                                                <div class="respostaGrupo">
                                                    <?php
                                                    // preenchendo os itens do grupo
                                                    foreach ($itens as $item) {

                                                        // gerando id do item anexo
                                                        $idHtmlItem = $item->getIdElementoHtml();

                                                        // item respondido ou não?
                                                        $itemRespondido = $arrayResp != NULL ? RespAnexoProc::isResposta($arrayResp, $item->getIAP_DS_ITEM()) : FALSE;

                                                        // resposta múltipla? inserir os checkbox na tela
                                                        if ($item->isRespostaMultipla()) {
                                                            // Nesse caso, adicionar checkbox na tela
                                                            ?>
                                                            <div class="respostaItem" <?php $itemRespondido ? print "" : print "style='display:none'"; ?>>
                                                                <i class='fa fa-quote-left'></i> <?php print $item->getIAP_NM_ITEM(); ?> <i class='fa fa-quote-right'></i>
                                                            </div>
                                                            <?php
                                                        } else {
                                                            // inserir radio na tela
                                                            ?>
                                                            <div class="respostaItem" <?php $itemRespondido ? print "" : print "style='display:none'"; ?>>
                                                                <i class='fa fa-quote-left'></i> <?php print $item->getIAP_NM_ITEM(); ?> <i class='fa fa-quote-right'></i>
                                                            </div>
                                                            <?php
                                                        }

                                                        // tratando os complementos
                                                        if ($item->temComplemento()) {
                                                            // nesse caso, carregar os complementos
                                                            $subitens = buscarSubitemPorItemCT($item->getIAP_ID_ITEM());

                                                            // recuperando tipo para montar o complemento adequadamente
                                                            $tipo = SubitemAnexoProc::getTipoSubitens($subitens);

                                                            // recuperando resposta do complemento
                                                            $respostaComp = "";
                                                            if ($itemRespondido) {
                                                                $respCompBD = buscarRespPorInscricaoItemCT($inscricao->getIPR_ID_INSCRICAO(), $item->getIAP_ID_ITEM());
                                                                if (!Util::vazioNulo($respCompBD)) {
                                                                    $respostaComp = RespAnexoProc::getDsResposta($respCompBD, FALSE);
                                                                }
                                                            }

                                                            // criando div para comportar o complemento
                                                            $idDivComplemento = "divCompItem" . $item->getIAP_ID_ITEM();
                                                            ?>
                                                            <div class="respostaSubItem" id="<?php print $idDivComplemento ?>" style="display: <?php $itemRespondido ? print "" : print "none" ?>;">
                                                                <?php
                                                                // caso de ser resposta múltipla do subitem
                                                                if (SubitemAnexoProc::subitemRespostaMultipla($subitens)) {
                                                                    // criando array com resposta do complemento
                                                                    $arrayRespComp = isset($respCompBD) && $respCompBD != NULL ? $respCompBD->respParaArray() : NULL;
                                                                    ?>
                                                                    <?php
                                                                    // percorrendo itens para criar checkbox
                                                                    foreach ($subitens as $subitem) {
                                                                        // criando id do subitem
                                                                        $idHtmlSubitem = $subitem->getIdElementoHtml();

                                                                        // subItem respondido ou não?
                                                                        $subItemRespondido = $arrayRespComp != NULL ? RespAnexoProc::isResposta($arrayRespComp, $subitem->getSAP_DS_SUBITEM()) : FALSE;
                                                                        ?>
                                                                        <div>
                                                                            <span <?php $subItemRespondido ? print "" : print "style='display:none'"; ?>>
                                                                                » <?php print $subitem->getSAP_NM_SUBITEM(); ?>
                                                                            </span>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                } else {
                                                                    // tratando caso de radio
                                                                    if ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_RADIO) {
                                                                        impressaoRadioGenerico($idHtmlItem, $subitens, "getIdNomeSubitem", $respostaComp, TRUE, TRUE);
                                                                    } elseif ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_TEXTO) {
                                                                        ?>
                                                                        <div>
                                                                            <p align="justify">» <?php print $respostaComp; ?></p>
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                            <?php
                                                        } //fim complemento
                                                    } // fim lista de itens 
                                                    ?>
                                                </div>
                                                <?php
                                            } // fim agrupamento pergunta
                                            ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </fieldset>
                    <?php } ?>
                    <div class="m02"> 
                        <input type="button" class="btn btn-default" onclick="javascript: window.location = 'listarInscProcessoUsuario.php';" value="Voltar">
                    </div>
                </div>
            </div>
        </div>
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
</html>