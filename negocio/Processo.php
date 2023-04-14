<?php

/**
 * tb_prc_processo class
 * This class manipulates the table Processo
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 16/10/2013
 * */
global $CFG;
require_once $CFG->rpasta . "/negocio/AcompProcChamada.php";

class Processo {

    private $PRC_ID_PROCESSO;
    private $TIC_ID_TIPO_CARGO;
    private $CUR_ID_CURSO;
    private $PRC_NR_EDITAL;
    private $PRC_ANO_EDITAL;
    private $PRC_DS_URL_EDITAL;
    private $PRC_DS_PROCESSO;
    private $PRC_DT_INICIO;
    private $PRC_DT_FIM;
    // processamento interno
    private $faseApresentacao = NULL; // FLAG que informa a fase do edital para apresentação. Apenas carregado ao utilizar a função buscarProcessosApresentacao
    public static $APRESENTACAO_NOVO = 'N';
    public static $APRESENTACAO_INSCRICAO = 'I';
    public static $APRESENTACAO_ANDAMENTO = 'A';
    public static $APRESENTACAO_FINALIZADO = 'F';
    // campos herdados
    public $TIC_NM_TIPO_CARGO;
    public $CUR_NM_CURSO;
    public $CUR_DS_CURSO;
    public $TPC_NM_TIPO_CURSO;
    public $PCH_DT_ABERTURA;
    public $PCH_DT_FECHAMENTO;
    public $PCH_ID_ULT_CHAMADA;
    public $PCH_DS_ULT_CHAMADA;
    public $PCH_NR_MAX_OPCAO_POLO;
    public $PCH_ADMITE_AREA;
    public $PCH_ADMITE_RESERVA_VAGA;
    public $PCH_INSCRICAO_MULTIPLA;
    public $PCH_CHAMADA_ATIVA;
    public $PCH_DT_REG_RESUL_FINAL;
    public $PCH_DT_FINALIZACAO;
    // herdados privados
    private $TIC_URL_BUSCA;
    private $CUR_URL_BUSCA;
    // constantes importantes
    public static $MAX_CARACTER_DS_EDITAL = 500;
    public static $TAM_MAX_ARQ_BYTES = 2097152; // 1024 * 1024 * 2; // 2MB
    public static $QTDE_DIAS_NOTICIA_RECENTE = 5; // Quantidade de dias em que uma notícia é considerada 'recente'
    public static $TEMPO_PADRAO_FINALIZACAO = 5; // Tempo padrão para finalização de um processo automaticamente
    //
    // processamento interno
    public static $PASTA_UPLOAD_EDITAIS = "editais";
    private static $TRADUCAO_TIPO_SIGLA_ARQ = array(1 => "al", 2 => 'cp', 3 => 'ct', 4 => 'pr', 5 => 'td', 6 => 'tp');
    //
    // Flags de exportação
    public static $EXP_DADO_GERAL = "G";
    public static $EXP_NOTA = "N";
    public static $EXP_RESULTADO = "E";
    public static $EXP_RECURSO = "R";

    public static function getDsTipoExp($tipo) {
        if ($tipo == self::$EXP_DADO_GERAL) {
            return "Dados Gerais";
        }
        if ($tipo == self::$EXP_NOTA) {
            return "Notas";
        }
        if ($tipo == self::$EXP_RESULTADO) {
            return "Resultado";
        }
        if ($tipo == self::$EXP_RECURSO) {
            return "Recursos";
        }
    }

    public static function getListaTipoDsTipoExp() {
        return array(self::$EXP_DADO_GERAL => self::getDsTipoExp(self::$EXP_DADO_GERAL),
            self::$EXP_NOTA => self::getDsTipoExp(self::$EXP_NOTA),
            self::$EXP_RESULTADO => self::getDsTipoExp(self::$EXP_RESULTADO),
            self::$EXP_RECURSO => self::getDsTipoExp(self::$EXP_RECURSO));
    }

    /* Construtor padrão da classe */

    public function __construct($PRC_ID_PROCESSO, $TIC_ID_TIPO_CARGO, $CUR_ID_CURSO, $PRC_NR_EDITAL, $PRC_ANO_EDITAL, $PRC_DS_URL_EDITAL, $PRC_DS_PROCESSO, $PRC_DT_INICIO, $PRC_DT_FIM) {
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->TIC_ID_TIPO_CARGO = $TIC_ID_TIPO_CARGO;
        $this->CUR_ID_CURSO = $CUR_ID_CURSO;
        $this->PRC_NR_EDITAL = $PRC_NR_EDITAL;
        $this->PRC_ANO_EDITAL = $PRC_ANO_EDITAL;
        $this->PRC_DS_URL_EDITAL = $PRC_DS_URL_EDITAL;
        $this->PRC_DS_PROCESSO = $PRC_DS_PROCESSO;
        $this->PRC_DT_INICIO = $PRC_DT_INICIO;
        $this->PRC_DT_FIM = $PRC_DT_FIM;
    }

#   ----------------------------- FUNÇÕES RELACIONADAS A MANIPULAÇÃO DE ARQUIVOS DO EDITAL -------------------------

    /**
     * Retorna o caminho onde deve ser salvo os arquivos do edital em questao.
     * 
     * Se o diretorio nao existir, ele é automaticamente criado.
     * 
     * NOTA: Essa funcao so funcionará corretamente se o número, ano,
     * atribuição e curso do edital estiverem corretamente preenchidos
     * 
     * 
     * @global stdClass $CFG
     * @return string Caminho do diretorio onde é salvo os arquivos do edital
     */
    public function getDiretorioUploadEdital() {
        global $CFG;
        $dir = "{$CFG->rpasta}/" . self::$PASTA_UPLOAD_EDITAIS . "/";

        // recuperando nome da pasta do edital
        $nmPasta = $this->getNomePastaEdital();

        $dir .= $nmPasta;

        if (!is_dir($dir)) {
            // criando pasta
            mkdir($dir);
        }

        return $dir;
    }

    /**
     * Esta função retorna o nome da pasta específica de um edital
     * 
     * @return string
     */
    public function getNomePastaEdital() {
        $tmp = self::$TRADUCAO_TIPO_SIGLA_ARQ[$this->TIC_ID_TIPO_CARGO];
        return "{$this->getPRC_NR_EDITAL()}-{$this->PRC_ANO_EDITAL}-$tmp-{$this->CUR_ID_CURSO}/";
    }

    /**
     * Esta função retorna o nome padrão, que deve estar contido em todos os arquivos relacionados ao edital
     * 
     * @return string
     */
    public function ARQS_getPadraoNomeArqsEdital() {
        $tmp = self::$TRADUCAO_TIPO_SIGLA_ARQ[$this->TIC_ID_TIPO_CARGO];
        return "{$this->getPRC_NR_EDITAL()}-{$this->PRC_ANO_EDITAL}-$tmp-{$this->CUR_ID_CURSO}";
    }

    private function ARQS_nomeArquivoEdital() {
        return "Edital-{$this->ARQS_getPadraoNomeArqsEdital()}.pdf";
    }

    private function ARQS_getUrlArqEdital() {
        return Processo::$PASTA_UPLOAD_EDITAIS . "/{$this->getNomePastaEdital()}{$this->ARQS_nomeArquivoEdital()}";
    }

    public function getUrlArquivoEdital() {
        global $CFG;

        // redirecionando para erro
        if (Util::vazioNulo($this->PRC_DS_URL_EDITAL)) {
            return "{$CFG->rwww}/404.php?err=arq";
        }

        return "{$CFG->rwww}/" . $this->PRC_DS_URL_EDITAL;
    }

#   ----------------------------- FIM DE FUNÇÕES RELACIONADAS A MANIPULAÇÃO DE ARQUIVOS DO EDITAL -------------------------    
#   
#   
#   
#   ----------------------------------- FUNÇÕES DE VERIFICAÇÃO DE STATUS --------------------------------------------------

    public function admitePoloObj() {
        return TipoCargo::idTipoAdmitePolo($this->TIC_ID_TIPO_CARGO);
    }

    public function isInscricaoMultipla() {
        return $this->admitePoloObj() && !Util::vazioNulo($this->PCH_INSCRICAO_MULTIPLA) && $this->PCH_INSCRICAO_MULTIPLA == FLAG_BD_SIM;
    }

