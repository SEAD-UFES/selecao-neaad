<?php

/**
 * tb_iap_item_anexo_proc class
 * This class manipulates the table ItemAnexoProc
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * @Modificaçao      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaaddev
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 24/10/2013
 * */

/**
 * Funçao que retorna array de identificaçao para ser usada como
 * callback nas impressoes de itens do tipo radio
 * @param ItemAnexoProc $itemAnexoProc
 * @return array no modelo [id, nome]
 */
function getIdNomeItem($itemAnexoProc) {
    return array($itemAnexoProc->getIAP_ID_ITEM(), $itemAnexoProc->getIAP_NM_ITEM());
}

class ItemAnexoProc {

    private $IAP_ID_ITEM;
    private $GAP_ID_GRUPO_PROC;
    private $IAP_NR_ORDEM_EXIBICAO;
    private $IAP_NM_ITEM;
    private $IAP_DS_ITEM;
    private $IAP_TP_ITEM;
    private $IAP_ITEM_OBRIGATORIO;
    private $IAP_NR_MAX_CARACTER;
    public static $TIPO_CHECKBOX = 'C';
    public static $TIPO_CHECKBOX_COM_COMP = 'D';
    public static $TIPO_RADIO = 'R';
    public static $TIPO_RADIO_COM_COMP = 'S';
    // tipos de complemento para tela
    public static $TIPO_TEL_TEXTO = 'T';
    public static $TIPO_TEL_CHECKBOX = 'B';
    public static $TIPO_TEL_RADIO = 'R';

    public static function getDsTipoItem($tipo) {
        if ($tipo == ItemAnexoProc::$TIPO_CHECKBOX) {
            return "Checkbox";
        }
        if ($tipo == ItemAnexoProc::$TIPO_CHECKBOX_COM_COMP) {
            return "Checkbox com complemento";
        }
        if ($tipo == ItemAnexoProc::$TIPO_RADIO) {
            return "Radio";
        }
        if ($tipo == ItemAnexoProc::$TIPO_RADIO_COM_COMP) {
            return "Radio com complemento";
        }
    }

    /**
     * Retorna o tipo do item de acordo com alguns parâmetros 
     * @param boolean $respMultipla
     * @param boolean $temComplemento
     * @return char
     */
    public static function getTpItem($respMultipla, $temComplemento) {
        if ($respMultipla && $temComplemento) {
            return self::$TIPO_CHECKBOX_COM_COMP;
        }
        if ($respMultipla && !$temComplemento) {
            return self::$TIPO_CHECKBOX;
        }

        if (!$respMultipla && $temComplemento) {
            return self::$TIPO_RADIO_COM_COMP;
        }
        if (!$respMultipla && !$temComplemento) {
            return self::$TIPO_RADIO;
        }
    }

    /**
     * 
     * @param SubitemAnexoProc $listaSubItem Array de itens
     */
    public static function getTpCompTela($listaSubItem) {
        if (Util::vazioNulo($listaSubItem)) {
            return "";
        }
        $tpSubItem = $listaSubItem[0]->getSAP_TP_SUBITEM();
        if ($tpSubItem == SubitemAnexoProc::$TIPO_SUBITEM_RADIO) {
            return ItemAnexoProc::$TIPO_TEL_RADIO;
        }
        if ($tpSubItem == SubitemAnexoProc::$TIPO_SUBITEM_CHECKBOX) {
            return ItemAnexoProc::$TIPO_TEL_CHECKBOX;
        }
        if ($tpSubItem == SubitemAnexoProc::$TIPO_SUBITEM_TEXTO) {
            return ItemAnexoProc::$TIPO_TEL_TEXTO;
        }
    }

    /**
     * Informa se a pergunta é resposta múltipla ou não
     * 
     */
    public function isRespostaMultipla() {
        return $this->IAP_TP_ITEM == self::$TIPO_CHECKBOX || $this->IAP_TP_ITEM == self::$TIPO_CHECKBOX_COM_COMP;
    }

    /**
     * 
     * @param ItemAnexoProc $listaItemAnexoProc Array de itens
     */
    public static function itemRespostaMultipla($listaItemAnexoProc) {
        return !Util::vazioNulo($listaItemAnexoProc) && $listaItemAnexoProc[0]->isRespostaMultipla();
    }

    public function getDsRespostaMultipla() {
        return $this->isRespostaMultipla() ? "Sim" : "Não";
    }

    public static function getDsTipoCompTelaItem($tipo) {
        if ($tipo == ItemAnexoProc::$TIPO_TEL_CHECKBOX) {
            return "Resposta Múltipla";
        }
        if ($tipo == ItemAnexoProc::$TIPO_TEL_RADIO) {
            return "Resposta Única";
        }
        if ($tipo == ItemAnexoProc::$TIPO_TEL_TEXTO) {
            return "Texto";
        }
    }

