<!DOCTYPE html>
<html>
    <head>  
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        // incluindo arquivos
        require_once ($CFG->rpasta . "/controle/CTNoticia.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/util/Mensagem.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        // recuperando parametros
        if (!isset($_GET['idProcesso']) || !isset($_GET['idChamada'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        $idProcesso = $_GET['idProcesso'];
        $idChamada = $_GET['idChamada'];
        $idNoticia = isset($_GET['idNoticia']) ? $_GET['idNoticia'] : NULL;
        $edicao = $idNoticia != NULL;
        ?>

        <title><?php $edicao ? print "Editar" : print "Criar"; ?> Notícia - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null && estaLogado(Usuario::$USUARIO_COORDENADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        // recuperando dados
        $processo = buscarProcessoComPermissaoCT($idProcesso);
        $chamada = buscarChamadaPorIdCT($idChamada);

        // validando
        if (!$chamada->permiteEdicao()) {
            throw new NegocioException("Não é possível acessar esta página, pois a chamada está finalizada.");
        }

        if ($edicao) {
            $noticia = buscarNoticiaPorIdCT($idNoticia, $idProcesso);
        } else {
            $noticia = NULL;
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Edital</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_NOTICIA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong><?php $edicao ? print "Editar" : print "Criar"; ?> Notícia</strong></h1>
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
                    <form id="formNoticia" class="form-horizontal" method="post" action="<?php print "$CFG->rwww"; ?>/controle/CTNoticia.php?acao=<?php $edicao ? print "editar" : print "criar"; ?>Noticia">
                        <input type="hidden" name="valido" value="ctnoticia">
                        <input type="hidden" name="idProcesso" value="<?php print $idProcesso; ?>">
                        <input type="hidden" name="idChamada" value="<?php print $idChamada; ?>">

                        <?php if ($edicao) {
                            ?>
                            <input type="hidden" name="idNoticia" value="<?php print $noticia->getNOT_ID_NOTICIA(); ?>">
                        <?php } ?>

                        <div class="form-group">
                            <label title="Pública: todos podem ver; Privada: apenas quem está inscrito no edital tem acesso." for="idTipo" class="control-label col-xs-12 col-sm-4 col-md-4">Privacidade: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <?php print impressaoPrivacidadeNot("idTipo", $edicao ? $noticia->getNOT_TP_NOTICIA() : NULL); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dtValidade" class="control-label col-xs-12 col-sm-4 col-md-4">Data de validade:</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="dtValidade" type="text" id="dtValidade" size="10" maxlength="10" value="<?php $edicao ? print $noticia->getNOT_DT_VALIDADE() : NULL; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="titulo" class="control-label col-xs-12 col-sm-4 col-md-4">Título: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input class="form-control" name="titulo" type="text" id="titulo" size="30" maxlength="100" value="<?php $edicao ? print $noticia->getNOT_NM_TITULO() : NULL; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Link:</label>

                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <div style="width:19%;float:left;margin-right:1%;">
                                    <?php impressaoTpLinkNot($edicao ? $noticia->getTpLink() : NULL); ?>
                                </div>
                                <div style="width:80%;float:left;">
                                    <div class="input-group" style="display: none;" id="divLinkInterno">
                                        <span class="input-group-addon" id="basic-addon1" style="font-size:0.8em;"><?php print str_replace("http://", "", "$CFG->rwww/"); ?></span>
                                        <input class="form-control" aria-describedby="basic-addon1" name="dsUrlInterno" type="text" id="dsUrlInterno" size="30" maxlength="200" placeholder="Link" value="<?php $edicao ? print $noticia->getLinkParcial() : NULL; ?>">
                                    </div>

                                    <div class="input-group" style="display:none;width:100%;" id="divLinkExterno">
                                        <!--<span class="input-group-addon" id="basic-addon2"></span>-->
                                        <input style="border-radius:4px;" class="form-control" aria-describedby="basic-addon2" name="dsUrlExterno" type="text" id="dsUrlExterno" size="30" maxlength="200" placeholder="Link" value="<?php $edicao ? print $noticia->getLinkParcial() : NULL; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Descrição: *</label>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <textarea class="form-control" cols="60" rows="6" name="dsNoticia" id="dsNoticia"><?php $edicao ? print $noticia->getNOT_DS_NOTICIA() : NULL; ?></textarea>
                                <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                            </div>
                        </div>

                        <div id="divBotoes" class="form-group">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                <input class= "btn btn-default" id="btVoltar" type="button" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_NOTICIA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>'" value="Voltar">
                            </div>
                        </div>

                        <div id="divMensagem" style="display:none" class="m01">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("metodos-adicionaisBR");
        carregaScript("jquery.maskedinput");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            adicionaContadorTextArea(160, "dsNoticia", "qtCaracteres");
            $("#dtValidade").mask("99/99/9999");

            function ativaLinkInterno(valor) {
                return valor === '<?php print Noticia::$TP_LINK_INTERNO; ?>';
            }

            var gatilho = adicionaGatilhoAddDivSelect("tpLinkNot", ativaLinkInterno, "divLinkInterno", "divLinkExterno");
            gatilho();

            //validando form
            $("#formNoticia").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                    idTipo: {
                        required: true
                    }, dtValidade: {
                        dataBR: true
                    }, titulo: {
                        required: true
                    }, dsUrlInterno: {
                        required: function () {
                            return $("#tpLinkNot").val() === "<?php print Noticia::$TP_LINK_INTERNO; ?>";
                        }
                    }, dsUrlExterno: {
                        required: function () {
                            return $("#tpLinkNot").val() === "<?php print Noticia::$TP_LINK_EXTERNO; ?>";
                        }
                    }, dsNoticia: {
                        required: true
                    }},
                messages: {
                }
            }
            );
        });
    </script>
</html>

