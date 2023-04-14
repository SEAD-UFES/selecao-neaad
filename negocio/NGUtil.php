<?php

/*
 * Arquivo de apoio para as classes de negocio
 */

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/persistencia/ConexaoMysql.php";

/* Muito cuidado ao alterar a definicao de ID_SELECT_SELECIONE, pois muita coisa
  pode deixar de funcionar
 */

define("ID_SELECT_SELECIONE", '""');

define("DS_SELECT_SELECIONE", 'Selecione');
define("ID_SELECT_OUTRO", '-1');
define("DS_SELECT_OUTRO", 'Outro');
define("DS_SELECT_OUTROS", 'Outros');
define("DS_SELECT_OUTRA", 'Outra');
define("DS_SELECT_OUTRAS", 'Outras');
define("NULO", 'N');
define("FLAG_BD_SIM", 'S');
define("FLAG_BD_NAO", 'N');

class NGUtil {

    private static $conexaoMysql;
    private static $SITUACAO_ATIVO = 'A';
    private static $SITUACAO_INATIVO = 'I';
    public static $PULO_LINHA_HTML = "<br/>";
    public static $SEPARADOR_CSV = ";";
    private static $MSG_ERRO_UPLOAD_ARQUIVO = array(1 => "O arquivo no upload é maior do que o limite do PHP", 2 => 'O arquivo ultrapassa o limite de tamanho especificado no HTML', 3 => 'O upload do arquivo foi feito parcialmente', 4 => 'Não foi feito o upload do arquivo');
    public static $PREPOSICOES_NOME = array("de", "da", "do", "das", "dos");

    public static function getCabecalhoPadraoEmailEnviado() {
        return "Esta mensagem foi enviada pelo <b>Sistema de Seleção de Ensino a Distância da UFES</b>.<br/><br/>";
    }

    public static function getRodapePadraoEmailEnviado() {
        global $CFG;
        $servidor = $CFG->rwww;
        return "<br/><br/>Clique abaixo (ou copie e cole o endereço em seu navegador) para acessar o sistema:<br/>$servidor<br/><br/>";
    }

    public static function imprimeVetorDepuracao($vet) {
        $i = 1;
        foreach ($vet as $valor) {
            echo "l $i: ";
            print_r($valor);
            echo "<br/><br/>";
            $i++;
        }
    }

    public static function getDsSituacao($stSituacao) {
        if ($stSituacao == NGUtil::$SITUACAO_ATIVO) {
            return "Ativo";
        }
        if ($stSituacao == NGUtil::$SITUACAO_INATIVO) {
            return "Inativo";
        }
    }

    public static function getListaSituacaoDsSit() {
        return array(NGUtil::getSITUACAO_ATIVO() => NGUtil::getDsSituacao(NGUtil::$SITUACAO_ATIVO),
            NGUtil::getSITUACAO_INATIVO() => NGUtil::getDsSituacao(NGUtil::$SITUACAO_INATIVO));
    }

    public static function getSITUACAO_ATIVO() {
        return self::$SITUACAO_ATIVO;
    }

    public static function getSITUACAO_INATIVO() {
        return self::$SITUACAO_INATIVO;
    }

    public static function getDsSimNao($flagBD, $completaVazio = FALSE) {
        if (Util::vazioNulo($flagBD)) {
            return $completaVazio ? Util::$STR_CAMPO_VAZIO : "";
        }
        if ($flagBD == FLAG_BD_NAO) {
            return "Não";
        }
        if ($flagBD == FLAG_BD_SIM) {
            return "Sim";
        }
    }

    public static function mapeamentoSimNao($flagBooleana) {
        if (Util::vazioNulo($flagBooleana)) {
            return "";
        }
        return $flagBooleana === TRUE || $flagBooleana === 'true' ? FLAG_BD_SIM : FLAG_BD_NAO;
    }

    public static function mapeamentoBooleano($flagSimNao) {
        if (Util::vazioNulo($flagSimNao)) {
            return FALSE;
        }
        return $flagSimNao == FLAG_BD_SIM;
    }

    public static function getFlagSim() {
        return FLAG_BD_SIM;
    }

    public static function getFlagNao() {
        return FLAG_BD_NAO;
    }

    public static function getPrimeiroNome($nomeCompleto) {
        if (Util::vazioNulo($nomeCompleto)) {
            return NULL;
        }

        // recuperando primeiro nome
        $temp = explode(" ", $nomeCompleto);
        return $temp[0];
    }

