<!DOCTYPE html>
<html>
    <head>     
        <title>Publicações - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado
        require_once ($CFG->rpasta . "/util/sessao.php");
        require_once ($CFG->rpasta . "/controle/CTCurriculo.php");

        if (estaLogado(Usuario::$USUARIO_CANDIDATO) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // preparando dados para a pagina
        require_once ($CFG->rpasta . "/util/filtro/FiltroPublicacao.php");

        //criando filtros
        $filtro = new FiltroPublicacao($_GET, 'listarPublicacao.php', getIdUsuarioLogado());

        //criando objetos de paginação
        $paginacao = new Paginacao('tabelaPublicacaoCandPorFiltroCT', 'contarPublicacaoCandPorFiltroCT', $filtro);
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

                <?php include ($CFG->rpasta . "/include/tutorial.php"); ?>

                <div id="breadcrumb">
                    <h1>Você está em: Candidato > <strong>Currículo</strong></h1>
                </div>

                <div class="col-full">
                    <?php include ($CFG->rpasta . "/include/fragmentoLattes.php"); ?>
                </div>

                <div class="col-full">
                    <ul class="nav nav-tabs m02 curriculo">
                        <li><a href= <?php print $CFG->rwww . "/visao/formacao/listarFormacao.php" ?> >Formação</a></li>
                        <li class="active"><a href="#">Publicação</a></li>
                        <li><a href=<?php print $CFG->rwww . "/visao/participacaoEvento/listarPartEvento.php" ?>>Participação em Evento</a></li>
                        <li><a href=<?php print $CFG->rwww . "/visao/atuacao/listarAtuacao.php" ?>>Atuação</a></li>
                    </ul>

                    <?php
                    $podeAltCurriculo = permiteAlteracaoCurriculoCT(buscarIdCandPorIdUsuCT(getIdUsuarioLogado()));
                    $avisoBtCriar = $podeAltCurriculo ? "title='Cadastrar nova publicação'" : "title='" . getMsgErroEdicaoCurriculo() . "' disabled";
                    $onclickBt = $podeAltCurriculo ? "javascript: window.location = 'criarPublicacao.php';" : "javascript: return false;";
                    ?>

                    <div class="col-full m01">
                        <input class="btn btn-primary m01" type="button" onclick="<?php echo $onclickBt; ?>" value="Nova Publicação" <?php echo $avisoBtCriar; ?>>
                    </div>

                    <div class="col-full m01"><?php $paginacao->imprimir(); ?></div>

                    <div class="col-full m02">
                        <button id="btVoltar" class="btn btn-default" type="button" onclick="javascript: window.location = '<?php echo "$CFG->rwww/inicio"; ?>';">Voltar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            function sucInsercao() {
                $().toastmessage('showToast', {
                    text: '<b>Publicação cadastrada com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucExclusao() {
                $().toastmessage('showToast', {
                    text: '<b>Publicação excluída com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucAtualizacao() {
                $().toastmessage('showToast', {
                    text: '<b>Publicação atualizada com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }
<?php
if (isset($_GET[Mensagem::$TOAST_VAR_GET]) && !Util::vazioNulo($_GET[Mensagem::$TOAST_VAR_GET])) {
    print $_GET[Mensagem::$TOAST_VAR_GET] . "();";
}
?>
        });
    </script>
</html>