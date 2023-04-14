<!DOCTYPE html>
<html>
    <head>      
        <title>Cadastro Comunidade Externa - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        include_once ($CFG->rpasta . "/util/sessao.php");
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

                <div id="breadcrumb">
                    <h1>Cadastro</h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="completo m02">
                    <form id="formCadastro" class="form-horizontal" method="post" action='<?php print $CFG->rwww . "/controle/CTCandidato.php?acao=criarCandidato" ?>' >
                        <input type="hidden" name="valido" value="ctcandidato">

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dsEmail">E-mail:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control tudo-minusculo" type="text" id="dsEmail" name="dsEmail" size="30" maxlength="100" placeholder="E-mail">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dsEmailRep">Repita o e-mail:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control tudo-minusculo" type="text" id="dsEmailRep" name="dsEmailRep" size="30" maxlength="100" placeholder="Repita o e-mail">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dsSenha">Senha:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control tudo-normal" type="password" id="dsSenha" name="dsSenha" size="20" maxlength="30" placeholder="Senha">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dsSenhaRep">Repita a senha:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control tudo-normal" type="password" id="dsSenhaRep" name="dsSenhaRep" size="20" maxlength="30" placeholder="Repita a senha">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dsNome">Nome completo:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dsNome" type="text" id="dsNome" size="40" maxlength="100" placeholder="Nome completo">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="dtNascimento">Data de nascimento:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="dtNascimento" class="form-control" name="dtNascimento" type="text" size="20" maxlength="10" placeholder="Data de nascimento">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4" for="nrCPF">CPF:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="nrCPF" type="text" id="nrCPF" size="20" maxlength="14" placeholder="CPF">
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4"><br></div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php echo "$CFG->rwww/inicio"; ?>';">Voltar</button>
                            </div>
                        </div>
                        <div id="divMensagem" style="display: none;">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                        <br/> 
                    </form>	
                </div>

            </div>  
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript('metodos-adicionaisBR');
        carregaScript('jquery.maskedinput');
        ?>
    </body>

    <script type = "text/javascript">
        $(document).ready(function () {
            $("#formCadastro").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                    form.submit();
                },
                rules: {
                    dsEmail: {
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
                    }, dsNome: {
                        required: true,
                        minlength: 3,
                        nomeCompleto: true

                    }, dtNascimento: {
                        required: true,
                        dataBR: true,
                        dataBRMenor: new Date()
                    }, nrCPF: {
                        required: true,
                        CPF: true,
                        remote: {
                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=CPFCadastro",
                            type: "post"
                        }

                    }}, messages: {
                    dsEmail: {
                        emailUfes: "Informe um email válido.",
                        remote: "Email já cadastrado."
                    },
                    dsEmailRep: {
                        emailUfes: "Informe um email válido.",
                        equalTo: "Informe um email igual ao anterior"
                    },
                    dsSenhaRep: {
                        equalTo: "Informe uma senha igual a anterior"
                    },
                    dtNascimento: {
                        dataBRMenor: "Data de nascimento deve ser menor que a data atual."
                    }, nrCPF: {
                        remote: "CPF já cadastrado."
                    }
                }
            }
            );

            //adicionando máscaras
            $("#dtNascimento").mask("99/99/9999");
            $("#nrCPF").mask("999.999.999-99");

            //bloqueando copiar colar
            bloquearCopiarColar("dsEmailRep");
            bloquearCopiarColar("dsSenhaRep");
        });
    </script>
</html>

