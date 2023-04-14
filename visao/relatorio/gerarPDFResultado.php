<?php

// carregando itens imprescindíveis
require_once (dirname(__FILE__) . "/../../config.php");
global $CFG;

require_once ($CFG->rpasta . "/util/sessao.php");
require_once ($CFG->rpasta . "/controle/CTProcesso.php");
?>

<?php

// verificando caso de chamada direta
if (isset($_POST['valido']) && $_POST['valido'] == "resultado") {

// validando ação
    if (isset($_GET['acao']) && ($_GET['acao'] == "previaResultado" || $_GET['acao'] == "validarPrevia")) {

        // apenas administrador ou coordenador
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem dos dados básicos
        if (!isset($_POST['idProcesso']) || !isset($_POST['idChamada']) || !isset($_POST['idEtapaSel'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // recuperando dados para processamento
        $processo = buscarProcessoComPermissaoCT($_POST['idProcesso']);
        $chamada = buscarChamadaPorIdCT($_POST['idChamada'], $processo->getPRC_ID_PROCESSO());
        $etapaVigente = buscarEtapaVigenteCT($chamada->getPCH_ID_CHAMADA(), $_POST['idEtapaSel']);

        // executando as devidas validações
        $msgErroValidacao = "";
        // etapa inválida
        if ($etapaVigente == NULL) {
            $msgErroValidacao = "Etapa vigente não encontrada.";
        } else {
            // validando publicação de resultado
            $valPublicacao = EtapaSelProc::validarPublicacaoResulPendente($chamada, $etapaVigente);
            if (!$valPublicacao['val']) {

                // filtrando caso de permissão
                if (!(isset($valPublicacao['permissao']) && $valPublicacao['permissao'])) {
                    $msgErroValidacao = $valPublicacao['msg'];
                }
            }
        }

        // definindo se é necessário exibir erros
        if ($msgErroValidacao != "") {
            if ($_GET['acao'] == "validarPrevia") {
                print json_encode(array("val" => false, "msg" => $msgErroValidacao));
                return;
            } elseif ($_GET['acao'] == "previaResultado") {
                print "Erro ao exibir prévia: $msgErroValidacao";
                return;
            }
        }

        // executando ação propriamente dita 
        if ($_GET['acao'] == "previaResultado") {
            // gerando arquivo de prévia
            resultado_gerarArquivoPrevia($processo, $chamada, $etapaVigente, "I");
        } elseif ($_GET['acao'] == "validarPrevia") {
            print json_encode(array("val" => true));
            return;
        }
    }
}

/**
 * 
 * @global stdclass $CFG
 * @param Processo $processo
 * @param ProcessoChamada $chamada
 * @param EtapaSelProc $etapaVigente 
 * @param string $tpSaida Tipo de saída do documento: I -> Arquivo direto para o navegador; F -> Salvar em arquivo; S -> Retorna a string
 * @param string $arqSaida Nome do arquivo onde deve ser salvo o PDF
 * @param mixed $resultadoFinal Diz se é para gerar o resultado final. (Apenas válido para $tpSaida = "F") Padrão: FALSE
 *  Se Sim (char), temos 'F -> Resultado final ou 'P' -> Resultado povisório.
 */
function resultado_gerarArquivoPrevia($processo, $chamada, $etapaVigente, $tpSaida, $arqSaida = NULL, $resultadoFinal = FALSE) {
    global $CFG;
    $strResulFinal = "Resultado final";
    $strProvisorio = " provisório";

    // carregando classes do PDF
    require_once ($CFG->rpasta . "/include/PDF/fpdf.php");
    require_once ($CFG->rpasta . "/include/PDF/PDFNeaad.php");

    // definindo resultado pendente
    $resultadoPendente = $etapaVigente->getResultadoPendente();

    // criando PDF
    $dsResultado = $resultadoFinal === FALSE ? $resultadoPendente[1] : ($resultadoFinal == 'F' ? $strResulFinal : $strResulFinal . $strProvisorio);
    $pdf = new PDFNeaad($processo->getDsEditalParaPDF(), $chamada->getPCH_DS_CHAMADA(TRUE), $dsResultado);

    // recuperando dados de exportação
    $etapaAval = EtapaAvalProc::buscarEtapaAvalPorId($etapaVigente->getEAP_ID_ETAPA_AVAL_PROC());
    $expResultado = InscricaoProcesso::montarObjExportacaoResultado($chamada, $resultadoFinal === FALSE ? $etapaAval : NULL);

    // gerando arquivo
    resultado__arquivoPrevia($expResultado, $pdf);

    // verificando se precisa escrever o resultado final ou sua prévia
    if ($etapaVigente->isUltimaEtapa()) {

        // caso de tipo de saída no navegador ou retornar a string
        if ($tpSaida == "I" || $tpSaida == "S") {

            // preparando objeto para resultado final
            $expResultadoFinal = InscricaoProcesso::montarObjExportacaoResultado($chamada);

            // verificando se é prévia
            if ($resultadoPendente[0] == EtapaSelProc::$PENDENTE_RESUL_PARCIAL || $resultadoPendente[0] == EtapaSelProc::$PENDENTE_RET_RESUL_PARCIAL) {
                $strResulFinal .= $strProvisorio;
            }
            $pdf->addPaginaNovoContexto($strResulFinal);

            // gerando arquivo final
            resultado__arquivoPrevia($expResultadoFinal, $pdf);
        }
    }

    // imprimindo saida do PDF
    $arq = Util::vazioNulo($arqSaida) ? "resultado.pdf" : $arqSaida;
    return $pdf->Output($arq, $tpSaida);
}

/**
 * 
 * FUNÇÃO DE PROCESSAMENTO INTERNO! Não chamar diretamente.
 * 
 * @param ExportacaoResultado $expResultado Objeto de exportação
 * @param PDFNeaad $pdf Objeto devidamente inicializado, onde será realizado o append dos dados
 */
function resultado__arquivoPrevia($expResultado, &$pdf) {

    // tratando caso de não haver inscritos
    if ($expResultado == NULL) {
        $pdf->SetY($pdf->getY() + 20);
        $pdf->SetFont('zapfdingbats', '', 20);
        $pdf->Cell(5, 5, 'F');
        $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), 'B', 9);
        $pdf->MultiCell(0, 5, ' Não houve candidatos inscritos neste processo.');
        $pdf->SetY($pdf->getY() + 4);
    } else {
        $expResultado->inicializaGruposResultadoPDF();
        while (1) {
            $grupo = $expResultado->getProxGrupoResultadoPDF();

            // caso de saída
            if ($grupo == NULL) {
                break; // acabaram os grupos
            }

            // verificando se o grupo e o tipo cabem na mesma página
            $altGrupo = 5;
            $nb = $pdf->NbLines(PDFNeaad::getLARGURA_PAG() - $pdf->getMargemDir() - $pdf->getMargemEsq(), $grupo->getDsGrupo());
            $nb += $pdf->NbLines(PDFNeaad::getLARGURA_PAG() - $pdf->getMargemDir() - $pdf->getMargemEsq(), $grupo->getDsTpResultado());
            $h = ($altGrupo * $nb) + 1;

            //Issue a page break first if needed
            $pdf->CheckPageBreak($h);

            // imprimindo grupo
            $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), 'B', 11);
            $pdf->SetTextColor(16, 71, 120);
            $pdf->MultiCell(0, $altGrupo, $grupo->getDsGrupo(), 0, 'C');
            $pdf->ln(1);

            // imprimindo tipo
            $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), 'B', 14);
            $pdf->SetTextColor(16, 71, 120);
            $pdf->MultiCell(0, $altGrupo, $grupo->getDsTpResultado(), 0, 'C');

            // linhas de espaçamento e cor
            $pdf->ln(10);
            $pdf->SetTextColor(0);

            // verificando caso de grupos vazios
            if ($grupo->isVazio()) {
                $pdf->SetWidths(array(PDFNeaad::getLARGURA_PAG() - $pdf->getMargemDir() - $pdf->getMargemEsq()));
                $pdf->SetAligns(array('L'));
                $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), '', 10);
                $pdf->Row(array('Não houve candidatos neste grupo.'));
            } else {
                // grupos completos
                // 
                // 
                // escrevendo cabeçalho da tabela
                $cabecalho = $expResultado->getCabecalhoPDF($grupo);

                // setando fonte do cabecalho e alinhamento
                $pdf->SetTextColor(16, 71, 120);
                $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), 'B', 10);
                $vetAlinCab = array_pad(array(), count($cabecalho), 'L');
                $pdf->SetAligns($vetAlinCab);

                // verificando casos para montagem dos tamanhos
                if ($grupo->getTpResultado() == "E") { // grupo dos eliminados
                    // definindo tamanhos
                    $insc = $pdf->GetStringWidth("0000000") + 1;
                    $sobra = PDFNeaad::getLARGURA_PAG() - $pdf->getMargemDir() - $pdf->getMargemEsq() - $insc;
                    $nome = 0.4 * $sobra;
                    $justificativa = 0.6 * $sobra;
                    $pdf->SetWidths(array($insc, $nome, $justificativa));
                } else {
                    // definindo tamanhos
                    $cPadrao = $pdf->GetStringWidth("0000000") + 1;
                    $nome = PDFNeaad::getLARGURA_PAG() - $pdf->getMargemDir() - $pdf->getMargemEsq() - ($cPadrao * (count($cabecalho) - 1));

                    $vetTamanhos = array_pad(array(), count($cabecalho), $cPadrao);
                    $vetTamanhos[1] = $nome;

                    $pdf->SetWidths($vetTamanhos);
                }
                $pdf->Row($cabecalho);

                // fim cabeçalho
                // inserindo dados da tabela
                // setando fontes dos dados
                // setando fonte do cabecalho
                $pdf->SetTextColor(0);
                $pdf->SetFont(PDFNeaad::getFONTE_PADRAO(), '', 10);

                // setando alinhamentos
                if ($grupo->getTpResultado() == "E") { // grupo dos eliminados
                    // definindo alinhamentos
                    $pdf->SetAligns(array('R', 'L', 'L'));
                } else {
                    // definindo tamanhos
                    $vetAlinhamentos = array_pad(array(), count($cabecalho), 'R');
                    $vetAlinhamentos[1] = 'L';

                    $pdf->SetAligns($vetAlinhamentos);
                }

                // 
                // 
                // preenchendo com os dados
                $matDados = $grupo->getDadosGrupoPDF($expResultado->getCabecalhoCSV(), $expResultado->getCabecalhoPDF($grupo), $expResultado);

                foreach ($matDados as $vetDados) {
                    $pdf->Row($vetDados);
                }
            }

            // linhas de espaçamento e cor
            $pdf->ln(10);
        }
    }
}
