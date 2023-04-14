<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FiltroInscritoProcesso
 *
 * @author Estevão Costa
 */
global $CFG;
require_once $CFG->rpasta . "/util/filtro/Filtro.php";
require_once $CFG->rpasta . "/util/Paginacao.php";

class FiltroInscritoProcesso extends Filtro {

    private $idCurso;
    private $cursoForcado;
    private $admiteGetVazio; // Armazena se o filtro admite chamada com o vetor $_GET vazio
    private static $telaCurso = "idCurso";
    private static $telaIdProc = "idProcesso";
    private static $telaIdPolo = "idPolo";
    private static $telaNmUsu = "nmCandidato";
    private static $telaNrCpf = "nrcpf";
    private static $telaIdCham = "idChamada";
    private static $telaIdAreaAtuacao = "idAreaAtuacao";
    private static $telaIdReservaVaga = "idReservaVaga";
    private static $telaCod = "codigo";
    private static $telaTpClassificacao = "tpClassificacao";
    private static $telaTpOrdenacao = "tpOrdenacao";
    private static $telaTpExibSituacao = "tpExibSituacao";

    public static function getPadraoTpClassificacao() {
        return InscricaoProcesso::$ORDEM_INSCRITOS_NOME;
    }

    public static function getPadraoTpOrdenacao() {
        return InscricaoProcesso::$ORDENACAO_CRESCENTE;
    }

    public static function getPadraoTpExibicaoSit() {
        return InscricaoProcesso::$MOSTRAR_SITUACAO_TODOS;
    }

    public function __construct($vet, $urlInicial, $loginRestrito = FALSE, $variante = "", $admiteGetVazio = FALSE) {
        $this->admiteGetVazio = $admiteGetVazio;
        parent::__construct($vet, $urlInicial, $variante);

        // tratando casos especificos
        if ($loginRestrito) {
            $idUsuario = getIdUsuarioLogado();
            if (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
                $this->idCurso = "(select `CUR_ID_CURSO` from tb_cur_curso where `CUR_ID_COORDENADOR` = $idUsuario)";
            } else {
                // usuario avaliador
                $this->idCurso = "(select `USR_ID_CUR_AVALIADOR` from tb_usr_usuario where `USR_ID_USUARIO` = $idUsuario)";
            }
            $this->cursoForcado = TRUE;
        } else {
            $this->idCurso = $this->vetParamsCookie[self::$telaCurso];
            $this->cursoForcado = FALSE;
        }

        // consertando chamada do processo
        if (!Util::vazioNulo($this->vetParamsCookie[self::$telaIdProc])) {
            if (!isChamadaDoProcessoCT($this->vetParamsCookie[self::$telaIdCham], $this->vetParamsCookie[self::$telaIdProc])) {
                $proc = buscarProcessoComPermissaoCT($this->vetParamsCookie[self::$telaIdProc]);
                $this->vetParamsCookie[self::$telaIdCham] = $proc->PCH_ID_ULT_CHAMADA;
                $this->vetParamsCookie[self::$telaIdPolo] = NULL;
                $this->vetParamsCookie[self::$telaIdAreaAtuacao] = NULL;
                $this->vetParamsCookie[self::$telaIdReservaVaga] = NULL;
            }
        }

        // consertando polo e area no caso de chamada vazia
        if (Util::vazioNulo($this->vetParamsCookie[self::$telaIdCham])) {
            $this->vetParamsCookie[self::$telaIdPolo] = NULL;
            $this->vetParamsCookie[self::$telaIdAreaAtuacao] = NULL;
            $this->vetParamsCookie[self::$telaIdReservaVaga] = NULL;
        }

        // consertando classificacao e ordem
        if (Util::vazioNulo($this->vetParamsCookie[self::$telaTpClassificacao])) {
            $this->vetParamsCookie[self::$telaTpClassificacao] = self::getPadraoTpClassificacao();
        } elseif ($this->vetParamsCookie[self::$telaTpClassificacao] == InscricaoProcesso::$ORDEM_INSCRITOS_CLASSIFICACAO) {
            $etapa = buscarEtapaVigenteCT($this->vetParamsCookie[self::$telaIdCham]);
            if (!permiteMostrarClassificacaoCT($etapa)) {
                $this->vetParamsCookie[self::$telaTpClassificacao] = self::getPadraoTpClassificacao();
            }
        }

        // consertando ordenacao
        if (Util::vazioNulo($this->vetParamsCookie[self::$telaTpOrdenacao])) {
            $this->vetParamsCookie[self::$telaTpOrdenacao] = self::getPadraoTpOrdenacao();
        }

        // consertando situação
        if (Util::vazioNulo($this->vetParamsCookie[self::$telaTpExibSituacao])) {
            $this->vetParamsCookie[self::$telaTpExibSituacao] = self::getPadraoTpExibicaoSit();
        }

        // definindo se o filtro deve aparecer aberto
        $this->filtroAberto = !Util::vazioNulo($this->getNmUsuario()) || !Util::vazioNulo($this->getNrCpf()) || !Util::vazioNulo($this->getIdPolo()) || !Util::vazioNulo($this->getIdAreaAtuacao()) || !Util::vazioNulo($this->getIdReservaVaga());


        // salvando cookie correto
        $this->salvaCookie($this->getNmCookie());
    }

