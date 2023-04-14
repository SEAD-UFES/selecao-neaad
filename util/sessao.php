<?php

require_once (dirname(__FILE__) . "/../config.php");
global $CFG;
require_once($CFG->rpasta . "/negocio/Usuario.php");
require_once($CFG->rpasta . "/negocio/Candidato.php");
require_once($CFG->rpasta . "/util/Mensagem.php");

define("TEMPO_MAXIMO_INATIVO", (3 * 60 * 60)); // 3h * 60m * 60s = 3 horas
define("TEMPO_MAXIMO_HASH", 5 * 60); // 5m * 60s = 5min
define("NM_SESSAO_SIS", "selSEAD");
//
//
//verificando o que fazer
if (isset($_GET['sair'])) {
    encerrar();
}

if (isset($_GET['acao']) && $_GET['acao'] == "login" && isset($_POST['valido']) && $_POST['valido'] == "sessao") {
    fazerLogin();
}

function inicializaSessao() {
    //recupera a sessão iniciada.
    session_name(NM_SESSAO_SIS);
    if (!isset($_SESSION)) {
        session_start();
    }
}

function fazerLogin() {
    global $CFG;

    // Se o usuário já está logado, nada a fazer
    if (estaLogado() != NULL) {
        //Redireciona a aplicação para a página principal
        header("Location: $CFG->rwww/inicio");
        return;
    }

    //recupera parâmetros via post
    if (!isset($_POST['login']) || !isset($_POST['senha'])) {
        new Mensagem("Chamada incorreta para login.", Mensagem::$MENSAGEM_ERRO);
        return;
    }
    $login = $_POST['login'];
    $senha = $_POST['senha'];
    $popup = $_POST['loginPopup'] === "true";


    // Define url de destino em caso de sucesso de login e em caso de erro
    $urlSessao = sessaoNav_getNavegacaoLogin(!$popup);
    $urlDestinoErro = $urlSessao != NULL ? "$CFG->rwww/$urlSessao" : "$CFG->rwww/acesso";
    $urlDestino = $urlSessao != NULL ? "$CFG->rwww/$urlSessao" : "$CFG->rwww/inicio";

    try {
        //verificando se o login é válido
        $usuario = Usuario::validarLogin($login, $senha);

        //caso de não ser válido
        if ($usuario === NULL) {
            new Mensagem("Login ou senha inválidos.", Mensagem::$MENSAGEM_ERRO, NULL, "errAutenticacao", $urlDestinoErro);
            return;
        }

        // caso de estar bloqueado
        if ($usuario === FALSE) {
            new Mensagem("Usuário bloqueado.", Mensagem::$MENSAGEM_ERRO, NULL, "errBloqueio", $urlDestinoErro);
            return;
        }
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }

    inicializaSessao();

    //salvando dados na sessão
    $_SESSION["login"] = true;
    $_SESSION['tipo'] = $usuario->getUSR_TP_USUARIO();
    $_SESSION['dsTipo'] = Usuario::getDsTipo($usuario->getUSR_TP_USUARIO());
    $_SESSION['idUsuario'] = $usuario->getUSR_ID_USUARIO();
    $_SESSION['dsNome'] = $usuario->getUSR_DS_NOME();
    $_SESSION['dsEmail'] = $usuario->getUSR_DS_EMAIL();
    $_SESSION['tpVinculoUfes'] = $usuario->getUSR_TP_VINCULO_UFES();
    $_SESSION['dtUltLogin'] = $usuario->getUSR_LOG_DT_ULT_LOGIN() == NULL ? "Primeiro Acesso" : $usuario->getUSR_LOG_DT_ULT_LOGIN();
    $_SESSION['tempoLogin'] = time();
    $_SESSION['trocarSenha'] = $usuario->isTrocarSenha();
    $_SESSION['hashSessao'] = $usuario->getUSR_HASH_ALTERACAO_EXT();
    $_SESSION['tempoHash'] = time();
    $_SESSION['conversaoLU'] = $usuario->isConversaoLU();


    // caso seja candidato, verificando necessidade de mostrar aviso de inatividade
    if ($_SESSION['tipo'] == Usuario::$USUARIO_CANDIDATO) {
        try {
            $_SESSION['mostrarInatividade'] = Candidato::mostrarInatividade($_SESSION['idUsuario']);
        } catch (Exception $e) {
            // Paciencia! 
            $_SESSION['mostrarInatividade'] = FALSE;
        }
    }


    // verificar se e necessario trocar a senha
    if ($_SESSION['trocarSenha']) {
        // redirecionando para troca se senha
        header("Location: $CFG->rwww/visao/usuario/alterarSenha.php?f=true");
        exit;
    }

    // definindo flag de conversao: Apenas se for para voltar a página principal
    $flagConversaoLU = $_SESSION['conversaoLU'] && $urlSessao == NULL && $_SESSION['tipo'] == Usuario::$USUARIO_CANDIDATO ? "?" . Mensagem::$TOAST_VAR_GET . "=conversaoLU" : "";

    // redirecionando para a página destino
    if ($_SESSION['tipo'] == Usuario::$USUARIO_CANDIDATO) {
        header("Location: {$urlDestino}{$flagConversaoLU}");
    } else {
        header("Location: $CFG->rwww/inicio{$flagConversaoLU}");
    }
}

