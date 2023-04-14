<?php

/**
 * tb_rvp_reserva_vaga_polo class
 * This class manipulates the table ReservaVagaPolo
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 11/11/2014
 * */
class ReservaVagaPolo {

    private $RVC_ID_RESERVA_CHAMADA;
    private $PCH_ID_CHAMADA;
    private $POL_ID_POLO;
    private $RVP_QT_VAGAS;

    /* Construtor padrão da classe */

    public function __construct($RVC_ID_RESERVA_CHAMADA, $PCH_ID_CHAMADA, $POL_ID_POLO, $RVP_QT_VAGAS) {
        $this->RVC_ID_RESERVA_CHAMADA = $RVC_ID_RESERVA_CHAMADA;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->POL_ID_POLO = $POL_ID_POLO;
        $this->RVP_QT_VAGAS = $RVP_QT_VAGAS;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_rvp_reserva_vaga_polo 
                       where PCH_ID_CHAMADA in
                       (select PCH_ID_CHAMADA from tb_pch_processo_chamada where PRC_ID_PROCESSO = '$idProcesso')";
    }

    public static function CLAS_getSqlSobraVagas($idChamada, $idPolo, $idReserva) {
        return "select RVP_QT_SOBRA_VAGAS as " . ProcessoChamada::$SQL_RET_SOBRA_VAGAS . " from tb_rvp_reserva_vaga_polo where 
                        PCH_ID_CHAMADA = '$idChamada'
                        AND POL_ID_POLO = '$idPolo'
                        AND RVC_ID_RESERVA_CHAMADA = '$idReserva'";
    }

    public static function CLAS_getSqlSobraVagasPubGeral($idChamada, $idPolo) {
        $flagCdtSel = FLAG_BD_SIM;

        return "select(((select PPC_QT_VAGAS from tb_ppc_polo_chamada where 
                PCH_ID_CHAMADA = '$idChamada'
                AND POL_ID_POLO = '$idPolo') - 
                (select sum(RVP_QT_VAGAS) from tb_rvp_reserva_vaga_polo where 
                PCH_ID_CHAMADA = '$idChamada'
                AND POL_ID_POLO = '$idPolo')) - 
                (select count(*) from tb_ipr_inscricao_processo where 
                PCH_ID_CHAMADA = '$idChamada'
                AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                AND RVC_ID_RESERVA_CHAMADA IS NULL   
                AND IPR_ID_POLO_SELECIONADO = '$idPolo')) as " . ProcessoChamada::$SQL_RET_SOBRA_VAGAS;
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     * @param ReservaVagaChamada $listaReservaVaga Array com todas as reservas de vaga da chamada
     */
    public static function CLAS_getSqlSumarizaVagas($chamada, &$arrayCmds, $listaReservaVaga) {
        if ($chamada->admitePoloObj() && $chamada->admiteReservaVagaObj()) {
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $flagCdtSel = FLAG_BD_SIM;

            // recuperando polos
            $polosChamada = PoloChamada::buscarPoloPorChamada($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo());

            foreach (array_keys($polosChamada) as $id) {
                foreach ($listaReservaVaga as $reserva) {
                    $arrayCmds [] = "update tb_rvp_reserva_vaga_polo
                            set RVP_QT_SOBRA_VAGAS = (RVP_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND IPR_ID_POLO_SELECIONADO = '$id'
                                    AND RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    ))
                            WHERE POL_ID_POLO = '$id'
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
     * @param int $idReserva
     */
    public static function CLAS_getSqlSumarizaVagasInd($chamada, &$arrayCmds, $idPolo, $idReserva) {
        $inscOk = InscricaoProcesso::$SIT_INSC_OK;
        $flagCdtSel = FLAG_BD_SIM;

        $arrayCmds [] = "update tb_rvp_reserva_vaga_polo
                            set RVP_QT_SOBRA_VAGAS = (RVP_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND IPR_ID_POLO_SELECIONADO = '$idPolo'
                                    AND RVC_ID_RESERVA_CHAMADA = '$idReserva'
                                    ))
                            WHERE POL_ID_POLO = '$idPolo'
                            and RVC_ID_RESERVA_CHAMADA = '$idReserva'
                            and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
    }

    public static function getSqlBuscaVagas($idChamada) {
        return "SELECT 
                    RVC_ID_RESERVA_CHAMADA,
                    PCH_ID_CHAMADA,
                    POL_ID_POLO,
                    RVP_QT_VAGAS as QT_VAGAS
                FROM
                    tb_rvp_reserva_vaga_polo
                where `PCH_ID_CHAMADA` = '$idChamada'";
    }

    public static function getSqlBuscaInscritos($idChamada) {
        return "SELECT 
                RVC_ID_RESERVA_CHAMADA,
                PCH_ID_CHAMADA,
                POL_ID_POLO,
                (SELECT 
                        COUNT(*)
                    FROM
                        tb_ipr_inscricao_processo ipr
                            JOIN
                        tb_pin_polo_inscricao pin ON ipr.IPR_ID_INSCRICAO = pin.IPR_ID_INSCRICAO
                            AND pin.PIN_NR_ORDEM = 1
                    WHERE
                        ipr.PCH_ID_CHAMADA = rvp.PCH_ID_CHAMADA
                            AND pin.POL_ID_POLO = rvp.POL_ID_POLO
                            AND ipr.RVC_ID_RESERVA_CHAMADA = rvp.RVC_ID_RESERVA_CHAMADA) AS QT_INSCRITOS
                FROM
                    tb_rvp_reserva_vaga_polo rvp
                WHERE
                    `PCH_ID_CHAMADA` = '$idChamada'";
    }

    public static function getSqlRemoverPorChamada($idChamada) {
        return "delete from tb_rvp_reserva_vaga_polo where PCH_ID_CHAMADA = '$idChamada'";
    }

    public static function getSqlCriarReservaPolo($idChamada, $idPolo, $idReserva, $qtVagas) {
        return "insert into tb_rvp_reserva_vaga_polo (RVC_ID_RESERVA_CHAMADA, PCH_ID_CHAMADA, POL_ID_POLO, RVP_QT_VAGAS) values
                ((select RVC_ID_RESERVA_CHAMADA from tb_rvc_reserva_vaga_chamada where PCH_ID_CHAMADA = '$idChamada' and RVG_ID_RESERVA_VAGA = '$idReserva'),
                '$idChamada', '$idPolo', '$qtVagas')";
    }

    /* GET FIELDS FROM TABLE */

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

    function getRVP_QT_VAGAS() {
        return $this->RVP_QT_VAGAS;
    }

    /* End of get RVP_QT_VAGAS */



    /* SET FIELDS FROM TABLE */

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

    function setRVP_QT_VAGAS($value) {
        $this->RVP_QT_VAGAS = $value;
    }

    /* End of SET RVP_QT_VAGAS */
}

?>
