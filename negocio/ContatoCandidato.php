<?php

/**
 * tb_ctc_contato_candidato class
 * This class manipulates the table ContatoCandidato
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
require_once $CFG->rpasta . "/negocio/NGUtil.php";

class ContatoCandidato {

    private $CTC_ID_CONTATO_CDT;
    private $CTC_NR_TEL_RES;
    private $CTC_NR_TEL_COM;
    private $CTC_NR_CELULAR;
    private $CTC_NR_FAX;
    private $CTC_EMAIL_CONTATO;
    private $CTC_EMAIL_VALIDADO;

    /* Construtor padrão da classe */

    public function __construct($CTC_ID_CONTATO_CDT, $CTC_NR_TEL_RES, $CTC_NR_TEL_COM, $CTC_NR_CELULAR, $CTC_NR_FAX = NULL, $CTC_EMAIL_CONTATO = NULL, $CTC_EMAIL_VALIDADO = NULL) {
        $this->CTC_ID_CONTATO_CDT = $CTC_ID_CONTATO_CDT;
        $this->CTC_NR_TEL_RES = $CTC_NR_TEL_RES;
        $this->CTC_NR_TEL_COM = $CTC_NR_TEL_COM;
        $this->CTC_NR_CELULAR = $CTC_NR_CELULAR;
        $this->CTC_NR_FAX = $CTC_NR_FAX;
        $this->CTC_EMAIL_CONTATO = $CTC_EMAIL_CONTATO;
        $this->CTC_EMAIL_VALIDADO = $CTC_EMAIL_VALIDADO;
    }

    /**
     * Diz se o usuario preencheu seu contato
     * Base para resposta: Campo contato preenchido
     * @param int $idUsuario
     * @return boolean
     */
    public static function preencheuContato($idUsuario) {
        try {
            // recuperando conexao
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = "select 
                        count(*) as qt
                    from
                        tb_cdt_candidato
                    where
                        USR_ID_USUARIO = '$idUsuario'
                            and CTC_ID_CONTATO_CDT IS NOT NULL";

            // executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("qt", $resp) == 1;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar preenchimento de contato.", $e);
        }
    }

    /**
     * 
     * @param ContatoCandidato $objContatoCand
     * @return string
     */
    public static function getStringCriacaoContatoCand($objContatoCand, $idUsuario = NULL) {
        // validando email de contato
        if (!Usuario::validarEmailAlternativo($objContatoCand->CTC_EMAIL_CONTATO, $idUsuario)) {
            throw new NegocioException("Email de contato já cadastrado.");
        }

        //convertendo dados para formato do banco
        $objContatoCand->CTC_NR_CELULAR = NGUtil::trataCampoStrParaBD($objContatoCand->CTC_NR_CELULAR);
        $objContatoCand->CTC_NR_FAX = NGUtil::trataCampoStrParaBD($objContatoCand->CTC_NR_FAX);
        $objContatoCand->CTC_NR_TEL_RES = NGUtil::trataCampoStrParaBD($objContatoCand->CTC_NR_TEL_RES);
        $objContatoCand->CTC_NR_TEL_COM = NGUtil::trataCampoStrParaBD($objContatoCand->CTC_NR_TEL_COM);
        $objContatoCand->CTC_EMAIL_CONTATO = NGUtil::trataCampoStrParaBD($objContatoCand->CTC_EMAIL_CONTATO);


        $ret = "INSERT INTO `tb_ctc_contato_candidato` 
                (`CTC_NR_TEL_RES`, `CTC_NR_TEL_COM`,
                 `CTC_NR_CELULAR`, `CTC_NR_FAX`, `CTC_EMAIL_CONTATO`) 
                VALUES ($objContatoCand->CTC_NR_TEL_RES, $objContatoCand->CTC_NR_TEL_COM,
                    $objContatoCand->CTC_NR_CELULAR, $objContatoCand->CTC_NR_FAX,
                        $objContatoCand->CTC_EMAIL_CONTATO)";
        return $ret;
    }

    public function getNrCelularMascarado() {
        if (!Util::vazioNulo($this->CTC_NR_CELULAR)) {
            if (strlen($this->CTC_NR_CELULAR) == 10) {
                return adicionarMascara("(##) ####-####", $this->CTC_NR_CELULAR);
            }
            return adicionarMascara("(##) #####-####", $this->CTC_NR_CELULAR);
        } else {
            return Util::$STR_CAMPO_VAZIO;
        }
        return NULL;
    }

    public static function getTelFaxMascarado($telFax) {
        if (!Util::vazioNulo($telFax)) {
            return adicionarMascara("(##) ####-####", $telFax);
        } else {
            return Util::$STR_CAMPO_VAZIO;
        }
    }

    public static function buscarContatoPorIdUsuario($idUsuario) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    CTC_ID_CONTATO_CDT,
                    CTC_NR_TEL_RES,
                    CTC_NR_TEL_COM,
                    CTC_NR_CELULAR,
                    CTC_NR_FAX,
                    CTC_EMAIL_CONTATO,
                    CTC_EMAIL_VALIDADO
                from
                    tb_ctc_contato_candidato
                where
                    CTC_ID_CONTATO_CDT = (select 
                            CTC_ID_CONTATO_CDT
                        from
                            tb_cdt_candidato
                        where
                            USR_ID_USUARIO = '$idUsuario')";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                //criando ojeto vazio e retornando
                $contatoRet = new ContatoCandidato("", "", "", "", "", "");
                return $contatoRet;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $contatoRet = new ContatoCandidato($dados['CTC_ID_CONTATO_CDT'], $dados['CTC_NR_TEL_RES'], $dados['CTC_NR_TEL_COM'], $dados['CTC_NR_CELULAR'], $dados['CTC_NR_FAX'], $dados['CTC_EMAIL_CONTATO'], $dados['CTC_EMAIL_VALIDADO']);
            return $contatoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar contato do usuário.", $e);
        }
    }

    public static function buscarContatoPorIdCand($idCandidato) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    CTC_ID_CONTATO_CDT,
                    CTC_NR_TEL_RES,
                    CTC_NR_TEL_COM,
                    CTC_NR_CELULAR,
                    CTC_NR_FAX,
                    CTC_EMAIL_CONTATO,
                    CTC_EMAIL_VALIDADO
                from
                    tb_ctc_contato_candidato
                where
                    CTC_ID_CONTATO_CDT = (select 
                            CTC_ID_CONTATO_CDT
                        from
                            tb_cdt_candidato
                        where
                            CDT_ID_CANDIDATO = '$idCandidato')";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                //criando ojeto vazio e retornando
                $contatoRet = new ContatoCandidato("", "", "", "", "", "");
                return $contatoRet;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $contatoRet = new ContatoCandidato($dados['CTC_ID_CONTATO_CDT'], $dados['CTC_NR_TEL_RES'], $dados['CTC_NR_TEL_COM'], $dados['CTC_NR_CELULAR'], $dados['CTC_NR_FAX'], $dados['CTC_EMAIL_CONTATO'], $dados['CTC_EMAIL_VALIDADO']);
            return $contatoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar contato do usuário.", $e);
        }
    }

    public function editarContatoCandidato($idUsuario) {
        try {

            //recuperando conexao com BD
            $conexao = NGUtil::getConexao();

            $arrayCmds = array();

            // tentando recuperar contato do BD
            $ctcBanco = buscarContatoCandPorIdUsuarioCT($idUsuario);

            // caso de criaçao
            if ($ctcBanco->isVazio()) {
                // vetor principal
                $vetPrincipal = array();
                $vetPrincipal [] = ContatoCandidato::getStringCriacaoContatoCand($this, $idUsuario);

                // informando candidato
                $vetDep = array();
                $vetDep [] = Candidato::getStringApontaContato($idUsuario, ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE);
            } else {
                // caso de atualizaçao
                $arrayCmds [] = $this->getStringAtualizacao($ctcBanco->CTC_ID_CONTATO_CDT, $idUsuario);
            }

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);
            $arrayCmds [] = $sqlAtualizaData;

            // persistindo 
            if (isset($vetPrincipal)) {
                $conexao->execTransacoesDepsComComplemento($vetPrincipal, $vetDep, $arrayCmds);
            } else {
                $conexao->execTransacaoArray($arrayCmds);
            }

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar contato do usuário.", $e);
        }
    }

    private function isVazio() {
        return Util::vazioNulo($this->CTC_NR_TEL_RES) && Util::vazioNulo($this->CTC_NR_TEL_COM) && Util::vazioNulo($this->CTC_NR_CELULAR) && Util::vazioNulo($this->CTC_NR_FAX) && Util::vazioNulo($this->CTC_EMAIL_CONTATO);
    }

    private function getStringAtualizacao($idContatoCand, $idUsuario) {
        // validando email de contato
        if (!Usuario::validarEmailAlternativo($this->CTC_EMAIL_CONTATO, $idUsuario)) {
            throw new NegocioException("Email de contato já cadastrado.");
        }

        $sql = "update tb_ctc_contato_candidato set";
        $sql .= " `CTC_NR_TEL_RES` = " . NGUtil::trataCampoStrParaBD($this->CTC_NR_TEL_RES);
        $sql .= " , `CTC_NR_TEL_COM` = " . NGUtil::trataCampoStrParaBD($this->CTC_NR_TEL_COM);
        $sql .= " , `CTC_NR_CELULAR` = " . NGUtil::trataCampoStrParaBD($this->CTC_NR_CELULAR);
        $sql .= " , `CTC_NR_FAX` = " . NGUtil::trataCampoStrParaBD($this->CTC_NR_FAX);
        $sql .= " , `CTC_EMAIL_CONTATO` = " . NGUtil::trataCampoStrParaBD($this->CTC_EMAIL_CONTATO);
        $sql .= " where `CTC_ID_CONTATO_CDT` = '$idContatoCand'";

        return $sql;
    }

    public static function getStringAtualizacaoEmailAlt($dsEmailAlternativo, $idUsuario) {
        if (!Usuario::validarEmailAlternativo($dsEmailAlternativo, $idUsuario)) {
            throw new NegocioException("Email alternativo já cadastrado.");
        }
        $dsEmailAlternativo = NGUtil::trataCampoStrParaBD($dsEmailAlternativo);
        $ret = "update tb_ctc_contato_candidato
                 set `CTC_EMAIL_CONTATO` = $dsEmailAlternativo where CTC_ID_CONTATO_CDT = (select CTC_ID_CONTATO_CDT from tb_cdt_candidato where USR_ID_USUARIO = '$idUsuario')";

        return $ret;
    }

    /* GET FIELDS FROM TABLE */

    function getCTC_ID_CONTATO_CDT() {
        return $this->CTC_ID_CONTATO_CDT;
    }

    /* End of get CTC_ID_CONTATO_CDT */

    function getCTC_NR_TEL_RES() {
        return $this->CTC_NR_TEL_RES;
    }

    /* End of get CTC_NR_TEL_RES */

    function getCTC_NR_TEL_COM() {
        return $this->CTC_NR_TEL_COM;
    }

    /* End of get CTC_NR_TEL_COM */

    function getCTC_NR_CELULAR() {
        return $this->CTC_NR_CELULAR;
    }

    /* End of get CTC_NR_CELULAR */

    function getCTC_NR_FAX() {
        return $this->CTC_NR_FAX;
    }

    /* End of get CTC_NR_FAX */

    function getCTC_EMAIL_CONTATO($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->CTC_EMAIL_CONTATO)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->CTC_EMAIL_CONTATO;
    }

    /* End of get CTC_EMAIL_CONTATO */

    function getCTC_EMAIL_VALIDADO() {
        return $this->CTC_EMAIL_VALIDADO;
    }

    /* End of get CTC_EMAIL_VALIDADO */



    /* SET FIELDS FROM TABLE */

    function setCTC_ID_CONTATO_CDT($value) {
        $this->CTC_ID_CONTATO_CDT = $value;
    }

    /* End of SET CTC_ID_CONTATO_CDT */

    function setCTC_NR_TEL_RES($value) {
        $this->CTC_NR_TEL_RES = $value;
    }

    /* End of SET CTC_NR_TEL_RES */

    function setCTC_NR_TEL_COM($value) {
        $this->CTC_NR_TEL_COM = $value;
    }

    /* End of SET CTC_NR_TEL_COM */

    function setCTC_NR_CELULAR($value) {
        $this->CTC_NR_CELULAR = $value;
    }

    /* End of SET CTC_NR_CELULAR */

    function setCTC_NR_FAX($value) {
        $this->CTC_NR_FAX = $value;
    }

    /* End of SET CTC_NR_FAX */

    function setCTC_EMAIL_CONTATO($value) {
        $this->CTC_EMAIL_CONTATO = $value;
    }

    /* End of SET CTC_EMAIL_CONTATO */

    function setCTC_EMAIL_VALIDADO($value) {
        $this->CTC_EMAIL_VALIDADO = $value;
    }

    /* End of SET CTC_EMAIL_VALIDADO */
}

?>
