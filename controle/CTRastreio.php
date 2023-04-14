<?php
require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/negocio/UsuarioRastreio.php";

function RAT_criarRastreioEditalCT($idUsuario, $idProcesso) {
    try {
        return UsuarioRastreio::criarRastreioEdital($idUsuario, $idProcesso);
    } catch (NegocioException $n) {
        // registrando erro, mas sem alarde
        error_log($n->getMensagem());
        return;
    } catch (Exception $e) {
        // registrando erro, mas sem alarde
        error_log($e->getMessage());
        return;
    }
}

function RAT_criarRastreioInscricaoEditalCT($idUsuario, $idProcesso, $idChamada) {
    try {
        return UsuarioRastreio::criarRastreioInscricaoEdital($idUsuario, $idProcesso, $idChamada);
    } catch (NegocioException $n) {
        // registrando erro, mas sem alarde
        error_log($n->getMensagem());
        return;
    } catch (Exception $e) {
        // registrando erro, mas sem alarde
        error_log($e->getMessage());
        return;
    }
}

function RAT_buscarRastreioPorFiltroCT($idUsuario, $idProcesso, $idChamada = NULL, $tpRastreio = NULL, $inicioDados = NULL, $qtdeDados = NULL) {
    try {
        return UsuarioRastreio::buscarRastreioPorFiltro($idUsuario, $idProcesso, $idChamada, $tpRastreio, $inicioDados, $qtdeDados);
    } catch (NegocioException $n) {
        // registrando erro, mas sem alarde
        error_log($n->getMensagem());
        return array();
    } catch (Exception $e) {
        // registrando erro, mas sem alarde
        error_log($e->getMessage());
        return array();
    }
}

function RAT_removerRastreioCT($idRastreio) {
    try {
        return UsuarioRastreio::removerRastreio($idRastreio);
    } catch (NegocioException $n) {
        // registrando erro, mas sem alarde
        error_log($n->getMensagem());
    } catch (Exception $e) {
        // registrando erro, mas sem alarde
        error_log($e->getMessage());
    }
}

function RAT_getSqlRemoverRastreioPorFiltroCT($idUsuario = NULL, $idProcesso = NULL, $idChamada = NULL) {
    try {
        return UsuarioRastreio::getSqlRemoverRastreioPorFiltro($idUsuario, $idProcesso, $idChamada);
    } catch (NegocioException $n) {
        // registrando erro, mas sem alarde
        error_log($n->getMensagem());
    } catch (Exception $e) {
        // registrando erro, mas sem alarde
        error_log($e->getMessage());
    }
}

function RAT_criarDadosSessaoInscProcesso($idProcesso, $dsCompleta) {
    sessaoDados_setDados("idProcessoInscricao", $idProcesso);
    sessaoDados_setDados("dsProcessoInscricao", $dsCompleta);
}

function RAT_removerDadosSessaoInscProcesso() {
    sessaoDados_removerDados("idProcessoInscricao");
    sessaoDados_removerDados("dsProcessoInscricao");
}

function RAT_imprimeListaUltimosEditaisVistos($idUsuario) {
    global $CFG;

    // buscando dados
    $listaRastreio = RAT_buscarRastreioPorFiltroCT($idUsuario, NULL, NULL, UsuarioRastreio::$TP_RASTREIO_EDITAL);

    // verificando se existem editais
    if (count($listaRastreio) > 0) {
        ?>
        <div class="col-md-12 col-sm-12 col-xs-12">
            <ul>
                <?php
                foreach ($listaRastreio as $rastreio) {
                    $processo = buscarProcessoPorIdCT($rastreio->getPRC_ID_PROCESSO_REL());
                    ?>
                    <li><a href="<?php echo "$CFG->rwww/{$rastreio->getURT_DS_URL_ACESSO()}"; ?>"><?php echo $processo->getDsEditalCompleta(); ?></a></li>
                <?php }
                ?>
            </ul>
        </div>
    <?php } else {
        ?>
        <div class="callout callout-info">
            Você ainda não visitou editais.
        </div>
        <?php
    }
}
?>