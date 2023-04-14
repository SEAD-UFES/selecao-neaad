<?php

/*
 * Classe auxiliar para geraçao de PDF do site SEAD
 */

/**
 * Description of PDFNeaad
 *
 * @author estevao
 */
global $CFG;

//function hex2dec
//returns an associative array (keys: R,G,B) from
//a hex html code (e.g. #3FE5AA)
function hex2dec($couleur = "#000000") {
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R'] = $rouge;
    $tbl_couleur['V'] = $vert;
    $tbl_couleur['B'] = $bleu;
    return $tbl_couleur;
}

//conversion pixel -> millimeter at 72 dpi
function px2mm($px) {
    return $px * 25.4 / 72;
}

function txtentities($html) {
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}

class PDFNeaad extends FPDF {

    private $tituloRelatorio;
    private $tituloEdital;
    private $tituloChamada;
    private $imprimirLogo;
    private $margemDir = 10.0;
    private $margemEsq = 10.0;
    private $margemCima = 10.0;
    private $margemBaixo = 40.0;
    private $divisaoBlocoX; // salva ponto de divisao do bloco de 2 itens no eixo X
    private static $FONTE_PADRAO = 'helvetica';
    public static $FONTE_ITEM_BLOCO = array("B", 8.5);
    public static $FONTE_VALOR_BLOCO = array("", 8.5);
    private static $LARGURA_PAG = 210;
    private static $ALTURA_PAG = 297;
    private $alturaCelAtual; // armazena a altura da celula atualmente em manipulaçao
    private static $MARGEM_BLOCO_ESQ = 5;
    private static $MARGEM_BLOCO_DIR = 5;
    private static $MARGEM_BLOCO_SUP = 1;
    private static $MARGEM_BLOCO_INF = 3;
    private static $ESP_BLOCO_LINHA_TAB = 3;
    //variaveis relativas a conversao de HTML
    var $B;
    var $I;
    var $U;
    var $HREF;
    var $fontList;
    var $issetfont;
    var $issetcolor;
    // variáveis relacionadas a tabela
    var $widths;
    var $aligns;

    function getMargemDir() {
        return $this->margemDir;
    }

    function getMargemEsq() {
        return $this->margemEsq;
    }

    function getMargemCima() {
        return $this->margemCima;
    }

    function getMargemBaixo() {
        return $this->margemBaixo;
    }

    static function getLARGURA_PAG() {
        return self::$LARGURA_PAG;
    }

    static function getALTURA_PAG() {
        return self::$ALTURA_PAG;
    }

    public function __construct($tituloEdital, $tituloChamada, $tituloRelatorio, $orientation = 'P', $unit = 'mm', $size = 'A4') {
        $this->FPDF($orientation, $unit, $size);
        $this->SetMargins($this->margemEsq, $this->margemCima, $this->margemDir);
        $this->tituloEdital = $tituloEdital;
        $this->tituloChamada = $tituloChamada;
        $this->tituloRelatorio = $tituloRelatorio;
        $this->divisaoBlocoX = (PDFNeaad::$LARGURA_PAG - ($this->margemEsq + $this->margemDir)) / 2.0;
        $this->SetAutoPageBreak(true, $this->margemBaixo);
        $this->imprimirLogo = true;

        // inicializaçao html
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';
        $this->fontlist = array('arial', 'times', 'courier', 'helvetica', 'symbol');
        $this->issetfont = false;
        $this->issetcolor = false;

        // Realizando operaçoes basicas
        $this->AliasNbPages();
        $this->AddPage();
    }

    /**
     * Esta função adiciona uma nova página, mudandoo contexto do relatório
     * 
     * @param string $tituloRelatorio Novo título do relatório
     */
    public function addPaginaNovoContexto($tituloRelatorio) {
        $this->tituloRelatorio = $tituloRelatorio;
        $this->imprimirLogo = TRUE;

        $this->AddPage();
    }

    // Cabeçalho da Pagina
    function Header() {
        global $CFG;
        $this->SetTextColor(0);

        if ($this->imprimirLogo) {
            $this->imprimirLogo = FALSE;

            // Escrevendo Logos
            $this->Image("$CFG->rpasta/imagens/sead-logo_impressao.png", $this->margemEsq, $this->margemCima, -300);
            $this->Image("$CFG->rpasta/imagens/ufes-logo_impressao.png", PDFNeaad::$LARGURA_PAG - $this->margemDir - 20, $this->margemCima, -300);

            // movendo cursor
            $this->SetY(28);
        }

        // imprimindo titulo do relatorio
        $this->imprimirTitulo();
        $margem = 20;
        $this->Line($margem, $this->GetY(), (self::$LARGURA_PAG - $margem), $this->GetY());
        $this->Ln(10);
    }

