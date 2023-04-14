<?php

/**
 * tb_pub_publicacao class
 * This class manipulates the table Publicacao
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 07/05/2014
 * */
class Publicacao {

    private $PUB_ID_PUBLICACAO;
    private $CDT_ID_CANDIDATO;
    private $PUB_TP_ITEM;
    private $PUB_ID_AREA_CONH;
    private $PUB_ID_SUBAREA_CONH;
    private $PUB_QT_ITEM;
    private $PUB_ST_COMPROVACAO;
    private $PUB_OBS_COMPROVACAO;
    public static $TIPO_LIVRO = 'B';
    public static $TIPO_CAP_LIVRO = 'C';
    public static $TIPO_ART_REVISTA = 'J';
    public static $TIPO_ART_EVENTO = 'O';
    public static $TIPO_RESUMO_ART = 'R';
    // campos sob demanda
    private $NM_AREA_CONH;
    private $NM_SUBAREA_CONH;

    public static function getDsTipo($tipo) {
        if ($tipo == self::$TIPO_LIVRO) {
            return "Livro";
        }
        if ($tipo == self::$TIPO_CAP_LIVRO) {
            return "Capítulo de Livro";
        }
        if ($tipo == self::$TIPO_ART_REVISTA) {
            return "Artigo Completo em Revista";
        }
        if ($tipo == self::$TIPO_ART_EVENTO) {
            return "Artigo Completo em Evento";
        }
        if ($tipo == self::$TIPO_RESUMO_ART) {
            return "Resumo de Artigo em Evento";
        }
        return null;
    }

    public static function getDsTipoSemAcento($tipo) {
        if ($tipo == self::$TIPO_LIVRO) {
            return "Livro";
        }
        if ($tipo == self::$TIPO_CAP_LIVRO) {
            return "Capitulo de Livro";
        }
        if ($tipo == self::$TIPO_ART_REVISTA) {
            return "Artigo Completo em Revista";
        }
        if ($tipo == self::$TIPO_ART_EVENTO) {
            return "Artigo Completo em Evento";
        }
        if ($tipo == self::$TIPO_RESUMO_ART) {
            return "Resumo de Artigo em Evento";
        }
        return null;
    }

    public static function getListaTipoDsTipo() {
        $ret = array(
            self::$TIPO_LIVRO => self::getDsTipo(self::$TIPO_LIVRO),
            self::$TIPO_CAP_LIVRO => self::getDsTipo(self::$TIPO_CAP_LIVRO),
            self::$TIPO_ART_REVISTA => self::getDsTipo(self::$TIPO_ART_REVISTA),
            self::$TIPO_ART_EVENTO => self::getDsTipo(self::$TIPO_ART_EVENTO),
            self::$TIPO_RESUMO_ART => self::getDsTipo(self::$TIPO_RESUMO_ART));

        return $ret;
    }

    public static function getDsUnidadeAdmin() {
        return "Pontuação por Participação / Apresentação";
    }

    /* Construtor padrão da classe */

    public function __construct($PUB_ID_PUBLICACAO, $CDT_ID_CANDIDATO, $PUB_TP_ITEM, $PUB_ID_AREA_CONH, $PUB_ID_SUBAREA_CONH, $PUB_QT_ITEM) {
        $this->PUB_ID_PUBLICACAO = $PUB_ID_PUBLICACAO;
        $this->CDT_ID_CANDIDATO = $CDT_ID_CANDIDATO;
        $this->PUB_TP_ITEM = $PUB_TP_ITEM;
        $this->PUB_ID_AREA_CONH = $PUB_ID_AREA_CONH;
        $this->PUB_ID_SUBAREA_CONH = $PUB_ID_SUBAREA_CONH;
        $this->PUB_QT_ITEM = $PUB_QT_ITEM;
    }

