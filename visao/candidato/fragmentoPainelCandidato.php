<?php
global $CFG;
require_once $CFG->rpasta . "/controle/CTCandidato.php";

if (estaLogado(Usuario::$USUARIO_CANDIDATO)) {
    ?>

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <?php include ($CFG->rpasta . "/include/fragmentoInscPendente.php"); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12 m02" style="margin-bottom:2em;">
            <h3 class="sublinhado">Minhas Inscrições</h3>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <?php
                require_once $CFG->rpasta . "/controle/CTProcesso.php";
                $matInscricoes = buscarInscricaoPorUsuarioCT(getIdUsuarioLogado(), NULL, NULL, NULL, NULL, NULL, NULL, 0, 2, InscricaoProcesso::$APRESENTACAO_ANDAMENTO);
                if (isset($matInscricoes[InscricaoProcesso::$APRESENTACAO_ANDAMENTO]) && count($matInscricoes[InscricaoProcesso::$APRESENTACAO_ANDAMENTO]) > 0) {
                    print _tabelaParcialInscUsu($matInscricoes[InscricaoProcesso::$APRESENTACAO_ANDAMENTO], InscricaoProcesso::$APRESENTACAO_ANDAMENTO);
                } else {
                    ?>
                    <div class="callout callout-info">
                        Você <b>NÃO</b> está inscrito em nenhum Edital em andamento.
                    </div>
                <?php }
                ?>
                <p><a href="<?php echo "$CFG->rwww/visao/inscricaoProcesso/listarInscProcessoUsuario.php"; ?>" style='color:#888;'>Ver todas as minhas inscrições »</a></p>
            </div>
        </div>
    </div>

    <div class="row painelCandLista" style="margin-bottom:2em;">
        <div class="col-md-6 col-sm-12 col-xs-12">
            <h3 class="sublinhado">Últimos editais visualizados</h3>
            <?php RAT_imprimeListaUltimosEditaisVistos(getIdUsuarioLogado()); ?>
        </div>

        <div class="col-md-6 col-sm-12 col-xs-12 m02-mob">
            <h3 class="sublinhado">Últimos editais publicados</h3>
            <?php imprimeListaEditaisPainelCandidato(); ?>
            <p class="m01 p15"><a href="<?php echo "$CFG->rwww/editais"; ?>" style='color:#888;'>Ver todos os editais »</a></p>
        </div>
    </div>
<?php }
?>
