<?php

// carregando itens imprescindiveis
require_once '../../config.php';
global $CFG;
?>

<?php

require_once ($CFG->rpasta . "/util/sessao.php");
//verificando se está logado: Restringindo acesso para avaliador
if (estaLogado() == NULL || estaLogado(Usuario::$USUARIO_AVALIADOR) != NULL) {
//redirecionando para tela de login
    header("Location: $CFG->rwww/acesso");
    return;
}

//verificando passagem por get
if (!isset($_GET['idInscricao'])) {
    new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
    return;
}

// buscando dados do relatorio
require_once ($CFG->rpasta . "/controle/CTProcesso.php");
require_once ($CFG->rpasta . "/controle/CTUsuario.php");
require_once ($CFG->rpasta . "/controle/CTCurriculo.php");
$inscricao = buscarInscricaoComPermissaoCT($_GET['idInscricao'], getIdUsuarioLogado());
$identCan = buscarIdentCandPorIdCandCT($inscricao->getCDT_ID_CANDIDATO());
$usu = buscarUsuarioPorIdCandCT($inscricao->getCDT_ID_CANDIDATO());
$contato = buscarContatoCandPorIdUsuarioCT($usu->getUSR_ID_USUARIO());
$end = buscarEnderecoCandPorIdUsuarioCT($usu->getUSR_ID_USUARIO(), Endereco::$TIPO_RESIDENCIAL);
$polos = buscarPoloPorInscricaoCT($inscricao->getIPR_ID_INSCRICAO());
$listaFormacao = buscarFormacaoPorIdUsuarioCT($usu->getUSR_ID_USUARIO(), NULL, NULL);
$grupos = buscarGrupoPorProcessoCT($inscricao->getPRC_ID_PROCESSO());
$listaPublicacao = buscarPublicacaoPorIdUsuarioCT($usu->getUSR_ID_USUARIO(), NULL, NULL);
$listaParticipacao = buscarPartEventoPorIdUsuarioCT($usu->getUSR_ID_USUARIO(), NULL, NULL);
$listaAtuacao = buscarAtuacaoPorIdUsuarioCT($usu->getUSR_ID_USUARIO(), NULL, NULL);
$linkLattes = buscarLinkLattesPorIdCandCT($inscricao->getCDT_ID_CANDIDATO(), FALSE);
$processo = buscarProcessoPorIdCT($inscricao->getPRC_ID_PROCESSO());
$chamada = buscarChamadaPorIdCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getPRC_ID_PROCESSO());

// carregando classes do PDF
require_once ($CFG->rpasta . "/include/PDF/fpdf.php");
require_once ($CFG->rpasta . "/include/PDF/PDFNeaad.php");

// criando PDF
$pdf = new PDFNeaad($processo->getDsEditalParaPDF(), $chamada->getPCH_DS_CHAMADA(TRUE), "Comprovante de Inscrição");

// imprimindo dados do processo / inscriçao
$arrayTxtEsq = array("Edital:", "Atribuição:", "Curso:", "Formação:");
$arrayTxtDir = array("Nº de Inscrição:", "Data:", "Chamada:");

$tamBlocoEsq = $pdf->maiorStrArray($arrayTxtEsq, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);
$tamBlocoDir = $pdf->maiorStrArray($arrayTxtDir, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);

$pdf->InicializaBloco("Dados da Inscrição");
$pdf->ItemBloco($arrayTxtEsq[0], $inscricao->PRC_NR_ANO_EDITAL, "L", $tamBlocoEsq);
$pdf->ItemBloco($arrayTxtDir[0], $inscricao->getIPR_NR_ORDEM_INSC(), "R", $tamBlocoDir);
$pdf->ItemBloco($arrayTxtEsq[1], $inscricao->TIC_NM_TIPO_CARGO, "L", $tamBlocoEsq);
$pdf->ItemBloco($arrayTxtDir[1], $inscricao->getIPR_DT_INSCRICAO(), "R", $tamBlocoDir);
$pdf->ItemBloco($arrayTxtEsq[2], $inscricao->CUR_NM_CURSO, "L", $tamBlocoEsq);
$pdf->ItemBloco($arrayTxtDir[2], $inscricao->PCH_DS_CHAMADA, "R", $tamBlocoDir);
$pdf->ItemBloco($arrayTxtEsq[3], $inscricao->TPC_NM_TIPO_CURSO, "L", $tamBlocoEsq);

