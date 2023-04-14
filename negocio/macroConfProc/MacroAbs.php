<?php

/**
 * Classe abstrata que estabelece as regras basicas para uma macro de configuracao de 
 * processo.
 * 
 *
 * @author estevao
 */
global $CFG;
require_once $CFG->rpasta . "/negocio/MacroConfProc.php";

// carregando classes filhas
MacroAbs::carregaClassesFilhas();

class ParamMacro {

    private $id;
    private $tipo;
    private $nome;
    private $valor;
    private $obrigatorio;
    private $editavel;
    private $listaVal;
    private $cargaAjax;
    private $pertenceChave; // informa se um parâmetro pertence à chave de duplicidade da macro
    private $callBackValorParam; // armazena funcao para recuperar descrição do parâmetro
    private $listaValidadorExtra; // armazena validadores extras para o parâmetro
// lista de possiveis tipo
    public static $TIPO_DECIMAL = 'D';
    public static $TIPO_INTEIRO = 'I';
    public static $TIPO_LISTA = 'L';
    public static $TIPO_LISTA_CALL_BACK = 'F';
// algumas tags
    public static $NM_PARAM_CHAVES = "paramChaves";
    public static $ID_CAMPO_SUMARIZA_PARAM_CHAVES = "sumarizaParamChaves";
// validadores extras
    public static $VALIDADOR_MIN_1 = 0; // Impoẽ valor mínimo 1 para o parâmetro

    /**
     * 
     * @param int $id
     * @param int $tipo Deve ser um dos tipos definidos nesta classe
     * @param string $nome
     * @param boolean $pertenceChave Informa se o parâmetro pertence à chave de duplicidade da Macro. Por padrão é falso. 
     * Se True, então é permitido criar a macro com vários valores diferentes do parâmetro em questão.
     * @param array|string $listaIdDsVal Array na forma [IdTipo1 => DescricaoTipo1, IdTipo2 => DescricaoTipo2, ...]
     * ou uma string com a funcao callback a ser chamada, no caso de tipo callback.
     * @param string $callBackDsValor Funcao callback que deve ser chamada no caso da necessidade de obter a descrição do 
     * parâmetro. Só funciona para o tipo listaCallBack.
     * @param boolean $obrigatorio Informa se o parâmetro em questão é obrigatório.
     * @param boolean $cargaAjax Diz se o parâmetro deve ser carregado via Ajax. Só funciona para o tipo listaCallBack.
     */

