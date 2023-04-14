<?php

/**
 * tb_rvg_reserva_vaga class
 * This class manipulates the table ReservaVaga
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 17/10/2014
 * */
class ReservaVaga {

    private $RVG_ID_RESERVA_VAGA;
    private $RVG_NM_RESERVA_VAGA;
    private $RVG_DS_RESERVA_VAGA;
    private $RVG_ST_RESERVA_VAGA;

    /* Construtor padrão da classe */

    public function __construct($RVG_ID_RESERVA_VAGA, $RVG_NM_RESERVA_VAGA, $RVG_DS_RESERVA_VAGA, $RVG_ST_RESERVA_VAGA) {
        $this->RVG_ID_RESERVA_VAGA = $RVG_ID_RESERVA_VAGA;
        $this->RVG_NM_RESERVA_VAGA = $RVG_NM_RESERVA_VAGA;
        $this->RVG_DS_RESERVA_VAGA = $RVG_DS_RESERVA_VAGA;
        $this->RVG_ST_RESERVA_VAGA = $RVG_ST_RESERVA_VAGA;
    }

    public static function buscarTodasReservaVagasAtivas() {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $stReserva = NGUtil::getSITUACAO_ATIVO();

            $sql = "select 
                        RVG_ID_RESERVA_VAGA, RVG_NM_RESERVA_VAGA
                    from
                        tb_rvg_reserva_vaga
                    where RVG_ST_RESERVA_VAGA = '$stReserva'
                    order by RVG_NM_RESERVA_VAGA";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['RVG_ID_RESERVA_VAGA'];
                $valor = $dados['RVG_NM_RESERVA_VAGA'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar reserva de vagas.", $e);
        }
    }

    public static function buscarReservasVagasPorIds($idReservasVagas) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        RVG_ID_RESERVA_VAGA, RVG_NM_RESERVA_VAGA, RVG_DS_RESERVA_VAGA, RVG_ST_RESERVA_VAGA
                    from
                        tb_rvg_reserva_vaga
                    where RVG_ID_RESERVA_VAGA in ($idReservasVagas)
                    order by RVG_NM_RESERVA_VAGA";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                // criando objeto
                $reservaVagaTemp = new ReservaVaga($dados['RVG_ID_RESERVA_VAGA'], $dados['RVG_NM_RESERVA_VAGA'], $dados['RVG_DS_RESERVA_VAGA'], $dados['RVG_ST_RESERVA_VAGA']);

                //adicionando no vetor
                $vetRetorno[] = $reservaVagaTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar reserva de vagas.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getRVG_ID_RESERVA_VAGA() {
        return $this->RVG_ID_RESERVA_VAGA;
    }

    /* End of get RVG_ID_RESERVA_VAGA */

    function getRVG_NM_RESERVA_VAGA() {
        return $this->RVG_NM_RESERVA_VAGA;
    }

    /* End of get RVG_NM_RESERVA_VAGA */

    function getRVG_DS_RESERVA_VAGA() {
        return $this->RVG_DS_RESERVA_VAGA;
    }

    /* End of get RVG_DS_RESERVA_VAGA */

    function getRVG_ST_RESERVA_VAGA() {
        return $this->RVG_ST_RESERVA_VAGA;
    }

    /* End of get RVG_ST_RESERVA_VAGA */



    /* SET FIELDS FROM TABLE */

    function setRVG_ID_RESERVA_VAGA($value) {
        $this->RVG_ID_RESERVA_VAGA = $value;
    }

    /* End of SET RVG_ID_RESERVA_VAGA */

    function setRVG_NM_RESERVA_VAGA($value) {
        $this->RVG_NM_RESERVA_VAGA = $value;
    }

    /* End of SET RVG_NM_RESERVA_VAGA */

    function setRVG_DS_RESERVA_VAGA($value) {
        $this->RVG_DS_RESERVA_VAGA = $value;
    }

    /* End of SET RVG_DS_RESERVA_VAGA */

    function setRVG_ST_RESERVA_VAGA($value) {
        $this->RVG_ST_RESERVA_VAGA = $value;
    }

    /* End of SET RVG_ST_RESERVA_VAGA */
}

?>
