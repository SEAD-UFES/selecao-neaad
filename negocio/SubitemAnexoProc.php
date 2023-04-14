<?php

/**
 * tb_sap_subitem_anexo_proc class
 * This class manipulates the table SubitemAnexoProc
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
 * callback nas impressoes de subitens do tipo radio
 * @param SubitemAnexoProc $subitem
 * @return array no modelo [id, nome]
 */
function getIdNomeSubitem($subitem) {
    return array($subitem->getSAP_DS_SUBITEM(), $subitem->getSAP_NM_SUBITEM());
}

class SubitemAnexoProc {

    private $SAP_ID_SUBITEM;
    private $IAP_ID_ITEM;
    private $SAP_NR_ORDEM_EXIBICAO;
    private $SAP_NM_SUBITEM;
    private $SAP_DS_SUBITEM;
    private $SAP_TP_SUBITEM;
    private $SAP_SUBITEM_OBRIGATORIO;
    private $SAP_NR_MAX_CARACTER;
    public static $TIPO_SUBITEM_RADIO = 'R';
    public static $TIPO_SUBITEM_CHECKBOX = 'C';
    public static $TIPO_SUBITEM_TEXTO = 'T';
    public static $ORDEM_UNICA = 1;

    public static function getDsTipoSubitem($tipo) {
        if ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_RADIO) {
            return "Radio";
        }

