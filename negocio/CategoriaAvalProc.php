
<?php

/**
 * tb_cap_categoria_aval_proc class
 * This class manipulates the table CategoriaAvalProc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 22/05/2014
 * */
global $CFG;
require_once $CFG->rpasta . "/negocio/ItemAvalProc.php";

class CategoriaAvalProc {

    private $CAP_ID_CATEGORIA_AVAL;
    private $PRC_ID_PROCESSO;
    private $CAP_TP_CATEGORIA;
    private $CAP_ORDEM;
    private $CAP_VL_PONTUACAO_MAX;
    private $CAP_CATEGORIA_EXCLUSIVA;
    private $CAP_NR_ETAPA_AVAL; // campo nao existe na base real: Apenas simulacao!
    private $CAP_TP_AVALIACAO;
    private $EAP_ID_ETAPA_AVAL_PROC;
    public static $TIPO_TITULACAO = 'A';
    public static $TIPO_PUBLICACAO = 'B';
    public static $TIPO_PART_EVENTO = 'C';
    public static $TIPO_ATUACAO = 'D';
    public static $TIPO_ENTREVISTA = 'E';
    public static $TIPO_AVAL_EXTERNA = 'X';
    public static $TIPO_AVAL_AUTOMATIZADA = 'S';
    public static $INT_TIPO_INF_COMP = 'F'; // Tipo Interno: Categoria para notas de avaliaçao complementar
    // Tipos de avaliaçao
    public static $AVAL_AUTOMATICA = 'A';
    public static $AVAL_MANUAL = 'M';
    // ajuda interface
    public static $COD_TP_ORDENACAO = 'Cat'; // Usado para atualizar ordem

    public static function getDsTipo($tipo) {
        if ($tipo == self::$TIPO_TITULACAO) {
            return "Titulação";
        }
        if ($tipo == self::$TIPO_PUBLICACAO) {
            return "Publicação";
        }
        if ($tipo == self::$TIPO_PART_EVENTO) {
            return "Participação em Evento";
        }
        if ($tipo == self::$TIPO_ATUACAO) {
            return "Atuação";
        }
        if ($tipo == self::$TIPO_ENTREVISTA) {
            return "Entrevista";
        }
        if ($tipo == self::$INT_TIPO_INF_COMP) {
            return "Informação Complementar";
        }
        if ($tipo == self::$TIPO_AVAL_EXTERNA) {
            return "Avaliação Externa";
        }
        if ($tipo == self::$TIPO_AVAL_AUTOMATIZADA) {
            return "Avaliação Automatizada";
        }

        return null;
    }

    public static function getDsTipoSemAcento($tipo) {
        if ($tipo == self::$TIPO_TITULACAO) {
            return "Titulacao";
        }
        if ($tipo == self::$TIPO_PUBLICACAO) {
            return "Publicacao";
        }
        if ($tipo == self::$TIPO_PART_EVENTO) {
            return "Participacao em Evento";
        }
        if ($tipo == self::$TIPO_ATUACAO) {
            return "Atuacao";
        }
        if ($tipo == self::$TIPO_ENTREVISTA) {
            return "Entrevista";
        }
        if ($tipo == self::$INT_TIPO_INF_COMP) {
            return "Informacao Complementar";
        }
        if ($tipo == self::$TIPO_AVAL_EXTERNA) {
            return "Avaliacao Externa";
        }
        if ($tipo == self::$TIPO_AVAL_AUTOMATIZADA) {
            return "Avaliacao Automatizada";
        }
        return null;
    }

    public static function getDsTipoAval($tipoAval) {
        if ($tipoAval == self::$AVAL_AUTOMATICA) {
            return "Automática";
        }
        if ($tipoAval == self::$AVAL_MANUAL) {
            return "Manual";
        }

        return null;
    }

    public function getHmlNomeCategoria() {
        return self::getDsTipo($this->CAP_TP_CATEGORIA) . " ({$this->CAP_VL_PONTUACAO_MAX} pts)";
    }

    public function getNomeCategoria() {
        return self::getDsTipo($this->CAP_TP_CATEGORIA);
    }

    public function getIdManualCatAuto() {
        return "cat$this->CAP_ID_CATEGORIA_AVAL";
    }

    public function getIdLinhaManualCatAuto() {
        return ItemAvalProc::$CONS_ID_LINHA . $this->getIdManualCatAuto();
    }

    public function getIdItemManualCatAuto() {
        return ItemAvalProc::$CONS_ID_ITEM . $this->getIdManualCatAuto();
    }

    public function getIdNotaManualCatAuto() {
        return "nota{$this->CAP_ID_CATEGORIA_AVAL}";
    }

    public function getIdJustManualCatAuto() {
        return "just{$this->CAP_ID_CATEGORIA_AVAL}";
    }

    public function getIdContManualCatAuto() {
        return "cont{$this->CAP_ID_CATEGORIA_AVAL}";
    }

    public function admiteItensAreaSubareaObj() {
        return self::admiteItensAreaSubarea($this->CAP_TP_CATEGORIA);
    }

    public static function admiteItensAreaSubarea($tpCategoria) {
        return $tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO || $tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO || $tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO || $tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO;
    }

    public static function getHtmlMsgCategoriaNaoPontuou() {
        return "<i>" . htmlentities("Não pontuou na avaliação automática") . "</i>";
    }

    public function getHtmlMsgAvalManualCatAuto() {
        return "Avaliação Manual da Categoria ({$this->CAP_VL_PONTUACAO_MAX} pts)";
    }