// incluindo area de atuacao
$incluiArea = $chamada->admiteAreaAtuacaoObj();
if ($incluiArea) {
    // recuperando area de atuacao
    $areaAtu = buscarAreaAtuChamadaPorIdCT($inscricao->getAAC_ID_AREA_CHAMADA());
    $pdf->ItemBloco("Área de Atuação:", $areaAtu->ARC_NM_SUBAREA_CONH, "R", $tamBlocoDir);
}

// incluindo reserva de vaga
$incluiReservaVaga = $chamada->admiteReservaVagaObj();
if ($incluiReservaVaga) {
    $pdf->ItemBloco("Vaga:", getDsReservaVagaInscricaoCT($inscricao->getRVC_ID_RESERVA_CHAMADA()), $incluiArea ? "L" : "R", $incluiArea ? $tamBlocoEsq : $tamBlocoDir);
}

// determina se o próximo dado será escrito em nova linha: Ou exclusivo
$novaLinha = ($incluiArea xor $incluiReservaVaga);

// incluindo polo
if (!Util::vazioNulo($polos)) {
    if (count($polos) == 1) {
        $pdf->ItemBloco("Polo:", arrayParaStr($polos), $novaLinha ? "L" : "R", $novaLinha ? $tamBlocoEsq : $tamBlocoDir);
        if ($novaLinha) {
            $pdf->Ln();
        }
    } else {
        if (!$novaLinha) {
            $pdf->ItemBloco("", "", "R", $tamBlocoDir);
        }
        $ds = "Polos em ordem de prioridade:";
        $pdf->ItemBlocoUnico($ds, arrayParaStr($polos), $pdf->GetStringWidth($ds) + 5);
    }
} else {
    if (!$novaLinha) {
        $pdf->ItemBloco("", "", "R", $tamBlocoDir);
    }
}

$pdf->FinalizaBloco();

// imprimindo dados do candidato
$arrayTxtEsq = array("Nome:", "Sexo:", "Nacionalidade:", "Naturalidade:", "Nascimento:", "Etnia:", "Estado Civil:", "Endereço:");
$arrayTxtDir = array("CPF:", "RG:", "Celular:", "Email:");

$tamBlocoEsq = $pdf->maiorStrArray($arrayTxtEsq, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);
$tamBlocoDir = $pdf->maiorStrArray($arrayTxtDir, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);

$pdf->InicializaBloco("Dados do Candidato", FALSE);
$pdf->ItemBloco($arrayTxtEsq[0], $usu->getUSR_DS_NOME(), "L", $tamBlocoEsq);
$pdf->ItemBloco($arrayTxtDir[0], $identCan->getNrCPFMascarado(), "R", $tamBlocoDir);
$pdf->ItemBloco($arrayTxtEsq[1], IdentificacaoCandidato::getDsSexo($identCan->getIDC_DS_SEXO()), "L", $tamBlocoEsq);
$pdf->ItemBloco($arrayTxtDir[1], $identCan->getIDC_RG_NUMERO() . " - " . $identCan->getIDC_RG_ORGAO_EXP() . " - " . $identCan->getIDC_RG_UF(), "R", $tamBlocoDir);
$pdf->ItemBloco($arrayTxtEsq[2], $identCan->getNacionalidade(), "L", $tamBlocoEsq);
$pdf->ItemBloco($arrayTxtDir[2], $contato->getNrCelularMascarado(), "R", $tamBlocoDir);
$pdf->ItemBloco($arrayTxtEsq[3], $identCan->getNaturalidade(), "L", $tamBlocoEsq);
$pdf->ItemBloco($arrayTxtDir[3], $usu->getUSR_DS_EMAIL(), "R", $tamBlocoDir);

$pdf->ItemBlocoUnico($arrayTxtEsq[4], $identCan->getIDC_NASC_DATA(), $tamBlocoEsq);
$pdf->ItemBlocoUnico($arrayTxtEsq[5], $identCan->getDsRaca($identCan->getIDC_TP_RACA()), $tamBlocoEsq);
$pdf->ItemBlocoUnico($arrayTxtEsq[6], IdentificacaoCandidato::getDsEstCivil($identCan->getIDC_TP_ESTADO_CIVIL()), $tamBlocoEsq);

