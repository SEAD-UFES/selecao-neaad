<?php

/**
 * tb_ipr_inscricao_processo class
 * This class manipulates the table InscricaoProcesso
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
require_once $CFG->rpasta . "/controle/CTCandidato.php";
require_once $CFG->rpasta . "/controle/CTRastreio.php";
require_once $CFG->rpasta . "/negocio/ProcessoChamada.php";
require_once $CFG->rpasta . "/negocio/PoloInscricao.php";
require_once $CFG->rpasta . "/negocio/RespAnexoProc.php";
require_once $CFG->rpasta . "/negocio/TipoCargo.php";
require_once $CFG->rpasta . "/negocio/PoloChamada.php";
require_once $CFG->rpasta . "/negocio/AreaAtuChamada.php";
require_once $CFG->rpasta . "/negocio/EtapaSelProc.php";
require_once $CFG->rpasta . "/negocio/HistoricoInscExc.php";
require_once $CFG->rpasta . "/negocio/NotasEtapaSelInsc.php";
require_once $CFG->rpasta . "/negocio/ExportacaoResultado.php";

class InscricaoProcesso {

    private $IPR_ID_INSCRICAO;
    private $CDT_ID_CANDIDATO;
    private $PRC_ID_PROCESSO;
    private $PCH_ID_CHAMADA;
    private $IPR_DT_INSCRICAO;
    private $IPR_NR_ORDEM_INSC;
    private $IPR_COD_COMP_INSC;
    private $IPR_LOCALIZACAO_VALIDA;
    private $IPR_VL_TOTAL_NOTA;
    private $IPR_DS_OBS_NOTA;
    private $IPR_ID_USR_AVALIADOR;
    private $IPR_DT_AVALIACAO;
    private $IPR_NR_CLASSIFICACAO_CAND;
    private $IPR_ID_POLO_SELECIONADO;
    private $IPR_CDT_SELECIONADO;
    private $IPR_ST_AVAL_AUTOMATICA;
    private $AAC_ID_AREA_CHAMADA;
    private $IPR_ST_ANALISE;
    private $IPR_ST_INSCRICAO;
    private $RVC_ID_RESERVA_CHAMADA;
    private $IPR_ST_AVAL_CEGA;
    private $IPR_ID_ETAPA_SEL_NOTA;
// Campos herdados
    public $TIC_NM_TIPO_CARGO;
    public $CUR_NM_CURSO;
    public $CUR_ID_CURSO;
    public $TPC_NM_TIPO_CURSO;
    public $PRC_NR_ANO_EDITAL;
    public $PCH_DS_CHAMADA;
    public $PCH_DT_ABERTURA;
    public $PCH_DT_FECHAMENTO;
    public $PCH_CHAMADA_ATIVA;
    public $PCH_DT_REG_RESUL_FINAL;
    public $PCH_DT_FINALIZACAO;
    public $PRC_DS_URL_EDITAL;
    public $PRC_DS_PROCESSO;
    public $PCH_TXT_COMP_INSCRICAO;
    public $USR_DS_NOME_CDT;
    public $USR_DS_EMAIL_CDT;
    public $IDC_NR_CPF_CDT;
    private $USR_DS_AVALIADOR;
    private $POL_DS_POLO_SELECIONADO;
// tipo de classificação das listas de apresentação
    public static $ORDEM_INSCRITOS_INSCRICAO = 1;
    public static $ORDEM_INSCRITOS_NOME = 2;
    public static $ORDEM_INSCRITOS_CLASSIFICACAO = 3;
// tipo de ordenação usada na apresentação dos dados
    public static $ORDENACAO_CRESCENTE = "C";
    public static $ORDENACAO_DECRESCENTE = "D";
// tipo de exibição com base na situação do candidato
    public static $MOSTRAR_SITUACAO_TODOS = "T";
    public static $MOSTRAR_SITUACAO_CLAS = "C";
    public static $MOSTRAR_SITUACAO_ELIM = "E";
// situacao de avaliacao automatica
    public static $SIT_AVAL_AUTO_CONCLUIDA = 'C';
    public static $SIT_AVAL_AUTO_PENDENTE = 'P';
// situacao da analise
    public static $SIT_ANALISE_CONCLUIDA = 'C';
    public static $SIT_ANALISE_PENDENTE = 'P';
// situacao da avaliação cega
    public static $SIT_AVAL_CEGA_CONCLUIDA = 'C';
// situacao da inscriçao
    public static $SIT_INSC_ELIMINADO = 'E';
    public static $SIT_INSC_AUTO_ELIMINADO = 'A';
    public static $SIT_INSC_OK = 'O';
    public static $SIT_INSC_CAD_RESERVA = 'R';
    public static $SIT_INSC_FALTA_VAGAS_ELIMINADO = 'V';
// situação de remanejamento
    public static $REMANEJAMENTO_POLO_MULTIPLO = 0; // Este tipo é uma base para uma soma a fim de identificar a prioridade do polo em que o candidato foi selecionado
    public static $REMANEJAMENTO_RESERVA_VAGA = 'V';
// constantes importantes
    public static $MAX_CARACTERES_OBS_NOTA = 2000;
//processamento interno: Nunca chamar diretamente
    private $podeMostrarClas;
    private $avalAutoConc;
    private $etapaEliminacao;
    private $nmEtapaNota;
// mais processamento interno
    private $faseApresentacao; // FLAG que informa a fase do edital para apresentação. Apenas carregado ao utilizar a função buscarInscricaoPorUsuario
    public static $APRESENTACAO_ANDAMENTO = 'A';
    public static $APRESENTACAO_FINALIZADO = 'F';
//Apresentação dos inscritos baseado na situação de sua avaliação (validada ou pendente)
    private $situacaoNota; // FLAG que informa a situação de avaliação da inscrição. Apenas carregado ao utilizar a função buscarInscritosPorProcesso
    public static $APRES_INSC_NT_VALIDADA = 'V';
    public static $APRES_INSC_NT_NAO_VALIDADA = 'N';
// cadastro de reserva
    public static $MSG_CADASTRO_RESERVA = " (Cadastro de reserva)";

    public static function getDsClassificacao($tpClassificacao) {
        if ($tpClassificacao == self::$ORDEM_INSCRITOS_CLASSIFICACAO) {
            return "Classificação final";
        }
        if ($tpClassificacao == self::$ORDEM_INSCRITOS_NOME) {
            return "Nome do candidato";
        }
        if ($tpClassificacao == self::$ORDEM_INSCRITOS_INSCRICAO) {
            return "Inscrição";
        }
        return null;
    }

    public static function getDsTpExibSituacao($tpExibSituacao) {
        if ($tpExibSituacao == self::$MOSTRAR_SITUACAO_CLAS) {
            return "Classificados";
        }
        if ($tpExibSituacao == self::$MOSTRAR_SITUACAO_ELIM) {
            return "Eliminados";
        }
        if ($tpExibSituacao == self::$MOSTRAR_SITUACAO_TODOS) {
            return "Todos";
        }
        return null;
    }

    public static function getDsTpOrdenacao($tpOrdenacao) {
        if ($tpOrdenacao == self::$ORDENACAO_CRESCENTE) {
            return "Crescente";
        }
        if ($tpOrdenacao == self::$ORDENACAO_DECRESCENTE) {
            return "Decrescente";
        }
        return null;
    }

    public static function getListaTpClasDsClas() {
        $ret = array(
            self::$ORDEM_INSCRITOS_NOME => self::getDsClassificacao(self::$ORDEM_INSCRITOS_NOME),
            self::$ORDEM_INSCRITOS_INSCRICAO => self::getDsClassificacao(self::$ORDEM_INSCRITOS_INSCRICAO),
            self::$ORDEM_INSCRITOS_CLASSIFICACAO => self::getDsClassificacao(self::$ORDEM_INSCRITOS_CLASSIFICACAO)
        );
        return $ret;
    }

    public static function getListaTpExibSitDsTpExib() {
        $ret = array(
            self::$MOSTRAR_SITUACAO_TODOS => self::getDsTpExibSituacao(self::$MOSTRAR_SITUACAO_TODOS),
            self::$MOSTRAR_SITUACAO_CLAS => self::getDsTpExibSituacao(self::$MOSTRAR_SITUACAO_CLAS),
            self::$MOSTRAR_SITUACAO_ELIM => self::getDsTpExibSituacao(self::$MOSTRAR_SITUACAO_ELIM)
        );
        return $ret;
    }

    public static function getListaTpOrdenacaoDsOrdenacao() {
        $ret = array(
            self::$ORDENACAO_CRESCENTE => self::getDsTpOrdenacao(self::$ORDENACAO_CRESCENTE),
            self::$ORDENACAO_DECRESCENTE => self::getDsTpOrdenacao(self::$ORDENACAO_DECRESCENTE)
        );
        return $ret;
    }

    /* Construtor padrão da classe */

    public function __construct($IPR_ID_INSCRICAO, $CDT_ID_CANDIDATO, $PRC_ID_PROCESSO, $PCH_ID_CHAMADA, $IPR_DT_INSCRICAO, $IPR_NR_ORDEM_INSC, $IPR_COD_COMP_INSC, $IPR_LOCALIZACAO_VALIDA = NULL, $IPR_VL_TOTAL_NOTA = NULL, $IPR_DS_OBS_NOTA = NULL, $IPR_ID_USR_AVALIADOR = NULL, $IPR_DT_AVALIACAO = NULL, $IPR_NR_CLASSIFICACAO_CAND = NULL, $IPR_ID_POLO_SELECIONADO = NULL, $IPR_CDT_SELECIONADO = NULL, $IPR_ST_AVAL_AUTOMATICA = NULL, $AAC_ID_AREA_CHAMADA = NULL, $IPR_ST_ANALISE = NULL, $IPR_ST_INSCRICAO = NULL, $RVC_ID_RESERVA_CHAMADA = NULL, $IPR_ST_AVAL_CEGA = NULL, $IPR_ID_ETAPA_SEL_NOTA = NULL) {
        $this->IPR_ID_INSCRICAO = $IPR_ID_INSCRICAO;
        $this->CDT_ID_CANDIDATO = $CDT_ID_CANDIDATO;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->IPR_DT_INSCRICAO = $IPR_DT_INSCRICAO;
        $this->IPR_NR_ORDEM_INSC = $IPR_NR_ORDEM_INSC;
        $this->IPR_COD_COMP_INSC = $IPR_COD_COMP_INSC;
        $this->IPR_LOCALIZACAO_VALIDA = $IPR_LOCALIZACAO_VALIDA;
        $this->IPR_VL_TOTAL_NOTA = $IPR_VL_TOTAL_NOTA;
        $this->IPR_DS_OBS_NOTA = $IPR_DS_OBS_NOTA;
        $this->IPR_ID_USR_AVALIADOR = $IPR_ID_USR_AVALIADOR;
        $this->IPR_DT_AVALIACAO = $IPR_DT_AVALIACAO;
        $this->IPR_NR_CLASSIFICACAO_CAND = $IPR_NR_CLASSIFICACAO_CAND;
        $this->IPR_ID_POLO_SELECIONADO = $IPR_ID_POLO_SELECIONADO;
        $this->IPR_CDT_SELECIONADO = $IPR_CDT_SELECIONADO;
        $this->IPR_ST_AVAL_AUTOMATICA = $IPR_ST_AVAL_AUTOMATICA;
        $this->AAC_ID_AREA_CHAMADA = $AAC_ID_AREA_CHAMADA;
        $this->IPR_ST_ANALISE = $IPR_ST_ANALISE;
        $this->IPR_ST_INSCRICAO = $IPR_ST_INSCRICAO;
        $this->RVC_ID_RESERVA_CHAMADA = $RVC_ID_RESERVA_CHAMADA;
        $this->IPR_ST_AVAL_CEGA = $IPR_ST_AVAL_CEGA;
        $this->IPR_ID_ETAPA_SEL_NOTA = $IPR_ID_ETAPA_SEL_NOTA;
        $this->podeMostrarClas = $this->avalAutoConc = $this->etapaEliminacao = $this->nmEtapaNota = NULL;
    }

    public static function getMatrizTpApresentacaoOrd() {
        return array(self::$APRESENTACAO_ANDAMENTO => "Chamadas em Andamento", self::$APRESENTACAO_FINALIZADO => "Chamadas Finalizadas");
    }

    public static function getMatrizTpApresInscOrd() {
        return array(self::$APRES_INSC_NT_NAO_VALIDADA => "Inscrições com notas pendentes", self::$APRES_INSC_NT_VALIDADA => "Inscrições com notas validadas");
    }

    /**
     * Se direcionar mensagem for falso, a funçao retorna um array do tipo:
     * (InscricaoValida, msg, classe da mensagem)
     * 
     * @global stdclass $CFG
     * 
     * @param int $idUsuario
     * @param int $idProcesso
     * @param int $idChamada
     * @param boolean $direcionarMsg
     * 
     * @return array
     * @throws NegocioException
     */
    public static function validaInscricaoUsuario($idUsuario, $idProcesso, $idChamada, $direcionarMsg = TRUE) {
        global $CFG;

        $msgInicial = "Você não pode se inscrever nesse edital, pois ainda falta você informar seus dados de ";

        try {

// verificando preenchimento do perfil
// identificaçao
            if (!preencheuIdentificacaoCT($idUsuario)) {
                $msg = $msgInicial . "'Identificação'.";
                $inst = "<br/><p>Você pode fazer isso clicando no menu <b>Candidato</b> acima a direita, no ícone <span class='fa fa-user'></span> e selecionando a opção <a title='Gerenciar suas informações de identificação' href='$CFG->rwww/visao/candidato/editarIdentificacao.php'>Identificação</a>.</p>";
                if ($direcionarMsg) {
                    RAT_criarRastreioInscricaoEditalCT($idUsuario, $idProcesso, $idChamada);
                    new Mensagem("Falta preencher Identificação...", Mensagem::$MENSAGEM_INFORMACAO, NULL, "", "$CFG->rwww/visao/candidato/editarIdentificacao.php");
                }
                return array(FALSE, $msg . $inst, Util::$CLASSE_MSG_ERRO);
            }

// endereço
            if (!preencheuEnderecoCT($idUsuario)) {
                $msg = $msgInicial . "'Endereço Residencial'.";
                $inst = "<br/><p>Você pode fazer isso clicando no menu <b>Candidato</b> acima a direita, no ícone <span class='fa fa-user'></span> e selecionando a opção <a title='Gerenciar suas informações de endereço' href='$CFG->rwww/visao/candidato/editarEndereco.php'>Endereço</a>.</p>";
                if ($direcionarMsg) {
                    RAT_criarRastreioInscricaoEditalCT($idUsuario, $idProcesso, $idChamada);
                    new Mensagem("Falta preencher Endereço...", Mensagem::$MENSAGEM_INFORMACAO, NULL, "", "$CFG->rwww/visao/candidato/editarEndereco.php");
                }
                return array(FALSE, $msg . $inst, Util::$CLASSE_MSG_ERRO);
            }

// contato
            if (!preencheuContatoCT($idUsuario)) {
                $msg = $msgInicial . "'Contato'.";
                $inst = "<br/><p>Você pode fazer isso clicando no menu <b>Candidato</b> acima a direita, no ícone <span class='fa fa-user'></span> e selecionando a opção <a title='Gerenciar suas informações de contato' href='$CFG->rwww/visao/candidato/editarContato.php'>Contato</a>.</p>";
                if ($direcionarMsg) {
                    RAT_criarRastreioInscricaoEditalCT($idUsuario, $idProcesso, $idChamada);
                    new Mensagem("Falta preencher Contato...", Mensagem::$MENSAGEM_INFORMACAO, NULL, "", "$CFG->rwww/visao/candidato/editarContato.php");
                }
                return array(FALSE, $msg . $inst, Util::$CLASSE_MSG_ERRO);
            }

// formaçao
            if (!preencheuFormacaoCT($idUsuario)) {
                $msg = $msgInicial . "'Formação'.";
                $inst = "<br/><p>Você pode fazer isso clicando no menu <b>Candidato</b> acima a direita, no ícone <span class='fa fa-user'></span> e selecionando a opção <a title='Gerenciar seu currículo' href='$CFG->rwww/visao/formacao/listarFormacao.php'>Currículo</a>.</p>";
                if ($direcionarMsg) {
                    RAT_criarRastreioInscricaoEditalCT($idUsuario, $idProcesso, $idChamada);
                    new Mensagem("Falta preencher Currículo...", Mensagem::$MENSAGEM_INFORMACAO, NULL, "", "$CFG->rwww/visao/formacao/listarFormacao.php");
                }
                return array(FALSE, $msg . $inst, Util::$CLASSE_MSG_ERRO);
            }

//caso de já ter feito inscrição
            if (InscricaoProcesso::contaInscProcUltChamada($idUsuario, $idProcesso) != 0) {
                $idInscricao = self::getIdInscProcUltChamada($idUsuario, $idProcesso);
                $msg = "Você já se inscreveu para este edital.";
                $inst = "<br/><p>Você pode consultar suas inscrições clicando no menu <b>Edital</b> acima a direita, no ícone <span class='fa fa-book'></span> e selecionando a opção <a title='Visualizar editais que eu me inscrevi' href='$CFG->rwww/visao/inscricaoProcesso/listarInscProcessoUsuario.php'>Minhas Inscrições</a>.<p>";
                if ($direcionarMsg) {
                    RAT_removerDadosSessaoInscProcesso();
                    new Mensagem("Você já está inscrito neste edital...", Mensagem::$MENSAGEM_INFORMACAO, NULL, "inscricao", "$CFG->rwww/visao/inscricaoProcesso/consultarInscProcesso.php?idInscricao=$idInscricao");
                }
                return array(FALSE, $msg . $inst, Util::$CLASSE_MSG_INFORMACAO);
            }

// caso de nao existir alguma chamada em aberto
            if (!ProcessoChamada::validaPeriodoInscPorProcesso($idProcesso)) {
                $msg = "Este edital não está com inscrições abertas.";
                if ($direcionarMsg) {
                    RAT_removerDadosSessaoInscProcesso();
                    new Mensagem($msg, Mensagem::$MENSAGEM_ERRO);
                }
                return array(FALSE, $msg, Util::$CLASSE_MSG_AVISO);
            }

// tudo ok
            return array(TRUE, NULL, NULL);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar validar inscrição do usuário.", $e);
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param EtapaAvalProc $etapaAval
     * 
     * @return ExportacaoResultado Objeto de exportação de dados
     * @throws NegocioException
     */
    public static function montarObjExportacaoResultado($chamada, $etapaAval = NULL) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $nmColClassif = ExportacaoResultado::$NM_COLUNA_CLASSIF;
            $nmColNome = ExportacaoResultado::$NM_COLUNA_NOME;

            /**
             * OBS IMPORTANTE: Ao Alterar esta sql, REVISE a classe ExportacaoResultado.php
             * Efetue os ajustes no atributo privado $COLUNAS_REMOVER e nos demais campos necessários!
             * 
             * OBS IMPORTANTE 2: NÃO ALTERE A ORDENAÇÃO UTILIZADA NESTA SQL, pois ela é utilizada para 
             * minimizar o processamento!
             */
// sql inicial
            $sql = "select 
                    ipr.IPR_ID_INSCRICAO, ";


// definindo o id do polo selecionado
            if ($etapaAval == NULL) {
// caso de não ter etapa: ID do polo da tabela master
                $sql.= "ipr.IPR_ID_POLO_SELECIONADO as 'polo', ";
            } else {
// id do polo da etapa
                $sql.= "NEI_ID_POLO_SELECIONADO as 'polo', ";
            }

// continuando sql inicial
            $sql .= " ipr.AAC_ID_AREA_CHAMADA as 'area',
                    ipr.RVC_ID_RESERVA_CHAMADA as 'reserva',
                    IPR_NR_ORDEM_INSC as 'Insc',
                    usr.USR_DS_NOME as '$nmColNome' ";

            if ($etapaAval == NULL) { // resultado final
// percorrendo etapas
                $etapas = buscarEtapaPorChamadaCT($chamada->getPCH_ID_CHAMADA());
                foreach ($etapas as $etapa) {
                    $sql .= " ,et{$etapa->getESP_NR_ETAPA_SEL()}.NEI_VL_TOTAL_NOTA as '{$etapa->getNomeEtapa()}'";
                }

// dados intermediários
                $sql.= ", IPR_VL_TOTAL_NOTA as 'Final',
                     IPR_NR_CLASSIFICACAO_CAND as '$nmColClassif',
                     IPR_DS_OBS_NOTA as 'Justificativa',
                     IPR_ST_INSCRICAO as 'situacao'
                    from
                    tb_ipr_inscricao_processo ipr
                        join
                    tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
                        join
                    tb_usr_usuario usr ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                        join
                    tb_ctc_contato_candidato ctc ON ctc.CTC_ID_CONTATO_CDT = cdt.CTC_ID_CONTATO_CDT
                        join
                    tb_idc_identificacao_candidato idc ON cdt.IDC_ID_IDENTIFICACAO_CDT = idc.IDC_ID_IDENTIFICACAO_CDT ";

// juntando as tabelas de etapa
                foreach ($etapas as $etapa) {
                    $sql .= " join 
                            tb_nei_notas_etapa_sel_insc et{$etapa->getESP_NR_ETAPA_SEL()} on ipr.IPR_ID_INSCRICAO = et{$etapa->getESP_NR_ETAPA_SEL()}.IPR_ID_INSCRICAO
                            and et{$etapa->getESP_NR_ETAPA_SEL()}.ESP_ID_ETAPA_SEL = '{$etapa->getESP_ID_ETAPA_SEL()}'";
                }

// finalização
                $sql .= " where PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND";
            } else { // caso de ser resultado de uma etapa
                $sql.= ", NEI_VL_TOTAL_NOTA as '{$etapaAval->getNomeEtapa()}',
                     NEI_NR_CLASSIFICACAO_CAND as '$nmColClassif',
                     NEI_DS_OBS_NOTA as 'Justificativa',
                     NEI_ST_INSCRICAO as 'situacao'
                    from
                        tb_ipr_inscricao_processo ipr
                            join
                        tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
                            join
                        tb_usr_usuario usr ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                            join 
                         tb_nei_notas_etapa_sel_insc nei on ipr.IPR_ID_INSCRICAO = nei.IPR_ID_INSCRICAO
                        and ESP_ID_ETAPA_SEL = (select ESP_ID_ETAPA_SEL from tb_esp_etapa_sel_proc where
                            EAP_ID_ETAPA_AVAL_PROC = '{$etapaAval->getEAP_ID_ETAPA_AVAL_PROC()}'
                            and PRC_ID_PROCESSO = '{$chamada->getPRC_ID_PROCESSO()}' and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}')
                    where PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND";
            }

//            print_r($sql);
//            exit;
//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
//retornando nulo
                return NULL;
            }

// Criando estrutura de processamento de dados para retorno
            return new ExportacaoResultado($chamada, $resp, $etapaAval);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao gerar objeto de exportação do resultado do processo.", $e);
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param EtapaAvalProc $etapaAval
     * 
     * @return string CSV
     * @throws NegocioException
     */
    public static function getCSVInscritosProcResultado($chamada, $etapaAval = NULL) {
        try {
// Recuperando estrutura de processamento de dados para retorno
            $expResultado = self::montarObjExportacaoResultado($chamada, $etapaAval);

// caso de não ter inscritos
            if ($expResultado == NULL) {
                throw new NegocioException("Não existem candidatos inscritos.");
            }
            return matrizParaCSV($expResultado->getCabecalhoCSV(), $expResultado->getMatrizCSV());
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao gerar arquivo CSV dos candidatos inscritos no processo.", $e);
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param EtapaAvalProc $etapaAval
     * 
     * @return string CSV
     * @throws NegocioException
     */
    public static function getCSVInscritosProcNotas($chamada, $etapaAval = NULL) {
        try {

//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

// sql inicial
            $sql = "select 
                    ipr.IPR_ID_INSCRICAO,
                    IPR_NR_ORDEM_INSC as 'Inscricao',
                    DATE_FORMAT(`IPR_DT_INSCRICAO`, '%d/%m/%Y %T') AS 'Data',
                    usr.USR_DS_NOME as 'Nome completo',
                    usr.USR_DS_EMAIL as 'Email',
                    CTC_NR_CELULAR as 'Celular',
                    IDC_DS_SEXO as 'Sexo',
                    DATE_FORMAT(`IDC_NASC_DATA`, '%d/%m/%Y') as 'Data de nascimento',
                    concat(substring(`IDC_NR_CPF` from 1 for 3),
                            '.',
                            substring(`IDC_NR_CPF` from 4 for 3),
                            '.',
                            substring(`IDC_NR_CPF` from 7 for 3),
                            '-',
                            substring(`IDC_NR_CPF` from 10 for 2)) as CPF, ";

            if ($etapaAval == NULL) {
                $sql.= "IPR_VL_TOTAL_NOTA as 'Nota final',
                     IPR_DS_OBS_NOTA as 'Just. eliminacao'
                from
                    tb_ipr_inscricao_processo ipr
                        join
                    tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
                        join
                    tb_usr_usuario usr ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                        join
                    tb_ctc_contato_candidato ctc ON ctc.CTC_ID_CONTATO_CDT = cdt.CTC_ID_CONTATO_CDT
                        join
                    tb_idc_identificacao_candidato idc ON cdt.IDC_ID_IDENTIFICACAO_CDT = idc.IDC_ID_IDENTIFICACAO_CDT
                where PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND";
            } else {
                $sql.= "NEI_VL_TOTAL_NOTA as 'Nota {$etapaAval->getNomeEtapa()}',
                     NEI_DS_OBS_NOTA as 'Just. eliminacao'
                from
                    tb_ipr_inscricao_processo ipr
                        join
                    tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
                        join
                    tb_usr_usuario usr ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                        join
                    tb_ctc_contato_candidato ctc ON ctc.CTC_ID_CONTATO_CDT = cdt.CTC_ID_CONTATO_CDT
                        join
                    tb_idc_identificacao_candidato idc ON cdt.IDC_ID_IDENTIFICACAO_CDT = idc.IDC_ID_IDENTIFICACAO_CDT
                        join 
                    tb_nei_notas_etapa_sel_insc nei on ipr.IPR_ID_INSCRICAO = nei.IPR_ID_INSCRICAO
                    and ESP_ID_ETAPA_SEL = (select ESP_ID_ETAPA_SEL from tb_esp_etapa_sel_proc where
                            EAP_ID_ETAPA_AVAL_PROC = '{$etapaAval->getEAP_ID_ETAPA_AVAL_PROC()}'
                            and PRC_ID_PROCESSO = '{$chamada->getPRC_ID_PROCESSO()}' and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}')
                where PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                order by NEI_NR_CLASSIFICACAO_CAND IS NULL, NEI_NR_CLASSIFICACAO_CAND";
            }

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
//retornando vazio
                throw new NegocioException("Não existem candidatos inscritos.");
            }

// criando estruturas para geracao de dados auxiliares
            $cabecalho = array();
            $matriz = array();
            $colAtual = 0;
            $numInscritos = 0;

// convertendo dados atuais
// 1º linha
            $dados = ConexaoMysql::getLinha($resp);
            $cabecalho = array_keys($dados);
            $matriz[$dados[$cabecalho[0]]] = array();
            for ($i = 1; $i < count($cabecalho); $i++) {
                $matriz[$dados[$cabecalho[0]]][$i - 1] = !Util::vazioNulo($dados[$cabecalho[$i]]) ? $dados[$cabecalho[$i]] : Util::$STR_CAMPO_VAZIO;
            }

// demais linhas
            for ($i = 1; $i < $numLinhas; $i++) {
                $dados = ConexaoMysql::getLinha($resp);
                $matriz[$dados[$cabecalho[0]]] = array();
                for ($j = 1; $j < count($dados); $j++) {
                    $matriz[$dados[$cabecalho[0]]][$j - 1] = !Util::vazioNulo($dados[$cabecalho[$j]]) ? $dados[$cabecalho[$j]] : Util::$STR_CAMPO_VAZIO;
                }
            }
            array_shift($cabecalho);
            $colAtual = count($cabecalho);
            $numInscritos = $numLinhas;

// agora, matriz e da forma: [IdInscricao => array(d1, d2, ..., dn)]
//
            // caso de admitir area de atuaçao
            if ($chamada->admiteAreaAtuacaoObj()) {
// gerando sql de recuperar area
                $sql = "select 
                            IPR_ID_INSCRICAO,
                                ARC_NM_AREA_CONH
                        from
                            tb_ipr_inscricao_processo ipr
                                join
                           tb_aac_area_atu_chamada aac on aac.AAC_ID_AREA_CHAMADA = ipr.AAC_ID_AREA_CHAMADA
                                        join 
                           tb_arc_area_conhecimento arc on arc.ARC_ID_AREA_CONH = aac.ARC_ID_SUBAREA_CONH
                        where ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                        order by IPR_NR_ORDEM_INSC";

                $resp = $conexao->execSqlComRetorno($sql);

// inserindo na matriz
                for ($i = 0; $i < $numInscritos; $i++) {
                    $dados = ConexaoMysql::getLinha($resp);
                    $matriz[$dados['IPR_ID_INSCRICAO']][$colAtual] = $dados['ARC_NM_AREA_CONH'];
                }
                $cabecalho [] = 'Area de atuacao';
                $colAtual++;
            }

// caso de admitir polo
            if ($chamada->admitePoloObj()) {
                if ($chamada->isInscricaoMultipla()) {

// gerando sql de recuperar polo multiplos
                    $sql = "SELECT 
                                ipr.IPR_ID_INSCRICAO,
                                GROUP_CONCAT(POL_DS_POLO ORDER BY PIN_NR_ORDEM
                                    SEPARATOR '|') as dsPolo
                            FROM
                                tb_ipr_inscricao_processo ipr
                                    JOIN
                                tb_pin_polo_inscricao pin ON ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO
                                    JOIN
                                tb_pol_polo pol ON pin.POL_ID_POLO = pol.POL_ID_POLO
                            WHERE
                                ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                            GROUP BY IPR_ID_INSCRICAO
                            ORDER BY IPR_NR_ORDEM_INSC";
                } else {

// gerando sql de recuperar polo
                    $sql = "select 
                            ipr.IPR_ID_INSCRICAO,
                            POL_DS_POLO as dsPolo
                        from
                            tb_ipr_inscricao_processo ipr
                                join
                           tb_pin_polo_inscricao pin on ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO
                                join 
                           tb_pol_polo pol on pin.POL_ID_POLO = pol.POL_ID_POLO
                        where ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                        order by IPR_NR_ORDEM_INSC";
                }

                $resp = $conexao->execSqlComRetorno($sql);

// inserindo na matriz
                for ($i = 0; $i < $numInscritos; $i++) {
                    $dados = ConexaoMysql::getLinha($resp);
                    $matriz[$dados['IPR_ID_INSCRICAO']][$colAtual] = $dados['dsPolo'];
                }
                $cabecalho [] = 'Polo';
                $colAtual++;
            }

// caso de admitir reserva de vaga
            if ($chamada->admiteReservaVagaObj()) {
// gerando sql de recuperar reservas de vaga
                $sql = "select 
                            ipr.IPR_ID_INSCRICAO,
                            RVG_NM_RESERVA_VAGA
                        from
                            tb_ipr_inscricao_processo ipr
                               left join
                           tb_rvc_reserva_vaga_chamada rvc on rvc.RVC_ID_RESERVA_CHAMADA = ipr.RVC_ID_RESERVA_CHAMADA
                               left join 
                           tb_rvg_reserva_vaga rvg on rvc.RVG_ID_RESERVA_VAGA = rvg.RVG_ID_RESERVA_VAGA
                        where ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                        order by IPR_NR_ORDEM_INSC";

                $resp = $conexao->execSqlComRetorno($sql);

// inserindo na matriz
                for ($i = 0; $i < $numInscritos; $i++) {
                    $dados = ConexaoMysql::getLinha($resp);
                    $dsReserva = Util::vazioNulo($dados['RVG_NM_RESERVA_VAGA']) ? ReservaVagaChamada::$DS_PUBLICO_GERAL : $dados['RVG_NM_RESERVA_VAGA'];
                    $matriz[$dados['IPR_ID_INSCRICAO']][$colAtual] = $dsReserva;
                }
                $cabecalho [] = 'Reserva de vaga';
                $colAtual++;
            }

// recuperando dados de pontuacao
            $etapas = EtapaSelProc::buscarEtapaPorChamada($chamada->getPCH_ID_CHAMADA(), $etapaAval == NULL ? NULL : $etapaAval->getEAP_ID_ETAPA_AVAL_PROC());
            if ($etapas == NULL) {
                throw new NegocioException("Não existem etapas de seleção.");
            }

// percorrendo etapas
            foreach ($etapas as $etapa) {
                if ($etapaAval == NULL) {
// inserindo nota da etapa corrente
// 
// gerando sql de recuperação da nota na etapa
                    $sql = "select 
                            ipr.IPR_ID_INSCRICAO,
                            NEI_VL_TOTAL_NOTA
                        from
                            tb_ipr_inscricao_processo ipr
                               join 
                            tb_nei_notas_etapa_sel_insc nei on ipr.IPR_ID_INSCRICAO = nei.IPR_ID_INSCRICAO
                            and ESP_ID_ETAPA_SEL = '{$etapa->getESP_ID_ETAPA_SEL()}'
                        where ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                        order by IPR_NR_ORDEM_INSC";

                    $resp = $conexao->execSqlComRetorno($sql);

// inserindo na matriz
                    for ($i = 0; $i < $numInscritos; $i++) {
                        $dados = ConexaoMysql::getLinha($resp);
                        $matriz[$dados['IPR_ID_INSCRICAO']][$colAtual] = $dados['NEI_VL_TOTAL_NOTA'];
                    }
                    $cabecalho [] = "Nota {$etapa->getNomeEtapa()}";
                    $colAtual++;
                }

// recuperando categoria
                $categorias = CategoriaAvalProc::buscarCatAvalPorProcEtapaTp($chamada->getPRC_ID_PROCESSO(), $etapa->getESP_NR_ETAPA_SEL());

// percorrendo categorias
                foreach ($categorias as $categoria) {
// inserindo cabecalho da categoria
                    $cabecalho [] = CategoriaAvalProc::getDsTipoSemAcento($categoria->getCAP_TP_CATEGORIA());


// buscando somatorio de notas
                    $notasCat = RelNotasInsc::buscarSomaNotasPorCatItem($chamada->getPCH_ID_CHAMADA(), $categoria->getCAP_ID_CATEGORIA_AVAL(), NULL, FALSE, RelNotasInsc::$SIT_ATIVA, TRUE);

//                    print_r($notasCat);
//                    print("<br/>");
//                    exit;
// preenchendo coluna
                    $matriz = self::preencheColunaMatrizNota($colAtual, $matriz, $notasCat);

// incrementando coluna
                    $colAtual++;

// recuperando itens
                    $itens = ItemAvalProc::buscarItensAvalPorCat($categoria->getPRC_ID_PROCESSO(), $categoria->getCAP_ID_CATEGORIA_AVAL());

// caso de categoria sem item: Criando vetor vazio
                    if ($itens == NULL) {
                        $itens = array();
                    }

//percorrendo itens
                    $cont = 1;
                    foreach ($itens as $item) {
// inserindo cabecalho do item
                        $nmCab = $item->getNomeItemCompletoArq($categoria->getCAP_TP_CATEGORIA());
                        if (array_search($nmCab, $cabecalho) !== FALSE) {
                            $nmCab .= $cont;
                            $cont++;
                        }
                        $cabecalho [] = $nmCab;

// recuperando relatorios do item
                        $notasItem = RelNotasInsc::buscarSomaNotasPorCatItem($chamada->getPCH_ID_CHAMADA(), $categoria->getCAP_ID_CATEGORIA_AVAL(), $item->getIAP_ID_ITEM_AVAL(), FALSE, RelNotasInsc::$SIT_ATIVA, TRUE);

//                        print_r($notasItem);
//                        print("<br/>");
//                        exit;
// preenchendo coluna
                        $matriz = self::preencheColunaMatrizNota($colAtual, $matriz, $notasItem);

// incrementando coluna
                        $colAtual++;
                    }
                }
            }


//gerando e retornando
            return matrizParaCSV($cabecalho, $matriz);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao gerar arquivo CSV dos candidatos inscritos no processo.", $e);
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @return string CSV
     * @throws NegocioException
     */
    public static function getCSVInscritosProcGeral($chamada) {
        try {

//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

// recuperando dados importantes
            $whenCaseEstCivil = IdentificacaoCandidato::getWhenCaseExpEstadoCivil();
            $whenCaseExpRaca = IdentificacaoCandidato::getWhenCaseExpRaca();
            $tpGraduacao = TipoCurso::getTpGraduacao();
            $stCursoConc = FormacaoAcademica::$ST_FORMACAO_COMPLETO;

// sql inicial
            $sql = "select 
                    IPR_ID_INSCRICAO,
                    IPR_NR_ORDEM_INSC as 'Inscricao',
                    DATE_FORMAT(`IPR_DT_INSCRICAO`, '%d/%m/%Y %T') AS 'Data',
                    usr.USR_DS_NOME as 'Nome completo',
                    usr.USR_DS_EMAIL as 'Email',
                    CTC_NR_CELULAR as 'Celular',
                    IDC_DS_SEXO as 'Sexo',
                    DATE_FORMAT(`IDC_NASC_DATA`, '%d/%m/%Y') as 'Data de nascimento',
                    concat(cid.CID_NM_CIDADE, ' - ', IDC_NASC_ESTADO) as 'Local de nascimento',
                    concat(cidA.CID_NM_CIDADE, ' - ', EST_ID_UF) as 'Endereco atual',
                    case `IDC_TP_ESTADO_CIVIL`
                    $whenCaseEstCivil
                    end as 'Estado civil',
                    case `IDC_TP_RACA`
                    $whenCaseExpRaca
                    end as 'Etnia',
                    (SELECT 
                        CONCAT(FRA_NM_CURSO,
                                ' de ',
                                FRA_ANO_INICIO,
                                ' a ',
                                FRA_ANO_CONCLUSAO,
                                ' em ',
                                FRA_NM_INSTITUICAO)
                    FROM
                        tb_fra_formacao_academica fra
                    WHERE
                        ipr.CDT_ID_CANDIDATO = fra.CDT_ID_CANDIDATO
                            AND TPC_ID_TIPO_CURSO = '$tpGraduacao'
                            AND FRA_STATUS_CURSO = '$stCursoConc'
                    order by FRA_ANO_CONCLUSAO desc
                    LIMIT 0 , 1) as 'Ultima graduacao',
                    IDC_RG_NUMERO as 'RG',
                    IDC_RG_ORGAO_EXP as 'RG emissor',
                    IDC_RG_UF as 'RG estado emissor',
                    DATE_FORMAT(`IDC_RG_DT_EMISSAO`, '%d/%m/%Y') as 'RG data de emissao',
                    concat(substring(`IDC_NR_CPF` from 1 for 3),
                            '.',
                            substring(`IDC_NR_CPF` from 4 for 3),
                            '.',
                            substring(`IDC_NR_CPF` from 7 for 3),
                            '-',
                            substring(`IDC_NR_CPF` from 10 for 2)) as CPF
                from
                    tb_ipr_inscricao_processo ipr
                        join
                    tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
                        join
                    tb_usr_usuario usr ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                        join
                    tb_ctc_contato_candidato ctc ON ctc.CTC_ID_CONTATO_CDT = cdt.CTC_ID_CONTATO_CDT
                        join
                    tb_idc_identificacao_candidato idc ON cdt.IDC_ID_IDENTIFICACAO_CDT = idc.IDC_ID_IDENTIFICACAO_CDT
                    left join
                        tb_cid_cidade cid on idc.idc_nasc_cidade = cid.cid_id_cidade
                    left join
                        tb_end_endereco end on end.END_ID_ENDERECO = cdt.CDT_ENDERECO_RES
                    left join
                        tb_cid_cidade cidA on cidA.cid_id_cidade = end.CID_ID_CIDADE
                where PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                order by IPR_NR_ORDEM_INSC";
                    
                    
                    /* "select 
                    IPR_ID_INSCRICAO,
                    IPR_NR_ORDEM_INSC as 'Inscricao',
                    DATE_FORMAT(`IPR_DT_INSCRICAO`, '%d/%m/%Y %T') AS 'Data',
                    usr.USR_DS_NOME as 'Nome completo',
                    usr.USR_DS_EMAIL as 'Email',
                    CTC_NR_CELULAR as 'Celular',
                    IDC_DS_SEXO as 'Sexo',
                    DATE_FORMAT(`IDC_NASC_DATA`, '%d/%m/%Y') as 'Data de nascimento',
                    concat(cid.CID_NM_CIDADE, ' - ', IDC_NASC_ESTADO) as 'Local de nascimento',
                    concat(cidA.CID_NM_CIDADE, ' - ', EST_ID_UF) as 'Endereco atual',
                    case `IDC_TP_ESTADO_CIVIL`
                    $whenCaseEstCivil
                    end as 'Estado civil',
                    case `IDC_TP_RACA`
                    $whenCaseExpRaca
                    end as 'Etnia',
                    (SELECT 
                        CONCAT(FRA_NM_CURSO,
                                ' de ',
                                FRA_ANO_INICIO,
                                ' a ',
                                FRA_ANO_CONCLUSAO,
                                ' em ',
                                FRA_NM_INSTITUICAO)
                    FROM
                        tb_fra_formacao_academica fra
                    WHERE
                        ipr.CDT_ID_CANDIDATO = fra.CDT_ID_CANDIDATO
                            AND TPC_ID_TIPO_CURSO = '$tpGraduacao'
                            AND FRA_STATUS_CURSO = '$stCursoConc'
                    order by FRA_ANO_CONCLUSAO desc
                    LIMIT 0 , 1) as 'Ultima graduacao',
                    IDC_RG_NUMERO as 'RG',
                    IDC_RG_ORGAO_EXP as 'RG emissor',
                    IDC_RG_UF as 'RG estado emissor',
                    DATE_FORMAT(`IDC_RG_DT_EMISSAO`, '%d/%m/%Y') as 'RG data de emissao',
                    concat(substring(`IDC_NR_CPF` from 1 for 3),
                            '.',
                            substring(`IDC_NR_CPF` from 4 for 3),
                            '.',
                            substring(`IDC_NR_CPF` from 7 for 3),
                            '-',
                            substring(`IDC_NR_CPF` from 10 for 2)) as CPF
                from
                    tb_ipr_inscricao_processo ipr
                        join
                    tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
                        join
                    tb_usr_usuario usr ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                        join
                    tb_ctc_contato_candidato ctc ON ctc.CTC_ID_CONTATO_CDT = cdt.CTC_ID_CONTATO_CDT
                        join
                    tb_idc_identificacao_candidato idc ON cdt.IDC_ID_IDENTIFICACAO_CDT = idc.IDC_ID_IDENTIFICACAO_CDT
                        join
                    tb_cid_cidade cid on idc.idc_nasc_cidade = cid.cid_id_cidade
                    left join
                        tb_end_endereco end on end.END_ID_ENDERECO = cdt.CDT_ENDERECO_RES
                    left join
                        tb_cid_cidade cidA on cidA.cid_id_cidade = end.CID_ID_CIDADE
                where PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                order by IPR_NR_ORDEM_INSC";*/

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
//retornando vazio
                throw new NegocioException("Não existem candidatos inscritos.");
            }

// criando estruturas para geracao de dados auxiliares
            $cabecalho = array();
            $matriz = array();
            $colAtual = 0;
            $numInscritos = 0;

// convertendo dados atuais
// 1º linha
            $dados = ConexaoMysql::getLinha($resp);
            $cabecalho = array_keys($dados);
            $matriz[$dados[$cabecalho[0]]] = array();
            for ($i = 1; $i < count($cabecalho); $i++) {
                $matriz[$dados[$cabecalho[0]]][$i - 1] = !Util::vazioNulo($dados[$cabecalho[$i]]) ? $dados[$cabecalho[$i]] : Util::$STR_CAMPO_VAZIO;
            }

// demais linhas
            for ($i = 1; $i < $numLinhas; $i++) {
                $dados = ConexaoMysql::getLinha($resp);
                $matriz[$dados[$cabecalho[0]]] = array();
                for ($j = 1; $j < count($dados); $j++) {
                    $matriz[$dados[$cabecalho[0]]][$j - 1] = !Util::vazioNulo($dados[$cabecalho[$j]]) ? $dados[$cabecalho[$j]] : Util::$STR_CAMPO_VAZIO;
                }
            }
            array_shift($cabecalho);
            $colAtual = count($cabecalho);
            $numInscritos = $numLinhas;

// agora, a matriz é da forma: [IdInscricao => array(d1, d2, ..., dn)]
// 
// caso de admitir area de atuaçao
            if ($chamada->admiteAreaAtuacaoObj()) {
// gerando sql de recuperar area
                $sql = "select 
IPR_ID_INSCRICAO,
ARC_NM_AREA_CONH
from
tb_ipr_inscricao_processo ipr
join
tb_aac_area_atu_chamada aac on aac.AAC_ID_AREA_CHAMADA = ipr.AAC_ID_AREA_CHAMADA
join 
tb_arc_area_conhecimento arc on arc.ARC_ID_AREA_CONH = aac.ARC_ID_SUBAREA_CONH
where ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
order by IPR_NR_ORDEM_INSC";

                $resp = $conexao->execSqlComRetorno($sql);

// inserindo na matriz
                for ($i = 0; $i < $numInscritos; $i++) {
                    $dados = ConexaoMysql::getLinha($resp);
                    $matriz[$dados['IPR_ID_INSCRICAO']][$colAtual] = $dados['ARC_NM_AREA_CONH'];
                }
                $cabecalho [] = 'Area de atuacao';
                $colAtual++;
            }

// caso de admitir polo
            if ($chamada->admitePoloObj()) {
                if ($chamada->isInscricaoMultipla()) {

// gerando sql de recuperar polo multiplos
                    $sql = "SELECT 
ipr.IPR_ID_INSCRICAO,
GROUP_CONCAT(POL_DS_POLO ORDER BY PIN_NR_ORDEM
SEPARATOR '|') as dsPolo
FROM
tb_ipr_inscricao_processo ipr
JOIN
tb_pin_polo_inscricao pin ON ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO
JOIN
tb_pol_polo pol ON pin.POL_ID_POLO = pol.POL_ID_POLO
WHERE
ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
GROUP BY IPR_ID_INSCRICAO
ORDER BY IPR_NR_ORDEM_INSC";
                } else {

// gerando sql de recuperar polo
                    $sql = "select 
ipr.IPR_ID_INSCRICAO,
POL_DS_POLO as dsPolo
from
tb_ipr_inscricao_processo ipr
join
tb_pin_polo_inscricao pin on ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO
join 
tb_pol_polo pol on pin.POL_ID_POLO = pol.POL_ID_POLO
where ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
order by IPR_NR_ORDEM_INSC";
                }

                $resp = $conexao->execSqlComRetorno($sql);

// inserindo na matriz
                for ($i = 0; $i < $numInscritos; $i++) {
                    $dados = ConexaoMysql::getLinha($resp);
                    $matriz[$dados['IPR_ID_INSCRICAO']][$colAtual] = $dados['dsPolo'];
                }
                $cabecalho [] = 'Polo';
                $colAtual++;
            }

// caso de admitir reserva de vaga
            if ($chamada->admiteReservaVagaObj()) {
// gerando sql de recuperar reservas de vaga
                $sql = "select 
ipr.IPR_ID_INSCRICAO,
RVG_NM_RESERVA_VAGA
from
tb_ipr_inscricao_processo ipr
left join
tb_rvc_reserva_vaga_chamada rvc on rvc.RVC_ID_RESERVA_CHAMADA = ipr.RVC_ID_RESERVA_CHAMADA
left join 
tb_rvg_reserva_vaga rvg on rvc.RVG_ID_RESERVA_VAGA = rvg.RVG_ID_RESERVA_VAGA
where ipr.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
order by IPR_NR_ORDEM_INSC";

                $resp = $conexao->execSqlComRetorno($sql);

// inserindo na matriz
                for ($i = 0; $i < $numInscritos; $i++) {
                    $dados = ConexaoMysql::getLinha($resp);
                    $dsReserva = Util::vazioNulo($dados['RVG_NM_RESERVA_VAGA']) ? ReservaVagaChamada::$DS_PUBLICO_GERAL : $dados['RVG_NM_RESERVA_VAGA'];
                    $matriz[$dados['IPR_ID_INSCRICAO']][$colAtual] = $dsReserva;
                }
                $cabecalho [] = 'Reserva de vaga';
                $colAtual++;
            }

// recuperando grupos do processo   
            $grupos = GrupoAnexoProc::buscarGrupoPorProcesso($chamada->getPRC_ID_PROCESSO());
            if ($grupos != NULL) {
// processando
                foreach ($grupos as $grupo) {
// cabeçalho com nome do grupo
                    $cabecalho [] = $grupo->getGAP_NM_GRUPO();

// @todo Melhorar exportação de respostas
// 
// recuperando respostas
                    $vetRespostas = RespAnexoProc::buscarRespPorProcChamadaGrupo($chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), $grupo->getGAP_ID_GRUPO_PROC(), NULL, TRUE);

// incluindo na matriz de respostas
                    $matriz = self::preencheColunaMatrizResp($colAtual, $matriz, $vetRespostas);

// incrementando coluna
                    $colAtual++;
                }
            } // fim do grupo != NULL
//
//
//gerando e retornando
            return matrizParaCSV($cabecalho, $matriz);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao gerar arquivo CSV Geral dos candidatos inscritos no processo.", $e);
        }
    }

    /**
     * 
     * @param int $coluna Coluna a ser preenchida
     * @param array $matriz - Matriz que, em cada linha tem dados de uma inscricao, indexada
     * pelo id da inscriçao
     * @param array $somaNotas - Vetor com o somatorio de notas indexado pelo id da inscriçao.
     */
    private static function preencheColunaMatrizNota($coluna, $matriz, $somaNotas) {
// percorrendo matriz
        foreach (array_keys($matriz) as $idInscricao) {
// recuperando nota na categoria
            $nota = RelNotasInsc::getNotaSemNota();

            if (isset($somaNotas[$idInscricao])) {
                $nota = RelNotasInsc::formataNota($somaNotas[$idInscricao]);
            }

// incluindo nota na matriz
            $matriz[$idInscricao][$coluna] = $nota;
        }

        return $matriz;
    }

    /**
     * 
     * @param int $coluna Coluna a ser preenchida
     * @param array $matriz - Matriz que, em cada linha tem dados de uma inscricao, indexada
     * pelo id da inscriçao
     * @param array $respostas - Vetor com as respostas indexado pelo id da inscriçao.
     */
    private static function preencheColunaMatrizResp($coluna, $matriz, $respostas) {
// percorrendo matriz
        foreach (array_keys($matriz) as $idInscricao) {
// recuperando resposta padrão
            $resp = RespAnexoProc::getStrSemResposta();

            if (isset($respostas[$idInscricao])) {
                $resp = $respostas[$idInscricao];
            }

// incluindo nota na matriz
            $matriz[$idInscricao][$coluna] = $resp;
        }

        return $matriz;
    }

    /**
     * Funçao que retorna o codigo verificador do comprovante de inscriçao
     * para confirmaçao de autenticidade
     * @return string
     * 
     */
    public function getVerificadorCompInsc() {
        if (!Util::vazioNulo($this->IPR_COD_COMP_INSC)) {
            return InscricaoProcesso::formataVerificadorCompInsc($this->IPR_COD_COMP_INSC);
        }

// gerando verificador
        $ret = $this->IPR_ID_INSCRICAO . $this->CDT_ID_CANDIDATO . $this->PRC_ID_PROCESSO . $this->PCH_ID_CHAMADA;
        $cod = md5($ret);
// salvando codigo no BD e retornando
        $this->salvarVerificadorCompInsc($cod);
        return InscricaoProcesso::formataVerificadorCompInsc($cod);
    }

    public static function formataVerificadorCompInsc($cod) {
// inserindo pontos e caixa alta para melhor visualizaçao
        return wordwrap(mb_strtoupper($cod), 8, ".", TRUE);
    }

    public static function desformataVerificadorCompInsc($cod) {
// removendo pontos e caixa alta
        return str_replace(".", "", strtolower($cod));
    }

    private function salvarVerificadorCompInsc($cod) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "update tb_ipr_inscricao_processo
set IPR_COD_COMP_INSC = '$cod'
where IPR_ID_INSCRICAO = '{$this->IPR_ID_INSCRICAO}'";

//executando sql
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao salvar verficador de autenticidade.", $e);
        }
    }

    /**
     * Exclui uma inscriçao e envia email ao candidato informando a exclusao
     * 
     * @param int $idInscricao
     * @param string $dsMotivo
     * @throws NegocioException
     */
    public static function excluirInscricaoProc($idInscricao, $dsMotivo) {
        try {
// recuperando inscricao a ser excluida
            $objInsc = InscricaoProcesso::buscarInscricaoPorId($idInscricao);

//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $idResponsavel = getIdUsuarioLogado();

// criando objeto de historico
            $hist = new HistoricoInscExc(NULL, $objInsc->CDT_ID_CANDIDATO, $objInsc->PRC_ID_PROCESSO, $objInsc->PCH_ID_CHAMADA, $objInsc->IPR_ID_INSCRICAO, $objInsc->IPR_DT_INSCRICAO, $objInsc->IPR_NR_ORDEM_INSC, $dsMotivo, NULL, $idResponsavel);

//montando sqls
            $sqls [] = $hist->getSqlInsercaoHistInscExc();
            $sqls [] = RespAnexoProc::getStrSqlExclusaoPorInscricao($idInscricao);
            $sqls [] = PoloInscricao::getStrSqlExclusaoPorInscricao($idInscricao);
            $sqls [] = RelNotasInsc::getStrSqlExclusaoPorInscricao($idInscricao);
            $sqls [] = NotasEtapaSelInsc::getStrSqlExclusaoPorInscricao($idInscricao);

// incluindo sql de exclusao da inscricao
            $sqls [] = "delete from tb_ipr_inscricao_processo where IPR_ID_INSCRICAO = $idInscricao";

// marcando etapa de seleção como avaliação pendente
            $sqls [] = EtapaSelProc::getStrSqlClassifPenPorChamada($objInsc->getPCH_ID_CHAMADA());

//executando sql
            $conexao->execTransacaoArray($sqls);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir inscrição.", $e);
        }

//enviando email de notificação
        $objInsc->enviarEmailExcInsc($dsMotivo);
    }

    public static function preencheuTodaNotaManual($idChamada, $idProcesso, $nrEtapa) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

