<?php

/**
 * tb_atu_atuacao class
 * This class manipulates the table Atuacao
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 09/05/2014
 * */
class Atuacao {

    private $ATU_ID_ATUACAO;
    private $CDT_ID_CANDIDATO;
    private $ATU_TP_ITEM;
    private $ATU_ID_AREA_CONH;
    private $ATU_ID_SUBAREA_CONH;
    private $ATU_QT_ITEM;
    private $ATU_LOG_DT_ATUALIZACAO;
    private $ATU_ST_COMPROVACAO;
    private $ATU_OBS_COMPROVACAO;
    public static $TIPO_EXP_DOCENTE = 'B';
    public static $TIPO_EXP_PROF_GERENCIAL = 'G';
    public static $TIPO_CONSULTORIA = 'I';
    public static $TIPO_TUTORIA = 'D';
    public static $TIPO_EXP_PROF_GERAL = 'C';
    public static $TIPO_PROJ_EXT = 'Q';
    public static $TIPO_PROJ_PESQ = 'P';
    public static $TIPO_MONITORIA = 'R';
    public static $TIPO_PRODUCAO_TEC = 'S';
    // tipos de label
    public static $LABEL_EM_ANOS = "A";
    public static $LABEL_EM_SEMESTRES = "S";
    public static $LABEL_EM_QUANTIDADE = "Q";
    // campos sob demanda
    private $NM_AREA_CONH;
    private $NM_SUBAREA_CONH;

    public static function getDsTipo($tipo) {
        if ($tipo == self::$TIPO_EXP_DOCENTE) {
            return "Experiência Profissional Docente";
        }
        if ($tipo == self::$TIPO_EXP_PROF_GERENCIAL) {
            return "Experiência Profissional Gerencial";
        }
        if ($tipo == self::$TIPO_TUTORIA) {
            return "Tutoria";
        }
        if ($tipo == self::$TIPO_CONSULTORIA) {
            return "Consultoria";
        }
        if ($tipo == self::$TIPO_EXP_PROF_GERAL) {
            return "Outra Experiência Profissional";
        }
        if ($tipo == self::$TIPO_PROJ_EXT) {
            return "Projeto de Extensão";
        }
        if ($tipo == self::$TIPO_PROJ_PESQ) {
            return "Projeto de Pesquisa";
        }
        if ($tipo == self::$TIPO_MONITORIA) {
            return "Monitoria";
        }
        if ($tipo == self::$TIPO_PRODUCAO_TEC) {
            return "Produção Técnica";
        }
        return null;
    }

    public static function getDsTipoSemAcento($tipo) {
        if ($tipo == self::$TIPO_EXP_DOCENTE) {
            return "Experiencia Profissional Docente";
        }
        if ($tipo == self::$TIPO_EXP_PROF_GERENCIAL) {
            return "Experiencia Profissional Gerencial";
        }
        if ($tipo == self::$TIPO_TUTORIA) {
            return "Tutoria";
        }
        if ($tipo == self::$TIPO_CONSULTORIA) {
            return "Consultoria";
        }
        if ($tipo == self::$TIPO_EXP_PROF_GERAL) {
            return"Outra Experiencia Profissional";
        }
        if ($tipo == self::$TIPO_PROJ_EXT) {
            return "Projeto de Extensao";
        }
        if ($tipo == self::$TIPO_PROJ_PESQ) {
            return "Projeto de Pesquisa";
        }
        if ($tipo == self::$TIPO_MONITORIA) {
            return "Monitoria";
        }
        if ($tipo == self::$TIPO_PRODUCAO_TEC) {
            return "Producao Tecnica";
        }
        return null;
    }

