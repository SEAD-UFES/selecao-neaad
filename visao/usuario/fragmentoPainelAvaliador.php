<?php
global $CFG;

if (estaLogado(Usuario::$USUARIO_AVALIADOR)) {
    // buscando curso que avalia
    $usu = buscarUsuarioPorIdCT(getIdUsuarioLogado());
    $curso = !Util::vazioNulo($usu->getUSR_ID_CUR_AVALIADOR()) ? buscarCursoPorIdCT($usu->getUSR_ID_CUR_AVALIADOR()) : NULL;

    if ($curso == NULL) {
        ?>
        <div class="callout callout-info">
            No momento, você <b>NÃO</b> avalia um curso.
        </div>
    <?php }
    ?>
    <div class="row m01">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3 class="sublinhado">Avaliação de <?php echo $curso->getCUR_NM_CURSO() ?> - <?php echo $curso->TPC_NM_TIPO_CURSO; ?></h3>
            <h4 style="color:#104778;font-weight:normal;margin-top:-0.5em;">Departamento de <?php echo $curso->DEP_DS_DEPARTAMENTO; ?> | <?php echo $curso->getDsAreaSubarea(); ?></h4>


            <div class="m02">
                <?php if ($curso->temCoordenador()) { ?>
                    <div class="table-responsive p15">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Coordenador</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $curso->CUR_NM_COORDENADOR; ?></td>
                                    <td><?php echo $curso->CUR_EMAIL_COORDENADOR; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="callout callout-info">
                        No momento, <b>NÃO</b> há um coordenador cadastrado para este curso.
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php }
?>