function verificaTempoSessao() {
    global $CFG;

    inicializaSessao();

    if (!isset($_SESSION["login"])) {
        return;
    }

    //verificando se está apto a continuar
    $tempoAtual = time();

    // tempo de inatividade
    if ($tempoAtual - $_SESSION['tempoLogin'] > TEMPO_MAXIMO_INATIVO) {
        encerrarSemRedirecionar();
        new Mensagem("Sua sessão expirou.<br/>Por favor, faça seu login novamente.", Mensagem::$MENSAGEM_AVISO, NULL, "notSessao", "$CFG->rwww/acesso");
        exit(0);
    }

    // validando hash da sessão
    if ($tempoAtual - $_SESSION['tempoHash'] > TEMPO_MAXIMO_HASH) {
        try {
            // validando
            if (!Usuario::validarHashSessao($_SESSION['idUsuario'], $_SESSION['hashSessao'])) {
                encerrarSemRedirecionar();
                new Mensagem("Sua sessão expirou.<br/>Por favor, faça seu login novamente.", Mensagem::$MENSAGEM_AVISO, NULL, "notSessao", "$CFG->rwww/acesso");
                exit(0);
            }
        } catch (Exception $e) {
            // Nada a fazer...; Apenas aguardar a próxima oportunidade de validação
        }

        // atualizando tempo de hash
        $_SESSION['tempoHash'] = time();
    }


    //atualizando o tempo
    $_SESSION['tempoLogin'] = time();
}

/**
 * 
 * @param string $nmUsuario
 * @param string $novoHash Novo Hash do usuário
 * @return void
 */
function atualizarNomeSessao($nmUsuario, $novoHash) {
    if (estaLogado() == NULL) {
        return;
    }
    $_SESSION['dsNome'] = $nmUsuario;
    $_SESSION['hashSessao'] = $novoHash;
}

/**
 * 
 * @param string $dsEmail
 * @param string $novoHash Novo Hash do usuário
 * @return void
 */
function atualizarEmailSessao($dsEmail, $novoHash) {
    if (estaLogado() == NULL) {
        return;
    }
    $_SESSION['dsEmail'] = $dsEmail;
    $_SESSION['hashSessao'] = $novoHash;
}

function sessao_isMostrarInatividade() {
    if (estaLogado(Usuario::$USUARIO_CANDIDATO) != NULL) {
        return $_SESSION['mostrarInatividade'];
    }
    return null;
}

function sessao_setMostrarInatividade($status) {
    if (estaLogado(Usuario::$USUARIO_CANDIDATO) != NULL) {
        $_SESSION['mostrarInatividade'] = $status;
    }
}

function encerrar() {
    global $CFG;

    inicializaSessao();

    //reseta o vetor $_SESSION
    $_SESSION = array();

    //Destrói a sessão.
    session_destroy();

    //Redireciona a aplicação para a página de login
    header("Location: $CFG->rwww/acesso");
}

function encerrarSemRedirecionar() {
    inicializaSessao();

    // reseta o vetor $_SESSION
    $_SESSION = array();

    //Destrói a sessão.
    session_destroy();
}

function encerrarApenasSessaoLogin() {
    inicializaSessao();

    // remove variáveis de login
    foreach (array_keys($_SESSION) as $chave) {
        if ($chave != "navegacaoLogin" && !startsWith($chave, "dados")) {
            $var = $_SESSION[$chave];
            unset($_SESSION[$chave], $var);
        }
    }
}

function getDadosLogin() {
    if (estaLogado() == null) {
        return null;
    }
    return array("tipo" => $_SESSION['tipo'], "dsTipo" => $_SESSION['dsTipo'], "idUsuario" => $_SESSION['idUsuario'],
        "dsNome" => $_SESSION['dsNome'], "dsEmail" => $_SESSION['dsEmail'], "dtUltLogin" => $_SESSION['dtUltLogin'], "tpVinculoUfes" => $_SESSION['tpVinculoUfes']);
}

function getNmUsuarioLogado() {
    if (estaLogado() != NULL) {
        return $_SESSION['dsNome'];
    }
    return null;
}

function getPrimeiroNmUsuarioLogado() {
    $nome = getNmUsuarioLogado();
    if ($nome == NULL) {
        return NULL;
    }
    // recuperando primeiro nome
    $temp = explode(" ", $nome);
    return $temp[0];
}

