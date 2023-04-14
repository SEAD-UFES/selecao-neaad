<!DOCTYPE html>
<html>
    <head>     
        <title>Alterar Vagas da Chamada - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTManutencaoProcesso.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        // coordenador ou administrador
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null && estaLogado(Usuario::$USUARIO_COORDENADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        //verificando passagem por get
        if (!isset($_GET['idProcesso']) || !isset($_GET['idChamada'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // buscando dados para processamento
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);
        $chamada = buscarChamadaPorIdCT($_GET['idChamada'], $processo->getPRC_ID_PROCESSO());

        // verificando se pode alterar a configuração
        if (!ProcessoChamada::permiteEditarConfiguracao($processo)) {
            new Mensagem("Não é possível alterar as vagas da chamada.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // tratando se é passo 2 da tela de alterar configuracao
        $passo2 = isset($_GET['parte']) && $_GET['parte'] == "true" && sessaoDados_getDados("idChamada") == $chamada->getPCH_ID_CHAMADA();

        // recuperando listas da sessão
        if ($passo2) {
            $idPolos = sessaoDados_getDados("idPolos");
            $idAreasAtu = sessaoDados_getDados("idAreasAtu");
            $idReservaVagas = sessaoDados_getDados("idReservaVagas");
            $nrMaxOpcaoPolo = sessaoDados_getDados("nrMaxOpcaoPolo");
        } else {
            $idPolos = implode(",", array_keys(buscarPoloPorChamadaCT($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo())));
            $idAreasAtu = implode(",", array_keys(buscarAreaAtuPorChamadaCT($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva())));
            $idReservaVagas = implode(",", array_keys(buscarIdsReservaVagaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaAtiva())));
        }

        // verificando se é criação da chamada
        $fluxoChamada = isset($_GET['cCham']) && $_GET['cCham'] == "true";

        // verificando se é volta de erro sem alteração
        $semAlteracao = isset($_GET[Mensagem::$TOAST_VAR_GET]) && $_GET[Mensagem::$TOAST_VAR_GET] == "errSemAlteracao";
        if ($semAlteracao) {
            // limpando
            unset($_GET[Mensagem::$TOAST_VAR_GET]);
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <a href="<?php print $CFG->rwww; ?>/visao/processo/manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Gerenciar</a> <?php if ($passo2) { ?> > <a href="<?php print $CFG->rwww; ?>/visao/chamada/alterarConfiguracaoChamada.php?volta=true&idChamada=<?php print $chamada->getPCH_ID_CHAMADA(); ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>">Configuração</a> <?php } ?> > <strong>Alterar Vagas da Chamada</strong></h1>
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

                <?php if ($passo2) { ?>
                    <div class='completo m02 callout callout-warning'>
                        Para concluir a operação de atualização da configuração, você deverá atualizar a tabela de vagas.
                    </div> 
                <?php } ?>

                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                <div class="col-full m02">

                    <?php if ($semAlteracao) { ?>
                        <div role="alert" class="alert alert-danger">
                            Você não modificou as vagas da chamada!
                        </div>
                    <?php } ?>

                    <form class="form-horizontal" id="form" method="post" action="<?php print $CFG->rwww; ?>/controle/CTManutencaoProcesso.php?acao=alterarVagasChamada">
                        <input type="hidden" name="valido" value="ctmanutencaoprocesso">
                        <input type="hidden" id='idProcesso' name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                        <input type="hidden" id='idChamada' name="idChamada" value="<?php print $chamada->getPCH_ID_CHAMADA(); ?>">
                        <input type="hidden" id='fluxoChamada' name="fluxoChamada" value="<?php print NGUtil::mapeamentoSimNao($fluxoChamada); ?>">

                        <?php if ($passo2) { ?>
                            <input type="hidden" id='passo2' name="passo2" value="<?php print FLAG_BD_SIM; ?>">
                            <input type="hidden" id='nrMaxOpcaoPolo' name="nrMaxOpcaoPolo" value="<?php print $nrMaxOpcaoPolo; ?>">
                        <?php } ?>

                        <input type="hidden" id='idPolos' name="idPolos" value="<?php print $idPolos; ?>">
                        <input type="hidden" id='idAreasAtu' name="idAreasAtu" value="<?php print $idAreasAtu; ?>">
                        <input type="hidden" id='idReservaVagas' name="idReservaVagas" value="<?php print $idReservaVagas; ?>">


                        <fieldset>
                            <legend>Vagas da Chamada</legend>
                            <div class="col-full">
                                <?php print tabelaVagasPorChamada($chamada, $processo, TRUE, ($passo2 ? $idPolos : FALSE), ($passo2 ? $idAreasAtu : FALSE), ($passo2 ? $idReservaVagas : FALSE)); ?>
                            </div>
                        </fieldset>

                        <div id="divBotoes" class="col-full">
                            <div class="form-group">
                                <input id="submeter" class="btn btn-success" type="submit" value="Salvar">
                                <?php if ($passo2) { ?>
                                    <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print $CFG->rwww; ?>/visao/chamada/alterarConfiguracaoChamada.php?volta=true&idChamada=<?php print $chamada->getPCH_ID_CHAMADA(); ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>';" value="Voltar">
                                <?php } else { ?>
                                    <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print "$CFG->rwww/visao/processo/"; ?>manterProcessoAdmin.php?<?php print Util::$ABA_PARAM; ?>=<?php print Util::$ABA_MPA_CHAMADA; ?>&idProcesso=<?php print $processo->getPRC_ID_PROCESSO(); ?>';" value="Voltar">
                                <?php } ?>
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
        carregaScript("jquery.maskedinput");
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            function corrigeTabela(idObjRemovido)
            {
                var totalLinha = $("#tabelaVagas > tbody > tr").size();
                var objTabela = $("#tabelaVagas");

                // percorrendo linhas para corrigir
                for (var i = parseInt(idObjRemovido) + 1; i <= totalLinha; i++)
                {
                    var novoId = i - 1;
                    var objLinha = $(objTabela).find("#" + i);
                    $(objLinha).attr("id", novoId);
                    $(objLinha).find("input[type='text'], select").each(function () {
                        $(this).attr("id", $(this).attr("id").replace(/seq[0-9]+/, "seq" + novoId));
                        $(this).attr("name", $(this).attr("name").replace(/seq[0-9]+/, "seq" + novoId));
                    });

                }
            }

            function removeLinha(objBotao) {
                var objRemover = $(objBotao).parent().parent();
                var idRemover = $(objRemover).attr("id");

                // Se tiver só uma linha ela não pode ser removida: Apenas limpeza
                if ($("#tabelaVagas > tbody > tr").size() === 1)
                {
                    $(objRemover).find("select").val("");
                    $(objRemover).find("input[type='text']").val(0);
                } else {
                    $(objRemover).remove();
                    corrigeTabela(idRemover);
                }
            }

            // criando função para procurar linhas semelhantes
            var linhasIguais = function (elem) {
                var linhaAlvo = $("#" + elem.target.id).parent().parent();
                var idPoloAlvo = (linhaAlvo).find("select[id*='Polo']").val();
                var idAreaAtuAlvo = (linhaAlvo).find("select[id*='AreaAtu']").val();

                // alguem e vazio: retornando
                if (idPoloAlvo === "" || idAreaAtuAlvo === "") {
                    return;
                }

                // pesquisando linhas iguais
                $("#tabelaVagas > tbody > tr").each(function () {
                    if ($(this).attr("id") !== $(linhaAlvo).attr("id"))
                    {
                        var poloSel = $(this).find("select[id*='Polo']").val();
                        if (poloSel === idPoloAlvo)
                        {
                            var areaSel = $(this).find("select[id*='AreaAtu']").val();
                            if (areaSel === idAreaAtuAlvo)
                            {
                                var linhaIgual = parseInt($(this).attr("id")) + 1;
                                alert("A linha que você acabou de preencher é igual a linha " + linhaIgual + ".\nSendo assim, a linha atual será reconfigurada e o cursor será movido para a linha equivalente.");

                                // limpa linha alvo
                                $(linhaAlvo).find("select").val("");

                                // movendo cursor para linha atual
                                $(this).find("input[type='text']").first().focus();
                            }
                        }
                    }
                });

            };
            // definindo gatilho
            $("#tabelaVagas").find("select").change(linhasIguais);


            // criando gatilho para adição de nova linha
            $("#adicionarVaga").click(function () {
                // copiando última linha
                var linhaClone = $("#tabelaVagas tr:last").clone();

                // alterando ids, names e zerando dados
                linhaClone.find("[name^='vaga']").val(0);
                linhaClone.attr("id", parseInt(linhaClone.attr("id")) + 1);
                var numLinha = linhaClone.attr("id");
                linhaClone.find("input[type='text'], select").each(function () {
                    $(this).attr("id", $(this).attr("id").replace(/seq[0-9]+/, "seq" + numLinha));
                    $(this).attr("name", $(this).attr("name").replace(/seq[0-9]+/, "seq" + numLinha));
                });
                linhaClone.find("select").val("");

                // colocando gatilho de linhas iguais
                linhaClone.find("select").change(linhasIguais);

                // adicionando
                linhaClone.insertAfter($("#tabelaVagas tr:last"));

            });


            function contaSelecoesVet(parteIdSelect, codMsgErro, vetOpcoesDisp)
            {
                var vetSels = [];
                var vetIds = [];
                var vetTexts = [];
                for (var i = 0; i < vetOpcoesDisp.length; i++)
                {
                    vetIds[i] = $(vetOpcoesDisp[i]).val();
                    vetTexts[i] = $(vetOpcoesDisp[i]).text();
                    vetSels[i] = 0;
                }

                // percorrendo selects
                $("#tabelaVagas").find("select[id*='" + parteIdSelect + "']").each(function () {
                    var indiceSel = vetIds.indexOf($(this).val());
                    if (indiceSel !== -1) {
                        vetSels[indiceSel] += 1;
                    }
                });

                // verificando
                var msg = "";
                for (var i = 0; i < vetSels.length; i++)
                {
                    if (vetSels[i] === 0)
                    {
                        msg += vetTexts[i] + "\n";
                    }
                }

                // melhorando msg
                if (msg !== "") {
                    msg = "Falta definir o quantitativo de vagas para " + codMsgErro + ":\n" + msg + "\n";
                }

                return msg;

            }



            // função que valida o uso de areas e polos
            function validaUsoAreasPolos() {
                // tem select?
                if ($("#tabelaVagas > tbody > tr").find("select").size() > 0) {
                    // polos
                    var vetObjPolos = [];
                    $("#tabelaVagas").find("#0 select[id*='Polo'] option").each(function () {
                        if ($(this).val() !== "") {
                            vetObjPolos[vetObjPolos.length] = this;
                        }
                    });

                    // áreas de atuaçao
                    var vetObjAreas = [];
                    $("#tabelaVagas").find("#0 select[id*='AreaAtu'] option").each(function () {
                        if ($(this).val() !== "") {
                            vetObjAreas[vetObjAreas.length] = this;
                        }
                    });

                    // conta a quantidade de polos e áreas e imprime msg de erro, se necessário
                    var msg = contaSelecoesVet("Polo", "o(s) Polo(s)", vetObjPolos);
                    msg += contaSelecoesVet("AreaAtu", "a(s) Área(s)", vetObjAreas);

                    if (msg !== "") {
                        alert("Não é possível salvar os dados pois aconteceu alguns problemas.\n\n" + msg);
                        return false;
                    }
                    return true;

                } else {
                    return true; // validado;
                }
            }

            $("#form").validate({
                ignore: "",
                submitHandler: function (form) {
                    // validando se usou todas as áreas e polos
                    if (!validaUsoAreasPolos()) {
                        return false;
                    }

                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                },
                rules: {
                }, messages: {
                }
            });


            // máscara e validação de todos inputs
            $("#tabelaVagas").find("input[type='text']").each(function () {
                $(this).mask("9?9999", {placeholder: ""});
                $(this).rules("add", {
                    digits: true,
                    messages: {
                    }
                });
            });
        });

    </script>
</html>

