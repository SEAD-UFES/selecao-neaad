<?php

/**
 * tb_est_estado class
 * This class manipulates the table Estado
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 17/09/2013
 * */
class Estado {

    private $EST_ID_ESTADO;
    private $EST_ID_UF;
    private $EST_NM_ESTADO;

    /* Construtor padrão da classe */

    public function __construct($EST_ID_ESTADO, $EST_ID_UF, $EST_NM_ESTADO) {
        $this->EST_ID_ESTADO = $EST_ID_ESTADO;
        $this->EST_ID_UF = $EST_ID_UF;
        $this->EST_NM_ESTADO = $EST_NM_ESTADO;
    }

    public static function buscarTodosEstados() {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select 
                        EST_ID_UF, EST_NM_ESTADO
                    from
                        tb_est_estado
                    order by EST_NM_ESTADO";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno;

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['EST_ID_UF'];
                $valor = $dados['EST_NM_ESTADO'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar estados.", $e);
        }
    }

    public static function buscarEstadoPorId($idUf) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select EST_ID_ESTADO, EST_ID_UF, EST_NM_ESTADO from tb_est_estado
            where `EST_ID_UF` = '$idUf'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Estado($retorno['EST_ID_ESTADO'], $retorno['EST_ID_UF'], $retorno['EST_NM_ESTADO']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar estado.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getEST_ID_ESTADO() {
        return $this->EST_ID_ESTADO;
    }

    /* End of get EST_ID_ESTADO */

    function getEST_ID_UF() {
        return $this->EST_ID_UF;
    }

    /* End of get EST_ID_UF */

    function getEST_NM_ESTADO() {
        return $this->EST_NM_ESTADO;
    }

    /* End of get EST_NM_ESTADO */



    /* SET FIELDS FROM TABLE */

    function setEST_ID_ESTADO($value) {
        $this->EST_ID_ESTADO = $value;
    }

    /* End of SET EST_ID_ESTADO */

    function setEST_ID_UF($value) {
        $this->EST_ID_UF = $value;
    }

    /* End of SET EST_ID_UF */

    function setEST_NM_ESTADO($value) {
        $this->EST_NM_ESTADO = $value;
    }

    /* End of SET EST_NM_ESTADO */
}

?>
