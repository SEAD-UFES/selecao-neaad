<!DOCTYPE html>
<html>
    <head>     
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
        if (!isset($_GET['idGrupoAnexoProc']) && !isset($_GET['idProcesso'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados adicionais, se necessario
        if (isset($_GET['idGrupoAnexoProc'])) {
            // recuperando grupo
            $grupoAnexoProc = buscarGrupoAnexoProcPorIdCT($_GET['idGrupoAnexoProc']);
            $idProcesso = $grupoAnexoProc->getPRC_ID_PROCESSO();
            $edicao = TRUE;
        } else {
            $idProcesso = $_GET['idProcesso'];
            $edicao = FALSE;
        }
        ?>

        <title><?php if (!$edicao) { ?> Nova Informação Complementar <?php } else { ?> Editar Informação Complementar <?php } ?> do Processo - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        // buscando processo
        $processo = buscarProcessoComPermissaoCT($idProcesso);

        // verificando se pode editar
        if (!permiteManterGrupoAnexoProcCT($processo)) {
            throw new NegocioException("Não é possível manter Informação Complementar.");
        }

        // verificando se permite criar nova Etapa
        $insNovaEtapa = permiteCriarEtapaAvalCT($processo);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_INF_COMP; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong><?php if (!$edicao) { ?> Nova Informação Complementar <?php } else { ?> Editar Informação Complementar <?php } ?></strong></h1>
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

                <?php print Util::$MSG_CAMPO_OBRIG; ?>                  

                <div class="col-full m02">
                    <form class="form-horizontal" id="formCadastro" method="post" action="<?php print $CFG->rwww; ?>/controle/CTManutencaoProcesso.php?acao=<?php !$edicao ? print "criarGrupoAnexoProc" : print "editarGrupoAnexoProc" ?>">
                        <input type="hidden" name="valido" value="ctmanutencaoprocesso">
                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">

                        <?php if ($edicao) { ?>
                            <input type="hidden" name="idGrupoAnexoProc" value="<?php print $grupoAnexoProc->getGAP_ID_GRUPO_PROC(); ?>">
                        <?php } ?>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tipo de Pergunta: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoTipoGrupoAnexoProc($edicao ? $grupoAnexoProc->getGAP_TP_GRUPO() : NULL, $edicao); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Nome: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" type='text' name='nmGrupo' id='nmGrupo' size="60" maxlength="255" value="<?php $edicao ? print $grupoAnexoProc->getGAP_NM_GRUPO() : print ""; ?>">
                            </div>
                        </div>

                        <div class=" form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Descrição:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8" style="display:block">
                                <textarea class="form-control" cols="60" rows="6" name="dsGrupo" id="dsGrupo"><?php $edicao ? print $grupoAnexoProc->getGAP_DS_GRUPO() : print ""; ?></textarea>
                                <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                            </div>
                        </div>

                        <div id="divDissertativa" class="form-group" style="display: none">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Tam Máx da Resposta: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" type='text' name='nrMaxCaracter' id='nrMaxCaracter' size="6" maxlength="4" value="<?php $edicao ? print $grupoAnexoProc->getGAP_NR_MAX_CARACTER() : print ""; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Obrigatoriedade: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoRadioSimNao('grupoObrigatorio', $edicao ? $grupoAnexoProc->getGAP_GRUPO_OBRIGATORIO() : FLAG_BD_SIM); ?>
                                <label for="grupoObrigatorio" class="error" style="display: none"></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Avaliação: <i title="Informe se a pergunta será avaliada em alguma etapa do processo seletivo." class="fa fa-question-circle"></i> *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php impressaoTipoAvalGrupoAnexoProc($edicao ? $grupoAnexoProc->getGAP_TP_AVALIACAO() : NULL); ?>
                            </div>
                        </div>

                        <div id="divAvaliacaoSim" style="display: none">
                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Etapa: <i title="Etapa na qual a avaliação será computada." class="fa fa-question-circle"></i> *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <?php impressaoEtapaAvalPorProc($processo->getPRC_ID_PROCESSO(), $insNovaEtapa, $edicao ? $grupoAnexoProc->getIdEtapaAval() : NULL); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Pontuação Máxima: <i title="Nota máxima que pode ser alcançada pelo candidato nesta pergunta." class="fa fa-question-circle"></i> *</label>
                                <div class="col-xs-12 col-sm-8 col-md-8">
                                    <input class="form-control" type='text' name='notaMax' id='notaMax' size="6" maxlength="6" value="<?php $edicao ? print $grupoAnexoProc->getPontuacaoMaxAval() : print ""; ?>">
                                </div>
                            </div>
                        </div>

                        <div id="divBotoes" class="m02">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_INF_COMP; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>';" value="Voltar">
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
        $(document).ready(function () {

            // adicionando contador 
            adicionaContadorTextArea(<?php print GrupoAnexoProc::$TAM_MAX_DS_GRUPO; ?>, 'dsGrupo', 'qtCaracteres');

            // colocando mascara
            addMascaraDecimal('notaMax');
            $("#nrMaxCaracter").mask("9?999", {placeholder: " "});

            // definindo funcao de aviso de exclusão de configuracao 
            var mostrarAviso = true;
            function exibeAvisoExcConfAval() {
                if (mostrarAviso && <?php $edicao ? print "true" : print "false" ?> && <?php ($edicao && $grupoAnexoProc->isAvaliativo()) ? print "true" : print "false" ?>) {
                    alert("Fique atento:\n\nAo alterar esta opção todas as configurações dos Critérios de Eliminação, Classificação, Desempate e Seleção da Etapa configurada anteriormente serão excluídos, bem como toda a configuração do Resultado Final.");
                    mostrarAviso = false;
                }
            }

            // gatilho em etapa para aviso
            $("#idEtapaAval").change(function () {
                exibeAvisoExcConfAval();
            });

            // definindo funcoes para exibicao de divs condicionais
            function exibeAvaliacao(valor)
            {
                if (valor == '<?php print GrupoAnexoProc::$AVAL_TP_SEM; ?>') {
                    exibeAvisoExcConfAval();
                }
                return valor == '<?php print GrupoAnexoProc::$AVAL_TP_MANUAL; ?>' || valor == '<?php print GrupoAnexoProc::$AVAL_TP_AUTOMATICA; ?>';
            }

            function exibeTamMaxResposta(valor)
            {
                return valor == '<?php print GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE; ?>';
            }

            var g1 = adicionaGatilhoAddDivSelect('tpAvalGrupoAnexoProc', exibeAvaliacao, 'divAvaliacaoSim');
            var g2 = adicionaGatilhoAddDivSelect('tpGrupoAnexoProc', exibeTamMaxResposta, 'divDissertativa');
            g1();
            g2();

            $("#formCadastro").validate({
                submitHandler: function (form) {

                    //evitar repetiçao do botao
                    mostrarMensagem();
//                    $(":input[type=text]").not("input.tudo-minusculo,input.tudo-normal").capitalize();
                    form.submit();
                },
                rules: {
                    tpGrupoAnexoProc: {
                        required: true
                    }, nmGrupo: {
                        required: true,
                        remote: {
                            url: "<?php print $CFG->rwww ?>/controle/CTAjax.php?val=nomeInfCompProc&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?><?php $edicao ? print "&idGrupoAnexoProc={$grupoAnexoProc->getGAP_ID_GRUPO_PROC()}" : print ""; ?>",
                                                    type: "post"
                                                }
                                            }, nrMaxCaracter: {
                                                required: function (element) {
                                                    return exibeTamMaxResposta($("#tpGrupoAnexoProc").val());
                                                }, max: <?php print RespAnexoProc::$TAM_LIMITE_RESP; ?>,
                                                min: 1
                                            }, grupoObrigatorio: {
                                                required: true
                                            }, tpAvalGrupoAnexoProc: {
                                                required: true
                                            }, idEtapaAval: {
                                                required: function (element) {
                                                    return exibeAvaliacao($("#tpAvalGrupoAnexoProc").val());
                                                }
                                            }, notaMax: {
                                                required: function (element) {
                                                    return exibeAvaliacao($("#tpAvalGrupoAnexoProc").val());
                                                }, min: 0.01
                                            }
                                        }, messages: {
                                            nmGrupo: {
                                                remote: "Pergunta já cadastrada."
                                            }
                                        }
                                    });

                                });
    </script>
</html>

