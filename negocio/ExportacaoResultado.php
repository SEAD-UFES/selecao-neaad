<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Esta classe armazena um grupo de resultados a ser exibido
 * 
 */
class GrupoResultado {

    private $dsGrupo;
    private $colClassCSV;
    private $colNomeCSV;
    private $tpResultado;
    private $dsTpResultado;
    private $identificador;
    private $matrizInsc; // armazena os dados na forma: [IdInscricao => array(d1, d2, ..., dn)]

    /**
     * 
     * @param string $identificador
     * @param string $dsGrupo
     * @param string $tpResultado
     * @param string $dsTpResultado
     */

    function __construct($identificador, $dsGrupo, $tpResultado, $dsTpResultado) {
        $this->identificador = $identificador;
        $this->dsGrupo = $dsGrupo;
        $this->tpResultado = $tpResultado;
        $this->dsTpResultado = $dsTpResultado;
        $this->matrizInsc = array();
    }

    public function isVazio() {
        return Util::vazioNulo($this->matrizInsc);
    }

    private function getColClassCSV() {
        if (Util::vazioNulo($this->colClassCSV)) {
            throw new NegocioException("Coluna de classificação não foi setada!");
        }
        return $this->colClassCSV;
    }

    private function getColNomeCSV() {
        if (Util::vazioNulo($this->colNomeCSV)) {
            throw new NegocioException("Coluna de nome não foi setada!");
        }
        return $this->colNomeCSV;
    }

    /**
     * 
     * @param array $matRetorno Endereço do array onde deve ser adicionado os dados da matriz
     * @param ExportacaoResultado $expResultado
     */
    public function addMatrizEmRetorno(&$matRetorno, $expResultado) {
        // setando colunas para classificação
        $this->colClassCSV = $expResultado->getColClassCSV();
        $this->colNomeCSV = $expResultado->getColNomeCSV();

        // Quando não é grupo de eliminados, realizar sequenciamento correto
        if ($this->tpResultado != "E") {
            $chs = array_keys($this->matrizInsc);
            for ($i = 1; $i <= count($chs); $i++) {
                // sequenciando classificação
                $this->matrizInsc[$chs[$i - 1]][$this->getColClassCSV()] = $i;
            }
        }

        // ordenando pelo nome
        uasort($this->matrizInsc, array($this, "ordenaPorNome"));

        // adicionando...
        foreach ($this->matrizInsc as $ch => $vl) {
            $matRetorno[$ch] = $vl;
        }
    }

    private function ordenaPorClass($a, $b) {
        if (intval($a[$this->getColClassCSV()]) == intval($b[$this->getColClassCSV()])) {
            return 0;
        }
        return intval($a[$this->getColClassCSV()]) < intval($b[$this->getColClassCSV()]) ? -1 : 1;
    }

    private function ordenaPorNome($a, $b) {
        if ($a[$this->getColNomeCSV()] == $b[$this->getColNomeCSV()]) {
            return 0;
        }
        return $a[$this->getColNomeCSV()] < $b[$this->getColNomeCSV()] ? -1 : 1;
    }

    public function addResultado($linhaBD, $cabecalho, $idInscricao, $final = FALSE) {
        // inicializando
        $this->matrizInsc[$idInscricao] = array($this->getDsGrupoCompleta());

        // percorrendo cabecalho e montando dados
        for ($i = 1; $i < count($cabecalho); $i++) {
            $dado = $linhaBD[$cabecalho[$i]];

            // removendo notas dos eliminados
            if ((substr($cabecalho[$i], 0, 5) == "Etapa" || ($final && substr($cabecalho[$i], 0, 5) == "Final"))) {
                if ($this->tpResultado == "E") {
                    $dado = "";
                } else {
                    // adicionando máscara
                    $dado = NGUtil::formataDecimal($dado);
                }
            }

            $this->matrizInsc[$idInscricao][] = $dado;
        }
    }

