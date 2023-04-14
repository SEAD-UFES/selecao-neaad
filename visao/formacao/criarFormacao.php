<!DOCTYPE html>
<html>
    <head>     
        <title>Adicionar Formação - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTCurriculo.php");
        include_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // verificando permissao
        if (!permiteAlteracaoCurriculoCT(buscarIdCandPorIdUsuCT(getIdUsuarioLogado()))) {
            new Mensagem("Você não pode alterar seu currículo enquanto está concorrendo a algum edital.", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: Candidato > <a href="listarFormacao.php">Currículo</a> > <strong>Adicionar Formação</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">

                    <div id="divErCriarFormacao" style="display: none" class="alert alert-danger">
                        Você já cadastrou uma formação com os parâmetros informados. Por favor, atualize o item existente.
                    </div>

                    <form id="formCadastro" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTCurriculo.php?acao=criarFormacao" ?>'>
                        <input type="hidden" name="valido" value="ctcurriculo">

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Formação*:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span><?php impressaoTipoFormacao(); ?></span>
                            </div>
                        </div>

                        <fieldset class="m02">
                            <legend>Instituição</legend>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">País: *</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <span><?php impressaoPais(Pais::$PAIS_BRASIL, "idPais"); ?></span>
                                </div> 
                            </div>

                            <div id="paisBrasil" style="display: block">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Estado: *</label>

                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <span><?php impressaoEstado(NULL, "idEstado"); ?></span>
                                        <div id="divEsperaCidade" style="display: none">
                                            <span>Aguarde, Carregando...</span>
                                        </div>
                                    </div>
                                </div>

                                <div id="divListaCidade" style="display: none">
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Cidade: *</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <select class="form-control" name="idCidade" id="idCidade"></select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="paisOutros" style="display: none">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Cidade: *</label>

                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" type="text" id="nmCidade" name="nmCidade" size="50" maxlength="100">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome da Instituição: *</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="nmInstituicao" name="nmInstituicao" size="50" maxlength="200">
                                </div>
                            </div>
                        </fieldset>

                        <div id="divCurso" style="display: none">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Curso: *</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="nmCurso" name="nmCurso" size="50" maxlength="200">
                                </div>
                            </div>
                        </div>

                        <div id="divArea" style="display: none">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Grande área: *</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <span><?php impressaoArea(NULL, "idAreaConh"); ?></span>
                                    <div id="divEsperaSubarea" style="display: none">
                                        <span>Aguarde, Carregando...</span>
                                    </div>
                                </div>
                            </div>
                            <div id="divListaSubarea" style="display: none">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Área: *</label>

                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <select class="form-control" name="idSubareaConh" id="idSubareaConh"></select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Situação: *</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoRadioSituacaoForm(); ?>
                                <label style="display: none" class="error" for="stFormacao"></label>
                            </div>
                        </div>

                        <div id="divCargaHoraria" style="display: none">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Carga horária (hs): *</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="cargaHoraria" name="cargaHoraria" size="5" maxlength="5">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Ano de início: *</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" type="text" id="anoInicio" name="anoInicio" size="4" maxlength="4">
                            </div>
                        </div>

                        <div id="divAnoConclusao" style="display: none">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Ano de conclusão: *</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="anoConclusao" name="anoConclusao" size="4" maxlength="4">
                                </div>
                            </div>
                        </div>

                        <fieldset id="divDetalhes" style="display: none">
                            <legend>Detalhes</legend>
                            <div class="form-group">
                                <label id="lbOutros1" style="display: block" class="control-label col-xs-12 col-sm-4 col-md-4">Título do trabalho de conclusão:</label>
                                <label id="lbResidencia1" style="display: none" class="control-label col-xs-12 col-sm-4 col-md-4">Residência médica em:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="nmTituloTrabalho" name="nmTituloTrabalho" size="50" maxlength="300">
                                </div>
                            </div>

                            <div class="form-group">
                                <label id="lbOutros2" style="display: block" class="control-label col-xs-12 col-sm-4 col-md-4">Orientador do trabalho de conclusão:</label>
                                <label id="lbResidencia2" style="display: none" class="control-label col-xs-12 col-sm-4 col-md-4">Número de registro:</label>

                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type="text" id="nmOrientadorTrabalho" name="nmOrientadorTrabalho" size="50" maxlength="200">
                                </div>
                            </div>
                        </fieldset>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                <button class="btn btn-default" type="button" onclick="javascript: window.location = 'listarFormacao.php';">Voltar</button>
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
        carregaScript("additional-methods");
        carregaScript("jquery.maskedinput");
        carregaScript("ajax");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            //validando form
            $("#formCadastro").validate({
                submitHandler: function (form) {
                    // tentando validaçao de item e area
                    $("#divErCriarFormacao").hide();
                    $.ajax({
                        type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?val=formacao",
                        data: {"tpFormacao": $("#tpFormacao").val(), "nmInstituicao": $("#nmInstituicao").val(), "nmCurso": $("#nmCurso").val(), "anoInicio": $("#anoInicio").val(), "idUsuario": getIdUsuarioLogado()},
                        dataType: "json",
                        success: function (json) {
                            // caso validou
                            if (json) {
                                // processa o submit
                                // 
                                //evitar repetiçao do botao
                                mostrarMensagem();
                                form.submit();
                            } else {
                                // exibe msg de erro e aborta operaçao de submit
                                $("#divErCriarFormacao").show();
                                return false;
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nA página será recarregada.\n\n";
                            msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                            // exibindo mensagem e reiniciando pagina
                            alert(msg);
                            //location.reload();
                            return false;
                        }
                    });
                },
                rules: {
                    tpFormacao: {
                        required: true
                    },
                    idPais: {
                        required: true
                    },
                    idEstado: {
                        required: function (element) {
                            return ativaParaPaisBrasil($("#idPais").val());
                        }
                    },
                    idCidade: {
                        required: function (element) {
                            return ativaParaPaisBrasil($("#idPais").val());
                        }
                    },
                    nmCidade: {
                        required: function (element) {
                            return !ativaParaPaisBrasil($("#idPais").val());
                        }

                    },
                    nmInstituicao: {
                        required: true

                    },
                    nmCurso: {
                        required: function (element) {
                            return ativaCurso($("#tpFormacao").val());
                        }

                    },
                    idAreaConh: {
                        required: function (element) {
                            return ativaArea($("#tpFormacao").val());
                        }

                    },
                    idSubareaConh: {
                        required: function (element) {
                            return ativaArea($("#tpFormacao").val());
                        }

                    },
                    stFormacao: {
                        required: true

                    },
                    cargaHoraria: {
                        required: function (element) {
                            return ativaCargaHoraria($("#tpFormacao").val());
                        }

                    },
                    anoInicio: {
                        required: true,
                        range: [1900, (new Date()).getFullYear()]

                    },
                    anoConclusao: {
                        required: function (element) {
                            return ativaAnoConclusao($("input[name='stFormacao']:checked").val());
                        },
                        min: function (element) {
                            return $("#anoInicio").val();
                        },
                        max: (new Date()).getFullYear()
                    }}, messages: {
                    anoInicio: {
                        range: "Por favor, Informe um valor entre 1900 e " + (new Date()).getFullYear() + "."

                    },
                    anoConclusao: {
                        min: function () {
                            return "Por favor, Informe um valor maior ou igual a " + $("#anoInicio").val() + "."
                        },
                        max: "Por favor, Informe um valor menor ou igual a " + (new Date()).getFullYear() + "."
                    }
                }
            }
            );

            //criando máscaras
            $("#anoInicio").mask("9999", {placeholder: ""});
            $("#anoConclusao").mask("9999", {placeholder: ""});
            $("#cargaHoraria").mask("9?9999", {placeholder: ""});

            // pais Brasil
            function ativaParaPaisBrasil(valor) {
                return valor == "<?php print Pais::$PAIS_BRASIL ?>";
            }

            // incluindo gatilho para pais Brasil
            adicionaGatilhoAddDivSelect("idPais", ativaParaPaisBrasil, "paisBrasil", "paisOutros");

            // tratando gatilho de ajax para cidade da instituiçao
            function getParamsCidade()
            {
                return {'cargaSelect': "cidade", 'idUf': $("#idEstado").val()};
            }
            adicionaGatilhoAjaxSelect("idEstado", getIdSelectSelecione(), "divEsperaCidade", "divListaCidade", "idCidade", null, getParamsCidade);


            // tratando gatilho de ajax para subarea
            function getParamsSubarea()
            {
                return {'cargaSelect': "areaConhecimento", 'idArea': $("#idAreaConh").val()};
            }
            adicionaGatilhoAjaxSelect("idAreaConh", getIdSelectSelecione(), "divEsperaSubarea", "divListaSubarea", "idSubareaConh", null, getParamsSubarea);

            // call back que ativa exibicao de curso
            function ativaCurso(valor)
            {
                var vals = <?php print TipoCurso::getListaAdmiteCurso(); ?>;
                return vals.indexOf(valor) != -1;
            }

            // call back que ativa exibicao de carga horaria
            function ativaCargaHoraria(valor)
            {
                var vals = <?php print TipoCurso::getListaAdmiteCargaHoraria(); ?>;
                return vals.indexOf(valor) != -1;
            }

            // call back que ativa exibicao de detalhamento
            function ativaDetalhamento(valor)
            {
                var vals = <?php print TipoCurso::getListaAdmiteDetalhamento(); ?>;
                return vals.indexOf(valor) != -1;
            }

            // call back que ativa exibicao de area / subarea
            function ativaArea(valor)
            {
                var vals = <?php print TipoCurso::getListaAdmiteAreaSubarea(); ?>;
                return vals.indexOf(valor) != -1;
            }

            // funçao que estabelece descriçao do detalhamento
            function defineDescDetalhamento(valor)
            {
                var idRes = <?php print TipoCurso::getIdResidenciaMedica(); ?>;
                if (valor == idRes) {
                    $("#lbOutros1, #lbOutros2").hide();
                    $("#lbResidencia1, #lbResidencia2").show();
                } else {
                    $("#lbResidencia1, #lbResidencia2").hide();
                    $("#lbOutros1, #lbOutros2").show();

                }
            }

            // criando funçao gatilho: Trata curso, carga horaria e detalhes do curso
            var funcaoGatilhoTpFormacao = function () {
                var valor = $("#tpFormacao").val();
                // trata curso
                if (ativaCurso(valor))
                {
                    $("#divCurso").show();
                } else {
                    $("#divCurso").hide();
                }

                // trata area / subarea
                if (ativaArea(valor))
                {
                    $("#divArea").show();
                } else {
                    $("#divArea").hide();
                }

                // trata carga horaria
                if (ativaCargaHoraria(valor))
                {
                    $("#divCargaHoraria").show();
                } else {
                    $("#divCargaHoraria").hide();
                }

                // trata detalhes
                if (ativaDetalhamento(valor))
                {
                    defineDescDetalhamento(valor);
                    $("#divDetalhes").show();
                } else {
                    $("#divDetalhes").hide();
                }

            };

            // Incluindo gatilho para tpFormacao: Trata curso, carga horaria e detalhes do curso
            $("#tpFormacao").change(funcaoGatilhoTpFormacao);


            // tratando exibiçao de ano de conclusao
            function ativaAnoConclusao(valor)
            {
                var conc = '<?php print FormacaoAcademica::$ST_FORMACAO_COMPLETO ?>';
                return valor == conc;
            }
            funcaoGatilhoMostraAnoConc = function ()
            {
                if (ativaAnoConclusao($("input[name='stFormacao']:checked").val()))
                {
                    $("#divAnoConclusao").show();
                } else {
                    $("#divAnoConclusao").hide();
                }

            };

            $("input[name='stFormacao']").change(funcaoGatilhoMostraAnoConc);

            // invocando funçoes de ajuste
            funcaoGatilhoTpFormacao();
            funcaoGatilhoMostraAnoConc();
        });
    </script>
</html>

