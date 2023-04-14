<?php

/**
 * tb_rap_resp_anexo_proc class
 * This class manipulates the table RespAnexoProc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 29/10/2013
 * */
class RespAnexoProc {

    private $RAP_ID_RESPOSTA;
    private $PRC_ID_PROCESSO;
    private $PCH_ID_CHAMADA;
    private $IPR_ID_INSCRICAO;
    private $GAP_ID_GRUPO_PROC;
    private $IAP_ID_ITEM;
    private $SAP_ID_SUBITEM;
    private $RAP_DS_RESPOSTA;
    private $RAP_VL_NOTA;
    private $RAP_DS_OBS_NOTA;
    private $RAP_ID_USR_AVALIADOR;
    private $RAP_DT_AVALIACAO;
    private static $SEPARADOR_RESPOSTA = ",";
    public static $TAM_LIMITE_RESP = 3000;
    public static $TAM_LIMITE_OBS_NOTA = 1000;
    private static $NOTA_ZERO = 0;
    private static $OBS_NOTA_ZERO = "Questão não respondida pelo candidato.";

    # armazenando iniciais de id's HTML
    private static $HTML_NOTA = 0;
    private static $HTML_OBS = 1;
    private static $HTML_HIDDEN = 2;
    private static $IDS_HTML = array("nota", "obs", "hidden");

    /* Construtor padrão da classe */

    public function __construct($RAP_ID_RESPOSTA, $PRC_ID_PROCESSO, $PCH_ID_CHAMADA, $IPR_ID_INSCRICAO, $GAP_ID_GRUPO_PROC, $IAP_ID_ITEM, $SAP_ID_SUBITEM, $RAP_DS_RESPOSTA, $RAP_VL_NOTA = NULL, $RAP_DS_OBS_NOTA = NULL, $RAP_ID_USR_AVALIADOR = NULL, $RAP_DT_AVALIACAO = NULL) {
        $this->RAP_ID_RESPOSTA = $RAP_ID_RESPOSTA;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->IPR_ID_INSCRICAO = $IPR_ID_INSCRICAO;
        $this->GAP_ID_GRUPO_PROC = $GAP_ID_GRUPO_PROC;
        $this->IAP_ID_ITEM = $IAP_ID_ITEM;
        $this->SAP_ID_SUBITEM = $SAP_ID_SUBITEM;
        $this->RAP_DS_RESPOSTA = $RAP_DS_RESPOSTA;
        $this->RAP_VL_NOTA = $RAP_VL_NOTA;
        $this->RAP_DS_OBS_NOTA = $RAP_DS_OBS_NOTA;
        $this->RAP_ID_USR_AVALIADOR = $RAP_ID_USR_AVALIADOR;
        $this->RAP_DT_AVALIACAO = $RAP_DT_AVALIACAO;
    }

    /**
     * 
     * @param GrupoAnexoProc $grupo
     * 
     * @return string
     */
    private function htmlNota($grupo) {

        if (!$grupo->isAvaliativo()) {
            // item nao avaliativo. Retornando span basico
            return "<span title='Item não avaliativo' class='titulo3'>(Nota: <i>Não avaliativo</i>)</span>";
        }


        // tem nota. Entao recuperando informaçoes extras
        if (!Util::vazioNulo($this->RAP_VL_NOTA)) { // foi avaliado
            // 
            // 
            // avaliaçao manual
            if (!Util::vazioNulo($this->RAP_ID_USR_AVALIADOR)) {
                $usuAvalOriginal = Usuario::buscarUsuarioPorId($this->RAP_ID_USR_AVALIADOR);
                $dsAvalOriginal = $usuAvalOriginal->getUSR_DS_NOME() . " (Código " . $this->RAP_ID_USR_AVALIADOR . ")";
            } else {
                // avaliacao automatica
                $dsAvalOriginal = "Avaliação automática";
            }
            $obsOriginal = "'$this->RAP_DS_OBS_NOTA'" . " - " . $dsAvalOriginal . " em $this->RAP_DT_AVALIACAO";

            // Recuperando dados do relatório de notas. Retorno: [$nota, $observacao]
            $dadosNotaComp = $this->getDsCompAvalRelNotas($grupo, $obsOriginal);

            // gerando título
            $tituloSpan = htmlspecialchars($dadosNotaComp[1], ENT_QUOTES);

            //retornando nota
            return "<span title='$tituloSpan' class='titulo3'>(Nota: $dadosNotaComp[0])</span>";
            //
        //
        } else {
            //
            //
            // span simples, sem nota
            return "<span class='titulo3'>(Nota: <i>Ainda não avaliado</i>)</span>";
        }
    }

