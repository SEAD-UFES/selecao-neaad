<?php

global $CFG;
require_once($CFG->rpasta . "/negocio/FormacaoAcademica.php");
require_once($CFG->rpasta . "/controle/CTProcesso.php");
require_once($CFG->rpasta . "/negocio/Publicacao.php");
require_once($CFG->rpasta . "/negocio/RecursoResulProc.php");
require_once($CFG->rpasta . "/controle/CTDepartamento.php");
require_once($CFG->rpasta . "/controle/CTUsuario.php");
require_once($CFG->rpasta . "/controle/CTPais.php");
require_once($CFG->rpasta . "/controle/CTEstado.php");
require_once($CFG->rpasta . "/controle/CTCandidato.php");
require_once($CFG->rpasta . "/controle/CTParametrizacao.php");

function getHtmlSelectGenerico($idSelect, $listaDados, $idSelecionado = NULL, $obrigatorio = FALSE, $dsSelecione = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = $dsSelecione == NULL ? DS_SELECT_SELECIONE : $dsSelecione;

    $obrigatorio = $obrigatorio ? "required" : "";

    $ret = "<select $obrigatorio class='form-control' id='$idSelect' name='$idSelect'>";
    $ret .= "<option value=$codSel>$dsSel</option>";
    $vetChaves = array_keys($listaDados);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $listaDados[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idSelecionado != NULL && $id == $idSelecionado) {
            $ret .= "<option value='$id' selected>$nome</option>";
        } else {
            $ret .= "<option value='$id'>$nome</option>";
        }
    }
    $ret .= "</select>";

    return $ret;
}

function impressaoAtivoInativo($idSelect = "st", $idSelecionado = NULL, $obrigatorio = FALSE) {
    echo getHtmlSelectGenerico($idSelect, NGUtil::getListaSituacaoDsSit(), $idSelecionado, $obrigatorio, DS_SELECT_SELECIONE);
}

function impressaoNacionalidade($idNacionalidade = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $codOutra = ID_SELECT_OUTRO;
    $dsOutra = DS_SELECT_OUTRA;
    echo "<select class='form-control' id='idNacionalidade' name='idNacionalidade'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarTodasNacionalidadesCT();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idNacionalidade != null && $id == $idNacionalidade) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    //outra 
    if ($idNacionalidade != null && $idNacionalidade == $codOutra) {
        echo "<option value='$codOutra' selected>$dsOutra</option>";
    } else {
        echo "<option value='$codOutra'>$dsOutra</option>";
    }
    echo "</select>";
}

/**
 * @param string $nmRadio
 * @param array $listaItens
 * @param callback function  $callBackNomeId - Funcao que retorna um array tipo [id,nome]
 * @param string $idSelecionado - Id do radio selecionado. Opcional
 * @param boolean $desabilitado - Informa se o radio sera impresso desabilitado. Padrao: Falso
 * @param boolean $comMargem - Informa se o radio sera impresso com margem. Padrao: Falso
 */
function impressaoRadioGenerico($nmRadio, $listaItens, $callBackNomeId, $idSelecionado = NULL, $desabilitado = FALSE, $comMargem = FALSE) {
    $margem = $comMargem ? "style='margin-left:1em;'" : "";
    // montando radio
    for ($i = 0; $i < sizeof($listaItens); $i++) {
        $dados = $callBackNomeId($listaItens[$i]);
        $id = $dados[0];
        $nome = $dados[1];
        echo "<div $margem>";
        echo "<input type='radio' name='$nmRadio' value='$id'";
        if (!Util::vazioNulo($idSelecionado) && $idSelecionado == $id) {
            echo " checked ";
        }
        if ($desabilitado) {
            echo " readonly='readonly' ";
        }
        echo "> $nome&nbsp;";

        echo "</div>";
    }
}

function impressaoRadioSexo($tpSexo = NULL) {
    // montando radio
    $lista = IdentificacaoCandidato::getListaSexoDsSexo();
    $vetChaves = array_keys($lista);
    echo "<div>";
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpSexo != null && $id == $tpSexo) {
            echo "<input type='radio' name='dsSexo' value='$id' checked> $nome&nbsp;";
        } else {
            echo "<input type='radio' name='dsSexo' value='$id'> $nome&nbsp;";
        }
    }
    echo "</div>";
}

