<?php

/**
 * tb_urt_usuario_rastreio class
 * This class manipulates the table UsuarioRastreio
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2015       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 17/03/2015
 * */
class UsuarioRastreio {

    private $URT_ID_RASTREIO;
    private $USR_ID_USUARIO;
    private $URT_DT_RASTREIO;
    private $URT_TP_RASTREIO;
    private $URT_DS_URL_ACESSO;
    private $PRC_ID_PROCESSO_REL;
    private $PCH_ID_CHAMADA_REL;
    private $URT_DS_RASTREIO;
    // tipos de rastreio
    public static $TP_RASTREIO_EDITAL = 'E';
    public static $TP_RASTREIO_INSC_EDITAL = 'I';
    // quantidade máxima de rastreios
    public static $QT_MAX_RASTREIO_EDITAL = 3;

    /* Construtor padrão da classe */

    public function __construct($URT_ID_RASTREIO, $USR_ID_USUARIO, $URT_DT_RASTREIO, $URT_TP_RASTREIO, $URT_DS_URL_ACESSO, $PRC_ID_PROCESSO_REL, $PCH_ID_CHAMADA_REL, $URT_DS_RASTREIO) {
        $this->URT_ID_RASTREIO = $URT_ID_RASTREIO;
        $this->USR_ID_USUARIO = $USR_ID_USUARIO;
        $this->URT_DT_RASTREIO = $URT_DT_RASTREIO;
        $this->URT_TP_RASTREIO = $URT_TP_RASTREIO;
        $this->URT_DS_URL_ACESSO = $URT_DS_URL_ACESSO;
        $this->PRC_ID_PROCESSO_REL = $PRC_ID_PROCESSO_REL;
        $this->PCH_ID_CHAMADA_REL = $PCH_ID_CHAMADA_REL;
        $this->URT_DS_RASTREIO = $URT_DS_RASTREIO;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_urt_usuario_rastreio where PRC_ID_PROCESSO_REL = '$idProcesso'";
    }

    private static function getSqlPadraoBusca() {
        return "SELECT 
                    URT_ID_RASTREIO,
                    USR_ID_USUARIO,
                    DATE_FORMAT(`URT_DT_RASTREIO`, '%d/%m/%Y às %T') AS URT_DT_RASTREIOSTR,
                    URT_TP_RASTREIO,
                    URT_DS_URL_ACESSO,
                    PRC_ID_PROCESSO_REL,
                    PCH_ID_CHAMADA_REL,
                    URT_DS_RASTREIO
                FROM
                    tb_urt_usuario_rastreio";
    }