    /**
     * 
     * @param string $dsEmail
     * @return string Parte do email a ser visualizada.
     */
    public static function parteVisivelEmail($dsEmail) {
        return substr($dsEmail, 0, 4) . "..." . substr($dsEmail, strpos($dsEmail, "@"));
    }

    public static function arq_verificaSucessoUpload($nmArq) {
        // questao de seguranca
        if (!is_uploaded_file($_FILES[$nmArq]['tmp_name'])) {
            throw new NegocioException("Arquivo enviado incorretamente.");
        }

        // caso de ter dado erro
        if (!$_FILES[$nmArq]['error'] === UPLOAD_ERR_OK) {
            // lançando excecao
            throw new NegocioException(self::$MSG_ERRO_UPLOAD_ARQUIVO[$_FILES[$nmArq]['error']]);
        }
    }

    public static function arq_copiarArquivoServidor($arqTmp, $novoArq) {
        // movendo e verificando erros
        if (!move_uploaded_file($arqTmp, $novoArq)) {
            throw new NegocioException("Não foi possível armazenar o arquivo no servidor.");
        }
    }

    public static function arq_excluirArquivoServidor($arquivo) {
        if (is_file($arquivo) && !unlink($arquivo)) {
            throw new NegocioException("Erro ao excluir arquivo do Servidor");
        }
    }

    public static function formataDecimal($Nota) {
        return money_format("%i", $Nota);
    }

