<?php

/**
 * tb_eap_etapa_aval_proc class
 * This class manipulates the table EtapaAvalProc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2014       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = 172.20.11.188
 * @date 02/07/2014
 * */
require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/negocio/MacroConfProc.php";

class EtapaAvalProc {

    private $EAP_ID_ETAPA_AVAL_PROC;
    private $PRC_ID_PROCESSO;
    private $EAP_NR_ETAPA_AVAL;
    public static $NM_INICIAL_ETAPA = "Etapa";
    public static $ID_SELECT_NOVA_ETAPA = 'N';
    public static $DS_SELECT_NOVA_ETAPA = 'Nova Etapa';
    public static $NR_PRIMEIRA_ETAPA = 1;
    // processamento interno
    private $flagPermiteExcluir, $flagPermiteAlterar;

    /* Construtor padrão da classe */

    public function __construct($EAP_ID_ETAPA_AVAL_PROC, $PRC_ID_PROCESSO, $EAP_NR_ETAPA_AVAL) {
        $this->EAP_ID_ETAPA_AVAL_PROC = $EAP_ID_ETAPA_AVAL_PROC;
        $this->PRC_ID_PROCESSO = $PRC_ID_PROCESSO;
        $this->EAP_NR_ETAPA_AVAL = $EAP_NR_ETAPA_AVAL;
        $this->flagPermiteExcluir = $this->flagPermiteAlterar = NULL;
    }

    /**
     * 
     * @param int $idProcesso
     * @param boolan $insNova Informa se é para incluir opção de criar nova etapa
     * @param boolean $apenasEditaveis Informa se é para incluir na lista apenas as etapas que podem ser alteradas
     * @return Array
     * @throws NegocioException
     */
    public static function buscarSelectEtapaAvalPorProc($idProcesso, $insNova = TRUE, $apenasEditaveis = TRUE) {
        try {
            $etapas = self::buscarEtapaAvalPorProc($idProcesso);
            $ret = array();
            if ($etapas != NULL) {
                foreach ($etapas as $etapa) {
                    if (!$apenasEditaveis || ($apenasEditaveis && $etapa->podeAlterar())) {
                        $ret[$etapa->EAP_ID_ETAPA_AVAL_PROC] = $etapa->getNomeEtapa();
                    }
                }
            }

            // inserindo novo...
            if ($insNova) {
                $ret[self::$ID_SELECT_NOVA_ETAPA] = self::$DS_SELECT_NOVA_ETAPA;
            }

            return $ret;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar select de Etapas de Avaliação.", $e);
        }
    }

