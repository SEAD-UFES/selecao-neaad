<?php

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/util/Mensagem.php";
require_once $CFG->rpasta . "/negocio/EtapaSelProc.php";
require_once $CFG->rpasta . "/negocio/RecursoResulProc.php";
require_once $CFG->rpasta . "/controle/CTProcesso.php";
require_once $CFG->rpasta . "/util/filtro/FiltroInscritoProcesso.php";

//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctnotas") {

    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];

        //caso de avaliação automática
        if ($acao == "avaliacaoAutomatica") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];

                // avaliando
                InscricaoProcesso::executarAvaliacaoAutomatica($idProcesso, $idChamada);

                //redirecionando
                new Mensagem('Avaliação automática realizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAvalAuto", "$CFG->rwww/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de classificar candidatos
        if ($acao == "classificarCandidatos") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];

                // classificando
                InscricaoProcesso::classificarCandidatos($idProcesso, $idChamada);

                //redirecionando
                new Mensagem('Classificação realizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucClassificacao", "$CFG->rwww/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso=$idProcesso&" . FiltroInscritoProcesso::getTelaTpClassificacao() . "=" . InscricaoProcesso::$ORDEM_INSCRITOS_CLASSIFICACAO);
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        if ($acao == "criarRecurso") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idInscricao = $_POST['idInscricao'];
                $idChamada = $_POST['idChamada'];

                // recuperando dados do recurso
                $tpRecurso = $_POST['tpRecurso'];
                $dsMotivoOutros = $_POST['dsMotivoOutros'];
                $dsJustificativa = $_POST['dsJustificativa'];

                // criando objeto
                $recurso = new RecursoResulProc(NULL, $idChamada, $idInscricao, NULL, NULL, $tpRecurso, $dsMotivoOutros, $dsJustificativa, RecursoResulProc::$SIT_EM_ANALISE, NULL, NULL, NULL);

                //criando recurso
                $recurso->criarRecurso();

                //redirecionando
                new Mensagem('Recurso protocolado com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/recurso/listarRecursoCandidato.php?idInscricao=$idInscricao");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        if ($acao == "criarCategoriaAval") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idEtapaAval = $_POST['idEtapaAval'];

                // criando objeto
                $categoriaAval = new CategoriaAvalProc(NULL, $idProcesso, $_POST['tpCategoriaAval'], NULL, isset($_POST['pontuacaoMax']) ? $_POST['pontuacaoMax'] : NULL, isset($_POST['catExclusiva']) ? $_POST['catExclusiva'] : NULL, NULL, isset($_POST['tpAvalCategoria']) ? $_POST['tpAvalCategoria'] : NULL);

                //criando categoria
                $categoriaAval->criarCategoriaAval($idEtapaAval);

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_AVALIACAO;
                new Mensagem('Categoria cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercaoCat", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idEtapaAval=$idEtapaAval");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "criarMacroConfProc") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idEtapaAval = $_POST['idEtapaAval'];
                $tipoMacro = $_POST['tipoMacro'];
                $idTipoMacro = $_POST['idTipoMacro'];

                // recuperando variáveis da macro
                $listaParam = array();
                $objMacro = MacroAbs::instanciaMacro($tipoMacro, $idTipoMacro, MacroAbs::montaVetParamExt($idProcesso, $idEtapaAval));
                // loop para recuperar parâmetros
                foreach ($objMacro->getListaParam() as $param) {
                    // validando parâmetro
                    if ($param->isObrigatorio() && (!isset($_POST[$param->getId()]) || Util::vazioNulo($_POST[$param->getId()]))) {
                        new Mensagem("Parâmetros da Macro inconsistentes.", Mensagem::$MENSAGEM_ERRO);
                    }
                    if (isset($_POST[$param->getId()]) && !Util::vazioNulo($_POST[$param->getId()])) {
                        $listaParam [$param->getId()] = $_POST[$param->getId()];
                    }
                }

                // criando objeto
                $macroConfProc = new MacroConfProc(NULL, $idProcesso, $idTipoMacro, $tipoMacro, NULL, NULL, $idEtapaAval);

                //criando macro
                $macroConfProc->criarMacroConfProc($listaParam);

                $compTipoMacro = MacroConfProc::getCompTipoMacro($tipoMacro);

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_AVALIACAO;
                new Mensagem('Macro cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucIncCrit$compTipoMacro", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idEtapaAval=$idEtapaAval");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "manterFormulaFinal") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $formula = $_POST['formula'];

                // recuperando processo
                $processo = buscarProcessoComPermissaoCT($idProcesso);

                MacroConfProc::atualizarFormulaFinalProc($processo, $formula);

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_AVALIACAO;
                new Mensagem('Fórmula Final atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucFormulaFinal", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        if ($acao == "criarItemAval") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idCategoriaAval = $_POST['idCategoriaAval'];

                // criando objeto
                $itemAval = new ItemAvalProc(NULL, $idProcesso, $idCategoriaAval, $_POST['tpItemAval'], NULL, isset($_POST['idAreaConh']) ? $_POST['idAreaConh'] : NULL, isset($_POST['idSubareaConh']) ? $_POST['idSubareaConh'] : NULL, isset($_POST['pontuacao']) ? $_POST['pontuacao'] : NULL, isset($_POST['pontuacaoMax']) ? $_POST['pontuacaoMax'] : NULL, NULL, isset($_POST['idGrupo']) ? $_POST['idGrupo'] : NULL);

                // chamando funçao de criaçao no BD
                $itemAval->criarItemAval(isset($_POST['stFormacao']) ? $_POST['stFormacao'] : NULL, isset($_POST['tpExclusivo']) ? $_POST['tpExclusivo'] : NULL, isset($_POST['segGraduacao']) ? $_POST['segGraduacao'] : NULL, isset($_POST['cargaHorariaMin']) ? $_POST['cargaHorariaMin'] : NULL, isset($_POST['dsItemExt']) ? $_POST['dsItemExt'] : NULL);

                //redirecionando
                new Mensagem('Item cadastrado com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/itemAvalProc/listarItemAvalProc.php?idCategoriaAval=$idCategoriaAval");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "editarCategoriaAval") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametro
                $idProcesso = $_POST['idProcesso'];
                $idEtapaAval = $_POST['idEtapaAval'];
                $idCategoriaAval = $_POST['idCategoriaAval'];

                // recuperando objeto
                $categoriaAval = buscarCatAvalPorIdCT($idCategoriaAval);

                // verificando post
                if ($idProcesso != $categoriaAval->getPRC_ID_PROCESSO() || $idEtapaAval != $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC()) {
                    throw new NegocioException("Requisição inválida.");
                }

                // setando campos a atualizar
                $categoriaAval->setCAP_TP_AVALIACAO(isset($_POST['tpAvalCategoria']) ? $_POST['tpAvalCategoria'] : NULL);
                $categoriaAval->setCAP_VL_PONTUACAO_MAX(isset($_POST['pontuacaoMax']) ? $_POST['pontuacaoMax'] : NULL);
                $categoriaAval->setCAP_CATEGORIA_EXCLUSIVA(isset($_POST['catExclusiva']) ? $_POST['catExclusiva'] : NULL);


                //editando categoria
                $categoriaAval->editarCategoriaAval();

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_AVALIACAO;
                new Mensagem('Categoria atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtualizacaoCat", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idEtapaAval=$idEtapaAval");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "editarMacroConfProc") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametro
                $idProcesso = $_POST['idProcesso'];
                $idEtapaAval = $_POST['idEtapaAval'];
                $idMacroConfProc = $_POST['idMacroConfProc'];

                // recuperando objeto
                $macroConfProc = buscarMacroConfProcPorIdCT($idMacroConfProc);

                // verificando post
                if ($idProcesso != $macroConfProc->getPRC_ID_PROCESSO() || ($idEtapaAval != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL && $idEtapaAval != $macroConfProc->getEAP_ID_ETAPA_AVAL_PROC())) {
                    throw new NegocioException("Requisição inválida.");
                }

                // recuperando variáveis da macro
                $listaParam = array();
                $objMacro = MacroAbs::instanciaMacro($macroConfProc->getMCP_TP_MACRO(), $macroConfProc->getMCP_DS_MACRO(), MacroAbs::montaVetParamExt($idProcesso, $idEtapaAval));
                // loop para recuperar parâmetros
                foreach ($objMacro->getListaParam() as $param) {
                    // validando parâmetro
                    if ($param->isObrigatorio() && (!isset($_POST[$param->getId()]) || Util::vazioNulo($_POST[$param->getId()]))) {
                        new Mensagem("Parâmetros da Macro inconsistentes.", Mensagem::$MENSAGEM_ERRO);
                    }
                    if (isset($_POST[$param->getId()]) && !Util::vazioNulo($_POST[$param->getId()])) {
                        $listaParam [$param->getId()] = $_POST[$param->getId()];
                    }
                }

                //editando macro
                $macroConfProc->editarMacroConfProc($listaParam);

                $compTipoMacro = MacroConfProc::getCompTipoMacro($macroConfProc->getMCP_TP_MACRO());

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_AVALIACAO;
                new Mensagem('Macro cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtuCrit$compTipoMacro", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idEtapaAval=$idEtapaAval");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "editarItemAval") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametro
                $idProcesso = $_POST['idProcesso'];
                $idCategoriaAval = $_POST['idCategoriaAval'];
                $idItemAval = $_POST['idItemAval'];

                // recuperando objeto
                $itemAval = buscarItemAvalPorIdCT($idItemAval);

                // verificando post
                if ($idProcesso != $itemAval->getPRC_ID_PROCESSO() || $idCategoriaAval != $itemAval->getCAP_ID_CATEGORIA_AVAL()) {
                    throw new NegocioException("Requisição inválida.");
                }

                // setando campos a atualizar
                $itemAval->setIAP_ID_AREA_CONH(isset($_POST['idAreaConh']) ? $_POST['idAreaConh'] : NULL);
                $itemAval->setIAP_ID_SUBAREA_CONH(isset($_POST['idSubareaConh']) ? $_POST['idSubareaConh'] : NULL);
                $itemAval->setIAP_VAL_PONTUACAO(isset($_POST['pontuacao']) ? $_POST['pontuacao'] : NULL);
                $itemAval->setIAP_VAL_PONTUACAO_MAX(isset($_POST['pontuacaoMax']) ? $_POST['pontuacaoMax'] : NULL);

                // chamando funçao de edicao no BD
                $itemAval->editarItemAval(isset($_POST['stFormacao']) ? $_POST['stFormacao'] : NULL, isset($_POST['tpExclusivo']) ? $_POST['tpExclusivo'] : NULL, isset($_POST['segGraduacao']) ? $_POST['segGraduacao'] : NULL, isset($_POST['cargaHorariaMin']) ? $_POST['cargaHorariaMin'] : NULL, isset($_POST['dsItemExt']) ? $_POST['dsItemExt'] : NULL, isset($_POST['idGrupo']) ? $_POST['idGrupo'] : NULL);

                //redirecionando
                new Mensagem('Item atualizado com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtualizacao", "$CFG->rwww/visao/itemAvalProc/listarItemAvalProc.php?idCategoriaAval=$idCategoriaAval");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "excluirCategoriaAval") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametro
                $idProcesso = $_POST['idProcesso'];
                $idEtapaAval = $_POST['idEtapaAval'];
                $idCategoriaAval = $_POST['idCategoriaAval'];

                // recuperando objeto
                $categoriaAval = buscarCatAvalPorIdCT($idCategoriaAval);

                // verificando post
                if ($idProcesso != $categoriaAval->getPRC_ID_PROCESSO() || $idEtapaAval != $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC()) {
                    throw new NegocioException("Requisição inválida.");
                }

                // excluindo categoria
                $categoriaAval->excluirCategoriaAval();

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_AVALIACAO;
                new Mensagem('Categoria excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusaoCat", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idEtapaAval=$idEtapaAval");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "excluirItemAval") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametro
                $idProcesso = $_POST['idProcesso'];
                $idCategoriaAval = $_POST['idCategoriaAval'];
                $idItemAval = $_POST['idItemAval'];

                // recuperando objeto
                $itemAval = buscarItemAvalPorIdCT($idItemAval);

                // verificando post
                if ($idProcesso != $itemAval->getPRC_ID_PROCESSO() || $idCategoriaAval != $itemAval->getCAP_ID_CATEGORIA_AVAL()) {
                    throw new NegocioException("Requisição inválida.");
                }

                // chamando funçao de exclusao no BD
                $itemAval->excluirItemAval();

                //redirecionando
                new Mensagem('Item excluído com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/itemAvalProc/listarItemAvalProc.php?idCategoriaAval=$idCategoriaAval");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "excluirMacroConfProc") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametro
                $idProcesso = $_POST['idProcesso'];
                $idMacroConfProc = $_POST['idMacroConfProc'];

                // recuperando objeto
                $macroConfProc = buscarMacroConfProcPorIdCT($idMacroConfProc);

                // verificando post
                if ($idProcesso != $macroConfProc->getPRC_ID_PROCESSO()) {
                    throw new NegocioException("Requisição inválida.");
                }

                // chamando funçao de exclusao no BD
                $macroConfProc->excluirMacroConfProc();

                $compTipoMacro = MacroConfProc::getCompTipoMacro($macroConfProc->getMCP_TP_MACRO());

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_AVALIACAO;
                new Mensagem('Macro excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExcCrit$compTipoMacro", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso&idEtapaAval={$macroConfProc->getEAP_ID_ETAPA_AVAL_PROC()}");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "excluirEtapaAval") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametro
                $idProcesso = $_POST['idProcesso'];
                $idEtapaAval = $_POST['idEtapaAval'];

                // recuperando objeto
                $etapaAval = buscarEtapaAvalPorIdCT($idEtapaAval);

                // verificando post
                if ($idProcesso != $etapaAval->getPRC_ID_PROCESSO()) {
                    throw new NegocioException("Requisição inválida.");
                }

                // chamando funçao de exclusao no BD
                $etapaAval->excluirEtapaAval();

                //redirecionando
                $strAba = Util::$ABA_PARAM . "=" . Util::$ABA_MPA_AVALIACAO;
                new Mensagem('Etapa excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExcEtapa", "$CFG->rwww/visao/processo/manterProcessoAdmin.php?$strAba&idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        if ($acao == "responderRecurso") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idInscricao = $_POST['idInscricao'];
                $idChamada = $_POST['idChamada'];
                $idProcesso = $_POST['idProcesso'];
                $idRecurso = $_POST['idRecurso'];

                // recuperando dados do recurso
                $stRecurso = $_POST['stRecurso'];
                $dsAnalise = $_POST['dsAnalise'];

                $enviarEmail = isset($_POST['enviarEmail']);

                // criando objeto
                $recurso = new RecursoResulProc($idRecurso, $idChamada, $idInscricao, NULL, NULL, NULL, NULL, NULL, $stRecurso, $dsAnalise, NULL, NULL);

                // respondendo recurso
                $recurso->registrarResposta($enviarEmail);

                //redirecionando
                new Mensagem('Recurso respondido com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucRecurso", "$CFG->rwww/visao/recurso/listarRecursoProcesso.php?idProcesso=$idProcesso");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso registrar nota
        if ($acao == "registrarNota") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // recuperando parametros
                $idProcesso = $_POST['idProcesso'];
                $idChamada = $_POST['idChamada'];
                $idInscricao = $_POST['idInscricao'];

                // recuperando itens do relatorio a ignorar
                $relIgnorar = isset($_POST['relIgnorar']) ? $_POST['relIgnorar'] : NULL;

                // recuperando itens 'fantasmas' a ignorar
                $itemIgnorar = isset($_POST['itemIgnorar']) ? $_POST['itemIgnorar'] : NULL;

                // recuperando item exclusivo
                $relExclusivo = isset($_POST['relExclusivo']) ? $_POST['relExclusivo'] : NULL;

                // buscando categorias de avaliaçao automatica para recuperar parametros post
                $etapaAval = buscarEtapaEmAndamentoCT($idChamada);

                $catsAuto = $etapaAval != NULL ? buscarCatAvalPorProcEtapaTpCT($idProcesso, $etapaAval->getESP_NR_ETAPA_SEL(), CategoriaAvalProc::$AVAL_AUTOMATICA, FALSE) : NULL;

                //print_r($catsAuto);
                // recuperando campos de aval Manual de Categoria Auto
                $arrayNotaMan = array();
                $arrayJustMan = array();

                if ($catsAuto != NULL) {
                    foreach ($catsAuto as $cat) {
                        $arrayNotaMan[$cat->getIdNotaManualCatAuto()] = $_POST[$cat->getIdNotaManualCatAuto()];
                        $arrayJustMan[$cat->getIdJustManualCatAuto()] = $_POST[$cat->getIdJustManualCatAuto()];
                    }
                }
//
//                print_r($arrayNotaMan);
//                print_r("<br/>");
//                print_r($arrayJustMan);
//                print_r("<br/>");
//                                
                // recuperando itens de categorias manual para recuperar post
                $arrayNotaItemMan = array();
                $itensMan = $etapaAval != NULL ? buscarItensAvalPorTipoCatEtapaCT($idProcesso, CategoriaAvalProc::$AVAL_MANUAL, $etapaAval->getESP_NR_ETAPA_SEL(), FALSE) : NULL;

                if ($itensMan != NULL) {
                    foreach ($itensMan as $item) {
                        $arrayNotaItemMan[$item->getIdCheckBoxGerencia()] = $_POST[$item->getIdCheckBoxGerencia()];
                    }
                }

                // recuperando dados de eliminacao
                if (isset($_POST['eliminarCand'])) {
                    $justEliminacao = $_POST['justEliminacaoCand'];
                } else {
                    $justEliminacao = NULL;
                }

                // salvando nota
                InscricaoProcesso::registrarAvalEtapaCdt($idProcesso, $idChamada, $idInscricao, $relIgnorar, $itemIgnorar, $relExclusivo, $arrayNotaMan, $arrayJustMan, $arrayNotaItemMan, $justEliminacao);

                //redirecionando
                new Mensagem('Nota atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucNota", "$CFG->rwww/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso=$idProcesso");
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

function validaClassificarCandsCTAjax($idChamada) {
    try {
        return EtapaSelProc::validarClassifCands($idChamada);
    } catch (Exception $e) {
        //retornando false
        return array('errInt' => true, 'msg' => $e->getMessage());
    }
}

function validaExportacaoCTAjax($idProcesso, $idTipoExportacao, $idChamada, $idEtapaAval) {
    try {
        return Processo::validarExportacaoDados($idProcesso, $idTipoExportacao, $idChamada, $idEtapaAval);
    } catch (Exception $e) {
        //retornando false
        return array('errInt' => true, 'msg' => $e->getMessage());
    }
}

function validaAvaliacaoAutomaticaCTAjax($idChamada) {
    try {
        return EtapaSelProc::validarExecAvalAutomatica($idChamada);
    } catch (Exception $e) {
        //retornando false
        return array('errInt' => true, 'msg' => $e->getMessage());
    }
}

function validaFormulaFinalProcCTAjax($formula, $idProcesso) {
    try {
        return MacroConfProc::validaFormulaFinalProc($formula, $idProcesso);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

/**
 * Retorna uma etapa na situaçao aberta ou em recurso
 * 
 * @param int $idChamada
 * @return EtapaSelProc
 */
function buscarEtapaEmAndamentoCT($idChamada) {
    try {
        return EtapaSelProc::buscarEtapaEmAndamento($idChamada);
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

function buscarEtapaAtivaCT($idChamada) {
    try {
        return EtapaSelProc::buscarEtapaAtiva($idChamada);
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

function permiteMostrarClassificacaoCT($etapa) {
    try {
        return EtapaSelProc::permiteMostrarClassificacao($etapa);
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

function buscarNotaMaxPorEtapaCT($idProcesso, $nrEtapa) {
    try {
        return EtapaSelProc::buscarNotaMaxPorEtapa($idProcesso, $nrEtapa);
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
 * Retorna uma etapa na situaçao em recurso
 * 
 * @param int $idChamada
 * @return EtapaSelProc
 */
function buscarEtapaEmRecursoCT($idChamada) {
    try {
        return EtapaSelProc::buscarEtapaEmRecurso($idChamada);
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

function buscarEtapaAjaxPorChamadaCT($idChamada) {
    try {
        return EtapaSelProc::buscarEtapaAjaxPorChamada($idChamada);
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

function buscarEtapaPenPorChamadaCT($idProcesso, $idChamada) {
    try {
        return EtapaSelProc::buscarEtapaPenPorChamada($idProcesso, $idChamada);
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

function buscarSomaNotasPorCatInscCT($idInscricao, $idCategoria, $stAvaliacao = NULL, $tpAvaliacao = NULL) {
    try {
        return RelNotasInsc::buscarSomaNotasPorCatInsc($idInscricao, $idCategoria, $stAvaliacao, $tpAvaliacao);
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

function buscarSomaNotasPorEtapaInscCT($idProcesso, $nrEtapa, $idInscricao, $stAvaliacao = NULL, $tpAvaliacao = NULL) {
    try {
        return RelNotasInsc::buscarSomaNotasPorEtapaInsc($idProcesso, $nrEtapa, $idInscricao, $stAvaliacao, $tpAvaliacao);
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
 * FUNCAO DE PROCESSAMENTO INTERNO. USE COM CUIDADO!
 * @param ItemAvalProc $listaItens - Array
 * @param CategoriaAvalProc $categoria
 * @param int $idCandidato
 * @return ItemAvalProc - Array
 */
function verifica_casamento_itensCT($listaItens, $categoria, $idCandidato) {
    try {
        return ItemAvalProc::verifica_casamento_itens($listaItens, $categoria, $idCandidato);
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

function buscarEtapaVigenteCT($idChamada, $idEtapaSel = NULL) {
    try {
        return EtapaSelProc::buscarEtapaVigente($idChamada, $idEtapaSel);
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
 * @return EtapaSelProc Array de etapas
 */
function buscarEtapaPorChamadaCT($idChamada) {
    try {
        return EtapaSelProc::buscarEtapaPorChamada($idChamada);
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
 * @param FiltroRecurso $filtro
 * @return type
 */
function contarRecursosPorInscricaoCT($filtro) {
    try {
        return RecursoResulProc::contarRecursosPorInscricao($filtro->getIdInscricao(), $filtro->getIdEtapa());
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
 * @param int $idEtapa
 * @param int $inicioDados
 * @param int $qtdeDados
 * @return RecursoResulProc
 */
function buscarRecursoPorInscricaoCT($idInscricao, $idEtapa, $inicioDados, $qtdeDados) {
    try {
        return RecursoResulProc::buscarRecursoPorInscricao($idInscricao, $idEtapa, $inicioDados, $qtdeDados);
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

function validaPeriodoRecursoCT($idEtapa) {
    try {
        return EtapaSelProc::validaPeriodoRecurso($idEtapa);
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

function buscarRecursoPorFiltroCT($idRecurso, $idOrdemInsc, $stSituacao, $idChamada, $idEtapa, $inicioDados, $qtdeDados) {
    try {
        return RecursoResulProc::buscarRecursoPorFiltro($idRecurso, $idOrdemInsc, $stSituacao, $idChamada, $idEtapa, $inicioDados, $qtdeDados);
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
 * Retorna um vetor com [Possibilidade, Mensagem]
 * 
 * @param int $idInscricao
 * @param int $idEtapa
 */
function podehabilitarRecursoCT($idInscricao, $idEtapa) {

    // fora do periodo de recurso 
    if (!validaPeriodoRecursoCT($idEtapa)) {
        return array(FALSE, "Esta etapa não está aceitando recursos.");
    }

    // ja existe recurso
    if (contarRecursoPorInscEtapaCT($idInscricao, $idEtapa) > 0) {
        return array(FALSE, "Você já protocolou um recurso para esta etapa.");
    }

    // tudo ok
    return array(TRUE);
}

function buscarItemAvalPorIdCT($idItem) {
    try {
        return ItemAvalProc::buscarItemAvalPorId($idItem);
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

function buscarSelectEtapaAvalPorProcCT($idProcesso, $insNova = TRUE, $apenasEditaveis = TRUE) {
    try {
        return EtapaAvalProc::buscarSelectEtapaAvalPorProc($idProcesso, $insNova, $apenasEditaveis);
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

function cargaAjaxParamMacroCT($funcCallBack, $listaParam) {
    try {
        return _cargaAjaxParamMacro($funcCallBack, $listaParam);
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

function buscarGruposItemAvalPorCatCT($idCategoria) {
    try {
        return ItemAvalProc::buscarGruposPorCat($idCategoria);
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
 * @param int $tpAvalCat
 * @param int $nrEtapa
 * @param boolean $comAutomatizada Informa se deve retornar os itens da categoria de avaliação automatizada
 * @return ItemAvalProc - Array de Itens de Avaliacao
 */
function buscarItensAvalPorTipoCatEtapaCT($idProcesso, $tpAvalCat, $nrEtapa, $comAutomatizada = TRUE) {
    try {
        return ItemAvalProc::buscarItensAvalPorTipoCatEtapa($idProcesso, $tpAvalCat, $nrEtapa, $comAutomatizada);
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

function buscarRecursoPorIdCT($idRecurso, $idInscricao) {
    try {
        return RecursoResulProc::buscarRecursoPorId($idRecurso, $idInscricao);
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

function contarRecursoPorInscEtapaCT($idInscricao, $idEtapa) {
    try {
        return RecursoResulProc::contarRecursoPorInscEtapa($idInscricao, $idEtapa);
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

function contarCatAvalPorProcEtapaTpCT($idProcesso, $nrEtapa, $tipoAval = NULL) {
    try {
        return CategoriaAvalProc::contarCatAvalPorProcNrEtapaTp($idProcesso, $nrEtapa, $tipoAval);
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
 * @param FiltroRecurso $filtro
 * @return int
 */
function contarRecursoPorFiltroCT($filtro) {
    try {
        return RecursoResulProc::contarRecursoPorFiltro($filtro->getIdRecurso(), $filtro->getOrdemInsc(), $filtro->getStSituacao(), $filtro->getIdChamada(), $filtro->getIdEtapaTela());
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
 * @param FiltroItemAvalProc $filtro
 * @return int
 */
function contarItemAvalPorCategoriaCT($filtro) {
    try {
        return ItemAvalProc::contarItemAvalPorCategoria($filtro->getIdCategoriaAval());
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

function validarCadastroCatCTAjax($idProcesso, $idEtapaAval, $tpCategoriaAval, $tpAvalCategoria, $catExclusiva, $edicao = FALSE, $idCategoriaAval = NULL) {
    try {
        return CategoriaAvalProc::validarCadastroCat($idProcesso, $idEtapaAval, $tpCategoriaAval, $tpAvalCategoria, $catExclusiva, $edicao, $idCategoriaAval);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

function validarCadastroItemAvalCTAjax($idProcesso, $idCategoriaAval, $tpItemAval, $idAreaConh, $idSubareaConh, $stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt, $edicao = FALSE, $idItemAval = NULL) {
    try {
        return ItemAvalProc::validarCadastroItemAval($idProcesso, $idCategoriaAval, $tpItemAval, $idAreaConh, $idSubareaConh, $stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt, $edicao, $idItemAval);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

function validarCadastroMacroCTAjax($idProcesso, $idEtapaAval, $tipoMacro, $idTipoMacro, $paramChave, $edicao = FALSE, $idMacroConfProc = NULL) {
    try {
        return MacroConfProc::validarCadastroMacro($idProcesso, $idEtapaAval, $tipoMacro, $idTipoMacro, $paramChave, $edicao, $idMacroConfProc);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

/**
 * Observacao importante: $idItemIsNotNull e $idItemIsNull nao podem ser 'True' ao mesmo tempo.
 * Caso isso ocorra, $idItemIsNotNull sera prioridade.
 * 
 * @param int $idChamada
 * @param int $idInscricao
 * @param int $idCategoria
 * @param int $idItem
 * @param int $stAvaliacao
 * @param boolean $idItemIsNotNull - Se true e $idItem = NULL, entao ele restringe a busca por itens cujo $idItem nao eh nulo
 * @param int $tpAvaliacao
 * @param boolean $idItemIsNull - Se true e $idItem = NULL, entao ele restringe a busca por itens cujo $idItem eh nulo
 * @return \RelNotasInsc|null - Array com relatorio de notas
 */
function buscarRelNotasPorInscCatItemCT($idChamada, $idInscricao, $idCategoria = NULL, $idItem = NULL, $stAvaliacao = NULL, $idItemIsNotNull = FALSE, $tpAvaliacao = NULL, $idItemIsNull = FALSE) {
    try {
        return RelNotasInsc::buscarRelNotasPorInscCatItem($idChamada, $idInscricao, $idCategoria, $idItem, $stAvaliacao, $idItemIsNotNull, $tpAvaliacao, $idItemIsNull);
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

function buscarCatAvalPorProcEtapaTpCT($idProcesso, $nrEtapa, $tipoAval = NULL, $comAutomatizada = TRUE) {
    try {
        return CategoriaAvalProc::buscarCatAvalPorProcEtapaTp($idProcesso, $nrEtapa, $tipoAval, $comAutomatizada);
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

function buscarCatAvalPorProcIdEtapaCT($idProcesso, $idEtapaAval, $tpCategoria = NULL) {
    try {
        return CategoriaAvalProc::buscarCatAValPorProcIdEtapa($idProcesso, $idEtapaAval, $tpCategoria);
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

function buscarMacroConfProcPorProcEtapaTpCT($idProcesso, $idEtapaAval = NULL, $tipoMacro = NULL) {
    try {
        return MacroConfProc::buscarMacroConfProcPorProcEtapaTp($idProcesso, $idEtapaAval, $tipoMacro);
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

function contarMacroPorProcEtapaCT($idProcesso, $idEtapaAval = NULL, $tpMacro = NULL) {
    try {
        return MacroConfProc::contarMacroPorProcEtapa($idProcesso, $idEtapaAval, $tpMacro);
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

function buscarCatAvalPorIdCT($idCategoriaAval) {
    try {
        return CategoriaAvalProc::buscarCatAvalPorId($idCategoriaAval);
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

function buscarTpCatAvalUsadosPorProcCT($idProcesso, $nrEtapa = NULL) {
    try {
        return CategoriaAvalProc::buscarTpCatAvalUsadosPorProc($idProcesso, $nrEtapa);
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
 * @param int $idCategoria
 * @param int $ordemMaiorQue - Busca apenas itens cuja ordem de avaliaçao eh maior que a ordem informada
 * @return null|\ItemAvalProc - Array com itens
 */
function buscarItensAvalPorCatCT($idProcesso, $idCategoria, $ordemMaiorQue = NULL, $inicioDados = NULL, $qtdeDados = NULL) {
    try {
        return ItemAvalProc::buscarItensAvalPorCat($idProcesso, $idCategoria, $ordemMaiorQue, $inicioDados, $qtdeDados);
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

function buscarMacroConfProcPorIdCT($idMacroConfProc, $idProcesso = NULL) {
    try {
        return MacroConfProc::buscarMacroConfProcPorId($idMacroConfProc, $idProcesso);
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

function buscarMacroConfNotaFinalCT($idProcesso) {
    try {
        return MacroConfProc::buscarMacroConfNotaFinal($idProcesso);
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

function permiteComporNotaFinalCT($processo) {
    try {
        return Processo::permiteComporNotaFinal($processo);
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
 * Imprime a tabela com relatorio de notas da categoria para visualizaçao ou ediçao
 * @param InscricaoProcesso $inscricao
 * @param CategoriaAvalProc $categoria
 * @param boolean $edicao
 */
function tabelaRelatorioNotas($inscricao, $categoria, $edicao = FALSE) {
    $adendo = "";

    // tratando possivel configuracao errada
    if ($categoria->isAvalManual() && $categoria->isCategoriaExclusiva()) {
        new Mensagem("Configuração de categorias de avaliação incorreta!", Mensagem::$MENSAGEM_ERRO);
        return;
    }

    // tratando categorias de avaliaçao automatica
    if ($categoria->isAvalAutomatica()) {
        // iniciando tabela
        $ret = "<div class='col-full table-responsive'>
            <table class='table table-hover table-bordered'>
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th class='numero'>Nota <i class='fa fa-question-circle' title='Nota sem critério de pontuação máxima.'></i></th>
                        <th title='Nota Normalizada' class='numero'>Norm. <i class='fa fa-question-circle' title='Nota com critério de pontuação máxima.'></i></th>";
        if ($edicao) {
            $ret .= "<th class='botao'>Ignorar</i></th>";
        } else {
            $ret .= "<th class='botao'><i class='fa fa-tags'></i></th>";
        }
        $ret .= "</tr>
            </thead>";


        // caso de NAO ser categoria exclusiva
        if (!$categoria->isCategoriaExclusiva()) {
            // recuperando itens de avaliaçao
            $itensAval = ItemAvalProc::buscarItensAvalPorCat($inscricao->getPRC_ID_PROCESSO(), $categoria->getCAP_ID_CATEGORIA_AVAL());

            // imprimindo itens
            if ($itensAval != NULL) {
                foreach ($itensAval as $item) {
                    $ret .= _preencheItemAvalTab($item, $categoria, $inscricao, $edicao);
                }
            }
        } else {
            // ATENÇÃO A LIMITAÇÃO: Apenas uma categoria exclusiva por etapa
            // @todo Alterar aqui caso seja necessário mais de uma categoria exclusiva por etapa
            // caso de ser exclusiva
            $temp = _preencheItensAvalTabExclusivo($categoria, $inscricao, $edicao);

            $ret .= $temp[0];
            $adendo = $temp[1];
        }

        if (!$edicao) {
            // incluindo relatorios de ajuste automatico
            $ret .= _incluiAjusteAuto($categoria, $inscricao);
        }

        // incluindo campo manual de nota
        $temp = _incluiCampoManualCatAuto($categoria, $inscricao, $edicao);
        $ret .= $temp[0];
        $adendo .= $temp[1];
    } elseif ($categoria->isAvalManual()) {

        // iniciando tabela
        $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
                    <thead>
                    <tr>
                        <th>Item</th>";
        if ($edicao) {
            $ret .= "<th class='numero'>Nota <i class='fa fa-question-circle' title='Nota do candidato no item'></i></th>";
        } else {
            $ret .= "<th class='numero'>Nota <i class='fa fa-question-circle' title='Nota sem critério de pontuação máxima'></i></th>
                    <th title='Nota Normalizada' class='numero'>Norm. <i class='fa fa-question-circle' title='Nota com critério de pontuação máxima'></i></th>
                    <th><i class='fa fa-tags'></i></th>";
        }
        $ret .= "</tr>
                </thead>";

        // recuperando itens de avaliaçao
        $itensAval = ItemAvalProc::buscarItensAvalPorCat($inscricao->getPRC_ID_PROCESSO(), $categoria->getCAP_ID_CATEGORIA_AVAL());

        // lançando exceçao caso nao tenha itens
        if ($itensAval == NULL) {
            new Mensagem("Categoria '{$categoria->getDsTipo($categoria->getCAP_TP_CATEGORIA())}' sem itens de avaliação.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // imprimindo itens
        foreach ($itensAval as $item) {
            $temp = _preencheItemAvalManualTab($item, $categoria, $inscricao, $edicao);

            $ret .= $temp[0];
            $adendo .= $temp[1];
        }
    } else {
        throw new NegocioException("Tipo de avaliação da categoria incorreto.");
    }

    // incluir somas
    if (!$edicao) {
        // incluindo somatorio da categoria
        $ret .= _incluiSomaCategoria($categoria, $inscricao);
    }

    $ret .= "</table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
                Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    // incluindo adendos
    $ret .= $adendo;

    return $ret;
}

/**
 * 
 * @param InscricaoProcesso $inscricao
 * @param EtapaSelProc $etapa
 * @return string
 */
function imprimirNotaEtapa($inscricao, $etapa) {
    // buscando soma da etapa
    $somaEtapa = buscarSomaNotasPorEtapaInscCT($inscricao->getPRC_ID_PROCESSO(), $etapa->getESP_NR_ETAPA_SEL(), $inscricao->getIPR_ID_INSCRICAO(), RelNotasInsc::$SIT_ATIVA);

    $somaEtapa['soma'] = RelNotasInsc::formataNota($somaEtapa['soma']);
    $somaEtapa['somaNorm'] = RelNotasInsc::formataNota($somaEtapa['somaNorm']);

    // recuperando pontuacao max da etapa
    $notaMaxEtapa = buscarNotaMaxPorEtapaCT($inscricao->getPRC_ID_PROCESSO(), $etapa->getESP_NR_ETAPA_SEL());
    $dsNotaEtapa = "Nota Final na {$etapa->getNomeEtapa()} ($notaMaxEtapa pts)";
    $dsTitNotaEtapa = "Nota Final obtida pelo candidato na {$etapa->getNomeEtapa()}.";


    $ret = "<div class='completo m02'>
                <h3 class='sublinhado'>$dsNotaEtapa</h3>
                <div class='table-responsive col-full'>
                <table class='table table-hover table-bordered'>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class='numero'>Nota <i class='fa fa-question-circle' title='Nota sem critério de pontuação máxima'></i></th>
                            <th title='Nota Normalizada' class='numero'>Norm. <i class='fa fa-question-circle' title='Nota com critério de pontuação máxima'></i></th>
                            <th class='botao'><i class='fa fa-tags'></i></th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr title='$dsTitNotaEtapa'>
                        <td><b>$dsNotaEtapa</b></td>
                        <td class='numero'><b>{$somaEtapa['soma']}</b></td>
                        <td class='numero'><b>{$somaEtapa['somaNorm']}</b></td>
                        <td class='botao'><b>=</b></td>
                    </tr>
                    </tbody>
                </table>
                </div>
                <div class='campoMobile' style='margin-bottom:2em;'>
                    Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
                </div>
        </div>";

    return $ret;
}

/**
 * 
 * @param CategoriaAvalProc $categoria
 * @param InscricaoProcesso $inscricao
 * @return String
 */
function _incluiSomaCategoria($categoria, $inscricao) {

    // tentando recuperar soma
    $soma = buscarSomaNotasPorCatInscCT($inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), RelNotasInsc::$SIT_ATIVA);

    // se soma e nulo, inconsistencia
    if ($soma == NULL) {
        new Mensagem("Inconsistência ao obter soma de categoria. Por favor, informe esta ocorrência ao administrador do sistema.", Mensagem::$MENSAGEM_ERRO);
        return;
    }

    $soma['soma'] = RelNotasInsc::formataNota($soma['soma']);
    $soma['somaNorm'] = RelNotasInsc::formataNota($soma['somaNorm']);

    $desc = RelNotasInsc::getHtmlMsgSomaCategoria();
    $titulo = RelNotasInsc::getHtmlMsgTituloSomaCategoria();

    $ret = "
        <tr title='$titulo'>
            <th>$desc</th>
            <th class='numero'>{$soma['soma']}</th>
            <th class='numero'>{$soma['somaNorm']}</th>
            <th class='botao'>=</th>
        </tr>";

    return $ret;
}

/**
 * Inclui os ajustes automaticos do sistema
 * 
 * @param CategoriaAvalProc $categoria
 * @param InscricaoProcesso $inscricao
 * @return string
 */
function _incluiAjusteAuto($categoria, $inscricao) {

    // tentando recuperar ajustes
    $relItens = buscarRelNotasPorInscCatItemCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), NULL, NULL, NULL, RelNotasInsc::$TP_AVAL_AUTOMATICA, TRUE);

    // se nao existe relatorio, retornar
    if ($relItens == NULL) {
        return "";
    }

    $ret = "";

    // loop nos itens
    foreach ($relItens as $rel) {
        $ret .= "
        <tr title='{$rel->getRNI_DS_OBJ_AVAL()}'>
            <td><i>{$rel->getHtmlMsgAjusteRel()}</i></td>
            <td class='numero'>{$rel->getNotaComMasc()}</td>
            <td class='numero'>{$rel->getNotaNormalizadaComMasc()}</td>
            <td><i class='fa fa-check'></i></td>
        </tr>";
    }

    return $ret;
}

/**
 * FUNCAO DE PROCESSAMENTO INTERNO
 * 
 * Esta funçao inclui o item de avaliaçao manual
 * 
 * @param CategoriaAvalProc $categoria
 * @param InscricaoProcesso $inscricao
 * @param boolean $edicao
 * @return string
 */
function _incluiCampoManualCatAuto($categoria, $inscricao, $edicao) {

    $maxCaracter = RelNotasInsc::$MAX_CARACTER_JUST_AVAL;

    // tentando recuperar relatorio manual
    $relItem = buscarRelNotasPorInscCatItemCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), NULL, NULL, NULL, RelNotasInsc::$TP_AVAL_MANUAL, TRUE);
    if ($relItem != NULL && count($relItem) > 1) {
        new Mensagem("Inconsistência ao obter nota do item manual de categoria automática. Por favor, informe esta ocorrência ao administrador do sistema.", Mensagem::$MENSAGEM_ERRO);
        return;
    }


    // se nao existe relatorio e nao e edicao, retornar
    if (!$edicao && $relItem == NULL) {
        return array("", "");
    }

    if ($relItem != NULL) {
        $notaItem = $relItem[0]->getNotaComMasc();
        $notaNorm = $relItem[0]->getNotaNormalizadaComMasc();
        $justificativa = $relItem[0]->getRNI_DS_JUSTIFICATIVA_AVAL();
    } else {
        $notaItem = $notaNorm = RelNotasInsc::getNotaSemNotaMan();
        $justificativa = "";
    }


    $esconder = $edicao && $categoria->isCategoriaExclusiva() ? "style='display: none'" : "";

    $script = $edicao ? "<script type=\"text/javascript\">$(document).ready(function () {" : "";

    if ($edicao) {
        $ret = "<tr $esconder id='{$categoria->getIdLinhaManualCatAuto()}'>
                <td colspan='4' class='textoEsquerda'>
                    <input class='checkbox-inline' style='display: none;' type='checkbox' name='' id='{$categoria->getIdItemManualCatAuto()}' value=''>
                    <span class='pull-left' class='itemTabela'>
                       {$categoria->getHtmlMsgAvalManualCatAuto()}
                    </span>
                    <span  class='pull-right' class='itemTabela'>
                        <input placeholder='Digite a nota aqui' type='text' name='{$categoria->getIdNotaManualCatAuto()}' id='{$categoria->getIdNotaManualCatAuto()}' class='form-control' value='$notaNorm'>
                    </span>
                    <br/>
                    <br/>
                    <div class='titulo-top'>
                    <span class='itemTabela'>
                        <textarea class='form-control' placeholder='Justificativa da nota manual' id='{$categoria->getIdJustManualCatAuto()}' cols='60' rows='4' name='{$categoria->getIdJustManualCatAuto()}'>$justificativa</textarea>
                    </span> 
                    <div id='{$categoria->getIdContManualCatAuto()}' class='totalCaracteres'>caracteres restantes</div> 
                    </div>
                </td>
            </tr>";
    } else {
        $ret = "
        <tr title='{$justificativa}' id='{$categoria->getIdLinhaManualCatAuto()}'>
            <td>{$categoria->getHtmlMsgAvalManualCatAuto()}</td>
            <td class='numero'>$notaItem</td>
            <td class='numero'>$notaNorm</td>
            <td><i class='fa fa-check'></i></td>
        </tr>";
    }

    if ($edicao) {
        // adicionando mascara
        $script .= Util::getScriptFormatacaoMoeda($categoria->getIdNotaManualCatAuto());

        // adicionando contador do textArea
        $script .= "adicionaContadorTextArea($maxCaracter, '{$categoria->getIdJustManualCatAuto()}', '{$categoria->getIdContManualCatAuto()}');";
    }

    // finalizando script 
    if ($edicao) {
        $script .= "});</script>";
    }

    return array($ret, $script);
}

/**
 * FUNCAO DE PROCESSAMENTO INTERNO
 * @param InscricaoProcesso $inscricao
 * @param RelNotasInsc $relItem
 * @param ItemAvalProc $item
 * @param CategoriaAvalProc $categoria
 * @param boolean $edicao
 * @param string $nmCheckBox - Nome a ser recuperado no servidor. Se NULL, o nome e o padrao da classe.
 * @return string
 */
function _preencheRelatorioItem($inscricao, $relItem, $item, $categoria, $edicao, $nmCheckBox = NULL) {
    if ($relItem != NULL) {
        $notaItem = $relItem->getNotaComMasc();
        $notaNorm = $relItem->getNotaNormalizadaComMasc();
        $ignorado = $relItem->isIgnorado();
        $valueCheck = $relItem->getRNI_ID_REL_NOTA();
    } else {
        $notaItem = $notaNorm = RelNotasInsc::getNotaSemNota();
        $ignorado = NULL;
        $valueCheck = "NULL";
    }

    $desabilitado = (!$edicao || $ignorado === NULL) ? "disabled" : "";
    $textoChecado = $ignorado === NULL ? "title='Item zerado.'" : ($ignorado ? "checked" : "");
    $nmCheckBox = $nmCheckBox == NULL ? "relIgnorar[]" : $nmCheckBox;

    //@todo Fazer titulo com HTML
    $tituloItem = RelNotasInsc::getObjAvalHtml($inscricao, $relItem != NULL ? $relItem : NULL, $item);
    $textoTituloItem = isset($tituloItem) ? "title='$tituloItem'" : "";

    if (!$edicao) {
        $itemIgnorar = "<span>" . ($ignorado !== NULL ? $relItem->htmlMarcacaoIgnorado() : "<i class='fa fa-times'></i>") . "</span>";
    } else {
        //@todo Marcar itens selecionados quando editavel, a fim de identificar as açoes do usuário em tempo de edição
        $itemIgnorar = "<input class='checkbox-inline' $desabilitado $textoChecado type='checkbox' name='$nmCheckBox' id='{$item->getIdCheckBoxGerencia()}' value='$valueCheck'>";
    }

    // classe de notas ignoradas, apenas no modo de visualização
    $classNotaIgnorada = !$edicao && $ignorado === TRUE ? "style='color:gray;'" : "";

    $ret = "
        <tr $textoTituloItem id='{$item->getIdLinhaGerencia()}'>
            <td>{$item->getHmlNomeItem($categoria->getCAP_TP_CATEGORIA())}</td>
            <td $classNotaIgnorada class='numero'>$notaItem</td>
            <td $classNotaIgnorada class='numero'>$notaNorm</td>
            <td $classNotaIgnorada class='botao'>$itemIgnorar</td>
        </tr>";

    return $ret;
}

/**
 * FUNCAO DE PROCESSAMENTO INTERNO
 * @param ItemAvalProc $item
 * @param CategoriaAvalProc $categoria
 * @param InscricaoProcesso $inscricao
 * @param boolean $edicao
 * @return string
 */
function _preencheItemAvalTab($item, $categoria, $inscricao, $edicao) {
    // tentando recuperar nota do candidato
    $relItem = buscarRelNotasPorInscCatItemCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), $item->getIAP_ID_ITEM_AVAL());
    if ($relItem != NULL && count($relItem) > 1) {
        new Mensagem("Inconsistência ao obter nota do Item. Por favor, informe esta ocorrência ao administrador do sistema.", Mensagem::$MENSAGEM_ERRO);
        return;
    }
    return _preencheRelatorioItem($inscricao, $relItem !== NULL ? $relItem[0] : NULL, $item, $categoria, $edicao);
}

/**
 * FUNCAO DE PROCESSAMENTO INTERNO
 * 
 * @param ItemAvalProc $item
 * @param CategoriaAvalProc $categoria
 * @param InscricaoProcesso $inscricao
 * @param boolean $edicao
 * @return array na forma: [stringTabela, scripts]
 */
function _preencheItemAvalManualTab($item, $categoria, $inscricao, $edicao) {
    // tentando recuperar nota do candidato
    $relItem = buscarRelNotasPorInscCatItemCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), $item->getIAP_ID_ITEM_AVAL());
    if ($relItem != NULL && count($relItem) > 1) {
        new Mensagem("Inconsistência ao obter nota do Item Manual. Por favor, informe esta ocorrência ao administrador do sistema.", Mensagem::$MENSAGEM_ERRO);
        return;
    }

    if ($relItem != NULL) {
        $notaItem = $relItem[0]->getNotaComMasc();
        $notaNorm = $relItem[0]->getNotaNormalizadaComMasc();
    } else {
        $notaItem = $notaNorm = RelNotasInsc::getNotaSemNotaMan();
    }

    //@todo Fazer titulo com HTML
    $tituloItem = RelNotasInsc::getObjAvalHtml($inscricao, $relItem != NULL ? $relItem[0] : NULL, $item);
    $textoTituloItem = isset($tituloItem) ? "title='$tituloItem'" : "";

    if (!$edicao) {
        
    } else {
        
    }

    $script = $edicao ? "<script type=\"text/javascript\">$(document).ready(function () {" : "";

    if (!$edicao) {
        $ret = "
        <tr $textoTituloItem>
            <td>{$item->getHmlNomeItem($categoria->getCAP_TP_CATEGORIA())}</td>
            <td class='numero'>$notaItem</td>
            <td class='numero'>$notaNorm</td>
            <td><i class='fa fa-check'></i></td>
        </tr>";
    } else {
        $ret = "
        <tr $textoTituloItem>
            <td>{$item->getHmlNomeItem($categoria->getCAP_TP_CATEGORIA())}</td>
            <td class='numero'><input style='width: 80px;' type='text' name='{$item->getIdCheckBoxGerencia()}' id='{$item->getIdCheckBoxGerencia()}' class='form-control' value='$notaNorm'></td>
        </tr>";
    }

    if ($edicao) {
        // adicionando mascara
        $script .= Util::getScriptFormatacaoMoeda($item->getIdCheckBoxGerencia());
    }

    // finalizando script 
    if ($edicao) {
        $script .= "});</script>";
    }


    return array($ret, $script);
}

/**
 * FUNCAO DE PROCESSAMENTO INTERNO
 * @param CategoriaAvalProc $categoria
 * @param InscricaoProcesso $inscricao
 * @param boolean $edicao
 * @return array - (stringTB, stringCOD)
 */
function _preencheItensAvalTabExclusivo($categoria, $inscricao, $edicao) {
    // tentando recuperar nota do candidato
    $relItem = buscarRelNotasPorInscCatItemCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), NULL, NULL, TRUE, RelNotasInsc::$TP_AVAL_AUTOMATICA);
    if ($relItem != NULL && count($relItem) > 1) {
        new Mensagem("Inconsistência ao obter nota do Item. Por favor, informe esta ocorrência ao administrador do sistema.", Mensagem::$MENSAGEM_ERRO);
        return;
    }

    $script = $edicao ? "<script type=\"text/javascript\">$(document).ready(function () {
                            mostrarExclusivo([" : "";
    $insereScript = FALSE;

    if ($relItem != NULL) {
        // buscar item avaliado
        $itemAval = buscarItemAvalPorIdCT($relItem[0]->getIAP_ID_ITEM_AVAL());

        // adicionando no script
        if ($edicao) {
            $script .= "{$itemAval->getIAP_ID_ITEM_AVAL()}";
            $insereScript = TRUE;
        }

        // preenchendo relatorio do item avaliado
        $ret = _preencheRelatorioItem($inscricao, $relItem[0], $itemAval, $categoria, $edicao, "relExclusivo");

        // buscando itens maiores que ele
        $itensMaiores = buscarItensAvalPorCatCT($inscricao->getPRC_ID_PROCESSO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), $itemAval->getIAP_ORDEM());

        // pegando apenas lista dos maiores que 'casam' com o candidato
        $itensMaiores = verifica_casamento_itensCT($itensMaiores, $categoria, $inscricao->getCDT_ID_CANDIDATO());

        foreach ($itensMaiores as $item) {
            $ret .= _preencheRelatorioItemFantasma($inscricao->getPCH_ID_CHAMADA(), $inscricao->getIPR_ID_INSCRICAO(), $item, $categoria, $edicao);

            // adicionando no script
            if ($edicao) {
                $script .= ", {$item->getIAP_ID_ITEM_AVAL()}";
            }
        }
    } else {
        $dsItem = CategoriaAvalProc::getHtmlMsgCategoriaNaoPontuou();
        $ret = "
        <tr title='Candidato não pontuou na avaliação automática da categoria'>
            <td align='center' colspan=3>{$dsItem}</td>
            <td class='botao'><i class='fa fa-times'></i></td>
        </tr>";
    }

    // adicionando id do item de avaliaçao automatico
    if ($edicao) {
        $script .= $insereScript ? ", '{$categoria->getIdManualCatAuto()}'" : "'{$categoria->getIdManualCatAuto()}'";
    }

    // finalizando script 
    if ($edicao) {
        $script .= "]);});</script>";
    }

    return array($ret, $script);
}

/**
 * FUNCAO DE PROCESSAMENTO INTERNO
 * 
 * Preenche os itens que devem ficar ocultos do usuario
 * 
 * @param ItemAvalProc $item
 * @param CategoriaAvalProc $categoria
 * @param boolean $edicao
 * @return string
 */
function _preencheRelatorioItemFantasma($idChamada, $idInscricao, $item, $categoria, $edicao) {

    //@todo Remover limitaçao de apenas uma categoria exclusiva por etapa
    $notaItem = $item->getHtmlNotaReal();
    $notaNorm = $item->getHtmlNotaNormalizada();

    //@todo Colocar texto do item que comprova o ponto do item fantasma
    $textoTituloItem = "";

    $idCheckIgnorar = $item->getIdCheckBoxGerencia();
    $valueCheckIgnorar = $item->getIAP_ID_ITEM_AVAL();

    // verificando se existe relatorio inserido
    $relItem = buscarRelNotasPorInscCatItemCT($idChamada, $idInscricao, $categoria->getCAP_ID_CATEGORIA_AVAL(), $item->getIAP_ID_ITEM_AVAL());
    if ($relItem != NULL && count($relItem) > 1) {
        new Mensagem("Inconsistência ao obter nota do Item. Por favor, informe esta ocorrência ao administrador do sistema.", Mensagem::$MENSAGEM_ERRO);
        return;
    }

    if ($relItem != NULL) {
        $ignorado = $relItem[0]->isIgnorado();
    } else {
        $ignorado = NULL;
    }

    if (!$edicao) {
        if ($ignorado !== NULL) {
            $iconeSituacao = $ignorado === TRUE ? "fa fa-ban" : "fa fa-check";
        } else {
            $iconeSituacao = "fa fa-times";
        }
    } else {
        //@todo Marcar itens selecionados em tempo de edição
    }

    // classe de notas ignoradas, apenas no modo de visualização
    $classNotaIgnorada = !$edicao && $ignorado === TRUE ? "style='color:gray;'" : "";

    $desabilitado = (!$edicao || $ignorado === NULL) ? "disabled" : "";
    $textoChecado = $ignorado === NULL ? "title='Item zerado.'" : ($ignorado ? "checked" : "");
    $mostrarLinha = ($edicao || (!$edicao && $ignorado === NULL)) ? "style='display: none'" : "";

    $ret = "
        <tr $mostrarLinha id='{$item->getIdLinhaGerencia()}'>
            <td><span $textoTituloItem>{$item->getHmlNomeItem($categoria->getCAP_TP_CATEGORIA())}</td>
            <td $classNotaIgnorada class='numero'>$notaItem</td>
            <td $classNotaIgnorada class='numero'>$notaNorm</td>
            <td $classNotaIgnorada class='botao'>";

    if ($edicao) {
        $ret .= "<input class='checkbox-inline' type='checkbox' $desabilitado $textoChecado name='itemIgnorar[]' id='$idCheckIgnorar' value='$valueCheckIgnorar'>";
    } else {
        $ret .= "<i class='$iconeSituacao'></i>";
    }

    $ret .= "</td>
        </tr>";

    return $ret;
}

/**
 * 
 * @param FiltroRecurso $filtro
 * @return string
 */
function tabelaRecursosPorInscricao($filtro) {

    // recuperando recurso
    $recursos = buscarRecursoPorInscricaoCT($filtro->getIdInscricao(), $filtro->getIdEtapa(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());

    if ($recursos == NULL) {
        return Util::$MSG_TABELA_VAZIA;
    }

    // recuperando recurso da etapa
    $recurso = $recursos[0];

    $ret = "<legend>Recurso Protocolado</legend>
            <table class='mobileBorda table-bordered table' style='width:100%'> 
                <tr>
                    <td class='campo20'><strong>Código:</strong></td>
                    <td class='campo80'>{$recurso->getRRP_ID_RECURSO()}</td>
                </tr>
                <tr>
                    <td class='campo20'><strong>Data:</strong></td>
                    <td class='campo80'>{$recurso->getRRP_DT_RECURSO()}</td>
                </tr>
                <tr>
                    <td class='campo20'><strong>Motivo:</strong></td>
                    <td class='campo80'>{$recurso->getDsMotivo()}</td>
                </tr>
                <tr>
                    <td class='campo20'><strong>Justificativa:</strong></td>
                    <td class='campo80'>{$recurso->getRRP_DS_JUSTIFICATIVA()}</td>
                </tr>
            </table>
        
        <legend class='m02'>Análise do Recurso</legend>
        <table class='mobileBorda table-bordered table' style='width:100%'>
            <tr>
                <td class='campo20'><strong>Situação:</td>
                <td class='campo80'>{$recurso->getDsSituacaoObj()}</td>
            </tr>
            <tr>
                <td class='campo20'><strong>Data da Análise:</td>
                <td class='campo80'>{$recurso->getRRP_DT_ANALISE()}</td>
            </tr>
            <tr>
                <td class='campo20'><strong>Descrição:</td>
                <td class='campo80'>{$recurso->getRRP_DS_ANALISE()}</td>
            </tr>
        </table>";

    return $ret;
}

/**
 * 
 * @global stdClass $CFG
 * @param FiltroRecurso $filtro
 * @return string
 */
function tabelaRecursoPorFiltro($filtro) {
    global $CFG;

    //recuperando recursos
    $recursos = buscarRecursoPorFiltroCT($filtro->getIdRecurso(), $filtro->getOrdemInsc(), $filtro->getStSituacao(), $filtro->getIdChamada(), $filtro->getIdEtapaTela(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());

    if (count($recursos) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<table class='table table-hover table-bordered table-responsive completo'>
        <thead><tr>
        <th>Data do Registro</th>
        <th>Recurso</th>
        <th>Inscrição</th>
        <th>Situação</th>
        <th>Etapa</th>
        <th class='botao'><i class='fa fa-eye'></i></th>
        <th class='botao'><i class='fa fa-reply'></i></th>
    </tr></thead>";

    //iteração para exibir processos
    for ($i = 1; $i <= sizeof($recursos); $i++) {
        $temp = $recursos[$i - 1];
        $idRecurso = $temp->getRRP_ID_RECURSO();
        $linkConsultar = "<a id='linkVisualizar' title='Visualizar Recurso' href='$CFG->rwww/visao/recurso/consultarRecurso.php?idRecurso=$idRecurso&idInscricao={$temp->getIPR_ID_INSCRICAO()}'><span class='fa fa-eye'></span></a>";

        if ($temp->permiteResponder()) {
            $linkResponder = "<a id='linkResponder' title='Responder Recurso' href='$CFG->rwww/visao/recurso/responderRecurso.php?idRecurso=$idRecurso&idInscricao={$temp->getIPR_ID_INSCRICAO()}'><span class='fa fa-reply'></span></a>";
        } else {
            $linkResponder = "<a onclick='return false' id='linkResponder' title='Você já respondeu este recurso' href=''><i class='fa fa-ban'></i></a>";
        }

        $dsSituacao = RecursoResulProc::getDsSituacao($temp->getRRP_ST_SITUACAO());
        $ret .= "<tr>
        <td>{$temp->getRRP_DT_RECURSO()}</td>
        <td>{$temp->getRRP_ID_RECURSO()}</td>
        <td>{$temp->IPR_NR_ORDEM_INSC}</td>
        <td>$dsSituacao</td>
        <td>$temp->ESP_DS_ETAPA_SEL</td>
        <td class='botao'>$linkConsultar</td>
        <td class='botao'>$linkResponder</td>
        </tr>";
    }

    $ret .= "</table>";
    return $ret;
}

/**
 * 
 * @global stdClass $CFG
 * @param EtapaAvalProc $etapa
 * @return string
 */
function tabelaCategoriaPorProcEtapa($etapa) {
    global $CFG;

    //recuperando dados
    $categorias = buscarCatAvalPorProcEtapaTpCT($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_NR_ETAPA_AVAL());

//    print_r($categorias);

    if (count($categorias) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = " <table class='table table-bordered table-hover'>
        <thead><tr>
        <th>Código</th>
        <th>Ordem</th>
        <th>Tipo</th>
        <th>Avaliação</th>
        <th>Pontuação Máx</th>
        <th>Exclusiva</th>
        <th class='botao'><i class='fa fa-edit'></i></th>
        <th class='botao'><i class='fa fa-th-list'></i></th>
        <th class='botao'><i class='fa fa-trash-o'></i></th>
    </tr></thead>";

    //iteração para exibir processos
    for ($i = 1; $i <= sizeof($categorias); $i++) {
        $temp = $categorias[$i - 1];
        $idCategoria = $temp->getCAP_ID_CATEGORIA_AVAL();

        $linkItens = "<a id='linkItens' title='Visualizar itens de avaliação da categoria' href='$CFG->rwww/visao/itemAvalProc/listarItemAvalProc.php?idCategoriaAval=$idCategoria'><i class='fa fa-th-list'></i></a>";

        if ($etapa->podeAlterar() && !$temp->isSomenteLeitura()) {
            $popExc = $temp->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA ? "" : "onclick=\"alert('Esta categoria não possui campos editáveis.');return false;\"";
            $linkEditar = "<a id='linkEditar' $popExc title='Editar esta categoria' href='$CFG->rwww/visao/categoriaAvalProc/criarEditarCategoria.php?idCategoriaAval=$idCategoria'><i class='fa fa-edit'></i></a>";
            $linkExcluir = "<a id='linkExcluir' title='Excluir esta categoria' href='$CFG->rwww/visao/categoriaAvalProc/excluirCategoria.php?idCategoriaAval=$idCategoria'><i class='fa fa-trash-o'></i></a>";
        } else {
            $compTitulo = !$temp->isSomenteLeitura() ? ", pois existe uma etapa de seleção finalizada" : ". Modifique a informação complementar correspondente";
            $linkEditar = "<a onclick='return false' id='linkEditar' title='Não é possível editar esta categoria$compTitulo'><i class='fa fa-ban'></i></a>";
            $linkExcluir = "<a onclick='return false' id='linkExcluir' title='Não é possível excluir esta categoria$compTitulo'><i class='fa fa-ban'></i></a>";
        }

        $dsTipoAval = CategoriaAvalProc::getDsTipoAval($temp->getCAP_TP_AVALIACAO());
        $ret .= "<tr>
        <td>{$temp->getCAP_ID_CATEGORIA_AVAL()}</td>
        <td><span id='{$temp->getEAP_ID_ETAPA_AVAL_PROC()}ordemCat{$temp->getCAP_ID_CATEGORIA_AVAL()}'>{$temp->getCAP_ORDEM()}</span></td>
        <td>{$temp->getNomeCategoria()}</td>
        <td>$dsTipoAval</td>
        <td>{$temp->getDsNotaMax()}</td>
        <td>{$temp->getDsCatExclusiva()}</td>
        <td class='botao'>$linkEditar</td>
        <td class='botao'>$linkItens</td>
        <td class='botao'>$linkExcluir</td>
        </tr>";
    }

    $ret .= "</table>";
    return $ret;
}

/**
 * 
 * @global stdClass $CFG
 * @param FiltroItemAvalProc $filtro
 * @return string
 */
function tabelaItemAvalPorCategoria($filtro) {

    // recuperando etapa para analise
    $categoriaAval = buscarCatAvalPorIdCT($filtro->getIdCategoriaAval());
    $etapa = buscarEtapaAvalPorIdCT($categoriaAval->getEAP_ID_ETAPA_AVAL_PROC());
    $temGrupo = $categoriaAval->getCAP_TP_AVALIACAO() == CategoriaAvalProc::$AVAL_AUTOMATICA;

    //recuperando itens
    $itensAval = buscarItensAvalPorCatCT($filtro->getIdProcesso(), $filtro->getIdCategoriaAval(), NULL, $filtro->getInicioDados(), $filtro->getQtdeDadosPag());

    if (count($itensAval) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    // nao tem grupo, entao tabela com todos os itens
    if (!$temGrupo) {
        $ret = _getTabelaItemAvalProc($itensAval, FALSE, $etapa, $categoriaAval, $filtro->getEdicao());
        return $ret;
    }

    // recuperando quantidade por grupo
    $qtdePorGrupo = ItemAvalProc::qtdePorGrupo($itensAval);

    // percorrendo grupos e montando
    $ret = "";
    $somaItens = 0;
    foreach ($qtdePorGrupo as $grupo => $qtde) {

        $itensGrupo = array_slice($itensAval, $somaItens, $qtde);
        $somaItens += $qtde;
        $dsGrupo = $grupo == ItemAvalProc::getCodSemSubgrupo() ? ItemAvalProc::getMsgSemSubgrupo() : "Grupo $grupo ({$itensGrupo[0]->getIAP_VAL_PONTUACAO_MAX()} pontos)";

        // criando fieldset para grupo
        $ret .= "<fieldset class='col-full'><legend><small>$dsGrupo</small></legend>";

        // colocando itens
        $ret .= _getTabelaItemAvalProc($itensGrupo, $grupo != ItemAvalProc::getCodSemSubgrupo(), $etapa, $categoriaAval, $filtro->getEdicao());

        $ret .= "</fieldset>";
    }

    return $ret;
}

/**
 * 
 * @global stdClass $CFG
 * @param EtapaAvalProc $etapa
 * @param char $tipoMacro Tipo da macro em questão
 * @param Processo $processo Processo em questão. Parâmetro opcional, apenas usado no caso da etapa ser nula.
 * @return string
 */
function tabelaMacroConfProcPorProcEtapa($etapa, $tipoMacro, $processo = NULL) {
    global $CFG;

    // validando chamada
    if ($etapa == NULL && $processo == NULL) {
        new Mensagem("Chamada incorreta de função de exibição de macro.", Mensagem::$MENSAGEM_ERRO);
    }

    //recuperando dados
    $buscaEtapa = $etapa != NULL;
    if ($buscaEtapa) {
        $macroConf = buscarMacroConfProcPorProcEtapaTpCT($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC(), $tipoMacro);
    } else {
        $macroConf = buscarMacroConfProcPorProcEtapaTpCT($processo->getPRC_ID_PROCESSO(), NULL, $tipoMacro);
    }

    if (count($macroConf) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $compPagEdicao = MacroConfProc::getCompTipoMacro($tipoMacro);

    $ret = " <table class='table table-hover table-bordered'>
        <thead><tr>
        <th>Código</th>
        <th>Ordem</th>
        <th>Critério</th>
        <th>Parâmetros</th>
        <th class='botao'><i class='fa fa-edit'></i></th>
        <th class='botao'><i class='fa fa-trash-o'></i></th>
    </tr></thead>";

    //iteração para exibir processos
    for ($i = 1; $i <= sizeof($macroConf); $i++) {
        $temp = $macroConf[$i - 1];
        $idMacro = $temp->getMCP_ID_MACRO_CONF_PROC();

        // ajustando id da etapa
        if (!$buscaEtapa) {
            $temp->setEAP_ID_ETAPA_AVAL_PROC(MacroConfProc::$ID_ETAPA_RESULTADO_FINAL);
        }

        if (($etapa != NULL && $etapa->podeAlterar()) || ($processo != NULL && $processo->permiteEdicao(TRUE))) {
            $temParam = $temp->temParametros() ? "" : "onclick=\"alert('Este critério não possui campos editáveis.');return false;\"";
            $linkEditar = "<a id='linkEditar' $temParam title='Editar critério' href='$CFG->rwww/visao/macroConfProc/manterCriterio$compPagEdicao.php?idMacroConfProc=$idMacro'><i class='fa fa-edit'></i></a>";
            $linkExcluir = "<a onclick=\"javascript: excluirMacroConfProc($idMacro, '{$temp->getDsTipoObj()} - (ID $idMacro)');return false;\" id='linkExcluir' title='Excluir critério' href=''><i class='fa fa-trash-o'></i></a>";
        } else {
            $linkEditar = "<a onclick='return false' id='linkEditar' title='Não é possível editar este critério, pois existe uma etapa de seleção finalizada'><i class='fa fa-ban'></i></a>";
            $linkExcluir = "<a onclick='return false' id='linkExcluir' title='Não é possível excluir este critério, pois existe uma etapa de seleção finalizada'><i class='fa fa-ban'></i></a>";
        }

        $ret .= "<tr>
        <td>{$temp->getMCP_ID_MACRO_CONF_PROC()}</td>
        <td><span id='{$temp->getEAP_ID_ETAPA_AVAL_PROC()}ordem{$temp->getCodTpOrdenacao()}{$temp->getMCP_ID_MACRO_CONF_PROC()}'>{$temp->getMCP_ORDEM_APLICACAO()}</span></td>
        <td>{$temp->getNomeObjMacro()}</td>
        <td>{$temp->getStrParametros()}</td>
        <td class='botao'>$linkEditar</td>
        <td class='botao'>$linkExcluir</td>
        </tr>";
    }

    $ret .= "</table>";
    return $ret;
}

/**
 * 
 * @param int $tipoMacro
 * @param int $idTipoMacro
 * @param boolean $edicao
 * @param int $idMacroConfProc
 * @param array $arrayParamExt Array com parâmetros externos na forma [idParam => vlParam]
 * @return array Array na forma [dadosHtml, dadosScript, dadosScriptAvulso]
 */
function htmlParametrosMacro($tipoMacro, $idTipoMacro, $edicao = NULL, $idMacroConfProc = NULL, $arrayParamExt = NULL) {
    $arrayParamExt = $arrayParamExt != NULL ? $arrayParamExt : array();

    if ($tipoMacro != NULL && !Util::vazioNulo($idTipoMacro)) {
        // definindo os parametros automaticamente
        // 
        // instanciando macro
        $objMacro = MacroAbs::instanciaMacro($tipoMacro, $idTipoMacro, $arrayParamExt);

        // verificando caso de edicao
        if ($edicao) {
            if ($idMacroConfProc != NULL) {
                // recuperando macro
                $macroConfProc = buscarMacroConfProcPorIdCT($idMacroConfProc);

                // atualizando valor
                MacroAbs::carregaValorParam($objMacro, $macroConfProc->getMCP_DS_PARAMETROS());
            }
        }

        // recuperando html dos parâmetros
        $html = $objMacro->getHTMLParametros($arrayParamExt);

        // recuperando script de validacao
        $script = $objMacro->getScriptValidacaoParametros($arrayParamExt);

        // recuperando script avulso
        $script .= $objMacro->getScriptAvulsoParametros($arrayParamExt);

        return array($html, $script);
    } else {
        return "";
    }
}

/**
 * Funcao de processamento interno. 
 * 
 * @param ItemAvalProc $itensAval - Array de itens
 * @param boolean $itemGrupo
 * @param EtapaAvalProc $etapa
 * @param CategoriaAvalProc $categoriaAval
 * @param boolean $edicao
 * @return string
 */
function _getTabelaItemAvalProc($itensAval, $itemGrupo, $etapa, $categoriaAval, $edicao = FALSE) {
    global $CFG;

    $ret = "<table class='table table-hover table-bordered'>
        <thead>
        <tr>
            <th>Ordem</th>
            <th>Código</th>
            <th>Tipo</th>";

    if ($categoriaAval->admiteItensAreaSubareaObj()) {
        $ret .= "<th>Área</th>";
    }

    if ($categoriaAval->isAvalAutomatica() && $categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_TITULACAO) {
        $ret .= "<th>Parâmetros</th>";
    }

    if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA && $categoriaAval->isAvalAutomatica()) {
        $ret .= "<th>Pontuação</th>";
    }

    if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA && !$itemGrupo && !$categoriaAval->isCategoriaExclusiva()) {
        $ret.= "<th>Pontuação Máx</th>";
    }

    if ($edicao) {
        if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
            $ret .= "<th class='botao'><i class='fa fa-edit'></i></th>";
        }
        $ret .= "<th class='botao'><i class='fa fa-trash-o'></i></th>";
    }

    $ret .= "</tr></thead>";

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($itensAval); $i++) {
        $temp = $itensAval[$i - 1];
        $idItemAval = $temp->getIAP_ID_ITEM_AVAL();

        if ($edicao) {
            if ($etapa->podeAlterar() && !$categoriaAval->isSomenteLeitura()) {
                $linkEditar = "<a id='linkEditar' title='Editar este item de avaliação' href='$CFG->rwww/visao/itemAvalProc/criarEditarItemAval.php?idItemAval=$idItemAval'><i class='fa fa-edit'></i></a>";
                $linkExcluir = "<a onclick='javascript: excluirItemAval($idItemAval, $idItemAval);return false;' id='linkExcluir' title='Excluir este item de avaliação' href=''><i class='fa fa-trash-o'></i></a>";
            } else {
                $compTitulo = !$categoriaAval->isSomenteLeitura() ? ", pois existe uma etapa de seleção finalizada." : ". Modifique a informação complementar correspondente.";
                $linkEditar = "<a onclick='return false' id='linkEditar' title='Não é possível editar este item$compTitulo' href=''><i class='fa fa-ban'></i></a>";
                $linkExcluir = "<a onclick='return false' id='linkExcluir' title='Não é possível excluir este item$compTitulo' href=''><i class='fa fa-ban'></i></a>";
            }
        }


        $nota = $categoriaAval->isAvalAutomatica() ? NGUtil::formataDecimal($temp->getIAP_VAL_PONTUACAO()) : NULL;
        $notaMax = NGUtil::formataDecimal($temp->getIAP_VAL_PONTUACAO_MAX());

        $ret .= "<tr>
        <td>{$temp->getIAP_ORDEM()}</td>
        <td>{$temp->getIAP_ID_ITEM_AVAL()}</td>
        <td>{$temp->getDsTipoItem($categoriaAval->getCAP_TP_CATEGORIA())}</td>";

        if ($categoriaAval->admiteItensAreaSubareaObj()) {
            $ret .= "<td>{$temp->getDsAreaSubarea()}</td>";
        }

        if ($categoriaAval->isAvalAutomatica() && $categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_TITULACAO) {
            $ret .= "<td>{$temp->getHtmlAmigavelParametros($categoriaAval->getCAP_TP_CATEGORIA())}</td>";
        }

        if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA && $categoriaAval->isAvalAutomatica()) {
            $ret .= "<td>$nota</td>";
        }

        // nota maxima
        if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA && !$itemGrupo && !$categoriaAval->isCategoriaExclusiva()) {
            $ret .= "<td>$notaMax</td>";
        }

        if ($edicao) {
            if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
                $ret .= "<td class='botao'>$linkEditar</td>";
            }
            $ret .= "<td class='botao'>$linkExcluir</td>";
        }

        $ret .= "</tr>";
    }

    $ret .= "</table>";
    return $ret;
}

?>
