<!DOCTYPE html>
<?php
require_once '../../config.php';
global $CFG;

//verificando passagem por get
if (!isset($_GET['idProcesso'])) {
    new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
    return;
}
$idProcesso = $_GET['idProcesso'];
?>

<?php
//verificando se está logado
require_once ($CFG->rpasta . "/util/sessao.php");

if (estaLogado(Usuario::$USUARIO_CANDIDATO, TRUE) == null) {
    // salvando na sessão a url de volta
    sessaoNav_setNavegacaoLogin("visao/inscricaoProcesso/criarInscProcesso.php?idProcesso=$idProcesso");

    // incluindo popup de login
    include("$CFG->rpasta/visao/usuario/popupLogin.php");
    return;
}
?>
<html>
    <head>     
        <title>Inscrever-se em um Edital - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/controle/CTRastreio.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");
        require_once ($CFG->rpasta . "/negocio/TipoCargo.php");

        //buscando processo
        $processo = buscarProcessoPorIdCT($idProcesso);

        // setando id do processo na sessão para volta posterior
        RAT_criarDadosSessaoInscProcesso($idProcesso, $processo->getHTMLDsEditalCompleta());

        //validando inscrição do usuário
        validaInscricaoUsuarioCT(getIdUsuarioLogado(), $idProcesso, $processo->PCH_ID_ULT_CHAMADA);

        // passou pela validação: removendo dados da sessão
        RAT_removerDadosSessaoInscProcesso();

        // criando rastreio de inscrição
        RAT_criarRastreioInscricaoEditalCT(getIdUsuarioLogado(), $idProcesso, $processo->PCH_ID_ULT_CHAMADA);
        ?>

        <?php
        // Recuperando grupos do processo
        $grupos = buscarGrupoPorProcessoCT($processo->getPRC_ID_PROCESSO());
