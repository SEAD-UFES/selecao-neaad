<?php

/**
 * tb_aac_area_atu_chamada class
 * This class manipulates the table AreaAtuChamada
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 27/05/2014
 * */
class AreaAtuChamada {

    private $AAC_ID_AREA_CHAMADA;
    private $ARC_ID_AREA_CONH;
    private $ARC_ID_SUBAREA_CONH;
    private $PCH_ID_CHAMADA;
    private $AAC_QT_VAGAS;
    private $AAC_AREA_DESABILITADA;
//campos herdados
    public $ARC_NM_SUBAREA_CONH;

    /* Construtor padrão da classe */

    public function __construct($AAC_ID_AREA_CHAMADA, $ARC_ID_AREA_CONH, $ARC_ID_SUBAREA_CONH, $PCH_ID_CHAMADA, $AAC_QT_VAGAS, $AAC_AREA_DESABILITADA = NULL) {
        $this->AAC_ID_AREA_CHAMADA = $AAC_ID_AREA_CHAMADA;
        $this->ARC_ID_AREA_CONH = $ARC_ID_AREA_CONH;
        $this->ARC_ID_SUBAREA_CONH = $ARC_ID_SUBAREA_CONH;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->AAC_QT_VAGAS = $AAC_QT_VAGAS;
        $this->AAC_AREA_DESABILITADA = $AAC_AREA_DESABILITADA;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_aac_area_atu_chamada 
                       where PCH_ID_CHAMADA in
                       (select PCH_ID_CHAMADA from tb_pch_processo_chamada where PRC_ID_PROCESSO = '$idProcesso')";
    }

    public static function getFlagAreaAtiva() {
        return FLAG_BD_NAO;
    }

    public static function getFlagAreaInativa() {
        return FLAG_BD_SIM;
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     */
    public static function CLAS_getSqlSumarizaVagas($chamada, &$arrayCmds) {
        if ($chamada->admiteAreaAtuacaoObj()) {
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $flagCdtSel = FLAG_BD_SIM;

            // recuperando áreas
            $areasChamada = self::buscarAreaAtuPorChamada($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva(), TRUE);

            foreach (array_keys($areasChamada) as $id) {
                $arrayCmds [] = "update tb_aac_area_atu_chamada
                            set AAC_QT_SOBRA_VAGAS = (AAC_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND AAC_ID_AREA_CHAMADA = '$id'
                                    ))
                            WHERE AAC_ID_AREA_CHAMADA = '$id'";
            }
        }
    }

