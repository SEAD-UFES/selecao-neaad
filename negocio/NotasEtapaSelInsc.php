<?php

/**
 * tb_nei_notas_etapa_sel_insc class
 * This class manipulates the table NotasEtapaSelInsc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2015       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 06/04/2015
 * */
class NotasEtapaSelInsc {

    private $ESP_ID_ETAPA_SEL;
    private $IPR_ID_INSCRICAO;
    private $NEI_VL_TOTAL_NOTA;
    private $NEI_DT_AVALIACAO;
    private $NEI_ST_INSCRICAO;
    private $NEI_DS_OBS_NOTA;
    private $NEI_ID_USR_AVALIADOR;
    private $NEI_CDT_SELECIONADO;
    private $NEI_NR_CLASSIFICACAO_CAND;
    private $NEI_ID_POLO_SELECIONADO;

    /* Construtor padrão da classe */

    public function __construct($ESP_ID_ETAPA_SEL, $IPR_ID_INSCRICAO, $NEI_VL_TOTAL_NOTA, $NEI_DT_AVALIACAO, $NEI_ST_INSCRICAO, $NEI_DS_OBS_NOTA, $NEI_ID_USR_AVALIADOR, $NEI_CDT_SELECIONADO, $NEI_NR_CLASSIFICACAO_CAND, $NEI_ID_POLO_SELECIONADO) {
        $this->ESP_ID_ETAPA_SEL = $ESP_ID_ETAPA_SEL;
        $this->IPR_ID_INSCRICAO = $IPR_ID_INSCRICAO;
        $this->NEI_VL_TOTAL_NOTA = $NEI_VL_TOTAL_NOTA;
        $this->NEI_DT_AVALIACAO = $NEI_DT_AVALIACAO;
        $this->NEI_ST_INSCRICAO = $NEI_ST_INSCRICAO;
        $this->NEI_DS_OBS_NOTA = $NEI_DS_OBS_NOTA;
        $this->NEI_ID_USR_AVALIADOR = $NEI_ID_USR_AVALIADOR;
        $this->NEI_CDT_SELECIONADO = $NEI_CDT_SELECIONADO;
        $this->NEI_NR_CLASSIFICACAO_CAND = $NEI_NR_CLASSIFICACAO_CAND;
        $this->NEI_ID_POLO_SELECIONADO = $NEI_ID_POLO_SELECIONADO;
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por remover todas as notas da etapa em questão
     * 
     * @param EtapaSelProc $etapa
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsLimparNotas($etapa, &$arrayCmds) {
        $arrayCmds [] = "delete from tb_nei_notas_etapa_sel_insc where ESP_ID_ETAPA_SEL = '{$etapa->getESP_ID_ETAPA_SEL()}'";
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por criar os registros de nota da etapa em questão
     * 
     * @param int $idChamada
     * @param int $idEtapaSel
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsRegistroNotaEtapa($idChamada, $idEtapaSel, &$arrayCmds) {
        $arrayCmds [] = "insert into tb_nei_notas_etapa_sel_insc (ESP_ID_ETAPA_SEL, IPR_ID_INSCRICAO, NEI_VL_TOTAL_NOTA, NEI_DT_AVALIACAO, NEI_ST_INSCRICAO, NEI_DS_OBS_NOTA, NEI_ID_USR_AVALIADOR, NEI_CDT_SELECIONADO, NEI_NR_CLASSIFICACAO_CAND, NEI_ID_POLO_SELECIONADO)
                        SELECT 
                            '$idEtapaSel', IPR_ID_INSCRICAO, IPR_VL_TOTAL_NOTA, IPR_DT_AVALIACAO, IPR_ST_INSCRICAO, IPR_DS_OBS_NOTA, IPR_ID_USR_AVALIADOR, IPR_CDT_SELECIONADO, IPR_NR_CLASSIFICACAO_CAND, IPR_ID_POLO_SELECIONADO
                        FROM
                            tb_ipr_inscricao_processo
                        WHERE
                            PCH_ID_CHAMADA = '$idChamada'";
    }

    /**
     * Esta função retorna o sql responsável por obter a nota dos candidato na etapa em questão.
     * 
     * É assumido que a tabela tb_ipr_inscricao_processo ipr já está incluída nas sqls pai
     * 
     * @param int $idEtapaSel
     * @return string SQL que retorna a nota do candidato na etapa específica
     */
    public static function CLAS_getSqlNotaInscEtapa($idEtapaSel) {
        return "(select NEI_VL_TOTAL_NOTA from tb_nei_notas_etapa_sel_insc nei where ipr.IPR_ID_INSCRICAO = nei.IPR_ID_INSCRICAO and ESP_ID_ETAPA_SEL = '$idEtapaSel')";
    }

    public static function getStrSqlExclusaoPorInscricao($idInscricao) {
        return "delete from tb_nei_notas_etapa_sel_insc where IPR_ID_INSCRICAO = '$idInscricao'";
    }

    public static function contarNotasEtapaSelInscPorUsuResp($idUsuResp) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando sql
            $sql = "SELECT 
                        count(*) as cont
                    from
                        tb_nei_notas_etapa_sel_insc
                    where
                        NEI_ID_USR_AVALIADOR = '$idUsuResp'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar notas de etapas por responsável.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getESP_ID_ETAPA_SEL() {
        return $this->ESP_ID_ETAPA_SEL;
    }

    /* End of get ESP_ID_ETAPA_SEL */

    function getIPR_ID_INSCRICAO() {
        return $this->IPR_ID_INSCRICAO;
    }

    /* End of get IPR_ID_INSCRICAO */

    function getNEI_VL_TOTAL_NOTA() {
        return $this->NEI_VL_TOTAL_NOTA;
    }

    /* End of get NEI_VL_TOTAL_NOTA */

    function getNEI_DT_AVALIACAO() {
        return $this->NEI_DT_AVALIACAO;
    }

    /* End of get NEI_DT_AVALIACAO */

    function getNEI_ST_INSCRICAO() {
        return $this->NEI_ST_INSCRICAO;
    }

    /* End of get NEI_ST_INSCRICAO */

    function getNEI_DS_OBS_NOTA() {
        return $this->NEI_DS_OBS_NOTA;
    }

    /* End of get NEI_DS_OBS_NOTA */

    function getNEI_ID_USR_AVALIADOR() {
        return $this->NEI_ID_USR_AVALIADOR;
    }

    /* End of get NEI_ID_USR_AVALIADOR */

    function getNEI_CDT_SELECIONADO() {
        return $this->NEI_CDT_SELECIONADO;
    }

    /* End of get NEI_CDT_SELECIONADO */

    function getNEI_NR_CLASSIFICACAO_CAND() {
        return $this->NEI_NR_CLASSIFICACAO_CAND;
    }

    /* End of get NEI_NR_CLASSIFICACAO_CAND */

    function getNEI_ID_POLO_SELECIONADO() {
        return $this->NEI_ID_POLO_SELECIONADO;
    }

    /* SET FIELDS FROM TABLE */

    function setESP_ID_ETAPA_SEL($value) {
        $this->ESP_ID_ETAPA_SEL = $value;
    }

    /* End of SET ESP_ID_ETAPA_SEL */

    function setIPR_ID_INSCRICAO($value) {
        $this->IPR_ID_INSCRICAO = $value;
    }

    /* End of SET IPR_ID_INSCRICAO */

    function setNEI_VL_TOTAL_NOTA($value) {
        $this->NEI_VL_TOTAL_NOTA = $value;
    }

    /* End of SET NEI_VL_TOTAL_NOTA */

    function setNEI_DT_AVALIACAO($value) {
        $this->NEI_DT_AVALIACAO = $value;
    }

    /* End of SET NEI_DT_AVALIACAO */

    function setNEI_ST_INSCRICAO($value) {
        $this->NEI_ST_INSCRICAO = $value;
    }

    /* End of SET NEI_ST_INSCRICAO */

    function setNEI_DS_OBS_NOTA($value) {
        $this->NEI_DS_OBS_NOTA = $value;
    }

    /* End of SET NEI_DS_OBS_NOTA */

    function setNEI_ID_USR_AVALIADOR($value) {
        $this->NEI_ID_USR_AVALIADOR = $value;
    }

    /* End of SET NEI_ID_USR_AVALIADOR */

    function setNEI_CDT_SELECIONADO($value) {
        $this->NEI_CDT_SELECIONADO = $value;
    }

    /* End of SET NEI_CDT_SELECIONADO */

    function setNEI_NR_CLASSIFICACAO_CAND($value) {
        $this->NEI_NR_CLASSIFICACAO_CAND = $value;
    }

    /* End of SET NEI_NR_CLASSIFICACAO_CAND */

    function setNEI_ID_POLO_SELECIONADO($NEI_ID_POLO_SELECIONADO) {
        $this->NEI_ID_POLO_SELECIONADO = $NEI_ID_POLO_SELECIONADO;
    }

}

?>
