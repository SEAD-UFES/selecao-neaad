<?php

/**
 * Classe que implementa a funcao de Idade maior que 60 anos
 * 
 * Ela pode ser usada como Criterio de Desempate
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";

class IdadeMaior60 extends MacroAbs implements MacroCritDesempate {

    public function __construct($tpMacro, $paramExt = NULL) {
        parent::__construct($tpMacro, $paramExt);

        $this->parametros = array();
        $this->qtParametros = count($this->parametros);
    }

    public function getNmFantasia() {
        return "Idade >= 60";
    }

    public function getIdMacro() {
        return "idadeMaior60";
    }

    public function getListaIdParam() {
        return array_keys($this->parametros);
    }

    public function getParamPorId($idParam) {
        if (!isset($this->parametros[$idParam])) {
            throw new NegocioException("Parâmetro inexistente!");
        }
        return $this->parametros[$idParam];
    }

    public function getListaParam() {
        return array_values($this->parametros);
    }

    public function getQtdeParametros() {
        return $this->qtParametros;
    }

    public function getSqlAddOrderByAplicaCriterio() {
        return "(SELECT 
                    TIMESTAMPDIFF(YEAR,
                        IDC_NASC_DATA,
                        curdate()) < 60
                FROM
                    tb_idc_identificacao_candidato idc
                        JOIN
                    tb_cdt_candidato cdt ON idc.IDC_ID_IDENTIFICACAO_CDT = cdt.IDC_ID_IDENTIFICACAO_CDT
                WHERE
                    cdt.CDT_ID_CANDIDATO = ipr.CDT_ID_CANDIDATO)";
    }

}
