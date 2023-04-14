<?php

/**
 * Classe que trata casos da busca de cep
 * */
global $CFG;
require_once $CFG->rpasta . "/util/NegocioException.php";
require_once $CFG->rpasta . "/persistencia/ConexaoMysql.php";
require_once $CFG->rpasta . "/negocio/NGUtil.php";

class CEP {

    /**
     * Busca CEP no servidor remoto
     * @param string $nrCEP
     * @return boolean|string
     */
    private static function busca_cep_remoto($nrCEP) {
        $resul = @file_get_contents("http://viacep.com.br/ws/$nrCEP/json/");

        // Erro ao acessar servidor remoto
        if (!$resul) {
            return $resul;
        }

        // decodificando dados
        $strDec = json_decode($resul);
        if (isset($strDec->erro)) {
            // retornando false, pois o CEP e invalido ate no servidor remoto
            return FALSE;
        }

        // criando representaçao no estilo da base local
        $ret = array();
        $ret['cep'] = $strDec->cep;
        $ret['bairro'] = $strDec->bairro;
        $ret['cidade'] = $strDec->localidade;
        $ret['uf'] = $strDec->uf;

        if (!Util::vazioNulo($strDec->logradouro)) {
            // separando logradouro e tipo
            $temp = explode(" ", $strDec->logradouro);
            $ret['tp_logradouro'] = array_shift($temp);
            $ret['logradouro'] = implode(" ", $temp);
        } else {
            $ret['tp_logradouro'] = $ret['logradouro'] = "";
        }

        // retornando
        return $ret;
    }

