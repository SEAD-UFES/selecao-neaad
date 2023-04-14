<?php

/**
 * tb_mcp_macro_conf_proc class
 * This class manipulates the table MacroConfProc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 04/07/2014
 * */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";
require_once $CFG->rpasta . "/negocio/ClasInscricaoReserva.php";

class MacroConfProc {

    private $MCP_ID_MACRO_CONF_PROC;
    private $PRC_ID_PROCESSO;
    private $MCP_DS_MACRO;
    private $MCP_TP_MACRO;
    private $MCP_ORDEM_APLICACAO;
    private $MCP_DS_PARAMETROS;
    private $EAP_ID_ETAPA_AVAL_PROC;
    // tipos de macro
    public static $TIPO_CRIT_ELIMINACAO = 'E';
    public static $TIPO_CRIT_CLASSIFICACAO = 'C';
    public static $TIPO_CRIT_DESEMPATE = 'D';
    public static $TIPO_CRIT_SELECAO = 'S';
    public static $TIPO_CRIT_SELECAO_RESERVA = 'R';
    // tipos de macro especiais
    public static $TIPO_ESP_FORMULA_FINAL = "F";
    public static $NM_TIPO_ESP_FORMULA_FINAL = "macroNotaFinal";
    // tipo de fórmula rapida
    public static $FORM_RAP_NOTA_ETAPA = "N";
    public static $FORM_RAP_SOMA_NOTAS = "S";
    public static $FORM_RAP_MEDIA_SIMPLES = "M";
    public static $FORM_RAP_MEDIA_PONDERADA = "P";
    // variaveis constantes
    public static $SEPARADOR_PARAM = ";";
    public static $SEPARADOR_VALOR = "=";
    // campos de processamento interno
    private $objMacro = NULL; // campo carregado sobre demanda. Nunca chamá-lo diretamente
    private $strParametros = NULL; // campo carregado sobre demanda. Nunca chamá-lo diretamente
    // ajuda interface
    public static $COD_TP_ORDENACAO_MACRO_ELI = 'CritEli'; // Usado para atualizar ordem
    public static $COD_TP_ORDENACAO_MACRO_CLAS = 'CritCla'; // Usado para atualizar ordem
    public static $COD_TP_ORDENACAO_MACRO_DES = 'CritDes'; // Usado para atualizar ordem
    public static $COD_TP_ORDENACAO_MACRO_SEL = 'CritSel'; // Usado para atualizar ordem
    public static $COD_TP_ORDENACAO_MACRO_RES = 'CritRes'; // Usado para atualizar ordem
    // constantes internas
    public static $OPERADOR_SOMA = "+";
    public static $OPERADOR_MULTIPLACAO = "*";
    public static $OPERADOR_DIVISAO = "/";
    public static $OPERADOR_SUBTRACAO = "-";
    public static $_PESO_GENERICO = "P";
    public static $ID_ETAPA_RESULTADO_FINAL = "final";

    public static function getDsTipoMacro($tipo) {
        if ($tipo == self::$TIPO_CRIT_CLASSIFICACAO) {
            return "Critério de Classificação";
        }
        if ($tipo == self::$TIPO_CRIT_DESEMPATE) {
            return "Critério de Desempate";
        }
        if ($tipo == self::$TIPO_CRIT_ELIMINACAO) {
            return "Critério de Eliminação";
        }
        if ($tipo == self::$TIPO_CRIT_SELECAO) {
            return "Critério de Seleção";
        }
        if ($tipo == self::$TIPO_CRIT_SELECAO_RESERVA) {
            return "Critério de Cadastro de Reserva";
        }
        return NULL;
    }

    public static function getCompTipoMacro($tipoMacro) {
        // arrumando tipo
        $tipoMacro = str_replace("'", "", $tipoMacro);

        if ($tipoMacro == MacroConfProc::$TIPO_CRIT_CLASSIFICACAO) {
            return "Classificacao";
        }
        if ($tipoMacro == MacroConfProc::$TIPO_CRIT_DESEMPATE) {
            return "Desempate";
        }
        if ($tipoMacro == MacroConfProc::$TIPO_CRIT_ELIMINACAO) {
            return "Eliminacao";
        }
        if ($tipoMacro == MacroConfProc::$TIPO_CRIT_SELECAO) {
            return "Selecao";
        }
        if ($tipoMacro == MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA) {
            return "CadReserva";
        }
        return "";
    }

    public static function getDsFormulaRapida($idFormula) {
        if ($idFormula == self::$FORM_RAP_SOMA_NOTAS) {
            return "Soma das Etapas";
        }
        if ($idFormula == self::$FORM_RAP_MEDIA_PONDERADA) {
            return "Média Ponderada das Etapas";
        }
        if ($idFormula == self::$FORM_RAP_MEDIA_SIMPLES) {
            return "Média Simples das Etapas";
        }
        if ($idFormula == self::$FORM_RAP_NOTA_ETAPA) {
            return "Nota da Etapa Única";
        }
        return NULL;
    }

    public static function getListaFormulaRapida($listaEtapas) {
        if (Util::vazioNulo($listaEtapas) || count($listaEtapas) <= 1) {
            return array(self::$FORM_RAP_NOTA_ETAPA => self::getDsFormulaRapida(self::$FORM_RAP_NOTA_ETAPA));
        }
        // caso geral
        return array(self::$FORM_RAP_SOMA_NOTAS => self::getDsFormulaRapida(self::$FORM_RAP_SOMA_NOTAS),
            self::$FORM_RAP_MEDIA_SIMPLES => self::getDsFormulaRapida(self::$FORM_RAP_MEDIA_SIMPLES),
            self::$FORM_RAP_MEDIA_PONDERADA => self::getDsFormulaRapida(self::$FORM_RAP_MEDIA_PONDERADA));
    }