    public function isAberto() {
        return dt_dataMenorIgual(dt_getTimestampDtBR($this->PRC_DT_INICIO), dt_getTimestampDtBR()) && !$this->isFechado();
    }

    public function isFechado() {
        return !Util::vazioNulo($this->PRC_DT_FIM) && dt_dataMenorIgual(dt_getTimestampDtBR($this->PRC_DT_FIM), dt_getTimestampDtBR());
    }

    public function isEmFinalizacao() {
        return !Util::vazioNulo($this->PRC_DT_FIM);
    }

    private function temChamadaAtiva() {
        return !Util::vazioNulo($this->PCH_ID_ULT_CHAMADA) && ProcessoChamada::isChamadaAtiva($this->PCH_CHAMADA_ATIVA);
    }

    public function temOpcaoInscricao() {
        return $this->admitePoloObj() || ProcessoChamada::admiteAreaAtuacao($this->PCH_ADMITE_AREA) || ProcessoChamada::admiteReservaVaga($this->PCH_ADMITE_RESERVA_VAGA);
    }

    /**
     * 
     * @param boolean $bloqFimProg Se True, a permissão de edição é bloqueada se a data de finalização já estiver definida
     * @return boolean
     */
    public function permiteEdicao($bloqFimProg = FALSE) {
        // processo nao esta fechado (no caso de $bloqFimProg = FALSE ou Data fim indefinida, no caso de $bloqFimProg = TRUE
        return (!$bloqFimProg && !$this->isFechado()) || ($bloqFimProg && $this->PRC_DT_FIM == NULL);
    }

    public function permiteExibirFluxo() {
        // tem pelo menos uma chamada
        return !Util::vazioNulo($this->PCH_ID_ULT_CHAMADA);
    }

    public function permiteExclusao() {
        // processo nao iniciado ou não tem chamada ativa
        return dt_dataMaior(dt_getTimestampDtBR($this->PRC_DT_INICIO), dt_getTimestampDtBR()) || !$this->temChamadaAtiva();
    }

    /**
     * Função que informa se pode ser exibido os botões de ação de um dado edital
     * 
     * @param ProcessoChamada $chamada Chamada a ser considerada. Se não informada, então é verificado apenas se o edital tem uma chamada ativa
     * @return boolean
     */
    public function permiteExibirAcaoCdt($chamada = NULL) {
        // processo iniciado e tem chamada ativa
        return dt_dataMenorIgual(dt_getTimestampDtBR($this->PRC_DT_INICIO), dt_getTimestampDtBR()) && (($chamada == NULL && $this->temChamadaAtiva() || $chamada != NULL && $chamada->isAtiva()));
    }

    /**
     * 
     * @param Processo $processo
     */
    public static function permiteComporNotaFinal($processo) {
        return $processo->permiteEdicao(TRUE) && EtapaAvalProc::contarEtapaAvalPorProc($processo->getPRC_ID_PROCESSO()) != 0 && EtapaAvalProc::podeAlterarUltimaEtapa($processo->getPRC_ID_PROCESSO());
    }

    /**
     * Função que verifica se é permitido criar uma nova chamada
     * 
     * Retorna um array na forma (flagPermissao, msgErro), onde:
     * flagPermissao - TRUE OU FALSE, indicando se é possível criar ou não
     * msgErro - Se flagPermissao é FALSE, então msgErro contém a mensagem explicando o motivo da negação
     * 
     * @param ProcessoChamada $listaChamadas lista de chamadas do processo
     * @return array
     */
    public function permiteCriarChamada($listaChamadas) {
        $strIni = "Não é possível criar uma nova Chamada pois ";

        // não permite edicao
        if (!$this->permiteEdicao()) {
            return array(FALSE, $strIni . "o edital está finalizado.");
        }


        // não tem chamadas?
        if (Util::vazioNulo($listaChamadas)) {
            // validando inf. complementares
            if (!GrupoAnexoProc::validarGrupoParaChamada($this->PRC_ID_PROCESSO)) {
                return array(FALSE, $strIni . "existem informações complementares de múltipla escolha sem opções de resposta.");
            }

            // validando etapas de avaliação
            $valEtapa = EtapaAvalProc::validarEtapaAvalParaChamada($this->PRC_ID_PROCESSO);
            if (!$valEtapa[0]) {
                return array(FALSE, $strIni . $valEtapa[1]);
            }

            // tem fórmula final
            $qtFomula = MacroConfProc::contarMacroPorProcEtapa($this->PRC_ID_PROCESSO, NULL, MacroConfProc::$TIPO_ESP_FORMULA_FINAL);
            if ($qtFomula == 0) {
                return array(FALSE, $strIni . "a fórmula da nota final não está configurada.");
            }

            // tem cadastro de reserva
            $qtCadReserva = MacroConfProc::contarMacroPorProcEtapa($this->PRC_ID_PROCESSO, NULL, MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA);
            if ($qtCadReserva == 0) {
                return array(FALSE, $strIni . "o critério de classificação para cadastro de reserva não está configurado.");
            }

            // tudo ok: Pode criar a chamada
            return array(TRUE);
        } else {
            // percorrendo chamadas
            foreach ($listaChamadas as $chamada) {
                if (!$chamada->isFinalizada()) {
                    return array(FALSE, $strIni . "existe uma Chamada em aberto.");
                }
            }
            // todas as chamadas existentes estão fechadas
            return array(TRUE);
        }
    }

#   ----------------------------------- FIM FUNÇÕES DE VERIFICAÇÃO DE STATUS --------------------------------------------------    
#
#    
#            
#   ----------------------------------- FUNÇÕES DE VALIDAÇÃO --------------------------------------------------   

    public function validaDataInicio() {
        try {
            if (Util::vazioNulo($this->PRC_DT_INICIO)) {
                return FALSE; // Data de início não pode ser vazia!
            }

            // recuperando timestamp da primeira chamada
            $dtPriChamada = ProcessoChamada::buscaDtUSPriChamadaDoProcesso($this->PRC_ID_PROCESSO);

            if ($dtPriChamada == NULL) {
                return TRUE; // ainda sem chamada
            }

            return dt_dataMenorIgual(dt_getTimestampDtBR($this->PRC_DT_INICIO), dt_getTimestampDtUS($dtPriChamada));
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar data de início do processo.", $e);
        }
    }

    /**
     * Função que verifica se é possível exportar dados do processo, baseado no tipo de exportação requisitado
     * e nas condições atuais do processo.
     * 
     * @param int $idProcesso
     * @param int $idTipoExportacao
     * @param int $idChamada
     * @param int $idEtapaAval
     *
     * @return array - Array tipo chave/valor. Caso queira verificar apenas validaçao, use a chave 'val'.
     * No caso de erro, a chave 'msg' contem a mensagem de erro.
     * 
     * @throws NegocioException
     */
    public static function validarExportacaoDados($idProcesso, $idTipoExportacao, $idChamada, $idEtapaAval) {
        try {
            $msgInicialPadrao = "Não é possível realizar a exportação, pois";

            // caso genérico
            // 
            // validando se há candidatos inscritos
            if (InscricaoProcesso::contaInscritosPorProcesso($idProcesso, NULL, NULL, NULL, NULL, NULL, $idChamada, NULL, NULL) == 0) {
                return array("val" => FALSE, "msg" => "$msgInicialPadrao não há candidatos inscritos nesta chamada.");
            }

            // caso de exportação de recursos
            if ($idTipoExportacao == self::$EXP_RECURSO) {
                // validando se há recursos
                if (RecursoResulProc::contarRecursoPorFiltro(NULL, NULL, NULL, $idChamada, $idEtapaAval) == 0) {
                    return array("val" => FALSE, "msg" => "$msgInicialPadrao não há recursos protocolizados para esta chamada e etapa.");
                }
            } elseif ($idTipoExportacao == self::$EXP_NOTA || $idTipoExportacao == self::$EXP_RESULTADO) {
                // Caso de exportação de notas ou resultado
                // 
                // Verificando casos especiais de finalização prematura
                // Buscando etapa relativa para verificação
                if (Util::vazioNulo($idEtapaAval)) {
                    $etapaSel = EtapaSelProc::buscarEtapaAtiva($idChamada);
                } else {
                    $temp = EtapaSelProc::buscarEtapaPorChamada($idChamada, $idEtapaAval);
                    if (count($temp) != 1) {
                        throw new NegocioException("Inconsistência ao validar exportação de dados.");
                    }
                    $etapaSel = $temp[0];
                }
                // é finalização forçada?
                if ($etapaSel->isFinalizacaoForcada(EtapaSelProc::$PENDENTE_RESUL_PARCIAL)) {
                    $dsInfGerada = $idTipoExportacao == self::$EXP_NOTA ? "nota" : "resultado";
                    return array("val" => FALSE, "msg" => "$msgInicialPadrao não foi gerado $dsInfGerada para esta chamada e etapa.");
                }


                // verificando se a classificação está concluída
                //
                // recuperando quantidade de etapas com classificação ok
                $qtEtapasOk = EtapaSelProc::contarEtapaPorSitClassificacao($idChamada, EtapaSelProc::$CLASSIF_CONCLUIDA, $idEtapaAval);

                // caso de etapa específica
                if (!Util::vazioNulo($idEtapaAval)) {
                    if ($qtEtapasOk == 0) {
                        return array("val" => FALSE, "msg" => "$msgInicialPadrao a classificação desta etapa não está concluída para esta chamada.");
                    }
                } else {
                    // caso de notas de todas as etapas
                    if ($qtEtapasOk != EtapaAvalProc::contarEtapaAvalPorProc($idProcesso)) {
                        return array("val" => FALSE, "msg" => "$msgInicialPadrao a classificação final desta chamada não está concluída.");
                    }
                }
            } elseif ($idTipoExportacao != self::$EXP_DADO_GERAL) {
                // Erro: Só restava dado geral e não é dado geral
                throw new NegocioException("Tipo de exportação desconhecido.");
            }

            // Tudo ok. Permitindo exportação
            return array("val" => TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar exportação de dados.", $e);
        }
    }

    public static function validarNumEdital($nrEdital, $anoEdital, $idTipoCargo, $idCurso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_prc_processo
                    where
                    PRC_NR_EDITAL = '$nrEdital'
                    and PRC_ANO_EDITAL = '$anoEdital'
                    and TIC_ID_TIPO_CARGO = '$idTipoCargo'
                    and CUR_ID_CURSO = '$idCurso'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando quantidade e retornando
            $quant = $conexao->getResult("cont", $resp);
            return $quant == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar numeração do Edital.", $e);
        }
    }