    /**
     * Esta função retorna o sql que remove as áreas de atuação que não estão em $idAreasAptas
     * e suas dependências.
     * 
     * @param int $idChamada
     * @param string $idAreasAptas
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function sqlRemoveForaLista($idChamada, $idAreasAptas, &$arrayRet) {

        $sqlAptas = $idAreasAptas == NULL ? "" : " and ARC_ID_SUBAREA_CONH NOT IN ($idAreasAptas)";

        // desabilitando usados por algum candidato
        self::sqlDesativarUtilizadaPorChamada($idChamada, $arrayRet, $sqlAptas);

        // removendo não desabilitados
        $flagAtiva = self::getFlagAreaAtiva();
        $arrayRet [] = "delete from tb_aac_area_atu_chamada where PCH_ID_CHAMADA = '$idChamada' and
                        (AAC_AREA_DESABILITADA IS NULL or AAC_AREA_DESABILITADA = '$flagAtiva')
                        $sqlAptas";
    }

    private static function sqlDesativarUtilizadaPorChamada($idChamada, &$arrayRet, $sqlAppendComAnd = NULL) {
        $sqlAppendComAnd = $sqlAppendComAnd == NULL ? "" : "$sqlAppendComAnd";
        $flagDes = self::getFlagAreaInativa();

        // comando de desativação
        $arrayRet [] = "update tb_aac_area_atu_chamada aac set AAC_AREA_DESABILITADA = '$flagDes' where PCH_ID_CHAMADA = '$idChamada' $sqlAppendComAnd and
                        (select count(*) from tb_ipr_inscricao_processo where PCH_ID_CHAMADA = '$idChamada' and AAC_ID_AREA_CHAMADA = aac.AAC_ID_AREA_CHAMADA) > 0";
    }

    /**
     * 
     * @param int $idChamada
     * @param char $flagSituacao
     * @param boolean $chaveOrigTab Informa se, no retorno, a chave do vetor deve ser a chave original da tabela. Padrão: FALSE
     * @return array Array na forma (ID -> NmArea)
     * @throws NegocioException
     */
    public static function buscarAreaAtuPorChamada($idChamada, $flagSituacao = NULL, $chaveOrigTab = FALSE) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select
                    aac.`ARC_ID_SUBAREA_CONH`,
                    AAC_ID_AREA_CHAMADA,
                    arc.ARC_NM_AREA_CONH
                    from
                    tb_aac_area_atu_chamada aac
                    join
                    tb_arc_area_conhecimento arc ON aac.ARC_ID_SUBAREA_CONH = arc.ARC_ID_AREA_CONH
                    where
                    PCH_ID_CHAMADA = '$idChamada'";


            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagAreaAtiva()) { // ativo
                    $sql .= " AND (AAC_AREA_DESABILITADA IS NULL
                              OR AAC_AREA_DESABILITADA = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagAreaInativa()) { // desativado
                    $sql .= " AND (AAC_AREA_DESABILITADA IS NOT NULL
                              AND AAC_AREA_DESABILITADA = '$flagSituacao')";
                }
            }


            $sql.= " order by arc.ARC_NM_AREA_CONH ";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os cargos
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = !$chaveOrigTab ? $dados['ARC_ID_SUBAREA_CONH'] : $dados['AAC_ID_AREA_CHAMADA'];
                $valor = $dados['ARC_NM_AREA_CONH'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar áreas de atuação do processo.", $e);
        }
    }

    public static function teveModificacaoAreaAtuChamada($idChamada, $idAreasAtu) {
        // recuperando polos anteriores da chamada
        $idAreaAtuAtual = array_keys(self::buscarAreaAtuPorChamada($idChamada, AreaAtuChamada::getFlagAreaAtiva()));
        return $idAreaAtuAtual != $idAreasAtu;
    }

    /**
     * 
     * @param boolean $passo2 Diz se a atualização refere-se ao passo 2 de configuração
     * @param int $idChamada
     * @param int $idAreaAtu
     * @param int $qtVagas
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function processaAtualizacaoVagas($passo2, $idChamada, $idAreaAtu, $qtVagas, &$arrayRet) {
        if ($passo2) {
            // Verificando se já existe a área para chamada
            $existePoloCham = self::existeAreaChamada($idChamada, $idAreaAtu);

            // Não existe?
            if (!$existePoloCham) {
                // tem que criar
                $arrayRet [] = self::getSqlCriarAreaAtuChamada($idChamada, $idAreaAtu, $qtVagas);
                return; // nada mais a fazer
            }
        }

        // gerando sql que atualiza a quantidade de vagas
        $arrayRet [] = self::getSqlAtualizaAreaAtuChamada($idChamada, $idAreaAtu, $qtVagas);
    }

    private static function getSqlCriarAreaAtuChamada($idChamada, $idSubAreaConh, $qtVagas) {
        return "insert into tb_aac_area_atu_chamada (ARC_ID_AREA_CONH, ARC_ID_SUBAREA_CONH, PCH_ID_CHAMADA, AAC_QT_VAGAS) values
                ((select ARC_ID_AREA_PAI_CONH from tb_arc_area_conhecimento where ARC_ID_AREA_CONH = '$idSubAreaConh'), '$idSubAreaConh', '$idChamada', '$qtVagas')";
    }

    private static function getSqlAtualizaAreaAtuChamada($idChamada, $idSubAreaConh, $qtVagas) {
        return "update tb_aac_area_atu_chamada set AAC_QT_VAGAS = '$qtVagas', AAC_AREA_DESABILITADA = NULL where
                PCH_ID_CHAMADA = '$idChamada' and ARC_ID_SUBAREA_CONH = '$idSubAreaConh'";
    }

    public static function existeAreaChamada($idChamada, $idSubAreaConh) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select
                    count(*) as cont
                    from tb_aac_area_atu_chamada
                    where `PCH_ID_CHAMADA` = '$idChamada'
                    and ARC_ID_SUBAREA_CONH = '$idSubAreaConh'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            return ConexaoMysql:: getResult("cont", $resp) != 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar Áreas de Atuação da chamada do processo.", $e);
        }
    }

    public static function contarAreaAtuPorChamada($idChamada, $flagSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select
                    count(*) as cont
                    from
                    tb_aac_area_atu_chamada aac
                    join
                    tb_arc_area_conhecimento arc ON aac.ARC_ID_SUBAREA_CONH = arc.ARC_ID_AREA_CONH
                    where
                    PCH_ID_CHAMADA = '$idChamada'";


            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagAreaAtiva()) { // ativo
                    $sql .= " AND (AAC_AREA_DESABILITADA IS NULL
                              OR AAC_AREA_DESABILITADA = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagAreaInativa()) { // desativado
                    $sql .= " AND (AAC_AREA_DESABILITADA IS NOT NULL
                              AND AAC_AREA_DESABILITADA = '$flagSituacao')";
                }
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);
            return ConexaoMysql:: getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar áreas de atuação da chamada do processo.", $e);
        }
    }

    public static function buscarIdAreaAtuPorChamadaArea($idChamada, $idAreaConh) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select
                    AAC_ID_AREA_CHAMADA
                    from
                    tb_aac_area_atu_chamada aac
                    where
                    PCH_ID_CHAMADA = '$idChamada'
                    and ARC_ID_SUBAREA_CONH = '$idAreaConh'";


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                // erro de emparelhamento
                throw new NegocioException("Área de atuação do processo não encontrada.");
            }

            return ConexaoMysql:: getResult("AAC_ID_AREA_CHAMADA", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar área de atuação da chamada do processo.", $e);
        }
    }

    public static function buscarAreaAtuChamVagasPorChamada($idChamada, $flagSituacao = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select `AAC_ID_AREA_CHAMADA`
                    , AAC_QT_VAGAS
                    from tb_aac_area_atu_chamada
                    where `PCH_ID_CHAMADA` = '$idChamada'";

            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagAreaAtiva()) { // ativo
                    $sql .= " AND (AAC_AREA_DESABILITADA IS NULL
                            OR AAC_AREA_DESABILITADA = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagAreaInativa()) { // desativado
                    $sql .= " AND (AAC_AREA_DESABILITADA IS NOT NULL
                            AND AAC_AREA_DESABILITADA = '$flagSituacao')";
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
                $chave = $dados['AAC_ID_AREA_CHAMADA'];
                $valor = $dados['AAC_QT_VAGAS'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar áreas de atuação e vagas da chamada do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idAreaAtuChamada
     * @return \AreaAtuChamada
     * @throws NegocioException
     */
    public static function buscarAreaAtuChamadaPorId($idAreaAtuChamada) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select
                    `AAC_ID_AREA_CHAMADA`,
                     arc.ARC_NM_AREA_CONH,
                     AAC_QT_VAGAS,
                     aac.ARC_ID_AREA_CONH,
                     ARC_ID_SUBAREA_CONH,
                     PCH_ID_CHAMADA,
                     AAC_AREA_DESABILITADA
                    from
                    tb_aac_area_atu_chamada aac
                    join
                    tb_arc_area_conhecimento arc ON aac.ARC_ID_SUBAREA_CONH = arc.ARC_ID_AREA_CONH
                    where
                    AAC_ID_AREA_CHAMADA = '$idAreaAtuChamada'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Área de atuação chamada não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $areaAtuTemp = new AreaAtuChamada($dados['AAC_ID_AREA_CHAMADA'], $dados['ARC_ID_AREA_CONH'], $dados['ARC_ID_SUBAREA_CONH'], $dados['PCH_ID_CHAMADA'], $dados['AAC_QT_VAGAS'], $dados['AAC_AREA_DESABILITADA']);
            // campos herdados
            $areaAtuTemp->ARC_NM_SUBAREA_CONH = $dados['ARC_NM_AREA_CONH'];

            return $areaAtuTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar área de atuação da chamada.", $e);
        }
    }

    /**
     * 
     * @param int $idChamada
     * @param char $flagSituacao
     * @param array $listaAreasAtu  Array de Áreas de atuação que devem aparecer no retorno, independente se participam ou não da chamada
     * @return \AreaAtuChamada
     * @throws NegocioException
     */
    public static function buscarAreaAtuCompPorChamada($idChamada, $flagSituacao = NULL, $listaAreasAtu = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select
                    `AAC_ID_AREA_CHAMADA`,
                     arc.ARC_NM_AREA_CONH,
                     AAC_QT_VAGAS,
                     aac.ARC_ID_AREA_CONH,
                     aac.ARC_ID_SUBAREA_CONH,
                     PCH_ID_CHAMADA,
                     AAC_AREA_DESABILITADA
                    from
                    tb_aac_area_atu_chamada aac
                    join
                    tb_arc_area_conhecimento arc ON aac.ARC_ID_SUBAREA_CONH = arc.ARC_ID_AREA_CONH
                    where
                    PCH_ID_CHAMADA = '$idChamada'";


            // caso de lista de áreas de atuação
            if ($listaAreasAtu != NULL) {
                $vetorAreasAtu = explode(", ", $listaAreasAtu);
                $sql .= " and aac.ARC_ID_SUBAREA_CONH in ($listaAreasAtu) ";
            }


            // tratando caso de flag situação
            if ($flagSituacao != NULL) {
                if ($flagSituacao == self::getFlagAreaAtiva()) { // ativo
                    $sql .= " AND (AAC_AREA_DESABILITADA IS NULL
                            OR AAC_AREA_DESABILITADA = '$flagSituacao')";
                } elseif ($flagSituacao == self::getFlagAreaInativa()) { // desativado
                    $sql .= " AND (AAC_AREA_DESABILITADA IS NOT NULL
                            AND AAC_AREA_DESABILITADA = '$flagSituacao')";
                }
            }

            $sql.= " order by arc.ARC_NM_AREA_CONH ";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // contando linhas retornadas
            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            $vetRetorno = array();

            //realizando iteração para recuperar os cargos
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $areaAtuTemp = new AreaAtuChamada($dados['AAC_ID_AREA_CHAMADA'], $dados['ARC_ID_AREA_CONH'], $dados['ARC_ID_SUBAREA_CONH'], $dados['PCH_ID_CHAMADA'], $dados['AAC_QT_VAGAS'], $dados['AAC_AREA_DESABILITADA']);

                // verificando necessidade de remover áreas já aparecidas
                if (isset($vetorAreasAtu)) {
                    unset($vetorAreasAtu[array_search($areaAtuTemp->ARC_ID_SUBAREA_CONH, $vetorAreasAtu)]);
                }

                // campos herdados
                $areaAtuTemp->ARC_NM_SUBAREA_CONH = $dados['ARC_NM_AREA_CONH'];

                //adicionando no vetor
                $vetRetorno[] = $areaAtuTemp;
            }

            // incluindo demais áreas requisitadas
            if (isset($vetorAreasAtu) && count($vetorAreasAtu) != 0) {
                $listaAreasAtu = implode(", ", $vetorAreasAtu);

                $areasAtu = AreaConhecimento::buscarAreasPorIds($listaAreasAtu);

                // percorrendo polos e gerando dados
                foreach ($areasAtu as $id => $nmArea) {

                    $areaAtuTemp = new AreaAtuChamada(NULL, NULL, $id, $idChamada, 0, NULL);
                    $areaAtuTemp->ARC_NM_SUBAREA_CONH = $nmArea;

                    $vetRetorno [] = $areaAtuTemp;
                }
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar áreas de atuação do processo.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getAAC_ID_AREA_CHAMADA() {
        return $this->AAC_ID_AREA_CHAMADA;
    }

    /* End of get AAC_ID_AREA_CHAMADA */

    function getARC_ID_AREA_CONH() {
        return $this->ARC_ID_AREA_CONH;
    }

    /* End of get ARC_ID_AREA_CONH */

    function getARC_ID_SUBAREA_CONH() {
        return $this->ARC_ID_SUBAREA_CONH;
    }

    /* End of get ARC_ID_SUBAREA_CONH */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getAAC_QT_VAGAS() {
        return $this->AAC_QT_VAGAS;
    }

    /* End of get AAC_QT_VAGAS */



    /* SET FIELDS FROM TABLE */

    function setAAC_ID_AREA_CHAMADA($value) {
        $this->AAC_ID_AREA_CHAMADA = $value;
    }

    /* End of SET AAC_ID_AREA_CHAMADA */

    function setARC_ID_AREA_CONH($value) {
        $this->ARC_ID_AREA_CONH = $value;
    }

    /* End of SET ARC_ID_AREA_CONH */

    function setARC_ID_SUBAREA_CONH($value) {
        $this->ARC_ID_SUBAREA_CONH = $value;
    }

    /* End of SET ARC_ID_SUBAREA_CONH */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setAAC_QT_VAGAS($value) {
        $this->AAC_QT_VAGAS = $value;
    }

    /* End of SET AAC_QT_VAGAS */
}

?>
