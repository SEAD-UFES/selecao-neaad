<!DOCTYPE html>
<html>
    <head>     
        <title>Tipos de Formação - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTParametrizacao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>
    </head>

    <body>  
        <?php
        include ($CFG->rpasta . "/include/cabecalho.php");
        ?>
        <div id="main">
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1>Você está em: Parametrização > <strong>Tipo de Formação</strong></h1>
                </div>
                
                <div class="contents completo m02 p15">
                    <?php echo tabelaTiposFormacao(); ?>

                    <button id="btVoltar" class="btn btn-default" type="button" onclick="javascript: window.location = '<?php echo "$CFG->rwww/inicio";?>';">Voltar</button>
                </div>
            </div> 
        </div>  
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
</html>