    public static function getFONTE_PADRAO() {
        return self::$FONTE_PADRAO;
    }

    /**
     * Funçao que retorna a largura maxima de uma linha de bloco
     * @param int $qtCols
     * @return int
     */
    public function getTamMaxLinhaBlocoTab($qtCols) {
        return self::$LARGURA_PAG - self::$MARGEM_BLOCO_ESQ - self::$MARGEM_BLOCO_DIR - $this->margemEsq - $this->margemDir - $qtCols;
    }

    private function imprimirTitulo() {
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, '', 13);
        $this->Cell(0, 10, $this->tituloEdital, "", 1, 'C');
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, '', 11);
        $this->Cell(0, 10, mb_strtoupper($this->tituloRelatorio) . " - " . mb_strtoupper($this->tituloChamada), "", 1, 'C');
    }

    /**
     * Funcao que informa se a proxima escrita vai gerar quebra de pagina
     * @param int $tam - Altura ainda a ser escrita
     */
    private function isFimDePagina($tam) {
        //print_r("tamAtual " . ($this->GetY() + $this->margemBaixo + $tam) . " Max " . self::$ALTURA_PAG . "<br/>");
        return $this->GetY() + $this->margemBaixo + $tam > self::$ALTURA_PAG;
    }

    /**
     * Funcao que cria um bloco, ou sessao de dados.
     * @param string $nomeBloco
     * @param boolean $bordaSuperior
     */
    public function InicializaBloco($nomeBloco, $bordaSuperior = TRUE) {
        $tam = 10;

        // verificando necessidade de começar bloco em outra pagina
        if ($this->isFimDePagina($tam)) {
            $this->AddPage();
            $bordaSuperior = true;
        }

        $this->SetFont(PDFNeaad::$FONTE_PADRAO, 'B', $tam);
        $this->Cell(0, $tam, " " . $nomeBloco, $bordaSuperior ? 1 : "LRB", 1, 'L');
        $this->SetY($this->getY() + 2);
    }

    /**
     * Funcao que cria o cabecalho de uma tabela em um bloco de dados.
     * 
     * Note: Se a soma do tamanho das colunas, $vetTamColuna, ultrapassar o limite
     * da pagina, a funcao retornara um erro e finalizara sua execuçao. O mesmo ocorrera
     * se $vetTamColuna nao tiver o mesmo tamanho que $vetTxtColuna.
     * 
     * @param array $vetTxtColuna - Array com texto das colunas
     * @param array $vetTamColuna - Array com tamanho de cada coluna. Parametro
     * opcional. Se este parametro nao for informado, o tamanho de cada coluna sera 
     * a divisao da largura do bloco pelo numero de colunas.
     * 
     * @return array $vetTamColuna - Vetor com o tamanho de cada coluna, para ser usado
     * ao inserir itens na tabela.
     */
    public function CabecalhoBlocoTabela($vetTxtColuna, $vetTamColuna = NULL) {
        $this->alturaCelAtual = 4;

        // verificando tamanho das colunas
        $qtCols = count($vetTxtColuna);
        $tamMaxCols = $this->getTamMaxLinhaBlocoTab($qtCols);
        if (!Util::vazioNulo($vetTamColuna)) {
            $soma = array_sum($vetTamColuna);
            if ($soma > $tamMaxCols) {
                die("Erro: Tamanho das colunas excede a largura do bloco em cabecalhoBlocoTabela");
            }
            if (count($vetTamColuna) != $qtCols) {
                die("Erro: \$vetTamColuna  não é do mesmo tamanho que \$vetTxtColuna em cabecalhoBlocoTabela");
            }
        } else {
            $vetTamColuna = array_fill(0, $qtCols, ($tamMaxCols / $qtCols));
        }

        // imprimindo colunas
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);
        $posX = $this->GetX() + self::$MARGEM_BLOCO_ESQ;
        $i = 0;
        $this->SetY($this->GetY() + PDFNeaad::$MARGEM_BLOCO_SUP);
        foreach ($vetTxtColuna as $coluna) {
            $this->SetX($posX);
            $this->Cell($vetTamColuna[$i], $this->alturaCelAtual, $coluna, "", 0, 'L');

            $posX += $vetTamColuna[$i];
            $i++;
        }
        $this->Ln(2);

        // retornando vetor de tamanho de colunas
        return $vetTamColuna;
    }

    /**
     * Funcao que cria uma linha em uma tabela de um bloco de dados.
     * 
     * ATENCAO: Essa funcao nao trata inconsistencias de quantidade e tamanho
     * de colunas. Procure sempre utilizar para $vetTamColuna o vetor retornado
     * na funcao de criaçao do cabeçalho da tabela.
     * 
     * Note: Se a soma do tamanho das colunas, $vetTamColuna, ultrapassar o limite
     * da pagina, a funcao retornara um erro e finalizara sua execuçao. O mesmo ocorrera
     * se $vetTamColuna nao tiver o mesmo tamanho que $vetTxtColuna.
     * 
     * @param array $vetTxtColuna - Array com texto das colunas
     * @param array $vetTamColuna - Array com tamanho de cada coluna. Parametro
     * opcional. Se este parametro nao for informado, o tamanho de cada coluna sera 
     * a divisao da largura do bloco pelo numero de colunas.
     * @param array $vetAlinhamentoCols - Vetor com o alinhamento de cada coluna. Possiveis valores: L, R
     * 
     */
    public function LinhaBlocoTabela($vetTxtColuna, $vetTamColuna = NULL, $vetAlinhamentoCols = NULL) {
        $this->alturaCelAtual = 4;

        // verificando tamanho das colunas
        $qtCols = count($vetTxtColuna);
        $tamMaxCols = self::$LARGURA_PAG - self::$MARGEM_BLOCO_ESQ - self::$MARGEM_BLOCO_DIR - $qtCols;
        if (!Util::vazioNulo($vetTamColuna)) {
            $soma = array_sum($vetTamColuna);
            if ($soma > $tamMaxCols) {
                die("Erro: Tamanho das colunas excede a largura do bloco em LinhaBlocoTabela");
            }
            if (count($vetTamColuna) != $qtCols) {
                die("Erro: \$vetTamColuna  não é do mesmo tamanho que \$vetTxtColuna em LinhaBlocoTabela");
            }
        } else {
            $vetTamColuna = array_fill(0, $qtCols, ($tamMaxCols / $qtCols));
        }

        // imprimindo colunas
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, PDFNeaad::$FONTE_VALOR_BLOCO[0], PDFNeaad::$FONTE_VALOR_BLOCO[1]);
        $posX = $this->GetX() + self::$MARGEM_BLOCO_ESQ;
        $i = 0;
        $this->SetY($this->GetY() + PDFNeaad::$ESP_BLOCO_LINHA_TAB);
        foreach ($vetTxtColuna as $coluna) {
            $this->SetX($posX);
            $this->Cell($vetTamColuna[$i], $this->alturaCelAtual, $coluna, "", 0, isset($vetAlinhamentoCols[$i]) ? $vetAlinhamentoCols[$i] : 'L');

            $posX += $vetTamColuna[$i];
            $i++;
        }
        $this->Ln(3);

        // retornando vetor de tamanho de colunas
        return $vetTamColuna;
    }

    /**
     * Funcao que imprime um bloco na forma Item -> Valor. A diferença dessa 
     * funcao para a funcao ItemBloco e que esta funcao considera a pagina 
     * inteira para o bloco, ao inves do centro da pagina. 
     * 
     * @param string $nomeItem
     * @param string $valorItem
     * @param int $tamNome - Tamanho do bloco de item
     * @param boolean $valorVermelho Informa se o valor deve ser pintado de vermelho
     */
    public function ItemBlocoUnico($nomeItem, $valorItem, $tamNome = NULL, $valorVermelho = FALSE) {
        $this->alturaCelAtual = 4;

        if ($tamNome == NULL || $tamNome > $this->divisaoBlocoX * 2.0) {
            $tamNome = $this->divisaoBlocoX * 2.0;
        }
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);
        $this->SetX($this->margemEsq);
        $this->Cell(1, 4, "", "", 0, 'L');
        $this->SetY($this->GetY() + PDFNeaad::$MARGEM_BLOCO_SUP);
        $this->SetX($this->margemEsq + PDFNeaad::$MARGEM_BLOCO_ESQ);

        $this->Cell($tamNome, $this->alturaCelAtual, $nomeItem, "", 0, 'L');

        $this->SetFont(PDFNeaad::$FONTE_PADRAO, self::$FONTE_VALOR_BLOCO[0], self::$FONTE_VALOR_BLOCO[1]);
        if ($valorVermelho) {
            $this->SetTextColor(255, 0, 0);
        }
        $this->MultiCell($this->divisaoBlocoX * 2.0 - $tamNome, $this->alturaCelAtual, $valorItem, "", 'L');
        $this->SetTextColor(0);
    }

    /**
     * Funçao que imprime um bloco de texto na horizontal, pulando linha, se necessario
     * 
     * @param string $nomeItem
     * @param string $estiloFonte - '', 'B', 'I', 'U'
     * @param int $margem
     */
    public function ItemBlocoCorrido($nomeItem, $estiloFonte = '', $margem = 0) {
        $this->alturaCelAtual = 4;
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, $estiloFonte, PDFNeaad::$FONTE_ITEM_BLOCO[1]);
        $this->SetX($this->margemEsq);
        $this->Cell(1, 4, "", "", 0, 'L');
        $this->SetY($this->GetY() + PDFNeaad::$MARGEM_BLOCO_SUP);
        $this->SetX($this->margemEsq + PDFNeaad::$MARGEM_BLOCO_ESQ + $margem);

        $this->MultiCell(0, $this->alturaCelAtual, $nomeItem, "", 'L');
    }

    function AcceptPageBreak() {
        return parent::AcceptPageBreak();
    }

    /**
     * Funcao que imprime um bloco na forma Item -> Valor do Item. 
     * Detalhe: Essa funcao considera sempre o centro da pagina para realizar a 
     * divisao dos blocos, ou seja, cada pagina cabe dois blocos desse tipo: Um na
     * esquerda e outro na direita.
     * 
     * @param string $nomeItem
     * @param string $valorItem
     * @param string $alinhamento - 'L' para Esquerda e 'R' para direita
     * @param int $tamNome - Tamanho do bloco de item
     *
     */
    public function ItemBloco($nomeItem, $valorItem, $alinhamento = "L", $tamNome = NULL) {
        if ($tamNome == NULL || $tamNome > $this->divisaoBlocoX) {
            $tamNome = $this->divisaoBlocoX / 2;
        }
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, PDFNeaad::$FONTE_ITEM_BLOCO[0], PDFNeaad::$FONTE_ITEM_BLOCO[1]);
        if ($alinhamento == "L") {
            $this->SetX($this->margemEsq);
            $this->Cell(1, 5, "", "", 0, 'L');
            $this->SetY($this->GetY() + PDFNeaad::$MARGEM_BLOCO_SUP);
            $this->SetX($this->margemEsq + PDFNeaad::$MARGEM_BLOCO_ESQ);
            $this->Cell($tamNome, 4, $nomeItem, "", 0, 'L');
            $this->SetFont(PDFNeaad::$FONTE_PADRAO, self::$FONTE_VALOR_BLOCO[0], self::$FONTE_VALOR_BLOCO[1]);
            $somaTam = ($this->divisaoBlocoX / 2.0) - $tamNome;
            $this->Cell($this->divisaoBlocoX / 2.0 + $somaTam, 4, $valorItem, "", 0, 'L');
        } elseif ($alinhamento == "R") {
            $this->SetX($this->divisaoBlocoX + $this->margemEsq);
            $this->Cell(1, 5, "", "", 0, 'L');
            $this->Cell($tamNome, 4, $nomeItem, "", 0, 'L');
            $this->SetFont(PDFNeaad::$FONTE_PADRAO, self::$FONTE_VALOR_BLOCO[0], self::$FONTE_VALOR_BLOCO[1]);
            $somaTam = ($this->divisaoBlocoX / 2.0) - $tamNome;
            $this->Cell(($this->divisaoBlocoX / 2.0) + $somaTam, 4, $valorItem, "", 1, 'L');
        }
    }

    /**
     * Funcao que realiza a operacao de finalizacao de um bloco
     */
    public function FinalizaBloco() {
        $this->SetY($this->GetY() + PDFNeaad::$MARGEM_BLOCO_INF);
        $this->Line($this->margemEsq, $this->GetY(), PDFNeaad::$LARGURA_PAG - $this->margemDir, $this->GetY());
    }

    /**
     * Retorna o maior tamanho de string presente no array se for usado fonte
     * com estilo e tamanho passado nos parametros
     * 
     * NOTE: ESSA FUNÇAO ALTERA A FONTE ATUALMENTE SELECIONADA!
     * 
     * @param array $array
     * @param string $style
     * @param int $size
     * @return int
     */
    public function maiorStrArray($array, $style = "", $size = 0) {
        if ($array === NULL) {
            return NULL;
        }

        // setando fonte para calculo correto
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, $style, $size);
        $mai = 0;
        foreach ($array as $str) {
            if (($tam = $this->GetStringWidth($str) + 1) > $mai) {
                $mai = $tam;
            }
        }

        return $mai;
    }

    /**
     * Imprime um guia para desenvolvimento
     */
    function guiaDev() {
        $this->Cell(0, 20, '', 1, 1);
    }

    // Rodape da pagina
    function Footer() {
        $this->SetTextColor(0);

        $tamRodape = 20;
        // Ajustando Y
        $this->SetY(PDFNeaad::$ALTURA_PAG - $this->margemCima - $tamRodape);

        // Escrevendo Endereço
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, 'I', 8);
        $this->Cell(0, 8, 'SEAD - UFES | Tel: (27) 4009-2208', 0, 0, 'C');
        $this->Ln(5);
        $this->Cell(0, 8, 'Av. Fernando Ferrari, 514, Goiabeiras | Vitória - ES - CEP 29075-910', 0, 0, 'C');


        // imprimindo data de impressao
