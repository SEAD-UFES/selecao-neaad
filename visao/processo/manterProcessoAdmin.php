<!DOCTYPE html>
<html>
    <head>     
        <title>Gerenciar Edital - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTNoticia.php");

        // coordenador ou administrador
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

        // buscando processo
        $processo = buscarProcessoComPermissaoCT($_GET['idProcesso']);

        // tratando data limite 
        $dtTemp = buscaDtUSPriChamadaDoProcessoCT($processo->getPRC_ID_PROCESSO());
        $dtLimite = $dtTemp != NULL ? explode("-", $dtTemp) : NULL;
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
                    <h1>Você está em: <a href="<?php print $CFG->rwww; ?>/visao/processo/listarProcessoAdmin.php">Editais</a> > <strong>Gerenciar</strong></h1>
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

                <div class="col-full m02">
                    <div class="tabbable">
                        <ul id="tabProcesso" class="nav nav-tabs">
                            <li class="active"><a href="#tab1" data-toggle="tab">Dados Básicos</a></li>
                            <li><a href="#tab2" data-toggle="tab">Inf. Complementares <i title="Configure aqui questões adicionais para obtenção de alguma informação específica dos candidatos para este Edital." class="fa fa-question-circle"></i></a></li>
                            <li><a href="#tab3" data-toggle="tab">Avaliação <i title="A avaliação das inscrições é separada em etapas. Cada etapa tem suas categorias e itens de avaliação, incluindo critérios de eliminação, classificação e de seleção." class="fa fa-question-circle"></i></a></li>
                            <li><a href="#tab4" data-toggle="tab">Chamada</a></li>
                            <li><a href="#tab5" data-toggle="tab">Notícia</a></li>
                            <li><a href="#tab6" data-toggle="tab">Inf. Anexas <i title="Documentos e informações anexas ao edital." class="fa fa-question-circle"></i></a></li>
                        </ul>

                        <div class="tab-content col-full">
                            <div class="tab-pane active m02" id="tab1">

                                <?php print Util::$MSG_CAMPO_OBRIG; ?>

                                <div class="completo m02">
                                    <form id='formDadosAdd' class="form-horizontal" enctype="multipart/form-data" method="post">
                                        <input type="hidden" name="idProcesso" value="<?php print $processo->getPRC_ID_PROCESSO(); ?>">
                                        <input type="hidden" name="MAX_FILE_SIZE" value="<?php print Processo::$TAM_MAX_ARQ_BYTES; ?>">
                                        <input type="hidden" id="dtInicioOrig" value="<?php print $processo->getPRC_DT_INICIO(); ?>">
                                        <input type="hidden" id="dsEditalOrig" value="<?php print $processo->getPRC_DS_PROCESSO(); ?>">

                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Numeração:</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input class="form-control" disabled name="numeracao" type="text" id="numeracao" value="<?php print $processo->getDsEditalCompleta(); ?>" required>
                                                <label style="display: none" for="numeracao" class="error"></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label title="Data a partir da qual o Edital poderá ser visualizado pelos candidatos."  class="control-label col-xs-12 col-sm-4 col-md-4">Data de Início*:</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input class="form-control" disabled name="dtInicio" type="text" id="dtInicio" size="10" maxlength="10" placeholder="Data de Início" value="<?php print $processo->getPRC_DT_INICIO(); ?>" required>
                                                <label style="display: none" for="dtInicio" class="error"></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label title="Data de finalização do Edital."  class="control-label col-xs-12 col-sm-4 col-md-4">Data de Finalização:</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input class="form-control" disabled name="dtFim" type="text" id="dtFim" size="10" maxlength="10" value="<?php print $processo->getPRC_DT_FIM(); ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4 col-md-4">PDF do Edital:</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input id='editalEdicao' style="display: none" name="arqEdital" type="file" id="arqEdital" placeholder="PDF do Edital">
                                                <span style="display: block">
                                                    <a id="editalConsulta" target="_blank" class="btn btn-default" href="<?php print $processo->getUrlArquivoEdital(); ?>" id='linkArqEdital'><span class="fa fa-book"></span> Visualizar</a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-12 col-sm-4 col-md-4">Descrição do Edital: *</label>
                                            <div class="col-xs-12 col-sm-8 col-md-8" style="display:block">
                                                <textarea class="form-control" disabled cols="60" rows="6" name="dsEdital" id="dsEdital"><?php print $processo->getPRC_DS_PROCESSO(); ?></textarea>
                                                <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                                            </div>
                                        </div>
                                        <div id='spanEditar'>
                                            <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>  
                                            <div class="col-xs-12 col-sm-8 col-md-8">
                                                <input id='btEditar' type="button" class="btn btn-primary" value="Editar" title="<?php !$processo->permiteEdicao(TRUE) ? print "Os dados adicionais deste edital não podem ser editados" : print "Editar dados do processo" ?>" <?php !$processo->permiteEdicao(TRUE) ? print "disabled" : print "" ?>>
                                            </div>
                                        </div>
                                        <div id="spanSalvar" style="display: none">
                                            <div id="divBotoes" class="m02">
                                                <label class="control-label col-xs-12 col-sm-4 col-md-4">&nbsp;</label>  
                                                <div class="col-xs-12 col-sm-8 col-md-8">
                                                    <input id='btSalvar' type="submit" class="btn btn-success" value="Salvar">
                                                    <input id='btCancelar' type="button" class="btn btn-default" value="Cancelar">
                                                </div>
                                            </div>
                                            <div id="divMensagem" class="col-full" style="display:none">
                                                <div class="alert alert-info">
                                                    Aguarde o processamento...
                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>

                            <div class="tab-pane m02" id="tab2">
                                <?php include($CFG->rpasta . "/visao/processo/fragmentoTabInfComp.php"); ?> 
                            </div>
                            <div class="tab-pane m02" id="tab3">
                                <?php include($CFG->rpasta . "/visao/processo/fragmentoTabAvaliacao.php"); ?> 
                            </div>
                            <div class="tab-pane m02" id="tab4">
                                <?php include($CFG->rpasta . "/visao/processo/fragmentoTabChamada.php"); ?> 
                            </div>
                            <div class="tab-pane m02" id="tab5">
                                <?php include($CFG->rpasta . "/visao/processo/fragmentoTabNoticia.php"); ?> 
                            </div>
                            <div class="tab-pane m02" id="tab6">
                                <?php include($CFG->rpasta . "/visao/processo/fragmentoTabDocumentos.php"); ?> 
                            </div>
                        </div>
                        <script type="text/javascript">
                            $(document).ready(function() {
                            var abaAtiva = obterParametroGet("<?php print Util::$ABA_PARAM; ?>");
                                    if (abaAtiva != "")
                            {
                            $('#tabProcesso li:eq(' + abaAtiva + ') a').tab('show');
                            }
                            });</script>
                    </div>  
                    <div class="completo m02">
                        <input type="button" class="btn btn-default" onclick="javascript: window.location = 'listarProcessoAdmin.php';" value="Voltar">
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
                // funcao de alteraçao de ordem de categoria
                        function alterarOrdem(tipo, escopo) {
                        // trocando botoes     
                        $("#spanVisualizacao" + tipo + escopo).hide(); $("#spanEdicao" + tipo + escopo).show();
                                // trocando campos por input
                                _habilitaEdicao(tipo, escopo);
                        }

                function cancelarAlteracaoOrdem(tipo, escopo) {
                // trocando botoes
                $("#spanEdicao" + tipo + escopo).hide();
                        $("#erroOrdem" + tipo + escopo).hide();
                        $("#spanVisualizacao" + tipo + escopo).show();
                        // trocando input por campo
                        _desabilitaEdicao(tipo, escopo);
                }

                function salvarOrdem(tipo, escopo) {
                // escondendo botao para processamento
                $("#botao" + tipo + escopo).hide();
                        $("#erroOrdem" + tipo + escopo).hide(); $("#mensagem" + tipo + escopo).show();
                        // alterando no BD        
                        _atualizaOrdemBD(tipo, escopo);
                }
                function _habilitaEdicao(tipo, escopo) {
                $("[id^='" + escopo + "ordem" + tipo + "']").each(function() {
                var temp = $(this).html();
                        var idInput = "input" + $(this).attr("id");
                        $(this).html("<input style='width: 80px;' id='" + idInput + "' class='form-control' type='text' value=" + temp + ">");
                        // incluindo mascara
                        $("#" + idInput).mask("9?9", {placeholder: " "});
                });
                }

                function _desabilitaEdicao(tipo, escopo) {
                $("[id^='" + escopo + "ordem" + tipo + "']").each(function(ind) {
                $(this).html(ind + 1);
                });
                }

                // funcao que valida se a ordem pode ser salva no banco
                function _validaOrdem(tipo, escopo)
                {
                // recupera itens
                var arrayOrdem = [];
                        var stringRet = "";
                        $("[id^='" + escopo + "ordem" + tipo + "']").each(function() {
                var idInput = "input" + $(this).attr("id");
                        arrayOrdem[arrayOrdem.length] = $("#" + idInput).val();
                        stringRet += $(this).attr("id").replace(/^[a-z0-9]*|[^0-9]*/g, '') + ":" + $("#" + idInput).val() + ";";
                });
                        // executa validacao               
                        arrayOrdem.sort();
                        for (var i = 1; i <= arrayOrdem.length; i++)
                {
                if (i != arrayOrdem[i - 1]) {
                return false;
                }
                }

                // retorna string
                return stringRet;
                }

                // executa operacao de atualizacao de ordem no banco
                function _atualizaOrdemBD(tipo, escopo) {
                // tentando validar
                var strEnvio = _validaOrdem(tipo, escopo);
                        if (strEnvio === false)
                {                                 $("#mensagem" + tipo + escopo).hide();
                        $("#erroOrdem" + tipo + escopo).show(); $("#botao" + tipo + escopo).show();
                        return;
                }

                // enviando dados 
                $.ajax({
                type: "POST",
                        url: getURLServidor() + "/controle/CTAjax.php?atualizacao=atualizarOrdemElemProc",
                        data: {"tipo": tipo, "idProcesso": '<?php print $processo->getPRC_ID_PROCESSO(); ?>', "idEtapaAval": escopo, "novaOrdenacao": strEnvio},
                        dataType: "json",
                        success: function(json) {

                        //restabelecendo pagina                                        
                        $("#mensagem" + tipo + escopo).hide();
                                $("#botao" + tipo + escopo).show();
                                cancelarAlteracaoOrdem(tipo, escopo);
                                if (!json['situacao']) {

                        // deu erro na atualizacao: criando toast de erro
                        $().toastmessage('showToast', {
                        text: '<b>Erro ao atualizar ordenação:</b> ' + json['msg'],
                                sticky: true,
                                type: 'error',
                                position: 'top-right'
                        });
                        } else {

                        // atualizando tabela                                        
                        $("#spanTabela" + tipo + escopo).html(json['htmlTabela']);
                                //
                                //
                                // criando toast de sucesso
                                $().toastmessage('showToast', {
                        text: '<b>Ordenação atualizada com sucesso.</b>',
                                sticky: false,
                                type: 'success',
                                position: 'top-right'
                        });
                        }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                        var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nTente novamente.\n\n";
                                msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;
                                // exibindo mensagem
                                alert(msg);
                                //restabelecendo
                                $("#mensagem" + tipo + escopo).hide();
                                $("#botao" + tipo + escopo).show();
                                cancelarAlteracaoOrdem(tipo, escopo);
                        }
                });
                }

                $(document).ready(function() {
                var ativaEdicao = function(){
                $("#dtInicio").attr("disabled", false);
                        $("#dsEdital").attr("disabled", false);
                        $("#editalConsulta").hide();
                        $("#editalEdicao").show();
                        //
                        //
                        $("#spanEditar").hide();
                        $("#spanSalvar").show();
                }

                var desativaEdicao = function(atualizou){
                if (atualizou){
                $("#dtInicioOrig").val($("#dtInicio").val());
                        $("#dsEditalOrig").val($("#dsEdital").val());
                } else{
                $("#dtInicio").val($("#dtInicioOrig").val());
                        $("#dsEdital").val($("#dsEditalOrig").val());
                }
                $("label.error").hide();
                        $("#dtInicio").attr("disabled", true);
                        $("#dsEdital").attr("disabled", true);
                        $("#editalEdicao").hide();
                        $("#editalConsulta").show();
                        //
                        //
                        $("#spanSalvar").hide();
                        $("#spanEditar").show();
                        mostrarBotoes();
                }

                // trabalhando opcao de edicao
                $("#btEditar").click(ativaEdicao);
                        // trabalhando cancelar
                        $("#btCancelar").click(desativaEdicao);
                        //
                        //
                        //incluindo contador para caracteres restantes
                        adicionaContadorTextArea(<?php print Processo::$MAX_CARACTER_DS_EDITAL; ?>, "dsEdital", "qtCaracteres");
                        //
                        //
                        // adicionando mascaras
                        $("#nrEdital").mask("9?99", {placeholder:" "});
                        $("#anoEdital").mask("9999", {placeholder:" "});
                        $("#dtInicio").mask("99/99/9999");
                        //
                        //
                        //
                        $("#formDadosAdd").validate({
                submitHandler: function(form) {
                //evitar repetiçao do botao
                mostrarMensagem();
                        // trabalhando submit com ajax
                        $.ajax({
                        type: "POST",
                                url: getURLServidor() + "/controle/CTAjax.php?atualizacao=dadosAddProcesso",
                                data: new FormData(form),
                                processData: false,
                                contentType: false,
                                success: function(json) {
                                // preparando pagina
                                desativaEdicao(true);
                                        // caso editou com sucesso
                                        if (json['situacao']){
                                // informando sucesso
                                sucDadosAdd();
                                        return true;
                                } else{
                                // deu erro na edicao: criando toast de erro
                                $().toastmessage('showToast', {
                                text: '<b>Erro ao atualizar dados adicionais:</b> ' + json['msg'],
                                        sticky: true,
                                        type: 'error',
                                        position: 'top-right'
                                });
                                        return false;
                                }
                                }, error: function(xhr, ajaxOptions, thrownError) {
                        var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nTente novamente.\n\n";
                                msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;
                                // exibindo mensagem
                                alert(msg);
                                //
                                //
                                desativaEdicao();
                                return false;
                        }

                        });
                },
                        rules: {
                        dtInicio: {
                        required: true,
                                dataBR: true
<?php
if ($dtLimite != NULL) {
    print ",
    dataBRMenorIgual: new Date($dtLimite[0], $dtLimite[1], $dtLimite[2])";
}
?>
                        }, arqEdital: {
                        extension: "pdf",
                                accept: "application/pdf",
                                tamMaxArq: 2 // tamanho em MB
                        }, dsEdital: {
                        required: true,
                                maxlength: <?php print Processo::$MAX_CARACTER_DS_EDITAL; ?>
                        }}, messages: {
                dtInicio: {
                dataBR: "Por favor, Informe uma data válida"
<?php
if ($dtLimite != NULL) {
    print ",
    dataBRMenorIgual: 'A data de início deve ser menor ou igual a data de início das inscrições da primeira chamada do processo.'";
}
?>
                }, arqEdital: {
                extension: "Só é possível enviar arquivo PDF.",
                        accept: "Só é possível enviar arquivo PDF.",
                        tamMaxArq: "O arquivo deve ter no máximo 2MB."
                }, dsEdital: {
                maxlength: "Tamanho do campo excede o limite de <?php print Processo::$MAX_CARACTER_DS_EDITAL; ?> caracteres."
                }
                }
                }
                );
                        function sucInsercao() {
                        $().toastmessage('showToast', {
                        text: '<b>Edital cadastrado com sucesso.</b> Agora você precisar complementar o cadastro...',
                                sticky: false,
                                type: 'success',
                                position: 'top-right'
                        });
                        }

                function sucInsercaoCat() {
                $().toastmessage('showToast', {
                text: '<b>Categoria de Avaliação cadastrada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCatAvaliacao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucIncCritEliminacao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Eliminação cadastrado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritEliminacao" + obterParametroGet('idEtapaAval')).attr('href');
                }
                function sucIncCritSelecao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Seleção cadastrado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritSelecao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucIncCritDesempate() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Desempate cadastrado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritDesempate" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucIncCritClassificacao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Classificação cadastrado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritClassificacao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucIncCritCadReserva() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Seleção - Cadastro de Reserva cadastrado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritCadReserva" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucInsercaoInfComp() {
                $().toastmessage('showToast', {
                text: '<b>Inf. Complementar cadastrada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucInsercaoNoticia() {
                $().toastmessage('showToast', {
                text: '<b>Notícia cadastrada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucRespostasInfComp() {
                $().toastmessage('showToast', {
                text: '<b>As Respostas da Inf. Complementar foram atualizadas com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }
                function sucAtualizacaoCat() {
                $().toastmessage('showToast', {
                text: '<b>Categoria de Avaliação atualizada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCatAvaliacao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucAtuCritEliminacao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Eliminação atualizado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritEliminacao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucAtuCritClassificacao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Classificação atualizado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritClassificacao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucAtuCritDesempate() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Desempate atualizado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritDesempate" + obterParametroGet('idEtapaAval')).attr('href');
                }
                function sucAtuCritSelecao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Seleção atualizado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritSelecao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucAtuCritCadReserva() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Seleção - Cadastro de Reserva atualizado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritCadReserva" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucAtuInfComp() {
                $().toastmessage('showToast', {
                text: '<b>Inf. Complementar atualizada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucAtuNoticia() {
                $().toastmessage('showToast', {
                text: '<b>Notícia atualizada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }


                function sucExclusaoCat() {
                $().toastmessage('showToast', {
                text: '<b>Categoria de Avaliação excluída com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCatAvaliacao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucExcCritEliminacao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Eliminação excluído com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritEliminacao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucExcCritDesempate() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Desempate excluído com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritDesempate" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucExcCritClassificacao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Classificação excluído com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritClassificacao" + obterParametroGet('idEtapaAval')).attr('href');
                }
                function sucExcCritSelecao() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Seleção excluído com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritSelecao" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucExcCritCadReserva() {
                $().toastmessage('showToast', {
                text: '<b>Critério de Seleção - Cadastro de Reserva excluído com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkCritCadReserva" + obterParametroGet('idEtapaAval')).attr('href');
                }

                function sucExcEtapa() {
                $().toastmessage('showToast', {
                text: '<b>Etapa excluída com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucExcInfComp() {
                $().toastmessage('showToast', {
                text: '<b>Inf. Complementar excluída com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucExcNoticia() {
                $().toastmessage('showToast', {
                text: '<b>Notícia excluída com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }


                function sucDadosAdd() {
                $().toastmessage('showToast', {
                text: '<b>Dados adicionais atualizados com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }


                function sucCalChamada() {
                $().toastmessage('showToast', {
                text: '<b>Calendário atualizado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucConfChamada() {
                $().toastmessage('showToast', {
                text: '<b>Configuração atualizada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucVagasChamada() {
                $().toastmessage('showToast', {
                text: '<b>Quantidade de Vagas atualizada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucMsgChamada() {
                $().toastmessage('showToast', {
                text: '<b>Mensagens da chamada atualizadas com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                }

                function sucFormulaFinal() {
                $().toastmessage('showToast', {
                text: '<b>Fórmula da Nota Final atualizada com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                });
                        // link
                        window.location.href = $("#linkResultadoFinal").attr('href');
                }

<?php
if (isset($_GET[Mensagem::$TOAST_VAR_GET])) {
    print $_GET[Mensagem::$TOAST_VAR_GET] . "();";
}
?>
                });
    </script>
</html>

