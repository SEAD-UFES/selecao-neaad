<!DOCTYPE html>
<html>
    <head>     
        <title>Classificar Candidatos - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");

        // apenas coordenador e administrador
        if (estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL && estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        $loginRestrito = estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL;
        if ($loginRestrito) {
            $curso = buscarCursoPorCoordenadorCT(getIdUsuarioLogado());

            if ($curso == NULL) {
                new Mensagem("Você ainda não está associado a um curso.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
        }

        //verificando passagem por get
        if (!isset($_GET['idProcesso'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando processo
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww ?>/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Inscrições</a> > <strong>Classificar Candidatos</strong></h1>
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
                                        <i class='fa fa-book'></i>
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full m02">
                    <form id="form" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTNotas.php?acao=classificarCandidatos" ?>'>
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO() ?>">
                        <div class="completo">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoChamadaPorProcesso($processo->getPRC_ID_PROCESSO(), $processo->PCH_ID_ULT_CHAMADA, NULL, TRUE); ?>
                                </div>
                            </div>
                            <div id="divParamChamada" style="display: none">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Etapa:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control-static" readonly disabled type="text" id="etapa" name="etapa" style="width:100%;padding:5px;">
                                    </div>
                                </div>
                            </div>
                            <div style="display: none" id="divConfirmacao">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8" style="padding:0 15px;">
                                        <div class="callout callout-warning" style="margin:0px;">
                                            <input type="checkbox" id="ciente" name="ciente" value="<?php print FLAG_BD_SIM; ?>">
                                            <b>Confirmo que o sistema pode classificar os candidatos</b> de acordo com a nota total obtida por cada um. Sei que após essa operação, não será mais possível executar a avaliação cega das inscrições.
                                            <label for="ciente" class="error" style="display: none"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: none" id="divErro">
                                <div id="divErroInterno" class="callout callout-danger">
                                </div>
                            </div>
                            <div class="callout callout-danger" style="display: none" id="divErroEtapa">
                            </div>
                        </div>

                        <div id="divBotoes">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input disabled id="submeter" class="btn btn-success"  type="submit" value="Classificar">
                                    <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print "listarInscricaoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}" ?>'" value="Voltar">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="divMensagem" class="col-full" style="display:none">
                    <div class="alert alert-info">
                        Aguarde o processamento. Esta operação pode demorar um pouco...
                    </div>
                </div>
            </div>
        </div>  
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("jquery.price_format");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            //validando form
            $("#form").validate({
                // ignore: "",
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    idChamada: {
                        required: true
                    },
                    ciente: {
                        required: true
                    }
                }
                , messages: {
                    ciente: {
                        required: "Por favor, leia o texto e confirme sua intenção."
                    }
                }
            }
            );

            // criando gatilho para tipo
            var gatilhoTipo = function () {
                // verificando se o campo e nulo
                if ($("#idChamada").val() == "")
                {
                    // esconder divs
                    $("#divConfirmacao").hide();
                    $("#divErro").hide();
                    $("#divErroEtapa").hide();
                    $("#divParamChamada").hide();
                    $("#submeter").attr("disabled", false);

                    // nada a fazer
                    return;
                }

                // montando requisiçao para validacao de chamada
                $.ajax({
                    type: "POST",
                    url: getURLServidor() + "/controle/CTAjax.php?val=classificarCands",
                    data: {"idChamada": $("#idChamada").val()},
                    dataType: "json",
                    success: function (json) {
                        // caso erro interno
                        if (json === null || json['errInt']) {
                            $("#divConfirmacao").hide();
                            $("#divErro").hide();
                            $("#divParamChamada").hide();
                            $("#divErroEtapa").hide();

                            alert("Erro no servidor ao tentar recuperar dados.\nPor favor, informe esta ocorrência ao administrador.\n\n" + (json == null ? "" : json['msg']));
                            return;
                        }

                        // tratando casos
                        if (json['val'])
                        {
                            // tudo ok.
                            $("#divErroEtapa").hide();
                            $("#divErro").hide();
                            $("#submeter").attr("disabled", false);
                            $("#etapa").val(json['etapa']);
                            $("#divParamChamada").show();
                            $("#divConfirmacao").show();
                            return;

                        } else {
                            // selecionando erros: etapa
                            if (json['errEtapa'])
                            {
                                $("#divConfirmacao").hide();
                                $("#divErro").hide();
                                $("#divParamChamada").hide();
                                $("#submeter").attr("disabled", true);
                                $("#divErroEtapa").html(json['msg']);
                                $("#divErroEtapa").show();
                                return;
                            }

                            // outros erros
                            if (!json['val'])
                            {
                                $("#divConfirmacao").hide();
                                $("#divErroEtapa").hide();
                                $("#submeter").attr("disabled", true);
                                $("#divErroInterno").html(json['msg']);
                                $("#etapa").val(json['etapa']);
                                $("#divParamChamada").show();
                                $("#divErro").show();
                                return;
                            }
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nA página será recarregada.\n\n";
                        msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                        // exibindo mensagem e reiniciando pagina
                        alert(msg);
                        location.reload();
                        return false;
                    }
                });

            };
            $("#idChamada").change(gatilhoTipo);
            gatilhoTipo();

        });
    </script>
</html>