// parametros
            $itemManual = CategoriaAvalProc::$AVAL_MANUAL;
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;

// montando sql
            $sql = "select 
count(*) as cont
from
tb_ipr_inscricao_processo ipr
left join
tb_rni_rel_notas_insc rni ON ipr.IPR_ID_INSCRICAO = rni.IPR_ID_INSCRICAO
where
pch_id_chamada = '$idChamada'
and prc_id_processo = '$idProcesso'
and (ipr_st_inscricao IS NULL or  ipr_st_inscricao = '$inscOk')
and (select 
count(*) as cont
from
tb_iap_item_aval_proc iap
join
tb_cap_categoria_aval_proc cap ON cap.CAP_ID_CATEGORIA_AVAL = iap.CAP_ID_CATEGORIA_AVAL
join
tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
where
iap.prc_id_processo = ipr.prc_id_processo
and CAP_TP_AVALIACAO = '$itemManual'
and EAP_NR_ETAPA_AVAL = '$nrEtapa'
and not exists( select 
*
from
tb_rni_rel_notas_insc rni
where
iap.IAP_ID_ITEM_AVAL = rni.IAP_ID_ITEM_AVAL
and rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO)) > 0";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//retornando se todos estao ok
// Quando é zero, significa que todos os itens manuais foram preenchidos
            return $conexao->getResult("cont", $resp) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar preenchimento de notas manuais.", $e);
        }
    }

    /**
     * Note: Essa funçao considera a ultima chamada vigente
     * 
     * @param int $idUsuario
     * @param int $idProcesso
     * @return int
     * @throws NegocioException
     */
    public static function contaInscProcUltChamada($idUsuario, $idProcesso) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $flagSim = FLAG_BD_SIM;

            $sql = "select count(*) as cont
