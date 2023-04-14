<?php

/**
 * tb_rvc_reserva_vaga_chamada class
 * This class manipulates the table ReservaVagaChamada
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 12/09/2014
 * */
class ReservaVagaChamada {

    private $RVC_ID_RESERVA_CHAMADA;
    private $PRC_ID_PROCESSO;
    private $PCH_ID_CHAMADA;
    private $RVG_ID_RESERVA_VAGA;
    private $RVC_QT_VAGAS_RESERVADAS;
    private $RVC_RESERVA_DESABILITADA;
    // campos herdados
    public $RVG_NM_RESERVA_VAGA;
    public $RVG_DS_RESERVA_VAGA;
    public static $DS_PUBLICO_GERAL = "Público Geral";
    public static $ID_PUBLICO_GERAL = "0";

    /* Construtor padrão da classe */

    public function __construct($RVC_ID_RESERVA_CHAMADA, $PRC_ID_PROCESSO, $PCH_ID_CHAMADA, $RVG_ID_RESERVA_VAGA, $RVC_QT_VAGAS_RESERVADAS, $RVC_RESERVA_DESABILITADA) {
        $this->RVC_ID_RESERVA_CHAMADA = $RVC_ID_RESERVA_CHAMADA;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->RVG_ID_RESERVA_VAGA = $RVG_ID_RESERVA_VAGA;
        $this->RVC_QT_VAGAS_RESERVADAS = $RVC_QT_VAGAS_RESERVADAS;
        $this->RVC_RESERVA_DESABILITADA = $RVC_RESERVA_DESABILITADA;
    }

    public static function getFlagReservaAtiva() {
        return FLAG_BD_NAO;
    }

    public static function getFlagReservaInativa() {
        return FLAG_BD_SIM;
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     * @param ReservaVagaChamada $listaReservaVaga Array com todas as reservas de vaga da chamada
     */
    public static function CLAS_getSqlSumarizaVagas($chamada, &$arrayCmds, $listaReservaVaga) {
        if ($chamada->admiteReservaVagaObj()) {
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $flagCdtSel = FLAG_BD_SIM;

            foreach ($listaReservaVaga as $reserva) {
                $arrayCmds [] = "update tb_rvc_reserva_vaga_chamada
                            set RVC_QT_SOBRA_VAGAS = (RVC_QT_VAGAS_RESERVADAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    ))
                            WHERE RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                            and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
            }
        }
    }