    public function getDsCatExclusiva() {
        // caso de nao se aplica
        if ($this->CAP_TP_AVALIACAO == self::$AVAL_MANUAL || $this->CAP_TP_CATEGORIA == CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return NGUtil::getDsSimNao($this->CAP_CATEGORIA_EXCLUSIVA);
    }

    public function getDsNotaMax() {
        // caso de nao se aplica
        if ($this->CAP_TP_CATEGORIA == CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
            return "-";
        }
        return NGUtil::formataDecimal($this->CAP_VL_PONTUACAO_MAX);
    }

    public function isCategoriaExclusiva() {
        return !Util::vazioNulo($this->CAP_CATEGORIA_EXCLUSIVA) && $this->CAP_CATEGORIA_EXCLUSIVA == FLAG_BD_SIM;
    }

    public function isAvalManual() {
        return $this->CAP_TP_AVALIACAO == self::$AVAL_MANUAL;
    }

    public function isAvalAutomatica() {
        return $this->CAP_TP_AVALIACAO == self::$AVAL_AUTOMATICA;
    }

    public static function getListaTpDsTipo() {
        $ret = array(
            self::$TIPO_TITULACAO => self::getDsTipo(self::$TIPO_TITULACAO),
            self::$TIPO_PUBLICACAO => self::getDsTipo(self::$TIPO_PUBLICACAO),
            self::$TIPO_PART_EVENTO => self::getDsTipo(self::$TIPO_PART_EVENTO),
            self::$TIPO_ATUACAO => self::getDsTipo(self::$TIPO_ATUACAO),
            self::$TIPO_ENTREVISTA => self::getDsTipo(self::$TIPO_ENTREVISTA),
            self::$TIPO_AVAL_EXTERNA => self::getDsTipo(self::$TIPO_AVAL_EXTERNA),
            self::$TIPO_AVAL_AUTOMATIZADA => self::getDsTipo(self::$TIPO_AVAL_AUTOMATIZADA));

        return $ret;
    }

    /**
     * Essa funcao retorna os tipos de categoria que admitem avaliaçao automatica.
     * @return array
     */
    public static function getListaTpAdmiteAvalAuto() {
        return array(self::$TIPO_TITULACAO, self::$TIPO_PUBLICACAO, self::$TIPO_PART_EVENTO, self::$TIPO_ATUACAO);
    }

    public static function getListatpAvalDsAval() {
        $ret = array(
            self::$AVAL_AUTOMATICA => self::getDsTipoAval(self::$AVAL_AUTOMATICA),
            self::$AVAL_MANUAL => self::getDsTipoAval(self::$AVAL_MANUAL));

        return $ret;
    }

    /* Construtor padrão da classe */

    public function __construct($CAP_ID_CATEGORIA_AVAL, $PRC_ID_PROCESSO, $CAP_TP_CATEGORIA, $CAP_ORDEM, $CAP_VL_PONTUACAO_MAX, $CAP_CATEGORIA_EXCLUSIVA, $CAP_NR_ETAPA_AVAL, $CAP_TP_AVALIACAO, $EAP_ID_ETAPA_AVAL_PROC = NULL) {
        $this->CAP_ID_CATEGORIA_AVAL = $CAP_ID_CATEGORIA_AVAL;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->CAP_TP_CATEGORIA = $CAP_TP_CATEGORIA;
        $this->CAP_ORDEM = $CAP_ORDEM;
        $this->CAP_VL_PONTUACAO_MAX = $CAP_VL_PONTUACAO_MAX;
        $this->CAP_CATEGORIA_EXCLUSIVA = $CAP_CATEGORIA_EXCLUSIVA;
        $this->CAP_NR_ETAPA_AVAL = $CAP_NR_ETAPA_AVAL;
        $this->CAP_TP_AVALIACAO = $CAP_TP_AVALIACAO;
        $this->EAP_ID_ETAPA_AVAL_PROC = $EAP_ID_ETAPA_AVAL_PROC;
    }

    /**
     * Essa função retorna um sql que atualiza a pontuação máxima do grupo de informações complementares
     * 
     * @param int $idProcesso
     * @param string $nmGrupo
     * @return string
     */
    public static function _getSqlAtuPontMaxCatInfComp($idProcesso, $nmGrupo) {
        $tpInfComp = self::$INT_TIPO_INF_COMP;
        $sql = "update tb_cap_categoria_aval_proc 
                set 
                    `CAP_VL_PONTUACAO_MAX` = (select 
                            sum(IAP_VAL_PONTUACAO_MAX)
                        from
                            tb_iap_item_aval_proc
                        where
                            CAP_ID_CATEGORIA_AVAL = (select 
                                    CAP_ID_CATEGORIA_AVAL
                                from
                                    tb_iap_item_aval_proc
                                where
                                    PRC_ID_PROCESSO = '$idProcesso'
                                        and IAP_DS_OUTROS_PARAM like '%=$nmGrupo'))
                where
                    PRC_ID_PROCESSO = '$idProcesso'
                        and CAP_TP_CATEGORIA = '$tpInfComp'
                        and CAP_ID_CATEGORIA_AVAL = (select 
                            CAP_ID_CATEGORIA_AVAL
                        from
                            tb_iap_item_aval_proc
                        where
                            PRC_ID_PROCESSO = '$idProcesso'
                                and IAP_DS_OUTROS_PARAM like '%=$nmGrupo')";
        return $sql;
    }

    /**
     * Essa funcao recupera a SQL que trata o item 'Manual' de uma categoria de avaliaçao 
     * automatica.
     * 
     * Se a nota for zero, entao e gerado a string de exclusao.
     * 
     * Retorna NULL, caso nenhuma alteraçao seja necessaria
     * 
     * @param int $idInscricao
     * @param CategoriaAvalProc $categoria
     * @param string $nota
     * @param string $justificativa
     * @return string
     * @throws NegocioException
     */
    public static function get_sql_manual_cat_auto($idInscricao, $categoria, $nota, $justificativa = NULL) {
        try {
            // verificando se existe notaManual
            $qtNotaMan = RelNotasInsc::contarRelNotasManPorCatAuto($idInscricao, $categoria->CAP_ID_CATEGORIA_AVAL);

            // tratando caso de nada a fazer
            if ($qtNotaMan == 0 && floatval($nota) == 0) {
                // nada a fazer: Nao tem nota e continua nao tendo
                return NULL;
            }

            // criando relatorio de notas
            $relatorio = new RelNotasInsc(NULL, $idInscricao, $categoria->CAP_ID_CATEGORIA_AVAL, NULL, RelNotasInsc::$ORDEM_MAXIMA, RelNotasInsc::$TP_AVAL_MANUAL, RelNotasInsc::getDsObjAvalManualCatAuto(), floatval($nota), floatval(min(array($categoria->CAP_VL_PONTUACAO_MAX, $nota))), RelNotasInsc::$SIT_ATIVA, getIdUsuarioLogado(), $justificativa);

            // caso de nao existir nota mas tem que colocar nota
            if ($qtNotaMan == 0 && floatval($nota) != 0) {
                // inserir a nota no bd
                return $relatorio->get_sql_criacao();
            }

            // caso de existir nota e nao tem nota
            if ($qtNotaMan != 0 && floatval($nota) == 0) {
                // tem que remover a nota
                return $relatorio->get_sql_remocao_man_cat_auto();
            }

            // caso de existir nota e tem nota
            if ($qtNotaMan != 0 && floatval($nota) != 0) {
                // tem que atualizar a nota
                return $relatorio->get_sql_atualizacao_man_cat_auto();
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar sql de atualização de nota manual de categoria automática para o relatório de notas do candidato.", $e);
        }
    }

    /**
     * Conta a quantidade de categorias de avaliaçao por etapa de um processo
     * @param int $idProcesso
     * @param int $nrEtapa
     * @return int
     * @throws NegocioException
     */
    public static function contarCatAvalPorProcNrEtapa($idProcesso, $nrEtapa) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where cap.`PRC_ID_PROCESSO` = '$idProcesso'
                    and EAP_NR_ETAPA_AVAL = '$nrEtapa'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar categorias de avaliação do processo.", $e);
        }
    }

