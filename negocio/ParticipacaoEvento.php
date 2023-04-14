<?php

/**
 * tb_pev_participacao_evento class
 * This class manipulates the table ParticipacaoEvento
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 09/05/2014
 * */
class ParticipacaoEvento {

    private $PEV_ID_PARTICIPACAO;
    private $CDT_ID_CANDIDATO;
    private $PEV_TP_ITEM;
    private $PEV_ID_AREA_CONH;
    private $PEV_ID_SUBAREA_CONH;
    private $PEV_QT_ITEM;
    private $PEV_LOG_DT_ATUALIZACAO;
    private $PEV_ST_COMPROVACAO;
    private $PEV_OBS_COMPROVACAO;
    public static $TIPO_APRESENTACAO_TRAB = 'A';
    public static $TIPO_OUVINTE = 'O';
    public static $TIPO_APRESENTACAO_CONF = 'C';
    public static $TIPO_SEMINARIO_TUT_EAD_UFES = 'E';
    // campos sob demanda
    private $NM_AREA_CONH;
    private $NM_SUBAREA_CONH;

    public static function getDsTipo($tipo) {
        if ($tipo == self::$TIPO_APRESENTACAO_TRAB) {
            return "Apresentação de Trabalho em Evento";
        }
        if ($tipo == self::$TIPO_OUVINTE) {
            return "Participação como Ouvinte em Evento";
        }
        if ($tipo == self::$TIPO_APRESENTACAO_CONF) {
            return "Apresentação de Trabalho como Conferencista";
        }
        if ($tipo == self::$TIPO_SEMINARIO_TUT_EAD_UFES) {
            return "Seminário de Formação de Tutores EAD-UFES";
        }
        return null;
    }

    public static function getDsTipoSemAcento($tipo) {
        if ($tipo == self::$TIPO_APRESENTACAO_TRAB) {
            return "Apresentacao de Trabalho em Evento";
        }
        if ($tipo == self::$TIPO_OUVINTE) {
            return "Participacao como Ouvinte em Evento";
        }
        if ($tipo == self::$TIPO_APRESENTACAO_CONF) {
            return "Apresentacao de Trabalho como Conferencista";
        }
        if ($tipo == self::$TIPO_SEMINARIO_TUT_EAD_UFES) {
            return "Seminario de Formacao de Tutores EAD-UFES";
        }
        return null;
    }

    public static function getTpFixarArea() {
        return self::$TIPO_SEMINARIO_TUT_EAD_UFES;
    }

    public static function getAreaFixa() {
        return AreaConhecimento::getAREA_OUTROS();
    }

    public static function getSubAreaFixa() {
        return AreaConhecimento::getSUBAREA_EAD();
    }

    public static function getListaTipoDsTipo() {
        $ret = array(
            self::$TIPO_APRESENTACAO_TRAB => self::getDsTipo(self::$TIPO_APRESENTACAO_TRAB),
            self::$TIPO_APRESENTACAO_CONF => self::getDsTipo(self::$TIPO_APRESENTACAO_CONF),
            self::$TIPO_SEMINARIO_TUT_EAD_UFES => self::getDsTipo(self::$TIPO_SEMINARIO_TUT_EAD_UFES),
            self::$TIPO_OUVINTE => self::getDsTipo(self::$TIPO_OUVINTE));

        return $ret;
    }

    public static function getDsUnidadeAdmin() {
        return "Pontuação por Publicação";
    }

    /* Construtor padrão da classe */

    public function __construct($PEV_ID_PARTICIPACAO, $CDT_ID_CANDIDATO, $PEV_TP_ITEM, $PEV_ID_AREA_CONH, $PEV_ID_SUBAREA_CONH, $PEV_QT_ITEM, $PEV_LOG_DT_ATUALIZACAO = NULL) {
        $this->PEV_ID_PARTICIPACAO = $PEV_ID_PARTICIPACAO;
        $this->CDT_ID_CANDIDATO = $CDT_ID_CANDIDATO;
        $this->PEV_TP_ITEM = $PEV_TP_ITEM;
        $this->PEV_ID_AREA_CONH = $PEV_ID_AREA_CONH;
        $this->PEV_ID_SUBAREA_CONH = $PEV_ID_SUBAREA_CONH;
        $this->PEV_QT_ITEM = $PEV_QT_ITEM;
        $this->PEV_LOG_DT_ATUALIZACAO = $PEV_LOG_DT_ATUALIZACAO;
    }

