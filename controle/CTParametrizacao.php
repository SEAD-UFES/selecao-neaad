<?php

/*
 * Classe de controle para o objeto Pais
 */

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/negocio/Polo.php";
require_once $CFG->rpasta . "/negocio/TipoCurso.php";
require_once $CFG->rpasta . "/negocio/TipoCargo.php";

function buscarPoloPorFiltroCT($idPolo, $dsPolo, $inicioDados, $qtdeDados) {
    try {
        return Polo::buscarPoloPorFiltro($idPolo, $dsPolo, $inicioDados, $qtdeDados);
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

function buscarTodosPolosCT() {
    try {
        return Polo::buscarTodosPolos();
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

function buscarPolosPorIdsCT($idPolos) {
    try {
        return Polo::buscarPolosPorIds($idPolos);
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

/**
 * 
 * @param FiltroPolo $filtroPolo
 * @return int
 */
function contarPoloPorFiltroCT($filtroPolo) {
    try {
        return Polo::contarPoloPorFiltro($filtroPolo->getIdPolo(), $filtroPolo->getDsPolo());
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

function buscarTodosTiposCursoCT() {
    try {
        return TipoCurso::buscarTodosTiposCurso();
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

/**
 * 
 * @param boolean $completo Diz se é para retornar os dados na forma completa, ou seja, o objeto TipoCargo.
  Se for false, então é retornado um vetor na forma id -> nome
 * 
 * @param char $stSituacao Situação dos cargos - Ativo ou Inativo
 * 
 * @return TipoCargo Array com todos os tipos de cargo
 */
function buscarTodosTiposCargoCT($completo = TRUE, $stSituacao = NULL) {
    try {
        return TipoCargo::buscarTodosTiposCargo($completo, $stSituacao);
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

function buscarIdTipoCargoPorUrlBuscaCT($urlBusca) {
    try {
        return TipoCargo::buscarIdTipoCargoPorUrlBusca($urlBusca);
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

/**
 * 
 * @param FiltroPolo $filtroPolo
 * @return string
 */
function tabelaPoloPorFiltro($filtroPolo) {

    //recuperando polos
    $polos = buscarPoloPorFiltroCT($filtroPolo->getIdPolo(), $filtroPolo->getDsPolo(), $filtroPolo->getInicioDados(), $filtroPolo->getQtdeDadosPag());

    if (count($polos) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<table class='table table-hover table-bordered'>
        <thead><tr>
        <th style='width:10%;'>Código</th>
        <th style='width:90%;'>Nome</th>
    </tr></thead>";

    //iteração para exibir dados
    foreach ($polos as $id => $nome) {

        $ret .= "<tr>
        <td style='width:10%;'>$id</td>
        <td style='width:90%;'>$nome</td>
        </tr>";
    }

    $ret .= "</table>";
    return $ret;
}

function tabelaTiposFormacao() {

    //recuperando polos
    $tiposFormacao = buscarTodosTiposCursoCT();

    if (count($tiposFormacao) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = " <table class='table table-hover table-bordered'>
        <thead>
        <tr>
        <th style='width:10%;'>Código</th>
        <th style='width:90%;'>Nome</th>
    </tr></thead>";

    //iteração para exibir dados
    foreach ($tiposFormacao as $id => $nome) {

        $ret .= "<tr>
        <td style='width:10%;'>$id</td>
        <td style='width:90%;'>$nome</td>
        </th>";
    }

    $ret .= "</table>";
    return $ret;
}

function tabelaTiposAtribuicao() {

    //recuperando polos
    $tiposAtribuicao = buscarTodosTiposCargoCT(TRUE, NGUtil::getSITUACAO_ATIVO());

    if (count($tiposAtribuicao) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = " <table class='table table-hover table-bordered'>
        <thead>
        <tr>
        <th style='width:10%;'>Código</th>
        <th>Nome</th>
        <th title='Nome utilizado quando o tipo de atribuição aparece em uma URL'>URL</th>
    </tr></thead>";

    //iteração para exibir dados
    foreach ($tiposAtribuicao as $tipo) {

        $ret .= "<tr>
        <td style='width:10%;'>{$tipo->getTIC_ID_TIPO()}</td>
        <td>{$tipo->getTIC_NM_TIPO()}</td>
        <td>{$tipo->getTIC_URL_BUSCA()}</td>
        </tr>";
    }

    $ret .= "</table>";
    return $ret;
}

?>
