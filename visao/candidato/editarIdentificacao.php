<!DOCTYPE html>
<html>
    <head>   
        <title>Identificação - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTCandidato.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //buscando dados do usuário
        $dadosLogin = getDadosLogin();
        $identUsu = buscarIdentCandPorIdUsuCT(getIdUsuarioLogado());

        // pre selecionando pais caso nao esteja selecionado
        if (Util::vazioNulo($identUsu->getIDC_NASC_PAIS())) {
            $identUsu->setIDC_NASC_PAIS(Pais::$PAIS_BRASIL);
        }

        // definindo id temporario de "outros"
        //nacionalidade
        if (!Util::vazioNulo($identUsu->getIDC_NM_NACIONALIDADE())) {
            $identUsu->setNAC_ID_NACIONALIDADE(ID_SELECT_OUTRO);
        }

        //ocupaçao
        if (!Util::vazioNulo($identUsu->getIDC_NM_OCUPACAO())) {
            $identUsu->setOCP_ID_OCUPACAO(ID_SELECT_OUTRO);
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

                <?php include ($CFG->rpasta . "/include/tutorial.php"); ?>

                <div id="breadcrumb">
                    <h1>Você está em: Candidato > <strong>Identificação</strong></h1>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">
                    <form id="formEditarIdent" class="form-horizontal" method="post" action=<?php print "$CFG->rwww/controle/CTCandidato.php?acao=editarIdentificacao" ?>>
                        <input type="hidden" name="valido" value="ctcandidato">
                        <input type="hidden" name="<?php echo Candidato::$PARAM_PREENC_REVISAO; ?>" value="<?php isset($_GET[Candidato::$PARAM_PREENC_REVISAO]) ? print Candidato::$PREENC_ENDERECO : print ""; ?>">
                        <input type="hidden" name="nrCpf" value="<?php print $identUsu->getNrCPFMascarado(); ?>">

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">CPF:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span class="form-control-static"> <?php print $identUsu->getNrCPFMascarado(); ?> </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome completo: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dsNome" type="text" id="dsNome" size="50" maxlength="100" value="<?php print $dadosLogin['dsNome']; ?>" placeholder='Nome completo' required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nacionalidade: *</label>
                            <div  class="col-xs-12 col-sm-8 col-md-8">
                                <div><?php impressaoNacionalidade($identUsu->getNAC_ID_NACIONALIDADE()); ?></div>
                            </div>
                        </div>

                        <div class="form-group" id="nacionalidadeOutra" style="display: <?php ($identUsu->getNAC_ID_NACIONALIDADE() == ID_SELECT_OUTRO) ? print "block" : print "none" ?>">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Outra Nacionalidade: *</label>
                            <div  class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dsNacionalidade" type="text" id="dsNacionalidade" size="50" maxlength="100" value="<?php print $identUsu->getIDC_NM_NACIONALIDADE(); ?>" placeholder='Outra Nacionalidade'>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Sexo: *</label>
                            <div  class="col-xs-12 col-sm-8 col-md-8">
                                <span><?php impressaoRadioSexo($identUsu->getIDC_DS_SEXO()); ?></span>
                                <label for="dsSexo" class="error" style="display:none"></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Etnia: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span><?php impressaoTipoRaca($identUsu->getIDC_TP_RACA()); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Estado Civil: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <span><?php impressaoTipoEstadoCivil($identUsu->getIDC_TP_ESTADO_CIVIL()); ?></span>
                            </div>
                        </div>

                        <div class="form-group" id="divNmConjuge" style="display: <?php ($identUsu->getIDC_TP_ESTADO_CIVIL() == IdentificacaoCandidato::$EST_CIVIL_CASADO || $identUsu->getIDC_TP_ESTADO_CIVIL() == IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL) ? print "block" : print "none" ?>">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome do Cônjuge: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="nmConjuge" type="text" id="nmConjuge" size="50" maxlength="100" value="<?php print $identUsu->getIDC_NM_CONJUGE(); ?>" placeholder='Nome do cônjuge'>
                            </div>
                        </div><br>

                        <fieldset class="m02"> 
                            <legend>Filiação</legend>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome do pai:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="filNmPai" type="text" id="filNmPai" size="50" maxlength="100" value="<?php print $identUsu->getIDC_FIL_NM_PAI(); ?>" placeholder='Nome do pai'>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome da mãe: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="filNmMae" type="text" id="filNmMae" size="50" maxlength="100" value="<?php print $identUsu->getIDC_FIL_NM_MAE(); ?>" placeholder='Nome da mãe' required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="m02">
                            <legend>Nascimento</legend> 

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">País: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <span><?php impressaoPais($identUsu->getIDC_NASC_PAIS(), "nasIdPais"); ?></span>
                                </div>
                            </div>

                            <div id="paisBrasil" style="display: <?php ($identUsu->getIDC_NASC_PAIS() == Pais::$PAIS_BRASIL) ? print "block" : print "none" ?>">

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Estado: *</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <span><?php impressaoEstado($identUsu->getIDC_NASC_ESTADO(), "nasIdEstado"); ?></span>
                                        <div id="divEsperaCidade" style="display: none">
                                            <br/>
                                            <span>Aguarde, Carregando...</span>
                                        </div>
                                    </div>
                                </div>

                                <div id="divListaCidade" style="display: none">
                                    <div class="form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Cidade: *</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <select class="form-control" name="nasIdCidade" id="nasIdCidade"></select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Data: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="nasData" type="text" id="nasData" size="10" maxlength="10" value="<?php print $identUsu->getIDC_NASC_DATA(); ?>" placeholder='Data de Nascimento' required>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset class="m02">
                            <legend>RG (Identidade)</legend>    

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Número: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="rgNr" type="text" id="rgNr" size="14" maxlength="20" value="<?php print $identUsu->getIDC_RG_NUMERO(); ?>" placeholder='Número da Identidade' required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Orgão emissor: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" class='tudo-maiusculo' name="rgOrgaoExp" type="text" id="rgOrgaoExp" size="14" maxlength="20" value="<?php print $identUsu->getIDC_RG_ORGAO_EXP(); ?>" placeholder='Orgão emissor da Identidade' required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Unidade Federativa: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <span><?php impressaoEstado($identUsu->getIDC_RG_UF(), "rgUf"); ?></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Data de emissão: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="rgDtEmissao" type="text" id="rgDtEmissao" size="10" maxlength="10" value="<?php print $identUsu->getIDC_RG_DT_EMISSAO(); ?>" placeholder='Data de emissão da Identidade' required>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset class="m02">  
                            <legend>Ocupação Principal</legend>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Ocupação: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <span><?php impressaoOcupacao($identUsu->getOCP_ID_OCUPACAO()); ?></span>
                                </div>
                            </div>

                            <div class="form-group" id="OcupacaoOutra" style="display: <?php ($identUsu->getOCP_ID_OCUPACAO() == ID_SELECT_OUTRO) ? print "inline" : print "none" ?>">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Outra Ocupação: *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="dsOcupacao" type="text" id="dsOcupacao" size="50" maxlength="255" value="<?php print $identUsu->getIDC_NM_OCUPACAO(); ?>" placeholder='Outra Ocupação'>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Vínculo Público:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="tpVinculoPublico" value="<?php print FLAG_BD_SIM ?>" <?php (!Util::vazioNulo($identUsu->getIDC_VINCULO_PUBLICO()) && $identUsu->getIDC_VINCULO_PUBLICO() == FLAG_BD_SIM) ? print "checked" : "" ?>>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <?php if ($dadosLogin['tpVinculoUfes'] == Usuario::$VINCULO_DOCENTE || $dadosLogin['tpVinculoUfes'] == Usuario::$VINCULO_TEC_ADMINISTRATIVO) { ?>

                            <fieldset class="m02">
                                <legend>Dados Funcionais (Apenas para Servidores da UFES)</legend>  

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">SIAPE:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control"  name="nrSIAPE" type="text" id="nrSIAPE" size="7" maxlength="7" value="<?php print $identUsu->getIDC_UFES_SIAPE(); ?>" placeholder='Número do SIAPE'>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Lotação:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" name="dsLotacao" type="text" id="dsLotacao" size="30" maxlength="50" value="<?php print $identUsu->getIDC_UFES_LOTACAO(); ?>" placeholder='Lotação'>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-12 col-sm-4 col-md-4">Setor:</label>
                                    <div class="col-xs-12 col-sm-8 col-md-8">
                                        <input class="form-control" name="dsSetor" type="text" id="dsSetor" size="30" maxlength="50" value="<?php print $identUsu->getIDC_UFES_SETOR(); ?>" placeholder='Setor'>
                                    </div>
                                </div>
                            </fieldset>
                        <?php } ?>

                        <fieldset class="m02">
                            <legend>Passaporte</legend>    

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Número:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="pspNr" type="text" id="pspNr" size="14" maxlength="20" value="<?php print $identUsu->getIDC_PSP_NUMERO(); ?>" placeholder='Número do passaporte'>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Data de emissão:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="pspDtEmissao" type="text" id="pspDtEmissao" size="10" maxlength="10" value="<?php print $identUsu->getIDC_PSP_DT_EMISSAO(); ?>" placeholder='Data de emissão do passaporte'>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Data de validade:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" name="pspDtValidade" type="text" id="pspDtValidade" size="10" maxlength="10" value="<?php print $identUsu->getIDC_PSP_DT_VALIDADE(); ?>" placeholder='Data de validade do passaporte'>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">País emissor:</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <span><?php impressaoPais($identUsu->getIDC_PSP_PAIS_ORIGEM(), "pspPaisOrigem"); ?></span>
                                </div>
                            </div>
                        </fieldset>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <button class="btn btn-success" id="submeter" type="submit">Salvar</button>
                                <button class="btn btn-default" type="button" onclick="window.location = '<?php echo "$CFG->rwww/inicio"; ?>'" >Voltar</button>
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
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        carregaScript("ajax");
        carregaScript("jquery.maskedinput");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#formEditarIdent").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal,input.tudo-maiusculo").capitalize();
                    $("input.tudo-maiusculo").uppercase();
                    form.submit();
                },
                rules: {
                    dsNome: {
                        required: true,
                        minlength: 3,
                        nomeCompleto: true
                    }, idNacionalidade: {
                        required: true
                    }, dsNacionalidade: {
                        required: function (element) {
                            return $("#idNacionalidade").val() == getIdSelectOutro();
                        }
                    }, dsSexo: {
                        required: true
                    }, tpRaca: {
                        required: true
                    }, tpEstadoCivil: {
                        required: true
                    }, nmConjuge: {
                        required: function (element) {
                            return ativaParaEstCasadoUniaoEst($("#tpEstadoCivil").val());
                        },
                        minlength: 3
                    }, filNmPai: {
                        minlength: 3
                    }, filNmMae: {
                        required: true,
                        minlength: 3
                    }, idOcupacao: {
                        required: true
                    }, dsOcupacao: {
                        required: function (element) {
                            return $("#idOcupacao").val() == getIdSelectOutro();
                        },
                        minlength: 3
                    }, nasIdPais: {
                        required: true
                    }, nasIdEstado: {
                        required: function (element) {
                            return ativaParaPaisBrasil($("#nasIdPais").val());
                        }
                    }, nasIdCidade: {
                        required: function (element) {
                            return ativaParaPaisBrasil($("#nasIdPais").val());
                        }
                    },
                    nasData: {
                        required: true,
                        dataBR: true,
                        dataBRMenor: new Date()
                    }, rgNr: {
                        required: true
                    }, rgOrgaoExp: {
                        required: true
                    }, rgUf: {
                        required: true
                    }, rgDtEmissao: {
                        required: true,
                        dataBR: true,
                        dataBRMenor: new Date()
                    }, nrSIAPE: {
                        minlength: 7,
                        maxlength: 7
                    }, pspDtEmissao: {
                        dataBR: true,
                        dataBRMenor: new Date()
                    }, pspDtValidade: {
                        dataBR: true,
                        dataBRMaiorIgual: new Date()
                    }}, messages: {
                    nasData: {
                        dataBR: "Informe uma data válida",
                        dataBRMenor: "Data de nascimento deve ser menor que a data atual."
                    }, rgDtEmissao: {
                        dataBR: "Informe uma data válida",
                        dataBRMenor: "Data de emissão deve ser menor que a data atual."
                    }, pspDtEmissao: {
                        dataBR: "Informe uma data válida",
                        ddataBRMenor: "Data de emissão deve ser menor que a data atual."
                    }, pspDtValidade: {
                        dataBR: "Informe uma data válida",
                        dataBRMaiorIgual: "Data de validade deve ser maior ou igual a data atual."
                    }
                }
            }
            );
            //adicionando máscaras
            $("#nasData").mask("99/99/9999");
            $("#rgDtEmissao").mask("99/99/9999");
            $("#pspDtEmissao").mask("99/99/9999");
            $("#pspDtValidade").mask("99/99/9999");
            $("#nrSIAPE").mask("9999999", {placeholder: " "});
            // funçoes de callback para selects
            // id outros
            function ativaParaIdOutro(valor) {
                return valor == getIdSelectOutro();
            }

            // pais Brasil
            function ativaParaPaisBrasil(valor) {
                return valor == "<?php print Pais::$PAIS_BRASIL; ?>";
            }

            // estado civil
            function ativaParaEstCasadoUniaoEst(valor)
            {
                return valor == "<?php print IdentificacaoCandidato::$EST_CIVIL_CASADO; ?>" || valor == "<?php print IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL; ?>";
            }

            // incluindo gatilho para estado civil
            adicionaGatilhoAddDivSelect("tpEstadoCivil", ativaParaEstCasadoUniaoEst, "divNmConjuge");
            // incluindo gatilho para pais Brasil de nascimento
            adicionaGatilhoAddDivSelect("nasIdPais", ativaParaPaisBrasil, "paisBrasil");
            // incluindo gatilhos para mecanismo "outros"
            //nacionalidade
            adicionaGatilhoAddDivSelect("idNacionalidade", ativaParaIdOutro, "nacionalidadeOutra");
            //ocupaçao
            adicionaGatilhoAddDivSelect("idOcupacao", ativaParaIdOutro, "OcupacaoOutra");
            // tratando gatilho de ajax para cidade de nascimento
            function getParamsCidadeNas()
            {
                return {'cargaSelect': "cidade", 'idUf': $("#nasIdEstado").val()};
            }
            adicionaGatilhoAjaxSelect("nasIdEstado", getIdSelectSelecione(), "divEsperaCidade", "divListaCidade", "nasIdCidade", "<?php print $identUsu->getIDC_NASC_CIDADE(); ?>", getParamsCidadeNas);
        });

    </script>	
</html>
