<?php

/**
 * tb_tpc_tipo_curso class
 * This class manipulates the table TipoCurso
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 01/10/2013
 * 
 * */
class TipoCurso {

    private $TPC_ID_TIPO_CURSO;
    private $TPC_NR_ORDEM_EXIBICAO;
    private $TPC_NM_TIPO_CURSO;
    // Array com Id dos tipos que admitem o campo "Curso"
    private static $ID_ADMITE_CURSO = array(1, 2, 5, 6, 8, 9, 10, 11, 12);
    // Array com Id dos tipos que admitem o campo "Carga Horaria"
    private static $ID_ADMITE_CARGA_HORARIA = array(1, 2, 7, 8);
    // Array com Id dos tipos que admitem os campos de detalhamento
    private static $ID_ADMITE_DETALHAMENTO = array(1, 2, 6, 7, 8, 9, 10, 11, 12);
    // Array com Id dos tipos que admitem os campos de area e subarea
    private static $ID_ADMITE_AREA_SUBAREA = array(1, 2, 5, 6, 7, 8, 9, 10, 11, 12);
    // Id da Residencia medica
    private static $ID_RESIDENCIA_MEDICA = 7;

    public static function isIdAdmiteCurso($idTipo) {
        return array_search($idTipo, TipoCurso::$ID_ADMITE_CURSO) !== false;
    }

    public static function isIdAdmiteCargaHoraria($idTipo) {
        return array_search($idTipo, TipoCurso::$ID_ADMITE_CARGA_HORARIA) !== false;
    }

    public static function isIdAdmiteDetalhamento($idTipo) {
        return array_search($idTipo, TipoCurso::$ID_ADMITE_DETALHAMENTO) !== false;
    }

    public static function isIdAdmiteAreaSubarea($idTipo) {
        return array_search($idTipo, TipoCurso::$ID_ADMITE_AREA_SUBAREA) !== false;
    }

    // tipos para notas
    public static function getTpDoutorado() {
        return 11;
    }

    public static function getTpPosDoutorado() {
        return 12;
    }

    public static function getTpMestrado() {
        return 10;
    }

    public static function getTpMestradoProf() {
        return 9;
    }

    public static function getTpEspecializacao() {
        return 8;
    }

    public static function getTpEspecializacaoRes() {
        return 7;
    }

    public static function getTpGraduacao() {
        return 6;
    }

    public static function getTpAperfeicoamento() {
        return 1;
    }

    public static function getTpCapacitacao() {
        return 2;
    }

    /**
     * Retorna array no formato ['x1', 'x2',...] representando os Id's dos tipos que 
     * admitem o campo curso
     * @return string
     */
    public static function getListaAdmiteCurso() {
        return strArrayJavaScript(TipoCurso::$ID_ADMITE_CURSO);
    }

    /**
     * Retorna array no formato ['x1', 'x2',...] representando os Id's dos tipos que 
     * admitem o campo carga horaria
     * @return string
     */
    public static function getListaAdmiteCargaHoraria() {
        return strArrayJavaScript(TipoCurso::$ID_ADMITE_CARGA_HORARIA);
    }

    /**
     * Retorna array no formato ['x1', 'x2',...] representando os Id's dos tipos que 
     * admitem os campos de detalhamento
     * @return string
     */
    public static function getListaAdmiteDetalhamento() {
        return strArrayJavaScript(TipoCurso::$ID_ADMITE_DETALHAMENTO);
    }

    /**
     * Retorna array no formato ['x1', 'x2',...] representando os Id's dos tipos que 
     * admitem os campos de area / subarea
     * @return string
     */
    public static function getListaAdmiteAreaSubarea() {
        return strArrayJavaScript(TipoCurso::$ID_ADMITE_AREA_SUBAREA);
    }

    public static function getIdResidenciaMedica() {
        return TipoCurso::$ID_RESIDENCIA_MEDICA;
    }

    /* Construtor padrão da classe */

    public function __construct($TPC_ID_TIPO_CURSO, $TPC_NR_ORDEM_EXIBICAO, $TPC_NM_TIPO_CURSO) {
        $this->TPC_ID_TIPO_CURSO = $TPC_ID_TIPO_CURSO;
        $this->TPC_NR_ORDEM_EXIBICAO = $TPC_NR_ORDEM_EXIBICAO;
        $this->TPC_NM_TIPO_CURSO = $TPC_NM_TIPO_CURSO;
    }

    /**
     * Busca todos os tipos de curso, retornando um array da forma [id]=>[nome]
     * @return array
     * @throws NegocioException
     */
    public static function buscarTodosTiposCurso() {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        TPC_ID_TIPO_CURSO, TPC_NM_TIPO_CURSO
                    from
                        tb_tpc_tipo_curso
                    order by TPC_NR_ORDEM_EXIBICAO";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno;

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['TPC_ID_TIPO_CURSO'];
                $valor = $dados['TPC_NM_TIPO_CURSO'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar tipos de curso.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getTPC_ID_TIPO_CURSO() {
        return $this->TPC_ID_TIPO_CURSO;
    }

    /* End of get TPC_ID_TIPO_CURSO */

    function getTPC_NR_ORDEM_EXIBICAO() {
        return $this->TPC_NR_ORDEM_EXIBICAO;
    }

    /* End of get TPC_NR_ORDEM_EXIBICAO */

    function getTPC_NM_TIPO_CURSO() {
        return $this->TPC_NM_TIPO_CURSO;
    }

    /* End of get TPC_NM_TIPO_CURSO */



    /* SET FIELDS FROM TABLE */

    function setTPC_ID_TIPO_CURSO($value) {
        $this->TPC_ID_TIPO_CURSO = $value;
    }

    /* End of SET TPC_ID_TIPO_CURSO */

    function setTPC_NR_ORDEM_EXIBICAO($value) {
        $this->TPC_NR_ORDEM_EXIBICAO = $value;
    }

    /* End of SET TPC_NR_ORDEM_EXIBICAO */

    function setTPC_NM_TIPO_CURSO($value) {
        $this->TPC_NM_TIPO_CURSO = $value;
    }

    /* End of SET TPC_NM_TIPO_CURSO */
}

?>
