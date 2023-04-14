<?php

/**
 * tb_not_noticia class
 * This class manipulates the table Noticia
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 07/11/2013
 * */
class Noticia {

    private $NOT_ID_NOTICIA;
    private $NOT_DT_PUBLICACAO;
    private $NOT_DT_VALIDADE;
    private $NOT_NM_TITULO;
    private $NOT_DS_NOTICIA;
    private $NOT_DS_URL;
    private $NOT_URL_EXTERNA;
    private $NOT_URL_IMAGEM;
    private $PRC_ID_PROCESSO;
    private $PCH_ID_CHAMADA;
    private $NOT_TP_NOTICIA;
    private $noticiaVencida;
    // constantes de tipo de notícia
    public static $NOTICIA_PUBLICA = 'A';
    public static $NOTICIA_PRIVADA = 'F';
    // tipo de link
    public static $TP_LINK_INTERNO = 'I';
    public static $TP_LINK_EXTERNO = 'E';
    // constantes para interface
    public static $QT_NOTICIAS_POR_PAG = 2;
    // constantes de controle
    private static $QT_DIAS_NOTICIA_RECENTE = 3;

    public static function getDsTipo($tipo) {
        if ($tipo == self::$NOTICIA_PRIVADA) {
            return "Privada";
        }
        if ($tipo == self::$NOTICIA_PUBLICA) {
            return "Pública";
        }
        return null;
    }

    public static function getDsTipoLink($tpLink) {
        if ($tpLink == self::$TP_LINK_EXTERNO) {
            return "Externo";
        }
        if ($tpLink == self::$TP_LINK_INTERNO) {
            return "Interno";
        }
        return null;
    }

    public static function getListaTipoDsTipo() {
        $ret = array(
            self::$NOTICIA_PUBLICA => self::getDsTipo(self::$NOTICIA_PUBLICA),
            self::$NOTICIA_PRIVADA => self::getDsTipo(self::$NOTICIA_PRIVADA)
        );
        return $ret;
    }

    public static function getListaTipoLinkDsTpLink() {
        $ret = array(
            self::$TP_LINK_EXTERNO => self::getDsTipoLink(self::$TP_LINK_EXTERNO),
            self::$TP_LINK_INTERNO => self::getDsTipoLink(self::$TP_LINK_INTERNO)
        );
        return $ret;
    }

    /* Construtor padrão da classe */

    public function __construct($NOT_ID_NOTICIA, $NOT_TP_NOTICIA, $NOT_DT_PUBLICACAO, $NOT_DT_VALIDADE, $NOT_NM_TITULO, $NOT_DS_NOTICIA, $NOT_DS_URL, $NOT_URL_IMAGEM, $PRC_ID_PROCESSO = NULL, $PCH_ID_CHAMADA = NULL, $NOT_URL_EXTERNA = NULL) {
        $this->NOT_ID_NOTICIA = $NOT_ID_NOTICIA;
        $this->NOT_TP_NOTICIA = $NOT_TP_NOTICIA;
        $this->NOT_DT_PUBLICACAO = $NOT_DT_PUBLICACAO;
        $this->NOT_DT_VALIDADE = $NOT_DT_VALIDADE;
        $this->NOT_NM_TITULO = $NOT_NM_TITULO;
        $this->NOT_DS_NOTICIA = $NOT_DS_NOTICIA;
        $this->NOT_DS_URL = $NOT_DS_URL;
        $this->NOT_URL_IMAGEM = $NOT_URL_IMAGEM;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->NOT_URL_EXTERNA = $NOT_URL_EXTERNA;

        // marcando notícias vencidas
        $this->marcaNoticiaVencida();
    }

    private function marcaNoticiaVencida() {
        if (!Util::vazioNulo($this->NOT_DT_VALIDADE)) {
            $this->noticiaVencida = dt_dataMenor(dt_getTimestampDtBR($this->NOT_DT_VALIDADE), dt_getTimestampDtBR());
        } else {
            $this->noticiaVencida = FALSE;
        }
    }