function impressaoRadioSituacaoForm($stSituacao = NULL) {
    // montando radio
    $lista = FormacaoAcademica::getListaSituacaoFormDsSituacao();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($stSituacao != null && $id == $stSituacao) {
            echo "<label class='radio-inline'><input type='radio' name='stFormacao' value='$id' checked>$nome</label>";
        } else {
            echo "<label class='radio-inline'><input type='radio' name='stFormacao' value='$id'>$nome</label>";
        }
    }
}

function impressaoRadioAtivoInativo($stSituacao = NULL) {
    // montando radio
    $lista = NGUtil::getListaSituacaoDsSit();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($stSituacao != null && $id == $stSituacao) {
            echo "<label class='radio-inline'><input type='radio' name='stSituacao' value='$id' checked>$nome</label>";
        } else {
            echo "<label class='radio-inline'><input type='radio' name='stSituacao' value='$id'>$nome</label>";
        }
    }
}

function impressaoRadioSimNao($idRadio = "radioSimNao", $vlMarcado = NULL, $edicao = TRUE) {
    // montando radio
    $edicao = $edicao ? "" : "disabled";
    $lista = array(FLAG_BD_SIM => NGUtil::getDsSimNao(FLAG_BD_SIM), FLAG_BD_NAO => NGUtil::getDsSimNao(FLAG_BD_NAO));
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($vlMarcado != null && $id == $vlMarcado) {
            echo "<label class='radio-inline'><input $edicao type='radio' name='$idRadio' value='$id' checked>$nome</label> ";
        } else {
            echo "<label class='radio-inline'><input $edicao type='radio' name='$idRadio' value='$id'>$nome</label> ";
        }
    }
}

function impressaoOcupacao($idOcupacao = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $codOutra = ID_SELECT_OUTRO;
    $dsOutra = DS_SELECT_OUTRA;
    echo "<select class='form-control' id='idOcupacao' name='idOcupacao'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarTodasOcupacoesCT();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idOcupacao != null && $id == $idOcupacao) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }

    //outra 
    if ($idOcupacao != null && $idOcupacao == $codOutra) {
        echo "<option value='$codOutra' selected>$dsOutra</option>";
    } else {
        echo "<option value='$codOutra'>$dsOutra</option>";
    }
    echo "</select>";
}

function impressaoArea($idArea = NULL, $idSelect = NULL, $semEdicao = FALSE) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    if ($idSelect != NULL) {
        echo "<select class='form-control' $semEdicao id='$idSelect' name='$idSelect'>";
    } else {
        echo "<select class='form-control' $semEdicao id='idAreaConh' name='idAreaConh'>";
    }
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarTodasAreaConhCT();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idArea != null && $id == $idArea) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoContato() {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;

    echo "<select class='form-control' id='tpContato' name='tpContato'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = Usuario::getListaContatoDsContato();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        echo "<option value='$id'>$nome</option>";
    }
    echo "</select>";
}

function impressaoSituacaoForm($stSituacao = NULL, $idSelect = NULL, $semEdicao = FALSE) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    if ($idSelect != NULL) {
        echo "<select class='form-control' $semEdicao id='$idSelect' name='$idSelect'>";
    } else {
        echo "<select class='form-control' $semEdicao id='stFormacao' name='stFormacao'>";
    }
    echo "<option value=$codSel>$dsSel</option>";
    $lista = FormacaoAcademica::getListaSituacaoFormDsSituacao();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($stSituacao != null && $id == $stSituacao) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoFormulaRapida($listaEtapas) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;

    echo "<select class='form-control' id='idFormulaRapida' name='idFormulaRapida'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = MacroConfProc::getListaFormulaRapida($listaEtapas);
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        echo "<option value='$id'>$nome</option>";
    }
    echo "</select>";
}

