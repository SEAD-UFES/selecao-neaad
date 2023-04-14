<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mensagem
 *
 * @author estevao
 */
class Mensagem {

    private $mensagem;
    public static $TOAST_VAR_GET = "tst";
    public static $MENSAGEM_ERRO = "E";
    public static $MENSAGEM_PADRAO_ERRO = "Erro ao exibir a página.";
    public static $MENSAGEM_AVISO = "A";
    public static $MENSAGEM_INFORMACAO = "I";
    // mensagem exibida quando campos validados via ajax ou script falham na validaçao do servidor
    public static $MSG_ERR_VAL_POS_AJAX = "Desculpe. Foi detectado uma inconsistência no envio do formulário.<br/>Isso pode ocorrer quando a página não é carregada totalmente antes de ser enviada, ou quando a conexão de Internet está muito lenta ou ainda quando seu navegador não está atualizado.<br/>Por favor, volte à página na qual você estava, espere o carregamento total da página e tente novamente. Se o erro persistir, verifique sua conexão e atualize seu navegador.";
    private static $vetTipo = array("E" => "Erro", "A" => "Aviso", "I" => "Informação");
    private static $vetClasse = array("E" => "alert alert-danger", "A" => "alert alert-warning", "I" => "alert alert-info");
    private $tipo; // E= Erro, A= Aviso, I = Informação

    //cria uma mensagem a ser exibida

    public function __construct($msg, $tipo, $msgRecuperavel = FALSE, $msgToast = FALSE, $url = FALSE) {
        global $CFG;

        // verificando se e Toast
        if ($msgToast !== FALSE) {
            // retornando a URL, sinalizando o erro
            $param = Mensagem::$TOAST_VAR_GET . "=" . $msgToast;
            header("Location: {$this->addParamUrl($url, $param)}");
            exit;
        }

        //setando valores
        $this->mensagem = $msg;
        $this->tipo = $tipo;

        if (!$msgRecuperavel) {
            //definindo redirecionamento
            echo "<html>
                <body>
                    <form method='post' action='$CFG->rwww/visao/mensagem.php' id='formMensagem'>
                        <input name='tipoMsg' type='hidden' value='$this->tipo'>
                        <input name='mensagem' type='hidden' value=" . '"' . $this->mensagem . '"' . ">
                    </form>
                    <script type='text/javascript'>
                        document.getElementById('formMensagem').submit();
                    </script>
                </body>
            </html>";
        } else {
            //gerando script de retorno
            echo "<html>
                <body>
                    <script type='text/javascript'>
                        window.back(-1);
                    </script>
                </body>
            </html>";
        }
        exit;
    }

    private function addParamUrl($url, $param) {
        // buscando por interrogaçao
        $interrogacao = strpos($url, "?");
        if ($interrogacao === FALSE) {
            return "$url?$param";
        }
        return "$url&$param";
    }

    public static function getDsTipo($tipo) {
        return Mensagem::$vetTipo[$tipo];
    }

    public static function getClasse($tipo) {
        return Mensagem::$vetClasse[$tipo];
    }

}

?>
