<?php

function LOT_fragmentoElimLote($idProcesso, $idChamada, $nrEtapa, $qtElimLote, $idDivPergElimLote = "perguntaElimLote") {
    global $CFG;
    ?>
    <div id="<?php echo $idDivPergElimLote; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $idDivPergElimLote; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">   
                <div class="modal-header">
                    <h2 style="margin:10px 0;">Confirmação de Eliminação</h2>
                </div>
                <form id="formElimLote" class="form-horizontal" action='<?php print $CFG->rwww . "/controle/CTProcesso.php?acao=eliminarEmLote" ?>' method="post">
                    <input type="hidden" name="valido" value="ctprocesso">
                    <input type="hidden" name="idProcesso" value="<?php echo $idProcesso; ?>">
                    <input type="hidden" name="idChamada" value="<?php echo $idChamada; ?>">
                    <input type="hidden" name="nrEtapa" value="<?php echo $nrEtapa; ?>">
                    <div class="modal-body" style="text-align:center;">
                        <h0 style="color:#d43f3a;"><i class="fa fa-warning"></i></h0>
                        <div class="aviso">Atenção! Essa ação não tem volta.</div>
                        <p align="center" class="m01">Tem certeza que deseja prosseguir com a eliminação de <b>todos os candidatos</b> que não foram avaliados? (<b><?php echo $qtElimLote; ?></b> no total)</p>
                        <p align="center">Você só poderá reverter a eliminação individualmente.</p>
                        <div class="form-group m01" style="text-align:left;">
                            <label class="control-label">Justificativa: *</label>
                            <textarea id="mensagem" name="mensagem" class="form-control" cols="25" rows="3" required></textarea>
                            <div id="qtCaracteres" class="totalCaracteres">caracteres restantes</div>
                        </div>
                    </div>
                    <div class="modal-footer" style="text-align:center;">
                        <div id="divBotoesElim">  
                            <button type="submit" class="btn btn-danger" aria-hidden="true">Sim, eliminar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancelar</button>
                        </div>
                        <div id="divMensagemElim" class="col-full" style="display:none">
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
                adicionaContadorTextArea('<?php print InscricaoProcesso::$MAX_CARACTERES_OBS_NOTA; ?>', 'mensagem', 'qtCaracteres');

                $('#<?php echo $idDivPergElimLote; ?>').appendTo($('body'));

                //validando form
                $("#formElimLote").validate({
                    submitHandler: function (form) {
                        //evitar repetiçao do botao
                        $('#divBotoesElim').hide();
                        $('#divMensagemElim').show();
                        form.submit();
                    },
                    rules: {
                        mensagem: {
                            required: true,
                        }, messages: {
                        }
                    }
                }
                );
            });
        </script>
    </div>
    <?php
}
?>