<?php

/*
 * Este CT contém algumas funções básicas para manutenção do processo (Edital)
 * 
 *  */
require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/negocio/GrupoAnexoProc.php";
require_once $CFG->rpasta . "/negocio/ItemAnexoProc.php";
require_once $CFG->rpasta . "/controle/CTProcesso.php";


//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctmanutencaoprocesso") {

    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];

        if ($acao == "criarGrupoAnexoProc") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];

                // parâmetros adicionais
                $idEtapaAval = isset($_POST['idEtapaAval']) ? $_POST['idEtapaAval'] : NULL;
                $notaMax = isset($_POST['notaMax']) ? $_POST['notaMax'] : NULL;

                // criando objeto
                $grupoAnexoProc = new GrupoAnexoProc(NULL, $idProcesso, NULL, $_POST['nmGrupo'], $_POST['dsGrupo'], $_POST['tpGrupoAnexoProc'], $_POST['grupoObrigatorio'], isset($_POST['nrMaxCaracter']) ? $_POST['nrMaxCaracter'] : NULL, $_POST['tpAvalGrupoAnexoProc']);

                //criando grupo
                $idGrupo = $grupoAnexoProc->criarGrupoAnexoProc($idEtapaAval, $notaMax);

                //redirecionando
                if ($grupoAnexoProc->possuiOpcoesResposta()) {
                    new Mensagem('Informação Complementar cadastrada com sucesso. Agora falta as opções de resposta...', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercaoInfComp", "$CFG->rwww/visao/itemAnexoProc/manterItemAnexoProc.php?idGrupoAnexoProc=$idGrupo");
                } else {
                    $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_INF_COMP;
                    new Mensagem('Informação Complementar cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercaoInfComp", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
                }
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "editarGrupoAnexoProc") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idGrupoAnexoProc = $_POST['idGrupoAnexoProc'];

                // parâmetros adicionais
                $idEtapaAval = isset($_POST['idEtapaAval']) ? $_POST['idEtapaAval'] : NULL;
                $notaMax = isset($_POST['notaMax']) ? $_POST['notaMax'] : NULL;

                // recuperando objeto
                $grupoAnexoProc = buscarGrupoAnexoProcPorIdCT($idGrupoAnexoProc);

                // verificando post
                if ($idProcesso != $grupoAnexoProc->getPRC_ID_PROCESSO()) {
                    throw new NegocioException("Requisição inválida.");
                }

                // setando campos a atualizar
                $grupoAnexoProc->setGAP_NM_GRUPO($_POST['nmGrupo']);
                $grupoAnexoProc->setGAP_DS_GRUPO($_POST['dsGrupo']);
                $grupoAnexoProc->setGAP_GRUPO_OBRIGATORIO($_POST['grupoObrigatorio']);
                $grupoAnexoProc->setGAP_NR_MAX_CARACTER(isset($_POST['nrMaxCaracter']) ? $_POST['nrMaxCaracter'] : NULL);

                // atualizando grupo
                $grupoAnexoProc->editarGrupoAnexoProc($idEtapaAval, $notaMax, $_POST['tpAvalGrupoAnexoProc']);

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_INF_COMP;
                new Mensagem('Informação Complementar atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtuInfComp", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "excluirGrupoAnexoProc") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idGrupoAnexoProc = $_POST['idGrupoAnexoProc'];

                // recuperando objeto
                $grupoAnexoProc = buscarGrupoAnexoProcPorIdCT($idGrupoAnexoProc);

                // verificando post
                if ($idProcesso != $grupoAnexoProc->getPRC_ID_PROCESSO()) {
                    throw new NegocioException("Requisição inválida.");
                }

                // removendo grupo
                $grupoAnexoProc->excluirGrupoAnexoProc();

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_INF_COMP;
                new Mensagem('Informação Complementar excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExcInfComp", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "criarChamada") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros principais
                $idProcesso = $_POST['idProcesso'];

                // recuperando parâmetros adicionais
                $dtInicio = $_POST['dtInicio'];
                $dtFim = $_POST['dtFim'];

                // buscando dados para processamento
                $processo = buscarProcessoComPermissaoCT($idProcesso);

                // chamando função responsável
                $idChamada = ProcessoChamada::criarChamada($processo, $dtInicio, $dtFim);

                new Mensagem('Chamada criada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucChamada", "$CFG->rwww/visao/chamada/alterarCalendarioChamada.php?idProcesso=$idProcesso&idChamada=$idChamada&cCham=true");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "alterarCalendario") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                // recuperando parametros principais
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $fluxoChamada = $_POST['fluxoChamada'] == NGUtil::getFlagSim();
                $textoInicial = isset($_POST['textoInicial']) ? $_POST['textoInicial'] : NULL;

                // recuperando chamada
                $chamada = buscarChamadaPorIdCT($idChamada, $idProcesso);

                // recuperando lista de parâmetros do calendário
                $listaCalendario = $chamada->listaItensCalendario(TRUE);
                $vetCal = array(); // vetor para armazenar dados
                foreach ($listaCalendario as $item) {
                    if ($item['editavel']) {
                        if (isset($_POST[$item['idInput1']])) {
                            $vetCal [$item['idInput1']] = $_POST[$item['idInput1']];
                        }
                        if ($item['itemDuplo'] && isset($_POST[$item['idInput2']])) {
                            $vetCal [$item['idInput2']] = $_POST[$item['idInput2']];
                        }
                    }
                }

                // persistindo no BD
                $semAlteracao = FALSE;
                $chamada->atualizarCalendario($vetCal, $fluxoChamada, $textoInicial, $semAlteracao);


                //redirecionando
                if (!$fluxoChamada) {
                    // fluxo normal
                    if (!$semAlteracao) {
                        $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_CHAMADA;
                        new Mensagem('Calendário atualizado com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucCalChamada", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idChamada=$idChamada");
                    } else {
                        new Mensagem('Nenhum dado alterado.', Mensagem::$MENSAGEM_ERRO, NULL, "errSemAlteracao", "$CFG->rwww/visao/chamada/alterarCalendarioChamada.php?idProcesso=$idProcesso&idChamada=$idChamada");
                    }
                } else {
                    // fluxo de criação de chamada: redirecionar para configuração da chamada
                    new Mensagem('Calendário atualizado com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucCalChamada", "$CFG->rwww/visao/chamada/alterarConfiguracaoChamada.php?idProcesso=$idProcesso&idChamada=$idChamada&cCham=true");
                }
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        if ($acao == "alterarConfChamada") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                // recuperando parametros principais
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $fluxoChamada = $_POST['fluxoChamada'] == NGUtil::getFlagSim();


                // recuperando chamada
                $chamada = buscarChamadaPorIdCT($idChamada, $idProcesso);

                // recuperando parâmetros da configuração
                $idPolos = isset($_POST['idPolos']) ? $_POST['idPolos'] : array();
                $idAreasAtu = isset($_POST['idAreasAtu']) ? $_POST['idAreasAtu'] : array();
                $idReservaVagas = isset($_POST['idReservaVagas']) ? $_POST['idReservaVagas'] : array();
                $nrMaxOpcaoPolo = isset($_POST['nrMaxOpcaoPolo']) ? $_POST['nrMaxOpcaoPolo'] : NULL;

                // persistindo no BD e verificando se é necessário atualizar vagas
                $semAlteracao = FALSE;
                $atualizaVagas = $chamada->salvarConfChamadaP1($nrMaxOpcaoPolo, $idPolos, $idAreasAtu, $idReservaVagas, $fluxoChamada, $semAlteracao);

                //redirecionando
                if (!$atualizaVagas) {
                    // indo para a tela de configuração da chamada
                    if (!$semAlteracao) {
                        $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_CHAMADA;
                        new Mensagem('Configuração atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucConfChamada", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idChamada=$idChamada");
                    } else {
                        new Mensagem('Nenhum dado alterado.', Mensagem::$MENSAGEM_ERRO, NULL, "errSemAlteracao", "$CFG->rwww/visao/chamada/alterarConfiguracaoChamada.php?idProcesso=$idProcesso&idChamada=$idChamada");
                    }
                } else {
                    // indo para a tela de atualização de vagas
                    if (!$fluxoChamada) {
                        // Ida padrão
                        header("Location: $CFG->rwww/visao/chamada/alterarVagasChamada.php?idProcesso=$idProcesso&idChamada=$idChamada&parte=true");
                    } else {
                        // Ida por motivo de fluxo de chamada
                        new Mensagem('Configuração atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucConfChamada", "$CFG->rwww/visao/chamada/alterarVagasChamada.php?idProcesso=$idProcesso&idChamada=$idChamada&parte=true&cCham=true");
                    }
                }
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "alterarVagasChamada") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                // recuperando parametros principais
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $fluxoChamada = $_POST['fluxoChamada'] == NGUtil::getFlagSim();

                // recuperando chamada
                $chamada = buscarChamadaPorIdCT($idChamada, $idProcesso);

                // é proveniente da parte 2?
                $parte2 = isset($_POST['passo2']) && $_POST['passo2'] == FLAG_BD_SIM;

                // recuperando dados da parte 2
                if ($parte2) {
                    $nrMaxOpcaoPolo = isset($_POST['nrMaxOpcaoPolo']) ? $_POST['nrMaxOpcaoPolo'] : NULL;
                }

                // recuperando auxiliares
                $idPolos = isset($_POST['idPolos']) ? $_POST['idPolos'] : array();
                $idAreasAtu = isset($_POST['idAreasAtu']) ? $_POST['idAreasAtu'] : array();
                $idReservaVagas = isset($_POST['idReservaVagas']) ? $_POST['idReservaVagas'] : array();
                $listaReservaVagas = !Util::vazioNulo($idReservaVagas) ? explode(",", $idReservaVagas) : NULL;
                $listaPolos = !Util::vazioNulo($idPolos) ? explode(",", $idPolos) : NULL;
                $listaAreasAtu = !Util::vazioNulo($idAreasAtu) ? explode(",", $idAreasAtu) : NULL;


                // buscando dados a atualizar
                $arrayTabelaAtualizar = array(); // array na forma: "idInput => vlInput";
                // sem polo e sem area
                if ($listaPolos == NULL && $listaAreasAtu == NULL) {
                    _setInputVagasPost($idChamada, $listaReservaVagas, $_POST, $arrayTabelaAtualizar);
                    // apenas polo
                } elseif ($listaPolos != NULL && $listaAreasAtu == NULL) {
                    foreach ($listaPolos as $idPolo) {
                        _setInputVagasPost($idChamada, $listaReservaVagas, $_POST, $arrayTabelaAtualizar, $idPolo);
                    }
                    // apenas área
                } elseif ($listaPolos == NULL && $listaAreasAtu != NULL) {
                    foreach ($listaAreasAtu as $idAreaAtu) {
                        _setInputVagasPost($idChamada, $listaReservaVagas, $_POST, $arrayTabelaAtualizar, NULL, $idAreaAtu);
                    }
                    // area e polo
                } elseif ($listaPolos != NULL && $listaAreasAtu != NULL) {
                    $i = 0;
                    $indiceTempPol = ProcessoChamada::getIdInputSelectPolo($idChamada, $i);
                    $indiceTempArea = ProcessoChamada::getIdInputSelectAreaAtu($idChamada, $i);
                    do {
                        // processa
                        $arrayTabelaAtualizar[$indiceTempPol] = $_POST[$indiceTempPol];
                        $arrayTabelaAtualizar[$indiceTempArea] = $_POST[$indiceTempArea];
                        _setInputVagasPost($idChamada, $listaReservaVagas, $_POST, $arrayTabelaAtualizar, NULL, NULL, $i);


                        // próxima sequência
                        $i++;
                        $indiceTempPol = ProcessoChamada::getIdInputSelectPolo($idChamada, $i);
                        $indiceTempArea = ProcessoChamada::getIdInputSelectAreaAtu($idChamada, $i);
                    } while (isset($_POST[$indiceTempPol]) && isset($_POST[$indiceTempArea]));
                }

                // chamando função de processamento
                $semAlteracao = FALSE;
                $chamada->salvarConfChamadaVagasConfP2($parte2, $idPolos, $idAreasAtu, $idReservaVagas, $arrayTabelaAtualizar, isset($nrMaxOpcaoPolo) ? $nrMaxOpcaoPolo : NULL, $fluxoChamada, $semAlteracao);

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_CHAMADA;
                if (!$fluxoChamada) {

                    // sem alteração? Retornando à página
                    if ($semAlteracao) {
                        new Mensagem('Nenhum dado alterado.', Mensagem::$MENSAGEM_ERRO, NULL, "errSemAlteracao", "$CFG->rwww/visao/chamada/alterarVagasChamada.php?idProcesso=$idProcesso&idChamada=$idChamada");
                    } else {
                        if ($parte2) {
                            new Mensagem('Configuração atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucConfChamada", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idChamada=$idChamada");
                        } else {
                            new Mensagem('Vagas atualizadas com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucVagasChamada", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idChamada=$idChamada");
                        }
                    }
                } else {
                    // redirecionando para mensagens
                    new Mensagem('Vagas atualizadas com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucVagasChamada", "$CFG->rwww/visao/chamada/alterarMensagensChamada.php?idProcesso=$idProcesso&idChamada=$idChamada&cCham=true");
                }
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "alterarMensagensChamada") {
            // apenas admin e coordenador
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros principais
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $fluxoChamada = $_POST['fluxoChamada'] == NGUtil::getFlagSim();

                // recuperando chamada
                $chamada = buscarChamadaPorIdCT($idChamada, $idProcesso);

                // setando dados a alterar
                $chamada->setPCH_TXT_COMP_INSCRICAO(isset($_POST['msgCompInsc']) ? $_POST['msgCompInsc'] : NULL);

                // atualizando no BD
                $chamada->salvarMensagensChamada();

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_CHAMADA;
                new Mensagem('Mensagens atualizadas com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucMsgChamada", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idChamada=$idChamada");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        if ($acao == "manterItemAnexoProc") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idGrupoAnexoProc = $_POST['idGrupoAnexoProc'];
                $multiplaResp = isset($_POST['respostaMultipla']);

                // recuperando opções 
                $i = 0;
                $matItemResp = array(); // indexado pela ordem. Forma: [Ordem => array(nmItem, dsItem, complemento, tpComplemento, compObrigatorio)]
                $matCompItemResp = array(); // indexado pela ordem do item. Forma tipo comp texto: [OrdemItem => array(tamMaxResposta, nmComplemento)]; Outros tipos: [OrdemItem => array(OrdemOpcao => array(nmOpcao, dsOpcao))]
                while (isset($_POST['respOrdemItem'][$i])) {
                    $matItemResp[$_POST['respOrdemItem'][$i]] = array($_POST['respNmItem'][$i], $_POST['respDsItem'][$i], isset($_POST['respCompItem'][$i]), isset($_POST['tpcompItem' . $i]) ? $_POST['tpcompItem' . $i] : NULL, isset($_POST['obrigatorioComp' . $i]));

                    // tem complemento?
                    if ($matItemResp[$_POST['respOrdemItem'][$i]][2]) {
                        // tipo texto? 
                        if ($matItemResp[$_POST['respOrdemItem'][$i]][3] == ItemAnexoProc::$TIPO_TEL_TEXTO) {
                            $matCompItemResp[$_POST['respOrdemItem'][$i]] = array($_POST['tamMaxCompItem' . $i], $_POST['nmCompItemTexto' . $i]);
                        } elseif ($matItemResp[$_POST['respOrdemItem'][$i]][3] == ItemAnexoProc::$TIPO_TEL_RADIO || $matItemResp[$_POST['respOrdemItem'][$i]][3] == ItemAnexoProc::$TIPO_TEL_CHECKBOX) {
                            $j = 0;
                            while (isset($_POST['compOrdemCompItem' . $i][$j])) {
                                if (!isset($matCompItemResp[$_POST['respOrdemItem'][$i]])) {
                                    $matCompItemResp[$_POST['respOrdemItem'][$i]] = array();
                                }
                                $matCompItemResp[$_POST['respOrdemItem'][$i]][$_POST['compOrdemCompItem' . $i][$j]] = array($_POST['compNmCompItem' . $i][$j], $_POST['compDsCompItem' . $i][$j]);

                                $j++;
                            }
                        }
                    }
                    $i++;
                }
//
//                print_r($matItemResp);
//                echo "<br/><br/>";
//                print_r($matCompItemResp);
//                exit;
//                
//                
                // chamando funcao de persistencia
                GrupoAnexoProc::manterItensRespGrupo($idProcesso, $idGrupoAnexoProc, $multiplaResp, $matItemResp, $matCompItemResp);


                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_INF_COMP;
                new Mensagem('As Respostas da Informação Complementar foram atualizadas com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucRespostasInfComp", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        if ($acao == "publicarResultado") {
            // apenas admin
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
                new Mensagem("Você precisa estar logado como administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros principais
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $idEtapaSel = isset($_POST['idEtapaSel']) ? $_POST['idEtapaSel'] : NULL;
                $textoInicial = isset($_POST['textoInicial']) ? $_POST['textoInicial'] : NULL;

                // recuperando chamada
                $chamada = buscarChamadaPorIdCT($idChamada, $idProcesso);

                // recuperando lista de parâmetros do calendário
                $listaCalendario = $chamada->listaItensCalendario(TRUE);
                $vetCal = array(); // vetor para armazenar dados
                foreach ($listaCalendario as $item) {
                    if ($item['editavel']) {
                        if (isset($_POST[$item['idInput1']])) {
                            $vetCal [$item['idInput1']] = $_POST[$item['idInput1']];
                        }
                        if ($item['itemDuplo'] && isset($_POST[$item['idInput2']])) {
                            $vetCal [$item['idInput2']] = $_POST[$item['idInput2']];
                        }
                    }
                }

                // recuperando campos especiais, se houver
                $forcarFinalizacao = isset($_POST['forcarFinalizacao']) && $_POST['forcarFinalizacao'] == FLAG_BD_SIM;
                $dtFinalizacao = isset($_POST['dtFinalizacao']) ? $_POST['dtFinalizacao'] : NULL;
                $arqExterno = isset($_POST['arqExterno']) ? $_POST['arqExterno'] === "true" : FALSE;

//                print_r($arqExterno);
//                exit;
//                
                // persistindo no BD
                $chamada->publicarResultado($idEtapaSel, $vetCal, $textoInicial, $forcarFinalizacao, $dtFinalizacao, $arqExterno);

                //redirecionando
                new Mensagem('Resultado publicado com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucPubResultado", "$CFG->rwww/visao/processo/gerenciarResultadosProcesso.php?idProcesso=$idProcesso&idChamada=$idChamada");
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

/**
 * 
 * @param int $idChamada
 * @param array $listaReservaVagas Array com lista de reserva de vagas
 * @param array $arrayPost Array com o POST enviado
 * @param array $arraySet Endereço de memória do array a ser setado
 * @param int $idPolo Id do polo que se deseja recuperar os dados
 * @param int $idAreaAtu Id da área que se deseja recuperar os dados
 * @param int $seqPoloArea Sequencial utilizado para recuperação de poloArea
 */
function _setInputVagasPost($idChamada, $listaReservaVagas, $arrayPost, &$arraySet, $idPolo = NULL, $idAreaAtu = NULL, $seqPoloArea = NULL) {
    // gerando índice inicial
    $indice = ProcessoChamada::getIdInputVagas($idChamada, $idPolo, $idAreaAtu, $seqPoloArea);

    // não tem reserva de vagas
    if ($listaReservaVagas == NULL) {
        // setando no vetor o dado
        $arraySet[$indice] = $arrayPost[$indice];
    } else {
        // percorrendo lista
        foreach ($listaReservaVagas as $idReserva) {
            // complementando indice e adicionano no vetor
            $indiceTmp = $indice . ProcessoChamada::idInputVagasAddReserva($idReserva);
            $arraySet[$indiceTmp] = $arrayPost[$indiceTmp];
        }
        // publico geral
        $indiceTmp = $indice . ProcessoChamada::idInputVagasAddReserva(NULL);
        $arraySet[$indiceTmp] = $arrayPost[$indiceTmp];
    }
}

/**
 * 
 * @global stdClass $CFG
 * @param EtapaAvalProc $etapa
 * @param string $nmTipo
 * @param string $tipo
 * @param string $nmPagina
 * @param string $nmBotao
 * @return string
 */
function _criaInputNova($etapa, $nmTipo, $tipo, $nmPagina, $nmBotao) {
    global $CFG;
    return "<input title='" . (!$etapa->podeAlterar() ? "Existe uma etapa de seleção finalizada. Não é possível adicionar $nmTipo." : "Adicionar $nmTipo.") . "' " . (!$etapa->podeAlterar() ? "disabled" : "") . " id='botao$tipo' class='btn btn-primary' type='button' onclick=\"javascript: window.location = '$CFG->rwww/visao/$nmPagina?idProcesso={$etapa->getPRC_ID_PROCESSO()}&idEtapaAval={$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'\" value='$nmBotao'>";
}

/**
 * 
 * @param EtapaAvalProc $etapa
 * @param string $nmTipo
 * @param string $tipo
 * @param int $qtItem Quantidade de itens a ser alterado a ordem
 * @return type
 */
function _criaInputAlteraOrdem($etapa, $nmTipo, $tipo, $qtItem) {
    $styleOrdem = $qtItem <= 0 ? "style='display: none'" : "";
    return "<input $styleOrdem title='" . (!$etapa->podeAlterar() ? "Existe uma etapa de seleção finalizada. Não é possível alterar a ordem $nmTipo." : "Alterar ordem $nmTipo.") . "' " . (!$etapa->podeAlterar() ? "disabled" : "") . " class='btn btn-default' type='button' onclick=\"javascript: alterarOrdem('$tipo', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Alterar Ordem'>";
}

/**
 * Esta função retona o HTML para criação de uma etapa
 * 
 * @param EtapaAvalProc $etapa
 * @return string HTML da Etapa de avaliação
 */
function getHtmlEtapaAval($etapa, $aberta = FALSE) {
    $nmEtapa = EtapaAvalProc::$NM_INICIAL_ETAPA . " " . $etapa->getEAP_NR_ETAPA_AVAL();
    $aberta = $aberta ? "in" : "";

    // html de inputs
    $novaCat = _criaInputNova($etapa, 'categoria de avaliação', 'Cat', 'categoriaAvalProc/criarEditarCategoria.php', 'Nova Categoria');
    $alteraOrdemCat = _criaInputAlteraOrdem($etapa, 'das categorias de avaliação', 'Cat', contarCatAvalPorProcEtapaTpCT($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_NR_ETAPA_AVAL()));

    $novoCritEli = _criaInputNova($etapa, 'critério de eliminação', 'CritEli', 'macroConfProc/manterCriterioEliminacao.php', 'Novo Critério');
    $alteraOrdemCritEli = _criaInputAlteraOrdem($etapa, 'do critério de eliminação', 'CritEli', contarMacroPorProcEtapaCT($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC(), MacroConfProc::$TIPO_CRIT_ELIMINACAO));

    $novoCritClas = _criaInputNova($etapa, 'critério de classificação', 'CritCla', 'macroConfProc/manterCriterioClassificacao.php', 'Novo Critério');
    $alteraOrdemCritClas = _criaInputAlteraOrdem($etapa, 'do critério de classificação', 'CritCla', contarMacroPorProcEtapaCT($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC(), MacroConfProc::$TIPO_CRIT_CLASSIFICACAO));

    $novoCritDes = _criaInputNova($etapa, 'critério de desempate', 'CritDes', 'macroConfProc/manterCriterioDesempate.php', 'Novo Critério');
    $alteraOrdemCritDes = _criaInputAlteraOrdem($etapa, 'do critério de desempate', 'CritDes', contarMacroPorProcEtapaCT($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC(), MacroConfProc::$TIPO_CRIT_DESEMPATE));

    $novoCritSel = _criaInputNova($etapa, 'critério de seleção', 'CritSel', 'macroConfProc/manterCriterioSelecao.php', 'Novo Critério');
    $alteraOrdemCritSel = _criaInputAlteraOrdem($etapa, 'do critério de seleção', 'CritSel', contarMacroPorProcEtapaCT($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC(), MacroConfProc::$TIPO_CRIT_SELECAO));


    $ret = "<div class='panel panel-default'>
                    <div class='panel-heading'>
                        <h4 class='panel-title'>
                            <a class='pull-left' style='width: 99%' data-toggle='collapse' data-parent='#accordion1' href='#col{$etapa->getEAP_NR_ETAPA_AVAL()}'>
                              $nmEtapa
                            </a> 
                            <span class='pull-right' style=' width:1%'>";

    if ($etapa->podeExcluir()) {
        $ret .= "<a onclick=\"javascript: excluirEtapa({$etapa->getEAP_ID_ETAPA_AVAL_PROC()}, '{$etapa->getNomeEtapa()}');
                                            return false;\" id='linkExcluir' title='Excluir etapa' href=''><span class='fa fa-trash-o'></span></a>";
    } else {
        $ret .= "<a onclick='javascript: return false;' id='linkExcluir' title='Não é possível excluir esta etapa de avaliação, pois ela possui itens ou já existe uma chamada configurada.'><i class='fa fa-ban'></i></a>";
    }

    $ret .= "</span>
                    </h4>
                    </div>
                    <div id='col{$etapa->getEAP_NR_ETAPA_AVAL()}' class='panel-collapse collapse $aberta'>
                        <div class='panel-body'>
                            <div id='mensagemCat{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display:none'>
                                <div class='alert alert-info'>
                                    Aguarde o processamento...
                                </div>
                            </div>

                            <div id='erroOrdemCat{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' class='alert alert-danger' style='display:none'>
                                A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
                            </div>
                            
                            <a id='linkCatAvaliacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' href='#catAvaliacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'></a>
                            <fieldset id='catAvaliacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                <legend>Categorias de Avaliação</legend>
                                
                                <div class='completo'>
                                    <div class='pull-left'>
                                        $novaCat
                                    </div>
                                    <div class='pull-right'>
                                        <span id='spanVisualizacaoCat{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: block'>
                                            $alteraOrdemCat
                                        </span>
                                        <span id='spanEdicaoCat{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'>
                                            <span id='botaoCat{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                                <input type='submit' class='btn btn-success' onclick=\"javascript: salvarOrdem('Cat', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Salvar'>
                                                <input type='button' class='btn btn-default' onclick=\"javascript: cancelarAlteracaoOrdem('Cat', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Cancelar'>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class='completo m01'>
                                    <span id='spanTabelaCat{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>";

    $ret .= tabelaCategoriaPorProcEtapa($etapa);
    $ret .= "</span></div>
                            </fieldset>
                            
                            <a id='linkCritEliminacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' href='#critEliminacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'></a>
                            <fieldset class='completo m02' id='critEliminacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                <legend>Critérios de Eliminação</legend>
                                
                                <div id='mensagemCritEli{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display:none'>
                                    <div class='alert alert-info'>
                                        Aguarde o processamento...
                                    </div>
                                </div>

                                <div id='erroOrdemCritEli{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' class='alert alert-danger' style='display:none'>
                                    A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
                                </div>
                                
                                <div class='completo'>
                                    <div class='pull-left'>
                                        $novoCritEli
                                    </div>
                                    <div class='pull-right'>
                                        <span id='spanVisualizacaoCritEli{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: block'>
                                            $alteraOrdemCritEli
                                        </span>
                                        <span id='spanEdicaoCritEli{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'>
                                            <span id='botaoCritEli{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                                <input type='submit' class='btn btn-success' onclick=\"javascript: salvarOrdem('CritEli', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Salvar'>
                                                <input type='button' class='btn btn-default' onclick=\"javascript: cancelarAlteracaoOrdem('CritEli', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Cancelar'>
                                            </span>
                                        </span>
                                    </div>
                                </div>

                            <div class='completo m01'>
                                <span id='spanTabelaCritEli{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>";

    $ret .= tabelaMacroConfProcPorProcEtapa($etapa, MacroConfProc::$TIPO_CRIT_ELIMINACAO);
    $ret .= "</span></div>
                            </fieldset>
                            
                            <a id='linkCritClassificacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' href='#critClassificacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'></a>
                            <fieldset class='completo m02' id='critClassificacao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                <legend>Critérios de Classificação <small><i class='fa fa-question-circle' title='Se não informado, a classificação será decrescente, de acordo com a nota total obtida.'></i></small></legend>
                                <div id='mensagemCritCla{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display:none'>
                                    <div class='alert alert-info'>
                                        Aguarde o processamento...
                                    </div>
                                </div>

                                <div id='erroOrdemCritCla{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' class='alert alert-danger' style='display:none'>
                                    A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
                                </div>
                                
                                <div class='completo'>
                                    <div class='pull-left'>
                                        $novoCritClas
                                    </div>
                                    <div class='pull-right'>
                                        <span id='spanVisualizacaoCritCla{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: block'>
                                            $alteraOrdemCritClas
                                        </span>
                                        <span id='spanEdicaoCritCla{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'>
                                            <span id='botaoCritCla{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                                <input type='submit' class='btn btn-success' onclick=\"javascript: salvarOrdem('CritCla', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Salvar'>
                                                <input type='button' class='btn btn-default' onclick=\"javascript: cancelarAlteracaoOrdem('CritCla', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Cancelar'>
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class='completo m01'>
                                    <span id='spanTabelaCritCla{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>";

    $ret .=tabelaMacroConfProcPorProcEtapa($etapa, MacroConfProc::$TIPO_CRIT_CLASSIFICACAO);
    $ret .= "</span></div>

                                <a id='linkCritDesempate{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' href='#critDesempate{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'></a>
                                <fieldset class='completo m02' id='critDesempate{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' class='itemInterno'>

                                <legend>Desempate <small><i class='fa fa-question-circle' title='Na persistência de empate, o critério final é a ordem de inscrição.'></i></small></legend>
                                    <div id='mensagemCritDes{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display:none'>
                                        <div class='alert alert-info'>
                                            Aguarde o processamento...
                                        </div>
                                    </div>

                                    <div id='erroOrdemCritDes{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' class='alert alert-danger' style='display:none'>
                                        A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
                                    </div>
                                    
                                    <div class='completo'>
                                        <div class='pull-left'>
                                        $novoCritDes
                                        </div>
                                        <div class='pull-right'>
                                            <span id='spanVisualizacaoCritDes{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: block'>
                                                $alteraOrdemCritDes
                                            </span>
                                            <span id='spanEdicaoCritDes{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'>
                                                <span id='botaoCritDes{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                                    <input type='submit' class='btn btn-small btn-primary' onclick=\"javascript: salvarOrdem('CritDes', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Salvar'>
                                                    <input type='button' class='btn btn-small' onclick=\"javascript: cancelarAlteracaoOrdem('CritDes', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Cancelar'>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class='completo m01'>
                                        <span id='spanTabelaCritDes{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>";

    $ret .=tabelaMacroConfProcPorProcEtapa($etapa, MacroConfProc::$TIPO_CRIT_DESEMPATE);
    $ret .= "</span></div>
                                </fieldset>
                            </fieldset>
                            
                            <a id='linkCritSelecao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' href='#critSelecao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'></a>
                            <fieldset class='completo m02' id='critSelecao{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                <legend>Critérios de Seleção</legend>
                                <div id='mensagemCritSel{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display:none'>
                                    <div class='alert alert-info'>
                                        Aguarde o processamento...
                                    </div>
                                </div>

                                <div id='erroOrdemCritSel{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' class='alert alert-danger' style='display:none'>
                                    A nova ordenação está incorreta. Verifique se todos os campos estão preenchidos corretamente e tente novamente.
                                </div>
                                
                                <div class='completo'>
                                <div class='pull-left'>
                                    $novoCritSel
                                </div>
                                <div class='pull-right'>
                                    <span id='spanVisualizacaoCritSel{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: block'>
                                        $alteraOrdemCritSel
                                    </span>
                                    <span id='spanEdicaoCritSel{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}' style='display: none'>
                                        <span id='botaoCritSel{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>
                                            <input type='submit' class='btn btn-success' onclick=\"javascript: salvarOrdem('CritSel', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Salvar'>
                                            <input type='button' class='btn btn-default' onclick=\"javascript: cancelarAlteracaoOrdem('CritSel', '{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}')\" value='Cancelar'>
                                        </span>
                                    </span>
                                </div>

                                <div class='completo m01'>
                                    <span id='spanTabelaCritSel{$etapa->getEAP_ID_ETAPA_AVAL_PROC()}'>";

    $ret .= tabelaMacroConfProcPorProcEtapa($etapa, MacroConfProc::$TIPO_CRIT_SELECAO);
    $ret .= "</span></div>
                            </fieldset>
                        </div>
                    </div>
                </div>";


    return $ret;
}

/**
 * 
 * @global stdClass $CFG
 * @param FiltroInfCompProc $filtroInfCompProc
 * @return string
 */
function tabelaInfCompProcPorFiltro($filtroInfCompProc) {
    global $CFG;

    //recuperando 
    $gruposProc = buscarGrupoPorProcessoCT($filtroInfCompProc->getIdProcesso(), $filtroInfCompProc->getInicioDados(), $filtroInfCompProc->getQtdeDadosPag());

    if (count($gruposProc) == 0) {
        $ret = "<div class='completo callout callout-warning'>Nenhuma informação complementar cadastrada.</div>";
        return $ret;
    }

    $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
    <thead>
    <tr>
        <th>Código</th>
        <th class='campoDesktop'>Ordem</th>
        <th>Nome</th>
        <th class='campoDesktop'>Tipo</th>
        <th class='campoDesktop'>Obrigatória</th>
        <th class='campoDesktop'>Avaliação</th>
        <th class='botao'><i class='fa fa-eye'></i></th>
        <th class='botao'><i class='fa fa-sliders'></i></th>
        <th class='botao'><i class='fa fa-edit'></i></th>
        <th class='botao'><i class='fa fa-trash-o'></i></th>
    </tr>
    </thead>";

    // buscando processo para verificação de edição
    $processo = buscarProcessoComPermissaoCT($filtroInfCompProc->getIdProcesso());
    $permiteEdicao = permiteManterGrupoAnexoProcCT($processo);
    $escopoOdemInfComp = GrupoAnexoProc::$ID_ESCOPO_ORDEM_INF_COMP;
    $codInfComp = GrupoAnexoProc::$COD_TP_ORDENACAO;

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($gruposProc); $i++) {
        $temp = $gruposProc[$i - 1];

        $linkConsultar = "<a id='linkConsultar' title='Consultar Informação Complementar' href='$CFG->rwww/visao/grupoAnexoProc/consultarGrupoAnexoProc.php?idGrupoAnexoProc={$temp->getGAP_ID_GRUPO_PROC()}' class='itemTabela'><i class='fa fa-eye'></i></a>";

        if ($permiteEdicao) {
            $linkItens = "<a id='linkItens' title='Alterar opções de resposta da questão' href='$CFG->rwww/visao/itemAnexoProc/manterItemAnexoProc.php?idGrupoAnexoProc={$temp->getGAP_ID_GRUPO_PROC()}' class='itemTabela'><i class='fa fa-sliders'></i></a>";
            $linkEditar = "<a id='linkEditar' title='Editar esta pergunta' href='$CFG->rwww/visao/grupoAnexoProc/criarEditarGrupoAnexoProc.php?idGrupoAnexoProc={$temp->getGAP_ID_GRUPO_PROC()}' class='itemTabela'><i class='fa fa-edit'></i></a>";
            $linkExcluir = "<a onclick=\"javascript: excluirInfComp({$temp->getGAP_ID_GRUPO_PROC()}, '{$temp->getGAP_NM_GRUPO()}');return false;\" id='linkExcluir' title='Excluir esta pergunta' href=''><i class='fa fa-trash-o'></i></a>";
        } else {
            $linkItens = "<a onclick='return false' id='linkItens' title='Você não pode manter as opções de resposta desta pergunta' class='itemTabela'><i class='fa fa-ban'></i></a>";
            $linkEditar = "<a onclick='return false' id='linkEditar' title='Você não pode editar esta pergunta' class='itemTabela'><i class='fa fa-ban'></i></a>";
            $linkExcluir = "<a onclick='return false' id='linkExcluir' title='Você não pode excluir esta pergunta' class='itemTabela'><i class='fa fa-ban'></i></a>";
        }

        $ret .= "<tr>
        <td>{$temp->getGAP_ID_GRUPO_PROC()}</td>
        <td class='campoDesktop'><span id='{$escopoOdemInfComp}ordem{$codInfComp}{$temp->getGAP_ID_GRUPO_PROC()}'>{$temp->getGAP_NR_ORDEM_EXIBICAO()}</span></td>
        <td>{$temp->getGAP_NM_GRUPO()}</td>
        <td class='campoDesktop'>{$temp->getDsTipoGrupoObj()}</td>
        <td class='campoDesktop'>{$temp->getDsGrupoObrigatorio()}</td>
        <td class='campoDesktop'>{$temp->getDsTipoAvalObj()}</td>
        <td class='botao'>$linkConsultar</td>

        <td class='botao'>";

        if ($temp->possuiOpcoesResposta()) {
            $ret .= $linkItens;
        } else {
            $ret .= "<a title='Pergunta dissertativa não tem opção de resposta'><i class='fa fa-ban'></i></a>";
        }

        $ret .= "</td>

        <td class='botao'>$linkEditar</td>
        <td class='botao'>$linkExcluir</td> 
        </tr>";
    }

    $ret .= "</table></div>
        <div class='campoMobile' style='margin-bottom:2em;'>
            Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
        </div>";

    return $ret;
}

/**
 * Essa função exibe a tabela de vagas de uma chamada
 * 
 * @param ProcessoChamada $chamada
 * @param Processo $processo 
 * @return string
 */
function tabelaVagasPorChamada($chamada, $processo, $edicao = FALSE, $idPolos = FALSE, $idAreasAtu = FALSE, $idReservaVagas = FALSE) {

    // Dados externos 
    $dadosExternos = $edicao && $idPolos !== FALSE && $idAreasAtu !== FALSE && $idReservaVagas !== FALSE;

    // verificando chamada correta
    if ($dadosExternos && !$edicao) {
        // ERRO: Não se pode montar uma tabela a partir de dados externos sem ser para edição
        throw new NegocioException("Impossível gerar tabela de vagas com a configuração pedida.");
    }


    // verificando se a chamada tem algumas configurações
    $temAreaAtuacao = (!$dadosExternos && $chamada->admiteAreaAtuacaoObj()) || ($dadosExternos && !Util::vazioNulo($idAreasAtu));
    $temPolo = $chamada->admitePoloObj();


    // tem botão no início da tabela?
    $temAdicaoLinha = $edicao && $temAreaAtuacao && $temPolo;
    if ($temAdicaoLinha) {
        $ret = "<input type='button' id='adicionarVaga' class='btn btn-primary' value='Nova Vaga'><br/><br/>";
    } else {
        $ret = "";
    }

    //recuperando dados e criando cabecalho
    $temPoloOuArea = FALSE;
    $ret .= "<div class='table-responsive'><table id='tabelaVagas' class='table table-hover table-bordered'>
                <thead>
                    <tr>";

    // polos
    if ($temPolo) {
        $temPoloOuArea = TRUE;
        $polos = buscarPoloCompPorChamadaCT($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo(), $dadosExternos ? $idPolos : NULL);
        $ret .= "<th>Polo</th>";
    }

    // áreas de atuação
    if ($temAreaAtuacao) {
        $temPoloOuArea = TRUE;
        $areasAtu = buscarAreaAtuCompPorChamadaCT($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva(), $dadosExternos ? $idAreasAtu : NULL);
        $ret .= "<th>Área de Atuação</th>";
    }

    // adicionando coluna padrão
    if (!$temPoloOuArea) {
        $ret .= "<th>&nbsp;</th>";
    }

    // reserva de vagas
    $temReservaVagas = (!$dadosExternos && $chamada->admiteReservaVagaObj()) || ($dadosExternos && !Util::vazioNulo($idReservaVagas));
    if ($temReservaVagas) {
        $reservasVaga = buscarReservaVagaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaAtiva(), $dadosExternos ? $idReservaVagas : NULL);
        foreach ($reservasVaga as $reserva) {
            $dsReserva = $reserva->RVG_DS_RESERVA_VAGA;
            $nmReserva = $reserva->RVG_NM_RESERVA_VAGA;

            $ret .= "<th><span title='$dsReserva'>$nmReserva</span></th>";
        }
        // Público geral
        $reservaGeral = ReservaVagaChamada::$DS_PUBLICO_GERAL;
        $ret .= "<th>$reservaGeral</th>";
    } else {
        $reservasVaga = array();
    }


    // sem edição? colocar total
    if (!$edicao || !$temReservaVagas) {
        // inserindo coluna de total
        $ret .= "<th>T. Vagas</th>";
    }

    // colocando coluna a mais para botão excluir
    if ($temAdicaoLinha) {
        // inserindo coluna de botão
        $ret .= "<th class='botao'><i class='fa fa-trash-o'></i></th>";
    }

    // finalizando cabecalho
    $ret .= "</tr>
            </thead>";


    // +++++++++++++++++++++++++++++++++++++++++++ Exibindo Dados ++++++++++++++++++++++++++++++++++++++++ 
    // sem polo e área
    if (!$temPoloOuArea) {
        $ret .= "<tr>";

        $ret .= "<th>Total de vagas da chamada</th>";

        // tratando reserva de vaga
        $ret .= _trataReservaVaga($chamada, ProcessoChamada::getIdInputVagas($chamada->getPCH_ID_CHAMADA()), $temAdicaoLinha, $temReservaVagas, $dadosExternos, $edicao, $reservasVaga, $chamada->getPCH_QT_VAGAS());
    } else {
        // demais casos:
        // Apenas polo
        if ($temPolo && !$temAreaAtuacao) {
            // verificando necessidade de carregar vagas de reserva
            $reservaPolo = $temReservaVagas ? buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO) : NULL;


            // percorrendo polos
            foreach ($polos as $polo) {
                $idPolo = $polo->getPOL_ID_POLO();
                $dsPolo = $polo->POL_DS_POLO;
                $qtVagas = $polo->getPPC_QT_VAGAS();


                $ret .= "<tr>
                            <td>$dsPolo</td>";

                // tratando reserva de vaga
                $ret .= _trataReservaVaga($chamada, ProcessoChamada::getIdInputVagas($chamada->getPCH_ID_CHAMADA(), $idPolo), $temAdicaoLinha, $temReservaVagas, $dadosExternos, $edicao, $reservasVaga, $qtVagas, $reservaPolo, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_POLO, $idPolo, NULL, $dadosExternos));
            }


            // não é edição? linha final de sumarização
            if (!$edicao) {
                // linha final com total de vagas
                $ret .= "<tr>
                            <td><b>Total de vagas</b></td>";

                // tratando reserva de vaga
                $ret .= _trataReservaVaga($chamada, ProcessoChamada::getIdInputVagas($chamada->getPCH_ID_CHAMADA()), $temAdicaoLinha, $temReservaVagas, $dadosExternos, $edicao, $reservasVaga, $chamada->getPCH_QT_VAGAS(), NULL, NULL, TRUE);
            }


            // Apenas área de atuação
        } elseif (!$temPolo && $temAreaAtuacao) {
            // verificando necessidade de carregar vagas de reserva
            $reservaArea = $temReservaVagas ? buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_AREA) : NULL;

            // percorrendo área
            foreach ($areasAtu as $area) {
                $idAreaAtu = $area->getARC_ID_SUBAREA_CONH();
                $nmArea = $area->ARC_NM_SUBAREA_CONH;
                $qtVagas = $area->getAAC_QT_VAGAS();

                $ret .= "<tr>
                            <td>$nmArea</td>";

                // tratando reserva de vaga
                $ret .= _trataReservaVaga($chamada, ProcessoChamada::getIdInputVagas($chamada->getPCH_ID_CHAMADA(), NULL, $idAreaAtu), $temAdicaoLinha, $temReservaVagas, $dadosExternos, $edicao, $reservasVaga, $qtVagas, $reservaArea, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_AREA, NULL, $idAreaAtu, $dadosExternos));
            }


            // não é edição? linha final de sumarização
            if (!$edicao) {
                // linha final com total de vagas
                $ret .= "<tr>
                            <td><b>Total de vagas</b></td>";

                // tratando reserva de vaga
                $ret .= _trataReservaVaga($chamada, ProcessoChamada::getIdInputVagas($chamada->getPCH_ID_CHAMADA()), $temAdicaoLinha, $temReservaVagas, $dadosExternos, $edicao, $reservasVaga, $chamada->getPCH_QT_VAGAS(), NULL, NULL, TRUE);
            }


            // Área de atuação e polo juntos
        } else {
            // verificando necessidade de carregar vagas de reserva
            $reservaPoloArea = $temReservaVagas ? buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO_AREA) : NULL;

            // recuperando dados de polo e área
            $polosAreas = buscarPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), $dadosExternos ? $idPolos : NULL, $dadosExternos ? $idAreasAtu : NULL);

            // recuperando listas para select
            if ($edicao) {
                if ($dadosExternos) {
                    $listaSelectPolos = buscarPolosPorIdsCT($idPolos);
                    $listaSelectAreaAtu = buscarAreasConhPorIdsCT($idAreasAtu);
                } else {
                    $temp = PoloAreaChamada::getListasPolosAreas($polosAreas);
                    $listaSelectPolos = $temp[0];
                    $listaSelectAreaAtu = $temp[1];
                }
            }

            // percorrendo matriz 
            $i = 0;
            foreach ($polosAreas as $poloArea) {
                if ($edicao) {
                    // preparando selects
                    $selectPolo = getHtmlSelectGenerico(ProcessoChamada::getIdInputSelectPolo($chamada->getPCH_ID_CHAMADA(), $i), $listaSelectPolos, $poloArea->getPOL_ID_POLO(), TRUE);
                    $selectAreaAtu = getHtmlSelectGenerico(ProcessoChamada::getIdInputSelectAreaAtu($chamada->getPCH_ID_CHAMADA(), $i), $listaSelectAreaAtu, $poloArea->ARC_ID_SUBAREA_CONH, TRUE);

                    $ret .= "<tr id='$i'>
                                <td>$selectPolo</td>
                                <td>$selectAreaAtu</td>";
                } else {

                    if (!$dadosExternos) {
                        $ret .= "<tr>
                                    <td>{$poloArea->POL_DS_POLO}</td>
                                    <td>{$poloArea->ARC_NM_SUBAREA_CONH}</td>";
                    } else {
                        // caso impossível dada a verificação adicional
                    }
                }



                $qtVagas = $poloArea->getPAC_QT_VAGAS();
                $idPolo = $poloArea->getPOL_ID_POLO();
                $idAreaAtu = $poloArea->ARC_ID_SUBAREA_CONH;

                // tratando reserva de vaga
                $ret .= _trataReservaVaga($chamada, ProcessoChamada::getIdInputVagas($chamada->getPCH_ID_CHAMADA(), $idPolo, $idAreaAtu, $i), $temAdicaoLinha, $temReservaVagas, $dadosExternos, $edicao, $reservasVaga, $qtVagas, $reservaPoloArea, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_POLO_AREA, $idPolo, $idAreaAtu, $dadosExternos));

                // incrementando 
                $i++;
            }

            // não é edição? linha final de sumarização
            if (!$edicao) {

                // linha final com total de vagas
                $ret .= "<tr>
                            <td colspan='2'><b>Total de vagas</b></td>";

                // tratando reserva de vaga
                $ret .= _trataReservaVaga($chamada, ProcessoChamada::getIdInputVagas($chamada->getPCH_ID_CHAMADA()), $temAdicaoLinha, $temReservaVagas, $dadosExternos, $edicao, $reservasVaga, $chamada->getPCH_QT_VAGAS(), NULL, NULL, TRUE);
            }
        }
    }// fim tem area ou polo

    $ret .= "</table></div>";

    return $ret;
}

/**
 * 
 * @param ProcessoChamada $chamada
 * @param int $idInputEdicao
 * @param boolean $temAdicaoLinha
 * @param boolean $temReservaVagas
 * @param boolean $dadosExternos Informa se a tabela a ser impressa é baseada em dados externos
 * @param boolean $edicao Informa se a tabela está sendo impressa para edição
 * @param ReservaVagaChamada $listaReservaVaga Array com lista de reserva de vagas
 * @param int $totalVagas
 * @param array $listaQtReserva Lista com quantidade de vagas indexada segundo a regra especial da classe ReservaPoloArea
 * @param string $indiceReserva Indice a ser utilizado na função de recuperação da quantidade de vagas da linha em questão
 * @param boolean $linhaTotal Informa se a linha em questão é a linha de sumarização dos resultados
 * @return string
 */
function _trataReservaVaga($chamada, $idInputEdicao, $temAdicaoLinha, $temReservaVagas, $dadosExternos, $edicao, $listaReservaVaga, $totalVagas, $listaQtReserva = NULL, $indiceReserva = NULL, $linhaTotal = FALSE) {
    $ret = "";
    if ($temReservaVagas) {
        // caso de reseva de vaga
        $contaReserva = 0;

        // contabilizando reservas
        foreach ($listaReservaVaga as $reserva) {
            // definindo valor das vagas
            if ($listaQtReserva != NULL) {
                $qtReserva = ReservaPoloArea::getValorIndiceBusca($listaQtReserva, $indiceReserva, $reserva->getRVC_ID_RESERVA_CHAMADA(), $dadosExternos);
            } else {
                $qtReserva = $reserva->getRVC_QT_VAGAS_RESERVADAS();
            }
            $contaReserva += $qtReserva;


            // linha total
            if ($linhaTotal) {
                $qtReserva = "<b>$qtReserva</b>";
            } elseif ($edicao) {
                $inputTemp = $idInputEdicao . ProcessoChamada::idInputVagasAddReserva($reserva->getRVG_ID_RESERVA_VAGA());
                $qtReserva = "<input title='Campo Obrigatório.' type='text' id='$inputTemp' name='$inputTemp' class='form-control' size='5' maxlength='5' value='$qtReserva' required>";
            } else {
                // cadastro de reserva
                if ($qtReserva == 0) {
                    $qtReserva = ProcessoChamada::getCodCadastroReserva($chamada);
                }
            }

            $ret .= "<td>$qtReserva</td>";
        }


        // publico geral
        $totalPubGeral = $totalVagas - $contaReserva;
        if ($linhaTotal) {
            $totalPubGeral = "<b>$totalPubGeral</b>";
        } elseif ($edicao) {
            $inputTemp = $idInputEdicao . ProcessoChamada::idInputVagasAddReserva(ReservaVagaChamada::$ID_PUBLICO_GERAL);
            $totalPubGeral = "<input title='Campo Obrigatório.' type='text' id='$inputTemp' name='$inputTemp' class='form-control' size='5' maxlength='5' value='$totalPubGeral' required>";
        } else {
            // cadastro de reserva
            if ($totalPubGeral == 0) {
                $totalPubGeral = ProcessoChamada::getCodCadastroReserva($chamada);
            }
        }
        $ret .= "<td>{$totalPubGeral}</td>";
    }


    // Parte sem reverva: apresenta total de vagas se não for edição
    if (!$edicao || !$temReservaVagas) {
        if ($linhaTotal) {
            $totalVagas = "<b>$totalVagas</b>";
        } elseif ($edicao) {
            $totalVagas = "<input title='Campo Obrigatório' type='text' id='$idInputEdicao' name='$idInputEdicao' class='form-control' size='5' maxlength='5' value='$totalVagas' required>";
        } else {
            // cadastro de reserva
            if ($totalVagas == 0) {
                $totalVagas = ProcessoChamada::getCodCadastroReserva($chamada);
            }
        }

        $ret .= "<td>$totalVagas</td>";
    }

    // botão de excluir
    if ($temAdicaoLinha) {
        $ret .= "<td class='botao'>
                    <a title='Remover Vaga' onclick='javascript: removeLinha(this);'>
                        <i class='fa fa-trash-o'></i>
                    </a>
                </td></tr>";
    } else {
        $ret .= "</tr>";
    }

    return $ret;
}

function validaNomeGrupoAnexoProcCTAjax($idProcesso, $nmGrupo, $idGrupoAnexoProc = NULL) {
    try {
        return GrupoAnexoProc::validaNomeGrupoAnexoProc($idProcesso, $nmGrupo, $idGrupoAnexoProc);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

function teveDownloadCompInscricaoCT($idProcesso, $idChamada) {
    try {
        return InscricaoProcesso::teveDownloadCompInscricao($idProcesso, $idChamada);
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
 * Essa função exibe a tabela de resumo da situação de vagas de uma chamada
 * 
 * @param ProcessoChamada $chamada
 * @param Processo $processo 
 * @return string
 */
function tabelaResumoPorChamada($chamada, $processo) {


    // verificando se a chamada tem algumas configurações
    $temAreaAtuacao = $chamada->admiteAreaAtuacaoObj();
    $temPolo = $chamada->admitePoloObj();

    //recuperando dados e criando cabecalho
    $temPoloOuArea = FALSE;
    $ret = "<div class='table-responsive'><table id='tabelaResumo' class='table table-hover table-bordered'>
                <thead>
                    <tr>";

    // polos
    if ($temPolo) {
        $temPoloOuArea = TRUE;
        $polos = buscarPoloCompPorChamadaCT($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo());
        $ret .= "<th>Polo</th>";
    }

    // áreas de atuação
    if ($temAreaAtuacao) {
        $temPoloOuArea = TRUE;
        $areasAtu = buscarAreaAtuCompPorChamadaCT($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva());
        $ret .= "<th>Área de Atuação</th>";
    }

    // adicionando coluna padrão
    if (!$temPoloOuArea) {
        $ret .= "<th>&nbsp;</th>";
    }

    // reserva de vagas
    $temReservaVagas = $chamada->admiteReservaVagaObj();
    if ($temReservaVagas) {
        $reservasVaga = buscarReservaVagaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaAtiva());
        $inscritosReservaVaga = $temReservaVagas ? contarInscricaoPorChamReservaVagasCT($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA()) : NULL;
        foreach ($reservasVaga as $reserva) {
            $dsReserva = $reserva->RVG_DS_RESERVA_VAGA;
            $nmReserva = $reserva->RVG_NM_RESERVA_VAGA;

            $ret .= "<th><span title='$dsReserva'>$nmReserva</span></th>";
        }
        // Público geral
        $reservaGeral = ReservaVagaChamada::$DS_PUBLICO_GERAL;
        $ret .= "<th>$reservaGeral</th>";
    } else {
        $reservasVaga = array();
        $inscritosReservaVaga = array();
    }

    // recuperando total de inscritos da chamada
    $totalInscritosCham = contarInscricaoPorProcessoChamCT($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());

    // inserindo coluna de total
    $ret .= "<th>T. Inscritos</th>";

    // finalizando cabecalho
    $ret .= "</tr>
            </thead>";


    // +++++++++++++++++++++++++++++++++++++++++++ Exibindo Dados ++++++++++++++++++++++++++++++++++++++++ 
    // sem polo e área
    if (!$temPoloOuArea) {
        $ret .= "<tr>";

        $ret .= "<td>Resumo das inscrições</td>";

        // tratando reserva de vaga
        $ret .= _trataResumoReservaVaga($chamada, $temReservaVagas, $reservasVaga, $inscritosReservaVaga, $chamada->getPCH_QT_VAGAS(), $totalInscritosCham);
    } else {
        // demais casos:
        // Apenas polo
        if ($temPolo && !$temAreaAtuacao) {
            // verificando necessidade de carregar vagas de reserva
            $reservaPolo = $temReservaVagas ? buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO) : NULL;
            $inscritosReservaPolo = $temReservaVagas ? buscarInscritosReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO) : NULL;

            // recuperando inscritos por polo
            $inscritosPolo = contarInscricaoPorChamPoloCT($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());


            // percorrendo polos
            foreach ($polos as $polo) {
                $idPolo = $polo->getPOL_ID_POLO();
                $dsPolo = $polo->POL_DS_POLO;
                $qtVagas = $polo->getPPC_QT_VAGAS();
                $qtInscritos = isset($inscritosPolo[$idPolo]) ? $inscritosPolo[$idPolo] : 0;


                $ret .= "<tr>
                            <td>$dsPolo</td>";

                // tratando reserva de vaga
                $ret .= _trataResumoReservaVaga($chamada, $temReservaVagas, $reservasVaga, $inscritosReservaVaga, $qtVagas, $qtInscritos, $reservaPolo, $inscritosReservaPolo, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_POLO, $idPolo, NULL));
            }



            // linha final com total de vagas
            $ret .= "<tr>
                        <td><b>Total de inscritos</b></td>";

            // tratando reserva de vaga
            $ret .= _trataResumoReservaVaga($chamada, $temReservaVagas, $reservasVaga, $inscritosReservaVaga, $chamada->getPCH_QT_VAGAS(), array_sum($inscritosPolo), NULL, NULL, NULL, TRUE);


            // Apenas área de atuação
        } elseif (!$temPolo && $temAreaAtuacao) {
            // verificando necessidade de carregar vagas de reserva
            $reservaArea = $temReservaVagas ? buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_AREA) : NULL;
            $inscritosReservaArea = $temReservaVagas ? buscarInscritosReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_AREA) : NULL;

            // recuperando inscritos por área
            $inscritosArea = contarInscricaoPorChamAreaAtuacaoCT($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());

            // percorrendo área
            foreach ($areasAtu as $area) {
                $idAreaAtu = $area->getARC_ID_SUBAREA_CONH();
                $nmArea = $area->ARC_NM_SUBAREA_CONH;
                $qtVagas = $area->getAAC_QT_VAGAS();
                $qtInscritos = isset($inscritosArea[$area->getAAC_ID_AREA_CHAMADA()]) ? $inscritosArea[$area->getAAC_ID_AREA_CHAMADA()] : 0;

                $ret .= "<tr>
                            <td>$nmArea</td>";

                // tratando reserva de vaga
                $ret .= _trataResumoReservaVaga($chamada, $temReservaVagas, $reservasVaga, $inscritosReservaVaga, $qtVagas, $qtInscritos, $reservaArea, $inscritosReservaArea, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_AREA, NULL, $idAreaAtu));
            }


            // linha final com total de vagas
            $ret .= "<tr>
                        <td><b>Total de inscritos</b></td>";

            // tratando reserva de vaga
            $ret .= _trataResumoReservaVaga($chamada, $temReservaVagas, $reservasVaga, $inscritosReservaVaga, $chamada->getPCH_QT_VAGAS(), array_sum($inscritosArea), NULL, NULL, NULL, TRUE);


            // Área de atuação e polo juntos
        } else {
            // verificando necessidade de carregar vagas de reserva
            $reservaPoloArea = $temReservaVagas ? buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO_AREA) : NULL;
            $inscritosReservaPoloArea = $temReservaVagas ? buscarInscritosReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO_AREA) : NULL;

            // recuperando inscritos por polo e área
            $inscritosPoloArea = contarInscricaoPorChamPoloAreaAtuCT($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());

            // recuperando dados de polo e área
            $polosAreas = buscarPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA());

            // percorrendo matriz 
            $i = 0;
            foreach ($polosAreas as $poloArea) {

                $ret .= "<tr>
                            <td>{$poloArea->POL_DS_POLO}</td>
                            <td>{$poloArea->ARC_NM_SUBAREA_CONH}</td>";


                $qtVagas = $poloArea->getPAC_QT_VAGAS();
                $idPolo = $poloArea->getPOL_ID_POLO();
                $idAreaAtu = $poloArea->ARC_ID_SUBAREA_CONH;
                $qtInscritos = isset($inscritosPoloArea[$poloArea->getPOL_ID_POLO() . ":" . $poloArea->getAAC_ID_AREA_CHAMADA()]) ? $inscritosPoloArea[$poloArea->getPOL_ID_POLO() . ":" . $poloArea->getAAC_ID_AREA_CHAMADA()] : 0;

                // tratando reserva de vaga
                $ret .= _trataResumoReservaVaga($chamada, $temReservaVagas, $reservasVaga, $inscritosReservaVaga, $qtVagas, $qtInscritos, $reservaPoloArea, $inscritosReservaPoloArea, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_POLO_AREA, $idPolo, $idAreaAtu));

                // incrementando 
                $i++;
            }

            //  linha final de sumarização
            // linha final com total de vagas
            $ret .= "<tr>
                            <td colspan='2'><b>Total de inscritos</b></td>";

            // tratando reserva de vaga
            $ret .= _trataResumoReservaVaga($chamada, $temReservaVagas, $reservasVaga, $inscritosReservaVaga, $chamada->getPCH_QT_VAGAS(), array_sum($inscritosPoloArea), NULL, NULL, NULL, TRUE);
        }
    }// fim tem area ou polo

    $ret .= "</table></div>";

    return $ret;
}

