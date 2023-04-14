<?php

/**
 * tb_cur_curso class
 * This class manipulates the table Curso
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * Modificado por      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 27/08/2013
 * */
global $CFG;
require_once $CFG->rpasta . "/util/NegocioException.php";
require_once $CFG->rpasta . "/persistencia/ConexaoMysql.php";
require_once $CFG->rpasta . "/negocio/Processo.php";

class Curso {

    private $CUR_ID_CURSO;
    private $TPC_ID_TIPO_CURSO;
    private $DEP_ID_DEPARTAMENTO;
    private $CUR_ID_COORDENADOR;
    private $CUR_NM_CURSO;
    private $CUR_ID_AREA_CONH;
    private $CUR_ID_SUBAREA_CONH;
    private $CUR_DS_CURSO;
    private $CUR_ST_SITUACAO;
    private $CUR_URL_BUSCA;
    // campos herdados
    public $DEP_DS_DEPARTAMENTO;
    public $TPC_NM_TIPO_CURSO;
    public $CUR_NM_COORDENADOR;
    public $CUR_EMAIL_COORDENADOR;
    // campos sob demanda
    private $NM_AREA_CONH;
    private $NM_SUBAREA_CONH;

    /* Construtor padrão da classe */

    public function __construct($CUR_ID_CURSO, $TPC_ID_TIPO_CURSO, $DEP_ID_DEPARTAMENTO, $CUR_ID_COORDENADOR, $CUR_NM_CURSO, $CUR_ID_AREA_CONH, $CUR_ID_SUBAREA_CONH, $CUR_DS_CURSO, $CUR_ST_SITUACAO, $CUR_URL_BUSCA = NULL) {
        $this->CUR_ID_CURSO = $CUR_ID_CURSO;
        $this->TPC_ID_TIPO_CURSO = $TPC_ID_TIPO_CURSO;
        $this->DEP_ID_DEPARTAMENTO = $DEP_ID_DEPARTAMENTO;
        $this->CUR_ID_COORDENADOR = $CUR_ID_COORDENADOR;
        $this->CUR_NM_CURSO = $CUR_NM_CURSO;
        $this->CUR_ID_AREA_CONH = $CUR_ID_AREA_CONH;
        $this->CUR_ID_SUBAREA_CONH = $CUR_ID_SUBAREA_CONH;
        $this->CUR_DS_CURSO = $CUR_DS_CURSO;
        $this->CUR_ST_SITUACAO = $CUR_ST_SITUACAO;
        $this->CUR_URL_BUSCA = $CUR_URL_BUSCA;
    }

    public function getDsAreaSubarea() {
        // caso de nao ter subarea
        if (Util::vazioNulo($this->CUR_ID_AREA_CONH)) {
            return "";
        }
        // verificando se os campos ja foram carregados
        if (Util::vazioNulo($this->NM_AREA_CONH)) {
            // carregando campos
            $this->carregaNmAreaSubarea();
        }
        return "{$this->NM_AREA_CONH} - {$this->NM_SUBAREA_CONH}";
    }

    private function carregaNmAreaSubarea() {
        try {

            $area = AreaConhecimento::buscarAreaConhPorId($this->CUR_ID_AREA_CONH);
            $this->NM_AREA_CONH = $area->getARC_NM_AREA_CONH();

            $subArea = AreaConhecimento::buscarAreaConhPorId($this->CUR_ID_SUBAREA_CONH);
            $this->NM_SUBAREA_CONH = $subArea->getARC_NM_AREA_CONH();
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar nomes de área / subarea.", $e);
        }
    }

    public static function getStringAtualizacaoCoordNull($idUsuario) {
        $ret = "update tb_cur_curso
                set CUR_ID_COORDENADOR = NULL
                where CUR_ID_COORDENADOR = '$idUsuario'";
        return $ret;
    }

    public static function getStringAtualizacaoCoord($idCurso, $idUsuario = NULL) {
        $dep = $idUsuario == NULL ? ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE : $idUsuario;
        $ret = "update tb_cur_curso
                set CUR_ID_COORDENADOR = '$dep'
                where CUR_ID_CURSO = '$idCurso'";
        return $ret;
    }

