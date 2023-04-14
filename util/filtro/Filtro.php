<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Filtro
 *
 * @author Estevão Costa
 */
abstract class Filtro {

    private $inicioDados;
    private $qtdeDadosPag;
    private $variante;
    private $manipularCookie;
    protected $urlInicial;
    protected $urlIdCookie;
    // armazena parametros para cookie
    protected $vetParamsCookie;
    // armazena nome dos parametros da tela: SEMPRE RECUPERAR ESSE DADO VIA GET!
    protected static $paramsTela;
    protected $filtroAberto = FALSE; // flag para informar se o filtro será exibido aberto ou não 
    protected $accordionAberto = TRUE; // flag para informar se o accordion será exibido aberto ou não 
    private $nmParamIni;
    private $nmParamQtde;
    // processamento interno
    private $primeiraChamada = FALSE; // Flag que informa se é a primeira chamada da tela (sem uso do filtro);
    // mais processamento interno
    private static $PARAM_MD5_COOKIE = "refSEAD90"; // Parâmetro com MD5 do cookie para validação de alteração de dados; 
    private $md5Anterior = ""; // Armazena o MD5 do cookie anterior recuperado pelo sistema
    private $md5Atual = ""; // Armazena o MD5 do cookie atual para comparação com a gravação anterior

    /**
     * 
     * @param array $vet Array Array com os parâmetros do filtro. Geralmente é o vetor $_GET
     * @param string $urlInicial Url inicial do filtro
     * @param string $variante Variante a ser utilizado no filtro de busca. Padrão é string vazia
     * @param boolean $manipularCookie Informa se é para manipular cookie. Este parâmetro sobrepõe a
     *                                 configuração do usuário, portanto, use com sabedoria
     */

    public function __construct($vet, $urlInicial, $variante = "", $manipularCookie = NULL) {
        if (!is_array($vet)) {
            die("Parâmetro incorreto na chamada de filtro.");
        }

        // manipulação de cookie
        if ($manipularCookie !== NULL) {
            $this->manipularCookie = $manipularCookie;
        }

        // carregando campos importantes
        $this->urlInicial = $urlInicial;
        $this->urlIdCookie = preg_replace("/[^A-Z]/i", "", $this->urlInicial);
        $this->variante = $variante;
        $this->nmParamIni = "inicio" . $this->variante;
        $this->nmParamQtde = "qtde" . $this->variante;

        // recuperando cookie, se existir e for necessário
        $this->pegaCookie($this->getNmCookie());

        $this->inicioDados = $this->pegaParametro($vet, $this->nmParamIni);
        $this->inicioDados = $this->inicioDados >= 0 ? $this->inicioDados : 0;


        if (isset($vet[$this->nmParamQtde])) {
            $this->qtdeDadosPag = $vet[$this->nmParamQtde];
        } else {
            $this->qtdeDadosPag = Paginacao::getQtdePorPagina();
        }
        $this->qtdeDadosPag = $this->qtdeDadosPag > 1 ? $this->qtdeDadosPag : Paginacao::getQtdePorPagina();


        //acertando início e salvando no cookie
        $this->inicioDados = (int) ($this->inicioDados / $this->qtdeDadosPag) * $this->qtdeDadosPag;
        $this->vetParamsCookie[$this->nmParamIni] = $this->inicioDados;


        // iterando nos campos e recuperando parametros
        foreach ($this->getParamsTela() as $param) {
            $this->vetParamsCookie[$param] = $this->pegaParametro($vet, $param);
        }

        // recuperando parâmetro MD5 anterior do cookie, se necessário
        $this->getMD5AnteriorCookie();

        // salvando cookie, se solicitado
        $this->salvaCookie($this->getNmCookie());

        // definindo se é primeira chamada
        $this->setPrimeiraChamada($vet);
    }

    private function isManipularCookie() {
        if ($this->manipularCookie === NULL) {
            $idLogado = getIdUsuarioLogado();
            // não está logado
            if (Util::vazioNulo($idLogado)) {
                $this->manipularCookie = FALSE;
            } else {
                // tentando pegar do BD
                try {
                    $conf = ConfiguracaoUsuario::buscarConfiguracaoPorUsuario($idLogado);
                    $this->manipularCookie = NGUtil::mapeamentoBooleano($conf->getCFU_FL_SALVAR_FILTRO());
                } catch (Exception $e) {
                    error_log("ID Usuário: $idLogado " . $e); // registrando erro...
                    $this->manipularCookie = FALSE;
                }
            }
        }
        return $this->manipularCookie;
    }

    public function getNmParamInicio() {
        return $this->nmParamIni;
    }

    private function setPrimeiraChamada($vet) {
        $this->primeiraChamada = implode("", array_keys($vet)) == $this->strVetGetPrimeiraChamada();
        if ($this->isManipularCookie()) {
            // mais uma analise
//            print_r("ATUAL: " . $this->md5Atual . " ANTERIOR: " . $this->md5Anterior);
            $this->primeiraChamada = $this->primeiraChamada && $this->md5Anterior != $this->md5Atual;
        }
    }

