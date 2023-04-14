<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once($CFG->rpasta . "/negocio/Usuario.php");
require_once($CFG->rpasta . "/negocio/IdentificacaoCandidato.php");
require_once($CFG->rpasta . "/negocio/ContatoCandidato.php");

// Chave específica para testes específicos
if (isset($_GET['aaa']) && $_GET['aaa'] == "testeLdap") {
    echo "validarLogin<br/>";
    echo ldap_gera_senha_aleatoria("usuValidar");
    echo "<br/>";
    print_r(ldap_validar_login("", ""));
    exit();
}

class LDAPUfes {

    public static $ERR_LDAP_SERVIDOR = 1;
    public static $ERR_LDAP_USUARIO_BUSCA = 2;
    public static $ERR_LDAP_DADOS_INCONSISTENTES = 3;

    public static function getMsgErroLdap($codErro, $msgComp = NULL) {
        if ($codErro == self::$ERR_LDAP_SERVIDOR) {
            return "No momento o servidor de autenticação da UFES não está funcionando. Tente mais tarde.";
        }

        if ($codErro == self::$ERR_LDAP_USUARIO_BUSCA) {
            return "Erro na configuração LDAP do sistema. Por favor, informe este erro ao administrador.";
        }

        if ($codErro == self::$ERR_LDAP_DADOS_INCONSISTENTES) {
            return "Desculpe. Você não pode acessar este sistema porque seu usuário possui inconsistências no $msgComp. Por favor, procure o NTI para atualizar seus dados.";
        }
    }

}

/**
 * Atenção: Essa função trata os casos especiais de validação, executando a sincronia com o banco de dados.
 * 
 *  
 * @param string $login - Login do usuário LDAP a pesquisar
 * @param string $senha - Senha do usuário LDAP a pesquisar
 * @return array Array na seguinte configuração [validacao, (Usuario ou MsgCompErro)]. 
 * Onde:
 * status - Pode possuir 3 valores:
 * 1 - True, se validou o usuário corretamente.
 * 2 - False, se está tudo ok mas o usuário está bloqueado no SEAD.
 * 3 - NULL, se o usuário não existe no LDAP ou a senha está incorreta.
 * 4 - Um código de erro, caso ocorreu um erro ao processar a validação do login.
 * 
 * (Usuario) - Caso validou corretamente (status = True), retorna o objeto Usuario recuperado do banco  
 * 
 * (MsgCompErro) - Caso retornou um código de erro, esse campo PODE informar uma mensagem complementar para a mensagem de erro correspondente
 * 
 * @throws NegocioException
 */
