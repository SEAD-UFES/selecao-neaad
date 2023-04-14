<?php
global $CFG;
require_once ($CFG->rpasta . "/controle/CTNoticia.php");
require_once ($CFG->rpasta . "/util/sessao.php");

$tpNoticia = estaLogado() != NULL ? NULL : Noticia::$NOTICIA_PUBLICA;

// Buscando notícias iniciais
$noticias = buscarUltimasNoticiasCT($tpNoticia, 0, Noticia::$QT_NOTICIAS_POR_PAG);
?>

<?php if (!Util::vazioNulo($noticias)) { ?>

    <script type="text/javascript">
        function buscarNoticia(ini) {
            if ($("#clicou").val() === "0")
            {
                // desabilitando botões
                $("#clicou").val("1");

                // enviando dados 
                $.ajax({
                    type: "POST",
                    url: getURLServidor() + "/controle/CTNoticia.php",
                    data: {"valido": 'noticia', "fn": 'noticia', "ini": ini},
                    dataType: "json",
                    success: function (json) {
                        if (!json['situacao'])
                        {
                            var msg = "Desculpe, ocorreu um erro no servidor e não foi possível recuperar as notícias.\n\nDetalhe: " + json['msg'] + "\n\nTente novamente."
                            alert(msg);
                        } else {
                            // substituindo Htmls
                            $("#divNoticia").fadeOut("fast").html(json['htmlNoticia']).fadeIn("fast");
                            $("#ulPaginacaoNoticia").html(json['htmlPaginacao']);
                        }

                        // habilita botões
                        $("#clicou").val("0");
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        var msg = "Desculpe, ocorreu um erro ao tentar recuperar as notícias.\n\n";
                        msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

                        // exibindo mensagem
                        alert(msg);

                        // habilita botões
                        $("#clicou").val("0");
                    }
                });
            }
        }
    </script>

    <div class="row m02">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h3 class="sublinhado">Notícias</h3>

            <input type="hidden" id="clicou" value="0">
            <div id="divNoticia">
                <?php
                foreach ($noticias as $noticia) {
                    print $noticia->getHtmlNoticia();
                }
                ?>
            </div>

            <div id="paginacao">
                <ul id="ulPaginacaoNoticia" class="col-md-12 col-sm-12 col-xs-12 pager">
                    <?php
                    print getHtmlUlPaginacaoNoticia($tpNoticia, 0);
                    ?>
                </ul>
            </div>
        </div>
    </div>

<?php } ?>