    /**
     * Cadastra uma publicaçao no BD. 
     * Assume o preenchimento correto dos campos
     * @param int $idUsuario
     * @throws NegocioException
     */
    public function criarPublicacao($idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // publicacao repetida?
            if (!self::validarInsercaoPublicacao($this->PUB_TP_ITEM, $this->PUB_ID_AREA_CONH, $this->PUB_ID_SUBAREA_CONH, $idUsuario)) {
                throw new NegocioException("Publicação já cadastrada!");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando campos para o BD
            $this->CDT_ID_CANDIDATO = Candidato::getIdCandidatoPorIdUsuario($idUsuario);

            // tratando campos
            $this->trataDadosBanco();

            //montando sql de criação
            $sql = "insert into tb_pub_publicacao(`CDT_ID_CANDIDATO`, PUB_TP_ITEM, `PUB_ID_AREA_CONH`, `PUB_ID_SUBAREA_CONH`, PUB_QT_ITEM, `PUB_LOG_DT_ATUALIZACAO`)
            values('$this->CDT_ID_CANDIDATO', $this->PUB_TP_ITEM, $this->PUB_ID_AREA_CONH, $this->PUB_ID_SUBAREA_CONH, $this->PUB_QT_ITEM, now())";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //inserindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar publicação do usuário.", $e);
        }
    }

    /**
     * Exclui uma publicaçao do usuario
     * 
     * @param int $idPublicacao
     * @param int $idUsuario
     * @throws NegocioException
     */
    public static function excluirPublicacao($idPublicacao, $idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // validando exclusao
            if (!self::publicacaoPertenceUsuario($idPublicacao, $idUsuario)) {
                throw new NegocioException("Permissão negada para a publicação.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //validando campos
            //montando sql de remoção
            $sql = "delete from tb_pub_publicacao
                    where `PUB_ID_PUBLICACAO` = '$idPublicacao'";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir publicação do usuário.", $e);
        }
    }

    public static function getSqlExclusaoPublicacao($idCandidato) {
        $ret = "delete from tb_pub_publicacao
                where CDT_ID_CANDIDATO = '$idCandidato'";
        return $ret;
    }

    private function trataDadosBanco() {
        //tipo
        $this->PUB_TP_ITEM = NGUtil::trataCampoStrParaBD($this->PUB_TP_ITEM);

        // verificando preenchimento correto dos campos
        if (Util::vazioNulo($this->PUB_ID_AREA_CONH) || Util::vazioNulo($this->PUB_ID_SUBAREA_CONH)) {
            new Mensagem(Mensagem::$MSG_ERR_VAL_POS_AJAX, Mensagem::$MENSAGEM_ERRO);
        }
        $this->PUB_ID_AREA_CONH = NGUtil::trataCampoStrParaBD($this->PUB_ID_AREA_CONH);
        $this->PUB_ID_SUBAREA_CONH = NGUtil::trataCampoStrParaBD(converteStrParaInt($this->PUB_ID_SUBAREA_CONH));
    }

    public static function contarPublicacaoPorIdUsuario($idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_pub_publicacao
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
            throw new NegocioException("Erro ao contar publicações do usuário.", $e);
        }
    }

    public static function validarInsercaoPublicacao($tpPublicacao, $idAreaConh, $idSubareaConh, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_pub_publicacao
                    where
                    PUB_TP_ITEM = '$tpPublicacao'
                    and PUB_ID_AREA_CONH = '$idAreaConh'
                    and PUB_ID_SUBAREA_CONH = $idSubareaConh
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
            throw new NegocioException("Erro ao validar inserção de publicação do candidato.", $e);
        }
    }

    /**
     * Busca publicacao por id, verificando restriçao de usuario, ou seja, so busca a 
     * publicacao caso ela pertença ao usuario informado. Se $idUsuario e nulo, entao
     * recupera a publicacao sem validar o usuario.
     * @param int $idPublicacao
     * @param int $idUsuario
     * @return \Publicacao
     * @throws NegocioException
     */
    public static function buscarPublicacaoPorId($idPublicacao, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        PUB_ID_PUBLICACAO,
                        CDT_ID_CANDIDATO,
                        PUB_TP_ITEM,
                        PUB_ID_AREA_CONH,
                        PUB_ID_SUBAREA_CONH,
                        PUB_QT_ITEM
                    from
                        tb_pub_publicacao
                        where `PUB_ID_PUBLICACAO` = '$idPublicacao'";

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
                throw new NegocioException("Publicação não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $publicacaoTemp = new Publicacao($dados['PUB_ID_PUBLICACAO'], $dados['CDT_ID_CANDIDATO'], $dados['PUB_TP_ITEM'], $dados['PUB_ID_AREA_CONH'], $dados['PUB_ID_SUBAREA_CONH'], $dados['PUB_QT_ITEM']);

            return $publicacaoTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar publicação do usuário.", $e);
        }
    }

    public static function CLAS_getStrPublicacao($idPublicacao) {
        // buscando publicacao
        $publicacao = self::buscarPublicacaoPorId($idPublicacao, NULL);
        $dsTipo = self::getDsTipo($publicacao->getPUB_TP_ITEM());
        $ret = "<b>Item: </b>$dsTipo" . NGUtil::$PULO_LINHA_HTML;
        $ret .= "<b>Área: </b>" . $publicacao->getDsAreaSubarea() . NGUtil::$PULO_LINHA_HTML;
        $ret .= "<b>N° de Publicações: </b>" . $publicacao->getPUB_QT_ITEM() . NGUtil::$PULO_LINHA_HTML;
        return $ret;
    }

    /**
     * Edita uma publicaçao no BD. 
     * Assume o preenchimento correto dos campos
     * @param int $idUsuario
     * @throws NegocioException
     */
    public function editarPublicacao($idUsuario) {
        try {

            //validando
            if (!Candidato::permiteAlteracaoCurriculo(buscarIdCandPorIdUsuCT($idUsuario))) {
                throw new NegocioException("Operação em currículo não permitida.");
            }

            // verificando se nao pode editar
            if (!self::publicacaoPertenceUsuario($this->PUB_ID_PUBLICACAO, $idUsuario)) {
                throw new NegocioException("Permissão negada para a publicação.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de ediçao
            $sql = "update tb_pub_publicacao
                    set 
                        PUB_QT_ITEM = $this->PUB_QT_ITEM,
                        `PUB_LOG_DT_ATUALIZACAO` = now()
                    where PUB_ID_PUBLICACAO = '$this->PUB_ID_PUBLICACAO'";

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao editar publicação do usuário.", $e);
        }
    }

    /**
     * Verifica se uma dada publicacao pertence ao usuario especificado
     * @param int $idPublicacao
     * @param int $idUsuario
     * @return boolean
     * @throws NegocioException
     */
    private static function publicacaoPertenceUsuario($idPublicacao, $idUsuario) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de verificaçao
            $sql = "select count(*) as cont from tb_pub_publicacao
                where PUB_ID_PUBLICACAO = '$idPublicacao'
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
            throw new NegocioException("Erro ao verificar usuário da publicação.", $e);
        }
    }

    public static function buscarPublicacaoPorIdUsuario($idUsuario, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        PUB_ID_PUBLICACAO,
                        CDT_ID_CANDIDATO,
                        PUB_TP_ITEM,
                        PUB_ID_AREA_CONH,
                        PUB_ID_SUBAREA_CONH,
                        PUB_QT_ITEM
                    from
                        tb_pub_publicacao
                    where
                    CDT_ID_CANDIDATO = (select 
                            CDT_ID_CANDIDATO
                        from
                            tb_cdt_candidato
                        WHERE
                            USR_ID_USUARIO = '$idUsuario')
                    order by PUB_TP_ITEM, PUB_QT_ITEM desc";

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

                $publicacaoTemp = new Publicacao($dados['PUB_ID_PUBLICACAO'], $dados['CDT_ID_CANDIDATO'], $dados['PUB_TP_ITEM'], $dados['PUB_ID_AREA_CONH'], $dados['PUB_ID_SUBAREA_CONH'], $dados['PUB_QT_ITEM']);

                //adicionando no vetor
                $vetRetorno[$i] = $publicacaoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar publicações do usuário.", $e);
        }
    }

    public static function buscarPublicacaoPorIdCand($idCandidato, $inicioDados, $qtdeDados) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        PUB_ID_PUBLICACAO,
                        CDT_ID_CANDIDATO,
                        PUB_TP_ITEM,
                        PUB_ID_AREA_CONH,
                        PUB_ID_SUBAREA_CONH,
                        PUB_QT_ITEM
                    from
                        tb_pub_publicacao
                    where
                    CDT_ID_CANDIDATO = '$idCandidato'
                    order by PUB_TP_ITEM, PUB_QT_ITEM desc";

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

                $publicacaoTemp = new Publicacao($dados['PUB_ID_PUBLICACAO'], $dados['CDT_ID_CANDIDATO'], $dados['PUB_TP_ITEM'], $dados['PUB_ID_AREA_CONH'], $dados['PUB_ID_SUBAREA_CONH'], $dados['PUB_QT_ITEM']);

                //adicionando no vetor
                $vetRetorno[$i] = $publicacaoTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar publicações do usuário.", $e);
        }
    }

    public function getDsAreaSubarea() {
        // caso de nao ter subarea
        if (Util::vazioNulo($this->PUB_ID_AREA_CONH)) {
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

            $area = buscarAreaConhPorIdCT($this->PUB_ID_AREA_CONH);
            $this->NM_AREA_CONH = $area->getARC_NM_AREA_CONH();

            $subArea = buscarAreaConhPorIdCT($this->PUB_ID_SUBAREA_CONH);

            $this->NM_SUBAREA_CONH = !Util::vazioNulo($subArea) ? $subArea->getARC_NM_AREA_CONH() : "";
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar nomes de área / subarea.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getPUB_ID_PUBLICACAO() {
        return $this->PUB_ID_PUBLICACAO;
    }

    /* End of get PUB_ID_PUBLICACAO */

    function getCDT_ID_CANDIDATO() {
        return $this->CDT_ID_CANDIDATO;
    }

    /* End of get CDT_ID_CANDIDATO */

    function getPUB_TP_ITEM() {
        return $this->PUB_TP_ITEM;
    }

    /* End of get PUB_TP_ITEM */

    function getPUB_ID_AREA_CONH() {
        return $this->PUB_ID_AREA_CONH;
    }

    /* End of get PUB_ID_AREA_CONH */

    function getPUB_ID_SUBAREA_CONH() {
        return $this->PUB_ID_SUBAREA_CONH;
    }

    /* End of get PUB_ID_SUBAREA_CONH */

    function getPUB_QT_ITEM() {
        return $this->PUB_QT_ITEM;
    }

    /* End of get PUT_QT_ITEM */

    function getPUB_ST_COMPROVACAO() {
        return $this->PUB_ST_COMPROVACAO;
    }

    /* End of get PUB_ST_COMPROVACAO */

    function getPUB_OBS_COMPROVACAO() {
        return $this->PUB_OBS_COMPROVACAO;
    }

    /* End of get PUB_OBS_COMPROVACAO */



    /* SET FIELDS FROM TABLE */

    function setPUB_ID_PUBLICACAO($value) {
        $this->PUB_ID_PUBLICACAO = $value;
    }

    /* End of SET PUB_ID_PUBLICACAO */

    function setCDT_ID_CANDIDATO($value) {
        $this->CDT_ID_CANDIDATO = $value;
    }

    /* End of SET CDT_ID_CANDIDATO */

    function setPUB_TP_ITEM($value) {
        $this->PUB_TP_ITEM = $value;
    }

    /* End of SET PUB_TP_ITEM */

    function setPUB_ID_AREA_CONH($value) {
        $this->PUB_ID_AREA_CONH = $value;
    }

    /* End of SET PUB_ID_AREA_CONH */

    function setPUB_ID_SUBAREA_CONH($value) {
        $this->PUB_ID_SUBAREA_CONH = $value;
    }

    /* End of SET PUB_ID_SUBAREA_CONH */

    function setPUB_QT_ITEM($value) {
        $this->PUB_QT_ITEM = $value;
    }

    /* End of SET PUB_QT_ITEM */

    function setPUB_ST_COMPROVACAO($value) {
        $this->PUB_ST_COMPROVACAO = $value;
    }

    /* End of SET PUB_ST_COMPROVACAO */

    function setPUB_OBS_COMPROVACAO($value) {
        $this->PUB_OBS_COMPROVACAO = $value;
    }

    /* End of SET PUB_OBS_COMPROVACAO */
}

?>
