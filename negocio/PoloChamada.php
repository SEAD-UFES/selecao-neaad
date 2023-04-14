<?php

/**
 * tb_ppc_polo_chamada class
 * This class manipulates the table PoloChamada
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 16/10/2013
 * */
class PoloChamada {

    private $POL_ID_POLO;
    private $PCH_ID_CHAMADA;
    private $PPC_QT_VAGAS;
    private $PPC_POL_DESABILITADO;
    // campos herdados
    public $POL_DS_POLO;

    /* Construtor padrão da classe */

    public function __construct($POL_ID_POLO, $PCH_ID_CHAMADA, $PPC_QT_VAGAS = NULL, $PPC_POL_DESABILITADO = NULL) {
        $this->POL_ID_POLO = $POL_ID_POLO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->PPC_QT_VAGAS = $PPC_QT_VAGAS;
        $this->PPC_POL_DESABILITADO = $PPC_POL_DESABILITADO;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_ppc_polo_chamada 
                       where PCH_ID_CHAMADA in
                       (select PCH_ID_CHAMADA from tb_pch_processo_chamada where PRC_ID_PROCESSO = '$idProcesso')";
    }

    public static function getFlagPoloAtivo() {
        return FLAG_BD_NAO;
    }

    public static function getFlagPoloInativo() {
        return FLAG_BD_SIM;
    }

