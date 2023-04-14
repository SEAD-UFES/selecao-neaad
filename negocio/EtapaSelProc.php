<?php

/**
 * tb_esp_etapa_sel_proc class
 * This class manipulates the table EtapaSelProc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 21/05/2014
 * */
global $CFG;
require_once $CFG->rpasta . "/negocio/CategoriaAvalProc.php";

class EtapaSelProc {

    private $ESP_ID_ETAPA_SEL;
    private $PRC_ID_PROCESSO;
    private $PCH_ID_CHAMADA;
    private $ESP_NR_ETAPA_SEL;
    private $ESP_DT_INI_RECURSO;
    private $ESP_DT_FIM_RECURSO;
    private $ESP_ST_CLASSIFICACAO;
    private $ESP_ST_ETAPA;
    private $ESP_ID_USUARIO_RESP_REC;
    private $ESP_ID_USUARIO_RESP_FIN;
    private $ESP_ID_USUARIO_RESP_CLAS;
    private $ESP_LOG_DT_REG_CLAS;
    private $ESP_LOG_DT_REG_REC;
    private $ESP_LOG_DT_REG_FIN;
    private $ESP_DT_PREV_RESUL_ETAPA;
    private $ESP_LOG_DT_REG_RESUL;
    private $ESP_ID_USUARIO_RESP_RESUL;
    private $ESP_DT_PREV_RESUL_REC;
    private $ESP_URL_ARQUIVO_RESUL;
    private $ESP_URL_ARQUIVO_POS_REC;
    private $EAP_ID_ETAPA_AVAL_PROC;
    private $ESP_ARQ_ATUALIZADO;
    private $ESP_ETAPA_ATIVA;
    private $ESP_ST_FINALIZACAO;
    private $ESP_PUB_DT_SOL_RESUL_PAR;
    private $ESP_PUB_DT_SOL_RESUL_FIN;
    private $ESP_PUB_USU_RESP_SOL_PAR;
    private $ESP_PUB_USU_RESP_SOL_FIN;
    // campos herdados
    private $ESP_PUB_NM_USU_RESP_SOL_PAR;
    private $ESP_PUB_NM_USU_RESP_SOL_FIN;
    // campos sobre demanda
    private $ultimaEtapa;
    private $resultadoPendente;
    private $opcaoFinalizacao;
    private $temInscritos;
    private $temRecursosEmAnalise;
    // situação da finalização
    public static $ST_FIM_RES_PARCIAL = "P";
    public static $ST_FIM_RES_POS_REC = "R";
    // situação da etapa
    public static $SIT_FECHADA = 'F';
    public static $SIT_ABERTA = 'A';
    public static $SIT_RECURSO = 'R';
    public static $SIT_FINALIZADA = 'O';
    // tipo de resultado pendente
    public static $PENDENTE_RESUL_PARCIAL = "P";
    public static $PENDENTE_RET_RESUL_PARCIAL = "R";
    public static $PENDENTE_RESUL_POS_REC = "E";
    public static $PENDENTE_RET_RESUL_POS_REC = "F";
    // situaçao da classificacao
    public static $CLASSIF_CONCLUIDA = 'C';
    public static $CLASSIF_PENDENTE = 'P';
    // controladores de interface
    private $exibirMsgAsterisco = FALSE;
    private static $TAG_protocolizar_RECURSO = "Protocolizar recurso";
    // casos de finalização automática ou forçada -> facilita o processamento do resultado final
    private static $FIM_SEM_INSCRICAO = 'F';
    private static $FIM_SEM_APP_RES_PARCIAL = 'P';
    private static $FIM_SEM_APP_RES_POS_REC = 'R';
    private static $FIM_ULTIMA_ETAPA = 'U';

    public static function getDsSituacao($tipo) {
        if ($tipo == self::$SIT_FECHADA) {
            return "Fechada";
        }
        if ($tipo == self::$SIT_ABERTA) {
            return "Aberta";
        }
        if ($tipo == self::$SIT_RECURSO) {
            return "Em Recurso";
        }
        if ($tipo == self::$SIT_FINALIZADA) {
            return "Finalizada";
        }
        return null;
    }

    public static function getListaSitDsSituacao() {
        $ret = array(
            self::$SIT_FECHADA => self::getDsSituacao(self::$SIT_FECHADA),
            self::$SIT_ABERTA => self::getDsSituacao(self::$SIT_ABERTA),
            self::$SIT_RECURSO => self::getDsSituacao(self::$SIT_RECURSO),
            self::$SIT_FINALIZADA => self::getDsSituacao(self::$SIT_FINALIZADA));

        return $ret;
    }

    public static function getDsClassif($tipo) {
        if ($tipo == self::$CLASSIF_CONCLUIDA) {
            return "Classificação concluída";
        }
        if ($tipo == self::$CLASSIF_PENDENTE) {
            return "Classificação pendente";
        }
        return null;
    }

    public static function getListaClasDsClassif() {
        $ret = array(
            self::$CLASSIF_CONCLUIDA => self::getDsSituacao(self::$CLASSIF_CONCLUIDA),
            self::$CLASSIF_PENDENTE => self::getDsSituacao(self::$CLASSIF_PENDENTE));

        return $ret;
    }

    private function getHtmlFinalizacaoForcada($tpApresentacao) {
        if ($this->isFinalizacaoForcada($tpApresentacao)) {
            return "<i>Sem aprovados</i>";
        }
    }

    /* Construtor padrão da classe */

    public function __construct($ESP_ID_ETAPA_SEL, $PRC_ID_PROCESSO, $PCH_ID_CHAMADA, $ESP_NR_ETAPA_SEL, $ESP_DT_INI_RECURSO, $ESP_DT_FIM_RECURSO, $ESP_ST_CLASSIFICACAO, $ESP_ST_ETAPA, $ESP_ID_USUARIO_RESP_REC, $ESP_ID_USUARIO_RESP_FIN, $ESP_ID_USUARIO_RESP_CLAS, $ESP_LOG_DT_REG_CLAS, $ESP_LOG_DT_REG_REC, $ESP_LOG_DT_REG_FIN, $ESP_DT_PREV_RESUL_ETAPA = NULL, $ESP_LOG_DT_REG_RESUL = NULL, $ESP_ID_USUARIO_RESP_RESUL = NULL, $ESP_DT_PREV_RESUL_REC = NULL, $ESP_URL_ARQUIVO_RESUL = NULL, $ESP_URL_ARQUIVO_POS_REC = NULL, $EAP_ID_ETAPA_AVAL_PROC = NULL, $ESP_ARQ_ATUALIZADO = NULL, $ESP_ETAPA_ATIVA = NULL, $ESP_ST_FINALIZACAO = NULL, $ESP_PUB_DT_SOL_RESUL_PAR = NULL, $ESP_PUB_DT_SOL_RESUL_FIN = NULL, $ESP_PUB_USU_RESP_SOL_PAR = NULL, $ESP_PUB_USU_RESP_SOL_FIN = NULL) {
        $this->ESP_ID_ETAPA_SEL = $ESP_ID_ETAPA_SEL;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->ESP_NR_ETAPA_SEL = $ESP_NR_ETAPA_SEL;
        $this->ESP_DT_INI_RECURSO = $ESP_DT_INI_RECURSO;
        $this->ESP_DT_FIM_RECURSO = $ESP_DT_FIM_RECURSO;
        $this->ESP_ST_CLASSIFICACAO = $ESP_ST_CLASSIFICACAO;
        $this->ESP_ST_ETAPA = $ESP_ST_ETAPA;
        $this->ESP_ID_USUARIO_RESP_REC = $ESP_ID_USUARIO_RESP_REC;
        $this->ESP_ID_USUARIO_RESP_FIN = $ESP_ID_USUARIO_RESP_FIN;
        $this->ESP_ID_USUARIO_RESP_CLAS = $ESP_ID_USUARIO_RESP_CLAS;
        $this->ESP_LOG_DT_REG_CLAS = $ESP_LOG_DT_REG_CLAS;
        $this->ESP_LOG_DT_REG_REC = $ESP_LOG_DT_REG_REC;
        $this->ESP_LOG_DT_REG_FIN = $ESP_LOG_DT_REG_FIN;
        $this->ESP_DT_PREV_RESUL_ETAPA = $ESP_DT_PREV_RESUL_ETAPA;
        $this->ESP_LOG_DT_REG_RESUL = $ESP_LOG_DT_REG_RESUL;
        $this->ESP_ID_USUARIO_RESP_RESUL = $ESP_ID_USUARIO_RESP_RESUL;
        $this->ESP_DT_PREV_RESUL_REC = $ESP_DT_PREV_RESUL_REC;
        $this->ESP_URL_ARQUIVO_RESUL = $ESP_URL_ARQUIVO_RESUL;
        $this->ESP_URL_ARQUIVO_POS_REC = $ESP_URL_ARQUIVO_POS_REC;
        $this->EAP_ID_ETAPA_AVAL_PROC = $EAP_ID_ETAPA_AVAL_PROC;
        $this->ESP_ARQ_ATUALIZADO = $ESP_ARQ_ATUALIZADO;
        $this->ESP_ETAPA_ATIVA = $ESP_ETAPA_ATIVA;
        $this->ESP_ST_FINALIZACAO = $ESP_ST_FINALIZACAO;
        $this->ESP_PUB_DT_SOL_RESUL_PAR = $ESP_PUB_DT_SOL_RESUL_PAR;
        $this->ESP_PUB_DT_SOL_RESUL_FIN = $ESP_PUB_DT_SOL_RESUL_FIN;
        $this->ESP_PUB_USU_RESP_SOL_PAR = $ESP_PUB_USU_RESP_SOL_PAR;
        $this->ESP_PUB_USU_RESP_SOL_FIN = $ESP_PUB_USU_RESP_SOL_FIN;

        $this->ultimaEtapa = NULL;
        $this->resultadoPendente = NULL;
        $this->opcaoFinalizacao = NULL;
        $this->temInscritos = NULL;
        $this->temRecursosEmAnalise = NULL;
    }

#   ----------------------------- FUNÇÕES DE MANIPULAÇÃO DE ARQUIVOS -----------------------------------------

    /**
     * Esta função retorna o nome padrão, que deve estar contido em todos os arquivos relacionados a etapa
     * 
     * ATENÇÃO: Não é necessário chamar a função padrão da chamada após chamar esta função
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    public function ARQS_getPadraoNomeArqsEtapa($processo, $chamada) {
        return "{$chamada->ARQS_getPadraoNomeArqsChamada($processo)}-etapa{$this->ESP_NR_ETAPA_SEL}";
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function ARQS_iniNomeArquivoResulParcial($processo, $chamada) {
        return "ResulParcial-{$this->ARQS_getPadraoNomeArqsEtapa($processo, $chamada)}";
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function ARQS_nomeArquivoResulParcial($processo, $chamada) {
        return "{$this->ARQS_iniNomeArquivoResulParcial($processo, $chamada)}" . AcompProcChamada::$TIPO_PDF;
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function ARQS_getUrlArqResulParcial($processo, $chamada) {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$processo->getNomePastaEdital()}{$this->ARQS_nomeArquivoResulParcial($processo, $chamada)}";
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function ARQS_getIniUrlArqResulParcial($processo, $chamada) {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$processo->getNomePastaEdital()}{$this->ARQS_iniNomeArquivoResulParcial($processo, $chamada)}";
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function ARQS_iniNomeArquivoResulPosRec($processo, $chamada) {
        return "ResulPosRec-{$this->ARQS_getPadraoNomeArqsEtapa($processo, $chamada)}";
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function ARQS_nomeArquivoResulPosRec($processo, $chamada) {
        return "{$this->ARQS_iniNomeArquivoResulPosRec($processo, $chamada)}" . AcompProcChamada::$TIPO_PDF;
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function ARQS_getUrlArqResulPosRec($processo, $chamada) {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$processo->getNomePastaEdital()}{$this->ARQS_nomeArquivoResulPosRec($processo, $chamada)}";
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function ARQS_getIniUrlArqResulPosRec($processo, $chamada) {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$processo->getNomePastaEdital()}{$this->ARQS_iniNomeArquivoResulPosRec($processo, $chamada)}";
    }

    public function getUrlArquivoResulParcial() {
        global $CFG;

        // redirecionando para erro
        if (Util::vazioNulo($this->ESP_URL_ARQUIVO_RESUL)) {
            return "{$CFG->rwww}/404.php?err=arq";
        }

        // verificando caso de arquivo externo
        if (!file_exists("{$CFG->rpasta}/{$this->ESP_URL_ARQUIVO_RESUL}")) {
            $proc = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            return "http://neaad.ufes.br/conteudo/edital-n%C2%BA-{$proc->getPRC_NR_EDITAL()}{$proc->getPRC_ANO_EDITAL()}";
        }

        return "{$CFG->rwww}/" . $this->ESP_URL_ARQUIVO_RESUL;
    }

    public function getUrlArquivoResulPosRec() {
        global $CFG;

        // redirecionando para erro
        if (Util::vazioNulo($this->ESP_URL_ARQUIVO_POS_REC)) {
            return "{$CFG->rwww}/404.php?err=arq";
        }

        // verificando caso de arquivo externo
        if (!file_exists("{$CFG->rpasta}/{$this->ESP_URL_ARQUIVO_POS_REC}")) {
            $proc = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            return "http://neaad.ufes.br/conteudo/edital-n%C2%BA-{$proc->getPRC_NR_EDITAL()}{$proc->getPRC_ANO_EDITAL()}";
        }

        return "{$CFG->rwww}/" . $this->ESP_URL_ARQUIVO_POS_REC;
    }

#   ----------------------------- FIM FUNÇÕES DE MANIPULAÇÃO DE ARQUIVOS -------------------------------------
#   
#   
#   
#  
#   ----------------------------------- FUNÇÕES DE VERIFICAÇÃO DE STATUS --------------------------------------

    public function isFechada() {
        return $this->ESP_ST_ETAPA == self::$SIT_FECHADA;
    }

    public function isAberta() {
        return $this->ESP_ST_ETAPA == self::$SIT_ABERTA;
    }

    public function isEmRecurso() {
        return $this->ESP_ST_ETAPA == self::$SIT_RECURSO;
    }

    public function isEmPeriodoRecurso() {
        return $this->isAtiva() && $this->isEmRecurso() && dt_dataPertenceIntervalo(dt_getTimestampDtUS(), dt_getTimestampDtBR($this->ESP_DT_INI_RECURSO), dt_getTimestampDtBR($this->ESP_DT_FIM_RECURSO));
    }

    public function isPeriodoRecursoPosterior() {
        return dt_dataMaior(dt_getTimestampDtBR($this->ESP_DT_INI_RECURSO), dt_getTimestampDtUS());
    }

    public function isPeriodoRecursoAnterior() {
        return dt_dataMenor(dt_getTimestampDtBR($this->ESP_DT_FIM_RECURSO), dt_getTimestampDtUS());
    }

    public function isFinalizada() {
        return $this->ESP_ST_ETAPA == self::$SIT_FINALIZADA;
    }

    public function isProcessamentoResulEtapa() {
        return $this->isPeriodoRecursoAnterior() && $this->publicouResultadoParcial() && !$this->isFinalizada();
    }

    public function isEtapaCorrente() {
        return $this->isAtiva() && ($this->isAberta() || $this->isEmRecurso());
    }

    public function isAtiva() {
        return $this->ESP_ETAPA_ATIVA != NULL && $this->ESP_ETAPA_ATIVA == FLAG_BD_SIM;
    }

    public function publicouResultadoParcial() {
        return !Util::vazioNulo($this->ESP_LOG_DT_REG_RESUL);
    }

    public function publicouResultadoPosRec() {
        return !Util::vazioNulo($this->ESP_LOG_DT_REG_REC);
    }

    public function isUltimaEtapa() {
        if ($this->ultimaEtapa === NULL) {
            // carregando
            $this->ultimaEtapa = EtapaAvalProc::contarEtapaAvalPorProc($this->PRC_ID_PROCESSO) == $this->ESP_NR_ETAPA_SEL;
        }
        return $this->ultimaEtapa;
    }

    public function isArquivoAtualizado() {
        return $this->ESP_ARQ_ATUALIZADO != NULL && $this->ESP_ARQ_ATUALIZADO = FLAG_BD_SIM;
    }

    public static function possuiAvaliacaoAuto($idChamada) {
        try {
            // buscando etapa em andamento
            $etapa = self::buscarEtapaEmAndamento($idChamada);

            if ($etapa == NULL) {
                return FALSE; // não existe etapa em andamento
            }

            // contando categorias de avaliação automática
            $qtAvalAuto = CategoriaAvalProc::contarCatAvalPorProcNrEtapaTp($etapa->PRC_ID_PROCESSO, $etapa->ESP_NR_ETAPA_SEL, CategoriaAvalProc::$AVAL_AUTOMATICA);

            // retornando sem avaliacao
            return $qtAvalAuto != 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar existência de avaliações automáticas para a chamada.", $e);
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param EtapaSelProc $etapaVigente
     */
    public static function temResultadoPendente($chamada, $etapaVigente) {
        return ($etapaVigente != NULL && !$chamada->isFinalizada() && !$chamada->isFechada()) && !($etapaVigente->isFinalizada() && $etapaVigente->isArquivoAtualizado());
    }

