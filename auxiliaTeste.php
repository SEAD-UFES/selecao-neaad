<?php
// ATENÇÃO: Não transfira este arquivo para o servidor de produção!

/**
 * Essa função executa o script de reinicialização do banco de teste.
 * 
 * @return string Status da reinicialização do banco de teste
 */
function reiniciarBDTeste() {
    $erros = array();
    $resultado = -1;

    // executando script de reinstalação
    exec("/var/scripts-selecaoneaad/instalacaoBD.sh 2>&1 1> /dev/null", $erros, $resultado);
    if ($resultado) {
        // Ocorreu erro: Infomar
        $ret = "Erro ao reiniciar banco de teste:\n";
        foreach ($erros as $linha) {
            $ret .= $linha . "\n";
        }
        return $ret;
    } else {
        return "Banco reinicializado com sucesso.\n";
    }
}

/**
 * Essa função executa o comando SQL no banco de teste
 * 
 * @param string $comando Comando Sql a ser executado no BD
 * @return string Status da execução do comando
 */
function executarComandoSql($comando) {
    try {
        $conexao = NGUtil::getConexao();
        $conexao->execSqlSemRetorno($comando, NULL, TRUE);

        return "Comando executado com sucesso.";
    } catch (Exception $ex) {
        $msg = str_replace("<br/>", "", $ex->getMessage());
        return "Erro ao executar comando:\n" . $msg;
    }
}
?>
<html>
    <head>
        <title>Seleção EAD Teste - Auxiliador de testes</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once 'config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado Administrador
        require_once ($CFG->rpasta . "/util/sessao.php");
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }

        // inicializando variáveis de estado
        $resultadoReiniciarBD = "Aqui aparecerá o resultado da reinicialização do BD...";
        $resultadoCmdSql = "Aqui aparecerá o resultado da execução do comando SQL no BD...";

        // verificando funções
        if (isset($_POST['fn'])) {
            if ($_POST['fn'] == "reiniciarBD") {
                $resultadoReiniciarBD = reiniciarBDTeste();
            } elseif ($_POST['fn'] == "cmdSql") {
                $resultadoCmdSql = executarComandoSql($_POST['comandoSql']);
            }
        }
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>
    </head>

    <body>
        <div id="main">
            <div id="container" class="clearfix contents">
                <fieldset class="completo m02">
                    <legend>Reiniciar Banco de Teste</legend>
                    <form class="form-horizontal col-full" id="formReiniciarBD" action='<?php print $CFG->rwww . "/auxiliaTeste.php" ?>' method="post">
                        <input type="hidden" name="fn" value="reiniciarBD">
                        <p>Esta função reinicia o banco de teste, retornando-o ao estado inicial de instalação.</p>

                        <div id="divBotoes" class="completo m02">
                            <button class="btn btn-default" id="submeter" type="submit">Reiniciar BD</button>
                        </div>
                        <div id="divMensagem" class="completo m02" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>

                        <div class="form-group completo m01">
                            <textarea class="form-control" rows="8" style="width:100%;font-size: 13px;" disabled><?php print $resultadoReiniciarBD; ?></textarea>
                        </div>
                    </form>
                </fieldset>

                <fieldset class="completo m02">
                    <legend>Executar <strong>Comando</strong> SQL</legend>
                    <form class="form-horizontal col-full" id="formCmdSql" action='<?php print $CFG->rwww . "/auxiliaTeste.php" ?>' method="post">
                        <input type="hidden" name="fn" value="cmdSql">
                        <p>Aqui é possível executar algum comando SQL no banco de teste.</p>

                        <div class="form-group m01">
                            <textarea class="form-control" title="Informe o comando Sql" required name="comandoSql" rows="8" style="width:100%;font-size: 13px;"></textarea>
                        </div>

                        <div id="divBotoesSql" class="completo m01">
                            <button class="btn btn-default" id="submeter" type="submit">Executar Comando</button>
                        </div>
                        <div id="divMensagemSql" class="completo m01" style="display:none">
                            <div class="alert alert-info">
                                Aguarde o processamento...
                            </div>
                        </div>

                        <div class="form-group completo m01">
                            <textarea class="form-control" rows="8" style="width:100%;font-size: 13px;" disabled><?php print $resultadoCmdSql; ?></textarea>
                        </div>
                    </form>
                </fieldset>

            </div>
        </div>
    </body>
    <?php
    require($CFG->rpasta . "/include/includesPos.php");
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            //validando form
            $("#formReiniciarBD").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    mostrarMensagem();
                    form.submit();
                }
            }
            );

            //validando form
            $("#formCmdSql").validate({
                submitHandler: function (form) {
                    //evitar repetiçao do botao
                    $("#divBotoesSql").hide();
                    $("#divMensagemSql").show();
                    form.submit();
                }
            }
            );

        });
    </script>
</html>
