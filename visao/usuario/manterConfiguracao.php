<!DOCTYPE html>
<html>
    <head>     
        <title>Configurações - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTUsuario.php");
        require_once ($CFG->rpasta . "/controle/CTConfiguracaoUsuario.php");

        if (estaLogado() == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }


        //buscando configuraçao
        $conf = buscarConfiguracaoPorUsuarioCT(getIdUsuarioLogado());

        $edicao = isset($_GET['e']) && $_GET['e'] == 1;
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>
    </head>

    <body>  
        <?php include ($CFG->rpasta . "/include/cabecalho.php"); ?>
        <div id="main">
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1>Você está em: Sistema > <strong>Configurações</strong></h1>
                </div>

                <div class="col-full m02">
                    <form class="form-horizontal" id="formEditar" method="post" action='<?php print "$CFG->rwww/controle/CTConfiguracaoUsuario.php?acao=editarConfiguracao" ?>'>
                        <input type="hidden" name="valido" value="ctconfiguracaousuario">
                        <input type="hidden" name="idConfiguracao" id="idConfiguracao" value="<?php print $conf->getCFU_ID_CONFIGURACAO(); ?>">

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Resultados por página: <i title="Quantos itens serão exibidos nas tabelas, por página" class="fa fa-question-circle"></i></label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" <?php !$edicao ? print "disabled" : ""; ?> name="qtRegistros" type="text" id="qtRegistros" size="3" maxlength="3" value="<?php print $conf->getCFU_QT_REGISTROS_PAG() ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class='control-label col-xs-12 col-sm-4 col-md-4'>Salvar filtro: <i title="Se optar por sim, os seus filtros de pesquisa manterão a última busca realizada, enquanto você estiver na mesma sessão" class="fa fa-question-circle"></i></label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoRadioSimNao("salvarFiltro", $conf->getCFU_FL_SALVAR_FILTRO(), $edicao); ?>
                            </div>
                        </div>

                        <?php if (estaLogado(Usuario::$USUARIO_CANDIDATO)) { ?>
                            <div class="form-group">
                                <label class='control-label col-xs-12 col-sm-4 col-md-4'>Atualizações de editais por email: <i title="Se optar por sim, todas as atualizações dos editais em que estiver inscrito serão enviadas para seu e-mail" class="fa fa-question-circle"></i></label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoRadioSimNao("atualizacoesProcesso", $conf->getCFU_FL_ACOMP_PROCESSO(), $edicao); ?>
                                </div>
                            </div>

                            <?php
                        } elseif (estaLogado(Usuario::$USUARIO_COORDENADOR) || estaLogado(Usuario::$USUARIO_AVALIADOR) || estaLogado(Usuario::$USUARIO_ADMINISTRADOR)) {
                            ?>
                            <div class="form-group">
                                <label class='control-label col-xs-12 col-sm-4 col-md-4'>Andamento dos editais por email: <i title="Se optar por sim, todas as atualizações de andamento dos editais serão enviadas para seu e-mail" class="fa fa-question-circle"></i></label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoRadioSimNao("atualizacoesAdministrador", $conf->getCFU_FL_ACOMP_ADMINISTRADOR(), $edicao); ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($edicao) { ?>
                            <div id="divBotoes" class="m02">
                                <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                    <button class="btn btn-default" type="button" onclick="window.location = 'manterConfiguracao.php'" >Voltar</button>
                                </div>
                            </div>

                        <?php } else { ?>
                            <div id="divBotoes" class="m02">
                                <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <button id='btEditar' class="btn btn-primary" type="button" onclick="javascript: window.location = 'manterConfiguracao.php?e=1';" >Editar</button>
                                    <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php echo "$CFG->rwww/inicio"; ?>';" >Voltar</button>
                                </div>
                            </div>
                        <?php } ?>

                        <div id="divMensagem" class="col-full" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>  
    <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
</body>

<script type="text/javascript">
    $(document).ready(function () {

        $("#formEditar").validate({
            submitHandler: function (form) {
                //evitar repetiçao do botao
                mostrarMensagem();
                form.submit();
            },
            rules: {
                qtRegistros: {
                    required: true,
                    digits: true,
                    max: 999
                }}, messages: {
            }
        });

        $("#btEditar").click(function () {
            mostrarMensagem();
        });

        function sucConf() {
            $().toastmessage('showToast', {
                text: '<b>Configuração alterada com sucesso.</b>',
                sticky: false,
                type: 'success', position: 'top-right'
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
