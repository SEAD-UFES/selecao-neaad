<?php

/*
 * Classe de controle para Noticia
 */

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/negocio/Noticia.php";


// Tratando solicitação get para obtenção de notícias
if (isset($_POST['fn']) && isset($_POST['valido']) && $_POST['valido'] == 'noticia') {
    // Recuperar dados de notícia
    if ($_POST['fn'] == 'noticia') {
        $ini = $_POST['ini'];
        print recuperarNoticiaAjax($ini);
    }
}

// Tratando solicitações gerais
if (isset($_POST['valido']) && $_POST['valido'] == "ctnoticia") {
    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];

        if ($acao == "criarNoticia") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                // recuperando parametros básicos
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];

                // link
                $tpLink = isset($_POST['tpLinkNot']) ? $_POST['tpLinkNot'] : NULL;
                $dsLink = $tpLink == Noticia::$TP_LINK_EXTERNO ? $_POST['dsUrlExterno'] : $_POST['dsUrlInterno'];

                // criando objeto
                $noticia = new Noticia(NULL, $_POST['idTipo'], NULL, isset($_POST['dtValidade']) ? $_POST['dtValidade'] : NULL, $_POST['titulo'], $_POST['dsNoticia'], $dsLink, NULL, $idProcesso, $idChamada);

                //criando notícia
                $noticia->criarNoticia($tpLink);

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_NOTICIA;
                new Mensagem('Notícia cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercaoNoticia", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "editarNoticia") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                // recuperando parametros básicos
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $idNoticia = $_POST['idNoticia'];

                // link
                $tpLink = isset($_POST['tpLinkNot']) ? $_POST['tpLinkNot'] : NULL;
                $dsLink = $tpLink == Noticia::$TP_LINK_EXTERNO ? $_POST['dsUrlExterno'] : $_POST['dsUrlInterno'];

                // criando objeto
                $noticia = new Noticia($idNoticia, $_POST['idTipo'], NULL, isset($_POST['dtValidade']) ? $_POST['dtValidade'] : NULL, $_POST['titulo'], $_POST['dsNoticia'], $dsLink, NULL, $idProcesso, $idChamada);

                //atualizando notícia
                $noticia->editarNoticia($tpLink);

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_NOTICIA;
                new Mensagem('Notícia atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtuNoticia", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "excluirNoticia") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                // recuperando parametros básicos
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $idNoticia = $_POST['idNoticia'];

                // algum dado não informado?
                if (Util::vazioNulo($idProcesso) || Util::vazioNulo($idChamada) || Util::vazioNulo($idNoticia)) {
                    throw new NegocioException("Chamada inválida.");
                }

                // excluindo notícia
                $noticia = buscarNoticiaPorIdCT($idNoticia, $idProcesso);
                $noticia->excluirNoticia();

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_NOTICIA;
                new Mensagem('Notícia excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExcNoticia", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }
    }
}

function recuperarNoticiaAjax($ini) {
    try {
        $tpNoticia = estaLogado() != NULL ? NULL : Noticia::$NOTICIA_PUBLICA;

        $noticias = Noticia::buscarUltimasNoticias($tpNoticia, $ini, Noticia::$QT_NOTICIAS_POR_PAG);
        $htmlNoticia = "";
        foreach ($noticias as $noticia) {
            $htmlNoticia .= $noticia->getHtmlNoticia();
        }
        $htmlPaginacao = getHtmlUlPaginacaoNoticia($tpNoticia, $ini);

        return json_encode(array('situacao' => TRUE, 'htmlNoticia' => $htmlNoticia, 'htmlPaginacao' => $htmlPaginacao));
    } catch (Exception $e) {
        return json_encode(array('situacao' => FALSE, 'msg' => $e->getMessage()));
    }
}

/**
 * 
 * @param char $tpNoticia Caracter espeficicando o tipo de notícia a apresentar
 * @param int $inicioDados
 * @param int $qtdeDados
 * @return Noticia Array de notícias
 */
function buscarUltimasNoticiasCT($tpNoticia = NULL, $inicioDados = NULL, $qtdeDados = NULL) {
    try {
        return Noticia::buscarUltimasNoticias($tpNoticia, $inicioDados, $qtdeDados);
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

function buscarNoticiaPorChamadaCT($idProcesso, $idChamada, $tpNoticia = NULL, $inicioDados = NULL, $qtdeDados = NULL) {
    try {
        return Noticia::buscarNoticiaPorChamada($idProcesso, $idChamada, $tpNoticia, $inicioDados, $qtdeDados);
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

function buscarNoticiaPorIdCT($idNoticia, $idProcesso = NULL) {
    try {
        return Noticia::buscarNoticiaPorId($idNoticia, $idProcesso);
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

function contarNoticiaCT($tpNoticia = NULL) {
    try {
        return Noticia::contarNoticia($tpNoticia);
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
 * @param FiltroNoticia $filtroNoticia
 * @return int
 */
function contarNoticiaPorChamadaCT($filtroNoticia) {
    try {
        return Noticia::contarNoticiaPorChamada($filtroNoticia->getIdProcesso(), $filtroNoticia->getIdChamada());
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

function getHtmlUlPaginacaoNoticia($tpNoticia, $inicioDados) {
    // definindo início da próxima página de notícia
    $proxIni = $inicioDados + Noticia::$QT_NOTICIAS_POR_PAG;
    $iniAnterior = max(0, ($inicioDados - Noticia::$QT_NOTICIAS_POR_PAG));

    // Início desabilitado?
    $iniDesabilitado = $inicioDados == 0 ? "disabled" : "";

    // Fim desabilitado?
    $qtNoticias = contarNoticiaCT($tpNoticia);
    $proxDesabilitado = $proxIni >= $qtNoticias ? "disabled" : "";

    // Títulos e onclick
    $tituloAnt = $iniDesabilitado != NULL ? "Não há notícia mais recente" : "Visualizar notícia mais recente";
    $tituloProx = $proxDesabilitado != NULL ? "Não há notícia mais antiga" : "Visualizar notícia mais antiga";

    $onclickAnt = $iniDesabilitado != NULL ? "" : "buscarNoticia($iniAnterior);";
    $onclickProx = $proxDesabilitado != NULL ? "" : "buscarNoticia($proxIni);";

    return "<li title='$tituloAnt' class='previous $iniDesabilitado'><a href='#' onclick='javascript: $onclickAnt return false;' class='no-radius'>← Voltar</a></li>
            <li title='$tituloProx' class='next voarright $proxDesabilitado'><a href='#' onclick='javascript: $onclickProx return false;' class='no-radius'>Mais antigas →</a></li>";
}

/**
 * Esta função imprime a tabela de notícias da chamada
 * 
 * @global stdClass $CFG
 * @param FiltroNoticia $filtroNot
 * @return string String da tabela
 */
function tabelaNoticiaChamada($filtroNot) {
    global $CFG;

    //recuperando dados para processamento
    $chamada = buscarChamadaPorIdCT($filtroNot->getIdChamada(), $filtroNot->getIdProcesso());
    $noticiasChamada = buscarNoticiaPorChamadaCT($chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), NULL, $filtroNot->getInicioDados(), $filtroNot->getQtdeDadosPag());

    if (count($noticiasChamada) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = " <table class='table table-hover table-bordered'>
    <thead><tr>
        <th>Publicação</th>
        <th>Validade</th>
        <th>Título</th>
        <th><i class='fa fa-eye'></i></th>
        <th><i class='fa fa-edit'></i></th>
        <th><i class='fa fa-trash-o'></i></th>
    </tr></thead>";

    // verificando edição
    $permiteEdicao = $chamada->permiteEdicao();

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($noticiasChamada); $i++) {
        $temp = $noticiasChamada[$i - 1];

        $linkConsultar = "<a id='linkConsultar' title='Consultar esta notícia' href='$CFG->rwww/visao/noticia/consultarNoticia.php?idProcesso={$temp->getPRC_ID_PROCESSO()}&idNoticia={$temp->getNOT_ID_NOTICIA()}'><i class='fa fa-eye'></i></a>";

        if ($permiteEdicao) {
            $linkEditar = "<a id='linkEditar' title='Editar esta notícia' href='$CFG->rwww/visao/noticia/criarEditarNoticia.php?idProcesso={$temp->getPRC_ID_PROCESSO()}&idChamada={$temp->getPCH_ID_CHAMADA()}&idNoticia={$temp->getNOT_ID_NOTICIA()}'><i class='fa fa-edit'></i></a>";
            $linkExcluir = "<a id='linkExcluir' title='Excluir esta notícia' onclick='javascript: excluirNoticia(\"{$temp->getNOT_ID_NOTICIA()}\");'><i class='fa fa-trash-o'></i></a>";
        } else {
            $linkEditar = "<a onclick='return false' id='linkEditar' title='Você não pode editar esta notícia'><i class='fa fa-ban'></i></a>";
            $linkExcluir = "<a onclick='return false' id='linkExcluir' title='Você não pode excluir esta notícia'><i class='fa fa-ban'></i></a>";
        }

        $ret .= "<tr>
        <td>{$temp->getNOT_DT_PUBLICACAO()}</td>
        <td>{$temp->getNOT_DT_VALIDADE(true)}</td>
        <td>{$temp->getNOT_NM_TITULO()}</td>
        <td>$linkConsultar</td>
        <td>$linkEditar</td>
        <td>$linkExcluir</td>
        </tr>";
    }

    $ret .= "</table>";

    return $ret;
}

?>