<?php

/**
 * tb_rpa_reserva_polo_area class
 * This class manipulates the table ReservaPoloArea
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 17/09/2014
 * */
require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/negocio/ReservaVagaArea.php";
require_once $CFG->rpasta . "/negocio/ReservaVagaPolo.php";

class ReservaPoloArea {

    private $RPA_ID_RESERVA_POL_AREA;
    private $RVC_ID_RESERVA_CHAMADA;
    private $PCH_ID_CHAMADA;
    private $POL_ID_POLO;
    private $AAC_ID_AREA_CHAMADA;
    private $RPA_QT_VAGAS;
    // auxiliares para forma de indexação do vetor de retorno
    public static $RESERVA_POLO = "P";
    public static $RESERVA_AREA = "A";
    public static $RESERVA_POLO_AREA = "M";

    /* Construtor padrão da classe */

    public function __construct($RVC_ID_RESERVA_CHAMADA, $PCH_ID_CHAMADA, $POL_ID_POLO, $AAC_ID_AREA_CHAMADA, $RPA_QT_VAGAS) {
        $this->RVC_ID_RESERVA_CHAMADA = $RVC_ID_RESERVA_CHAMADA;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->POL_ID_POLO = $POL_ID_POLO;
        $this->AAC_ID_AREA_CHAMADA = $AAC_ID_AREA_CHAMADA;
        $this->RPA_QT_VAGAS = $RPA_QT_VAGAS;
    }

    public static function CLAS_getSqlSobraVagas($idChamada, $idPolo, $idArea, $idReserva) {
        return "select RPA_QT_SOBRA_VAGAS as " . ProcessoChamada::$SQL_RET_SOBRA_VAGAS . " from tb_rpa_reserva_polo_area where 
                        PCH_ID_CHAMADA = '$idChamada'
                        AND AAC_ID_AREA_CHAMADA = '$idArea'
                        AND POL_ID_POLO = '$idPolo'
                        AND RVC_ID_RESERVA_CHAMADA = '$idReserva'";
    }