//        $grupos = array();
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/editais">Editais</a> > <a href="<?php print $CFG->rwww ?>/visao/processo/consultarProcesso.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO() ?>">Visualizar Edital</a> > <strong>Inscrição</strong></h1>
                </div>

                <div class="col-full m02">
                    <div class="callout callout-warning" style="margin-top:-1em;">
                        Por favor, confira se este é realmente o edital no qual você pretende se inscrever e certifique-se de ter <strong>preenchido seu currículo completamente</strong>, pois não será permitido alterá-lo durante o processo de seleção.
                    </div>
                </div>

                <div class="completo">
                    <div id="sobreedital" class="col-half">
                        <h3 class="sublinhado">Informações</h3>
                        <table class="m01" style="width:100%;">                 
                            <tr>
                                <td><strong>Edital nº: </strong></td>
                                <td><?php print $processo->getNumeracaoEdital(); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Atribuição: </strong></td>
                                <td><?php print $processo->TIC_NM_TIPO_CARGO; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Curso: </strong></td>
                                <td><?php print $processo->CUR_NM_CURSO; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nível: </strong></td>
                                <td><?php print $processo->TPC_NM_TIPO_CURSO; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Documento: </strong></td>
                                <td><a target="_blank" href="<?php print $processo->getUrlArquivoEdital(); ?>">Leia o edital <i class="fa fa-external-link"></i></a></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-half">
                        <h3 class="sublinhado">Descrição</h3>
                        <div class="m01">
                            <p align="justify"><?php print $processo->getPRC_DS_PROCESSO(); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-full m02">
                    <form id="formInscricao" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTProcesso.php?acao=criarInscProcesso" ?>'>
                        <input type="hidden" name="valido" value="ctprocesso">
                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO() ?>">
                        <input type="hidden" id="idChamada" name="idChamada" value="<?php print $processo->PCH_ID_ULT_CHAMADA; ?>">

                        <?php
                        if ($processo->temOpcaoInscricao() || count($grupos) > 0) {
                            // definindo variaveis de controle
                            $regraValidacao = "";
                            $msgValidacao = "";
                            $scriptAvulso = "";
                            ?>

                        <?php } ?>
                        <?php if (count($grupos) > 0) { ?>

                            <fieldset id="criarInscProcesso">
                                <h3 class="sublinhado">Informações Complementares</h3>

                                <div class="row m01">
                                    <?php
                                    print Util::$MSG_CAMPO_OBRIG;
                                    ?>
                                </div>

                                <div class="m01" id="questoes">
                                    <?php
                                    // preparando mais variaveis de controle
                                    $idsVariaveis = ""; // armazena uma lista de id's variaveis que deverao ser requisitados no post
                                    //  modelo de $idsVariaveis: "id1, id2, id3"
                                    //
                                // preenchendo os grupos
                                    // ATENÇÃO: AO ALTERAR ESTE BLOCO DE CÓDIGO, É IMPORTANTE REVISAR OS ARQUIVOS ASSOCIADOS:
                                    // 1 - consultarGrupoAnexoProc.php
                                    // 2 - criarInscProcesso.php
                                    // 3 - consultarInscProcesso.php
                                    // 4 - imprimirCompInscricao.php
                                    // 5 - consultarInscProcessoAdmin.php
                                    // 6 - fragmentoAvaliarInfComp.php
                                    foreach ($grupos as $grupo) {
                                        // A seguir: Nome da pergunta e descriçao
                                        ?>
                                        <div id="questao" class="row form-group m02">

                                            <?php if (!Util::vazioNulo($grupo->getGAP_NM_GRUPO())) { ?>
                                                <label class="faixa">
                                                    <h4><?php print $grupo->getGAP_NM_GRUPO() . ($grupo->isObrigatorio() ? " *" : ""); ?></h4>
                                                </label>
                                            <?php } ?>

                                            <?php if (!Util::vazioNulo($grupo->getGAP_DS_GRUPO())) { ?>
                                                <label class="col-md-12 col-sm-12 col-xs-12">
                                                    <p class="descricao"><?php print $grupo->getGAP_DS_GRUPO() ?></p>
                                                </label>
                                            <?php } ?>

                                            <div id="resposta" class="col-md-12 col-sm-12 col-xs-12">
                                                <?php
                                                if ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
                                                    // Nesse caso, adicionar input para a pergunta
                                                    // Esse input deve ser grande, pois o tamanho vai ate 3000 caracteres
                                                    $idTextArea = $grupo->getIdElementoHtml();
                                                    $idContador = "qtGrupo" . $grupo->getGAP_ID_GRUPO_PROC();


                                                    // verificando necessidade de gerar validador
                                                    if ($grupo->isObrigatorio()) {
                                                        $regraValidacao = adicionaConteudoVirgula($regraValidacao, "$idTextArea: {
                                                                    required: true
                                                                }");
                                                        $msgValidacao = adicionaConteudoVirgula($msgValidacao, "$idTextArea: {
                                                                    required: 'Campo obrigatório.'
                                                                }");
                                                    }

                                                    // geraçao de script de contagem de caracteres
                                                    $maxCaracter = $grupo->getGAP_NR_MAX_CARACTER();
                                                    $scriptAvulso .= "adicionaContadorTextArea($maxCaracter, '$idTextArea', '$idContador');";

                                                    // incluindo id do contador na lista de elementos a serem recuperados no post
                                                    $idsVariaveis = adicionaConteudoVirgula($idsVariaveis, $idTextArea);
                                                    ?>
                                                    <textarea id="<?php print $idTextArea ?>" class="form-control" cols="60" rows="4" style="width:100%;" name="<?php print $idTextArea ?>"></textarea>
                                                    <div id="<?php print $idContador ?>" class="totalCaracteres">caracteres restantes</div>                                    

                                                    <?php
                                                } elseif ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {

                                                    // nesse caso carregar perguntas
                                                    $itens = buscarItemPorGrupoCT($grupo->getGAP_ID_GRUPO_PROC());

                                                    // gerar id do agrupamento e inserindo na lista de variaveis
                                                    $idAgrupamento = $grupo->getIdElementoHtml();
                                                    $idsVariaveis = adicionaConteudoVirgula($idsVariaveis, $idAgrupamento);

                                                    // caso de não ser resposta múltipla: criar array de controle
                                                    if (!ItemAnexoProc::itemRespostaMultipla($itens)) {
                                                        $arrayDivsComp = array();
                                                        $idAgrupTela = $idAgrupamento;
                                                    } else {
                                                        $idAgrupTela = "{$idAgrupamento}[]";
                                                    }

                                                    // verificando necessidade de gerar validador
                                                    if ($grupo->isObrigatorio()) {
                                                        $regraValidacao = adicionaConteudoVirgula($regraValidacao, "'$idAgrupTela': {
                                                                    required: true
                                                                }");
                                                        $msgValidacao = adicionaConteudoVirgula($msgValidacao, "'$idAgrupTela': {
                                                                    required: 'Item obrigatório.'
                                                                }");
                                                        // inserindo classe de erro
                                                        ?>
                                                        <label for="<?php print $idAgrupTela; ?>" class="error" style="display: none"></label>
                                                    <?php }
                                                    ?>
                                                    <div class="m0p5">
                                                        <?php
                                                        // preenchendo os itens do grupo
                                                        foreach ($itens as $item) {

                                                            // gerando id do item anexo
                                                            $idHtmlItem = $item->getIdElementoHtml();


                                                            // resposta múltipla? inserir os checkbox na tela
                                                            if ($item->isRespostaMultipla()) {
                                                                // Nesse caso, adicionar checkbox na tela
                                                                ?>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input class="checkbox m0p5" style="margin:0 !important;" id="<?php print $idHtmlItem; ?>" name="<?php print $idAgrupTela ?>" type="checkbox" value="<?php print $item->getIAP_DS_ITEM(); ?>">
                                                                        <?php print $item->getIAP_NM_ITEM(); ?>
                                                                    </label>
                                                                </div>
                                                                <?php
                                                            } else {
                                                                // inserir radio na tela
                                                                ?>
                                                                <div class="radio">
                                                                    <label>
                                                                        <input class="m0p5" type="radio" id="<?php print $idHtmlItem; ?>" value ="<?php print $item->getIAP_DS_ITEM(); ?>" name="<?php print $idAgrupTela; ?>">
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

                                                                // incluindo id da variavel no post
                                                                $idsVariaveis = adicionaConteudoVirgula($idsVariaveis, $idHtmlItem);

                                                                // 
                                                                $idHtmlItemTela = SubitemAnexoProc::subitemRespostaMultipla($subitens) ? "{$idHtmlItem}[]" : $idHtmlItem;

                                                                // verificando necessidade de gerar validador
                                                                if ($item->isObrigatorio()) {
                                                                    $regraValidacao = adicionaConteudoVirgula($regraValidacao, "'$idHtmlItemTela': {
                                                                    required: function(element) {
                                                                        return $('#$idHtmlItem').is(':checked');
                                                                    }            
                                                                    }");
                                                                    $msgValidacao = adicionaConteudoVirgula($msgValidacao, "'$idHtmlItemTela': {
                                                                    required: 'Item obrigatório.'
                                                                }");

                                                                    // inserindo classe de erro
                                                                    ?>
                                                                    <label for="<?php print $idHtmlItem; ?>" class="error" style="display:none;"></label>

                                                                    <?php
                                                                }
                                                                // criando div para comportar o complemento
                                                                $idDivComplemento = "divCompItem" . $item->getIAP_ID_ITEM();
                                                                ?>
                                                                <div id="<?php print $idDivComplemento ?>" style="display: none;margin-top:5px;">
                                                                    <?php
                                                                    // caso de ser resposta múltipla do subitem
                                                                    if (SubitemAnexoProc::subitemRespostaMultipla($subitens)) {
                                                                        ?>
                                                                        <?php
                                                                        // percorrendo itens para criar checkbox
                                                                        foreach ($subitens as $subitem) {
                                                                            // criando id do subitem
                                                                            $idHtmlSubitem = $subitem->getIdElementoHtml();
                                                                            ?>
                                                                            <div class="checkbox">
                                                                                <label style="margin-left:20px;">
                                                                                    <input type="checkbox" id="<?php print $idHtmlSubitem; ?>" name="<?php print $idHtmlItem; ?>[]"  value="<?php print $subitem->getSAP_DS_SUBITEM(); ?>">
                                                                                    <?php print $subitem->getSAP_NM_SUBITEM(); ?>
                                                                                </label>
                                                                            </div>
                                                                            <?php
                                                                        }
                                                                    } else {

                                                                        // tratando caso de radio
                                                                        if ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_RADIO) {
                                                                            impressaoRadioGenerico($idHtmlItem, $subitens, "getIdNomeSubitem", NULL, FALSE, TRUE);
                                                                            //
                                                                            //
                                                                        // caso de texto
                                                                        } elseif ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_TEXTO) {
                                                                            // colocando textArea para complemento
                                                                            $idHtmlSubitem = $subitens[0]->getIdElementoHtml();
                                                                            $idContador = 'contador' . $subitens[0]->getSAP_ID_SUBITEM();
                                                                            $scriptAvulso .= "adicionaContadorTextArea({$subitens[0]->getSAP_NR_MAX_CARACTER()}, '$idHtmlSubitem', '$idContador');";
                                                                            ?>
                                                                            <div class="row">
                                                                                <div style="margin-left:20px;" class="col-md-12 col-sm-12 col-xs-12 m0p5">
                                                                                    <span><?php print $subitens[0]->getSAP_NM_SUBITEM(); ?></span>
                                                                                    <textarea id="<?php print $idHtmlSubitem; ?>" name="<?php print $idHtmlItem; ?>" class="form-control" cols="60" rows="4" style="width:90%;"></textarea>
                                                                                    <span id="<?php print $idContador; ?>" class="totalCaracteres">caracteres restantes</span>      
                                                                                </div>
                                                                            </div>

                                                                            <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                </div>

                                                                <?php
                                                                // tratando script para complementos
                                                                if ($item->isRespostaMultipla()) {
                                                                    // inserindo script controlador para exibicao da div
                                                                    $scriptAvulso .= "adicionaGatilhoAddDivCheckbox('$idHtmlItem', '$idDivComplemento');";
                                                                } else {
                                                                    // adicionando div no array de exibicao de divs
                                                                    $arrayDivsComp[] = $item->getIAP_DS_ITEM() . ";" . $idDivComplemento;
                                                                }
                                                                ?>
                                                                <?php
                                                            } //fim complemento 
                                                        } // fim lista de itens
                                                        // 
                                                        ?>

                                                    </div>

                                                    <?php
                                                    // gerando script avulso para radio
                                                    if (!ItemAnexoProc::itemRespostaMultipla($itens)) {
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
                                    <?php } ?>
                                </div>
                            </fieldset>
                        <?php } ?>

                        <div class="m02">
                            <?php
                            if ($processo->temOpcaoInscricao()) {
                                $admiteAreaAtuAjax = FALSE;
                                ?>
                                <h3 class="sublinhado">Opções de Inscrição *</h3>

                                <div class="row m01">
                                    <?php
                                    print Util::$MSG_CAMPO_OBRIG;
                                    ?>
                                </div>

                                <div class="col-full m01">
                                    Por favor, informe suas opções a seguir.
                                    <?php if ($processo->isInscricaoMultipla()) { ?>
                                        <i class="fa fa-question-circle" title="Coloque à direita os polos para os quais você deseja se inscrever, ordenando-os de acordo com sua prioridade. Você pode escolher até <?php print $processo->PCH_NR_MAX_OPCAO_POLO; ?> polos."></i>
                                    <?php } ?>
                                </div>

                                <div class="completo m02">
                                    <?php
                                    if ($processo->admitePoloObj()) {
                                        if ($processo->isInscricaoMultipla()) {
                                            // imprimindo processos para multipla escolha
                                            ?>
                                            <div class="completo" style="margin-bottom:2em;">
                                                <?php
                                                $tam = impressaoPolosPorProcesso($processo->PCH_ID_ULT_CHAMADA, PoloChamada::getFlagPoloAtivo(), NULL, TRUE);

                                                // adicionando regras de validacao
                                                $regraValidacao = adicionaConteudoVirgula($regraValidacao, "'idPolo[]': {
                                                                    required: true,
                                                                    qtdeMaxSelect: {$processo->PCH_NR_MAX_OPCAO_POLO}
                                                                }");

                                                $msgValidacao = adicionaConteudoVirgula($msgValidacao, "'idPolo[]': {
                                                                    required: 'Escolha os polos para os quais você deseja se inscrever.',
                                                                    qtdeMaxSelect: 'Você só pode escolher, no máximo, {$processo->PCH_NR_MAX_OPCAO_POLO} polos.'
                                                                }");
                                                ?>
                                            </div>
                                            <?php
                                        } else {
                                            // imprimindo polos do processo para escolha unica
                                            $admiteAreaAtuAjax = TRUE; // Esse caso admite area ajax
                                            $idSelectPolo = "idPolo";
                                            ?>

                                            <div class="form-group completo">
                                                <label class="control-label col-xs-12 col-sm-4 col-md-4">Polo de Atuação: *</label>
                                                <div class="col-xs-12 col-sm-8 col-md-8">
                                                    <?php
                                                    impressaoPolosPorProcesso($processo->PCH_ID_ULT_CHAMADA, PoloChamada::getFlagPoloAtivo());

                                                    // adicionando regras de validacao
                                                    $regraValidacao = adicionaConteudoVirgula($regraValidacao, "$idSelectPolo: {
                                                                    required: true
                                                                }");
                                                    $msgValidacao = adicionaConteudoVirgula($msgValidacao, "$idSelectPolo: {
                                                                    required: 'Escolha o polo para o qual você deseja se inscrever.'
                                                                }");
                                                    ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>


                                <?php
                                if (ProcessoChamada::admiteAreaAtuacao($processo->PCH_ADMITE_AREA)) {
                                    if ($admiteAreaAtuAjax) {
                                        ?>
                                        <div id="divEsperaAreaAtu" style="display: none;">
                                            <span>Aguarde, Carregando...</span>
                                        </div>
                                    <?php }
                                    ?>  

                                    <div id="divListaAreaAtu" style="display: <?php $admiteAreaAtuAjax ? print "none" : print "block" ?>" class="completo form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Área de Atuação: *</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <?php
                                            if (!$admiteAreaAtuAjax) {
                                                impressaoAreaAtuPorProcesso($processo->PCH_ID_ULT_CHAMADA, AreaAtuChamada::getFlagAreaAtiva());
                                            } else {
                                                ?>
                                                <select class="form-control" name="idAreaAtuChamada" id="idAreaAtuChamada"></select>
                                                <?php
                                            }

                                            // adicionando regras de validacao
                                            $regraValidacao = adicionaConteudoVirgula($regraValidacao, "idAreaAtuChamada: {
                                                                    required: true
                                                                }");
                                            $msgValidacao = adicionaConteudoVirgula($msgValidacao, "idAreaAtuChamada: {
                                                                    required: 'Escolha a área de atuação para a qual você deseja se inscrever.'
                                                                }");
                                            ?>
                                        </div>
                                    </div>
                                <?php }
                                ?>

                                <?php
                                if (ProcessoChamada::admiteReservaVaga($processo->PCH_ADMITE_RESERVA_VAGA)) {
                                    ?>

                                    <div id="divListaReservaVaga" class="completo form-group">
                                        <label class="control-label col-xs-12 col-sm-4 col-md-4">Reserva de Vaga: *</label>
                                        <div class="col-xs-12 col-sm-8 col-md-8">
                                            <?php
                                            impressaoReservaVagaPorProcesso($processo->PCH_ID_ULT_CHAMADA, ReservaVagaChamada::getFlagReservaAtiva());

                                            // adicionando regras de validacao
                                            $regraValidacao = adicionaConteudoVirgula($regraValidacao, "idReservaVaga: {
                                                                    required: true
                                                                }");
                                            $msgValidacao = adicionaConteudoVirgula($msgValidacao, "idReservaVaga: {
                                                                    required: 'Escolha uma das opções de reserva de vaga.'
                                                                }");
                                            ?>
                                        </div>
                                    </div>
                                <?php }
                                ?>
                            </div>
                        <?php } ?>
                        <input type="hidden" name="varQuest" value="<?php isset($idsVariaveis) ? print $idsVariaveis : print ""; ?>">

                        <div id="divBotoes">
                            <div class="col-xs-12 col-sm-4 col-md-4">&nbsp;</div>
                            <div class="col-xs-12 col-sm-8 col-md-8">
                                <input id="submeter" class="btn btn-success" type="submit" value="Inscreva-me">
                                <input type="button" class="btn btn-default"  onclick="javascript: window.location = '<?php print $CFG->rwww ?>/visao/processo/consultarProcesso.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO() ?>';" value="Voltar">
                            </div>
                        </div>
                    </form>
                </div>
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
    carregaCSS("jquery.multiselect2side");
    carregaScript("jquery.multiselect2side");
    carregaScript("metodos-adicionaisBR");
    carregaScript("ajax");
    ?>
</body>

<script type="text/javascript">
    $(document).ready(function () {

<?php if ($processo->admitePoloObj()) { ?>
            // tratando select multiplo
            if ($("#idPolo").attr("multiple") == "multiple")
            {
                // criando o multiselect
                $('#idPolo').attr("size", "<?php isset($tam) ? print $tam : print "13" ?>");
                $('#idPolo').multiselect2side({
                    selectedPosition: 'right',
                    moveOptions: true,
                    sortOptions: false,
                    maxSelected: <?php print $processo->PCH_NR_MAX_OPCAO_POLO; ?>,
                    labelsx: '',
                    labeldx: '',
                    search: true,
                    autoSort: false,
                    autoSortAvailable: true,
                    placeHolderSearch: 'Buscar polo'
                });
            }
<?php } ?>
<?php if (isset($admiteAreaAtuAjax) && $admiteAreaAtuAjax) { ?>
            // tratando gatilho de ajax para área de atuação
            function getParamsAreaAtu()
            {
                return {'cargaSelect': "areaAtuChamadaPolo", 'idPolo': $("#<?php print $idSelectPolo; ?>").val(), 'idChamada': $("#idChamada").val(), 'flagSituacao': '<?php print AreaAtuChamada::getFlagAreaAtiva(); ?>'};
            }
            adicionaGatilhoAjaxSelect("<?php print $idSelectPolo; ?>", getIdSelectSelecione(), "divEsperaAreaAtu", "divListaAreaAtu", "idAreaAtuChamada", null, getParamsAreaAtu);
<?php } ?>

        //validando form
        $("#formInscricao").validate({
        ignore: [],
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
<?php
if (isset($regraValidacao)) {
    print $regraValidacao;
}
?>
                }
        , messages: {
<?php
if (isset($msgValidacao)) {
    print $msgValidacao;
}
?>
        }
        }
        );
<?php
if (isset($scriptAvulso)) {
    print $scriptAvulso;
}
?>
    });
</script>
</html>