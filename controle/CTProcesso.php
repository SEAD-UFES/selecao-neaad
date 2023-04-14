<?php
require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/util/Mensagem.php";
require_once $CFG->rpasta . "/negocio/Usuario.php";
require_once $CFG->rpasta . "/negocio/Processo.php";
require_once $CFG->rpasta . "/negocio/PoloChamada.php";
require_once $CFG->rpasta . "/negocio/PoloInscricao.php";
require_once $CFG->rpasta . "/negocio/InscricaoProcesso.php";
require_once $CFG->rpasta . "/negocio/GrupoAnexoProc.php";
require_once $CFG->rpasta . "/negocio/ItemAnexoProc.php";
require_once $CFG->rpasta . "/negocio/SubitemAnexoProc.php";
require_once $CFG->rpasta . "/negocio/AreaAtuChamada.php";
require_once $CFG->rpasta . "/negocio/EtapaAvalProc.php";
require_once $CFG->rpasta . "/negocio/PoloAreaChamada.php";
require_once $CFG->rpasta . "/negocio/ReservaVagaChamada.php";
require_once $CFG->rpasta . "/negocio/ReservaPoloArea.php";
require_once $CFG->rpasta . "/controle/CTCurso.php";
require_once $CFG->rpasta . "/controle/CTNotas.php";

