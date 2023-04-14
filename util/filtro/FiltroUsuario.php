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

class FiltroUsuario extends Filtro {

    private static $telaDsNome = "dsNome";
    private static $telaDsEmail = "dsEmail";
    private static $telaTpUsuario = "tpUsuario";
    private static $telaNrcpf = "nrcpf";
    private static $telaStSituacao = "stUsu";

    public function __construct($vet, $urlInicial, $variante = "") {
        parent::__construct($vet, $urlInicial, $variante);

        // definindo filtro aberto ou fechado
        $this->filtroAberto = !Util::vazioNulo($this->vetParamsCookie[self::$telaDsEmail]) || !Util::vazioNulo($this->vetParamsCookie[self::$telaStSituacao]);
    }

    protected function getCompNmCookie() {
        return "filtroUsu";
    }

    protected function strVetGetPrimeiraChamada() {
        return "";
    }

    protected function getParamsTela() {
        if (Util::vazioNulo(self::$paramsTela)) {
            // carregando
            self::$paramsTela = array(self::$telaDsNome, self::$telaDsEmail, self::$telaTpUsuario, self::$telaNrcpf, self::$telaStSituacao);
        }
        return self::$paramsTela;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        $ret .= $this->getDsNome() != NULL ? self::$telaDsNome . "={$this->getDsNome()}" : "";
        $ret .= $this->getDsEmail() != NULL ? "&" . self::$telaDsEmail . "={$this->getDsEmail()}" : "";
        $ret .= $this->getTpUsuario() != NULL ? "&" . self::$telaTpUsuario . "={$this->getTpUsuario()}" : "";
        $ret .= $this->getNrcpf() != NULL ? "&" . self::$telaNrcpf . "={$this->getNrcpf()}" : "";
        $ret .= $this->getStSituacao() != NULL ? "&" . self::$telaStSituacao . "={$this->getStSituacao()}" : "";
        return $ret;
    }

    public function getDsNome() {
        return $this->vetParamsCookie[self::$telaDsNome];
    }

    public function getDsEmail() {
        return $this->vetParamsCookie[self::$telaDsEmail];
    }

    public function getTpUsuario() {
        return $this->vetParamsCookie[self::$telaTpUsuario];
    }

    public function getStSituacao() {
        return $this->vetParamsCookie[self::$telaStSituacao];
    }

    public function getNrcpf() {
        if ($this->vetParamsCookie[self::$telaNrcpf] != NULL) {
            return removerMascara("###.###.###-##", $this->vetParamsCookie[self::$telaNrcpf]);
        }
        return NULL;
    }

}

?>
