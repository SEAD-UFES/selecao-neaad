<?php

/**
 * tb_end_endereco class
 * This class manipulates the table Endereco
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
require_once $CFG->rpasta . "/util/Util.php";

class Endereco {

    private $END_ID_ENDERECO;
    private $END_DS_ENDERECO;
    private $END_NM_LOGRADOURO;
    private $END_NR_NUMERO;
    private $END_NM_BAIRRO;
    private $END_NM_CIDADE;
    private $CID_ID_CIDADE;
    private $EST_ID_UF;
    private $END_NR_CEP;
    private $END_DS_COMPLEMENTO;
    // campos herdados
    private $CID_NM_CIDADE;
    public static $DESC_END_RESIDENCIAL = "Endereço Residencial";
    public static $DESC_END_COMERCIAL = "Endereço Comercial";
    public static $TIPO_RESIDENCIAL = "CDT_ENDERECO_RES";
    public static $TIPO_COMERCIAL = "CDT_ENDERECO_COM";

    /* Construtor padrão da classe */

    public function __construct($END_ID_ENDERECO, $END_DS_ENDERECO, $END_NM_LOGRADOURO, $END_NR_NUMERO, $END_NM_BAIRRO, $END_NM_CIDADE, $CID_ID_CIDADE, $EST_ID_UF, $END_NR_CEP, $END_DS_COMPLEMENTO) {
        $this->END_ID_ENDERECO = $END_ID_ENDERECO;
        $this->END_DS_ENDERECO = $END_DS_ENDERECO;
        $this->END_NM_LOGRADOURO = $END_NM_LOGRADOURO;
        $this->END_NR_NUMERO = $END_NR_NUMERO;
        $this->END_NM_BAIRRO = $END_NM_BAIRRO;
        $this->END_NM_CIDADE = $END_NM_CIDADE;
        $this->CID_ID_CIDADE = $CID_ID_CIDADE;
        $this->EST_ID_UF = $EST_ID_UF;
        $this->END_NR_CEP = $END_NR_CEP;
        $this->END_DS_COMPLEMENTO = substr($END_DS_COMPLEMENTO, 0, 250); // trunca o complemento para o limite maximo do banco
    }

    public function getNomeCidade() {
        if (Util::vazioNulo($this->END_NM_CIDADE)) {
            return $this->CID_NM_CIDADE;
        }
        return $this->END_NM_CIDADE;
    }

    public function getStrEndereco($sepLinha = "<br/>") {
        if (Util::vazioNulo($this->END_NR_CEP)) {
            return "<div class='callout callout-info'>Endereço não informado</div>";
        }
        $str = $this->END_NM_LOGRADOURO;
        $str .=!Util::vazioNulo($this->END_NR_NUMERO) ? ', ' . $this->END_NR_NUMERO : " s/n";
        $str .= ", " . $this->END_NM_BAIRRO;
        $str .= " - " . $this->getNomeCidade() . "/" . $this->EST_ID_UF;
        $str .= " - CEP: " . adicionarMascara("#####-###", $this->END_NR_CEP);
        $str .=!Util::vazioNulo($this->END_DS_COMPLEMENTO) ? "$sepLinha" . $this->END_DS_COMPLEMENTO : "";
        return $str;
    }

    public function __toString() {
        return $this->getStrEndereco("\n");
    }

    /**
     * Diz se o usuario preencheu seu endereço
     * Base para resposta: Endereço residencial preenchido
     * @param int $idUsuario
     * @return boolean
     */
    public static function preencheuEndereco($idUsuario) {
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
                            and CDT_ENDERECO_RES IS NOT NULL";

            // executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("qt", $resp) == 1;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar preenchimento do endereço.", $e);
        }
    }

    /**
     * 
     * @param int $idUsuario
     * @param string $tipo - Um dos tipos especificados nesta classe
     * @return \Endereco
     * @throws NegocioException
     */
    public static function buscarEnderecoPorIdUsuario($idUsuario, $tipo) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    END_ID_ENDERECO,
                    END_DS_ENDERECO,
                    END_NM_LOGRADOURO,
                    END_NR_NUMERO,
                    END_NM_BAIRRO,
                    end.CID_ID_CIDADE,
                    CID_NM_CIDADE,
                    EST_ID_UF,
                    END_NR_CEP,
                    END_DS_COMPLEMENTO
                from
                    tb_end_endereco end
                    join tb_cid_cidade cid on cid.CID_ID_CIDADE = end.CID_ID_CIDADE
                where END_ID_ENDERECO = (select $tipo from tb_cdt_candidato
                where USR_ID_USUARIO = '$idUsuario')";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                //criando ojeto vazio e retornando
                $enderecoRet = new Endereco("", "", "", "", "", "", "", "", "", "");
                return $enderecoRet;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $enderecoRet = new Endereco($dados['END_ID_ENDERECO'], $dados['END_DS_ENDERECO'], $dados['END_NM_LOGRADOURO'], $dados['END_NR_NUMERO'], $dados['END_NM_BAIRRO'], NULL, $dados['CID_ID_CIDADE'], $dados['EST_ID_UF'], $dados['END_NR_CEP'], $dados['END_DS_COMPLEMENTO']);
            // setando campos herdados
            $enderecoRet->CID_NM_CIDADE = $dados['CID_NM_CIDADE'];

            return $enderecoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar endereço do usuário.", $e);
        }
    }

    /**
     * 
     * @param int $idCandidato
     * @param string $tipo - Um dos tipos especificados nesta classe
     * @return \Endereco
     * @throws NegocioException
     */
    public static function buscarEnderecoPorIdCand($idCandidato, $tipo) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    END_ID_ENDERECO,
                    END_DS_ENDERECO,
                    END_NM_LOGRADOURO,
                    END_NR_NUMERO,
                    END_NM_BAIRRO,
                    end.CID_ID_CIDADE,
                    CID_NM_CIDADE,
                    EST_ID_UF,
                    END_NR_CEP,
                    END_DS_COMPLEMENTO
                from
                    tb_end_endereco end
                    join tb_cid_cidade cid on cid.CID_ID_CIDADE = end.CID_ID_CIDADE
                where END_ID_ENDERECO = (select $tipo from tb_cdt_candidato
                where CDT_ID_CANDIDATO = '$idCandidato')";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                //criando ojeto vazio e retornando
                $enderecoRet = new Endereco("", "", "", "", "", "", "", "", "", "");
                return $enderecoRet;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $enderecoRet = new Endereco($dados['END_ID_ENDERECO'], $dados['END_DS_ENDERECO'], $dados['END_NM_LOGRADOURO'], $dados['END_NR_NUMERO'], $dados['END_NM_BAIRRO'], NULL, $dados['CID_ID_CIDADE'], $dados['EST_ID_UF'], $dados['END_NR_CEP'], $dados['END_DS_COMPLEMENTO']);
            // setando campos herdados
            $enderecoRet->CID_NM_CIDADE = $dados['CID_NM_CIDADE'];

            return $enderecoRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar endereço do usuário.", $e);
        }
    }

    /**
     * 
     * @param Endereco $objEnd
     */
    private static function trataDadosBD($objEnd) {
        // caso de campos vazios
        if (Util::vazioNulo($objEnd->END_NM_LOGRADOURO) || Util::vazioNulo($objEnd->END_NM_BAIRRO)) {
            new Mensagem("Desculpe. Não foi possível atualizar seu endereço. Por favor, preencha os campos 'Logradouro' e 'Bairro'.", Mensagem::$MENSAGEM_ERRO);
        }
        $objEnd->END_DS_ENDERECO = NGUtil::trataCampoStrParaBD($objEnd->END_DS_ENDERECO);
        $objEnd->END_NR_NUMERO = NGUtil::trataCampoStrParaBD($objEnd->END_NR_NUMERO);
        $objEnd->CID_ID_CIDADE = NGUtil::trataCampoStrParaBD($objEnd->CID_ID_CIDADE);
        $objEnd->END_NM_CIDADE = NGUtil::trataCampoStrParaBD($objEnd->END_NM_CIDADE);
        $objEnd->END_NR_CEP = NGUtil::trataCampoStrParaBD($objEnd->END_NR_CEP);
        $objEnd->END_DS_COMPLEMENTO = NGUtil::trataCampoStrParaBD($objEnd->END_DS_COMPLEMENTO);

        // forçando capitalize
        $objEnd->END_NM_LOGRADOURO = str_capitalize_forcado($objEnd->END_NM_LOGRADOURO);
        $objEnd->END_NM_BAIRRO = str_capitalize_forcado($objEnd->END_NM_BAIRRO);
    }

    /**
     * 
     * @param Endereco $objEnd
     * @return string
     */
    public static function getStringCriacaoEnd($objEnd) {
        // verificando necessidade de criação
        if (!self::objHabilitado($objEnd)) {
            return NULL;
        }

        // tratando campos
        Endereco::trataDadosBD($objEnd);

        $ret = "call sp_end_endereco_insert($objEnd->END_DS_ENDERECO, '$objEnd->END_NM_LOGRADOURO', $objEnd->END_NR_NUMERO, '$objEnd->END_NM_BAIRRO', $objEnd->END_NM_CIDADE, $objEnd->CID_ID_CIDADE, '$objEnd->EST_ID_UF', $objEnd->END_NR_CEP, $objEnd->END_DS_COMPLEMENTO)";
        return $ret;
    }

    /**
     * 
     * @param Endereco $objEnd
     */
    private static function objHabilitado($objEnd) {
        return !Util::vazioNulo($objEnd->EST_ID_UF) && !Util::vazioNulo($objEnd->END_NR_CEP);
    }

    /**
     * Executa as operaçoes CUD para endereço do usuario
     * 
     * Assume o preenchimento correto dos objetos
     * 
     * @param int $idUsuario
     * @param Endereco $endRes
     * @param Endereco $endCom
     * @throws NegocioException
     */
    public static function salvarEnderecoCandidato($idUsuario, $endRes, $endCom) {
        try {

            //recuperando conexao com BD
            $conexao = NGUtil::getConexao();

            $arrayCmds = array();

            // tentando recuperar endereços do usuario
            $endResBanco = Endereco::buscarEnderecoPorIdUsuario($idUsuario, Endereco::$TIPO_RESIDENCIAL);
            $endComBanco = Endereco::buscarEnderecoPorIdUsuario($idUsuario, Endereco::$TIPO_COMERCIAL);

            // tratando endereço residencial
            // caso exclusao
            if ($endRes->isVazio() && !$endResBanco->isVazio()) {
                // recuperando sql de exclusao
                $arrayCmds [] = Candidato::getStringAnulaEnd($idUsuario, Endereco::$TIPO_RESIDENCIAL);
                $arrayCmds [] = $endResBanco->getStringExclusaoEnd();

                // caso criaçao
            } else if (!$endRes->isVazio() && $endResBanco->isVazio()) {
                if (Util::vazioNulo($endRes->END_DS_ENDERECO)) {
                    $endRes->END_DS_ENDERECO = Endereco::$DESC_END_RESIDENCIAL;
                }
                $cmdDepRes = Candidato::getStringApontaEnd($idUsuario, Endereco::$TIPO_RESIDENCIAL, ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE);
                $cmdCriaRes = Endereco::getStringCriacaoEnd($endRes);
            } else if (!$endRes->isVazio() && !$endResBanco->isVazio()) {
                // caso atualizaçao
                $arrayCmds [] = $endRes->getStringAtualizacao($endResBanco->END_ID_ENDERECO);
            }

            // tratando endereço comercial
            // 
            // caso exclusao
            if ($endCom->isVazio() && !$endComBanco->isVazio()) {
                // recuperando sql de exclusao
                $arrayCmds [] = Candidato::getStringAnulaEnd($idUsuario, Endereco::$TIPO_COMERCIAL);
                $arrayCmds [] = $endComBanco->getStringExclusaoEnd();

                // caso criaçao
            } else if (!$endCom->isVazio() && $endComBanco->isVazio()) {
                if (Util::vazioNulo($endCom->END_DS_ENDERECO)) {
                    $endCom->END_DS_ENDERECO = Endereco::$DESC_END_COMERCIAL;
                }
                $cmdDepCom = Candidato::getStringApontaEnd($idUsuario, Endereco::$TIPO_COMERCIAL, ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE);
                $cmdCriaCom = Endereco::getStringCriacaoEnd($endCom);
            } else if (!$endCom->isVazio() && !$endComBanco->isVazio()) {
                // caso atualizaçao
                $arrayCmds [] = $endCom->getStringAtualizacao($endComBanco->END_ID_ENDERECO);
            }

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);
            $arrayCmds [] = $sqlAtualizaData;


            // tratando da persistencia no banco
            // apenas exclusao ou atualizacao
            if (!isset($cmdDepRes) && !isset($cmdDepCom)) {
                $conexao->execTransacaoArray($arrayCmds);
            } else {
                // envolve alguma criaçao
                // criando array cmd x seu dependente
                $arrayCmdPrincipal = array();
                $arrayCmdDependente = array();
                if (isset($cmdDepRes) && !Util::vazioNulo($cmdCriaRes)) {
                    $arrayCmdPrincipal [] = $cmdCriaRes;
                    $arrayCmdDependente [] = $cmdDepRes;
                }
                if (isset($cmdDepCom) && !Util::vazioNulo($cmdCriaCom)) {
                    $arrayCmdPrincipal [] = $cmdCriaCom;
                    $arrayCmdDependente [] = $cmdDepCom;
                }

                if (count($arrayCmdPrincipal) > 0) {
                    // persistindo 
                    $conexao->execTransacoesDepsComComplemento($arrayCmdPrincipal, $arrayCmdDependente, $arrayCmds);
                }
            }

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar endereço do usuário.", $e);
        }
    }

    private function isVazio() {
        return Util::vazioNulo($this->END_NR_CEP);
    }

    private function getStringExclusaoEnd() {
        $sql = "delete from tb_end_endereco 
                where
                    END_ID_ENDERECO = '$this->END_ID_ENDERECO'";
        return $sql;
    }

    private function getStringAtualizacao($idEndereco) {
        // tratando campos
        Endereco::trataDadosBD($this);

        $ret = "call sp_end_endereco_update('$idEndereco', $this->END_DS_ENDERECO, '$this->END_NM_LOGRADOURO', $this->END_NR_NUMERO, '$this->END_NM_BAIRRO', $this->END_NM_CIDADE, $this->CID_ID_CIDADE, '$this->EST_ID_UF', $this->END_NR_CEP, $this->END_DS_COMPLEMENTO)";

        return $ret;
    }

    /* GET FIELDS FROM TABLE */

    function getEND_ID_ENDERECO() {
        return $this->END_ID_ENDERECO;
    }

    /* End of get END_ID_ENDERECO */

    function getEND_DS_ENDERECO() {
        return $this->END_DS_ENDERECO;
    }

    /* End of get END_DS_ENDERECO */

    function getEND_NM_LOGRADOURO() {
        return $this->END_NM_LOGRADOURO;
    }

    /* End of get END_NM_LOGRADOURO */

    function getEND_NR_NUMERO() {
        return $this->END_NR_NUMERO;
    }

    /* End of get END_NR_NUMERO */

    function getEND_NM_BAIRRO() {
        return $this->END_NM_BAIRRO;
    }

    /* End of get END_NM_BAIRRO */

    function getEND_NM_CIDADE() {
        return $this->END_NM_CIDADE;
    }

    /* End of get END_NM_CIDADE */

    function getCID_ID_CIDADE() {
        return $this->CID_ID_CIDADE;
    }

    /* End of get CID_ID_CIDADE */

    function getEST_ID_UF() {
        return $this->EST_ID_UF;
    }

    /* End of get EST_ID_UF */

    function getEND_NR_CEP() {
        return $this->END_NR_CEP;
    }

    /* End of get END_NR_CEP */

    function getEND_DS_COMPLEMENTO() {
        return $this->END_DS_COMPLEMENTO;
    }

    /* End of get END_DS_COMPLEMENTO */



    /* SET FIELDS FROM TABLE */

    function setEND_ID_ENDERECO($value) {
        $this->END_ID_ENDERECO = $value;
    }

    /* End of SET END_ID_ENDERECO */

    function setEND_DS_ENDERECO($value) {
        $this->END_DS_ENDERECO = $value;
    }

    /* End of SET END_DS_ENDERECO */

    function setEND_NM_LOGRADOURO($value) {
        $this->END_NM_LOGRADOURO = $value;
    }

    /* End of SET END_NM_LOGRADOURO */

    function setEND_NR_NUMERO($value) {
        $this->END_NR_NUMERO = $value;
    }

    /* End of SET END_NR_NUMERO */

    function setEND_NM_BAIRRO($value) {
        $this->END_NM_BAIRRO = $value;
    }

    /* End of SET END_NM_BAIRRO */

    function setEND_NM_CIDADE($value) {
        $this->END_NM_CIDADE = $value;
    }

    /* End of SET END_NM_CIDADE */

    function setCID_ID_CIDADE($value) {
        $this->CID_ID_CIDADE = $value;
    }

    /* End of SET CID_ID_CIDADE */

    function setEST_ID_UF($value) {
        $this->EST_ID_UF = $value;
    }

    /* End of SET EST_ID_UF */

    function setEND_NR_CEP($value) {
        $this->END_NR_CEP = $value;
    }

    /* End of SET END_NR_CEP */

    function setEND_DS_COMPLEMENTO($value) {
        $this->END_DS_COMPLEMENTO = $value;
    }

    /* End of SET END_DS_COMPLEMENTO */
}

?>
