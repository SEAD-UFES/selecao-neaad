<?php

/**
 * Classe que implementa a paginaçao multinivel
 * 
 * @author Estevão Costa
 */
class Paginacao {

    private $callBackTabela;
    private $callBackContador;
    private $filtro;
    public static $QTDE_POR_PAGINA_PADRAO = 15;
    //
    private $totalItens; //total geral de itens sem paginação
    //
    //definições para "quantidade de links por página"
    private static $QTDE_LINK_PAGINA = 10;
    private static $QTDE_LINK_ANT_PAG_ATUAL = 4;
    //
    // variável interna para contagem de itens por página
    private static $QTDE_POR_PAGINA = NULL;

    public static function getQtdePorPagina() {
        if (Paginacao::$QTDE_POR_PAGINA == NULL) {
            $idLogado = getIdUsuarioLogado();
            // não está logado
            if (Util::vazioNulo($idLogado)) {
                return Paginacao::$QTDE_POR_PAGINA_PADRAO;
            } else {
                try {
                    $conf = ConfiguracaoUsuario::buscarConfiguracaoPorUsuario(getIdUsuarioLogado());
                    Paginacao::$QTDE_POR_PAGINA = $conf->getCFU_QT_REGISTROS_PAG();
                } catch (Exception $e) {
                    return Paginacao::$QTDE_POR_PAGINA_PADRAO;
                }
            }
        }
        return Paginacao::$QTDE_POR_PAGINA;
    }

    /**
     * 
     * @param callback function $callBackTabela
     * @param callback function $callBackContador
     * @param Filtro $filtro
     */
    public function __construct($callBackTabela, $callBackContador, $filtro) {
        $this->callBackTabela = $callBackTabela;
        $this->callBackContador = $callBackContador;

        $this->atualizaObjFiltro($filtro);
    }

    public function atualizaObjFiltro($novoFiltro) {
        $this->filtro = $novoFiltro;

        //recuperando total de itens a ser paginado
        $this->totalItens = call_user_func($this->callBackContador, $this->filtro);

        // setando accordion aberto ou fechado
        $this->filtro->setAccordionAberto($this->totalItens);

        //acertando valor do filtro em caso de extrapolação
        if ($this->filtro->getInicioDados() + 1 > $this->totalItens) {
            $this->filtro->setInicioDados(0);
        }
    }

    private function cabecalhoPaginacao() {
        $ini = $this->filtro->getInicioDados() + 1;
        //casos específicos
        if ($this->totalItens == 0) {
            $texto = "nenhuma ocorrência";
        } else if ($this->totalItens == 1) {
            $texto = $ini > $this->totalItens ? "início maior que o total de $this->totalItens ocorrência" : "única ocorrência";
        } else {
            //é maior que 1
            if ($ini + $this->filtro->getQtdeDadosPag() - 1 > $this->totalItens) {
                $fim = $this->totalItens;
            } else {
                $fim = $ini + $this->filtro->getQtdeDadosPag() - 1;
            }
            $texto = $ini > $this->totalItens ? "início maior que o total de $this->totalItens ocorrências" : "$ini - $fim de $this->totalItens";
        }


        //montando div com texto
        $result = "<div class='completo qt-itens' style='margin-bottom:10px;'>$texto</div>";

        return $result;
    }

