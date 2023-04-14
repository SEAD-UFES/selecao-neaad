<?php

/**
 * tb_pch_processo_chamada class
 * This class manipulates the table ProcessoChamada
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date localhost
 * */
global $CFG;
require_once $CFG->rpasta . "/negocio/AcompProcChamada.php";
require_once $CFG->rpasta . "/negocio/Noticia.php";

class ProcessoChamada {

    private $PCH_ID_CHAMADA;
    private $PRC_ID_PROCESSO;
    private $PCH_DT_ABERTURA;
    private $PCH_DT_FECHAMENTO;
    private $PCH_DS_CHAMADA;
    private $PCH_NR_ORDEM;
    private $PCH_CHAMADA_ATUAL;
    private $PCH_NR_MAX_OPCAO_POLO;
    private $PCH_TXT_COMP_INSCRICAO;
    private $PCH_QT_VAGAS;
    private $PCH_ADMITE_AREA;
    private $PCH_INSCRICAO_MULTIPLA;
    private $PCH_DT_PREV_RESUL_FINAL;
    private $PCH_DT_REG_RESUL_FINAL;
    private $PCH_ID_USU_RESP_RESUL_FIN;
    private $PCH_ADMITE_RESERVA_VAGA;
    private $PCH_HASH_ALTERACAO_VAGAS;
    private $PCH_CHAMADA_ATIVA;
    private $PCH_URL_ARQ_RESUL_FINAL;
    private $PCH_DT_FINALIZACAO;
    //campos de controle de ativação da chamada
    private $PCH_ATV_DT_SOLICITACAO;
    private $PCH_ATV_DT_ATIVACAO;
    private $PCH_ATV_USU_RESPONSAVEL;
    private $PCH_ATV_USU_SOLICITANTE;
    // campos herdados
    private $NM_USU_RESP_RESUL_FIN;
    private $ATV_NOME_SOLICITANTE;
    public $PRC_DT_FIM;
    public $PRC_DT_INICIO;
    public $TIC_ID_TIPO_CARGO;
    // nome das fases de chamada
    private static $FASE_EM_CONSTRUCAO = "Chamada em construção";
    private static $FASE_FECHADA = "Chamada fechada";
    private static $FASE_INSCRICAO = "Período de inscrição";
    private static $FASE_ETAPA_RESUL_PARCIAL = "Proc. resultado parcial";
    private static $FASE_ETAPA_ESP_PER_RECURSO = "Aguardando período de recurso";
    private static $FASE_ETAPA_PER_RECURSO = "Período de recurso";
    private static $FASE_ETAPA_PROC_RECURSO = "Proc. recurso";
    private static $FASE_RESUL_PUBLICADO = "Resultado final publicado";
    private static $FASE_FINALIZADA = "Chamada finalizada";
    private static $FASE_NAO_DEFINIDA = "Não definida";
    public static $PREPOSICAO_FASE_CHAMADA_DA = "da";
    // nome das fases de chamada para candidato
    private static $FASECAN_INSCRICAO = "Inscrição";
    private static $FASECAN_ETAPA_RESUL_PARCIAL = "Resultado parcial";
    private static $FASECAN_ETAPA_PER_RECURSO = "Período de recurso";
    private static $FASECAN_ETAPA_PROC_RECURSO = "Resultado pós-Recurso";
    private static $FASECAN_RESULTADO_FINAL = "Resultado final";
    private static $FASECAN_FINALIZADA = "Finalização automática da chamada";
    // codigos da fase
    private static $COD_FASE_EM_CONSTRUCAO = "C";
    private static $COD_FASE_FECHADA = "F";
    private static $COD_FASE_INSCRICAO = "I";
    private static $COD_FASE_ETAPA_RESUL_PARCIAL = "P";
    private static $COD_FASE_ETAPA_ESP_PER_RECURSO = "E";
    private static $COD_FASE_ETAPA_PER_RECURSO = "R";
    private static $COD_FASE_ETAPA_PROC_RECURSO = "U";
    private static $COD_FASE_RESUL_PUBLICADO = "B";
    private static $COD_FASE_FINALIZADA = "O";
    private static $COD_FASE_NAO_DEFINIDA = "N";
    // campos de processamento interno
    private $idDsFaseChamada; // array na forma: [idFase, dsFaseChamada, dtFinalizacaoFase]
    private $idDsProxFaseChamada; // array na forma: [idProxFase, dsProxFaseChamada, dtExecucaoFase]
    private $listaCal; // lista de itens do calendário
    private $listaCalEdicao; // lista de itens do calendario para edição
    private $statusPerInscricao = NULL; // Armazena o vetor de status do período de inscrição. Carregado sob demanda. Nunca chame diretamente.
    private $temCadastroReserva = NULL; // Informa se a chamada tem reserva de vaga. Carregado sob demanda. Nunca chame diretamente.
    //
    //
    // códigos de evento
    public static $EVENTO_PASSADO = "A";
    public static $EVENTO_PRESENTE = "P";
    public static $EVENTO_FUTURO = "F";
    //
    //
    //
    // códigos do tipo de item do calendário
    public static $CAL_TP_ITEM_INSCRICAO = "I";
    public static $CAL_TP_ITEM_RESUL_PARC = "P";
    public static $CAL_TP_ITEM_RECURSO = "R";
    public static $CAL_TP_ITEM_RESUL_FIN = "F";
    // vetor de descrição de chamadas
    private static $VET_DESC_CHAMADA = array("Primeira", "Segunda", "Terceira", "Quarta", "Quinta", "Sexta", "Sétima", "Oitava", "Nona", "Décima", "Décima Primeira", "Décima Segunda", "Décima Terceira", "Décima Quarta", "Décima Quinta");
    // campos de auxilio
    public static $TAM_MAX_TEXTO_COMP_INSC = 3000;
    //
    //
    //
    // Definição de mensagens com * (asterisco)
    public static $UM_ASTERISCO = "*";
    public static $UM_ASTERISCO_HTML_MSG = "<p>* Data prevista, pode sofrer alterações.</p>";
    //
    //
    // Algumas constantes de interface
    private static $COD_CADASTRO_RESERVA = "CR";
    //
    // Algumas constantes de processamento interno
    public static $SQL_RET_SOBRA_VAGAS = "sobra";
    public static $ADENDO_ARQ_PROVISORIO = "Provisorio";

    /**
     * 
     * @param ProcessoChamada $chamada
     */
    public static function getCodCadastroReserva($chamada) {
        return $chamada->temCadastroReserva() ? self::$COD_CADASTRO_RESERVA : Util::$STR_CAMPO_VAZIO;
    }

    /* Construtor padrão da classe */

    public function __construct($PCH_ID_CHAMADA, $PRC_ID_PROCESSO, $PCH_DT_ABERTURA, $PCH_DT_FECHAMENTO, $PCH_DS_CHAMADA, $PCH_NR_ORDEM, $PCH_CHAMADA_ATUAL, $PCH_NR_MAX_OPCAO_POLO, $PCH_TXT_COMP_INSCRICAO, $PCH_ADMITE_AREA = NULL, $PCH_INSCRICAO_MULTIPLA = NULL, $PCH_DT_PREV_RESUL_FINAL = NULL, $PCH_DT_REG_RESUL_FINAL = NULL, $PCH_ID_USU_RESP_RESUL_FIN = NULL, $PCH_ADMITE_RESERVA_VAGA = NULL, $PCH_QT_VAGAS = NULL, $PCH_HASH_ALTERACAO_VAGAS = NULL, $PCH_CHAMADA_ATIVA = NULL, $PCH_URL_ARQ_RESUL_FINAL = NULL, $PCH_DT_FINALIZACAO = NULL, $PCH_ATV_DT_SOLICITACAO = NULL, $PCH_ATV_DT_ATIVACAO = NULL, $PCH_ATV_USU_RESPONSAVEL = NULL, $PCH_ATV_USU_SOLICITANTE = NULL) {
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->PCH_DT_ABERTURA = $PCH_DT_ABERTURA;
        $this->PCH_DT_FECHAMENTO = $PCH_DT_FECHAMENTO;
        $this->PCH_DS_CHAMADA = $PCH_DS_CHAMADA;
        $this->PCH_NR_ORDEM = $PCH_NR_ORDEM;
        $this->PCH_CHAMADA_ATUAL = $PCH_CHAMADA_ATUAL;
        $this->PCH_NR_MAX_OPCAO_POLO = $PCH_NR_MAX_OPCAO_POLO;
        $this->PCH_TXT_COMP_INSCRICAO = $PCH_TXT_COMP_INSCRICAO;
        $this->PCH_QT_VAGAS = $PCH_QT_VAGAS;
        $this->PCH_ADMITE_AREA = $PCH_ADMITE_AREA;
        $this->PCH_INSCRICAO_MULTIPLA = $PCH_INSCRICAO_MULTIPLA;
        $this->PCH_DT_PREV_RESUL_FINAL = $PCH_DT_PREV_RESUL_FINAL;
        $this->PCH_DT_REG_RESUL_FINAL = $PCH_DT_REG_RESUL_FINAL;
        $this->PCH_ID_USU_RESP_RESUL_FIN = $PCH_ID_USU_RESP_RESUL_FIN;
        $this->PCH_ADMITE_RESERVA_VAGA = $PCH_ADMITE_RESERVA_VAGA;
        $this->PCH_HASH_ALTERACAO_VAGAS = $PCH_HASH_ALTERACAO_VAGAS;
        $this->PCH_CHAMADA_ATIVA = $PCH_CHAMADA_ATIVA;
        $this->PCH_URL_ARQ_RESUL_FINAL = $PCH_URL_ARQ_RESUL_FINAL;
        $this->PCH_DT_FINALIZACAO = $PCH_DT_FINALIZACAO;
        $this->idDsFaseChamada = NULL;
        $this->idDsProxFaseChamada = NULL;
        $this->listaCal = NULL;
        $this->listaCalEdicao = NULL;
        $this->PCH_ATV_DT_SOLICITACAO = $PCH_ATV_DT_SOLICITACAO;
        $this->PCH_ATV_DT_ATIVACAO = $PCH_ATV_DT_ATIVACAO;
        $this->PCH_ATV_USU_RESPONSAVEL = $PCH_ATV_USU_RESPONSAVEL;
        $this->PCH_ATV_USU_SOLICITANTE = $PCH_ATV_USU_SOLICITANTE;
    }

#   ----------------------------- FUNÇÕES DE MANIPULAÇÃO DE ARQUIVOS -----------------------------------------

    /**
     * Esta função retorna o nome padrão, que deve estar contido em todos os arquivos relacionados a chamada
     * 
     * ATENÇÃO: Não é necessário chamar a função padrão do processo após chamar esta função
     * 
     * @param Processo $processo
     * @return string
     */
    public function ARQS_getPadraoNomeArqsChamada($processo) {
        return "{$processo->ARQS_getPadraoNomeArqsEdital()}-cham{$this->PCH_NR_ORDEM}";
    }

    private function ARQS_getUrlIniArqAltCalendario($processo) {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$processo->getNomePastaEdital()}RetCal-{$this->ARQS_getPadraoNomeArqsChamada($processo)}";
    }

    private function ARQS_getUrlIniArqAltVagas($processo) {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$processo->getNomePastaEdital()}RetVagas-{$this->ARQS_getPadraoNomeArqsChamada($processo)}";
    }

    /**
     * 
     * @param Processo $processo
     * @return string
     */
    private function ARQS_iniNomeArquivoResulFinal($processo, $provisorio = FALSE) {
        $adendo = !$provisorio ? "" : self::$ADENDO_ARQ_PROVISORIO;
        return "ResulFinal-{$this->ARQS_getPadraoNomeArqsChamada($processo)}" . $adendo;
    }

    /**
     * 
     * @param Processo $processo
     * @return string
     */
    private function ARQS_nomeArquivoResulFinal($processo, $provisorio = FALSE) {
        return "{$this->ARQS_iniNomeArquivoResulFinal($processo, $provisorio)}" . AcompProcChamada::$TIPO_PDF;
    }

    /**
     * 
     * @param Processo $processo
     * @return string
     */
    public function ARQS_getUrlArqResulFinal($processo, $provisorio = FALSE) {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$processo->getNomePastaEdital()}{$this->ARQS_nomeArquivoResulFinal($processo, $provisorio)}";
    }

    /**
     * 
     * @param Processo $processo
     * @return string
     */
    public function ARQS_getIniUrlArqResulFinal($processo, $provisorio = FALSE) {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$processo->getNomePastaEdital()}{$this->ARQS_iniNomeArquivoResulFinal($processo, $provisorio)}";
    }

    public function getUrlArquivoResulFinal($provisorio = FALSE) {
        global $CFG;

        // redirecionando para erro
        if (Util::vazioNulo($this->PCH_URL_ARQ_RESUL_FINAL)) {
            return "{$CFG->rwww}/404.php?err=arq";
        }

        // definindo url
        if (!$provisorio) {
            $urlArq = $this->PCH_URL_ARQ_RESUL_FINAL;
        } else {
            $urlArq = str_replace(AcompProcChamada::$TIPO_PDF, self::$ADENDO_ARQ_PROVISORIO . AcompProcChamada::$TIPO_PDF, $this->PCH_URL_ARQ_RESUL_FINAL);
        }

        // verificando caso de arquivo externo
        if (!file_exists("{$CFG->rpasta}/$urlArq")) {
            $proc = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            return "http://neaad.ufes.br/conteudo/edital-n%C2%BA-{$proc->getPRC_NR_EDITAL()}{$proc->getPRC_ANO_EDITAL()}";
        }

        return "{$CFG->rwww}/$urlArq";
    }

#   ----------------------------- FIM FUNÇÕES DE MANIPULAÇÃO DE ARQUIVOS -------------------------------------
#   
#   
#   
#   
#   ----------------------------------- FUNÇÕES DE VERIFICAÇÃO DE STATUS --------------------------------------

    public static function admiteAreaAtuacao($admiteArea) {
        return $admiteArea != NULL && $admiteArea == FLAG_BD_SIM;
    }

    public function admiteAreaAtuacaoObj() {
        return self::admiteAreaAtuacao($this->PCH_ADMITE_AREA);
    }

    public static function admiteReservaVaga($admiteReserva) {
        return $admiteReserva != NULL && $admiteReserva == FLAG_BD_SIM;
    }

    public function admiteReservaVagaObj() {
        return self::admiteReservaVaga($this->PCH_ADMITE_RESERVA_VAGA);
    }

    public function admitePoloObj() {
        return TipoCargo::idTipoAdmitePolo($this->TIC_ID_TIPO_CARGO);
    }

    public function isInscricaoMultipla() {
        return TipoCargo::idTipoAdmitePolo($this->TIC_ID_TIPO_CARGO) && !Util::vazioNulo($this->PCH_INSCRICAO_MULTIPLA) && $this->PCH_INSCRICAO_MULTIPLA == FLAG_BD_SIM;
    }

    /**
     * Função que informa se uma dada chamada tem opções de inscrição
     * 
     * @param ProcessoChamada $chamada
     * 
     * @return boolean Informa se tem ou não opção de inscrição
     * 
     */
    public static function temOpcaoInscricao($chamada) {
        return $chamada->admitePoloObj() || $chamada->admiteAreaAtuacaoObj() || $chamada->admiteReservaVagaObj();
    }

    public function isChamadaAtual() {
        return $this->PCH_CHAMADA_ATUAL != NULL && $this->PCH_CHAMADA_ATUAL == FLAG_BD_SIM;
    }

    public function isFinalizada() {
        return self::chamadaFinalizada($this->PCH_DT_FINALIZACAO);
    }

    public function isAtiva() {
        return self::isChamadaAtiva($this->PCH_CHAMADA_ATIVA);
    }

    public function isSolicitouAtivacao() {
        return !Util::vazioNulo($this->PCH_ATV_DT_SOLICITACAO);
    }

    public function isCalendarioAtrasado() {
        return !$this->isFinalizada() && dt_dataMenor(dt_getTimestampDtBR($this->getDtExecucaoProximaFaseChamada()), dt_getTimestampDtBR());
    }

    public function isEmPeriodoInscricao() {
        return $this->getIdFaseChamada() == self::$COD_FASE_INSCRICAO;
    }

    public function isAguardandoFechamentoAuto() {
        return $this->getIdFaseChamada() == self::$COD_FASE_RESUL_PUBLICADO;
    }

    public function isFechada() {
        return $this->getIdFaseChamada() == self::$COD_FASE_FECHADA;
    }

    public function isMostrarFaseAtual() {
        $idFaseChamada = $this->getIdFaseChamada();
        return $idFaseChamada != self::$COD_FASE_FECHADA;
    }

    public function isMostrarProxFase() {
        $idProxFaseChamada = $this->getIdProximaFaseChamada();
        return $idProxFaseChamada != self::$COD_FASE_INSCRICAO && $idProxFaseChamada != self::$COD_FASE_FINALIZADA;
    }

    public function saiuResultadoFinal() {
        return self::publicouResulFinal($this->PCH_DT_REG_RESUL_FINAL);
    }

    /**
     * 
     * @param boolean $restrito Diz se é para realizar uma análise restringindo inclusive nos casos de aguardando fechamento automático
     * @return boolean
     */
    public function permiteEdicao($restrito = FALSE) {
        return (!$restrito && !$this->isFinalizada()) || ($restrito && !$this->isFinalizada() && !$this->isAguardandoFechamentoAuto());
    }

    /**
     * 
     * @param Processo $processo
     */
    public static function permiteEditarCalendario($processo) {
        // Pode editar o processo
        return $processo->permiteEdicao(TRUE);
    }

    /**
     * 
     * @param Processo $processo
     */
    public static function permiteEditarConfiguracao($processo) {

        // Pode editar o processo
        return $processo->permiteEdicao(TRUE);
    }

    /**
     * 
     * @param Processo $processo
     */
    public static function permiteEditarMensagem($processo) {

        // Pode editar o processo
        return $processo->permiteEdicao();
    }

    public static function isChamadaAtiva($chamadaAtiva) {
        return $chamadaAtiva == FLAG_BD_SIM;
    }

    /**
     * 
     * @return boolean
     * @throws NegocioException
     */
    public function temCadastroReserva() {
        if ($this->temCadastroReserva === NULL) {
            // carregando...
            $macroCadReserva = buscarMacroConfProcPorProcEtapaTpCT($this->PRC_ID_PROCESSO, NULL, MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA);

            if (Util::vazioNulo($macroCadReserva)) {
                throw new NegocioException("Informação de opção por cadastro de reserva não informado!");
            }
            $this->temCadastroReserva = $macroCadReserva[0]->getIdObjMacro() != SemCadastroReserva::$ID_MACRO_SEM_CADASTRO_RESERVA;
        }
        return $this->temCadastroReserva;
    }

    public static function isChamadaDoProcesso($idChamada, $idProcesso) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Não tem chamada? Então não é do processo
            if (Util::vazioNulo($idChamada)) {
                return FALSE;
            }

            $sql = "select count(*) as cont
                from tb_pch_processo_chamada
                where PRC_ID_PROCESSO = '$idProcesso'
                and PCH_ID_CHAMADA = '$idChamada'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando 
            return ConexaoMysql::getResult("cont", $resp) > 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar se chamada pertence a processo.", $e);
        }
    }

    private static function chamadaFinalizada($dtFinalizacao) {
        return !Util::vazioNulo($dtFinalizacao) && dt_dataMenorIgual(dt_getTimestampDtBR($dtFinalizacao), dt_getTimestampDtBR());
    }

    private static function publicouResulFinal($dtRegResulFinal) {
        return $dtRegResulFinal != NULL;
    }

