<?php

/**
 * tb_cdt_candidato class
 * This class manipulates the table Candidato
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * Modificado por      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 27/08/2013
 * */
class Candidato {

    private $CDT_ID_CANDIDATO;
    private $USR_ID_USUARIO;
    private $IDC_ID_IDENTIFICACAO_CDT;
    private $CTC_ID_CONTATO_CDT;
    private $CDT_DT_ULT_ATUALIZACAO;
    private $CDT_ENDERECO_RES;
    private $CDT_ENDERECO_COM;
    private $CDT_DS_INF_COMPLEMENTAR;
    private static $MAX_DIAS_INATIVIDADE = 180;
    // constantes de preenchimento de perfil
    public static $PREENC_IDENTIFICACAO = "I";
    public static $PREENC_ENDERECO = "E";
    public static $PREENC_CONTATO = "C";
    public static $PREENC_CURRICULO = "F";
    public static $PARAM_PREENC_REVISAO = "r";

    /* Construtor padrão da classe */

    public function __construct($CDT_ID_CANDIDATO, $USR_ID_USUARIO, $IDC_ID_IDENTIFICACAO_CDT, $CTC_ID_CONTATO_CDT, $CDT_DT_ULT_ATUALIZACAO, $CDT_ENDERECO_RES, $CDT_ENDERECO_COM, $CDT_DS_INF_COMPLEMENTAR) {
        $this->CDT_ID_CANDIDATO = $CDT_ID_CANDIDATO;
        $this->USR_ID_USUARIO = $USR_ID_USUARIO;
        $this->IDC_ID_IDENTIFICACAO_CDT = $IDC_ID_IDENTIFICACAO_CDT;
        $this->CTC_ID_CONTATO_CDT = $CTC_ID_CONTATO_CDT;
        $this->CDT_DT_ULT_ATUALIZACAO = $CDT_DT_ULT_ATUALIZACAO;
        $this->CDT_ENDERECO_RES = $CDT_ENDERECO_RES;
        $this->CDT_ENDERECO_COM = $CDT_ENDERECO_COM;
        $this->CDT_DS_INF_COMPLEMENTAR = $CDT_DS_INF_COMPLEMENTAR;
    }

    public static function permiteAlteracaoCurriculo($idCandidato) {
        // Deixar currículo livre quando estiver em desenvolvimento
        global $CFG;
        if ($CFG->ambiente == Util::$AMBIENTE_DESENVOLVIMENTO) {
            return TRUE;
        }

        // caso do candidato estar concorrendo a algum edital
        $qtInsAberta = InscricaoProcesso::contarInscProcChamAbertaPorCdt($idCandidato);
        return $qtInsAberta == 0;
//        return TRUE;
    }