    public function getDadosGrupoPDF($cabecalhoOriginal, $cabecalhoPDF, $expResultado) {
        $matTemp = array();
        $this->addMatrizEmRetorno($matTemp, $expResultado);

        // mapeando itens procurados
        $mapa = array();
        foreach ($cabecalhoPDF as $dadoRequerido) {
            $mapa [] = array_search($dadoRequerido, $cabecalhoOriginal);
        }

        // percorrendo vetor para gerar a matriz de retorno
        $matRetorno = array();
        foreach ($matTemp as $vetDados) {
            $linha = array();
            foreach ($mapa as $nrColunaDado) {
                $linha [] = $vetDados[$nrColunaDado];
            }
            $matRetorno [] = $linha;
        }

        return $matRetorno;
    }

    private function getDsGrupoCompleta() {
        $ret = $this->dsGrupo;
        $ret .= $ret == "" ? "" : " - ";
        $tmp = ExportacaoResultado::getTIPO_RESULTADO();
        return "$ret{$tmp[$this->tpResultado]}";
    }

    public function getIdentificador() {
        return $this->identificador;
    }

    public function getDsGrupo() {
        return $this->dsGrupo;
    }

    public function getDsTpResultado() {
        $tmp = ExportacaoResultado::getTIPO_RESULTADO();
        return "{$tmp[$this->tpResultado]}";
    }

    public function getTpResultado() {
        return $this->tpResultado;
    }

}

/**
 * Esta classe trata da organização dos dados para exportação do resultado de um edital
 *
 * @author estevao
 */
class ExportacaoResultado {

    private $arrayGrupos;
    private $posColClassCSV;
    private $posColNomeCSV;
    public static $NM_COLUNA_CLASSIF = "Class";
    public static $NM_COLUNA_NOME = "Nome";
    private static $TIPO_RESULTADO = array("A" => "APROVADO", "R" => "CADASTRO DE RESERVA", "E" => "ELIMINADO"); // Não Altere as chaves deste vetor!
    private $cabecalhoCSV;
    private static $COLUNAS_REMOVER = array("IPR_ID_INSCRICAO", "polo", "area", "reserva", "situacao"); // Ao alterar este vetor é necessário rever o código! Para fins de simplificação, são utilizados os indices deste vetor para o processamento!
    // campos exibição no PDF
    private $posGrupoExibPDFAtual = 0;
    private $arrayChavesGrupo;
    private $cabecalhoPDF;
    private $temCadastroReserva;

    public static function getTIPO_RESULTADO() {
        return self::$TIPO_RESULTADO;
    }

    /**
     * 
     * @return Retorna a posição da coluna de classificação no cabeçalho
     */
    public function getColClassCSV() {
        if (Util::vazioNulo($this->cabecalhoCSV)) {
            throw new NegocioException("Cabeçalho não definido para exportação de resultados via CSV.");
        }

        if ($this->posColClassCSV === NULL) {
            // carregando
            $this->posColClassCSV = array_search(self::$NM_COLUNA_CLASSIF, $this->cabecalhoCSV);

            // previnindo erros...
            if ($this->posColClassCSV === FALSE) {
                throw new NegocioException("Coluna de classifição não encontrada no cabeçalho CSV para exportação de resultados.");
            }
        }

        return $this->posColClassCSV;
    }

