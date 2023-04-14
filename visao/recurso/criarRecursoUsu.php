<!DOCTYPE html>
<?php
require_once '../../config.php';
global $CFG;

//verificando passagem por get
if (!isset($_GET['idChamada']) || (!isset($_GET['idInscricao']) && !isset($_GET['idProcesso']))) {
    new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
    return;
}

$idProcesso = isset($_GET['idProcesso']) ? $_GET['idProcesso'] : NULL;
$idChamada = $_GET['idChamada'];
?>

<?php
//verificando se está logado
require_once ($CFG->rpasta . "/util/sessao.php");

if (estaLogado(Usuario::$USUARIO_CANDIDATO, TRUE) == null) {
    // salvando na sessão a url de volta
    sessaoNav_setNavegacaoLogin("visao/recurso/criarRecursoUsu.php?idProcesso=$idProcesso&idChamada=$idChamada");

    // incluindo popup de login
    include("$CFG->rpasta/visao/usuario/popupLogin.php");
    return;
}
?>
<html>
    <head>     
        <title>Protocolizar Recurso - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");
        require_once ($CFG->rpasta . "/negocio/TipoCargo.php");

        //recuperando inscricao
        $idInscricao = isset($_GET['idInscricao']) ? $_GET['idInscricao'] : buscarIdInscricaoPorChamUsuarioCT($idProcesso, $idChamada, getIdUsuarioLogado());
        $inscricao = buscarInscricaoComPermissaoCT($idInscricao, getIdUsuarioLogado());

        // recuperando dados para apresentação
        $processo = buscarProcessoPorIdCT($inscricao->getPRC_ID_PROCESSO());
        $chamada = buscarChamadaPorIdCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getPRC_ID_PROCESSO());

        // recuperando etapa em recurso
        $etapaRecurso = buscarEtapaEmRecursoCT($idChamada);

        $podeRec = podehabilitarRecursoCT($idInscricao, $etapaRecurso != NULL ? $etapaRecurso->getESP_ID_ETAPA_SEL() : NULL);
        if (!$podeRec[0]) {
            new Mensagem("Não é possível protocolizar um recurso para esta etapa do Edital. $podeRec[1]", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: Editais > <a href="<?php print $CFG->rwww; ?>/visao/inscricaoProcesso/listarInscProcessoUsuario.php">Minhas Inscrições</a> > <a href="<?php print $CFG->rwww; ?>/visao/recurso/listarRecursoCandidato.php?idInscricao=<?php print $inscricao->getIPR_ID_INSCRICAO(); ?>">Recursos</a> > <strong>Protocolizar Recurso</strong></h1>
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
                    <div class="callout callout-danger">
                        <strong>Muita atenção</strong> ao redigir o recurso, pois <b>NÃO</b> será possível fazer qualquer alteração após o envio do recurso para análise.
                        Note que você <strong>não poderá</strong> protocolizar outro recurso para a mesma etapa desta chamada do processo seletivo.
                    </div>                    
                </div>

                <div class="col-full"> 
                    <form class="form-horizontal" id="formRecurso" method="post" action='<?php print "$CFG->rwww/controle/CTNotas.php?acao=criarRecurso" ?>'>
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" name="idInscricao" value="<?php print $inscricao->getIPR_ID_INSCRICAO(); ?>">
                        <input type="hidden" name="idChamada" value="<?php print $inscricao->getPCH_ID_CHAMADA(); ?>">

                        <fieldset class="m02">
                            <legend>Recurso contra o resultado da <?php print $etapaRecurso->getNomeEtapa(); ?></legend>
                            <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>
                            <br clear="all">

                            <div class="form-group m02">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Motivo:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8"><?php impressaoTipoRecurso(); ?></div>
                            </div>

                            <div id="divOutros" style="display: none;">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Descrição do Motivo:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" type="text" id="dsMotivoOutros" name="dsMotivoOutros" size="100" maxlength="100" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Justificativa:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <textarea class="form-control" id="dsJustificativa" cols="60" rows="10" style="width: 100%" name="dsJustificativa"></textarea>
                                    <span id="contador" class="totalCaracteres">caracteres restantes</span>                                    
                                </div>
                            </div>
                        </fieldset>

                        <div id="divBotoes">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-primary" type="submit" value="Protocolizar">
                                <input type="button" class="btn btn-default"  onclick="javascript: window.location = '<?php print $CFG->rwww ?>/visao/recurso/listarRecursoCandidato.php?idInscricao=<?php print $inscricao->getIPR_ID_INSCRICAO(); ?>';" value="Voltar">
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
                    tpRecurso: {
                        required: true
                    }, dsJustificativa: {
                        required: true
                    }, dsMotivoOutros: {
                        required: function (element) {
                            return ativaParaOutros($("#tpRecurso").val());
                        }
                    }
                }
                , messages: {
                }
            }
            );


            adicionaContadorTextArea(<?php print RecursoResulProc::$TAM_MAX_RECURSO; ?>, "dsJustificativa", "contador");

            function ativaParaOutros(valor) {
                return valor == "<?php print RecursoResulProc::$TIPO_OUTROS; ?>";
            }

            // incluindo gatilho para outros
            adicionaGatilhoAddDivSelect("tpRecurso", ativaParaOutros, "divOutros");
        });
    </script>
</html>