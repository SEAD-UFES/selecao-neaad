<?php

require_once 'util/Util.php';

//============================================================================//
//
//             Arquivo de configuração do sistema 
//
//
// Este arquivo contém as configurações do sistema. Altere as configurações
// conforme sua necessidade.
//============================================================================//
//
//============================================================================//
// Criação e inicialização da variável global
//============================================================================//
unset($CFG);
global $CFG;
$CFG = new stdClass();
//============================================================================//
//
//
//
//============================================================================//
// Configuração do banco de dados
//============================================================================//
//
//----------------------------------------------------------------------------//
// PRODUÇÃO
// 
// Pré-configuração para o ambiente de produção
// ---------------------------------------------------------------------------//
//$CFG->bdhost = "172.20.11.188";
//
//$CFG->bdporta = "3306";
//
//$CFG->bdusuario = "selecaoneaad";
//
//$CFG->bdsenha = "pduH9OXSead";
//
//$CFG->bdbanco = "selecaoneaad";
//
//----------------------------------------------------------------------------//
// DESENVOLVIMENTO
// 
// Pré-configuração para o ambiente de desenvolvimento
// ---------------------------------------------------------------------------//
//$CFG->bdhost = "172.20.11.188";
//
//$CFG->bdporta = "3306";
//
//$CFG->bdusuario = "selecaoneaaddev";
//
//$CFG->bdsenha = "selecaoneaaddev";
//
//$CFG->bdbanco = "selecaoneaaddev";
//
//----------------------------------------------------------------------------//
// TESTE
// 
// Pré-configuração para o ambiente de teste
// ---------------------------------------------------------------------------//
$CFG->bdhost = "172.20.11.188";

$CFG->bdporta = "3306";

$CFG->bdusuario = "selecaoneaadtest";

//$CFG->bdsenha = "tstH9OXSead";
$CFG->bdsenha = "selecaoneaadtest";

$CFG->bdbanco = "selecaoneaadteste";

////Teste no banco local no computador do Fernando
//$CFG->bdhost = "localhost";
//$CFG->bdporta = "3306";
//$CFG->bdusuario = "usuario_local";
//$CFG->bdsenha = "usuario_local";
//$CFG->bdbanco = "base_local";

//============================================================================//
//
//
//============================================================================//
// Configuração do ambiente de execução
//============================================================================//
//
//----------------------------------------------------------------------------//
// Ambiente de Produção
// $CFG->ambiente = Util::$AMBIENTE_PRODUCAO;
// 
// Se configurado como ambiente de produção, o sistema usará arquivos de estilo
// e script compactados, além de mensagens de erro genéricas. 
// Utilize esta opção se o sistema estiver em produção ou em teste final, para
// ocultar do usuário detalhes de erro e otimizar o download de arquivos de
// estilo e script.
// 
// ---------------------------------------------------------------------------//
// Ambiente de Desenvolvimento
// $CFG->ambiente = Util::$AMBIENTE_DESENVOLVIMENTO;
// 
// Se configurado como ambiente de desenvolvimento, o sistema usará arquivos de 
// estilo e script na sua versão original, além de mensagens de erro detalhadas
// para facilitar a depuração do código. Neste ambiente o sistema também imprime
// os emails diretamente na tela, caso o servidor de email não esteja
// configurado ou não esteja funcionando corretamente.
// Utilize esta opção se o sistema estiver em desenvolvimento ou em fase inicial
// de teste.
// ---------------------------------------------------------------------------//
// Ambiente de execução
$CFG->ambiente = Util::$AMBIENTE_DESENVOLVIMENTO;
//
// Flag de sistema em teste
// Se for TRUE, então o sistema força a exibição de erros na tela!
$CFG->emTeste = FALSE;
//============================================================================//
//
//
//============================================================================//
// Localização do site no servidor (Sem a barra final)
//============================================================================//
$nomeServidor = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost";
$CFG->rwww = "http://$nomeServidor/selecaoneaad";
$CFG->rpasta = "/var/www/html/selecaoneaad";
//============================================================================//
//
//
//============================================================================//
// Outras configurações
//============================================================================//
$CFG->emailContato = "Suporte EAD <suporte.sead.ufes@gmail.com>";
//============================================================================//
//
//
//
//
//
//============================================================================//
// Forçando mensagens de erro no ambiente de desenvolvimento
//============================================================================//
if ($CFG->emTeste || $CFG->ambiente == Util::$AMBIENTE_DESENVOLVIMENTO) {
    ini_set('display_errors', 1);
    ini_set('display_startup_erros', 1);
    error_reporting(E_ALL);
}
?>