    /**
     * Esta função recupera os dados de avaliação registrados no relatório de notas e retorna a string complementar
     * 
     * @param GrupoAnexoProc $grupo
     * @param string $obsOriginal Observação construída com base na nota original
     * 
     * @return array Array com dados de nota na forma [nota, observacao]
     */
    private function getDsCompAvalRelNotas($grupo, $obsOriginal) {
        $notaOriginal = money_format("%i", $this->RAP_VL_NOTA);

        // verificando compatibilidade 
        if (!$grupo->temItemAvalInfComp()) {
            // Nada a modificar
            return array($notaOriginal, $obsOriginal);
        }

        $relNotas = RelNotasInsc::buscarRelNotasPorInscCatItem($this->PCH_ID_CHAMADA, $this->IPR_ID_INSCRICAO, $grupo->getIdCategoriaAval(), $grupo->getIdItemAval(), RelNotasInsc::$SIT_ATIVA, FALSE, RelNotasInsc::$TP_AVAL_MANUAL);

        // tem nota diferente no relatório de notas
        if (!Util::vazioNulo($relNotas) && $relNotas[0]->getRNI_VL_NOTA_NORMALIZADA() != $this->RAP_VL_NOTA) {
            $novaObs = "Nota original: $notaOriginal ->" . $obsOriginal;

            $novaObs .= "<br/><br/>Nota alterada por {$relNotas[0]->USR_DS_NOME_RESP} (Código {$relNotas[0]->getRNI_ID_USUARIO_RESP()}) em {$relNotas[0]->getRNI_LOG_DT_ALTERACAO()}";

            return array($relNotas[0]->getNotaNormalizadaComMasc(), $novaObs);
        } else {
            // Nada a modificar
            return array($notaOriginal, $obsOriginal);
        }
    }

    /**
     * 
     * @param RespAnexoProc $resp
     * @param GrupoAnexoProc $grupo
     */
    public static function getHtmlNota($resp, $grupo) {
        if (Util::vazioNulo($resp)) {
            // retornando nota 0
            $nota = "<i>Sem Nota</i>";
            return "<span title='Item não respondido' class='titulo3'>(Nota: $nota)</span>";
        }
        return $resp->htmlNota($grupo);
    }

