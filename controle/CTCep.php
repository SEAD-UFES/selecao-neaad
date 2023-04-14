<?php

/*
 * Classe de controle para o objeto Estado
 */

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/negocio/Cep.php";
require_once $CFG->rpasta . "/util/Mensagem.php";

function getEnderecoCEPCT($nrCep) {
    try {
        return CEP::getEnderecoCEP($nrCep);
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }
}

?>