    public static function permiteMostrarRecurso($idChamada) {
        try {
            $stRec = self::$SIT_RECURSO;
            $stFin = self::$SIT_FINALIZADA;
            $temp = self::buscarEtapaPorRestricao($idChamada, "(ESP_ST_ETAPA = '$stRec' || ESP_ST_ETAPA = '$stFin')", TRUE);
            return $temp != NULL;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar disponibilidade do botão de recurso.", $e);
        }
    }

    public static function permiteMostrarProtocolizacaoRecurso($idChamada, $idInscricao) {
        try {
            // verificando etapa em recurso
            $stRec = self::$SIT_RECURSO;
            $etapaSel = self::buscarEtapaPorRestricao($idChamada, "(ESP_ST_ETAPA = '$stRec')", TRUE);

            if ($etapaSel != NULL) {

                // verificando se já tem recurso
                $qtRecurso = RecursoResulProc::contarRecursosPorInscricao($idInscricao, $etapaSel->ESP_ID_ETAPA_SEL);

                // está em período de recurso e não tem recurso
                return $etapaSel->isEmPeriodoRecurso() && $qtRecurso == 0;
            }

            // sequer tem etapa em recurso
            return FALSE;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar disponibilidade do botão de recurso.", $e);
        }
    }

    /**
     * 
     * @param EtapaSelProc $etapa
     * @return boolean Boolean informando se pode ou não mostrar a classificação
     */
    public static function permiteMostrarClassificacao($etapa) {
        return $etapa != NULL && $etapa->ESP_ST_CLASSIFICACAO == self::$CLASSIF_CONCLUIDA;
    }

    public function permiteMostrarClassificacaoObj() {
        return self::permiteMostrarClassificacao($this);
    }

    public function isFinalizacaoForcada($tpApresentacao = NULL) {
        if ($this->ESP_ST_FINALIZACAO == NULL) {
            return FALSE; // finalização normal
        }

        // tipo de apresentação nula: Verificar finalização forçada em qualquer caso
        if ($tpApresentacao == NULL) {
            return $this->ESP_ST_FINALIZACAO != NULL;
        }

        //
        //
        // Casos: É resultado parcial e a etapa parou em resultado parcial ou
        // é recurso e a etapa parou em resultado pós recurso ou resultado parcial
        return ($tpApresentacao == self::$PENDENTE_RESUL_PARCIAL && $this->ESP_ST_FINALIZACAO == self::$ST_FIM_RES_PARCIAL) || ($tpApresentacao == self::$PENDENTE_RESUL_POS_REC && ($this->ESP_ST_FINALIZACAO == self::$ST_FIM_RES_POS_REC || $this->ESP_ST_FINALIZACAO == self::$ST_FIM_RES_PARCIAL));
    }

#   ----------------------------------- FIM FUNÇÕES DE VERIFICAÇÃO DE STATUS ------------------------------------
#   
#   
#   
#   ----------------------------------- FUNÇÕES DE VALIDAÇÃO --------------------------------------------------   
    /**
     * Verifica se a etapa esta no periodo de recurso
     * @param int $idEtapa
     * @return boolean
     * @throws NegocioException
     */

    public static function validaPeriodoRecurso($idEtapa) {
        try {
            // etapa nula?
            if ($idEtapa == NULL) {
                return FALSE; // não tem etapa em recurso
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $stRec = self::$SIT_RECURSO;

            $sql = "select UNIX_TIMESTAMP(`ESP_DT_INI_RECURSO`) as ESP_DT_INI_RECURSO,
                UNIX_TIMESTAMP(`ESP_DT_FIM_RECURSO`) as ESP_DT_FIM_RECURSO
                    from tb_esp_etapa_sel_proc
                    where ESP_ID_ETAPA_SEL = '$idEtapa'
                        and ESP_ST_ETAPA = '$stRec'
                        and ESP_DT_INI_RECURSO IS NOT NULL
                        and ESP_DT_FIM_RECURSO IS NOT NULL";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // verificando existencia
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                // nao ha recurso aberto
                return FALSE;
            }

            // verificando campos
            $linha = ConexaoMysql::getLinha($resp);
            $dtAbertura = $linha["ESP_DT_INI_RECURSO"];
            $dtFechamento = $linha["ESP_DT_FIM_RECURSO"];
            $dtAtual = dt_getTimestampDtUS();
//            print_r($dtAbertura . " " . $dtFechamento . " atual: " . $dtAtual);
            // verificando se esta dentro do periodo de inscriçao
            return dt_dataPertenceIntervalo($dtAtual, $dtAbertura, $dtFechamento);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar período de recurso.", $e);
        }
    }

    /**
     * Função que verifica se é possível classificar os candidatos. 
     * 
     * Basicamente, esta funcao analisa se nao existem categorias manuais sem preenchimento,
     * se todas as avaliações foram revisadas e se a etapa permite classificaçao.
     * 
     * @param int $idChamada
     *
     * @return array - Array tipo chave/valor. Caso queira verificar apenas validaçao, use a chave 'val'.
     * No caso de erro, a chave 'msg' contem a mensagem de erro.
     * 
     * @throws NegocioException
     */
    public static function validarClassifCands($idChamada) {
        global $CFG;
        try {
            // recuperando dados e verificando se existe etapa em aberto
            $chamada = ProcessoChamada::buscarChamadaPorId($idChamada);
            $etapa = self::buscarEtapaEmAndamento($idChamada);

            // se nao existir, retornando com erro
            if ($etapa == NULL) {

                // verificando caso de chamada finalizada
                // @todo Modificação necessária para habilitar resultado pós-recursos
                if ($chamada->isFinalizada() || $chamada->isAguardandoFechamentoAuto()) {
                    return array("val" => FALSE, "errEtapa" => TRUE, "msg" => "Processo de avaliação finalizado.");
                }

                return array("val" => FALSE, "errEtapa" => TRUE, "msg" => "Não é possível classificar os candidatos, pois não existe uma etapa de seleção em andamento.");
            }

            // validando categorias de avaliação e itens
            $temp = CategoriaAvalProc::validarCatAvalProcParaAvaliacao($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC());
            if (!$temp["val"]) {
                return array("val" => FALSE, "errEtapa" => TRUE, "msg" => "Não é possível classificar os candidatos. {$temp["msg"]}");
            }
            $temp = ItemAvalProc::validarItensAvalProcParaAvaliacao($etapa->getPRC_ID_PROCESSO());
            if (!$temp["val"]) {
                return array("val" => FALSE, "errEtapa" => TRUE, "msg" => "Não é possível classificar os candidatos. {$temp["msg"]}");
            }

            // recuperando identificaçao da etapa
            $dsEtapa = $etapa->getNomeEtapa();

            // verificando se há candidatos inscritos
            if (!$etapa->temCandidatosInscritos($chamada)) {
                // erro: não tem candidatos inscritos
                return array("val" => FALSE, "etapa" => "$dsEtapa", "msg" => "Não há candidatos inscritos nesta chamada do edital.");
            }

            // verificando se existe avaliação automática por fazer.
            $avalAFazer = InscricaoProcesso::existeAvalAutoAFazer($etapa);

            // Se existe avaliação a fazer, disparar erro
            if ($avalAFazer) {
                // erro: é necessário executar avaliação automática
                return array("val" => FALSE, "etapa" => "$dsEtapa", "msg" => "Existem avaliações automáticas que precisam ser processadas antes da classificação. Por favor, <a title='Ir para a página de avaliação automática' href='$CFG->rwww/visao/inscricaoProcesso/avaliacaoAutomatica.php?idProcesso={$chamada->getPRC_ID_PROCESSO()}'>execute a avaliação automática</a> antes de classificar os candidatos.");
            }

            // verificando se a classificação esta atualizada
            if ($etapa->permiteMostrarClassificacaoObj()) {
                // erro: classificacao atualizada
                return array("val" => FALSE, "etapa" => "$dsEtapa", "msg" => "Não é necessário classificar os candidatos, pois a classificação está atualizada.");
            }

            // verificando se existem avaliação de itens de informação complementar não realizada
            $qtRespNaoAval = RespAnexoProc::contarRespNaoAvaliadaPorCham($idChamada);
            if ($qtRespNaoAval != 0) {
                // erro de avaliaçoes cegas
                return array("val" => FALSE, "etapa" => "$dsEtapa", "msg" => "Não é possível executar a avaliação automática dos candidatos, pois existem avaliações manuais de informações complementares não registradas. Por favor, acesse a página de <a title='Ir para a página de avaliação cega' href='$CFG->rwww/visao/inscricaoProcesso/listarAvaliacaoCegaInsc.php'>Avaliação Cega</a> para registrar as avaliações.");
            }

            // validando se todas as notas manuais foram preenchidas
            $notaManOk = InscricaoProcesso::preencheuTodaNotaManual($etapa->PCH_ID_CHAMADA, $etapa->PRC_ID_PROCESSO, $etapa->ESP_NR_ETAPA_SEL);
            if (!$notaManOk) {
                // erro: Falta preencher notas manuais
                return array("val" => FALSE, "etapa" => "$dsEtapa", "msg" => "Não é possível classificar os candidatos, pois existem inscrições cujos itens de avaliação manual não estão registrados no sistema. Por favor, <strong>preencha todos os itens de avaliação manual</strong> de todos os candidatos e tente novamente.");
            }

            // validando se todas as notas foram validadas
            $qtInscSemAnalise = InscricaoProcesso::contarInscSemAnaliseHumana($etapa->PRC_ID_PROCESSO, $etapa->PCH_ID_CHAMADA);
            if ($qtInscSemAnalise != 0) {
                // erro de avaliaçoes cegas
                return array("val" => FALSE, "etapa" => "$dsEtapa", "msg" => "Não é possível classificar os candidatos, pois existem inscrições cujas avaliações não foram validadas. Por favor, <strong>revise</strong> todas as notas e tente novamente.");
            }

            // Tudo ok. Permitindo classificacao
            return array("val" => TRUE, "etapa" => "$dsEtapa");
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar classificação dos candidatos.", $e);
        }
    }

    /**
     * Funçao que verifica se é possivel avaliar os candidatos automaticamente. 
     * 
     * Basicamente, esta função analisa se existem categorias de avaliação automática e se as informações complementares foram avaliadas.
     * 
     * @param int $idChamada
     * 
     * @return array - Array tipo chave/valor. Caso queira verificar apenas validaçao, use a chave 'val'.
     * No caso de erro, a chave 'msg' contem a mensagem de erro.
     * 
     * @throws NegocioException
     */
    public static function validarExecAvalAutomatica($idChamada) {
        global $CFG;
        try {
            // verificando se existe etapa em aberto
            $etapa = self::buscarEtapaEmAndamento($idChamada);

            // se nao existir, retornando com erro
            if ($etapa == NULL) {
                return array("val" => FALSE, "errEtapa" => TRUE, "msg" => "Não é possível executar a avaliação automática, pois não existe uma etapa de seleção em andamento.");
            }

            // validando categorias de avaliação e itens
            $temp = CategoriaAvalProc::validarCatAvalProcParaAvaliacao($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC());
            if (!$temp["val"]) {
                return array("val" => FALSE, "errEtapa" => TRUE, "msg" => "Não é possível executar a avaliação automática. {$temp["msg"]}");
            }
            $temp = ItemAvalProc::validarItensAvalProcParaAvaliacao($etapa->getPRC_ID_PROCESSO());
            if (!$temp["val"]) {
                return array("val" => FALSE, "errEtapa" => TRUE, "msg" => "Não é possível executar a avaliação automática. {$temp["msg"]}");
            }

            // recuperando identificaçao da etapa
            $dsEtapa = $etapa->getNomeEtapa();

            // verificando se existe avaliação automática por fazer.
            $avalAFazer = InscricaoProcesso::existeAvalAutoAFazer($etapa);

            // Se não existe avaliação a fazer, disparar erro
            if (!$avalAFazer) {
                // erro: Não há avaliações automáticas a fazer
                return array("val" => FALSE, "etapa" => "$dsEtapa", "msg" => "Nenhuma avaliação automática precisa ser feita. Nada a fazer...");
            }

            // verificando se existem avaliação de itens de informação complementar não realizada
            $qtRespNaoAval = RespAnexoProc::contarRespNaoAvaliadaPorCham($idChamada);
            if ($qtRespNaoAval != 0) {
                // erro de avaliaçoes cegas
                return array("val" => FALSE, "etapa" => "$dsEtapa", "msg" => "Não é possível executar a avaliação automática dos candidatos, pois existem avaliações manuais de informações complementares não registradas. Por favor, acesse a página de <a title='Ir para a página de avaliação cega' href='$CFG->rwww/visao/inscricaoProcesso/listarAvaliacaoCegaInsc.php'>Avaliação Cega</a> para registrar as avaliações.");
            }

            // tudo ok. Permitindo avaliação automática
            return array("val" => TRUE, "etapa" => "$dsEtapa");
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar execução de avaliação automática dos candidatos.", $e);
        }
    }