    public static function getListaTipoDsTipo($tpLabel = NULL) {
        $ret = array(
            self::$TIPO_EXP_DOCENTE => self::getDsTipo(self::$TIPO_EXP_DOCENTE),
            self::$TIPO_EXP_PROF_GERENCIAL => self::getDsTipo(self::$TIPO_EXP_PROF_GERENCIAL),
            self::$TIPO_EXP_PROF_GERAL => self::getDsTipo(self:: $TIPO_EXP_PROF_GERAL),
            self::$TIPO_TUTORIA => self::getDsTipo(self:: $TIPO_TUTORIA),
            self::$TIPO_PROJ_PESQ => self::getDsTipo(self:: $TIPO_PROJ_PESQ),
            self::$TIPO_PROJ_EXT => self::getDsTipo(self:: $TIPO_PROJ_EXT),
            self::$TIPO_MONITORIA => self::getDsTipo(self::$TIPO_MONITORIA),
            self::$TIPO_PRODUCAO_TEC => self::getDsTipo(self::$TIPO_PRODUCAO_TEC),
            self::$TIPO_CONSULTORIA => self::getDsTipo(self::$TIPO_CONSULTORIA));

        if ($tpLabel != NULL) {
            $temp = array();
            foreach ($ret as $id => $ds) {
                if ($tpLabel == self::$LABEL_EM_ANOS) {
                    if (self::exibeLabelAnos($id)) {
                        $temp[$id] = $ds;
                    }
                } elseif ($tpLabel == self::$LABEL_EM_SEMESTRES) {
                    if (self::exibeLabelSemestres($id)) {
                        $temp[$id] = $ds;
                    }
                } elseif ($tpLabel == self::$LABEL_EM_QUANTIDADE) {
                    if (!self::exibeLabelAnos($id) && !self::exibeLabelSemestres($id)) {
                        $temp[$id] = $ds;
                    }
                }
            }
            $ret = $temp;
        }

        return $ret;
    }

    public static function exibeLabelAnos($tpAtuacao) {
        return Atuacao::$TIPO_EXP_DOCENTE == $tpAtuacao || Atuacao::$TIPO_EXP_PROF_GERAL == $tpAtuacao || Atuacao::$TIPO_TUTORIA == $tpAtuacao || Atuacao::$TIPO_EXP_PROF_GERENCIAL == $tpAtuacao;
    }

    public static function exibeLabelSemestres($tpAtuacao) {
        return Atuacao::$TIPO_PROJ_PESQ == $tpAtuacao || Atuacao::$TIPO_PROJ_EXT == $tpAtuacao || Atuacao::$TIPO_MONITORIA == $tpAtuacao;
    }

    public static function getDsUnidadeAdmin() {
        $ret = "";

        // percorrendo tipos 
        foreach (self:: getListaTipoDsTipo(self::$LABEL_EM_ANOS) as $id => $ds) {
            $ret = adicionaConteudoVirgula($ret, "$ds");
        }
        $ret .= ": Em  Anos |  ";

        $tmp = "";
        foreach (self::getListaTipoDsTipo(self::$LABEL_EM_SEMESTRES) as $id => $ds) {
            $tmp = adicionaConteudoVirgula($tmp, "$ds");
        }
        $ret .= "$tmp: Em Semest res | ";

        $tmp = "";
        foreach (self::getListaTipoDsTipo(self::$LABEL_EM_QUANTIDADE) as $id => $ds) {
            $tmp = adicionaConteudoVirgula($tmp, "$ds");
        }

        $ret .= "$tmp: Em Quantidade";

        return $ret;
    }

    /* Construtor padrão da classe */

    public function __construct($ATU_ID_ATUACAO, $CDT_ID_CANDIDATO, $ATU_TP_ITEM, $ATU_ID_AREA_CONH, $ATU_ID_SUBAREA_CONH, $ATU_QT_ITEM, $ATU_LOG_DT_ATUALIZACAO = NULL) {
        $this->ATU_ID_ATUACAO = $ATU_ID_ATUACAO;
        $this->CDT_ID_CANDIDATO = $CDT_ID_CANDIDATO;
        $this->ATU_TP_ITEM = $ATU_TP_ITEM;
        $this->ATU_ID_AREA_CONH = $ATU_ID_AREA_CONH;
        $this->ATU_ID_SUBAREA_CONH = $ATU_ID_SUBAREA_CONH;
        $this->ATU_QT_ITEM = $ATU_QT_ITEM;
        $this->ATU_LOG_DT_ATUALIZACAO = $ATU_LOG_DT_ATUALIZACAO;
    }

    /**
     * Cadastra uma atuacao no BD. 
     * Assume o preenchimento correto dos campos
     * @param int $idUsuario
     * @throws NegocioException
     */
    public function criarAtuacao($idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // atuação repetida?
            if (!self::validarInsercaoAtuacao($this->ATU_TP_ITEM, $this->ATU_ID_AREA_CONH, $this->ATU_ID_SUBAREA_CONH, $idUsuario)) {
                throw new NegocioException("Atuação já cadastrada!");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando campos para o BD
            $this->CDT_ID_CANDIDATO = Candidato::getIdCandidatoPorIdUsuario($idUsuario);

            // tratando campos
            $this->trataDadosBanco();

            //montando sql de criação
            $sql = "insert into tb_atu_atuacao(`CDT_ID_CANDIDATO`, ATU_TP_ITEM, `ATU_ID_AREA_CONH`, `ATU_ID_SUBAREA_CONH`, ATU_QT_ITEM, `ATU_LOG_DT_ATUALIZACAO`)
            values('$this->CDT_ID_CANDIDATO', $this->ATU_TP_ITEM, $this->ATU_ID_AREA_CONH, $this->ATU_ID_SUBAREA_CONH, $this->ATU_QT_ITEM, now())";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //inserindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {

            throw new NegocioException("Erro ao cadastrar atuação do usuário.", $e);
        }
    }

