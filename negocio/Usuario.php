<?php

/**
 * tb_usr_usuario class
 * This class manipulates the table Usuario
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 27/08/2013
 * */
global $CFG;
require_once $CFG->rpasta . "/persistencia/ConexaoMysql.php";
require_once $CFG->rpasta . "/util/NegocioException.php";
require_once $CFG->rpasta . "/negocio/NGUtil.php";
require_once $CFG->rpasta . "/negocio/ConfiguracaoUsuario.php";
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/util/Util.php";
require_once $CFG->rpasta . "/negocio/Curso.php";
require_once $CFG->rpasta . "/negocio/Candidato.php";
require_once $CFG->rpasta . "/negocio/InscricaoProcesso.php";
require_once $CFG->rpasta . "/negocio/RecursoResulProc.php";
require_once $CFG->rpasta . "/negocio/LDAP.php";

class Usuario {

    private $USR_ID_USUARIO;
    private $USR_TP_USUARIO;
    private $USR_DS_LOGIN;
    private $USR_DS_EMAIL;
    private $USR_DS_SENHA;
    private $USR_DS_NOME;
    private $USR_TP_VINCULO_UFES;
    private $USR_ST_SITUACAO;
    private $USR_DT_SOLIC_TROCA_SENHA;
    private $USR_DS_URL_TROCA_SENHA;
    private $USR_LOG_DT_CRIACAO;
    private $USR_LOG_DT_ULT_LOGIN;
    private $USR_TROCAR_SENHA;
    private $USR_ID_CUR_AVALIADOR;
    private $USR_HASH_ALTERACAO_EXT;
    private $USR_EMAIL_VALIDADO;
    private $conversaoLU; // informa se houve conversão para login único
// campos herdados
    private $IDC_NR_CPF;
    private $IDC_NASC_DATA;
    private $CTC_EMAIL_CONTATO;
    public static $USUARIO_COORDENADOR = 'O';
    public static $USUARIO_ADMINISTRADOR = 'A';
    public static $USUARIO_CANDIDATO = 'C';
    public static $USUARIO_AVALIADOR = 'V';

    /**
     * Tipos de Vínculo com a UFES, de acordo com a documentação do LDAP
     * Ver também em LDAP: pegar_tipo_vinculo_ldap
     * 1. Docente;
     * 2. Técnico Administrativo;
     * 3. Instrutor; 
     * 4. Aluno de Graduação; 
     * 5. Aluno de Pós-Graduação;
     * 6. Aluno de Intercâmbio;
     * 7. Aluno Mobilidade;
     * 8. Visitante;
     * 9. Terceirizado;
     * 10. Residente de Medicina; - 
     * 11. Aluno de Graduação Especial; - 3
     * 12. Professor Colaborador/Visitante de Pós-Graduação; - 
     * 13. Secretário da Pós-Graduação; -  
     * 15. Residente Multi-Funcional; - 
     * 16. Tutor EAD; - 
     * 17. Aluno de Graduação EAD; - 3
     */
    public static $VINCULO_DOCENTE = 1;
    public static $VINCULO_TEC_ADMINISTRATIVO = 2;
    public static $VINCULO_INSTRUTOR = 3;
    public static $VINCULO_ESTUDANTE = 4; // engloga: 4, 5, 6, 7, 11, 17
    public static $VINCULO_VISITANTE = 8;
    public static $VINCULO_TERCEIRIZADO = 9;
    public static $VINCULO_RESIDENTE_MED = 10;
    public static $VINCULO_PROF_COLABORADOR_VIS = 12;
    public static $VINCULO_SEC_POS_GRAD = 13;
    public static $VINCULO_RESIDENTE_MULTI = 15;
    public static $VINCULO_TUTOR_EAD = 15;
    public static $VINCULO_NENHUM = 0;
    //
    //
    public static $MAX_DIA_LINK_VALIDO = 3;
    //
    // Tipos de contato
    public static $CONTATO_DUVIDA_SISTEMA = "D";
    public static $CONTATO_INFORMACAO = "I";
    public static $CONTATO_SUGESTAO = "S";

    public static function getDsContato($tpContato) {
        if ($tpContato == Usuario::$CONTATO_INFORMACAO) {
            return "Informação";
        }
        if ($tpContato == Usuario::$CONTATO_DUVIDA_SISTEMA) {
            return "Problemas com o sistema";
        }
        if ($tpContato == Usuario::$CONTATO_SUGESTAO) {
            return "Sugestão";
        }
    }

    public static function getDsTipo($tipo) {
        if ($tipo == Usuario::$USUARIO_CANDIDATO) {
            return "Candidato";
        }
        if ($tipo == Usuario::$USUARIO_COORDENADOR) {
            return "Coordenador";
        }
        if ($tipo == Usuario::$USUARIO_ADMINISTRADOR) {
            return "Administrador";
        }
        if ($tipo == Usuario::$USUARIO_AVALIADOR) {
            return "Avaliador";
        }
        return null;
    }

    public static function getDsVinculoUFES($tipo, $dsNenhum = NULL) {
        if ($tipo == Usuario::$VINCULO_ESTUDANTE) {
            return "Estudante";
        }
        if ($tipo == Usuario::$VINCULO_INSTRUTOR) {
            return "Instrutor";
        }
        if ($tipo == Usuario::$VINCULO_VISITANTE) {
            return "Visitante";
        }
        if ($tipo == Usuario::$VINCULO_TERCEIRIZADO) {
            return "Terceirizado";
        }
        if ($tipo == Usuario::$VINCULO_RESIDENTE_MED) {
            return "Residente de Medicina";
        }
        if ($tipo == Usuario::$VINCULO_PROF_COLABORADOR_VIS) {
            return "Prof. Colaborador / Visitante";
        }
        if ($tipo == Usuario::$VINCULO_NENHUM) {
            return Util::vazioNulo($dsNenhum) ? "Nenhum" : $dsNenhum;
        }
        if ($tipo == Usuario::$VINCULO_DOCENTE) {
            return "Docente";
        }
        if ($tipo == Usuario::$VINCULO_TEC_ADMINISTRATIVO) {
            return "Técnico Administrativo";
        }
        if ($tipo == Usuario::$VINCULO_SEC_POS_GRAD) {
            return "Secretário da Pós-Graduação";
        }
        if ($tipo == Usuario::$VINCULO_RESIDENTE_MULTI) {
            return "Residente Multi-Funcional";
        }
        if ($tipo == Usuario::$VINCULO_TUTOR_EAD) {
            return "Tutor EAD";
        }
        return null;
    }

    public static function getListaTipoDsTipo() {
        $ret = array(
            Usuario::$USUARIO_ADMINISTRADOR => Usuario::getDsTipo(Usuario::$USUARIO_ADMINISTRADOR),
            Usuario::$USUARIO_AVALIADOR => Usuario::getDsTipo(Usuario::$USUARIO_AVALIADOR),
            Usuario::$USUARIO_CANDIDATO => Usuario::getDsTipo(Usuario::$USUARIO_CANDIDATO),
            Usuario::$USUARIO_COORDENADOR => Usuario::getDsTipo(Usuario::$USUARIO_COORDENADOR)
        );
        return $ret;
    }

    public static function getListaContatoDsContato() {
        $ret = array(
            Usuario::$CONTATO_INFORMACAO => Usuario::getDsContato(Usuario::$CONTATO_INFORMACAO),
            Usuario::$CONTATO_DUVIDA_SISTEMA => Usuario::getDsContato(Usuario::$CONTATO_DUVIDA_SISTEMA),
            Usuario::$CONTATO_SUGESTAO => Usuario::getDsContato(Usuario::$CONTATO_SUGESTAO)
        );
        return $ret;
    }

    public function isComunidadeExterna() {
        return $this->USR_TP_VINCULO_UFES == Usuario::$VINCULO_NENHUM;
    }

    public function getDsTipoAcesso() {
        if ($this->isComunidadeExterna()) {
            return "Comunidade Externa";
        } else {
            return "Login único UFES";
        }
    }

    /* Construtor padrão da classe */

    public function __construct($USR_ID_USUARIO, $USR_TP_USUARIO, $USR_DS_LOGIN, $USR_DS_EMAIL, $USR_DS_SENHA, $USR_DS_NOME, $USR_TP_VINCULO_UFES, $USR_ST_SITUACAO = NULL, $USR_DT_SOLIC_TROCA_SENHA = NULL, $USR_DS_URL_TROCA_SENHA = NULL, $USR_LOG_DT_CRIACAO = NULL, $USR_LOG_DT_ULT_LOGIN = NULL, $USR_TROCAR_SENHA = NULL, $USR_ID_CUR_AVALIADOR = NULL, $USR_HASH_ALTERACAO_EXT = NULL, $USR_EMAIL_VALIDADO = NULL) {
        $this->USR_ID_USUARIO = $USR_ID_USUARIO;
        $this->USR_TP_USUARIO = $USR_TP_USUARIO;
        $this->USR_DS_LOGIN = $USR_DS_LOGIN;
        $this->USR_DS_EMAIL = $USR_DS_EMAIL;
        $this->USR_DS_SENHA = $USR_DS_SENHA;
        $this->USR_DS_NOME = $USR_DS_NOME;
        $this->USR_TP_VINCULO_UFES = $USR_TP_VINCULO_UFES;
        $this->USR_ST_SITUACAO = $USR_ST_SITUACAO;
        $this->USR_DT_SOLIC_TROCA_SENHA = $USR_DT_SOLIC_TROCA_SENHA;
        $this->USR_DS_URL_TROCA_SENHA = $USR_DS_URL_TROCA_SENHA;
        $this->USR_LOG_DT_CRIACAO = $USR_LOG_DT_CRIACAO;
        $this->USR_LOG_DT_ULT_LOGIN = $USR_LOG_DT_ULT_LOGIN;
        $this->USR_TROCAR_SENHA = $USR_TROCAR_SENHA;
        $this->USR_ID_CUR_AVALIADOR = $USR_ID_CUR_AVALIADOR;
        $this->USR_HASH_ALTERACAO_EXT = $USR_HASH_ALTERACAO_EXT;
        $this->USR_EMAIL_VALIDADO = $USR_EMAIL_VALIDADO;
        $this->conversaoLU = FALSE;
    }

