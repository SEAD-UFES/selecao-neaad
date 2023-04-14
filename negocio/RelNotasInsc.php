<?php

/**
 * tb_rni_rel_notas_insc class
 * This class manipulates the table RelNotasInsc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 22/05/2014
 * */
class RelNotasInsc {

    private $RNI_ID_REL_NOTA;
    private $IPR_ID_INSCRICAO;
    private $CAP_ID_CATEGORIA_AVAL;
    private $IAP_ID_ITEM_AVAL;
    private $RNI_ORDEM;
    private $RNI_TP_AVALIACAO;
    private $RNI_DS_OBJ_AVAL;
    private $RNI_VL_NOTA_REAL;
    private $RNI_VL_NOTA_NORMALIZADA;
    private $RNI_ST_AVALIACAO;
    private $RNI_ID_USUARIO_RESP;
    private $RNI_DS_JUSTIFICATIVA_AVAL;
    private $RNI_LOG_DT_ALTERACAO;
    // campos herdados
    public $USR_DS_NOME_RESP;
    //
    public static $ORDEM_MAXIMA = 999;
    // constantes 
    public static $MAX_CARACTER_JUST_AVAL = 1000;
    // Tipos de avaliaçao
    public static $TP_AVAL_AUTOMATICA = 'A';
    public static $TP_AVAL_MANUAL = 'M';
    // situacao da avaliaçao
    public static $SIT_ATIVA = 'A';
    public static $SIT_IGNORADA = 'I';

    /* Construtor padrão da classe */

    public function __construct($RNI_ID_REL_NOTA, $IPR_ID_INSCRICAO, $CAP_ID_CATEGORIA_AVAL, $IAP_ID_ITEM_AVAL, $RNI_ORDEM, $RNI_TP_AVALIACAO, $RNI_DS_OBJ_AVAL, $RNI_VL_NOTA_REAL, $RNI_VL_NOTA_NORMALIZADA, $RNI_ST_AVALIACAO, $RNI_ID_USUARIO_RESP, $RNI_DS_JUSTIFICATIVA_AVAL, $RNI_LOG_DT_ALTERACAO = NULL) {
        $this->RNI_ID_REL_NOTA = $RNI_ID_REL_NOTA;
        $this->IPR_ID_INSCRICAO = $IPR_ID_INSCRICAO;
        $this->CAP_ID_CATEGORIA_AVAL = $CAP_ID_CATEGORIA_AVAL;
        $this->IAP_ID_ITEM_AVAL = $IAP_ID_ITEM_AVAL;
        $this->RNI_ORDEM = $RNI_ORDEM;
        $this->RNI_TP_AVALIACAO = $RNI_TP_AVALIACAO;
        $this->RNI_DS_OBJ_AVAL = $RNI_DS_OBJ_AVAL;
        $this->RNI_VL_NOTA_REAL = $RNI_VL_NOTA_REAL;
        $this->RNI_VL_NOTA_NORMALIZADA = $RNI_VL_NOTA_NORMALIZADA;
        $this->RNI_ST_AVALIACAO = $RNI_ST_AVALIACAO;
        $this->RNI_ID_USUARIO_RESP = $RNI_ID_USUARIO_RESP;
        $this->RNI_DS_JUSTIFICATIVA_AVAL = $RNI_DS_JUSTIFICATIVA_AVAL;
        $this->RNI_LOG_DT_ALTERACAO = $RNI_LOG_DT_ALTERACAO;
    }