    public static function CLAS_getStrAtuacao($idAtuacao) {
        // buscando atuacao
        $atuacao = self::buscarAtuacaoPorId($idAtuacao, NULL);
        $dsTipo = self::getDsTipo($atuacao->getATU_TP_ITEM());
        $ret = "<b>Item: </b>$dsTipo" . NGUtil::$PULO_LINHA_HTML;
        $ret .= "<b>Área: </b>" . $atuacao->getDsAreaSubarea() . NGUtil::$PULO_LINHA_HTML;
        $ret .= "<b>Quantidade: </b>" . $atuacao->getATU_QT_ITEM() . NGUtil::$PULO_LINHA_HTML;
        return $ret;
    }

    /**
     * Exclui uma atuacao do usuario
     * 
     * @param int $idAtuacao
     * @param int $idUsuario
     * @throws NegocioException
     */
    public static function excluirAtuacao($idAtuacao, $idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // validando exclusao
            if (!self::atuacaoPertenceUsuario($idAtuacao, $idUsuario)) {
                throw new NegocioException("Permissão negada para a atuação.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //validando campos
            //montando sql de remoção
            $sql = "delete from tb_atu_atuacao
                    where `ATU_ID_ATUACAO` = '$idAtuacao'";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {

            throw new NegocioException("Erro ao excluir atuação do usuário.", $e);
        }
    }

    public static function getSqlExclusaoAtuacao($idCandidato) {
        $ret = "delete from tb_atu_atuacao
                where CDT_ID_CANDIDATO = '$idCandidato'";
        return $ret;
    }

    private function trataDadosBanco() {
        //tipo
        $this->ATU_TP_ITEM = NGUtil::trataCampoStrParaBD($this->ATU_TP_ITEM);

        // verificando preenchimento correto dos campos
        if (Util::vazioNulo($this->ATU_ID_AREA_CONH) || Util::vazioNulo($this->ATU_ID_SUBAREA_CONH)) {
            new Mensagem(Mensagem::$MSG_ERR_VAL_POS_AJAX, Mensagem::$MENSAGEM_ERRO);
        }
        $this->ATU_ID_AREA_CONH = NGUtil::trataCampoStrParaBD($this->ATU_ID_AREA_CONH);
        $this->ATU_ID_SUBAREA_CONH = NGUtil::trataCampoStrParaBD(converteStrParaInt($this->ATU_ID_SUBAREA_CONH));
    }

    public static function contarAtuacaoPorIdUsuario($idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_atu_atuacao
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

            throw new NegocioException("Erro ao contar Atuações do usuário.", $e);
        }
    }

    public static function validarInsercaoAtuacao($tpAtuacao, $idAreaConh, $idSubareaConh, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_atu_atuacao
                    where
                    ATU_TP_ITEM = '$tpAtuacao'
                    and ATU_ID_AREA_CONH = '$idAreaConh'
                    and ATU_ID_SUBAREA_CONH = $idSubareaConh
                    and CDT_ID_CANDIDATO = (
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
            throw new NegocioException("Erro ao validar inserção de atuação do candidato.", $e);
        }
    }