    public function __construct($id, $tipo, $nome, $pertenceChave = FALSE, $listaIdDsVal = NULL, $callBackDsValor = NULL, $obrigatorio = TRUE, $cargaAjax = FALSE) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->nome = $nome;
        $this->obrigatorio = $obrigatorio;
        $this->listaVal = $this->tipo == self::$TIPO_LISTA || $tipo == self::$TIPO_LISTA_CALL_BACK ? $listaIdDsVal : NULL;
        $this->callBackValorParam = $tipo == self::$TIPO_LISTA_CALL_BACK ? $callBackDsValor : NULL;
        $this->valor = NULL;
        $this->editavel = TRUE;
        $this->cargaAjax = $this->tipo == self::$TIPO_LISTA_CALL_BACK ? $cargaAjax : FALSE;
        $this->pertenceChave = $pertenceChave;
        $this->listaValidadorExtra = array();
    }

    public function addValidadorExtra($validador) {
        if ($validador !== self::$VALIDADOR_MIN_1) {
            return; // nada a fazer
        }
        $this->listaValidadorExtra [] = $validador;
    }

    public function getId() {
        return $this->id;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getEditavel() {
        return $this->editavel;
    }

    public function setEditavel($editavel) {
        $this->editavel = $editavel;
    }

    public function getValor() {
        return $this->valor;
    }

    public function setValor($valor) {
        $this->valor = $valor;
    }

    public function getStrParametro($ocultaNome = FALSE) {
        $ret = !$ocultaNome ? "$this->nome " : "";

        // valor nao definido
        if (Util::vazioNulo($this->valor)) {
            return $ret . htmlentities("<Não Def.>");
        }

        if ($this->tipo == self::$TIPO_DECIMAL) {
            $ret .= NGUtil::formataDecimal($this->valor);
        } elseif ($this->tipo == self::$TIPO_INTEIRO) {
            $ret .= $this->valor;
        } elseif ($this->tipo == self::$TIPO_LISTA) {
            $ret .= $this->listaVal[$this->valor];
        } elseif ($this->tipo == self::$TIPO_LISTA_CALL_BACK) {
            if (Util::vazioNulo($this->callBackValorParam)) {
                $ret .= "(Cód $this->valor)";
            } else {
                $ret .= call_user_func($this->callBackValorParam, $this->valor);
            }
        }
        return $ret;
    }

    private function getIdDivInput() {
        return "divApres$this->id";
    }

    private function getIdDivEspera() {
        return "divEsperaProx$this->id";
    }

    /**
     * Esta função retorna o HTML da div responsável pelo processamento do parâmetro 
     * 
     * @param array $arrayParamExt Array na forma [idParam => vlParam]
     * @param boolean $divEspera Informa se deve ser incluído uma div de espera para próximas cargas
     * @return string HTML com a div responsável por exibir o input
     */
    public function getDivInput($arrayParamExt, $divEspera = FALSE) {
        $style = $this->exibirInput() ? "style='display: none'" : "";

        $ret = "<div id='{$this->getIdDivInput()}' $style class='form-group'>
                    <label class='control-label col-xs-12 col-sm-4 col-md-4'>{$this->getNome()}</label>
                    <div class='col-xs-12 col-sm-8 col-md-8'>
                        {$this->getHTMLInput($arrayParamExt)}";

// adicionando div de espera
        if ($divEspera) {
            $ret .= " <div id='{$this->getIdDivEspera()}' style='display: none'>
                            <span>Aguarde, Carregando...</span>
                        </div>";
        }

        $ret.= "</div>
                </div>";

        return $ret;
    }

    /**
     * Essa funçao retorna o HTML correspondente ao parametro
     * 
     * @param array $arrayParamExt Array na forma [idParam => vlParam]
     * @return string
     */
    private function getHTMLInput($arrayParamExt) {
        $ret = "";
// caso decimal
        if ($this->tipo == self::$TIPO_DECIMAL) {
            $ret = "<input class='form-control' class='tudo-normal' type='text' name='$this->id' id='$this->id' size='6' maxlength='6' value='$this->valor' {$this->getHTMLObrigatorio()}>";
        } elseif ($this->tipo == self::$TIPO_INTEIRO) {
            $ret = "<input class='form-control' class='tudo-normal' type='text' name='$this->id' id='$this->id' size='6' maxlength='6' value='$this->valor' {$this->getHTMLObrigatorio()}>";
        } elseif ($this->tipo == self::$TIPO_LISTA) {
            $ret = $this->getHTMLLista($this->listaVal);
        } elseif ($this->tipo == self::$TIPO_LISTA_CALL_BACK) {
            $lista = $this->exibirInput() ? array() : call_user_func($this->listaVal, $arrayParamExt);
            $ret = $this->getHTMLLista($lista);
        }
        return $ret;
    }

    /**
     * Retorna se o input deve ser exibido ou não.
     * 
     * @return boolean
     */
    public function exibirInput() {
        return $this->cargaAjax && Util::vazioNulo($this->valor);
    }

    /**
     * 
     * @param array $lista Array na forma [idOpcao, dsOpcao]
     * @return string
     */
    private function getHTMLLista($lista) {
        $ret = "";
        $codSel = ID_SELECT_SELECIONE;
        $dsSel = DS_SELECT_SELECIONE;
        $semEdicao = !$this->editavel ? "disabled" : "";
        $ret .= "<select {$this->getHTMLObrigatorio()} class='form-control' $semEdicao id='$this->id' name='$this->id'>";
        $ret .= "<option value=$codSel>$dsSel</option>";
        $vetChaves = array_keys($lista);
        for ($i = 0; $i < sizeof($vetChaves); $i++) {
            $nome = $lista[$vetChaves[$i]];
            $id = $vetChaves[$i];
            if ($this->valor != null && $id == $this->valor) {
                $ret .= "<option value='$id' selected>$nome</option>";
            } else {
                $ret.= "<option value='$id'>$nome</option>";
            }
        }
        $ret .= "</select>";

        return $ret;
    }

    /**
     * 
     * @param ParamMacro $listaParam Array de parametros
     * @param array $arrayParamExt - Array na forma [idParam -> ValorParam] 
     * @return string Script com a validaçao dos itens
     */
    public static function getScriptValidacao($listaParam, $arrayParamExt) {
        $ret = "";

        foreach ($listaParam as $param) {
            $tempRegra = $tempMsg = "";
            $virgula = FALSE;

// verificando validador extra
            $tempValExtra = $param->getScriptValidadorExtra();
            if ($tempValExtra[0] != "") {
                $tempRegra .= $tempValExtra[0];
                $tempMsg .= $tempValExtra[1];
                $virgula = TRUE;
            }

// parametro obrigatorio
            if ($param->obrigatorio) {
                $tempRegra .= ($virgula ? "," : "") . "required: true";
                $tempMsg .= ($virgula ? "," : "") . "required: 'Campo obrigatório.'";
                $virgula = TRUE;
            }

// tipo inteiro
            if ($param->tipo == self::$TIPO_INTEIRO) {
                $tempRegra .= ($virgula ? "," : "") . "digits: true";
                $tempMsg .= ($virgula ? "," : "") . "digits: 'Por favor, insira apenas números.'";
                $virgula = TRUE;
            }

// tipo decimal exige valor minimo, se nao tiver ja uma definicao
            if ($param->tipo == self::$TIPO_DECIMAL && array_search(self::$VALIDADOR_MIN_1, $param->listaValidadorExtra) === FALSE) {
                $tempRegra .= ($virgula ? "," : "") . "min: 0.01";
                $tempMsg .= ($virgula ? "," : "") . "min: 'Este campo deve ser maior que zero.'";
                $virgula = TRUE;
            }

// inserindo regras no retorno
            if ($tempRegra != "") {
                $ret .= "$( '#$param->id' ).rules( 'add', {
                         $tempRegra,
                         messages: {
                         $tempMsg 
                        }});";
            }
        }

        return $ret;
    }

    private function getScriptValidadorExtra() {
        $retRegra = "";
        $retMsg = "";
        $virgula = FALSE;
        foreach ($this->listaValidadorExtra as $validador) {
            if ($validador === self::$VALIDADOR_MIN_1) {
                $retRegra .= ($virgula ? "," : "") . "min: 1.00";
                $retMsg .= ($virgula ? "," : "") . "min: 'Este campo deve ser maior ou igual a 1.'";
                $virgula = TRUE;
            }
        }
        return array($retRegra, $retMsg);
    }

    /**
     * 
     * @param ParamMacro $listaParam Array de parametros
     * @param array $arrayParamExt - Array na forma [idParam -> ValorParam] 
     * @return string Script avulso para complementaçao dos itens
     */
    public static function getScriptAvulso($listaParam, $arrayParamExt) {
        $ret = "";
        $arrayParamInt = array();
        $idAnt = NULL;

// recuperando funcoes de pos carga
        $ret .= self::getFuncoesPosCargaAjax($listaParam);

        foreach ($listaParam as $param) {
// mascara decimal
            if ($param->tipo == self::$TIPO_DECIMAL) {
                $ret.= "addMascaraDecimal('$param->id');";
            }

// mascara inteiro
            if ($param->tipo == self::$TIPO_INTEIRO) {
                $ret .= "$(\"#$param->id\") . mask(\"9?99999\");";
            }

// carga ajax
            if ($param->isCargaAjax()) {
                $ret.= $param->getFuncParamAjax($arrayParamExt, $arrayParamInt);
                $ret.= $param->getGatilhoParamAjax($arrayParamExt, $arrayParamInt, $idAnt);
            }

// adicionando parâmetro interno no vetor e marcando id anterior
            $arrayParamInt [] = $param->id;
            $idAnt = $param->id;
        }

        return $ret;
    }

    /**
     * 
     * @param ParamMacro $listaParam Array de parametros
     */
    public static function getSpanSumarizaParamChave($listaParam) {
        $ret = "";
        $separar = FALSE;
        $i = 0;
        foreach ($listaParam as $param) {
            if ($param->pertenceChave) {
                $ret.= ($separar ? ($i > 0 ? " + '" : "") . MacroConfProc::$SEPARADOR_PARAM : "'") . $param->id . MacroConfProc::$SEPARADOR_VALOR . "' + $(\"#$param->id\").val()";
                $separar = TRUE;

                $i++;
            }
        }
        return "<span style='display: none' id='" . self::$ID_CAMPO_SUMARIZA_PARAM_CHAVES . "'>$ret</span>";
    }

    /**
     * 
     * @param ParamMacro $listaParam Array de Parâmetros ajax
     * @return string String com funções
     */
    private static function getFuncoesPosCargaAjax($listaParam) {
// criando estrutura para funcao
        $matrizDependencia = array();
        foreach ($listaParam as $param) {
            if ($param->isCargaAjax()) {
                foreach (array_keys($matrizDependencia) as $id) {
                    $matrizDependencia[$id][] = $param;
                }
                if (!isset($matrizDependencia[$param->id])) {
                    $matrizDependencia[$param->id] = array();
                }
            }
        }


// criando funcoes
        $ret = "";
        foreach ($listaParam as $param) {
            if ($param->isCargaAjax()) {
                $ret .= "function {$param->getNmFuncaoPosCargaParamAjax()}()
                {";
                foreach ($matrizDependencia[$param->id] as $dependente) {
                    $ret .= "{$dependente->getNmVarGatilhoParamAjax()}();";
                }
                $ret .= "return;}";
            }
        }
        return $ret;
    }

    private function getFuncParamAjax($arrayParamExt, $arrayParamInt) {
        $nmCargaSelect = MacroAbs::$CARGA_SELECT_PARAM_AJAX;
        $nmParamCallBack = MacroAbs::$PARAM_AJAX_CALLBACK;
        $nmListaParam = MacroAbs::$PARAM_AJAX_LISTA_PARAM;
        return "function {$this->getNmFuncaoParamAjax()}()
                {
                    return {'cargaSelect': \"$nmCargaSelect\", '$nmParamCallBack': \"$this->listaVal\", '$nmListaParam': {$this->codificaParamAjax($arrayParamExt, $arrayParamInt)}};
                }";
    }

    private function getGatilhoParamAjax($arrayParamExt, $arrayParamInt, $idAnt) {
// validando caso de anterior
        if ($idAnt == NULL) {
            throw new NegocioException("Parâmetros da Macro configurado incorretamente: Parâmetro AJAX sem pai.");
        }
        $valor = $this->valor != NULL ? $this->valor : 'null';
        return "var {$this->getNmVarGatilhoParamAjax()} = adicionaGatilhoAjaxSelectIn(\"$idAnt\", getIdSelectSelecione(), \"{$this->getIdDivEspera()}\", \"{$this->getIdDivInput()}\", \"$this->id\", $valor, {$this->getNmFuncaoParamAjax()}, {$this->getNmFuncaoPosCargaParamAjax()}, false, 'block');";
    }

    private function codificaParamAjax($arrayParamExt, $arrayParamInt) {
        $ret = "";
        $separar = FALSE;

// param ext
        foreach ($arrayParamExt as $idParam => $vlParam) {
            if (!Util::vazioNulo($vlParam)) {
                $ret .= ($separar ? MacroConfProc::$SEPARADOR_PARAM : "'") . $idParam . MacroConfProc::$SEPARADOR_VALOR . $vlParam;
                $separar = TRUE;
            }
        }

// param int
        $i = 0;
        foreach ($arrayParamInt as $idParam) {
            $ret.= ($separar ? ($i > 0 ? " + '" : "") . MacroConfProc::$SEPARADOR_PARAM : "'") . $idParam . MacroConfProc::$SEPARADOR_VALOR . "' + $(\"#$idParam\").val()";
            $separar = TRUE;

            $i++;
        }

// finalizacao
        if ($i == 0) {
            $ret .= "'";
        }

        return $ret;
    }

    /**
     * Essa função decodifica uma string de parâmetros, gerando um vetor nos moldes aceito
     * pelas funções de callback.
     * 
     * @param string $stringCodificada String com a lista de parâmetros codificados
     * @return array Array com parâmetros decodificados
     */
    public static function decodificaParamAjax($stringCodificada) {
        $ret = array();

        if (Util::vazioNulo($stringCodificada)) {
            return $ret;
        }

// separando parâmetros
        $listaParam = explode(MacroConfProc::$SEPARADOR_PARAM, $stringCodificada);

// navegando nos parâmetros
        foreach ($listaParam as $strParam) {
            $temp = explode(MacroConfProc::$SEPARADOR_VALOR, $strParam);
            if (!isset($temp[0]) || !isset($temp[1])) {
                throw new NegocioException("Erro ao decodificar parâmetros AJAX da Macro: Codificação incorreta.");
            }

// adicionando no retorno
            $ret[$temp[0]] = $temp[1];
        }

        return $ret;
    }

    private function getNmFuncaoParamAjax() {
        return "getParams$this->id";
    }

    private function getNmVarGatilhoParamAjax() {
        return "gatilho$this->id";
    }

    private function getNmFuncaoPosCargaParamAjax() {
        return "posCarga$this->id";
    }

    private function getHTMLObrigatorio() {
        return $this->obrigatorio ? "required" : "";
    }

    public function isObrigatorio() {
        return $this->obrigatorio;
    }

    public function isChave() {
        return $this->pertenceChave;
    }

    public function isCargaAjax() {
        return $this->tipo == self::$TIPO_LISTA_CALL_BACK ? $this->cargaAjax : FALSE;
    }

}

abstract class MacroAbs {

    private static $listaMacro = NULL;
    private static $mapaTipoInterface = NULL;
    private static $listaClasses = NULL;
    private static $mapaClasses = NULL; // armazena mapa: [IdClasse, NmClasse]
    private static $myReflection = NULL;
    protected $tpMacro; // tipo de macro
    private $paramExt; // array para armazenar parâmetros externos; Nunca chamar diretamente.
    protected $parametros; // para armazenamento dos parametros
    protected $qtParametros; // para armazenamento dos parametros
// código dos parâmetros externos
    public static $_PARAM_ID_PROCESSO = 'idProcesso';
    public static $_PARAM_ID_ETAPA_AVAL = 'idEtapaAval';
// definicao de tags para carga ajax de parâmetros
    public static $CARGA_SELECT_PARAM_AJAX = "cargaSelectParamMacro";
    public static $PARAM_AJAX_CALLBACK = "funcCallBack";
    public static $PARAM_AJAX_LISTA_PARAM = "listaParam";
// processamento interno
    private static $TIPO_MACRO_ABS = '-1';
    private static $paramNULL = "null";

// metodos abstratos
    /**
     * Seja especifico ao definir este nome: Deve ser unico
     * 
     * Este nome deve ser amigavel ao usuario, remetendo a funcao da macro.
     * 
     * @return string Nome fantasia da Macro, amigável ao usuário
     */
    public abstract function getNmFantasia();

    /**
     * Seja especifico ao definir este nome: Deve ser unico
     * 
     * Este nome NÃO deve conter espacos, pois e utilizado para identificacao
     * unica da Macro
     * 
     * @return string String sem espaços que identifica a macro
     */
    public abstract function getIdMacro();

    /**
     * Esta funçao deve retornar uma lista com o id dos parametros da Macro. 
     * Atente para o nome sucinto e bem definido.
     * 
     * @return array Array com o id dos parâmetros da Macro
     */
    public abstract function getListaIdParam();

    /**
     * Esta funçao deve retornar uma lista com os objetos parametros da Macro. 
     * 
     * @return ParamMacro Lista de objetos parâmetros
     * 
     */
    public abstract function getListaParam();

    /**
     * Essa funcao deve retornar um objeto ParamMacro, com os 
     * dados do parametro passado no argumento.
     * 
     * @param string $idParam Id do Parametro que se deseja obter os dados.
     * @return ParamMacro
     */
    public abstract function getParamPorId($idParam);

    /**
     * Essa função deve retornar a quantidade de parametros que a macro possui
     * 
     * @return int Número de parâmetros da Macro.
     */
    public abstract function getQtdeParametros();

    /**
     * 
     * @param int $tpMacro
     * @param array $paramExt - Array na forma [idParam -> ValorParam] 
     */
    public function __construct($tpMacro, $paramExt = NULL) {
        $this->tpMacro = $tpMacro;
        $this->carregaParamExt($paramExt);
    }

    /**
     * Essa função retorna o html dos parâmetros da macro
     * 
     * @param array $arrayParamExt - Array na forma [idParam -> ValorParam] 
     * @return string HTML dos parâmetros
     */
    public function getHTMLParametros($arrayParamExt) {
        $ret = "";

        // loop nos parametros para criacao
        $listaParam = $this->getListaIdParam();
        for ($i = 0; $i < $this->getQtdeParametros(); $i++) {

            $param = $this->getParamPorId($listaParam[$i]);

            // adicionando valor do parâmetro no array externo
            $arrayParamExt[$param->getId()] = $param->getValor();

// verificando necessidade de adicionar div de espera
            $divEspera = isset($listaParam[$i + 1]) && $this->getParamPorId($listaParam[$i + 1])->isCargaAjax();

            $ret .= $param->getDivInput($arrayParamExt, $divEspera);
        }

// incluindo funcao que sumariza os parâmetros chaves
        $ret .= ParamMacro::getSpanSumarizaParamChave($this->getListaParam());

        return $ret;
    }

    /**
     * Essa função retorna uma string com o script de validação dos parâmetros da macro
     * 
     * @param array $arrayParamExt - Array na forma [idParam -> ValorParam] 
     * @return string com script de validação.
     */
    public function getScriptValidacaoParametros($arrayParamExt) {
        return ParamMacro::getScriptValidacao($this->getListaParam(), $arrayParamExt);
    }

    /**
     * Essa função retorna uma string com os scripts avulsos dos parâmetros da macro
     * 
     * @param array $arrayParamExt - Array na forma [idParam -> ValorParam] 
     * @return string com script avulso
     */
    public function getScriptAvulsoParametros($arrayParamExt) {
        return ParamMacro::getScriptAvulso($this->getListaParam(), $arrayParamExt);
    }

    private function carregaParamExt($paramExt) {
        $this->paramExt = array();

        $this->paramExt[self::$_PARAM_ID_PROCESSO] = $paramExt != NULL && isset($paramExt[self::$_PARAM_ID_PROCESSO]) ? $paramExt[self::$_PARAM_ID_PROCESSO] : self::$paramNULL;
        $this->paramExt[self::$_PARAM_ID_ETAPA_AVAL] = $paramExt != NULL && isset($paramExt[self::$_PARAM_ID_ETAPA_AVAL]) ? $paramExt[self::$_PARAM_ID_ETAPA_AVAL] : self::$paramNULL;
    }

    /**
     * 
     * @param string $idParam
     * @return mixed Valor do Parâmetro procurado. Pode ser nulo
     * @throws NegocioException
     */
    protected function getValorParamExterno($idParam) {
        if (!isset($this->paramExt[$idParam])) {
            throw new NegocioException("Parâmetro externo inexistente.");
        }
        return $this->paramExt[$idParam] != self::$paramNULL ? $this->paramExt[$idParam] : NULL;
    }

    /**
     * Essa funcao cria uma instancia da Macro passada como argumento.
     * 
     * @param int $tpMacro
     * @param int $idMacro
     * @param array $paramExt Array com parâmetros externos na forma [idParam => vlParam];
     * @return MacroAbs
     * @throws NegocioException
     */
    public static function instanciaMacro($tpMacro, $idMacro, $paramExt = NULL) {
        $mapaTemp = self::getMapaClasses();

// validando
        if (!isset($mapaTemp[$idMacro])) {
            throw new NegocioException("Macro inexistente.");
        }

// criando a instancia
        $ref = new ReflectionClass($mapaTemp[$idMacro]);
        return $ref->newInstance($tpMacro, $paramExt);
    }

    /**
     * Essa função monta o vetor de parâmetros externos a ser usado na criação de instâncias de macro
     * 
     * @param int $idProcesso
     * @param int $idEtapaAVal
     * @return array Array com o vetor a ser utilizado para criação da instância
     */
    public static function montaVetParamExt($idProcesso = NULL, $idEtapaAVal = NULL) {
        return array(self::$_PARAM_ID_PROCESSO => $idProcesso, self::$_PARAM_ID_ETAPA_AVAL => $idEtapaAVal);
    }

    /**
     * Esta função carrega o valor dos parâmetros codificados na $strParam na macro $objMacro.
     * Após chamar esta função, o valor de cada parâmetro da macro estará setado de acordo com o valor informado na string codificada.
     * 
     * @param MacroAbs $objMacro
     * @param string $strParam - String com parâmetros codificados na forma: "dsParam1=vlParam1;dsParam2=vlParam2;..."
     */
    public static function carregaValorParam($objMacro, $strParam) {
        if (Util::vazioNulo($strParam)) {
            return; // nada a fazer
        }

// decodificando parametros
        $temp = explode(MacroConfProc::$SEPARADOR_PARAM, $strParam);
        $listaParam = "";
        foreach ($temp as $param) {
            $part = explode(MacroConfProc::$SEPARADOR_VALOR, $param);
            $listaParam[$part[0]] = $part[1];
        }

        // percorrendo parâmetros e atualizando valor
        foreach ($objMacro->getListaParam() as $param) {
            if (isset($listaParam[$param->getId()])) {
                $param->setValor($listaParam[$param->getId()]);
            }
        }
    }

    public static function getListaMacro($tpMacro) {
//         print_r(self::getMapaClasses());

        if (self::$listaMacro == NULL || !isset(self::$listaMacro[$tpMacro])) {
            // carregando
            $ret = array();

            // verificando quem implementa o determinado tipo
            foreach (self::getListaClasses() as $nmClasse) {
                if (self::classeImplementa($nmClasse, self::getMapaTipoInterface()[$tpMacro])) {
                    $ref = new ReflectionClass($nmClasse);
                    $inst = $ref->newInstance($tpMacro);
                    $ret [self::refIdMacro($ref, $inst)] = self::refNmFantasia($ref, $inst);
                }
            }

            self::$listaMacro[$tpMacro] = $ret;
        }
        return self::$listaMacro[$tpMacro];
    }

    private static function classeImplementa($nmClasse, $nmInterface) {
        // analisando classe
        $ref = new ReflectionClass(($nmClasse));

        // retornando se classe implementa o que e pedido
        return $ref->implementsInterface($nmInterface);
    }

    public static function carregaClassesFilhas() {
        global $CFG;

        // carregando classes filhas
        $nmDirFilhas = "$CFG->rpasta/negocio/macroConfProc";
        $dirFilhas = new DirectoryIterator($nmDirFilhas);
        $filtro = new RegexIterator($dirFilhas, "/php/");
        self::$listaClasses = array();
        foreach ($filtro as $arq) {
            // gerando o required
            require_once "$nmDirFilhas/$arq";

            // validando se a classe pode ser incluida na lista de classes
            $nmClasse = str_replace(".php", "", $arq);
            if (self::validaClasse($nmClasse)) {
                // incluindo na lista de Classes
                self::$listaClasses [] = $nmClasse;
            }
        }
    }

    private static function carregaMapaClasses() {
        self::$mapaClasses = array();
        // loop nas classes
        foreach (self::getListaClasses() as $nmClasse) {
            $ref = new ReflectionClass($nmClasse);
            $inst = $ref->newInstance(self::$TIPO_MACRO_ABS);
            self::$mapaClasses[self::refIdMacro($ref, $inst)] = $nmClasse;
        }
    }

    private static function refNmFantasia($ref, $inst) {
        $metodo = $ref->getMethod("getNmFantasia");
        return $metodo->invoke($inst);
    }

    private static function refIdMacro($ref, $inst) {
        $metodo = $ref->getMethod("getIdMacro");
        return $metodo->invoke($inst);
    }

    private static function validaClasse($nmClasse) {
        // classe existe?
        if (class_exists($nmClasse)) {
            // entao, verificando se implementa a classe de macro
            $ref = new ReflectionClass($nmClasse);
            return $ref->isSubclassOf("MacroAbs");
        }
    }

    public static function getMyReflection() {
        if (self::$myReflection == NULL) {
            // carga
            self::$myReflection = new ReflectionClass("MacroAbs");
        }
        return self::$myReflection;
    }

    public static function getMapaTipoInterface() {
        if (self::$mapaTipoInterface == NULL) {
            // carregando 
            self::$mapaTipoInterface = array(MacroConfProc::$TIPO_CRIT_CLASSIFICACAO => 'MacroCritClassificacao', MacroConfProc::$TIPO_CRIT_DESEMPATE => 'MacroCritDesempate', MacroConfProc::$TIPO_CRIT_ELIMINACAO => 'MacroCritEliminacao', MacroConfProc::$TIPO_CRIT_SELECAO => 'MacroCritSelecao', MacroConfProc::$TIPO_CRIT_SELECAO_RESERVA => 'MacroCritCadReserva');
        }
        return self::$mapaTipoInterface;
    }

    private static function getListaClasses() {
        if (self::$listaClasses == NULL) {
            // carregando... 
            self::carregaClassesFilhas();
        }
        return self::$listaClasses;
    }

    private static function getMapaClasses() {
        if (self::$mapaClasses == NULL) {
            // carregando... 
            self::carregaMapaClasses();
        }
        return self::$mapaClasses;
    }

}

interface MacroCritEliminacao {

    /**
     * Esta função deve retornar uma string informando o motivo de eliminação, personalizada, baseada
     * no valor dos parâmetros já carregado no objeto, e de forma amigável ao usuário
     * 
     * @return string Motivo da eliminação
     */
    function getDsMotivoEliminacao();

    /**
     * Esta função deve retornar uma string contendo as condições que devem ser adicionadas à sql pré-definida
     * para aplicação do critério em questão, baseando-se no valor dos parâmetros já carregados no objeto.
     * 
     * SQLInicial = update tb_ipr_inscricao_processo ipr 
     *                  set IPR_ST_INSCRICAO = '$stEliminadoAuto',
     *                  IPR_DS_OBS_NOTA = $motivoEliminacao
     *                  where
     *                  pch_id_chamada = '{$etapa->getPCH_ID_CHAMADA()}'
     *                  and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
     * 
     * Tabelas disponíveis para uso na sql sem menção: tb_ipr_inscricao_processo ipr 
     * 
     * 
     * @param int $idEtapaAval ID da etapa que está sendo avaliada. Quando esse parâmetro é nulo, significa que é o resultado final.
     * 
     * @return string Complemento da sql padrão para aplicação do critério (sem instrução inicial where ou and)
     */
    function getSqlCondAplicaCriterio($idEtapaAval = NULL);
}

interface MacroCritClassificacao {

    /**
     * Esta função deve retornar uma string contendo o order by que deve ser aplicado à sql de classificação 
     * de forma a garantir o critério.
     * 
     * SQLInicial = UPDATE tb_ipr_inscricao_processo ipr
     *               SET IPR_NR_CLASSIFICACAO_CAND = @counter := @counter + 1
     *               where (IPR_ST_INSCRICAO IS NULL or (IPR_ST_INSCRICAO <> '$eliminado' and IPR_ST_INSCRICAO <> '$eliminadoAuto'))
     *               and  pch_id_chamada = '{$etapa->getPCH_ID_CHAMADA()}'
     *               ORDER BY 
     * 
     * 
     * Tabelas disponíveis para uso na sql sem menção: tb_ipr_inscricao_processo ipr 
     * 
     * @return string order by a ser adicionado na sql inicial de classificação
     */
    function getSqlOrderByAplicaCriterio();
}

interface MacroCritDesempate {

    /**
     * Esta função deve retornar uma string contendo um adendo ao order by de classificação que deve ser aplicado à sql de classificação 
     * de forma a garantir o critério.
     * 
     * 
     * Tabelas disponíveis para uso na sql sem menção: tb_ipr_inscricao_processo ipr 
     * 
     * @return string Adendo ao order by de classificação a ser adicionado na sql inicial de classificação
     */
    function getSqlAddOrderByAplicaCriterio();
}

interface MacroCritSelecao {

    /**
     * Esta função deve adicionar ao $arrayCmds as sqls necessárias à aplicação do critério em questão,
     * baseando-se no valor dos parâmetros já carregados no objeto.
     * 
     * SQLInicial = update tb_ipr_inscricao_processo ipr 
     *                   set 
     *                       IPR_CDT_SELECIONADO = '$flagCdtSel'
     *                  where
     *                       PCH_ID_CHAMADA = '{$etapa->getPCH_ID_CHAMADA()}'
     *                       and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
     * 
     * WhereRestritivo: PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
     *                   and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
     * 
     * 
     * @param ProcessoChamada $chamada Chamada a ser processada
     * @param string $sqlInicial SQL Inicial a ser utilizada para composição das sqls
     * @param string $whereRestritivo SQL com parte da cláusula where que limita a ação das sqls construidas internamente. Esta string deve ser adicionada
     * primeiramente à cláusula where de todas as sqls internas criadas nesta função
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado as sqls
     * 
     */
    function addSqlsAplicaCriterioEtapa($chamada, $sqlInicial, $whereRestritivo, &$arrayCmds);
}

interface MacroCritCadReserva {

    /**
     * Esta função deve adicionar ao $arrayCmds as sqls necessárias à aplicação do critério em questão,
     * baseando-se no valor dos parâmetros já carregados no objeto.
     * 
     * SQLInicial:  update tb_ipr_inscricao_processo ipr 
     *                   set 
     *                       IPR_CDT_SELECIONADO = '$flagCdtSel',
     *                       IPR_ST_INSCRICAO = '$stInscCadReserva'
     *                  where
     *                       PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
     *                       and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
     *                       and (IPR_CDT_SELECIONADO IS NULL or IPR_CDT_SELECIONADO != '$flagCdtSel')
     * 
     * WhereRestritivo: PCH_ID_CHAMADA = '{$chamada->getPCH_ID_CHAMADA()}'
     *                   and (IPR_ST_INSCRICAO IS NULL or IPR_ST_INSCRICAO = '$stInscOk')
     *                   and (IPR_CDT_SELECIONADO IS NULL or IPR_CDT_SELECIONADO != '$flagCdtSel')
     * 
     * 
     * @param ProcessoChamada $chamada Chamada a ser processada
     * @param string $sqlInicial SQL Inicial a ser utilizada para composição das sqls
     * @param string $whereRestritivo SQL com parte da cláusula where que limita a ação das sqls construidas internamente. Esta string deve ser adicionada
     * primeiramente à cláusula where de todas as sqls internas criadas nesta função
     * @param array $arrayCmds Array de comandos, onde deve ser adicionado as sqls
     * 
     */
    function addSqlsAplicaCriterio($chamada, $sqlInicial, $whereRestritivo, &$arrayCmds);
}

function _cargaAjaxParamMacro($funcCallBack, $listaParam) {
    if (Util::vazioNulo($funcCallBack) || Util::vazioNulo($listaParam)) {
        throw new NegocioException("Parâmetros incorretos para carga de Parâmetros de Macro via AJAX.");
    }
// decofidicando parâmetros e chamando callback
    return call_user_func($funcCallBack, ParamMacro::decodificaParamAjax($listaParam));
}