function ldap_validar_login($login, $senha) {
    try {
        $servidor = "ldaps://ldap1.ufes.br";
        $porta = "636"; // SSL
        $usuAutent = "uid=user.moodlenead,ou=moodle,ou=apps,dc=ufes,dc=br";
        $senAutent = "eldooM.s3nh4";


        // conectando-se ao servidor
        if (!($conexao = ldap_connect($servidor, $porta))) {
            // impossível se conectar ao servidor
            return array(LDAPUfes::$ERR_LDAP_SERVIDOR);
        }


        // setando parâmetros importantes
        ldap_set_option($conexao, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conexao, LDAP_OPT_REFERRALS, 0);



        //autenticando usuário de pesquisa
        if (!ldap_validar_autenticacao($conexao, $usuAutent, $senAutent)) {
            // usuário para busca errado
            return array(LDAPUfes::$ERR_LDAP_USUARIO_BUSCA);
        }

        // pesquisando por usuário
        $busca = ldap_search($conexao, "ou=people,dc=ufes,dc=br", "uid=$login", array("uid", "cn", "mail", "postaladdress", "l", "st", "postalcode", "brpersoncpf", "dateOfBirth", "matsiape", "homephone", "mobile", "edupersonaffiliation", "nsaccountlock", "nsaccountkeep"));
//
//        $infoBusca = ldap_get_entries($conexao, $busca);
//        for ($x = 0; $x < 15; $x++) {
//            $chave = $infoBusca[0][$x];
//            echo $chave . " = " . $infoBusca[0][$chave][0] . "<br>";
//        }
//        exit;
//        
//        
        // verificando se retornou pelo menos um, e apenas um, item
        if (ldap_count_entries($conexao, $busca) == 1) {
            $infoBusca = ldap_get_entries($conexao, $busca);

            // checando flags para determinar se o usuário está bloqueado
            $bloqueado = $infoBusca[0]['nsaccountlock'][0] === "true";
            $liberadoTemp = $infoBusca[0]['nsaccountkeep'][0] === "true";


//            print_r($bloqueado);
//            print_r("<br/>");
//            print_r($liberadoTemp);
//            exit;
//            
//            
            // caso de estar bloqueado
            if (($bloqueado && !$liberadoTemp)) {
                // tratando usuário bloqueado
                // Nesse ponto, o retorno sempre gera uma mensagem na tela
                return Usuario::tratarLoginLDAPBloqueado($login, $infoBusca[0]['mail'][0], $cpf = $infoBusca[0]['brpersoncpf'][0]);
            }



            //tentando autenticar com o usuário pesquisado
            if (!ldap_validar_autenticacao($conexao, $infoBusca[0]['dn'], $senha)) {
                // Não validou: Senha do usuário é inválida
                return array(NULL);
            }

//            for ($x = 0; $x < 15; $x++) {
//                $chave = $infoBusca[0][$x];
//                echo $chave . " = " . $infoBusca[0][$chave][0] . "<br>";
//            }
//            exit;
//            
//            
            // verificando uid do usuário encontrado
            if ($login == $infoBusca[0]['uid'][0]) {

                //validou no ldap, agora resta sincronização de banco
                //criando objetos com dados capturados do ldap
                //Note: Para usuários LDAP o campo senha é uma string aleatória, gerada pela função geraSenhaAleatoria. Observe que esse campo não 
                // é usado para autenticação do usuário.
                $nome = $infoBusca[0]['cn'][0];
                $email = $infoBusca[0]['mail'][0];
                $tpVinculo = ldap_pegar_tipo_vinculo($infoBusca);
                //$tpVinculo = Usuario::$VINCULO_NENHUM;
                $objUsuario = new Usuario(NULL, Usuario::$USUARIO_CANDIDATO, $login, $email, ldap_gera_senha_aleatoria($login), $nome, $tpVinculo, NGUtil::getSITUACAO_ATIVO());
//                print_r($tpVinculo);
//                print_r($objUsuario);
//                echo "</br></br>";
//                exit;
                //endereço 
                $end = ldap_desmembrar_endereco($infoBusca[0]['postaladdress'][0]);
                $logradouro = $end[0];
                $numero = $end[1];
                $bairro = $end[2];
                $complemento = $end[3];
                $cidade = isset($infoBusca[0]['l'][0]) ? $infoBusca[0]['l'][0] : NULL;
                $uf = isset($infoBusca[0]['st'][0]) ? $infoBusca[0]['st'][0] : NULL;
                $cep = isset($infoBusca[0]['postalcode'][0]) ? $infoBusca[0]['postalcode'][0] : NULL;

                // validando campos obrigatórios
                if (!ldap_validar_campos_obrigatorios(array($logradouro, $bairro, $cidade, $uf, $cep))) {
                    // campos de endereço faltando... 
                    return array(LDAPUfes::$ERR_LDAP_DADOS_INCONSISTENTES, "Endereço");
                }
                $objEndereco = new Endereco(NULL, Endereco::$DESC_END_RESIDENCIAL, $logradouro, $numero, $bairro, $cidade, NULL, $uf, $cep, $complemento);

//            print_r($objEndereco);
//            echo "</br></br>";

                /* Dados adicionais */
                $cpf = $infoBusca[0]['brpersoncpf'][0];
                $matSiape = isset($infoBusca[0]['matsiape'][0]) ? $infoBusca[0]['matsiape'][0] : NULL;
                $dtNascimento = ldap_pegar_data_nascimento($infoBusca[0]['dateofbirth'][0]);
                // validando campos obrigatórios
                if (!ldap_validar_campos_obrigatorios(array($cpf, $infoBusca[0]['dateofbirth'][0]))) {
                    // campos faltando... 
                    return array(LDAPUfes::$ERR_LDAP_DADOS_INCONSISTENTES, "CPF ou Data de Nascimento");
                }
                $objIdentCandidato = new IdentificacaoCandidato(NULL, $cpf, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $dtNascimento, NULL, NULL, NULL, NULL, $matSiape);

                /* Dados de contato */
                $telRes = isset($infoBusca[0]['homephone'][0]) ? ldap_tratar_tipo_tel($infoBusca[0]['homephone'][0]) : NULL;
                $telCel = isset($infoBusca[0]['mobile'][0]) ? ldap_tratar_tipo_tel($infoBusca[0]['mobile'][0]) : NULL;
                $objContatoCand = new ContatoCandidato(NULL, $telRes, NULL, $telCel);

//                for ($x = 0; $x < 15; $x++) {
//                    $chave = $infoBusca[0][$x];
//                    echo $chave . " = " . $infoBusca[0][$chave][0] . "<br>";
//                }
//                exit;
//            echo "TESTE: " . $infoBusca[0]['edupersonaffiliation'][1] . "<br>";
                return Usuario::sincronizaSisComLdap($objUsuario, $objEndereco, $objIdentCandidato, $objContatoCand);
            } else {
                // erro no LDAP: Caso improvável
                return array(NULL);
            }
        } else {
            // usuário não existe no ldap
            return array(NULL);
        }
    } catch (NegocioException $n) {
        throw $n;
    } catch (Exception $e) {
        throw new NegocioException("Erro ao validar login SIS_L.", $e);
    }
}

function ldap_validar_autenticacao($conexao, $usuario, $senha) {
    // Tenta autenticar no servidor 
    $bind = @ldap_bind($conexao, $usuario, $senha);

    //verifica
    if (isset($bind) && $bind) {
        return TRUE;
    }
    //não validou
    return FALSE;
}

