<?php

/**
 * tb_gap_grupo_anexo_proc class
 * This class manipulates the table GrupoAnexoProc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 23/10/2013
 * */
global $CFG;
require_once $CFG->rpasta . "/negocio/EtapaAvalProc.php";
require_once $CFG->rpasta . "/negocio/SubitemAnexoProc.php";

class GrupoAnexoProc {

    private $GAP_ID_GRUPO_PROC;
    private $PRC_ID_PROCESSO;
    private $GAP_NR_ORDEM_EXIBICAO;
    private $GAP_NM_GRUPO;
    private $GAP_DS_GRUPO;
    private $GAP_TP_GRUPO;
    private $GAP_GRUPO_OBRIGATORIO;
    private $GAP_NR_MAX_CARACTER;
    private $GAP_TP_AVALIACAO;
    public static $TIPO_AGRUPAMENTO_PERGUNTA = 'G';
    public static $TIPO_PERGUNTA_LIVRE = 'P';
    public static $AVAL_TP_SEM = 'S';
    public static $AVAL_TP_MANUAL = 'M';
    public static $AVAL_TP_AUTOMATICA = 'A';
    public static $TAM_MAX_DS_GRUPO = 2000;
    public static $ID_ESCOPO_ORDEM_INF_COMP = "infcomp";
    public static $COD_TP_ORDENACAO = "InfComp"; // usado para atualizacao da ordem 
    // campos de processamento interno
    private $nmEtapaAval;
    private $idEtapaAval;
    private $idItemAvalProc;
    private $idCategoriaAvalProc;
    private $vlPontuacaoMaxAval;

    /* Construtor padrão da classe */

    public function __construct($GAP_ID_GRUPO_PROC, $PRC_ID_PROCESSO, $GAP_NR_ORDEM_EXIBICAO, $GAP_NM_GRUPO, $GAP_DS_GRUPO, $GAP_TP_GRUPO, $GAP_GRUPO_OBRIGATORIO, $GAP_NR_MAX_CARACTER, $GAP_TP_AVALIACAO = NULL) {
        $this->GAP_ID_GRUPO_PROC = $GAP_ID_GRUPO_PROC;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->GAP_NR_ORDEM_EXIBICAO = $GAP_NR_ORDEM_EXIBICAO;
        $this->GAP_NM_GRUPO = $GAP_NM_GRUPO;
        $this->GAP_DS_GRUPO = $GAP_DS_GRUPO;
        $this->GAP_TP_GRUPO = $GAP_TP_GRUPO;
        $this->GAP_GRUPO_OBRIGATORIO = $GAP_GRUPO_OBRIGATORIO;
        $this->GAP_NR_MAX_CARACTER = $GAP_NR_MAX_CARACTER;
        $this->GAP_TP_AVALIACAO = $GAP_TP_AVALIACAO;
        $this->nmEtapaAval = $this->idEtapaAval = $this->idCategoriaAvalProc = $this->idItemAvalProc = $this->vlPontuacaoMaxAval = NULL;
    }

