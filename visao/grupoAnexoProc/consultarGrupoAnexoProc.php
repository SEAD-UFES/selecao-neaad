<!DOCTYPE html>
<html>
    <head>     
        <title>Consultar Informação Complementar do Processo - Seleção EAD</title>
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

        // tem item? recuperando grupo
        if ($grupoAnexoProc->possuiOpcoesResposta()) {
            $listaItemAnexoProc = buscarItemPorGrupoCT($grupoAnexoProc->getGAP_ID_GRUPO_PROC());
            $dsRespMultipla = Util::vazioNulo($listaItemAnexoProc) ? "-" : $listaItemAnexoProc[0]->getDsRespostaMultipla();
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_INF_COMP; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> > <strong>Consultar Inf. Comp.</strong></h1>
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

                <div class="completo m02">
                    <h3 class="sublinhado">Dados da Pergunta</h3>
                    <div class="col-full">
                        <table class="mobileBorda table-bordered table" style="width:100%">
                            <tr>
                                <td class="campo20">Código:</td>
                                <td class="campo80"><?php print $grupoAnexoProc->getGAP_ID_GRUPO_PROC(); ?></td>
                            </tr>
                            <tr>
                                <td class="campo20">Ordem de Exibição:</td>
                                <td class="campo80"><?php print $grupoAnexoProc->getGAP_NR_ORDEM_EXIBICAO(); ?></td>
                            </tr>
                            <tr>
                                <td class="campo20">Nome:</td>
                                <td class="campo80"><?php print $grupoAnexoProc->getGAP_NM_GRUPO(); ?></td>
                            </tr>
                            <tr>
                                <td class="campo20">Tipo:</td>
                                <td class="campo80"><?php print $grupoAnexoProc->getDsTipoGrupoObj(); ?></td>
                            </tr>
                            <tr>
                                <td class="campo20">Obrigatória:</td>
                                <td class="campo80"><?php print $grupoAnexoProc->getDsGrupoObrigatorio(); ?></td>
                            </tr>
                            <?php
                            if ($grupoAnexoProc->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
                                ?>
                                <tr>
                                    <td class="campo20">Tam Máx da Resposta:</td>
                                    <td class="campo80"><?php print $grupoAnexoProc->getGAP_NR_MAX_CARACTER(); ?></td>
                                </tr>
                                <?php
                            } elseif ($grupoAnexoProc->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {
                                ?>
                                <tr>
                                    <td class="campo20">Resposta Múltipla:</td>
                                    <td class="campo80"><?php print $dsRespMultipla; ?></td>
                                </tr>
                            <?php }
                            ?>
                            <tr>
                                <td class="campo20">Avaliação:</td>
                                <td class="campo80"><?php print $grupoAnexoProc->getDsTipoAvalObj(); ?></td>
                            </tr>
                            <?php if ($grupoAnexoProc->isAvaliativo()) {
                                ?>
                                <tr>
                                    <td class="campo20">Etapa:</td>
                                    <td class="campo80"><?php print $grupoAnexoProc->getNmEtapaAval(); ?></td>
                                </tr>
                                <tr>
                                    <td class="campo20">Pontuação Máx:</td>
                                    <td class="campo80"><?php print $grupoAnexoProc->getPontuacaoMaxAval(); ?></td>
                                </tr>
                            <?php }
                            ?>
                            <tr> 
                                <td class="campo20">Descrição da Pergunta:</td>
                                <td class="campo80"><?php print $grupoAnexoProc->getGAP_DS_GRUPO(); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="completo m02">
                    <h3 class="sublinhado">Visualização do Candidato</h3>

                    <div id="questoes">
                        <?php
                        // opções não cadastradas
                        if ($grupoAnexoProc->possuiOpcoesResposta() && Util::vazioNulo($listaItemAnexoProc)) {
                            ?>
                            <div class="callout callout-info">
                                Esta pergunta não está totalmente configurada. Por favor, cadastre as opções de resposta.
                            </div>
                            <?php
                        } else {
                            $scriptAvulso = ""; // definindo variável de script  
                            ?>
                            <form id="formSimulacao" class="form-horizontal">
                                <?php
                                // ATENÇÃO: AO ALTERAR ESTE BLOCO DE CÓDIGO, É IMPORTANTE REVISAR OS ARQUIVOS ASSOCIADOS:
                                // 1 - consultarGrupoAnexoProc.php
                                // 2 - criarInscProcesso.php
                                // 3 - consultarInscProcesso.php
                                // 4 - imprimirCompInscricao.php
                                // 5 - consultarInscProcessoAdmin.php
                                // 6 - fragmentoAvaliarInfComp.php
                                ?>
                                <div id="questao" class="row form-group m01">
                                    <label class="faixa">
                                        <?php if (!Util::vazioNulo($grupoAnexoProc->getGAP_NM_GRUPO())) { ?>
                                            <h4><?php print $grupoAnexoProc->getGAP_NM_GRUPO() ?></h4>
                                        <?php } ?>
                                    </label>
                                    <label class="col-md-12 col-sm-12 col-xs-12">
                                        <?php if (!Util::vazioNulo($grupoAnexoProc->getGAP_DS_GRUPO())) { ?>
                                            <p class="descricao"><?php print $grupoAnexoProc->getGAP_DS_GRUPO() ?></p>
                                        <?php } ?>
                                    </label>

                                    <div id="resposta" class="col-md-12 col-sm-12 col-xs-12">
                                        <?php
                                        if ($grupoAnexoProc->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
                                            $idTextArea = 'textAreaDemo';
                                            $idContador = 'contadorDemo';
                                            $scriptAvulso .= "adicionaContadorTextArea({$grupoAnexoProc->getGAP_NR_MAX_CARACTER()}, '$idTextArea', '$idContador');";
                                            ?>
                                            <textarea id="<?php print $idTextArea; ?>" class="form-control" cols="60" rows="4" style="width:100%;"></textarea>
                                            <div id="<?php print $idContador; ?>" class="totalCaracteres">caracteres restantes</div>                                    
                                            <?php
                                        } elseif ($grupoAnexoProc->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {
                                            ?>
                                            <?php
                                            // definindo id do agrupamento
                                            $idAgrupamento = $grupoAnexoProc->getIdElementoHtml();

                                            // caso de não ser resposta múltipla: criar array de controle
                                            if (!ItemAnexoProc::itemRespostaMultipla($listaItemAnexoProc)) {
                                                $arrayDivsComp = array();
                                            }

                                            // percorrendo os itens do grupo
                                            foreach ($listaItemAnexoProc as $item) {

                                                // gerando id do item anexo
                                                $idHtmlItem = $item->getIdElementoHtml();


                                                // resposta múltipla? inserir os checkbox na tela
                                                if ($item->isRespostaMultipla()) {

                                                    // Nesse caso, adicionar checkbox na tela
                                                    ?>
                                                    <div class="checkbox">
                                                        <label>
                                                            <!-- TESTE DE PROPOSTA <input class="checkbox-inline m0p5" style="margin:0 !important" id="<?php print $idHtmlItem; ?>" name="<?php print $idAgrupamento ?>[]" type="checkbox" value="<?php print $item->getIAP_DS_ITEM(); ?>"> <?php print $item->getIAP_NM_ITEM(); ?>-->
                                                            <input type="checkbox" id="<?php print $idHtmlItem; ?>" name="<?php print $idAgrupamento ?>[]" value="<?php print $item->getIAP_DS_ITEM(); ?>"> <?php print $item->getIAP_NM_ITEM(); ?>
                                                        </label>
                                                    </div>
                                                    <?php
                                                } else {
                                                    // inserir radio na tela
                                                    ?>
                                                    <!--    TESTE DE PROPOSTA                                                <div class="rad">
                                                                                                            <input class="m0p5" type="radio" id="<?php print $idHtmlItem; ?>" value="<?php print $item->getIAP_ID_ITEM(); ?>" name="<?php print $idAgrupamento; ?>">
                                                    <?php print $item->getIAP_NM_ITEM(); ?>
                                                                                                        </div>-->
                                                    <div class="radio">
                                                        <label>
                                                            <input type="radio" id="<?php print $idHtmlItem; ?>" value="<?php print $item->getIAP_ID_ITEM(); ?>" name="<?php print $idAgrupamento; ?>">
                                                            <?php print $item->getIAP_NM_ITEM(); ?>
                                                        </label>
                                                    </div>
                                                    <?php
                                                }

                                                // tratando os complementos
                                                if ($item->temComplemento()) {

                                                    // nesse caso, carregar os complementos
                                                    $subitens = buscarSubitemPorItemCT($item->getIAP_ID_ITEM());

                                                    // recuperando tipo para montar o complemento adequadamente
                                                    $tipo = SubitemAnexoProc::getTipoSubitens($subitens);

                                                    // criando div para comportar o complemento
                                                    $idDivComplemento = "divCompItem" . $item->getIAP_ID_ITEM();
                                                    ?>
                                                    <div id="<?php print $idDivComplemento ?>" style="display: none;">
                                                        <?php
                                                        // caso de ser um subitem do tipo resposta múltipla
                                                        if (SubitemAnexoProc::subitemRespostaMultipla($subitens)) {
                                                            ?>
                                                            <?php
                                                            // percorrendo itens para criar checkbox
                                                            foreach ($subitens as $subitem) {
                                                                // criando id do subitem
                                                                $idHtmlSubitem = $subitem->getIdElementoHtml();
                                                                ?>
<!--                                                                <div> TESTE DE PROPOSTA
                                                                    <div style="margin-left:1em;margin-bottom:5px;">
                                                                        <input class="checkbox-inline" id="<?php print $idHtmlSubitem; ?>" name="<?php print $idHtmlItem; ?>[]" type="checkbox" value="<?php print $subitem->getSAP_DS_SUBITEM(); ?>">
                                                                        <?php print $subitem->getSAP_NM_SUBITEM(); ?>
                                                                    </div>
                                                                </div>-->
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input id="<?php print $idHtmlSubitem; ?>" name="<?php print $idHtmlItem; ?>[]" type="checkbox" value="<?php print $subitem->getSAP_DS_SUBITEM(); ?>">
                                                                        <?php print $subitem->getSAP_NM_SUBITEM(); ?>
                                                                    </label>
                                                                </div>
                                                                <?php
                                                            }
                                                        } else {
                                                            // tratando caso de radio
                                                            if ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_RADIO) {
                                                                impressaoRadioGenerico($idHtmlItem, $subitens, "getIdNomeSubitem", NULL, FALSE, TRUE);
                                                            } elseif ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_TEXTO) {
                                                                // colocando textArea para complemento
                                                                // criando id do subitem
                                                                $idHtmlSubitem = $subitens[0]->getIdElementoHtml();
                                                                $idContador = 'contadorDemoSub' . $subitens[0]->getSAP_ID_SUBITEM();
                                                                $scriptAvulso .= "adicionaContadorTextArea({$subitens[0]->getSAP_NR_MAX_CARACTER()}, '$idHtmlSubitem', '$idContador');";
                                                                ?>
                                                                <div>
                                                                    <div style="margin: 0.5em 0 0.5em 1em;">
                                                                        <span><?php print $subitens[0]->getSAP_NM_SUBITEM(); ?></span>
                                                                        <textarea id="<?php print $idHtmlSubitem; ?>" name="<?php print $idHtmlItem; ?>" class="form-control" cols="60" rows="4" style="width: 90%;"></textarea>
                                                                        <span id="<?php print $idContador; ?>" class="totalCaracteres">caracteres restantes</span>      
                                                                    </div>
                                                                </div>
                                                                <?php
                                                            }
                                                        }

                                                        // tratando script para complementos
                                                        if ($item->isRespostaMultipla()) {
                                                            // inserindo script controlador para exibicao da div
                                                            $scriptAvulso .= "adicionaGatilhoAddDivCheckbox('$idHtmlItem', '$idDivComplemento');";
                                                        } else {
                                                            // adicionando div no array de exibicao de divs
                                                            $arrayDivsComp[] = $item->getIAP_ID_ITEM() . ";" . $idDivComplemento;
                                                        }
                                                        ?>
                                                    </div>
                                                    <?php
                                                } //fim complemento
                                            //
                                    //
                                        //
                                    } // fim lista de itens
                                            // gerando script avulso para radio
                                            if (!ItemAnexoProc::itemRespostaMultipla($listaItemAnexoProc)) {
                                                $strArray = strArrayJavaScript($arrayDivsComp);
                                                $scriptAvulso .= "adicionaGatilhoAddDivRadio('$idAgrupamento', $strArray);";
                                            }
                                            //
                                        //
                                    //
                                }// fim agrup. pergunta
                                        ?>
                                    </div>
                                </div>
                            </form>
                            <?php
                        } // fim opções cadastradas 
                        //         
                        ?>
                    </div>
                </div>
                <input type="button" class="btn btn-default m02" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_INF_COMP; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>';" value="Voltar">
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
<?php
// colocando scripts avulso
if (isset($scriptAvulso)) {
    print $scriptAvulso;
}
?>
        });
    </script>
</html>

