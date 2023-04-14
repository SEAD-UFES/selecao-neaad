<?php

/**
 * Description of FiltroRecurso
 *
 * @author Estevão Costa
 */
global $CFG;

require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroRecurso extends Filtro {

    private $idInscricao;
    private $idEtapa;
    private $idProcesso;
    private static $telaIdRecurso = "idRecurso";
    private static $telaStSituacao = "stRecurso";
    private static $telaIdEtapa = "idEtapaAval";
    private static $telaIdChamada = "idChamada";
    private static $telaIdProcesso = "idProcesso";
    private static $telaOrdemInsc = "ordemInsc";
    private static $idProcParamTela = "idProcesso"; // parâmetro passado no get, mas não utilizado para fins de filtro

    public function __construct($vet, $urlInicial, $idInscricao = NULL, $idEtapa = NULL, $idProcesso = NULL, $variante = "", $salvarCookie = NULL) {
        parent::__construct($vet, $urlInicial, $variante, $salvarCookie);

        // caso de dados enviados diretamente no parametro
        $this->idInscricao = $idInscricao;
        $this->idEtapa = $idEtapa;
        $this->idProcesso = $idProcesso;

        // consertando chamada do processo
        if (!Util::vazioNulo($this->idProcesso)) {
            if (!isChamadaDoProcessoCT($this->vetParamsCookie[self::$telaIdChamada], $this->idProcesso)) {
                $proc = buscarProcessoComPermissaoCT($this->idProcesso);
                $this->vetParamsCookie[self::$telaIdChamada] = $proc->PCH_ID_ULT_CHAMADA;

                // verificando etapa em recurso
                $etapa = buscarEtapaEmRecursoCT($this->vetParamsCookie[self::$telaIdChamada]);

                $this->vetParamsCookie[self::$telaIdEtapa] = $etapa != NULL ? $etapa->getEAP_ID_ETAPA_AVAL_PROC() : NULL;
            }
        }

        // definindo filtro aberto ou fechado
        $this->filtroAberto = !Util::vazioNulo($this->vetParamsCookie[self::$telaIdRecurso]) || !Util::vazioNulo($this->vetParamsCookie[self::$telaOrdemInsc]);

        // salvando cookie correto
        $this->salvaCookie($this->getNmCookie());
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        $ret .= $this->getIdRecurso() != NULL ? self::$telaIdRecurso . "={$this->getIdRecurso()}" : "";
        $ret .= $this->getStSituacao() != NULL ? "&" . self::$telaStSituacao . "={$this->getStSituacao()}" : "";
        $ret .= $this->getIdEtapaTela() != NULL ? "&" . self::$telaIdEtapa . "={$this->getIdEtapaTela()}" : "";
        $ret .= $this->getOrdemInsc() != NULL ? "&" . self::$telaOrdemInsc . "={$this->getOrdemInsc()}" : "";
        $ret .= $this->getIdChamada() != NULL ? "&" . self::$telaIdChamada . "={$this->getIdChamada()}" : "";
        $ret .= $this->getIdProcesso() != NULL ? "&" . self::$telaIdProcesso . "={$this->getIdProcesso()}" : "";
        return $ret;
    }

    protected function getCompNmCookie() {
        return "filtroRec";
    }

    protected function strVetGetPrimeiraChamada() {
        return self::$idProcParamTela;
    }

    protected function getParamsTela() {
        if (Util::vazioNulo(self::$paramsTela)) {
            self::$paramsTela = array(self::$telaIdRecurso, self::$telaStSituacao, self::$telaIdChamada, self::$telaOrdemInsc, self::$telaIdEtapa);
        }
        return self::$paramsTela;
    }

    public function getIdInscricao() {
        return $this->idInscricao;
    }

    public function getOrdemInsc() {
        return $this->vetParamsCookie[self::$telaOrdemInsc];
    }

    public function getIdEtapa() {
        return $this->idEtapa;
    }

    public function getIdProcesso() {
        return $this->idProcesso;
    }

    public function getIdEtapaTela() {
        return $this->vetParamsCookie[self::$telaIdEtapa];
    }

    public function getIdChamada() {
        return $this->vetParamsCookie[self::$telaIdChamada];
    }

    public function getIdRecurso() {
        return $this->vetParamsCookie[self::$telaIdRecurso];
    }

    public function getStSituacao() {
        return $this->vetParamsCookie[self::$telaStSituacao];
    }

    public function setIdEtapa($idEtapa) {
        $this->idEtapa = $idEtapa;
    }

}

?>
