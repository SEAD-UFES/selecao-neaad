<?php

/**
 * Description of FiltroAtuacao
 *
 * @author Estevão Costa
 */
global $CFG;

require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroInfCompProc extends Filtro {

    private $idProcesso;
    private $abaApresentacao;

    public function __construct($vet, $urlInicial, $idProcesso, $abaApresentacao, $variante = "", $salvarCookie = NULL) {
        parent::__construct($vet, $urlInicial, $variante, $salvarCookie);
        $this->idProcesso = $idProcesso;
        $this->abaApresentacao = $abaApresentacao;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        $ret .= $this->abaApresentacao != NULL ? Util::$ABA_PARAM . "=$this->abaApresentacao" : "";
        return $ret;
    }

    protected function getCompNmCookie() {
        return "filtroInfCompProc";
    }

    protected function strVetGetPrimeiraChamada() {
        return "";
    }

    protected function getParamsTela() {
        if (Util::vazioNulo(self::$paramsTela)) {
            self::$paramsTela = array();
        }
        return self::$paramsTela;
    }

    public function getIdProcesso() {
        return $this->idProcesso;
    }

}

?>