    public static function calculaEquacao($equacao) {
        // Remove whitespaces
        $equacao = preg_replace('/\s+/', '', $equacao);
//        echo "$equacao\n";

        if (NGUtil::validaEquacao($equacao)) {
            $equacao = preg_replace('!pi|π!', 'pi()', $equacao); // Replace pi with pi function
//            echo "$equacao\n";
            eval('$result = ' . $equacao . ';');
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * 
     * @param string $formula
     * @param boolean $versaoEstendida Diz se é permitido funções estendidas do PHP. Padrão: False
     * 
     * Funções estendidas:
     *  - gmp_fact
     * 
     * @return boolean
     */
    public static function validaEquacao($formula, $versaoEstendida = FALSE) {
        if (empty($formula)) {
            return false;
        }

        $formula = preg_replace('/\s+/', '', $formula);

        $formula = str_split($formula);
        $errors = false;
        $foundComma = false;
        $numParentesi = 0;
        for ($i = 0; $i < count($formula); $i++) {

            // Supported chars
            if (!is_numeric($formula[$i]) && $formula[$i] != '+' && $formula[$i] != '-' && $formula[$i] != '*' && $formula[$i] != '/' && $formula[$i] != '(' && $formula[$i] != ')' && $formula[$i] != ',' && $formula[$i] != '^' && $formula[$i] != 'a' && $formula[$i] != 'c' && $formula[$i] != 'e' && $formula[$i] != 'g' && $formula[$i] != 'i' && $formula[$i] != 'l' && $formula[$i] != 'r' && $formula[$i] != 's' && $formula[$i] != 't'
            ) {
                $errors = true;
                break;
            }


            // Verify numbers of brackets
            if ($formula[$i] == '(') {
                $numParentesi++;
            }
            if ($formula[$i] == ')') {
                $numParentesi--;
            }


            if ($formula[$i] === ')') {                                                         // (
                if (isset($formula[$i + 1]) && $formula[$i + 1] != '+' && $formula[$i + 1] != '-' && $formula[$i + 1] != '*' && $formula[$i + 1] != '/' && $formula[$i + 1] != ')' && $formula[$i + 1] != '^'
                ) {
                    $errors = true;
                    break;
                }
                continue;
            }


            if (is_numeric($formula[$i])                                                     // Numbers
            ) {
                if (isset($formula[$i + 1]) && $formula[$i + 1] != '+' && $formula[$i + 1] != '-' && $formula[$i + 1] != '*' && $formula[$i + 1] != '/' && $formula[$i + 1] != ')' && $formula[$i + 1] != '^' && $formula[$i + 1] != ',' && !is_numeric($formula[$i + 1])
                ) {
                    $errors = true;
                    break;
                }
                continue;
            }


            if ($formula[$i] == '('                               // '('
                    || $formula[$i] == '+'                               // '+'
                    || $formula[$i] == '*'                               // '*'
                    || $formula[$i] == '/'                               // '/'
                    || $formula[$i] == '^'                               // '^'
            ) {
                $foundComma = false;
                if ((
                        isset($formula[$i + 1]) && (
                        $formula[$i + 1] == '+'                             // a++b not allowed
                        //      ||      $formula[$i+1]=='-'                             // a+-b allowed
                        || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ',' || $formula[$i + 1] == ')'
                        )
                        ) || ($i + 1 == count($formula))
                ) {
                    $errors = true;
                    break;
                }
                if ($formula[$i] == '+' && isset($formula[$i + 1]) && isset($formula[$i + 2]) && $formula[$i + 1] == '-' && $formula[$i + 2] == '+') {        // a+-+b not allowed
                    $errors = true;
                    break;
                }
                continue;
            }
            if ($formula[$i] == '-'                               // '-'
            ) {
                $foundComma = false;
                if ((
                        isset($formula[$i + 1]) && (
                        //      $formula[$i+1]=='+' ||  // a-+b allowed
                        $formula[$i + 1] == '-'             // a--b not allowed
                        || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ',' || $formula[$i + 1] == ')'
                        )
                        ) || ($i + 1 == count($formula))
                ) {
                    $errors = true;
                    break;
                }
                if (isset($formula[$i + 1]) && isset($formula[$i + 2]) && $formula[$i + 1] == '+' && $formula[$i + 2] == '-') {             // a-+-b not allowed
                    $errors = true;
                    break;
                }
                continue;
            }


            if ($formula[$i] == ','                               // ','
            ) {
                if ($foundComma) {                                // if i matched a comma
                    $errors = true;
                    break;
                }
                $foundComma = true;
                if (isset($formula[$i + 1]) && !(
                        is_numeric($formula[$i + 1])
                        )
                ) {
                    $errors = true;
                    break;
                }
                continue;
            }


            // MATH FUNCTIONS
            // abs, atan
            if ($formula[$i] == 'a') {
                if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3])) {
                    $errors = true;
                    break;
                } else {
                    if (!(
                            ($formula[$i + 1] == 'b' && $formula[$i + 2] == 's' && $formula[$i + 3] == '(') || (isset($formula[$i + 4]) && ($formula[$i + 1] == 't' && $formula[$i + 2] == 'a' && $formula[$i + 3] == 'n' && $formula[$i + 4] == '('))
                            )
                    ) {
                        $errors = true;
                        break;
                    } else {
                        $numParentesi++;
                        if ($formula[$i + 1] == 'b' && $formula[$i + 2] == 's' && $formula[$i + 3] == '(') {
                            $i = $i + 3;
                        } else {
                            $i = $i + 4;
                        }
                        if (!isset($formula[$i + 1]) || (
                                isset($formula[$i + 1]) && (
                                $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                )
                                )
                        ) {
                            $errors = true;
                            break;
                        }
                    }
                }
                continue;
            }


            // ceil, cot, cos
            if ($formula[$i] == 'c') {
                if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3])) {
                    $errors = true;
                    break;
                } else {
                    if (!(
                            ($formula[$i + 1] == 'o' && $formula[$i + 2] == 't' && $formula[$i + 3] == '(') || ($formula[$i + 1] == 'o' && $formula[$i + 2] == 's' && $formula[$i + 3] == '(') || (isset($formula[$i + 4]) && ($formula[$i + 1] == 'e' && $formula[$i + 2] == 'i' && $formula[$i + 3] == 'l' && $formula[$i + 4] == '('))
                            )
                    ) {
                        $errors = true;
                        break;
                    } else {
                        $numParentesi++;
                        if (
                                $formula[$i + 1] == 'o' && $formula[$i + 2] == 't' && $formula[$i + 3] == '(' || $formula[$i + 1] == 'o' && $formula[$i + 2] == 's' && $formula[$i + 3] == '('
                        ) {
                            $i = $i + 3;
                        } else {
                            $i = $i + 4;
                        }
                        if (!isset($formula[$i + 1]) || (
                                isset($formula[$i + 1]) && (
                                $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                )
                                )
                        ) {
                            $errors = true;
                            break;
                        }
                    }
                }
                continue;
            }
            // exp
            if ($formula[$i] == 'e') {
                if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3])) {
                    $errors = true;
                    break;
                } else {
                    if (!(
                            ($formula[$i + 1] == 'x' && $formula[$i + 2] == 'p' && $formula[$i + 3] == '(')
                            )
                    ) {
                        $errors = true;
                        break;
                    } else {
                        $numParentesi++;
                        $i = $i + 3;
                        if (!isset($formula[$i + 1]) || (
                                isset($formula[$i + 1]) && (
                                $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                )
                                )
                        ) {
                            $errors = true;
                            break;
                        }
                    }
                }
                continue;
            }
            // intval
            if ($formula[$i] == 'i') {
                if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3]) || !isset($formula[$i + 4]) || !isset($formula[$i + 5]) || !isset($formula[$i + 6])) {
                    $errors = true;
                    break;
                } else {
                    if (!(
                            ($formula[$i + 1] == 'n' && $formula[$i + 2] == 't' && $formula[$i + 3] == 'v' && $formula[$i + 4] == 'a' && $formula[$i + 5] == 'l' && $formula[$i + 6] == '(')
                            )
                    ) {
                        $errors = true;
                        break;
                    } else {
                        $numParentesi++;
                        $i = $i + 6;
                        if (!isset($formula[$i + 1]) || (
                                isset($formula[$i + 1]) && (
                                $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                )
                                )
                        ) {
                            $errors = true;
                            break;
                        }
                    }
                }
                continue;
            }
            // sin, sqrt
            if ($formula[$i] == 's') {
                if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3])) {
                    $errors = true;
                    break;
                } else {
                    if (!(
                            ($formula[$i + 1] == 'i' && $formula[$i + 2] == 'n' && $formula[$i + 3] == '(') || (isset($formula[$i + 4]) && ($formula[$i + 1] == 'q' && $formula[$i + 2] == 'r' && $formula[$i + 3] == 't' && $formula[$i + 4] == '('))
                            )
                    ) {
                        $errors = true;
                        break;
                    } else {
                        $numParentesi++;
                        if ($formula[$i + 1] == 'i' && $formula[$i + 2] == 'n' && $formula[$i + 3] == '(') {
                            $i = $i + 3;
                        } else {
                            $i = $i + 4;
                        }
                        if (!isset($formula[$i + 1]) || (
                                isset($formula[$i + 1]) && (
                                $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                )
                                )
                        ) {
                            $errors = true;
                            break;
                        }
                    }
                }
                continue;
            }
            // tan
            if ($formula[$i] == 't') {
                if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3])) {
                    $errors = true;
                    break;
                } else {
                    if (!(
                            ($formula[$i + 1] == 'a' && $formula[$i + 2] == 'n' && $formula[$i + 3] == '(')
                            )
                    ) {
                        $errors = true;
                        break;
                    } else {
                        $numParentesi++;
                        $i = $i + 3;
                        if (!isset($formula[$i + 1]) || (
                                isset($formula[$i + 1]) && (
                                $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                )
                                )
                        ) {
                            $errors = true;
                            break;
                        }
                    }
                }
                continue;
            }
            // log, log10
            if ($formula[$i] == 'l') {
                if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3])) {
                    $errors = true;
                    break;
                } else {
                    if (!(
                            ($formula[$i + 1] == 'o' && $formula[$i + 2] == 'g' && $formula[$i + 3] == '(') || (isset($formula[$i + 4]) && isset($formula[$i + 5]) && ($formula[$i + 1] == 'o' && $formula[$i + 2] == 'g' && $formula[$i + 3] == '1' && $formula[$i + 4] == '0' && $formula[$i + 5] == '('))
                            )
                    ) {
                        $errors = true;
                        break;
                    } else {
                        $numParentesi++;
                        if ($formula[$i + 1] == 'o' && $formula[$i + 2] == 'g' && $formula[$i + 3] == '(') {
                            $i = $i + 3;
                        } else {
                            $i = $i + 5;
                        }
                        if (!isset($formula[$i + 1]) || (
                                isset($formula[$i + 1]) && (
                                $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                )
                                )
                        ) {
                            $errors = true;
                            break;
                        }
                    }
                }
                continue;
            }
            // rand, round
            if ($formula[$i] == 'r') {
                if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3]) || !isset($formula[$i + 4])) {
                    $errors = true;
                    break;
                } else {
                    if (!(
                            ($formula[$i + 1] == 'a' && $formula[$i + 2] == 'n' && $formula[$i + 3] == 'd' && $formula[$i + 4] == '(') || (isset($formula[$i + 5]) && ($formula[$i + 1] == 'o' && $formula[$i + 2] == 'u' && $formula[$i + 3] == 'n' && $formula[$i + 4] == 'd' && $formula[$i + 5] == '('))
                            )
                    ) {
                        $errors = true;
                        break;
                    } else {
                        $numParentesi++;
                        if ($formula[$i + 1] == 'a' && $formula[$i + 2] == 'n' && $formula[$i + 3] == 'd' && $formula[$i + 4] == '(') {
                            $i = $i + 4;
                        } else {
                            $i = $i + 5;
                        }
                        if (!isset($formula[$i + 1]) || (
                                isset($formula[$i + 1]) && (
                                $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                )
                                )
                        ) {
                            $errors = true;
                            break;
                        }
                    }
                }
                continue;
            }

            // VERSAO ESTENDIDA
            if ($versaoEstendida) {
                // gmp_fact
                if ($formula[$i] == 'g') {
                    if (!isset($formula[$i + 1]) || !isset($formula[$i + 2]) || !isset($formula[$i + 3]) || !isset($formula[$i + 4]) || !isset($formula[$i + 5]) || !isset($formula[$i + 6]) || !isset($formula[$i + 7]) || !isset($formula[$i + 8])) {
                        $errors = true;
                        break;
                    } else {
                        if (!(
                                ($formula[$i + 1] == 'm' && $formula[$i + 2] == 'p' && $formula[$i + 3] == '_' && $formula[$i + 4] == 'f' && $formula[$i + 5] == 'a' && $formula[$i + 6] == 'c' && $formula[$i + 7] == 't' && $formula[$i + 8] == '(')
                                )
                        ) {
                            $errors = true;
                            break;
                        } else {
                            $numParentesi++;
                            $i = $i + 8;
                            if (!isset($formula[$i + 1]) || (
                                    isset($formula[$i + 1]) && (
                                    $formula[$i + 1] == '+' || $formula[$i + 1] == '*' || $formula[$i + 1] == '/' || $formula[$i + 1] == '^' || $formula[$i + 1] == ')' || $formula[$i + 1] == ','
                                    )
                                    )
                            ) {
                                $errors = true;
                                break;
                            }
                        }
                    }
                    continue;
                }
            }
        }

        if ($numParentesi != 0) {
            $errors = true;
        }

        return !$errors;
    }

    /**
     * Retorna uma conexao valida para o banco de dados
     * @return ConexaoMysql
     */
    public static function getConexao() {
        if (NGUtil::$conexaoMysql == NULL) {
            NGUtil::$conexaoMysql = new ConexaoMysql();
        }
        return NGUtil::$conexaoMysql;
    }

    /**
     * Esta função trata o campo string para ser enviado ao BD com segurança. 
     * 
     * Se $campo for nulo, é automaticamente tratado, retonando o comando NULL. 
     * 
     * Nota: Após usar esta função, não é necessário adicionar aspas à string retornada.
     * 
     * @param string $campo
     */
    public static function trataCampoStrParaBD($campo) {
        if (Util::vazioNulo($campo)) {
            return "NULL";
        } else {
            return "'" . mysqli_real_escape_string(self::getConexao()->getConexaoBD(), $campo) . "'";
        }
    }

    /**
     * Esta função trata o campo int para ser enviado ao BD com segurança. 
     * 
     * Se $campo for nulo, é automaticamente tratado, retonando o comando NULL. 
     * Esta função também verifica se $campo é numérico, disparando uma exceção em caso negativo.
     * 
     * Nota: Após usar esta função, não é necessário adicionar aspas à string retornada.
     * 
     * @param string $campo
     */
    public static function trataCampoIntParaBD($campo) {
        if (Util::vazioNulo($campo)) {
            return "NULL";
        } else {
            if (!is_numeric($campo)) {
                throw new NegocioException("Valor não númerico onde só é permitido valor numérico.");
            }
            return "'" . mysqli_real_escape_string(self::getConexao()->getConexaoBD(), $campo) . "'";
        }
    }

    public static function enviaStrComoArquivoCSV($string, $nmArquivo) {
        //preparando cabeçalho e enviando dados
        header('Content-Encoding: UTF-8');
        header("Content-type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=$nmArquivo");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header("Content-Length: " . strlen($string));
        ob_clean();
        print $string;
    }

}

