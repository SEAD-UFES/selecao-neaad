<?php

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/util/Mensagem.php";
require_once $CFG->rpasta . "/negocio/Usuario.php";
require_once $CFG->rpasta . "/negocio/Endereco.php";
require_once $CFG->rpasta . "/negocio/FormacaoAcademica.php";

//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctusuario") {
    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];

        //caso recuperar senha
        if ($acao == "recuperarSenha") {
            if (estaLogado() != NULL) {
                //redirecionando para página principal
                header("Location:$CFG->rwww/inicio");
                return;
            }
            try {

                // recuperando e preparando dados para formato banco
                $nrCPF = isset($_POST['nrCPF']) ? removerMascara("###.###.###-##", $_POST['nrCPF']) : NULL;
                $dtNascimento = isset($_POST['dtNascimento']) ? $_POST['dtNascimento'] : NULL;


                $msgRet = Usuario::gerarRecuperacaoSenha($_POST['dsEmail'], $dtNascimento, $nrCPF);

                //redirecionando
                new Mensagem($msgRet, Mensagem::$MENSAGEM_INFORMACAO);
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de trocar senha recuperada
        if ($acao == "trocarSenhaRecuperada") {
            if (estaLogado() != NULL) {
                //redirecionando para página principal
                header("Location:$CFG->rwww/inicio");
                return;
            }
            try {
                $msgRet = Usuario::trocarSenhaRecuperada($_POST['id'], $_POST['ch'], $_POST['dt'], $_POST['idUsuario'], $_POST['dsEmail'], $_POST['dsSenha']);

                //redirecionando
                new Mensagem("Nova senha criada com sucesso.<br/>Para acessar o sistema, navegue até a página inicial (link abaixo) e insira o seu email e a nova senha cadastrada.", Mensagem::$MENSAGEM_INFORMACAO);
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso de alterar senha
        if ($acao == "alterarSenha") {
            if (estaLogado() == NULL) {
                //redirecionando para página de login
                header("Location:$CFG->rwww/acesso");
                return;
            }
            try {
                if (Usuario::alterarSenha(getIdUsuarioLogado(), $_POST['dsSenhaAtual'], $_POST['dsSenha']) === TRUE) {
                    // encerrando sessao para novo login
                    encerrarSemRedirecionar();

                    //redirecionando
                    new Mensagem("Senha alterada com sucesso.<br/>Utilize sua nova senha para acessar o sistema.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucNovaSenha", "$CFG->rwww/acesso");
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

        //caso de alterar login
        if ($acao == "alterarLogin") {
            if (estaLogado() == NULL) {
                //redirecionando para página de login
                header("Location:$CFG->rwww/acesso");
                return;
            }
            try {
                if (Usuario::alterarLogin(getIdUsuarioLogado(), $_POST['dsEmail'], $_POST['dsSenhaAtual']) === TRUE) {
                    // encerrando sessao para novo login
                    encerrarSemRedirecionar();

                    //redirecionando
                    new Mensagem("Login alterado com sucesso.<br/>Utilize seu novo login para acessar o sistema.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucNovoLogin", "$CFG->rwww/acesso");
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

        //caso cadastro administrador
        if ($acao == "criarUsuarioAdmin") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
                new Mensagem("Você precisa estar logado como administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                // recuperando possivel curso do avaliador
                $idCursoAval = isset($_POST['idCursoAvaliador']) ? $_POST['idCursoAvaliador'] : NULL;

                //criando objeto usuário com os parâmetros
                $objUsuario = new Usuario(NULL, $_POST['tpUsuario'], $_POST['dsEmail'], $_POST['dsEmail'], $_POST['dsSenha'], $_POST['dsNome'], Usuario::$VINCULO_NENHUM, $_POST['stSituacao'], NULL, NULL, NULL, NULL, (isset($_POST['forcarTroca']) ? TRUE : FALSE), $idCursoAval);

                // criando objetos auxiliares
                // recuperando possivel curso
                $idCursoCoord = isset($_POST['idCurso']) ? $_POST['idCurso'] : NULL;

                // recuperando possivel cpf, data de nascimento e email alternativo para candidato
                $identCan = new IdentificacaoCandidato(NULL, removerMascara("###.###.###-##", $_POST['nrCPF']), NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $_POST['dtNascimento']);

                $contatoCand = new ContatoCandidato(NULL, NULL, NULL, NULL, NULL, $_POST['dsEmailAlternativo']);

                //criando
                $objUsuario->criarUsuario(NULL, $identCan, $contatoCand, $idCursoCoord, $idCursoAval);

                //redirecionando
                new Mensagem("Usuário cadastrado com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/usuario/listarUsuario.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso alterar Senha
        if ($acao == "alterarSenhaUsuarioAdmin") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
                new Mensagem("Você precisa estar logado como administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //recuperando parâmetros
                $idUsuario = $_POST['idUsuario'];
                $dsSenha = $_POST['dsSenha'];
                $forcarTroca = isset($_POST['forcarTroca']) ? TRUE : FALSE;

                //reiniciando senha
                Usuario::reiniciarSenha($idUsuario, $dsSenha, $forcarTroca);

                //redirecionando
                if ($idUsuario != getIdUsuarioLogado()) {
                    new Mensagem("Senha do Usuário reiniciada com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucSenha", "$CFG->rwww/visao/usuario/listarUsuario.php");
                } else {
                    encerrarSemRedirecionar();
                    new Mensagem("Sua senha foi reiniciada com sucesso.", Mensagem::$MENSAGEM_INFORMACAO);
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

        //caso editarUsuarioAdmin
        if ($acao == "editarUsuario") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
                new Mensagem("Você precisa estar logado como administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {

                //criando objeto usuário com os parâmetros
                $objUsuario = new Usuario($_POST['idUsuario'], NULL, NULL, isset($_POST['dsEmail']) ? $_POST['dsEmail'] : NULL, NULL, $_POST['dsNome'], NULL, $_POST['stSituacao'], NULL, NULL, NULL, NULL, NULL, $_POST['idCursoAvaliador']);

                //atualizando
                $sair = $objUsuario->editarUsuario((isset($_POST['idCurso']) ? $_POST['idCurso'] : NULL), (isset($_POST['nrCPF']) ? removerMascara("###.###.###-##", $_POST['nrCPF']) : NULL), (isset($_POST['dtNascimento']) ? $_POST['dtNascimento'] : NULL), (isset($_POST['dsEmailAlternativo']) ? $_POST['dsEmailAlternativo'] : NULL), (isset($_POST['desvincular']) ? TRUE : FALSE));

                if (!$sair) {
                    new Mensagem("Usuário atualizado com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucEdicao", "$CFG->rwww/visao/usuario/listarUsuario.php");
                } else {
                    encerrarSemRedirecionar();
                    new Mensagem("Seu Login foi atualizado com sucesso.", Mensagem::$MENSAGEM_INFORMACAO);
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

        //caso excluir usuário
        if ($acao == "excluirUsuario") {
            if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
                new Mensagem("Você precisa estar logado como administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
            try {
                //recuperando parâmetros
                $idUsuario = $_POST['idUsuario'];

                //excluindo
                Usuario::excluirUsuario($idUsuario);

                //redirecionando
                new Mensagem("Usuário excluído com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/usuario/listarUsuario.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso enviar contato
        if ($acao == "enviarContato") {
            try {
                // recuperando campos
                $nome = $_POST['nome'];
                $email = $_POST['email'];
                $msg = $_POST['mensagem'];
                $telefone = $_POST['telefone'];
                $tpContato = $_POST['tpContato'];
                $captchaSemHash = $_POST['defaultReal'];
                $captchaComHash = $_POST['defaultRealHash'];

                if (rpHash($captchaSemHash) == $captchaComHash) {
                    // enviando mensagem
                    $envio = Usuario::enviarMsgContato($nome, $email, $tpContato, $telefone, $msg);
                } else {
                    new Mensagem("Erro: Captcha incorreto.", Mensagem::$MENSAGEM_ERRO, NULL, "errCaptcha", "$CFG->rwww/contato");
                }

                //redirecionando
                if ($envio) {
                    new Mensagem("Mensagem enviada com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucContato", "$CFG->rwww/contato");
                } else {
                    new Mensagem("Erro ao enviar mensagem.", Mensagem::$MENSAGEM_ERRO, NULL, "errContato", "$CFG->rwww/contato");
                }
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

// Inserindo funções que recebem os paramentros do captcha
function rpHash($value) {
    $hash = 5381;
    $value = strtoupper($value);
    for ($i = 0; $i < strlen($value); $i++) {
        $hash = (leftShift32($hash, 5) + $hash) + ord(substr($value, $i));
    }
    return $hash;
}

// Perform a 32bit left shift 
function leftShift32($number, $steps) {
    // convert to binary (string) 
    $binary = decbin($number);
    // left-pad with 0's if necessary 
    $binary = str_pad($binary, 32, "0", STR_PAD_LEFT);
    // left shift manually 
    $binary = $binary . str_repeat("0", $steps);
    // get the last 32 bits 
    $binary = substr($binary, strlen($binary) - 32);
    // if it's a positive number return it 
    // otherwise return the 2's complement 
    return ($binary{0} == "0" ? bindec($binary) : -(pow(2, 31) - bindec(substr($binary, 1))));
}

function validarAlterarSenhaCT($id, $ch, $dt, $idUsuario, $dsEmail = NULL) {
    try {
        Usuario::validarAlterarSenha($id, $ch, $dt, $idUsuario, $dsEmail);
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

function permiteExclusaoUsuCT($idUsuario, $tpUsuario) {
    try {
        return Usuario::permiteExclusaoUsu($idUsuario, $tpUsuario);
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

function buscaTodosUsuariosCT($stSituacao, $tpUsuario) {
    try {
        return Usuario::buscarTodosUsuarios($stSituacao, $tpUsuario);
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
 * @return Usuario
 */
function buscarUsuarioPorIdCT($idUsuario) {
    try {
        return Usuario::buscarUsuarioPorId($idUsuario);
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
 * @param int $idCurso
 * @return Usuario
 */
function buscarUsusAvaliadoresPorCursoCT($idCurso) {
    try {
        return Usuario::buscarUsusAvaliadoresPorCurso($idCurso);
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
 * @param int $idCurso
 * @param boolean $restrito
 * @return array
 */
function buscarAvalLivrePorCursoCT($idCurso, $restrito = FALSE) {
    try {
        return Usuario::buscarAvalLivrePorCurso($idCurso, $restrito);
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
 * @return Usuario
 */
function buscarUsuarioPorIdCandCT($idCandidato) {
    try {
        return Usuario::buscarUsuarioPorIdCand($idCandidato);
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

function validarCadastroEmailCTAjax($dsEmail, $idUsuario = NULL) {
    try {
        return Usuario::validarCadastroEmail($dsEmail, $idUsuario);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

function validarEmailRecuperarSenhaCTAjax($dsEmail) {
    try {
        return Usuario::validarEmailRecuperarSenha($dsEmail);
    } catch (Exception $e) {
        //retornando false
        return array('status' => FALSE, 'msg' => "<b>Erro</b> ao buscar dados de recuperação de senha: {$e->getMessage()}");
    }
}

function validarEmailAlternativoCTAjax($dsEmail, $idUsuario = NULL) {
    try {
        return Usuario::validarEmailAlternativo($dsEmail, $idUsuario);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

/**
 * 
 * @param FiltroUsuario $filtroUsu
 * @return int
 */
function contaUsuariosPorFiltroCT($filtroUsu) {
    try {
        return Usuario::contaUsuariosPorFiltro($filtroUsu->getDsNome(), $filtroUsu->getDsEmail(), $filtroUsu->getTpUsuario(), $filtroUsu->getNrcpf(), $filtroUsu->getStSituacao());
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

function buscarUsuariosPorFiltroCT($dsNome, $dsEmail, $tpUsuario, $nrcpf, $stSituacao, $inicioDados, $qtdeDados) {
    try {
        return Usuario::buscarUsuariosPorFiltro($dsNome, $dsEmail, $tpUsuario, $nrcpf, $stSituacao, $inicioDados, $qtdeDados);
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
 * @param FiltroUsuario $filtroUsu
 * @return string
 */
function tabelaUsuariosPorFiltro($filtroUsu) {
    //recuperando 
    $usuarios = buscarUsuariosPorFiltroCT($filtroUsu->getDsNome(), $filtroUsu->getDsEmail(), $filtroUsu->getTpUsuario(), $filtroUsu->getNrcpf(), $filtroUsu->getStSituacao(), $filtroUsu->getInicioDados(), $filtroUsu->getQtdeDadosPag());

    if (count($usuarios) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
    <thead><tr>
        <th>Código</th>
        <th>Nome</th>
        <th class='campoDesktop'>Email</th>
        <th class='campoDesktop'>CPF</th>
        <th>Tipo</th>
        <th class='botao'><span class='fa fa-eye'></span></th>
        <th class='botao'><span class='fa fa-asterisk'></span></th>
        <th class='botao'><i class='fa fa-edit'></i></th>
        <th class='botao'><span class='fa fa-trash-o'></span></th>
    </tr></thead>";

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($usuarios); $i++) {
        $temp = $usuarios[$i - 1];

        //definindo msg de excluir
        $msgExcluir = "Este usuário não pode ser excluído porque já realizou alguma operação com necessidade de registro";

        $nmTipo = Usuario::getDsTipo($temp->getUSR_TP_USUARIO());
        $linkConsultar = "<a id='linkConsultar' title='Consultar usuário' href='consultarUsuario.php?idUsuario={$temp->getUSR_ID_USUARIO()}' class='itemTabela'><span class='fa fa-eye'></span></a>";
        $linkEditar = "<a id='linkEditar' title='Editar usuário' href='editarUsuario.php?idUsuario={$temp->getUSR_ID_USUARIO()}' class='itemTabela'><i class='fa fa-edit'></i></a>";

        if (permiteExclusaoUsuCT($temp->getUSR_ID_USUARIO(), $temp->getUSR_TP_USUARIO())) {
            $linkExcluir = "<a id='linkExcluir' title='Excluir usuário' href='excluirUsuario.php?idUsuario={$temp->getUSR_ID_USUARIO()}' class='itemTabela'><span class='fa fa-trash-o'></span></a>";
        } else {
            $linkExcluir = "<a onclick='return false' id='linkExcluir' title='$msgExcluir' href='' class='itemTabela'><i class='fa fa-ban'></i></a>";
        }

        if ($temp->isComunidadeExterna() && $temp->isAtivo()) {
            $linkalterarSenha = "<a id='linkReiniciar' title='Alterar senha do usuário' href='alterarSenhaUsuAdmin.php?idUsuario={$temp->getUSR_ID_USUARIO()}' class='itemTabela'><span class='fa fa-asterisk'></span></a>";
        } else {
            $tituloAltSenha = $temp->isAtivo() ? "Usuário pertencente ao Login Único da UFES" : "Usuário inativo";
            $linkalterarSenha = "<a onclick='return false' id='linkReiniciar' title='$tituloAltSenha' href='' class='itemTabela'><i class='fa fa-ban'></i></a>";
        }

        // tratamento especial dos inativos
        if (!$temp->isAtivo()) {
            $adendoLinha = "title='Usuário inativo' class='inativo'";
            $codUsu = Util::$STR_CAMPO_VAZIO;
        } else {
            $adendoLinha = "";
            $codUsu = $temp->getUSR_ID_USUARIO();
        }

        $ret .= "<tr $adendoLinha>
                    <td>$codUsu</td>
                    <td>{$temp->getUSR_DS_NOME()}</td>
                    <td class='campoDesktop'>{$temp->getUSR_DS_EMAIL()}</td>
                    <td class='campoDesktop'>{$temp->getNrCpfMascarado()}</td>
                    <td>$nmTipo</td>
                    <td class='botao'>$linkConsultar</td>
                    <td class='botao'>$linkalterarSenha</td>
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

?>
