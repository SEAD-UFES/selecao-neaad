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
        require_once ($CFG->rpasta . "/controle/CTCurriculo.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");

        // verificando se esta logado
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idInscricao'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // incluindo classes importantes
        require_once ($CFG->rpasta . "/util/filtro/FiltroPublicacao.php");
        require_once ($CFG->rpasta . "/util/filtro/FiltroPartEvento.php");
        require_once ($CFG->rpasta . "/util/filtro/FiltroAtuacao.php");

        // buscando inscriçao e dados para apresentação
        $inscricao = buscarInscricaoComPermissaoCT($_GET['idInscricao'], getIdUsuarioLogado());
        $processo = buscarProcessoPorIdCT($inscricao->getPRC_ID_PROCESSO());
        $chamada = buscarChamadaPorIdCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getPRC_ID_PROCESSO());
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>

        <?php
        //buscando objeto candidato e fragmento html de ultima atualização
        $candidato = buscarCandidatoPorIdCT($inscricao->getCDT_ID_CANDIDATO());
        require_once ($CFG->rpasta . "/visao/candidato/fragmentoDtAtuCurriculo.php");
        ?>

    </head>

    <body>  
        <?php
        include ($CFG->rpasta . "/include/cabecalho.php");
        ?>
        <div id="main">
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww ?>/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso=<?php print $inscricao->getPRC_ID_PROCESSO(); ?>">Inscrições</a> > <strong>Visualizar</strong></h1>
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
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                    <p>
                                        <i class="fa fa-user"></i>
                                        <b>Candidato:</b> <?php print $inscricao->USR_DS_NOME_CDT; ?> <separador class='barra'></separador> 
                                    <b>Inscrição:</b> <?php print $inscricao->getIPR_NR_ORDEM_INSC(); ?> <separador class='barra'></separador> 
                                    <b>Chamada:</b> <?php print $inscricao->PCH_DS_CHAMADA; ?> <separador class='barra'></separador>
                                    <b>Data:</b> <?php print $inscricao->getIPR_DT_INSCRICAO(); ?>
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
                                                $dsAreaAtuacao = $areaAtu->ARC_NM_SUBAREA_CONH;
                                                ?>
                                                <b>Área:</b> <?php print $dsAreaAtuacao; ?>
                                                <?php
                                                $barra = TRUE;
                                            }

                                            // reserva de vaga
                                            if ($chamada->admiteReservaVagaObj()) {
                                                $dsReservaVaga = getDsReservaVagaInscricaoCT($inscricao->getRVC_ID_RESERVA_CHAMADA());
                                                ?>
                                                <?php if ($barra) { ?>
                                                <separador class='barra'></separador>
                                            <?php } ?>
                                            <b>Vaga:</b> <?php print $dsReservaVaga; ?>
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

                                        // exibindo localizacao valida ou nao
                                        if (!Util::vazioNulo($inscricao->getIPR_LOCALIZACAO_VALIDA())) {
                                            // buscando cidade do candidato
                                            $endRes = buscarEnderecoCandPorIdCandCT($inscricao->getCDT_ID_CANDIDATO(), Endereco::$TIPO_RESIDENCIAL);
                                            $descDomicilo = NGUtil::getDsSimNao($inscricao->getIPR_LOCALIZACAO_VALIDA()) . " ({$endRes->getNomeCidade()})";
                                            ?>
                                            <separador class='barra'></separador>
                                            <b>Domicílio Próximo:</b> <?php print $descDomicilo; ?>
                                            <?php
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
                            <li class="active"><a href="#tab1" data-toggle="tab">Inscrição</a></li>
                            <li><a href="#tab2" data-toggle="tab">Identificação</a></li>
                            <li><a href="#tab3" data-toggle="tab">Endereço/Contato</a></li>
                            <li><a href="#tab4" data-toggle="tab">Formação</a></li>
                            <li><a href="#tab5" data-toggle="tab">Publicação</a></li>
                            <li><a href="#tab6" data-toggle="tab">Part. Evento</a></li>
                            <li><a href="#tab7" data-toggle="tab">Atuação</a></li>
                            <li><a id='idTab8' href="#tab8" data-toggle="tab">Notas</a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="tab1">
<!--                                <script type="text/javascript">
                                    $(document).ready(function () {
                                        $('#popoverId').popover({
                                            html: true,
                                            title: 'por Fulano de Tal e Silva <a class="close" href="#");">&times;</a>',
                                            content: '<div class="msg">Observação: Os motivos não são suficientes para sustentação do benefício.</div>',
                                        });
                                        $('#popoverId').click(function (e) {
                                            e.stopPropagation();
                                        });
                                        $(document).click(function (e) {
                                            if (($('.popover').has(e.target).length == 0) || $(e.target).is('.close')) {
                                                $('#popoverId').popover('hide');
                                            }
                                        });
                                    });
                                </script>-->
                                <?php
                                // Recuperando grupos do processo
                                $grupos = buscarGrupoPorProcessoCT($inscricao->getPRC_ID_PROCESSO());
                                if (count($grupos) > 0) {
                                    ?>
                                    <div class="completo m02">
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
                                                //recuperando resposta do grupo
                                                $resp = buscarRespPorInscricaoGrupoCT($inscricao->getIPR_ID_INSCRICAO(), $grupo->getGAP_ID_GRUPO_PROC());

                                                // A seguir: Nome da pergunta e nota do item, se houver
                                                if (!Util::vazioNulo($grupo->getGAP_NM_GRUPO())) {
                                                    ?>
                                                    <div id="questao" class="form-group m01" style="float:left;width:100%;">
                                                        <label class="faixa">
                                                            <h4><?php print $grupo->getGAP_NM_GRUPO() ?>
            <!--                                                                <i id="popoverId" class="popoverThis fa fa-question-circle"></i>-->
                                                                <?php print RespAnexoProc::getHtmlNota($resp, $grupo); ?>
                                                            </h4>

                                                        </label>

                                                        <div id="resposta">
                                                            <?php
                                                        }
                                                        if ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
                                                            // apenas incluindo resposta
                                                            $strResposta = RespAnexoProc::getDsResposta($resp);
                                                            ?>
                                                            <p align="justify"><?php print $strResposta; ?></p>

                                                            <?php
                                                        } elseif ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {

                                                            // nesse caso carregar perguntas
                                                            $itens = buscarItemPorGrupoCT($grupo->getGAP_ID_GRUPO_PROC());

                                                            // recuperando resposta do grupo, se existir
                                                            $resp = buscarRespPorInscricaoGrupoCT($inscricao->getIPR_ID_INSCRICAO(), $grupo->getGAP_ID_GRUPO_PROC());
                                                            $arrayResp = $resp != NULL ? $resp->respParaArray() : $arrayResp = NULL;

                                                            // caso de nao existir resposta
                                                            if ($resp == NULL) {
                                                                ?>
                                                                <span><?php print RespAnexoProc::getDsResposta($resp); ?></span>
                                                                <?php
                                                            } else {
                                                                // percorrendo os itens do grupo
                                                                foreach ($itens as $item) {
                                                                    // verificando se o item esta respondido
                                                                    $itemRespondido = $arrayResp != NULL ? RespAnexoProc::isResposta($arrayResp, $item->getIAP_DS_ITEM()) : FALSE;

                                                                    // caso de item respondido
                                                                    if ($itemRespondido) {
                                                                        $respostaTela = "<i class='fa fa-quote-left'></i> {$item->getIAP_NM_ITEM()}";

                                                                        // tratando complementos
                                                                        if ($item->temComplemento()) {

                                                                            // recuperando resposta do complemento
                                                                            $respostaComp = "";
                                                                            $respCompBD = buscarRespPorInscricaoItemCT($inscricao->getIPR_ID_INSCRICAO(), $item->getIAP_ID_ITEM());
                                                                            if (!Util::vazioNulo($respCompBD)) {
                                                                                $respostaComp = RespAnexoProc::getDsResposta($respCompBD, FALSE);
                                                                            }

                                                                            // tem resposta? incluir os dados
                                                                            if (!Util::vazioNulo($respostaComp)) {

                                                                                // nesse caso, carregar os complementos
                                                                                $subitens = buscarSubitemPorItemCT($item->getIAP_ID_ITEM());

                                                                                // recuperando tipo para montar o complemento adequadamente
                                                                                $tipo = SubitemAnexoProc::getTipoSubitens($subitens);

                                                                                // multipla escolha? 
                                                                                if (SubitemAnexoProc::subitemRespostaMultipla($subitens)) {
                                                                                    // criando array e inicial da resposta
                                                                                    $arrayRespComp = $respCompBD != NULL ? $respCompBD->respParaArray() : NULL;
                                                                                    $respostaTela .= ": ";

                                                                                    // percorrendo respostas
                                                                                    $temp = "";
                                                                                    foreach ($arrayRespComp as $opcaoResp) {
                                                                                        $temp = adicionaConteudoVirgula($temp, SubitemAnexoProc::getSubitemPorDescricao($opcaoResp, $subitens)->getSAP_NM_SUBITEM());
                                                                                    }
                                                                                    $respostaTela .= $temp;
                                                                                } elseif ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_RADIO) {
                                                                                    $respostaTela .= ": " . SubitemAnexoProc::getSubitemPorDescricao($respostaComp, $subitens)->getSAP_NM_SUBITEM();
                                                                                } elseif ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_TEXTO) {
                                                                                    $respostaTela .= ": $respostaComp";
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <div><?php print $respostaTela; ?> <i class='fa fa-quote-right'></i></div>

                                                                        <?php
                                                                        //
                                                                    } // fim item respondido
                                                                }
                                                            }// fim else com resposta 
                                                        } // fim else tipo pergunta
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php }
                                ?>

                                <div class="completo m02">
                                    <h3 class="sublinhado">Avaliação da Inscrição</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">Nota Final:</td>
                                                <td class="campo80">
                                                    <?php print $inscricao->getNotaFormatadaHtml(); ?>
                                                    <?php if ($inscricao->isAvaliada()) { ?>
                                                        <a title="Visualizar relatório de notas" onclick="javascript: $('#idTab8').click();">Detalhar</a>
                                                    <?php } ?> 
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Avaliação em:</td>
                                                <td class="campo80"><?php print $inscricao->getDtAvaliacaoHtml(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Avaliador:</td>
                                                <td class="campo80"><?php print $inscricao->getDadosAvaliadorHtml(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Classificação:</td>
                                                <td class="campo80"><?php print $inscricao->getClassificacaoHtml(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Selecionado:</td>
                                                <td class="campo80"><?php print $inscricao->getSelecionadoHtml(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Grupo:</td>
                                                <td class="campo80"><?php print $inscricao->getHtmlGrupo($chamada, isset($dsAreaAtuacao) ? $dsAreaAtuacao : NULL, isset($dsReservaVaga) ? $dsReservaVaga : NULL); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Observações:</td>
                                                <td class="campo80"><?php print $inscricao->getObservacoesHtml(); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>     
                            </div>
                            <?php
                            // recuperando identificaçao do usuario
                            $objIdent = buscarIdentCandPorIdCandCT($inscricao->getCDT_ID_CANDIDATO());
                            ?>
                            <div class="tab-pane" id="tab2">
                                <div class="m02">
                                    <h3 class="sublinhado">Dados</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">CPF:</td>
                                                <td class="campo80"><?php print $objIdent->getNrCPFMascarado(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Nacionalidade:</td>
                                                <td class="campo80">
                                                    <?php
                                                    if (!Util::vazioNulo($objIdent->getIDC_NM_NACIONALIDADE())) {
                                                        print DS_SELECT_OUTRA . ": " . $objIdent->getIDC_NM_NACIONALIDADE();
                                                    } else {
                                                        $nac = buscarNacionalidadePorIdCT($objIdent->getNAC_ID_NACIONALIDADE());
                                                        print!Util::vazioNulo($nac) ? $nac->getNAC_NM_NACIONALIDADE() : "";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Sexo:</td>
                                                <td class="campo80"><?php print $objIdent->getDsSexo($objIdent->getIDC_DS_SEXO()); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Etnia:</td>
                                                <td class="campo80"><?php print $objIdent->getDsRaca($objIdent->getIDC_TP_RACA()); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Estado Civil:</td>
                                                <td class="campo80"><?php print $objIdent->getDsEstCivil($objIdent->getIDC_TP_ESTADO_CIVIL()); ?></td>
                                            </tr>
                                            <?php if ($objIdent->getIDC_TP_ESTADO_CIVIL() == IdentificacaoCandidato::$EST_CIVIL_CASADO || $objIdent->getIDC_TP_ESTADO_CIVIL() == IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL) { ?>
                                                <tr>
                                                    <td class="campo20">Nome do cônjuge:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_NM_CONJUGE(); ?></td>
                                                </tr>
                                            <?php } ?> 
                                            <tr>
                                                <td class="campo20">Nome do pai:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_FIL_NM_PAI(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Nome da mãe:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_FIL_NM_MAE(); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="m02">
                                    <h3 class="sublinhado">Nascimento</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">País:</td>
                                                <td class="campo80">
                                                    <?php
                                                    $pais = buscarPaisPorIdCT($objIdent->getIDC_NASC_PAIS());
                                                    print!Util::vazioNulo($pais) ? $pais->getPAI_NM_PAIS() : "";
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php if ($objIdent->getIDC_NASC_PAIS() == Pais::$PAIS_BRASIL) { ?>
                                                <tr>
                                                    <td class="campo20">Estado:</td>
                                                    <td class="campo80">
                                                        <?php
                                                        $estado = buscarEstadoPorIdCT($objIdent->getIDC_NASC_ESTADO());
                                                        print $estado->getEST_NM_ESTADO();
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Cidade:</td>
                                                    <td class="campo80">
                                                        <?php
                                                        $cidade = buscarCidadePorIdCT($objIdent->getIDC_NASC_CIDADE());
                                                        if (!Util::vazioNulo($cidade)) {
                                                            print $cidade->getCID_NM_CIDADE();
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td class="campo20">Data:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_NASC_DATA(); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="m02">
                                    <h3 class="sublinhado">RG (Identidade)</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">Número:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_RG_NUMERO(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Órgão emissor:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_RG_ORGAO_EXP(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Unidade Federativa:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_RG_UF(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Data de emissão:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_RG_DT_EMISSAO(); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="m02">
                                    <h3 class="sublinhado">Ocupação Principal</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">Ocupação:</td>
                                                <td class="campo80">
                                                    <?php
                                                    if (!Util::vazioNulo($objIdent->getIDC_NM_OCUPACAO())) {
                                                        print DS_SELECT_OUTRA . ": " . $objIdent->getIDC_NM_OCUPACAO();
                                                    } else {
                                                        $ocp = buscarOcupacaoPorIdCT($objIdent->getOCP_ID_OCUPACAO());
                                                        print!Util::vazioNulo($ocp) ? $ocp->getOCP_NM_OCUPACAO() : "";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Vínculo Público:</td>
                                                <td class="campo80"><?php print NGUtil::getDsSimNao($objIdent->getIDC_VINCULO_PUBLICO()); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="m02">
                                    <?php
                                    $mostrarFunc = !Util::vazioNulo($objIdent->getIDC_UFES_SIAPE()) || !Util::vazioNulo($objIdent->getIDC_UFES_SETOR()) || !Util::vazioNulo($objIdent->getIDC_UFES_LOTACAO());
                                    if ($mostrarFunc) {
                                        ?>
                                        <h3 class="sublinhado">Dados Funcionais (Apenas para Servidores da UFES)</h3> 
                                        <div class="col-full">
                                            <table class="mobileBorda table-bordered table" style="width:100%;">
                                                <tr>
                                                    <td class="campo20">SIAPE:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_UFES_SIAPE(); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Lotação:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_UFES_LOTACAO(); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Setor:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_UFES_SETOR(); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="m02">
                                    <h3 class="sublinhado">Passaporte</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">Número:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_PSP_NUMERO(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Data de emissão:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_PSP_DT_EMISSAO(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Data de validade:</td>
                                                <td class="campo80"><?php print $objIdent->getIDC_PSP_DT_VALIDADE(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">País emissor:</td>
                                                <td class="campo80">
                                                    <?php
                                                    if (!Util::vazioNulo($objIdent->getIDC_PSP_PAIS_ORIGEM())) {
                                                        $pai = buscarPaisPorIdCT($objIdent->getIDC_PSP_PAIS_ORIGEM());
                                                        print $pai->getPAI_NM_PAIS();
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab3">
                                <?php
                                // buscando endereços
                                $endRes = buscarEnderecoCandPorIdCandCT($inscricao->getCDT_ID_CANDIDATO(), Endereco::$TIPO_RESIDENCIAL);
                                $endCom = buscarEnderecoCandPorIdCandCT($inscricao->getCDT_ID_CANDIDATO(), Endereco::$TIPO_COMERCIAL);

                                // buscando contato
                                $contCand = buscarContatoCandPorIdCandCT($inscricao->getCDT_ID_CANDIDATO());
                                ?>
                                <div class="completo m02">
                                    <h3 class="sublinhado">Endereço Residencial</h3>
                                    <div class="col-full">
                                        <?php print $endRes->getStrEndereco(); ?>
                                    </div>
                                </div>

                                <div class="completo m02">
                                    <h3 class="sublinhado">Endereço Comercial</h3>
                                    <div class="col-full">
                                        <?php print $endCom->getStrEndereco(); ?>
                                    </div>
                                </div>

                                <div class="completo m02">
                                    <h3 class="sublinhado">Contato</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">Telefone residencial:</td>
                                                <td class="campo80"><?php print ContatoCandidato::getTelFaxMascarado($contCand->getCTC_NR_TEL_RES()); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Telefone comercial:</td>
                                                <td class="campo80"><?php print ContatoCandidato::getTelFaxMascarado($contCand->getCTC_NR_TEL_COM()); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Celular:</td>
                                                <td class="campo80"><?php print $contCand->getNrCelularMascarado(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Fax:</td>
                                                <td class="campo80"><?php print ContatoCandidato::getTelFaxMascarado($contCand->getCTC_NR_FAX()) ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Email:</td>
                                                <td class="campo80"><?php print $inscricao->USR_DS_EMAIL_CDT; ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Email alternativo:</td>
                                                <td class="campo80"><?php print $contCand->getCTC_EMAIL_CONTATO(TRUE); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab4">
                                <?php
                                // buscando lattes
                                $lattes = buscarLinkLattesPorIdCandCT($inscricao->getCDT_ID_CANDIDATO());
                                ?>

                                <?php DAC_geraHtml($candidato, "m01"); ?>

                                <div class="m01">
                                    <b>Currículo Lattes:</b>
                                    <a id="linkLattes" target="_blank" <?php !$lattes['val'] ? print "title='URL do link Lattes não informada' onclick='javascript: return false;'" : print "href='" . $lattes['link'] . "'"; ?>><?php print $lattes['link']; ?></a>
                                </div>
                                <?php
                                // buscando formaçoes
                                $listaFormacao = buscarFormacaoPorIdCandCT($inscricao->getCDT_ID_CANDIDATO(), NULL, NULL);
                                if (!Util::vazioNulo($listaFormacao)) {
                                    foreach ($listaFormacao as $formacao) {
                                        ?>                                                                            
                                        <div class="m02">
                                            <h3 class="sublinhado"><?php print "{$formacao->getDsPeriodo()} - {$formacao->TPC_NM_TIPO_CURSO}" ?></h3>
                                            <div class="col-full">
                                                <table class="mobileBorda table-bordered table" style="width:100%;">
                                                    <tr>
                                                        <td class="campo20">Instituição:</td>
                                                        <td class="campo80"><?php print $formacao->getDsInstituicaoComp(); ?></td>
                                                    </tr>
                                                    <?php if (!Util::vazioNulo($formacao->getFRA_NM_CURSO())) { ?>
                                                        <tr>
                                                            <td class="campo20">Curso:</td>
                                                            <td class="campo80"><?php print $formacao->getFRA_NM_CURSO(); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if (!Util::vazioNulo($formacao->getFRA_ID_AREA_CONH())) { ?>
                                                        <tr>
                                                            <td class="campo20">Área:</td>
                                                            <td class="campo80"><?php print $formacao->getDsAreaSubarea(); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if (!Util::vazioNulo($formacao->getFRA_CARGA_HORARIA())) { ?>
                                                        <tr>
                                                            <td class="campo20">Carga horária (hs):</td>
                                                            <td class="campo80"><?php print $formacao->getFRA_CARGA_HORARIA(); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if (TipoCurso::isIdAdmiteDetalhamento($formacao->getTPC_ID_TIPO_CURSO())) { ?>
                                                        <tr>
                                                            <td class="campo20">Trabalho:</td>
                                                            <td class="campo80"><?php print $formacao->getFRA_TITULO_TRABALHO(); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="campo20">Orientador:</td>
                                                            <td class="campo80"><?php print $formacao->getFRA_ORIENTADOR_TRABALHO(); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </table>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php
                                } else {
                                    ?>
                                    <div class="callout callout-warning">
                                        Usuário não cadastrou formação/titulação.
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>

                            <div class="tab-pane" id="tab5">

                                <?php DAC_geraHtml($candidato, "m01"); ?>

                                <div class="m02">
                                    <?php
                                    // criando filtro para obtençao de dados
                                    // NOTE: Aqui o idUsuario = idCandidato. Portanto, NAO USE ESTE FILTRO PARA OUTROS FINS
                                    $filtro = new FiltroPublicacao(array(), NULL, $inscricao->getCDT_ID_CANDIDATO(), "", FALSE);
                                    $filtro->setInicioDados(NULL);
                                    $filtro->setQtdeDados(NULL);

                                    // imprimindo tabela
                                    print tabelaPublicacaoCandPorFiltroCT($filtro, FALSE, 'buscarPublicacaoPorIdCandCT');
                                    ?>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab6">

                                <?php DAC_geraHtml($candidato, "m01"); ?>

                                <div class="m02">
                                    <?php
                                    // criando filtro para obtençao de dados
                                    // NOTE: Aqui o idUsuario = idCandidato. Portanto, NAO USE ESTE FILTRO PARA OUTROS FINS
                                    $filtro = new FiltroPartEvento(array(), NULL, $inscricao->getCDT_ID_CANDIDATO(), "", FALSE);
                                    $filtro->setInicioDados(NULL);
                                    $filtro->setQtdeDados(NULL);

                                    // imprimindo tabela
                                    print tabelaPartEventoCandPorFiltroCT($filtro, FALSE, 'buscarPartEventoPorIdCandCT');
                                    ?>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab7">

                                <?php DAC_geraHtml($candidato, "m01"); ?>

                                <div class="m02">
                                    <?php
                                    // criando filtro para obtençao de dados
                                    // NOTE: Aqui o idUsuario = idCandidato. Portanto, NAO USE ESTE FILTRO PARA OUTROS FINS
                                    $filtro = new FiltroAtuacao(array(), NULL, $inscricao->getCDT_ID_CANDIDATO(), "", FALSE);
                                    $filtro->setInicioDados(NULL);
                                    $filtro->setQtdeDados(NULL);

                                    // imprimindo tabela
                                    print tabelaAtuacaoCandPorFiltroCT($filtro, FALSE, 'buscarAtuacaoPorIdCandCT');
                                    ?>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab8">
                                <?php
                                // a inscrição está avaliada?
                                if ($inscricao->isAvaliada()) {


                                    // recuperando dados para montar relatorio.
                                    // recuperando etapas
                                    $etapas = buscarEtapaPorChamadaCT($inscricao->getPCH_ID_CHAMADA());
                                    // recuperando etapa Ativa
                                    $etapaAtiva = buscarEtapaEmAndamentoCT($inscricao->getPCH_ID_CHAMADA());

                                    if (Util::vazioNulo($etapas)) {
                                        ?>
                                        <div class="callout callout-warning">
                                            Não existem etapas de avaliação cadastradas.
                                        </div>
                                    <?php } else {
                                        ?>
                                        <div class="tabbable"> 
                                            <div class="col-md-2">
                                                <ul class="nav nav-pills tabs-left m02">
                                                    <?php
                                                    // gerando abas para etapas
                                                    for ($i = 0; $i < count($etapas); $i++) {
                                                        $ativarEtapa = ($etapaAtiva != NULL && $etapaAtiva->getESP_ID_ETAPA_SEL() === $etapas[$i]->getESP_ID_ETAPA_SEL()) || ($etapaAtiva == NULL && $i == 0);
                                                        ?>
                                                        <li class="<?php $ativarEtapa ? print "active" : "" ?>"><a href="#tab2<?php print $i + 1; ?>" data-toggle="tab"><?php print $etapas[$i]->getNomeEtapa(); ?></a></li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </div>

                                            <div class="col-md-10">
                                                <div class="tab-content">
                                                    <?php
                                                    // gerando conteudo para abas
                                                    $mostrarConteudo = true;
                                                    for ($i = 0; $i < count($etapas); $i++) {
                                                        $ativarEtapa = ($etapaAtiva != NULL && $etapaAtiva->getESP_ID_ETAPA_SEL() === $etapas[$i]->getESP_ID_ETAPA_SEL()) || ($etapaAtiva == NULL && $i == 0);
                                                        ?>
                                                        <div class="tab-pane <?php $ativarEtapa ? print "active" : "" ?>" id="tab2<?php print $i + 1; ?>">
                                                            <?php
                                                            // etapas ativas ou ja fechadas
                                                            if ($mostrarConteudo) {
                                                                // recuperando categorias da etapa
                                                                $categorias = buscarCatAvalPorProcEtapaTpCT($inscricao->getPRC_ID_PROCESSO(), $etapas[$i]->getESP_NR_ETAPA_SEL(), NULL, FALSE);

                                                                // compatibilidade com versões anteriores
                                                                if (Util::vazioNulo($categorias)) {
                                                                    ?>
                                                                    <div class="m02 callout callout-warning">
                                                                        <?php print EtapaSelProc::getMsgHtmlEtapaIncompleta(); ?>
                                                                    </div>
                                                                    <?php
                                                                    continue;
                                                                }

                                                                // loop nas categorias
                                                                foreach ($categorias as $categoria) {
                                                                    // criando html das categorias
                                                                    ?>
                                                                    <div class="completo m02">
                                                                        <h3 class="sublinhado"><?php print $categoria->getHmlNomeCategoria(); ?></h3>

                                                                        <?php
                                                                        // imprimindo tabela
                                                                        print tabelaRelatorioNotas($inscricao, $categoria);
                                                                        ?>
                                                                    </div>
                                                                    <?php
                                                                }

                                                                // imprimindo nota da etapa
                                                                print imprimirNotaEtapa($inscricao, $etapas[$i]);
                                                            } else {
                                                                ?>
                                                                <div class="m02 callout callout-warning">
                                                                    <?php print EtapaSelProc::getMsgHtmlEtapaFechadaNota(); ?>
                                                                </div>
                                                            <?php }
                                                            ?>

                                                            <?php
                                                            // mecanismo de bloqueio de conteudo
                                                            if ($etapaAtiva != NULL && $etapaAtiva->getESP_ID_ETAPA_SEL() == $etapas[$i]->getESP_ID_ETAPA_SEL()) {
                                                                $mostrarConteudo = false;
                                                            }
                                                            ?>
                                                        </div>
                                                    <?php }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>  
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <div class="callout callout-warning">
                                        Inscrição não avaliada.
                                    </div>
                                <?php }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="completo m02">
                    <input type="button" class="btn btn-default" onclick="javascript: window.location = 'listarInscricaoProcesso.php?idProcesso=<?php print $inscricao->getPRC_ID_PROCESSO(); ?>';" value="Voltar">
                </div>
            </div>
        </div>  
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaCSS("bootstrap.vertical-tabs");
        ?>
    </body>
</html>