$pdf->ItemBlocoUnico($arrayTxtEsq[7], $end, $tamBlocoEsq);
$pdf->FinalizaBloco();


// Imprimindo informaçoes complementares
if (count($grupos) > 0) {
    $pdf->InicializaBloco("Informações Complementares", FALSE);
    imprimeInfoComp($pdf, $grupos, $inscricao);
    $pdf->FinalizaBloco();
}

// verificando necessidade de incluir aviso de possível alteração de dados
if ($chamada->isFinalizada()) {
    $pdf->SetY($pdf->getY() + 4);
    $pdf->SetFont('', 'B', 9);
    $pdf->Cell(15, 5, "Atenção:");
    $msg = "Como o período de avaliação desta chamada já foi finalizado, as informações abaixo podem não corresponder ao que foi efetivamente processado no ato da avaliação, pois refletem os dados atuais.";
    $pdf->SetFont('', '', 9);
    $pdf->MultiCell(0, 5, $msg);
    $pdf->SetY($pdf->getY() + 4);
}

// Imprimindo dados da formaçao
$tam = count($listaFormacao);
$i = 0;

$arrayTxtEsq = array("Período / Formação:", "Instituição:", "Curso:", "Área:", "Carga horária (hs):");

$tamBlocoEsq = $pdf->maiorStrArray($arrayTxtEsq, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);

$pdf->InicializaBloco("Formação", $chamada->isFinalizada());

// incluindo link Lattes
//@todo Implementar colocaçao do link lattes como link
$pdf->ItemBlocoUnico("Currículo Lattes:", $linkLattes['link'], 25);
$pdf->Ln();

if (!Util::vazioNulo($listaFormacao)) {
    foreach ($listaFormacao as $formacao) {
        $i++;
        imprimeFormacao($pdf, $formacao, $arrayTxtEsq, $tamBlocoEsq);
        if ($i < $tam) {
            $pdf->Ln(3);
            $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), '', 5);
            $pdf->Cell(0, 0.5, "______________________________________________________________________________________________________________________________________________________________________________________", 0, 0, 'C');
            $pdf->Ln(5);
        }
    }
} else {
    $pdf->Ln(1);
    $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), NULL, PDFNeaad::$FONTE_ITEM_BLOCO[1]);
    $pdf->Cell(0, 4, "Usuário não cadastrou formação / titulação.", 0, 1, 'L');
}
$pdf->FinalizaBloco();

// Imprimindo dados de publicacao
$tam = count($listaPublicacao);
$i = 0;

$arrayCabecalho = array("Item", "Área", "N° de Publicações");

$pdf->InicializaBloco("Publicação", FALSE);
if (!Util::vazioNulo($listaPublicacao)) {
    // definindo tamanho das colunas
    $tamMaxLinha = $pdf->getTamMaxLinhaBlocoTab(count($arrayCabecalho));
    $t0 = 70;
    $t2 = $pdf->maiorStrArray(array($arrayCabecalho[2]), PDFNeaad::$FONTE_VALOR_BLOCO[0], PDFNeaad::$FONTE_VALOR_BLOCO[1]) - count($arrayCabecalho) - 1;
    $vetTamCols = array($t0, ($tamMaxLinha - ($t0 + $t2)), $t2);

    // imprimindo cabecalho da tabela
    $vetTamCols = $pdf->CabecalhoBlocoTabela($arrayCabecalho, $vetTamCols);
    //print_r($vetTamCols);
    foreach ($listaPublicacao as $publicacao) {
        $i++;

        $dsItem = Publicacao::getDsTipo($publicacao->getPUB_TP_ITEM());
        $dsAreaSubarea = $publicacao->getDsAreaSubarea();
        $qt = $publicacao->getPUB_QT_ITEM();

        $pdf->LinhaBlocoTabela(array($dsItem, $dsAreaSubarea, $qt), $vetTamCols, array("L", "L", "R"));
    }
} else {
    $pdf->Ln(1);
    $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), NULL, PDFNeaad::$FONTE_ITEM_BLOCO[1]);
    $pdf->Cell(0, 4, "Usuário não cadastrou publicação.", 0, 1, 'L');
}
$pdf->FinalizaBloco();