    public static function getListaTpCompDsTipoTelaItem() {
        $ret = array(
            ItemAnexoProc::$TIPO_TEL_TEXTO => ItemAnexoProc::getDsTipoCompTelaItem(ItemAnexoProc::$TIPO_TEL_TEXTO),
            ItemAnexoProc::$TIPO_TEL_RADIO => ItemAnexoProc::getDsTipoCompTelaItem(ItemAnexoProc::$TIPO_TEL_RADIO),
            ItemAnexoProc::$TIPO_TEL_CHECKBOX => ItemAnexoProc::getDsTipoCompTelaItem(ItemAnexoProc::$TIPO_TEL_CHECKBOX));

        return $ret;
    }

    /**
     * Retorna o Id do elemento na montagem do HTML
     * @return string
     */
    public function getIdElementoHtml() {
        if ($this->isRespostaMultipla()) {
            return "checkbox" . $this->IAP_ID_ITEM;
        } else {
            return "radio" . $this->IAP_ID_ITEM;
        }
        throw new NegocioException("Código de ITEM não implementado!");
    }

    public function isObrigatorio() {
        return $this->IAP_ITEM_OBRIGATORIO == FLAG_BD_SIM;
    }

    public function temComplemento() {
        return $this->IAP_TP_ITEM == self::$TIPO_CHECKBOX_COM_COMP || $this->IAP_TP_ITEM == self::$TIPO_RADIO_COM_COMP;
    }

    /* Construtor padrão da classe */

    public function __construct($IAP_ID_ITEM, $GAP_ID_GRUPO_PROC, $IAP_NR_ORDEM_EXIBICAO, $IAP_NM_ITEM, $IAP_DS_ITEM, $IAP_TP_ITEM, $IAP_ITEM_OBRIGATORIO, $IAP_NR_MAX_CARACTER) {
        $this->IAP_ID_ITEM = $IAP_ID_ITEM;
        $this->GAP_ID_GRUPO_PROC = $GAP_ID_GRUPO_PROC;
        $this->IAP_NR_ORDEM_EXIBICAO = $IAP_NR_ORDEM_EXIBICAO;
        $this->IAP_NM_ITEM = $IAP_NM_ITEM;
        $this->IAP_DS_ITEM = $IAP_DS_ITEM;
        $this->IAP_TP_ITEM = $IAP_TP_ITEM;
        $this->IAP_ITEM_OBRIGATORIO = $IAP_ITEM_OBRIGATORIO;
        $this->IAP_NR_MAX_CARACTER = $IAP_NR_MAX_CARACTER;
    }

    public static function getItemAnexoProcPadrao() {
        return new ItemAnexoProc(NULL, "", SubitemAnexoProc::$ORDEM_UNICA, "", "", "", FALSE, "");
    }

    /**
     * 
     * @param int $idGrupo
     * @return \ItemAnexoProc|null
     * @throws NegocioException
     */
    public static function buscarItemPorGrupo($idGrupo) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    IAP_ID_ITEM,
                    GAP_ID_GRUPO_PROC,
                    IAP_NR_ORDEM_EXIBICAO,
                    IAP_NM_ITEM,
                    IAP_DS_ITEM,
                    IAP_TP_ITEM,
                    IAP_ITEM_OBRIGATORIO,
                    IAP_NR_MAX_CARACTER
                FROM
                    tb_iap_item_anexo_proc
                where
                    GAP_ID_GRUPO_PROC = '$idGrupo'
                order by IAP_NR_ORDEM_EXIBICAO";

            //executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            //verificando se retornou alguma linha
            $numLinhas = ConexaoMysql::getNumLinhas($resp);
            if ($numLinhas == 0) {
                //retornando nulo
                return NULL;
            }

            $vetRetorno = array();