function impressaoTpItemAnexoProcTela($tpItemTela = NULL, $idSelect = NULL, $semEdicao = FALSE, $script = "") {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    if ($idSelect != NULL) {
        echo "<select $script class='form-control-esp' $semEdicao id='$idSelect' name='$idSelect'>";
    } else {
        echo "<select $script class='form-control-esp' $semEdicao id='tpItemAnexoProcTela' name='tpItemAnexoProcTela'>";
    }
    echo "<option value=$codSel>$dsSel</option>";
    $lista = ItemAnexoProc::getListaTpCompDsTipoTelaItem();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpItemTela != null && $id == $tpItemTela) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoPais($idPais = NULL, $idSelect = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    if ($idSelect != NULL) {
        echo "<select class='form-control' id='$idSelect' name='$idSelect'>";
    } else {
        echo "<select class='form-control' id='idPais' name='idPais'>";
    }
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarTodosPaisesCT();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idPais != null && $id == $idPais) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoEstado($idEstado = NULL, $idSelect = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    if ($idSelect != NULL) {
        echo "<select class='form-control' id='$idSelect' name='$idSelect'>";
    } else {
        echo "<select class='form-control' id='idEstado' name='idEstado'>";
    }
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarTodosEstadosCT();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idEstado != null && $id == $idEstado) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoRaca($tpRaca = NULL) {
    //percorrendo  para montar select
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='tpRaca' name='tpRaca'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = IdentificacaoCandidato::getListaRacaDsRaca();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpRaca != null && $id == $tpRaca) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoEstadoCivil($tpEstadoCivil = NULL) {
    //percorrendo  para montar select
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='tpEstadoCivil' name='tpEstadoCivil'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = IdentificacaoCandidato::getListaEstCivilDsEstCivil();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpEstadoCivil != null && $id == $tpEstadoCivil) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoRecurso($tpRecursoSel = NULL, $semEdicao = FALSE) {
    //percorrendo  para montar select
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";

    echo "<select $semEdicao class='form-control' id='tpRecurso' name='tpRecurso'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = RecursoResulProc::getListaTipoDsTipo();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpRecursoSel != null && $id == $tpRecursoSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoSitRecurso($stRecursoSel = NULL, $semEdicao = FALSE) {
    //percorrendo  para montar select
    $codSel = ID_SELECT_SELECIONE;
    // $dsSel = DS_SELECT_SELECIONE;
    $dsSel = "Selecione";
    $semEdicao = $semEdicao ? "disabled" : "";

    echo "<select $semEdicao class='form-control' id='stRecurso' name='stRecurso'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = RecursoResulProc::getListaSitDsSit();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($stRecursoSel != null && $id == $stRecursoSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoSitRecursoDefInd($stRecursoSel = NULL, $semEdicao = FALSE) {
    //percorrendo  para montar select
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";

    echo "<select class='form-control' $semEdicao id='stRecurso' name='stRecurso'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = RecursoResulProc::getListaSitDsSitDefIndef();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($stRecursoSel != null && $id == $stRecursoSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoPublicacao($tpPublicacao = NULL, $semEdicao = FALSE) {
    //percorrendo  para montar select
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='tpPublicacao' name='tpPublicacao'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = Publicacao::getListaTipoDsTipo();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpPublicacao != null && $id == $tpPublicacao) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoPartEvento($tpPartEvento = NULL, $semEdicao = FALSE) {
    //percorrendo  para montar select
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='tpPartEvento' name='tpPartEvento'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = ParticipacaoEvento::getListaTipoDsTipo();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpPartEvento != null && $id == $tpPartEvento) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoAtuacao($tpAtuacao = NULL, $semEdicao = FALSE) {
    //percorrendo  para montar select
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='tpAtuacao' name='tpAtuacao'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = Atuacao::getListaTipoDsTipo();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpAtuacao != null && $id == $tpAtuacao) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoFormacao($tpFormacaoSel = NULL, $semEdicao = FALSE) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = "Selecione"; // DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled data-naoedicao='true'" : "";
    echo "<select class='form-control' $semEdicao id='tpFormacao' name='tpFormacao'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarTodosTiposCursoCT();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpFormacaoSel != null && $id == $tpFormacaoSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTpClassificacaoInsc($tpClassificacaoSel = NULL, $dadoPadrao = NULL) {
    //percorrendo  para montar select
    $dadoPadrao = $dadoPadrao != NULL ? "data-padrao='$dadoPadrao'" : "";
    echo "<select class='form-control' $dadoPadrao id='tpClassificacao' name='tpClassificacao'>";
    $lista = InscricaoProcesso::getListaTpClasDsClas();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpClassificacaoSel != NULL && $tpClassificacaoSel == $id) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTpOrdenacaoInsc($tpOrdenacaoSel = NULL, $dadoPadrao = NULL) {
    //percorrendo  para montar select
    $dadoPadrao = $dadoPadrao != NULL ? "data-padrao='$dadoPadrao'" : "";
    echo "<select class='form-control' $dadoPadrao id='tpOrdenacao' name='tpOrdenacao'>";
    $lista = InscricaoProcesso::getListaTpOrdenacaoDsOrdenacao();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpOrdenacaoSel != NULL && $tpOrdenacaoSel == $id) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTpExibSituacaoInsc($tpExibicaoSitSel = NULL, $dadoPadrao = NULL) {
    //percorrendo  para montar select
    $dadoPadrao = $dadoPadrao != NULL ? "data-padrao='$dadoPadrao'" : "";
    echo "<select class='form-control' $dadoPadrao id='tpExibSituacao' name='tpExibSituacao'>";
    $lista = InscricaoProcesso::getListaTpExibSitDsTpExib();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpExibicaoSitSel != NULL && $tpExibicaoSitSel == $id) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoPolos($idChamada = NULL, $listaPoloChamada = NULL) {
    //percorrendo 
    echo "<select id='idPolos' name='idPolos[]' multiple='multiple'>";
    $lista = buscarTodosPolosCT();
    if ($idChamada != NULL) {
        $listaPoloChamada = $listaPoloChamada == NULL ? array_keys(buscarPoloPorChamadaCT($idChamada, PoloChamada::getFlagPoloAtivo())) : $listaPoloChamada;
    }
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idChamada != NULL && in_array($id, $listaPoloChamada)) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoPolosPorProcesso($idChamada, $flagSitPolo, $poloSel = NULL, $multipla = FALSE) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = "Polo:"; //DS_SELECT_SELECIONE;
    if (!$multipla) {
        echo "<select class='form-control' id='idPolo' name='idPolo'>";
        echo "<option value=$codSel>$dsSel</option>";
    } else {
        echo "<select id='idPolo' name='idPolo[]' multiple='multiple'>";
    }
    $lista = buscarPoloPorChamadaCT($idChamada, $flagSitPolo);
    $vetChaves = array_keys($lista);
    $tam = sizeof($vetChaves);
    for ($i = 0; $i < $tam; $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($poloSel != NULL && $poloSel == $id) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";

    //retornando tamanho da lista
    return $tam;
}

