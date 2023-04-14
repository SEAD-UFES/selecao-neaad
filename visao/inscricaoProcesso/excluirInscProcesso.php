<!DOCTYPE html>
<html>
    <head>     
        <title>Excluir Inscrição - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTUsuario.php");
        include_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idInscricao'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando inscriçao e dados para processamento
        $inscricao = buscarInscricaoComPermissaoCT($_GET['idInscricao'], getIdUsuarioLogado());
        $processo = buscarProcessoComPermissaoCT($inscricao->getPRC_ID_PROCESSO(), TRUE);
        $chamada = buscarChamadaPorIdCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getPRC_ID_PROCESSO());

        // verificando caso do periodo de inscriçao ter acabado
        // 
        $podeExcluir = validaPeriodoInscPorChamadaCT($inscricao->getPCH_ID_CHAMADA());
        // nao exibir caso o processo esteja fechado
        if (!$podeExcluir) {
            new Mensagem('Desculpe. Você não pode excluir esta inscrição, pois o período de inscrição do edital está finalizado.', Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: Editais > <a href="<?php print $CFG->rwww ?>/visao/inscricaoProcesso/listarInscProcessoUsuario.php">Minhas Inscrições</a> > <strong>Excluir</strong></h1>
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
                                        <?php print $processo->getHTMLDsEditalCompleta(); ?>
                                    </p>
                                    <p> 
                                        <i class="fa fa-user"></i> 
                                        <b>Inscrição: </b> <?php print $inscricao->getIPR_NR_ORDEM_INSC(); ?> <separador class='barra'></separador> 
                                    <b>Chamada: </b> <?php print $inscricao->PCH_DS_CHAMADA; ?> <separador class='barra'></separador>
                                    <b>Data: </b> <?php print $inscricao->getIPR_DT_INSCRICAO(); ?>
                                    </p>
                                    <?php
                                    // tem opção de inscrição?
                                    if (ProcessoChamada::temOpcaoInscricao($chamada)) {
                                        $barra = FALSE;
                                        ?>
                                        <p>
                                            <i class="fa fa-info-circle"></i>
                                            <?php
                                            // área de atuação
                                            if ($chamada->admiteAreaAtuacaoObj()) {
                                                // recuperando area
                                                $areaAtu = buscarAreaAtuChamadaPorIdCT($inscricao->getAAC_ID_AREA_CHAMADA());
                                                ?>
                                                <b>Área:</b> <?php print $areaAtu->ARC_NM_SUBAREA_CONH; ?>
                                                <?php
                                                $barra = TRUE;
                                            }

                                            // reserva de vaga
                                            if ($chamada->admiteReservaVagaObj()) {
                                                ?>
                                                <?php if ($barra) { ?>
                                                <separador class='barra'></separador>
                                            <?php } ?>
                                            <b>Vaga:</b> <?php print getDsReservaVagaInscricaoCT($inscricao->getRVC_ID_RESERVA_CHAMADA()); ?>
                                            <?php
                                            $barra = TRUE;
                                        }

                                        // polos
                                        if ($chamada->admitePoloObj()) {
                                            $polos = buscarPoloPorInscricaoCT($inscricao->getIPR_ID_INSCRICAO());
                                            $dsPolo = count($polos) == 1 ? "Polo:" : "Polos:";
                                            ?>
                                            <?php if ($barra) { ?>
                                                <separador class='barra'></separador>
                                            <?php } ?>
                                            <b><?php print $dsPolo; ?></b> <?php print arrayParaStr($polos); ?>
                                            <?php
                                            $barra = TRUE;
                                        }
                                        ?>
                                        </p>
                                    <?php }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full">
                    <div class="callout callout-warning">
                        <strong>Atenção:</strong> Esta operação é irreversível. No entanto, você poderá inscrever-se novamente, desde que dentro do prazo de inscrição do edital, recebendo um novo código de inscrição.
                    </div>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full">
                    <form id="formExcluir" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTProcesso.php?acao=excluirInscProcesso" ?>'>
                        <input type="hidden" name="valido" value="ctprocesso">
                        <input type="hidden" name="idInscricao" value="<?php print $inscricao->getIPR_ID_INSCRICAO(); ?>">

                        <fieldset class="m02">
                            <legend>Motivo da Exclusão</legend>
                            <div class="col-full">
                                <textarea class="form-control" style="width: 100%;" cols="60" rows="4" name="dsMotivo" id="dsMotivo"></textarea>
                                <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                            </div>
                        </fieldset>

                        <?php
                        require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
                        EXC_fragmentoPergExcEmPag();
                        ?>

                        <div id="divBotoes" class="col-full m02">
                            <button class="btn btn-danger" id="submeter" type="button" role="button" data-toggle="modal" data-target="#perguntaExclusao">Excluir</button>
                            <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/inscricaoProcesso/consultarInscProcesso.php?idInscricao={$inscricao->getIPR_ID_INSCRICAO()}"; ?>';">Voltar</button>
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
        carregaScript("ajax");
        ?>
    </body>
    <script type="text/javascript">

        $(document).ready(function () {

            //validando form
            $("#formExcluir").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    dsMotivo: {
                        required: true
                    }}, messages: {
                }
            }
            );

            var maxCaracter = 500;

            // criando funcao de callback para processamento de botao
            funcBtExc = function (qtFalta)
            {
                if (qtFalta < maxCaracter)
                {
                    // habilitar botao
                    $("#submeter").attr("disabled", false);
                    $("#submeter").attr("title", "");
                } else {
                    $("#submeter").attr("disabled", true);
                    $("#submeter").attr("title", "Para habilitar o botão, por favor, escreva o motivo da exclusão.");
                }
            }

            //incluindo contador para caracteres restantes
            adicionaContadorTextArea(maxCaracter, "dsMotivo", "qtCaracteres", funcBtExc);

        });
    </script>
</html>