#   ----------------------------------- FIM FUNÇÕES DE VERIFICAÇÃO DE STATUS ------------------------------------

    public static function getIdInputVagas($idChamada, $idPolo = NULL, $idAreaAtu = NULL, $seqPoloArea = NULL) {
        $ret = "vaga$idChamada";

        // polo area é caso especial
        if ($seqPoloArea !== NULL) {
            return $ret . "seq$seqPoloArea";
        }

        if ($idPolo != NULL) {
            $ret .= "polo$idPolo";
        }
        if ($idAreaAtu != NULL) {
            $ret .= "areaAtu$idAreaAtu";
        }
        return $ret;
    }

    public static function idInputVagasAddReserva($idReservaVaga = NULL) {
        return $idReservaVaga == NULL ? "reserva0" : "reserva$idReservaVaga";
    }

    public static function getIdInputSelectPolo($idChamada, $seqPoloArea) {
        return "selectPolo{$idChamada}seq$seqPoloArea";
    }

    public static function getIdInputSelectAreaAtu($idChamada, $seqPoloArea) {
        return "selectAreaAtu{$idChamada}seq$seqPoloArea";
    }

    /**
     * Função que informa se é necessário criar versões com atualização dos dados da chamada
     * 
     * @param int $idChamada
     */
    public static function necessarioInfAtuEdital($idChamada) {
        try {
            // Nulo: Não há necessidade de registro
            if (Util::vazioNulo($idChamada)) {
                return FALSE;
            }

            $chamada = self::buscarChamadaPorId($idChamada);
            return $chamada->necessarioInfAtuEditalObj();
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar necessidade de comunicação da atualização do edital.", $e);
        }
    }

    public function necessarioInfAtuEditalObj() {
        // processo ainda não está visível
        if (dt_dataMaior(dt_getTimestampDtBR($this->PRC_DT_INICIO), dt_getTimestampDtBR())) {
            return FALSE;
        }
        $idFase = $this->getIdFaseChamada();
        // não está em construção ou finalizada
        return $idFase != self::$COD_FASE_EM_CONSTRUCAO && $idFase != self::$COD_FASE_FINALIZADA;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        PoloAreaChamada::addSqlRemoverPorProcesso($idProcesso, $vetSqls);
        PoloChamada::addSqlRemoverPorProcesso($idProcesso, $vetSqls);
        AreaAtuChamada::addSqlRemoverPorProcesso($idProcesso, $vetSqls);

        $vetSqls [] = "delete from tb_pch_processo_chamada where PRC_ID_PROCESSO = '$idProcesso'";
    }

    /**
     * 
     * @param int $idChamada
     * @param boolean $chamadaAtiva Flag que informa se a chamada está ativa ou não
     * @param string $dtAbertura Data na forma dd/mm/yyyy representando a data de abertura das inscrições
     * @param string $dtFechamento Data na forma dd/mm/yyyy representando a data de fechamento das inscrições
     * @param string $dtRegResulFinal Data de registro do resultado final na forma dd/mm/yyyy ou nulo, se não definido
     * @param string $dtFinalizacaoCham Data de finalização da chamada na forma dd/mm/yyyy ou nulo, se não definido
     * @return array Array na forma [idFase, dsFase, dtFinalizacaoFase, (idEtapaAtual)]
     * @throws NegocioException
     */
    public static function getFaseChamada($idChamada, $chamadaAtiva, $dtAbertura, $dtFechamento, $dtRegResulFinal, $dtFinalizacaoCham) {
        try {
            // fase em construção: chamada não está ativa
            if (!self::isChamadaAtiva($chamadaAtiva)) {
                return array(self::$COD_FASE_EM_CONSTRUCAO, self::$FASE_EM_CONSTRUCAO, NULL);
            }

            // Chamada está finalizada
            if (self::chamadaFinalizada($dtFinalizacaoCham)) {
                return array(self::$COD_FASE_FINALIZADA, self::$FASE_FINALIZADA, NULL);
            }

            // fechada: Data de abertura posterior a data atual
            if (dt_dataMaior(dt_getTimestampDtBR($dtAbertura), dt_getTimestampDtUS())) {
                return array(self::$COD_FASE_FECHADA, self::$FASE_FECHADA, $dtAbertura);
            }

            // inscrições: Está no período de inscrições
            if (dt_dataPertenceIntervalo(dt_getTimestampDtUS(), dt_getTimestampDtBR($dtAbertura), dt_getTimestampDtBR($dtFechamento))) {
                return array(self::$COD_FASE_INSCRICAO, self::$FASE_INSCRICAO, $dtFechamento);
            }

            // recuperando etapas de seleção 
            $etapasSel = EtapaSelProc::buscarEtapaPorChamada($idChamada);
            if ($etapasSel != NULL) {
                // loop nas etapas
                foreach ($etapasSel as $etapaSel) {
                    // processando resultado parcial: Etapa aberta
                    if ($etapaSel->isAberta()) {
                        return array(self::$COD_FASE_ETAPA_RESUL_PARCIAL, self::$FASE_ETAPA_RESUL_PARCIAL . " " . self::$PREPOSICAO_FASE_CHAMADA_DA . " " . $etapaSel->getNomeEtapa(), $etapaSel->getESP_DT_PREV_RESUL_ETAPA(), $etapaSel->getESP_NR_ETAPA_SEL());
                    }

                    // aguardando período de recurso: resultado já saiu, mas período de recurso é porterior
                    if ($etapaSel->isEmRecurso() && $etapaSel->isPeriodoRecursoPosterior()) {
                        return array(self::$COD_FASE_ETAPA_ESP_PER_RECURSO, self::$FASE_ETAPA_ESP_PER_RECURSO . " " . self::$PREPOSICAO_FASE_CHAMADA_DA . " " . $etapaSel->getNomeEtapa(), $etapaSel->getESP_DT_INI_RECURSO(), $etapaSel->getESP_NR_ETAPA_SEL());
                    }

                    // período de recurso: Etapa em período de recurso
                    if ($etapaSel->isEmPeriodoRecurso()) {
                        return array(self::$COD_FASE_ETAPA_PER_RECURSO, self::$FASE_ETAPA_PER_RECURSO . " " . self::$PREPOSICAO_FASE_CHAMADA_DA . " " . $etapaSel->getNomeEtapa(), $etapaSel->getESP_DT_FIM_RECURSO(), $etapaSel->getESP_NR_ETAPA_SEL());
                    }

                    //  processamento de recurso da etapa: Etapa em recurso
                    if ($etapaSel->isEmRecurso()) {
                        return array(self::$COD_FASE_ETAPA_PROC_RECURSO, self::$FASE_ETAPA_PROC_RECURSO . " " . self::$PREPOSICAO_FASE_CHAMADA_DA . " " . $etapaSel->getNomeEtapa(), $etapaSel->getESP_DT_PREV_RESUL_REC(), $etapaSel->getESP_NR_ETAPA_SEL());
                    }
                }
            }

            // Resultado final publicado
            if (self::publicouResulFinal($dtRegResulFinal)) {
                return array(self::$COD_FASE_RESUL_PUBLICADO, self::$FASE_RESUL_PUBLICADO, NULL);
            }

            // Dados inconsistentes: Fase indefinida
            return array(self::$COD_FASE_NAO_DEFINIDA, self::$FASE_NAO_DEFINIDA, NULL);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar fase atual da chamada.", $e);
        }
    }

    /**
     * 
     * @param int $idChamada
     * @param string $dtAbertura Data na forma dd/mm/yyyy
     * @param string $dtFechamento Data na forma dd/mm/yyyy
     * @param string $dtRegResulFinal Data de registro do resultado final na forma dd/mm/yyyy ou nulo, se não definido
     * @param string $dtFinalizacaoCham Data de finalização da chamada na forma dd/mm/yyyy ou nulo, se não definido
     * @return array Array na forma [idFase, dsFase, dtExecProxFase]
     * @throws NegocioException
     */
    public static function getProximaFaseChamada($idChamada, $dtAbertura, $dtFechamento, $dtRegResulFinal, $dtFinalizacaoCham) {
        try {
            // Resultado final publicado
            if (self::publicouResulFinal($dtRegResulFinal)) {

                // Próxima etapa é o resultado final
                if (!self::chamadaFinalizada($dtFinalizacaoCham)) {
                    return array(self::$COD_FASE_FINALIZADA, self::$FASECAN_FINALIZADA, $dtFinalizacaoCham);
                }
            }

            // Chamada já está finalizada
            if (self::chamadaFinalizada($dtFinalizacaoCham)) {
                return array(self::$COD_FASE_FINALIZADA, self::$FASE_FINALIZADA, $dtFinalizacaoCham);
            }


            $retornaProx = FALSE; // flag que informa se é preciso retornar a próxima fase
            //
            // fechada: Data de abertura posterior a data atual
            if (dt_dataMaior(dt_getTimestampDtBR($dtAbertura), dt_getTimestampDtUS())) {
                return array(self::$COD_FASE_INSCRICAO, self::$FASECAN_INSCRICAO, $dtAbertura); // Próxima fase é inscrição
            }

            // inscrições: Está no período de inscrições
            if (dt_dataPertenceIntervalo(dt_getTimestampDtUS(), dt_getTimestampDtBR($dtAbertura), dt_getTimestampDtBR($dtFechamento))) {
                $retornaProx = TRUE; // informar que é para retornar a próxima etapa
            }

            // recuperando etapas de seleção 
            $etapasSel = EtapaSelProc::buscarEtapaPorChamada($idChamada);
            if ($etapasSel != NULL) {
                // loop nas etapas
                $i = 0;
                $qtEtapa = count($etapasSel);
                foreach ($etapasSel as $etapaSel) {
                    // processando resultado parcial: Etapa aberta
                    if ($etapaSel->isAberta() || $retornaProx) {
                        return array(self::$COD_FASE_ETAPA_RESUL_PARCIAL, self::$FASECAN_ETAPA_RESUL_PARCIAL . " " . self::$PREPOSICAO_FASE_CHAMADA_DA . " " . $etapaSel->getNomeEtapa(), $etapaSel->getESP_DT_PREV_RESUL_ETAPA());
                    }

                    // aguardando período de recurso: resultado já saiu, mas período de recurso é porterior
                    if ($etapaSel->isEmRecurso() && $etapaSel->isPeriodoRecursoPosterior()) {
                        return array(self::$COD_FASE_ETAPA_PER_RECURSO, self::$FASECAN_ETAPA_PER_RECURSO . " " . self::$PREPOSICAO_FASE_CHAMADA_DA . " " . $etapaSel->getNomeEtapa(), $etapaSel->getESP_DT_INI_RECURSO()); // Próxima fase é período de recurso
                    }

                    // período de recurso: Etapa em período de recurso
                    if ($etapaSel->isEmPeriodoRecurso()) {
                        $retornaProx = TRUE; // retorna a próxima etapa
                    }

                    //  processamento de recurso da etapa: Etapa em recurso
                    if ($etapaSel->isEmRecurso() || $retornaProx) {
                        // Resultado final do processo
                        if ($i == $qtEtapa - 1) {
                            return array(self::$COD_FASE_ETAPA_PROC_RECURSO, self::$FASECAN_RESULTADO_FINAL, $etapaSel->getESP_DT_PREV_RESUL_REC());
                        } else {
                            // resultado pós recurso da etapa
                            return array(self::$COD_FASE_ETAPA_PROC_RECURSO, self::$FASECAN_ETAPA_PROC_RECURSO . " " . self::$PREPOSICAO_FASE_CHAMADA_DA . " " . $etapaSel->getNomeEtapa(), $etapaSel->getESP_DT_PREV_RESUL_REC());
                        }
                    }

                    $i++;
                }
            }

            // Dados inconsistentes: Fase indefinida
            return array(self::$COD_FASE_NAO_DEFINIDA, self::$FASE_NAO_DEFINIDA, NULL);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar próxima fase da chamada.", $e);
        }
    }

    public function getNrEtapaFaseChamada() {
        if ($this->idDsFaseChamada == NULL) {
            $this->idDsFaseChamada = self::getFaseChamada($this->PCH_ID_CHAMADA, $this->PCH_CHAMADA_ATIVA, $this->PCH_DT_ABERTURA, $this->PCH_DT_FECHAMENTO, $this->PCH_DT_REG_RESUL_FINAL, $this->PCH_DT_FINALIZACAO);
        }

        // tem número de etapa?
        if (isset($this->idDsFaseChamada[3])) {
            return $this->idDsFaseChamada[3];
        }

        // Fase não pertence a etapa
        return NULL;
    }

    public function getDtFinalizacaoFaseChamada() {
        if ($this->idDsFaseChamada == NULL) {
            $this->idDsFaseChamada = self::getFaseChamada($this->PCH_ID_CHAMADA, $this->PCH_CHAMADA_ATIVA, $this->PCH_DT_ABERTURA, $this->PCH_DT_FECHAMENTO, $this->PCH_DT_REG_RESUL_FINAL, $this->PCH_DT_FINALIZACAO);
        }
        return $this->idDsFaseChamada[2];
    }

    public function getDsFaseChamada() {
        if ($this->idDsFaseChamada == NULL) {
            $this->idDsFaseChamada = self::getFaseChamada($this->PCH_ID_CHAMADA, $this->PCH_CHAMADA_ATIVA, $this->PCH_DT_ABERTURA, $this->PCH_DT_FECHAMENTO, $this->PCH_DT_REG_RESUL_FINAL, $this->PCH_DT_FINALIZACAO);
        }
        return $this->idDsFaseChamada[1];
    }

    public function getIdFaseChamada() {
        if ($this->idDsFaseChamada == NULL) {
            $this->idDsFaseChamada = self::getFaseChamada($this->PCH_ID_CHAMADA, $this->PCH_CHAMADA_ATIVA, $this->PCH_DT_ABERTURA, $this->PCH_DT_FECHAMENTO, $this->PCH_DT_REG_RESUL_FINAL, $this->PCH_DT_FINALIZACAO);
        }
        return $this->idDsFaseChamada[0];
    }

    public function getDtExecucaoProximaFaseChamada() {
        if ($this->idDsProxFaseChamada == NULL) {
            $this->idDsProxFaseChamada = self::getProximaFaseChamada($this->PCH_ID_CHAMADA, $this->PCH_DT_ABERTURA, $this->PCH_DT_FECHAMENTO, $this->PCH_DT_REG_RESUL_FINAL, $this->PCH_DT_FINALIZACAO);
        }
        return $this->idDsProxFaseChamada[2];
    }

    public function getDsProximaFaseChamada() {
        if ($this->idDsProxFaseChamada == NULL) {
            $this->idDsProxFaseChamada = self::getProximaFaseChamada($this->PCH_ID_CHAMADA, $this->PCH_DT_ABERTURA, $this->PCH_DT_FECHAMENTO, $this->PCH_DT_REG_RESUL_FINAL, $this->PCH_DT_FINALIZACAO);
        }
        return $this->idDsProxFaseChamada[1];
    }

    public function getIdProximaFaseChamada() {
        if ($this->idDsProxFaseChamada == NULL) {
            $this->idDsProxFaseChamada = self::getProximaFaseChamada($this->PCH_ID_CHAMADA, $this->PCH_DT_ABERTURA, $this->PCH_DT_FECHAMENTO, $this->PCH_DT_REG_RESUL_FINAL, $this->PCH_DT_FINALIZACAO);
        }
        return $this->idDsProxFaseChamada[0];
    }

