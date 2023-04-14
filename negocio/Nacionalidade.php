<?php

/**
 * tb_nac_nacionalidade class
 * This class manipulates the table Nacionalidade
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * Modificado por      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 27/08/2013
 * */
class Nacionalidade {

    private $NAC_ID_NACIONALIDADE;
    private $NAC_NM_NACIONALIDADE;

    /* Construtor padrão da classe */

    public function __construct($NAC_ID_NACIONALIDADE, $NAC_NM_NACIONALIDADE) {
        $this->NAC_ID_NACIONALIDADE = $NAC_ID_NACIONALIDADE;
        $this->NAC_NM_NACIONALIDADE = $NAC_NM_NACIONALIDADE;
    }

    public static function buscarTodasNacionalidades() {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    NAC_ID_NACIONALIDADE, NAC_NM_NACIONALIDADE
                    from
                        tb_nac_nacionalidade
                    order by NAC_NM_NACIONALIDADE";

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
                $chave = $dados['NAC_ID_NACIONALIDADE'];
                $valor = $dados['NAC_NM_NACIONALIDADE'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar nacionalidades.", $e);
        }
    }

    public static function buscarNacionalidadePorId($idNacionalidade) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select NAC_ID_NACIONALIDADE, NAC_NM_NACIONALIDADE from tb_nac_nacionalidade
            where `NAC_ID_NACIONALIDADE` = '$idNacionalidade'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Nacionalidade($retorno['NAC_ID_NACIONALIDADE'], $retorno['NAC_NM_NACIONALIDADE']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar nacionalidade.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getNAC_ID_NACIONALIDADE() {
        return $this->NAC_ID_NACIONALIDADE;
    }

    /* End of get NAC_ID_NACIONALIDADE */

    function getNAC_NM_NACIONALIDADE() {
        return $this->NAC_NM_NACIONALIDADE;
    }

    /* End of get NAC_NM_NACIONALIDADE */



    /* SET FIELDS FROM TABLE */

    function setNAC_ID_NACIONALIDADE($value) {
        $this->NAC_ID_NACIONALIDADE = $value;
    }

    /* End of SET NAC_ID_NACIONALIDADE */

    function setNAC_NM_NACIONALIDADE($value) {
        $this->NAC_NM_NACIONALIDADE = $value;
    }

    /* End of SET NAC_NM_NACIONALIDADE */
}

?>
