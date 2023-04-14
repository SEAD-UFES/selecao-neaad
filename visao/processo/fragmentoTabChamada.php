<?php
// deve haver a variavel para manipulacao de dados
if (isset($processo)) {

    // recuperando chamadas do processo
    $listaChamadas = buscarChamadaPorProcessoCT($processo->getPRC_ID_PROCESSO());

    // verificando se é possível criar nova chamada
    $criarEtapa = $processo->permiteCriarChamada($listaChamadas);
    $habilitadoNovaChamada = $criarEtapa[0];
    $tituloNovaChamada = $habilitadoNovaChamada ? "Criar nova Chamada" : $criarEtapa[1];

    // tentando recuperar chamada a ser aberta
    $idChamadaAberta = isset($_GET['idChamada']) ? $_GET['idChamada'] : $processo->PCH_ID_ULT_CHAMADA;
    ?>

    <div class="completo">    
        <input title="<?php print $tituloNovaChamada; ?>" <?php !$habilitadoNovaChamada ? print "disabled" : print "" ?> id="botaoChamada" class="btn btn-primary" type="button" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/chamada/criarChamada.php?idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>'" value="Nova Chamada">
    </div>

    <div class="panel-group completo m02" id="accordionChamada">
        <?php
        // percorrendo chamadas para impressão
        foreach ($listaChamadas as $chamada) {
            ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordionChamada" href="#collapse<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                            <?php print $chamada->getPCH_DS_CHAMADA(TRUE); ?>
                        </a>
                    </h4>
                </div>

                <div id="collapse<?php print $chamada->getPCH_ID_CHAMADA(); ?>" class="panel-collapse collapse <?php $chamada->getPCH_ID_CHAMADA() == $idChamadaAberta ? print "in" : print ""; ?>">
                    <div class="panel-body">
                        <script type="text/javascript">
                            function ativarChamada(idChamada) {
                                // preparando tela para processamento...
                                $("#divInfoAtivacaoChamada").hide();
                                $("#mensagemAtivacaoChamada").show();

                                // enviando dados 
                                $.ajax({
                                    type: "POST",
                                    url: getURLServidor() + "/controle/CTAjax.php?atualizacao=ativarChamada",
                                    data: {"idProcesso": '<?php print $chamada->getPRC_ID_PROCESSO(); ?>', "idChamada": idChamada},
                                    dataType: "json",
                                    success: function (json) {
                                        if (!json['situacao'])
                                        {
                                            //alert de erro
                                            alert("Não foi possível ativar a chamada. Corrija os problemas a seguir:\n\n" + json['msg']);

                                            // restabelecendo
                                            $("#mensagemAtivacaoChamada").hide();
                                            $("#divInfoAtivacaoChamada").show();
                                        } else {

                                            alert("Chamada ativada com sucesso.\nVocê pode editar a notícia de publicação na aba 'Notícia'.");
                                            location.reload();

                                        }
                                    },
                                    error: function (xhr, ajaxOptions, thrownError) {
                                        var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nTente novamente.\n\n";
                                        msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                                        // exibindo mensagem
                                        alert(msg);

                                        // restabelecendo
                                        $("#mensagemAtivacaoChamada").hide();
                                        $("#divInfoAtivacaoChamada").show();

                                    }
                                });
                                return false;
                            }


                            function solicitarAtivacao(idChamada) {
                                // preparando tela para processamento...
                                $("#divInfoAtivacaoChamada").hide();
                                $("#mensagemAtivacaoChamada").show();

                                // enviando dados 
                                $.ajax({
                                    type: "POST",
                                    url: getURLServidor() + "/controle/CTAjax.php?atualizacao=solicitarAtivacaoCham",
                                    data: {"idProcesso": '<?php print $chamada->getPRC_ID_PROCESSO(); ?>', "idChamada": idChamada},
                                    dataType: "json",
                                    success: function (json) {
                                        if (!json['situacao'])
                                        {
                                            //alert de erro
                                            alert("Não foi possível solicitar a ativação da chamada. Corrija os problemas a seguir:\n\n" + json['msg']);

                                            // restabelecendo
                                            $("#mensagemAtivacaoChamada").hide();
                                            $("#divInfoAtivacaoChamada").show();
                                        } else {

                                            alert("Solicitação de ativação executada com sucesso.");
                                            location.reload();

                                        }
                                    },
                                    error: function (xhr, ajaxOptions, thrownError) {
                                        var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nTente novamente.\n\n";
                                        msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                                        // exibindo mensagem
                                        alert(msg);

                                        // restabelecendo
                                        $("#mensagemAtivacaoChamada").hide();
                                        $("#divInfoAtivacaoChamada").show();

                                    }
                                });
                                return false;
                            }
                        </script>
                        <?php if ($chamada->isAtiva()) { ?>
                            <h4>Fase Atual: <span class="text-info"><?php print $chamada->getDsFaseChamada(); ?></span></h4>
                        <?php } else { ?>
                            <div id="divInfoAtivacaoChamada" class="callout callout-warning">
                                <?php if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR)) { ?>
                                    Esta Chamada não está ativada e <strong>não aparecerá aos candidatos</strong>.<br>
                                    <?php if ($chamada->isSolicitouAtivacao()) {
                                        ?>
                                        Solicitação de ativação enviada
                                        em <b><?php echo $chamada->getPCH_ATV_DT_SOLICITACAO(); ?></b>
                                        por <b><?php echo $chamada->getATV_NOME_SOLICITANTE(); ?></b>.
                                    <?php }
                                    ?>
                                    <a href="#" onclick="javascript: return ativarChamada('<?php print $chamada->getPCH_ID_CHAMADA(); ?>');">Clique aqui para ativar esta chamada</a>.
                                    <?php
                                } elseif (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
                                    if ($chamada->isSolicitouAtivacao()) {
                                        ?>
                                        Esta Chamada não está ativada e <strong>não aparecerá aos candidatos</strong>.<br>
                                        Solicitação de ativação já enviada 
                                        em <b><?php echo $chamada->getPCH_ATV_DT_SOLICITACAO(); ?></b>
                                        por <b><?php echo $chamada->getATV_NOME_SOLICITANTE(); ?></b>
                                    <?php } else {
                                        ?>
                                        Esta Chamada não está ativada e <strong>não aparecerá aos candidatos</strong>. Quando estiver <strong>tudo pronto</strong>, <a href="#" onclick="javascript: return solicitarAtivacao('<?php print $chamada->getPCH_ID_CHAMADA(); ?>');">solicite a ativação ao Administrador</a>.
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <div id="mensagemAtivacaoChamada" style="display: none;">
                                <div class="alert alert-info">
                                    Aguarde o processamento...
                                </div>
                            </div>
                        <?php } ?>

                        <div class="panel-group m02" id="accordionChamadaCal<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class='pull-left' style='width: 99%' data-toggle='collapse' data-parent="#accordionChamadaCal<?php print $chamada->getPCH_ID_CHAMADA(); ?>" href="#collapseChamadaCal<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                                            Calendário
                                        </a> 
                                        <span class='pull-right' style='width:1%'>
                                            <?php if (ProcessoChamada::permiteEditarCalendario($processo)) { ?>
                                                <a title='Alterar calendário da chamada' href='<?php print "$CFG->rwww/visao/chamada/alterarCalendarioChamada.php?idProcesso={$chamada->getPRC_ID_PROCESSO()}&idChamada={$chamada->getPCH_ID_CHAMADA()}"; ?>'><i class='fa fa-edit'></i></a>
                                            <?php } else { ?>
                                                <a onclick="javascript: return false;" title='Não é possível alterar o calendário da chamada' href=''><i class='fa fa-ban'></i></a>
                                            <?php } ?>
                                        </span>
                                    </h4>
                                </div>
                                <div id="collapseChamadaCal<?php print $chamada->getPCH_ID_CHAMADA(); ?>" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <?php
                                        // recuperando itens do calendário
                                        $itensCal = $chamada->listaItensCalendario();

                                        // percorrendo e imprimindo
                                        foreach ($itensCal as $item) {
                                            $classeItem = $item['status'] == ProcessoChamada::$EVENTO_PASSADO ? "" : ($item['status'] == ProcessoChamada::$EVENTO_PRESENTE ? "" : "");
                                            ?>
                                            <p><b><?php print $item['nmItem']; ?></b> <span class="<?php print $classeItem; ?>"><?php print $item['vlItem']; ?></span></p>
                                        <?php }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel-group" id="accordionChamadaConf<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class='pull-left' style='width: 99%' data-toggle='collapse' data-parent="#accordionChamadaConf<?php print $chamada->getPCH_ID_CHAMADA(); ?>" href="#collapseChamadaConf<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                                            Configurações da Chamada
                                        </a> 
                                        <span class='pull-right' style='width:1%'>
                                            <?php if (ProcessoChamada::permiteEditarConfiguracao($processo)) { ?>
                                                <a title='Alterar as configurações da chamada' href='<?php print "$CFG->rwww/visao/chamada/alterarConfiguracaoChamada.php?idProcesso={$chamada->getPRC_ID_PROCESSO()}&idChamada={$chamada->getPCH_ID_CHAMADA()}"; ?>'><i class='fa fa-edit'></i></a>
                                            <?php } else { ?>
                                                <a onclick="javascript: return false;" title='Não é possível alterar as configurações da chamada' href=''><i class='fa fa-ban'></i></a>
                                            <?php } ?>
                                        </span>
                                    </h4>
                                </div>
                                <div id="collapseChamadaConf<?php print $chamada->getPCH_ID_CHAMADA(); ?>" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <?php
                                        // questao de polo
                                        if ($chamada->admitePoloObj()) {
                                            $qtPolos = contarPoloPorChamadaCT($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo());
                                            $polos = stringPolosChamada($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo());
                                            ?>
                                            <p><b>Polos participantes<?php $qtPolos > 0 ? print "(<strong>$qtPolos</strong>)" : ""; ?>:</b>
                                                <?php $qtPolos > 0 ? print $polos[0] : print Processo::getMsgPoloNaoConfigurado(); ?></p>
                                            <?php
                                            // polos desativados? se tiver, então  recuperando e mostrando
                                            $qtPolosDesativados = contarPoloPorChamadaCT($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloInativo());
                                            if ($qtPolosDesativados > 0) {
                                                $polosDesativados = stringPolosChamada($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloInativo());
                                                ?>
                                                <p><b>Polos Desativados (<?php print $qtPolosDesativados; ?>):</b>
                                                    <?php print $polosDesativados[0]; ?></p>
                                            <?php }
                                            ?>

                                            <?php if ($qtPolos > 0) { ?>
                                                <p><b><span title="Informa o número de polos que o candidato pode escolher no ato da inscrição.">Número Máx Opção de Polo:</b>
                                                    <?php print $chamada->getPCH_NR_MAX_OPCAO_POLO(); ?></p>
                                            <?php } ?>
                                        <?php } else {
                                            ?>
                                            <p><b>Polos participantes:</b>
                                                <i><?php print Processo::getMsgSemPolo(); ?></i></p>
                                            <?php
                                        }

                                        // questao de area de atuação
                                        if ($chamada->admiteAreaAtuacaoObj()) {
                                            $areaAtu = stringAreaAtuChamada($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva());
                                            $qtAreaAtu = contarAreaAtuPorChamadaCT($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva());
                                            ?>
                                            <p><b>Áreas de Atuação (<?php print $qtAreaAtu; ?>):</b>
                                                <?php print $areaAtu; ?></p>
                                            <?php
                                        } else {
                                            ?>  
                                            <p><b>Áreas de Atuação:</b>
                                                <i><?php print ProcessoChamada::getMsgSemAreaAtuacao(); ?></i></p>
                                            <?php
                                        }
                                        // áreas desativadas? então recuperando e mostrando
                                        $qtAreaAtuDesativadas = contarAreaAtuPorChamadaCT($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaInativa());
                                        if ($qtAreaAtuDesativadas > 0) {
                                            $areaAtuDesativadas = stringAreaAtuChamada($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaInativa());
                                            ?>
                                            <p><b>Áreas Desativadas (<?php print $qtAreaAtuDesativadas; ?>):</b>
                                                <?php print $areaAtuDesativadas; ?></p>
                                            <?php
                                        }

                                        // questao de reserva de vaga
                                        if ($chamada->admiteReservaVagaObj()) {
                                            $reservaVaga = stringReservaVagaChamada($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaAtiva());
                                            $qtReserva = contarReservaVagaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaAtiva());
                                            ?>
                                            <p><b>Reserva de Vagas (<?php print $qtReserva; ?>):</b>
                                                <?php print $reservaVaga; ?></p>
                                            <?php
                                        } else {
                                            ?>  
                                            <p><b>Reserva de Vagas:</b>
                                                <i><?php print ProcessoChamada::getMsgSemAreaAtuacao(); ?></i></p>
                                            <?php
                                        }
                                        $qtReservaDesativadas = contarReservaVagaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaInativa());
                                        if ($qtReservaDesativadas > 0) {
                                            $reservaDesativadas = stringReservaVagaChamada($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaInativa());
                                            ?>
                                            <p><b>Reserva Inativas (<?php print $qtReservaDesativadas; ?>):</b>
                                                <?php print $reservaDesativadas; ?></p>
                                        <?php }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-group" id="accordionChamadaVagas<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class='pull-left' style='width: 99%' data-toggle='collapse' data-parent="#accordionChamadaVagas<?php print $chamada->getPCH_ID_CHAMADA(); ?>" href="#collapseChamadaVagas<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                                            Vagas
                                        </a> 
                                        <span class='pull-right' style=' width:1%'>
                                            <?php if (ProcessoChamada::permiteEditarConfiguracao($processo)) { ?>
                                                <a title='Alterar a quantidade de vagas da chamada' href='<?php print "$CFG->rwww/visao/chamada/alterarVagasChamada.php?idProcesso={$chamada->getPRC_ID_PROCESSO()}&idChamada={$chamada->getPCH_ID_CHAMADA()}"; ?>'><i class='fa fa-edit'></i></a>
                                            <?php } else { ?>
                                                <a onclick="javascript: return false;" title='Não é possível alterar a quantidade de vagas da chamada' href=''><i class='fa fa-ban'></i></a>
                                            <?php } ?>
                                        </span>
                                    </h4>
                                </div>
                                <div id="collapseChamadaVagas<?php print $chamada->getPCH_ID_CHAMADA(); ?>" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <?php print tabelaVagasPorChamada($chamada, $processo); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-group" id="accordionChamadaMsg<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class='pull-left' style='width: 99%' data-toggle='collapse' data-parent="#accordionChamadaMsg<?php print $chamada->getPCH_ID_CHAMADA(); ?>" href="#collapseChamadaMsg<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                                            Mensagens
                                        </a> 
                                        <span class='pull-right' style=' width:1%'>
                                            <?php if (ProcessoChamada::permiteEditarConfiguracao($processo)) { ?>
                                                <a title='Alterar mensagens padrão da chamada' href='<?php print "$CFG->rwww/visao/chamada/alterarMensagensChamada.php?idProcesso={$chamada->getPRC_ID_PROCESSO()}&idChamada={$chamada->getPCH_ID_CHAMADA()}"; ?>'><i class='fa fa-edit'></i></a>
                                            <?php } else { ?>
                                                <a onclick="javascript: return false;" title='Não é possível alterar as mensagens da chamada' href=''><i class='fa fa-ban'></i></a>
                                            <?php } ?>
                                        </span>
                                    </h4>
                                </div>
                                <div id="collapseChamadaMsg<?php print $chamada->getPCH_ID_CHAMADA(); ?>" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <p><b><span title="Mensagem a ser exibida no final do Comprovante de Inscrição do candidato">Mensagem do Comprovante de Inscrição:</b></p>
                                        <p align="justify"><?php print $chamada->getPCH_TXT_COMP_INSCRICAO(); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php }
?>