#   ---------------- FUNÇÕES QUE RETORNAM HTML's -----------------------------------------------------

    public function getHTMLFaseAtualFluxoAdmin() {
        global $CFG;

        if ($this->getIdFaseChamada() != ProcessoChamada::$COD_FASE_FINALIZADA && $this->getIdFaseChamada() != ProcessoChamada::$COD_FASE_EM_CONSTRUCAO) {
            $ret = "<p><b>Fase atual:</b> {$this->getDsFaseChamada()}";
            $dt = !Util::vazioNulo($this->getDtFinalizacaoFaseChamada()) ? " <i>até {$this->getDtFinalizacaoFaseChamada()}</i> " : "";

            // Chamada de standby, aguardando fechamento auto
            if ($this->isAguardandoFechamentoAuto()) {
                $dt = !Util::vazioNulo($this->getDtExecucaoProximaFaseChamada()) ? " <i>até {$this->getDtExecucaoProximaFaseChamada()}</i> " : "";
                $ret .= "$dt <a data-toggle='modal' data-target='#pergAltFimChamada' title='Alterar finalização automática'><i class='fa fa-edit'></i></a></p>";
                return $ret;
            }

            $ret .= "$dt <a title='Alterar calendário' href='$CFG->rwww/visao/chamada/alterarCalendarioChamada.php?idProcesso={$this->PRC_ID_PROCESSO}&idChamada={$this->PCH_ID_CHAMADA}'><i class='fa fa-edit'></i></a></p>";
            return $ret;
        }
    }

    public function getHTMLProximaFaseFluxoAdmin() {
        global $CFG;

        $ret = "<p><b>Próxima fase:</b> {$this->getDsProximaFaseChamada()} ";

        // Chamada de standby, aguardando fechamento auto
        if ($this->isAguardandoFechamentoAuto()) {
            $dt = !Util::vazioNulo($this->getDtExecucaoProximaFaseChamada()) ? " <i>em {$this->getDtExecucaoProximaFaseChamada()}</i> " : "";
            $ret .= "$dt <a data-toggle='modal' data-target='#pergAltFimChamada' title='Alterar finalização automática'><i class='fa fa-edit'></i></a></p>";
            return $ret;
        }

        // Demais casos
        if ($this->getIdProximaFaseChamada() != ProcessoChamada::$COD_FASE_FINALIZADA) {
            $dt = !Util::vazioNulo($this->getDtExecucaoProximaFaseChamada()) ? " <i>em {$this->getDtExecucaoProximaFaseChamada()}</i> " : "";
            $ret .= "$dt <a title='Alterar calendário' href='$CFG->rwww/visao/chamada/alterarCalendarioChamada.php?idProcesso={$this->PRC_ID_PROCESSO}&idChamada={$this->PCH_ID_CHAMADA}'><i class='fa fa-edit'></i></a></p>";
            return $ret;
        }
    }

    public function getHtmlCaixaResultadoAdmin() {
        // Ainda não saiu resultado?
        if (!$this->saiuResultadoFinal()) {
            return "<p><i class='fa fa-file-o'></i> Resultado: {$this->getHtmlData($this->PCH_DT_PREV_RESUL_FINAL)}</p>";
        }

        // preparando retorno de link do arquivo de edital
        $retorno = "<p><i class='fa fa-file-pdf-o'></i> <a target='_blank' href='{$this->getUrlArquivoResulFinal()}' title='Visualizar o resultado final'>Resultado final ";

        // atualização?
        $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($this->PCH_URL_ARQ_RESUL_FINAL, $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);
        if ($dtAtualizacao != NULL) {
            $retorno .= " (Atualizado em $dtAtualizacao)";
        } else {
            $retorno .= " (Publicado em {$this->getPCH_DT_REG_RESUL_FINAL(TRUE)})";
        }
        $retorno .= " <i class='fa fa-external-link'></i></a></p>";
        return $retorno;
    }

