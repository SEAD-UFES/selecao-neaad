<!DOCTYPE html>
<html>
    <head>  
        <?php
        // recuperando parametros
        $arrayFuncoes = array("editar" => "editar", "excluir" => "excluir", "visualizar" => "consultar");
        if (!isset($_GET['idDepartamento']) || !isset($_GET['fn']) || array_search($_GET['fn'], $arrayFuncoes) === FALSE) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        $idDepartamento = $_GET['idDepartamento'];
        $funcao = $_GET['fn'];
        ?>

        <title><?php print ucfirst($funcao) ?> Departamento - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        // recuperando departamento
        require_once ($CFG->rpasta . "/controle/CTDepartamento.php");
        $departamento = buscarDepartamentoPorIdCT($idDepartamento);
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
                    <h1>Você está em: Cadastros > <a href="<?php print $CFG->rwww; ?>/visao/departamento/listarDepartamento.php">Departamento</a> > <strong><?php print ucfirst($funcao) ?></strong></h1>
                </div>

                <div class="contents completo m02 p15">
                    <form id="form" class="form-horizontal" method="post" action="<?php print $CFG->rwww ?>/controle/CTDepartamento.php?acao=<?php print $funcao ?>Departamento">
                        <input type="hidden" name="valido" value="ctdepartamento">
                        <input id='idDepartamento' type="hidden" name="idDepartamento" value="<?php print $departamento->getDEP_ID_DEPARTAMENTO(); ?>">

                        <div class="form-group">
                            <label for="idDepartamentoTela" class="control-label col-xs-12 col-sm-4 col-md-4">Código:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" disabled="true" name="idDepartamentoTela" type="text" id="idDepartamentoTela" size="30" maxlength="50" placeholder="Código do Departamento" value="<?php print $departamento->getDEP_ID_DEPARTAMENTO() ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dsNome" class="control-label col-xs-12 col-sm-4 col-md-4">Nome:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dsNome" type="text" id="dsNome" size="30" maxlength="50" placeholder="Nome do Departamento" value="<?php print $departamento->getDEP_DS_DEPARTAMENTO() ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Situação:</label>
                            <?php
                            $permiteInativo = validaInativacaoDepCT($departamento->getDEP_ID_DEPARTAMENTO());
                            ?>
                            <div title="<?php $permiteInativo ? print "" : print "Você não pode alterar a situação deste departamento porque existem cursos ativos vinculados a ele." ?>" class="col-xs-12 col-sm-8 col-md-8">
                                <?php
                                include_once ($CFG->rpasta . "/util/selects.php");
                                impressaoRadioAtivoInativo($departamento->getDEP_ST_SITUACAO());
                                ?>
                                <?php if (!$permiteInativo) { ?>
                                    <script type="text/javascript">
                                        $(document).ready(function () {
                                            $("[name='stSituacao']").attr("disabled", true);
                                        });
                                    </script>
                                <?php } ?>
                            </div>
                        </div>

                        <div id="divBotoes" class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>
                            <?php
                            if ($funcao == $arrayFuncoes['excluir']) {
                                require_once ($CFG->rpasta . "/include/fragmentoPergExclusao.php");
                                EXC_fragmentoPergExcEmPag("form");
                            }
                            ?>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php if ($funcao == $arrayFuncoes["excluir"]) { ?>
                                    <button type="button" data-target="#perguntaExclusao" class="btn btn-danger" role="button" data-toggle="modal">Excluir</button>
                                    <input class="btn btn-default" id="btVoltar" type="button" onclick="javascript: window.location = 'listarDepartamento.php';" value="Voltar">
                                <?php } elseif ($funcao == $arrayFuncoes["editar"]) { ?>
                                    <input class="btn btn-success" id="submeter" type="submit" value="Salvar">
                                    <input class="btn btn-default" id="btVoltar" type="button" onclick="javascript: window.location = 'listarDepartamento.php';" value="Voltar">
                                <?php } else { ?>
                                    <input class="btn btn-default" id="btVoltar" type="button" onclick="javascript: window.location = 'listarDepartamento.php';" value="Voltar">
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="divMensagem" class="col-full" style="display: none;">
                <div class="alert alert-info">
                    Aguarde o processamento...
                </div>
            </div>
        </div>
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {

<?php
// Bloquear campos de ediçao, caso necessario
if ($funcao != $arrayFuncoes["editar"]) {
    ?>
                $(":input").not(":button,:submit,[type='hidden']").attr("disabled", true);
<?php } ?>

            $("#form").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    //                $(":input[type=text]").capitalize();
                    form.submit();
                },
                rules: {
                    stSituacao: {
                        required: true
                    },
                    dsNome: {
                        required: true,
                        minlength: 3,
                        remote: {
                            url: "<?php print $CFG->rwww ?>/controle/CTAjax.php?val=nomeDepartamento&idDepartamento=" + $("#idDepartamento").val(),
                            type: "post"
                        }

                    }}, messages: {
                    dsNome: {
                        remote: "Departamento já cadastrado."
                    },
                    stSituacao: {
                        required: "Campo obrigatório."
                    }
                }
            }
            );
        });
    </script>
</html>

