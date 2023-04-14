<!DOCTYPE html>
<html>
    <head>     
        <title>Gerenciar Notas do Candidato - Seleção EAD</title>
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

        // verificando login
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL && estaLogado(Usuario::$USUARIO_COORDENADOR) == NULL) {
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

        // buscando dados para análise
        $inscricao = buscarInscricaoComPermissaoCT($_GET['idInscricao'], getIdUsuarioLogado());
        $processo = buscarProcessoComPermissaoCT($inscricao->getPRC_ID_PROCESSO());

        // verificando se e possivel gerenciar nota
        if (!$inscricao->avalAutoConcluida()) {
            //redirecionando
            new Mensagem("É necessário executar a avaliação automática...", Mensagem::$MENSAGEM_INFORMACAO, NULL, "execAvalAuto", "$CFG->rwww/visao/inscricaoProcesso/avaliacaoAutomatica.php?idProcesso={$inscricao->getPRC_ID_PROCESSO()}");
            return;
        }

        // recuperando etapa Ativa
        $etapaAtiva = buscarEtapaEmAndamentoCT($inscricao->getPCH_ID_CHAMADA());

        if ($etapaAtiva == NULL) {
            New Mensagem("Não existem etapas de avaliação em andamento.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // pode editar
        if (!$inscricao->permiteEditarNotaTab($etapaAtiva)) {
            new Mensagem("Você não pode editar as notas deste candidato.", Mensagem::$MENSAGEM_ERRO);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww ?>/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso=<?php print $inscricao->getPRC_ID_PROCESSO(); ?>">Inscrição</a> > <strong>Gerenciar Notas</strong></h1>
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
                                    <p>
                                        <i class="fa fa-user"></i>
                                        <b>Candidato:</b> <?php print $inscricao->USR_DS_NOME_CDT; ?> <separador class='barra'></separador> 
                                    <b>Inscrição:</b> <?php print $inscricao->getIPR_NR_ORDEM_INSC(); ?> <separador class='barra'></separador> 
                                    <b>Chamada:</b> <?php print $inscricao->PCH_DS_CHAMADA; ?> <separador class='barra'></separador>
                                    <b>Data:</b> <?php print $inscricao->getIPR_DT_INSCRICAO(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full m02">
                    <?php
                    // campos para validacao
                    $regraValidacao = "";
                    $msgValidacao = "";
                    ?>
                    <form id="form" class="form-horizontal" method="post" action='<?php print "$CFG->rwww/controle/CTNotas.php?acao=registrarNota" ?>'>
                        <input type="hidden" name="valido" value="ctnotas">
                        <input type="hidden" name="idInscricao" value="<?php print $inscricao->getIPR_ID_INSCRICAO() ?>">
                        <input type="hidden" name="idProcesso" value="<?php print $inscricao->getPRC_ID_PROCESSO() ?>">
                        <input type="hidden" name="idChamada" value="<?php print $inscricao->getPCH_ID_CHAMADA() ?>">

                        <fieldset>
                            <legend>Situação da Inscrição</legend>
                            <div class="col-full">
                                <div>
                                    <b>Candidato Eliminado: </b>
                                    <span class='checkbox'>
                                        <label>
                                            <input <?php $inscricao->isEliminada() ? print "checked" : ""; ?> type='checkbox' name='eliminarCand' id='eliminarCand' value='<?php print InscricaoProcesso::$SIT_INSC_ELIMINADO; ?>'>
                                        </label>
                                    </span>
                                </div>
                                <div id='divEliminado' class="completo m01" style="display: none">
                                    <textarea class="form-control" placeholder='Justificativa da Eliminação' id='justEliminacaoCand' cols='60' rows='4' style='width:100%' name='justEliminacaoCand'><?php print $inscricao->getIPR_DS_OBS_NOTA(); ?></textarea>
                                    <div id='contadorJust' class='totalCaracteres'>caracteres restantes</div> 

                                    <?php
                                    $regraValidacao = adicionaConteudoVirgula($regraValidacao, "justEliminacaoCand: {
                                                                      required: function(element) {
                                                                        return ativaJustEliminacao();
                                                                        }
                                                                }");

                                    $msgValidacao = adicionaConteudoVirgula($msgValidacao, "justEliminacaoCand: {
                                                                    required: 'Campo obrigatório.'
                                                                }");
                                    ?>
                                </div>
                            </div>
                        </fieldset>

                        <?php
                        // recuperando dados para montar relatorio.
                        // recuperando etapas
                        $etapas = buscarEtapaPorChamadaCT($inscricao->getPCH_ID_CHAMADA());
                        ?>
                        <div class="tabbable completo m02">
                            <ul class="nav nav-tabs">
                                <?php
                                // gerando abas para etapas
                                for ($i = 0; $i < count($etapas); $i++) {
                                    ?>
                                    <li class = "<?php ($etapaAtiva->getESP_ID_ETAPA_SEL() === $etapas[$i]->getESP_ID_ETAPA_SEL()) ? print "active" : "" ?>"><a href = "#tab<?php print $i + 1; ?>" data-toggle = "tab"><?php print $etapas[$i]->getNomeEtapa(); ?></a></li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <div class="tab-content">
                                <?php
                                // gerando conteudo para abas
                                $mostrarConteudo = true;
                                for ($i = 0; $i < count($etapas); $i++) {
                                    $edicao = $etapaAtiva->getESP_ID_ETAPA_SEL() === $etapas[$i]->getESP_ID_ETAPA_SEL();
                                    ?>
                                    <div class="tab-pane <?php ($etapaAtiva->getESP_ID_ETAPA_SEL() === $etapas[$i]->getESP_ID_ETAPA_SEL()) ? print "active" : "" ?>" id="tab<?php print $i + 1; ?>">
                                        <?php
                                        // etapas ativas ou ja fechadas
                                        if ($mostrarConteudo) {
                                            // recuperando categorias da etapa
                                            $categorias = buscarCatAvalPorProcEtapaTpCT($inscricao->getPRC_ID_PROCESSO(), $etapas[$i]->getESP_NR_ETAPA_SEL(), NULL, FALSE);

                                            // loop nas categorias
                                            foreach ($categorias as $categoria) {
                                                // criando html das categorias
                                                ?>

                                                <fieldset class="col-full m02">
                                                    <legend><?php print $categoria->getHmlNomeCategoria(); ?></legend>

                                                    <div class="col-full">
                                                        <?php
                                                        // imprimindo tabela
                                                        print tabelaRelatorioNotas($inscricao, $categoria, $edicao);

                                                        if ($edicao) {
                                                            if ($categoria->isAvalAutomatica()) {
                                                                $regraValidacao = adicionaConteudoVirgula($regraValidacao, "{$categoria->getIdNotaManualCatAuto()}: {
                                                                    required: function(element) {
                                                                        return ativaObrigatoriedade($(\"#{$categoria->getIdNotaManualCatAuto()}\").val(),$(\"#{$categoria->getIdJustManualCatAuto()}\").val());
                                                                        }
                                                                },{$categoria->getIdJustManualCatAuto()}: {
                                                                    required: function(element) {
                                                                        return ativaObrigatoriedade($(\"#{$categoria->getIdNotaManualCatAuto()}\").val(),$(\"#{$categoria->getIdJustManualCatAuto()}\").val());
                                                                        }
                                                                }");

                                                                $msgValidacao = adicionaConteudoVirgula($msgValidacao, "{$categoria->getIdNotaManualCatAuto()}: {
                                                                    required: 'Campo obrigatório.'
                                                                },{$categoria->getIdJustManualCatAuto()}: {
                                                                    required: 'Campo obrigatório.'
                                                                }");
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </fieldset>
                                                <?php
                                            }

                                            if (!$edicao) {
                                                // imprimindo nota da etapa
                                                print imprimirNotaEtapa($inscricao, $etapas[$i]);
                                            }
                                        } else {
                                            ?>
                                            <div class="callout callout-info"><?php print EtapaSelProc::getMsgHtmlEtapaFechadaNota(); ?></div>
                                        <?php }
                                        ?>

                                        <?php
                                        // mecanismo de bloqueio de conteudo
                                        if ($etapaAtiva->getESP_ID_ETAPA_SEL() == $etapas[$i]->getESP_ID_ETAPA_SEL()) {
                                            $mostrarConteudo = false;
                                        }
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-full">
                            <div class='callout callout-info'>
                                <b>Confirmar visto <i class='fa fa-question-circle' title="Confirme que a nota e a situação do candidato foram vistas e revisadas"></i>:</b>
                                <span class='checkbox'>
                                    <label>
                                        <input required="true" type='checkbox' name='visto' id='visto' value='<?php print FLAG_BD_SIM; ?>'>
                                    </label>
                                </span>
                            </div>
                            <label class="error" for="visto" style="display: none"></label>
                            <?php
                            $regraValidacao = adicionaConteudoVirgula($regraValidacao, "visto: {
                                                                    required: true
                                                                }");

                            $msgValidacao = adicionaConteudoVirgula($msgValidacao, "visto: {
                                                                    required: 'Por favor, confirme que a nota e a situação do candidato foram vistas e revisadas.'
                                                                }");
                            ?>
                        </div>

                        <div id="divBotoes" class="col-full">
                            <input class="btn btn-success" id="submeter" type="submit" value="Salvar">
                            <?php $urlVoltar = "/visao/inscricaoProcesso/listarInscricaoProcesso.php?idProcesso={$inscricao->getPRC_ID_PROCESSO()}"; ?>
                            <input type="button" class="btn btn-default"  onclick="javascript: window.location = '<?php print $CFG->rwww . $urlVoltar ?>'" value="Voltar">
                        </div>
                    </form>
                </div>

                <div id="divMensagem" class="col-full m02" style="display:none">
                    <div class="alert alert-info">
                        Aguarde o processamento...
                    </div>
                </div>
            </div>
        </div>  
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("jquery.price_format");
        ?>
    </body>

    <script type="text/javascript">

        // criando script de gerenciar mostra de tabela exclusiva
        function mostrarExclusivo(listaIdExc)
        {
            if (typeof listaIdExc == 'undefined' || listaIdExc.length == 0)
            {
                // nada a fazer
                return;
            }
            var iniItem = '<?php print ItemAvalProc::$CONS_ID_ITEM; ?>';
            var iniLinha = '<?php print ItemAvalProc::$CONS_ID_LINHA; ?>';
            var gatilho = function () {

                // descobrindo o ultimo atualmente marcado
                var i = 0;
                var marcado = null;
                for (; i < listaIdExc.length; i++)
                {
                    if ($("#" + iniItem + listaIdExc[i]).is(':checked')) {
                        marcado = i;
                    }
                }

                //mostrando ate marcado + 1
                marcado = (marcado === null) ? -1 : marcado;
                for (i = 0; i <= marcado + 1; i++)
                {
                    $("#" + iniLinha + listaIdExc[i]).show();
                    $("#" + iniItem + listaIdExc[i]).attr("disabled", false);
                }

                // escondendo outros
                for (i = marcado + 2; i < listaIdExc.length; i++)
                {
                    $("#" + iniLinha + listaIdExc[i]).hide();
                }

                // desabilitando checkbox superiores
                for (i = 0; i < marcado; i++)
                {
                    $("#" + iniItem + listaIdExc[i]).attr("disabled", true);
                }

            }

            // adicionando gatilhos
            for (var i = 0; i < listaIdExc.length; i++)
            {
                $("#" + iniItem + listaIdExc[i]).change(gatilho);
            }

            // chamando gatilho
            gatilho();
        }

        // funcao de ativar obrigatoriedade
        function ativaObrigatoriedade(v1, v2) {
            return !vazioOuNulo(v1) || !vazioOuNulo(v2);
        }
        // ativação de justificativa da eliminação
        function ativaJustEliminacao() {
            return $("#eliminarCand").is(':checked');
        }

        $(document).ready(function () {
            //validando form
            $("#form").validate({
            //ignore: "",
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
                    // adicionar contador
                    var gat = adicionaContadorTextArea('<?php print InscricaoProcesso::$MAX_CARACTERES_OBS_NOTA; ?>', 'justEliminacaoCand', 'contadorJust');
            gat();

            var gat2 = adicionaGatilhoAddDivCheckbox('eliminarCand', 'divEliminado');

            gat2();
        });
    </script>
</html>