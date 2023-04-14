<?php
global $CFG;

if (estaLogado(Usuario::$USUARIO_COORDENADOR)) {
    // buscando curso que coordena
    $curso = buscarCursoPorCoordenadorCT(getIdUsuarioLogado());

    if ($curso == NULL) {
        ?>
        <div class="callout callout-info">
            No momento, você <b>NÃO</b> coordena um curso.
        </div>
    <?php }
    ?>
    <div class="row m01">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3 class="sublinhado">Coordenação de <?php echo $curso->getCUR_NM_CURSO() ?> - <?php echo $curso->TPC_NM_TIPO_CURSO; ?></h3>
            <h4 style="color:#104778;font-weight:normal;margin-top:-0.5em;">Departamento de <?php echo $curso->DEP_DS_DEPARTAMENTO; ?> | <?php echo $curso->getDsAreaSubarea(); ?></h4>

            <div class="m02">
                <div class="panel-group" id="accordionFiltro" role="tablist" aria-multiselectable="true">
                    <div class="panel panel-default p15" style="border:none;box-shadow:none;">
                        <div class="panel-heading" role="tab" style="border-width:1px;border-style:solid;text-align:center;">
                            <a data-toggle="collapse" data-parent="#accordionAvaliadores" href="#listaAvaliadores" aria-expanded="true" aria-controls="listaAvaliadores">
                                <h6 class="panel-title">Lista de avaliadores</h6>
                            </a>
                        </div>

                        <div id="listaAvaliadores" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="listaAvaliadores">
                            <?php echo tabelaAvaliadoresPorCurso($curso->getCUR_ID_CURSO()); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }
?>
