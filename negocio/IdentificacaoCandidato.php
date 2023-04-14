<?php

/**
 * tb_idc_identificacao_candidato class
 * This class manipulates the table IdentificacaoCandidato
 * @requires    >= PHP 5
 * @author      Marcus Brasizza            <mvbrasizza@oztechnology.com.br>
 * Modificado por      Estevão de Oliveira da Costa            <estevao90@gmail.com>
 * @copyright   (C)2013       
 * @DataBase = selecaoneaad
 * @DatabaseType = mysql
 * @Host = localhost
 * @date 27/08/2013
 * */
global $CFG;
require_once $CFG->rpasta . "/util/NegocioException.php";
require_once $CFG->rpasta . "/persistencia/ConexaoMysql.php";
require_once $CFG->rpasta . "/negocio/NGUtil.php";

class IdentificacaoCandidato {

    private $IDC_ID_IDENTIFICACAO_CDT;
    private $IDC_NR_CPF;
    private $NAC_ID_NACIONALIDADE;
    private $IDC_NM_NACIONALIDADE;
    private $IDC_DS_SEXO;
    private $IDC_TP_RACA;
    private $OCP_ID_OCUPACAO;
    private $IDC_NM_OCUPACAO;
    private $IDC_VINCULO_PUBLICO;
    private $IDC_NASC_PAIS;
    private $IDC_NASC_ESTADO;
    private $IDC_NASC_CIDADE;
    private $IDC_NASC_DATA;
    private $IDC_RG_NUMERO;
    private $IDC_RG_ORGAO_EXP;
    private $IDC_RG_UF;
    private $IDC_RG_DT_EMISSAO;
    private $IDC_UFES_SIAPE;
    private $IDC_UFES_LOTACAO;
    private $IDC_UFES_SETOR;
    private $IDC_PSP_NUMERO;
    private $IDC_PSP_DT_EMISSAO;
    private $IDC_PSP_DT_VALIDADE;
    private $IDC_PSP_PAIS_ORIGEM;
    private $IDC_TP_ESTADO_CIVIL;
    private $IDC_NM_CONJUGE;
    private $IDC_FIL_NM_PAI;
    private $IDC_FIL_NM_MAE;
    // campos herdados
    private $NAC_NM_NACIONALIDADE;
    private $DS_NATURALIDADE;
    public static $SEXO_MASCULINO = 'M';
    public static $SEXO_FEMININO = 'F';
    public static $RACA_BRANCA = 'B';
    public static $RACA_PRETA = 'P';
    public static $RACA_PARDA = 'R';
    public static $RACA_INDIGENA = 'I';
    public static $RACA_AMARELA = 'A';
    public static $RACA_NAO_DECLARADA = 'N';
    public static $EST_CIVIL_SOLTEIRO = 'S';
    public static $EST_CIVIL_DIVORCIADO = 'D';
    public static $EST_CIVIL_CASADO = 'C';
    public static $EST_CIVIL_VIUVO = 'V';
    public static $EST_CIVIL_SEPARADO = 'P';
    public static $EST_CIVIL_UNIAO_ESTAVEL = 'U';

    /**
     * Diz se o usuario preencheu sua identificaçao.
     * Base para resposta: Nome da mae preenchido
     * @param int $idUsuario
     * @return boolean
     */
    public static function preencheuIdentificacao($idUsuario) {
        try {
            // recuperando conexao
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = "select 
                    count(*) as qt
                from
                    tb_idc_identificacao_candidato
                where
                    IDC_FIL_NM_MAE IS NOT NULL
                        and IDC_ID_IDENTIFICACAO_CDT = (select 
                            IDC_ID_IDENTIFICACAO_CDT
                        from
                            tb_cdt_candidato
                        where
                            USR_ID_USUARIO = '$idUsuario')";

            // executando sql
            $resp = $conexao->execSqlComRetorno($sql);

            // retornando
            return ConexaoMysql::getResult("qt", $resp) == 1;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao verificar preenchimento da identificação.", $e);
        }
    }

    public static function getDsSexo($sexo) {
        if ($sexo == IdentificacaoCandidato::$SEXO_MASCULINO) {
            return "Masculino";
        }
        if ($sexo == IdentificacaoCandidato::$SEXO_FEMININO) {
            return "Feminino";
        }
        return Util::$STR_CAMPO_VAZIO;
    }

    public static function getDsRaca($raca) {
        if ($raca == IdentificacaoCandidato::$RACA_BRANCA) {
            return "Branca";
        }
        if ($raca == IdentificacaoCandidato::$RACA_PRETA) {
            return "Preta";
        }
        if ($raca == IdentificacaoCandidato::$RACA_PARDA) {
            return "Parda";
        }
        if ($raca == IdentificacaoCandidato::$RACA_INDIGENA) {
            return "Indígena";
        }
        if ($raca == IdentificacaoCandidato::$RACA_AMARELA) {
            return "Amarela";
        }
        if ($raca == IdentificacaoCandidato::$RACA_NAO_DECLARADA) {
            return "Não desejo declarar";
        }
        return Util::$STR_CAMPO_VAZIO;
    }

    public static function getDsEstCivil($estCivil) {
        if ($estCivil == IdentificacaoCandidato::$EST_CIVIL_SOLTEIRO) {
            return "Solteiro(a)";
        }
        if ($estCivil == IdentificacaoCandidato::$EST_CIVIL_DIVORCIADO) {
            return "Divorciado(a)";
        }
        if ($estCivil == IdentificacaoCandidato::$EST_CIVIL_CASADO) {
            return "Casado(a)";
        }
        if ($estCivil == IdentificacaoCandidato::$EST_CIVIL_VIUVO) {
            return "Viúvo(a)";
        }
        if ($estCivil == IdentificacaoCandidato::$EST_CIVIL_SEPARADO) {
            return "Separado(a)";
        }
        if ($estCivil == IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL) {
            return "União Estável";
        }
        return Util::$STR_CAMPO_VAZIO;
    }

