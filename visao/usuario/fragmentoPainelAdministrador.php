<?php
global $CFG;

if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR)) {
    ?>
    <div class="row m01">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3 class="sublinhado">Pendências</h3>

            <?php
            echo tabelaPendenciasAdministrador();
            ?>

        </div>
    </div>
<?php }
?>