    private function rodapePaginacao() {
        //string com código das páginas
        $strPag = "";

        //descobrindo total de páginas e página atual
        $totalPag = ceil($this->totalItens / $this->filtro->getQtdeDadosPag());
        $pagAtual = (int) ($this->filtro->getInicioDados() / $this->filtro->getQtdeDadosPag()) + 1;

        //definindo se é necessário mostrar link para a primeira página
        if ($pagAtual > 1 && $totalPag > self::$QTDE_LINK_PAGINA) {

            //definindo link para a primeira página
            $url = "{$this->filtro->getUrlParametros()}&{$this->filtro->getNmParamInicio()}=0";
            $strPag .= "<li><a href='$url' title='Voltar à primeira página'><i class='fa fa-angle-double-left'></i></a></li>";

            //definindo link para a página anterior
            $iniPagAnterior = $this->filtro->getInicioDados() - $this->filtro->getQtdeDadosPag();
            $pagAnterior = $pagAtual - 1;
            $url = "{$this->filtro->getUrlParametros()}&{$this->filtro->getNmParamInicio()}=$iniPagAnterior";
            $strPag .= "<li><a href='$url' title='Voltar à página $pagAnterior'><i class='fa fa-angle-left'></i></a></li>";
        }


        // imprimindo páginas atrás da página atual
        $iniPagCorrente = $this->filtro->getInicioDados() - $this->filtro->getQtdeDadosPag();
        $pagCorrente = $pagAtual - 1;
        $totalLinkInserido = 0;
        $qtPaginaAnterior = $totalPag > self::$QTDE_LINK_PAGINA ? self::$QTDE_LINK_ANT_PAG_ATUAL : $pagAtual;
        $arrayTemp = array();
        for ($i = 0; $i < $qtPaginaAnterior && $iniPagCorrente >= 0; $i++, $iniPagCorrente-=$this->filtro->getQtdeDadosPag(), $pagCorrente--, $totalLinkInserido++) {
            //url
            $url = "{$this->filtro->getUrlParametros()}&{$this->filtro->getNmParamInicio()}=$iniPagCorrente";
            $arrayTemp[] = "<li><a title='Ir para a página $pagCorrente' href='$url'>$pagCorrente</a></li>";
        }
        $strPag .= implode("", array_reverse($arrayTemp));


        // imprimindo página atual
        $strPag .= "<li class='active'><a href='#'>$pagAtual</a></li>";


        //imprimindo páginas a frente da página atual
        $iniPagCorrente = $this->filtro->getInicioDados() + $this->filtro->getQtdeDadosPag();
        $pagCorrente = $pagAtual + 1;
        for ($i = 0; $i < (self::$QTDE_LINK_PAGINA - $totalLinkInserido - 1) && $pagCorrente <= $totalPag; $i++, $iniPagCorrente+=$this->filtro->getQtdeDadosPag(), $pagCorrente++) {
            //url
            $url = "{$this->filtro->getUrlParametros()}&{$this->filtro->getNmParamInicio()}=$iniPagCorrente";
            $strPag .= "<li><a title='Ir para a página $pagCorrente' href='$url'>$pagCorrente</a></li>";
        }

        //definindo se é necessário mostrar link para a última página
        if ($pagAtual < $totalPag && $totalPag > self::$QTDE_LINK_PAGINA) {

            //definindo link para a próxima página
            $iniPagPosterior = $this->filtro->getInicioDados() + $this->filtro->getQtdeDadosPag();
            $pagPosterior = $pagAtual + 1;
            $url = "{$this->filtro->getUrlParametros()}&{$this->filtro->getNmParamInicio()}=$iniPagPosterior";
            $strPag .= "<li><a href='$url' title='Avançar à página $pagPosterior'><i class='fa fa-angle-right'></i></a></li>";

            //definindo link para a última página
            $iniUltimaPag = ($totalPag - 1) * $this->filtro->getQtdeDadosPag();
            $url = "{$this->filtro->getUrlParametros()}&{$this->filtro->getNmParamInicio()}=$iniUltimaPag";
            $strPag .= "<li><a href='$url' title='Ir para a última página'><i class='fa fa-angle-double-right'></i></a></li>";
        }

        return "<nav style='text-align:center;'><ul class='pagination'>$strPag</ul></nav>";
    }

    public function imprimir() {
        if ($this->totalItens > 0) {
            echo $this->cabecalhoPaginacao();
            echo call_user_func($this->callBackTabela, $this->filtro);
            echo "";
            echo $this->rodapePaginacao();
        } else {
            echo call_user_func($this->callBackTabela, $this->filtro);
        }
    }

}

?>