    public static function buscarRastreioPorFiltro($idUsuario, $idProcesso = NULL, $idChamada = NULL, $tpRastreio = NULL, $inicioDados = NULL, $qtdeDados = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = self::getSqlPadraoBusca();

            // campos obrigatórios 
            $sql .= " where USR_ID_USUARIO = '$idUsuario'";


            // opcionais
            if ($idProcesso != NULL) {
                $sql .= " and PRC_ID_PROCESSO_REL = '$idProcesso' ";
            }

            if ($idChamada != NULL) {
                $sql .= " and PCH_ID_CHAMADA_REL = '$idChamada' ";
            }

            if ($tpRastreio != NULL) {
                $sql .= " and URT_TP_RASTREIO = '$tpRastreio' ";
            }

            // questão de ordenação
            $sql .= " order by URT_DT_RASTREIO DESC";

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
                //retornando array vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar as titulações
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $rastreioTemp = new UsuarioRastreio($dados['URT_ID_RASTREIO'], $dados['USR_ID_USUARIO'], $dados['URT_DT_RASTREIOSTR'], $dados['URT_TP_RASTREIO'], $dados['URT_DS_URL_ACESSO'], $dados['PRC_ID_PROCESSO_REL'], $dados['PCH_ID_CHAMADA_REL'], $dados['URT_DS_RASTREIO']);


                //adicionando no vetor
                $vetRetorno[$i] = $rastreioTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar rastreios.", $e);
        }
    }

    public static function criarRastreioEdital($idUsuario, $idProcesso) {
        try {

            if (Util::vazioNulo($idUsuario)) {
                // nada a fazer;
                return;
            }

            $arrayCmds = array();

            //verificando se já existe rastreio para o edital 
            $idRastreio = self::getIdRastreio($idUsuario, $idProcesso, NULL, self::$TP_RASTREIO_EDITAL);
            if ($idRastreio != NULL) {
                // apenas atualizar a data do rastreio
                $arrayCmds [] = self::sql_atualizaDataRastreio($idRastreio);
            } else {
                // verificando quantidade de rastreio para ver se não ultrapassa o limite
                $qtRastreio = self::contarRastreio($idUsuario, NULL, NULL, self::$TP_RASTREIO_EDITAL);
                if ($qtRastreio >= self::$QT_MAX_RASTREIO_EDITAL) {
                    // recuperando sql para remover rastreio mais velho
                    $arrayCmds [] = self::sql_removeRastreioAntigo($idUsuario, NULL, NULL, self::$TP_RASTREIO_EDITAL);
                }

                // criando novo rastreio
                $rastreio = new UsuarioRastreio(NULL, $idUsuario, NULL, self::$TP_RASTREIO_EDITAL, "visao/processo/consultarProcesso.php?idProcesso=$idProcesso", $idProcesso, NULL, NULL);
                $arrayCmds [] = $rastreio->getSqlCriacao();
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();
            // executando sqls
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao criar rastreio do edital.", $e);
        }
    }

    public static function criarRastreioInscricaoEdital($idUsuario, $idProcesso, $idChamada) {
        try {

            if (Util::vazioNulo($idUsuario)) {
                // nada a fazer;
                return;
            }

            $urlAcesso = "visao/inscricaoProcesso/criarInscProcesso.php?idProcesso=$idProcesso";

            //verificando se já existe rastreio para o usuário 
            $idRastreio = self::getIdRastreio($idUsuario, NULL, NULL, self::$TP_RASTREIO_INSC_EDITAL);

            if ($idRastreio != NULL) {
                // atualizar o rastreio
                $sql = self::getSqlAtualizacao($idRastreio, $idProcesso, $idChamada, $urlAcesso, NULL);
            } else {
                // criando novo rastreio
                $rastreio = new UsuarioRastreio(NULL, $idUsuario, NULL, self::$TP_RASTREIO_INSC_EDITAL, $urlAcesso, $idProcesso, $idChamada, NULL);
                $sql = $rastreio->getSqlCriacao();
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();
            // executando sqls
            $conexao->execSqlSemRetorno($sql);

            // disparando criação de rastreio de edital (manutenção da coerência)
            self::criarRastreioEdital($idUsuario, $idProcesso);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao criar rastreio de inscrição em edital.", $e);
        }
    }

    public static function removerRastreio($idRastreio) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // sql de remoção
            $sql = "delete from tb_urt_usuario_rastreio where URT_ID_RASTREIO = '$idRastreio'";

            // executando sqls
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao remover rastreio.", $e);
        }
    }

    public static function getSqlRemoverRastreioPorFiltro($idUsuario = NULL, $idProcesso = NULL, $idChamada = NULL) {
        // restrição para inpedir a exclusão acidental de todos os rastreios
        if (Util::vazioNulo($idUsuario) && Util::vazioNulo($idProcesso) && Util::vazioNulo($idChamada)) {
            throw new NegocioException("Pelo menos um dos parâmetros de exclusão deve ser não nulo.");
        }

        // sql de remoção
        $sql = "delete from tb_urt_usuario_rastreio ";

        $where = TRUE;

        // parâmetros
        if ($idUsuario != NULL) {
            if ($where) {
                $sql .= " where ";
                $where = false;
                $and = true;
            } else if ($and) {
                $sql .= " and ";
            }
            $sql .= " USR_ID_USUARIO = '$idUsuario' ";
        }

        if ($idProcesso != NULL) {
            if ($where) {
                $sql .= " where ";
                $where = false;
                $and = true;
            } else if ($and) {
                $sql .= " and ";
            }
            $sql .= " PRC_ID_PROCESSO_REL = '$idProcesso' ";
        }

        if ($idChamada != NULL) {
            if ($where) {
                $sql .= " where ";
                $where = false;
                $and = true;
            } else if ($and) {
                $sql .= " and ";
            }
            $sql .= " PCH_ID_CHAMADA_REL = '$idChamada' ";
        }
        return $sql;
    }

    private function getSqlCriacao() {
        $this->USR_ID_USUARIO = NGUtil::trataCampoIntParaBD($this->USR_ID_USUARIO);
        $this->PRC_ID_PROCESSO_REL = NGUtil::trataCampoIntParaBD($this->PRC_ID_PROCESSO_REL);
        $this->PCH_ID_CHAMADA_REL = NGUtil::trataCampoIntParaBD($this->PCH_ID_CHAMADA_REL);

        $this->URT_TP_RASTREIO = NGUtil::trataCampoStrParaBD($this->URT_TP_RASTREIO);
        $this->URT_DS_URL_ACESSO = NGUtil::trataCampoStrParaBD($this->URT_DS_URL_ACESSO);
        $this->URT_DS_RASTREIO = NGUtil::trataCampoStrParaBD($this->URT_DS_RASTREIO);

        return "insert into tb_urt_usuario_rastreio (USR_ID_USUARIO, URT_DT_RASTREIO, URT_TP_RASTREIO, URT_DS_URL_ACESSO, PRC_ID_PROCESSO_REL, PCH_ID_CHAMADA_REL, URT_DS_RASTREIO)
                values ($this->USR_ID_USUARIO, now(), $this->URT_TP_RASTREIO, $this->URT_DS_URL_ACESSO, $this->PRC_ID_PROCESSO_REL, $this->PCH_ID_CHAMADA_REL, $this->URT_DS_RASTREIO)";
    }

    private static function getSqlAtualizacao($idRastreio, $idProcesso, $idChamada, $dsUrlAcesso, $dsRastreio) {
        $idRastreio = NGUtil::trataCampoIntParaBD($idRastreio);

        $idProcesso = NGUtil::trataCampoIntParaBD($idProcesso);
        $idChamada = NGUtil::trataCampoIntParaBD($idChamada);


        $dsUrlAcesso = NGUtil::trataCampoStrParaBD($dsUrlAcesso);
        $dsRastreio = NGUtil::trataCampoStrParaBD($dsRastreio);

        return "update tb_urt_usuario_rastreio set 
                    URT_DT_RASTREIO = now(),
                    URT_DS_URL_ACESSO = $dsUrlAcesso, 
                    PRC_ID_PROCESSO_REL = $idProcesso,
                    PCH_ID_CHAMADA_REL = $idChamada,
                    URT_DS_RASTREIO = $dsRastreio
                where URT_DT_RASTREIO = $idRastreio";
    }

    /**
     * 
     * @param int $idUsuario
     * @param int $idProcesso
     * @param int $idChamada
     * @param char $tpRastreio
     * @return int ID do rastreio em questão ou NULL, caso não tenha sido encontrado.
     * @throws NegocioException
     */
    private static function getIdRastreio($idUsuario, $idProcesso = NULL, $idChamada = NULL, $tpRastreio = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // montando sql
            $sql = "select URT_ID_RASTREIO as id 
                    from tb_urt_usuario_rastreio
                    where USR_ID_USUARIO = '$idUsuario'";

            if ($idProcesso != NULL) {
                $sql .= " and PRC_ID_PROCESSO_REL = '$idProcesso' ";
            }

            if ($idChamada != NULL) {
                $sql .= " and PCH_ID_CHAMADA_REL = '$idChamada' ";
            }

            if ($tpRastreio != NULL) {
                $sql .= " and URT_TP_RASTREIO = '$tpRastreio' ";
            }

            // incluindo limite
            $sql .= " limit 1";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return NULL;
            }
            return ConexaoMysql::getResult("id", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar ID de rastreio.", $e);
        }
    }

    private static function contarRastreio($idUsuario, $idProcesso = NULL, $idChamada = NULL, $tpRastreio = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // montando sql
            $sql = "select count(*) as cont 
                    from tb_urt_usuario_rastreio
                    where USR_ID_USUARIO = '$idUsuario'";


            if ($idProcesso != NULL) {
                $sql .= " and PRC_ID_PROCESSO_REL = '$idProcesso' ";
            }

            if ($idChamada != NULL) {
                $sql .= " and PCH_ID_CHAMADA_REL = '$idChamada' ";
            }

            if ($tpRastreio != NULL) {
                $sql .= " and URT_TP_RASTREIO = '$tpRastreio' ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);
            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar rastreios.", $e);
        }
    }

    private static function sql_atualizaDataRastreio($idRastreio) {
        return "update tb_urt_usuario_rastreio set URT_DT_RASTREIO = now() where URT_ID_RASTREIO = '$idRastreio'";
    }

    public static function getSqlRemoveRastreioPorUsuario($idUsuario) {
        return "delete from tb_urt_usuario_rastreio where USR_ID_USUARIO = '$idUsuario'";
    }

    public static function getSqlRemoveRastreioInscEdital($idProcesso) {
        $tpInscEdital = self::$TP_RASTREIO_INSC_EDITAL;
        return "delete from tb_urt_usuario_rastreio where PRC_ID_PROCESSO_REL = '$idProcesso' and URT_TP_RASTREIO = '$tpInscEdital'";
    }

    private static function sql_removeRastreioAntigo($idUsuario, $idProcesso = NULL, $idChamada = NULL, $tpRastreio = NULL) {
        $sql = "delete from tb_urt_usuario_rastreio
                where USR_ID_USUARIO = '$idUsuario'";


        if ($idProcesso != NULL) {
            $sql .= " and PRC_ID_PROCESSO_REL = '$idProcesso' ";
        }

        if ($idChamada != NULL) {
            $sql .= " and PCH_ID_CHAMADA_REL = '$idChamada' ";
        }

        if ($tpRastreio != NULL) {
            $sql .= " and URT_TP_RASTREIO = '$tpRastreio' ";
        }

        $sql .= " order by URT_DT_RASTREIO desc limit 1";

        return $sql;
    }

    /* GET FIELDS FROM TABLE */

    function getURT_ID_RASTREIO() {
        return $this->URT_ID_RASTREIO;
    }

    /* End of get URT_ID_RASTREIO */

    function getUSR_ID_USUARIO() {
        return $this->USR_ID_USUARIO;
    }

    /* End of get USR_ID_USUARIO */

    function getURT_DT_RASTREIO() {
        return $this->URT_DT_RASTREIO;
    }

    /* End of get URT_DT_RASTREIO */

    function getURT_TP_RASTREIO() {
        return $this->URT_TP_RASTREIO;
    }

    /* End of get URT_TP_RASTREIO */

    function getURT_DS_URL_ACESSO() {
        return $this->URT_DS_URL_ACESSO;
    }

    /* End of get URT_DS_URL_ACESSO */

    function getPRC_ID_PROCESSO_REL() {
        return $this->PRC_ID_PROCESSO_REL;
    }

    /* End of get PRC_ID_PROCESSO_REL */

    function getPCH_ID_CHAMADA_REL() {
        return $this->PCH_ID_CHAMADA_REL;
    }

    /* End of get PCH_ID_CHAMADA_REL */

    function getURT_DS_RASTREIO() {
        return $this->URT_DS_RASTREIO;
    }

    /* End of get URT_DS_RASTREIO */



    /* SET FIELDS FROM TABLE */

    function setURT_ID_RASTREIO($value) {
        $this->URT_ID_RASTREIO = $value;
    }

    /* End of SET URT_ID_RASTREIO */

    function setUSR_ID_USUARIO($value) {
        $this->USR_ID_USUARIO = $value;
    }

    /* End of SET USR_ID_USUARIO */

    function setURT_DT_RASTREIO($value) {
        $this->URT_DT_RASTREIO = $value;
    }

    /* End of SET URT_DT_RASTREIO */

    function setURT_TP_RASTREIO($value) {
        $this->URT_TP_RASTREIO = $value;
    }

    /* End of SET URT_TP_RASTREIO */

    function setURT_DS_URL_ACESSO($value) {
        $this->URT_DS_URL_ACESSO = $value;
    }

    /* End of SET URT_DS_URL_ACESSO */

    function setPRC_ID_PROCESSO_REL($value) {
        $this->PRC_ID_PROCESSO_REL = $value;
    }

    /* End of SET PRC_ID_PROCESSO_REL */

    function setPCH_ID_CHAMADA_REL($value) {
        $this->PCH_ID_CHAMADA_REL = $value;
    }

    /* End of SET PCH_ID_CHAMADA_REL */

    function setURT_DS_RASTREIO($value) {
        $this->URT_DS_RASTREIO = $value;
    }

    /* End of SET URT_DS_RASTREIO */
}

?>