function impressaoAreaAtu($idChamada = NULL, $listaAreaAtuChamada = NULL) {
    //percorrendo 
    echo "<select id='idAreasAtu' name='idAreasAtu[]' multiple='multiple'>";
    $lista = buscarTodasAreaConhFilhasCT();
    if ($idChamada != NULL) {
        $listaAreaAtuChamada = $listaAreaAtuChamada == NULL ? array_keys(buscarAreaAtuPorChamadaCT($idChamada, AreaAtuChamada::getFlagAreaAtiva())) : $listaAreaAtuChamada;
    }
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idChamada != NULL && in_array($id, $listaAreaAtuChamada)) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoReservaVagas($idChamada = NULL, $listaReservaChamada = NULL) {
    //percorrendo 
    echo "<select id='idReservaVagas' name='idReservaVagas[]' multiple='multiple'>";
    $lista = buscarTodasReservaVagasCT();
    if ($idChamada != NULL) {
        $listaReservaChamada = $listaReservaChamada == NULL ? array_keys(buscarIdsReservaVagaPorChamadaCT($idChamada, ReservaVagaChamada::getFlagReservaAtiva())) : $listaReservaChamada;
    }
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idChamada != NULL && in_array($id, $listaReservaChamada)) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoAreaAtuPorProcesso($idChamada, $flagSituacao, $areaAtuSel = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = "Selecione"; ///DS_SELECT_SELECIONE;

    echo "<select class='form-control' id='idAreaAtuChamada' name='idAreaAtuChamada'>";
    echo "<option value=$codSel>$dsSel</option>";

    $lista = buscarAreaAtuPorChamadaCT($idChamada, $flagSituacao);
    $vetChaves = array_keys($lista);
    $tam = sizeof($vetChaves);
    for ($i = 0; $i < $tam; $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($areaAtuSel != NULL && $areaAtuSel == $id) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";

    //retornando tamanho da lista
    return $tam;
}

function impressaoReservaVagaPorProcesso($idChamada, $flagSituacao, $reservaVagaSel = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = "Reserva de Vaga:"; ///DS_SELECT_SELECIONE;

    echo "<select class='form-control' id='idReservaVaga' name='idReservaVaga'>";
    echo "<option value=$codSel>$dsSel</option>";

    $lista = buscarIdsReservaVagaPorChamadaCT($idChamada, $flagSituacao, TRUE);
    $vetChaves = array_keys($lista);
    $tam = sizeof($vetChaves);
    for ($i = 0; $i < $tam; $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($reservaVagaSel != NULL && $reservaVagaSel == $id) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";

    //retornando tamanho da lista
    return $tam;
}

/**
 * Funçao que imprime a lista de avaliadores de um curso, inclui:
 * Avaliadores do curso e avaliadores nao alocados.
 * @param int $idCurso
 * @param array $avalSel
 * @return void - Impressao em tela
 */
function impressaoAvalLivrePorCurso($idCurso, $avalSel = NULL) {
    // criando select
    echo "<select class='form-control' style='width:350px'  id='idAvaliador' name='idAvaliador[]' multiple='multiple'>";

    $lista = buscarAvalLivrePorCursoCT($idCurso);
    $vetChaves = array_keys($lista);
    $tam = sizeof($vetChaves);
    for ($i = 0; $i < $tam; $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($avalSel != NULL && array_search($id, $avalSel) !== FALSE) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";

    //retornando tamanho da lista
    return $tam;
}

function impressaoChamadaPorProcesso($idProcesso, $idChamadaSel = NULL, $dadoPadrao = NULL, $semVazio = FALSE) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $dadoPadrao = $dadoPadrao != NULL ? "data-padrao='$dadoPadrao'" : "";
    echo "<select class='form-control' $dadoPadrao id='idChamada' name='idChamada'>";
    if (!$semVazio) {
        echo "<option value=$codSel>Tipo:</option>";
    }
    $lista = buscarIdDsChamadaPorProcessoCT($idProcesso);
    $vetChaves = array_keys($lista);
    $tam = sizeof($vetChaves);
    for ($i = 0; $i < $tam; $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idChamadaSel != NULL && $idChamadaSel == $id) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";

    //retornando tamanho da lista
    return $tam;
}

function impressaoTipoExpProcesso($idTipoSel = NULL) {
    //percorrendo 
    echo "<select class='form-control' id='idTipoExportacao' name='idTipoExportacao'>";
    $lista = Processo::getListaTipoDsTipoExp();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idTipoSel != null && $id == $idTipoSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoCargo($idTipo = NULL, $semEdicao = FALSE) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select $semEdicao class='form-control' id='idTipoCargo' name='idTipoCargo'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarTodosTiposCargoCT(FALSE, NGUtil::getSITUACAO_ATIVO());
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idTipo != null && $id == $idTipo) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoUsuario($idUsuario = NULL, $stSituacao = NULL, $tpUsuario = NULL) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='idUsuario' name='idUsuario'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscaTodosUsuariosCT($stSituacao, $tpUsuario);
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idUsuario != null && $id == $idUsuario) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

/**
 * 
 * @param int $idCurso
 * @param boolean $dsSelNormal - TRUE para aparecer o famoso 'Selecione'.
 * @param boolean $semEdicao - Flag para bloquear ediçao
 * @param string $idSelect - Id do select 
 */
function impressaoCurso($idCurso = NULL, $dsSelNormal = NULL, $semEdicao = FALSE, $idSelect = "idCurso") {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled data-naoedicao='true'" : "";
    $dsSel = $dsSelNormal == NULL ? "&LT;Não alocado&GT;" : DS_SELECT_SELECIONE;
    echo "<select $semEdicao class='form-control' id='$idSelect' name='$idSelect'>";
    echo "<option value=$codSel>Selecione</option>";
    $lista = buscaTodosCursosCT(NGUtil::getSITUACAO_ATIVO());
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idCurso != null && $id == $idCurso) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoDepartamento($idDepartamento = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='idDepartamento' name='idDepartamento'>";
    echo "<option value=$codSel>Selecione</option>";
    $lista = buscaTodosDepartamentosCT(NGUtil::getSITUACAO_ATIVO());
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idDepartamento != null && $id == $idDepartamento) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoSelectAno($idSelect = "ano", $anoSel = NULL, $anoInicial = 1900) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='$idSelect' name='$idSelect'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = gerarListaAno($anoInicial);
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($anoSel != null && $id == $anoSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoPrivacidadeNot($idSelect = "idTipo", $privacidadeSel = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='$idSelect' name='$idSelect'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = Noticia::getListaTipoDsTipo();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($privacidadeSel != null && $id == $privacidadeSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTpLinkNot($tpLink = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = "Tipo...";
    echo "<select class='form-control' id='tpLinkNot' name='tpLinkNot'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = Noticia::getListaTipoLinkDsTpLink();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpLink != null && $id == $tpLink) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoCurso($tpCurso = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='tpCurso' name='tpCurso'>";
    echo "<option value=$codSel>Selecione</option>";
    $lista = buscarTodosTiposCursoCT();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpCurso != null && $id == $tpCurso) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoUsuario($tpUsuario = NULL) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='tpUsuario' name='tpUsuario'>";
    echo "<option value=$codSel>Tipo de Usuário</option>";
    $lista = Usuario::getListaTipoDsTipo();
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpUsuario != null && $id == $tpUsuario) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoCategoriaAval($listaBloq = array(), $tpCategoria = NULL, $semEdicao = FALSE) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='tpCategoriaAval' name='tpCategoriaAval'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = CategoriaAvalProc::getListaTpDsTipo();

    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if (array_search($id, $listaBloq) !== FALSE) {
            echo "<option value='$id' disabled>$nome</option>";
        } elseif ($tpCategoria != null && $id == $tpCategoria) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }

    echo "</select>";
}

function impressaoTipoItemAval($tpCategoria, $tpItemAval = NULL, $semEdicao = FALSE) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='tpItemAval' name='tpItemAval'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = ItemAvalProc::getListaTpDsTipo($tpCategoria);

    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpItemAval != null && $id == $tpItemAval) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }

    echo "</select>";
}

