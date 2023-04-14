<?php

/**
 * tb_dep_departamento class
 * This class manipulates the table Departamento
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 16/10/2013
 * */
class Departamento {

    private $DEP_ID_DEPARTAMENTO;
    private $DEP_DS_DEPARTAMENTO;
    private $DEP_ST_SITUACAO;

    /* Construtor padrão da classe */

    public function __construct($DEP_ID_DEPARTAMENTO, $DEP_DS_DEPARTAMENTO, $DEP_ST_SITUACAO) {
        $this->DEP_ID_DEPARTAMENTO = $DEP_ID_DEPARTAMENTO;
        $this->DEP_DS_DEPARTAMENTO = $DEP_DS_DEPARTAMENTO;
        $this->DEP_ST_SITUACAO = $DEP_ST_SITUACAO;
    }

    public static function buscarTodosDepartamentos($stSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select DEP_ID_DEPARTAMENTO as idDepartamento
                    , DEP_DS_DEPARTAMENTO as dsDepartamento
                    from tb_dep_departamento";
            if ($stSituacao != NULL) {
                $sql .= " where DEP_ST_SITUACAO = '$stSituacao' ";
            }
            $sql .= " order by DEP_DS_DEPARTAMENTO";

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
                $chave = $dados['idDepartamento'];
                $valor = $dados['dsDepartamento'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar departamentos.", $e);
        }
    }

    public static function buscarDepartamentosPorFiltro($dsNome, $stSituacao, $inicioDados, $qtdeDados) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select DEP_ID_DEPARTAMENTO as idDepartamento
                    , DEP_DS_DEPARTAMENTO as dsDepartamento
                    , DEP_ST_SITUACAO as stSituacao
                    from tb_dep_departamento ";

            //complementando sql de acordo com o filtro
            $where = true;
            $and = false;
            //nome
            if ($dsNome != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `DEP_DS_DEPARTAMENTO` like '%$dsNome%' ";
            }


            //situação 
            if ($stSituacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `DEP_ST_SITUACAO` = '$stSituacao' ";
            }