    public function editarUsuario($idCurso, $nrCpf, $dtNascimento, $dsEmailAlternativo, $desvincular) {
        try {

            //validando caso email
            if (!Util::vazioNulo($this->USR_DS_EMAIL) && !Usuario::validarCadastroEmail($this->USR_DS_EMAIL, $this->USR_ID_USUARIO)) {
                throw new NegocioException("Email já cadastrado.");
            }

            //busca usuario do BD
            $usuarioBD = Usuario::buscarUsuarioPorId($this->USR_ID_USUARIO);

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // flag atualizar nome
            $atualizarNome = $this->USR_ID_USUARIO == getIdUsuarioLogado() && $usuarioBD->USR_DS_NOME != $this->USR_DS_NOME;

            // forçar capitalize no nome
            $this->USR_DS_NOME = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($this->USR_DS_NOME));

            //montando sql inicial de atualizacao
            $sql = "update tb_usr_usuario
                    set `USR_DS_NOME` = $this->USR_DS_NOME
                    , USR_ST_SITUACAO = '$this->USR_ST_SITUACAO'";


            // verificando se pode editar email
            if ($usuarioBD->USR_TP_VINCULO_UFES == Usuario::$VINCULO_NENHUM || $desvincular) {
                $sql .= ", USR_DS_EMAIL = '$this->USR_DS_EMAIL'";
            }

            // usuario avaliador
            if ($usuarioBD->USR_TP_USUARIO == Usuario::$USUARIO_AVALIADOR) {
                $this->USR_ID_CUR_AVALIADOR = Util::vazioNulo($this->USR_ID_CUR_AVALIADOR) ? "NULL" : NGUtil::trataCampoStrParaBD($this->USR_ID_CUR_AVALIADOR);
                // append no sql
                $sql.= ", USR_ID_CUR_AVALIADOR = $this->USR_ID_CUR_AVALIADOR";
            }


            // tratando caso de desvincular
            if ($usuarioBD->USR_TP_VINCULO_UFES == Usuario::$VINCULO_NENHUM || $desvincular) {
                // definindo novo login
                $this->USR_DS_LOGIN = $this->USR_DS_EMAIL;
                $this->USR_TP_VINCULO_UFES = Usuario::$VINCULO_NENHUM;

                // realizando append no sql
                $sql .= ", `USR_DS_LOGIN` = '$this->USR_DS_LOGIN'
                        , `USR_TP_VINCULO_UFES` = '$this->USR_TP_VINCULO_UFES'";
            } else {
                $this->USR_DS_LOGIN = $usuarioBD->USR_DS_LOGIN;
            }


            $sql .= " where `USR_ID_USUARIO` = '$this->USR_ID_USUARIO'";


            //verificando tipos para tratar casos separados
            if ($usuarioBD->USR_TP_USUARIO == Usuario::$USUARIO_ADMINISTRADOR || $usuarioBD->USR_TP_USUARIO == Usuario::$USUARIO_AVALIADOR) {
                //atualizando no banco normalmente
                $conexao->execSqlSemRetorno($sql);
            } elseif ($usuarioBD->USR_TP_USUARIO == Usuario::$USUARIO_COORDENADOR) {
                //executando comandos de atualizaçao de coordenador
                $cmdGeral = Curso::getStringAtualizacaoCoordNull($this->USR_ID_USUARIO);
                $cmdEsp = $idCurso != "" ? Curso::getStringAtualizacaoCoord($idCurso, $this->USR_ID_USUARIO) : "";
                if ($cmdEsp == "") {
                    $conexao->execTransacao($sql, array($cmdGeral));
                } else {
                    $conexao->execTransacao($sql, array($cmdGeral, $cmdEsp));
                }
            } elseif ($usuarioBD->USR_TP_USUARIO == Usuario::$USUARIO_CANDIDATO) {
                // usuario candidato: CPF e data de nascimento
                $sqlCpfDtNasc = IdentificacaoCandidato::getStringAtualizacaoCpfDtNasc($nrCpf, $dtNascimento, $this->USR_ID_USUARIO);

                // usuario candidato: Email alternativo
                $sqlEmailAlt = ContatoCandidato::getStringAtualizacaoEmailAlt($dsEmailAlternativo, $this->USR_ID_USUARIO);

                // persistindo no BD
                $conexao->execTransacao($sql, array($sqlCpfDtNasc, $sqlEmailAlt));
            }

            // atualizar nome na sessao
            if ($atualizarNome) {
                // buscando usuário atualizado
                $usuAtualizado = self::buscarUsuarioPorId($usuarioBD->USR_ID_USUARIO);
                atualizarNomeSessao($usuAtualizado->USR_DS_NOME, $usuAtualizado->USR_HASH_ALTERACAO_EXT);
            }

            // retornando se e para sair ou nao
            return $this->USR_ID_USUARIO == getIdUsuarioLogado() && ($this->USR_DS_LOGIN != $usuarioBD->USR_DS_LOGIN || $this->USR_ST_SITUACAO != $usuarioBD->USR_ST_SITUACAO);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar usuário.", $e);
        }
    }

    public static function contaUsuariosPorFiltro($dsNome, $dsEmail, $tpUsuario, $nrcpf, $stSituacao) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select
                    count(*) as cont
                    from
                    tb_usr_usuario usr
                    left join
                    tb_cdt_candidato cdt ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                    left join
                    tb_idc_identificacao_candidato idc ON idc.IDC_ID_IDENTIFICACAO_CDT = cdt.IDC_ID_IDENTIFICACAO_CDT
                    left join
                    tb_ctc_contato_candidato ctc ON ctc.CTC_ID_CONTATO_CDT = cdt.CTC_ID_CONTATO_CDT ";

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
                $sql .= " `USR_DS_NOME` like '%$dsNome%' ";
            }

            //email
            if ($dsEmail != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " (`USR_DS_EMAIL` like '%$dsEmail%' or CTC_EMAIL_CONTATO like '%$dsEmail%') ";
            }

            //tp usuário 
            if ($tpUsuario != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `USR_TP_USUARIO` = '$tpUsuario' ";
            }

            //nrcpf
            if ($nrcpf != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " IDC_NR_CPF = '$nrcpf' ";
            }

            //situação usuário
            if ($stSituacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `USR_ST_SITUACAO` = '$stSituacao' ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar usuários.", $e);
        }
    }

    public static function validarHashSessao($idUsuario, $hashAtual) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select
                    count(*) as cont
                    from
                    tb_usr_usuario usr
                    where USR_HASH_ALTERACAO_EXT = '$hashAtual' and USR_ID_USUARIO = '$idUsuario'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp) == 1;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar sessão do usuário.", $e);
        }
    }

    public static function buscarUsuariosPorFiltro($dsNome, $dsEmail, $tpUsuario, $nrcpf, $stSituacao, $inicioDados, $qtdeDados) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = Usuario::getSqlInicialBusca("usr.") .
                    ", IDC_NR_CPF from
                    tb_usr_usuario usr
                    left join
                    tb_cdt_candidato cdt ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                    left join
                    tb_idc_identificacao_candidato idc ON idc.IDC_ID_IDENTIFICACAO_CDT = cdt.IDC_ID_IDENTIFICACAO_CDT
                    left join
                    tb_ctc_contato_candidato ctc ON ctc.CTC_ID_CONTATO_CDT = cdt.CTC_ID_CONTATO_CDT ";

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
                $sql .= " `USR_DS_NOME` like '%$dsNome%' ";
            }

            //email
            if ($dsEmail != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " (`USR_DS_EMAIL` like '%$dsEmail%' or CTC_EMAIL_CONTATO like '%$dsEmail%') ";
            }

            //tp usuário 
            if ($tpUsuario != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `USR_TP_USUARIO` = '$tpUsuario' ";
            }

            //nrcpf
            if ($nrcpf != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " IDC_NR_CPF = '$nrcpf' ";
            }

            //situação usuário
            if ($stSituacao != NULL) {
                if ($where) {
                    $sql .= " where ";
                    $where = false;
                    $and = true;
                } else if ($and) {
                    $sql .= " and ";
                }
                $sql .= " `USR_ST_SITUACAO` = '$stSituacao' ";
            }

            //finalização: caso de ordenação
            $sql .= " order by USR_ST_SITUACAO, USR_DS_NOME, USR_TP_USUARIO ";

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
                $retorno = ConexaoMysql::getLinha($resp);
                $usuarioTemp = new Usuario($retorno['idUsuario'], $retorno['tpUsuario'], $retorno['dsLogin'], $retorno['dsEmail'], $retorno['dsSenha'], $retorno['dsNome'], $retorno['tpVinculoUfes'], $retorno['stSituacao'], $retorno['dtSolicTrocaSenha'], $retorno['dsUrlTrocaSenha'], $retorno['dtCriacao'], $retorno['dtUltLogin'], $retorno['USR_TROCAR_SENHA'], $retorno['USR_ID_CUR_AVALIADOR'], $retorno['USR_HASH_ALTERACAO_EXT'], $retorno['USR_EMAIL_VALIDADO']);

                // preenchendo campos herdados
                $usuarioTemp->IDC_NR_CPF = $retorno['IDC_NR_CPF'];

                //adicionando no vetor
                $vetRetorno[$i] = $usuarioTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar usuários.", $e);
        }
    }

    public static function buscarTodosUsuarios($stSituacao = NULL, $tpUsuario = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select USR_DS_NOME as dsNome
                    , USR_ID_USUARIO as idUsuario
                    from tb_usr_usuario";

            $comp = " where ";
            if ($stSituacao != NULL) {
                $sql .= " $comp USR_ST_SITUACAO = '$stSituacao' ";
                $comp = " and ";
            }
            if ($tpUsuario != NULL) {
                $sql .= " $comp USR_TP_USUARIO = '$tpUsuario' ";
            }
            $sql .= " order by USR_DS_NOME";

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
                $chave = $dados['idUsuario'];
                $valor = $dados['dsNome'] . ' (Código ' . $dados['idUsuario'] . ')';

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar usuários.", $e);
        }
    }

    public static function getSqlAtualizacaoNome($idUsuario, $novoNome) {
        $novoNome = NGUtil::trataCampoStrParaBD($novoNome);
        $ret = "update tb_usr_usuario
                set USR_DS_NOME = $novoNome
                where USR_ID_USUARIO = '$idUsuario'";
        return $ret;
    }

    /**
     * 
     * @param int $idCurso
     * @return string
     */
    public static function getSqlRemocaoAvaliador($idCurso) {
        $ret = "update tb_usr_usuario
                set USR_ID_CUR_AVALIADOR = NULL
                where USR_ID_CUR_AVALIADOR = '$idCurso'";
        return $ret;
    }

    /**
     * 
     * @param int $idCurso
     * @param array $listaAval
     * @return array - Array de comandos sql para atualizaçao de avaliadores
     */
    public static function getSqlAlocacaoAvaliador($idCurso, $listaAval) {
        $ret = array();

        if (!Util::vazioNulo($listaAval)) {
            foreach ($listaAval as $aval) {
                $ret [] = "update tb_usr_usuario
                set USR_ID_CUR_AVALIADOR = '$idCurso'
                where USR_ID_USUARIO = '$aval'";
            }
        }

        return $ret;
    }

    /**
     * 
     * @global stdClass $CFG
     * @param string $dsEmail
     * @param string $dtNascimento
     * @param string $nrCPF
     * @param boolean $emailForcado Força a exibição de mensagem quando ocorre algum erro ao enviar email para o candidato. Padrão: TRUE
     * @return string
     * @throws NegocioException
     */
    public static function gerarRecuperacaoSenha($dsEmail, $dtNascimento, $nrCPF, $emailForcado = TRUE) {
        global $CFG;
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $stPermitida = NGUtil::getSITUACAO_ATIVO();
            $dtNascimento = dt_dataStrParaMysql($dtNascimento);

            //montando sql de busca do usuário
            $sql = "select
                    usr.`USR_ID_USUARIO` as idUsuario
                    from
                    tb_usr_usuario usr
                    left join
                    tb_cdt_candidato cdt ON usr.USR_ID_USUARIO = cdt.USR_ID_USUARIO
                    left join
                    tb_idc_identificacao_candidato idc ON cdt.IDC_ID_IDENTIFICACAO_CDT = idc.IDC_ID_IDENTIFICACAO_CDT
                    where
                    USR_DS_EMAIL = '$dsEmail'
                    and USR_ST_SITUACAO = '$stPermitida'";

            // incluindo data de nascimento e cpf
            if (!Util::vazioNulo($nrCPF)) {
                $sql.= " and idc.IDC_NASC_DATA = $dtNascimento
                        and idc.IDC_NR_CPF = '$nrCPF'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                new Mensagem('Desculpe.<br/>Não foi possível encontrar um usuário com os dados informados.', Mensagem::$MENSAGEM_ERRO, NULL, "errUsu", "$CFG->rwww/recuperar-senha");
                return;
            }

            //extraindo id do usuário
            $idUsuario = ConexaoMysql::getResult("idUsuario", $resp);

            //recuperando usuário
            $usuario = Usuario::buscarUsuarioPorId($idUsuario);

            // verificando caso de candidato sem dados importantes
            if ($usuario->USR_TP_USUARIO == Usuario::$USUARIO_CANDIDATO && Util::vazioNulo($nrCPF)) {
                new Mensagem('Desculpe.<br/>Não foi possível encontrar um usuário com os dados informados.', Mensagem::$MENSAGEM_ERRO, NULL, "errUsu", "$CFG->rwww/recuperar-senha");
                return;
            }

            // verificando vinculo com a UFES
            if (!$usuario->isComunidadeExterna()) {
                new Mensagem("Desculpe.<br/>Seu login está associado à UFES. Para recuperar a sua senha <a target = '_blank' href = 'https://senha.ufes.br/'>clique aqui</a>.", Mensagem::$MENSAGEM_ERRO, NULL, "errUfes", "$CFG->rwww/recuperar-senha");
                return;
            }

            //montando url
            $url = "$CFG->rwww/inserir-nova-senha?";

            //gerando parametros
            $parametros = "";
            //idUsuario
            $parametros .= "id=" . md5($usuario->USR_ID_USUARIO);
            $parametros .= "&";

            //senha: hash do hash da senha do usuário
            $parametros .= "ch=" . md5($usuario->USR_DS_SENHA);
            $parametros .= "&";

            //recuperando data atual para inclusao na url
            $dataHora = dt_getDataEmStr("d/m/Y H:i:s");
            $parametros .= "dt=" . md5($dataHora);

            //incluindo id do usuário
            $parametros .= "&idUsuario=" . $idUsuario;

            //concatenando url
            $url .= $parametros;

            $dataSql = dt_dataHoraStrParaMysql($dataHora);

            //atualizando usuário com dados de validação
            $sql = "update tb_usr_usuario
                    set `USR_DS_URL_TROCA_SENHA` = '$parametros'
                    , `USR_DT_SOLIC_TROCA_SENHA` = $dataSql
                    where `USR_ID_USUARIO` = '$usuario->USR_ID_USUARIO'";

            //executando sql
            $resp = $conexao->execSqlSemRetorno($sql);


            //montando mensagem com instruções de recuperação de senha
            $assunto = "Solicitação de nova senha";

            $mensagem = "Olá, {$usuario->USR_DS_NOME}.<br/><br/>";
            $mensagem .= "Você solicitou em <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>$dataHora</b></span> a recuperação de sua senha para acesso ao <span style='color: " . Util::$COR_DESTAQUE_EMAIL_HEX . ";'><b>Sistema de Seleção EAD - UFES</b></span>.";
            $mensagem .= "<br/><br/>Para prosseguir com a recuperação e trocar sua senha, por favor, acesse o endereço abaixo:";
            $mensagem .= "<br/>$url";
            $mensagem .= "<br/><br/><i>* Caso não tenha sido você que solicitou a recuperação de senha, favor desconsiderar esta mensagem.</i>";

            $destinatario = $usuario->USR_DS_EMAIL;

            if (!enviaEmail($destinatario, $assunto, $mensagem)) {
                // Mostra mensagem se o email é forcado
                if ($emailForcado) {
                    new Mensagem($mensagem, Mensagem::$MENSAGEM_INFORMACAO);
                    return;
                }
            }

            $msgRetorno = "Processo de recuperação de senha iniciado com sucesso.<br/>Em breve você receberá uma mensagem em seu email <b>({$usuario->USR_DS_EMAIL})</b> com instruções para recuperar sua senha.";
            return $msgRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao gerar recuperação de senha do usuário.", $e);
        }
    }

    public static function validarAlterarSenha($id, $ch, $dt, $idUsuario, $dsEmail = NULL) {
        global $CFG;
        try {
            //recuperando usuário
            $usuario = Usuario::buscarUsuarioPorId($idUsuario);
            if ($usuario == NULL) {
                throw new NegocioException("Desculpe.<br/>Link de recuperação de senha inválido.");
            }


            //validando email
            if ($dsEmail != NULL && $dsEmail != $usuario->USR_DS_EMAIL) {
                throw new NegocioException("Email de usuário inválido.");
            }

            //usuário não ativo
            if ($usuario->USR_ST_SITUACAO != NGUtil::getSITUACAO_ATIVO()) {
                throw new NegocioException("Desculpe.<br/>Link de recuperação de senha inválido.");
            }

            $param = "id=" . $id . "&ch=" . $ch . "&dt=" . $dt . "&idUsuario=" . $idUsuario;

            //verificando se o usuário solicitou recuperação de senha
            if ($usuario->USR_DS_URL_TROCA_SENHA == NULL || $usuario->USR_DS_URL_TROCA_SENHA != $param || $usuario->USR_DT_SOLIC_TROCA_SENHA == NULL) {
                throw new NegocioException("Desculpe.<br/>Link de recuperação de senha inválido.");
            }


            //verificando id
            $idVal = md5($usuario->USR_ID_USUARIO) == $id;

            //verificando chave
            $chVal = md5($usuario->USR_DS_SENHA) == $ch;

            //verificando data
            $dtVal = md5($usuario->USR_DT_SOLIC_TROCA_SENHA) == $dt;

            //verificando consistência
            if (!$idVal || !$chVal || !$dtVal) {
                throw new NegocioException("Desculpe.<br/>Link de recuperação de senha inconsistente.");
            }

            //verificando expiração do link
            $dataAtual = dt_getTimestampDtBR(dt_getDataEmStr("d/m/Y"));
            $maxDtValida = dt_getTimestampDtBR(dt_somarData($usuario->USR_DT_SOLIC_TROCA_SENHA, Usuario::$MAX_DIA_LINK_VALIDO));
            if ($dataAtual > $maxDtValida) {
                $msg = "Seu link de recuperação de senha expirou.<br/>";
                $msg .= "Por favor, <a href = '$CFG->rwww/recuperar-senha'>Solicite</a> outro link e tente novamente.";
                throw new NegocioException($msg);
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar validade do link de recuperação de senha.", $e);
        }
    }

    public static function alterarSenha($idUsuario, $dsSenhaAtual, $dsNovaSenha) {
        try {
            global $CFG;

            // buscando usuario
            $usu = buscarUsuarioPorIdCT($idUsuario);
            if ($usu == NULL) {
                throw new NegocioException("Usuário inválido.");
            }

            // apenas comunidade externa
            if (!$usu->isComunidadeExterna()) {
                throw new NegocioException("Processo de alteração de senha não permitido para o seu tipo de usuário.");
            }

            // verificando senha atual
            if (md5($dsSenhaAtual) != $usu->USR_DS_SENHA) {
                $compForcado = isTrocarSenhaUsuarioLogado() ? "?f=true" : "";
                new Mensagem("Senha atual incorreta.", Mensagem::$MENSAGEM_ERRO, NULL, "errSenhaAtual", "$CFG->rwww/visao/usuario/alterarSenha.php$compForcado");
                return NULL;
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //senha em md5
            $senhaBanco = md5($dsNovaSenha);

            $flagNao = FLAG_BD_NAO;

            //preparando sql de troca de senha
            $sql = "update tb_usr_usuario
                    set `USR_DS_SENHA` = '$senhaBanco',
                     USR_TROCAR_SENHA = '$flagNao'
                    where `USR_ID_USUARIO` = '$idUsuario'";

            //executando sql
            $conexao->execSqlSemRetorno($sql);

            return TRUE; // senha alterada com sucesso
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao alterar senha.", $e);
        }
    }

    public static function alterarLogin($idUsuario, $dsLogin, $dsSenhaAtual) {
        try {
            global $CFG;

            // buscando usuario
            $usu = buscarUsuarioPorIdCT($idUsuario);

            if ($usu == NULL) {
                throw new NegocioException("Usuário inválido.");
            }

            // apenas comunidade externa
            if (!$usu->isComunidadeExterna()) {
                throw new NegocioException("Processo de alteração de login não permitido para o seu tipo de usuário.");
            }

            // verificando senha atual
            if (md5($dsSenhaAtual) != $usu->USR_DS_SENHA) {
                new Mensagem("Senha atual incorreta.", Mensagem::$MENSAGEM_ERRO, NULL, "errSenhaAtual", "$CFG->rwww/visao/usuario/alterarDadosAcesso.php?e=true");
                return NULL;
            }

            // validando login
            if (!Usuario::validarCadastroEmail($dsLogin, $idUsuario)) {
                throw new NegocioException("Login já cadastrado.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //preparando sql de troca de login
            $sql = "update tb_usr_usuario
                    set `USR_DS_LOGIN` = '$dsLogin',
                    `USR_DS_EMAIL` = USR_DS_LOGIN
                    where `USR_ID_USUARIO` = '$idUsuario'";

            //executando sql
            $conexao->execSqlSemRetorno($sql);

            return TRUE; // login alterado com sucesso
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao alterar login.", $e);
        }
    }

    public static function trocarSenhaRecuperada($id, $ch, $dt, $idUsuario, $dsEmail, $dsSenha) {
        try {

            //validando troca
            Usuario::validarAlterarSenha($id, $ch, $dt, $idUsuario, $dsEmail);

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //senha em md5
            $senhaBanco = md5($dsSenha);

            //preparando sql de troca de senha
            $sql = "update tb_usr_usuario
                    set `USR_DS_URL_TROCA_SENHA` = NULL
                    , `USR_DT_SOLIC_TROCA_SENHA` = NULL
                    , `USR_DS_SENHA` = '$senhaBanco'
                    where `USR_ID_USUARIO` = '$idUsuario'";

            //executando sql
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao trocar senha recuperada.", $e);
        }
    }

    /**
     * Essa funçao realiza a operaçao de migraçao de um usuario LDAP para o acesso local.
     * Em resumo, realiza as seguintes operaçoes:
     * 1 - Altera o vinculo do usuario para NENHUM
     * 2 - Substitui o login do usuario pelo seu endereco de email alternativo
     * 3 - Altera o Email do usuário pelo email alternativo
     * 
     * @param string $emailAlternativo Email alternativo do candidato.
     * @return boolean Informa se foi transformado com sucesso ou não.
     * @throws NegocioException
     */
    private function transformarLoginLdapEmLocal($emailAlternativo) {
        try {
            // verificando se pode ser realizado a conversão
            if (!self::validarCadastroEmail($emailAlternativo, $this->USR_ID_USUARIO)) {
                return FALSE;
            }

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $vin = Usuario::$VINCULO_NENHUM;

            //montando sql
            $sql = "update tb_usr_usuario
                    set
                    USR_TP_VINCULO_UFES = '$vin'
                    , USR_DS_EMAIL = '$emailAlternativo'    
                    , USR_DS_LOGIN = USR_DS_EMAIL
                    where
                    USR_ID_USUARIO = '$this->USR_ID_USUARIO'";

            $conexao->execSqlSemRetorno($sql);

            return TRUE;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar transformar usuário LDAP em local.", $e);
        }
    }

    /**
     * Essa funçao realiza a operaçao de migraçao de um usuario local para o acesso via LDAP.
     * Em resumo, realiza as seguintes operaçoes:
     * 1 - Altera o vinculo do usuario para o vínculo especificado no objeto LU
     * 2 - Substitui o login do usuario pelo seu login único
     * 3 - Altera o endereço de email do usuário (por uma questão de integridade)
     * 4 - Coloca o email atual do usuário como email alternativo
     * 5 - Sinaliza no objeto atual a conversão de login
     * @param Usuario $objUsuLoginUnico Objeto criado com os dados de login único
     * @return boolean Informa se foi transformado com sucesso ou não.
     * @throws NegocioException
     */
    private function transformarLoginLocalEmLdap($objUsuLoginUnico) {
        try {
            // verificando se pode ser realizado a conversão
            if (!self::validarCadastroLogin($objUsuLoginUnico->USR_DS_LOGIN)) {
                return FALSE;
            }

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $vin = $objUsuLoginUnico->USR_TP_VINCULO_UFES;

            //montando sql
            $sql = "update tb_usr_usuario
                    set
                    USR_TP_VINCULO_UFES = '$vin'
                    , USR_DS_EMAIL = ' $objUsuLoginUnico->USR_DS_EMAIL'    
                    , USR_DS_LOGIN = '$objUsuLoginUnico->USR_DS_LOGIN'
                    where
                    USR_ID_USUARIO = '$this->USR_ID_USUARIO'";


            $conexao->execTransacao($sql, array(ContatoCandidato::getStringAtualizacaoEmailAlt($this->USR_DS_EMAIL, $this->USR_ID_USUARIO)));

            return TRUE;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar transformar usuário local em LDAP.", $e);
        }
    }

    /**
     * Verifica se o login em questão é o login para comunidade externa
     * 
     * @param string $login Login do candidato
     * @return boolean True se for um login externo, False caso contrário.
     */
    private static function isLoginExterno($login) {
        return strpos($login, "@") !== FALSE;
    }

    /**
     * Esta função valida se o login e senha é válido ou não
     * 
     * Retornos possíveis:
     *  Usuario - Objeto usuário, caso o login esteja válido
     *  NULL - Caso o login seja inválido
     *  FALSE - Caso o login seja válido mas exista alguma restrição de login (como usuário bloqueado) 
     * 
     * @param string $login
     * @param string $senha
     * @return Usuario|NULL|FALSE
     * @throws NegocioException
     */
    public static function validarLogin($login, $senha) {
        try {
            // Caso de login externo
            if (self::isLoginExterno($login)) {

                //criando objeto de conexão
                $conexao = NGUtil::getConexao();
                $stPermitida = NGUtil::getSITUACAO_ATIVO();

                //convertendo senha e tratando login
                $senhacript = md5($senha);
                $login = NGUtil::trataCampoStrParaBD($login);

                //montando sql
                $sql = Usuario::getSqlInicialBusca() . "
                    from tb_usr_usuario
                    where `USR_DS_LOGIN` = $login
                    and `USR_DS_SENHA` = '$senhacript'";

                $ret = $conexao->execSqlComRetorno($sql);

                //verificando linhas de retorno
                if (ConexaoMysql::getNumLinhas($ret) != 0) {
                    $dados = ConexaoMysql::getLinha($ret);

                    $idUsu = $dados['idUsuario'];
                    $situacao = $dados['stSituacao'];

                    // verificando se está bloqueado
                    if ($situacao !== $stPermitida) {
                        return FALSE;
                    }

                    //setando data do último login e invalidando possível troca de senha
                    $sql = "update tb_usr_usuario
                        set `USR_LOG_DT_ULT_LOGIN` = now()
                        , `USR_DS_URL_TROCA_SENHA` = NULL
                        , `USR_DT_SOLIC_TROCA_SENHA` = NULL
                        where `USR_ID_USUARIO` = '$idUsu'";

                    $conexao->execSqlSemRetorno($sql);

                    $objUsu = new Usuario($dados['idUsuario'], $dados['tpUsuario'], $dados['dsLogin'], $dados['dsEmail'], $dados['dsSenha'], $dados['dsNome'], $dados['tpVinculoUfes'], $dados['stSituacao'], $dados['dtSolicTrocaSenha'], $dados['dsUrlTrocaSenha'], $dados['dtCriacao'], $dados['dtUltLogin'], $dados['USR_TROCAR_SENHA'], $dados['USR_ID_CUR_AVALIADOR'], $dados['USR_HASH_ALTERACAO_EXT'], $dados['USR_EMAIL_VALIDADO']);
                    return $objUsu;
                } else {
                    // Usuário não existe no sistema
                    return NULL;
                }
            }

            // LOGIN ÚNICO: Tentando validar no ldap
            $val_ldap = ldap_validar_login($login, $senha);

            // tratando casos de validação Ldap
            // caso de sucesso
            if ($val_ldap[0] === TRUE) {
                return $val_ldap[1];
            } elseif ($val_ldap[0] === FALSE) {
                return FALSE; // Candidato bloqueado no sistema
            } elseif ($val_ldap[0] === NULL) {
                return NULL; // Candidato inexistente ou senha incorreta
            } else {
                // disparando exceção com erros LDAP
                throw new NegocioException(LDAPUfes::getMsgErroLdap($val_ldap[0], isset($val_ldap[1]) ? $val_ldap[1] : NULL));
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar validar login.", $e);
        }
    }

    /**
     * 
     * @param Usuario $objUsu
     * @param Endereco $objEnd
     * @param IdentificacaoCandidato $objIdentCand
     * @param ContatoCandidato $objContatoCand
     * @return Usuario|NULL
     * @throws NegocioException
     */
    public static function sincronizaSisComLdap($objUsu, $objEnd, $objIdentCand, $objContatoCand) {
        try {

            //verificando se o usuário não possui vínculo
            if ($objUsu->USR_TP_VINCULO_UFES == Usuario::$VINCULO_NENHUM) {
                // situação equivalente ao candidato "bloqueado": tratando caso
                self::tratarLoginLDAPBloqueado($objUsu->USR_DS_LOGIN, $objUsu->USR_DS_EMAIL, $objIdentCand->getIDC_NR_CPF(), "Seu usuário não tem permissão para acessar este sistema");
            }

            //verifica se o usuário já foi criado
            $usuario = Usuario::buscarUsuarioPorLogin($objUsu->USR_DS_LOGIN);

            if ($usuario != NULL) {

                // verificando se o usuário está inativo
                if (!$usuario->isAtivo()) {

                    // retornando erro de usuário inativo
                    return array(FALSE);
                }

                //setando data do último login
                $sql = "update tb_usr_usuario
                        set `USR_LOG_DT_ULT_LOGIN` = now()
                        where `USR_ID_USUARIO` = '$usuario->USR_ID_USUARIO'";

                $conexao = NGUtil::getConexao();

                $conexao->execSqlSemRetorno($sql);

                // retorna usuário já cadastrado no sistema
                return array(TRUE, $usuario);
            }

            // verificando se existe login adicional (Mesmo CPF)
            $usuCpf = self::buscarUsuarioPorCPF($objIdentCand->getIDC_NR_CPF());
            $usuAdd = !Util::vazioNulo($usuCpf) ? $usuCpf : NULL;

            // validando se o email já está sendo utilizado por outra pessoa
            if (!self::validarCadastroEmail($objUsu->USR_DS_EMAIL, $usuAdd != NULL ? $usuAdd->USR_ID_USUARIO : NULL)) {
                // informar problema ao usuário
                new Mensagem("Desculpe. Seu endereço de email está sendo usado por outra pessoa.<br/>Por favor, entre em contato com o suporte para verificar sua situação.", Mensagem :: $MENSAGEM_ERRO);
                return;
            }

            // não tem login adicional? 
            if (Util::vazioNulo($usuAdd)) {
                // cadastrando usuario
                $objUsu->criarUsuario($objEnd, $objIdentCand, $objContatoCand, NULL);

                return array(TRUE, Usuario::buscarUsuarioPorLogin($objUsu->USR_DS_LOGIN));
            } else {
                // convertendo login adicional para login único
                $converteu = $usuAdd->transformarLoginLocalEmLdap($objUsu);
                if (!$converteu) {
                    // informando erro ao usuário
                    new Mensagem("Desculpe. Seu login único está inconsistente.<br/>Por favor, entre em contato com o suporte para verificar sua situação.", Mensagem::$MENSAGEM_ERRO);
                    return;
                }

                // recuperando novo usuário
                $tempUsu = self::buscarUsuarioPorId($usuAdd->USR_ID_USUARIO);

                // sinalizando no objeto
                $tempUsu->conversaoLU = TRUE;

                return array(TRUE, $tempUsu);
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar login SIS.", $e);
        }
    }

    /**
     * Esta função cuida do tratamento de Login bloqueado no LDAP. 
     * 
     * 
     * 
     * @param string $login
     * @param string $email
     * @param string $cpf
     * @param string $msgBloqueio Mensagem a ser exibida no lugar de "Login Bloqueado". Campo opcional. Ao utilizá-lo, não coloque ponto no final das frases.
     * @return void
     */
    public static function tratarLoginLDAPBloqueado($login, $email, $cpf, $msgBloqueio = NULL) {
        // preparando justificativa
        $justificativaUsu = Util::vazioNulo($msgBloqueio) ? "Seu usuário está bloqueado no sistema de autenticação da UFES" : $msgBloqueio;

        // verificando se existe o tal login único no sistema
        $usu = Usuario::buscarUsuarioPorLogin($login);
        if (!Util::vazioNulo($usu)) {

            // verificando se o usuário possui email alternativo
            $contato = ContatoCandidato::buscarContatoPorIdUsuario($usu->USR_ID_USUARIO);
            if (!Util::vazioNulo($contato->getCTC_EMAIL_CONTATO())) {

                // transformando login ldap em local
                $transformou = $usu->transformarLoginLdapEmLocal($contato->getCTC_EMAIL_CONTATO());

                // caso transformou com sucesso, iniciando processo de recuperação de senha
                if ($transformou) {
                    // iniciando processo de recuperação de senha
                    $identificacao = IdentificacaoCandidato::buscarIdentCandPorIdUsu($usu->USR_ID_USUARIO);
                    self::gerarRecuperacaoSenha($contato->getCTC_EMAIL_CONTATO(), $identificacao->getIDC_NASC_DATA(), $identificacao->getIDC_NR_CPF(), FALSE);

                    // informando os detalhes ao usuário
                    $parteEmail = NGUtil::parteVisivelEmail($contato->getCTC_EMAIL_CONTATO());
                    $segundaInstrucao = "Um email foi enviado para você com as instruções para recuperar sua senha de acesso ao sistema como comunidade externa.<br/><br/>Não se preocupe, pois você não perderá nenhum dado e, caso seu login único seja desbloqueado, você poderá utilizá-lo normalmente no futuro.";
                    new Mensagem("$justificativaUsu. Para você não ficar sem acesso, alteramos seu login para comunidade externa e você deverá logar no sistema com seu email alternativo (<strong>$parteEmail</strong>).<br/>$segundaInstrucao", Mensagem::$MENSAGEM_AVISO);
                    return;
                } else {
                    // informar usuário sobre impossibilidade de conversão
                    new Mensagem("Desculpe. $justificativaUsu e seu login não pode ser convertido para acesso como comunidade externa.<br/>Por favor, entre em contato com o NTI para habilitar seu login ou informe o caso ao suporte para atualização de seus dados. ", Mensagem::$MENSAGEM_ERRO);
                    return;
                }
            } else {
                // não possui email alternativo? Tentando a exclusão
                if (self::permiteExclusaoUsu($usu->USR_ID_USUARIO, $usu->USR_TP_USUARIO)) {
                    // excluindo
                    self::excluirUsuario($usu->USR_ID_USUARIO);
                } else {
                    // Não tem email alternativo: Informando que o usuário está bloqueado e que não é possível excluir o usuário
                    new Mensagem(" Desculpe. $justificativaUsu e você não possui um email alternativo para realizar login como comunidade externa.<br/>Por favor, entre em contato com o NTI para habilitar seu login ou informe o caso ao suporte para atualização de seus dados.", Mensagem::$MENSAGEM_ERRO);
                    return;
                }
            }
        }

        // verificando se existe login adicional (Mesmo CPF)
        $usuCpf = self::buscarUsuarioPorCPF($cpf);
        $emailLoginAdd = !Util::vazioNulo($usuCpf) ? $usuCpf->USR_DS_EMAIL : NULL;

        // casos: login único não existe ou login único excluído do sistema.
        // Ação: Apenas informando que o referido login está bloqueado no LDAP
        $parteEmailAdd = isset($emailLoginAdd) ? NGUtil::parteVisivelEmail($emailLoginAdd) : NULL;
        $msgAdd = !isset($emailLoginAdd) ? "Se preferir, você também pode realizar um cadastro como comunidade externa." : " Se preferir, você também pode acessar o sistema utilizando seu login como comunidade externa (<strong>$parteEmailAdd</strong>).";
        new Mensagem("Desculpe. $justificativaUsu. Se você acha que isto é um erro, por favor, procure o NTI ou o suporte.<br/>$msgAdd", Mensagem::$MENSAGEM_ERRO);
        return;
    }

    /**
     * Atenção: Devido a questões de compatibilidade com versões anteriores e desempenho, essa função
     * faz referência à tabela de contatos do candidato.
     * 
     * @param string $dsEmail
     * @param int $idUsuario
     * @return boolean
     * @throws NegocioException
     */
    public static function validarCadastroEmail($dsEmail, $idUsuario = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //caso email
            $sql = "select count(*) as cont from tb_usr_usuario usr
                    left join tb_cdt_candidato cdt on usr.USR_ID_USUARIO = cdt.USR_ID_USUARIO
                    left join tb_ctc_contato_candidato ctc on cdt.CTC_ID_CONTATO_CDT = ctc.CTC_ID_CONTATO_CDT 
                    where (`USR_DS_EMAIL` = '$dsEmail' or CTC_EMAIL_CONTATO = '$dsEmail')";

            if ($idUsuario != NULL) {
                $sql .= " and usr.USR_ID_USUARIO != '$idUsuario'";
            }
            $res = $conexao->execSqlComRetorno($sql);
            return $conexao->getResult("cont", $res) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar email.", $e);
        }
    }

    /**
     * Esta função valida o cadastro do email alternativo. 
     * 
     * Atenção: Devido a questões de compatibilidade com versões anteriores e desempenho, essa função
     * faz referência à tabela de contatos do candidato.
     * 
     * 
     * @param string $dsEmail
     * @param int $idUsuario
     * @return boolean
     * @throws NegocioException
     */
    public static function validarEmailAlternativo($dsEmail, $idUsuario = NULL) {
        try {
            // vazio é válido
            if (Util::vazioNulo($dsEmail)) {
                return TRUE;
            }

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // montando sql
            $sql = "select count(*) as cont from tb_usr_usuario usr
                    left join tb_cdt_candidato cdt on usr.USR_ID_USUARIO = cdt.USR_ID_USUARIO
                    left join tb_ctc_contato_candidato ctc on cdt.CTC_ID_CONTATO_CDT = ctc.CTC_ID_CONTATO_CDT 
                    where (`USR_DS_EMAIL` = '$dsEmail' or CTC_EMAIL_CONTATO = '$dsEmail')";

            if ($idUsuario != NULL) {
                $sql.= " and usr.USR_ID_USUARIO != '$idUsuario'";
            }

            $res = $conexao->execSqlComRetorno($sql);
            return

                    $conexao->getResult("cont", $res) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar email alternativo.", $e);
        }
    }

    public static function validarEmailRecuperarSenha($dsEmail) {
        try {
            // tentano recuperar usuário com o email informado
            $usu = self::buscarUsuarioPorEmail($dsEmail);

            // usuáio inexistente
            if (Util::vazioNulo($usu)) {
                return array('status' => FALSE, 'msg' => "<b>Usuário não encontrado.<b>");
            }

            // usuário pertence à ufes
            if (!self::isLoginExterno($usu->USR_DS_LOGIN)) {
                return array('status' => FALSE, 'msg' => "<b>Usuário pertence ao login único.</b> Altere sua senha em http://senha.ufes.br.");
            }

            // usuário administrador
            if ($usu->USR_TP_USUARIO == Usuario::$USUARIO_ADMINISTRADOR) {
                return array('status' => FALSE, 'msg' => "<b>Usuário sem permissão para recuperar senha.</b> Contate o Administrador para atualização de seus dados.");
            }

            // a partir daqui, casos de sucesso
            return array('status' => TRUE, 'campos' => $usu->USR_TP_USUARIO == Usuario::$USUARIO_CANDIDATO);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {

            throw new NegocioException("Erro ao validar email de recuperação de senha.", $e);
        }
    }

    /**
     * 
     * @param string $dsLogin
     * @param int $idUsuario
     * @return boolean
     * @throws NegocioException
     */
    public static function validarCadastroLogin($dsLogin, $idUsuario = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //caso de login comunidade externa
            $sql = "select count(*) as cont from tb_usr_usuario where `USR_DS_LOGIN` = '$dsLogin'";
            if ($idUsuario != NULL) {
                $sql .= " and USR_ID_USUARIO != '$idUsuario'";
            }
            $res = $conexao->execSqlComRetorno($sql);
            return $conexao->getResult("cont", $res) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar login.", $e);
        }
    }

    /**
     * Cria um usuario no banco de dados adicionando dados auxiliares para candidato, se for o caso.
     * Assume que todos os dados necessarios dos objetos estao preenchidos corretamente. 
     * @param Endereco $objEnd
     * @param IdentificacaoCandidato $objIdentCand
     * @param ContatoCandidato $objContatoCand
     * @param int $idCursoCoord
     * @throws NegocioException
     */
    public function criarUsuario($objEnd = NULL, $objIdentCand = NULL, $objContatoCand = NULL, $idCursoCoord = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //validando campos
            //
            //
            //caso email
            if (!Usuario::validarCadastroEmail($this->USR_DS_EMAIL)) {
                throw new NegocioException("Email já cadastrado.");
            }

            //criptografando senha
            $senhaCrip = md5($this->USR_DS_SENHA);

            if ($this->USR_TROCAR_SENHA) {
                $trocarSenha = "'" . FLAG_BD_SIM . "'";
            } else {
                $trocarSenha = "NULL";
            }

            // forçar capitalize no nome
            $this->USR_DS_NOME = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($this->USR_DS_NOME));

            //ajustar email e login
            $this->USR_DS_EMAIL = NGUtil::trataCampoStrParaBD($this->USR_DS_EMAIL);
            $this->USR_DS_LOGIN = NGUtil::trataCampoStrParaBD($this->USR_DS_LOGIN);

            // usuario avaliador
            if ($this->USR_TP_USUARIO == Usuario::$USUARIO_AVALIADOR) {
                $this->USR_ID_CUR_AVALIADOR = Util::vazioNulo($this->USR_ID_CUR_AVALIADOR) ? "NULL" : NGUtil::trataCampoStrParaBD($this->USR_ID_CUR_AVALIADOR);
            } else {
                $this->USR_ID_CUR_AVALIADOR = "NULL";
            }

            //montando sql de criação
            $sql = "insert into tb_usr_usuario (`USR_TP_USUARIO`, `USR_DS_LOGIN`, `USR_DS_EMAIL`, `USR_DS_SENHA`, `USR_DS_NOME`, `USR_TP_VINCULO_UFES`, `USR_ST_SITUACAO`, `USR_LOG_DT_CRIACAO`, `USR_TROCAR_SENHA`, USR_ID_CUR_AVALIADOR)
            values('$this->USR_TP_USUARIO', LOWER($this->USR_DS_LOGIN), LOWER($this->USR_DS_EMAIL), '$senhaCrip', $this->USR_DS_NOME, '$this->USR_TP_VINCULO_UFES', '$this->USR_ST_SITUACAO', now(), $trocarSenha, $this->USR_ID_CUR_AVALIADOR)";

            // verificando caso de candidato
            if ($this->USR_TP_USUARIO == Usuario :: $USUARIO_CANDIDATO && ( $objEnd != NULL || $objContatoCand != NULL || $objIdentCand != NULL)) {
                // delegando tarefa de criaçao ao objeto candidato
                Candidato::criarCandidato($this->USR_TP_VINCULO_UFES, $sql, $objEnd, NULL, $objIdentCand, $objContatoCand);
            } else {
                // verificando se será necessário setar curso
                if ($this->USR_TP_USUARIO == Usuario::$USUARIO_COORDENADOR && !Util::vazioNulo($idCursoCoord)) {


                    // buscando sql de atualizaçao de coordenador
                    $arrayExecDeps = array();
                    $arrayExecDeps[] = Curso::getStringAtualizacaoCoord($idCursoCoord);


                    // criando usuario com dependencia
                    $conexao->execTransacaoDependente($sql, $arrayExecDeps);
                } else {
                    // criando usuario normalmente
                    $conexao->execSqlSemRetorno($sql);
                }
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar usuário.", $e);
        }
    }

    /**
     * 
     * @param String $nmLogin
     * @return \Usuario|null
     * @throws NegocioException
     */ public static function buscarUsuarioPorLogin($nmLogin) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $nmLogin = str_replace("'", "", $nmLogin);

            //montando sql
            $sql = Usuario::getSqlInicialBusca() . " from tb_usr_usuario
            where `USR_DS_LOGIN` = '$nmLogin'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql:: getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Usuario($retorno['idUsuario'], $retorno['tpUsuario'], $retorno['dsLogin'], $retorno['dsEmail'], $retorno['dsSenha'], $retorno['dsNome'], $retorno['tpVinculoUfes'], $retorno['stSituacao'], $retorno['dtSolicTrocaSenha'], $retorno['dsUrlTrocaSenha'], $retorno['dtCriacao'], $retorno['dtUltLogin'], $retorno['USR_TROCAR_SENHA'], $retorno['USR_ID_CUR_AVALIADOR'], $retorno['USR_HASH_ALTERACAO_EXT'], $retorno['USR_EMAIL_VALIDADO']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar usuário.", $e);
        }
    }

    public static function buscarUsuarioPorIdCand($idCandidato) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();
            ;

            //montando sql
            $sql = Usuario::getSqlInicialBusca() . " from tb_usr_usuario
            where `USR_ID_USUARIO` = (select USR_ID_USUARIO from tb_cdt_candidato where CDT_ID_CANDIDATO = '$idCandidato')";


            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql:: getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Usuario($retorno['idUsuario'], $retorno['tpUsuario'], $retorno['dsLogin'], $retorno['dsEmail'], $retorno['dsSenha'], $retorno['dsNome'], $retorno['tpVinculoUfes'], $retorno['stSituacao'], $retorno['dtSolicTrocaSenha'], $retorno['dsUrlTrocaSenha'], $retorno['dtCriacao'], $retorno['dtUltLogin'], $retorno['USR_TROCAR_SENHA'], $retorno['USR_ID_CUR_AVALIADOR'], $retorno['USR_HASH_ALTERACAO_EXT'], $retorno['USR_EMAIL_VALIDADO']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar usuário.", $e);
        }
    }

    private static function getSqlInicialBusca($ident_tabela = "") {
        return "select $ident_tabela`USR_ID_USUARIO` as idUsuario,
                `USR_DS_EMAIL` as dsEmail,
                `USR_DS_SENHA` as dsSenha,
                 USR_ID_CUR_AVALIADOR,
                `USR_DS_NOME` as dsNome,
                `USR_DS_LOGIN` as dsLogin,
                `USR_ST_SITUACAO` as stSituacao,
                `USR_TP_USUARIO` as tpUsuario,
                `USR_TP_VINCULO_UFES` as tpVinculoUfes,
                date_format(`USR_DT_SOLIC_TROCA_SENHA`, '%d/%m/%Y %T') as dtSolicTrocaSenha,
                `USR_DS_URL_TROCA_SENHA` as dsUrlTrocaSenha,
                date_format(`USR_LOG_DT_CRIACAO`, '%d/%m/%Y %T') as dtCriacao,
                date_format(`USR_LOG_DT_ULT_LOGIN`, '%d/%m/%Y %T') as dtUltLogin,
                USR_TROCAR_SENHA,
                USR_HASH_ALTERACAO_EXT,
                USR_EMAIL_VALIDADO,
                USR_ID_CUR_AVALIADOR";
    }

    /**
     * 
     * @param String $nrCPF
     * @return Usuario
     * @throws NegocioException
     */
    public static function buscarUsuarioPorCPF($nrCPF) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = Usuario::getSqlInicialBusca() . " from tb_usr_usuario where `USR_ID_USUARIO` = (
            select USR_ID_USUARIO from tb_cdt_candidato where IDC_ID_IDENTIFICACAO_CDT = (select IDC_ID_IDENTIFICACAO_CDT from tb_idc_identificacao_candidato
            where IDC_NR_CPF = '$nrCPF'))";


            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql:: getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Usuario($retorno['idUsuario'], $retorno['tpUsuario'], $retorno['dsLogin'], $retorno['dsEmail'], $retorno['dsSenha'], $retorno['dsNome'], $retorno['tpVinculoUfes'], $retorno['stSituacao'], $retorno['dtSolicTrocaSenha'], $retorno['dsUrlTrocaSenha'], $retorno['dtCriacao'], $retorno['dtUltLogin'], $retorno['USR_TROCAR_SENHA'], $retorno['USR_ID_CUR_AVALIADOR'], $retorno['USR_HASH_ALTERACAO_EXT'], $retorno['USR_EMAIL_VALIDADO']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar usuário.", $e);
        }
    }

    /**
     * Funçao que retorna avaliadores alocados em um curso especifico.
     * Se o parametro $restrito for falso, tambem retorna os avaliadores nao alocados 
     * em algum curso.
     * @param int $idCurso
     * @param boolean $restrito - Diz se e para restringir a busca apenas aos avaliadores alocados ao curso.
     * @return mixed - Caso nao tenha restriçao, e retornado um array na forma (chave,valor).
     * Caso contrario, e retornando um array na forma (chave).
     * @throws NegocioException
     */
    public static function buscarAvalLivrePorCurso($idCurso, $restrito = FALSE) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $tpAval = Usuario::$USUARIO_AVALIADOR;

            $sql = "select 
                        USR_ID_USUARIO,
                        concat(USR_DS_NOME,
                                concat(' (', concat(USR_DS_EMAIL, ')'))) as Nome
                    from
                        tb_usr_usuario
                    where
                        USR_TP_USUARIO = '$tpAval'
                            and (`USR_ID_CUR_AVALIADOR` = '$idCurso'";

            // tratando restriçao
            if (!$restrito) {
                $sql .= " or `USR_ID_CUR_AVALIADOR` IS NULL)";
            } else {
                $sql .= ")";
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

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['USR_ID_USUARIO'];
                $valor = $dados['Nome'];

                //adicionando no vetor
                if (!$restrito) {
                    $vetRetorno[$chave] = $valor;
                } else {
                    $vetRetorno [] = $chave;
                }
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar avaliadores do curso.", $e);
        }
    }

    /**
     * 
     * @param int $idUsuario
     * @return \Usuario|null
     * @throws NegocioException
     */
    public static function buscarUsuarioPorId($idUsuario) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = Usuario::getSqlInicialBusca("usr.") .
                    ", IDC_NR_CPF
                     , date_format(`IDC_NASC_DATA`, '%d/%m/%Y') as IDC_NASC_DATA   
                     , CTC_EMAIL_CONTATO
                        from
                    tb_usr_usuario usr
                    left join
                    tb_cdt_candidato cdt ON cdt.USR_ID_USUARIO = usr.USR_ID_USUARIO
                    left join
                    tb_idc_identificacao_candidato idc ON idc.IDC_ID_IDENTIFICACAO_CDT = cdt.IDC_ID_IDENTIFICACAO_CDT
                    left join tb_ctc_contato_candidato ctc on ctc.CTC_ID_CONTATO_CDT = cdt.CTC_ID_CONTATO_CDT
                    where usr.`USR_ID_USUARIO` = '$idUsuario'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql:: getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Usuario($retorno['idUsuario'], $retorno['tpUsuario'], $retorno['dsLogin'], $retorno['dsEmail'], $retorno['dsSenha'], $retorno['dsNome'], $retorno['tpVinculoUfes'], $retorno['stSituacao'], $retorno['dtSolicTrocaSenha'], $retorno['dsUrlTrocaSenha'], $retorno['dtCriacao'], $retorno['dtUltLogin'], $retorno['USR_TROCAR_SENHA'], $retorno['USR_ID_CUR_AVALIADOR'], $retorno['USR_HASH_ALTERACAO_EXT'], $retorno['USR_EMAIL_VALIDADO']);

                // colocando campos herdados
                $objUsu->IDC_NR_CPF = $retorno['IDC_NR_CPF'];
                $objUsu->IDC_NASC_DATA = $retorno['IDC_NASC_DATA'];
                $objUsu->CTC_EMAIL_CONTATO = $retorno['CTC_EMAIL_CONTATO'];
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar usuário.", $e);
        }
    }

    /**
     * 
     * @param int $idCurso
     * @return \Usuario|null
     * @throws NegocioException
     */
    public static function buscarUsusAvaliadoresPorCurso($idCurso) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $tpAval = Usuario::$USUARIO_AVALIADOR;

            //montando sql
            $sql = Usuario::getSqlInicialBusca() .
                    " from
                    tb_usr_usuario
                    where USR_TP_USUARIO = '$tpAval' and
                     `USR_ID_CUR_AVALIADOR` = '$idCurso'";

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
                $retorno = ConexaoMysql:: getLinha($resp);
                $usuarioTemp = new Usuario($retorno['idUsuario'], $retorno['tpUsuario'], $retorno['dsLogin'], $retorno['dsEmail'], $retorno['dsSenha'], $retorno['dsNome'], $retorno['tpVinculoUfes'], $retorno['stSituacao'], $retorno['dtSolicTrocaSenha'], $retorno['dsUrlTrocaSenha'], $retorno['dtCriacao'], $retorno['dtUltLogin'], $retorno['USR_TROCAR_SENHA'], $retorno['USR_ID_CUR_AVALIADOR'], $retorno['USR_HASH_ALTERACAO_EXT'], $retorno['USR_EMAIL_VALIDADO']);

                //adicionando no vetor
                $vetRetorno[$i] = $usuarioTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar avaliadores do curso.", $e);
        }
    }

    /**
     * 
     * @param String $dsEmail
     * @return \Usuario|null
     * @throws NegocioException
     */
    public static function buscarUsuarioPorEmail($dsEmail) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = Usuario::getSqlInicialBusca() . " from tb_usr_usuario
            where `USR_DS_EMAIL` = '$dsEmail'";


            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql:: getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objUsu = new Usuario($retorno['idUsuario'], $retorno['tpUsuario'], $retorno['dsLogin'], $retorno['dsEmail'], $retorno['dsSenha'], $retorno['dsNome'], $retorno['tpVinculoUfes'], $retorno['stSituacao'], $retorno['dtSolicTrocaSenha'], $retorno['dsUrlTrocaSenha'], $retorno['dtCriacao'], $retorno['dtUltLogin'], $retorno['USR_TROCAR_SENHA'], $retorno['USR_ID_CUR_AVALIADOR'], $retorno['USR_HASH_ALTERACAO_EXT'], $retorno['USR_EMAIL_VALIDADO']);
                return $objUsu;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar usuário.", $e);
        }
    }

    /**
     * 
     * @param int $idUsuario
     * @param char $tpUsuario
     * @return boolean
     * @throws NegocioException
     */
    public static function permiteExclusaoUsu($idUsuario, $tpUsuario) {
        try {

            if ($tpUsuario == Usuario::$USUARIO_AVALIADOR || $tpUsuario == Usuario::$USUARIO_ADMINISTRADOR || $tpUsuario == Usuario::$USUARIO_COORDENADOR) {
                /**
                 * Não pode ter sido responsável por algum registro em RespAnexoProc
                 * Não pode ter sido responsável por algum relatório de nota em RelNotasInsc
                 * Não pode ter sido responsável por algum registro em EtapaSelProc
                 * Não pode ter sido responsável por algum registro em ProcessoChamada
                 * Não pode ter sido responsável por algum registro em RecursoResulProc
                 * Não pode ter sido responsável por algum registro em InscricaoProcesso
                 * Não pode ter sido responsável por algum registro em AcompProcChamada
                 * Não pode ter sido responsável por algum registro em NotasEtapaSelInsc
                 */
                return RespAnexoProc::contarAvaliacaoPorUsuResp($idUsuario) == 0 && RelNotasInsc::contarRelNotasporUsuResp($idUsuario) == 0 && EtapaSelProc::contarEtapaPorUsuResp($idUsuario) == 0 && ProcessoChamada::contarChamadaporUsuResp($idUsuario) == 0 && RecursoResulProc::contarRecursosPorUsuResp($idUsuario) == 0 && InscricaoProcesso::contarInscricaoPorUsuResp($idUsuario) == 0 && AcompProcChamada::contarAcompPorUsuResp($idUsuario) == 0 && NotasEtapaSelInsc::contarNotasEtapaSelInscPorUsuResp($idUsuario) == 0;
            }

            if ($tpUsuario == Usuario::$USUARIO_CANDIDATO) {
                /**
                 * Não pode ter inscrição em edital
                 * Não pode ter histórico de exclusão
                 */
                return InscricaoProcesso::contarInscricaoPorUsuario($idUsuario) == 0 && HistoricoInscExc::contarHistInscExcPorUsuario($idUsuario) == 0;
            }

            return FALSE;
        } catch (NegocioException $n) {

            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar validar exclusão de usuário.", $e);
        }
    }

    /**
     * 
     * @param int $idUsuario
     * @throws NegocioException
     */
    public static function excluirUsuario($idUsuario) {
        try {

            // verificando se é o login atual
            if ($idUsuario == getIdUsuarioLogado()) {
                throw new NegocioException("Você não pode excluir seu usuário");
            }

            // recuperando usuario a ser excluido
            $usu = self::buscarUsuarioPorId($idUsuario);

            // verificando se pode excluir
            if (!Usuario::permiteExclusaoUsu($usu->USR_ID_USUARIO, $usu->USR_TP_USUARIO)) {
                new Mensagem("Este usuário não pode ser excluído pois já executou alguma operação com necessidade de registro.", Mensagem::$MENSAGEM_ERRO);
            }

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = "delete from tb_usr_usuario
                    where USR_ID_USUARIO = '$idUsuario'";

            //recuperando sqls de exclusão em cascata
            $arrayCmds = array();

            // Removendo o usuário da coordenação de todos os cursos
            $arrayCmds [] = Curso::getStringAtualizacaoCoordNull($idUsuario);

            // removendo currículo no caso de candidato
            if ($usu->USR_TP_USUARIO == self::$USUARIO_CANDIDATO) {
                // excluindo dados do usuario candidato: identificaçao, end, contato, formaçao e dados add
                $arrayCmds = array_merge($arrayCmds, Candidato::getArrayStrExclusaoCandidato($idUsuario));
            }

            // excluindo possíveis rastreios
            $arrayCmds [] = UsuarioRastreio::getSqlRemoveRastreioPorUsuario($idUsuario);

            // adicionando exclusao usuario
            $arrayCmds [] = $sql;

            //persistindo no banco
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar excluir usuário.", $e);
        }
    }

    public static function reiniciarSenha($idUsuario, $senha, $forcarTroca) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //convertendo senha
            $senha = md5($senha);
            $trocarSenha = $forcarTroca ? FLAG_BD_SIM : FLAG_BD_NAO;

            //montando sql
            $sql = "update tb_usr_usuario
                    set USR_DS_SENHA = '$senha',
                     USR_TROCAR_SENHA = '$trocarSenha'
                    , USR_DT_SOLIC_TROCA_SENHA = NULL
                    , USR_DS_URL_TROCA_SENHA = NULL
                    where USR_ID_USUARIO = '$idUsuario'";

            //persistindo no banco
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar reiniciar senha do usuário.", $e);
        }
    }

    /**
     * 
     * @param int $idUsuario
     * @param char $stSituacao
     * @throws NegocioException
     */
    public static function atualizarSituacaoUsuario($idUsuario, $stSituacao) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = "update tb_usr_usuario
                    set
                    USR_ST_SITUACAO = '$stSituacao'
                    , USR_DT_SOLIC_TROCA_SENHA = NULL
                    , USR_DS_URL_TROCA_SENHA = NULL
                    where
                    USR_ID_USUARIO = '$idUsuario'";

            //persistindo no banco


            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar situação de usuário.", $e);
        }
    }

    private static function getSqlNotificacaoAdmins() {
        $tpAdmin = Usuario::$USUARIO_ADMINISTRADOR;
        $stAtivo = NGUtil::getSITUACAO_ATIVO();
        $flagSim = NGUtil::getFlagSim();

        return "SELECT 
                    USR_DS_NOME, USR_DS_EMAIL
                FROM
                    tb_usr_usuario usr
                    join tb_cfu_configuracao_usuario cfu on usr.usr_id_usuario = cfu.usr_id_usuario
                WHERE
                    USR_TP_USUARIO = '$tpAdmin'
                    AND USR_ST_SITUACAO = '$stAtivo' and cfu_fl_acomp_administrador = '$flagSim'";
    }

    /**
     * 
     * @global stdClass $CFG
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     */
    public static function enviarNotAltCalendarioEditalAdmin($processo, $chamada) {

        // recuperar lista de administradores que receberão a notificação
        $sql = Usuario::getSqlNotificacaoAdmins();

        $conexao = NGUtil::getConexao();
        $ret = $conexao->execSqlComRetorno($sql);

        // caso de erro...
        $numLinhas = ConexaoMysql::getNumLinhas($ret);
        if ($numLinhas == 0) {
            error_log("Não há administradores ativos!");
            return;
        }

        // criando títulos e textos comuns
        $titulo = "{$processo->getDsEditalCompleta()} - Calendário alterado";
        $corpoEmail = "O calendário da {$chamada->getPCH_DS_CHAMADA(TRUE)} do Edital {$processo->getDsEditalCompleta()} foi retificado.<br/>Acesse o sistema para verificar as novas datas em {$processo->getUrlAmigavel(TRUE)}.";

        // enviando emails
        for ($i = 0; $i < $numLinhas; $i++) {
            //recuperando linha 
            $dados = ConexaoMysql::getLinha($ret);

            // atualizando dados
            $nome = NGUtil::getPrimeiroNome($dados['USR_DS_NOME']);
            $email = $dados['USR_DS_EMAIL'];
            $temp = "Olá, $nome.<br/><br/>" . $corpoEmail;

            // enviando email
            enviaEmail($email, $titulo, $temp, NULL, TRUE);
        }
    }

    /**
     * 
     * @global stdClass $CFG
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param array $pubPendente Array informando a publicação pendente nos moldes da classe EtapaSelProc [tp, nome]
     * @param int $idUsuSolicitante
     */
    public static function enviarNotSolPubResultadoChamAdmin($processo, $chamada, $pubPendente, $idUsuSolicitante) {

        // recuperar lista de administradores que receberão a notificação
        $sql = Usuario::getSqlNotificacaoAdmins();

        $conexao = NGUtil::getConexao();
        $ret = $conexao->execSqlComRetorno($sql);

        // caso de erro...
        $numLinhas = ConexaoMysql::getNumLinhas($ret);
        if ($numLinhas == 0) {
            error_log("Não há administradores ativos!");
            return;
        }

        // criando títulos e textos comuns
        $usuario = Usuario::buscarUsuarioPorId($idUsuSolicitante);
        $titulo = "{$processo->getDsEditalCompleta()} - Solicitação de publicação";
        $artigo = ($pubPendente[0] == EtapaSelProc::$PENDENTE_RET_RESUL_PARCIAL || $pubPendente[0] == EtapaSelProc::$PENDENTE_RET_RESUL_POS_REC) ? "a" : "o";
        $corpoEmail = "Foi solicitada a publicação d$artigo $pubPendente[1] da {$chamada->getPCH_DS_CHAMADA(TRUE)} do Edital {$processo->getDsEditalCompleta()} pelo usuário {$usuario->getUSR_DS_NOME()}.<br/>Acesse o sistema para publicar o resultado.";

        // enviando emails
        for ($i = 0; $i < $numLinhas; $i++) {
            //recuperando linha 
            $dados = ConexaoMysql::getLinha($ret);

            // atualizando dados
            $nome = NGUtil::getPrimeiroNome($dados['USR_DS_NOME']);
            $email = $dados['USR_DS_EMAIL'];
            $temp = "Olá, $nome.<br/><br/>" . $corpoEmail;

            // enviando email
            enviaEmail($email, $titulo, $temp, NULL, TRUE);
        }
    }

    /**
     * 
     * @global stdClass $CFG
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param int $idUsuSolicitante
     */
    public static function enviarNotSolAtivacaoChamAdmin($processo, $chamada, $idUsuSolicitante) {

        // recuperar lista de administradores que receberão a notificação
        $sql = Usuario::getSqlNotificacaoAdmins();

        $conexao = NGUtil::getConexao();
        $ret = $conexao->execSqlComRetorno($sql);

        // caso de erro...
        $numLinhas = ConexaoMysql::getNumLinhas($ret);
        if ($numLinhas == 0) {
            error_log("Não há administradores ativos!");
            return;
        }

        // criando títulos e textos comuns
        $usuario = Usuario::buscarUsuarioPorId($idUsuSolicitante);
        $titulo = "{$processo->getDsEditalCompleta()} - Solicitação de ativação";
        $corpoEmail = "Foi solicitada a ativação da {$chamada->getPCH_DS_CHAMADA(TRUE)} do Edital {$processo->getDsEditalCompleta()} pelo usuário {$usuario->getUSR_DS_NOME()}.<br/>Acesse o sistema para ativar a chamada.";

        // enviando emails
        for ($i = 0; $i < $numLinhas; $i++) {
            //recuperando linha 
            $dados = ConexaoMysql::getLinha($ret);

            // atualizando dados
            $nome = NGUtil::getPrimeiroNome($dados['USR_DS_NOME']);
            $email = $dados['USR_DS_EMAIL'];
            $temp = "Olá, $nome.<br/><br/>" . $corpoEmail;

            // enviando email
            enviaEmail($email, $titulo, $temp, NULL, TRUE);
        }
    }

    /**
     * 
     * @global stdClass $CFG
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param int $idResponsavel
     */
    public static function enviarNotAtivacaoChamEditalAdmin($processo, $chamada, $idResponsavel) {

        // recuperar lista de administradores que receberão a notificação
        $sql = Usuario::getSqlNotificacaoAdmins();

        $conexao = NGUtil::getConexao();
        $ret = $conexao->execSqlComRetorno($sql);

        // caso de erro...
        $numLinhas = ConexaoMysql::getNumLinhas($ret);
        if ($numLinhas == 0) {
            error_log("Não há administradores ativos!");
            return;
        }

        // criando títulos e textos comuns
        $usuario = Usuario::buscarUsuarioPorId($idResponsavel);
        $titulo = "{$processo->getDsEditalCompleta()} - Ativação de chamada";
        $corpoEmail = "A {$chamada->getPCH_DS_CHAMADA(TRUE)} do Edital {$processo->getDsEditalCompleta()} foi ativada pelo usuário {$usuario->getUSR_DS_NOME()}.<br/>Confira em {$processo->getUrlAmigavel(TRUE)}.";

        // enviando emails
        for ($i = 0; $i < $numLinhas; $i++) {
            //recuperando linha 
            $dados = ConexaoMysql::getLinha($ret);

            // atualizando dados
            $nome = NGUtil::getPrimeiroNome($dados['USR_DS_NOME']);
            $email = $dados['USR_DS_EMAIL'];
            $temp = "Olá, $nome.<br/><br/>" . $corpoEmail;

            // enviando email
            enviaEmail($email, $titulo, $temp, NULL, TRUE);
        }
    }

    /**
     * 
     * @global stdClass $CFG
     * @param Processo $processo
     * @param int $idResponsavel
     */
    public static function enviarNotAlteracaoEditalAdmin($processo, $idResponsavel) {

        // recuperar lista de administradores que receberão a notificação
        $sql = Usuario::getSqlNotificacaoAdmins();

        $conexao = NGUtil::getConexao();
        $ret = $conexao->execSqlComRetorno($sql);

        // caso de erro...
        $numLinhas = ConexaoMysql::getNumLinhas($ret);
        if ($numLinhas == 0) {
            error_log("Não há administradores ativos!");
            return;
        }

        // criando títulos e textos comuns
        $usuario = Usuario::buscarUsuarioPorId($idResponsavel);
        $titulo = "{$processo->getDsEditalCompleta()} - Alteração do edital";
        $corpoEmail = "O PDF do Edital {$processo->getDsEditalCompleta()} foi atualizado pelo usuário {$usuario->getUSR_DS_NOME()}.<br/>Confira em {$processo->getUrlAmigavel(TRUE)}.";

        // enviando emails
        for ($i = 0; $i < $numLinhas; $i++) {
            //recuperando linha 
            $dados = ConexaoMysql::getLinha($ret);

            // atualizando dados
            $nome = NGUtil::getPrimeiroNome($dados['USR_DS_NOME']);
            $email = $dados['USR_DS_EMAIL'];
            $temp = "Olá, $nome.<br/><br/>" . $corpoEmail;

            // enviando email
            enviaEmail($email, $titulo, $temp, NULL, TRUE);
        }
    }

    /**
     * 
     * @global stdClass $CFG
     * @param Processo $processo
     * @param string $dsPublicacao
     * @param int $idResponsavel
     */
    public static function enviarNotPubResultadoAdmin($processo, $dsPublicacao, $idResponsavel) {

        // recuperar lista de administradores que receberão a notificação
        $sql = Usuario::getSqlNotificacaoAdmins();

        $conexao = NGUtil::getConexao();
        $ret = $conexao->execSqlComRetorno($sql);

        // caso de erro...
        $numLinhas = ConexaoMysql::getNumLinhas($ret);
        if ($numLinhas == 0) {
            error_log("Não há administradores ativos!");
            return;
        }

        // criando títulos e textos comuns
        $usuario = Usuario::buscarUsuarioPorId($idResponsavel);
        $titulo = "{$processo->getDsEditalCompleta()} - Publicação de resultado";
        $corpoEmail = "O Edital {$processo->getDsEditalCompleta()} foi alterado por {$usuario->getUSR_DS_NOME()}.<br/>
                       $dsPublicacao<br/>Confira em {$processo->getUrlAmigavel(TRUE)}.";

        // enviando emails
        for ($i = 0; $i < $numLinhas; $i++) {
            //recuperando linha 
            $dados = ConexaoMysql::getLinha($ret);

            // atualizando dados
            $nome = NGUtil::getPrimeiroNome($dados['USR_DS_NOME']);
            $email = $dados['USR_DS_EMAIL'];
            $temp = "Olá, $nome.<br/><br/>" . $corpoEmail;

            // enviando email
            enviaEmail($email, $titulo, $temp, NULL, TRUE);
        }
    }

    /**
     * 
     * @global stdClass $CFG
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param int $idResponsavel
     */
    public static function enviarNotRetVagasAdmin($processo, $chamada, $idResponsavel) {

        // recuperar lista de administradores que receberão a notificação
        $sql = Usuario::getSqlNotificacaoAdmins();

        $conexao = NGUtil::getConexao();
        $ret = $conexao->execSqlComRetorno($sql);

        // caso de erro...
        $numLinhas = ConexaoMysql::getNumLinhas($ret);
        if ($numLinhas == 0) {
            error_log("Não há administradores ativos!");
            return;
        }

        // criando títulos e textos comuns
        $usuario = Usuario::buscarUsuarioPorId($idResponsavel);
        $titulo = "{$processo->getDsEditalCompleta()} - Retificação de vagas";
        $corpoEmail = "As Vagas da {$chamada->getPCH_DS_CHAMADA(TRUE)} do Edital {$processo->getDsEditalCompleta()} foram retificadas por {$usuario->getUSR_DS_NOME()}.<br/>Confira em {$processo->getUrlAmigavel(TRUE)}.";

        // enviando emails
        for ($i = 0; $i < $numLinhas; $i++) {
            //recuperando linha 
            $dados = ConexaoMysql::getLinha($ret);

            // atualizando dados
            $nome = NGUtil::getPrimeiroNome($dados['USR_DS_NOME']);
            $email = $dados['USR_DS_EMAIL'];
            $temp = "Olá, $nome.<br/><br/>" . $corpoEmail;

            // enviando email
            enviaEmail($email, $titulo, $temp, NULL, TRUE);
        }
    }

    public static function enviarMsgContato($nome, $email, $tpContato, $telefone, $mensagem) {
        global $CFG;

        // email de contato está configurado?
        if (!isset($CFG->emailContato) || Util::vazioNulo($CFG->emailContato)) {
            error_log("Email de contato não configurado!"); // Informando no LOG
            return FALSE;
        }

        $corpoEmail = "============================================================<br/><br/>";
        $corpoEmail .= "<b>Nome:</b> $nome<br/>";
        $corpoEmail .= "<b>Email:</b> $email<br/>";

        $dsContato = Usuario::getDsContato($tpContato);
        $corpoEmail .= "<b>Tipo de contato:</b> $dsContato<br/>";

        if (!Util::vazioNulo($telefone)) {
            $corpoEmail .= "<b>Telefone:</b> $telefone<br/>";
        }

        $corpoEmail .= "<br/>============================================================<br/><br/>";
        $corpoEmail .= "<b>Mensagem:</b><br/><br/>";
        $corpoEmail .= "$mensagem<br/>";
        $corpoEmail .= "<br/>============================================================";

        $titulo = "$dsContato";

        // tentando enviar email 
        return enviaEmail($CFG->emailContato, $titulo, $corpoEmail, $email, TRUE);
    }

    public function isTrocarSenha() {
        return !Util::vazioNulo($this->USR_TROCAR_SENHA) && $this->USR_TROCAR_SENHA == FLAG_BD_SIM;
    }

    public function isAtivo() {
        return $this->USR_ST_SITUACAO == NGUtil::getSITUACAO_ATIVO();
    }

    public function isConversaoLU() {
        return $this->conversaoLU;
    }

    public function getNrCpfMascarado() {
        return !Util::vazioNulo($this->IDC_NR_CPF) ? adicionarMascara("###.###.###-##", $this->IDC_NR_CPF) : Util::$STR_CAMPO_VAZIO;
    }

    public function getDtNascimento() {
        return $this->IDC_NASC_DATA;
    }

    public function getEmailAlternativo() {
        return $this->CTC_EMAIL_CONTATO;
    }

    /* GET FIELDS FROM TABLE */

    function getUSR_ID_USUARIO() {
        return $this->USR_ID_USUARIO;
    }

    /* End of get USR_ID_USUARIO */

    function getUSR_TP_USUARIO() {
        return $this->USR_TP_USUARIO;
    }

    /* End of get USR_TP_USUARIO */

    function getUSR_DS_LOGIN() {
        return $this->USR_DS_LOGIN;
    }

    /* End of get USR_DS_LOGIN */

    function getUSR_DS_EMAIL() {
        return $this->USR_DS_EMAIL;
    }

    /* End of get USR_DS_EMAIL */

    function getUSR_DS_SENHA() {
        return $this->USR_DS_SENHA;
    }

    /* End of get USR_DS_SENHA */

    function getUSR_DS_NOME() {
        return $this->USR_DS_NOME;
    }

    /* End of get USR_DS_NOME */

    function getUSR_TP_VINCULO_UFES() {
        return $this->USR_TP_VINCULO_UFES;
    }

    /* End of get USR_TP_VINCULO_UFES */

    function getUSR_ST_SITUACAO() {
        return $this->USR_ST_SITUACAO;
    }

    /* End of get USR_ST_SITUACAO */

    function getUSR_DT_SOLIC_TROCA_SENHA($completaVazio = TRUE) {
        if ($completaVazio && Util::vazioNulo($this->USR_DT_SOLIC_TROCA_SENHA)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->USR_DT_SOLIC_TROCA_SENHA;
    }

    /* End of get USR_DT_SOLIC_TROCA_SENHA */

    function getUSR_DS_URL_TROCA_SENHA($completaVazio = TRUE) {
        if ($completaVazio && Util::vazioNulo($this->USR_DS_URL_TROCA_SENHA)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->USR_DS_URL_TROCA_SENHA;
    }

    /* End of get USR_DS_URL_TROCA_SENHA */

    function getUSR_LOG_DT_CRIACAO() {
        return $this->USR_LOG_DT_CRIACAO;
    }

    /* End of get USR_LOG_DT_CRIACAO */

    function getUSR_LOG_DT_ULT_LOGIN() {
        return $this->USR_LOG_DT_ULT_LOGIN;
    }

    /* End of get USR_LOG_DT_ULT_LOGIN */

    function getUSR_TROCAR_SENHA() {
        return $this->USR_TROCAR_SENHA;
    }

    function getUSR_ID_CUR_AVALIADOR() {

        return $this->USR_ID_CUR_AVALIADOR;
    }

    /* End of get USR_ID_CUR_AVALIADOR */

    function getUSR_HASH_ALTERACAO_EXT() {
        return $this->USR_HASH_ALTERACAO_EXT;
    }

    /* End of get USR_HASH_ALTERACAO_EXT */

    function getUSR_EMAIL_VALIDADO() {
        return $this->USR_EMAIL_VALIDADO;
    }

    /* End of get USR_EMAIL_VALIDADO */


    /* SET FIELDS FROM TABLE */

    function setUSR_ID_USUARIO($value) {
        $this->USR_ID_USUARIO = $value;
    }

    /* End of SET USR_ID_USUARIO */

    function setUSR_TP_USUARIO($value) {
        $this->USR_TP_USUARIO = $value;
    }

    /* End of SET USR_TP_USUARIO */

    function setUSR_DS_LOGIN($value) {
        $this->USR_DS_LOGIN = $value;
    }

    /* End of SET USR_DS_LOGIN */

    function setUSR_DS_EMAIL($value) {
        $this->USR_DS_EMAIL = $value;
    }

    /* End of SET USR_DS_EMAIL */

    function setUSR_DS_SENHA($value) {
        $this->USR_DS_SENHA = $value;
    }

    /* End of SET USR_DS_SENHA */

    function setUSR_DS_NOME($value) {
        $this->USR_DS_NOME = $value;
    }

    /* End of SET USR_DS_NOME */

    function setUSR_TP_VINCULO_UFES($value) {
        $this->USR_TP_VINCULO_UFES = $value;
    }

    /* End of SET USR_TP_VINCULO_UFES */

    function setUSR_ST_SITUACAO($value) {

        $this->USR_ST_SITUACAO = $value;
    }

    /* End of SET USR_ST_SITUACAO */

    function setUSR_DT_SOLIC_TROCA_SENHA($value) {
        $this->USR_DT_SOLIC_TROCA_SENHA = $value;
    }

    /* End of SET USR_DT_SOLIC_TROCA_SENHA */

    function setUSR_DS_URL_TROCA_SENHA($value) {

        $this->USR_DS_URL_TROCA_SENHA = $value;
    }

    /* End of SET USR_DS_URL_TROCA_SENHA */

    function setUSR_LOG_DT_CRIACAO($value) {

        $this->USR_LOG_DT_CRIACAO = $value;
    }

    /* End of SET USR_LOG_DT_CRIACAO */

    function setUSR_LOG_DT_ULT_LOGIN($value) {
        $this->USR_LOG_DT_ULT_LOGIN = $value;
    }

    /* End of SET USR_LOG_DT_ULT_LOGIN */

    function setUSR_TROCAR_SENHA($value) {
        $this->USR_TROCAR_SENHA = $value;
    }

    /* End of SET USR_TROCAR_SENHA */

    function setUSR_ID_CUR_AVALIADOR($value) {

        $this->USR_ID_CUR_AVALIADOR = $value;
    }

    /* End of SET USR_ID_CUR_AVALIADOR */

    function setUSR_HASH_ALTERACAO_EXT($value) {
        $this->USR_HASH_ALTERACAO_EXT = $value;
    }

    /* End of SET USR_HASH_ALTERACAO_EXT */

    function setUSR_EMAIL_VALIDADO($value) {
        $this->USR_EMAIL_VALIDADO = $value;
    }

    /* End of SET USR_EMAIL_VALIDADO */
}

?>