function str_capitalize_forcado($str, $a_char = array("'", "-", " ")) {
    //$str contains the complete raw name string
    //$a_char is an array containing the characters we use as separators for capitalization. If you don't pass anything, there are three in there as default.
    $string = strtolower($str);
    foreach ($a_char as $temp) {
        $pos = strpos($string, $temp);
        if ($pos) {
            //we are in the loop because we found one of the special characters in the array, so lets split it up into chunks and capitalize each one.
            $mend = '';
            $a_split = explode($temp, $string);
            foreach ($a_split as $temp2) {
                //capitalize each portion of the string which was separated at a special character
                if (in_array($temp2, NGUtil::$PREPOSICOES_NOME)) {
                    $mend .= strtolower($temp2) . $temp;
                } else {
                    $mend .= ucfirst($temp2) . $temp;
                }
            }
            $string = substr($mend, 0, -1);
        }
    }
    return ucfirst($string);
}

function dadosPost($url, $data, $optional_headers = null) {
    $params = array('http' => array
            (
            'method' => 'POST',
            'content' => http_build_query($data, "", "&")
    ));
    if ($optional_headers !== null):
        $params['http']['header'] = $optional_headers;
    endif;
    $ctx = stream_context_create($params);
    $fp = @fopen($url, 'rb', false, $ctx);
    if (!$fp):
        throw new Exception("Problema com $url, $php_errormsg");
    endif;
    $response = @stream_get_contents($fp);
    if ($response === false):
        throw new Exception("Problema ao ler dados de $url, $php_errormsg");
    endif;
    return $response;
}

