<!DOCTYPE html>
<html>
    <head>     
        <title>Departamentos - Seleção EAD</title>
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
        require_once ($CFG->rpasta . "/util/filtro/FiltroDepartamento.php");
        require_once ($CFG->rpasta . "/util/selects.php");
        require_once ($CFG->rpasta . "/controle/CTDepartamento.php");

        //criando filtros
        $filtro = new FiltroDepartamento($_GET, 'listarDepartamento.php');

        //criando objetos de paginação
        $paginacao = new Paginacao('tabelaDepartamentosPorFiltro', 'contaDepartamentosPorFiltroCT', $filtro);
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
                    <h1>Você está em: Cadastros > <strong>Departamento</strong></h1>
                </div>

                <div class="col-full m02">
                    <input class="btn btn-primary" type="button" onclick="javascript: window.location = 'criarDepartamento.php'" value="Novo Departamento" title="Criar novo departamento">
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
                                    <form id="formBusca" method="get" action="listarDepartamento.php">
                                        <div class="filtro">
                                            <div class="col-md-7">
                                                <h4>Departamento</h4>
                                                <input class="form-control" type="text" id="dsNome" name="dsNome" size="50" maxlength="100" value="<?php print $filtro->getDsNome() ?>" placeholder="Nome do departamento">
                                            </div>
                                            <div class="col-md-5">
                                                <h4>Situação</h4>
                                                <?php impressaoAtivoInativo("stDep", $filtro->getStSituacao()); ?>
                                            </div>

                                            <div id="divBotoes" class="completo campo-botoes m02">
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
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
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
                    text: '<b>Departamento cadastrado com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucExclusao() {
                $().toastmessage('showToast', {
                    text: '<b>Departamento excluído com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucEdicao() {
                $().toastmessage('showToast', {
                    text: '<b>Departamento atualizado com sucesso.</b>',
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