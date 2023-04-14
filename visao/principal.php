<!DOCTYPE html>
<html>
    <head>
        <title>Início - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        if (estaLogado() == null) {
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

                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div id="breadcrumb">
                            <h1><?php print getPrimeiroNmUsuarioLogado(); ?>, bem-vindo ao sistema de seleção dos cursos da SEAD - UFES</h1>
                        </div>
                    </div>
                </div>

                <?php if (estaLogado(Usuario::$USUARIO_CANDIDATO)) {
                    ?>

                    <!--                    <div class="completo callout callout-info">  #TP01 TESTE DE PROPOSTA: Para Estevão ver // Ele invalida o tutorial 
                                            <p><strong>Novos resultados!</strong> Confira abaixo os resultados pertinentes a suas inscrições:</p>
                                            <ul>
                                                <li><a href="#">406/2015 | Tutor a Distância | Moodle para tutores</a></li>
                                                <li><a href="#">405/2015 | Aluno | Moodle para tutores</a></li>
                                            </ul>
                                        </div>-->

                    <?php include ($CFG->rpasta . "/include/tutorial.php"); ?>
                    <?php include ($CFG->rpasta . "/visao/candidato/fragmentoPainelCandidato.php"); ?>

                <?php } elseif (estaLogado(Usuario::$USUARIO_ADMINISTRADOR)) {
                    ?>
                    <?php include ($CFG->rpasta . "/visao/usuario/fragmentoPainelAdministrador.php"); ?>
                    <?php
                } elseif (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
                    ?>
                    <?php include ($CFG->rpasta . "/visao/usuario/fragmentoPainelCoordenador.php"); ?>
                <?php } elseif (estaLogado(Usuario::$USUARIO_AVALIADOR)) {
                    ?>
                    <?php include ($CFG->rpasta . "/visao/usuario/fragmentoPainelAvaliador.php"); ?>
                <?php }
                ?>
                <?php include ($CFG->rpasta . "/include/noticia.php"); ?>                     
            </div>
        </div>

        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            function sucIdentificacao() {
                $().toastmessage('showToast', {
                    text: '<b>Identificação atualizada com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucEndereco() {
                $().toastmessage('showToast', {
                    text: '<b>Endereço atualizado com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucContato() {
                $().toastmessage('showToast', {
                    text: '<b>Contatos atualizados com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function conversaoLU() {
                $().toastmessage('showToast', {
                    text: '<b>Seu cadastro antigo, como comunidade externa, foi convertido em login único.</b> A partir de agora, utilize seu login único para acessar o sistema.',
                    sticky: true,
                    type: 'warning',
                    position: 'top-right'
                });
            }


<?php
if (isset($_GET[Mensagem::$TOAST_VAR_GET])) {
    print $_GET[Mensagem::$TOAST_VAR_GET] . "();";
}
?>
        });
    </script>
</html>