    private function getMD5AnteriorCookie() {
        if ($this->isManipularCookie() && isset($this->vetParamsCookie[self::$PARAM_MD5_COOKIE])) {
            $this->md5Anterior = $this->vetParamsCookie[self::$PARAM_MD5_COOKIE];
            // desabilitando 
            unset($this->vetParamsCookie[self::$PARAM_MD5_COOKIE]);
        }
    }

    private function pegaParametro($vet, $nmParam) {
        // tenta buscar no vetor
        if (isset($vet[$nmParam])) {
            return $vet[$nmParam];
        }

        // tenta no cookie
        if ($this->isManipularCookie() && isset($this->vetParamsCookie[$nmParam])) {
            return $this->vetParamsCookie[$nmParam];
        }

        // retorna nulo
        return NULL;
    }

    private function pegaCookie($nmCookie) {
        // Só pega o cookie se for solicitado
        if ($this->isManipularCookie()) {
            // verificando casos de falha
            if (Util::vazioNulo($nmCookie) || !isset($_COOKIE[$nmCookie])) {
                // apenas criando vetor de cookie em branco
                $this->vetParamsCookie = array();
            } else {
                // recuperando cookie e processando
                $params = explode(",", $_COOKIE[$nmCookie]);
                foreach ($params as $param) {
                    $temp = explode("$", $param);
                    $this->vetParamsCookie[$temp[0]] = $temp[1];
                }
            }
        }
    }

    public function salvaCookie($nmCookie) {
        if ($this->isManipularCookie()) {

            // montando string com parametros
            $cookieStr = "";
            $strCalcMD5 = "";

            $chaves = array_keys($this->vetParamsCookie);
            foreach ($chaves as $chave) {
                $cookieStr = adicionaConteudoVirgula($cookieStr, "$chave\${$this->vetParamsCookie[$chave]}", FALSE);
                if ($chave != $this->nmParamIni) {
                    $strCalcMD5 = adicionaConteudoVirgula($strCalcMD5, "$chave\${$this->vetParamsCookie[$chave]}", FALSE);
                }
            }

            // salvando MD5
            $this->md5Atual = md5($strCalcMD5);
//            echo "ATUAL: $this->md5Atual  ANTERIOR: $this->md5Anterior";
//            exit;

            $cookieStr = adicionaConteudoVirgula($cookieStr, self::$PARAM_MD5_COOKIE . "\$$this->md5Atual", FALSE);

            // salvando cookie
            setcookie($nmCookie, $cookieStr);
        }
    }

    /**
     * Retorna o nome do Cookie
     * @return string
     */
    public function getNmCookie() {
        return $this->getCompNmCookie() . getIdUsuarioLogado() . $this->urlIdCookie . $this->variante;
    }

// Funçoes Abstratas

    /**
     * Funcao que deve retornar a URL de acesso ja com parametros do filtro
     * 
     * @return string - URL completa
     */
    public abstract function getUrlParametros();

    /**
     * Funcao que deve retornar um complemento para o nome do Cookie, identificando
     * o cookie como da classe.
     * 
     * @return string - Complemento para o nome do Cookie.
     */
    protected abstract function getCompNmCookie();

    /**
     * Funcao que deve retornar um array com os parametros de tela do filtro
     * 
     * @return array - Array com a lista de parametros
     */
    protected abstract function getParamsTela();

    /**
     * Função que deve retornar a string com o "implode" dos parâmetros que aparecem 
     * no vetor $_GET na primeira chamada da página (sem utilização dos filtros). 
     * 
     * Essa string será usada para verificar se é a primeira chamada da página
     * 
     * @return string String do "implode" das chaves de $_GET na primeira chamada da página (sem filtros) 
     */
    protected abstract function strVetGetPrimeiraChamada();

// Fim funções abstratas

    /**
     * Função que seta se o accordion deve aparecer aberto ou fechado
     * 
     * @param int $qtItensRetornado Quantidade de itens retornados na busca
     */
    public function setAccordionAberto($qtItensRetornado) {
        $this->accordionAberto = $this->primeiraChamada || $qtItensRetornado == 0;
    }

    public function getInicioDados() {
        // primeira chamada sempre retorna zero!
        if ($this->primeiraChamada) {
            return 0;
        }

        // MD5 correto? Filtro sem alteração
        if (!$this->isManipularCookie() || ($this->isManipularCookie() && $this->md5Anterior == $this->md5Atual)) {
            return $this->inicioDados;
        }
        // deve-se zerar o início
        return 0;
    }

    public function getQtdeDadosPag() {
        return $this->qtdeDadosPag;
    }

    public function getUrlInicial() {
        return $this->urlInicial;
    }

    public function setInicioDados($inicioDados) {
        $this->inicioDados = $inicioDados;
    }

    public function setQtdeDados($qtdeDados) {
        $this->qtdeDadosPag = $qtdeDados;
    }

    public function getFiltroAberto() {
        return $this->filtroAberto;
    }

    public function getAccordionAberto() {
        return $this->accordionAberto;
    }

}

?>
