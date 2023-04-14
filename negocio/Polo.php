<?php

/**
 * tb_pol_polo class
 * This class manipulates the table Polo
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 16/10/2013
 * */
class Polo {

    private $POL_ID_POLO;
    private $POL_DS_POLO;

    /* Construtor padrão da classe */

    public function __construct($POL_ID_POLO, $POL_DS_POLO) {
        $this->POL_ID_POLO = $POL_ID_POLO;
        $this->POL_DS_POLO = $POL_DS_POLO;
    }

    public static function buscarTodosPolos() {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select `POL_ID_POLO` as idPolo
                    , `POL_DS_POLO` as dsPolo
                    from `tb_pol_polo` order by `POL_DS_POLO`";

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
                $chave = $dados['idPolo'];
                $valor = $dados['dsPolo'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar polos.", $e);
        }
    }

    public static function buscarPolosPorIds($idPolos) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select `POL_ID_POLO` as idPolo
                    , `POL_DS_POLO` as dsPolo
                    from `tb_pol_polo`
                    where POL_ID_POLO in ($idPolos)
                    order by `POL_DS_POLO`";

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
                $chave = $dados['idPolo'];
                $valor = $dados['dsPolo'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar polos.", $e);
        }
    }

    public static function buscarPoloPorFiltro($idPolo, $dsPolo, $inicioDados, $qtdeDados) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select `POL_ID_POLO` as idPolo
                    , `POL_DS_POLO` as dsPolo
                    from `tb_pol_polo` ";

            //complementando sql de acordo com o filtro
            $where = true;
            $and = false;

            // id do polo
            if ($idPolo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `POL_ID_POLO` = '$idPolo' ";
            }

            // nome do polo
            if ($dsPolo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `POL_DS_POLO` like '%$dsPolo%' ";
            }

            //finalização: caso de ordenação
            $sql .= "  order by `POL_DS_POLO`";

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
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['idPolo'];
                $valor = $dados['dsPolo'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar polos.", $e);
        }
    }

    public static function contarPoloPorFiltro($idPolo, $dsPolo) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from `tb_pol_polo` ";

            //complementando sql de acordo com o filtro
            $where = true;
            $and = false;

            // id do polo
            if ($idPolo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `POL_ID_POLO` = '$idPolo' ";
            }

            // nome do polo
            if ($dsPolo != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `POL_DS_POLO` like '%$dsPolo%' ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar Polos.", $e);
        }
    }

    public static function buscarPoloPorId($idPolo) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select POL_ID_POLO, POL_DS_POLO from tb_pol_polo
            where `POL_ID_POLO` = '$idPolo'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Polo($retorno['POL_ID_POLO'], $retorno['POL_DS_POLO']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar polo por id.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getPOL_ID_POLO() {
        return $this->POL_ID_POLO;
    }

    /* End of get POL_ID_POLO */

    function getPOL_DS_POLO() {
        return $this->POL_DS_POLO;
    }

    /* End of get POL_DS_POLO */



    /* SET FIELDS FROM TABLE */

    function setPOL_ID_POLO($value) {
        $this->POL_ID_POLO = $value;
    }

    /* End of SET POL_ID_POLO */

    function setPOL_DS_POLO($value) {
        $this->POL_DS_POLO = $value;
    }

    /* End of SET POL_DS_POLO */
}

?>
