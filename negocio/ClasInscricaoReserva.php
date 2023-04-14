<?php

/**
 * tb_cir_clas_inscricao_reserva class
 * This class manipulates the table ClasInscricaoReserva
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2015       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 07/04/2015
 * */
class ClasInscricaoReserva {

    private $IPR_ID_INSCRICAO;
    private $RVC_ID_RESERVA_CHAMADA;
    private $CIR_NR_CLASSIFICACAO_CAND;
    private $CIR_CLASSIF_UTILIZADA;

    /* Construtor padrão da classe */

    public function __construct($IPR_ID_INSCRICAO, $RVC_ID_RESERVA_CHAMADA, $CIR_NR_CLASSIFICACAO_CAND, $CIR_CLASSIF_UTILIZADA) {
        $this->IPR_ID_INSCRICAO = $IPR_ID_INSCRICAO;
        $this->RVC_ID_RESERVA_CHAMADA = $RVC_ID_RESERVA_CHAMADA;
        $this->CIR_NR_CLASSIFICACAO_CAND = $CIR_NR_CLASSIFICACAO_CAND;
        $this->CIR_CLASSIF_UTILIZADA = $CIR_CLASSIF_UTILIZADA;
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por remover todos os registros da chamada em questão
     * 
     * @param int $idChamada
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsLimparClas($idChamada, &$arrayCmds) {
        $arrayCmds [] = "delete from tb_cir_clas_inscricao_reserva where IPR_ID_INSCRICAO in (select IPR_ID_INSCRICAO from tb_ipr_inscricao_processo where PCH_ID_CHAMADA = '$idChamada')";
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por criar as classificações de uma chamada para uma dada reserva de vaga
     * 
     * @param int $idChamada
     * @param int $idReservaChamada
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsCriarClas($idChamada, $idReservaChamada, &$arrayCmds) {

        // ajustando string de comparação e recuperando flags
        $strCompReserva = $idReservaChamada == ReservaVagaChamada::$ID_PUBLICO_GERAL ? " IS NULL" : " = '$idReservaChamada'";
        $classifUtilizada = FLAG_BD_SIM;


        $arrayCmds [] = "SET @counter = 0";

        $arrayCmds [] = "insert into tb_cir_clas_inscricao_reserva (IPR_ID_INSCRICAO, RVC_ID_RESERVA_CHAMADA, CIR_NR_CLASSIFICACAO_CAND, CIR_CLASSIF_UTILIZADA)
                        SELECT 
                            IPR_ID_INSCRICAO, $idReservaChamada, @counter := @counter + 1,  (CASE
                                WHEN ipr.RVC_ID_RESERVA_CHAMADA $strCompReserva THEN '$classifUtilizada'
                                ELSE NULL
                            END)
                        FROM
                            tb_ipr_inscricao_processo ipr
                        WHERE
                            PCH_ID_CHAMADA = '$idChamada'
                            and IPR_NR_CLASSIFICACAO_CAND IS NOT NULL
                        order by RVC_ID_RESERVA_CHAMADA $strCompReserva desc, IPR_NR_CLASSIFICACAO_CAND";
    }

    /* GET FIELDS FROM TABLE */

    function getIPR_ID_INSCRICAO() {
        return $this->IPR_ID_INSCRICAO;
    }

    /* End of get IPR_ID_INSCRICAO */

    function getRVC_ID_RESERVA_CHAMADA() {
        return $this->RVC_ID_RESERVA_CHAMADA;
    }

    /* End of get RVC_ID_RESERVA_CHAMADA */

    function getCIR_NR_CLASSIFICACAO_CAND() {
        return $this->CIR_NR_CLASSIFICACAO_CAND;
    }

    /* End of get CIR_NR_CLASSIFICACAO_CAND */

    function getCIR_CLASSIF_UTILIZADA() {
        return $this->CIR_CLASSIF_UTILIZADA;
    }

    /* End of get CIR_CLASSIF_UTILIZADA */



    /* SET FIELDS FROM TABLE */

    function setIPR_ID_INSCRICAO($value) {
        $this->IPR_ID_INSCRICAO = $value;
    }

    /* End of SET IPR_ID_INSCRICAO */

    function setRVC_ID_RESERVA_CHAMADA($value) {
        $this->RVC_ID_RESERVA_CHAMADA = $value;
    }

    /* End of SET RVC_ID_RESERVA_CHAMADA */

    function setCIR_NR_CLASSIFICACAO_CAND($value) {
        $this->CIR_NR_CLASSIFICACAO_CAND = $value;
    }

    /* End of SET CIR_NR_CLASSIFICACAO_CAND */

    function setCIR_CLASSIF_UTILIZADA($value) {
        $this->CIR_CLASSIF_UTILIZADA = $value;
    }

    /* End of SET CIR_CLASSIF_UTILIZADA */
}

?>
