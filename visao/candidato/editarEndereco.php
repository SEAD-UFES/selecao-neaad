<!DOCTYPE html>
<html>
    <head>      
        <title>Endereço - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        include_once ($CFG->rpasta . "/util/sessao.php");
        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // buscando endereços
        include_once ($CFG->rpasta . "/controle/CTCandidato.php");
        include_once ($CFG->rpasta . "/util/selects.php");
        $endRes = buscarEnderecoCandPorIdUsuarioCT(getIdUsuarioLogado(), Endereco::$TIPO_RESIDENCIAL);
        $endCom = buscarEnderecoCandPorIdUsuarioCT(getIdUsuarioLogado(), Endereco::$TIPO_COMERCIAL);
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

                <?php include ($CFG->rpasta . "/include/tutorial.php"); ?>

                <div id="breadcrumb">
                    <h1>Você está em: Candidato > <strong>Endereço</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-md-12 col-sm-12 col-xs-12 m02">
                    <form id="formEndereco" class="form-horizontal" method="post" action='<?php print $CFG->rwww . "/controle/CTCandidato.php?acao=editarEndereco" ?>' >
                        <input type="hidden" name="valido" value="ctcandidato">
                        <input type="hidden" name="<?php echo Candidato::$PARAM_PREENC_REVISAO; ?>" value="<?php isset($_GET[Candidato::$PARAM_PREENC_REVISAO]) ? print Candidato::$PREENC_CONTATO : print ""; ?>">

                        <input type="hidden" id='cepAnteriorRes'name="cepAnteriorRes" value="">
                        <input type="hidden" id='erroAnteriorRes'name="erroAnteriorRes" value="">
                        <input type="hidden" id='focoRes'name="focoRes" value="">

                        <fieldset>
                            <legend>Endereço Residencial</legend>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">CEP: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" id="nrCepRes" name="nrCepRes" size="10" type="text" value="<?php print $endRes->getEND_NR_CEP(); ?>">
                                    <label id='nrCepResErro' class="error" style="display:none"></label>
                                    <a target="_blank" href=" http://www.buscacep.correios.com.br/servicos/dnec/index.do">Não sei meu CEP</a>
                                    <div id="divEsperaCEP" style="display: none">
                                        <span>Aguarde, Carregando...</span>
                                    </div>
                                </div>
                            </div>

                            <div id="enderecoRes" style="display: none">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Logradouro: *</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" name="nmLogradouroRes" type="text" id="nmLogradouroRes" size="50" maxlength="100" value="<?php print $endRes->getEND_NM_LOGRADOURO(); ?>" placeholder='Logradouro'>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Número: *</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" name="nrNumeroRes" type="text" id="nrNumeroRes" size="5" maxlength="20" value="<?php print $endRes->getEND_NR_NUMERO(); ?>" placeholder='Número'>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Bairro: *</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" name="nmBairroRes" type="text" id="nmBairroRes" size="50" maxlength="100" value="<?php print $endRes->getEND_NM_BAIRRO(); ?>" placeholder='Bairro'>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Estado: *</label>

                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <span><?php impressaoEstado($endRes->getEST_ID_UF(), "idEstadoRes"); ?></span>
                                        <div id="divEsperaCidade" style="display: none">
                                            <span>Aguarde, Carregando...</span>
                                        </div>
                                    </div>
                                </div>

                                <div id="divListaCidade" style="display: none">
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Cidade: *</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <select class="form-control" name="idCidadeRes" id="idCidadeRes"></select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Complemento:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <textarea class="form-control" cols="60" rows="4" name="dsComplementoRes" id="dsComplementoRes"><?php print $endRes->getEND_DS_COMPLEMENTO(); ?></textarea>
                                        <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>                                    
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="m02">
                            <legend>Endereço Comercial</legend>

                            <input type="hidden" id='cepAnteriorCom'name="cepAnteriorCom" value="">
                            <input type="hidden" id='erroAnteriorCom'name="erroAnteriorCom" value="">
                            <input type="hidden" id='focoCom'name="focoCom" value="">

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">CEP:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" id="nrCepCom" name="nrCepCom" size="10" type="text" value="<?php print $endCom->getEND_NR_CEP(); ?>">
                                    <label id='nrCepComErro' class="error" style="display:none"></label>
                                    <a target="_blank" href=" http://www.buscacep.correios.com.br/servicos/dnec/index.do">Não sei meu CEP</a>
                                    <div id="divEsperaCEPCom" style="display: none">
                                        <span>Aguarde, Carregando...</span>
                                    </div>
                                </div>
                            </div>
                            <div id="enderecoCom" style="display: none">
                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Logradouro*:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" name="nmLogradouroCom" type="text" id="nmLogradouroCom" size="50" maxlength="100" value="<?php print $endCom->getEND_NM_LOGRADOURO(); ?>" placeholder='Logradouro'>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Número:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" name="nrNumeroCom" type="text" id="nrNumeroCom" size="5" maxlength="20" value="<?php print $endCom->getEND_NR_NUMERO(); ?>" placeholder='Número'>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Bairro*:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" name="nmBairroCom" type="text" id="nmBairroCom" size="50" maxlength="100" value="<?php print $endCom->getEND_NM_BAIRRO(); ?>" placeholder='Bairro'>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Estado*:</label>

                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <span><?php impressaoEstado($endCom->getEST_ID_UF(), "idEstadoCom"); ?></span>
                                        <div id="divEsperaCidadeCom" style="display: none">
                                            <span>Aguarde, Carregando...</span>
                                        </div>
                                    </div>
                                </div>

                                <div id="divListaCidadeCom" style="display: none">
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Cidade*:</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <select class="form-control" name="idCidadeCom" id="idCidadeCom"></select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Complemento:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <textarea class="form-control" cols="60" rows="4" name="dsComplementoCom" id="dsComplementoCom"><?php print $endCom->getEND_DS_COMPLEMENTO(); ?></textarea>
                                        <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>                                    
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                <button class="btn btn-default" type="button" onclick="javascript: window.location = '<?php echo "$CFG->rwww/inicio"; ?>';">Voltar</button>
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
        carregaScript('jquery.maskedinput');
        carregaScript('cep');
        carregaScript('ajax');
        ?>
    </body>
    <script type = "text/javascript">
        $(document).ready(function () {
            $("#formEndereco").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                    form.submit();
                },
                rules: {
                    nrCepRes: {
                        required: true
                    },
                    nmLogradouroRes: {
                        required: function (element) {
                            return !camposVazios(['nmLogradouroRes', 'nmBairroRes', 'idEstadoRes', 'idCidadeRes', 'nrCepRes', 'dsComplementoRes', 'NrNumeroRes']);
                        },
                        minlength: 3
                    }, nmBairroRes: {
                        required: function (element) {
                            return !camposVazios(['nmLogradouroRes', 'nmBairroRes', 'idEstadoRes', 'idCidadeRes', 'nrCepRes', 'dsComplementoRes', 'NrNumeroRes']);
                        },
                        minlength: 3
                    }, idEstadoRes: {
                        required: function (element) {
                            return !camposVazios(['nmLogradouroRes', 'nmBairroRes', 'idEstadoRes', 'idCidadeRes', 'nrCepRes', 'dsComplementoRes', 'NrNumeroRes']);
                        }
                    }, idCidadeRes: {
                        required: function (element) {
                            return !camposVazios(['nmLogradouroRes', 'nmBairroRes', 'idEstadoRes', 'idCidadeRes', 'nrCepRes', 'dsComplementoRes', 'NrNumeroRes']);
                        }
                    }, nmLogradouroCom: {
                        required: function (element) {
                            return !camposVazios(['nmLogradouroCom', 'nmBairroCom', 'idEstadoCom', 'idCidadeCom', 'nrCepCom', 'dsComplementoCom', 'NrNumeroCom']);
                        },
                        minlength: 3
                    }, nmBairroCom: {
                        required: function (element) {
                            return !camposVazios(['nmLogradouroCom', 'nmBairroCom', 'idEstadoCom', 'idCidadeCom', 'nrCepCom', 'dsComplementoCom', 'NrNumeroCom']);
                        },
                        minlength: 3
                    }, idEstadoCom: {
                        required: function (element) {
                            return !camposVazios(['nmLogradouroCom', 'nmBairroCom', 'idEstadoCom', 'idCidadeCom', 'nrCepCom', 'dsComplementoCom', 'NrNumeroCom']);
                        }
                    }, idCidadeCom: {
                        required: function (element) {
                            return !camposVazios(['nmLogradouroCom', 'nmBairroCom', 'idEstadoCom', 'idCidadeCom', 'nrCepCom', 'dsComplementoCom', 'NrNumeroCom']);
                        }
                    }}, messages: {
                }
            }
            );

            //adicionando máscara
            $("#nrCepRes").mask("99.999-999");
            $("#nrCepCom").mask("99.999-999");

            // adicionando gatilho para textArea residencial
            var gatilhoComp = adicionaContadorTextArea(250, "dsComplementoRes", "qtCaracteres");

            // adicionando gatilho para textArea comercial
            var gatilhoCompCom = adicionaContadorTextArea(250, "dsComplementoCom", "qtCaracteresCom");

            // tratando gatilho de ajax para cidade do endereço residencial
            function getParamsCidadeRes()
            {
                return {'cargaSelect': "cidade", 'idUf': $("#idEstadoRes").val()};
            }

            var gatilhoCidade = adicionaGatilhoAjaxSelect("idEstadoRes", getIdSelectSelecione(), "divEsperaCidade", "divListaCidade", "idCidadeRes", "<?php print $endRes->getCID_ID_CIDADE() ?>", getParamsCidadeRes);

            // tratando gatilho de ajax para cidade do endereço comercial
            function getParamsCidadeCom()
            {
                return {'cargaSelect': "cidade", 'idUf': $("#idEstadoCom").val()};
            }

            var gatilhoCidadeCom = adicionaGatilhoAjaxSelect("idEstadoCom", getIdSelectSelecione(), "divEsperaCidadeCom", "divListaCidadeCom", "idCidadeCom", "<?php print $endCom->getCID_ID_CIDADE() ?>", getParamsCidadeCom);

            // adicionando gatilho para CEP residencial
            adicionaGatilhoAjaxCep('divEsperaCEP', 'enderecoRes', 'Res', gatilhoCidade, gatilhoComp);

            // adicionando gatilho para CEP comercial
            adicionaGatilhoAjaxCep('divEsperaCEPCom', 'enderecoCom', 'Com', gatilhoCidadeCom, gatilhoCompCom);

        });
    </script>
</html>

