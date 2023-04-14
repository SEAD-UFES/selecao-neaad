<?php

/**
 * tb_iap_item_aval_proc class
 * This class manipulates the table ItemAvalProc
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
require_once $CFG->rpasta . "/negocio/RelNotasInsc.php";
require_once $CFG->rpasta . "/negocio/Publicacao.php";
require_once $CFG->rpasta . "/negocio/ParticipacaoEvento.php";
require_once $CFG->rpasta . "/negocio/Atuacao.php";

class ItemAvalProc {

    private $IAP_ID_ITEM_AVAL;
    private $PRC_ID_PROCESSO;
    private $CAP_ID_CATEGORIA_AVAL;
    private $IAP_TP_ITEM;
    private $IAP_ORDEM;
    private $IAP_ID_AREA_CONH;
    private $IAP_ID_SUBAREA_CONH;
    private $IAP_VAL_PONTUACAO;
    private $IAP_VAL_PONTUACAO_MAX;
    private $IAP_DS_OUTROS_PARAM;
    private $IAP_ID_SUBGRUPO;
    public static $TP_ENT_PONT_GERAL = "P"; // pontuacao geral da entrevista
    public static $TP_EXT_NOTA_EXTERNA = "E"; // pontuacao externa
    public static $TP_AUT_ORDEM_INSC = "O"; // pontuacao automática: Ordem de inscrição
    public static $TP_INF_COMP = "I"; // tipo informação complementar
    // constantes importantes
    public static $SEM_PONTUACAO = 0;
    public static $PONTUACAO_MAX_ITEM = 999.99;
    public static $ORDEM_MAX = 99;
    public static $ID_GRUPO_SEM_AGRUPAMENTO = 'S';
    public static $DS_GRUPO_SEM_AGRUPAMENTO = 'Sem Agrupamento';
    public static $ID_GRUPO_NOVO_GRUPO = 'N';
    public static $DS_GRUPO_NOVO_GRUPO = 'Novo Grupo';
    private static $NOTA_TP_AUT_ORDEM_INSC = 1;
    // processamento interno
    private $CLAS_STR_ESQUELETO_SQL; // NUNCA ACESSAR ESSA VARIAVEL DIRETAMENTE!
    // armazena um array do tipo (chave => valor) com os parametros do item
    private $CLAS_ARRAY_PARAM_PROC; // NUNCA ACESSAR ESSA VARIAVEL ANTES DE CARREGA-LA!
    // armazena as notas obtidas com o casamento
    private $notaReal;
    private $notaNormalizada;
    // parametros do item
    public static $PARAM_TIT_STFORMACAO = "stFormacao";
    public static $PARAM_TIT_EXCLUSIVO = "exc";
    public static $PARAM_TIT_SEGGRADUACAO = "seg";
    public static $PARAM_TIT_CARGA_HORARIA_MIN = "cargaHorariaMin";
    // variaveis constantes
    private static $SEPARADOR_PARAM = ";";
    private static $SEPARADOR_VALOR = "=";
    private static $C_FALSE = "false";
    private static $C_TRUE = "true";
    // constantes para visualizacao
    public static $CONS_ID_ITEM = "item";
    public static $CONS_ID_LINHA = "linha";
    // campos sob demanda
    private $NM_AREA_CONH;
    private $NM_SUBAREA_CONH;

    public static function getDsTipo($tpCategoria, $tipo) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO) {
            if ($tipo == TipoCurso::getTpDoutorado()) {
                return "Doutorado";
            }
            if ($tipo == TipoCurso::getTpMestrado()) {
                return "Mestrado";
            }
            if ($tipo == TipoCurso::getTpEspecializacao()) {
                return "Especialização";
            }
            if ($tipo == TipoCurso::getTpGraduacao()) {
                return "Graduação";
            }
            if ($tipo == TipoCurso::getTpAperfeicoamento()) {
                return "Aperfeiçoamento";
            }
            if ($tipo == TipoCurso::getTpCapacitacao()) {
                return "Capacitação";
            }
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO) {
            return Publicacao::getDsTipo($tipo);
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
            return ParticipacaoEvento::getDsTipo($tipo);
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO) {
            return Atuacao::getDsTipo($tipo);
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_ENTREVISTA) {
            if ($tipo == self::$TP_ENT_PONT_GERAL) {
                return "Média Final da Entrevista";
            }
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_AVAL_EXTERNA) {
            if ($tipo == self::$TP_EXT_NOTA_EXTERNA) {
                return "Aval. Externa: ";
            }
        }

        if ($tpCategoria == CategoriaAvalProc::$INT_TIPO_INF_COMP) {
            if ($tipo == self::$TP_INF_COMP) {
                return "Inf: ";
            }
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
            if ($tipo == self::$TP_AUT_ORDEM_INSC) {
                return "Ordem de Inscrição";
            }
        }
    }

    public static function getListaTpDsTipo($tpCategoria) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO) {
            return array(TipoCurso::getTpDoutorado() => self::getDsTipo($tpCategoria, TipoCurso::getTpDoutorado()),
                TipoCurso::getTpMestrado() => self::getDsTipo($tpCategoria, TipoCurso::getTpMestrado()),
                TipoCurso::getTpEspecializacao() => self::getDsTipo($tpCategoria, TipoCurso::getTpEspecializacao()),
                TipoCurso::getTpGraduacao() => self::getDsTipo($tpCategoria, TipoCurso::getTpGraduacao()),
                TipoCurso::getTpCapacitacao() => self::getDsTipo($tpCategoria, TipoCurso::getTpCapacitacao()),
                TipoCurso::getTpAperfeicoamento() => self::getDsTipo($tpCategoria, TipoCurso::getTpAperfeicoamento()));
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO) {
            return Publicacao::getListaTipoDsTipo();
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
            return ParticipacaoEvento::getListaTipoDsTipo();
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO) {
            return Atuacao::getListaTipoDsTipo();
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_ENTREVISTA) {
            return array(self::$TP_ENT_PONT_GERAL => self::getDsTipo($tpCategoria, self::$TP_ENT_PONT_GERAL));
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_AVAL_EXTERNA) {
            return array(self::$TP_EXT_NOTA_EXTERNA => self::getDsTipo($tpCategoria, self::$TP_EXT_NOTA_EXTERNA));
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
            return array(self::$TP_AUT_ORDEM_INSC => self::getDsTipo($tpCategoria, self::$TP_AUT_ORDEM_INSC));
        }
    }

    public static function getDsTipoSemAcento($tpCategoria, $tipo) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO) {
            if ($tipo == TipoCurso::getTpDoutorado()) {
                return "Doutorado";
            }
            if ($tipo == TipoCurso::getTpMestrado()) {
                return "Mestrado";
            }
            if ($tipo == TipoCurso::getTpEspecializacao()) {
                return "Especializacao";
            }
            if ($tipo == TipoCurso::getTpGraduacao()) {
                return "Graduacao";
            }
            if ($tipo == TipoCurso::getTpAperfeicoamento()) {
                return "Aperfeicoamento";
            }
            if ($tipo == TipoCurso::getTpCapacitacao()) {
                return "Capacitacao";
            }
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO) {
            return Publicacao::getDsTipoSemAcento($tipo);
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
            return ParticipacaoEvento::getDsTipoSemAcento($tipo);
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO) {
            return Atuacao::getDsTipoSemAcento($tipo);
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_ENTREVISTA) {
            if ($tipo == self::$TP_ENT_PONT_GERAL) {
                return "Media Final da Entrevista";
            }
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_AVAL_EXTERNA) {
            if ($tipo == self::$TP_EXT_NOTA_EXTERNA) {
                return "Aval. Externa: ";
            }
        }

        if ($tpCategoria == CategoriaAvalProc::$INT_TIPO_INF_COMP) {
            if ($tipo == self::$TP_INF_COMP) {
                return "Inf: ";
            }
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
            if ($tipo == self::$TP_AUT_ORDEM_INSC) {
                return "Ordem de Inscricao";
            }
        }
    }

    public static function getArrayJSTpFixarArea($tpCategoria) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
            return strArrayJavaScript(array(ParticipacaoEvento::getTpFixarArea()));
        } else {
            return strArrayJavaScript(array());
        }
    }

    public static function getArrayTpFixarArea($tpCategoria) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
            return array(ParticipacaoEvento::getTpFixarArea());
        } else {
            return array();
        }
    }

    public static function getSubAreaFixa($tpCategoria) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
            return ParticipacaoEvento::getSubAreaFixa();
        } else {
            return "";
        }
    }

    public static function getAreaFixa($tpCategoria) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
            return ParticipacaoEvento::getAreaFixa();
        } else {
            return "";
        }
    }

    /**
     * 
     * @param boolean $semAcento Informa se deve ser retornado o nome sem acento
     * @return string
     */
    public function getDsAreaSubarea($semAcento = FALSE) {
        // caso de nao ter subarea
        if (Util::vazioNulo($this->IAP_ID_AREA_CONH) && Util::vazioNulo($this->IAP_ID_SUBAREA_CONH)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        // verificando se os campos ja foram carregados
        if (Util::vazioNulo($this->NM_AREA_CONH)) {
            // carregando campos
            $this->carregaNmAreaSubarea();
        }
        $ret = $this->NM_AREA_CONH;
        $ret .=!Util::vazioNulo($this->NM_SUBAREA_CONH) ? " - {$this->NM_SUBAREA_CONH}" : "";

        return !$semAcento ? $ret : removerAcentos($ret);
    }

    private function carregaNmAreaSubarea() {
        try {

            $area = buscarAreaConhPorIdCT($this->IAP_ID_AREA_CONH);
            $this->NM_AREA_CONH = $area->getARC_NM_AREA_CONH();

            $subArea = buscarAreaConhPorIdCT($this->IAP_ID_SUBAREA_CONH);

            $this->NM_SUBAREA_CONH = !Util::vazioNulo($subArea) ? $subArea->getARC_NM_AREA_CONH() : "";
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao carregar nomes de área / subarea.", $e);
        }
    }

    /**
     * 
     * @param int $tpCategoria
     * @return string Retorna o nome completo do item para uso em arquivos (sem acento)
     */
    public function getNomeItemCompletoArq($tpCategoria) {
        //@todo Definir nome amigavel dos parametros
        $dsParam = Util::vazioNulo($this->IAP_DS_OUTROS_PARAM) ? "" : " {$this->IAP_DS_OUTROS_PARAM}";
        $ret = self::getDsTipoSemAcento($tpCategoria, $this->IAP_TP_ITEM) . "$dsParam";

        // tem área?
        if (CategoriaAvalProc::admiteItensAreaSubarea($tpCategoria)) {
            if (!Util::vazioNulo($this->IAP_ID_AREA_CONH) || !Util::vazioNulo($this->IAP_ID_SUBAREA_CONH)) {
                $ret .= " em {$this->getDsAreaSubarea(TRUE)}";
            } else {
                $ret .= " em QA";
            }
        }
        return $ret;
    }

    public function getHmlNomeItem($tpCategoria) {
        //@todo Definir nome amigavel dos parametros
        $dsParam = Util::vazioNulo($this->IAP_DS_OUTROS_PARAM) ? "" : " {$this->IAP_DS_OUTROS_PARAM}";
        $ret = self::getDsTipo($tpCategoria, $this->IAP_TP_ITEM) . "$dsParam";

        // tem área?
        if (CategoriaAvalProc::admiteItensAreaSubarea($tpCategoria)) {
            if (!Util::vazioNulo($this->IAP_ID_AREA_CONH) || !Util::vazioNulo($this->IAP_ID_SUBAREA_CONH)) {
                $ret .= " em {$this->getDsAreaSubarea()}";
            } else {
                $ret .= " em <i>qualquer área</i>";
            }
        }

        // Tem pontuação máxima
        if (!Util::vazioNulo($this->IAP_VAL_PONTUACAO_MAX)) {
            $ret .= " ({$this->IAP_VAL_PONTUACAO_MAX} pts)";
        }

        return $ret;
    }

    public function getDsTipoItem($tpCategoria) {
        $ret = self::getDsTipo($tpCategoria, $this->IAP_TP_ITEM);
        if ($tpCategoria == CategoriaAvalProc::$TIPO_AVAL_EXTERNA) {
            $ret .= " $this->IAP_DS_OUTROS_PARAM";
        }
        if ($tpCategoria == CategoriaAvalProc::$INT_TIPO_INF_COMP) {
            // separando param
            $temp = explode("=", $this->IAP_DS_OUTROS_PARAM);
            $ret .= " $temp[1]";
        }
        return $ret;
    }

    public function getDsSelectCategoria($tpCategoria) {
        return $this->getDsTipoItem($tpCategoria) . " (Cód $this->IAP_ID_ITEM_AVAL)";
    }

    /* Construtor padrão da classe */

    public function __construct($IAP_ID_ITEM_AVAL, $PRC_ID_PROCESSO, $CAP_ID_CATEGORIA_AVAL, $IAP_TP_ITEM, $IAP_ORDEM, $IAP_ID_AREA_CONH, $IAP_ID_SUBAREA_CONH, $IAP_VAL_PONTUACAO, $IAP_VAL_PONTUACAO_MAX, $IAP_DS_OUTROS_PARAM, $IAP_ID_SUBGRUPO) {
        $this->IAP_ID_ITEM_AVAL = $IAP_ID_ITEM_AVAL;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->CAP_ID_CATEGORIA_AVAL = $CAP_ID_CATEGORIA_AVAL;
        $this->IAP_TP_ITEM = $IAP_TP_ITEM;
        $this->IAP_ORDEM = $IAP_ORDEM;
        $this->IAP_ID_AREA_CONH = $IAP_ID_AREA_CONH;
        $this->IAP_ID_SUBAREA_CONH = $IAP_ID_SUBAREA_CONH;
        $this->IAP_VAL_PONTUACAO = $IAP_VAL_PONTUACAO;
        $this->IAP_VAL_PONTUACAO_MAX = $IAP_VAL_PONTUACAO_MAX;
        $this->IAP_DS_OUTROS_PARAM = $IAP_DS_OUTROS_PARAM;
        $this->IAP_ID_SUBGRUPO = $IAP_ID_SUBGRUPO;
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idCategoria
     * @param int $ordemMaiorQue - Busca apenas itens cuja ordem de avaliaçao eh maior ou igual a ordem informada
     * @return null|\ItemAvalProc - Array com itens
     * @throws NegocioException
     */
    public static function buscarItensAvalPorCat($idProcesso, $idCategoria, $ordemMaiorQue = NULL, $inicioDados = NULL, $qtdeDados = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        IAP_ID_ITEM_AVAL,
                        PRC_ID_PROCESSO,
                        CAP_ID_CATEGORIA_AVAL,
                        IAP_TP_ITEM,
                        IAP_ORDEM,
                        IAP_ID_AREA_CONH,
                        IAP_ID_SUBAREA_CONH,
                        IAP_VAL_PONTUACAO,
                        IAP_VAL_PONTUACAO_MAX,
                        IAP_DS_OUTROS_PARAM,
                        IAP_ID_SUBGRUPO
                    from
                        tb_iap_item_aval_proc
                    where
                        PRC_ID_PROCESSO = '$idProcesso'
                        and CAP_ID_CATEGORIA_AVAL = '$idCategoria'";

            if ($ordemMaiorQue != NULL) {
                $sql .= "and IAP_ORDEM > $ordemMaiorQue";
            }

            // adicionando ordenacao
            $sql .=" order by IAP_ID_SUBGRUPO, IAP_ORDEM";

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

                $itemAvalTemp = new ItemAvalProc($dados['IAP_ID_ITEM_AVAL'], $dados['PRC_ID_PROCESSO'], $dados['CAP_ID_CATEGORIA_AVAL'], $dados['IAP_TP_ITEM'], $dados['IAP_ORDEM'], $dados['IAP_ID_AREA_CONH'], $dados['IAP_ID_SUBAREA_CONH'], $dados['IAP_VAL_PONTUACAO'], $dados['IAP_VAL_PONTUACAO_MAX'], $dados['IAP_DS_OUTROS_PARAM'], $dados['IAP_ID_SUBGRUPO']);

                //adicionando no vetor
                $vetRetorno[$i] = $itemAvalTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar itens de avaliação da categoria.", $e);
        }
    }

    /**
     * Essa funcao prepara os dados para uma operaçao de BD. 
     * 
     * Nota: ESTA FUNCAO ALTERA CAMPOS IMPORTANTES DO OBJETO!
     * 
     * @param CategoriaAvalProc $categoriaAval
     * @param char $stFormacao
     * @param char $tpExclusivo
     * @param char $segGraduacao
     * @param int $cargaHorariaMin
     * @param string $dsItemExt
     * @param $edicao boolean
     */
    private function trataDadosBanco($categoriaAval, $stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt, $edicao = FALSE) {
        // tratando de acordo com a categoria
        // tipo automatizado, nao tem nenhum parametro
        if ($categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA) {
            // anulando campos
            $this->IAP_ID_SUBGRUPO = 'NULL';
            $this->IAP_VAL_PONTUACAO = 'NULL';
            $this->IAP_VAL_PONTUACAO_MAX = 'NULL';
            $this->IAP_DS_OUTROS_PARAM = 'NULL';
        } elseif ($categoriaAval->isAvalManual()) {
            // anulando campos
            $this->IAP_ID_SUBGRUPO = 'NULL';
            $this->IAP_VAL_PONTUACAO = 'NULL';

            // tipo externo tem particularidade
            if ($categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_AVAL_EXTERNA) {
                $this->IAP_DS_OUTROS_PARAM = NGUtil::trataCampoStrParaBD($dsItemExt);
            } else {
                $this->IAP_DS_OUTROS_PARAM = 'NULL';
            }
        } elseif ($categoriaAval->isAvalAutomatica()) {
            // tratando nota maxima
            if ($this->IAP_ID_SUBGRUPO == self::$ID_GRUPO_NOVO_GRUPO || $this->IAP_ID_SUBGRUPO == self::$ID_GRUPO_SEM_AGRUPAMENTO) {
                if (Util::vazioNulo($this->IAP_VAL_PONTUACAO_MAX)) {
                    throw new NegocioException("Pontuação máxima não definida!");
                }
            }

            // tratando caso de grupo
            if (Util::vazioNulo($this->IAP_ID_SUBGRUPO)) {
                // caso de ser categoria exclusiva
                if ($categoriaAval->isCategoriaExclusiva()) {
                    // nao tem grupo e pontuacao maxima e igual a pontuacao (compatibilidade)
                    $this->IAP_ID_SUBGRUPO = 'NULL';
                    $this->IAP_VAL_PONTUACAO_MAX = $this->IAP_VAL_PONTUACAO;
                } else {
                    throw new NegocioException("Grupo não definido");
                }
            } elseif ($this->IAP_ID_SUBGRUPO == self::$ID_GRUPO_SEM_AGRUPAMENTO) {
                // nao tem grupo
                $this->IAP_ID_SUBGRUPO = 'NULL';
            } elseif ($this->IAP_ID_SUBGRUPO == self::$ID_GRUPO_NOVO_GRUPO) {
                // definindo novo grupo
                $this->IAP_ID_SUBGRUPO = "(SELECT COALESCE(MAX(`IAP_ID_SUBGRUPO`),0) + 1
                    FROM (
                    SELECT *
                    FROM tb_iap_item_aval_proc) AS temp
                    WHERE `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'
                    and CAP_ID_CATEGORIA_AVAL = '$this->CAP_ID_CATEGORIA_AVAL')";
            } else {
                // caso de edicao: Definir nova pontuacao maxima do grupo, a parte
                if (!$edicao) {
                    // definindo pontuaçao maxima
                    $this->IAP_VAL_PONTUACAO_MAX = "(select IAP_VAL_PONTUACAO_MAX from 
                    (select * from tb_iap_item_aval_proc) As temp
                    WHERE `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'
                    and CAP_ID_CATEGORIA_AVAL = '$this->CAP_ID_CATEGORIA_AVAL'
                    and IAP_ID_SUBGRUPO = '$this->IAP_ID_SUBGRUPO' limit 0, 1)";
                }
            }

            // tratando caso de parametros de titulacao
            if ($categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_TITULACAO) {
                // validando parametros
                if (Util::vazioNulo($stFormacao)) {
                    throw new NegocioException("Parâmetro de formação não definido");
                }

                // montando parametros
                $strParam = self::$PARAM_TIT_STFORMACAO . self::$SEPARADOR_VALOR . $stFormacao;

                // tipo exclusivo
                if (self::admiteParametro($this->IAP_TP_ITEM, self::$PARAM_TIT_EXCLUSIVO)) {
                    $strParam .= self::$SEPARADOR_PARAM . self::$PARAM_TIT_EXCLUSIVO . self::$SEPARADOR_VALOR . self::mapaTrueFalse($tpExclusivo);
                }

                // seg graduacao
                if (self::admiteParametro($this->IAP_TP_ITEM, self::$PARAM_TIT_SEGGRADUACAO)) {
                    $strParam .= self::$SEPARADOR_PARAM . self::$PARAM_TIT_SEGGRADUACAO . self::$SEPARADOR_VALOR . self::mapaTrueFalse($segGraduacao);
                }

                // carga horaria minima
                if (self::admiteParametro($this->IAP_TP_ITEM, self::$PARAM_TIT_CARGA_HORARIA_MIN)) {
                    if (!Util::vazioNulo($cargaHorariaMin)) {
                        $strParam .= self::$SEPARADOR_PARAM . self::$PARAM_TIT_CARGA_HORARIA_MIN . self::$SEPARADOR_VALOR . $cargaHorariaMin;
                    }
                }

                $this->IAP_DS_OUTROS_PARAM = NGUtil::trataCampoStrParaBD($strParam);
            } else {
                // outros parametros e nulo
                $this->IAP_DS_OUTROS_PARAM = 'NULL';
            }
        }

        // tratando area e subarea
        if ($categoriaAval->admiteItensAreaSubareaObj()) {
            // verificando se e subareafixada
            if (array_search($this->IAP_TP_ITEM, self::getArrayTpFixarArea($categoriaAval->getCAP_TP_CATEGORIA())) !== FALSE) {
                $this->IAP_ID_AREA_CONH = self::getAreaFixa($categoriaAval->getCAP_TP_CATEGORIA());
                $this->IAP_ID_SUBAREA_CONH = self::getSubAreaFixa($categoriaAval->getCAP_TP_CATEGORIA());
            } else {
                $this->IAP_ID_AREA_CONH = NGUtil::trataCampoStrParaBD($this->IAP_ID_AREA_CONH);
                // subarea
                if (Util::vazioNulo($this->IAP_ID_SUBAREA_CONH)) {
                    $this->IAP_ID_SUBAREA_CONH = 'NULL';
                }
            }
        } else {
            $this->IAP_ID_AREA_CONH = $this->IAP_ID_SUBAREA_CONH = 'NULL';
        }

        // definindo ordem automatica
        if (Util::vazioNulo($this->IAP_ORDEM)) {
            $this->IAP_ORDEM = "(SELECT COALESCE(MAX(`IAP_ORDEM`),0) + 1
                    FROM (
                    SELECT *
                    FROM tb_iap_item_aval_proc) AS tempOrdem
                    WHERE `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'
                    and CAP_ID_CATEGORIA_AVAL = '$this->CAP_ID_CATEGORIA_AVAL')";
        }

        // preparando campos
        $this->IAP_TP_ITEM = NGUtil::trataCampoStrParaBD($this->IAP_TP_ITEM);
    }

    public function criarItemAval($stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt) {
        try {

            // buscando categoria e etapa para validaçao
            $categoriaAval = CategoriaAvalProc::buscarCatAvalPorId($this->CAP_ID_CATEGORIA_AVAL);
            $etapa = EtapaAvalProc::buscarEtapaAvalPorId($categoriaAval->getEAP_ID_ETAPA_AVAL_PROC());

            // nao pode editar etapa
            if (!$etapa->podeAlterar()) {
                throw new NegocioException("Etapa não pode ser alterada.");
            }

            // validar item
            $validou = self::validarCadastroItemAval($this->PRC_ID_PROCESSO, $this->CAP_ID_CATEGORIA_AVAL, $this->IAP_TP_ITEM, $this->IAP_ID_AREA_CONH, $this->IAP_ID_SUBAREA_CONH, $stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt);
            if (!$validou[0]) {
                if (!Util::vazioNulo($validou[1])) {
                    throw new NegocioException($validou[1]);
                } else {
                    throw new NegocioException("Já existe um item com os parâmetros informados.");
                }
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando campos
            $this->trataDadosBanco($categoriaAval, $stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt);

            //montando sql de criação
            $sql = "insert into tb_iap_item_aval_proc(`PRC_ID_PROCESSO`, CAP_ID_CATEGORIA_AVAL, IAP_TP_ITEM, `IAP_ID_AREA_CONH`, `IAP_ID_SUBAREA_CONH`, `IAP_VAL_PONTUACAO`, `IAP_VAL_PONTUACAO_MAX`, IAP_DS_OUTROS_PARAM, `IAP_ID_SUBGRUPO`, IAP_ORDEM)
            values($this->PRC_ID_PROCESSO, $this->CAP_ID_CATEGORIA_AVAL, $this->IAP_TP_ITEM, $this->IAP_ID_AREA_CONH, $this->IAP_ID_SUBAREA_CONH, $this->IAP_VAL_PONTUACAO, $this->IAP_VAL_PONTUACAO_MAX, $this->IAP_DS_OUTROS_PARAM, $this->IAP_ID_SUBGRUPO, $this->IAP_ORDEM)";

            // definindo array de comandos
            $arrayCmds = array($sql);

            // recuperando sql de reordenacao para quem nao e externo
            if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_EXTERNA) {
                $temp = $this->getSqlReordenacaoAuto();
                $arrayCmds [] = $temp[0];
                $arrayCmds [] = $temp[1];
            }

            // recuperando sql de invalidacao de provaveis avaliaçoes já realizadas
            $arrayCmds = array_merge($arrayCmds, EtapaAvalProc::getArraySqlResetAvalProcEtapa($this->PRC_ID_PROCESSO, $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC(), $categoriaAval->isAvalAutomatica()));

            // persistindo no BD
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar item de avaliação.", $e);
        }
    }

    public static function codificaParamInfComp($idGrupoAnexoProc, $nmGrupo) {
        // removendo caracter especial
        $nmGrupo = str_replace("=", "", $nmGrupo);

        return "$idGrupoAnexoProc=$nmGrupo";
    }

    /**
     * 
     * @param string $dsParam
     * @return array Array na forma [$idGrupoAnexoProc, $nmGrupo]
     * @throws NegocioException
     */
    public static function decodificaParamInfComp($dsParam) {
        $temp = explode("&", $dsParam);
        if (Util::vazioNulo($temp) || count($temp) != 2) {
            throw new NegocioException("Codificação de parâmetros de Inf. Complementar incorreta.");
        }
        return array($temp[0], $temp[1]);
    }

    /**
     * Esta função retorna um array com as sqls responsáveis por remover um item de informação complementar, 
     * tratando os casos da necessidade de atualização e remoção da categoria.
     * 
     * @return array Array com sqls de ajuste
     */
    public function _getSqlRemoverItemInfComp() {
        // verificando se é item complementar
        if ($this->IAP_TP_ITEM != self::$TP_INF_COMP) {
            throw new NegocioException("Chamada incorreta de função para remoção de Item de Inf. Complementar.");
        }

        $ret = array();

        // criando sql de remoção do item
        $ret [] = "delete from tb_iap_item_aval_proc where IAP_ID_ITEM_AVAL = '$this->IAP_ID_ITEM_AVAL'";

        // criando sqls de ajustes
        // contando itens da categoria
        $qtItemCat = ItemAvalProc::contarItemAvalPorCategoria($this->CAP_ID_CATEGORIA_AVAL);
        // recuperando categoria para processamento
        $categoria = CategoriaAvalProc::buscarCatAvalPorId($this->CAP_ID_CATEGORIA_AVAL);

        // é o único item da categoria?
        if ($qtItemCat == 1) {
            // recuperando sql para remover categoria 
            $ret = array_merge($ret, $categoria->getArraySqlRemoverCatPorId());
        } else {
            // criando sql de ajuste para pontuação máxima do grupo
            $novaPontuacaoMax = $categoria->getCAP_VL_PONTUACAO_MAX() - $this->IAP_VAL_PONTUACAO_MAX;

            $ret [] = CategoriaAvalProc::_getSqlAjustaPontMaxCatPorId($this->PRC_ID_PROCESSO, $this->CAP_ID_CATEGORIA_AVAL, $novaPontuacaoMax);
        }

        // Incluindo sql de exclusão de configurações da etapa  e do resultado final
        $ret [] = MacroConfProc::getSqlExcluirConfProc($this->PRC_ID_PROCESSO, $categoria->getEAP_ID_ETAPA_AVAL_PROC());
        $ret [] = MacroConfProc::getSqlExcluirConfProc($this->PRC_ID_PROCESSO);

        return $ret;
    }

    public static function _getSqlCriarItemInfComp($idProcesso, $idCategoriaAval, $idGrupoAnexoProc, $nmGrupo, $notaMax) {
        $tpInfComp = self::$TP_INF_COMP;
        $ordemMax = ItemAvalProc::$ORDEM_MAX;
        $dsParam = self::codificaParamInfComp($idGrupoAnexoProc, $nmGrupo);
        $sql = "insert into tb_iap_item_aval_proc(`PRC_ID_PROCESSO`, CAP_ID_CATEGORIA_AVAL, IAP_TP_ITEM, `IAP_ID_AREA_CONH`, `IAP_ID_SUBAREA_CONH`, `IAP_VAL_PONTUACAO`, `IAP_VAL_PONTUACAO_MAX`, IAP_DS_OUTROS_PARAM, `IAP_ID_SUBGRUPO`, IAP_ORDEM)
            values('$idProcesso', '$idCategoriaAval', '$tpInfComp', NULL, NULL, NULL, $notaMax, '$dsParam', NULL, $ordemMax)";

        return $sql;
    }

    public static function _getSqlAtualizarItemInfComp($idProcesso, $idItemAvalProc, $idGrupoAnexoProc, $nmGrupo, $notaMax) {
        $dsParam = self::codificaParamInfComp($idGrupoAnexoProc, $nmGrupo);
        $sql = "update tb_iap_item_aval_proc set `IAP_VAL_PONTUACAO_MAX` = '$notaMax', IAP_DS_OUTROS_PARAM = '$dsParam'
                where PRC_ID_PROCESSO = '$idProcesso' and IAP_ID_ITEM_AVAL = '$idItemAvalProc'";

        return $sql;
    }

    public static function _getSqlAjusteItemInfCompPos($idProcesso, $nmGrupo) {
        $tpInfComp = self::$TP_INF_COMP;
        $dsParamGrupo = "(select concat(GAP_ID_GRUPO_PROC, concat('=', GAP_NM_GRUPO)) from tb_gap_grupo_anexo_proc where PRC_ID_PROCESSO = '$idProcesso' and GAP_NM_GRUPO = '$nmGrupo')";
        $sql = "update tb_iap_item_aval_proc set `IAP_DS_OUTROS_PARAM` = $dsParamGrupo
                where PRC_ID_PROCESSO = '$idProcesso' and IAP_TP_ITEM = '$tpInfComp' and IAP_DS_OUTROS_PARAM like '%=$nmGrupo'";

        return $sql;
    }

    /**
     * Verifica se uma etapa possui a categoria de informações complementares. Se possuir, 
     * retorna o ID da categoria especial. Caso contrário, é retornado FALSE.
     * 
     * @param int $idProcesso
     * @param int $idCategoriaAval
     * @return boolean|int
     * @throws NegocioException
     */
    public static function grupoAnexoProcPossuiItemAval($idProcesso, $idCategoriaAval, $idGrupoAnexoProc) {
        try {
            $item = ItemAvalProc::buscarItemAvalPorCatTpParam($idProcesso, $idCategoriaAval, self::$TP_INF_COMP, "'$idGrupoAnexoProc=%'");
            if ($item === NULL) {
                return FALSE;
            }
            return $item[0]->getIAP_ID_ITEM_AVAL();
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar se grupo possui item de avaliação.", $e);
        }
    }

    public static function codBuscaParamItemInfComp($idGrupoAnexoProc) {
        return "'$idGrupoAnexoProc=%'";
    }

    public function excluirItemAval() {
        try {

            // buscando categoria e etapa para validaçao
            $categoriaAval = CategoriaAvalProc::buscarCatAvalPorId($this->CAP_ID_CATEGORIA_AVAL);
            $etapa = EtapaAvalProc::buscarEtapaAvalPorId($categoriaAval->getEAP_ID_ETAPA_AVAL_PROC());

            // nao pode editar etapa
            if (!$etapa->podeAlterar()) {
                throw new NegocioException("Etapa não pode ser alterada.");
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // array de comandos
            $arrayCmds = array();


            // recuperando sql de remocao do relatorio de notas
            $arrayCmds [] = RelNotasInsc::getSqlExclusaoPorItemAval($this->IAP_ID_ITEM_AVAL);

            //montando sql de exclusao
            $arrayCmds [] = "delete from tb_iap_item_aval_proc
                    where PRC_ID_PROCESSO = '$this->PRC_ID_PROCESSO'
                    and CAP_ID_CATEGORIA_AVAL = '$this->CAP_ID_CATEGORIA_AVAL'
                    and IAP_ID_ITEM_AVAL = '$this->IAP_ID_ITEM_AVAL'";

            // adicionando sql de remocao de itens da etapa e resultado final
            $arrayCmds [] = MacroConfProc::getSqlExcluirConfProc($this->PRC_ID_PROCESSO, $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC());
            $arrayCmds [] = MacroConfProc::getSqlExcluirConfProc($this->PRC_ID_PROCESSO);

            // montando sql de reordenacao
            // recuperando sql de reordenacao para quem nao e externo
            $temp = $this->getSqlReordenacaoAuto($categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_AVAL_EXTERNA);
            $arrayCmds [] = $temp[0];
            $arrayCmds [] = $temp[1];

            // incluindo reordenacao de grupos
            if ($categoriaAval->isAvalAutomatica() && !$categoriaAval->isCategoriaExclusiva()) {
                $arrayCmds = array_merge($arrayCmds, $this->getSqlReordenacaoGrupo($this->IAP_ID_SUBGRUPO));
            }

            // recuperando sql de invalidacao de provaveis avaliaçoes
            $arrayCmds = array_merge($arrayCmds, EtapaAvalProc::getArraySqlResetAvalProcEtapa($this->PRC_ID_PROCESSO, $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC(), $categoriaAval->isAvalAutomatica()));

//            print_r($arrayCmds);
//            exit;
            // persistindo no BD
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir item de avaliação.", $e);
        }
    }

    public function editarItemAval($stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt, $idGrupo) {
        try {

            // buscando categoria e etapa para validaçao
            $categoriaAval = CategoriaAvalProc::buscarCatAvalPorId($this->CAP_ID_CATEGORIA_AVAL);
            $etapa = EtapaAvalProc::buscarEtapaAvalPorId($categoriaAval->getEAP_ID_ETAPA_AVAL_PROC());

            // nao pode editar etapa
            if (!$etapa->podeAlterar()) {
                throw new NegocioException("Etapa não pode ser alterada.");
            }

            // validar item
            $validou = self::validarCadastroItemAval($this->PRC_ID_PROCESSO, $this->CAP_ID_CATEGORIA_AVAL, $this->IAP_TP_ITEM, $this->IAP_ID_AREA_CONH, $this->IAP_ID_SUBAREA_CONH, $stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt, TRUE, $this->IAP_ID_ITEM_AVAL);
            if (!$validou[0]) {
                if (!Util::vazioNulo($validou[1])) {
                    throw new NegocioException($validou[1]);
                } else {
                    throw new NegocioException("Já existe um item com os parâmetros informados.");
                }
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // array de comandos
            $arrayCmds = array();

            // verificando necessidade de atualizacao de nota do grupo
            // apenas se o grupo editado eh o grupo atual do item
            // categoria manual e categoria exclusiva nao tem grupo
            if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA && $categoriaAval->isAvalAutomatica() && !$categoriaAval->isCategoriaExclusiva()) {
                if ($idGrupo == $this->IAP_ID_SUBGRUPO) {
                    // sql de grupo
                    $arrayCmds [] = $this->getSqlAtualizacaoNotaMaxGrupo();
                } else {
                    // setando grupo antigo para analise de reordenacao de grupo
                    $grupoAntigo = $this->IAP_ID_SUBGRUPO;
                    $novoGrupo = $idGrupo == self::$ID_GRUPO_NOVO_GRUPO;
                }
                $this->IAP_ID_SUBGRUPO = $idGrupo;
            }

            // tratando campos
            $this->trataDadosBanco($categoriaAval, $stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt, TRUE);

            //montando sql de atualizacao
            $arrayCmds [] = "update tb_iap_item_aval_proc
                    set IAP_ORDEM = $this->IAP_ORDEM,
                    IAP_ID_AREA_CONH = $this->IAP_ID_AREA_CONH,
                    IAP_ID_SUBAREA_CONH = $this->IAP_ID_SUBAREA_CONH,
                    IAP_VAL_PONTUACAO = $this->IAP_VAL_PONTUACAO,
                    IAP_VAL_PONTUACAO_MAX = $this->IAP_VAL_PONTUACAO_MAX,
                    IAP_DS_OUTROS_PARAM = $this->IAP_DS_OUTROS_PARAM,
                    IAP_ID_SUBGRUPO = $this->IAP_ID_SUBGRUPO
                    where PRC_ID_PROCESSO = $this->PRC_ID_PROCESSO
                    and CAP_ID_CATEGORIA_AVAL = $this->CAP_ID_CATEGORIA_AVAL
                    and IAP_ID_ITEM_AVAL = '$this->IAP_ID_ITEM_AVAL'";


            // recuperando sql de reordenacao para quem nao e externo
            if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_EXTERNA) {
                $temp = $this->getSqlReordenacaoAuto();
                $arrayCmds [] = $temp[0];
                $arrayCmds [] = $temp[1];
            }

            // incluindo reordenacao de grupos
            if ($categoriaAval->getCAP_TP_CATEGORIA() != CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA && $categoriaAval->isAvalAutomatica() && !$categoriaAval->isCategoriaExclusiva()) {
                $arrayCmds = array_merge($arrayCmds, $this->getSqlReordenacaoGrupo(isset($grupoAntigo) ? $grupoAntigo : NULL, isset($novoGrupo) ? $novoGrupo : FALSE));
            }

            // recuperando sql de invalidacao de provaveis avaliaçoes
            $arrayCmds = array_merge($arrayCmds, EtapaAvalProc::getArraySqlResetAvalProcEtapa($this->PRC_ID_PROCESSO, $categoriaAval->getEAP_ID_ETAPA_AVAL_PROC(), $categoriaAval->isAvalAutomatica()));
//
//            print_r($arrayCmds);
//            exit;
//            
            // persistindo no BD
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar item de avaliação.", $e);
        }
    }

    private function getSqlReordenacaoAuto($catExterna = FALSE) {
        $sql1 = "SET @counter = 0";

        $sql2 = "UPDATE tb_iap_item_aval_proc
                    SET IAP_ORDEM = @counter := @counter + 1
                    where PRC_ID_PROCESSO = $this->PRC_ID_PROCESSO
                    and CAP_ID_CATEGORIA_AVAL = $this->CAP_ID_CATEGORIA_AVAL";

        //definindo ordem
        $sql2 .=!$catExterna ? " ORDER BY IAP_ID_SUBGRUPO, IAP_VAL_PONTUACAO desc, IAP_VAL_PONTUACAO_MAX desc" : " order by IAP_ID_ITEM_AVAL";

        return array($sql1, $sql2);
    }

    private function getSqlAtualizacaoNotaMaxGrupo() {
        $sql = "UPDATE tb_iap_item_aval_proc
                    SET IAP_VAL_PONTUACAO_MAX = $this->IAP_VAL_PONTUACAO_MAX
                    where PRC_ID_PROCESSO = $this->PRC_ID_PROCESSO
                    and  CAP_ID_CATEGORIA_AVAL = $this->CAP_ID_CATEGORIA_AVAL
                    and IAP_ID_SUBGRUPO = $this->IAP_ID_SUBGRUPO";

        return $sql;
    }

    public static function getSqlExclusaoItemPorCategoriaAval($idCategoria) {
        $sql = "delete from tb_iap_item_aval_proc
                    where CAP_ID_CATEGORIA_AVAL = '$idCategoria'";

        return $sql;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_iap_item_aval_proc where PRC_ID_PROCESSO = '$idProcesso'";
    }

    private function getSqlReordenacaoGrupo($grupoAntigo = NULL, $novoGrupo = FALSE) {
        // recuperando grupos
        $grupos = self::buscarGruposPorCat($this->CAP_ID_CATEGORIA_AVAL, FALSE);

        // verificando caso de remoçao de grupo da lista
        if ($grupoAntigo != NULL) {
            // contando elementos do grupo
            $qtdeGrupo = self::contarItemAvalPorCategoria($this->CAP_ID_CATEGORIA_AVAL, $grupoAntigo);

            // se so tiver 1 item no grupo, deve-se remover o grupo da lista, pois o 
            // item em questao esta sendo atualizado
            if ($qtdeGrupo == 1) {
                unset($grupos[$grupoAntigo]);
            }
        }

        // verificando caso de criacao de novo grupo
        if ($novoGrupo) {
            // deve-se incluir o novo grupo na lista para analise
            $grupos[$this->previsaoProxCodGrupo()] = "";
        }

        // gerando sqls
        $i = 1;
        $ret = array();
        foreach (array_keys($grupos) as $idGrupo) {
            // gerando as sqls
            $ret [] = "UPDATE tb_iap_item_aval_proc
                    SET IAP_ID_SUBGRUPO = '$i'
                    where PRC_ID_PROCESSO = $this->PRC_ID_PROCESSO
                    and  CAP_ID_CATEGORIA_AVAL = $this->CAP_ID_CATEGORIA_AVAL
                    and IAP_ID_SUBGRUPO = $idGrupo";

            $i++;
        }

        return $ret;
    }

    private function previsaoProxCodGrupo() {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql de pesquisa
            $sql = "SELECT COALESCE(MAX(`IAP_ID_SUBGRUPO`),0) + 1 as idGrupo
                    FROM tb_iap_item_aval_proc
                    WHERE `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'
                    and CAP_ID_CATEGORIA_AVAL = '$this->CAP_ID_CATEGORIA_AVAL'";

            // executando comando no BD
            $resp = $conexao->execSqlComRetorno($sql);

            return ConexaoMysql::getResult("idGrupo", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar código do próximo grupo do item de avaliação.", $e);
        }
    }

    public static function contarItemAvalPorCategoria($idCategoriaAval, $idGrupo = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_iap_item_aval_proc
                    where CAP_ID_CATEGORIA_AVAL = '$idCategoriaAval'";

            // adicionando grupo
            if ($idGrupo != NULL) {
                $sql .= " and IAP_ID_SUBGRUPO = $idGrupo";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar itens de avaliação da categoria.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idCategoriaAval
     * @param char $tpItemAval
     * @param string $dsParam String para ser usada em uma comparação com 'like'. Não esqueça de incluir os aspas na string.
     * @return int
     * @throws NegocioException
     */
    public static function contarItemAvalPorCatTpParam($idProcesso, $idCategoriaAval, $tpItemAval = NULL, $dsParam = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from tb_iap_item_aval_proc
                    where PRC_ID_PROCESSO = '$idProcesso'
                    and CAP_ID_CATEGORIA_AVAL = '$idCategoriaAval'";

            // adicionando opcionais
            if ($tpItemAval != NULL) {
                $sql .= " and IAP_TP_ITEM = '$tpItemAval'";
            }

            if ($tpItemAval != NULL) {
                $sql .= " and IAP_DS_OUTROS_PARAM like $dsParam";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar item de avaliação.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idCategoriaAval
     * @param char $tpItemAval
     * @param string $dsParam String para ser usada em uma comparação com 'like'. Não esqueça de incluir os aspas na string.
     * @return int
     * @throws NegocioException
     */
    public static function buscarItemAvalPorCatTpParam($idProcesso, $idCategoriaAval = NULL, $tpItemAval = NULL, $dsParam = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        IAP_ID_ITEM_AVAL,
                        PRC_ID_PROCESSO,
                        CAP_ID_CATEGORIA_AVAL,
                        IAP_TP_ITEM,
                        IAP_ORDEM,
                        IAP_ID_AREA_CONH,
                        IAP_ID_SUBAREA_CONH,
                        IAP_VAL_PONTUACAO,
                        IAP_VAL_PONTUACAO_MAX,
                        IAP_DS_OUTROS_PARAM,
                        IAP_ID_SUBGRUPO
                    from tb_iap_item_aval_proc
                    where PRC_ID_PROCESSO = '$idProcesso'";


            // adicionando opcionais
            if ($idCategoriaAval != NULL) {
                $sql.= " and CAP_ID_CATEGORIA_AVAL = '$idCategoriaAval'";
            }

            if ($tpItemAval != NULL) {
                $sql .= " and IAP_TP_ITEM = '$tpItemAval'";
            }

            if ($tpItemAval != NULL) {
                $sql .= " and IAP_DS_OUTROS_PARAM like $dsParam";
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

                $itemAvalTemp = new ItemAvalProc($dados['IAP_ID_ITEM_AVAL'], $dados['PRC_ID_PROCESSO'], $dados['CAP_ID_CATEGORIA_AVAL'], $dados['IAP_TP_ITEM'], $dados['IAP_ORDEM'], $dados['IAP_ID_AREA_CONH'], $dados['IAP_ID_SUBAREA_CONH'], $dados['IAP_VAL_PONTUACAO'], $dados['IAP_VAL_PONTUACAO_MAX'], $dados['IAP_DS_OUTROS_PARAM'], $dados['IAP_ID_SUBGRUPO']);

                //adicionando no vetor
                $vetRetorno[$i] = $itemAvalTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar itens de avaliação.", $e);
        }
    }

    private static function getMnemonicoFormacao($stFormacao) {
        if ($stFormacao == FormacaoAcademica::$ST_FORMACAO_ANDAMENTO) {
            $temp = "And.";
        } elseif ($stFormacao == FormacaoAcademica::$ST_FORMACAO_COMPLETO) {
            $temp = "Com.";
        } elseif ($stFormacao == FormacaoAcademica::$ST_FORMACAO_INCOMPLETO) {
            $temp = "Inc.";
        } else {
            $temp = "";
        }
        return "$temp";
    }

    private static function getMnemonicoExclusivo($tpExclusivo) {
        if ($tpExclusivo == self::$C_TRUE) {
            $temp = "Exc.";
        } elseif ($tpExclusivo == self::$C_FALSE) {
            $temp = "Não Exc.";
        } else {
            $temp = "";
        }
        return "$temp";
    }

    private static function getMnemonicoSegGraduacao($segGraduacao) {
        if ($segGraduacao == self::$C_TRUE) {
            $temp = "Seg. Grad.";
        } elseif ($segGraduacao == self::$C_FALSE) {
            $temp = "Pri. Grad.";
        } else {
            $temp = "";
        }
        return "$temp";
    }

    private static function getMnemonicoCargaHoraria($cargaHoraria) {
        if (!Util::vazioNulo($cargaHoraria)) {
            $temp = "{$cargaHoraria}hs";
        } else {
            $temp = "";
        }
        return "$temp";
    }

    public function getHtmlAmigavelParametros($tpCategoria) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO) {
            // definindo parametros
            $ret = "";

            // situacao
            $stFormacao = $this->getValorParam($tpCategoria, self::$PARAM_TIT_STFORMACAO);
            $ret = self::getMnemonicoFormacao($stFormacao);

            // exclusivo
            if (self::admiteParametro($this->IAP_TP_ITEM, self::$PARAM_TIT_EXCLUSIVO)) {
                $tpExclusivo = $this->getValorParam($tpCategoria, self::$PARAM_TIT_EXCLUSIVO);
                $ret .= ", " . self::getMnemonicoExclusivo($tpExclusivo);
            }

            // graduacao
            if (self::admiteParametro($this->IAP_TP_ITEM, self::$PARAM_TIT_SEGGRADUACAO)) {
                $segGraduacao = $this->getValorParam($tpCategoria, self::$PARAM_TIT_SEGGRADUACAO);
                $ret .= ", " . self::getMnemonicoSegGraduacao($segGraduacao);
            }

            // carga horaria
            if (self::admiteParametro($this->IAP_TP_ITEM, self::$PARAM_TIT_CARGA_HORARIA_MIN)) {
                $cargaHoraria = $this->getValorParam($tpCategoria, self::$PARAM_TIT_CARGA_HORARIA_MIN);
                $ret .= ", " . self::getMnemonicoCargaHoraria($cargaHoraria);
            }

            return $ret;
        } else {
            return "";
        }
    }

    public static function buscarItensAvalPorTipoCatEtapa($idProcesso, $tpAvalCat, $nrEtapa, $comAutomatizada) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        IAP_ID_ITEM_AVAL,
                        iap.PRC_ID_PROCESSO,
                        iap.CAP_ID_CATEGORIA_AVAL,
                        IAP_TP_ITEM,
                        IAP_ORDEM,
                        IAP_ID_AREA_CONH,
                        IAP_ID_SUBAREA_CONH,
                        IAP_VAL_PONTUACAO,
                        IAP_VAL_PONTUACAO_MAX,
                        IAP_DS_OUTROS_PARAM,
                        IAP_ID_SUBGRUPO
                    from
                        tb_iap_item_aval_proc iap
                            join
                        tb_cap_categoria_aval_proc cap ON iap.CAP_ID_CATEGORIA_AVAL = cap.CAP_ID_CATEGORIA_AVAL
                            join
                        tb_eap_etapa_aval_proc eap on cap.EAP_ID_ETAPA_AVAL_PROC = eap.EAP_ID_ETAPA_AVAL_PROC
                    where
                        iap.PRC_ID_PROCESSO = '$idProcesso'
                            and EAP_NR_ETAPA_AVAL = '$nrEtapa'
                            and CAP_TP_AVALIACAO = '$tpAvalCat'";


            // removendo categorias automatizadas
            if (!$comAutomatizada) {
                $sql .= " and CAP_TP_CATEGORIA != '" . CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA . "'";
            }

            // ordenação
            $sql .= " order by IAP_ORDEM";

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

                $itemAvalTemp = new ItemAvalProc($dados['IAP_ID_ITEM_AVAL'], $dados['PRC_ID_PROCESSO'], $dados['CAP_ID_CATEGORIA_AVAL'], $dados['IAP_TP_ITEM'], $dados['IAP_ORDEM'], $dados['IAP_ID_AREA_CONH'], $dados['IAP_ID_SUBAREA_CONH'], $dados['IAP_VAL_PONTUACAO'], $dados['IAP_VAL_PONTUACAO_MAX'], $dados['IAP_DS_OUTROS_PARAM'], $dados['IAP_ID_SUBGRUPO']);

                //adicionando no vetor
                $vetRetorno[$i] = $itemAvalTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar itens de avaliação de categoria manual de uma etapa.", $e);
        }
    }

    /**
     * 
     * @param int $idItem
     * @return \ItemAvalProc
     * @throws NegocioException
     */
    public static function buscarItemAvalPorId($idItem) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        IAP_ID_ITEM_AVAL,
                        PRC_ID_PROCESSO,
                        CAP_ID_CATEGORIA_AVAL,
                        IAP_TP_ITEM,
                        IAP_ORDEM,
                        IAP_ID_AREA_CONH,
                        IAP_ID_SUBAREA_CONH,
                        IAP_VAL_PONTUACAO,
                        IAP_VAL_PONTUACAO_MAX,
                        IAP_DS_OUTROS_PARAM,
                        IAP_ID_SUBGRUPO
                    from
                        tb_iap_item_aval_proc
                    where
                        IAP_ID_ITEM_AVAL = '$idItem'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Item de avaliação não encontrado.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $itemAvalTemp = new ItemAvalProc($dados['IAP_ID_ITEM_AVAL'], $dados['PRC_ID_PROCESSO'], $dados['CAP_ID_CATEGORIA_AVAL'], $dados['IAP_TP_ITEM'], $dados['IAP_ORDEM'], $dados['IAP_ID_AREA_CONH'], $dados['IAP_ID_SUBAREA_CONH'], $dados['IAP_VAL_PONTUACAO'], $dados['IAP_VAL_PONTUACAO_MAX'], $dados['IAP_DS_OUTROS_PARAM'], $dados['IAP_ID_SUBGRUPO']);

            return $itemAvalTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar item de avaliação.", $e);
        }
    }

    /**
     * Verifica se o candidato possui no curriculo o item pedido pela regra. 
     * Retorna uma lista de id's que casam com o item, ou Nulo se nao existir itens
     * que casam.
     * 
     * FUNCAO DE PROCESSAMENTO INTERNO. USE SE TIVER CERTEZA DO QUE ESTA FAZENDO!
     * 
     * @param int $tpCategoria
     * @param int $idCandidato
     * @return array - Array com Id's dos itens que casam com a regra.
     * No caso de titulacao, o array e da forma: [id1, id2, ...]
     * Para Publicaçao, PartEvento e Atuacao o array e da forma:
     * [id1 => notaReal, id2 => notaReal, "soma" => notaNormalizadaDoItem, "grupo" => array(idGrupo, pontuacao, limite)]
     * @throws NegocioException
     */
    public function CLAS_candidato_casa_item($tpCategoria, $idCandidato) {
        try {

            // recuperando conexao
            $conexao = NGUtil::getConexao();

            // montando sql de pesquisa
            $sql = $this->CLAS_monta_sql_validacao($tpCategoria, $idCandidato);

            // executando busca no BD
            $resp = $conexao->execSqlComRetorno($sql);

            // verificando quantidade de linhas
            $numLinhas = ConexaoMysql::getNumLinhas($resp);

            // criando array de retorno
            $ret = array();

            // retornando de acordo com os casos
            if ($tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO) {

                // caso de nao casar
                if ($numLinhas == 0 || ($this->IAP_TP_ITEM == TipoCurso::getTpGraduacao() && $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_SEGGRADUACAO] == self::$C_TRUE && $numLinhas == 1)) {
                    return FALSE;
                }

                // casou. Calculando nota
                $this->notaNormalizada = $this->notaReal = $this->IAP_VAL_PONTUACAO;

                // gerando retorno
                for ($i = 0; $i < $numLinhas; $i++) {
                    $ret [] = ConexaoMysql::getResult("FRA_ID_FORMACAO", $resp);
                }
            } elseif ($tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO || $tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO || $tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO) {
                // caso de nao casar
                if ($numLinhas == 0) {
                    return FALSE;
                }

                // casou. Calculando nota e gerando retorno
                $this->notaReal = 0;
                for ($i = 0; $i < $numLinhas; $i++) {
                    $dados = ConexaoMysql::getLinha($resp);
                    $this->notaReal += $this->IAP_VAL_PONTUACAO * floatval($dados['qtItem']);
                    $ret [$dados["idItem"]] = $this->notaReal;
                }
                $this->notaNormalizada = min(array($this->notaReal, $this->IAP_VAL_PONTUACAO_MAX));
                $ret["soma"] = $this->notaNormalizada;

                // caso de grupo
                if ($this->IAP_ID_SUBGRUPO != NULL) {
                    $ret['grupo'] = array($this->IAP_ID_SUBGRUPO, $this->notaNormalizada, $this->IAP_VAL_PONTUACAO_MAX);
                }
            } else {
                throw new NegocioException("Casamento não programado para a categoria.");
            }

            // retornando vetor com casamento
            return $ret;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar casamento de regra do item com perfil / currículo do candidato.", $e);
        }
    }

    /**
     * 
     * @param ItemAvalProc $listaItens - Array com lista de itens
     * @param CategoriaAvalProc $categoria
     * @param int $idCandidato
     * @return ItemAvalProc - Array com itens que casam
     * @throws NegocioException
     */
    public static function verifica_casamento_itens($listaItens, $categoria, $idCandidato) {
        try {
            // caso de nao ter itens
            if ($listaItens == NULL) {
                return array();
            }

            // recuperando conexao
            $conexao = NGUtil::getConexao();

            // array de retorno
            $ret = array();

            // para cada item, verificando
            foreach ($listaItens as $item) {
                // montando sql de pesquisa
                $sql = $item->CLAS_monta_sql_validacao($categoria->getCAP_TP_CATEGORIA(), $idCandidato);

                // executando busca no BD
                $resp = $conexao->execSqlComRetorno($sql);

                // verificando quantidade de linhas
                $numLinhas = ConexaoMysql::getNumLinhas($resp);

                // retornando de acordo com os casos
                if ($categoria->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_TITULACAO) {

                    // caso de nao casar
                    if ($numLinhas == 0 || ($item->IAP_TP_ITEM == TipoCurso::getTpGraduacao() && $item->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_SEGGRADUACAO] == self::$C_TRUE && $numLinhas == 1)) {
                        continue;
                    }

                    // casou. Calculando nota
                    $item->notaReal = $item->IAP_VAL_PONTUACAO;
                    $item->notaNormalizada = min(array($item->notaReal, $categoria->getCAP_VL_PONTUACAO_MAX()));

                    //colocando no vetor de retorno
                    $ret [] = $item;
                } elseif ($categoria->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_PUBLICACAO || $categoria->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_PART_EVENTO || $categoria->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_ATUACAO) {
                    //@todo Implementar verificaçao para outros casos, se necessario
                    throw new NegocioException("Função de verificação de casamento não programada para esse caso.");
                } else {
                    throw new NegocioException("Casamento não programado para a categoria.");
                }
            }


            // retornando apenas casados
            return $ret;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar casamento de regras dos itens com perfil / currículo do candidato.", $e);
        }
    }

    /**
     * Funcao que torna uma string com a sql de insercao / update no relatorio de notas
     * 
     * @param int $tpCategoria
     * @param int $idInscricao
     * @param int $vetIdCasou - Array com Id's retornados pela funcao de casamento de um item
     * @param float $notaMaxCategoria - Nota maxima permitida pela categoria
     * @param boolean $catExclusiva - Diz se uma categoria e exclusiva
     * @return string
     * @throws NegocioException
     */
    public function CLAS_get_sql_rel_notas($tpCategoria, $idInscricao, $vetIdCasou, $notaMaxCategoria, $catExclusiva) {
        try {
            // definindo parametros e criando relatorio
            $objAval = $this->CLAS_get_str_obj_aval($tpCategoria, $vetIdCasou);

            $relatorio = new RelNotasInsc(NULL, $idInscricao, $this->CAP_ID_CATEGORIA_AVAL, $this->IAP_ID_ITEM_AVAL, RelNotasInsc::$ORDEM_MAXIMA, RelNotasInsc::$TP_AVAL_AUTOMATICA, $objAval, $this->notaReal, ($notaMaxCategoria == NULL ? $this->notaNormalizada : min(array($notaMaxCategoria, $this->notaNormalizada))), RelNotasInsc::$SIT_ATIVA, getIdUsuarioLogado(), NULL);

//            print_r($relatorio);
//            print_r("<br/>");
            // recuperando string
            return $relatorio->CLAS_getSqlRelNotasCand($idInscricao, $catExclusiva);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar sql para o relatório de notas do candidato.", $e);
        }
    }

    /**
     * Funçao que retorna o sql a ser executado no banco para validaçao do 
     * item 'fantasma' de uma categoria exclusiva
     * 
     * @param InscricaoProcesso $inscricao
     * @param CategoriaAvalProc $categoria
     * @param string $obsRel
     * @param int $stRelatorio
     * @return string
     */
    public function get_sql_rel_item_fan_exc($inscricao, $categoria, $obsRel, $stRelatorio) {
        $this->notaReal = $this->IAP_VAL_PONTUACAO;
        $this->notaNormalizada = min(array($this->IAP_VAL_PONTUACAO, $this->IAP_VAL_PONTUACAO_MAX, $categoria->getCAP_VL_PONTUACAO_MAX()));
        $temp = new RelNotasInsc(NULL, $inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), $this->IAP_ID_ITEM_AVAL, RelNotasInsc::$ORDEM_MAXIMA, RelNotasInsc::$TP_AVAL_MANUAL, $obsRel, $this->notaReal, $this->notaNormalizada, $stRelatorio, getIdUsuarioLogado(), NULL);
        return $temp->CLAS_getSqlRelNotasCand($inscricao->getIPR_ID_INSCRICAO(), FALSE);
    }

    /**
     * Funcao que retorna o sql a ser executado no banco para registrar a nota de um 
     * item de avaliacao manual.
     * 
     * @param InscricaoProcesso $inscricao
     * @param CategoriaAvalProc $categoria
     * @param float $nota
     * @return string
     */
    public function get_sql_rel_item_man($inscricao, $categoria, $nota) {
        $this->notaReal = $nota;
        $this->notaNormalizada = $this->normalizarNota($nota);
        $temp = new RelNotasInsc(NULL, $inscricao->getIPR_ID_INSCRICAO(), $categoria->getCAP_ID_CATEGORIA_AVAL(), $this->IAP_ID_ITEM_AVAL, RelNotasInsc::$ORDEM_MAXIMA, RelNotasInsc::$TP_AVAL_MANUAL, "Avaliação inserida manualmente.", $this->notaReal, $this->notaNormalizada, RelNotasInsc::$SIT_ATIVA, getIdUsuarioLogado(), NULL);
        return $temp->CLAS_getSqlRelNotasCand($inscricao->getIPR_ID_INSCRICAO(), FALSE);
    }

    /**
     * Verifica se um item de informação complementar necessita de registro da nota inicial (caso de ter sido avaliado direto 
     * pelo admin, sem passar por avaliação cega)
     * 
     * @param InscricaoProcesso $inscricao
     * @param floar $nota Nota a ser registrada
     * @param array $vetSql Endereço do array onde deve ser adicionado a sql, se necessário
     */
    public function add_sql_registra_nota_inicial_inf_comp($inscricao, $nota, &$vetSql) {
        if ($this->IAP_TP_ITEM == self::$TP_INF_COMP) {
            $objResp = RespAnexoProc::buscarRespPorInscricaoGrupo($inscricao->getIPR_ID_INSCRICAO(), $this->getIdGrupoInfComp());
            $objResp->add_sql_registra_nota_inicial($nota, $vetSql);
        }
    }

    public function normalizarNota($nota) {
        return min(array($nota, $this->IAP_VAL_PONTUACAO_MAX));
    }

    public function CLAS_get_sql_ajuste_cat($idInscricao, $dsCategoria, $diferenca, $msg = NULL) {
        try {
            // descricao do ajuste
            $dsObjAval = $msg ? $msg : "Ajustando para o limite máximo da categoria $dsCategoria.";
            $relatorio = new RelNotasInsc(NULL, $idInscricao, $this->CAP_ID_CATEGORIA_AVAL, NULL, RelNotasInsc::$ORDEM_MAXIMA, RelNotasInsc::$TP_AVAL_AUTOMATICA, $dsObjAval, -$diferenca, -$diferenca, RelNotasInsc::$SIT_ATIVA, getIdUsuarioLogado(), NULL);

            // recuperando string
            return $relatorio->CLAS_getSqlRelNotasCand($idInscricao, TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar sql de ajuste de categoria para o relatório de notas do candidato.", $e);
        }
    }

    public static function get_sql_ajuste_cat_st($idInscricao, $idCategoria, $dsCategoria, $diferenca, $msg = NULL) {
        try {
            // descricao do ajuste
            $dsObjAval = $msg ? $msg : "Ajustando para o limite máximo da categoria $dsCategoria.";
            $relatorio = new RelNotasInsc(NULL, $idInscricao, $idCategoria, NULL, RelNotasInsc::$ORDEM_MAXIMA, RelNotasInsc::$TP_AVAL_AUTOMATICA, $dsObjAval, -$diferenca, -$diferenca, RelNotasInsc::$SIT_ATIVA, getIdUsuarioLogado(), NULL);

            // recuperando string
            return $relatorio->CLAS_getSqlRelNotasCand($idInscricao, TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao recuperar sql de ajuste de categoria para o relatório de notas do candidato.", $e);
        }
    }

    private function CLAS_get_str_obj_aval($tpCategoria, $vetIdCasou) {
        $ret = "";
        foreach ($vetIdCasou as $idCasou) {
            // inserindo codificacao padrao
            $ret .= "<b>COD:</b> $idCasou" . NGUtil::$PULO_LINHA_HTML;

            // recuperando string
            if ($tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO) {
                $ret .= FormacaoAcademica::CLAS_getStrFormacao($idCasou);
            } elseif ($tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO) {
                $ret .= Publicacao::CLAS_getStrPublicacao($idCasou);
            } elseif ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
                $ret .= ParticipacaoEvento::CLAS_getStrPartEvento($idCasou);
            } elseif ($tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO) {
                $ret .= Atuacao::CLAS_getStrAtuacao($idCasou);
            } else {
                throw new NegocioException("Recuperação de string de descrição do objeto de avaliação não programado para a categoria.");
            }
        }

        return $ret;
    }

    private function CLAS_monta_sql_validacao($tpCategoria, $idCandidato) {
        $esqueleto = $this->CLAS_get_esqueleto_sql($tpCategoria);

        // complementando sql e retornando
        return str_replace(ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE, $idCandidato, $esqueleto);
    }

    private function CLAS_get_esqueleto_sql($tpCategoria) {
        if (Util::vazioNulo($this->CLAS_STR_ESQUELETO_SQL)) {
            // carregando parametros
            $this->CLAS_carrega_param_item($tpCategoria);

            // carregando esqueleto de acordo com o tipo
            if ($tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO) {
                $this->CLAS_STR_ESQUELETO_SQL = $this->CLAS_carrega_esq_titulacao();
            } elseif ($tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO) {
                $this->CLAS_STR_ESQUELETO_SQL = $this->CLAS_carrega_esq_publicacao();
            } elseif ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
                $this->CLAS_STR_ESQUELETO_SQL = $this->CLAS_carrega_esq_part_evento();
            } elseif ($tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO) {
                $this->CLAS_STR_ESQUELETO_SQL = $this->CLAS_carrega_esq_atuacao();
            } else {
                throw new NegocioException("Código de obtenção de esqueleto não está programado para a categoria.");
            }
        }
        return $this->CLAS_STR_ESQUELETO_SQL;
    }

    /**
     * ASSUME QUE CLAS_ARRAY_PARAM_PROC JA ESTA PREENCHIDO.
     * @return string
     * @throws NegocioException
     */
    private function CLAS_carrega_esq_titulacao() {
        $sql = "select 
                    FRA_ID_FORMACAO
                from
                    tb_fra_formacao_academica
                where CDT_ID_CANDIDATO = " . ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE;

        // processando tipo observando parametros e mantendo escalabilidade
        if ($this->IAP_TP_ITEM == TipoCurso::getTpDoutorado() && $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_EXCLUSIVO] == self::$C_FALSE) {
            $sql .= " and (TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'
                    || TPC_ID_TIPO_CURSO = '" . TipoCurso::getTpPosDoutorado() . "') ";
        } elseif ($this->IAP_TP_ITEM == TipoCurso::getTpDoutorado() && $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_EXCLUSIVO] == self::$C_TRUE) {
            $sql .= " and TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'";
        } elseif ($this->IAP_TP_ITEM == TipoCurso::getTpMestrado() && $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_EXCLUSIVO] == self::$C_FALSE) {
            $sql .= " and (TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'
                    || TPC_ID_TIPO_CURSO = '" . TipoCurso::getTpMestradoProf() . "') ";
        } elseif ($this->IAP_TP_ITEM == TipoCurso::getTpMestrado() && $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_EXCLUSIVO] == self::$C_TRUE) {
            $sql .= " and TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'";
        } elseif ($this->IAP_TP_ITEM == TipoCurso::getTpEspecializacao() && $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_EXCLUSIVO] == self::$C_FALSE) {
            $sql .= " and (TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'
                    || TPC_ID_TIPO_CURSO = '" . TipoCurso::getTpEspecializacaoRes() . "') ";
        } elseif ($this->IAP_TP_ITEM == TipoCurso::getTpEspecializacao() && $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_EXCLUSIVO] == self::$C_TRUE) {
            $sql .= " and TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'";
        } elseif ($this->IAP_TP_ITEM == TipoCurso::getTpGraduacao()) {
            $sql .= " and TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'";
        } elseif ($this->IAP_TP_ITEM == TipoCurso::getTpAperfeicoamento()) {
            $sql .= " and TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'";
        } elseif ($this->IAP_TP_ITEM == TipoCurso::getTpCapacitacao()) {
            $sql .= " and TPC_ID_TIPO_CURSO = '" . $this->IAP_TP_ITEM . "'";
        } else {
            throw new NegocioException("Código não programado para o tipo de item especificado.");
        }

        // processando parametro de conclusao
        $sql .= " and FRA_STATUS_CURSO = '" . $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_STFORMACAO] . "'";

        // processando area / subarea
        if (!Util::vazioNulo($this->IAP_ID_AREA_CONH)) {
            $sql .= " and FRA_ID_AREA_CONH = '" . $this->IAP_ID_AREA_CONH . "'";
        }
        if (!Util::vazioNulo($this->IAP_ID_SUBAREA_CONH)) {
            $sql .= " and FRA_ID_SUBAREA_CONH = '" . $this->IAP_ID_SUBAREA_CONH . "'";
        }

        // processando carga horaria para especializacao e outros
        if (self::admiteParametro($this->IAP_TP_ITEM, self::$PARAM_TIT_CARGA_HORARIA_MIN)) {
            if (!Util::vazioNulo($this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_CARGA_HORARIA_MIN])) {
                $sql .= " and FRA_CARGA_HORARIA >= " . $this->CLAS_ARRAY_PARAM_PROC[self::$PARAM_TIT_CARGA_HORARIA_MIN];
            }
        }

        return $sql;
    }

    public function getIdCheckBoxGerencia() {
        return self::$CONS_ID_ITEM . $this->IAP_ID_ITEM_AVAL;
    }

    public function getIdLinhaGerencia() {
        return self::$CONS_ID_LINHA . $this->IAP_ID_ITEM_AVAL;
    }

    /**
     * ASSUME QUE CLAS_ARRAY_PARAM_PROC JA ESTA PREENCHIDO.
     * @return string
     * @throws NegocioException
     */
    private function CLAS_carrega_esq_publicacao() {
        $sql = "select 
                    PUB_ID_PUBLICACAO as idItem, PUB_QT_ITEM as qtItem
                from
                    tb_pub_publicacao
                where CDT_ID_CANDIDATO = " . ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE;

        // processando tipo
        $sql .= " and PUB_TP_ITEM = '{$this->IAP_TP_ITEM}' ";

        // processando area / subarea
        if (!Util::vazioNulo($this->IAP_ID_AREA_CONH)) {
            $sql .= " and PUB_ID_AREA_CONH = '" . $this->IAP_ID_AREA_CONH . "'";
        }
        if (!Util::vazioNulo($this->IAP_ID_SUBAREA_CONH)) {
            $sql .= " and PUB_ID_SUBAREA_CONH = '" . $this->IAP_ID_SUBAREA_CONH . "'";
        }

        return $sql;
    }

    /**
     * ASSUME QUE CLAS_ARRAY_PARAM_PROC JA ESTA PREENCHIDO.
     * @return string
     * @throws NegocioException
     */
    private function CLAS_carrega_esq_part_evento() {
        $sql = "select 
                    PEV_ID_PARTICIPACAO as idItem, PEV_QT_ITEM as qtItem
                from
                    tb_pev_participacao_evento
                where CDT_ID_CANDIDATO = " . ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE;

        // processando tipo
        $sql .= " and PEV_TP_ITEM = '{$this->IAP_TP_ITEM}' ";

        // processando area / subarea
        if (!Util::vazioNulo($this->IAP_ID_AREA_CONH)) {
            $sql .= " and PEV_ID_AREA_CONH = '" . $this->IAP_ID_AREA_CONH . "'";
        }
        if (!Util::vazioNulo($this->IAP_ID_SUBAREA_CONH)) {
            $sql .= " and PEV_ID_SUBAREA_CONH = '" . $this->IAP_ID_SUBAREA_CONH . "'";
        }

        return $sql;
    }

    /**
     * ASSUME QUE CLAS_ARRAY_PARAM_PROC JA ESTA PREENCHIDO.
     * @return string
     * @throws NegocioException
     */
    private function CLAS_carrega_esq_atuacao() {
        $sql = "select 
                    ATU_ID_ATUACAO as idItem, ATU_QT_ITEM as qtItem
                from
                    tb_atu_atuacao
                where CDT_ID_CANDIDATO = " . ConexaoMysql::$_CARACTER_INSERCAO_DEPENDENTE;

        // processando tipo
        $sql .= " and ATU_TP_ITEM = '{$this->IAP_TP_ITEM}' ";

        // processando area / subarea
        if (!Util::vazioNulo($this->IAP_ID_AREA_CONH)) {
            $sql .= " and ATU_ID_AREA_CONH = '" . $this->IAP_ID_AREA_CONH . "'";
        }
        if (!Util::vazioNulo($this->IAP_ID_SUBAREA_CONH)) {
            $sql .= " and ATU_ID_SUBAREA_CONH = '" . $this->IAP_ID_SUBAREA_CONH . "'";
        }

        return $sql;
    }

    private function CLAS_carrega_param_item($tpCategoria) {
        // titulacao
        if ($tpCategoria == CategoriaAvalProc::$TIPO_TITULACAO) {
            $this->CLAS_ARRAY_PARAM_PROC = array();
            $this->CLAS_ARRAY_PARAM_PROC [self::$PARAM_TIT_STFORMACAO] = self::valorPadraoParam(self::$PARAM_TIT_STFORMACAO);
            $this->CLAS_ARRAY_PARAM_PROC [self::$PARAM_TIT_EXCLUSIVO] = self::valorPadraoParam(self::$PARAM_TIT_EXCLUSIVO);
            $this->CLAS_ARRAY_PARAM_PROC [self::$PARAM_TIT_SEGGRADUACAO] = self::valorPadraoParam(self::$PARAM_TIT_SEGGRADUACAO);
            $this->CLAS_ARRAY_PARAM_PROC [self::$PARAM_TIT_CARGA_HORARIA_MIN] = self::valorPadraoParam(self::$PARAM_TIT_CARGA_HORARIA_MIN);
        } elseif ($tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO || $tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO || $tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO) {
            // Nenhum parametro especial
            $this->CLAS_ARRAY_PARAM_PROC = array();
        } else {
            throw new NegocioException("Código de obtenção de parâmetros não está programado para a categoria.");
        }

        // verificando parametros explicitos
        if (!Util::vazioNulo($this->IAP_DS_OUTROS_PARAM)) {
            $temp = explode(self::$SEPARADOR_PARAM, $this->IAP_DS_OUTROS_PARAM);
            foreach ($temp as $param) {
                $sep = explode(self::$SEPARADOR_VALOR, $param);
                if (isset($this->CLAS_ARRAY_PARAM_PROC[$sep[0]])) {
                    $this->CLAS_ARRAY_PARAM_PROC[$sep[0]] = $sep[1];
                }
            }
        }
    }

    public static function valorPadraoParam($param) {
        if ($param == self::$PARAM_TIT_STFORMACAO) {
            return FormacaoAcademica::$ST_FORMACAO_COMPLETO;
        }
        if ($param == self::$PARAM_TIT_EXCLUSIVO) {
            return self::$C_FALSE;
        }
        if ($param == self::$PARAM_TIT_SEGGRADUACAO) {
            return self::$C_TRUE;
        }
        if ($param == self::$PARAM_TIT_CARGA_HORARIA_MIN) {
            return "";
        }

        throw new NegocioException("Parâmetro de Item de Avaliação inexistente.");
    }

    public function getValorParam($tpCategoria, $param) {
        // ainda nao carregou
        if ($this->CLAS_ARRAY_PARAM_PROC == NULL) {
            $this->CLAS_carrega_param_item($tpCategoria);
        }

        // verificando se existe o referido parametro
        if (!array_key_exists($param, $this->CLAS_ARRAY_PARAM_PROC)) {
            throw new NegocioException("Parâmetro de Item de Avaliação inexistente.");
        }

        // retornando
        return $this->CLAS_ARRAY_PARAM_PROC[$param];
    }

    /**
     * Essa funçao verifica se um determinado tipo de item admite
     * um determinado parametro. 
     * 
     * NOTA: Essa funcao nao verifica a categoria!
     * 
     * @param int $tpItem
     * @param int $param
     * @return boolean
     */
    public static function admiteParametro($tpItem, $param) {
        if ($param == self::$PARAM_TIT_CARGA_HORARIA_MIN) {
            return TipoCurso::isIdAdmiteCargaHoraria($tpItem);
        }

        if ($param == self::$PARAM_TIT_EXCLUSIVO) {
            return $tpItem == TipoCurso::getTpEspecializacao() || $tpItem == TipoCurso::getTpMestrado() ||
                    $tpItem == TipoCurso::getTpDoutorado();
        }

        if ($param == self::$PARAM_TIT_SEGGRADUACAO) {
            return $tpItem == TipoCurso::getTpGraduacao();
        }

        if ($param == self::$PARAM_TIT_STFORMACAO) {
            throw new NegocioException("Análise do parâmetro depende da categoria!");
        }

        throw new NegocioException("Parâmetro de Item de Avaliação inexistente.");
    }

    /**
     * Essa funçao retorna uma lista de tipo de item que admite
     * um determinado parametro. Retorna uma string representando 
     * um array no formato ['x1', 'x2',...].
     * 
     * NOTA: Essa funcao nao verifica a categoria!
     * 
     * @param int $param
     * @return string
     * @throws NegocioException
     */
    public static function getListaAdmiteParametro($param) {
        if ($param == self::$PARAM_TIT_CARGA_HORARIA_MIN) {
            return TipoCurso::getListaAdmiteCargaHoraria();
        }

        if ($param == self::$PARAM_TIT_EXCLUSIVO) {
            return strArrayJavaScript(array(TipoCurso::getTpEspecializacao(), TipoCurso::getTpMestrado(),
                TipoCurso::getTpDoutorado()));
        }

        if ($param == self::$PARAM_TIT_SEGGRADUACAO) {
            return strArrayJavaScript(array(TipoCurso::getTpGraduacao()));
        }

        if ($param == self::$PARAM_TIT_STFORMACAO) {
            throw new NegocioException("Análise do parâmetro depende da categoria!");
        }

        throw new NegocioException("Parâmetro de Item de Avaliação inexistente.");
    }

    /**
     * 
     * @param int $idCategoria
     * @param boolean $addTpTela Informa se eh para incluir tipos 'fantasmas', 
     * especifico de tela
     * @return array
     * @throws NegocioException
     */
    public static function buscarGruposPorCat($idCategoria, $addTpTela = TRUE) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();


            $sql = "select distinct(IAP_ID_SUBGRUPO),
                        concat('Grupo ', IAP_ID_SUBGRUPO) as dsGrupo
                    from
                        tb_iap_item_aval_proc
                    where CAP_ID_CATEGORIA_AVAl = '$idCategoria'
                    and IAP_ID_SUBGRUPO IS NOT NULL
                    order by IAP_ID_SUBGRUPO";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            if ($addTpTela) {
                // criando vetor com grupos 'padrao'
                $vetRetorno = array(self::$ID_GRUPO_SEM_AGRUPAMENTO => self::$DS_GRUPO_SEM_AGRUPAMENTO);
            } else {
                $vetRetorno = array();
            }

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                if ($addTpTela) {
                    //inserindo dados adicionais e retornando vetor
                    $vetRetorno[self::$ID_GRUPO_NOVO_GRUPO] = self::$DS_GRUPO_NOVO_GRUPO;
                }
                return $vetRetorno;
            }

            //realizando iteração para recuperar
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                //recuperando chave e valor
                $chave = $dados['IAP_ID_SUBGRUPO'];
                $valor = $dados['dsGrupo'];

                //adicionando no vetor
                $vetRetorno[$chave] = $valor;
            }

            if ($addTpTela) {
                //inserindo dados adicionais e retornando vetor
                $vetRetorno[self::$ID_GRUPO_NOVO_GRUPO] = self::$DS_GRUPO_NOVO_GRUPO;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar grupo de itens de avaliação por categoria.", $e);
        }
    }

    /**
     * Essa funçao valida se e possivel cadastrar / alterar um item de avaliacao.
     * 
     * 
     * @param int $idProcesso
     * @param int $idCategoriaAval
     * @param char $tpItemAval
     * @param int $idAreaConh
     * @param int $idSubareaConh
     * @param char $stFormacao
     * @param char $tpExclusivo
     * @param char $segGraduacao
     * @param char $cargaHorariaMin
     * @param string $dsItemExt
     * @param boolean $edicao - Opcional
     * @param int $idItemAval - Opcional
     * @return array na forma: [validou, msgErro]
     * @throws NegocioException
     */
    public static function validarCadastroItemAval($idProcesso, $idCategoriaAval, $tpItemAval, $idAreaConh, $idSubareaConh, $stFormacao, $tpExclusivo, $segGraduacao, $cargaHorariaMin, $dsItemExt, $edicao = FALSE, $idItemAval = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $categoriaAval = CategoriaAvalProc::buscarCatAvalPorId($idCategoriaAval);

            // verificando se existe algum item de avaliaçao com os parametros
            $sql = "select count(*) as cont,
                    IAP_ID_ITEM_AVAL
                    from tb_iap_item_aval_proc
                    where
                    PRC_ID_PROCESSO = '$idProcesso'
                    and CAP_ID_CATEGORIA_AVAL = '$idCategoriaAval'    
                    and IAP_TP_ITEM = '$tpItemAval'";


            // inserindo verificaçoes adicionais
            if ($categoriaAval->admiteItensAreaSubareaObj()) {
                if (!Util::vazioNulo($idAreaConh)) {
                    $sql .= " and IAP_ID_AREA_CONH = '$idAreaConh'";
                } else {
                    $sql .= " and IAP_ID_AREA_CONH IS NULL";
                }

                if (!Util::vazioNulo($idSubareaConh)) {
                    $sql .= " and IAP_ID_SUBAREA_CONH = $idSubareaConh";
                } else {
                    $sql .= " and IAP_ID_AREA_CONH IS NULL";
                }
            }

            // caso de texto externo
            if ($categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_AVAL_EXTERNA) {
                $sql .= " and IAP_DS_OUTROS_PARAM = '$dsItemExt'";
            }

            if ($categoriaAval->isAvalAutomatica() && $categoriaAval->getCAP_TP_CATEGORIA() == CategoriaAvalProc::$TIPO_TITULACAO) {

                // situacao da formacao
                $temp = self::$PARAM_TIT_STFORMACAO . self::$SEPARADOR_VALOR . $stFormacao;
                $sql .= " and IAP_DS_OUTROS_PARAM like '%$temp%'";

                // tipo exclusivo
                if (self::admiteParametro($tpItemAval, self::$PARAM_TIT_EXCLUSIVO)) {
                    $temp = self::$PARAM_TIT_EXCLUSIVO . self::$SEPARADOR_VALOR . self::mapaTrueFalse($tpExclusivo);
                    $sql .= " and IAP_DS_OUTROS_PARAM like '%$temp%'";
                }

                // seg graduacao
                if (self::admiteParametro($tpItemAval, self::$PARAM_TIT_SEGGRADUACAO)) {
                    $temp = self::$PARAM_TIT_SEGGRADUACAO . self::$SEPARADOR_VALOR . self::mapaTrueFalse($segGraduacao);
                    $sql .= " and IAP_DS_OUTROS_PARAM like '%$temp%'";
                }

                // carga horaria minima
                if (self::admiteParametro($tpItemAval, self::$PARAM_TIT_CARGA_HORARIA_MIN)) {
                    if (!Util::vazioNulo($cargaHorariaMin)) {
                        $temp = self::$PARAM_TIT_CARGA_HORARIA_MIN . self::$SEPARADOR_VALOR . $cargaHorariaMin;
                        $sql .= " and IAP_DS_OUTROS_PARAM like '%$temp%'";
                    }
                }
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // recuperando linha
            $dados = ConexaoMysql::getLinha($resp);

            // recuperando dados e analisando
            $quant = $dados['cont'];
            $idCatBD = $dados['IAP_ID_ITEM_AVAL'];
            $validou = $quant == 0;

            // caso especifico de edicao
            if (!$validou && $edicao) {
                // se tiver 1, e for a propria categoria, esta valendo.
                $validou = $quant == 1 && $idItemAval != NULL && $idCatBD == $idItemAval;
            }

            return array($validou, '');
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar cadastro de item de avaliação do Edital.", $e);
        }
    }

    /**
     * Esta função valida se os itens de avaliação estão em conformidade com as regras para participar de uma avaliação
     * 
     * Casos analisados: 
     * 1 - Todos os itens de avaliação de categoria automática tem o valor da pontuação do item diferente de zero,
     *     com exceção do item $TP_AUT_ORDEM_INSC da categoria automatizada.
     * 
     * @param int $idProcesso
     * @return array Array na forma [val => (TRUE, FALSE), msg => ""], onde:
     * val - Boolean indicando a situação da validação
     * msg - String com mensagem de erro, caso val seja FALSE
     * 
     * @throws NegocioException
     */
    public static function validarItensAvalProcParaAvaliacao($idProcesso) {
        try {

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // SQL Para validação do caso 1
            $avalAuto = CategoriaAvalProc::$AVAL_AUTOMATICA;
            $tpCatAvalAuto = CategoriaAvalProc::$TIPO_AVAL_AUTOMATIZADA;
            $tpItemOrdemInsc = self::$TP_AUT_ORDEM_INSC;
            $sql = "select count(*) as cont
                    from tb_iap_item_aval_proc iap join tb_cap_categoria_aval_proc cap on iap.CAP_ID_CATEGORIA_AVAL = cap.CAP_ID_CATEGORIA_AVAL
                    where iap.PRC_ID_PROCESSO = '$idProcesso'
                    and CAP_TP_AVALIACAO = '$avalAuto'
                    and CAP_TP_CATEGORIA != '$tpCatAvalAuto' and IAP_TP_ITEM = '$tpItemOrdemInsc'
                    and (IAP_VAL_PONTUACAO IS NULL or IAP_VAL_PONTUACAO <= 0)";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // se tem itens nessa condição, então está errado!
            if ($conexao->getResult("cont", $resp) != 0) {
                return array("val" => FALSE, "msg" => "Existem itens de avaliação automática sem pontuação!");
            }

            // Tudo OK
            return array("val" => TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar itens de avaliação do processo.", $e);
        }
    }

    private static function mapaTrueFalse($flag) {
        return $flag == FLAG_BD_SIM ? self::$C_TRUE : self::$C_FALSE;
    }

    /**
     * Essa funcao retorna um array indexado pelo grupo, informando a quantidade de
     * itens em cada grupo.
     * 
     * @param ItemAvalProc $vetItens - Array de itens
     * @return array Array na forma [Grupo,Qtde]
     */
    public static function qtdePorGrupo($vetItens) {
        $vetRetorno = array();

        if ($vetItens == NULL) {
            return $vetRetorno;
        }

        foreach ($vetItens as $item) {
            $vetRetorno[$item->getSubgrupoVisualizacao()] = !isset($vetRetorno[$item->getSubgrupoVisualizacao()]) ? 1 : $vetRetorno[$item->getSubgrupoVisualizacao()] + 1;
        }
        return $vetRetorno;
    }

    public function getIdGrupoInfComp() {
        if ($this->IAP_TP_ITEM == self::$TP_INF_COMP) {
            $temp = explode("=", $this->IAP_DS_OUTROS_PARAM);
            return $temp[0];
        }
    }

    public function getNotaReal() {
        return $this->notaReal;
    }

    public function getNotaNormalizada() {
        return $this->notaNormalizada;
    }

    public function getHtmlNotaReal() {
        return money_format("%i", $this->notaReal);
    }

    public function getHtmlNotaNormalizada() {
        return money_format("%i", $this->notaNormalizada);
    }

    public function getVlNotaFormatada() {
        return NGUtil::formataDecimal($this->IAP_VAL_PONTUACAO);
    }

    public function getVlNotaMaxFormatada() {
        return NGUtil::formataDecimal($this->IAP_VAL_PONTUACAO_MAX);
    }

    public function setNotaReal($notaReal) {
        $this->notaReal = $notaReal;
    }

    public function setNotaNormalizada($notaNormalizada) {
        $this->notaNormalizada = $notaNormalizada;
    }

    public function getSubgrupoVisualizacao() {
        return $this->IAP_ID_SUBGRUPO == NULL ? self::getCodSemSubgrupo() : $this->IAP_ID_SUBGRUPO;
    }

    public static function getCodSemSubgrupo() {
        return 0;
    }

    public static function getMsgSemSubgrupo() {
        return "Sem Agrupamento";
    }

    public static function getMsgSemPontuacao() {
        return "-";
    }

    /**
     * Essa função retorna uma breve explicação sobre a unidade de pontuação para 
     * auxiliar o administrador na criação das regras de pontuação
     * 
     * 
     * @param char $tpCategoria
     */
    public static function getExpUnidadePontuacaoAdmin($tpCategoria) {
        if ($tpCategoria == CategoriaAvalProc::$TIPO_PUBLICACAO) {
            return Publicacao::getDsUnidadeAdmin();
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_PART_EVENTO) {
            return ParticipacaoEvento::getDsUnidadeAdmin();
        }

        if ($tpCategoria == CategoriaAvalProc::$TIPO_ATUACAO) {
            return Atuacao::getDsUnidadeAdmin();
        }
    }

    public function isSemSubgrupo() {
        return $this->IAP_ID_SUBGRUPO == NULL;
    }

    public function getIdSubGrupoVisualizacao() {
        return $this->isSemSubgrupo() ? self::$ID_GRUPO_SEM_AGRUPAMENTO : $this->getIAP_ID_SUBGRUPO();
    }

    /* GET FIELDS FROM TABLE */

    function getIAP_ID_ITEM_AVAL() {
        return $this->IAP_ID_ITEM_AVAL;
    }

    /* End of get IAP_ID_ITEM_AVAL */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getCAP_ID_CATEGORIA_AVAL() {
        return $this->CAP_ID_CATEGORIA_AVAL;
    }

    /* End of get CAP_ID_CATEGORIA_AVAL */

    function getIAP_TP_ITEM() {
        return $this->IAP_TP_ITEM;
    }

    /* End of get IAP_TP_ITEM */

    function getIAP_ORDEM() {
        return $this->IAP_ORDEM;
    }

    /* End of get IAP_ORDEM */

    function getIAP_ID_AREA_CONH() {
        return $this->IAP_ID_AREA_CONH;
    }

    /* End of get IAP_ID_AREA_CONH */

    function getIAP_ID_SUBAREA_CONH() {
        return $this->IAP_ID_SUBAREA_CONH;
    }

    /* End of get IAP_ID_SUBAREA_CONH */

    function getIAP_VAL_PONTUACAO() {
        return $this->IAP_VAL_PONTUACAO;
    }

    /* End of get IAP_VAL_PONTUACAO */

    function getIAP_VAL_PONTUACAO_MAX() {
        return $this->IAP_VAL_PONTUACAO_MAX;
    }

    /* End of get IAP_VAL_PONTUACAO_MAX */

    function getIAP_DS_OUTROS_PARAM() {
        return $this->IAP_DS_OUTROS_PARAM;
    }

    /* End of get IAP_DS_OUTROS_PARAM */

    function getIAP_ID_SUBGRUPO() {
        return $this->IAP_ID_SUBGRUPO;
    }

    /* End of get IAP_ID_SUBGRUPO */


    /* SET FIELDS FROM TABLE */

    function setIAP_ID_ITEM_AVAL($value) {
        $this->IAP_ID_ITEM_AVAL = $value;
    }

    /* End of SET IAP_ID_ITEM_AVAL */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setCAP_ID_CATEGORIA_AVAL($value) {
        $this->CAP_ID_CATEGORIA_AVAL = $value;
    }

    /* End of SET CAP_ID_CATEGORIA_AVAL */

    function setIAP_TP_ITEM($value) {
        $this->IAP_TP_ITEM = $value;
    }

    /* End of SET IAP_TP_ITEM */

    function setIAP_ORDEM($value) {
        $this->IAP_ORDEM = $value;
    }

    /* End of SET IAP_ORDEM */

    function setIAP_ID_AREA_CONH($value) {
        $this->IAP_ID_AREA_CONH = $value;
    }

    /* End of SET IAP_ID_AREA_CONH */

    function setIAP_ID_SUBAREA_CONH($value) {
        $this->IAP_ID_SUBAREA_CONH = $value;
    }

    /* End of SET IAP_ID_SUBAREA_CONH */

    function setIAP_VAL_PONTUACAO($value) {
        $this->IAP_VAL_PONTUACAO = $value;
    }

    /* End of SET IAP_VAL_PONTUACAO */

    function setIAP_VAL_PONTUACAO_MAX($value) {
        $this->IAP_VAL_PONTUACAO_MAX = $value;
    }

    /* End of SET IAP_VAL_PONTUACAO_MAX */

    function setIAP_DS_OUTROS_PARAM($value) {
        $this->IAP_DS_OUTROS_PARAM = $value;
    }

    /* End of SET IAP_DS_OUTROS_PARAM */

    function setIAP_ID_SUBGRUPO($value) {
        $this->IAP_ID_SUBGRUPO = $value;
    }

    /* End of SET IAP_ID_SUBGRUPO */
}

?>