#   ---------------- FIM FUNÇÕES QUE RETORNAM HTML's -----------------------------------------------------

    /**
     * Esta função retorna uma matriz com a lista de itens do calendário. Cada
     * linha contém um array com os seguintes indices:
     * nmItem - Nome do Item de calendário
     * vlItem - Valor do Item do calendáio
     * status - Flag que informa se o item é referente ao passado (A), Presente (P) ou Futuro (F)
     * 
     * Índices exclusivos  para edição
     * itemDuplo - Informa se o item é duplo, ou seja, possui dois valores
     * vlItem1 - Primeira parte do valor do item.
     * vlItem2 - Segunda parte do valor do item, quando este é periodizado.
     * idInput1 - ID do primeiro input
     * idInput2 - ID do segundo input, se houver
     * editavel - Boolean indicando se o item pode ser editado ou não. 
     * obrigatorio - Informa se o item é obrigatório ou não
     * tipo - Campo de uso interno
     * idEtapaSel - Campo de uso interno, presente apenas em alguns casos
     * 
     * @param boolean $edicao Informa se a lista será usada para fins de edição. Por padrão, é falso.
     * @return array Matriz com a lista de itens do calendário. 
     */
    public function listaItensCalendario($edicao = FALSE) {
        // verificando se já está carregado
        if (!$edicao) {
            if ($this->listaCal !== NULL) {
                return $this->listaCal;
            }
        } else {
            if ($this->listaCalEdicao !== NULL) {
                return $this->listaCalEdicao;
            }
        }


        $ret = array();
        $listaFases = self::getListaOrdFasesChamada();

        $faseAtual = $this->getIdFaseChamada();
        $nrEtapaFaseAtual = $this->getNrEtapaFaseChamada();

        // variáveis para edição
        if ($edicao) {
            // buscando processo e validando permissao para edicao
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            $permiteEdicao = ProcessoChamada::permiteEditarCalendario($processo);

            // criando variável para referenciar o último índice da lista
            $ultimoIndice = NULL;
        }

        // inscrições
        $arrayInsc = array("nmItem" => "Período de Inscrição:", "vlItem" => $this->getDsPeriodoInscricao(), "status" => self::defineStatusCalendario($listaFases, $faseAtual, self::$COD_FASE_INSCRICAO, $nrEtapaFaseAtual));
        if ($edicao) {
            $arrayInsc["vlItem1"] = $this->PCH_DT_ABERTURA;
            $arrayInsc["vlItem2"] = $this->PCH_DT_FECHAMENTO;
            $arrayInsc["idInput1"] = "inputInscricao1";
            $arrayInsc["idInput2"] = "inputInscricao2";
            $arrayInsc["itemDuplo"] = TRUE;
            $arrayInsc["tipo"] = self::$CAL_TP_ITEM_INSCRICAO;
            $arrayInsc["obrigatorio"] = TRUE;
        }
        $ret [] = $arrayInsc;
        $ultimoIndice = 0;

        // recuperando etapas
        $etapasSel = EtapaSelProc::buscarEtapaPorChamada($this->PCH_ID_CHAMADA);
        if ($etapasSel != NULL) {
            // loop nas etapas
            $i = 0;
            $qtEtapa = count($etapasSel);
            foreach ($etapasSel as $etapaSel) {
                // resultado parcial
                $arrayResulParc = array("nmItem" => "Resultado Parcial da {$etapaSel->getNomeEtapa()}:", "vlItem" => $etapaSel->getESP_DT_PREV_RESUL_ETAPA(), "status" => self::defineStatusCalendario($listaFases, $faseAtual, self::$COD_FASE_ETAPA_RESUL_PARCIAL, $nrEtapaFaseAtual, $etapaSel->getESP_NR_ETAPA_SEL()));
                if ($edicao) {
                    $arrayResulParc["vlItem1"] = $arrayResulParc["vlItem"];
                    $arrayResulParc["idInput1"] = self::getIdInputCalResulParcial($etapaSel);
                    $arrayResulParc["itemDuplo"] = FALSE;
                    $arrayResulParc["tipo"] = self::$CAL_TP_ITEM_RESUL_PARC;
                    $arrayResulParc['obrigatorio'] = TRUE;
                    $arrayResulParc["idEtapaSel"] = $etapaSel->getESP_ID_ETAPA_SEL();
                    $arrayResulParc["objEtapaSel"] = $etapaSel;
                    $ret[$ultimoIndice]["editavel"] = self::defineStatusCalendarioEditavel($arrayResulParc["status"], $permiteEdicao, $ret[$ultimoIndice]["tipo"], isset($ret[$ultimoIndice]["objEtapaSel"]) ? $ret[$ultimoIndice]["objEtapaSel"] : NULL);
                }
                $ret [] = $arrayResulParc;
                $ultimoIndice++;



                // recursos
                $arrayRec = array("nmItem" => "Período para Recursos da {$etapaSel->getNomeEtapa()}:", "vlItem" => $etapaSel->getDsPeriodoRecurso(), "status" => self::defineStatusCalendario($listaFases, $faseAtual, self::$COD_FASE_ETAPA_PER_RECURSO, $nrEtapaFaseAtual, $etapaSel->getESP_NR_ETAPA_SEL()));
                if ($edicao) {
                    $arrayRec["vlItem1"] = $etapaSel->getESP_DT_INI_RECURSO();
                    $arrayRec["vlItem2"] = $etapaSel->getESP_DT_FIM_RECURSO();
                    $arrayRec["idInput1"] = "input1Recurso" . $etapaSel->getESP_ID_ETAPA_SEL();
                    $arrayRec["idInput2"] = "input2Recurso" . $etapaSel->getESP_ID_ETAPA_SEL();
                    $arrayRec["itemDuplo"] = TRUE;
                    $arrayRec["tipo"] = self::$CAL_TP_ITEM_RECURSO;
                    $arrayRec['obrigatorio'] = TRUE;
                    $arrayRec["idEtapaSel"] = $etapaSel->getESP_ID_ETAPA_SEL();
                    $arrayRec["objEtapaSel"] = $etapaSel;
                    $ret[$ultimoIndice]["editavel"] = self::defineStatusCalendarioEditavel($arrayRec["status"], $permiteEdicao, $ret[$ultimoIndice]["tipo"], isset($ret[$ultimoIndice]["objEtapaSel"]) ? $ret[$ultimoIndice]["objEtapaSel"] : NULL);
                }
                $ret [] = $arrayRec;
                $ultimoIndice++;

                if ($i == $qtEtapa - 1) {
                    // resultado final do processo
                    $arrayFinal = array("nmItem" => "Resultado Final:", "vlItem" => $etapaSel->getESP_DT_PREV_RESUL_REC(), "status" => self::defineStatusCalendario($listaFases, $faseAtual, self::$COD_FASE_ETAPA_PROC_RECURSO, $nrEtapaFaseAtual, $etapaSel->getESP_NR_ETAPA_SEL()));
                    if ($edicao) {
                        $arrayFinal["vlItem1"] = $arrayFinal["vlItem"];
                        $arrayFinal["idInput1"] = self::getIdInputCalResulFinal($etapaSel);
                        $arrayFinal["itemDuplo"] = FALSE;
                        $arrayFinal["tipo"] = self::$CAL_TP_ITEM_RESUL_FIN;
                        $arrayFinal['obrigatorio'] = TRUE;
                        $arrayFinal["idEtapaSel"] = $etapaSel->getESP_ID_ETAPA_SEL();
                        $arrayFinal["objEtapaSel"] = $etapaSel;
                        $ret[$ultimoIndice]["editavel"] = self::defineStatusCalendarioEditavel($arrayFinal["status"], $permiteEdicao, $ret[$ultimoIndice]["tipo"], isset($ret[$ultimoIndice]["objEtapaSel"]) ? $ret[$ultimoIndice]["objEtapaSel"] : NULL);
                    }
                    $ret [] = $arrayFinal;
                    $ultimoIndice++;
                } else {
                    // resultado final da etapa
                    $arrayFimEtapa = array("nmItem" => "Resultado Final da {$etapaSel->getNomeEtapa()}:", "vlItem" => $etapaSel->getESP_DT_PREV_RESUL_REC(), "status" => self::defineStatusCalendario($listaFases, $faseAtual, self::$COD_FASE_ETAPA_PROC_RECURSO, $nrEtapaFaseAtual, $etapaSel->getESP_NR_ETAPA_SEL()));
                    if ($edicao) {
                        $arrayFimEtapa["vlItem1"] = $arrayFimEtapa["vlItem"];
                        $arrayFimEtapa["idInput1"] = self::getIdInputCalResulFinal($etapaSel);
                        $arrayFimEtapa["itemDuplo"] = FALSE;
                        $arrayFimEtapa["tipo"] = self::$CAL_TP_ITEM_RESUL_FIN;
                        $arrayFimEtapa['obrigatorio'] = TRUE;
                        $arrayFimEtapa["idEtapaSel"] = $etapaSel->getESP_ID_ETAPA_SEL();
                        $arrayFimEtapa["objEtapaSel"] = $etapaSel;
                        $ret[$ultimoIndice]["editavel"] = self::defineStatusCalendarioEditavel($arrayFimEtapa["status"], $permiteEdicao, $ret[$ultimoIndice]["tipo"], isset($ret[$ultimoIndice]["objEtapaSel"]) ? $ret[$ultimoIndice]["objEtapaSel"] : NULL);
                    }
                    $ret [] = $arrayFimEtapa;
                    $ultimoIndice++;
                }

                $i++;
            }
        }

        // definindo status de edição do último item
        if ($edicao && $ultimoIndice !== NULL) {
            $ret[$ultimoIndice]["editavel"] = self::defineStatusCalendarioEditavel($ret[$ultimoIndice]["status"], $permiteEdicao, $ret[$ultimoIndice]["tipo"], isset($ret[$ultimoIndice]["objEtapaSel"]) ? $ret[$ultimoIndice]["objEtapaSel"] : NULL);
        }

        // salvando dados em campo específico
        if ($edicao) {
            $this->listaCalEdicao = $ret;
        } else {
            $this->listaCal = $ret;
        }

        // retornando dados
        return $ret;
    }

    /**
     * 
     * @param EtapaSelProc $etapaSel
     */
    public static function getIdInputCalResulParcial($etapaSel) {
        return "inputResulEtapa" . $etapaSel->getESP_ID_ETAPA_SEL();
    }

    /**
     * 
     * @param EtapaSelProc $etapaSel
     */
    public static function getIdInputCalResulFinal($etapaSel) {
        if (!$etapaSel->isUltimaEtapa()) {
            return "inputResulFinal";
        } else {
            return "inputResulFinEtapa" . $etapaSel->getESP_ID_ETAPA_SEL();
        }
    }

    /**
     * Esta função persiste os novos dados do calendário no banco de dados
     * 
     * @param array $vetDados Array na forma [idInput => valor, ...], onde idInput deve ser retirado da matriz obtida pela função 
     * listaItensCalendario.
     * @param boolean $fluxoChamada Informa se a procedência é de uma criação de chamada
     * @param string $textoInicial Texto a ser utilizado no documento de notificação de alteração
     * @param boolean $semAlteracao Informa se não houve alteração do calendário
     */
    public function atualizarCalendario($vetDados, $fluxoChamada, $textoInicial, &$semAlteracao) {
        $arquivo = NULL;
        try {
            // verificando permissão de edição
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            if (!ProcessoChamada::permiteEditarCalendario($processo)) {
                throw new NegocioException("Não é possível editar o calendário da chamada.");
            }

            //criando variável para sqls
            $arraySqls = array();

            // variável para validar se houve alguma modificação
            $teveModificacao = FALSE;

            // percorrendo lista de itens para processamento do calendário
            $listaCalendario = $this->listaItensCalendario(TRUE);

            // armazenando previsão do resultado final
            $dtPrevResulFinal = NULL;

            foreach ($listaCalendario as $item) {
                if ($item['editavel']) {
                    // validando preenchimento
                    $dadosIguais = self::validaPreenchimentoItemCal($item, $vetDados);

                    // dados diferentes? adicionar sqls de modificação
                    if (!$dadosIguais) {
                        $teveModificacao = TRUE;
                        $arraySqls [] = self::getSqlAlteracaoCalendario($this->PCH_ID_CHAMADA, $item, $vetDados);

                        // atualização de previsão
                        if ($item['tipo'] == self::$CAL_TP_ITEM_RESUL_FIN) {
                            $dtPrevResulFinal = $vetDados[$item['idInput1']];
                        }

                        // validação do início do período de inscrições
                        if ($item['tipo'] == self::$CAL_TP_ITEM_INSCRICAO) {
                            if (!$this->validaIniInscricaoCal($processo, $vetDados[$item['idInput1']])) {
                                throw new NegocioException("Data de início das inscrições inconsistente!");
                            }
                        }
                    }
                }
            }

            // não teve modificação? Lançar exceção
            if (!$teveModificacao && !$fluxoChamada) {
                // sem alteração do calendário
                $semAlteracao = TRUE;
                return;
            }

            // adicionando sql de alteração de previsão do resultado final
            if (!Util::vazioNulo($dtPrevResulFinal)) {
                $arraySqls [] = self::getSqlAlteracaoPrevResulFinal($this->PCH_ID_CHAMADA, $dtPrevResulFinal);
            }

            // processando atualização de dados
            $arquivo = AcompProcChamada::processaRetificacaoCalendario($processo, $this, $this->ARQS_getUrlIniArqAltCalendario($processo), $listaCalendario, $vetDados, $textoInicial, $arraySqls);

            // verificando necessidade de persistência
            if (!Util::vazioNulo($arraySqls)) {
                // persistindo no banco
                $conexao = NGUtil::getConexao();
                $conexao->execTransacaoArray($arraySqls);
            }
        } catch (NegocioException $n) {
            NGUtil::arq_excluirArquivoServidor($arquivo);
            throw $n;
        } catch (Exception $e) {
            NGUtil::arq_excluirArquivoServidor($arquivo);
            throw new NegocioException("Erro ao salvar calendário.", $e);
        }
    }

    /**
     * Esta função persiste a publicação de um resultado no banco de dados
     * 
     * Esta função realiza todas as validações necessárias à publicação, identificando o tipo de 
     * publicação a ser realizado.
     * 
     * @param int $idEtapaSel ID da etapa de seleção a ser analisada
     * @param array $vetDados Array na forma [idInput => valor, ...], onde idInput deve ser retirado da matriz obtida pela função 
     * listaItensCalendario.
     * @param string $textoInicial Texto inicial da notificação de alteração do calendário, se houver
     * @param boolean $forcarFinalizacao Informa se é para forçar a finalização da chamada
     * @param string $dtFinalizacao Data de finalização da chamada
     * @param boolean $arqExterno Informa se não é para gerar o arquivo de resultado automaticamente, pois será utilizado um arquivo externo
     */
    public function publicarResultado($idEtapaSel, $vetDados, $textoInicial, $forcarFinalizacao, $dtFinalizacao, $arqExterno) {
        $resulPublicado = FALSE; // flag que informa se o resultado foi de fato publicado.
        $arquivo = NULL;
        try {
            // verificando permissão de publicação
            // recuperando etapa vigente
            $etapaVigente = buscarEtapaVigenteCT($this->PCH_ID_CHAMADA, $idEtapaSel);
            $valPublicacao = EtapaSelProc::validarPublicacaoResulPendente($this, $etapaVigente);
            if (!$valPublicacao['val']) {
                throw new NegocioException($valPublicacao['msg']);
            }

            // Tudo Ok, proseguir com publicação
            // 
            // recuperando dados para processamento
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            // 
            // 
            //criando variável para sqls
            $arraySqls = array();

            // variável para validar se houve alguma modificação no calendário
            $teveModificacaoCal = FALSE;

            // percorrendo lista de itens para processamento do calendário
            $listaCalendario = $this->listaItensCalendario(TRUE);
            $etapaVigente->removerItensIrrelevantesCalPubResul($this, $listaCalendario);

            if ($etapaVigente->mostrarCalendarioPubResultado($this, $listaCalendario)) {
                // armazenando previsão do resultado final
                $dtPrevResulFinal = NULL;

                foreach ($listaCalendario as $item) {
                    if ($item['editavel']) {
                        // validando preenchimento
                        $dadosIguais = self::validaPreenchimentoItemCal($item, $vetDados);

                        // dados diferentes? adicionar sqls de modificação
                        if (!$dadosIguais) {
                            $teveModificacaoCal = TRUE;
                            $arraySqls [] = self::getSqlAlteracaoCalendario($this->PCH_ID_CHAMADA, $item, $vetDados);

                            // atualização de previsão
                            if ($item['tipo'] == self::$CAL_TP_ITEM_RESUL_FIN) {
                                $dtPrevResulFinal = $vetDados[$item['idInput1']];
                            }

                            // validação do início do período de inscrições
                            if ($item['tipo'] == self::$CAL_TP_ITEM_INSCRICAO) {
                                if (!$this->validaIniInscricaoCal($processo, $vetDados[$item['idInput1']])) {
                                    throw new NegocioException("Data de início das inscrições inconsistente!");
                                }
                            }
                        }
                    }
                }

                // adicionando sql de alteração de previsão do resultado final
                if (!Util::vazioNulo($dtPrevResulFinal)) {
                    $arraySqls [] = self::getSqlAlteracaoPrevResulFinal($this->PCH_ID_CHAMADA, $dtPrevResulFinal);
                }
            }
            // Tratando da publicação em si!
            // 
            // 
            // adicionando sqls de publicação relacionados a etapa de seleção
            $teveModificacaoCal = $etapaVigente->adicionaSqlsPublicacaoResultado($processo, $this, $arraySqls, $vetDados, $forcarFinalizacao, $dtFinalizacao, $arqExterno) || $teveModificacaoCal;

            // Teve modificação do calendário? processando...
            $teveModificacaoCal = $teveModificacaoCal && $etapaVigente->temCandidatosInscritos($this);

            if ($teveModificacaoCal) {
                // processando atualização de dados
                $arquivo = AcompProcChamada::processaRetificacaoCalendario($processo, $this, $this->ARQS_getUrlIniArqAltCalendario($processo), $this->listaItensCalendario(TRUE), $vetDados, $textoInicial, $arraySqls);
            }

//            NGUtil::imprimeVetorDepuracao($arraySqls);
//            exit;
//    
//exit;                    
            // persistindo no banco
            $conexao = NGUtil::getConexao();
            $conexao->execTransacaoArray($arraySqls);
            $resulPublicado = TRUE;
        } catch (NegocioException $n) {
            NGUtil::arq_excluirArquivoServidor($arquivo);
            throw $n;
        } catch (Exception $e) {
            NGUtil::arq_excluirArquivoServidor($arquivo);
            throw new NegocioException("Erro ao publicar resultado do edital.", $e);
        }

        // disparando envio de emails para inscritos
        if ($resulPublicado) {
            try {
                $listaEmails = InscricaoProcesso::buscarEmailInscritosAtualizacao($processo->getPRC_ID_PROCESSO(), $this->PCH_ID_CHAMADA);

                // tem gente inscrito? Então montando mensagem...
                if ($listaEmails != NULL) {
                    // definindo resultado publicado
                    $pubPendente = $etapaVigente->getResultadoPendente();
                    $artigo = ($pubPendente[0] == EtapaSelProc::$PENDENTE_RET_RESUL_PARCIAL || $pubPendente[0] == EtapaSelProc::$PENDENTE_RET_RESUL_POS_REC) ? "a" : "o";

                    // montando mensagem
                    $assunto = "Saiu um resultado do PS {$processo->getDsEditalCompleta()}";
                    $mensagem = "Foi publicado $artigo <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$pubPendente[1]}</b></span> do edital <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$processo->getDsEditalCompleta()} - {$this->getPCH_DS_CHAMADA(TRUE)}</b></span>, que você está participando.";
                    $mensagem .= "<br/><br/>Você pode consultar suas inscrições acessando o sistema e clicando no menu Editais, acima a direita, e selecionando a opção Minhas inscrições.";

                    // percorrendo lista de emails
                    foreach ($listaEmails as $email => $nome) {
                        $temp = "Olá, $nome.<br/><br/>" . $mensagem;

//                        print_r("$assunto<br/>$temp<br/><br/>");
//                        
                        // disparando email
                        enviaEmail($email, $assunto, $temp, NULL, TRUE);
                    }
                }
            } catch (Exception $ex) {
                // registrando no log
                error_log($ex->getMessage());
                // Nada a fazer: Apenas aguardar que o administrador resolva o problema, através da análise do log
            }
        }
    }

    public function alterarFimChamada($dtFinalizacao, $finalizarAgora, $finalizarEdital) {
        try {

            // definindo array de sqls
            $arrayCmds = array();

            // recuperando SQL
            $dtFim = $finalizarAgora ? dt_getDataEmStr("d/m/Y") : $dtFinalizacao;
            $arrayCmds [] = self::getSqlFinalizacaoChamada($this->PCH_ID_CHAMADA, $dtFim);

            // tbm tem que finalizar edital
            if ($finalizarAgora && $finalizarEdital) {
                Processo::addSqlFinalizacaoEdital($arrayCmds, $this->PRC_ID_PROCESSO, NULL, $dtFim);
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao alterar finalização do chamada.", $e);
        }
    }

    /**
     * 
     * @param int $nrMaxOpcaoPolo
     * @param array $idPolos
     * @param array $idAreasAtu
     * @param array $idReservaVagas
     * @param boolean $fluxoChamada Informa se a atualização está vindo de uma criação de chamada
     * @param boolean $semAlteracao Informa se não houve alteração dos dados
     * 
     * @return boolean Informa se é necessário atualizar a quantidade de vagas
     * 
     * @throws NegocioException
     */
    public function salvarConfChamadaP1($nrMaxOpcaoPolo, $idPolos, $idAreasAtu, $idReservaVagas, $fluxoChamada, &$semAlteracao) {
        try {
            // verificando permissão de edição
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            if (!ProcessoChamada::permiteEditarConfiguracao($processo)) {
                throw new NegocioException("Não é possível editar as configurações da chamada.");
            }

            // variável para validar se houve alguma modificação
            $teveModificacao = FALSE;
            $teveModificacaoP2 = FALSE; // teve modificação que precisa de parte 2
            //


            // Tratando caso de polos
            if ($this->admitePoloObj()) {
                // tratando número máx opção polo
                if ($nrMaxOpcaoPolo != $this->PCH_NR_MAX_OPCAO_POLO) {
                    $teveModificacao = TRUE;

                    // acertando tamanho máximo de opções de polo
                    $nrMaxOpcaoPolo = min(array(count($idPolos), $nrMaxOpcaoPolo));

                    //recuperando sql
                    $sql = $this->getSqlAtuNrMaxOpcaoPolo($nrMaxOpcaoPolo);
                }

                // Modificações com necessidade de parte 2
                // modificou polo
                $teveModificacaoP2 = PoloChamada::teveModificacaoPoloChamada($this->PCH_ID_CHAMADA, $idPolos);
            }


            // ainda não teve modificação de P2? Então verificar outras modificações
            if (!$teveModificacaoP2) {
                // modificou área
                $teveModificacaoP2 = AreaAtuChamada::teveModificacaoAreaAtuChamada($this->PCH_ID_CHAMADA, $idAreasAtu);
                if (!$teveModificacaoP2) {
                    // modificação reserva de vaga
                    $teveModificacaoP2 = ReservaVagaChamada::teveModificacaoReservaVagasChamada($this->PCH_ID_CHAMADA, $idReservaVagas);
                }
            }

            // não teve modificação? Lançar exceção
            if (!$teveModificacao && !$teveModificacaoP2 && !$fluxoChamada) {
                $semAlteracao = TRUE; // marcando que não houve alteração
            }

            // apenas foi modificado coisas que não precisam de parte 2
            if ($teveModificacao && !$teveModificacaoP2) {
                $arraySqls = array($sql);

                // processando atualização
                // @todo Alterar aqui geração do PDF de retificação
                AcompProcChamada::processaRetificacaoVagas($processo, $this, $this->ARQS_getUrlIniArqAltVagas($processo), NULL, $arraySqls);

                // adicionando reset de classificação
                $arraySqls [] = EtapaSelProc::getStrSqlClassifPenPorChamada($this->PCH_ID_CHAMADA);

                // persistindo no banco
                $conexao = NGUtil::getConexao();
                $conexao->execTransacaoArray($arraySqls);
            }

            // teve modificação com necessidade da parte 2, então salvar dados na sessão para a etapa 2
            if ($teveModificacaoP2) {
                sessaoDados_setDados("idChamada", $this->PCH_ID_CHAMADA);
                sessaoDados_setDados("idPolos", implode(",", $idPolos));
                sessaoDados_setDados("idAreasAtu", implode(",", $idAreasAtu));
                sessaoDados_setDados("idReservaVagas", implode(",", $idReservaVagas));
                sessaoDados_setDados("nrMaxOpcaoPolo", $nrMaxOpcaoPolo);
            }

            // para onde vamos? parte 2 (se parte ou fluxo chamada) ou finalização
            return $teveModificacaoP2 || $fluxoChamada;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao salvar configurações da chamada.", $e);
        }
    }

    /**
     * Esta função salva as mensagens da chamada no BD
     * 
     * O objeto deve estar com as mensagens atualizadas antes de chamar esta função
     * 
     */
    public function salvarMensagensChamada() {
        try {

            $conexao = NGUtil::getConexao();

            // preparando dados
            $this->PCH_TXT_COMP_INSCRICAO = NGUtil::trataCampoStrParaBD($this->PCH_TXT_COMP_INSCRICAO);

            $sql = "update tb_pch_processo_chamada set PCH_TXT_COMP_INSCRICAO = $this->PCH_TXT_COMP_INSCRICAO where PCH_ID_CHAMADA = '$this->PCH_ID_CHAMADA'";

            // persistindo no BD
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao salvar mensagens da chamada.", $e);
        }
    }

    private function validarAtivacaoChamada() {
        // realizando as devidas validações
        // Caso de rechamada
        if ($this->isAtiva()) {
            throw new NegocioException("Esta chamada já está ativada!");
        }

        $vetErros = array();


        // calendário
        if (!$this->validaAtivacaoCalendario()) {
            $vetErros [] = " - O calendário da chamada está vencido. Atualize-o para ativar a chamada.";
        }

        // configurações
        if (!$this->validaConfiguracaoChamada()) {
            $vetErros [] = " - Os polos participantes da chamada não foram configurados. Configure-os.";
        }

        // vagas
        if (!$this->validaVagasChamada()) {
            $vetErros [] = " - A chamada não tem vagas e não foi informado que o edital terá cadastro de reserva.";
        }

        // tratando erros
        if (!Util::vazioNulo($vetErros)) {
            // teve erros
            return [FALSE, implode("\n", $vetErros)];
        }

        // Tudo Ok
        return [TRUE];
    }

    /**
     * Função que ativa uma dada chamada 
     * 
     * @param Processo $processo
     * @return array Array na forma [situacao, msgErro] informando o status da ativação em situacao e, em caso de erro
     * informa as dependências em msgErro.
     *  
     * @throws NegocioException
     */
    public function ativarChamada($processo) {
        try {

            // realizando as devidas validações
            $val = $this->validarAtivacaoChamada();
            if (!$val[0]) {
                return $val;
            }

            // Tudo Ok. Procedendo com a ativação...
            $conexao = NGUtil::getConexao();

            // flags
            $flagAtiva = FLAG_BD_SIM;
            $flagChamAntiga = NGUtil::trataCampoStrParaBD(NGUtil::getFlagNao());
            $flagChamAtual = NGUtil::trataCampoStrParaBD(NGUtil::getFlagSim());
            $idResp = getIdUsuarioLogado();

            $arrayCmds = array();

            // recuperando sql de desabilitar chamada anterior, se existir
            $arrayCmds [] = "update tb_pch_processo_chamada set PCH_CHAMADA_ATUAL = $flagChamAntiga where PRC_ID_PROCESSO = {$processo->getPRC_ID_PROCESSO()} and PCH_ID_CHAMADA != '$this->PCH_ID_CHAMADA'";

            // Sql de ativação da chamada
            $arrayCmds [] = "update tb_pch_processo_chamada
                            set PCH_CHAMADA_ATIVA = '$flagAtiva',
                            PCH_CHAMADA_ATUAL = $flagChamAtual,
                            PCH_ATV_USU_RESPONSAVEL = '$idResp',
                            PCH_ATV_DT_ATIVACAO = now()
                            where PCH_ID_CHAMADA = '$this->PCH_ID_CHAMADA'";

            // Abrindo a primeira etapa de seleção do processo
            $arrayCmds [] = EtapaSelProc::getStrSqlAbrirPrimeiraEtapaCham($this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);

            // recuperando sql de criação da notícia do edital
            $arrayCmds = array_merge($arrayCmds, Noticia::getArraySqlCriarNoticiaEdital($processo, $this));

            // recuperando sql para criação do acompanhamento
            AcompProcChamada::processaAtivacaoChamada($processo, $this, $idResp, $arrayCmds);

            // persistindo no BD
            $conexao->execTransacaoArray($arrayCmds);

            return [TRUE, ""];
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao ativar chamada.", $e);
        }
    }

    /**
     * Função que solicita a ativação de uma dada chamada 
     * 
     * @param Processo $processo
     * @return array Array na forma [situacao, msgErro] informando o status da solicitação em situacao e, em caso de erro,
     * informa as dependências em msgErro.
     *  
     * @throws NegocioException
     */
    public function solicitarAtivacao($processo) {
        $usuResp = getIdUsuarioLogado();
        try {
            // realizando as devidas validações
            $val = $this->validarAtivacaoChamada();
            if (!$val[0]) {
                return $val;
            }

            // verificando rechamada
            if ($this->isSolicitouAtivacao()) {
                return array(FALSE, "Solicitação de ativação já solicitada!");
            }

            // Tudo Ok. Procedendo com a solicitação...
            $conexao = NGUtil::getConexao();

            // criando sql de solicitação
            $sql = "update tb_pch_processo_chamada set PCH_ATV_DT_SOLICITACAO = now(), PCH_ATV_USU_SOLICITANTE = '$usuResp'";

            // persistindo no BD
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao solicitar ativação da chamada.", $e);
        }

        // enviando email aos administradores
        Usuario::enviarNotSolAtivacaoChamAdmin($processo, $this, $usuResp);

        // retornando tudo certo
        return [TRUE, ""];
    }

#   ----------------------- FUNÇÕES DE VALIDAÇÃO ----------------------------------------------------

    /**
     * Função que verifica se o calendário da chamada está apto para a ativação da mesma
     * 
     * @return boolean
     */
    private function validaAtivacaoCalendario() {
        // período de inscrição ainda está valendo
        return dt_dataMaiorIgual(dt_getTimestampDtBR($this->PCH_DT_FECHAMENTO), dt_getTimestampDtBR());
    }

    /**
     * Função que verifica se a data de início das inscrições é coerente com a data de início do processo
     * 
     * @param Processo $processo 
     * @param string $dtIniInscricao Data de início das inscrições
     *
     * @return boolean
     */
    public function validaIniInscricaoCal($processo, $dtIniInscricao) {
        return dt_dataMaiorIgual(dt_getTimestampDtBR($dtIniInscricao), dt_getTimestampDtBR($processo->getPRC_DT_INICIO()));
    }

    /**
     * Função que verifica se a configuração da chamada está OK
     * 
     * 
     * @param Processo $processo
     * @return boolean
     */
    private function validaConfiguracaoChamada() {
        if ($this->admitePoloObj()) {
            return PoloChamada::contaPoloPorChamada($this->PCH_ID_CHAMADA, PoloChamada::getFlagPoloAtivo()) > 0;
        }

        // Tudo Ok
        return TRUE;
    }

    /**
     *  Função que verifica se as vagas da chamada está OK
     * 
     * @return boolean
     */
    private function validaVagasChamada() {
        if (Util::vazioNulo($this->PCH_QT_VAGAS) || $this->PCH_QT_VAGAS == 0) {
            // verificando se tem cadastro de reserva
            $macrosCadReserva = MacroConfProc::buscarMacroConfProcPorProcEtapaTp($this->PRC_ID_PROCESSO, NULL, MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA);

            if (Util::vazioNulo($macrosCadReserva)) {
                return FALSE;
            }

            // percorrendo macros e verificando
            foreach ($macrosCadReserva as $macroCadReserva) {
                if ($macroCadReserva->getIdObjMacro() == SemCadastroReserva::$ID_MACRO_SEM_CADASTRO_RESERVA) {
                    return FALSE;
                }
            }
        }

        // Tudo Ok
        return TRUE;
    }

    /**
     * 
     * @param array $item
     * @param array $vetDados
     * @param boolean $lancarExcecao Informa se deve lançar exceção, caso ocorra algum problema
     * @return boolean Retorna se os dados preenchidos são iguais aos dados antigos.
     * @throws NegocioException
     */
    public static function validaPreenchimentoItemCal($item, $vetDados, $lancarExcecao = TRUE) {
        // caso de não preenchimento
        if (!isset($vetDados[$item['idInput1']]) || ($item['itemDuplo'] && !isset($vetDados[$item['idInput2']]))) {
            $msg = "Dados de calendário incompleto!";
            if ($lancarExcecao) {
                throw new NegocioException($msg);
            } else {
                return $msg;
            }
        }

        // verificando igualdade: parte 1
        $dadosIguais = $item['vlItem1'] == $vetDados[$item['idInput1']];

        // verificando igualdade: parte 2
        if ($item['itemDuplo']) {
            $dadosIguais = $dadosIguais && $item['vlItem2'] == $vetDados[$item['idInput2']];
        }

        return $dadosIguais;
    }

    /**
     * Verifica se o processo esta no periodo de inscriçao
     * @param int $idProcesso
     * @return boolean
     * @throws NegocioException
     */
    public static function validaPeriodoInscPorProcesso($idProcesso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Flags
            $flagUltChamada = FLAG_BD_SIM;
            $flagChamadaAtiva = FLAG_BD_SIM;

            $sql = "select UNIX_TIMESTAMP(`PCH_DT_ABERTURA`) as PCH_DT_ABERTURA,
                UNIX_TIMESTAMP(`PCH_DT_FECHAMENTO`) as PCH_DT_FECHAMENTO
                    from tb_pch_processo_chamada
                    where PRC_ID_PROCESSO = '$idProcesso'
                        and PCH_CHAMADA_ATUAL = '$flagUltChamada'
                        and PCH_CHAMADA_ATIVA = '$flagChamadaAtiva'
                    and PCH_DT_ABERTURA IS NOT NULL and PCH_DT_FECHAMENTO IS NOT NULL";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // verificando existencia
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                // nao ha inscriçao aberta
                return FALSE;
            }

            // verificando campos
            $linha = ConexaoMysql::getLinha($resp);
            $dtAbertura = $linha["PCH_DT_ABERTURA"];
            $dtFechamento = $linha["PCH_DT_FECHAMENTO"];
            $dtAtual = dt_getTimestampDtUS();

//            print_r($dtAbertura . " " . $dtFechamento . " atual: " . $dtAtual);
            // verificando se esta dentro do periodo de inscriçao
            return dt_dataPertenceIntervalo($dtAtual, $dtAbertura, $dtFechamento);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar período de inscrição.", $e);
        }
    }

    /**
     * Verifica se a chamada está no periodo de inscriçao. 
     * 
     * @param int $idChamada
     * @param boolean $retornoCompleto Informa se é para ser utilizado o retorno completo. Padrão: False
     * 
     * @return [boolean, char] - Se $retornoCompleto, então é retornado um vetor informando se a chamada está dentro do período de inscrição e, caso negativo, 
     * se o evento é passado ou futuro; Senão, é retornado apnas um boolean informando se está dentro do período de inscrição. Utilize as constantes de evento
     * EVENTO_PASSADO, EVENTO_PRESENTE e EVENTO_FUTURO. 
     * 
     * @throws NegocioException
     */
    public static function validaPeriodoInscPorChamada($idChamada, $retornoCompleto = FALSE) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select UNIX_TIMESTAMP(`PCH_DT_ABERTURA`) as PCH_DT_ABERTURA,
                UNIX_TIMESTAMP(`PCH_DT_FECHAMENTO`) as PCH_DT_FECHAMENTO
                    from tb_pch_processo_chamada
                    where PCH_ID_CHAMADA = '$idChamada'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // verificando existencia
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                // nao ha inscriçao aberta
                return FALSE;
            }

            // verificando campos
            $linha = ConexaoMysql::getLinha($resp);
            $dtAbertura = $linha["PCH_DT_ABERTURA"];
            $dtFechamento = $linha["PCH_DT_FECHAMENTO"];
            $dtAtual = dt_getTimestampDtUS();

