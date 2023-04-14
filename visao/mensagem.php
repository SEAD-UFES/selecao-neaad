<!DOCTYPE html>
<html>
    <head>     
        <title>Mensagem - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../config.php';
        global $CFG;
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

                <div class="contents m02">
                    <?php
                    require_once ($CFG->rpasta . "/util/Mensagem.php");
                    $titulo = Mensagem::getDsTipo((isset($_POST['tipoMsg'])) ? $_POST['tipoMsg'] : Mensagem::$MENSAGEM_ERRO);
                    ?>


                    <legend><?php print $titulo; ?></legend>
                    <?php
                    $classe = Mensagem::getClasse((isset($_POST['tipoMsg'])) ? $_POST['tipoMsg'] : Mensagem::$MENSAGEM_ERRO);
                    $mensagem = ((isset($_POST['mensagem'])) ? $_POST['mensagem'] : Mensagem::$MENSAGEM_PADRAO_ERRO);
                    ?>

                    <div class="<?php print $classe ?>">					
                        <div>
                            <span><?php print $mensagem; ?></span>
                        </div>
                        <br/>
                        <a href="<?php print "$CFG->rwww/inicio" ?>">Página Inicial</a>
                    </div>
                </div>
            </div>  
        </div>

        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
</html>

