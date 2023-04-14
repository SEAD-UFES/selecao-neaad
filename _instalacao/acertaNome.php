<?php

/* 
 * Script para acertar nome dos usuários cadastrados no sistema.
 */

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/persistencia/ConexaoMysql.php";
require_once $CFG->rpasta . "/negocio/NGUtil.php";

$conexao = NGUtil::getConexao();

$sql = "select USR_DS_NOME, USR_ID_USUARIO from tb_usr_usuario";

$resp = $conexao->execSqlComRetorno($sql);

$numLinhas = ConexaoMysql::getNumLinhas($resp);

for($i = 0; $i < $numLinhas; $i++)
{
    $dados = ConexaoMysql::getLinha($resp);
    $nm = $dados["USR_DS_NOME"];
    $id = $dados["USR_ID_USUARIO"];
    
    $nmCorrigido = str_replace("'", "\'", str_capitalize_forcado($nm));
    
    $atu = "update tb_usr_usuario set USR_DS_NOME = '$nmCorrigido' where USR_ID_USUARIO = '$id'";
    
    $conexao->execSqlSemRetorno($atu);
    
    print_r($nmCorrigido . "<br/>");
}

?>