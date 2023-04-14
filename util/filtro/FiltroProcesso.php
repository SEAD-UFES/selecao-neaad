<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FiltroProcesso
 *
 * @author Estevão Costa
 */
global $CFG;
require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroProcesso extends Filtro {

    private $idCurso;
    private $tpFormacao;
    private $cursoForcado;
    private $avisoErrEdital; // Use com moderação: Apenas para UrlAmigavel
    private static $telaCurso = "idCurso";
    private static $telaIdProc = "idProcesso";
    private static $telaTpCargo = "idTipoCargo";
    private static $telaTpFormacao = "tpFormacao";
    private static $telaAnoEdital = "anoEdital";
    private static $telaNrEdital = "nrEdital";
    // campos de url amigavel
    private static $amigavel_nmCurso = "nmCurso";
    private static $amigavel_tpCargo = "nmTipoCargo";

    /**
     * 
     * @param array $vet
     * @param boolean $loginRestrito
     * @param string $urlInicial
     * @param int $idUsuario
     * @param string $pagVolta
     * @param string $variante
     * @param Curso $curso
     * @param boolean $urlAmigavel
     * @param boolean $telaPublica Diz se o filtro é de uma tela pública
     */
    public function __construct($vet, $loginRestrito, $urlInicial, $idUsuario = NULL, $variante = "", $curso = NULL, $urlAmigavel = FALSE, $telaPublica = FALSE) {
        // manipular cookie
        $manipularCookie = NULL;

        if ($urlAmigavel) {

            // nome do curso
            $nmCurso = isset($_GET[self::$amigavel_nmCurso]) ? $_GET[self::$amigavel_nmCurso] : NULL;

            if ($nmCurso != NULL) {
                $vet[self::$telaCurso] = buscarIdCursoPorUrlBuscaCT($nmCurso);
                $this->avisoErrEdital = Util::vazioNulo($vet[self::$telaCurso]);
                $manipularCookie = FALSE;
            }

            // tipo de cargo
            $nmTpCargo = isset($_GET[self::$amigavel_tpCargo]) ? $_GET[self::$amigavel_tpCargo] : NULL;

            if ($nmTpCargo != NULL) {
                $vet[self::$telaTpCargo] = buscarIdTipoCargoPorUrlBuscaCT($nmTpCargo);
                $this->avisoErrEdital = $this->avisoErrEdital || Util::vazioNulo($vet[self::$telaTpCargo]);
                $manipularCookie = FALSE;
            }
        }

        // chamando construtor pai
        parent::__construct($vet, $urlInicial, $variante, $manipularCookie);

        //casos especificos
        if ($loginRestrito) {
            $idUsuario = getIdUsuarioLogado();
            if (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
                $this->idCurso = "(select `CUR_ID_CURSO` from tb_cur_curso where `CUR_ID_COORDENADOR` = $idUsuario)";
                $this->tpFormacao = "";
            } elseif (estaLogado(Usuario::$USUARIO_AVALIADOR)) {
                // usuario avaliador
                $this->idCurso = "(select `USR_ID_CUR_AVALIADOR` from tb_usr_usuario where `USR_ID_USUARIO` = $idUsuario)";
                $this->tpFormacao = "";
            }
            $this->cursoForcado = TRUE;
            $this->vetParamsCookie[self::$telaCurso] = $curso->getCUR_ID_CURSO();
            $this->vetParamsCookie[self::$telaTpFormacao] = $curso->getTPC_ID_TIPO_CURSO();
        } else {
            $this->idCurso = $this->vetParamsCookie[self::$telaCurso];
            if (Util::vazioNulo($this->vetParamsCookie[self::$telaCurso])) {
                $this->tpFormacao = $this->vetParamsCookie[self::$telaTpFormacao];
            } else {
                $this->tpFormacao = NULL;
            }
            $this->cursoForcado = FALSE;
        }

        //idUsuario
        $this->idUsuario = $idUsuario;

        // filtro aberto?
        if ($telaPublica) {
            $this->filtroAberto = !Util::vazioNulo($this->getIdTipoCargo()) || !Util::vazioNulo($this->getNrEdital()) || !Util::vazioNulo($this->getAnoEdital());
        } else {
            // Admin tem linha diferenciada
            $this->filtroAberto = !$loginRestrito && (!Util::vazioNulo($this->getIdCursoTela()) || !Util::vazioNulo($this->getTpFormacaoTela()));
        }
    }

    protected function getCompNmCookie() {
        return "filtroProc";
    }

    protected function strVetGetPrimeiraChamada() {
        return "";
    }

    protected function getParamsTela() {
        if (Util::vazioNulo(self::$paramsTela)) {
            // carregando
            self::$paramsTela = array(self::$telaIdProc, self::$telaAnoEdital, self::$telaCurso, self::$telaNrEdital, self::$telaTpCargo, self::$telaTpFormacao);
        }
        return self::$paramsTela;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        $ret .= $this->getIdProcesso() != NULL ? self::$telaIdProc . "={$this->getIdProcesso()}" : "";
        $ret .= $this->getNrEdital() != NULL ? "&" . self::$telaNrEdital . "={$this->getNrEdital()}" : "";
        $ret .= $this->getAnoEdital() != NULL ? "&" . self::$telaAnoEdital . "={$this->getAnoEdital()}" : "";
        $ret .= $this->getIdCursoTela() != NULL ? "&" . self::$telaCurso . "={$this->getIdCursoTela()}" : "";
        $ret .= $this->getIdTipoCargo() != NULL ? "&" . self::$telaTpCargo . "={$this->getIdTipoCargo()}" : "";
        $ret .= $this->getTpFormacaoTela() != NULL ? "&" . self::$telaTpFormacao . "={$this->getTpFormacaoTela()}" : "";
        return $ret;
    }

    function getAvisoErrEdital() {
        return $this->avisoErrEdital;
    }

    public function getIdCurso() {
        return $this->idCurso;
    }

    public function getIdCursoTela() {
        return $this->vetParamsCookie[self::$telaCurso];
    }

    public function getIdProcesso() {
        return $this->vetParamsCookie[self::$telaIdProc];
    }

    public function getIdTipoCargo() {
        return $this->vetParamsCookie[self::$telaTpCargo];
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function getNrEdital() {
        return $this->vetParamsCookie[self::$telaNrEdital];
    }

    public function getAnoEdital() {
        return $this->vetParamsCookie[self::$telaAnoEdital];
    }

    public function getTpFormacaoTela() {
        return $this->vetParamsCookie[self::$telaTpFormacao];
    }

    public function getTpFormacao() {
        return $this->tpFormacao;
    }

    public function getCursoForcado() {
        return $this->cursoForcado;
    }

}

?>
