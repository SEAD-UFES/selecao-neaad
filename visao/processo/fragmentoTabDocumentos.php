<div id="att" role="tabpanel" aria-labelledby="atualizacaoEdital">
    <div class="panel-body">                           
        <?php
        if ($processo->isFechado()) {
            ?>
            <div class='callout callout-warning'>Este edital está finalizado.</div>
            <?php
        }
        print tabelaAtualizacaoProcesso($processo->getPRC_ID_PROCESSO());
        ?>
    </div> 
</div>