<!DOCTYPE html>
<html>
    <head>     
        <title>Alterar Configuração da Chamada - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
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
        if (!ProcessoChamada::permiteEditarConfiguracao($processo)) {
            new Mensagem("Não é possível alterar as configurações da chamada.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // verificando se é volta
        $dadosSessao = isset($_GET['volta']) && $_GET['volta'] == "true" && sessaoDados_getDados("idChamada") == $chamada->getPCH_ID_CHAMADA();

        // verificando se é criação da chamada
        $fluxoChamada = isset($_GET['cCham']) && $_GET['cCham'] == "true";

        // verificando se é volta de erro sem alteração
        $semAlteracao = isset($_GET[Mensagem::$TOAST_VAR_GET]) && $_GET[Mensagem::$TOAST_VAR_GET] == "errSemAlteracao";
        if ($semAlteracao) {
            // limpando
            unset($_GET[Mensagem::$TOAST_VAR_GET]);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar Chamada</a> > <strong>Alterar Configurações</strong></h1>
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

                    <?php if ($semAlteracao) { ?>
                        <div role="alert" class="alert alert-danger">
                            Você não modificou a configuração da chamada!
                        </div>
                    <?php } ?>

                    <form class="form-horizontal left-full" id="form" method="post" action="<?php print $CFG->rwww; ?>/controle/CTManutencaoProcesso.php?acao=alterarConfChamada">
                        <input type="hidden" name="valido" value="ctmanutencaoprocesso">
                        <input type="hidden" id='idProcesso' name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" id='idChamada' name="idChamada" value="<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                        <input type="hidden" id='fluxoChamada' name="fluxoChamada" value="<?php print NGUtil::mapeamentoSimNao($fluxoChamada); ?>">

                        <fieldset id="alterarConfiguracaoChamada">
                            <legend>Configurações da Chamada</legend>
                            <div class="col-full">
                                <?php
                                if ($chamada->admitePoloObj()) {
                                    ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading" title="Coloque à direita os polos que farão parte desta Chamada">Polos da Chamada: *</div>
                                        <div class="panel-body">
                                            <?php
                                            impressaoPolos($chamada->getPCH_ID_CHAMADA(), ($dadosSessao ? explode(",", sessaoDados_getDados("idPolos")) : NULL));
                                            ?>
                                        </div>
                                    </div>

                                    <div class="panel panel-default">
                                        <div class="panel-heading" title="Informa o número de polos que o candidato pode escolher no ato da inscrição">Número Máx Opção de Polo: *</div>
                                        <div class="panel-body">
                                            <input class="form-control" name="nrMaxOpcaoPolo" type="text" id="nrMaxOpcaoPolo" size="3" maxlength="3" value="<?php print $dadosSessao ? sessaoDados_getDados("nrMaxOpcaoPolo") : $chamada->getPCH_NR_MAX_OPCAO_POLO(); ?>" required>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="panel panel-default">
                                    <div class="panel-heading" title="Coloque à direita as áreas de atuação que farão parte desta Chamada">Áreas de Atuação:</div>
                                    <div class="panel-body">
                                        <?php
                                        impressaoAreaAtu($chamada->getPCH_ID_CHAMADA(), ($dadosSessao ? explode(",", sessaoDados_getDados("idAreasAtu")) : NULL));
                                        ?>
                                    </div>
                                </div>

                                <div class="panel panel-default">
                                    <div class="panel-heading" title="Coloque à direita as reserva de vagas que farão parte desta Chamada">Reserva de vagas:</div>
                                    <div class="panel-body">
                                        <?php
                                        impressaoReservaVagas($chamada->getPCH_ID_CHAMADA(), ($dadosSessao ? explode(",", sessaoDados_getDados("idReservaVagas")) : NULL));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div id="divBotoes" class="col-full m02">
                            <button id="submeter" class="btn btn-success" type="submit">Salvar</button>
                            <button type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/processo/"; ?>manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>';">Voltar</button>
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
        carregaScript("jquery.maskedinput");
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        carregaCSS("jquery.multiselect2side");
        carregaScript("jquery.multiselect2side");
        ?>
    </body>
    <script type="text/javascript">
                $(document).ready(function () {

<?php if ($chamada->admitePoloObj()) { ?>
            // criando o multiselect
            $('#idPolos').attr("size", "13");
                    $('#idPolos').multiselect2side({
            selectedPosition: 'right',
                    moveOptions: false,
                    sortOptions: false,
                    labelsx: '',
                    labeldx: '',
                    search: true,
                    autoSort: true,
                    autoSortAvailable: true,
                    placeHolderSearch: 'Buscar Polo'
            });
                    // inserindo máscara para opção de polo
                    $("#nrMaxOpcaoPolo").mask("9?99", {placeholder:""});
<?php } ?>

        // criando o multiselect de área
        $('#idAreasAtu').attr("size", "13");
                $('#idAreasAtu').multiselect2side({
        selectedPosition: 'right',
                moveOptions: false,
                sortOptions: false,
                labelsx: '',
                labeldx: '',
                search: true,
                autoSort: true,
                autoSortAvailable: true,
                placeHolderSearch: 'Buscar Área'
        });
                // criando o multiselect de reserva de vaga
                $('#idReservaVagas').attr("size", "13");
                $('#idReservaVagas').multiselect2side({
        selectedPosition: 'right',
                moveOptions: false,
                sortOptions: false,
                labelsx: '',
                labeldx: '',
                search: true,
                autoSort: true,
                autoSortAvailable: true,
                placeHolderSearch: 'Buscar Reserva de Vaga'
        });
                $("#form").validate({
        ignore: "",
                submitHandler: function (form) {
                //evitar repetiçao do botao
                mostrarMensagem();
                        form.submit();
                },
                rules: {
<?php if ($chamada->admitePoloObj()) { ?>
                    'idPolos[]': {
                    required: true
                    }, nrMaxOpcaoPolo:{
                    required: true,
                            digits: true,
                            min: 1
                    }
<?php } ?>
                }, messages: {
<?php if ($chamada->admitePoloObj()) { ?>
            'idPolos[]': {
            required: "A Chamada deve ter pelo menos um polo participante."
            }
<?php } ?>
        }
        });
                function sucCalChamada() {
                $().toastmessage('showToast', {
                text: '<b>Calendário atualizado com sucesso.</b> Configure a chamada...',
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
        });
    </script>
</html>

