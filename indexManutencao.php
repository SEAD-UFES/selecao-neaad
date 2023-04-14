<!DOCTYPE html>
<html style="background-color:#eee">
    <head>
        <title>
            Sistema em manutenção - Seleção EAD
        </title>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <link rel="shortcut icon" href="./favicon.ico" >

        <?php
        require_once 'config.php';
        global $CFG;
        ?>

        <link rel="stylesheet" type="text/css" href="<?php print "$CFG->rwww/css/estilo.min.css" ?>">
    </head>
    <body style="background-color:#eee">
        <div id="main">
            <div id="container" class="clearfix">
                <div class="contents">
                    <div class="jumbotron">
                        <h1>Ops... Sistema em manutenção!</h1>
                        <p class="m02">No momento estamos em manutenção para melhor atendê-lo. Não se preocupe, pois nenhum dado será perdido.</p>
                        <p>Voltaremos em breve!</p>
                        <p class="m01"><a class="btn btn-primary btn-lg" href="http://neaad.ufes.br">Ir para site da SEAD</a></p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>