            //finalização: caso de ordenação
            $sql .= " order by DEP_ST_SITUACAO, DEP_DS_DEPARTAMENTO ";

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
                //retornando nulo
                return NULL;
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);
                $depTemp = new Departamento($dados['idDepartamento'], $dados['dsDepartamento'], $dados['stSituacao']);
                //adicionando no vetor
                $vetRetorno[$i] = $depTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar departamentos.", $e);
        }
    }

    public static function contaDepartamentosPorFiltro($dsNome, $stSituacao) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                from tb_dep_departamento";

            //complementando sql de acordo com o filtro
            $where = true;
            $and = false;
            //nome
            if ($dsNome != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `DEP_DS_DEPARTAMENTO` like '%$dsNome%' ";
            }


            //situação 
            if ($stSituacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `DEP_ST_SITUACAO` = '$stSituacao' ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar departamentos.", $e);
        }
    }

    public static function validaExclusaoDep($idDepartamento) {
        try {
            //recuperando dados para tomada de decisao
            $qtdeCurso = Curso::contaCursoPorDepartamento($idDepartamento);
            return $qtdeCurso == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar execução da operação de exclusão em departamento.", $e);
        }
    }

    public static function validaInativacaoDep($idDepartamento) {
        try {
            //recuperando dados para tomada de decisao
            $qtCurAtivos = Curso::contaCursoPorDepartamento($idDepartamento, NGUtil::getSITUACAO_ATIVO());
            return $qtCurAtivos == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar execução da operação de inativação em departamento.", $e);
        }
    }

    public static function validaNomeDepartamento($dsNome, $idDepartamento = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            if (Util::vazioNulo($dsNome)) {
                return FALSE;
            }

            //caso nome
            $sql = "select count(*) as cont from tb_dep_departamento where `DEP_DS_DEPARTAMENTO` = '$dsNome'";
            if ($idDepartamento != NULL) {
                $sql .= " and DEP_ID_DEPARTAMENTO != '$idDepartamento'";
            }
            $res = $conexao->execSqlComRetorno($sql);

            return $conexao->getResult("cont", $res) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar nome de departamento.", $e);
        }
    }

    public function criaDepartamento() {
        try {

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();
            ;

            //validando nome
            if (!Departamento::validaNomeDepartamento($this->DEP_DS_DEPARTAMENTO)) {
                throw new NegocioException("Departamento '$this->DEP_DS_DEPARTAMENTO' já cadastrado.");
            }

            $sql = "insert into tb_dep_departamento (DEP_DS_DEPARTAMENTO, DEP_ST_SITUACAO)
                    values('$this->DEP_DS_DEPARTAMENTO','$this->DEP_ST_SITUACAO')";

            //executandono banco
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao criar departamento.", $e);
        }
    }

    public static function excluirDepartamento($idDepartamento) {
        try {

            //validando
            if (!Departamento::validaExclusaoDep($idDepartamento)) {
                throw new NegocioException("Exclusão de departamento não permitida.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //sql de exclusão
            $sql = "delete from tb_dep_departamento
                    where DEP_ID_DEPARTAMENTO = '$idDepartamento'";

            //executando no banco
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir departamento.", $e);
        }
    }

    public static function buscarDepartamentoPorId($idDepartamento) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select DEP_ID_DEPARTAMENTO as idDepartamento
                    , DEP_DS_DEPARTAMENTO as dsDepartamento
                    , DEP_ST_SITUACAO as stSituacao
                    from tb_dep_departamento
                    where DEP_ID_DEPARTAMENTO = '$idDepartamento'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                throw new NegocioException("Departamento não encontrado.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $depRet = new Departamento($dados['idDepartamento'], $dados['dsDepartamento'], $dados['stSituacao']);
            return $depRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar departamento.", $e);
        }
    }

    public function atualizarDepartamento() {
        try {

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // verificando validade de inativacao
            if ($this->DEP_ST_SITUACAO == NGUtil::getSITUACAO_INATIVO() && !Departamento::validaInativacaoDep($this->DEP_ID_DEPARTAMENTO)) {
                $this->DEP_ST_SITUACAO = NGUtil::getSITUACAO_ATIVO();
            }

            //sql
            $sql = "update tb_dep_departamento
                    set DEP_DS_DEPARTAMENTO = '$this->DEP_DS_DEPARTAMENTO',
                    DEP_ST_SITUACAO = '$this->DEP_ST_SITUACAO'
                    where DEP_ID_DEPARTAMENTO = '$this->DEP_ID_DEPARTAMENTO'";

            //executando no banco
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar departamento.", $e);
        }
    }

    public function isAtivo() {
        return $this->DEP_ST_SITUACAO == NGUtil::getSITUACAO_ATIVO();
    }

    /* GET FIELDS FROM TABLE */

    function getDEP_ID_DEPARTAMENTO() {
        return $this->DEP_ID_DEPARTAMENTO;
    }

    /* End of get DEP_ID_DEPARTAMENTO */

    function getDEP_DS_DEPARTAMENTO() {
        return $this->DEP_DS_DEPARTAMENTO;
    }

    /* End of get DEP_DS_DEPARTAMENTO */

    function getDEP_ST_SITUACAO() {
        return $this->DEP_ST_SITUACAO;
    }

    /* End of get DEP_ST_SITUACAO */



    /* SET FIELDS FROM TABLE */

    function setDEP_ID_DEPARTAMENTO($value) {
        $this->DEP_ID_DEPARTAMENTO = $value;
    }

    /* End of SET DEP_ID_DEPARTAMENTO */

    function setDEP_DS_DEPARTAMENTO($value) {
        $this->DEP_DS_DEPARTAMENTO = $value;
    }

    /* End of SET DEP_DS_DEPARTAMENTO */

    function setDEP_ST_SITUACAO($value) {
        $this->DEP_ST_SITUACAO = $value;
    }

    /* End of SET DEP_ST_SITUACAO */
}

?>
