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
require_once $CFG->rpasta . "/negocio/Publicacao.php";
require_once $CFG->rpasta . "/negocio/Atuacao.php";
require_once $CFG->rpasta . "/negocio/DadoCurriculo.php";
require_once $CFG->rpasta . "/negocio/ParticipacaoEvento.php";
require_once $CFG->rpasta . "/negocio/Estado.php";
require_once $CFG->rpasta . "/negocio/Cidade.php";

//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctcurriculo") {

    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];

        //caso de criar formação acadêmica
        if ($acao == "criarFormacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto de formação
                $formacao = new FormacaoAcademica(NULL, NULL, $_POST['tpFormacao'], $_POST['nmInstituicao'], $_POST['nmCurso'], $_POST['idAreaConh'], isset($_POST['idSubareaConh']) ? $_POST['idSubareaConh'] : NULL, $_POST['stFormacao'], $_POST['anoInicio'], $_POST['anoConclusao'], $_POST['cargaHoraria'], $_POST['nmTituloTrabalho'], $_POST['nmOrientadorTrabalho'], $_POST['idPais'], $_POST['idEstado'], isset($_POST['idCidade']) ? $_POST['idCidade'] : NULL, $_POST['nmCidade']);

                //criando
                $formacao->criarFormacao(getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Nova Formação cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/formacao/listarFormacao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de editar formação acadêmica
        if ($acao == "editarFormacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto de formação
                $formacao = new FormacaoAcademica($_POST['idFormacao'], NULL, $_POST['tpFormacao'], $_POST['nmInstituicao'], $_POST['nmCurso'], $_POST['idAreaConh'], (isset($_POST['idSubareaConh']) ? $_POST['idSubareaConh'] : NULL), $_POST['stFormacao'], $_POST['anoInicio'], $_POST['anoConclusao'], $_POST['cargaHoraria'], $_POST['nmTituloTrabalho'], $_POST['nmOrientadorTrabalho'], $_POST['idPais'], $_POST['idEstado'], isset($_POST['idCidade']) ? $_POST['idCidade'] : NULL, $_POST['nmCidade']);

                // editando
                $formacao->editarFormacao(getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Formação atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtualizacao", "$CFG->rwww/visao/formacao/listarFormacao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de excluir formação acadêmica
        if ($acao == "excluirFormacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            try {

                // recuperando parametro
                $idFormacao = $_POST['idFormacao'];

                // editando
                FormacaoAcademica::excluirFormacao($idFormacao, getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Formação excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/formacao/listarFormacao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de criar publicaçao
        if ($acao == "criarPublicacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto de publicacao
                $publicacao = new Publicacao(NULL, NULL, $_POST['tpPublicacao'], $_POST['idAreaConh'], isset($_POST['idSubareaConh']) ? $_POST['idSubareaConh'] : NULL, $_POST['qtde']);

                //criando
                $publicacao->criarPublicacao(getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Nova Publicação cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/publicacao/listarPublicacao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de editar publicacao
        if ($acao == "editarPublicacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto de publicacao
                $publicacao = new Publicacao($_POST['idPublicacao'], NULL, NULL, NULL, NULL, $_POST['qtde']);

                // editando
                $publicacao->editarPublicacao(getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Publicação atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtualizacao", "$CFG->rwww/visao/publicacao/listarPublicacao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de excluir publicacao
        if ($acao == "excluirPublicacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            try {

                // recuperando parametro
                $idPublicacao = $_POST['idPublicacao'];

                // processando
                Publicacao::excluirPublicacao($idPublicacao, getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Publicação excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/publicacao/listarPublicacao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de criar participacao em evento
        if ($acao == "criarPartEvento") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto de participacao
                $partEvento = new ParticipacaoEvento(NULL, NULL, $_POST['tpPartEvento'], isset($_POST['idAreaConh']) ? $_POST['idAreaConh'] : NULL, isset($_POST['idSubareaConh']) ? $_POST['idSubareaConh'] : NULL, $_POST['qtde']);

                //criando
                $partEvento->criarPartEvento(getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Nova Participação cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/participacaoEvento/listarPartEvento.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de editar participacao em evento
        if ($acao == "editarPartEvento") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto de participacao em evento
                $partEvento = new ParticipacaoEvento($_POST['idPartEvento'], NULL, NULL, NULL, NULL, $_POST['qtde']);

                // editando
                $partEvento->editarPartEvento(getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Participação atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtualizacao", "$CFG->rwww/visao/participacaoEvento/listarPartEvento.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de excluir participacao
        if ($acao == "excluirPartEvento") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            try {
                // recuperando parametro
                $idPartEvento = $_POST['idPartEvento'];

                // processando
                ParticipacaoEvento::excluirPartEvento($idPartEvento, getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Participação excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/participacaoEvento/listarPartEvento.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de criar atuacao
        if ($acao == "criarAtuacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto de atuacao
                $atuacao = new Atuacao(NULL, NULL, $_POST['tpAtuacao'], $_POST['idAreaConh'], isset($_POST['idSubareaConh']) ? $_POST['idSubareaConh'] : NULL, $_POST['qtde']);

                //criando
                $atuacao->criarAtuacao(getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Nova Atuação cadastrada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/atuacao/listarAtuacao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de editar atuacao
        if ($acao == "editarAtuacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //criando objeto de atuacao
                $atuacao = new Atuacao($_POST['idAtuacao'], NULL, NULL, NULL, NULL, $_POST['qtde']);

                // editando
                $atuacao->editarAtuacao(getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Atuação atualizada com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucAtualizacao", "$CFG->rwww/visao/atuacao/listarAtuacao.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de excluir atuacao
        if ($acao == "excluirAtuacao") {
            if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
                new Mensagem("Você precisa estar logado para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }

            try {

                // recuperando parametro
                $idAtuacao = $_POST['idAtuacao'];

                // processando
                Atuacao::excluirAtuacao($idAtuacao, getIdUsuarioLogado());

                //redirecionando
                new Mensagem('Atuação excluída com sucesso.', Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/atuacao/listarAtuacao.php");
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

function permiteAlteracaoCurriculoCT($idCandidato) {
    try {
        return Candidato::permiteAlteracaoCurriculo($idCandidato);
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

function buscarFormacaoPorIdUsuarioCT($idUsuario, $inicioDados, $qtdeDados) {
    try {
        return FormacaoAcademica::buscarFormacaoPorIdUsuario($idUsuario, $inicioDados, $qtdeDados);
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

function buscarPublicacaoPorIdUsuarioCT($idUsuario, $inicioDados, $qtdeDados) {
    try {
        return Publicacao::buscarPublicacaoPorIdUsuario($idUsuario, $inicioDados, $qtdeDados);
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

function buscarPublicacaoPorIdCandCT($idCandidato, $inicioDados, $qtdeDados) {
    try {
        return Publicacao::buscarPublicacaoPorIdCand($idCandidato, $inicioDados, $qtdeDados);
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

function buscarAtuacaoPorIdUsuarioCT($idUsuario, $inicioDados, $qtdeDados) {
    try {
        return Atuacao::buscarAtuacaoPorIdUsuario($idUsuario, $inicioDados, $qtdeDados);
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

function buscarAtuacaoPorIdCandCT($idCandidato, $inicioDados, $qtdeDados) {
    try {
        return Atuacao::buscarAtuacaoPorIdCand($idCandidato, $inicioDados, $qtdeDados);
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

function buscarDadoCurriculoPorIdUsuCT($idUsuario) {
    try {
        return DadoCurriculo::buscarDadoCurriculoPorUsu($idUsuario);
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

function buscarLinkLattesPorIdUsuCT($idUsuario) {
    try {
        return DadoCurriculo::buscarLinkLattesPorUsu($idUsuario);
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

function buscarLinkLattesPorIdCandCT($idCandidato, $retornoHTML = TRUE) {
    try {
        return DadoCurriculo::buscarLinkLattesPorCand($idCandidato, $retornoHTML);
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

function salvarLinkLattesPorIdUsuCT($idUsuario, $linkLattes) {
    try {
        return DadoCurriculo::salvarLinkLattesPorUsu($idUsuario, $linkLattes);
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

function buscarPartEventoPorIdUsuarioCT($idUsuario, $inicioDados, $qtdeDados) {
    try {
        return ParticipacaoEvento::buscarPartEventoPorIdUsuario($idUsuario, $inicioDados, $qtdeDados);
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

function buscarPartEventoPorIdCandCT($idCandidato, $inicioDados, $qtdeDados) {
    try {
        return ParticipacaoEvento::buscarPartEventoPorIdCand($idCandidato, $inicioDados, $qtdeDados);
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

function buscarFormacaoPorIdCandCT($idCandidato, $inicioDados, $qtdeDados) {
    try {
        return FormacaoAcademica::buscarFormacaoPorIdCand($idCandidato, $inicioDados, $qtdeDados);
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
 * @param FiltroFormacao $filtro
 * @return type
 */
function contarFormacaoCandPorFiltroCT($filtro) {
    try {
        return FormacaoAcademica::contarFormacaoPorIdUsuario($filtro->getIdUsuario());
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
 * @param FiltroPublicacao $filtro
 * @return type
 */
function contarPublicacaoCandPorFiltroCT($filtro) {
    try {
        return Publicacao::contarPublicacaoPorIdUsuario($filtro->getIdUsuario());
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
 * @param FiltroAtuacao $filtro
 * @return type
 */
function contarAtuacaoCandPorFiltroCT($filtro) {
    try {
        return Atuacao::contarAtuacaoPorIdUsuario($filtro->getIdUsuario());
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
 * @param FiltroPartEvento $filtro
 * @return type
 */
function contarPartEventoCandPorFiltroCT($filtro) {
    try {
        return ParticipacaoEvento::contarPartEventoPorIdUsuario($filtro->getIdUsuario());
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

function buscarFormacaoPorIdFormacaoCT($idFormacao, $idUsuario) {
    try {
        return FormacaoAcademica::buscarFormacaoPorIdFormacao($idFormacao, $idUsuario);
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

function buscarPublicacaoPorIdCT($idPublicacao, $idUsuario) {
    try {
        return Publicacao::buscarPublicacaoPorId($idPublicacao, $idUsuario);
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

function buscarPartEventoPorIdCT($idPartEvento, $idUsuario) {
    try {
        return ParticipacaoEvento::buscarPartEventoPorId($idPartEvento, $idUsuario);
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

function buscarAtuacaoPorIdCT($idAtuacao, $idUsuario) {
    try {
        return Atuacao::buscarAtuacaoPorId($idAtuacao, $idUsuario);
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

function validaCriarFormacaoCTAjax($tpFormacao, $nmInstituicao, $nmCurso, $anoInicio, $idFormacao, $idUsuario) {
    try {
        return FormacaoAcademica::validarInsercaoFormacao($tpFormacao, $nmInstituicao, $nmCurso, $anoInicio, $idFormacao, $idUsuario);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

function validaCriarPublicacaoCTAjax($tpPublicacao, $idAreaConh, $idSubareaConh, $idUsuario) {
    try {
        return Publicacao::validarInsercaoPublicacao($tpPublicacao, $idAreaConh, $idSubareaConh, $idUsuario);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

function validaCriarPartEventoCTAjax($tpPartEvento, $idAreaConh, $idSubareaConh, $idUsuario) {
    try {
        return ParticipacaoEvento::validarInsercaoPartEvento($tpPartEvento, $idAreaConh, $idSubareaConh, $idUsuario);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

function validaCriarAtuacaoCTAjax($tpAtuacao, $idAreaConh, $idSubareaConh, $idUsuario) {
    try {
        return Atuacao::validarInsercaoAtuacao($tpAtuacao, $idAreaConh, $idSubareaConh, $idUsuario);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

/**
 * Funçao que imprime a tabela de formaçao academica de um determinado usuario
 * 
 * @global stdClass $CFG
 * @param FiltroFormacao $filtro
 * @return void
 */
function tabelaFormacaoCandPorFiltroCT($filtro) {
    global $CFG;

    //recuperando formacao
    $formacoes = buscarFormacaoPorIdUsuarioCT($filtro->getIdUsuario(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());

    if ($formacoes == NULL) {
        echo "<div class='callout callout-warning'>Você ainda não cadastrou formação / titulação.</div>";
        return;
    }

    // verificando se e possivel alterar curiculo
    $permiteAltCurriculo = permiteAlteracaoCurriculoCT($formacoes[0]->getCDT_ID_CANDIDATO());
    $msgCurriculo = getMsgErroEdicaoCurriculo();

    $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
    <thead>
    <tr>
        <th>Período</th>
        <th>Formação</th>
        <th>Curso</th>
        <th class='botao'><span class='fa fa-eye'></span></th>
        <th class='botao'><i class='fa fa-edit'></i></th>
        <th class='botao'><span class='fa fa-trash-o'></span></th>";

    $ret .="</tr></thead>";

    //iteração para exibir formações
    for ($i = 1; $i <= sizeof($formacoes); $i++) {
        $temp = $formacoes[$i - 1];
        $idFormacao = $temp->getFRA_ID_FORMACAO();

        $periodo = $temp->getDsPeriodo();
        $nmCurso = $temp->getFRA_NM_CURSO(TRUE);

        $linkEditar = "$CFG->rwww/visao/formacao/editarFormacao.php?idFormacao=$idFormacao";
        $linkConsultar = "$CFG->rwww/visao/formacao/editarFormacao.php?idFormacao=$idFormacao&fn=consultar";

        if ($permiteAltCurriculo) {
            $linkExcluir = "<a id='linkExcluir' title='Excluir Formação' href='$CFG->rwww/visao/formacao/excluirFormacao.php?idFormacao=$idFormacao' class='itemTabela'><span class='fa fa-trash-o'></span></a>";
        } else {
            $linkExcluir = "<a onclick='return false' id='linkExcluir' title='$msgCurriculo' class='itemTabela'><i class='fa fa-ban'></i></a>";
        }
        $permiteEdicao = $temp->permiteEdicao();

        if ($permiteAltCurriculo) {
            $ret .= "<tr>";
        } else {
            $ret .= "<tr title='$msgCurriculo'>";
        }

        $ret .= "
        <td>$periodo</td>
        <td>$temp->TPC_NM_TIPO_CURSO</td>
        <td>$nmCurso</td>
        <td><a title='Visualizar Formação' href='$linkConsultar'><span class='fa fa-eye'></span></a></td>
        <td class='botao'>";

        if ($permiteEdicao && $permiteAltCurriculo) {
            $ret .= "<a title='Editar Formação' href='$linkEditar'><i class='fa fa-edit'></i></a>";
        } else {
            $ret .= "<a onclick='return false' title='$msgCurriculo'><i class='fa fa-ban'></i></a>";
        }

        $ret .= " </td>
        <td class='botao'>
           $linkExcluir
        </td>";

        $ret .= "</tr>";
    }

    $ret .= "</table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
                Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    return $ret;
}

/**
 * Funçao que imprime a tabela de publicaçao de um determinado usuario
 * 
 * @global stdClass $CFG
 * @param FiltroPublicacao $filtro
 * @param boolean $mostrarLink - Diz se e para mostrar links da tabela
 * @param callbackfunction  $callBackBusca - Funcao de call back para busca. Padrao: NULL
 * Quando nao passada, e utilizada a funcao de busca padrao: buscarPublicacaoPorIdUsuarioCT
 * @return void
 */
function tabelaPublicacaoCandPorFiltroCT($filtro, $mostrarLink = TRUE, $callBackBusca = NULL) {
    global $CFG;

    //recuperando publicacao
    if (Util::vazioNulo($callBackBusca)) {
        $publicacoes = buscarPublicacaoPorIdUsuarioCT($filtro->getIdUsuario(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());
    } else {
        $publicacoes = $callBackBusca($filtro->getIdUsuario(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());
    }

    if ($publicacoes == NULL) {
        if ($mostrarLink) {
            echo "<div class='callout callout-warning'>Você ainda não cadastrou publicação.</div>";
        } else {
            echo "<div class='callout callout-info'>O candidato não cadastrou publicação.</div>";
        }
        return;
    }

    // verificando se e possivel alterar curiculo
    $permiteAltCurriculo = permiteAlteracaoCurriculoCT($publicacoes[0]->getCDT_ID_CANDIDATO());
    $msgCurriculo = getMsgErroEdicaoCurriculo();

    $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
    <thead>
    <tr>
        <th>Item</th>
        <th>Área</th>
        <th>N° de Publicações</th>
        ";
    if ($mostrarLink) {
        $ret .= "  <th class='botao'><i class='fa fa-edit'></i></th>
        <th class='botao'><span class='fa fa-trash-o'></span></th>";
    }

    $ret .="</tr></thead>";

    //iteração para exibir formações
    for ($i = 1; $i <= sizeof($publicacoes); $i++) {
        $temp = $publicacoes[$i - 1];
        $idPublicacao = $temp->getPUB_ID_PUBLICACAO();

        $dsTipo = Publicacao::getDsTipo($temp->getPUB_TP_ITEM());

        if ($mostrarLink) {
            if ($permiteAltCurriculo) {
                $linkEditar = "<a id='linkEditar' title='Editar Publicação' href='$CFG->rwww/visao/publicacao/criarPublicacao.php?idPublicacao=$idPublicacao'><i class='fa fa-edit'></i></a>";
                $linkExcluir = "<a id='linkExcluir' title='Excluir Publicação' href='$CFG->rwww/visao/publicacao/excluirPublicacao.php?idPublicacao=$idPublicacao'><span class='fa fa-trash-o'></span></a>";
            } else {
                $linkEditar = "<a onclick='return false' id='linkEditar' title='$msgCurriculo'><i class='fa fa-ban'></i></a>";
                $linkExcluir = "<a onclick='return false' id='linkExcluir' title='$msgCurriculo'><i class='fa fa-ban'></i></a>";
            }
        }

        if ($permiteAltCurriculo || !$mostrarLink) {
            $ret .= "<tr>";
        } else {
            $ret .= "<tr title='$msgCurriculo'>";
        }

        $ret .= "
        <td>$dsTipo</td>
        
        <td>{$temp->getDsAreaSubarea()}</td>
        <td>{$temp->getPUB_QT_ITEM()}</td>";

        if ($mostrarLink) {
            $ret .= "<td class='botao'>$linkEditar</td>
                
        <td class='botao'>$linkExcluir</td>";
        }

        $ret .= "</tr>";
    }

    $ret .= "</table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
                Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    return $ret;
}

/**
 * Funçao que imprime a tabela de participaçao em evento de um determinado usuario
 * 
 * @global stdClass $CFG
 * @param FiltroPartEvento $filtro
 * @param boolean $mostrarLink - Diz se e para mostrar links da tabela
 * @param callbackfunction  $callBackBusca - Funcao de call back para busca. Padrao: NULL
 * Quando nao passada, e utilizada a funcao de busca padrao: buscarPartEventoPorIdUsuarioCT
 * @return void
 */
function tabelaPartEventoCandPorFiltroCT($filtro, $mostrarLink = TRUE, $callBackBusca = NULL) {
    global $CFG;

    //recuperando publicacao
    if (Util::vazioNulo($callBackBusca)) {
        $partEvento = buscarPartEventoPorIdUsuarioCT($filtro->getIdUsuario(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());
    } else {
        $partEvento = $callBackBusca($filtro->getIdUsuario(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());
    }

    if ($partEvento == NULL) {
        if ($mostrarLink) {
            echo "<div class='callout callout-warning'>Você ainda não cadastrou participação em evento.</div>";
        } else {
            echo "<div class='callout callout-info'>O candidato não cadastrou participação em evento.</div>";
        }
        return;
    }

    // verificando se e possivel alterar curiculo
    $permiteAltCurriculo = permiteAlteracaoCurriculoCT($partEvento[0]->getCDT_ID_CANDIDATO());
    $msgCurriculo = getMsgErroEdicaoCurriculo();

    $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
    <thead>
    <tr>
        <th>Item</th>
        <th>Área</th>
        <th>Quantidade</th>";

    if ($mostrarLink) {
        $ret .= "<th class='botao'><i class='fa fa-edit'></i></th>
        <th class='botao'><span class='fa fa-trash-o'></span></td>";
    }

    $ret .="</tr></thead>";

    //iteração para exibir formações
    for ($i = 1; $i <= sizeof($partEvento); $i++) {
        $temp = $partEvento[$i - 1];
        $idPartEvento = $temp->getPEV_ID_PARTICIPACAO();

        $dsTipo = ParticipacaoEvento::getDsTipo($temp->getPEV_TP_ITEM());

        if ($mostrarLink) {
            if ($permiteAltCurriculo) {
                $linkEditar = "<a id='linkEditar' title='Editar Participação em Evento' href='$CFG->rwww/visao/participacaoEvento/criarPartEvento.php?idPartEvento=$idPartEvento'><i class='fa fa-edit'></i></a>";
                $linkExcluir = "<a id='linkExcluir' title='Excluir Participação em Evento' href='$CFG->rwww/visao/participacaoEvento/excluirPartEvento.php?idPartEvento=$idPartEvento'><span class='fa fa-trash-o'></span></a>";
            } else {
                $linkEditar = "<a onclick='return false' id='linkEditar' title='$msgCurriculo'><i class='fa fa-ban'></i></a>";
                $linkExcluir = "<a onclick='return false' id='linkExcluir' title='$msgCurriculo'><i class='fa fa-ban'></i></a>";
            }
        }

        if ($permiteAltCurriculo || !$mostrarLink) {
            $ret .= "<tr>";
        } else {
            $ret .= "<tr title='$msgCurriculo'>";
        }

        $ret .= "
        <td style='word-break: normal !important;'>$dsTipo</td>
            
        <td>{$temp->getDsAreaSubarea()}</td>
        <td>{$temp->getPEV_QT_ITEM()}</td>";

        if ($mostrarLink) {
            $ret .= "<td class='botao'>$linkEditar</td>
        <td class='botao'>$linkExcluir</td>";
        }

        $ret .= "</tr>";
    }

    $ret .= "</table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
                Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    return $ret;
}

/**
 * Funçao que imprime a tabela de atuacao de um determinado usuario
 * 
 * @global stdClass $CFG
 * @param FiltroAtuacao $filtro
 * @param boolean $mostrarLink - Diz se e para mostrar links da tabela. Padrao: TRUE
 * @param callbackfunction  $callBackBusca - Funcao de call back para busca. Padrao: NULL
 * Quando nao passada, e utilizada a funcao de busca padrao: buscarAtuacaoPorIdUsuarioCT
 * @return void
 */
function tabelaAtuacaoCandPorFiltroCT($filtro, $mostrarLink = TRUE, $callBackBusca = NULL) {
    global $CFG;

    //recuperando atuacao
    if (Util::vazioNulo($callBackBusca)) {
        $atuacoes = buscarAtuacaoPorIdUsuarioCT($filtro->getIdUsuario(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());
    } else {
        $atuacoes = $callBackBusca($filtro->getIdUsuario(), $filtro->getInicioDados(), $filtro->getQtdeDadosPag());
    }

    if ($atuacoes == NULL) {
        if ($mostrarLink) {
            echo "<div class='callout callout-warning'>Você ainda não cadastrou atuação.</div>";
        } else {
            echo "<div class='callout callout-info'>O candidato não cadastrou atuação.</div>";
        }
        return;
    }

    // verificando se e possivel alterar curiculo
    $permiteAltCurriculo = permiteAlteracaoCurriculoCT($atuacoes[0]->getCDT_ID_CANDIDATO());
    $msgCurriculo = getMsgErroEdicaoCurriculo();

    $ret = "<div class='table-responsive completo'><table class='table table-responsive table-hover table-bordered'>
    <thead><tr>
        <th>Item</th>
        <th>Área</th>
        <th class='texto-direita'>Quantidade</th>";

    if ($mostrarLink) {
        $ret .= "<th class='botao'><i class='fa fa-edit'></i></td>
        <th class='botao'><span class='fa fa-trash-o'></span></th>";
    }

    $ret .="</tr></thead>";

    //iteração para exibir formações
    for ($i = 1; $i <= sizeof($atuacoes); $i++) {
        $temp = $atuacoes[$i - 1];
        $idAtuacao = $temp->getATU_ID_ATUACAO();

        $dsTipo = Atuacao::getDsTipo($temp->getATU_TP_ITEM());

        if ($mostrarLink) {
            if ($permiteAltCurriculo) {
                $linkEditar = "<a id='linkEditar' title='Editar Atuação' href='$CFG->rwww/visao/atuacao/criarAtuacao.php?idAtuacao=$idAtuacao'><i class='fa fa-edit'></i></a>";
                $linkExcluir = "<a id='linkExcluir' title='Excluir Atuação' href='$CFG->rwww/visao/atuacao/excluirAtuacao.php?idAtuacao=$idAtuacao'><span class='fa fa-trash-o'></span></a>";
            } else {
                $linkEditar = "<a onclick='return false' id='linkEditar' title='$msgCurriculo'><i class='fa fa-ban'></i></a>";
                $linkExcluir = "<a onclick='return false' id='linkExcluir' title='$msgCurriculo'><i class='fa fa-ban'></i></a>";
            }
        }

        if ($permiteAltCurriculo || !$mostrarLink) {
            $ret .= "<tr>";
        } else {
            $ret .= "<tr title='$msgCurriculo'>";
        }

        $ret .= "
        <td>$dsTipo</td>
        <td>{$temp->getDsAreaSubarea()}</td>
        <td class='texto-direita'>{$temp->getATU_QT_ITEM()}</td>";

        if ($mostrarLink) {
            $ret .= "<td class='botao'>$linkEditar</td>
        <td class='botao'>$linkExcluir</td>";
        }

        $ret .= "</tr>";
    }

    $ret .= "</table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
                Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    return $ret;
}

function getMsgErroEdicaoCurriculo() {
    return "Você não pode alterar seu currículo enquanto estiver inscrito em algum edital com avaliação em andamento";
}

?>