    public static function validaNomeGrupoAnexoProc($idProcesso, $nmGrupo, $idGrupoAnexoProc = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //caso nome
            $sql = "select count(*) as cont from tb_gap_grupo_anexo_proc where PRC_ID_PROCESSO = '$idProcesso' && `GAP_NM_GRUPO` = '$nmGrupo'";
            if ($idGrupoAnexoProc != NULL) {
                $sql .= " and GAP_ID_GRUPO_PROC != '$idGrupoAnexoProc'";
            }
            $res = $conexao->execSqlComRetorno($sql);
            return $conexao->getResult("cont", $res) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar nome do grupo.", $e);
        }
    }

    public static function getDsTipoGrupo($tipo) {
        if ($tipo == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
            return "Dissertativa";
        }
        if ($tipo == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {
            return "Múltipla Escolha";
        }
    }

    public static function getDsTipoAval($tipoAval) {
        if ($tipoAval == GrupoAnexoProc::$AVAL_TP_SEM) {
            return "Não";
        }
        if ($tipoAval == GrupoAnexoProc::$AVAL_TP_MANUAL) {
            return "Sim";
        }
//        if ($tipoAval == GrupoAnexoProc::$AVAL_TP_AUTOMATICA) {
//            return "Sim: Automática";
//        }
    }

    public function isAvaliativo() {
        return $this->GAP_TP_AVALIACAO == GrupoAnexoProc::$AVAL_TP_MANUAL ||
                $this->GAP_TP_AVALIACAO == GrupoAnexoProc::$AVAL_TP_AUTOMATICA;
    }

    public function isAvaliacaoAutomatica() {
        return $this->GAP_TP_AVALIACAO == GrupoAnexoProc::$AVAL_TP_AUTOMATICA;
    }

    /**
     * Retorna o Id do elemento na montagem do HTML
     * @return string
     */
    public function getIdElementoHtml() {
        if ($this->GAP_TP_GRUPO == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
            return "textoGrupo" . $this->GAP_ID_GRUPO_PROC;
        }
        if ($this->GAP_TP_GRUPO == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {
            return "agrupamento" . $this->GAP_ID_GRUPO_PROC;
        }
        throw new NegocioException("Código de GRUPO não implementado!");
    }

    public static function getListaTipoDsTipoGrupo() {
        $ret = array(GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE => GrupoAnexoProc::getDsTipoGrupo(GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE),
            GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA => GrupoAnexoProc::getDsTipoGrupo(GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA));

        return $ret;
    }

    public static function getListaTipoAvalDsTipoAvaliacao() {
        $ret = array(GrupoAnexoProc::$AVAL_TP_MANUAL => GrupoAnexoProc::getDsTipoAval(GrupoAnexoProc::$AVAL_TP_MANUAL),
            GrupoAnexoProc::$AVAL_TP_SEM => GrupoAnexoProc::getDsTipoAval(GrupoAnexoProc::$AVAL_TP_SEM));

//        GrupoAnexoProc::$AVAL_TP_AUTOMATICA => GrupoAnexoProc::getDsTipoGrupo(GrupoAnexoProc::$AVAL_TP_AUTOMATICA)

        return $ret;
    }

    public function isObrigatorio() {
        return $this->GAP_GRUPO_OBRIGATORIO == FLAG_BD_SIM;
    }

    private function trataDadosBanco() {
        // preparando campos
        $this->GAP_NM_GRUPO = NGUtil::trataCampoStrParaBD($this->GAP_NM_GRUPO);
        $this->GAP_DS_GRUPO = NGUtil::trataCampoStrParaBD($this->GAP_DS_GRUPO);
        $this->GAP_GRUPO_OBRIGATORIO = NGUtil::trataCampoStrParaBD($this->GAP_GRUPO_OBRIGATORIO);
        $this->GAP_TP_AVALIACAO = NGUtil::trataCampoStrParaBD($this->GAP_TP_AVALIACAO);

        // tipo
        if ($this->GAP_TP_GRUPO == self::$TIPO_PERGUNTA_LIVRE) {
            if (Util::vazioNulo($this->GAP_NR_MAX_CARACTER)) {
                throw new NegocioException("Número máx de Caracter não pode ser nulo.");
            }
            $this->GAP_NR_MAX_CARACTER = NGUtil::trataCampoStrParaBD($this->GAP_NR_MAX_CARACTER);
        } else {
            $this->GAP_NR_MAX_CARACTER = 'NULL';
        }
    }

    /**
     * Função que recupera os Sqls para persistência da avaliação pré-criação do grupo
     * 
     * @param Processo $processo
     * @param int $idEtapaAval
     * @param float $notaMax
     * @param boolean $edicao Diz se é uma edição. Por padrão é falso.
     * @param char $novoTpAvaliacao Novo tipo de avaliação, a ser setado após o tratamento da edição. Parâmetro obrigatório quando edição é TRUE.
     * @return array Array de comandos sql para efetivação do esquema de avaliação
     */
    private function trataAvaliacaoPre($processo, $idEtapaAval, $notaMax, $edicao = FALSE, $novoTpAvaliacao = NULL) {
        // verificando obrigatoriedade
        if ($edicao && Util::vazioNulo($novoTpAvaliacao)) {
            throw new NegocioException("Chamada incorreta da função de tratamento de avaliação.");
        }

        // tratando caso de edição
        if ($edicao) {
            // atualmente é avaliativo?
            if ($this->isAvaliativo()) {
                // avaliação manual? 
                if ($this->GAP_TP_AVALIACAO == self::$AVAL_TP_MANUAL) {
                    // Atualmente não é avaliativo ou etapa antiga é diferente da atual
                    if ($novoTpAvaliacao == self::$AVAL_TP_SEM || $this->getIdEtapaAval() != $idEtapaAval) {

                        // recuperando sql de remoção do item da pergunta, já com o processamento necessário
                        $ret = $this->getItemAvalInfComp()->_getSqlRemoverItemInfComp();
                    }
                    // não é diferente? nada a fazer
                }
            }
            // sem avaliacao? Nada a fazer
            // 
            // 
            // atualizando status
            $this->GAP_TP_AVALIACAO = $novoTpAvaliacao;
        }

        // criando vetor para sqls, se necessário.
        if (!isset($ret)) {
            $ret = array();
        }

        // caso de ter avaliação
        if ($this->isAvaliativo()) {
            if ($this->GAP_TP_AVALIACAO == self::$AVAL_TP_MANUAL) {

                // caso de ter de criar etapa
                if ($idEtapaAval == EtapaAvalProc::$ID_SELECT_NOVA_ETAPA) {
                    // validando criação 
                    if (!EtapaAvalProc::permiteCriarEtapa($processo)) {
                        throw new NegocioException("Não é possível criar uma nova etapa de avaliação.");
                    }

                    // criar etapa
                    $ret [] = EtapaAvalProc::_getSqlCriarEtapaAval($this->PRC_ID_PROCESSO);

                    // criar categoria especial
                    $ret [] = CategoriaAvalProc::_getSqlCriarCatInfComp($this->PRC_ID_PROCESSO, ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, $notaMax);

                    $idCategoriaEsp = ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE;
                } else {

                    $catEsp = EtapaAvalProc::possuiCatInfComp($this->PRC_ID_PROCESSO, $idEtapaAval);
                    $idCategoriaEsp = $catEsp === FALSE ? ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE : $catEsp;
                    if ($catEsp === FALSE) {
                        // criar categoria especial
                        $ret [] = CategoriaAvalProc::_getSqlCriarCatInfComp($this->PRC_ID_PROCESSO, $idEtapaAval, $notaMax);
                    }
                }

                // tratando o item de avaliação
                $idGrupoAnexoProc = Util::vazioNulo($this->GAP_ID_GRUPO_PROC) ? ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE : $this->GAP_ID_GRUPO_PROC;
                $idItemAval = ItemAvalProc::grupoAnexoProcPossuiItemAval($this->PRC_ID_PROCESSO, $idCategoriaEsp, $idGrupoAnexoProc);
                if ($idItemAval === FALSE) {
                    $ret [] = ItemAvalProc::_getSqlCriarItemInfComp($this->PRC_ID_PROCESSO, $idCategoriaEsp, $idGrupoAnexoProc, $this->GAP_NM_GRUPO, $notaMax);
                } else {
                    $ret [] = ItemAvalProc::_getSqlAtualizarItemInfComp($this->PRC_ID_PROCESSO, $idItemAval, $idGrupoAnexoProc, $this->GAP_NM_GRUPO, $notaMax);
                }

                // processando reset da etapa
                if ($idEtapaAval != EtapaAvalProc::$ID_SELECT_NOVA_ETAPA) {
                    $ret = array_merge($ret, EtapaAvalProc::getArraySqlResetAvalProcEtapa($this->PRC_ID_PROCESSO, $idEtapaAval, $this->isAvaliacaoAutomatica()));
                }
            }// fim avaliação manual
        } // fim sem avaliação
        // 
//        print_r($ret);
//        exit;

        return $ret;
    }

    /**
     * Função que recupera os Sqls para persistência da avaliação pós-criação do grupo
     * 
     * @param int $idEtapaAval
     * @return array Array de comandos sql para efetivação do esquema de avaliação
     */
    private function trataAvaliacaoPos() {
        $ret = array();

        // restaurando campos
        $this->GAP_TP_AVALIACAO = str_replace("'", "", $this->GAP_TP_AVALIACAO);
        $this->GAP_NM_GRUPO = str_replace("'", "", $this->GAP_NM_GRUPO);

        // caso de ter avaliação
        if ($this->isAvaliativo()) {
            if ($this->GAP_TP_AVALIACAO == self::$AVAL_TP_MANUAL) {
                // atualizando item de avaliação com id do gupo
                $ret [] = ItemAvalProc::_getSqlAjusteItemInfCompPos($this->PRC_ID_PROCESSO, $this->GAP_NM_GRUPO);

                // atualizando pontuação máxima da categoria de inf. complementares
                $ret [] = CategoriaAvalProc::_getSqlAtuPontMaxCatInfComp($this->PRC_ID_PROCESSO, $this->GAP_NM_GRUPO);
            }// fim avaliação manual
        } // fim sem avaliação
        // 
        return $ret;
    }

    public function getNmEtapaAval() {
        if (!$this->isAvaliativo()) {
            return NULL;
        }
        if ($this->nmEtapaAval == NULL) {
            // caregando dados de avaliação
            $this->carregarDadosAvaliacao();
        }
        return $this->nmEtapaAval;
    }

    public function getIdEtapaAval() {
        if (!$this->isAvaliativo()) {
            return NULL;
        }
        if ($this->idEtapaAval == NULL) {
            // caregando dados de avaliação
            $this->carregarDadosAvaliacao();
        }
        return $this->idEtapaAval;
    }

    public function getIdItemAval() {
        if (!$this->isAvaliativo()) {
            return NULL;
        }
        if ($this->idItemAvalProc == NULL) {
            // caregando dados de avaliação
            $this->carregarDadosAvaliacao();
        }
        return $this->idItemAvalProc;
    }

    public function getIdCategoriaAval() {
        if (!$this->isAvaliativo()) {
            return NULL;
        }
        if ($this->idCategoriaAvalProc == NULL) {
            // caregando dados de avaliação
            $this->carregarDadosAvaliacao();
        }
        return $this->idCategoriaAvalProc;
    }

    public function getPontuacaoMaxAval() {
        if (!$this->isAvaliativo()) {
            return NULL;
        }
        if ($this->vlPontuacaoMaxAval == NULL) {
            // carregando dados de avaliação
            $this->carregarDadosAvaliacao();
        }
        return $this->vlPontuacaoMaxAval;
    }

    private function carregarDadosAvaliacao() {
        try {
            if (!$this->isAvaliativo()) {
                return;
            }
            $itemAval = $this->getItemAvalInfComp();

            // recuperando categoria e etapa de avaliação
            $catAval = CategoriaAvalProc::buscarCatAvalPorId($itemAval->getCAP_ID_CATEGORIA_AVAL());
            $etapaAval = EtapaAvalProc::buscarEtapaAvalPorId($catAval->getEAP_ID_ETAPA_AVAL_PROC());

            // atualizando campos
            $this->vlPontuacaoMaxAval = NGUtil::formataDecimal($itemAval->getIAP_VAL_PONTUACAO_MAX());
            $this->nmEtapaAval = $etapaAval->getNomeEtapa();
            $this->idEtapaAval = $etapaAval->getEAP_ID_ETAPA_AVAL_PROC();
            $this->idItemAvalProc = $itemAval->getIAP_ID_ITEM_AVAL();
            $this->idCategoriaAvalProc = $itemAval->getCAP_ID_CATEGORIA_AVAL();
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar dados de avaliação da informação complementar.", $e);
        }
    }

    /**
     * Essa função recupera o item de avaliação relacionado à pergunta
     * 
     * @return ItemAvalProc
     * @throws NegocioException
     */
    public function getItemAvalInfComp() {
        if (!$this->isAvaliativo()) {
            return NULL;
        }

        // recuperando item de avaliação 
        $itemAval = ItemAvalProc::buscarItemAvalPorCatTpParam($this->PRC_ID_PROCESSO, NULL, ItemAvalProc::$TP_INF_COMP, ItemAvalProc::codBuscaParamItemInfComp($this->GAP_ID_GRUPO_PROC));

        if (Util::vazioNulo($itemAval)) {
            // erro estranho... de codificação
            throw new NegocioException("Item de avaliação não configurado para a pergunta.");
        }

        return $itemAval[0];
    }

    /**
     * Essa função verifica se a pergunta possui um item de avaliação (Compatibilidade com versões anteriores do sistema)
     * 
     * @return ItemAvalProc
     * @throws NegocioException
     */
    public function temItemAvalInfComp() {
        if (!$this->isAvaliativo()) {
            return NULL;
        }
        // recuperando item de avaliação 
        $itemAval = ItemAvalProc::buscarItemAvalPorCatTpParam($this->PRC_ID_PROCESSO, NULL, ItemAvalProc::$TP_INF_COMP, ItemAvalProc::codBuscaParamItemInfComp($this->GAP_ID_GRUPO_PROC));

        return !Util::vazioNulo($itemAval);
    }

    public function getDsObjAval() {
        return "Avaliação manual da pergunta \'$this->GAP_NM_GRUPO\'.";
    }

    /**
     * 
     * @param int $idEtapaAval
     * @param float $notaMax
     * @return int ID do grupo inserido
     * @throws NegocioException
     */
    public function criarGrupoAnexoProc($idEtapaAval, $notaMax) {
        try {

            // buscando processo para validação
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);

            // nao pode editar
            if (!self::permiteManterGrupo($processo)) {
                throw new NegocioException("Informação complementar não pode ser criada.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando caso de avaliação
            $arrayCmds = $this->trataAvaliacaoPre($processo, $idEtapaAval, $notaMax);

            // tratando campos
            $this->trataDadosBanco();

            //montando sql de criação
            $sql = "insert into tb_gap_grupo_anexo_proc(`PRC_ID_PROCESSO`, GAP_NR_ORDEM_EXIBICAO, `GAP_NM_GRUPO`, `GAP_DS_GRUPO`, GAP_TP_GRUPO, `GAP_GRUPO_OBRIGATORIO`, `GAP_NR_MAX_CARACTER`, `GAP_TP_AVALIACAO`)
            values('$this->PRC_ID_PROCESSO', (
                    SELECT COALESCE(MAX(`GAP_NR_ORDEM_EXIBICAO`),0) + 1
                    FROM (
                    SELECT *
                    FROM tb_gap_grupo_anexo_proc) AS temp
                    WHERE `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'), $this->GAP_NM_GRUPO, $this->GAP_DS_GRUPO, '$this->GAP_TP_GRUPO', $this->GAP_GRUPO_OBRIGATORIO, $this->GAP_NR_MAX_CARACTER, $this->GAP_TP_AVALIACAO)";

            // adicionando sql de criação ao array de comandos
            $arrayCmds [] = $sql;

            $cmdsAjuste = $this->trataAvaliacaoPos();

//            print_r($arrayCmds);
//            exit;
            // executando no banco
            return $conexao->execTransacaoArrayDependente($arrayCmds, TRUE, $cmdsAjuste);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar informação complementar.", $e);
        }
    }

    /**
     * 
     * @param int $idEtapaAval
     * @param float $notaMax
     * @param char $novoTpAvaliacao Novo tipo de avaliação do grupo.
     * @throws NegocioException
     */
    public function editarGrupoAnexoProc($idEtapaAval, $notaMax, $novoTpAvaliacao) {
        try {

            // buscando processo para validação
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);

            // nao pode editar
            if (!self::permiteManterGrupo($processo)) {
                throw new NegocioException("Informação complementar não pode ser atualizada.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando caso de avaliação
            $arrayCmds = $this->trataAvaliacaoPre($processo, $idEtapaAval, $notaMax, TRUE, $novoTpAvaliacao);

            // tratando campos
            $this->trataDadosBanco();

            //montando sql de atualização
            $sql = "update tb_gap_grupo_anexo_proc set `GAP_NM_GRUPO` = $this->GAP_NM_GRUPO,
                `GAP_DS_GRUPO` = $this->GAP_DS_GRUPO, 
                `GAP_GRUPO_OBRIGATORIO` = $this->GAP_GRUPO_OBRIGATORIO,
                `GAP_NR_MAX_CARACTER` = $this->GAP_NR_MAX_CARACTER,
                `GAP_TP_AVALIACAO` = $this->GAP_TP_AVALIACAO
                 WHERE `GAP_ID_GRUPO_PROC` = '$this->GAP_ID_GRUPO_PROC'";

            // adicionando sql de atualização ao array de comandos
            $arrayCmds [] = $sql;

            $cmdsAjuste = $this->trataAvaliacaoPos();

//            print_r($arrayCmds);
//            exit;
//            
            // executando no banco
            return $conexao->execTransacaoArrayDependente($arrayCmds, TRUE, $cmdsAjuste);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar informação complementar.", $e);
        }
    }

    /**
     * Funcao que edita a ordenaçao das informações complementares
     * 
     * @param int $idProcesso
     * @param int $idEscopoOrdemInfComp
     * @param string $novaOrdenacao - String na forma: [codItem1:novaOrdem1;codItem2:novaOrdem2;...]
     * @throws NegocioException
     */
    public static function editarOrdensGrupoAnexoProc($idProcesso, $idEscopoOrdemInfComp, $novaOrdenacao) {
        try {
            // validando escopo
            if ($idEscopoOrdemInfComp != self::$ID_ESCOPO_ORDEM_INF_COMP) {
                throw new NegocioException("Escopo para alteração de informação complementar incorreto.");
            }

            // buscando processo para validação
            $processo = Processo::buscarProcessoPorId($idProcesso);

            // nao pode editar
            if (!self::permiteManterGrupo($processo)) {
                throw new NegocioException("Informação complementar não pode ser alterada.");
            }

            // destrinchando string
            $vetAtu = array(); // vetor na forma [idGrupoAnexoProc => novaOrdem]
            $vetTemp = explode(";", $novaOrdenacao);
            if (count($vetTemp) != 0) {
                array_pop($vetTemp);
            }

            foreach ($vetTemp as $atu) {
                $temp = explode(":", $atu);
                $vetAtu[$temp[0]] = $temp[1];
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // montando sqls
            $vetSql = array();

            foreach ($vetAtu as $idInfComp => $ordem) {
                // verificando validacao
                if (Util::vazioNulo($idInfComp) || Util::vazioNulo($ordem)) {
                    throw new NegocioException("Parâmetros incorretos.");
                }

                //montando sql de atualizacao
                $sql = "update tb_gap_grupo_anexo_proc 
                        set GAP_NR_ORDEM_EXIBICAO = '$ordem'
                    where GAP_ID_GRUPO_PROC = '$idInfComp'
                          and PRC_ID_PROCESSO = '$idProcesso'";

                // inserindo no array
                $vetSql [] = $sql;
            }

//            print_r($vetSql);
//            exit;
//            
            $conexao->execTransacaoArray($vetSql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar ordenação das informações complementares.", $e);
        }
    }

    public function excluirGrupoAnexoProc() {
        try {

            // buscando processo para validação
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);

            // nao pode editar
            if (!self::permiteManterGrupo($processo)) {
                throw new NegocioException("Informação complementar não pode ser atualizada.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando questao de avaliação
            if ($this->isAvaliativo()) {
                if ($this->GAP_TP_AVALIACAO == self::$AVAL_TP_MANUAL) {
                    $arrayCmds = $this->getItemAvalInfComp()->_getSqlRemoverItemInfComp();
                }
            }
            if (!isset($arrayCmds)) {
                $arrayCmds = array();
            }

            // incluindo sql de exclusão de possíveis subitens
            $arrayCmds [] = SubitemAnexoProc::getSqlExcluirPorGrupo($this->GAP_ID_GRUPO_PROC);

            // incluindo sql de exclusão de possíveis itens em anexo
            $arrayCmds [] = ItemAnexoProc::getSqlExcluirPorProcGrupo($this->PRC_ID_PROCESSO, $this->GAP_ID_GRUPO_PROC);

            //montando sql de exclusão
            $arrayCmds [] = "delete from tb_gap_grupo_anexo_proc
                 WHERE `GAP_ID_GRUPO_PROC` = '$this->GAP_ID_GRUPO_PROC'";

            // adicionando sql de atualização de ordem
            $arrayCmds = array_merge($arrayCmds, $this->getSqlReordenacaoAuto());

//            print_r($arrayCmds);
//            exit;
//            
            // executando no banco
            return $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir informação complementar.", $e);
        }
    }

    private function getSqlReordenacaoAuto() {
        $sql1 = "SET @counter = 0";

        $sql2 = "UPDATE tb_gap_grupo_anexo_proc
                    SET GAP_NR_ORDEM_EXIBICAO = @counter := @counter + 1
                    where PRC_ID_PROCESSO = '$this->PRC_ID_PROCESSO'
                    ORDER BY GAP_NR_ORDEM_EXIBICAO";

        return array($sql1, $sql2);
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_gap_grupo_anexo_proc where PRC_ID_PROCESSO = '$idProcesso'";
    }

    /**
     *  
     * 
     * @param int $idProcesso
     * @param int $idGrupoAnexoProc
     * @param boolean $respMultipla Informa se a pergunta aceita resposta múltipla ou não, 
     * @param array $matItemResp Array indexado pela ordem do item. Forma: [OrdemItem => array(nmItem, dsItem, complemento, tpComplemento, compObrigatorio)]
     * @param array $matCompItemResp Array indexado pela ordem do item. Pode ter duas formas, dependendo do tipo de complemento do item:
     * Tipo de complemento texto: [OrdemItem => array(tamMaxResposta, nmComplemento)]; Outros tipos de complemento: [OrdemItem => array(OrdemOpcao => array(nmOpcao, dsOpcao))]
     * 
     * @throws NegocioException
     */
    public static function manterItensRespGrupo($idProcesso, $idGrupoAnexoProc, $respMultipla, $matItemResp, $matCompItemResp) {
        try {
            // buscando dados para validação
            $processo = Processo::buscarProcessoPorId($idProcesso);
            $grupoAnexoProc = self::buscarGrupoAnexoProcPorId($idGrupoAnexoProc);

            // nao pode editar
            if (!self::permiteManterGrupo($processo)) {
                throw new NegocioException("Informação complementar não pode ser editada.");
            }

            // possui opções
            if (!$grupoAnexoProc->possuiOpcoesResposta()) {
                throw new NegocioException("Inf. Complementar não possui opções de resposta.");
            }

            // percorrendo matrizes para criar os itens de resposta e seus complementos
            //
            // matriz para armazenar as sqls de criação dos itens. É indexada pela ordem do Item, contendo todas as sqls referentes ao item.
            $matItemSubItemDep = array();
            foreach ($matItemResp as $ordemItem => $vetDadosItem) {
                $itemTemp = new ItemAnexoProc(NULL, $idGrupoAnexoProc, $ordemItem, $vetDadosItem[0], $vetDadosItem[1], ItemAnexoProc::getTpItem($respMultipla, $vetDadosItem[2]), $vetDadosItem[4], NULL);
                $matItemSubItemDep[$ordemItem][] = $itemTemp->getSqlCriacaoItem();

                // tem comp?
                if ($vetDadosItem[2]) {
                    // criando de acordo com o tipo
                    if ($vetDadosItem[3] == ItemAnexoProc::$TIPO_TEL_TEXTO) {
                        $compTemp = new SubitemAnexoProc(NULL, ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, SubitemAnexoProc::$ORDEM_UNICA, $matCompItemResp[$ordemItem][1], NULL, SubitemAnexoProc::$TIPO_SUBITEM_TEXTO, NULL, $matCompItemResp[$ordemItem][0]);
                        $matItemSubItemDep[$ordemItem][] = $compTemp->getSqlCriacaoSubitem();
                    } elseif ($vetDadosItem[3] == ItemAnexoProc::$TIPO_TEL_RADIO || $vetDadosItem[3] == ItemAnexoProc::$TIPO_TEL_CHECKBOX) {
                        // percorrendo lista de complementos do item
                        foreach ($matCompItemResp[$ordemItem] as $ordemComp => $vetDadosComp) {
                            $compTemp = new SubitemAnexoProc(NULL, ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, $ordemComp, $vetDadosComp[0], $vetDadosComp[1], ($vetDadosItem[3] == ItemAnexoProc::$TIPO_TEL_RADIO) ? SubitemAnexoProc::$TIPO_SUBITEM_RADIO : SubitemAnexoProc::$TIPO_SUBITEM_CHECKBOX, NULL, NULL);
                            $matItemSubItemDep[$ordemItem][] = $compTemp->getSqlCriacaoSubitem();
                        }
                    } else {
                        throw new NegocioException("Tipo de Complemento desconhecido!");
                    }
                }
            }

            // recuperando sqls para exclusão de itens anteriores
            $arraySqlIni [] = SubitemAnexoProc::getSqlExcluirPorGrupo($idGrupoAnexoProc);
            $arraySqlIni [] = ItemAnexoProc::getSqlExcluirPorProcGrupo($idProcesso, $idGrupoAnexoProc);

//            echo "<br/><br/>";
//            print_r($matItemSubItemDep);
//            echo "<br/><br/>";
//            exit;
//            
//            
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // persistindo no banco
            $conexao->execTransacaoMatrizDependente($matItemSubItemDep, $arraySqlIni);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao manter itens de resposta do grupo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $tpAvaliacao Tipo de avaliação. Parâmetro Opcional
     * @return \GrupoAnexoProc|null
     * @throws NegocioException
     */
    public static function buscarGrupoPorProcesso($idProcesso, $tpAvaliacao = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    GAP_ID_GRUPO_PROC,
                    PRC_ID_PROCESSO,
                    GAP_NR_ORDEM_EXIBICAO,
                    GAP_NM_GRUPO,
                    GAP_DS_GRUPO,
                    GAP_TP_GRUPO,
                    GAP_GRUPO_OBRIGATORIO,
                    GAP_NR_MAX_CARACTER,
                    GAP_TP_AVALIACAO
                from
                    tb_gap_grupo_anexo_proc
                where
                    PRC_ID_PROCESSO = '$idProcesso'";

            // adicionando tipo de avaliação
            if ($tpAvaliacao != NULL) {
                $sql .= " and GAP_TP_AVALIACAO = '$tpAvaliacao'";
            }

            // definindo ordem
            $sql .= " order by gap_nr_ordem_exibicao";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }

            $vetRetorno = array();

            //realizando iteração para recuperar as titulações
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);
                $grupoTemp = new GrupoAnexoProc($dados['GAP_ID_GRUPO_PROC'], $dados['PRC_ID_PROCESSO'], $dados['GAP_NR_ORDEM_EXIBICAO'], $dados['GAP_NM_GRUPO'], $dados['GAP_DS_GRUPO'], $dados['GAP_TP_GRUPO'], $dados['GAP_GRUPO_OBRIGATORIO'], $dados['GAP_NR_MAX_CARACTER'], $dados['GAP_TP_AVALIACAO']);

                //adicionando no vetor
                $vetRetorno[$i] = $grupoTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar grupos do processo.", $e);
        }
    }

    public static function buscarGrupoAnexoProcPorId($idGrupoAnexoProc, $idProcesso = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    GAP_ID_GRUPO_PROC,
                    PRC_ID_PROCESSO,
                    GAP_NR_ORDEM_EXIBICAO,
                    GAP_NM_GRUPO,
                    GAP_DS_GRUPO,
                    GAP_TP_GRUPO,
                    GAP_GRUPO_OBRIGATORIO,
                    GAP_NR_MAX_CARACTER,
                    GAP_TP_AVALIACAO
                from
                    tb_gap_grupo_anexo_proc
                where
                    GAP_ID_GRUPO_PROC = '$idGrupoAnexoProc'";

            if (!Util::vazioNulo($idProcesso)) {
                $sql .=" and PRC_ID_PROCESSO = '$idProcesso'";
            }
            $sql .= " order by gap_nr_ordem_exibicao";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Grupo anexo do processo não encontrado.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $grupoTemp = new GrupoAnexoProc($dados['GAP_ID_GRUPO_PROC'], $dados['PRC_ID_PROCESSO'], $dados['GAP_NR_ORDEM_EXIBICAO'], $dados['GAP_NM_GRUPO'], $dados['GAP_DS_GRUPO'], $dados['GAP_TP_GRUPO'], $dados['GAP_GRUPO_OBRIGATORIO'], $dados['GAP_NR_MAX_CARACTER'], $dados['GAP_TP_AVALIACAO']);

            return $grupoTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar grupo anexo do processo.", $e);
        }
    }

    /**
     * Esta função verifica se os grupos estão OK para a criação de uma chamada
     * 
     * @param int $idProcesso
     * @return boolean
     * @throws NegocioException
     */
    public static function validarGrupoParaChamada($idProcesso) {
        try {
            $tpMultEscolha = self::$TIPO_AGRUPAMENTO_PERGUNTA;

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // sql
            // basicamente, verifica se todas as questões de múltipla escolha possuem opções de resposta
            $sql = "select 
                    count(*) as cont
                from
                    tb_gap_grupo_anexo_proc gap
                where
                    PRC_ID_PROCESSO = '$idProcesso'
                    and GAP_TP_GRUPO = '$tpMultEscolha'
                    and (select count(*) from tb_iap_item_anexo_proc iap where iap.GAP_ID_GRUPO_PROC = gap.GAP_ID_GRUPO_PROC) = 0";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            return ConexaoMysql::getResult("cont", $resp) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar validar grupos do processo.", $e);
        }
    }

    public static function contarGrupoPorProcesso($idProcesso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    count(*) as cont
                from
                    tb_gap_grupo_anexo_proc
                where
                    PRC_ID_PROCESSO = '$idProcesso'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }
            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar grupos do processo.", $e);
        }
    }

    /**
     * 
     * @param Processo $processo
     * @return boolean
     * @throws NegocioException
     */
    public static function permiteManterGrupo($processo) {
        try {
            // verificando permissão de edicao de processo
            if ($processo->permiteEdicao(TRUE)) {
                // tem alguma inscrição no edital
                return InscricaoProcesso::contarInscricaoPorProcessoCham($processo->getPRC_ID_PROCESSO()) == 0;
            } else {
                return false; // processo não deixa editar
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar permissão de manutenção de grupo.", $e);
        }
    }

    public function getDsTipoGrupoObj() {
        return self::getDsTipoGrupo($this->GAP_TP_GRUPO);
    }

    public function possuiOpcoesResposta() {
        return $this->GAP_TP_GRUPO == self::$TIPO_AGRUPAMENTO_PERGUNTA;
    }

    public function getDsGrupoObrigatorio() {
        return NGUtil::getDsSimNao($this->GAP_GRUPO_OBRIGATORIO);
    }

    public function getDsTipoAvalObj() {
        return self::getDsTipoAval($this->GAP_TP_AVALIACAO);
    }

    /* GET FIELDS FROM TABLE */

    function getGAP_ID_GRUPO_PROC() {
        return $this->GAP_ID_GRUPO_PROC;
    }

    /* End of get GAP_ID_GRUPO_PROC */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getGAP_NR_ORDEM_EXIBICAO() {
        return $this->GAP_NR_ORDEM_EXIBICAO;
    }

    /* End of get GAP_NR_ORDEM_EXIBICAO */

    function getGAP_NM_GRUPO() {
        return $this->GAP_NM_GRUPO;
    }

    /* End of get GAP_NM_GRUPO */

    function getGAP_DS_GRUPO() {
        return $this->GAP_DS_GRUPO;
    }

    /* End of get GAP_DS_GRUPO */

    function getGAP_TP_GRUPO() {
        return $this->GAP_TP_GRUPO;
    }

    /* End of get GAP_TP_GRUPO */

    function getGAP_GRUPO_OBRIGATORIO() {
        return $this->GAP_GRUPO_OBRIGATORIO;
    }

    /* End of get GAP_GRUPO_OBRIGATORIO */

    function getGAP_NR_MAX_CARACTER() {
        return $this->GAP_NR_MAX_CARACTER;
    }

    /* End of get GAP_NR_MAX_CARACTER */

    function getGAP_TP_AVALIACAO() {
        return $this->GAP_TP_AVALIACAO;
    }

    /* End of get GAP_TP_AVALIACAO */



    /* SET FIELDS FROM TABLE */

    function setGAP_ID_GRUPO_PROC($value) {
        $this->GAP_ID_GRUPO_PROC = $value;
    }

    /* End of SET GAP_ID_GRUPO_PROC */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setGAP_NR_ORDEM_EXIBICAO($value) {
        $this->GAP_NR_ORDEM_EXIBICAO = $value;
    }

    /* End of SET GAP_NR_ORDEM_EXIBICAO */

    function setGAP_NM_GRUPO($value) {
        $this->GAP_NM_GRUPO = $value;
    }

    /* End of SET GAP_NM_GRUPO */

    function setGAP_DS_GRUPO($value) {
        $this->GAP_DS_GRUPO = $value;
    }

    /* End of SET GAP_DS_GRUPO */

    function setGAP_TP_GRUPO($value) {
        $this->GAP_TP_GRUPO = $value;
    }

    /* End of SET GAP_TP_GRUPO */

    function setGAP_GRUPO_OBRIGATORIO($value) {
        $this->GAP_GRUPO_OBRIGATORIO = $value;
    }

    /* End of SET GAP_GRUPO_OBRIGATORIO */

    function setGAP_NR_MAX_CARACTER($value) {
        $this->GAP_NR_MAX_CARACTER = $value;
    }

    /* End of SET GAP_NR_MAX_CARACTER */
}

?>