    private static function getSqlPadraoBusca() {
        return "SELECT 
                NOT_ID_NOTICIA,
                NOT_TP_NOTICIA,
                date_format(`NOT_DT_PUBLICACAO`, '%d/%m/%Y às %T') as NOT_DT_PUBLICACAOSTR,
                date_format(`NOT_DT_VALIDADE`, '%d/%m/%Y') as NOT_DT_VALIDADESTR,
                NOT_NM_TITULO,
                NOT_DS_NOTICIA,
                NOT_DS_URL,
                NOT_URL_EXTERNA,
                NOT_URL_IMAGEM,
                PRC_ID_PROCESSO,
                PCH_ID_CHAMADA
            from
                tb_not_noticia";
    }

    public static function buscarUltimasNoticias($tpNoticia = NULL, $inicioDados = NULL, $qtdeDados = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = self::getSqlPadraoBusca();

            // tipo de notícia
            $where = TRUE;
            if ($tpNoticia != NULL) {
                $sql .= " where NOT_TP_NOTICIA = '$tpNoticia'";
                $where = FALSE;
            }

            // questão de ordenação e finalização 
            $conector = $where ? "where" : "and";
            $sql .= " $conector NOT_DT_PUBLICACAO <= now()
                    order by (NOT_DT_VALIDADE >= curdate() or NOT_DT_VALIDADE IS NULL) desc, NOT_DT_PUBLICACAO desc";

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
                //retornando array vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar as titulações
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $noticiaTemp = new Noticia($dados['NOT_ID_NOTICIA'], $dados['NOT_TP_NOTICIA'], $dados['NOT_DT_PUBLICACAOSTR'], $dados['NOT_DT_VALIDADESTR'], $dados['NOT_NM_TITULO'], $dados['NOT_DS_NOTICIA'], $dados['NOT_DS_URL'], $dados['NOT_URL_IMAGEM'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['NOT_URL_EXTERNA']);

                //adicionando no vetor
                $vetRetorno[$i] = $noticiaTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar notícias.", $e);
        }
    }

    public static function buscarNoticiaPorChamada($idProcesso, $idChamada, $tpNoticia = NULL, $inicioDados = NULL, $qtdeDados = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = $sql = self::getSqlPadraoBusca()
                    . " where PRC_ID_PROCESSO = '$idProcesso'
                        and PCH_ID_CHAMADA = '$idChamada'";


            // tipo de notícia
            if ($tpNoticia != NULL) {
                $sql .= " and NOT_TP_NOTICIA = '$tpNoticia'";
            }

            // questão de ordenação e finalização 
            $sql .= " and NOT_DT_PUBLICACAO <= now()
                    order by (NOT_DT_VALIDADE >= curdate() or NOT_DT_VALIDADE IS NULL) desc, NOT_DT_PUBLICACAO desc";

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
                //retornando array vazio
                return array();
            }

            $vetRetorno = array();
            //realizando iteração para recuperar dados
            for ($i = 0; $i < $numLinhas; $i++) {

                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $noticiaTemp = new Noticia($dados['NOT_ID_NOTICIA'], $dados['NOT_TP_NOTICIA'], $dados['NOT_DT_PUBLICACAOSTR'], $dados['NOT_DT_VALIDADESTR'], $dados['NOT_NM_TITULO'], $dados['NOT_DS_NOTICIA'], $dados['NOT_DS_URL'], $dados['NOT_URL_IMAGEM'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['NOT_URL_EXTERNA']);


                //adicionando no vetor
                $vetRetorno[$i] = $noticiaTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar notícias da chamada.", $e);
        }
    }

    public static function buscarNoticiaPorId($idNoticia, $idProcesso = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            //montando sql
            $sql = $sql = self::getSqlPadraoBusca()
                    . " where NOT_ID_NOTICIA = '$idNoticia'";


            if ($idProcesso != NULL) {
                $sql .= " and PRC_ID_PROCESSO = '$idProcesso'";
            }


            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql:: getNumLinhas($ret) != 0) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($ret);

