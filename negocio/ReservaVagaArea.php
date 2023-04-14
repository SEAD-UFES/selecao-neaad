<?php

/**
 * tb_rva_reserva_vaga_area class
 * This class manipulates the table ReservaVagaArea
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 11/11/2014
 * */
class ReservaVagaArea {

    private $RVC_ID_RESERVA_CHAMADA;
    private $PCH_ID_CHAMADA;
    private $AAC_ID_AREA_CHAMADA;
    private $RVA_QT_VAGAS;

    /* Construtor padrão da classe */

    public function __construct($RVC_ID_RESERVA_CHAMADA, $PCH_ID_CHAMADA, $AAC_ID_AREA_CHAMADA, $RVA_QT_VAGAS) {
        $this->RVC_ID_RESERVA_CHAMADA = $RVC_ID_RESERVA_CHAMADA;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->AAC_ID_AREA_CHAMADA = $AAC_ID_AREA_CHAMADA;
        $this->RVA_QT_VAGAS = $RVA_QT_VAGAS;
    }
    
    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_rva_reserva_vaga_area 
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
        if ($chamada->admiteAreaAtuacaoObj() && $chamada->admiteReservaVagaObj()) {
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $flagCdtSel = FLAG_BD_SIM;

            // recuperando áreas
            $areasChamada = AreaAtuChamada::buscarAreaAtuPorChamada($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva(), TRUE);

            foreach (array_keys($areasChamada) as $id) {
                foreach ($listaReservaVaga as $reserva) {
                    $arrayCmds [] = "update tb_rva_reserva_vaga_area
                            set RVA_QT_SOBRA_VAGAS = (RVA_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND AAC_ID_AREA_CHAMADA = '$id'
                                    AND RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    ))
                            WHERE AAC_ID_AREA_CHAMADA = '$id'
                            and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                            and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
                }
            }
        }
    }

    public static function getSqlBuscaVagas($idChamada) {
        return "select 
                    RVC_ID_RESERVA_CHAMADA,
                    rva.PCH_ID_CHAMADA,
                    rva.AAC_ID_AREA_CHAMADA,
                    RVA_QT_VAGAS as QT_VAGAS,
                    ARC_ID_SUBAREA_CONH
                from
                    tb_rva_reserva_vaga_area rva
                left join tb_aac_area_atu_chamada aac on rva.AAC_ID_AREA_CHAMADA = aac.AAC_ID_AREA_CHAMADA
                where rva.`PCH_ID_CHAMADA` = '$idChamada'";
    }

    public static function getSqlBuscaInscritos($idChamada) {
        return "SELECT 
                RVC_ID_RESERVA_CHAMADA,
                rva.PCH_ID_CHAMADA,
                rva.AAC_ID_AREA_CHAMADA,
                (SELECT 
                        COUNT(*)
                    FROM
                        tb_ipr_inscricao_processo ipr
                    WHERE
                        ipr.PCH_ID_CHAMADA = rva.PCH_ID_CHAMADA
                            AND ipr.AAC_ID_AREA_CHAMADA = rva.AAC_ID_AREA_CHAMADA
                            AND ipr.RVC_ID_RESERVA_CHAMADA = rva.RVC_ID_RESERVA_CHAMADA) AS QT_INSCRITOS,
                ARC_ID_SUBAREA_CONH
                FROM
                    tb_rva_reserva_vaga_area rva
                        LEFT JOIN
                    tb_aac_area_atu_chamada aac ON rva.AAC_ID_AREA_CHAMADA = aac.AAC_ID_AREA_CHAMADA
                WHERE
                    rva.`PCH_ID_CHAMADA` = '$idChamada'";
    }

    public static function getSqlRemoverPorChamada($idChamada) {
        return "delete from tb_rva_reserva_vaga_area where PCH_ID_CHAMADA = '$idChamada'";
    }

    public static function getSqlCriarReservaArea($idChamada, $idAreaAtu, $idReserva, $qtVagas) {
        $idAreaAtu = "(select AAC_ID_AREA_CHAMADA from tb_aac_area_atu_chamada where PCH_ID_CHAMADA = '$idChamada' and ARC_ID_SUBAREA_CONH = '$idAreaAtu')";

        return "insert into tb_rva_reserva_vaga_area (RVC_ID_RESERVA_CHAMADA, PCH_ID_CHAMADA, AAC_ID_AREA_CHAMADA, RVA_QT_VAGAS) values
                ((select RVC_ID_RESERVA_CHAMADA from tb_rvc_reserva_vaga_chamada where PCH_ID_CHAMADA = '$idChamada' and RVG_ID_RESERVA_VAGA = '$idReserva'),
                '$idChamada', $idAreaAtu, '$qtVagas')";
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

    function getAAC_ID_AREA_CHAMADA() {
        return $this->AAC_ID_AREA_CHAMADA;
    }

    /* End of get AAC_ID_AREA_CHAMADA */

    function getRVA_QT_VAGAS() {
        return $this->RVA_QT_VAGAS;
    }

    /* End of get RVA_QT_VAGAS */



    /* SET FIELDS FROM TABLE */

    function setRVC_ID_RESERVA_CHAMADA($value) {
        $this->RVC_ID_RESERVA_CHAMADA = $value;
    }

    /* End of SET RVC_ID_RESERVA_CHAMADA */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setAAC_ID_AREA_CHAMADA($value) {
        $this->AAC_ID_AREA_CHAMADA = $value;
    }

    /* End of SET AAC_ID_AREA_CHAMADA */

    function setRVA_QT_VAGAS($value) {
        $this->RVA_QT_VAGAS = $value;
    }

    /* End of SET RVA_QT_VAGAS */
}

?>
