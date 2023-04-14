<!DOCTYPE html>
<html style="background-color:#eee">
    <head>
        <title>
            <?php
            $tpErro = isset($_GET['err']) ? $_GET['err'] : NULL;
            if ($tpErro == "arq") {
                ?>
                Arquivo não encontrado
            <?php } else {
                ?>
                Página não encontrada
            <?php } ?>
            - Seleção EAD
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
                        <?php
                        if ($tpErro == "arq") {
                            ?>
                            <h1>Arquivo não encontrado!</h1>
                            <p class="m02">O arquivo solicitado não está disponível no sistema.</p>
                            <p class="01"><a class="btn btn-primary btn-lg" onclick="javascript: window.close()">Tudo bem, voltar</a></p>
                        <?php } else {
                            ?>
                            <h1>Ops... Página não encontrada!</h1>
                            <p class="m02">O endereço requisitado não foi encontrado neste servidor. Se você digitou a URL manualmente, por favor verifique novamente o endereço.</p>
                            <p>Se você acredita ter encontrado um problema, por favor entre em contato com nosso Suporte.</p>
                            <p class="m01"><a class="btn btn-primary btn-lg" href="<?php print "$CFG->rwww/index.php" ?>">Ir para página inicial</a></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>