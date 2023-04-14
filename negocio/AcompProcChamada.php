<?php

/**
 * tb_apc_acomp_proc_chamada class
 * This class manipulates the table AcompProcChamada
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2015       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 09/01/2015
 * */
require_once (dirname(__FILE__) . "/../config.php");
global $CFG;
require_once $CFG->rpasta . "/visao/relatorio/gerarPDFRetificacaoCalendario.php";
require_once $CFG->rpasta . "/visao/relatorio/gerarPDFResultado.php";

class AcompProcChamada {

    private $APC_ID_ACOMP_PROC_CHAM;
    private $PRC_ID_PROCESSO;
    private $PCH_ID_CHAMADA;
    private $APC_TP_ACOMPANHAMENTO;
    private $APC_DS_ACOMPANHAMENTO;
    private $APC_URL_ACOMPANHAMENTO;
    private $APC_DT_ACOMPANHAMENTO;
    private $APC_ARQ_ASSOCIADO;
    private $APC_URL_ARQ_ANTERIOR;
    private $APC_VERSAO_ATUAL_ARQ;
    private $APC_ID_USUARIO_RESP;
    // campos herdados
    public $USR_DS_NOME_RESPONSAVEL;
    // tipos de acompanhamento
    public static $TIPO_ATUALIZACAO_EDITAL = "E";
    public static $TIPO_RETIFICACAO_CALENDARIO = "C";
    public static $TIPO_RETIFICACAO_VAGAS = "G";
    public static $TIPO_PUBLICACAO_RESULTADO = "S";
    public static $TIPO_ATIVACAO_CHAMADA = "H";
    // projetados
    public static $TIPO_RETIFICACAO_EDITAL = "R";
    public static $TIPO_INF_PUB_ADICIONAL = "I";
    public static $TIPO_ARQ_PUB_ADICIONAL = "F";
    public static $TIPO_INF_INSCRITOS = "A";
    public static $TIPO_ARQ_INSCRITOS = "D";
    public static $TIPO_RETIFICACAO_DOCUMENTO = "U";
    public static $TIPO_ATUALIZACAO_DOCUMENTO = "V";
    // campos de controle
    private static $VERSAO_BASE_ARQ = 1;
    private static $SEPARADOR_VERSAO = "_";
    public static $TIPO_PDF = ".pdf";

    /* Construtor padrão da classe */

    public function __construct($APC_ID_ACOMP_PROC_CHAM, $PRC_ID_PROCESSO, $PCH_ID_CHAMADA, $APC_TP_ACOMPANHAMENTO, $APC_DS_ACOMPANHAMENTO, $APC_URL_ACOMPANHAMENTO, $APC_DT_ACOMPANHAMENTO, $APC_ARQ_ASSOCIADO, $APC_URL_ARQ_ANTERIOR = NULL, $APC_VERSAO_ATUAL_ARQ = NULL, $APC_ID_USUARIO_RESP = NULL) {
        $this->APC_ID_ACOMP_PROC_CHAM = $APC_ID_ACOMP_PROC_CHAM;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->PCH_ID_CHAMADA = $PCH_ID_CHAMADA;
        $this->APC_TP_ACOMPANHAMENTO = $APC_TP_ACOMPANHAMENTO;
        $this->APC_DS_ACOMPANHAMENTO = $APC_DS_ACOMPANHAMENTO;
        $this->APC_URL_ACOMPANHAMENTO = $APC_URL_ACOMPANHAMENTO;
        $this->APC_DT_ACOMPANHAMENTO = $APC_DT_ACOMPANHAMENTO;
        $this->APC_ARQ_ASSOCIADO = $APC_ARQ_ASSOCIADO;
        $this->APC_URL_ARQ_ANTERIOR = $APC_URL_ARQ_ANTERIOR;
        $this->APC_VERSAO_ATUAL_ARQ = $APC_VERSAO_ATUAL_ARQ;
        $this->APC_ID_USUARIO_RESP = $APC_ID_USUARIO_RESP;
    }

    /**
     * Esta função retorna a data da última atualização do arquivo associado.
     * 
     * @param string $arqAssociado
     * @param int $idProcesso
     * @param int $idChamada
     * @return string string com a data de atualização do arquivo no formato dd/mm/yyyy
     * @throws NegocioException
     */
    public static function getDtAtualizacaoArquivoPorCham($arqAssociado, $idProcesso, $idChamada) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // flags
            $versaoBase = self::$VERSAO_BASE_ARQ;