    /**
     * 
     * @param array $matResposta - Matriz de resposta capturado no post
     * @param InscricaoProcesso $objInsc
     * @param int $idInscricao
     * @return array
     */
    public static function getArraySqlInsercaoResp($matResposta, $objInsc, $idInscricao) {
        // variavel de retorno
        $ret = array();

        // sql inicial
        $sqlIni = "insert into tb_rap_resp_anexo_proc( PRC_ID_PROCESSO, PCH_ID_CHAMADA, IPR_ID_INSCRICAO, GAP_ID_GRUPO_PROC, IAP_ID_ITEM, SAP_ID_SUBITEM, RAP_DS_RESPOSTA)
        values('{$objInsc->getPRC_ID_PROCESSO()}', '{$objInsc->getPCH_ID_CHAMADA()}', '$idInscricao', ";

        // recuperando estrutura de questoes para armazenar resposta
        // 
        // Recuperando grupos do processo
        $grupos = buscarGrupoPorProcessoCT($objInsc->getPRC_ID_PROCESSO());

        // iterando nos grupos
        foreach ($grupos as $grupo) {
            //@todo WFUTURO: Avaliar perguntas cujo modo de avaliaçao e automatico aqui, caso algum dia exista!
            //
            //
            //
            // recuperando resposta(s) do grupo
            $resp = isset($matResposta[$grupo->getIdElementoHtml()]) ? $matResposta[$grupo->getIdElementoHtml()] : NULL;

            // Verificar preenchimento obrigatorio do campo
            if ($grupo->isObrigatorio() && Util::vazioNulo($resp)) {
                throw new NegocioException("Informação adicional obrigatória não informada.");
            }


            // caso pergunta livre
            if ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_PERGUNTA_LIVRE) {
                // apenas armazenar resposta
                if (!Util::vazioNulo($resp)) {
                    // validando tamanho
                    if (RespAnexoProc::getTamanhoResposta($resp) > RespAnexoProc::$TAM_LIMITE_RESP) {
                        $tamMax = RespAnexoProc::$TAM_LIMITE_RESP;
                        new Mensagem("Desculpe. Sua resposta não deve exceder $tamMax caracteres.<br/>Por favor, tente novamente escrevendo no máximo $tamMax caracteres.", Mensagem::$MENSAGEM_ERRO);
                        return;
                    }
                    //tratando resposta e criando sql
                    $resp = addslashes($resp);
                    $sql = $sqlIni . "'{$grupo->getGAP_ID_GRUPO_PROC()}', NULL, NULL, '$resp')";
                    $ret [] = $sql;
                }
                // caso de agrupamento pergunta
            } elseif ($grupo->getGAP_TP_GRUPO() == GrupoAnexoProc::$TIPO_AGRUPAMENTO_PERGUNTA) {

                if (!Util::vazioNulo($resp)) {
                    $respBD = RespAnexoProc::converteRespParaStr($resp);
                    // gerando sql de resposta e adicionando ao retorno
                    $sql = $sqlIni . "'{$grupo->getGAP_ID_GRUPO_PROC()}', NULL, NULL, '$respBD')";
                    $ret [] = $sql;


                    // buscando itens para verificar os que necessitam de complemento
                    $itens = buscarItemPorGrupoCT($grupo->getGAP_ID_GRUPO_PROC());
                    foreach ($itens as $item) {
                        //caso o item nao foi selecionado, nada a fazer
                        if (!RespAnexoProc::isItemSelecionado($resp, $item)) {
                            continue;
                        }

                        // item com complemento
                        if ($item->temComplemento()) {
                            // recuperando resposta do subitem
                            $respSub = isset($matResposta[$item->getIdElementoHtml()]) ? $matResposta[$item->getIdElementoHtml()] : NULL;

                            // Verificar preenchimento obrigatorio do campo
                            if ($item->isObrigatorio() && Util::vazioNulo($respSub)) {
                                throw new NegocioException("Informação adicional obrigatória não informada.");
                            }

                            if (!Util::vazioNulo($respSub)) {
                                $respSubBD = RespAnexoProc::converteRespParaStr($respSub);

                                // gerando sql de resposta e adicionando ao retorno
                                $sql = $sqlIni . "NULL, '{$item->getIAP_ID_ITEM()}', NULL, '$respSubBD')";
                                $ret [] = $sql;
                            }
                        } // fim temComplemento
                    } // item
                }// vazio ou nulo grupo pergunta
            } // grupo pergunta
        }
//        print_r($ret);
//        exit;
        return $ret;
    }

    public static function getTamanhoResposta($resp) {
        if ($resp == NULL) {
            return 0;
        }
        return mb_strlen($resp, 'utf8') - substr_count($resp, "\r\n");
    }

    private static function getSqlPadrao($asTabela = "") {
        if ($asTabela != "") {
            $asTabela = "$asTabela.";
        }
        return "select 
                {$asTabela}RAP_ID_RESPOSTA, 
                {$asTabela}PRC_ID_PROCESSO,
                {$asTabela}PCH_ID_CHAMADA,
                {$asTabela}IPR_ID_INSCRICAO,
                {$asTabela}GAP_ID_GRUPO_PROC,
                {$asTabela}IAP_ID_ITEM,
                {$asTabela}SAP_ID_SUBITEM,
                RAP_DS_RESPOSTA,
                RAP_VL_NOTA,
                RAP_DS_OBS_NOTA,
                RAP_ID_USR_AVALIADOR,
                DATE_FORMAT(`RAP_DT_AVALIACAO`, '%d/%m/%Y %T') AS RAP_DT_AVALIACAO ";
    }

