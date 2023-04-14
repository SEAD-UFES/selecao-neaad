<?php

function _EXC__fragmentoPergExc($onclickBtExc, $idDivPergExclusao = "perguntaExclusao", $htmlConfirmacao = "", $idParamAddConfirmacao = "") {
    ?>
    <div id="<?php echo $idDivPergExclusao; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $idDivPergExclusao; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">   
                <div class="modal-header">
                    <h2 style="margin:10px 0;">Confirmação de Exclusão</h2>
                </div>

                <div class="modal-body" style="text-align:center;">
                    <h0 style="color:#d43f3a;"><i class="fa fa-warning"></i></h0>
                    <div class="aviso">Atenção! Essa ação não tem volta.</div>
                    <?php if (!Util::vazioNulo($htmlConfirmacao)) { ?>
                        <p class="m01" align="left">
                            <?php
                            echo $htmlConfirmacao;
                            if (!Util::vazioNulo($idParamAddConfirmacao)) {
                                ?>
                                <strong id="<?php echo $idParamAddConfirmacao; ?>"></strong>
                            <?php }
                            ?>
                        </p>
                    <?php } ?>
                    <p align="center" class="m01">Tem certeza que deseja prosseguir com a exclusão?</p>
                </div>
                <div class="modal-footer" style="text-align:center;">
                    <div class="popupBtExcluir">  
                        <button type="button" class="btn btn-danger" aria-hidden="true" onclick="<?php print $onclickBtExc; ?>">Sim, excluir</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancelar</button>
                    </div>
                    <div class="popupMsgExcluir col-full" style="display:none">
                        <div class="alert alert-info">
                            Aguarde o processamento...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function () {
                $('#<?php echo $idDivPergExclusao; ?>').appendTo($('body'));
            });
        </script>
    </div>


    <?php
}

/**
 * Ao chamar esta função, utilize:
 * 
 *  
 * nome da div de pergunta: perguntaExclusao
 * 
 * @param string $nmForm Nome do formulário de exclusão. Padrão: formExcluir
 * @param string $htmlConfirmacao Código HTML com explicação adicional sobre a exclusão
 * 
 */
function EXC_fragmentoPergExcEmPag($nmForm = "formExcluir", $htmlConfirmacao = "") {
    _EXC__fragmentoPergExc("javascript: $('.popupBtExcluir').hide();
                            $('.popupMsgExcluir').show();
                            $('#$nmForm').submit();", "perguntaExclusao", $htmlConfirmacao);
}

/**
 * 
 * @param string $pagEnvio URL para a qual deve ser feito o post 
 * @param string $nmFuncaoJS Nome da função javascript responsável por tratar os dados
 * @param array $arrayIdElemExc Array com os id's que identificam o objeto na lista a ser excluído.
 * @param array $arrayParamHidden Array na forma [id => valor] com os parâmetros hidden a ser enviados.
 * @param string $idFormExc String com o ID que deverá ser utilizado no form de exclusão.
 * @param string $idDivPerguntaExc String com o ID que deverá ser utilizado na divPergunta.
 * @param string $htmlConfirmacao Mensagem de confirmação a ser exibida ao solicitar a exclusão, se houver.
 * @param string $idParamAddConfirmacao ID do parâmetro adicional da mensagem, que será informado em tempo de chamada da confirmação de exclusão
 */
function EXC_fragmentoPergExcEmLista($pagEnvio, $nmFuncaoJS, $arrayIdElemExc, $arrayParamHidden = array(), $idFormExc = "formExcLista", $idDivPerguntaExc = "divPergExclusao", $htmlConfirmacao = "", $idParamAddConfirmacao = "") {
    ?>
    <form id="<?php echo $idFormExc; ?>" method="post" action="<?php echo $pagEnvio; ?>">
        <?php
        // parametros hidden padrao
        foreach ($arrayParamHidden as $id => $valor) {
            ?>    
            <input type="hidden" name="<?php echo $id; ?>" value="<?php echo $valor; ?>">
            <?php
        }

        // parâmetros hidden identificadores do elemento
        $paramsJS = implode(",", $arrayIdElemExc);

        foreach ($arrayIdElemExc as $id) {
            ?>    
            <input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="">
            <?php
        }

        // parâmetro adicional de msg para o JS
        if (!Util::vazioNulo($idParamAddConfirmacao)) {
            $paramsJS .= ", $idParamAddConfirmacao";
        }
        ?>
    </form>

    <script type="text/javascript">
        function <?php echo $nmFuncaoJS; ?>(<?php echo $paramsJS; ?>) {
    <?php
// setando valor dos id's variáveis
    foreach ($arrayIdElemExc as $id) {
        ?>
                $("#<?php echo $idFormExc; ?>").find("#<?php echo $id; ?>").val(<?php echo $id; ?>);
        <?php
    }

// setando parâmetro adicional da msg
    if (!Util::vazioNulo($idParamAddConfirmacao)) {
        ?>
                $("#<?php echo $idDivPerguntaExc; ?>").find("#<?php echo $idParamAddConfirmacao; ?>").html(<?php echo $idParamAddConfirmacao; ?>);
    <?php }
    ?>
            $("#<?php echo $idDivPerguntaExc; ?>").modal();
        }
    </script>

    <?php
    _EXC__fragmentoPergExc("javascript: $('.popupBtExcluir').hide();
                            $('.popupMsgExcluir').show();
                            $('#$idFormExc').submit();", $idDivPerguntaExc, $htmlConfirmacao, $idParamAddConfirmacao);
    ?>

<?php }
?>