//            print_r($dtAbertura . " " . $dtFechamento . " atual: " . $dtAtual);
//            
            // verificando se esta dentro do periodo de inscriçao
            $dentroPeriodo = dt_dataPertenceIntervalo($dtAtual, $dtAbertura, $dtFechamento);

            if (!$retornoCompleto) {
                // retorno parcial
                return $dentroPeriodo;
            } else {

                // providenciando status
                if (!$dentroPeriodo) {
                    $status = dt_dataMaior($dtAbertura, $dtAtual) ? self::$EVENTO_FUTURO : self::$EVENTO_PASSADO;
                } else {
                    $status = self::$EVENTO_PRESENTE;
                }

                return array($dentroPeriodo, $status);
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar período de inscrição da chamada.", $e);
        }
    }

#   ----------------------- FIM FUNÇÕES DE VALIDAÇÃO -------------------------------------------------

    /**
     * 
     * @param boolean $passo2 Diz se é o passo 2 de uma configuração 
     * @param string $idPolos String com id dos polos selecionados para a chamada separados por ','
     * @param string $idAreasAtu String com id das áreas selecionadas para a chamada separadas por ','
     * @param string $idReservaVagas Array com id das reservas de vaga selecionadas para a chamada separadas por ','
     * @param array $arrayDadosAtualizar Array na forma: "idInput => vlInput";
     * @param int $nrMaxOpcaoPolo Apenas quando é proveniente de passo 2
     * @param boolean $fluxoChamada Informa se o fluxo é proveniente da criação de uma chamada
     * @param boolean $semAlteracao Informa se não houve alteração dos dados
     * 
     * @throws NegocioException
     */
    public function salvarConfChamadaVagasConfP2($passo2, $idPolos, $idAreasAtu, $idReservaVagas, $arrayDadosAtualizar, $nrMaxOpcaoPolo = NULL, $fluxoChamada = FALSE, &$semAlteracao = FALSE) {
        try {
            // verificando permissão de edição
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            if (!ProcessoChamada::permiteEditarConfiguracao($processo)) {
                throw new NegocioException("Não é possível editar as configurações da chamada.");
            }

            // Houve alteração de dados?
            $hashAltVagas = md5(http_build_query($arrayDadosAtualizar));
            if (!$passo2 && $hashAltVagas == $this->PCH_HASH_ALTERACAO_VAGAS && !$fluxoChamada) {
                $semAlteracao = TRUE; // informando que não houve alteração
            }

            // convertendo listas
            $listaPolos = $idPolos != NULL ? explode(",", $idPolos) : NULL;
            $listaAreasAtu = $idAreasAtu != NULL ? explode(",", $idAreasAtu) : NULL;
            $listaReservaVagas = $idReservaVagas != NULL ? explode(",", $idReservaVagas) : NULL;

            // Verificar se tem que remover conf. Anterior: Passo 2 ou é uma atualização de Polo-Área
            $removeConfAnt = $passo2 || ($idPolos != NULL && $idAreasAtu != NULL);


            $arrayCmds = array(); // array de comandos a ser executados no BD
            //
            //
            //
            // removendo possível configuração anterior
            $this->sqlsRemoveConfAnterior($removeConfAnt, $processo, $idPolos, $idAreasAtu, $idReservaVagas, $arrayCmds);


            // sem polo e sem area
            if ($idPolos == NULL && $idAreasAtu == NULL) {
                // processando
                $this->_processaConf_SemPoloSemArea($arrayDadosAtualizar, $listaReservaVagas, $arrayCmds);

                // apenas polo
            } elseif ($listaPolos != NULL && $listaAreasAtu == NULL) {
                // processando
                $this->_processaConf_ComPoloSemArea($removeConfAnt, $arrayDadosAtualizar, $listaReservaVagas, $listaPolos, $arrayCmds);

                // apenas área
            } elseif ($listaPolos == NULL && $listaAreasAtu != NULL) {
                // processando
                $this->_processaConf_SemPoloComArea($removeConfAnt, $arrayDadosAtualizar, $listaReservaVagas, $listaAreasAtu, $arrayCmds);

                // area e polo
            } elseif ($listaPolos != NULL && $listaAreasAtu != NULL) {
                // processando
                $this->_processaConf_ComPoloComArea($removeConfAnt, $arrayDadosAtualizar, $listaReservaVagas, $listaPolos, $listaAreasAtu, $arrayCmds);
            }


            // adicionando sql de atualização de nrMaxPolo
            if ($passo2 && $this->admitePoloObj() && !Util::vazioNulo($nrMaxOpcaoPolo)) {
                $arrayCmds [] = $this->getSqlAtuNrMaxOpcaoPolo($nrMaxOpcaoPolo);
            }

            // Atualizando hash de vagas
            $arrayCmds [] = $this->getSqlAtuHashVagas($hashAltVagas);

            // processando atualização
            // @todo Alterar aqui geração do PDF de retificação
            AcompProcChamada::processaRetificacaoVagas($processo, $this, $this->ARQS_getUrlIniArqAltVagas($processo), NULL, $arrayCmds);

            // adicionando reset de classificação
            $arrayCmds [] = EtapaSelProc::getStrSqlClassifPenPorChamada($this->PCH_ID_CHAMADA);

//            foreach ($arrayCmds as $value) {
//                print_r($value);
//                echo ";<br/><br/>";
//            }
//            exit;
//            
            // persistindo no BD
            $conexao = NGUtil::getConexao();
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao salvar configurações / vagas da chamada.", $e);
        }
    }

    /**
     * Função de processamento de vagas por caso.
     * ATENÇÃO: Para uma melhor clareza do que está sendo feito, esta função pode repetir código das funções agregadas.
     * 
     * @param array $arrayDados Array na forma: "idInput => vlInput";
     * @param array $listaReservaVagas Array com id das reservas de vaga selecionadas para a chamada
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    private function _processaConf_SemPoloSemArea($arrayDados, $listaReservaVagas, &$arrayRet) {
        // sem reserva de vagas
        if ($listaReservaVagas == NULL) {
            // Apenas atualizando quantidade de vagas
            $arrayRet [] = self::getSqlAtualizaQtVagasChamada($this->PCH_ID_CHAMADA, $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA)]);
        } else {
            // com reserva de vagas
            $totalVagas = 0;

            // percorrendo reserva de vagas e processando
            foreach ($listaReservaVagas as $idReserva) {
                $qtVagas = $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA) . ProcessoChamada::idInputVagasAddReserva($idReserva)];
                $totalVagas += $qtVagas;

                ReservaVagaChamada::processaAtualizacaoVagas($this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA, $qtVagas, $idReserva, $arrayRet);
            }
            // somando quantidade de vagas do público geral
            $totalVagas += $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA) . ProcessoChamada::idInputVagasAddReserva(NULL)];

            // Atualizando quantidade de vagas
            $arrayRet [] = self::getSqlAtualizaQtVagasChamada($this->PCH_ID_CHAMADA, $totalVagas);
        }
    }

    /**
     * 
     * Função de processamento de vagas por caso.
     * ATENÇÃO: Para uma melhor clareza do que está sendo feito, esta função pode repetir código das funções agregadas.
     * 
     * 
     * @param boolean $passo2 Diz se a atualização é proveniente do passo 2 de uma configuração
     * @param array $arrayDados Array na forma: "idInput => vlInput";
     * @param array $listaReservaVagas Array com id das reservas de vaga selecionadas para a chamada
     * @param array $listaPolos Array com id dos polos selecionados para a chamada
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    private function _processaConf_ComPoloSemArea($passo2, $arrayDados, $listaReservaVagas, $listaPolos, &$arrayRet) {
        // Armazenando total de vagas
        $totalVagas = 0;

        // array para total de vagas de uma reserva
        $temReserva = $listaReservaVagas != NULL;
        if ($temReserva) {
            $arrayVagasReserva = array();
            foreach ($listaReservaVagas as $idReserva) {
                $arrayVagasReserva[$idReserva] = 0; // forma ['idReserva' => 'qtVagas']
            }

            // array temporário para sqls de polo reserva
            $arraysqlPolReserva = array();
        }

        // percorrendo polos para processar
        foreach ($listaPolos as $idPolo) {
            // quantidade de vagas
            $qtVagasPolo = 0;

            // Não tem reserva de vagas?
            if (!$temReserva) {
                $qtVagasPolo = $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, $idPolo)];
            } else {
                // Tem reserva
                // percorrendo reserva de vagas e processando
                foreach ($listaReservaVagas as $idReserva) {
                    $qtVagas = $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, $idPolo) . ProcessoChamada::idInputVagasAddReserva($idReserva)];

                    // incrementando vagas da reserva e do polo
                    $qtVagasPolo += $qtVagas;
                    $arrayVagasReserva[$idReserva] += $qtVagas;

                    // processando reserva de vaga por polo
                    ReservaPoloArea::processaAtualizacaoVagas($this->PCH_ID_CHAMADA, $idPolo, NULL, $idReserva, $qtVagas, ReservaPoloArea::$RESERVA_POLO, $arraysqlPolReserva);
                }
                // somando quantidade de vagas do público geral
                $qtVagasPolo += $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, $idPolo) . ProcessoChamada::idInputVagasAddReserva(NULL)];
            }

            // processando polo
            PoloChamada::processaAtualizacaoVagas($passo2, $this->PCH_ID_CHAMADA, $idPolo, $qtVagasPolo, $arrayRet);


            // incrementando total de vagas
            $totalVagas += $qtVagasPolo;
        }


        if ($temReserva) {
            // processando Vagas da reserva
            foreach ($arrayVagasReserva as $id => $totalVagasReserva) {
                ReservaVagaChamada::processaAtualizacaoVagas($this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA, $totalVagasReserva, $id, $arrayRet);
            }

            // adicionando sqls dependentes
            $arrayRet = array_merge($arrayRet, $arraysqlPolReserva);
        }


        // Atualizando quantidade de vagas da chamada
        $arrayRet [] = self::getSqlAtualizaQtVagasChamada($this->PCH_ID_CHAMADA, $totalVagas);
    }

    /**
     * 
     * Função de processamento de vagas por caso.
     * ATENÇÃO: Para uma melhor clareza do que está sendo feito, esta função pode repetir código das funções agregadas.
     * 
     * 
     * @param boolean $passo2 Diz se a atualização é proveniente do passo 2 de uma configuração
     * @param array $arrayDados Array na forma: "idInput => vlInput";
     * @param array $listaReservaVagas Array com id das reservas de vaga selecionadas para a chamada
     * @param array $listaAreasAtu Array com id das áreas de atuação selecionadas para a chamada
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    private function _processaConf_SemPoloComArea($passo2, $arrayDados, $listaReservaVagas, $listaAreasAtu, &$arrayRet) {
        // Armazenando total de vagas
        $totalVagas = 0;

        // array para total de vagas de uma reserva
        $temReserva = $listaReservaVagas != NULL;
        if ($temReserva) {
            $arrayVagasReserva = array();
            foreach ($listaReservaVagas as $idReserva) {
                $arrayVagasReserva[$idReserva] = 0; // forma ['idReserva' => 'qtVagas']
            }

            // array temporário para sqls de área reserva
            $arraysqlAreaReserva = array();
        }

        // percorrendo áreas para processar
        foreach ($listaAreasAtu as $idArea) {
            // quantidade de vagas
            $qtVagasArea = 0;

            // Não tem reserva de vagas?
            if (!$temReserva) {
                $qtVagasArea = $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, NULL, $idArea)];
            } else {
                // Tem reserva
                // percorrendo reserva de vagas e processando
                foreach ($listaReservaVagas as $idReserva) {
                    $qtVagas = $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, NULL, $idArea) . ProcessoChamada::idInputVagasAddReserva($idReserva)];

                    // incrementando vagas da reserva e da área
                    $qtVagasArea += $qtVagas;
                    $arrayVagasReserva[$idReserva] += $qtVagas;

                    // processando reserva de vaga por área
                    ReservaPoloArea::processaAtualizacaoVagas($this->PCH_ID_CHAMADA, NULL, $idArea, $idReserva, $qtVagas, ReservaPoloArea::$RESERVA_AREA, $arraysqlAreaReserva);
                }
                // somando quantidade de vagas do público geral
                $qtVagasArea += $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, NULL, $idArea) . ProcessoChamada::idInputVagasAddReserva(NULL)];
            }

            // processando área
            AreaAtuChamada::processaAtualizacaoVagas($passo2, $this->PCH_ID_CHAMADA, $idArea, $qtVagasArea, $arrayRet);


            // incrementando total de vagas
            $totalVagas += $qtVagasArea;
        }


        if ($temReserva) {
            // processando Vagas da reserva
            foreach ($arrayVagasReserva as $id => $totalVagasReserva) {
                ReservaVagaChamada::processaAtualizacaoVagas($this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA, $totalVagasReserva, $id, $arrayRet);
            }

            // adicionando sqls dependentes
            $arrayRet = array_merge($arrayRet, $arraysqlAreaReserva);
        }


        // Atualizando quantidade de vagas da chamada
        $arrayRet [] = self::getSqlAtualizaQtVagasChamada($this->PCH_ID_CHAMADA, $totalVagas);
    }

    /**
     * 
     * Função de processamento de vagas por caso.
     * ATENÇÃO: Para uma melhor clareza do que está sendo feito, esta função pode repetir código das funções agregadas.
     * 
     * 
     * @param boolean $passo2 Diz se a atualização é proveniente do passo 2 de uma configuração
     * @param array $arrayDados Array na forma: "idInput => vlInput";
     * @param array $listaReservaVagas Array com id das reservas de vaga selecionadas para a chamada
     * @param array $listaPolos Array com id dos polos selecionados para a chamada
     * @param array $listaAreasAtu Array com id das áreas de atuação selecionadas para a chamada
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    private function _processaConf_ComPoloComArea($passo2, $arrayDados, $listaReservaVagas, $listaPolos, $listaAreasAtu, &$arrayRet) {
        // Inicialização de variáveis
        $totalVagas = 0;
        $temReserva = $listaReservaVagas != NULL;

        // array temporário para sqls construídas antes de suas dependências 
        // armazena comandos a serem executados no fim do processamento principal
        $arraysqlDeps = array();


        if ($temReserva) {
            // array para total de vagas de uma reserva
            $arrayVagasReserva = array();
            foreach ($listaReservaVagas as $idReserva) {
                $arrayVagasReserva[$idReserva] = 0; // forma ['idReserva' => 'qtVagas']
            }
        }

        // Inicializando arrays para armazenamento de vagas de polo e area
        $arrayVagasPolo = array(); // forma ['idPolo => 'qtVagas']
        $arrayVagasArea = array(); // forma ['idSubAreaConh => 'qtVagas']
        foreach ($listaPolos as $idPolo) {
            $arrayVagasPolo[$idPolo] = 0;
        }
        foreach ($listaAreasAtu as $idAreaAtu) {
            $arrayVagasArea[$idAreaAtu] = 0;
        }


        // percorrendo dados para processamento
        for ($i = 0; isset($arrayDados[self::getIdInputSelectPolo($this->PCH_ID_CHAMADA, $i)]) && isset($arrayDados[self::getIdInputSelectAreaAtu($this->PCH_ID_CHAMADA, $i)]); $i++) {
            $idPolo = $arrayDados[self::getIdInputSelectPolo($this->PCH_ID_CHAMADA, $i)];
            $idAreaAtu = $arrayDados[self::getIdInputSelectAreaAtu($this->PCH_ID_CHAMADA, $i)];

            // Não tem reserva de vagas?
            if (!$temReserva) {
                $qtVagasPoloArea = $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, $idPolo, $idAreaAtu, $i)];
            } else {
                // Tem reserva
                // percorrendo reserva de vagas e processando
                $qtVagasPoloArea = 0;
                foreach ($listaReservaVagas as $idReserva) {
                    $qtVagas = $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, $idPolo, $idAreaAtu, $i) . ProcessoChamada::idInputVagasAddReserva($idReserva)];

                    // incrementando vagas da reserva e do polo-area
                    $qtVagasPoloArea += $qtVagas;
                    $arrayVagasReserva[$idReserva] += $qtVagas;

                    // processando reserva de vaga por área
                    ReservaPoloArea::processaAtualizacaoVagas($this->PCH_ID_CHAMADA, $idPolo, $idAreaAtu, $idReserva, $qtVagas, ReservaPoloArea::$RESERVA_POLO_AREA, $arraysqlDeps);
                }

                // somando quantidade de vagas do público geral
                $qtVagasPoloArea += $arrayDados[self::getIdInputVagas($this->PCH_ID_CHAMADA, $idPolo, $idAreaAtu, $i) . ProcessoChamada::idInputVagasAddReserva(NULL)];
            }

            // incrementando quantidade de vagas do polo, da area e do total de vagas
            $arrayVagasPolo[$idPolo] += $qtVagasPoloArea;
            $arrayVagasArea[$idAreaAtu] += $qtVagasPoloArea;
            $totalVagas += $qtVagasPoloArea;

            // processando atualização de vaga polo / área
            PoloAreaChamada::processaAtualizacaoVagas($this->PCH_ID_CHAMADA, $idPolo, $idAreaAtu, $qtVagasPoloArea, $arraysqlDeps);
        }


        // finalizando processamento de polos e áreas e reserva de vagas, se houver
        // Polos
        foreach ($arrayVagasPolo as $id => $qtVagas) {
            PoloChamada::processaAtualizacaoVagas($passo2, $this->PCH_ID_CHAMADA, $id, $qtVagas, $arrayRet);
        }

        // Áreas
        foreach ($arrayVagasArea as $id => $qtVagas) {
            // processando área
            AreaAtuChamada::processaAtualizacaoVagas($passo2, $this->PCH_ID_CHAMADA, $id, $qtVagas, $arrayRet);
        }

        if ($temReserva) {
            // processando Vagas da reserva
            foreach ($arrayVagasReserva as $id => $totalVagasReserva) {
                ReservaVagaChamada::processaAtualizacaoVagas($this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA, $totalVagasReserva, $id, $arrayRet);
            }
        }

        // adicionando sqls dependentes
        $arrayRet = array_merge($arrayRet, $arraysqlDeps);


        // Atualizando quantidade de vagas da chamada
        $arrayRet [] = self::getSqlAtualizaQtVagasChamada($this->PCH_ID_CHAMADA, $totalVagas);
    }

    /**
     *  Esta função adiciona em $arrayRet as sqls responsáveis por remover a configuração anterior da chamada. 
     * 
     * @param boolean $passo2 Diz se é o passo 2 de uma configuração 
     * @param Processo $processo 
     * @param string $idPolos String com id dos polos selecionados para a chamada separados por ','
     * @param string $idAreasAtu String com id das áreas selecionadas para a chamada separadas por ','
     * @param string $idReservaVagas Array com id das reservas de vaga selecionadas para a chamada separadas por ','
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    private function sqlsRemoveConfAnterior($passo2, $processo, $idPolos, $idAreasAtu, $idReservaVagas, &$arrayRet) {

        // Gerando booleanos
        $confPoloAtu = $idPolos != NULL;
        $confAreaAtu = $idAreasAtu != NULL;
        $confReservaVaga = $idReservaVagas != NULL;


        // Tem polo e tinha área? Remover dados da tabela intermediária
        if ($confPoloAtu && $this->admiteAreaAtuacaoObj()) {
            $arrayRet [] = PoloAreaChamada::getSqlRemoverPorChamada($this->PCH_ID_CHAMADA);
        }

        // Tinha reserva e, tem polo ou tinha área? Remover dados da tabela intermediária de reserva de vagas
        if ($this->admiteReservaVagaObj() && ($confPoloAtu || $this->admiteAreaAtuacaoObj())) {
            ReservaPoloArea::sqlRemoverPorChamada($this->PCH_ID_CHAMADA, $arrayRet);
        }

        // Não é passo 2? Então a remoção de dados anteriores termina aqui!
        if (!$passo2) {
            return;
        }

        // Tratando alterações de listas
        // Caso Polo
        PoloChamada::sqlRemoveForaLista($this->PCH_ID_CHAMADA, $idPolos, $arrayRet);

        // Caso Área de atuação
        AreaAtuChamada::sqlRemoveForaLista($this->PCH_ID_CHAMADA, $idAreasAtu, $arrayRet);

        // Caso ReservaVaga
        ReservaVagaChamada::sqlRemoveForaLista($this->PCH_ID_CHAMADA, $idReservaVagas, $arrayRet);


        // Tratando alterações de configuração da chamada
        // Caso de área de atuação
        $arrayRet [] = $this->getSqlAlteraStatusAreaAtuacao($confAreaAtu ? FLAG_BD_SIM : FLAG_BD_NAO);

        // Caso Reserva de vaga
        $arrayRet [] = $this->getSqlAlteraStatusReservaVaga($confReservaVaga ? FLAG_BD_SIM : FLAG_BD_NAO);
    }

    public function getSqlAlteraStatusReservaVaga($novoStatus) {
        return "update tb_pch_processo_chamada set PCH_ADMITE_RESERVA_VAGA = '$novoStatus' where PCH_ID_CHAMADA = $this->PCH_ID_CHAMADA";
    }

    public function getSqlAlteraStatusAreaAtuacao($novoStatus) {
        return "update tb_pch_processo_chamada set PCH_ADMITE_AREA = '$novoStatus' where PCH_ID_CHAMADA = $this->PCH_ID_CHAMADA";
    }

    public static function getSqlAtualizaQtVagasChamada($idChamada, $qtVagas) {
        return "update tb_pch_processo_chamada set PCH_QT_VAGAS = '$qtVagas' where PCH_ID_CHAMADA = '$idChamada'";
    }

    private function getSqlAtuNrMaxOpcaoPolo($nrMaxOpcaoPolo) {
        $inscricaoMultipla = $nrMaxOpcaoPolo > 1 ? FLAG_BD_SIM : FLAG_BD_NAO;
        return "update tb_pch_processo_chamada set PCH_NR_MAX_OPCAO_POLO = '$nrMaxOpcaoPolo', PCH_INSCRICAO_MULTIPLA = '$inscricaoMultipla' where PCH_ID_CHAMADA = '$this->PCH_ID_CHAMADA'";
    }

    private function getSqlAtuHashVagas($novoHash) {
        return "update tb_pch_processo_chamada set PCH_HASH_ALTERACAO_VAGAS = '$novoHash' where PCH_ID_CHAMADA = '$this->PCH_ID_CHAMADA'";
    }

    /**
     * @param int $idChamada
     * @param array $item
     * @param array $vetDados
     * @return string Sql para persistência da alteração do calendário
     */
    private static function getSqlAlteracaoCalendario($idChamada, $item, $vetDados) {
        if ($item['tipo'] == self::$CAL_TP_ITEM_INSCRICAO) {
            return self::getSqlAlteracaoInscricao($idChamada, $vetDados[$item['idInput1']], $vetDados[$item['idInput2']]);
        } elseif ($item['tipo'] == self::$CAL_TP_ITEM_RESUL_PARC) {
            return EtapaSelProc::getSqlAlteracaoPrevResulEtapa($idChamada, $item['idEtapaSel'], $vetDados[$item['idInput1']]);
        } elseif ($item['tipo'] == self::$CAL_TP_ITEM_RECURSO) {
            return EtapaSelProc::getSqlAlteracaoRecurso($idChamada, $item['idEtapaSel'], $vetDados[$item['idInput1']], $vetDados[$item['idInput2']]);
        } elseif ($item['tipo'] == self::$CAL_TP_ITEM_RESUL_FIN) {
            return EtapaSelProc::getSqlAlteracaoPrevResulRecurso($idChamada, $item['idEtapaSel'], $vetDados[$item['idInput1']]);
        }
    }

    public static function getSqlAlteracaoPrevResulFinal($idChamada, $dt, $dataMysql = FALSE) {
        $dt = !$dataMysql ? dt_dataStrParaMysql($dt) : $dt;
        return "update tb_pch_processo_chamada set PCH_DT_PREV_RESUL_FINAL = $dt where PCH_ID_CHAMADA = '$idChamada'";
    }

    public static function getSqlPubResultadoFinal($idChamada, $usuResp, $urlArquivo) {
        return "update tb_pch_processo_chamada set PCH_DT_REG_RESUL_FINAL = now(),
                PCH_ID_USU_RESP_RESUL_FIN = '$usuResp', PCH_URL_ARQ_RESUL_FINAL = '$urlArquivo' where PCH_ID_CHAMADA = '$idChamada'";
    }

    public static function getSqlSetUrlResulFinal($idChamada, $urlArquivo) {
        return "update tb_pch_processo_chamada set PCH_URL_ARQ_RESUL_FINAL = '$urlArquivo' where PCH_ID_CHAMADA = '$idChamada'";
    }

    public static function getSqlFinalizacaoChamada($idChamada, $dtFim = NULL, $dataMysql = FALSE) {
        $dtFim = $dtFim == NULL ? dt_dataStrParaMysql(dt_getDataEmStr("d/m/Y")) : (!$dataMysql ? dt_dataStrParaMysql($dtFim) : $dtFim);
        return "update tb_pch_processo_chamada set PCH_DT_FINALIZACAO = $dtFim where PCH_ID_CHAMADA = '$idChamada'";
    }

    private static function getSqlAlteracaoInscricao($idChamada, $dt1, $dt2) {
        $dt1 = dt_dataStrParaMysql($dt1);
        $dt2 = dt_dataStrParaMysql($dt2);
        return "update tb_pch_processo_chamada set PCH_DT_ABERTURA = $dt1, PCH_DT_FECHAMENTO = $dt2 where PCH_ID_CHAMADA = '$idChamada'";
    }

    /**
     * 
     * @param Array $listaFases Lista de fases de uma chamada devidamente ordenada
     * @param int $faseAtual Fase atual da chamada
     * @param int $itemAtual Item Atual a ser impresso
     * @param int $nrEtapaFaseAtual Número da etapa da fase atual da chamada
     * @param int $nrEtapaItem Número da etapa do item atual a ser impresso
     * @return status Status do item de calendário: Passado, Presente ou Futuro (descrito em self::EVENTO_*)
     */
    private static function defineStatusCalendario($listaFases, $faseAtual, $itemAtual, $nrEtapaFaseAtual, $nrEtapaItem = NULL) {
        // item atual é a fase atual? presente
        if ($faseAtual == $itemAtual && $nrEtapaFaseAtual == $nrEtapaItem) {
            return self::$EVENTO_PRESENTE;
        }

        // verificando se é passado ou futuro
        $posFase = array_search($faseAtual, $listaFases);
        $posAtu = array_search($itemAtual, $listaFases);

        // print_r("Atual: $posAtu Fase: $posFase ");
        // 
        // passado
        if ($nrEtapaItem < $nrEtapaFaseAtual || ($nrEtapaItem == $nrEtapaFaseAtual && $posAtu < $posFase)) {
            return self::$EVENTO_PASSADO;
        }

        // futuro
        return self::$EVENTO_FUTURO;
    }

    /**
     * Esta função retorna o status 'editavel' da lista do calendário.
     * 
     * Observe que esta função altera o status do elemento anterior da lista,
     * visto que a decisão de edição depende do próximo item a ser inserido.
     * 
     * @param char $statusProxItem Status do próximo item a ser inserido na lista
     * @param boolean $permiteEdicao Informa como está a flag de permisão de edição
     * @param char $tpItemCalAtual Tipo do item de calendário atual, a ser setado a edição
     * @param EtapaSelProc $etapaSelItemAtual Etapa de seleção em processamento atualmente, se houver
     */
    private static function defineStatusCalendarioEditavel($statusProxItem, $permiteEdicao, $tpItemCalAtual, $etapaSelItemAtual = NULL) {
        // inicializando flag de editavel
        $editavel = $permiteEdicao && ($statusProxItem == self::$EVENTO_PRESENTE || $statusProxItem == self::$EVENTO_FUTURO);

        // tratando casos especiais
        if ($editavel && $statusProxItem == self::$EVENTO_PRESENTE) {
            // verificando resultados publicados 
            // 
            // caso de resultado parcial
            if ($tpItemCalAtual == self::$CAL_TP_ITEM_RESUL_PARC && $etapaSelItemAtual != NULL && $etapaSelItemAtual->publicouResultadoParcial()) {
                // travando
                return FALSE;
            }

            // caso de resultado pós recurso
            if ($tpItemCalAtual == self::$CAL_TP_ITEM_RESUL_FIN && $etapaSelItemAtual != NULL && $etapaSelItemAtual->publicouResultadoPosRec()) {
                // travando
                return FALSE;
            }
        }
        return $editavel;
    }

    public static function getListaOrdFasesChamada() {
        return array(self::$COD_FASE_EM_CONSTRUCAO, self::$COD_FASE_FECHADA, self::$COD_FASE_INSCRICAO, self::$COD_FASE_ETAPA_RESUL_PARCIAL, self::$COD_FASE_ETAPA_ESP_PER_RECURSO, self::$COD_FASE_ETAPA_PER_RECURSO, self::$COD_FASE_ETAPA_PROC_RECURSO, self::$COD_FASE_FINALIZADA);
    }

    /**
     * Se IdProcesso nao eh nulo, tambem eh validado se a chamada pertence ao 
     * processo.
     * 
     * @param int $idChamada
     * @param int $idProcesso
     * @return \ProcessoChamada
     * @throws NegocioException
     */
    public static function buscarChamadaPorId($idChamada, $idProcesso = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    PCH_ID_CHAMADA,
                    prc.PRC_ID_PROCESSO,
                    DATE_FORMAT(`PRC_DT_FIM`,'%d/%m/%Y') as PRC_DT_FIM,
                    DATE_FORMAT(`PRC_DT_INICIO`,'%d/%m/%Y') as PRC_DT_INICIO,
                    DATE_FORMAT(`PCH_DT_ABERTURA`,'%d/%m/%Y') as PCH_DT_ABERTURA,
                    DATE_FORMAT(`PCH_DT_FECHAMENTO`,'%d/%m/%Y') as PCH_DT_FECHAMENTO,
                    PCH_DS_CHAMADA,
                    PCH_NR_ORDEM,
                    PCH_CHAMADA_ATUAL,
                    PCH_NR_MAX_OPCAO_POLO,
                    PCH_TXT_COMP_INSCRICAO,
                    PCH_QT_VAGAS,
                    PCH_ADMITE_AREA,
                    PCH_INSCRICAO_MULTIPLA,
                    DATE_FORMAT(`PCH_DT_PREV_RESUL_FINAL`,'%d/%m/%Y') as PCH_DT_PREV_RESUL_FINAL,
                    DATE_FORMAT(`PCH_DT_REG_RESUL_FINAL`,'%d/%m/%Y %T') as PCH_DT_REG_RESUL_FINAL,
                    DATE_FORMAT(`PCH_DT_FINALIZACAO`,'%d/%m/%Y') as PCH_DT_FINALIZACAO,
                    PCH_ID_USU_RESP_RESUL_FIN,
                    PCH_ADMITE_RESERVA_VAGA,
                    usr.USR_DS_NOME,
                    PCH_HASH_ALTERACAO_VAGAS,
                    PCH_CHAMADA_ATIVA,
                    PCH_URL_ARQ_RESUL_FINAL,
                    TIC_ID_TIPO_CARGO,
                    DATE_FORMAT(`PCH_ATV_DT_SOLICITACAO`,'%d/%m/%Y %T') as PCH_ATV_DT_SOLICITACAO,
                    DATE_FORMAT(`PCH_ATV_DT_ATIVACAO`,'%d/%m/%Y %T') as PCH_ATV_DT_ATIVACAO,
                    PCH_ATV_USU_RESPONSAVEL,
                    PCH_ATV_USU_SOLICITANTE,
                    atvusu.USR_DS_NOME as ATV_NOME_SOLICITANTE
                from
                    tb_pch_processo_chamada pch
                    left join tb_usr_usuario usr on pch.PCH_ID_USU_RESP_RESUL_FIN = usr.USR_ID_USUARIO
                    left join tb_usr_usuario atvusu on pch.PCH_ATV_USU_SOLICITANTE = atvusu.USR_ID_USUARIO
                    join tb_prc_processo prc on prc.PRC_ID_PROCESSO = pch.PRC_ID_PROCESSO
                where
                    PCH_ID_CHAMADA = '$idChamada'";

            // verificando necessidade de adicionar processo
            if ($idProcesso != NULL) {
                $sql .= " and prc.PRC_ID_PROCESSO = '$idProcesso'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                throw new NegocioException("Chamada de processo não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $chamadaRet = new ProcessoChamada($dados['PCH_ID_CHAMADA'], $dados['PRC_ID_PROCESSO'], $dados['PCH_DT_ABERTURA'], $dados['PCH_DT_FECHAMENTO'], $dados['PCH_DS_CHAMADA'], $dados['PCH_NR_ORDEM'], $dados['PCH_CHAMADA_ATUAL'], $dados['PCH_NR_MAX_OPCAO_POLO'], $dados['PCH_TXT_COMP_INSCRICAO'], $dados['PCH_ADMITE_AREA'], $dados['PCH_INSCRICAO_MULTIPLA'], $dados['PCH_DT_PREV_RESUL_FINAL'], $dados['PCH_DT_REG_RESUL_FINAL'], $dados['PCH_ID_USU_RESP_RESUL_FIN'], $dados['PCH_ADMITE_RESERVA_VAGA'], $dados['PCH_QT_VAGAS'], $dados['PCH_HASH_ALTERACAO_VAGAS'], $dados['PCH_CHAMADA_ATIVA'], $dados['PCH_URL_ARQ_RESUL_FINAL'], $dados['PCH_DT_FINALIZACAO'], $dados['PCH_ATV_DT_SOLICITACAO'], $dados['PCH_ATV_DT_ATIVACAO'], $dados['PCH_ATV_USU_RESPONSAVEL'], $dados['PCH_ATV_USU_SOLICITANTE']);

            // campos adicionais
            $chamadaRet->NM_USU_RESP_RESUL_FIN = $dados['USR_DS_NOME'];
            $chamadaRet->PRC_DT_FIM = $dados['PRC_DT_FIM'];
            $chamadaRet->PRC_DT_INICIO = $dados['PRC_DT_INICIO'];
            $chamadaRet->TIC_ID_TIPO_CARGO = $dados['TIC_ID_TIPO_CARGO'];
            $chamadaRet->ATV_NOME_SOLICITANTE = $dados['ATV_NOME_SOLICITANTE'];

            return $chamadaRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar chamada de processo.", $e);
        }
    }

    /**
     * 
     * @param Processo $processo
     * @param string $dtInicio Data de início das inscrições no formato dd/mm/yyyy
     * @param string $dtFim Data de término das inscrições no formato dd/mm/yyyy
     * @return int ID da chamada criada
     * @throws NegocioException
     */
    public static function criarChamada($processo, $dtInicio, $dtFim) {
        try {
            // verificando se pode criar chamada
            $listaChamadas = ProcessoChamada::buscarChamadaPorProcesso($processo->getPRC_ID_PROCESSO());
            $permiteCriar = $processo->permiteCriarChamada($listaChamadas);
            if (!$permiteCriar[0]) {
                throw new NegocioException($permiteCriar[1]);
            }

            // validando início das inscrições
            if (dt_dataMenor(dt_getTimestampDtBR($dtInicio), dt_getTimestampDtBR($processo->getPRC_DT_INICIO()))) {
                throw new NegocioException("Data inicial do período de inscrição é anterior ao dia de abertura do edital.");
            }

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando datas
            $dtInicio = dt_dataStrParaMysql($dtInicio);
            $dtFim = dt_dataStrParaMysql($dtFim);

            // recuperando ordem e descrição da chamada
            $ordemDesc = self::getOrdemDescChamada(count($listaChamadas));
            $flagChamAtual = Util::vazioNulo($listaChamadas) ? NGUtil::trataCampoStrParaBD(NGUtil::getFlagSim()) : NGUtil::trataCampoStrParaBD(NGUtil::getFlagNao());
            $flagNao = NGUtil::trataCampoStrParaBD(NGUtil::getFlagNao());

            // montando SQL de criação
            $sql = "insert into tb_pch_processo_chamada
                (`PRC_ID_PROCESSO`, `PCH_DT_ABERTURA`, `PCH_DT_FECHAMENTO`, PCH_DS_CHAMADA, PCH_NR_ORDEM, PCH_CHAMADA_ATUAL, PCH_QT_VAGAS, PCH_CHAMADA_ATIVA)
                 values('{$processo->getPRC_ID_PROCESSO()}', $dtInicio, $dtFim, '$ordemDesc[1]', '$ordemDesc[0]', $flagChamAtual, 0, $flagNao)";

            $arrayCmds = array();

            // recuperando sql de criar etapas de seleção
            $arrayCmds [] = EtapaSelProc::_getSqlCriarEtapaSel($processo->getPRC_ID_PROCESSO(), ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE);

            // desabilitar finalização do processo
            Processo::addSqlFinalizacaoEdital($arrayCmds, $processo->getPRC_ID_PROCESSO(), NULL, NULL, TRUE);

            // persistindo no bd e recuperando id
            return $conexao->execTransacaoDependente($sql, $arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao criar chamada do Edital.", $e);
        }
    }

    private static function getOrdemDescChamada($qtChamadasAtual) {
        if ($qtChamadasAtual > count(self::$VET_DESC_CHAMADA)) {
            throw new NegocioException("Número máximo de chamadas excedido. Por favor, entre em contato com o administrador do sistema.");
        }
        return array($qtChamadasAtual + 1, self::$VET_DESC_CHAMADA[$qtChamadasAtual]);
    }

    /**
     * 
     * @param int $idProcesso
     * @return int
     * @throws NegocioException
     */
    public static function contarChamadaPorProcesso($idProcesso) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
                        count(*) as cont
                    from
                        tb_pch_processo_chamada
                    where
                        PRC_ID_PROCESSO = '$idProcesso'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar chamadas por processo.", $e);
        }
    }

    public static function contarChamadaporUsuResp($idUsuResp) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_pch_processo_chamada
                    where PCH_ID_USU_RESP_RESUL_FIN = '$idUsuResp'
                          or PCH_ATV_USU_RESPONSAVEL = '$idUsuResp'
                          or PCH_ATV_USU_SOLICITANTE = '$idUsuResp'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar chamadas do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @return array Array na forma: [idChamada => dsChamada, ...]
     * @throws NegocioException
     */
    public static function buscarIdDsChamadaPorProcesso($idProcesso) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select `PCH_ID_CHAMADA`
                , `PCH_DS_CHAMADA`
                from tb_pch_processo_chamada
                where PRC_ID_PROCESSO = '$idProcesso'
                order by `PCH_NR_ORDEM` desc";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os cargos
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['PCH_ID_CHAMADA'];
                $valor = $dados['PCH_DS_CHAMADA'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar chamadas do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param boolean $soAtivas Diz se é para retornar apenas as chamadas ativas. Padrão é false
     * @return ProcessoChamada Array de Chamadas do processo
     * @throws NegocioException
     */
    public static function buscarChamadaPorProcesso($idProcesso, $soAtivas = FALSE) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    PCH_ID_CHAMADA,
                    prc.PRC_ID_PROCESSO,
                    DATE_FORMAT(`PRC_DT_FIM`,'%d/%m/%Y') as PRC_DT_FIM,
                    DATE_FORMAT(`PRC_DT_INICIO`,'%d/%m/%Y') as PRC_DT_INICIO,
                    DATE_FORMAT(`PCH_DT_ABERTURA`,'%d/%m/%Y') as PCH_DT_ABERTURA,
                    DATE_FORMAT(`PCH_DT_FECHAMENTO`,'%d/%m/%Y') as PCH_DT_FECHAMENTO,
                    PCH_DS_CHAMADA,
                    PCH_NR_ORDEM,
                    PCH_CHAMADA_ATUAL,
                    PCH_NR_MAX_OPCAO_POLO,
                    PCH_TXT_COMP_INSCRICAO,
                    PCH_QT_VAGAS,
                    PCH_ADMITE_AREA,
                    PCH_INSCRICAO_MULTIPLA,
                    DATE_FORMAT(`PCH_DT_PREV_RESUL_FINAL`,'%d/%m/%Y') as PCH_DT_PREV_RESUL_FINAL,
                    DATE_FORMAT(`PCH_DT_REG_RESUL_FINAL`,'%d/%m/%Y %T') as PCH_DT_REG_RESUL_FINAL,
                    DATE_FORMAT(`PCH_DT_FINALIZACAO`,'%d/%m/%Y') as PCH_DT_FINALIZACAO,
                    PCH_ID_USU_RESP_RESUL_FIN,
                    PCH_ADMITE_RESERVA_VAGA,
                    usr.USR_DS_NOME,
                    PCH_HASH_ALTERACAO_VAGAS,
                    PCH_CHAMADA_ATIVA,
                    PCH_URL_ARQ_RESUL_FINAL,
                    TIC_ID_TIPO_CARGO,
                    DATE_FORMAT(`PCH_ATV_DT_SOLICITACAO`,'%d/%m/%Y %T') as PCH_ATV_DT_SOLICITACAO,
                    DATE_FORMAT(`PCH_ATV_DT_ATIVACAO`,'%d/%m/%Y %T') as PCH_ATV_DT_ATIVACAO,
                    PCH_ATV_USU_RESPONSAVEL,
                    PCH_ATV_USU_SOLICITANTE,
                    atvusu.USR_DS_NOME as ATV_NOME_SOLICITANTE
                from
                    tb_pch_processo_chamada pch
                    left join tb_usr_usuario usr on pch.PCH_ID_USU_RESP_RESUL_FIN = usr.USR_ID_USUARIO
                    left join tb_usr_usuario atvusu on pch.PCH_ATV_USU_SOLICITANTE = atvusu.USR_ID_USUARIO
                    join tb_prc_processo prc on prc.PRC_ID_PROCESSO = pch.PRC_ID_PROCESSO
                where prc.PRC_ID_PROCESSO = '$idProcesso'";

            // apenas chamadas ativas?
            if ($soAtivas) {
                $sql .= " and PCH_CHAMADA_ATIVA = '" . FLAG_BD_SIM . "' ";
            }

            // finalizando
            $sql .= " order by `PCH_NR_ORDEM`";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os cargos
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                // chamada temporária
                $chamadaTemp = new ProcessoChamada($dados['PCH_ID_CHAMADA'], $dados['PRC_ID_PROCESSO'], $dados['PCH_DT_ABERTURA'], $dados['PCH_DT_FECHAMENTO'], $dados['PCH_DS_CHAMADA'], $dados['PCH_NR_ORDEM'], $dados['PCH_CHAMADA_ATUAL'], $dados['PCH_NR_MAX_OPCAO_POLO'], $dados['PCH_TXT_COMP_INSCRICAO'], $dados['PCH_ADMITE_AREA'], $dados['PCH_INSCRICAO_MULTIPLA'], $dados['PCH_DT_PREV_RESUL_FINAL'], $dados['PCH_DT_REG_RESUL_FINAL'], $dados['PCH_ID_USU_RESP_RESUL_FIN'], $dados['PCH_ADMITE_RESERVA_VAGA'], $dados['PCH_QT_VAGAS'], $dados['PCH_HASH_ALTERACAO_VAGAS'], $dados['PCH_CHAMADA_ATIVA'], $dados['PCH_URL_ARQ_RESUL_FINAL'], $dados['PCH_DT_FINALIZACAO'], $dados['PCH_ATV_DT_SOLICITACAO'], $dados['PCH_ATV_DT_ATIVACAO'], $dados['PCH_ATV_USU_RESPONSAVEL'], $dados['PCH_ATV_USU_SOLICITANTE']);

                // campos adicionais
                $chamadaTemp->NM_USU_RESP_RESUL_FIN = $dados['USR_DS_NOME'];
                $chamadaTemp->PRC_DT_FIM = $dados['PRC_DT_FIM'];
                $chamadaTemp->PRC_DT_INICIO = $dados['PRC_DT_INICIO'];
                $chamadaTemp->TIC_ID_TIPO_CARGO = $dados['TIC_ID_TIPO_CARGO'];
                $chamadaTemp->ATV_NOME_SOLICITANTE = $dados['ATV_NOME_SOLICITANTE'];

                //adicionando no vetor
                $vetRetorno[] = $chamadaTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar chamadas do processo.", $e);
        }
    }

    /**
     * Esta função retorna as chamadas com pendências, seguindo um formato específico
     * 
     * @return array Matriz indexada por idProcesso:idChamada onde cada linha contém um array com os seguintes dados (índices):
     *  - data Data da pendência
     *  - edital Descrição do edital
     *  - ocorrencia Descrição da ocorrência
     *  - link Link para onde o admin deve ser redirecionado
     *  - solicitante Nome do usuário que causou (solicitou uma ação) a pendência
     * 
     * @throws NegocioException
     */
    public static function buscarChamadaComPendencia() {
        global $CFG;

        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // constantes
            $etapaAberta = EtapaSelProc::$SIT_ABERTA;
            $etapaRecurso = EtapaSelProc::$SIT_RECURSO;
            $flSim = NGUtil::getFlagSim();

            $sql = "SELECT 
                        'R' AS tipo,
                        pch.PCH_ID_CHAMADA,
                        prc.PRC_ID_PROCESSO,
                        CONCAT('Edital ',
                                LPAD(PRC_NR_EDITAL, 3, '0'),
                                '/',
                                PRC_ANO_EDITAL,
                                ' | ',
                                TIC_NM_TIPO,
                                ' | ',
                                CUR_NM_CURSO) AS edital,
                        (CASE ESP_ST_ETAPA
                            WHEN
                                '$etapaAberta'
                            THEN
                                CONCAT('Resultado parc. da Etapa ',
                                        EAP_NR_ETAPA_AVAL,
                                        ' - ',
                                        PCH_DS_CHAMADA,
                                        ' Chamada')
                            WHEN
                                '$etapaRecurso'
                            THEN
                                CONCAT('Resultado pós-rec. da Etapa ',
                                        EAP_NR_ETAPA_AVAL,
                                        ' - ',
                                        PCH_DS_CHAMADA,
                                        ' Chamada')
                        END) AS dsOcorrencia,
                        DATE_FORMAT((CASE ESP_ST_ETAPA
                                    WHEN '$etapaAberta' THEN `ESP_PUB_DT_SOL_RESUL_PAR`
                                    WHEN '$etapaRecurso' THEN `ESP_PUB_DT_SOL_RESUL_FIN`
                                END),
                                '%d/%m/%Y') AS dtOcorrenciaStr,
                        (CASE ESP_ST_ETAPA
                            WHEN '$etapaAberta' THEN `ESP_PUB_DT_SOL_RESUL_PAR`
                            WHEN '$etapaRecurso' THEN `ESP_PUB_DT_SOL_RESUL_FIN`
                        END) AS dtOcorrencia,
                        (CASE ESP_ST_ETAPA
                            WHEN '$etapaAberta' THEN usP.USR_DS_NOME
                            WHEN '$etapaRecurso' THEN usF.USR_DS_NOME
                        END) AS solicitante
                    FROM
                        tb_pch_processo_chamada pch
                            LEFT JOIN
                        tb_usr_usuario atvusu ON pch.PCH_ATV_USU_SOLICITANTE = atvusu.USR_ID_USUARIO
                            JOIN
                        tb_prc_processo prc ON prc.PRC_ID_PROCESSO = pch.PRC_ID_PROCESSO
                            JOIN
                        tb_tic_tipo_cargo tic ON prc.TIC_ID_TIPO_CARGO = tic.TIC_ID_TIPO
                            JOIN
                        tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
                            JOIN
                        tb_esp_etapa_sel_proc esp ON pch.PCH_ID_CHAMADA = esp.PCH_ID_CHAMADA
                            AND esp.ESP_ETAPA_ATIVA = '$flSim'
                            JOIN
                        tb_eap_etapa_aval_proc eap ON esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                            LEFT JOIN
                        tb_usr_usuario usP ON usP.USR_ID_USUARIO = esp.ESP_PUB_USU_RESP_SOL_PAR
                            LEFT JOIN
                        tb_usr_usuario usF ON usF.USR_ID_USUARIO = esp.ESP_PUB_USU_RESP_SOL_FIN
                    WHERE
                        PCH_CHAMADA_ATIVA = '$flSim'
                            AND ((ESP_ST_ETAPA = '$etapaAberta'
                            AND ESP_PUB_DT_SOL_RESUL_PAR IS NOT NULL)
                            OR (ESP_ST_ETAPA = '$etapaRecurso'
                            AND ESP_PUB_DT_SOL_RESUL_FIN IS NOT NULL)) 
                    UNION SELECT 
                        'A' AS tipo,
                        PCH_ID_CHAMADA,
                        prc.PRC_ID_PROCESSO,
                        CONCAT('Edital ',
                                LPAD(PRC_NR_EDITAL, 3, '0'),
                                '/',
                                PRC_ANO_EDITAL,
                                ' | ',
                                TIC_NM_TIPO,
                                ' | ',
                                CUR_NM_CURSO) AS edital,
                        CONCAT(PCH_DS_CHAMADA, ' Chamada') AS dsOcorrencia,
                        DATE_FORMAT(`PCH_ATV_DT_SOLICITACAO`, '%d/%m/%Y') AS dtOcorrenciaStr,
                        `PCH_ATV_DT_SOLICITACAO` AS dtOcorrencia,
                        atvusu.USR_DS_NOME AS solicitante
                    FROM
                        tb_pch_processo_chamada pch
                            LEFT JOIN
                        tb_usr_usuario atvusu ON pch.PCH_ATV_USU_SOLICITANTE = atvusu.USR_ID_USUARIO
                            JOIN
                        tb_prc_processo prc ON prc.PRC_ID_PROCESSO = pch.PRC_ID_PROCESSO
                            JOIN
                        tb_tic_tipo_cargo tic ON prc.TIC_ID_TIPO_CARGO = tic.TIC_ID_TIPO
                            JOIN
                        tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
                    WHERE
                        PCH_CHAMADA_ATIVA != '$flSim'
                            AND PCH_ATV_DT_SOLICITACAO IS NOT NULL
                    ORDER BY dtOcorrencia";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e preparando retorno
                $dados = ConexaoMysql::getLinha($resp);

                $chave = $dados['PRC_ID_PROCESSO'] . ":" . $dados['PCH_ID_CHAMADA'];

                //adicionando no vetor
                if ($dados['tipo'] == "A") {
                    $link = "$CFG->rwww/visao/processo/manterProcessoAdmin.php?" . Util::$ABA_PARAM . "=" . Util::$ABA_MPA_CHAMADA . "&idProcesso=" . $dados['PRC_ID_PROCESSO'] . "&idChamada=" . $dados['PCH_ID_CHAMADA'];
                } elseif ($dados['tipo'] == "R") {
                    $link = "$CFG->rwww/visao/processo/gerenciarResultadosProcesso.php?&idProcesso=" . $dados['PRC_ID_PROCESSO'] . "&idChamada=" . $dados['PCH_ID_CHAMADA'];
                }
                $vetRetorno[$chave] = array("data" => $dados['dtOcorrenciaStr'], "edital" => $dados['edital'], "ocorrencia" => $dados['dsOcorrencia'], "link" => $link, "solicitante" => $dados['solicitante']);
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar chamadas com pendências.", $e);
        }
    }

    /**
     * Essa funcao retorna a data de inscricao da primeira chamda do processo
     * no formato americano (yyyy-mm-dd).
     * Se nao existir chamada, é retornado NULL
     * @param int $idProcesso
     * @return null|timestamp
     * @throws NegocioException
     */
    public static function buscaDtUSPriChamadaDoProcesso($idProcesso) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        PCH_DT_ABERTURA as dt
                    from
                        tb_pch_processo_chamada
                    where
                        PRC_ID_PROCESSO = '$idProcesso'
                    order by PCH_DT_ABERTURA
                    limit 0 , 1";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // nao tem chamada
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return NULL;
            }

            //retornando 
            return ConexaoMysql::getResult("dt", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar recuperar a primeira chamada do processo.", $e);
        }
    }

    public function getDsPeriodoInscricao() {
        return "$this->PCH_DT_ABERTURA a $this->PCH_DT_FECHAMENTO";
    }

    public static function getMsgSemAreaAtuacao() {
        return htmlentities("Não aplicada a esta chamada");
    }

    public static function getMsgSemReservaVaga() {
        return htmlentities("Não aplicada a esta chamada");
    }

