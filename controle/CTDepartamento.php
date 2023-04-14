<?php

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/util/Mensagem.php";
require_once $CFG->rpasta . "/negocio/Departamento.php";

//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctdepartamento") {
    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
            new Mensagem("Você precisa estar logado como administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
            return;
        }
        //caso cadastro
        if ($acao == "criarDepartamento") {
            try {
                //criando objeto com os parâmetros
                $departamento = new Departamento(NULL, $_POST['dsNome'], NGUtil::getSITUACAO_ATIVO());

                //criando
                $departamento->criaDepartamento();

                //redirecionando
                new Mensagem("Departamento cadastrado com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/departamento/listarDepartamento.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso excluir
        if ($acao == "excluirDepartamento") {
            try {
                //recuperando parâmetro
                $idDepartamento = $_POST['idDepartamento'];

                //excluindo
                Departamento::excluirDepartamento($idDepartamento);

                //redirecionando
                new Mensagem("Departamento excluído com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/departamento/listarDepartamento.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        }

        //caso editar
        if ($acao == "editarDepartamento") {
            try {
                //recuperando parâmetro
                $departamento = new Departamento($_POST['idDepartamento'], $_POST['dsNome'], isset($_POST['stSituacao']) ? $_POST['stSituacao'] : NGUtil::getSITUACAO_ATIVO());

                //atualizando
                $departamento->atualizarDepartamento();

                //redirecionando
                new Mensagem("Departamento atualizado com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucEdicao", "$CFG->rwww/visao/departamento/listarDepartamento.php");
            } catch (NegocioException $n) {
                //redirecionando para erro
                new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
            } catch (Exception $e) {
                //redirecionando para erro
                new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
            }
            return;
        } else {
            //chamando página de erro
            new Mensagem("Chamada de função inconsistente.", Mensagem::$MENSAGEM_ERRO);
        }
    }
}

function validaNomeDepartamentoCTAjax($dsNome, $idDepartamento = NULL) {
    try {
        return Departamento::validaNomeDepartamento($dsNome, $idDepartamento);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

/**
 * 
 * @param FiltroDepartamento $filtroDep
 * @return Departamento
 */
function contaDepartamentosPorFiltroCT($filtroDep) {
    try {
        return Departamento::contaDepartamentosPorFiltro($filtroDep->getDsNome(), $filtroDep->getStSituacao());
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }
}

function buscarDepartamentoPorIdCT($idDepartamento) {
    try {
        return Departamento::buscarDepartamentoPorId($idDepartamento);
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }
}

function buscaTodosDepartamentosCT($stSituacao = NULL) {
    try {
        return Departamento::buscarTodosDepartamentos($stSituacao);
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }
}

/**
 * 
 * @param string $dsNome
 * @param string $stSituacao
 * @param int $inicioDados
 * @param int $qtdeDados
 * @return array
 */
function buscaDepartamentosPorFiltroCT($dsNome, $stSituacao, $inicioDados, $qtdeDados) {
    try {
        return Departamento::buscarDepartamentosPorFiltro($dsNome, $stSituacao, $inicioDados, $qtdeDados);
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }
}

function validaExclusaoDepCT($idDepartamento) {
    try {
        return Departamento::validaExclusaoDep($idDepartamento);
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }
}

function validaInativacaoDepCT($idDepartamento) {
    try {
        return Departamento::validaInativacaoDep($idDepartamento);
    } catch (NegocioException $n) {
        //redirecionando para erro
        new Mensagem($n->getMensagem(), Mensagem::$MENSAGEM_ERRO);
        return;
    } catch (Exception $e) {
        //redirecionando para erro
        new Mensagem($e->getMessage(), Mensagem::$MENSAGEM_ERRO);
        return;
    }
}

/**
 * 
 * @param FiltroDepartamento $filtroDep
 * @return string
 */
function tabelaDepartamentosPorFiltro($filtroDep) {
    //recuperando 
    $departamentos = buscaDepartamentosPorFiltroCT($filtroDep->getDsNome(), $filtroDep->getStSituacao(), $filtroDep->getInicioDados(), $filtroDep->getQtdeDadosPag());

    if (count($departamentos) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = " <table class='table table-hover table-responsive table-bordered'>
    <thead>
    <tr>
        <th>Código</th>
        <th>Nome</th>
        <th class='botao'><span class='fa fa-eye'></th>
        <th class='botao'><span class='fa fa-edit'></th>
        <th class='botao'><span class='fa fa-trash-o'></th>
    </tr>
    </thead>";

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($departamentos); $i++) {
        $temp = $departamentos[$i - 1];

        $linkConsultar = "<a id='linkConsultar' title='Consultar departamento' href='manterDepartamento.php?idDepartamento={$temp->getDEP_ID_DEPARTAMENTO()}&fn=consultar' class='itemTabela'><span class='fa fa-eye'></span></a>";
        $linkEditar = "<a id='linkEditar' title='Editar departamento' href='manterDepartamento.php?idDepartamento={$temp->getDEP_ID_DEPARTAMENTO()}&fn=editar' class='itemTabela'><i class='fa fa-edit'></i></a>";

        $podeExcluir = validaExclusaoDepCT($temp->getDEP_ID_DEPARTAMENTO());

        if ($podeExcluir) {
            $linkExcluir = "<a id='linkExcluir' title='Excluir departamento' href='manterDepartamento.php?idDepartamento={$temp->getDEP_ID_DEPARTAMENTO()}&fn=excluir' class='itemTabela'><span class='fa fa-trash-o'></span></a>";
        } else {
            $linkExcluir = "<a onclick='return false' id='linkExcluir' title='Você não pode excluir este departamento porque ele possui cursos.' href='' class='itemTabela'><i class='fa fa-ban'></i></a>";
        }

        // tratamento especial dos inativos
        if (!$temp->isAtivo()) {
            $adendoLinha = "title='Departamento inativo' class='inativo'";
            $codDep = Util::$STR_CAMPO_VAZIO;
        } else {
            $adendoLinha = "";
            $codDep = $temp->getDEP_ID_DEPARTAMENTO();
        }

        $ret .= " <tr $adendoLinha>
        <td>$codDep</td>
        <td>{$temp->getDEP_DS_DEPARTAMENTO()}</td>
        <td class='botao'>$linkConsultar</td>
        <td class='botao'>$linkEditar</td>
        <td class='botao'>$linkExcluir</td> 
        </tr>";
    }

    $ret .= "</table>";

    return $ret;
}

?>