    public static function getWhenCaseExpEstadoCivil() {
        return "when '" . IdentificacaoCandidato::$EST_CIVIL_SOLTEIRO . "' then '" . IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_SOLTEIRO) . "'
                when '" . IdentificacaoCandidato::$EST_CIVIL_DIVORCIADO . "' then '" . IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_DIVORCIADO) . "'
                when '" . IdentificacaoCandidato::$EST_CIVIL_CASADO . "' then '" . IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_CASADO) . "'
                when '" . IdentificacaoCandidato::$EST_CIVIL_VIUVO . "' then '" . IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_VIUVO) . "'
                when '" . IdentificacaoCandidato::$EST_CIVIL_SEPARADO . "' then '" . IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_SEPARADO) . "'
                when '" . IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL . "' then '" . IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL) . "'";
    }

    public static function getWhenCaseExpRaca() {
        return "when '" . IdentificacaoCandidato::$RACA_AMARELA . "' then '" . IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_AMARELA) . "'
                when '" . IdentificacaoCandidato::$RACA_BRANCA . "' then '" . IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_BRANCA) . "'
                when '" . IdentificacaoCandidato::$RACA_INDIGENA . "' then '" . IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_INDIGENA) . "'
                when '" . IdentificacaoCandidato::$RACA_PARDA . "' then '" . IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_PARDA) . "'
                when '" . IdentificacaoCandidato::$RACA_PRETA . "' then '" . IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_PRETA) . "'
                when '" . IdentificacaoCandidato::$RACA_NAO_DECLARADA . "' then '" . IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_NAO_DECLARADA) . "'";
    }

    public static function getListaSexoDsSexo() {
        $ret = array(
            IdentificacaoCandidato::$SEXO_MASCULINO => IdentificacaoCandidato::getDsSexo(IdentificacaoCandidato::$SEXO_MASCULINO),
            IdentificacaoCandidato::$SEXO_FEMININO => IdentificacaoCandidato::getDsSexo(IdentificacaoCandidato::$SEXO_FEMININO)
        );
        return $ret;
    }

    public static function getListaRacaDsRaca() {
        $ret = array(
            IdentificacaoCandidato::$RACA_BRANCA => IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_BRANCA),
            IdentificacaoCandidato::$RACA_PRETA => IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_PRETA),
            IdentificacaoCandidato::$RACA_PARDA => IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_PARDA),
            IdentificacaoCandidato::$RACA_INDIGENA => IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_INDIGENA),
            IdentificacaoCandidato::$RACA_AMARELA => IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_AMARELA),
            IdentificacaoCandidato::$RACA_NAO_DECLARADA => IdentificacaoCandidato::getDsRaca(IdentificacaoCandidato::$RACA_NAO_DECLARADA)
        );
        return $ret;
    }

    public static function getListaEstCivilDsEstCivil() {
        $ret = array(
            IdentificacaoCandidato::$EST_CIVIL_SOLTEIRO => IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_SOLTEIRO),
            IdentificacaoCandidato::$EST_CIVIL_DIVORCIADO => IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_DIVORCIADO),
            IdentificacaoCandidato::$EST_CIVIL_CASADO => IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_CASADO),
            IdentificacaoCandidato::$EST_CIVIL_VIUVO => IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_VIUVO),
            IdentificacaoCandidato::$EST_CIVIL_SEPARADO => IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_SEPARADO),
            IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL => IdentificacaoCandidato::getDsEstCivil(IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL)
        );
        return $ret;
    }

    /* Construtor padrão da classe */

    public function __construct($IDC_ID_IDENTIFICACAO_CDT, $IDC_NR_CPF, $NAC_ID_NACIONALIDADE = NULL, $IDC_NM_NACIONALIDADE = NULL, $IDC_DS_SEXO = NULL, $IDC_TP_RACA = NULL, $OCP_ID_OCUPACAO = NULL, $IDC_NM_OCUPACAO = NULL, $IDC_VINCULO_PUBLICO = NULL, $IDC_NASC_PAIS = NULL, $IDC_NASC_ESTADO = NULL, $IDC_NASC_CIDADE = NULL, $IDC_NASC_DATA = NULL, $IDC_RG_NUMERO = NULL, $IDC_RG_ORGAO_EXP = NULL, $IDC_RG_UF = NULL, $IDC_RG_DT_EMISSAO = NULL, $IDC_UFES_SIAPE = NULL, $IDC_UFES_LOTACAO = NULL, $IDC_UFES_SETOR = NULL, $IDC_PSP_NUMERO = NULL, $IDC_PSP_DT_EMISSAO = NULL, $IDC_PSP_DT_VALIDADE = NULL, $IDC_PSP_PAIS_ORIGEM = NULL, $IDC_TP_ESTADO_CIVIL = NULL, $IDC_NM_CONJUGE = NULL, $IDC_FIL_NM_PAI = NULL, $IDC_FIL_NM_MAE = NULL) {
        $this->IDC_ID_IDENTIFICACAO_CDT = $IDC_ID_IDENTIFICACAO_CDT;
        $this->IDC_NR_CPF = $IDC_NR_CPF;
        $this->NAC_ID_NACIONALIDADE = $NAC_ID_NACIONALIDADE;
        $this->IDC_NM_NACIONALIDADE = $IDC_NM_NACIONALIDADE;
        $this->IDC_DS_SEXO = $IDC_DS_SEXO;
        $this->IDC_TP_RACA = $IDC_TP_RACA;
        $this->OCP_ID_OCUPACAO = $OCP_ID_OCUPACAO;
        $this->IDC_NM_OCUPACAO = $IDC_NM_OCUPACAO;
        $this->IDC_VINCULO_PUBLICO = $IDC_VINCULO_PUBLICO;
        $this->IDC_NASC_PAIS = $IDC_NASC_PAIS;
        $this->IDC_NASC_ESTADO = $IDC_NASC_ESTADO;
        $this->IDC_NASC_CIDADE = $IDC_NASC_CIDADE;
        $this->IDC_NASC_DATA = $IDC_NASC_DATA;
        $this->IDC_RG_NUMERO = $IDC_RG_NUMERO;
        $this->IDC_RG_ORGAO_EXP = $IDC_RG_ORGAO_EXP;
        $this->IDC_RG_UF = $IDC_RG_UF;
        $this->IDC_RG_DT_EMISSAO = $IDC_RG_DT_EMISSAO;
        $this->IDC_UFES_SIAPE = $IDC_UFES_SIAPE;
        $this->IDC_UFES_LOTACAO = $IDC_UFES_LOTACAO;
        $this->IDC_UFES_SETOR = $IDC_UFES_SETOR;
        $this->IDC_PSP_NUMERO = $IDC_PSP_NUMERO;
        $this->IDC_PSP_DT_EMISSAO = $IDC_PSP_DT_EMISSAO;
        $this->IDC_PSP_DT_VALIDADE = $IDC_PSP_DT_VALIDADE;
        $this->IDC_PSP_PAIS_ORIGEM = $IDC_PSP_PAIS_ORIGEM;
        $this->IDC_TP_ESTADO_CIVIL = $IDC_TP_ESTADO_CIVIL;
        $this->IDC_NM_CONJUGE = $IDC_NM_CONJUGE;
        $this->IDC_FIL_NM_PAI = $IDC_FIL_NM_PAI;
        $this->IDC_FIL_NM_MAE = $IDC_FIL_NM_MAE;
    }

    public function getNrCPFMascarado() {
        return adicionarMascara("###.###.###-##", $this->IDC_NR_CPF);
    }

    /**
     * 
     * @param string $nrCPF
     * @param int $idIdentCand
     * @return boolean
     * @throws NegocioException
     */
    public static function validarCadastroCPF($nrCPF, $idIdentCand = NULL) {
        try {

            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //caso nulo, dizer que é válido
            if (Util::vazioNulo($nrCPF)) {
                return TRUE;
            }

            //validar tamanho do CPF
            if (strlen($nrCPF) != 11) {
                return FALSE;
            }

            //caso cpf
            $sql = "select count(*) as cont from tb_idc_identificacao_candidato
                where `IDC_NR_CPF` = '$nrCPF'";
            if ($idIdentCand != NULL) {
                $sql .= " and IDC_ID_IDENTIFICACAO_CDT != '$idIdentCand'";
            }
            $res = $conexao->execSqlComRetorno($sql);

            return $conexao->getResult("cont", $res) == 0;
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao validar CPF.", $e);
        }
    }

    private static function trataDadosParaBanco($objIdentCand, $tpVinculoUfes) {
        // trata cpf
        $objIdentCand->IDC_NR_CPF = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_NR_CPF);


        // tratar nacionalidade
        if (Util::vazioNulo($objIdentCand->NAC_ID_NACIONALIDADE)) {
            $objIdentCand->NAC_ID_NACIONALIDADE = $objIdentCand->IDC_NM_NACIONALIDADE = "NULL";
        } elseif ($objIdentCand->NAC_ID_NACIONALIDADE == ID_SELECT_OUTRO) {
            $objIdentCand->NAC_ID_NACIONALIDADE = "NULL";
            $objIdentCand->IDC_NM_NACIONALIDADE = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($objIdentCand->IDC_NM_NACIONALIDADE));
        } else {
            $objIdentCand->IDC_NM_NACIONALIDADE = "NULL";
        }

        // tratando campos opcionais
        $objIdentCand->IDC_DS_SEXO = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_DS_SEXO);
        $objIdentCand->IDC_TP_RACA = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_TP_RACA);

        // tratar ocupaçao
        if (Util::vazioNulo($objIdentCand->OCP_ID_OCUPACAO)) {
            $objIdentCand->OCP_ID_OCUPACAO = $objIdentCand->IDC_NM_OCUPACAO = "NULL";
        } elseif ($objIdentCand->OCP_ID_OCUPACAO == ID_SELECT_OUTRO) {
            $objIdentCand->OCP_ID_OCUPACAO = "NULL";
            $objIdentCand->IDC_NM_OCUPACAO = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($objIdentCand->IDC_NM_OCUPACAO));
        } else {
            $objIdentCand->IDC_NM_OCUPACAO = "NULL";
        }

        // vinculo publico
        if (!Util::vazioNulo($objIdentCand->OCP_ID_OCUPACAO) || !Util::vazioNulo($objIdentCand->IDC_NM_OCUPACAO)) {
            $objIdentCand->IDC_VINCULO_PUBLICO = $objIdentCand->IDC_VINCULO_PUBLICO ? FLAG_BD_SIM : FLAG_BD_NAO;
            $objIdentCand->IDC_VINCULO_PUBLICO = "'$objIdentCand->IDC_VINCULO_PUBLICO'";
        } else {
            $objIdentCand->IDC_VINCULO_PUBLICO = "NULL";
        }

        // nascimento
        if ($objIdentCand->IDC_NASC_PAIS == Pais::$PAIS_BRASIL) {
            $objIdentCand->IDC_NASC_ESTADO = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_NASC_ESTADO);
            //verificando preenchimento correto dos campos
            if (Util::vazioNulo($objIdentCand->IDC_NASC_CIDADE)) {
                new Mensagem(Mensagem::$MSG_ERR_VAL_POS_AJAX, Mensagem::$MENSAGEM_ERRO);
            }
            $objIdentCand->IDC_NASC_CIDADE = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_NASC_CIDADE);
        } else {
            $objIdentCand->IDC_NASC_ESTADO = "NULL";
            $objIdentCand->IDC_NASC_CIDADE = "NULL";
        }
        $objIdentCand->IDC_NASC_PAIS = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_NASC_PAIS);

        //data de nascimento formato banco
        if (!Util::vazioNulo($objIdentCand->IDC_NASC_DATA)) {
            $objIdentCand->IDC_NASC_DATA = dt_dataStrParaMysql($objIdentCand->IDC_NASC_DATA);
        } else {
            $objIdentCand->IDC_NASC_DATA = "NULL";
        }

        // mais campos
        $objIdentCand->IDC_RG_NUMERO = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_RG_NUMERO);
        $objIdentCand->IDC_RG_ORGAO_EXP = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_RG_ORGAO_EXP);
        $objIdentCand->IDC_RG_UF = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_RG_UF);

        //data de emissao rg formato banco
        if (!Util::vazioNulo($objIdentCand->IDC_RG_DT_EMISSAO)) {
            $objIdentCand->IDC_RG_DT_EMISSAO = dt_dataStrParaMysql($objIdentCand->IDC_RG_DT_EMISSAO);
        } else {
            $objIdentCand->IDC_RG_DT_EMISSAO = "NULL";
        }

        if ($tpVinculoUfes != Usuario::$VINCULO_NENHUM && $tpVinculoUfes != Usuario::$VINCULO_ESTUDANTE) {
            $objIdentCand->IDC_UFES_SIAPE = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_UFES_SIAPE);
            $objIdentCand->IDC_UFES_LOTACAO = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($objIdentCand->IDC_UFES_LOTACAO));
            $objIdentCand->IDC_UFES_SETOR = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($objIdentCand->IDC_UFES_SETOR));
        } else {
            $objIdentCand->IDC_UFES_SIAPE = $objIdentCand->IDC_UFES_LOTACAO = $objIdentCand->IDC_UFES_SETOR = "NULL";
        }

        $objIdentCand->IDC_PSP_NUMERO = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_PSP_NUMERO);

        //data de emissao e validade do passaporte formato banco
        if (!Util::vazioNulo($objIdentCand->IDC_PSP_DT_EMISSAO)) {
            $objIdentCand->IDC_PSP_DT_EMISSAO = dt_dataStrParaMysql($objIdentCand->IDC_PSP_DT_EMISSAO);
        } else {
            $objIdentCand->IDC_PSP_DT_EMISSAO = "NULL";
        }
        if (!Util::vazioNulo($objIdentCand->IDC_PSP_DT_VALIDADE)) {
            $objIdentCand->IDC_PSP_DT_VALIDADE = dt_dataStrParaMysql($objIdentCand->IDC_PSP_DT_VALIDADE);
        } else {
            $objIdentCand->IDC_PSP_DT_VALIDADE = "NULL";
        }
        $objIdentCand->IDC_PSP_PAIS_ORIGEM = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_PSP_PAIS_ORIGEM);

        if ($objIdentCand->IDC_TP_ESTADO_CIVIL == IdentificacaoCandidato::$EST_CIVIL_CASADO || $objIdentCand->IDC_TP_ESTADO_CIVIL == IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL) {
            $objIdentCand->IDC_NM_CONJUGE = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($objIdentCand->IDC_NM_CONJUGE));
        } else {
            $objIdentCand->IDC_NM_CONJUGE = "NULL";
        }
        $objIdentCand->IDC_TP_ESTADO_CIVIL = NGUtil::trataCampoStrParaBD($objIdentCand->IDC_TP_ESTADO_CIVIL);

        $objIdentCand->IDC_FIL_NM_PAI = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($objIdentCand->IDC_FIL_NM_PAI));
        $objIdentCand->IDC_FIL_NM_MAE = NGUtil::trataCampoStrParaBD(str_capitalize_forcado($objIdentCand->IDC_FIL_NM_MAE));
    }

    /**
     * Funçao que edita uma identificaçao de usuario. Assume que os campos 
     * estao corretamente preenchidos. 
     * Caso $nmAtual e $novoNome seja diferente de nulo, o nome do usuario 
     * e atualizado quando necessario.
     * 
     * @param int $idUsuario - Id do usuario a ser alterado os dados
     * @param string $nmAtual - Nome do usuario atualmente gravado no banco
     * @param string $novoNome - Novo nome designado pelo candidato
     * @return void
     */
    public function editarIdentificacao($idUsuario, $nmAtual = NULL, $novoNome = NULL) {
        try {

            $dadosLogin = getDadosLogin();

            // tratando campos
            IdentificacaoCandidato::trataDadosParaBanco($this, $dadosLogin['tpVinculoUfes']);

            // recuperando conexao
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = "update tb_idc_identificacao_candidato 
                    set IDC_NR_CPF = $this->IDC_NR_CPF,
                    NAC_ID_NACIONALIDADE = $this->NAC_ID_NACIONALIDADE,
                    IDC_NM_NACIONALIDADE = $this->IDC_NM_NACIONALIDADE,
                    IDC_DS_SEXO = $this->IDC_DS_SEXO,
                    IDC_TP_RACA = $this->IDC_TP_RACA,
                    OCP_ID_OCUPACAO = $this->OCP_ID_OCUPACAO,
                    IDC_NM_OCUPACAO = $this->IDC_NM_OCUPACAO,
                    IDC_VINCULO_PUBLICO = $this->IDC_VINCULO_PUBLICO,
                    IDC_NASC_PAIS = $this->IDC_NASC_PAIS,
                    IDC_NASC_ESTADO = $this->IDC_NASC_ESTADO,
                    IDC_NASC_CIDADE = $this->IDC_NASC_CIDADE,
                    IDC_NASC_DATA = $this->IDC_NASC_DATA,
                    IDC_RG_NUMERO = $this->IDC_RG_NUMERO,
                    IDC_RG_ORGAO_EXP = $this->IDC_RG_ORGAO_EXP,
                    IDC_RG_UF = $this->IDC_RG_UF,
                    IDC_RG_DT_EMISSAO = $this->IDC_RG_DT_EMISSAO,
                    IDC_UFES_SIAPE = $this->IDC_UFES_SIAPE,
                    IDC_UFES_LOTACAO = $this->IDC_UFES_LOTACAO,
                    IDC_UFES_SETOR = $this->IDC_UFES_SETOR,
                    IDC_PSP_NUMERO = $this->IDC_PSP_NUMERO,
                    IDC_PSP_DT_EMISSAO = $this->IDC_PSP_DT_EMISSAO,
                    IDC_PSP_DT_VALIDADE = $this->IDC_PSP_DT_VALIDADE,
                    IDC_PSP_PAIS_ORIGEM = $this->IDC_PSP_PAIS_ORIGEM,
                    IDC_TP_ESTADO_CIVIL = $this->IDC_TP_ESTADO_CIVIL,
                    IDC_NM_CONJUGE = $this->IDC_NM_CONJUGE,
                    IDC_FIL_NM_PAI = $this->IDC_FIL_NM_PAI,
                    IDC_FIL_NM_MAE = $this->IDC_FIL_NM_MAE
                where
                    IDC_ID_IDENTIFICACAO_CDT = (select 
                            IDC_ID_IDENTIFICACAO_CDT
                        from
                            tb_cdt_candidato
                        where
                            USR_ID_USUARIO = '$idUsuario')";

            // verificando caso de atualizar nome
            if (!Util::vazioNulo($nmAtual) && !Util::vazioNulo($novoNome)) {
                if ($nmAtual != $novoNome) {
                    $atualizarNome = true;
                    $sqlAtualizaNome = Usuario::getSqlAtualizacaoNome($idUsuario, $novoNome);
                }
            }

            // pegando sql de ajuste de data de atualizacao de informaçao do candidato
            $sqlAtualizaData = Candidato::getSqlAtualizaDtAlteracaoPerfil($idUsuario);

            //persistindo no banco
            if (!isset($atualizarNome)) {
                $conexao->execTransacaoArray(array($sql, $sqlAtualizaData));
            } else {
                $conexao->execTransacaoArray(array($sql, $sqlAtualizaNome, $sqlAtualizaData));

                // buscando usuário atualizado
                $usuAtualizado = Usuario::buscarUsuarioPorId($idUsuario);
                atualizarNomeSessao($usuAtualizado->getUSR_DS_NOME(), $usuAtualizado->getUSR_HASH_ALTERACAO_EXT());
            }

            // atualizando dados na sessão: Não mostrar inatividade
            sessao_setMostrarInatividade(FALSE);
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao atualizar identificação do candidato.", $e);
        }
    }

    /**
     * 
     * @param IdentificacaoCandidato $objIdentCand
     * @return string
     */
    public static function getStringCriacaoIdentCand($objIdentCand, $tpVinculoUfes) {

        //tratando dados
        IdentificacaoCandidato::trataDadosParaBanco($objIdentCand, $tpVinculoUfes);

        $ret = "INSERT INTO `tb_idc_identificacao_candidato`
                (`IDC_NR_CPF`, `NAC_ID_NACIONALIDADE`, `IDC_NM_NACIONALIDADE`,
                `IDC_DS_SEXO`, `IDC_TP_RACA`, `OCP_ID_OCUPACAO`,
                `IDC_NM_OCUPACAO`, `IDC_VINCULO_PUBLICO`, `IDC_NASC_PAIS`,
                `IDC_NASC_ESTADO`, `IDC_NASC_CIDADE`, `IDC_NASC_DATA`, `IDC_RG_NUMERO`,
                `IDC_RG_ORGAO_EXP`, `IDC_RG_UF`, `IDC_RG_DT_EMISSAO`, `IDC_UFES_SIAPE`, `IDC_UFES_LOTACAO`,
                `IDC_UFES_SETOR`, `IDC_PSP_NUMERO`, `IDC_PSP_DT_EMISSAO`,
                `IDC_PSP_DT_VALIDADE`, `IDC_PSP_PAIS_ORIGEM`, `IDC_TP_ESTADO_CIVIL`,
                `IDC_NM_CONJUGE`, `IDC_FIL_NM_PAI`, `IDC_FIL_NM_MAE`)
                VALUES ($objIdentCand->IDC_NR_CPF, $objIdentCand->NAC_ID_NACIONALIDADE,
                $objIdentCand->IDC_NM_NACIONALIDADE, $objIdentCand->IDC_DS_SEXO,
                $objIdentCand->IDC_TP_RACA, $objIdentCand->OCP_ID_OCUPACAO,
                $objIdentCand->IDC_NM_OCUPACAO, $objIdentCand->IDC_VINCULO_PUBLICO,
                $objIdentCand->IDC_NASC_PAIS, $objIdentCand->IDC_NASC_ESTADO,
                $objIdentCand->IDC_NASC_CIDADE, $objIdentCand->IDC_NASC_DATA, $objIdentCand->IDC_RG_NUMERO,
                $objIdentCand->IDC_RG_ORGAO_EXP, $objIdentCand->IDC_RG_UF, $objIdentCand->IDC_RG_DT_EMISSAO,
                $objIdentCand->IDC_UFES_SIAPE,$objIdentCand->IDC_UFES_LOTACAO,
                $objIdentCand->IDC_UFES_SETOR, $objIdentCand->IDC_PSP_NUMERO,
                $objIdentCand->IDC_PSP_DT_EMISSAO, $objIdentCand->IDC_PSP_DT_VALIDADE, $objIdentCand->IDC_PSP_PAIS_ORIGEM,
                $objIdentCand->IDC_TP_ESTADO_CIVIL, $objIdentCand->IDC_NM_CONJUGE,
                $objIdentCand->IDC_FIL_NM_PAI, $objIdentCand->IDC_FIL_NM_MAE)";

        return $ret;
    }

    public static function getStringAtualizacaoCpfDtNasc($nrCpf, $dtNascimento, $idUsuario) {
        if (!Candidato::validarCadastroCPF($nrCpf, $idUsuario)) {
            throw new NegocioException("CPF já cadastrado.");
        }
        $dtNascimento = dt_dataStrParaMysql($dtNascimento);
        $ret = "update tb_idc_identificacao_candidato
                 set IDC_NR_CPF = '$nrCpf', IDC_NASC_DATA = $dtNascimento where IDC_ID_IDENTIFICACAO_CDT = (select IDC_ID_IDENTIFICACAO_CDT from tb_cdt_candidato where USR_ID_USUARIO = '$idUsuario')";

        return $ret;
    }

    private static function getSqlInicialBusca() {
        return "select 
                    IDC_ID_IDENTIFICACAO_CDT,
                    IDC_NR_CPF,
                    idc.NAC_ID_NACIONALIDADE,
                    NAC_NM_NACIONALIDADE,
                    IDC_NM_NACIONALIDADE,
                    IDC_DS_SEXO,
                    IDC_TP_RACA,
                    OCP_ID_OCUPACAO,
                    IDC_NM_OCUPACAO,
                    IDC_VINCULO_PUBLICO,
                    IDC_NASC_PAIS,
                    IDC_NASC_ESTADO,
                    IDC_NASC_CIDADE,
                    date_format(`IDC_NASC_DATA`, '%d/%m/%Y') as IDC_NASC_DATA,
                    IDC_RG_NUMERO,
                    IDC_RG_ORGAO_EXP,
                    IDC_RG_UF,
                    date_format(`IDC_RG_DT_EMISSAO`, '%d/%m/%Y') as IDC_RG_DT_EMISSAO,
                    IDC_UFES_SIAPE,
                    IDC_UFES_LOTACAO,
                    IDC_UFES_SETOR,
                    IDC_PSP_NUMERO,
                    date_format(`IDC_PSP_DT_EMISSAO`, '%d/%m/%Y') as IDC_PSP_DT_EMISSAO,
                    date_format(`IDC_PSP_DT_VALIDADE`, '%d/%m/%Y') as IDC_PSP_DT_VALIDADE,
                    IDC_PSP_PAIS_ORIGEM,
                    IDC_TP_ESTADO_CIVIL,
                    IDC_NM_CONJUGE,
                    IDC_FIL_NM_PAI,
                    IDC_FIL_NM_MAE
                from
                    tb_idc_identificacao_candidato idc 
                    left join tb_nac_nacionalidade nac on nac.NAC_ID_NACIONALIDADE = idc.NAC_ID_NACIONALIDADE";
    }

    public static function buscarIdentCandPorIdCand($idCandidato) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = IdentificacaoCandidato::getSqlInicialBusca() . " where
            IDC_ID_IDENTIFICACAO_CDT = (select
            IDC_ID_IDENTIFICACAO_CDT
            from
            tb_cdt_candidato
    where
            CDT_ID_CANDIDATO = '$idCandidato')";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objIdent = new IdentificacaoCandidato($retorno['IDC_ID_IDENTIFICACAO_CDT'], $retorno['IDC_NR_CPF'], $retorno['NAC_ID_NACIONALIDADE'], $retorno['IDC_NM_NACIONALIDADE'], $retorno['IDC_DS_SEXO'], $retorno['IDC_TP_RACA'], $retorno['OCP_ID_OCUPACAO'], $retorno['IDC_NM_OCUPACAO'], $retorno['IDC_VINCULO_PUBLICO'], $retorno['IDC_NASC_PAIS'], $retorno['IDC_NASC_ESTADO'], $retorno['IDC_NASC_CIDADE'], $retorno['IDC_NASC_DATA'], $retorno['IDC_RG_NUMERO'], $retorno['IDC_RG_ORGAO_EXP'], $retorno['IDC_RG_UF'], $retorno['IDC_RG_DT_EMISSAO'], $retorno['IDC_UFES_SIAPE'], $retorno['IDC_UFES_LOTACAO'], $retorno['IDC_UFES_SETOR'], $retorno['IDC_PSP_NUMERO'], $retorno['IDC_PSP_DT_EMISSAO'], $retorno['IDC_PSP_DT_VALIDADE'], $retorno ['IDC_PSP_PAIS_ORIGEM'], $retorno['IDC_TP_ESTADO_CIVIL'], $retorno['IDC_NM_CONJUGE'], $retorno['IDC_FIL_NM_PAI'], $retorno['IDC_FIL_NM_MAE']);

                // setando campos herdados
                $objIdent->NAC_NM_NACIONALIDADE = $retorno['NAC_NM_NACIONALIDADE'];
                return $objIdent;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar identificação do candidato.", $e);
        }
    }

    /**
     * 
     * @param int $idUsuario


     * @return \IdentificacaoCandidato|null
     * @throws NegocioException
     */
    public static function buscarIdentCandPorIdUsu($idUsuario) {
        try {
            //criando objeto de conexão
            $conexao = NGUtil::getConexao();

            //montando sql
            $sql = IdentificacaoCandidato::getSqlInicialBusca() . " where
            IDC_ID_IDENTIFICACAO_CDT = (select
            IDC_ID_IDENTIFICACAO_CDT
            from
            tb_cdt_candidato
            where
            USR_ID_USUARIO = '$idUsuario')";

            $ret = $conexao->execSqlComRetorno($sql);

            //verificando linhas de retorno
            if (ConexaoMysql::getNumLinhas($ret) != 0) {
                $retorno = ConexaoMysql::getLinha($ret);
                $objIdent = new IdentificacaoCandidato($retorno['IDC_ID_IDENTIFICACAO_CDT'], $retorno['IDC_NR_CPF'], $retorno['NAC_ID_NACIONALIDADE'], $retorno['IDC_NM_NACIONALIDADE'], $retorno['IDC_DS_SEXO'], $retorno['IDC_TP_RACA'], $retorno['OCP_ID_OCUPACAO'], $retorno['IDC_NM_OCUPACAO'], $retorno['IDC_VINCULO_PUBLICO'], $retorno['IDC_NASC_PAIS'], $retorno['IDC_NASC_ESTADO'], $retorno['IDC_NASC_CIDADE'], $retorno['IDC_NASC_DATA'], $retorno['IDC_RG_NUMERO'], $retorno['IDC_RG_ORGAO_EXP'], $retorno['IDC_RG_UF'], $retorno['IDC_RG_DT_EMISSAO'], $retorno['IDC_UFES_SIAPE'], $retorno['IDC_UFES_LOTACAO'], $retorno['IDC_UFES_SETOR'], $retorno['IDC_PSP_NUMERO'], $retorno['IDC_PSP_DT_EMISSAO'], $retorno['IDC_PSP_DT_VALIDADE'], $retorno ['IDC_PSP_PAIS_ORIGEM'], $retorno['IDC_TP_ESTADO_CIVIL'], $retorno['IDC_NM_CONJUGE'], $retorno['IDC_FIL_NM_PAI'], $retorno['IDC_FIL_NM_MAE']);

                // setando campos herdados
                $objIdent->NAC_NM_NACIONALIDADE = $retorno['NAC_NM_NACIONALIDADE'];
                return $objIdent;
            } else {
                return NULL;
            }
        } catch (NegocioException $n) {
            throw $n;
        } catch (Exception $e) {
            throw new NegocioException("Erro ao buscar identificação do candidato.", $e
            );
        }
    }

    /**
     * Retorna a nacionalidade do candidato observando os varios casos
     * @return string
     */
    public function getNacionalidade() {
        if (Util::vazioNulo($this->IDC_NM_NACIONALIDADE)) {
            return $this->NAC_NM_NACIONALIDADE;
        }
        return $this->IDC_NM_NACIONALIDADE;
    }

    public function getNaturalidade() {
        if (Util::vazioNulo($this->DS_NATURALIDADE)) {
            $this->carregaNaturalidade();
        }
        return $this->DS_NATURALIDADE;
    }

    private function carregaNaturalidade() {
        try {
            //recuperando objeto de conexão
            $conexao = NGUtil::getConexao();

            // caso de nao ser brasileiro
            if ($this->IDC_NASC_PAIS != Pais::$PAIS_BRASIL) {
                $this->DS_NATURALIDADE = "<Não aplicável>";
                return;
            }

            // montando sql 
            $sql = "select CID_NM_CIDADE as cidade from tb_cid_cidade where CID_ID_CIDADE = '{$this->IDC_NASC_CIDADE}'";

            // executando sql
            $ret = $conexao->execSqlComRetorno($sql);

            $cid = ConexaoMysql::getResult("cidade", $ret);

            // escrevendo 
            $this->DS_NATURALIDADE = "$cid / {$this->getIDC_NASC_ESTADO()}";
        } catch (NegocioException $n) {
            // nada a fazer
        } catch (Exception $e) {
            // nada a fazer
        }
    }

    /* GET FIELDS FROM TABLE */

    function getIDC_ID_IDENTIFICACAO_CDT() {
        return $this->IDC_ID_IDENTIFICACAO_CDT;
    }

    /* End of get IDC_ID_IDENTIFICACAO_CDT */

    function getIDC_NR_CPF() {
        return $this->IDC_NR_CPF;
    }

    /* End of get IDC_NR_CPF */

    function getNAC_ID_NACIONALIDADE() {
        return $this->NAC_ID_NACIONALIDADE;
    }

    /* End of get NAC_ID_NACIONALIDADE */

    function getIDC_NM_NACIONALIDADE() {
        return $this->IDC_NM_NACIONALIDADE;
    }

    /* End of get IDC_NM_NACIONALIDADE */

    function getIDC_DS_SEXO() {
        return $this->IDC_DS_SEXO;
    }

    /* End of get IDC_DS_SEXO */

    function getIDC_TP_RACA() {
        return $this->IDC_TP_RACA;
    }

    /* End of get IDC_TP_RACA */

    function getOCP_ID_OCUPACAO() {
        return $this->OCP_ID_OCUPACAO;
    }

    /* End of get OCP_ID_OCUPACAO */

    function getIDC_NM_OCUPACAO() {
        return $this->IDC_NM_OCUPACAO;
    }

    /* End of get IDC_NM_OCUPACAO */

    function getIDC_VINCULO_PUBLICO() {
        return $this->IDC_VINCULO_PUBLICO;
    }

    /* End of get IDC_VINCULO_PUBLICO */

    function getIDC_NASC_PAIS() {
        return $this->IDC_NASC_PAIS;
    }

    /* End of get IDC_NASC_PAIS */

    function getIDC_NASC_ESTADO() {
        return $this->IDC_NASC_ESTADO;
    }

    /* End of get IDC_NASC_ESTADO */

    function getIDC_NASC_CIDADE() {
        return $this->IDC_NASC_CIDADE;
    }

    /* End of get IDC_NASC_CIDADE */

    function getIDC_NASC_DATA($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_NASC_DATA)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_NASC_DATA;
    }

    /* End of get IDC_NASC_DATA */

    function getIDC_RG_NUMERO($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_RG_NUMERO)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_RG_NUMERO;
    }

    /* End of get IDC_RG_NUMERO */

    function getIDC_RG_ORGAO_EXP($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_RG_ORGAO_EXP)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_RG_ORGAO_EXP;
    }

    /* End of get IDC_RG_ORGAO_EXP */

    function getIDC_RG_UF($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_RG_UF)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_RG_UF;
    }

    /* End of get IDC_RG_UF */

    function getIDC_RG_DT_EMISSAO($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_RG_DT_EMISSAO)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_RG_DT_EMISSAO;
    }

    /* End of get IDC_RG_DT_EMISSAO */

    function getIDC_UFES_SIAPE($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_UFES_SIAPE)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_UFES_SIAPE;
    }

    /* End of get IDC_UFES_SIAPE */

    function getIDC_UFES_LOTACAO($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_UFES_LOTACAO)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_UFES_LOTACAO;
    }

    /* End of get IDC_UFES_LOTACAO */

    function getIDC_UFES_SETOR($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_UFES_SETOR)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_UFES_SETOR;
    }

    /* End of get IDC_UFES_SETOR */

    function getIDC_PSP_NUMERO($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_PSP_NUMERO)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_PSP_NUMERO;
    }

    /* End of get IDC_PSP_NUMERO */

    function getIDC_PSP_DT_EMISSAO($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_PSP_DT_EMISSAO)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_PSP_DT_EMISSAO;
    }

    /* End of get IDC_PSP_DT_EMISSAO */

    function getIDC_PSP_DT_VALIDADE($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_PSP_DT_VALIDADE)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_PSP_DT_VALIDADE;
    }

    /* End of get IDC_PSP_DT_VALIDADE */

    function getIDC_PSP_PAIS_ORIGEM() {
        return $this->IDC_PSP_PAIS_ORIGEM;
    }

    /* End of get IDC_PSP_PAIS_ORIGEM */

    function getIDC_TP_ESTADO_CIVIL() {
        return $this->IDC_TP_ESTADO_CIVIL;
    }

    /* End of get IDC_TP_ESTADO_CIVIL */

    function getIDC_NM_CONJUGE($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_NM_CONJUGE)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_NM_CONJUGE;
    }

    /* End of get IDC_NM_CONJUGE */

    function getIDC_FIL_NM_PAI($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_FIL_NM_PAI)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_FIL_NM_PAI;
    }

    /* End of get IDC_FIL_NM_PAI */

    function getIDC_FIL_NM_MAE($preencherVazio = FALSE) {
        if ($preencherVazio && Util::vazioNulo($this->IDC_FIL_NM_MAE)) {
            return Util::$STR_CAMPO_VAZIO;
        }
        return $this->IDC_FIL_NM_MAE;
    }

    /* End of get IDC_FIL_NM_MAE */



    /* SET FIELDS FROM TABLE */

    function setIDC_ID_IDENTIFICACAO_CDT($value) {
        $this->IDC_ID_IDENTIFICACAO_CDT = $value;
    }

    /* End of SET IDC_ID_IDENTIFICACAO_CDT */

    function setIDC_NR_CPF($value) {
        $this->IDC_NR_CPF = $value;
    }

    /* End of SET IDC_NR_CPF */

    function setNAC_ID_NACIONALIDADE($value) {
        $this->NAC_ID_NACIONALIDADE = $value;
    }

    /* End of SET NAC_ID_NACIONALIDADE */

    function setIDC_NM_NACIONALIDADE($value) {
        $this->IDC_NM_NACIONALIDADE = $value;
    }

    /* End of SET IDC_NM_NACIONALIDADE */

    function setIDC_DS_SEXO($value) {
        $this->IDC_DS_SEXO = $value;
    }

    /* End of SET IDC_DS_SEXO */

    function setIDC_TP_RACA($value) {
        $this->IDC_TP_RACA = $value;
    }

    /* End of SET IDC_TP_RACA */

    function setOCP_ID_OCUPACAO($value) {
        $this->OCP_ID_OCUPACAO = $value;
    }

    /* End of SET OCP_ID_OCUPACAO */

    function setIDC_NM_OCUPACAO($value) {
        $this->IDC_NM_OCUPACAO = $value;
    }

    /* End of SET IDC_NM_OCUPACAO */

    function setIDC_VINCULO_PUBLICO($value) {
        $this->IDC_VINCULO_PUBLICO = $value;
    }

    /* End of SET IDC_VINCULO_PUBLICO */

    function setIDC_NASC_PAIS($value) {
        $this->IDC_NASC_PAIS = $value;
    }

    /* End of SET IDC_NASC_PAIS */

    function setIDC_NASC_ESTADO($value) {
        $this->IDC_NASC_ESTADO = $value;
    }

    /* End of SET IDC_NASC_ESTADO */

    function setIDC_NASC_CIDADE($value) {
        $this->IDC_NASC_CIDADE = $value;
    }

    /* End of SET IDC_NASC_CIDADE */

    function setIDC_NASC_DATA($value) {
        $this->IDC_NASC_DATA = $value;
    }

    /* End of SET IDC_NASC_DATA */

    function setIDC_RG_NUMERO($value) {
        $this->IDC_RG_NUMERO = $value;
    }

    /* End of SET IDC_RG_NUMERO */

    function setIDC_RG_ORGAO_EXP($value) {
        $this->IDC_RG_ORGAO_EXP = $value;
    }

    /* End of SET IDC_RG_ORGAO_EXP */

    function setIDC_RG_UF($value) {
        $this->IDC_RG_UF = $value;
    }

    /* End of SET IDC_RG_UF */

    function setIDC_RG_DT_EMISSAO($value) {
        $this->IDC_RG_DT_EMISSAO = $value;
    }

    /* End of SET IDC_RG_DT_EMISSAO */

    function setIDC_UFES_SIAPE($value) {
        $this->IDC_UFES_SIAPE = $value;
    }

    /* End of SET IDC_UFES_SIAPE */

    function setIDC_UFES_LOTACAO($value) {
        $this->IDC_UFES_LOTACAO = $value;
    }

    /* End of SET IDC_UFES_LOTACAO */

    function setIDC_UFES_SETOR($value) {
        $this->IDC_UFES_SETOR = $value;
    }

    /* End of SET IDC_UFES_SETOR */

    function setIDC_PSP_NUMERO($value) {
        $this->IDC_PSP_NUMERO = $value;
    }

    /* End of SET IDC_PSP_NUMERO */

    function setIDC_PSP_DT_EMISSAO($value) {
        $this->IDC_PSP_DT_EMISSAO = $value;
    }

    /* End of SET IDC_PSP_DT_EMISSAO */

    function setIDC_PSP_DT_VALIDADE($value) {
        $this->IDC_PSP_DT_VALIDADE = $value;
    }

    /* End of SET IDC_PSP_DT_VALIDADE */

    function setIDC_PSP_PAIS_ORIGEM($value) {
        $this->IDC_PSP_PAIS_ORIGEM = $value;
    }

    /* End of SET IDC_PSP_PAIS_ORIGEM */

    function setIDC_TP_ESTADO_CIVIL($value) {
        $this->IDC_TP_ESTADO_CIVIL = $value;
    }

    /* End of SET IDC_TP_ESTADO_CIVIL */

    function setIDC_NM_CONJUGE($value) {
        $this->IDC_NM_CONJUGE = $value;
    }

    /* End of SET IDC_NM_CONJUGE */

    function setIDC_FIL_NM_PAI($value) {
        $this->IDC_FIL_NM_PAI = $value;
    }

    /* End of SET IDC_FIL_NM_PAI */

    function setIDC_FIL_NM_MAE($value) {
        $this->IDC_FIL_NM_MAE = $value;
    }

    /* End of SET IDC_FIL_NM_MAE */
}

?>
