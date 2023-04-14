<?php

/**
 * Description of FiltroPublicacao
 *
 * @author Estevão Costa
 */
global $CFG;

require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroPublicacao extends Filtro {

    private $idUsuario;

    public function __construct($vet, $urlInicial, $idUsuario, $variante = "", $salvarCookie = NULL) {
        parent::__construct($vet, $urlInicial, $variante, $salvarCookie);
        $this->idUsuario = $idUsuario;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        return $ret;
    }

    protected function getCompNmCookie() {
        return "filtroPub";
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

    public function getIdUsuario() {
        return $this->idUsuario;
    }

}

?>
