<?php

/**
 * Classe que implementa a funcao de Múltiplo da quantidade de vagas
 * 
 * Ela pode ser usada como Criterio de Seleção ou cadastro de reserva
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";

class MultiploQtdeVagas extends MacroAbs implements MacroCritSelecao, MacroCritCadReserva {

    private static $paramValor = "vlMultiplicador";

    public function __construct($tpMacro, $paramExt = NULL) {
        parent::__construct($tpMacro, $paramExt);

        $param = new ParamMacro(self::$paramValor, ParamMacro::$TIPO_DECIMAL, "Multiplicador:");
        // setando validador extra
        $param->addValidadorExtra(ParamMacro::$VALIDADOR_MIN_1);

        $this->parametros = array(self::$paramValor => $param);
        $this->qtParametros = count($this->parametros);
    }

    public function getNmFantasia() {
        return "Múltiplo das Vagas";
    }

    public function getIdMacro() {
        return "multiploQtdeVagas";
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

    public function addSqlsAplicaCriterioEtapa($chamada, $sqlInicial, $whereRestritivo, &$arrayCmds) {
        return $this->addSqlsAplicaCriterio($chamada, $sqlInicial, $whereRestritivo, $arrayCmds);
    }

    /**
     * 
     * @param ProcessoChamada $chamada
     * @param string $sqlInicial
     * @param array $arrayCmds
     */
    public function addSqlsAplicaCriterio($chamada, $sqlInicial, $whereRestritivo, &$arrayCmds) {
        // recuperando multiplicador de vagas
        $multiplicadorVagas = $this->getValorParam(self::$paramValor);

        // carregando reservas de vaga, no caso de admissão
        if ($chamada->admiteReservaVagaObj()) {
            // carregando vagas de reserva
            $listaReservaVaga = buscarReservaVagaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaVagaChamada::getFlagReservaAtiva());
        }

        // polos e área de atuação ao mesmo tempo
        if ($chamada->admitePoloObj() && $chamada->admiteAreaAtuacaoObj()) {
            // recuperando dados de polo e área
            $polosAreasCham = buscarPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA());

            if ($chamada->admiteReservaVagaObj()) {
                // carregando vagas de polo e área
                $reservaPoloArea = buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO_AREA);

                //percorrendo áreas e polos e executando sql
                foreach ($polosAreasCham as $poloArea) {
                    // contador para a quantidade de reservas de vaga
                    $contaReserva = 0;

                    // percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {

                        $qtReserva = ReservaPoloArea::getValorIndiceBusca($reservaPoloArea, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_POLO_AREA, $poloArea->getPOL_ID_POLO(), $poloArea->ARC_ID_SUBAREA_CONH), $reserva->getRVC_ID_RESERVA_CHAMADA());
                        $contaReserva += $qtReserva;

                        $qtCadReserva = round($multiplicadorVagas * $qtReserva);

                        // sql para reservas de vagas
                        $arrayCmds [] = $sqlInicial . "
                                and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
                                and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
                                and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
                                        and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
                                        and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                    }

                    // sql para público geral
                    $qtReserva = $qtVagasPolo = $contaReserva;
                    $qtCadReserva = round($multiplicadorVagas * $qtReserva);

                    $arrayCmds [] = $sqlInicial . "
                                and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
                                and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
                                and RVC_ID_RESERVA_CHAMADA IS NULL
                                and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
                                        and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
                                        and RVC_ID_RESERVA_CHAMADA IS NULL
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                }//
            //
            } else { // SEM RESERVA DE VAGAS
                //percorrendo áreas e polos e executando sql
                foreach ($polosAreasCham as $poloArea) {

                    $qtCadReserva = round($multiplicadorVagas * $poloArea->getPAC_QT_VAGAS());

                    $arrayCmds [] = $sqlInicial . "
                                and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
                                and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
                                and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and IPR_ID_POLO_SELECIONADO = '{$poloArea->getPOL_ID_POLO()}'
                                        and AAC_ID_AREA_CHAMADA = '{$poloArea->getAAC_ID_AREA_CHAMADA()}'
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                }
            }
        } elseif ($chamada->admitePoloObj()) { // APENAS POLO
            // recuperar polos da chamada
            $polosCham = PoloChamada::buscarPoloVagasPorChamada($chamada->getPCH_ID_CHAMADA(), PoloChamada::getFlagPoloAtivo());


            if ($chamada->admiteReservaVagaObj()) {
                // carregando vagas do polo
                $reservaPolo = buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_POLO);

                //percorrendo polos e executando sql
                foreach ($polosCham as $idPoloCham => $qtVagasPolo) {
                    // contador para a quantidade de reservas de vaga
                    $contaReserva = 0;

                    // percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {

                        $qtReserva = ReservaPoloArea::getValorIndiceBusca($reservaPolo, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_POLO, $idPoloCham), $reserva->getRVC_ID_RESERVA_CHAMADA());
                        $contaReserva += $qtReserva;

                        $qtCadReserva = round($multiplicadorVagas * $qtReserva);

                        // sql para reservas de vagas
                        $arrayCmds [] = $sqlInicial . "
                                and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
                                and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
                                        and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                    }

                    // sql para público geral
                    $qtReserva = $qtVagasPolo = $contaReserva;
                    $qtCadReserva = round($multiplicadorVagas * $qtReserva);

                    $arrayCmds [] = $sqlInicial . "
                                and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
                                and RVC_ID_RESERVA_CHAMADA IS NULL
                                and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
                                        and RVC_ID_RESERVA_CHAMADA IS NULL
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                }//
            //
            } else { // SEM RESERVA DE VAGAS
                //percorrendo polos e executando sql
                foreach ($polosCham as $idPoloCham => $qtVagas) {

                    $qtCadReserva = round($multiplicadorVagas * $qtVagas);

                    $arrayCmds [] = $sqlInicial . "
                                and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
                                and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and IPR_ID_POLO_SELECIONADO = '$idPoloCham'
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                }
            }
        } elseif ($chamada->admiteAreaAtuacaoObj()) {  // APENAS ÁREA DE ATUAÇÃO
            // recuperar areas de atuacao
            $areasAtuCham = AreaAtuChamada::buscarAreaAtuCompPorChamada($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva());

            if ($chamada->admiteReservaVagaObj()) {
                // carregando vagas da área
                $reservaArea = buscarReservaPoloAreaPorChamadaCT($chamada->getPCH_ID_CHAMADA(), ReservaPoloArea::$RESERVA_AREA);

                //percorrendo áreas e executando sql
                foreach ($areasAtuCham as $areaChamada) {
                    // contador para a quantidade de reservas de vaga
                    $contaReserva = 0;

                    // percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {

                        $qtReserva = ReservaPoloArea::getValorIndiceBusca($reservaArea, ReservaPoloArea::getIndiceBusca(ReservaPoloArea::$RESERVA_AREA, NULL, $areaChamada->getARC_ID_SUBAREA_CONH()), $reserva->getRVC_ID_RESERVA_CHAMADA());
                        $contaReserva += $qtReserva;

                        $qtCadReserva = round($multiplicadorVagas * $qtReserva);

                        // sql para reservas de vagas
                        $arrayCmds [] = $sqlInicial . "
                                and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
                                and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
                                        and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                    }

                    // sql para público geral
                    $qtReserva = $areaChamada->getAAC_QT_VAGAS() - $contaReserva;
                    $qtCadReserva = round($multiplicadorVagas * $qtReserva);

                    $arrayCmds [] = $sqlInicial . "
                                and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
                                and RVC_ID_RESERVA_CHAMADA IS NULL
                                and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
                                        and RVC_ID_RESERVA_CHAMADA IS NULL
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                }
                //
            //
                
            } else { // SEM RESERVA DE VAGAS
                //percorrendo areas e executando sql
                foreach ($areasAtuCham as $areaChamada) {

                    $qtCadReserva = round($multiplicadorVagas * $areaChamada->getAAC_QT_VAGAS());

                    // sql para reservas de vagas
                    $arrayCmds [] = $sqlInicial . "
                                    and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
                                    and IPR_ID_INSCRICAO in (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        (select 
                                            IPR_ID_INSCRICAO
                                        from
                                            tb_ipr_inscricao_processo
                                        where
                                        $whereRestritivo
                                                and AAC_ID_AREA_CHAMADA = '{$areaChamada->getAAC_ID_AREA_CHAMADA()}'
                                        order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                        limit 0 , $multiplicadorVagas) tmp)";
                }
            }
        } else { // Sem polo e sem área
            if ($chamada->admiteReservaVagaObj()) {
                // contador para a quantidade de reservas de vaga
                $contaReserva = 0;

                // percorrendo reservas de vagas
                foreach ($listaReservaVaga as $reserva) {

                    $qtReserva = $reserva->getRVC_QT_VAGAS_RESERVADAS();
                    $contaReserva += $qtReserva;

                    $qtCadReserva = round($multiplicadorVagas * $qtReserva);

                    // sql para reservas de vagas
                    $arrayCmds [] = $sqlInicial . "
                            and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                            and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp) ";
                }


                // sql para público geral
                $qtReserva = $qtVagasArea = $contaReserva;
                $qtCadReserva = round($multiplicadorVagas * $qtReserva);

                $arrayCmds [] = $sqlInicial . "
                            and RVC_ID_RESERVA_CHAMADA IS NULL
                            and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                        and RVC_ID_RESERVA_CHAMADA IS NULL
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp) ";
                //
            //
            } else {  // SEM RESERVA DE VAGAS
                $qtCadReserva = round($multiplicadorVagas * $chamada->getPCH_QT_VAGAS());

                // selecionando pelo numero total de vagas
                $arrayCmds [] = $sqlInicial . "
                            and IPR_ID_INSCRICAO in (select 
                                    IPR_ID_INSCRICAO
                                from
                                    (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        tb_ipr_inscricao_processo
                                    where
                                        $whereRestritivo
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
            }
        }
    }

}