// Imprimindo dados de participacao em evento
$tam = count($listaParticipacao);
$i = 0;

$arrayCabecalho = array("Item", "Área", "Quantidade");

$pdf->InicializaBloco("Participação em Evento", FALSE);
if (!Util::vazioNulo($listaParticipacao)) {
    // definindo tamanho das colunas
    $tamMaxLinha = $pdf->getTamMaxLinhaBlocoTab(count($arrayCabecalho));
    $t0 = 70;
    $t2 = $pdf->maiorStrArray(array($arrayCabecalho[2]), PDFNeaad::$FONTE_VALOR_BLOCO[0], PDFNeaad::$FONTE_VALOR_BLOCO[1]) + 1;
    $vetTamCols = array($t0, ($tamMaxLinha - ($t0 + $t2)), $t2);

    // imprimindo cabecalho da tabela
    $vetTamCols = $pdf->CabecalhoBlocoTabela($arrayCabecalho, $vetTamCols);
    //print_r($vetTamCols);
    foreach ($listaParticipacao as $partEvento) {
        $i++;

        $dsItem = ParticipacaoEvento::getDsTipo($partEvento->getPEV_TP_ITEM());
        $dsAreaSubarea = $partEvento->getDsAreaSubarea();
        $qt = $partEvento->getPEV_QT_ITEM();

        $pdf->LinhaBlocoTabela(array($dsItem, $dsAreaSubarea, $qt), $vetTamCols, array("L", "L", "R"));
    }
} else {
    $pdf->Ln(1);
    $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), NULL, PDFNeaad::$FONTE_ITEM_BLOCO[1]);
    $pdf->Cell(0, 4, "Usuário não cadastrou participação em evento.", 0, 1, 'L');
}
$pdf->FinalizaBloco();


// Imprimindo dados de atuacao
$tam = count($listaAtuacao);
$i = 0;

$arrayCabecalho = array("Item", "Área", "Quantidade");

$pdf->InicializaBloco("Atuação", FALSE);
if (!Util::vazioNulo($listaAtuacao)) {
    // definindo tamanho das colunas
    $tamMaxLinha = $pdf->getTamMaxLinhaBlocoTab(count($arrayCabecalho));
    $t0 = 70;
    $t2 = $pdf->maiorStrArray(array($arrayCabecalho[2]), PDFNeaad::$FONTE_VALOR_BLOCO[0], PDFNeaad::$FONTE_VALOR_BLOCO[1]) + 1;
    $vetTamCols = array($t0, ($tamMaxLinha - ($t0 + $t2)), $t2);

    // imprimindo cabecalho da tabela
    $vetTamCols = $pdf->CabecalhoBlocoTabela($arrayCabecalho, $vetTamCols);
    //print_r($vetTamCols);
    foreach ($listaAtuacao as $atuacao) {
        $i++;

        $dsItem = Atuacao::getDsTipo($atuacao->getATU_TP_ITEM());
        $dsAreaSubarea = $atuacao->getDsAreaSubarea();
        $qt = $atuacao->getATU_QT_ITEM();

        $pdf->LinhaBlocoTabela(array($dsItem, $dsAreaSubarea, $qt), $vetTamCols, array("L", "L", "R"));
    }
} else {
    $pdf->Ln(1);
    $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), NULL, PDFNeaad::$FONTE_ITEM_BLOCO[1]);
    $pdf->Cell(0, 4, "Usuário não cadastrou atuação.", 0, 1, 'L');
}
$pdf->FinalizaBloco();

// escrevendo validador
$pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), "", 10);
$pdf->SetY($pdf->GetY() + 1);
$pdf->Cell(0, 4, "Autenticidade: {$inscricao->getVerificadorCompInsc()}", 0, 0, 'R');

// escrevendo alerta
$pdf->Ln(8);
$pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), "", 10);
$pdf->MultiCell(0, 4, "$inscricao->PCH_TXT_COMP_INSCRICAO");

// imprimindo saida do PDF
$pdf->Output("comprovanteInscricao.pdf", "I");