    /**
     * 
     * @return Retorna a posição da coluna nome no cabeçalho
     */
    public function getColNomeCSV() {
        if (Util::vazioNulo($this->cabecalhoCSV)) {
            throw new NegocioException("Cabeçalho não definido para exportação de resultados via CSV.");
        }

        if ($this->posColNomeCSV === NULL) {
            // carregando
            $this->posColNomeCSV = array_search(self::$NM_COLUNA_NOME, $this->cabecalhoCSV);

            // previnindo erros...
            if ($this->posColNomeCSV === FALSE) {
                throw new NegocioException("Coluna nome não encontrada no cabeçalho CSV para exportação de resultados.");
            }
        }

        return $this->posColNomeCSV;
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $matrizDadosBD Dados brutos retornados do BD para processamento
     * @param EtapaAvalProc $etapaAval Etapa de avaliação em questão. Pode ser nula, no caso de resultado final
     */
    function __construct($chamada, $matrizDadosBD, $etapaAval = NULL) {
        // criando grupos de resultado
        $this->arrayGrupos = array();

        // setando cadastro de reserva
        $this->temCadastroReserva = $etapaAval == NULL && $chamada->temCadastroReserva();

        //
        //
        // tem opções de inscrição?
        if (ProcessoChamada::temOpcaoInscricao($chamada)) {
            // recuperando dados para processamento
            $polos = $chamada->admitePoloObj() ? PoloChamada::buscarPoloPorChamada($chamada->getPCH_ID_CHAMADA()) : NULL;
            $areas = $chamada->admiteAreaAtuacaoObj() ? AreaAtuChamada::buscarAreaAtuCompPorChamada($chamada->getPCH_ID_CHAMADA()) : NULL;
            $reservas = $chamada->admiteReservaVagaObj() ? ReservaVagaChamada::buscarReservaVagaPorChamada($chamada->getPCH_ID_CHAMADA()) : NULL;

            // adicionando reserva de vaga
            if ($reservas != NULL) {
                $temp = new ReservaVagaChamada(ReservaVagaChamada::$ID_PUBLICO_GERAL, $chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), NULL, NULL, NULL);
                $temp->RVG_NM_RESERVA_VAGA = ReservaVagaChamada::$DS_PUBLICO_GERAL;
                $reservas [] = $temp;
            }

            // criando vetor de combinações e processando
            $arrayPri = $polos != NULL ? $polos : ($areas != NULL ? $areas : $reservas); // O primeiro a ter item, é o primeiro a ser iterado
            $arraySeg = $polos != NULL ? ($areas != NULL ? $areas : ($reservas != NULL ? $reservas : array())) : ($reservas != NULL ? $reservas : array()); // Aqui só pode ser área ou reserva de vaga
            $arrayTer = $reservas != NULL ? $reservas : array(); // Aqui só pode ser reserva de vaga
            //
            // iterando
            foreach ($arrayPri as $ch => $vl) {
                // é o último estágio
                if (Util::vazioNulo($arraySeg)) {
                    // Adicionando combinação
                    $this->addCombGrupo($etapaAval, $ch, $vl, NULL, NULL, NULL, NULL, $polos != NULL, $areas != NULL, $reservas != NULL);
                } else {
                    foreach ($arraySeg as $ch2 => $vl2) {
                        // é o último estágio
                        if (Util::vazioNulo($arrayTer)) {
                            // Adicionando combinação
                            $this->addCombGrupo($etapaAval, $ch, $vl, $ch2, $vl2, NULL, NULL, $polos != NULL, $areas != NULL, $reservas != NULL);
                        } else {
                            foreach ($arrayTer as $ch3 => $vl3) {
                                // Adicionando combinação
                                $this->addCombGrupo($etapaAval, $ch, $vl, $ch2, $vl2, $ch3, $vl3, $polos != NULL, $areas != NULL, $reservas != NULL);
                            }
                        }
                    }
                }
            }
        } else {
            // Não tem: Caso mais simples, só existe o grupo vazio
            $this->addCombGrupo($etapaAval);
        }

//        print_r(count($this->arrayGrupos));
//        // para depurar
//        foreach ($this->arrayGrupos as $grupos) {
//            print_r($grupos->getIdentificador() . "<br/>");
//        }
//        exit;
//        
//        
        // percorrendo dados do BD para processamento
        $qtLinhas = $numLinhas = ConexaoMysql::getNumLinhas($matrizDadosBD);
        for ($i = 0; $i < $qtLinhas; $i++) {
            $dadosLinha = ConexaoMysql::getLinha($matrizDadosBD);

            // primeira linha? então cria cabecalho
            if ($i == 0) {
                $this->geraCabecalhoCSV(array_keys($dadosLinha));
                $this->geraCabecalhoPDF(array_keys($dadosLinha));
            }

            // definindo grupo para enquadramento
            // 
            // tratando caso especial de polo: Tem polo e o candidato está eliminado
            if ($chamada->admitePoloObj() && Util::vazioNulo($dadosLinha[self::$COLUNAS_REMOVER[1]])) {
                // recuperando primeiro polo selecionados pelo candidato
                $poloSelInsc = PoloInscricao::buscarPoloPorInscricao($dadosLinha[self::$COLUNAS_REMOVER[0]], TRUE);
            } else {
                // O polo selecionado é o definido na própria sql de retorno
                $poloSelInsc = $dadosLinha[self::$COLUNAS_REMOVER[1]];
            }
            $idGrupo = $this->geraIdentificadorGrupo($this->getTpResultadoInsc($dadosLinha[self::$COLUNAS_REMOVER[4]]), $poloSelInsc, $dadosLinha[self::$COLUNAS_REMOVER[2]], !Util::vazioNulo($dadosLinha[self::$COLUNAS_REMOVER[3]]) ? $dadosLinha[self::$COLUNAS_REMOVER[3]] : ($chamada->admiteReservaVagaObj() ? ReservaVagaChamada::$ID_PUBLICO_GERAL : NULL));
            $this->processaResultado($dadosLinha, $idGrupo, $etapaAval == NULL);
        }
    }

