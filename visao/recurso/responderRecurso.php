<!DOCTYPE html>
<html>
    <head>     
        <title>Responder Recurso - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");
        require_once ($CFG->rpasta . "/negocio/TipoCargo.php");


        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idRecurso']) || !isset($_GET['idInscricao'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // tentando recuperar inscricao e dados para apresentação
        $inscricao = buscarInscricaoComPermissaoCT($_GET['idInscricao'], getIdUsuarioLogado());
        $processo = buscarProcessoPorIdCT($inscricao->getPRC_ID_PROCESSO());
        $chamada = buscarChamadaPorIdCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getPRC_ID_PROCESSO());

        // tentando recuperar recurso 
        $recurso = buscarRecursoPorIdCT($_GET['idRecurso'], $_GET['idInscricao']);

        // verificando se pode responder
        if (!$recurso->permiteResponder()) {
            new Mensagem("Você já respondeu este recurso.", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href = "<?php print $CFG->rwww ?>/visao/recurso/listarRecursoProcesso.php?idProcesso=<?php print $inscricao->getPRC_ID_PROCESSO(); ?>">Recursos</a> > <strong>Responder</strong></h1>
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
                                        <i class="fa fa-book"></i>
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                    <p>
                                        <i class="fa fa-user"></i>
                                        <b>Candidato:</b> <?php print $inscricao->USR_DS_NOME_CDT; ?> <separador class='barra'></separador> 
                                    <b>Inscrição:</b> <?php print $inscricao->getIPR_NR_ORDEM_INSC(); ?> <separador class='barra'></separador> 
                                    <b>Chamada:</b> <?php print $inscricao->PCH_DS_CHAMADA; ?> <separador class='barra'></separador>
                                    <b>Data:</b> <?php print $inscricao->getIPR_DT_INSCRICAO(); ?>
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

                                        // exibindo localizacao valida ou nao
                                        if (!Util::vazioNulo($inscricao->getIPR_LOCALIZACAO_VALIDA())) {
                                            // buscando cidade do candidato
                                            $endRes = buscarEnderecoCandPorIdCandCT($inscricao->getCDT_ID_CANDIDATO(), Endereco::$TIPO_RESIDENCIAL);
                                            $descDomicilo = NGUtil::getDsSimNao($inscricao->getIPR_LOCALIZACAO_VALIDA()) . " ({$endRes->getNomeCidade()})";
                                            ?>
                                            <separador class='barra'></separador>
                                            <b>Domicílio Próximo:</b> <?php print $descDomicilo; ?>
                                            <?php
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
                    <form id="formRecurso" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTNotas.php?acao=responderRecurso" ?>'>
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" name="idInscricao" value="<?php print $inscricao->getIPR_ID_INSCRICAO(); ?>">
                        <input type="hidden" name="idChamada" value="<?php print $inscricao->getPCH_ID_CHAMADA(); ?>">
                        <input type="hidden" name="idProcesso" value="<?php print $inscricao->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" name="idRecurso" value="<?php print $recurso->getRRP_ID_RECURSO(); ?>">

                        <div class="callout callout-danger">
                            <strong>Muita atenção</strong> ao responder o recurso, pois <b>NÃO</b> será possível alterar a resposta.
                        </div>

                        <fieldset class="m02">
                            <legend>Recurso contra o resultado da <?php print $recurso->ESP_DS_ETAPA_SEL; ?> (Recurso <?php echo $recurso->getRRP_ID_RECURSO() ?>)</legend>
                            <div class="col-full">
                                <div class="form-group">
                                    <label>Motivo:</label>
                                    <?php impressaoTipoRecurso($recurso->getRRP_TP_MOTIVO(), TRUE); ?>
                                </div>
                                <div class="form-group" id="divOutros" style="display: none">
                                    <label>Descrição do Motivo:</label>
                                    <input class="form-control" disabled type="text" id="dsMotivoOutros" name="dsMotivoOutros" size="100" maxlength="100" value="<?php print $recurso->getDsMotivo(); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Justificativa:</label>
                                    <textarea class="form-control" disabled id="dsJustificativa" cols="60" rows="10" style="width: 100%" name="dsJustificativa"><?php print $recurso->getRRP_DS_JUSTIFICATIVA(); ?></textarea>
                                    <div id="contador" class="totalCaracteres">caracteres restantes</div>     
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="m02">
                            <legend>Análise do Recurso</legend>
                            <?php print Util::$MSG_CAMPO_OBRIG; ?>

                            <div class="col-full m01">
                                <div class="form-group">
                                    <label>Situação: *</label>
                                    <?php impressaoSitRecursoDefInd(); ?>
                                </div>
                                <div class="form-group">
                                    <label>Descrição da Análise: *</label>
                                    <textarea class="form-control" id="dsAnalise" cols="60" rows="10" style="width: 100%;" name="dsAnalise"></textarea>
                                    <div id="contadorAnalise" class="totalCaracteres">caracteres restantes</div>      
                                </div>
                                <label title="Enviar email para o candidato com o resultado do recurso">
                                    <input type="checkbox" id="enviarEmail" name="enviarEmail" checked value="<?php print FLAG_BD_SIM ?>"> Informar ao candidato via email
                                </label>
                            </div>
                        </fieldset>

                        <div id="divBotoes" class="col-full m01">
                            <input id="submeter" class="btn btn-success" type="submit" value="Responder">
                            <input class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print $CFG->rwww ?>/visao/recurso/listarRecursoProcesso.php?idProcesso=<?php print $inscricao->getPRC_ID_PROCESSO(); ?>';" value="Voltar">
                        </div>

                        <div id="divMensagem" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                    </form>
                </div>
            </div>  
        </div>
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            //validando form
            $("#formRecurso").validate({
                ignore: "",
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    stRecurso: {
                        required: true
                    }, dsAnalise: {
                        required: true
                    }
                }
                , messages: {
                }
            });


            adicionaContadorTextArea(<?php print RecursoResulProc::$TAM_MAX_RECURSO; ?>, "dsJustificativa", "contador");

            adicionaContadorTextArea(<?php print RecursoResulProc::$TAM_MAX_RESPOSTA; ?>, "dsAnalise", "contadorAnalise");

            function ativaParaOutros(valor) {
                return valor == "<?php print RecursoResulProc::$TIPO_OUTROS; ?>";
            }

            // incluindo gatilho para outros
            var gatilho = adicionaGatilhoAddDivSelect("tpRecurso", ativaParaOutros, "divOutros");
            gatilho();

        });
    </script>
</html>