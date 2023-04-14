<?php

/**
 * tb_fra_formacao_academica class
 * This class manipulates the table FormacaoAcademica
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * Modificado por      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 27/08/2013
 * */
global $CFG;
require_once $CFG->rpasta . "/persistencia/ConexaoMysql.php";
require_once $CFG->rpasta . "/util/NegocioException.php";
require_once $CFG->rpasta . "/negocio/NGUtil.php";
require_once $CFG->rpasta . "/negocio/Pais.php";

class FormacaoAcademica {

    private $FRA_ID_FORMACAO;
    private $CDT_ID_CANDIDATO;
    private $TPC_ID_TIPO_CURSO;
    private $FRA_NM_INSTITUICAO;
    private $FRA_NM_CURSO;
    private $FRA_ID_AREA_CONH;
    private $FRA_ID_SUBAREA_CONH;
    private $FRA_STATUS_CURSO;
    private $FRA_ANO_INICIO;
    private $FRA_ANO_CONCLUSAO;
    private $FRA_CARGA_HORARIA;
    private $FRA_TITULO_TRABALHO;
    private $FRA_ORIENTADOR_TRABALHO;
    private $FRA_ID_PAIS;
    private $FRA_ID_ESTADO;
    private $FRA_ID_CIDADE;
    private $FRA_CID_OUTRO_PAIS;
    private $FRA_URL_DOC_COMPROVACAO;
    private $FRA_ST_COMPROVACAO;
    private $FRA_OBS_COMPROVACAO;
    // campos herdados
    public $TPC_NM_TIPO_CURSO;
    public $PAI_NM_PAIS;
    public $EST_NM_ESTADO;
    public $CID_NM_CIDADE;
    public static $ST_FORMACAO_COMPLETO = 'C';
    public static $ST_FORMACAO_INCOMPLETO = 'I';
    public static $ST_FORMACAO_ANDAMENTO = 'A';
    // campos sob demanda
    private $NM_AREA_CONH;
    private $NM_SUBAREA_CONH;

    public static function getStFormacao($situacao) {
        if ($situacao == FormacaoAcademica::$ST_FORMACAO_COMPLETO) {
            return "Concluído";
        }
        if ($situacao == FormacaoAcademica::$ST_FORMACAO_ANDAMENTO) {
            return "Em andamento";
        }
        if ($situacao == FormacaoAcademica::$ST_FORMACAO_INCOMPLETO) {
            return "Incompleto";
        }
        return null;
    }

    public static function getListaSituacaoFormDsSituacao() {
        $ret = array(
            FormacaoAcademica::$ST_FORMACAO_COMPLETO => FormacaoAcademica::getStFormacao(FormacaoAcademica::$ST_FORMACAO_COMPLETO),
            FormacaoAcademica::$ST_FORMACAO_ANDAMENTO => FormacaoAcademica::getStFormacao(FormacaoAcademica::$ST_FORMACAO_ANDAMENTO),
            FormacaoAcademica::$ST_FORMACAO_INCOMPLETO => FormacaoAcademica::getStFormacao(FormacaoAcademica::$ST_FORMACAO_INCOMPLETO)
        );
        return $ret;
    }

    /* Construtor padrão da classe */

    public function __construct($FRA_ID_FORMACAO, $CDT_ID_CANDIDATO, $TPC_ID_TIPO_CURSO, $FRA_NM_INSTITUICAO, $FRA_NM_CURSO, $FRA_ID_AREA_CONH, $FRA_ID_SUBAREA_CONH, $FRA_STATUS_CURSO, $FRA_ANO_INICIO, $FRA_ANO_CONCLUSAO, $FRA_CARGA_HORARIA, $FRA_TITULO_TRABALHO, $FRA_ORIENTADOR_TRABALHO, $FRA_ID_PAIS, $FRA_ID_ESTADO, $FRA_ID_CIDADE, $FRA_CID_OUTRO_PAIS, $FRA_URL_DOC_COMPROVACAO = NULL, $FRA_ST_COMPROVACAO = NULL, $FRA_OBS_COMPROVACAO = NULL) {
        $this->FRA_ID_FORMACAO = $FRA_ID_FORMACAO;
        $this->CDT_ID_CANDIDATO = $CDT_ID_CANDIDATO;
        $this->TPC_ID_TIPO_CURSO = $TPC_ID_TIPO_CURSO;
        $this->FRA_NM_INSTITUICAO = $FRA_NM_INSTITUICAO;
        $this->FRA_NM_CURSO = $FRA_NM_CURSO;
        $this->FRA_ID_AREA_CONH = $FRA_ID_AREA_CONH;
        $this->FRA_ID_SUBAREA_CONH = $FRA_ID_SUBAREA_CONH;
        $this->FRA_STATUS_CURSO = $FRA_STATUS_CURSO;
        $this->FRA_ANO_INICIO = $FRA_ANO_INICIO;
        $this->FRA_ANO_CONCLUSAO = $FRA_ANO_CONCLUSAO;
        $this->FRA_CARGA_HORARIA = $FRA_CARGA_HORARIA;
        $this->FRA_TITULO_TRABALHO = $FRA_TITULO_TRABALHO;
        $this->FRA_ORIENTADOR_TRABALHO = $FRA_ORIENTADOR_TRABALHO;
        $this->FRA_ID_PAIS = $FRA_ID_PAIS;
        $this->FRA_ID_ESTADO = $FRA_ID_ESTADO;
        $this->FRA_ID_CIDADE = $FRA_ID_CIDADE;
        $this->FRA_CID_OUTRO_PAIS = $FRA_CID_OUTRO_PAIS;
        $this->FRA_URL_DOC_COMPROVACAO = $FRA_URL_DOC_COMPROVACAO;
        $this->FRA_ST_COMPROVACAO = $FRA_ST_COMPROVACAO;
        $this->FRA_OBS_COMPROVACAO = $FRA_OBS_COMPROVACAO;
    }

