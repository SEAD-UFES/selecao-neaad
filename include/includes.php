<?php
require_once dirname(__FILE__) . "/../config.php";
global $CFG;

// Validando acesso ao site: O navegador possui as tecnologias necessárias?
require_once $CFG->rpasta . "/include/validadorAcesso.php";
?>

<?php
require_once $CFG->rpasta . "/util/sessao.php";
inicializaSessao(); // inicializando sessão

require_once $CFG->rpasta . "/negocio/NGUtil.php";
require_once $CFG->rpasta . "/util/Util.php";
?>

<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<link rel="shortcut icon" href="<?php print $CFG->rwww; ?>/favicon.ico" >
<meta name="keywords" content="ufes,ead,seleção,ensino a distancia" />
<meta name="author" content="http://www.ufes.br" />
<meta name="description" content="Sistema de seleção da Secretaria de Ensino a Distância da Universidade Federal do Espírito Santo - SEAD / UFES."/>

<?php

// definindo constantes de carga...
        const CSS_PASTA = "/css/";
        const CSS_EXTENSAO = ".css";
        const CSS_ADD_PROD = ".min";

        const JS_PASTA = "/javascript/";
        const JS_EXTENSAO = ".js";
        const JS_ADD_PROD = ".min";

// Carregando css padrão
foreach (array('estilo') as $arq) {
    carregaCSS($arq);
}

// carregando script padrão
foreach (array('jquery') as $arq) {
    carregaScript($arq);
}
?>

<?php

// Função que carrega arquivo CSS
function carregaCSS($nmArquivo) {
    global $CFG;
    if ($CFG->ambiente == Util::$AMBIENTE_DESENVOLVIMENTO) {
        $nmArquivo .= CSS_EXTENSAO;
    } else {
        $nmArquivo .= CSS_ADD_PROD . CSS_EXTENSAO;
    }
    echo "<link rel='stylesheet' href='$CFG->rwww" . CSS_PASTA . "$nmArquivo' type='text/css' />";
}

// Função que carrega arquivo Javascript
function carregaScript($nmArquivo) {
    global $CFG;
    if ($CFG->ambiente == Util::$AMBIENTE_DESENVOLVIMENTO) {
        $nmArquivo .= JS_EXTENSAO;
    } else {
        $nmArquivo .= JS_ADD_PROD . JS_EXTENSAO;
    }
    echo "<script src='$CFG->rwww" . JS_PASTA . "$nmArquivo' type ='text/javascript'></script>";
}
?>