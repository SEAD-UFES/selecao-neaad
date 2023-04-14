<!DOCTYPE html>
<html>
    <head>     
        <title>Seleção EAD - Cadastrar Novo Curso</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");

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
                    <h1>Você está em: Cadastros > <a href="<?php print $CFG->rwww; ?>/visao/curso/listarCurso.php">Curso</a> > <strong>Cadastrar</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">
                    <form class="form-horizontal" id="formCadastro" method="post" action="<?php print $CFG->rwww; ?>/controle/CTCurso.php?acao=criarCurso">
                        <input type="hidden" name="valido" value="ctcurso">
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="nmCurso" type="text" id="nmCurso" size="30" maxlength="30" placeholder="Nome do Curso" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo de Curso: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoTipoCurso() ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Departamento: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoDepartamento() ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Coordenador:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoUsuario(NULL, NGUtil::getSITUACAO_ATIVO(), Usuario::$USUARIO_COORDENADOR); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Grande área: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span><?php impressaoArea(NULL, "idAreaConh"); ?></span>
                                <div id="divEsperaSubarea" style="display: none">
                                    <span>Aguarde, Carregando...</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" id="divListaSubarea" style="display: none">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Área: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <select class="form-control" name="idSubareaConh" id="idSubareaConh"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome extenso: * <i title="Este nome será utilizado para emissão de relatórios. Siga o modelo: curso de licenciatura em Artes Visuais - EAD" class="fa fa-question-circle"></i></label>

                            <div class="controls col-xs-12 col-sm-8 col-md-8" style="display:block">
                                <textarea class="form-control" cols="60" rows="4" name="dsCurso" id="dsCurso"></textarea>
                                <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                            </div>
                        </div>
                        <div id="divBotoes">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                    <input type="button" class="btn btn-default" onclick="javascript: window.location = 'listarCurso.php';" value="Voltar">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="divMensagem" class="col-full" style="display:none">
                    <div class="alert alert-info">
                        Aguarde o processamento...
                    </div>
                </div>
            </div>
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("ajax");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#formCadastro").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
//                    $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                    form.submit();
                },
                rules: {
                    nmCurso: {
                        required: true,
                        minlength: 3,
                        remote: {
                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=nomeCurso",
                            type: "post"
                        }
                    }, tpCurso: {
                        required: true
                    }, idDepartamento: {
                        required: true
                    }, idAreaConh: {
                        required: true
                    },
                    idSubareaConh: {
                        required: true
                    }
                    , dsCurso: {
                        required: true,
                        maxlength: 250
                    }}, messages: {
                }
            }
            );

            //incluindo contador para caracteres restantes
            adicionaContadorTextArea(250, "dsCurso", "qtCaracteres");


            // tratando gatilho de ajax para subarea
            function getParamsSubarea()
            {
                return {'cargaSelect': "areaConhecimento", 'idArea': $("#idAreaConh").val()};
            }
            adicionaGatilhoAjaxSelect("idAreaConh", getIdSelectSelecione(), "divEsperaSubarea", "divListaSubarea", "idSubareaConh", null, getParamsSubarea);


        });
    </script>
</html>

