<?php

/**
 * tb_pin_polo_inscricao class
 * This class manipulates the table PoloInscricao
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 25/10/2013
 * */
class PoloInscricao {

    private $IPR_ID_INSCRICAO;
    private $POL_ID_POLO;
    private $PIN_NR_ORDEM;

    /* Construtor padrão da classe */

    public function __construct($IPR_ID_INSCRICAO, $POL_ID_POLO, $PIN_NR_ORDEM) {
        $this->IPR_ID_INSCRICAO = $IPR_ID_INSCRICAO;
        $this->POL_ID_POLO = $POL_ID_POLO;
        $this->PIN_NR_ORDEM = $PIN_NR_ORDEM;
    }

    /**
     * 
     * @param int $idInscricao
     * @param boolean $apenasPrimeiro Informa se é para retornar apenas o primeiro polo selecionado
     * 
     * @return mixed: Array com polos da inscrição na forma [idPolo => dsPolo] ou se $apenasPrimeiro é TRUE,
     *  é retornado o Id do pimeiro polo selecionado
     * @throws NegocioException
     */
    public static function buscarPoloPorInscricao($idInscricao, $apenasPrimeiro = FALSE) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select pol.`POL_ID_POLO` as POL_ID_POLO
                    , pol.POL_DS_POLO as POL_DS_POLO
                    from tb_pin_polo_inscricao pin
                    join tb_pol_polo pol on pin.POL_ID_POLO = pol.POL_ID_POLO
                    where `IPR_ID_INSCRICAO` = '$idInscricao'
                    order by PIN_NR_ORDEM";

            // apenas primeiro polo selecionado
            if ($apenasPrimeiro) {
                $sql .= " limit 0,1";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return $apenasPrimeiro ? NULL : array();
            }

            if (!$apenasPrimeiro) {

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
            } else {
                return ConexaoMysql::getResult("POL_ID_POLO", $resp);
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar polos da inscrição.", $e);
        }
    }

    public static function contarPolosPorInscricao($idInscricao) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select
                        count(*) as cont
                    from
                        tb_pin_polo_inscricao
                    where
                        IPR_ID_INSCRICAO = '$idInscricao'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar polos por inscrição.", $e);
        }
    }

    public static function contaInscPorPoloChamada($idChamada) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    POL_DS_POLO,
                    (select 
                            count(*)
                        from
                            tb_pin_polo_inscricao
                        where
                            POL_ID_POLO = ppc.POL_ID_POLO
                                and IPR_ID_INSCRICAO in (select 
                                    ipr_id_inscricao
                                from
                                    tb_ipr_inscricao_processo
                                where
                                    pch_id_chamada = '$idChamada')) as qtInscritos
                from
                    tb_ppc_polo_chamada ppc
                        join
                    tb_pol_polo pol ON ppc.POL_ID_POLO = pol.POL_ID_POLO
                where
                    PCH_ID_CHAMADA = '$idChamada'
                order by POL_DS_POLO";

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
                $chave = $dados['POL_DS_POLO'];
                $valor = $dados['qtInscritos'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar inscritos por polo.", $e);
        }
    }

    /**
     * Retorna array com sqls de inserçao de polos para inscriçao
     * @param array $listaPolos - Lista com ordem de opçoes
     * @param int $idInscricao
     * @return array
     */
    public static function getArraySqlInsercaoInsc($listaPolos, $idInscricao) {
        $i = 1;
        foreach ($listaPolos as $idPolo) {
            $ret [] = "insert into tb_pin_polo_inscricao(IPR_ID_INSCRICAO, POL_ID_POLO, PIN_NR_ORDEM) "
                    . "VALUES('$idInscricao', '$idPolo', '$i')";
            $i++;
        }
        return $ret;
    }

    public static function getStrSqlExclusaoPorInscricao($idInscricao) {
        return "delete from tb_pin_polo_inscricao where IPR_ID_INSCRICAO = $idInscricao";
    }

    /* GET FIELDS FROM TABLE */

    function getIPR_ID_INSCRICAO() {
        return $this->IPR_ID_INSCRICAO;
    }

    /* End of get IPR_ID_INSCRICAO */

    function getPOL_ID_POLO() {
        return $this->POL_ID_POLO;
    }

    /* End of get POL_ID_POLO */

    function getPIN_NR_ORDEM() {
        return $this->PIN_NR_ORDEM;
    }

    /* End of get PIN_NR_ORDEM */



    /* SET FIELDS FROM TABLE */

    function setIPR_ID_INSCRICAO($value) {
        $this->IPR_ID_INSCRICAO = $value;
    }

    /* End of SET IPR_ID_INSCRICAO */

    function setPOL_ID_POLO($value) {
        $this->POL_ID_POLO = $value;
    }

    /* End of SET POL_ID_POLO */

    function setPIN_NR_ORDEM($value) {
        $this->PIN_NR_ORDEM = $value;
    }

    /* End of SET PIN_NR_ORDEM */
}

?>