    public static function validarInsercaoFormacao($tpFormacao, $nmInstituicao, $nmCurso, $anoInicio, $idFormacao, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_fra_formacao_academica
                    where
                    TPC_ID_TIPO_CURSO = '$tpFormacao'
                    and FRA_NM_INSTITUICAO like '$nmInstituicao'
                    and FRA_ANO_INICIO = '$anoInicio' ";

            if (TipoCurso::isIdAdmiteCurso($tpFormacao)) {
                $sql .= " and FRA_NM_CURSO like '$nmCurso' ";
            }

            if (!Util::vazioNulo($idFormacao)) {
                $sql .= " and FRA_ID_FORMACAO != '$idFormacao' ";
            }

            $sql .= " and CDT_ID_CANDIDATO = (
                    select CDT_ID_CANDIDATO 
                    from tb_cdt_candidato
                    where `USR_ID_USUARIO` = '$idUsuario')";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando quantidade e retornando
            $quant = $conexao->getResult("cont", $resp);
            return $quant == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar inserção de formação do candidato.", $e);
        }
    }

    /**
     * Diz se o usuario preencheu sua formaçao
     * Base para resposta: Pelo menos uma formaçao cadastrada
     * @param int $idUsuario
     * @return boolean
     */
    public static function preencheuFormacao($idUsuario) {
        try {
            // recuperando conexao
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = "select 
                        count(*) as qt
                    from
                        tb_fra_formacao_academica
                    where
                        CDT_ID_CANDIDATO = (select 
                                CDT_ID_CANDIDATO
                            from
                                tb_cdt_candidato
                            where
                                USR_ID_USUARIO = '$idUsuario')";

            // executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("qt", $resp) > 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar preenchimento de formação.", $e);
        }
    }

    public static function getSqlExclusaoFormAcad($idCandidato) {
        $ret = "delete from tb_fra_formacao_academica
                where CDT_ID_CANDIDATO = '$idCandidato'";
        return $ret;
    }

    private function trataDadosBanco() {
        $this->FRA_NM_INSTITUICAO = NGUtil::trataCampoStrParaBD($this->FRA_NM_INSTITUICAO);
        if (TipoCurso::isIdAdmiteCurso(converteStrParaInt($this->TPC_ID_TIPO_CURSO))) {
            $this->FRA_NM_CURSO = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($this->FRA_NM_CURSO));
        } else {
            $this->FRA_NM_CURSO = "NULL";
        }

        $this->FRA_ANO_INICIO = NGUtil::trataCampoStrParaBD($this->FRA_ANO_INICIO);
        if ($this->FRA_STATUS_CURSO == FormacaoAcademica::$ST_FORMACAO_COMPLETO) {
            $this->FRA_ANO_CONCLUSAO = NGUtil::trataCampoStrParaBD($this->FRA_ANO_CONCLUSAO);
        } else {
            $this->FRA_ANO_CONCLUSAO = "NULL";
        }
        $this->FRA_STATUS_CURSO = NGUtil::trataCampoStrParaBD($this->FRA_STATUS_CURSO);
        if (TipoCurso::isIdAdmiteCargaHoraria(converteStrParaInt($this->TPC_ID_TIPO_CURSO))) {
            $this->FRA_CARGA_HORARIA = NGUtil::trataCampoStrParaBD($this->FRA_CARGA_HORARIA);
        } else {
            $this->FRA_CARGA_HORARIA = "NULL";
        }
        if (TipoCurso::isIdAdmiteAreaSubarea(converteStrParaInt($this->TPC_ID_TIPO_CURSO))) {
            // verificando preenchimento correto dos campos
            if (Util::vazioNulo($this->FRA_ID_AREA_CONH) || Util::vazioNulo($this->FRA_ID_SUBAREA_CONH)) {
                new Mensagem(Mensagem::$MSG_ERR_VAL_POS_AJAX, Mensagem::$MENSAGEM_ERRO);
            }
            $this->FRA_ID_AREA_CONH = NGUtil::trataCampoStrParaBD($this->FRA_ID_AREA_CONH);
            $this->FRA_ID_SUBAREA_CONH = NGUtil::trataCampoStrParaBD(converteStrParaInt($this->FRA_ID_SUBAREA_CONH));
        } else {
            $this->FRA_ID_AREA_CONH = $this->FRA_ID_SUBAREA_CONH = "NULL";
        }
        if (TipoCurso::isIdAdmiteDetalhamento(converteStrParaInt($this->TPC_ID_TIPO_CURSO))) {
            $this->FRA_TITULO_TRABALHO = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($this->FRA_TITULO_TRABALHO));
            $this->FRA_ORIENTADOR_TRABALHO = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($this->FRA_ORIENTADOR_TRABALHO));
        } else {
            $this->FRA_TITULO_TRABALHO = "NULL";
            $this->FRA_ORIENTADOR_TRABALHO = "NULL";
        }
        $this->TPC_ID_TIPO_CURSO = NGUtil::trataCampoStrParaBD($this->TPC_ID_TIPO_CURSO);

        if ($this->FRA_ID_PAIS == Pais::$PAIS_BRASIL) {
            $this->FRA_ID_ESTADO = NGUtil::trataCampoStrParaBD($this->FRA_ID_ESTADO);
            $this->FRA_ID_CIDADE = NGUtil::trataCampoStrParaBD($this->FRA_ID_CIDADE);
            $this->FRA_CID_OUTRO_PAIS = "NULL";
        } else {
            $this->FRA_CID_OUTRO_PAIS = NGUtil::trataCampoStrParaBD($this->FRA_CID_OUTRO_PAIS);
            $this->FRA_ID_ESTADO = "NULL";
            $this->FRA_ID_CIDADE = "NULL";
        }
        $this->FRA_ID_PAIS = NGUtil::trataCampoStrParaBD($this->FRA_ID_PAIS);

        //@todo Tratar aqui campos de upload

        $this->FRA_URL_DOC_COMPROVACAO = NGUtil::trataCampoStrParaBD($this->FRA_URL_DOC_COMPROVACAO);
        $this->FRA_ST_COMPROVACAO = NGUtil::trataCampoStrParaBD($this->FRA_ST_COMPROVACAO);
        $this->FRA_OBS_COMPROVACAO = NGUtil::trataCampoStrParaBD($this->FRA_OBS_COMPROVACAO);
    }

    /**
     * Cadastra uma formaçao no BD. 
     * Assume o preenchimento correto dos campos
     * @param int $idUsuario
     * @throws NegocioException
     */
    public function criarFormacao($idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // formação repetida?
            if (!self::validarInsercaoFormacao($this->TPC_ID_TIPO_CURSO, $this->FRA_NM_INSTITUICAO, $this->FRA_NM_CURSO, $this->FRA_ANO_INICIO, NULL, $idUsuario)) {
                throw new NegocioException("Formação já cadastrada!");
            }


            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando campos para o BD
            $this->CDT_ID_CANDIDATO = Candidato::getIdCandidatoPorIdUsuario($idUsuario);

            // tratando campos
            $this->trataDadosBanco();

            //montando sql de criação
            $sql = "insert into tb_fra_formacao_academica(`CDT_ID_CANDIDATO`, TPC_ID_TIPO_CURSO, `FRA_NM_INSTITUICAO`, `FRA_NM_CURSO`, `FRA_ID_AREA_CONH`, `FRA_ID_SUBAREA_CONH`, `FRA_STATUS_CURSO`, `FRA_ANO_INICIO`, `FRA_ANO_CONCLUSAO`, `FRA_CARGA_HORARIA`, `FRA_TITULO_TRABALHO`, FRA_ORIENTADOR_TRABALHO, FRA_ID_PAIS, FRA_ID_ESTADO, FRA_ID_CIDADE, FRA_CID_OUTRO_PAIS, FRA_URL_DOC_COMPROVACAO, FRA_ST_COMPROVACAO, FRA_OBS_COMPROVACAO, FRA_LOG_DT_ATUALIZACAO)
            values('$this->CDT_ID_CANDIDATO', $this->TPC_ID_TIPO_CURSO, $this->FRA_NM_INSTITUICAO, $this->FRA_NM_CURSO,$this->FRA_ID_AREA_CONH, $this->FRA_ID_SUBAREA_CONH, $this->FRA_STATUS_CURSO, $this->FRA_ANO_INICIO, $this->FRA_ANO_CONCLUSAO, $this->FRA_CARGA_HORARIA, $this->FRA_TITULO_TRABALHO, $this->FRA_ORIENTADOR_TRABALHO, $this->FRA_ID_PAIS, $this->FRA_ID_ESTADO, $this->FRA_ID_CIDADE, $this->FRA_CID_OUTRO_PAIS, $this->FRA_URL_DOC_COMPROVACAO, $this->FRA_ST_COMPROVACAO, $this->FRA_OBS_COMPROVACAO, now())";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //inserindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar formação do usuário.", $e);
        }
    }

    /**
     * Verifica se uma dada formaçao pertence ao usuario especificado
     * @param int $idFormacao
     * @param int $idUsuario
     * @return boolean
     * @throws NegocioException
     */
    private static function formacaoPertenceUsuario($idFormacao, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de verificaçao
            $sql = "select count(*) as cont from tb_fra_formacao_academica
                where FRA_ID_FORMACAO = '$idFormacao'
                and CDT_ID_CANDIDATO = (select 
                            CDT_ID_CANDIDATO
                        from
                            tb_cdt_candidato
                        WHERE
                            USR_ID_USUARIO = '$idUsuario')";

            //executando sql no banco
            $res = $conexao->execSqlComRetorno($sql);

            // recuperando dados e retornando
            return ConexaoMysql::getResult("cont", $res) == 1;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar usuário da formação.", $e);
        }
    }

    /**
     * Edita uma formaçao no BD. 
     * Assume o preenchimento correto dos campos
     * @param int $idUsuario
     * @throws NegocioException
     */
    public function editarFormacao($idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // verificando se nao pode editar
            if (!FormacaoAcademica::formacaoPertenceUsuario($this->FRA_ID_FORMACAO, $idUsuario) || !$this->permiteEdicao()) {
                throw new NegocioException("Permissão negada para a formação.");
            }

            // formação repetida?
            if (!self::validarInsercaoFormacao($this->TPC_ID_TIPO_CURSO, $this->FRA_NM_INSTITUICAO, $this->FRA_NM_CURSO, $this->FRA_ANO_INICIO, $this->FRA_ID_FORMACAO, $idUsuario)) {
                throw new NegocioException("Formação já cadastrada!");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando campos
            $this->trataDadosBanco();

            //montando sql de ediçao
            $sql = "update tb_fra_formacao_academica
                    set 
                        TPC_ID_TIPO_CURSO = $this->TPC_ID_TIPO_CURSO,
                        FRA_NM_INSTITUICAO = $this->FRA_NM_INSTITUICAO,
                        FRA_NM_CURSO = $this->FRA_NM_CURSO,
                        FRA_ID_AREA_CONH = $this->FRA_ID_AREA_CONH,
                        FRA_ID_SUBAREA_CONH = $this->FRA_ID_SUBAREA_CONH,
                        FRA_STATUS_CURSO = $this->FRA_STATUS_CURSO,
                        FRA_ANO_INICIO = $this->FRA_ANO_INICIO,
                        FRA_ANO_CONCLUSAO = $this->FRA_ANO_CONCLUSAO,
                        FRA_CARGA_HORARIA = $this->FRA_CARGA_HORARIA,
                        FRA_TITULO_TRABALHO = $this->FRA_TITULO_TRABALHO,
                        FRA_ORIENTADOR_TRABALHO = $this->FRA_ORIENTADOR_TRABALHO,
                        FRA_ID_PAIS = $this->FRA_ID_PAIS,
                        FRA_ID_ESTADO = $this->FRA_ID_ESTADO,
                        FRA_ID_CIDADE = $this->FRA_ID_CIDADE,
                        FRA_CID_OUTRO_PAIS = $this->FRA_CID_OUTRO_PAIS,
                        FRA_URL_DOC_COMPROVACAO = $this->FRA_URL_DOC_COMPROVACAO,
                        FRA_ST_COMPROVACAO = $this->FRA_ST_COMPROVACAO,
                        FRA_OBS_COMPROVACAO = $this->FRA_OBS_COMPROVACAO,
                        FRA_LOG_DT_ATUALIZACAO = now()    
                    where FRA_ID_FORMACAO = '$this->FRA_ID_FORMACAO'";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao editar formação do usuário.", $e);
        }
    }

    /**
     * Exclui uma formaçao do usuario
     * 
     * @param int $idFormacao
     * @param int $idUsuario
     * @throws NegocioException
     */
    public static function excluirFormacao($idFormacao, $idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // validando exclusao
            if (!FormacaoAcademica::formacaoPertenceUsuario($idFormacao, $idUsuario)) {
                throw new NegocioException("Permissão negada para a formação.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //validando campos
            //montando sql de remoção
            $sql = "delete from tb_fra_formacao_academica
                    where `FRA_ID_FORMACAO` = '$idFormacao'";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir formação do usuário.", $e);
        }
    }

    public static function buscarFormacaoPorIdUsuario($idUsuario, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    FRA_ID_FORMACAO,
                    CDT_ID_CANDIDATO,
                    fra.TPC_ID_TIPO_CURSO,
                    TPC_NM_TIPO_CURSO,
                    FRA_NM_INSTITUICAO,
                    FRA_NM_CURSO,
                    FRA_ID_AREA_CONH,
                    FRA_ID_SUBAREA_CONH,
                    FRA_STATUS_CURSO,
                    FRA_ANO_INICIO,
                    FRA_ANO_CONCLUSAO,
                    FRA_CARGA_HORARIA,
                    FRA_TITULO_TRABALHO,
                    FRA_ORIENTADOR_TRABALHO,
                    FRA_ID_PAIS,
                    FRA_ID_ESTADO,
                    FRA_ID_CIDADE,
                    FRA_CID_OUTRO_PAIS,
                    FRA_URL_DOC_COMPROVACAO,
                    FRA_ST_COMPROVACAO,
                    FRA_OBS_COMPROVACAO, 
                    PAI_NM_PAIS,
                    EST_NM_ESTADO,
                    CID_NM_CIDADE
                from
                    tb_fra_formacao_academica fra join tb_tpc_tipo_curso tpc
                    on fra.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
                    left join tb_pai_pais pai on pai.PAI_ISO = FRA_ID_PAIS
                    left join tb_est_estado est on est.EST_ID_UF = FRA_ID_ESTADO
                    left join tb_cid_cidade cid on cid.CID_ID_CIDADE = FRA_ID_CIDADE
                where
                    CDT_ID_CANDIDATO = (select 
                            CDT_ID_CANDIDATO
                        from
                            tb_cdt_candidato
                        WHERE
                            USR_ID_USUARIO = '$idUsuario')
                order by FRA_ANO_CONCLUSAO IS NULL desc, FRA_ANO_INICIO desc, FRA_ANO_CONCLUSAO desc";

            //questão de limite
            if ($qtdeDados != NULL) {
                $inicio = $inicioDados != NULL ? $inicioDados : 0;
                $sql .= " limit $inicio, $qtdeDados ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }

            $vetRetorno = array();

            //realizando iteração para recuperar as titulações
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $formacaoTemp = new FormacaoAcademica($dados['FRA_ID_FORMACAO'], $dados['CDT_ID_CANDIDATO'], $dados['TPC_ID_TIPO_CURSO'], $dados['FRA_NM_INSTITUICAO'], $dados['FRA_NM_CURSO'], $dados['FRA_ID_AREA_CONH'], $dados['FRA_ID_SUBAREA_CONH'], $dados['FRA_STATUS_CURSO'], $dados['FRA_ANO_INICIO'], $dados['FRA_ANO_CONCLUSAO'], $dados['FRA_CARGA_HORARIA'], $dados['FRA_TITULO_TRABALHO'], $dados['FRA_ORIENTADOR_TRABALHO'], $dados['FRA_ID_PAIS'], $dados['FRA_ID_ESTADO'], $dados['FRA_ID_CIDADE'], $dados['FRA_CID_OUTRO_PAIS'], $dados['FRA_URL_DOC_COMPROVACAO'], $dados['FRA_ST_COMPROVACAO'], $dados['FRA_OBS_COMPROVACAO']);

                // setando campos herdados
                $formacaoTemp->TPC_NM_TIPO_CURSO = $dados['TPC_NM_TIPO_CURSO'];
                $formacaoTemp->PAI_NM_PAIS = $dados['PAI_NM_PAIS'];
                $formacaoTemp->CID_NM_CIDADE = $dados['CID_NM_CIDADE'];
                $formacaoTemp->EST_NM_ESTADO = $dados['EST_NM_ESTADO'];

                //adicionando no vetor
                $vetRetorno[$i] = $formacaoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar formações do usuário.", $e);
        }
    }

    public static function buscarFormacaoPorIdCand($idCandidato, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    FRA_ID_FORMACAO,
                    CDT_ID_CANDIDATO,
                    fra.TPC_ID_TIPO_CURSO,
                    TPC_NM_TIPO_CURSO,
                    FRA_NM_INSTITUICAO,
                    FRA_NM_CURSO,
                    FRA_ID_AREA_CONH,
                    FRA_ID_SUBAREA_CONH,
                    FRA_STATUS_CURSO,
                    FRA_ANO_INICIO,
                    FRA_ANO_CONCLUSAO,
                    FRA_CARGA_HORARIA,
                    FRA_TITULO_TRABALHO,
                    FRA_ORIENTADOR_TRABALHO,
                    FRA_ID_PAIS,
                    FRA_ID_ESTADO,
                    FRA_ID_CIDADE,
                    FRA_CID_OUTRO_PAIS,
                    FRA_URL_DOC_COMPROVACAO,
                    FRA_ST_COMPROVACAO,
                    FRA_OBS_COMPROVACAO, 
                    PAI_NM_PAIS,
                    EST_NM_ESTADO,
                    CID_NM_CIDADE
                from
                    tb_fra_formacao_academica fra join tb_tpc_tipo_curso tpc
                    on fra.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
                    left join tb_pai_pais pai on pai.PAI_ISO = FRA_ID_PAIS
                    left join tb_est_estado est on est.EST_ID_UF = FRA_ID_ESTADO
                    left join tb_cid_cidade cid on cid.CID_ID_CIDADE = FRA_ID_CIDADE
                where
                    CDT_ID_CANDIDATO = (select 
                            CDT_ID_CANDIDATO
                        from
                            tb_cdt_candidato
                        WHERE
                            CDT_ID_CANDIDATO = '$idCandidato')
                order by FRA_ANO_CONCLUSAO IS NULL desc, FRA_ANO_INICIO desc, FRA_ANO_CONCLUSAO desc";

            //questão de limite
            if ($qtdeDados != NULL) {
                $inicio = $inicioDados != NULL ? $inicioDados : 0;
                $sql .= " limit $inicio, $qtdeDados ";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }

            $vetRetorno = array();

            //realizando iteração para recuperar as titulações
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $formacaoTemp = new FormacaoAcademica($dados['FRA_ID_FORMACAO'], $dados['CDT_ID_CANDIDATO'], $dados['TPC_ID_TIPO_CURSO'], $dados['FRA_NM_INSTITUICAO'], $dados['FRA_NM_CURSO'], $dados['FRA_ID_AREA_CONH'], $dados['FRA_ID_SUBAREA_CONH'], $dados['FRA_STATUS_CURSO'], $dados['FRA_ANO_INICIO'], $dados['FRA_ANO_CONCLUSAO'], $dados['FRA_CARGA_HORARIA'], $dados['FRA_TITULO_TRABALHO'], $dados['FRA_ORIENTADOR_TRABALHO'], $dados['FRA_ID_PAIS'], $dados['FRA_ID_ESTADO'], $dados['FRA_ID_CIDADE'], $dados['FRA_CID_OUTRO_PAIS'], $dados['FRA_URL_DOC_COMPROVACAO'], $dados['FRA_ST_COMPROVACAO'], $dados['FRA_OBS_COMPROVACAO']);

                // setando campos herdados
                $formacaoTemp->TPC_NM_TIPO_CURSO = $dados['TPC_NM_TIPO_CURSO'];
                $formacaoTemp->PAI_NM_PAIS = $dados['PAI_NM_PAIS'];
                $formacaoTemp->CID_NM_CIDADE = $dados['CID_NM_CIDADE'];
                $formacaoTemp->EST_NM_ESTADO = $dados['EST_NM_ESTADO'];

                //adicionando no vetor
                $vetRetorno[$i] = $formacaoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar formações do usuário.", $e);
        }
    }

    public static function contarFormacaoPorIdUsuario($idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_fra_formacao_academica
                    where CDT_ID_CANDIDATO = (
                    select CDT_ID_CANDIDATO 
                    from tb_cdt_candidato
                    where `USR_ID_USUARIO` = '$idUsuario')";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar formações do usuário.", $e);
        }
    }

    /**
     * Busca formaçao por id, verificando restriçao de usuario, ou seja, so busca a 
     * formacao caso ela pertença ao usuario informado. Se $idUsuario e nulo, entao
     * recupera a formaçao sem validar o usuario.
     * @param int $idFormacao
     * @param int $idUsuario
     * @return \FormacaoAcademica
     * @throws NegocioException
     */
    public static function buscarFormacaoPorIdFormacao($idFormacao, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    FRA_ID_FORMACAO,
                    CDT_ID_CANDIDATO,
                    fra.TPC_ID_TIPO_CURSO,
                    TPC_NM_TIPO_CURSO,
                    FRA_NM_INSTITUICAO,
                    FRA_NM_CURSO,
                    FRA_ID_AREA_CONH,
                    FRA_ID_SUBAREA_CONH,
                    FRA_STATUS_CURSO,
                    FRA_ANO_INICIO,
                    FRA_ANO_CONCLUSAO,
                    FRA_CARGA_HORARIA,
                    FRA_TITULO_TRABALHO,
                    FRA_ORIENTADOR_TRABALHO,
                    FRA_ID_PAIS,
                    FRA_ID_ESTADO,
                    FRA_ID_CIDADE,
                    FRA_CID_OUTRO_PAIS,
                    FRA_URL_DOC_COMPROVACAO,
                    FRA_ST_COMPROVACAO,
                    FRA_OBS_COMPROVACAO
                from tb_fra_formacao_academica fra join tb_tpc_tipo_curso tpc
                    on fra.TPC_ID_TIPO_CURSO = tpc.TPC_ID_TIPO_CURSO
                where `FRA_ID_FORMACAO` = '$idFormacao'";

            if (!Util::vazioNulo($idUsuario)) {
                $sql .= " and CDT_ID_CANDIDATO = (select CDT_ID_CANDIDATO from tb_cdt_candidato 
                where USR_ID_USUARIO = '$idUsuario')";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Formação não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $formacaoTemp = new FormacaoAcademica($dados['FRA_ID_FORMACAO'], $dados['CDT_ID_CANDIDATO'], $dados['TPC_ID_TIPO_CURSO'], $dados['FRA_NM_INSTITUICAO'], $dados['FRA_NM_CURSO'], $dados['FRA_ID_AREA_CONH'], $dados['FRA_ID_SUBAREA_CONH'], $dados['FRA_STATUS_CURSO'], $dados['FRA_ANO_INICIO'], $dados['FRA_ANO_CONCLUSAO'], $dados['FRA_CARGA_HORARIA'], $dados['FRA_TITULO_TRABALHO'], $dados['FRA_ORIENTADOR_TRABALHO'], $dados['FRA_ID_PAIS'], $dados['FRA_ID_ESTADO'], $dados['FRA_ID_CIDADE'], $dados['FRA_CID_OUTRO_PAIS'], $dados['FRA_URL_DOC_COMPROVACAO'], $dados['FRA_ST_COMPROVACAO'], $dados['FRA_OBS_COMPROVACAO']);
            // setando campos herdados
            $formacaoTemp->TPC_NM_TIPO_CURSO = $dados['TPC_NM_TIPO_CURSO'];

            return $formacaoTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar formação do usuário.", $e);
        }
    }

    public function getDsPeriodo() {
        if ($this->FRA_STATUS_CURSO == FormacaoAcademica::$ST_FORMACAO_COMPLETO) {
            return $this->FRA_ANO_INICIO . " a " . $this->FRA_ANO_CONCLUSAO;
        }
        if ($this->FRA_STATUS_CURSO == FormacaoAcademica::$ST_FORMACAO_INCOMPLETO) {
            return $this->FRA_ANO_INICIO . " (incompleto)";
        }
        return $this->FRA_ANO_INICIO . " até hoje";
    }

    public function getDsInstituicaoComp() {
        $ret = $this->FRA_NM_INSTITUICAO;
        if ($this->FRA_ID_PAIS == Pais::$PAIS_BRASIL) {
            $ret .= ", {$this->CID_NM_CIDADE} / {$this->FRA_ID_ESTADO} - {$this->PAI_NM_PAIS}";
        } else {
            $ret .= ", {$this->FRA_CID_OUTRO_PAIS} - {$this->PAI_NM_PAIS}";
        }
        return $ret;
    }

    public function getDsAreaSubarea() {
        // caso de nao ter subarea
        if (Util::vazioNulo($this->FRA_ID_AREA_CONH)) {
            return "";
        }
        // verificando se os campos ja foram carregados
        if (Util::vazioNulo($this->NM_AREA_CONH)) {
            // carregando campos
            $this->carregaNmAreaSubarea();
        }
        $ret = $this->NM_AREA_CONH;
        $ret .=!Util::vazioNulo($this->NM_SUBAREA_CONH) ? " - {$this->NM_SUBAREA_CONH}" : "";

        return $ret;
    }

    private function carregaNmAreaSubarea() {
        try {
            $area = buscarAreaConhPorIdCT($this->FRA_ID_AREA_CONH);
            $this->NM_AREA_CONH = $area->getARC_NM_AREA_CONH();

            $subArea = buscarAreaConhPorIdCT($this->FRA_ID_SUBAREA_CONH);

            $this->NM_SUBAREA_CONH = !Util::vazioNulo($subArea) ? $subArea->getARC_NM_AREA_CONH() : "";
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar nomes de área / subarea.", $e);
        }
    }

    public static function CLAS_getStrFormacao($idFormacao) {
        // buscando formacao
        $formacao = self::buscarFormacaoPorIdFormacao($idFormacao, NULL);
        $ret = "<b>Período / Formação: </b>{$formacao->getDsPeriodo()} - {$formacao->TPC_NM_TIPO_CURSO}" . NGUtil::$PULO_LINHA_HTML;
        $ret .="<b>Instituição: </b>" . $formacao->getDsInstituicaoComp() . NGUtil::$PULO_LINHA_HTML;
        if (!Util::vazioNulo($formacao->getFRA_NM_CURSO())) {
            $ret .= "<b>Curso: </b>" . $formacao->getFRA_NM_CURSO() . NGUtil::$PULO_LINHA_HTML;
        }
        if (!Util::vazioNulo($formacao->getFRA_ID_AREA_CONH())) {
            $ret .= "<b>Área: </b>" . $formacao->getDsAreaSubarea() . NGUtil::$PULO_LINHA_HTML;
        }
        if (!Util::vazioNulo($formacao->getFRA_CARGA_HORARIA())) {
            $ret .= "<b>Carga horária (hs): </b>" . $formacao->getFRA_CARGA_HORARIA() . NGUtil::$PULO_LINHA_HTML;
        }

        return $ret;
    }

    public function permiteEdicao() {
        //@todo Implementar regra de permissao de ediçao para upload
        return true;
    }

    /* GET FIELDS FROM TABLE */

    function getFRA_ID_FORMACAO() {
        return $this->FRA_ID_FORMACAO;
    }

    /* End of get FRA_ID_FORMACAO */

    function getCDT_ID_CANDIDATO() {
        return $this->CDT_ID_CANDIDATO;
    }

    /* End of get CDT_ID_CANDIDATO */

    function getTPC_ID_TIPO_CURSO() {
        return $this->TPC_ID_TIPO_CURSO;
    }

    /* End of get TPC_ID_TIPO_CURSO */

    function getFRA_NM_INSTITUICAO() {
        return $this->FRA_NM_INSTITUICAO;
    }

    /* End of get FRA_NM_INSTITUICAO */

    function getFRA_NM_CURSO($preencherVazio = FALSE) {
        // não admite curso: mostra vazio
        if (!TipoCurso::isIdAdmiteCurso($this->TPC_ID_TIPO_CURSO)) {
            return $preencherVazio ? Util::$STR_CAMPO_VAZIO : "";
        }
        return $this->FRA_NM_CURSO;
    }

    /* End of get FRA_NM_CURSO */

    function getFRA_ID_AREA_CONH() {
        return $this->FRA_ID_AREA_CONH;
    }

    /* End of get FRA_ID_AREA_CONH */

    function getFRA_ID_SUBAREA_CONH() {
        return $this->FRA_ID_SUBAREA_CONH;
    }

    /* End of get FRA_ID_SUBAREA_CONH */

    function getFRA_STATUS_CURSO() {
        return $this->FRA_STATUS_CURSO;
    }

    /* End of get FRA_STATUS_CURSO */

    function getFRA_ANO_INICIO() {
        return $this->FRA_ANO_INICIO;
    }

    /* End of get FRA_ANO_INICIO */

    function getFRA_ANO_CONCLUSAO() {
        return $this->FRA_ANO_CONCLUSAO;
    }

    /* End of get FRA_ANO_CONCLUSAO */

    function getFRA_CARGA_HORARIA() {
        return $this->FRA_CARGA_HORARIA;
    }

    /* End of get FRA_CARGA_HORARIA */

    function getFRA_TITULO_TRABALHO() {
        return $this->FRA_TITULO_TRABALHO;
    }

    /* End of get FRA_TITULO_TRABALHO */

    function getFRA_ORIENTADOR_TRABALHO() {
        return $this->FRA_ORIENTADOR_TRABALHO;
    }

    /* End of get FRA_ORIENTADOR_TRABALHO */

    function getFRA_ID_PAIS() {
        return $this->FRA_ID_PAIS;
    }

    /* End of get FRA_ID_PAIS */

    function getFRA_ID_ESTADO() {
        return $this->FRA_ID_ESTADO;
    }

    /* End of get FRA_ID_ESTADO */

    function getFRA_ID_CIDADE() {
        return $this->FRA_ID_CIDADE;
    }

    /* End of get FRA_ID_CIDADE */

    function getFRA_CID_OUTRO_PAIS() {
        return $this->FRA_CID_OUTRO_PAIS;
    }

    /* End of get FRA_CID_OUTRO_PAIS */

    function getFRA_URL_DOC_COMPROVACAO() {
        return $this->FRA_URL_DOC_COMPROVACAO;
    }

    /* End of get FRA_URL_DOC_COMPROVACAO */

    function getFRA_ST_COMPROVACAO() {
        return $this->FRA_ST_COMPROVACAO;
    }

    /* End of get FRA_ST_COMPROVACAO */

    function getFRA_OBS_COMPROVACAO() {
        return $this->FRA_OBS_COMPROVACAO;
    }

    /* End of get FRA_OBS_COMPROVACAO */



    /* SET FIELDS FROM TABLE */

    function setFRA_ID_FORMACAO($value) {
        $this->FRA_ID_FORMACAO = $value;
    }

    /* End of SET FRA_ID_FORMACAO */

    function setCDT_ID_CANDIDATO($value) {
        $this->CDT_ID_CANDIDATO = $value;
    }

    /* End of SET CDT_ID_CANDIDATO */

    function setTPC_ID_TIPO_CURSO($value) {
        $this->TPC_ID_TIPO_CURSO = $value;
    }

    /* End of SET TPC_ID_TIPO_CURSO */

    function setFRA_NM_INSTITUICAO($value) {
        $this->FRA_NM_INSTITUICAO = $value;
    }

    /* End of SET FRA_NM_INSTITUICAO */

    function setFRA_NM_CURSO($value) {
        $this->FRA_NM_CURSO = $value;
    }

    /* End of SET FRA_NM_CURSO */

    function setFRA_ID_AREA_CONH($value) {
        $this->FRA_ID_AREA_CONH = $value;
    }

    /* End of SET FRA_ID_AREA_CONH */

    function setFRA_ID_SUBAREA_CONH($value) {
        $this->FRA_ID_SUBAREA_CONH = $value;
    }

    /* End of SET FRA_ID_SUBAREA_CONH */

    function setFRA_STATUS_CURSO($value) {
        $this->FRA_STATUS_CURSO = $value;
    }

    /* End of SET FRA_STATUS_CURSO */

    function setFRA_ANO_INICIO($value) {
        $this->FRA_ANO_INICIO = $value;
    }

    /* End of SET FRA_ANO_INICIO */

    function setFRA_ANO_CONCLUSAO($value) {
        $this->FRA_ANO_CONCLUSAO = $value;
    }

    /* End of SET FRA_ANO_CONCLUSAO */

    function setFRA_CARGA_HORARIA($value) {
        $this->FRA_CARGA_HORARIA = $value;
    }

    /* End of SET FRA_CARGA_HORARIA */

    function setFRA_TITULO_TRABALHO($value) {
        $this->FRA_TITULO_TRABALHO = $value;
    }

    /* End of SET FRA_TITULO_TRABALHO */

    function setFRA_ORIENTADOR_TRABALHO($value) {
        $this->FRA_ORIENTADOR_TRABALHO = $value;
    }

    /* End of SET FRA_ORIENTADOR_TRABALHO */

    function setFRA_ID_PAIS($value) {
        $this->FRA_ID_PAIS = $value;
    }

    /* End of SET FRA_ID_PAIS */

    function setFRA_ID_ESTADO($value) {
        $this->FRA_ID_ESTADO = $value;
    }

    /* End of SET FRA_ID_ESTADO */

    function setFRA_ID_CIDADE($value) {
        $this->FRA_ID_CIDADE = $value;
    }

    /* End of SET FRA_ID_CIDADE */

    function setFRA_CID_OUTRO_PAIS($value) {
        $this->FRA_CID_OUTRO_PAIS = $value;
    }

    /* End of SET FRA_CID_OUTRO_PAIS */

    function setFRA_URL_DOC_COMPROVACAO($value) {
        $this->FRA_URL_DOC_COMPROVACAO = $value;
    }

    /* End of SET FRA_URL_DOC_COMPROVACAO */

    function setFRA_ST_COMPROVACAO($value) {
        $this->FRA_ST_COMPROVACAO = $value;
    }

    /* End of SET FRA_ST_COMPROVACAO */

    function setFRA_OBS_COMPROVACAO($value) {
        $this->FRA_OBS_COMPROVACAO = $value;
    }

    /* End of SET FRA_OBS_COMPROVACAO */
}

?>
