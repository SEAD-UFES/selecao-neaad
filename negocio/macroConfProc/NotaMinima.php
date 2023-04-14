<?php

/**
 * Classe que implementa a funcao de NotaMinima
 * 
 * Ela pode ser usada como Criterio de Eliminacao
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";

// funcões de callback

/**
 * Função que carrega a lista de categorias para o sistema
 * 
 * @param array $arrayParamExt - Array na forma [idParam => vlParam]. Este array pode conter parâmetros externos
 * e parâmetros da macro. A falta de algum parâmetro imprescindível pode fazer disparar uma exceção.
 * 
 * @throws NegocioException
 */
function carrega_categoria_proc_notaMinima($arrayParamExt) {
    // recuperando parâmetros
    if (!isset($arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'NotaMinima' inconsistentes.");
    }
    $idProcesso = $arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO];

    if (!isset($arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'NotaMinima' inconsistentes.");
    }
    $idEtapaAval = $arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL];

    $categorias = buscarCatAvalPorProcIdEtapaCT($idProcesso, $idEtapaAval);

    // loop nas categorias
    $ret = array();
    if ($categorias != NULL) {
        foreach ($categorias as $categoria) {
            $ret[$categoria->getCAP_ID_CATEGORIA_AVAL()] = $categoria->getDsSelectCategoria();
        }
    }
    return $ret;
}

/**
 * Função que carrega a lista de itens de uma categoria para o sistema
 * 
 * @param array $arrayParamExt - Array na forma [idParam => vlParam]. Este array pode conter parâmetros externos
 * e parâmetros da macro. A falta de algum parâmetro imprescindível pode fazer disparar uma exceção.
 * 
 * @throws NegocioException
 */
function carrega_itemAval_proc_notaMinima($arrayParamExt) {
    // recuperando parâmetros
    if (!isset($arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'NotaMinima' inconsistentes.");
    }
    $idProcesso = $arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO];

    if (!isset($arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'NotaMinima' inconsistentes.");
    }
    $idEtapaAval = $arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL];

    if (!isset($arrayParamExt[NotaMinima::$paramCategoria])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'NotaMinima' inconsistentes.");
    }
    $idCategoria = $arrayParamExt[NotaMinima::$paramCategoria];

    // buscar categoria para processar
    $categoria = buscarCatAvalPorIdCT($idCategoria);

    $itensAval = buscarItensAvalPorCatCT($idProcesso, $idCategoria);

    // loop nos itens
    $ret = array();
    if ($itensAval != NULL) {
        foreach ($itensAval as $itemAval) {
            $ret[$itemAval->getIAP_ID_ITEM_AVAL()] = $itemAval->getDsSelectCategoria($categoria->getCAP_TP_CATEGORIA());
        }
    }
    return $ret;
}

function get_dsCategoriaProc_notaMinima($idCategoria) {
    $categoria = buscarCatAvalPorIdCT($idCategoria);
    return $categoria->getDsSelectCategoria();
}

function get_dsItemAvalProc_NotaMinima($idItemAval) {
    $itemAval = buscarItemAvalPorIdCT($idItemAval);
    $categoria = buscarCatAvalPorIdCT($itemAval->getCAP_ID_CATEGORIA_AVAL());
    return $itemAval->getDsSelectCategoria($categoria->getCAP_TP_CATEGORIA());
}

class NotaMinima extends MacroAbs implements MacroCritEliminacao {

    private static $paramValor = "vlNota";
    private static $paramInterpretacao = "tpInterpretacao";
    public static $paramCategoria = "idCategoria";
    public static $paramItem = "idItem";
    // OBS: Ao criar outro tipo de interpretação, deve-se ajustar as funções desta classe!
    private static $INT_ABSOLUTO = 'A';
    private static $INT_PERCENTUAL = 'P';
    private static $INT_DS_ABSOLUTO = 'Valor Absoluto';
    private static $INT_DS_PERCENTUAL = 'Valor Percentual';

    public function __construct($tpMacro, $paramExt = NULL) {
        parent::__construct($tpMacro, $paramExt);

        // criando parametros
        $temp = array(self::$INT_ABSOLUTO => self::$INT_DS_ABSOLUTO, self::$INT_PERCENTUAL => self::$INT_DS_PERCENTUAL);

        $this->parametros = array(self::$paramCategoria => new ParamMacro(self::$paramCategoria, ParamMacro::$TIPO_LISTA_CALL_BACK, "Categoria:", TRUE, "carrega_categoria_proc_notaMinima", "get_dsCategoriaProc_notaMinima", FALSE),
            self::$paramItem => new ParamMacro(self::$paramItem, ParamMacro::$TIPO_LISTA_CALL_BACK, "Item:", TRUE, "carrega_itemAval_proc_notaMinima", "get_dsItemAvalProc_notaMinima", FALSE, TRUE),
            self::$paramValor => new ParamMacro(self::$paramValor, ParamMacro::$TIPO_DECIMAL, "Valor:"),
            self::$paramInterpretacao => new ParamMacro(self::$paramInterpretacao, ParamMacro::$TIPO_LISTA, "Interpretação:", FALSE, $temp));
        $this->qtParametros = count($this->parametros);
    }

