<!DOCTYPE html>
<html>
    <head>  
        <title>Cadastrar Usuário - Seleção EAD</title>
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
        require_once ($CFG->rpasta . "/util/selects.php");
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
                    <h1>Você está em: Cadastros > <a href="<?php print $CFG->rwww; ?>/visao/usuario/listarUsuario.php">Usuário</a> > <strong>Cadastrar</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">
                    <form class='form-horizontal' id="formCadastro" method="post" action="<?php print $CFG->rwww ?>/controle/CTUsuario.php?acao=criarUsuarioAdmin">
                        <input type="hidden" name="valido" value="ctusuario">
                        <div class="form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoTipoUsuario(); ?>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome completo: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dsNome" type="text" id="dsNome" size="30" maxlength="100" required>
                            </div>
                        </div>

                        <div class=" form-group " id="divCoordenador" style="display: none">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Curso que coordena:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php
                                impressaoCurso();
                                ?>
                            </div> 
                        </div>


                        <div class=" form-group" id="divAvaliador" style="display: none">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Curso que avalia:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php
                                impressaoCurso(NULL, NULL, NULL, "idCursoAvaliador");
                                ?>
                            </div> 
                        </div>

                        <div id="divCandidato" style="display: none">
                            <div class=" form-group ">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Email Alternativo:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control tudo-minusculo"  type="text" id="dsEmailAlternativo" name="dsEmailAlternativo" size="30" maxlength="100" value="">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class='control-label col-xs-12 col-sm-4 col-md-4'>CPF: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="nrCPF" type="text" id="nrCPF" size="14" maxlength="14" value=''>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class='control-label col-xs-12 col-sm-4 col-md-4'>Data de Nascimento: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="dtNascimento" type="text" id="dtNascimento" size="10" maxlength="10" value=''>
                                </div>
                            </div>
                        </div>

                        <div class=" form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Email: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control tudo-minusculo" type="text" id="dsEmail" name="dsEmail" size="30" maxlength="100" required>
                            </div>
                        </div>

                        <div class=" form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Repita o email: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control tudo-minusculo" type="text" id="dsEmailRep" name="dsEmailRep" size="30" maxlength="100" required>
                            </div>
                        </div>

                        <div class=" form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Senha: *</label>
                            <div class="col-sm-3">
                                <input class="form-control tudo-normal" type="password" id="dsSenha" name="dsSenha" size="30" maxlength="30" required>
                            </div>
                        </div>

                        <div class=" form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Repita a senha: *</label>
                            <div class="col-sm-3">
                                <input class='form-control tudo-normal' type="password" id="dsSenhaRep" name="dsSenhaRep" size="30" maxlength="30" required>
                            </div>
                        </div>

                        <div class=" form-group ">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Forçar Troca de Senha:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <label class="checkbox-inline">
                                    <input type="checkbox" value="<?php print FLAG_BD_SIM ?>" id="forcarTroca" name="forcarTroca" checked>
                                </label>
                            </div>
                        </div>

                        <div class=" form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Situação:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoRadioAtivoInativo(NGUtil::getSITUACAO_ATIVO()); ?>
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
                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=emailAlternativo",
                            type: "post"
                        }

                    }, nrCPF: {
                        required: function (element) {
                            return ativaParaCandidato($("#tpUsuario").val());
                        },
                        CPF: true,
                        remote: {
                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=CPFCadastro",
                            type: "post"
                        }

                    }, dtNascimento: {
                        required: function (element) {
                            return ativaParaCandidato($("#tpUsuario").val());
                        },
                        dataBR: true,
                        dataBRMenor: new Date()
                    }, dsEmail: {
                        required: true,
                        emailUfes: true,
                        remote: {
                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=emailCadastro",
                            type: "post"
                        }
                    },
                    dsEmailRep: {
                        required: true,
                        emailUfes: true,
                        equalTo: "#dsEmail"
                    },
                    dsSenha: {
                        required: true,
                        minlength: 6
                    },
                    dsSenhaRep: {
                        required: true,
                        minlength: 6,
                        equalTo: "#dsSenha"
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
                    },
                    dsEmailRep: {
                        emailUfes: "Informe um email válido.",
                        equalTo: "Informe um email igual ao anterior"
                    },
                    dsSenhaRep: {
                        equalTo: "Informe uma senha igual a anterior"
                    }
                }
            }
            );
            //adicionando máscaras
            $("#nrCPF").mask("999.999.999-99");
            $("#dtNascimento").mask("99/99/9999");


            //bloqueando copiar colar
            bloquearCopiarColar("dsEmailRep");
            bloquearCopiarColar("dsSenhaRep");


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

            //adicionando controle para tipo
            $("#tpUsuario").change(function () {
                if (ativaParaCandidato($("#tpUsuario").val()))
                {
                    $("#divCoordenador").hide();
                    $("#divAvaliador").hide();
                    $("#divCandidato").show();
                }
                else if (ativaParaCoordenador($("#tpUsuario").val())) {
                    $("#divCandidato").hide();
                    $("#divAvaliador").hide();
                    $("#divCoordenador").show();
                } else if (ativaParaAvaliador($("#tpUsuario").val())) {
                    $("#divCandidato").hide();
                    $("#divCoordenador").hide();
                    $("#divAvaliador").show();
                } else {
                    $("#divCandidato").hide();
                    $("#divCoordenador").hide();
                    $("#divAvaliador").hide();
                }
            });
        });
    </script>
</html>

