<?php

/**
 * Description of FiltroPublicacao
 *
 * @author Estevão Costa
 */
global $CFG;

require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroPolo extends Filtro {

    private static $telaDsPolo = "dsPolo";
    private static $telaIdPolo = "idPolo";

    public function __construct($vet, $urlInicial, $variante = "", $salvarCookie = NULL) {
        parent::__construct($vet, $urlInicial, $variante, $salvarCookie);
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        $ret .= $this->getIdPolo() != NULL ? self::$telaIdPolo . "={$this->getIdPolo()}" : "";
        $ret .= $this->getDsPolo() != NULL ? "&" . self::$telaDsPolo . "={$this->getDsPolo()}" : "";
        return $ret;
    }

    protected function getCompNmCookie() {
        return "filtroPolo";
    }

    protected function strVetGetPrimeiraChamada() {
        return "";
    }

    protected function getParamsTela() {
        if (Util::vazioNulo(self::$paramsTela)) {
            self::$paramsTela = array(self::$telaIdPolo, self::$telaDsPolo);
        }
        return self::$paramsTela;
    }

    public function getDsPolo() {
        return $this->vetParamsCookie[self::$telaDsPolo];
    }

    public function getIdPolo() {
        return $this->vetParamsCookie[self::$telaIdPolo];
    }

}

?>
