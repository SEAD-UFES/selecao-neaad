<?php
// apenas candidatos tem inscrições pendentes
if (estaLogado(Usuario::$USUARIO_CANDIDATO)) {
    // verificando se há inscrição pendente
    $arrayInscPendente = RAT_buscarRastreioPorFiltroCT(getIdUsuarioLogado(), NULL, NULL, UsuarioRastreio::$TP_RASTREIO_INSC_EDITAL);

    if (count($arrayInscPendente) > 0) {
        $inscPendente = $arrayInscPendente[0];

        require_once ($CFG->rpasta . "/controle/CTProcesso.php");

        // acabou o período de inscrição?
        if (!validaPeriodoInscPorChamadaCT($inscPendente->getPCH_ID_CHAMADA_REL())) {
            // removendo insc pendente e retornando
            RAT_removerRastreioCT($inscPendente->getURT_ID_RASTREIO());
            return;
        }

        // Validou: recuperando dados para inseção de aviso
        $processo = buscarProcessoPorIdCT($inscPendente->getPRC_ID_PROCESSO_REL());
        $chamada = buscarChamadaPorIdCT($inscPendente->getPCH_ID_CHAMADA_REL(), $inscPendente->getPRC_ID_PROCESSO_REL());
        ?>

        <div id="inscricaoPendente" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="inscricaoPendente" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">   
                    <div class="modal-header">
                        <h2 style="margin:10px 0;">Inscrição Pendente</h2>
                    </div>

                    <div class="modal-body" style="width:80%;margin:0 auto;">
                        <div style="text-align:center;">
                            <h0 style="color:#104778;"><i class="fa fa-info-circle"></i></h0>
                            <div class="aviso">Atenção! Há uma inscrição pendente.</div>
                            <p align="center">Você visitou a página de inscrição do edital abaixo, mas não concluiu o processo.</p>
                        </div>

                        <div class="bs-callout bs-callout-info m02">
                            <b>Edital:</b> <?php echo $processo->getNumeracaoEdital(); ?><br>
                            <b>Atribuição:</b> <?php echo $processo->TIC_NM_TIPO_CARGO; ?><br>
                            <b>Curso:</b> <?php echo $processo->TPC_NM_TIPO_CURSO; ?><br>
                            <b>Chamada</b> <?php echo $chamada->getPCH_DS_CHAMADA(); ?>
                        </div>
                    </div>

                    <div id='inscPendenteDivBotoes' class="modal-footer">
                        <div style="text-align:center;width:80%;margin:0 auto;">
                            <div class="col-half">
                                <button type="button" style="width:100%;" class="btn btn-success"  data-dismiss="modal" onclick="javascript: window.open('<?php echo "$CFG->rwww/{$inscPendente->getURT_DS_URL_ACESSO()}"; ?>', '_blank');" aria-hidden="true">Inscrever-se</button>
                            </div>
                            <div class="col-half m01">
                                <button type="button" style="width:100%;" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Lembrar mais tarde</button>
                            </div>
                            <div class="col-full m01">
                                <button type="button" style="width:100%;" class="btn btn-default" onclick="javascript: removeAlerta();" data-dismiss="modal" aria-hidden="true">Não tenho interesse <span class='campoDesktop'>neste edital, ignorar</span></button>
                            </div>
                        </div>
                    </div>
                    <div id='inscPendenteDivMensagem' style="display: none" class="alert alert-info">
                        Aguarde o processamento...
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            // funçao que remove alerta de inscrição pendente
            function removeAlerta() {
                // chamando função
                $.ajax({
                    type: "POST",
                    url: getURLServidor() + "/controle/CTAjax.php?atualizacao=removerRastreio",
                    data: {"idRastreio": '<?php echo $inscPendente->getURT_ID_RASTREIO(); ?>'},
                    dataType: "json",
                    success: function (json) {
                    },
                    error: function (xhr, ajaxOptions, thrownError) {

                    }
                });
            }

            $(document).ready(function () {
                $('#inscricaoPendente').appendTo($('body'));
                $('#inscricaoPendente').modal();
            });
        </script>
        <?php
    }
}
?>