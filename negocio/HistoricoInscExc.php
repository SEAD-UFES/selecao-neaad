<?php

/**
 * tb_hie_historico_insc_exc class
 * This class manipulates the table HistoricoInscExc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 07/02/2014
 * */
class HistoricoInscExc {

    private $HIE_ID_HISTORICO_EXCLUSAO;
    private $CDT_ID_CANDIDATO;
    private $PRC_ID_PROCESSO;
    private $PCH_ID_CHAMADA;
    private $HIE_IPR_ID_INSCRICAO;
    private $HIE_IPR_DT_INSCRICAO;
    private $HIE_IPR_NR_ORDEM_INSC;
    private $HIE_DS_MOTIVO_EXCLUSAO;
    private $HIE_DT_EXCLUSAO;
    private $HIE_ID_USUARIO_EXCLUSAO;

    /* Construtor padrão da classe */

    public function __construct($HIE_ID_HISTORICO_EXCLUSAO, $CDT_ID_CANDIDATO, $PRC_ID_PROCESSO, $PCH_ID_CHAMADA, $HIE_IPR_ID_INSCRICAO, $HIE_IPR_DT_INSCRICAO, $HIE_IPR_NR_ORDEM_INSC, $HIE_DS_MOTIVO_EXCLUSAO, $HIE_DT_EXCLUSAO, $HIE_ID_USUARIO_EXCLUSAO) {
        $this->HIE_ID_HISTORICO_EXCLUSAO = $HIE_ID_HISTORICO_EXCLUSAO;
        $this->CDT_ID_CANDIDATO = $CDT_ID_CANDIDATO;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->HIE_IPR_ID_INSCRICAO = $HIE_IPR_ID_INSCRICAO;
        $this->HIE_IPR_DT_INSCRICAO = $HIE_IPR_DT_INSCRICAO;
        $this->HIE_IPR_NR_ORDEM_INSC = $HIE_IPR_NR_ORDEM_INSC;
        $this->HIE_DS_MOTIVO_EXCLUSAO = $HIE_DS_MOTIVO_EXCLUSAO;
        $this->HIE_DT_EXCLUSAO = $HIE_DT_EXCLUSAO;
        $this->HIE_ID_USUARIO_EXCLUSAO = $HIE_ID_USUARIO_EXCLUSAO;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_hie_historico_insc_exc where PRC_ID_PROCESSO = '$idProcesso'";
    }

    public function getSqlInsercaoHistInscExc() {
        $dtBD = dt_dataHoraStrParaMysql($this->HIE_IPR_DT_INSCRICAO);
        $ret = "insert into tb_hie_historico_insc_exc (CDT_ID_CANDIDATO, PRC_ID_PROCESSO, PCH_ID_CHAMADA, HIE_IPR_ID_INSCRICAO, HIE_IPR_DT_INSCRICAO, HIE_IPR_NR_ORDEM_INSC, HIE_DS_MOTIVO_EXCLUSAO, HIE_DT_EXCLUSAO, HIE_ID_USUARIO_EXCLUSAO)
                values('{$this->CDT_ID_CANDIDATO}','{$this->PRC_ID_PROCESSO}','{$this->PCH_ID_CHAMADA}','{$this->HIE_IPR_ID_INSCRICAO}',$dtBD,'{$this->HIE_IPR_NR_ORDEM_INSC}','{$this->HIE_DS_MOTIVO_EXCLUSAO}',now(),'{$this->HIE_ID_USUARIO_EXCLUSAO}')";
        return $ret;
    }

    public static function contarHistInscExcPorUsuario($idUsuario) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
                        count(*) as cont
                    from
                        tb_hie_historico_insc_exc
                    where
                        HIE_ID_USUARIO_EXCLUSAO = '$idUsuario'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar histórico de exclusão de inscrições do usuário.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getHIE_ID_HISTORICO_EXCLUSAO() {
        return $this->HIE_ID_HISTORICO_EXCLUSAO;
    }

    /* End of get HIE_ID_HISTORICO_EXCLUSAO */

    function getCDT_ID_CANDIDATO() {
        return $this->CDT_ID_CANDIDATO;
    }

    /* End of get CDT_ID_CANDIDATO */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getHIE_IPR_ID_INSCRICAO() {
        return $this->HIE_IPR_ID_INSCRICAO;
    }

    /* End of get HIE_IPR_ID_INSCRICAO */

    function getHIE_IPR_DT_INSCRICAO() {
        return $this->HIE_IPR_DT_INSCRICAO;
    }

    /* End of get HIE_IPR_DT_INSCRICAO */

    function getHIE_IPR_NR_ORDEM_INSC() {
        return $this->HIE_IPR_NR_ORDEM_INSC;
    }

    /* End of get HIE_IPR_NR_ORDEM_INSC */

    function getHIE_DS_MOTIVO_EXCLUSAO() {
        return $this->HIE_DS_MOTIVO_EXCLUSAO;
    }

    /* End of get HIE_DS_MOTIVO_EXCLUSAO */

    function getHIE_DT_EXCLUSAO() {
        return $this->HIE_DT_EXCLUSAO;
    }

    /* End of get HIE_DT_EXCLUSAO */

    function getHIE_ID_USUARIO_EXCLUSAO() {
        return $this->HIE_ID_USUARIO_EXCLUSAO;
    }

    /* End of get HIE_ID_USUARIO_EXCLUSAO */



    /* SET FIELDS FROM TABLE */

    function setHIE_ID_HISTORICO_EXCLUSAO($value) {
        $this->HIE_ID_HISTORICO_EXCLUSAO = $value;
    }

    /* End of SET HIE_ID_HISTORICO_EXCLUSAO */

    function setCDT_ID_CANDIDATO($value) {
        $this->CDT_ID_CANDIDATO = $value;
    }

    /* End of SET CDT_ID_CANDIDATO */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setHIE_IPR_ID_INSCRICAO($value) {
        $this->HIE_IPR_ID_INSCRICAO = $value;
    }

    /* End of SET HIE_IPR_ID_INSCRICAO */

    function setHIE_IPR_DT_INSCRICAO($value) {
        $this->HIE_IPR_DT_INSCRICAO = $value;
    }

    /* End of SET HIE_IPR_DT_INSCRICAO */

    function setHIE_IPR_NR_ORDEM_INSC($value) {
        $this->HIE_IPR_NR_ORDEM_INSC = $value;
    }

    /* End of SET HIE_IPR_NR_ORDEM_INSC */

    function setHIE_DS_MOTIVO_EXCLUSAO($value) {
        $this->HIE_DS_MOTIVO_EXCLUSAO = $value;
    }

    /* End of SET HIE_DS_MOTIVO_EXCLUSAO */

    function setHIE_DT_EXCLUSAO($value) {
        $this->HIE_DT_EXCLUSAO = $value;
    }

    /* End of SET HIE_DT_EXCLUSAO */

    function setHIE_ID_USUARIO_EXCLUSAO($value) {
        $this->HIE_ID_USUARIO_EXCLUSAO = $value;
    }

    /* End of SET HIE_ID_USUARIO_EXCLUSAO */
}

?>