    private function validaDadosBD() {
        // Checando data de início
        if (Util::vazioNulo($this->PRC_DT_INICIO)) {
            throw new NegocioException("Data de início não pode ser vazia!");
        }

        // definindo url
        $this->PRC_DS_URL_EDITAL = NGUtil::trataCampoStrParaBD($this->ARQS_getUrlArqEdital());

        $this->PRC_DS_PROCESSO = NGUtil::trataCampoStrParaBD($this->PRC_DS_PROCESSO);

        $this->PRC_DT_INICIO = dt_dataStrParaMysql($this->PRC_DT_INICIO);
    }

#   ----------------------------------- FIM FUNÇÕES DE VALIDAÇÃO --------------------------------------------------   

    public function exportarDadosProcesso($idTipoExportacao, $idChamada, $idEtapaAval) {
        try {

            // validando exportação
            $val = self::validarExportacaoDados($this->PRC_ID_PROCESSO, $idTipoExportacao, $idChamada, $idEtapaAval);
            if (!$val['val']) {
                throw new NegocioException($val['msg']);
            }

            // Tudo Ok, gerando exportação
            // caso de exportação de dados gerais
            if ($idTipoExportacao == self::$EXP_DADO_GERAL) {
                $chamada = ProcessoChamada::buscarChamadaPorId($idChamada, $this->PRC_ID_PROCESSO);
                $stringCSV = InscricaoProcesso::getCSVInscritosProcGeral($chamada);

                $nmArquivo = "insc_{$this->getDsEditalParaArq()}_c{$chamada->getPCH_NR_ORDEM()}.csv";
            } elseif ($idTipoExportacao == self::$EXP_RECURSO) {
                $chamada = ProcessoChamada::buscarChamadaPorId($idChamada, $this->PRC_ID_PROCESSO);
                $etapaAval = Util::vazioNulo($idEtapaAval) ? NULL : EtapaAvalProc::buscarEtapaAvalPorId($idEtapaAval);
                $stringCSV = RecursoResulProc::getCSVRecursosProcesso($chamada, $etapaAval);

                $compNomeArq = $etapaAval == NULL ? "" : $etapaAval->getNomeEtapaArq();
                $nmArquivo = "rec_{$this->getDsEditalParaArq()}_c{$chamada->getPCH_NR_ORDEM()}{$compNomeArq}.csv";
            } elseif ($idTipoExportacao == self::$EXP_NOTA) {
                $chamada = ProcessoChamada::buscarChamadaPorId($idChamada, $this->PRC_ID_PROCESSO);
                $etapaAval = Util::vazioNulo($idEtapaAval) ? NULL : EtapaAvalProc::buscarEtapaAvalPorId($idEtapaAval);
                $stringCSV = InscricaoProcesso::getCSVInscritosProcNotas($chamada, $etapaAval);

                $compNomeArq = $etapaAval == NULL ? "" : $etapaAval->getNomeEtapaArq();
                $nmArquivo = "aval_{$this->getDsEditalParaArq()}_c{$chamada->getPCH_NR_ORDEM()}{$compNomeArq}.csv";
            } elseif ($idTipoExportacao == self::$EXP_RESULTADO) {
                $chamada = ProcessoChamada::buscarChamadaPorId($idChamada, $this->PRC_ID_PROCESSO);
                $etapaAval = Util::vazioNulo($idEtapaAval) ? NULL : EtapaAvalProc::buscarEtapaAvalPorId($idEtapaAval);
                $stringCSV = InscricaoProcesso::getCSVInscritosProcResultado($chamada, $etapaAval);

                $compNomeArq = $etapaAval == NULL ? "" : $etapaAval->getNomeEtapaArq();
                $nmArquivo = "resul_{$this->getDsEditalParaArq()}_c{$chamada->getPCH_NR_ORDEM()}{$compNomeArq}.csv";
            } else {
                throw new NegocioException("Tipo de exportação desconhecido.");
            }

            // enviando arquivo para download
            NGUtil::enviaStrComoArquivoCSV($stringCSV, $nmArquivo);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao executar exportação de dados.", $e);
        }
    }

    public static function contaProcessosPorFiltro($idCurso, $tpFormacao, $idProcesso, $nrEdital, $anoEdital, $idTipoCargo) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                from tb_prc_processo prc
                join tb_tpc_tipo_curso tpc on tpc.TPC_ID_TIPO_CURSO = (select TPC_ID_TIPO_CURSO from tb_cur_curso where CUR_ID_CURSO = prc.CUR_ID_CURSO)";

            //complementando sql de acordo com o filtro
            $where = true;
            $and = false;

            // curso
            if ($idCurso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `CUR_ID_CURSO` = $idCurso ";
            }

            // tipo de formação
            if ($tpFormacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `TPC_ID_TIPO_CURSO` = $tpFormacao ";
            }

            // processo
            if ($idProcesso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `PRC_ID_PROCESSO` = '$idProcesso' ";
            }

            // nr Edital
            if ($nrEdital != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `PRC_NR_EDITAL` = '$nrEdital' ";
            }

            // ano Edital
            if ($anoEdital != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `PRC_ANO_EDITAL` = '$anoEdital' ";
            }

