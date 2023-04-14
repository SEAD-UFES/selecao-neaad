<?php
if (isset($inscricao) && isset($processo) && isset($chamada)) {

// Recuperando grupos do processo com avaliação manual
    $grupos = buscarGrupoPorProcessoCT($inscricao->getPRC_ID_PROCESSO(), GrupoAnexoProc::$AVAL_TP_MANUAL);
    if (count($grupos) > 0) {
        // criando variaveis de controle para inclusao de campos de nota
        $regraValidacao = "";
        $msgValidacao = "";
        $scriptAvulso = "";
        $idsVariaveis = ""; // armazena uma lista de id's variaveis que deverão ser requisitados no post
        //  modelo de $idsVariaveis: "is1, id2, id3"
        ?>
        <fieldset class="completo m02">
            <legend>Avaliação manual</legend>

            <div id="questoes">
                <?php
                // preenchendo os grupos
                // ATENÇÃO: AO ALTERAR ESTE BLOCO DE CÓDIGO, É IMPORTANTE REVISAR OS ARQUIVOS ASSOCIADOS:
                // 1 - consultarGrupoAnexoProc.php
                // 2 - criarInscProcesso.php
                // 3 - consultarInscProcesso.php
                // 4 - imprimirCompInscricao.php
                // 5 - consultarInscProcessoAdmin.php
                // 6 - fragmentoAvaliarInfComp.php

                foreach ($grupos as $grupo) {
                    // A seguir: Nome da pergunta
                    ?>
                    <div id="questao" class="form-group m01" style="float:left;width:100%;">
                        <label class="faixa">
                            <?php if (!Util::vazioNulo($grupo->getGAP_NM_GRUPO())) { ?>
                                <h4><?php print $grupo->getGAP_NM_GRUPO() ?></h4>
                            <?php } ?>
                        </label>
                        <div id="resposta">
                            <h4>Resposta do Candidato</h4>
                            <?php
                            if ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
                                // recuperando resposta do grupo
                                $resp = buscarRespPorInscricaoGrupoCT($inscricao->getIPR_ID_INSCRICAO(), $grupo->getGAP_ID_GRUPO_PROC());
                                $strResposta = RespAnexoProc::getDsResposta($resp);
                                $maxCaracter = RespAnexoProc::getTamanhoResposta($strResposta);
                                ?>

                                <p align="justify"><?php print $strResposta; ?></p>
                                <?php
                            } elseif ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {
                                // nesse caso carregar perguntas
                                $itens = buscarItemPorGrupoCT($grupo->getGAP_ID_GRUPO_PROC());

                                // recuperando resposta do grupo, se existir
                                $resp = buscarRespPorInscricaoGrupoCT($inscricao->getIPR_ID_INSCRICAO(), $grupo->getGAP_ID_GRUPO_PROC());
                                $arrayResp = $resp != NULL ? $resp->respParaArray() : $arrayResp = NULL;

                                // caso de nao existir resposta
                                if ($resp == NULL) {
                                    ?>
                                    <div><?php print RespAnexoProc::getDsResposta($resp); ?></div>
                                    <?php
                                } else {
                                    // percorrendo os itens do grupo
                                    foreach ($itens as $item) {
                                        // verificando se o item esta respondido
                                        $itemRespondido = $arrayResp != NULL ? RespAnexoProc::isResposta($arrayResp, $item->getIAP_DS_ITEM()) : FALSE;

                                        // caso de item respondido
                                        if ($itemRespondido) {
                                            $respostaTela = "<i class='fa fa-quote-left'></i> {$item->getIAP_NM_ITEM()}";

                                            // tratando complementos
                                            if ($item->temComplemento()) {

                                                // recuperando resposta do complemento
                                                $respostaComp = "";
                                                $respCompBD = buscarRespPorInscricaoItemCT($inscricao->getIPR_ID_INSCRICAO(), $item->getIAP_ID_ITEM());
                                                if (!Util::vazioNulo($respCompBD)) {
                                                    $respostaComp = RespAnexoProc::getDsResposta($respCompBD, FALSE);
                                                }

                                                // tem resposta? incluir os dados
                                                if (!Util::vazioNulo($respostaComp)) {

                                                    // nesse caso, carregar os complementos
                                                    $subitens = buscarSubitemPorItemCT($item->getIAP_ID_ITEM());

                                                    // recuperando tipo para montar o complemento adequadamente
                                                    $tipo = SubitemAnexoProc::getTipoSubitens($subitens);

                                                    // multipla escolha? 
                                                    if (SubitemAnexoProc::subitemRespostaMultipla($subitens)) {
                                                        // criando array e inicial da resposta
                                                        $arrayRespComp = $respCompBD != NULL ? $respCompBD->respParaArray() : NULL;
                                                        $respostaTela .= ": ";

                                                        // percorrendo respostas
                                                        $temp = "";
                                                        foreach ($arrayRespComp as $opcaoResp) {
                                                            $temp = adicionaConteudoVirgula($temp, SubitemAnexoProc::getSubitemPorDescricao($opcaoResp, $subitens)->getSAP_NM_SUBITEM());
                                                        }
                                                        $respostaTela .= $temp;
                                                    } elseif ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_RADIO) {
                                                        $respostaTela .= ": " . SubitemAnexoProc::getSubitemPorDescricao($respostaComp, $subitens)->getSAP_NM_SUBITEM();
                                                    } elseif ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_TEXTO) {
                                                        $respostaTela .= ": $respostaComp";
                                                    }
                                                }
                                            }
                                            ?>
                                            <div><?php print $respostaTela; ?> <i class='fa fa-quote-right'></i></div>
                                            <?php
                                            //
                                        } // fim item respondido
                                    }
                                }// fim else com resposta
                            //
                                    } // fim else tipo pergunta
                            //
                                            // Teve resposta? Inserir campo para nota
                            if (!Util::vazioNulo($resp)) {
                                // definindo id
                                $idContador = "cont" . $resp->getGAP_ID_GRUPO_PROC();
                                ?>   
                                <input type="hidden" name="<?php print $resp->getIdElemHtmlHidden(); ?>" value="<?php print $resp->getRAP_ID_RESPOSTA(); ?>">
                                <div class="form-group m02">
                                    <span><b>Nota</b> (Máx.: <?php print $grupo->getPontuacaoMaxAval(); ?>):</span>
                                    <div class="titulo-top">
                                        <input class="form-control" class="tudo-normal" type="text" id="<?php print $resp->getIdElementoNota(); ?>" name="<?php print $resp->getIdElementoNota(); ?>" size="6" maxlength="10" value="<?php print $resp->getVlNotaMascarada(); ?>">
                                    </div>
                                </div>

                                <div class="form-group m02">
                                    <div class="titulo-top">
                                        <label>Especifique os motivos de sua avaliação:</label>
                                        <textarea id="<?php print $resp->getIdElementoObsNota(); ?>" class="form-control" cols="60" rows="4" style="width:100%" name="<?php print $resp->getIdElementoObsNota(); ?>"><?php print $resp->getRAP_DS_OBS_NOTA(); ?></textarea>
                                        <span id="<?php print $idContador; ?>" class="totalCaracteres">caracteres restantes</span>                                    
                                    </div>
                                </div>

                                <?php
                                $maxCaracter = RespAnexoProc::$TAM_LIMITE_OBS_NOTA;

                                // incluindo validadores
                                $regraValidacao = adicionaConteudoVirgula($regraValidacao, "{$resp->getIdElementoNota()}: {
                                                                    required: true,
                                                                    max: {$grupo->getPontuacaoMaxAval()}
                                                                },{$resp->getIdElementoObsNota()}: {
                                                                    required: true
                                                                }");
                                $msgValidacao = adicionaConteudoVirgula($msgValidacao, "{$resp->getIdElementoNota()}: {
                                                                    required: 'Campo obrigatório.',
                                                                    max: 'A nota deve ser menor ou igual à nota máxima.'
                                                                },{$resp->getIdElementoObsNota()}: {
                                                                    required: 'Campo obrigatório.'
                                                                }");

                                // incluindo controladores: contador de caracteres do text area
                                $scriptAvulso .= "adicionaContadorTextArea($maxCaracter, '{$resp->getIdElementoObsNota()}', '$idContador');";

                                // incluindo controladores: mascara da nota do item
                                $scriptAvulso .= Util::getScriptFormatacaoMoeda($resp->getIdElementoNota());

                                // incluindo campos na lista de elementos a serem recuperados no post
                                $idsVariaveis = adicionaConteudoVirgula($idsVariaveis, $resp->getIdElemHtmlHidden());
                                $idsVariaveis = adicionaConteudoVirgula($idsVariaveis, $resp->getIdElementoNota());
                                $idsVariaveis = adicionaConteudoVirgula($idsVariaveis, $resp->getIdElementoObsNota());
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </fieldset>
        <input type="hidden" name="varNotas" value="<?php isset($idsVariaveis) ? print $idsVariaveis : print ""; ?>">
        <?php
        // verificando necessidade de por virgula em campos de validacao
        $regraValidacao .=!Util::vazioNulo($regraValidacao) ? "," : "";
        $msgValidacao .=!Util::vazioNulo($msgValidacao) ? "," : "";
    }
    ?>
    <?php
    // carregando polos, se necessário
    if ($chamada->admitePoloObj()) {
        $polos = buscarPoloPorInscricaoCT($inscricao->getIPR_ID_INSCRICAO());
        $numPolos = count($polos);
        ?>
        <fieldset class="completo m02">
            <legend>Localização</legend>
            <div class="col-full">
                <?php
                if ($numPolos > 1) {
                    $dsProxPolo = "aos polos";
                    ?>
                    <p><b>Polos selecionados:</b>
                        <?php
                    } else {
                        $dsProxPolo = "ao polo";
                        ?>
                    <p><b>Polo selecionado:</b>
                        <?php
                    }
                    ?>
                    <?php print arrayParaStr($polos); ?></p>
                <?php
                // buscando cidade do candidato
                $endRes = buscarEnderecoCandPorIdCandCT($inscricao->getCDT_ID_CANDIDATO(), Endereco::$TIPO_RESIDENCIAL);
                ?>
                <p><b>Cidade do Candidato:</b> <?php print $endRes->getNomeCidade() . " - " . $endRes->getEST_ID_UF(); ?></p>
                <p>
                    <label class="control-label" style="float:left;margin-right:5px;"><b>Domicílio próximo <?php print $dsProxPolo; ?>: </b></label>
                    <?php impressaoRadioSimNao("domicilioProximo", $inscricao->getIPR_LOCALIZACAO_VALIDA()) ?>
                    <label for="domicilioProximo" style="display: none" class="error"></label>
                </p>
            </div>
        </fieldset>        
        <?php
    }
}
?>