    public static function buscarRespAnexoProcPorInscricao($idInscricao, $tpAvalGrupo = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $asTab = "rap";
            $sql = RespAnexoProc::getSqlPadrao($asTab) . " from
                    tb_rap_resp_anexo_proc $asTab
                    join tb_gap_grupo_anexo_proc gap on $asTab.GAP_ID_GRUPO_PROC = gap.GAP_ID_GRUPO_PROC
                where
                    IPR_ID_INSCRICAO = '$idInscricao'";

            // incluindo sql de aval grupo
            if ($tpAvalGrupo != NULL) {
                $sql .= " and GAP_TP_AVALIACAO = '$tpAvalGrupo'";
            }

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

                $respostaTemp = new RespAnexoProc($dados['RAP_ID_RESPOSTA'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_ID_INSCRICAO'], $dados['GAP_ID_GRUPO_PROC'], $dados['IAP_ID_ITEM'], $dados['SAP_ID_SUBITEM'], $dados['RAP_DS_RESPOSTA'], $dados['RAP_VL_NOTA'], $dados['RAP_DS_OBS_NOTA'], $dados['RAP_ID_USR_AVALIADOR'], $dados['RAP_DT_AVALIACAO']);

                //adicionando no vetor
                $vetRetorno[] = $respostaTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar respostas do usuário para as informações complementares do processo.", $e);
        }
    }

    public static function buscarRespPorInscricaoGrupo($idInscricao, $idGrupo) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = RespAnexoProc::getSqlPadrao() . " from
                    tb_rap_resp_anexo_proc
                where
                    IPR_ID_INSCRICAO = '$idInscricao'
                        and GAP_ID_GRUPO_PROC = '$idGrupo'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                //retorna nulo
                return NULL;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $respostaRet = new RespAnexoProc($dados['RAP_ID_RESPOSTA'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_ID_INSCRICAO'], $dados['GAP_ID_GRUPO_PROC'], $dados['IAP_ID_ITEM'], $dados['SAP_ID_SUBITEM'], $dados['RAP_DS_RESPOSTA'], $dados['RAP_VL_NOTA'], $dados['RAP_DS_OBS_NOTA'], $dados['RAP_ID_USR_AVALIADOR'], $dados['RAP_DT_AVALIACAO']);

            return $respostaRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar respostas do usuário para as informações complementares do processo por grupo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @param int $idGrupo
     * @param int $idInscricao
     * @param boolean $indexarIdInsc - Se true, entao o vetor e indexado pelo id da inscriçao.
     * @return \array|null - Array com a resposta, indexado numericamente ou 
     * pelo id da inscriçao. Formas:
     * 1 - (chave => array[idInscricao, Resposta])
     * 2 - (IdInscricao => Resposta)
     * @throws NegocioException
     */
    public static function buscarRespPorProcChamadaGrupo($idProcesso, $idChamada, $idGrupo, $idInscricao = NULL, $indexarIdInsc = FALSE) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        IPR_ID_INSCRICAO, 
                        replace(RAP_DS_RESPOSTA, ',', '|') as RAP_DS_RESPOSTA
                    from tb_rap_resp_anexo_proc
                where
                    PRC_ID_PROCESSO = '$idProcesso'
                    and PCH_ID_CHAMADA = '$idChamada'
                    and GAP_ID_GRUPO_PROC = '$idGrupo'";

            // adicionando id de inscrição
            if ($idInscricao != NULL) {
                $sql .= " and IPR_ID_INSCRICAO = '$idInscricao'";
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

                //adicionando no vetor
                if ($indexarIdInsc) {
                    $vetRetorno[$dados['IPR_ID_INSCRICAO']] = $dados['RAP_DS_RESPOSTA'];
                } else {
                    $vetRetorno[$i] = array($dados['IPR_ID_INSCRICAO'], $dados['RAP_DS_RESPOSTA']);
                }
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar respostas do usuário às informações complementares do processo.", $e);
        }
    }

    public static function buscarRespPorInscricaoItem($idInscricao, $idItem) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = RespAnexoProc::getSqlPadrao() . " from
                    tb_rap_resp_anexo_proc
                where
                    IPR_ID_INSCRICAO = '$idInscricao'
                        and IAP_ID_ITEM = '$idItem'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                // Retorna Nulo
                return NULL;
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $respostaRet = new RespAnexoProc($dados['RAP_ID_RESPOSTA'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['IPR_ID_INSCRICAO'], $dados['GAP_ID_GRUPO_PROC'], $dados['IAP_ID_ITEM'], $dados['SAP_ID_SUBITEM'], $dados['RAP_DS_RESPOSTA'], $dados['RAP_VL_NOTA'], $dados['RAP_DS_OBS_NOTA'], $dados['RAP_ID_USR_AVALIADOR'], $dados['RAP_DT_AVALIACAO']);

            return $respostaRet;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar respostas do usuário para as informações complementares do processo por item.", $e);
        }
    }

    public static function contarRespNaoAvaliadaPorCham($idChamada) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $semAval = GrupoAnexoProc::$AVAL_TP_SEM;

            $sql = "select 
                        count(*) as cont
                    from
                        tb_rap_resp_anexo_proc rap
                    join tb_gap_grupo_anexo_proc gap on rap.GAP_ID_GRUPO_PROC = gap.GAP_ID_GRUPO_PROC
                    where
                        rap.PCH_ID_CHAMADA = '$idChamada'
                            and rap_dt_avaliacao IS NULL
                            and GAP_TP_AVALIACAO != '$semAval'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar respostas não avaliadas da chamada.", $e);
        }
    }

    public static function contarAvaliacaoPorUsuResp($idUsuResp) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
                        count(*) as cont
                    from
                        tb_rap_resp_anexo_proc
                    where
                        RAP_ID_USR_AVALIADOR = '$idUsuResp'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar avaliações por responsável.", $e);
        }
    }

    public static function getStrSqlExclusaoPorInscricao($idInscricao) {
        return "delete from tb_rap_resp_anexo_proc where IPR_ID_INSCRICAO = $idInscricao";
    }

    /**
     * 
     * @param int $resp
     * @param ItemAnexoProc $item
     * @return boolean
     */
    private static function isItemSelecionado($resp, $item) {
        if (is_array($resp)) {
            return array_search($item->getIAP_DS_ITEM(), $resp) !== FALSE;
        } elseif (is_string($resp)) {
            return strpos($resp, $item->getIAP_DS_ITEM()) !== FALSE;
        }
    }

    /**
     * Funçao que retorna um array representando as respostas de um item
     * Util para realizar pesquisas de resposta
     * @return array
     */
    public function respParaArray() {
        return explode(RespAnexoProc::$SEPARADOR_RESPOSTA, $this->RAP_DS_RESPOSTA);
    }

    /**
     * Funçao que verifica se um determinado item esta respondido (marcado, 
     * no caso de radios e checkbox)
     * @param array $arrayResp
     * @param string $resp
     * @return array
     */
    public static function isResposta($arrayResp, $resp) {
        return array_search($resp, $arrayResp) !== FALSE;
    }

    /**
     * 
     * @param object $resp
     */
    private static function converteRespParaStr($resp) {
        $ret = "";
        if (is_array($resp)) {
            // iterando para retornar
            for ($i = 0; $i < count($resp); $i++) {
                if ($i != 0) {
                    $ret .= RespAnexoProc::$SEPARADOR_RESPOSTA . $resp[$i];
                } else {
                    $ret .= $resp[$i];
                }
            }
            return $ret;
        }
        if (is_string($resp)) {
            return $resp;
        }

        throw new NegocioException("Conversão de resposta não implementada!");
    }

    /**
     * Retorna Array de Sqls com registro de notas de cada item referenciado na 
     * matriz de informaçoes complementares.
     * 
     * OBS: Essa funcao nao faz validacao dos campos de $matInfComp, pois assume a 
     * correta validaçao nas funcoes anteriores. 
     * 
     * @param int $idInscricao
     * @param array $matInfComp - Matriz de informaçoes complementares com notas de itens
     * @param int $idAvaliador
     * @param string $sqlDataHora - SQL que obtem no BD a data e a hora corrente.
     * @return array
     */
    public static function getArraySqlRegistroAvaliacao($idInscricao, $matInfComp, $idAvaliador, $sqlDataHora) {
        // sem dados
        if ($matInfComp == NULL) {
            return array(); // retorna array vazio
        }

        $listaRet = array();
        $listaEtapaReset = array();

        // recuperando respostas das inscriçoes
        $listaResp = RespAnexoProc::buscarRespAnexoProcPorInscricao($idInscricao, GrupoAnexoProc::$AVAL_TP_MANUAL);

        // percorrendo respostas para registrar nota
        foreach ($listaResp as $respAnexoProc) {
            $idHidden = self::geradorIdsHtml($respAnexoProc->getRAP_ID_RESPOSTA(), self::$HTML_HIDDEN);

            // se esta na matriz, atualizar nota
            if (isset($matInfComp[$idHidden])) {
                $idNota = self::geradorIdsHtml($respAnexoProc->getRAP_ID_RESPOSTA(), self::$HTML_NOTA);
                $idObs = self::geradorIdsHtml($respAnexoProc->getRAP_ID_RESPOSTA(), self::$HTML_OBS);

                // recuperando grupo para tratamento de nota e relatório
                $grupoAnexo = GrupoAnexoProc::buscarGrupoAnexoProcPorId($respAnexoProc->getGAP_ID_GRUPO_PROC());
                $itemAval = $grupoAnexo->getItemAvalInfComp();

                // lançando exceção de nota
                if ($matInfComp[$idNota] > $itemAval->getIAP_VAL_PONTUACAO_MAX()) {
                    throw new NegocioException("Nota da pergunta '{$grupoAnexo->getGAP_NM_GRUPO()}' é maior que o valor máximo permitido.");
                }

                //montando sql
                $temp = "update `tb_rap_resp_anexo_proc`
                set rap_vl_nota = '{$matInfComp[$idNota]}',
                    rap_ds_obs_nota = '{$matInfComp[$idObs]}',
                    rap_id_usr_avaliador = '$idAvaliador',
                    rap_dt_avaliacao = $sqlDataHora
                where `RAP_ID_RESPOSTA` = '{$respAnexoProc->getRAP_ID_RESPOSTA()}'";

                // incluindo no retorno 
                $listaRet [] = $temp;

                // recuperando sql de inserção no relatóio de notas e incluindo no retorno
                $listaRet [] = $respAnexoProc->getSqlAtualizacaoRelNotas($grupoAnexo, $itemAval, $matInfComp[$idNota], $matInfComp[$idObs], $idAvaliador);

                // verificando necessidade de incluir sql de reset da classificação da etapa
                if (!in_array($grupoAnexo->getIdEtapaAval(), $listaEtapaReset)) {
                    // incluindo reset
                    $listaRet [] = EtapaSelProc::getStrSqlClassifPenProcEtapa($grupoAnexo->getPRC_ID_PROCESSO(), $grupoAnexo->getIdEtapaAval());

                    // infomando no array
                    $listaEtapaReset [] = $grupoAnexo->getIdEtapaAval();
                }
            } else {
                // se não tem resposta, incluindo sql de zerar nota
                if (Util::vazioNulo($respAnexoProc->RAP_DS_RESPOSTA)) {
                    //montando sql
                    $notaZero = self::$NOTA_ZERO;
                    $obsNotaZero = self::$OBS_NOTA_ZERO;

                    $temp = "update `tb_rap_resp_anexo_proc`
                            set rap_vl_nota = '$notaZero',
                                rap_ds_obs_nota = '$obsNotaZero',
                                rap_id_usr_avaliador = '$idAvaliador',
                                rap_dt_avaliacao = $sqlDataHora
                            where `RAP_ID_RESPOSTA` = '{$respAnexoProc->getRAP_ID_RESPOSTA()}'";

                    // incluindo no retorno 
                    $listaRet [] = $temp;
                }
            }
        }

        // incluindo sql de data de avaliacao para quem nao tem
        $temp = "update `tb_rap_resp_anexo_proc`
                set rap_id_usr_avaliador = '$idAvaliador',
                rap_dt_avaliacao = $sqlDataHora
                where rap_dt_avaliacao IS NULL and `IPR_ID_INSCRICAO` = '$idInscricao'";


        // incluindo no retorno 
        $listaRet [] = $temp;

        return $listaRet;
    }

    public function add_sql_registra_nota_inicial($nota, &$vetSql) {
        print_r($this);
        // Ainda não foi avaliado via avaliação cega
        if (Util::vazioNulo($this->RAP_DT_AVALIACAO)) {
            $vetSql [] = "update `tb_rap_resp_anexo_proc`
                set rap_vl_nota = '$nota',
                    rap_ds_obs_nota = '" . self::getstrNotaInicial() . "',
                    rap_id_usr_avaliador = '" . getIdUsuarioLogado() . "',
                    rap_dt_avaliacao = now()
                where `RAP_ID_RESPOSTA` = '$this->RAP_ID_RESPOSTA'";
        }
    }

    public static function getStrNotaInicial() {
        return "Nota registrada sem avaliação cega.";
    }

    /**
     * 
     * @param GrupoAnexoProc $grupoAnexo
     * @param ItemAvalProc $itemAval
     * @param float $vlNota
     * @param string $obsNota
     * @param int $idAvaliador
     */
    private function getSqlAtualizacaoRelNotas($grupoAnexo, $itemAval, $vlNota, $obsNota, $idAvaliador) {
        // criando relatório de notas
        $relatorio = new RelNotasInsc(NULL, $this->IPR_ID_INSCRICAO, $itemAval->getCAP_ID_CATEGORIA_AVAL(), $itemAval->getIAP_ID_ITEM_AVAL(), RelNotasInsc::$ORDEM_MAXIMA, RelNotasInsc::$TP_AVAL_MANUAL, $grupoAnexo->getDsObjAval(), $vlNota, min(array($itemAval->getIAP_VAL_PONTUACAO_MAX(), $vlNota)), RelNotasInsc::$SIT_ATIVA, $idAvaliador, $obsNota);

        // recuperando sql 
        return $relatorio->CLAS_getSqlRelNotasCand($this->IPR_ID_INSCRICAO, FALSE);
    }

    /**
     * Retorna o somatorio de notas da matriz $matInfComp
     * @param array $matInfComp - Matriz de informaçoes complementares
     * @return real
     */
    public static function somaNotasMatriz($matInfComp) {
        if ($matInfComp == NULL) {
            return 0.0;
        }

        $ret = 0.0;

        //criando ER
        $iniChNota = self::$IDS_HTML[self::$HTML_NOTA];
        $er = "/$iniChNota.*/";

        # Criando vetor com indice das notas
        $vetNotas = preg_grep($er, array_keys($matInfComp));

        // sumarizando notas
        foreach ($vetNotas as $id) {
            $ret += floatval($matInfComp[$id]);
        }

        return $ret;
    }

    function getVlNotaMascarada() {
        if (Util::vazioNulo($this->RAP_VL_NOTA)) {
            return NULL;
        }
        return $this->RAP_VL_NOTA * 100;
    }

    /**
     * 
     * @param RespAnexoProc $resp
     * @param boolean $html
     * @return string
     */
    public static function getDsResposta($resp, $html = TRUE) {
        if (Util::vazioNulo($resp)) {
            $str = self::getStrSemResposta();
            return $html ? "<i>$str</i>" : $str;
        } else {
            return $html ? "<i class='fa fa-quote-left'></i> $resp->RAP_DS_RESPOSTA <i class='fa fa-quote-right'></i>" : $resp->RAP_DS_RESPOSTA;
        }
    }

    static function getStrSemResposta() {
        return "Item sem resposta";
    }

    /**
     * Retorna o Id do elemento nota na montagem do HTML
     * @return string
     */
    public function getIdElementoNota() {
        return self::geradorIdsHtml($this->RAP_ID_RESPOSTA, self::$HTML_NOTA);
    }

    public function getIdElementoObsNota() {
        return self::geradorIdsHtml($this->RAP_ID_RESPOSTA, self::$HTML_OBS);
    }

    /**
     * Retorna o id do elemento hidden para submissao de respostas
     * @return boolean
     */
    public function getIdElemHtmlHidden() {
        return self::geradorIdsHtml($this->RAP_ID_RESPOSTA, self::$HTML_HIDDEN);
    }

    private static function geradorIdsHtml($idResp, $campo) {
        if ($campo >= 0 && $campo < count(self::$IDS_HTML)) {
            return self::$IDS_HTML[$campo] . $idResp;
        } else {
            throw new NegocioException("Chamada de funcao interna de classe incorreta!");
        }
    }

    /* GET FIELDS FROM TABLE */

    function getRAP_ID_RESPOSTA() {
        return $this->RAP_ID_RESPOSTA;
    }

    /* End of get RAP_ID_RESPOSTA */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getIPR_ID_INSCRICAO() {
        return $this->IPR_ID_INSCRICAO;
    }

    /* End of get IPR_ID_INSCRICAO */

    function getGAP_ID_GRUPO_PROC() {
        return $this->GAP_ID_GRUPO_PROC;
    }

    /* End of get GAP_ID_GRUPO_PROC */

    function getIAP_ID_ITEM() {
        return $this->IAP_ID_ITEM;
    }

    /* End of get IAP_ID_ITEM */

    function getSAP_ID_SUBITEM() {
        return $this->SAP_ID_SUBITEM;
    }

    /* End of get SAP_ID_SUBITEM */

    function getRAP_DS_OBS_NOTA() {
        return $this->RAP_DS_OBS_NOTA;
    }

    /* End of get RAP_DS_OBS_NOTA */

    function getRAP_ID_USR_AVALIADOR() {
        return $this->RAP_ID_USR_AVALIADOR;
    }

    /* End of get RAP_ID_USR_AVALIADOR */

    function getRAP_DT_AVALIACAO() {
        return $this->RAP_DT_AVALIACAO;
    }

    /* End of get RAP_DT_AVALIACAO */






    /* SET FIELDS FROM TABLE */

    function setRAP_ID_RESPOSTA($value) {
        $this->RAP_ID_RESPOSTA = $value;
    }

    /* End of SET RAP_ID_RESPOSTA */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function setIPR_ID_INSCRICAO($value) {
        $this->IPR_ID_INSCRICAO = $value;
    }

    /* End of SET IPR_ID_INSCRICAO */

    function setGAP_ID_GRUPO_PROC($value) {
        $this->GAP_ID_GRUPO_PROC = $value;
    }

    /* End of SET GAP_ID_GRUPO_PROC */

    function setIAP_ID_ITEM($value) {
        $this->IAP_ID_ITEM = $value;
    }

    /* End of SET IAP_ID_ITEM */

    function setSAP_ID_SUBITEM($value) {
        $this->SAP_ID_SUBITEM = $value;
    }

    /* End of SET SAP_ID_SUBITEM */

    function setRAP_DS_RESPOSTA($value) {
        $this->RAP_DS_RESPOSTA = $value;
    }

    /* End of SET RAP_DS_RESPOSTA */

    function setRAP_VL_NOTA($value) {
        $this->RAP_VL_NOTA = $value;
    }

    /* End of SET RAP_VL_NOTA */

    function setRAP_DS_OBS_NOTA($value) {
        $this->RAP_DS_OBS_NOTA = $value;
    }

    /* End of SET RAP_DS_OBS_NOTA */

    function setRAP_ID_USR_AVALIADOR($value) {
        $this->RAP_ID_USR_AVALIADOR = $value;
    }

    /* End of SET RAP_ID_USR_AVALIADOR */

    function setRAP_DT_AVALIACAO($value) {
        $this->RAP_DT_AVALIACAO = $value;
    }

    /* End of SET RAP_DT_AVALIACAO */
}

?>
