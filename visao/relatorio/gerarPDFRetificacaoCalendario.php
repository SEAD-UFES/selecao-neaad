<?php

// carregando itens imprescindíveis
require_once (dirname(__FILE__) . "/../../config.php");
global $CFG;

require_once ($CFG->rpasta . "/util/sessao.php");
require_once ($CFG->rpasta . "/controle/CTProcesso.php");
?>

<?php

// verificando caso de chamada direta
if (isset($_POST['valido']) && $_POST['valido'] == "retificacaocalendario") {

// validando ação
    if (isset($_GET['acao']) && ($_GET['acao'] == "previaCalendario" || $_GET['acao'] == "validarPrevia")) {

        // apenas administrador ou coordenador
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem dos dados básicos
        if (!isset($_POST['dadosPrevia'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // extraindo dados
        $vetParam = array();
        parse_str($_POST['dadosPrevia'], $vetParam);

        if (!isset($vetParam['idProcesso']) || !isset($vetParam['idChamada']) || !isset($vetParam['textoInicial'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // variáveis de controle
        $teveModificacaoCal = FALSE;
        $msgErroValidacao = "";

        // recuperando dados do calendário e validando
        $processo = buscarProcessoComPermissaoCT($vetParam['idProcesso']);
        $chamada = buscarChamadaPorIdCT($vetParam['idChamada'], $vetParam['idProcesso']);
        $listaCalendario = $chamada->listaItensCalendario(TRUE);

        // verificando se tem etapa vigente para eliminar itens desnecessários
        if (isset($vetParam['idEtapaVigente']) && !Util::vazioNulo($vetParam['idEtapaVigente'])) {
            $etapaVigente = buscarEtapaVigenteCT($chamada->getPCH_ID_CHAMADA(), $vetParam['idEtapaVigente']);
            $etapaVigente->removerItensIrrelevantesCalPubResul($chamada, $listaCalendario);
        }

        $vetCal = array(); // vetor para armazenar dados
        foreach ($listaCalendario as $item) {
            if ($item['editavel']) {

                if (isset($vetParam[$item['idInput1']])) {
                    $vetCal [$item['idInput1']] = $vetParam[$item['idInput1']];
                }
                if ($item['itemDuplo'] && isset($vetParam[$item['idInput2']])) {
                    $vetCal [$item['idInput2']] = $vetParam[$item['idInput2']];
                }

                // validando preenchimento
                $dadosIguais = ProcessoChamada::validaPreenchimentoItemCal($item, $vetCal, FALSE);
                if (!is_bool($dadosIguais)) {
                    $msgErroValidacao = $dadosIguais;
                    break; // saindo, pois houve erro
                }

                if (!$dadosIguais) {
                    $teveModificacaoCal = TRUE;

                    // validação do início do período de inscrições
                    if ($item['tipo'] == ProcessoChamada::$CAL_TP_ITEM_INSCRICAO) {
                        if (!$chamada->validaIniInscricaoCal($processo, $vetCal[$item['idInput1']])) {
                            $msgErroValidacao = "Data de início das inscrições inconsistente!";
                            break; // saindo, pois houve erro
                        }
                    }
                }
            }
        }

        // validando caso de não modificação do calendário
        if ($msgErroValidacao == "") {
            if (!$teveModificacaoCal) {
                $msgErroValidacao = "Você não modificou o calendário!";
            }
        }

        // definindo se é necessário exibir erros
        if ($msgErroValidacao != "") {
            if ($_GET['acao'] == "validarPrevia") {
                print json_encode(array("val" => false, "msg" => $msgErroValidacao, "semModificacao" => !$teveModificacaoCal));
                return;
            } elseif ($_GET['acao'] == "previaCalendario") {
                print "Erro ao exibir prévia: $msgErroValidacao";
                return;
            }
        }



        if ($_GET['acao'] == "previaCalendario") {
            // gerando arquivo de prévia
            calendario_gerarArquivoPrevia($processo, $chamada, $vetParam['textoInicial'], $chamada->listaItensCalendario(TRUE), $vetCal, "I");
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
 * @param string $textoInicial Texto inicial a ser incluída no documento
 * @param array $listaCalendario Array com a lista de itens do calendário, estruturado conforme função específica
 * @param array $dadosNovos Array com os novos dados, indexados na forma do array $listaCalendario
 * @param string $tpSaida Tipo de saída do documento: I -> Arquivo direto para o navegador; F -> Salvar em arquivo; S -> Retorna a string
 * @param string $arqSaida Nome do arquivo onde deve ser salvo o PDF
 */
function calendario_gerarArquivoPrevia($processo, $chamada, $textoInicial, $listaCalendario, $dadosNovos, $tpSaida, $arqSaida = NULL) {
    global $CFG;

    // carregando classes do PDF
    require_once ($CFG->rpasta . "/include/PDF/fpdf.php");
    require_once ($CFG->rpasta . "/include/PDF/PDFNeaad.php");

    // criando PDF
    $pdf = new PDFNeaad($processo->getDsEditalParaPDF(), $chamada->getPCH_DS_CHAMADA(TRUE), "Retificação do calendário");

    // imprimindo mensagem inicial
    $pdf->SetY($pdf->getY() + 4);
    $pdf->SetFont('', '', 9);
    $pdf->MultiCell(0, 5, $textoInicial);
    $pdf->SetY($pdf->getY() + 4);

    // Imprimindo calendário
    $pdf->InicializaBloco("Novo Calendário");

    // percorrendo para verificar tamanho do bloco à esquerda
    $arraTextoEsq = array();
    foreach ($listaCalendario as $item) {
        $arraTextoEsq [] = $item['nmItem'];
    }
    $tamBlocoEsq = $pdf->maiorStrArray($arraTextoEsq, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);

    // percorrendo e imprimindo
    foreach ($listaCalendario as $item) {
        // montando valor
        $alterou = FALSE;
        $vlItem = defineVlItem($item, $dadosNovos, $alterou);
        $pdf->ItemBlocoUnico($item['nmItem'], $vlItem, $tamBlocoEsq, $alterou);
    }

    // imprimindo nota
    $msg = "* As novas datas estão destacadas em vermelho.";
    $pdf->SetY($pdf->getY() + 4);
    $pdf->SetFont('', '', 9);
    $pdf->MultiCell(0, 5, $msg, 0, 'L');

    $pdf->FinalizaBloco();

    // imprimindo assinatura
    $msg = "Coordenação do $processo->CUR_DS_CURSO.";
    $pdf->SetY($pdf->getY() + 10);
    $pdf->SetFont('', 'B', 9);
    $pdf->MultiCell(0, 5, $msg, 0, 'R');

    // imprimindo saida do PDF
    $arqSaida = Util::vazioNulo($arqSaida) ? "retificacaoCalendario.pdf" : $arqSaida;

    return $pdf->Output($arqSaida, $tpSaida);
}

function defineVlItem($item, $dadosNovos, &$alterou) {
    if (!$item['editavel']) {
        return $item['vlItem'];
    }

    $vlItem = isset($dadosNovos[$item['idInput1']]) ? $dadosNovos[$item['idInput1']] : $item['vlItem1'];
    $alterou = isset($dadosNovos[$item['idInput1']]) && $dadosNovos[$item['idInput1']] != $item['vlItem1'];

    if ($item['itemDuplo']) {
        $vlItem .= " a ";
        $vlItem .= isset($dadosNovos[$item['idInput2']]) ? $dadosNovos[$item['idInput2']] : $item['vlItem2'];
        $alterou .= isset($dadosNovos[$item['idInput2']]) && $dadosNovos[$item['idInput2']] != $item['vlItem2'];
    }

    return $vlItem;
}
