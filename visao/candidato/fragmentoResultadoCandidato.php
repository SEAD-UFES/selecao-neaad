<?php

/**
 * Esta função adiciona o aviso de aprovação ou eliminação de um candidato em sua página de 
 * minhas inscrições
 * 
 * @param InscricaoProcesso $inscricao
 * @param ProcessoChamada $chamada
 */
function RES_USU_MinhasInscricoes($inscricao, $chamada) {
    ?>
    <!-- #TP02 TESTE DE PROPOSTA: Para Renato validar rs -->   
    <div class="col-full m02"> 
        <?php
        // caso de eliminação
        if ($inscricao->isEliminada()) { // estilizar o motivo...
            ?>
            <div class="callout callout-info">
                Você foi <b>eliminado</b> deste processo seletivo.
                <br/>
                Motivo: <?php echo $inscricao->getIPR_DS_OBS_NOTA(); ?>
            </div>
        <?php } elseif ($inscricao->isCadastroReserva()) {
            ?>
            <div class="callout callout-info">
                Você foi inserido na lista de <b>cadastro de reserva</b> deste processo seletivo.
            </div>
            <?php
        } elseif ($inscricao->isAprovada()) {
            // recuperando o complemento de aprovação
            $compAprovacao = $inscricao->isNotaFinal($chamada) ? "neste edital" : "na {$inscricao->getNomeEtapaNota()} deste edital";

            if (!Util::vazioNulo($inscricao->getIPR_ID_ETAPA_SEL_NOTA())) {
                ?>
                <div class="callout callout-success">
                    Você foi <b>aprovado</b> <?php echo $compAprovacao ?>.
                </div>
                <?php
            }
        } else {
            // dispara exceção, pois é um caso não programado
            throw new NegocioException("Tipo de inscrição não programada em RES_USU_MinhasInscricoes.");
        }
        ?>
    </div>
    <?php
}