    /**
     * 
     * @param EtapaAvalProc $etapaAval
     * @param mixed $ch1
     * @param mixed $vl1
     * @param mixed $ch2
     * @param mixed $vl2
     * @param mixed $ch3
     * @param mixed $vl3
     * @param boolean $temPolo
     * @param boolean $temArea
     * @param boolean $temReserva
     */
    private function addCombGrupo($etapaAval, $ch1 = NULL, $vl1 = NULL, $ch2 = NULL, $vl2 = NULL, $ch3 = NULL, $vl3 = NULL, $temPolo = FALSE, $temArea = FALSE, $temReserva = FALSE) {
        // definindo itens para id e descrição
        $idPolo = $temPolo ? $ch1 : NULL;
        $dsPolo = $temPolo ? $vl1 : NULL;

        $idArea = $temArea ? ($temPolo ? $vl2->getAAC_ID_AREA_CHAMADA() : $vl1->getAAC_ID_AREA_CHAMADA()) : NULL;
        $dsArea = $temArea ? ($temPolo ? $vl2->ARC_NM_SUBAREA_CONH : $vl1->ARC_NM_SUBAREA_CONH) : NULL;

        $idReserva = $temReserva ? (($temPolo && $temArea) ? $vl3->getRVC_ID_RESERVA_CHAMADA() : ($temPolo || $temArea ? $vl2->getRVC_ID_RESERVA_CHAMADA() : $vl1->getRVC_ID_RESERVA_CHAMADA())) : NULL;
        $dsReserva = $temReserva ? (($temPolo && $temArea) ? $vl3->RVG_NM_RESERVA_VAGA : ($temPolo || $temArea ? $vl2->RVG_NM_RESERVA_VAGA : $vl1->RVG_NM_RESERVA_VAGA)) : NULL;

        // iterando nos tipos de resultados
        foreach (self::$TIPO_RESULTADO as $ch => $vl) {

            // Não tem cadastro de reserva, removendo...
            if ($ch == "R" && !$this->temCadastroReserva) {
                continue;
            }

            $idGrupo = $this->geraIdentificadorGrupo($ch, $idPolo, $idArea, $idReserva);
            $grupoResulTemp = new GrupoResultado($idGrupo, $this->geraDsGrupo($dsPolo, $dsArea, $dsReserva), $ch, $vl);
            $this->arrayGrupos[$idGrupo] = $grupoResulTemp;
        }
    }

    private function processaResultado($linhaBD, $idGrupo, $final = FALSE) {
        if (isset($this->arrayGrupos[$idGrupo])) {
            $this->arrayGrupos[$idGrupo]->addResultado($linhaBD, $this->cabecalhoCSV, $linhaBD[self::$COLUNAS_REMOVER[0]], $final);
            return;
        }
        throw new NegocioException("Inconsistência ao processar exportação de resultado. Grupo '$idGrupo' inexistente!");
    }