function getEmailUsuarioLogado() {
    if (estaLogado() != NULL) {
        return $_SESSION['dsEmail'];
    }
    return null;
}

function getIdUsuarioLogado() {
    if (estaLogado() != NULL) {
        return $_SESSION['idUsuario'];
    }
    return null;
}

function getTipoUsuarioLogado() {
    if (estaLogado() != NULL) {
        return $_SESSION['tipo'];
    }
    return null;
}

function isTrocarSenhaUsuarioLogado() {
    if (estaLogado() != NULL) {
        return $_SESSION['trocarSenha'];
    }
    return null;
}

/**
 * Esta função verifica se o usuário logado no sistema
 * 
 * @global stdclass $CFG
 * 
 * @param char $tipo Tipo de usuário que se deseja saber se está logado
 * @param boolean $forcarSaidaTpDiferente Informa se, caso exista algum usuário diferente do tipo pretendido logado, o mesmo 
 * deve ser deslogado. Funciona apenas se o tipo for não NULO. Valor padrão: FALSE.
 * 
 * @return char Retorna o tipo de usuário logado.
 */
function estaLogado($tipo = NULL, $forcarSaidaTpDiferente = FALSE) {
    global $CFG;

    inicializaSessao();


    if (isset($_SESSION["login"]) && $_SESSION['login'] === TRUE) {
        // verificar se e necessario trocar a senha
        if ($_SESSION['trocarSenha']) {
            $url = $_SERVER['REQUEST_URI'];
            if (strpos($url, 'visao') !== FALSE && strpos($url, "alterarSenha.php?f=true") === FALSE) {
                // redirecionando para troca se senha
                header("Location: $CFG->rwww/visao/usuario/alterarSenha.php?f=true");
                exit();
            }
        }

        //verifica tempo
        verificaTempoSessao();

        //vendo o q retornar
        if ($tipo == NULL) {
            return $_SESSION['tipo'];
        }

        //verificando um tipo específico
        if ($_SESSION['tipo'] == $tipo) {
            return $_SESSION['tipo'];
        }

        //não é do tipo específico? // deve forçar saída?
        if ($forcarSaidaTpDiferente) {
            encerrarApenasSessaoLogin();
        }
        return NULL;
    }
    //não está logado
    return NULL;
}

/**
 * 
 * Esta função cria uma regra de navegação especial para os casos de pós-login, 
 * ou seja, ao concluir a operação de fazer login com sucesso, para qual página
 * o usuário deve ser redirecionado.
 * 
 * @param string $destino URL complementar, ou seja, depois da raiz, que deve ser requisitada ao fazer Login.
 * Exemplo: para redirecionar o usuário para a página de editais (em selecaoneaad/edital.php), o destino deve conter apenas a seguinte string: edital.php
 * 
 */
function sessaoNav_setNavegacaoLogin($destino) {
    inicializaSessao();

    // setando valores
    $_SESSION['navegacaoLogin'] = $destino;
}

/**
 * Esta função recupera a URL que o usuário deve ser redirecionado após fazer login.
 * Caso não tenha uma pré definição, é retornado NULL.
 * 
 * OBS: ESTA FUNÇÃO SEMPRE LIMPA A VARIÁVEL DA URL DE NAVEGAÇÃO APÓS SUA CHAMADA. 
 * 
 * @param boolean $apenasLimpeza Informa se é apenas para limpar o conteúdo da sessão, sem retorná-lo.
 * 
 *  */
function sessaoNav_getNavegacaoLogin($apenasLimpeza = FALSE) {
    inicializaSessao();

    if (isset($_SESSION['navegacaoLogin'])) {
        $ret = $_SESSION['navegacaoLogin'];

        // removendo
        $var = $_SESSION['navegacaoLogin'];
        unset($_SESSION['navegacaoLogin'], $var);

        return $apenasLimpeza ? NULL : $ret;
    }
    return NULL;
}

// funções de manipulação da sessão dados
function sessaoDados_setDados($chave, $valor) {
    inicializaSessao();

    // setando valores
    $_SESSION["dados" . $chave] = $valor;
}

function sessaoDados_removerDados($chave) {
    inicializaSessao();

    // removendo
    if (isset($_SESSION["dados" . $chave])) {
        $var = $_SESSION["dados" . $chave];
        unset($_SESSION["dados" . $chave], $var);
    }
}

/**
 * Retorna o dado da sessão. 
 * Se a chave requisitada não está preenchida, é retornado NULL.
 * 
 * @param string $chave
 * @return mixed
 */
function sessaoDados_getDados($chave) {
    inicializaSessao();

    if (isset($_SESSION["dados" . $chave])) {
        return $_SESSION["dados" . $chave];
    } else {
        return NULL;
    }
}

?>
