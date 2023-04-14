<?php

/**
 * tb_ocp_ocupacao class
 * This class manipulates the table Ocupacao
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 17/09/2013
 * */
class Ocupacao {

    private $OCP_ID_OCUPACAO;
    private $OCP_NR_ORDEM_EXIBICAO;
    private $OCP_NM_OCUPACAO;

    /* Construtor padrão da classe */

    public function __construct($OCP_ID_OCUPACAO, $OCP_NR_ORDEM_EXIBICAO, $OCP_NM_OCUPACAO) {
        $this->OCP_ID_OCUPACAO = $OCP_ID_OCUPACAO;
        $this->OCP_NR_ORDEM_EXIBICAO = $OCP_NR_ORDEM_EXIBICAO;
        $this->OCP_NM_OCUPACAO = $OCP_NM_OCUPACAO;
    }

    public static function buscarTodasOcupacoes() {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select 
                        OCP_ID_OCUPACAO, OCP_NM_OCUPACAO
                    from
                        tb_ocp_ocupacao order by OCP_NR_ORDEM_EXIBICAO";

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
                $chave = $dados['OCP_ID_OCUPACAO'];
                $valor = $dados['OCP_NM_OCUPACAO'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar ocupações.", $e);
        }
    }

    public static function buscarOcupacaoPorId($idOcupacao) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select OCP_ID_OCUPACAO, OCP_NR_ORDEM_EXIBICAO, OCP_NM_OCUPACAO from tb_ocp_ocupacao
            where `OCP_ID_OCUPACAO` = '$idOcupacao'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Ocupacao($retorno['OCP_ID_OCUPACAO'], $retorno['OCP_NR_ORDEM_EXIBICAO'], $retorno['OCP_NM_OCUPACAO']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar ocupação.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getOCP_ID_OCUPACAO() {
        return $this->OCP_ID_OCUPACAO;
    }

    /* End of get OCP_ID_OCUPACAO */

    function getOCP_NR_ORDEM_EXIBICAO() {
        return $this->OCP_NR_ORDEM_EXIBICAO;
    }

    /* End of get OCP_NR_ORDEM_EXIBICAO */

    function getOCP_NM_OCUPACAO() {
        return $this->OCP_NM_OCUPACAO;
    }

    /* End of get OCP_NM_OCUPACAO */



    /* SET FIELDS FROM TABLE */

    function setOCP_ID_OCUPACAO($value) {
        $this->OCP_ID_OCUPACAO = $value;
    }

    /* End of SET OCP_ID_OCUPACAO */

    function setOCP_NR_ORDEM_EXIBICAO($value) {
        $this->OCP_NR_ORDEM_EXIBICAO = $value;
    }

    /* End of SET OCP_NR_ORDEM_EXIBICAO */

    function setOCP_NM_OCUPACAO($value) {
        $this->OCP_NM_OCUPACAO = $value;
    }

    /* End of SET OCP_NM_OCUPACAO */
}

?>
