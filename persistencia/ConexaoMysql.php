<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Conexao
 *
 * @author Estevão Costa
 */
global $CFG;
require_once $CFG->rpasta . "/util/Util.php";

class ConexaoMysql {

    private $host;
    private $usuario;
    private $senha;
    private $porta;
    private $nmBanco;
    private $conexao;
    public static $_CARACTER_INSERCAO_DEPENDENTE = "£";
    private static $DEBUG = FALSE;
    private static $MSG_PADRAO = "Falha ao acessar base de dados.<br/>";
    private static $SQL_ULTIMO_ID = "select last_insert_id() as id";

    public function __construct() {
        global $CFG;
        $this->host = $CFG->bdhost;
        $this->porta = $CFG->bdporta;
        $this->usuario = $CFG->bdusuario;
        $this->senha = $CFG->bdsenha;
        $this->nmBanco = $CFG->bdbanco;

        # Alterando Debug
        ConexaoMysql::$DEBUG = $CFG->ambiente == Util::$AMBIENTE_DESENVOLVIMENTO || $CFG->emTeste ? TRUE : FALSE;

        $this->conectarBanco();
    }

    private function conectarBanco() {

        //tentando conectar-se ao banco
        $this->conexao = mysqli_connect($this->host, $this->usuario, $this->senha, $this->nmBanco, $this->porta);

        // verificando criação da conexão
        if ($this->conexao === FALSE) {
            throw new Exception(ConexaoMysql::$MSG_PADRAO);
        }

        // setando charset
        if (!mysqli_set_charset($this->conexao, "utf8")) {
            throw new Exception(ConexaoMysql::_MSG_PADRAO() . (ConexaoMysql::$DEBUG ? mysqli_error($this->conexao) : ""));
        }

        //executando parâmetros de conexão
        $this->execSqlSemRetorno("SET NAMES 'utf8'");
        $this->execSqlSemRetorno('SET character_set_connection=utf8');
        $this->execSqlSemRetorno('SET character_set_client=utf8');
        $this->execSqlSemRetorno('SET character_set_results=utf8');
    }

    private function iniciarTransacao() {
        $ret = mysqli_query($this->conexao, "BEGIN");
        if ($ret == FALSE) {
            throw new Exception(ConexaoMysql::$MSG_PADRAO . (ConexaoMysql::$DEBUG ? ("Erro ao iniciar transação: " . mysqli_error($this->conexao) ) : ""));
        }
    }

    private function rollbackTransacao() {
        $ret = mysqli_query($this->conexao, "ROLLBACK");
        if ($ret == FALSE) {
            throw new Exception(ConexaoMysql::$MSG_PADRAO . (ConexaoMysql::$DEBUG ? ("Erro ao executar roolback em transação: " . mysqli_error($this->conexao)) : ""));
        }
    }

    private function commitTransacao() {
        $ret = mysqli_query($this->conexao, "COMMIT");
        if ($ret == FALSE) {
            //chamando rollback
            $this->rollbackTransacao();

            //disparando exceção
            throw new Exception(ConexaoMysql::$MSG_PADRAO . (ConexaoMysql::$DEBUG ? ("Erro ao executar commit em transação: " . mysqli_error($this->conexao)) : ""));
        }
    }

    /**
     * Retorna uma conexão mysqli
     * 
     * @return mysqli
     */
    public function getConexaoBD() {
        return $this->conexao;
    }

