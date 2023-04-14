<?php

/**
 * tb_cfu_configuracao_usuario class
 * This class manipulates the table ConfiguracaoUsuario
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * Modificado por      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 27/08/2013
 * */
require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/NegocioException.php";
require_once $CFG->rpasta . "/persistencia/ConexaoMysql.php";
require_once $CFG->rpasta . "/negocio/NGUtil.php";
require_once $CFG->rpasta . "/negocio/Usuario.php";

class ConfiguracaoUsuario {

    private $CFU_ID_CONFIGURACAO;
    private $USR_ID_USUARIO;
    private $CFU_QT_REGISTROS_PAG;
    private $CFU_FL_ACOMP_PROCESSO;
    private $CFU_FL_SALVAR_FILTRO;
    private $CFU_FL_ACOMP_ADMINISTRADOR;

    /* Construtor padrão da classe */

    public function __construct($CFU_ID_CONFIGURACAO, $USR_ID_USUARIO, $CFU_QT_REGISTROS_PAG, $CFU_FL_ACOMP_PROCESSO, $CFU_FL_SALVAR_FILTRO = NULL, $CFU_FL_ACOMP_ADMINISTRADOR = NULL) {
        $this->CFU_ID_CONFIGURACAO = $CFU_ID_CONFIGURACAO;
        $this->USR_ID_USUARIO = $USR_ID_USUARIO;
        $this->CFU_QT_REGISTROS_PAG = $CFU_QT_REGISTROS_PAG;
        $this->CFU_FL_ACOMP_PROCESSO = $CFU_FL_ACOMP_PROCESSO;
        $this->CFU_FL_SALVAR_FILTRO = $CFU_FL_SALVAR_FILTRO;
        $this->CFU_FL_ACOMP_ADMINISTRADOR = $CFU_FL_ACOMP_ADMINISTRADOR;
    }

    public static function buscarConfiguracaoPorUsuario($idUsuario) {
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select CFU_ID_CONFIGURACAO as idConfiguracao
                    , USR_ID_USUARIO as idUsuario
                    , CFU_QT_REGISTROS_PAG as qtRegistrosPag
                    , CFU_FL_ACOMP_PROCESSO as flAcompProcesso
                    , CFU_FL_SALVAR_FILTRO as flSalvarFiltro
                    , CFU_FL_ACOMP_ADMINISTRADOR
                    from tb_cfu_configuracao_usuario
                    where USR_ID_USUARIO = '$idUsuario'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                throw new NegocioException("Configuração não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $confRet = new ConfiguracaoUsuario($dados['idConfiguracao'], $dados['idUsuario'], $dados['qtRegistrosPag'], $dados['flAcompProcesso'], $dados['flSalvarFiltro'], $dados['CFU_FL_ACOMP_ADMINISTRADOR']);
            return $confRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar configuração.", $e);
        }
    }

    public function editarConfiguracao() {
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // Tratando campos
            $usuario = Usuario::buscarUsuarioPorId($this->USR_ID_USUARIO);
            if ($usuario->getUSR_TP_USUARIO() != Usuario::$USUARIO_CANDIDATO) {
                $flAcompProc = 'NULL';
            } else {
                $flAcompProc = NGUtil::trataCampoStrParaBD($this->CFU_FL_ACOMP_PROCESSO);
            }
            $this->CFU_FL_SALVAR_FILTRO = NGUtil::trataCampoStrParaBD($this->CFU_FL_SALVAR_FILTRO);
            $this->CFU_FL_ACOMP_ADMINISTRADOR = NGUtil::trataCampoStrParaBD($this->CFU_FL_ACOMP_ADMINISTRADOR);


            $sql = "update tb_cfu_configuracao_usuario
                    set CFU_QT_REGISTROS_PAG = '$this->CFU_QT_REGISTROS_PAG'
                    , CFU_FL_ACOMP_PROCESSO = $flAcompProc
                    , CFU_FL_SALVAR_FILTRO = $this->CFU_FL_SALVAR_FILTRO
                    , CFU_FL_ACOMP_ADMINISTRADOR = $this->CFU_FL_ACOMP_ADMINISTRADOR
                    where CFU_ID_CONFIGURACAO = '$this->CFU_ID_CONFIGURACAO'";

            //executando no banco
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao editar configuração.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getCFU_ID_CONFIGURACAO() {
        return $this->CFU_ID_CONFIGURACAO;
    }

    /* End of get CFU_ID_CONFIGURACAO */

    function getUSR_ID_USUARIO() {
        return $this->USR_ID_USUARIO;
    }

    /* End of get USR_ID_USUARIO */

    function getCFU_QT_REGISTROS_PAG() {
        return $this->CFU_QT_REGISTROS_PAG;
    }

    /* End of get CFU_QT_REGISTROS_PAG */

    function getCFU_FL_ACOMP_PROCESSO() {
        if (Util::vazioNulo($this->CFU_FL_ACOMP_PROCESSO)) {
            return FLAG_BD_NAO;
        }
        return $this->CFU_FL_ACOMP_PROCESSO;
    }

    /* End of get CFU_FL_ACOMP_PROCESSO */

    function getCFU_FL_SALVAR_FILTRO() {
        if (Util::vazioNulo($this->CFU_FL_SALVAR_FILTRO)) {
            return FLAG_BD_NAO;
        }
        return $this->CFU_FL_SALVAR_FILTRO;
    }

    function getCFU_FL_ACOMP_ADMINISTRADOR() {
        if (Util::vazioNulo($this->CFU_FL_ACOMP_ADMINISTRADOR)) {
            return FLAG_BD_NAO;
        }
        return $this->CFU_FL_ACOMP_ADMINISTRADOR;
    }

    /* SET FIELDS FROM TABLE */

    function setCFU_ID_CONFIGURACAO($value) {
        $this->CFU_ID_CONFIGURACAO = $value;
    }

    /* End of SET CFU_ID_CONFIGURACAO */

    function setUSR_ID_USUARIO($value) {
        $this->USR_ID_USUARIO = $value;
    }

    /* End of SET USR_ID_USUARIO */

    function setCFU_QT_REGISTROS_PAG($value) {
        $this->CFU_QT_REGISTROS_PAG = $value;
    }

    /* End of SET CFU_QT_REGISTROS_PAG */

    function setCFU_FL_ACOMP_PROCESSO($value) {
        $this->CFU_FL_ACOMP_PROCESSO = $value;
    }

    /* End of SET CFU_FL_ACOMP_PROCESSO */
}

?>