        if ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_CHECKBOX) {
            return "CheckBox";
        }

        if ($tipo == SubitemAnexoProc::$TIPO_SUBITEM_TEXTO) {
            return "Texto";
        }
    }

    public function getIdElementoHtml() {
        if ($this->SAP_TP_SUBITEM == SubitemAnexoProc::$TIPO_SUBITEM_RADIO) {
            return "radiosub" . $this->SAP_ID_SUBITEM;
        }
        if ($this->SAP_TP_SUBITEM == SubitemAnexoProc::$TIPO_SUBITEM_CHECKBOX) {
            return "checkboxsub" . $this->SAP_ID_SUBITEM;
        }
        if ($this->SAP_TP_SUBITEM == SubitemAnexoProc::$TIPO_SUBITEM_TEXTO) {
            return "textosub" . $this->SAP_ID_SUBITEM;
        }
        throw new NegocioException("ID do elemento não definido para o tipo especificado.");
    }

    public function isObrigatorio() {
        return $this->SAP_SUBITEM_OBRIGATORIO == FLAG_BD_SIM;
    }

    /**
     * 
     * @param array $subitens
     * @return string
     */
    public static function getTipoSubitens($subitens) {
        if (count($subitens) == 0) {
            return FALSE;
        }
        return $subitens[0]->SAP_TP_SUBITEM;
    }

    /**
     * Informa se o subitem é resposta múltipla ou não
     * 
     */
    public function isRespostaMultipla() {
        return $this->SAP_TP_SUBITEM == self::$TIPO_SUBITEM_CHECKBOX;
    }

    public function isMultiplaEscolha() {
        return $this->SAP_TP_SUBITEM == self::$TIPO_SUBITEM_CHECKBOX || $this->SAP_TP_SUBITEM == self::$TIPO_SUBITEM_RADIO;
    }

    /**
     * 
     * @param SubitemAnexoProc $listaSubitemAnexoProc Array de subitens
     */
    public static function subitemRespostaMultipla($listaSubitemAnexoProc) {
        return !Util::vazioNulo($listaSubitemAnexoProc) && $listaSubitemAnexoProc[0]->isRespostaMultipla();
    }

    /**
     * 
     * @param SubitemAnexoProc $listaSubitemAnexoProc Array de subitens
     */
    public static function subitemMultiplaEscolha($listaSubitemAnexoProc) {
        return !Util::vazioNulo($listaSubitemAnexoProc) && $listaSubitemAnexoProc[0]->isMultiplaEscolha();
    }

    /**
     * @param string $descricao
     * @param SubitemAnexoProc $subitens - Array de subitens
     * @return SubitemAnexoProc
     */
    public static function getSubitemPorDescricao($descricao, $subitens) {
        foreach ($subitens as $subitem) {
            if ($subitem->SAP_DS_SUBITEM == $descricao) {
                return $subitem;
            }
        }
        return NULL;
    }

    /* Construtor padrão da classe */

    public function __construct($SAP_ID_SUBITEM, $IAP_ID_ITEM, $SAP_NR_ORDEM_EXIBICAO, $SAP_NM_SUBITEM, $SAP_DS_SUBITEM, $SAP_TP_SUBITEM, $SAP_SUBITEM_OBRIGATORIO, $SAP_NR_MAX_CARACTER) {
        $this->SAP_ID_SUBITEM = $SAP_ID_SUBITEM;
        $this->IAP_ID_ITEM = $IAP_ID_ITEM;
        $this->SAP_NR_ORDEM_EXIBICAO = $SAP_NR_ORDEM_EXIBICAO;
        $this->SAP_NM_SUBITEM = $SAP_NM_SUBITEM;
        $this->SAP_DS_SUBITEM = $SAP_DS_SUBITEM;
        $this->SAP_TP_SUBITEM = $SAP_TP_SUBITEM;
        $this->SAP_SUBITEM_OBRIGATORIO = $SAP_SUBITEM_OBRIGATORIO;
        $this->SAP_NR_MAX_CARACTER = $SAP_NR_MAX_CARACTER;
    }

    public static function addSqlRemoverPorProcesso($idProcesso, &$vetSqls) {
        $vetSqls [] = "delete from tb_sap_subitem_anexo_proc 
                       where IAP_ID_ITEM in
                       (select IAP_ID_ITEM from tb_iap_item_anexo_proc
                       where GAP_ID_GRUPO_PROC in
                       (select GAP_ID_GRUPO_PROC from tb_gap_grupo_anexo_proc where PRC_ID_PROCESSO = '$idProcesso'))";
    }

    public static function getSubitemAnexoProcPadrao() {
        return new SubitemAnexoProc(NULL, "", SubitemAnexoProc::$ORDEM_UNICA, "", "", "", FALSE, "");
    }

    public function getSqlCriacaoSubitem() {
        //tratando dados
        $this->SAP_DS_SUBITEM = NGUtil::trataCampoStrParaBD(str_replace(" ", "", removerAcentos($this->SAP_DS_SUBITEM)));
        $this->SAP_NM_SUBITEM = NGUtil::trataCampoStrParaBD($this->SAP_NM_SUBITEM);
        $this->SAP_TP_SUBITEM = NGUtil::trataCampoStrParaBD($this->SAP_TP_SUBITEM);

        $this->IAP_ID_ITEM = NGUtil::trataCampoStrParaBD($this->IAP_ID_ITEM);
        $this->SAP_NR_ORDEM_EXIBICAO = NGUtil::trataCampoStrParaBD($this->SAP_NR_ORDEM_EXIBICAO);
        $this->SAP_NR_MAX_CARACTER = NGUtil::trataCampoStrParaBD($this->SAP_NR_MAX_CARACTER);

        $this->SAP_SUBITEM_OBRIGATORIO = ($this->SAP_SUBITEM_OBRIGATORIO != NULL && $this->SAP_SUBITEM_OBRIGATORIO) ? NGUtil::trataCampoStrParaBD(FLAG_BD_SIM) : NGUtil::trataCampoStrParaBD(FLAG_BD_NAO);

        // gerando insert
        return "insert into tb_sap_subitem_anexo_proc(IAP_ID_ITEM, SAP_NR_ORDEM_EXIBICAO, SAP_NM_SUBITEM, SAP_DS_SUBITEM, SAP_TP_SUBITEM, SAP_SUBITEM_OBRIGATORIO, SAP_NR_MAX_CARACTER)
                values($this->IAP_ID_ITEM, $this->SAP_NR_ORDEM_EXIBICAO, $this->SAP_NM_SUBITEM, $this->SAP_DS_SUBITEM, $this->SAP_TP_SUBITEM, $this->SAP_SUBITEM_OBRIGATORIO, $this->SAP_NR_MAX_CARACTER)";
    }

    public static function getSqlExcluirPorGrupo($idGrupoAnexoProc) {
        return "delete from tb_sap_subitem_anexo_proc where IAP_ID_ITEM IN 
                (select distinct(IAP_ID_ITEM) from tb_iap_item_anexo_proc where  GAP_ID_GRUPO_PROC = '$idGrupoAnexoProc')";
    }

    public static function buscarSubitemPorItem($idItem) {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            $sql = "select 
                    SAP_ID_SUBITEM,
                    IAP_ID_ITEM,
                    SAP_NR_ORDEM_EXIBICAO,
                    SAP_NM_SUBITEM,
                    SAP_DS_SUBITEM,
                    SAP_TP_SUBITEM,
                    SAP_SUBITEM_OBRIGATORIO,
                    SAP_NR_MAX_CARACTER
                from
                    tb_sap_subitem_anexo_proc
                where
                    IAP_ID_ITEM = '$idItem'
                order by SAP_NR_ORDEM_EXIBICAO";

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
                $itemTemp = new SubitemAnexoProc($dados['SAP_ID_SUBITEM'], $dados['IAP_ID_ITEM'], $dados['SAP_NR_ORDEM_EXIBICAO'], $dados['SAP_NM_SUBITEM'], $dados['SAP_DS_SUBITEM'], $dados['SAP_TP_SUBITEM'], $dados['SAP_SUBITEM_OBRIGATORIO'], $dados['SAP_NR_MAX_CARACTER']);

                //adicionando no vetor
                $vetRetorno[$i] = $itemTemp;
            }

            return $vetRetorno;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar subitens do item.", $e);
        }
    }

    /* GET FIELDS FROM TABLE */

    function getSAP_ID_SUBITEM() {
        return $this->SAP_ID_SUBITEM;
    }

    /* End of get SAP_ID_SUBITEM */

    function getIAP_ID_ITEM() {
        return $this->IAP_ID_ITEM;
    }

    /* End of get IAP_ID_ITEM */

    function getSAP_NR_ORDEM_EXIBICAO() {
        return $this->SAP_NR_ORDEM_EXIBICAO;
    }

    /* End of get SAP_NR_ORDEM_EXIBICAO */

    function getSAP_NM_SUBITEM() {
        return $this->SAP_NM_SUBITEM;
    }

    /* End of get SAP_NM_SUBITEM */

    function getSAP_DS_SUBITEM() {
        return $this->SAP_DS_SUBITEM;
    }

    /* End of get SAP_DS_SUBITEM */

    function getSAP_TP_SUBITEM() {
        return $this->SAP_TP_SUBITEM;
    }

    /* End of get SAP_TP_SUBITEM */

    function getSAP_SUBITEM_OBRIGATORIO() {
        return $this->SAP_SUBITEM_OBRIGATORIO;
    }

    /* End of get SAP_SUBITEM_OBRIGATORIO */

    function getSAP_NR_MAX_CARACTER() {
        return $this->SAP_NR_MAX_CARACTER;
    }

    /* End of get SAP_NR_MAX_CARACTER */



    /* SET FIELDS FROM TABLE */

    function setSAP_ID_SUBITEM($value) {
        $this->SAP_ID_SUBITEM = $value;
    }

    /* End of SET SAP_ID_SUBITEM */

    function setIAP_ID_ITEM($value) {
        $this->IAP_ID_ITEM = $value;
    }

    /* End of SET IAP_ID_ITEM */

    function setSAP_NR_ORDEM_EXIBICAO($value) {
        $this->SAP_NR_ORDEM_EXIBICAO = $value;
    }

    /* End of SET SAP_NR_ORDEM_EXIBICAO */

    function setSAP_NM_SUBITEM($value) {
        $this->SAP_NM_SUBITEM = $value;
    }

    /* End of SET SAP_NM_SUBITEM */

    function setSAP_DS_SUBITEM($value) {
        $this->SAP_DS_SUBITEM = $value;
    }

    /* End of SET SAP_DS_SUBITEM */

    function setSAP_TP_SUBITEM($value) {
        $this->SAP_TP_SUBITEM = $value;
    }

    /* End of SET SAP_TP_SUBITEM */

    function setSAP_SUBITEM_OBRIGATORIO($value) {
        $this->SAP_SUBITEM_OBRIGATORIO = $value;
    }

    /* End of SET SAP_SUBITEM_OBRIGATORIO */

    function setSAP_NR_MAX_CARACTER($value) {
        $this->SAP_NR_MAX_CARACTER = $value;
    }

    /* End of SET SAP_NR_MAX_CARACTER */
}

?>
