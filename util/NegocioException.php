<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NegocioException
 *
 * @author Estevão Costa
 */
class NegocioException extends Exception {

    private static $DEBUG = FALSE;
    private $mensagemCompleta;

    public function __construct($mensagem, $excecao = NULL) {
        # Alterando debug
        global $CFG;
        NegocioException::$DEBUG = $CFG->ambiente == Util::$AMBIENTE_DESENVOLVIMENTO || $CFG->emTeste ? TRUE : FALSE;

        parent::__construct($mensagem);
        if ($excecao != NULL) {
            $this->mensagemCompleta = $excecao->getMessage();
        }
        error_log(parent::getMessage() . "<br/>" . $this->mensagemCompleta);
    }

    public function getMensagem() {
        return parent::getMessage() . (NegocioException::$DEBUG ? "<br/>" . $this->mensagemCompleta : "");
    }

}

?>
