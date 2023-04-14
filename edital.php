<!DOCTYPE html>
<html>
    <head>
        <title>Editais - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="description" content="Editais de seleção para cursos EAD - SEAD/UFES.">

        <?php
        require_once 'config.php';
        global $CFG;
        ?>

        <?php
        // recuperando parâmetro de erro no get
        $avisoErrEdital = isset($_GET['err']) && !Util::vazioNulo($_GET['err']);

        require_once ($CFG->rpasta . "/util/filtro/FiltroProcesso.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        //criando filtro
        $filtro = new FiltroProcesso($_GET, false, "$CFG->rwww/edital.php", NULL, NULL, NULL, TRUE, TRUE);

        //criando objeto de paginação
        $paginacao = new Paginacao('tabelaProcessosApresentacao', 'contaProcessosApresentacaoCT', $filtro);
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
                    <h1>Você está em: <strong>Editais</strong></h1>
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
                                    <form id="formBuscaProcesso" method="get" action="<?php print "$CFG->rwww/edital.php"; ?>">
                                        <div class="filtro">
                                            <div class="col-md-6">
                                                <h4>Curso</h4>
                                                <?php impressaoCurso($filtro->getIdCursoTela()); ?>
                                            </div>

                                            <div class="col-md-5">
                                                <h4>Formação</h4>
                                                <?php impressaoTipoFormacao($filtro->getTpFormacaoTela()); ?>
                                            </div>

                                            <div class="col-md-1">
                                                <h4>&nbsp;</h4>
                                                <button type="button" title="Mais filtros" id="maisFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "none" : print "block"; ?>"><span class="fa fa-plus"></span></button>
                                                <button type="button" title="Menos filtros" id="menosFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>;"><span class="fa fa-minus"></span></button>
                                            </div>

                                            <div id="filtroInterno" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>">
                                                <div class="completo m01">
                                                    <div class="col-md-6">
                                                        <h4>Atribuição</h4>
                                                        <?php impressaoTipoCargo($filtro->getIdTipoCargo()); ?>
                                                    </div>   
                                                    <div class="col-md-3">
                                                        <h4>Número</h4>
                                                        <input class="form-control" type="text" id="nrEdital" name="nrEdital" size="5" maxlength="3" value="<?php print $filtro->getNrEdital(); ?>" placeholder="Número do edital">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <h4>Ano</h4>                                   
                                                        <?php impressaoSelectAno("anoEdital", $filtro->getAnoEdital(), 2013); ?>
                                                    </div>                                        
                                                </div>
                                            </div>

                                            <div id="divBotoes" class="campo-botoes">
                                                <button id="submeter" class="btn btn-success" type="submit">Filtrar</button>
                                                <button id="limpar" class="btn btn-default" type="button" title="Limpar busca">Limpar busca</button>
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
                <div class="col-full">
                    <?php
                    //imprimindo dados
                    $paginacao->imprimir();
                    ?>
                    <p class="m02"><button id="btVoltar" class="btn btn-default" type="button" onclick="javascript: window.location = '<?php echo "$CFG->rwww/inicio"; ?>';">Voltar</button></p>
                </div>
            </div>
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript('jquery.maskedinput');
        carregaScript("jquery.cookie");
        carregaScript("filtro");
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            filtroProcesso("<?php print $filtro->getNmCookie() ?>");


<?php if ($avisoErrEdital || $filtro->getAvisoErrEdital()) { ?>
                $().toastmessage('showToast', {
                    text: '<b>Edital não encontrado.</b> Veja se não é um dos listados a seguir...',
                    sticky: true,
                    type: 'error',
                    position: 'top-right'
                });
<?php } ?>
        });
    </script>
</html>