    /**
     * Busca participacao em evento por id, verificando restriçao de usuario, ou seja, so busca a 
     * participacao em evento caso ela pertença ao usuario informado. Se $idUsuario for null,
     * a funcao ignora a verificacao.
     * @param int $idPartEvento
     * @param int $idUsuario
     * @return \ParticipacaoEvento
     * @throws NegocioException
     */
    public static function buscarPartEventoPorId($idPartEvento, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        PEV_ID_PARTICIPACAO,
                        CDT_ID_CANDIDATO,
                        PEV_TP_ITEM,
                        PEV_ID_AREA_CONH,
                        PEV_ID_SUBAREA_CONH,
                        PEV_QT_ITEM,
                        DATE_FORMAT(`PEV_LOG_DT_ATUALIZACAO`, '%d/%m/%Y %T') as PEV_LOG_DT_ATUALIZACAO
                    from
                        tb_pev_participacao_evento
                        where `PEV_ID_PARTICIPACAO` = '$idPartEvento'";

            if (!Util::vazioNulo($idUsuario)) {
                $sql .=" and CDT_ID_CANDIDATO = (select CDT_ID_CANDIDATO from TB_CDT_CANDIDATO 
                        where USR_ID_USUARIO = '$idUsuario')";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Participação não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $partEventoTemp = new ParticipacaoEvento($dados['PEV_ID_PARTICIPACAO'], $dados['CDT_ID_CANDIDATO'], $dados['PEV_TP_ITEM'], $dados['PEV_ID_AREA_CONH'], $dados['PEV_ID_SUBAREA_CONH'], $dados['PEV_QT_ITEM'], $dados['PEV_LOG_DT_ATUALIZACAO']);

            return $partEventoTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar participação em eventos do usuário.", $e);
        }
    }

    public static function CLAS_getStrPartEvento($idPartEvento) {
        // buscando participacao em evento
        $partEvento = self::buscarPartEventoPorId($idPartEvento, NULL);
        $dsTipo = self::getDsTipo($partEvento->getPEV_TP_ITEM());
        $ret = "<b>Item: </b>$dsTipo" . NGUtil::$PULO_LINHA_HTML;
        $ret .= "<b>Área: </b>" . $partEvento->getDsAreaSubarea() . NGUtil::$PULO_LINHA_HTML;
        $ret .= "<b>Quantidade: </b>" . $partEvento->getPEV_QT_ITEM() . NGUtil::$PULO_LINHA_HTML;
        return $ret;
    }

    /**
     * Cadastra uma participaçao no BD. 
     * Assume o preenchimento correto dos campos
     * @param int $idUsuario
     * @throws NegocioException
     */
    public function criarPartEvento($idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // tratando campos
            $this->trataDadosBanco();

            // part. evento repetida?
            if (!self::validarInsercaoPartEvento($this->PEV_TP_ITEM, $this->PEV_ID_AREA_CONH, $this->PEV_ID_SUBAREA_CONH, $idUsuario, TRUE)) {
                throw new NegocioException("Part. Evento já cadastrada!");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando campos para o BD
            $this->CDT_ID_CANDIDATO = Candidato::getIdCandidatoPorIdUsuario($idUsuario);

            //montando sql de criação
            $sql = "insert into tb_pev_participacao_evento(`CDT_ID_CANDIDATO`, PEV_TP_ITEM, `PEV_ID_AREA_CONH`, `PEV_ID_SUBAREA_CONH`, PEV_QT_ITEM, `PEV_LOG_DT_ATUALIZACAO`)
            values('$this->CDT_ID_CANDIDATO', $this->PEV_TP_ITEM, $this->PEV_ID_AREA_CONH, $this->PEV_ID_SUBAREA_CONH, $this->PEV_QT_ITEM, now())";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //inserindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar participação em evento do usuário.", $e);
        }
    }

    /**
     * Exclui uma participacao do usuario
     * 
     * @param int $idPartEvento
     * @param int $idUsuario
     * @throws NegocioException
     */
    public static function excluirPartEvento($idPartEvento, $idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // validando exclusao
            if (!self::participacaoPertenceUsuario($idPartEvento, $idUsuario)) {
                throw new NegocioException("Permissão negada para a participação em evento.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //validando campos
            //montando sql de remoção
            $sql = "delete from tb_pev_participacao_evento
                    where `PEV_ID_PARTICIPACAO` = '$idPartEvento'";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir participação em evento do usuário.", $e);
        }
    }

    public static function getSqlExclusaoPartEvento($idCandidato) {
        $ret = "delete from tb_pev_participacao_evento
                where CDT_ID_CANDIDATO = '$idCandidato'";
        return $ret;
    }

    private function trataDadosBanco() {
        // caso de area obrigatoria
        if ($this->PEV_TP_ITEM == self::getTpFixarArea()) {
            $this->PEV_ID_AREA_CONH = self::getAreaFixa();
            $temp = self::getSubAreaFixa();
            $this->PEV_ID_SUBAREA_CONH = "'$temp'";
        }

        //tipo
        $this->PEV_TP_ITEM = NGUtil::trataCampoStrParaBD($this->PEV_TP_ITEM);

        // verificando preenchimento correto dos campos
        if (Util::vazioNulo($this->PEV_ID_AREA_CONH) || Util::vazioNulo($this->PEV_ID_SUBAREA_CONH)) {
            new Mensagem(Mensagem::$MSG_ERR_VAL_POS_AJAX, Mensagem::$MENSAGEM_ERRO);
        }
        $this->PEV_ID_AREA_CONH = NGUtil::trataCampoStrParaBD($this->PEV_ID_AREA_CONH);
        $this->PEV_ID_SUBAREA_CONH = NGUtil::trataCampoStrParaBD(converteStrParaInt($this->PEV_ID_SUBAREA_CONH));
    }

    public static function contarPartEventoPorIdUsuario($idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_pev_participacao_evento
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
            throw new NegocioException("Erro ao contar participações em evento do usuário.", $e);
        }
    }

    public static function validarInsercaoPartEvento($tpPartEvento, $idAreaConh, $idSubareaConh, $idUsuario, $posTratamento = FALSE) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            if (!$posTratamento) {
                $tpPartEvento = NGUtil::trataCampoStrParaBD($tpPartEvento);
                $idAreaConh = NGUtil::trataCampoStrParaBD($idAreaConh);
            }

            $sql = "select count(*) as cont
                    from tb_pev_participacao_evento
                    where
                    PEV_TP_ITEM = $tpPartEvento
                    and PEV_ID_AREA_CONH = $idAreaConh
                    and PEV_ID_SUBAREA_CONH = $idSubareaConh
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
            throw new NegocioException("Erro ao validar inserção de participação em evento do candidato.", $e);
        }
    }

    /**
     * Edita uma participacao no BD. 
     * Assume o preenchimento correto dos campos
     * @param int $idUsuario
     * @throws NegocioException
     */
    public function editarPartEvento($idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // verificando se nao pode editar
            if (!self::participacaoPertenceUsuario($this->PEV_ID_PARTICIPACAO, $idUsuario)) {
                throw new NegocioException("Permissão negada para a participação em evento.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de ediçao
            $sql = "update tb_pev_participacao_evento
                    set 
                        PEV_QT_ITEM = $this->PEV_QT_ITEM,
                        `PEV_LOG_DT_ATUALIZACAO` = now()
                    where PEV_ID_PARTICIPACAO = '$this->PEV_ID_PARTICIPACAO'";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao editar participação em evento do usuário.", $e);
        }
    }

    /**
     * Verifica se uma dada participacao pertence ao usuario especificado
     * @param int $idPartEvento
     * @param int $idUsuario
     * @return boolean
     * @throws NegocioException
     */
    private static function participacaoPertenceUsuario($idPartEvento, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de verificaçao
            $sql = "select count(*) as cont from tb_pev_participacao_evento
                where PEV_ID_PARTICIPACAO = '$idPartEvento'
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
            throw new NegocioException("Erro ao verificar usuário da participação em evento.", $e);
        }
    }

    public static function buscarPartEventoPorIdUsuario($idUsuario, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        PEV_ID_PARTICIPACAO,
                        CDT_ID_CANDIDATO,
                        PEV_TP_ITEM,
                        PEV_ID_AREA_CONH,
                        PEV_ID_SUBAREA_CONH,
                        PEV_QT_ITEM,
                        DATE_FORMAT(`PEV_LOG_DT_ATUALIZACAO`, '%d/%m/%Y %T') as PEV_LOG_DT_ATUALIZACAO
                    from tb_pev_participacao_evento
                    where
                    CDT_ID_CANDIDATO = (select 
                            CDT_ID_CANDIDATO
                        from
                            tb_cdt_candidato
                        WHERE
                            USR_ID_USUARIO = '$idUsuario')
                    order by PEV_TP_ITEM, PEV_QT_ITEM desc";

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

                $partEventoTemp = new ParticipacaoEvento($dados['PEV_ID_PARTICIPACAO'], $dados['CDT_ID_CANDIDATO'], $dados['PEV_TP_ITEM'], $dados['PEV_ID_AREA_CONH'], $dados['PEV_ID_SUBAREA_CONH'], $dados['PEV_QT_ITEM'], $dados['PEV_LOG_DT_ATUALIZACAO']);

                //adicionando no vetor
                $vetRetorno[$i] = $partEventoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar participações em eventos do usuário.", $e);
        }
    }

    public static function buscarPartEventoPorIdCand($idCandidato, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        PEV_ID_PARTICIPACAO,
                        CDT_ID_CANDIDATO,
                        PEV_TP_ITEM,
                        PEV_ID_AREA_CONH,
                        PEV_ID_SUBAREA_CONH,
                        PEV_QT_ITEM,
                        DATE_FORMAT(`PEV_LOG_DT_ATUALIZACAO`, '%d/%m/%Y %T') as PEV_LOG_DT_ATUALIZACAO
                    from tb_pev_participacao_evento
                    where
                    CDT_ID_CANDIDATO = '$idCandidato'
                    order by PEV_TP_ITEM, PEV_QT_ITEM desc";

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

                $partEventoTemp = new ParticipacaoEvento($dados['PEV_ID_PARTICIPACAO'], $dados['CDT_ID_CANDIDATO'], $dados['PEV_TP_ITEM'], $dados['PEV_ID_AREA_CONH'], $dados['PEV_ID_SUBAREA_CONH'], $dados['PEV_QT_ITEM'], $dados['PEV_LOG_DT_ATUALIZACAO']);

                //adicionando no vetor
                $vetRetorno[$i] = $partEventoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar participações em eventos do usuário.", $e);
        }
    }

    public function getDsAreaSubarea() {
        // caso de nao ter subarea
        if (Util::vazioNulo($this->PEV_ID_AREA_CONH)) {
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

            $area = buscarAreaConhPorIdCT($this->PEV_ID_AREA_CONH);
            $this->NM_AREA_CONH = $area->getARC_NM_AREA_CONH();

            $subArea = buscarAreaConhPorIdCT($this->PEV_ID_SUBAREA_CONH);

            $this->NM_SUBAREA_CONH = !Util::vazioNulo($subArea) ? $subArea->getARC_NM_AREA_CONH() : "";
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar nomes de área / subarea.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getPEV_ID_PARTICIPACAO() {
        return $this->PEV_ID_PARTICIPACAO;
    }

    /* End of get PEV_ID_PARTICIPACAO */

    function getCDT_ID_CANDIDATO() {
        return $this->CDT_ID_CANDIDATO;
    }

    /* End of get CDT_ID_CANDIDATO */

    function getPEV_TP_ITEM() {
        return $this->PEV_TP_ITEM;
    }

    /* End of get PEV_TP_ITEM */

    function getPEV_ID_AREA_CONH() {
        return $this->PEV_ID_AREA_CONH;
    }

    /* End of get PEV_ID_AREA_CONH */

    function getPEV_ID_SUBAREA_CONH() {
        return $this->PEV_ID_SUBAREA_CONH;
    }

    /* End of get PEV_ID_SUBAREA_CONH */

    function getPEV_QT_ITEM() {
        return $this->PEV_QT_ITEM;
    }

    /* End of get PEV_QT_ITEM */

    function getPEV_LOG_DT_ATUALIZACAO() {
        return $this->PEV_LOG_DT_ATUALIZACAO;
    }

    /* End of get PEV_LOG_DT_ATUALIZACAO */

    function getPEV_ST_COMPROVACAO() {
        return $this->PEV_ST_COMPROVACAO;
    }

    /* End of get PEV_ST_COMPROVACAO */

    function getPEV_OBS_COMPROVACAO() {
        return $this->PEV_OBS_COMPROVACAO;
    }

    /* End of get PEV_OBS_COMPROVACAO */


    /* SET FIELDS FROM TABLE */

    function setPEV_ID_PARTICIPACAO($value) {
        $this->PEV_ID_PARTICIPACAO = $value;
    }

    /* End of SET PEV_ID_PARTICIPACAO */

    function setCDT_ID_CANDIDATO($value) {
        $this->CDT_ID_CANDIDATO = $value;
    }

    /* End of SET CDT_ID_CANDIDATO */

    function setPEV_TP_ITEM($value) {
        $this->PEV_TP_ITEM = $value;
    }

    /* End of SET PEV_TP_ITEM */

    function setPEV_ID_AREA_CONH($value) {
        $this->PEV_ID_AREA_CONH = $value;
    }

    /* End of SET PEV_ID_AREA_CONH */

    function setPEV_ID_SUBAREA_CONH($value) {
        $this->PEV_ID_SUBAREA_CONH = $value;
    }

    /* End of SET PEV_ID_SUBAREA_CONH */

    function setPEV_QT_ITEM($value) {
        $this->PEV_QT_ITEM = $value;
    }

    /* End of SET PEV_QT_ITEM */

    function setPEV_LOG_DT_ATUALIZACAO($value) {
        $this->PEV_LOG_DT_ATUALIZACAO = $value;
    }

    /* End of SET PEV_LOG_DT_ATUALIZACAO */

    function setPEV_ST_COMPROVACAO($value) {
        $this->PEV_ST_COMPROVACAO = $value;
    }

    /* End of SET PEV_ST_COMPROVACAO */

    function setPEV_OBS_COMPROVACAO($value) {
        $this->PEV_OBS_COMPROVACAO = $value;
    }

    /* End of SET PEV_OBS_COMPROVACAO */
}

?>
