<!DOCTYPE html>
<html>
    <head>     
        <title>Formação - Seleção EAD</title>
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
        require_once ($CFG->rpasta . "/util/filtro/FiltroFormacao.php");

        //criando filtros
        $filtro = new FiltroFormacao($_GET, 'listarFormacao.php', getIdUsuarioLogado());

        //criando objetos de paginação
        $paginacao = new Paginacao('tabelaFormacaoCandPorFiltroCT', 'contarFormacaoCandPorFiltroCT', $filtro);
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
                    <ul class="nav nav-tabs m02 curriculo ">
                        <li class="active">
                            <a href="#">Formação</a>
                        </li>
                        <li><a href=<?php print $CFG->rwww . "/visao/publicacao/listarPublicacao.php" ?>>Publicação</a></li>
                        <li><a href=<?php print $CFG->rwww . "/visao/participacaoEvento/listarPartEvento.php" ?>>Participação em Evento</a></li>
                        <li><a href=<?php print $CFG->rwww . "/visao/atuacao/listarAtuacao.php" ?>>Atuação</a></li>
                    </ul>

                    <div class="col-full">
                        <div class="callout callout-info">
                            <strong>Atenção:</strong> Caso possua graduação, não esqueça de incluí-la (mesmo se você possuir titulação superior).
                        </div>
                    </div>

                    <?php
                    $podeAltCurriculo = permiteAlteracaoCurriculoCT(buscarIdCandPorIdUsuCT(getIdUsuarioLogado()));
                    $avisoBtCriar = $podeAltCurriculo ? "title='Cadastrar nova formação'" : "title='" . getMsgErroEdicaoCurriculo() . "' disabled";
                    $onclickBt = $podeAltCurriculo ? "javascript: window.location = 'criarFormacao.php';" : "javascript: return false;";
                    ?>

                    <div class="col-full">
                        <input class="btn btn-primary" type="button" onclick="<?php echo $onclickBt; ?>" value="Nova Formação" <?php echo $avisoBtCriar; ?>>
                    </div>

                    <div class="col-full m01"><?php $paginacao->imprimir(); ?></div>

                    <div class="col-full">
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
                    text: '<b>Formação cadastrada com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucExclusao() {
                $().toastmessage('showToast', {
                    text: '<b>Formação excluída com sucesso.</b>',
                    sticky: false,
                    type: 'success',
                    position: 'top-right'
                });
            }

            function sucAtualizacao() {
                $().toastmessage('showToast', {
                    text: '<b>Formação atualizada com sucesso.</b>',
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
