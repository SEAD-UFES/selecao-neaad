<!DOCTYPE html>
<html>
    <head>   
        <?php
        // recuperando parametros
        $arrayFuncoes = array("editar" => "editar", "visualizar" => "consultar");
        if (!isset($_GET['idCurso']) || !isset($_GET['fn']) || array_search($_GET['fn'], $arrayFuncoes) === FALSE) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        $idCurso = $_GET['idCurso'];
        $funcao = $_GET['fn'];

        // definindo se deverá ser mostrado o *
        $asteriscoEdicao = $funcao == $arrayFuncoes["editar"] ? "*" : "";
        ?>

        <title><?php print ucfirst($funcao) ?> Curso - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        // recuperando curso
        require_once ($CFG->rpasta . "/controle/CTCurso.php");
        $curso = buscarCursoPorIdCT($idCurso);
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>


        <?php

        function botoesTela($funcao, $arrayFuncoes, $avaliadores = FALSE) {
            $classeLabel = $avaliadores ? "" : "control-label col-xs-12 col-sm-4 col-md-4";
            $classeDiv = $avaliadores ? "" : "col-xs-12 col-sm-8 col-md-8";
            $ret = " <div class='control-group m02' id='divBotoes'>
                                        <label class='$classeLabel'>&nbsp;</label>
                                        <div class='$classeDiv'>";
            if ($funcao != $arrayFuncoes["visualizar"]) {
                $ret.= "<input class='btn btn-success' type='submit' value='Salvar'> ";
                $ret .= "<input class= 'btn btn-default' type='button' onclick=\"javascript: window.location = 'listarCurso.php';\" value='Voltar'>";
            } else {
                $ret .= "<input class= 'btn btn-default' type='button' onclick=\"javascript: window.location = 'listarCurso.php';\" value='Voltar'>";
            }
            $ret .= "</div>
                    </div>";

            return $ret;
        }
        ?>
    </head>
    <body>  
        <?php
        include ($CFG->rpasta . "/include/cabecalho.php");
        ?>

        <div id="main">
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1>Você está em: Cadastros > <a href="<?php print $CFG->rwww; ?>/visao/curso/listarCurso.php">Curso</a> > <strong><?php print ucfirst($funcao); ?></strong></h1>
                </div>

                <?php if ($funcao == $arrayFuncoes["editar"]) { ?>
                    <?php print Util::$MSG_CAMPO_OBRIG; ?>
                <?php } ?>

                <div class="col-full m02">
                    <form id="formCadastro" class="form-horizontal" method="post" action="<?php print $CFG->rwww; ?>/controle/CTCurso.php?acao=editarCurso">

                        <div class="tabbable">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab1" data-toggle="tab">Dados Gerais</a></li>
                                <li><a href="#tab2" data-toggle="tab">Avaliadores</a></li>
                            </ul>

                            <div class="tab-content m02">
                                <div class="tab-pane active" id="tab1">
                                    <input type="hidden" name="valido" value="ctcurso">
                                    <input id='idCurso' type="hidden" name="idCurso" value="<?php print $curso->getCUR_ID_CURSO(); ?>">
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Código:</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <input class="form-control" disabled="true" name="idCursoTela" type="text" id="idCursoTela" size="30" maxlength="50" placeholder="Código do Curso" value="<?php print $curso->getCUR_ID_CURSO(); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome: <?php echo $asteriscoEdicao; ?></label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <input class="form-control" name="nmCurso" type="text" id="nmCurso" size="30" maxlength="30" placeholder="Nome do Curso" value="<?php print $curso->getCUR_NM_CURSO(); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo: <?php echo $asteriscoEdicao; ?></label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <?php impressaoTipoCurso($curso->getTPC_ID_TIPO_CURSO()) ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Departamento: <?php echo $asteriscoEdicao; ?></label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <?php impressaoDepartamento($curso->getDEP_ID_DEPARTAMENTO()) ?>
                                        </div>
                                    </div>
                                    <?php
                                    if ($funcao == $arrayFuncoes["visualizar"]) {
                                        ?>
                                        <div class="form-group">
                                            <label title="Nome utilizado quando o curso aparece em uma URL" class="control-label col-xs-12 col-sm-4 col-md-4">URL:</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input class="form-control" name="urlBusca" type="text" id="urlBusca" size="30" maxlength="30" placeholder="UrlBusca" value="<?php print $curso->getCUR_URL_BUSCA(); ?>">
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Coordenador:</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <?php impressaoUsuario($curso->getCUR_ID_COORDENADOR(), NGUtil::getSITUACAO_ATIVO(), Usuario::$USUARIO_COORDENADOR); ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Grande área: <?php echo $asteriscoEdicao; ?></label>

                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <span><?php impressaoArea($curso->getCUR_ID_AREA_CONH(), "idAreaConh"); ?></span>
                                            <div id="divEsperaSubarea" style="display: none">
                                                <span>Aguarde, Carregando...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="divListaSubarea" style="display: none">
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Área: <?php echo $asteriscoEdicao; ?></label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <select class="form-control" name="idSubareaConh" id="idSubareaConh"></select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome extenso: * <i title="Este nome será utilizado para emissão de relatórios. Siga o modelo: curso de licenciatura em Artes Visuais - EAD" class="fa fa-question-circle"></i></label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <textarea class="form-control" cols="60" rows="4" name="dsCurso" id="dsCurso"><?php print $curso->getCUR_DS_CURSO(); ?></textarea>
                                            <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Situação <?php echo $asteriscoEdicao; ?>:</label>
                                        <?php
                                        $permiteInativo = validaInativacaoCursoCT($curso->getCUR_ID_CURSO());
                                        ?>
                                        <div title="<?php $permiteInativo ? print "" : print "Você não pode alterar a situação deste curso porque ele possui editais em aberto." ?>" class="controls col-xs-12 col-sm-8 col-md-8">
                                            <?php
                                            include_once ($CFG->rpasta . "/util/selects.php");
                                            impressaoRadioAtivoInativo($curso->getCUR_ST_SITUACAO());
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
                                    <?php print botoesTela($funcao, $arrayFuncoes); ?>
                                </div>

                                <div class="tab-pane" id="tab2" style="overflow-x: hidden;">
                                    <?php
                                    if ($funcao == $arrayFuncoes["editar"]) {
                                        ?>
                                        <p>Coloque a direita os avaliadores que você deseja alocar para o curso.</p>
                                        <?php
                                        $listaSel = buscarAvalLivrePorCursoCT($curso->getCUR_ID_CURSO(), TRUE);
                                        $tam = impressaoAvalLivrePorCurso($curso->getCUR_ID_CURSO(), $listaSel);
                                    } else {
                                        print tabelaAvaliadoresPorCurso($curso->getCUR_ID_CURSO());
                                    }
                                    ?>

                                    <?php print botoesTela($funcao, $arrayFuncoes, TRUE); ?>
                                </div>
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
        carregaScript("ajax");
        carregaCSS("jquery.multiselect2side");
        carregaScript("jquery.multiselect2side");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#formCadastro").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
//                    $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                    form.submit();
                },
                rules: {
                    nmCurso: {
                        required: true,
                        minlength: 3,
                        remote: {
                            url: "<?php print $CFG->rwww; ?>/controle/CTAjax.php?val=nomeCurso&idCurso=<?php print $curso->getCUR_ID_CURSO(); ?>",
                                                    type: "post"
                                                }
                                            }, tpCurso: {
                                                required: true
                                            }, idDepartamento: {
                                                required: true
                                            }, idAreaConh: {
                                                required: true
                                            },
                                            idSubareaConh: {
                                                required: true
                                            }
                                            , dsCurso: {
                                                required: true,
                                                maxlength: 250
                                            }}, messages: {
                                        }
                                    }
                                    );

                                    //incluindo contador para caracteres restantes
                                    adicionaContadorTextArea(250, "dsCurso", "qtCaracteres");


                                    // tratando gatilho de ajax para subarea
                                    function getParamsSubarea()
                                    {
                                        return {'cargaSelect': "areaConhecimento", 'idArea': $("#idAreaConh").val()};
                                    }
                                    adicionaGatilhoAjaxSelect("idAreaConh", getIdSelectSelecione(), "divEsperaSubarea", "divListaSubarea", "idSubareaConh", "<?php print "'{$curso->getCUR_ID_SUBAREA_CONH()}'"; ?>", getParamsSubarea);

<?php
// Bloquear campos de ediçao, caso necessario
if ($funcao != $arrayFuncoes["editar"]) {
    ?>
                                        $(":input").not(":button,:submit,[type='hidden']").attr("disabled", true);
<?php } ?>


                                    // criando o multiselect
                                    $('#idAvaliador').attr("size", "<?php isset($tam) ? print $tam : print "13" ?>");
                                    $('#idAvaliador').multiselect2side({
                                        selectedPosition: 'right',
                                        moveOptions: false,
                                        sortOptions: false,
                                        labelsx: '',
                                        labeldx: '',
                                        search: true,
                                        autoSort: false,
                                        autoSortAvailable: true,
                                        placeHolderSearch: 'Buscar Avaliador',
                                        widthSelect: 350
                                    });

                                });
    </script>
</html>

