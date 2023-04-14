<?php

/**
 * Classe que implementa a funcao de Maior Pontuação
 * 
 * Ela pode ser usada como Criterio de Desempate
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";

// funcoes para call back
// 
// 

/**
 * Função que carrega a lista de etapas para o sistema
 * 
 * @param array $arrayParamExt - Array na forma [idParam => vlParam]. Este array pode conter parâmetros externos
 * e parâmetros da macro. A falta de algum parâmetro imprescindível pode fazer disparar uma exceção.
 * 
 * @throws NegocioException
 */
function carrega_etapa_proc_maiorPontuacao($arrayParamExt) {
    // recuperando parâmetros
    if (!isset($arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'MaiorPontuacao' inconsistentes.");
    }
    $idProcesso = $arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO];

    $etapas = buscarEtapaAvalPorProcCT($idProcesso);

    // loop nas etapas
    $ret = array();
    if ($etapas != NULL) {
        foreach ($etapas as $etapa) {
            $ret[$etapa->getEAP_ID_ETAPA_AVAL_PROC()] = $etapa->getNomeEtapa();
        }
    }
    return $ret;
}

/**
 * Função que carrega a lista de categorias para o sistema
 * 
 * @param array $arrayParamExt - Array na forma [idParam => vlParam]. Este array pode conter parâmetros externos
 * e parâmetros da macro. A falta de algum parâmetro imprescindível pode fazer disparar uma exceção.
 * 
 * @throws NegocioException
 */
function carrega_categoria_proc_maiorPontuacao($arrayParamExt) {
    // recuperando parâmetros
    if (!isset($arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'MaiorPontuacao' inconsistentes.");
    }
    $idProcesso = $arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO];

    if ((!isset($arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL]) && !isset($arrayParamExt[MaiorPontuacao::$paramEtapa])) || (!isset($arrayParamExt[MaiorPontuacao::$paramEtapa]) && isset($arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL]) && $arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL] == MacroConfProc::$ID_ETAPA_RESULTADO_FINAL)) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'MaiorPontuacao' inconsistentes.");
    }
    $idEtapaAval = isset($arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL]) && $arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL] != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL ? $arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL] : $arrayParamExt[MaiorPontuacao::$paramEtapa];

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
function carrega_itemAval_proc_maiorPontuacao($arrayParamExt) {
    // recuperando parâmetros
    if (!isset($arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'MaiorPontuacao' inconsistentes.");
    }
    $idProcesso = $arrayParamExt[MacroAbs::$_PARAM_ID_PROCESSO];

    if (!isset($arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL]) && !isset($arrayParamExt[MaiorPontuacao::$paramEtapa])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'MaiorPontuacao' inconsistentes.");
    }
    $idEtapaAval = isset($arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL]) ? $arrayParamExt[MacroAbs::$_PARAM_ID_ETAPA_AVAL] : $arrayParamExt[MaiorPontuacao::$paramEtapa];

    if (!isset($arrayParamExt[MaiorPontuacao::$paramCategoria])) {
        throw new NegocioException("Informações para carga de parâmetros da Macro 'MaiorPontuacao' inconsistentes.");
    }
    $idCategoria = $arrayParamExt[MaiorPontuacao::$paramCategoria];

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

function get_dsEtapaProc_maiorPontuacao($idEtapaAval) {
    $etapa = buscarEtapaAvalPorIdCT($idEtapaAval);
    return $etapa->getNomeEtapa();
}

function get_dsCategoriaProc_maiorPontuacao($idCategoria) {
    $categoria = buscarCatAvalPorIdCT($idCategoria);
    return $categoria->getDsSelectCategoria();
}

function get_dsItemAvalProc_maiorPontuacao($idItemAval) {
    $itemAval = buscarItemAvalPorIdCT($idItemAval);
    $categoria = buscarCatAvalPorIdCT($itemAval->getCAP_ID_CATEGORIA_AVAL());
    return $itemAval->getDsSelectCategoria($categoria->getCAP_TP_CATEGORIA());
}

/**
 * Parâmetros chaves: idItem, idCategoria e idEtapaAvalMacro
 * 
 */
