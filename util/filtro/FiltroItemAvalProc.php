<?php

/**
 * Description of FiltroPublicacao
 *
 * @author Estevão Costa
 */
global $CFG;

require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroItemAvalProc extends Filtro {

    private $idCategoriaAval;
    private $idProcesso;
    private $edicao;

    public function __construct($vet, $urlInicial, $idCategoriaAval, $idProcesso, $edicao = FALSE, $variante = "", $salvarCookie = NULL) {
        parent::__construct($vet, $urlInicial, $variante, $salvarCookie);
        $this->idCategoriaAval = $idCategoriaAval;
        $this->idProcesso = $idProcesso;
        $this->edicao = $edicao;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        return $ret;
    }

    protected function getCompNmCookie() {
        return "filtroItemAval";
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

    public function getIdCategoriaAval() {
        return $this->idCategoriaAval;
    }

    public function getIdProcesso() {
        return $this->idProcesso;
    }

    public function getEdicao() {
        return $this->edicao;
    }

}

?>
