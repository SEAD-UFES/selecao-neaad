<?php

/**
 * tb_arc_area_conhecimento class
 * This class manipulates the table AreaConhecimento
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 10/10/2013
 * */
class AreaConhecimento {

    private $ARC_ID_AREA_CONH;
    private $ARC_NM_AREA_CONH;
    private $ARC_ID_AREA_PAI_CONH;
    private static $AREA_OUTROS = 9;
    private static $SUBAREA_EAD = 109;

    public static function getAREA_OUTROS() {
        return self::$AREA_OUTROS;
    }

    public static function getSUBAREA_EAD() {
        return self::$SUBAREA_EAD;
    }

    /* Construtor padrão da classe */

    public function __construct($ARC_ID_AREA_CONH, $ARC_NM_AREA_CONH, $ARC_ID_AREA_PAI_CONH) {
        $this->ARC_ID_AREA_CONH = $ARC_ID_AREA_CONH;
        $this->ARC_NM_AREA_CONH = $ARC_NM_AREA_CONH;
        $this->ARC_ID_AREA_PAI_CONH = $ARC_ID_AREA_PAI_CONH;
    }

    public static function buscarTodasAreas() {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        ARC_ID_AREA_CONH, ARC_NM_AREA_CONH
                    from
                        tb_arc_area_conhecimento
                    where
                        ARC_ID_AREA_PAI_CONH is NULL
                    order by ARC_NM_AREA_CONH";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['ARC_ID_AREA_CONH'];
                $valor = $dados['ARC_NM_AREA_CONH'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar áreas do conhecimento.", $e);
        }
    }

    public static function buscarAreasPorIds($idAreas) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        ARC_ID_AREA_CONH, ARC_NM_AREA_CONH
                    from
                        tb_arc_area_conhecimento
                    where
                        ARC_ID_AREA_CONH in ($idAreas)
                    order by ARC_NM_AREA_CONH";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['ARC_ID_AREA_CONH'];
                $valor = $dados['ARC_NM_AREA_CONH'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar áreas do conhecimento.", $e);
        }
    }

    public static function buscarTodasAreasFilhas() {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        ARC_ID_AREA_CONH, ARC_NM_AREA_CONH
                    from
                        tb_arc_area_conhecimento
                    where
                        ARC_ID_AREA_PAI_CONH is NOT NULL
                    order by ARC_NM_AREA_CONH";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['ARC_ID_AREA_CONH'];
                $valor = $dados['ARC_NM_AREA_CONH'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar áreas do conhecimento.", $e);
        }
    }

    public static function buscarSubAreaPorArea($idArea) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        ARC_ID_AREA_CONH, ARC_NM_AREA_CONH
                    from
                        tb_arc_area_conhecimento
                    where
                        ARC_ID_AREA_PAI_CONH = '$idArea'
                    order by ARC_NM_AREA_CONH";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $ch = $dados['ARC_ID_AREA_CONH'];
                $chave = "'$ch'";
                $valor = $dados['ARC_NM_AREA_CONH'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar áreas do conhecimento.", $e);
        }
    }

    public static function buscarAreaConhPorId($idAreaConh) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select ARC_ID_AREA_CONH, ARC_NM_AREA_CONH, ARC_ID_AREA_PAI_CONH from tb_arc_area_conhecimento
            where `ARC_ID_AREA_CONH` = '$idAreaConh'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new AreaConhecimento($retorno['ARC_ID_AREA_CONH'], $retorno['ARC_NM_AREA_CONH'], $retorno['ARC_ID_AREA_PAI_CONH']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar área do conhecimento.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getARC_ID_AREA_CONH() {
        return $this->ARC_ID_AREA_CONH;
    }

    /* End of get ARC_ID_AREA_CONH */

    function getARC_NM_AREA_CONH() {
        return $this->ARC_NM_AREA_CONH;
    }

    /* End of get ARC_NM_AREA_CONH */

    function getARC_ID_AREA_PAI_CONH() {
        return $this->ARC_ID_AREA_PAI_CONH;
    }

    /* End of get ARC_ID_AREA_PAI_CONH */



    /* SET FIELDS FROM TABLE */

    function setARC_ID_AREA_CONH($value) {
        $this->ARC_ID_AREA_CONH = $value;
    }

    /* End of SET ARC_ID_AREA_CONH */

    function setARC_NM_AREA_CONH($value) {
        $this->ARC_NM_AREA_CONH = $value;
    }

    /* End of SET ARC_NM_AREA_CONH */

    function setARC_ID_AREA_PAI_CONH($value) {
        $this->ARC_ID_AREA_PAI_CONH = $value;
    }

    /* End of SET ARC_ID_AREA_PAI_CONH */
}

?>