from tb_ipr_inscricao_processo
where CDT_ID_CANDIDATO = 
(select CDT_ID_CANDIDATO from 
tb_cdt_candidato where `USR_ID_USUARIO` = '$idUsuario')
and `PCH_ID_CHAMADA` = 
(select PCH_ID_CHAMADA from
tb_pch_processo_chamada where PRC_ID_PROCESSO = '$idProcesso'
and PCH_CHAMADA_ATUAL = '$flagSim')";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições do usuário.", $e);
        }
    }

    /**
     * Note: Essa funçao considera a ultima chamada vigente.
     * 
     * @param int $idUsuario
     * @param int $idProcesso
     * @return int
     * @throws NegocioException
     */
    public static function getIdInscProcUltChamada($idUsuario, $idProcesso) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $flagSim = FLAG_BD_SIM;

            $sql = "select ipr_id_inscricao
from tb_ipr_inscricao_processo
where CDT_ID_CANDIDATO = 
(select CDT_ID_CANDIDATO from 
tb_cdt_candidato where `USR_ID_USUARIO` = '$idUsuario')
and `PCH_ID_CHAMADA` = 
(select PCH_ID_CHAMADA from
tb_pch_processo_chamada where PRC_ID_PROCESSO = '$idProcesso'
and PCH_CHAMADA_ATUAL = '$flagSim')";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

// retornou nada?
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return NULL;
            }

//retornando
            return $conexao->getResult("ipr_id_inscricao", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar inscrição do usuário.", $e);
        }
    }

    /**
     * Esta funçao executa a avaliação automática dos candidatos, após verificar todas as 
     * restriçoes.
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @throws NegocioException
     */
    public static function executarAvaliacaoAutomatica($idProcesso, $idChamada) {
        try {
// falta de parametros
            if (Util::vazioNulo($idProcesso) || Util::vazioNulo($idChamada)) {
                throw new NegocioException("Parâmetros de avaliação automática incorretos.");
            }

// validando se é possivel executar avaliação automática
            $temp = EtapaSelProc::validarExecAvalAutomatica($idChamada);
            if (!$temp['val']) {
// disparando exceçao com erro
                throw new NegocioException($temp['msg']);
            }

// Tudo correto. Iniciar processo. 
// 
// 
// recuperando etapa de classificacao
            $etapa = EtapaSelProc::buscarEtapaEmAndamento($idChamada);

// verificando se existe categoria de avaliacao automatica
            $qtAvalAuto = CategoriaAvalProc::contarCatAvalPorProcNrEtapaTp($idProcesso, $etapa->getESP_NR_ETAPA_SEL(), CategoriaAvalProc::$AVAL_AUTOMATICA);

// caso existem categorias automaticas...
            if ($qtAvalAuto > 0) {

// Verificando se existe inscriçao com avaliacao automatica por fazer
                $listaCands = self::CLAS_getInscricoesAvalAutomatica($idChamada);
                if ($listaCands != NULL) {
// executando a avaliação automática dos candidatos...
                    $etapa->CLAS_execAvalAutomaticaCand($idProcesso, $listaCands);
                }
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao executar avaliação automática dos candidatos.", $e);
        }
    }

    /**
     * Essa funçao executa a classificaçao dos candidatos, verificando todas as 
     * restriçoes.
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @throws NegocioException
     */
    public static function classificarCandidatos($idProcesso, $idChamada) {
        try {
// falta de parametros
            if (Util::vazioNulo($idProcesso) || Util::vazioNulo($idChamada)) {
                throw new NegocioException("Parâmetros de classificação incorretos.");
            }

// validando se e possivel classificar
            $temp = EtapaSelProc::validarClassifCands($idChamada);
            if (!$temp['val']) {
// disparando exceçao com erro
                throw new NegocioException($temp['msg']);
            }

// Tudo correto. Iniciar processo. 
// 
// 
// recuperando etapa de classificacao
            $etapa = EtapaSelProc::buscarEtapaEmAndamento($idChamada);


// Classificacao e selecao dos candidatos
            self::CLAS_classificarSelecionar($etapa);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao classificar candidatos.", $e);
        }
    }

    public static function registrarAvalEtapaCdt($idProcesso, $idChamada, $idInscricao, $relIgnorar, $itemIgnorar, $relExclusivo, $arrayNotaMan, $arrayJustMan, $arrayNotaItemMan, $justEliminacao) {
        try {

// recuperando primeiro elemento do itemIgnorar
//@todo Mexer aqui, caso seja necessario implementar mais de uma categoria exclusiva por etapa
            $ignorarItemAte = $itemIgnorar == NULL ? NULL : $itemIgnorar[0];

//recuperando conexao com BD
            $conexao = NGUtil::getConexao();
            $vetSql = array();

// falta de parametros
            if (Util::vazioNulo($idProcesso) || Util::vazioNulo($idChamada) || Util::vazioNulo($idInscricao)) {
                throw new NegocioException("Parâmetros de registro de avaliação incorretos.");
            }

// validando se é possivel registrar avaliaçao
            $inscricao = InscricaoProcesso::buscarInscricaoPorId($idInscricao);
            if (!$inscricao->avalAutoConcluida()) {
                throw new NegocioException("Você não pode gerenciar a nota deste candidato porque a avaliação automática não está concluída.");
            }

// recuperando etapa de classificacao
            $etapa = EtapaSelProc::buscarEtapaEmAndamento($idChamada);

// caso de nao existir etapas de avaliaçao
            if (Util::vazioNulo($etapa)) {
                throw new NegocioException("Não existem etapas de avaliação em andamento.");
            }

// pode editar a nota?
            if (!$inscricao->permiteEditarNotaTab($etapa)) {
                throw new NegocioException("Você não pode editar as notas deste candidato.");
            }

// ativando todos relatorios da etapa
            $vetSql [] = RelNotasInsc::getStrSqlAtivarPorInsc($idInscricao, $inscricao->PRC_ID_PROCESSO, $etapa->getESP_NR_ETAPA_SEL());

// recuperando sql para ignorar relatorios
            if ($relIgnorar != NULL) {
// ignorando quem pediu
                $vetSql [] = RelNotasInsc::getStrSqlIgnorarRelPorIds($relIgnorar);
            }

// buscar categorias da etapa
            $categorias = CategoriaAvalProc::buscarCatAvalPorProcEtapaTp($idProcesso, $etapa->getESP_NR_ETAPA_SEL(), NULL, FALSE);

// percorrendo categorias para reavaliar limites e analisar casos manuais
            foreach ($categorias as $categoria) {

// caso de categoria de avaliaçao automatica
                if ($categoria->isAvalAutomatica()) {

// flags importantes
                    $terminouIgnorado = FALSE;  // informa se os itens ignorados de uma categoria exclusiva terminaram ou nao
                    $inseriuNaoIgnorado = TRUE; // informa que foi inserido um item não ignorado no relatório exclusivo
//
//
// caso nao implementado
// @todo Mexer aqui caso necessite implementar categoria exclusiva para categorias diferentes de titulação
                    if ($categoria->isCategoriaExclusiva() && CategoriaAvalProc::$TIPO_TITULACAO != $categoria->getCAP_TP_CATEGORIA()) {
                        throw new NegocioException("Função não implementada em 'registrarAvalEtapaCdt'.");
                    }

// tratando caso da categoria exclusiva
                    if ($categoria->isCategoriaExclusiva() && CategoriaAvalProc::$TIPO_TITULACAO == $categoria->getCAP_TP_CATEGORIA()) {

// tentando recuperar nota do candidato
                        $relItem = RelNotasInsc::buscarRelNotasPorInscCatItem($idChamada, $idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL(), NULL, NULL, TRUE, RelNotasInsc::$TP_AVAL_AUTOMATICA);
                        if ($relItem != NULL && count($relItem) > 1) {
                            throw new NegocioException("Inconsistência ao obter nota do Item ao registrar nota. Por favor, informe esta ocorrência ao administrador do sistema.");
                        }

                        $vetNaoRemover = array(); // armazena avaliaçoes manuais (itens fantasmas) que NAO devem ser removidas
//
// possui relatorio de notas
                        if ($relItem != NULL) {

// eliminando inconsistencia
                            if ($relExclusivo != NULL && $relExclusivo != $relItem[0]->getRNI_ID_REL_NOTA()) {
                                throw new NegocioException("Inconsistência ao tratar categoria exclusiva em 'registrarAvalEtapaCdt'.");
                            }

// verificando se o relatorio automatico esta ignorado
                            if ($relExclusivo != NULL || ($relExclusivo == NULL && $ignorarItemAte != NULL)) {

// marcando relatorio automatico como ignorado
                                $vetSql [] = $relItem[0]->getSqlAlterarSituacao(RelNotasInsc::$SIT_IGNORADA);
                                $inseriuNaoIgnorado = FALSE;

// buscar item avaliado
                                $itemAval = ItemAvalProc::buscarItemAvalPorId($relItem[0]->getIAP_ID_ITEM_AVAL());

// buscando itens maiores que o item avaliado
                                $itensMaiores = ItemAvalProc::buscarItensAvalPorCat($idProcesso, $categoria->getCAP_ID_CATEGORIA_AVAL(), $itemAval->getIAP_ORDEM());

// pegando apenas lista dos maiores que 'casam' com o candidato
                                $itensMaiores = ItemAvalProc::verifica_casamento_itens($itensMaiores, $categoria, $inscricao->getCDT_ID_CANDIDATO());

// percorrendo itens maiores ate inserir um item valido
                                $cont = 0;
                                do {
// caso nao tenha itens maiores: Saindo...
                                    if (count($itensMaiores) < 1) {
                                        break;
                                    }

// preparando dados
                                    $itemTemp = $itensMaiores[$cont];

                                    $ignorado = $ignorarItemAte != NULL && !$terminouIgnorado;
                                    $obsRel = $ignorado ? "Titulação ignorada pelo usuário." : "Titulação homologada pelo usuário.";
                                    $stRelatorio = $ignorado ? RelNotasInsc::$SIT_IGNORADA : RelNotasInsc::$SIT_ATIVA;

// adicionando sql de validacao do item fantasma e marcando como nao destrutivel
                                    $vetSql [] = $itemTemp->get_sql_rel_item_fan_exc($inscricao, $categoria, $obsRel, $stRelatorio);
                                    $vetNaoRemover [] = $itemTemp->getIAP_ID_ITEM_AVAL();

// saindo caso inseriu alguem nao ignorado
                                    if (!$ignorado) {
                                        $inseriuNaoIgnorado = TRUE;
                                        break;
                                    }

// definindo se terminou ignorado
                                    $terminouIgnorado = $ignorarItemAte != NULL && $ignorarItemAte == $itemTemp->getIAP_ID_ITEM_AVAL();

                                    $cont++;
                                } while ($cont < count($itensMaiores));
                            } else {// rel automatico nao esta ignorado
// nada a fazer
                            }
                        }// fim possui relatório de notas
                        else {
// marcar que não tem nenhum item não ignorado
                            $inseriuNaoIgnorado = FALSE;
                        }
// gerando sql para remover possiveis relatorios anteriores
                        $vetSql [] = RelNotasInsc::getStrSqlExclusaoPorInscCatExcMan($idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL(), $vetNaoRemover);
//
//
// tratando caso de item manual em avaliacao automatica
                        $temp = self::getSqlItemManualCatAuto($inscricao, $categoria, $arrayNotaMan[$categoria->getIdNotaManualCatAuto()], $arrayJustMan[$categoria->getIdJustManualCatAuto()], !$inseriuNaoIgnorado);
                        if ($temp != NULL) {
                            $vetSql [] = $temp;
                        }
// fim categoria exclusiva
                    } else {
//
//
// tratando caso de item manual em avaliacao automatica
                        $temp = self::getSqlItemManualCatAuto($inscricao, $categoria, $arrayNotaMan[$categoria->getIdNotaManualCatAuto()], $arrayJustMan[$categoria->getIdJustManualCatAuto()]);
                        if ($temp != NULL) {
                            $vetSql [] = $temp;
                        }

// refazendo calculo de limites da categoria
// 
// removendo limites anteriores
                        $vetSql [] = RelNotasInsc::CLAS_getSqlExclusaoAjuste($idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL());

// recuperando itens da categoria para analisar subgrupos
                        $itensCat = ItemAvalProc::buscarItensAvalPorCat($idProcesso, $categoria->getCAP_ID_CATEGORIA_AVAL());

// recuperando matriz de limites
                        $matLimites = InscricaoProcesso::_soma_subgrupos($itensCat, $categoria, $idChamada, $idInscricao, $relIgnorar, $arrayNotaMan[$categoria->getIdNotaManualCatAuto()]);

// tratando limite dos grupos
                        $grupos = array_keys($matLimites[1]);
                        $totalCat = 0;
                        for ($i = 0; $i < count($grupos); $i++) {

// tem que limitar?
                            if ($matLimites[0][$grupos[$i]] > $matLimites[1][$grupos[$i]]) {
                                $msg = "Ajustando para o limite máximo do grupo $grupos[$i].";
                                $vetSql [] = ItemAvalProc::get_sql_ajuste_cat_st($idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL(), CategoriaAvalProc::getDsTipo($categoria->getCAP_TP_CATEGORIA()), ($matLimites[0][$grupos[$i]] - $matLimites[1][$grupos[$i]]), $msg);
                            }
                            $totalCat += min(array($matLimites[0][$grupos[$i]], $matLimites[1][$grupos[$i]]));
                        }

// tratando limites da categoria
                        $totalCat += $matLimites[0]['semGrupo'];
                        if ($totalCat > $categoria->getCAP_VL_PONTUACAO_MAX()) {
                            $vetSql [] = ItemAvalProc::get_sql_ajuste_cat_st($idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL(), CategoriaAvalProc::getDsTipo($categoria->getCAP_TP_CATEGORIA()), ($totalCat - $categoria->getCAP_VL_PONTUACAO_MAX()));
                        }


//                    print_r($totalCat);
//                    print_r("<br/>");
//                    print_r($matLimites);
//                    print_r("<br/><br/>");
//
                    } // fim de nao eh categoria exclusiva
//
//
                } // fim de aval automatica 
// 
// Início de nota manual
                elseif ($categoria->isAvalManual()) {
// recuperando itens para registrar avaliacao
                    $itensCat = ItemAvalProc::buscarItensAvalPorCat($idProcesso, $categoria->getCAP_ID_CATEGORIA_AVAL());

// criando nota de controle
                    $somaCat = 0;

// percorrendo itens para criar sql de nota
                    foreach ($itensCat as $item) {

                        if (!Util::vazioNulo($arrayNotaItemMan[$item->getIdCheckBoxGerencia()])) {
// gerando sql
                            $vetSql [] = $item->get_sql_rel_item_man($inscricao, $categoria, $arrayNotaItemMan[$item->getIdCheckBoxGerencia()]);

// Se o item é de informação complementar, é necessário verificar se já foi respondido via avaliação cega
                            if ($item->getIAP_TP_ITEM() == ItemAvalProc::$TP_INF_COMP) {
                                $item->add_sql_registra_nota_inicial_inf_comp($inscricao, $arrayNotaItemMan[$item->getIdCheckBoxGerencia()], $vetSql);
                            }

// agregando valor na soma
                            $somaCat += $item->normalizarNota($arrayNotaItemMan[$item->getIdCheckBoxGerencia()]);
                        } else {
// Nota manual não foi preenchida
//                            print_r("Tem nota manual sem prenchimento!");
// sql para remover item
                            $vetSql [] = RelNotasInsc::getStrSqlExcPorInscCatItem($inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), $item->getIAP_ID_ITEM_AVAL());
                        }
                    }

// removendo ajustes anteriores
                    $vetSql [] = RelNotasInsc::CLAS_getSqlExclusaoAjuste($idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL());

// verificando necessidade de criar ajuste
                    if ($somaCat > $categoria->getCAP_VL_PONTUACAO_MAX()) {
                        $vetSql [] = ItemAvalProc::get_sql_ajuste_cat_st($idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL(), $categoria->getNomeCategoria(), ($somaCat - $categoria->getCAP_VL_PONTUACAO_MAX()));
                    }
                } else {
                    throw new NegocioException("Tipo de avaliação desconhecido ao registrar nota!");
                }
            } // fim de analise das categorias
//
//
// atualizando nota
            InscricaoProcesso::getSqlAtualizarNota($idInscricao, getIdUsuarioLogado(), $etapa->getESP_ID_ETAPA_SEL(), $etapa->getEAP_ID_ETAPA_AVAL_PROC(), $vetSql);

// atualizando caso de eliminado
            $vetSql [] = InscricaoProcesso::getSqlEliminacaoCandidato($inscricao, $justEliminacao);

// alterando flags de controle
            $vetSql [] = EtapaSelProc::getStrSqlClassifPenPorEtSel($etapa->getESP_ID_ETAPA_SEL());
            $vetSql [] = InscricaoProcesso::getSqlAlterarAnalise($idInscricao, InscricaoProcesso::$SIT_ANALISE_CONCLUIDA);

//            print_r($vetSql);
//            exit;
// executando no bd
            $conexao->execTransacaoArray($vetSql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw

            new NegocioException("Erro ao atualizar nota do candidato.", $e);
        }
    }

    /**
     * 
     * @param InscricaoProcesso $inscricao
     * @param CategoriaAvalProc $categoria
     * @param string $nota
     * @param string $justificativa
     * @param boolean $permiteExclusiva Flag que informa se é permitido item manual na categoria automática exclusiva
     * @return string
     */
    private static function getSqlItemManualCatAuto($inscricao, $categoria, $nota, $justificativa, $permiteExclusiva = FALSE) {

// tratando flag da categoria exclusiva
        $nota = ($categoria->isCategoriaExclusiva() && !$permiteExclusiva) ? 0 : $nota;

// recuperando sql 
        $ret = CategoriaAvalProc::get_sql_manual_cat_auto($inscricao->IPR_ID_INSCRICAO, $categoria, $nota, $justificativa);

        return $ret;
    }

    private static function getSqlAlterarAnalise($idInscricao, $stAnalise) {
        return "update tb_ipr_inscricao_processo set IPR_ST_ANALISE = '$stAnalise' where 
IPR_ID_INSCRICAO = '$idInscricao'";
    }

    /**
     * Esta função retorna a sql responsável por limpar a análise humana das inscrições de uma dada chamada
     * 
     * @param int $idChamada
     * @param boolean $apenasSitAutoPen Diz se é para restringir a atualização às inscrições com avaliação automática pendente. Padrão: FALSE
     * @return string Sql que executa a tarefa no BD
     */
    public static function getSqlLimparAnaliseHumPorCham($idChamada, $apenasSitAutoPen = FALSE) {
        $inscOk = self::$SIT_INSC_OK;
        $ret = "update tb_ipr_inscricao_processo set IPR_ST_ANALISE = NULL where PCH_ID_CHAMADA = '$idChamada' and
(IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')";

        if ($apenasSitAutoPen) {
// restringindo atualização apenas às avaliações automáticas não concluídas
            $avalPen = self::$SIT_AVAL_AUTO_PENDENTE;
            $ret .= " and (IPR_ST_AVAL_AUTOMATICA IS NULL
or IPR_ST_AVAL_AUTOMATICA = '$avalPen') ";
        }
        return $ret;
    }

    /**
     * 
     * @param int $idChamada
     * @param int $idEtapaSel
     * @param boolean $apenasSitAutoPen Diz se é para restringir a atualização às inscrições com avaliação automática pendente. Padrão: FALSE
     * @return string Sql que executa a tarefa no BD
     */
    public static function getSqlLimparElimAutoPorChamEtapa($idChamada, $idEtapaSel, $apenasSitAutoPen = FALSE) {
        $stInscElim = InscricaoProcesso::$SIT_INSC_ELIMINADO;
        $stInscOk = InscricaoProcesso::$SIT_INSC_OK;
        $ret = "update tb_ipr_inscricao_processo set IPR_ST_INSCRICAO = '$stInscOk',
IPR_DS_OBS_NOTA = NULL where PCH_ID_CHAMADA = '$idChamada'
and (IPR_ST_INSCRICAO != '$stInscOk' and IPR_ST_INSCRICAO != '$stInscElim')
and (IPR_ID_ETAPA_SEL_NOTA IS NULL or IPR_ID_ETAPA_SEL_NOTA = '$idEtapaSel')";

        if ($apenasSitAutoPen) {
// restringindo atualização apenas às avaliações automáticas não concluídas
            $avalPen = self::$SIT_AVAL_AUTO_PENDENTE;
            $ret .= " and (IPR_ST_AVAL_AUTOMATICA IS NULL
or IPR_ST_AVAL_AUTOMATICA = '$avalPen')";
        }
        return $ret;
    }

    private static function getSqlAtualizarNota($idInscricao, $idUsuResp, $idEtapaSel, $idEtapaAval, &$vetSql) {
// gerando sql de sumarizacao de notas
// OBS: Existem alguns pontos de SQL SIMILIAR que devem ser alterados, caso haja mudanças aqui
// Pesquisa: #sqlNotas
        $avalAtiva = RelNotasInsc::$SIT_ATIVA;
        $anal = InscricaoProcesso::$SIT_ANALISE_CONCLUIDA;
        $vetSql [] = "update tb_ipr_inscricao_processo ipr 
set 
ipr_vl_total_nota = coalesce((select 
sum(RNI_VL_NOTA_NORMALIZADA)
from
tb_rni_rel_notas_insc rni
where
ipr.IPR_ID_INSCRICAO = rni.IPR_ID_INSCRICAO
and RNI_ST_AVALIACAO = '$avalAtiva'
and CAP_ID_CATEGORIA_AVAL IN 
(select CAP_ID_CATEGORIA_AVAL from tb_cap_categoria_aval_proc 
where EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval')), 0),
ipr_id_usr_avaliador = '$idUsuResp',
ipr_st_analise = '$anal',
ipr_dt_avaliacao = now(),
IPR_ID_ETAPA_SEL_NOTA = '$idEtapaSel'
where
IPR_ID_INSCRICAO = '$idInscricao'";
    }

    /**
     * 
     * @param InscricaoProcesso $inscricao
     * @param string $justEliminacao
     * @return string SQL responsável por processar a eliminação do candidato
     */
    private static function getSqlEliminacaoCandidato($inscricao, $justEliminacao) {

// Eliminação manual nos casos:
// 1- Candidato não estava eliminado
// 2- Candidato já estava eliminado manualmente
// 3- Candidato estava eliminado de outra forma, mas o texto de eliminação foi modificado.
// OBS: Caso de eliminação automática é removida logo no ato da atualização da nota
        $elimManual = Util::vazioNulo($inscricao->IPR_ST_INSCRICAO) || $inscricao->IPR_ST_INSCRICAO == InscricaoProcesso::$SIT_INSC_ELIMINADO || $inscricao->IPR_DS_OBS_NOTA != $justEliminacao;

// caso de eliminacao ser nula ou não ter eliminação manual
        if (Util::vazioNulo($justEliminacao) || !$elimManual) {
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            return "update tb_ipr_inscricao_processo ipr 
set 
ipr_st_inscricao = '$inscOk',
ipr_ds_obs_nota = NULL
where
ipr.IPR_ID_INSCRICAO = '$inscricao->IPR_ID_INSCRICAO'";
        } else {

// caso de ser eliminado
            $inscEliminado = InscricaoProcesso::$SIT_INSC_ELIMINADO;
            return "update tb_ipr_inscricao_processo ipr 
set 
ipr_st_inscricao = '$inscEliminado',
ipr_ds_obs_nota = '$justEliminacao'
where
IPR_ID_INSCRICAO = '$inscricao->IPR_ID_INSCRICAO'";
        }
    }

    /**
     * Retorna uma matriz, contendo:
     * 
     * 1 - Um array indexado pelo numero do grupo com 
     * somatorio das notas do grupo. Possui um indice
     * 'semGrupo' que armazena as notas de quem nao tem subgrupo.
     * 
     * 2 - Um array indexado pelo numero do grupo contendo o limite
     * de cada grupo
     * 
     * @param ItemAvalProc $itensCat - Array de itens
     * @param CategoriaAvalProc $categoria
     * @param  int $idChamada
     * @param int $idInscricao
     * @param array $relIgnorar
     * @param string $notaManual
     */
    private static function _soma_subgrupos($itensCat, $categoria, $idChamada, $idInscricao, $relIgnorar, $notaManual) {
        $soma = array();
        $limites = array();
        $soma['semGrupo'] = floatval(min(array($categoria->getCAP_VL_PONTUACAO_MAX(), $notaManual)));

        foreach ($itensCat as $item) {
// recuperando relatorios do item
            $relatorios = RelNotasInsc:: buscarNotasPorInscCatItem($idChamada, $idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL(), $item->getIAP_ID_ITEM_AVAL());

// removendo itens atualmente ignorados
            if ($relIgnorar != NULL) {
                foreach ($relIgnorar as $rel) {
                    if (isset($relatorios[$rel])) {
                        unset($relatorios[$rel]);
                    }
                }
            }

// inserindo no vetor
            if ($item->getIAP_ID_SUBGRUPO() != NULL) {
                if (isset($limites[$item->getIAP_ID_SUBGRUPO()])) {
                    $soma[$item->getIAP_ID_SUBGRUPO()] += $relatorios != NULL ? array_sum($relatorios) : 0;
                } else {
                    $soma[$item->getIAP_ID_SUBGRUPO()] = $relatorios != NULL ? array_sum($relatorios) : 0;
                    $limites[$item->getIAP_ID_SUBGRUPO()] = $item->getIAP_VAL_PONTUACAO_MAX();
                }
            } else {
                $soma['semGrupo'] += $relatorios != NULL ? array_sum($relatorios) : 0;
            }
        }

        return array($soma, $limites);
    }

    /**
     * Funcao que recupera as inscriçoes a serem avaliadas automaticamente.
     * NAO DEVE SER USADA PARA OUTROS FINS A NÃO SER O DE CLASSIFICAÇÃO!
     * @param int $idChamada
     * @return array - Array do tipo (IPR_ID_INSCRICAO => CDT_ID_CANDIDATO)
     * @throws NegocioException
     */
    private static function CLAS_getInscricoesAvalAutomatica($idChamada) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $avalPen = self::$SIT_AVAL_AUTO_PENDENTE;

            $sql = "select 
IPR_ID_INSCRICAO, CDT_ID_CANDIDATO
from
tb_ipr_inscricao_processo
where
pch_id_chamada = '$idChamada'
and (IPR_ST_AVAL_AUTOMATICA IS NULL
or IPR_ST_AVAL_AUTOMATICA = '$avalPen')";

// executando query
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
                $dados = ConexaoMysql::getLinha($resp);

// criando estrutura
                $vetRetorno[$dados['IPR_ID_INSCRICAO']] = $dados['CDT_ID_CANDIDATO'];
            }

// retornando
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar inscrições a avaliar.", $e);
        }
    }

    /**
     * Funcao que conta as inscriçoes a serem avaliadas automaticamente.
     * NAO DEVE SER USADA PARA OUTROS FINS A NÃO SER O DE CLASSIFICAÇÃO!
     * @param int $idChamada
     * @return int
     * @throws NegocioException
     */
    private static function CLAS_contaInscricoesAvalAutomatica($idChamada) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $avalPen = self::$SIT_AVAL_AUTO_PENDENTE;

            $sql = "select 