            $sql = "SELECT 
                        DATE_FORMAT(`APC_DT_ACOMPANHAMENTO`,'%d/%m/%Y') as APC_DT_ACOMPANHAMENTOSTR
                    from
                        tb_apc_acomp_proc_chamada
                    where
                        PRC_ID_PROCESSO = '$idProcesso'
                        and PCH_ID_CHAMADA = '$idChamada' 
                        and APC_ARQ_ASSOCIADO = '$arqAssociado'
                        and APC_VERSAO_ATUAL_ARQ > $versaoBase
                    order by APC_DT_ACOMPANHAMENTO desc
                    limit 0,1";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return NULL; // Não há atualização
            }
            return $conexao->getResult("APC_DT_ACOMPANHAMENTOSTR", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar atualização de arquivo.", $e);
        }
    }

    /**
     * Esta função conta quantos acompanhamentos há, nos parâmetros informados, em uma determinada data, também informada
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @param char $tpAcomp Tipo de acompanhamento a ser pesquisado
     * @param string $dataAcomp Data no formato dd/mm/yyyy
     * @param string $arqAssociado Arquivo associado, se aplicável
     * @return int
     * @throws NegocioException
     */
    private static function contarAcompPorChamTipoData($idProcesso, $idChamada, $tpAcomp, $dataAcomp, $arqAssociado = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando sql
            $sql = "SELECT 
                        count(*) as cont
                    from
                        tb_apc_acomp_proc_chamada
                    where
                        PRC_ID_PROCESSO = '$idProcesso'
                        and PCH_ID_CHAMADA = '$idChamada' 
                        and APC_TP_ACOMPANHAMENTO = '$tpAcomp'
                        and DATE_FORMAT(`APC_DT_ACOMPANHAMENTO`,'%d/%m/%Y') = '$dataAcomp'";

            // arquivo associado
            if ($arqAssociado != NULL) {
                $sql .= " and APC_ARQ_ASSOCIADO = '$arqAssociado'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar acompanhamentos do processo.", $e);
        }
    }

    public static function contarAcompPorUsuResp($idUsuResp) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando sql
            $sql = "SELECT 
                        count(*) as cont
                    from
                        tb_apc_acomp_proc_chamada
                    where
                        APC_ID_USUARIO_RESP = '$idUsuResp'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar acompanhamentos do processo por responsável.", $e);
        }
    }

    private static function buscarVersaoArquivoPorCham($idProcesso, $idChamada, $tipoAcomp, $arqAssociado = NULL) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // preparando sql
            $sql = "SELECT 
                        APC_VERSAO_ATUAL_ARQ
                    from
                        tb_apc_acomp_proc_chamada
                    where
                        PRC_ID_PROCESSO = '$idProcesso'
                        and PCH_ID_CHAMADA = '$idChamada' 
                        and APC_TP_ACOMPANHAMENTO = '$tipoAcomp'";

            // arquivo associado
            if ($arqAssociado != NULL) {
                $sql .= " and APC_ARQ_ASSOCIADO = '$arqAssociado'";
            }

            // finalizando
            $sql .= " order by `APC_DT_ACOMPANHAMENTO` desc limit 0,1";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando valor
            if (ConexaoMysql::getNumLinhas($resp) == 0) {
                return self::$VERSAO_BASE_ARQ;
            }
            return $conexao->getResult("APC_VERSAO_ATUAL_ARQ", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar versão de um arquivo.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @param int $idChamada
     * @param boolean $agrupamentoDiario Infoma se é para agrupar os acompanhamentos semelhantes por dia, de forma a evitar 
     * a exibição de várias atualizações de um mesmo tipo em um mesmo dia. Padrão: TRUE
     * @return \AcompProcChamada Array com acompanhamentos da chamada
     * @throws NegocioException
     */
    public static function buscarAcompProcChamadaPorCham($idProcesso, $idChamada = NULL, $agrupamentoDiario = TRUE) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "SELECT 
                        *
                    FROM
                        (SELECT 
                            APC_ID_ACOMP_PROC_CHAM,
                                PRC_ID_PROCESSO,
                                PCH_ID_CHAMADA,
                                APC_TP_ACOMPANHAMENTO,
                                APC_DS_ACOMPANHAMENTO,
                                APC_URL_ACOMPANHAMENTO,
                                APC_DT_ACOMPANHAMENTO,
                                DATE_FORMAT(`APC_DT_ACOMPANHAMENTO`, '%d/%m/%Y') AS APC_DT_ACOMPANHAMENTOSTR,
                                APC_ARQ_ASSOCIADO,
                                APC_URL_ARQ_ANTERIOR,
                                APC_VERSAO_ATUAL_ARQ,
                                APC_ID_USUARIO_RESP,
                                USR_DS_NOME
                        FROM
                            tb_apc_acomp_proc_chamada
                        LEFT JOIN tb_usr_usuario usr ON USR_ID_USUARIO = APC_ID_USUARIO_RESP
                        WHERE
                            PRC_ID_PROCESSO = '$idProcesso' ";

            // caso de ter chamada
            if ($idChamada != NULL) {
                $sql .= " PCH_ID_CHAMADA = '$idChamada' ";
            }

            $sql .= " ORDER BY APC_DT_ACOMPANHAMENTO DESC , APC_ID_ACOMP_PROC_CHAM DESC) temp ";

            // agrupamento diario
            if ($agrupamentoDiario) {
                $sql .= " GROUP BY APC_TP_ACOMPANHAMENTO , APC_ARQ_ASSOCIADO , APC_DT_ACOMPANHAMENTOSTR
                          ORDER BY APC_DT_ACOMPANHAMENTO DESC , APC_ID_ACOMP_PROC_CHAM DESC ";
            }



//            print_r($sql);
//            exit;
//            
            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando vazio
                return array();
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $acompTemp = new AcompProcChamada($dados['APC_ID_ACOMP_PROC_CHAM'], $dados['PRC_ID_PROCESSO'], $dados['PCH_ID_CHAMADA'], $dados['APC_TP_ACOMPANHAMENTO'], $dados['APC_DS_ACOMPANHAMENTO'], $dados['APC_URL_ACOMPANHAMENTO'], $dados['APC_DT_ACOMPANHAMENTOSTR'], $dados['APC_ARQ_ASSOCIADO'], $dados['APC_URL_ARQ_ANTERIOR'], $dados['APC_VERSAO_ATUAL_ARQ'], $dados['APC_ID_USUARIO_RESP']);

                // campos herdados
                $acompTemp->USR_DS_NOME_RESPONSAVEL = $dados['USR_DS_NOME'];

                //adicionando no vetor
                $vetRetorno[$i] = $acompTemp;
            }
            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar acompanhamentos da chamada.", $e);
        }
    }

    public static function processaAtualizacaoEdital($idProcesso, $idChamada, $arqAssociado, $destinoArqServ, $arqTemporario, &$arraySqls) {
        // Copiando arquivo temporario para o local correto no servidor
        NGUtil::arq_copiarArquivoServidor($arqTemporario, $destinoArqServ);

        // verificando se é necessário registrar atualização de arquivo
        if (ProcessoChamada::necessarioInfAtuEdital($idChamada)) {

            // recuperando versão atual do arquivo e recuperando sql
            $versaoArq = self::buscarVersaoArquivoPorCham($idProcesso, $idChamada, self::$TIPO_ATUALIZACAO_EDITAL, $arqAssociado);
            $arraySqls [] = self::getSqlCriacaoAcomp($idProcesso, $idChamada, self::$TIPO_ATUALIZACAO_EDITAL, $arqAssociado, "Edital atualizado.", NULL, NULL, $versaoArq + 1);

            // Enviar email de notificação aos administradores
            $processo = Processo::buscarProcessoPorId($idProcesso);
            Usuario::enviarNotAlteracaoEditalAdmin($processo, getIdUsuarioLogado());
        }
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param int $idResponsavel
     * @param array $arraySqls Endereço para o array onde deve ser adicionado as sqls
     */
    public static function processaAtivacaoChamada($processo, $chamada, $idResponsavel, &$arraySqls) {
        $arraySqls [] = self::getSqlCriacaoAcomp($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_ATIVACAO_CHAMADA, NULL, "Aberta a {$chamada->getPCH_DS_CHAMADA(TRUE)}. Confira o calendário.", NULL, NULL, NULL);

        // Enviar email de notificação aos administradores
        Usuario::enviarNotAtivacaoChamEditalAdmin($processo, $chamada, $idResponsavel);
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param string $iniUrldestinoArqServ
     * @param array $listaCalendario Array com a lista de itens do calendário, estruturado conforme função específica
     * @param array $vetNovosDados Array com os novos dados, indexados na forma do array $listaCalendario
     * @param string $textoInicial Texto inicial a ser incluída no documento
     * @param array $arraySqls
     */
    public static function processaRetificacaoCalendario($processo, $chamada, $iniUrldestinoArqServ, $listaCalendario, $vetNovosDados, $textoInicial, &$arraySqls) {
        global $CFG;

        // verificando se é necessário registrar atualização
        if ($chamada->necessarioInfAtuEditalObj()) {

            // recuperando versão atual da modificação
            $versaoArq = self::buscarVersaoArquivoPorCham($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_RETIFICACAO_CALENDARIO);
            $novaVersao = $versaoArq + 1;

            // completando nome de arquivo com versão
            $urlArqAnt = $versaoArq != self::$VERSAO_BASE_ARQ ? $iniUrldestinoArqServ . self::$SEPARADOR_VERSAO . $versaoArq . self::$TIPO_PDF : NULL;
            $urlArqAtual = $iniUrldestinoArqServ . self::$SEPARADOR_VERSAO . $novaVersao . self::$TIPO_PDF;
            $urlFisica = "$CFG->rpasta/$urlArqAtual";

            // criando pastas, se necessário
            $processo->getDiretorioUploadEdital();

            // criando arquivo com retificação 
            calendario_gerarArquivoPrevia($processo, $chamada, $textoInicial, $listaCalendario, $vetNovosDados, 'F', $urlFisica);

            $arraySqls [] = self::getSqlCriacaoAcomp($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_RETIFICACAO_CALENDARIO, $iniUrldestinoArqServ, "Calendário da {$chamada->getPCH_DS_CHAMADA(TRUE)} retificado.", $urlArqAtual, $urlArqAnt, $novaVersao);

            // adicionando sqls de notícia
            if (self::contarAcompPorChamTipoData($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_RETIFICACAO_CALENDARIO, dt_getDataEmStr("d/m/Y")) == 0) {
                Noticia::addSqlCriarNoticiaAltCalendario($processo, $chamada, $arraySqls);
            }

            // Enviar email de notificação aos administradores
            Usuario::enviarNotAltCalendarioEditalAdmin($processo, $chamada);
        }

        return $urlFisica;
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param string $iniUrldestinoArqServ
     * @param string $arqTemporario
     * @param array $arraySqls
     */
    public static function processaRetificacaoVagas($processo, $chamada, $iniUrldestinoArqServ, $arqTemporario, &$arraySqls) {
        // verificando se é necessário registrar atualização
        if ($chamada->necessarioInfAtuEditalObj()) {
            // recuperando versão atual da modificação
            $versaoArq = self::buscarVersaoArquivoPorCham($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_RETIFICACAO_VAGAS);
            $novaVersao = $versaoArq + 1;

            // completando nome de arquivo com versão
            $urlArqAnt = $versaoArq != self::$VERSAO_BASE_ARQ ? $iniUrldestinoArqServ . self::$SEPARADOR_VERSAO . $versaoArq . self::$TIPO_PDF : NULL;
            $iniUrldestinoArqServ .= self::$SEPARADOR_VERSAO . $novaVersao . self::$TIPO_PDF;

            // tem arquivo? copiando para o servidor
            if ($arqTemporario != NULL) {
                // Copiando arquivo temporario para o local correto no servidor
                NGUtil::arq_copiarArquivoServidor($arqTemporario, $iniUrldestinoArqServ);
            }
            $arraySqls [] = self::getSqlCriacaoAcomp($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_RETIFICACAO_VAGAS, NULL, "Retificação das vagas da {$chamada->getPCH_DS_CHAMADA(TRUE)}.", $iniUrldestinoArqServ, $urlArqAnt, $novaVersao);

            // adicionando sqls de notícia
            if (self::contarAcompPorChamTipoData($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_RETIFICACAO_VAGAS, dt_getDataEmStr("d/m/Y")) == 0) {
                Noticia::addSqlCriarNoticiaAltVagas($processo, $chamada, $arraySqls);
            }

            // Enviar email de notificação aos administradores
            Usuario::enviarNotRetVagasAdmin($processo, $chamada, getIdUsuarioLogado());
        }
    }

    /**
     * 
     * @param Processo $processo
     * @param ProcessoChamada $chamada
     * @param string $arqAssociado
     * @param string $iniUrldestinoArqServ
     * @param EtapaSelProc $etapaVigente
     * @param string $dsPublicacao
     * @param array $arraySqls
     * @param boolean $arqExterno Informa se não é para gerar o arquivo de resultado automaticamente, pois será utilizado um arquivo externo
     * @param string $dtFimEdital Data programada para finalização do Edital (se houver)
     * @param mixed $resultadoFinal Diz se é um processamento do resultado final.
     *  Se Sim (char), temos 'F -> Resultado final ou 'P' -> Resultado povisório. Padrão: FALSE
     */
    public static function processaPublicacaoResultado($processo, $chamada, $arqAssociado, $iniUrldestinoArqServ, $etapaVigente, $dsPublicacao, &$arraySqls, $arqExterno, $dtFimEdital = NULL, $resultadoFinal = FALSE) {
        global $CFG;
        $urlFisica = NULL;

        // Adicionando chamada à publicação
        $dsPublicacao = "{$chamada->getPCH_DS_CHAMADA(TRUE)} - $dsPublicacao";

        // recuperando versão atual da modificação
        $versaoArq = self::buscarVersaoArquivoPorCham($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_PUBLICACAO_RESULTADO, $arqAssociado);

        // definindo nova versão: Atente para o comparador de tipo: '===' -> Quando já tem resultado, é retornado uma string e não um int
        $novaVersao = $versaoArq === self::$VERSAO_BASE_ARQ ? self::$VERSAO_BASE_ARQ : $versaoArq + 1;

        // completando nome de arquivo com versão
        $urlArqAnt = $novaVersao != self::$VERSAO_BASE_ARQ ? $iniUrldestinoArqServ . self::$SEPARADOR_VERSAO . $versaoArq . self::$TIPO_PDF : NULL;
        $urlArqAtual = $iniUrldestinoArqServ . self::$SEPARADOR_VERSAO . $novaVersao . self::$TIPO_PDF;

        // verificando se é para gerar o arquivo de resultados
        if (!$arqExterno) {
            $urlFisica = "$CFG->rpasta/$urlArqAtual";
            $urlFisicaOficial = "$CFG->rpasta/$arqAssociado";

            // criando pastas, se necessário
            $processo->getDiretorioUploadEdital();
//        
            // criando arquivo com retificação 
            resultado_gerarArquivoPrevia($processo, $chamada, $etapaVigente, 'F', $urlFisica, $resultadoFinal);

            // Atualizando arquivo 'Oficial' para facilitar o acesso ao arquivo final
            copy($urlFisica, $urlFisicaOficial);
        }

        $arraySqls [] = self::getSqlCriacaoAcomp($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_PUBLICACAO_RESULTADO, $arqAssociado, $dsPublicacao, $urlArqAtual, $urlArqAnt, $novaVersao);

        // adicionando sqls de notícia
        if (self::contarAcompPorChamTipoData($processo->getPRC_ID_PROCESSO(), $chamada->getPCH_ID_CHAMADA(), self::$TIPO_PUBLICACAO_RESULTADO, dt_getDataEmStr("d/m/Y"), $arqAssociado) == 0) {
            Noticia::addSqlCriarNoticiaPubResultado($processo, $chamada, $dsPublicacao, $arraySqls, $dtFimEdital);
        }

        // Notificando os admin 
        Usuario::enviarNotPubResultadoAdmin($processo, $dsPublicacao, getIdUsuarioLogado());

        return $urlFisica;
    }

    private static function getSqlCriacaoAcomp($idProcesso, $idChamada, $tpAcompanhamento, $arqAssociado, $dsAcomp, $urlAcomp, $arqAnterior, $versaoArq) {
        // criando objeto relacionado e retornando sql
        $obj = new AcompProcChamada(NULL, $idProcesso, $idChamada, $tpAcompanhamento, $dsAcomp, $urlAcomp, NULL, $arqAssociado, $arqAnterior, $versaoArq);
        return $obj->getSqlCriacaoObjeto();
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_apc_acomp_proc_chamada where PRC_ID_PROCESSO = '$idProcesso'";
    }

    public function getHTMLDescricaoAcomp() {
        // @todo Alterar aqui quando começar a gerar PDF de resultados ou alteração de dados do edital
        if ($this->APC_TP_ACOMPANHAMENTO == self::$TIPO_ATUALIZACAO_EDITAL) {
            return "$this->APC_DS_ACOMPANHAMENTO&nbsp;"
                    . "<a target='_blank' href='{$this->getUrlArquivoAssociado()}'>Clique aqui <i class='fa fa-external-link'></i></a>";
        } elseif ($this->APC_TP_ACOMPANHAMENTO == self::$TIPO_RETIFICACAO_CALENDARIO) {
            return "$this->APC_DS_ACOMPANHAMENTO&nbsp;"
                    . "<a target='_blank' href='{$this->getUrlArquivoAcompanhamento()}'>Clique aqui <i class='fa fa-external-link'></i></a>";
        } elseif ($this->APC_TP_ACOMPANHAMENTO == self::$TIPO_RETIFICACAO_VAGAS) {
            return "$this->APC_DS_ACOMPANHAMENTO";
        } elseif ($this->APC_TP_ACOMPANHAMENTO == self::$TIPO_PUBLICACAO_RESULTADO) {
            return "$this->APC_DS_ACOMPANHAMENTO&nbsp;"
                    . "<a target='_blank' href='{$this->getUrlArquivoAssociado()}'>Clique aqui <i class='fa fa-external-link'></i></a>";
        } elseif ($this->APC_TP_ACOMPANHAMENTO == self::$TIPO_ATIVACAO_CHAMADA) {
            return "$this->APC_DS_ACOMPANHAMENTO";
        } else {
            throw new NegocioException("Descrição do acompanhamento não programada.");
        }
    }

    private function getUrlArquivoAssociado() {
        global $CFG;

        // redirecionando para erro
        if (Util::vazioNulo($this->APC_ARQ_ASSOCIADO)) {
            return "{$CFG->rwww}/404.php?err=arq";
        }

        // verificando caso de arquivo externo
        if (!file_exists("{$CFG->rpasta}/{$this->APC_ARQ_ASSOCIADO}")) {
            $proc = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            return "http://neaad.ufes.br/conteudo/edital-n%C2%BA-{$proc->getPRC_NR_EDITAL()}{$proc->getPRC_ANO_EDITAL()}";
        }

        return "$CFG->rwww/$this->APC_ARQ_ASSOCIADO";
    }

    private function getUrlArquivoAcompanhamento() {
        global $CFG;

        // redirecionando para erro
        if (Util::vazioNulo($this->APC_URL_ACOMPANHAMENTO)) {
            return "{$CFG->rwww}/404.php?err=arq";
        }

        // verificando caso de arquivo externo
        if (!file_exists("{$CFG->rpasta}/{$this->APC_URL_ACOMPANHAMENTO}")) {
            $proc = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            return "http://neaad.ufes.br/conteudo/edital-n%C2%BA-{$proc->getPRC_NR_EDITAL()}{$proc->getPRC_ANO_EDITAL()}";
        }

        return "$CFG->rwww/$this->APC_URL_ACOMPANHAMENTO";
    }

    private function getSqlCriacaoObjeto() {
        // tratando campos
        $this->APC_DS_ACOMPANHAMENTO = NGUtil::trataCampoStrParaBD($this->APC_DS_ACOMPANHAMENTO);
        $this->APC_URL_ACOMPANHAMENTO = NGUtil::trataCampoStrParaBD($this->APC_URL_ACOMPANHAMENTO);
        $this->APC_ARQ_ASSOCIADO = NGUtil::trataCampoStrParaBD($this->APC_ARQ_ASSOCIADO);
        $this->APC_URL_ARQ_ANTERIOR = NGUtil::trataCampoStrParaBD($this->APC_URL_ARQ_ANTERIOR);
        $this->APC_VERSAO_ATUAL_ARQ = NGUtil::trataCampoIntParaBD($this->APC_VERSAO_ATUAL_ARQ);
        $this->APC_ID_USUARIO_RESP = NGUtil::trataCampoIntParaBD(getIdUsuarioLogado());



        return "insert into tb_apc_acomp_proc_chamada (PRC_ID_PROCESSO, PCH_ID_CHAMADA, APC_TP_ACOMPANHAMENTO, APC_DS_ACOMPANHAMENTO,
            APC_URL_ACOMPANHAMENTO, APC_DT_ACOMPANHAMENTO, APC_ARQ_ASSOCIADO, APC_URL_ARQ_ANTERIOR, APC_VERSAO_ATUAL_ARQ, APC_ID_USUARIO_RESP)
            values ('$this->PRC_ID_PROCESSO', '$this->PCH_ID_CHAMADA', '$this->APC_TP_ACOMPANHAMENTO', $this->APC_DS_ACOMPANHAMENTO
            , $this->APC_URL_ACOMPANHAMENTO, now(), $this->APC_ARQ_ASSOCIADO, $this->APC_URL_ARQ_ANTERIOR, $this->APC_VERSAO_ATUAL_ARQ, $this->APC_ID_USUARIO_RESP)";
    }

    /* GET FIELDS FROM TABLE */

    function getAPC_ID_ACOMP_PROC_CHAM() {
        return $this->APC_ID_ACOMP_PROC_CHAM;
    }

    /* End of get APC_ID_ACOMP_PROC_CHAM */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getPCH_ID_CHAMADA() {
        return $this->PCH_ID_CHAMADA;
    }

    /* End of get PCH_ID_CHAMADA */

    function getAPC_TP_ACOMPANHAMENTO() {
        return $this->APC_TP_ACOMPANHAMENTO;
    }

    /* End of get APC_TP_ACOMPANHAMENTO */

    function getAPC_DS_ACOMPANHAMENTO() {
        return $this->APC_DS_ACOMPANHAMENTO;
    }

    /* End of get APC_DS_ACOMPANHAMENTO */

    function getAPC_URL_ACOMPANHAMENTO() {
        return $this->APC_URL_ACOMPANHAMENTO;
    }

    /* End of get APC_URL_ACOMPANHAMENTO */

    function getAPC_DT_ACOMPANHAMENTO($apenasData = FALSE) {
        if ($apenasData) {
            $temp = explode(" ", $this->APC_DT_ACOMPANHAMENTO);
            return $temp[0];
        }
        return $this->APC_DT_ACOMPANHAMENTO;
    }

    /* End of get APC_DT_ACOMPANHAMENTO */

    function getAPC_ARQ_ASSOCIADO
    () {
        return $this->APC_ARQ_ASSOCIADO;
    }

    /* End of get APC_ARQ_ASSOCIADO */



    /* SET FIELDS FROM TABLE */

    function

    setAPC_ID_ACOMP_PROC_CHAM($value) {
        $this->APC_ID_ACOMP_PROC_CHAM = $value;
    }

    /* End of SET APC_ID_ACOMP_PROC_CHAM */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setPCH_ID_CHAMADA($value) {
        $this->PCH_ID_CHAMADA = $value;
    }

    /* End of SET PCH_ID_CHAMADA */

    function

    setAPC_TP_ACOMPANHAMENTO($value) {
        $this->APC_TP_ACOMPANHAMENTO = $value;
    }

    /* End of SET APC_TP_ACOMPANHAMENTO */

    function

    setAPC_DS_ACOMPANHAMENTO($value) {
        $this->APC_DS_ACOMPANHAMENTO = $value;
    }

    /* End of SET APC_DS_ACOMPANHAMENTO */

    function

    setAPC_URL_ACOMPANHAMENTO($value) {
        $this->APC_URL_ACOMPANHAMENTO = $value;
    }

    /* End of SET APC_URL_ACOMPANHAMENTO */

    function

    setAPC_DT_ACOMPANHAMENTO($value) {
        $this->APC_DT_ACOMPANHAMENTO = $value;
    }

    /* End of SET APC_DT_ACOMPANHAMENTO */

    function setAPC_ARQ_ASSOCIADO($value) {
        $this->APC_ARQ_ASSOCIADO = $value;
    }

    /* End of SET APC_ARQ_ASSOCIADO */
}

?>