    public function getNmFantasia() {
        return "Nota Mínima";
    }

    public function getIdMacro() {
        return "notaMin";
    }

    public function getListaIdParam() {
        return array_keys($this->parametros);
    }

    public function getParamPorId($idParam) {
        if (!isset($this->parametros[$idParam])) {
            throw new NegocioException("Parâmetro inexistente!");
        }
        return $this->parametros[$idParam];
    }

    public function getListaParam() {
        return array_values($this->parametros);
    }

    public function getQtdeParametros() {
        return $this->qtParametros;
    }

    public function getDsMotivoEliminacao() {
        $ret = "Não alcançou ";

        $ret .= $this->isValorAbsoluto() ? "o mínimo de " : "o percentual mínimo de ";
        $ret .= $this->getValorParam(self::$paramValor, TRUE);
        $ret .= $this->isValorAbsoluto() ? " pontos" : "% do total de pontos";

        // Tem item
        if (!Util::vazioNulo($this->getValorParam(self::$paramItem))) {
            $ret .= " no item '" . preg_replace('/ \(Cód [0-9]*\)/', "", $this->getValorParam(self::$paramItem, TRUE)) . "'";
            // tem categoria
        } elseif (!Util::vazioNulo($this->getValorParam(self::$paramCategoria))) {
            $ret .= " na categoria '" . preg_replace('/ \(Cód [0-9]*\)/', "", $this->getValorParam(self::$paramCategoria, TRUE)) . "'";
        }
        $ret .= ".";

        return $ret;
    }

    public function getSqlCondAplicaCriterio($idEtapaAval = NULL) {
        $avalAtiva = RelNotasInsc::$SIT_ATIVA;

        $ret = "coalesce((select sum(RNI_VL_NOTA_NORMALIZADA)
                            from
                                tb_rni_rel_notas_insc rni
                            where
                                ipr.IPR_ID_INSCRICAO = rni.IPR_ID_INSCRICAO
                                and RNI_ST_AVALIACAO = '$avalAtiva' ";

        // tem categoria 
        if (!Util::vazioNulo($this->getValorParam(self::$paramCategoria))) {
            $ret .= " and CAP_ID_CATEGORIA_AVAL = '" . $this->getValorParam(self::$paramCategoria) . "'";
        }

        // tem item
        if (!Util::vazioNulo($this->getValorParam(self::$paramItem))) {
            $ret .= " and IAP_ID_ITEM_AVAL = '" . $this->getValorParam(self::$paramItem) . "'";
        }

        // terminando bloco e colocando simbolo comparativo
        $ret .= "), 0) < ";

        // caso de valor absoluto
        if ($this->isValorAbsoluto()) {
            $ret .= $this->getValorParam(self::$paramValor);
        } else {
            $ret .= "(" . ($this->getValorParam(self::$paramValor) * 0.01) . " * ";

            // tem item
            if (!Util::vazioNulo($this->getValorParam(self::$paramItem))) {
                $ret .= "(select IAP_VAL_PONTUACAO_MAX from tb_iap_item_aval_proc where IAP_ID_ITEM_AVAL = '" . $this->getValorParam(self::$paramItem) . "')";
            } elseif (!Util::vazioNulo($this->getValorParam(self::$paramCategoria))) {
                $ret .= "(select CAP_VL_PONTUACAO_MAX from tb_cap_categoria_aval_proc where CAP_ID_CATEGORIA_AVAL = '" . $this->getValorParam(self::$paramCategoria) . "')";
            } else {
                $ret .= "(select sum(CAP_VL_PONTUACAO_MAX) from tb_cap_categoria_aval_proc cap where cap.PRC_ID_PROCESSO = ipr.PRC_ID_PROCESSO";

                // adicionando etapa, se necessário
                $ret .= $idEtapaAval != NULL ? " and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval')" : ")";
            }

            // finalizando
            $ret .= ")";
        }

        return $ret;
    }

    private function isValorAbsoluto() {
        return $this->getValorParam(self::$paramInterpretacao) == self::$INT_ABSOLUTO;
    }

    private function getValorParam($nmParam, $string = FALSE) {
        if (!$string) {
            return $this->parametros[$nmParam]->getValor();
        }
        return $this->parametros[$nmParam]->getStrParametro(TRUE);
    }

}
