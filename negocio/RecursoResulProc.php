<?php

/**
 * tb_rrp_recurso_resul_proc class
 * This class manipulates the table RecursoResulProc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 03/06/2014
 * */
class RecursoResulProc {

    private $RRP_ID_RECURSO;
    private $PCH_ID_CHAMADA;
    private $IPR_ID_INSCRICAO;
    private $ESP_ID_ETAPA_SEL;
    private $RRP_DT_RECURSO;
    private $RRP_TP_MOTIVO;
    private $RRP_DS_MOTIVO;
    private $RRP_DS_JUSTIFICATIVA;
    private $RRP_ST_SITUACAO;
    private $RRP_DS_ANALISE;
    private $RRP_DT_ANALISE;
    private $USR_ID_USU_RESP_ANALISE;
    public static $TIPO_ASPECTOS_LEGAIS = 'A';
    public static $TIPO_INOB_ITEM_EDITAL = 'E';
    public static $TIPO_OUTROS = 'O';
    public static $SIT_EM_ANALISE = 'A';
    public static $SIT_DEFERIDO = 'D';
    public static $SIT_INDEFERIDO = 'I';
    // campos herdados
    public $IPR_NR_ORDEM_INSC;
    public $ESP_DS_ETAPA_SEL;
    // campos de auxílio
    public static $TAM_MAX_RECURSO = 3000;
    public static $TAM_MAX_RESPOSTA = 3000;

    public static function getDsTipo($tipo) {
        if ($tipo == self::$TIPO_ASPECTOS_LEGAIS) {
            return "Inconsistência ou inobservância a aspectos legais";
        }
        if ($tipo == self::$TIPO_INOB_ITEM_EDITAL) {
            return "Inobservância a item previsto no Edital";
        }
        if ($tipo == self::$TIPO_OUTROS) {
            return "Outros";
        }
        return null;
    }

    public static function getDsSituacao($situacao) {
        if ($situacao == self::$SIT_DEFERIDO) {
            return "Deferido";
        }
        if ($situacao == self::$SIT_INDEFERIDO) {
            return "Indeferido";
        }
        if ($situacao == self::$SIT_EM_ANALISE) {
            return "Em Análise";
        }

        return null;
    }

    public static function getListaTipoDsTipo() {
        $ret = array(
            self::$TIPO_ASPECTOS_LEGAIS => self::getDsTipo(self::$TIPO_ASPECTOS_LEGAIS),
            self::$TIPO_INOB_ITEM_EDITAL => self::getDsTipo(self::$TIPO_INOB_ITEM_EDITAL),
            self::$TIPO_OUTROS => self::getDsTipo(self::$TIPO_OUTROS));

        return $ret;
    }

    public static function getListaSitDsSit() {
        $ret = array(
            self::$SIT_EM_ANALISE => self::getDsSituacao(self::$SIT_EM_ANALISE),
            self::$SIT_DEFERIDO => self::getDsSituacao(self::$SIT_DEFERIDO),
            self::$SIT_INDEFERIDO => self::getDsSituacao(self::$SIT_INDEFERIDO));

        return $ret;
    }

    public static function getListaSitDsSitDefIndef() {
        $ret = self::getListaSitDsSit();
        array_shift($ret);
        return $ret;
    }

    /* Construtor padrão da classe */

    public function __construct($RRP_ID_RECURSO, $PCH_ID_CHAMADA, $IPR_ID_INSCRICAO, $ESP_ID_ETAPA_SEL, $RRP_DT_RECURSO, $RRP_TP_MOTIVO, $RRP_DS_MOTIVO, $RRP_DS_JUSTIFICATIVA, $RRP_ST_SITUACAO, $RRP_DS_ANALISE, $RRP_DT_ANALISE, $USR_ID_USU_RESP_ANALISE) {
        $this->RRP_ID_RECURSO = $RRP_ID_RECURSO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->IPR_ID_INSCRICAO = $IPR_ID_INSCRICAO;
        $this->ESP_ID_ETAPA_SEL = $ESP_ID_ETAPA_SEL;
        $this->RRP_DT_RECURSO = $RRP_DT_RECURSO;
        $this->RRP_TP_MOTIVO = $RRP_TP_MOTIVO;
        $this->RRP_DS_MOTIVO = $RRP_DS_MOTIVO;
        $this->RRP_DS_JUSTIFICATIVA = $RRP_DS_JUSTIFICATIVA;
        $this->RRP_ST_SITUACAO = $RRP_ST_SITUACAO;
        $this->RRP_DS_ANALISE = $RRP_DS_ANALISE;
        $this->RRP_DT_ANALISE = $RRP_DT_ANALISE;
        $this->USR_ID_USU_RESP_ANALISE = $USR_ID_USU_RESP_ANALISE;
    }