class MaiorPontuacao extends MacroAbs implements MacroCritDesempate {

    public static $paramEtapa = "idEtapaAvalMacro";
    public static $paramCategoria = "idCategoria";
    public static $paramItem = "idItem";

    public function __construct($tpMacro, $paramExt = NULL) {
        parent::__construct($tpMacro, $paramExt);

        // consultando parâmetros externos: Chamada com etapa ou 'Resultado Final'?
        $idEtapaInt = $this->getValorParamExterno(self::$_PARAM_ID_ETAPA_AVAL);
        if ($idEtapaInt != NULL && $idEtapaInt != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL) {
            // criando parametros
            $this->parametros = array(self::$paramCategoria => new ParamMacro(self::$paramCategoria, ParamMacro::$TIPO_LISTA_CALL_BACK, "Categoria:", TRUE, "carrega_categoria_proc_maiorPontuacao", "get_dsCategoriaProc_maiorPontuacao"),
                self::$paramItem => new ParamMacro(self::$paramItem, ParamMacro::$TIPO_LISTA_CALL_BACK, "Item:", TRUE, "carrega_itemAval_proc_maiorPontuacao", "get_dsItemAvalProc_maiorPontuacao", FALSE, TRUE));
        } else {
            // criando parametros com etapa
            $this->parametros = array(self::$paramEtapa => new ParamMacro(self::$paramEtapa, ParamMacro::$TIPO_LISTA_CALL_BACK, "Etapa:", TRUE, "carrega_etapa_proc_maiorPontuacao", "get_dsEtapaProc_maiorPontuacao"),
                self::$paramCategoria => new ParamMacro(self::$paramCategoria, ParamMacro::$TIPO_LISTA_CALL_BACK, "Categoria:", TRUE, "carrega_categoria_proc_maiorPontuacao", "get_dsCategoriaProc_maiorPontuacao", FALSE, TRUE),
                self::$paramItem => new ParamMacro(self::$paramItem, ParamMacro::$TIPO_LISTA_CALL_BACK, "Item:", TRUE, "carrega_itemAval_proc_maiorPontuacao", "get_dsItemAvalProc_maiorPontuacao", FALSE, TRUE));
        }
        $this->qtParametros = count($this->parametros);
    }

    public function getNmFantasia() {
        return "Maior Pontuação";
    }

    public function getIdMacro() {
        return "maiorPontuacao";
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

    public function getSqlAddOrderByAplicaCriterio() {
        $avalAtiva = RelNotasInsc::$SIT_ATIVA;

        $ret = "(coalesce((select sum(RNI_VL_NOTA_NORMALIZADA)
                            from
                                tb_rni_rel_notas_insc rni
                            where
                                ipr.IPR_ID_INSCRICAO = rni.IPR_ID_INSCRICAO
                                and RNI_ST_AVALIACAO = '$avalAtiva' ";

        // tem etapa 
        if (!Util::vazioNulo($this->getValorParam(self::$paramEtapa))) {
            $ret .= " and CAP_ID_CATEGORIA_AVAL IN (select CAP_ID_CATEGORIA_AVAL from tb_cap_categoria_aval_proc where EAP_ID_ETAPA_AVAL_PROC = '" . $this->getValorParam(self::$paramEtapa) . "')";
        }

        // tem categoria 
        if (!Util::vazioNulo($this->getValorParam(self::$paramCategoria))) {
            $ret .= " and CAP_ID_CATEGORIA_AVAL = '" . $this->getValorParam(self::$paramCategoria) . "'";
        }

        // tem item
        if (!Util::vazioNulo($this->getValorParam(self::$paramItem))) {
            $ret .= " and IAP_ID_ITEM_AVAL = '" . $this->getValorParam(self::$paramItem) . "'";
        }

        // terminando bloco e colocando simbolo comparativo
        $ret .= "), 0)) desc";

        return $ret;
    }

    private function getValorParam($nmParam, $string = FALSE) {
        // Parâmetro sazonal não incluído
        if (!isset($this->parametros[$nmParam])) {
            return NULL;
        }

        if (!$string) {
            return $this->parametros[$nmParam]->getValor();
        }
        return $this->parametros[$nmParam]->getStrParametro(TRUE);
    }

}
