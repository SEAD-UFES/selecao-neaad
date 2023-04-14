<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Util
 *
 * @author Estevão Costa
 */
class Util {

    public static $AMBIENTE_PRODUCAO = 'P';
    public static $AMBIENTE_DESENVOLVIMENTO = 'D';
    // Tipos de mensagens
    public static $CLASSE_MSG_ERRO = "alert alert-danger";
    public static $CLASSE_MSG_AVISO = "alert alert-warning";
    public static $CLASSE_MSG_INFORMACAO = "alert alert-info";
    // alturas de textarea
    private static $ALT_TEXT_AREA_P = 100;
    private static $ALT_TEXT_AREA_M = 200;
    private static $ALT_TEXT_AREA_G = 400;
    // constantes para retorno na aba correta
    public static $ABA_PARAM = "aba";
    public static $ABA_MPA_INF_COMP = "1";
    public static $ABA_MPA_AVALIACAO = "2";
    public static $ABA_MPA_CHAMADA = "3";
    public static $ABA_MPA_NOTICIA = "4";
    public static $ABA_MPA_INF_ANEXAS = "5";
    // mensagens gerais
    public static $MSG_CAMPO_OBRIG = "<div class='text-red col-md-12 col-sm-12 col-xs-12'>Campos marcados com * são obrigatórios.</div>";
    public static $MSG_CAMPO_OBRIG_TODOS = "<div class='text-red col-md-12 col-sm-12 col-xs-12'>Todos os campos são obrigatórios.</div>";
    public static $MSG_TABELA_VAZIA = "<div class='callout callout-warning'>Não há itens a serem exibidos.</div>";
    // string de campo vazio da tabela
    public static $STR_CAMPO_VAZIO = "---";
    // cor destacada dos emails
    public static $COR_DESTAQUE_EMAIL_HEX = "#104778";

    public static function vazioNulo($valor) {
        if (is_array($valor)) {
            return count($valor) == 0;
        }
        return $valor === NULL || $valor === "" || $valor === "''" || $valor === "NULL";
    }

    public static function getScriptFormatacaoMoeda($idCampo) {
        return "$('#$idCampo').priceFormat({
                    prefix: '',
                    centsSeparator: '.',
                    thousandsSeparator: '',
                    limit: 5,
                    centsLimit: 2,
                    clearOnEmpty:false
                    });";
    }

    public static function getAlturaTextArea($nrMaxCaracter, $texto = NULL) {
        if ($texto != NULL) {
            // transformando pulos de linha em caracteres a mais
            $cont = 0;
            $tam = strlen($texto);
            while ($cont < $tam) {
                // pesquisando pulo de linha
                $pesq = strpos($texto, "\n", $cont);
                if ($pesq === FALSE) {
                    break;
                }
                $cont = $pesq + 1;
                $nrMaxCaracter += 130;
            }
        }
        if ($nrMaxCaracter < 600) {
            return Util::$ALT_TEXT_AREA_P;
        }
        if ($nrMaxCaracter < 1500) {
            return Util::$ALT_TEXT_AREA_M;
        }
        return Util::$ALT_TEXT_AREA_G;
    }

}

?>