    public static function validaExclusaoCurso($idCurso) {
        try {
            //recuperando array com processos abertos
            $qtdeProc = Processo::contaProcessoPorCurso($idCurso);

            return $qtdeProc == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar execução de operação de exclusão em curso.", $e);
        }
    }

    public static function validaInativacaoCurso($idCurso) {
        try {
            //recuperando array com processos abertos
            $qtdeProc = Processo::contaProcessoPorCurso($idCurso);

            return $qtdeProc == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar execução de operação de inativação em curso.", $e);
        }
    }

    public static function excluirCurso($idCurso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //validando
            if (!Curso::validaExclusaoCurso($idCurso)) {
                throw new NegocioException("Operação de exclusão de curso não permitida");
            }

            //sql
            $sql = "delete from tb_cur_curso
                    where CUR_ID_CURSO = '$idCurso'";

            //executando
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir curso.", $e);
        }
    }

    private static function getSqlInicial() {
        return "select 
                    CUR_ID_CURSO,
                    cur.TPC_ID_TIPO_CURSO,
                    cur.DEP_ID_DEPARTAMENTO,
                    CUR_ID_COORDENADOR,
                    CUR_NM_CURSO,
                    CUR_ID_AREA_CONH,
                    CUR_ID_SUBAREA_CONH,
                    CUR_DS_CURSO,
                    CUR_ST_SITUACAO,
                    CUR_URL_BUSCA,
                    DEP_DS_DEPARTAMENTO,
                    USR_DS_NOME as CUR_NM_COORDENADOR,
                    TPC_NM_TIPO_CURSO,
                    USR_DS_EMAIL as CUR_EMAIL_COORDENADOR";
    }

    public static function buscarCursoPorId($idCurso) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = Curso::getSqlInicial() . "
                from
                    tb_cur_curso cur
                        join
                    tb_dep_departamento dep ON cur.DEP_ID_DEPARTAMENTO = dep.DEP_ID_DEPARTAMENTO
                        left join
                    tb_usr_usuario usr ON cur.CUR_ID_COORDENADOR = usr.USR_ID_USUARIO
                        join
                    tb_tpc_tipo_curso tpc ON tpc.TPC_ID_TIPO_CURSO = cur.TPC_ID_TIPO_CURSO
                    where CUR_ID_CURSO = '$idCurso'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                throw new NegocioException("Curso não encontrado.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);

            $cursoRet = new Curso($dados['CUR_ID_CURSO'], $dados['TPC_ID_TIPO_CURSO'], $dados['DEP_ID_DEPARTAMENTO'], $dados['CUR_ID_COORDENADOR'], $dados['CUR_NM_CURSO'], $dados['CUR_ID_AREA_CONH'], $dados['CUR_ID_SUBAREA_CONH'], $dados['CUR_DS_CURSO'], $dados['CUR_ST_SITUACAO'], $dados['CUR_URL_BUSCA']);

            // setando campos herdados
            $cursoRet->TPC_NM_TIPO_CURSO = $dados['TPC_NM_TIPO_CURSO'];
            $cursoRet->CUR_NM_COORDENADOR = $dados['CUR_NM_COORDENADOR'];
            $cursoRet->DEP_DS_DEPARTAMENTO = $dados['DEP_DS_DEPARTAMENTO'];
            $cursoRet->CUR_EMAIL_COORDENADOR = $dados['CUR_EMAIL_COORDENADOR'];

            return $cursoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar curso.", $e);
        }
    }

    public static function buscarIdCursoPorUrlBusca($urlBusca) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select cur_id_curso as id
                    from tb_cur_curso
                    where CUR_URL_BUSCA = '$urlBusca'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return NULL;
            }

            return ConexaoMysql::getResult("id", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar ID do curso.", $e);
        }
    }

    public static function buscaCursoPorCoordenador($idCoordenador) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = Curso::getSqlInicial() . "
                from
                    tb_cur_curso cur
                        join
                    tb_dep_departamento dep ON cur.DEP_ID_DEPARTAMENTO = dep.DEP_ID_DEPARTAMENTO
                        left join
                    tb_usr_usuario usr ON cur.CUR_ID_COORDENADOR = usr.USR_ID_USUARIO
                        join
                    tb_tpc_tipo_curso tpc ON tpc.TPC_ID_TIPO_CURSO = cur.TPC_ID_TIPO_CURSO
                    where CUR_ID_COORDENADOR = '$idCoordenador'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return NULL;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);

            $cursoRet = new Curso($dados['CUR_ID_CURSO'], $dados['TPC_ID_TIPO_CURSO'], $dados['DEP_ID_DEPARTAMENTO'], $dados['CUR_ID_COORDENADOR'], $dados['CUR_NM_CURSO'], $dados['CUR_ID_AREA_CONH'], $dados['CUR_ID_SUBAREA_CONH'], $dados['CUR_DS_CURSO'], $dados['CUR_ST_SITUACAO'], $dados['CUR_URL_BUSCA']);

            // setando campos herdados
            $cursoRet->TPC_NM_TIPO_CURSO = $dados['TPC_NM_TIPO_CURSO'];
            $cursoRet->CUR_NM_COORDENADOR = $dados['CUR_NM_COORDENADOR'];
            $cursoRet->DEP_DS_DEPARTAMENTO = $dados['DEP_DS_DEPARTAMENTO'];
            $cursoRet->CUR_EMAIL_COORDENADOR = $dados['CUR_EMAIL_COORDENADOR'];

            return $cursoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar curso por coordenador.", $e);
        }
    }

    public static function buscaTodosCursos($stSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();
            ;

            $sql = "select CUR_ID_CURSO as idCurso
                    , CUR_NM_CURSO as dsCurso
                    from tb_cur_curso cur";
            if ($stSituacao != NULL) {
                $sql .= " where CUR_ST_SITUACAO = '$stSituacao' ";
            }
            $sql .= " order by cur.CUR_NM_CURSO";

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
                $chave = $dados['idCurso'];
                $valor = $dados['dsCurso'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar cursos.", $e);
        }
    }

    /**
     * 
     * @param array $listaAval - Lista de avaliadores do curso
     * @throws NegocioException
     */
    public function atualizarCurso($listaAval = NULL) {
        try {

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //validando nome
            if (!Curso::validaNomeCurso($this->CUR_NM_CURSO, $this->CUR_ID_CURSO)) {
                throw new NegocioException("Curso '$this->CUR_NM_CURSO' já cadastrado.");
            }

            // acertando campos opcionais
            $this->CUR_DS_CURSO = NGUtil::trataCampoStrParaBD($this->CUR_DS_CURSO);

            //idCoordenador
            if ($this->CUR_ID_COORDENADOR == "") {
                $idCoord = "NULL";
            } else {
                $idCoord = "'$this->CUR_ID_COORDENADOR'";
                // evitando que um usuario coordene mais de um curso
                $sqlLimpaCoord = Curso::getStringAtualizacaoCoordNull($this->CUR_ID_COORDENADOR);
            }

            // verificando validade de inativacao
            if ($this->CUR_ST_SITUACAO == NGUtil::getSITUACAO_INATIVO() && !Curso::validaInativacaoCurso($this->CUR_ST_SITUACAO)) {
                $this->CUR_ST_SITUACAO = NGUtil::getSITUACAO_ATIVO();
            }

            $urlBusca = NGUtil::trataCampoStrParaBD($this->criaUrlBusca());


            $sql = "update tb_cur_curso
                    set TPC_ID_TIPO_CURSO = '$this->TPC_ID_TIPO_CURSO'
                    , DEP_ID_DEPARTAMENTO = '$this->DEP_ID_DEPARTAMENTO'
                    , CUR_ID_COORDENADOR = $idCoord
                    , CUR_NM_CURSO = '$this->CUR_NM_CURSO'
                    , CUR_ID_AREA_CONH = '$this->CUR_ID_AREA_CONH'
                    , CUR_ID_SUBAREA_CONH = $this->CUR_ID_SUBAREA_CONH
                    , CUR_DS_CURSO = $this->CUR_DS_CURSO
                    , CUR_ST_SITUACAO = '$this->CUR_ST_SITUACAO'
                    , CUR_URL_BUSCA = $urlBusca
                    where CUR_ID_CURSO = '$this->CUR_ID_CURSO'";

            // tratando caso do avaliador
            $sqlRemocao = Usuario::getSqlRemocaoAvaliador($this->CUR_ID_CURSO);
            $arrayIns = Usuario::getSqlAlocacaoAvaliador($this->CUR_ID_CURSO, $listaAval);

            //executando no banco
            if (isset($sqlLimpaCoord)) {
                $conexao->execTransacaoArray(array_merge(array($sqlLimpaCoord, $sql, $sqlRemocao), $arrayIns));
            } else {
                $conexao->execTransacaoArray(array_merge(array($sql, $sqlRemocao), $arrayIns));
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao editar curso.", $e);
        }
    }

    public function criaCurso() {
        try {

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //validando nome
            if (!Curso::validaNomeCurso($this->CUR_NM_CURSO)) {
                throw new NegocioException("Curso '$this->CUR_NM_CURSO' já cadastrado.");
            }

            // acertando campos opcionais
            $this->CUR_DS_CURSO = NGUtil::trataCampoStrParaBD($this->CUR_DS_CURSO);

            //idCoordenador
            if ($this->CUR_ID_COORDENADOR == "") {
                $idCoord = "NULL";
            } else {
                $idCoord = "'$this->CUR_ID_COORDENADOR'";
                // evitando que um usuario coordene mais de um curso
                $sqlLimpaCoord = Curso::getStringAtualizacaoCoordNull($this->CUR_ID_COORDENADOR);
            }

            $urlBusca = NGUtil::trataCampoStrParaBD($this->criaUrlBusca());

            $sql = "insert INTO tb_cur_curso
                    (TPC_ID_TIPO_CURSO, DEP_ID_DEPARTAMENTO, CUR_ID_COORDENADOR, CUR_NM_CURSO, CUR_ID_AREA_CONH, CUR_ID_SUBAREA_CONH, CUR_DS_CURSO,
                     CUR_ST_SITUACAO, CUR_URL_BUSCA)
                     VALUES('$this->TPC_ID_TIPO_CURSO', '$this->DEP_ID_DEPARTAMENTO', $idCoord, '$this->CUR_NM_CURSO',
                    '$this->CUR_ID_AREA_CONH', $this->CUR_ID_SUBAREA_CONH, $this->CUR_DS_CURSO, '$this->CUR_ST_SITUACAO', $urlBusca)";

            //executando no banco
            if (isset($sqlLimpaCoord)) {
                $conexao->execTransacaoArray(array($sqlLimpaCoord, $sql));
            } else {
                $conexao->execSqlSemRetorno($sql);
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao criar curso.", $e);
        }
    }

    private function criaUrlBusca() {
        $aux = str_replace(" ", "-", strtolower(removerAcentos($this->CUR_NM_CURSO)));
        $aux = str_replace(".", "", $aux);
        return $aux;
    }

    public static function contaCursoPorDepartamento($idDepartamento, $stSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                from tb_cur_curso
                where DEP_ID_DEPARTAMENTO = '$idDepartamento'";

            if ($stSituacao != NULL) {
                $sql .= " and CUR_ST_SITUACAO = '$stSituacao'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar curso por departamento.", $e);
        }
    }

    public static function buscarCursosPorFiltro($dsNome, $idDepartamento, $tpCurso, $stSituacao, $inicioDados, $qtdeDados) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    CUR_ID_CURSO,
                    cur.TPC_ID_TIPO_CURSO,
                    cur.DEP_ID_DEPARTAMENTO,
                    CUR_ID_COORDENADOR,
                    CUR_NM_CURSO,
                    CUR_ID_AREA_CONH,
                    CUR_ID_SUBAREA_CONH,
                    CUR_DS_CURSO,
                    CUR_ST_SITUACAO,
                    CUR_URL_BUSCA,
                    DEP_DS_DEPARTAMENTO,
                    USR_DS_NOME as CUR_NM_COORDENADOR,
                    TPC_NM_TIPO_CURSO
                from
                    tb_cur_curso cur join tb_dep_departamento dep
                    on cur.DEP_ID_DEPARTAMENTO = dep.DEP_ID_DEPARTAMENTO
                    left join tb_usr_usuario usr on cur.CUR_ID_COORDENADOR = usr.USR_ID_USUARIO
                    join tb_tpc_tipo_curso tpc on tpc.TPC_ID_TIPO_CURSO = cur.TPC_ID_TIPO_CURSO";

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
                $sql .= " `CUR_NM_CURSO` like '%$dsNome%' ";
            }

            //idDepartamento
            if ($idDepartamento != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " cur.DEP_ID_DEPARTAMENTO = '$idDepartamento' ";
            }

            //tpCurso
            if ($tpCurso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " cur.`TPC_ID_TIPO_CURSO` = '$tpCurso' ";
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
                $sql .= " `CUR_ST_SITUACAO` = '$stSituacao' ";
            }

            //finalização: caso de ordenação
            $sql .= " order by CUR_ST_SITUACAO, CUR_NM_CURSO ";

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
                $curTemp = new Curso($dados['CUR_ID_CURSO'], $dados['TPC_ID_TIPO_CURSO'], $dados['DEP_ID_DEPARTAMENTO'], $dados['CUR_ID_COORDENADOR'], $dados['CUR_NM_CURSO'], $dados['CUR_ID_AREA_CONH'], $dados['CUR_ID_SUBAREA_CONH'], $dados['CUR_DS_CURSO'], $dados['CUR_ST_SITUACAO'], $dados['CUR_URL_BUSCA']);

                // setando campos herdados
                $curTemp->TPC_NM_TIPO_CURSO = $dados['TPC_NM_TIPO_CURSO'];
                $curTemp->CUR_NM_COORDENADOR = $dados['CUR_NM_COORDENADOR'];
                $curTemp->DEP_DS_DEPARTAMENTO = $dados['DEP_DS_DEPARTAMENTO'];
                $curTemp->CUR_EMAIL_COORDENADOR = $dados['CUR_EMAIL_COORDENADOR'];

                //adicionando no vetor
                $vetRetorno[$i] = $curTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar cursos.", $e);
        }
    }

    public static function contaCursosPorFiltro($dsNome, $idDepartamento, $tpCurso, $stSituacao) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                from
                    tb_cur_curso cur join tb_dep_departamento dep
                    on cur.DEP_ID_DEPARTAMENTO = dep.DEP_ID_DEPARTAMENTO
                    left join tb_usr_usuario usr on cur.CUR_ID_COORDENADOR = usr.USR_ID_USUARIO
                    join tb_tpc_tipo_curso tpc on tpc.TPC_ID_TIPO_CURSO = cur.TPC_ID_TIPO_CURSO";


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
                $sql .= " `CUR_NM_CURSO` like '%$dsNome%' ";
            }

            //idDepartamento
            if ($idDepartamento != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " cur.DEP_ID_DEPARTAMENTO = '$idDepartamento' ";
            }

            //tpCurso
            if ($tpCurso != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " cur.`TPC_ID_TIPO_CURSO` = '$tpCurso' ";
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
                $sql .= " `CUR_ST_SITUACAO` = '$stSituacao' ";
            }


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar cursos.", $e);
        }
    }

    public static function validaNomeCurso($nmCurso, $idCurso = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //caso nome
            $sql = "select count(*) as cont from tb_cur_curso where `CUR_NM_CURSO` = '$nmCurso'";
            if ($idCurso != NULL) {
                $sql .= " and CUR_ID_CURSO != '$idCurso'";
            }
            $res = $conexao->execSqlComRetorno($sql);
            return $conexao->getResult("cont", $res) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar nome do curso.", $e);
        }
    }

    public function temCoordenador() {
        return !Util::vazioNulo($this->CUR_ID_COORDENADOR);
    }

    public function isAtivo() {
        return $this->CUR_ST_SITUACAO == NGUtil::getSITUACAO_ATIVO();
    }

    /* GET FIELDS FROM TABLE */

    function getCUR_ID_CURSO() {
        return $this->CUR_ID_CURSO;
    }

    /* End of get CUR_ID_CURSO */

    function getTPC_ID_TIPO_CURSO() {
        return $this->TPC_ID_TIPO_CURSO;
    }

    /* End of get TPC_ID_TIPO_CURSO */

    function getDEP_ID_DEPARTAMENTO() {
        return $this->DEP_ID_DEPARTAMENTO;
    }

    /* End of get DEP_ID_DEPARTAMENTO */

    function getCUR_ID_COORDENADOR() {
        return $this->CUR_ID_COORDENADOR;
    }

    /* End of get CUR_ID_COORDENADOR */

    function getCUR_NM_CURSO() {
        return $this->CUR_NM_CURSO;
    }

    function getCUR_ID_AREA_CONH() {
        return $this->CUR_ID_AREA_CONH;
    }

    /* End of get CUR_ID_AREA_CONH */

    function getCUR_ID_SUBAREA_CONH() {
        return $this->CUR_ID_SUBAREA_CONH;
    }

    /* End of get CUR_ID_SUBAREA_CONH */


    /* End of get CUR_NM_CURSO */

    function getCUR_DS_CURSO() {
        return $this->CUR_DS_CURSO;
    }

    /* End of get CUR_DS_CURSO */

    function getCUR_ST_SITUACAO() {
        return $this->CUR_ST_SITUACAO;
    }

    /* End of get CUR_ST_SITUACAO */

    function getCUR_URL_BUSCA() {
        return $this->CUR_URL_BUSCA;
    }

    /* SET FIELDS FROM TABLE */

    function setCUR_ID_CURSO($value) {
        $this->CUR_ID_CURSO = $value;
    }

    /* End of SET CUR_ID_CURSO */

    function setTPC_ID_TIPO_CURSO($value) {
        $this->TPC_ID_TIPO_CURSO = $value;
    }

    /* End of SET TPC_ID_TIPO_CURSO */

    function setDEP_ID_DEPARTAMENTO($value) {
        $this->DEP_ID_DEPARTAMENTO = $value;
    }

    /* End of SET DEP_ID_DEPARTAMENTO */

    function setCUR_ID_COORDENADOR($value) {
        $this->CUR_ID_COORDENADOR = $value;
    }

    /* End of SET CUR_ID_COORDENADOR */

    function setCUR_NM_CURSO($value) {
        $this->CUR_NM_CURSO = $value;
    }

    function setCUR_ID_AREA_CONH($value) {
        $this->CUR_ID_AREA_CONH = $value;
    }

    /* End of SET CUR_ID_AREA_CONH */

    function setCUR_ID_SUBAREA_CONH($value) {
        $this->CUR_ID_SUBAREA_CONH = $value;
    }

    /* End of SET CUR_ID_SUBAREA_CONH */

    /* End of SET CUR_NM_CURSO */

    function setCUR_DS_CURSO($value) {
        $this->CUR_DS_CURSO = $value;
    }

    /* End of SET CUR_DS_CURSO */

    function setCUR_ST_SITUACAO($value) {
        $this->CUR_ST_SITUACAO = $value;
    }

    /* End of SET CUR_ST_SITUACAO */

    function setCUR_URL_BUSCA($CUR_URL_BUSCA) {
        $this->CUR_URL_BUSCA = $CUR_URL_BUSCA;
    }

}

?>