    /**
     * Função que verifica se é possível realizar a publicação de um resultado pendente
     * 
     * Para cada tipo de resultado pendente há uma verificação específica
     * 
     * @param ProcessoChamada $chamada
     * @param EtapaSelProc $etapaVigente Etapa de seleção vigente
     *
     * @return array - Array tipo chave/valor.
     * Caso queira verificar apenas validaçao, use a chave 'val'.
     * No caso de erro, outras chaves podem ser acessadas:
     * - 'msg'      Contem a mensagem de erro
     * - 'html'     Contém a mensagem de erro em uma versão html
     * - 'classe'   Contém a classe a ser aplicada na mensagem de erro para exibições em HTML
     * 
     * @throws NegocioException
     */
    public static function validarPublicacaoResulPendente($chamada, $etapaVigente) {
        global $CFG;
        try {

            // verificando se tem resultado pendente
            if (!self::temResultadoPendente($chamada, $etapaVigente)) {
                // se não tem, dispara erro
                $msgErro = "Não há resultado pendente de publicação.";
                return array("val" => FALSE, "msg" => $msgErro, "html" => $msgErro, "classe" => "info");
            }

            // tratando os diversos casos de resultado pendente
            $resulPendente = $etapaVigente->getResultadoPendente();

            // casos comuns
            //
            
            // classsificação atualizada, se tem candidato
            if ($etapaVigente->temCandidatosInscritos($chamada) && !$etapaVigente->permiteMostrarClassificacaoObj()) {
                $msgErro = "A classificação dos candidatos não está concluída ou está desatualizada. Classifique os candidatos para publicar o resultado.";
                return array("val" => FALSE, "msg" => $msgErro, "html" => $msgErro, "classe" => "danger");
            }

            // casos específicos
            // resultado parcial
            if ($resulPendente[0] == self::$PENDENTE_RESUL_PARCIAL) {
                // ainda está no período de inscrição 
                if ($chamada->isEmPeriodoInscricao()) {
                    $msgErro = "Aguarde o término do período de inscrição para publicar o resultado.";
                    return array("val" => FALSE, "msg" => $msgErro, "html" => $msgErro, "classe" => "warning");
                }
            }

            // resultado pós recurso
            if ($resulPendente[0] == self::$PENDENTE_RESUL_POS_REC) {
                // ainda está no período de recursos
                if (!$etapaVigente->isPeriodoRecursoAnterior()) {
                    $msgErro = "Aguarde o término do período de recursos para publicar o resultado.";
                    return array("val" => FALSE, "msg" => $msgErro, "html" => $msgErro, "classe" => "warning");
                }

                // Existem recursos em análise
                if ($etapaVigente->temRecursosEmAnalise($chamada)) {
                    $msgErro = "Há recursos não analisados. Responda todos os recursos para publicar o resultado.";
                    return array("val" => FALSE, "msg" => $msgErro, "html" => $msgErro, "classe" => "danger");
                }
            }

            // Apenas o Admin pode publicar resultados
            if (getTipoUsuarioLogado() != Usuario::$USUARIO_ADMINISTRADOR) {
                $msgErro = "Tudo certo para a publicação do resultado. Solicite ao Administrador a publicação.";

                // preparando msg html
                $msgErroHtml = "<p style='font-weight:bold;color:#356635'>
                                    <i class='fa fa-check'></i> Tudo certo para a publicação do resultado.
                                </p>";

                // adicionando link para ver arquivo
                $form = "<form id='formPreviaResultado' target='previaResultado' method='post' action='$CFG->rwww/visao/relatorio/gerarPDFResultado.php?acao=previaResultado'>
                                        <input type='hidden' name='valido' value='resultado'>
                                        <input type='hidden' name='idProcesso' value='$etapaVigente->PRC_ID_PROCESSO'>
                                        <input type='hidden' name='idChamada' value='$etapaVigente->PCH_ID_CHAMADA'>
                                        <input type='hidden' name='idEtapaSel' value='$etapaVigente->ESP_ID_ETAPA_SEL'>
                        </form>";


                $msgErroHtml .= "$form
                                <p><button onclick='javascript: abrirPreviaResultado();' class='btn btn-info btn-sm' type='button'>
                                    Visualizar prévia do documento a ser publicado
                                </button></p>";

                // adicionando de acordo com o caso
                if ($etapaVigente->isSolicitouPublicacao($resulPendente[0])) {
                    $dtSol = $etapaVigente->getPubDtSolResul($resulPendente[0]);
                    $respSol = $etapaVigente->getPubUsuRespSol($resulPendente[0]);
                    $infoSol = "<p>Solicitação de publicação realizada em <b>$dtSol</b> por <b>$respSol</b>. Por favor, aguarde a publicação.</p>";

                    $msgErroHtml .= "$infoSol";
                } else {
                    // solicitar publicação
                    $divSolPub = "<div id='divSolPublicacaoResul'><hr style='border-color:#b9ceab;'><button onclick=\"javascript: solicitarPublicacao('$etapaVigente->PRC_ID_PROCESSO', '$etapaVigente->PCH_ID_CHAMADA', '$etapaVigente->ESP_ID_ETAPA_SEL');\" class='btn btn-info btn-sm' type='button'>Solicitar publicação ao Administrador</button></div>";
                    $divMsgSolPub = "<div id='mensagemSolPublicacaoResul' style='display: none'>Aguarde, processando...</div>";

                    $msgErroHtml .= "$divSolPub{$divMsgSolPub}";
                }

                return array("val" => FALSE, "permissao" => TRUE, "msg" => $msgErro, "html" => $msgErroHtml, "classe" => "success");
            }

            // Tudo ok. Permitindo publicação
            return array("val" => TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar classificação dos candidatos.", $e);
        }
    }

    public function getPubDtSolResul($tpPublicacao) {
        return $tpPublicacao == self::$PENDENTE_RESUL_PARCIAL || $tpPublicacao == self::$PENDENTE_RET_RESUL_PARCIAL ? $this->ESP_PUB_DT_SOL_RESUL_PAR : $this->ESP_PUB_DT_SOL_RESUL_FIN;
    }

    public function getPubUsuRespSol($tpPublicacao) {
        return $tpPublicacao == self::$PENDENTE_RESUL_PARCIAL || $tpPublicacao == self::$PENDENTE_RET_RESUL_PARCIAL ? $this->ESP_PUB_NM_USU_RESP_SOL_PAR : $this->ESP_PUB_NM_USU_RESP_SOL_FIN;
    }

    public function isSolicitouPublicacao($tpPublicacao) {
        // resultado parcial
        if ($tpPublicacao == self::$PENDENTE_RESUL_PARCIAL || $tpPublicacao == self::$PENDENTE_RET_RESUL_PARCIAL) {
            return !Util::vazioNulo($this->ESP_PUB_DT_SOL_RESUL_PAR);
        } elseif ($tpPublicacao == self::$PENDENTE_RESUL_POS_REC || $tpPublicacao == self::$PENDENTE_RET_RESUL_POS_REC) {
            // resultado pós recurso
            return !Util::vazioNulo($this->ESP_PUB_DT_SOL_RESUL_FIN);
        }

        throw new NegocioException("Tipo de publicação não programado em 'isSolicitouPublicacao'");
    }

#   ----------------------------------- FIM FUNÇÕES DE VALIDAÇÃO --------------------------------------------------   

    /**
     * Função que solicita a publicação de um resultado ao administrador
     * 
     * @param Processo $processo
     * @return array Array na forma [situacao, msgErro] informando o status da solicitação em situacao e, em caso de erro,
     * informa as dependências em msgErro.
     *  
     * @throws NegocioException
     */
    public function solicitarPublicacao($processo) {
        $usuResp = getIdUsuarioLogado();
        try {

            // realizando as devidas validações
            $chamada = buscarChamadaPorIdCT($this->PCH_ID_CHAMADA);
            $valPublicacao = EtapaSelProc::validarPublicacaoResulPendente($chamada, $this);
            if (!$valPublicacao['val'] && !(isset($valPublicacao['permissao']) && $valPublicacao['permissao'])) {
                return array(FALSE, $valPublicacao['msg']);
            }

            // verificando rechamada
            $resulPendente = $this->getResultadoPendente();
            if ($this->isSolicitouPublicacao($resulPendente[0])) {
                return array(FALSE, "Solicitação de publicação já solicitada!");
            }

            // Tudo Ok. Procedendo com a solicitação...
            $conexao = NGUtil::getConexao();

            // criando sql de solicitação
            // 
            // caso resul parcial
            if ($resulPendente[0] == self::$PENDENTE_RESUL_PARCIAL || $resulPendente[0] == self::$PENDENTE_RET_RESUL_PARCIAL) {
                $sql = "update tb_esp_etapa_sel_proc set ESP_PUB_DT_SOL_RESUL_PAR = now(), ESP_PUB_USU_RESP_SOL_PAR = '$usuResp' where esp_id_etapa_sel = '$this->ESP_ID_ETAPA_SEL'";
            } elseif ($resulPendente[0] == self::$PENDENTE_RESUL_POS_REC || $resulPendente[0] == self::$PENDENTE_RET_RESUL_POS_REC) {
                // resultado pós recurso
                $sql = "update tb_esp_etapa_sel_proc set ESP_PUB_DT_SOL_RESUL_FIN = now(), ESP_PUB_USU_RESP_SOL_FIN = '$usuResp' where esp_id_etapa_sel = '$this->ESP_ID_ETAPA_SEL'";
            }

            // persistindo no BD
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao solicitar publicação de resultado da chamada.", $e);
        }

        // enviando email aos administradores
        Usuario::enviarNotSolPubResultadoChamAdmin($processo, $chamada, $resulPendente, $usuResp);

        // retornando tudo certo
        return [TRUE, ""];
    }

    public function getDsPeriodoRecurso($PreencherVazio = TRUE) {
        if (Util::vazioNulo($this->ESP_DT_INI_RECURSO)) {
            return $PreencherVazio ? Util::$STR_CAMPO_VAZIO : "";
        }
        return "$this->ESP_DT_INI_RECURSO a $this->ESP_DT_FIM_RECURSO";
    }

    public function getNomeEtapa() {
        return "Etapa {$this->ESP_NR_ETAPA_SEL}";
    }

    /**
     * @param boolean $apresentacaoTabInsc Informa se o objetivo do retorno é apenas para apresentação dos dados na tabela de inscritos em um edital
     * 
     * OBS: Utilize a opção $apresentacaoTabInsc = TRUE com cuidado. Esta opção deve ser utilizada apenas para fins de visualização
     * 
     *  @return array Vetor informando o tipo de resultado pendente e a descrição do resultado
     * na forma [tipoResultadoPendente, DsResultadoPendente] ou uma string, se a opção $apresentacaoTabInsc = TRUE.
     * 
     * @throws NegocioException
     */
    public function getResultadoPendente($apresentacaoTabInsc = FALSE) {
        if ($this->isFechada()) {
            if (!$apresentacaoTabInsc) {
                //erro: Sem necessidade de atualização
                throw new NegocioException("Nenhum resultado pendente para esta chamada do edital!");
            } else {
                return "";
            }
        }

        // carregando...
        if ($this->resultadoPendente == NULL || $apresentacaoTabInsc) {
            if ($this->isAberta()) {
                // Ainda não foi publicado o resultado parcial
                //
                $temp = "Resultado parcial da {$this->getNomeEtapa()}";
                if (!$apresentacaoTabInsc) {
                    $this->resultadoPendente = array(self::$PENDENTE_RESUL_PARCIAL, $temp);
                } else {
                    return $temp;
                }
            } elseif (!$apresentacaoTabInsc && $this->isEmRecurso() && !$this->isArquivoAtualizado() && !$this->isPeriodoRecursoAnterior()) {
                // foi publicado o resultado parcial, mas o arquivo de resultado está desatualizado e ainda não terminou o período de recurso
                //
                $this->resultadoPendente = array(self::$PENDENTE_RET_RESUL_PARCIAL, "Retificação do resultado parcial da {$this->getNomeEtapa()}");
            } elseif ($this->isEmRecurso()) {
                // Arquivo está atualizado e a etapa está em recurso
                //
                $temp = "Resultado pós-recursos da {$this->getNomeEtapa()}";
                if (!$apresentacaoTabInsc) {
                    $this->resultadoPendente = array(self::$PENDENTE_RESUL_POS_REC, $temp);
                } else {
                    return $temp;
                }
            } elseif (!$apresentacaoTabInsc && !$this->isArquivoAtualizado()) {
                // Edital está finalizado mas o arquivo está desatualizado
                //
                $this->resultadoPendente = array(self::$PENDENTE_RET_RESUL_POS_REC, "Retificação do resultado pós-recursos da {$this->getNomeEtapa()}");
            } else {
                if (!$apresentacaoTabInsc) {
                    throw new NegocioException("Nenhum resultado pendente para esta chamada do edital!");
                } else {
                    if (!$this->isArquivoAtualizado()) {
                        return $this->isUltimaEtapa() ? "Retificação do resultado final" : "Retificação do resultado pós-recursos da {$this->getNomeEtapa()}";
                    }
                    return $this->isUltimaEtapa() ? "Resultado final" : "";
                }
            }
        }

        // retonando dado anterior
        return $this->resultadoPendente;
    }

    /**
     * Esta função remove os itens irrelevantes do calendário para uma publicação de resultado.
     * 
     * @param ProcessoChamada $chamada
     * @param array $itensCal Ponteiro para o array com itens do calendário a serem exibidos na ocasião 
     * de publicação de um resultado.
     */
    public function removerItensIrrelevantesCalPubResul($chamada, &$itensCal) {
        $tipoPubPendente = $this->getResultadoPendente();
        $qtItens = count($itensCal);
        for ($i = 0; $i < $qtItens; $i++) {
            $item = $itensCal[$i];

            // eliminando itens sem etapa
            if (!isset($item['idEtapaSel'])) {
                unset($itensCal[$i]);
                continue;
            }

            // eliminando itens de etapas menores
            if ($item['objEtapaSel']->ESP_NR_ETAPA_SEL < $this->ESP_NR_ETAPA_SEL) {
                unset($itensCal[$i]);
                continue;
            }


            // se chegou aqui, é porque a etapa é igual ou maior
            // 
            // 
            // caso de resultado parcial e retificação de resultado parcial
            if ($tipoPubPendente[0] == self::$PENDENTE_RESUL_PARCIAL || $tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_PARCIAL) {
                // se chegou ao período de recurso, parar remoção
                if ($item['tipo'] == ProcessoChamada::$CAL_TP_ITEM_RECURSO) {
                    break;
                } else {
                    // senão, remover pois é irrelevante
                    unset($itensCal[$i]);
                    continue;
                }
            } elseif ($tipoPubPendente[0] == self::$PENDENTE_RESUL_POS_REC || $tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_POS_REC) {
                // se chegou ao resultado parcial da próxima etapa, então parar remoção
                if ($item['idEtapaSel'] != $this->ESP_ID_ETAPA_SEL && $item['tipo'] == ProcessoChamada::$CAL_TP_ITEM_RESUL_PARC) {
                    break;
                } else {
                    // senão, remover pois é irrelevante
                    unset($itensCal[$i]);
                    continue;
                }
            } else {
                throw new NegocioException("Remoção de itens irrelevantes não programada para o tipo de resultado.");
            }
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     */
    public function temCandidatosInscritos($chamada) {
        // carregando
        if ($this->temInscritos === NULL) {
            $this->temInscritos = InscricaoProcesso::contarInscricaoPorProcessoCham($chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA()) != 0;
        }
        return $this->temInscritos;
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     */
    public function temRecursosEmAnalise($chamada) {
        // carregando
        if ($this->temRecursosEmAnalise === NULL) {
            $this->temRecursosEmAnalise = RecursoResulProc::contarRecursoPorFiltro(NULL, NULL, RecursoResulProc::$SIT_EM_ANALISE, $chamada->getPCH_ID_CHAMADA(), $this->EAP_ID_ETAPA_AVAL_PROC) != 0;
        }
        return $this->temRecursosEmAnalise;
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     */
    private function carregarOpcaoFinImediata($chamada) {
        $tipoPubPendente = $this->getResultadoPendente();

        // analisando casos específicos
        // sem inscrição: Então deve finalizar imediatamente
        if (!$this->temCandidatosInscritos($chamada)) {
            $this->opcaoFinalizacao = array(TRUE, "Esta chamada não teve candidatos inscritos, portanto, após a publicação do resultado, será finalizada.", "", self::$FIM_SEM_INSCRICAO);
            return;
        }

        // analisando se não houve candidatos aprovados
        $semAprovados = InscricaoProcesso::contarInscricaoPorSituacao($chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), InscricaoProcesso::$SIT_INSC_OK) == 0 && InscricaoProcesso::contarInscricaoPorSituacao($chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), InscricaoProcesso::$SIT_INSC_CAD_RESERVA) == 0;

        // caso de publicação de resultado parcial ou ret. resul parcial
        if ($tipoPubPendente[0] == self::$PENDENTE_RESUL_PARCIAL || $tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_PARCIAL) {
            if ($semAprovados) {
                $this->opcaoFinalizacao = array(TRUE, "Esta chamada não teve candidatos aprovados. Se desejar, você pode ignorar o prazo de recurso e finalizar esta chamada.", "<div class='completo m01'><div class='checkbox'><label><input type ='checkbox' id='forcarFinalizacao' name ='forcarFinalizacao' value='" . FLAG_BD_SIM . "'> <b>Desejo ignorar o prazo de recursos e finalizar esta chamada.</b></label></div></div>", self::$FIM_SEM_APP_RES_PARCIAL);
            } else {
                $this->opcaoFinalizacao = array(FALSE, "", "");
            }
            return;
        }

        // caso de publicação de resultado pós recurso e ret. resultado pós rec.
        if ($tipoPubPendente[0] == self::$PENDENTE_RESUL_POS_REC || $tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_POS_REC) {
            if ($semAprovados) {
                $this->opcaoFinalizacao = array(TRUE, "Esta chamada não teve candidatos aprovados, portanto, após a publicação do resultado, será finalizada.", "", self::$FIM_SEM_APP_RES_POS_REC);
            } elseif ($this->isUltimaEtapa()) {
                // adicionando tempo extra a data de hoje
                $dtFinalizacao = dt_somarData(dt_getDataEmStr("d/m/Y"), Processo::$TEMPO_PADRAO_FINALIZACAO);
                $this->opcaoFinalizacao = array(TRUE, "Por padrão, o sistema aguarda " . Processo::$TEMPO_PADRAO_FINALIZACAO . " dias para finalizar a chamada. Se desejar, altere este período no campo abaixo.", "<div class='completo m01'><label class='control-label col-xs-12 col-sm-4 col-md-4'>Data de finalização: *</label> <div class='col-xs-12 col-sm-8 col-md-8'><input type='text' class='form-control' name='dtFinalizacao' id='dtFinalizacao' size='10' maxlength='10' value='$dtFinalizacao'></div></div>", self::$FIM_ULTIMA_ETAPA, TRUE);
            } else {
                // print_r("ULTIMA ETAPA");
                $this->opcaoFinalizacao = array(FALSE, "", "");
            }
            return;
        }

        throw new NegocioException("Tipo de publicação não tratado para opção imediata.");
    }

    /**
     * Esta função informa se deve ser exibida a opção de finalizar o edital imediatamente.
     * 
     * @param ProcessoChamada $chamada
     */
    public function mostrarOpcaoFinImedPubResul($chamada) {
        if ($this->opcaoFinalizacao == NULL) {
            $this->carregarOpcaoFinImediata($chamada);
        }
        return $this->opcaoFinalizacao[0];
    }

    /**
     * Esta função retorna a mensagem a ser exibida no caso da opção de finalização imediata estar habilitada.
     * 
     * OBS: Retorna NULL, se não é permitido exibir a opção de finalização imediata
     *
     * @param ProcessoChamada $chamada
     */
    public function getMsgOpcaoFinImedPubResul($chamada) {
        if ($this->opcaoFinalizacao == NULL) {
            $this->carregarOpcaoFinImediata($chamada);
        }
        return $this->opcaoFinalizacao[0] ? $this->opcaoFinalizacao[1] : NULL;
    }

    /**
     * Esta função retorna o input a ser exibido no caso da opção de finalização imediata estar habilitada.
     * 
     * OBS: Retorna NULL, se não é permitido exibir a opção de finalização imediata
     *
     * @param ProcessoChamada $chamada
     */
    public function getOpcaoFinImedPubResul($chamada) {
        if ($this->opcaoFinalizacao == NULL) {
            $this->carregarOpcaoFbinImediata($chamada);
        }
        return $this->opcaoFinalizacao[0] ? $this->opcaoFinalizacao[2] : NULL;
    }

    /**
     * Esta função retorna o tipo de finalização imediata no caso da opção de finalização imediata estar habilitada.
     * 
     * OBS: Retorna NULL, se não é permitido exibir a opção de finalização imediata
     *
     * @param ProcessoChamada $chamada
     */
    public function getTpOpcaoFinImedPubResul($chamada) {
        if ($this->opcaoFinalizacao == NULL) {
            $this->carregarOpcaoFinImediata($chamada);
        }
        return $this->opcaoFinalizacao[0] ? $this->opcaoFinalizacao[3] : NULL;
    }

    /**
     * Esta função informa se a opção de finalização imediata é a data de finalização
     * 
     * 
     * @param ProcessoChamada $chamada
     */
    public function isOpcaoFimImedDtFinalizacao($chamada) {
        if ($this->opcaoFinalizacao == NULL) {
            $this->carregarOpcaoFinImediata($chamada);
        }
        return $this->opcaoFinalizacao[0] && isset($this->opcaoFinalizacao[4]) && $this->opcaoFinalizacao[4];
    }

    /**
     * Esta função informa se deve exibir o calendário na publicação do resultado
     * 
     * 
     * @param ProcessoChamada $chamada
     * @param array $itensCal Array com itens do calendário
     */
    public function mostrarCalendarioPubResultado($chamada, $itensCal) {
        if ($this->opcaoFinalizacao == NULL) {
            $this->carregarOpcaoFinImediata($chamada);
        }
        return !Util::vazioNulo($itensCal) && (!$this->opcaoFinalizacao[0] || ($this->opcaoFinalizacao[0] && $this->opcaoFinalizacao[3] != self::$FIM_SEM_INSCRICAO && $this->opcaoFinalizacao[3] != self::$FIM_SEM_APP_RES_POS_REC));
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param array $arraySqls Endereço do array onde deve ser adicionado os sqls
     * @param array $$vetCalendario Endereço do array onde está armazenado os novos dados do calendário
     * @param boolean $forcarFinalizacao Informa se é para forçar a finalização da chamada
     * @param string $dtFinalizacao Data de finalização da chamada
     * @param boolean $arqExterno Informa se não é para gerar o arquivo de resultado automaticamente, pois será utilizado um arquivo externo
     * 
     * 
     * @return boolean Informa se o calendário foi alterado ou não durante o processo
     */
    public function adicionaSqlsPublicacaoResultado($processo, $chamada, &$arraySqls, &$vetCalendario, $forcarFinalizacao, $dtFinalizacao, $arqExterno) {
        // algumas constantes
        $arqResultado = $arqResultadoFinal = NULL;
        $stRec = self::$SIT_RECURSO;
        $stAbt = self::$SIT_ABERTA;
        $stFin = self::$SIT_FINALIZADA;
        $clascConc = self::$CLASSIF_CONCLUIDA;
        $stFimParcial = self::$ST_FIM_RES_PARCIAL;
        $stFimPosRec = self::$ST_FIM_RES_POS_REC;
        $idUsuLogado = getIdUsuarioLogado();
        $flagArqAtualizado = FLAG_BD_SIM;
        $flagChamAtiva = FLAG_BD_SIM;
        $alterouCalendario = FALSE;

        try {

            // Tipo de publicação
            $tipoPubPendente = $this->getResultadoPendente();

            // Tratando alguns casos de finalização imediata
            //
            //
            $tpFimImediato = $this->getTpOpcaoFinImedPubResul($chamada);
            if ($tpFimImediato != NULL) {
                // Caso de não ter candidatos inscritos ou sem aprovados já no resultado parcial
                //
                if ($tpFimImediato == self::$FIM_SEM_INSCRICAO || $tpFimImediato == self::$FIM_SEM_APP_RES_PARCIAL) {
                    // finalização da chamada e de todas suas etapas, sem perguntas!
                    $finalizacaoForcada = $tpFimImediato == self::$FIM_SEM_INSCRICAO || $forcarFinalizacao;

                    if ($finalizacaoForcada) {

                        // gerando url do resultado parcial
                        $urlArqResultado = $this->ARQS_getUrlArqResulParcial($processo, $chamada);

                        // Setando flags do resultado parcial da etapa
                        $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ID_USUARIO_RESP_RESUL = '$idUsuLogado',
                            ESP_LOG_DT_REG_RESUL = now(),
                            ESP_URL_ARQUIVO_RESUL = '$urlArqResultado', ESP_ARQ_ATUALIZADO = '$flagArqAtualizado',
                            ESP_ST_FINALIZACAO = '$stFimPosRec', ESP_ST_ETAPA = '$stFin',
                            ESP_ST_CLASSIFICACAO = '$clascConc',
                            ESP_ID_USUARIO_RESP_CLAS = '$idUsuLogado',
                            ESP_ID_USUARIO_RESP_FIN = '$idUsuLogado',
                            ESP_LOG_DT_REG_CLAS = now(),
                            ESP_LOG_DT_REG_FIN = now(),
                            ESP_ETAPA_ATIVA = NULL
                            where ESP_ID_ETAPA_SEL = '{$this->ESP_ID_ETAPA_SEL}'";

                        // Finalizando etapas da chamada diferentes da atual
                        $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ST_ETAPA = '$stFin',
                            ESP_ST_CLASSIFICACAO = '$clascConc',
                            ESP_ARQ_ATUALIZADO = '$flagArqAtualizado',
                            ESP_ID_USUARIO_RESP_CLAS = '$idUsuLogado',
                            ESP_ID_USUARIO_RESP_FIN = '$idUsuLogado',
                            ESP_LOG_DT_REG_CLAS = now(),
                            ESP_LOG_DT_REG_FIN = now(),
                            ESP_ETAPA_ATIVA = NULL,
                            ESP_ST_FINALIZACAO = '$stFimParcial'
                            where PCH_ID_CHAMADA = '{$this->PCH_ID_CHAMADA}'
                            and ESP_ID_ETAPA_SEL != '{$this->ESP_ID_ETAPA_SEL}'";

                        // ajustando flag da última etapa
                        $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ETAPA_ATIVA = '$flagChamAtiva'
                            where PCH_ID_CHAMADA = '{$this->PCH_ID_CHAMADA}'
                            and EAP_ID_ETAPA_AVAL_PROC = (select EAP_ID_ETAPA_AVAL_PROC 
                            from tb_eap_etapa_aval_proc where PRC_ID_PROCESSO = '{$this->PRC_ID_PROCESSO}' order by EAP_NR_ETAPA_AVAL desc limit 0,1)";

                        // informando publicação do resultado final
                        $arraySqls [] = ProcessoChamada::getSqlPubResultadoFinal($chamada->getPCH_ID_CHAMADA(), $idUsuLogado, $urlArqResultado);

                        // Finalizando chamada
                        $arraySqls [] = ProcessoChamada::getSqlFinalizacaoChamada($chamada->getPCH_ID_CHAMADA());

                        // programando finalização do edital
                        $dtFimEdital = dt_somarData(dt_getDataEmStr("d/m/Y"), Processo::$TEMPO_PADRAO_FINALIZACAO);
                        Processo::addSqlFinalizacaoEdital($arraySqls, $chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), $dtFimEdital);

                        // processando publicação de resultado
                        $arqResultado = AcompProcChamada::processaPublicacaoResultado($processo, $chamada, $urlArqResultado, $this->ARQS_getIniUrlArqResulParcial($processo, $chamada), $this, "Resultado Parcial da {$this->getNomeEtapa()} publicado.", $arraySqls, $arqExterno, $dtFimEdital);

                        // retornando
                        return $alterouCalendario;
                    }
                } elseif ($tpFimImediato == self::$FIM_SEM_APP_RES_POS_REC) {
                    // gerando url do resultado pós recurso
                    $urlArqResultado = $this->ARQS_getUrlArqResulPosRec($processo, $chamada);

                    // Setando flags do resultado pós recurso da etapa
                    $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ID_USUARIO_RESP_REC = '$idUsuLogado',
                            ESP_LOG_DT_REG_REC = now(),
                            ESP_URL_ARQUIVO_POS_REC = '$urlArqResultado', ESP_ARQ_ATUALIZADO = '$flagArqAtualizado',
                            ESP_ST_ETAPA = '$stFin',
                            ESP_ID_USUARIO_RESP_FIN = '$idUsuLogado',
                            ESP_LOG_DT_REG_FIN = now(),
                            ESP_ETAPA_ATIVA = NULL
                            where ESP_ID_ETAPA_SEL = '{$this->ESP_ID_ETAPA_SEL}'";

                    // Finalizando etapas da chamada diferentes da atual
                    $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ST_ETAPA = '$stFin',
                            ESP_ID_USUARIO_RESP_FIN = '$idUsuLogado',
                            ESP_LOG_DT_REG_FIN = now(),
                            ESP_ETAPA_ATIVA = NULL,
                            ESP_ST_CLASSIFICACAO = '$clascConc',
                            ESP_ARQ_ATUALIZADO = '$flagArqAtualizado',
                            ESP_ST_FINALIZACAO = '$stFimParcial'
                            where PCH_ID_CHAMADA = '{$this->PCH_ID_CHAMADA}'
                            and ESP_ID_ETAPA_SEL != '{$this->ESP_ID_ETAPA_SEL}'";

                    // ajustando flag da última etapa
                    $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ETAPA_ATIVA = '$flagChamAtiva'
                            where PCH_ID_CHAMADA = '{$this->PCH_ID_CHAMADA}'
                            and EAP_ID_ETAPA_AVAL_PROC = (select EAP_ID_ETAPA_AVAL_PROC 
                            from tb_eap_etapa_aval_proc where PRC_ID_PROCESSO = '{$this->PRC_ID_PROCESSO}' order by EAP_NR_ETAPA_AVAL desc limit 0,1)";

                    // informando publicação do resultado final
                    $arraySqls [] = ProcessoChamada::getSqlPubResultadoFinal($chamada->getPCH_ID_CHAMADA(), $idUsuLogado, $urlArqResultado);

                    // Finalizando chamada
                    $arraySqls [] = ProcessoChamada::getSqlFinalizacaoChamada($chamada->getPCH_ID_CHAMADA());

                    // programando finalização do edital
                    $dtFimEdital = dt_somarData(dt_getDataEmStr("d/m/Y"), Processo::$TEMPO_PADRAO_FINALIZACAO);
                    Processo::addSqlFinalizacaoEdital($arraySqls, $chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), $dtFimEdital);

                    // processando publicação de resultado
                    $arqResultado = AcompProcChamada::processaPublicacaoResultado($processo, $chamada, $urlArqResultado, $this->ARQS_getIniUrlArqResulParcial($processo, $chamada), $this, "Resultado Pós-Recursos da {$this->getNomeEtapa()} publicado.", $arraySqls, $arqExterno, $dtFimEdital);

                    // retornando
                    return $alterouCalendario;
                } elseif ($tpFimImediato == self::$FIM_ULTIMA_ETAPA) {
                    // nada a fazer
                }
            }


            // verificando os diversos casos de publicação
            //
            if ($tipoPubPendente[0] == self::$PENDENTE_RESUL_PARCIAL) {
                // arquivo de resultado
                $urlArqResultado = $this->ARQS_getUrlArqResulParcial($processo, $chamada);

                // verificando nova data de previsão do resultado
                if (dt_dataMaior(dt_getTimestampDtBR($this->ESP_DT_PREV_RESUL_ETAPA), dt_getTimestampDtBR())) {
                    $alterouCalendario = TRUE;
                    $this->ESP_DT_PREV_RESUL_ETAPA = dt_getDataEmStr("d/m/Y");
                    $vetCalendario[ProcessoChamada::getIdInputCalResulParcial($this)] = $this->ESP_DT_PREV_RESUL_ETAPA;
                }
                $dtPrevResultado = dt_dataStrParaMysql($this->ESP_DT_PREV_RESUL_ETAPA);

                // alterando status da etapa atual para recurso
                $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ST_ETAPA = '$stRec', ESP_DT_PREV_RESUL_ETAPA = $dtPrevResultado,
                            ESP_ID_USUARIO_RESP_RESUL = '$idUsuLogado', ESP_LOG_DT_REG_RESUL = now(), 
                            ESP_URL_ARQUIVO_RESUL = '$urlArqResultado', ESP_ARQ_ATUALIZADO = '$flagArqAtualizado'
                            where ESP_ID_ETAPA_SEL = '{$this->ESP_ID_ETAPA_SEL}'";

                // processando publicação de resultado
                $arqResultado = AcompProcChamada::processaPublicacaoResultado($processo, $chamada, $urlArqResultado, $this->ARQS_getIniUrlArqResulParcial($processo, $chamada), $this, "Resultado Parcial da {$this->getNomeEtapa()} publicado.", $arraySqls, $arqExterno);
                //
            //
            } elseif ($tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_PARCIAL) {
                // Apenas informando que o arquivo está atualizado
                $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ARQ_ATUALIZADO = '$flagArqAtualizado'
                            where ESP_ID_ETAPA_SEL = '{$this->ESP_ID_ETAPA_SEL}'";

                // processando publicação de resultado
                $arqResultado = AcompProcChamada::processaPublicacaoResultado($processo, $chamada, $this->ESP_URL_ARQUIVO_RESUL, $this->ARQS_getIniUrlArqResulParcial($processo, $chamada), $this, "Retificação do Resultado Parcial da {$this->getNomeEtapa()} publicada.", $arraySqls, $arqExterno);
                //
            //
            } elseif ($tipoPubPendente[0] == self::$PENDENTE_RESUL_POS_REC) {
                // arquivo de resultado
                $urlArqResultado = $this->ARQS_getUrlArqResulPosRec($processo, $chamada);

                // verificando nova data de previsão do resultado
                if (dt_dataMaior(dt_getTimestampDtBR($this->ESP_DT_PREV_RESUL_REC), dt_getTimestampDtBR())) {
                    $alterouCalendario = TRUE;
                    $this->ESP_DT_PREV_RESUL_REC = dt_getDataEmStr("d/m/Y");
                    $vetCalendario[ProcessoChamada::getIdInputCalResulFinal($this)] = $this->ESP_DT_PREV_RESUL_REC;
                }
                $dtPrevResultado = dt_dataStrParaMysql($this->ESP_DT_PREV_RESUL_REC);

                // Finalizando etapa atual
                $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ST_ETAPA = '$stFin', ESP_DT_PREV_RESUL_REC = $dtPrevResultado,
                    ESP_ID_USUARIO_RESP_REC = '$idUsuLogado', ESP_LOG_DT_REG_REC = now(), 
                    ESP_URL_ARQUIVO_POS_REC = '$urlArqResultado', ESP_ARQ_ATUALIZADO = '$flagArqAtualizado'
                    where ESP_ID_ETAPA_SEL = '{$this->ESP_ID_ETAPA_SEL}'";

                // processando publicação de resultado
                $arqResultado = AcompProcChamada::processaPublicacaoResultado($processo, $chamada, $urlArqResultado, $this->ARQS_getIniUrlArqResulPosRec($processo, $chamada), $this, "Resultado Pós-Recursos da {$this->getNomeEtapa()} publicada.", $arraySqls, $arqExterno);

                // tratando as especificidades de última etapa e etapa meio
                // 
                // caso de não ser última etapa
                if (!$this->isUltimaEtapa()) {

                    // já setando flags de finalização de etapa, pois não é permitido retificar etapa meio
                    $arraySqls [] = "update tb_esp_etapa_sel_proc set
                    ESP_ID_USUARIO_RESP_FIN = '$idUsuLogado', ESP_LOG_DT_REG_FIN = now(), ESP_ETAPA_ATIVA = NULL
                    where ESP_ID_ETAPA_SEL = '{$this->ESP_ID_ETAPA_SEL}'";

                    // abrindo próxima etapa
                    $nrProxEtapa = $this->ESP_NR_ETAPA_SEL + 1;
                    $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_ST_ETAPA = '$stAbt', ESP_ETAPA_ATIVA = '$flagChamAtiva'
                    where PCH_ID_CHAMADA = '{$this->PCH_ID_CHAMADA}' and EAP_ID_ETAPA_AVAL_PROC = (select EAP_ID_ETAPA_AVAL_PROC
                    from tb_eap_etapa_aval_proc where PRC_ID_PROCESSO = '{$this->PRC_ID_PROCESSO}' and EAP_NR_ETAPA_AVAL = '$nrProxEtapa')";
                } else {
                    // caso de ser a última etapa
                    //
                    
                    // alterando previsão do resultado final
                    $arraySqls [] = ProcessoChamada::getSqlAlteracaoPrevResulFinal($this->PCH_ID_CHAMADA, $dtPrevResultado, TRUE);

                    // gerando url do arquivo final
                    $urlArqResultadoFinal = $chamada->ARQS_getUrlArqResulFinal($processo);

                    // data de finalização programada
                    $dtFinalizacao = dt_dataStrParaMysql($dtFinalizacao);

                    // informando publicação do resultado final
                    $arraySqls [] = ProcessoChamada::getSqlPubResultadoFinal($chamada->getPCH_ID_CHAMADA(), $idUsuLogado, $urlArqResultadoFinal);

                    // programando finalização da etapa
                    $arraySqls [] = "update tb_esp_etapa_sel_proc set
                    ESP_ID_USUARIO_RESP_FIN = '$idUsuLogado', ESP_LOG_DT_REG_FIN = $dtFinalizacao
                    where ESP_ID_ETAPA_SEL = '{$this->ESP_ID_ETAPA_SEL}'";

                    // programando finalização da chamada
                    $arraySqls [] = ProcessoChamada::getSqlFinalizacaoChamada($chamada->getPCH_ID_CHAMADA(), $dtFinalizacao, TRUE);

                    // programando finalização do edital
                    $dtFimEdital = dt_somarData(dt_getDataEmStr("d/m/Y"), Processo::$TEMPO_PADRAO_FINALIZACAO);
                    Processo::addSqlFinalizacaoEdital($arraySqls, $chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), $dtFimEdital);

                    // processando publicação do resultado final
                    $arqResultadoFinal = AcompProcChamada::processaPublicacaoResultado($processo, $chamada, $urlArqResultadoFinal, $chamada->ARQS_getIniUrlArqResulFinal($processo), $this, "Resultado final publicado.", $arraySqls, $arqExterno, $dtFimEdital, 'F');
                }
            } elseif ($tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_POS_REC) {
                // @todo Modificação necessária para habilitar resultado pós-recursos
                throw new NegocioException("Retificação do resultado pós-recursos não implementado!");
            } else {
                throw new NegocioException("SQL de publicação não programada para o tipo especificado!");
            }

            // Tratando caso do resultado provisório
            //
            if (($tipoPubPendente[0] == self::$PENDENTE_RESUL_PARCIAL || $tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_PARCIAL) && $this->isUltimaEtapa()) {
                // gerando url do arquivo final provisório
                $urlArqResulFinalProv = $chamada->ARQS_getUrlArqResulFinal($processo, TRUE);

                // gerando url do arquivo final
                $urlArqResultadoFinal = $chamada->ARQS_getUrlArqResulFinal($processo);
                $arraySqls [] = ProcessoChamada::getSqlSetUrlResulFinal($chamada->getPCH_ID_CHAMADA(), $urlArqResultadoFinal);


                // processando publicação do resultado final provisório
                $arqResultadoFinal = AcompProcChamada::processaPublicacaoResultado($processo, $chamada, $urlArqResulFinalProv, $chamada->ARQS_getIniUrlArqResulFinal($processo, TRUE), $this, "Resultado final provisório publicado.", $arraySqls, $arqExterno, NULL, 'P');
            }

            // ajustando controles de solicitação de publicação
            if ($tipoPubPendente[0] == self::$PENDENTE_RESUL_PARCIAL || $tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_PARCIAL) {
                $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_PUB_DT_SOL_RESUL_PAR = NULL, ESP_PUB_USU_RESP_SOL_PAR = NULL where esp_id_etapa_sel = '$this->ESP_ID_ETAPA_SEL'";
            } elseif ($tipoPubPendente[0] == self::$PENDENTE_RESUL_POS_REC || $tipoPubPendente[0] == self::$PENDENTE_RET_RESUL_POS_REC) {
                // resultado pós recurso
                $arraySqls [] = "update tb_esp_etapa_sel_proc set ESP_PUB_DT_SOL_RESUL_FIN = NULL, ESP_PUB_USU_RESP_SOL_FIN = NULL where esp_id_etapa_sel = '$this->ESP_ID_ETAPA_SEL'";
            }


            return $alterouCalendario;
        } catch (Exception $e) {
            // excluindo possíveis arquivos temporários
            NGUtil::arq_excluirArquivoServidor($arqResultado);
            NGUtil::arq_excluirArquivoServidor($arqResultadoFinal);

            // relançando exceção
            throw $e;
        }
    }

    /**
     * Esta função retorna um array com html dos resultados publicados na chamada
     * 
     * @param ProcessoChamada $chamada
     * @return array Array com HTML de cada resultado publicado na chamada
     * @throws NegocioException
     */
    public static function buscarResultadosPublicados($chamada) {
        try {
            // buscando etapas da chamada
            $listaEtapas = self::buscarEtapaPorChamada($chamada->getPCH_ID_CHAMADA());

            if ($listaEtapas != NULL) {
                $ret = array();
                $qtEtapas = count($listaEtapas);
                for ($i = 0; $i < $qtEtapas; $i++) {
                    $etapa = $listaEtapas[$i];

                    // resultado parcial
                    if (!$etapa->isAberta() && !$etapa->isFechada() && !$etapa->isFinalizacaoForcada(self::$PENDENTE_RESUL_PARCIAL)) {
                        // mostrando link do resultado parcial
                        $temp = "<tr><td><a target='_blank' href='{$etapa->getUrlArquivoResulParcial()}' title='Visualizar o resultado parcial da etapa'>Resultado parcial da {$etapa->getNomeEtapa()} <i class='fa fa-external-link'></i></a></td>";
                        $temp .= "<td class='campoDesktop'>{$etapa->getESP_LOG_DT_REG_RESUL(true)}</td>";

                        // atualização?
                        $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($etapa->ESP_URL_ARQUIVO_RESUL, $etapa->PRC_ID_PROCESSO, $etapa->PCH_ID_CHAMADA);
                        if ($dtAtualizacao != NULL) {
                            $temp .= "<td class='campoDesktop'>$dtAtualizacao</td>";
                        } else {
                            $temp .= "<td class='campoDesktop'>" . Util::$STR_CAMPO_VAZIO . "</td>";
                        }
                        $temp .= "</tr>";

                        $ret [] = $temp;
                    }

                    // resultado final da etapa ou do processo
                    if (!Util::vazioNulo($etapa->ESP_LOG_DT_REG_REC) && !$etapa->isFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)) {
                        // mostrando link do resultado final
                        $temp = "<tr><td><a target='_blank' href='{$etapa->getUrlArquivoResulPosRec()}' title='Visualizar o resultado pós-recursos'>Resultado pós-recursos da {$etapa->getNomeEtapa()} <i class='fa fa-external-link'></i></a></td>";
                        $temp .= "<td class='campoDesktop'>{$etapa->getESP_LOG_DT_REG_REC(true)}</td>";

                        // atualização?
                        $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($etapa->ESP_URL_ARQUIVO_POS_REC, $etapa->PRC_ID_PROCESSO, $etapa->PCH_ID_CHAMADA);
                        if ($dtAtualizacao != NULL) {
                            $temp .= "<td class='campoDesktop'>$dtAtualizacao</td>";
                        } else {
                            $temp .= "<td class='campoDesktop'>" . Util::$STR_CAMPO_VAZIO . "</td>";
                        }
                        $temp .= "</tr>";

                        $ret [] = $temp;
                    }
                }

                // caso do resultado final
                $etapa = $listaEtapas[$qtEtapas - 1];
                if (!Util::vazioNulo($etapa->ESP_LOG_DT_REG_REC) && !$etapa->isFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)) {
                    // mostrando link do resultado final
                    $temp = "<tr><td><a target='_blank' href='{$chamada->getUrlArquivoResulFinal()}' title='Visualizar o resultado final'>Resultado final <i class='fa fa-external-link'></i></a></td>";
                    $temp .= "<td class='campoDesktop'>{$etapa->getESP_LOG_DT_REG_REC(true)}</td>";

                    // atualização?
                    $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($chamada->getPCH_URL_ARQ_RESUL_FINAL(), $etapa->PRC_ID_PROCESSO, $etapa->PCH_ID_CHAMADA);
                    if ($dtAtualizacao != NULL) {
                        $temp .= "<td class='campoDesktop'>$dtAtualizacao</td>";
                    } else {
                        $temp .= "<td class='campoDesktop'>" . Util::$STR_CAMPO_VAZIO . "</td>";
                    }
                    $temp .= "</tr>";

                    $ret [] = $temp;
                } elseif (!Util::vazioNulo($etapa->ESP_LOG_DT_REG_RESUL) && !$etapa->isFinalizacaoForcada(self::$PENDENTE_RESUL_PARCIAL)) {
                    // mostrando link do resultado final provisório
                    $temp = "<tr><td><a target='_blank' href='{$chamada->getUrlArquivoResulFinal(TRUE)}' title='Visualizar o resultado final provisório'>Resultado final provisório <i class='fa fa-external-link'></i></a></td>";
                    $temp .= "<td class='campoDesktop'>{$etapa->getESP_LOG_DT_REG_RESUL(true)}</td>";

                    // atualização?
                    $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($chamada->getPCH_URL_ARQ_RESUL_FINAL(TRUE), $etapa->PRC_ID_PROCESSO, $etapa->PCH_ID_CHAMADA);
                    if ($dtAtualizacao != NULL) {
                        $temp .= "<td class='campoDesktop'>$dtAtualizacao</td>";
                    } else {
                        $temp .= "<td class='campoDesktop'>" . Util::$STR_CAMPO_VAZIO . "</td>";
                    }
                    $temp .= "</tr>";

                    $ret [] = $temp;
                }

                // retornando vetor
                return $ret;
            }

            // sem resultados
            return NULL;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar resultados publicados.", $e);
        }
    }

    /**
     * Retorna uma etapa na situaçao aberta ou em recursos
     * 
     * @param int $idChamada
     * @return EtapaSelProc
     * @throws NegocioException
     */
    public static function buscarEtapaEmAndamento($idChamada) {
        try {
            $stAberta = self::$SIT_ABERTA;
            $stRec = self::$SIT_RECURSO;
            // 
            // @todo Modificação necessária para habilitar resultado pós-recursos
            // SQL adicional a ser adicionada nas restrições para permitir retificação de resultado pós-recursos
            //
            //$stFin = self::$SIT_FINALIZADA;
            //
//            $ultEtapaFin = " || (esp.EAP_ID_ETAPA_AVAL_PROC = (select EAP_ID_ETAPA_AVAL_PROC from tb_eap_etapa_aval_proc eapi
//                            where eapi.PRC_ID_PROCESSO = esp.PRC_ID_PROCESSO order by eapi.EAP_NR_ETAPA_AVAL desc limit 0,1)
//                            and ESP_ST_ETAPA = '$stFin' and ESP_LOG_DT_REG_FIN > now())";


            return self::buscarEtapaPorRestricao($idChamada, "(ESP_ST_ETAPA = '$stAberta' || ESP_ST_ETAPA = '$stRec')", TRUE, FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapa em andamento do processo.", $e);
        }
    }

    public static function buscarEtapaAtiva($idChamada) {
        try {
            $etapaAtiva = FLAG_BD_SIM;

            return self::buscarEtapaPorRestricao($idChamada, "ESP_ETAPA_ATIVA = '$etapaAtiva'", TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapa em andamento do processo.", $e);
        }
    }

    /**
     * Retorna uma etapa na situaçao em recurso
     * 
     * @param int $idChamada
     * @return EtapaSelProc
     * @throws NegocioException
     */
    public static function buscarEtapaEmRecurso($idChamada) {
        try {
            $stRec = self::$SIT_RECURSO;

            return self::buscarEtapaPorRestricao($idChamada, "(ESP_ST_ETAPA = '$stRec')", TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapa em recurso do processo.", $e);
        }
    }

    public static function buscarEtapaVigente($idChamada, $idEtapaSel = NULL) {
        try {
            $stAberta = self::$SIT_ABERTA;
            $stRec = self::$SIT_RECURSO;
            $stFin = self::$SIT_FINALIZADA;

            $idEtapaSel = $idEtapaSel != NULL ? " and ESP_ID_ETAPA_SEL = '$idEtapaSel'" : "";

            return self::buscarEtapaPorRestricao($idChamada, "(ESP_ST_ETAPA = '$stAberta' || ESP_ST_ETAPA = '$stRec'|| ESP_ST_ETAPA = '$stFin') $idEtapaSel", TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapa vigente do processo.", $e);
        }
    }

    public static function buscarNotaMaxPorEtapa($idProcesso, $nrEtapa) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                         sum(CAP_VL_PONTUACAO_MAX) as somaValMax
                    from
                        tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                        where
                            cap.PRC_ID_PROCESSO = '$idProcesso'
                                and EAP_NR_ETAPA_AVAL = '$nrEtapa'";
            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }

            // retornando nota maxima
            return ConexaoMysql::getResult("somaValMax", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar nota máxima da etapa.", $e);
        }
    }

    public static function buscarEtapaAjaxPorChamada($idChamada) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select ESP_ID_ETAPA_SEL,
                    concat('Etapa ', EAP_NR_ETAPA_AVAL) as ESP_DS_ETAPA_SEL 
                    from tb_esp_etapa_sel_proc esp
                    join tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where `PCH_ID_CHAMADA` = '$idChamada'
                    order by EAP_NR_ETAPA_AVAL";

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
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['ESP_ID_ETAPA_SEL'];
                $valor = $dados['ESP_DS_ETAPA_SEL'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapas da chamada do processo.", $e);
        }
    }

    public static function buscarEtapaPenPorChamada($idProcesso, $idChamada) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // flags 
            $stRec = self::$SIT_RECURSO;
            $etapaAtiva = FLAG_BD_SIM;

            $sql = "select ESP_ID_ETAPA_SEL,
                    concat('Etapa ', EAP_NR_ETAPA_AVAL) as ESP_DS_ETAPA_SEL 
                    from tb_esp_etapa_sel_proc esp
                    join tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where esp.PRC_ID_PROCESSO = '$idProcesso' and `PCH_ID_CHAMADA` = '$idChamada'
                    and (ESP_ST_ETAPA = '$stRec' or ESP_ETAPA_ATIVA = '$etapaAtiva')
                    order by EAP_NR_ETAPA_AVAL";

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
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['ESP_ID_ETAPA_SEL'];
                $valor = $dados['ESP_DS_ETAPA_SEL'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapas pendentes da chamada do processo.", $e);
        }
    }

    /**
     * Funcao que executa a avaliacao automatica dos candidatos ainda nao avaliados.
     * Caso esteja tudo correto, eh realizada a persistencia no BD.
     * 
     * USE ESTA FUNCAO COM CONCIENCIA! FUNCAO DE PROCESSAMENTO RAPIDO, SEM VALIDACOES!
     * 
     * @param int $idProcesso
     * @param array $listaCands
     * @throws NegocioException
     */
    public function CLAS_execAvalAutomaticaCand($idProcesso, $listaCands) {
        try {
            // recuperando estruturas para avaliaçao
            $listaCat = CategoriaAvalProc::buscarCatAvalPorProcEtapaTp($idProcesso, $this->ESP_NR_ETAPA_SEL, CategoriaAvalProc::$AVAL_AUTOMATICA);

            // armazena uma lista de lista de comandos sql a ser executada no BD
            $listaArraySql = array();

//            print_r($listaCat);
//            print_r("<br/>");
            // para cada categoria, executar avaliaçao dos candidatos
            foreach ($listaCat as $categoria) {
                $listaArraySql [] = $categoria->CLAS_aplicaRegrasAvalAuto($listaCands);
            }

            // incluindo sqls de atualizacao de flags para classificacao automatica dos candidatos
            $listaArraySql [] = array(InscricaoProcesso::getSqlLimparAnaliseHumPorCham($this->PCH_ID_CHAMADA, TRUE), InscricaoProcesso::getSqlLimparElimAutoPorChamEtapa($this->PCH_ID_CHAMADA, $this->ESP_ID_ETAPA_SEL, TRUE), InscricaoProcesso::CLAS_getStrSqlSitAutoOk($this->PCH_ID_CHAMADA));

//            foreach (array_keys($listaArraySql) as $ind) {
//                print_r($listaArraySql[$ind]);
//                print_r("<br/>");
//            }
//            exit;
//            
//
            // salvando no BD
            $conexao = NGUtil::getConexao();
            $conexao->execTransacaoMatriz($listaArraySql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao executar avaliação automática dos candidatos.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @return string Sql responsável por abrir a primeira etapa de seleção da chamada
     */
    public static function getStrSqlAbrirPrimeiraEtapaCham($idProcesso, $idChamada) {
        return "update tb_esp_etapa_sel_proc set ESP_ST_ETAPA = '" . self::$SIT_ABERTA
                . "', ESP_ETAPA_ATIVA = '" . FLAG_BD_SIM . "' where PRC_ID_PROCESSO = '$idProcesso' and PCH_ID_CHAMADA = '$idChamada'
                and EAP_ID_ETAPA_AVAL_PROC = (select EAP_ID_ETAPA_AVAL_PROC from tb_eap_etapa_aval_proc where PRC_ID_PROCESSO = '$idProcesso'
                and EAP_NR_ETAPA_AVAL = '" . EtapaAvalProc::$NR_PRIMEIRA_ETAPA . "')";
    }

    public static function CLAS_getSqlClassifConc($idEtapa, $idUsuLogado) {
        return "update tb_esp_etapa_sel_proc set ESP_ST_CLASSIFICACAO = '" . self::$CLASSIF_CONCLUIDA
                . "', ESP_ID_USUARIO_RESP_CLAS = '$idUsuLogado', ESP_LOG_DT_REG_CLAS = now()
                 where ESP_ID_ETAPA_SEL = '$idEtapa'";
    }

    private static function getSqlAdendoChamNaoFinalizada() {
        $chamFechada = self::$SIT_FINALIZADA;
        return " and ESP_ST_ETAPA != '$chamFechada'";
    }

    /**
     * Esta funçao retorna uma string com a sql responsavel por invalidar a classificaçao
     * da etapa de seleçao. 
     * 
     * @param int $idEtapaSelProc
     * @return string
     */
    public static function getStrSqlClassifPenPorEtSel($idEtapaSelProc) {
        return "update tb_esp_etapa_sel_proc set ESP_ST_CLASSIFICACAO = '" . self::$CLASSIF_PENDENTE
                . "', ESP_ID_USUARIO_RESP_CLAS = NULL, ESP_LOG_DT_REG_CLAS = NULL, ESP_ARQ_ATUALIZADO = NULL
                 where ESP_ID_ETAPA_SEL = '$idEtapaSelProc'" . self::getSqlAdendoChamNaoFinalizada();
    }

    /**
     * Esta funçao retorna uma string com a sql responsavel por invalidar a classificaçao
     * da etapa de seleçao. 
     * 
     * @param int $idChamada
     * @return string
     */
    public static function getStrSqlClassifPenPorChamada($idChamada) {
        return "update tb_esp_etapa_sel_proc set ESP_ST_CLASSIFICACAO = '" . self::$CLASSIF_PENDENTE
                . "', ESP_ID_USUARIO_RESP_CLAS = NULL, ESP_LOG_DT_REG_CLAS = NULL, ESP_ARQ_ATUALIZADO = NULL
                 where PCH_ID_CHAMADA = '$idChamada'" . self::getSqlAdendoChamNaoFinalizada();
    }

    /**
     * Esta funçao retorna uma string com a sql responsavel por invalidar a classificaçao
     * da etapa de seleçao. 
     * 
     * @param int $idEtapaAvalProc
     * @return string
     */
    public static function getStrSqlClassifPenPorEtAval($idEtapaAvalProc) {
        return "update tb_esp_etapa_sel_proc set ESP_ST_CLASSIFICACAO = '" . self::$CLASSIF_PENDENTE
                . "', ESP_ID_USUARIO_RESP_CLAS = NULL, ESP_LOG_DT_REG_CLAS = NULL, ESP_ARQ_ATUALIZADO = NULL
                 where EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAvalProc'" . self::getSqlAdendoChamNaoFinalizada();
    }

    /**
     * Esta funçao retorna uma string com a sql responsavel por invalidar a classificaçao
     * da última etapa de seleção do processo. 
     * 
     * @param int $idProcesso
     * @return string
     */
    public static function getStrSqlClassifPenUltEtapa($idProcesso) {
        $nrUltimaEtapa = EtapaAvalProc::getNrUltimaEtapaAvalProc($idProcesso);

        return "update tb_esp_etapa_sel_proc set ESP_ST_CLASSIFICACAO = '" . self::$CLASSIF_PENDENTE
                . "', ESP_ID_USUARIO_RESP_CLAS = NULL, ESP_LOG_DT_REG_CLAS = NULL, ESP_ARQ_ATUALIZADO = NULL
                 where EAP_ID_ETAPA_AVAL_PROC in
                 (select EAP_ID_ETAPA_AVAL_PROC from tb_eap_etapa_aval_proc
                 where PRC_ID_PROCESSO = '$idProcesso' and EAP_NR_ETAPA_AVAL = '$nrUltimaEtapa')" . self::getSqlAdendoChamNaoFinalizada();
    }

    /**
     * Esta funçao retorna uma string com a sql responsavel por invalidar a classificaçao
     * da etapa de seleçao. 
     * 
     * @param int $idProcesso
     * @param int $idEtapaAvalProc
     * @return string
     */
    public static function getStrSqlClassifPenProcEtapa($idProcesso, $idEtapaAvalProc) {
        return "update tb_esp_etapa_sel_proc set ESP_ST_CLASSIFICACAO = '" . self::$CLASSIF_PENDENTE
                . "', ESP_ID_USUARIO_RESP_CLAS = NULL, ESP_LOG_DT_REG_CLAS = NULL, ESP_ST_ETAPA = '" . self::$SIT_ABERTA .
                "', ESP_ID_USUARIO_RESP_REC = NULL, ESP_LOG_DT_REG_REC = NULL, ESP_ARQ_ATUALIZADO = NULL
                 where PRC_ID_PROCESSO = $idProcesso and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAvalProc'" . self::getSqlAdendoChamNaoFinalizada();
    }

    public static function getSqlAlteracaoPrevResulEtapa($idChamada, $idEtapaSel, $dt1) {
        $dt1 = dt_dataStrParaMysql($dt1);
        return "update tb_esp_etapa_sel_proc set ESP_DT_PREV_RESUL_ETAPA = $dt1 where PCH_ID_CHAMADA = '$idChamada' and ESP_ID_ETAPA_SEL = '$idEtapaSel'";
    }

    public static function getSqlAlteracaoRecurso($idChamada, $idEtapaSel, $dt1, $dt2) {
        $dt1 = dt_dataStrParaMysql($dt1);
        $dt2 = dt_dataStrParaMysql($dt2);
        return "update tb_esp_etapa_sel_proc set ESP_DT_INI_RECURSO = $dt1, ESP_DT_FIM_RECURSO = $dt2 where PCH_ID_CHAMADA = '$idChamada' and ESP_ID_ETAPA_SEL = '$idEtapaSel'";
    }

    public static function getSqlAlteracaoPrevResulRecurso($idChamada, $idEtapaSel, $dt1) {
        $dt1 = dt_dataStrParaMysql($dt1);
        return "update tb_esp_etapa_sel_proc set ESP_DT_PREV_RESUL_REC = $dt1 where PCH_ID_CHAMADA = '$idChamada' and ESP_ID_ETAPA_SEL = '$idEtapaSel'";
    }

    public static function getSqlIdEtapaAvalEtapaSelAtivaCham($strIdChamada) {
        return "(select EAP_ID_ETAPA_AVAL_PROC from tb_esp_etapa_sel_proc where PCH_ID_CHAMADA = $strIdChamada and ESP_ETAPA_ATIVA = '" . FLAG_BD_SIM . "')";
    }

    public static function _getSqlCriarEtapaSel($idProcesso, $idChamada) {
        $classifPen = self::$CLASSIF_PENDENTE;
        $sitFechada = self::$SIT_FECHADA;

        $sql = "insert into tb_esp_etapa_sel_proc
                (`PRC_ID_PROCESSO`,`PCH_ID_CHAMADA`, ESP_ST_CLASSIFICACAO, ESP_ST_ETAPA, EAP_ID_ETAPA_AVAL_PROC)
                select PRC_ID_PROCESSO, '$idChamada', '$classifPen', '$sitFechada', EAP_ID_ETAPA_AVAL_PROC from tb_eap_etapa_aval_proc where PRC_ID_PROCESSO = '$idProcesso' order by EAP_NR_ETAPA_AVAL";

        return $sql;
    }

    /**
     * Busca uma etapa por situacao. Todos os parametros sao 
     * obrigatorios
     * @param int $idChamada - Id da chamada do processo.
     * @param string $sqlRestricoes - Comando sql com restrições de busca da etapa.
     * @param boolean $ultima - Diz se e para incluir limite, ou seja, exibir apenas a última chamada com as especificações
     * @param boolean $ordemDecrescente Quando $ultima é TRUE, este parâmetro informa se é para considerar a ordenação decrescente
     * do número da etapa para determinar quem é a última etapa. Padrão é TRUE.
     * @return \EtapaSelProc
     * @throws NegocioException
     */
    private static function buscarEtapaPorRestricao($idChamada, $sqlRestricoes, $ultima = FALSE, $ordemDecrescente = TRUE) {
        try {

            // verificando restricoes da funcao
            if (Util::vazioNulo($idChamada) || Util::vazioNulo($sqlRestricoes)) {
                return NULL;
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    ESP_ID_ETAPA_SEL,
                    esp.PRC_ID_PROCESSO,
                    PCH_ID_CHAMADA,
                    EAP_NR_ETAPA_AVAL as ESP_NR_ETAPA_SEL,
                    esp.EAP_ID_ETAPA_AVAL_PROC,
                    DATE_FORMAT(`ESP_DT_INI_RECURSO`, '%d/%m/%Y') AS ESP_DT_INI_RECURSO,
                    DATE_FORMAT(`ESP_DT_FIM_RECURSO`, '%d/%m/%Y') AS ESP_DT_FIM_RECURSO,
                    ESP_ST_CLASSIFICACAO,
                    ESP_ST_ETAPA,
                    ESP_ID_USUARIO_RESP_REC,
                    ESP_ID_USUARIO_RESP_FIN,
                    ESP_ID_USUARIO_RESP_CLAS,
                    DATE_FORMAT(`ESP_LOG_DT_REG_CLAS`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_CLAS,
                    DATE_FORMAT(`ESP_LOG_DT_REG_REC`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_REC,
                    DATE_FORMAT(`ESP_LOG_DT_REG_FIN`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_FIN,
                    DATE_FORMAT(`ESP_DT_PREV_RESUL_ETAPA`, '%d/%m/%Y') AS ESP_DT_PREV_RESUL_ETAPA,
                    DATE_FORMAT(`ESP_LOG_DT_REG_RESUL`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_RESUL,
                    ESP_ID_USUARIO_RESP_RESUL,
                    DATE_FORMAT(`ESP_DT_PREV_RESUL_REC`, '%d/%m/%Y') AS ESP_DT_PREV_RESUL_REC,
                    ESP_URL_ARQUIVO_RESUL,
                    ESP_URL_ARQUIVO_POS_REC,
                    ESP_ARQ_ATUALIZADO,
                    ESP_ETAPA_ATIVA,
                    ESP_ST_FINALIZACAO,
                    DATE_FORMAT(`ESP_PUB_DT_SOL_RESUL_PAR`, '%d/%m/%Y %T') AS ESP_PUB_DT_SOL_RESUL_PAR,
                    DATE_FORMAT(`ESP_PUB_DT_SOL_RESUL_FIN`, '%d/%m/%Y %T') AS ESP_PUB_DT_SOL_RESUL_FIN,
                    ESP_PUB_USU_RESP_SOL_PAR,
                    ESP_PUB_USU_RESP_SOL_FIN,
                    usP.USR_DS_NOME as ESP_PUB_NM_USU_RESP_SOL_PAR,
                    usF.USR_DS_NOME as ESP_PUB_NM_USU_RESP_SOL_FIN
                from
                    tb_esp_etapa_sel_proc esp
                    join tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    left join tb_usr_usuario usP on usP.USR_ID_USUARIO = esp.ESP_PUB_USU_RESP_SOL_PAR
                    left join tb_usr_usuario usF on usF.USR_ID_USUARIO = esp.ESP_PUB_USU_RESP_SOL_FIN
                    where `PCH_ID_CHAMADA` = '$idChamada'
                    and $sqlRestricoes";

            if ($ultima) {
                $ordem = $ordemDecrescente ? "desc" : "";
                $sql .= " order by EAP_NR_ETAPA_AVAL $ordem limit 0,1";
            } else {
                $sql .= " order by EAP_NR_ETAPA_AVAL";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                return NULL;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);

            $etapaTemp = new EtapaSelProc($dados['ESP_ID_ETAPA_SEL'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['ESP_NR_ETAPA_SEL'], $dados['ESP_DT_INI_RECURSO'], $dados['ESP_DT_FIM_RECURSO'], $dados['ESP_ST_CLASSIFICACAO'], $dados['ESP_ST_ETAPA'], $dados['ESP_ID_USUARIO_RESP_REC'], $dados['ESP_ID_USUARIO_RESP_FIN'], $dados['ESP_ID_USUARIO_RESP_CLAS'], $dados['ESP_LOG_DT_REG_CLAS'], $dados['ESP_LOG_DT_REG_REC'], $dados['ESP_LOG_DT_REG_FIN'], $dados['ESP_DT_PREV_RESUL_ETAPA'], $dados['ESP_LOG_DT_REG_RESUL'], $dados['ESP_ID_USUARIO_RESP_RESUL'], $dados['ESP_DT_PREV_RESUL_REC'], $dados['ESP_URL_ARQUIVO_RESUL'], $dados['ESP_URL_ARQUIVO_POS_REC'], $dados['EAP_ID_ETAPA_AVAL_PROC'], $dados['ESP_ARQ_ATUALIZADO'], $dados['ESP_ETAPA_ATIVA'], $dados['ESP_ST_FINALIZACAO'], $dados['ESP_PUB_DT_SOL_RESUL_PAR'], $dados['ESP_PUB_DT_SOL_RESUL_FIN'], $dados['ESP_PUB_USU_RESP_SOL_PAR'], $dados['ESP_PUB_USU_RESP_SOL_FIN']);
            $etapaTemp->ESP_PUB_NM_USU_RESP_SOL_PAR = $dados['ESP_PUB_NM_USU_RESP_SOL_PAR'];
            $etapaTemp->ESP_PUB_NM_USU_RESP_SOL_FIN = $dados['ESP_PUB_NM_USU_RESP_SOL_FIN'];

            return $etapaTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapa do processo.", $e);
        }
    }

    public static function contarEtapaPorSit($idChamada, $stEtapa, $idEtapaAval = NULL) {
        try {

            // verificando restricoes da funcao
            if (Util::vazioNulo($idChamada) || Util::vazioNulo($stEtapa)) {
                return NULL;
            }


            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from
                    tb_esp_etapa_sel_proc
                    where `PCH_ID_CHAMADA` = '$idChamada'
                    and ESP_ST_ETAPA = '$stEtapa'";

            // caso de incluir etapa de avaliação
            if ($idEtapaAval != NULL) {
                $sql .= " and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar etapas de seleção do processo.", $e);
        }
    }

    public static function contarEtapaPorSitClassificacao($idChamada, $stClassificacaoEtapa, $idEtapaAval = NULL) {
        try {

            // verificando restricoes da funcao
            if (Util::vazioNulo($idChamada) || Util::vazioNulo($stClassificacaoEtapa)) {
                return NULL;
            }


            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from
                    tb_esp_etapa_sel_proc
                    where `PCH_ID_CHAMADA` = '$idChamada'
                    and ESP_ST_CLASSIFICACAO = '$stClassificacaoEtapa'";

            // caso de incluir etapa de avaliação
            if ($idEtapaAval != NULL) {
                $sql .= " and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar etapas de seleção do processo.", $e);
        }
    }

    public static function contarEtapaPorUsuResp($idUsuResp) {
        try {

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from
                    tb_esp_etapa_sel_proc
                    where `ESP_ID_USUARIO_RESP_REC` = '$idUsuResp'
                    or `ESP_ID_USUARIO_RESP_FIN` = '$idUsuResp'
                    or `ESP_ID_USUARIO_RESP_CLAS` = '$idUsuResp'
                    or `ESP_ID_USUARIO_RESP_RESUL` = '$idUsuResp'
                    or `ESP_PUB_USU_RESP_SOL_PAR` = '$idUsuResp'
                    or `ESP_PUB_USU_RESP_SOL_FIN` = '$idUsuResp'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar etapas de seleção do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idChamada - Campo opcional
     * @param int $nrEtapa - Campo opcional
     * @param int $stEtapa - Campo opcional
     * @return int
     * @throws NegocioException
     */
    public static function contarEtapaPorProcNum($idProcesso, $idChamada = NULL, $nrEtapa = NULL, $stEtapa = NULL) {
        try {
            // verificando restricoes da funcao
            if (Util::vazioNulo($idProcesso)) {
                return NULL;
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from
                    tb_esp_etapa_sel_proc esp
                    join tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where esp.`PRC_ID_PROCESSO` = '$idProcesso'";

            if ($idChamada != NULL) {
                $sql .= " and PCH_ID_CHAMADA = '$idChamada'";
            }


            // caso de número de etapa não ser nulo
            if ($nrEtapa != NULL) {
                $sql .= " and EAP_NR_ETAPA_AVAL = '$nrEtapa'";
            }

            // caso de situacao nao ser nula
            if ($stEtapa != NULL) {
                $sql .= " and ESP_ST_ETAPA = '$stEtapa'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar etapas de seleção do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idChamada
     * @param int $idEtapaAvalProc 
     * @return \EtapaSelProc - Array com etapas
     * @throws NegocioException
     */
    public static function buscarEtapaPorChamada($idChamada, $idEtapaAvalProc = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    ESP_ID_ETAPA_SEL,
                    esp.PRC_ID_PROCESSO,
                    PCH_ID_CHAMADA,
                    EAP_NR_ETAPA_AVAL as ESP_NR_ETAPA_SEL,
                    esp.EAP_ID_ETAPA_AVAL_PROC,
                    DATE_FORMAT(`ESP_DT_INI_RECURSO`, '%d/%m/%Y') AS ESP_DT_INI_RECURSO,
                    DATE_FORMAT(`ESP_DT_FIM_RECURSO`, '%d/%m/%Y') AS ESP_DT_FIM_RECURSO,
                    ESP_ST_CLASSIFICACAO,
                    ESP_ST_ETAPA,
                    ESP_ID_USUARIO_RESP_REC,
                    ESP_ID_USUARIO_RESP_FIN,
                    ESP_ID_USUARIO_RESP_CLAS,
                    DATE_FORMAT(`ESP_LOG_DT_REG_CLAS`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_CLAS,
                    DATE_FORMAT(`ESP_LOG_DT_REG_REC`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_REC,
                    DATE_FORMAT(`ESP_LOG_DT_REG_FIN`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_FIN,
                    DATE_FORMAT(`ESP_DT_PREV_RESUL_ETAPA`, '%d/%m/%Y') AS ESP_DT_PREV_RESUL_ETAPA,
                    DATE_FORMAT(`ESP_LOG_DT_REG_RESUL`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_RESUL,
                    ESP_ID_USUARIO_RESP_RESUL,
                    DATE_FORMAT(`ESP_DT_PREV_RESUL_REC`, '%d/%m/%Y') AS ESP_DT_PREV_RESUL_REC,
                    ESP_URL_ARQUIVO_RESUL,
                    ESP_URL_ARQUIVO_POS_REC,
                    ESP_ARQ_ATUALIZADO,
                    ESP_ETAPA_ATIVA,
                    ESP_ST_FINALIZACAO,
                    DATE_FORMAT(`ESP_PUB_DT_SOL_RESUL_PAR`, '%d/%m/%Y %T') AS ESP_PUB_DT_SOL_RESUL_PAR,
                    DATE_FORMAT(`ESP_PUB_DT_SOL_RESUL_FIN`, '%d/%m/%Y %T') AS ESP_PUB_DT_SOL_RESUL_FIN,
                    ESP_PUB_USU_RESP_SOL_PAR,
                    ESP_PUB_USU_RESP_SOL_FIN,
                    usP.USR_DS_NOME as ESP_PUB_NM_USU_RESP_SOL_PAR,
                    usF.USR_DS_NOME as ESP_PUB_NM_USU_RESP_SOL_FIN
                    from
                    tb_esp_etapa_sel_proc esp
                    join tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    left join tb_usr_usuario usP on usP.USR_ID_USUARIO = esp.ESP_PUB_USU_RESP_SOL_PAR
                    left join tb_usr_usuario usF on usF.USR_ID_USUARIO = esp.ESP_PUB_USU_RESP_SOL_FIN
                    where `PCH_ID_CHAMADA` = '$idChamada' ";

            // caso etapa de avaliação 
            if ($idEtapaAvalProc != NULL) {
                $sql .= " and esp.EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAvalProc'";
            }

            // ordenação
            $sql .= " order by EAP_NR_ETAPA_AVAL";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }

            $vetRetorno = array();

            //realizando iteração para recuperar dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $etapaTemp = new EtapaSelProc($dados['ESP_ID_ETAPA_SEL'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['ESP_NR_ETAPA_SEL'], $dados['ESP_DT_INI_RECURSO'], $dados['ESP_DT_FIM_RECURSO'], $dados['ESP_ST_CLASSIFICACAO'], $dados['ESP_ST_ETAPA'], $dados['ESP_ID_USUARIO_RESP_REC'], $dados['ESP_ID_USUARIO_RESP_FIN'], $dados['ESP_ID_USUARIO_RESP_CLAS'], $dados['ESP_LOG_DT_REG_CLAS'], $dados['ESP_LOG_DT_REG_REC'], $dados['ESP_LOG_DT_REG_FIN'], $dados['ESP_DT_PREV_RESUL_ETAPA'], $dados['ESP_LOG_DT_REG_RESUL'], $dados['ESP_ID_USUARIO_RESP_RESUL'], $dados['ESP_DT_PREV_RESUL_REC'], $dados['ESP_URL_ARQUIVO_RESUL'], $dados['ESP_URL_ARQUIVO_POS_REC'], $dados['EAP_ID_ETAPA_AVAL_PROC'], $dados['ESP_ARQ_ATUALIZADO'], $dados['ESP_ETAPA_ATIVA'], $dados['ESP_ST_FINALIZACAO'], $dados['ESP_PUB_DT_SOL_RESUL_PAR'], $dados['ESP_PUB_DT_SOL_RESUL_FIN'], $dados['ESP_PUB_USU_RESP_SOL_PAR'], $dados['ESP_PUB_USU_RESP_SOL_FIN']);
                $etapaTemp->ESP_PUB_NM_USU_RESP_SOL_PAR = $dados['ESP_PUB_NM_USU_RESP_SOL_PAR'];
                $etapaTemp->ESP_PUB_NM_USU_RESP_SOL_FIN = $dados['ESP_PUB_NM_USU_RESP_SOL_FIN'];

                //adicionando no vetor
                $vetRetorno[$i] = $etapaTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapas do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idEtapaSel
     * @return EtapaSelProc 
     * @throws NegocioException
     */
    public static function buscarEtapaSelPorId($idEtapaSel) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    ESP_ID_ETAPA_SEL,
                    esp.PRC_ID_PROCESSO,
                    PCH_ID_CHAMADA,
                    EAP_NR_ETAPA_AVAL as ESP_NR_ETAPA_SEL,
                    esp.EAP_ID_ETAPA_AVAL_PROC,
                    DATE_FORMAT(`ESP_DT_INI_RECURSO`, '%d/%m/%Y') AS ESP_DT_INI_RECURSO,
                    DATE_FORMAT(`ESP_DT_FIM_RECURSO`, '%d/%m/%Y') AS ESP_DT_FIM_RECURSO,
                    ESP_ST_CLASSIFICACAO,
                    ESP_ST_ETAPA,
                    ESP_ID_USUARIO_RESP_REC,
                    ESP_ID_USUARIO_RESP_FIN,
                    ESP_ID_USUARIO_RESP_CLAS,
                    DATE_FORMAT(`ESP_LOG_DT_REG_CLAS`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_CLAS,
                    DATE_FORMAT(`ESP_LOG_DT_REG_REC`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_REC,
                    DATE_FORMAT(`ESP_LOG_DT_REG_FIN`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_FIN,
                    DATE_FORMAT(`ESP_DT_PREV_RESUL_ETAPA`, '%d/%m/%Y') AS ESP_DT_PREV_RESUL_ETAPA,
                    DATE_FORMAT(`ESP_LOG_DT_REG_RESUL`, '%d/%m/%Y %T') AS ESP_LOG_DT_REG_RESUL,
                    ESP_ID_USUARIO_RESP_RESUL,
                    DATE_FORMAT(`ESP_DT_PREV_RESUL_REC`, '%d/%m/%Y') AS ESP_DT_PREV_RESUL_REC,
                    ESP_URL_ARQUIVO_RESUL,
                    ESP_URL_ARQUIVO_POS_REC,
                    ESP_ARQ_ATUALIZADO,
                    ESP_ETAPA_ATIVA,
                    ESP_ST_FINALIZACAO,
                    DATE_FORMAT(`ESP_PUB_DT_SOL_RESUL_PAR`, '%d/%m/%Y %T') AS ESP_PUB_DT_SOL_RESUL_PAR,
                    DATE_FORMAT(`ESP_PUB_DT_SOL_RESUL_FIN`, '%d/%m/%Y %T') AS ESP_PUB_DT_SOL_RESUL_FIN,
                    ESP_PUB_USU_RESP_SOL_PAR,
                    ESP_PUB_USU_RESP_SOL_FIN,
                    usP.USR_DS_NOME as ESP_PUB_NM_USU_RESP_SOL_PAR,
                    usF.USR_DS_NOME as ESP_PUB_NM_USU_RESP_SOL_FIN
                    from
                    tb_esp_etapa_sel_proc esp
                    join tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    left join tb_usr_usuario usP on usP.USR_ID_USUARIO = esp.ESP_PUB_USU_RESP_SOL_PAR
                    left join tb_usr_usuario usF on usF.USR_ID_USUARIO = esp.ESP_PUB_USU_RESP_SOL_FIN
                    where `ESP_ID_ETAPA_SEL` = '$idEtapaSel' ";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                // exceção
                throw new NegocioException("Etapa de seleção não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);

            $etapaTemp = new EtapaSelProc($dados['ESP_ID_ETAPA_SEL'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['ESP_NR_ETAPA_SEL'], $dados['ESP_DT_INI_RECURSO'], $dados['ESP_DT_FIM_RECURSO'], $dados['ESP_ST_CLASSIFICACAO'], $dados['ESP_ST_ETAPA'], $dados['ESP_ID_USUARIO_RESP_REC'], $dados['ESP_ID_USUARIO_RESP_FIN'], $dados['ESP_ID_USUARIO_RESP_CLAS'], $dados['ESP_LOG_DT_REG_CLAS'], $dados['ESP_LOG_DT_REG_REC'], $dados['ESP_LOG_DT_REG_FIN'], $dados['ESP_DT_PREV_RESUL_ETAPA'], $dados['ESP_LOG_DT_REG_RESUL'], $dados['ESP_ID_USUARIO_RESP_RESUL'], $dados['ESP_DT_PREV_RESUL_REC'], $dados['ESP_URL_ARQUIVO_RESUL'], $dados['ESP_URL_ARQUIVO_POS_REC'], $dados['EAP_ID_ETAPA_AVAL_PROC'], $dados['ESP_ARQ_ATUALIZADO'], $dados['ESP_ETAPA_ATIVA'], $dados['ESP_ST_FINALIZACAO'], $dados['ESP_PUB_DT_SOL_RESUL_PAR'], $dados['ESP_PUB_DT_SOL_RESUL_FIN'], $dados['ESP_PUB_USU_RESP_SOL_PAR'], $dados['ESP_PUB_USU_RESP_SOL_FIN']);
            $etapaTemp->ESP_PUB_NM_USU_RESP_SOL_PAR = $dados['ESP_PUB_NM_USU_RESP_SOL_PAR'];
            $etapaTemp->ESP_PUB_NM_USU_RESP_SOL_FIN = $dados['ESP_PUB_NM_USU_RESP_SOL_FIN'];

            return $etapaTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapa de seleção.", $e);
        }
    }

    public static function getMsgHtmlEtapaFechadaNota() {
        return "Esta etapa está fechada no momento.<br/>Aguarde a conclusão das etapas anteriores para visualizar as notas dessa etapa.";
    }

    public static function getMsgHtmlEtapaIncompleta() {
        return "Esta etapa é antiga e não possui categorias de avaliação.";
    }

    public static function getMsgHtmlEtapaFechadaRec() {
        return "Esta etapa está fechada para recursos no momento.<br/>Aguarde a conclusão das etapas anteriores para visualizar os recursos dessa etapa.";
    }

    /**
     * Função que retorna o html da flag da etapa.
     * 
     * 
     * @param ProcessoChamada $chamada Chamada ligada à etapa
     * @return string HTML da flag de acordo com o status da etapa
     */
    public function getHtmlFlagEtapa($chamada) {

        // A etapa está finalizada
        if ($this->isFinalizada()) {
            // tag finalizada
            return "<span class='label label-default'>Etapa finalizada</span>";
        }

        // caso de recurso
        if ($this->isEmPeriodoRecurso()) {
            return "<span class='label label-success'><a title='Clique aqui para protocolizar um recurso contra o resultado do edital' target='_blank' href='{$this->getHrefprotocolizarRecurso()}'>" . self::$TAG_protocolizar_RECURSO . "</a></span>";
        }

        // etapa corrente? 
        if ($this->isEtapaCorrente() && $chamada->isMostrarProxFase()) {
            // tag da fase atual
            $faseAtual = str_replace(ProcessoChamada::$PREPOSICAO_FASE_CHAMADA_DA . " " . $this->getNomeEtapa(), "", $chamada->getDsFaseChamada());

            return "<span class='label label-success'>$faseAtual</span>";
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @return string
     */
    public function getHtmlCaixaEtapa($chamada) {
        return $this->htmlCaixaParte1($chamada) . $this->htmlCaixaParte2() . ($this->exibirMsgAsterisco ? ProcessoChamada::$UM_ASTERISCO_HTML_MSG : "");
    }

    public function getHtmlResulParcialAdmin() {
        // finalização forçada
        if ($this->isFinalizacaoForcada(self::$PENDENTE_RESUL_PARCIAL)) {
            return "<p><i class='fa fa-file-o'></i> Resultado parcial</p><p><i class='fa fa-calendar'></i> Data prevista: {$this->getHtmlFinalizacaoForcada(self::$PENDENTE_RESUL_PARCIAL)}</p>";
        }

        // Etapa está aberta? 
        if ($this->isAberta() || $this->isFechada()) {
            // mostrando previsão do resultado parcial
            $data = ProcessoChamada::getHtmlData($this->ESP_DT_PREV_RESUL_ETAPA);
            return "<p><i class='fa fa-file-o'></i> Resultado parcial</p><p><i class='fa fa-calendar'></i> Data prevista: $data</p>";
        } else {

            // mostrando link do resultado parcial
            $retorno = "<p><i class='fa fa-file-pdf-o'></i> <a target='_blank' href='{$this->getUrlArquivoResulParcial()}' title='Visualizar o resultado parcial da etapa'>Resultado parcial da etapa <i class='fa fa-external-link'></i></a></p>";

            // atualização?
            $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($this->ESP_URL_ARQUIVO_RESUL, $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);
            if ($dtAtualizacao != NULL) {
                $retorno .= "<p><i class='fa fa-calendar'></i> Atualizado em: $dtAtualizacao</p>";
            } else {
                $retorno .= "<p><i class='fa fa-calendar'></i> Publicado em: {$this->getESP_LOG_DT_REG_RESUL(true)}</p>";
            }

            return $retorno;
        }
    }

    public function getHTMLRecursosAdmin() {
        // finalização forçada
        if ($this->isFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)) {
            return "<p><i class='fa fa-bookmark-o'></i> Protocolização de recurso: {$this->getHtmlFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)}</p>";
        }

        // data do recurso
        $data = ProcessoChamada::getHtmlData($this->ESP_DT_INI_RECURSO, $this->ESP_DT_FIM_RECURSO);

        // Ainda não chegou o período de recursos
        if (($this->isAberta() || $this->isFechada()) || (!$this->isEmPeriodoRecurso() && !$this->isPeriodoRecursoAnterior())) {
            $ret = "<p><i class='fa fa-bookmark-o'></i> Protocolização de recurso: $data</p>";
        } else {
            // período de recurso ativo ou finalizado
            $ret = "<p><i class='fa fa-bookmark'></i> Protocolização de recurso: $data</p>";
        }
        return $ret;
    }

    public function getHTMLResulFinalAdmin() {
        // finalização forçada
        if ($this->isFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)) {
            return "<p><i class='fa fa-file-o'></i> Resultado pós-recursos</p><p><i class='fa fa-calendar'></i> Data prevista: {$this->getHtmlFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)}</p>";
        }

        // tratando exibição do resultado pós recurso
        if (!$this->publicouResultadoPosRec()) {
            // mostrando previsão do período pós recurso
            $data = ProcessoChamada::getHtmlData($this->ESP_DT_PREV_RESUL_REC);
            $ret = "<p><i class='fa fa-file-o'></i> Resultado pós-recursos</p><p><i class='fa fa-calendar'></i> Data prevista: $data</p>";
        } else {

            // já publicou pós recurso: mostrando link
            $ret = "<p><i class='fa fa-file-pdf-o'></i> <a target='_blank' href='{$this->getUrlArquivoResulPosRec()}' title='Visualizar o resultado pós-recursos'>Resultado pós-recursos <i class='fa fa-external-link'></i></a></p>";

            // atualização?
            $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($this->ESP_URL_ARQUIVO_POS_REC, $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);
            if ($dtAtualizacao != NULL) {
                $ret .= "<p><i class='fa fa-calendar'></i> Atualizado em: $dtAtualizacao</p>";
            } else {
                $ret .= "<p><i class='fa fa-calendar'></i> Publicado em: {$this->getESP_LOG_DT_REG_REC(true)}</p>";
            }
        }
        return $ret;
    }

    /**
     *  Html da parte 1: Edital
     * 
     * @param ProcessoChamada $chamada
     * @return string
     */
    private function htmlCaixaParte1($chamada) {
        // finalização forçada
        if ($this->isFinalizacaoForcada(self::$PENDENTE_RESUL_PARCIAL)) {
            return "<p><i class='fa fa-file-o'></i> Resultado parcial: {$this->getHtmlFinalizacaoForcada(self::$PENDENTE_RESUL_PARCIAL)}</p>";
        }

        // Etapa está aberta? 
        if ($this->isAberta() || $this->isFechada()) {
            // mostrando previsão do resultado parcial
            $um = $this->ESP_DT_PREV_RESUL_ETAPA != NULL ? ProcessoChamada::$UM_ASTERISCO : "";
            $this->exibirMsgAsterisco = $this->ESP_DT_PREV_RESUL_ETAPA != NULL;
            $data = ProcessoChamada::getHtmlData($this->ESP_DT_PREV_RESUL_ETAPA);
            return "<p><i class='fa fa-file-o'></i> Resultado parcial: $data $um</p>";
        } else {

            // mostrando link do resultado parcial
            $retorno = "<p><i class='fa fa-file-pdf-o'></i> <a target='_blank' href='{$this->getUrlArquivoResulParcial()}' title='Visualizar o resultado parcial da etapa'>Resultado parcial da etapa <i class='fa fa-external-link'></i></a></p>";

            // mostrando link resultado final provisório
            if ($this->isUltimaEtapa()) {
                $retorno .= "<p><i class='fa fa-file-pdf-o'></i> <a target='_blank' href='{$chamada->getUrlArquivoResulFinal(TRUE)}' title='Visualizar o resultado final provisório'>Resultado final provisório <i class='fa fa-external-link'></i></a></p>";
            }

            // atualização?
            $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($this->ESP_URL_ARQUIVO_RESUL, $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);
            if ($dtAtualizacao != NULL) {
                $retorno .= "<p><i class='fa fa-calendar'></i> Atualizado em: <a href='#atualizacoes'>$dtAtualizacao</a></p>";
            } else {
                $retorno .= "<p><i class='fa fa-calendar'></i> Publicado em: {$this->getESP_LOG_DT_REG_RESUL(true)}</p>";
            }

            return $retorno;
        }
    }

    // Html da parte 2: Recurso
    private function htmlCaixaParte2() {
        // finalização forçada
        if ($this->isFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)) {
            $arq = "<p><i class='fa fa-file-o'></i> Resultado pós-recursos: {$this->getHtmlFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)}</p>";

            // parte 1
            $rec = "<p><i class='fa fa-bookmark-o'></i> Protocolização de recurso: <a onclick='javascript: return false;' title='Não é possível realizar protocolização de recurso' target='_blank'>{$this->getHtmlFinalizacaoForcada(self::$PENDENTE_RESUL_POS_REC)}</a></p>";

            return $rec . $arq;
        }


        // tratando exibição do resultado pós recurso
        if (!$this->publicouResultadoPosRec()) {
            // mostrando previsão do período pós recurso
            $um = $this->ESP_DT_PREV_RESUL_REC != NULL ? ProcessoChamada::$UM_ASTERISCO : "";
            $this->exibirMsgAsterisco = $this->ESP_DT_PREV_RESUL_REC != NULL;
            $data = ProcessoChamada::getHtmlData($this->ESP_DT_PREV_RESUL_REC);
            $posRec = "<p><i class='fa fa-file-o'></i> Resultado pós-recursos: $data $um</p>";
        } else {

            // já publicou pós recurso: mostrando link
            $posRec = "<p><i class='fa fa-file-pdf-o'></i> <a target='_blank' href='{$this->getUrlArquivoResulPosRec()}' title='Visualizar o resultado pós-recursos'>Resultado pós-recursos <i class='fa fa-external-link'></i></a></p>";

            // atualização?
            $dtAtualizacao = AcompProcChamada::getDtAtualizacaoArquivoPorCham($this->ESP_URL_ARQUIVO_POS_REC, $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);
            if ($dtAtualizacao != NULL) {
                $posRec .= "<p><i class='fa fa-calendar'></i> Atualizado em: <a href='#atualizacoes'>$dtAtualizacao</a></p>";
            } else {
                $posRec .= "<p><i class='fa fa-calendar'></i> Publicado em: {$this->getESP_LOG_DT_REG_REC(true)}</p>";
            }
        }


        // Ainda não chegou o período de recursos
        $data = ProcessoChamada::getHtmlData($this->ESP_DT_INI_RECURSO, $this->ESP_DT_FIM_RECURSO);
        if (($this->isAberta() || $this->isFechada()) || (!$this->isEmPeriodoRecurso() && !$this->isPeriodoRecursoAnterior())) {
            // mostrando previsão do período de recurso
            $um = $this->ESP_DT_INI_RECURSO != NULL ? ProcessoChamada::$UM_ASTERISCO : "";
            $this->exibirMsgAsterisco = $this->ESP_DT_INI_RECURSO != NULL;
            $titulo = $this->isPeriodoRecursoPosterior() ? "Aguarde o período de recursos para fazer uma protocolização" : "Aguarde a publicação do resultado parcial";
            $ret = "<p><i class='fa fa-bookmark-o'></i> Protocolização de recurso: <a onclick='javascript: return false;' title='$titulo' target='_blank' href='{$this->getHrefprotocolizarRecurso()}'>$data</a> $um</p>";
        } elseif ($this->isEmPeriodoRecurso()) {
            // em período de recurso
            $ret = "<p><i class='fa fa-bookmark'></i> Protocolização de recurso: <a title='Clique aqui para protocolizar um recurso contra o resultado do edital.' target='_blank' href='{$this->getHrefprotocolizarRecurso()}'>{$data} <i class='fa fa-external-link'></i></a></p>";
        } else {
            // período de recurso finalizado
            $ret = "<p><i class='fa fa-bookmark'></i> Protocolização de recurso: <a onclick='javascript: return false;' title='Período de recursos finalizado.' target='_blank' href='{$this->getHrefprotocolizarRecurso()}'>{$data}</a></p>";
        }

        $ret .= $posRec;

        return $ret;
    }

    private function getHrefprotocolizarRecurso() {
        global $CFG;
        return "$CFG->rwww/visao/recurso/criarRecursoUsu.php?idProcesso={$this->PRC_ID_PROCESSO}&idChamada={$this->PCH_ID_CHAMADA}";
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_esp_etapa_sel_proc where PRC_ID_PROCESSO = '$idProcesso'";
    }

    /* GET FIELDS FROM TABLE */

    function getESP_ID_ETAPA_SEL() {
        return $this->ESP_ID_ETAPA_SEL;
    }

    /* End of get ESP_ID_ETAPA_SEL */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getESP_NR_ETAPA_SEL() {
        return $this->ESP_NR_ETAPA_SEL;
    }

    /* End of get ESP_NR_ETAPA_SEL */

    function getESP_DT_INI_RECURSO() {
        return $this->ESP_DT_INI_RECURSO;
    }

    /* End of get ESP_DT_INI_RECURSO */

    function getESP_DT_FIM_RECURSO() {
        return $this->ESP_DT_FIM_RECURSO;
    }

    /* End of get ESP_DT_FIM_RECURSO */

    function getESP_ST_CLASSIFICACAO() {
        return $this->ESP_ST_CLASSIFICACAO;
    }

    /* End of get ESP_ST_CLASSIFICACAO */

    function getESP_ST_ETAPA() {
        return $this->ESP_ST_ETAPA;
    }

    /* End of get ESP_ST_ETAPA */

    function getESP_ID_USUARIO_RESP_REC() {
        return $this->ESP_ID_USUARIO_RESP_REC;
    }

    /* End of get ESP_ID_USUARIO_RESP_REC */

    function getESP_ID_USUARIO_RESP_FIN() {
        return $this->ESP_ID_USUARIO_RESP_FIN;
    }

    /* End of get ESP_ID_USUARIO_RESP_FIN */

    function getESP_ID_USUARIO_RESP_CLAS() {
        return $this->ESP_ID_USUARIO_RESP_CLAS;
    }

    /* End of get ESP_ID_USUARIO_RESP_CLAS */

    function getESP_LOG_DT_REG_CLAS() {
        return $this->ESP_LOG_DT_REG_CLAS;
    }

    /* End of get ESP_LOG_DT_REG_CLAS */

    function getESP_LOG_DT_REG_REC($apenasData = FALSE) {
        if ($apenasData) {
            if (Util::vazioNulo($this->ESP_LOG_DT_REG_REC)) {
                return Util::$STR_CAMPO_VAZIO;
            }
            $temp = explode(" ", $this->ESP_LOG_DT_REG_REC);
            return $temp[0];
        }
        return $this->ESP_LOG_DT_REG_REC;
    }

    /* End of get ESP_LOG_DT_REG_REC */

    function getESP_LOG_DT_REG_FIN() {
        return $this->ESP_LOG_DT_REG_FIN;
    }

    /* End of get ESP_LOG_DT_REG_FIN */

    function getESP_DT_PREV_RESUL_ETAPA() {
        return $this->ESP_DT_PREV_RESUL_ETAPA;
    }

    /* End of get ESP_DT_PREV_RESUL_ETAPA */

    function getESP_LOG_DT_REG_RESUL($apenasData = FALSE) {
        if ($apenasData) {
            if (Util::vazioNulo($this->ESP_LOG_DT_REG_RESUL)) {
                return Util::$STR_CAMPO_VAZIO;
            }
            $temp = explode(" ", $this->ESP_LOG_DT_REG_RESUL);
            return $temp[0];
        }
        return $this->ESP_LOG_DT_REG_RESUL;
    }

    /* End of get ESP_LOG_DT_REG_RESUL */

    function getESP_ID_USUARIO_RESP_RESUL() {
        return $this->ESP_ID_USUARIO_RESP_RESUL;
    }

    /* End of get ESP_ID_USUARIO_RESP_RESUL */

    function getESP_DT_PREV_RESUL_REC() {
        return $this->ESP_DT_PREV_RESUL_REC;
    }

    /* End of get ESP_DT_PREV_RESUL_REC */

    function getEAP_ID_ETAPA_AVAL_PROC() {
        return $this->EAP_ID_ETAPA_AVAL_PROC;
    }

    /* SET FIELDS FROM TABLE */

    function setESP_ID_ETAPA_SEL($value) {
        $this->ESP_ID_ETAPA_SEL = $value;
    }

    /* End of SET ESP_ID_ETAPA_SEL */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setESP_NR_ETAPA_SEL($value) {
        $this->ESP_NR_ETAPA_SEL = $value;
    }

    /* End of SET ESP_NR_ETAPA_SEL */

    function setESP_DT_INI_RECURSO($value) {
        $this->ESP_DT_INI_RECURSO = $value;
    }

    /* End of SET ESP_DT_INI_RECURSO */

    function setESP_DT_FIM_RECURSO($value) {
        $this->ESP_DT_FIM_RECURSO = $value;
    }

    /* End of SET ESP_DT_FIM_RECURSO */

    function setESP_ST_CLASSIFICACAO($value) {
        $this->ESP_ST_CLASSIFICACAO = $value;
    }

    /* End of SET ESP_ST_CLASSIFICACAO */

    function setESP_ST_ETAPA($value) {
        $this->ESP_ST_ETAPA = $value;
    }

    /* End of SET ESP_ST_ETAPA */

    function setESP_ID_USUARIO_RESP_REC($value) {
        $this->ESP_ID_USUARIO_RESP_REC = $value;
    }

    /* End of SET ESP_ID_USUARIO_RESP_REC */

    function setESP_ID_USUARIO_RESP_FIN($value) {
        $this->ESP_ID_USUARIO_RESP_FIN = $value;
    }

    /* End of SET ESP_ID_USUARIO_RESP_FIN */

    function setESP_ID_USUARIO_RESP_CLAS($value) {
        $this->ESP_ID_USUARIO_RESP_CLAS = $value;
    }

    /* End of SET ESP_ID_USUARIO_RESP_CLAS */

    function setESP_LOG_DT_REG_CLAS($value) {
        $this->ESP_LOG_DT_REG_CLAS = $value;
    }

    /* End of SET ESP_LOG_DT_REG_CLAS */

    function setESP_LOG_DT_REG_REC($value) {
        $this->ESP_LOG_DT_REG_REC = $value;
    }

    /* End of SET ESP_LOG_DT_REG_REC */

    function setESP_LOG_DT_REG_FIN($value) {
        $this->ESP_LOG_DT_REG_FIN = $value;
    }

    /* End of SET ESP_LOG_DT_REG_FIN */

    function setESP_DT_PREV_RESUL_ETAPA($value) {
        $this->ESP_DT_PREV_RESUL_ETAPA = $value;
    }

    /* End of SET ESP_DT_PREV_RESUL_ETAPA */

    function setESP_LOG_DT_REG_RESUL($value) {
        $this->ESP_LOG_DT_REG_RESUL = $value;
    }

    /* End of SET ESP_LOG_DT_REG_RESUL */

    function setESP_ID_USUARIO_RESP_RESUL($value) {
        $this->ESP_ID_USUARIO_RESP_RESUL = $value;
    }

    /* End of SET ESP_ID_USUARIO_RESP_RESUL */

    function setESP_DT_PREV_RESUL_REC($value) {
        $this->ESP_DT_PREV_RESUL_REC = $value;
    }

    /* End of SET ESP_DT_PREV_RESUL_REC */

    function setESP_URL_ARQUIVO_RESUL($value) {
        $this->ESP_URL_ARQUIVO_RESUL = $value;
    }

    /* End of SET ESP_URL_ARQUIVO_RESUL */

    function setESP_URL_ARQUIVO_POS_REC($value) {
        $this->ESP_URL_ARQUIVO_POS_REC = $value;
    }

    /* End of SET ESP_URL_ARQUIVO_POS_REC */

    function setEAP_ID_ETAPA_AVAL_PROC($EAP_ID_ETAPA_AVAL_PROC) {
        $this->EAP_ID_ETAPA_AVAL_PROC = $EAP_ID_ETAPA_AVAL_PROC;
    }

}

?>