    public static function contarRecursoPorInscEtapa($idInscricao, $idEtapa) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_rrp_recurso_resul_proc
                    where IPR_ID_INSCRICAO = '$idInscricao'
                    and ESP_ID_ETAPA_SEL = '$idEtapa'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar recursos do candidato.", $e);
        }
    }

    public static function contarRecursoPorFiltro($idRecurso, $idOrdemInsc, $stSituacao, $idChamada, $idEtapaAval) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_rrp_recurso_resul_proc rrp 
                    join tb_ipr_inscricao_processo ipr on ipr.IPR_ID_INSCRICAO = rrp.IPR_ID_INSCRICAO
                    join tb_esp_etapa_sel_proc esp on esp.ESP_ID_ETAPA_SEL = rrp.ESP_ID_ETAPA_SEL";

            //complementando sql de acordo com o filtro
            $where = true;
            $and = false;

            // id Recurso
            if ($idRecurso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " RRP_ID_RECURSO = '$idRecurso' ";
            }

            // id ordem insc
            if ($idOrdemInsc != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " IPR_NR_ORDEM_INSC = '$idOrdemInsc' ";
            }

            // stSituacao
            if ($stSituacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " RRP_ST_SITUACAO = '$stSituacao' ";
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
                $sql .= " rrp.PCH_ID_CHAMADA = '$idChamada' ";
            }

            // idEtapa
            if ($idEtapaAval != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " esp.EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval' ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar recursos por filtro.", $e);
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
    public static function getCSVRecursosProcesso($chamada, $etapaAval = NULL) {
        try {

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $def = self::$SIT_DEFERIDO;
            $indef = self::$SIT_INDEFERIDO;
            $anal = self::$SIT_EM_ANALISE;
            $descDef = self::getDsSituacao($def);
            $descIndef = self::getDsSituacao($indef);
            $descAnal = self::getDsSituacao($anal);

            // sql inicial
            $sql = "select 
                    concat('Etapa ', EAP_NR_ETAPA_AVAL) as Etapa,
                    RRP_ID_RECURSO as 'Recurso',
                    USR_DS_NOME as 'Candidato',
                    case RRP_ST_SITUACAO
                        when '$anal' then '$descAnal'
                        when '$def' then '$descDef'
                        when '$indef' then '$descIndef'
                    end as 'Situacao',
                    RRP_DS_ANALISE as 'Justificativa'
                from
                    tb_rrp_recurso_resul_proc rrp
                        join
                    tb_ipr_inscricao_processo ipr ON ipr.IPR_ID_INSCRICAO = rrp.IPR_ID_INSCRICAO
                        join
                    tb_cdt_candidato cdt ON cdt.CDT_ID_CANDIDATO = ipr.CDT_ID_CANDIDATO
                        join
                    tb_usr_usuario usr ON usr.USR_ID_USUARIO = cdt.USR_ID_USUARIO
                        join
                    tb_esp_etapa_sel_proc esp on rrp.ESP_ID_ETAPA_SEL = esp.ESP_ID_ETAPA_SEL
                        join 
                    tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                where
                    rrp.PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";

            //tem etapa?
            if ($etapaAval != NULL) {
                $sql .= " and eap.EAP_ID_ETAPA_AVAL_PROC = '{$etapaAval->getEAP_ID_ETAPA_AVAL_PROC()}'";
            }

            // ordenação
            $sql .= " order by RRP_ID_RECURSO";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                throw new NegocioException("Não existem recursos.");
            }

            // executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //gerando e retornando
            return consultaParaCSV($resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao gerar arquivo CSV dos recursos.", $e);
        }
    }

    public static function buscarRecursoPorFiltro($idRecurso, $idOrdemInsc, $stSituacao, $idChamada, $idEtapaAval, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    RRP_ID_RECURSO,
                    rrp.PCH_ID_CHAMADA,
                    rrp.IPR_ID_INSCRICAO,
                    rrp.ESP_ID_ETAPA_SEL,
                    concat('Etapa ', EAP_NR_ETAPA_AVAL) as ESP_DS_ETAPA_SEL,
                    DATE_FORMAT(`RRP_DT_RECURSO`, '%d/%m/%Y %T') AS RRP_DT_RECURSOSTR,
                    RRP_TP_MOTIVO,
                    RRP_DS_MOTIVO,
                    RRP_DS_JUSTIFICATIVA,
                    RRP_ST_SITUACAO,
                    RRP_DS_ANALISE,
                    DATE_FORMAT(`RRP_DT_ANALISE`, '%d/%m/%Y %T') AS RRP_DT_ANALISE,
                    USR_ID_USU_RESP_ANALISE,
                    IPR_NR_ORDEM_INSC
                from
                    tb_rrp_recurso_resul_proc rrp join tb_ipr_inscricao_processo ipr
                    on rrp.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
                    join tb_esp_etapa_sel_proc esp on esp.ESP_ID_ETAPA_SEL = rrp.ESP_ID_ETAPA_SEL
                    join tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC";


            //complementando sql de acordo com o filtro
            $where = true;
            $and = false;

            // id Recurso
            if ($idRecurso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " RRP_ID_RECURSO = '$idRecurso' ";
            }

            // id ordem insc
            if ($idOrdemInsc != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " IPR_NR_ORDEM_INSC = '$idOrdemInsc' ";
            }

            // stSituacao
            if ($stSituacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " RRP_ST_SITUACAO = '$stSituacao' ";
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
                $sql .= " rrp.PCH_ID_CHAMADA = '$idChamada' ";
            }

            // idEtapa
            if ($idEtapaAval != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " esp.EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval' ";
            }

            // incluindo ordem
            $sql .= " order by RRP_DT_RECURSO desc";

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

            //realizando iteração para recuperar dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $recursoTemp = new RecursoResulProc($dados['RRP_ID_RECURSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_ID_INSCRICAO'], $dados['ESP_ID_ETAPA_SEL'], $dados['RRP_DT_RECURSOSTR'], $dados['RRP_TP_MOTIVO'], $dados['RRP_DS_MOTIVO'], $dados['RRP_DS_JUSTIFICATIVA'], $dados['RRP_ST_SITUACAO'], $dados['RRP_DS_ANALISE'], $dados['RRP_DT_ANALISE'], $dados['USR_ID_USU_RESP_ANALISE']);

                // preenchendo campos herdados
                $recursoTemp->IPR_NR_ORDEM_INSC = $dados['IPR_NR_ORDEM_INSC'];
                $recursoTemp->ESP_DS_ETAPA_SEL = $dados['ESP_DS_ETAPA_SEL'];

                //adicionando no vetor
                $vetRetorno[$i] = $recursoTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar recursos por filtro.", $e);
        }
    }

    private function validaCriacaoRecurso() {
        // campos preenchidos
        if (Util::vazioNulo($this->IPR_ID_INSCRICAO) || Util::vazioNulo($this->PCH_ID_CHAMADA)) {
            throw new NegocioException("Parâmetros incorretos!");
        }

        // verificando se esta aberto o recurso
        $etapa = EtapaSelProc::buscarEtapaEmRecurso($this->PCH_ID_CHAMADA);
        if ($etapa == NULL || !EtapaSelProc::validaPeriodoRecurso($etapa->getESP_ID_ETAPA_SEL())) {
            throw new NegocioException("Não é possível protocolizar recurso, pois o Edital não aceita recursos.");
        }

        // verificando se ja nao possui recurso para o candidato
        if (self::contarRecursoPorInscEtapa($this->IPR_ID_INSCRICAO, $etapa->getESP_ID_ETAPA_SEL()) > 0) {
            throw new NegocioException("Não é possível protocolizar recurso, pois você já protocolou recurso para esse Edital.");
        }

        // tudo ok. Preenchendo campos
        $this->ESP_ID_ETAPA_SEL = $etapa->getESP_ID_ETAPA_SEL();
    }

    private function tratarDadosBanco() {

        // descriçao do motivo e tipo
        if ($this->RRP_TP_MOTIVO == self::$TIPO_OUTROS) {
            if (Util::vazioNulo($this->RRP_DS_MOTIVO)) {
                throw new NegocioException("Descrição do motivo não foi preenchido!");
            }
            $this->RRP_DS_MOTIVO = NGUtil::trataCampoStrParaBD($this->RRP_DS_MOTIVO);
        } else {
            $this->RRP_DS_MOTIVO = 'NULL';
        }
        $this->RRP_TP_MOTIVO = NGUtil::trataCampoStrParaBD($this->RRP_TP_MOTIVO);

        // justificativa
        $this->RRP_DS_JUSTIFICATIVA = NGUtil::trataCampoStrParaBD($this->RRP_DS_JUSTIFICATIVA);

        // situacao
        $this->RRP_ST_SITUACAO = NGUtil::trataCampoStrParaBD($this->RRP_ST_SITUACAO);
    }

    public function criarRecurso() {
        try {

            // validando criacao de recurso e preenchendo dados importantes
            $this->validaCriacaoRecurso();
            $this->tratarDadosBanco();

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de criação
            $sql = "insert into tb_rrp_recurso_resul_proc(`PCH_ID_CHAMADA`, IPR_ID_INSCRICAO, `ESP_ID_ETAPA_SEL`, `RRP_DT_RECURSO`, RRP_TP_MOTIVO, `RRP_DS_MOTIVO`, `RRP_DS_JUSTIFICATIVA`, `RRP_ST_SITUACAO`)
            values('$this->PCH_ID_CHAMADA', '$this->IPR_ID_INSCRICAO', '$this->ESP_ID_ETAPA_SEL', now(), $this->RRP_TP_MOTIVO, $this->RRP_DS_MOTIVO, $this->RRP_DS_JUSTIFICATIVA, $this->RRP_ST_SITUACAO)";

            // inserindo no banco
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao protocolizar recurso para o processo.", $e);
        }
    }

    public function registrarResposta($enviarEmail) {
        try {

            // validando registro de respostas
            // dados validos
            if (Util::vazioNulo($this->RRP_ID_RECURSO) || Util::vazioNulo($this->RRP_ST_SITUACAO) || Util::vazioNulo($this->RRP_DS_ANALISE)) {
                throw new NegocioException("Parâmetros de registro de resposta incorretos.");
            }

            // recuperando recurso do BD
            $recursoBD = self::buscarRecursoPorId($this->RRP_ID_RECURSO);

            // pode responder?
            if (!$recursoBD->permiteResponder()) {
                throw new NegocioException("Recurso já respondido.");
            }

            // atualizando objeto do BD
            $recursoBD->RRP_ST_SITUACAO = $this->RRP_ST_SITUACAO;
            $recursoBD->RRP_DS_ANALISE = $this->RRP_DS_ANALISE;

            // tratando campos para BD
            $this->RRP_ST_SITUACAO = NGUtil::trataCampoStrParaBD($this->RRP_ST_SITUACAO);
            $this->RRP_DS_ANALISE = NGUtil::trataCampoStrParaBD($this->RRP_DS_ANALISE);
            $this->USR_ID_USU_RESP_ANALISE = NGUtil::trataCampoStrParaBD(getIdUsuarioLogado());

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de criação
            $sql = "update tb_rrp_recurso_resul_proc 
                    set 
                        RRP_DT_ANALISE = now(),
                        RRP_ST_SITUACAO = {$this->RRP_ST_SITUACAO},
                        USR_ID_USU_RESP_ANALISE = {$this->USR_ID_USU_RESP_ANALISE},
                        RRP_DS_ANALISE = {$this->RRP_DS_ANALISE}
                    where
                        RRP_ID_RECURSO = {$recursoBD->RRP_ID_RECURSO}";

            // inserindo no banco
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao registrar resposta do recurso.", $e);
        }

        // enviando email de notificacao
        if ($enviarEmail) {
            $recursoBD->enviarEmailRecurso();
        }
    }

    private function enviarEmailRecurso() {
        try {

            //recuperando dados para processamento
            $inscricao = InscricaoProcesso::buscarInscricaoPorId($this->IPR_ID_INSCRICAO);
            $processo = Processo::buscarProcessoPorId($inscricao->getPRC_ID_PROCESSO());
            $chamada = ProcessoChamada::buscarChamadaPorId($inscricao->getPCH_ID_CHAMADA());
            $usuario = Usuario::buscarUsuarioPorIdCand($inscricao->getCDT_ID_CANDIDATO());

            //montando mensagem
            $assunto = "Seu recurso foi respondido";

            $mensagem = "Olá, {$usuario->getUSR_DS_NOME()}.<br/><br/>";
            $mensagem .= "O recurso <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$this->RRP_ID_RECURSO}</b></b></span> protocolado em <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$this->getRRP_DT_RECURSO(TRUE)}</b></span> contra o resultado da <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$this->ESP_DS_ETAPA_SEL}</b></span> do Edital <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>{$processo->getDsEditalCompleta()} - {$chamada->getPCH_DS_CHAMADA(TRUE)}</b></span> foi respondido.";
            $mensagem .= "<br/><br/><b>Detalhes da Resposta</b>";
            $mensagem .= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Situação:</b></span> {$this->getDsSituacaoObj()}";
            $mensagem .= "<br/><span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Descrição da Análise:</b></span><br/>$this->RRP_DS_ANALISE";

            $mensagem .= "<br/><br/>Você pode consultar suas inscrições acessando o sistema e clicando no menu Editais, acima a direita, e selecionando a opção Minhas inscrições.";

            $destinatario = $usuario->getUSR_DS_EMAIL();

            // enviando email
            enviaEmail($destinatario, $assunto, $mensagem);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao enviar email de notificação de resposta ao recurso.", $e);
        }
    }

    public static function buscarRecursoPorInscricao($idInscricao, $idEtapa, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    RRP_ID_RECURSO,
                    PCH_ID_CHAMADA,
                    IPR_ID_INSCRICAO,
                    ESP_ID_ETAPA_SEL,
                    DATE_FORMAT(`RRP_DT_RECURSO`, '%d/%m/%Y %T') AS RRP_DT_RECURSOSTR,
                    RRP_TP_MOTIVO,
                    RRP_DS_MOTIVO,
                    RRP_DS_JUSTIFICATIVA,
                    RRP_ST_SITUACAO,
                    RRP_DS_ANALISE,
                    DATE_FORMAT(`RRP_DT_ANALISE`, '%d/%m/%Y %T') AS RRP_DT_ANALISE,
                    USR_ID_USU_RESP_ANALISE
                from
                    tb_rrp_recurso_resul_proc
                where
                    IPR_ID_INSCRICAO = '$idInscricao'
                        and ESP_ID_ETAPA_SEL = '$idEtapa'
                order by RRP_DT_RECURSO desc";

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

            //realizando iteração para recuperar dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $recursoTemp = new RecursoResulProc($dados['RRP_ID_RECURSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_ID_INSCRICAO'], $dados['ESP_ID_ETAPA_SEL'], $dados['RRP_DT_RECURSOSTR'], $dados['RRP_TP_MOTIVO'], $dados['RRP_DS_MOTIVO'], $dados['RRP_DS_JUSTIFICATIVA'], $dados['RRP_ST_SITUACAO'], $dados['RRP_DS_ANALISE'], $dados['RRP_DT_ANALISE'], $dados['USR_ID_USU_RESP_ANALISE']);

                //adicionando no vetor
                $vetRetorno[$i] = $recursoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar recursos por inscrição.", $e);
        }
    }

    public static function buscarRecursoPorId($idRecurso, $idInscricao = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    RRP_ID_RECURSO,
                    rrp.PCH_ID_CHAMADA,
                    rrp.IPR_ID_INSCRICAO,
                    rrp.ESP_ID_ETAPA_SEL,
                    concat('Etapa ', EAP_NR_ETAPA_AVAL) as ESP_DS_ETAPA_SEL,
                    DATE_FORMAT(`RRP_DT_RECURSO`, '%d/%m/%Y %T') AS RRP_DT_RECURSOSTR,
                    RRP_TP_MOTIVO,
                    RRP_DS_MOTIVO,
                    RRP_DS_JUSTIFICATIVA,
                    RRP_ST_SITUACAO,
                    RRP_DS_ANALISE,
                    DATE_FORMAT(`RRP_DT_ANALISE`, '%d/%m/%Y %T') AS RRP_DT_ANALISE,
                    USR_ID_USU_RESP_ANALISE,
                    IPR_NR_ORDEM_INSC
                from
                    tb_rrp_recurso_resul_proc rrp join tb_ipr_inscricao_processo ipr
                    on rrp.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
                    join tb_esp_etapa_sel_proc esp on esp.ESP_ID_ETAPA_SEL = rrp.ESP_ID_ETAPA_SEL
                    join tb_eap_etapa_aval_proc eap on esp.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                where
                    RRP_ID_RECURSO = '$idRecurso'";

            // incluindo inscrição
            if ($idInscricao != NULL) {
                $sql .= " and rrp.IPR_ID_INSCRICAO = '$idInscricao'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Recurso não encontrado.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);

            $recursoTemp = new RecursoResulProc($dados['RRP_ID_RECURSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_ID_INSCRICAO'], $dados['ESP_ID_ETAPA_SEL'], $dados['RRP_DT_RECURSOSTR'], $dados['RRP_TP_MOTIVO'], $dados['RRP_DS_MOTIVO'], $dados['RRP_DS_JUSTIFICATIVA'], $dados['RRP_ST_SITUACAO'], $dados['RRP_DS_ANALISE'], $dados['RRP_DT_ANALISE'], $dados['USR_ID_USU_RESP_ANALISE']);

            // preenchendo campos herdados
            $recursoTemp->IPR_NR_ORDEM_INSC = $dados['IPR_NR_ORDEM_INSC'];
            $recursoTemp->ESP_DS_ETAPA_SEL = $dados['ESP_DS_ETAPA_SEL'];

            return $recursoTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar recurso.", $e);
        }
    }

    public static function contarRecursosPorInscricao($idInscricao, $idEtapa) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    count(*) as cont
                from
                    tb_rrp_recurso_resul_proc
                where
                    IPR_ID_INSCRICAO = '$idInscricao'
                        and ESP_ID_ETAPA_SEL = '$idEtapa'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando zero
                return 0;
            }

            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar recursos por inscrição.", $e);
        }
    }

    public static function contarRecursosPorUsuResp($idUsuResp) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    count(*) as cont
                from
                    tb_rrp_recurso_resul_proc
                where
                    USR_ID_USU_RESP_ANALISE = '$idUsuResp'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando zero
                return 0;
            }

            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar recursos por responsável.", $e);
        }
    }

    function getDsMotivo() {
        if (Util::vazioNulo($this->RRP_TP_MOTIVO)) {
            return "";
        }
        if ($this->RRP_TP_MOTIVO == self::$TIPO_OUTROS) {
            return $this->RRP_DS_MOTIVO;
        }
        return self::getDsTipo($this->RRP_TP_MOTIVO);
    }

    function getDsSituacaoObj() {
        return self::getDsSituacao($this->RRP_ST_SITUACAO);
    }

    function permiteResponder() {
        return $this->RRP_ST_SITUACAO == self::$SIT_EM_ANALISE;
    }

    /* GET FIELDS FROM TABLE */

    function getRRP_ID_RECURSO() {
        return $this->RRP_ID_RECURSO;
    }

    /* End of get RRP_ID_RECURSO */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getIPR_ID_INSCRICAO() {
        return $this->IPR_ID_INSCRICAO;
    }

    /* End of get IPR_ID_INSCRICAO */

    function getESP_ID_ETAPA_SEL() {
        return $this->ESP_ID_ETAPA_SEL;
    }

    /* End of get ESP_ID_ETAPA_SEL */

    function getRRP_DT_RECURSO($apenasData = FALSE) {
        if ($apenasData) {
            $temp = explode(" ", $this->RRP_DT_RECURSO);
            return $temp[0];
        }
        return $this->RRP_DT_RECURSO;
    }

    /* End of get RRP_DT_RECURSO */

    function getRRP_TP_MOTIVO() {
        return $this->RRP_TP_MOTIVO;
    }

    /* End of get RRP_TP_MOTIVO */

    function getRRP_DS_MOTIVO() {
        return $this->RRP_DS_MOTIVO;
    }

    /* End of get RRP_DS_MOTIVO */

    function getRRP_DS_JUSTIFICATIVA() {
        return $this->RRP_DS_JUSTIFICATIVA;
    }

    /* End of get RRP_DS_JUSTIFICATIVA */

    function getRRP_ST_SITUACAO() {
        return $this->RRP_ST_SITUACAO;
    }

    /* End of get RRP_ST_SITUACAO */

    function getRRP_DS_ANALISE($preencherVazio = TRUE) {
        if ($preencherVazio && Util::vazioNulo($this->RRP_DS_ANALISE)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->RRP_DS_ANALISE;
    }

    /* End of get RRP_DS_ANALISE */

    function getRRP_DT_ANALISE() {
        if (Util::vazioNulo($this->RRP_DT_ANALISE)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->RRP_DT_ANALISE;
    }

    /* End of get RRP_DT_ANALISE */

    function getUSR_ID_USU_RESP_ANALISE() {
        return $this->USR_ID_USU_RESP_ANALISE;
    }

    /* End of get USR_ID_USU_RESP_ANALISE */



    /* SET FIELDS FROM TABLE */

    function setRRP_ID_RECURSO($value) {
        $this->RRP_ID_RECURSO = $value;
    }

    /* End of SET RRP_ID_RECURSO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setIPR_ID_INSCRICAO($value) {
        $this->IPR_ID_INSCRICAO = $value;
    }

    /* End of SET IPR_ID_INSCRICAO */

    function setESP_ID_ETAPA_SEL($value) {
        $this->ESP_ID_ETAPA_SEL = $value;
    }

    /* End of SET ESP_ID_ETAPA_SEL */

    function setRRP_DT_RECURSO($value) {
        $this->RRP_DT_RECURSO = $value;
    }

    /* End of SET RRP_DT_RECURSO */

    function setRRP_TP_MOTIVO($value) {
        $this->RRP_TP_MOTIVO = $value;
    }

    /* End of SET RRP_TP_MOTIVO */

    function setRRP_DS_MOTIVO($value) {
        $this->RRP_DS_MOTIVO = $value;
    }

    /* End of SET RRP_DS_MOTIVO */

    function setRRP_DS_JUSTIFICATIVA($value) {
        $this->RRP_DS_JUSTIFICATIVA = $value;
    }

    /* End of SET RRP_DS_JUSTIFICATIVA */

    function setRRP_ST_SITUACAO($value) {
        $this->RRP_ST_SITUACAO = $value;
    }

    /* End of SET RRP_ST_SITUACAO */

    function setRRP_DS_ANALISE($value) {
        $this->RRP_DS_ANALISE = $value;
    }

    /* End of SET RRP_DS_ANALISE */

    function setRRP_DT_ANALISE($value) {
        $this->RRP_DT_ANALISE = $value;
    }

    /* End of SET RRP_DT_ANALISE */

    function setUSR_ID_USU_RESP_ANALISE($value) {
        $this->USR_ID_USU_RESP_ANALISE = $value;
    }

    /* End of SET USR_ID_USU_RESP_ANALISE */
}

?>
