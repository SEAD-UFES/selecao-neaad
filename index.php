<?php

require_once 'config.php';
global $CFG;
include ($CFG->rpasta . "/util/sessao.php");
if (estaLogado() != null) {
    //redirecionando para página principal
    header("Location: $CFG->rwww/inicio");
    return;
} else {
    //redirecionando para login
    header("Location: $CFG->rwww/acesso");
    return;
}
?>

