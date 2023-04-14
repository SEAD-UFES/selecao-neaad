<?php

/**
 * tb_dcu_dado_curriculo class
 * This class manipulates the table DadoCurriculo
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 12/05/2014
 * */
class DadoCurriculo {

    private $DCU_ID_DADO_CURRICULO;
    private $CDT_ID_CANDIDATO;
    private $DCU_DS_URL_LATTES;
    private $DCU_LOG_DT_ATUALIZACAO;

    /* Construtor padrão da classe */

    public function __construct($DCU_ID_DADO_CURRICULO, $CDT_ID_CANDIDATO, $DCU_DS_URL_LATTES, $DCU_LOG_DT_ATUALIZACAO = NULL) {
        $this->DCU_ID_DADO_CURRICULO = $DCU_ID_DADO_CURRICULO;
        $this->CDT_ID_CANDIDATO = $CDT_ID_CANDIDATO;
        $this->DCU_DS_URL_LATTES = $DCU_DS_URL_LATTES;
        $this->DCU_LOG_DT_ATUALIZACAO = $DCU_LOG_DT_ATUALIZACAO;
    }

    /**
     * Busca dado curriculo por usuario
     * @param int $idUsuario
     * @return \DadoCurriculo
     * @throws NegocioException
     */
    public static function buscarDadoCurriculoPorUsu($idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        DCU_ID_DADO_CURRICULO,
                        CDT_ID_CANDIDATO,
                        DCU_DS_URL_LATTES,
                        DCU_LOG_DT_ATUALIZACAO
                    from
                        tb_dcu_dado_curriculo
                        where CDT_ID_CANDIDATO = (select CDT_ID_CANDIDATO from tb_cdt_candidato 
                        where USR_ID_USUARIO = '$idUsuario')";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                // retornando nulo
                return NULL;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $dadoCurriculoTemp = new DadoCurriculo($dados['DCU_ID_DADO_CURRICULO'], $dados['CDT_ID_CANDIDATO'], $dados['DCU_DS_URL_LATTES'], $dados['DCU_LOG_DT_ATUALIZACAO']);

            return $dadoCurriculoTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar dados do currículo do usuário.", $e);
        }
    }

    /**
     * Busca link do lattes do usuario especificado
     * 
     * 
     * @param int $idUsuario
     * @return array Array com as chaves: 
     * - situacao (Tudo OK, usado para transmissão AJAX)
     * - val (Diz se o link retornado é válido)
     * - link (Link lattes, ou mensagem de aviso)
     * 
     * @throws NegocioException
     */
    public static function buscarLinkLattesPorUsu($idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select DCU_DS_URL_LATTES
                    from
                        tb_dcu_dado_curriculo
                        where CDT_ID_CANDIDATO = (select CDT_ID_CANDIDATO from tb_cdt_candidato 
                        where USR_ID_USUARIO = '$idUsuario')";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retorna string padrão
                return array("situacao" => TRUE, "val" => FALSE, "link" => DadoCurriculo::getHtmlMsgLattesNaoReg());
            }

            //recuperando dados
            $link = ConexaoMysql::getResult("DCU_DS_URL_LATTES", $resp);

            // sem link
            if (Util::vazioNulo($link)) {
                return array("situacao" => TRUE, "val" => FALSE, "link" => DadoCurriculo::getHtmlMsgLattesNaoReg());
            }

            return array("situacao" => TRUE, "val" => TRUE, "link" => $link);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar link lattes do usuário.", $e);
        }
    }

    /**
     * Busca link do lattes do usuario especificado
     * 
     * 
     * @param int $idCandidato
     * @param boolean $retornoHTML Diz se o retorno deverá ser HTML. Padrão: TRUE
     * 
     * @return array Array com as chaves: 
     * - val (Diz se o link retornado é válido)
     * - link (Link lattes, ou mensagem de aviso)
     * 
     * @throws NegocioException
     */
    public static function buscarLinkLattesPorCand($idCandidato, $retornoHTML = TRUE) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select DCU_DS_URL_LATTES
                    from tb_dcu_dado_curriculo
                    where CDT_ID_CANDIDATO = '$idCandidato'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retorna string padrão
                return array("val" => FALSE, "link" => DadoCurriculo::getHtmlMsgLattesNaoReg($retornoHTML));
            }

            //recuperando dados
            $link = ConexaoMysql::getResult("DCU_DS_URL_LATTES", $resp);

            // sem link
            if (Util::vazioNulo($link)) {
                return array("val" => FALSE, "link" => DadoCurriculo::getHtmlMsgLattesNaoReg($retornoHTML));
            }

            return array("val" => TRUE, "link" => $link);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar link lattes do candidato.", $e);
        }
    }

    /**
     * Salva link do lattes do usuario especificado
     * @param int $idUsuario
     * @param string $linkLattes
     * @return string
     * @throws NegocioException
     */
    public static function salvarLinkLattesPorUsu($idUsuario, $linkLattes) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // verificando se ja existe objeto no bd
            $dadoCurriculo = self::buscarDadoCurriculoPorUsu($idUsuario);

            if ($dadoCurriculo == NULL) {
                // sql de criacao
                $sql = "insert into tb_dcu_dado_curriculo (CDT_ID_CANDIDATO, DCU_DS_URL_LATTES, DCU_LOG_DT_ATUALIZACAO)
                        values ((select CDT_ID_CANDIDATO from tb_cdt_candidato 
                        where USR_ID_USUARIO = '$idUsuario'),'$linkLattes', now())";
            } else {
                // sql de edicao
                $sql = "update tb_dcu_dado_curriculo
                         set DCU_DS_URL_LATTES = '$linkLattes',
                         DCU_LOG_DT_ATUALIZACAO = now()
                        where CDT_ID_CANDIDATO = (select CDT_ID_CANDIDATO from tb_cdt_candidato 
                        where USR_ID_USUARIO = '$idUsuario')";
            }

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //executando sql
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // tudo certo
            return true;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao salvar link lattes do candidato.", $e);
        }
    }

    public static function getHtmlMsgLattesNaoReg($html = TRUE) {
        $msg = "Não informado";
        if ($html) {
            return "<i>$msg</i>";
        } else {
            return $msg;
        }
    }

    /* GET FIELDS FROM TABLE */

    function getDCU_ID_DADO_CURRICULO() {
        return $this->DCU_ID_DADO_CURRICULO;
    }

    /* End of get DCU_ID_DADO_CURRICULO */

    function getCDT_ID_CANDIDATO() {
        return $this->CDT_ID_CANDIDATO;
    }

    /* End of get CDT_ID_CANDIDATO */

    function getDCU_DS_URL_LATTES() {
        return $this->DCU_DS_URL_LATTES;
    }

    /* End of get DCU_DS_URL_LATTES */

    function getDCU_LOG_DT_ATUALIZACAO() {
        return $this->DCU_LOG_DT_ATUALIZACAO;
    }

    /* End of get DCU_LOG_DT_ATUALIZACAO */



    /* SET FIELDS FROM TABLE */

    function setDCU_ID_DADO_CURRICULO($value) {
        $this->DCU_ID_DADO_CURRICULO = $value;
    }

    /* End of SET DCU_ID_DADO_CURRICULO */

    function setCDT_ID_CANDIDATO($value) {
        $this->CDT_ID_CANDIDATO = $value;
    }

    /* End of SET CDT_ID_CANDIDATO */

    function setDCU_DS_URL_LATTES($value) {
        $this->DCU_DS_URL_LATTES = $value;
    }

    /* End of SET DCU_DS_URL_LATTES */

    function setDCU_LOG_DT_ATUALIZACAO($value) {
        $this->DCU_LOG_DT_ATUALIZACAO = $value;
    }

    /* End of SET DCU_LOG_DT_ATUALIZACAO */
}

?>