    public function CLAS_getSqlRelNotasCand($idInscricao, $forcaCriar) {

        // tratando idItem
        $idItem = NGUtil::trataCampoStrParaBD($this->IAP_ID_ITEM_AVAL);

        // verificando se ja existe o relatorio
        if (!$forcaCriar && self::contarRelNotasporCatItemInsc($idInscricao, $this->CAP_ID_CATEGORIA_AVAL, $this->IAP_ID_ITEM_AVAL) > 0) {

            // sql de update
            return "update tb_rni_rel_notas_insc set
                    RNI_DS_OBJ_AVAL = '{$this->RNI_DS_OBJ_AVAL}',
                    RNI_VL_NOTA_REAL = {$this->RNI_VL_NOTA_REAL},
                    RNI_VL_NOTA_NORMALIZADA = {$this->RNI_VL_NOTA_NORMALIZADA},
                    RNI_ID_USUARIO_RESP = '{$this->RNI_ID_USUARIO_RESP}',
                    RNI_ST_AVALIACAO = '{$this->RNI_ST_AVALIACAO}',    
                    RNI_LOG_DT_ALTERACAO = now()
                    where CAP_ID_CATEGORIA_AVAL = '{$this->CAP_ID_CATEGORIA_AVAL}'
                    and IAP_ID_ITEM_AVAL = $idItem
                    and ipr_id_inscricao = '$idInscricao'";
        } else {
            // retorna sql de criaçao
            return "insert into tb_rni_rel_notas_insc(IPR_ID_INSCRICAO,CAP_ID_CATEGORIA_AVAL,
             IAP_ID_ITEM_AVAL,RNI_ORDEM,RNI_TP_AVALIACAO,RNI_DS_OBJ_AVAL,RNI_VL_NOTA_REAL,
             RNI_VL_NOTA_NORMALIZADA,RNI_ST_AVALIACAO,RNI_ID_USUARIO_RESP,RNI_LOG_DT_ALTERACAO)
             values('{$this->IPR_ID_INSCRICAO}','{$this->CAP_ID_CATEGORIA_AVAL}',$idItem,
             {$this->RNI_ORDEM},'{$this->RNI_TP_AVALIACAO}','{$this->RNI_DS_OBJ_AVAL}',
             {$this->RNI_VL_NOTA_REAL},{$this->RNI_VL_NOTA_NORMALIZADA},'{$this->RNI_ST_AVALIACAO}',
             '{$this->RNI_ID_USUARIO_RESP}',now())";
        }
    }

    /**
     * Retorna o sql de criaçao do relatorio. 
     * 
     * Use com cuidado. FUNCAO DE PROCESSAMENTO INTERNO. 
     * @return string
     */
    public function get_sql_criacao() {
        $idItem = NGUtil::trataCampoStrParaBD($this->IAP_ID_ITEM_AVAL);
        $dsJustificativa = NGUtil::trataCampoStrParaBD($this->RNI_DS_JUSTIFICATIVA_AVAL);

        $ret = "insert into tb_rni_rel_notas_insc(IPR_ID_INSCRICAO,CAP_ID_CATEGORIA_AVAL,
             IAP_ID_ITEM_AVAL,RNI_ORDEM,RNI_TP_AVALIACAO,RNI_DS_OBJ_AVAL,RNI_VL_NOTA_REAL,
             RNI_VL_NOTA_NORMALIZADA,RNI_ST_AVALIACAO,RNI_ID_USUARIO_RESP,RNI_LOG_DT_ALTERACAO,RNI_DS_JUSTIFICATIVA_AVAL)
             values('{$this->IPR_ID_INSCRICAO}','{$this->CAP_ID_CATEGORIA_AVAL}',$idItem,
             {$this->RNI_ORDEM},'{$this->RNI_TP_AVALIACAO}','{$this->RNI_DS_OBJ_AVAL}',
             {$this->RNI_VL_NOTA_REAL},{$this->RNI_VL_NOTA_NORMALIZADA},'{$this->RNI_ST_AVALIACAO}',
             '{$this->RNI_ID_USUARIO_RESP}',now(), $dsJustificativa)";

        return $ret;
    }

    /**
     * Retorna o sql de atualizacao de um relatorio manual de uma categoria automatica
     * 
     * Use com cuidado. FUNCAO DE PROCESSAMENTO INTERNO. 
     * @return string
     */
    public function get_sql_atualizacao_man_cat_auto() {
        $dsJustificativa = NGUtil::trataCampoStrParaBD($this->RNI_DS_JUSTIFICATIVA_AVAL);
        $avalMan = RelNotasInsc::$TP_AVAL_MANUAL;

        $ret = "update tb_rni_rel_notas_insc set
                    RNI_VL_NOTA_REAL = {$this->RNI_VL_NOTA_REAL},
                    RNI_VL_NOTA_NORMALIZADA = {$this->RNI_VL_NOTA_NORMALIZADA},
                    RNI_ID_USUARIO_RESP = '{$this->RNI_ID_USUARIO_RESP}',
                    RNI_DS_JUSTIFICATIVA_AVAL = $dsJustificativa,    
                    RNI_LOG_DT_ALTERACAO = now()
                    where CAP_ID_CATEGORIA_AVAL = '{$this->CAP_ID_CATEGORIA_AVAL}'
                    and ipr_id_inscricao = '{$this->IPR_ID_INSCRICAO}'                   
                    and IAP_ID_ITEM_AVAL IS NULL 
                    and RNI_TP_AVALIACAO = '$avalMan'";

        return $ret;
    }

    /**
     * Retorna o sql para remover um relatorio manual de uma categoria automatica
     * 
     * Use com cuidado. FUNCAO DE PROCESSAMENTO INTERNO. 
     * @return string
     */
    public function get_sql_remocao_man_cat_auto() {
        $avalMan = RelNotasInsc::$TP_AVAL_MANUAL;

        return "delete from tb_rni_rel_notas_insc where IPR_ID_INSCRICAO = '{$this->IPR_ID_INSCRICAO}' and 
                CAP_ID_CATEGORIA_AVAL = '{$this->CAP_ID_CATEGORIA_AVAL}' and IAP_ID_ITEM_AVAL IS NULL
                and RNI_TP_AVALIACAO = '$avalMan'";
    }

    public function getSqlAlterarSituacao($novaSit) {
        if (Util::vazioNulo($this->RNI_ID_REL_NOTA)) {
            throw new NegocioException("Não é possível gerar sql para relatório Nulo.");
        }
        return "update tb_rni_rel_notas_insc set RNI_ST_AVALIACAO = '$novaSit' where RNI_ID_REL_NOTA = '{$this->RNI_ID_REL_NOTA}'";
    }

    public static function getSqlExclusaoPorItemAval($idItemAval) {
        return "delete from tb_rni_rel_notas_insc where IAP_ID_ITEM_AVAL = '$idItemAval'";
    }

    public static function getSqlExclusaoPorCatAval($idCategoria) {
        return "delete from tb_rni_rel_notas_insc where CAP_ID_CATEGORIA_AVAL = '$idCategoria'";
    }

    /**
     * Funcao que exclui itens de uma categoria especifica
     * 
     * PROCESSAMENTO INTERNO
     * 
     * @param int $idInscricao
     * @param int $idCategoria
     * @return string
     */
    public static function CLAS_getSqlExclusaoCatAuto($idInscricao, $idCategoria) {
        $auto = self::$TP_AVAL_AUTOMATICA;
        return "delete from tb_rni_rel_notas_insc where CAP_ID_CATEGORIA_AVAL = '$idCategoria'
               and IPR_ID_INSCRICAO = '$idInscricao' and RNI_TP_AVALIACAO = '$auto'";
    }

    public static function CLAS_getSqlExclusaoAjuste($idInscricao, $idCategoria) {
        $auto = self::$TP_AVAL_AUTOMATICA;
        return "delete from tb_rni_rel_notas_insc where CAP_ID_CATEGORIA_AVAL = '$idCategoria'
               and IPR_ID_INSCRICAO = '$idInscricao' and RNI_TP_AVALIACAO = '$auto' 
               and IAP_ID_ITEM_AVAL IS NULL and RNI_VL_NOTA_REAL < 0";
    }

    public static function getStrSqlExclusaoPorInscricao($idInscricao) {
        return "delete from tb_rni_rel_notas_insc where IPR_ID_INSCRICAO = '$idInscricao'";
    }

    public static function getStrSqlExcPorInscCatItem($idInscricao, $idCategoria, $idItem) {
        return "delete from tb_rni_rel_notas_insc where IPR_ID_INSCRICAO = '$idInscricao'
                and CAP_ID_CATEGORIA_AVAL = '$idCategoria' and IAP_ID_ITEM_AVAL = '$idItem'";
    }

    public static function getStrSqlExclusaoPorInscCatExcMan($idInscricao, $idCategoria, $listaExcecao) {
        $man = RelNotasInsc::$TP_AVAL_MANUAL;
        $ret = "delete from tb_rni_rel_notas_insc where IPR_ID_INSCRICAO = '$idInscricao'
               and CAP_ID_CATEGORIA_AVAL = '$idCategoria' and IAP_ID_ITEM_AVAL IS NOT NULL and 
                RNI_TP_AVALIACAO = '$man'";
        if ($listaExcecao != NULL && count($listaExcecao) > 0) {
            $ret .= " and IAP_ID_ITEM_AVAL NOT in ('$listaExcecao[0]'";
            for ($i = 1; $i < count($listaExcecao); $i++) {
                $ret .= ", '$listaExcecao[$i]'";
            }
            $ret .= ")";
        }
        return $ret;
    }

    public static function getDsObjAvalManualCatAuto() {
        return "Avaliação Manual da Categoria";
    }

    public static function contarRelNotasporCatItem($idCategoria, $idItem = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_rni_rel_notas_insc
                    where CAP_ID_CATEGORIA_AVAL = '$idCategoria'";

            if (!Util::vazioNulo($idItem)) {
                $sql .= " and IAP_ID_ITEM_AVAL = '$idItem'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar relatório de notas do candidato.", $e);
        }
    }

    public static function contarRelNotasporCatItemInsc($idInscricao, $idCategoria, $idItem = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_rni_rel_notas_insc
                    where CAP_ID_CATEGORIA_AVAL = '$idCategoria'
                    and IPR_ID_INSCRICAO = '$idInscricao'";

            if (!Util::vazioNulo($idItem)) {
                $sql .= " and IAP_ID_ITEM_AVAL = '$idItem'";
            }


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar relatório de notas do candidato.", $e);
        }
    }

    public static function contarRelNotasporUsuResp($idUsuResp) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_rni_rel_notas_insc
                    where RNI_ID_USUARIO_RESP = '$idUsuResp'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar relatório de notas.", $e);
        }
    }

    public static function contarRelNotasManPorCatAuto($idInscricao, $idCategoria) {
        try {
            $avalMan = RelNotasInsc::$TP_AVAL_MANUAL;

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_rni_rel_notas_insc
                    where CAP_ID_CATEGORIA_AVAL = '$idCategoria'
                    and IPR_ID_INSCRICAO = '$idInscricao'
                    and IAP_ID_ITEM_AVAL IS NULL 
                    and RNI_TP_AVALIACAO = '$avalMan'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar relatório de notas manual em categoria automática do candidato.", $e);
        }
    }

    /**
     * 
     * @param int $idChamada
     * @param int $stAvaliacao
     * @return \RelNotasInsc|null - Array com relatorio de notas
     * @throws NegocioException
     */
    public static function buscarRelNotasPorChamada($idChamada, $stAvaliacao = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        RNI_ID_REL_NOTA,
                        rni.IPR_ID_INSCRICAO,
                        CAP_ID_CATEGORIA_AVAL,
                        IAP_ID_ITEM_AVAL,
                        RNI_ORDEM,
                        RNI_TP_AVALIACAO,
                        RNI_DS_OBJ_AVAL,
                        RNI_VL_NOTA_REAL,
                        RNI_VL_NOTA_NORMALIZADA,
                        RNI_ST_AVALIACAO,
                        RNI_ID_USUARIO_RESP,
                        USR_DS_NOME as USR_DS_NOME_RESP,
                        RNI_DS_JUSTIFICATIVA_AVAL,
                        DATE_FORMAT(`RNI_LOG_DT_ALTERACAO`, '%d/%m/%Y %T') AS RNI_LOG_DT_ALTERACAO
                    from
                        tb_rni_rel_notas_insc rni
                            join
                        tb_ipr_inscricao_processo ipr ON rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
                            left join
                        tb_usr_usuario usr on usr.USR_ID_USUARIO = RNI_ID_USUARIO_RESP
                    where
                        PCH_ID_CHAMADA = '$idChamada'";

            if ($stAvaliacao != NULL) {
                $sql .= " and RNI_ST_AVALIACAO = '$stAvaliacao'";
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

                $relNotaTemp = new RelNotasInsc($dados['RNI_ID_REL_NOTA'], $dados['IPR_ID_INSCRICAO'], $dados['CAP_ID_CATEGORIA_AVAL'], $dados['IAP_ID_ITEM_AVAL'], $dados['RNI_ORDEM'], $dados['RNI_TP_AVALIACAO'], $dados['RNI_DS_OBJ_AVAL'], $dados['RNI_VL_NOTA_REAL'], $dados['RNI_VL_NOTA_NORMALIZADA'], $dados['RNI_ST_AVALIACAO'], $dados['RNI_ID_USUARIO_RESP'], $dados['RNI_DS_JUSTIFICATIVA_AVAL'], $dados['RNI_LOG_DT_ALTERACAO']);

                // campos herdados
                $relNotaTemp->USR_DS_NOME_RESP = $dados['USR_DS_NOME_RESP'];

                //adicionando no vetor
                $vetRetorno[$i] = $relNotaTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar relatório de notas da chamada.", $e);
        }
    }

    public function getNotaComMasc() {
        return money_format("%i", $this->RNI_VL_NOTA_REAL);
    }

    public function getNotaNormalizadaComMasc() {
        return money_format("%i", $this->RNI_VL_NOTA_NORMALIZADA);
    }

    public function isIgnorado() {
        return $this->RNI_ST_AVALIACAO != NULL && $this->RNI_ST_AVALIACAO == RelNotasInsc::$SIT_IGNORADA;
    }

    public function htmlMarcacaoIgnorado() {
        if ($this->RNI_ST_AVALIACAO != NULL && $this->RNI_ST_AVALIACAO == RelNotasInsc::$SIT_IGNORADA) {
            return "<i class='fa fa-ban'></i>";
        }
        return "<i class='fa fa-check'></i>";
    }

    /**
     * Observacao importante: $idItemIsNotNull e $idItemIsNull nao podem ser 'True' ao mesmo tempo.
     * Caso isso ocorra, $idItemIsNotNull sera prioridade.
     * 
     * @param int $idChamada
     * @param int $idInscricao
     * @param int $idCategoria
     * @param int $idItem
     * @param int $stAvaliacao
     * @param boolean $idItemIsNotNull - Se true e $idItem = NULL, entao ele restringe a busca por itens cujo $idItem nao eh nulo
     * @param int $tpAvaliacao
     * @param boolean $idItemIsNull - Se true e $idItem = NULL, entao ele restringe a busca por itens cujo $idItem eh nulo
     * @return \RelNotasInsc|null - Array com relatorio de notas
     * @throws NegocioException
     */
    public static function buscarRelNotasPorInscCatItem($idChamada, $idInscricao, $idCategoria = NULL, $idItem = NULL, $stAvaliacao = NULL, $idItemIsNotNull = FALSE, $tpAvaliacao = NULL, $idItemIsNull = FALSE) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        RNI_ID_REL_NOTA,
                        rni.IPR_ID_INSCRICAO,
                        CAP_ID_CATEGORIA_AVAL,
                        IAP_ID_ITEM_AVAL,
                        RNI_ORDEM,
                        RNI_TP_AVALIACAO,
                        RNI_DS_OBJ_AVAL,
                        RNI_VL_NOTA_REAL,
                        RNI_VL_NOTA_NORMALIZADA,
                        RNI_ST_AVALIACAO,
                        RNI_ID_USUARIO_RESP,
                        USR_DS_NOME as USR_DS_NOME_RESP,
                        RNI_DS_JUSTIFICATIVA_AVAL,
                        DATE_FORMAT(`RNI_LOG_DT_ALTERACAO`, '%d/%m/%Y %T') AS RNI_LOG_DT_ALTERACAO
                    from
                        tb_rni_rel_notas_insc rni
                            join
                        tb_ipr_inscricao_processo ipr ON rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
                            left join
                        tb_usr_usuario usr on usr.USR_ID_USUARIO = RNI_ID_USUARIO_RESP
                    where
                        PCH_ID_CHAMADA = '$idChamada'
                    and
                        rni.IPR_ID_INSCRICAO = '$idInscricao'";

            if ($idCategoria != NULL) {
                $sql .= " and CAP_ID_CATEGORIA_AVAL = '$idCategoria'";
            }

            if ($idItem != NULL) {
                $sql .= " and IAP_ID_ITEM_AVAL = '$idItem'";
            } elseif ($idItemIsNotNull) {
                $sql .= " and IAP_ID_ITEM_AVAL IS NOT NULL";
            } elseif ($idItemIsNull) {
                $sql .= " and IAP_ID_ITEM_AVAL IS NULL";
            }

            if ($stAvaliacao != NULL) {
                $sql .= " and RNI_ST_AVALIACAO = '$stAvaliacao'";
            }

            if ($tpAvaliacao != NULL) {
                $sql .= " and RNI_TP_AVALIACAO = '$tpAvaliacao'";
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

                $relNotaTemp = new RelNotasInsc($dados['RNI_ID_REL_NOTA'], $dados['IPR_ID_INSCRICAO'], $dados['CAP_ID_CATEGORIA_AVAL'], $dados['IAP_ID_ITEM_AVAL'], $dados['RNI_ORDEM'], $dados['RNI_TP_AVALIACAO'], $dados['RNI_DS_OBJ_AVAL'], $dados['RNI_VL_NOTA_REAL'], $dados['RNI_VL_NOTA_NORMALIZADA'], $dados['RNI_ST_AVALIACAO'], $dados['RNI_ID_USUARIO_RESP'], $dados['RNI_DS_JUSTIFICATIVA_AVAL'], $dados['RNI_LOG_DT_ALTERACAO']);

                // campos herdados
                $relNotaTemp->USR_DS_NOME_RESP = $dados['USR_DS_NOME_RESP'];

                //adicionando no vetor
                $vetRetorno[$i] = $relNotaTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar relatório de notas por inscrição-categoria-item.", $e);
        }
    }

    /**
     * 
     * @param array $vetIdsRel - Array com relatorios a serem ignorados
     * @return string
     */
    public static function getStrSqlIgnorarRelPorIds($vetIdsRel) {
        if (Util::vazioNulo($vetIdsRel) || count($vetIdsRel) === 0) {
            return NULL;
        }
        $ret = "update tb_rni_rel_notas_insc set rni_st_avaliacao = '" . self::$SIT_IGNORADA
                . "' where rni_id_rel_nota in (";
        $ret .= "'$vetIdsRel[0]'";
        for ($i = 1; $i < count($vetIdsRel); $i++) {
            $ret .= ", '$vetIdsRel[$i]'";
        }
        $ret .= ")";

        return $ret;
    }

    public static function getStrSqlAtivarPorInsc($idInscricao, $idProcesso, $nrEtapa) {
        $ret = "update tb_rni_rel_notas_insc set rni_st_avaliacao = '" . self::$SIT_ATIVA
                . "' where ipr_id_inscricao = '$idInscricao' and cap_id_categoria_aval in
                (select cap_id_categoria_aval from tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC where
                cap.prc_id_processo = '$idProcesso' and eap_nr_etapa_aval = '$nrEtapa')";
        return $ret;
    }

    /**
     * @param int $idChamada
     * @param int $idCategoria
     * @param int $idItem - Pode ser nulo
     * @param boolean $idItemIsNull - Quando $idItem eh nulo, informar se eh para trazer apenas categorias cujo id do item eh nulo
     * @param int $stAvaliacao - Pode ser nulo
     * @param boolean $indexarIdInsc - Se true, entao o vetor e indexado pelo id da inscriçao.
     * Essa opçao so funciona se $idItem eh diferente de nulo
     * @param int $tpAvaliacao
     * @return \RelNotasInsc|null - Array com o relatorio de notas. 
     * @throws NegocioException
     */
    public static function buscarRelNotasPorCatItem($idChamada, $idCategoria, $idItem = NULL, $idItemIsNull = FALSE, $stAvaliacao = NULL, $indexarIdInsc = FALSE, $tpAvaliacao = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        RNI_ID_REL_NOTA,
                        rni.IPR_ID_INSCRICAO,
                        CAP_ID_CATEGORIA_AVAL,
                        IAP_ID_ITEM_AVAL,
                        RNI_ORDEM,
                        RNI_TP_AVALIACAO,
                        RNI_DS_OBJ_AVAL,
                        RNI_VL_NOTA_REAL,
                        RNI_VL_NOTA_NORMALIZADA,
                        RNI_ST_AVALIACAO,
                        RNI_ID_USUARIO_RESP,
                        USR_DS_NOME as USR_DS_NOME_RESP,
                        RNI_DS_JUSTIFICATIVA_AVAL,
                        DATE_FORMAT(`RNI_LOG_DT_ALTERACAO`, '%d/%m/%Y %T') AS RNI_LOG_DT_ALTERACAO
                    from
                        tb_rni_rel_notas_insc rni
                            join
                        tb_ipr_inscricao_processo ipr ON rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
                            left join
                        tb_usr_usuario usr on usr.USR_ID_USUARIO = RNI_ID_USUARIO_RESP
                    where
                        PCH_ID_CHAMADA = '$idChamada'
                    and
                        CAP_ID_CATEGORIA_AVAL = '$idCategoria'";

            if ($idItem != NULL) {
                $sql .= " and IAP_ID_ITEM_AVAL = '$idItem'";
            } elseif ($idItemIsNull) {
                $sql .= " and IAP_ID_ITEM_AVAL IS NULL";
            }

            if ($stAvaliacao != NULL) {
                $sql .= " and RNI_ST_AVALIACAO = '$stAvaliacao'";
            }

            if ($tpAvaliacao != NULL) {
                $sql .= " and RNI_TP_AVALIACAO = '$tpAvaliacao'";
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

                $relNotaTemp = new RelNotasInsc($dados['RNI_ID_REL_NOTA'], $dados['IPR_ID_INSCRICAO'], $dados['CAP_ID_CATEGORIA_AVAL'], $dados['IAP_ID_ITEM_AVAL'], $dados['RNI_ORDEM'], $dados['RNI_TP_AVALIACAO'], $dados['RNI_DS_OBJ_AVAL'], $dados['RNI_VL_NOTA_REAL'], $dados['RNI_VL_NOTA_NORMALIZADA'], $dados['RNI_ST_AVALIACAO'], $dados['RNI_ID_USUARIO_RESP'], $dados['RNI_DS_JUSTIFICATIVA_AVAL'], $dados['RNI_LOG_DT_ALTERACAO']);

                // campos herdados
                $relNotaTemp->USR_DS_NOME_RESP = $dados['USR_DS_NOME_RESP'];

                //adicionando no vetor
                if ($idItem != NULL && $indexarIdInsc) {
                    $vetRetorno[$relNotaTemp->IPR_ID_INSCRICAO] = $relNotaTemp;
                } else {
                    $vetRetorno[$i] = $relNotaTemp;
                }
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar relatório de notas por inscrição-categoria-item.", $e);
        }
    }

    public static function getNotaSemNota() {
        return money_format("%i", 0);
    }

    public static function getNotaSemNotaMan() {
        return "";
    }

    public static function formataNota($nota) {
        return money_format("%i", $nota);
    }

    /**
     * 
     * @param InscricaoProcesso $inscricao
     * @param RelNotasInsc $relNotas
     * @param ItemAvalProc $itemAvalProc
     * @return type
     */
    public static function getObjAvalHtml($inscricao, $relNotas, $itemAvalProc) {
        // tratando caso específico do item de informação complementar
        if ($itemAvalProc->getIAP_TP_ITEM() == ItemAvalProc::$TP_INF_COMP) {
            // buscando resposta do candidato
            $vetResp = RespAnexoProc::buscarRespPorProcChamadaGrupo($inscricao->getPRC_ID_PROCESSO(), $inscricao->getPCH_ID_CHAMADA(), $itemAvalProc->getIdGrupoInfComp(), $inscricao->getIPR_ID_INSCRICAO(), TRUE);
            if (isset($vetResp[$inscricao->getIPR_ID_INSCRICAO()])) {
                return $vetResp[$inscricao->getIPR_ID_INSCRICAO()];
            } else {
                return RespAnexoProc::getStrSemResposta();
            }
        }

        // Retornando objeto da avaliação
        if ($relNotas != NULL) {
            return htmlentities($relNotas->RNI_DS_OBJ_AVAL);
        }
    }

    /**
     * @param int $idChamada
     * @param int $idCategoria
     * @param int $idItem - Pode ser nulo
     * @param boolean $idItemIsNull - Quando $idItem eh nulo, informar se eh para somar apenas categorias cujo id do item eh nulo
     * @param int $stAvaliacao - Pode ser nulo
     * @param boolean $indexarIdInsc - Se true, entao o vetor e indexado pelo id da inscriçao.
     * @param int $tpAvaliacao - Pode ser nulo
     * @return \array|null - Array com o somatorio, indexado numericamente ou 
     * pelo id da inscriçao. Formas:
     * 1 - (chave => array[idInscricao, soma])
     * 2 - (IdInscricao => soma)
     * @throws NegocioException
     */
    public static function buscarSomaNotasPorCatItem($idChamada, $idCategoria, $idItem = NULL, $idItemIsNull = FALSE, $stAvaliacao = NULL, $indexarIdInsc = FALSE, $tpAvaliacao = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        rni.IPR_ID_INSCRICAO,
                        sum(RNI_VL_NOTA_NORMALIZADA) as soma
                    from
                        tb_rni_rel_notas_insc rni
                        join
                        tb_ipr_inscricao_processo ipr ON rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
                    where
                        PCH_ID_CHAMADA = '$idChamada'
                    and
                        CAP_ID_CATEGORIA_AVAL = '$idCategoria'";

            if ($idItem != NULL) {
                $sql .= " and IAP_ID_ITEM_AVAL = '$idItem'";
            } elseif ($idItemIsNull) {
                $sql .= " and IAP_ID_ITEM_AVAL IS NULL";
            }

            if ($stAvaliacao != NULL) {
                $sql .= " and RNI_ST_AVALIACAO = '$stAvaliacao'";
            }

            if ($tpAvaliacao != NULL) {
                $sql .= " and RNI_TP_AVALIACAO = '$tpAvaliacao'";
            }

            // adicionando group by
            $sql .= " group by (rni.IPR_ID_INSCRICAO)";

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

                //adicionando no vetor
                if ($indexarIdInsc) {
                    $vetRetorno[$dados['IPR_ID_INSCRICAO']] = $dados['soma'];
                } else {
                    $vetRetorno[$i] = array($dados['IPR_ID_INSCRICAO'], $dados['soma']);
                }
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao somar notas por inscrição-categoria-item.", $e);
        }
    }

    /**
     * Funçao que busca o somatorio de uma categoria de uma dada inscriçao
     * 
     * @param int $idInscricao
     * @param int $idCategoria
     * @param int $stAvaliacao
     * @param int $tpAvaliacao
     * @return Array na forma [soma, somaNorm]
     * @throws NegocioException
     */
    public static function buscarSomaNotasPorCatInsc($idInscricao, $idCategoria, $stAvaliacao = NULL, $tpAvaliacao = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        sum(RNI_VL_NOTA_NORMALIZADA) as somaNorm,
                        sum(RNI_VL_NOTA_REAL) as soma
                    from
                        tb_rni_rel_notas_insc rni
                    where
                        IPR_ID_INSCRICAO = '$idInscricao'
                    and
                        CAP_ID_CATEGORIA_AVAL = '$idCategoria'";


            if ($stAvaliacao != NULL) {
                $sql .= " and RNI_ST_AVALIACAO = '$stAvaliacao'";
            }

            if ($tpAvaliacao != NULL) {
                $sql .= " and RNI_TP_AVALIACAO = '$tpAvaliacao'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando consistencia dos dados
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas != 1) {
                //retornando nulo
                return NULL;
            }

            // recuperando dados para gerar retorno
            $dados = ConexaoMysql::getLinha($resp);


            return array('soma' => $dados['soma'], 'somaNorm' => $dados['somaNorm']);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao somar notas da inscrição por categoria.", $e);
        }
    }

    /**
     * Funçao que busca o somatorio de uma etapa de uma dada inscriçao
     * 
     * @param int $idProcesso
     * @param int $nrEtapa
     * @param int $idInscricao
     * @param int $stAvaliacao
     * @param int $tpAvaliacao
     * @return Array na forma [soma, somaNorm]
     * @throws NegocioException
     */
    public static function buscarSomaNotasPorEtapaInsc($idProcesso, $nrEtapa, $idInscricao, $stAvaliacao = NULL, $tpAvaliacao = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        sum(RNI_VL_NOTA_NORMALIZADA) as somaNorm,
                        sum(RNI_VL_NOTA_REAL) as soma
                    from
                        tb_rni_rel_notas_insc rni
                        join tb_cap_categoria_aval_proc cap on cap.cap_id_categoria_aval = rni.cap_id_categoria_aval
                    where
                        IPR_ID_INSCRICAO = '$idInscricao'
                    and
                        rni.CAP_ID_CATEGORIA_AVAL in
                        (select CAP_ID_CATEGORIA_AVAL from tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                        where cap.PRC_ID_PROCESSO = '$idProcesso' and EAP_NR_ETAPA_AVAL = '$nrEtapa')";


            if ($stAvaliacao != NULL) {
                $sql .= " and RNI_ST_AVALIACAO = '$stAvaliacao'";
            }

            if ($tpAvaliacao != NULL) {
                $sql .= " and RNI_TP_AVALIACAO = '$tpAvaliacao'";
            }

//            print_r($sql);
//            exit;
            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando consistencia dos dados
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas != 1) {
                //retornando nulo
                return NULL;
            }

            // recuperando dados para gerar retorno
            $dados = ConexaoMysql::getLinha($resp);


            return array('soma' => $dados['soma'], 'somaNorm' => $dados['somaNorm']);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao somar notas da inscrição por etapa.", $e);
        }
    }

    /**
     * Essa funçao retorna apenas a nota do relatorio
     * 
     * @param int $idChamada
     * @param int $idInscricao
     * @param int $idCategoria
     * @param int $idItem - Pode ser nulo
     * @param boolean $idItemIsNull - Quando $idItem eh nulo, informar se eh para recuperar apenas categorias cujo id do item eh nulo
     * @param int $stAvaliacao - Pode ser nulo
     * @param int $tpAvaliacao - Pode ser nulo
     * @return \RelNotasInsc|null - Array com a nota, indexado pelo id do relatorio.
     * Forma: 2 - (IdRelatorio => nota)
     * @throws NegocioException
     */
    public static function buscarNotasPorInscCatItem($idChamada, $idInscricao, $idCategoria, $idItem = NULL, $idItemIsNull = FALSE, $stAvaliacao = NULL, $tpAvaliacao = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        rni.RNI_ID_REL_NOTA,
                        RNI_VL_NOTA_NORMALIZADA
                    from
                        tb_rni_rel_notas_insc rni
                        join
                        tb_ipr_inscricao_processo ipr ON rni.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
                    where
                        rni.IPR_ID_INSCRICAO = '$idInscricao'
                    and PCH_ID_CHAMADA = '$idChamada'
                    and CAP_ID_CATEGORIA_AVAL = '$idCategoria'";

            if ($idItem != NULL) {
                $sql .= " and IAP_ID_ITEM_AVAL = '$idItem'";
            } elseif ($idItemIsNull) {
                $sql .= " and IAP_ID_ITEM_AVAL IS NULL";
            }

            if ($stAvaliacao != NULL) {
                $sql .= " and RNI_ST_AVALIACAO = '$stAvaliacao'";
            }

            if ($tpAvaliacao != NULL) {
                $sql .= " and RNI_TP_AVALIACAO = '$tpAvaliacao'";
            }

            // adicionando order by
            $sql .= " order by rni.RNI_ID_REL_NOTA";

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

                $vetRetorno[$dados['RNI_ID_REL_NOTA']] = $dados['RNI_VL_NOTA_NORMALIZADA'];
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar notas por inscrição-categoria-item.", $e);
        }
    }

    function getHtmlMsgAjusteRel() {
        return "Ajuste Automático para a Categoria";
    }

    static function getHtmlMsgSomaCategoria() {
        return "Nota na Categoria";
    }

    static function getHtmlMsgTituloSomaCategoria() {
        return "Nota total na categoria";
    }

    /* GET FIELDS FROM TABLE */

    function getRNI_ID_REL_NOTA() {
        return $this->RNI_ID_REL_NOTA;
    }

    /* End of get RNI_ID_REL_NOTA */

    function getIPR_ID_INSCRICAO() {
        return $this->IPR_ID_INSCRICAO;
    }

    /* End of get IPR_ID_INSCRICAO */

    function getCAP_ID_CATEGORIA_AVAL() {
        return $this->CAP_ID_CATEGORIA_AVAL;
    }

    /* End of get CAP_ID_CATEGORIA_AVAL */

    function getIAP_ID_ITEM_AVAL() {
        return $this->IAP_ID_ITEM_AVAL;
    }

    /* End of get IAP_ID_ITEM_AVAL */

    function getRNI_ORDEM() {
        return $this->RNI_ORDEM;
    }

    /* End of get RNI_ORDEM */

    function getRNI_TP_AVALIACAO() {
        return $this->RNI_TP_AVALIACAO;
    }

    /* End of get RNI_TP_AVALIACAO */

    function getRNI_DS_OBJ_AVAL() {
        return $this->RNI_DS_OBJ_AVAL;
    }

    /* End of get RNI_DS_OBJ_AVAL */

    function getRNI_VL_NOTA_REAL() {
        return $this->RNI_VL_NOTA_REAL;
    }

    /* End of get RNI_VL_NOTA_REAL */

    function getRNI_VL_NOTA_NORMALIZADA() {
        return $this->RNI_VL_NOTA_NORMALIZADA;
    }

    /* End of get RNI_VL_NOTA_NORMALIZADA */

    function getRNI_ST_AVALIACAO() {
        return $this->RNI_ST_AVALIACAO;
    }

    /* End of get RNI_ST_AVALIACAO */

    function getRNI_ID_USUARIO_RESP() {
        return $this->RNI_ID_USUARIO_RESP;
    }

    /* End of get RNI_ID_USUARIO_RESP */

    function getRNI_DS_JUSTIFICATIVA_AVAL() {
        return $this->RNI_DS_JUSTIFICATIVA_AVAL;
    }

    /* End of get RNI_DS_JUSTIFICATIVA_AVAL */

    function getRNI_LOG_DT_ALTERACAO() {
        return $this->RNI_LOG_DT_ALTERACAO;
    }

    /* End of get RNI_LOG_DT_ALTERACAO */



    /* SET FIELDS FROM TABLE */

    function setRNI_ID_REL_NOTA($value) {
        $this->RNI_ID_REL_NOTA = $value;
    }

    /* End of SET RNI_ID_REL_NOTA */

    function setIPR_ID_INSCRICAO($value) {
        $this->IPR_ID_INSCRICAO = $value;
    }

    /* End of SET IPR_ID_INSCRICAO */

    function setCAP_ID_CATEGORIA_AVAL($value) {
        $this->CAP_ID_CATEGORIA_AVAL = $value;
    }

    /* End of SET CAP_ID_CATEGORIA_AVAL */

    function setIAP_ID_ITEM_AVAL($value) {
        $this->IAP_ID_ITEM_AVAL = $value;
    }

    /* End of SET IAP_ID_ITEM_AVAL */

    function setRNI_ORDEM($value) {
        $this->RNI_ORDEM = $value;
    }

    /* End of SET RNI_ORDEM */

    function setRNI_TP_AVALIACAO($value) {
        $this->RNI_TP_AVALIACAO = $value;
    }

    /* End of SET RNI_TP_AVALIACAO */

    function setRNI_DS_OBJ_AVAL($value) {
        $this->RNI_DS_OBJ_AVAL = $value;
    }

    /* End of SET RNI_DS_OBJ_AVAL */

    function setRNI_VL_NOTA_REAL($value) {
        $this->RNI_VL_NOTA_REAL = $value;
    }

    /* End of SET RNI_VL_NOTA_REAL */

    function setRNI_VL_NOTA_NORMALIZADA($value) {
        $this->RNI_VL_NOTA_NORMALIZADA = $value;
    }

    /* End of SET RNI_VL_NOTA_NORMALIZADA */

    function setRNI_ST_AVALIACAO($value) {
        $this->RNI_ST_AVALIACAO = $value;
    }

    /* End of SET RNI_ST_AVALIACAO */

    function setRNI_ID_USUARIO_RESP($value) {
        $this->RNI_ID_USUARIO_RESP = $value;
    }

    /* End of SET RNI_ID_USUARIO_RESP */

    function setRNI_DS_JUSTIFICATIVA_AVAL($value) {
        $this->RNI_DS_JUSTIFICATIVA_AVAL = $value;
    }

    /* End of SET RNI_DS_JUSTIFICATIVA_AVAL */

    function setRNI_LOG_DT_ALTERACAO($value) {
        $this->RNI_LOG_DT_ALTERACAO = $value;
    }

    /* End of SET RNI_LOG_DT_ALTERACAO */
}

?>
