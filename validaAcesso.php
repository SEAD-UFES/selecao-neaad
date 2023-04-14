<!DOCTYPE html>
<html style="background-color:#eee">
    <head>
        <title>Seleção EAD - Validação de Acesso</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

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
                    <?php
                    $tpVal = isset($_GET['val']) ? $_GET['val'] : NULL;
                    if ($tpVal === NULL) {
                        return; // tipo de validação não especificado. Retornar
                    } elseif ($tpVal == "js") {
                        ?>
                        <div class="jumbotron">
                            <h1>Ops...</h1>
                            <p class="m02">Para completa funcionalidade deste site é necessário habilitar o JavaScript.</p>
                            <p class="m01"><a title="Aprender" class="btn btn-primary btn-lg" href="http://www.enable-javascript.com/pt/" target="_blank">
                                    Como habilitar JavaScript
                                </a>
                            </p>
                        </div>

                    <?php } elseif ($tpVal == 'nav') { ?>
                        <div class="jumbotron">
                            <h1>Ops...</h1>
                            <p class="m02">Você está utilizando uma versão antiga de seu navegador, que contém vulnerabilidades, não sendo compatível com o nosso sistema. Atualize seu navegador ou baixe um recomendado:</p>
                            <p class="m01"><a class="btn btn-success btn-lg" href="http://www.google.com.br/chrome/" target="_blank">Google Chrome</a>
                                <a class="btn btn-warning btn-lg" href="http://pt-br.www.mozilla.com/pt-BR/" target="_blank">Mozilla Firefox</a>
                            </p>
                        </div>

                    <?php } elseif ($tpVal == "cok") { ?>
                        <div class="jumbotron">
                            <h1>Ops...</h1>
                            <p class="m02">Para completa funcionalidade deste site é necessário que seu navegador suporte a definição de cookies. Seu navegador atual parece não suportar ou esta funcionalidade foi desabilitada.</p>
                            <p>Por favor, habilite esta funcionalidade ou utilize um navegador com esta tecnologia. Navegadores recomendados:</p>
                            <p class="m01"><a class="btn btn-success btn-lg" href="http://www.google.com.br/chrome/" target="_blank">Google Chrome</a>
                                <a class="btn btn-warning btn-lg" href="http://pt-br.www.mozilla.com/pt-BR/" target="_blank">Mozilla Firefox</a>
                            </p>
                        </div>

                    <?php } elseif ($tpVal == "ajax") { ?>
                        <div class="jumbotron">
                            <h1>Ops...</h1>
                            <p class="m02">Para completa funcionalidade deste site é necessário que seu navegador suporte AJAX e seu navegador atual parece não 
                                suportar esta tecnologia.</p>
                            <p>Por favor, utilize um navegador com esta tecnologia. Navegadores recomendados:</p>
                            <p class="m01"><a class="btn btn-success btn-lg" href="http://www.google.com.br/chrome/" target="_blank">Google Chrome</a>
                                <a class="btn btn-warning btn-lg" href="http://pt-br.www.mozilla.com/pt-BR/" target="_blank">Mozilla Firefox</a>
                            </p>
                        </div>
                    <?php }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>
