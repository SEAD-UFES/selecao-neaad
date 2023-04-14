<!DOCTYPE html>
<html>
    <head>  
        <title>Consultar Usuário - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        //verificando se está logado como administrador
        require_once ($CFG->rpasta . "/util/sessao.php");

        if (estaLogado(Usuario::$USUARIO_ADMINISTRADOR) == NULL) {
            //redirecionando para tela de login
            header("Location: $CFG->rwww/acesso");
            return;
        }
        ?>

        <?php
        //verificando passagem por get
        if (!isset($_GET['idUsuario'])) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        //recuperando usuário
        require_once ($CFG->rpasta . "/controle/CTUsuario.php");
        require_once ($CFG->rpasta . "/controle/CTCurriculo.php");
        $idUsuario = $_GET['idUsuario'];
        $objUsuario = buscarUsuarioPorIdCT($idUsuario);

        // incluindo classes importantes
        require_once ($CFG->rpasta . "/util/filtro/FiltroPublicacao.php");
        require_once ($CFG->rpasta . "/util/filtro/FiltroPartEvento.php");
        require_once ($CFG->rpasta . "/util/filtro/FiltroAtuacao.php");
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>

        <?php
        //buscando objeto candidato e fragmento html de ultima atualização
        $candidato = buscarCandidatoPorIdUsuCT($objUsuario->getUSR_ID_USUARIO());
        require_once ($CFG->rpasta . "/visao/candidato/fragmentoDtAtuCurriculo.php");
        ?>

    </head>
    <body>  
        <?php
        include ($CFG->rpasta . "/include/cabecalho.php");
        ?>

        <div id="main">
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1>Você está em: Cadastros > <a href="<?php print $CFG->rwww; ?>/visao/usuario/listarUsuario.php">Usuário</a> > <strong>Consultar</strong></h1>
                </div>

                <div class="col-full m02">
                    <div class="panel-group ficha-tecnica" id="accordion">
                        <div class="painel">
                            <div class="panel-heading">
                                <a style="text-decoration:none;" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                    <h4 class="panel-title">Ficha Técnica</h4>
                                </a>
                            </div>

                            <div id="collapseOne" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <p>
                                        <i class="fa fa-user"></i>
                                        <b>Nome:</b> <?php print $objUsuario->getUSR_DS_NOME(); ?> <separador class='barra'></separador>
                                    <b>Tipo:</b> <?php print Usuario::getDsTipo($objUsuario->getUSR_TP_USUARIO()); ?> <separador class='barra'></separador> 
                                    <b>Login:</b> <?php print $objUsuario->getUSR_DS_LOGIN(); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-full m02">
                    <div class="tabbable"> 
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab1" data-toggle="tab">Dados de Acesso</a></li>
                            <?php if ($objUsuario->getUSR_TP_USUARIO() == Usuario::$USUARIO_CANDIDATO) { ?>
                                <li><a href="#tab2" data-toggle="tab">Identificação</a></li>
                                <li><a href="#tab3" data-toggle="tab">Endereço / Contato</a></li>
                                <li><a href="#tab4" data-toggle="tab">Formação</a></li>
                                <li><a href="#tab5" data-toggle="tab">Publicação</a></li>
                                <li><a href="#tab6" data-toggle="tab">Part. em Evento</a></li>
                                <li><a href="#tab7" data-toggle="tab">Atuação</a></li>
                            <?php } ?>
                        </ul>

                        <div class="tab-content col-full">
                            <div class="tab-pane active" id="tab1">
                                <div class="completo m02">
                                    <h3 class="sublinhado">Informações</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">Nome do Usuário:</td>
                                                <td class="campo80"><?php print $objUsuario->getUSR_DS_NOME(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Código:</td>
                                                <td class="campo80"><?php print $objUsuario->getUSR_ID_USUARIO(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Tipo:</td>
                                                <td class="campo80"><?php print Usuario::getDsTipo($objUsuario->getUSR_TP_USUARIO()); ?></td>
                                            </tr>
                                            <tr id="divCoordenador" style="display:none;">
                                                <td class="campo20">Curso que coordena:</td>
                                                <td class="campo80">
                                                    <?php
                                                    require_once ($CFG->rpasta . "/controle/CTCurso.php");
                                                    $curso = buscarCursoPorCoordenadorCT($objUsuario->getUSR_ID_USUARIO());
                                                    if ($curso != NULL) {
                                                        ?>
                                                        <span class='textoItem'><?php print $curso->getCUR_NM_CURSO(); ?></span><br/>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <span class='textoItem'><i>Não alocado</i></span><br/>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr id="divAvaliador" style="display:none;">
                                                <td class="campo20">Curso que avalia:</td>
                                                <td class="campo80">
                                                    <?php
                                                    require_once ($CFG->rpasta . "/controle/CTCurso.php");
                                                    $curso = !Util::vazioNulo($objUsuario->getUSR_ID_CUR_AVALIADOR()) ? buscarCursoPorIdCT($objUsuario->getUSR_ID_CUR_AVALIADOR()) : NULL;
                                                    if ($curso != NULL) {
                                                        ?>
                                                        <span class='textoItem'><?php print $curso->getCUR_NM_CURSO(); ?></span><br/>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <span class='textoItem'>&LT;Não alocado&GT;</span><br/>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Login:</td>
                                                <td class="campo80"><?php print $objUsuario->getUSR_DS_LOGIN(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Email</td>
                                                <td class="campo80"><?php print $objUsuario->getUSR_DS_EMAIL(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Vínculo com a UFES:</td>
                                                <td class="campo80"><?php print Usuario::getDsVinculoUFES($objUsuario->getUSR_TP_VINCULO_UFES()); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Situação:</td>
                                                <td class="campo80"><?php print NGUtil::getDsSituacao($objUsuario->getUSR_ST_SITUACAO()); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="completo m02">
                                    <h3 class="sublinhado">Logs</h3>
                                    <div class="col-full">
                                        <table class="mobileBorda table-bordered table" style="width:100%;">
                                            <tr>
                                                <td class="campo20">Solic. de Troca de Senha:</td>
                                                <td class="campo80"><?php print $objUsuario->getUSR_DT_SOLIC_TROCA_SENHA(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">URL de Troca de Senha:</td>
                                                <td class="campo80"><?php print $objUsuario->getUSR_DS_URL_TROCA_SENHA(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Data de Criação:</td>
                                                <td class="campo80"><?php print $objUsuario->getUSR_LOG_DT_CRIACAO(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Último Acesso:</td>
                                                <td class="campo80"><?php print $objUsuario->getUSR_LOG_DT_ULT_LOGIN(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="campo20">Trocar Senha Próx. Acesso:</td>
                                                <td class="campo80"><?php print NGUtil::getDsSimNao($objUsuario->getUSR_TROCAR_SENHA(), TRUE); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <?php
                            if ($objUsuario->getUSR_TP_USUARIO() == Usuario::$USUARIO_CANDIDATO) {
                                // recuperando identificaçao do usuario
                                $objIdent = buscarIdentCandPorIdUsuCT($objUsuario->getUSR_ID_USUARIO());
                                ?>
                                <div class="tab-pane" id="tab2">
                                    <div class="completo m02">
                                        <h3 class="sublinhado">Dados</h3>
                                        <div class="col-full">
                                            <table class="mobileBorda table-bordered table">
                                                <tr>
                                                    <td class="campo20">CPF:</td>
                                                    <td class="campo80"><?php print $objIdent->getNrCPFMascarado(); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Nacionalidade:</td>
                                                    <td class="campo80">
                                                        <?php
                                                        if (!Util::vazioNulo($objIdent->getIDC_NM_NACIONALIDADE())) {
                                                            print DS_SELECT_OUTRA . ": " . $objIdent->getIDC_NM_NACIONALIDADE();
                                                        } else {
                                                            $nac = buscarNacionalidadePorIdCT($objIdent->getNAC_ID_NACIONALIDADE());
                                                            print !Util::vazioNulo($nac) ? $nac->getNAC_NM_NACIONALIDADE() : Util::$STR_CAMPO_VAZIO;
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Sexo:</td>
                                                    <td class="campo80"><?php print $objIdent->getDsSexo($objIdent->getIDC_DS_SEXO()); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Etnia:</td>
                                                    <td class="campo80"><?php print $objIdent->getDsRaca($objIdent->getIDC_TP_RACA()); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Estado Civil:</td>
                                                    <td class="campo80"><?php print $objIdent->getDsEstCivil($objIdent->getIDC_TP_ESTADO_CIVIL()); ?></td>
                                                </tr>
                                                <?php if ($objIdent->getIDC_TP_ESTADO_CIVIL() == IdentificacaoCandidato::$EST_CIVIL_CASADO || $objIdent->getIDC_TP_ESTADO_CIVIL() == IdentificacaoCandidato::$EST_CIVIL_UNIAO_ESTAVEL) { ?>
                                                    <tr>
                                                        <td class="campo20">Nome do cônjuge:</td>
                                                        <td class="campo80"><?php print $objIdent->getIDC_NM_CONJUGE(TRUE); ?></td>
                                                    </tr>
                                                <?php } ?> 
                                                <tr>
                                                    <td class="campo20">Nome do pai:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_FIL_NM_PAI(TRUE); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Nome da mãe:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_FIL_NM_MAE(TRUE); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="completo m02">
                                        <h3 class="sublinhado">Nascimento</h3>
                                        <div class="col-full">
                                            <table class="mobileBorda table-bordered table" style="width:100%;">
                                                <tr>
                                                    <td class="campo20">País:</td>
                                                    <td class="campo80">
                                                        <?php
                                                        $pais = buscarPaisPorIdCT($objIdent->getIDC_NASC_PAIS());
                                                        print !Util::vazioNulo($pais) ? $pais->getPAI_NM_PAIS() : Util::$STR_CAMPO_VAZIO;
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php if ($objIdent->getIDC_NASC_PAIS() == Pais::$PAIS_BRASIL) { ?>
                                                    <tr>
                                                        <td class="campo20">Estado:</td>
                                                        <td class="campo80">
                                                            <?php
                                                            $estado = buscarEstadoPorIdCT($objIdent->getIDC_NASC_ESTADO());
                                                            print $estado->getEST_NM_ESTADO();
                                                            ?>                                                
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="campo20">Cidade:</td>
                                                        <td class="campo80">
                                                            <?php
                                                            $cidade = buscarCidadePorIdCT($objIdent->getIDC_NASC_CIDADE());
                                                            if (!Util::vazioNulo($cidade)) {
                                                                print $cidade->getCID_NM_CIDADE();
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                <tr>
                                                    <td class="campo20">Data:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_NASC_DATA(TRUE); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="completo m02">
                                        <h3 class="sublinhado">RG (Identidade)</h3> 
                                        <div class="col-full">
                                            <table class="mobileBorda table-bordered table" style="width:100%;">
                                                <tr>
                                                    <td class="campo20">Número:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_RG_NUMERO(TRUE); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Órgão emissor:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_RG_ORGAO_EXP(TRUE); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Unidade Federativa:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_RG_UF(TRUE); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Data de emissão:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_RG_DT_EMISSAO(TRUE); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="completo m02">
                                        <h3 class="sublinhado">Ocupação Principal</h3> 
                                        <div class="col-full">
                                            <table class="mobileBorda table-bordered table" style="width:100%;">
                                                <tr>
                                                    <td class="campo20">Ocupação:</td>
                                                    <td class="campo80">
                                                        <?php
                                                        if (!Util::vazioNulo($objIdent->getIDC_NM_OCUPACAO())) {
                                                            print DS_SELECT_OUTRA . ": " . $objIdent->getIDC_NM_OCUPACAO();
                                                        } else {
                                                            $ocp = buscarOcupacaoPorIdCT($objIdent->getOCP_ID_OCUPACAO());
                                                            print !Util::vazioNulo($ocp) ? $ocp->getOCP_NM_OCUPACAO() : Util::$STR_CAMPO_VAZIO;
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Vínculo Público:</td>
                                                    <td class="campo80"><?php print NGUtil::getDsSimNao($objIdent->getIDC_VINCULO_PUBLICO(), TRUE); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <?php
                                    $mostrarFunc = !Util::vazioNulo($objIdent->getIDC_UFES_SIAPE()) || !Util::vazioNulo($objIdent->getIDC_UFES_SETOR()) || !Util::vazioNulo($objIdent->getIDC_UFES_LOTACAO());
                                    if ($mostrarFunc) {
                                        ?>

                                        <div class="completo m02">
                                            <h3 class="sublinhado">Dados Funcionais (apenas para servidores da UFES)</h3> 
                                            <div class="col-full">
                                                <table class="mobileBorda table-bordered table" style="width:100%;">
                                                    <tr>
                                                        <td class="campo20">SIAPE:</td>
                                                        <td class="campo80"><?php print $objIdent->getIDC_UFES_SIAPE(TRUE); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="campo20">Lotação:</td>
                                                        <td class="campo80"><?php print $objIdent->getIDC_UFES_LOTACAO(TRUE); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="campo20">Setor:</td>
                                                        <td class="campo80"><?php print $objIdent->getIDC_UFES_SETOR(TRUE); ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <div class="completo m02">
                                        <h3 class="sublinhado">Passaporte</h3> 
                                        <div class="col-full">
                                            <table class="mobileBorda table-bordered table" style="width:100%;">
                                                <tr>
                                                    <td class="campo20">Número:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_PSP_NUMERO(TRUE); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Data de emissão:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_PSP_DT_EMISSAO(TRUE); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Data de validade:</td>
                                                    <td class="campo80"><?php print $objIdent->getIDC_PSP_DT_VALIDADE(TRUE); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">País emissor:</td>
                                                    <td class="campo80">
                                                        <?php
                                                        if (!Util::vazioNulo($objIdent->getIDC_PSP_PAIS_ORIGEM())) {
                                                            $pai = buscarPaisPorIdCT($objIdent->getIDC_PSP_PAIS_ORIGEM());
                                                            print $pai->getPAI_NM_PAIS();
                                                        } else {
                                                            print Util::$STR_CAMPO_VAZIO;
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab3">
                                    <?php
                                    // buscando endereços
                                    $endRes = buscarEnderecoCandPorIdUsuarioCT($objUsuario->getUSR_ID_USUARIO(), Endereco::$TIPO_RESIDENCIAL);
                                    $endCom = buscarEnderecoCandPorIdUsuarioCT($objUsuario->getUSR_ID_USUARIO(), Endereco::$TIPO_COMERCIAL);

                                    // buscando contato
                                    $contCand = buscarContatoCandPorIdUsuarioCT($objUsuario->getUSR_ID_USUARIO());
                                    ?>
                                    <div class="completo m02">
                                        <h3 class="sublinhado">Endereço Residencial</h3>
                                        <div class="col-full">
                                            <?php print $endRes->getStrEndereco(); ?>
                                        </div>
                                    </div>
                                    <div class="completo m02">
                                        <h3 class="sublinhado">Endereço Comercial</h3>
                                        <div class="col-full">
                                            <?php print $endCom->getStrEndereco(); ?>
                                        </div>
                                    </div>
                                    <div class="completo m02">
                                        <h3 class="sublinhado">Contato</h3>
                                        <div class="col-full">
                                            <table class="mobileBorda table-bordered table" style="width:100%;">
                                                <tr>
                                                    <td class="campo20">Telefone residencial:</td>
                                                    <td class="campo80"><?php print ContatoCandidato::getTelFaxMascarado($contCand->getCTC_NR_TEL_RES()); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Telefone comercial:</td>
                                                    <td class="campo80"><?php print ContatoCandidato::getTelFaxMascarado($contCand->getCTC_NR_TEL_COM()); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Celular:</td>
                                                    <td class="campo80"><?php print $contCand->getNrCelularMascarado(); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Fax:</td>
                                                    <td class="campo80"><?php print ContatoCandidato::getTelFaxMascarado($contCand->getCTC_NR_FAX()) ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="campo20">Email alternativo:</td>
                                                    <td class="campo80"><?php print $contCand->getCTC_EMAIL_CONTATO(TRUE); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab4">
                                    <?php
                                    // buscando lattes
                                    $lattes = buscarLinkLattesPorIdUsuCT($objUsuario->getUSR_ID_USUARIO());
                                    ?>

                                    <?php DAC_geraHtml($candidato, "m01"); ?>


                                    <div class="m01">
                                        <b>Currículo Lattes:</b>
                                        <a id="linkLattes" target="_blank" <?php !$lattes['val'] ? print "title='URL do link Lattes não informada' onclick='javascript: return false;'" : print "href='" . $lattes['link'] . "'"; ?>><?php print $lattes['link']; ?></a>
                                    </div>
                                    <?php
                                    // buscando formaçoes
                                    $listaFormacao = buscarFormacaoPorIdUsuarioCT($objUsuario->getUSR_ID_USUARIO(), NULL, NULL);
                                    if (!Util::vazioNulo($listaFormacao)) {
                                        foreach ($listaFormacao as $formacao) {
                                            ?>  
                                            <div class="completo m02">
                                                <h3 class="sublinhado"><?php print "{$formacao->getDsPeriodo()} - {$formacao->TPC_NM_TIPO_CURSO}" ?></h3>
                                                <div class="col-full">
                                                    <table class="mobileBorda table-bordered table" style="width:100%;">
                                                        <tr>
                                                            <td class="campo20">Instituição:</td>
                                                            <td class="campo80"><?php print $formacao->getDsInstituicaoComp(); ?></td>
                                                        </tr>
                                                        <?php if (!Util::vazioNulo($formacao->getFRA_NM_CURSO())) { ?>
                                                            <tr>
                                                                <td class="campo20">Curso:</td>
                                                                <td class="campo80"><?php print $formacao->getFRA_NM_CURSO(); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        <?php if (!Util::vazioNulo($formacao->getFRA_ID_AREA_CONH())) { ?>
                                                            <tr>
                                                                <td class="campo20">Área:</td>
                                                                <td class="campo80"><?php print $formacao->getDsAreaSubarea(); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        <?php if (!Util::vazioNulo($formacao->getFRA_CARGA_HORARIA())) { ?>
                                                            <tr>
                                                                <td class="campo20">Carga horária (hs):</td>
                                                                <td class="campo80"><?php print $formacao->getFRA_CARGA_HORARIA(); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        <?php if (TipoCurso::isIdAdmiteDetalhamento($formacao->getTPC_ID_TIPO_CURSO())) { ?>
                                                            <tr>
                                                                <td class="campo20">Trabalho:</td>
                                                                <td class="campo80"><?php print $formacao->getFRA_TITULO_TRABALHO(); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="campo20">Orientador:</td>
                                                                <td class="campo80"><?php print $formacao->getFRA_ORIENTADOR_TRABALHO(); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php
                                    } else {
                                        ?>
                                        <div class="callout callout-warning m02">O candidato não cadastrou formação / titulação.</div>
                                    <?php }
                                    ?>
                                </div>

                                <div class="tab-pane" id="tab5">

                                    <?php DAC_geraHtml($candidato, "m01"); ?>

                                    <div class="completo m02"><?php
                                        // criando filtro para obtençao de dados
                                        $filtro = new FiltroPublicacao(array(), NULL, $objUsuario->getUSR_ID_USUARIO(), "", FALSE);
                                        $filtro->setInicioDados(NULL);
                                        $filtro->setQtdeDados(NULL);

                                        // imprimindo tabela
                                        print tabelaPublicacaoCandPorFiltroCT($filtro, FALSE);
                                        ?></div>
                                </div>
                                <div class="tab-pane" id="tab6">

                                    <?php DAC_geraHtml($candidato, "m01"); ?>

                                    <div class="completo m02"><?php
                                        // criando filtro para obtençao de dados
                                        $filtro = new FiltroPartEvento(array(), NULL, $objUsuario->getUSR_ID_USUARIO(), "", FALSE);
                                        $filtro->setInicioDados(NULL);
                                        $filtro->setQtdeDados(NULL);

                                        // imprimindo tabela
                                        print tabelaPartEventoCandPorFiltroCT($filtro, FALSE);
                                        ?></div>
                                </div>
                                <div class="tab-pane" id="tab7">

                                    <?php DAC_geraHtml($candidato, "m01"); ?>

                                    <div class="completo m02"><?php
                                        // criando filtro para obtençao de dados
                                        $filtro = new FiltroAtuacao(array(), NULL, $objUsuario->getUSR_ID_USUARIO(), "", FALSE);
                                        $filtro->setInicioDados(NULL);
                                        $filtro->setQtdeDados(NULL);

                                        // imprimindo tabela
                                        print tabelaAtuacaoCandPorFiltroCT($filtro, FALSE);
                                        ?></div>
                                </div>
                            <?php }
                            ?>
                        </div>
                    </div>
                    <div class="completo m02">
                        <button class="btn btn-default" type="button" onclick="window.location = 'listarUsuario.php'" >Voltar</button>
                    </div>
                </div>
            </div>
        </div>  
        <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {

            // ativar para coordenador
            function ativaParaCoordenador(valor) {
                return valor == '<?php print Usuario::$USUARIO_COORDENADOR; ?>';
            }

            // ativar para avaliador
            function ativaParaAvaliador(valor) {
                return valor == '<?php print Usuario::$USUARIO_AVALIADOR; ?>';
            }

            // definindo exibicao de divs
            var tpUsuario = '<?php print $objUsuario->getUSR_TP_USUARIO(); ?>';
            if (ativaParaCoordenador(tpUsuario)) {
                $("#divCoordenador").show();
            } else if (ativaParaAvaliador(tpUsuario))
            {
                $("#divAvaliador").show();
            }
        });
    </script>
</html>