    /**
     * Essa funçao executa cada comando do array de comandos substituindo os caracteres
     * de dependencia de $cmdDependente pelo id do ultimo comando de inserçao. Por fim, 
     * e executado o comando dependente.
     * 
     * Note que o tamanho do array de comandos deve ser igual a quantidade de 
     * dependencias do comando dependente. 
     * 
     * @param string $cmdDependente
     * @param array $arrayComandos
     * @throws Exception
     */
    public function execTransacaoEmFilaDependente($cmdDependente, $arrayComandos) {
        try {

            //iniciar transação
            $this->iniciarTransacao();

            //loop nos comandos
            foreach ($arrayComandos as $comando) {
                if (Util::vazioNulo($comando)) {
                    // substituindo dependencia corrrespondente em $cmdDependente por 'NULL'
                    $cmdDependente = str_replace_once(ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, "NULL", $cmdDependente);
                    continue;
                }

//                print_r($comando); echo "</br>";echo "</br>";
                //executando comando
                $this->execSqlSemRetorno($comando);

                // pegando id
                $resp = $this->execSqlComRetorno(ConexaoMysql::$SQL_ULTIMO_ID);
                $ultId = $this->getResult("id", $resp);
//                print_r($ultId);echo "</br>";
                // substituindo dependencia corrrespondente em $cmdDependente
                $cmdDependente = str_replace_once(ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, $ultId, $cmdDependente);
            }

//            print_r($cmdDependente);echo "</br>";echo "</br>";
            //executando comando dependente
            $this->execSqlSemRetorno($cmdDependente);

            //executando commit
            $this->commitTransacao();
        } catch (Exception $e) {
            //chamando rollback
            $this->rollbackTransacao();

            // re-disparando exceção
            throw $e;
        }
    }

    /**
     * $cmdPrincipal deve ser o comando sql a ser executado primeiramente.
     * Será, então executado o comando para recuperar o id do elemento inserido.
     * A seguir, será feito um replace_all dos caracteres "_CARACTER_INSERCAO_DEPENDENTE", 
     * colocando o código recebido. 
     * 
     * 
     * @param string $cmdPrincipal
     * @param array $arrayComandos 
     * @return int - ID do comando dependente
     */
    public function execTransacaoDependente($cmdPrincipal, $arrayComandos) {
        try {

            //iniciar transação
            $this->iniciarTransacao();

            //executando comando inicial
            $this->execSqlSemRetorno($cmdPrincipal);

            //executando comando de recuperar último id
            $resp = $this->execSqlComRetorno(ConexaoMysql::$SQL_ULTIMO_ID);

            //salvando último id para retornar
            $ultId = $this->getResult("id", $resp);

            //loop nos comandos restantes
            foreach ($arrayComandos as $comando) {
                $comando = str_replace(ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, $ultId, $comando);
                $this->execSqlSemRetorno($comando);
            }

            //executando commit
            $this->commitTransacao();

            //retornando
            return $ultId;
        } catch (Exception $e) {
            //chamando rollback
            $this->rollbackTransacao();

            // re-disparando exceção
            throw $e;
        }
    }

