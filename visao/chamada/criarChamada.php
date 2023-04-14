<!DOCTYPE html>
<html>
    <head>     
        <title>Criar Chamada - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTManutencaoProcesso.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        // coordenador ou administrador
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null && estaLogado(Usuario::$USUARIO_COORDENADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idProcesso'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados para processamento
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);

        // verificando se pode criar chamada
        $listaChamadas = ProcessoChamada::buscarChamadaPorProcesso($processo->getPRC_ID_PROCESSO());
        $permiteCriar = $processo->permiteCriarChamada($listaChamadas);
        if (!$permiteCriar[0]) {
            new Mensagem("$permiteCriar[1]", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar Edital</a> > <strong>Criar chamada</strong></h1>
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
                    <form class="form-horizontal" id="form" method="post" action="<?php print $CFG->rwww; ?>/controle/CTManutencaoProcesso.php?acao=criarChamada">
                        <input type="hidden" name="valido" value="ctmanutencaoprocesso">
                        <input type="hidden" id='idProcesso' name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" id='dtInicioProcesso' name="dtInicioProcesso" value="<?php print $processo->getPRC_DT_INICIO(); ?>">

                        <?php
                        // ATENÇÃO: Layout semelhante a alterarCalendarioChamada.php
                        // Ao alterar este arquivo, considere revisar alterarCalendarioChamada.php e gerenciarResultadosProcesso.php
                        // 
                        // 
                        ?>
                        <div class="calendario form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Período de Inscrição</label>
                            <div class="inputs col-xs-12 col-sm-8 col-md-8">
                                <div class="completo">
                                    <input type="text" class="form-control" name="dtInicio" id="dtInicio" size="10" maxlength="10">
                                    <div class="ate">a</div>
                                    <input type="text" class="form-control" name="dtFim" id="dtFim" size="10" maxlength="10">
                                </div>

                                <div class="completo">
                                    <label style="display: none" for="dtInicio" class="error"></label>
                                </div>

                                <div class="completo">
                                    <label style="display: none" for="dtFim" class="error"></label>
                                </div>
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-success" type="submit" value="Criar">
                                <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/processo/"; ?>manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>';" value="Voltar">
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
        carregaScript("jquery.maskedinput");
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#dtInicio").mask("99/99/9999");

            $("#dtFim").mask("99/99/9999");

            $("#form").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    dtInicio: {
                        required: true,
                        dataBR: true,
                        dataBRMaiorIgual: "#dtInicioProcesso"
                    }, dtFim: {
                        required: true,
                        dataBR: true,
                        dataBRMaiorIgual: "#dtInicio"
                    }
                }, messages: {
                    dtInicio: {
                        required: "Data de início obrigatória.",
                        dataBR: "Data de início inválida.",
                        dataBRMaiorIgual: "A data inicial do período de inscrição deve ser maior ou igual a data de abertura do Edital."
                    }, dtFim: {
                        required: "Data de finalização obrigatória.",
                        dataBR: "Data de finalização inválida.",
                        dataBRMaiorIgual: "Esta data deve ser maior ou igual a data anterior."
                    }
                }
            });
        });
    </script>
</html>

