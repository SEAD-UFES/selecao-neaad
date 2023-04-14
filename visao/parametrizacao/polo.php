<!DOCTYPE html>
<html>
    <head>     
        <title>Polos - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTParametrizacao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // preparando dados para a pagina
        require_once ($CFG->rpasta . "/util/filtro/FiltroPolo.php");

        //criando filtros
        $filtro = new FiltroPolo($_GET, 'polo.php');

        //criando objetos de paginação
        $paginacao = new Paginacao('tabelaPoloPorFiltro', 'contarPoloPorFiltroCT', $filtro);
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
                    <h1>Você está em: Parametrização > <strong>Polo</strong></h1>
                </div>

                <div class="col-full m02">
                    <div class="panel-group" id="accordionFiltro" role="tablist" aria-multiselectable="true">
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab">
                                <a data-toggle="collapse" data-parent="#accordionFiltro" href="#filtroPadrao" aria-expanded="true" aria-controls="filtroPadrao">
                                    <h4 class="panel-title">Filtro</h4>
                                </a>
                            </div>

                            <div id="filtroPadrao" class="panel-collapse collapse <?php $filtro->getAccordionAberto() ? print "in" : ""; ?>" role="tabpanel" aria-labelledby="filtroPadrao">
                                <div class="panel-body">                           
                                    <form id="formBusca" class="form-inline" method="get" action="polo.php">
                                        <div class="filtro">
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <h4>Código</h4>
                                                <input class="form-control" type="text" id="idPolo" name="idPolo" size="50" maxlength="10" value="<?php print $filtro->getIdPolo() ?>" placeholder="Código do Polo">
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <h4>Nome do polo</h4>                         
                                                <input class="form-control" type="text" id="dsPolo" name="dsPolo" size="50" maxlength="100" value="<?php print $filtro->getDsPolo() ?>" placeholder="Nome do polo">
                                            </div>

                                            <div id="divBotoes" class="campo-botoes">
                                                <button id="submeter" class="btn btn-success" type="submit">Filtrar</button>
                                                <button id="limpar" class="btn btn-default" type="button" title="Limpar filtros">Limpar busca</button>
                                            </div>
                                            <div id="divMensagem" class="col-full campo-carregando" style="display:none">
                                                <div class="alert alert-info">
                                                    Aguarde o processamento...
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-full m02">
                    <?php echo $paginacao->imprimir(); ?>
                </div>

                <div class="col-full m01">
                    <button id="btVoltar" class="btn btn-default" type="button" onclick="javascript: window.location = '<?php echo "$CFG->rwww/inicio"; ?>';">Voltar</button>
                </div>
            </div>
        </div> 
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("jquery.cookie");
        carregaScript("jquery.maskedinput");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#limpar").click(function () {
                // destroi cookie
                $.removeCookie('<?php print $filtro->getNmCookie() ?>');

                limparFormulario($("#formBusca"));
            });

            //adicionando máscaras
            $("#idPolo").mask("9?9999", {placeholder: ""});

            $("#formBusca").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagemInline();
                    form.submit();
                }, rules: {
                    idPolo: {
                        digits: true
                    }
                }
            });

        });

    </script>	
</html>