    /**
     * Esta função recebe uma lista de comandos no qual o segundo é dependente do primeiro e assim por diante, ou seja,
     * Para três comandos da lista x, y e z, executa-se os passos a seguir:
     * 1 - Executa x
     * 2 - Recupera o ID de x
     * 3 - Substitui ID de x em y 
     * 4 - Executa y.
     * 5 - Recupera o ID de y
     * 6 - Substitui ID de y em z
     * 7 - Executa z
     * 
     * Se $retIdUltimaInsercao é true, entao essa funcao retorna o ultimo 
     * ID gerado por uma insercao com auto incremento.
     * 
     * Atenção ao usar o retorno do último ID: Certifique-se de que o último comando a ser executado seja a inserção
     * que deve ser recuperada.
     * 
     * @param array $arrayDependentes Array com comandos dependentes
     * @param boolean $retIdUltimaInsercao Flag que informa a necessidade de retonar o último ID inserido.
     * @param array $arrayOutrosCmds Array com outros comandos que precisam ser executados na mesma seção, sem regra de dependência e necessidade de 
     * Id de inserção.
     * @throws Exception
     */
    public function execTransacaoArrayDependente($arrayDependentes, $retIdUltimaInsercao = FALSE, $arrayOutrosCmds = NULL) {
        try {
            //iniciar transação
            $this->iniciarTransacao();

            //loop nos comandos restantes
            for ($i = 0; $i < count($arrayDependentes) - 1; $i++) {
                // executa comando principal
                $this->execSqlSemRetorno($arrayDependentes[$i]);

                //executando comando de recuperar último id
                $resp = $this->execSqlComRetorno(ConexaoMysql::$SQL_ULTIMO_ID);

                //salvando último id para retornar
                $ultId = $this->getResult("id", $resp);

                // substituindo dependência no próximo comando
                $arrayDependentes[$i + 1] = str_replace(ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, $ultId, $arrayDependentes[$i + 1]);
            }

            // executando último comando
            if (isset($i)) {
                $this->execSqlSemRetorno($arrayDependentes[$i]);
            }

            if ($retIdUltimaInsercao) {
                //executando comando de recuperar último id
                $resp = $this->execSqlComRetorno(ConexaoMysql::$SQL_ULTIMO_ID);

                //salvando último id para retornar
                $ultId = $this->getResult("id", $resp);
            }

            // outros comandos
            if ($arrayOutrosCmds != NULL) {
                foreach ($arrayOutrosCmds as $comando) {
                    $this->execSqlSemRetorno($comando);
                }
            }

            //executando commit
            $this->commitTransacao();

            if ($retIdUltimaInsercao) {
                return $ultId;
            }
        } catch (Exception $e) {
            //chamando rollback
            $this->rollbackTransacao();

            // re-disparando exceção
            throw $e;
        }
    }

    /**
     * Essa funçao procede da seguinte forma:
     * Cada comando do $arrayCmdsPrincipal e executado. Em seguida, um comando do 
     * array $arrayCmdsDep e executado realizando a substituiçao do caracter dependente.
     * Por ultimo, os outros comandos sao executados.
     * 
     * Note: $arrayCmdPrincipal deve ter o mesmo tamanho que $arrayCmdDep!
     * 
     *  
     * @param array $arrayCmdPrincipal - Array de Comando principal
     * @param array $arrayCmdDep - Array de Comando dependente
     * @param array $arrayOutrosCmds - Array de outros comandos
     * @return void
     * @throws Exception
     */
    public function execTransacoesDepsComComplemento($arrayCmdPrincipal, $arrayCmdDep, $arrayOutrosCmds) {
        try {

            //iniciar transação
            $this->iniciarTransacao();

            // loop nos comandos principais
            while (count($arrayCmdPrincipal)) {
                //executando comando inicial
                $this->execSqlSemRetorno(array_shift($arrayCmdPrincipal));

                //executando comando de recuperar último id
                $resp = $this->execSqlComRetorno(ConexaoMysql::$SQL_ULTIMO_ID);

                //salvando último id para manipular
                $ultId = $this->getResult("id", $resp);

                // trocando caracter dependente do elemento correspondente do array dependente
                $comando = str_replace(ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, $ultId, array_shift($arrayCmdDep));

                // executando comando dependente
                $this->execSqlSemRetorno($comando);
            }

            //loop nos comandos restantes
            foreach ($arrayOutrosCmds as $comando) {
                // executando
                $this->execSqlSemRetorno($comando);
            }

            //executando commit
            $this->commitTransacao();
        } catch (Exception $e) {
            //chamando rollback
            $this->rollbackTransacao();

            // re-disparando exceção
            throw $e;
        }
    }

    /**
     * Executa o comando principal e o array de comandos em uma transação sql
     * @param string $cmdPrincipal
     * @param array $arrayComandos
     */
    public function execTransacao($cmdPrincipal, $arrayComandos) {
        try {

            //iniciar transação
            $this->iniciarTransacao();

            //executando comando inicial
            $this->execSqlSemRetorno($cmdPrincipal);

            //loop nos comandos restantes
            foreach ($arrayComandos as $comando) {
                $this->execSqlSemRetorno($comando);
            }

            //executando commit
            $this->commitTransacao();
        } catch (Exception $e) {
            //chamando rollback
            $this->rollbackTransacao();

            // re-disparando exceção
            throw $e;
        }
    }