    /**
     * 
     * @param int $idProcesso
     * @return \EtapaAvalProc|null
     * @throws NegocioException
     */
    public static function buscarEtapaAvalPorProc($idProcesso) {
        try {

            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        EAP_ID_ETAPA_AVAL_PROC, PRC_ID_PROCESSO, EAP_NR_ETAPA_AVAL
                    from
                        tb_eap_etapa_aval_proc
                    where PRC_ID_PROCESSO = '$idProcesso'
                    order by EAP_NR_ETAPA_AVAL";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando array Nulo
                return NULL;
            }

            $vetRetorno = array();

            //realizando iteração para recuperar dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);

                $etapaAvalTemp = new EtapaAvalProc($dados['EAP_ID_ETAPA_AVAL_PROC'], $dados['PRC_ID_PROCESSO'], $dados['EAP_NR_ETAPA_AVAL']);

                //adicionando no vetor
                $vetRetorno[$i] = $etapaAvalTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapas de avaliação do processo.", $e);
        }
    }

    public static function contarEtapaAvalPorProc($idProcesso) {
        try {

            //obtendo objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        count(*) as cont
                    from
                        tb_eap_etapa_aval_proc
                    where PRC_ID_PROCESSO = '$idProcesso'";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //retornando
            return $conexao->getResult("cont", $resp);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao contar etapas de avaliação do processo.", $e);
        }
    }

    public static function getNrUltimaEtapaAvalProc($idProcesso) {
        return self::contarEtapaAvalPorProc($idProcesso);
    }

    /**
     *  Esta função verifica se as etapas de avaliação estão OK para a criação de uma chamada
     * 
     * @param int $idProcesso
     * @return array Array na foma (flagValidacao, msgErro)
     * @throws NegocioException
     */
    public static function validarEtapaAvalParaChamada($idProcesso) {
        try {
            // tentando recuperar etapas
            $etapas = self::buscarEtapaAvalPorProc($idProcesso);

            if ($etapas == NULL) {
                // não tem etapa de avaliação: Isso é um erro
                return array(FALSE, "ainda não foi cadastrada uma etapa de avaliação.");
            }

            // percorrendo etapas
            foreach ($etapas as $etapa) {
                // contando categorias
                $qtCat = CategoriaAvalProc::contarCatAvalPorProcNrEtapa($idProcesso, $etapa->EAP_NR_ETAPA_AVAL);

                // Não tem categorias?
                if ($qtCat == 0) {
                    return array(FALSE, "a {$etapa->getNomeEtapa()} não possui categorias de avaliação.");
                }

                // validando categorias
                if (!CategoriaAvalProc::validarCatAvalParaChamada($idProcesso, $etapa->EAP_ID_ETAPA_AVAL_PROC)) {
                    return array(FALSE, "a {$etapa->getNomeEtapa()} possui categorias sem itens de avaliação.");
                }

                // etapa possui critério de seleção?
                $qtCritSel = MacroConfProc::contarMacroPorProcEtapa($idProcesso, $etapa->EAP_ID_ETAPA_AVAL_PROC, MacroConfProc::$TIPO_CRIT_SELECAO);
                if ($qtCritSel == 0) {
                    return array(FALSE, "a {$etapa->getNomeEtapa()} não possui critério de seleção.");
                }
            }

            // tudo ok
            return array(TRUE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar etapas de avaliação do processo.", $e);
        }
    }

    public static function buscarUltEtapaAvalPorProc($idProcesso) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        EAP_ID_ETAPA_AVAL_PROC, PRC_ID_PROCESSO, EAP_NR_ETAPA_AVAL
                    from
                        tb_eap_etapa_aval_proc
                    where PRC_ID_PROCESSO = '$idProcesso'
                    order by EAP_NR_ETAPA_AVAL desc
                    limit 0, 1";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Etapa de Avaliação não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $etapaAvalTemp = new EtapaAvalProc($dados['EAP_ID_ETAPA_AVAL_PROC'], $dados['PRC_ID_PROCESSO'], $dados['EAP_NR_ETAPA_AVAL']);

            return $etapaAvalTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapa de avaliação do processo.", $e);
        }
    }

    /**
     * Essa função retorna uma string com os elementos disponíveis para apresentação 
     * 
     * @param EtapaAvalProc $listaEtapas Array com Etapas
     * @return string String com os elementos da fórmula
     */
    public static function strElementosFormula($listaEtapas) {
        if ($listaEtapas == NULL) {
            return htmlentities("<Não há elementos disponíveis>");
        }
        $ret = "";
        foreach ($listaEtapas as $etapa) {
            $ret .= "{$etapa->getNomeEtapa()}: {$etapa->getIdElementoFormula()}\n";
        }
        return $ret;
    }

    public function getIdElementoFormula() {
        return self::getIdElemento($this->EAP_NR_ETAPA_AVAL);
    }

    public static function getIdElemento($nrEtapa) {
        return "[[" . self::$NM_INICIAL_ETAPA . "$nrEtapa]]";
    }

    /**
     * Se ID do processo nao e nulo, a funcao tambem verifica se a avaliacao 
     * pertence ao processo informado, e so retorna a etapa caso ela pertença ao 
     * processo.
     * 
     * @param int $idEtapaAval
     * @param int $idProcesso
     * @return \EtapaAvalProc
     * @throws NegocioException
     */
    public static function buscarEtapaAvalPorId($idEtapaAval, $idProcesso = NULL) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                        EAP_ID_ETAPA_AVAL_PROC, PRC_ID_PROCESSO, EAP_NR_ETAPA_AVAL
                    from
                        tb_eap_etapa_aval_proc
                    where EAP_ID_ETAPA_AVAL_PROC = '$idEtapaAval'";

            if ($idProcesso != NULL) {
                $sql .= " and PRC_ID_PROCESSO = '$idProcesso'";
            }

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //lançando exceção
                throw new NegocioException("Etapa de Avaliação não encontrada.");
            }

            //recuperando linha e criando objeto
            $dados = ConexaoMysql::getLinha($resp);
            $etapaAvalTemp = new EtapaAvalProc($dados['EAP_ID_ETAPA_AVAL_PROC'], $dados['PRC_ID_PROCESSO'], $dados['EAP_NR_ETAPA_AVAL']);

            return $etapaAvalTemp;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar etapa de avaliação do processo.", $e);
        }
    }

    /**
     * 
     * @param Processo $processo
     * @throws NegocioException
     */
    public static function criarEtapaAval($processo) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // nao pode editar
            if (!self::permiteCriarEtapa($processo)) {
                throw new NegocioException("Não é possível criar Etapa de Avaliação, pois o Edital está finalizado ou já existe uma chamada configurada.");
            }

            // montando SQL
            $sql = self::_getSqlCriarEtapaAval($processo->getPRC_ID_PROCESSO());

            // persistindo no bd
            $conexao->execSqlSemRetorno($sql);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao criar Etapa de Avaliação do Edital.", $e);
        }
    }

    public static function _getSqlCriarEtapaAval($idProcesso) {
        $sql = "insert into tb_eap_etapa_aval_proc
                (`PRC_ID_PROCESSO`,`EAP_NR_ETAPA_AVAL`)
                 values('$idProcesso',
                (
                SELECT COALESCE(MAX(`EAP_NR_ETAPA_AVAL`),0) + 1
                FROM (
                SELECT *
                FROM tb_eap_etapa_aval_proc) AS temp
                WHERE `PRC_ID_PROCESSO` = '$idProcesso'))";

        return $sql;
    }

    public function excluirEtapaAval() {
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            // nao pode excluir
            if (!$this->podeExcluir()) {
                throw new NegocioException("Não é possível excluir Etapa de Avaliação.");
            }

            $arrayCmds = array();

            // montando SQL
            $arrayCmds [] = "delete FROM tb_eap_etapa_aval_proc
                WHERE `PRC_ID_PROCESSO` = '$this->PRC_ID_PROCESSO'
                and EAP_ID_ETAPA_AVAL_PROC = '$this->EAP_ID_ETAPA_AVAL_PROC'";

            // recuperando sql de excluir configuracao do resultado final
            $arrayCmds [] = MacroConfProc::getSqlExcluirConfProc($this->PRC_ID_PROCESSO);

            // recuperando array 
            $arrayCmds = array_merge($arrayCmds, $this->getSqlReordenacaoNrEtapa());

            // persistindo no bd
            $conexao->execTransacaoArray($arrayCmds);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao excluir Etapa de Avaliação do Edital.", $e);
        }
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_eap_etapa_aval_proc where PRC_ID_PROCESSO = '$idProcesso'";
    }

    private function getSqlReordenacaoNrEtapa() {
        // recuperando etapas
        $etapas = self::buscarEtapaAvalPorProc($this->PRC_ID_PROCESSO);

        // gerando sqls
        $i = 1;
        $ret = array();
        foreach ($etapas as $etapa) {
            if ($etapa->EAP_ID_ETAPA_AVAL_PROC == $this->EAP_ID_ETAPA_AVAL_PROC) {
                continue; // pulando item excluído
            }

            // gerando as sqls
            $ret [] = "UPDATE tb_eap_etapa_aval_proc
                    SET EAP_NR_ETAPA_AVAL = '$i'
                    where PRC_ID_PROCESSO = $etapa->PRC_ID_PROCESSO
                    and  EAP_ID_ETAPA_AVAL_PROC = $etapa->EAP_ID_ETAPA_AVAL_PROC";

            $i++;
        }

        return $ret;
    }

    /**
     * Essa funçao retorna um array com as sqls responsaveis por resetar a avaliaçao
     * de uma etapa.
     * 
     * Essa funçao deve ser usada sempre que houver alteraçao nas regras de avaliaçao 
     * de um edital.
     * 
     * @param int $idProcesso
     * @param int $idEtapaAvalProc
     * @param boolean $avalAuto Flag que informa se a alteração realizada força o reset da avaliação automática
     * @return array - Array com sqls 
     */
    public static function getArraySqlResetAvalProcEtapa($idProcesso, $idEtapaAvalProc, $avalAuto) {
        $ret = array();

        // setando etapa de selecao como pendente
        $ret [] = EtapaSelProc::getStrSqlClassifPenProcEtapa($idProcesso, $idEtapaAvalProc);

        if ($avalAuto) {
            // resetando avaliaçao automatica dos candidatos
            $ret [] = InscricaoProcesso::getStrSqlSitAutoPendente($idProcesso);
        }

        return $ret;
    }

    /**
     * Verifica se uma etapa possui a categoria de informações complementares. Se possuir, 
     * retorna o ID da categoria especial. Caso contrário, é retornado FALSE.
     * 
     * @param int $idProcesso
     * @param int $idEtapaAval
     * @return boolean|int
     * @throws NegocioException
     */
    public static function possuiCatInfComp($idProcesso, $idEtapaAval) {
        try {
            $cat = CategoriaAvalProc::buscarCatAValPorProcIdEtapa($idProcesso, $idEtapaAval, CategoriaAvalProc::$INT_TIPO_INF_COMP);
            if ($cat === NULL) {
                return FALSE;
            }
            return $cat[0]->getCAP_ID_CATEGORIA_AVAL();
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar se Etapa possui categoria de Informações Complementares.", $e);
        }
    }

    public function getNomeEtapa() {
        return self::$NM_INICIAL_ETAPA . " {$this->EAP_NR_ETAPA_AVAL}";
    }

    public function getNomeEtapaArq() {
        return "_e{$this->EAP_NR_ETAPA_AVAL}";
    }

    public function podeExcluir() {
        if ($this->flagPermiteExcluir === NULL) {
            try {
                // carregando flag
                $this->carregaPodeExcluir();
            } catch (Exception $e) {
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
                return;
            }
        }
        return $this->flagPermiteExcluir;
    }

    public static function permiteCriarEtapa($processo) {
        try {
            // verificando permissão de edicao de processo
            if ($processo->permiteEdicao(TRUE)) {
                // tem alguma chamada configurada
                return EtapaSelProc::contarEtapaPorProcNum($processo->getPRC_ID_PROCESSO()) == 0;
            } else {
                return false; // processo não deixa editar
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar permissão de criação de Etapa de Avaliação.", $e);
        }
    }

    /**
     * Essa funçao carrega a regra de permissao de Exclusao.
     * 
     * Regra: Nao pode existir nenhum item ligado a etapa, a saber, 
     * Categoria de Avaliaçao, Etapas de Seleçao e Macros.
     * @throws NegocioException
     */
    private function carregaPodeExcluir() {
        try {
            // verificando se o processo permite edicao
            $proc = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            if (!$proc->permiteEdicao()) {
                return FALSE;
            }

            // verificando quantidade de categoria
            $qtCat = CategoriaAvalProc::contarCatAvalPorProcNrEtapa($this->PRC_ID_PROCESSO, $this->EAP_NR_ETAPA_AVAL);

            // verificando quantidade de etapas de selecao
            $qtEtSel = EtapaSelProc::contarEtapaPorProcNum($this->PRC_ID_PROCESSO);

            // verificando quantidade de macros
            $qtMacro = MacroConfProc::contarMacroPorProcEtapa($this->PRC_ID_PROCESSO, $this->EAP_ID_ETAPA_AVAL_PROC);

            // para excluir, nao pode ter nenhum desses itens
            $this->flagPermiteExcluir = $qtCat == 0 && $qtEtSel == 0 && $qtMacro == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar possibilidade de exclusão de etapa.", $e);
        }
    }

    public function podeAlterar() {
        if ($this->flagPermiteAlterar === NULL) {
            try {
                // carregando flag
                $this->carregaPodeAlterar();
            } catch (Exception $e) {
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
                return;
            }
        }
        return $this->flagPermiteAlterar;
    }

    /**
     * Essa funçao carrega a regra de permissao de Alteracao.
     * 
     * Regra: Nao pode existir uma etapa de selecao finalizada.
     * @throws NegocioException
     */
    private function carregaPodeAlterar() {
        try {
            // verificando se o processo permite edicao
            $processo = Processo::buscarProcessoPorId($this->PRC_ID_PROCESSO);
            if (!$processo->permiteEdicao(TRUE)) {
                return FALSE;
            }

            // verificando quantidade de etapas de selecao finalizadas 
            $qtEtSel = EtapaSelProc::contarEtapaPorProcNum($this->PRC_ID_PROCESSO, NULL, $this->EAP_NR_ETAPA_AVAL, EtapaSelProc::$SIT_FINALIZADA);
            // para editar, nao pode ter nenhuma etapa nesse caso
            $this->flagPermiteAlterar = $qtEtSel == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar possibilidade de alteração de etapa.", $e);
        }
    }

    public static function podeAlterarUltimaEtapa($idProcesso) {
        try {
            // verificando se o processo permite edicao
            $processo = Processo::buscarProcessoPorId($idProcesso);
            if (!$processo->permiteEdicao()) {
                return FALSE;
            }

            // verificando quantidade de etapas de selecao finalizadas 
            $qtEtSel = EtapaSelProc::contarEtapaPorProcNum($idProcesso, NULL, self::getNrUltimaEtapaAvalProc($idProcesso), EtapaSelProc::$SIT_FINALIZADA);
            // para editar, nao pode ter nenhuma etapa nesse caso
            return $qtEtSel == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar possibilidade de alteração da última etapa.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getEAP_ID_ETAPA_AVAL_PROC() {
        return $this->EAP_ID_ETAPA_AVAL_PROC;
    }

    /* End of get EAP_ID_ETAPA_AVAL_PROC */

    function getPRC_ID_PROCESSO() {
        return $this->PRC_ID_PROCESSO;
    }

    /* End of get PRC_ID_PROCESSO */

    function getEAP_NR_ETAPA_AVAL() {
        return $this->EAP_NR_ETAPA_AVAL;
    }

    /* End of get EAP_NR_ETAPA_AVAL */



    /* SET FIELDS FROM TABLE */

    function setEAP_ID_ETAPA_AVAL_PROC($value) {
        $this->EAP_ID_ETAPA_AVAL_PROC = $value;
    }

    /* End of SET EAP_ID_ETAPA_AVAL_PROC */

    function setPRC_ID_PROCESSO($value) {
        $this->PRC_ID_PROCESSO = $value;
    }

    /* End of SET PRC_ID_PROCESSO */

    function setEAP_NR_ETAPA_AVAL($value) {
        $this->EAP_NR_ETAPA_AVAL = $value;
    }

    /* End of SET EAP_NR_ETAPA_AVAL */
}

?>