    /**
     * 
     * @param int $idReservaCham
     * 
     * @return \ReservaVagaChamada
     * @throws NegocioException
     */
    public static function buscarReservaVagaChamPorId($idReservaCham) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        RVC_ID_RESERVA_CHAMADA,
                        PRC_ID_PROCESSO,
                        PCH_ID_CHAMADA,
                        rvc.RVG_ID_RESERVA_VAGA,
                        RVG_NM_RESERVA_VAGA,
                        RVG_DS_RESERVA_VAGA,
                        RVC_QT_VAGAS_RESERVADAS,
                        RVC_RESERVA_DESABILITADA
                    from
                        tb_rvc_reserva_vaga_chamada rvc
                        join tb_rvg_reserva_vaga rvg on rvg.RVG_ID_RESERVA_VAGA = rvc.RVG_ID_RESERVA_VAGA
                    where `RVC_ID_RESERVA_CHAMADA` = '$idReservaCham'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // recuperando quantidade de dados
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                throw new NegocioException("Reserva de Vaga da chamada não encontrada.");
            }

            //recuperando dados
            $dados = ConexaoMysql::getLinha($resp);


            // criando objeto temporário
            $reservaTemp = new ReservaVagaChamada($dados['RVC_ID_RESERVA_CHAMADA'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['RVG_ID_RESERVA_VAGA'], $dados['RVC_QT_VAGAS_RESERVADAS'], $dados['RVC_RESERVA_DESABILITADA']);


            // inserindo campos herdados
            $reservaTemp->RVG_NM_RESERVA_VAGA = $dados['RVG_NM_RESERVA_VAGA'];
            $reservaTemp->RVG_DS_RESERVA_VAGA = $dados['RVG_DS_RESERVA_VAGA'];


            return $reservaTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar reservas de vaga da chamada por ID.", $e);
        }
    }

    /**
     * 
     * @param int $idChamada
     * @param char $flagSituacao
     * @param array $listaReservaVagas Array de Reserva de vagas que devem aparecer no retorno, independente se participam ou não da chamada
     * @param boolean $indexacaoPorIdReservaCham Diz se a indexação do vetor de retorno deve ser feito pelo id da reserva da chamada. Padrão: FALSE
     * @return \ReservaVagaChamada Array com as reservas da chamada
     * 
     * OBS1: Se $indexacaoPorIdReservaCham é true, então o vetor de retorno é indexado pelo campo RVC_ID_RESERVA_CHAMADA
     * OBS2: Se $indexacaoPorIdReservaCham é true, então o parâmetro $listaReservaVagas não é considerado!
     * 
     * 
     * @throws NegocioException
     */
    public static function buscarReservaVagaPorChamada($idChamada, $flagSituacao = NULL, $listaReservaVagas = NULL, $indexacaoPorIdReservaCham = FALSE) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        RVC_ID_RESERVA_CHAMADA,
                        PRC_ID_PROCESSO,
                        PCH_ID_CHAMADA,
                        rvc.RVG_ID_RESERVA_VAGA,
                        RVG_NM_RESERVA_VAGA,
                        RVG_DS_RESERVA_VAGA,
                        RVC_QT_VAGAS_RESERVADAS,
                        RVC_RESERVA_DESABILITADA
                    from
                        tb_rvc_reserva_vaga_chamada rvc
                        join tb_rvg_reserva_vaga rvg on rvg.RVG_ID_RESERVA_VAGA = rvc.RVG_ID_RESERVA_VAGA
                    where `PCH_ID_CHAMADA` = '$idChamada'";

            // caso de lista de reserva de vagas
            if ($listaReservaVagas != NULL) {
                $vetorReservaVagas = explode(",", $listaReservaVagas);
                $sql .= " and rvc.RVG_ID_RESERVA_VAGA in ($listaReservaVagas) ";
            }

            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagReservaAtiva()) { // ativo
                    $sql .= " AND (RVC_RESERVA_DESABILITADA IS NULL
                                OR RVC_RESERVA_DESABILITADA = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagReservaInativa()) { // desativado
                    $sql .= " AND (RVC_RESERVA_DESABILITADA IS NOT NULL
                                AND RVC_RESERVA_DESABILITADA = '$flagSituacao')";
                }
            }

            $sql .= " order by RVG_NM_RESERVA_VAGA";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // recuperando quantidade de dados
            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando dado
                $reservaTemp = new ReservaVagaChamada($dados['RVC_ID_RESERVA_CHAMADA'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['RVG_ID_RESERVA_VAGA'], $dados['RVC_QT_VAGAS_RESERVADAS'], $dados['RVC_RESERVA_DESABILITADA']);

                // verificando necessidade de remover reservas já aparecidas
                if (isset($vetorReservaVagas)) {
                    unset($vetorReservaVagas[array_search($reservaTemp->RVG_ID_RESERVA_VAGA, $vetorReservaVagas)]);
                }

                // inserindo campos herdados
                $reservaTemp->RVG_NM_RESERVA_VAGA = $dados['RVG_NM_RESERVA_VAGA'];
                $reservaTemp->RVG_DS_RESERVA_VAGA = $dados['RVG_DS_RESERVA_VAGA'];

                //adicionando no vetor
                if (!$indexacaoPorIdReservaCham) {
                    $vetRetorno[] = $reservaTemp;
                } else {
                    $vetRetorno[$reservaTemp->RVC_ID_RESERVA_CHAMADA] = $reservaTemp;
                }
            }

            // incluindo demais reservas requisitadas
            if (!$indexacaoPorIdReservaCham && isset($vetorReservaVagas) && count($vetorReservaVagas) != 0) {
                $listaReservaVagas = implode(",", $vetorReservaVagas);

                $reservaVagas = ReservaVaga::buscarReservasVagasPorIds($listaReservaVagas);

                // percorrendo polos e gerando dados
                foreach ($reservaVagas as $reserva) {

                    $reservaTemp = new ReservaVagaChamada(NULL, NULL, $idChamada, $reserva->getRVG_ID_RESERVA_VAGA(), 0, NULL);
                    $reservaTemp->RVG_NM_RESERVA_VAGA = $reserva->getRVG_NM_RESERVA_VAGA();
                    $reservaTemp->RVG_DS_RESERVA_VAGA = $reserva->getRVG_DS_RESERVA_VAGA();

                    $vetRetorno [] = $reservaTemp;
                }
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar reservas de vaga da chamada do processo.", $e);
        }
    }

    public static function buscarIdsReservaVagaPorChamada($idChamada, $flagSituacao = NULL, $publicoGeral = FALSE) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        rvc.RVG_ID_RESERVA_VAGA,
                        RVG_NM_RESERVA_VAGA
                    from
                        tb_rvc_reserva_vaga_chamada rvc
                        join tb_rvg_reserva_vaga rvg on rvg.RVG_ID_RESERVA_VAGA = rvc.RVG_ID_RESERVA_VAGA
                    where `PCH_ID_CHAMADA` = '$idChamada'";

            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagReservaAtiva()) { // ativo
                    $sql .= " AND (RVC_RESERVA_DESABILITADA IS NULL
                                OR RVC_RESERVA_DESABILITADA = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagReservaInativa()) { // desativado
                    $sql .= " AND (RVC_RESERVA_DESABILITADA IS NOT NULL
                                AND RVC_RESERVA_DESABILITADA = '$flagSituacao')";
                }
            }

            $sql .= " order by RVG_NM_RESERVA_VAGA";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);


            $vetRetorno = array();

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando 
                return $vetRetorno;
            }

            // adicionar público local? 
            if ($publicoGeral) {
                $vetRetorno[self::$ID_PUBLICO_GERAL] = self::$DS_PUBLICO_GERAL;
            }

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['RVG_ID_RESERVA_VAGA'];
                $valor = $dados['RVG_NM_RESERVA_VAGA'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar reservas de vaga da chamada do processo.", $e);
        }
    }

    public static function teveModificacaoReservaVagasChamada($idChamada, $idReservaVagas) {
        // recuperando polos anteriores da chamada
        $idReservaVagasAtu = array_keys(self::buscarIdsReservaVagaPorChamada($idChamada, ReservaVagaChamada::getFlagReservaAtiva()));
        return $idReservaVagasAtu != $idReservaVagas;
    }

    public static function contarReservaVagaPorChamada($idChamada, $flagSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                       count(*) as cont
                     from
                        tb_rvc_reserva_vaga_chamada rvc
                        join tb_rvg_reserva_vaga rvg on rvg.RVG_ID_RESERVA_VAGA = rvc.RVG_ID_RESERVA_VAGA
                    where `PCH_ID_CHAMADA` = '$idChamada'";


            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagReservaAtiva()) { // ativo
                    $sql .= " AND (RVC_RESERVA_DESABILITADA IS NULL
                                OR RVC_RESERVA_DESABILITADA = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagReservaInativa()) { // desativado
                    $sql .= " AND (RVC_RESERVA_DESABILITADA IS NOT NULL
                                AND RVC_RESERVA_DESABILITADA = '$flagSituacao')";
                }
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);
            return ConexaoMysql:: getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar reserva de vagas da chamada do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso 
     * @param int $idChamada 
     * @param int $qtVagas
     * @param int $idReserva
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function processaAtualizacaoVagas($idProcesso, $idChamada, $qtVagas, $idReserva, &$arrayRet) {
        // Tentando recuperar o id da reserva
        $idReservaCham = self::buscarIdReservaChamPorReservaVaga($idChamada, $idReserva);

        // Não existe?
        if ($idReservaCham == NULL) {
            // tem que criar a reserva
            $arrayRet [] = self::getSqlCriarReservaVagaChamada($idProcesso, $idChamada, $idReserva, $qtVagas);
            return; // nada mais a fazer
        }

        // gerando sql que atualiza a quantidade de vagas
        $arrayRet [] = self::getSqlAtualizaReservaVagaChamada($idReservaCham, $qtVagas);
    }

    private static function getSqlAtualizaReservaVagaChamada($idReservaCham, $qtVagas) {
        return "update tb_rvc_reserva_vaga_chamada set RVC_QT_VAGAS_RESERVADAS = '$qtVagas', RVC_RESERVA_DESABILITADA = NULL where
                RVC_ID_RESERVA_CHAMADA = '$idReservaCham'";
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        ReservaPoloArea::addSqlRemoverPorProcesso($idProcesso, $vetSqls);
        ReservaVagaPolo::addSqlRemoverPorProcesso($idProcesso, $vetSqls);
        ReservaVagaArea::addSqlRemoverPorProcesso($idProcesso, $vetSqls);

        $vetSqls [] = "delete from tb_rvc_reserva_vaga_chamada where PRC_ID_PROCESSO = '$idProcesso'";
    }

    /**
     * Esta função retorna o sql que remove as reservas da chamada que não estão em $idReservasAptas
     * e suas dependências.
     * 
     * @param int $idChamada
     * @param string $idReservasAptas
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function sqlRemoveForaLista($idChamada, $idReservasAptas, &$arrayRet) {

        $sqlAptos = $idReservasAptas == NULL ? "" : " and RVG_ID_RESERVA_VAGA NOT IN ($idReservasAptas)";

        // desabilitando usados por algum candidato
        $arrayRet [] = self::getSqlDesativarUtilizadaPorChamada($idChamada, $sqlAptos);

        // removendo não desabilitados
        $flagAtiva = self::getFlagReservaAtiva();
        $arrayRet [] = "delete from tb_rvc_reserva_vaga_chamada where PCH_ID_CHAMADA = '$idChamada' and 
                         (RVC_RESERVA_DESABILITADA IS NULL or RVC_RESERVA_DESABILITADA = '$flagAtiva')
                        $sqlAptos";
    }

    private static function getSqlCriarReservaVagaChamada($idProcesso, $idChamada, $idReserva, $qtVagas) {
        return "insert into tb_rvc_reserva_vaga_chamada (PRC_ID_PROCESSO, PCH_ID_CHAMADA, RVG_ID_RESERVA_VAGA, RVC_QT_VAGAS_RESERVADAS) values
                ('$idProcesso', '$idChamada', '$idReserva', '$qtVagas')";
    }

    private static function getSqlDesativarUtilizadaPorChamada($idChamada, $sqlAppendComAnd = NULL) {
        $sqlAppendComAnd = $sqlAppendComAnd == NULL ? "" : "$sqlAppendComAnd";
        $flagDes = self::getFlagReservaInativa();
        return "update tb_rvc_reserva_vaga_chamada rvc set RVC_RESERVA_DESABILITADA = '$flagDes' where PCH_ID_CHAMADA = '$idChamada' $sqlAppendComAnd and 
                (select count(*) from tb_ipr_inscricao_processo where PCH_ID_CHAMADA = '$idChamada' and RVC_ID_RESERVA_CHAMADA = rvc.RVC_ID_RESERVA_CHAMADA) > 0";
    }

    public static function buscarIdReservaChamPorReservaVaga($idChamada, $idReservaVaga) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                       RVC_ID_RESERVA_CHAMADA as id
                     from
                        tb_rvc_reserva_vaga_chamada
                    where `PCH_ID_CHAMADA` = '$idChamada'
                           and RVG_ID_RESERVA_VAGA = '$idReservaVaga'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            if ($numLinhas == 0) {
                return NULL;
            }
            return ConexaoMysql:: getResult("id", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar ID da reserva de vaga da chamada do processo.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getRVC_ID_RESERVA_CHAMADA() {
        return $this->RVC_ID_RESERVA_CHAMADA;
    }

    /* End of get RVC_ID_RESERVA_CHAMADA */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getRVG_ID_RESERVA_VAGA() {
        return $this->RVG_ID_RESERVA_VAGA;
    }

    /* End of get RVG_ID_RESERVA_VAGA */

    function getRVC_QT_VAGAS_RESERVADAS() {
        return $this->RVC_QT_VAGAS_RESERVADAS;
    }

    /* End of get RVC_QT_VAGAS_RESERVADAS */

    function getRVC_RESERVA_DESABILITADA() {
        return $this->RVC_RESERVA_DESABILITADA;
    }

    /* End of get RVC_RESERVA_DESABILITADA */



    /* SET FIELDS FROM TABLE */

    function setRVC_ID_RESERVA_CHAMADA($value) {
        $this->RVC_ID_RESERVA_CHAMADA = $value;
    }

    /* End of SET RVC_ID_RESERVA_CHAMADA */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setRVG_ID_RESERVA_VAGA($value) {
        $this->RVG_ID_RESERVA_VAGA = $value;
    }

    /* End of SET RVG_ID_RESERVA_VAGA */

    function setRVC_QT_VAGAS_RESERVADAS($value) {
        $this->RVC_QT_VAGAS_RESERVADAS = $value;
    }

    /* End of SET RVC_QT_VAGAS_RESERVADAS */

    function setRVC_RESERVA_DESABILITADA($value) {
        $this->RVC_RESERVA_DESABILITADA = $value;
    }

    /* End of SET RVC_RESERVA_DESABILITADA */
}

?>
