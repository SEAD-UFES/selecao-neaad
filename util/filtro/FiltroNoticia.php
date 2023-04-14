<?php

/**
 * Description of FiltroNoticia
 *
 * @author Estevão Costa
 */
global $CFG;

require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroNoticia extends Filtro {

    private $idProcesso;
    private $idChamada;

    public function __construct($vet, $urlInicial, $idProcesso, $variante = "", $salvarCookie = NULL) {
        parent::__construct($vet, $urlInicial, $variante, $salvarCookie);
        $this->idProcesso = $idProcesso;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial;
        if (!strpos($this->urlInicial, "?")) {
            $ret .= "?";
        }
        return $ret;
    }

    protected function getCompNmCookie() {
        return "filtroNot";
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

    public function getIdChamada() {
        return $this->idChamada;
    }

    function setIdChamada($idChamada) {
        $this->idChamada = $idChamada;
    }

}

?>
