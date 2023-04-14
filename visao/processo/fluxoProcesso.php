<!DOCTYPE html>
<html>
    <head>     
        <title>Visualizar Fluxo do Edital - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/negocio/TipoCargo.php");

        // apenas administrador ou coordenador
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null && estaLogado(Usuario::$USUARIO_COORDENADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idProcesso'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        //verificando permissão e recuperando dados para processamento
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);

        // permite exibir fluxo
        if (!$processo->permiteExibirFluxo()) {
            new Mensagem("Este edital não possui chamada.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // recuperando última chamada o processo: OBS: Nem sempre é a última chamada de fato, pois pode ter chamada em construção
        $chamada = buscarChamadaPorIdCT($processo->PCH_ID_ULT_CHAMADA);

        // recuperando chamadas do processo para montar abas
        $listaChamadas = buscarChamadaPorProcessoCT($processo->getPRC_ID_PROCESSO());

        // recuperando chamada aberta
        $temp = $listaChamadas[count($listaChamadas) - 1];
        $idsChamadas = array();
        foreach ($listaChamadas as $chamada) {
            $idsChamadas [] = $chamada->getPCH_ID_CHAMADA();
        }
        $idChamadaAberta = !isset($_GET['idChamada']) || in_array($_GET['idChamada'], $idsChamadas) === FALSE ? $temp->getPCH_ID_CHAMADA() : $_GET['idChamada'];

        // recuperando etapas de seleção
        $etapasSel = buscarEtapaPorChamadaCT($chamada->getPCH_ID_CHAMADA());
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
                    <h1>Você está em: <a href="<?php echo "$CFG->rwww/visao/processo/listarProcessoAdmin.php"; ?>">Editais</a> > <strong>Fluxo</strong></h1>
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
                                    <?php print $processo->getHTMLDsEditalCompleta(); ?> <separador class="barra"></separador>
                                    <?php echo $processo->getHTMLLinkFluxo(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="fluxoAdmin" class="col-full m02">
                    <div class="tabbable">
                        <ul id="tabProcesso" class="nav nav-tabs">
                            <?php
                            require_once ($CFG->rpasta . "/visao/processo/fragmentoFinalizacaoProcesso.php");
                            $dtFimModal = !$chamada->isFinalizada() ? $chamada->getPCH_DT_FINALIZACAO(TRUE) : $processo->getPRC_DT_FIM();
                            PRO__fragmentoAlterarFimChamada($chamada->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), $dtFimModal, !$chamada->isFinalizada());
                            PRO__fragmentoReabrirEdital($chamada->getPRC_ID_PROCESSO());
                            ?>

                            <?php
                            // percorrendo chamadas para criar título das abas
                            foreach ($listaChamadas as $chamadaTemp) {
                                ?>
                                <li <?php $chamadaTemp->getPCH_ID_CHAMADA() === $idChamadaAberta ? print "class='active'" : print ""; ?>><a href="#chamada<?php echo $chamadaTemp->getPCH_ID_CHAMADA(); ?>" data-toggle="tab"><?php echo $chamadaTemp->getPCH_DS_CHAMADA(TRUE); ?></a></li>
                            <?php } ?>
                        </ul>

                        <div class="tab-content">
                            <?php
                            // percorrendo chamadas para preencher dados
                            foreach ($listaChamadas as $chamadaTemp) {
                                // bloqueando links
                                if ($chamadaTemp->isFinalizada() || $chamada->isAguardandoFechamentoAuto()) {
                                    $tituloEBloqueioLink = "onclick='return false;' title='Chamada finalizada (ou em fase de finalização). Não é possível editar os dados'";
                                    $hrefLink = "";
                                    $iconeLink = "fa fa-ban";
                                } else {
                                    $tituloEBloqueioLink = "title='Alterar calendário'";
                                    $hrefLink = "href='$CFG->rwww/visao/chamada/alterarCalendarioChamada.php?idProcesso={$chamadaTemp->getPRC_ID_PROCESSO()}&idChamada={$chamadaTemp->getPCH_ID_CHAMADA()}'";
                                    $iconeLink = "fa fa-edit";
                                }
                                ?>

                                <div class="tab-pane <?php $chamadaTemp->getPCH_ID_CHAMADA() === $idChamadaAberta ? print "active" : print ""; ?> <?php $chamadaTemp->isFinalizada() ? print "chamada-inativa" : print ""; ?>" id="chamada<?php echo $chamadaTemp->getPCH_ID_CHAMADA(); ?>">

                                    <div class="completo m02">
                                        <div class="col-md-8 col-sm-12 col-xs-12">
                                            <h3 class="sublinhado">Resumo</h3>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <?php if ($processo->isFechado()) { // edital está finalizado  ?>
                                                    <div class="callout callout-info">
                                                        Edital finalizado em <?php echo $processo->getPRC_DT_FIM(); ?>. 
                                                        <?php if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR)) { ?>
                                                            <a data-toggle="modal" data-target="#pergReabrirEdital">Reabrir edital »</a>
                                                        <?php } elseif (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
                                                            ?>    
                                                            Contate o Administrador caso seja necessária a reabertura deste edital.
                                                        <?php }
                                                        ?>
                                                    </div>
                                                <?php } elseif ($chamadaTemp->isFinalizada() && !$chamadaTemp->isChamadaAtual()) { // chamada finalizada e não é a chamada atual
                                                    ?>
                                                    <div class="callout callout-info">
                                                        Esta chamada está finalizada.
                                                    </div>  
                                                    <?php
                                                } elseif (!$chamadaTemp->isAtiva()) { // a chamada está em construção
                                                    ?>
                                                    <div class="callout callout-warning">Esta chamada está em construção. Conclua as configurações necessárias para prosseguir.</div>
                                                    <?php
                                                } elseif (!$chamadaTemp->isFinalizada()) {
                                                    print $chamadaTemp->getHTMLFaseAtualFluxoAdmin();
                                                    print $chamadaTemp->getHTMLProximaFaseFluxoAdmin();
                                                    ?>
                                                    <?php if ($chamadaTemp->isCalendarioAtrasado()) { ?>
                                                        <div class="callout callout-danger"><b>Atenção!</b> O calendário está atrasado.</div>
                                                    <?php } ?>
                                                    <?php
                                                } elseif ($processo->isEmFinalizacao()) { // todas as chamadas estão finalizadas, aguardando o fim do processo
                                                    ?>
                                                    <div class="callout callout-info">
                                                        Chamada finalizada. O Edital será finalizado automaticamente em <?php echo $processo->getPRC_DT_FIM(); ?>. 
                                                        <?php if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR)) { ?>
                                                            <a data-toggle="modal" data-target="#pergAltFimChamada">Alterar data »</a>
                                                        <?php } elseif (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
                                                            ?>    
                                                            Contate o Administrador caso seja necessária a alteração dessa data.
                                                        <?php }
                                                        ?>
                                                    </div>
                                                <?php } elseif ($chamadaTemp->isFinalizada()) { // chamada finalizada
                                                    ?>
                                                    <div class="callout callout-info">
                                                        Esta chamada está finalizada.
                                                    </div>  
                                                <?php }
                                                ?>

                                                <?php
                                                // definindo exibição dos botões de gerencia
                                                $exibExportar = $processo->permiteExibirAcaoCdt($chamadaTemp) ? "href='$CFG->rwww/visao/inscricaoProcesso/exportarDados.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idChamada={$chamadaTemp->getPCH_ID_CHAMADA()}'" : "disabled";
                                                ?>
                                                <a class="btn btn-default m01" <?php echo $exibExportar; ?>><i class ="fa fa-mail-forward"></i> Exportar dados</a>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-12 col-xs-12 m02-mob">
                                            <h3 class="sublinhado">Gerenciar</h3>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <p style="text-align:center;">
                                                    <a class="btn btn-primary completo" target="_blank" href="<?php print "$CFG->rwww/visao/processo/manterProcessoAdmin.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idChamada={$chamadaTemp->getPCH_ID_CHAMADA()}&" . Util::$ABA_PARAM . "=" . Util::$ABA_MPA_CHAMADA; ?>">Chamada</button>
                                                        <?php
                                                        // definindo exibição dos botões de gerencia
                                                        $exibInscritos = $processo->permiteExibirAcaoCdt($chamadaTemp) ? "href='$CFG->rwww/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idChamada={$chamadaTemp->getPCH_ID_CHAMADA()}'" : "disabled";
                                                        $exibRecursos = $processo->permiteExibirAcaoCdt($chamadaTemp) ? "href='$CFG->rwww/visao/recurso/listarRecursoProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idChamada={$chamadaTemp->getPCH_ID_CHAMADA()}'" : "disabled";
                                                        $exibResultados = $processo->permiteExibirAcaoCdt($chamadaTemp) ? "href='$CFG->rwww/visao/processo/gerenciarResultadosProcesso.php?idProcesso={$processo->getPRC_ID_PROCESSO()}&idChamada={$chamadaTemp->getPCH_ID_CHAMADA()}'" : "disabled";
                                                        ?>
                                                        <a target="_blank" class="btn btn-primary completo m01" <?php echo $exibInscritos; ?>>Inscritos</a>
                                                        <a target="_blank" class="btn btn-primary completo m01" <?php echo $exibRecursos; ?>>Recursos</a>
                                                        <a class="btn btn-primary completo m01" <?php echo $exibResultados; ?>>Resultados</a> 
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-sm-12 col-xs-12 m02">
                                        <h3 class="sublinhado">Fluxograma</h3>
                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            <div class="panel gerenciar-etapa <?php $chamadaTemp->isEmPeriodoInscricao() ? print "ativa" : print "inativa"; ?>">
                                                <div class="panel-heading">
                                                    Inscrição
                                                    <a target="_blank" <?php echo $tituloEBloqueioLink; ?> <?php print $hrefLink; ?>><i class="<?php echo $iconeLink; ?>"></i></a>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="completo">
                                                        <p><i class="fa fa-pencil"></i> Período de Inscrição: <?php print $chamadaTemp->getDsPeriodoInscricao(); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-sm-12 col-xs-12">
                                            <div class="panel gerenciar-etapa <?php $chamadaTemp->isAguardandoFechamentoAuto() ? print "ativa" : print "inativa"; ?>">
                                                <div class="panel-heading">
                                                    Resultado final
                                                    <a target="_blank" <?php echo $tituloEBloqueioLink; ?> <?php print $hrefLink; ?>><i class="<?php echo $iconeLink; ?>"></i></a>
                                                </div>
                                                <div class="panel-body">
                                                    <?php print $chamadaTemp->getHtmlCaixaResultadoAdmin(); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="completo">
                                            <?php
                                            // Recuperando etapas de seleção
                                            if ($chamadaTemp->getPCH_ID_CHAMADA() != $chamada->getPCH_ID_CHAMADA()) {
                                                // recuperando etapas de seleção
                                                $etapasSelTemp = buscarEtapaPorChamadaCT($chamadaTemp->getPCH_ID_CHAMADA());
                                            } else {
                                                $etapasSelTemp = $etapasSel;
                                            }
                                            ?>

                                            <?php
                                            // validando dados
                                            if (Util::vazioNulo($etapasSelTemp)) {
                                                ?>
                                                <div class="callout callout-info">
                                                    <i class='fa fa-warning'></i> Etapas de seleção não cadastradas.
                                                </div>
                                                <?php
                                            } else {

                                                // percorrendo etapas
                                                $qtEtapas = count($etapasSelTemp);
                                                for ($i = 0; $i < $qtEtapas; $i++) {
                                                    $etapa = $etapasSelTemp[$i];
                                                    ?>

                                                    <div class="<?php $qtEtapas == 1 ? print "col-md-12 col-sm-12 col-xs-12" : print "col-md-6 col-sm-12 col-xs-12"; ?>">

                                                        <div class="panel gerenciar-etapa <?php $etapa->isEtapaCorrente() && !$etapa->isFinalizada() && !$chamadaTemp->isEmPeriodoInscricao() ? print "ativa" : print "inativa"; ?>">
                                                            <div class="panel-heading">
                                                                <?php echo $etapa->getNomeEtapa(); ?>
                                                                <a target="_blank" <?php echo $tituloEBloqueioLink; ?> <?php print $hrefLink; ?>><i class="<?php echo $iconeLink; ?>"></i></a>
                                                            </div>
                                                            <div class="panel-body">
                                                                <h4 <?php $etapa->isAberta() && !$chamadaTemp->isEmPeriodoInscricao() ? print "class='ativa'" : print ""; ?>>Resultado Parcial da <?php echo $etapa->getNomeEtapa(); ?></h4>
                                                                <?php print $etapa->getHtmlResulParcialAdmin(); ?>
                                                                <div class="m02">
                                                                    <h4 <?php $etapa->isEmRecurso() && !$etapa->isPeriodoRecursoAnterior() ? print "class='ativa'" : print ""; ?>>Período para Recursos da <?php echo $etapa->getNomeEtapa(); ?></h4>
                                                                    <?php print $etapa->getHTMLRecursosAdmin(); ?>
                                                                </div>
                                                                <div class="m02">
                                                                    <h4 <?php $etapa->isProcessamentoResulEtapa() ? print "class='ativa'" : print ""; ?>>Resultado da <?php echo $etapa->getNomeEtapa(); ?></h4>
                                                                    <?php print $etapa->getHTMLResulFinalAdmin(); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <input class="btn btn-default" type="button" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/processo/listarProcessoAdmin.php" ?>'" value="Voltar">
            </div> 
        </div>

        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("jquery.maskedinput");
        carregaScript("metodos-adicionaisBR");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            function sucOperacao() {
                $().toastmessage('showToast', {
                    text: '<b>Operação realizada com sucesso.</b>',
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