function ldap_validar_campos_obrigatorios($arrayValores) {
    foreach ($arrayValores as $val) {
        if (Util::vazioNulo($val)) {
            return FALSE;
        }
    }
    // tudo ok
    return TRUE;
}

/**
 * 
 * @param Campo LDAP de endereço $end
 * @return array(logradouro, numero, bairro, complemento)
 */
function ldap_desmembrar_endereco($end) {
    $log = explode(",", $end);
    if (isset($log[1])) {
        if (!empty($log[1])) {
            if (isset($log[1])) {
                $num = explode("-", $log[1]);
            } else {
                $num = NULL;
            }
        } else { //Tratamento de caso de endereços com 2 virgulas.
            if (isset($log[2])) {
                $num = explode("-", $log[2]);
            } else {
                $num = NULL;
            }
        }
    } else { //Tratando o caso onde não temos virgula.
        $log2 = explode("-", $log[0]);
        $log[0] = $log2[0];
        $num[0] = "s/n";
        $num[1] = $log2[1];
        $num[2] = $log2[2];
    }

    return array(trim($log[0]), ($num != NULL ? trim($num[0]) : NULL), (isset($num[1]) ? trim($num[1]) : NULL), (isset($num[2]) ? trim($num[2]) : NULL));
}

/**
 * Esta função converte uma data presente no LDAP por uma data interpretada pelo sistema
 * 
 * @param string $dtNascLdap
 * @return string Data no formato dd/mm/yyyy
 */
function ldap_pegar_data_nascimento($dtNascLdap) {
    $dia = substr($dtNascLdap, 6);
    $mes = substr($dtNascLdap, 4, 2);
    $ano = substr($dtNascLdap, 0, 4);

    return "$dia/$mes/$ano";
}

function ldap_pegar_tipo_vinculo($infoBusca) { // matriz do ldap
    /**
     * Tipos de Vínculo com a UFES, de acordo com a documentação do LDAP
     * Ver também em Usuario.php
     * 
     * Na frente de cada Tipo, é informado a prioridade de registro. Na ausência de prioridade, 
     * considere prioridade semelhante.
     * 
     * 1. Docente; - 1
     * 2. Técnico Administrativo; - 2
     * 3. Instrutor; -
     * 4. Aluno de Graduação; - 3
     * 5. Aluno de Pós-Graduação; - 3
     * 6. Aluno de Intercâmbio; - 3
     * 7. Aluno Mobilidade; - 3
     * 8. Visitante; - 
     * 9. Terceirizado; - 
     * 10. Residente de Medicina; - 
     * 11. Aluno de Graduação Especial; - 3
     * 12. Professor Colaborador/Visitante de Pós-Graduação; - 
     * 13. Secretário da Pós-Graduação; -  
     * 15. Residente Multi-Funcional; - 
     * 16. Tutor EAD; - 
     * 17. Aluno de Graduação EAD; - 3
     */
    $listaAluno = array('4', '5', '6', '7', '11', '17');
    $i = 0;
    $servidor = FALSE;
    $aluno = FALSE;
    $listaNaoPrioritario = array(Usuario::$VINCULO_INSTRUTOR, Usuario::$VINCULO_VISITANTE, Usuario::$VINCULO_TERCEIRIZADO, Usuario::$VINCULO_RESIDENTE_MED, Usuario::$VINCULO_PROF_COLABORADOR_VIS);
    $tpNaoPrioritario = NULL;
    while (isset($infoBusca[0]['edupersonaffiliation'][$i])) {
        if ($infoBusca[0]['edupersonaffiliation'][$i] == Usuario::$VINCULO_DOCENTE) {
            return (int) $infoBusca[0]['edupersonaffiliation'][$i];
        }
        if ($infoBusca[0]['edupersonaffiliation'][$i] == Usuario::$VINCULO_TEC_ADMINISTRATIVO) {
            $servidor = TRUE;
        }
        if (in_array($infoBusca[0]['edupersonaffiliation'][$i], $listaAluno)) {
            $aluno = TRUE;
        }
        if (in_array($infoBusca[0]['edupersonaffiliation'][$i], $listaNaoPrioritario)) {
            $tpNaoPrioritario = $infoBusca[0]['edupersonaffiliation'][$i];
        }
        $i += 1;
    }
    return $servidor ? Usuario::$VINCULO_TEC_ADMINISTRATIVO : ($aluno ? Usuario::$VINCULO_ESTUDANTE : ($tpNaoPrioritario != NULL ? $tpNaoPrioritario : Usuario::$VINCULO_NENHUM));
}

function ldap_tratar_tipo_tel($tel_ldap) {
    if ($tel_ldap == NULL || $tel_ldap == "") {
        return "NULL";
    }
    $tel = "";

    // eliminando espaços
    for ($i = 0; $i < strlen($tel_ldap); $i++) {
        $tel .= $tel_ldap[$i] != ' ' ? $tel_ldap[$i] : "";
    }

    // adicionando ddd, se não existir
    if (strlen($tel) == 8) {
        $tel = "27" . $tel;
    }

    return $tel;
}

function ldap_gera_senha_aleatoria($usuario) {
    $senha = $usuario . mt_rand();
    return str_shuffle($senha);
}

?>