    /**
     * Executa cada comando do array. Se $retIdUltimaInsercao é true, entao essa funcao retorna o ultimo 
     * ID gerado por uma insercao com auto incremento.
     * 
     * Atenção ao usar o retorno do último ID: Certifique-se de que o último comando a ser executado seja a inserção
     * que deve ser recuperada.
     * 
     * @param array $arrayComandos
     * @param boolean $retIdUltimaInsercao Flag que informa a necessidade de retonar o último ID inserido.
     * @throws Exception
     */
    public function execTransacaoArray($arrayComandos, $retIdUltimaInsercao = FALSE) {
        try {

            //iniciar transação
            $this->iniciarTransacao();

            //loop nos comandos 
            foreach ($arrayComandos as $comando) {
                $this->execSqlSemRetorno($comando);
            }

            if ($retIdUltimaInsercao) {
                //executando comando de recuperar último id
                $resp = $this->execSqlComRetorno(ConexaoMysql::$SQL_ULTIMO_ID);

                //salvando último id para retornar
                $ultId = $this->getResult("id", $resp);
            }

            //executando commit
            $this->commitTransacao();

            if ($retIdUltimaInsercao) {
                return $ultId;
            }
        } catch (Exception $e) {
            //chamando rollback
            $this->rollbackTransacao();

            // re-disparando exceção
            throw $e;
        }
    }

    /**
     * Executa cada comando da matriz, sequencialmente, linha a linha.
     * @param matriz $matrizComandos
     * @throws Exception
     */
    public function execTransacaoMatriz($matrizComandos) {
        try {

            //iniciar transação
            $this->iniciarTransacao();

            //loop nos comandos 
            for ($i = 0; $i < count($matrizComandos); $i++) {
                for ($j = 0; $j < count($matrizComandos[$i]); $j++) {
//                    print_r($matrizComandos[$i][$j]);
//                    print_r("<br/>");
                    $this->execSqlSemRetorno($matrizComandos[$i][$j]);
                }
            }

            //executando commit
            $this->commitTransacao();
        } catch (Exception $e) {
            //chamando rollback
            $this->rollbackTransacao();

            // re-disparando exceção
            throw $e;
        }
    }

    /**
     * Essa função é focada na matriz dependente. A matriz dependente é uma array no qual cada linha contém um array com um comando 
     * principal, sem dependência, e um número indefinido de comandos dependentes. Então, o processamento ocorre da seguinte forma: 
     * Para cada linha da matriz é executado o comando principal, recuperando o ID de inserção (dependência). Em seguida, a dependência
     * é resolvida, e os comandos adicionais da linha são executados. 
     * 
     * Adicionalmente, é possível informar um array com comandos adicionais a serem executados antes ou depois do processamento da matriz.
     * 
     * Note: Essa função não verifica a validade dos parâmetros. Então, seja atencioso!
     * 
     * @param array $matrizDependente Matriz na qual em cada linha existe um array com comandos dependentes.
     * @param array $arraySqlAdd Array com comandos adicionais a serem executaos antes ou depois do processamento da matriz
     * @param boolean $execAddAntes Boolean que informa se os comandos adicionais devem ser executados antes. Por padrão, é TRUE.
     * @throws Exception
     */
    public function execTransacaoMatrizDependente($matrizDependente, $arraySqlAdd, $execAddAntes = TRUE) {
        try {
            //iniciar transação
            $this->iniciarTransacao();

            // execução de comandos adicionais antes
            if ($arraySqlAdd != NULL && $execAddAntes) {
                foreach ($arraySqlAdd as $cmd) {
                    $this->execSqlSemRetorno($cmd);
                }
            }

            // processamento da matriz
            foreach ($matrizDependente as $vetDep) {
                // separando e executando comando principal
                $cmdPrincipal = array_shift($vetDep);

                $this->execSqlSemRetorno($cmdPrincipal);

                //executando comando de recuperar último id
                $resp = $this->execSqlComRetorno(ConexaoMysql::$SQL_ULTIMO_ID);
                $id = $this->getResult("id", $resp);

                // iterando nos outros comandos
                foreach ($vetDep as $cmdDep) {
                    // substituindo dependência
                    $comando = str_replace(ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, $id, $cmdDep);

                    // executando
                    $this->execSqlSemRetorno($comando);
                }
            }

            // execução de comandos adicionais depois
            if ($arraySqlAdd != NULL && !$execAddAntes) {
                foreach ($arraySqlAdd as $cmd) {
                    $this->execSqlSemRetorno($cmd);
                }
            }



            //executando commit
            $this->commitTransacao();
        } catch (Exception $e) {
            //chamando rollback
            $this->rollbackTransacao();

            // re-disparando exceção
            throw $e;
        }
    }