    /**
     * Busca atuacao por id, verificando restriçao de usuario, ou seja, so busca a 
     * atuacao caso ela pertença ao usuario informado. Se $idUsuario e nulo, 
     * entao a verificacao e ignorada
     * @param int $idAtuacao
     * @param int $idUsuario
     * @return \Publicacao
     * @throws NegocioException
     */
    public static function buscarAtuacaoPorId($idAtuacao, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    ATU_ID_ATUACAO,
                    CDT_ID_CANDIDATO,
                    ATU_TP_ITEM,
                    ATU_ID_AREA_CONH,
                    ATU_ID_SUBAREA_CONH,
                    ATU_QT_ITEM,
                    DATE_FORMAT(`ATU_LOG_DT_ATUALIZACAO`, '%d/%m/%Y %T') as ATU_LOG_DT_ATUALIZACAO,
                    ATU_ST_COMPROVACAO,
                    ATU_OBS_COMPROVACAO
                from
                    tb_atu_atuacao
                        where `ATU_ID_ATUACAO` = '$idAtuacao'";

            if (!Util::vazioNulo($idUsuario)) {
                $sql .= " and CDT_ID_CANDIDATO = (select CDT_ID_CANDIDATO from TB_CDT_CANDIDATO 
                        where USR_ID_USUARIO = '$idUsuario')";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Atuação não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql:: getLinha($resp);
            $atuacaoTemp = new Atuacao($dados['ATU_ID_ATUACAO'], $dados ['CDT_ID_CANDIDATO'], $dados['ATU_TP_ITEM'], $dados['ATU_ID_AREA_CONH'], $dados['ATU_ID_SUBAREA_CONH'], $dados['ATU_QT_ITEM']);

            return $atuacaoTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar atuação do usuário.", $e);
        }
    }

    /**
     * Edita uma atuacao no BD. 
     * Assume o preenchimento correto dos campos
     * @param int $idUsuario
     * @throws NegocioException
     */
    public function editarAtuacao($idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // verificando se nao pode editar
            if (!self::atuacaoPertenceUsuario($this->ATU_ID_ATUACAO, $idUsuario)) {
                throw new NegocioException("Permissão negada para a atuação.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de ediçao
            $sql = "update tb_atu_atuacao
                    set 
                        ATU_QT_ITEM = $this->ATU_QT_ITEM,
                        `ATU_LOG_DT_ATUALIZACAO` = now()
                    where ATU_ID_ATUACAO = '$this->ATU_ID_ATUACAO'";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao editar atuação do usuário.", $e);
        }
    }

    /**
     * Verifica se uma dada atuacao pertence ao usuario especificado
     * @param int $idAtuacao
     * @param int $idUsuario
     * @return boolean
     * @throws NegocioException
     */
    private static function atuacaoPertenceUsuario($idAtuacao, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de verificaçao
            $sql = "select count(*) as cont from tb_atu_atuacao
                where ATU_ID_ATUACAO = '$idAtuacao'
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

            throw new NegocioException("Erro ao verificar usuário da atuação.", $e);
        }
    }

    public static function buscarAtuacaoPorIdUsuario($idUsuario, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    ATU_ID_ATUACAO,
                    CDT_ID_CANDIDATO,
                    ATU_TP_ITEM,
                    ATU_ID_AREA_CONH,
                    ATU_ID_SUBAREA_CONH,
                    ATU_QT_ITEM,
                    DATE_FORMAT(`ATU_LOG_DT_ATUALIZACAO`, '%d/%m/%Y %T') as ATU_LOG_DT_ATUALIZACAO,
                    ATU_ST_COMPROVACAO,
                    ATU_OBS_COMPROVACAO
                from
                    tb_atu_atuacao
                    where
                    CDT_ID_CANDIDATO = (select 
                            CDT_ID_CANDIDATO
                        from
                            tb_cdt_candidato
                        WHERE
                            USR_ID_USUARIO = '$idUsuario')
                    order by ATU_TP_ITEM, ATU_QT_ITEM desc";

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

            //realizando iteração para recuperar dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);
                $atuacaoTemp = new Atuacao($dados['ATU_ID_ATUACAO'], $dados ['CDT_ID_CANDIDATO'], $dados['ATU_TP_ITEM'], $dados['ATU_ID_AREA_CONH'], $dados['ATU_ID_SUBAREA_CONH'], $dados['ATU_QT_ITEM']);

                //adicionando no vetor
                $vetRetorno[$i] = $atuacaoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {

            throw new NegocioException("Erro ao buscar atuações do usuário.", $e);
        }
    }

    public static function buscarAtuacaoPorIdCand($idCandidato, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    ATU_ID_ATUACAO,
                    CDT_ID_CANDIDATO,
                    ATU_TP_ITEM,
                    ATU_ID_AREA_CONH,
                    ATU_ID_SUBAREA_CONH,
                    ATU_QT_ITEM,
                    DATE_FORMAT(`ATU_LOG_DT_ATUALIZACAO`, '%d/%m/%Y %T') as ATU_LOG_DT_ATUALIZACAO,
                    ATU_ST_COMPROVACAO,
                    ATU_OBS_COMPROVACAO
                from
                    tb_atu_atuacao
                    where
                    CDT_ID_CANDIDATO = '$idCandidato'
                    order by ATU_TP_ITEM, ATU_QT_ITEM desc";

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

            //realizando iteração para recuperar dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);
                $atuacaoTemp = new Atuacao($dados['ATU_ID_ATUACAO'], $dados ['CDT_ID_CANDIDATO'], $dados['ATU_TP_ITEM'], $dados['ATU_ID_AREA_CONH'], $dados['ATU_ID_SUBAREA_CONH'], $dados['ATU_QT_ITEM']);

                //adicionando no vetor
                $vetRetorno[$i] = $atuacaoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {

            throw new NegocioException("Erro ao buscar atuações do usuário.", $e);
        }
    }

    public function getDsAreaSubarea() {
// caso de nao ter subarea
        if (Util::vazioNulo($this->ATU_ID_AREA_CONH)) {
            return "";
        }
        // verificando se os campos ja foram carregados
        if (Util::vazioNulo($this->NM_AREA_CONH)) {
            // carregando campos
            $this->carregaNmAreaSubarea();
        }
        $ret = $this->NM_AREA_CONH;
        $ret .=!Util::vazioNulo($this->NM_SUBAREA_CONH) ? " - {$this->NM_SUBAREA_CONH}" :
                "";

        return $ret;
    }

    private function carregaNmAreaSubarea() {
        try {

            $area = buscarAreaConhPorIdCT($this->ATU_ID_AREA_CONH);
            $this->NM_AREA_CONH = $area->getARC_NM_AREA_CONH();

            $subArea = buscarAreaConhPorIdCT($this->ATU_ID_SUBAREA_CONH);

            $this->NM_SUBAREA_CONH = !Util::vazioNulo($subArea) ? $subArea->getARC_NM_AREA_CONH() : "";
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException(
            "Erro ao carregar nomes de área / subarea.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function

    getATU_ID_ATUACAO() {
        return $this->ATU_ID_ATUACAO;
    }

    /* End of get ATU_ID_ATUACAO */

    function

    getCDT_ID_CANDIDATO() {
        return $this->CDT_ID_CANDIDATO;
    }

    /* End of get CDT_ID_CANDIDATO */

    function getATU_TP_ITEM() {
        return $this->ATU_TP_ITEM;
    }

    /* End of get ATU_TP_ITEM */

    function

    getATU_ID_AREA_CONH() {
        return $this->ATU_ID_AREA_CONH;
    }

    /* End of get ATU_ID_AREA_CONH */

    function

    getATU_ID_SUBAREA_CONH() {
        return $this->ATU_ID_SUBAREA_CONH;
    }

    /* End of get ATU_ID_SUBAREA_CONH */

    function getATU_QT_ITEM() {
        return $this->ATU_QT_ITEM;
    }

    /* End of get ATU_QT_ITEM */

    function getATU_LOG_DT_ATUALIZACAO() {

        return $this->ATU_LOG_DT_ATUALIZACAO;
    }

    /* End of get ATU_LOG_DT_ATUALIZACAO */

    function

    getATU_ST_COMPROVACAO() {
        return $this->ATU_ST_COMPROVACAO;
    }

    /* End of get ATU_ST_COMPROVACAO */

    function getATU_OBS_COMPROVACAO() {
        return $this->
                ATU_OBS_COMPROVACAO;
    }

    /* End of get ATU_OBS_COMPROVACAO */


    /* SET FIELDS FROM TABLE */

    function setATU_ID_ATUACAO(
    $value) {
        $this->ATU_ID_ATUACAO = $value;
    }

    /* End of SET ATU_ID_ATUACAO */

    function setCDT_ID_CANDIDATO(
    $value) {
        $this->CDT_ID_CANDIDATO = $value;
    }

    /* End of SET CDT_ID_CANDIDATO */

    function

    setATU_TP_ITEM($value) {
        $this->ATU_TP_ITEM = $value;
    }

    /* End of SET ATU_TP_ITEM */

    function setATU_ID_AREA_CONH(
    $value) {
        $this->ATU_ID_AREA_CONH = $value;
    }

    /* End of SET ATU_ID_AREA_CONH */

    function setATU_ID_SUBAREA_CONH($value
    ) {
        $this->ATU_ID_SUBAREA_CONH = $value;
    }

    /* End of SET ATU_ID_SUBAREA_CONH */

    function

    setATU_QT_ITEM($value) {
        $this->ATU_QT_ITEM = $value;
    }

    /* End of SET ATU_QT_ITEM */

    function setATU_LOG_DT_ATUALIZACAO($value) {

        $this->ATU_LOG_DT_ATUALIZACAO = $value;
    }

    /* End of SET ATU_LOG_DT_ATUALIZACAO */

    function setATU_ST_COMPROVACAO(
    $value) {
        $this->ATU_ST_COMPROVACAO = $value;
    }

    /* End of SET ATU_ST_COMPROVACAO */

    function setATU_OBS_COMPROVACAO($value) {
        $this->ATU_OBS_COMPROVACAO = $value;
    }

    /* End of SET ATU_OBS_COMPROVACAO */
}

?>