                $noticiaTemp = new Noticia($dados['NOT_ID_NOTICIA'], $dados['NOT_TP_NOTICIA'], $dados['NOT_DT_PUBLICACAOSTR'], $dados['NOT_DT_VALIDADESTR'], $dados['NOT_NM_TITULO'], $dados['NOT_DS_NOTICIA'], $dados['NOT_DS_URL'], $dados['NOT_URL_IMAGEM'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['NOT_URL_EXTERNA']);

                return $noticiaTemp;
            } else {
                throw new NegocioException("Notícia não encontrada.");
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar notícia.", $e);
        }
    }

    public static function contarNoticia($tpNoticia = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
                       count(*) as cont
                    from
                        tb_not_noticia";


            // tipo de notícia
            if ($tpNoticia != NULL) {
                $sql .= " where NOT_TP_NOTICIA = '$tpNoticia'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar notícias.", $e);
        }
    }

    public static function contarNoticiaPorChamada($idProcesso, $idChamada = NULL, $tpNoticia = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
                       count(*) as cont
                    from
                        tb_not_noticia
                    where PRC_ID_PROCESSO = '$idProcesso'";


            // tem chamada
            if ($idChamada != NULL) {
                $sql .= " and PCH_ID_CHAMADA = '$idChamada'";
            }

            // tipo de notícia
            if ($tpNoticia != NULL) {
                $sql .= " where NOT_TP_NOTICIA = '$tpNoticia'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar notícias por chamada.", $e);
        }
    }

    private function trataDadosBD($tpLink) {

        $this->NOT_NM_TITULO = NGUtil::trataCampoStrParaBD($this->NOT_NM_TITULO);
        $this->NOT_DS_NOTICIA = NGUtil::trataCampoStrParaBD($this->NOT_DS_NOTICIA);
        $this->NOT_TP_NOTICIA = NGUtil::trataCampoStrParaBD($this->NOT_TP_NOTICIA);

        $this->NOT_DT_VALIDADE = dt_dataStrParaMysql($this->NOT_DT_VALIDADE);

        $this->PCH_ID_CHAMADA = NGUtil::trataCampoIntParaBD($this->PCH_ID_CHAMADA);
        $this->PRC_ID_PROCESSO = NGUtil::trataCampoIntParaBD($this->PRC_ID_PROCESSO);

        // tratalink
        if (Util::vazioNulo($tpLink)) {
            $this->NOT_DS_URL = "NULL";
            $this->NOT_URL_EXTERNA = "NULL";
        } else {
            $this->NOT_DS_URL = NGUtil::trataCampoStrParaBD($this->NOT_DS_URL);
            $this->NOT_URL_EXTERNA = $tpLink === self::$TP_LINK_EXTERNO ? FLAG_BD_SIM : FLAG_BD_NAO;
            $this->NOT_URL_EXTERNA = NGUtil::trataCampoStrParaBD($this->NOT_URL_EXTERNA);
        }

        // sem imagem
        $this->NOT_URL_IMAGEM = "NULL";
    }

    public function criarNoticia($tpLink) {
        try {
            // validando criação da notícia
            $chamada = ProcessoChamada::buscarChamadaPorId($this->PCH_ID_CHAMADA);
            if (!$chamada->permiteEdicao()) {
                throw new NegocioException("Não é possível criar uma notícia, pois a chamada está finalizada.");
            }

            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            // Ajustando validade de notícias antigas
            $arrayCmds = array(self::getSqlAjustaValidade($this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA));

            // Recuperando sql de criação da notícia
            $arrayCmds [] = $this->getSqlCriacaoObjeto($tpLink);

            //executando sql
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao criar notícia.", $e);
        }
    }

    public function editarNoticia($tpLink) {
        try {

            $objAtual = self::buscarNoticiaPorId($this->NOT_ID_NOTICIA);

            // validando edição da notícia
            $chamada = ProcessoChamada::buscarChamadaPorId($objAtual->PCH_ID_CHAMADA);
            if (!$chamada->permiteEdicao()) {
                throw new NegocioException("Não é possível atualizar a notícia, pois a chamada está finalizada.");
            }

            // atualizando dados
            $objAtual->NOT_TP_NOTICIA = $this->NOT_TP_NOTICIA;
            $objAtual->NOT_DT_VALIDADE = $this->NOT_DT_VALIDADE;
            $objAtual->NOT_NM_TITULO = $this->NOT_NM_TITULO;
            $objAtual->NOT_DS_URL = $this->NOT_DS_URL;
            $objAtual->NOT_DS_NOTICIA = $this->NOT_DS_NOTICIA;

            // tratando dados
            $objAtual->trataDadosBD($tpLink);

            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "update tb_not_noticia set NOT_TP_NOTICIA = $objAtual->NOT_TP_NOTICIA,
                    NOT_DT_VALIDADE = $objAtual->NOT_DT_VALIDADE,
                    NOT_NM_TITULO = $objAtual->NOT_NM_TITULO,
                    NOT_DS_URL = $objAtual->NOT_DS_URL,
                    NOT_URL_EXTERNA = $objAtual->NOT_URL_EXTERNA,
                    NOT_DT_PUBLICACAO = now(),
                    NOT_URL_EXTERNA = $objAtual->NOT_URL_EXTERNA
                    where NOT_ID_NOTICIA = '$objAtual->NOT_ID_NOTICIA'";

            //executando sql
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar notícia.", $e);
        }
    }

    public function excluirNoticia() {
        try {
            // validando exclusão da notícia
            $chamada = ProcessoChamada::buscarChamadaPorId($this->PCH_ID_CHAMADA);
            if (!$chamada->permiteEdicao()) {
                throw new NegocioException("Não é possível excluir a notícia, pois a chamada está finalizada.");
            }

            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "delete from tb_not_noticia 
                    where NOT_ID_NOTICIA = '$this->NOT_ID_NOTICIA'";

            //executando sql
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir notícia.", $e);
        }
    }

    /**
     * Retorna um array com sqls que cria a notícia do edital e realiza os ajustes de validade necessários
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @return array Array com a sqls de criação da notícia e de ajustes de validade
     * 
     */
    public static function getArraySqlCriarNoticiaEdital($processo, $chamada) {
        $tituloNoticia = "Edital {$processo->getDsEditalCompleta()}";
        $dsNoticia = "Fique atento ao período de inscrição da {$chamada->getPCH_DS_CHAMADA(TRUE)}: de {$chamada->getDsPeriodoInscricao()}. Clique aqui para mais informações.";
        $urlNoticia = $processo->getUrlAmigavel(FALSE);

        // definindo data de publicação da notícia
        $dtPublicacao = $processo->isAberto() ? NULL : $processo->getPRC_DT_INICIO();

        // criando objeto
        $noticia = new Noticia(NULL, self::$NOTICIA_PUBLICA, $dtPublicacao, $chamada->getPCH_DT_FECHAMENTO(), $tituloNoticia, $dsNoticia, $urlNoticia, NULL, $processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());

        return array(self::getSqlAjustaValidade($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), $dtPublicacao), $noticia->getSqlCriacaoObjeto(self::$TP_LINK_INTERNO, $dtPublicacao));
    }
    
       public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_not_noticia where PRC_ID_PROCESSO = '$idProcesso'";
    }

    /**
     * Adiciona em $arraySql as sqls responsáveis por processar a retificação do calendário de um edital
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param array $arraySql Endereço do array onde deve ser adicionado as sqls responsáveis por executar a ação
     */
    public static function addSqlCriarNoticiaAltCalendario($processo, $chamada, &$arraySql) {
        $tituloNoticia = "Edital {$processo->getDsEditalCompleta()}";
        $dsNoticia = "O calendário da {$chamada->getPCH_DS_CHAMADA(TRUE)} deste edital foi retificado. Confira as novas datas!";
        $urlNoticia = $processo->getUrlAmigavel(FALSE) . "#calendario";

        // criando objeto
        $noticia = new Noticia(NULL, self::$NOTICIA_PUBLICA, NULL, NULL, $tituloNoticia, $dsNoticia, $urlNoticia, NULL, $processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());

        // adicionando sqls
        $arraySql [] = self::getSqlAjustaValidade($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());
        $arraySql [] = $noticia->getSqlCriacaoObjeto(self::$TP_LINK_INTERNO);
    }

    /**
     * Adiciona em $arraySql as sqls responsáveis por processar a retificação das vagas de um edital
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param array $arraySql Endereço do array onde deve ser adicionado as sqls responsáveis por executar a ação
     */
    public static function addSqlCriarNoticiaAltVagas($processo, $chamada, &$arraySql) {
        $tituloNoticia = "Edital {$processo->getDsEditalCompleta()}";
        $dsNoticia = "A quantidade de vagas da {$chamada->getPCH_DS_CHAMADA(TRUE)} deste edital foi retificada. Confira as novas informações!";
        $urlNoticia = $processo->getUrlAmigavel(FALSE) . "#vagas";

        // criando objeto
        $noticia = new Noticia(NULL, self::$NOTICIA_PUBLICA, NULL, NULL, $tituloNoticia, $dsNoticia, $urlNoticia, NULL, $processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());

        // adicionando sqls
        $arraySql [] = self::getSqlAjustaValidade($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());
        $arraySql [] = $noticia->getSqlCriacaoObjeto(self::$TP_LINK_INTERNO);
    }

    /**
     * Adiciona em $arraySql as sqls responsáveis por processar a publicação de um resultado de um edital
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param string $dsPublicacao 
     * @param array $arraySql Endereço do array onde deve ser adicionado as sqls responsáveis por executar a ação
     * @param string $dtFimEdital Data programada para finalização do Edital (se houver)
     */
    public static function addSqlCriarNoticiaPubResultado($processo, $chamada, $dsPublicacao, &$arraySql, $dtFimEdital) {
        $tituloNoticia = "Edital {$processo->getDsEditalCompleta()}";
        $dsNoticia = "$dsPublicacao Confira!";
        $urlNoticia = $processo->getUrlAmigavel(FALSE);

        // criando objeto
        $noticia = new Noticia(NULL, self::$NOTICIA_PUBLICA, NULL, $dtFimEdital, $tituloNoticia, $dsNoticia, $urlNoticia, NULL, $processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());

        // adicionando sqls
        $arraySql [] = self::getSqlAjustaValidade($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA());
        $arraySql [] = $noticia->getSqlCriacaoObjeto(self::$TP_LINK_INTERNO);
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @param string $dtPublicacao Data de publicação da nova notícia
     * @return string Sql que seta a data de validade como a data atual - 1 das notícias anteriores do Edital 
     */
    public static function getSqlAjustaValidade($idProcesso, $idChamada, $dtPublicacao = NULL) {
        $dtPublicacao = $dtPublicacao == NULL ? "curdate()" : dt_dataStrParaMysql($dtPublicacao);
        return "update tb_not_noticia set NOT_DT_VALIDADE = subdate($dtPublicacao, 1) where PRC_ID_PROCESSO = '$idProcesso' and PCH_ID_CHAMADA = '$idChamada'
                and (NOT_DT_VALIDADE IS NULL or NOT_DT_VALIDADE > subdate($dtPublicacao, 1))";
    }

    private function getSqlCriacaoObjeto($tpLink, $dtPublicacao = NULL) {
        // tratando dados para inserção no BD
        $this->trataDadosBD($tpLink);
        $dtPublicacao = $dtPublicacao == NULL ? "now()" : dt_dataStrParaMysql($dtPublicacao);

        return "insert into tb_not_noticia(NOT_DT_PUBLICACAO, NOT_DT_VALIDADE, NOT_NM_TITULO, NOT_DS_NOTICIA, NOT_DS_URL, NOT_URL_IMAGEM, PRC_ID_PROCESSO,
        PCH_ID_CHAMADA, NOT_TP_NOTICIA, NOT_URL_EXTERNA) values($dtPublicacao,
        $this->NOT_DT_VALIDADE, $this->NOT_NM_TITULO, $this->NOT_DS_NOTICIA, $this->NOT_DS_URL, $this->NOT_URL_IMAGEM,
        $this->PRC_ID_PROCESSO, $this->PCH_ID_CHAMADA, $this->NOT_TP_NOTICIA, $this->NOT_URL_EXTERNA)";
    }

    public function getHtmlNoticia() {
        // classe que informa vencimento
        $classDiv = $this->isVencida() ? "finalizado" : "aberto";

        // definição de label
        $htmlLabel = !$this->isVencida() && $this->isNoticiaRecente() ? "<span class='label label-success'>Recente</span>" : "";

        // notícia com link ou sem link?
        if ($this->temLink()) {
            $descricao = "<a target='_blank' href='{$this->getLinkNoticia()}'>$this->NOT_DS_NOTICIA</a>";
            $titulo = "<a target='_blank' href='{$this->getLinkNoticia()}'>$this->NOT_NM_TITULO</a>";
        } else {
            $descricao = "$this->NOT_DS_NOTICIA";
            $titulo = "$this->NOT_NM_TITULO";
        }

        return "<div class='col-md-6'><div class='callout callout-noticia $classDiv' style='position:relative'>
                    <p class='chamada'>$titulo</p>
                    <p>$htmlLabel</p>
                    <p class='descricao'>$descricao</p>              
                    <p class='dataPostagem' style='position:absolute;bottom:0px;'>Postado em $this->NOT_DT_PUBLICACAO</p>
                </div></div>";
    }

    public function isNoticiaRecente() {
        $dt = dt_somarData($this->getNOT_DT_PUBLICACAO(TRUE), self::$QT_DIAS_NOTICIA_RECENTE);
        return dt_dataMaiorIgual(dt_getTimestampDtBR($dt), dt_getTimestampDtBR());
    }

    public function isVencida() {
        return $this->noticiaVencida;
    }

    public function temValidade() {
        return !Util::vazioNulo($this->NOT_DT_VALIDADE);
    }

    public function getDsTipoObj() {
        return self::getDsTipo($this->NOT_TP_NOTICIA);
    }

    public function temLink() {
        return !Util::vazioNulo($this->NOT_DS_URL);
    }

    public function getTpLink() {
        return $this->isLinkExterno() ? self::$TP_LINK_EXTERNO : self::$TP_LINK_INTERNO;
    }

    public function isLinkExterno() {
        return !Util::vazioNulo($this->NOT_URL_EXTERNA) && $this->NOT_URL_EXTERNA == FLAG_BD_SIM;
    }

    public function getLinkNoticia() {
        global $CFG;
        if ($this->temLink()) {
            return $this->isLinkExterno() ? $this->NOT_DS_URL : "$CFG->rwww/$this->NOT_DS_URL";
        } else {
            throw new NegocioException("Notícia sem URL.");
        }
    }

    public function getLinkParcial() {
        return $this->NOT_DS_URL;
    }

    public static function msgHtmlSemLink() {
        return htmlentities("<Sem Link>");
    }

    /* GET FIELDS FROM TABLE */

    function getNOT_ID_NOTICIA() {
        return $this->NOT_ID_NOTICIA;
    }

    /* End of get NOT_ID_NOTICIA */

    function getNOT_DT_PUBLICACAO($apenasData = FALSE) {
        if ($apenasData) {
            $temp = explode(" ", $this->NOT_DT_PUBLICACAO);
            return $temp[0];
        }
        return $this->NOT_DT_PUBLICACAO;
    }

    /* End of get NOT_DT_PUBLICACAO */

    function getNOT_DT_VALIDADE($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->NOT_DT_VALIDADE)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->NOT_DT_VALIDADE;
    }

    /* End of get NOT_DT_VALIDADE */

    function getNOT_NM_TITULO() {
        return $this->NOT_NM_TITULO;
    }

    /* End of get NOT_NM_TITULO */

    function getNOT_DS_NOTICIA() {
        return $this->NOT_DS_NOTICIA;
    }

    /* End of get NOT_DS_NOTICIA */

    function getNOT_URL_IMAGEM() {
        return $this->NOT_URL_IMAGEM;
    }

    /* End of get NOT_URL_IMAGEM */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getNOT_TP_NOTICIA() {
        return $this->NOT_TP_NOTICIA;
    }

    /* SET FIELDS FROM TABLE */

    function setNOT_ID_NOTICIA($value) {
        $this->NOT_ID_NOTICIA = $value;
    }

    /* End of SET NOT_ID_NOTICIA */

    function setNOT_DT_PUBLICACAO($value) {
        $this->NOT_DT_PUBLICACAO = $value;
    }

    /* End of SET NOT_DT_PUBLICACAO */

    function setNOT_DT_VALIDADE($value) {
        $this->NOT_DT_VALIDADE = $value;
    }

    /* End of SET NOT_DT_VALIDADE */

    function setNOT_NM_TITULO($value) {
        $this->NOT_NM_TITULO = $value;
    }

    /* End of SET NOT_NM_TITULO */

    function setNOT_DS_NOTICIA($value) {
        $this->NOT_DS_NOTICIA = $value;
    }

    /* End of SET NOT_DS_NOTICIA */

    function setNOT_DS_URL($value) {
        $this->NOT_DS_URL = $value;
    }

    /* End of SET NOT_DS_URL */

    function setNOT_URL_IMAGEM($value) {
        $this->NOT_URL_IMAGEM = $value;
    }

    /* End of SET NOT_URL_IMAGEM */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */
}

?>
