<!DOCTYPE html>
<html>
    <head>     
        <title>Cursos - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == null) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        require_once ($CFG->rpasta . "/util/filtro/FiltroCurso.php");
        require_once ($CFG->rpasta . "/controle/CTCurso.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        //criando filtros
        $filtro = new FiltroCurso($_GET, 'listarCurso.php');

        //criando objetos de paginação
        $paginacao = new Paginacao('tabelaCursosPorFiltro', 'contaCursoPorFiltroCT', $filtro);
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
                    <h1>Você está em: Cadastros > <strong>Curso</strong></h1>
                </div>

                <div class="col-full m02">
                    <input class="btn btn-primary" type="button" onclick="javascript: window.location = 'criarCurso.php'" value="Novo Curso">
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
                                    <form id="formBusca" method="get" action="listarCurso.php">
                                        <div class="filtro">
                                            <div class="col-md-8">
                                                <h4>Nome do Curso</h4>
                                                <input class="form-control" type="text" id="nmCurso" name="nmCurso" size="20" maxlength="30" value="<?php print $filtro->getNmCurso() ?>" placeholder="Nome do Curso">
                                            </div>
                                            <div class="col-md-3">
                                                <h4>Situação</h4>
                                                <?php impressaoAtivoInativo("stCurso", $filtro->getStSituacao()); ?>
                                            </div>

                                            <div class="col-md-1">
                                                <h4>&nbsp;</h4>
                                                <button type="button" title="Mais filtros" id="maisFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "none" : print "block"; ?>"><span class="fa fa-plus"></span></button>
                                                <button type="button" title="Menos filtros" id="menosFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>;"><span class="fa fa-minus"></span></button>
                                            </div>

                                            <div id="filtroInterno" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>">
                                                <div class="completo m01">
                                                    <div class="col-md-6">
                                                        <h4>Departamento</h4>
                                                        <?php impressaoDepartamento($filtro->getIdDepartamento()); ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h4>Tipo de Curso</h4>
                                                        <?php impressaoTipoCurso($filtro->getTpCurso()); ?>
                                                    </div>
                                                </div>
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
                    <?php $paginacao->imprimir(); ?>
                </div>
            </div>
        </div>  
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaScript("jquery.cookie");
        carregaScript("filtro");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {

            filtro_BtMaisMenos();

            $("#limpar").click(function () {
                // destroi cookie
                $.removeCookie('<?php print $filtro->getNmCookie() ?>');

                limparFormulario($("#formBusca"));
            });

            $("#formBusca").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagemInline();
                    form.submit();
                }
            });

            function sucInsercao() {
                $().toastmessage('showToast', {
                    text: '<b>Curso cadastrado com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucExclusao() {
                $().toastmessage('showToast', {
                    text: '<b>Curso excluído com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucEdicao() {
                $().toastmessage('showToast', {
                    text: '<b>Curso atualizado com sucesso.</b>',
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