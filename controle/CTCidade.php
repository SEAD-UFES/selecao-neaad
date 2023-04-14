<?php

/*
 * Classe de controle para o objeto Cidade
 */

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/negocio/Cidade.php";
require_once $CFG->rpasta . "/negocio/NGUtil.php";

function buscarCidadePorUfCT($idUf) {
    try {
        return Cidade::buscarCidadePorUf($idUf);
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