/**
 * 
 * @param PDFNeaad $pdf
 * @param FormacaoAcademica $formacao
 * @param array $arrayLabs
 * @param int $tamEsq 
 */
function imprimeFormacao($pdf, $formacao, $arrayLabs, $tamEsq) {
    $pdf->ItemBlocoUnico($arrayLabs[0], "{$formacao->getDsPeriodo()} - {$formacao->TPC_NM_TIPO_CURSO}", $tamEsq);
    $pdf->ItemBlocoUnico($arrayLabs[1], $formacao->getDsInstituicaoComp(), $tamEsq);
    if (!Util::vazioNulo($formacao->getFRA_NM_CURSO())) {
        $pdf->ItemBlocoUnico($arrayLabs[2], $formacao->getFRA_NM_CURSO(), $tamEsq);
    }
    if (!Util::vazioNulo($formacao->getFRA_ID_AREA_CONH())) {
        $pdf->ItemBlocoUnico($arrayLabs[3], $formacao->getDsAreaSubarea(), $tamEsq);
    }
    if (!Util::vazioNulo($formacao->getFRA_CARGA_HORARIA())) {
        $pdf->ItemBlocoUnico($arrayLabs[4], $formacao->getFRA_CARGA_HORARIA(), $tamEsq);
    }
}

/**
 * 
 * @param PDFNeaad $pdf
 * @param array $grupos
 * @param InscricaoProcesso $insc
 */
function imprimeInfoComp($pdf, $grupos, $insc) {
    // ATENÇÃO: AO ALTERAR ESTE BLOCO DE CÓDIGO, É IMPORTANTE REVISAR OS ARQUIVOS ASSOCIADOS:
    // 1 - consultarGrupoAnexoProc.php
    // 2 - criarInscProcesso.php
    // 3 - consultarInscProcesso.php
    // 4 - imprimirCompInscricao.php
    // 5 - consultarInscProcessoAdmin.php
    // 6 - fragmentoAvaliarInfComp.php

    $margemResposta = 2;

    // preenchendo os grupos
    foreach ($grupos as $grupo) {
        // A seguir: Nome da pergunta
        if (!Util::vazioNulo($grupo->getGAP_NM_GRUPO())) {
            $pdf->ItemBlocoCorrido($grupo->getGAP_NM_GRUPO(), 'B');
        }

        if ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
            // apenas incluindo resposta
            $resp = buscarRespPorInscricaoGrupoCT($insc->getIPR_ID_INSCRICAO(), $grupo->getGAP_ID_GRUPO_PROC());
            $pdf->ItemBlocoCorrido(RespAnexoProc::getDsResposta($resp, FALSE), NULL, $margemResposta);
        } elseif ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {

            // nesse caso carregar perguntas
            $itens = buscarItemPorGrupoCT($grupo->getGAP_ID_GRUPO_PROC());

            // recuperando resposta do grupo, se existir
            $resp = buscarRespPorInscricaoGrupoCT($insc->getIPR_ID_INSCRICAO(), $grupo->getGAP_ID_GRUPO_PROC());
            $arrayResp = $resp != NULL ? $resp->respParaArray() : $arrayResp = NULL;

            // caso de nao existir resposta
            if ($resp == NULL) {
                $pdf->ItemBlocoCorrido(RespAnexoProc::getStrSemResposta(), NULL, $margemResposta);
            } else {
                // percorrendo os itens do grupo
                foreach ($itens as $item) {
                    // verificando se o item esta respondido
                    $itemRespondido = $arrayResp != NULL ? RespAnexoProc::isResposta($arrayResp, $item->getIAP_DS_ITEM()) : FALSE;

                    // caso de item respondido
                    if ($itemRespondido) {
                        $respostaTela = "- {$item->getIAP_NM_ITEM()}";

                        // tratando complementos
                        if ($item->temComplemento()) {

                            // recuperando resposta do complemento
                            $respostaComp = "";
                            $respCompBD = buscarRespPorInscricaoItemCT($insc->getIPR_ID_INSCRICAO(), $item->getIAP_ID_ITEM());
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
                        // adicionando no pdf
                        $pdf->ItemBlocoCorrido($respostaTela, NULL, $margemResposta);
                        //
                    } // fim item respondido
                }
            }// fim else com resposta
        }
    }
}
?>
