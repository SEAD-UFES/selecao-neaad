<!DOCTYPE html>
<html>
    <head>     
        <title>Alterar Mensagens da Chamada - Seleção EAD</title>
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
        if (!isset($_GET['idProcesso']) || !isset($_GET['idChamada'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados para processamento
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);
        $chamada = buscarChamadaPorIdCT($_GET['idChamada'], $processo->getPRC_ID_PROCESSO());

        // verificando se pode alterar a configuração
        if (!ProcessoChamada::permiteEditarMensagem($processo)) {
            new Mensagem("Não é possível alterar as mensagens da chamada.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // Teve Download do comprovante de inscrição
        $teveDownloadCompInsc = teveDownloadCompInscricaoCT($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());

        // verificando se é criação da chamada
        $fluxoChamada = isset($_GET['cCham']) && $_GET['cCham'] == "true";
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong>Alterar Mensagens da Chamada</strong></h1>
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
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class='barra'></separador>
                                    <b>Chamada:</b> <?php print $chamada->getPCH_DS_CHAMADA(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">

                    <form class="form-horizontal" id="form" method="post" action="<?php print $CFG->rwww; ?>/controle/CTManutencaoProcesso.php?acao=alterarMensagensChamada">
                        <input type="hidden" name="valido" value="ctmanutencaoprocesso">
                        <input type="hidden" id='idProcesso' name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" id='idChamada' name="idChamada" value="<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                        <input type="hidden" id='fluxoChamada' name="fluxoChamada" value="<?php print NGUtil::mapeamentoSimNao($fluxoChamada); ?>">

                        <fieldset>
                            <h3 class="sublinhado">Mensagens</h3>

                            <?php if ($teveDownloadCompInsc) { ?>
                                <div class="callout callout-warning">
                                    Atenção: Ao alterar esta mensagem poderá ocorrer divergências entre os comprovantes de inscrição.
                                </div>
                            <?php } ?>

                            <div class="form-group col-full">
                                <label>Mensagem do Comprovante de Inscrição:</label>
                                <div class="m01">
                                    <textarea title="Mensagem a ser exibida no final do Comprovante de Inscrição do candidato" rows="8" style="width: 100%;" id="msgCompInsc" name="msgCompInsc" class="form-control"><?php print $chamada->getPCH_TXT_COMP_INSCRICAO(TRUE); ?></textarea>
                                    <span id="contador" class="totalCaracteres">caracteres restantes</span>     
                                </div>
                            </div>
                        </fieldset>

                        <div id="divBotoes" class="col-full m02">
                            <div class="form-group">
                                <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
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
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#form").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                }, messages: {
                }
            });


            function sucVagasChamada() {
                $().toastmessage('showToast', {
                    text: '<b>Configuração atualizada com sucesso.</b> Configure as mensagens...',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }
<?php
if (isset($_GET[Mensagem::$TOAST_VAR_GET])) {
    print $_GET[Mensagem::$TOAST_VAR_GET] . "();";
}
?>


            adicionaContadorTextArea(<?php print ProcessoChamada::$TAM_MAX_TEXTO_COMP_INSC; ?>, "msgCompInsc", "contador");

        });

    </script>
</html>