/**
 * Retorna string com impressao de array estilo javascript ['x1', 'x2', ...]
 * 
 * @param array $array Vetor a ser convertido
 * @return string
 */
function strArrayJavaScript($array) {
    $ret = "[";
    for ($i = 0; $i < count($array); $i++) {
        $ret .= "'" . $array[$i] . "'";
        if ($i < count($array) - 1) {
            $ret .= ",";
        }
    }
    $ret .= "]";
    return $ret;
}

function gerarListaAno($anoInicial = 1900) {
    $anoAtual = dt_getDataEmStr("Y");
    $listaRet = array();
    while ($anoAtual >= $anoInicial) {
        $listaRet[$anoAtual] = $anoAtual;
        $anoAtual--;
    }
    return $listaRet;
}

/**
 * Adiciona um conteudo a uma string, observando as regras de adiçao de virgula
 * 
 * Retorna a string acrescido de $adicao
 * @param string $string
 * @param string $adicao
 * @param boolean $espaco Informa se é para adicionar espaço ou não após a vírgula. Por padrão é sim.
 * @return string
 */
function adicionaConteudoVirgula($string, $adicao, $espaco = TRUE) {
    if (Util::vazioNulo($string)) {
        return $adicao;
    }
    return $string . "," . ($espaco ? " " : "") . $adicao;
}

