<?php

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/util/Mensagem.php";
require_once $CFG->rpasta . "/negocio/Nacionalidade.php";
require_once $CFG->rpasta . "/negocio/Ocupacao.php";
require_once $CFG->rpasta . "/negocio/TipoCurso.php";
require_once $CFG->rpasta . "/negocio/Endereco.php";
require_once $CFG->rpasta . "/negocio/FormacaoAcademica.php";
require_once $CFG->rpasta . "/negocio/AreaConhecimento.php";
require_once $CFG->rpasta . "/negocio/Estado.php";
require_once $CFG->rpasta . "/negocio/Cidade.php";
require_once $CFG->rpasta . "/negocio/ReservaVaga.php";

//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctcandidato") {

    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];

        //caso criar candidato
        if ($acao == "criarCandidato") {
            if (estaLogado() != NULL) {
                //redirecionando para página principal
                header("Location:$CFG->rwww/inicio");
                return;
            }
            try {
                //criando objetos para manipulaçao
                // Usuario
                $objUsuario = new Usuario(NULL, Usuario::$USUARIO_CANDIDATO, $_POST['dsEmail'], $_POST['dsEmail'], $_POST['dsSenha'], $_POST['dsNome'], Usuario::$VINCULO_NENHUM, NGUtil::getSITUACAO_ATIVO());

                // identificaçao usuario
                // 
                //preparando dados para formato banco
                $nrCPF = removerMascara("###.###.###-##", $_POST['nrCPF']);

                $objIdentCandidato = new IdentificacaoCandidato(NULL, $nrCPF, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $_POST['dtNascimento']);

                //criando
                $objUsuario->criarUsuario(NULL, $objIdentCandidato);

                //redirecionando
                new Mensagem("Usuário cadastrado com sucesso.<br/>Para acessar o sistema, navegue até a página inicial (link abaixo) e insira o email e a senha cadastrados.", Mensagem::$MENSAGEM_INFORMACAO);
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }


        //caso editar identificacao
        if ($acao == "editarIdentificacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado como candidato para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto identificaçao com os parametros
                $objIdent = new IdentificacaoCandidato(NULL, removerMascara("###.###.###-##", $_POST['nrCpf']), $_POST['idNacionalidade'], $_POST['dsNacionalidade'], $_POST['dsSexo'], $_POST['tpRaca'], $_POST['idOcupacao'], $_POST['dsOcupacao'], isset($_POST['tpVinculoPublico']), $_POST['nasIdPais'], (isset($_POST['nasIdEstado']) ? $_POST['nasIdEstado'] : NULL), (isset($_POST['nasIdCidade']) ? $_POST['nasIdCidade'] : NULL), $_POST['nasData'], $_POST['rgNr'], $_POST['rgOrgaoExp'], $_POST['rgUf'], $_POST['rgDtEmissao'], isset($_POST['nrSIAPE']) ? $_POST['nrSIAPE'] : NULL, isset($_POST['dsLotacao']) ? $_POST['dsLotacao'] : NULL, isset($_POST['dsSetor']) ? $_POST['dsSetor'] : NULL, $_POST['pspNr'], $_POST['pspDtEmissao'], $_POST['pspDtValidade'], $_POST['pspPaisOrigem'], $_POST['tpEstadoCivil'], $_POST['nmConjuge'], $_POST['filNmPai'], $_POST['filNmMae']);

                //atualizando
                $dadosLogin = getDadosLogin();
                $objIdent->editarIdentificacao(getIdUsuarioLogado(), $dadosLogin['dsNome'], $_POST['dsNome']);

                //redirecionando
                redirecionamentoPreenchimentoPerfil(getIdUsuarioLogado(), Candidato::$PREENC_IDENTIFICACAO, "Identificação atualizada com sucesso.", "sucIdentificacao");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso editar endereço
        if ($acao == "editarEndereco") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado como candidato para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto endereço residencial
                $endRes = new Endereco(NULL, NULL, $_POST['nmLogradouroRes'], $_POST['nrNumeroRes'], $_POST['nmBairroRes'], NULL, isset($_POST['idCidadeRes']) ? $_POST['idCidadeRes'] : NULL, $_POST['idEstadoRes'], removerMascara("##.###-###", $_POST['nrCepRes']), $_POST['dsComplementoRes']);

                // criando objeto endereço comercial
                $endCom = new Endereco(NULL, NULL, $_POST['nmLogradouroCom'], $_POST['nrNumeroCom'], $_POST['nmBairroCom'], NULL, isset($_POST['idCidadeCom']) ? $_POST['idCidadeCom'] : NULL, $_POST['idEstadoCom'], removerMascara("##.###-###", $_POST['nrCepCom']), $_POST['dsComplementoCom']);

                // atualizando
                Endereco::salvarEnderecoCandidato(getIdUsuarioLogado(), $endRes, $endCom);

                //redirecionando
                redirecionamentoPreenchimentoPerfil(getIdUsuarioLogado(), Candidato::$PREENC_ENDERECO, "Endereço atualizado com sucesso.", "sucEndereco");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso editar contato
        if ($acao == "editarContato") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado como candidato para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                // criando objeto de contato              
                $objCont = new ContatoCandidato(NULL, removerMascara("(##)####-####", $_POST['nrTelResidencial']), removerMascara("(##)####-####", $_POST['nrTelComercial']), strlen($_POST['nrTelCelular']) > 14 ? removerMascara("(##)#####-####", $_POST['nrTelCelular']) : removerMascara("(##)####-####", $_POST['nrTelCelular']), removerMascara("(##)####-####", $_POST['nrTelFax']), $_POST['dsEmailAlternativo']);

                // realizando atualizaçao
                $objCont->editarContatoCandidato(getIdUsuarioLogado());

                //redirecionando
                redirecionamentoPreenchimentoPerfil(getIdUsuarioLogado(), Candidato::$PREENC_CONTATO, "Contato atualizado com sucesso.", "sucContato");
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

function validarCadastroCPFCTAjax($nrCPF, $idUsuario = NULL) {
    try {
        return Candidato::validarCadastroCPF($nrCPF, $idUsuario);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

/**
 *  Faz o redirecionamento automático do preenchimento de perfil do candidato
 * 
 *  @global type $CFG
 * 
 * @param int $idUsuario
 * @param char $pontoPartida IdPreenchimento de perfil atual (da classe Candidato)
 * @param type $dsMensagem Descrição da mensagem de redirecionamento
 * @param type $dsToast Toast a ser ativado no direcionamento padrão (caso não tenha necessidade de executar o tutorial)
 */
function redirecionamentoPreenchimentoPerfil($idUsuario, $pontoPartida, $dsMensagem, $dsToast) {
    global $CFG;

    $toast = "";
    $pagina = "$CFG->rwww/inicio";

    // verificando se é caso de percorrer perfil
    $percorrerPerfil = isset($_POST[Candidato::$PARAM_PREENC_REVISAO]) ? $_POST[Candidato::$PARAM_PREENC_REVISAO] : NULL;

    // inscrição
    if ($pontoPartida != Candidato::$PREENC_IDENTIFICACAO && !preencheuIdentificacaoCT($idUsuario)) {
        $pagina = "$CFG->rwww/visao/candidato/editarIdentificacao.php";
    } elseif ($pontoPartida != Candidato::$PREENC_ENDERECO && (!preencheuEnderecoCT($idUsuario) || $percorrerPerfil == Candidato::$PREENC_ENDERECO)) {
        $pagina = "$CFG->rwww/visao/candidato/editarEndereco.php";
    } elseif ($pontoPartida != Candidato::$PREENC_CONTATO && (!preencheuContatoCT($idUsuario) || $percorrerPerfil == Candidato::$PREENC_CONTATO)) {
        $pagina = "$CFG->rwww/visao/candidato/editarContato.php";
    } elseif ($pontoPartida != Candidato::$PREENC_CURRICULO && (!preencheuFormacaoCT($idUsuario) || $percorrerPerfil == Candidato::$PREENC_CURRICULO)) {
        $pagina = "$CFG->rwww/visao/formacao/listarFormacao.php";
    } else {
        // página padrão. Não há necessidade de tutorial
        $toast = $dsToast;
    }

    // propagando o percorrer perfil
    $pagina .= $percorrerPerfil != NULL ? "?" . Candidato::$PARAM_PREENC_REVISAO : "";

    new Mensagem($dsMensagem, Mensagem::$MENSAGEM_INFORMACAO, NULL, $toast, $pagina);
}

function preencheuIdentificacaoCT($idUsuario) {
    try {
        return IdentificacaoCandidato::preencheuIdentificacao($idUsuario);
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

function preencheuEnderecoCT($idUsuario) {
    try {
        return Endereco::preencheuEndereco($idUsuario);
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

function preencheuContatoCT($idUsuario) {
    try {
        return ContatoCandidato::preencheuContato($idUsuario);
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

function preencheuFormacaoCT($idUsuario) {
    try {
        return FormacaoAcademica::preencheuFormacao($idUsuario);
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
 * @param int $idUsuario
 * @return IdentificacaoCandidato
 */
function buscarIdentCandPorIdUsuCT($idUsuario) {
    try {
        return IdentificacaoCandidato::buscarIdentCandPorIdUsu($idUsuario);
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
 * @param int $idCandidato
 * @return IdentificacaoCandidato
 */
function buscarIdentCandPorIdCandCT($idCandidato) {
    try {
        return IdentificacaoCandidato::buscarIdentCandPorIdCand($idCandidato);
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
 * @param int $idCandidato
 * @return Candidato
 */
function buscarCandidatoPorIdCT($idCandidato) {
    try {
        return Candidato::buscarCandidatoPorId($idCandidato);
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

function buscarCandidatoPorIdUsuCT($idUsuario) {
    try {
        return Candidato::buscarCandidatoPorIdUsu($idUsuario);
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

function buscarIdCandPorIdUsuCT($idUsuario) {
    try {
        return Candidato::getIdCandidatoPorIdUsuario($idUsuario);
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

function buscarTodasNacionalidadesCT() {
    try {
        return Nacionalidade::buscarTodasNacionalidades();
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

function buscarTodasAreaConhCT() {
    try {
        return AreaConhecimento::buscarTodasAreas();
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

function buscarAreasConhPorIdsCT($idAreas) {
    try {
        return AreaConhecimento::buscarAreasPorIds($idAreas);
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

function buscarTodasAreaConhFilhasCT() {
    try {
        return AreaConhecimento::buscarTodasAreasFilhas();
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

function buscarTodasReservaVagasCT() {
    try {
        return ReservaVaga::buscarTodasReservaVagasAtivas();
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

function buscarReservasVagasPorIdsCT($idReservasVagas) {
    try {
        return ReservaVaga::buscarReservasVagasPorIds($idReservasVagas);
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

function buscarSubAreaConhPorAreaCT($idArea) {
    try {
        return AreaConhecimento::buscarSubAreaPorArea($idArea);
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

function buscarTodasOcupacoesCT() {
    try {
        return Ocupacao::buscarTodasOcupacoes();
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

function buscarEnderecoCandPorIdUsuarioCT($idUsuario, $tipo) {
    try {
        return Endereco::buscarEnderecoPorIdUsuario($idUsuario, $tipo);
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

function buscarEnderecoCandPorIdCandCT($idCandidato, $tipo) {
    try {
        return Endereco::buscarEnderecoPorIdCand($idCandidato, $tipo);
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

function buscarContatoCandPorIdUsuarioCT($idUsuario) {
    try {
        return ContatoCandidato::buscarContatoPorIdUsuario($idUsuario);
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

function buscarContatoCandPorIdCandCT($idCandidato) {
    try {
        return ContatoCandidato::buscarContatoPorIdCand($idCandidato);
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

function buscarNacionalidadePorIdCT($idNacionalidade) {
    try {
        return Nacionalidade::buscarNacionalidadePorId($idNacionalidade);
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

function buscarAreaConhPorIdCT($idAreaConh) {
    try {
        return AreaConhecimento::buscarAreaConhPorId($idAreaConh);
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

function buscarOcupacaoPorIdCT($idOcupacao) {
    try {
        return Ocupacao::buscarOcupacaoPorId($idOcupacao);
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

function buscarPaisPorIdCT($paiIso) {
    try {
        return Pais::buscarPaisPorId($paiIso);
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

function buscarEstadoPorIdCT($idUf) {
    try {
        return Estado::buscarEstadoPorId($idUf);
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

function buscarCidadePorIdCT($idCidade) {
    try {
        return Cidade::buscarCidadePorId($idCidade);
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
 * @param FiltroCandidato $filtro
 * @return int
 */
function contaCandidatosPorFiltroCT($filtro) {
    try {
        return Usuario::contaUsuariosPorFiltro($filtro->getDsNome(), $filtro->getDsEmail(), $filtro->getTpUsuario(), $filtro->getNrcpf(), $filtro->getStSituacao());
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
 * @param FiltroCandidato $filtro
 * @return string
 */
function tabelaCandidatosPorFiltro($filtro) {
    global $CFG;

    //recuperando 
    $usuarios = buscarUsuariosPorFiltroCT($filtro->getDsNome(), $filtro->getDsEmail(), $filtro->getTpUsuario(), $filtro->getNrcpf(), $filtro->getStSituacao(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());

    if (count($usuarios) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
    <thead><tr>
        <th>Nome</th>
        <th class='campoDesktop'>Email</th>
        <th>CPF</th>
        <th class='botao'><span class='fa fa-eye'></span></th>
    </tr></thead>";

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($usuarios); $i++) {
        $temp = $usuarios[$i - 1];

        $linkConsultar = "<a id='linkConsultar' title='Consultar candidato' href='$CFG->rwww/visao/candidato/consultarCandidato.php?idUsuario={$temp->getUSR_ID_USUARIO()}' class='itemTabela'><span class='fa fa-eye'></span></a>";

        $ret .= "<tr>
                    <td>{$temp->getUSR_DS_NOME()}</td>
                    <td class='campoDesktop'>{$temp->getUSR_DS_EMAIL()}</td>
                    <td>{$temp->getNrCpfMascarado()}</td>
                    <td class='botao'>$linkConsultar</td>
                </tr>";
    }

    $ret .= "</table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
                Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    return $ret;
}

?>
