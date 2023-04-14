<!DOCTYPE html>
<html>
    <head>     
        <title>Avaliar Candidato - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/negocio/Usuario.php");

        // todos usuarios, com exceçao do candidato
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL && estaLogado(Usuario::$USUARIO_AVALIADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        $loginRestrito = estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL;
        if ($loginRestrito) {

            if (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
                $curso = buscarCursoPorCoordenadorCT(getIdUsuarioLogado());
            } else {
                // recuperando usuario para manipulaçao: caso avaliador
                $usu = buscarUsuarioPorIdCT(getIdUsuarioLogado());
                $curso = !Util::vazioNulo($usu->getUSR_ID_CUR_AVALIADOR()) ? buscarCursoPorIdCT($usu->getUSR_ID_CUR_AVALIADOR()) : NULL;
            }

            if ($curso == NULL) {
                new Mensagem("Você ainda não está associado a um curso.", Mensagem::$MENSAGEM_ERRO);
                return;
            }
        }

        //verificando passagem por get
        if (!isset($_GET['idInscricao'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando inscrição e dados adicionais
        $inscricao = buscarInscricaoComPermissaoCT($_GET['idInscricao'], getIdUsuarioLogado(), TRUE);
        $processo = buscarProcessoComPermissaoCT($inscricao->getPRC_ID_PROCESSO());
        $chamada = buscarChamadaPorIdCT($inscricao->getPCH_ID_CHAMADA(), $inscricao->getPRC_ID_PROCESSO());
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/inscricaoProcesso/listarAvaliacaoCegaInsc.php">Avaliação Cega</a> > <strong>Avaliar Candidato</strong></h1>
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
                                    <p>
                                        <i class="fa fa-user"></i> 
                                        <b>Inscrição:</b> <?php print $inscricao->getIPR_ID_INSCRICAO(); ?>  
                                        <?php
                                        // tem opção de inscrição?
                                        if (ProcessoChamada::temOpcaoInscricao($chamada)) {
                                            $barra = FALSE;
                                            ?>
                                        <p>
                                            <i class="fa fa-info-circle"></i>
                                            <?php
                                            // área de atuação
                                            if ($chamada->admiteAreaAtuacaoObj()) {
                                                // recuperando area
                                                $areaAtu = buscarAreaAtuChamadaPorIdCT($inscricao->getAAC_ID_AREA_CHAMADA());
                                                ?>
                                                <b>Área:</b> <?php print $areaAtu->ARC_NM_SUBAREA_CONH; ?>
                                                <?php
                                                $barra = TRUE;
                                            }

                                            // reserva de vaga
                                            if ($chamada->admiteReservaVagaObj()) {
                                                ?>
                                                <?php if ($barra) { ?>
                                                <separador class='barra'></separador>
                                            <?php } ?>
                                            <b>Vaga:</b> <?php print getDsReservaVagaInscricaoCT($inscricao->getRVC_ID_RESERVA_CHAMADA()); ?>
                                            <?php
                                            $barra = TRUE;
                                        }

                                        // polos
                                        if ($chamada->admitePoloObj()) {
                                            $polos = buscarPoloPorInscricaoCT($inscricao->getIPR_ID_INSCRICAO());
                                            $dsPolo = count($polos) == 1 ? "Polo:" : "Polos:";
                                            ?>
                                            <?php if ($barra) { ?>
                                                <separador class='barra'></separador>
                                            <?php } ?>
                                            <b><?php print $dsPolo; ?></b> <?php print arrayParaStr($polos); ?>
                                            <?php
                                            $barra = TRUE;
                                        }
                                        ?>
                                        </p>
                                    <?php }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php print Util::$MSG_CAMPO_OBRIG_TODOS; ?>

                <div class="col-full">
                    <form id="formAvalInfComp" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTProcesso.php?acao=avaliarInfCompCega" ?>'>
                        <input type="hidden" name="valido" value="ctprocesso">
                        <input type="hidden" name="idInscricao" value="<?php print $inscricao->getIPR_ID_INSCRICAO() ?>">
                        <input type="hidden" name="idProcesso" value="<?php print $inscricao->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" name="idChamada" value="<?php print $inscricao->getPCH_ID_CHAMADA(); ?>">

                        <?php include "$CFG->rpasta/visao/inscricaoProcesso/fragmentoAvaliarInfComp.php"; ?>

                        <div id="divBotoes" class="campo-botoes">
                            <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                            <input type="button" class="btn btn-default"  onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/inscricaoProcesso/listarAvaliacaoCegaInsc.php" ?>'" value="Voltar">
                        </div>
                        <div id="divMensagem" class="col-full campo-carregando" style="display:none">
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
        carregaScript("jquery.price_format");
        ?>
    </body>

    <script type="text/javascript">
                $(document).ready(function() {
        //validando form
        $("#formAvalInfComp").validate({
        // ignore: "",
        submitHandler: function(form) {
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

                domicilioProximo: {
                required: true
                }
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