    /*
     * Implementaçao das classes abstratas
     */

    protected function getCompNmCookie() {
        return "filtroInscProc";
    }

    protected function strVetGetPrimeiraChamada() {
        return !$this->admiteGetVazio ? self::$telaIdProc : "";
    }

    protected function getParamsTela() {
        if (Util::vazioNulo(self::$paramsTela)) {
            // carregando
            self::$paramsTela = array(self::$telaIdProc, self::$telaIdPolo, self::$telaIdReservaVaga, self::$telaCod, self::$telaNmUsu, self::$telaNrCpf, self::$telaIdCham, self::$telaIdAreaAtuacao, self::$telaCurso, self::$telaTpClassificacao, self::$telaTpOrdenacao, self::$telaTpExibSituacao);
        }
        return self::$paramsTela;
    }

    public function getUrlParametros() {
        $ret = $this->urlInicial . "?";
        $ret .= $this->getIdProcesso() != NULL ? self::$telaIdProc . "={$this->getIdProcesso()}" : "";
        $ret .= $this->getIdPolo() != NULL ? "&" . self::$telaIdPolo . "={$this->getIdPolo()}" : "";
        $ret .= $this->getIdReservaVaga() != NULL ? "&" . self::$telaIdReservaVaga . "={$this->getIdReservaVaga()}" : "";
        $ret .= $this->getIdAreaAtuacao() != NULL ? "&" . self::$telaIdAreaAtuacao . "={$this->getIdAreaAtuacao()}" : "";
        $ret .= $this->getCodigo() != NULL ? "&" . self::$telaCod . "={$this->getCodigo()}" : "";
        $ret .= $this->getNmUsuario() != NULL ? "&" . self::$telaNmUsu . "={$this->getNmUsuario()}" : "";
        $ret .= $this->getNrCpf() != NULL ? "&" . self::$telaNrCpf . "={$this->getNrCpf()}" : "";
        $ret .= $this->getIdChamada() != NULL ? "&" . self::$telaIdCham . "={$this->getIdChamada()}" : "";
        $ret .= $this->getIdCurso() != NULL ? "&" . self::$telaCurso . "={$this->getIdCurso()}" : "";
        $ret .= $this->getTpClassificacao() != NULL ? "&" . self::$telaTpClassificacao . "={$this->getTpClassificacao()}" : "";
        $ret .= $this->getTpOrdenacao() != NULL ? "&" . self::$telaTpOrdenacao . "={$this->getTpOrdenacao()}" : "";
        $ret .= $this->getTpExibSituacao() != NULL ? "&" . self::$telaTpExibSituacao . "={$this->getTpExibSituacao()}" : "";
        return $ret;
    }

    public function getIdProcesso() {
        return $this->vetParamsCookie[self::$telaIdProc];
    }

    public function getIdPolo() {
        return $this->vetParamsCookie[self::$telaIdPolo];
    }

    public function getNmUsuario() {
        return $this->vetParamsCookie[self::$telaNmUsu];
    }

    public function getNrCpf() {
        return $this->vetParamsCookie[self::$telaNrCpf];
    }

    public function getIdChamada() {
        return $this->vetParamsCookie[self::$telaIdCham];
    }

    public function getIdAreaAtuacao() {
        return $this->vetParamsCookie[self::$telaIdAreaAtuacao];
    }

    public function getIdReservaVaga() {
        return $this->vetParamsCookie[self::$telaIdReservaVaga];
    }

    public function getTpClassificacao() {
        return $this->vetParamsCookie[self::$telaTpClassificacao];
    }

    public function getCodigo() {
        return $this->vetParamsCookie[self::$telaCod];
    }

    public function getIdCurso() {
        return $this->idCurso;
    }

    public function getIdCursoTela() {
        return $this->vetParamsCookie[self::$telaCurso];
    }

    public function getTpOrdenacao() {
        return $this->vetParamsCookie[self::$telaTpOrdenacao];
    }

    public function getTpExibSituacao() {
        return $this->vetParamsCookie[self::$telaTpExibSituacao];
    }

    public function getCursoForcado() {
        return $this->cursoForcado;
    }

    public function setIdPoloTela($idPolo) {
        $this->vetParamsCookie[self::$telaIdPolo] = $idPolo;
    }

    public function setIdAreaAtuChamadaTela($idAreaAtuChamada) {
        $this->vetParamsCookie[self::$telaIdAreaAtuacao] = $idAreaAtuChamada;
    }

    public static function getTelaTpClassificacao() {
        return self::$telaTpClassificacao;
    }

}

?>
