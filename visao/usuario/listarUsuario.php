<!DOCTYPE html>
<html>
    <head>     
        <title>Usuários - Seleção EAD</title>
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
        require_once ($CFG->rpasta . "/util/filtro/FiltroUsuario.php");
        require_once ($CFG->rpasta . "/controle/CTUsuario.php");
        require_once ($CFG->rpasta . "/util/selects.php");

        //criando filtros
        $filtro = new FiltroUsuario($_GET, 'listarUsuario.php');

        //criando objetos de paginação
        $paginacao = new Paginacao('tabelaUsuariosPorFiltro', 'contaUsuariosPorFiltroCT', $filtro);
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
                    <h1>Você está em: Cadastros > <strong>Usuário</strong></h1>
                </div>

                <div class="col-full m02">
                    <input class="btn btn-primary" type="button" onclick="javascript: window.location = 'criarUsuarioAdmin.php'" value="Novo Usuário">
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
                                    <form id="formBuscaUsuario" method="get" action="listarUsuario.php">
                                        <div class="filtro">
                                            <div class="col-md-5">
                                                <h4>Nome</h4>
                                                <input class="form-control" type="text" id="dsNome" name="dsNome" size="25" maxlength="100" value="<?php print $filtro->getDsNome(); ?>" placeholder="Nome">
                                            </div>
                                            <div class="col-md-3">
                                                <h4>CPF</h4>
                                                <input class="form-control tudo-normal" type="text" id="nrcpf" name="nrcpf" size="14" maxlength="14" value="<?php print $filtro->getNrcpf(); ?>" placeholder="CPF">
                                            </div>
                                            <div class="col-md-3">
                                                <h4>Tipo</h4>
                                                <?php impressaoTipoUsuario($filtro->getTpUsuario()); ?>
                                            </div>

                                            <div class="col-md-1">
                                                <h4>&nbsp;</h4>
                                                <button type="button" title="Mais filtros" id="maisFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "none" : print "block"; ?>"><span class="fa fa-plus"></span></button>
                                                <button type="button" title="Menos filtros" id="menosFiltros" class="mouse-ativo btn btn-primary" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>;"><span class="fa fa-minus"></span></button>
                                            </div>

                                            <div id="filtroInterno" style="display: <?php $filtro->getFiltroAberto() ? print "block" : print "none"; ?>">
                                                <div class="completo m01">
                                                    <div class="col-md-8">
                                                        <h4>Email</h4>
                                                        <input class="form-control tudo-minusculo" type="text" id="dsEmail" name="dsEmail" size="20" maxlength="100" value="<?php print $filtro->getDsEmail(); ?>" placeholder="Email">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h4>Situação</h4>
                                                        <?php impressaoAtivoInativo("stUsu", $filtro->getStSituacao()); ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="divBotoes" class="campo-botoes">
                                                <input id="submeter" class="btn btn-success" type="submit" value="Filtrar">
                                                <button id="limpar" class='btn btn-default' type="button" title="Limpar filtros">Limpar busca</button>
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
        carregaScript("additional-methods");
        carregaScript("metodos-adicionaisBR");
        carregaScript("jquery.maskedinput");
        carregaScript("jquery.cookie");
        carregaScript("filtro");
        ?>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {

            filtro_BtMaisMenos();

            $("#formBuscaUsuario").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagemInline();
                    form.submit();
                }, rules: {
                    nrcpf: {
                        CPF: true
                    }
                }, messages: {
                    nrcpf: {
                        CPF: "Informe um CPF válido."
                    }
                }
            });


            $("#limpar").click(function () {
                // destroi cookie
                $.removeCookie('<?php print $filtro->getNmCookie() ?>');

                limparFormulario($("#formBuscaUsuario"));
            });

            //adicionando mascara
            $("#nrcpf").mask("999.999.999-99");


            function sucInsercao() {
                $().toastmessage('showToast', {
                    text: '<b>Usuário cadastrado com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucExclusao() {
                $().toastmessage('showToast', {
                    text: '<b>Usuário excluído com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucEdicao() {
                $().toastmessage('showToast', {
                    text: '<b>Usuário atualizado com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucSenha() {
                $().toastmessage('showToast', {
                    text: '<b>Senha do Usuário reiniciada com sucesso.</b>',
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