function converteStrParaInt($str) {
    return (int) str_replace_once("'", "", $str);
}

#   ---------------------- MANIPULAÇÃO DE DATAS --------------------------------------

/**
 * Esta função retorna uma string representando a data especificada em $timestamp
 * no formato $formato
 * 
 * @param string $formato
 * @param int $timestamp Se Nulo, é utilizado a data atual
 * 
 * @return string - Data no formato especificado em $formato.
 */
function dt_getDataEmStr($formato, $timestamp = NULL) {
    date_default_timezone_set('America/Sao_Paulo');
    if ($timestamp == NULL) {
        $timestamp = time();
    }
    return date($formato, $timestamp);
}

/**
 * Esta função retorna o timestamp da data representada em $dataStrUS
 * 
 * @param string $dataStrUS - Data no formato: yyyy-mm-dd
 * @return timestamp
 */
function dt_getTimestampDtUS($dataStrUS = NULL) {
    date_default_timezone_set('America/Sao_Paulo');
    if ($dataStrUS == NULL) {
        $dataStrUS = dt_getDataEmStr('Y-m-d');
    }
    return strtotime($dataStrUS);
}

/**
 * Esta função retorna o timestamp da data representada em $dataStrBrasil
 * 
 * @param string $dataStrBrasil - Data no formato: dd/mm/yyyy
 * @return timestamp
 */
function dt_getTimestampDtBR($dataStrBrasil = NULL) {
    date_default_timezone_set('America/Sao_Paulo');
    if ($dataStrBrasil == NULL) {
        $dataUS = dt_getDataEmStr('Y-m-d');
    } else {
        $varData = explode("/", $dataStrBrasil);
        $dataUS = "$varData[2]-$varData[1]-$varData[0]";
    }
    return strtotime($dataUS);
}

/**
 * Verifica se uma data pertence a um determinado intervalo, inclusive.
 * 
 * @param timestamp $dt
 * @param timestamp $dtInicio Data inicial do intervalo
 * @param timestamp $dtFim Data final do intervalo
 * 
 * @return boolean
 */
function dt_dataPertenceIntervalo($dt, $dtInicio, $dtFim) {
    return $dt >= $dtInicio && $dt <= $dtFim;
}

/**
 * Verifica se uma data e maior ou igual a uma data base.
 * 
 * @param timestamp $dt
 * @param timestamp $dataBase
 * 
 * @return boolean
 */
function dt_dataMaiorIgual($dt, $dataBase) {
    return $dt >= $dataBase;
}

