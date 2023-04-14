<?php
// deve haver as variáveis para manipulação dos dados
if (isset($processo) && isset($chamada)) {
    require_once ($CFG->rpasta . "/controle/CTManutencaoProcesso.php");

    // recuperando etapas de seleção
    $etapasSel = buscarEtapaPorChamadaCT($chamada->getPCH_ID_CHAMADA());

    // Candidato está logado?
    $logadoCand = estaLogado(Usuario::$USUARIO_CANDIDATO);

    // recuperando chamadas do processo para montar abas
    $listaChamadas = buscarChamadaPorProcessoCT($processo->getPRC_ID_PROCESSO(), TRUE);
    ?>  
    <div class="completo">
        <div id="sobreedital" class="col-half m02">
            <h3 class="sublinhado">Informações</h3>
            <table>                 
                <tr>
                    <td><strong>Edital nº: </strong></td>
                    <td><?php print $processo->getNumeracaoEdital(); ?></td>
                </tr>
                <tr>
                    <td><strong>Documento: </strong></td>
                    <td><a target="_blank" href="<?php print $processo->getUrlArquivoEdital(); ?>">Leia o edital <i class="fa fa-external-link"></i></a></td>
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
                    <td><strong>Sobre o processo: </strong></td>
                    <td><a href="#calendario">Calendário</a> | <a href="#vagas">Vagas</a> | <a href="#atualizacoes">Atualizações</a></td>
                </tr>                
                <tr><td>&nbsp;</td><!-- Separação dos blocos --><td>&nbsp;</td></tr>
                <?php if ($chamada->isMostrarFaseAtual()) { ?>
                    <tr>
                        <td><strong>Fase atual: </strong></td>
                        <td><?php print $chamada->getDsFaseChamada(); ?></td>
                    </tr>
                <?php } ?>

                <?php if ($chamada->isMostrarProxFase()) { ?>
                    <tr>
                        <td><strong>Próxima fase: </strong></td>
                        <td><?php print $chamada->getDsProximaFaseChamada(); ?></td>
                    </tr>

                <?php } ?>
            </table>
        </div>

        <div class="col-half m02">
            <h3 class="sublinhado">Descrição</h3>
            <div class="col-full">
                <p align="justify"><?php print $processo->getPRC_DS_PROCESSO(); ?></p>
            </div>
        </div>

        <div id="atualizacoes" class="col-full m04">
            <div id="accordionAtualizacao" class="panel-group" role="tablist" aria-multiselectable="true">
                <div class="panel panel-primary">
                    <div class="panel-heading" role="tab">
                        <a style="color:#fff;text-decoration:none;" data-toggle="collapse" data-parent="#accordionAtualizacao" href="#att" aria-expanded="true" aria-controls="atualizacaoEdital">
                            <h4 class="panel-title accordion-toggle">Atualizações</h4>
                        </a>
                    </div>

                    <div id="att" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="atualizacaoEdital">
                        <div class="panel-body">                           
                            <?php
                            if ($processo->isFechado()) {
                                ?>
                                <div class='callout callout-warning'>Este edital está finalizado.</div>
                                <?php
                            }
                            print tabelaAtualizacaoProcesso($processo->getPRC_ID_PROCESSO());
                            ?>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-full m04">
        <div class="tabbable">
            <ul id="tabProcesso" class="nav nav-tabs">
                <?php
                // percorrendo chamadas para criar título das abas
                foreach ($listaChamadas as $chamadaTemp) {
                    ?>
                    <li <?php $chamadaTemp->getPCH_ID_CHAMADA() === $chamada->getPCH_ID_CHAMADA() ? print "class='active'" : print ""; ?>><a href="#chamada<?php echo $chamadaTemp->getPCH_ID_CHAMADA(); ?>" data-toggle="tab"><?php echo $chamadaTemp->getPCH_DS_CHAMADA(TRUE); ?></a></li>
                <?php } ?>
            </ul>

            <div class="tab-content">
                <?php
                // percorrendo chamadas para preencher dados
                foreach ($listaChamadas as $chamadaTemp) {
                    ?>
                    <div class="tab-pane <?php $chamadaTemp->isChamadaAtual() ? print "active" : print ""; ?> <?php $chamadaTemp->isFinalizada() ? print "chamada-inativa" : print ""; ?>" id="chamada<?php echo $chamadaTemp->getPCH_ID_CHAMADA(); ?>">
                        <div id="calendario" class="completo">          
                            <?php
                            if ($chamadaTemp->isFinalizada()) {
                                ?>
                                <div class='callout callout-warning'>Esta chamada está encerrada. <a target="_blank" href="<?php echo $chamadaTemp->getUrlArquivoResulFinal(); ?>">Clique aqui para ver o resultado final.</a></div>
                            <?php }
                            ?>
                            <div class="completo m02">
                                <h3 class="sublinhado">Calendário do Processo Seletivo</h3>
                                <div class="col-full">
                                    <div class="completo bs-callout bs-callout-info">
                                        <div class="col-half">
                                            <h3>Inscrição <?php $chamadaTemp->isMostrarLinkInscricao() ? print $chamadaTemp->getHtmlFlagInscricao() : print ""; ?></h3>
                                            <?php !$chamadaTemp->isMostrarLinkInscricao() ? print "<p>{$chamadaTemp->getHtmlFlagInscricao()}</p>" : print ""; ?>
                                            <p><i class="fa fa-pencil"></i> Período de Inscrição: <?php print $chamadaTemp->getLinkInscricao($chamadaTemp->getDsPeriodoInscricao()); ?></p>

                                            <?php
                                            if (!$logadoCand) {
                                                ?>
                                                <p><i class="fa fa-info-circle"></i> Atenção: Para se inscrever neste edital você deve estar cadastrado em nosso sistema de seleção.</p>
                                                <?php
                                            } else {
                                                // Candidato está logado
                                                ?>
                                                <p><i class="fa fa-info-circle"></i> <?php print $chamadaTemp->getMsgLogadoBoxInscricao(); ?></p>
                                            <?php } ?>

                                        </div>
                                        <div class="col-half">
                                            <h3>Resultado</h3>
                                            <p><?php print $chamadaTemp->getHtmlFlagResultado(); ?></p>
                                            <div>
                                                <?php print $chamadaTemp->getHtmlCaixaResultado(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                // Recuperando etapas de seleção
                                if ($chamadaTemp->getPCH_ID_CHAMADA() != $chamada->getPCH_ID_CHAMADA()) {
                                    // recuperando etapas de seleção
                                    $etapasSelTemp = buscarEtapaPorChamadaCT($chamadaTemp->getPCH_ID_CHAMADA());
                                } else {
                                    $etapasSelTemp = $etapasSel;
                                }
                                ?>


                                <div id="processo" class="completo">
                                    <?php
                                    // validando dados
                                    if (Util::vazioNulo($etapasSelTemp)) {
                                        ?>
                                        <div class="col-full">
                                            <div class="callout callout-info">
                                                <i class='fa fa-warning'></i> Etapas de seleção não cadastradas.
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                        // percorrendo etapas
                                        for ($i = 0; $i < count($etapasSelTemp); $i++) {
                                            $etapa = $etapasSelTemp[$i];
                                            ?>
                                            <div class="col-half m01" style="padding: 0 15px 15px 15px;">
                                                <h3><?php print $etapa->getNomeEtapa(); ?> <?php $etapa->isEmPeriodoRecurso() ? print $etapa->getHtmlFlagEtapa($chamadaTemp) : print ""; ?></h3>
                                                <p><?php !$etapa->isEmPeriodoRecurso() ? print $etapa->getHtmlFlagEtapa($chamadaTemp) : print ""; ?></p>
                                                <?php print $etapa->getHtmlCaixaEtapa($chamadaTemp); ?>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div id="vagas" class="completo m04">
                            <h3 class="sublinhado">Vagas</h3>
                            <div class="col-full m01">
                                <?php print tabelaVagasPorChamada($chamadaTemp, $processo); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            $("[data-toggle=popover]").popover();
        });
    </script>
<?php }
?>