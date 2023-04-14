<?php

/*
 * Controle para requisiçao AJAX em geral
 */
require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/controle/CTCidade.php";
require_once $CFG->rpasta . "/controle/CTCep.php";
require_once $CFG->rpasta . "/controle/CTCandidato.php";
require_once $CFG->rpasta . "/controle/CTProcesso.php";
require_once $CFG->rpasta . "/controle/CTCurriculo.php";

// casos de validaçao de campos
if (isset($_GET['val'])) {
    $validacao = $_GET['val'];
    if ($validacao == "emailCadastro") {
        $dsEmail = $_POST['dsEmail'];
        include_once 'CTUsuario.php';
        if (isset($_GET['idUsuario'])) {
            print validarCadastroEmailCTAjax($dsEmail, $_GET['idUsuario']) ? "true" : "false";
        } else {
            print validarCadastroEmailCTAjax($dsEmail) ? "true" : "false";
        }
        return;
    }

    if ($validacao == "CPFCadastro") {
        include_once 'CTCandidato.php';
        $nrCPF = removerMascara("###.###.###-##", $_POST['nrCPF']);
        if (isset($_GET['idUsuario'])) {
            print validarCadastroCPFCTAjax($nrCPF, $_GET['idUsuario']) ? "true" : "false";
        } else {
            print validarCadastroCPFCTAjax($nrCPF) ? "true" : "false";
        }
        return;
    }

    if ($validacao == "emailAlternativo") {
        $dsEmail = $_POST['dsEmailAlternativo'];
        include_once 'CTUsuario.php';
        if (isset($_GET['idUsuario'])) {
            print validarEmailAlternativoCTAjax($dsEmail, $_GET['idUsuario']) ? "true" : "false";
        } else {
            print validarEmailAlternativoCTAjax($dsEmail) ? "true" : "false";
        }
        return;
    }

    if ($validacao == "emailRecuperarSenha") {
        $dsEmail = $_POST['dsEmail'];
        include_once 'CTUsuario.php';
        print json_encode(validarEmailRecuperarSenhaCTAjax($dsEmail));

        return;
    }

    if ($validacao == "nomeDepartamento") {
        $dsNome = $_POST['dsNome'];
        include_once 'CTDepartamento.php';
        if (isset($_GET['idDepartamento'])) {
            print validaNomeDepartamentoCTAjax($dsNome, $_GET['idDepartamento']) ? "true" : "false";
        } else {
            print validaNomeDepartamentoCTAjax($dsNome) ? "true" : "false";
        }
        return;
    }

    if ($validacao == "nomeCurso") {
        $nmCurso = $_POST['nmCurso'];
        include_once 'CTCurso.php';
        if (isset($_GET['idCurso'])) {
            print validaNomeCursoCTAjax($nmCurso, $_GET['idCurso']) ? "true" : "false";
        } else {
            print validaNomeCursoCTAjax($nmCurso) ? "true" : "false";
        }
        return;
    }

    if ($validacao == "nomeInfCompProc") {
        $idProcesso = $_GET['idProcesso'];
        $nmGrupo = $_POST['nmGrupo'];
        include_once 'CTManutencaoProcesso.php';
        if (isset($_GET['idGrupoAnexoProc'])) {
            print validaNomeGrupoAnexoProcCTAjax($idProcesso, $nmGrupo, $_GET['idGrupoAnexoProc']) ? "true" : "false";
        } else {
            print validaNomeGrupoAnexoProcCTAjax($idProcesso, $nmGrupo) ? "true" : "false";
        }
        return;
    }

    if ($validacao == "formulaFinalProc") {
        $formula = $_POST['formula'];
        include_once 'CTNotas.php';
        print validaFormulaFinalProcCTAjax($formula, $_GET['idProcesso']) ? "true" : "false";
        return;
    }

    if ($validacao == "formacao") {
        include_once 'CTCurriculo.php';
        print validaCriarFormacaoCTAjax($_POST['tpFormacao'], $_POST['nmInstituicao'], $_POST['nmCurso'], $_POST['anoInicio'], isset($_POST['idFormacao']) ? $_POST['idFormacao'] : NULL, $_POST['idUsuario']) ? "true" : "false";
        return;
    }

    if ($validacao == "publicacao") {
        include_once 'CTCurriculo.php';

        validaAreaSubAreaObrigatoria();
        print validaCriarPublicacaoCTAjax($_POST['tpPublicacao'], $_POST['idAreaConh'], $_POST['idSubareaConh'], $_POST['idUsuario']) ? "true" : "false";
        return;
    }

    if ($validacao == "partEvento") {
        include_once 'CTCurriculo.php';

        validaAreaSubAreaObrigatoria();
        print validaCriarPartEventoCTAjax($_POST['tpPartEvento'], $_POST['idAreaConh'], $_POST['idSubareaConh'], $_POST['idUsuario']) ? "true" : "false";
        return;
    }

    if ($validacao == "atuacao") {
        include_once 'CTCurriculo.php';

        validaAreaSubAreaObrigatoria();
        print validaCriarAtuacaoCTAjax($_POST['tpAtuacao'], $_POST['idAreaConh'], $_POST['idSubareaConh'], $_POST['idUsuario']) ? "true" : "false";
        return;
    }

    if ($validacao == "numeracaoEdital") {
        include_once 'CTProcesso.php';
        print validaNumEditalCTAjax($_POST['nrEdital'], $_POST['anoEdital'], $_POST['idTipoCargo'], $_POST['idCurso']) ? "true" : "false";
        return;
    }

    if ($validacao == "categoriaAval") {
        include_once 'CTNotas.php';

        $edicao = $_POST['edicao'] == 'true' ? TRUE : FALSE;
        $idCategoriaAval = $edicao ? $_POST['idCategoriaAval'] : NULL;

        $validou = validarCadastroCatCTAjax($_POST['idProcesso'], $_POST['idEtapaAval'], $_POST['tpCategoriaAval'], $_POST['tpAvalCategoria'], isset($_POST['catExclusiva']) ? $_POST['catExclusiva'] : NULL, $edicao, $idCategoriaAval);

        if ($validou === FALSE) {
            print json_encode(array('situacao' => FALSE, 'msg' => "Erro ao tentar validar cadastro de categoria. Por favor, informe esse problema ao Administrador."));
            return;
        }
        print json_encode(array('situacao' => $validou[0], 'msg' => $validou[1]));
        return;
    }

    if ($validacao == "itemAval") {
        include_once 'CTNotas.php';

        $edicao = $_POST['edicao'] == 'true' ? TRUE : FALSE;
        $idItemAval = $edicao ? $_POST['idItemAval'] : NULL;

        $validou = validarCadastroItemAvalCTAjax($_POST['idProcesso'], $_POST['idCategoriaAval'], $_POST['tpItemAval'], isset($_POST['idAreaConh']) ? $_POST['idAreaConh'] : NULL, isset($_POST['idSubareaConh']) ? $_POST['idSubareaConh'] : NULL, isset($_POST['stFormacao']) ? $_POST['stFormacao'] : NULL, isset($_POST['tpExclusivo']) ? $_POST['tpExclusivo'] : NULL, isset($_POST['segGraduacao']) ? $_POST['segGraduacao'] : NULL, isset($_POST['cargaHorariaMin']) ? $_POST['cargaHorariaMin'] : NULL, isset($_POST['dsItemExt']) ? $_POST['dsItemExt'] : NULL, $edicao, $idItemAval);

        if ($validou === FALSE) {
            print json_encode(array('situacao' => FALSE, 'msg' => "Erro ao tentar validar cadastro de item. Por favor, informe esse problema ao Administrador."));
            return;
        }
        print json_encode(array('situacao' => $validou[0], 'msg' => $validou[1]));
        return;
    }

    if ($validacao == "macroConfProc") {
        include_once 'CTNotas.php';

        $edicao = $_POST['edicao'] == 'true' ? TRUE : FALSE;
        $idMacroConfProc = $edicao ? $_POST['idMacroConfProc'] : NULL;

        $validou = validarCadastroMacroCTAjax($_POST['idProcesso'], $_POST['idEtapaAval'], $_POST['tipoMacro'], $_POST['idTipoMacro'], isset($_POST[ParamMacro::$NM_PARAM_CHAVES]) ? $_POST[ParamMacro::$NM_PARAM_CHAVES] : NULL, $edicao, $idMacroConfProc);

        if ($validou === FALSE) {
            print json_encode(array('situacao' => FALSE, 'msg' => "Erro ao tentar validar cadastro de item. Por favor, informe esse problema ao Administrador."));
            return;
        }
        print json_encode(array('situacao' => $validou[0], 'msg' => $validou[1]));
        return;
    }

    if ($validacao == "classificarCands") {
        include_once 'CTNotas.php';
        print json_encode(validaClassificarCandsCTAjax($_POST['idChamada']));
        return;
    }

    if ($validacao == "exportacao") {
        include_once 'CTNotas.php';
        print json_encode(validaExportacaoCTAjax($_POST['idProcesso'], $_POST['idTipoExportacao'], $_POST['idChamada'], $_POST['idEtapaAval']));
        return;
    }

    if ($validacao == "avaliacaoAutomatica") {
        include_once 'CTNotas.php';
        print json_encode(validaAvaliacaoAutomaticaCTAjax($_POST['idChamada']));
        return;
    }

    // casos de carregamento de selects
} elseif (isset($_POST['cargaSelect'])) {
    $acao = $_POST['cargaSelect'];

    // cidade
    if ($acao == "cidade") {
        $idUf = $_POST['idUf'];
        header('Content-type: text/json');
        try {
            echo json_encode(buscarCidadePorUfCT($idUf));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }

    // area de conhecimento
    if ($acao == "areaConhecimento") {
        $idArea = $_POST['idArea'];
        header('Content-type: text/json');
        try {
            echo json_encode(buscarSubAreaConhPorAreaCT($idArea));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }

    // polos de chamada
    if ($acao == "poloChamada") {
        $idChamada = $_POST['idChamada'];
        $flagSituacao = isset($_POST['flagSituacao']) ? $_POST['flagSituacao'] : NULL;
        header('Content-type: text/json');
        try {
            echo json_encode(buscarPoloPorChamadaCT($idChamada, $flagSituacao));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }

    // etapas de chamada
    if ($acao == "etapaChamada") {
        $idChamada = $_POST['idChamada'];
        header('Content-type: text/json');
        try {
            echo json_encode(buscarEtapaAjaxPorChamadaCT($idChamada));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }

    // areas de atuaçao de chamada
    if ($acao == "areaAtuChamada") {
        $idChamada = $_POST['idChamada'];
        $flagSituacao = isset($_POST['flagSituacao']) ? $_POST['flagSituacao'] : NULL;
        header('Content-type: text/json');
        try {
            echo json_encode(buscarAreaAtuPorChamadaCT($idChamada, $flagSituacao));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }


    // reserva de vagas da chamada
    if ($acao == "reservaVagaChamada") {
        $idChamada = $_POST['idChamada'];
        $flagSituacao = isset($_POST['flagSituacao']) ? $_POST['flagSituacao'] : NULL;
        header('Content-type: text/json');
        try {
            echo json_encode(buscarIdsReservaVagaPorChamadaCT($idChamada, $flagSituacao, TRUE));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }

    // areas de atuaçao de chamada e polo
    if ($acao == "areaAtuChamadaPolo") {
        $idChamada = $_POST['idChamada'];
        $idPolo = $_POST['idPolo'];
        header('Content-type: text/json');
        try {
            echo json_encode(buscarAreaAtuPorChamadaPoloCT($idChamada, $idPolo));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }

    // processos abertos de um curso
    if ($acao == "processoAbtCurso") {
        $idCurso = $_POST['idCurso'];
        header('Content-type: text/json');
        try {
            echo json_encode(buscarProcessoPorCursoCT($idCurso));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }

    // chamadas de um processo
    if ($acao == "chamadaProcesso") {
        $idProcesso = $_POST['idProcesso'];
        header('Content-type: text/json');
        try {
            echo json_encode(buscarIdDsChamadaPorProcessoCT($idProcesso));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }

    // parâmetro de macro de configuracao
    if ($acao == MacroAbs::$CARGA_SELECT_PARAM_AJAX) {
        // recuperando parametros
        $funcCallBack = $_POST[MacroAbs::$PARAM_AJAX_CALLBACK];
        $listaParamCallBack = $_POST[MacroAbs::$PARAM_AJAX_LISTA_PARAM];

        header('Content-type: text/json');
        try {
            echo json_encode(cargaAjaxParamMacroCT($funcCallBack, $listaParamCallBack));
        } catch (Exception $e) {
            echo 'false';
        }
        return;
    }
} elseif (isset($_POST['buscacep'])) {
    $cepBusca = $_POST['buscacep'];
    header('Content-type: text/json');
    try {
        echo json_encode(getEnderecoCEPCT($cepBusca));
    } catch (Exception $e) {
        echo 'false';
    }
} elseif (isset($_GET['lattes'])) {
    if ($_GET['lattes'] == "obter") {
        try {
            $arrayResp = DadoCurriculo::buscarLinkLattesPorUsu($_POST['idUsuario']);
            print json_encode($arrayResp);
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    } elseif ($_GET['lattes'] == "salvar") {
        header('Content-type: text/json');
        try {

            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL || getIdUsuarioLogado() != $_POST['idUsuario']) {
                throw new NegocioException("Você precisa estar logado para acessar esta página");
            }

            if (DadoCurriculo::salvarLinkLattesPorUsu($_POST['idUsuario'], $_POST['linkLattes'])) {
                print json_encode(array("situacao" => TRUE));
            }
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }
} elseif (isset($_GET['atualizacao'])) {
    if ($_GET['atualizacao'] == "dadosAddProcesso") {
        header('Content-type: text/json');
        try {

            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                throw new NegocioException("Você precisa estar logado para acessar esta página");
            }

            // recuperando dados post
            $idProcesso = $_POST['idProcesso'];
            $dtInicio = $_POST['dtInicio'];
            $dsEdital = $_POST['dsEdital'];


            $temArquivo = !empty($_FILES['arqEdital']) && $_FILES['arqEdital']['error'] == 0;
            if ($temArquivo) {
                // verificando upload com sucesso
                NGUtil::arq_verificaSucessoUpload('arqEdital');
            }

            // recuperando processo
            $processo = buscarProcessoComPermissaoCT($idProcesso);

            // setando dados para edicao
            $processo->setPRC_DT_INICIO($dtInicio);
            $processo->setPRC_DS_PROCESSO($dsEdital);

            // chamando funcao de edicao
            $processo->editarDadosAddProcesso($temArquivo ? $_FILES['arqEdital']['tmp_name'] : NULL);

            // informando sucesso
            print json_encode(array("situacao" => "true"));
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }

    if ($_GET['atualizacao'] == "criarEtapaAval") {
        include_once "$CFG->rpasta/negocio/EtapaAvalProc.php";
        include_once "$CFG->rpasta/controle/CTManutencaoProcesso.php";
        header('Content-type: text/json');
        try {

            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                throw new NegocioException("Você precisa estar logado para acessar esta página");
            }

            // recuperando dados post
            $idProcesso = $_POST['idProcesso'];

            // recuperando processo
            $processo = buscarProcessoComPermissaoCT($idProcesso);

            // chamando funcao para criar etapa
            EtapaAvalProc::criarEtapaAval($processo);

            // recuperando etapa
            $etapa = EtapaAvalProc::buscarUltEtapaAvalPorProc($processo->getPRC_ID_PROCESSO());

            // informando sucesso
            print json_encode(array("situacao" => "true", "nrEtapa" => $etapa->getEAP_NR_ETAPA_AVAL(), "htmlEtapa" => getHtmlEtapaAval($etapa)));
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }

    if ($_GET['atualizacao'] == "atualizarOrdemElemProc") {
        include_once "$CFG->rpasta/negocio/CategoriaAvalProc.php";
        include_once "$CFG->rpasta/controle/CTManutencaoProcesso.php";
        include_once "$CFG->rpasta/util/filtro/FiltroInfCompProc.php";
        header('Content-type: text/json');
        try {

            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                throw new NegocioException("Você precisa estar logado para acessar esta página");
            }

            // recuperando dados post
            $idProcesso = $_POST['idProcesso'];
            $idEtapaAval = $_POST['idEtapaAval'];
            $novaOrdenacao = $_POST['novaOrdenacao'];
            $tipo = $_POST['tipo'];

            // recuperando processo
            $processo = buscarProcessoComPermissaoCT($idProcesso);

            if ($tipo == CategoriaAvalProc::$COD_TP_ORDENACAO) {
                // Chamando funçao para realizar ordenacao
                CategoriaAvalProc::editarOrdensCategoriasAval($processo->getPRC_ID_PROCESSO(), $idEtapaAval, $novaOrdenacao);

                // recuperando nova tabela
                $etapa = buscarEtapaAvalPorIdCT($idEtapaAval);
                $htmlTabela = tabelaCategoriaPorProcEtapa($etapa);
            } elseif ($tipo == GrupoAnexoProc::$COD_TP_ORDENACAO) {
                // Chamando funçao para realizar ordenacao
                GrupoAnexoProc::editarOrdensGrupoAnexoProc($processo->getPRC_ID_PROCESSO(), $idEtapaAval, $novaOrdenacao);

                // recuperando nova tabela
                $htmlTabela = tabelaInfCompProcPorFiltro(new FiltroInfCompProc(array(), "$CFG->rwww/visao/processo/manterProcessoAdmin.php", $processo->getPRC_ID_PROCESSO(), Util::$ABA_MPA_INF_COMP, "", FALSE));
            } elseif ($tipo == MacroConfProc::$COD_TP_ORDENACAO_MACRO_ELI || $tipo == MacroConfProc::$COD_TP_ORDENACAO_MACRO_CLAS || $tipo == MacroConfProc::$COD_TP_ORDENACAO_MACRO_DES || $tipo == MacroConfProc::$COD_TP_ORDENACAO_MACRO_SEL || $tipo == MacroConfProc::$COD_TP_ORDENACAO_MACRO_RES) {
                // Chamando funçao para realizar ordenacao
                MacroConfProc::editarOrdensMacroConfProc($processo->getPRC_ID_PROCESSO(), $idEtapaAval, $novaOrdenacao);

                if ($idEtapaAval != NULL && $idEtapaAval != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL) {
                    // recuperando nova tabela
                    $etapa = buscarEtapaAvalPorIdCT($idEtapaAval);
                    $htmlTabela = tabelaMacroConfProcPorProcEtapa($etapa, MacroConfProc::getMapaTipoOrdemTipoMacro($tipo));
                } else {
                    $htmlTabela = tabelaMacroConfProcPorProcEtapa(NULL, MacroConfProc::getMapaTipoOrdemTipoMacro($tipo), $processo);
                }
            }

            // informando sucesso
            print json_encode(array("situacao" => "true", "htmlTabela" => $htmlTabela));
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }

    if ($_GET['atualizacao'] == "ativarChamada") {
        include_once "$CFG->rpasta/controle/CTManutencaoProcesso.php";
        header('Content-type: text/json');
        try {

            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                throw new NegocioException("Você precisa estar logado para acessar esta página!");
            }

            // recuperando dados post
            $idProcesso = $_POST['idProcesso'];
            $idChamada = $_POST['idChamada'];

            // recuperando processo e chamada
            $processo = buscarProcessoComPermissaoCT($idProcesso);
            $chamada = buscarChamadaPorIdCT($idChamada, $idProcesso);

            // chamando função responsável por ativação
            $resul = $chamada->ativarChamada($processo);

            // informando sucesso
            print json_encode(array("situacao" => $resul[0], "msg" => $resul[1]));
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }

    if ($_GET['atualizacao'] == "solicitarAtivacaoCham") {
        include_once "$CFG->rpasta/controle/CTManutencaoProcesso.php";
        header('Content-type: text/json');
        try {

            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                throw new NegocioException("Você precisa estar logado para acessar esta página!");
            }

            // recuperando dados post
            $idProcesso = $_POST['idProcesso'];
            $idChamada = $_POST['idChamada'];

            // recuperando processo e chamada
            $processo = buscarProcessoComPermissaoCT($idProcesso);
            $chamada = buscarChamadaPorIdCT($idChamada, $idProcesso);

            // chamando função responsável pela solicitação de ativação
            $resul = $chamada->solicitarAtivacao($processo);

            // informando sucesso
            print json_encode(array("situacao" => $resul[0], "msg" => $resul[1]));
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }

    if ($_GET['atualizacao'] == "solicitarPublicacaoResul") {
        include_once "$CFG->rpasta/controle/CTManutencaoProcesso.php";
        header('Content-type: text/json');
        try {

            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                throw new NegocioException("Você precisa estar logado para acessar esta página!");
            }

            // recuperando dados post
            $idProcesso = $_POST['idProcesso'];
            $idChamada = $_POST['idChamada'];
            $idEtapaSel = $_POST['idEtapaSel'];

            // recuperando processo e etapa
            $processo = buscarProcessoComPermissaoCT($idProcesso);
            $etapaVigente = buscarEtapaVigenteCT($idChamada, $idEtapaSel);

            // chamando função responsável pela solicitação de publicação
            $resul = $etapaVigente->solicitarPublicacao($processo);

            // informando sucesso
            print json_encode(array("situacao" => $resul[0], "msg" => $resul[1]));
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }

    if ($_GET['atualizacao'] == "removerRastreio") {
        include_once "$CFG->rpasta/controle/CTRastreio.php";
        header('Content-type: text/json');
        try {

            // Este caso é diferente: Os erros são registrados em log e nenhum dado é retornado
            // 
            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                error_log("Tentativa de acesso à exclusão de rastreio sem login.");
                return;
            }

            if (isset($_POST['idRastreio'])) {
                RAT_removerRastreioCT($_POST['idRastreio']);
            }
        } catch (Exception $e) {
            error_log("Erro ao tentar excluir rastreio.");
            return;
        }
    }
} elseif (isset($_GET['obterHTML'])) {
    if ($_GET['obterHTML'] == "paramMacroConfProc") {
        header('Content-type: text/json');
        try {
            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                throw new NegocioException("Você precisa estar logado para acessar esta página");
            }

            // definindo parâmetros externos
            $vetParamExt = MacroAbs::montaVetParamExt(isset($_POST['idProcesso']) ? $_POST['idProcesso'] : NULL, isset($_POST['idEtapaAval']) ? $_POST['idEtapaAval'] : NULL);

//            print_r($vetParamExt);

            $dados = htmlParametrosMacro($_POST['tipoMacro'], $_POST['idTipoMacro'], isset($_POST['edicao']) ? $_POST['edicao'] : FALSE, isset($_POST['idMacroConfProc']) ? $_POST['idMacroConfProc'] : NULL, $vetParamExt);

            // informando sucesso
            print json_encode(array("situacao" => "true", "html" => $dados[0], "script" => $dados[1]));
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }

    if ($_GET['obterHTML'] == "formulaFinalProc") {
        header('Content-type: text/json');
        try {
            // verificando caso de login
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
                throw new NegocioException("Você precisa estar logado para acessar esta página");
            }

            // chamando função de processamento
            $formula = MacroConfProc::getFormulaFinalRapida($_POST['idProcesso'], $_POST['idFormulaRapida']);

            // informando sucesso
            print json_encode(array("situacao" => "true", "formula" => $formula));
        } catch (Exception $e) {
            print json_encode(array("situacao" => FALSE, 'msg' => $e->getMessage()));
        }
    }
} else {
    return "false";
}

function validaAreaSubAreaObrigatoria() {
    if (!isset($_POST['idAreaConh']) || !isset($_POST['idSubareaConh']) || Util::vazioNulo($_POST['idAreaConh']) || Util::vazioNulo($_POST['idSubareaConh'])) {
        new Mensagem("Dados para verificação inconsistentes!", Mensagem::$MENSAGEM_ERRO);
    }
}

?>