            //realizando iteração para recuperar os dados
            for ($i = 0; $i < $numLinhas; $i++) {
                //recuperando linha e criando objeto
                $dados = ConexaoMysql::getLinha($resp);
                $itemTemp = new ItemAnexoProc($dados['IAP_ID_ITEM'], $dados['GAP_ID_GRUPO_PROC'], $dados['IAP_NR_ORDEM_EXIBICAO'], $dados['IAP_NM_ITEM'], $dados['IAP_DS_ITEM'], $dados['IAP_TP_ITEM'], $dados['IAP_ITEM_OBRIGATORIO'], $dados['IAP_NR_MAX_CARACTER']);

                //adicionando no vetor
                $vetRetorno[$i] = $itemTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar itens do grupo.", $e);
        }
    }

    public function getSqlCriacaoItem() {
        //tratando dados
        $this->IAP_DS_ITEM = NGUtil::trataCampoStrParaBD(str_replace(" ", "", removerAcentos($this->IAP_DS_ITEM)));
        $this->IAP_NM_ITEM = NGUtil::trataCampoStrParaBD($this->IAP_NM_ITEM);
        $this->IAP_TP_ITEM = NGUtil::trataCampoStrParaBD($this->IAP_TP_ITEM);

        $this->GAP_ID_GRUPO_PROC = NGUtil::trataCampoStrParaBD($this->GAP_ID_GRUPO_PROC);
        $this->IAP_NR_ORDEM_EXIBICAO = NGUtil::trataCampoStrParaBD($this->IAP_NR_ORDEM_EXIBICAO);
        $this->IAP_NR_MAX_CARACTER = NGUtil::trataCampoStrParaBD($this->IAP_NR_MAX_CARACTER);

        $this->IAP_ITEM_OBRIGATORIO = $this->IAP_ITEM_OBRIGATORIO ? NGUtil::trataCampoStrParaBD(FLAG_BD_SIM) : NGUtil::trataCampoStrParaBD(FLAG_BD_NAO);

        // gerando insert
        return "insert into tb_iap_item_anexo_proc(GAP_ID_GRUPO_PROC, IAP_NR_ORDEM_EXIBICAO, IAP_NM_ITEM, IAP_DS_ITEM, IAP_TP_ITEM, IAP_ITEM_OBRIGATORIO, IAP_NR_MAX_CARACTER)
                values($this->GAP_ID_GRUPO_PROC, $this->IAP_NR_ORDEM_EXIBICAO, $this->IAP_NM_ITEM, $this->IAP_DS_ITEM, $this->IAP_TP_ITEM, $this->IAP_ITEM_OBRIGATORIO, $this->IAP_NR_MAX_CARACTER)";
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        SubitemAnexoProc::addSqlRemoverPorProcesso($idProcesso, $vetSqls);

        $vetSqls [] = "delete from tb_iap_item_anexo_proc
                       where GAP_ID_GRUPO_PROC in
                       (select GAP_ID_GRUPO_PROC from tb_gap_grupo_anexo_proc where PRC_ID_PROCESSO = '$idProcesso')";
    }

    public static function getSqlExcluirPorProcGrupo($idProcesso, $idGrupoAnexoProc) {
        return "delete from tb_iap_item_anexo_proc where GAP_ID_GRUPO_PROC = '$idGrupoAnexoProc' and
                (select count(*) from tb_gap_grupo_anexo_proc where PRC_ID_PROCESSO = '$idProcesso' and GAP_ID_GRUPO_PROC = '$idGrupoAnexoProc') > 0";
    }

    /* GET FIELDS FROM TABLE */

    function getIAP_ID_ITEM() {
        return $this->IAP_ID_ITEM;
    }

    /* End of get IAP_ID_ITEM */

    function getGAP_ID_GRUPO_PROC() {
        return $this->GAP_ID_GRUPO_PROC;
    }

    /* End of get GAP_ID_GRUPO_PROC */

    function getIAP_NR_ORDEM_EXIBICAO() {
        return $this->IAP_NR_ORDEM_EXIBICAO;
    }

    /* End of get IAP_NR_ORDEM_EXIBICAO */

    function getIAP_NM_ITEM() {
        return $this->IAP_NM_ITEM;
    }

    /* End of get IAP_NM_ITEM */

    function getIAP_DS_ITEM() {
        return $this->IAP_DS_ITEM;
    }

    /* End of get IAP_DS_ITEM */

    function getIAP_TP_ITEM() {
        return $this->IAP_TP_ITEM;
    }

    /* End of get IAP_TP_ITEM */

    function getIAP_ITEM_OBRIGATORIO() {
        return $this->IAP_ITEM_OBRIGATORIO;
    }

    /* End of get IAP_ITEM_OBRIGATORIO */

    function getIAP_NR_MAX_CARACTER() {
        return $this->IAP_NR_MAX_CARACTER;
    }

    /* End of get IAP_NR_MAX_CARACTER */



    /* SET FIELDS FROM TABLE */

    function setIAP_ID_ITEM($value) {
        $this->IAP_ID_ITEM = $value;
    }

    /* End of SET IAP_ID_ITEM */

    function setGAP_ID_GRUPO_PROC($value) {
        $this->GAP_ID_GRUPO_PROC = $value;
    }

    /* End of SET GAP_ID_GRUPO_PROC */

    function setIAP_NR_ORDEM_EXIBICAO($value) {
        $this->IAP_NR_ORDEM_EXIBICAO = $value;
    }

    /* End of SET IAP_NR_ORDEM_EXIBICAO */

    function setIAP_NM_ITEM($value) {
        $this->IAP_NM_ITEM = $value;
    }

    /* End of SET IAP_NM_ITEM */

    function setIAP_DS_ITEM($value) {
        $this->IAP_DS_ITEM = $value;
    }

    /* End of SET IAP_DS_ITEM */

    function setIAP_TP_ITEM($value) {
        $this->IAP_TP_ITEM = $value;
    }

    /* End of SET IAP_TP_ITEM */

    function setIAP_ITEM_OBRIGATORIO($value) {
        $this->IAP_ITEM_OBRIGATORIO = $value;
    }

    /* End of SET IAP_ITEM_OBRIGATORIO */

    function setIAP_NR_MAX_CARACTER($value) {
        $this->IAP_NR_MAX_CARACTER = $value;
    }

    /* End of SET IAP_NR_MAX_CARACTER */
}

?>