count(*) as cont
from
tb_ipr_inscricao_processo
where
pch_id_chamada = '$idChamada'
and (IPR_ST_AVAL_AUTOMATICA IS NULL
or IPR_ST_AVAL_AUTOMATICA = '$avalPen')";

// executando query
            $resp = $conexao->execSqlComRetorno($sql);

// retornando
            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições a avaliar.", $e);
        }
    }

    /**
     * 
     * @param EtapaSelProc $etapa
     */
    public static function existeAvalAutoAFazer($etapa) {

// verificando se existe categoria de avaliacao automatica
        $qtAvalAuto = CategoriaAvalProc::contarCatAvalPorProcNrEtapaTp($etapa->getPRC_ID_PROCESSO(), $etapa->getESP_NR_ETAPA_SEL(), CategoriaAvalProc::$AVAL_AUTOMATICA);

        if ($qtAvalAuto > 0) {
// verificando se existe avaliação automática a fazer
            $qtAvaliar = self::CLAS_contaInscricoesAvalAutomatica($etapa->getPCH_ID_CHAMADA());
        }

// retornando: Tem avaliação automática e tem gente para avaliar
        return $qtAvalAuto > 0 && $qtAvaliar > 0;
    }

    /**
     * Esta função registra a avaliaçao das informações complementares da inscrição.
     * 
     * Atenção: Idependente da flag $avalCega, esta função SEMPRE persiste que a avaliação cega já foi executada. 
     * 
     * Caso a flag $avalCega seja FALSE, então esta função retorna um array com as sqls a serem executadas no BD;
     * caso contrário, ela mesma realiza a persistência.
     * 
     * @param array $matInfComp - Matriz de informação complementar com a nota dos itens
     * @param char $domicilioProximo
     * @param boolean $avalCega Informa se o registro advém de uma avaliação cega. Padrão: TRUE
     * @throws NegocioException
     */
    public function registrarAvalInfComp($matInfComp, $domicilioProximo, $avalCega = TRUE) {
        try {

            if ($avalCega) {
// validando
                if ($this->isAvaliadaCegamente()) {
                    throw new NegocioException("Avaliação cega já registrada!");
                }
            }

// array de sqls
            $arraySqls = array();

// caso de informaçoes complementares
            if ($matInfComp != NULL) {

// recuperando dados importantes
                $usuLogado = getIdUsuarioLogado();
                $sqlDataHora = dt_dataHoraStrParaMysql(dt_getDataEmStr("d/m/Y H:i:s"));
                $arraySqls = RespAnexoProc::getArraySqlRegistroAvaliacao($this->IPR_ID_INSCRICAO, $matInfComp, $usuLogado, $sqlDataHora);
            }

// atualização de domicílio
            $domicilioProximo = NGUtil::trataCampoStrParaBD($domicilioProximo);
            $arraySqls [] = "update tb_ipr_inscricao_processo set
ipr_localizacao_valida = $domicilioProximo
where ipr_id_inscricao = '$this->IPR_ID_INSCRICAO'";


// validando avaliação cega
            $avalCegaConc = self::$SIT_AVAL_CEGA_CONCLUIDA;
            $arraySqls [] = "update tb_ipr_inscricao_processo set
IPR_ST_AVAL_CEGA = '$avalCegaConc'
where ipr_id_inscricao = '$this->IPR_ID_INSCRICAO'";

            if ($avalCega) {
//recuperando objeto de conexão
                $conexao = NGUtil::getConexao();
                $conexao->execTransacaoArray($arraySqls);
            } else {
                return $arraySqls;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new

            NegocioException("Erro ao registrar avaliação das informações complementares.", $e);
        }
    }

    public static function validarNrCompInscricao($nrAutenticidade, $idInscricao = NULL) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

// desformatando codigo de autenticidade
            $codAutent = InscricaoProcesso::desformataVerificadorCompInsc($nrAutenticidade);

// montando sql de validacao
            $sql = "select IPR_ID_INSCRICAO from tb_ipr_inscricao_processo
where IPR_COD_COMP_INSC = '$codAutent'";

// idInscicao?
            if ($idInscricao != NULL) {
                $sql .= " and IPR_ID_INSCRICAO = '$idInscricao'";
            }

// executando no banco
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return FALSE; // Comprovante invalido
            }