    private function geraCabecalhoCSV($chaves) {
        $this->cabecalhoCSV = array("Grupo");
        foreach ($chaves as $ch) {
            if (!in_array($ch, self::$COLUNAS_REMOVER)) {
                $this->cabecalhoCSV [] = $ch;
            }
        }
    }

    private function geraCabecalhoPDF($chaves) {
        foreach ($chaves as $ch) {
            if (!in_array($ch, self::$COLUNAS_REMOVER)) {
                $this->cabecalhoPDF [] = $ch;
            }
        }
    }

    private function getTpResultadoInsc($situacaoInsc) {
        if ($situacaoInsc == NULL || $situacaoInsc == InscricaoProcesso::$SIT_INSC_OK) {
            return "A"; // candidato aprovado
        }
        if ($situacaoInsc == InscricaoProcesso::$SIT_INSC_CAD_RESERVA) {
            return "R"; // cadastro de reserva
        }
        return "E"; // Candidato eliminado
    }

    private function geraIdentificadorGrupo($tpResultado, $idPolo = NULL, $idArea = NULL, $idReserva = NULL) {
        $ret = $tpResultado;
        $temSeparador = FALSE;
        if ($idPolo != NULL) {
            $temSeparador = TRUE;
            $ret .= $idPolo;
        }
        if ($idArea != NULL) {
            $ret .= ($temSeparador ? ":" : "") . $idArea;
        }
        if ($idReserva != NULL) {
            $ret .= ($temSeparador ? ":" : "") . $idReserva;
        }
        return $ret;
    }

    private function geraDsGrupo($dsPolo = NULL, $dsArea = NULL, $dsReserva = NULL) {
        $ret = "";
        if ($dsPolo != NULL) {
            $ret .= $dsPolo;
        }
        if ($dsArea != NULL) {
            if ($dsPolo != NULL) {
                $ret .= " / ";
            }
            $ret .= $dsArea;
        }
        if ($dsReserva != NULL) {
            if ($dsPolo != NULL || $dsArea != NULL) {
                $ret .= " / ";
            }
            $ret .= $dsReserva;
        }
        return $ret;
    }

    public function getCabecalhoCSV() {
        return $this->cabecalhoCSV;
    }

    /**
     * 
     * @param GrupoResultado $grupo
     * @return array
     */
    public function getCabecalhoPDF($grupo) {
        $temp = array();

        foreach ($this->cabecalhoPDF as $cabecalho) {
            if ($grupo->getTpResultado() == "E" && $cabecalho != "Insc" && $cabecalho != "Nome" && $cabecalho != "Justificativa") {
                continue;
            }
            if ($grupo->getTpResultado() != "E" && $cabecalho == "Justificativa") {
                continue;
            }
            $temp [] = $cabecalho;
        }
        return $temp;
    }

    public function getMatrizCSV() {
        $matrizRet = array();

        // percorrendo grupos
        foreach ($this->arrayGrupos as $grupo) {
            if (!$grupo->isVazio()) {
                $grupo->addMatrizEmRetorno($matrizRet, $this);
            }
        }

//        print_r("Retorno:<br/>");
//        NGUtil::imprimeVetorDepuracao($matrizRet);
//        print_r("Cabeçalho:<br/>");
//        NGUtil::imprimeVetorDepuracao($this->getCabecalhoCSV());
//        exit;
        // retornando
        return $matrizRet;
    }

    public function inicializaGruposResultadoPDF() {
        $this->arrayChavesGrupo = array_keys($this->arrayGrupos);
    }

    /**
     * 
     * @return GrupoResultado
     */
    public function getProxGrupoResultadoPDF() {
        // acabaram os grupos
        if (Util::vazioNulo($this->arrayChavesGrupo) || $this->posGrupoExibPDFAtual >= count($this->arrayChavesGrupo)) {
            return NULL;
        }
        // incrementando e retornando
        $this->posGrupoExibPDFAtual++;
        return $this->arrayGrupos[$this->arrayChavesGrupo[$this->posGrupoExibPDFAtual - 1]];
    }

}
