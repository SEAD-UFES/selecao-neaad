<?php 
   //buscando objeto candidato
$candidato = buscarCandidatoPorIdUsuCT(getIdUsuarioLogado());
require_once ($CFG->rpasta . "/visao/candidato/fragmentoDtAtuCurriculo.php");
DAC_geraHtml($candidato);
?>

<div class="lattes m01">
    <div class="form-group">
        <img src="<?php print $CFG->rwww . "/imagens/lattes18.png" ?>" /> <span style="font-weight: bold">Currículo Lattes: </span>
        <span style="display: none" id="exibicaoLattes">
            <a id="linkLattes" target="_blank" href=""></a>
            <a title="Editar endereço do currículo Lattes" onclick="javascript: editarLattes();"><i class='fa fa-edit'></i></a>
        </span>

        <span style="display: none" id="erroLattes">
            &lt;Erro ao tentar recuperar link do Currículo Lattes&gt;&nbsp;
            <a title="Tentar novamente..." href="javascript: tratarLattes();"><i class='fa fa-refresh'></i></a>
        </span>

    </div>
</div>

<div id="modalEditarLattes" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEditarLattes" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">   
            <div class="modal-header">
                <h2 style="margin:10px 0;">Plataforma Lattes</h2>
            </div>

            <div class="modal-body" style="width:80%;margin:0 auto;text-align:center;">
                <h0 style="color:#104778;"><i class="fa fa-warning"></i></h0>
                <div class="azul">Atenção! Não substitui o preenchimento do currículo no sistema.</div>

                <div class="m02">
                    <input style="display: inline;width:100%;max-width:100%;" class="input-fixo form-control tudo-normal" type="text" id="dsLinkLattes" name="dsLinkLattes" size="50" maxlength="255" placeholder="http://lattes.cnpq.br/1234567898765432" value="">
                    <span style="display: inline">&nbsp;<label id="erroLinkLattes" for="dsLinkLattes" class="error" style="display:none"></label></span>
                </div>
            </div>
            <div id='popupBtLattes' class="modal-footer" style="text-align:center;">
                <button  id="btSalvarLattes" type="button" class="btn btn-success" title="Salvar atualização de endereço do currículo Lattes" onclick="javascript: salvarLattes();" aria-hidden="true">Salvar</button>
                <button id="btRemoverLattes" style="display: none;" type="button" class="btn btn-danger" title="Remover link do currículo Lattes do sistema" onclick="javascript: $('#dsLinkLattes').prop('value', '');
                        salvarLattes();" aria-hidden="true">Remover URL</button>
                <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            </div>
            <div id='popupMsgLattes' style="display: none" class="alert alert-info">
                Aguarde o processamento...
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    // funcoes de edicao lattes
    function editarLattes() {
        // colocando no campo de edicao o link atual e verificando necessidade de botão de exclusão
        if (/^http*/.test($("#linkLattes").html()))
        {
            $("#dsLinkLattes").prop("value", $("#linkLattes").html());
            $("#btRemoverLattes").show();
        } else {
            $("#dsLinkLattes").prop("value", "");
            $("#btRemoverLattes").hide();
        }


        // chamando modal
        $("#modalEditarLattes").modal('show');
    }

    // executa operacao de salvar curriculo lattes
    function salvarLattes() {
        $("#popupBtLattes").hide();
        $("#popupMsgLattes").show();

        // Erro: Link inválido
        if (!vazioOuNulo($("#dsLinkLattes").val() && !validaURLLattes())) {
            return;
        }

        // enviando dados 
        $.ajax({
            type: "POST",
            url: getURLServidor() + "/controle/CTAjax.php?lattes=salvar",
            data: {"idUsuario": '<?php print getIdUsuarioLogado() ?>', "linkLattes": $("#dsLinkLattes").val()},
            dataType: "json",
            success: function (json) {
                if (!json['situacao'])
                {
                    var msg = "Desculpe, ocorreu um erro no servidor e não foi possível salvar o link do seu currículo Lattes.\n\nDetalhe: " + json['msg'] + "\n\nTente novamente."
                    alert(msg);
                } else {

                    // disparando toast
                    $().toastmessage('showToast', {
                        text: '<b>Endereço do currículo Lattes atualizado com sucesso.</b>',
                        sticky: false,
                        type: 'success',
                        position: 'top-right'
                    });
                }
                tratarLattes();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor para salvar o link do seu currículo Lattes.\n\n";
                msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                // exibindo mensagem
                alert(msg);
                tratarLattes();
            }
        });

        $("#popupMsgLattes").hide();
        $("#popupBtLattes").show();

        // fechando modal
        $("#modalEditarLattes").modal('hide');
    }


    // funçao que trata detalhes de obtençao do curriculo lattes
    function tratarLattes() {
        // tentando obter link do lattes
        $.ajax({
            type: "POST",
            url: getURLServidor() + "/controle/CTAjax.php?lattes=obter",
            data: {"idUsuario": '<?php print getIdUsuarioLogado() ?>'},
            dataType: "json",
            success: function (json) {
                if (json['situacao'])
                {
                    //alterando links 
                    if (json['val'])
                    {
                        // link é válido
                        $("#linkLattes").attr("href", json['link']);
                    } else {
                        // desabilitando clique
                        $("#linkLattes").attr("onclick", "javascript: return false;");
                    }
                    $("#linkLattes").html(json['link']);

                    //mostrando exibicao
                    $("#erroLattes").hide();
                    $("#exibicaoLattes").show();
                } else {
                    // erro 
                    $("#exibicaoLattes").hide();
                    $("#erroLattes").show();
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
                // exibindo div de erro
                $("#exibicaoLattes").hide();
                $("#erroLattes").show();
            }
        });
    }

    /**
     * Função que valida uma URL lattes
     * 
     * @param {Object} obj - Objeto de validação
     * @returns {Boolean}  
     **/
    validaURLLattes = function (obj) {
        var val = $("#dsLinkLattes").val();
        var er = /^http:\/\/lattes\.cnpq\.br\/\d{16}$/;
        var validou = val === "" || er.test(val);
        if (typeof obj === 'undefined')
        {
            return validou;
        }

        if (!validou) {
            $("#erroLinkLattes").html("Link inválido");
            $("#btSalvarLattes").attr("disabled", true);
            $("#erroLinkLattes").show();
            return true;
        } else {
            $("#erroLinkLattes").hide();
            $("#btSalvarLattes").attr("disabled", false);
            return true;
        }
    };

    $(document).ready(function () {
        // chamando funcao de tratamento do lattes
        tratarLattes();

        // gatilhos para input correto

        // incluindo gatilho para o input
        $("#dsLinkLattes").keyup(validaURLLattes);
        $("#dsLinkLattes").keydown(validaURLLattes);
        $('#modalEditarLattes').appendTo($('body'));
    });
</script>