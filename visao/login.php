<!DOCTYPE html>
<html>
    <head>
        <title>Acesso - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta content="Sistema Seleção EAD - SEAD/UFES." name="description">

        <?php
        require_once '../config.php';
        global $CFG;
        ?>

        <?php
        include_once ($CFG->rpasta . "/util/sessao.php");
        //verificando se está logado
        if (estaLogado() != null) {
            //redirecionando para página principal
            header("Location: $CFG->rwww/inicio");
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
                <div class="row m02">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h3 class="sublinhado">Acesso</h3>
                        <div class="p15">
                            <?php include ($CFG->rpasta . "/include/fragmentoAcesso.php"); ?>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 col-xs-12 m02-mob">
                        <h3 class="sublinhado">Instruções para acesso</h3>
                        <div class="p15">
                            <h4>Servidor ou Aluno da UFES</h4>
                            Utilize sua <a title='Identificação Única' id="link_login_unico" target="_blank" href='http://senha.ufes.br'>Identificação Única</a> para acessar o sistema.

                            <h4 class="m02">Comunidade Externa</h4>
                            Acesse com seu email e senha cadastrados ou <a title='Cadastrar usuário' href="<?php print $CFG->rwww ?>/cadastre-se">realize o cadastro</a>, caso ainda não possua.

                            <h4 class="m02">Fale Conosco</h4>
                            <a href="<?php print $CFG->rwww . "/contato" ?>">Formulário de contato <i class="fa fa-external-link"></i></a><br>
                            Telefone: (27) 4009-2208
                        </div>
                    </div>
                </div>
                <?php include ($CFG->rpasta . "/include/noticia.php"); ?>
            </div>
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript('metodos-adicionaisBR');
        ?>
    </body>
</html>