    /**
     * Se $retIdUltimaInsercao e true, entao essa funcao retorna o ultimo 
     * ID gerado por uma insercao com auto incremento
     * @param string $comando
     * @param boolean $retIdUltimaInsercao
     * @param boolean $debugTemp Diz se é para aceitar debug para o comando
     * @throws Exception
     */
    public function execSqlSemRetorno($comando, $retIdUltimaInsercao = FALSE, $debugTemp = FALSE) {
        // nao e para pegar ultima insercao
        if (!$retIdUltimaInsercao) {
            $ret = mysqli_query($this->conexao, $comando);

            if ($ret == FALSE) {
                throw new Exception(ConexaoMysql::$MSG_PADRAO . (ConexaoMysql::$DEBUG || $debugTemp ? ("Comando: $comando Erro: " . mysqli_error($this->conexao) ) : ""));
            }
        } else {
            // iniciando processo, com uma transacao
            try {
                //iniciar transação
                $this->iniciarTransacao();

                //executando comando
                $ret = mysqli_query($this->conexao, $comando);

                if ($ret == FALSE) {
                    throw new Exception(ConexaoMysql::$MSG_PADRAO . (ConexaoMysql::$DEBUG || $debugTemp ? ("Comando: $comando Erro: " . mysqli_error($this->conexao) ) : ""));
                }

                //executando comando de recuperar último id
                $resp = $this->execSqlComRetorno(ConexaoMysql::$SQL_ULTIMO_ID);

                //salvando último id para retornar
                $ultId = $this->getResult("id", $resp);

                //executando commit
                $this->commitTransacao();

                //retornando
                return $ultId;
            } catch (Exception $e) {
                //chamando rollback
                $this->rollbackTransacao();

                // re-disparando exceção
                throw $e;
            }
        }
    }

    public function execSqlComRetorno($consulta) {
        $res = mysqli_query($this->conexao, $consulta);
        if ($res == FALSE) {

            throw new Exception(ConexaoMysql::$MSG_PADRAO . (ConexaoMysql::$DEBUG ? ("Consulta: $consulta Erro: " . mysqli_error($this->conexao) ) : ""));
        }
        return $res;
    }

    public static function getLinha($result_set) {
        $res = mysqli_fetch_assoc($result_set);
        return $res;
    }

    /**
     * ATENÇAO: So pode ser usado para recuperar um unico dado, pois a linha sera perdida
     * @param type $coluna
     * @param type $res
     * @return type
     */
    public static function getResult($coluna, $res) {
        $registro = ConexaoMysql::getLinha($res);
        return $registro[$coluna];
    }

    public static function getNumLinhas($result_set) {
        $num = mysqli_num_rows($result_set);
        return $num;
    }

}

?>
