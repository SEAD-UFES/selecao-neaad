<?php

/**
 * Classe que implementa a funcao de Número fixo
 * 
 * Ela pode ser usada como Criterio de Seleção ou Cadastro de Reserva
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/macroConfProc/MacroAbs.php";

class NumeroFixo extends MacroAbs implements MacroCritSelecao, MacroCritCadReserva {

    private static $paramValor = "vlFixo";

    public function __construct($tpMacro, $paramExt = NULL) {
        parent::__construct($tpMacro, $paramExt);

        // criando parâmetro
        $param = new ParamMacro(self::$paramValor, ParamMacro::$TIPO_INTEIRO, "Quantidade:");
        // setando validador extra
        $param->addValidadorExtra(ParamMacro::$VALIDADOR_MIN_1);

        $this->parametros = array(self::$paramValor => $param);
        $this->qtParametros = count($this->parametros);
    }

    public function getNmFantasia() {
        return "Número Fixo";
    }

    public function getIdMacro() {
        return "numeroFixo";
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

    public function addSqlsAplicaCriterio($chamada, $sqlInicial, $whereRestritivo, &$arrayCmds) {
        // recuperando quantidade de cadastro de reserva
        $qtCadReserva = $this->getValorParam(self::$paramValor);

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

                //percorrendo áreas e polos e executando sql
                foreach ($polosAreasCham as $poloArea) {
                    // percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {

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
                //percorrendo polos e executando sql
                foreach (array_keys($polosCham) as $idPoloCham) {
                    // percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {
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
                foreach (array_keys($polosCham) as $idPoloCham) {

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
            $areasAtuCham = AreaAtuChamada::buscarAreaAtuChamVagasPorChamada($chamada->getPCH_ID_CHAMADA(), AreaAtuChamada::getFlagAreaAtiva());

            if ($chamada->admiteReservaVagaObj()) {
                //percorrendo áreas e executando sql
                foreach (array_keys($areasAtuCham) as $idAreaCham) {
                    // percorrendo vagas
                    foreach ($listaReservaVaga as $reserva) {
                        // sql para reservas de vagas
                        $arrayCmds [] = $sqlInicial . "
                                and AAC_ID_AREA_CHAMADA = '$idAreaCham'
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
                                        and AAC_ID_AREA_CHAMADA = '$idAreaCham'
                                        and RVC_ID_RESERVA_CHAMADA = '{$reserva->getRVC_ID_RESERVA_CHAMADA()}'
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                    }

                    // sql para público geral
                    $arrayCmds [] = $sqlInicial . "
                                and AAC_ID_AREA_CHAMADA = '$idAreaCham'
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
                                        and AAC_ID_AREA_CHAMADA = '$idAreaCham'
                                        and RVC_ID_RESERVA_CHAMADA IS NULL
                                    order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                    limit 0 , $qtCadReserva) tmp)";
                }
                //
            //
                
            } else { // SEM RESERVA DE VAGAS
                //percorrendo areas e executando sql
                foreach (array_keys($areasAtuCham) as $idAreaCham) {

                    // sql para reservas de vagas
                    $arrayCmds [] = $sqlInicial . "
                                    and AAC_ID_AREA_CHAMADA = '$idAreaCham'
                                    and IPR_ID_INSCRICAO in (select 
                                        IPR_ID_INSCRICAO
                                    from
                                        (select 
                                            IPR_ID_INSCRICAO
                                        from
                                            tb_ipr_inscricao_processo
                                        where
                                        $whereRestritivo
                                                and AAC_ID_AREA_CHAMADA = '$idAreaCham'
                                        order by IPR_NR_CLASSIFICACAO_CAND IS NULL, IPR_NR_CLASSIFICACAO_CAND
                                        limit 0 , $qtCadReserva) tmp)";
                }
            }
        } else { // Sem polo e sem área
            if ($chamada->admiteReservaVagaObj()) {
                // percorrendo reservas de vagas
                foreach ($listaReservaVaga as $reserva) {
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
