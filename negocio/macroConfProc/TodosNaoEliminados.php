<?php

/**
 * Classe que implementa a funcao de Todos Classificados
 * 
 * Ela pode ser usada como Criterio de Seleção ou cadastro de reserva
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";

class TodosNaoEliminados extends MacroAbs implements MacroCritSelecao, MacroCritCadReserva {

    public function __construct($tpMacro, $paramExt = NULL) {
        parent::__construct($tpMacro, $paramExt);

        $this->parametros = array();
        $this->qtParametros = count($this->parametros);
    }

    public function getNmFantasia() {
        if ($this->tpMacro == MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA) {
            return "Todos Aprovados";
        }
        return "Todos Não Eliminados";
    }

    public function getIdMacro() {
        return "todosNaoEliminados";
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

    public function addSqlsAplicaCriterioEtapa($chamada, $sqlInicial, $whereRestritivo, &$arrayCmds) {
        // Aprovando todos os candidatos
        $arrayCmds [] = $sqlInicial;
    }

    public function addSqlsAplicaCriterio($chamada, $sqlInicial, $whereRestritivo, &$arrayCmds) {
        // Marcando todos os candidatos como cadastro de reserva
        $arrayCmds [] = $sqlInicial;
    }

}
