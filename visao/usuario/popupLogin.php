<?php
global $CFG;
require_once($CFG->rpasta . "/include/includes.php");
?>
<html>
    <head>
        <title>Página de acesso restrito</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    </head>
    <body>
        <div id="popLogin" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">  
                <div class="modal-content">   
                    <div class="modal-header">
                        <h2 style="margin:10px 0;">Acesso restrito</h2>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-danger" style="width:100%;margin:0 auto;margin-bottom:1em;">
                            Você precisa estar logado para ver esta página.
                        </div>
                        <?php include("$CFG->rpasta/include/fragmentoAcesso.php"); ?>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">Tudo bem, não quero acessar agora. Sair <i class="fa fa-external-link"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        require_once($CFG->rpasta . "/include/includesPos.php");
        carregaScript('metodos-adicionaisBR');
        ?>
        <script>
            $(document).ready(function () {
                $('#popLogin').modal('show');

                $('#popLogin').on('hidden.bs.modal', function (e) {
                    window.close();
                });
            });
        </script>
    </body>
</html>