    public static function getFormulaFinalRapida($idProcesso, $idFormulaRapida) {
        if (Util::vazioNulo($idFormulaRapida)) {
            return "";
        }
        // recuperando etapas
        $etapas = EtapaAvalProc::buscarEtapaAvalPorProc($idProcesso);
        // sem etapas? 
        if (Util::vazioNulo($etapas)) {
            return "";
        }
        $qtEtapas = count($etapas);

        // caso de uma etapa
        if ($qtEtapas == 1) {
            if ($idFormulaRapida == self::$FORM_RAP_NOTA_ETAPA) {
                return $etapas[0]->getIdElementoFormula();
            } else {
                return "";
            }
        } else {
            $ret = "";
            $i = 0;


            if ($idFormulaRapida == self::$FORM_RAP_SOMA_NOTAS) {
                foreach ($etapas as $etapa) {
                    $ret .= $etapa->getIdElementoFormula();
                    if ($i < $qtEtapas - 1) {
                        $ret .= " " . self::$OPERADOR_SOMA . " ";
                    }
                    $i++;
                }
                return $ret;
            } elseif ($idFormulaRapida == self::$FORM_RAP_MEDIA_SIMPLES) {
                $ret = "(";
                foreach ($etapas as $etapa) {
                    $ret .= $etapa->getIdElementoFormula();
                    if ($i < $qtEtapas - 1) {
                        $ret .= " " . self::$OPERADOR_SOMA . " ";
                    }
                    $i++;
                }

                $ret .= ") " . self::$OPERADOR_DIVISAO . " " . $qtEtapas;
                return $ret;
            } elseif ($idFormulaRapida == self::$FORM_RAP_MEDIA_PONDERADA) {
                $ret = "(";
                $divisao = "";
                foreach ($etapas as $etapa) {
                    $temp = self::$_PESO_GENERICO . ($i + 1) . " " . self::$OPERADOR_MULTIPLACAO . " ";
                    $divisao .= self::$_PESO_GENERICO . ($i + 1);
                    $ret .= $temp . $etapa->getIdElementoFormula();
                    if ($i < $qtEtapas - 1) {
                        $ret .= " " . self::$OPERADOR_SOMA . " ";
                        $divisao .= " " . self::$OPERADOR_SOMA . " ";
                    }
                    $i++;
                }

                $ret .= ") " . self::$OPERADOR_DIVISAO . " " . $divisao;
                return $ret;
            } else {
                return "";
            }
        }
    }

    public static function getMapaTipoOrdemTipoMacro($tipoOrdem) {
        if ($tipoOrdem == self::$COD_TP_ORDENACAO_MACRO_CLAS) {
            return self::$TIPO_CRIT_CLASSIFICACAO;
        }

        if ($tipoOrdem == self::$COD_TP_ORDENACAO_MACRO_DES) {
            return self::$TIPO_CRIT_DESEMPATE;
        }

        if ($tipoOrdem == self::$COD_TP_ORDENACAO_MACRO_ELI) {
            return self::$TIPO_CRIT_ELIMINACAO;
        }

        if ($tipoOrdem == self::$COD_TP_ORDENACAO_MACRO_SEL) {
            return self::$TIPO_CRIT_SELECAO;
        }

        if ($tipoOrdem == self::$COD_TP_ORDENACAO_MACRO_RES) {
            return self::$TIPO_CRIT_SELECAO_RESERVA;
        }
        throw new NegocioException("Tipo de ordenação desconhecido. Impossível mapear!");
    }

    public function getDsTipoObj() {
        return self::getDsTipoMacro($this->MCP_TP_MACRO);
    }

    public function getCodTpOrdenacao() {
        if ($this->MCP_TP_MACRO == self::$TIPO_CRIT_CLASSIFICACAO) {
            return self::$COD_TP_ORDENACAO_MACRO_CLAS;
        }

        if ($this->MCP_TP_MACRO == self::$TIPO_CRIT_DESEMPATE) {
            return self::$COD_TP_ORDENACAO_MACRO_DES;
        }

        if ($this->MCP_TP_MACRO == self::$TIPO_CRIT_ELIMINACAO) {
            return self::$COD_TP_ORDENACAO_MACRO_ELI;
        }

        if ($this->MCP_TP_MACRO == self::$TIPO_CRIT_SELECAO) {
            return self::$COD_TP_ORDENACAO_MACRO_SEL;
        }

        if ($this->MCP_TP_MACRO == self::$TIPO_CRIT_SELECAO_RESERVA) {
            return self::$COD_TP_ORDENACAO_MACRO_RES;
        }
        return NULL;
    }

    /* Construtor padrão da classe */

