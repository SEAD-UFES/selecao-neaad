<?php

/**
 * Ao utilizar esta função, utilize:
 * 
 * Nome da div pergunta: pergAltFimChamada
 * 
 * Deve ter sido incluído os seguintes scripts na página pai:
 * "jquery.maskedinput", "additional-methods" e "metodos-adicionaisBR"
 */
function PRO__fragmentoAlterarFimChamada($idProcesso, $idChamada, $dtFimAtual, $mostrarFimEdital) {
    global $CFG;

    $dtMax = dt_somarData(dt_getDataEmStr("d/m/Y"), Processo::$TEMPO_PADRAO_FINALIZACAO);
    ?>
    <div id="pergAltFimChamada" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="pergAltFimChamada" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">   
                <div class="modal-header">
                    <h2 style="margin:10px 0;">Alteração de finalização <?php $mostrarFimEdital ? print "da chamada" : print "do edital"; ?></h2>
                </div>
                <form id="formFimProcesso" class="form-horizontal" action='<?php print $CFG->rwww . "/controle/CTProcesso.php?acao=alterarFinalizacao" ?>' method="post">
                    <input type="hidden" name="valido" value="ctprocesso">
                    <input type="hidden" name="idProcesso" value="<?php echo $idProcesso; ?>">
                    <input type="hidden" name="idChamada" value="<?php echo $idChamada; ?>">
                    <input type="hidden" name="fecharChamada" value="<?php $mostrarFimEdital ? print FLAG_BD_SIM : print FLAG_BD_NAO; ?>">
                    <input type="hidden" id="dtFimAtual" value="<?php echo $dtFimAtual; ?>">
                    <input type="hidden" id="dtAtual" value="<?php echo dt_getDataEmStr("d/m/Y"); ?>">
                    <input type="hidden" id="dtMax" value="<?php echo $dtMax; ?>">
                    <div class="modal-body">
                        <p>
                            Altere a data abaixo (data máx: <?php echo $dtMax; ?>):
                            <i class="fa fa-question-circle" title="Se você escolher o dia de hoje, <?php $mostrarFimEdital ? print "a chamada será finalizada" : print "o edital será finalizado"; ?> imediatamente."></i>
                        </p>
                        <input type="date" class="form-control" name="dtFinalizacao" id="dtFinalizacao" value="<?php echo $dtFimAtual; ?>" required>
                        <p>ou</p>
                        <label>
                            <input id="finalizarAgora" name="finalizarAgora" type="checkbox" value="<?php echo FLAG_BD_SIM; ?>" > 
                            Finalizar agora 
                            <i class="fa fa-question-circle" title="Se você marcar esta opção, <?php $mostrarFimEdital ? print "a chamada será finalizada" : print "o edital será finalizado"; ?> neste momento."></i>
                        </label>
                        <?php if ($mostrarFimEdital) { ?>
                            <label id="labelFimEdital" style="display: none">
                                <input id="fimEdital" name="fimEdital" type="checkbox" checked="true" value="<?php echo FLAG_BD_SIM; ?>"> 
                                Também finalizar o edital
                                <i class="fa fa-question-circle" title="Se você marcar esta opção, então o edital também será finalizado."></i>
                            </label>
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <div id="divBotoesFimProcesso">  
                            <button type="submit" class="btn btn-success" aria-hidden="true">Enviar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancelar</button>
                        </div>
                        <div id="divMensagemFimProcesso" class="col-full" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                // adicionando máscaras
                $("#dtFinalizacao").mask("99/99/9999");

                $("#finalizarAgora").change(function () {
                    if ($(this).is(':checked')) {
                        $("#dtFinalizacao").val($("#dtAtual").val());
                        $("#dtFinalizacao").prop("disabled", true);
                        $("#labelFimEdital").show();
                    } else {
                        $("#dtFinalizacao").val($("#dtFimAtual").val());
                        $("#dtFinalizacao").prop("disabled", false);
                        $("#labelFimEdital").hide();
                    }
                }
                );

                $("#formFimProcesso").validate({
                    submitHandler: function (form) {
                        //evitar repetiçao do botao
                        $('#divBotoesFimProcesso').hide();
                        $('#divMensagemFimProcesso').show();
                        form.submit();
                    },
                    rules: {
                        dtFinalizacao: {
                            required: true,
                            dataBR: true,
                            dataBRMaior: "#dtFimAtual",
                            dataBRMenorIgual: "#dtMax"
                        }
                    }, messages: {
                        dtFinalizacao: {
                            dataBRMaior: "A nova data de finalização deve ser maior que a data de finalização atual.",
                            dataBRMenorIgual: "A nova data de finalização deve ser menor ou igual a data máxima."
                        }
                    }
                });

            });
            $('#pergAltFimChamada').appendTo($('body'));
        </script>
    </div>
    <?php
}

/**
 * Ao utilizar esta função, utilize:
 * 
 * Nome da div pergunta: pergReabrirEdital
 */
function PRO__fragmentoReabrirEdital($idProcesso) {
    global $CFG;
    ?>
    <div id="pergReabrirEdital" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="pergReabrirEdital" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">   
                <div class="modal-header">
                    <h2 style="margin:10px 0;">Reabrir edital</h2>
                </div>
                <form id="formReabrirEdital" class="form-horizontal" action='<?php print $CFG->rwww . "/controle/CTProcesso.php?acao=reabrirEdital" ?>' method="post">
                    <input type="hidden" name="valido" value="ctprocesso">
                    <input type="hidden" name="idProcesso" value="<?php echo $idProcesso; ?>">
                    <div class="modal-body">
                        <div class="alerta">
                            <h0><i class="fa fa-warning"></i></h0>
                            Atenção! Leia atentamente a mensagem abaixo.
                        </div>
                        <p>O edital será reaberto e, caso não haja mudanças em até <b><?php echo Processo::$TEMPO_PADRAO_FINALIZACAO; ?></b> dias, ele será novamente fechado.</p>
                        <label>
                            <input id="reabrirChamada" name="reabrirChamada" type="checkbox" value="<?php echo FLAG_BD_SIM; ?>" > 
                            Também reabrir última chamada
                            <i class="fa fa-question-circle" title="Se você marcar esta opção, a última chamada do edital também será reaberta."></i>
                        </label>
                    </div>
                    <div class="modal-footer">
                        <div id="divBotoes">  
                            <button type="submit" class="btn btn-success" aria-hidden="true">Tudo bem, reabrir</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancelar</button>
                        </div>
                        <div id="divMensagem" class="col-full" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function () {
                $("#formReabrirEdital").validate({
                    submitHandler: function (form) {
                        //evitar repetiçao do botao
                        mostrarMensagem();
                        form.submit();
                    },
                    rules: {
                    }, messages: {
                    }
                });

                $('#pergReabrirEdital').appendTo($('body'));
            });
        </script>
    </div>
    <?php
}
?>