//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctprocesso") {    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];
        //caso de não estar logado
        if (estaLogado() == NULL) {
            new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
            return;
        }
        //caso adicionar inscrição
        if ($acao == "criarInscProcesso") {
            //caso de não estar logado como candidato
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado como candidato para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto  de inscriçao principal
                $objInsc = new InscricaoProcesso(NULL, NULL, $_POST['idProcesso'], $_POST['idChamada'], NULL, NULL, NULL);

                // verificando opçao de polo de inscricao
                if (isset($_POST['idPolo'])) {
                    // recuperando polos do post
                    if (is_array($_POST['idPolo'])) {
                        $tam = count($_POST['idPolo']);
                        for ($i = 0; $i < $tam; $i++) {
                            $listaPolos [] = $_POST['idPolo'][$i];
                        }
                    } else {
                        $listaPolos = array($_POST['idPolo']);
                    }
                }

                // verificando opçao de area de atuacao
                if (isset($_POST['idAreaAtuChamada'])) {
                    $areaAtu = $_POST['idAreaAtuChamada'];
                }

                // verificando opção de reserva de vaga
                if (isset($_POST['idReservaVaga'])) {
                    $reservaVaga = $_POST['idReservaVaga'];
                }

                // verificando e recuperando informaçoes complementares, caso exista
                if (isset($_POST['varQuest'])) {
                    // lista de id's a recuperar
                    $listaIds = explode(',', $_POST['varQuest']);

                    $matInfComp = array(); // matriz de informaçoes complementares
                    //
                    //
                    // iterando para recuperar os ids
                    foreach ($listaIds as $id) {
                        // removendo espaços
                        $id = trim($id);

                        if (isset($_POST[$id])) {
                            // recuperando post relatado nos ids
                            for ($i = 0; $i < count($_POST[$id]); $i++) {
                                $matInfComp[$id] = $_POST[$id];
                            }
                        }
                    }
                }
//                print_r($matInfComp);
//                exit;

                $idInsc = $objInsc->criarInscricaoProcesso(getIdUsuarioLogado(), isset($listaPolos) ? $listaPolos : NULL, isset($areaAtu) ? $areaAtu : NULL, isset($reservaVaga) ? $reservaVaga : NULL, isset($matInfComp) ? $matInfComp : NULL);

                $linkInsc = "$CFG->rwww/visao/inscricaoProcesso/consultarInscProcesso.php?idInscricao=$idInsc";

                $linkComp = "$CFG->rwww/visao/relatorio/imprimirCompInscricao.php?idInscricao=$idInsc";

                $msgImpressao = "<br/><br/>Caso queira imprimir o <b>Comprovante de Inscrição</b> agora, <a target='_blank' title='Imprimir Comprovante de Inscrição' href='$linkComp'>clique aqui</a>.";

                //redirecionando
                new Mensagem("Inscrição efetuada com sucesso.<br/>Em breve você receberá um email com os dados da inscrição. Você pode consultar os detalhes de sua inscrição <a title='Consultar Inscrição' href='$linkInsc'>aqui</a>.$msgImpressao", Mensagem::$MENSAGEM_INFORMACAO);
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso excluir inscrição
        if ($acao == "excluirInscProcesso") {
            //caso de não estar logado como usuario candidato
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado como candidato para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idInsc = $_POST['idInscricao'];
                $dsMotivo = $_POST['dsMotivo'];

                // excluindo
                InscricaoProcesso::excluirInscricaoProc($idInsc, $dsMotivo);

                //redirecionando
                new Mensagem("Inscrição excluída com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/inscricaoProcesso/listarInscProcessoUsuario.php");
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        // caso eliminação em lote
        if ($acao == "eliminarEmLote") {
            //caso de não estar logado como usuario admin ou coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $nrEtapa = $_POST['nrEtapa'];
                $dsMotivo = $_POST['mensagem'];

                // recuperando dados para validação
                $processo = buscarProcessoComPermissaoCT($idProcesso);
                $chamada = buscarChamadaPorIdCT($idChamada, $processo->getPRC_ID_PROCESSO());

                // processando eliminação em lote
                InscricaoProcesso::executarEliminacaoLote($processo, $chamada, $nrEtapa, $dsMotivo);

                //redirecionando
                new Mensagem("Eliminação em lote concluída com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucElimLote", "$CFG->rwww/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}");
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        // caso de reabrir Edital
        if ($acao == "reabrirEdital") {
            //caso de não estar logado como usuario admin 
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $reabrirChamada = isset($_POST['reabrirChamada']) && $_POST['reabrirChamada'] == FLAG_BD_SIM;

                // recuperando dados 
                $processo = buscarProcessoComPermissaoCT($idProcesso);

                // processando dados
                $processo->reabrirEdital($reabrirChamada);

                //redirecionando
                new Mensagem("Operação concluída com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucOperacao", "$CFG->rwww/visao/processo/fluxoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}");
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        // caso de alterar finalização
        if ($acao == "alterarFinalizacao") {
            //caso de não estar logado como usuario admin ou coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $dtFinalizacao = isset($_POST['dtFinalizacao']) ? $_POST['dtFinalizacao'] : NULL;
                $fecharChamada = $_POST['fecharChamada'] == FLAG_BD_SIM;
                $fimEdital = $fecharChamada && (isset($_POST['fimEdital']) && $_POST['fimEdital'] == FLAG_BD_SIM);
                $finalizarAgora = isset($_POST['finalizarAgora']) && $_POST['finalizarAgora'] == FLAG_BD_SIM;

                // checando permissão adicional
                if (!$fecharChamada && estaLogado(Usuario::$USUARIO_COORDENADOR) != NULL) {
                    new Mensagem("Você não tem permissão para executar esta operação.", Mensagem::$MENSAGEM_ERRO);
                    return;
                }

                // recuperando dados 
                $processo = buscarProcessoComPermissaoCT($idProcesso);

                // processando dados
                if (!$fecharChamada) {
                    // caso de alterar finalização do edital
                    $processo->alterarFimEdital($dtFinalizacao, $finalizarAgora);
                } else {
                    // caso de alterar finalização da chamada
                    $chamada = buscarChamadaPorIdCT($idChamada, $processo->getPRC_ID_PROCESSO());
                    $chamada->alterarFimChamada($dtFinalizacao, $finalizarAgora, $fimEdital);
                }

                //redirecionando
                new Mensagem("Operação concluída com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucOperacao", "$CFG->rwww/visao/processo/fluxoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}");
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        // validar comprovante de inscriçao
        if ($acao == "validarCompInscProc") {
            //caso de não estar logado como administrador ou coordenador
            if (estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL && estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
                new Mensagem("Você precisa estar logado como coordenador ou administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando campos
                $nrAutenticidade = $_POST['nrAutenticidade'];
                $inscricao = InscricaoProcesso::validarNrCompInscricao($nrAutenticidade);

                // redirecionando para o local adequado
                if ($inscricao !== FALSE) {
                    new Mensagem("Comprovante emitido pelo sistema.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "{$inscricao->getIPR_ID_INSCRICAO()}|{$inscricao->getVerificadorCompInsc()}", "$CFG->rwww/visao/inscricaoProcesso/validarCodigoCompInsc.php");
                } else {
                    new Mensagem("Comprovante NÃO emitido pelo sistema.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "err|{$nrAutenticidade}", "$CFG->rwww/visao/inscricaoProcesso/validarCodigoCompInsc.php");
                }
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        // avaliar informação complementar cegamente
        if ($acao == "avaliarInfCompCega") {

            //caso de login não permitido
            $tpUsuario = estaLogado();
            if (($tpUsuario != Usuario::$USUARIO_COORDENADOR) && ($tpUsuario != Usuario::$USUARIO_ADMINISTRADOR) && ($tpUsuario != Usuario::$USUARIO_AVALIADOR)) {
                new Mensagem("Você precisa estar logado como avaliador, coordenador ou administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            try {
                // recuperando campos importantes
                $idInscricao = $_POST['idInscricao'];
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];

                // recuperando inscrição com permissão
                $inscricao = buscarInscricaoComPermissaoCT($idInscricao, getIdUsuarioLogado(), TRUE);

                // verificando e recuperando informaçoes complementares, caso exista
                $domicilioProximo = isset($_POST['domicilioProximo']) ? $_POST['domicilioProximo'] : NULL;

                if (isset($_POST['varNotas'])) {

                    // lista de id's a recuperar
                    $listaIds = explode(',', $_POST['varNotas']);

                    $matInfComp = array(); // matriz de informaçoes complementares
                    // iterando para recuperar os ids
                    foreach ($listaIds as $id) {
                        // removendo espaços
                        $id = trim($id);

                        if (isset($_POST[$id])) {
                            // recuperando post relatado nos ids
                            for ($i = 0; $i < count($_POST[$id]); $i++) {
                                $matInfComp[$id] = $_POST[$id];
                            }
                        }
                    }
                }

                // chamando função responsável 
                $inscricao->registrarAvalInfComp(isset($matInfComp) ? $matInfComp : NULL, $domicilioProximo);

                //redirecionando
                new Mensagem("Avaliação registrada com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAvaliacao", "$CFG->rwww/visao/inscricaoProcesso/listarAvaliacaoCegaInsc.php");
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso exportar inscrição geral
        if ($acao == "exportarDados") {
            //caso de login não permitido
            $tpUsuario = estaLogado();
            if (($tpUsuario != Usuario::$USUARIO_COORDENADOR) && ($tpUsuario != Usuario::$USUARIO_ADMINISTRADOR)) {
                new Mensagem("Você precisa estar logado como coordenador ou administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            try {

                // recuperando parâmetros
                $idProcesso = $_POST['idProcesso'];
                $idTipoExportacao = $_POST['idTipoExportacao'];
                $idChamada = $_POST['idChamada'];
                $idEtapaAval = isset($_POST['idEtapaAval']) ? $_POST['idEtapaAval'] : NULL;

                // recuperando processo
                $processo = buscarProcessoComPermissaoCT($idProcesso);

                // executando exportação
                $processo->exportarDadosProcesso($idTipoExportacao, $idChamada, $idEtapaAval);
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso criar processo
        if ($acao == "criarProcesso") {
            //caso de login não permitido
            $tpUsuario = estaLogado();
            if (($tpUsuario != Usuario::$USUARIO_COORDENADOR) && ($tpUsuario != Usuario::$USUARIO_ADMINISTRADOR)) {
                new Mensagem("Você precisa estar logado como coordenador ou administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto com os parâmetros
                $processo = new Processo(NULL, $_POST['idTipoCargo'], $_POST['idCurso'], $_POST['nrEdital'], $_POST['anoEdital'], NULL, $_POST['dsEdital'], $_POST['dtInicio'], NULL);

                // verificando upload com sucesso
                NGUtil::arq_verificaSucessoUpload('arqEdital');

                // criando 
                $processo->criarProcesso($_FILES['arqEdital']['tmp_name']);

                //redirecionando
                new Mensagem("Edital cadastrado com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?idProcesso={$processo->getPRC_ID_PROCESSO()}");
            } catch (NegocioException $n) {
                // redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso excluir processo
        if ($acao == "excluirProcesso") {
            //caso de login não permitido
            $tpUsuario = estaLogado();
            if (($tpUsuario != Usuario::$USUARIO_COORDENADOR) && ($tpUsuario != Usuario::$USUARIO_ADMINISTRADOR)) {
                new Mensagem("Você precisa estar logado como coordenador ou administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //validando acesso e recuperando processo
                $processoAtual = buscarProcessoComPermissaoCT($_POST['idProcesso']);

                //excluindo
                $processoAtual->excluirProcesso();
//                
                //redirecionando
                new Mensagem("Edital excluído com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/processo/listarProcessoAdmin.php");
            } catch (NegocioException $n) {
                // redirecionando para erro
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

function buscarProcessoPorIdCT($idProcesso) {
    try {
        return Processo::buscarProcessoPorId($idProcesso);
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

function buscarIdProcessoPorUrlAmigavelCT($nmCurso, $nmTipoCargo, $id) {
    try {
        return Processo::buscarIdProcessoPorUrlAmigavel($nmCurso, $nmTipoCargo, $id);
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

function buscarIdInscricaoPorChamUsuarioCT($idProcesso, $idChamada, $idUsuario) {
    try {
        return InscricaoProcesso::buscarIdInscricaoPorChamUsuario($idProcesso, $idChamada, $idUsuario);
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

function contarEliminacaoLoteCT($idProcesso, $idChamada, $nrEtapa) {
    try {
        return InscricaoProcesso::contarEliminacaoLote($idProcesso, $idChamada, $nrEtapa);
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

function buscarAreaAtuPorChamadaCT($idChamada, $flagSituacao = NULL) {
    try {
        return AreaAtuChamada::buscarAreaAtuPorChamada($idChamada, $flagSituacao);
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

function contarAreaAtuPorChamadaCT($idChamada, $flagSituacao = NULL) {
    try {
        return AreaAtuChamada::contarAreaAtuPorChamada($idChamada, $flagSituacao);
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

function buscarAreaAtuPorChamadaPoloCT($idChamada, $idPolo) {
    try {
        return PoloAreaChamada::buscarAreaAtuPorChamadaPolo($idChamada, $idPolo);
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

function buscarAreaAtuCompPorChamadaCT($idChamada, $flagSituacao = NULL, $listaAreasAtu = NULL) {
    try {
        return AreaAtuChamada::buscarAreaAtuCompPorChamada($idChamada, $flagSituacao, $listaAreasAtu);
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

function buscarPoloAreaPorChamadaCT($idChamada, $listaPolos = NULL, $listaAreasAtu = NULL) {
    try {
        return PoloAreaChamada::buscarPoloAreaPorChamada($idChamada, $listaPolos, $listaAreasAtu);
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

function buscarReservaVagaPorChamadaCT($idChamada, $flagSituacao = NULL, $listaReservaVagas = NULL) {
    try {
        return ReservaVagaChamada::buscarReservaVagaPorChamada($idChamada, $flagSituacao, $listaReservaVagas);
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

function contarInscricaoPorChamReservaVagasCT($idProcesso, $idChamada) {
    try {
        return InscricaoProcesso::contarInscricaoPorChamReservaVagas($idProcesso, $idChamada);
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

function contarInscricaoPorChamAreaAtuacaoCT($idProcesso, $idChamada) {
    try {
        return InscricaoProcesso::contarInscricaoPorChamAreaAtuacao($idProcesso, $idChamada);
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

function contarInscricaoPorChamPoloAreaAtuCT($idProcesso, $idChamada) {
    try {
        return InscricaoProcesso::contarInscricaoPorChamPoloAreaAtu($idProcesso, $idChamada);
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

function contarInscricaoPorChamPoloCT($idProcesso, $idChamada) {
    try {
        return InscricaoProcesso::contarInscricaoPorChamPolo($idProcesso, $idChamada);
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

function contarInscricaoPorProcessoChamCT($idProcesso, $idChamada) {
    try {
        return InscricaoProcesso::contarInscricaoPorProcessoCham($idProcesso, $idChamada);
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

function buscarIdsReservaVagaPorChamadaCT($idChamada, $flagSituacao = NULL, $publicoGeral = FALSE) {
    try {
        return ReservaVagaChamada::buscarIdsReservaVagaPorChamada($idChamada, $flagSituacao, $publicoGeral);
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

function contarReservaVagaPorChamadaCT($idChamada, $flagSituacao = NULL) {
    try {
        return ReservaVagaChamada::contarReservaVagaPorChamada($idChamada, $flagSituacao);
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

function buscarReservaPoloAreaPorChamadaCT($idChamada, $tpIndexacao) {
    try {
        return ReservaPoloArea::buscarReservaPoloAreaPorChamada($idChamada, $tpIndexacao);
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

function buscarInscritosReservaPoloAreaPorChamadaCT($idChamada, $tpIndexacao) {
    try {
        return ReservaPoloArea::buscarInscritosReservaPoloAreaPorChamada($idChamada, $tpIndexacao);
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

function buscarAreaAtuChamadaPorIdCT($idAreaAtuChamada) {
    try {
        return AreaAtuChamada::buscarAreaAtuChamadaPorId($idAreaAtuChamada);
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

function buscarReservaVagaChamPorIdCT($idReservaCham) {
    try {
        return ReservaVagaChamada::buscarReservaVagaChamPorId($idReservaCham);
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

function buscarInscricaoPorIdCT($idInscricao) {
    try {
        return InscricaoProcesso::buscarInscricaoPorId($idInscricao);
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

function buscarResultadosPublicadosCT($chamada) {
    try {
        return EtapaSelProc::buscarResultadosPublicados($chamada);
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
 * Esta função processa a apresentação da validação do comprovante de inscrição, fazendo as devidas validações
 * 
 * @param string $dadosToast String retornada no parâmetro get de validação
 * 
 * @return array Array informando como deve ser apresentado os dados, na forma (bool_validou, msg).
 */
function processaApresValidacaoComp($dadosToast) {
    global $CFG;

    if (Util::vazioNulo($dadosToast)) {
        return; // Nada a fazer
    }

    // validando dados do toast
    $split = explode("|", $dadosToast);
    $autenticidade = InscricaoProcesso::desformataVerificadorCompInsc($split[1]);
    if (count($split) != 2 || ($split[0] != "err" && !is_numeric($split[0])) || (!ctype_xdigit($autenticidade) || strlen($autenticidade) != 32)) {
        new Mensagem("Parâmetros inconsistentes.", Mensagem::$MENSAGEM_ERRO);
    }

    // tudo certo.
    // Validando caso de comprovante inválido
    if (!is_numeric($split[0])) {
        return array(FALSE, "Este comprovante de inscrição NÃO foi emitido pelo sistema.<br/>Verifique se o código de autencidade informado está correto: <b>{$split[1]}</b>");
    }

    // validando caso de comprovante válido
    $inscricao = InscricaoProcesso::validarNrCompInscricao($split[1], $split[0]);
    if ($inscricao === FALSE) {
        new Mensagem("Dados de validação inconsistentes.", Mensagem::$MENSAGEM_ERRO);
    }
    return array(TRUE, "Comprovante de inscrição emitido pelo sistema. <a target='_blank' href='$CFG->rwww/visao/relatorio/imprimirCompInscricao.php?idInscricao={$inscricao->getIPR_ID_INSCRICAO()}'>Clique para visualizar o original.</a><br/>Inscrição: <b>{$inscricao->getIPR_NR_ORDEM_INSC()}</b><br/>Candidato: <b>{$inscricao->USR_DS_NOME_CDT}</b><br/>Autenticidade: <b>{$inscricao->getVerificadorCompInsc()}</b>");
}

/**
 * Busca inscriçao validando permissao
 * 
 * Analisa casos de Administraçao e Usuario
 *  
 * @param int $idInscricao
 * @param int $idUsuario
 * @param boolean $avaliacaoCega - Informa se o motivo de busca e para avaliaçao cega
 * @return InscricaoProcesso
 * @throws NegocioException
 */
function buscarInscricaoComPermissaoCT($idInscricao, $idUsuario, $avaliacaoCega = FALSE) {
    try {

        $usuLogado = estaLogado();

        if ($usuLogado == NULL) {
            new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        //recuperando inscriçao
        $inscricao = InscricaoProcesso::buscarInscricaoPorId($idInscricao);

        // validando caso de administraçao
        if ($usuLogado == Usuario::$USUARIO_ADMINISTRADOR) {

            // verificando caso de inscriçao ja avaliada
            if ($avaliacaoCega && $inscricao->isAvaliada()) {
                //redirecionando para erro
                new Mensagem("Desculpe, inscrição já avaliada.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            // usuario poderoso: retornando os dados
            return $inscricao;
        }

        // validando caso de coordenador e avaliador 
        if ($usuLogado == Usuario::$USUARIO_COORDENADOR || $usuLogado == Usuario::$USUARIO_AVALIADOR) {

            //buscando curso para validacao
            if ($usuLogado == Usuario::$USUARIO_COORDENADOR) {
                //buscando como coordenador
                $curso = buscarCursoPorCoordenadorCT(getIdUsuarioLogado());
            } else {
                // estilo avaliador
                $usu = buscarUsuarioPorIdCT(getIdUsuarioLogado());
                $curso = buscarCursoPorIdCT($usu->getUSR_ID_CUR_AVALIADOR());
            }

            if ($curso->getCUR_ID_CURSO() != $inscricao->CUR_ID_CURSO) {
                //redirecionando para erro
                new Mensagem("Desculpe, você não possui permissão para acessar esta inscrição.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            if ($avaliacaoCega && $inscricao->isAvaliadaCegamente()) {
                //redirecionando para erro
                new Mensagem("Desculpe, inscrição já avaliada.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            return $inscricao;
        }

        // validando caso de usuario
        if (InscricaoProcesso::isInscricaoUsuario($idInscricao, $idUsuario)) {
            return $inscricao;
        }

        // bloqueando acesso
        throw new NegocioException("Desculpe, você não tem permissão para acessar esta página.");
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

function buscarProcessosPorFiltroCT($idCurso, $tpFormacao, $idProcesso, $nrEdital, $anoEdital, $idTipoCargo, $inicioDados, $qtdeDados) {
    try {
        return Processo::buscarProcessosPorFiltro($idCurso, $tpFormacao, $idProcesso, $nrEdital, $anoEdital, $idTipoCargo, $inicioDados, $qtdeDados);
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
 * @param FiltroProcesso $filtroProcesso
 * @return int
 */
function contaProcessosPorFiltroCT($filtroProcesso) {
    try {
        return Processo::contaProcessosPorFiltro($filtroProcesso->getIdCurso(), $filtroProcesso->getTpFormacao(), $filtroProcesso->getIdProcesso(), $filtroProcesso->getNrEdital(), $filtroProcesso->getAnoEdital(), $filtroProcesso->getIdTipoCargo());
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

function buscarRespPorInscricaoGrupoCT($idInscricao, $idGrupo) {
    try {
        return RespAnexoProc::buscarRespPorInscricaoGrupo($idInscricao, $idGrupo);
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

function buscarRespPorInscricaoItemCT($idInscricao, $idItem) {
    try {
        return RespAnexoProc::buscarRespPorInscricaoItem($idInscricao, $idItem);
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
 * @param int $idInscricao
 * @return array
 */
function buscarPoloPorInscricaoCT($idInscricao) {
    try {
        return PoloInscricao::buscarPoloPorInscricao($idInscricao);
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

function buscarGrupoPorProcessoCT($idProcesso, $tpAvaliacao = NULL) {
    try {
        return GrupoAnexoProc::buscarGrupoPorProcesso($idProcesso, $tpAvaliacao);
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

function buscarGrupoAnexoProcPorIdCT($idGrupoAnexoProc, $idProcesso = NULL) {
    try {
        return GrupoAnexoProc::buscarGrupoAnexoProcPorId($idGrupoAnexoProc, $idProcesso);
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

function contarGrupoPorProcessoCT($idProcesso) {
    try {
        return GrupoAnexoProc::contarGrupoPorProcesso($idProcesso);
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
 * @param FiltroInfCompProc $filtroInfCompProc
 */
function contarInfCompProcCT($filtroInfCompProc) {
    return contarGrupoPorProcessoCT($filtroInfCompProc->getIdProcesso());
}

function buscarItemPorGrupoCT($idGrupo) {
    try {
        return ItemAnexoProc::buscarItemPorGrupo($idGrupo);
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

function buscarSubitemPorItemCT($idItem) {
    try {
        return SubitemAnexoProc::buscarSubitemPorItem($idItem);
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

function buscarPoloPorChamadaCT($idChamada, $flagSituacao = NULL) {
    try {
        return PoloChamada::buscarPoloPorChamada($idChamada, $flagSituacao);
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

function contarPoloPorChamadaCT($idChamada, $flagSituacao = NULL) {
    try {
        return PoloChamada::contaPoloPorChamada($idChamada, $flagSituacao);
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

function buscarPoloCompPorChamadaCT($idChamada, $flagSituacao = NULL, $listaPolos = NULL) {
    try {
        return PoloChamada::buscarPoloCompPorChamada($idChamada, $flagSituacao, $listaPolos);
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

function contarInscPorPoloChamadaCT($idChamada) {
    try {
        return PoloInscricao::contaInscPorPoloChamada($idChamada);
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

function buscarProcessoPorCursoCT($idCurso) {
    try {
        return Processo::buscarProcessoAbtPorCurso($idCurso);
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

function buscarUltProcAbtPorCursoCT($idCurso) {
    try {
        return Processo::buscarUltProcAbtPorCurso($idCurso);
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

function buscarIdDsChamadaPorProcessoCT($idProcesso) {
    try {
        return ProcessoChamada::buscarIdDsChamadaPorProcesso($idProcesso);
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

function buscarChamadaPorProcessoCT($idProcesso, $soAtivas = FALSE) {
    try {
        return ProcessoChamada::buscarChamadaPorProcesso($idProcesso, $soAtivas);
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

function buscarChamadaPorIdCT($idChamada, $idProcesso = NULL) {
    try {
        return ProcessoChamada::buscarChamadaPorId($idChamada, $idProcesso);
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

function buscaDtUSPriChamadaDoProcessoCT($idProcesso) {
    try {
        return ProcessoChamada::buscaDtUSPriChamadaDoProcesso($idProcesso);
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

function validaInscricaoUsuarioCT($idUsuario, $idProcesso, $idChamada, $direcionarMsg = TRUE) {
    try {
        return InscricaoProcesso::validaInscricaoUsuario($idUsuario, $idProcesso, $idChamada, $direcionarMsg);
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_AVISO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }
}

function validaNumEditalCTAjax($nrEdital, $anoEdital, $idTipoCargo, $idCurso) {
    try {
        return Processo::validarNumEdital($nrEdital, $anoEdital, $idTipoCargo, $idCurso);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

/**
 * ATENÇÃO: ESTA FUNÇÃO É DIFERENTE DAS DEMAIS FUNÇÕES DE BUSCA!
 * 
 * Ela retorna uma estrutura de matriz indexada pelo tipo de apresentação, onde cada índice contém uma lista de 
 * processos que devem ser listados naquela categoria de apresentação.
 * 
 * @param int $idTipo
 * @param char $tpFormacao
 * @param int $idCurso
 * @param int $anoEdital
 * @param int $nrEdital
 * @param int $inicioDados
 * @param int $qtdeDados
 * @param array $arrayTpApresentacao Tipos de apresentação desejados. Parâmetro opcional.
 * @return \Processo
 */
function buscarProcessosApresentacaoCT($idTipo, $tpFormacao, $idCurso, $anoEdital, $nrEdital, $inicioDados, $qtdeDados, $arrayTpApresentacao = NULL) {
    try {
        return Processo::buscarProcessosApresentacao($idTipo, $tpFormacao, $idCurso, $anoEdital, $nrEdital, $inicioDados, $qtdeDados, $arrayTpApresentacao);
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
 * @param FiltroProcesso $filtroProc
 * @return Processo
 */
function contaProcessosApresentacaoCT($filtroProc) {
    try {
        return Processo::contaProcessosApresentacao($filtroProc->getIdTipoCargo(), $filtroProc->getTpFormacao(), $filtroProc->getIdCurso(), $filtroProc->getAnoEdital(), $filtroProc->getNrEdital());
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

function permiteManterGrupoAnexoProcCT($processo) {
    try {
        return GrupoAnexoProc::permiteManterGrupo($processo);
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

function permiteCriarEtapaAvalCT($processo) {
    try {
        return EtapaAvalProc::permiteCriarEtapa($processo);
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

function buscarInscricaoPorUsuarioCT($idUsuario, $idProcesso, $idTipo, $tpFormacao, $idCurso, $anoEdital, $nrEdital, $inicioDados, $qtdeDados, $tpApresentacao = NULL) {
    try {
        return InscricaoProcesso::buscarInscricaoPorUsuario($idUsuario, $idProcesso, $idTipo, $tpFormacao, $idCurso, $anoEdital, $nrEdital, $inicioDados, $qtdeDados, $tpApresentacao);
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
 * @param int $idProcesso
 * @return EtapaAvalProc - Array de Etapas
 */
function buscarEtapaAvalPorProcCT($idProcesso) {
    try {
        return EtapaAvalProc::buscarEtapaAvalPorProc($idProcesso);
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
 * @param int $idEtapaAval
 * @param int $idProcesso
 * @return EtapaAvalProc
 */
function buscarEtapaAvalPorIdCT($idEtapaAval, $idProcesso = NULL) {
    try {
        return EtapaAvalProc::buscarEtapaAvalPorId($idEtapaAval, $idProcesso);
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

function buscarUltEtapaAvalPorProcCT($idProcesso) {
    try {
        return EtapaAvalProc::buscarUltEtapaAvalPorProc($idProcesso);
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
 * @param FiltroProcesso $filtroProc
 * @return int
 */
function contaInscricaoPorUsuarioCT($filtroProc) {
    try {
        return InscricaoProcesso::contarInscPorFiltroUsuario($filtroProc->getIdUsuario(), $filtroProc->getIdProcesso(), $filtroProc->getIdTipoCargo(), $filtroProc->getTpFormacao(), $filtroProc->getIdCurso(), $filtroProc->getAnoEdital(), $filtroProc->getNrEdital());
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

function contaInscProcUltChamadaCT($idUsuario, $idProcesso) {
    try {
        return InscricaoProcesso::contaInscProcUltChamada($idUsuario, $idProcesso);
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
 * Verifica se a chamada está no periodo de inscriçao. 
 * 
 * @param int $idChamada
 * @param boolean $retornoCompleto Informa se é para ser utilizado o retorno completo. Padrão: False
 * 
 * @return [boolean, char] - Se $retornoCompleto, então é retornado um vetor informando se a chamada está dentro do período de inscrição e, caso negativo, 
 * se o evento é passado ou futuro; Senão, é retornado apnas um boolean informando se está dentro do período de inscrição. Utilize as constantes de evento
 * EVENTO_PASSADO, EVENTO_PRESENTE e EVENTO_FUTURO. 
 * 
 */
function validaPeriodoInscPorChamadaCT($idChamada, $retornoCompleto = FALSE) {
    try {
        return ProcessoChamada::validaPeriodoInscPorChamada($idChamada, $retornoCompleto);
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

function permiteMostrarRecursoCT($idChamada) {
    try {
        return EtapaSelProc::permiteMostrarRecurso($idChamada);
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

function permiteMostrarProtocolizacaoRecursoCT($idChamada, $idInscricao) {
    try {
        return EtapaSelProc::permiteMostrarProtocolizacaoRecurso($idChamada, $idInscricao);
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

function buscaInscritosPorProcessoCT($idProcesso, $idPolo, $idAreaAtuacao, $idReservaVaga, $nmUsuario, $nrcpf, $idChamada, $ordem, $tpExibSituacao, $ordenacao, $decrescente, $inicioDados, $qtdeDados) {
    try {
        return InscricaoProcesso::buscaInscritosPorProcesso($idProcesso, $idPolo, $idAreaAtuacao, $idReservaVaga, $nmUsuario, $nrcpf, $idChamada, $ordem, $tpExibSituacao, $ordenacao, $decrescente, $inicioDados, $qtdeDados);
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

function buscarAvalCegaPorProcessoCT($idCurso, $idProcesso, $idChamada, $idPolo, $idAreaAtuacao, $idReservaVaga, $inicioDados, $qtdeDados) {
    try {
        return InscricaoProcesso::buscarAvalCegaPorProcesso($idCurso, $idProcesso, $idChamada, $idPolo, $idAreaAtuacao, $idReservaVaga, $inicioDados, $qtdeDados);
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

function buscarAcompProcChamadaPorChamCT($idProcesso, $idChamada = NULL, $agrupamentoDiario = TRUE) {
    try {
        return AcompProcChamada::buscarAcompProcChamadaPorCham($idProcesso, $idChamada = NULL, $agrupamentoDiario);
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
 * @param FiltroInscritoProcesso $filtroInscProc
 * 
 * @return int
 */
function contaInscritosPorProcessoCT($filtroInscProc) {
    try {
        return InscricaoProcesso::contaInscritosPorProcesso($filtroInscProc->getIdProcesso(), $filtroInscProc->getIdPolo(), $filtroInscProc->getIdAreaAtuacao(), $filtroInscProc->getIdReservaVaga(), $filtroInscProc->getNmUsuario(), (!Util::vazioNulo($filtroInscProc->getNrCpf()) ? removerMascara("###.###.###-##", $filtroInscProc->getNrCpf()) : NULL), $filtroInscProc->getIdChamada(), $filtroInscProc->getCodigo(), $filtroInscProc->getTpExibSituacao());
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
 * @param int $idChamada
 * @param int $idProcesso
 * @return boolean
 */
function isChamadaDoProcessoCT($idChamada, $idProcesso) {
    try {
        return ProcessoChamada::isChamadaDoProcesso($idChamada, $idProcesso);
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
 * @param FiltroInscritoProcesso $filtroInscProc
 * @return int
 */
function contaAvalCegaPorProcessoCT($filtroInscProc) {
    try {
        return InscricaoProcesso::contaAvalCegaPorProcesso($filtroInscProc->getIdCurso(), $filtroInscProc->getIdProcesso(), $filtroInscProc->getIdChamada(), $filtroInscProc->getIdPolo(), $filtroInscProc->getIdAreaAtuacao(), $filtroInscProc->getIdReservaVaga());
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
 * Esta função retorna as chamadas com pendências, seguindo um formato específico
 * 
 * @return array Matriz indexada por idProcesso:idChamada onde cada linha contém um array com os seguintes dados (índices):
 *  - data Data da pendência
 *  - edital Descrição do edital
 *  - ocorrencia Descrição da ocorrência
 *  - link Link para onde o admin deve ser redirecionado
 *  - solicitante Nome do usuário que causou (solicitou uma ação) a pendência
 * 
 */
function buscarChamadaComPendenciaCT() {
    try {
        return ProcessoChamada::buscarChamadaComPendencia();
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
 * @param int $idProcesso
 * @param boolean $permiteTodosLogado
 * @param boolean $acessoLivre Diz se o acesso é livre
 * @return Processo
 */
function buscarProcessoComPermissaoCT($idProcesso, $permiteTodosLogado = FALSE, $acessoLivre = FALSE) {

    $usuLogado = estaLogado();

    if (!$acessoLivre) {
        if ($usuLogado == null) {
            new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        if (!$permiteTodosLogado) {
            if ($usuLogado != Usuario::$USUARIO_COORDENADOR && $usuLogado != Usuario::$USUARIO_ADMINISTRADOR && $usuLogado != Usuario::$USUARIO_AVALIADOR) {
                new Mensagem("Você não possui permissão de acesso a esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
        }
    }

    $processo = buscarProcessoPorIdCT($idProcesso);

    //caso coordenador
    if (!$acessoLivre) {

        if ($usuLogado == Usuario::$USUARIO_COORDENADOR || $usuLogado == Usuario::$USUARIO_AVALIADOR) {
            if ($usuLogado == Usuario::$USUARIO_COORDENADOR) {
                //buscando curso
                $curso = buscarCursoPorCoordenadorCT(getIdUsuarioLogado());
            } else {
                // buscando curso 
                $usu = buscarUsuarioPorIdCT($usuLogado);
                $curso = !Util::vazioNulo($usu->getUSR_ID_CUR_AVALIADOR()) ? buscarCursoPorIdCT($usu->getUSR_ID_CUR_AVALIADOR()) : NULL;
            }


            if ($curso == NULL || $curso->getCUR_ID_CURSO() != $processo->getCUR_ID_CURSO()) {
                //redirecionando para erro
                new Mensagem("Desculpe, você não possui permissão para acessar este processo.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
        }
    }

    //retornando o processo
    return $processo;
}

/**
 * 
 * @global stdclass $CFG
 * @param FiltroProcesso $filtroProc
 * @return string
 */
function tabelaProcessosApresentacao($filtroProc) {

    //recuperando processos
    $matrizProcessos = buscarProcessosApresentacaoCT($filtroProc->getIdTipoCargo(), $filtroProc->getTpFormacao(), $filtroProc->getIdCurso(), $filtroProc->getAnoEdital(), $filtroProc->getNrEdital(), $filtroProc->getInicioDados(), $filtroProc->getQtdeDadosPag());

    if (count($matrizProcessos) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "";

    foreach (Processo::getMatrizTpApresentacaoOrd() as $tp => $descricao) {
        if (isset($matrizProcessos[$tp])) {
            $ret .= "<fieldset class='completo m01'><legend>$descricao</legend>";

            $ret .= _tabelaParcialApresentacao($matrizProcessos[$tp], $tp);

            $ret .= "</fieldset>";
        }
    }

    return $ret;
}

/**
 * Função de uso interno
 * 
 * @param Processo $processos Array de processos
 * @param char $tpApresentacao Tipo de apresentação, segundo a classe Processo 
 * @return string
 */
function _tabelaParcialApresentacao($processos, $tpApresentacao) {

    $ret = "<table class='table table-hover table-responsive table-bordered'>
        <thead><tr>
        <th>Edital</th>
        <th>Atribuição</th>
        <th>Curso</th>
        <th class='campoDesktop'>Formação</th>";

    // definindo campos do tipo de apresentação
    if ($tpApresentacao == Processo::$APRESENTACAO_NOVO || $tpApresentacao == Processo::$APRESENTACAO_INSCRICAO) {
        $ret .= "<th class='campoDesktop'>Inscrição</th>";
    } elseif ($tpApresentacao == Processo::$APRESENTACAO_ANDAMENTO) {
        $ret .= "<th class='campoDesktop'>Fase Atual</th>";
    } elseif ($tpApresentacao == Processo::$APRESENTACAO_FINALIZADO) {
        $ret .= "<th class='campoDesktop'>Data de Finalização</th>";
    }


    $ret .= "<th class='botao'><span class='fa fa-book'></span></th>
    </tr></thead><tbody>";

    //iteração para exibir processos
    for ($i = 1; $i <= sizeof($processos); $i++) {
        $temp = $processos[$i - 1];
        $linkConsulta = "<a id='linkVisualizar' title='Edital completo' href='{$temp->getUrlAmigavel()}'><span class='fa fa-book'></span></a>";

        $dsNumeracao = $temp->getNumeracaoEdital();
        $periodoInscricao = $temp->getDsPeriodoInscricao();
        $nivel = $temp->TPC_NM_TIPO_CURSO;
        $cargo = $temp->TIC_NM_TIPO_CARGO;
        $curso = $temp->CUR_NM_CURSO;
        $ret .= "<tr>
        <td>$dsNumeracao</td>
        <td>$cargo</td>
        <td>$curso</td>
        <td class='campoDesktop'>$nivel</td>";

        if ($tpApresentacao == Processo::$APRESENTACAO_NOVO || $tpApresentacao == Processo::$APRESENTACAO_INSCRICAO) {
            $ret .= "<td class='campoDesktop'>$periodoInscricao</td>";
        } elseif ($tpApresentacao == Processo::$APRESENTACAO_ANDAMENTO) {
            $fase = ProcessoChamada::getFaseChamada($temp->PCH_ID_ULT_CHAMADA, $temp->PCH_CHAMADA_ATIVA, $temp->PCH_DT_ABERTURA, $temp->PCH_DT_FECHAMENTO, $temp->PCH_DT_REG_RESUL_FINAL, $temp->PCH_DT_FINALIZACAO);
            $ret .= "<td class='campoDesktop'>{$fase[1]}</td>";
        } elseif ($tpApresentacao == Processo::$APRESENTACAO_FINALIZADO) {
            $ret .= "<td class='campoDesktop'>{$temp->getPRC_DT_FIM(true)}</td>";
        }

        $ret .= "<td class='botao'>$linkConsulta</td>
        </tr>";
    }

    $ret .= "</tbody></table>";
    return $ret;
}

/**
 * 
 * @param FiltroProcesso $filtroProc
 * @return string
 */
function tabelaInscricaoUsuario($filtroProc) {

    //recuperando processos
    $matrizInsc = buscarInscricaoPorUsuarioCT($filtroProc->getIdUsuario(), $filtroProc->getIdProcesso(), $filtroProc->getIdTipoCargo(), $filtroProc->getTpFormacao(), $filtroProc->getIdCurso(), $filtroProc->getAnoEdital(), $filtroProc->getNrEdital(), $filtroProc->getInicioDados(), $filtroProc->getQtdeDadosPag());

    if (count($matrizInsc) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "";

    foreach (InscricaoProcesso::getMatrizTpApresentacaoOrd() as $tp => $descricao) {
        if (isset($matrizInsc[$tp])) {
            $ret .= "<h3 class='sublinhado'>$descricao</h3>";

            $ret .= _tabelaParcialInscUsu($matrizInsc[$tp], $tp);
        }
    }
    return $ret;
}

/**
 * Função de uso interno
 * 
 * @global stdclass $CFG
 * @param InscricaoProcesso $arrayInscricao Array de inscrições do usuário
 * @param char $tpApresentacao Tipo de apresentação, segundo a classe InscricaoProcesso 
 * @return string
 */
function _tabelaParcialInscUsu($inscricoes, $tpApresentacao) {
    global $CFG;

    $ret = "<div class='table-responsive'><table class='table table-hover table-bordered'>
        <thead><tr>
        <th>Edital</th>
        <th>Atribuição</th>
        <th>Curso</th>
        <th>Chamada</th>";

    if ($tpApresentacao == InscricaoProcesso::$APRESENTACAO_ANDAMENTO) {
        $ret .= "<th class='campoDesktop'>Fase Atual</th>";
    }


    $ret .= "<th class='botao'><span class='fa fa-eye'></span></th>
            </tr></thead><tbody>";


    //iteração para exibir processos
    for ($i = 1; $i <= sizeof($inscricoes); $i++) {
        $temp = $inscricoes[$i - 1];
        $idInscricao = $temp->getIPR_ID_INSCRICAO();
        $linkInscricao = "<a id='linkVisualizar' title='Visualizar detalhes da inscrição' href='$CFG->rwww/visao/inscricaoProcesso/consultarInscProcesso.php?idInscricao=$idInscricao'>
		<span class='fa fa-eye'></span>
		</a>";

        $dsNumeracao = $temp->PRC_NR_ANO_EDITAL;
        $vetChave = ProcessoChamada::getFaseChamada($temp->getPCH_ID_CHAMADA(), $temp->PCH_CHAMADA_ATIVA, $temp->PCH_DT_ABERTURA, $temp->PCH_DT_FECHAMENTO, $temp->PCH_DT_REG_RESUL_FINAL, $temp->PCH_DT_FINALIZACAO);
        $faseAtual = $vetChave[1];
        $cargo = $temp->TIC_NM_TIPO_CARGO;
        $curso = $temp->CUR_NM_CURSO;
        $sel = $temp->PCH_DS_CHAMADA;
        $ret .= "<tr>
                <td>$dsNumeracao</td>
                <td>$cargo</td>
                <td>$curso</td>
                <td>$sel</td>";

        if ($tpApresentacao == InscricaoProcesso::$APRESENTACAO_ANDAMENTO) {
            $ret .= "<td class='campoDesktop'>$faseAtual</td>";
        }

        $ret .= "<td class='botao'>$linkInscricao</td>
                </tr>";
    }

    $ret .= "</tbody></table></div>
        <div class='campoMobile' style='margin-bottom:2em;'>
            Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
        </div>";

    return $ret;
}

/**
 * Função que retorna a tabela de pendências do administrador para ser exibido no painel
 * 
 * @global stdclass $CFG
 * @return string
 */
function tabelaPendenciasAdministrador() {
    $pendencias = buscarChamadaComPendenciaCT();

    if ($pendencias == NULL) {
        return "<div class='callout callout-info'>
                        No momento, <b>NÃO</b> há pendências que precisam de sua atenção.
                </div>";
    }

    $ret = "<div class='table-responsive p15'>
                <table class='table table-hover table-bordered'>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Edital</th>
                            <th>Ocorrência</th>
                        </tr>
                    </thead>
                    <tbody>";

    // percorrendo dados para preenchimento
    foreach ($pendencias as $linha) {
        $ret .= "<tr title='Solicitado por {$linha['solicitante']}'>
                    <td>{$linha['data']}</td>
                    <td>{$linha['edital']}</td>
                    <td><a target='_blank' href='{$linha['link']}'>{$linha['ocorrencia']} <i class='fa fa-external-link'></i></a></td>
                </tr>";
    }

    $ret .= "</tbody>
        </table>
    </div>";

    return $ret;
}

/**
 * 
 * @param FiltroInscritoProcesso $filtroInscProc
 * @return string
 */
function tabelaAvalCegaPorProcesso($filtroInscProc) {
    global $CFG;

    //recuperando inscrições
    $inscricoes = buscarAvalCegaPorProcessoCT($filtroInscProc->getIdCurso(), $filtroInscProc->getIdProcesso(), $filtroInscProc->getIdChamada(), $filtroInscProc->getIdPolo(), $filtroInscProc->getIdAreaAtuacao(), $filtroInscProc->getIdReservaVaga(), $filtroInscProc->getInicioDados(), $filtroInscProc->getQtdeDadosPag());

    if (count($inscricoes) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<table class='table table-hover table-bordered completo'>
    <thead><tr>
        <th style='max-width:10%;'>Inscrição</th>";

    // curso forçado
    if (!$filtroInscProc->getCursoForcado()) {
        $ret .= "<th>Curso</th>";
    }

    $ret .= "<th>Edital</th>
             <th>Chamada</th>
            <th class='botao'>Avaliar</th>
        </tr></thead><tbody>";

    //iteração para exibir
    for ($i = 1; $i <= sizeof($inscricoes); $i++) {
        $temp = $inscricoes[$i - 1];

        $linkAvaliar = "<a id='linkAvaliar' title='Avaliar Candidato' href='$CFG->rwww/visao/inscricaoProcesso/avaliarInfCompCega.php?idInscricao={$temp->getIPR_ID_INSCRICAO()}'><i class='fa fa-pencil'></i></a>";


        $ret .= "<tr>
        <td style='max-width:10%;'>{$temp->getIPR_ID_INSCRICAO()}</td>";

        if (!$filtroInscProc->getCursoForcado()) {
            $ret .= "<td>{$temp->CUR_NM_CURSO}</td>";
        }

        $ret .= "<td>{$temp->PRC_NR_ANO_EDITAL}</td>
                 <td>{$temp->PCH_DS_CHAMADA}</td>
                 <td class='botao'>$linkAvaliar</td>";

        $ret .= "</tr>";
    }

    $ret .= "</tbody></table>";

    return $ret;
}

function tabelaInscritosPorProcesso($filtroInscProc) {

    //recuperando inscrições
    $matInscricoes = buscaInscritosPorProcessoCT($filtroInscProc->getIdProcesso(), $filtroInscProc->getIdPolo(), $filtroInscProc->getIdAreaAtuacao(), $filtroInscProc->getIdReservaVaga(), $filtroInscProc->getNmUsuario(), (!Util::vazioNulo($filtroInscProc->getNrCpf()) ? removerMascara("###.###.###-##", $filtroInscProc->getNrCpf()) : NULL), $filtroInscProc->getIdChamada(), $filtroInscProc->getCodigo(), $filtroInscProc->getTpExibSituacao(), $filtroInscProc->getTpClassificacao(), $filtroInscProc->getTpOrdenacao(), $filtroInscProc->getInicioDados(), $filtroInscProc->getQtdeDadosPag());

    if (count($matInscricoes) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    // verificando se e necessario mostrar opçoes de edicao
    if (Util::vazioNulo($filtroInscProc->getIdChamada())) {
        $permiteEdicao = FALSE;
        $strCompDescricao = "";
        $etapaVigente = NULL;
    } else {
        // recuperando chamada
        $chamada = buscarChamadaPorIdCT($filtroInscProc->getIdChamada(), $filtroInscProc->getIdProcesso());

        // setando permissão de edição
        $permiteEdicao = $chamada->permiteEdicao();

        // recuperando etapa vigente
        $etapaVigente = buscarEtapaVigenteCT($chamada->getPCH_ID_CHAMADA());
        $strCompDescricao = $etapaVigente != NULL ? $etapaVigente->getResultadoPendente(TRUE) : "";
        $strCompDescricao = $strCompDescricao != "" ? "($strCompDescricao)" : "";
    }

    // inicializando variáveis de controle
    $ret = "";
    $vetTpApresInsc = InscricaoProcesso::getMatrizTpApresInscOrd();

    foreach ($vetTpApresInsc as $tp => $descricao) {
        if (isset($matInscricoes[$tp])) {
            $ret .= "<h3 class='sublinhado'>$descricao $strCompDescricao</h3>";

            $ret .= _tabelaParcialInscPorProcesso($matInscricoes[$tp], $permiteEdicao, $etapaVigente);

            $ret .= "";
        }
    }
    return $ret;
}

/**
 * 
 * @param InscricaoProcesso $inscricoes array de Inscrições
 * @param boolean $permiteEdicao Flag que informa se é permitido exibir botões de edição
 * @param EtapaSelProc $etapaVigente Etapa de seleção vigente
 * 
 * @return string
 */
function _tabelaParcialInscPorProcesso($inscricoes, $permiteEdicao, $etapaVigente) {
    global $CFG;

    $ret = "<div class='table-responsive'><table class='table table-hover table-bordered'>
    <thead><tr>
        <th>Inscrição</th>
        <th>Candidato</th>
        <th class='campoDesktop'>CPF</th>
        <th class='campoDesktop'>Nota</th>
        <th class='botao'><i class='fa fa-pencil'></i></th>
        <th class='botao'><i class='fa fa-eye'></i></th>
        <th class='botao'><i class='fa fa-download'></i></th>
    </tr></thead><tbody>";

    //iteração para exibir
    for ($i = 1; $i <= sizeof($inscricoes); $i++) {
        $temp = $inscricoes[$i - 1];

        $linkConsultar = "<a id='linkConsultar' title='Consultar inscrição' href='$CFG->rwww/visao/inscricaoProcesso/consultarInscProcessoAdmin.php?idInscricao={$temp->getIPR_ID_INSCRICAO()}'><i class='fa fa-eye'></i></a>";
        $linkBaixarComp = "<a target='_blank' id='linkBaixarComp' title='Comprovante de inscrição' href='$CFG->rwww/visao/relatorio/imprimirCompInscricao.php?idInscricao={$temp->getIPR_ID_INSCRICAO()}'><i class='fa fa-download'></i></a>";

        if ($permiteEdicao && $temp->permiteEditarNotaTab($etapaVigente)) {
            $linkGerenciarNota = "<a id='linkGerenciarNota' title='Gerenciar notas e situação do candidato' href='$CFG->rwww/visao/inscricaoProcesso/gerenciarNotasInsc.php?idInscricao={$temp->getIPR_ID_INSCRICAO()}'><i class='fa fa-pencil'></i></a>";
        } else {
            $linkGerenciarNota = "<a onclick='return false' id='linkGerenciarNota' title='Não é possível gerenciar notas do candidato'><i class='fa fa-ban'></i></a>";
        }

        // adendo na classe de tr sinalizando eliminados
        $adendoTr = $temp->isEliminada() ? "class='inativo'" : "";


        $cpf = adicionarMascara("###.###.###-##", $temp->IDC_NR_CPF_CDT);
        $ret .= "<tr $adendoTr>
                    <td>{$temp->getIPR_NR_ORDEM_INSC()}</td>
                    <td>{$temp->USR_DS_NOME_CDT}</td>
                    <td class='campoDesktop'>{$cpf}</td>
                    <td class='campoDesktop'>{$temp->getNotaTabela()}</td>
                    <td class='botao'>$linkGerenciarNota</td>
                    <td class='botao'>$linkConsultar</td>
                    <td class='botao'>$linkBaixarComp</td>
                </tr>";
    }

    $ret .= "</tbody></table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
            Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    return $ret;
}

/**
 * 
 * @param FiltroProcesso $filtroProcesso
 * @return string
 */
function tabelaProcessosPorFiltro($filtroProcesso) {
    global $CFG;

    //recuperando processos
    $processos = buscarProcessosPorFiltroCT($filtroProcesso->getIdCurso(), $filtroProcesso->getTpFormacao(), $filtroProcesso->getIdProcesso(), $filtroProcesso->getNrEdital(), $filtroProcesso->getAnoEdital(), $filtroProcesso->getIdTipoCargo(), $filtroProcesso->getInicioDados(), $filtroProcesso->getQtdeDadosPag());

    if (count($processos) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
    <thead><tr>
        <th>Edital</th>
        <th>Atribuição</th>";

    //mostrando curso
    if (!$filtroProcesso->getCursoForcado()) {
        $ret .= "<th>Curso</th>";
    }
    $ret .= "<th class='campoDesktop'>Início</th>
        <th class='campoDesktop'>Fim</th>
        <th class='botao'><i class='fa fa-pencil-square-o'></i></th>
        <th class='botao'><i class='fa fa-list'></i></th>
        <th class='campoDesktop botao'><i class='fa fa-user'></i></th>
        <th class='campoDesktop botao'><i class='fa fa-bookmark'></i></th>
        <th class='botao'><i class='fa fa-trash-o'></i></th>
    </tr></thead>
    <tbody>";

    //iteração para exibir processos
    for ($i = 1; $i <= sizeof($processos); $i++) {
        $temp = $processos[$i - 1];

        $linkConsultar = "<a  title='Gerenciar edital' href='manterProcessoAdmin.php?idProcesso={$temp->getPRC_ID_PROCESSO()}'><i class='fa fa-pencil-square-o'></i></a>";

        if ($temp->permiteExclusao()) {
            $linkExcluir = "<a ' title='Excluir edital' href='excluirProcesso.php?idProcesso={$temp->getPRC_ID_PROCESSO()}'><i class='fa fa-trash-o'></i></a>";
        } else {
            $linkExcluir = "<a onclick='return false' title='Não é possível excluir este edital'><i class='fa fa-ban'></i></a>";
        }

        if ($temp->permiteExibirAcaoCdt()) {
            $linkInscricao = "<a title='Visualizar inscritos' href='$CFG->rwww/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso={$temp->getPRC_ID_PROCESSO()}'><i class='fa fa-user'></i></a>";
        } else {
            $linkInscricao = "<a onclick='return false'  title='Edital não iniciado'><i class='fa fa-ban'></i></a>";
        }

        if ($temp->permiteExibirAcaoCdt()) {
            $linkRecurso = "<a  title='Visualizar recursos' href='$CFG->rwww/visao/recurso/listarRecursoProcesso.php?idProcesso={$temp->getPRC_ID_PROCESSO()}'><i class='fa fa-bookmark'></i></a>";
        } else {
            $linkRecurso = "<a onclick='return false' title='Edital não iniciado'><i class='fa fa-ban'></i></a>";
        }

        if ($temp->permiteExibirFluxo()) {
            $linkFluxo = "<a title='Visualizar fluxo' href='$CFG->rwww/visao/processo/fluxoProcesso.php?idProcesso={$temp->getPRC_ID_PROCESSO()}'><i class='fa fa-list'></i></a>";
        } else {
            $linkFluxo = "<a onclick='return false' title='Edital não possui chamada'><i class='fa fa-ban'></i></a>";
        }

        $ret .= "<tr>
                <td>{$temp->getNumeracaoEdital()}</td>
                <td>{$temp->TIC_NM_TIPO_CARGO}</td>";

        //mostrando curso
        if (!$filtroProcesso->getCursoForcado()) {
            $ret .= "<td>{$temp->CUR_NM_CURSO}</td>";
        }
        $ret .= "<td class='campoDesktop'>{$temp->getPRC_DT_INICIO()}</td>
                <td class='campoDesktop'>{$temp->getPRC_DT_FIM(true)}</td>
                <td class='botao'>$linkConsultar</td>
                <td class='botao'>$linkFluxo</td>
                <td class='campoDesktop botao'>$linkInscricao</td>
                <td class='campoDesktop botao'>$linkRecurso</td>
                <td class='botao'>$linkExcluir</td>";

        $ret .= "</tr>";
    }

    $ret .= "</tbody></table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
                Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    return $ret;
}

function tabelaInscPorPoloChamada($idChamada) {

    //recuperando 
    $lista = contarInscPorPoloChamadaCT($idChamada);

    if (count($lista) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<table class='table table-hover table-bordered'>
            <thead><tr>
                <th>Polo</th>
                <th>Qtde de Inscritos</th>
            </tr></thead>
            <tbody>";

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($lista); $i++) {
        $polo = key($lista);
        $qtInsc = $lista[$polo];

        $ret .= "<tr>
            <td>$polo</td>
            <td>$qtInsc</td> 
        </tr>";

        next($lista);
    }

    //incluindo sumarizaçao
    $totalInsc = array_sum($lista);
    $ret .= "<td>Total de Inscritos</td>
        <td>$totalInsc</td> 
        </tr>";


    $ret .= "</tbody>
            </table>";

    return $ret;
}

function tabelaAtualizacaoProcesso($idProcesso) {

    // recuperando atualizações
    $acomps = buscarAcompProcChamadaPorChamCT($idProcesso);

    if (Util::vazioNulo($acomps)) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<style>.atualizacaoAntiga {display: none;}</style><div class='col-full'><table class='table table-hover table-bordered'>
                <thead><tr>
                    <th class='campo20'>Data</th>
                    <th class='campo80'>Informação</th>
                </tr></thead>
                <tbody>";

    // percorrendo dados
    $qtdeAberta = Processo::$QTDE_DIAS_NOTICIA_RECENTE;
    $qtAtu = 0;
    foreach ($acomps as $acompanhamento) {
        $qtAtu++;
        $classeOculta = $qtAtu > $qtdeAberta ? " class='atualizacaoAntiga'" : "";

        $ret .= "<tr $classeOculta>
                    <td class='campo20'>{$acompanhamento->getAPC_DT_ACOMPANHAMENTO(TRUE)}</td>
                    <td class='campo80'>{$acompanhamento->getHTMLDescricaoAcomp()}</td>
                </tr>";
    }

    $ret .= "</tbody>
            </table></div>";

    // adicionando link, se necessário
    if ($qtAtu > $qtdeAberta) {
        $ret .= "<p><a onclick=\"javascript: $('.atualizacaoAntiga').toggle();$(this).addClass('ocultar');\" style='color:#888;'>Ver todas as atualizações »</a></p>";
    }

    return $ret;
}

function imprimeListaEditaisPainelCandidato() {
    global $CFG;

    // recuperando últimos editais
    $matEditais = buscarProcessosApresentacaoCT(NULL, NULL, NULL, NULL, NULL, 0, 3, array(Processo::$APRESENTACAO_NOVO, Processo::$APRESENTACAO_INSCRICAO));
    $arrayDados = array_merge(isset($matEditais[Processo::$APRESENTACAO_NOVO]) ? $matEditais[Processo::$APRESENTACAO_NOVO] : array(), isset($matEditais[Processo::$APRESENTACAO_INSCRICAO]) ? $matEditais[Processo::$APRESENTACAO_INSCRICAO] : array());

    // verificando se existem editais
    if (count($arrayDados) > 0) {
        ?>
        <div class="col-md-12 col-sm-12 col-xs-12">
            <ul>
                <?php
                foreach ($arrayDados as $processo) {
                    ?>
                    <li><a href="<?php echo "$CFG->rwww/visao/processo/consultarProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}"; ?>"><?php echo $processo->getDsEditalCompleta(); ?></a></li>
                <?php }
                ?>
            </ul>
        </div>
    <?php } else {
        ?>
        <div class="callout callout-info">
            No momento não há editais novos ou com inscrição aberta.
        </div>
        <?php
    }
}

/**
 * 
 * @param int $idChamada
 * @param char $flagSituacao 
 * @return array(string, Qtde Polo);
 */
function stringPolosChamada($idChamada, $flagSituacao = NULL) {
    $vet = buscarPoloPorChamadaCT($idChamada, $flagSituacao);
    $tam = sizeof($vet);
    return array(arrayParaStr($vet), $tam);
}

function stringAreaAtuChamada($idChamada, $flagSituacao = NULL) {
    $vet = buscarAreaAtuPorChamadaCT($idChamada, $flagSituacao);
    return arrayParaStr($vet);
}

function stringReservaVagaChamada($idChamada, $flagSituacao = NULL) {
    $temp = buscarReservaVagaPorChamadaCT($idChamada, $flagSituacao);
    // montando vetor 
    $vet = array();
    foreach ($temp as $reserva) {
        $vet[$reserva->getRVC_ID_RESERVA_CHAMADA()] = $reserva->RVG_NM_RESERVA_VAGA;
    }
    return arrayParaStr($vet);
}

function getDsReservaVagaInscricaoCT($idReservaVagaCham) {
    if ($idReservaVagaCham != NULL) {
        // recuperando reserva de vaga
        $reservaVaga = buscarReservaVagaChamPorIdCT($idReservaVagaCham);
        return $reservaVaga->RVG_NM_RESERVA_VAGA;
    } else {
        return ReservaVagaChamada::$DS_PUBLICO_GERAL;
    }
}
?>
