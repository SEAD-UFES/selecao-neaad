<?php
global $CFG;
require_once ($CFG->rpasta . "/util/sessao.php");
?>

<!-- Barra do Governo -->
<div id="barra-brasil" style="background:#7F7F7F; height: 20px; padding:0 0 0 10px;display:block;"> 
    <ul id="menu-barra-temp" style="list-style:none;">
        <li style="display:inline; float:left;padding-right:10px; margin-right:10px; border-right:1px solid #EDEDED"><a href="http://brasil.gov.br" style="font-family:sans,sans-serif; text-decoration:none; color:white;">Portal do Governo Brasileiro</a></li> 
        <li><a style="font-family:sans,sans-serif; text-decoration:none; color:white;" href="http://epwg.governoeletronico.gov.br/barra/atualize.html">Atualize sua Barra de Governo</a></li>
    </ul>
</div>

<div id="toposite">
    <nav id="menu-superior" class="navbar app-navbar app-navbar-inverse navbar-inverse">
        <nav class="navbar-default">
            <div class="header-bg brand pull-left" style="width:100%;">
                <span class="sead-bio" style="float:left;">
                    <a href="http://www.neaad.ufes.br/" target="_blank"><img alt="Logo da SEAD" src="<?php print $CFG->rwww . "/imagens/sead-menu.png"; ?>"></a>
                </span>
                <span style="float:right;margin:5px 0px;">
                    <a href="http://portal.ufes.br" title="Universidade Federal do Espírito Santo" target="_blank">
                        <span class="logoufes-topo"><img alt="Logo da UFES" src="<?php print $CFG->rwww . "/imagens/ufes-logo.png"; ?>"></span>
                    </a>
                </span>
            </div>
        </nav>
    </nav>

    <nav id="menu-principal" class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menuPrincipal">
                    <span style="color:#fff;">Menu</span>
                </button>
                <a class="navbar-brand" href="<?php print $CFG->rwww . "/inicio"; ?>"><span class='xsmall no-tablet'>Sistema de</span> Seleção</a>
            </div>


            <?php
            if (estaLogado() != NULL) {
                //recuperando dados do login
                $tipo = getTipoUsuarioLogado();
                ?>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="menuPrincipal">
                    <ul class="nav navbar-nav navbar-right menupai">
                        <li class="mnp dropdown pri">
                            <a class="dropdown-toggle" data-hover="dropdown" data-close-othrs="true" aria-expanded="false" href="<?php print $CFG->rwww . "/inicio"; ?>"><i class="fa fa-home menuicone"></i> Início</a>
                        </li>

                        <?php if ($tipo == Usuario::$USUARIO_ADMINISTRADOR) { ?>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-list menuicone"></i> Parametrização <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="<?php print $CFG->rwww . "/visao/parametrizacao/polo.php" ?>">Polo</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/parametrizacao/tipoAtribuicao.php" ?>">Tipo de Atribuição</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/parametrizacao/tipoFormacao.php" ?>">Tipo de Formação</a></li>
                                </ul>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-file menuicone"></i> Cadastros <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="<?php print $CFG->rwww . "/visao/departamento/listarDepartamento.php" ?>">Departamento</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/curso/listarCurso.php" ?>">Curso</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/usuario/listarUsuario.php" ?>">Usuário</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/candidato/listarCandidato.php" ?>">Candidato</a></li>
                                </ul>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-book menuicone"></i> Editais <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="<?php print $CFG->rwww . "/visao/processo/criarProcesso.php" ?>">Novo</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/processo/listarProcessoAdmin.php" ?>">Lista</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/inscricaoProcesso/listarAvaliacaoCegaInsc.php" ?>">Avaliação Cega</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/inscricaoProcesso/validarCodigoCompInsc.php" ?>">Validar Comprovante</a></li>
                                </ul>
                            </li>


                        <?php } elseif ($tipo == Usuario::$USUARIO_COORDENADOR) { ?>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-file menuicone"></i> Cadastros <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="<?php print $CFG->rwww . "/visao/candidato/listarCandidato.php" ?>">Candidato</a></li>
                                </ul>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-book menuicone"></i> Editais <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="<?php print $CFG->rwww . "/visao/processo/criarProcesso.php" ?>">Novo</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/processo/listarProcessoAdmin.php" ?>">Lista</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/inscricaoProcesso/listarAvaliacaoCegaInsc.php" ?>">Avaliação Cega</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/inscricaoProcesso/validarCodigoCompInsc.php" ?>">Validar Comprovante</a></li>
                                </ul>
                            </li>


                        <?php } elseif ($tipo == Usuario::$USUARIO_AVALIADOR) { ?>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-book menuicone"></i> Editais <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="<?php print $CFG->rwww . "/visao/inscricaoProcesso/listarAvaliacaoCegaInsc.php" ?>">Avaliação Cega</a></li>
                                </ul>
                            </li>


                        <?php } elseif ($tipo == Usuario::$USUARIO_CANDIDATO) { ?>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-user menuicone"></i> Candidato <span class="caret"></span></a>
                                <ul class="dropdown-menu submenu" role="menu">
                                    <li><a href="<?php print $CFG->rwww . "/visao/candidato/editarIdentificacao.php" ?>">Identificação</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/candidato/editarEndereco.php" ?>">Endereço</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/candidato/editarContato.php" ?>">Contato</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/formacao/listarFormacao.php" ?>">Currículo</a></li>
                                </ul>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-book menuicone"></i> Editais <span class="caret"></span></a>
                                <ul class="dropdown-menu submenu" role="menu">
                                    <li><a href="<?php print $CFG->rwww . "/editais" ?>">Lista</a></li>
                                    <li><a href="<?php print $CFG->rwww . "/visao/inscricaoProcesso/listarInscProcessoUsuario.php" ?>">Minhas inscrições</a></li>
                                </ul>
                            </li>

                        <?php } ?>                    

                        <li class="dropdown ult">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" role="button" aria-expanded="false"><i class="fa fa-cog menuicone"></i> Sistema <span class="caret"></span></a>
                            <ul class="dropdown-menu submenu" role="menu">
                                <?php if ($tipo == Usuario::$USUARIO_CANDIDATO) { ?>
                                    <li><a href="<?php print $CFG->rwww . "/ajuda" ?>">Ajuda</a></li>
                                <?php } ?>
                                <li><a href="<?php print $CFG->rwww . "/contato" ?>">Contato</a></li>
                                <li><a href="<?php print $CFG->rwww . "/visao/usuario/alterarDadosAcesso.php" ?>">Gerenciar Acesso</a></li>
                                <li><a href="<?php print $CFG->rwww . "/visao/usuario/manterConfiguracao.php" ?>">Configurações</a></li>
                                <li class="divider"></li>
                                <li><a href="<?php print $CFG->rwww . "/sair" ?>">Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
                <?php
            } else {
                ?>
                <div class="collapse navbar-collapse" id="menuPrincipal">
                    <ul class="nav navbar-nav navbar-right menupai">
                        <li class="dropdown">
                            <a href="<?php print $CFG->rwww . "/ajuda" ?>" role="button"><i class="fa fa-info-circle menuicone"></i> Precisa de ajuda?</a>
                        </li>
                        <li class="dropdown">
                            <a href="<?php print $CFG->rwww . "/contato" ?>" role="button"><i class="fa fa-envelope menuicone"></i> Entre em contato</a>
                        </li>
                    </ul>
                </div>
            <?php }
            ?>
        </div><!-- /.container-fluid -->
    </nav>
</div>