/**
 * @param ProcessoChamada $chamada
 * @param boolean $temReservaVagas
 * @param ReservaVagaChamada $listaReservaVaga Array com lista de reserva de vagas
 * @param array $listaInscritosReserva Array com lista de inscritos por reserva de vaga na forma [RVC_ID_RESERVA_CHAMADA, totalInscritos]
 * @param int $totalVagas
 * @param int $totalInscritos
 * @param array $listaQtReserva Lista com quantidade de vagas indexada segundo a regra especial da classe ReservaPoloArea
 * @param array $listaQtInscritosReserva Lista com quantidade de inscritos indexada segundo a regra especial da classe ReservaPoloArea
 * @param string $indiceReserva Indice a ser utilizado na função de recuperação da quantidade de vagas da linha em questão
 * @param boolean $linhaTotal Informa se a linha em questão é a linha de sumarização dos resultados
 * @return string
 */
function _trataResumoReservaVaga($chamada, $temReservaVagas, $listaReservaVaga, $listaInscritosReserva, $totalVagas, $totalInscritos, $listaQtReserva = NULL, $listaQtInscritosReserva = NULL, $indiceReserva = NULL, $linhaTotal = FALSE) {
    $ret = "";
    if ($temReservaVagas) {
        // caso de reserva de vaga
        $contaReserva = 0;
        $contaInscritosReserva = 0;

        // contabilizando reservas
        foreach ($listaReservaVaga as $reserva) {
            // definindo valor das vagas
            if ($listaQtReserva != NULL) {
                $qtReserva = ReservaPoloArea::getValorIndiceBusca($listaQtReserva, $indiceReserva, $reserva->getRVC_ID_RESERVA_CHAMADA());
            } else {
                $qtReserva = $reserva->getRVC_QT_VAGAS_RESERVADAS();
            }
            $contaReserva += $qtReserva;

            // definindo quantidade de inscritos
            if ($listaQtInscritosReserva != NULL) {
                $qtInscritosReserva = ReservaPoloArea::getValorIndiceBusca($listaQtInscritosReserva, $indiceReserva, $reserva->getRVC_ID_RESERVA_CHAMADA());
            } else {
                $qtInscritosReserva = isset($listaInscritosReserva[$reserva->getRVC_ID_RESERVA_CHAMADA()]) ? $listaInscritosReserva[$reserva->getRVC_ID_RESERVA_CHAMADA()] : 0;
            }
            $contaInscritosReserva += $qtInscritosReserva;

            // definindo relação candidatos / vaga
            if ($qtReserva == 0) {
                $candVaga = ProcessoChamada::getCodCadastroReserva($chamada);
            } else {
                $candVaga = NGUtil::formataDecimal($qtInscritosReserva / $qtReserva) . "/1";
            }

            // linha total
            if ($linhaTotal) {
                $qtInscritosReserva = "<b>$qtInscritosReserva</b>";
            }

            $ret .= "<td>$qtInscritosReserva <span class='relCV' title='Relação candidato/vaga'>($candVaga)</span></td>";
        }


        // publico geral
        $totalPubGeral = $totalVagas - $contaReserva;
        $totalInscritosPubGeral = $totalInscritos - $contaInscritosReserva;

        // definindo relação candidatos / vaga
        if ($totalPubGeral == 0) {
            $candVagaGeral = ProcessoChamada::getCodCadastroReserva($chamada);
        } else {
            $candVagaGeral = NGUtil::formataDecimal($totalInscritosPubGeral / $totalPubGeral) . "/1";
        }

        // linha total
        if ($linhaTotal) {
            $totalInscritosPubGeral = "<b>$totalInscritosPubGeral</b>";
        }

        $ret .= "<td>{$totalInscritosPubGeral} <span class='relCV' title='Relação candidato/vaga'>($candVagaGeral)</span></td>";
    }


    // Parte sem reverva: apresenta total de vagas e de inscritos
    // verificando relação candidato vaga
    if ($totalVagas == 0) {
        $candVaga = ProcessoChamada::getCodCadastroReserva($chamada);
    } else {
        $candVaga = NGUtil::formataDecimal($totalInscritos / $totalVagas) . "/1";
    }


    // colocando negrito no somatório
    if ($linhaTotal) {
        $totalInscritos = "<b>$totalInscritos</b>";
    }

    $ret .= "<td>$totalInscritos <span class='relCV' title='Relação candidato/vaga'>($candVaga)</span></td>";

    $ret .= "</tr>";

    return $ret;
}

?>