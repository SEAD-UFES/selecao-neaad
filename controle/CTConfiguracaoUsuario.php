<?php

/*
 * Controle da classe <Configuracao>
 */

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/util/Mensagem.php";
require_once $CFG->rpasta . "/negocio/ConfiguracaoUsuario.php";

//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctconfiguracaousuario") {
    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];
        //caso editar
        if ($acao == "editarConfiguracao") {
            try {
                //recuperando parâmetro
                $idUsuario = getIdUsuarioLogado();

                $conf = new ConfiguracaoUsuario($_POST['idConfiguracao'], $idUsuario, $_POST['qtRegistros'], (isset($_POST['atualizacoesProcesso']) ? $_POST['atualizacoesProcesso'] : NULL), (isset($_POST['salvarFiltro']) ? $_POST['salvarFiltro'] : NULL), (isset($_POST['atualizacoesAdministrador']) ? $_POST['atualizacoesAdministrador'] : NULL));

                //atualizando
                $conf->editarConfiguracao();

                //redirecionando
                new Mensagem("Configuração alterada com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucConf", "$CFG->rwww/visao/usuario/manterConfiguracao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        } else {
            //chamando página de erro
            new Mensagem("Chamada de função inconsistente.", Mensagem::$MENSAGEM_ERRO);
        }
    }
}

function buscarConfiguracaoPorUsuarioCT($idUsuario) {
    try {
        return ConfiguracaoUsuario::buscarConfiguracaoPorUsuario($idUsuario);
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