    public function __construct($MCP_ID_MACRO_CONF_PROC, $PRC_ID_PROCESSO, $MCP_DS_MACRO, $MCP_TP_MACRO, $MCP_ORDEM_APLICACAO, $MCP_DS_PARAMETROS, $EAP_ID_ETAPA_AVAL_PROC) {
        $this->MCP_ID_MACRO_CONF_PROC = $MCP_ID_MACRO_CONF_PROC;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->MCP_DS_MACRO = $MCP_DS_MACRO;
        $this->MCP_TP_MACRO = $MCP_TP_MACRO;
        $this->MCP_ORDEM_APLICACAO = $MCP_ORDEM_APLICACAO;
        $this->MCP_DS_PARAMETROS = $MCP_DS_PARAMETROS;
        $this->EAP_ID_ETAPA_AVAL_PROC = $EAP_ID_ETAPA_AVAL_PROC;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_mcp_macro_conf_proc where PRC_ID_PROCESSO = '$idProcesso'";
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por aplicar os critérios de eliminação 
     * automáticos da etapa em questão.
     * 
     * @param EtapaSelProc $etapa
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsAplicaCritEliminacao($etapa, &$arrayCmds) {
        // recuperando situação de inscrição
        $stEliminadoAuto = InscricaoProcesso::$SIT_INSC_AUTO_ELIMINADO;
        $stInscOk = InscricaoProcesso::$SIT_INSC_OK;

        // sql para remover toda eliminação automática que é da própria etapa
        $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
                            set IPR_ST_INSCRICAO = NULL,
                            IPR_DS_OBS_NOTA = NULL
                        where
                            pch_id_chamada = '{$etapa->getPCH_ID_CHAMADA()}' and IPR_ST_INSCRICAO = '$stEliminadoAuto'
                            and IPR_ID_ETAPA_SEL_NOTA = {$etapa->getESP_ID_ETAPA_SEL()}";

        // recuperando macros com critérios de eliminação
        $listaMacros = self::buscarMacroConfProcPorProcEtapaTp($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC(), MacroConfProc::$TIPO_CRIT_ELIMINACAO);

        if ($listaMacros != NULL) {

            // percorrendo lista para realizar os tratamentos necessários
            foreach ($listaMacros as $macroConf) {
                // recuperando objeto
                $objMacro = $macroConf->getObjMacro();

                // carregando valor nos parametros
                MacroAbs::carregaValorParam($objMacro, $macroConf->MCP_DS_PARAMETROS);

                // recuperando motivo de eliminação personalizado
                $motivoEliminacao = NGUtil::trataCampoStrParaBD($objMacro->getDsMotivoEliminacao());

                // definindo sql inicial
                $sqlTmp = "update tb_ipr_inscricao_processo ipr 
                       set IPR_ST_INSCRICAO = '$stEliminadoAuto',
                       IPR_DS_OBS_NOTA = $motivoEliminacao
                       where
                       pch_id_chamada = '{$etapa->getPCH_ID_CHAMADA()}'
                       and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')";

                // adicionando restrições 
                $sqlTmp .= "and " . $objMacro->getSqlCondAplicaCriterio($etapa->getEAP_ID_ETAPA_AVAL_PROC());

                // adicionando no vetor
                $arrayCmds [] = $sqlTmp;
            }
        }
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por aplicar os critérios de clasificação 
     * da etapa (ou resultado final) em questão, incluindo as regras de desempate.
     * 
     * Esta função replica a classificação considerando o caso de reserva de vagas
     * 
     * @param ProcessoChamada $chamada Chamada do edital
     * @param EtapaSelProc $etapa A etapa pode ser Nula, no caso de resultado final
     * 
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsAplicaClassifComDesempate($chamada, $etapa, &$arrayCmds) {

        // removendo possíveis dados anteriores de classificação de reserva de vagas
        ClasInscricaoReserva::CLAS_addSqlsLimparClas($chamada->getPCH_ID_CHAMADA(), $arrayCmds);

        // constantes importantes
        $stInscOk = InscricaoProcesso::$SIT_INSC_OK;

        // comando inicial
        $arrayCmds [] = "SET @counter = 0";

        // definindo sql inicial
        $sqlTemp = "UPDATE tb_ipr_inscricao_processo ipr
                    SET IPR_NR_CLASSIFICACAO_CAND = @counter := @counter + 1
                    where (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
                    and  pch_id_chamada = '{$chamada->getPCH_ID_CHAMADA()}'
                    ORDER BY ";

        // recuperando macros com critérios de classificação
        $listaMacros = self::buscarMacroConfProcPorProcEtapaTp($chamada->getPRC_ID_PROCESSO(), $etapa != NULL ? $etapa->getEAP_ID_ETAPA_AVAL_PROC() : NULL, MacroConfProc::$TIPO_CRIT_CLASSIFICACAO);

        $strOrderBy = "";
        if ($listaMacros != NULL) {

            // percorrendo lista para realizar os tratamentos necessários
            foreach ($listaMacros as $macroConf) {
                // recuperando objeto
                $objMacro = $macroConf->getObjMacro();

                // carregando valor nos parametros
                MacroAbs::carregaValorParam($objMacro, $macroConf->MCP_DS_PARAMETROS);

                // adicionando order by da macro
                $strOrderBy = adicionaConteudoVirgula($strOrderBy, $objMacro->getSqlOrderByAplicaCriterio());
            }
        } else {
            // adicionando order by do critério padrão: Nota decrescente
            $strOrderBy = NotaDecrescente::getOrderBy();
        }

        // APLICANDO critérios de desempate
        // 
        // recuperando macros com critérios de desempate
        $listaMacros = self::buscarMacroConfProcPorProcEtapaTp($chamada->getPRC_ID_PROCESSO(), $etapa != NULL ? $etapa->getEAP_ID_ETAPA_AVAL_PROC() : NULL, MacroConfProc::$TIPO_CRIT_DESEMPATE);

        if ($listaMacros != NULL) {

            // percorrendo lista para realizar os tratamentos necessários
            foreach ($listaMacros as $macroConf) {
                // recuperando objeto
                $objMacro = $macroConf->getObjMacro();

                // carregando valor nos parametros
                MacroAbs::carregaValorParam($objMacro, $macroConf->MCP_DS_PARAMETROS);

                // adicionando order by da macro
                $strOrderBy = adicionaConteudoVirgula($strOrderBy, $objMacro->getSqlAddOrderByAplicaCriterio());
            }
        }

        // adicionando desempate final e incluindo order by à sql
        $strOrderBy = adicionaConteudoVirgula($strOrderBy, "IPR_NR_ORDEM_INSC");
        $sqlTemp .= $strOrderBy;


        // adicionando sql temporária
        $arrayCmds [] = $sqlTemp;


        // tratando caso de classificação em reservas de vaga, se houver
        if ($chamada->admiteReservaVagaObj()) {
            // recuperando reservas de vaga para análise
            $listaReservas = ReservaVagaChamada::buscarIdsReservaVagaPorChamada($chamada->getPCH_ID_CHAMADA(), NULL, TRUE);

            foreach (array_keys($listaReservas) as $idReservaChamada) {
                // adicionando sql de criação
                ClasInscricaoReserva::CLAS_addSqlsCriarClas($chamada->getPCH_ID_CHAMADA(), $idReservaChamada, $arrayCmds);
            }
        }
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por aplicar os critérios de seleção 
     * da etapa em questão
     * 
     * @param ProcessoChamada $chamada
     * @param EtapaSelProc $etapa
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsAplicaCritSelecao($chamada, $etapa, &$arrayCmds) {
        // recuperando macros com critérios de seleção
        $listaMacros = self::buscarMacroConfProcPorProcEtapaTp($etapa->getPRC_ID_PROCESSO(), $etapa->getEAP_ID_ETAPA_AVAL_PROC(), MacroConfProc::$TIPO_CRIT_SELECAO);

        if ($listaMacros == NULL) {
            throw new NegocioException("Critério de seleção da {$etapa->getNomeEtapa()} não configurado.");
        } else {
            // constantes importantes
            $stInscOk = InscricaoProcesso::$SIT_INSC_OK;
            $flagCdtSel = FLAG_BD_SIM;
            $flagCdtNaoSel = FLAG_BD_NAO;
            $stInscFaltaVagasElim = InscricaoProcesso::$SIT_INSC_FALTA_VAGAS_ELIMINADO;
            $dsObsNotaAutoElim = "Vagas esgotadas na {$etapa->getNomeEtapa()}.";

            // percorrendo lista para realizar os tratamentos necessários
            foreach ($listaMacros as $macroConf) {
                // recuperando objeto
                $objMacro = $macroConf->getObjMacro();

                // carregando valor nos parametros
                MacroAbs::carregaValorParam($objMacro, $macroConf->MCP_DS_PARAMETROS);

                // sql inicial
                $sqlInicial = "update tb_ipr_inscricao_processo ipr 
                        set 
                            IPR_CDT_SELECIONADO = '$flagCdtSel'
                       where
                            PCH_ID_CHAMADA = '{$etapa->getPCH_ID_CHAMADA()}'
                            and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk') ";

                // where restritivo
                $whereRestritivo = "PCH_ID_CHAMADA = '{$etapa->getPCH_ID_CHAMADA()}'
                                and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')";

                // Adicionando possíveis sqls necessárias
                $objMacro->addSqlsAplicaCriterioEtapa($chamada, $sqlInicial, $whereRestritivo, $arrayCmds);
            }

            // Eliminando demais candidatos
            $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
                            set 
                                IPR_CDT_SELECIONADO = '$flagCdtNaoSel',
                                IPR_ST_INSCRICAO = '$stInscFaltaVagasElim',
                                IPR_DS_OBS_NOTA = '$dsObsNotaAutoElim'
                           where
                                PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
                                and (IPR_CDT_SELECIONADO IS NULL or IPR_CDT_SELECIONADO != '$flagCdtSel')";
        }
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por aplicar os critérios de cadastro
     * de reserva
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsAplicaCadastroReserva($chamada, &$arrayCmds) {
        // recuperando macros com critérios de seleção - cadastro de reserva
        $listaMacros = self::buscarMacroConfProcPorProcEtapaTp($chamada->getPRC_ID_PROCESSO(), NULL, MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA);

        if ($listaMacros == NULL || count($listaMacros) != 1) {
            throw new NegocioException("Critérios de Seleção - Cadastro de Reserva não estão configurados ou há múltiplos critérios.");
        } else {

            // constantes importantes
            $stInscOk = InscricaoProcesso::$SIT_INSC_OK;
            $stInscCadReserva = InscricaoProcesso::$SIT_INSC_CAD_RESERVA;
            $stInscFaltaVagas = InscricaoProcesso::$SIT_INSC_FALTA_VAGAS_ELIMINADO;
            $flagCdtSel = FLAG_BD_SIM;
            $flagCdtNaoSel = FLAG_BD_NAO;
            $dsObsNotaFaltaVaga = "Vagas ou cadastro de reservas esgotados.";

            // recuperando macro para realizar os tratamentos necessários
            $macroConf = $listaMacros[0];

            // recuperando objeto
            $objMacro = $macroConf->getObjMacro();

            // carregando valor nos parametros
            MacroAbs::carregaValorParam($objMacro, $macroConf->MCP_DS_PARAMETROS);

            // sql inicial
            $sqlInicial = "update tb_ipr_inscricao_processo ipr 
                        set 
                            IPR_CDT_SELECIONADO = '$flagCdtSel',
                            IPR_ST_INSCRICAO = '$stInscCadReserva'
                       where
                            PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                            and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
                            and (IPR_CDT_SELECIONADO IS NULL or IPR_CDT_SELECIONADO != '$flagCdtSel')";

            // where restritivo
            $whereRestritivo = "PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
                                and (IPR_CDT_SELECIONADO IS NULL or IPR_CDT_SELECIONADO != '$flagCdtSel')";


            // Adicionando possíveis sqls necessárias
            $objMacro->addSqlsAplicaCriterio($chamada, $sqlInicial, $whereRestritivo, $arrayCmds);

            // Eliminando demais candidatos
            $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
                            set 
                                IPR_CDT_SELECIONADO = '$flagCdtNaoSel',
                                IPR_ST_INSCRICAO = '$stInscFaltaVagas',
                                IPR_DS_OBS_NOTA = '$dsObsNotaFaltaVaga'
                           where
                                PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
                                and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
                                and (IPR_CDT_SELECIONADO IS NULL or IPR_CDT_SELECIONADO != '$flagCdtSel')";
        }
    }

    public static function validaFormulaFinalProc($formula, $idProcesso) {
        try {
            // recuperando etapas
            $etapas = EtapaAvalProc::buscarEtapaAvalPorProc($idProcesso);
            if (Util::vazioNulo($etapas)) {
                return FALSE; // sem etapas!
            }
            // removendo id das etapas
            foreach ($etapas as $etapa) {
                $formula = str_replace($etapa->getIdElementoFormula(), "(1)", $formula);
            }
            return NGUtil::validaEquacao($formula);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar fórmula final do Edital.", $e);
        }
    }

    /**
     * Esta função adiciona em $arrayCmds os sqls responsáveis por aplicar a fórmula final na nota dos candidatos
     * 
     * @param ProcessoChamada $chamada
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado os demais comandos
     */
    public static function CLAS_addSqlsAplicaFormulaFinal($chamada, &$arrayCmds) {
        // recuperando nota final
        $formulaFinal = self::buscarMacroConfNotaFinal($chamada->getPRC_ID_PROCESSO());

        if ($formulaFinal == NULL) {
            throw new NegocioException("A fórmula do resultado final do edital não está configurada.");
        } else {
            // conversão das etapas da fórmula em sql
            // 
            // recuperando etapas de seleção
            $etapas = EtapaSelProc::buscarEtapaPorChamada($chamada->getPCH_ID_CHAMADA());
            if (Util::vazioNulo($etapas)) {
                throw new NegocioException("Inconsistência de dados ao aplicar fórmula final. Por favor, informe este erro ao administrador!");
            }
            // substituindo id das etapas por sqls
            $formula = $formulaFinal->MCP_DS_PARAMETROS;
            foreach ($etapas as $etapa) {
                $formula = str_replace(EtapaAvalProc::getIdElemento($etapa->getESP_NR_ETAPA_SEL()), NotasEtapaSelInsc::CLAS_getSqlNotaInscEtapa($etapa->getESP_ID_ETAPA_SEL()), $formula);
            }

            // gerando sql
            $arrayCmds [] = "update tb_ipr_inscricao_processo ipr 
                            set 
                                ipr_vl_total_nota = $formula
                            where
                                pch_id_chamada = '{$chamada->getPCH_ID_CHAMADA()}'";
        }
    }

    /**
     * Essa função conta a quantidade de macros de acordo com os parÂmetros especificados.
     * 
     * Note: Se $tpMacro é não nulo e $idEtapaAval é nulo, então é incluído explicitamente a condição
     * EAP_ID_ETAPA_AVAL_PROC IS NULL na sql de busca
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @param char $tpMacro
     * @return int Quantidade de itens
     * @throws NegocioException
     */
    public static function contarMacroPorProcEtapa($idProcesso, $idEtapaAval = NULL, $tpMacro = NULL) {
        try {

            // verificando restricoes da funcao
            if (Util::vazioNulo($idProcesso)) {
                return NULL;
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select count(*) as cont
                    from
                    tb_mcp_macro_conf_proc
                    where `PRC_ID_PROCESSO` = '$idProcesso'";

            if ($idEtapaAval != NULL) {
                $sql .= " and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";
            } else {
                if ($tpMacro != NULL) {
                    $sql .= " and EAP_ID_ETAPA_AVAL_PROC IS NULL";
                }
            }

            if ($tpMacro != NULL) {
                $sql .= " and MCP_TP_MACRO = '$tpMacro'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar macros do processo.", $e);
        }
    }

    /**
     * Essa funçao valida se é possível cadastrar/alterar uma macro.
     * 
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @param int $tipoMacro
     * @param int $idTipoMacro
     * @param string|array $paramChave String codificada ou array na forma [IdParam => vlParam]
     * @param boolean $edicao
     * @param int $idMacroConfProc
     * @return array na forma: [validou, msgErro]
     * @throws NegocioException
     */
    public static function validarCadastroMacro($idProcesso, $idEtapaAval, $tipoMacro, $idTipoMacro, $paramChave = NULL, $edicao = FALSE, $idMacroConfProc = NULL) {
        try {

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // verificando se existe alguma macro com os parametros informados
            $sql = "select count(*) as cont,
                    MCP_ID_MACRO_CONF_PROC
                    from tb_mcp_macro_conf_proc
                    where
                    PRC_ID_PROCESSO = '$idProcesso'
                    and MCP_TP_MACRO = '$tipoMacro'    
                    and MCP_DS_MACRO = '$idTipoMacro'";

            if ($idEtapaAval != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL) {
                $sql .= " and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";
            } else {
                $sql .= " and EAP_ID_ETAPA_AVAL_PROC IS NULL";
            }

            // tratando parâmetros chaves
            // 
            // instanciando macro para ver se possui parametros chaves
            $objMacro = MacroAbs::instanciaMacro($tipoMacro, $idTipoMacro, MacroAbs::montaVetParamExt($idProcesso, $idEtapaAval));
            //
            // gerando string de busca
            if (!is_array($paramChave)) {
                $arrayParamChave = self::decodificaParamBusca($paramChave);
            } else {
                $arrayParamChave = $paramChave;
            }

//            print_r("paramChave: ");
//            print_r($paramChave);
//            print_r("\nArrayparamChave: ");
//            print_r($arrayParamChave);
//            exit;

            $strBusca = "";
            $strBuscaNegada = "";
            // percorrendo parametros para definir os chaves
            foreach ($objMacro->getListaParam() as $param) {
                if ($param->isChave()) {
                    if (isset($arrayParamChave[$param->getId()])) {
                        $strBusca .= "%" . $param->getId() . self::$SEPARADOR_VALOR . $arrayParamChave[$param->getId()] . "%";
                    } else {
                        $strBuscaNegada .= "%" . $param->getId() . "%";
                    }
                }
            }
            //
            // incluindo na busca
            if ($strBusca != "") {
                $sql .= " and MCP_DS_PARAMETROS like '$strBusca'";
            }
            if ($strBuscaNegada != "") {
                $sql .= " and MCP_DS_PARAMETROS not like '$strBuscaNegada'";
            }

//            print_r($sql);
//            exit;
//            
            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // recuperando linha
            $dados = ConexaoMysql::getLinha($resp);

            // recuperando dados e analisando
            $quant = $dados['cont'];
            $idCatBD = $dados['MCP_ID_MACRO_CONF_PROC'];
            $validou = $quant == 0;

            // caso especifico de edicao
            if (!$validou && $edicao) {
                // se tiver 1, e for a propria categoria, esta valendo.
                $validou = $quant == 1 && $idMacroConfProc != NULL && $idCatBD == $idMacroConfProc;
            }

            // validando caso do cadastro de reserva
            if (!$edicao && $tipoMacro == MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA && self::contarMacroPorProcEtapa($idProcesso, NULL, MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA) > 0) {
                return array(FALSE, "Edital já possui um critério de seleção - Cadastro de Reserva");
            }

            return array($validou, "");
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar cadastro de Macro do Edital.", $e);
        }
    }

    private static function decodificaParamBusca($paramChave) {
        $ret = array();

        $arrayParamVal = Util::vazioNulo($paramChave) ? array() : explode(MacroConfProc::$SEPARADOR_PARAM, $paramChave);

        foreach ($arrayParamVal as $strIdVal) {
            $temp = explode(MacroConfProc::$SEPARADOR_VALOR, $strIdVal);
            if (!isset($temp[0]) || !isset($temp[1]) || Util::vazioNulo($temp[1])) {
                continue;
            }
            $ret[$temp[0]] = $temp[1];
        }
        return $ret;
    }

    public static function buscarMacroConfProcPorId($idMacroConfProc, $idProcesso = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        MCP_ID_MACRO_CONF_PROC,
                        PRC_ID_PROCESSO,
                        MCP_DS_MACRO,
                        MCP_TP_MACRO,
                        MCP_ORDEM_APLICACAO,
                        MCP_DS_PARAMETROS,
                        EAP_ID_ETAPA_AVAL_PROC
                    from
                        tb_mcp_macro_conf_proc
                    where
                        MCP_ID_MACRO_CONF_PROC = '$idMacroConfProc'";

            // validaçao de processo
            if ($idProcesso != NULL) {
                $sql .= " and PRC_ID_PROCESSO = '$idProcesso'";
            }


//            print_r($sql);
            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                // disparando exceçao
                throw new NegocioException("Macro de Configuração não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $macroTemp = new MacroConfProc($dados['MCP_ID_MACRO_CONF_PROC'], $dados['PRC_ID_PROCESSO'], $dados['MCP_DS_MACRO'], $dados['MCP_TP_MACRO'], $dados['MCP_ORDEM_APLICACAO'], $dados['MCP_DS_PARAMETROS'], $dados['EAP_ID_ETAPA_AVAL_PROC']);


            // retornando
            return $macroTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar macro do processo.", $e);
        }
    }

    public static function buscarMacroConfNotaFinal($idProcesso) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $tpNotaFinal = self::$TIPO_ESP_FORMULA_FINAL;

            $sql = "select 
                        MCP_ID_MACRO_CONF_PROC,
                        PRC_ID_PROCESSO,
                        MCP_DS_MACRO,
                        MCP_TP_MACRO,
                        MCP_ORDEM_APLICACAO,
                        MCP_DS_PARAMETROS,
                        EAP_ID_ETAPA_AVAL_PROC
                    from
                        tb_mcp_macro_conf_proc
                    where
                        PRC_ID_PROCESSO = '$idProcesso'
                        and MCP_TP_MACRO = '$tpNotaFinal'";


//            print_r($sql);
            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                return NULL; // nao existe a macro 
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $macroTemp = new MacroConfProc($dados['MCP_ID_MACRO_CONF_PROC'], $dados['PRC_ID_PROCESSO'], $dados['MCP_DS_MACRO'], $dados['MCP_TP_MACRO'], $dados['MCP_ORDEM_APLICACAO'], $dados['MCP_DS_PARAMETROS'], $dados['EAP_ID_ETAPA_AVAL_PROC']);

            // retornando
            return $macroTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar macro de nota final do processo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval Se $idEtapaAval é nulo, então é incluído explicitamente na sql a instrução IS NULL
     * @param char $tipoMacro
     * @return \MacroConfProc
     * @throws NegocioException
     */
    public static function buscarMacroConfProcPorProcEtapaTp($idProcesso, $idEtapaAval = NULL, $tipoMacro = NULL) {
        try {
            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        MCP_ID_MACRO_CONF_PROC,
                        PRC_ID_PROCESSO,
                        MCP_DS_MACRO,
                        MCP_TP_MACRO,
                        MCP_ORDEM_APLICACAO,
                        MCP_DS_PARAMETROS,
                        EAP_ID_ETAPA_AVAL_PROC
                    from
                        tb_mcp_macro_conf_proc
                    where
                        PRC_ID_PROCESSO = '$idProcesso'";

            if ($idEtapaAval != NULL) {
                $sql.= " and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";
            } else {
                $sql.= " and EAP_ID_ETAPA_AVAL_PROC IS NULL";
            }

            if ($tipoMacro != NULL) {
                $sql .= " and MCP_TP_MACRO = '$tipoMacro'";
            }

            $sql .= " order by MCP_ORDEM_APLICACAO";

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

                $macroTemp = new MacroConfProc($dados['MCP_ID_MACRO_CONF_PROC'], $dados['PRC_ID_PROCESSO'], $dados['MCP_DS_MACRO'], $dados['MCP_TP_MACRO'], $dados['MCP_ORDEM_APLICACAO'], $dados['MCP_DS_PARAMETROS'], $dados['EAP_ID_ETAPA_AVAL_PROC']);

                //adicionando no vetor
                $vetRetorno[$i] = $macroTemp;
            }


            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar Macros de Configuração do processo.", $e);
        }
    }

    /**
     * Funcao que edita a ordenaçao das macros de configuração de uma etapa.
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @param string $novaOrdenacao - String na forma: [codCat1:novaOrdem1;codCat2:novaOrdem2;...]
     * @throws NegocioException
     */
    public static function editarOrdensMacroConfProc($idProcesso, $idEtapaAval, $novaOrdenacao) {
        try {
            $buscaEtapa = $idEtapaAval != NULL && $idEtapaAval != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL;
            if ($buscaEtapa) {
                // buscando etapa
                $etapa = buscarEtapaAvalPorIdCT($idEtapaAval);

                // verificando se pode alterar a etapa
                if (!$etapa->podeAlterar()) {
                    throw new NegocioException("Etapa não pode ser alterada.");
                }
            } else {
                $processo = Processo::buscarProcessoPorId($idProcesso);

                // validando edicao de processo
                if (!$processo->permiteEdicao()) {
                    throw new NegocioException("Processo Finalizado.");
                }
            }

            // destrinchando string
            $vetAtu = array(); // vetor na forma [idMacro => novaOrdem]
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

            foreach ($vetAtu as $idMacro => $ordem) {
                // verificando validacao
                if (Util::vazioNulo($idMacro) || Util::vazioNulo($ordem)) {
                    throw new NegocioException("Parâmetros incorretos.");
                }

                //montando sql de atualizacao
                $sql = "update tb_mcp_macro_conf_proc 
                        set MCP_ORDEM_APLICACAO = '$ordem'
                    where MCP_ID_MACRO_CONF_PROC = '$idMacro'
                          and PRC_ID_PROCESSO = '$idProcesso'";

                // inserindo no array
                $vetSql [] = $sql;
            }

//            print_r($vetSql);
//            exit;
//            
//            
            // tratando forma de reset: provável classificação
            if (!$buscaEtapa) {
                // criando array de comandos e adicionando reset
                $vetSql [] = EtapaSelProc::getStrSqlClassifPenPorEtAval($idEtapaAval);
            } else {
                // criando array de comandos e adicionando reset
                $vetSql [] = EtapaSelProc::getStrSqlClassifPenUltEtapa($idProcesso);
            }

            $conexao->execTransacaoArray($vetSql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar ordenação das Macros de Configuração do Processo.", $e);
        }
    }

    private function trataDadosBanco($listaParam) {

        // verificando parametros da macro para montar url de parametros
        $objMacro = MacroAbs::instanciaMacro($this->MCP_TP_MACRO, $this->MCP_DS_MACRO, MacroAbs::montaVetParamExt($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC));
        foreach ($objMacro->getListaParam() as $param) {
            // parametro obrigatorio ok?
            if ($param->isObrigatorio()) {
                if (!isset($listaParam[$param->getId()]) || Util::vazioNulo($listaParam[$param->getId()])) {
                    throw new NegocioException("Parâmetros da Macro inconsistentes!");
                }
            }
        }

        // montando url de parâmetros
        if (!Util::vazioNulo($listaParam)) {
            $temp = "";
            $i = 0;
            foreach (array_keys($listaParam) as $idParam) {

                if (!Util::vazioNulo($listaParam[$idParam])) {
                    $temp .= $idParam . self::$SEPARADOR_VALOR . $listaParam[$idParam] . self::$SEPARADOR_PARAM;
                }
                $i++;
            }
            $this->MCP_DS_PARAMETROS = NGUtil::trataCampoStrParaBD(substr_replace($temp, '', -1));
        } else {
            $this->MCP_DS_PARAMETROS = 'NULL';
        }

        // preparando parâmetros
        $this->MCP_DS_MACRO = NGUtil::trataCampoStrParaBD($this->MCP_DS_MACRO);
        $this->MCP_TP_MACRO = NGUtil::trataCampoStrParaBD($this->MCP_TP_MACRO);

        if ($this->EAP_ID_ETAPA_AVAL_PROC == MacroConfProc::$ID_ETAPA_RESULTADO_FINAL) {
            $this->EAP_ID_ETAPA_AVAL_PROC = "NULL";
        }
    }

    public function criarMacroConfProc($listaParam) {
        try {

            if ($this->EAP_ID_ETAPA_AVAL_PROC != MacroConfProc::$ID_ETAPA_RESULTADO_FINAL) {
                // buscando etapa
                $etapa = buscarEtapaAvalPorIdCT($this->EAP_ID_ETAPA_AVAL_PROC);

                // verificando se pode alterar a etapa
                if (!$etapa->podeAlterar()) {
                    throw new NegocioException("Etapa não pode ser alterada.");
                }
                $sqlRestOrdemEtapa = "EAP_ID_ETAPA_AVAL_PROC = $this->EAP_ID_ETAPA_AVAL_PROC";
            } else {
                // validando edicao de última etapa do processo
                if (!EtapaAvalProc::podeAlterarUltimaEtapa($this->PRC_ID_PROCESSO)) {
                    throw new NegocioException("Dados não podem ser alterados.");
                }
                $sqlRestOrdemEtapa = "EAP_ID_ETAPA_AVAL_PROC IS NULL";
            }

            // validar macro
            $validou = self::validarCadastroMacro($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC, $this->MCP_TP_MACRO, $this->MCP_DS_MACRO, $listaParam);
            if (!$validou[0]) {
                if (!Util::vazioNulo($validou[1])) {
                    throw new NegocioException($validou[1]);
                } else {
                    throw new NegocioException("Já existe uma Macro com os parâmetros informados.");
                }
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando campos
            $this->trataDadosBanco($listaParam);

            //montando sql de criação
            $sql = "insert into tb_mcp_macro_conf_proc(`PRC_ID_PROCESSO`, MCP_DS_MACRO, `MCP_TP_MACRO`, `MCP_ORDEM_APLICACAO`, MCP_DS_PARAMETROS, `EAP_ID_ETAPA_AVAL_PROC`)
            values('$this->PRC_ID_PROCESSO', $this->MCP_DS_MACRO, $this->MCP_TP_MACRO, (
                    SELECT COALESCE(MAX(`MCP_ORDEM_APLICACAO`),0) + 1
                    FROM (
                    SELECT *
                    FROM tb_mcp_macro_conf_proc) AS temp
                    WHERE `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO' and $sqlRestOrdemEtapa and MCP_TP_MACRO = $this->MCP_TP_MACRO), $this->MCP_DS_PARAMETROS, $this->EAP_ID_ETAPA_AVAL_PROC)";

            // tratando forma de reset
            if ($this->EAP_ID_ETAPA_AVAL_PROC != "NULL") {
                // criando array de comandos e adicionando reset
                $arrayCmds = array($sql, EtapaSelProc::getStrSqlClassifPenPorEtAval($this->EAP_ID_ETAPA_AVAL_PROC));
            } else {
                // criando array de comandos e adicionando reset
                $arrayCmds = array($sql, EtapaSelProc::getStrSqlClassifPenUltEtapa($this->PRC_ID_PROCESSO));
            }

            // executando no banco
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao cadastrar Macro de Configuração do Processo.", $e);
        }
    }

    /**
     * 
     * @param Processo $processo
     * @param string $formula
     * @throws NegocioException
     */
    public static function atualizarFormulaFinalProc($processo, $formula) {
        try {
            // validando edicao de última etapa do processo
            if (!Processo::permiteComporNotaFinal($processo)) {
                throw new NegocioException("Dados não podem ser alterados.");
            }

            // validando formula
            if (!self::validaFormulaFinalProc($formula, $processo->getPRC_ID_PROCESSO())) {
                throw new NegocioException("Fórmula Final inválida!");
            }
            // removendo espaços
            $formula = preg_replace('/\s+/', '', $formula);

            // verificando se já existe a fórmula
            $macroForm = self::buscarMacroConfProcPorProcEtapaTp($processo->getPRC_ID_PROCESSO(), NULL, self::$TIPO_ESP_FORMULA_FINAL);

            $cmds = array();

            if (Util::vazioNulo($macroForm)) {
                // caso de não existir
                // 
                // preparando parâmetros
                $dsMacro = NGUtil::trataCampoStrParaBD(self::$NM_TIPO_ESP_FORMULA_FINAL);
                $tpMacro = NGUtil::trataCampoStrParaBD(self::$TIPO_ESP_FORMULA_FINAL);
                $dsParametros = NGUtil::trataCampoStrParaBD($formula);

                //montando sql de criação
                $cmds [] = "insert into tb_mcp_macro_conf_proc(`PRC_ID_PROCESSO`, MCP_DS_MACRO, `MCP_TP_MACRO`, `MCP_ORDEM_APLICACAO`, MCP_DS_PARAMETROS)
                        values('{$processo->getPRC_ID_PROCESSO()}', $dsMacro, $tpMacro, '1', $dsParametros)";
            } else {
                // caso de atualização
                // 
                $dsParametros = NGUtil::trataCampoStrParaBD($formula);
                //montando sql de atualização
                $cmds [] = "update tb_mcp_macro_conf_proc set MCP_DS_PARAMETROS = $dsParametros
                        where MCP_ID_MACRO_CONF_PROC = '{$macroForm[0]->MCP_ID_MACRO_CONF_PROC}'";
            }

            // recuperando sql para resetar classificação da última etapa
            $cmds[] = EtapaSelProc::getStrSqlClassifPenUltEtapa($processo->getPRC_ID_PROCESSO());

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // executando no banco
            $conexao->execTransacaoArray($cmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar Fórmula Final do Processo.", $e);
        }
    }

    public function editarMacroConfProc($listaParam) {
        try {

            if ($this->EAP_ID_ETAPA_AVAL_PROC != NULL) {
                // buscando etapa
                $etapa = buscarEtapaAvalPorIdCT($this->EAP_ID_ETAPA_AVAL_PROC);

                // verificando se pode alterar a etapa
                if (!$etapa->podeAlterar()) {
                    throw new NegocioException("Etapa não pode ser alterada.");
                }
            } else {
                // validando edicao de última etapa do processo
                if (!EtapaAvalProc::podeAlterarUltimaEtapa($this->PRC_ID_PROCESSO)) {
                    throw new NegocioException("Dados não podem ser alterados.");
                }
            }

            // validar macro
            $validou = self::validarCadastroMacro($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC, $this->MCP_TP_MACRO, $this->MCP_DS_MACRO, $listaParam, TRUE, $this->MCP_ID_MACRO_CONF_PROC);
            if (!$validou[0]) {
                if (!Util::vazioNulo($validou[1])) {
                    throw new NegocioException($validou[1]);
                } else {
                    throw new NegocioException("Já existe uma Macro com os parâmetros informados.");
                }
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // tratando campos
            $this->trataDadosBanco($listaParam);

            //montando sql de atualizacao
            $sql = "update tb_mcp_macro_conf_proc 
                        set MCP_DS_PARAMETROS = $this->MCP_DS_PARAMETROS
                    where MCP_ID_MACRO_CONF_PROC = '$this->MCP_ID_MACRO_CONF_PROC'";

            // tratando forma de reset
            if ($this->EAP_ID_ETAPA_AVAL_PROC != "NULL") {
                // criando array de comandos e adicionando reset
                $arrayCmds = array($sql, EtapaSelProc::getStrSqlClassifPenPorEtAval($this->EAP_ID_ETAPA_AVAL_PROC));
            } else {
                // criando array de comandos e adicionando reset
                $arrayCmds = array($sql, EtapaSelProc::getStrSqlClassifPenUltEtapa($this->PRC_ID_PROCESSO));
            }

            // executando no banco
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar Macro de Configuração do Processo.", $e);
        }
    }

    public function excluirMacroConfProc() {
        try {

            if ($this->EAP_ID_ETAPA_AVAL_PROC != NULL) {
                // buscando etapa
                $etapa = buscarEtapaAvalPorIdCT($this->EAP_ID_ETAPA_AVAL_PROC);

                // verificando se pode alterar a etapa
                if (!$etapa->podeAlterar()) {
                    throw new NegocioException("Etapa não pode ser alterada.");
                }
            } else {
                $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);

                // validando edicao de processo
                if (!$processo->permiteEdicao()) {
                    throw new NegocioException("Processo Finalizado.");
                }

                // atualizando etapa
                $this->EAP_ID_ETAPA_AVAL_PROC = MacroConfProc::$ID_ETAPA_RESULTADO_FINAL;
            }

            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // array de comandos
            $arrayCmds = array();

            //montando sql de exclusao
            $sql = "delete from tb_mcp_macro_conf_proc
                    where PRC_ID_PROCESSO = '$this->PRC_ID_PROCESSO'
                    and MCP_ID_MACRO_CONF_PROC = '$this->MCP_ID_MACRO_CONF_PROC'";

            $etapaNull = $this->EAP_ID_ETAPA_AVAL_PROC == MacroConfProc::$ID_ETAPA_RESULTADO_FINAL;
            if (!$etapaNull) {
                $sql .= " and EAP_ID_ETAPA_AVAL_PROC = '$this->EAP_ID_ETAPA_AVAL_PROC'";
            } else {
                $sql .= " and EAP_ID_ETAPA_AVAL_PROC IS NULL";
            }
            $arrayCmds [] = $sql;

            // montando sql de reordenacao
            $temp = $this->getSqlReordenacaoAuto($etapaNull);
            $arrayCmds[] = $temp[0];
            $arrayCmds[] = $temp[1];

            // tratando forma de reset
            if (!$etapaNull) {
                // criando array de comandos e adicionando reset
                $arrayCmds [] = EtapaSelProc::getStrSqlClassifPenPorEtAval($this->EAP_ID_ETAPA_AVAL_PROC);
            } else {
                // criando array de comandos e adicionando reset
                $arrayCmds [] = EtapaSelProc::getStrSqlClassifPenUltEtapa($this->PRC_ID_PROCESSO);
            }

            // persistindo no BD
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir Macro de Configuração do Processo.", $e);
        }
    }

    private function getSqlReordenacaoAuto($etapaNull) {
        $sql1 = "SET @counter = 0";

        $sql2 = "UPDATE tb_mcp_macro_conf_proc
                    SET MCP_ORDEM_APLICACAO = @counter := @counter + 1
                    where PRC_ID_PROCESSO = '$this->PRC_ID_PROCESSO'
                    and MCP_TP_MACRO = '$this->MCP_TP_MACRO'";

        if (!$etapaNull) {
            $sql2.= " and EAP_ID_ETAPA_AVAL_PROC = '$this->EAP_ID_ETAPA_AVAL_PROC'";
        } else {
            $sql2.= " and EAP_ID_ETAPA_AVAL_PROC IS NULL";
        }

        $sql2 .= " ORDER BY MCP_ORDEM_APLICACAO";

        return array($sql1, $sql2);
    }

    public static function getSqlExcluirConfProc($idProcesso, $idEtapaAval = NULL) {
        $sql = "delete from tb_mcp_macro_conf_proc
                    where PRC_ID_PROCESSO = '$idProcesso'";

        if ($idEtapaAval == NULL) {
            $sql .= " and EAP_ID_ETAPA_AVAL_PROC IS NULL";
        } else {
            $sql .= " and EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";
        }

        return $sql;
    }

    public function temParametros() {
        return !Util::vazioNulo($this->MCP_DS_PARAMETROS);
    }

    public function getNomeObjMacro() {
        if (Util::vazioNulo($this->MCP_DS_MACRO) || Util::vazioNulo($this->MCP_TP_MACRO)) {
            return "";
        }
        $obj = $this->getObjMacro();
        return $obj->getNmFantasia();
    }

    public function getIdObjMacro() {
        if (Util::vazioNulo($this->MCP_DS_MACRO) || Util::vazioNulo($this->MCP_TP_MACRO)) {
            return "";
        }
        $obj = $this->getObjMacro();
        return $obj->getIdMacro();
    }

    public function getStrParametros() {
        if ($this->strParametros == NULL) {
            $this->strParametros = Util::$STR_CAMPO_VAZIO;

            // carregando parametros
            if ($this->temParametros()) {
                // recuperando objeto
                $objMacro = $this->getObjMacro();

                // carregando valor nos parametros
                MacroAbs::carregaValorParam($objMacro, $this->MCP_DS_PARAMETROS);

                // carregando parametros
                $temp = "";
                $i = 0;
                $qt = $objMacro->getQtdeParametros();
                foreach ($objMacro->getListaParam() as $param) {
                    $temp .= $param->getStrParametro();
                    if ($i < $qt - 1) {
                        $temp .= MacroConfProc::$SEPARADOR_PARAM . " ";
                    }
                    $i++;
                }
                $this->strParametros = $temp;
            }
        }

        return $this->strParametros;
    }

    /**
     * Esta função tenta criar uma instância do objeto representante da Macro
     * 
     * @return MacroAbs
     * @throws NegocioException
     */
    private function getObjMacro() {
        if ($this->objMacro == NULL) {
            if (Util::vazioNulo($this->MCP_DS_MACRO) || Util::vazioNulo($this->MCP_TP_MACRO)) {
                throw new NegocioException("Impossível recuperar objeto representante da Macro.");
            }
            $this->objMacro = MacroAbs::instanciaMacro($this->MCP_TP_MACRO, $this->MCP_DS_MACRO, MacroAbs::montaVetParamExt($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC));
        }
        return $this->objMacro;
    }

    /* GET FIELDS FROM TABLE */

    function getMCP_ID_MACRO_CONF_PROC() {
        return $this->MCP_ID_MACRO_CONF_PROC;
    }

    /* End of get MCP_ID_MACRO_CONF_PROC */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getMCP_DS_MACRO() {
        return $this->MCP_DS_MACRO;
    }

    /* End of get MCP_DS_MACRO */

    function getMCP_TP_MACRO() {
        return $this->MCP_TP_MACRO;
    }

    /* End of get MCP_TP_MACRO */

    function getMCP_ORDEM_APLICACAO() {
        return $this->MCP_ORDEM_APLICACAO;
    }

    /* End of get MCP_ORDEM_APLICACAO */

    function getMCP_DS_PARAMETROS() {
        return $this->MCP_DS_PARAMETROS;
    }

    /* End of get MCP_DS_PARAMETROS */

    function getEAP_ID_ETAPA_AVAL_PROC() {
        return $this->EAP_ID_ETAPA_AVAL_PROC;
    }

    /* End of get EAP_ID_ETAPA_AVAL_PROC */



    /* SET FIELDS FROM TABLE */

    function setMCP_ID_MACRO_CONF_PROC($value) {
        $this->MCP_ID_MACRO_CONF_PROC = $value;
    }

    /* End of SET MCP_ID_MACRO_CONF_PROC */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setMCP_DS_MACRO($value) {
        $this->MCP_DS_MACRO = $value;
    }

    /* End of SET MCP_DS_MACRO */

    function setMCP_TP_MACRO($value) {
        $this->MCP_TP_MACRO = $value;
    }

    /* End of SET MCP_TP_MACRO */

    function setMCP_ORDEM_APLICACAO($value) {
        $this->MCP_ORDEM_APLICACAO = $value;
    }

    /* End of SET MCP_ORDEM_APLICACAO */

    function setMCP_DS_PARAMETROS($value) {
        $this->MCP_DS_PARAMETROS = $value;
    }

    /* End of SET MCP_DS_PARAMETROS */

    function setEAP_ID_ETAPA_AVAL_PROC($value) {
        $this->EAP_ID_ETAPA_AVAL_PROC = $value;
    }

    /* End of SET EAP_ID_ETAPA_AVAL_PROC */
}

?>
