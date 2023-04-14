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

class FiltroCurso extends Filtro {

    private static $telaNmCurso = "nmCurso";
    private static $telaIdDepartamento = "idDepartamento";
    private static $telaTpCurso = "tpCurso";
    private static $telaStSituacao = "stCurso";

    public function __construct($vet, $urlInicial, $variante = "") {
        parent::__construct($vet, $urlInicial, $variante);

        // definindo filtro aberto ou fechado
        $this->filtroAberto = !Util::vazioNulo($this->vetParamsCookie[self::$telaIdDepartamento]) || !Util::vazioNulo($this->vetParamsCookie[self::$telaTpCurso]);
    }

    protected function getCompNmCookie() {
        return "filtroCurso";
    }

    protected function strVetGetPrimeiraChamada() {
        return "";
    }

    protected function getParamsTela() {
        if (Util::vazioNulo(self::$paramsTela)) {
            // carregando
            self::$paramsTela = array(self::$telaNmCurso, self::$telaIdDepartamento, self::$telaTpCurso, self::$telaStSituacao);
        }
        return self::$paramsTela;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        $ret .= $this->getNmCurso() != NULL ? self::$telaNmCurso . "={$this->getNmCurso()}" : "";
        $ret .= $this->getIdDepartamento() != NULL ? "&" . self::$telaIdDepartamento . "={$this->getIdDepartamento()}" : "";
        $ret .= $this->getTpCurso() != NULL ? "&" . self::$telaTpCurso . "={$this->getTpCurso()}" : "";
        $ret .= $this->getStSituacao() != NULL ? "&" . self::$telaStSituacao . "={$this->getStSituacao()}" : "";
        return $ret;
    }

    public function getNmCurso() {
        return $this->vetParamsCookie[self::$telaNmCurso];
    }

    public function getIdDepartamento() {
        return $this->vetParamsCookie[self::$telaIdDepartamento];
    }

    public function getTpCurso() {
        return $this->vetParamsCookie[self::$telaTpCurso];
    }

    public function getStSituacao() {
        return $this->vetParamsCookie[self::$telaStSituacao];
    }

}

?>