//-------------- Geração automática de HTML ----------------------

    /**
     * Função que retorna o html da flag de inscrição.
     * 
     * @return string HTML da flag de acordo com o status da inscrição
     */
    public function getHtmlFlagInscricao() {
        $statusPerInsc = $this->getStatusPerInscricao();

        // dentro do período? 
        if ($statusPerInsc[0]) {
            // tag aberta
            return "<span class='label label-success'>" . $this->getLinkInscricao("Inscreva-se aqui", FALSE) . "</span>";
        } elseif ($statusPerInsc[1] == ProcessoChamada::$EVENTO_PASSADO) {
            return "<span class='label label-default'>Período encerrado</span>";
        } elseif ($statusPerInsc[1] == ProcessoChamada::$EVENTO_FUTURO) {
            return "<span class='label label-default' style='color: black;'>" . $this->getLinkInscricao("Inscreva-se aqui", FALSE) . "</span>";
        }
    }

    public function isMostrarLinkInscricao($considerarFuturo = TRUE) {
        $statusPerInsc = $this->getStatusPerInscricao();
        return $statusPerInsc[0] || ($considerarFuturo && $statusPerInsc[1] == ProcessoChamada::$EVENTO_FUTURO);
    }

    public function getLinkInscricao($textoLink, $extensao = TRUE) {
        global $CFG;

        $statusPerInsc = $this->getStatusPerInscricao();

        // Definindo link de acordo com a situação
        if ($statusPerInsc[0]) {
            // tem link
            $dadosLink = "target='_blank' href='$CFG->rwww/visao/inscricaoProcesso/criarInscProcesso.php?idProcesso={$this->getPRC_ID_PROCESSO()}'";
        } elseif ($statusPerInsc[1] == ProcessoChamada::$EVENTO_PASSADO) {
            $dadosLink = "tabindex='0' data-toggle='popover' data-trigger='focus' title='Período encerrado' data-content='O período de inscrição está encerrado.'";
        } elseif ($statusPerInsc[1] == ProcessoChamada::$EVENTO_FUTURO) {
            $dadosLink = "tabindex='0' data-toggle='popover' data-trigger='focus' title='Período não iniciado' data-content='Aguarde o início do período de inscrição.'";
        }

        $ret = "<a $dadosLink >$textoLink";

        if ($extensao && ($this->isMostrarLinkInscricao(FALSE))) {
            $ret .= " <i class='fa fa-external-link'></i>";
        }

        $ret .= "</a>";

        return $ret;
    }

    public function getMsgLogadoBoxInscricao() {
        $statusPerInsc = $this->getStatusPerInscricao();

        $ret = "Atenção: Não é possível editar seu currículo durante o processo seletivo";
        if ($statusPerInsc[0]) {
            $ret.= ", se necessário, atualize-o antes de realizar sua inscrição.";
        } else {
            $ret .= ".";
        }
        return $ret;
    }

    private function getStatusPerInscricao() {
        if ($this->statusPerInscricao == NULL) {
            try {
                $this->statusPerInscricao = self::validaPeriodoInscPorChamada($this->PCH_ID_CHAMADA, TRUE);
            } catch (NegocioException $n) {
                throw $n;
            } catch (Exception $e) {
                throw new NegocioException("Erro ao definir status do período de inscrição.", $e);
            }
        }
        return $this->statusPerInscricao;
    }

    /**
     * Função que retorna o html da flag de resultado.
     * 
     * @return string HTML da flag de acordo com o status do resultado
     */
    public function getHtmlFlagResultado() {
        $idFaseChamada = $this->getIdFaseChamada();
        $dsFase = $this->getDsFaseChamada();

        // É uma fase que representa etapas do processo
        if (self::isFaseEtapaProcesso($idFaseChamada)) {
            // tag descrevendo as fases
            return "<span class='label label-default'>$dsFase</span>";
        }

        if ($idFaseChamada == self::$COD_FASE_FINALIZADA) {
            return "<span class='label label-default'>Resultado Final</span>";
        }
    }

    /**
     * Função que informa se a fase em questão é uma fase que representa etapas do processo
     * 
     * @param char $idFaseChamada
     * @return boolean
     */
    private static function isFaseEtapaProcesso($idFaseChamada) {
        return $idFaseChamada != self::$COD_FASE_FECHADA && $idFaseChamada != self::$COD_FASE_FINALIZADA && $idFaseChamada != self::$COD_FASE_INSCRICAO && $idFaseChamada != self::$COD_FASE_RESUL_PUBLICADO && $idFaseChamada != self::$COD_FASE_NAO_DEFINIDA;
    }

    /**
     * Prepara a string de data para apresentação correta, verificando 
     * os casos de presença e ausência.
     * 
     * @param string $data1
     * @param string $data2
     */
    public static function getHtmlData($data1, $data2 = NULL) {
        if (Util::vazioNulo($data1)) {
            return Util::$STR_CAMPO_VAZIO;
        } elseif (Util::vazioNulo($data2)) {
            return $data1;
        } else {
            return "$data1 a $data2";
        }
    }

    public function getHtmlCaixaResultado() {
        // Ainda não saiu resultado?
        if (!$this->saiuResultadoFinal()) {
            $um = $this->PCH_DT_PREV_RESUL_FINAL != NULL ? self::$UM_ASTERISCO : "";
            $msgUm = $this->PCH_DT_PREV_RESUL_FINAL != NULL ? self::$UM_ASTERISCO_HTML_MSG : "";
            return "<p><i class='fa fa-file-o'></i> Resultado final: {$this->getHtmlData($this->PCH_DT_PREV_RESUL_FINAL)} $um</p>$msgUm";
        } else {
            // preparando retorno de link do arquivo de edital
            $retorno = "<p><i class='fa fa-file-pdf-o'></i> <a target='_blank' href='{$this->getUrlArquivoResulFinal()}' title='Visualizar o resultado final'>Resultado final <i class='fa fa-external-link'></i></a></p>";

            // atualização?
            $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($this->PCH_URL_ARQ_RESUL_FINAL, $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);
            if ($dtAtualizacao != NULL) {
                $retorno .= "<p><i class='fa fa-calendar'></i> Atualizado em: <a href='#atualizacoes'>$dtAtualizacao</a></p>";
            } else {
                $retorno .= "<p><i class='fa fa-calendar'></i> Publicado em: {$this->getPCH_DT_REG_RESUL_FINAL(TRUE)}</p>";
            }

            return $retorno;
        }
    }

