<?php

/**
 * tb_pai_pais class
 * This class manipulates the table Pais
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 17/09/2013
 * */
class Pais {

    private $PAI_ISO;
    private $PAI_ISO3;
    private $PAI_CD_PAIS;
    private $PAI_NM_PAIS;
    public static $PAIS_BRASIL = "BR";

    /* Construtor padrão da classe */

    public function __construct($PAI_ISO, $PAI_ISO3, $PAI_CD_PAIS, $PAI_NM_PAIS) {
        $this->PAI_ISO = $PAI_ISO;
        $this->PAI_ISO3 = $PAI_ISO3;
        $this->PAI_CD_PAIS = $PAI_CD_PAIS;
        $this->PAI_NM_PAIS = $PAI_NM_PAIS;
    }

    public static function buscarTodosPaises() {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select 
                        PAI_ISO, PAI_NM_PAIS
                    from
                        tb_pai_pais
                    order by PAI_NM_PAIS";

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
                $chave = $dados['PAI_ISO'];
                $valor = $dados['PAI_NM_PAIS'];

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

    public static function buscarPaisPorId($paiIso) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select PAI_ISO, PAI_ISO3, PAI_CD_PAIS,PAI_NM_PAIS from tb_pai_pais
            where `PAI_ISO` = '$paiIso'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Pais($retorno['PAI_ISO'], $retorno['PAI_ISO3'], $retorno['PAI_CD_PAIS'], $retorno['PAI_NM_PAIS']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar país.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getPAI_ISO() {
        return $this->PAI_ISO;
    }

    /* End of get PAI_ISO */

    function getPAI_ISO3() {
        return $this->PAI_ISO3;
    }

    /* End of get PAI_ISO3 */

    function getPAI_CD_PAIS() {
        return $this->PAI_CD_PAIS;
    }

    /* End of get PAI_CD_PAIS */

    function getPAI_NM_PAIS() {
        return $this->PAI_NM_PAIS;
    }

    /* End of get PAI_NM_PAIS */



    /* SET FIELDS FROM TABLE */

    function setPAI_ISO($value) {
        $this->PAI_ISO = $value;
    }

    /* End of SET PAI_ISO */

    function setPAI_ISO3($value) {
        $this->PAI_ISO3 = $value;
    }

    /* End of SET PAI_ISO3 */

    function setPAI_CD_PAIS($value) {
        $this->PAI_CD_PAIS = $value;
    }

    /* End of SET PAI_CD_PAIS */

    function setPAI_NM_PAIS($value) {
        $this->PAI_NM_PAIS = $value;
    }

    /* End of SET PAI_NM_PAIS */
}

?>