    public static function buscarCandidatoPorId($idCandidato) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select 
                    CDT_ID_CANDIDATO,
                    USR_ID_USUARIO,
                    IDC_ID_IDENTIFICACAO_CDT,
                    CTC_ID_CONTATO_CDT,
                    date_format(`CDT_DT_ULT_ATUALIZACAO`, '%d/%m/%Y') as CDT_DT_ULT_ATUALIZACAO,
                    CDT_ENDERECO_RES,
                    CDT_ENDERECO_COM,
                    CDT_DS_INF_COMPLEMENTAR
                from
                    tb_cdt_candidato
                where
                    CDT_ID_CANDIDATO = '$idCandidato'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objCdt = new Candidato($retorno['CDT_ID_CANDIDATO'], $retorno['USR_ID_USUARIO'], $retorno['IDC_ID_IDENTIFICACAO_CDT'], $retorno['CTC_ID_CONTATO_CDT'], $retorno['CDT_DT_ULT_ATUALIZACAO'], $retorno['CDT_ENDERECO_RES'], $retorno['CDT_ENDERECO_COM'], $retorno['CDT_DS_INF_COMPLEMENTAR']);
                return $objCdt;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar candidato.", $e);
        }
    }

    public static function buscarCandidatoPorIdUsu($idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = "select 
                    CDT_ID_CANDIDATO,
                    USR_ID_USUARIO,
                    IDC_ID_IDENTIFICACAO_CDT,
                    CTC_ID_CONTATO_CDT,
                    date_format(`CDT_DT_ULT_ATUALIZACAO`, '%d/%m/%Y') as CDT_DT_ULT_ATUALIZACAO,
                    CDT_ENDERECO_RES,
                    CDT_ENDERECO_COM,
                    CDT_DS_INF_COMPLEMENTAR
                from
                    tb_cdt_candidato
                where
                    USR_ID_USUARIO = '$idUsuario'";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objCdt = new Candidato($retorno['CDT_ID_CANDIDATO'], $retorno['USR_ID_USUARIO'], $retorno['IDC_ID_IDENTIFICACAO_CDT'], $retorno['CTC_ID_CONTATO_CDT'], $retorno['CDT_DT_ULT_ATUALIZACAO'], $retorno['CDT_ENDERECO_RES'], $retorno['CDT_ENDERECO_COM'], $retorno['CDT_DS_INF_COMPLEMENTAR']);
                return $objCdt;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar candidato.", $e);
        }
    }

    public static function mostrarInatividade($idUsuario) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //sql
            $sql = "select 
                        UNIX_TIMESTAMP(`CDT_DT_ULT_ATUALIZACAO`) as dtUltAtualizacao
                    from
                        tb_cdt_candidato
                    where
                        USR_ID_USUARIO = '$idUsuario'";

            $res = $conexao->execSqlComRetorno($sql);
            $ultAtualizacao = $conexao->getResult("dtUltAtualizacao", $res);
            $hoje = dt_getTimestampDtUS();

            return floor(($hoje - $ultAtualizacao) / (60 * 60 * 24)) > self::$MAX_DIAS_INATIVIDADE;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar campos internos de candidato.", $e);
        }
    }

    /**
     * 
     * @param string $nrCPF
     * @return boolean
     * @throws NegocioException
     */
    public static function validarCadastroCPF($nrCPF, $idUsuario = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //sql
            $sql = "select count(*) as cont from tb_idc_identificacao_candidato where `IDC_NR_CPF` = '$nrCPF'";

            // incluindo id usuario
            if (!Util::vazioNulo($idUsuario)) {
                $sql .= " and IDC_ID_IDENTIFICACAO_CDT != (select IDC_ID_IDENTIFICACAO_CDT from tb_cdt_candidato where USR_ID_USUARIO = '$idUsuario')";
            }

            $res = $conexao->execSqlComRetorno($sql);
            return $conexao->getResult("cont", $res) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar CPF.", $e);
        }
    }

    /**
     * 
     * @param int $idUsuario
     * @return int
     * @throws NegocioException
     */
    public static function getIdCandidatoPorIdUsuario($idUsuario) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //sql
            $sql = "select CDT_ID_CANDIDATO as id from tb_cdt_candidato where `USR_ID_USUARIO` = '$idUsuario'";

            $res = $conexao->execSqlComRetorno($sql);
            return $conexao->getResult("id", $res);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar candidato por usuário.", $e);
        }
    }

    public static function getArrayStrExclusaoCandidato($idUsuario) {
        $array = array();

        // buscando objeto candidato a ser excluído
        $candidato = self::buscarCandidatoPorIdUsu($idUsuario);

        // formaçao academica
        $array [] = FormacaoAcademica::getSqlExclusaoFormAcad($candidato->CDT_ID_CANDIDATO);

        // publicação 
        $array [] = Publicacao::getSqlExclusaoPublicacao($candidato->CDT_ID_CANDIDATO);

        // atuação 
        $array [] = Atuacao::getSqlExclusaoAtuacao($candidato->CDT_ID_CANDIDATO);

        // participação em evento 
        $array [] = ParticipacaoEvento::getSqlExclusaoPartEvento($candidato->CDT_ID_CANDIDATO);

        // exclusão dado currículo
        $array [] = "delete from tb_dcu_dado_curriculo where CDT_ID_CANDIDATO = '$candidato->CDT_ID_CANDIDATO'";

        // exclusao candidato
        $temp = "delete from tb_cdt_candidato
                where USR_ID_USUARIO = '$idUsuario'";
        $array [] = $temp;


        //exclusao identificacao candidato
        if (!Util::vazioNulo($candidato->IDC_ID_IDENTIFICACAO_CDT)) {
            $temp = "delete from tb_idc_identificacao_candidato 
                where
                    IDC_ID_IDENTIFICACAO_CDT = '$candidato->IDC_ID_IDENTIFICACAO_CDT'";
            $array [] = $temp;
        }

        // exclusao contato
        if (!Util::vazioNulo($candidato->CTC_ID_CONTATO_CDT)) {
            $temp = "delete from tb_ctc_contato_candidato 
                where
                    CTC_ID_CONTATO_CDT = '$candidato->CTC_ID_CONTATO_CDT'";
            $array [] = $temp;
        }

        // exclusao endereco res
        if (!Util::vazioNulo($candidato->CDT_ENDERECO_RES)) {
            $temp = "delete from tb_end_endereco 
                where
                    END_ID_ENDERECO = '$candidato->CDT_ENDERECO_RES'";
            $array [] = $temp;
        }

        // exclusao endereco com
        if (!Util::vazioNulo($candidato->CDT_ENDERECO_COM)) {
            $temp = "delete from tb_end_endereco 
                where
                    END_ID_ENDERECO = '$candidato->CDT_ENDERECO_COM'";
            $array [] = $temp;
        }

        return $array;
    }

    /**
     * Cria um candidato no banco de dados adicionando os dados auxiliares.
     * Assume que todos os dados necessarios dos objetos estao preenchidos corretamente. 
     * @param string $tpVinculoUfes
     * @param string $sqlUsuarioSis
     * @param Endereco $objEndRes
     * @param Endereco $objEndCom
     * @param IdentificacaoCandidato $objIdentCand
     * @param ContatoCandidato $objContatoCand
     * @param string $dsInfComp
     * @throws NegocioException
     */
    public static function criarCandidato($tpVinculoUfes, $sqlUsuarioSis, $objEndRes = NULL, $objEndCom = NULL, $objIdentCand = NULL, $objContatoCand = NULL, $dsInfComp = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $dep = ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE;

            $dsInfComp = NGUtil::trataCampoStrParaBD($dsInfComp);

            //montando sql de criação
            $sql = "insert into tb_cdt_candidato (`USR_ID_USUARIO`, `IDC_ID_IDENTIFICACAO_CDT`, `CTC_ID_CONTATO_CDT`, `CDT_DT_ULT_ATUALIZACAO`, `CDT_ENDERECO_RES`, `CDT_ENDERECO_COM`, `CDT_DS_INF_COMPLEMENTAR`)
             values('$dep', '$dep', $dep, curdate(), $dep, $dep, $dsInfComp)";

            // verificando caso de dependencias
            $arrayCmds = array();

            // caso do usuario
            $arrayCmds [] = $sqlUsuarioSis;

            // caso de identificaçao candidato
            $arrayCmds [] = IdentificacaoCandidato::getStringCriacaoIdentCand($objIdentCand, $tpVinculoUfes);

            // verificando caso de contato candidato
            if ($objContatoCand != NULL) {
                $arrayCmds [] = ContatoCandidato::getStringCriacaoContatoCand($objContatoCand);
            } else {
                $arrayCmds [] = NULL;
            }

            // verificando caso de endereço residencial
            if ($objEndRes != NULL) {
                $temp = Endereco::getStringCriacaoEnd($objEndRes);
                if ($temp != NULL) {
                    $arrayCmds [] = $temp;
                }
            } else {
                $arrayCmds [] = NULL;
            }

            // verificando caso de endereço comercial
            if ($objEndCom != NULL) {
                $temp = Endereco::getStringCriacaoEnd($objEndCom);
                if ($temp != NULL) {
                    $arrayCmds [] = $temp;
                }
            } else {
                $arrayCmds [] = NULL;
            }

            // realizando persistencia no banco
            $conexao->execTransacaoEmFilaDependente($sql, $arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar usuário.", $e);
        }
    }

    /**
     * Retorna sql que faz o endereço do usuario apontar para nulo. 
     * Observe que antes de executar esse sql deve ser recuperado o id do endereco
     * para posterior exclusao.
     * @param int $idUsuario
     * @param Tipo de endereço da classe Endereco $tipo 
     * @return string
     */
    public static function getStringAnulaEnd($idUsuario, $tipo) {
        $sql = "update tb_cdt_candidato";
        if ($tipo == Endereco::$TIPO_RESIDENCIAL) {
            $sql .= " set CDT_ENDERECO_RES = NULL";
        } else {
            $sql .= " set CDT_ENDERECO_COM = NULL";
        }
        $sql .= " where USR_ID_USUARIO = '$idUsuario'";

        return $sql;
    }

    /**
     * Retorna sql que faz o endereço do usuario apontar para o $idEndereco
     * 
     * @param int $idUsuario
     * @param Tipo de endereço da classe Endereco $tipo 
     * @param int $idEndereco 
     * @return string
     */
    public static function getStringApontaEnd($idUsuario, $tipo, $idEndereco) {
        $sql = "update tb_cdt_candidato";
        if ($tipo == Endereco::$TIPO_RESIDENCIAL) {
            $sql .= " set CDT_ENDERECO_RES = '$idEndereco'";
        } else {
            $sql .= " set CDT_ENDERECO_COM = '$idEndereco'";
        }
        $sql .= " where USR_ID_USUARIO = '$idUsuario'";
        return $sql;
    }

    /**
     * Retorna sql que faz o contato do usuario apontar para o $idContato
     * @param int $idUsuario
     * @param int $idContato
     * @return string
     */
    public static function getStringApontaContato($idUsuario, $idContato) {
        $sql = "update tb_cdt_candidato set CTC_ID_CONTATO_CDT = '$idContato'
            where USR_ID_USUARIO = '$idUsuario'";
        return $sql;
    }

    /**
     * Funçao que retorna string com comando que atualiza o campo data de atualizaçao
     * das informaçoes do candidato
     * 
     * @param int $idUsuario
     * @return string
     */
    public static function getSqlAtualizaDtAlteracaoPerfil($idUsuario) {
        $sql = "update tb_cdt_candidato 
                set 
                    CDT_DT_ULT_ATUALIZACAO = curdate()
                where
                    USR_ID_USUARIO = '$idUsuario'";
        return $sql;
    }

    /* GET FIELDS FROM TABLE */

    function getCDT_ID_CANDIDATO() {
        return $this->CDT_ID_CANDIDATO;
    }

    /* End of get CDT_ID_CANDIDATO */

    function getUSR_ID_USUARIO() {
        return $this->USR_ID_USUARIO;
    }

    /* End of get USR_ID_USUARIO */

    function getIDC_ID_IDENTIFICACAO_CDT() {
        return $this->IDC_ID_IDENTIFICACAO_CDT;
    }

    /* End of get IDC_ID_IDENTIFICACAO_CDT */

    function getCTC_ID_CONTATO_CDT() {
        return $this->CTC_ID_CONTATO_CDT;
    }

    /* End of get CTC_ID_CONTATO_CDT */

    function getCDT_DT_ULT_ATUALIZACAO() {
        return $this->CDT_DT_ULT_ATUALIZACAO;
    }

    /* End of get CDT_DT_ULT_ATUALIZACAO */

    function getCDT_ENDERECO_RES() {
        return $this->CDT_ENDERECO_RES;
    }

    /* End of get CDT_ENDERECO_RES */

    function getCDT_ENDERECO_COM() {
        return $this->CDT_ENDERECO_COM;
    }

    /* End of get CDT_ENDERECO_COM */

    function getCDT_DS_INF_COMPLEMENTAR() {
        return $this->CDT_DS_INF_COMPLEMENTAR;
    }

    /* End of get CDT_DS_INF_COMPLEMENTAR */



    /* SET FIELDS FROM TABLE */

    function setCDT_ID_CANDIDATO($value) {
        $this->CDT_ID_CANDIDATO = $value;
    }

    /* End of SET CDT_ID_CANDIDATO */

    function setUSR_ID_USUARIO($value) {
        $this->USR_ID_USUARIO = $value;
    }

    /* End of SET USR_ID_USUARIO */

    function setIDC_ID_IDENTIFICACAO_CDT($value) {
        $this->IDC_ID_IDENTIFICACAO_CDT = $value;
    }

    /* End of SET IDC_ID_IDENTIFICACAO_CDT */

    function setCTC_ID_CONTATO_CDT($value) {
        $this->CTC_ID_CONTATO_CDT = $value;
    }

    /* End of SET CTC_ID_CONTATO_CDT */

    function setCDT_DT_ULT_ATUALIZACAO($value) {
        $this->CDT_DT_ULT_ATUALIZACAO = $value;
    }

    /* End of SET CDT_DT_ULT_ATUALIZACAO */

    function setCDT_ENDERECO_RES($value) {
        $this->CDT_ENDERECO_RES = $value;
    }

    /* End of SET CDT_ENDERECO_RES */

    function setCDT_ENDERECO_COM($value) {
        $this->CDT_ENDERECO_COM = $value;
    }

    /* End of SET CDT_ENDERECO_COM */

    function setCDT_DS_INF_COMPLEMENTAR($value) {
        $this->CDT_DS_INF_COMPLEMENTAR = $value;
    }

    /* End of SET CDT_DS_INF_COMPLEMENTAR */
}

?>
