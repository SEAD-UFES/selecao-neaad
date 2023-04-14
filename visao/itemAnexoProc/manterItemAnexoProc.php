<!DOCTYPE html>
<html>
    <head>     
        <title>Manter Respostas de Inf. Comp. do Processo - Seleção EAD</title>
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
        if (!isset($_GET['idGrupoAnexoProc'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando grupo e processo
        $grupoAnexoProc = buscarGrupoAnexoProcPorIdCT($_GET['idGrupoAnexoProc']);
        $processo = buscarProcessoComPermissaoCT($grupoAnexoProc->getPRC_ID_PROCESSO());

        // casos de não poder editar
        if (!permiteManterGrupoAnexoProcCT($processo)) {
            new Mensagem("Inf. Complementar não pode ser alterada.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // possui opções
        if (!$grupoAnexoProc->possuiOpcoesResposta()) {
            new Mensagem("Inf. Complementar não possui opções de resposta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando itens de resposta
        $listaItemAnexoProc = buscarItemPorGrupoCT($grupoAnexoProc->getGAP_ID_GRUPO_PROC());

        // se estiver vazio, criar item inicial
        if (Util::vazioNulo($listaItemAnexoProc)) {
            $listaItemAnexoProc [] = ItemAnexoProc::getItemAnexoProcPadrao();
            $respMultipla = FALSE;
        } else {
            $respMultipla = $listaItemAnexoProc[0]->isRespostaMultipla();
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_INF_COMP; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong>Opções Inf. Comp.</strong></h1>
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

                <div class="contents col-full">
                    <form class="form-horizontal" id="formCadastro" method="post" action="<?php print $CFG->rwww; ?>/controle/CTManutencaoProcesso.php?acao=manterItemAnexoProc">
                        <input type="hidden" name="valido" value="ctmanutencaoprocesso">
                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" name="idGrupoAnexoProc" value="<?php print $grupoAnexoProc->getGAP_ID_GRUPO_PROC(); ?>">

                        <fieldset class="completo">
                            <legend>Pergunta</legend>
                            <div class="col-full">
                                <p><b>Nome:</b> <?php print $grupoAnexoProc->getGAP_NM_GRUPO(); ?></p>
                                <p><b>Obrigatória:</b> <?php print $grupoAnexoProc->getDsGrupoObrigatorio(); ?></p>
                                <p><b>Avaliação:</b> <?php print $grupoAnexoProc->getDsTipoAvalObj(); ?></p>
                                <p>
                                    <label class="control-label" style="float:left;margin-right:5px;">
                                        <b>Resposta Múltipla
                                            <i class="fa fa-question-circle" title="Informe se o candidato poderá marcar mais de uma opção."></i>:
                                        </b>
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" <?php $respMultipla ? print "checked" : print ""; ?> id="respostaMultipla" name="respostaMultipla" value="<?php print FLAG_BD_SIM; ?>">
                                    </label>
                                </p>
                            </div>
                        </fieldset>

                        <fieldset class="completo m04">
                            <legend>Opções de Resposta</legend>

                            <?php print Util::$MSG_CAMPO_OBRIG; ?> 

                            <div class="col-full m02">
                                <input type="button" id="addResposta" class="btn btn-primary" value="Novo Item">
                            </div>

                            <div class="col-full m02">
                                <table id="tabelaResposta" class="completo">
                                    <tbody>
                                        <?php
                                        $i = 0;
                                        foreach ($listaItemAnexoProc as $itemAnexoProc) {
                                            // recuperando complemento, se houver
                                            if ($itemAnexoProc->temComplemento()) {
                                                $listaCompItem = buscarSubitemPorItemCT($itemAnexoProc->getIAP_ID_ITEM());
                                                $tpComp = ItemAnexoProc::getTpCompTela($listaCompItem);
                                            } else {
                                                $listaCompItem = array(SubitemAnexoProc::getSubitemAnexoProcPadrao());
                                                $tpComp = "";
                                            }
                                            ?>
                                            <tr id="linhaResposta<?php print $i; ?>">
                                                <td class="textoEsquerdaForcado">     
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Ordem *</th>
                                                                <th>Nome *</th>
                                                                <th>Cód. de resposta <i class="fa fa-question-circle" title="Palavra única (sem acentos, espaços e caracteres especiais), representante da resposta."></i> *</th>
                                                                <th>Complemento <i class="fa fa-question-circle" title="Informe se, caso seja selecionado esta resposta, seja necessário um complemento."></i></th>
                                                                <th class="botao"><i class='fa fa-trash-o'></i></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><input required class="form-control" type ="text" name ="respOrdemItem[<?php print $i; ?>]" size="4" maxlength = "2" value="<?php print $itemAnexoProc->getIAP_NR_ORDEM_EXIBICAO(); ?>"></td>
                                                                <td><input required class="form-control" type ="text" name ="respNmItem[<?php print $i; ?>]" size="30" maxlength = "255" value="<?php print $itemAnexoProc->getIAP_NM_ITEM(); ?>"></td>
                                                                <td><input required class="form-control tudo-normal" onblur="javascript: removeEspacoAcento(this);" type ="text" name ="respDsItem[<?php print $i; ?>]" size="15" maxlength = "100" value="<?php print $itemAnexoProc->getIAP_DS_ITEM(); ?>"></td>
                                                                <td><input style="height:20px;width:20px;margin:0 auto;" class="form-control" onclick="javascript: mostraComplemento(this)" type="checkbox" <?php $itemAnexoProc->temComplemento() ? print "checked" : print ""; ?> id="compItem<?php print $i; ?>" name="respCompItem[<?php print $i; ?>]" value="<?php print FLAG_BD_SIM; ?>"></td>
                                                                <td class="botao"><a onclick="javascript: removeLinhaItem(this);"><i class='fa fa-trash-o'></i></a></td>
                                                            </tr>
                                                            <tr id="divcompItem<?php print $i; ?>" style="<?php !$itemAnexoProc->temComplemento() ? print "display: none" : print ""; ?>" > 
                                                                <td class="textoEsquerdaForcado" colspan="5">
                                                                    <fieldset class="itemInterno">
                                                                        <legend style="height: 40px">
                                                                            <small>
                                                                                <span style="margin-top: 7px;" class="pull-left">Complemento do item de resposta</span>
                                                                                <span class="pull-right">
                                                                                    Tipo:
                                                                                    <?php impressaoTpItemAnexoProcTela($tpComp, "tpcompItem$i", FALSE, "onchange=\"javascript: mostraTpCompItem(this)\" required"); ?>
                                                                                    Obrigatório:
                                                                                    <input type="checkbox" <?php $itemAnexoProc->isObrigatorio() ? print "checked" : print ""; ?> id="obrigatorioComp<?php print $i; ?>" name ="obrigatorioComp<?php print $i; ?>" value="<?php print FLAG_BD_SIM; ?>">
                                                                                </span>
                                                                            </small>
                                                                        </legend>
                                                                        <div id="divTextotpcompItem<?php print $i; ?>" style="<?php $tpComp == ItemAnexoProc::$TIPO_TEL_TEXTO ? print "" : print "display: none"; ?>">
                                                                            <div class="form-group">
                                                                                <label class="col-sm-6 control-label">Descrição do complemento: *</label>
                                                                                <div class="col-sm-5">
                                                                                    <input required class="form-control" type="text" id="nmCompItemTexto<?php print $i; ?>" name="nmCompItemTexto<?php print $i; ?>" size="30" maxlength = "255"  value="<?php print $listaCompItem[0]->getSAP_NM_SUBITEM(); ?>">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="col-sm-6 control-label">Tamanho máximo da resposta do complemento: *</label>
                                                                                <div class="col-sm-5">
                                                                                    <input required class="form-control" type="text" id="tamMaxCompItem<?php print $i; ?>" name="tamMaxCompItem<?php print $i; ?>" size="4" maxlength = "4" value="<?php print $listaCompItem[0]->getSAP_NR_MAX_CARACTER(); ?>">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div id="divtpcompItem<?php print $i; ?>" style="<?php ($tpComp == ItemAnexoProc::$TIPO_TEL_RADIO || $tpComp == ItemAnexoProc::$TIPO_TEL_CHECKBOX) ? print "" : print "display: none"; ?>">
                                                                            <input type="button" id="addCompItem<?php print $i; ?>" class="btn btn-primary" value="Novo Item de Complemento">
                                                                            <br>
                                                                            <br>
                                                                            <table id="tabelaCompItem<?php print $i; ?>" class="table table-hover table-bordered">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>Ordem *</th>
                                                                                        <th>Nome *</th>
                                                                                        <th>Cód. de resposta <i class="fa fa-question-circle" title="Palavra única (sem acentos, espaços e caracteres especiais), representante da resposta."></i> *</td>
                                                                                        <th class="botao"><i class='fa fa-trash-o'></i></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php
                                                                                    // iterando para criar os complementos
                                                                                    $j = 0;
                                                                                    foreach ($listaCompItem as $subitemAnexoProc) {
                                                                                        ?>
                                                                                        <tr id="linhaCompItem<?php print $j; ?>">
                                                                                            <td><input required class="form-control" type ="text" name="compOrdemCompItem<?php print $i; ?>[<?php print $j; ?>]" size="4" maxlength = "2" value="<?php print $subitemAnexoProc->getSAP_NR_ORDEM_EXIBICAO(); ?>"></td>
                                                                                            <td><input required class="form-control" type ="text" name="compNmCompItem<?php print $i; ?>[<?php print $j; ?>]" size="30" maxlength = "255" value="<?php print $subitemAnexoProc->getSAP_NM_SUBITEM(); ?>"></td>
                                                                                            <td><input required class="form-control tudo-normal" onblur="javascript: removeEspacoAcento(this);" type ="text" name="compDsCompItem<?php print $i; ?>[<?php print $j; ?>]" size="15" maxlength = "100" value="<?php print $subitemAnexoProc->getSAP_DS_SUBITEM(); ?>"></td>
                                                                                            <td class="botao"><a onclick="javascript: removeLinhaCompItem(this);"><i class='fa fa-trash-o'></i></a></td>
                                                                                        </tr>
                                                                                        <?php
                                                                                        $j++;
                                                                                    }
                                                                                    ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </fieldset> 
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?php
                                            $i++; // incrementa contador
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>

                        <div id="divBotoes">
                            <div class="form-group">
                                <div class="col-sm-6">
                                    <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                    <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_INF_COMP; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>';" value="Voltar">
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
        carregaScript("jquery.price_format");
        carregaScript("jquery.maskedinput");
        ?>
    </body>

    <script type="text/javascript">

        // funcoes auxiliares
        function trocaNumLinha(strId, novoNum)
        {
            return strId.replace(/[0-9]*/g, '') + novoNum;
        }

        function trocaNumVetLinha(strId, novoNum)
        {
            return strId.replace(/[0-9\[\]]*/g, '') + novoNum + "[" + novoNum + "]";
        }

        function substituiIndiceInput(nomeInput, novoIndice)
        {
            var nm = nomeInput.replace(/\[[0-9]*\]/g, '');
            return nm + "[" + novoIndice + "]";
        }

        function linhaItemAtual(strId)
        {
            return strId.replace(/^[a-zA-Z]*/g, '');
        }

        function removeEspacoAcento(objInput)
        {
            $(objInput).val(retiraAcentos($(objInput).val().replace(/ /g, "")));
        }

        function acertaIdsTabCompItem(idTabela)
        {
            // acertando ids
            var i = 0;
            $("#" + idTabela + " > tbody > tr").each(function () {
                $(this).attr("id", "linhaCompItem" + i);

                $(this).find("input[type='text']").each(function () {
                    $(this).rules("remove", "required");

                    $(this).attr("name", substituiIndiceInput($(this).attr("name"), i));

                    $(this).rules("add", {required: true});
                });

                i++;
            });
        }

        function acertaIdsTabItem(idTabela)
        {
            // acertando ids
            var i = 0;
            $("#" + idTabela + " > tbody > tr").each(function () {
                $(this).attr("id", "linhaResposta" + i);

                $(this).find("input[name^='resp']").each(function () {
                    if (!$(this).attr("name").match("^respCompItem"))
                    {
                        $(this).rules("remove", "required");
                    }
                    $(this).attr("name", substituiIndiceInput($(this).attr("name"), i));

                    if (!$(this).attr("name").match("^respCompItem"))
                    {
                        $(this).rules("add", {required: true});
                    }
                });

                $(this).find("[id]").each(function () {
                    $(this).attr("id", trocaNumLinha($(this).attr("id"), i));
                });

                $(this).find("input[name^='comp']").each(function () {
                    $(this).rules("remove", "required");
                    $(this).attr("name", trocaNumVetLinha($(this).attr("name"), i));
                    $(this).rules("add", {required: true});
                });

                $(this).find("#obrigatorioComp" + i).attr("name", trocaNumLinha('obrigatorioComp', i));
                $(this).find("#tpcompItem" + i).attr("name", trocaNumLinha('tpcompItem', i));
                $(this).find("#tpcompItem" + i).rules("add", {required: true});

                $(this).find("#nmCompItemTexto" + i).attr("name", trocaNumLinha('nmCompItemTexto', i));
                $(this).find("#nmCompItemTexto" + i).rules("add", {required: true});

                $(this).find("#tamMaxCompItem" + i).attr("name", trocaNumLinha('tamMaxCompItem', i));
                $(this).find("#tamMaxCompItem" + i).rules("add", {
                    required: true,
                    max: <?php print RespAnexoProc::$TAM_LIMITE_RESP; ?>,
                    messages: {
                        max: "O tamanho máximo para a resposta é <?php print RespAnexoProc::$TAM_LIMITE_RESP; ?> caracteres."
                    }
                });

                i++;
            });
        }

        function mostraTpCompItem(objSelect) {
            if ($(objSelect).val() == '') {
                $('#divTexto' + $(objSelect).attr('id')).hide();
                $('#div' + $(objSelect).attr('id')).hide();
                return;
            }
            if ($(objSelect).val() == '<?php print ItemAnexoProc::$TIPO_TEL_TEXTO; ?>') {
                $('#div' + $(objSelect).attr('id')).hide();
                $('#divTexto' + $(objSelect).attr('id')).show();
                return;
            }
            if ($(objSelect).val() == '<?php print ItemAnexoProc::$TIPO_TEL_RADIO; ?>' || $(objSelect).val() == '<?php print ItemAnexoProc::$TIPO_TEL_CHECKBOX; ?>') {
                $('#divTexto' + $(objSelect).attr('id')).hide();
                $('#div' + $(objSelect).attr('id')).show();
                return;
            }
            alert("Tipo de complemento desconhecido");
        }

        function mostraComplemento(objCheckBox)
        {
            $(objCheckBox).is(':checked') ? $('#div' + $(objCheckBox).attr('id')).show() : $('#div' + $(objCheckBox).attr('id')).hide();
        }

        // objLinha é uma tr da resposta
        function resetaEstadoLinhaItem(objLinha, numLinha)
        {
            objLinha.find("input[type='text'],select").each(function () {
                $(this).val("");
            });
            objLinha.find("input:checkbox").removeAttr('checked');
            mostraTpCompItem(objLinha.find("select"));
            mostraComplemento(objLinha.find("input:checkbox"));
            objLinha.find("#tabelaCompItem" + numLinha + " > tbody > tr:gt(0)").remove();
            acertaIdsTabCompItem("tabelaCompItem" + numLinha);
        }

        // script que remove uma linha do complemento de um dado item
        function removeLinhaCompItem(objBt)
        {
            var idTabela = $(objBt).parent().parent().parent().parent().attr("id");
            var qtComp = $("#" + idTabela + " tbody tr").size();

            // se for 1, só limpar campos de input
            if (qtComp == 1)
            {
                $(objBt).parent().parent().find("input").val("");
                alert("O complemento deve possuir pelo menos um item.");
            } else {
                // removendo validador
                $(objBt).parent().parent().find("input[type='text']").each(function () {
                    $(this).rules("remove", "required");
                });

                // removendo item
                $(objBt).parent().parent().remove();

                acertaIdsTabCompItem(idTabela);
            }
        }

        // script que remove uma linha de resposta
        function removeLinhaItem(objBt)
        {
            var idTabela = "tabelaResposta";
            var qtComp = $("#" + idTabela + " > tbody > tr").size();
            var objLinhaResp = $(objBt).parent().parent().parent().parent().parent().parent();
            var linhaAtual = linhaItemAtual($(objLinhaResp).attr("id"));

            // se for 1, só resetar 
            if (qtComp == 1)
            {
                resetaEstadoLinhaItem(objLinhaResp, linhaAtual);
                alert("A pergunta deve possuir pelo menos um item de resposta.");
            } else {
                // removendo validador
                $(objLinhaResp).find("input[type='text'], select").each(function () {
                    $(this).rules("remove", "required");
                });

                // removendo item
                $(objLinhaResp).remove();

                acertaIdsTabItem(idTabela);
            }
        }

        function _validaOrdenacao(arrayOrdem)
        {
            // executa validacao
            arrayOrdem.sort();
            for (var i = 1; i <= arrayOrdem.length; i++)
            {
                if (i != arrayOrdem[i - 1]) {
                    return false;
                }
            }
            return true;
        }

        function _validaUnicidade(arrayBusca, novoItem)
        {
            if (arrayBusca.indexOf(novoItem) === -1)
            {
                // item inexistente
                arrayBusca[arrayBusca.length] = novoItem;
                return true;
            }
            // item repetido
            return false;
        }

        $(document).ready(function () {

            $("#formCadastro").validate({
                submitHandler: function (form) {
                    // verificando se a ordenacao e a unicidade dos itens esta correta
                    var aOrdemResp = [];
                    $("input[name^='respOrdemItem']").each(function () {
                        aOrdemResp[aOrdemResp.length] = $(this).val();
                    });
                    if (!_validaOrdenacao(aOrdemResp))
                    {
                        alert("A ordenação dos itens de resposta está inconsistente.\n\nVerifique e tente novamente!");
                        return;
                    }


                    var vetUnicidade = [];
                    var mostraErro = false;
                    $("input[name^='respNmItem']").each(function () {
                        if (!_validaUnicidade(vetUnicidade, $(this).val())) {
                            mostraErro = true;
                        }
                    });
                    if (mostraErro) {
                        alert("Há alguma opção de resposta repetida (com o mesmo nome).\n\nVerifique e tente novamente!");
                        return;
                    }

                    vetUnicidade.length = 0; // zerando vetor
                    $("input[name^='respDsItem']").each(function () {
                        if (!_validaUnicidade(vetUnicidade, $(this).val())) {
                            mostraErro = true;
                        }
                    });
                    if (mostraErro) {
                        alert("Há algum código de resposta repetido (com o mesmo nome).\n\nVerifique e tente novamente!");
                        return;
                    }



                    // verificando a ordenação e a unicidade dos complementos
                    for (var i = 0; i < aOrdemResp.length; i++)
                    {
                        if ($("#compItem" + i).is(":checked") && ($("#tpcompItem" + i).val() === '<?php print ItemAnexoProc::$TIPO_TEL_CHECKBOX; ?>' || $("#tpcompItem" + i).val() === '<?php print ItemAnexoProc::$TIPO_TEL_RADIO; ?>')) {
                            var arrayVal = [];
                            vetUnicidade.length = 0; // zerando vetor

                            $("input[name^='compOrdemCompItem" + i + "']").each(function () {
                                arrayVal[arrayVal.length] = $(this).val();
                            });
                            if (!_validaOrdenacao(arrayVal))
                            {
                                alert("A ordenação dos complementos do item de resposta de Ordem '" + (i + 1) + "' está inconsistente.\n\nVerifique e tente novamente!");
                                return;
                            }


                            $("input[name^='compNmCompItem" + i + "']").each(function () {
                                if (!_validaUnicidade(vetUnicidade, $(this).val())) {
                                    mostraErro = true;
                                }
                            });
                            if (mostraErro) {
                                alert("Os complementos do item de resposta de Ordem '" + (i + 1) + "' contém nome repetido.\n\nVerifique e tente novamente!");
                                return;
                            }


                            $("input[name^='compDsCompItem" + i + "']").each(function () {
                                if (!_validaUnicidade(vetUnicidade, $(this).val())) {
                                    mostraErro = true;
                                }
                            });
                            if (mostraErro) {
                                alert("Os complementos do item de resposta de Ordem '" + (i + 1) + "' contém código de resposta repetido.\n\nVerifique e tente novamente!");
                                return;
                            }

                        }
                    }

                    //evitar repetiçao do botao
                    mostrarMensagem();
                    //                    $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                    form.submit();

                },
                rules: {
                }, messages: {
                }
            });

            function gatAddCompItem(objBt) {
                var linhaItem = linhaItemAtual($(objBt).attr("id"));
                var linhaCompItem = linhaItemAtual($("#tabelaCompItem" + linhaItem + "  tr:last").attr("id"));

                // recuperando tabela do item
                var linhaClone = $("#tabelaCompItem" + linhaItem + " tr:last").clone();

                // alterando id do elemento e processando itens
                linhaCompItem++;
                linhaClone.attr("id", "linhaCompItem" + linhaCompItem);
                linhaClone.find("input[type='text']").each(function () {
                    $(this).val("");
                    $(this).attr("name", substituiIndiceInput($(this).attr("name"), linhaCompItem));
                });
                linhaClone.find("[name^='compOrdemCompItem']").val(linhaCompItem + 1);
                linhaClone.find("[name^='compOrdemCompItem']").mask("9?9", {placeholder: " "});

                // adicionando
                linhaClone.insertAfter($("#tabelaCompItem" + linhaItem + " tr:last"));

                // adicionando validador
                linhaClone.find("input[type='text']").each(function () {
                    $(this).rules("add", {required: true});
                });
            }

            // mascara para ordem
            $("[name^='compOrdemCompItem']").mask("9?9", {placeholder: " "});
            $("[name^='respOrdemItem']").mask("9?9", {placeholder: " "});

            // mascara e validador para quantidade máxima de caracteres
            $("[name^='tamMaxCompItem']").mask("9?999", {placeholder: " "});
            $("[name^='tamMaxCompItem']").rules("add", {
                required: true, max: <?php print RespAnexoProc::$TAM_LIMITE_RESP; ?>,
                messages: {
                    max: "O tamanho máximo para a resposta é <?php print RespAnexoProc::$TAM_LIMITE_RESP; ?> caracteres."
                }
            });

            // adicionando trigger para adição de item de resposta
            $("#addResposta").click(function () {
                // recuperando tabela do item
                var linhaClone = $("#tabelaResposta > tbody > tr:last").clone();
                var qtResp = linhaItemAtual(linhaClone.attr("id"));
                qtResp++;

                // alterando id do elemento e processando itens
                linhaClone.attr("id", "linhaResposta" + qtResp);

                linhaClone.find("input[name^='resp']").each(function () {
                    $(this).attr("name", substituiIndiceInput($(this).attr("name"), qtResp));
                });

                linhaClone.find("[id]").each(function () {
                    $(this).attr("id", trocaNumLinha($(this).attr("id"), qtResp));
                });

                linhaClone.find("input[name^='comp']").each(function () {
                    $(this).attr("name", trocaNumVetLinha($(this).attr("name"), qtResp));
                });
                linhaClone.find("#tpcompItem" + qtResp).attr("name", trocaNumLinha('tpcompItem', qtResp));
                linhaClone.find("#tamMaxCompItem" + qtResp).attr("name", trocaNumLinha('tamMaxCompItem', qtResp));
                linhaClone.find("#nmCompItemTexto" + qtResp).attr("name", trocaNumLinha('nmCompItemTexto', qtResp));
                linhaClone.find("#obrigatorioComp" + qtResp).attr("name", trocaNumLinha('obrigatorioComp', qtResp));

                // adicionando
                linhaClone.insertAfter($("#tabelaResposta > tbody > tr:last"));

                // adicionando validador
                linhaClone.find("input[type='text'][name^='resp']").each(function () {
                    $(this).rules("add", {required: true});
                });
                linhaClone.find("#tpcompItem" + qtResp).rules("add", {required: true});
                linhaClone.find("#nmCompItemTexto" + qtResp).rules("add", {required: true});
                linhaClone.find("#tamMaxCompItem" + qtResp).rules("add", {
                    required: true,
                    max: <?php print RespAnexoProc::$TAM_LIMITE_RESP; ?>,
                    messages: {
                        max: "O tamanho máximo para a resposta é <?php print RespAnexoProc::$TAM_LIMITE_RESP; ?> caracteres."
                    }
                });

                // inserindo gatilho
                $("#addCompItem" + qtResp).click(function () {
                    gatAddCompItem($(this));
                });

                // limpando campos e resetando estado
                resetaEstadoLinhaItem(linhaClone, qtResp);

                // colocando valor em ordem
                linhaClone.find("[name^='respOrdemItem']").val(qtResp + 1);
                linhaClone.find("[name^='respOrdemItem']").mask("9?9", {placeholder: " "});

                linhaClone.find("[name^='compOrdemCompItem']").val(1);
                linhaClone.find("[name^='compOrdemCompItem']").mask("9?9", {placeholder: " "});


            });

            // adicionando trigger para adição de complementos
            $("input[type=button][id^='addCompItem']").each(function () {
                $(this).click(function () {
                    gatAddCompItem($(this));
                });
            });



            function sucInsercaoInfComp() {
                $().toastmessage('showToast', {
                    text: '<b>Questão cadastrada com sucesso.</b> Agora falta configurar as opções de resposta...',
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

