<?php

require_once dirname(__FILE__) . "/../config.php";
global $CFG;
require_once $CFG->rpasta . "/util/sessao.php";
require_once $CFG->rpasta . "/util/Mensagem.php";
require_once $CFG->rpasta . "/negocio/Curso.php";
require_once $CFG->rpasta . "/controle/CTUsuario.php";

//recuperando os parâmetros enviados via post
if (isset($_POST['valido']) && $_POST['valido'] == "ctcurso") {
    //verificando função
    if (isset($_GET['acao'])) {
        $acao = $_GET['acao'];
        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
            new Mensagem("Você precisa estar logado como administrador para acessar esta página.", Mensagem::$MENSAGEM_ERRO);
            return;
        }
        //caso criar
        if ($acao == "criarCurso") {
            try {
                //criando objeto
                $curso = new Curso(NULL, $_POST['tpCurso'], $_POST['idDepartamento'], $_POST['idUsuario'], $_POST['nmCurso'], $_POST['idAreaConh'], $_POST['idSubareaConh'], $_POST['dsCurso'], NGUtil::getSITUACAO_ATIVO());

                //criando
                $curso->criaCurso();

                //redirecionando
                new Mensagem("Curso cadastrado com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucInsercao", "$CFG->rwww/visao/curso/listarCurso.php");
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
        if ($acao == "editarCurso") {
            try {
                //criando objeto
                $curso = new Curso($_POST['idCurso'], $_POST['tpCurso'], $_POST['idDepartamento'], $_POST['idUsuario'], $_POST['nmCurso'], $_POST['idAreaConh'], $_POST['idSubareaConh'], $_POST['dsCurso'], isset($_POST['stSituacao']) ? $_POST['stSituacao'] : NGUtil::getSITUACAO_ATIVO());

                // recuperando avaliadores
                $listaAval = isset($_POST['idAvaliador']) ? $_POST['idAvaliador'] : NULL;

                //editando
                $curso->atualizarCurso($listaAval);

                //redirecionando
                new Mensagem("Curso atualizado com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucEdicao", "$CFG->rwww/visao/curso/listarCurso.php");
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
        if ($acao == "excluirCurso") {
            try {
                //recuperando parâmetro
                $idCurso = $_POST['idCurso'];

                //excluindo
                Curso::excluirCurso($idCurso);

                //redirecionando
                new Mensagem("Curso excluído com sucesso.", Mensagem::$MENSAGEM_INFORMACAO, NULL, "sucExclusao", "$CFG->rwww/visao/curso/listarCurso.php");
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

function validaNomeCursoCTAjax($nmCurso, $idCurso = NULL) {
    try {
        return Curso::validaNomeCurso($nmCurso, $idCurso);
    } catch (Exception $e) {
        //retornando false
        return FALSE;
    }
}

function buscarCursoPorCoordenadorCT($idCoordenador) {
    try {
        return Curso::buscaCursoPorCoordenador($idCoordenador);
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

function buscarCursoPorIdCT($idCurso) {
    try {
        return Curso::buscarCursoPorId($idCurso);
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

function buscarIdCursoPorUrlBuscaCT($urlBusca) {
    try {
        return Curso::buscarIdCursoPorUrlBusca($urlBusca);
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

function validaExclusaoCursoCT($idCurso) {
    try {
        return Curso::validaExclusaoCurso($idCurso);
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

function validaInativacaoCursoCT($idCurso) {
    try {
        return Curso::validaInativacaoCurso($idCurso);
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

function buscarCursoPorDepartamentoCT($idDepartamento) {
    try {
        return Curso::buscaCursoPorDepartamento($idDepartamento);
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

function buscaTodosCursosCT($stSituacao = NULL) {
    try {
        return Curso::buscaTodosCursos($stSituacao);
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
 * @param FiltroCurso $filtroCur
 * @return Curso
 */
function contaCursoPorFiltroCT($filtroCur) {
    try {
        return Curso::contaCursosPorFiltro($filtroCur->getNmCurso(), $filtroCur->getIdDepartamento(), $filtroCur->getTpCurso(), $filtroCur->getStSituacao());
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

function contaCursoPorDepartamentoCT($idDepartamento) {
    try {
        return Curso::contaCursoPorDepartamento($idDepartamento);
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

function buscarCursosPorFiltroCT($dsNome, $idDepartamento, $tpCurso, $stSituacao, $inicioDados, $qtdeDados) {
    try {
        return Curso::buscarCursosPorFiltro($dsNome, $idDepartamento, $tpCurso, $stSituacao, $inicioDados, $qtdeDados);
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
 * A variavel $coordenador controla o que e exibido. Se coordenador = TRUE (Padrao)
 * entao e exibido os avaliadores do curso
 * 
 * @param int $idUsuario
 * @param boolean $coordenador
 * @return void - Impressao em tela dos dados do curso
 */
function informacaoCursoCoordAvalCT($idUsuario, $coordenador = TRUE) {

    //recuperando curso do coordenador / avaliador
    if ($coordenador) {
        $curso = buscarCursoPorCoordenadorCT($idUsuario);
    } else {
        $usu = buscarUsuarioPorIdCT($idUsuario);
        $curso = buscarCursoPorIdCT($usu->getUSR_ID_CUR_AVALIADOR());
    }

    if ($curso == NULL) {
        echo "<span>No momento você não está alocado a um curso.</span>";
        return;
    }

    $dsTipo = $curso->TPC_NM_TIPO_CURSO;

    $descLegenda = $coordenador ? "Curso que você coordena" : "Curso para o qual você está alocado";

    //criando fieldset com o elemento
    echo "<fieldset style='padding-left: 5px;'>
        <legend>$descLegenda</legend>    
        <span class='tituloItemSemMargem'>Nome:</span><span class='textoItem'>{$curso->getCUR_NM_CURSO()}</span><br/>
        <span class='tituloItemSemMargem'>Departamento:</span><span class='textoItem'>{$curso->DEP_DS_DEPARTAMENTO}</span><br/>
        <span class='tituloItemSemMargem'>Chamada:</span><span class='textoItem'>$dsTipo</span><br/>
        <span class='tituloItemSemMargem'>Grande Área / Área:</span><span class='textoItem'>{$curso->getDsAreaSubarea()}</span><br/><br/>
        <span class='tituloItemSemMargem'>Descrição:</span><br/><span class='textoItem'>{$curso->getCUR_DS_CURSO()}</span><br/>";

    if ($coordenador) {
        echo "<br/><span class='tituloItemSemMargem'>Avaliadores:</span>";
        print tabelaAvaliadoresPorCurso($curso->getCUR_ID_CURSO());
    }

    echo "</fieldset><br/><br/>";
}

function tabelaCursosConsultaDepartamento($idDepartamento) {

    //recuperando 
    $cursos = buscarCursoPorDepartamentoCT($idDepartamento);

    if (count($cursos) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = " <table>
    <thead>
    <tr>
        <td class='textoEsquerda'>
            <span class='tituloTabela'>Nome</span>
        </td>
        <td class='textoEsquerda'>
            <span class='tituloTabela'>Tipo</span>
        </td>
        <td class='textoEsquerda'>
            <span class='tituloTabela'>Situação</span>
        </td>
        <td class='textoEsquerda'>
            <span class='tituloTabela'>Coordenador</span>
        </td>";

    $ret .="</tr></thead>";

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($cursos); $i++) {
        $temp = $cursos[$i - 1];

        $tipo = Curso::getDsTipo($temp->tpCurso);
        $situacao = Curso::getDsSituacao($temp->stSituacao);
        $coordenador = $temp->idCoordenador != NULL ? $temp->nmCoordenador : "-";
        $ret .= " <tr>
        <td class='textoEsquerda'>
         <span class='itemTabela'>$temp->nmCurso</span>
        </td>
        <td class='textoEsquerda'>
           <span class='itemTabela'>$tipo</span>
        </td>
        <td class='textoEsquerda'>
            <span class='itemTabela'>$situacao</span>
        </td>
        <td class='textoEsquerda'>
            <span class='itemTabela'>$coordenador</span>
        </td>";

        $ret .= "</tr>";
    }

    $ret .= "</table>";

    return $ret;
}

/**
 * 
 * @param FiltroCurso $filtroCur
 * @return string
 */
function tabelaCursosPorFiltro($filtroCur) {

    //recuperando 
    $cursos = buscarCursosPorFiltroCT($filtroCur->getNmCurso(), $filtroCur->getIdDepartamento(), $filtroCur->getTpCurso(), $filtroCur->getStSituacao(), $filtroCur->getInicioDados(), $filtroCur->getQtdeDadosPag());

    if (count($cursos) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<div class='table-responsive completo'><table class='table table-hover table-bordered'>
    <thead>
    <tr>
        <th>Código</th>
        <th>Nome</th>
        <th class='campoDesktop'>Tipo</th>
        <th>Departamento</th>
        <th class='botao'><span class='fa fa-eye'></span></th>
        <th class='botao'><i class='fa fa-edit'></i></th>
        <th class='botao'><span class='fa fa-trash-o'></span></th>
    </tr>
    </thead>";

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($cursos); $i++) {
        $temp = $cursos[$i - 1];

        $linkConsultar = "<a id='linkConsultar' title='Consultar curso' href='manterCurso.php?idCurso={$temp->getCUR_ID_CURSO()}&fn=consultar' class='itemTabela'><span class='fa fa-eye'></span></a>";
        $linkEditar = "<a id='linkEditar' title='Editar curso' href='manterCurso.php?idCurso={$temp->getCUR_ID_CURSO()}&fn=editar' class='itemTabela'><i class='fa fa-edit'></i></a>";

        $podeExcluir = validaExclusaoCursoCT($temp->getCUR_ID_CURSO());

        if ($podeExcluir) {
            $linkExcluir = "<a id='linkExcluir' title='Excluir curso' href='excluirCurso.php?idCurso={$temp->getCUR_ID_CURSO()}' class='itemTabela'><span class='fa fa-trash-o'></span></a>";
        } else {
            $linkExcluir = "<a onclick='return false' id='linkExcluir' title='Você não pode excluir este curso porque ele possui editais.' href='' class='itemTabela'><i class='fa fa-ban'></i></a>";
        }

        // tratamento especial dos inativos
        if (!$temp->isAtivo()) {
            $adendoLinha = "title='Curso inativo' class='inativo'";
            $codCurso = Util::$STR_CAMPO_VAZIO;
        } else {
            $adendoLinha = "";
            $codCurso = $temp->getCUR_ID_CURSO();
        }

        $ret .= "<tr $adendoLinha><td>$codCurso</td>
        <td>{$temp->getCUR_NM_CURSO()}</td>
        <td class='campoDesktop'>{$temp->TPC_NM_TIPO_CURSO}</td>
        <td>{$temp->DEP_DS_DEPARTAMENTO}</td>
        <td class='botao'>$linkConsultar</td>
        <td class='botao'>$linkEditar</td>
        <td class='botao'>$linkExcluir</td>  
        </tr>";
    }

    $ret .= "</table></div>
            <div class='campoMobile' style='margin-bottom:2em;'>
                Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
            </div>";

    return $ret;
}

function tabelaAvaliadoresPorCurso($idCurso) {

    //recuperando 
    $avaliadores = buscarUsusAvaliadoresPorCursoCT($idCurso);

    if (count($avaliadores) == 0) {
        return Util::$MSG_TABELA_VAZIA;
    }

    $ret = "<div class='table-responsive'>
                <table class='table table-hover table-bordered'>
                    <thead>
                        <tr>
                            <th>Avaliador</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>";

    //iteração para exibir itens
    for ($i = 1; $i <= sizeof($avaliadores); $i++) {
        $temp = $avaliadores[$i - 1];

        $ret .= "<tr>
                    <td>{$temp->getUSR_DS_NOME()}</td>
                    <td>{$temp->getUSR_DS_EMAIL()}</td> 
                </tr>";
    }

    $ret .= "</tbody>
        </table>
    </div>
    <div class='campoMobile' style='margin-bottom:2em;'>
        Obs: Se necessário, deslize a tabela da direita para a esquerda para ver colunas ocultas.
    </div>";

    return $ret;
}

?>