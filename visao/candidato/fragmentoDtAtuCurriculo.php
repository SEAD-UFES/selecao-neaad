<?php

/**
 * 
 * @param Candidato $candidato
 * @param string $classeadd adiciona classe a div.
 */
function DAC_geraHtml($candidato, $classeadd = "") {
    $classeadd = Util::vazioNulo($classeadd) ? "" : "class='$classeadd'";
    if ($candidato != NULL) {
        ?>
        <div <?php echo $classeadd; ?> >  
            <i class="fa fa-clock-o"></i> Última alteração: <?php !Util::vazioNulo($candidato->getCDT_DT_ULT_ATUALIZACAO()) ? print $candidato->getCDT_DT_ULT_ATUALIZACAO() : print Util::$STR_CAMPO_VAZIO; ?>
        </div>
        <?php
    }
}
?>

