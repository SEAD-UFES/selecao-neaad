<?php

/**
 * tb_pac_polo_area_chamada class
 * This class manipulates the table PoloAreaChamada
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 17/09/2014
 * */
class PoloAreaChamada {

    private $PCH_ID_CHAMADA;
    private $POL_ID_POLO;
    private $AAC_ID_AREA_CHAMADA;
    private $PAC_QT_VAGAS;
    // campos herdados
    public $POL_DS_POLO;
    public $ARC_NM_SUBAREA_CONH;
    public $ARC_ID_SUBAREA_CONH;

    /* Construtor padrão da classe */

    public function __construct($PCH_ID_CHAMADA, $POL_ID_POLO, $AAC_ID_AREA_CHAMADA, $PAC_QT_VAGAS) {
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->POL_ID_POLO = $POL_ID_POLO;
        $this->AAC_ID_AREA_CHAMADA = $AAC_ID_AREA_CHAMADA;
        $this->PAC_QT_VAGAS = $PAC_QT_VAGAS;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_pac_polo_area_chamada 
                       where PCH_ID_CHAMADA in
                       (select PCH_ID_CHAMADA from tb_pch_processo_chamada where PRC_ID_PROCESSO = '$idProcesso')";
    }

    public static function CLAS_getSqlSobraVagas($idChamada, $idPolo, $idArea) {
        return "select PAC_QT_SOBRA_VAGAS as " . ProcessoChamada::$SQL_RET_SOBRA_VAGAS . " from tb_pac_polo_area_chamada where 
                        PCH_ID_CHAMADA = '$idChamada'
                        AND AAC_ID_AREA_CHAMADA = '$idArea'
                        AND POL_ID_POLO = '$idPolo'";
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     */
    public static function CLAS_getSqlSumarizaVagas($chamada, &$arrayCmds) {
        if ($chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj()) {
            $inscOk = InscricaoProcesso::$SIT_INSC_OK;
            $flagCdtSel = FLAG_BD_SIM;

            // recuperando dados
            $polosAreasChamada = self::buscarPoloAreaPorChamada($chamada->getPCH_ID_CHAMADA());

            foreach ($polosAreasChamada as $poloAreaChamada) {
                $arrayCmds [] = "update tb_pac_polo_area_chamada
                            set PAC_QT_SOBRA_VAGAS = (PAC_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND IPR_ID_POLO_SELECIONADO = '{$poloAreaChamada->POL_ID_POLO}'
                                    AND AAC_ID_AREA_CHAMADA = '{$poloAreaChamada->AAC_ID_AREA_CHAMADA}'
                                    ))
                            WHERE POL_ID_POLO = '{$poloAreaChamada->POL_ID_POLO}'
                            and AAC_ID_AREA_CHAMADA = '{$poloAreaChamada->AAC_ID_AREA_CHAMADA}'
                            and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
            }
        }
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array onde deve ser colocado a sql, se necessário
     * @param int $idPolo
     * @param int $idArea
     */
    public static function CLAS_getSqlSumarizaVagasInd($chamada, &$arrayCmds, $idPolo, $idArea) {
        $inscOk = InscricaoProcesso::$SIT_INSC_OK;
        $flagCdtSel = FLAG_BD_SIM;

        $arrayCmds [] = "update tb_pac_polo_area_chamada
                            set PAC_QT_SOBRA_VAGAS = (PAC_QT_VAGAS - (SELECT 
                                    COUNT(*)
                                FROM
                                    tb_ipr_inscricao_processo
                                WHERE
                                    PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                    AND IPR_ST_INSCRICAO = '$inscOk'
                                    AND IPR_CDT_SELECIONADO = '$flagCdtSel'
                                    AND IPR_ID_POLO_SELECIONADO = '$idPolo'
                                    AND AAC_ID_AREA_CHAMADA = '$idArea'
                                    ))
                            WHERE POL_ID_POLO = '$idPolo'
                            AND AAC_ID_AREA_CHAMADA = '$idArea'
                            and PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'";
    }

    /**
     * @param int $idChamada
     * @param array $listaPolos Array de Polos que devem aparecer no retorno, independente se participam ou não da chamada
     * @param array $listaAreasAtu Array de Áreas de atuação que podem aparecer no retorno, independente se participam ou não da chamada
     * @return PoloAreaChamada Array de polos e área
     * @throws NegocioException
     */
    public static function buscarPoloAreaPorChamada($idChamada, $listaPolos = NULL, $listaAreasAtu = NULL) {
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select pac.`POL_ID_POLO` 
                    ,pac. AAC_ID_AREA_CHAMADA
                    , pac.PCH_ID_CHAMADA
                    , pac.PAC_QT_VAGAS 
                    , POL_DS_POLO
                    , ARC_NM_AREA_CONH 
                    , aac.ARC_ID_SUBAREA_CONH
                    from tb_pac_polo_area_chamada pac 
                    join tb_pol_polo pol on pac.POL_ID_POLO = pol.POL_ID_POLO
                    join tb_aac_area_atu_chamada aac on pac.AAC_ID_AREA_CHAMADA = aac.AAC_ID_AREA_CHAMADA
                    join tb_arc_area_conhecimento arc ON aac.ARC_ID_SUBAREA_CONH = arc.ARC_ID_AREA_CONH
                    where pac.`PCH_ID_CHAMADA` = '$idChamada'
                    order by POL_DS_POLO, ARC_NM_AREA_CONH";



            // caso de lista de áreas de atuação
            if ($listaAreasAtu != NULL) {
                $vetorAreasAtu = explode(",", $listaAreasAtu);
                $sql .= " and aac.ARC_ID_SUBAREA_CONH in ($listaAreasAtu) ";
            }

            // caso de lista polo
            if ($listaPolos != NULL) {
                $vetorPolos = explode(",", $listaPolos);
                $sql .= " and pac.POL_ID_POLO in ($listaPolos) ";
            }


            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // recuperando quantidade de dados retornados
            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            $vetRetorno = array();


            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando dados
                $poloAreaTemp = new PoloAreaChamada($dados['PCH_ID_CHAMADA'], $dados['POL_ID_POLO'], $dados['AAC_ID_AREA_CHAMADA'], $dados['PAC_QT_VAGAS']);

                // verificando necessidade de remover polos já aparecidos da lista de polos
                if (isset($vetorPolos)) {
                    unset($vetorPolos[array_search($poloAreaTemp->POL_ID_POLO, $vetorPolos)]);
                }

                // verificando necessidade de remover áreas já aparecidas
                if (isset($vetorAreasAtu)) {
                    unset($vetorAreasAtu[array_search($poloAreaTemp->ARC_ID_SUBAREA_CONH, $vetorAreasAtu)]);
                }

                // campos herdados
                $poloAreaTemp->POL_DS_POLO = $dados['POL_DS_POLO'];
                $poloAreaTemp->ARC_NM_SUBAREA_CONH = $dados['ARC_NM_AREA_CONH'];
                $poloAreaTemp->ARC_ID_SUBAREA_CONH = $dados['ARC_ID_SUBAREA_CONH'];

                //adicionando no vetor
                $vetRetorno[] = $poloAreaTemp;
            }


            // incluindo demais polos requisitados
            if (isset($vetorPolos) && count($vetorPolos) != 0) {
                $listaPolos = implode(",", $vetorPolos);

                $polos = Polo::buscarPolosPorIds($listaPolos);

                // percorrendo polos e gerando dados
                foreach ($polos as $id => $nmPolo) {

                    $poloAreaTemp = new PoloAreaChamada($idChamada, $id, NULL, 0);
                    $poloAreaTemp->POL_DS_POLO = $nmPolo;

                    $vetRetorno [] = $poloAreaTemp;
                }
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar polo e área da chamada do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idChamada
     * @param int $idPolo
     * @param int $idAreaAtu
     * @param int $qtVagas
     * @param array &$arrayRet Endereço de memória do vetor de comandos sqls
     */
    public static function processaAtualizacaoVagas($idChamada, $idPolo, $idAreaAtu, $qtVagas, &$arrayRet) {
        // criando polo área
        $arrayRet [] = self::getSqlCriarPolAreaAtuChamada($idChamada, $idPolo, $idAreaAtu, $qtVagas);
    }

    private static function getSqlCriarPolAreaAtuChamada($idChamada, $idPolo, $idSubAreaConh, $qtVagas) {
        return "insert into tb_pac_polo_area_chamada (AAC_ID_AREA_CHAMADA, POL_ID_POLO, PCH_ID_CHAMADA, PAC_QT_VAGAS) values
                ((select AAC_ID_AREA_CHAMADA from tb_aac_area_atu_chamada where PCH_ID_CHAMADA = '$idChamada' and ARC_ID_SUBAREA_CONH = '$idSubAreaConh'), '$idPolo', '$idChamada', '$qtVagas')";
    }

    /**
     * 
     * @param PoloAreaChamada $listaPolosAreas Array de PoloAreaChamada
     * @return array Matriz na forma ($listaPolos, $listaAreasAtu) 
     */
    public static function getListasPolosAreas($listaPolosAreas) {
        $listaPolos = array();
        $listaAreasAtu = array();

        //percorrendo polosAreas
        foreach ($listaPolosAreas as $poloArea) {
            // polos
            if (!isset($listaPolos[$poloArea->getPOL_ID_POLO()])) {
                $listaPolos[$poloArea->getPOL_ID_POLO()] = $poloArea->POL_DS_POLO;
            }

            // areas
            if (!isset($listaAreasAtu[$poloArea->ARC_ID_SUBAREA_CONH])) {
                $listaAreasAtu[$poloArea->ARC_ID_SUBAREA_CONH] = $poloArea->ARC_NM_SUBAREA_CONH;
            }
        }

        return array($listaPolos, $listaAreasAtu);
    }

    public static function buscarAreaAtuPorChamadaPolo($idChamada, $idPolo) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        aac.ARC_ID_SUBAREA_CONH,
                        arc.ARC_NM_AREA_CONH 
                    from tb_pac_polo_area_chamada pac 
                     join tb_aac_area_atu_chamada aac on pac.AAC_ID_AREA_CHAMADA = aac.AAC_ID_AREA_CHAMADA
                     join tb_arc_area_conhecimento arc ON aac.ARC_ID_SUBAREA_CONH = arc.ARC_ID_AREA_CONH
                    where
                        pac.PCH_ID_CHAMADA = '$idChamada'
                        and POL_ID_POLO = '$idPolo'
                    order by  arc.ARC_NM_AREA_CONH ";

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
                $chave = $dados['ARC_ID_SUBAREA_CONH'];
                $valor = $dados['ARC_NM_AREA_CONH'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar áreas de atuação por polo do processo.", $e);
        }
    }

    public static function getSqlRemoverPorChamada($idChamada) {
        return "delete from tb_pac_polo_area_chamada where PCH_ID_CHAMADA = '$idChamada'";
    }

    /* GET FIELDS FROM TABLE */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getPOL_ID_POLO() {
        return $this->POL_ID_POLO;
    }

    /* End of get POL_ID_POLO */

    function getAAC_ID_AREA_CHAMADA() {
        return $this->AAC_ID_AREA_CHAMADA;
    }

    /* End of get AAC_ID_AREA_CHAMADA */

    function getPAC_QT_VAGAS() {
        return $this->PAC_QT_VAGAS;
    }

    /* End of get PAC_QT_VAGAS */



    /* SET FIELDS FROM TABLE */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setPOL_ID_POLO($value) {
        $this->POL_ID_POLO = $value;
    }

    /* End of SET POL_ID_POLO */

    function setAAC_ID_AREA_CHAMADA($value) {
        $this->AAC_ID_AREA_CHAMADA = $value;
    }

    /* End of SET AAC_ID_AREA_CHAMADA */

    function setPAC_QT_VAGAS($value) {
        $this->PAC_QT_VAGAS = $value;
    }

    /* End of SET PAC_QT_VAGAS */
}

?>