            // idTipo
            if ($idTipoCargo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " prc.`TIC_ID_TIPO_CARGO` = '$idTipoCargo' ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar processos.", $e);
        }
    }

    public static function buscarProcessosPorFiltro($idCurso, $tpFormacao, $idProcesso, $nrEdital, $anoEdital, $idTipoCargo, $inicioDados, $qtdeDados) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Flag última chamada
            $flagUltChamada = FLAG_BD_SIM;

            $sql = "select 
                    prc.PRC_ID_PROCESSO,
                    prc.`TIC_ID_TIPO_CARGO` as TIC_ID_TIPO_CARGO,
                    prc.`CUR_ID_CURSO` as CUR_ID_CURSO,
                    TPC_NM_TIPO_CURSO as tipoCurso,
                    PRC_NR_EDITAL,
                    PRC_ANO_EDITAL,
                    PRC_DS_PROCESSO,
                    PRC_DS_URL_EDITAL,
                    DATE_FORMAT(`PRC_DT_FIM`, '%d/%m/%Y') as PRC_DT_FIM,
                    DATE_FORMAT(`PRC_DT_INICIO`, '%d/%m/%Y') as PRC_DT_INICIO,
                    `TIC_NM_TIPO` as nmTipo,
                    `CUR_NM_CURSO` as nmCurso,
                    `CUR_DS_CURSO` as dsCurso,
                    TIC_URL_BUSCA,
                    CUR_URL_BUSCA,
                    PCH_ID_CHAMADA,
                    PCH_DS_CHAMADA,
                    PCH_CHAMADA_ATIVA
                from
                    tb_prc_processo prc
                        join
                    tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
                        join
                    tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
                        join
                    tb_tpc_tipo_curso tpc ON cur.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
                        left join
                    tb_pch_processo_chamada pch ON prc.PRC_ID_PROCESSO = pch.PRC_ID_PROCESSO
                    and PCH_CHAMADA_ATUAL = '$flagUltChamada'";

            //complementando sql de acordo com o filtro
            $where = true;
            $and = false;

            //curso
            if ($idCurso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " prc.`CUR_ID_CURSO` = $idCurso ";
            }

            // tipo de formação
            if ($tpFormacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " tpc.`TPC_ID_TIPO_CURSO` = $tpFormacao ";
            }

            //processo
            if ($idProcesso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `PRC_ID_PROCESSO` = '$idProcesso' ";
            }

            //nr Edital
            if ($nrEdital != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `PRC_NR_EDITAL` = '$nrEdital' ";
            }

            //ano Edital
            if ($anoEdital != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `PRC_ANO_EDITAL` = '$anoEdital' ";
            }

            //idTipo
            if ($idTipoCargo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " prc.`TIC_ID_TIPO_CARGO` = '$idTipoCargo' ";
            }

            //finalização: caso de ordenação
            $sql .= " order by PRC_ANO_EDITAL desc , PRC_NR_EDITAL desc, TIC_NM_TIPO, CUR_NM_CURSO";

            //questão de limite
            if ($qtdeDados != NULL) {
                $inicio = $inicioDados != NULL ? $inicioDados : 0;
                $sql .= " limit $inicio, $qtdeDados ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $processoTemp = new Processo($dados['PRC_ID_PROCESSO'], $dados['TIC_ID_TIPO_CARGO'], $dados['CUR_ID_CURSO'], $dados['PRC_NR_EDITAL'], $dados['PRC_ANO_EDITAL'], $dados['PRC_DS_URL_EDITAL'], $dados['PRC_DS_PROCESSO'], $dados['PRC_DT_INICIO'], $dados['PRC_DT_FIM']);

                //preenchendo campos herdados
                $processoTemp->TIC_NM_TIPO_CARGO = $dados['nmTipo'];
                $processoTemp->CUR_NM_CURSO = $dados['nmCurso'];
                $processoTemp->CUR_DS_CURSO = $dados['dsCurso'];
                $processoTemp->TPC_NM_TIPO_CURSO = $dados['tipoCurso'];
                $processoTemp->TIC_URL_BUSCA = $dados['TIC_URL_BUSCA'];
                $processoTemp->CUR_URL_BUSCA = $dados['CUR_URL_BUSCA'];
                $processoTemp->PCH_ID_ULT_CHAMADA = $dados['PCH_ID_CHAMADA'];
                $processoTemp->PCH_DS_ULT_CHAMADA = $dados['PCH_DS_CHAMADA'];
                $processoTemp->PCH_CHAMADA_ATIVA = $dados['PCH_CHAMADA_ATIVA'];

                //adicionando no vetor
                $vetRetorno[$i] = $processoTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar processos.", $e);
        }
    }

    public function reabrirEdital($reabrirChamada) {
        try {
            // definimdo array de comandos
            $arrayCmds = array();

            // recuperando SQL
            $dtFimEdital = dt_somarData(dt_getDataEmStr("d/m/Y"), Processo::$TEMPO_PADRAO_FINALIZACAO);
            self::addSqlFinalizacaoEdital($arrayCmds, $this->PRC_ID_PROCESSO, NULL, $dtFimEdital);

            // tem que reabrir última chamada?
            if ($reabrirChamada) {
                $arrayCmds [] = ProcessoChamada::getSqlFinalizacaoChamada($this->PCH_ID_ULT_CHAMADA, $dtFimEdital);
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao reabrir processo.", $e);
        }
    }

    public function alterarFimEdital($dtFinalizacao, $finalizarAgora) {
        try {

            // recuperando SQL
            $arraySql = array();
            $dtFimEdital = $finalizarAgora ? dt_getDataEmStr("d/m/Y") : $dtFinalizacao;
            self::addSqlFinalizacaoEdital($arraySql, $this->PRC_ID_PROCESSO, NULL, $dtFimEdital);

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();
            $conexao->execTransacaoArray($arraySql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao alterar finalização do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @return \Processo
     * @throws NegocioException
     */
    public static function buscarProcessoPorId($idProcesso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Flags
            $flagUltChamada = FLAG_BD_SIM;

            $sql = "select 
                    prc.PRC_ID_PROCESSO,
                    prc.`TIC_ID_TIPO_CARGO` as TIC_ID_TIPO_CARGO,
                    prc.`CUR_ID_CURSO` as CUR_ID_CURSO,
                    TPC_NM_TIPO_CURSO as tipoCurso,
                    PRC_NR_EDITAL,
                    PCH_ID_CHAMADA,
                    PCH_DS_CHAMADA,
                    PRC_ANO_EDITAL,
                    PRC_DS_PROCESSO,
                    PRC_DS_URL_EDITAL,
                    PCH_INSCRICAO_MULTIPLA,
                    PCH_NR_MAX_OPCAO_POLO,
                    DATE_FORMAT(`PRC_DT_FIM`, '%d/%m/%Y') as PRC_DT_FIM,
                    DATE_FORMAT(`PRC_DT_INICIO`, '%d/%m/%Y') as PRC_DT_INICIO,
                    DATE_FORMAT(`PCH_DT_FECHAMENTO`, '%d/%m/%Y') as PCH_DT_FECHAMENTO,
                    DATE_FORMAT(`PCH_DT_ABERTURA`, '%d/%m/%Y') as PCH_DT_ABERTURA,
                    `TIC_NM_TIPO` as nmTipo,
                    `CUR_NM_CURSO` as nmCurso,
                    `CUR_DS_CURSO` as dsCurso,
                    PCH_ADMITE_AREA, 
                    PCH_ADMITE_RESERVA_VAGA, 
                    PCH_CHAMADA_ATIVA,
                    DATE_FORMAT(`PCH_DT_REG_RESUL_FINAL`,'%d/%m/%Y %T') as PCH_DT_REG_RESUL_FINAL,
                    DATE_FORMAT(`PCH_DT_FINALIZACAO`,'%d/%m/%Y') as PCH_DT_FINALIZACAO,
                    TIC_URL_BUSCA,
                    CUR_URL_BUSCA
                from
                    tb_prc_processo prc
                        join
                    tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
                        join
                    tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
                        join
                    tb_tpc_tipo_curso tpc ON cur.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
                        left join
                    tb_pch_processo_chamada pch ON prc.PRC_ID_PROCESSO = pch.PRC_ID_PROCESSO
                    WHERE prc.`PRC_ID_PROCESSO` = '$idProcesso'
                    and ((select 
                            count(*)
                        from
                            tb_pch_processo_chamada
                        where
                            PRC_ID_PROCESSO = prc.PRC_ID_PROCESSO) = 0
                        or PCH_CHAMADA_ATUAL = '$flagUltChamada')";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                throw new NegocioException("Processo não encontrado.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $processoRet = new Processo($dados['PRC_ID_PROCESSO'], $dados['TIC_ID_TIPO_CARGO'], $dados['CUR_ID_CURSO'], $dados['PRC_NR_EDITAL'], $dados['PRC_ANO_EDITAL'], $dados['PRC_DS_URL_EDITAL'], $dados['PRC_DS_PROCESSO'], $dados['PRC_DT_INICIO'], $dados['PRC_DT_FIM']);
            //preenchendo campos herdados
            $processoRet->TIC_NM_TIPO_CARGO = $dados['nmTipo'];
            $processoRet->CUR_NM_CURSO = $dados['nmCurso'];
            $processoRet->CUR_DS_CURSO = $dados['dsCurso'];
            $processoRet->TPC_NM_TIPO_CURSO = $dados['tipoCurso'];
            $processoRet->PCH_DT_FECHAMENTO = $dados['PCH_DT_FECHAMENTO'];
            $processoRet->PCH_DT_ABERTURA = $dados['PCH_DT_ABERTURA'];
            $processoRet->PCH_ID_ULT_CHAMADA = $dados['PCH_ID_CHAMADA'];
            $processoRet->PCH_DS_ULT_CHAMADA = $dados['PCH_DS_CHAMADA'];
            $processoRet->PCH_CHAMADA_ATIVA = $dados['PCH_CHAMADA_ATIVA'];
            $processoRet->PCH_NR_MAX_OPCAO_POLO = $dados['PCH_NR_MAX_OPCAO_POLO'];
            $processoRet->PCH_ADMITE_AREA = $dados['PCH_ADMITE_AREA'];
            $processoRet->PCH_ADMITE_RESERVA_VAGA = $dados['PCH_ADMITE_RESERVA_VAGA'];
            $processoRet->PCH_INSCRICAO_MULTIPLA = $dados['PCH_INSCRICAO_MULTIPLA'];
            $processoRet->PCH_DT_REG_RESUL_FINAL = $dados['PCH_DT_REG_RESUL_FINAL'];
            $processoRet->PCH_DT_FINALIZACAO = $dados['PCH_DT_FINALIZACAO'];
            $processoRet->TIC_URL_BUSCA = $dados['TIC_URL_BUSCA'];
            $processoRet->CUR_URL_BUSCA = $dados['CUR_URL_BUSCA'];

            return $processoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar processo.", $e);
        }
    }

    /**
     * 
     * @param string $nmCurso Url amigável do curso
     * @param string $nmTipoCargo Url amigável d tipo de cargo
     * @param string $id número-ano do Edital
     * 
     * @return int
     * @throws NegocioException
     */
    public static function buscarIdProcessoPorUrlAmigavel($nmCurso, $nmTipoCargo, $id) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Flags
            $flagUltChamada = FLAG_BD_SIM;
            $flagChamadaAtiva = FLAG_BD_SIM;

            // destrinchando id
            $num = explode("-", $id);
            $numero = $num[0];
            $ano = $num[1];

            $sql = "select 
                    prc.PRC_ID_PROCESSO as id
                    from
                    tb_prc_processo prc
                        join
                    tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
                        join
                    tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
                        join
                    tb_pch_processo_chamada pch ON prc.PRC_ID_PROCESSO = pch.PRC_ID_PROCESSO and PCH_CHAMADA_ATUAL = '$flagUltChamada' and PCH_CHAMADA_ATIVA = '$flagChamadaAtiva'
                    WHERE CUR_URL_BUSCA = '$nmCurso'
                    and TIC_URL_BUSCA = '$nmTipoCargo'
                    and PRC_NR_EDITAL = '$numero'
                    and PRC_ANO_EDITAL = '$ano'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return NULL;
            }

            return ConexaoMysql::getResult("id", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar ID do processo por url amigável.", $e);
        }
    }

    public function criarProcesso($arqEditalTmp) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // nao validou numeraçao do edital
            if (!self::validarNumEdital($this->PRC_NR_EDITAL, $this->PRC_ANO_EDITAL, $this->TIC_ID_TIPO_CARGO, $this->CUR_ID_CURSO)) {
                throw new NegocioException("Edital já cadastrado no sistema!");
            }

            // array de comandos
            $arrayCmdsDep = array();

            // processando atualização do arquivo no servidor
            AcompProcChamada::processaAtualizacaoEdital($this->PRC_ID_PROCESSO, $this->PCH_ID_ULT_CHAMADA, $this->ARQS_getUrlArqEdital(), $this->getDiretorioUploadEdital() . $this->ARQS_nomeArquivoEdital(), $arqEditalTmp, $arrayCmdsDep);

            // validando dados para o BD
            $this->validaDadosBD();

            // montando SQL
            $sql = "insert into tb_prc_processo
                (`TIC_ID_TIPO_CARGO`,`CUR_ID_CURSO`, PRC_NR_EDITAL, PRC_ANO_EDITAL, `PRC_DS_URL_EDITAL`,`PRC_DT_INICIO`,
                `PRC_DS_PROCESSO`)
                 values('$this->TIC_ID_TIPO_CARGO', '$this->CUR_ID_CURSO', '$this->PRC_NR_EDITAL', '$this->PRC_ANO_EDITAL',
                 $this->PRC_DS_URL_EDITAL, $this->PRC_DT_INICIO, $this->PRC_DS_PROCESSO)";

            // recuperando sql de criar etapa padrão
            $arrayCmdsDep [] = EtapaAvalProc::_getSqlCriarEtapaAval(ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE);

            // persistindo no bd e recuperando id
            $this->PRC_ID_PROCESSO = $conexao->execTransacaoDependente($sql, $arrayCmdsDep);
        } catch (NegocioException $n) {
            // excluindo arquivo
            NGUtil::arq_excluirArquivoServidor($this->getDiretorioUploadEdital() . $this->ARQS_nomeArquivoEdital());
            throw $n;
        } catch (Exception $e) {
            NGUtil::arq_excluirArquivoServidor($this->getDiretorioUploadEdital() . $this->ARQS_nomeArquivoEdital());
            throw new NegocioException("Erro ao criar Edital.", $e);
        }
    }

    public function editarDadosAddProcesso($arqEditalTmp = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // validando ediçao
            if (!$this->permiteEdicao(TRUE)) {
                throw new NegocioException("Os dados adicionais deste edital não podem ser editados.");
            }

            // validando data inicial
            if (!$this->validaDataInicio()) {
                throw new NegocioException('A data de início deve ser menor ou igual a data de início das inscrições da primeira chamada do processo.');
            }

            // array de comandos
            $arrayCmds = array();

            // tem arquivo?
            if ($arqEditalTmp != NULL) {
                // processando atualização do arquivo no servidor
                AcompProcChamada::processaAtualizacaoEdital($this->PRC_ID_PROCESSO, $this->PCH_ID_ULT_CHAMADA, $this->ARQS_getUrlArqEdital(), $this->getDiretorioUploadEdital() . $this->ARQS_nomeArquivoEdital(), $arqEditalTmp, $arrayCmds);
            }

            // validando dados para o BD
            $this->validaDadosBD();

            //montando sql de edição
            $arrayCmds [] = "update tb_prc_processo
                            set `PRC_DT_INICIO` = $this->PRC_DT_INICIO
                            , `PRC_DS_PROCESSO` = $this->PRC_DS_PROCESSO
                            where `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'";

            // persistindo no BD
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao editar dados adicionais do processo.", $e);
        }
    }

    public function excluirProcesso() {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // validando exclusão
            if (!$this->permiteExclusao()) {
                throw new NegocioException("Não é possível excluir este edital.");
            }

            // array de comandos
            $arrayCmds = array();

            // recuperando sqls de remoção de dependências
            AcompProcChamada::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            ItemAvalProc::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            CategoriaAvalProc::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            MacroConfProc::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            EtapaSelProc::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            EtapaAvalProc::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            ItemAnexoProc::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            GrupoAnexoProc::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            Noticia::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            ReservaVagaChamada::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            UsuarioRastreio::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            ProcessoChamada::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);
            HistoricoInscExc::addSqlRemoverPorProcesso($this->PRC_ID_PROCESSO, $arrayCmds);

            //montando sql de exclusão
            $arrayCmds [] = "delete from tb_prc_processo where `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'";

//            NGUtil::imprimeVetorDepuracao($arrayCmds);
//            exit;
            // persistindo no BD
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir edital.", $e);
        }
    }

    public static function buscarUltProcAbtPorCurso($idCurso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Flags
            $flagUltChamada = FLAG_BD_SIM;
            $flagChamadaAtiva = FLAG_BD_SIM;

            $sql = "select 
                    prc.PRC_ID_PROCESSO,
                    prc.`TIC_ID_TIPO_CARGO` as TIC_ID_TIPO_CARGO,
                    prc.`CUR_ID_CURSO` as CUR_ID_CURSO,
                    TPC_NM_TIPO_CURSO as tipoCurso,
                    PRC_NR_EDITAL,
                    PCH_ID_CHAMADA,
                    PCH_DS_CHAMADA,
                    PRC_ANO_EDITAL,
                    PRC_DS_PROCESSO,
                    PRC_DS_URL_EDITAL,
                    PCH_INSCRICAO_MULTIPLA,
                    PCH_NR_MAX_OPCAO_POLO,
                    DATE_FORMAT(`PRC_DT_FIM`, '%d/%m/%Y') as PRC_DT_FIM,
                    DATE_FORMAT(`PRC_DT_INICIO`, '%d/%m/%Y') as PRC_DT_INICIO,
                    DATE_FORMAT(`PCH_DT_FECHAMENTO`, '%d/%m/%Y') as PCH_DT_FECHAMENTO,
                    DATE_FORMAT(`PCH_DT_ABERTURA`, '%d/%m/%Y') as PCH_DT_ABERTURA,
                    `TIC_NM_TIPO` as nmTipo,
                    `CUR_NM_CURSO` as nmCurso,
                    `CUR_DS_CURSO` as dsCurso,
                    PCH_ADMITE_AREA,
                    PCH_CHAMADA_ATIVA,
                    DATE_FORMAT(`PCH_DT_REG_RESUL_FINAL`,'%d/%m/%Y %T') as PCH_DT_REG_RESUL_FINAL,
                    DATE_FORMAT(`PCH_DT_FINALIZACAO`,'%d/%m/%Y') as PCH_DT_FINALIZACAO,
                    TIC_URL_BUSCA,
                    CUR_URL_BUSCA
                from
                    tb_prc_processo prc
                        join
                    tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
                        join
                    tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
                        join
                    tb_tpc_tipo_curso tpc ON cur.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
                        join
                    tb_pch_processo_chamada pch ON pch.PRC_ID_PROCESSO = prc.PRC_ID_PROCESSO and PCH_CHAMADA_ATUAL = '$flagUltChamada' and PCH_CHAMADA_ATIVA = '$flagChamadaAtiva'
                    WHERE prc.`CUR_ID_CURSO` = '$idCurso'
                    and PRC_DT_FIM IS NULL
                    order by PRC_ANO_EDITAL desc, PRC_NR_EDITAL desc limit 0, 1";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                throw new NegocioException("Processo não encontrado.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $processoRet = new Processo($dados['PRC_ID_PROCESSO'], $dados['TIC_ID_TIPO_CARGO'], $dados['CUR_ID_CURSO'], $dados['PRC_NR_EDITAL'], $dados['PRC_ANO_EDITAL'], $dados['PRC_DS_URL_EDITAL'], $dados['PRC_DS_PROCESSO'], $dados['PRC_DT_INICIO'], $dados['PRC_DT_FIM']);
            //preenchendo campos herdados
            $processoRet->TIC_NM_TIPO_CARGO = $dados['nmTipo'];
            $processoRet->CUR_NM_CURSO = $dados['nmCurso'];
            $processoRet->CUR_DS_CURSO = $dados['dsCurso'];
            $processoRet->TPC_NM_TIPO_CURSO = $dados['tipoCurso'];
            $processoRet->PCH_DT_FECHAMENTO = $dados['PCH_DT_FECHAMENTO'];
            $processoRet->PCH_DT_ABERTURA = $dados['PCH_DT_ABERTURA'];
            $processoRet->PCH_ID_ULT_CHAMADA = $dados['PCH_ID_CHAMADA'];
            $processoRet->PCH_DS_ULT_CHAMADA = $dados['PCH_DS_CHAMADA'];
            $processoRet->PCH_NR_MAX_OPCAO_POLO = $dados['PCH_NR_MAX_OPCAO_POLO'];
            $processoRet->PCH_ADMITE_AREA = $dados['PCH_ADMITE_AREA'];
            $processoRet->PCH_INSCRICAO_MULTIPLA = $dados['PCH_INSCRICAO_MULTIPLA'];
            $processoRet->PCH_CHAMADA_ATIVA = $dados['PCH_CHAMADA_ATIVA'];
            $processoRet->PCH_DT_REG_RESUL_FINAL = $dados['PCH_DT_REG_RESUL_FINAL'];
            $processoRet->PCH_DT_FINALIZACAO = $dados['PCH_DT_FINALIZACAO'];
            $processoRet->TIC_URL_BUSCA = $dados['TIC_URL_BUSCA'];
            $processoRet->CUR_URL_BUSCA = $dados['CUR_URL_BUSCA'];

            return $processoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar processo.", $e);
        }
    }

    public static function contaProcessoAbertoPorDep($idDepartamento) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //verificando se existem processos abertos 
            $sql = "select 
                        count(*) as conta
                    from
                        tb_prc_processo prc
                            join
                        tb_cur_curso cur ON cur.CUR_ID_CURSO = prc.CUR_ID_CURSO
                    where
                        PRC_DT_FIM is NULL
                            and cur.DEP_ID_DEPARTAMENTO = '$idDepartamento'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            return ConexaoMysql::getResult("conta", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar processos abertos do departamento.", $e);
        }
    }

    /**
     * Esta função busca os processos a serem exibidos para os candidatos
     * 
     * ATENÇÃO: ESTA FUNÇÃO É DIFERENTE DAS DEMAIS FUNÇÕES DE BUSCA!
     * 
     * Ela retorna uma estrutura de matriz indexada pelo tipo de apresentação, onde cada índice contém uma lista de 
     * processos que devem ser listados naquela categoria de apresentação.
     * 
     * @param int $idTipo
     * @param char $tpFormacao
     * @param int $idCurso
     * @param int $anoEdital
     * @param int $nrEdital
     * @param int $inicioDados
     * @param int $qtdeDados
     * @param array $arrayTpApresentacao Tipos de apresentação desejados. Parâmetro opcional.
     * @return \Processo
     * @throws NegocioException
     */
    public static function buscarProcessosApresentacao($idTipo, $tpFormacao, $idCurso, $anoEdital, $nrEdital, $inicioDados, $qtdeDados, $arrayTpApresentacao = NULL) {
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Flags
            $flagUltChamada = FLAG_BD_SIM;
            $flagChamadaAtiva = FLAG_BD_SIM;

            $sql = "select 
                    prc.PRC_ID_PROCESSO,
                    prc.`TIC_ID_TIPO_CARGO` as TIC_ID_TIPO_CARGO,
                    prc.`CUR_ID_CURSO` as CUR_ID_CURSO,
                    TPC_NM_TIPO_CURSO as tipoCurso,
                    PRC_NR_EDITAL,
                    PRC_ANO_EDITAL,
                    PCH_ID_CHAMADA,
                    PCH_DS_CHAMADA,
                    PCH_INSCRICAO_MULTIPLA,
                    PCH_NR_MAX_OPCAO_POLO,
                    DATE_FORMAT(`PRC_DT_INICIO`, '%d/%m/%Y') as PRC_DT_INICIO_S,
                    DATE_FORMAT(`PRC_DT_FIM`, '%d/%m/%Y') as PRC_DT_FIM_S,
                    DATE_FORMAT(`PCH_DT_FECHAMENTO`, '%d/%m/%Y') as PCH_DT_FECHAMENTO_S,
                    DATE_FORMAT(`PCH_DT_ABERTURA`, '%d/%m/%Y') as PCH_DT_ABERTURA_S,
                    `TIC_NM_TIPO` as nmTipo,
                    `CUR_NM_CURSO` as nmCurso,
                    `CUR_DS_CURSO` as dsCurso,
                    PCH_ADMITE_AREA,
                    PCH_CHAMADA_ATIVA,
                    DATE_FORMAT(`PCH_DT_REG_RESUL_FINAL`,'%d/%m/%Y %T') as PCH_DT_REG_RESUL_FINAL_S,
                    DATE_FORMAT(`PCH_DT_FINALIZACAO`,'%d/%m/%Y') as PCH_DT_FINALIZACAO_S,
                    TIC_URL_BUSCA,
                    CUR_URL_BUSCA
                from
                    tb_prc_processo prc
                        join
                    tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
                        join
                    tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
                        join
                    tb_tpc_tipo_curso tpc ON cur.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
                        join
                    tb_pch_processo_chamada pch ON pch.PRC_ID_PROCESSO = prc.PRC_ID_PROCESSO
                        and PCH_CHAMADA_ATUAL = '$flagUltChamada' and PCH_CHAMADA_ATIVA = '$flagChamadaAtiva'
                where
                    `PRC_DT_INICIO` <= curdate()";

            // caso tipo de apresentação
            if ($arrayTpApresentacao != NULL) {
                $sql .= " and (";
                $or = FALSE;
                foreach ($arrayTpApresentacao as $tipo) {
                    $sql .= ($or ? " or " : " ") . self::getCompSqlBuscaTpApresentacao($tipo) . " ";
                    $or = TRUE;
                }
                $sql .= ") ";
            }

            //idTipos
            if ($idTipo != NULL) {
                $sql .= " and prc.`TIC_ID_TIPO_CARGO` = '$idTipo' ";
            }

            //idCurso
            if ($idCurso != NULL) {
                $sql .= " and cur.`CUR_ID_CURSO` = '$idCurso' ";
            }

            //tpFormacao
            if ($tpFormacao != NULL) {
                $sql .= " and cur.`TPC_ID_TIPO_CURSO` = '$tpFormacao' ";
            }

            // ano edital
            if ($anoEdital != NULL) {
                $sql .= " and prc.`PRC_ANO_EDITAL` = '$anoEdital' ";
            }

            // número edital
            if ($nrEdital != NULL) {
                $sql .= " and prc.`PRC_NR_EDITAL` = '$nrEdital' ";
            }

            //finalização
            $sql .= " order by " . self::getCompSqlOrdenacaoTpApresentacao() . ", PRC_ANO_EDITAL desc, PRC_NR_EDITAL desc, TIC_NM_TIPO, CUR_NM_CURSO";

            //questão de limite
            if ($qtdeDados != NULL) {
                $inicio = $inicioDados != NULL ? $inicioDados : 0;
                $sql .= " limit $inicio, $qtdeDados ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }

            $matRetorno = array();

            //realizando iteração para recuperar as titulações
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);
                $processoTemp = new Processo($dados['PRC_ID_PROCESSO'], $dados['TIC_ID_TIPO_CARGO'], $dados['CUR_ID_CURSO'], $dados['PRC_NR_EDITAL'], $dados['PRC_ANO_EDITAL'], NULL, NULL, $dados['PRC_DT_INICIO_S'], $dados['PRC_DT_FIM_S']);
                //preenchendo campos herdados
                $processoTemp->TIC_NM_TIPO_CARGO = $dados['nmTipo'];
                $processoTemp->CUR_NM_CURSO = $dados['nmCurso'];
                $processoTemp->CUR_DS_CURSO = $dados['dsCurso'];
                $processoTemp->TPC_NM_TIPO_CURSO = $dados['tipoCurso'];
                $processoTemp->PCH_DT_FECHAMENTO = $dados['PCH_DT_FECHAMENTO_S'];
                $processoTemp->PCH_DT_ABERTURA = $dados['PCH_DT_ABERTURA_S'];
                $processoTemp->PCH_ID_ULT_CHAMADA = $dados['PCH_ID_CHAMADA'];
                $processoTemp->PCH_DS_ULT_CHAMADA = $dados['PCH_DS_CHAMADA'];
                $processoTemp->PCH_NR_MAX_OPCAO_POLO = $dados['PCH_NR_MAX_OPCAO_POLO'];
                $processoTemp->PCH_ADMITE_AREA = $dados['PCH_ADMITE_AREA'];
                $processoTemp->PCH_INSCRICAO_MULTIPLA = $dados['PCH_INSCRICAO_MULTIPLA'];
                $processoTemp->PCH_CHAMADA_ATIVA = $dados['PCH_CHAMADA_ATIVA'];
                $processoTemp->PCH_DT_REG_RESUL_FINAL = $dados['PCH_DT_REG_RESUL_FINAL_S'];
                $processoTemp->PCH_DT_FINALIZACAO = $dados['PCH_DT_FINALIZACAO_S'];
                $processoTemp->TIC_URL_BUSCA = $dados['TIC_URL_BUSCA'];
                $processoTemp->CUR_URL_BUSCA = $dados['CUR_URL_BUSCA'];

                // setando apresentação
                $processoTemp->setTpApresentacao();

                //adicionando na matriz
                if (!isset($matRetorno[$processoTemp->faseApresentacao])) {
                    $matRetorno[$processoTemp->faseApresentacao] = array($processoTemp);
                } else {
                    $matRetorno[$processoTemp->faseApresentacao][] = $processoTemp;
                }
            }

            return $matRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar processos para apresentação.", $e);
        }
    }

    private function setTpApresentacao() {
        $dtIniInsc = dt_getTimestampDtBR($this->PCH_DT_ABERTURA);
        $dtFimInsc = dt_getTimestampDtBR($this->PCH_DT_FECHAMENTO);
        $hoje = dt_getTimestampDtUS();

        if (dt_dataMaior($dtIniInsc, $hoje)) {
            // caso de novo edital
            $this->faseApresentacao = self::$APRESENTACAO_NOVO;
        } elseif (dt_dataPertenceIntervalo($hoje, $dtIniInsc, $dtFimInsc)) {
            // período de inscrição
            $this->faseApresentacao = self::$APRESENTACAO_INSCRICAO;
        } elseif (Util::vazioNulo($this->PRC_DT_FIM)) {
            // em andamento
            $this->faseApresentacao = self::$APRESENTACAO_ANDAMENTO;
        } else {
            // finalizado
            $this->faseApresentacao = self::$APRESENTACAO_FINALIZADO;
        }
    }

    private static function getCompSqlBuscaTpApresentacao($tpApresentacao) {
        if ($tpApresentacao == self::$APRESENTACAO_NOVO) {
            return self::getSqlRestricaoNovo();
        } elseif ($tpApresentacao == self::$APRESENTACAO_INSCRICAO) {
            return self::getSqlRestricaoInscricao();
        } elseif ($tpApresentacao == self::$APRESENTACAO_ANDAMENTO) {
            return self::getSqlRestricaoAndamento();
        } elseif ($tpApresentacao == self::$APRESENTACAO_FINALIZADO) {
            return self::getSqlRestricaoFinalizado();
        } else {
            throw new NegocioException("Tipo de apresentação desconhecido!");
        }
    }

    private static function getCompSqlOrdenacaoTpApresentacao() {
        return self::getSqlRestricaoNovo() . " desc, " . self::getSqlRestricaoInscricao() . " desc, " . self::getSqlRestricaoAndamento() . " desc, " . self::getSqlRestricaoFinalizado() . " desc";
    }

    private static function getSqlRestricaoNovo() {
        return "(PCH_DT_ABERTURA > curdate())";
    }

    private static function getSqlRestricaoInscricao() {
        return "(curdate() >= PCH_DT_ABERTURA and curdate() <= PCH_DT_FECHAMENTO)";
    }

    private static function getSqlRestricaoAndamento() {
        return "((PCH_DT_ABERTURA < curdate() and curdate() > PCH_DT_FECHAMENTO) and (PRC_DT_FIM IS NULL or PRC_DT_FIM > curdate()))";
    }

    private static function getSqlRestricaoFinalizado() {
        return "(PRC_DT_FIM IS NOT NULL and PRC_DT_FIM <= curdate())";
    }

    public function getFaseApresentacao() {
        if ($this->faseApresentacao == NULL) {
            throw new NegocioException("Fase de apresentação não carregada!");
        }
        return $this->faseApresentacao;
    }

    public static function getMatrizTpApresentacaoOrd() {
        return array(self::$APRESENTACAO_NOVO => "Novos Editais", self::$APRESENTACAO_INSCRICAO => "Inscrições Abertas", self::$APRESENTACAO_ANDAMENTO => "Em Andamento", self::$APRESENTACAO_FINALIZADO => "Editais Finalizados");
    }

    public static function contaProcessosApresentacao($idTipo, $tpFormacao, $idCurso, $anoEdital, $nrEdital) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Flags
            $flagUltChamada = FLAG_BD_SIM;
            $flagChamadaAtiva = FLAG_BD_SIM;

            $sql = "select count(*) as cont
                from
                    tb_prc_processo prc
                        join
                    tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
                        join
                    tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
                        join
                    tb_tpc_tipo_curso tpc ON cur.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
                        join
                    tb_pch_processo_chamada pch ON pch.PRC_ID_PROCESSO = prc.PRC_ID_PROCESSO and PCH_CHAMADA_ATUAL = '$flagUltChamada' and PCH_CHAMADA_ATIVA = '$flagChamadaAtiva'
                where
                    `PRC_DT_INICIO` <= curdate()";

            //idTipo
            if ($idTipo != NULL) {
                $sql .= " and prc.`TIC_ID_TIPO_CARGO` = '$idTipo' ";
            }

            //idCurso
            if ($idCurso != NULL) {
                $sql .= " and cur.`CUR_ID_CURSO` = '$idCurso' ";
            }

            //tpFormacao
            if ($tpFormacao != NULL) {
                $sql .= " and cur.`TPC_ID_TIPO_CURSO` = '$tpFormacao' ";
            }

            // ano edital
            if ($anoEdital != NULL) {
                $sql .= " and prc.`PRC_ANO_EDITAL` = '$anoEdital' ";
            }

            // número edital
            if ($nrEdital != NULL) {
                $sql .= " and prc.`PRC_NR_EDITAL` = '$nrEdital' ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar processos.", $e);
        }
    }

    public function getUrlAmigavel($completa = TRUE) {
        global $CFG;
        if ($completa) {
            return "$CFG->rwww/editais/$this->CUR_URL_BUSCA/$this->TIC_URL_BUSCA/" . $this->getNumeracaoEdital("-");
        } else {
            return "editais/$this->CUR_URL_BUSCA/$this->TIC_URL_BUSCA/" . $this->getNumeracaoEdital("-");
        }
    }

    public static function buscarProcessoAbtPorCurso($idCurso) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select CONCAT(LPAD(CAST(PRC_NR_EDITAL as CHAR (3)),3,'0'),
                    '/',
                    CAST(PRC_ANO_EDITAL as CHAR (4))) as PRC_NR_ANO_EDITAL
                    , PRC_ID_PROCESSO
                    from tb_prc_processo
                    where PRC_DT_FIM IS NULL and `CUR_ID_CURSO` = '$idCurso'
                    order by PRC_ANO_EDITAL desc, PRC_NR_EDITAL desc";

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
                $chave = $dados['PRC_ID_PROCESSO'];
                $valor = $dados['PRC_NR_ANO_EDITAL'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar processos do curso.", $e);
        }
    }

    /**
     * Retorna a quantidade de processos para o curso
     * @param int $idCurso
     * @return int 
     */
    public static function contaProcessoPorCurso($idCurso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //verificando se existem processos abertos para o curso
            $sql = "select count(*) as cont 
                    from tb_prc_processo
                    where CUR_ID_CURSO = '$idCurso'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            $ret = ConexaoMysql::getResult("cont", $resp);
            return $ret;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar processos do curso.", $e);
        }
    }

    /**
     * Retorna a quantidade de processos abertos para o curso
     * @param int $idCurso
     * @return int 
     */
    public static function contaProcessoAbertoPorCurso($idCurso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //verificando se existem processos abertos para o curso
            $sql = "select count(*) as cont 
                    from tb_prc_processo
                    where CUR_ID_CURSO = '$idCurso'
                    and PRC_DT_FIM is NULL";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            $ret = ConexaoMysql::getResult("cont", $resp);
            return $ret;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar processos abertos do curso.", $e);
        }
    }

    public static function addSqlFinalizacaoEdital(&$arrayCmds, $idProcesso, $idChamada, $dtFim = NULL, $limparData = FALSE) {
        if ($limparData) {
            $dtFimProc = "NULL";
        } else {
            $dtFimProc = $dtFim == NULL ? dt_dataStrParaMysql(dt_getDataEmStr("d/m/Y")) : dt_dataStrParaMysql($dtFim);
        }

        $arrayCmds [] = "update tb_prc_processo set PRC_DT_FIM = $dtFimProc where PRC_ID_PROCESSO = '$idProcesso'";

        if (!$limparData && $idChamada != NULL) {
            // Finalizando notícias antigas do edital
            $arrayCmds [] = Noticia::getSqlAjustaValidade($idProcesso, $idChamada, $dtFim);
        }

        // removendo possíveis rastreios
        $arrayCmds [] = UsuarioRastreio::getSqlRemoveRastreioInscEdital($idProcesso);
    }

    public function getTextoInicialPadraoNotAltCalendario($dsChamada) {
        return "A Coordenação do $this->CUR_DS_CURSO, vem, por meio desta notificação, alterar o calendário da $dsChamada do presente edital, de acordo com as informações a seguir:";
    }

    public function getNumeracaoEdital($separador = "/") {
        return "{$this->getPRC_NR_EDITAL()}{$separador}$this->PRC_ANO_EDITAL";
    }

    public function getHTMLDsEditalCompleta() {
        return "<b>Edital:</b> {$this->getNumeracaoEdital()} <separador class='barra'></separador> <b>Atribuição:</b> {$this->TIC_NM_TIPO_CARGO} <separador class='barra'></separador> <b>Curso:</b> {$this->CUR_NM_CURSO}";
    }

    public function getHTMLLinkFluxo() {
        global $CFG;
        return "<b>Fluxo:</b> <a href='$CFG->rwww/visao/processo/fluxoProcesso.php?idProcesso=$this->PRC_ID_PROCESSO' target='_blank'><i class='fa fa-external-link'></i></a>";
    }

    public function getDsEditalCompleta() {
        return "{$this->getNumeracaoEdital()} | {$this->TIC_NM_TIPO_CARGO} | {$this->CUR_NM_CURSO}";
    }

    public function getDsEditalParaPDF() {
        return "Edital {$this->getNumeracaoEdital()} | {$this->TIC_NM_TIPO_CARGO} | Curso: {$this->CUR_NM_CURSO}";
    }

    public function getDsEditalParaArq() {
        $tmp = self::$TRADUCAO_TIPO_SIGLA_ARQ[$this->TIC_ID_TIPO_CARGO];
        return "ed{$this->getNumeracaoEdital("-")}_{$tmp}_{$this->CUR_ID_CURSO}";
    }

    public function getDsPeriodoInscricao() {
        return "$this->PCH_DT_ABERTURA a $this->PCH_DT_FECHAMENTO";
    }

    public static function getMsgSemPolo() {
        return "<i>Não aplicado a este tipo de Edital</i>";
    }

    public static function getMsgPoloNaoConfigurado() {
        return "<i>Ainda não configurado></i>";
    }

    /* GET FIELDS FROM TABLE */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getTIC_ID_TIPO_CARGO() {
        return $this->TIC_ID_TIPO_CARGO;
    }

    /* End of get TIC_ID_TIPO_CARGO */

    function getCUR_ID_CURSO() {
        return $this->CUR_ID_CURSO;
    }

    /* End of get CUR_ID_CURSO */

    function getPRC_NR_EDITAL() {
        return str_pad($this->PRC_NR_EDITAL, 3, "0", STR_PAD_LEFT);
    }

    /* End of get PRC_NR_EDITAL */

    function getPRC_ANO_EDITAL() {
        return $this->PRC_ANO_EDITAL;
    }

    /* End of get PRC_ANO_EDITAL */

    function getPRC_DS_PROCESSO() {
        return $this->PRC_DS_PROCESSO;
    }

    /* End of get PRC_DS_PROCESSO */

    function getPRC_DT_INICIO() {
        return $this->PRC_DT_INICIO;
    }

    /* End of get PRC_DT_INICIO */

    function getPRC_DT_FIM($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->PRC_DT_FIM)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->PRC_DT_FIM;
    }

    /* End of get PRC_DT_FIM */



    /* SET FIELDS FROM TABLE */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setTIC_ID_TIPO_CARGO($value) {
        $this->TIC_ID_TIPO_CARGO = $value;
    }

    /* End of SET TIC_ID_TIPO_CARGO */

    function setCUR_ID_CURSO($value) {
        $this->CUR_ID_CURSO = $value;
    }

    /* End of SET CUR_ID_CURSO */

    function setPRC_NR_EDITAL($value) {
        $this->PRC_NR_EDITAL = $value;
    }

    /* End of SET PRC_NR_EDITAL */

    function setPRC_ANO_EDITAL($value) {
        $this->PRC_ANO_EDITAL = $value;
    }

    /* End of SET PRC_ANO_EDITAL */

    function setPRC_DS_PROCESSO($value) {
        $this->PRC_DS_PROCESSO = $value;
    }

    /* End of SET PRC_DS_PROCESSO */

    function setPRC_DT_INICIO($value) {
        $this->PRC_DT_INICIO = $value;
    }

    /* End of SET PRC_DT_INICIO */

    function setPRC_DT_FIM($value) {
        $this->PRC_DT_FIM = $value;
    }

    /* End of SET PRC_DT_FIM */
}

?>