/**
 * Verifica se uma data e maior que uma data base.
 * 
 * @param timestamp $dt
 * @param timestamp $dataBase
 * 
 * @return boolean
 */
function dt_dataMaior($dt, $dataBase) {
    return $dt > $dataBase;
}

/**
 * Verifica se uma data e menor que uma data base.
 * 
 * @param timestamp $dt
 * @param timestamp $dataBase
 * 
 * @return boolean
 */
function dt_dataMenor($dt, $dataBase) {
    return $dt < $dataBase;
}

/**
 * Verifica se uma data e menor ou igual a uma data base.
 * 
 * @param timestamp $dt
 * @param timestamp $dataBase
 * 
 * @return boolean
 */
function dt_dataMenorIgual($dt, $dataBase) {
    return $dt <= $dataBase;
}

/**
 * 
 * @param string $dataStr Data no formato dd/mm/yyyy
 * @return string
 */
function dt_dataStrParaMysql($dataStr) {
    if (Util::vazioNulo($dataStr)) {
        return "NULL";
    }
    return "STR_TO_DATE('$dataStr','%d/%m/%Y')";
}

/**
 * 
 * @param string $dataHoraStr Data no formato dd/mm/yyyy HH:mm:ss
 * @return string
 */
function dt_dataHoraStrParaMysql($dataHoraStr) {
    if (Util::vazioNulo($dataHoraStr)) {
        return "NULL";
    }
    return "STR_TO_DATE('$dataHoraStr','%d/%m/%Y %T')";
}

/**
 * Esta função adiciona à $dataStrBR os dias, meses e/ou anos informados.
 * 
 * @param string $dataStrBR string no formato dd/mm/yyyy com a data base a ser somada
 * @param int $diaAdd
 * @param int $mesAdd
 * @param int $anoAdd
 * 
 * @return string string representando a nova data, ou seja, a soma de $dataStrBR com os dias, meses ou anos informados.
 */
function dt_somarData($dataStrBR, $diaAdd = 0, $mesAdd = 0, $anoAdd = 0) {
    $dt = dt_getTimestampDtBR($dataStrBR);
    $novaData = dt_getDataEmStr("d/m/Y", mktime(0, 0, 0, dt_getDataEmStr('m', $dt) + $mesAdd, dt_getDataEmStr('d', $dt) + $diaAdd, dt_getDataEmStr('Y', $dt) + $anoAdd));
    return $novaData;
}

#   ---------------------- FIM MANIPULAÇÃO DE DATAS --------------------------------------  

function mensagemBoasVindas() {
//formato 0 a 23
    $hora = dt_getDataEmStr("G");
    if ($hora < 12) {
        return "Bom dia";
    }
    if ($hora < 18) {
        return "Boa tarde";
    }
    return "Boa noite";
}

function removerAcentos($str, $enc = 'UTF-8') {
    $acentos = array(
        'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
        'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;/',
        'C' => '/&Ccedil;/',
        'c' => '/&ccedil;/',
        'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
        'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
        'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
        'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
        'N' => '/&Ntilde;/',
        'n' => '/&ntilde;/',
        'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
        'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;/',
        'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
        'u' => '/&ugrave;|&uacute;|&ucirc;|&uuml;/',
        'Y' => '/&Yacute;/',
        'y' => '/&yacute;|&yuml;/',
        'a.' => '/&ordf;/',
        'o.' => '/&ordm;/'
    );
    return preg_replace($acentos, array_keys($acentos), htmlentities($str, ENT_NOQUOTES, $enc));
}

function adicionarMascara($mascara, $string) {
    if (Util::vazioNulo($string)) {
        return "";
    }
    $string = str_replace(" ", "", $string);
    for ($i = 0; $i < strlen($string); $i++) {
        $mascara[strpos($mascara, "#")] = $string[$i];
    }
    return $mascara;
}

function removerMascara($mascara, $string) {
    $string = str_replace(" ", "", $string);
    if (strlen($string) < strlen($mascara)) {
        return $string;
    }
    $stringRet = "";
    for ($i = 0; $i < strlen($mascara); $i++) {
        $stringRet .= $mascara[$i] == "#" ? $string[$i] : "";
    }
    return $stringRet;
}

function str_replace_once($search, $replace, $subject) {
    if (($pos = strpos($subject, $search)) !== false) {
        $ret = substr($subject, 0, $pos) . $replace . substr($subject, $pos + strlen($search));
    } else {
        $ret = $subject;
    }
    return($ret);
}

