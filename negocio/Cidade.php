<?php

/**
 * tb_cid_cidade class
 * This class manipulates the table Cidade
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 17/09/2013
 * */
class Cidade {

    private $CID_ID_CIDADE;
    private $CID_ID_ESTADO;
    private $CID_ID_UF;
    private $CID_NM_CIDADE;

    /* Construtor padrão da classe */

    public function __construct($CID_ID_CIDADE, $CID_ID_ESTADO, $CID_ID_UF, $CID_NM_CIDADE) {
        $this->CID_ID_CIDADE = $CID_ID_CIDADE;
        $this->CID_ID_ESTADO = $CID_ID_ESTADO;
        $this->CID_ID_UF = $CID_ID_UF;
        $this->CID_NM_CIDADE = $CID_NM_CIDADE;
    }

    public static function buscarCidadePorUf($idUf) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select 
                        CID_ID_CIDADE, CID_NM_CIDADE
                    from
                        tb_cid_cidade
                    where
                        CID_ID_UF = '$idUf'
                    order by CID_NM_CIDADE";

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
                $chave = $dados['CID_ID_CIDADE'];
                $valor = $dados['CID_NM_CIDADE'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar países.", $e);
        }
    }

    public static function buscarCidadePorId($idCidade) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select CID_ID_CIDADE, CID_ID_ESTADO, CID_ID_UF, CID_NM_CIDADE from tb_cid_cidade
            where `CID_ID_CIDADE` = '$idCidade'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Cidade($retorno['CID_ID_CIDADE'], $retorno['CID_ID_ESTADO'], $retorno['CID_ID_UF'], $retorno['CID_NM_CIDADE']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar cidade.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getCID_ID_CIDADE() {
        return $this->CID_ID_CIDADE;
    }

    /* End of get CID_ID_CIDADE */

    function getCID_ID_ESTADO() {
        return $this->CID_ID_ESTADO;
    }

    /* End of get CID_ID_ESTADO */

    function getCID_ID_UF() {
        return $this->CID_ID_UF;
    }

    /* End of get CID_ID_UF */

    function getCID_NM_CIDADE() {
        return $this->CID_NM_CIDADE;
    }

    /* End of get CID_NM_CIDADE */



    /* SET FIELDS FROM TABLE */

    function setCID_ID_CIDADE($value) {
        $this->CID_ID_CIDADE = $value;
    }

    /* End of SET CID_ID_CIDADE */

    function setCID_ID_ESTADO($value) {
        $this->CID_ID_ESTADO = $value;
    }

    /* End of SET CID_ID_ESTADO */

    function setCID_ID_UF($value) {
        $this->CID_ID_UF = $value;
    }

    /* End of SET CID_ID_UF */

    function setCID_NM_CIDADE($value) {
        $this->CID_NM_CIDADE = $value;
    }

    /* End of SET CID_NM_CIDADE */
}

?>