    /**
     * Conta a quantidade de categorias de avaliaçao por etapa de um processo
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @return int
     * @throws NegocioException
     */
    public static function contarCatAvalPorProcEtapa($idProcesso, $idEtapaAval) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_cap_categoria_aval_proc cap
                    where cap.`PRC_ID_PROCESSO` = '$idProcesso'
                    and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar categorias de avaliação do processo.", $e);
        }
    }

    /**
     * Esta função verifica se as categorias de avaliação de uma etapa estão OK para a criação de uma chamada
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @return boolean
     * @throws NegocioException
     */
    public static function validarCatAvalParaChamada($idProcesso, $idEtapaAval) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // sql
            // basicamente verifica se todas categorias possuem itens de avaliação
            $sql = "select count(*) as cont
                    from tb_cap_categoria_aval_proc cap
                    where cap.`PRC_ID_PROCESSO` = '$idProcesso'
                    and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'
                    and (select count(*) from tb_iap_item_aval_proc iap where cap.CAP_ID_CATEGORIA_AVAL = iap.CAP_ID_CATEGORIA_AVAL) = 0";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao tentar validar as categorias de avaliação do processo.", $e);
        }
    }

    /**
     * Conta o numero de categorias que atendem aos parametros especificados.
     * @param int $idProcesso
     * @param int $nrEtapa
     * @param int $tipoAval
     * @return int
     * @throws NegocioException
     */
    public static function contarCatAvalPorProcNrEtapaTp($idProcesso, $nrEtapa, $tipoAval = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where cap.`PRC_ID_PROCESSO` = '$idProcesso'
                    and EAP_NR_ETAPA_AVAL = '$nrEtapa'";

            if ($tipoAval != NULL) {
                $sql .= " and CAP_TP_AVALIACAO = '$tipoAval'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar categorias de avaliação do processo.", $e);
        }
    }

    /**
     * Essa funçao valida se e possivel cadastrar / alterar uma categoria.
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @param int $tpCategoriaAval
     * @param int $tpAvalCategoria
     * @param int $catExclusiva
     * @param boolean $edicao
     * @return array na forma: [validou, msgErro]
     * @throws NegocioException
     */
    public static function validarCadastroCat($idProcesso, $idEtapaAval, $tpCategoriaAval, $tpAvalCategoria, $catExclusiva, $edicao = FALSE, $idCategoriaAval = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // verificando casos ainda nao implementados
            if (!Util::vazioNulo($tpAvalCategoria) && $tpAvalCategoria == CategoriaAvalProc::$AVAL_AUTOMATICA) {

                // titulacao nao exclusiva
                $titNaoExc = $tpCategoriaAval == CategoriaAvalProc::$TIPO_TITULACAO && $catExclusiva == FLAG_BD_NAO;

                // pub, part Evento e Atu exclusivos
                $pubPartAtuExc = ($tpCategoriaAval == CategoriaAvalProc::$TIPO_PUBLICACAO || $tpCategoriaAval == CategoriaAvalProc::$TIPO_PART_EVENTO || $tpCategoriaAval == CategoriaAvalProc::$TIPO_ATUACAO) && $catExclusiva == FLAG_BD_SIM;

                if ($titNaoExc || $pubPartAtuExc) {
                    // retornando msg de nao implementado
                    return array(FALSE, 'Cálculo não implementado para esse tipo de Categoria. Caso necessite dessa configuração, informe ao administrador do sistema.');
                }
            }

            // verificando se existe alguma categoria com os parametros informados
            $sql = "select count(*) as cont,
                    CAP_ID_CATEGORIA_AVAL
                    from tb_cap_categoria_aval_proc
                    where
                    PRC_ID_PROCESSO = '$idProcesso'
                    and CAP_TP_AVALIACAO = '$tpAvalCategoria'    
                    and CAP_TP_CATEGORIA = '$tpCategoriaAval'";


            // tipo de aval auto? Incluir cat exclusiva
            if ($tpAvalCategoria == CategoriaAvalProc::$AVAL_AUTOMATICA) {
                $sql .= " and CAP_CATEGORIA_EXCLUSIVA = '$catExclusiva'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // recuperando linha
            $dados = ConexaoMysql::getLinha($resp);

            // recuperando dados e analisando
            $quant = $dados['cont'];
            $idCatBD = $dados['CAP_ID_CATEGORIA_AVAL'];
            $validou = $quant == 0;

            // caso especifico de edicao
            if (!$validou && $edicao) {
                // se tiver 1, e for a propria categoria, esta valendo.
                $validou = $quant == 1 && $idCategoriaAval != NULL && $idCatBD == $idCategoriaAval;
            }

            return array($validou, '');
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar cadastro de Categoria do Edital.", $e);
        }
    }

    /**
     * 
     * Esta função valida se as categorias de avaliação estão em conformidade com as regras para participar de uma avaliação
     * 
     * Casos analisados: 
     * 1 - Existe pelo menos uma categoria de avaliação na etapa em questão
     * 2 - Todas as categorias possuem pelo menos um item de avaliação
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @return array Array na forma [val => (TRUE, FALSE), msg => ""], onde:
     * val - Boolean indicando a situação da validação
     * msg - String com mensagem de erro, caso val seja FALSE
     * 
     * @throws NegocioException
     */
    public static function validarCatAvalProcParaAvaliacao($idProcesso, $idEtapaAval) {
        try {

            // Caso 1
            if (self::contarCatAvalPorProcEtapa($idProcesso, $idEtapaAval) == 0) {
                return array("val" => FALSE, "msg" => "As categorias de avaliação não estão configuradas!");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // SQL Para validação do caso 2
            $sql = "select count(*) as cont
                    from tb_cap_categoria_aval_proc
                    where PRC_ID_PROCESSO = '$idProcesso'
                    and (select count(*) from tb_iap_item_aval_proc iap where iap.PRC_ID_PROCESSO = PRC_ID_PROCESSO and iap.CAP_ID_CATEGORIA_AVAL = CAP_ID_CATEGORIA_AVAL) = 0";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // se tiver alguém nesse caso, estão está errado!
            if ($conexao->getResult("cont", $resp) != 0) {
                return array("val" => FALSE, "msg" => "Existem categorias de avaliação sem itens!");
            }

            // TUDO OK
            return array("val" => TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar categorias de avaliação do processo.", $e);
        }
    }

    /**
     * Funcao que aplica as regras de avaliaçao automatica da categoria para a 
     * lista de candidatos $listaCands.
     * Retorna a lista de $sqls a ser executada no BD
     * 
     * FUNCAO DE PROCESSAMENTO INTERNO! USE COM CUIDADO, POIS NAO POSSUI VALIDACOES!
     * 
     * @param array $listaCands
     * @return array - Lista de Sql's a ser executada no BD
     * @throws NegocioException
     */
    public function CLAS_aplicaRegrasAvalAuto($listaCands) {
        try {
            // recuperando itens de avaliacao
            $listaItensAval = ItemAvalProc::buscarItensAvalPorCat($this->PRC_ID_PROCESSO, $this->CAP_ID_CATEGORIA_AVAL);

            if ($listaItensAval == NULL) {
                throw new NegocioException("Categoria sem itens de avaliação!");
            }

            // chamando funçao especialista de acordo com o tipo
            if ($this->CAP_TP_CATEGORIA == self::$TIPO_TITULACAO) {
                return $this->CLAS_regraTitulacao($listaItensAval, $listaCands);
            } elseif ($this->CAP_TP_CATEGORIA == self::$TIPO_PUBLICACAO) {
                return $this->CLAS_regraPubPartAtu($listaItensAval, $listaCands);
            } elseif ($this->CAP_TP_CATEGORIA == self::$TIPO_PART_EVENTO) {
                return $this->CLAS_regraPubPartAtu($listaItensAval, $listaCands);
            } elseif ($this->CAP_TP_CATEGORIA == self::$TIPO_ATUACAO) {
                return $this->CLAS_regraPubPartAtu($listaItensAval, $listaCands);
            } elseif ($this->CAP_TP_CATEGORIA == self::$TIPO_AVAL_AUTOMATIZADA) {
                return $this->CLAS_regraAvalAutomatizada($listaItensAval, $listaCands);
            } else {
                throw new NegocioException("Regras de avaliação não programada para a categoria.");
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao aplicar regras de avaliação automática da categoria '{$this->CAP_NR_ETAPA_AVAL} - {$this->CAP_ORDEM}' para os candidatos.", $e);
        }
    }

    private function trataDadosBanco() {
        // tipo de avaliaçao
        if (array_search($this->CAP_TP_CATEGORIA, self::getListaTpAdmiteAvalAuto()) === FALSE) {
            $this->CAP_TP_AVALIACAO = CategoriaAvalProc::$AVAL_MANUAL;
        }

        // categoria exclusiva
        if ($this->CAP_TP_AVALIACAO == CategoriaAvalProc::$AVAL_MANUAL) {
            $this->CAP_CATEGORIA_EXCLUSIVA = FLAG_BD_NAO;
        }

        // categoria automatizada
        if ($this->CAP_TP_CATEGORIA == self::$TIPO_AVAL_AUTOMATIZADA) {
            // tipo automático
            $this->CAP_TP_AVALIACAO = CategoriaAvalProc::$AVAL_AUTOMATICA;
            // não exclusivo
            $this->CAP_CATEGORIA_EXCLUSIVA = FLAG_BD_NAO;
            // pontuacao máxima não se aplica
            $this->CAP_VL_PONTUACAO_MAX = self::getSemNotaMax();
        }

        // preparando campos
        $this->PRC_ID_PROCESSO = NGUtil::trataCampoStrParaBD($this->PRC_ID_PROCESSO);
        $this->CAP_TP_CATEGORIA = NGUtil::trataCampoStrParaBD($this->CAP_TP_CATEGORIA);
        $this->CAP_CATEGORIA_EXCLUSIVA = NGUtil::trataCampoStrParaBD($this->CAP_CATEGORIA_EXCLUSIVA);
        $this->CAP_TP_AVALIACAO = NGUtil::trataCampoStrParaBD($this->CAP_TP_AVALIACAO);
    }

    public static function getSemNotaMax() {
        return 0;
    }

    public function criarCategoriaAval($idEtapaAval) {
        try {

            // buscando etapa para validaçao
            $etapa = EtapaAvalProc::buscarEtapaAvalPorId($idEtapaAval);

            // nao pode editar
            if (!$etapa->podeAlterar()) {
                throw new NegocioException("Etapa não pode ser alterada.");
            }

            // validar categoria
            $validou = self::validarCadastroCat($this->PRC_ID_PROCESSO, $idEtapaAval, $this->CAP_TP_CATEGORIA, $this->CAP_TP_AVALIACAO, $this->CAP_CATEGORIA_EXCLUSIVA);
            if (!$validou[0]) {
                if (!Util::vazioNulo($validou[1])) {
                    throw new NegocioException($validou[1]);
                } else {
                    throw new NegocioException("Já existe uma categoria com os parâmetros informados.");
                }
            }

            // setando flag de avaliação automática
            $avalAuto = $this->isAvalAutomatica();

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando campos
            $this->trataDadosBanco();

            //montando sql de criação
            $sql = "insert into tb_cap_categoria_aval_proc(`PRC_ID_PROCESSO`, CAP_TP_CATEGORIA, `CAP_ORDEM`, `CAP_VL_PONTUACAO_MAX`, CAP_CATEGORIA_EXCLUSIVA, `CAP_TP_AVALIACAO`, `EAP_ID_ETAPA_AVAL_PROC`)
            values($this->PRC_ID_PROCESSO, $this->CAP_TP_CATEGORIA, (
                    SELECT COALESCE(MAX(`CAP_ORDEM`),0) + 1
                    FROM (
                    SELECT *
                    FROM tb_cap_categoria_aval_proc) AS temp
                    WHERE `PRC_ID_PROCESSO` = $this->PRC_ID_PROCESSO and EAP_ID_ETAPA_AVAL_PROC = $idEtapaAval), $this->CAP_VL_PONTUACAO_MAX, $this->CAP_CATEGORIA_EXCLUSIVA, $this->CAP_TP_AVALIACAO, $idEtapaAval)";

            // criando array de comandos e adicionando reset
            $arrayCmds = array_merge(array($sql), EtapaAvalProc::getArraySqlResetAvalProcEtapa($this->PRC_ID_PROCESSO, $idEtapaAval, $avalAuto));

            // executando no banco
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar categoria de avaliação.", $e);
        }
    }

    public static function _getSqlCriarCatInfComp($idProcesso, $idEtapaAval, $notaMax) {
        $tpInfComp = self::$INT_TIPO_INF_COMP;
        $catNaoExc = FLAG_BD_NAO;
        $avalMan = self::$AVAL_MANUAL;
        $sql = "insert into tb_cap_categoria_aval_proc(`PRC_ID_PROCESSO`, CAP_TP_CATEGORIA, `CAP_ORDEM`, `CAP_VL_PONTUACAO_MAX`, CAP_CATEGORIA_EXCLUSIVA, `CAP_TP_AVALIACAO`, `EAP_ID_ETAPA_AVAL_PROC`)
            values('$idProcesso', '$tpInfComp', (
                    SELECT COALESCE(MAX(`CAP_ORDEM`),0) + 1
                    FROM (
                    SELECT *
                    FROM tb_cap_categoria_aval_proc) AS temp
                    WHERE `PRC_ID_PROCESSO` = '$idProcesso' and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'), $notaMax, '$catNaoExc', '$avalMan','$idEtapaAval')";

        return $sql;
    }

    public function getArraySqlRemoverCatPorId() {
        // sql de exclusão
        $sql = array();

        $sql [] = "delete from tb_cap_categoria_aval_proc where `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO' and CAP_ID_CATEGORIA_AVAL = '$this->CAP_ID_CATEGORIA_AVAL'";

        // retornando array com sql de remoção e ajuste de ordenação
        return array_merge($sql, $this->getSqlReordenacaoAuto());
    }

    private function getSqlReordenacaoAuto() {
        $sql1 = "SET @counter = 0";

        $sql2 = "UPDATE tb_cap_categoria_aval_proc
                    SET CAP_ORDEM = @counter := @counter + 1
                    where PRC_ID_PROCESSO = '$this->PRC_ID_PROCESSO'
                    and EAP_ID_ETAPA_AVAL_PROC = '$this->EAP_ID_ETAPA_AVAL_PROC'
                    ORDER BY CAP_ORDEM";

        return array($sql1, $sql2);
    }

    public static function _getSqlAjustaPontMaxCatPorId($idProcesso, $idCategoria, $novaPontuacaoMax) {
        $sql = "update tb_cap_categoria_aval_proc set CAP_VL_PONTUACAO_MAX = '$novaPontuacaoMax' where `PRC_ID_PROCESSO` = '$idProcesso' and CAP_ID_CATEGORIA_AVAL = '$idCategoria'";
        return $sql;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_cap_categoria_aval_proc where PRC_ID_PROCESSO = '$idProcesso'";
    }

    /**
     * Funcao que edita a ordenaçao das categorias de avaliaçao de uma etapa.
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @param string $novaOrdenacao - String na forma: [codCat1:novaOrdem1;codCat2:novaOrdem2;...]
     * @throws NegocioException
     */
    public static function editarOrdensCategoriasAval($idProcesso, $idEtapaAval, $novaOrdenacao) {
        try {

            // buscando etapa para validaçao
            $etapa = EtapaAvalProc::buscarEtapaAvalPorId($idEtapaAval, $idProcesso);

            // nao pode editar
            if (!$etapa->podeAlterar()) {
                throw new NegocioException("Etapa não pode ser alterada.");
            }

            // destrinchando string
            $vetAtu = array(); // vetor na forma [idCategoria => novaOrdem]
            $vetTemp = explode(";", $novaOrdenacao);
            if (count($vetTemp) != 0) {
                array_pop($vetTemp);
            }

            foreach ($vetTemp as $atu) {
                $temp = explode(":", $atu);
                $vetAtu[$temp[0]] = $temp[1];
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // montando sqls
            $vetSql = array();

            foreach ($vetAtu as $idCat => $ordem) {
                // verificando validacao
                if (Util::vazioNulo($idCat) || Util::vazioNulo($ordem)) {
                    throw new NegocioException("Parâmetros incorretos.");
                }

                //montando sql de atualizacao
                $sql = "update tb_cap_categoria_aval_proc 
                        set CAP_ORDEM = '$ordem'
                    where CAP_ID_CATEGORIA_AVAL = '$idCat'
                          and PRC_ID_PROCESSO = '$idProcesso'";

                // inserindo no array
                $vetSql [] = $sql;
            }

            //recuperando sql de invalidacao de provaveis avaliaçao
            $vetSql = array_merge($vetSql, EtapaAvalProc::getArraySqlResetAvalProcEtapa($idProcesso, $idEtapaAval, TRUE));


//            print_r($vetSql);
//            exit;
//            
            $conexao->execTransacaoArray($vetSql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar ordenação das categoria de avaliação.", $e);
        }
    }

    public function excluirCategoriaAval() {
        try {

            // buscando etapa para validaçao
            $etapa = EtapaAvalProc::buscarEtapaAvalPorId($this->EAP_ID_ETAPA_AVAL_PROC);

            // nao pode editar
            if (!$etapa->podeAlterar()) {
                throw new NegocioException("Etapa não pode ser alterada.");
            }

            // setando flag de avaliação automática
            $avalAuto = $this->isAvalAutomatica();

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // criando array de comandos com sql de remoçao de itens da categoria
            $arrayCmds [] = ItemAvalProc::getSqlExclusaoItemPorCategoriaAval($this->CAP_ID_CATEGORIA_AVAL);

            // removendo possiveis notas da categoria
            $arrayCmds [] = RelNotasInsc::getSqlExclusaoPorCatAval($this->CAP_ID_CATEGORIA_AVAL);

            //montando sql de exclusao
            $arrayCmds [] = "delete from tb_cap_categoria_aval_proc
                    where CAP_ID_CATEGORIA_AVAL = '$this->CAP_ID_CATEGORIA_AVAL'";

            // adicionando sql de remocao de itens da etapa e resultado final
            $arrayCmds [] = MacroConfProc::getSqlExcluirConfProc($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC);
            $arrayCmds [] = MacroConfProc::getSqlExcluirConfProc($this->PRC_ID_PROCESSO);

            // adicionando sql de reordenação
            $arrayCmds = array_merge($arrayCmds, $this->getSqlReordenacaoAuto());

            // adicionando sql de reset de avaliaçoes da etapa
            $arrayCmds = array_merge($arrayCmds, EtapaAvalProc::getArraySqlResetAvalProcEtapa($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC, $avalAuto));

            //inserindo no banco
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir categoria de avaliação.", $e);
        }
    }

    public function editarCategoriaAval() {
        try {

            // buscando etapa para validaçao
            $etapa = EtapaAvalProc::buscarEtapaAvalPorId($this->EAP_ID_ETAPA_AVAL_PROC);

            // nao pode editar
            if (!$etapa->podeAlterar()) {
                throw new NegocioException("Etapa não pode ser alterada.");
            }

            // validar categoria
            $validou = self::validarCadastroCat($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC, $this->CAP_TP_CATEGORIA, $this->CAP_TP_AVALIACAO, $this->CAP_CATEGORIA_EXCLUSIVA, TRUE, $this->CAP_ID_CATEGORIA_AVAL);
            if (!$validou[0]) {
                if (!Util::vazioNulo($validou[1])) {
                    throw new NegocioException($validou[1]);
                } else {
                    throw new NegocioException("Já existe uma categoria com os parâmetros informados.");
                }
            }

            // setando flag de avaliação automática
            $avalAuto = $this->isAvalAutomatica();

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando campos
            $this->trataDadosBanco();

            //montando sql de atualizacao
            $sql = "update tb_cap_categoria_aval_proc 
                        set CAP_VL_PONTUACAO_MAX = '$this->CAP_VL_PONTUACAO_MAX',
                        CAP_CATEGORIA_EXCLUSIVA = $this->CAP_CATEGORIA_EXCLUSIVA,
                        CAP_TP_AVALIACAO = $this->CAP_TP_AVALIACAO
                    where CAP_ID_CATEGORIA_AVAL = '$this->CAP_ID_CATEGORIA_AVAL'";

            // definindo array de comandos e adicionando reset
            $arrayCmds = array_merge(array($sql), EtapaAvalProc::getArraySqlResetAvalProcEtapa($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC, $avalAuto));

            //inserindo no banco
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar categoria de avaliação.", $e);
        }
    }

    /**
     * Funcao que executa as regras de avaliacao para titulacao
     * @param ItemAvalProc $listaItensAval  - Array com lista de Itens
     * @param array $listaCands
     * @throws NegocioException
     */
    private function CLAS_regraTitulacao($listaItensAval, $listaCands) {
        // em  caso de erro...
        $dsRegra = self::getDsTipo($this->CAP_TP_CATEGORIA);

        try {
            // variavel de retorno
            $ret = array();

            // caso de ser categoria exclusiva
            if ($this->isCategoriaExclusiva()) {
                //percorrendo itens para processar
                foreach ($listaItensAval as $item) {

                    //percorrendo candidatos a avaliar
                    foreach ($listaCands as $idInscricao => $idCandidato) {

                        // verificando se o candidato possui uma titulacao que 'casa' com o item
                        $vetIdCasou = $item->CLAS_candidato_casa_item($this->CAP_TP_CATEGORIA, $idCandidato);
                        if ($vetIdCasou !== FALSE) {

//                            print_r($idCasou);
//                            print_r("<br/>");
//                            
//                          
                            // inserindo sql de exclusao de relatorios da categoria, pois é exclusivo
                            $ret [] = RelNotasInsc::CLAS_getSqlExclusaoCatAuto($idInscricao, $this->CAP_ID_CATEGORIA_AVAL);

                            // recuperando sql para o relatorio de notas
                            $ret [] = $item->CLAS_get_sql_rel_notas($this->CAP_TP_CATEGORIA, $idInscricao, $vetIdCasou, $this->CAP_VL_PONTUACAO_MAX, $this->isCategoriaExclusiva());

                            // removendo da lista de candidatos, pois ja teve sua nota
                            unset($listaCands[$idInscricao]);
                        }
                    }
                }
            } else {
                //@todo WFUTURO: Implementar aqui regra de titulacao nao exclusiva, caso exista algum dia.
                throw new NegocioException("Regra de '$dsRegra' não exclusiva ainda não está implementada.");
            }

            // retornando array com sqls 
            return $ret;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao aplicar regras de avaliação automática da categoria '$dsRegra' para os candidatos.", $e);
        }
    }

    /**
     * Funcao que executa as regras de avaliacao para a categoria de avaliação automatizada
     * 
     * @param ItemAvalProc $listaItensAval  - Array com lista de Itens
     * @param array $listaCands
     * @throws NegocioException
     */
    private function CLAS_regraAvalAutomatizada($listaItensAval, $listaCands) {
        //
        // para fins de log...
        $dsRegra = self::getDsTipo($this->CAP_TP_CATEGORIA);

        try {

            // variavel de retorno
            $ret = array();

            //percorrendo itens para processar
            foreach ($listaItensAval as $item) {

                if ($item->getIAP_TP_ITEM() == ItemAvalProc::$TP_AUT_ORDEM_INSC) {
                    // nada a fazer, pois o trabalho será feito no critério de desempate
                } else {
                    throw new NegocioException("Regras não programadas para item de categoria de avaliação automatizada.");
                }
            }

            // retornando array com sqls 
            return $ret;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao aplicar regras de avaliação automática da categoria '$dsRegra' para os candidatos.", $e);
        }
    }

    /**
     * Funcao que executa as regras de avaliacao para publicacao, part evento e atuaçao
     * @param ItemAvalProc $listaItensAval  - Array com lista de Itens
     * @param array $listaCands
     * @throws NegocioException
     */
    private function CLAS_regraPubPartAtu($listaItensAval, $listaCands) {
        //
        // para fins de log...
        $dsRegra = self::getDsTipo($this->CAP_TP_CATEGORIA);

        try {
            // variavel de retorno
            $ret = array();

            // vetor com somatorio de notas do candidato
            // forma: [idInscricao => SomaCategoria]
            $somasCdt = array();
            // vetor com somatorio de notas do grupo
            // forma: [idInscricao => array(IdGrupo => array(somaGrupo, limite))]
            $somasGrupoCdt = array();

            //percorrendo itens para processar
            foreach ($listaItensAval as $item) {

                //percorrendo candidatos a avaliar
                foreach ($listaCands as $idInscricao => $idCandidato) {

                    // verificando se o candidato possui em seu curriculo um item que 'casa' com as regras
                    $vetIdCasou = $item->CLAS_candidato_casa_item($this->CAP_TP_CATEGORIA, $idCandidato);

                    // recuperando somas e eliminando do vetor
                    $somasCdt[$idInscricao] = isset($somasCdt[$idInscricao]) ? $somasCdt[$idInscricao] + $vetIdCasou['soma'] : $vetIdCasou['soma'];
                    unset($vetIdCasou['soma']);

                    // recuperando soma de notas do grupo
                    if (isset($vetIdCasou['grupo'])) {
//                        print_r($vetIdCasou['grupo']);
//                        print_r("<br/>");

                        if (isset($somasGrupoCdt[$idInscricao])) {
                            if (isset($somasGrupoCdt[$idInscricao][$vetIdCasou['grupo'][0]])) {
                                $somasGrupoCdt[$idInscricao][$vetIdCasou['grupo'][0]][0] += $vetIdCasou['grupo'][1];
                            } else {
                                $somasGrupoCdt[$idInscricao][$vetIdCasou['grupo'][0]] = array($vetIdCasou['grupo'][1], $vetIdCasou['grupo'][2]);
                            }
                        } else {
                            $somasGrupoCdt[$idInscricao][$vetIdCasou['grupo'][0]] = array($vetIdCasou['grupo'][1], $vetIdCasou['grupo'][2]);
//                            print_r($somasGrupoCdt);
//                            print_r("<br/>");
                        }
                        unset($vetIdCasou['grupo']);
                    }

                    if ($vetIdCasou !== FALSE) {

//                        print_r($vetIdCasou);
//                        print_r("<br/>");
//                        exit;
//                            
//                          
                        // recuperando sql para o relatorio de notas
                        $ret [] = $item->CLAS_get_sql_rel_notas($this->CAP_TP_CATEGORIA, $idInscricao, array_keys($vetIdCasou), NULL, $this->isCategoriaExclusiva());

                        // caso de ser categoria exclusiva
                        if ($this->isCategoriaExclusiva()) {
                            // removendo da lista de candidatos, pois ja teve sua nota
                            unset($listaCands[$idInscricao]);
                        }
                    } else {
                        // Caso que não casou:
                        // removendo possível dado antigo
                        $ret [] = RelNotasInsc::getStrSqlExcPorInscCatItem($idInscricao, $this->CAP_ID_CATEGORIA_AVAL, $item->getIAP_ID_ITEM_AVAL());
                    }
                }
            }


            // removendo ajustes anteriores
            foreach ($somasCdt as $idInscricao => $somaCat) {
                $ret [] = RelNotasInsc::CLAS_getSqlExclusaoAjuste($idInscricao, $this->CAP_ID_CATEGORIA_AVAL);
            }

//            print_r($somasGrupoCdt);
//            print_r("<br/>");
            // aplicando restricao de grupo
            //forma: [idInscricao => array(IdGrupo => array(somaGrupo, limite))]
            foreach ($somasGrupoCdt as $idInscricao => $vetGrupo) {
                // percorrendo grupos
                $grupos = array_keys($vetGrupo);
                foreach ($grupos as $grupo) {
                    if ($vetGrupo[$grupo][0] > $vetGrupo[$grupo][1]) {
                        // recuperando sql de ajuste     
                        $msg = "Ajustando para o limite máximo do grupo $grupo.";
                        $ret [] = $item->CLAS_get_sql_ajuste_cat($idInscricao, $dsRegra, ($vetGrupo[$grupo][0] - $vetGrupo[$grupo][1]), $msg);

                        // retirando do somatorio final
                        $somasCdt[$idInscricao] -= $vetGrupo[$grupo][0] - $vetGrupo[$grupo][1];
                    }
                }
            }

            // aplicando restriçao da categoria nos candidatos
            foreach ($somasCdt as $idInscricao => $somaCat) {
                if ($somaCat > $this->CAP_VL_PONTUACAO_MAX) {
                    // inserindo ajuste
                    $ret [] = $item->CLAS_get_sql_ajuste_cat($idInscricao, $dsRegra, ($somaCat - $this->CAP_VL_PONTUACAO_MAX));
                }
            }


            // retornando array com sqls 
            return $ret;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao aplicar regras de avaliação automática da categoria '$dsRegra' para os candidatos.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $nrEtapa - Opcional
     * @return array - Array com tipos de categoria ja usados no processo.
     * @throws NegocioException
     */
    public static function buscarTpCatAvalUsadosPorProc($idProcesso, $nrEtapa = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        CAP_TP_CATEGORIA
                    from
                        tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where
                        cap.PRC_ID_PROCESSO = '$idProcesso'";

            if ($nrEtapa != NULL) {
                $sql .= " and EAP_NR_ETAPA_AVAL = '$nrEtapa'";
            }

            $sql .= " order by CAP_ORDEM";

//            print_r($sql);
            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //adicionando no vetor
                $vetRetorno[$i] = $dados['CAP_TP_CATEGORIA'];
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar tipos de categoria de avaliação usados no processo.", $e);
        }
    }

    /**
     * Busca categorias que atendem aos parametros especificados.
     * @param int $idProcesso
     * @param int $nrEtapa
     * @param int $tipoAval
     * @param boolean $comAutomatizada Diz se é para trazer também a categoria de avaliação automatizada. Padrão é true
     * @return \CategoriaAvalProc|null
     * @throws NegocioException
     */
    public static function buscarCatAvalPorProcEtapaTp($idProcesso, $nrEtapa, $tipoAval = NULL, $comAutomatizada = TRUE) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        CAP_ID_CATEGORIA_AVAL,
                        cap.PRC_ID_PROCESSO,
                        CAP_TP_CATEGORIA,
                        CAP_ORDEM,
                        CAP_VL_PONTUACAO_MAX,
                        CAP_CATEGORIA_EXCLUSIVA,
                        EAP_NR_ETAPA_AVAL as CAP_NR_ETAPA_AVAL,
                        CAP_TP_AVALIACAO,
                        cap.EAP_ID_ETAPA_AVAL_PROC
                    from
                        tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where
                        cap.PRC_ID_PROCESSO = '$idProcesso'
                            and EAP_NR_ETAPA_AVAL = '$nrEtapa'";

            if ($tipoAval != NULL) {
                $sql .= " and CAP_TP_AVALIACAO = '$tipoAval'";
            }

            if (!$comAutomatizada) {
                $sql .= " and CAP_TP_CATEGORIA != '" . self::$TIPO_AVAL_AUTOMATIZADA . "'";
            }

            $sql .= " order by CAP_ORDEM";

//            print_r($sql);
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

                $categoriaTemp = new CategoriaAvalProc($dados['CAP_ID_CATEGORIA_AVAL'], $dados['PRC_ID_PROCESSO'], $dados['CAP_TP_CATEGORIA'], $dados['CAP_ORDEM'], $dados['CAP_VL_PONTUACAO_MAX'], $dados['CAP_CATEGORIA_EXCLUSIVA'], $dados['CAP_NR_ETAPA_AVAL'], $dados['CAP_TP_AVALIACAO'], $dados['EAP_ID_ETAPA_AVAL_PROC']);

                //adicionando no vetor
                $vetRetorno[$i] = $categoriaTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar categorias de avaliação do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @param char $tpCategoria
     * @return \CategoriaAvalProc|null Array de Categorias
     * @throws NegocioException
     */
    public static function buscarCatAValPorProcIdEtapa($idProcesso, $idEtapaAval, $tpCategoria = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        CAP_ID_CATEGORIA_AVAL,
                        cap.PRC_ID_PROCESSO,
                        CAP_TP_CATEGORIA,
                        CAP_ORDEM,
                        CAP_VL_PONTUACAO_MAX,
                        CAP_CATEGORIA_EXCLUSIVA,
                        EAP_NR_ETAPA_AVAL as CAP_NR_ETAPA_AVAL,
                        CAP_TP_AVALIACAO,
                        cap.EAP_ID_ETAPA_AVAL_PROC
                    from
                        tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where
                        cap.PRC_ID_PROCESSO = '$idProcesso'
                            and cap.EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";

            if ($tpCategoria != NULL) {
                $sql .= " and CAP_TP_CATEGORIA = '$tpCategoria'";
            }

            $sql .= " order by CAP_ORDEM";

//            print_r($sql);
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

                $categoriaTemp = new CategoriaAvalProc($dados['CAP_ID_CATEGORIA_AVAL'], $dados['PRC_ID_PROCESSO'], $dados['CAP_TP_CATEGORIA'], $dados['CAP_ORDEM'], $dados['CAP_VL_PONTUACAO_MAX'], $dados['CAP_CATEGORIA_EXCLUSIVA'], $dados['CAP_NR_ETAPA_AVAL'], $dados['CAP_TP_AVALIACAO'], $dados['EAP_ID_ETAPA_AVAL_PROC']);

                //adicionando no vetor
                $vetRetorno[$i] = $categoriaTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar categorias de avaliação do processo.", $e);
        }
    }

    public static function buscarCatAvalPorId($idCategoriaAval) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        CAP_ID_CATEGORIA_AVAL,
                        cap.PRC_ID_PROCESSO,
                        CAP_TP_CATEGORIA,
                        CAP_ORDEM,
                        CAP_VL_PONTUACAO_MAX,
                        CAP_CATEGORIA_EXCLUSIVA,
                        EAP_NR_ETAPA_AVAL as CAP_NR_ETAPA_AVAL,
                        CAP_TP_AVALIACAO,
                        cap.EAP_ID_ETAPA_AVAL_PROC
                    from
                        tb_cap_categoria_aval_proc cap
                    join tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where
                        cap.CAP_ID_CATEGORIA_AVAL = '$idCategoriaAval'";


//            print_r($sql);
            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                // disparando exceçao
                throw new NegocioException("Categoria de Avaliação não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $categoriaTemp = new CategoriaAvalProc($dados['CAP_ID_CATEGORIA_AVAL'], $dados['PRC_ID_PROCESSO'], $dados['CAP_TP_CATEGORIA'], $dados['CAP_ORDEM'], $dados['CAP_VL_PONTUACAO_MAX'], $dados['CAP_CATEGORIA_EXCLUSIVA'], $dados['CAP_NR_ETAPA_AVAL'], $dados['CAP_TP_AVALIACAO'], $dados['EAP_ID_ETAPA_AVAL_PROC']);


            // retornando
            return $categoriaTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar categoria de avaliação do processo.", $e);
        }
    }

    public function getVlNotaMaxFormatada() {
        // caso de nao se aplica
        if ($this->CAP_TP_CATEGORIA == CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return NGUtil::formataDecimal($this->CAP_VL_PONTUACAO_MAX);
    }

    public function getDsSelectCategoria() {
        return $this->getDsTipo($this->CAP_TP_CATEGORIA) . " (Cód $this->CAP_ID_CATEGORIA_AVAL)";
    }

    public function isSomenteLeitura() {
        return $this->CAP_TP_CATEGORIA == self::$INT_TIPO_INF_COMP;
    }

    /* GET FIELDS FROM TABLE */

    function getCAP_ID_CATEGORIA_AVAL() {
        return $this->CAP_ID_CATEGORIA_AVAL;
    }

    /* End of get CAP_ID_CATEGORIA_AVAL */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getCAP_TP_CATEGORIA() {
        return $this->CAP_TP_CATEGORIA;
    }

    /* End of get CAP_TP_CATEGORIA */

    function getCAP_ORDEM() {
        return $this->CAP_ORDEM;
    }

    /* End of get CAP_ORDEM */

    function getCAP_VL_PONTUACAO_MAX() {
        return $this->CAP_VL_PONTUACAO_MAX;
    }

    /* End of get CAP_VL_PONTUACAO_MAX */

    function getCAP_CATEGORIA_EXCLUSIVA() {
        return $this->CAP_CATEGORIA_EXCLUSIVA;
    }

    /* End of get CAP_CATEGORIA_EXCLUSIVA */

    function getCAP_NR_ETAPA_AVAL() {
        return $this->CAP_NR_ETAPA_AVAL;
    }

    /* End of get CAP_NR_ETAPA_AVAL */

    function getCAP_TP_AVALIACAO() {
        return $this->CAP_TP_AVALIACAO;
    }

    /* End of get CAP_TP_AVALIACAO */

    public function getEAP_ID_ETAPA_AVAL_PROC() {
        return $this->EAP_ID_ETAPA_AVAL_PROC;
    }

    /* SET FIELDS FROM TABLE */

    function setCAP_ID_CATEGORIA_AVAL($value) {
        $this->CAP_ID_CATEGORIA_AVAL = $value;
    }

    /* End of SET CAP_ID_CATEGORIA_AVAL */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setCAP_TP_CATEGORIA($value) {
        $this->CAP_TP_CATEGORIA = $value;
    }

    /* End of SET CAP_TP_CATEGORIA */

    function setCAP_ORDEM($value) {
        $this->CAP_ORDEM = $value;
    }

    /* End of SET CAP_ORDEM */

    function setCAP_VL_PONTUACAO_MAX($value) {
        $this->CAP_VL_PONTUACAO_MAX = $value;
    }

    /* End of SET CAP_VL_PONTUACAO_MAX */

    function setCAP_CATEGORIA_EXCLUSIVA($value) {
        $this->CAP_CATEGORIA_EXCLUSIVA = $value;
    }

    /* End of SET CAP_CATEGORIA_EXCLUSIVA */

    function setCAP_NR_ETAPA_AVAL($value) {
        $this->CAP_NR_ETAPA_AVAL = $value;
    }

    /* End of SET CAP_NR_ETAPA_AVAL */

    function setCAP_TP_AVALIACAO($value) {
        $this->CAP_TP_AVALIACAO = $value;
    }

    /* End of SET CAP_TP_AVALIACAO */
}

?>
