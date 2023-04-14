<?php

/**
 * Classe que implementa a funcao de Nota Decrescente
 * 
 * Ela pode ser usada como Criterio de Classificação
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";

class NotaDecrescente extends MacroAbs implements MacroCritClassificacao {

    public function __construct($tpMacro, $paramExt = NULL) {
        parent::__construct($tpMacro, $paramExt);

        $this->parametros = array();
        $this->qtParametros = count($this->parametros);
    }

    public function getNmFantasia() {
        return "Nota Decrescente";
    }

    public function getIdMacro() {
        return "notaDecrescente";
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

    public function getSqlOrderByAplicaCriterio() {
        return self::getOrderBy();
    }

    public static function getOrderBy() {
        return "IPR_VL_TOTAL_NOTA desc";
    }

}