    /**
     * Insere um novo cep na base local
     * 
     * @param array $vetDados - Array com dados no formato de resposta
     * @param boolean $cepUnico - Diz se e ou nao um CEP unico
     */
    private static function insere_novo_cep($vetDados, $cepUnico = FALSE) {
        // recuperando conexao
        $conexao = NGUtil::getConexao();

        if ($cepUnico) {
            $cep = $vetDados['cep'];

            // verifica se ja existe o cep
            $sql = "select count(*) as qt from cep_unico where cep='$cep'";
            $res = $conexao->execSqlComRetorno($sql);
            if (ConexaoMysql::getResult("qt", $res) > 0) {
                // nada a fazer. CEP ja existe na base
                return;
            }

            // preparando dados
            $cid = $vetDados['cidade'];
            $cidSemAcento = removerAcentos($vetDados['cidade']);
            $uf = $vetDados['uf'];
            $sql = "INSERT INTO `cep_unico`
                    (`Nome`, `NomeSemAcento`, `Cep`, `UF`)
                   VALUES ('$cid', '$cidSemAcento', '$cep', '$uf')";

            // inserindo
            $conexao->execSqlSemRetorno($sql);
        } else {
            $uf = strtolower($vetDados['uf']);
            $cep = $vetDados['cep'];

            // verifica se ja existe o cep
            $sql = "select count(*) as qt from $uf where cep='$cep'";
            $res = $conexao->execSqlComRetorno($sql);
            if (ConexaoMysql::getResult("qt", $res) > 0) {
                // nada a fazer. CEP ja existe na base
                return;
            }


            // preparando dados
            $cid = $vetDados['cidade'];
            $log = $vetDados['logradouro'];
            $tpLog = $vetDados['tp_logradouro'];
            $bai = $vetDados['bairro'];
            $sql = "INSERT INTO `$uf` (`cidade`, `logradouro`, `bairro`,
                `cep`, `tp_logradouro`)
                VALUES ('$cid', '$log', '$bai', '$cep', '$tpLog')";

            // inserindo
            $conexao->execSqlSemRetorno($sql);
        }
    }

    public static function getEnderecoCEP($nrCEP) {
        try {
            // tratando cep recebido 
            if (preg_match('/^(\d{5})(\d{3})$/', $nrCEP, $matches)) {
                $nrCEP = $matches[1] . '-' . $matches[2];
            } elseif (!preg_match('/^\d{5}-\d{3}$/', $nrCEP)) {
                throw new NegocioException("CEP inválido.");
            }
            $cep_parts = explode('-', $nrCEP);

            // recuperando conexao
            $conexao = NGUtil::getConexao();


            // descobrindo estado do cep
            $sql = "select uf from cep_log_index where cep5='$cep_parts[0]'";
            $resp = $conexao->execSqlComRetorno($sql);

            // não retornou nenhuma linha?
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                // verificando se e um cep unico
                $sql = "select 
                        cid_id_cidade as id_cidade,
                        Nome as cidade,
                        UF as uf,
                        Cep as cep
                    from
                        cep_unico cep
                            join
                        tb_cid_cidade ON cep.Nome = CID_NM_CIDADE
                        and cid_id_uf = UF
                    where Cep = '$nrCEP'";
                $resp = $conexao->execSqlComRetorno($sql);

                if (ConexaoMysql::getNumLinhas($resp) == 0) {
                    // buscar cep unico no servidor remoto
                    $buscaCep = CEP::busca_cep_remoto($nrCEP);

                    // nao encontrou
                    if (!$buscaCep) {
                        return NULL;
                    }

                    // inserindo cep unico na base local para consulta posterior
                    CEP::insere_novo_cep($buscaCep, TRUE);

                    // buscando id da cidade
                    $sql = "select cid_id_cidade as id_cidade
                        from tb_cid_cidade where cid_nm_cidade = '{$buscaCep['cidade']}'
                        and cid_id_uf = '{$buscaCep['uf']}'";

                    $res = $conexao->execSqlComRetorno($sql);
                    $idCidade = ConexaoMysql::getResult("id_cidade", $res);

                    // retornando 
                    return ["resultado" => 1, "id_cidade" => $idCidade] + $buscaCep;
                } else {
                    // preparando dados para retorno
                    $ret = ConexaoMysql::getLinha($resp);

                    // tirando acento de cidade para compatibilidade
                    $ret['cidade'] = removerAcentos($ret['cidade']);

                    //caixa alta no estado
                    $ret['uf'] = mb_strtoupper($ret['uf']);

                    // retornando
                    return ["resultado" => 1, "logradouro" => "", "bairro" => "", "tp_logradouro" => ""] + $ret;
                }
            } else {

                // verificando se o cep existe na tabela de estados respectiva
                $estado = $conexao->getResult("uf", $resp);
                $sql = "select cid_id_cidade as id_cidade,
                            cidade, logradouro, bairro, cep, tp_logradouro
                        from
                            $estado join tb_cid_cidade on cidade = cid_nm_cidade
                            and cid_id_uf = '$estado'
                        where
                            cep = '$nrCEP'";
                $resp = $conexao->execSqlComRetorno($sql);

                if (ConexaoMysql::getNumLinhas($resp) == 0) {
                    // buscar cep no servidor remoto
                    $buscaCep = CEP::busca_cep_remoto($nrCEP);

                    // nao encontrou
                    if (!$buscaCep) {
                        return NULL;
                    }

                    // inserindo cep na base local para consulta posterior
                    CEP::insere_novo_cep($buscaCep);

                    // buscando id da cidade
                    $sql = "select cid_id_cidade as id_cidade
                        from tb_cid_cidade where cid_nm_cidade = '{$buscaCep['cidade']}'
                        and cid_id_uf = '{$buscaCep['uf']}'";

                    $res = $conexao->execSqlComRetorno($sql);
                    $idCidade = ConexaoMysql::getResult("id_cidade", $res);

                    // retornando 
                    return ["resultado" => 1, "id_cidade" => $idCidade] + $buscaCep;
                } else {
                    // preparando dados para retorno
                    $ret = ConexaoMysql::getLinha($resp);

                    // tirando acento de cidade para compatibilidade
                    $ret['cidade'] = removerAcentos($ret['cidade']);

                    //caixa alta no estado
                    $estado = mb_strtoupper($estado);

                    // retornando
                    return ["resultado" => 1, "uf" => "$estado"] + $ret;
                }
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar cep.", $e);
        }
    }

}

?>
