<!DOCTYPE html>
<html>
    <head>     
        <title>Exportar Dados - Seleção EAD</title>
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
        $opcaoExportacao = isset($_GET['rec']) && $_GET['rec'] == 'true' ? Processo::$EXP_RECURSO : NULL;

        // recuperando chamada aberta
        $listaChamadas = buscarChamadaPorProcessoCT($processo->getPRC_ID_PROCESSO());
        $temp = $listaChamadas[count($listaChamadas) - 1];
        $idsChamadas = array();
        foreach ($listaChamadas as $chamada) {
            $idsChamadas [] = $chamada->getPCH_ID_CHAMADA();
        }
        $idChamadaAberta = !isset($_GET['idChamada']) || in_array($_GET['idChamada'], $idsChamadas) === FALSE ? $temp->getPCH_ID_CHAMADA() : $_GET['idChamada'];
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww ?>/visao/processo/fluxoProcesso.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Fluxo</a> > <strong>Exportar Dados</strong></h1>
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

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">
                    <form id="form" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTProcesso.php?acao=exportarDados" ?>'>
                        <input type="hidden" name="valido" value="ctprocesso">
                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO() ?>">
                        <div class="completo">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Exportar: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoTipoExpProcesso($opcaoExportacao); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Chamada: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoChamadaPorProcesso($processo->getPRC_ID_PROCESSO(), $idChamadaAberta, NULL, TRUE); ?>
                                </div>
                            </div>

                            <div id="divEtapaChamada" style="display: none">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Etapa:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <?php impressaoEtapaAvalPorProc($processo->getPRC_ID_PROCESSO(), FALSE, NULL); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="callout callout-danger" style="display: none" id="divErro">
                            </div>

                        </div>

                        <div id="divBotoes">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input id="submeter" class="btn btn-success" type="submit" value="Exportar">
                                    <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/processo/fluxoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idChamada=$idChamadaAberta" ?>'" value="Voltar">
                                </div>
                            </div>
                        </div>
                        <div id="divMensagem" class="col-full" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                    </form>
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

            // incluindo gatilho para exibição da etapa
            function exibeEtapa(tpExportacao)
            {
                return tpExportacao !== '<?php echo Processo::$EXP_DADO_GERAL; ?>';
            }
            var gat = adicionaGatilhoAddDivSelect("idTipoExportacao", exibeEtapa, "divEtapaChamada");
            gat();

            // tratando mudanças em selects
            $("select").change(function () {
                $("#divErro").hide();
                $("#submeter").attr("disabled", false);
            });


            //validando form
            $("#form").validate({
                submitHandler: function (form) {
                    mostrarMensagem();

                    // realizando processamento
                    $.ajax({
                        type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?val=exportacao",
                        data: {"idProcesso": "<?php echo $processo->getPRC_ID_PROCESSO(); ?>", "idTipoExportacao": $("#idTipoExportacao").val(), "idChamada": $("#idChamada").val(), "idEtapaAval": $("#idEtapaAval").val()},
                        dataType: "json",
                        success: function (json) {
                            mostrarBotoes();
                            // tratando erros
                            if (json['val'])
                            {
                                // tudo ok. Prosseguindo com o post
                                $("#submeter").attr("disabled", true);
                                form.submit();
                            } else {
                                // exibindo erro...
                                $("#submeter").attr("disabled", true);
                                $("#divErro").html(json['msg']);
                                $("#divErro").show();
                                return false;
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            mostrarBotoes();
                            var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nA página será recarregada.\n\n";
                            msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                            // exibindo mensagem e reiniciando pagina
                            alert(msg);
                            location.reload();
                            return false;
                        }
                    });
                },
                rules: {
                    idChamada: {
                        required: true
                    },
                    idTipoExportacao: {
                        required: true
                    }
                }
                , messages: {
                }
            }
            );
        });
    </script>
</html>