function impressaoTipoGrupoAnexoProc($tpGrupoAnexoProc = NULL, $semEdicao = FALSE) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='tpGrupoAnexoProc' name='tpGrupoAnexoProc'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = GrupoAnexoProc::getListaTipoDsTipoGrupo();

    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpGrupoAnexoProc != null && $id == $tpGrupoAnexoProc) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoAvalGrupoAnexoProc($tpAvalGrupoAnexoProc = NULL, $semEdicao = FALSE) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='tpAvalGrupoAnexoProc' name='tpAvalGrupoAnexoProc'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = GrupoAnexoProc::getListaTipoAvalDsTipoAvaliacao();

    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpAvalGrupoAnexoProc != null && $id == $tpAvalGrupoAnexoProc) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoEtapaAvalPorProc($idProcesso, $inserirNova = TRUE, $idEtapaAvalSel = NULL, $dadoPadrao = NULL, $semEdicao = FALSE) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    $dadoPadrao = $dadoPadrao != NULL ? "data-padrao='$dadoPadrao'" : "";
    echo "<select class='form-control' $semEdicao $dadoPadrao id='idEtapaAval' name='idEtapaAval'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarSelectEtapaAvalPorProcCT($idProcesso, $inserirNova, FALSE);

    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idEtapaAvalSel != null && $id == $idEtapaAvalSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoEtapaSelPenPorCham($idProcesso, $idChamada, $idEtapaSel = NULL, $semEdicao = FALSE) {
//percorrendo 
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='idEtapaSel' name='idEtapaSel'>";
    $lista = buscarEtapaPenPorChamadaCT($idProcesso, $idChamada);

    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idEtapaSel != null && $id == $idEtapaSel) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoMacro($catMacro, $idTipo = NULL, $semEdicao = FALSE) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    $semEdicao = $semEdicao ? "disabled" : "";
    echo "<select class='form-control' $semEdicao id='idTipoMacro' name='idTipoMacro'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = MacroAbs::getListaMacro($catMacro);

    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idTipo != null && $id == $idTipo) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }

    echo "</select>";
}

