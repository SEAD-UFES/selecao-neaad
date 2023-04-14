<?php

/**
 * Classe que implementa a funcao Sem Cadastro de reserva
 * 
 * Ela pode ser usada como Cadastro de reserva
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";

class SemCadastroReserva extends MacroAbs implements MacroCritCadReserva {

    public static $ID_MACRO_SEM_CADASTRO_RESERVA = "semCadastroReserva";

    public function __construct($tpMacro, $paramExt = NULL) {
        parent::__construct($tpMacro, $paramExt);

        $this->parametros = array();
        $this->qtParametros = count($this->parametros);
    }

    public function getNmFantasia() {
        return "Sem Cadastro de Reserva";
    }

    public function getIdMacro() {
        return self::$ID_MACRO_SEM_CADASTRO_RESERVA;
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

    public function addSqlsAplicaCriterio($chamada, $sqlInicial, $whereRestritivo, &$arrayCmds) {
        // Sem cadastro de reserva: logo, nada a ser feito.
    }

}