    public static function CLAS_getSqlSobraVagas($idChamada, $idPolo) {
        return "select PPC_QT_SOBRA_VAGAS as " . ProcessoChamada::$SQL_RET_SOBRA_VAGAS . " from tb_ppc_polo_chamada where 
                        PCH_ID_CHAMADA = '$idChamada'
                        AND POL_ID_POLO = '$idPolo'";
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     */
    public static function CLAS_getSqlSumarizaVagas($chamada, &$arrayCmds) {
        if ($chamada->admitePoloObj()) {
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $flagCdtSel = FLAG_BD_SIM;

            // recuperando polos
            $polosChamada = self::buscarPoloPorChamada($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo());

            foreach (array_keys($polosChamada) as $id) {
                $arrayCmds [] = "update tb_ppc_polo_chamada
                            set PPC_QT_SOBRA_VAGAS = (PPC_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND IPR_ID_POLO_SELECIONADO = '$id'
                                    ))
                            WHERE POL_ID_POLO = '$id' and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
            }
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     * @param int $idPolo
     */
    public static function CLAS_getSqlSumarizaVagasInd($chamada, &$arrayCmds, $idPolo) {
        $inscOk = InscricaoProcesso::$SIT_INSC_OK;
        $flagCdtSel = FLAG_BD_SIM;

        $arrayCmds [] = "update tb_ppc_polo_chamada
                            set PPC_QT_SOBRA_VAGAS = (PPC_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND IPR_ID_POLO_SELECIONADO = '$idPolo'
                                    ))
                            WHERE POL_ID_POLO = '$idPolo'
                            and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
    }

    /**
     * Esta função retorna o sql que remove os polos que não estão em $idPolosAptos
     * e suas dependências.
     * 
     * @param int $idChamada
     * @param string $idPolosAptos
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function sqlRemoveForaLista($idChamada, $idPolosAptos, &$arrayRet) {

        $sqlAptos = $idPolosAptos == NULL ? "" : " and POL_ID_POLO NOT IN ($idPolosAptos)";

        // desabilitando usados por algum candidato
        self::sqlDesativarUtilizadoPorChamada($idChamada, $arrayRet, $sqlAptos);

        // removendo não desabilitados
        $flagAtivo = self::getFlagPoloAtivo();
        $arrayRet [] = "delete from tb_ppc_polo_chamada where PCH_ID_CHAMADA = '$idChamada' and 
                         (PPC_POL_DESABILITADO IS NULL or PPC_POL_DESABILITADO = '$flagAtivo')
                        $sqlAptos";
    }

    private static function sqlDesativarUtilizadoPorChamada($idChamada, &$arrayRet, $sqlAppendComAnd = NULL) {
        $sqlAppendComAnd = $sqlAppendComAnd == NULL ? "" : "$sqlAppendComAnd";
        $flagDes = self::getFlagPoloInativo();

        // lista de polos
        $arrayRet [] = "update tb_ppc_polo_chamada ppc set PPC_POL_DESABILITADO = '$flagDes' where PCH_ID_CHAMADA = '$idChamada' $sqlAppendComAnd and 
                (select count(*) from tb_pin_polo_inscricao pin 
                join tb_ipr_inscricao_processo ipr on pin.IPR_ID_INSCRICAO = ipr.IPR_ID_INSCRICAO
                where PCH_ID_CHAMADA = '$idChamada' and POL_ID_POLO = ppc.POL_ID_POLO) > 0";

        // parte da inscrição
        $arrayRet [] = "update tb_ppc_polo_chamada ppc set PPC_POL_DESABILITADO = '$flagDes' where PCH_ID_CHAMADA = '$idChamada' $sqlAppendComAnd and 
                (select count(*) from tb_ipr_inscricao_processo where PCH_ID_CHAMADA = '$idChamada' and IPR_ID_POLO_SELECIONADO = ppc.POL_ID_POLO) > 0";
    }

    /**
     * 
     * @param int $idChamada
     * @param char $flagSituacao
     * @param array $listaPolos - Array de Polos que devem aparecer no retorno, independente se participam ou não da chamada
     * @return \PoloChamada
     * @throws NegocioException
     */
    public static function buscarPoloCompPorChamada($idChamada, $flagSituacao = NULL, $listaPolos = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select pol.`POL_ID_POLO` as POL_ID_POLO
                    , pol.POL_DS_POLO as POL_DS_POLO
                    , PCH_ID_CHAMADA
                    , PPC_QT_VAGAS      
                    , PPC_POL_DESABILITADO
                    from tb_ppc_polo_chamada ppc
                    join tb_pol_polo pol on ppc.POL_ID_POLO = pol.POL_ID_POLO
                    where `PCH_ID_CHAMADA` = '$idChamada'";


            // caso de lista polo
            if ($listaPolos != NULL) {
                $vetorPolos = explode(",", $listaPolos);
                $sql .= " and ppc.POL_ID_POLO in ($listaPolos) ";
            }


            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagPoloAtivo()) { // ativo
                    $sql .= " AND (PPC_POL_DESABILITADO IS NULL
                                OR PPC_POL_DESABILITADO = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagPoloInativo()) { // desativado
                    $sql .= " AND (PPC_POL_DESABILITADO IS NOT NULL
                                AND PPC_POL_DESABILITADO = '$flagSituacao')";
                }
            }

            $sql .= " order by pol.POL_DS_POLO";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // contando linhas retornadas
            $numLinhas = ConexaoMysql::getNumLinhas($resp);


            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                // objeto temporario
                $poloChamadaTemp = new PoloChamada($dados['POL_ID_POLO'], $dados['PCH_ID_CHAMADA'], $dados['PPC_QT_VAGAS'], $dados['PPC_POL_DESABILITADO']);

                // verificando necessidade de remover polos já aparecidos da lista de polos
                if (isset($vetorPolos)) {
                    unset($vetorPolos[array_search($poloChamadaTemp->POL_ID_POLO, $vetorPolos)]);
                }

                // campos herdados
                $poloChamadaTemp->POL_DS_POLO = $dados['POL_DS_POLO'];

                //adicionando no vetor
                $vetRetorno[] = $poloChamadaTemp;
            }

            // incluindo demais polos requisitados
            if (isset($vetorPolos) && count($vetorPolos) != 0) {
                $listaPolos = implode(",", $vetorPolos);

                $polos = Polo::buscarPolosPorIds($listaPolos);

                // percorrendo polos e gerando dados
                foreach ($polos as $id => $nmPolo) {

                    $poloChamadaTemp = new PoloChamada($id, $idChamada, 0);
                    $poloChamadaTemp->POL_DS_POLO = $nmPolo;

                    $vetRetorno [] = $poloChamadaTemp;
                }
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar polos da chamada do processo.", $e);
        }
    }

    public static function buscarPoloPorChamada($idChamada, $flagSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select pol.`POL_ID_POLO` as POL_ID_POLO
                    , pol.POL_DS_POLO as POL_DS_POLO
                    from tb_ppc_polo_chamada ppc
                    join tb_pol_polo pol on ppc.POL_ID_POLO = pol.POL_ID_POLO
                    where `PCH_ID_CHAMADA` = '$idChamada'";

            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagPoloAtivo()) { // ativo
                    $sql .= " AND (PPC_POL_DESABILITADO IS NULL
                                OR PPC_POL_DESABILITADO = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagPoloInativo()) { // desativado
                    $sql .= " AND (PPC_POL_DESABILITADO IS NOT NULL
                                AND PPC_POL_DESABILITADO = '$flagSituacao')";
                }
            }


            // finalização
            $sql .= " order by pol.POL_DS_POLO";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['POL_ID_POLO'];
                $valor = $dados['POL_DS_POLO'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar polos da chamada do processo.", $e);
        }
    }

    public static function teveModificacaoPoloChamada($idChamada, $idPolos) {
        // recuperando polos anteriores da chamada
        $polosAtuais = array_keys(self::buscarPoloPorChamada($idChamada, PoloChamada::getFlagPoloAtivo()));
        return $polosAtuais != $idPolos;
    }

    /**
     * 
     * @param boolean $passo2 Diz se a atualização refere-se ao passo 2 de configuração
     * @param int $idChamada
     * @param int $idPolo
     * @param int $qtVagas
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function processaAtualizacaoVagas($passo2, $idChamada, $idPolo, $qtVagas, &$arrayRet) {
        if ($passo2) {
            // Verificando se já existe o polo para chamada
            $existePoloCham = self::existePoloChamada($idChamada, $idPolo);

            // Não existe?
            if (!$existePoloCham) {
                // tem que criar
                $arrayRet [] = self::getSqlCriarPoloChamada($idChamada, $idPolo, $qtVagas);
                return; // nada mais a fazer
            }
        }

        // gerando sql que atualiza a quantidade de vagas
        $arrayRet [] = self::getSqlAtualizaPoloChamada($idChamada, $idPolo, $qtVagas);
    }

    private static function getSqlCriarPoloChamada($idChamada, $idPolo, $qtVagas) {
        return "insert into tb_ppc_polo_chamada (POL_ID_POLO, PCH_ID_CHAMADA, PPC_QT_VAGAS) values
                ('$idPolo', '$idChamada', '$qtVagas')";
    }

    private static function getSqlAtualizaPoloChamada($idChamada, $idPolo, $qtVagas) {
        return "update tb_ppc_polo_chamada set PPC_QT_VAGAS = '$qtVagas', PPC_POL_DESABILITADO = NULL where
                PCH_ID_CHAMADA = '$idChamada' and POL_ID_POLO = '$idPolo'";
    }

    public static function existePoloChamada($idChamada, $idPolo) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                       count(*) as cont
                     from tb_ppc_polo_chamada
                    where `PCH_ID_CHAMADA` = '$idChamada'
                           and POL_ID_POLO = '$idPolo'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            return ConexaoMysql:: getResult("cont", $resp) != 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar polos da chamada do processo.", $e);
        }
    }

    public static function contaPoloPorChamada($idChamada, $flagSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select count(*) as cont
                    from tb_ppc_polo_chamada ppc
                    join tb_pol_polo pol on ppc.POL_ID_POLO = pol.POL_ID_POLO
                    where `PCH_ID_CHAMADA` = '$idChamada'";

            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagPoloAtivo()) { // ativo
                    $sql .= " AND (PPC_POL_DESABILITADO IS NULL
                                OR PPC_POL_DESABILITADO = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagPoloInativo()) { // desativado
                    $sql .= " AND (PPC_POL_DESABILITADO IS NOT NULL
                                AND PPC_POL_DESABILITADO = '$flagSituacao')";
                }
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);
            return ConexaoMysql:: getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar polos da chamada do processo.", $e);
        }
    }

    public static function buscarPoloVagasPorChamada($idChamada, $flagSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select `POL_ID_POLO` 
                    , PPC_QT_VAGAS 
                    from tb_ppc_polo_chamada
                    where `PCH_ID_CHAMADA` = '$idChamada'";


            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagPoloAtivo()) { // ativo
                    $sql .= " AND (PPC_POL_DESABILITADO IS NULL
                                OR PPC_POL_DESABILITADO = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagPoloInativo()) { // desativado
                    $sql .= " AND (PPC_POL_DESABILITADO IS NOT NULL
                                AND PPC_POL_DESABILITADO = '$flagSituacao')";
                }
            }


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['POL_ID_POLO'];
                $valor = $dados['PPC_QT_VAGAS'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (
        NegocioException $n) {
            throw $n;
        } catch (xception $e) {
            throw new

            NegocioException("Erro ao buscar polos e vagas da chamada do processo.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getPOL_ID_POLO() {
        return $this->POL_ID_POLO;
    }

    /* End of get POL_ID_POLO */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getPPC_QT_VAGAS() {
        return $this->PPC_QT_VAGAS;
    }

    /* SET FIELDS FROM TABLE */

    function setPOL_ID_POLO($value) {
        $this->POL_ID_POLO = $value;
    }

    /* End of SET POL_ID_POLO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */
}

?>
