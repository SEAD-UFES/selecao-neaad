<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FiltroUsuario
 *
 * @author Estevão Costa
 */
global $CFG;
require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroDepartamento extends Filtro {

    private static $telaDsNome = "dsNome";
    private static $telaStSituacao = "stDep";

    public function __construct($vet, $urlInicial, $variante = "") {
        parent::__construct($vet, $urlInicial, $variante);
    }

    protected function getCompNmCookie() {
        return "filtroDepartamento";
    }

    protected function strVetGetPrimeiraChamada() {
        return "";
    }

    protected function getParamsTela() {
        if (Util::vazioNulo(self::$paramsTela)) {
            // carregando
            self::$paramsTela = array(self::$telaDsNome, self::$telaStSituacao);
        }
        return self::$paramsTela;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        $ret .= $this->getDsNome() != NULL ? self::$telaDsNome . "={$this->getDsNome()}" : "";
        $ret .= $this->getStSituacao() != NULL ? self::$telaStSituacao . "={$this->getStSituacao()}" : "";
        return $ret;
    }

    public function getDsNome() {
        return $this->vetParamsCookie[self::$telaDsNome];
    }

    public function getStSituacao() {
        return $this->vetParamsCookie[self::$telaStSituacao];
    }

}

?>