    public static function CLAS_getSqlSobraVagasPubGeral($idChamada, $idPolo, $idArea) {
        $flagCdtSel = FLAG_BD_SIM;

        return "select(((select PAC_QT_VAGAS from tb_pac_polo_area_chamada
                where 
                PCH_ID_CHAMADA = '$idChamada'
                AND POL_ID_POLO = '$idPolo'
                AND AAC_ID_AREA_CHAMADA = '$idArea') - 
                (select sum(RPA_QT_VAGAS) from tb_rpa_reserva_polo_area where 
                PCH_ID_CHAMADA = '$idChamada'
                AND POL_ID_POLO = '$idPolo'
                AND AAC_ID_AREA_CHAMADA = '$idArea')) - 
                (select count(*) from tb_ipr_inscricao_processo where 
                PCH_ID_CHAMADA = '$idChamada'
                AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                AND RVC_ID_RESERVA_CHAMADA IS NULL   
                AND IPR_ID_POLO_SELECIONADO = '$idPolo'
                AND AAC_ID_AREA_CHAMADA = '$idArea')) as " . ProcessoChamada::$SQL_RET_SOBRA_VAGAS;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_rpa_reserva_polo_area 
                       where PCH_ID_CHAMADA in
                       (select PCH_ID_CHAMADA from tb_pch_processo_chamada where PRC_ID_PROCESSO = '$idProcesso')";
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     * @param ReservaVagaChamada $listaReservaVaga Array com todas as reservas de vaga da chamada
     */
    public static function CLAS_getSqlSumarizaVagas($chamada, &$arrayCmds, $listaReservaVaga) {
        if ($chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj() && $chamada->admiteReservaVagaObj()) {
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $flagCdtSel = FLAG_BD_SIM;

            // recuperando dados
            $polosAreasChamada = PoloAreaChamada::buscarPoloAreaPorChamada($chamada->getPCH_ID_CHAMADA());

            foreach ($polosAreasChamada as $poloAreaChamada) {
                foreach ($listaReservaVaga as $reserva) {
                    $arrayCmds [] = "update tb_rpa_reserva_polo_area
                            set RPA_QT_SOBRA_VAGAS = (RPA_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND IPR_ID_POLO_SELECIONADO = '{$poloAreaChamada->getPOL_ID_POLO()}'
                                    AND AAC_ID_AREA_CHAMADA = '{$poloAreaChamada->getAAC_ID_AREA_CHAMADA()}'
                                    AND RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    ))
                            WHERE POL_ID_POLO = '{$poloAreaChamada->getPOL_ID_POLO()}'
                            AND AAC_ID_AREA_CHAMADA = '{$poloAreaChamada->getAAC_ID_AREA_CHAMADA()}'
                            and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                            and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
                }
            }
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     * @param int $idPolo
     * @param int $idArea
     * @param int $idReserva
     */
    public static function CLAS_getSqlSumarizaVagasInd($chamada, &$arrayCmds, $idPolo, $idArea, $idReserva) {
        $inscOk = InscricaoProcesso::$SIT_INSC_OK;
        $flagCdtSel = FLAG_BD_SIM;

        $arrayCmds [] = "update tb_rpa_reserva_polo_area
                            set RPA_QT_SOBRA_VAGAS = (RPA_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND IPR_ID_POLO_SELECIONADO = '$idPolo'
                                    AND AAC_ID_AREA_CHAMADA = '$idArea'
                                    AND RVC_ID_RESERVA_CHAMADA = '$idReserva'
                                    ))
                            WHERE POL_ID_POLO = '$idPolo'
                            AND AAC_ID_AREA_CHAMADA = '$idArea'
                            and RVC_ID_RESERVA_CHAMADA = '$idReserva'
                            and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
    }

    public static function getIndiceBusca($formaIndexacao, $idPolo = NULL, $idAreaAtu = NULL, $dadosExternos = NULL) {
        // polo ou area tem que ser nao nulo
        if (Util::vazioNulo($idPolo) && Util::vazioNulo($idAreaAtu) && $dadosExternos == FALSE) {
            throw new NegocioException("Polo ou área de atuação deve ser diferente de nulo para a construção do índice.");
        }

        // caso de tudo nulo e dados externos
        if (Util::vazioNulo($idPolo) && Util::vazioNulo($idAreaAtu) && $dadosExternos === TRUE) {
            return 0; // indice fictício
        }

        // definindo índice
        if ($formaIndexacao == self::$RESERVA_POLO) {
            $indice = $idPolo;
        } elseif ($formaIndexacao == self::$RESERVA_AREA) {
            $indice = $idAreaAtu;
        } elseif ($formaIndexacao == self::$RESERVA_POLO_AREA) {
            $indice = $idPolo . ";" . $idAreaAtu;
        } else {
            throw new NegocioException("Forma de indexação desconhecida para reserva-polo-área.");
        }

        return $indice;
    }

    public static function getValorIndiceBusca($listaBusca, $indice, $idReservaChamada, $dadosExternos = NULL) {
        if (!isset($listaBusca[$indice][$idReservaChamada])) {
            if ($dadosExternos) {
                return 0;
            }
            throw new NegocioException("Erro na indexação de reserva de vagas.");
        }
        return $listaBusca[$indice][$idReservaChamada];
    }

    /**
     * ATENCÃO: RECOMENDA-SE UTILIZAR FUNÇÕES PRÓPRIA DA CLASSE PARA MANIPULAR OS DADOS RETORNADOS
     * POR ESTA FUNÇÃO.
     * 
     * Para obter o índice de uma busca, utilize: getIndiceBusca($formaIndexacao, $idPolo = NULL, $idAreaAtu = NULL, $dadosExternos = NULL);
     * Para obter o valor de um índice, utilize: getValorIndiceBusca($listaBusca, $indice, $idReservaChamada, $dadosExternos = NULL);
     * 
     * @param int $idChamada
     * @param char $formaIndexacao
     * @return array Array indexado na forma informada, contendo a quantidade de vagas. 
     * 
     * 
     * @throws NegocioException
     */
    public static function buscarReservaPoloAreaPorChamada($idChamada, $formaIndexacao) {
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // definindo sql de busca
            if ($formaIndexacao == ReservaPoloArea::$RESERVA_POLO) {
                $sql = ReservaVagaPolo::getSqlBuscaVagas($idChamada);
            } elseif ($formaIndexacao == ReservaPoloArea::$RESERVA_AREA) {
                $sql = ReservaVagaArea::getSqlBuscaVagas($idChamada);
            } elseif ($formaIndexacao == ReservaPoloArea::$RESERVA_POLO_AREA) {
                $sql = "select 
                        RVC_ID_RESERVA_CHAMADA,
                        rpa.PCH_ID_CHAMADA,
                        POL_ID_POLO,
                        rpa.AAC_ID_AREA_CHAMADA,
                        RPA_QT_VAGAS as QT_VAGAS,
                        ARC_ID_SUBAREA_CONH
                    from
                        tb_rpa_reserva_polo_area rpa
                    left join tb_aac_area_atu_chamada aac on rpa.AAC_ID_AREA_CHAMADA = aac.AAC_ID_AREA_CHAMADA
                    where rpa.`PCH_ID_CHAMADA` = '$idChamada'";
            } else {
                throw new NegocioException("Tipo de indexação desconhecido para reserva-polo-área");
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

                // definindo indice
                $indice = self::getIndiceBusca($formaIndexacao, isset($dados['POL_ID_POLO']) ? $dados['POL_ID_POLO'] : NULL, isset($dados['ARC_ID_SUBAREA_CONH']) ? $dados['ARC_ID_SUBAREA_CONH'] : NULL);

                //adicionando no vetor
                if (!isset($vetRetorno[$indice])) {
                    $vetRetorno[$indice] = array($dados['RVC_ID_RESERVA_CHAMADA'] => $dados['QT_VAGAS']);
                } else {
                    $vetRetorno[$indice][$dados['RVC_ID_RESERVA_CHAMADA']] = $dados['QT_VAGAS'];
                }
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar reservas de vaga polo-área da chamada do processo.", $e);
        }
    }

    /**
     * ATENCÃO: RECOMENDA-SE UTILIZAR FUNÇÕES PRÓPRIA DA CLASSE PARA MANIPULAR OS DADOS RETORNADOS
     * POR ESTA FUNÇÃO.
     * 
     * Para obter o índice de uma busca, utilize: getIndiceBusca($formaIndexacao, $idPolo = NULL, $idAreaAtu = NULL, $dadosExternos = NULL);
     * Para obter o valor de um índice, utilize: getValorIndiceBusca($listaBusca, $indice, $idReservaChamada, $dadosExternos = NULL);
     * 
     * @param int $idChamada
     * @param char $formaIndexacao
     * @return array Array indexado na forma informada, contendo a quantidade de inscritos. 
     * 
     * 
     * @throws NegocioException
     */
    public static function buscarInscritosReservaPoloAreaPorChamada($idChamada, $formaIndexacao) {
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // definindo sql de busca
            if ($formaIndexacao == ReservaPoloArea::$RESERVA_POLO) {
                $sql = ReservaVagaPolo::getSqlBuscaInscritos($idChamada);
            } elseif ($formaIndexacao == ReservaPoloArea::$RESERVA_AREA) {
                $sql = ReservaVagaArea::getSqlBuscaInscritos($idChamada);
            } elseif ($formaIndexacao == ReservaPoloArea::$RESERVA_POLO_AREA) {
                $sql = "SELECT 
                        RVC_ID_RESERVA_CHAMADA,
                        rpa.PCH_ID_CHAMADA,
                        POL_ID_POLO,
                        rpa.AAC_ID_AREA_CHAMADA,
                        (SELECT 
                                COUNT(*)
                            FROM
                                tb_ipr_inscricao_processo ipr
                                    JOIN
                                tb_pin_polo_inscricao pin ON ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO
                                    AND pin.PIN_NR_ORDEM = 1
                            WHERE
                                ipr.PCH_ID_CHAMADA = rpa.PCH_ID_CHAMADA
                                    AND pin.POL_ID_POLO = rpa.POL_ID_POLO
                                    AND ipr.RVC_ID_RESERVA_CHAMADA = rpa.RVC_ID_RESERVA_CHAMADA
                                    AND ipr.AAC_ID_AREA_CHAMADA = rpa.AAC_ID_AREA_CHAMADA) AS QT_INSCRITOS,
                        ARC_ID_SUBAREA_CONH
                        FROM
                            tb_rpa_reserva_polo_area rpa
                                LEFT JOIN
                            tb_aac_area_atu_chamada aac ON rpa.AAC_ID_AREA_CHAMADA = aac.AAC_ID_AREA_CHAMADA
                        WHERE
                            rpa.`PCH_ID_CHAMADA` = '$idChamada'";
            } else {
                throw new NegocioException("Tipo de indexação desconhecido para reserva-polo-área");
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

                // definindo indice
                $indice = self::getIndiceBusca($formaIndexacao, isset($dados['POL_ID_POLO']) ? $dados['POL_ID_POLO'] : NULL, isset($dados['ARC_ID_SUBAREA_CONH']) ? $dados['ARC_ID_SUBAREA_CONH'] : NULL);

                //adicionando no vetor
                if (!isset($vetRetorno[$indice])) {
                    $vetRetorno[$indice] = array($dados['RVC_ID_RESERVA_CHAMADA'] => $dados['QT_INSCRITOS']);
                } else {
                    $vetRetorno[$indice][$dados['RVC_ID_RESERVA_CHAMADA']] = $dados['QT_INSCRITOS'];
                }
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar inscritos por reservas de vaga polo-área da chamada.", $e);
        }
    }

    /**
     * 
     * @param int $idChamada
     * @param int $idPolo
     * @param int $idAreaAtu
     * @param int $idReserva
     * @param int $qtVagas
     * @param int $indexador Informa o indexador em questão: Uma das opções da classe: polo, área ou polo-área
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function processaAtualizacaoVagas($idChamada, $idPolo, $idAreaAtu, $idReserva, $qtVagas, $indexador, &$arrayRet) {
        if ($indexador == self::$RESERVA_POLO) {
            $arrayRet [] = ReservaVagaPolo::getSqlCriarReservaPolo($idChamada, $idPolo, $idReserva, $qtVagas);
        } elseif ($indexador == self::$RESERVA_AREA) {
            $arrayRet [] = ReservaVagaArea::getSqlCriarReservaArea($idChamada, $idAreaAtu, $idReserva, $qtVagas);
        } elseif ($indexador == self::$RESERVA_POLO_AREA) {
            $arrayRet [] = self::getSqlCriarReservaPolArea($idChamada, $idPolo, $idAreaAtu, $idReserva, $qtVagas);
        } else {
            throw new NegocioException("Indexador inválido para processamento de reserva-polo-área.");
        }
    }

    private static function getSqlCriarReservaPolArea($idChamada, $idPolo, $idAreaAtu, $idReserva, $qtVagas) {
        $idAreaAtu = "(select AAC_ID_AREA_CHAMADA from tb_aac_area_atu_chamada where PCH_ID_CHAMADA = '$idChamada' and ARC_ID_SUBAREA_CONH = '$idAreaAtu')";

        return "insert into tb_rpa_reserva_polo_area (RVC_ID_RESERVA_CHAMADA, PCH_ID_CHAMADA, POL_ID_POLO, AAC_ID_AREA_CHAMADA, RPA_QT_VAGAS) values
                ((select RVC_ID_RESERVA_CHAMADA from tb_rvc_reserva_vaga_chamada where PCH_ID_CHAMADA = '$idChamada' and RVG_ID_RESERVA_VAGA = '$idReserva'),
                '$idChamada', '$idPolo', $idAreaAtu, '$qtVagas')";
    }

    /**
     * 
     * @param int $idChamada
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function sqlRemoverPorChamada($idChamada, &$arrayRet) {
        // polo 
        $arrayRet [] = ReservaVagaPolo::getSqlRemoverPorChamada($idChamada);

        // área
        $arrayRet [] = ReservaVagaArea::getSqlRemoverPorChamada($idChamada);

        // polo área
        $arrayRet [] = "delete from tb_rpa_reserva_polo_area where PCH_ID_CHAMADA = '$idChamada'";
    }

    /* GET FIELDS FROM TABLE */

    function getRPA_ID_RESERVA_POL_AREA() {
        return $this->RPA_ID_RESERVA_POL_AREA;
    }

    /* End of get RPA_ID_RESERVA_POL_AREA */

    function getRVC_ID_RESERVA_CHAMADA() {
        return $this->RVC_ID_RESERVA_CHAMADA;
    }

    /* End of get RVC_ID_RESERVA_CHAMADA */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getPOL_ID_POLO() {
        return $this->POL_ID_POLO;
    }

    /* End of get POL_ID_POLO */

    function getAAC_ID_AREA_CHAMADA() {
        return $this->AAC_ID_AREA_CHAMADA;
    }

    /* End of get AAC_ID_AREA_CHAMADA */

    function getRPA_QT_VAGAS() {
        return $this->RPA_QT_VAGAS;
    }

    /* End of get RPA_QT_VAGAS */



    /* SET FIELDS FROM TABLE */

    function setRPA_ID_RESERVA_POL_AREA($value) {
        $this->RPA_ID_RESERVA_POL_AREA = $value;
    }

    /* End of SET RPA_ID_RESERVA_POL_AREA */

    function setRVC_ID_RESERVA_CHAMADA($value) {
        $this->RVC_ID_RESERVA_CHAMADA = $value;
    }

    /* End of SET RVC_ID_RESERVA_CHAMADA */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setPOL_ID_POLO($value) {
        $this->POL_ID_POLO = $value;
    }

    /* End of SET POL_ID_POLO */

    function setAAC_ID_AREA_CHAMADA($value) {
        $this->AAC_ID_AREA_CHAMADA = $value;
    }

    /* End of SET AAC_ID_AREA_CHAMADA */

    function setRPA_QT_VAGAS($value) {
        $this->RPA_QT_VAGAS = $value;
    }

    /* End of SET RPA_QT_VAGAS */
}

?>