function arrayParaStr($array) {
    $tam = count($array);
    $ret = "";
    $i = 0;
    foreach ($array as $elem) {
        $ret .= $elem;
        $i++;
        if ($i != $tam) {
            if ($i == $tam - 1) {
                $ret .= " e ";
            } else {
                $ret .= ", ";
            }
        }
    }
    return $ret;
}

/**
 * 
 * @global stdclass $CFG
 * @param string $destinatario
 * @param string $assunto
 * @param string $mensagem
 * @param string $responderPara Email para onde deve ser enviado a resposta
 * @param boolean $forcarEnvio Diz se é para forçar o envio de email, mesmo no ambiente de desenvolvimento
 * @return boolean Informa se o email foi enviado corretamente
 */
function enviaEmail($destinatario, $assunto, $mensagem, $responderPara = NULL, $forcarEnvio = FALSE) {
    global $CFG;

    // adicionando tag padrão ao assunto:
    $assunto = "SEAD/UFES - " . $assunto;

    // adicionando cabeçalho e rodapé à mensagem
    $mensagem = NGUtil::getCabecalhoPadraoEmailEnviado() . $mensagem . NGUtil::getRodapePadraoEmailEnviado();

    $cabResponderPara = Util::vazioNulo($responderPara) ? "" : PHP_EOL . "Reply-To: <$responderPara>";
    $cabecalho = "From: Seleção EAD <nao-responda@ufes.br> $cabResponderPara " . PHP_EOL . "MIME-Version: 1.0" . PHP_EOL . "Content-type: text/html;charset=UTF-8" . PHP_EOL;
    if ($forcarEnvio || $CFG->ambiente == Util::$AMBIENTE_PRODUCAO) {
        return mail($destinatario, $assunto, $mensagem, $cabecalho);
    } else {
        new Mensagem($mensagem, Mensagem::$MENSAGEM_INFORMACAO);
    }
}

/**
 * Retorna uma String com o conteúdo da busca em formato CSV.
 * O cabeçalho é feito conforme o nome dos campos extraídos da consulta.
 * @param matriz $result_set 
 */
function consultaParaCSV($result_set) {

    $separador = NGUtil::$SEPARADOR_CSV;
    $numLinha = ConexaoMysql::getNumLinhas($result_set);
    if ($numLinha == 0) {
        //string vazia
        return "";
    }

    $ret = "";

    //recupera 1º linha para montar cabeçalho
    $linha = ConexaoMysql::getLinha($result_set);

    //lista de colunas
    $chaves = array_keys($linha);

    //montando cabeçalho
    foreach ($chaves as $value) {
        $ret .= mb_strtoupper($value) . $separador;
    }
    $ret .= PHP_EOL;

    //1º linha
    foreach ($chaves as $value) {
        $ret .= trataLinhaCSV($linha[$value]) . $separador;
    }
    $ret .= PHP_EOL;

    //demais linhas
    for ($i = 1; $i < $numLinha; $i++) {
        //recupera linha
        $linha = ConexaoMysql::getLinha($result_set);
        foreach ($chaves as $value) {
            $ret .= trataLinhaCSV($linha[$value]) . $separador;
        }
        $ret .= PHP_EOL;
    }
    return $ret;
}

function trataLinhaCSV($str) {
    // removendo pulos de linha e tabs desnecessarios
    $str = preg_replace("/\\r\\n|\\n|\\t/", " ", $str);

    // removendo separador
    $str = str_replace(NGUtil::$SEPARADOR_CSV, ".", $str);

    return $str;
}

/**
 * Funcao que converte uma matriz em arquivo csv. 
 * 
 * ESTA FUNCAO NAO FAZ VALIDACAO DOS PARAMETROS. USE COM CUIDADO.
 * 
 * @param array $cabecalho - Array com cabecalho dos dados
 * @param matriz $matriz - Array do tipo (chave => array(col1, col2, ..., coln))
 * @return string
 */
function matrizParaCSV($cabecalho, $matriz) {

    $separador = NGUtil::$SEPARADOR_CSV;

    $ret = "";

    //montando cabeçalho
    foreach ($cabecalho as $value) {
        $ret .= mb_strtoupper(str_replace($separador, " ", $value)) . $separador;
    }
    $ret .= PHP_EOL;

    // imprimindo matriz
    foreach (array_keys($matriz) as $chave) {
        //recupera linha
        $linha = $matriz[$chave];
        foreach ($linha as $value) {
            $ret .= trataLinhaCSV($value) . $separador;
        }
        $ret .= PHP_EOL;
    }
    return $ret;
}

function startsWith($haystack, $needle) {
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle) {
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

?>