//        $this->SetY(PDFNeaad::$ALTURA_PAG - $this->margemCima - $tamRodape);
//        $dtImp = dt_getDataEmStr("d/m/Y H:i:s");
//        $this->SetX($this->margemEsq);
//        $this->Cell(0, 8, "$dtImp", 0, 0, 'L');
//        
        // imprimindo numero de pagina
        $this->Cell(0, 8, $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        $txt = utf8_decode($txt);
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    function WriteHTML($html) {
        //HTML parser
        $html = strip_tags($html, "<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
//        print_r("$html");
        $html = str_replace("\n", ' ', $html); //remplace retour à la ligne par un espace
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                //Text
                if ($this->HREF) {
                    $this->PutLink($this->HREF, $e);
                } else {
//                    $temp = stripslashes(txtentities($e));
//                    print_r("'$temp'");
//                    print_r(" <br/>");
                    $this->Write(5, stripslashes(txtentities($e)));
                }
            } else {
                //Tag
                if ($e[0] == '/')
                    $this->CloseTag(mb_strtoupper(substr($e, 1)));
                else {
                    //Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = mb_strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                            $attr[mb_strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function OpenTag($tag, $attr) {
        //Opening tag
        switch ($tag) {
            case 'STRONG':
                $this->SetStyle('B', true);
                break;
            case 'EM':
                $this->SetStyle('I', true);
                break;
            case 'B':
            case 'I':
            case 'U':
                $this->SetStyle($tag, true);
                break;
            case 'A':
                $this->HREF = $attr['HREF'];
                break;
            case 'IMG':
                if (isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if (!isset($attr['WIDTH']))
                        $attr['WIDTH'] = 0;
                    if (!isset($attr['HEIGHT']))
                        $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
                }
                break;
            case 'TR':
            case 'BLOCKQUOTE':
            case 'BR':
                $this->Ln(5);
                break;
            case 'P':
                $this->Ln(10);
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR'] != '') {
                    $coul = hex2dec($attr['COLOR']);
                    $this->SetTextColor($coul['R'], $coul['V'], $coul['B']);
                    $this->issetcolor = true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont = true;
                }
                break;
        }
    }

    function CloseTag($tag) {
        //Closing tag
        if ($tag == 'STRONG')
            $tag = 'B';
        if ($tag == 'EM')
            $tag = 'I';
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';
        if ($tag == 'FONT') {
            if ($this->issetcolor == true) {
                $this->SetTextColor(0);
            }
            if ($this->issetfont) {
                $this->SetFont(PDFNeaad::$FONTE_PADRAO);
                $this->issetfont = false;
            }
        }
    }

    function SetStyle($tag, $enable) {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s) {
            if ($this->$s > 0)
                $style.=$s;
        }
        $this->SetFont(PDFNeaad::$FONTE_PADRAO, $style);
    }

    function PutLink($URL, $txt) {
        //Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    /* Criando funções relacionadas a tabela */

    function SetWidths($w) {
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a) {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data, $alinharCentro = TRUE) {

        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 7 * $nb;

        //Issue a page break first if needed
        $this->CheckPageBreak($h);


        // cauculando x inicial, caso seja para alinhar no centro
        if ($alinharCentro) {
            $sobra = self::$LARGURA_PAG - array_sum($this->widths);
            $this->SetX($sobra / 2);
        }


        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';

            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();

            //Draw the border
            $this->Rect($x, $y, $w, $h);

            //Print the text
            $this->MultiCell($w, 7, $data[$i], 0, $a);

            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }

        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function NbLines($w, $txt) {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l+=$cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

}
