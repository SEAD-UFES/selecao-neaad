<?php

/**
 * tb_tic_tipo_cargo class
 * This class manipulates the table TipoCargo
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 16/10/2013
 * */
class TipoCargo {

    private $TIC_ID_TIPO;
    private $TIC_NM_TIPO;
    private $TIC_URL_BUSCA;
    private $TIC_ST_SITUACAO;
    // Array com Id dos tipos que admitem polo
    private static $ID_ADMITE_POLO = array(1, 2, 6);

    public static function idTipoAdmitePolo($idTipo) {
        return array_search($idTipo, TipoCargo::$ID_ADMITE_POLO) !== false;
    }

    /**
     * Retorna array no formato ['x1', 'x2',...] representando os Id's dos tipos que 
     * admitem polo
     * @return string
     */
    public static function getListaEmJSAdmitePolo() {
        return strArrayJavaScript(TipoCargo::$ID_ADMITE_POLO);
    }

    public static function getListaStrAdmitePolo() {
        return implode(",", TipoCargo::$ID_ADMITE_POLO);
    }

    /* Construtor padrão da classe */

    public function __construct($TIC_ID_TIPO, $TIC_NM_TIPO, $TIC_URL_BUSCA = NULL, $TIC_ST_SITUACAO = NULL) {
        $this->TIC_ID_TIPO = $TIC_ID_TIPO;
        $this->TIC_NM_TIPO = $TIC_NM_TIPO;
        $this->TIC_URL_BUSCA = $TIC_URL_BUSCA;
        $this->TIC_ST_SITUACAO = $TIC_ST_SITUACAO;
    }

    /**
     * 
     * @param boolean $completo Diz se é para retornar os dados na forma completa, ou seja, o objeto TipoCargo.
     * Se for false, então é retornado um vetor na forma id -> nome
     * 
     * @param char $stSituacao Situação dos cargos - Ativo ou Inativo
     * 
     * 
     * @return \TipoCargo Array com todos os tipos de cargo
     * @throws NegocioException
     */
    public static function buscarTodosTiposCargo($completo = TRUE, $stSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select `TIC_ID_TIPO` as idTipo
                , `TIC_NM_TIPO` as nmTipo
                , TIC_URL_BUSCA
                , TIC_ST_SITUACAO
                from tb_tic_tipo_cargo";

            // tem situação?
            if ($stSituacao != NULL) {
                $sql .= " where TIC_ST_SITUACAO = '$stSituacao'";
            }

            // adicionando ordem 
            $sql .= " order by `TIC_NM_TIPO`";

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

                if ($completo) {
                    $tipoTemp = new TipoCargo($dados['idTipo'], $dados['nmTipo'], $dados['TIC_URL_BUSCA'], $dados['TIC_ST_SITUACAO']);
                    $vetRetorno [] = $tipoTemp;
                } else {
                    $vetRetorno[$dados['idTipo']] = $dados['nmTipo'];
                }
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar tipos de atribuição.", $e);
        }
    }

    public static function buscarIdTipoCargoPorUrlBusca($urlBusca) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select `TIC_ID_TIPO` as id
                from tb_tic_tipo_cargo
                where tic_url_busca = '$urlBusca'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                return NULL;
            }

            return ConexaoMysql::getResult("id", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar ID do tipo de atribuição.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getTIC_ID_TIPO() {
        return $this->TIC_ID_TIPO;
    }

    /* End of get TIC_ID_TIPO */

    function getTIC_NM_TIPO() {
        return $this->TIC_NM_TIPO;
    }

    /* End of get TIC_NM_TIPO */

    function getTIC_URL_BUSCA() {
        return $this->TIC_URL_BUSCA;
    }

    /* SET FIELDS FROM TABLE */

    function setTIC_ID_TIPO($value) {
        $this->TIC_ID_TIPO = $value;
    }

    /* End of SET TIC_ID_TIPO */

    function setTIC_NM_TIPO($value) {
        $this->TIC_NM_TIPO = $value;
    }

    /* End of SET TIC_NM_TIPO */

    function setTIC_URL_BUSCA($TIC_URL_BUSCA) {
        $this->TIC_URL_BUSCA = $TIC_URL_BUSCA;
    }

}

?>
