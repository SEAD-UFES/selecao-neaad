<!DOCTYPE html>
<html>
    <head>     
        <title>Gerenciar Item de Avaliação - Seleção EAD</title>
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
        if (!isset($_GET['idItemAval']) && (!isset($_GET['idProcesso']) || !isset($_GET['idCategoriaAval']))) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados adicionais, se necessario
        if (isset($_GET['idItemAval'])) {
            // recuperando categorias
            $itemAval = buscarItemAvalPorIdCT($_GET['idItemAval']);

            $idProcesso = $itemAval->getPRC_ID_PROCESSO();
            $idCategoriaAval = $itemAval->getCAP_ID_CATEGORIA_AVAL();
            $edicao = TRUE;
        } else {
            $idProcesso = $_GET['idProcesso'];
            $idCategoriaAval = $_GET['idCategoriaAval'];
            $edicao = FALSE;
        }

        // buscando processo
        $processo = buscarProcessoComPermissaoCT($idProcesso);

        // buscando dados
        $categoriaAval = buscarCatAvalPorIdCT($idCategoriaAval);
        $etapa = buscarEtapaAvalPorIdCT($categoriaAval->getEAP_ID_ETAPA_AVAL_PROC(), $processo->getPRC_ID_PROCESSO());

        // verificando se pode alterar a etapa
        if (!$etapa->podeAlterar()) {
            new Mensagem("Item não pode ser alterado.", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_INF_COMP; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <a href="<?php print $CFG->rwww; ?>/visao/itemAvalProc/listarItemAvalProc.php?idCategoriaAval=<?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?>">Itens de Avaliação</a> > <strong> <?php if (!$edicao) { ?> Novo <?php } else { ?> Editar <?php } ?></strong></h1>
                </div>

                <div class="col-full">
                    <div class="bs-callout bs-callout-info ficha-tecnica">
                        <p>
                            <i class="fa fa-book"></i>
                        <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class="barra"></separador>
                        <b>Etapa:</b> <?php print $etapa->getNomeEtapa(); ?> <separador class="barra"></separador>
                        <?php echo $processo->getHTMLLinkFluxo(); ?>
                        </p>
                    </div>
                </div>

                <div class="col-full m02">
                    <fieldset>
                        <legend>Categoria de Avaliação</legend>
                        <div class="completo">
                            <div class="col-half">
                                <p><b>Tipo:</b> <?php print $categoriaAval->getNomeCategoria(); ?></p>
                                <p><b>Avaliação:</b> <?php print CategoriaAvalProc::getDsTipoAval($categoriaAval->getCAP_TP_AVALIACAO()); ?></p>
                                <p><b>Exclusiva:</b> <?php print $categoriaAval->getDsCatExclusiva(); ?></p>
                            </div>
                            <div class="col-half">
                                <p><b>Código:</b> <?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?></p>
                                <p><b>Ordem:</b> <?php print $categoriaAval->getCAP_ORDEM(); ?></p>
                                <p><b>Pontuação Máx:</b> <?php print$categoriaAval->getVlNotaMaxFormatada(); ?></p>
                            </div>
                        </div>
                    </fieldset>

                    <form class="form-horizontal" id="formCadastro" method="post" action="<?php print $CFG->rwww; ?>/controle/CTNotas.php?acao=<?php !$edicao ? print "criarItemAval" : print "editarItemAval" ?>">
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" name="idCategoriaAval" value="<?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?>">
                        <input type="hidden" id="idNotaMaxCategoria" value="<?php print $categoriaAval->getCAP_VL_PONTUACAO_MAX(); ?>">

                        <fieldset class="m02">
                            <legend><?php !$edicao ? print 'Novo' : print 'Editar' ?> Item de Avaliação</legend>
                            <div id="divErCriarItem" style="display: none" class="alert alert-danger">
                                Desculpe. Você já cadastrou um Item com os parâmetros informados. Por favor, altere o Item atual ou clique em 'Voltar' para visualizar os Itens cadastrados.
                            </div>

                            <?php print Util::$MSG_CAMPO_OBRIG; ?>

                            <?php if ($edicao) { ?>
                                <input type="hidden" name="idItemAval" value="<?php print $itemAval->getIAP_ID_ITEM_AVAL(); ?>">
                            <?php } ?>

                            <div class="completo m02">

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo: *</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <?php impressaoTipoItemAval($categoriaAval->getCAP_TP_CATEGORIA(), $edicao ? $itemAval->getIAP_TP_ITEM() : NULL, $edicao); ?>
                                    </div>
                                </div>

                                <?php if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) { ?>

                                    <?php if ($categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_AVAL_EXTERNA) { ?>
                                        <div id="divdsItemExt" class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Descrição do Item: *</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input required class="form-control" type='text' name='dsItemExt' id='dsItemExt' size="100" maxlength="30" value="<?php $edicao ? print $itemAval->getIAP_DS_OUTROS_PARAM() : print ""; ?>">
                                            </div>
                                        </div>

                                    <?php } ?>

                                    <?php if ($categoriaAval->admiteItensAreaSubareaObj()) { ?>
                                        <div class="form-group app">
                                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Grande Área:</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <?php impressaoArea($edicao ? $itemAval->getIAP_ID_AREA_CONH() : NULL, "idAreaConh"); ?>
                                                <div id="divEsperaSubarea" style="display: none">
                                                    <span>Aguarde, Carregando...</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="divListaSubarea" style="display: none">
                                            <div class="form-group">
                                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Área:</label>
                                                <div class="col-xs-12 col-sm-8 col-md-8">
                                                    <select class="form-control" name="idSubareaConh" id="idSubareaConh"></select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php
                                    if ($categoriaAval->isAvalAutomatica() && $categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_TITULACAO) {
                                        // parametros especiais para titulacao
                                        ?>

                                        <div class="form-group app">
                                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Situação da Formação: *</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <?php impressaoSituacaoForm($edicao ? $itemAval->getValorParam($categoriaAval->getCAP_TP_CATEGORIA(), ItemAvalProc::$PARAM_TIT_STFORMACAO) : ItemAvalProc::valorPadraoParam(ItemAvalProc::$PARAM_TIT_STFORMACAO), "stFormacao"); ?>
                                            </div>
                                        </div>

                                        <div id="divTpExclusivo" style="display: none" class="form-group app">
                                            <label title="Se 'Sim', tipos de formação equivalentes são desconsiderados na análise de pontuação" class="control-label col-xs-12 col-sm-4 col-md-4">Tipo Exclusivo: *</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <?php impressaoRadioSimNao("tpExclusivo", $edicao ? NGUtil::mapeamentoSimNao($itemAval->getValorParam($categoriaAval->getCAP_TP_CATEGORIA(), ItemAvalProc::$PARAM_TIT_EXCLUSIVO)) : NGUtil::mapeamentoSimNao(ItemAvalProc::valorPadraoParam(ItemAvalProc::$PARAM_TIT_EXCLUSIVO)));
                                                ?>
                                            </div>
                                        </div>

                                        <div id="divSegGraduacao" style="display: none" class="form-group app">
                                            <label title="Indica se o enquadramento ocorrerá apenas a partir da segunda graduação" class="control-label col-xs-12 col-sm-4 col-md-4">Segunda Graduação: *</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <?php impressaoRadioSimNao("segGraduacao", $edicao ? NGUtil::mapeamentoSimNao($itemAval->getValorParam($categoriaAval->getCAP_TP_CATEGORIA(), ItemAvalProc::$PARAM_TIT_SEGGRADUACAO)) : NGUtil::mapeamentoSimNao(ItemAvalProc::valorPadraoParam(ItemAvalProc::$PARAM_TIT_SEGGRADUACAO)));
                                                ?>
                                            </div>
                                        </div>

                                        <div id="divCargaHorariaMin" style="display: none" class="form-group">
                                            <label title="Carga horária mínima, em horas, para o enquadramento. Caso não seja informada, qualquer carga horária será considerada na análise" class="control-label col-xs-12 col-sm-4 col-md-4">Carga Horária Mín (hs):</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input class="tudo-normal form-control" type='text' name='cargaHorariaMin' id='cargaHorariaMin' size="6" maxlength="6" value="<?php $edicao ? print $itemAval->getValorParam($categoriaAval->getCAP_TP_CATEGORIA(), ItemAvalProc::$PARAM_TIT_CARGA_HORARIA_MIN) : print ItemAvalProc::valorPadraoParam(ItemAvalProc::$PARAM_TIT_CARGA_HORARIA_MIN); ?>">
                                            </div>
                                        </div>

                                    <?php } ?>

                                    <?php if ($categoriaAval->isAvalAutomatica()) { ?>
                                        <div class="form-group">
                                            <label title="<?php print ItemAvalProc::getExpUnidadePontuacaoAdmin($categoriaAval->getCAP_TP_CATEGORIA()); ?>" class="control-label col-xs-12 col-sm-4 col-md-4">Pontuação: *</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input class="tudo-normal form-control" type='text' name='pontuacao' id='pontuacao' size="6" maxlength="6" value="<?php $edicao ? print $itemAval->getVlNotaFormatada() : print ""; ?>">
                                            </div>
                                        </div>

                                        <?php if (!$categoriaAval->isCategoriaExclusiva()) { ?>
                                            <div class="form-group">
                                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Grupo: *</label>
                                                <div class="col-xs-12 col-sm-8 col-md-8">
                                                    <?php impressaoGruposItemAval($categoriaAval->getCAP_ID_CATEGORIA_AVAL(), $edicao ? $itemAval->getIdSubGrupoVisualizacao() : NULL, "idGrupo"); ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>

                                    <div id="divPontuacaoMax" style="display: none" class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Pontuação Máx: *</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <input class="tudo-normal form-control" type='text' name='pontuacaoMax' id='pontuacaoMax' size="6" maxlength="6" value="<?php $edicao ? print $itemAval->getVlNotaMaxFormatada() : print ""; ?>">
                                        </div>
                                    </div>
                                <?php } ?>

                            </div>
                        </fieldset>
                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/itemAvalProc/listarItemAvalProc.php?idCategoriaAval=<?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?>';" value="Voltar">
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
        carregaScript("jquery.price_format");
        carregaScript("jquery.maskedinput");
        ?>
    </body>
    <script type="text/javascript">
                $(document).ready(function() {
<?php if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) { ?>
            // verificando quando mostrar pontuacao max
            function mostrarPontuacaoMax(){

            // sempre mostrar se nao e avaliacao automatica
            if (<?php !$categoriaAval->isAvalAutomatica() ? print "true" : print "false" ?>){
            return true;
            }

            // ocultar sempre se for exclusiva
            if (<?php $categoriaAval->isCategoriaExclusiva() ? print "true" : print "false" ?>){
            return false;
            }

            // verficando caso de grupo
            return $("#idGrupo").val() == '<?php print ItemAvalProc::$ID_GRUPO_NOVO_GRUPO; ?>' ||
                    $("#idGrupo").val() == '<?php print ItemAvalProc::$ID_GRUPO_SEM_AGRUPAMENTO; ?>' ||
                    (<?php $edicao ? print 'true' : print 'false' ?> && $("#idGrupo").val() == '<?php $edicao ? print $itemAval->getIAP_ID_SUBGRUPO() : print ""; ?>');
            };
                    // criando gatilho para grupo
                    var gatGrupo = function(){
                    mostrarPontuacaoMax()? $("#divPontuacaoMax").show(): $("#divPontuacaoMax").hide();
                    };
    <?php if ($categoriaAval->isAvalAutomatica() && !$categoriaAval->isCategoriaExclusiva()) { ?>
                $("#idGrupo").change(gatGrupo);
    <?php } ?>
            gatGrupo();
    <?php if ($categoriaAval->isAvalAutomatica()) { ?>
                addMascaraDecimal("pontuacao");
    <?php } ?>

            addMascaraDecimal("pontuacaoMax");
    <?php if ($categoriaAval->admiteItensAreaSubareaObj()) { ?>
                // tratando gatilho de ajax para subarea
                function getParamsSubarea()
                {
                return {'cargaSelect': "areaConhecimento", 'idArea': $("#idAreaConh").val()};
                }
                gatilhoArea = adicionaGatilhoAjaxSelect("idAreaConh", getIdSelectSelecione(), "divEsperaSubarea", "divListaSubarea", "idSubareaConh", getIdSubarea(), getParamsSubarea);
                        //
                                function isFixarArea(tipo)
                                {
                                var arrayFixar = <?php print ItemAvalProc::getArrayJSTpFixarArea($categoriaAval->getCAP_TP_CATEGORIA()); ?>;
                                        return arrayFixar.indexOf(tipo) !== - 1;
                                }

                        function getIdSubarea() {
                        var id = isFixarArea($("#tpItemAval").val()) ? "'<?php print ItemAvalProc::getSubAreaFixa($categoriaAval->getCAP_TP_CATEGORIA()); ?>'" : "";
                                if (id == "")
                        {
                        id = <?php $edicao ? print "\"'{$itemAval->getIAP_ID_SUBAREA_CONH()}'\"" : print "''" ?>;
                        }
                        return id;
                        }

                        function getIdArea() {
                        var id = isFixarArea($("#tpItemAval").val()) ? '<?php print ItemAvalProc::getAreaFixa($categoriaAval->getCAP_TP_CATEGORIA()); ?>' : "";
                                if (id == "")
                        {
                        id = <?php $edicao ? print "'{$itemAval->getIAP_ID_AREA_CONH()}'" : print "''" ?>;
                        }
                        return id;
                        }


                        var funcaoFixaArea = function()
                        {
                        $("#idAreaConh").val(getIdArea());
                                gatilhoArea(getIdSubarea());
                                if (isFixarArea($("#tpItemAval").val())) {
                        $("#idAreaConh").attr("disabled", true);
                                $("#idSubareaConh").attr("disabled", true);
                        }
                        else if (<?php !$edicao ? print "true" : print "false"; ?>) {
                        $("#idAreaConh").attr("disabled", false);
                                $("#idSubareaConh").attr("disabled", false);
                        }

                        };
                                $("#tpItemAval").change(funcaoFixaArea);
                                funcaoFixaArea();
    <?php } ?>


    <?php if ($categoriaAval->isAvalAutomatica() && $categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_TITULACAO) { ?>
                        // mascara para carga horaria minima
                        $("#cargaHorariaMin").mask("9?99999", {placeholder:" "});
                                // programando gatilho para tipo
                                var gatilhoTpAval = function() {
                                var admiteExclusivo = <?php print ItemAvalProc::getListaAdmiteParametro(ItemAvalProc::$PARAM_TIT_EXCLUSIVO); ?>;
                                        var admiteSegGraduacao = <?php print ItemAvalProc::getListaAdmiteParametro(ItemAvalProc::$PARAM_TIT_SEGGRADUACAO); ?>;
                                        var admiteCargaHoraria = <?php print ItemAvalProc::getListaAdmiteParametro(ItemAvalProc::$PARAM_TIT_CARGA_HORARIA_MIN); ?>;
                                        // definindo o que mostrar

                                        admiteExclusivo.indexOf($("#tpItemAval").val()) !== - 1 ? $("#divTpExclusivo").show() : $("#divTpExclusivo").hide();
                                        admiteSegGraduacao.indexOf($("#tpItemAval").val()) !== - 1 ? $("#divSegGraduacao").show() : $("#divSegGraduacao").hide();
                                        admiteCargaHoraria.indexOf($("#tpItemAval").val()) !== - 1 ? $("#divCargaHorariaMin").show() : $("#divCargaHorariaMin").hide();
                                };
                                $("#tpItemAval").change(gatilhoTpAval);
                                gatilhoTpAval();<?php } ?>
<?php } ?>

                $("#formCadastro").validate({
                submitHandler: function(form) {
                // tentando validaçao de item e area
                $("#divErCriarItem").hide();
                        $.ajax({
                        type: "POST",
                                url: getURLServidor() + "/controle/CTAjax.php?val=itemAval",
                                data: {"idProcesso": '<?php print $processo->getPRC_ID_PROCESSO(); ?>', "idCategoriaAval": '<?php print $categoriaAval->getCAP_ID_CATEGORIA_AVAL(); ?>', "tpItemAval": $("#tpItemAval").val(), "idAreaConh": $("#idAreaConh").val(), "idSubareaConh": $("#idSubareaConh").val(), "stFormacao": $("#stFormacao").val(), "segGraduacao": $("input[name=segGraduacao]:checked").val(), "tpExclusivo": $("input[name=tpExclusivo]:checked").val(), "cargaHorariaMin": $("#cargaHorariaMin").val(), "dsItemExt": $("#dsItemExt").val(), "edicao": <?php $edicao ? print "true" : print "false"; ?>, "idItemAval":<?php $edicao ? print $itemAval->getIAP_ID_ITEM_AVAL() : print "''"; ?>},
                                dataType: "json",
                                success: function(json) {
                                // caso validou
                                if (json['situacao']) {
                                // processa o submit
                                // 
                                //evitar repetiçao do botao
                                mostrarMensagem();
                                        $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                                        form.submit();
                                } else {
                                // caso de mensagem direto do sistema
                                if (json['msg'] != "")
                                {
                                alert(json['msg']);
                                        return false;
                                } else {
                                // exibe msg de erro e aborta operaçao de submit
                                $("#divErCriarItem").show();
                                        return false;
                                }
                                }
                                },
                                error: function(xhr, ajaxOptions, thrownError) {
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
                        tpItemAval: {
                        required: true
                        }
<?php if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) { ?>
                            , pontuacaoMax: {
                            required: function(){
                            return mostrarPontuacaoMax();
                            },
                                    min: 0.01,
                                    max: function(){
                                    return parseFloat($("#idNotaMaxCategoria").val());
                                    }
                            }
    <?php if ($categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_AVAL_EXTERNA) { ?>
                                , dsItemExt:{
                                required: true,
                                        maxlength: 30
                                }
    <?php } ?>
    <?php if ($categoriaAval->isAvalAutomatica()) { ?>
                                , pontuacao: {
                                required: true,
                                        min: 0.01,
                                        max: function(){
                                        if (mostrarPontuacaoMax())
                                        {
                                        return parseFloat($("#pontuacaoMax").val());
                                        } else{
                                        return parseFloat($("#idNotaMaxCategoria").val());
                                        }
                                        }
                                }
        <?php if (!$categoriaAval->isCategoriaExclusiva()) { ?>
                                    , idGrupo:{
                                    required: true
                                    }
            <?php
        }
    }
    ?>
    <?php if ($categoriaAval->isAvalAutomatica() && $categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_TITULACAO) { ?>
                                , stFormacao:{
                                required: true
                                }, cargaHorariaMin:{
                                min: '1'
                                }
    <?php } ?>
<?php } ?>}, messages: {
<?php
if ($categoriaAval->isAvalAutomatica()) {
    $virgula = TRUE;
    ?>
                    pontuacao: {
                    max: "A pontuação do item deve ser menor que a pontuação máxima."
                    }
    <?php
}
?>
<?php if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
    ?>
    <?php if (isset($virgula) && $virgula) {
        ?>,
    <?php } ?>
                    pontuacaoMax: {
                    max: "A pontuação máxima do item deve ser menor que a pontuação máxima da categoria."
                    }
<?php } ?>
                }
                });
                });
    </script>
</html>