// caso de existir: Retornando Inscriçao
            return InscricaoProcesso::buscarInscricaoPorId(ConexaoMysql::getResult("IPR_ID_INSCRICAO", $resp));
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar validar comprovante de inscrição do candidato.", $e);
        }
    }

    /**
     * Assume o preenchimento correto dos dados
     * @param int $idUsuario
     * @param array $listaPolos - Lista de polos da inscriçao
     * @param int $idAreaAtu - Area de atuaçao escolhida
     * @param int $idReservaVaga - Reserva de vaga escolhida
     * @param array $matInfComp - Matriz de informaçao complementar
     * @return int - Id da inscriçao
     * @throws NegocioException
     */
    public function criarInscricaoProcesso($idUsuario, $listaPolos, $idAreaAtu, $idReservaVaga, $matInfComp) {
        try {
// verificando se ja existem etapas finalizadas
            if (EtapaSelProc::contarEtapaPorSit($this->PCH_ID_CHAMADA, EtapaSelProc::$SIT_FINALIZADA) > 0) {
                throw new NegocioException("Não é possível realizar a inscrição. Existem etapas de avaliação finalizadas.");
            }

//validando inscriçao
            InscricaoProcesso::validaInscricaoUsuario($idUsuario, $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);

// recuperando chamada e processo para executar as devidas avaliações
            $chamada = ProcessoChamada::buscarChamadaPorId($this->PCH_ID_CHAMADA, $this->PRC_ID_PROCESSO);

// trata campo area de atuacao
            if ($chamada->admiteAreaAtuacaoObj()) {
                if ($idAreaAtu == NULL) {
                    throw new NegocioException("Área de atuação não informada!");
                }
                $idAreaAtu = NGUtil::trataCampoStrParaBD(AreaAtuChamada::buscarIdAreaAtuPorChamadaArea($this->PCH_ID_CHAMADA, $idAreaAtu));
            } else {
                $idAreaAtu = NGUtil::trataCampoStrParaBD(NULL);
            }

// trata campo de reserva de vaga
            if ($chamada->admiteReservaVagaObj()) {
                if ($idReservaVaga == NULL) {
                    throw new NegocioException("Reserva de vaga não informada!");
                }
                $idReservaVaga = NGUtil::trataCampoStrParaBD(ReservaVagaChamada::buscarIdReservaChamPorReservaVaga($this->PCH_ID_CHAMADA, $idReservaVaga));
            } else {
                $idReservaVaga = NGUtil::trataCampoStrParaBD(NULL);
            }

//montando sql de criação de inscriçao
            $sql = "INSERT INTO tb_ipr_inscricao_processo (`CDT_ID_CANDIDATO`, `PRC_ID_PROCESSO`, `PCH_ID_CHAMADA`, `IPR_DT_INSCRICAO`, `IPR_NR_ORDEM_INSC`, AAC_ID_AREA_CHAMADA, RVC_ID_RESERVA_CHAMADA)
VALUES((select CDT_ID_CANDIDATO
from tb_cdt_candidato
where USR_ID_USUARIO = '$idUsuario'),'$this->PRC_ID_PROCESSO', '$this->PCH_ID_CHAMADA', NOW(),
(
SELECT COALESCE(MAX(`IPR_NR_ORDEM_INSC`),0) + 1
FROM (
SELECT *
FROM tb_ipr_inscricao_processo) AS temp
WHERE `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'), $idAreaAtu, $idReservaVaga)";

            $arrayCmds = array();


// recuperando etapa em andamento
            $etapa = EtapaSelProc:: buscarEtapaEmAndamento($this->PCH_ID_CHAMADA);

// incluindo sql de desabilitar classificacao
            if ($etapa != NULL) {
                $arrayCmds [] = EtapaSelProc::getStrSqlClassifPenPorEtSel($etapa->getESP_ID_ETAPA_SEL());
            }

// gerando sql de polos
            if ($chamada->admitePoloObj()) {
                if ($listaPolos == NULL) {
                    throw new NegocioException("Polo não informado!");
                }
                if (count($listaPolos) > $chamada->getPCH_NR_MAX_OPCAO_POLO()) {
                    throw new NegocioException("O número de polos selecionados deve ser menor ou igual a {$chamada->getPCH_NR_MAX_OPCAO_POLO()}!");
                }
                $arrayCmds = array_merge($arrayCmds, PoloInscricao::getArraySqlInsercaoInsc($listaPolos, ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE));
            }

// gerando sql de informaçoes complementares
            if ($matInfComp != NULL) {
                $arrayCmds = array_merge($arrayCmds, RespAnexoProc::getArraySqlInsercaoResp($matInfComp, $this, ConexaoMysql:: $_CARACTER_INSERCAO_DEPENDENTE));
            }

// recuperando sql de remoção de rastreio
            $arrayCmds [] = RAT_getSqlRemoverRastreioPorFiltroCT(getIdUsuarioLogado(), $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA);


//            print_r($arrayCmds);
//            exit;
//inserindo no banco
            $conexao = NGUtil::getConexao();
            $idInsc = $conexao->execTransacaoDependente($sql, $arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao criar inscrição do usuário no processo.", $e);
        }

//enviando email de notificação
        InscricaoProcesso::enviarEmailInscricao($idUsuario, $idInsc);

// retornando id da inscriçao
        return $idInsc;
    }

    public static function contaInscritosPorProcesso($idProcesso, $idPolo, $idAreaAtuacao, $idReservaVaga, $nmUsuario, $nrcpf, $idChamada, $ordem, $tpExibSituacao) {
        try {

//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
from tb_ipr_inscricao_processo ipr
join tb_cdt_candidato cdt on ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
join tb_usr_usuario usr on cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
join tb_idc_identificacao_candidato idc on cdt.IDC_ID_IDENTIFICACAO_CDT = idc.IDC_ID_IDENTIFICACAO_CDT";

//complementando sql de acordo com o filtro
            $where = true;
            $and = false;

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

//area atu chamada
            if ($idAreaAtuacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `AAC_ID_AREA_CHAMADA` = (select AAC_ID_AREA_CHAMADA from tb_aac_area_atu_chamada where PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
and ARC_ID_SUBAREA_CONH = '$idAreaAtuacao') ";
            }

// reserva de vaga
            if ($idReservaVaga != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }

                if ($idReservaVaga == ReservaVagaChamada::$ID_PUBLICO_GERAL) {
                    $sql .= " `RVC_ID_RESERVA_CHAMADA` IS NULL";
                } else {
                    $sql .= " `RVC_ID_RESERVA_CHAMADA` = (select RVC_ID_RESERVA_CHAMADA from tb_rvc_reserva_vaga_chamada where PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO and
PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA and RVG_ID_RESERVA_VAGA = '$idReservaVaga') ";
                }
            }

//id polo
            if ($idPolo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " '$idPolo' in (select POL_ID_POLO from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO) ";
            }

//nome usuário
            if ($nmUsuario != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " usr.USR_DS_NOME like '%$nmUsuario%' ";
            }

// nrcpf
            if ($nrcpf != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " IDC_NR_CPF = '$nrcpf' ";
            }

// ordem
            if ($ordem != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " IPR_NR_ORDEM_INSC = '$ordem' ";
            }

// situação
            if ($tpExibSituacao != NULL) {
                if ($tpExibSituacao != self::$MOSTRAR_SITUACAO_TODOS) {
                    if ($where) {
                        $sql .= " where ";
                        $where = false;
                        $and = true;
                    } else if ($and) {
                        $sql .= " and ";
                    }
                    $sql .= self::converteTpExibSituacaoEmSitInsc($tpExibSituacao);
                }
            }

// idChamada
            if ($idChamada != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " PCH_ID_CHAMADA = '$idChamada' ";
            }


//executando sql


            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new

            NegocioException("Erro ao contar usuários inscritos no processo", $e);
        }
    }

    private static function converteTpExibSituacaoEmSitInsc($tpExibSituacao) {
        if ($tpExibSituacao == self::$MOSTRAR_SITUACAO_CLAS) {
            return " (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '" . InscricaoProcesso::$SIT_INSC_OK . "')";
        }

        if ($tpExibSituacao == self::$MOSTRAR_SITUACAO_ELIM) {
            return " (IPR_ST_INSCRICAO = '" . InscricaoProcesso::$SIT_INSC_ELIMINADO . "' or IPR_ST_INSCRICAO = '" . InscricaoProcesso::$SIT_INSC_AUTO_ELIMINADO . "'
or IPR_ST_INSCRICAO = '" . InscricaoProcesso::$SIT_INSC_FALTA_VAGAS_ELIMINADO . "')";
        }

        throw new NegocioException("Conversão do tipo de exibição de situação de inscrição não codificada!");
    }

    public static function contaAvalCegaPorProcesso($idCurso, $idProcesso, $idChamada, $idPolo, $idAreaAtuacao, $idReservaVaga) {
        try {

//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

// constantes
            $strAdmitePolo = TipoCargo::getListaStrAdmitePolo();
            $avalCegaConc = self::$SIT_AVAL_CEGA_CONCLUIDA;
            $grupoAvalMan = GrupoAnexoProc::$AVAL_TP_MANUAL;

            $sql = "select 
count(*) as cont
FROM
tb_ipr_inscricao_processo ipr
JOIN
tb_prc_processo prc ON prc.PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO
JOIN
tb_cur_curso cur ON cur.CUR_ID_CURSO = prc.CUR_ID_CURSO
JOIN
tb_pch_processo_chamada pch ON pch.PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
WHERE
(IPR_ST_AVAL_CEGA IS NULL
OR IPR_ST_AVAL_CEGA != '$avalCegaConc')
AND IPR_DT_AVALIACAO IS NULL
AND PCH_DT_REG_RESUL_FINAL IS NULL
AND (TIC_ID_TIPO_CARGO IN ($strAdmitePolo)
OR (SELECT 
COUNT(*)
FROM
tb_gap_grupo_anexo_proc
WHERE
PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO
AND GAP_TP_AVALIACAO = '$grupoAvalMan' > 0))";


//complementando sql de acordo com o filtro
            $where = false;
            $and = !$where;

//processo
            if ($idProcesso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " ipr.`PRC_ID_PROCESSO` = '$idProcesso' ";
            }

//id polo
            if ($idPolo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " '$idPolo' in (select POL_ID_POLO from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO) ";
            }

//area atu chamada
            if ($idAreaAtuacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `AAC_ID_AREA_CHAMADA` = (select AAC_ID_AREA_CHAMADA from tb_aac_area_atu_chamada where PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
and ARC_ID_SUBAREA_CONH = '$idAreaAtuacao') ";
            }

// reserva de vaga
            if ($idReservaVaga != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }

                if ($idReservaVaga == ReservaVagaChamada::$ID_PUBLICO_GERAL) {
                    $sql .= " `RVC_ID_RESERVA_CHAMADA` IS NULL";
                } else {
                    $sql .= " `RVC_ID_RESERVA_CHAMADA` = (select RVC_ID_RESERVA_CHAMADA from tb_rvc_reserva_vaga_chamada where PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO and
PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA and RVG_ID_RESERVA_VAGA = '$idReservaVaga') ";
                }
            }

// idChamada
            if ($idChamada != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " ipr.PCH_ID_CHAMADA = '$idChamada' ";
            }

// idCurso
            if ($idCurso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;

                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " prc.CUR_ID_CURSO = $idCurso ";
            }

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new

            NegocioException("Erro ao contar inscrições para avaliação cega.", $e);
        }
    }

    private function enviarEmailInscricao($idUsuario, $idInscricao) {
        try {
//recuperando usuário
            $usuario = Usuario::buscarUsuarioPorId($idUsuario);

//recuperando inscrição
            $inscricao = InscricaoProcesso::buscarInscricaoPorId($idInscricao);

//recuperando processo
            $processo = Processo::buscarProcessoPorId($inscricao->PRC_ID_PROCESSO);

// recuperando chamada da inscriçao
            $chamada = ProcessoChamada::buscarChamadaPorId($inscricao->PCH_ID_CHAMADA);

// recuperando polos da inscriçao
            $polos = PoloInscricao::buscarPoloPorInscricao($idInscricao);

//montando mensagem
            $assunto = "Inscrição realizada com sucesso";

            $mensagem = "Olá, {$usuario->getUSR_DS_NOME()}.<br/><br/>";
            $mensagem .= "Você se inscreveu com sucesso na <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$chamada->getPCH_DS_CHAMADA(TRUE)}</b></span> do processo seletivo para <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$processo->TIC_NM_TIPO_CARGO}</b></span> do curso <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>$processo->CUR_NM_CURSO</b></span>.";
            $mensagem .= "<br/><br/><b>Dados da Inscrição</b>";
            $mensagem .= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Código:</b></span> $inscricao->IPR_NR_ORDEM_INSC";
            $mensagem .= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Data:</b></span> $inscricao->IPR_DT_INSCRICAO";
            $mensagem .= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Chamada:</b></span> $inscricao->PCH_DS_CHAMADA";

            if (ProcessoChamada::temOpcaoInscricao($chamada)) {
                $mensagem .= "<br/><br/><b>Opções de Inscrição</b>";
            } else {
                $mensagem .= "<br/>";
            }

            if ($chamada->admitePoloObj()) {
                $strPolos = arrayParaStr($polos);
                if (!$chamada->isInscricaoMultipla()) {
                    $mensagem.= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Polo:</b></span> $strPolos";
                } else {
                    $mensagem .= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Polos em ordem de prioridade:</b></span> $strPolos";
                }
            }

// recuperando area de atuaçao, se existir
            if ($chamada->admiteAreaAtuacaoObj()) {
                $areaAtu = AreaAtuChamada::buscarAreaAtuChamadaPorId($inscricao->AAC_ID_AREA_CHAMADA);
                $mensagem.= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Área de atuação:</b></span> $areaAtu->ARC_NM_SUBAREA_CONH";
            }

// recuperando reserva de vaga, se existir
            if ($chamada->admiteReservaVagaObj()) {
                $reservaVaga = getDsReservaVagaInscricaoCT($inscricao->getRVC_ID_RESERVA_CHAMADA());
                $mensagem.= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Reserva de Vaga:</b></span> $reservaVaga";
            }

            $mensagem .= "<br/><br/>Você pode consultar suas inscrições acessando o sistema e clicando no menu Editais, acima a direita, e selecionando a opção Minhas inscrições.";


            $destinatario = $usuario->getUSR_DS_EMAIL();

// enviando email
            enviaEmail($destinatario, $assunto, $mensagem);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao enviar email de notificação de inscrição.", $e);
        }
    }

    /**
     * 
     * @param string $dsMotivo
     * @return void - Faz solicitaçao de envio de email ao usuario por parte do php
     * @throws NegocioException
     */
    private function enviarEmailExcInsc($dsMotivo) {
        try {

//recuperando processo
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);

// recuperando chamada da inscriçao
            $chamada = ProcessoChamada::buscarChamadaPorId($this->PCH_ID_CHAMADA);

// recuperando usuario do candidato
            $usuario = buscarUsuarioPorIdCandCT($this->getCDT_ID_CANDIDATO());

//montando mensagem
            $assunto = "Sua inscrição foi excluída";

            $mensagem = "Olá, {$usuario->getUSR_DS_NOME()}.<br/><br/>";
            $mensagem .= "Sua inscrição na <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$chamada->getPCH_DS_CHAMADA(TRUE)}</b></span> do processo seletivo para <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$processo->TIC_NM_TIPO_CARGO}</b></span> do curso <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>$processo->CUR_NM_CURSO</b></span> foi excluída.";
            $mensagem .= "<br/><br/><b>Motivo da Exclusão</b>";
            $mensagem .= "<br/>$dsMotivo";

            $mensagem .="<br/><br/><i>* Você poderá inscrever-se novamente, desde que dentro do prazo de inscrição do edital, recebendo um novo código de inscrição.</i>";

            $destinatario = $usuario->getUSR_DS_EMAIL();

// enviando email
            enviaEmail($destinatario, $assunto, $mensagem);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao enviar email de notificação de exclusão da inscrição.", $e);
        }
    }

    public static function buscarInscricaoPorId($idInscricao) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
IPR_ID_INSCRICAO,
IPR_COD_COMP_INSC,
CONCAT(CAST(PRC_NR_EDITAL as CHAR (3)),
'/',
CAST(PRC_ANO_EDITAL as CHAR (4))) as PRC_NR_ANO_EDITAL,
ipr.CDT_ID_CANDIDATO,
ipr.PRC_ID_PROCESSO,
ipr.PCH_ID_CHAMADA,
PCH_DS_CHAMADA,
DATE_FORMAT(`PCH_DT_REG_RESUL_FINAL`,'%d/%m/%Y %T') as PCH_DT_REG_RESUL_FINAL,
DATE_FORMAT(`PCH_DT_FINALIZACAO`,'%d/%m/%Y') as PCH_DT_FINALIZACAO,
DATE_FORMAT(`IPR_DT_INSCRICAO`, '%d/%m/%Y %T') AS IPR_DT_INSCRICAO,
IPR_NR_ORDEM_INSC,
`TIC_NM_TIPO` as nmTipo,
`CUR_NM_CURSO` as nmCurso,
TPC_NM_TIPO_CURSO as tipoCurso,
PRC_DS_URL_EDITAL,
PRC_DS_PROCESSO,
PCH_TXT_COMP_INSCRICAO,
prc.CUR_ID_CURSO,
USR_DS_NOME,
USR_DS_EMAIL,
IPR_LOCALIZACAO_VALIDA,
IPR_VL_TOTAL_NOTA,
IPR_DS_OBS_NOTA,
IPR_ID_USR_AVALIADOR,
DATE_FORMAT(`IPR_DT_AVALIACAO`, '%d/%m/%Y %T') AS IPR_DT_AVALIACAO,
IPR_NR_CLASSIFICACAO_CAND,
IPR_ID_POLO_SELECIONADO,
IPR_CDT_SELECIONADO,
IPR_ST_AVAL_AUTOMATICA,
AAC_ID_AREA_CHAMADA,
IPR_ST_ANALISE,
IPR_ST_INSCRICAO,
RVC_ID_RESERVA_CHAMADA,
IPR_ST_AVAL_CEGA,
IPR_ID_ETAPA_SEL_NOTA
from
tb_ipr_inscricao_processo ipr
join
tb_prc_processo prc ON prc.PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO
join
tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
join
tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
join
tb_usr_usuario usr ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
join
tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
join
tb_tpc_tipo_curso tpc ON cur.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
join
tb_pch_processo_chamada pch ON pch.PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
where IPR_ID_INSCRICAO = '$idInscricao'";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                throw new NegocioException("Inscrição não encontrada.");
            }

//recuperando linha e criando objeto
            $dados = ConexaoMysql:: getLinha($resp);
            $inscricaoRet = new InscricaoProcesso($dados['IPR_ID_INSCRICAO'], $dados ['CDT_ID_CANDIDATO'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_DT_INSCRICAO'], $dados['IPR_NR_ORDEM_INSC'], NULL, $dados['IPR_LOCALIZACAO_VALIDA'], $dados['IPR_VL_TOTAL_NOTA'], $dados['IPR_DS_OBS_NOTA'], $dados['IPR_ID_USR_AVALIADOR'], $dados['IPR_DT_AVALIACAO'], $dados['IPR_NR_CLASSIFICACAO_CAND'], $dados['IPR_ID_POLO_SELECIONADO'], $dados['IPR_CDT_SELECIONADO'], $dados['IPR_ST_AVAL_AUTOMATICA'], $dados['AAC_ID_AREA_CHAMADA'], $dados['IPR_ST_ANALISE'], $dados['IPR_ST_INSCRICAO'], $dados['RVC_ID_RESERVA_CHAMADA'], $dados['IPR_ST_AVAL_CEGA'], $dados['IPR_ID_ETAPA_SEL_NOTA']);

//setando campos herdados
            $inscricaoRet->PRC_NR_ANO_EDITAL = $dados['PRC_NR_ANO_EDITAL'];
            $inscricaoRet->TIC_NM_TIPO_CARGO = $dados['nmTipo'];
            $inscricaoRet->TPC_NM_TIPO_CURSO = $dados['tipoCurso'];
            $inscricaoRet->CUR_NM_CURSO = $dados['nmCurso'];
            $inscricaoRet->PCH_DS_CHAMADA = $dados['PCH_DS_CHAMADA'];
            $inscricaoRet->PCH_DT_REG_RESUL_FINAL = $dados['PCH_DT_REG_RESUL_FINAL'];
            $inscricaoRet->PCH_DT_FINALIZACAO = $dados['PCH_DT_FINALIZACAO'];
            $inscricaoRet->PRC_DS_URL_EDITAL = $dados['PRC_DS_URL_EDITAL'];

            $inscricaoRet->PRC_DS_PROCESSO = $dados['PRC_DS_PROCESSO'];
            $inscricaoRet->PCH_TXT_COMP_INSCRICAO = $dados['PCH_TXT_COMP_INSCRICAO'];
            $inscricaoRet->CUR_ID_CURSO = $dados['CUR_ID_CURSO'];
            $inscricaoRet->USR_DS_NOME_CDT = $dados['USR_DS_NOME'];
            $inscricaoRet->USR_DS_EMAIL_CDT = $dados['USR_DS_EMAIL'];

            return $inscricaoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar inscrição do usuário.", $e);
        }
    }

    /**
     * Verifica se a inscriçao de id $idInscricao pertence ao usuario $idUsuario
     * @param int $idInscricao
     * @param int $idUsuario
     * @return boolean
     * @throws NegocioException
     */
    public static function isInscricaoUsuario($idInscricao, $idUsuario) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
count(*) as conta
from
tb_ipr_inscricao_processo
where
IPR_ID_INSCRICAO = '$idInscricao'
and CDT_ID_CANDIDATO = (select 
CDT_ID_CANDIDATO
from
tb_cdt_candidato
where
USR_ID_USUARIO = '$idUsuario')";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

// retornando se e dono
            return ConexaoMysql::getResult("conta", $resp) > 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar dono de inscrição.", $e);
        }
    }

    public static function buscarIdInscricaoPorChamUsuario($idProcesso, $idChamada, $idUsuario) {
        try {

//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PRC_ID_PROCESSO = '$idProcesso'
and PCH_ID_CHAMADA = '$idChamada'
and CDT_ID_CANDIDATO = (select 
CDT_ID_CANDIDATO
from
tb_cdt_candidato
where
USR_ID_USUARIO = '$idUsuario')";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

// retornando 
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                throw new NegocioException("Inscrição não encontrada.");
            }

            return ConexaoMysql::getResult("IPR_ID_INSCRICAO", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar Inscrição.", $e);
        }
    }

    /**
     * Esta função verifica se é permitido editar as notas do candidato em questão, caso ela esteja na tabela de inscritos
     * 
     * @param EtapaSelProc $etapaVigente
     */
    public function permiteEditarNotaTab($etapaVigente) {
// caso de bloqueio de pós recurso
// @todo Modificação necessária para habilitar resultado pós-recursos
        if ($etapaVigente != NULL && $etapaVigente->publicouResultadoPosRec()) {
            return FALSE; // Bloqueando
        }
        return $etapaVigente != NULL && (Util::vazioNulo($this->etapaEliminacao) || ($etapaVigente->getESP_ID_ETAPA_SEL() == $this->etapaEliminacao));
    }

    public static function contarInscPorFiltroUsuario($idUsuario, $idProcesso, $idTipo, $tpFormacao, $idCurso, $anoEdital, $nrEdital) {
        try {
// obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT count(*) as cont
from
tb_ipr_inscricao_processo ipr
join
tb_prc_processo prc ON prc.PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO
join
tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
join
tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
join
tb_tpc_tipo_curso tpc ON cur.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
join
tb_pch_processo_chamada pch ON pch.PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
where ipr.CDT_ID_CANDIDATO = (select 
CDT_ID_CANDIDATO
from
tb_cdt_candidato
where
USR_ID_USUARIO = '$idUsuario')";

//caso filtro
//processo
            if ($idProcesso != NULL) {
                $sql .= " and prc.`PRC_ID_PROCESSO` = '$idProcesso' ";
            }

//idTipo
            if ($idTipo != NULL) {
                $sql .= " and prc.`TIC_ID_TIPO_CARGO` = '$idTipo' ";
            }

//tpFormacao
            if ($tpFormacao != NULL) {
                $sql .= " and cur.TPC_ID_TIPO_CURSO = '$tpFormacao' ";
            }

//idCurso
            if ($idCurso != NULL) {
                $sql .= " and cur.CUR_ID_CURSO = '$idCurso' ";
            }

// ano edital
            if ($anoEdital != NULL) {
                $sql .= " and prc.PRC_ANO_EDITAL = '$anoEdital' ";
            }

// número edital
            if ($nrEdital != NULL) {
                $sql .= " and prc.PRC_NR_EDITAL = '$nrEdital' ";
            }



//executando sql

            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw

            new NegocioException("Erro ao contar inscrições do usuário.", $e);
        }
    }

    /**
     * 
     * ATENÇÃO: ESTA FUNÇÃO É DIFERENTE DAS DEMAIS FUNÇÕES DE BUSCA!
     * 
     * Ela retorna uma estrutura de matriz indexada pela situação do preenchimento da nota (Veja constantes APRES_INSC_NT*),
     *  onde cada índice contém uma lista de inscrições que devem ser listados naquela categoria de apresentação.
     * 
     * @param int $idProcesso
     * @param int $idPolo
     * @param int $idAreaAtuacao
     * @param int $idReservaVaga
     * @param string $nmUsuario
     * @param string $nrcpf
     * @param int $idChamada
     * @param int $ordem
     * @param char $tpExibSituacao
     * @param char $tpClassificacao
     * @param char $tpOrdenacao
     * @param int $inicioDados
     * @param int $qtdeDados
     * @return \InscricaoProcesso Matriz indexada com base nas constantes APRES_INSC_NT*, onde cada índice contém uma lista de inscrições naquela situação
     * @throws NegocioException
     */
    public static function buscaInscritosPorProcesso($idProcesso, $idPolo, $idAreaAtuacao, $idReservaVaga, $nmUsuario, $nrcpf, $idChamada, $ordem, $tpExibSituacao, $tpClassificacao, $tpOrdenacao, $inicioDados, $qtdeDados) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

// parametros
            $itemManual = CategoriaAvalProc::$AVAL_MANUAL;
            $sqlSitEliminado = "NEI_ST_INSCRICAO != '" . InscricaoProcesso::$SIT_INSC_OK . "'" . " and NEI_ST_INSCRICAO != '" . InscricaoProcesso::$SIT_INSC_CAD_RESERVA . "'";
            $idEtapaAvalAtiva = EtapaSelProc::getSqlIdEtapaAvalEtapaSelAtivaCham("ipr.PCH_ID_CHAMADA");

            $sql = "select 
IPR_ID_INSCRICAO,
IPR_COD_COMP_INSC,
ipr.CDT_ID_CANDIDATO,
ipr.PRC_ID_PROCESSO,
ipr.PCH_ID_CHAMADA,
DATE_FORMAT(`IPR_DT_INSCRICAO`, '%d/%m/%Y %T') AS IPR_DT_INSCRICAO,
IPR_NR_ORDEM_INSC,
USR_DS_NOME,
USR_DS_EMAIL,
IDC_NR_CPF,
IPR_LOCALIZACAO_VALIDA,
IPR_VL_TOTAL_NOTA,
IPR_DS_OBS_NOTA,
IPR_ID_USR_AVALIADOR,
DATE_FORMAT(`IPR_DT_AVALIACAO`, '%d/%m/%Y %T') AS IPR_DT_AVALIACAO,
IPR_NR_CLASSIFICACAO_CAND,
IPR_ID_POLO_SELECIONADO,
IPR_CDT_SELECIONADO,
IPR_ST_AVAL_AUTOMATICA,
AAC_ID_AREA_CHAMADA,
IPR_ST_ANALISE,
IPR_ST_INSCRICAO,
RVC_ID_RESERVA_CHAMADA,
IPR_ST_AVAL_CEGA,
IPR_ID_ETAPA_SEL_NOTA,
(SELECT 
ESP_ID_ETAPA_SEL
FROM
tb_nei_notas_etapa_sel_insc nei
WHERE
nei.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
AND $sqlSitEliminado order by NEI_DT_AVALIACAO limit 0,1) as etapaEliminacao,
(select 
count(*) as cont
from
tb_iap_item_aval_proc iap
join
tb_cap_categoria_aval_proc cap ON cap.CAP_ID_CATEGORIA_AVAL = iap.CAP_ID_CATEGORIA_AVAL
join
tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
where
iap.prc_id_processo = ipr.prc_id_processo
and CAP_TP_AVALIACAO = '$itemManual'
and eap.EAP_ID_ETAPA_AVAL_PROC = $idEtapaAvalAtiva
and not exists( select 
*
from
tb_rni_rel_notas_insc rni
where
iap.IAP_ID_ITEM_AVAL = rni.IAP_ID_ITEM_AVAL
and rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO)) as qtNotaManSemAval
from
tb_ipr_inscricao_processo ipr
join
tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
join
tb_usr_usuario usr ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
join
tb_idc_identificacao_candidato idc ON cdt.IDC_ID_IDENTIFICACAO_CDT = idc.IDC_ID_IDENTIFICACAO_CDT ";

//complementando sql de acordo com o filtro
            $where = true;
            $and = false;

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


//area atu chamada
            if ($idAreaAtuacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `AAC_ID_AREA_CHAMADA` = (select AAC_ID_AREA_CHAMADA from tb_aac_area_atu_chamada where PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
and ARC_ID_SUBAREA_CONH = '$idAreaAtuacao') ";
            }

// reserva de vaga
            if ($idReservaVaga != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }

                if ($idReservaVaga == ReservaVagaChamada::$ID_PUBLICO_GERAL) {
                    $sql .= " `RVC_ID_RESERVA_CHAMADA` IS NULL";
                } else {
                    $sql .= " `RVC_ID_RESERVA_CHAMADA` = (select RVC_ID_RESERVA_CHAMADA from tb_rvc_reserva_vaga_chamada where PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO and
PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA and RVG_ID_RESERVA_VAGA = '$idReservaVaga')";
                }
            }

//id polo
            if ($idPolo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " '$idPolo' in (select POL_ID_POLO from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO) ";
            }

//nome usuário
            if ($nmUsuario != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " usr.USR_DS_NOME like '%$nmUsuario%' ";
            }

// nrcpf
            if ($nrcpf != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " IDC_NR_CPF = '$nrcpf' ";
            }

// ordem
            if ($ordem != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " IPR_NR_ORDEM_INSC = '$ordem' ";
            }

// situação
            if ($tpExibSituacao != NULL) {
                if ($tpExibSituacao != self::$MOSTRAR_SITUACAO_TODOS) {
                    if ($where) {
                        $sql .= " where ";
                        $where = false;
                        $and = true;
                    } else if ($and) {
                        $sql .= " and ";
                    }
                    $sql .= self::converteTpExibSituacaoEmSitInsc($tpExibSituacao);
                }
            }

// idChamada
            if ($idChamada != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " PCH_ID_CHAMADA = '$idChamada' ";
            }

// Finalização: caso de ordenação
//
// Tipo de exibição todos: Separar eliminados
            if ($tpExibSituacao == self::$MOSTRAR_SITUACAO_TODOS) {
                $sql .= " order by (IPR_ST_INSCRICAO IS NOT NULL and (IPR_ST_INSCRICAO = '" . self::$SIT_INSC_ELIMINADO . "' or IPR_ST_INSCRICAO = '" . self::$SIT_INSC_AUTO_ELIMINADO . "' or IPR_ST_INSCRICAO = '" . self::$SIT_INSC_FALTA_VAGAS_ELIMINADO . "')), ";
            } else {
                $sql .= " order by ";
            }

//
// tratando caso de ascendente ou descendente
            $dec = $tpOrdenacao == self::$ORDENACAO_DECRESCENTE;

            if ($tpClassificacao == InscricaoProcesso::$ORDEM_INSCRITOS_NOME) {
                $sql .= " usr.USR_DS_NOME " . ($dec ? "desc " : " ");
            } elseif ($tpClassificacao == InscricaoProcesso::$ORDEM_INSCRITOS_CLASSIFICACAO) {
                $sql .= " ISNULL(ipr_nr_classificacao_cand) , `IPR_NR_CLASSIFICACAO_CAND` " . ($dec ? "desc " : " ") . ", ISNULL(ipr_vl_total_nota) , `IPR_VL_TOTAL_NOTA` " . ($dec ? "desc " : " ");
            } else {
                $sql .= " `IPR_NR_ORDEM_INSC` " . ($dec ? "desc " : " ");
            }

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

            $matRetorno = array(); //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
//recuperando linha e criando objeto
                $dados = ConexaoMysql:: getLinha($resp);
                $inscricaoTemp = new InscricaoProcesso($dados['IPR_ID_INSCRICAO'], $dados ['CDT_ID_CANDIDATO'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_DT_INSCRICAO'], $dados['IPR_NR_ORDEM_INSC'], NULL, $dados['IPR_LOCALIZACAO_VALIDA'], $dados['IPR_VL_TOTAL_NOTA'], $dados['IPR_DS_OBS_NOTA'], $dados['IPR_ID_USR_AVALIADOR'], $dados['IPR_DT_AVALIACAO'], $dados['IPR_NR_CLASSIFICACAO_CAND'], $dados['IPR_ID_POLO_SELECIONADO'], $dados['IPR_CDT_SELECIONADO'], $dados['IPR_ST_AVAL_AUTOMATICA'], $dados['AAC_ID_AREA_CHAMADA'], $dados['IPR_ST_ANALISE'], $dados['IPR_ST_INSCRICAO'], $dados['RVC_ID_RESERVA_CHAMADA'], $dados['IPR_ST_AVAL_CEGA'], $dados['IPR_ID_ETAPA_SEL_NOTA']);

//preenchendo campos restantes
                $inscricaoTemp->USR_DS_NOME_CDT = $dados['USR_DS_NOME'];
                $inscricaoTemp->IDC_NR_CPF_CDT = $dados['IDC_NR_CPF'];
                $inscricaoTemp->USR_DS_EMAIL_CDT = $dados['USR_DS_EMAIL'];
                $inscricaoTemp->etapaEliminacao = $dados['etapaEliminacao'];

// setando a situação da nota
                $inscricaoTemp->setTpSituacaoNota($dados['qtNotaManSemAval']);

//adicionando na matriz
                if (!isset($matRetorno[$inscricaoTemp->situacaoNota])) {
                    $matRetorno[$inscricaoTemp->situacaoNota] = array($inscricaoTemp);
                } else {
                    $matRetorno[$inscricaoTemp->situacaoNota][] = $inscricaoTemp;
                }
            }
            return $matRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar usuários inscritos no processo.", $e);
        }
    }

    private function setTpSituacaoNota($qtNotaManSemAval) {
        $this->situacaoNota = $this->isEliminada() || ($qtNotaManSemAval == 0 && !Util::vazioNulo($this->IPR_ST_ANALISE) && $this->IPR_ST_ANALISE == self::$SIT_ANALISE_CONCLUIDA) ? self::$APRES_INSC_NT_VALIDADA : self::$APRES_INSC_NT_NAO_VALIDADA;
    }

    public static function buscarAvalCegaPorProcesso($idCurso, $idProcesso, $idChamada, $idPolo, $idAreaAtuacao, $idReservaVaga, $inicioDados, $qtdeDados) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

// constantes
            $strAdmitePolo = TipoCargo::getListaStrAdmitePolo();
            $avalCegaConc = self::$SIT_AVAL_CEGA_CONCLUIDA;
            $grupoAvalMan = GrupoAnexoProc::$AVAL_TP_MANUAL;

            $sql = "select 
IPR_ID_INSCRICAO,
CONCAT(LPAD(CAST(PRC_NR_EDITAL as CHAR (3)),3,'0'),
'/',
CAST(PRC_ANO_EDITAL as CHAR (4))) as PRC_NR_ANO_EDITAL,
PCH_DS_CHAMADA,
DATE_FORMAT(`PCH_DT_REG_RESUL_FINAL`,'%d/%m/%Y %T') as PCH_DT_REG_RESUL_FINAL,
DATE_FORMAT(`PCH_DT_FINALIZACAO`,'%d/%m/%Y') as PCH_DT_FINALIZACAO,
`CUR_NM_CURSO`,
IPR_COD_COMP_INSC,
ipr.CDT_ID_CANDIDATO,
ipr.PRC_ID_PROCESSO,
ipr.PCH_ID_CHAMADA,
DATE_FORMAT(`IPR_DT_INSCRICAO`, '%d/%m/%Y %T') AS IPR_DT_INSCRICAO,
IPR_NR_ORDEM_INSC,
IPR_LOCALIZACAO_VALIDA,
IPR_VL_TOTAL_NOTA,
IPR_DS_OBS_NOTA,
IPR_ID_USR_AVALIADOR,
DATE_FORMAT(`IPR_DT_AVALIACAO`, '%d/%m/%Y %T') AS IPR_DT_AVALIACAO,
IPR_NR_CLASSIFICACAO_CAND,
IPR_ID_POLO_SELECIONADO,
IPR_CDT_SELECIONADO,
IPR_ST_AVAL_AUTOMATICA,
AAC_ID_AREA_CHAMADA,
IPR_ST_ANALISE,
IPR_ST_INSCRICAO,
RVC_ID_RESERVA_CHAMADA,
IPR_ST_AVAL_CEGA,
IPR_ID_ETAPA_SEL_NOTA
from
tb_ipr_inscricao_processo ipr
join
tb_prc_processo prc ON prc.PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO
join
tb_cur_curso cur ON cur.CUR_ID_CURSO = prc.CUR_ID_CURSO
join
tb_pch_processo_chamada pch ON pch.PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
WHERE
(IPR_ST_AVAL_CEGA IS NULL
OR IPR_ST_AVAL_CEGA != '$avalCegaConc')
AND IPR_DT_AVALIACAO IS NULL
AND PCH_DT_REG_RESUL_FINAL IS NULL
AND (TIC_ID_TIPO_CARGO IN ($strAdmitePolo)
OR (SELECT 
COUNT(*)
FROM
tb_gap_grupo_anexo_proc
WHERE
PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO
AND GAP_TP_AVALIACAO = '$grupoAvalMan' > 0))";


//complementando sql de acordo com o filtro
            $where = false;
            $and = !$where;

//processo
            if ($idProcesso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " ipr.`PRC_ID_PROCESSO` = '$idProcesso' ";
            }

//id polo
            if ($idPolo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " '$idPolo' in (select POL_ID_POLO from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO) ";
            }

//area atu chamada
            if ($idAreaAtuacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `AAC_ID_AREA_CHAMADA` = (select AAC_ID_AREA_CHAMADA from tb_aac_area_atu_chamada where PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
and ARC_ID_SUBAREA_CONH = '$idAreaAtuacao') ";
            }

// reserva de vaga
            if ($idReservaVaga != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }

                if ($idReservaVaga == ReservaVagaChamada::$ID_PUBLICO_GERAL) {
                    $sql .= " `RVC_ID_RESERVA_CHAMADA` IS NULL";
                } else {
                    $sql .= " `RVC_ID_RESERVA_CHAMADA` = (select RVC_ID_RESERVA_CHAMADA from tb_rvc_reserva_vaga_chamada where PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO and
PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA and RVG_ID_RESERVA_VAGA = '$idReservaVaga') ";
                }
            }

// idChamada
            if ($idChamada != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " ipr.PCH_ID_CHAMADA = '$idChamada' ";
            }

// idCurso
            if ($idCurso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " prc.CUR_ID_CURSO = $idCurso ";
            }

// questão de ordenação
            $sql .= "order by ipr.IPR_ID_INSCRICAO ";

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
                $dados = ConexaoMysql:: getLinha($resp);
                $inscTemp = new InscricaoProcesso($dados['IPR_ID_INSCRICAO'], $dados ['CDT_ID_CANDIDATO'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_DT_INSCRICAO'], $dados['IPR_NR_ORDEM_INSC'], NULL, $dados['IPR_LOCALIZACAO_VALIDA'], $dados['IPR_VL_TOTAL_NOTA'], $dados['IPR_DS_OBS_NOTA'], $dados['IPR_ID_USR_AVALIADOR'], $dados['IPR_DT_AVALIACAO'], $dados['IPR_NR_CLASSIFICACAO_CAND'], $dados['IPR_ID_POLO_SELECIONADO'], $dados['IPR_CDT_SELECIONADO'], $dados['IPR_ST_AVAL_AUTOMATICA'], $dados['AAC_ID_AREA_CHAMADA'], $dados['IPR_ST_ANALISE'], $dados['IPR_ST_INSCRICAO'], $dados['RVC_ID_RESERVA_CHAMADA'], $dados['IPR_ST_AVAL_CEGA'], $dados['IPR_ID_ETAPA_SEL_NOTA']);

//setando campos herdados
                $inscTemp->PRC_NR_ANO_EDITAL = $dados['PRC_NR_ANO_EDITAL'];
                $inscTemp->CUR_NM_CURSO = $dados['CUR_NM_CURSO'];
                $inscTemp->PCH_DS_CHAMADA = $dados['PCH_DS_CHAMADA'];
                $inscTemp->PCH_DT_REG_RESUL_FINAL = $dados['PCH_DT_REG_RESUL_FINAL'];
                $inscTemp->PCH_DT_FINALIZACAO = $dados['PCH_DT_FINALIZACAO'];

//adicionando no vetor

                $vetRetorno[$i] = $inscTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar inscrições para avaliação cega.", $e);
        }
    }

    /**
     * Esta função retorna uma lista com email dos candidatos que estão inscritos em atualização do edital, para o 
     * envio de alguma mensagem de atualização.
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @return Array Lista de email dos candidatos inscritos em atualização do edital na forma: ['email' => 'nome']
     * @throws NegocioException
     */
    public static function buscarEmailInscritosAtualizacao($idProcesso, $idChamada) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

// constantes
            $flAcompSim = FLAG_BD_SIM;

            $sql = "select 
USR_DS_EMAIL as email,
USR_DS_NOME as nome
from
tb_ipr_inscricao_processo ipr
join
tb_cdt_candidato cdt ON ipr.CDT_ID_CANDIDATO = cdt.CDT_ID_CANDIDATO
join
tb_usr_usuario usr ON usr.USR_ID_USUARIO = cdt.USR_ID_USUARIO
join
tb_cfu_configuracao_usuario cfu ON cdt.USR_ID_USUARIO = cfu.USR_ID_USUARIO
WHERE
CFU_FL_ACOMP_PROCESSO = '$flAcompSim'
and PRC_ID_PROCESSO = '$idProcesso'
and PCH_ID_CHAMADA = '$idChamada'";

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
//recuperando linha e adicionando no vetor
                $dados = ConexaoMysql:: getLinha($resp);
                $vetRetorno[$dados['email']] = $dados['nome'];
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar email inscrito para notificação de atualização de edital.", $e);
        }
    }

    public static function contarInscricaoPorUsuario($idUsuario) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
count(*) as cont
from
tb_ipr_inscricao_processo
where
CDT_ID_CANDIDATO = (select 
CDT_ID_CANDIDATO
from
tb_cdt_candidato
where
USR_ID_USUARIO = '$idUsuario')";

//executando sql

            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições do usuário.", $e);
        }
    }

    public static function contarInscricaoPorUsuResp($idUsuResp) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
count(*) as cont
from
tb_ipr_inscricao_processo
where IPR_ID_USR_AVALIADOR = '$idUsuResp'";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições por responsável.", $e);
        }
    }

    public static function contarInscricaoPorProcessoCham($idProcesso, $idChamada = NULL) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
count(*) as cont
from
tb_ipr_inscricao_processo
where
PRC_ID_PROCESSO = '$idProcesso'";

// tem chamada?
            if ($idChamada != NULL) {
                $sql .= " and PCH_ID_CHAMADA = '$idChamada'";
            }

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições por processo chamada.", $e);
        }
    }

    public static function contarInscricaoPorChamReservaVagas($idProcesso, $idChamada) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
RVC_ID_RESERVA_CHAMADA, COUNT(*) AS cont
FROM
tb_ipr_inscricao_processo
WHERE
PRC_ID_PROCESSO = '$idProcesso'
AND PCH_ID_CHAMADA = '$idChamada'
GROUP BY RVC_ID_RESERVA_CHAMADA";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            if ($numLinhas == 0) {
//retornando array vazio
                return array();
            }

            $vetRetorno = array();

//realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
//recuperando linha e criando objeto
                $dados = ConexaoMysql:: getLinha($resp);

// recuperando indice (Já 'consertando' público geral)
                $chave = !Util::vazioNulo($dados['RVC_ID_RESERVA_CHAMADA']) ? $dados['RVC_ID_RESERVA_CHAMADA'] : ReservaVagaChamada::$ID_PUBLICO_GERAL;

//adicionando no vetor
                $vetRetorno[$chave] = $dados['cont'];
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições por reserva de vagas.", $e);
        }
    }

    public static function contarInscricaoPorChamAreaAtuacao($idProcesso, $idChamada) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
AAC_ID_AREA_CHAMADA, COUNT(*) AS cont
FROM
tb_ipr_inscricao_processo
WHERE
PRC_ID_PROCESSO = '$idProcesso'
AND PCH_ID_CHAMADA = '$idChamada'
GROUP BY AAC_ID_AREA_CHAMADA";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            if ($numLinhas == 0) {
//retornando array vazio
                return array();
            }

            $vetRetorno = array();

//realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
//recuperando linha e criando objeto
                $dados = ConexaoMysql:: getLinha($resp);

//adicionando no vetor
                $vetRetorno[$dados['AAC_ID_AREA_CHAMADA']] = $dados['cont'];
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições por área de atuação.", $e);
        }
    }

    public static function contarInscricaoPorChamPolo($idProcesso, $idChamada) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
POL_ID_POLO, COUNT(*) AS cont
FROM
tb_ipr_inscricao_processo ipr
join tb_pin_polo_inscricao pin on ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO
and PIN_NR_ORDEM = 1
WHERE
PRC_ID_PROCESSO = '$idProcesso'
AND PCH_ID_CHAMADA = '$idChamada'
GROUP BY POL_ID_POLO";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            if ($numLinhas == 0) {
//retornando array vazio
                return array();
            }

            $vetRetorno = array();

//realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
//recuperando linha e criando objeto
                $dados = ConexaoMysql:: getLinha($resp);

//adicionando no vetor
                $vetRetorno[$dados['POL_ID_POLO']] = $dados['cont'];
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições por polo.", $e);
        }
    }

    public static function contarInscricaoPorChamPoloAreaAtu($idProcesso, $idChamada) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
POL_ID_POLO, AAC_ID_AREA_CHAMADA, COUNT(*) AS cont
FROM
tb_ipr_inscricao_processo ipr
join tb_pin_polo_inscricao pin on ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO
and PIN_NR_ORDEM = 1
WHERE
PRC_ID_PROCESSO = '$idProcesso'
AND PCH_ID_CHAMADA = '$idChamada'
GROUP BY POL_ID_POLO, AAC_ID_AREA_CHAMADA";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            if ($numLinhas == 0) {
//retornando array vazio
                return array();
            }

            $vetRetorno = array();

//realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
//recuperando linha e criando objeto
                $dados = ConexaoMysql:: getLinha($resp);

//adicionando no vetor
                $vetRetorno[$dados['POL_ID_POLO'] . ":" . $dados['AAC_ID_AREA_CHAMADA']] = $dados['cont'];
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições por polo-área de atuação.", $e);
        }
    }

    public static function contarInscSemAnaliseHumana($idProcesso, $idChamada) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

// sem análise
            $stAnalise = self::$SIT_ANALISE_PENDENTE;

            $sql = "SELECT 
count(*) as cont
from
tb_ipr_inscricao_processo
where
PRC_ID_PROCESSO = '$idProcesso'
and PCH_ID_CHAMADA = '$idChamada'
and (IPR_ST_ANALISE IS NULL or IPR_ST_ANALISE = '$stAnalise')";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições sem análise humana.", $e);
        }
    }

    public static function contarEliminacaoLote($idProcesso, $idChamada, $nrEtapa) {
        try {

//criando objeto de conexão
            $conexao = NGUtil::getConexao();

// parametros
            $itemManual = CategoriaAvalProc::$AVAL_MANUAL;
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $stAnalise = self::$SIT_ANALISE_PENDENTE;

// montando sql
            $sql = "select 
count(*) as cont
from
tb_ipr_inscricao_processo ipr
where
pch_id_chamada = '$idChamada'
and prc_id_processo = '$idProcesso'
and (((ipr_st_inscricao IS NULL or ipr_st_inscricao = '$inscOk')
and (select 
count(*) as cont
from
tb_iap_item_aval_proc iap
join
tb_cap_categoria_aval_proc cap ON cap.CAP_ID_CATEGORIA_AVAL = iap.CAP_ID_CATEGORIA_AVAL
join
tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
where
iap.prc_id_processo = ipr.prc_id_processo
and CAP_TP_AVALIACAO = '$itemManual'
and EAP_NR_ETAPA_AVAL = '$nrEtapa'
and not exists( select 
*
from
tb_rni_rel_notas_insc rni
where
iap.IAP_ID_ITEM_AVAL = rni.IAP_ID_ITEM_AVAL
and rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO)) > 0) 
or (IPR_ST_ANALISE IS NULL or IPR_ST_ANALISE = '$stAnalise'))";

//            print_r($sql);
//            exit;
//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

// retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar quantidade de eliminação em lote.", $e);
        }
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param int $nrEtapa
     * @param string $justificativa
     * @throws NegocioException
     */
    public static function executarEliminacaoLote($processo, $chamada, $nrEtapa, $justificativa) {
        try {

// justificativa é nula?
            if (Util::vazioNulo($justificativa)) {
                throw new NegocioException("Justificativa de eliminação em lote não pode ser vazia.");
            }

// recuperando etapa de classificacao
            $etapa = EtapaSelProc::buscarEtapaEmAndamento($chamada->getPCH_ID_CHAMADA());

// caso de nao existir etapas de avaliaçao
            if (Util::vazioNulo($etapa)) {
                throw new NegocioException("Não existem etapas de avaliação em andamento.");
            }

// caso de etapa incorreta
            if ($etapa->getESP_NR_ETAPA_SEL() != $nrEtapa) {
                throw new NegocioException("Etapa de avaliação incorreta ao eliminar em lote.");
            }

// pode editar a nota?
// @todo Modificação necessária para habilitar resultado pós-recursos
            if ($etapa->publicouResultadoPosRec()) {
                throw new NegocioException("Você não pode editar as notas dos candidatos.");
            }

//criando objeto de conexão
            $conexao = NGUtil::getConexao();

// parametros
            $itemManual = CategoriaAvalProc::$AVAL_MANUAL;
            $avalAtiva = RelNotasInsc::$SIT_ATIVA;
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $inscEliminado = InscricaoProcesso::$SIT_INSC_ELIMINADO;
            $stAnalisePen = self::$SIT_ANALISE_PENDENTE;
            $stAnaliseCon = self::$SIT_ANALISE_CONCLUIDA;
            $idAvaliador = getIdUsuarioLogado();

// montando sql
// OBS: Existem alguns pontos de SQL SIMILIAR que devem ser alterados, caso haja mudanças aqui
// Pesquisa: #sqlNotas
            $sql = "update tb_ipr_inscricao_processo ipr
set 
IPR_ST_ANALISE = '$stAnaliseCon',
IPR_ID_USR_AVALIADOR = '$idAvaliador',
IPR_DT_AVALIACAO = now(),
IPR_ID_ETAPA_SEL_NOTA = '{$etapa->getESP_ID_ETAPA_SEL()}',
ipr_vl_total_nota = coalesce((select 
sum(RNI_VL_NOTA_NORMALIZADA)
from
tb_rni_rel_notas_insc rniInt
where
ipr.IPR_ID_INSCRICAO = rniInt.IPR_ID_INSCRICAO
and RNI_ST_AVALIACAO = '$avalAtiva'
and CAP_ID_CATEGORIA_AVAL IN 
(select CAP_ID_CATEGORIA_AVAL from tb_cap_categoria_aval_proc 
where EAP_ID_ETAPA_AVAL_PROC = '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')), 0),
ipr_st_inscricao = '$inscEliminado',
ipr_ds_obs_nota = '$justificativa' 
where
pch_id_chamada = '{$chamada->getPCH_ID_CHAMADA()}'
and prc_id_processo = '{$chamada->getPRC_ID_PROCESSO()}'
and (((ipr_st_inscricao IS NULL or ipr_st_inscricao = '$inscOk')
and (select 
count(*) as cont
from
tb_iap_item_aval_proc iap
join
tb_cap_categoria_aval_proc cap ON cap.CAP_ID_CATEGORIA_AVAL = iap.CAP_ID_CATEGORIA_AVAL
join
tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
where
iap.prc_id_processo = ipr.prc_id_processo
and CAP_TP_AVALIACAO = '$itemManual'
and EAP_NR_ETAPA_AVAL = '$nrEtapa'
and not exists( select 
*
from
tb_rni_rel_notas_insc rni
where
iap.IAP_ID_ITEM_AVAL = rni.IAP_ID_ITEM_AVAL
and rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO)) > 0) 
or (IPR_ST_ANALISE IS NULL or IPR_ST_ANALISE = '$stAnalisePen'))";

//executando sql
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao executar eliminação em lote.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @param char $stInscricao
     * @return int
     * @throws NegocioException
     */
    public static function contarInscricaoPorSituacao($idProcesso, $idChamada, $stInscricao) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
count(*) as cont
from
tb_ipr_inscricao_processo
where
PRC_ID_PROCESSO = '$idProcesso'
and PCH_ID_CHAMADA = '$idChamada'
and IPR_ST_INSCRICAO = '$stInscricao'";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições sem análise humana.", $e);
        }
    }

    public static function teveDownloadCompInscricao($idProcesso, $idChamada) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
count(*) as cont
from
tb_ipr_inscricao_processo
where
PRC_ID_PROCESSO = '$idProcesso'
and PCH_ID_CHAMADA = '$idChamada' 
and IPR_COD_COMP_INSC IS NOT NULL";

//executando sql

            $resp = $conexao->execSqlComRetorno($sql);

//retornando
            return $conexao->getResult("cont", $resp) != 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar se teve download de comprovantes de inscrição da chamada.", $e);
        }
    }

    public static function contarInscProcChamAbertaPorCdt($idCandidato) {
        try {
//criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
count(*) as cont
from
tb_ipr_inscricao_processo ipr
join
tb_pch_processo_chamada pch ON pch.PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO and pch.PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
where
(PCH_DT_FINALIZACAO IS NULL or PCH_DT_FINALIZACAO > curdate())
and ipr.CDT_ID_CANDIDATO = '$idCandidato'";

//executando sql
            $resp = $conexao->execSqlComRetorno($sql);

//retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscrições com processos em aberto do candidato.", $e);
        }
    }

    /**
     * 
     * ATENÇÃO: ESTA FUNÇÃO É DIFERENTE DAS DEMAIS FUNÇÕES DE BUSCA!
     * 
     * Ela retorna uma estrutura de matriz indexada pelo tipo de apresentação, onde cada índice contém uma lista de 
     * inscrições que devem ser listados naquela categoria de apresentação.
     * 
     * @param int $idUsuario
     * @param int $idProcesso
     * @param int $idTipo
     * @param int $tpFormacao
     * @param int $idCurso
     * @param int $anoEdital
     * @param int $nrEdital
     * @param int $inicioDados
     * @param int $qtdeDados
     * @param char $tpApresentacao Tipo de apresentação desejado. Parâmetro opcional.
     * @return \InscricaoProcesso Matriz indexada conforme já detalhado
     * @throws NegocioException
     */
    public static function buscarInscricaoPorUsuario($idUsuario, $idProcesso, $idTipo, $tpFormacao, $idCurso, $anoEdital, $nrEdital, $inicioDados, $qtdeDados, $tpApresentacao = NULL) {
        try {
//recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
IPR_ID_INSCRICAO,
CONCAT(LPAD(CAST(PRC_NR_EDITAL as CHAR (3)),3,'0'),
'/',
CAST(PRC_ANO_EDITAL as CHAR (4))) as PRC_NR_ANO_EDITAL,
CDT_ID_CANDIDATO,
ipr.PRC_ID_PROCESSO,
ipr.PCH_ID_CHAMADA,
PCH_DS_CHAMADA,
DATE_FORMAT(`PCH_DT_ABERTURA`, '%d/%m/%Y') AS PCH_DT_ABERTURA_S, 
DATE_FORMAT(`PCH_DT_FECHAMENTO`, '%d/%m/%Y') AS PCH_DT_FECHAMENTO_S, 
PCH_CHAMADA_ATIVA,
DATE_FORMAT(`PCH_DT_REG_RESUL_FINAL`,'%d/%m/%Y %T') as PCH_DT_REG_RESUL_FINAL_S,
DATE_FORMAT(`PCH_DT_FINALIZACAO`,'%d/%m/%Y') as PCH_DT_FINALIZACAO_S,
DATE_FORMAT(`IPR_DT_INSCRICAO`, '%d/%m/%Y %T') AS IPR_DT_INSCRICAOSTR,
IPR_NR_ORDEM_INSC,
`TIC_NM_TIPO` as nmTipo,
`CUR_NM_CURSO` as nmCurso,
TPC_NM_TIPO_CURSO as tipoCurso,
PRC_DS_URL_EDITAL,
PRC_DS_PROCESSO,
IPR_LOCALIZACAO_VALIDA,
IPR_VL_TOTAL_NOTA,
IPR_DS_OBS_NOTA,
IPR_ID_USR_AVALIADOR,
DATE_FORMAT(`IPR_DT_AVALIACAO`, '%d/%m/%Y %T') AS IPR_DT_AVALIACAO_S,
IPR_NR_CLASSIFICACAO_CAND,
IPR_ID_POLO_SELECIONADO,
IPR_CDT_SELECIONADO,
IPR_ST_AVAL_AUTOMATICA,
AAC_ID_AREA_CHAMADA,
IPR_ST_ANALISE,
IPR_ST_INSCRICAO,
RVC_ID_RESERVA_CHAMADA,
IPR_ST_AVAL_CEGA,
IPR_ID_ETAPA_SEL_NOTA
from
tb_ipr_inscricao_processo ipr
join
tb_prc_processo prc ON prc.PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO
join
tb_tic_tipo_cargo tic ON prc.`TIC_ID_TIPO_CARGO` = tic.TIC_ID_TIPO
join
tb_cur_curso cur ON prc.CUR_ID_CURSO = cur.CUR_ID_CURSO
join
tb_tpc_tipo_curso tpc ON cur.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
join
tb_pch_processo_chamada pch ON pch.PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA
where ipr.CDT_ID_CANDIDATO = (select 
CDT_ID_CANDIDATO
from
tb_cdt_candidato
where
USR_ID_USUARIO = '$idUsuario')";

// caso tipo de apresentação
            if ($tpApresentacao != NULL) {
                $sql .= " and " . self::getCompSqlBuscaTpApresentacao($tpApresentacao) . " ";
            }

//caso filtro
//processo
            if ($idProcesso != NULL) {
                $sql .= " and prc.`PRC_ID_PROCESSO` = '$idProcesso' ";
            }

//idTipo
            if ($idTipo != NULL) {
                $sql .= " and prc.`TIC_ID_TIPO_CARGO` = '$idTipo' ";
            }

//tpFormacao
            if ($tpFormacao != NULL) {
                $sql .= " and cur.TPC_ID_TIPO_CURSO = '$tpFormacao' ";
            }

//idCurso
            if ($idCurso != NULL) {
                $sql .= " and cur.CUR_ID_CURSO = '$idCurso' ";
            }

// ano edital
            if ($anoEdital != NULL) {
                $sql .= " and prc.PRC_ANO_EDITAL = '$anoEdital' ";
            }

// número edital
            if ($nrEdital != NULL) {
                $sql .= " and prc.PRC_NR_EDITAL = '$nrEdital' ";
            }

//finalização: caso de ordenação
            $sql .= " order by " . self::getCompSqlOrdenacaoTpApresentacao() . ", IPR_DT_INSCRICAO desc ";

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

//realizando iteração para recuperar as inscrições
            for ($i = 0; $i < $numLinhas; $i ++) {
//recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);
                $inscricaoTemp = new InscricaoProcesso($dados['IPR_ID_INSCRICAO'], $dados ['CDT_ID_CANDIDATO'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_DT_INSCRICAOSTR'], $dados['IPR_NR_ORDEM_INSC'], NULL, $dados['IPR_LOCALIZACAO_VALIDA'], $dados['IPR_VL_TOTAL_NOTA'], $dados['IPR_DS_OBS_NOTA'], $dados['IPR_ID_USR_AVALIADOR'], $dados['IPR_DT_AVALIACAO_S'], $dados['IPR_NR_CLASSIFICACAO_CAND'], $dados['IPR_ID_POLO_SELECIONADO'], $dados['IPR_CDT_SELECIONADO'], $dados['IPR_ST_AVAL_AUTOMATICA'], $dados['AAC_ID_AREA_CHAMADA'], $dados['IPR_ST_ANALISE'], $dados['IPR_ST_INSCRICAO'], $dados['RVC_ID_RESERVA_CHAMADA'], $dados['IPR_ST_AVAL_CEGA'], $dados['IPR_ID_ETAPA_SEL_NOTA']);

//setando campos herdados
                $inscricaoTemp->PRC_NR_ANO_EDITAL = $dados['PRC_NR_ANO_EDITAL'];
                $inscricaoTemp->TIC_NM_TIPO_CARGO = $dados['nmTipo'];
                $inscricaoTemp->TPC_NM_TIPO_CURSO = $dados['tipoCurso'];
                $inscricaoTemp->CUR_NM_CURSO = $dados['nmCurso'];
                $inscricaoTemp->PCH_DS_CHAMADA = $dados['PCH_DS_CHAMADA'];
                $inscricaoTemp->PCH_DT_ABERTURA = $dados['PCH_DT_ABERTURA_S'];
                $inscricaoTemp->PCH_DT_FECHAMENTO = $dados['PCH_DT_FECHAMENTO_S'];
                $inscricaoTemp->PCH_CHAMADA_ATIVA = $dados['PCH_CHAMADA_ATIVA'];
                $inscricaoTemp->PCH_DT_REG_RESUL_FINAL = $dados['PCH_DT_REG_RESUL_FINAL_S'];
                $inscricaoTemp->PCH_DT_FINALIZACAO = $dados['PCH_DT_FINALIZACAO_S'];
                $inscricaoTemp->PRC_DS_URL_EDITAL = $dados['PRC_DS_URL_EDITAL'];
                $inscricaoTemp->PRC_DS_PROCESSO = $dados['PRC_DS_PROCESSO'];


// setando apresentação
                $inscricaoTemp->setTpApresentacao();


//adicionando na matriz
                if (!isset($matRetorno[$inscricaoTemp->faseApresentacao])) {
                    $matRetorno[$inscricaoTemp->faseApresentacao] = array($inscricaoTemp);
                } else {
                    $matRetorno[$inscricaoTemp->faseApresentacao][] = $inscricaoTemp;
                }
            }
            return $matRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar inscrição do usuário.", $e);
        }
    }

    private function setTpApresentacao() {
        $this->faseApresentacao = Util::vazioNulo($this->PCH_DT_REG_RESUL_FINAL) ? self::$APRESENTACAO_ANDAMENTO : self::$APRESENTACAO_FINALIZADO;
    }

    private static function getCompSqlBuscaTpApresentacao($tpApresentacao) {
        if ($tpApresentacao == self::$APRESENTACAO_ANDAMENTO) {
            return self::getSqlRestricaoAndamento();
        } elseif ($tpApresentacao == self::$APRESENTACAO_FINALIZADO) {
            return self::getSqlRestricaoFinalizado();
        } else {
            throw new NegocioException("Tipo de apresentação desconhecido!");
        }
    }

    private static function getCompSqlOrdenacaoTpApresentacao() {
        return self::getSqlRestricaoAndamento() . " desc, " . self::getSqlRestricaoFinalizado() . " desc";
    }

    private static function getSqlRestricaoAndamento() {
        return "(PCH_DT_REG_RESUL_FINAL IS NULL)";
    }

    private static function getSqlRestricaoFinalizado() {
        return "(PCH_DT_REG_RESUL_FINAL IS NOT NULL)";
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @return string
     */
    public function getNmTituloPolo($chamada) {
        if ($chamada->isInscricaoMultipla() && $this->isCadastroReserva() && PoloInscricao::contarPolosPorInscricao($this->IPR_ID_INSCRICAO) > 1) {
            return "Polos";
        }
        return "Polo";
    }

    public function getFaseApresentacao() {
        return $this->faseApresentacao;
    }

    public function isAvaliada() {
        return $this->IPR_VL_TOTAL_NOTA !== NULL;
    }

    public function isSelecionada() {
        return $this->IPR_CDT_SELECIONADO !== NULL && $this->IPR_CDT_SELECIONADO == FLAG_BD_SIM;
    }

    public function isCadastroReserva() {
        return $this->IPR_ST_INSCRICAO !== NULL && $this->IPR_ST_INSCRICAO == self::$SIT_INSC_CAD_RESERVA;
    }

    public function isAprovada() {
        return $this->IPR_ST_INSCRICAO === NULL || $this->IPR_ST_INSCRICAO == self::$SIT_INSC_OK;
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @return boolean
     */
    public function isNotaFinal($chamada) {
        return $chamada->isFinalizada() || $chamada->isAguardandoFechamentoAuto();
    }

    public function isAvaliadaCegamente() {
        return $this->IPR_ST_AVAL_CEGA != NULL && $this->IPR_ST_AVAL_CEGA == self::$SIT_AVAL_CEGA_CONCLUIDA;
    }

    public function avalAutoConcluida() {
        if ($this->avalAutoConc == NULL) {
// carregando... 
            $this->carregaAvalAutoConc();
        }
        return $this->avalAutoConc;
    }

    private function carregaAvalAutoConc() {
        try {
            if (EtapaSelProc::possuiAvaliacaoAuto($this->PCH_ID_CHAMADA)) {
                $this->avalAutoConc = $this->IPR_ST_AVAL_AUTOMATICA != NULL && $this->IPR_ST_AVAL_AUTOMATICA == self::$SIT_AVAL_AUTO_CONCLUIDA;
            } else {
// não tem aval. auto. Logo, está concluído.
                $this->avalAutoConc = TRUE;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar situação da avaliação automática.", $e);
        }
    }

    public function htmlMsgNaoAvaliada() {
        return "<i>Ainda não avaliado</i>";
    }

    public function htmlMsgEliminada() {
        return "<i>Candidato Eliminado</i>";
    }

    public function getNotaFormatadaHtml() {
        if ($this->isAvaliada()) {
            return money_format("%i", $this->IPR_VL_TOTAL_NOTA);
        } else {
            return $this->htmlMsgNaoAvaliada();
        }
    }

    public function getNotaTabela() {
        if (!$this->isAvaliada()) {
            return Util::$STR_CAMPO_VAZIO;
        }
        if ($this->isAvaliada() && $this->isEliminada()) {
            $tpEliminacao = $this->getTpEliminacao();
            if ($tpEliminacao == self::$SIT_INSC_ELIMINADO) {
// eliminação manual
                return "EM";
            } elseif ($tpEliminacao == self::$SIT_INSC_AUTO_ELIMINADO) {
// eliminação automática
                return "EA";
            } elseif ($tpEliminacao == self::$SIT_INSC_FALTA_VAGAS_ELIMINADO) {
// falta de vagas ou cadastro de reserva
                return "FV";
            } else {
                throw new NegocioException("Código de eliminação não configurado para a tabela!");
            }
        }
        $temp = $this->getNotaFormatadaHtml();

// verificando cadastro de reserva
        return !$this->isCadastroReserva() ? $temp : $temp . " (CR)";
    }

    public function getDtAvaliacaoHtml() {
        if ($this->isAvaliada()) {
            return $this->IPR_DT_AVALIACAO;
        } else {
            return $this->htmlMsgNaoAvaliada();
        }
    }

    public function getDadosAvaliadorHtml() {
        if ($this->isAvaliada()) {
            return $this->getDadosAvaliador();
        } else {
            return $this->htmlMsgNaoAvaliada();
        }
    }

    public function permiteMostrarClassificacao() {
        if ($this->podeMostrarClas == NULL) {
//carregando... 
            $etapa = EtapaSelProc::buscarEtapaVigente($this->PCH_ID_CHAMADA);
            $this->podeMostrarClas = EtapaSelProc::permiteMostrarClassificacao($etapa);
        }
        return $this->podeMostrarClas;
    }

    public function getClassificacaoHtml() {
        if (!$this->isAvaliada()) {
            return $this->htmlMsgNaoAvaliada();
        } elseif ($this->isEliminada()) {
            return $this->htmlMsgEliminada();
        } elseif ($this->isAvaliada() && !$this->permiteMostrarClassificacao()) {
            return "<i>Ainda não definida</i>";
        } else {
            return $this->IPR_NR_CLASSIFICACAO_CAND;
        }
    }

    public function getPoloHtml() {
        if (!$this->isAvaliada()) {
            return $this->htmlMsgNaoAvaliada();
        } elseif ($this->isEliminada()) {
            return $this->htmlMsgEliminada();
        } elseif ($this->isAvaliada() && !$this->permiteMostrarClassificacao()) {
            return "<i>Ainda não definido</i>";
        } else {
            return $this->getDadosPoloSel();
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param string $dsAreaAtuacao 
     * @param string $dsReservaVaga
     * @return string
     */
    public function getHtmlGrupo($chamada, $dsAreaAtuacao, $dsReservaVaga) {
        if (!$this->isAvaliada()) {
            return $this->htmlMsgNaoAvaliada();
        } elseif ($this->isEliminada()) {
            return $this->htmlMsgEliminada();
        } elseif ($this->isAvaliada() && !$this->permiteMostrarClassificacao()) {
            return "<i>Ainda não definido</i>";
        } else {

// Imprimindo grupo: Caso não selecionado
            if (!$this->isSelecionada()) {
                return Util::$STR_CAMPO_VAZIO;
            }

// Analisando casos...
            if ($chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj() && $chamada->admiteReservaVagaObj()) {
// Tem polo, área e reserva
                return "{$this->getDadosPoloSel()} / $dsAreaAtuacao / $dsReservaVaga";
            } elseif ($chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj() && !$chamada->admiteReservaVagaObj()) {
// Tem polo, área e não tem reserva
                return "{$this->getDadosPoloSel()} / $dsAreaAtuacao";
            } elseif ($chamada->admitePoloObj() && !$chamada->admiteAreaAtuacaoObj() && $chamada->admiteReservaVagaObj()) {
// Tem polo, não tem área e reserva
                return "{$this->getDadosPoloSel()} / $dsReservaVaga";
            } elseif ($chamada->admitePoloObj() && !$chamada->admiteAreaAtuacaoObj() && !$chamada->admiteReservaVagaObj()) {
// Tem polo, não tem área e não tem reserva
                return $this->getDadosPoloSel();
            } elseif (!$chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj() && $chamada->admiteReservaVagaObj()) {
// Não tem polo, área e reserva
                return "$dsAreaAtuacao / $dsReservaVaga";
            } elseif (!$chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj() && !$chamada->admiteReservaVagaObj()) {
// Não tem polo, área e não tem reserva
                return $dsAreaAtuacao;
            } elseif (!$chamada->admitePoloObj() && !$chamada->admiteAreaAtuacaoObj() && $chamada->admiteReservaVagaObj()) {
// Não tem polo, não tem área e reserva
                return $dsReservaVaga;
            } elseif (!$chamada->admitePoloObj() && !$chamada->admiteAreaAtuacaoObj() && !$chamada->admiteReservaVagaObj()) {
// Não tem polo, não tem área e não tem reserva
                return "Geral";
            }
        }
    }

    public function getSelecionadoHtml() {
        if (!$this->isAvaliada()) {
            return $this->htmlMsgNaoAvaliada();
        } elseif ($this->isEliminada()) {
            return $this->htmlMsgEliminada();
        } elseif ($this->isAvaliada() && !$this->permiteMostrarClassificacao()) {
            return "<i>Ainda não definido</i>";
        } else {
            if (Util::vazioNulo($this->IPR_CDT_SELECIONADO)) {
                $this->IPR_CDT_SELECIONADO = FLAG_BD_NAO;
            }
            $ret = NGUtil::getDsSimNao($this->IPR_CDT_SELECIONADO);
            if ($this->isSelecionada() && $this->isCadastroReserva()) {
                $ret .= self::$MSG_CADASTRO_RESERVA;
            }
            return $ret;
        }
    }

    public function getObservacoesHtml() {
        if ($this->isAvaliada()) {
            return !Util::vazioNulo($this->IPR_DS_OBS_NOTA) ? $this->IPR_DS_OBS_NOTA : Util::$STR_CAMPO_VAZIO;
        } else {
            return $this->htmlMsgNaoAvaliada();
        }
    }

    public function isEliminada() {
        return $this->IPR_ST_INSCRICAO != NULL && ($this->IPR_ST_INSCRICAO == InscricaoProcesso::$SIT_INSC_ELIMINADO || $this->IPR_ST_INSCRICAO == InscricaoProcesso::$SIT_INSC_AUTO_ELIMINADO || $this->IPR_ST_INSCRICAO == InscricaoProcesso::$SIT_INSC_FALTA_VAGAS_ELIMINADO);
    }

    public function getTpEliminacao() {
        if ($this->isEliminada()) {
            return $this->IPR_ST_INSCRICAO;
        }
        throw new NegocioException("Inscrição não eliminada!");
    }

    public function getDadosAvaliador() {
// inscriçao nao avaliada
        if (!$this->isAvaliada()) {
            return "";
        }

// necessidade de carregar dados
        if (Util::vazioNulo($this->USR_DS_AVALIADOR)) {
            $this->carregaDadosAvaliador();
        }

// retornando dado
        return $this->USR_DS_AVALIADOR;
    }

    private function carregaDadosAvaliador() {
        try {
            $usu = Usuario:: buscarUsuarioPorId($this->IPR_ID_USR_AVALIADOR);
            $this->USR_DS_AVALIADOR = "{$usu->getUSR_DS_NOME()} (Código {$usu->getUSR_ID_USUARIO()})";
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar dados do avaliador.", $e);
        }
    }

    /**
     * Funcao que retorna string com sql que marca avaliaçao automatica dos candidatos como ok.
     * FUNCAO DE PROCESSAMENTO INTERNO. NAO CHAMAR FORA DO PROCESSO DE CLASSIFICACAO.
     * @param int $idChamada
     * @return string
     */
    public static function CLAS_getStrSqlSitAutoOk($idChamada) {
        return "update tb_ipr_inscricao_processo set ipr_st_aval_automatica = '" .
                self::$SIT_AVAL_AUTO_CONCLUIDA
                . "' where PCH_ID_CHAMADA = '$idChamada'";
    }

    public static function getStrSqlSitAutoPendente($idProcesso) {
        return "update tb_ipr_inscricao_processo set ipr_st_aval_automatica = '" .
                self::$SIT_AVAL_AUTO_PENDENTE
                . "' where PRC_ID_PROCESSO = $idProcesso";
    }

    /** Funcao interna que executa o processamento da classificacao
     * e selecao dos candidatos.
     * FUNCAO DE PROCESSAMENTO INTERNO.
     * 
     * @param EtapaSelProc $etapa
     */
    private static function CLAS_classificarSelecionar($etapa) {
        $cmds = array();
        $idUsuario = getIdUsuarioLogado();
        $chamada = buscarChamadaPorIdCT($etapa->getPCH_ID_CHAMADA(), $etapa->getPRC_ID_PROCESSO());
        $conexao = NGUtil::getConexao();

// Flags importantes
        $stInscFaltaVagas = InscricaoProcesso::$SIT_INSC_FALTA_VAGAS_ELIMINADO;
        $stInscCadReserva = InscricaoProcesso::$SIT_INSC_CAD_RESERVA;
        $stInscOk = InscricaoProcesso::$SIT_INSC_OK;

// removendo dados anteriores de notas da etapa a ser reavaliada
        NotasEtapaSelInsc::CLAS_addSqlsLimparNotas($etapa, $cmds);

// Revertendo eliminação por falta de vagas e reserva de vagas
        $cmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_ST_INSCRICAO = '$stInscOk',
IPR_DS_OBS_NOTA = NULL
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO = '$stInscFaltaVagas' or IPR_ST_INSCRICAO = '$stInscCadReserva')";


// limpando dados anteriores da tabela de inscrição
        $cmds [] = "update tb_ipr_inscricao_processo ipr 
set 
ipr_vl_total_nota = NULL,
ipr_id_usr_avaliador = NULL,
ipr_dt_avaliacao = NULL,
ipr_cdt_selecionado = NULL,
ipr_nr_classificacao_cand = NULL,
ipr_id_polo_selecionado = NULL
where
pch_id_chamada = '{$etapa->getPCH_ID_CHAMADA()}'";


// gerando sql de sumarizacao de notas
// OBS: Existem alguns pontos de SQL SIMILIAR que devem ser alterados, caso haja mudanças aqui
// Pesquisa: #sqlNotas
        $avalAtiva = RelNotasInsc::$SIT_ATIVA;
        $cmds [] = "update tb_ipr_inscricao_processo ipr 
set 
ipr_vl_total_nota = coalesce((select 
sum(RNI_VL_NOTA_NORMALIZADA)
from
tb_rni_rel_notas_insc rni
where
ipr.IPR_ID_INSCRICAO = rni.IPR_ID_INSCRICAO
and RNI_ST_AVALIACAO = '$avalAtiva'
and CAP_ID_CATEGORIA_AVAL IN 
(select CAP_ID_CATEGORIA_AVAL from tb_cap_categoria_aval_proc 
where EAP_ID_ETAPA_AVAL_PROC = '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')), 0),
ipr_id_usr_avaliador = '$idUsuario',
ipr_dt_avaliacao = now()
where
pch_id_chamada = '{$etapa->getPCH_ID_CHAMADA()}'";

// tratando critérios de eliminação automática da etapa em questão
        MacroConfProc::CLAS_addSqlsAplicaCritEliminacao($etapa, $cmds);

// tratando critérios de classificação, incluindo os critérios de desempate da etapa em questão
        MacroConfProc::CLAS_addSqlsAplicaClassifComDesempate($chamada, $etapa, $cmds);

// sql de escolha de polo selecionado, quando há admissão de polo
        if ($chamada->admitePoloObj()) {
            if (!$chamada->isInscricaoMultipla()) {
// sql 
                $cmds [] = "UPDATE tb_ipr_inscricao_processo ipr 
SET 
IPR_ID_POLO_SELECIONADO = (select 
pol_id_polo
from
tb_pin_polo_inscricao pin
where
ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO)
where
pch_id_chamada = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')";
            } else {

// limpando possíveis remanejamentos
                $cmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_ST_REMANEJAMENTO = NULL
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and IPR_ST_REMANEJAMENTO REGEXP '^[0-9]+$'";

// sql
                $cmds [] = "UPDATE tb_ipr_inscricao_processo ipr 
SET 
IPR_ID_POLO_SELECIONADO = (select 
pol_id_polo
from
tb_pin_polo_inscricao pin
where
ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO order by PIN_NR_ORDEM limit 0,1)
where
pch_id_chamada = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')";
            }
        }



// tratando critério de seleção para a etapa
        MacroConfProc::CLAS_addSqlsAplicaCritSelecao($chamada, $etapa, $cmds);

// registrando notas da etapa
        NotasEtapaSelInsc::CLAS_addSqlsRegistroNotaEtapa($chamada->getPCH_ID_CHAMADA(), $etapa->getESP_ID_ETAPA_SEL(), $cmds);



// se for a última etapa da chamada, então processando resultado final
        if ($etapa->isUltimaEtapa()) {

            /**
             * Persistindo alterações de etapa no BD e limpando array
             * 
             * Note que a partir daqui será processado o resultado final e a etapa só será validada via flag 
             * se este segundo processamento ocorrer com sucesso (Veja o último comando inserido neste IF)
             */
            $conexao->execTransacaoArray($cmds);
            $cmds = NULL;
            $cmds = array();


// limpando dados de avaliação da etapa 
            $cmds [] = "update tb_ipr_inscricao_processo ipr 
set 
ipr_vl_total_nota = NULL,
ipr_cdt_selecionado = NULL,
ipr_nr_classificacao_cand = NULL
where
pch_id_chamada = '{$etapa->getPCH_ID_CHAMADA()}'";


// processando nota final
            MacroConfProc::CLAS_addSqlsAplicaFormulaFinal($chamada, $cmds);

// tratando critérios de classificação, incluindo os critérios de desempate para resultado final
            MacroConfProc::CLAS_addSqlsAplicaClassifComDesempate($chamada, NULL, $cmds);

// recuperando dados adicionais para os demais processamentos
// carregando reservas de vaga, no caso de admissão
            if ($chamada->admiteReservaVagaObj()) {
// carregando vagas de reserva
                $listaReservaVaga = buscarReservaVagaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaAtiva());
            } else {
                $listaReservaVaga = NULL;
            }

// selecionando de acordo com a quantidade de vagas
            self::CLAS_selecionaAcordoVagasResulFinal($chamada, $cmds, $listaReservaVaga);

// sumarizando preenchimento das vagas
            self::CLAS_sumarizacaoVagas($chamada, $cmds, $listaReservaVaga);

//            NGUtil::imprimeVetorDepuracao($cmds);
//            exit;
// tratando cadastro de reservas
            MacroConfProc::CLAS_addSqlsAplicaCadastroReserva($chamada, $cmds);

// limpando polos de quem não foi selecionado
            $cmds [] = "update tb_ipr_inscricao_processo ipr 
set 
ipr_id_polo_selecionado = NULL
where
pch_id_chamada = '{$etapa->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NOT NULL and IPR_ST_INSCRICAO != '$stInscOk')";


            /**
             * Persistindo alterações do resultado final no BD e limpando array
             * 
             * Note que a partir daqui será processado as sobras (remanejamentos) de vagas e a etapa só será validada via flag 
             * se este terceiro processamento ocorrer com sucesso (Veja o último comando inserido neste IF)
             */
            $conexao->execTransacaoArray($cmds);
            $cmds = NULL;
            $cmds = array();


// @todo Implementar comutação de vagas no caso de reserva de vagas
// 
// 
// 
// implementando prioridade de preenchimento de vagas restantes quando é permitido a escolha de múltiplos polos
// OBS: Esta função realiza transações direto no banco de dados!
            self::CLAS_implementaPrioridadeMultiplosPolos($chamada, $listaReservaVaga);

// marcando flags de etapa apenas neste segundo momento para evitar erros
            $cmds [] = EtapaSelProc::CLAS_getSqlClassifConc($etapa->getESP_ID_ETAPA_SEL(), getIdUsuarioLogado());

//            exit;
// persistindo no BD
            $conexao->execTransacaoArray($cmds);
        } else {

// marcando flags de etapa
            $cmds [] = EtapaSelProc::CLAS_getSqlClassifConc($etapa->getESP_ID_ETAPA_SEL(), getIdUsuarioLogado());

// persistindo no BD
            $conexao->execTransacaoArray($cmds);
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $cmds Array onde deve ser adicionado os comandos para sumarização
     * @param ReservaVagaChamada $listaReservaVaga Array com reservas de vaga da chamada
     */
    private static function CLAS_sumarizacaoVagas($chamada, &$cmds, $listaReservaVaga) {
        PoloChamada::CLAS_getSqlSumarizaVagas($chamada, $cmds);
        AreaAtuChamada::CLAS_getSqlSumarizaVagas($chamada, $cmds);
        PoloAreaChamada::CLAS_getSqlSumarizaVagas($chamada, $cmds);
        ReservaVagaPolo::CLAS_getSqlSumarizaVagas($chamada, $cmds, $listaReservaVaga);
        ReservaVagaArea::CLAS_getSqlSumarizaVagas($chamada, $cmds, $listaReservaVaga);
        ReservaPoloArea::CLAS_getSqlSumarizaVagas($chamada, $cmds, $listaReservaVaga);
        ReservaVagaChamada::CLAS_getSqlSumarizaVagas($chamada, $cmds, $listaReservaVaga);
    }

    /**
     * 
     * Esta função adiciona em $arrayCmds os sqls responsáveis por selecionar os candidatos de acordo com a
     * quantidade de vagas informada na chamada
     * 
     * @param ProcessoChamada $chamada Chamada do edital
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     * @param ReservaVagaChamada $listaReservaVaga Array com todas as reservas de vaga da chamada
     */
    private static function CLAS_selecionaAcordoVagasResulFinal($chamada, &$arrayCmds, $listaReservaVaga) {
// Flags importantes
        $inscOk = self::$SIT_INSC_OK;
        $flagCdtSel = FLAG_BD_SIM;


// TRATANDO SELEÇÃO AQUI
// 
// polos e área de atuação ao mesmo tempo
        if ($chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj()) {
// recuperando dados de polo e área
            $polosAreasCham = buscarPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA());

            if ($chamada->admiteReservaVagaObj()) {
// carregando vagas de polo e área
                $reservaPoloArea = buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO_AREA);

//percorrendo áreas e polos e executando sql
                foreach ($polosAreasCham as $poloArea) {
// contador para a quantidade de reservas de vaga
                    $contaReserva = 0;

// percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {

                        $qtReserva = ReservaPoloArea::getValorIndiceBusca($reservaPoloArea, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_POLO_AREA, $poloArea->getPOL_ID_POLO(), $poloArea->ARC_ID_SUBAREA_CONH), $reserva->getRVC_ID_RESERVA_CHAMADA());
                        $contaReserva += $qtReserva;

// sql para reservas de vagas
                        $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtReserva) tmp)";
                    }

// sql para público geral
                    $qtReserva = $poloArea->getPAC_QT_VAGAS() - $contaReserva;
                    $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA IS NULL
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA IS NULL
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtReserva) tmp)";
                }//
//
            } else { // SEM RESERVA DE VAGAS
//percorrendo áreas e polos e executando sql
                foreach ($polosAreasCham as $poloArea) {

                    $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , {$poloArea->getPAC_QT_VAGAS()}) tmp)";
                }
            }
        } elseif ($chamada->admitePoloObj()) { // APENAS POLO
// recuperar polos da chamada
            $polosCham = PoloChamada::buscarPoloVagasPorChamada($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo());


            if ($chamada->admiteReservaVagaObj()) {
// carregando vagas do polo
                $reservaPolo = buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO);

//percorrendo polos e executando sql
                foreach ($polosCham as $idPoloCham => $qtVagasPolo) {
// contador para a quantidade de reservas de vaga
                    $contaReserva = 0;

// percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {

                        $qtReserva = ReservaPoloArea::getValorIndiceBusca($reservaPolo, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_POLO, $idPoloCham), $reserva->getRVC_ID_RESERVA_CHAMADA());
                        $contaReserva += $qtReserva;

// sql para reservas de vagas
                        $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtReserva) tmp)";
                    }

// sql para público geral
                    $qtReserva = $qtVagasPolo - $contaReserva;
                    $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
and RVC_ID_RESERVA_CHAMADA IS NULL
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
and RVC_ID_RESERVA_CHAMADA IS NULL
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtReserva) tmp)";
                }//
//
            } else { // SEM RESERVA DE VAGAS
//percorrendo polos e executando sql
                foreach ($polosCham as $idPoloCham => $qtVagas) {

                    $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtVagas) tmp)";
                }
            }
        } elseif ($chamada->admiteAreaAtuacaoObj()) {  // APENAS ÁREA DE ATUAÇÃO
// recuperar areas de atuacao
            $areasAtuCham = AreaAtuChamada::buscarAreaAtuCompPorChamada($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva());

            if ($chamada->admiteReservaVagaObj()) {
// carregando vagas da área
                $reservaArea = buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_AREA);

//percorrendo áreas e executando sql
                foreach ($areasAtuCham as $areaChamada) {
// contador para a quantidade de reservas de vaga
                    $contaReserva = 0;

// percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {

                        $qtReserva = ReservaPoloArea::getValorIndiceBusca($reservaArea, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_AREA, NULL, $areaChamada->getARC_ID_SUBAREA_CONH()), $reserva->getRVC_ID_RESERVA_CHAMADA());
                        $contaReserva += $qtReserva;

// sql para reservas de vagas
                        $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtReserva) tmp)";
                    }

// sql para público geral
                    $qtReserva = $areaChamada->getAAC_QT_VAGAS() - $contaReserva;
                    $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA IS NULL
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA IS NULL
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtReserva) tmp)";
                }
//
//
            } else { // SEM RESERVA DE VAGAS
//percorrendo areas e executando sql
                foreach ($areasAtuCham as $areaChamada) {

// sql para reservas de vagas
                    $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , {$areaChamada->getAAC_QT_VAGAS()}) tmp)";
                }
            }
        } else { // Sem polo e sem área
            if ($chamada->admiteReservaVagaObj()) {
// contador para a quantidade de reservas de vaga
                $contaReserva = 0;

// percorrendo reservas de vagas
                foreach ($listaReservaVaga as $reserva) {

                    $qtReserva = $reserva->getRVC_QT_VAGAS_RESERVADAS();
                    $contaReserva += $qtReserva;

// sql para reservas de vagas
                    $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtReserva) tmp) ";
                }


// sql para público geral
                $qtReserva = $chamada->getPCH_QT_VAGAS() - $contaReserva;
                $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and RVC_ID_RESERVA_CHAMADA IS NULL
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and RVC_ID_RESERVA_CHAMADA IS NULL
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtReserva) tmp) ";
//
//
            } else {  // SEM RESERVA DE VAGAS
// selecionando pelo numero total de vagas
                $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
set 
IPR_CDT_SELECIONADO = '$flagCdtSel'
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$inscOk')
and IPR_NR_CLASSIFICACAO_CAND <= (select 
PCH_QT_VAGAS
from
tb_pch_processo_chamada pch
where
pch.PCH_ID_CHAMADA = ipr.PCH_ID_CHAMADA) ";
            }
        }
    }

    /**
     * 
     * Esta função adiciona em $arrayCmds os sqls responsáveis por implementar a prioridade de
     * preenchimento de vagas restantes quando é permitido a escolha de múltiplos polos
     * 
     * @param ProcessoChamada $chamada Chamada do edital
     * @param ReservaVagaChamada $listaReservaVaga Array com todas as reservas de vaga da chamada
     */
    private static function CLAS_implementaPrioridadeMultiplosPolos($chamada, $listaReservaVaga) {
        if ($chamada->isInscricaoMultipla()) {
// recuperando conexão
            $conexao = NGUtil::getConexao();

// Flags importantes
            $stInscCadReserva = InscricaoProcesso::$SIT_INSC_CAD_RESERVA;
            $stInscFaltaVagas = InscricaoProcesso::$SIT_INSC_FALTA_VAGAS_ELIMINADO;
            $stInscOk = InscricaoProcesso::$SIT_INSC_OK;

            for ($i = 2; $i <= $chamada->getPCH_NR_MAX_OPCAO_POLO(); $i++) {
                $stRemanejamento = self::$REMANEJAMENTO_POLO_MULTIPLO + $i;

// polos e área de atuação ao mesmo tempo
                if ($chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj()) {
// recuperando dados de polo e área
                    $polosAreasCham = buscarPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA());

// caso de ter reserva de vaga
                    if ($chamada->admiteReservaVagaObj()) {

//percorrendo áreas e polos e executando sql
                        foreach ($polosAreasCham as $poloArea) {

// percorrendo vagas
                            foreach ($listaReservaVaga as $reserva) {

// buscando sobras
                                $sqlSobra = ReservaPoloArea::CLAS_getSqlSobraVagas($chamada->getPCH_ID_CHAMADA(), $poloArea->getPOL_ID_POLO(), $poloArea->getAAC_ID_AREA_CHAMADA(), $reserva->getRVC_ID_RESERVA_CHAMADA());
                                $resp = $conexao->execSqlComRetorno($sqlSobra);
                                $qtSobra = ConexaoMysql::getResult(ProcessoChamada::$SQL_RET_SOBRA_VAGAS, $resp);

// passando, caso não tenha sobra
                                if (Util::vazioNulo($qtSobra)) {
                                    continue;
                                }

// criando sql de sobras
                                $arrayCmds = NULL;
                                $arrayCmds = array();
                                $arrayCmds [] = "update tb_ipr_inscricao_processo
set ipr_id_polo_selecionado = '{$poloArea->getPOL_ID_POLO()}',
IPR_ST_INSCRICAO = '$stInscOk',
IPR_ST_REMANEJAMENTO = '$stRemanejamento'
WHERE
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
AND RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo ipr
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
AND RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
and (select count(*) from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
and POL_ID_POLO = '{$poloArea->getPOL_ID_POLO()}' and PIN_NR_ORDEM = '$i') > 0
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtSobra) tmp)";

// adicionando resumarização e executando sqls
                                ReservaPoloArea::CLAS_getSqlSumarizaVagasInd($chamada, $arrayCmds, $poloArea->getPOL_ID_POLO(), $poloArea->getAAC_ID_AREA_CHAMADA(), $reserva->getRVC_ID_RESERVA_CHAMADA());
                                $conexao->execTransacaoArray($arrayCmds);
                            }

// sqls para público geral
// buscando sobra de vagas do público geral
                            $sqlSobra = ReservaPoloArea::CLAS_getSqlSobraVagasPubGeral($chamada->getPCH_ID_CHAMADA(), $poloArea->getPOL_ID_POLO(), $poloArea->getAAC_ID_AREA_CHAMADA());
                            $resp = $conexao->execSqlComRetorno($sqlSobra);
                            $qtSobra = ConexaoMysql::getResult(ProcessoChamada::$SQL_RET_SOBRA_VAGAS, $resp);

// passando, caso não tenha sobra
                            if (Util::vazioNulo($qtSobra)) {
                                continue;
                            }

// criando sql de sobras
                            $arrayCmds = NULL;
                            $arrayCmds = array();
                            $arrayCmds [] = "update tb_ipr_inscricao_processo
set ipr_id_polo_selecionado = '{$poloArea->getPOL_ID_POLO()}',
IPR_ST_INSCRICAO = '$stInscOk',
IPR_ST_REMANEJAMENTO = '$stRemanejamento'
WHERE
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
AND RVC_ID_RESERVA_CHAMADA IS NULL
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo ipr
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
AND RVC_ID_RESERVA_CHAMADA IS NULL
and (select count(*) from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
and POL_ID_POLO = '{$poloArea->getPOL_ID_POLO()}' and PIN_NR_ORDEM = '$i') > 0
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtSobra) tmp)";

// executando sqls
                            $conexao->execTransacaoArray($arrayCmds);
                        }//
//
                    } else { // SEM RESERVA DE VAGAS
//percorrendo áreas e polos e executando sql
                        foreach ($polosAreasCham as $poloArea) {

// buscando sobras
                            $sqlSobra = PoloAreaChamada::CLAS_getSqlSobraVagas($chamada->getPCH_ID_CHAMADA(), $poloArea->getPOL_ID_POLO(), $poloArea->getAAC_ID_AREA_CHAMADA());
//                            print_r($sqlSobra);
                            $resp = $conexao->execSqlComRetorno($sqlSobra);
                            $qtSobra = ConexaoMysql::getResult(ProcessoChamada::$SQL_RET_SOBRA_VAGAS, $resp);

//                            print_r($poloArea->POL_DS_POLO . " " . $poloArea->ARC_NM_SUBAREA_CONH . ": " . $qtSobra);
//                            echo '<br/>';
// passando, caso não tenha sobra
                            if (Util::vazioNulo($qtSobra)) {
                                continue;
                            }

// criando sql de sobras
                            $arrayCmds = NULL;
                            $arrayCmds = array();
                            $arrayCmds [] = "update tb_ipr_inscricao_processo
set ipr_id_polo_selecionado = '{$poloArea->getPOL_ID_POLO()}',
IPR_ST_INSCRICAO = '$stInscOk',
IPR_ST_REMANEJAMENTO = '$stRemanejamento'
WHERE
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo ipr
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
and (select count(*) from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
and POL_ID_POLO = '{$poloArea->getPOL_ID_POLO()}' and PIN_NR_ORDEM = '$i') > 0
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtSobra) tmp)";

// adicionando resumarização e executando sqls
                            PoloAreaChamada::CLAS_getSqlSumarizaVagasInd($chamada, $arrayCmds, $poloArea->getPOL_ID_POLO(), $poloArea->getAAC_ID_AREA_CHAMADA());
                            $conexao->execTransacaoArray($arrayCmds);
                        }
                    }
                } elseif ($chamada->admitePoloObj()) { // APENAS POLO
// recuperar polos da chamada
                    $polosCham = PoloChamada::buscarPoloVagasPorChamada($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo());

                    if ($chamada->admiteReservaVagaObj()) {

//percorrendo polos e executando sql
                        foreach (array_keys($polosCham) as $idPoloCham) {

// percorrendo vagas
                            foreach ($listaReservaVaga as $reserva) {

// buscando sobras
                                $sqlSobra = ReservaVagaPolo::CLAS_getSqlSobraVagas($chamada->getPCH_ID_CHAMADA(), $idPoloCham, $reserva->getRVC_ID_RESERVA_CHAMADA());
                                $resp = $conexao->execSqlComRetorno($sqlSobra);
                                $qtSobra = ConexaoMysql::getResult(ProcessoChamada::$SQL_RET_SOBRA_VAGAS, $resp);

// passando, caso não tenha sobra
                                if (Util::vazioNulo($qtSobra)) {
                                    continue;
                                }

// criando sql de sobras
                                $arrayCmds = NULL;
                                $arrayCmds = array();
                                $arrayCmds [] = "update tb_ipr_inscricao_processo
set ipr_id_polo_selecionado = '$idPoloCham',
IPR_ST_INSCRICAO = '$stInscOk',
IPR_ST_REMANEJAMENTO = '$stRemanejamento'
WHERE
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo ipr
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
and (select count(*) from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
and POL_ID_POLO = '$idPoloCham' and PIN_NR_ORDEM = '$i') > 0
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtSobra) tmp)";

// adicionando resumarização e executando sqls
                                ReservaVagaPolo::CLAS_getSqlSumarizaVagasInd($chamada, $arrayCmds, $idPoloCham, $reserva->getRVC_ID_RESERVA_CHAMADA());
                                $conexao->execTransacaoArray($arrayCmds);
                            }

// sqls para público geral
// buscando sobra de vagas do público geral
                            $sqlSobra = ReservaVagaPolo::CLAS_getSqlSobraVagasPubGeral($chamada->getPCH_ID_CHAMADA(), $idPoloCham);
                            $resp = $conexao->execSqlComRetorno($sqlSobra);
                            $qtSobra = ConexaoMysql::getResult(ProcessoChamada::$SQL_RET_SOBRA_VAGAS, $resp);

// passando, caso não tenha sobra
                            if (Util::vazioNulo($qtSobra)) {
                                continue;
                            }

// criando sql de sobras
                            $arrayCmds = NULL;
                            $arrayCmds = array();
                            $arrayCmds [] = "update tb_ipr_inscricao_processo
set ipr_id_polo_selecionado = '$idPoloCham',
IPR_ST_INSCRICAO = '$stInscOk',
IPR_ST_REMANEJAMENTO = '$stRemanejamento'
WHERE
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND RVC_ID_RESERVA_CHAMADA IS NULL
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo ipr
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
AND RVC_ID_RESERVA_CHAMADA IS NULL
and (select count(*) from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
and POL_ID_POLO = '$idPoloCham' and PIN_NR_ORDEM = '$i') > 0
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtSobra) tmp)";

// executando sqls
                            $conexao->execTransacaoArray($arrayCmds);
                        }//
//
                    } else { // SEM RESERVA DE VAGAS
//percorrendo polos e executando sql
                        foreach (array_keys($polosCham) as $idPoloCham) {

// buscando sobras
                            $sqlSobra = PoloChamada::CLAS_getSqlSobraVagas($chamada->getPCH_ID_CHAMADA(), $idPoloCham);
                            $resp = $conexao->execSqlComRetorno($sqlSobra);
                            $qtSobra = ConexaoMysql::getResult(ProcessoChamada::$SQL_RET_SOBRA_VAGAS, $resp);

// passando, caso não tenha sobra
                            if (Util::vazioNulo($qtSobra)) {
                                continue;
                            }

// criando sql de sobras
                            $arrayCmds = NULL;
                            $arrayCmds = array();
                            $arrayCmds [] = "update tb_ipr_inscricao_processo
set ipr_id_polo_selecionado = '$idPoloCham',
IPR_ST_INSCRICAO = '$stInscOk',
IPR_ST_REMANEJAMENTO = '$stRemanejamento'
WHERE
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
and IPR_ID_INSCRICAO in (select 
IPR_ID_INSCRICAO
from
(select 
IPR_ID_INSCRICAO
from
tb_ipr_inscricao_processo ipr
where
PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
AND (IPR_ST_INSCRICAO = '$stInscCadReserva' or IPR_ST_INSCRICAO = '$stInscFaltaVagas')
and (select count(*) from tb_pin_polo_inscricao pin where pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
and POL_ID_POLO = '$idPoloCham' and PIN_NR_ORDEM = '$i') > 0
order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
limit 0 , $qtSobra tmp)";

// adicionando resumarização e executando sqls
                            PoloChamada::CLAS_getSqlSumarizaVagasInd($chamada, $arrayCmds, $idPoloCham);
                            $conexao->execTransacaoArray($arrayCmds);
                        }
                    }
                }
            }
        }
    }

    public function getDadosPoloSel() {
// inscriçao nao avaliada ou não selecionada
        if (!$this->isAvaliada() || !$this->isSelecionada()) {
            return Util::$STR_CAMPO_VAZIO;
        }

// necessidade de carregar dados
        if (Util::vazioNulo($this->POL_DS_POLO_SELECIONADO)) {
            $this->carregaDadosPoloSel();
        }

// retornando dado
        return $this->POL_DS_POLO_SELECIONADO;
    }

    private function carregaDadosPoloSel() {
        try {
            if ($this->isCadastroReserva()) {
                $polos = PoloInscricao::buscarPoloPorInscricao($this->IPR_ID_INSCRICAO);
                $this->POL_DS_POLO_SELECIONADO = arrayParaStr($polos);
            } else {
                $polo = Polo::buscarPoloPorId($this->IPR_ID_POLO_SELECIONADO);
                $this->POL_DS_POLO_SELECIONADO = $polo->getPOL_DS_POLO();
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar dados do polo selecionado.", $e);
        }
    }

    /**
     * Retorna a etapa na qual a nota atual se refere
     * 
     */
    public function getNomeEtapaNota() {
// Tem a etapa da nota
        if (!Util::vazioNulo($this->IPR_ID_ETAPA_SEL_NOTA)) {

// precisa carregar?
            if ($this->nmEtapaNota === NULL) {
                $etapa = EtapaSelProc::buscarEtapaSelPorId($this->IPR_ID_ETAPA_SEL_NOTA);
                $this->nmEtapaNota = $etapa->getNomeEtapa();
            }
            return $this->nmEtapaNota;
        }
    }

    /* GET FIELDS FROM TABLE */

    function getIPR_ID_INSCRICAO() {

        return $this->IPR_ID_INSCRICAO;
    }

    /* End of get IPR_ID_INSCRICAO */

    function getCDT_ID_CANDIDATO() {

        return $this->CDT_ID_CANDIDATO;
    }

    /* End of get CDT_ID_CANDIDATO */

    function getPRC_ID_PROCESSO() {

        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getPCH_ID_CHAMADA() {

        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getIPR_DT_INSCRICAO() {
        return $this->IPR_DT_INSCRICAO;
    }

    /* End of get IPR_DT_INSCRICAO */

    function getIPR_NR_ORDEM_INSC() {
        return $this->IPR_NR_ORDEM_INSC;
    }

    /* End of get IPR_NR_ORDEM_INSC */

    function getIPR_LOCALIZACAO_VALIDA() {
        return $this->IPR_LOCALIZACAO_VALIDA;
    }

    /* End of get IPR_LOCALIZACAO_VALIDA */

    function getIPR_VL_TOTAL_NOTA() {
        return $this->IPR_VL_TOTAL_NOTA;
    }

    /* End of get IPR_VL_TOTAL_NOTA */

    function getIPR_DS_OBS_NOTA() {
        return $this->IPR_DS_OBS_NOTA;
    }

    /* End of get IPR_DS_OBS_NOTA */

    function getIPR_ID_USR_AVALIADOR() {
        return $this->IPR_ID_USR_AVALIADOR;
    }

    /* End of get IPR_ID_USR_AVALIADOR */

    function getIPR_DT_AVALIACAO() {
        return $this->IPR_DT_AVALIACAO;
    }

    /* End of get IPR_DT_AVALIACAO */

    function getIPR_NR_CLASSIFICACAO_CAND() {
        return $this->IPR_NR_CLASSIFICACAO_CAND;
    }

    /* End of get IPR_NR_CLASSIFICACAO_CAND */

    function getIPR_ID_POLO_SELECIONADO() {
        return $this->IPR_ID_POLO_SELECIONADO;
    }

    /* End of get IPR_ID_POLO_SELECIONADO */

    function getIPR_CDT_SELECIONADO() {
        return $this->IPR_CDT_SELECIONADO;
    }

    /* End of get IPR_CDT_SELECIONADO */

    function getIPR_ST_AVAL_AUTOMATICA() {
        return $this->IPR_ST_AVAL_AUTOMATICA;
    }

    /* End of get IPR_ST_AVAL_AUTOMATICA */

    function getAAC_ID_AREA_CHAMADA() {
        return $this->AAC_ID_AREA_CHAMADA;
    }

    /* End of get AAC_ID_AREA_CHAMADA */

    function getIPR_ST_ANALISE() {
        return $this->IPR_ST_ANALISE;
    }

    /* End of get IPR_ST_ANALISE */

    function getIPR_ST_INSCRICAO() {
        return $this->IPR_ST_INSCRICAO;
    }

    /* End of get IPR_ST_INSCRICAO */

    function getRVC_ID_RESERVA_CHAMADA() {
        return $this->RVC_ID_RESERVA_CHAMADA;
    }

    /* End of get RVC_ID_RESERVA_CHAMADA */

    function getIPR_ID_ETAPA_SEL_NOTA() {
        return $this->IPR_ID_ETAPA_SEL_NOTA;
    }

    /* SET FIELDS FROM TABLE */

    function setIPR_ID_INSCRICAO($value) {
        $this->IPR_ID_INSCRICAO = $value;
    }

    /* End of SET IPR_ID_INSCRICAO */

    function setCDT_ID_CANDIDATO($value) {
        $this->CDT_ID_CANDIDATO = $value;
    }

    /* End of SET CDT_ID_CANDIDATO */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setIPR_DT_INSCRICAO($value) {
        $this->IPR_DT_INSCRICAO = $value;
    }

    /* End of SET IPR_DT_INSCRICAO */

    function setIPR_NR_ORDEM_INSC($value) {
        $this->IPR_NR_ORDEM_INSC = $value;
    }

    /* End of SET IPR_NR_ORDEM_INSC */

    function setIPR_LOCALIZACAO_VALIDA($value) {
        $this->IPR_LOCALIZACAO_VALIDA = $value;
    }

    /* End of SET IPR_LOCALIZACAO_VALIDA */

    function setIPR_VL_TOTAL_NOTA($value) {
        $this->IPR_VL_TOTAL_NOTA = $value;
    }

    /* End of SET IPR_VL_TOTAL_NOTA */

    function setIPR_DS_OBS_NOTA($value) {
        $this->IPR_DS_OBS_NOTA = $value;
    }

    /* End of SET IPR_DS_OBS_NOTA */

    function setIPR_ID_USR_AVALIADOR($value) {
        $this->IPR_ID_USR_AVALIADOR = $value;
    }

    /* End of SET IPR_ID_USR_AVALIADOR */

    function setIPR_DT_AVALIACAO($value) {
        $this->IPR_DT_AVALIACAO = $value;
    }

    /* End of SET IPR_DT_AVALIACAO */

    function setIPR_NR_CLASSIFICACAO_CAND($value) {
        $this->IPR_NR_CLASSIFICACAO_CAND = $value;
    }

    /* End of SET IPR_NR_CLASSIFICACAO_CAND */

    function setIPR_ID_POLO_SELECIONADO($value) {
        $this->IPR_ID_POLO_SELECIONADO = $value;
    }

    /* End of SET IPR_ID_POLO_SELECIONADO */

    function setIPR_CDT_SELECIONADO($value) {
        $this->IPR_CDT_SELECIONADO = $value;
    }

    /* End of SET IPR_CDT_SELECIONADO */

    function setIPR_ST_AVAL_AUTOMATICA($value) {
        $this->IPR_ST_AVAL_AUTOMATICA = $value;
    }

    /* End of SET IPR_ST_AVAL_AUTOMATICA */

    function setAAC_ID_AREA_CHAMADA($value) {
        $this->AAC_ID_AREA_CHAMADA = $value;
    }

    /* End of SET AAC_ID_AREA_CHAMADA */

    function setIPR_ST_ANALISE($value) {
        $this->IPR_ST_ANALISE = $value;
    }

    /* End of SET IPR_ST_ANALISE */

    function setIPR_ST_INSCRICAO($value) {
        $this->IPR_ST_INSCRICAO = $value;
    }

    /* End of SET IPR_ST_INSCRICAO */

    function setRVC_ID_RESERVA_CHAMADA($value) {
        $this->RVC_ID_RESERVA_CHAMADA = $value;
    }

    /* End of SET RVC_ID_RESERVA_CHAMADA */
}
?>
