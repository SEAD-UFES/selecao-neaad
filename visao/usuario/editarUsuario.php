<!DOCTYPE html>
<html>
    <head>  
        <title>Editar Usuário - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        //verificando passagem por get
        if (!isset($_GET['idUsuario'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        //recuperando usuário
        require_once ($CFG->rpasta . "/controle/CTUsuario.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        $idUsuario = $_GET['idUsuario'];
        $objUsuario = buscarUsuarioPorIdCT($idUsuario);

        $vinculoNenhum = $objUsuario->getUSR_TP_VINCULO_UFES() == Usuario::$VINCULO_NENHUM;
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
                    <h1>Você está em: Cadastros > <a href="<?php print $CFG->rwww; ?>/visao/usuario/listarUsuario.php">Usuário</a> > <strong>Editar Usuário</strong></h1>
                </div>

                <div class="col-full m02">
                    <div class="panel-group ficha-tecnica" id="accordion">
                        <div class="painel">
                            <div class="panel-heading">
                                <a style="text-decoration:none;" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                    <h4 class="panel-title">Ficha Técnica</h4>
                                </a>
                            </div>

                            <div id="collapseOne" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <p>
                                        <i class="fa fa-user"></i>
                                        <b>Nome:</b> <?php print $objUsuario->getUSR_DS_NOME(); ?> <separador class='barra'></separador> 
                                    <b>Tipo:</b> <?php print Usuario::getDsTipo($objUsuario->getUSR_TP_USUARIO()); ?> <separador class='barra'></separador> 
                                    <b>Login:</b> <?php print $objUsuario->getUSR_DS_LOGIN(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                // verificando se e o login atual
                if ($objUsuario->getUSR_ID_USUARIO() == getIdUsuarioLogado()) {
                    ?>
                    <div class="col-full">
                        <div class="callout callout-warning">
                            <b>Atenção:</b> Você está alterando os dados de seu acesso!
                        </div>
                    </div>
                <?php }
                ?>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">
                    <form class='form-horizontal' id="formCadastro" method="post" action="<?php print $CFG->rwww ?>/controle/CTUsuario.php?acao=editarUsuario">
                        <input type="hidden" name="valido" value="ctusuario">
                        <input type="hidden" name="idUsuario" value="<?php print $objUsuario->getUSR_ID_USUARIO(); ?>">

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome completo: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dsNome" type="text" id="dsNome" size="40" maxlength="100" value="<?php print $objUsuario->getUSR_DS_NOME() ?>">
                            </div>
                        </div>

                        <div id="divCoordenador" class="form-group" style="display: <?php $objUsuario->getUSR_TP_USUARIO() == Usuario::$USUARIO_COORDENADOR ? print "block" : print "none"; ?>">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Curso que coordena:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php
                                // recuperando curso 
                                $curso = buscarCursoPorCoordenadorCT($objUsuario->getUSR_ID_USUARIO());
                                impressaoCurso($curso != NULL ? $curso->getCUR_ID_CURSO() : NULL);
                                ?>
                            </div>
                        </div>

                        <div id="divAvaliador" class="form-group"  style="display: <?php $objUsuario->getUSR_TP_USUARIO() == Usuario::$USUARIO_AVALIADOR ? print "block" : print "none"; ?>">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Curso que avalia:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php
                                impressaoCurso($objUsuario->getUSR_ID_CUR_AVALIADOR(), NULL, NULL, "idCursoAvaliador");
                                ?>
                            </div> 
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Email: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control tudo-minusculo"  <?php !$vinculoNenhum ? print "disabled" : ""; ?> type="text" id="dsEmail" name="dsEmail" size="30" maxlength="100" value="<?php print $objUsuario->getUSR_DS_EMAIL(); ?>">
                            </div>
                        </div>

                        <div id="divCandidato" style="display: <?php $objUsuario->getUSR_TP_USUARIO() == Usuario::$USUARIO_CANDIDATO ? print "block" : print "none"; ?>">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Email Alternativo:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control tudo-minusculo" type="text" id="dsEmailAlternativo" name="dsEmailAlternativo" size="30" maxlength="100" value="<?php print $objUsuario->getEmailAlternativo(); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class='control-label col-xs-12 col-sm-4 col-md-4'>CPF: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="nrCPF" type="text" id="nrCPF" size="14" maxlength="14" value='<?php print $objUsuario->getNrCpfMascarado(); ?>'>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class='control-label col-xs-12 col-sm-4 col-md-4'>Data de Nascimento:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="dtNascimento" type="text" id="dtNascimento" size="10" maxlength="10" value='<?php print $objUsuario->getDtNascimento(); ?>'>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo de Vínculo com a UFES: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8 titulo-alinhar">
                                <span id='tpVinculoUfes' class="input uneditable-input"><?php print Usuario::getDsVinculoUFES($objUsuario->getUSR_TP_VINCULO_UFES()); ?></span>
                            </div>
                        </div>

                        <?php
                        // incluindo opçao de desvincular login
                        if (!$vinculoNenhum) {
                            ?>
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Desvincular Login da UFES:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" value="<?php print FLAG_BD_SIM ?>" id="desvincular" name="desvincular">
                                    </label>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Situação:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoRadioAtivoInativo($objUsuario->getUSR_ST_SITUACAO()); ?>
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class='btn btn-success' id="submeter" type="submit" value="Salvar">
                                <input class='btn btn-default' type="button" onclick="javascript: window.location = 'listarUsuario.php';" value="Voltar">
                            </div>
                        </div>
                    </form>
                </div>

                <div id="divMensagem" class="col-full" style="display: none;">
                    <div class="alert alert-info">
                        Aguarde o processamento...
                    </div>
                </div>
            </div>
        </div>  
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript('metodos-adicionaisBR');
        carregaScript('jquery.maskedinput');
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            $("#formCadastro").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                    $("input.tudo-minusculo").lowercase();
                    form.submit();
                },
                rules: {
                    tpUsuario: {
                        required: true
                    },
                    dsNome: {
                        required: true,
                        minlength: 3,
                        nomeCompleto: true

                    }, dsEmailAlternativo: {
                        emailUfes: true,
                        remote: {
                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=emailAlternativo&idUsuario=<?php print $objUsuario->getUSR_ID_USUARIO(); ?>",
                                                    type: "post"
                                                }

                                            }, nrCPF: {
                                                required: function (element) {
                                                    return ativaParaCandidato('<?php print $objUsuario->getUSR_TP_USUARIO(); ?>');
                                                },
                                                CPF: true,
                                                remote: {
                                                    url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=CPFCadastro&idUsuario=<?php print $objUsuario->getUSR_ID_USUARIO(); ?>",
                                                                            type: "post"
                                                                        }

                                                                    }, dtNascimento: {
                                                                        required: function (element) {
                                                                            return ativaParaCandidato('<?php print $objUsuario->getUSR_TP_USUARIO(); ?>');
                                                                        },
                                                                        dataBR: true,
                                                                        dataBRMenor: new Date()
                                                                    }, dsEmail: {
                                                                        required: true,
                                                                        emailUfes: true,
                                                                        remote: {
                                                                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=emailCadastro&idUsuario=<?php print $objUsuario->getUSR_ID_USUARIO(); ?>",
                                                                                                    type: "post"
                                                                                                }
                                                                                            }}, messages: {
                                                                                            dsEmailAlternativo: {
                                                                                                remote: "Email já cadastrado.",
                                                                                                emailUfes: "Informe um email válido."
                                                                                            }, nrCPF: {
                                                                                                remote: "CPF já cadastrado."
                                                                                            }, dtNascimento: {
                                                                                                dataBR: "Informe uma data válida",
                                                                                                dataBRMenor: "Data de nascimento deve ser menor que a data atual."
                                                                                            }, dsEmail: {
                                                                                                emailUfes: "Informe um email válido.",
                                                                                                remote: "Email já cadastrado."
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    );
                                                                                    //adicionando máscaras
                                                                                    $("#nrCPF").mask("999.999.999-99");
                                                                                    $("#dtNascimento").mask("99/99/9999");

                                                                                    // ativar para candidato
                                                                                    function ativaParaCandidato(valor) {
                                                                                        return valor == '<?php print Usuario::$USUARIO_CANDIDATO; ?>';
                                                                                    }

                                                                                    // ativar para coordenador
                                                                                    function ativaParaCoordenador(valor) {
                                                                                        return valor == '<?php print Usuario::$USUARIO_COORDENADOR; ?>';
                                                                                    }

                                                                                    // ativar para avaliador
                                                                                    function ativaParaAvaliador(valor) {
                                                                                        return valor == '<?php print Usuario::$USUARIO_AVALIADOR; ?>';
                                                                                    }

<?php if (!$vinculoNenhum) { ?>
                                                                                        // resposta visual para desvinculaçao
                                                                                        $("#desvincular").change(function () {
                                                                                            if ($("#desvincular").is(":checked")) {
                                                                                                // trocar vinculo para nenhum
                                                                                                $("#tpVinculoUfes").html('<?php print Usuario::getDsVinculoUFES(Usuario::$VINCULO_NENHUM); ?>');
                                                                                                // rebatendo email alternativo
                                                                                                $("#dsEmail").val($("#dsEmailAlternativo").val());

                                                                                                // login como email
                                                                                                $("#dsEmail").attr("disabled", false);
                                                                                            } else {
                                                                                                // restaurar vinculo
                                                                                                $("#tpVinculoUfes").html('<?php print Usuario::getDsVinculoUFES($objUsuario->getUSR_TP_VINCULO_UFES()); ?>');

                                                                                                // desabilitando
                                                                                                $("#dsEmail").val('<?php print $objUsuario->getUSR_DS_EMAIL() ?>');
                                                                                                $("#dsEmail").attr("disabled", true);
                                                                                            }
                                                                                        });

<?php } ?>
                                                                                });
    </script>
</html>