//-------------- Geração automática de HTML ----------------------


    /* GET FIELDS FROM TABLE */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getPCH_DT_ABERTURA() {
        return $this->PCH_DT_ABERTURA;
    }

    /* End of get PCH_DT_ABERTURA */

    function getPCH_DT_FECHAMENTO() {
        return $this->PCH_DT_FECHAMENTO;
    }

    /* End of get PCH_DT_FECHAMENTO */

    function getPCH_DS_CHAMADA($completa = FALSE) {
        return $this->PCH_DS_CHAMADA . (!$completa ? "" : " Chamada");
    }

    /* End of get PCH_DS_CHAMADA */

    function getPCH_NR_ORDEM() {
        return $this->PCH_NR_ORDEM;
    }

    /* End of get PCH_NR_ORDEM */

    function getPCH_CHAMADA_ATUAL() {
        return $this->PCH_CHAMADA_ATUAL;
    }

    /* End of get PCH_CHAMADA_ATUAL */

    function getPCH_NR_MAX_OPCAO_POLO() {
        return $this->PCH_NR_MAX_OPCAO_POLO;
    }

    /* End of get PCH_NR_MAX_OPCAO_POLO */

    function getPCH_TXT_COMP_INSCRICAO($dadosCru = FALSE) {
        if (!$dadosCru && Util::vazioNulo($this->PCH_TXT_COMP_INSCRICAO)) {
            return "<i>Não definida</i>";
        }

        if (!$dadosCru) {
            return str_replace("\n", "<br/>", $this->PCH_TXT_COMP_INSCRICAO);
        }

        return $this->PCH_TXT_COMP_INSCRICAO;
    }

    /* End of get PCH_TXT_COMP_INSCRICAO */

    function getPCH_ADMITE_AREA() {
        return $this->PCH_ADMITE_AREA;
    }

    /* End of get PCH_ADMITE_AREA */

    function getPCH_DT_PREV_RESUL_FINAL() {
        return $this->PCH_DT_PREV_RESUL_FINAL;
    }

    /* End of get PCH_DT_PREV_RESUL_FINAL */

    function getPCH_DT_REG_RESUL_FINAL($apenasData = FALSE) {
        if ($apenasData && !Util::vazioNulo($this->PCH_DT_REG_RESUL_FINAL)) {
            $temp = explode(" ", $this->PCH_DT_REG_RESUL_FINAL);
            return $temp[0];
        }
        return $this->PCH_DT_REG_RESUL_FINAL;
    }

    /* End of get PCH_DT_REG_RESUL_FINAL */

    function getPCH_ID_USU_RESP_RESUL_FIN() {
        return $this->PCH_ID_USU_RESP_RESUL_FIN;
    }

    /* End of get PCH_ID_USU_RESP_RESUL_FIN */

    function getPCH_ADMITE_RESERVA_VAGA() {
        return $this->PCH_ADMITE_RESERVA_VAGA;
    }

    /* End of get PCH_ADMITE_RESERVA_VAGA */

    function getPCH_QT_VAGAS() {
        return $this->PCH_QT_VAGAS;
    }

    function getPCH_DT_FINALIZACAO($apenasData = FALSE) {
        if ($apenasData && !Util::vazioNulo($this->PCH_DT_FINALIZACAO)) {
            $temp = explode(" ", $this->PCH_DT_FINALIZACAO);
            return $temp[0];
        }
        return $this->PCH_DT_FINALIZACAO;
    }

    function getPCH_ATV_DT_SOLICITACAO() {
        return $this->PCH_ATV_DT_SOLICITACAO;
    }

    function getPCH_ATV_DT_ATIVACAO() {
        return $this->PCH_ATV_DT_ATIVACAO;
    }

    function getPCH_ATV_USU_RESPONSAVEL() {
        return $this->PCH_ATV_USU_RESPONSAVEL;
    }

    function getPCH_ATV_USU_SOLICITANTE() {
        return $this->PCH_ATV_USU_SOLICITANTE;
    }

    function getATV_NOME_SOLICITANTE() {
        return $this->ATV_NOME_SOLICITANTE;
    }

    function getPCH_URL_ARQ_RESUL_FINAL($provisorio = FALSE) {
        if (!$provisorio) {
            return $this->PCH_URL_ARQ_RESUL_FINAL;
        }
        return str_replace(AcompProcChamada::$TIPO_PDF, self::$ADENDO_ARQ_PROVISORIO . AcompProcChamada::$TIPO_PDF, $this->PCH_URL_ARQ_RESUL_FINAL);
    }

    /* SET FIELDS FROM TABLE */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setPCH_DT_ABERTURA($value) {
        $this->PCH_DT_ABERTURA = $value;
    }

    /* End of SET PCH_DT_ABERTURA */

    function setPCH_DT_FECHAMENTO($value) {
        $this->PCH_DT_FECHAMENTO = $value;
    }

    /* End of SET PCH_DT_FECHAMENTO */

    function setPCH_DS_CHAMADA($value) {
        $this->PCH_DS_CHAMADA = $value;
    }

    /* End of SET PCH_DS_CHAMADA */

    function setPCH_NR_ORDEM($value) {
        $this->PCH_NR_ORDEM = $value;
    }

    /* End of SET PCH_NR_ORDEM */

    function setPCH_CHAMADA_ATUAL($value) {
        $this->PCH_CHAMADA_ATUAL = $value;
    }

    /* End of SET PCH_CHAMADA_ATUAL */

    function setPCH_NR_MAX_OPCAO_POLO($value) {
        $this->PCH_NR_MAX_OPCAO_POLO = $value;
    }

    /* End of SET PCH_NR_MAX_OPCAO_POLO */

    function setPCH_TXT_COMP_INSCRICAO($value) {
        $this->PCH_TXT_COMP_INSCRICAO = $value;
    }

    /* End of SET PCH_TXT_COMP_INSCRICAO */

    function setPCH_ADMITE_AREA($value) {
        $this->PCH_ADMITE_AREA = $value;
    }

    /* End of SET PCH_ADMITE_AREA */

    function setPCH_DT_PREV_RESUL_FINAL($value) {
        $this->PCH_DT_PREV_RESUL_FINAL = $value;
    }

    /* End of SET PCH_DT_PREV_RESUL_FINAL */

    function setPCH_DT_REG_RESUL_FINAL($value) {
        $this->PCH_DT_REG_RESUL_FINAL = $value;
    }

    /* End of SET PCH_DT_REG_RESUL_FINAL */

    function setPCH_ID_USU_RESP_RESUL_FIN($value) {
        $this->PCH_ID_USU_RESP_RESUL_FIN = $value;
    }

    /* End of SET PCH_ID_USU_RESP_RESUL_FIN */

    function setPCH_ADMITE_RESERVA_VAGA($value) {
        $this->PCH_ADMITE_RESERVA_VAGA = $value;
    }

    /* End of SET PCH_ADMITE_RESERVA_VAGA */
}

?>