function impressaoGruposItemAval($idCategoria, $idGrupo = NULL, $idSelect = NULL) {
    //percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    if ($idSelect != NULL) {
        echo "<select class='form-control' id='$idSelect' name='$idSelect'>";
    } else {
        echo "<select class='form-control' id='idGrupoAval' name='idGrupoAval'>";
    }
    echo "<option value=$codSel>$dsSel</option>";
    $lista = buscarGruposItemAvalPorCatCT($idCategoria);
    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($idGrupo != null && $id == $idGrupo) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }
    echo "</select>";
}

function impressaoTipoAvalCategoriaAval($tpAval = NULL) {
//percorrendo 
    $codSel = ID_SELECT_SELECIONE;
    //$dsSel = DS_SELECT_SELECIONE;
    $dsSel = DS_SELECT_SELECIONE;
    echo "<select class='form-control' id='tpAvalCategoria' name='tpAvalCategoria'>";
    echo "<option value=$codSel>$dsSel</option>";
    $lista = CategoriaAvalProc::getListatpAvalDsAval();

    $vetChaves = array_keys($lista);
    for ($i = 0; $i < sizeof($vetChaves); $i++) {
        $nome = $lista[$vetChaves[$i]];
        $id = $vetChaves[$i];
        if ($tpAval != null && $id == $tpAval) {
            echo "<option value='$id' selected>$nome</option>";
        } else {
            echo "<option value='$id'>$nome</option>";
        }
    }

    echo "</select>";
}

?>
